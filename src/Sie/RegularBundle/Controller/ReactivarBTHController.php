<?php

namespace Sie\RegularBundle\Controller;

use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Controller\WfTramiteController;
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLogTipo;
use Sie\AppWebBundle\Entity\RehabilitacionBth;



/**
 * ChangeMatricula controller.
 *
 */
class ReactivarBTHController extends Controller {
    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
    }
    public function indexAction (Request $request) {

        $form = $this->createFormBuilder()
            ->add('nroTramite', 'text', array('label' => 'Nro.', 'required' => true, 'attr' => array('class' => 'form-control')))
            ->add('buscar', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary','onclick'=>'buscarTramite()')))
            ->getForm();
        return $this->render('SieRegularBundle:ReactivarBTH:index.html.twig',array(
            'form' => $form->createView()
            )
        );
    }
    public function buscaTramiteBTHAction(Request $request){
        
        $nroTramite = $request->get('nroTramite');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->createQueryBuilder('t')
                    ->select('t')
                    ->where('t.id = :id or t.institucioneducativa = :id')
                    ->andwhere('t.tramiteTipo IN (:tipo)')
                    ->andwhere('t.fechaFin is not null')
                    ->setParameter('id', $nroTramite)
                    ->setParameter('tipo', array(27,28))
                    ->getQuery()
                    ->getResult();
        //dump($tramite);die;
        if ($tramite){
            
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('reactivarbth_buscar_nuevo_guardar'))
                ->add('idtramite', 'hidden', array('data' => $tramite[0]->getId()))
                ->add('codsie', 'hidden', array('data' => $tramite[0]->getInstitucioneducativa()->getId()))
                ->add('obs', 'textarea', array('label' => 'Observación', 'required' => true, 'attr' => array('class' => 'form-control','style'=>'text-transform:uppercase;')))
                ->add('adjunto', 'file', array('label' => 'Adjuntar respaldo', 'attr' => array('title'=>"Adjuntar Respaldo",'accept'=>"application/pdf,.doc,.docx")))
                ->add('guardar', 'submit', array('label' => 'Reactivar Trámite', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

            return $this->render('SieRegularBundle:ReactivarBTH:reactivarBthGuardar.html.twig',array(
                'formDatos' => $form->createView(),
                'tramite'=> $tramite[0],
                )
            );

        }else{
            $response = new JsonResponse();    
            return $response->setData(array(
                    'msg' => 'El Nro. de trámite/código SIE son incorrectos.',
            ));
        }
    }

    public function buscaTramiteBTHGuardarAction(Request $request){
        
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $form = $request->get('form');
        $documento = $request->files->get('form')['adjunto'];
        
        /*Validacion para que guarde el docuemnto*/
        if(!empty($documento)){
            $root_bth_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$form['codsie'];
            //$root_bth_path = 'uploads/archivos/flujos/'.$form['codsie'];
            //dump($root_bth_path);die;
            if (!file_exists($root_bth_path)) {
                mkdir($root_bth_path, 0777);
            }
            $destination_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$form['codsie'].'/bth/';
            //$destination_path = 'uploads/archivos/flujos/'.$form['codsie'].'/bth/';
            if (!file_exists($destination_path)) {
                mkdir($destination_path, 0777);
            }
            $imagen = date('YmdHis').'.'.$documento->getClientOriginalExtension();
            $documento->move($destination_path, $imagen);
        }else{
            $imagen='default-2x.pdf';
        }
        $em = $this->getDoctrine()->getManager();
        //dump($form,$imagen,$sesion->get('currentyear'));die;
        $ieht = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$form['codsie'],'gestionTipoId'=>$sesion->get('currentyear')));
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($form['idtramite']);
        //dump($form,$imagen,$ieht);die;
        $em->getConnection()->beginTransaction();
        try {
            /**
             * registro de reactivacion bth
            */
            $rehabilitacionBth = new RehabilitacionBth();    
            $rehabilitacionBth->setObs(mb_strtoupper($form['obs'],'UTF-8') );
            $rehabilitacionBth->setFechaInicio(new \DateTime(date('Y-m-d')));
            $rehabilitacionBth->setAdjunto($imagen);
            $rehabilitacionBth->setTramite($tramite);
            $rehabilitacionBth->setInstitucioneducativaHumanisticoTecnico($ieht);
            $em->persist($rehabilitacionBth);
            $em->flush();

            $ieht->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(0));
            $ieht->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find(5));
            $ieht->setFechaModificacion(new \DateTime(date('Y-m-d')));
            $em->flush();

            $tramite->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find(31));
            $em->flush();

            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('success', 'El tramite fué reactivado, la unidad Educativa puede volver a iniciar un nuevo trámite.');
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un error al reactivar el trámite, vuelva a intentar.');
        }
        
        return $this->redirectToRoute('reactivarbth_index');

    }
}
