<?php

namespace Sie\InfraestructuraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\InfraestructuraJuridiccionGeografica;

class InfoController extends Controller
{
  public $session;
  public function __construct(){
    $this->session = new Session();
  }

    public function indexAction(Request $request)
    {
      //ge the data by operativo
//dump($this->session->get('currentyear'));
        //$this->session->set('ie_id','80730037');
        $em = $this->getDoctrine()->getManager();
        if($this->session->get('ie_id')!= -1){
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id'));
            $infJurGoe = $em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->findBy(array('juridiccionGeografica'=>$institucion->getLeJuridicciongeografica()->getId()),array('gestionTipo'=>'DESC','infraestructura'=>'ASC'));

                $this->session->set('infCodigoEdificio',$institucion->getLeJuridicciongeografica()->getId());
                $infArray = array();
                $gestionRef = $infJurGoe[0]->getId();
                $cont = 0;
                foreach ($infJurGoe as $i) {;
                    $infArray[$i->getGestionTipo()->getId()][] = $i;
                }
            if(!$infJurGoe){
                $infArray = null;
            }
        }else{
            $infArray = null;
        }
        //dump($infArray);die;
        /*
        dump($this->session->get('ie_id'));
        die;
        $this->session->set('infJurGeoId',13394);*/
        return $this->render('SieInfraestructuraBundle:Info:index.html.twig', array('infJurGeo'=>$infArray));
    }

    /*private function formAcceder($infJurGoe){
      return $this->createFormBuilder()
                  ->setAction($this->generateUrl('infra_info_acceder'))
                  ->add('gestionId','hidden',array('data'=>$inf->getGestionTipo()->getid()))
                  ->add('infJurGeoId','hidden',array('data'=>$inf->getId()))
                  ->add('acceder', 'submit', array('label'=>'Acceder'))
                  ->getForm()
                  ;
    }*/

    public function accederAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $infJurGeo = $em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($this->session->get('infJurGeoId'));
        return $this->render('SieInfraestructuraBundle:Info:acceder.html.twig',array('infJurGeo'=>$infJurGeo));
    }

    public function principalAction(Request $request){
        $this->session->set('infJurGeoId',$request->get('infJurGeoId'));
        $em = $this->getDoctrine()->getManager();
        $infJurGeo = $em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($this->session->get('infJurGeoId'));
        $this->session->set('infCodigoEdificio',$infJurGeo->getJuridiccionGeografica()->getId());
        $this->session->set('infNumeroInfraestructura',$infJurGeo->getInfraestructura());
        $this->session->set('infGestionId',$infJurGeo->getGestionTipo()->getId());
        return $this->render('SieInfraestructuraBundle:Info:acceder.html.twig',array('infJurGeo'=>$infJurGeo));
    }

    public function adicionarInfraestructuraAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $infJurGeo = $em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($request->get('infJurGeoId'));

        $infJurGeoGestion = $em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->findBy(array('juridiccionGeografica'=>$infJurGeo->getJuridiccionGeografica()->getId(),'gestionTipo'=>$infJurGeo->getGestionTipo()->getId()));

        $numeroInfra = count($infJurGeoGestion) + 1;

        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_juridiccion_geografica');")->execute();
        $newInfJurGeo = new InfraestructuraJuridiccionGeografica();
        $newInfJurGeo->setJuridiccionGeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($infJurGeo->getJuridiccionGeografica()->getId()));
        $newInfJurGeo->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($infJurGeo->getGestionTipo()->getId()));
        $newInfJurGeo->setFechaoperativo(new \DateTime('now'));
        $newInfJurGeo->setObs('');
        $newInfJurGeo->setFecharegistro(new \DateTime('now'));
        $newInfJurGeo->setInfraestructura($numeroInfra);

        $em->persist($newInfJurGeo);
        $em->flush();

        return $this->redirectToRoute('infra_info_index');
    }
}
