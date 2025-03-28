<?php

namespace Sie\UniversityBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\UnivUniversidadCarrera;
use Sie\AppWebBundle\Entity\UnivSede;
use Sie\AppWebBundle\Entity\UnivNivelAcademicoTipo;
use Sie\AppWebBundle\Entity\UnivRegimenEstudiosTipo;
use Sie\AppWebBundle\Entity\UnivModalidadEnsenanzaTipo;
use Sie\AppWebBundle\Entity\UnivGradoacademicoTipo;

use Symfony\Component\HttpFoundation\JsonResponse;

class CarrerasController extends Controller
{
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->baseData = array('sedeId' => $this->session->get('sedeId'),  'userId' => $this->session->get('userId'));
    }

    private function kdecrypt($data){
        $data = hex2bin($data);
        return unserialize($data);
    }

    public function indexAction(Request $request)
    {
       
        //$form = $request->get('form');
        
        //$data = ($this->kdecrypt($form['data']));

        //dump($data); die;
        // univalle sede central = 62
        $sedeId = $this->session->get('sedeId');
        $arrData = array('sedeId'=> $sedeId);
        $gestiones = $this->get('univfunctions')->getAllOperative($arrData);
        //dump($gestiones); die;
        $maxgestion = $this->max_attribute_in_array($gestiones, 'gestion_tipo_id');
        
        $sedeId = $this->session->get('sedeId'); //$data['sedeId'];
        $userId = $this->session->get('userId'); //$data['userId'];


        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();  
        
        //contadores

        $query = "select count(*) from univ_universidad_carrera where univ_sede_id = " . $sedeId;
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $total_carreras = $po[0]['count'];

        $query = "select count(*) from univ_universidad_carrera where univ_sede_id = " . $sedeId . " and univ_nivel_academico_tipo_id = 1";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $total_carreras_pre = $po[0]['count'];

        $query = "select count(*) from univ_universidad_carrera where univ_sede_id = " . $sedeId . " and univ_nivel_academico_tipo_id = 2";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $total_carreras_post = $po[0]['count'];

       
        
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = $fechaActual->format('Y');
        
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

       
        $entityUsuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $id_usuario));

        $entityUnivSede = $em->getRepository('SieAppWebBundle:UnivSede')->findBy(array('usuario' => $id_usuario));
               
        $entityUnivSedeCentral = $em->getRepository('SieAppWebBundle:UnivSede')->findById($sedeId);

        $entityPregrado = $em->getRepository('SieAppWebBundle:UnivNivelAcademicoTipo')->findById(1);    
        $entityPostgrado = $em->getRepository('SieAppWebBundle:UnivNivelAcademicoTipo')->findById(2);    
        
        $entityUniv = $em->getRepository('SieAppWebBundle:UnivUniversidad')->findById(1);
        //dump($entityUniv);die;

        $carreras = $em->getRepository('SieAppWebBundle:UnivUniversidadCarrera')->findBy(array('univSede' => $entityUnivSedeCentral, 'univNivelAcademicoTipo' => $entityPregrado), array('carrera'=> 'ASC'));
        //dump($carreras); die;
        $carreras_post = $em->getRepository('SieAppWebBundle:UnivUniversidadCarrera')->findBy(array('univSede' => $entityUnivSedeCentral, 'univNivelAcademicoTipo' => $entityPostgrado), array('carrera'=> 'ASC'));
        
        $niveles = $em->getRepository('SieAppWebBundle:UnivNivelAcademicoTipo')->findAll();       
        $modalidad = $em->getRepository('SieAppWebBundle:UnivModalidadEnsenanzaTipo')->findAll();       
        //$grado_academico = $em->getRepository('SieAppWebBundle:UnivClaGradoAcademico')->findAll(); 
        //version 2 separado por nivel academico     
        $grado_academico_pre =  $em->getRepository('SieAppWebBundle:UnivGradoTipo')->findAll(); 
        $grado_academico_post =  $em->getRepository('SieAppWebBundle:UnivGradoTipo')->findAll(); 


        $regimen_estudios = $em->getRepository('SieAppWebBundle:UnivregimenEstudiosTipo')->findAll();       
        $periodo_academico = $em->getRepository('SieAppWebBundle:UnivPeriodoAcademicoTipo')->findAll();       
        
        $area_conocimiento = $em->getRepository('SieAppWebBundle:UnivAreaConocimientoTipo')->findAll();       
       
        return $this->render('SieUniversityBundle:Carreras:index.html.twig', array(
            'sedes' => $entityUnivSede,
            'central' => $entityUniv[0],
            'titulo' => "Carreras",
            'carreras' => $carreras,      
            'carreras_post' => $carreras_post,      
            'niveles' => $niveles,           
            'modalidad' => $modalidad,           
            //'grado_academico' => $grado_academico,           
            'grado_academico_pre' => $grado_academico_pre,           
            'grado_academico_post' => $grado_academico_post,           
            'regimen_estudios' => $regimen_estudios,           
            'periodo_academico' => $periodo_academico,   
            'total_carreras' => $total_carreras,
            'total_carreras_pre' => $total_carreras_pre,
            'total_carreras_post' => $total_carreras_post,
            'area_conocimiento' => $area_conocimiento,
            'last_gestion' => $maxgestion

        ));
        
    }


    public function infoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 
        $response = new JsonResponse();

        $carrera_id = $request->query->get('carrera_id');
        $gestion = $request->query->get('gestion');

        //comunicacion y medios digitales ** pregrado = 1511
        //CIENCIAS ECONÓMICAS Y ADMINISTRATIVAS **  = 972
        // gestion = 2022
       
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        //$carrera_id = 1582;

        $entityUnivSede = $em->getRepository('SieAppWebBundle:UnivSede')->findBy(array('usuario' => $id_usuario));

        //TODO: JALAR DE LA SESION ?
        $sedeId = $this->session->get('sedeId');
        //dump($sedeId); die; 62

        // para saber si el operatico esta abierto(true) o cerrado (false)
        $sql = "select activo from univ_registro_consolidacion
        where univ_sede_id = ".$sedeId." and gestion_tipo_id = ". $gestion;
        $stmt = $db->prepare($sql);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $opestatus = $po[0]['activo'];
        
        $entityUnivSedeCentral = $em->getRepository('SieAppWebBundle:UnivSede')->findById($sedeId); //43

        $es_indigena = 0;
        $nombre_universidad = $entityUnivSedeCentral[0]->getUnivUniversidad()->getUniversidad();
         //"UNIVERSIDAD PRIVADA DEL VALLE"
        if (strpos($nombre_universidad, 'INDÍGENA') !== false) {       
            $es_indigena = 1;
        }
        if (strpos($nombre_universidad, 'INDIGENA') !== false) {       
            $es_indigena = 1;
        }
        if (strpos($nombre_universidad, 'MILITAR') !== false) {       
            $es_indigena = 1;
        }
        if (strpos($nombre_universidad, 'POLI') !== false) {       
            $es_indigena = 1;
        }
       
        $carreraEntity = $em->getRepository('SieAppWebBundle:UnivUniversidadCarrera')->find($carrera_id);        
        $periodos = $carreraEntity->getUnivRegimenEstudiosTipo()->getId();
        $nivel_academico =  $carreraEntity->getUnivNivelAcademicoTipo()->getDescripcion();

        // 1: semestral
        // 2: anual
        //3 : modular
        
        $nro_periodos = 1;
        if($periodos != 2){
             $nro_periodos = 2;
        } 

               
        $entityUniv = $em->getRepository('SieAppWebBundle:UnivUniversidad')->findById(1);
        
        $gestiones = $em->getRepository('SieAppWebBundle:UnivRegistroConsolidacion')->findAll();      

        $arrData = array('sedeId'=> $sedeId);
        //$arrData = array('sedeId'=> $request->get(64));
        $gestiones = $this->get('univfunctions')->getAllOperative($arrData);
        //dump($gestiones); die;

        //$generos = $em->getRepository('SieAppWebBundle:UnivClaGenero')->findAll();   
        $generos = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();   

        if($nro_periodos == 1) {
            $periodos = $em->getRepository('SieAppWebBundle:UnivPeriodoAcademicoTipo')->findById(1);     
        }else{
            $periodos = $em->getRepository('SieAppWebBundle:UnivPeriodoAcademicoTipo')->findAll();    
        }

        $matriculas = $em->getRepository('SieAppWebBundle:UnivMatriculaNacionalidadBecaTipo')->findAll();  
        
        /*$matriculasestado = $em->getRepository('SieAppWebBundle:UnivEstadomatriculaTipo')->findAll( array(),array('id' => 'ASC'));    
        dump($matriculasestado); die;*/

        $sql = "select * from univ_estadomatricula_tipo order by 2";
        $stmt = $db->prepare($sql);
        $params = array();
        $stmt->execute($params);
        $matriculasestado = $stmt->fetchAll();
        //dump($matriculasestado); die;


        $cargos = $em->getRepository('SieAppWebBundle:UnivCargoTipo')->findAll();   

        //verificar si hay datos para le gestion y carrera, si no hay generar con ceros
        $this->verifica_univ_universidad_carrera_estudiante_estado($carrera_id, $nro_periodos, $gestion, $nivel_academico);
        $this->verifica_univ_matricula_nacionalidad_beca_tipo($carrera_id, $nro_periodos, $gestion, $nivel_academico);
        
        //solo si es universidad indigena
        $this->verifica_get_univ_universidad_carrera_docente_administrativo($carrera_id, $nro_periodos, $gestion);
      
        //dump('generated !'); die; 
        //datos estudiantes por genero y tipo beca


        $filas = $this->get_univ_matricula_nacionalidad_beca_tipo($carrera_id, $nro_periodos, $gestion, $nivel_academico);
        $totales1 = array();
        $t1 = 0;
        $t2 = 0;
        $t3 = 0;
        $t4 = 0;
       
        for($i = 0; $i < sizeof($filas); $i++ ){
            $t1 = $t1 + $filas[$i]['m1'];
            $t2 = $t2 + $filas[$i]['f1'];         
            //TODO: las del periodo 2
            if($nro_periodos == 2){
                $t3 = $t3 + $filas[$i]['m2'];
                $t4 = $t4 + $filas[$i]['f2'];              
            }

        }
        $totales = array(   
            'id' => 0,
            'matricula' => "TOTALES",        
            'm1'  => $t1,
            'f1'  => $t2,           
            'm2'  => $t3,
            'f2'  => $t4,
            
        );
       
        array_push($totales1, $totales);

        //solo para universidad indigena y otros
        $cargos_array = $this->get_univ_universidad_carrera_docente_administrativo($carrera_id, $nro_periodos, $gestion);
        $filas3 = $cargos_array;
        $totales3 = array();
        $t1 = 0;
        $t2 = 0;
        $t3 = 0;
        $t4 = 0;
       
        for($i = 0; $i < sizeof($filas3); $i++ ){
            $t1 = $t1 + $filas3[$i]['m1'];
            $t2 = $t2 + $filas3[$i]['f1'];         
            //TODO: las del periodo 2
            if($nro_periodos == 2){
                $t3 = $t3 + $filas3[$i]['m2'];
                $t4 = $t4 + $filas3[$i]['f2'];              
            }

        }
        $totales = array(   
            'id' => 0,
            'matricula' => "TOTALES",        
            'm1'  => $t1,
            'f1'  => $t2,           
            'm2'  => $t3,
            'f2'  => $t4,
            
        );
        array_push($totales3, $totales);

        //datos estudiantes por tipo de matricula
        /*--------------------------------------------------*/
        $tipo_matricula_array = $this->get_univ_universidad_carrera_estudiante_estado($carrera_id, $nro_periodos, $gestion, $nivel_academico);
        $filas2 = $tipo_matricula_array;
        $totales2 = array();
        $t1 = 0;
        $t2 = 0;
        $t3 = 0;
        $t4 = 0;
       
        for($i = 0; $i < sizeof($filas2); $i++ ){
            $t1 = $t1 + $filas2[$i]['m1'];
            $t2 = $t2 + $filas2[$i]['f1'];         
            //TODO: las del periodo 2
            if($nro_periodos == 2){
                $t3 = $t3 + $filas2[$i]['m2'];
                $t4 = $t4 + $filas2[$i]['f2'];              
            }

        }
        $totales = array(   
            'id' => 0,
            'matricula' => "TOTALES",        
            'm1'  => $t1,
            'f1'  => $t2,           
            'm2'  => $t3,
            'f2'  => $t4,
            
        );
       
        array_push($totales2, $totales);
              
        $data = array(            
            'periodos' => $nro_periodos,
            'array_estudiantes' => $filas,
            'array_cargos' => $cargos_array,
            'array_tipo_matricula' => $tipo_matricula_array
        );

        //ver si tiene dato en la gestion
        //univ_universidad_carrera_ctr
        $sindatoenlagestion = false;  // se muestra el boton
        $sql = "select univ_estadocarrera_tipo_id from univ_universidad_carrera_ctr where univ_universidad_carrera_id = " .$carrera_id . " and gestion_tipo_id = " . $gestion;
        $stmt = $db->prepare($sql);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $estado = $po[0]['univ_estadocarrera_tipo_id'];
        //dump($estado);die;
        if($estado == 2){$sindatoenlagestion = true;}
        
        return $this->render('SieUniversityBundle:Carreras:info.html.twig', array(
            'carrera_id' => $carrera_id,
            'gestion_id' =>  $gestion,
            'entity' => $carreraEntity,           
            'sedes' => $entityUnivSede,
            'central' => $entityUniv[0],
            'titulo' => "Carreras",                     
            'gestiones' => $gestiones,                     
            'generos' => $generos,                     
            'periodos' => $periodos,                     
            'matriculas' => $matriculas,  
            'matriculasestado' => $matriculasestado,
            'cargos' => $cargos,  
            'data' => $data,
            'totales1' => $totales1,
            'totales2' => $totales2,
            'totales3' => $totales3,
            'es_indigena' => $es_indigena,
            'opestatus' => $opestatus,
            'sindatoenlagestion' => $sindatoenlagestion,

           
        ));
        

    }

    public function newAction(Request $request)
    {
        date_default_timezone_set('America/La_Paz');
        $response = new JsonResponse();
        
        $fechaActual = new \DateTime(date('Y-m-d'));
        $sedeId = $this->session->get('sedeId');

        $nivel_academico_id = $request->get('nivel_academico_id');
        $modalidad_id = $request->get('modalidad_id');
        $regimen_id = $request->get('regimen_id');
        $grado_id = $request->get('grado_id');
        $duracion = $request->get('duracion');
        $duracion_anios = $request->get('duracion_anios');
        
        $area_conocimiento_id = $request->get('area_conocimiento_id');
       
        $fecha_apertura_tmp = $request->get('fecha_apertura');
        //15/12/2003
       
        $fecha_apertura = date('Y-m-d', strtotime($fecha_apertura_tmp));

        $em = $this->getDoctrine()->getManager();

        $carreraEntity = new UnivUniversidadCarrera();
       

        $carreraEntity->setUnivSede($em->getRepository('SieAppWebBundle:UnivSede')->find($sedeId));
        //$carreraEntity->setUnivAreaConocimiento($request->get('area_conocimiento'));

        $carreraEntity->setUnivNivelAcademicoTipo($em->getRepository('SieAppWebBundle:UnivNivelAcademicoTipo')->find($nivel_academico_id));
        $carreraEntity->setUnivRegimenEstudiosTipo($em->getRepository('SieAppWebBundle:UnivRegimenEstudiosTipo')->find($regimen_id));
        $carreraEntity->setUnivModalidadEnsenanzaTipo($em->getRepository('SieAppWebBundle:UnivModalidadEnsenanzaTipo')->find($modalidad_id));

        $carreraEntity->setCarrera($request->get('carrera'));
        $carreraEntity->setResolucion($request->get('resolucion'));  
        
        $carreraEntity->setUnivAreaConocimientoTipo($em->getRepository('SieAppWebBundle:UnivAreaConocimientoTipo')->find($area_conocimiento_id));  
        

        //$aux = $em->getRepository('SieAppWebBundle:UnivAreaConocimientoTipo')->find($area_conocimiento_id);
        //dump($aux); die;
        
        //fecha ??
        //rm_apertura ??
        //dump($fecha_apertura); die;
        $carreraEntity->setFechaApertura(new \DateTime( $fecha_apertura));
        
        $carreraEntity->setUnivGradoAcademicoTipo($em->getRepository('SieAppWebBundle:UnivGradoTipo')->find($grado_id));

        $carreraEntity->setDuracion($request->get('duracion'));
        $carreraEntity->setDuracionAnios($request->get('duracion_anios'));

        $carreraEntity->setEsSiesu(0);
        $carreraEntity->setEstado(0);
        $carreraEntity->setFechaRegistro(new \DateTime('now'));
        $carreraEntity->setFechaModificacion(new \DateTime('now'));

        //dump($carreraEntity); die; 
       
        $em->persist($carreraEntity);
        $em->flush();       

        $this->get('session')->getFlashBag()->add('carreraok', 'La carrera fue creada correctamente');
        return $this->redirect($this->generateUrl('carreras_index', array('op' => 'result')));


    }

    public function editAction(Request $request)
    {
        date_default_timezone_set('America/La_Paz');
        $response = new JsonResponse();
        
        //dump($request); die;
        //TODO: esto de donde ?
        $carrera_id = $request->get('carrera_id');
        $sedeId = $this->session->get('sedeId');
        $nivel_academico_id = $request->get('edit_nivel_academico_id');
        $modalidad_id = $request->get('edit_modalidad_id');
        $regimen_id = $request->get('edit_regimen_id');
        
        $grado_id = $request->get('edit_grado_id');
        $duracion = $request->get('edit_duracion');
        $duracion_anios = $request->get('edit_duracion_anios');

        $fecha_apertura_tmp = $request->get('edit_fecha_apertura');
        //15/12/2003
       
        $fecha_apertura = date('Y-m-d', strtotime($fecha_apertura_tmp));

        $em = $this->getDoctrine()->getManager();

        $area_conocimiento_id = $request->get('edit_area_conocimiento_id');
       
        $carreraEntity = $em->getRepository('SieAppWebBundle:UnivUniversidadCarrera')->find($carrera_id);       

        //$carreraEntity->setUnivSede($em->getRepository('SieAppWebBundle:UnivSede')->find($sedeId));
        //$carreraEntity->setUnivAreaConocimiento($request->get('edit_area_conocimiento'));

        //$carreraEntity->setUnivNivelAcademicoTipo($em->getRepository('SieAppWebBundle:UnivNivelAcademicoTipo')->find($nivel_academico_id));
        $carreraEntity->setUnivRegimenEstudiosTipo($em->getRepository('SieAppWebBundle:UnivRegimenEstudiosTipo')->find($regimen_id));
        $carreraEntity->setUnivModalidadEnsenanzaTipo($em->getRepository('SieAppWebBundle:UnivModalidadEnsenanzaTipo')->find($modalidad_id));

        $carreraEntity->setCarrera(trim($request->get('edit_carrera')));
        $carreraEntity->setResolucion(trim($request->get('edit_resolucion')));  
        
        //fecha ??
        //rm_apertura ??
        //dump($fecha_apertura); die;
        $carreraEntity->setFechaApertura(new \DateTime( $fecha_apertura));
        
        $carreraEntity->setUnivGradoAcademicoTipo($em->getRepository('SieAppWebBundle:UnivGradoTipo')->find($grado_id));

        $carreraEntity->setDuracion($request->get('edit_duracion'));
        $carreraEntity->setDuracionAnios($request->get('edit_duracion_anios'));
        $carreraEntity->setUnivAreaConocimientoTipo($em->getRepository('SieAppWebBundle:UnivAreaConocimientoTipo')->find($area_conocimiento_id));  
        

        //$univJuridicciongeograficaEntity->setFechaRegistro(new \DateTime('now'));
       
        $em->persist($carreraEntity);
        $em->flush();       

        $this->get('session')->getFlashBag()->add('carreraok', 'La carrera fue actualizada correctamente');
        return $this->redirect($this->generateUrl('carreras_index', array('op' => 'result')));


    }

    public function deleteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $response = new JsonResponse();

        //TODO: validar antes !!!

        /*$query = $em->getConnection()->prepare("	");
        //$query->bindValue(':gestion', $gestion);
		$query->execute();
		$existe_info = $query->fetchAll();*/
        $existe_info = false;
        if ($existe_info == true){
            $msg = 'No esposible eliminar, existen datos relacionados';
            return $response->setData(array(
                'status'=>201,
                'msg'=>$msg,                
            ));
        }

        $em->remove($em->getRepository('SieAppWebBundle:UnivUniversidadCarrera')->findOneById($id));
        $em->flush();

        $this->get('session')->getFlashBag()->add('carreradeleteok', 'La carrera fue eliminada correctamente');       
        //return $this->redirect($this->generateUrl('carreras_index', array('op' => 'result')));
        $msg = 'La carrera y toda la información relacionada ha sido eliminada !';
            return $response->setData(array(
                'status'=>201,
                'msg'=>$msg,                
            ));

        
    }

    public function verifica_univ_universidad_carrera_estudiante_estado($carrera_id, $nro_periodos, $gestion, $nivel_academico){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        //dump($nivel_academico); die;  llega PREGRADO o POSTGRADO

        $sql = "select count(*) as existe from univ_universidad_carrera_estudiante_estado
        where univ_universidad_carrera_id = ".$carrera_id." and gestion_tipo_id = ". $gestion;
        $stmt = $db->prepare($sql);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $total_filas = $po[0]['existe'];

        //dump($total_filas); die;
        if($total_filas == 0){
            //creamos las filas en univ_universidad_carrera_estudiante_estado

            if($nivel_academico == 'PREGRADO'){
                $wheresql = " where es_pregrado_nivel_academico = true";
            }else{
                $wheresql = " where es_postgrado_nivel_academico = true";
            }

            $sql = "select * from univ_estadomatricula_tipo " . $wheresql;
            $stmt = $db->prepare($sql);
            $params = array();
            $stmt->execute($params);
            $data = $stmt->fetchAll();
            for($i = 0; $i < sizeof($data); $i++ ){

                
                for($j = 1; $j <= $nro_periodos; $j++ ){

                    //masculinos
                    $query = $em->getConnection()->prepare('INSERT INTO univ_universidad_carrera_estudiante_estado(gestion_tipo_id, univ_universidad_carrera_id, genero_tipo_id, univ_estadomatricula_tipo_id, univ_periodo_academico_tipo_id, cantidad)'
                                . 'VALUES(:gestion_tipo_id, :univ_universidad_carrera_id,:genero_tipo_id,:univ_estadomatricula_tipo_id,:univ_periodo_academico_tipo_id, :cantidad)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':univ_universidad_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 1);
                    $query->bindValue(':univ_estadomatricula_tipo_id', $data[$i]['id']);
                    $query->bindValue(':univ_periodo_academico_tipo_id', $j);
                    //$query->bindValue(':cantidad', rand(10, 80));                
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();

                    //femeninos
                    $query = $em->getConnection()->prepare('INSERT INTO univ_universidad_carrera_estudiante_estado(gestion_tipo_id, univ_universidad_carrera_id, genero_tipo_id, univ_estadomatricula_tipo_id, univ_periodo_academico_tipo_id, cantidad)'
                                . 'VALUES(:gestion_tipo_id, :univ_universidad_carrera_id,:genero_tipo_id,:univ_estadomatricula_tipo_id,:univ_periodo_academico_tipo_id, :cantidad)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':univ_universidad_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 2);
                    $query->bindValue(':univ_estadomatricula_tipo_id', $data[$i]['id']);
                    $query->bindValue(':univ_periodo_academico_tipo_id', $j);
                    //$query->bindValue(':cantidad', rand(10, 80));                
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();

                }

            }
        }
    }

    public function verifica_univ_matricula_nacionalidad_beca_tipo($carrera_id, $nro_periodos, $gestion, $nivel_academico){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        $sql = "select count(*) as existe from univ_universidad_carrera_estudiante_nacionalidad
        where univ_universidad_carrera_id = ".$carrera_id." and gestion_tipo_id = ". $gestion;
        $stmt = $db->prepare($sql);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $total_filas = $po[0]['existe'];

        //dump($total_filas); die;
        if($total_filas == 0){
            //creamos las filas en univ_universidad_carrera_estudiante_estado

            if($nivel_academico == 'PREGRADO'){
                $wheresql = " where es_pregrado_nivel_academico = true";
            }else{
                $wheresql = " where es_postgrado_nivel_academico = true";
            }

            $sql = "select * from univ_matricula_nacionalidad_beca_tipo " . $wheresql;
            $stmt = $db->prepare($sql);
            $params = array();
            $stmt->execute($params);
            $data = $stmt->fetchAll();
            for($i = 0; $i < sizeof($data); $i++ ){

                
                for($j = 1; $j <= $nro_periodos; $j++ ){

                    //masculinos
                    $query = $em->getConnection()->prepare('INSERT INTO univ_universidad_carrera_estudiante_nacionalidad(gestion_tipo_id, univ_universidad_carrera_id, genero_tipo_id, univ_matricula_nacionalidad_beca_tipo_id, univ_periodo_academico_tipo_id, cantidad)'
                                . 'VALUES(:gestion_tipo_id, :univ_universidad_carrera_id,:genero_tipo_id,:univ_matricula_beca_tipo_id,:univ_periodo_academico_tipo_id, :cantidad)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':univ_universidad_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 1);
                    $query->bindValue(':univ_matricula_beca_tipo_id', $data[$i]['id']);
                    $query->bindValue(':univ_periodo_academico_tipo_id', $j);
                    //$query->bindValue(':cantidad', rand(10, 70));                
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();

                    //femeninos
                    $query = $em->getConnection()->prepare('INSERT INTO univ_universidad_carrera_estudiante_nacionalidad(gestion_tipo_id, univ_universidad_carrera_id, genero_tipo_id, univ_matricula_nacionalidad_beca_tipo_id, univ_periodo_academico_tipo_id, cantidad)'
                                . 'VALUES(:gestion_tipo_id, :univ_universidad_carrera_id,:genero_tipo_id,:univ_matricula_beca_tipo_id,:univ_periodo_academico_tipo_id, :cantidad)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':univ_universidad_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 2);
                    $query->bindValue(':univ_matricula_beca_tipo_id', $data[$i]['id']);
                    $query->bindValue(':univ_periodo_academico_tipo_id', $j);
                    //$query->bindValue(':cantidad', rand(10, 50));                
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();

                }

            }
        }
    }
    public function verifica_get_univ_universidad_carrera_docente_administrativo($carrera_id, $nro_periodos, $gestion){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        $sql = "select count(*) as existe from univ_universidad_carrera_docente_administrativo
        where univ_universidad_carrera_id = ".$carrera_id." and gestion_tipo_id = ". $gestion;
        $stmt = $db->prepare($sql);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $total_filas = $po[0]['existe'];

        //dump($total_filas); die;
        if($total_filas == 0){
            //creamos las filas en univ_universidad_carrera_estudiante_estado

            $sql = "select * from univ_cargo_tipo";
            $stmt = $db->prepare($sql);
            $params = array();
            $stmt->execute($params);
            $data = $stmt->fetchAll();
            
            for($i = 0; $i < sizeof($data); $i++ ){
                
                for($j = 1; $j <= $nro_periodos; $j++ ){

                    //masculinos
                    $query = $em->getConnection()->prepare('INSERT INTO univ_universidad_carrera_docente_administrativo(gestion_tipo_id, univ_universidad_carrera_id, genero_tipo_id, univ_cargo_tipo_id, univ_periodo_academico_tipo_id, cantidad)'
                                . 'VALUES(:gestion_tipo_id, :univ_universidad_carrera_id,:genero_tipo_id,:univ_cargo_tipo_id,:univ_periodo_academico_tipo_id, :cantidad)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':univ_universidad_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 1);
                    $query->bindValue(':univ_cargo_tipo_id', $data[$i]['id']);
                    $query->bindValue(':univ_periodo_academico_tipo_id', $j);
                    //$query->bindValue(':cantidad', rand(10, 70));                
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();

                    //femeninos
                    $query = $em->getConnection()->prepare('INSERT INTO univ_universidad_carrera_docente_administrativo(gestion_tipo_id, univ_universidad_carrera_id, genero_tipo_id, univ_cargo_tipo_id, univ_periodo_academico_tipo_id, cantidad)'
                                . 'VALUES(:gestion_tipo_id, :univ_universidad_carrera_id,:genero_tipo_id,:univ_cargo_tipo_id,:univ_periodo_academico_tipo_id, :cantidad)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':univ_universidad_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 2);
                    $query->bindValue(':univ_cargo_tipo_id', $data[$i]['id']);
                    $query->bindValue(':univ_periodo_academico_tipo_id', $j);
                    //$query->bindValue(':cantidad', rand(10, 50));                
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();

                }

            }
        }
    }

    public function get_univ_matricula_nacionalidad_beca_tipo($carrera_id, $nro_periodos, $gestion, $nivel_academico){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        if($nivel_academico == 'PREGRADO'){
            $wheresql = " and univ_matricula_nacionalidad_beca_tipo.es_pregrado_nivel_academico = true";
        }else{
            $wheresql = " and univ_matricula_nacionalidad_beca_tipo.es_postgrado_nivel_academico = true";
        }

        $query ="SELECT
            univ_universidad_carrera_estudiante_nacionalidad.id, 
            univ_universidad_carrera_estudiante_nacionalidad.gestion_tipo_id, 
            univ_universidad_carrera_estudiante_nacionalidad.univ_universidad_carrera_id, 
            univ_universidad_carrera_estudiante_nacionalidad.genero_tipo_id, 
            univ_universidad_carrera_estudiante_nacionalidad.univ_periodo_academico_tipo_id, 
            univ_universidad_carrera_estudiante_nacionalidad.cantidad, 
            univ_universidad_carrera_estudiante_nacionalidad.univ_matricula_nacionalidad_beca_tipo_id, 
            univ_matricula_nacionalidad_beca_tipo.nacionalidad_beca
        FROM
            univ_universidad_carrera_estudiante_nacionalidad
            INNER JOIN
            univ_matricula_nacionalidad_beca_tipo
            ON 
                univ_universidad_carrera_estudiante_nacionalidad.univ_matricula_nacionalidad_beca_tipo_id = univ_matricula_nacionalidad_beca_tipo.id
        WHERE
            univ_universidad_carrera_id = ".$carrera_id." and gestion_tipo_id = ". $gestion . $wheresql . " order by nacionalidad_beca";
        //dump($query);die;
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        //dump($data); die;
        $filasaux = array();
        for($i = 0; $i < sizeof($data); $i++ ){
            array_push($filasaux,$data[$i]['univ_matricula_nacionalidad_beca_tipo_id'] );
        }
        
        $rowsaux = array_unique($filasaux);
        $filas = array();
        $rows = array();
        foreach ($rowsaux as $valor) {
            array_push($rows, $valor);
        }
        //dump($rowsaux);die;

        $rowsaux = array();
        for($i = 0; $i < sizeof($rows); $i++ ){

            for($j = 0; $j < sizeof($data); $j++ ){
                if ($data[$j]['univ_matricula_nacionalidad_beca_tipo_id']  == $rows[$i]){
                    array_push($rowsaux, $data[$j]);
                }
            }

            //dump($rowsaux); die;

            //beca tipo 1
            $m1 = 0; $f1 = 0; $t1 = 0;
            $m2 = 0; $f2 = 0; $t2 = 0;
            $idm2 = 0; $idf2 = 0;
            $idm1= 0; $idf1 = 0;

            for($k = 0; $k < sizeof($rowsaux); $k++ ){               
                $matricula = $rowsaux[$k]['nacionalidad_beca'];   
                
                if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['univ_periodo_academico_tipo_id'] == 1){
                    $m1 = $rowsaux[$k]['cantidad'];
                    $idm1 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['univ_periodo_academico_tipo_id'] == 1){
                    $f1  = $rowsaux[$k]['cantidad'];
                    $idf1 =  $rowsaux[$k]['id'];
                }

                if($nro_periodos == 2){
                    if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['univ_periodo_academico_tipo_id'] == 2){
                        $m2  = $rowsaux[$k]['cantidad'];
                        $idm2 =  $rowsaux[$k]['id'];
                    }
                    if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['univ_periodo_academico_tipo_id'] == 2){
                        $f2  = $rowsaux[$k]['cantidad'];
                        $idf2 =  $rowsaux[$k]['id'];
                    }   
                }
                
            }

            $row = array(
                'id' => 972,
                'matricula' => $matricula,
                'm1'  => $m1,
                'idm1'  => $idm1,
                'f1'  => $f1, 
                'idf1'  => $idf1,               
                'total1' => $m1 + $f1,
                'm2'  => $m2,
                'idm2'  => $idm2,
                'f2'  => $f2,
                'idf2'  => $idf2,
                'total2' => $m2 + $f2
            );

            array_push($filas, $row);
            
        }

        /*$t1 = 0;
        $t2 = 0;
        $t3 = 0;
        $t4 = 0;
        $t5 = 0;
        $t6 = 0;
        for($i = 0; $i < sizeof($filas); $i++ ){
            $t1 = $t1 + $filas[$i]['m1'];
            $t2 = $t2 + $filas[$i]['f1'];
            $t3 += $filas[$i]['total1'];
            //TODO: las del periodo 2
            if($nro_periodos == 2){
                $t4 = $t1 + $filas[$i]['m2'];
                $t5 = $t2 + $filas[$i]['f2'];
                $t6 += $filas[$i]['total2'];
            }

        }
        $totales = array(   
            'id' => 0,
            'matricula' => "TOTALES",        
            'm1'  => $t1,
            'f1'  => $t2,
            'total1' => $t3,
            'm2'  => $t4,
            'f2'  => $t5,
            'total2' => $t6
        );
        array_push($filas, $totales);*/
        return $filas;
        
    }

    public function get_univ_universidad_carrera_estudiante_estado($carrera_id, $nro_periodos, $gestion, $nivel_academico){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        if($nivel_academico == 'PREGRADO'){
            $wheresql = " and univ_estadomatricula_tipo.es_pregrado_nivel_academico = true";
        }else{
            $wheresql = " and univ_estadomatricula_tipo.es_postgrado_nivel_academico = true";
        }

        $query ="
        SELECT
            univ_universidad_carrera_estudiante_estado.id, 
            univ_universidad_carrera_estudiante_estado.gestion_tipo_id, 
            univ_universidad_carrera_estudiante_estado.univ_universidad_carrera_id, 
            univ_universidad_carrera_estudiante_estado.genero_tipo_id, 
            univ_universidad_carrera_estudiante_estado.univ_estadomatricula_tipo_id, 
            univ_universidad_carrera_estudiante_estado.univ_periodo_academico_tipo_id, 
            univ_universidad_carrera_estudiante_estado.cantidad, 
            univ_estadomatricula_tipo.estadomatricula
        FROM
            univ_universidad_carrera_estudiante_estado
            INNER JOIN
            univ_estadomatricula_tipo
            ON 
                univ_universidad_carrera_estudiante_estado.univ_estadomatricula_tipo_id = univ_estadomatricula_tipo.id
            where univ_universidad_carrera_id = ".$carrera_id." and gestion_tipo_id = ". $gestion . $wheresql ." order by estadomatricula";        

        
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        //dump($data); die;
        $filasaux = array();
        for($i = 0; $i < sizeof($data); $i++ ){
             array_push($filasaux,$data[$i]['univ_estadomatricula_tipo_id'] );
        }
        $rowsx = array_unique($filasaux);

        $rows = array();
        foreach ($rowsx as $valor) {
            array_push($rows, $valor);
        }


        $tipo_matricula_array = array();

        $rowsaux = array();
        
        for($i = 0; $i < sizeof($rows); $i++ ){

            for($j = 0; $j < sizeof($data); $j++ ){
                if ($data[$j]['univ_estadomatricula_tipo_id']  == $rows[$i]){
                    array_push($rowsaux, $data[$j]);
                }
            }

            //beca tipo 1
            $m1 = 0; $f1 = 0; $t1 = 0;
            $m2 = 0; $f2 = 0; $t2 = 0;
            $idm2 = 0; $idf2 = 0;
            $idm1= 0; $idf1 = 0;

            for($k = 0; $k < sizeof($rowsaux); $k++ ){               
                $matricula = $rowsaux[$k]['estadomatricula'];

                if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['univ_periodo_academico_tipo_id'] == 1){
                    $m1  = $rowsaux[$k]['cantidad'];
                    $idm1 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['univ_periodo_academico_tipo_id'] == 1){
                    $f1  = $rowsaux[$k]['cantidad'];
                    $idf1 =  $rowsaux[$k]['id'];
                }

                if($nro_periodos == 2){
                    if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['univ_periodo_academico_tipo_id'] == 2){
                        $m2  = $rowsaux[$k]['cantidad'];
                        $idm2 =  $rowsaux[$k]['id'];
                    }
                    if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['univ_periodo_academico_tipo_id'] == 2){
                        $f2  = $rowsaux[$k]['cantidad'];
                        $idf2 =  $rowsaux[$k]['id'];
                    }   
                }


            }

            $row = array(
                'id' => 972,
                'matricula' => $matricula,
                'm1'  => $m1,
                'idm1'  => $idm1,
                'f1'  => $f1, 
                'idf1'  => $idf1,               
                'total1' => $m1 + $f1,
                'm2'  => $m2,
                'idm2'  => $idm2,
                'f2'  => $f2,
                'idf2'  => $idf2,
                'total2' => $m2 + $f2
            );

            array_push($tipo_matricula_array, $row);
            
        }

        /*$t1 = 0;
        $t2 = 0;
        $t3 = 0;
        $t4 = 0;
        $t5 = 0;
        $t6 = 0;
        for($i = 0; $i < sizeof($tipo_matricula_array); $i++ ){
            $t1 = $t1 + $tipo_matricula_array[$i]['m1'];
            $t2 = $t2 + $tipo_matricula_array[$i]['f1'];
            $t3 += $tipo_matricula_array[$i]['total1'];
            //TODO: las del periodo 2
            if($nro_periodos == 2){
                $t4 = $t1 + $tipo_matricula_array[$i]['m2'];
                $t5 = $t2 + $tipo_matricula_array[$i]['f2'];
                $t6 += $tipo_matricula_array[$i]['total2'];
            }

        }
        $totales = array(   
            'id' => 0,
            'matricula' => "TOTALES",        
            'm1'  => $t1,
            'f1'  => $t2,
            'total1' => $t3,
            'm2'  => $t4,
            'f2'  => $t5,
            'total2' => $t6
        );
        array_push($tipo_matricula_array, $totales);*/

        return($tipo_matricula_array);
        //dump($filas); die;

        //$tipo_matricula_array = array();
        /*$fila = array(
            'id' => 5,
            'matricula' => "MATRICULADO NACIONAL: NUEVO",
            'm1'  => 5,
            'f1'  => 6,
            'total1' => 11,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila2 = array(
            'id' => 6,
            'matricula' => "MATRICULADO EXTRANJERO: NUEVO",
            'm1'  => 15,
            'f1'  => 1,
            'total1' => 16,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila3 = array(
            'id' => 6,
            'matricula' => "MATRICULADO NACIONAL: ANTIGUO",
            'm1'  => 15,
            'f1'  => 1,
            'total1' => 16,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila4 = array(
            'id' => 6,
            'matricula' => "MATRICULADO EXTRANJERO: ANTIGUO",
            'm1'  => 10,
            'f1'  => 1,
            'total1' => 11,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila5 = array(
            'id' => 6,
            'matricula' => "NACIONAL: EGRESADO",
            'm1'  => 15,
            'f1'  => 1,
            'total1' => 16,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila6 = array(
            'id' => 6,
            'matricula' => "EXTRANJERO: EGRESADO",
            'm1'  => 15,
            'f1'  => 1,
            'total1' => 16,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila7 = array(
            'id' => 6,
            'matricula' => "NACIONAL: TITULADO",
            'm1'  => 5,
            'f1'  => 1,
            'total1' => 6,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila8 = array(
            'id' => 6,
            'matricula' => "EXTRANJERO: TITULADO",
            'm1'  => 5,
            'f1'  => 1,
            'total1' => 6,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila9 = array(
            'id' => 6,
            'matricula' => "NACIONAL: DESERCION",
            'm1'  => 5,
            'f1'  => 1,
            'total1' => 6,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila10 = array(
            'id' => 6,
            'matricula' => "EXTRANJERO: DESERCION",
            'm1'  => 5,
            'f1'  => 1,
            'total1' => 6,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila4 = array(   
            'id' => 0,
            'matricula' => "TOTALES",        
            'm1'  => 20,
            'f1'  => 7,
            'total1' => 27,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );

        array_push($tipo_matricula_array, $fila);
        array_push($tipo_matricula_array, $fila2);
        array_push($tipo_matricula_array, $fila3);
        array_push($tipo_matricula_array, $fila4);
        array_push($tipo_matricula_array, $fila5);
        array_push($tipo_matricula_array, $fila6);
        array_push($tipo_matricula_array, $fila7);
        array_push($tipo_matricula_array, $fila8);
        array_push($tipo_matricula_array, $fila9);*/
        //array_push($tipo_matricula_array, $filas);
        //dump($tipo_matricula_array); die;


    }

    public function get_univ_universidad_carrera_docente_administrativo($carrera_id, $nro_periodos, $gestion){
        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        $query ="
        SELECT
            univ_universidad_carrera_docente_administrativo.id, 
            univ_universidad_carrera_docente_administrativo.gestion_tipo_id, 
            univ_universidad_carrera_docente_administrativo.univ_universidad_carrera_id, 
            univ_universidad_carrera_docente_administrativo.genero_tipo_id, 
            univ_universidad_carrera_docente_administrativo.univ_cargo_tipo_id, 
            univ_universidad_carrera_docente_administrativo.univ_periodo_academico_tipo_id, 
            univ_universidad_carrera_docente_administrativo.cantidad, 
            univ_cargo_tipo.cargo
        FROM
            univ_universidad_carrera_docente_administrativo
            INNER JOIN
            univ_cargo_tipo
            ON 
            univ_universidad_carrera_docente_administrativo.univ_cargo_tipo_id = univ_cargo_tipo.id
            where univ_universidad_carrera_id = ".$carrera_id." and gestion_tipo_id = ". $gestion . " order by cargo";        

        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        //dump($data); die;
        $filasaux = array();
        for($i = 0; $i < sizeof($data); $i++ ){
             array_push($filasaux,$data[$i]['univ_cargo_tipo_id'] );
        }
        $rowsaux = array_unique($filasaux);

        $rows = array();
        foreach ($rowsaux as $valor) {
            array_push($rows, $valor);
        }


        $tipo_matricula_array = array();
       

        $rowsaux = array();
        for($i = 0; $i < sizeof($rows); $i++ ){

            for($j = 0; $j < sizeof($data); $j++ ){
                if ($data[$j]['univ_cargo_tipo_id']  == $rows[$i]){
                    array_push($rowsaux, $data[$j]);
                }
            }

            //beca tipo 1
            $m1 = 0; $f1 = 0; $t1 = 0;
            $m2 = 0; $f2 = 0; $t2 = 0;
            $idm2 = 0; $idf2 = 0;
            $idm1= 0; $idf1 = 0;

            for($k = 0; $k < sizeof($rowsaux); $k++ ){               
                $matricula = $rowsaux[$k]['cargo'];

                if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['univ_periodo_academico_tipo_id'] == 1){
                    $m1  = $rowsaux[$k]['cantidad'];
                    $idm1 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['univ_periodo_academico_tipo_id'] == 1){
                    $f1  = $rowsaux[$k]['cantidad'];
                    $idf1 =  $rowsaux[$k]['id'];
                }

                if($nro_periodos == 2){
                    if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['univ_periodo_academico_tipo_id'] == 2){
                        $m2  = $rowsaux[$k]['cantidad'];
                        $idm2 =  $rowsaux[$k]['id'];
                    }
                    if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['univ_periodo_academico_tipo_id'] == 2){
                        $f2  = $rowsaux[$k]['cantidad'];
                        $idf2 =  $rowsaux[$k]['id'];
                    }   
                }


            }

            $row = array(
                'id' => 972,
                'matricula' => $matricula,
                'm1'  => $m1,
                'idm1'  => $idm1,
                'f1'  => $f1, 
                'idf1'  => $idf1,               
                'total1' => $m1 + $f1,
                'm2'  => $m2,
                'idm2'  => $idm2,
                'f2'  => $f2,
                'idf2'  => $idf2,
                'total2' => $m2 + $f2
            );

            array_push($tipo_matricula_array, $row);
            
        }

        return $tipo_matricula_array;

        /*$cargos_array = array();
        $fila = array(
            'id' => 5,
            'matricula' => "DIRECTOR",
            'm1'  => 5,
            'f1'  => 6,
            'total1' => 11,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila2 = array(
            'id' => 6,
            'matricula' => "DOCENTE DE PLANTA",
            'm1'  => 15,
            'f1'  => 1,
            'total1' => 16,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila3 = array(
            'id' => 6,
            'matricula' => "OTRO ADMINISTRATIVO",
            'm1'  => 15,
            'f1'  => 1,
            'total1' => 16,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila4 = array(   
            'id' => 0,
            'matricula' => "TOTALES",        
            'm1'  => 35,
            'f1'  => 8,
            'total1' => 43,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );

        array_push($cargos_array, $fila);
        array_push($cargos_array, $fila2);
        array_push($cargos_array, $fila3);
        array_push($cargos_array, $fila4);*/
    }

    public function statsBecasSaveAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $response = new JsonResponse();
        //dump($request);die;
        //recibe todas las variables del form
        $form = $request->get('form');       

        try {    

            foreach ($form as $clave => $valor) {      
                $id = $clave; 
                $cantidad = $valor; 

                //update al registro, solo se cambia cantidad
                $query ="update univ_universidad_carrera_estudiante_nacionalidad set cantidad = ? where id = ?";
                $stmt = $db->prepare($query);
                $params = array($cantidad, $id);
                $stmt->execute($params);           
            }      
            $msg  = 'Datos registrados correctamente';
            return $response->setData(array('estado' => true, 'msg' => $msg, 'cantidad' => 0));

        } catch (\Doctrine\ORM\NoResultException $ex) {           
            $msg  = 'Error al realizar el registro, intente nuevamente';
            return $response->setData(array('estado' => false, 'msg' => $msg, 'cantidad' => -1));
        } 
    }

    public function statsMatriculasSaveAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $response = new JsonResponse();
        
        //recibe todas las variables del form
        $form = $request->get('form');
      
        try {    

            foreach ($form as $clave => $valor) {      
                $id = $clave; 
                $cantidad = $valor; 

                //update al registro, solo se cambia cantidad
                $query ="update univ_universidad_carrera_estudiante_estado set cantidad = ? where id = ?";
                $stmt = $db->prepare($query);
                $params = array($cantidad, $id);
                $stmt->execute($params);           
            }      
            $msg  = 'Datos registrados correctamente';
            return $response->setData(array('estado' => true, 'msg' => $msg, 'cantidad' => 0));

        } catch (\Doctrine\ORM\NoResultException $ex) {           
            $msg  = 'Error al realizar el registro, intente nuevamente';
            return $response->setData(array('estado' => false, 'msg' => $msg, 'cantidad' => -1));
        } 
    }

    public function statscargosSaveAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $response = new JsonResponse();
                
        //recibe todas las variables del form
        $form = $request->get('form');
      
        try {    

            foreach ($form as $clave => $valor) {      
                $id = $clave; 
                $cantidad = $valor; 

                //update al registro, solo se cambia cantidad
                $query ="update univ_universidad_carrera_docente_administrativo set cantidad = ? where id = ?";
                $stmt = $db->prepare($query);
                $params = array($cantidad, $id);
                $stmt->execute($params);           
            }      
            $msg  = 'Datos registrados correctamente';
            return $response->setData(array('estado' => true, 'msg' => $msg, 'cantidad' => 0));

        } catch (\Doctrine\ORM\NoResultException $ex) {           
            $msg  = 'Error al realizar el registro, intente nuevamente';
            return $response->setData(array('estado' => false, 'msg' => $msg, 'cantidad' => -1));
        } 
    }


    public function statsSinInfoSaveAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $response = new JsonResponse();
           
        $justifica  =  $request->get('justifica');
        $carrera_id =  $request->get('carrera_id');
        $gestion_id =  $request->get('gestion_id');
        
        //dump($carrera_id); die;

        try {              

            //update al registro, solo se cambia cantidad
            $query ="update univ_universidad_carrera_ctr set univ_estadocarrera_tipo_id = 2, justificacion = ? where univ_universidad_carrera_id = ? and gestion_tipo_id = ?";
            $stmt = $db->prepare($query);
            $params = array($justifica, $carrera_id, $gestion_id);
            $stmt->execute($params);           
          
            $msg  = 'La carrera no reportará datos en esta gestion';
            return $response->setData(array('estado' => true, 'msg' => $msg, 'cantidad' => 0));

        } catch (\Doctrine\ORM\NoResultException $ex) {           
            $msg  = 'Error al realizar el registro, intente nuevamente';
            return $response->setData(array('estado' => false, 'msg' => $msg, 'cantidad' => -1));
        } 


    }

    function max_attribute_in_array($data){
    $max=0;
    foreach($data as $item){
        if($max < $item['gestion_tipo_id']){
            $max = $item['gestion_tipo_id'];
        }
    }
    return $max;
}

    
}
