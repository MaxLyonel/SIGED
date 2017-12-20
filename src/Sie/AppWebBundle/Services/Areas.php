<?php

namespace Sie\AppWebBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\LogTransaccion;
use JMS\Serializer\SerializerBuilder;
use Sie\AppWebBundle\Services\Funciones;
use Sie\AppWebBundle\Services\Notas;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;

class Areas {

	protected $em;
	protected $router;
    protected $session;
    protected $funciones;
    protected $notas;

	public function __construct(EntityManager $entityManager, Router $router) {
		$this->em = $entityManager;
		$this->router = $router;
        $this->session = new Session();
	}

    public function setServices(Funciones $funciones,Notas $notas){
        $this->funciones = $funciones;
        $this->notas = $notas;
    }

    public function tecnicas($grado){
        $asignaturasTecnicas = array();
        $idsAsignaturas = array(6404,5782,30103,30119,30104,5991,30140,5998,5997,6419,992,5692,406,5697,5699,908,30163,30164,30165,5573,5733,5588,5738,
                            5742,5742,5744,5746,5593,5594,5612,5633,5613,5634,5614,5636,5578,5596,5615,5616,5637,5638,5639,5574,5580,5581,5597,5598,5599,5600,5617,5618,5619,5620,30022,5569,5622,5644,5586,5587,5604,5605,5606,5624,5625,5646,5642,
                            30179,30180,30157,30146,30149,30190,30151,30152,30153,30154,30178,30082,5763,5794,30068,5797,30156,
                            6401,6103,6203,6303,5800,5793,30070,30177,30161,30162,
                            32768,32769,32770,32771,32772,32773,32774,32775,32776,32777,32778,32779,32780,32781,32782,32783,32784,32785,32786,32787,32788,32789,32790,32791,32792,32793,32794,32795,32796,32797,32798,32799,32800,32801,32802,32803, 30074, 32804, 7611, 32805, 32806, 30160, 32807, 32808, 32809, 32810, 32811, 32812, 32813, 32814, 30073, 9685, 9686, 30096, 5658, 30084,30085, 32815, 30087, 30075, 32816, 32817, 32818, 1016, 32819, 30067, 32820, 32821, 32822, 30077, 32823, 32824, 32825, 32826, 32827, 5668, 5669, 30091, 5621, 5643, 30083, 30086, 5673, 30092, 9683, 32828, 30081, 30075, 5757, 5761, 5765, 32829
                            );
        for($i=30;$i<40;$i++){
            $idsAsignaturas[] = $i;
        }
        for($i=300;$i<400;$i++){
            $idsAsignaturas[] = $i;
        }
        for($i=3000;$i<4000;$i++){
            $idsAsignaturas[] = $i;
        }

        

        return $idsAsignaturas;
    }

    public function humanisticas($gestion,$nivel,$grado,$tipoUE){
        $tipoUEId = $tipoUE['id'];
        $gradoUEId = $tipoUE['grado'];

        $matpri = array(203, 204, 205, 207, 250, 252, 253, 254, 257);
        $matsec = array(203, 204, 205, 207, 221, 251, 252, 253, 254, 255, 257, 258);

        $matiniant = array(101, 102, 103, 104, 105);
        $matpriant = array(201, 203, 204, 205, 206, 207, 208, 209, 210, 251, 258);
        $matsecantter = array(301, 302, 303, 304, 305, 307, 308, 309, 310, 311, 312, 313, 315, 316, 317, 318, 319, 320, 321);

        $matsecantcua = array(301, 302, 303, 304, 305, 309, 313, 316, 317, 318, 319);
        $matsecantquisex = array(301, 302, 303, 305, 307, 308, 310, 311, 312, 313, 315, 316, 317, 318, 319, 362);


        $matinia = array(1000, 1001, 1002, 1003);
        $matpria = array(1011, 1012, 1013, 1014, 1015, 1016, 1017, 1018, 1019);

        $matseca = array(1031, 1032, 1033, 1034, 1035, 1036, 1037, 1038, 1040, 1043, 1044);
        $matsecb = array(1031, 1032, 1033, 1034, 1035, 1036, 1037, 1038, 1040, 1041, 1042, 1043, 1044);
        $matsecc = array(1031, 1032, 1033, 1034, 1035, 1036, 1037, 1040, 1041, 1042, 1043, 1044);

        $matsecd = array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044);
        $matsece = array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044,1045);
        $matsecf = array(1031,1032,1033,1034,1035,1036,1037,1039,1040,1043,1044,1045);

        $matsecg = array(1031,1032,1033,1034,1035,1036,1037,1040,1043,1044,1045); // PAra gestiones 2016 en adelante grados 5 y 6

        // Para unidades educativas nocturnas
        $matnocta = array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044); // para 1 y 2
        $matnoctb = array(1031,1032,1033,1037,1040,1043,1044,1045); // para 3,4,5 y 6


        $idsAsignaturas = array();
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
                            default:
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
            default:
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
                                if($tipoUEId == 6){
                                    // Para unidades educativas nocturnas
                                    $idsAsignaturas = $matnocta;
                                }else{
                                    $idsAsignaturas = $matsecd;
                                }
                                break;
                            case 3:
                            case 4:
                                if($tipoUEId == 6){
                                    // Para unidades educativas nocturnas
                                    $idsAsignaturas = $matnoctb;
                                }else{
                                    $idsAsignaturas = $matsece;
                                }
                                break;
                            case 5:
                            case 6:
                                if($tipoUEId == 6){
                                    // Para unidades educativas nocturnas
                                    $idsAsignaturas = $matnoctb;
                                }else{
                                    if($tipoUEId == 1 and $grado <= $gradoUEId){

                                        $idsAsignaturas = $matsecf;
                                    }else{
                                        $idsAsignaturas = $matsecg;
                                    }
                                }
                                break;
                        }
                        break;
                }
                break;
        }

        return $idsAsignaturas;
    }

    public function getAreas($idCurso){
        try {
            $curso = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
            $sie = $curso->getInstitucioneducativa()->getId();
            $gestion = $curso->getGestionTipo()->getId();
            $nivel = $curso->getNivelTipo()->getId();
            $grado = $curso->getGradoTipo()->getId();
            $paralelo = $curso->getParaleloTipo()->getId();

            $tipoUE = $this->funciones->getTipoUE($sie,$gestion);
            $asignaturas = array();
            $posiblesEliminar = array();
            if($tipoUE){
                switch ($tipoUE['id']) {
                    case 2: // Ues tecnicas
                        $asignaturas = $this->tecnicas($grado);
                        break;
                    default: // Todas las demas ues
                        $asignaturas = $this->humanisticas($gestion,$nivel,$grado,$tipoUE);

                        /** //////////////////////////////
                         * Validacion para ue AMERINST 
                         * Aunque sea plena no reporta materia especializada en los paralelos a,b,c,d,e
                         * LOs demas paralelos si reportan esta materia
                         *///////////////////////////////*/
                        if($sie == '80730460' and $gestion == 2017 and $nivel == 13 and ($grado == 5 or $grado == 6) and $paralelo <= 5){
                            $asignaturas = array(1031,1032,1033,1034,1035,1036,1037,1040,1043,1044,1045);
                        }
                        //////////////////////////////////////////

                        // Para todas las ues que puedan eliminar materias tecnica general y especializada
                        if(in_array($tipoUE['id'], array(2,3,4,5)) and $grado > 2){
                            $posiblesEliminar = array(1038,1039);
                        }

                        // Posibles a eliminar para ues nocturanas (6)
                        if($tipoUE['id'] == 6){
                            if($gestion >= 2016 and $nivel == 13){
                                switch ($grado) {
                                    case 1:
                                    case 2:
                                        $posiblesEliminar = array(1039,1045);
                                        break;
                                    case 3:
                                    case 4:
                                    case 5:
                                    case 6:
                                        $posiblesEliminar = array(1034,1035,1036,1038,1039);
                                        break;
                                }
                            }
                        }
                        // Posibles eliminar si es plena segun el grado
                        if($tipoUE['id'] == 1){
                            if($grado == 6 and $tipoUE['grado'] == 5){
                                $posiblesEliminar = array(1039);
                            }
                        }
                        // Posibles a eliminar para ues en transformacion
                        if($tipoUE['id'] == 7){
                            if($grado == 4 and $tipoUE['grado'] == 3){
                                $posiblesEliminar = array(1038,1039);
                            }else{
                                $posiblesEliminar = array(1039);
                            }
                        }

                        break;
                }
                // Asignaturas del curso
                // Curso oferta asignaturas del curso
                $cursoOferta = $this->em->createQueryBuilder()
                                    ->select('ieco.id, at.id as idAsignatura, at.asignatura')
                                    ->from('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco')
                                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ieco.insitucioneducativaCurso = iec.id')
                                    ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','WITH','ieco.asignaturaTipo = at.id')
                                    ->where('iec.id = :idCurso')
                                    ->setParameter('idCurso',$idCurso)
                                    ->orderBy('at.id','ASC')
                                    ->getQuery()
                                    ->getResult();

                $actuales = array();
                foreach ($cursoOferta as $co) {
                    $actuales[] = $co['idAsignatura'];
                }

                if($this->session->get('roluser') == 8){
                    $posiblesEliminar = $actuales;
                }

                // PAra ues tecnicas se les da la opcion de eliminar sus areas
                if($tipoUE['id'] == 2){
                    $posiblesEliminar = $actuales;
                }
                
                if(count($actuales)==0){
                  $actuales[]=1;
                }
                // Asignaturas faltantes que le corresponde al curso
                $faltantes = $this->em->createQueryBuilder()
                                    ->select('at')
                                    ->from('SieAppWebBundle:AsignaturaTipo','at')
                                    //->innerJoin('SieAppWebBundle:AsignaturaNivelTipo','ant','WITH','at.asignaturaNivel = ant.id')
                                    //->where('ant.id = :idNivel')
                                    ->where('at.id IN (:idAsignaturas)')
                                    ->andWhere('at.id NOT IN (:cursoOferta)')
                                    //->setParameter('idNivel',$nivel)
                                    ->setParameter('idAsignaturas',$asignaturas)
                                    ->setParameter('cursoOferta',$actuales)
                                    ->getQuery()
                                    ->getResult();

                // Verificamos si se puede adicionar materias, en base al operativo antes de tercer bimestre
                if($gestion == $this->session->get('currentyear')){
                    $operativo = $this->funciones->obtenerOperativo($sie,$gestion);
                    if($operativo <= 3 or ($tipoUE['id'] == 3 and in_array($nivel, array(3,13)))){
                        $vista = 1;
                    }else{
                        $vista = 0;
                    }
                }else{
                    $vista = 1;
                }

                return array(
                    'asignaturas'=>$asignaturas,
                    'cursoOferta'=>$cursoOferta,
                    'faltantes'=>$faltantes,
                    'tipoUE'=>$tipoUE['id'],
                    'idCurso'=>$idCurso,
                    'posiblesEliminar'=>$posiblesEliminar,
                    'vista'=>$vista
                );
            } 
            return null;
        } catch (Exception $e) {
            return null;
        }
        
    }

    public function delete($idCursoOferta){
        $cursoOferta = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta);

        $cursosOfertasSimilares = $this->em->createQuery(
                        'SELECT ieco FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
            INNER JOIN ieco.insitucioneducativaCurso iec
            INNER JOIN ieco.asignaturaTipo at
            WHERE iec.id = :idCurso
            AND at.id = :idAsignatura')
                ->setParameter('idCurso', $cursoOferta->getInsitucioneducativaCurso()->getId())
                ->setParameter('idAsignatura', $cursoOferta->getAsignaturaTipo()->getId())
                ->getResult();
        
        foreach ($cursosOfertasSimilares as $co) {

            $estudianteAsignatura = $this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('institucioneducativaCursoOferta' => $co->getId()));

            foreach ($estudianteAsignatura as $ea) {
                // Eliminamos las notas
                $this->em->createQuery(
                                'DELETE FROM SieAppWebBundle:EstudianteNota en
                    WHERE en.estudianteAsignatura = :idEstAsig')
                        ->setParameter('idEstAsig', $ea->getId())->execute();
            }

            // Eliminamos en la tabla estudiante asignatura
            $this->em->createQuery(
                            'DELETE FROM SieAppWebBundle:EstudianteAsignatura ea
                WHERE ea.institucioneducativaCursoOferta = :idCO')
                    ->setParameter('idCO', $co->getId())->execute();

            // Eliminamos en la tabla curso oferta maestro
            $this->em->createQuery('DELETE FROM SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro iecom
                              WHERE iecom.institucioneducativaCursoOferta = :idCO')
                              ->setParameter('idCO',$co->getId())->execute();

            // Registramos en la tabla de control
            // Eliminamos el curso oferta

            $cursoOfertaEliminar = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId());

            // Actualizamos los estados de matricula de los estudiantes
            $inscritos = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso'=>$cursoOferta->getInsitucioneducativaCurso()->getId()));
            foreach ($inscritos as $ins) {
                $this->notas->actualizarEstadoMatricula($ins->getId());
            }

            // Registro en el log
            /*
            $this->funciones->setLogTransaccion(
                $cursoOfertaEliminar->getId(),
                'institucioneducativa_curso_oferta',
                'D',
                '',
                $cursoOfertaEliminar,
                '',
                'ACADEMICO',
                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );
            */

            $this->em->remove($cursoOfertaEliminar);


        }
        $this->em->flush();

        return new JsonResponse(array('msg'=>'ok'));
    }

    public function nuevo($idCurso,$idAsignatura){
        try {
            $this->em->getConnection()->beginTransaction();

            $curso = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
            $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();
            // Registramos la nueva Área
            $newArea = new InstitucioneducativaCursoOferta();
            $newArea->setAsignaturaTipo($this->em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($idAsignatura));
            $newArea->setInsitucioneducativaCurso($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso));
            $newArea->setHorasmes(0);
            $this->em->persist($newArea);
            $this->em->flush();

            // Registro de materia curso oferta en el log
            /*
            $this->funciones->setLogTransaccion(
                $newArea->getId(),
                'institucioneducativa_curso_oferta',
                'C',
                '',
                $newArea,
                '',
                'ACADEMICO',
                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );*/

            // Actualizamos el id de la tabla estudiante asignatura
            $query = $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');")->execute();
            // Listamos los estudinates inscritos
            // para registrar el curso a los estudiantes
            $inscritos = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso' => $idCurso));

            foreach ($inscritos as $ins) {
                // Verificamos si el estudiante ya tiene la asignatura
                $estInscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array('estudianteInscripcion'=>$ins->getId(),'institucioneducativaCursoOferta'=>$newArea->getId()));
                if(!$estInscripcion){
                    $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');")->execute();
                    $newEstAsig = new EstudianteAsignatura();
                    $newEstAsig->setGestionTipo($this->em->getRepository('SieAppWebBundle:GestionTipo')->find($curso->getGestionTipo()->getId()));
                    $newEstAsig->setFechaRegistro(new \DateTime('now'));
                    $newEstAsig->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($ins->getId()));
                    $newEstAsig->setInstitucioneducativaCursoOferta($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($newArea->getId()));
                    $this->em->persist($newEstAsig);
                    $this->em->flush();

                    // Registro de materia para estudiantes estudiante_asignatura en el log
                    /*
                    $this->funciones->setLogTransaccion(
                        $newEstAsig->getId(),
                        'estudiante_asignatura',
                        'C',
                        '',
                        $newEstAsig,
                        '',
                        'ACADEMICO',
                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                    );*/
                }

                // Actualizamos los estados de matricula de los estudiantes
                
                $this->notas->actualizarEstadoMatricula($ins->getId());
            }

            $this->em->getConnection()->commit();

            return new JsonResponse(array('msg'=>'ok','idNewArea'=>$newArea->getId()));
        } catch (Exception $e) {
            $this->em->getConnection()->rollback();
            return new JsonResponse(array('msg'=>'error'));
        }
    }
}