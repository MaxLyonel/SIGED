<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\EstudianteTrabajoRemuneracion;

class EstudianteTrabajaNuevoController extends Controller{

     public $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request){
        //create db conexion
        $em = $this->getDoctrine()->getManager();
        //get send parameters
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        

        $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfoId']['iecId']);

        $data = array(

            'sie'      => $iec->getInstitucioneducativa()->getId(),
            'gestion'  => $iec->getGestionTipo()->getId(),
            'nivel'    => $iec->getNivelTipo()->getId(),
            'grado'    => $iec->getGradoTipo()->getId(),
            'paralelo' => $iec->getParaleloTipo()->getId(),
            'turno'    => $iec->getTurnoTipo()->getId(),
            'iecId'    => $aInfoUeducativa['ueducativaInfoId']['iecId'],
        );

        $jsonData = json_encode($data);
    
        return $this->render('SieHerramientaBundle:EstudianteTrabajaNuevo:index.html.twig', array(
                'form' => $this->createFormEstudiante($jsonData)->createView(),
                'registrados'=>$this->listaRegistrados($data)
        ));
    }

    private function createFormEstudiante($data){
        return $this->createFormBuilder()
                ->add('rudeoci', 'text', array('mapped' => false, 'required' => true, 'invalid_message' => 'Campor 1 obligatorio', 'attr'=>array('class'=>'form-control','placeholder'=>'Carnet o Rude')))
                ->add('data', 'hidden', array('data'=>$data))
                ->getForm();
    }

    public function buscarAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $rudeoci = $form['rudeoci'];
        $data = $form['data'];

        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('carnetIdentidad'=>$rudeoci));
        if(!$estudiante){
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$rudeoci));
        }

        $inscripcion = null;
        if($estudiante){
            $inscripcion = $em->createQueryBuilder()
                        ->select('ei')
                        ->from('SieAppWebBundle:Estudiante','e')
                        ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','ei.estudiante = e.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                        ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                        ->where('e.id = :estudianteId')
                        // ->andWhere('gt.id = 2018')
                        ->setParameter('estudianteId', $estudiante->getId())
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getResult();
// dump($inscripcion);die;
            if($inscripcion){
                $inscripcion = $inscripcion[0];
                $form = $this->createFormBuilder()
                            ->add('idInscripcion', 'hidden', array('data' => $inscripcion->getId()))
                            ->add('data', 'hidden', array('data' => $data))
                            ->add('ocupacion', 'entity', array(
                                    'class' => 'SieAppWebBundle:ActividadTipo',
                                    'query_builder' => function (EntityRepository $e) {
                                        return $e->createQueryBuilder('at')
                                                ->where('at.id != 20');
                                    },
                                    'property'=>'descripcionOcupacion',
                                    'empty_value' => 'Selecionar...',
                                    'required' => true
                                    // 'data'=>$em->getReference('SieAppWebBundle:ActividadTipo', $ocupacion)
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

                return $this->render('SieHerramientaBundle:EstudianteTrabajaNuevo:find.html.twig', array(
                    'estudiante'=> $estudiante,
                    'formEstudiante'=>$form->createView()
                ));       
            }
        }

        return $this->render('SieHerramientaBundle:EstudianteTrabajaNuevo:find.html.twig', array(
            'estudiante'=>null        
        ));
    }

    public function saveAction(Request $request){
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        $estudianteTrabaja = $em->getRepository('SieAppWebBundle:EstudianteTrabajoRemuneracion')->findBy(array('estudianteInscripcionId'=>$form['idInscripcion']));
        // Verificamos si el estudiante no fue registrado en otra unidad educativa
        $data = json_decode($form['data'],true);

        if(count($estudianteTrabaja) == 0){

            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_trabajo_remuneracion');")->execute();
            $trabajo = new EstudianteTrabajoRemuneracion();
            $trabajo->setEstudianteInscripcionId($form['idInscripcion']);
            $trabajo->setSie($data['sie']);
            $trabajo->setNivel($data['nivel']);
            $trabajo->setGrado($data['grado']);
            $trabajo->setParalelo($data['paralelo']);
            $trabajo->setTurno($data['turno']);
            $trabajo->setGestion($data['gestion']);
            $trabajo->setOcupacion($form['ocupacion']);
            $trabajo->setOcupacionOtro(mb_strtoupper($form['ocupacionOtro'],'utf-8'));
            $trabajo->setRemuneracion($form['remuneracion']);
            $trabajo->setEspecificacion(mb_strtoupper($form['especificacion'],'utf-8'));
            $trabajo->setFechaRegistro(new \DateTime('now'));
            $trabajo->setFechaModificacion(new \DateTime('now'));
            $trabajo->setUsuarioId($this->session->get('userId'));
            $trabajo->setTraslado(true);
            $em->persist($trabajo);
            $em->flush();

            $status = 200;
        }else{
            $status = 500;
        }

        return $this->render('SieHerramientaBundle:EstudianteTrabajaNuevo:list.html.twig', array(
            'registrados'=>$this->listaRegistrados($data),
            'status'=>$status
        ));    
    }

    public function listaRegistrados($data){
        $em = $this->getDoctrine()->getManager();
        // $data = json_decode($form['data'],true);
        // $registrados = $em->getRepository('SieAppWebBundle:EstudianteTrabajoRemuneracion')->findBy(array(
        //     'sie'=>$data['sie'],
        //     'nivel'=>$data['nivel'],
        //     'grado'=>$data['grado'],
        //     'paralelo'=>$data['paralelo'],
        //     'turno'=>$data['turno'],
        //     'gestion'=>$data['gestion']
        // ));

        $registrados = $em->createQueryBuilder()
                        ->select('etr.id, e.codigoRude, e.nombre, e.paterno, e.materno, at.id as ocupacionId, at.descripcionOcupacion as ocupacion, etr.ocupacionOtro, etr.remuneracion, etr.especificacion')
                        ->from('SieAppWebBundle:EstudianteTrabajoRemuneracion','etr')
                        ->innerJoin('SieAppWebBundle:ActividadTipo','at','with','etr.ocupacion = at.id')
                        ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','etr.estudianteInscripcionId = ei.id')
                        ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                        ->where('etr.sie = :sie')
                        ->andWhere('etr.nivel = :nivel')
                        ->andWhere('etr.grado = :grado')
                        ->andWhere('etr.paralelo = :paralelo')
                        ->andWhere('etr.turno = :turno')
                        ->andWhere('etr.gestion = :gestion')
                        ->andWhere('etr.traslado = true')
                        ->setParameter('sie', $data['sie'])
                        ->setParameter('nivel', $data['nivel'])
                        ->setParameter('grado', $data['grado'])
                        ->setParameter('paralelo', $data['paralelo'])
                        ->setParameter('turno', $data['turno'])
                        ->setParameter('gestion', $data['gestion'])
                        ->orderBy('etr.id','asc')
                        ->getQuery()
                        ->getResult();

        return $registrados;
    }

    public function eliminarAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $registro = $em->getRepository('SieAppWebBundle:EstudianteTrabajoRemuneracion')->find($request->get('id'));
        $data = null;
        if ($registro) {
            $data = array(
                'sie'      => $registro->getSie(),
                'gestion'  => $registro->getGestion(),
                'nivel'    => $registro->getNivel(),
                'grado'    => $registro->getGrado(),
                'paralelo' => $registro->getParalelo(),
                'turno'    => $registro->getTurno()
            );

            $em->remove($registro);
            $em->flush();
        }

        return $this->render('SieHerramientaBundle:EstudianteTrabajaNuevo:list.html.twig', array(
            'registrados'=>$this->listaRegistrados($data),
            'status'=>200
        ));
    }
}
