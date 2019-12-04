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
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoEstudianteNotasController extends Controller {

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
        $aInfoUe = unserialize($infoUe);

        $infoStudent = $request->get('infoStudent');
        $aInfoStudent = json_decode($infoStudent,true);

        $idInscripcion = $aInfoStudent['eInsId'];
        //dump($aInfoUe['requestUser']['gestion']);die;
        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
        $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();

        $tipoNota = $this->get('notas')->getTipoNota($sie,$gestion,$nivel,$grado);

        if($tipoNota == 'Bimestre'){
            // Obtenemos el tipo de unidad educativa y el operativo
            $tipoUE = $this->get('funciones')->getTipoUE($aInfoUe['requestUser']['sie'],$aInfoUe['requestUser']['gestion']);
            $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);

            $notas = $this->get('notas')->regular($idInscripcion,$operativo);

            if($tipoUE){
                // PAra ues modulares secundaria
                if($tipoUE['id'] == 3 and $notas['nivel'] == 13){
                    $notas = $this->get('notas')->regular($idInscripcion,4);
                    $plantilla = 'modular';
                    $vista = 1;
                }else{
                    // Verificamos si el tipo es 1:plena, 2:tecnica tecnologica, 3:modular, 5: humanistica 7:transformacion (las que hayan hecho una solicitud pàra trabajar gestion actual)
                    if($tipoUE['id'] == 1 or $tipoUE['id'] == 2 or $tipoUE['id'] == 3 or $tipoUE['id'] == 5 or $tipoUE['id'] == 7){
                        $plantilla = 'regular';
                        $vista = 1;
                    }else{
                        // Regularizacion de gestiones pasadas 
                        if($notas['gestion'] < $notas['gestionActual']){
                            $plantilla = 'regular';
                            $vista = 1;
                        }else{
                            $plantilla = 'regular';
                            $vista = 0;
                        }
                    }
                }
            }else{
                $plantilla = 'regular';
                $vista = 0;
            }
            $swspeciality  = false;
            $objLevelModular    = false;
            foreach ($notas['cuantitativas'] as $key => $value) {
              if($value['idAsignatura']==1039){
                $swspeciality    = true;
                $objLevelModular = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array(
                  'institucioneducativaId'=>$sie,
                  'gestionTipoId'=>$gestion
                ));
              }

            }
            
            /*==================================================================================
            =            VALIDACION DE CIERRE DE CALIFICACIONES SEXTO DE SECUNDARIA            =
            ==================================================================================*/
            
            if($gestion >= 2019 and $operativo == 4 and $nivel == 13 and $grado == 6){
                $validacionSexto = $this->get('funciones')->verificarSextoSecundariaCerrado($sie, $gestion);
                if($validacionSexto){
                    if ($inscripcion->getEstadomatriculaTipo()->getId() != 4) {
                        $vista = 0;
                    }
                }
            }
            
            /*=====  End of VALIDACION DE CIERRE DE CALIFICACIONES SEXTO DE SECUNDARIA  ======*/
            

            return $this->render('SieHerramientaBundle:InfoEstudianteNotas:bimestre.html.twig',array(
                'notas'=>$notas,
                'swspeciality'=>$swspeciality,
                'grado'=>$grado,
                'objLevelModular'=>$objLevelModular,
                'inscripcion'=>$inscripcion,
                'vista'=>$vista,
                'plantilla'=>$plantilla,
                'infoUe'=>$infoUe,
                'infoStudent'=>$infoStudent
            ));
        }else{
            $notas = $this->get('notas')->regular($idInscripcion,0);
            return $this->render('SieHerramientaBundle:InfoEstudianteNotas:trimestre.html.twig',array(
                'notas'=>$notas,
                'grado'=>$grado,
                'inscripcion'=>$inscripcion,
                'vista'=>1,
                'plantilla'=>'trimestral',
                'infoUe'=>$infoUe,
                'infoStudent'=>$infoStudent
            ));
            /*
            $data = $this->getNotas($infoUe,$infoStudent);
            return $this->render('SieHerramientaBundle:InfoEstudianteNotas:notasTrimestre.html.twig',$data);*/
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
        $swspeciality    = false;
        $nombreEspecialidad = '';
        $objLevelModular = false;

        $sie = $aInfoUeducativa['requestUser']['sie'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];

        $nivelNombre = $aInfoUeducativa['ueducativaInfo']['nivel'];
        $gradoNombre = $aInfoUeducativa['ueducativaInfo']['grado'];
        $paraleloNombre = $aInfoUeducativa['ueducativaInfo']['paralelo'];
        $turnoNombre = $aInfoUeducativa['ueducativaInfo']['turno'];
        // datos estudiante
        $aInfoStudent = json_decode($infoStudent,true);

        $idInscripcion = $aInfoStudent['eInsId'];
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        //dump($inscripcion);die;
        $datosEstudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($aInfoStudent['id']);
        $estudiante = array(
                            'codigoRude'=>$aInfoStudent['codigoRude'],
                            'estudiante'=>$datosEstudiante->getNombre().' '.$datosEstudiante->getPaterno().' '.$datosEstudiante->getMaterno(),
                            'estadoMatricula'=>$inscripcion->getEstadomatriculaTipo()->getEstadomatricula(),
                            'idEstadoMatricula'=>$inscripcion->getEstadomatriculaTipo()->getId());

        $operativo =  $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie,$gestion);

        if($gestion <= 2012 or ($gestion == 2013 and $grado > 1 and $nivel != 11) or ($gestion == 2013 and $grado == (1 or 2 ) and $nivel == 11) ){

            vuelveRegistro:
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

            $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$iecId));

            $arrayAsignaturasEstudiante = array();
            foreach ($asignaturas as $a) {
                $arrayAsignaturasEstudiante[] = $a['asignaturaId'];
            }

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

            //die;

            // Volvemos atras si se adiciono alguna nueva materia o asignatura
            if($nuevaArea == true){
                goto vuelveRegistro;
            }
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
            if($gestion == 2013 and $nivel != 11 and $grado == 1){
                $conArea = true;
            }else{
                $conArea = true;
            }
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

            $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$iecId));

            $arrayAsignaturasEstudiante = array();
            foreach ($asignaturas as $a) {
                $arrayAsignaturasEstudiante[] = $a['asignaturaId'];
            }

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

            //die;

            // Volvemos atras si se adiciono alguna nueva materia o asignatura
            if($nuevaArea == true){
                goto vuelve;
            }

            //dump($asignaturas);die;
            $notasArray = array();
            $cont = 0;
            foreach ($asignaturas as $a) {
                if($a['asignaturaId'] == 1039){
                    $especialidad = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion'=>$idInscripcion));
                    if($especialidad){
                        $nombreEspecialidad =  mb_strtoupper($especialidad->getEspecialidadTecnicoHumanisticoTipo()->getEspecialidad(),'utf-8');
                    }else{
                        //$nombreEspecialidad = 'Registrar Especialidad'; // Colocar enlace
                        $swspeciality = true;
                        $objLevelModular = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$sie));

                    }
                    $a['asignatura'] = $a['asignatura'].' '.$nombreEspecialidad;
                }
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

        // Cambiamos el operativo -1 por que aun no esta la funcion o bandera para verificar 4to bimestre
        /*if ($operativo >= 4) {
            $operativo = 3;
        }*/


        $em->getConnection()->commit();
        return array('tipo'=>$tipo,'areas'=>$areas,'cualitativas'=>$arrayCualitativas,'nivel'=>$nivel,'estudiante'=>$estudiante,'grado'=>$grado, 'gestion'=>$gestion, 'infoStudent'=>$infoStudent, 'infoUe'=>$infoUe, 'operativo'=>$operativo, 'sie'=>$sie,
            'nivelNombre'    => $nivelNombre,
            'gradoNombre'    => $gradoNombre,
            'paraleloNombre' => $paraleloNombre,
            'turnoNombre'    => $turnoNombre,
            'swspeciality'   => $swspeciality,
            'objLevelModular'=> $objLevelModular,
            'specialityForm' => $this->specialityForm(serialize(array('infoStudent'=>$infoStudent, 'infoUe'=>$infoUe)))->createView()

          ) ;
    }

    //crete form to open the option to register speciality
    private function specialityForm($infoStudentAll){
      return $this->createFormBuilder()
              //->setAction($this->generateUrl(''))
              ->add('infoStudent','hidden',array('data'=>'krlos'))
              ->add('registerspeciality', 'button', array('label'=>'Registrar Especiality', 'attr' => array('class'=>'btn btn-danger btn-stroke btn-inset','conclick' => "newSpeciality1('$infoStudentAll');")))
              ->getForm();
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

    public function createUpdateAction(Request $request){

        $this->get('notas')->regularRegistro($request);
        $idInscripcion = $request->get('idInscripcion');

        $this->get('notas')->actualizarEstadoMatricula($idInscripcion);
        die;
        return 1;
    }
    /**
     * Function putSpecialityAction
     *
     * @author krlos Pacha C. <pckrlos@gmail.com>
     * @access public
     * @param string Request
     * @return string
     */
    public function putSpecialityAction(Request $request){
      //create DB conexion
      $em = $this->getDoctrine()->getManager();
      //get values to send
      $infoStudent = $request->get('infoStudent');
      $infoUe = $request->get('infoUe');
      //dump(json_decode($infoStudent,true));
      //dump(unserialize($infoUe));
      //die;

        return $this->render('SieHerramientaBundle:InfoEstudianteNotas:putspeciality.html.twig', array(
          'form'=>$this->putSpecialityForm($infoStudent, $infoUe)->createView()
        ));

    }
    /**
     * Function putSpecialityForm
     *
     * @author krlos Pacha C. <pckrlos@gmail.com>
     * @access public
     * @param string infoStudent, infoUe
     * @return twig
     */
    private function putSpecialityForm($infoStudent, $infoUe){
      //create conexion DB
      $em = $this->getDoctrine()->getManager();
      //convert values send
      $arrInfoUe = unserialize($infoUe);
      $arrInfoStudent = json_decode($infoStudent,true);

      $objSpeciality = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->getSpecialityByUnidadEducativa(
        $arrInfoUe['requestUser']['sie'],
        $arrInfoUe['requestUser']['gestion']
      );
      //built the speciailty array
      $arrSpeciality = array();
      foreach ($objSpeciality as $key => $speciality) {
        # code...
        $arrSpeciality[$speciality['ieethId'].'-'.$speciality['ethtId']] = $speciality['especialidad'];
      }
      //create fields form
        $form = $this->createFormBuilder()
                //->setAction($this->generateUrl('tecnico_humanistico_save'))
                //->add('especialidad', 'entity', array('label' => 'Especialidad', 'attr' => array('class' => 'form-control'), 'mapped' => false, 'class' => 'SieAppWebBundle:EspecialidadTipoHumnisticoTecnico'))
                ->add('especialidad', 'choice', array('label' => 'Especialidad','choices'=>$arrSpeciality, 'attr' => array('class' => 'form-control'), 'mapped' => false))
                ->add('horas', 'text', array('label' => 'Horas Cursadas', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{2,8}', 'maxlength' => '10', 'onkeypress' => 'onlyNumber(event)')))
                ->add('infoUe', 'hidden', array('data' => $infoUe))
                ->add('infoStudent', 'hidden', array('data' => $infoStudent))
                ->add('eInsId','hidden', array('data'=>$arrInfoStudent['eInsId']))
                //->add('registrar', 'button', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-default', 'onclick' => 'goSave(' . $idIns . ',' . $iddiv . ')')))
                ->add('registrar', 'button', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-success', 'onclick' => 'saveSpecialityNew()')))
                ->getForm();
        return $form;

    }

}
