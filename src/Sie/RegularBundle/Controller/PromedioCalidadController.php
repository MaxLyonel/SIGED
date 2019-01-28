<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;

/**
 * EstudianteInscripcion controller.
 *
 */
class PromedioCalidadController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
    }

    

    /**
     * Datos del estudiante y de sus materias fisica quimica
     */
    public function indexAction(Request $request) {
        $form = $request->get('form');
        $idObservacion = $form['idDetalle'];
        $sie = $form['institucioneducativa'];
        $gestion = $form['gestion'];
        $llave = $form['llave'];
        $parametros = explode('|', $llave);

        $idInscripcion = $parametros[0];
        $idNotaTipo = $parametros[1];
        $idNota = $parametros[2];
        $promedio = $parametros[3];

        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $nota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($idNota);

        $notas = $em->createQueryBuilder()
                    ->select('at.id as codigo, at.asignatura, en.id, en.notaCuantitativa')
                    ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                    ->innerJoin('SieAppWebBundle:EstudianteNota','en','with','en.estudianteAsignatura = ea.id')
                    ->innerJoin('SieAppWebBundle:NotaTipo','nt','with','en.notaTipo = nt.id')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','with','ea.institucioneducativaCursoOferta = ieco.id')
                    ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','with','ieco.asignaturaTipo = at.id')
                    ->where('ea.estudianteInscripcion = :idInscripcion')
                    ->andWhere('at.id IN (:ids)')
                    ->andWhere('nt.id = :idNotaTipo')
                    ->setParameter('idInscripcion', $idInscripcion)
                    ->setParameter('ids', array(1051, 1052, 1053))
                    ->setParameter('idNotaTipo', $idNotaTipo)
                    ->orderBy('at.id','DESC')
                    ->getQuery()
                    ->getResult();

        $promedioCalculado = 0;
        $suma = 0;
        foreach ($notas as $n) {
            if($n['codigo'] != 1051){
                $suma = $suma + $n['notaCuantitativa'];
            }
        }

        $observacion = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($idObservacion);


        $promedioCalculado = round($suma/2);

        return $this->render('SieRegularBundle:PromedioCalidad:index.html.twig', [
            'inscripcion'=>$inscripcion,
            'nota'=>$nota,
            'notas'=>$notas,
            'promedio'=>$promedioCalculado,
            'idObservacion'=>$idObservacion,
            'observacion'=>$observacion,
            'bimestre'=>$idNotaTipo,
            'sie'=>$sie,
            'gestion'=>$gestion
        ]);

    }

    /*
     * Save cambios 
     */
    public function saveAction(Request $request) {
        $idNota = $request->get('idNota');
        $promedio = $request->get('promedio');
        $acuerdo = $request->get('acuerdo');

        $em = $this->getDoctrine()->getManager();

        $idObservacion = $request->get('idObservacion');
        $observacion = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($idObservacion);
        $observacionAnterior = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($idObservacion);
        
        if($acuerdo == "1"){
            $nota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find(intval($idNota));
            $nota->setNotaCuantitativa($promedio);
            $em->flush();

            $observacion->setEsActivo(true);
            $em->flush();

        }else{
            $observacion->setSolucionTipoId(1);
            $em->flush();
        }

        /**
         * Registro de log transaccion
         */
        $this->get('funciones')->setLogTransaccion(
            $observacion->getId(),
            'validacion_proceso',
            'U',
            'Update Observación de Promedio FISICA-QUIMICA',
            $observacion,
            $observacionAnterior,
            'SIGED',
            json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
        );

        $message = 'Se realizó la validación satisfactoriamente para la observación: ' . $observacion->getObs();
        $this->addFlash('success', $message);
        return $this->redirect($this->generateUrl('ccalidad_list', array(
            'id' => $observacion->getValidacionReglaTipo()->getValidacionReglaEntidadTipo()->getId(),
            'gestion' => $observacion->getGestionTipo()->getId()
        )));
    }


    public function listAction(Request $request) {

        $id_usuario = $this->session->get('userId');
        $gestion = 2018;//$this->session->get('currentyear');
        $id = 27;

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // redireccionamos si se excedio la fecha de corte del modulo
        // $fechaActual = new \DateTime('now');
        // $fechaCorte = new \DateTime('2018-11-24');

        // if($fechaActual > $fechaCorte) {
        //     return $this->redirect($this->generateUrl('principal_web'));
        // }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '9') {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $em = $this->getDoctrine()->getManager();

        $regla = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($id);

        $roluserlugarid = $this->session->get('roluserlugarid');
        
        $usuario_lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($roluserlugarid);

        $repository = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo');

        $query = $repository->createQueryBuilder('vrt')
            ->select('vp')->distinct()
            ->innerJoin('SieAppWebBundle:ValidacionProceso', 'vp', 'WITH', 'vrt.id = vp.validacionReglaTipo')
            ->where('vrt.id = :reglaTipo')
            ->andWhere('vp.esActivo = :esactivo')
            ->andWhere('vp.institucionEducativaId = :sie')
            ->andWhere('vp.gestionTipo = :gestion')
            ->setParameter('reglaTipo', $id)
            ->setParameter('esactivo', 'f')
            ->setParameter('sie', $this->session->get('ie_id'))
            ->setParameter('gestion', $gestion)
            ->addOrderBy('vp.gestionTipo', 'desc')
            ->getQuery();
        $lista_detalle = $query->getResult();

        $data = [];
        foreach ($lista_detalle as $ld) {
            $llave = $ld->getLlave();
            $parametros = explode('|', $llave);

            $idInscripcion = $parametros[0];
            $idNotaTipo = $parametros[1];
            $idNota = $parametros[2];
            $promedio = $parametros[3];

            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $nota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($idNota);

            $notas = $em->createQueryBuilder()
                        ->select('at.id as codigo, at.asignatura, en.id, en.notaCuantitativa')
                        ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                        ->innerJoin('SieAppWebBundle:EstudianteNota','en','with','en.estudianteAsignatura = ea.id')
                        ->innerJoin('SieAppWebBundle:NotaTipo','nt','with','en.notaTipo = nt.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','with','ea.institucioneducativaCursoOferta = ieco.id')
                        ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','with','ieco.asignaturaTipo = at.id')
                        ->where('ea.estudianteInscripcion = :idInscripcion')
                        ->andWhere('at.id IN (:ids)')
                        ->andWhere('nt.id = :idNotaTipo')
                        ->setParameter('idInscripcion', $idInscripcion)
                        ->setParameter('ids', array(1051, 1052, 1053))
                        ->setParameter('idNotaTipo', $idNotaTipo)
                        ->setMaxResults(5)
                        ->orderBy('at.id','DESC')
                        ->getQuery()
                        ->getResult();

            $promedioCalculado = 0;
            $suma = 0;
            foreach ($notas as $n) {
                if($n['codigo'] != 1051){
                    $suma = $suma + $n['notaCuantitativa'];
                }
            }
            $promedioCalculado = round($suma/2);

            $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo);

            $data[] = array(
                'idValidacion'=>$ld->getId(),
                'rude'=>$inscripcion->getEstudiante()->getCodigoRude(),
                'estudiante'=>$inscripcion->getEstudiante()->getNombre().' '.$inscripcion->getEstudiante()->getPaterno(),
                'nivel'=>$inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(),
                'grado'=>$inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(),
                'paralelo'=>$inscripcion->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(),
                'turno'=>$inscripcion->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno(),
                'notas'=>$notas,
                'idNota'=>$idNota,
                'promedio'=>$promedioCalculado,
                'notaTipo'=>$notaTipo
            );
        }

        return $this->render('SieRegularBundle:PromedioCalidad:lista.html.twig', array(
            'listado' => $data,
            'regla' => $regla,
            'gestion' => $gestion
        ));
    }

    /*
     * Save cambios 
     */
    public function saveListAction(Request $request) {
        $idValidacion = $request->get('idValidacion');
        $idNota = $request->get('idNota');
        $promedio = $request->get('promedio');
        $acuerdo = $request->get('acuerdo');

        $em = $this->getDoctrine()->getManager();

        for ($i=0; $i < count($idValidacion); $i++) { 
            $validacion = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($idValidacion[$i]);
            $validacionAnterior = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($idValidacion[$i]);
            if($acuerdo[$idValidacion[$i]] == "1"){
                $nota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find(intval($idNota[$i]));
                $nota->setNotaCuantitativa($promedio[$i]);
                $em->flush();

                // CALCULAMOS EL PROMEDIO
                $this->get('notas')->calcularPromedioBimestral($nota->getEstudianteAsignatura()->getId());

                // ACTUALIZAMOS EL ESTADO DE MATRICULA SI CORRESPONDE
                $this->get('notas')->actualizarEstadoMatricula($nota->getEstudianteAsignatura()->getEstudianteInscripcion()->getId());

                $validacion->setEsActivo(true);
                $em->flush();

            }else{
                $validacion->setSolucionTipoId(1);
                $validacion->setEsActivo(true);
                $em->flush();
            }
            /**
             * Registro de log transaccion
             */
            $this->get('funciones')->setLogTransaccion(
                $validacion->getId(),
                'validacion_proceso',
                'U',
                'Update Observación de Promedio FISICA-QUIMICA',
                $validacion,
                $validacionAnterior,
                'SIGED',
                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );
        }

        $message = 'Se realizó la validación satisfactoriamente, aquellos casos que fueron marcados con la opcion no debe solucionarlos';
        $this->addFlash('success', $message);
        return $this->redirect($this->generateUrl('promedio_calidad_lista'));
    }
}
