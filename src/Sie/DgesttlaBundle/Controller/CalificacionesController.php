<?php

namespace Sie\DgesttlaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\TtecDocentePersona;
use Sie\AppWebBundle\Entity\TtecDocenteMateria;
use GuzzleHttp\Client;
use Sie\AppWebBundle\Entity\TtecEstudianteNota;

/**
 * EstudianteInscripcion controller.
 *
 */
class CalificacionesController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * list de materias
     *
     */
    public function indexAction(Request $request) {
        try {


            $em = $this->getDoctrine()->getManager();
            $personaId = 64116;//$this->session->get('personaId');
            
            /* Buscar materias vigentes del docente */
            $materias = $em->createQueryBuilder()
                        ->select('tdm')
                        ->from('SieAppWebBundle:TtecDocentePersona','tdp')
                        ->innerJoin('SieAppWebBundle:TtecDocenteMateria','tdm','with','tdm.ttecDocentePersona = tdp.id')
                        ->where('tdp.persona = :personaId')
                        ->andWhere('tdm.esVigente = true')
                        ->setParameter('personaId',$personaId)
                        ->getQuery()
                        ->getResult();
            
            return $this->render('SieDgesttlaBundle:Calificaciones:index.html.twig', array('materias'=>$materias));

        } catch (Exception $e) {
            
        }
    }

    /**
     * Lista de estudiantes inscritos
     */
    public function listStudentsAction(Request $request) {
        try {
            $paraleloMateriaId = $request->get('paraleloMateriaId');
            $em = $this->getDoctrine()->getManager();
            //$estudiantes = $em->getRepository('SieAppWebBundle:TtecEstudianteInscripcion')->findBy(array('ttecParaleloMateria'=>$paraleloMateriaId), array('persona'=>'ASC'));
            $paraleloMateria = $em->getRepository('SieAppWebBundle:TtecParaleloMateria')->find($paraleloMateriaId);
            $estudiantes = $em->createQueryBuilder()
                                ->select('tei')
                                ->from('SieAppWebBundle:TtecEstudianteInscripcion','tei')
                                ->innerJoin('SieAppWebBundle:Persona','p','with','tei.persona = p.id')
                                ->where('tei.ttecParaleloMateria = :paraleloMateriaId')
                                ->orderBy('p.paterno, p.materno','ASC')
                                ->setParameter('paraleloMateriaId', $paraleloMateriaId)
                                ->getQuery()
                                ->getResult();

            $arrayNotas = array();
            $cont = 0;
            foreach ($estudiantes as $e) {
                $arrayNotas[$cont] = array(
                    'estudianteInscripcionId'=>$e->getId(),
                    'carnet'=>$e->getPersona()->getCarnet(),
                    'paterno'=>$e->getPersona()->getPaterno(),
                    'materno'=>$e->getPersona()->getMaterno(),
                    'nombre'=>$e->getPersona()->getNombre(),
                    'estadoMatricula'=>$e->getEstadomatriculaTipoFin()
                );
                $nota = $em->getRepository('SieAppWebBundle:TtecEstudianteNota')->findOneBy(array('ttecEstudianteInscripcion'=>$e->getId()));
                if($nota){
                    $arrayNotas[$cont]['notas'][] = array(
                        'estudianteNotaId'=>$nota->getId(),
                        'notaCuantitativa'=>$nota->getNotaCuantitativa(),
                        'notaCualitativa'=>$nota->getNotaCualitativa()
                    );
                }else{
                    $arrayNotas[$cont]['notas'][] = array(
                        'estudianteNotaId'=>'new',
                        'notaCuantitativa'=>'',
                        'notaCualitativa'=>''
                    );
                }
                $cont++;
            }

            //dump($arrayNotas);die;

            return $this->render('SieDgesttlaBundle:Calificaciones:listStudents.html.twig', array('inscritos'=>$arrayNotas,'paralelo'=>$paraleloMateria));

        } catch (Exception $e) {
            
        }
    }

    /**
     * Registro de calificaciones
     */
    public function createUpdateAction(Request $request) {
        try {
            $estudianteInscripcionId = $request->get('estudianteInscripcionId');
            $estudianteNotaId = $request->get('estudianteNotaId');
            $nota = $request->get('nota');
            /*dump($estudianteInscripcionId);
            dump($estudianteNotaId);
            dump($nota);
            //die;*/
            $em = $this->getDoctrine()->getManager();
            for ($i=0; $i < count($estudianteNotaId); $i++) {
                $estudianteInscripcion = $em->getRepository('SieAppWebBundle:TtecEstudianteInscripcion')->find($estudianteInscripcionId[$i]);
                if($estudianteNotaId[$i] == 'new' and $nota[$i] != ""){
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_estudiante_nota');")->execute();
                    $newNota = new TtecEstudianteNota();
                    $newNota->setTtecEstudianteInscripcion($em->getRepository('SieAppWebBundle:TtecEstudianteInscripcion')->find($estudianteInscripcionId[$i]));
                    $newNota->setNotaCuantitativa($nota[$i]);
                    $newNota->setNotaCualitativa('');
                    $newNota->setFechaRegistro(new \DateTime('now'));
                    $newNota->setFechaModificacion(new \DateTime('now'));
                    $em->persist($newNota);
                    $em->flush();

                    if($nota[$i] >= 51){
                        $estudianteInscripcion->setEstadomatriculaTipoFin($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(5));
                    }else{
                        $estudianteInscripcion->setEstadomatriculaTipoFin($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(11));
                    }
                }else{
                    if($nota[$i] == ""){ $nota[$i] = 0; }
                    $notaUpdate = $em->getRepository('SieAppWebBundle:TtecEstudianteNota')->find($estudianteNotaId[$i]);
                    if($notaUpdate){
                        $notaUpdate->setNotaCuantitativa($nota[$i]);
                        $notaUpdate->setFechaModificacion(new \DateTime('now'));
                        $em->flush();

                        if($nota[$i] >= 51){
                            $estudianteInscripcion->setEstadomatriculaTipoFin($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(5));
                        }else{
                            $estudianteInscripcion->setEstadomatriculaTipoFin($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(11));
                        }
                    }
                }
            }
            $this->session->getFlashBag()->add('messaje', 'Registro de calificaciones realizada correctamente');
            return $this->redirectToRoute('dgesttla_calificaciones_index');
        } catch (Exception $e) {
            
        }
    }
}
