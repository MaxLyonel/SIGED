<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
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
        // $sucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($this->session->get('ie_suc_id'));
        // $sucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find(611764);
        // if($sucursal){
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
                                    spt.periodoSuperior,
                                    ies.id as idSucursal
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
                            ->andWhere('gt.id = :idGestion')
                            // ->andWhere('ies.id = :idSucursal')
                            ->orderBy('sat.id','ASC')
                            ->addOrderBy('sespt.id','ASC')
                            ->setParameter('idPersona',$this->session->get('personaId'))
                            ->setParameter('idGestion',$this->session->get('currentyear'))
                            //->setParameter('idInstitucion',$this->session->get('ie_id'))
                            // ->setParameter('idSucursal',611764)
                            ->getQuery()
                            ->getResult();
        // }else{
        //     $asignaturas = array();
        // }
        // dump($asignaturas);die;
        //dump($this->session->get('personaId'));die;


        //dump($this->session->get('personaId'));
        //die;
        $em->getConnection()->commit();
        return $this->render('SieHerramientaAlternativaBundle:NotasMaestro:index.html.twig',array('asignaturas'=>$asignaturas));
    }

    public function newAction(Request $request){
        try {
            // $this->session->set('ie_per_estado', 2);
            $idCursoOferta = $request->get('idCursoOferta');
            $idSucursal = $request->get('idSucursal');
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $curso = $em->createQueryBuilder()
                        ->select('supet.especialidad,sat.acreditacion,smt.modulo,stt.turnoSuperior, sast.areaSuperior, pt.paralelo, ie.id as sie, ie.institucioneducativa, gt.gestion,
                                spt.periodoSuperior, sfat.codigo as idNivel, iec.id as idCurso, spt.id as idPeriodo
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

            $inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso'=>$curso[0]['idCurso']));
            foreach ($inscritos as $ins) {
                $asignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array('estudianteInscripcion'=>$ins->getId(), 'institucioneducativaCursoOferta'=>$idCursoOferta));
                if(!$asignatura){
                    $newAsignatura = new EstudianteAsignatura();
                    $newAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($curso[0]['gestion']));
                    $newAsignatura->setFechaRegistro(new \DateTime('now'));
                    $newAsignatura->setEstudianteInscripcion($ins);
                    $newAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta));
                    $em->persist($newAsignatura);
                    $em->flush();
                }
            }

            $estudiantes = $em->createQueryBuilder()
                              ->select('e.paterno, e.materno, e.nombre, ea.id as idEstudianteAsignatura, eae.id as idEstadoAsignatura, ei.id as idInscripcion')
                              ->from('SieAppWebBundle:Estudiante','e')
                              ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','ei.estudiante = e.id')
                              ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','with','ea.estudianteInscripcion = ei.id')
                              ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','with','ea.institucioneducativaCursoOferta = ieco.id')
                              ->leftJoin('SieAppWebBundle:EstudianteasignaturaEstado','eae','with','ea.estudianteasignaturaEstado = eae.id')
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

            $primariaNuevo = $this->get('funciones')->validatePrimariaCourse($curso[0]['idCurso']);
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

                // CARGAMOS LAS NOTAS CUALITATIVAS
                
                if($primariaNuevo){
                    $cualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$e['idInscripcion'], 'notaTipo'=>16));
                    if($cualitativa){
                        $idNotaCualitativa = $cualitativa->getId();
                        $notaCualitativa = $cualitativa->getNotaCualitativa();
                    }else{
                        $idNotaCualitativa = 'nuevo';
                        $notaCualitativa = '';
                    }

                    // Armamos el array con los datos del Xestudiante
                    $arrayEst[$cont] = array(
                                            'paterno'=>$e['paterno'],
                                            'materno'=>$e['materno'],
                                            'nombre'=>$e['nombre'],
                                            'idEstudianteAsignatura'=>$e['idEstudianteAsignatura'],
                                            'idNota'=>$idNota,
                                            'nota'=>$nota,
                                            'idNotaTipo'=>$idNotaTipo,
                                            'idEstadoAsignatura'=>$e['idEstadoAsignatura'],
                                            'idNotaCualitativa'=>$idNotaCualitativa,
                                            'notaCualitativa'=>$notaCualitativa,
                                            'idInscripcion' => $e['idInscripcion']);
                }else{
                    // Armamos el array con los datos del Xestudiante
                    $arrayEst[$cont] = array(
                                            'paterno'=>$e['paterno'],
                                            'materno'=>$e['materno'],
                                            'nombre'=>$e['nombre'],
                                            'idEstudianteAsignatura'=>$e['idEstudianteAsignatura'],
                                            'idNota'=>$idNota,
                                            'nota'=>$nota,
                                            'idNotaTipo'=>$idNotaTipo,
                                            'idEstadoAsignatura'=>$e['idEstadoAsignatura']);
                }
                
                $cont++;
            }
            
            //dump($arrayEst);die;
            // Obtenemos los estados de de las asignaturas
            $estadosAsignatura = $em->createQueryBuilder()
                                    ->select('eae')
                                    ->from('SieAppWebBundle:EstudianteasignaturaEstado','eae')
                                    ->getQuery()
                                    ->getResult();

            
            /**
             * VERIFICAMOS EL ESTADO EN EL CUAL SE ENCUENTRA LA UNIDAD EDUCATIVA
             */
            $sucursalTramite = $em->createQueryBuilder()
                            ->select('iest')
                            ->from('SieAppWebBundle:InstitucioneducativaSucursalTramite','iest')
                            ->where('iest.institucioneducativaSucursal = :sucursal')
                            ->setParameter('sucursal', $idSucursal)
                            ->getQuery()
                            ->getResult();

            $estado = $sucursalTramite[0]->getTramiteEstado()->getId();
            if($estado == 6 or $estado == 13){
                $this->session->set('ie_per_estado', 2);
            }else{
                $this->session->set('ie_per_estado', 0);
            }

            //dump('adsfadsf');die;
            $em->getConnection()->commit();
            return $this->render('SieHerramientaAlternativaBundle:NotasMaestro:notas.html.twig',array('curso'=>$curso,'estudiantes'=>$arrayEst,'estadosAsignatura'=>$estadosAsignatura, 'primariaNuevo'=>$primariaNuevo));
        } catch (Exception $e) {

        }
    }

    public function createUpdateAction(Request $request){
        try {

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $idAsignatura = $request->get('idAsignatura');
            $idNota = $request->get('idNota');
            $idNotaTipo = $request->get('idNotaTipo');
            $idNotaTipo = $request->get('idNotaTipo');
            $notas = $request->get('notas');

            $idNotaCualitativa = $request->get('idNotaCualitativa');
            $notaCualitativa = $request->get('notaCualitativa');
            $idInscripcion = $request->get('idInscripcion');
            /*dump($idAsignatura);
            dump($idNota);
            dump($idNotaTipo);
            dump($notas);
            die;*/

            if(count($notas)>0){
                for($i=0;$i<count($notas);$i++){
                    // Acualizamos el estado de la asignatura
                    if($notas[$i] == 0){ $nuevoEstado = 3; }
                    if($notas[$i] >= 1 and $notas[$i] <= 50){ $nuevoEstado = 25; }
                    if($notas[$i] >= 51 and $notas[$i] <= 100){ $nuevoEstado = 5; }                    


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
                }
            }

            if(count($idNotaCualitativa)>0){
                for($i=0;$i<count($idNotaCualitativa);$i++){

                    // Nuevo Registro
                    if($idNotaCualitativa[$i] == 'nuevo'){
                        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota_cualitativa');")->execute();
                        $newNotaCualitativa = new EstudianteNotaCualitativa();
                        $newNotaCualitativa->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(16));
                        $newNotaCualitativa->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion[$i]));
                        $newNotaCualitativa->setNotaCuantitativa(0);
                        $newNotaCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$i], 'utf-8'));
                        $newNotaCualitativa->setRecomendacion('');
                        $newNotaCualitativa->setUsuarioId($this->session->get('userId'));
                        $newNotaCualitativa->setFechaRegistro(new \DateTime('now'));
                        $newNotaCualitativa->setFechaModificacion(new \DateTime('now'));
                        $newNotaCualitativa->setObs('');
                        $em->persist($newNotaCualitativa);
                        $em->flush();
                    }else{
                        // Modificar Registro
                        $notaCualitativaUpdate = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idNotaCualitativa[$i]);
                        if($notaCualitativaUpdate){
                            $notaCualitativaUpdate->setNotaCualitativa(mb_strtoupper($notaCualitativa[$i],'utf-8'));
                            $em->flush();
                        }
                    }
                }
            }
            // VERIFICAR SI ES PRIMARIA Y SI EL ESTUDIANTE TIENE LAS 5 NOTAS PARA CALCULAR EL PROMEDIO
            if(count($idInscripcion) > 0){
                $idCurso = $em->createQueryBuilder()
                            ->select('iec.id')
                            ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
                            ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','ei.institucioneducativaCurso = iec.id')
                            ->where('ei.id = :idInscripcion')
                            ->setParameter('idInscripcion', $idInscripcion[0])
                            ->getQuery()
                            ->getResult()[0];

                $primariaNuevo = $this->get('funciones')->validatePrimariaCourse($idCurso['id']);
                if($primariaNuevo){
                    for ($i=0; $i < count($idInscripcion); $i++) {

                        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion[$i]);
                        // ACTUALIZAR ESTADO DE MATRICULA
                        $materias = $em->createQueryBuilder('')
                                    ->select('count(ea)')
                                    ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                                    ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','with','ea.estudianteInscripcion = ei.id')
                                    ->where('ei.id = :idInscripcion')
                                    ->setParameter('idInscripcion', $idInscripcion[$i])
                                    ->getQuery()
                                    ->getSingleResult();

                        $notas = $em->createQueryBuilder('')
                                    ->select('en')
                                    ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                                    ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','with','ea.estudianteInscripcion = ei.id')
                                    ->innerJoin('SieAppWebBundle:EstudianteNota','en','with','en.estudianteAsignatura = ea.id')
                                    ->where('ei.id = :idInscripcion')
                                    ->setParameter('idInscripcion', $idInscripcion[$i])
                                    ->getQuery()
                                    ->getResult();

                        if($materias[1] == count($notas)){
                            $nuevoEstado = $inscripcion->getEstadomatriculaTipo()->getId();
                            $contadorCeros = 0;
                            $contadorReprobados = 0;
                            $contadorAprobados = 0;
                            foreach ($notas as $n) {
                                if($n->getNotaCuantitativa() == 0){ $contadorCeros+=1; } // PORTERGADO
                                if($n->getNotaCuantitativa()>=1 and $n->getNotaCuantitativa()<=50){ $contadorReprobados+=1; } // PORTERGADO
                                if($n->getNotaCuantitativa()>=51 and $n->getNotaCuantitativa()<=100){  $contadorAprobados+=1; } // APROBADO
                            }  

                            if($contadorCeros == count($notas)){
                                $nuevoEstado = 3;
                            }else{
                                if($contadorAprobados == count($notas)){
                                  $nuevoEstado = 5;
                                }else{
                                  $nuevoEstado = 22;
                                }
                            }

                            $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($nuevoEstado));
                            // $em->persist($inscripcion);
                            $em->flush();

                        }
                    }
                }
            }
            


            $em->getConnection()->commit();

            return $this->redirect($this->generateUrl('herramienta_alter_notas_maestro_index'));
        } catch (Exception $e) {
            print_r($e);die;
            $em->getConnection()->rollback();
        }

    }
}
