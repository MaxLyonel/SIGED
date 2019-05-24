<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\RegistroConsolidacion;
use Sie\AppWebBundle\Entity\EstudianteInscripcionHumnisticoTecnico;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * InfoEstudianteAreasEstudiante controller.
 */
class InfoEstudianteAreasEstudianteController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * [indexAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function indexAction(Request $request){

        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');
        $infoStudent = json_decode($infoStudent, true);

        $idInscripcion = $infoStudent['eInsId'];

        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

        $areas = $this->get('areasEstudiante')->areasEstudiante($idInscripcion);

        return $this->render('SieHerramientaBundle:InfoEstudianteAreasEstudiante:index.html.twig',array(
                'areas'=>$areas,
                'inscripcion'=>$inscripcion
        ));
    }

    public function deleteAction(Request $request){
        $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
        $em = $this->getDoctrine()->getManager();
        $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura);
        
        $inscripcion = $estudianteAsignatura->getEstudianteInscripcion();
        $idInscripcion = $inscripcion->getId();
        
        $data = [];

        if($estudianteAsignatura){
            // VERIFICAMOS SI EL AREA NO TIENE NOTAS
            $notas = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura'=>$idEstudianteAsignatura));
            if($notas){
                $data = array(
                    'status'=>500,
                    'type'=>'warning',
                    'msg'=> 'No se puede eliminar el área. porque ya cuenta con notas.'
                );
            }else{

                // ELIMINAMOS LA ESPECIALIDAD SI LA MATERIA ES TECNICA ESPECIALIZADA
                $codigoAsignatura = $estudianteAsignatura->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId();
                if($codigoAsignatura == 1039){
                    $eliminarEspecialidad = $em->createQueryBuilder()
                                    ->delete('')
                                    ->from('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico','eiht')
                                    ->where('eiht.estudianteInscripcion = :idEstudianteInscripcion')
                                    ->setParameter('idEstudianteInscripcion', $idInscripcion)
                                    ->getQuery()
                                    ->getResult();
                }

                $eliminar = $this->get('areasEstudiante')->delete($idEstudianteAsignatura);
                if($eliminar){
                    $data = array(
                        'status'=>200,
                        'type'=>'success',
                        'msg'=> 'Área  eliminada correctamente.'
                    );
                }else{
                    $data = array(
                        'status'=>500,
                        'type'=>'danger',
                        'msg'=> 'Error al eliminar el área del estudiante.'
                    );
                }
            }
        }else{
            $data = array(
                'status'=>500,
                'type'=>'danger',
                'msg'=> 'Error al eliminar el área del estudiante.'
            );
        }

        $areas = $this->get('areasEstudiante')->areasEstudiante($idInscripcion);

        return $this->render('SieHerramientaBundle:InfoEstudianteAreasEstudiante:index.html.twig',array(
                'areas'=>$areas,
                'inscripcion'=>$inscripcion,
                'data'=>$data
        ));
    }

    public function newAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            

            $idCursoOferta = $request->get('idco');
            $idInscripcion = $request->get('idInscripcion');

            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

            $area = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array(
                'estudianteInscripcion'=>$idInscripcion,
                'institucioneducativaCursoOferta'=>$idCursoOferta
            ));
            $data = [];
            if($area){
                // SI EL ESTUDIANTE YA TIENE EL AREA
                $data = array(
                    'status'=>500,
                    'type'=>'warning',
                    'msg'=> 'El estudiante ya cuenta con el área.'
                );
            }else{

                $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
                
                // SI NO TIENE EL AREA, ENTONCES LO AGREGAMOS
                $nuevaArea = $this->get('areasEstudiante')->nuevo($idCursoOferta, $idInscripcion, $gestion);

                if($nuevaArea){
                    // SI TODO SE REALIZO CON EXITO
                    $data = array(
                        'status'=>200,
                        'type'=>'success',
                        'msg'=> 'El área se agregó correctamente.'
                    );
                }else{
                    // SI OCURRIO UN ERROR AL AGREGAR EL AREA
                    $data = array(
                        'status'=>500,
                        'type'=>'danger',
                        'msg'=> 'Ocurrió un error al agregar el área.'
                    );
                }
            }
            
            $areas = $this->get('areasEstudiante')->areasEstudiante($idInscripcion);

            return $this->render('SieHerramientaBundle:InfoEstudianteAreasEstudiante:index.html.twig',array(
                    'areas'=>$areas,
                    'inscripcion'=>$inscripcion,
                    'data'=>$data
            ));

        } catch (Exception $e) {
            $em->getConnection()->rollback();
        }        
    }

    /**
     * FUNCIONES PARA VALIDAR QUE LA MATERIA ESPECIALIZADA TENGA REGISTRADA LA ESPECILIADAD
     */

    public function especialidadVerificarAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $idInscripcion = $request->get('idInscripcion');
        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');

        // VERIFICAMOS SI EL ESTUDIANTE TIENE LA MATERIA ESPECIALIZADA
        $materiaEspecializada = $em->createQueryBuilder()
                        ->select('at.id, at.asignatura')
                        ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                        ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','with','ea.estudianteInscripcion = ei.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','with','ea.institucioneducativaCursoOferta = ieco.id')
                        ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','with','ieco.asignaturaTipo = at.id')
                        ->where('ei.id = :idInscripcion')
                        ->andWhere('at.id = 1039')
                        ->setParameter('idInscripcion', $idInscripcion)
                        ->getQuery()
                        ->getResult();

        if($materiaEspecializada){
            // VERIFICAMOS SI YA TIENE REGISTRADO LA ESPECIALIDAD
            $especialidadEstudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array(
                'estudianteInscripcion'=>$idInscripcion
            ));

            // SI EL ESTUDIANTE YA TIENE ESPECIALIDAD REGISTRADA
            if(!$especialidadEstudiante){
                $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
                $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
                // OBTENEMOS LAS ESPECIALIDADES DE LA UNIDAD EDUCATIVA
                $especialidadesUe = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->findBy(array(
                    'institucioneducativa'=>$sie,
                    'gestionTipo'=>$gestion
                ));
                $arrayEsp = [];
                foreach ($especialidadesUe as $eue) {
                    $arrayEsp[$eue->getId()] = $eue->getEspecialidadTecnicoHumanisticoTipo()->getEspecialidad();
                }
                $form = $this->createFormBuilder()
                            ->add('idInscripcion', 'hidden', array('data' => $idInscripcion))
                            ->add('infoUe', 'hidden', array('data' => $infoUe))
                            ->add('infoStudent', 'hidden', array('data' => $infoStudent))
                            ->add('idieeht', 'choice', array('required' => true, 'choices'=>$arrayEsp, 'empty_value'=>'Seleccionar...', 'attr'=>array('class'=>'form-control')))
                            ->getForm();

                return $this->render('SieHerramientaBundle:InfoEstudianteAreasEstudiante:especialidad.html.twig',array(
                    'inscripcion'=>$inscripcion,
                    'form'=>$form->createView()
                ));   
            }else{

            }
        }else{
            // SI NO TIENE LA MATERIA ESPECIALIZADA ENTONCES PASA LA VALIDACION
            $response = new JsonResponse();
            return $response->setData(array(
                'validado'=>true,
                'idInscripcion'=>$idInscripcion,
                'infoUe'=>$infoUe,
                'infoStudent'=>$infoStudent
            ));
        }

        $response = new JsonResponse();
        return $response->setData(array(
            'validado'=>true,
            'idInscripcion'=>$idInscripcion,
            'infoUe'=>$infoUe,
            'infoStudent'=>$infoStudent
        ));
       
    }

    public function especialidadRegistrarAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $infoUe = $form['infoUe'];
        $infoStudent = $form['infoStudent'];
        $idInscripcion = $form['idInscripcion'];
        $idieeht = $form['idieeht'];

        $institucionEspecialidad = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->find($idieeht);

        $especialidadEstudiante = new EstudianteInscripcionHumnisticoTecnico();
        $especialidadEstudiante->setInstitucioneducativaHumanisticoId($institucionEspecialidad->getId());
        $especialidadEstudiante->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
        $especialidadEstudiante->setEspecialidadTecnicoHumanisticoTipo($em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($institucionEspecialidad->getEspecialidadTecnicoHumanisticoTipo()->getId()));
        $especialidadEstudiante->setHoras(0);
        $em->persist($especialidadEstudiante);
        $em->flush();

        $response = new JsonResponse();
        return $response->setData(array(
            'idInscripcion'=>$idInscripcion,
            'infoUe'=>$infoUe,
            'infoStudent'=>$infoStudent
        ));

    }
}
