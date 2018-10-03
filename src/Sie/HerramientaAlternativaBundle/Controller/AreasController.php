<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\SuperiorModuloPeriodo;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\AltModuloemergente;

/**
 * EstudianteInscripcion controller.
 *
 */
class AreasController extends Controller {

    public $session;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //get the send values
        $infoUe = $request->get('infoUe');
        $arrInfoUe = unserialize($infoUe);
        
        // dump($arrInfoUe);die;
        // check if the course is PRIMARIA
        if( $arrInfoUe['ueducativaInfoId']['sfatCodigo'] == 15 &&
            $arrInfoUe['ueducativaInfoId']['setId'] == 13 &&
            $arrInfoUe['ueducativaInfoId']['periodoId'] == 3
          ){
            //set the All data about curricula on the course
            $createNewCurricula = $this->get('funciones')->loadCurriculaCurso($infoUe);
            $templateToView = 'indexprimaria.html.twig';
        }else{
            $templateToView = 'index.html.twig';
        }

        $data = $this->getAreas($infoUe);
        return $this->render('SieHerramientaAlternativaBundle:Areas:'.$templateToView, $data);

        
    }

    public function areasaddAction(Request $request) {
        $infoUe = $request->get('infoUe');
        $idAsignatura = $request->get('ida');
        $gestion = $this->session->get('ie_gestion');

        $aInfoUeducativa = unserialize($infoUe);
        $idCurso = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
//        dump($idCurso);
//        die('f');
        try {
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
            //$sip = $curso->getSuperiorInstitucioneducativaPeriodo();

//            $smt = $em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->findOneBy(array('id' => $idAsignatura));
//            $smp = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->findOneBy(array('superiorModuloTipo' => $smt, 'institucioneducativaPeriodo' => $sip));

//            dump($idAsignatura);
//            die;
            $smp = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($idAsignatura);
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();

            $ieco = new InstitucioneducativaCursoOferta();
            $ieco->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find('0'));
            $ieco->setInsitucioneducativaCurso($curso);
            $ieco->setSuperiorModuloPeriodo($smp);
            $ieco->setHorasmes(0);
            $em->persist($ieco);
            $em->flush();

            $em->getConnection()->commit();
            // Mostramos nuevamente las areas del curso
            $data = $this->getAreas($infoUe);
            return $this->render('SieHerramientaAlternativaBundle:Areas:index.html.twig', $data);
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!' . $ex));
        }
    }

    public function areasdeleteAction(Request $request) {
        $infoUe = $request->get('infoUe');
        $coid = $request->get('idco');
        $smpid = $request->get('smpId');

        // eliminamos el area del curso
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            
            $iecoen = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($coid);
            $iecoma = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('institucioneducativaCursoOferta' => $iecoen));
            $easig = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('institucioneducativaCursoOferta' => $iecoen));
            $enota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura' => $easig));

            //dump($iecoen);die;
            foreach($enota as $enota_aux){
                $em->remove($enota_aux);
            }
            
            foreach($easig as $easig_aux){
                $em->remove($easig_aux);
            }
            
            foreach($iecoma as $iecoma_aux){
                $em->remove($iecoma_aux);
            }

            $em->remove($iecoen);
            
            $em->flush();

            $em->getConnection()->commit();
            // Mostramos nuevamente las areas del curso
            $data = $this->getAreas($infoUe);
            return $this->render('SieHerramientaAlternativaBundle:Areas:index.html.twig', $data);
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
//            return $this->render('SieHerramientaBundle:InfoEstudianteAreas:index.html.twig',$data);
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!' . $ex));
        }
    }

    public function getAreas($infoUe) {
        $aInfoUeducativa = unserialize($infoUe);
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        //$iecId = '';
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        //dump($iecId);dump($nivel);dump($grado);die;
        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        if($nivel == 15 || $nivel == 5){
            try {                
                $iePeriodo = $em->createQueryBuilder()
                    ->select('g')
                    ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo = g.id')
                    ->where('h.id = :idCurso')
                    ->setParameter('idCurso', $iecId)
                    ->getQuery()
                    ->getResult();
                //dump($iePeriodo);die;
                $moduloPeriodo = $em->createQueryBuilder()
                    ->select('l')
                    ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo = g.id')
                    ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'k', 'WITH', 'g.id = k.institucioneducativaPeriodo')
                    ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'l', 'WITH', 'l.id = k.superiorModuloTipo ')
                    ->where('h.id = :idCurso')
                    ->setParameter('idCurso', $iecId)
                    ->getQuery()
                    ->getResult();
                //dump($moduloPeriodo);die;                
                    if($moduloPeriodo) {                    
                        $modulos = $em->createQueryBuilder()
                        ->select('l')
                        ->from('SieAppWebBundle:SuperiorModuloTipo' ,'l')
                        ->where('l.codigo IN (:codigos)')
                        ->andWhere('l.id NOT IN (:modulos)')
                        ->setParameter('codigos', array(401,402,403,404,408))
                        ->setparameter('modulos', $moduloPeriodo)
                        ->getQuery()
                        ->getResult();
                    }else {
                        $modulos = $em->createQueryBuilder()
                        ->select('l')
                        ->from('SieAppWebBundle:SuperiorModuloTipo' ,'l')
                        ->where('l.codigo IN (:codigos)')
                        ->setParameter('codigos', array(401,402,403,404,408))
                        ->getQuery()
                        ->getResult();
                    }                
                //dump($modulos); die;
                foreach ($modulos as $modulo) {
                    //die("abc");        
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                    $smp = new SuperiorModuloPeriodo();
                    $smp->setSuperiorModuloTipo($modulo);
                    $smp->setInstitucioneducativaPeriodo($iePeriodo[0]);
                    $smp->setHorasModulo(0);
                    $em->persist($smp);
                    $em->flush();
                }            
                $em->getConnection()->commit();
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
            }
        }

        // Curso oferta asignaturas del curso
        $cursoOferta = $em->createQueryBuilder()
                ->select('l.id as smpid, k.modulo, g.id as iecoid, k.codigo as codigo')
                ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'g')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.id = g.insitucioneducativaCurso')                
                ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'l', 'WITH', 'l.id = g.superiorModuloPeriodo')              
                ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'k', 'WITH', 'k.id = l.superiorModuloTipo')              
                ->where('h.id = :idCurso')
                ->setParameter('idCurso', $iecId)
                ->getQuery()
                ->getResult();
        
//                ->select('h.id as iecid, l.id as id, l.modulo as modulo, l.codigo as codigo, k.id as smpId, m.id as iecoid, g.id as siep')
//                ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g')
//                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo = g.id')
//                ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'k', 'WITH', 'g.id = k.institucioneducativaPeriodo')
//                ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'l', 'WITH', 'l.id = k.superiorModuloTipo ')
//                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'm', 'WITH', 'm.superiorModuloPeriodo=k.id')
//                ->where('h.id = :idCurso')
//                ->setParameter('idCurso', $iecId)
//                ->getQuery()
//                ->getResult();
        
//        dump($cursoOferta);
//        die;

        $actuales = array();
        foreach ($cursoOferta as $co) {
            $actuales[] = $co['smpid'];
        }
        
        //dump($iecId);  dump($actuales); die;
        
        if($actuales){
            $curso = $em->createQueryBuilder()
                ->select('l.id as id, l.modulo as modulo, l.codigo as codigo, k.id as smpId, g.id as siep, h.id as iecId')
                ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo = g.id')
                ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'k', 'WITH', 'g.id = k.institucioneducativaPeriodo')
                ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'l', 'WITH', 'l.id = k.superiorModuloTipo ')
                ->where('h.id = :idCurso')
                //->andWhere('h.gestionTipo = :gestion')
                ->andWhere('k.id NOT IN (:actuales)')
                ->setParameter('idCurso', $iecId)
                //->setParameter('gestion', $gestion)
                ->setParameter('actuales', $actuales)
                ->getQuery()
                ->getResult();
        }
        else{
            $curso = $em->createQueryBuilder()
                ->select('l.id as id, l.modulo as modulo, l.codigo as codigo, k.id as smpId, h.id as iecId')
                ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo = g.id')
                ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'k', 'WITH', 'g.id = k.institucioneducativaPeriodo')
                ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'l', 'WITH', 'l.id = k.superiorModuloTipo ')
                ->where('h.id = :idCurso')
                //->andWhere('h.gestionTipo = :gestion')
                ->setParameter('idCurso', $iecId)
                //->setParameter('gestion', $gestion)
                ->getQuery()
                ->getResult();
        }
//        dump($iecId);
        //dump($curso); die;

//        $todas = array();
//        foreach ($curso as $c) {
//            $todas[] = $c['id'];
//        }
//
//        $codAsignaturas = $todas;

        // Asignaturas faltantes que le corresponde al curso
//        $asignaturas = $em->createQueryBuilder()
//                ->select('smt')
//                ->from('SieAppWebBundle:SuperiorModuloTipo', 'smt')
//                ->where('smt.id IN (:idAsignaturas)')             
//                ->setParameter('idAsignaturas', $codAsignaturas)
//                ->getQuery()
//                ->getResult();
        
//        $asignaturas = $em->createQueryBuilder()
//                ->select('l.id as id, l.modulo as modulo, l.codigo as codigo, k.id as smpId')
//                ->from('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g')
//                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo = g.id')
//                ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo', 'k', 'WITH', 'g.id = k.institucioneducativaPeriodo')
//                ->innerJoin('SieAppWebBundle:SuperiorModuloTipo', 'l', 'WITH', 'l.id = k.superiorModuloTipo ')
//                ->where('h.id = :idCurso')
//                ->andWhere('l.id IN (:idAsignaturas)')
//                ->setParameter('idCurso', $iecId)
//                ->setParameter('idAsignaturas', $codAsignaturas)
//                ->getQuery()
//                ->getResult();
//        dump($curso);
//        die;
        $nivelCurso = $aInfoUeducativa['ueducativaInfo']['ciclo'];
        $gradoParaleloCurso = $aInfoUeducativa['ueducativaInfo']['grado'] . " - " . $aInfoUeducativa['ueducativaInfo']['paralelo'];
        return array('cursoOferta' => $cursoOferta, 'asignaturas' => $curso, 'infoUe' => $infoUe, 'operativo' => '', 'nivel' => $nivel, 'grado' => $grado, 'nivelCurso' => $nivelCurso, 'gradoParaleloCurso' => $gradoParaleloCurso);
    }

    public function areamaestroAction(Request $request) {

        $ieco = $request->get('idco');
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);

        $sie = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        $em = $this->getDoctrine()->getManager();

        $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($ieco);
        
        $notaT = $em->getRepository('SieAppWebBundle:NotaTipo')->find(0);

        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro');
        $query = $repository->createQueryBuilder('iecom')
                ->where('iecom.institucioneducativaCursoOferta = :curso')
                //->andWhere('iecom.notaTipo = :nota')
                ->setParameter('curso', $cursoOferta)
                //->setParameter('nota', $notaT)
                ->getQuery();

        $maestrosMateria = $query->getResult();
        
        $arrayMaestros = array();

        if ($maestrosMateria) {
            foreach ($maestrosMateria as $mm) {
                $arrayMaestros[] = array(
                    'id' => $mm->getId(),
                    'idmi' => $mm->getMaestroInscripcion()->getId(),
                    'horas' => $mm->getHorasMes(),
                    'idNotaTipo' => 0,
                    'periodo' => $periodo,
                    'idco' => $ieco);
            }
        } else {
            $arrayMaestros[] = array(
                'id' => 'nuevo',
                'idmi' => '',
                'horas' => '',
                'idNotaTipo' => 0,
                'periodo' => $periodo,
                'idco' => $ieco);
        }

        $query = $em->createQuery(
                        'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.rolTipo IN (:id) ORDER BY ct.id')
                ->setParameter('id', array(2));
        $cargosArray = $query->getResult();

        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');

        $query = $repository->createQueryBuilder('mi')
                ->select('mi')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
                ->innerJoin('SieAppWebBundle:FormacionTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'isuc', 'WITH', 'mi.institucioneducativaSucursal = isuc.id')
                ->innerJoin('SieAppWebBundle:CargoTipo', 'ct', 'with', 'mi.cargoTipo = ct.id')
                ->innerJoin('SieAppWebBundle:RolTipo', 'rt', 'with', 'ct.rolTipo = rt.id')
                ->where('mi.institucioneducativa = :idInstitucion')
                ->andWhere('mi.gestionTipo = :gestion')
                ->andWhere('mi.cargoTipo IN (:cargos)')
                ->andWhere('mi.periodoTipo = :periodo')
                ->andWhere('mi.institucioneducativaSucursal = :sucursal')
                ->andWhere('mi.esVigenteAdministrativo = :vigente')
                ->setParameter('idInstitucion', $sie)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargos', $cargosArray)
                ->setParameter('periodo', $periodo)
                ->setParameter('sucursal', $sucursal)
                ->setParameter('vigente', 't')
                ->orderBy('p.paterno')
                ->addOrderBy('p.materno')
                ->addOrderBy('p.nombre')
                ->getQuery();

        $maestros = $query->getResult();

        return $this->render($this->session->get('pathSystem') . ':Areas:maestros.html.twig', array(
                    'maestrosCursoOferta' => $arrayMaestros,
                    'maestros' => $maestros,
                    'ieco' => $ieco,
                    'operativo' => $periodo)
        );
    }

    public function maestrosAsignarAction(Request $request){
        $iecom = $request->get('iecom');
        $ieco = $request->get('ieco');
        $idmi = $request->get('idmi');
        $idnt = $request->get('idnt');
        $horas = $request->get('horas');

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

    public function areaEmergenteAction(Request $request) {
        $ieco = $request->get('idco');

        $sie = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        $em = $this->getDoctrine()->getManager();

        $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($ieco);

        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($cursoOferta->getInsitucioneducativaCurso());

        $emergente = $em->getRepository('SieAppWebBundle:AltModuloemergente')->findOneBy(array('institucioneducativaCurso' => $curso->getId()));

        return $this->render($this->session->get('pathSystem') . ':Areas:emergente.html.twig', array(
                    'iec' => $curso->getId(),
                    'ieco' => $ieco,
                    'operativo' => $periodo,
                    'emergente' => $emergente,
            )
        );
    }

    public function emergenteAsignarAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id_usuario = $this->session->get('userId');
        $ieco = $request->get('ieco');
        $iec = $request->get('iec');
        $emergente = mb_strtoupper($request->get('emergente'), 'utf-8');
        
        $moduloEmergente = $em->getRepository('SieAppWebBundle:AltModuloemergente')->findOneBy(array('institucioneducativaCurso' => $iec));
        if($moduloEmergente){
            $moduloEmergente->setModuloEmergente($emergente);
            $moduloEmergente->setUsuario($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($id_usuario));
            $moduloEmergente->setFechaRegistro(new \DateTime('now'));
            $em->persist($moduloEmergente);
            $em->flush();
        } else {
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('alt_moduloemergente');")->execute();
            $newEmergente = new AltModuloemergente();
            $newEmergente->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iec));
            $newEmergente->setModuloEmergente($emergente);
            $newEmergente->setUsuario($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($id_usuario));
            $newEmergente->setFechaRegistro(new \DateTime('now'));
            $em->persist($newEmergente);
            $em->flush();
            dump($newEmergente);
        }

        $response = new JsonResponse();
        return $response->setData(array(
            'ieco'=>$ieco,
            'iec'=>$iec,
            'emergente'=>$emergente
        ));
    }

}
