<?php

namespace Sie\DgesttlaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\TtecDocenteMateria;

/**
 * Institucioneducativa Controller.
 *
 */
class DocenteMateriaController extends Controller {

      public $session;
      /**
       * the class constructor
       */
      public function __construct() {
          //init the session values
          $this->session = new Session();
      }

    public function indexAction(Request $request) {
        
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $ieducativa_id = $request->getSession()->get('idInstitucion');
        $gestion_id = $request->getSession()->get('idGestion');
        $carrera_id = $request->getSession()->get('idCarrera');
        $denominacion_id = $request->getSession()->get('idDenominacion');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare('select a.institucioneducativa_id,c.institucioneducativa,a.ttec_carrera_tipo_id,b.nombre as nombre_carrera,d.denominacion,e.pensum,e.resolucion_administrativa,e.nro_resolucion
        ,g.periodo,f.codigo as codigo_materia,f.materia as asignatura,i.paralelo,j.turno,k.id codigo_doc_mat,k.es_vigente,o.carnet,o.paterno,o.materno,o.nombre
        from ttec_institucioneducativa_carrera_autorizada a
            inner join ttec_carrera_tipo b on b.id=a.ttec_carrera_tipo_id
                inner join institucioneducativa c on a.institucioneducativa_id=c.id
                    inner join ttec_denominacion_titulo_profesional_tipo d on a.ttec_carrera_tipo_id=d.ttec_carrera_tipo_id
                        inner join ttec_pensum e on e.ttec_denominacion_titulo_profesional_tipo_id=d.id
                            inner join ttec_materia_tipo f on e.id=f.ttec_pensum_id
                                inner join ttec_periodo_tipo g on f.ttec_periodo_tipo_id=g.id
                                    inner join ttec_paralelo_materia h on h.ttec_materia_tipo_id=f.id
                                        inner join ttec_paralelo_tipo i on h.ttec_paralelo_tipo_id=i.id
                                            inner join turno_tipo j on h.turno_tipo_id=j.id
                                                inner join ttec_docente_materia k on k.ttec_paralelo_materia_id=h.id
                                                    inner join ttec_docente_persona n on k.ttec_docente_persona_id=n.id
                                                        inner join persona o on n.persona_id=o.id
        where a.institucioneducativa_id= :idInstitucion and b.id = :idCarrera
        order by pensum, turno, periodo, codigo_materia, paralelo;');

        $query->bindValue(':idInstitucion', $ieducativa_id);
        $query->bindValue(':idCarrera', $carrera_id);
        $query->execute();
        $docentemateria = $query->fetchAll();
        
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ieducativa_id);

        return $this->render($this->session->get('pathSystem') . ':DocenteMateria:index.html.twig', array(
            'institucion' => $institucion,
            'gestion' => $gestion_id,
            'docentes' => $docentemateria,
            'denominacion' => $denominacion_id
        ));
    }    

    /*
     * Llamamos al formulario para nuevo operativo/calendario
     */

    public function newAction(Request $request) {

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

        $form = $request->get('form');
        
        $ieducativa_id = $form['idInstitucion'];
        $gestion_id = $form['idGestion'];
        $denominacion_id = $form['idDenominacion'];
        $carrera_id = $request->getSession()->get('idCarrera');

        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ieducativa_id);
        $denominacion = $em->getRepository('SieAppWebBundle:TtecDenominacionTituloProfesionalTipo')->findOneById($denominacion_id);
        $carrera = $em->getRepository('SieAppWebBundle:TtecCarreraTipo')->findOneById($carrera_id);

        return $this->render($this->session->get('pathSystem') . ':DocenteMateria:new.html.twig', array(
                    'form' => $this->newParaleloForm($ieducativa_id, $gestion_id, $denominacion_id)->createView(),
                    'institucion' => $institucion,
                    'denominacion' => $denominacion,
                    'carrera' => $carrera,
                    'gestion' => $gestion_id
        ));
    }

     /*
     * formulario de nueva/o operativo
     */
    private function newParaleloForm($idInstitucion, $idGestion, $idDenominacion) {
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->createQuery(
            'SELECT a FROM SieAppWebBundle:TurnoTipo a
            WHERE a.id NOT IN (:id) ORDER BY a.id')
            ->setParameter('id', array(0));

        $turno = $query->getResult();
        
        $turnoArray = array();
        foreach ($turno as $value) {
            $turnoArray[$value->getId()] = $value->getTurno();
        }

        $query = $em->createQuery(
            'SELECT a FROM SieAppWebBundle:TtecPeriodoTipo a
            WHERE a.id NOT IN (:id) ORDER BY a.id')
            ->setParameter('id', array(0,7,8,9));

        $periodo = $query->getResult();
        
        $periodoArray = array();
        foreach ($periodo as $value) {
            $periodoArray[$value->getId()] = $value->getPeriodo();
        }

        $query = $em->createQuery(
            'SELECT a FROM SieAppWebBundle:TtecParaleloTipo a
            WHERE a.id IN (:id) ORDER BY a.id')
            ->setParameter('id', array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15));

        $paralelo = $query->getResult();
        
        $paraleloArray = array();
        foreach ($paralelo as $value) {
            $paraleloArray[$value->getId()] = $value->getParalelo();
        }

        $query = $em->getConnection()->prepare('select f.id,f.codigo as codigo_materia,f.materia as asignatura
        from ttec_institucioneducativa_carrera_autorizada a
            inner join ttec_carrera_tipo b on b.id=a.ttec_carrera_tipo_id
                inner join institucioneducativa c on a.institucioneducativa_id=c.id
                    inner join ttec_denominacion_titulo_profesional_tipo d on a.ttec_carrera_tipo_id=d.ttec_carrera_tipo_id
                        inner join ttec_pensum e on e.ttec_denominacion_titulo_profesional_tipo_id=d.id
                            inner join ttec_materia_tipo f on e.id=f.ttec_pensum_id
                                inner join ttec_periodo_tipo g on f.ttec_periodo_tipo_id=g.id
        where d.id = :idDenominacion
        order by f.id;');

        $query->bindValue(':idDenominacion', $idDenominacion);
        $query->execute();
        $materia = $query->fetchAll();

        $materiaArray = array();
        foreach ($materia as $value) {
            $materiaArray[$value['id']] = $value['codigo_materia'].' / '.$value['asignatura'];
        }

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('dgesttla_carrera_docente_materia_create'))
            ->add('idInstitucion', 'hidden', array('data' => $idInstitucion))
            ->add('idGestion', 'hidden', array('data' => $idGestion))
            ->add('idDenominacion', 'hidden', array('data' => $idDenominacion))
            ->add('periodo', 'choice', array('label' => 'Periodo:', 'required' => true, 'choices' => $periodoArray, 'attr' => array('class' => 'form-control')))
            ->add('turno', 'choice', array('label' => 'Turno:', 'required' => true, 'choices' => $turnoArray, 'attr' => array('class' => 'form-control')))
            ->add('materia', 'choice', array('label' => 'Materia:', 'required' => true, 'choices' => $materiaArray, 'attr' => array('class' => 'form-control')))
            ->add('paralelo', 'choice', array('label' => 'Paralelo:', 'required' => true, 'choices' => $paraleloArray, 'attr' => array('class' => 'form-control')))
            ->add('cupo', 'text', array('label' => 'Cupo:', 'required' => true, 'data' => '0', 'attr' => array('class' => 'form-control')))
            ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();
            
        return $form;
    }

    /*
     * registramos el nuevo operativo
     */
    public function createAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');
                                        
            $paralelo = $em->getRepository('SieAppWebBundle:TtecParaleloMateria')->findBy(array('ttecPeriodoTipo' => $form['periodo'], 'turnoTipo' => $form['turno'], 'ttecMateriaTipo' => $form['materia'], 'ttecParaleloTipo' => $form['paralelo']));
            
            if ($paralelo) {
                $this->get('session')->getFlashBag()->add('newError', 'No se realizó el registro, el paralelo ya se encuentra registrado.');
                return $this->redirect($this->generateUrl('dgesttla_carrera_docente_materia'));
            }

            // Registro paralelo
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_paralelo_materia');")->execute();
            $paraleloNew = new TtecParaleloMateria();
            $paraleloNew->setTtecMateriaTipo($em->getRepository('SieAppWebBundle:TtecMateriaTipo')->findOneById($form['materia']));
            $paraleloNew->setTtecParaleloTipo($em->getRepository('SieAppWebBundle:TtecParaleloTipo')->findOneById($form['paralelo']));
            $paraleloNew->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneById($form['turno']));
            $paraleloNew->setTtecPeriodoTipo($em->getRepository('SieAppWebBundle:TtecPeriodoTipo')->findOneById($form['periodo']));
            $paraleloNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($form['idGestion']));            
            $paraleloNew->setCupo(intval($form['cupo']));
            $paraleloNew->setFechaRegistro(new \DateTime('now'));
            $em->persist($paraleloNew);
            $em->flush();
            
            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente.');
            return $this->redirect($this->generateUrl('dgesttla_carrera_docente_materia'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * Llamar al formulario de edicion
     */
    public function editAction(Request $request) {
        
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

        $form = $request->get('formE');

        $ieducativa_id = $form['idInstitucion'];
        $gestion_id = $form['idGestion'];
        $denominacion_id = $form['idDenominacion'];
        $paralelo_id = $form['idParalelo'];
        $carrera_id = $request->getSession()->get('idCarrera');

        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ieducativa_id);
        $denominacion = $em->getRepository('SieAppWebBundle:TtecDenominacionTituloProfesionalTipo')->findOneById($denominacion_id);
        $carrera = $em->getRepository('SieAppWebBundle:TtecCarreraTipo')->findOneById($carrera_id);

        return $this->render($this->session->get('pathSystem') . ':DocenteMateria:edit.html.twig', array(
                    'form' => $this->editPensumForm($ieducativa_id, $gestion_id, $paralelo_id, $denominacion_id)->createView(),
                    'institucion' => $institucion,
                    'denominacion' => $denominacion,
                    'carrera' => $carrera,
                    'gestion' => $gestion_id
        ));
    }

    /*
    * formulario de edicion
    */
    private function editPensumForm($idInstitucion, $idGestion, $idParalelo, $idDenominacion) {
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->createQuery(
            'SELECT a FROM SieAppWebBundle:TurnoTipo a
            WHERE a.id NOT IN (:id) ORDER BY a.id')
            ->setParameter('id', array(0));

        $turno = $query->getResult();
        
        $turnoArray = array();
        foreach ($turno as $value) {
            $turnoArray[$value->getId()] = $value->getTurno();
        }

        $query = $em->createQuery(
            'SELECT a FROM SieAppWebBundle:TtecPeriodoTipo a
            WHERE a.id NOT IN (:id) ORDER BY a.id')
            ->setParameter('id', array(0,7,8,9));

        $periodo = $query->getResult();
        
        $periodoArray = array();
        foreach ($periodo as $value) {
            $periodoArray[$value->getId()] = $value->getPeriodo();
        }

        $query = $em->createQuery(
            'SELECT a FROM SieAppWebBundle:TtecParaleloTipo a
            WHERE a.id IN (:id) ORDER BY a.id')
            ->setParameter('id', array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15));

        $paralelo = $query->getResult();
        
        $paraleloArray = array();
        foreach ($paralelo as $value) {
            $paraleloArray[$value->getId()] = $value->getParalelo();
        }

        $query = $em->getConnection()->prepare('select f.id,f.codigo as codigo_materia,f.materia as asignatura
        from ttec_institucioneducativa_carrera_autorizada a
            inner join ttec_carrera_tipo b on b.id=a.ttec_carrera_tipo_id
                inner join institucioneducativa c on a.institucioneducativa_id=c.id
                    inner join ttec_denominacion_titulo_profesional_tipo d on a.ttec_carrera_tipo_id=d.ttec_carrera_tipo_id
                        inner join ttec_pensum e on e.ttec_denominacion_titulo_profesional_tipo_id=d.id
                            inner join ttec_materia_tipo f on e.id=f.ttec_pensum_id
                                inner join ttec_periodo_tipo g on f.ttec_periodo_tipo_id=g.id
        where d.id = :idDenominacion
        order by f.id;');

        $query->bindValue(':idDenominacion', $idDenominacion);
        $query->execute();
        $materia = $query->fetchAll();

        $materiaArray = array();
        foreach ($materia as $value) {
            $materiaArray[$value['id']] = $value['codigo_materia'].' / '.$value['asignatura'];
        }

        $paraleloMateria = $em->getRepository('SieAppWebBundle:TtecParaleloMateria')->findOneById($idParalelo);

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('dgesttla_carrera_docente_materia_update'))
            ->add('idInstitucion', 'hidden', array('data' => $idInstitucion))
            ->add('idGestion', 'hidden', array('data' => $idGestion))
            ->add('idDenominacion', 'hidden', array('data' => $idDenominacion))
            ->add('idParalelo', 'hidden', array('data' => $paraleloMateria->getId()))
            ->add('periodo', 'choice', array('label' => 'Periodo:', 'required' => true, 'choices' => $periodoArray, 'data' => $paraleloMateria->getTtecPeriodoTipo()->getId(), 'attr' => array('class' => 'form-control')))
            ->add('turno', 'choice', array('label' => 'Turno:', 'required' => true, 'choices' => $turnoArray, 'data' => $paraleloMateria->getTurnoTipo()->getId(), 'attr' => array('class' => 'form-control')))
            ->add('materia', 'choice', array('label' => 'Materia:', 'required' => true, 'choices' => $materiaArray, 'data' => $paraleloMateria->getTtecMateriaTipo()->getId(), 'attr' => array('class' => 'form-control')))
            ->add('paralelo', 'choice', array('label' => 'Paralelo:', 'required' => true, 'choices' => $paraleloArray, 'data' => $paraleloMateria->getTtecParaleloTipo()->getId(), 'attr' => array('class' => 'form-control')))
            ->add('cupo', 'text', array('label' => 'Cupo:', 'required' => true, 'data' => $paraleloMateria->getCupo(), 'attr' => array('class' => 'form-control')))
            ->add('guardar', 'submit', array('label' => 'Guardar cambios', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        return $form;
    }

    /*
    * guardar datos de modificacion
    */
    public function updateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');
            
            // Actiualización pensum
            $paraleloEdit = $em->getRepository('SieAppWebBundle:TtecParaleloMateria')->findOneById($form['idParalelo']);
            $paraleloEdit->setTtecMateriaTipo($em->getRepository('SieAppWebBundle:TtecMateriaTipo')->findOneById($form['materia']));
            $paraleloEdit->setTtecParaleloTipo($em->getRepository('SieAppWebBundle:TtecParaleloTipo')->findOneById($form['paralelo']));
            $paraleloEdit->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneById($form['turno']));
            $paraleloEdit->setTtecPeriodoTipo($em->getRepository('SieAppWebBundle:TtecPeriodoTipo')->findOneById($form['periodo']));
            $paraleloEdit->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($form['idGestion']));
            $paraleloEdit->setCupo(intval($form['cupo']));
            $paraleloEdit->setFechaModificacion(new \DateTime('now'));
            $em->persist($paraleloEdit);
            $em->flush();
            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('updateOk', 'Datos modificados correctamente.');
            return $this->redirect($this->generateUrl('dgesttla_carrera_docente_materia'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la modificación de datos.');
            return $this->redirect($this->generateUrl('dgesttla_carrera_docente_materia'));
        }
    }

    /*
    * Eliminacion de operativo
    */
    public function deleteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('formD');
        
        $docenteMateriaDelete = $em->getRepository('SieAppWebBundle:TtecDocenteMateria')->findOneById($form['idDocMat']);
        
        $em->getConnection()->beginTransaction();
        try {
            //eliminamos el registro
            $em->remove($docenteMateriaDelete);
            $em->flush();
            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('eliminarOk', 'El registro fue eliminado exitosamente.');
            return $this->redirect($this->generateUrl('dgesttla_carrera_docente_materia'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('eliminarError', 'El registro no se pudo eliminar.');
            $em->getConnection()->rollback();
            return $this->redirect($this->generateUrl('dgesttla_carrera_docente_materia'));
        }
    }

    /*
    * guardar datos de modificacion
    */
    public function esVigenteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $form = $request->get('formV');
        
        try {           
            // Actiualización pensum
            $docenteMateriaVigente = $em->getRepository('SieAppWebBundle:TtecDocenteMateria')->findOneById($form['idDocMat']);
            $docenteMateriaVigente->setEsVigente(1 - $docenteMateriaVigente->getEsVigente());
            $em->persist($docenteMateriaVigente);
            $em->flush();
            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('updateOk', 'Datos modificados correctamente.');
            return $this->redirect($this->generateUrl('dgesttla_carrera_docente_materia'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la modificación de datos.');
            return $this->redirect($this->generateUrl('dgesttla_carrera_docente_materia'));
        }
    }

}
