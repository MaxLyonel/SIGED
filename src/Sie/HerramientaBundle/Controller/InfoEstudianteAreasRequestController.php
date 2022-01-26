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
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\RegistroConsolidacion;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoEstudianteAreasRequestController extends Controller {

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
        $data = $this->getAreas($infoUe);
        return $this->render('SieHerramientaBundle:InfoEstudianteAreasRequest:index.html.twig',$data);
    }

    public function newAction(Request $request){
        $infoUe = $request->get('infoUe');
        $idAsignatura = $request->get('ida');

        $aInfoUeducativa = unserialize($infoUe);
        $idCurso = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];

        // Registramos la nueva asignatura en curso oferta
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();

        $newArea = new InstitucioneducativaCursoOferta();
        $newArea->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($idAsignatura));
        $newArea->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso));
        $newArea->setHorasmes(0);
        $em->persist($newArea);
        $em->flush();

        // Actualizamos el id de la tabla estudiante asignatura
        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
        $query->execute();
        // Listamos los estudinates inscritos
        // para registrar el curso a los estudiantes
        $inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso' => $idCurso));
        foreach ($inscritos as $ins) {
            // Verificamos si el estudiante ya tiene la asignatura
            $estInscripcion = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array('estudianteInscripcion'=>$ins->getId(),'institucioneducativaCursoOferta'=>$newArea->getId()));
            if(!$estInscripcion){
                //echo "agregando notas a ". $ins->getEstudiante()->getNombre() ." <br>";
                /*$estAsignaturaNew = new EstudianteAsignatura();
                $estAsignaturaNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                $estAsignaturaNew->setFerchaLastUpdate(new \DateTime('now'));
                $estAsignaturaNew->setVersion(0);
                $estAsignaturaNew->setRevisionId(0);
                $estAsignaturaNew->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($ins->getId()));
                $estAsignaturaNew->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($newArea->getId()));
                $em->persist($estAsignaturaNew);
                $em->flush();*/

                //$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');")->execute();
                $newEstAsig = new EstudianteAsignatura();
                $newEstAsig->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                $newEstAsig->setFechaRegistro(new \DateTime('now'));
                $newEstAsig->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($ins->getId()));
                $newEstAsig->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($newArea->getId()));
                $em->persist($newEstAsig);
                $em->flush();
            }
        }
        $em->getConnection()->commit();
        // Mostramos nuevamente las areas del curso
        $data = $this->getAreas($infoUe);
        return $this->render('SieHerramientaBundle:InfoEstudianteAreasRequest:index.html.twig',$data);
    }

    /**
     * [deleteAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function deleteAction(Request $request){
        $infoUe = $request->get('infoUe');
        $idCursoOferta = $request->get('idco');
        // Eliminamos el registro de curso oferta
        $em = $this->getDoctrine()->getManager();
        $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta);
        $cursosOfertasSimilares = $em->createQuery(
                        'SELECT ieco FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
            INNER JOIN ieco.insitucioneducativaCurso iec
            INNER JOIN ieco.asignaturaTipo at
            WHERE iec.id = :idCurso
            AND at.id = :idAsignatura')
                ->setParameter('idCurso', $cursoOferta->getInsitucioneducativaCurso()->getId())
                ->setParameter('idAsignatura', $cursoOferta->getAsignaturaTipo()->getId())
                ->getResult();

        foreach ($cursosOfertasSimilares as $co) {
            $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('institucioneducativaCursoOferta' => $co->getId()));

            foreach ($estudianteAsignatura as $ea) {
                // Eliminamos las notas
                $em->createQuery(
                                'DELETE FROM SieAppWebBundle:EstudianteNota en
                    WHERE en.estudianteAsignatura = :idEstAsig')
                        ->setParameter('idEstAsig', $ea->getId())->execute();
            }

            // Eliminamos en la tabla estudiante asignatura
            $em->createQuery(
                            'DELETE FROM SieAppWebBundle:EstudianteAsignatura ea
                WHERE ea.institucioneducativaCursoOferta = :idCO')
                    ->setParameter('idCO', $co->getId())->execute();

            // Eliminamos en la tabla curso oferta maestro
            $em->createQuery('DELETE FROM SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro iecom
                              WHERE iecom.institucioneducativaCursoOferta = :idCO')
                              ->setParameter('idCO',$co->getId())->execute();

            // Registramos en la tabla de control
            // Eliminamos el curso oferta

            $cursoOfertaEliminar = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId());
            $em->remove($cursoOfertaEliminar);


        }
        $em->flush();
        // Mostramos nuevamente las areas del curso
        $data = $this->getAreas($infoUe);
        return $this->render('SieHerramientaBundle:InfoEstudianteAreasRequest:index.html.twig',$data);
    }

    /**
     * get Areas Curso
     * @param  Request $request [description]
     * @return View table areas
     */
    public function getAreas($infoUe){

        $aInfoUeducativa = unserialize($infoUe);
        //get the values throght the infoUe
        $sie = $aInfoUeducativa['requestUser']['sie'];
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
        $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
        $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];
        //dump($iecId);die;
        //dump($nivel);
        //dump($grado);
        //dump($paralelo);
        //die;

        $matpri = array(203, 204, 205, 207, 250, 252, 253, 254, 257);
        $matsec = array(203, 204, 205, 207, 251, 252, 253, 254, 257, 258);

        $matiniant = array(101, 102, 103, 104, 105);
        $matpriant = array(201, 203, 204, 205, 206, 207, 209, 210);
        //$matsecantter = array(301, 302, 304, 305, 309, 313, 316, 317, 318, 319);
        $matsecantter = array(301, 302, 303, 305, 307, 308, 309, 310, 311, 312, 313, 315, 316, 317, 318, 319, 320);

        $matsecantcua = array(301, 302, 303, 304, 305, 309, 313, 316, 317, 318, 319);

        $matsecantquisex = array(301, 302, 303, 305, 307, 308, 310, 311, 312, 313, 316, 317, 318, 319);


        $matinia = array(1000, 1001, 1002, 1003);
        $matpria = array(1011, 1012, 1013, 1014, 1015, 1016, 1017, 1018, 1019);

        $matseca = array(1031, 1032, 1033, 1034, 1035, 1036, 1037, 1038, 1040, 1044);
        $matsecb = array(1031, 1032, 1033, 1034, 1035, 1036, 1037, 1038, 1040, 1041, 1042, 1043, 1044);
        $matsecc = array(1031, 1032, 1033, 1034, 1035, 1036, 1037, 1040, 1041, 1042, 1043, 1044);

        $matsecd = array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044);
        $matsece = array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044,1045);
        $matsecf = array(1031,1032,1033,1034,1035,1036,1037,1039,1040,1043,1044,1045);

        $matsecg = array(1031,1032,1033,1034,1035,1036,1037,1040,1043,1044,1045); // PAra gestiones 2016 en adelante grados 5 y 6

        // Para unidades educativas nocturnas
        $matnocta = array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044); // para 1 y 2
        $matnoctb = array(1031,1032,1033,1037,1040,1043,1044,1045); // para 3,4,5 y 6

        switch ($gestion) {
            case 2008:
            case 2009:
            case 2010:
            case 2011:
            case 2012:
            case 2013:
                switch ($nivel) {
                    case 11:
                    case 1:
                        $idsAsignaturas = $matiniant;
                        break;
                    case 12:
                    case 2:
                        switch ($grado) {
                            case 1:
                                $idsAsignaturas = $matpri;
                                break;
                            case 2:
                            case 3:
                            case 4:
                            case 5:
                            case 6:
                                $idsAsignaturas = $matpriant;
                                break;
                        }
                        break;
                    case 13:
                    case 3:
                        switch ($grado) {
                            case 1:
                                $idsAsignaturas = $matsec;
                                break;
                            case 2:
                                $idsAsignaturas = $matpriant;
                                break;
                            case 3:
                                $idsAsignaturas = $matsecantter;
                                break;
                            case 4:
                                $idsAsignaturas = $matsecantcua;
                                break;
                            case 5:
                            case 6:
                                $idsAsignaturas = $matsecantquisex;
                                break;
                        }
                        break;
                }
                break;
            case 2014:
                switch ($nivel) {
                    case 11:
                        $idsAsignaturas = $matinia;
                        break;
                    case 12:
                        $idsAsignaturas = $matpria;
                        break;
                    case 13:
                        switch ($grado) {
                            case 1:
                            case 2:
                                $idsAsignaturas = $matseca;
                                break;
                            case 3:
                            case 4:
                                $idsAsignaturas = $matsecb;
                                break;
                            case 5:
                            case 6:
                                $idsAsignaturas = $matsecc;
                                break;
                        }
                        break;
                }
                break;
            case 2015:
                switch ($nivel) {
                    case 11:
                        $idsAsignaturas = $matinia;
                        break;
                    case 12:
                        $idsAsignaturas = $matpria;
                        break;
                    case 13:
                        switch ($grado) {
                            case 1:
                            case 2:
                                $idsAsignaturas = $matsecd;
                                break;
                            case 3:
                            case 4:
                                $idsAsignaturas = $matsece;
                                break;
                            case 5:
                            case 6:
                                $idsAsignaturas = $matsecf;
                                break;
                        }
                        break;
                }
                break;

            case 2016:
                switch ($nivel) {
                    case 11:
                        $idsAsignaturas = $matinia;
                        break;
                    case 12:
                        $idsAsignaturas = $matpria;
                        break;
                    case 13:
                        switch ($grado) {
                            case 1:
                            case 2:
                                if($turno == 4){
                                    // Para unidades educativas nocturnas
                                    $idsAsignaturas = $matnocta;
                                }else{
                                    $idsAsignaturas = $matsecd;
                                }
                                break;
                            case 3:
                            case 4:
                                if($turno == 4){
                                    // Para unidades educativas nocturnas
                                    $idsAsignaturas = $matnoctb;
                                }else{
                                    $idsAsignaturas = $matsece;
                                }
                                break;
                            case 5:
                            case 6:
                                if($turno == 4){
                                    // Para unidades educativas nocturnas
                                    $idsAsignaturas = $matnoctb;
                                }else{
                                    if($this->session->get('ue_plena') == true){
                                        $idsAsignaturas = $matsecf;
                                    }else{
                                        if($this->session->get('ue_plena') == true){
                                            $idsAsignaturas = $matsecf;
                                        }else{
                                            $idsAsignaturas = $matsecg;
                                        }
                                    }
                                }
                                break;
                        }
                        break;
                }
                break;
        }
        //dump($idsAsignaturas);die;
        /*switch($nivel){
            case 11: $idsAsignaturas = array(1000,1001,1002,1003);break;
            case 12: $idsAsignaturas = array(1011,1012,1013,1014,1015,1016,1017,1018,1019);break;
            case 13:
                    switch($grado){
                        case 1:
                        case 2: $idsAsignaturas = array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044);break;
                        case 3:
                        case 4: $idsAsignaturas = array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044,1045);break;
                        case 5:
                        case 6: $idsAsignaturas = array(1031,1032,1033,1034,1035,1036,1037,1039,1040,1043,1044,1045);break;
                    }
                    break;
        }*/

        $em = $this->getDoctrine()->getManager();

        // Curso oferta asignaturas del curso
        $cursoOferta = $em->createQueryBuilder()
                            ->select('ieco.id, at.id as idAsignatura, at.asignatura')
                            ->from('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ieco.insitucioneducativaCurso = iec.id')
                            ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','WITH','ieco.asignaturaTipo = at.id')
                            ->where('iec.id = :idCurso')
                            ->setParameter('idCurso',$iecId)
                            ->orderBy('at.id','ASC')
                            ->getQuery()
                            ->getResult();

        //dump($cursoOferta);die;
        $actuales = array();
        foreach ($cursoOferta as $co) {
            $actuales[] = $co['idAsignatura'];
        }
        if(count($actuales)==0){
          $actuales[]=1;
        }
        //dump($actuales);die;
        // Si no hay materias actuales asignamos un valor 1 para que no de error en las siguientes consultas de NOT IN
        if(count($actuales)==0){$actuales[] = 100;}
        // Asignaturas faltantes que le corresponde al curso
        $asignaturas = $em->createQueryBuilder()
                            ->select('at')
                            ->from('SieAppWebBundle:AsignaturaTipo','at')
                            //->innerJoin('SieAppWebBundle:AsignaturaNivelTipo','ant','WITH','at.asignaturaNivel = ant.id')
                            ->where('at.id IN (:idAsignaturas)')
                            ->andWhere('at.id NOT IN (:cursoOferta)')
                            //->setParameter('idNivel',$nivel)
                            ->setParameter('idAsignaturas',$idsAsignaturas)
                            ->setParameter('cursoOferta',$actuales)
                            ->getQuery()
                            ->getResult();

        //dump($asignaturas);die;
        // Asignaturas faltantes que le corresponde al curso
        /*$variable = '3';
        $asignaturasTecnicas = $em->createQueryBuilder()
                            ->select('ati')
                            ->from('SieAppWebBundle:AsignaturaTipo','ati')
                            ->where('ati.id like :asigTec')
                            ->setParameter('asigTec',(integer)('%' . $variable . '%'))
                            ->getQuery()
                            ->getResult();
        */
        $asignaturasTecnicas = array();
        $idsValidos = array(6404,5782,30103,30119,30104,5991,30140,5998,5997,6419,992,5692,406,5697,5699,908,30163,30164,30165,5573,5733,5588,5738,
                            5742,5742,5744,5746,5593,5594,5612,5633,5613,5634,5614,5636,5578,5596,5615,5616,5637,5638,5639,5574,5580,5581,5597,5598,5599,5600,5617,5618,5619,5620,30022,5569,5622,5644,5586,5587,5604,5605,5606,5624,5625,5646,5642,
                            30179,30180,30157,30146,30149,30190,30151,30152,30153,30154,30178,30082,5763,5794,30068,5797,30156,
                            6401,6103,6203,6303,5800,5793,30070,30177,30161,30162,
                            32768,32769,32770,32771,32772,32773,32774,32775,32776,32777,32778,32779,32780,32781,32782,32783,32784,32785,32786,32787,32788,32789,32790,32791,32792,32793,32794,32795,32796,32797,32798,32799,32800,32801,32802,32803, 30074, 32804, 7611, 32805, 32806, 30160, 32807, 32808, 32809, 32810, 32811, 32812, 32813, 32814, 30073, 9685, 9686, 30096, 5658, 30084,30085, 32815, 30087, 30075, 32816, 32817, 32818, 1016, 32819, 30067, 32820, 32821, 32822, 30077, 32823, 32824, 32825, 32826, 32827, 5668, 5669, 30091, 5621, 5643, 30083, 30086, 5673, 30092, 9683, 32828, 30081, 30075, 5757, 5761, 5765, 32829
                            );
        for($i=30;$i<40;$i++){
            $idsValidos[] = $i;
        }
        for($i=300;$i<400;$i++){
            $idsValidos[] = $i;
        }
        for($i=3000;$i<4000;$i++){
            $idsValidos[] = $i;
        }

        if(($nivel == 13) or ($nivel == 3)){
            $asignaturasTecnicas = $em->createQueryBuilder()
                                ->select('at')
                                ->from('SieAppWebBundle:AsignaturaTipo','at')
                                ->where('at.id IN (:idsValidos)')
                                ->andWhere('at.id NOT IN (:cursoOferta)')
                                ->setParameter('idsValidos',$idsValidos)
                                ->setParameter('cursoOferta',$actuales)
                                ->orderBy('at.asignatura','ASC')
                                ->getQuery()
                                ->getResult();
        }
        //dump($asignaturasTecnicas);die;

        $operativo = $this->operativo($sie,$gestion);
        $nivelCurso = $aInfoUeducativa['ueducativaInfo']['nivel'];
        $gradoParaleloCurso = $aInfoUeducativa['ueducativaInfo']['grado']." - ".$aInfoUeducativa['ueducativaInfo']['paralelo'];

        return array('cursoOferta'=>$cursoOferta,'asignaturas'=>$asignaturas,'asignaturasTecnicas'=>$asignaturasTecnicas, 'infoUe'=>$infoUe, 'operativo'=>$operativo,'nivel'=>$nivel,'grado'=>$grado,'nivelCurso'=>$nivelCurso,'gradoParaleloCurso'=>$gradoParaleloCurso) ;
    }

    public function literal($num){
        switch ($num) {
            case '0': $lit = 'Inicio de gestión'; break;
            case '1': $lit = '1er Bimestre'; break;
            case '2': $lit = '2do Bimestre'; break;
            case '3': $lit = '3er Bimestre'; break;
            case '4': $lit = '4to Bimestre'; break;
            case '6': $lit = '1er Trimestre'; break;
            case '7': $lit = '2do Trimestre'; break;
            case '8': $lit = '3er Trimestre'; break;
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

    public function maestrosAction(Request $request){
        $ieco = $request->get('idco');
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);

        //get the values throght the infoUe
        $sie = $aInfoUeducativa['requestUser']['sie'];
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
        $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
        $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];

        $em = $this->getDoctrine()->getManager();
        $maestrosMateria = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('institucioneducativaCursoOferta'=>$ieco));

        $arrayMaestros = array();
        if($gestion <= 2012 or ($gestion == 2013 and $grado > 1 and $nivel != 11) or ($gestion == 2013 and $grado == (1 or 2 ) and $nivel == 11) ){
            // trimestrales
            $inicio = 6;
            $fin = 8;
        }else{
            // Bimestrales
            $inicio = 0;
            $operativo = $this->operativo($sie,$gestion);
            if($operativo == 5){
                $fin = 4; //4;
            }else{
                $fin = $operativo;
            }

        }
        for($i=$inicio;$i<=$fin;$i++){
            $existe = false;
            foreach ($maestrosMateria as $mm) {
                if($mm->getNotaTipo()->getId() == $i){
                    $arrayMaestros[] = array(
                                            'id'=>$mm->getId(),
                                            'idmi'=>$mm->getMaestroInscripcion()->getId(),
                                            'horas'=>$mm->getHorasMes(),
                                            'idNotaTipo'=>$mm->getNotaTipo()->getId(),
                                            'periodo'=>$this->literal($i),
                                            'idco'=>$ieco);
                    $existe = true;
                    break;
                }
            }
            if($existe == false){
                $arrayMaestros[] = array(
                                            'id'=>'nuevo',
                                            'idmi'=>'',
                                            'horas'=>'',
                                            'idNotaTipo'=>$i,
                                            'periodo'=>$this->literal($i),
                                            'idco'=>$ieco);
            }
        }
        $maestros = $em->createQueryBuilder()
                        ->select('mi')
                        ->from('SieAppWebBundle:MaestroInscripcion','mi')
                        ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','WITH','mi.institucioneducativa = ie.id')
                        ->innerJoin('SieAppWebBundle:GestionTipo','gt','WITH','mi.gestionTipo = gt.id')
                        ->innerJoin('SieAppWebBundle:Persona','p','WITH','mi.persona = p.id')
                        ->innerJoin('SieAppWebBundle:CargoTipo','ct','with','mi.cargoTipo = ct.id')
                        ->innerJoin('SieAppWebBundle:RolTipo','rt','with','ct.rolTipo = rt.id')
                        ->where('ie.id = :sie')
                        ->andWhere('gt.id = :gestion')
                        ->andWhere('rt.id = 2')
                        ->orderBy('p.paterno','ASC')
                        ->addOrderBy('p.materno','ASC')
                        ->addOrderBy('p.nombre','ASC')
                        ->setParameter('sie',$sie)
                        ->setParameter('gestion',$gestion)
                        ->getQuery()
                        ->getResult();

        $operativo = $this->operativo($sie,$gestion);

        return $this->render('SieHerramientaBundle:InfoEstudianteAreasRequest:maestros.html.twig',array('maestrosCursoOferta'=>$arrayMaestros, 'maestros'=>$maestros,'ieco'=>$ieco,'operativo'=>$operativo));
    }

    public function maestrosAsignarAction(Request $request){
        $iecom = $request->get('iecom');
        $ieco = $request->get('ieco');
        $idmi = $request->get('idmi');
        $idnt = $request->get('idnt');
        $horas = $request->get('horas');
        /*dump($iecom);
        dump($ieco);
        dump($idmi);
        dump($idnt);
        dump($horas);die;*/

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta_maestro');")->execute();
        for($i=0;$i<count($iecom);$i++){
            if($horas[$i] == ''){
                $horasNum = 0;
            }else{
                $horasNum = $horas[$i];
            }
            if($iecom[$i] == 'nuevo' and $idmi[$i] != ''){
                $newCOM = new InstitucioneducativaCursoOfertaMaestro();
                $newCOM->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($ieco[$i]));
                $newCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idmi[$i]));
                $newCOM->setHorasMes($horasNum);
                $newCOM->setFechaRegistro(new \DateTime('now'));
                $newCOM->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idnt[$i]));
                $newCOM->setEsVigenteMaestro('t');
                $em->persist($newCOM);
                $em->flush();
            }else{
                if($idmi[$i] != ''){
                    $updateCOM = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->find($iecom[$i]);
                    $updateCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idmi[$i]));
                    $updateCOM->setHorasMes($horasNum);
                    $updateCOM->setFechaModificacion(new \DateTime('now'));
                    $updateCOM->setEsVigenteMaestro('t');
                    $em->flush();
                }
            }
        }
        $response = new JsonResponse();
        return $response->setData(array('ieco'=>$ieco[0]));
    }
}
