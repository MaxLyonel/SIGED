<?php

namespace Sie\DgesttlaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\TtecMateriaTipo;

/**
 * Institucioneducativa Controller.
 *
 */
class MallaCurricularController extends Controller {

      public $session;
      /**
       * the class constructor
       */
      public function __construct() {
          //init the session values
          $this->session = new Session();
      }

    public function indexAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $ieducativa_id = $request->getSession()->get('idInstitucion');
        $gestion_id = $request->getSession()->get('idGestion');
        $carrera_id = $request->getSession()->get('idCarrera');
        
        $denominacion_id = $request->getSession()->get('idDenominacion');

        $query = $em->getConnection()->prepare('        
        select a.institucioneducativa_id, c.institucioneducativa, i.regimen_estudio,
        a.ttec_carrera_tipo_id, b.nombre as nombre_carrera
        ,d.id as denominacion_id,d.denominacion, e.id as pensum_id, e.pensum, e.resolucion_administrativa, e.nro_resolucion
        ,g.id as periodo_id,g.periodo, f.id as materia_id, f.codigo as codigo_materia, f.materia as asignatura        
        from ttec_institucioneducativa_carrera_autorizada a        
            inner join ttec_resolucion_carrera h on h.ttec_institucioneducativa_carrera_autorizada_id = a.id
            inner join ttec_regimen_estudio_tipo i on i.id = h.ttec_regimen_estudio_tipo_id            
            inner join ttec_carrera_tipo b on b.id=a.ttec_carrera_tipo_id
                inner join institucioneducativa c on a.institucioneducativa_id=c.id
                    inner join ttec_denominacion_titulo_profesional_tipo d on a.ttec_carrera_tipo_id=d.ttec_carrera_tipo_id
                        inner join ttec_pensum e on e.ttec_denominacion_titulo_profesional_tipo_id=d.id
                            inner join ttec_materia_tipo f on e.id=f.ttec_pensum_id
                                inner join ttec_periodo_tipo g on f.ttec_periodo_tipo_id=g.id                                
        where a.institucioneducativa_id = :idInstitucion and a.ttec_carrera_tipo_id = :idCarrera and d.id = :idDenominacion  ');

        $query->bindValue(':idInstitucion', $ieducativa_id);
        $query->bindValue(':idCarrera', $carrera_id);
        $query->bindValue(':idDenominacion', $denominacion_id);
        $query->execute();
        $materias = $query->fetchAll();

        $query = $em->getConnection()->prepare('        
        select e.id as pensum_id, a.institucioneducativa_id,c.institucioneducativa,a.ttec_carrera_tipo_id,b.nombre as nombre_carrera,d.denominacion,e.pensum,e.resolucion_administrativa,e.nro_resolucion
        from ttec_institucioneducativa_carrera_autorizada a
            inner join ttec_carrera_tipo b on b.id=a.ttec_carrera_tipo_id
                inner join institucioneducativa c on a.institucioneducativa_id=c.id
                    inner join ttec_denominacion_titulo_profesional_tipo d on a.ttec_carrera_tipo_id=d.ttec_carrera_tipo_id
                        inner join ttec_pensum e on e.ttec_denominacion_titulo_profesional_tipo_id=d.id
        where a.institucioneducativa_id = :idInstitucion
        and b.id = :idCarrera;');

        $query->bindValue(':idInstitucion', $ieducativa_id);
        $query->bindValue(':idCarrera', $carrera_id);
        //$query->bindValue(':idDenominacion', $denominacion_id);
        $query->execute();
        $pensum = $query->fetchAll();

        $query = $em->getConnection()->prepare('select * from ttec_periodo_tipo');

        //$query->bindValue(':idInstitucion', $ieducativa_id);
        //$query->bindValue(':idCarrera', $carrera_id);
        //$query->bindValue(':idDenominacion', $denominacion_id);
        $query->execute();
        $periodo = $query->fetchAll();
     
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ieducativa_id);

        return $this->render($this->session->get('pathSystem') . ':MallaCurricular:index.html.twig', array(
            'institucion' => $institucion,
            'gestion' => $gestion_id,
            'pensum' => $pensum,
            'periodo' => $periodo,
            'denominacion' => $denominacion_id,
            'materias' => $materias
        ));

    }    

    public function addasignaturaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $ieducativa_id = $request->getSession()->get('idInstitucion');
        $gestion_id = $request->getSession()->get('idGestion');
        $carrera_id = $request->getSession()->get('idCarrera');        
        $denominacion_id = $request->getSession()->get('idDenominacion');

        $periodoid = $request->get('periodoid');
        $codigo = $request->get('codigo');
        $asignatura = $request->get('asignatura');
        $pensumid = $request->get('pensumid');

        $response = new JsonResponse(); 

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {            
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_materia_tipo');")->execute();
            $materia = new TtecMateriaTipo();
            $materia->setTtecPeriodoTipo($em->getRepository('SieAppWebBundle:TtecPeriodoTipo')->find($periodoid));
            $materia->setCodigo($codigo);
            $materia->setMateria($asignatura);            
            $materia->setTtecPensum($em->getRepository('SieAppWebBundle:TtecPensum')->find($pensumid));                

            $em->persist($materia);
            $em->flush();

           $em->getConnection()->commit();
           return $response->setData(array('mensaje'=>'¡Proceso realizado exitosamente!'));           
            
        } catch (Exception $ex) {
            $em->getConnection()->rollback(); 
            return $response->setData(array('mensaje'=>'Proceso detenido.'));
        }
    }   

    /*public listmaterias(){

    }*/
}
