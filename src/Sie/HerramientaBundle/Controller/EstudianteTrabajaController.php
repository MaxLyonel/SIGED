<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\EstudianteTrabajoRemuneracion;

class EstudianteTrabajaController extends Controller
{
    public $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request){

        $em = $this->getDoctrine()->getManager();

        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');

        $aInfoUeducativa = unserialize($infoUe);
        $aInfoStudent = json_decode($infoStudent, TRUE);

        $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfoId']['iecId']);
        $sie = $iec->getInstitucioneducativa()->getId();
        $gestion = $iec->getGestionTipo()->getId();
        $nivel = $iec->getNivelTipo()->getId();
        $grado = $iec->getGradoTipo()->getId();
        $paralelo = $iec->getParaleloTipo()->getId();
        $turno = $iec->getTurnoTipo()->getId();

        $idInscripcion = $aInfoStudent['eInsId'];
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $estudiante = $inscripcion->getEstudiante();

        $trabaja = $em->getRepository('SieAppWebBundle:EstudianteTrabajoRemuneracion')->findOneBy(array('estudianteInscripcionId'=>$inscripcion->getId()));
        if(!$trabaja){
            $trabaja = array(
                'id'=>'nuevo',
                'sie'=>$sie,
                'nivel'=>$nivel,
                'grado'=>$grado,
                'paralelo'=>$paralelo,
                'turno'=>$turno,
                'gestion'=>$gestion,
                'ocupacion'=>0,
                'ocupacionOtro'=>'',
                'remuneracion'=>false,
                'especificacion'=>''
            );
        }
        // dump($trabaja);die;
        if(is_array($trabaja)){
            $ocupacion = $trabaja['ocupacion'];
        }else{
            $ocupacion = $trabaja->getOcupacion();
        }

        // dump($ocupacion);die;

        $form = $this->createFormBuilder($trabaja)
                    // ->setAction($this->generateUrl('info_estudiante_rude_save_form2'))
                    ->add('id', 'hidden')
                    ->add('idInscripcion', 'hidden', array('data' => $inscripcion->getId(), 'mapped'=>false))
                    ->add('sie', 'hidden')
                    ->add('nivel', 'hidden')
                    ->add('grado', 'hidden')
                    ->add('paralelo', 'hidden')
                    ->add('turno', 'hidden')
                    ->add('gestion', 'hidden')
                    ->add('ocupacion', 'entity', array(
                            'class' => 'SieAppWebBundle:ActividadTipo',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('at')
                                        ->where('at.id != 20');
                            },
                            'property'=>'descripcionOcupacion',
                            'empty_value' => 'Selecionar...',
                            'required' => true,
                            'data'=>$em->getReference('SieAppWebBundle:ActividadTipo', $ocupacion)
                        ))
                    ->add('ocupacionOtro', 'text', array('required'=>false))
                    ->add('remuneracion', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            // 'data'=>$socioeconomico->getSeccionvEstudianteEsEnergiaelectrica(),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false,
                            'expanded'=>true
                        ))
                    ->add('especificacion', 'textarea', array('required'=>false))
                    ->getForm();

        // dump($form);die;

        return $this->render('SieHerramientaBundle:EstudianteTrabaja:index.html.twig', array(
            'form'=>$form->createView(),
            'estudiante'=>$estudiante
        ));    
    }

    public function saveAction(Request $request){
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        if($form['id'] == 'nuevo'){
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_trabajo_remuneracion');")->execute();
            $trabajo = new EstudianteTrabajoRemuneracion();
            $trabajo->setEstudianteInscripcionId($form['idInscripcion']);
            $trabajo->setSie($form['sie']);
            $trabajo->setNivel($form['nivel']);
            $trabajo->setGrado($form['grado']);
            $trabajo->setParalelo($form['paralelo']);
            $trabajo->setTurno($form['turno']);
            $trabajo->setGestion($form['gestion']);
            $trabajo->setOcupacion($form['ocupacion']);
            $trabajo->setOcupacionOtro($form['ocupacionOtro']);
            $trabajo->setRemuneracion($form['remuneracion']);
            $trabajo->setEspecificacion(mb_strtoupper($form['especificacion'],'utf-8'));
            $trabajo->setFechaRegistro(new \DateTime('now'));
            $trabajo->setFechaModificacion(new \DateTime('now'));
            $trabajo->setUsuarioId($this->session->get('userId'));
            $em->persist($trabajo);
            $em->flush();
        }else{
            $trabajo = $em->getRepository('SieAppWebBundle:EstudianteTrabajoRemuneracion')->find($form['id']);
            $trabajo->setSie($form['sie']);
            $trabajo->setNivel($form['nivel']);
            $trabajo->setGrado($form['grado']);
            $trabajo->setParalelo($form['paralelo']);
            $trabajo->setTurno($form['turno']);
            $trabajo->setGestion($form['gestion']);
            $trabajo->setOcupacion($form['ocupacion']);
            $trabajo->setOcupacionOtro($form['ocupacionOtro']);
            $trabajo->setRemuneracion($form['remuneracion']);
            $trabajo->setEspecificacion(mb_strtoupper($form['especificacion'],'utf-8'));
            $trabajo->setFechaModificacion(new \DateTime('now'));
            $trabajo->setUsuarioId($this->session->get('userId'));
            $em->flush();
        }

        $response = new JsonResponse();
        return $response->setData([
            'id'=>$trabajo->getId()
        ]);
        
    }

}
