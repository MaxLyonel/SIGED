<?php

namespace Sie\DgesttlaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\TtecDocentePersona;
use Sie\AppWebBundle\Entity\TtecDocenteMateria;
use GuzzleHttp\Client;

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
            $personaId = $this->session->get('personaId');
            //dump($personaId);die;
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
            $estudiantes = $em->getRepository('SieAppWebBundle:TtecEstudianteInscripcion')->findBy(array('ttecParaleloMateria'=>$paraleloMateriaId), array('persona'=>'ASC'));

            $arrayNotas = array();
            $cont = 0;
            foreach ($estudiantes as $e) {
                $arrayNotas[$cont] = array(
                    'estudianteInscripcionId'=>$e->getId(),
                    'paterno'=>$e->getPersona()->getPaterno(),
                    'materno'=>$e->getPersona()->getMaterno(),
                    'nombre'=>$e->getPersona()->getNombre()
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

            return $this->render('SieDgesttlaBundle:Calificaciones:listStudents.html.twig', array('inscritos'=>$arrayNotas));

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

            for ($i=0; $i < count($estudianteNotaId); $i++) { 
                if($estudianteNotaId[$i] == 'new'){
                    $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_estudiante_nota');")->execute();
                    $newNota = new TtecEstudianteNota();
                    $newNota->setTtecEstudinateInscripcion($em->getRepository('SieAppWebBundle:TtecEstudianteInscripcion')->find($estudianteInscripcionId[$i]));
                    $newNota->setNotaCuantitativa($nota[$i]);
                    $newNota->setNotaCualitativa('');
                    $newNota->setFechaRegistro(new \DateTime('now'));
                    $newNota->setFechaModificacion(new \DateTime('now'));
                    $em->persist($newNota);
                    $em->flush();
                }else{
                    $notaUpdate = $em->getRepository('SieAppWebBundle:TtecEstudianteNota')->find($estudianteNotaId);
                    $notaUpdate->setNotaCuantitativa($nota[$i]);
                    $newNota->setFechaModificacion(new \DateTime('now'));
                    $em->flush();
                }
            }

            dump($nota);die;
        } catch (Exception $e) {
            
        }
    }
}
