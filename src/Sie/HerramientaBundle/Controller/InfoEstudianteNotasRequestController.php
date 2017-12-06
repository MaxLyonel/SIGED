<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoEstudianteNotasRequestController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction(Request $request){
        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');
        $data = $this->getNotas($infoUe, $infoStudent);
        if($data['tipo'] == 'bimestre'){
            return $this->render('SieHerramientaBundle:InfoEstudianteNotasRequest:notasBimestre.html.twig',$data);
        }else{
            return $this->render('SieHerramientaBundle:InfoEstudianteNotasRequest:notasTrimestre.html.twig',$data);
        }
    }

    public function newAction(Request $request){

    }

    /**
     * [deleteAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function deleteAction(Request $request){

    }

    /**
     * get Areas Curso
     * @param  Request $request [description]
     * @return View table areas
     */
    public function getNotas($infoUe, $infoStudent){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        // datos ue
        $aInfoUeducativa = unserialize($infoUe);

        $sie = $aInfoUeducativa['requestUser']['sie'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        // datos estudiante
        $aInfoStudent = json_decode($infoStudent,true);

        $idInscripcion = $aInfoStudent['eInsId'];
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        //dump($inscripcion);die;
        $estudiante = array(
                            'codigoRude'=>$aInfoStudent['codigoRude'],
                            'estudiante'=>$aInfoStudent['nombre'].' '.$aInfoStudent['paterno'].' '.$aInfoStudent['materno'],
                            'estadoMatricula'=>$inscripcion->getEstadomatriculaTipo()->getEstadomatricula());

        $operativo = $this->operativo($sie,$gestion);

        if($gestion <= 2012 or ($gestion == 2013 and $grado > 1 and ($nivel != 11 and $nivel != 1)) or ($gestion == 2013 and $grado == (1 or 2 ) and $nivel == 11 and $nivel == 1) ){
            // TRIMESTRALES
            $asignaturas = $em->createQueryBuilder()
                        ->select('asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                        ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                        ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ea.estudianteInscripcion = ei.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                        ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                        ->groupBy('asit.id, asit.asignatura, ea.id')
                        ->orderBy('asit.id','ASC')
                        ->where('ei.id = :idInscripcion')
                        ->setParameter('idInscripcion',$idInscripcion)
                        ->getQuery()
                        ->getResult();

            //dump($asignaturas);die;
            $notasArray = array();
            $cont = 0;
            foreach ($asignaturas as $a) {
                $notasArray[$cont] = array('idAsignatura'=>$a['asignaturaId'],'asignatura'=>$a['asignatura']);
                $asignaturasNotas = $em->createQueryBuilder()
                                    ->select('en.id as idNota, nt.id as idNotaTipo, nt.notaTipo, ea.id as idEstudianteAsignatura, en.notaCuantitativa, en.notaCualitativa, at.id')
                                    ->from('SieAppWebBundle:EstudianteNota','en')
                                    ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','en.estudianteAsignatura = ea.id')
                                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                                    ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','WITH','ieco.asignaturaTipo = at.id')
                                    ->innerJoin('SieAppWebBundle:NotaTipo','nt','with','en.notaTipo = nt.id')
                                    ->orderBy('nt.id','ASC')
                                    ->where('ea.id = :estAsigId')
                                    ->setParameter('estAsigId',$a['estAsigId'])
                                    ->getQuery()
                                    ->getResult();

                for($i=6;$i<=8;$i++){
                    $existe = 'no';
                    foreach ($asignaturasNotas as $an) {
                        if($i == $an['idNotaTipo']){
                            $notasArray[$cont]['notas'][] =   array(
                                                    'id'=>$cont."-".$i,
                                                    'idEstudianteNota'=>$an['idNota'],
                                                    'nota'=>$an['notaCuantitativa'],
                                                    'notaCualitativa'=>$an['notaCualitativa'],
                                                    'idNotaTipo'=>$an['idNotaTipo'],
                                                    'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                                );
                            $existe = 'si';
                            break;
                        }

                    }
                    if($existe == 'no'){
                        $notasArray[$cont]['notas'][] =   array(
                                                    'id'=>$cont."-".$i,
                                                    'idEstudianteNota'=>'nuevo',
                                                    'nota'=>0,
                                                    'notaCualitativa'=>'',
                                                    'idNotaTipo'=>$i,
                                                    'idEstudianteAsignatura'=>$a['estAsigId']
                                                );
                    }
                }

                if($nivel != 11 and $nivel != 1){
                    // Para el promedio anual
                    foreach ($asignaturasNotas as $an) {
                        $existe = 'no';
                        if($an['idNotaTipo'] == 9){
                            $notasArray[$cont]['notas'][] =   array(
                                                        'id'=>$cont."-9",
                                                        'idEstudianteNota'=>$an['idNota'],
                                                        'nota'=>$an['notaCuantitativa'],
                                                        'notaCualitativa'=>$an['notaCualitativa'],
                                                        'idNotaTipo'=>$an['idNotaTipo'],
                                                        'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                                    );
                            $existe = 'si';
                            break;
                        }
                    }
                    if($existe == 'no'){
                        $notasArray[$cont]['notas'][] =   array(
                                                    'id'=>$cont."-9",
                                                    'idEstudianteNota'=>'nuevo',
                                                    'nota'=>0,
                                                    'notaCualitativa'=>'',
                                                    'idNotaTipo'=>9,
                                                    'idEstudianteAsignatura'=>$a['estAsigId']
                                                );
                    }
                    // Para el reforzamiento
                    foreach ($asignaturasNotas as $an) {
                        $existe = 'no';
                        if($an['idNotaTipo'] == 10){
                            $notasArray[$cont]['notas'][] =   array(
                                                        'id'=>$cont."-10",
                                                        'idEstudianteNota'=>$an['idNota'],
                                                        'nota'=>$an['notaCuantitativa'],
                                                        'notaCualitativa'=>$an['notaCualitativa'],
                                                        'idNotaTipo'=>$an['idNotaTipo'],
                                                        'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                                    );
                            $existe = 'si';
                            break;
                        }
                    }
                    if($existe == 'no'){
                        $notasArray[$cont]['notas'][] =   array(
                                                    'id'=>$cont."-10",
                                                    'idEstudianteNota'=>'nuevo',
                                                    'nota'=>0,
                                                    'notaCualitativa'=>'',
                                                    'idNotaTipo'=>10,
                                                    'idEstudianteAsignatura'=>$a['estAsigId']
                                                );
                    }
                    // Para el promedio final
                    foreach ($asignaturasNotas as $an) {
                        $existe = 'no';
                        if($an['idNotaTipo'] == 11){
                            $notasArray[$cont]['notas'][] =   array(
                                                        'id'=>$cont."-11",
                                                        'idEstudianteNota'=>$an['idNota'],
                                                        'nota'=>$an['notaCuantitativa'],
                                                        'notaCualitativa'=>$an['notaCualitativa'],
                                                        'idNotaTipo'=>$an['idNotaTipo'],
                                                        'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                                    );
                            $existe = 'si';
                            break;
                        }
                    }
                    if($existe == 'no'){
                        $notasArray[$cont]['notas'][] =   array(
                                                    'id'=>$cont."-11",
                                                    'idEstudianteNota'=>'nuevo',
                                                    'nota'=>0,
                                                    'notaCualitativa'=>'',
                                                    'idNotaTipo'=>11,
                                                    'idEstudianteAsignatura'=>$a['estAsigId']
                                                );
                    }
                }
                $cont++;
            }

            $areas = array();
            $areas = $notasArray;
            //dump($areas);die;
            $tipo = 'trimestre';

        }else{
            // BIMESTRALES
            //if($gestion == 2013 and $nivel != 11 and $grado == 1){
                $conArea = false;
            //}else{
            //    $conArea = true;
            //}
            vuelve:
            if($conArea == true){
                // Obtenemos las areas o campos del estudiante
                $asignaturas = $em->createQueryBuilder()
                            ->select('at.id, at.area, asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                            ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                            ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ea.estudianteInscripcion = ei.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                            ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                            ->innerJoin('SieAppWebBundle:AreaTipo','at','WITH','asit.areaTipo = at.id')
                            ->groupBy('at.id, at.area, asit.id, asit.asignatura, ea.id')
                            ->orderBy('at.id','ASC')
                            ->addOrderBy('asit.id','ASC')
                            ->where('ei.id = :idInscripcion')
                            ->setParameter('idInscripcion',$idInscripcion)
                            ->getQuery()
                            ->getResult();
            }else{
                // Obtenemos las asigganturas sin areas o campos del estudiante
                $asignaturas = $em->createQueryBuilder()
                            ->select('asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                            ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                            ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ea.estudianteInscripcion = ei.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                            ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                            ->groupBy('asit.id, asit.asignatura, ea.id')
                            ->orderBy('asit.id','ASC')
                            ->where('ei.id = :idInscripcion')
                            ->setParameter('idInscripcion',$idInscripcion)
                            ->getQuery()
                            ->getResult();
            }

            // Verificamos si el estudiante tiene las mismas materias de curso oferta
            $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$iecId));

            $arrayAsignaturasEstudiante = array();
            foreach ($asignaturas as $a) {
                $arrayAsignaturasEstudiante[] = $a['asignaturaId'];
            }
            //dump($arrayAsignaturasEstudiante);die;
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');")->execute();
            $nuevaArea = false;
            foreach ($cursoOferta as $co) {
                if(!in_array($co->getAsignaturaTipo()->getId(), $arrayAsignaturasEstudiante)){
                    // Si no existe la asignatura, registramos la asignatura para el maestro
                    $newEstAsig = new EstudianteAsignatura();
                    $newEstAsig->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                    $newEstAsig->setFechaRegistro(new \DateTime('now'));
                    $newEstAsig->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                    $newEstAsig->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId()));
                    $em->persist($newEstAsig);
                    $em->flush();
                    $nuevaArea = true;
                }
            }
            // Volvemos atras si se adiciono alguna nueva materia o asignatura
            if($nuevaArea == true){
                goto vuelve;
            }

            //dump($asignaturas);die;
            $notasArray = array();
            $cont = 0;
            foreach ($asignaturas as $a) {
                if($conArea == true){
                    $notasArray[$cont] = array('areaId'=>$a['id'],'area'=>$a['area'],'idAsignatura'=>$a['asignaturaId'],'asignatura'=>$a['asignatura']);
                }else{
                    $notasArray[$cont] = array('idAsignatura'=>$a['asignaturaId'],'asignatura'=>$a['asignatura']);
                }
                $asignaturasNotas = $em->createQueryBuilder()
                                    ->select('en.id as idNota, nt.id as idNotaTipo, nt.notaTipo, ea.id as idEstudianteAsignatura, en.notaCuantitativa, en.notaCualitativa, at.id')
                                    ->from('SieAppWebBundle:EstudianteNota','en')
                                    ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','en.estudianteAsignatura = ea.id')
                                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                                    ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','WITH','ieco.asignaturaTipo = at.id')
                                    ->innerJoin('SieAppWebBundle:NotaTipo','nt','with','en.notaTipo = nt.id')
                                    ->orderBy('nt.id','ASC')
                                    ->where('ea.id = :estAsigId')
                                    ->setParameter('estAsigId',$a['estAsigId'])
                                    ->getQuery()
                                    ->getResult();

                for($i=1;$i<=4;$i++){
                    $existe = 'no';
                    foreach ($asignaturasNotas as $an) {
                        if($nivel != 11 and $nivel != 1){
                            $valorNota = $an['notaCuantitativa'];
                        }else{
                            $valorNota = $an['notaCualitativa'];
                        }
                        if($i == $an['idNotaTipo']){
                            $notasArray[$cont]['notas'][] =   array(
                                                    'id'=>$cont."-".$i,
                                                    'idEstudianteNota'=>$an['idNota'],
                                                    'nota'=>$valorNota,
                                                    'idNotaTipo'=>$an['idNotaTipo'],
                                                    'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                                );
                            $existe = 'si';
                            break;
                        }

                    }
                    if($existe == 'no'){
                        if($nivel != 11 and $nivel != 1){
                            $valorNota = 0;
                        }else{
                            $valorNota = '';
                        }
                        $notasArray[$cont]['notas'][] =   array(
                                                    'id'=>$cont."-".$i,
                                                    'idEstudianteNota'=>'nuevo',
                                                    'nota'=>$valorNota,
                                                    'idNotaTipo'=>$i,
                                                    'idEstudianteAsignatura'=>$a['estAsigId']
                                                );
                    }
                }
                if($nivel != 11 and $nivel != 1){
                    // Para el promedio
                    foreach ($asignaturasNotas as $an) {
                        $existe = 'no';
                        if($an['idNotaTipo'] == 5){
                            $notasArray[$cont]['notas'][] =   array(
                                                        'id'=>$cont."-5",
                                                        'idEstudianteNota'=>$an['idNota'],
                                                        'nota'=>$an['notaCuantitativa'],
                                                        'idNotaTipo'=>$an['idNotaTipo'],
                                                        'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                                    );
                            $existe = 'si';
                            break;
                        }
                    }
                    if($existe == 'no'){

                        $notasArray[$cont]['notas'][] =   array(
                                                    'id'=>$cont."-5",
                                                    'idEstudianteNota'=>'nuevo',
                                                    'nota'=>0,
                                                    'idNotaTipo'=>5,
                                                    'idEstudianteAsignatura'=>$a['estAsigId']
                                                );
                    }
                }
                $cont++;
            }
            $areas = array();
            if($conArea == true){
                foreach ($notasArray as $n) {
                    $areas[$n['area']][] = $n;
                }
            }else{
                $areas = $notasArray;
            }
            //dump($areas);die;
            $tipo = 'bimestre';
        }

        //notas cualitativas
        $arrayCualitativas = array();

        $cualitativas = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion'=>$idInscripcion),array('notaTipo'=>'ASC'));
        if($nivel == 11 or $nivel == 1){
            // Para inicial
            $existe = false;
            foreach ($cualitativas as $c) {
                if($c->getNotaTipo()->getId() == 18){
                    $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                 'idEstudianteNotaCualitativa'=>$c->getId(),
                                                 'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                 'notaCualitativa'=>$c->getNotaCualitativa(),
                                                 'notaTipo'=>$c->getNotaTipo()->getNotaTipo()
                                                );
                    $existe = true;
                }
            }
            if($existe == false){
                $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                             'idEstudianteNotaCualitativa'=>'nuevo',
                                             'idNotaTipo'=>18,
                                             'notaCualitativa'=>'',
                                             'notaTipo'=>$this->literal(18).' Bimestre'
                                            );
                $existe = true;
            }
        }else{
            // Para primaria y secundaria
            if($tipo == 'bimestre'){
              $inicio = 1;
              $fin = 4;
              $tipoNot = 'Bimestre';
            }else{
              $inicio = 6;
              $fin = 8;
              $tipoNot = 'Trimestre';
            }
            for($i=$inicio;$i<=$fin;$i++){
                $existe = false;
                foreach ($cualitativas as $c) {
                    if($c->getNotaTipo()->getId() == $i){
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>$c->getId(),
                                                     'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                     'notaCualitativa'=>$c->getNotaCualitativa(),
                                                     'notaTipo'=>$c->getNotaTipo()->getNotaTipo()
                                                    );
                        $existe = true;
                    }
                }
                if($existe == false){
                    $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                 'idEstudianteNotaCualitativa'=>'nuevo',
                                                 'idNotaTipo'=>$i,
                                                 'notaCualitativa'=>'',
                                                 'notaTipo'=>$this->literal($i).' '.$tipoNot
                                                );
                    $existe = true;
                }
            }
        }
        $em->getConnection()->commit();
        return array('tipo'=>$tipo,'areas'=>$areas,'cualitativas'=>$arrayCualitativas,'nivel'=>$nivel,'estudiante'=>$estudiante,'grado'=>$grado, 'gestion'=>$gestion, 'infoStudent'=>$infoStudent, 'infoUe'=>$infoUe, 'operativo'=>$operativo) ;
    }

    public function literal($num){
        switch ($num) {
            case '1': $lit = 'Primer'; break;
            case '2': $lit = 'Segundo'; break;
            case '3': $lit = 'Tercer'; break;
            case '4': $lit = 'Cuarto'; break;
            case '6': $lit = 'Primer'; break;
            case '7': $lit = 'Segundo'; break;
            case '8': $lit = 'Tercer'; break;
            case '18': $lit = 'Informe Final Inicial'; break;
        }
        return $lit;
    }

    public function operativo($sie,$gestion){
        $em = $this->getDoctrine()->getManager();
        // Obtenemos el operativo para bloquear los controles
        $registroOperativo = $em->createQueryBuilder()
                        ->select('rc.bim1,rc.bim2,rc.bim3,rc.bim4')
                        ->from('SieAppWebBundle:RegistroConsolidacion','rc')
                        ->where('rc.unidadEducativa = :ue')
                        ->andWhere('rc.gestion = :gestion')
                        ->setParameter('ue',$sie)
                        ->setParameter('gestion',$gestion)
                        ->getQuery()
                        ->getResult();
        $operativo = 5;
        if(!$registroOperativo){
            // Si no existe es operativo inicio de gestion
            $operativo = 0;
        }else{
            //dump($registroOperativo);die;
            if($registroOperativo[0]['bim1'] == 0 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 1; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 2; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 3; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 4; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] >= 1){
                $operativo = 5; // Fin de gestion o cerrado
            }
        }
        return $operativo;
    }

    public function createUpdateAction(Request $request){
        $infoStudent = $request->get('infoStudent');
        //dump($infoStudent);

        // datos ue
        $infoUe= $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);

        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];

        // datos estudiante
        $aInfoStudent = json_decode($infoStudent,true);
        $idInscripcion = $aInfoStudent['eInsId'];

        // DAtos de las notas cuantitativas
        $idEstudianteNota = $request->get('idEstudianteNota');
        $idNotaTipo = $request->get('idNotaTipo');
        $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
        $notas = $request->get('notas');

        // Datos de las notas cualitativas
        $idEstudianteNotaCualitativa = $request->get('idEstudianteNotaCualitativa');
        $idNotaTipoCualitativa = $request->get('idNotaTipoCualitativa');
        $notaCualitativa = $request->get('notaCualitativa');

        // Datos de las notas cualitativas de primaria gestion 2013
        $idEstudianteNotaC = $request->get('idEstudianteNotaC');
        $idNotaTipoC = $request->get('idNotaTipoC');
        $idEstudianteAsignaturaC = $request->get('idEstudianteAsignaturaC');
        $notasC = $request->get('notasC');

        /*dump($idEstudianteNota);
        dump($idNotaTipo);
        dump($idEstudianteAsignatura);
        dump($notas);
        dump($idEstudianteNotaCualitativa);
        dump($idNotaTipoCualitativa);
        dump($notaCualitativa);
        dump($idEstudianteNotaC);
        dump($idNotaTipoC);
        dump($idEstudianteAsignaturaC);
        dump($notasC);*/

        $em = $this->getDoctrine()->getManager();
        $tipo = $request->get('tipo');
        /**
         * Para las notas BIMESTRALES
         */
        if($tipo == 'b'){
            // Registro y/o modificacion de notas
            for($i=0;$i<count($idEstudianteNota);$i++) {
                if($idEstudianteNota[$i] == 'nuevo'){
                    $newNota = new EstudianteNota();
                    $newNota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$i]));
                    $newNota->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                    if($nivel == 11){
                        $newNota->setNotaCuantitativa(0);
                        $newNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                    }else{
                        $newNota->setNotaCuantitativa($notas[$i]);
                        $newNota->setNotaCualitativa('');
                    }
                    $newNota->setRecomendacion('');
                    $newNota->setUsuarioId($this->session->get('userId'));
                    $newNota->setFechaRegistro(new \DateTime('now'));
                    $newNota->setFechaModificacion(new \DateTime('now'));
                    $newNota->setObs('');
                    $em->persist($newNota);
                    $em->flush();
                }else{
                    $updateNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
                    if($updateNota){
                        if($nivel == 11){
                            $updateNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                        }else{
                            $updateNota->setNotaCuantitativa($notas[$i]);
                        }
                        $updateNota->setUsuarioId($this->session->get('userId'));
                        $updateNota->setFechaModificacion(new \DateTime('now'));
                        $em->flush();
                    }
                }
            }

            // Registro de notas cualitativas de incial primaria yo secundaria
            for($j=0;$j<count($idEstudianteNotaCualitativa);$j++){
                if($idEstudianteNotaCualitativa[$j] == 'nuevo'){
                    $newCualitativa = new EstudianteNotaCualitativa();
                    $newCualitativa->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipoCualitativa[$j]));
                    $newCualitativa->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                    $newCualitativa->setNotaCuantitativa(0);
                    $newCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                    $newCualitativa->setRecomendacion('');
                    $newCualitativa->setUsuarioId($this->session->get('userId'));
                    $newCualitativa->setFechaRegistro(new \DateTime('now'));
                    $newCualitativa->setFechaModificacion(new \DateTime('now'));
                    $newCualitativa->setObs('');
                    $em->persist($newCualitativa);
                    $em->flush();
                }else{
                    $updateCualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaCualitativa[$j]);
                    if($updateCualitativa){
                        $updateCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                        $updateCualitativa->setUsuarioId($this->session->get('userId'));
                        $updateCualitativa->setFechaModificacion(new \DateTime('now'));
                        $em->flush();
                    }
                }
            }
        }
        /**
         * PAra las notas TRIMESTRALES
         */
        if($tipo == 't'){
            for($i=0;$i<count($idEstudianteNota);$i++) {
                if($idEstudianteNota[$i] == 'nuevo'){
                    if(($idNotaTipo[$i] <=9) or (($idNotaTipo[$i]==10 or $idNotaTipo[$i]==11) and $notas[$i]!=0 )){
                        $newNota = new EstudianteNota();
                        $newNota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$i]));
                        $newNota->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                        if($nivel == 11 or $nivel == 1){
                            $newNota->setNotaCuantitativa(0);
                            $newNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                        }else{
                            $newNota->setNotaCuantitativa($notas[$i]);
                            $newNota->setNotaCualitativa('');
                        }
                        $newNota->setRecomendacion('');
                        $newNota->setUsuarioId($this->session->get('userId'));
                        $newNota->setFechaRegistro(new \DateTime('now'));
                        $newNota->setFechaModificacion(new \DateTime('now'));
                        $newNota->setObs('');
                        $em->persist($newNota);
                        $em->flush();
                    }
                }else{
                    $updateNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
                    if($updateNota){
                        if(($idNotaTipo[$i] <=9) or (($idNotaTipo[$i]==10 or $idNotaTipo[$i]==11) and $notas[$i]!=0)){
                            if($nivel == 11 or $nivel == 1){
                                $updateNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                            }else{
                                $updateNota->setNotaCuantitativa($notas[$i]);
                            }
                            $updateNota->setUsuarioId($this->session->get('userId'));
                            $updateNota->setFechaModificacion(new \DateTime('now'));
                            $em->flush();
                        }else{
                            // Eliminar la nota de reforzamiento o promedio final si es cero
                            $em->remove($updateNota);
                            $em->flush();
                        }
                    }
                }
            }
            if($nivel == 12 or $nivel == 2 or $nivel == 3 or ($nivel == 13 and $gestion <= 2013)){
                // Registro de notas cualitativas de primaria
                for($j=0;$j<count($idEstudianteNotaC);$j++){
                    if($idEstudianteNotaC[$j] == 'nuevo'){
                        // Verificamos si la nota cuantitativa existe para hacer el update
                        $existeNotaCuantitativa = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('notaTipo'=>$idNotaTipoC[$j],'estudianteAsignatura'=>$idEstudianteAsignaturaC[$j]));
                        if($existeNotaCuantitativa){
                            $existeNotaCuantitativa->setNotaCualitativa(mb_strtoupper($notasC[$j],'utf-8'));
                            $existeNotaCuantitativa->setUsuarioId($this->session->get('userId'));
                            $existeNotaCuantitativa->setFechaModificacion(new \DateTime('now'));
                            $em->flush();
                        }

                    }else{
                        $updateNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNotaC[$j]);
                        if($updateNota){
                            $updateNota->setNotaCualitativa(mb_strtoupper($notasC[$j],'utf-8'));
                            $updateNota->setUsuarioId($this->session->get('userId'));
                            $updateNota->setFechaModificacion(new \DateTime('now'));
                            $em->flush();
                        }
                    }
                }
            }else{
                // Registro de notas cualitativas de incial y secundaria
                for($j=0;$j<count($idEstudianteNotaCualitativa);$j++){
                    if($idEstudianteNotaCualitativa[$j] == 'nuevo'){
                        $newCualitativa = new EstudianteNotaCualitativa();
                        $newCualitativa->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipoCualitativa[$j]));
                        $newCualitativa->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                        $newCualitativa->setNotaCuantitativa(0);
                        $newCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                        $newCualitativa->setRecomendacion('');
                        $newCualitativa->setUsuarioId($this->session->get('userId'));
                        $newCualitativa->setFechaRegistro(new \DateTime('now'));
                        $newCualitativa->setFechaModificacion(new \DateTime('now'));
                        $newCualitativa->setObs('');
                        $em->persist($newCualitativa);
                        $em->flush();
                    }else{
                        $updateCualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaCualitativa[$j]);
                        if($updateCualitativa){
                            $updateCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                            $updateCualitativa->setUsuarioId($this->session->get('userId'));
                            $updateCualitativa->setFechaModificacion(new \DateTime('now'));
                            $em->flush();
                        }
                    }
                }
            }
        }

        /**
         * Actualizacion del estado de matricula del estudiante
         */
        if($nivel == 11 or $nivel == 1){
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(5));
            $em->flush();
        }else{
            $asignaturas = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findby(array('estudianteInscripcion'=>$idInscripcion));
            $arrayPromedios = array();
            foreach ($asignaturas as $a) {
                // Notas Bimestrales
                if($tipo == 'b'){
                    $notaPromedio = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>5));
                    if($notaPromedio){
                        $arrayPromedios[] = $notaPromedio->getNotaCuantitativa();
                    }
                }
                // Notas Trimestrales
                if($tipo == 't'){
                    $notaPromedioFinal = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>11));
                    if($notaPromedioFinal){
                        $arrayPromedios[] = $notaPromedioFinal->getNotaCuantitativa();
                    }else{
                        $notaPromedioAnual = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>9));
                        if($notaPromedioAnual){
                            $arrayPromedios[] = $notaPromedioAnual->getNotaCuantitativa();
                        }
                    }
                }
            }

            if(count($asignaturas) == count($arrayPromedios)){
                $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                $nuevoEstado = 5; // Aprobado
                if($tipo == 'b'){
                    foreach ($arrayPromedios as $ap) {
                        if($ap < 51){
                            $nuevoEstado = 11;
                            break;
                        }
                    }
                }
                if($tipo == 't'){
                    foreach ($arrayPromedios as $ap) {
                        if($ap < 36){
                            $nuevoEstado = 11;
                            break;
                        }
                    }
                }
                $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($nuevoEstado));
                $em->flush();
            }
        }
        die;
        return 1;
    }
}
