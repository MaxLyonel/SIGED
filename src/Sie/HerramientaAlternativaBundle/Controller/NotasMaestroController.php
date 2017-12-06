<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;

/**
 * Notas maestro controller.
 */
class NotasMaestroController extends Controller {

    public $session;

    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //dump($this->session->get('ie_per_estado'));die;

        /*$sucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array(
                'sucursalTipo'=>$this->session->get('ie_suc_id'),//0
                'periodoTipoId'=>$this->session->get('ie_per_cod'),//2
                'gestionTipo'=>$this->session->get('ie_gestion'),//2016
                'institucioneducativa'=>$this->session->get('ie_id')
        ));*/
        $sucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($this->session->get('ie_suc_id'));
      if($sucursal){

          $asignaturas = $em->createQueryBuilder()
                            ->select('smt.modulo,
                                    ie.id as sie,
                                    ie.institucioneducativa,
                                    stt.turnoSuperior as turno,
                                    iec.id as idCurso,
                                    ieco.id as idCursoOferta,
                                    pt.paralelo,
                                    gt.gestion,
                                    sat.acreditacion,
                                    sespt.especialidad,
                                    st.id as sucursal,
                                    spt.periodoSuperior
                            ')
                            ->from('SieAppWebBundle:Persona','p')
                            ->innerJoin('SieAppWebBundle:MaestroInscripcion','mi','with','mi.persona = p.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal','ies','with','mi.institucioneducativaSucursal = ies.id')
                            ->innerJoin('SieAppWebBundle:SucursalTipo','st','with','ies.sucursalTipo = st.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro','iecom','with','iecom.maestroInscripcion = mi.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','with','iecom.institucioneducativaCursoOferta = ieco.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ieco.insitucioneducativaCurso = iec.id')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
                            ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                            ->innerJoin('SieAppWebBundle:TurnoTipo','tt','with','iec.turnoTipo = tt.id')
                            ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','with','iec.paraleloTipo = pt.id')
                            ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo','smp','with','ieco.superiorModuloPeriodo = smp.id')
                            ->innerJoin('SieAppWebBundle:SuperiorModuloTipo','smt','with','smp.superiorModuloTipo = smt.id')
                            ->innerJoin('SieAppWebBundle:SuperiorAreaSaberesTipo','sast','with','smt.superiorAreaSaberesTipo = sast.id')
                            ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo','siep','with','smp.institucioneducativaPeriodo = siep.id')
                            ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion','siea','with','siep.superiorInstitucioneducativaAcreditacion = siea.id')
                            ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad','sae','with','siea.acreditacionEspecialidad = sae.id')
                            ->innerJoin('SieAppWebBundle:SuperiorAcreditacionTipo','sat','with','sae.superiorAcreditacionTipo = sat.id')
                            ->innerJoin('SieAppWebBundle:SuperiorEspecialidadTipo','sespt','with','sae.superiorEspecialidadTipo = sespt.id')
                            ->innerJoin('SieAppWebBundle:SuperiorTurnoTipo','stt','with','siea.superiorTurnoTipo = stt.id')
                            ->innerJoin('SieAppWebBundle:SuperiorPeriodoTipo','spt','with','siep.superiorPeriodoTipo = spt.id')
                            ->where('p.id = :idPersona')
                            ->andWhere('ies.id = :idSucursal')
                            ->orderBy('sat.id','ASC')
                            ->addOrderBy('sespt.id','ASC')
                            ->setParameter('idPersona',$this->session->get('personaId'))
                            //->setParameter('idInstitucion',$this->session->get('ie_id'))
                            ->setParameter('idSucursal',$this->session->get('ie_suc_id'))
                            ->getQuery()
                            ->getResult();
        }else{
            $asignaturas = array();
        }
        //dump($asignaturas);die;
        //dump($this->session->get('personaId'));die;


        //dump($this->session->get('personaId'));
        //die;
        $em->getConnection()->commit();
        return $this->render('SieHerramientaAlternativaBundle:NotasMaestro:index.html.twig',array('asignaturas'=>$asignaturas));
    }

    public function newAction(Request $request){
        try {
            $idCursoOferta = $request->get('idCursoOferta');
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $curso = $em->createQueryBuilder()
                        ->select('supet.especialidad,sat.acreditacion,smt.modulo,stt.turnoSuperior, sast.areaSuperior, pt.paralelo, ie.id as sie, ie.institucioneducativa, gt.gestion,
                                spt.periodoSuperior, sfat.codigo as idNivel
                        ')
                        ->from('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ieco.insitucioneducativaCurso = iec.id')
                        ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
                        //->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
                        ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','with','iec.paraleloTipo = pt.id')
                        ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                        ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo','smp','with','ieco.superiorModuloPeriodo = smp.id')
                        ->innerJoin('SieAppWebBundle:SuperiorModuloTipo','smt','with','smp.superiorModuloTipo = smt.id')
                        ->innerJoin('SieAppWebBundle:SuperiorAreaSaberesTipo','sast','with','smt.superiorAreaSaberesTipo = sast.id')
                        ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo','siep','with','smp.institucioneducativaPeriodo = siep.id')
                        ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion','siea','with','siep.superiorInstitucioneducativaAcreditacion = siea.id')
                        ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad','sae','with','siea.acreditacionEspecialidad = sae.id')
                        ->innerJoin('SieAppWebBundle:SuperiorEspecialidadTipo','supet','with','sae.superiorEspecialidadTipo = supet.id')
                        ->innerJoin('SieAppWebBundle:SuperiorFacultadAreaTipo','sfat','with','supet.superiorFacultadAreaTipo = sfat.id')
                        ->innerJoin('SieAppWebBundle:SuperiorAcreditacionTipo','sat','with','sae.superiorAcreditacionTipo = sat.id')
                        ->innerJoin('SieAppWebBundle:SuperiorTurnoTipo','stt','with','siea.superiorTurnoTipo = stt.id')
                        ->innerJoin('SieAppWebBundle:SuperiorPeriodoTipo','spt','with','siep.superiorPeriodoTipo = spt.id')
                        ->where('ieco.id = :idCursoOferta')
                        ->setParameter('idCursoOferta',$idCursoOferta)
                        ->getQuery()
                        ->getResult();

            $estudiantes = $em->createQueryBuilder()
                              ->select('e.paterno, e.materno, e.nombre, ea.id as idEstudianteAsignatura, eae.id as idEstadoAsignatura')
                              ->from('SieAppWebBundle:Estudiante','e')
                              ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','ei.estudiante = e.id')
                              ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','with','ea.estudianteInscripcion = ei.id')
                              ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','with','ea.institucioneducativaCursoOferta = ieco.id')
                              ->innerJoin('SieAppWebBundle:EstudianteasignaturaEstado','eae','with','ea.estudianteasignaturaEstado = eae.id')
                              ->where('ieco.id = :idCursoOferta')
                              ->setParameter('idCursoOferta',$idCursoOferta)
                              ->orderBy('e.paterno','ASC')
                              ->addOrderBy('e.materno','ASC')
                              ->addOrderBy('e.nombre','ASC')
                              ->getQuery()
                              ->getResult();
             //dump($curso[0]['idNivel']);die;
             //dump($estudiantes);die;
             if($curso[0]['idNivel'] == 15){
                 $idNotaTipo = 26;
             }else{
                 $idNotaTipo = 22;
             }
             //dump($idNotaTipo);die;
            $arrayEst = array();
            $cont = 0;
            foreach ($estudiantes as $e) {
                // Verificamos si la materia tiene nota o no
                $datosNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$e['idEstudianteAsignatura'],'notaTipo'=>$idNotaTipo));
                /*$datosNota = $em->createQueryBuilder()
                                ->select('en')
                                ->from('SieAppWebBundle:EstudianteNota','en')
                                ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','with','en.estudianteAsignatura = ea.id')
                                ->innerJoin('SieAppWebBundle:NotaTipo','nt','with','en.notaTipo = nt.id')
                                ->where('ea.id = :idEstAsig')
                                ->andWhere('nt.id = :idNotaTipo')
                                ->setParameter('idEstAsig',$e['idEstudianteAsignatura'])
                                ->setParameter('idNotaTipo',$idNotaTipo)
                                ->getQuery()
                                //dump($datosNota->getQuery()->getSQL());die;
                                ->getResult();*/

                //dump($datosNota);die;
                if($datosNota){
                    $idNota = $datosNota->getId();
                    $nota = $datosNota->getNotaCuantitativa();
                    $idNotaTipo = $datosNota->getNotaTipo()->getId();
                }else{
                    $idNota = 'nuevo';
                    $nota = 0;
                }
                // Armamos el array con los datos del estudiante
                $arrayEst[$cont] = array(
                                        'paterno'=>$e['paterno'],
                                        'materno'=>$e['materno'],
                                        'nombre'=>$e['nombre'],
                                        'idEstudianteAsignatura'=>$e['idEstudianteAsignatura'],
                                        'idNota'=>$idNota,
                                        'nota'=>$nota,
                                        'idNotaTipo'=>$idNotaTipo,
                                        'idEstadoAsignatura'=>$e['idEstadoAsignatura']);
                $cont++;
            }
            //dump($arrayEst);die;
            // Obtenemos los estados de de las asignaturas
            $estadosAsignatura = $em->createQueryBuilder()
                                    ->select('eae')
                                    ->from('SieAppWebBundle:EstudianteasignaturaEstado','eae')
                                    ->getQuery()
                                    ->getResult();

            //dump('adsfadsf');die;
            $em->getConnection()->commit();
            return $this->render('SieHerramientaAlternativaBundle:NotasMaestro:notas.html.twig',array('curso'=>$curso,'estudiantes'=>$arrayEst,'estadosAsignatura'=>$estadosAsignatura));
        } catch (Exception $e) {

        }
    }

    public function createUpdateAction(Request $request){
        try {
            $idAsignatura = $request->get('idAsignatura');
            $idNota = $request->get('idNota');
            $idNotaTipo = $request->get('idNotaTipo');
            $notas = $request->get('notas');
            /*dump($idAsignatura);
            dump($idNota);
            dump($idNotaTipo);
            dump($notas);
            die;*/
            $em = $this->getDoctrine()->getManager();

            if(count($notas)>0){
                for($i=0;$i<count($notas);$i++){
                    // Acualizamos el estado de la asignatura
                    if($notas[$i] == 0){ $nuevoEstado = 3; }
                    if($notas[$i] >= 1 and $notas[$i] <= 50){ $nuevoEstado = 25; }
                    if($notas[$i] >= 51 and $notas[$i] <= 100){ $nuevoEstado = 5; }

                    $em->getConnection()->beginTransaction();


                    $estAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idAsignatura[$i]);
                    $estAsignatura->setEstudianteasignaturaEstado($em->getRepository('SieAppWebBundle:EstudianteasignaturaEstado')->find($nuevoEstado));
                    $em->flush($estAsignatura);

                    // Nuevo Registro
                    if($idNota[$i] == 'nuevo'){
                        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();
                        $newNota = new EstudianteNota();
                        $newNota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$i]));
                        $newNota->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idAsignatura[$i]));
                        $newNota->setNotaCuantitativa($notas[$i]);
                        $newNota->setNotaCualitativa('');
                        $newNota->setRecomendacion('');
                        $newNota->setUsuarioId($this->session->get('userId'));
                        $newNota->setFechaRegistro(new \DateTime('now'));
                        $newNota->setFechaModificacion(new \DateTime('now'));
                        $newNota->setObs('');
                        $em->persist($newNota);
                        $em->flush();
                    }else{
                        // Modificar Registro
                        $notaUpdate = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($idNota[$i]);
                        if($notaUpdate){
                            $notaUpdate->setNotaCuantitativa($notas[$i]);
                            $em->flush();
                        }/*
                        $notaUpdate = $em->createQueryBuilder()
                                        ->update('SieAppWebBundle:EstudianteNota','en')
                                        ->set('en.notaCuantitativa',$notas[$i])
                                        ->where('en.id = :idNota')
                                        ->setParameter('idNota',$idNota[$i])
                                        ->getQuery()
                                        ->getResult();*/
                    }

                    $em->getConnection()->commit();
                }
            }
            //$em->flush();

            return $this->redirect($this->generateUrl('herramienta_alter_notas_maestro_index'));
        } catch (Exception $e) {
            print_r($e);die;
            $em->getConnection()->rollback();
        }

    }
}
