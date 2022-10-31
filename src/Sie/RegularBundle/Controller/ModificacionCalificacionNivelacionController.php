<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;

/**
 * Modificacion de calificaciones par estudaintes con nivelacion
 */
class ModificacionCalificacionNivelacionController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request)
    {
        return $this->render('SieRegularBundle:ModificacionCalificacionNivelacion:index.html.twig');
    }

    public function buscarAction($codigoRude)
    {
        $data = array(
            "status"=>"error",
            "code"=>400,
            "msg"=>"Datos no válidos !!!"
        );
        
        if ($codigoRude != null) {
            $em = $this->getDoctrine()->getManager();
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$codigoRude));
            if (is_object($estudiante)) {

                // validar tuicion sobre estudiantecomo
                
                $nivelaciones = $em->createQueryBuilder()
                                    ->select('bei.id as idNivelacion, gest.gestion, nt.nivel, gt.grado, pt.paralelo, at.asignatura as area, esp.especialidad, enc.id as idNotaCualitativa, enc.notaCuantitativa as calificacion, bei.obs')
                                    ->from('SieAppWebBundle:BthEstudianteNivelacion','bei')
                                    ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','bei.estudianteInscripcion = ei.id')
                                    ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                                    ->innerJoin('SieAppWebBundle:GestionTipo','gest','with','iec.gestionTipo = gest.id')
                                    ->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
                                    ->innerJoin('SieAppWebBundle:GradoTipo','gt','with','iec.gradoTipo = gt.id')
                                    ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','with','iec.paraleloTipo = pt.id')
                                    ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','with','bei.asignaturaTipo = at.id')
                                    ->leftJoin('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo','esp','with','bei.especialidadTecnicoHumanisticoTipo = esp.id')
                                    ->innerJoin('SieAppWebBundle:EstudianteNotaCualitativa','enc','with','bei.estudianteNotaCualitativa = enc.id')
                                    ->where('e.codigoRude = :rude')
                                    ->setParameter('rude', $codigoRude)
                                    ->getQuery()
                                    ->getResult();

                if (count($nivelaciones)>0) {

                    $estudianteData['codigoRude'] = $estudiante->getCodigoRude(); 
                    $estudianteData['nombre'] = $estudiante->getNombre(); 
                    $estudianteData['paterno'] = $estudiante->getPaterno(); 
                    $estudianteData['materno'] = $estudiante->getMaterno(); 

                    $data = array(
                        "status"=>"success",
                        "code"=>200,
                        "data"=>array(
                            "estudiante"=>$estudianteData,
                            "nivelaciones"=>$nivelaciones
                        )
                    );
                }else{
                    $data = array(
                        "status"=>"error",
                        "code"=>400,
                        "msg"=>"El estudiante no cuenta con nivelaciones registradas !!!"
                    );
                }

            }else{
                $data = array(
                    "status"=>"error",
                    "code"=>400,
                    "msg"=>"El Código Rude ingresado no es válido !!!"
                );
            }
        }else{
            $data = array(
                "status"=>"error",
                "code"=>400,
                "msg"=>"Debe ingresar un Código Rude válido !!!"
            );
        }

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    public function registrarAction(Request $request)
    {
        $idNivelacion = $request->get('idNivelacion', null);
        $idNotaCualitativa = $request->get('idNotaCualitativa', null);
        $nuevaCalificacion = $request->get('nuevaCalificacion', null);
        $obs = $request->get('obs', null);

        if ($idNivelacion != null and $idNotaCualitativa != null and $nuevaCalificacion != null) {

            // $numberConstraint = new Assert\Positive();
            // $numberConstraint->message = "La calificación no es válida !!";

            // $validate_nota = $this->get('validator')->validate($nuevaCalificacion, $numberConstraint);

            if (is_numeric($nuevaCalificacion) and $nuevaCalificacion > 0 and $nuevaCalificacion <= 100) {
                $em = $this->getDoctrine()->getManager();
                try {

                    $notaCuantitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idNotaCualitativa);
                    $notaCuantitativa->setNotaCuantitativa($nuevaCalificacion);
                    $em->persist($notaCuantitativa);
                    $em->flush();

                    $bthEstudianteNivelacion = $em->getRepository('SieAppWebBundle:BthEstudianteNivelacion')->find($idNivelacion);
                    $bthEstudianteNivelacion->setObs($obs);
                    $em->persist($bthEstudianteNivelacion);
                    $em->flush();

                    $data = array(
                        "status"=>"success",
                        "code"=>200,
                        "msg"=>"Calificación modificada exitosamente !!!"
                    );

                } catch (Exception $e) {
                    $data = array(
                        "status"=>"error",
                        "code"=>400,
                        "msg"=>"La calificación no pudo ser modificada !!!"
                    );
                }
            }else{
                $data = array(
                    "status"=>"error",
                    "code"=>400,
                    "msg"=>"La calificación no es válida, debe registrar una calificación entre 1 y 100 !!!"
                );
            }
        }else{
            $data = array(
                "status"=>"error",
                "code"=>400,
                "msg"=>"Debe completar la información !!!"
            );
        }

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }
}
