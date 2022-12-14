<?php

namespace Sie\TecnicaEstBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\UnivUniversidadCarrera;
use Sie\AppWebBundle\Entity\UnivSede;
use Sie\AppWebBundle\Entity\UnivNivelAcademicoTipo;
use Sie\AppWebBundle\Entity\UnivRegimenEstudiosTipo;
use Sie\AppWebBundle\Entity\UnivModalidadEnsenanzaTipo;
use Sie\AppWebBundle\Entity\UnivGradoacademicoTipo;
use Sie\AppWebBundle\Entity\EstTecInstitutoCarrera;

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
        
        $sedeId = $this->session->get('sedeId'); //$data['sedeId'];
        $userId = $this->session->get('userId'); //$data['userId'];

        //$sedeId = 80830098;

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();  
        
        //contadores

        $query = "select count(*) from est_tec_instituto_carrera where est_tec_nivel_tipo_id  = 500 and est_tec_sede_id = " . $sedeId;
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $total_carreras_tec_sup = $po[0]['count'];

        $query = "select count(*) from est_tec_instituto_carrera where est_tec_nivel_tipo_id  = 501 and est_tec_sede_id = " . $sedeId;
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $total_carreras_tec_med = $po[0]['count'];

       
       
        
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = $fechaActual->format('Y');
        
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }


     

        $entityUsuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $id_usuario));

        $entityUnivSede = $em->getRepository('SieAppWebBundle:UnivSede')->findBy(array('usuario' => $id_usuario));
               
        $entityUnivSedeCentral = $em->getRepository('SieAppWebBundle:EstTecSede')->findOneBy(array('id' => $sedeId));

        //$entityPregrado = $em->getRepository('SieAppWebBundle:UnivNivelAcademicoTipo')->findById(1);    
       // $entityPostgrado = $em->getRepository('SieAppWebBundle:UnivNivelAcademicoTipo')->findById(2);    
        
        //$entityUniv = $em->getRepository('SieAppWebBundle:UnivUniversidad')->findById(1);
        //dump($entityUniv);die;

        $carreras = $em->getRepository('SieAppWebBundle:EstTecInstitutoCarrera')->findBy(array('estTecSede' => $entityUnivSedeCentral), array('carrera'=> 'ASC'));
        //$carreras = $em->getRepository('SieAppWebBundle:UnivUniversidadCa☻rrera')->findBy(array('univSede' => $entityUnivSedeCentral), array('carrera'=> 'ASC'));
        //$carreras_post = $em->getRepository('SieAppWebBundle:UnivUniversidadCarrera')->findBy(array('univSede' => $entityUnivSedeCentral, 'univNivelAcademicoTipo' => $entityPostgrado), array('carrera'=> 'ASC'));

        //dump($carreras); exit;
        
        $niveles = $em->getRepository('SieAppWebBundle:UnivNivelAcademicoTipo')->findAll();       
        $modalidad = $em->getRepository('SieAppWebBundle:UnivModalidadEnsenanzaTipo')->findAll();       
        //$grado_academico = $em->getRepository('SieAppWebBundle:UnivClaGradoAcademico')->findAll();       
        //$grado_academico = $em->getRepository('SieAppWebBundle:UnivgradoAcademicoTipo')->findAll(); 
        $regimen_estudios = $em->getRepository('SieAppWebBundle:EstTecRegimenEstudioTipo')->findAll();       
        $periodo_academico = $em->getRepository('SieAppWebBundle:UnivPeriodoAcademicoTipo')->findAll();   
        
        //$area_conocimiento = $em->getRepository('SieAppWebBundle:UnivAreaConocimientoTipo')->findAll();       
        $area_formacion = $em->getRepository('SieAppWebBundle:EstTecAreaFormacionTipo')->findAll();       
       
        return $this->render('SieTecnicaEstBundle:Carreras:index.html.twig', array(
            'sedes' => $entityUnivSede,
            'sede_id' => $sedeId,
            //'central' => $entityUniv[0],
            'titulo' => "Carreras",
            'carreras' => $carreras,      
            //'carreras_post' => $carreras_post,      
            'niveles' => $niveles,
            'regimen_estudios' => $regimen_estudios,
            'area_formacion' => $area_formacion,           
            //'grado_academico' => $grado_academico,           
            //'regimen_estudios' => $regimen_estudios,           
            'periodo_academico' => $periodo_academico,   
            'total_carreras_tec_sup' => $total_carreras_tec_sup,
            'total_carreras_tec_med' => $total_carreras_tec_med  

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

        $entityUnivSede = $em->getRepository('SieAppWebBundle:EstTecSede')->findBy(array('usuario' => $id_usuario));

        //TODO: JALAR DE LA SESION ?
        $sedeId = $this->session->get('sedeId');
        //$sedeId = 80830098;

        $entityUnivSedeCentral = $em->getRepository('SieAppWebBundle:EstTecSede')->findById($sedeId); //43
      
      
        $nombre_universidad = $entityUnivSedeCentral[0]->getEstTecInstituto()->getInstituto();
        
               
        $carreraEntity = $em->getRepository('SieAppWebBundle:EstTecInstitutoCarrera')->find($carrera_id); 
        $periodos = $carreraEntity->getEstTecRegimenEstudioTipo()->getId();
        $nivel_academico = "";// $carreraEntity->getUnivNivelAcademicoTipo()->getDescripcion();

        // 1: anual
        // 2: semestral
        //3 : modular
        //4: anbual con sistema modular
        
        $nro_periodos = 1;
        if($periodos == 1){
             $nro_periodos = 3;
        }else{
            $nro_periodos = 6;
        }

               
        $entityUniv = $em->getRepository('SieAppWebBundle:EstTecInstituto')->findById(1);
        
        $gestiones = $em->getRepository('SieAppWebBundle:EstTecRegistroConsolidacion')->findAll();      

        $arrData = array('sedeId'=> $sedeId);
        //$arrData = array('sedeId'=> $request->get(64));
        $gestiones = $this->get('tecestfunctions')->getAllOperative($arrData);
        //dump($gestiones); die;

        //$generos = $em->getRepository('SieAppWebBundle:UnivClaGenero')->findAll();   
        $generos = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();   

        if($nro_periodos == 1) {
            $periodos = $em->getRepository('SieAppWebBundle:EstTecPeriodoAcademicoTipo')->findById(1);     
        }else{
            $periodos = $em->getRepository('SieAppWebBundle:EstTecPeriodoAcademicoTipo')->findAll();    
        }

        

        $matriculas = $em->getRepository('SieAppWebBundle:EstTecMatriculaNacionalidadBecaTipo')->findAll();      
        $matriculasestado = $em->getRepository('SieAppWebBundle:EstTecEstadomatriculaTipo')->findAll();      
        $cargos = $em->getRepository('SieAppWebBundle:EstTecCargoTipo')->findAll();   

        
        //verifica egresados y titulados
        $this->verifica_get_univ_universidad_carrera_egresados($carrera_id, $nro_periodos, $gestion);

        //verificar si hay datos para le gestion y carrera, si no hay generar con ceros
        $this->verifica_univ_universidad_carrera_estudiante_estado($carrera_id, $nro_periodos, $gestion);
        
        $this->verifica_univ_matricula_nacionalidad_beca_tipo($carrera_id, $nro_periodos, $gestion);
        
        //solo si es universidad indigena
        $this->verifica_get_univ_universidad_carrera_docente_administrativo($carrera_id, $nro_periodos, $gestion);
      
        //dump('generated !'); die; 
        //datos estudiantes por genero y tipo beca

      
        $filas_egresados = $this->get_univ_universidad_carrera_egresados($carrera_id, $nro_periodos, $gestion);
        $totales0 = array();
        $t1 = 0;
        $t2 = 0;

        for($i = 0; $i < sizeof($filas_egresados); $i++ ){
            $t1 = $t1 + $filas_egresados[$i]['m1'];
            $t2 = $t2 + $filas_egresados[$i]['f1']; 
        }
        $totales0_aux = array(   
            'id' => 0,
            'matricula' => "TOTALES",        
            'm1'  => $t1,
            'f1'  => $t2,           
        );
       
        array_push($totales0, $totales0_aux);

        $filas_egresadosp2= array();
        if($nro_periodos == 6){
            $filas_egresadosp2 = $this->get_univ_universidad_carrera_egresados($carrera_id, $nro_periodos, $gestion,2);            
            $totales0p2 = array();
            $t1 = 0;
            $t2 = 0;
    
            for($i = 0; $i < sizeof($filas_egresadosp2); $i++ ){
                $t1 = $t1 + $filas_egresadosp2[$i]['m1'];
                $t2 = $t2 + $filas_egresadosp2[$i]['f1']; 
            }
            $totales0_auxp2 = array(   
                'id' => 0,
                'matricula' => "TOTALES",        
                'm1'  => $t1,
                'f1'  => $t2,           
            );
           
            array_push($totales0p2, $totales0_auxp2);
        }
       

        /*--------------------------------------------*/
        $filas = $this->get_univ_matricula_nacionalidad_beca_tipo($carrera_id, $nro_periodos, $gestion);
        $filasp2 = array();
        
        $totales1 = array();
        $t1 = 0;
        $t2 = 0;
        $t3 = 0;
        $t4 = 0;
        $t5 = 0;
        $t6 = 0;

        $t7 = 0;
        $t8 = 0;
        $t9 = 0;
        $t10 = 0;
        $t11 = 0;
        $t12 = 0;
       
        for($i = 0; $i < sizeof($filas); $i++ ){
            $t1 = $t1 + $filas[$i]['m1'];
            $t2 = $t2 + $filas[$i]['f1']; 
            
            $t3 = $t3 + $filas[$i]['m2'];
            $t4 = $t4 + $filas[$i]['f2'];
            $t5 = $t5 + $filas[$i]['m3'];
            $t6 = $t6 + $filas[$i]['f3'];
                
                
            //TODO: las del periodo 2
            if($nro_periodos == 6){
                $t7 = $t7 + $filas[$i]['m4'];
                $t8 = $t8 + $filas[$i]['f4'];    
                $t9 = $t9 + $filas[$i]['m5'];
                $t10 = $t10 + $filas[$i]['f5'];     
                $t11 = $t11 + $filas[$i]['m6'];
                $t12 = $t12 + $filas[$i]['f6'];     
            }

        }
        $totales = array(   
            'id' => 0,
            'matricula' => "TOTALES",        
            'm1'  => $t1,
            'f1'  => $t2,           
            'm2'  => $t3,
            'f2'  => $t4,
            'm3'  => $t5,
            'f3'  => $t6,

            'm4'  => $t7,
            'f4'  => $t8,           
            'm5'  => $t9,
            'f5'  => $t10,
            'm6'  => $t11,
            'f6'  => $t12,
            
        );
       
        array_push($totales1, $totales);

        //inicio tipo de beca si es semestral para el periodo2
        if($nro_periodos == 6){
            $filasp2 = $this->get_univ_matricula_nacionalidad_beca_tipo($carrera_id, $nro_periodos, $gestion,2);            
            //dump($filasp2); die;
            $totales1p2 = array();
            $t1 = 0;
            $t2 = 0;
            $t3 = 0;
            $t4 = 0;
            $t5 = 0;
            $t6 = 0;

            $t7 = 0;
            $t8 = 0;
            $t9 = 0;
            $t10 = 0;
            $t11 = 0;
            $t12 = 0;
        
            for($i = 0; $i < sizeof($filasp2); $i++ ){
                $t1 = $t1 + $filasp2[$i]['m1'];
                $t2 = $t2 + $filasp2[$i]['f1']; 
                
                $t3 = $t3 + $filasp2[$i]['m2'];
                $t4 = $t4 + $filasp2[$i]['f2'];
                $t5 = $t5 + $filasp2[$i]['m3'];
                $t6 = $t6 + $filasp2[$i]['f3'];
                    
                $t7 = $t7 + $filasp2[$i]['m4'];
                $t8 = $t8 + $filasp2[$i]['f4'];    
                $t9 = $t9 + $filasp2[$i]['m5'];
                $t10 = $t10 + $filasp2[$i]['f5'];     
                $t11 = $t11 + $filasp2[$i]['m6'];
                $t12 = $t12 + $filasp2[$i]['f6'];                   

            }
            $totales = array(   
                'id' => 0,
                'matricula' => "TOTALES",        
                'm1'  => $t1,
                'f1'  => $t2,           
                'm2'  => $t3,
                'f2'  => $t4,
                'm3'  => $t5,
                'f3'  => $t6,

                'm4'  => $t7,
                'f4'  => $t8,           
                'm5'  => $t9,
                'f5'  => $t10,
                'm6'  => $t11,
                'f6'  => $t12,
                
            );
            array_push($totales1p2, $totales);

        }
        //fin tipo de beca si es semestral

        //solo para universidad indigena y otros
        $cargos_array = $this->get_univ_universidad_carrera_docente_administrativo($carrera_id, $nro_periodos, $gestion);
        $filas3 = $cargos_array;
        $cargos_arrayp2 = array();
        $totales3 = array();
        $t1 = 0;
        $t2 = 0;
        $t3 = 0;
        $t4 = 0;
        $t5 = 0;
        $t6 = 0;
        $t7 = 0;
        $t8 = 0;
        $t9 = 0;
        $t10 = 0;
        $t11 = 0;
        $t12 = 0;
       
        for($i = 0; $i < sizeof($filas3); $i++ ){
            $t1 = $t1 + $filas3[$i]['m1'];
            $t2 = $t2 + $filas3[$i]['f1'];    
            $t3 = $t3 + $filas3[$i]['m2'];
            $t4 = $t4 + $filas3[$i]['f2'];
            $t5 = $t5 + $filas3[$i]['m3'];
            $t6 = $t6 + $filas3[$i]['f3'];

            if($nro_periodos == 6){
                $t7 = $t7 + $filas3[$i]['m4'];
                $t8 = $t8 + $filas3[$i]['f4'];    
                $t9 = $t9 + $filas3[$i]['m5'];
                $t10 = $t10 + $filas3[$i]['f5'];     
                $t11 = $t11 + $filas3[$i]['m6'];
                $t12 = $t12 + $filas3[$i]['f6'];                  
            }

        }
        $totales = array(   
            'id' => 0,
            'matricula' => "TOTALES",        
            'm1'  => $t1,
            'f1'  => $t2,           
            'm2'  => $t3,
            'f2'  => $t4,
            'm3'  => $t5,
            'f3'  => $t6,
            'm4'  => $t7,
            'f4'  => $t8,           
            'm5'  => $t9,
            'f5'  => $t10,
            'm6'  => $t11,
            'f6'  => $t12,
            
        );
        array_push($totales3, $totales);

         //inicio docentes si es semestral para el periodo2
         if($nro_periodos == 6){

            $cargos_arrayp2 = $this->get_univ_universidad_carrera_docente_administrativo($carrera_id, $nro_periodos, $gestion,2);
            $filas3p2 = $cargos_arrayp2;            
            $totales3p2 = array();
            $t1 = 0;
            $t2 = 0;
            $t3 = 0;
            $t4 = 0;
            $t5 = 0;
            $t6 = 0;
            $t7 = 0;
            $t8 = 0;
            $t9 = 0;
            $t10 = 0;
            $t11 = 0;
            $t12 = 0;
           
            for($i = 0; $i < sizeof($filas3p2); $i++ ){
                $t1 = $t1 + $filas3p2[$i]['m1'];
                $t2 = $t2 + $filas3p2[$i]['f1'];    
                $t3 = $t3 + $filas3p2[$i]['m2'];
                $t4 = $t4 + $filas3p2[$i]['f2'];
                $t5 = $t5 + $filas3p2[$i]['m3'];
                $t6 = $t6 + $filas3p2[$i]['f3'];
    
                if($nro_periodos == 6){
                    $t7 = $t7 + $filas3p2[$i]['m4'];
                    $t8 = $t8 + $filas3p2[$i]['f4'];    
                    $t9 = $t9 + $filas3p2[$i]['m5'];
                    $t10 = $t10 + $filas3p2[$i]['f5'];     
                    $t11 = $t11 + $filas3p2[$i]['m6'];
                    $t12 = $t12 + $filas3p2[$i]['f6'];                  
                }
    
            }
            $totales = array(   
                'id' => 0,
                'matricula' => "TOTALES",        
                'm1'  => $t1,
                'f1'  => $t2,           
                'm2'  => $t3,
                'f2'  => $t4,
                'm3'  => $t5,
                'f3'  => $t6,
                'm4'  => $t7,
                'f4'  => $t8,           
                'm5'  => $t9,
                'f5'  => $t10,
                'm6'  => $t11,
                'f6'  => $t12,
                
            );
            array_push($totales3p2, $totales);

         }
         //fin docentes si es semestral para el periodo2

        //datos estudiantes por tipo de matricula
        /*--------------------------------------------------*/
        $tipo_matricula_array = $this->get_univ_universidad_carrera_estudiante_estado($carrera_id, $nro_periodos, $gestion);
        $filas2 = $tipo_matricula_array;
        $tipo_matricula_arrayp2 = array();
        $totales2 = array();
        $t1 = 0;
        $t2 = 0;
        $t3 = 0;
        $t4 = 0;
        $t5 = 0;
        $t6 = 0;
        $t7 = 0;
        $t8 = 0;
        $t9 = 0;
        $t10 = 0;
        $t11 = 0;
        $t12 = 0;
        
       
        for($i = 0; $i < sizeof($filas2); $i++ ){
            $t1 = $t1 + $filas2[$i]['m1'];
            $t2 = $t2 + $filas2[$i]['f1']; 
            
            $t3 = $t3 + $filas2[$i]['m2'];
            $t4 = $t4 + $filas2[$i]['f2'];
            $t5 = $t5 + $filas2[$i]['m3'];
            $t6 = $t6 + $filas2[$i]['f3'];    
            
            if($nro_periodos == 6){
                $t7 = $t7 + $filas2[$i]['m4'];
                $t8 = $t8 + $filas2[$i]['f4'];    
                $t9 = $t9 + $filas2[$i]['m5'];
                $t10 = $t10 + $filas2[$i]['f5'];     
                $t11 = $t11 + $filas2[$i]['m6'];
                $t12 = $t12 + $filas2[$i]['f6'];                  
            }

        }
        $totales_aux3 = array(   
            'id' => 0,
            'matricula' => "TOTALES",        
            'm1'  => $t1,
            'f1'  => $t2,           
            'm2'  => $t3,
            'f2'  => $t4,
            'm3'  => $t5,
            'f3'  => $t6,
            'm4'  => $t7,
            'f4'  => $t8,           
            'm5'  => $t9,
            'f5'  => $t10,
            'm6'  => $t11,
            'f6'  => $t12,
            
        );
       
        array_push($totales2, $totales_aux3);

        // inicio tipo matricula periodo 2
        if($nro_periodos == 6){ 

            $tipo_matricula_arrayp2 = $this->get_univ_universidad_carrera_estudiante_estado($carrera_id, $nro_periodos, $gestion, 2);
            $filas2p2 = $tipo_matricula_arrayp2;
            $totales2p2 = array();
            $t1 = 0;
            $t2 = 0;
            $t3 = 0;
            $t4 = 0;
            $t5 = 0;
            $t6 = 0;
            $t7 = 0;
            $t8 = 0;
            $t9 = 0;
            $t10 = 0;
            $t11 = 0;
            $t12 = 0;
            
        
            for($i = 0; $i < sizeof($filas2p2); $i++ ){
                $t1 = $t1 + $filas2p2[$i]['m1'];
                $t2 = $t2 + $filas2p2[$i]['f1']; 
                
                $t3 = $t3 + $filas2p2[$i]['m2'];
                $t4 = $t4 + $filas2p2[$i]['f2'];
                $t5 = $t5 + $filas2p2[$i]['m3'];
                $t6 = $t6 + $filas2p2[$i]['f3'];    
                
                if($nro_periodos == 6){
                    $t7 = $t7 + $filas2p2[$i]['m4'];
                    $t8 = $t8 + $filas2p2[$i]['f4'];    
                    $t9 = $t9 + $filas2p2[$i]['m5'];
                    $t10 = $t10 + $filas2p2[$i]['f5'];     
                    $t11 = $t11 + $filas2p2[$i]['m6'];
                    $t12 = $t12 + $filas2p2[$i]['f6'];                  
                }

            }
            $totales_aux3 = array(   
                'id' => 0,
                'matricula' => "TOTALES",        
                'm1'  => $t1,
                'f1'  => $t2,           
                'm2'  => $t3,
                'f2'  => $t4,
                'm3'  => $t5,
                'f3'  => $t6,
                'm4'  => $t7,
                'f4'  => $t8,           
                'm5'  => $t9,
                'f5'  => $t10,
                'm6'  => $t11,
                'f6'  => $t12,
                
            );
        
            array_push($totales2p2, $totales_aux3);

        }
        // fin tipo matricula periodo 2

        
        $data = array(            
            'periodos' => $nro_periodos,
            'array_estudiantes' => $filas,  //tipo de beca periodo1
            'array_estudiantesp2' => $filasp2,  //tipo de beca periodo2
            'array_cargos' => $cargos_array,    //cargos periodo 1
            'array_cargosp2' => $cargos_arrayp2, //cargos periodo2
            'array_tipo_matricula' => $tipo_matricula_array,
            'array_tipo_matriculap2' => $tipo_matricula_arrayp2,
            'filas_egresados' => $filas_egresados,
            'filas_egresadosp2' => $filas_egresadosp2,
        );

        //ver si tiene dato en la gestion
        //univ_universidad_carrera_ctr
        $sindatoenlagestion = false;  // se muestra el boton
        $sql = "select est_tec_estadocarrera_tipo_id from est_tec_instituto_carrera_ctr where est_tec_instituto_carrera_id = " .$carrera_id . " and gestion_tipo_id = " . $gestion;
        //dump($sql); die;
        $stmt = $db->prepare($sql);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $estado = $po[0]['est_tec_estadocarrera_tipo_id'];
        //dump($estado);die;
        if($estado == 2){$sindatoenlagestion = true;}

        //$sedeId = $this->session->get('sedeId');
        // para saber si el operatico esta abierto(true) o cerrado (false)
        $sql = "select activo from est_tec_registro_consolidacion
        where est_tec_sede_id = ".$sedeId." and gestion_tipo_id = ". $gestion;
        $stmt = $db->prepare($sql);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $opestatus = $po[0]['activo'];
        //dump($opestatus); die; 

        //para abrir solo 2022
        if($opestatus == false and $gestion == 2022){
            $opestatus == true;
        }
        
        if($nro_periodos == 3){  //anual
            return $this->render('SieTecnicaEstBundle:Carreras:info.html.twig', array(
                'carrera_id' => $carrera_id,
                'gestion_id' =>  $gestion,
                'entity' => $carreraEntity,           
                'sedes' => $entityUnivSede,
                'central' => 0,//$entityUniv[0],
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
                'es_indigena' => 0,
                'headertxt' => 'Año',
                'filas_egresados' => $filas_egresados,
                'totales0' => $totales0,
                'sindatoenlagestion' => $sindatoenlagestion,
                'opestatus' => $opestatus,

    
            ));
        }else{ //semestral
            return $this->render('SieTecnicaEstBundle:Carreras:infosem.html.twig', array(
                'carrera_id' => $carrera_id,
                'gestion_id' =>  $gestion,
                'entity' => $carreraEntity,           
                'sedes' => $entityUnivSede,
                'central' => 0,//$entityUniv[0],
                'titulo' => "Carreras",                     
                'gestiones' => $gestiones,                     
                'generos' => $generos,                     
                'periodos' => $periodos,                     
                'matriculas' => $matriculas,  
                'matriculasestado' => $matriculasestado,
                'cargos' => $cargos,  
                'data' => $data,
                'totales1' => $totales1,
                'totales1p2' => $totales1p2,
                'totales2' => $totales2,
                'totales2p2' => $totales2p2,
                'totales3' => $totales3,
                'totales3p2' => $totales3p2,
                'es_indigena' => 0,
                'headertxt' => 'Semestre',
                'totales0' => $totales0,
                'totales0p2' => $totales0p2,
                'sindatoenlagestion' => $sindatoenlagestion,
                'opestatus' => $opestatus,
    
            ));
        }
        
        

    }

    public function newAction(Request $request)
    {

        //dump($request);die;
        date_default_timezone_set('America/La_Paz');
        $response = new JsonResponse();
        
        $fechaActual = new \DateTime(date('Y-m-d'));

        //$sedeId = $this->session->get('sedeId');
        $sedeId =  $request->get('sede_id');

        $nivel_academico_id = $request->get('nivel_academico_id');
        $regimen_id = $request->get('modalidad_id');
       
        $area_formacion_id = $request->get('grado_id');
        $tiempo_estudio = $request->get('tiempo_estudio');
        $carga_horaria = $request->get('duracion_anios');
        
        //$area_conocimiento_id = $request->get('area_conocimiento_id');
       
        //$fecha_apertura_tmp = $request->get('fecha_apertura');
        //15/12/2003
       
        //$fecha_apertura = date('Y-m-d', strtotime($fecha_apertura_tmp));

        $em = $this->getDoctrine()->getManager();

        $carreraEntity = new EstTecInstitutoCarrera();
       

        $carreraEntity->setEstTecSede($em->getRepository('SieAppWebBundle:EstTecSede')->find($sedeId));
      
     
        $carreraEntity->setEstTecRegimenEstudioTipo($em->getRepository('SieAppWebBundle:EstTecRegimenEstudioTipo')->find($regimen_id));
        $carreraEntity->setEstTecAreaFormacionTipo($em->getRepository('SieAppWebBundle:EstTecAreaFormacionTipo')->find($area_formacion_id));

        $carreraEntity->setCarrera($request->get('carrera'));
        $carreraEntity->setResolucion($request->get('resolucion'));  
        
        $carreraEntity->setTiempoEstudio($request->get('tiempo_estudio'));
        $carreraEntity->setCargaHoraria($request->get('carga_horaria'));

       
        $carreraEntity->setEstado(1);
        $carreraEntity->setFechaRegistro(new \DateTime('now'));
        $carreraEntity->setFechaModificacion(new \DateTime('now'));

        //dump($carreraEntity); die; 
       
        $em->persist($carreraEntity);
        $em->flush();       

        $this->get('session')->getFlashBag()->add('carreraok', 'La carrera fue creada correctamente');
        return $this->redirect($this->generateUrl('tecest_carreras', array('op' => 'result')));


    }

    public function editAction(Request $request)
    {
        
        date_default_timezone_set('America/La_Paz');
        $response = new JsonResponse();
        
        //dump($request); die;
        //TODO: esto de donde ?
        $carrera_id = $request->get('carrera_id');
        //$sedeId = $this->session->get('sedeId');
        $sedeId =$request->get('sede_id');

        $nivel_academico_id = $request->get('edit_nivel_academico_id');
        $regimen_id = $request->get('edit_modalidad_id');

        $area_formacion_id = $request->get('edit_grado_id');
      
        $tiempo_estudio = $request->get('edit_tiempo_estudio');
        $carga_horaria = $request->get('edit_carga_horaria');
        $resolucion = $request->get('edit_resolucion');

        //$area_conocimiento_id = $request->get('area_conocimiento_id');

        //$fecha_apertura_tmp = $request->get('fecha_apertura');
        //15/12/2003

        //$fecha_apertura = date('Y-m-d', strtotime($fecha_apertura_tmp));

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        $query = "update est_tec_instituto_carrera set est_tec_regimen_estudio_tipo_id = ? , est_tec_area_formacion_tipo_id = ?, tiempo_estudio = ?, carga_horaria = ?, resolucion = ? where id = ?";
        $stmt = $db->prepare($query);
        $params = array($regimen_id, $area_formacion_id, $tiempo_estudio, $carga_horaria, $resolucion, $carrera_id);
        $stmt->execute($params);       
       
        /*$carreraEntity = $em->getRepository('SieAppWebBundle:EstTecInstitutoCarrera')->find($carrera_id);

        //dump($carreraEntity);die;

        $carreraEntity->setEstTecSede($em->getRepository('SieAppWebBundle:EstTecSede')->findOneBy(array('id' => $sedeId)));

        dump($em->getRepository('SieAppWebBundle:EstTecSede')->findOneBy(array('id' => $sedeId)));


        $carreraEntity->setEstTecRegimenEstudioTipo($em->getRepository('SieAppWebBundle:EstTecRegimenEstudioTipo')->findOneBy(array('id' => $regimen_id)));
        $carreraEntity->setEstTecAreaFormacionTipo($em->getRepository('SieAppWebBundle:EstTecAreaFormacionTipo')->findOneBy(array('id' => $area_formacion_id)));

        $carreraEntity->setCarrera($request->get('carrera'));
        $carreraEntity->setResolucion($request->get('resolucion'));

        $carreraEntity->setTiempoEstudio($request->get('tiempo_estudio'));
        $carreraEntity->setCargaHoraria($request->get('carga_horaria'));


        $carreraEntity->setEstado(1);       
        $carreraEntity->setFechaModificacion(new \DateTime('now'));

        //$univJuridicciongeograficaEntity->setFechaRegistro(new \DateTime('now'));
       
        $em->persist($carreraEntity);
        $em->flush();      */

        $this->get('session')->getFlashBag()->add('carreraok', 'La carrera fue actualizada correctamente');
        return $this->redirect($this->generateUrl('tecest_carreras', array('op' => 'result')));


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
        $existe_info = true;
        if ($existe_info == true){
            $msg = 'No esposible eliminar, existen datos relacionados';
            return $response->setData(array(
                'status'=>201,
                'msg'=>$msg,                
            ));
        }

        /*$em->remove($em->getRepository('SieAppWebBundle:UnivUniversidadCarrera')->findOneById($id));
        $em->flush();*/

        $this->get('session')->getFlashBag()->add('carreradeleteok', 'La carrera fue eliminada correctamente');
        return $this->redirect($this->generateUrl('carreras_index', array('op' => 'result')));
    }

    public function verifica_univ_universidad_carrera_estudiante_estado($carrera_id, $nro_periodos, $gestion){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        //dump($nro_periodos); die; //3: anual   6:semestral
        // valores por defecto

        $sql = "select count(*) as existe from est_tec_instituto_carrera_estudiante_estado
        where est_tec_instituto_carrera_id = ".$carrera_id." and gestion_tipo_id = ". $gestion;
        $stmt = $db->prepare($sql);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $total_filas = $po[0]['existe'];

        //dump($total_filas); die;
        if($total_filas == 0){
            //creamos las filas en univ_universidad_carrera_estudiante_estado

            $sql = "select * from est_tec_estadomatricula_tipo";
            $stmt = $db->prepare($sql);
            $params = array();
            $stmt->execute($params);
            $data = $stmt->fetchAll();

            for($i = 0; $i < sizeof($data); $i++ ){
                
                for($j = 1; $j <= $nro_periodos; $j++ ){
                    //masculinos
                    $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_estudiante_estado(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_estadomatricula_tipo_id, est_tec_periodo_academico_tipo_id, cantidad,est_tec_grado_academico_tipo_id)'
                                . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_estadomatricula_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 1);
                    $query->bindValue(':est_tec_estadomatricula_tipo_id', $data[$i]['id']);
                    $query->bindValue(':est_tec_periodo_academico_tipo_id', $j);
                    $query->bindValue(':est_tec_grado_academico_tipo_id', 1); // del primer periodo academico
                    //$query->bindValue(':cantidad', rand(10, 80));                
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();

                    //femeninos
                    $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_estudiante_estado(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_estadomatricula_tipo_id, est_tec_periodo_academico_tipo_id, cantidad,est_tec_grado_academico_tipo_id)'
                                . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_estadomatricula_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 2);
                    $query->bindValue(':est_tec_estadomatricula_tipo_id', $data[$i]['id']);
                    $query->bindValue(':est_tec_periodo_academico_tipo_id', $j);
                    $query->bindValue(':est_tec_grado_academico_tipo_id', 1); // del primer periodo academico
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();
                }
            }
            if($nro_periodos == 6){
                for($i = 0; $i < sizeof($data); $i++ ){                
                    for($j = 1; $j <= $nro_periodos; $j++ ){
                        //masculinos
                        $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_estudiante_estado(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_estadomatricula_tipo_id, est_tec_periodo_academico_tipo_id, cantidad, est_tec_grado_academico_tipo_id)'
                                    . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_estadomatricula_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                        $query->bindValue(':gestion_tipo_id', $gestion);
                        $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                        $query->bindValue(':genero_tipo_id', 1);
                        $query->bindValue(':est_tec_estadomatricula_tipo_id', $data[$i]['id']);
                        $query->bindValue(':est_tec_periodo_academico_tipo_id', $j);
                        $query->bindValue(':est_tec_grado_academico_tipo_id',2 ); // del segundo periodo academico
                        $query->bindValue(':cantidad',0);                
                        $query->execute();

                        //femeninos
                        $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_estudiante_estado(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_estadomatricula_tipo_id, est_tec_periodo_academico_tipo_id, cantidad, est_tec_grado_academico_tipo_id)'
                                    . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_estadomatricula_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                        $query->bindValue(':gestion_tipo_id', $gestion);
                        $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                        $query->bindValue(':genero_tipo_id', 2);
                        $query->bindValue(':est_tec_estadomatricula_tipo_id', $data[$i]['id']);
                        $query->bindValue(':est_tec_periodo_academico_tipo_id', $j);
                        $query->bindValue(':est_tec_grado_academico_tipo_id',2 ); // del segundo periodo academico
                        $query->bindValue(':cantidad',0);                
                        $query->execute();
                    }
                }
            }


        }
    }

    public function verifica_univ_matricula_nacionalidad_beca_tipo($carrera_id, $nro_periodos, $gestion){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        //nro_periodos  3: anual (1 periodo), 6:semestral (2 periodos)

        $sql = "select count(*) as existe from est_tec_instituto_carrera_estudiante_nacionalidad
        where est_tec_instituto_carrera_id = ".$carrera_id." and gestion_tipo_id = ". $gestion;
        $stmt = $db->prepare($sql);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $total_filas = $po[0]['existe'];

        //dump($total_filas); die;
        if($total_filas == 0){
            //creamos las filas en est_tec_instituto_carrera_estudiante_estado

            $sql = "select * from est_tec_matricula_nacionalidad_beca_tipo";
            $stmt = $db->prepare($sql);
            $params = array();
            $stmt->execute($params);
            $data = $stmt->fetchAll();
            for($i = 0; $i < sizeof($data); $i++ ){
                for($j = 1; $j <= $nro_periodos; $j++ ){
                    //masculinos
                    $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_estudiante_nacionalidad(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_matricula_nacionalidad_beca_tipo_id, est_tec_periodo_academico_tipo_id, cantidad,est_tec_grado_academico_tipo_id)'
                                . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_matricula_beca_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 1);
                    $query->bindValue(':est_tec_matricula_beca_tipo_id', $data[$i]['id']);
                    $query->bindValue(':est_tec_periodo_academico_tipo_id', $j);
                    $query->bindValue(':est_tec_grado_academico_tipo_id', 1); // del primer periodo academico
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();

                    //femeninos
                    $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_estudiante_nacionalidad(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_matricula_nacionalidad_beca_tipo_id, est_tec_periodo_academico_tipo_id, cantidad, est_tec_grado_academico_tipo_id)'
                                . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_matricula_beca_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 2);
                    $query->bindValue(':est_tec_matricula_beca_tipo_id', $data[$i]['id']);
                    $query->bindValue(':est_tec_periodo_academico_tipo_id',$j);
                    $query->bindValue(':est_tec_grado_academico_tipo_id', 1); // del primer periodo academico
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();
                }

            }

            if($nro_periodos == 6){
                // generar otros 12 para el periodo academico 2

                for($i = 0; $i < sizeof($data); $i++ ){
                    for($j = 1; $j <= $nro_periodos; $j++ ){
                        //masculinos
                        $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_estudiante_nacionalidad(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_matricula_nacionalidad_beca_tipo_id, est_tec_periodo_academico_tipo_id, cantidad, est_tec_grado_academico_tipo_id)'
                                    . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_matricula_beca_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                        $query->bindValue(':gestion_tipo_id', $gestion);
                        $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                        $query->bindValue(':genero_tipo_id', 1);
                        $query->bindValue(':est_tec_matricula_beca_tipo_id', $data[$i]['id']);
                        $query->bindValue(':est_tec_periodo_academico_tipo_id', $j);
                        $query->bindValue(':est_tec_grado_academico_tipo_id', 2); // del segundo periodo academico
                        $query->bindValue(':cantidad', 0);                
                        $query->execute();
    
                        //femeninos
                        $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_estudiante_nacionalidad(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_matricula_nacionalidad_beca_tipo_id, est_tec_periodo_academico_tipo_id, cantidad, est_tec_grado_academico_tipo_id)'
                                    . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_matricula_beca_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                        $query->bindValue(':gestion_tipo_id', $gestion);
                        $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                        $query->bindValue(':genero_tipo_id', 2);
                        $query->bindValue(':est_tec_matricula_beca_tipo_id', $data[$i]['id']);
                        $query->bindValue(':est_tec_periodo_academico_tipo_id',$j);
                        $query->bindValue(':est_tec_grado_academico_tipo_id', 2); // del segundo periodo academico
                        $query->bindValue(':cantidad', 0);                
                        $query->execute();
                    }
    
                }

            }
        }
    }

    public function verifica_get_univ_universidad_carrera_docente_administrativo($carrera_id, $nro_periodos, $gestion){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        $sql = "select count(*) as existe from est_tec_instituto_carrera_docente_administrativo
        where est_tec_instituto_carrera_id = ".$carrera_id." and gestion_tipo_id = ". $gestion;
        
        $stmt = $db->prepare($sql);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $total_filas = $po[0]['existe'];

        //dump($total_filas); die;
        if($total_filas == 0){
            //creamos las filas en est_tec_instituto_carrera_estudiante_estado

            $sql = "select * from est_tec_cargo_tipo";
            $stmt = $db->prepare($sql);
            $params = array();
            $stmt->execute($params);
            $data = $stmt->fetchAll();
           
            for($i = 0; $i < sizeof($data); $i++ ){
                // periodo academico 1
                for($j = 1; $j <= $nro_periodos; $j++ ){

                    //masculinos
                    $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_docente_administrativo(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_cargo_tipo_id, est_tec_periodo_academico_tipo_id, cantidad,est_tec_grado_academico_tipo_id)'
                                . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_cargo_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 1);
                    $query->bindValue(':est_tec_cargo_tipo_id', $data[$i]['id']);
                    $query->bindValue(':est_tec_periodo_academico_tipo_id', $j);
                    $query->bindValue(':est_tec_grado_academico_tipo_id', 1); // del primer periodo academico
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();

                    //femeninos
                    $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_docente_administrativo(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_cargo_tipo_id, est_tec_periodo_academico_tipo_id, cantidad, est_tec_grado_academico_tipo_id)'
                                . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_cargo_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 2);
                    $query->bindValue(':est_tec_cargo_tipo_id', $data[$i]['id']);
                    $query->bindValue(':est_tec_periodo_academico_tipo_id', $j);
                    $query->bindValue(':est_tec_grado_academico_tipo_id', 1); // del primer periodo academico
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();

                }

            }

            if($nro_periodos == 6){
                 // generar otros 12 para el periodo academico 2

                 for($i = 0; $i < sizeof($data); $i++ ){
                    // periodo academico 1
                    for($j = 1; $j <= $nro_periodos; $j++ ){
    
                        //masculinos
                        $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_docente_administrativo(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_cargo_tipo_id, est_tec_periodo_academico_tipo_id, cantidad,est_tec_grado_academico_tipo_id)'
                                    . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_cargo_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                        $query->bindValue(':gestion_tipo_id', $gestion);
                        $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                        $query->bindValue(':genero_tipo_id', 1);
                        $query->bindValue(':est_tec_cargo_tipo_id', $data[$i]['id']);
                        $query->bindValue(':est_tec_periodo_academico_tipo_id', $j);
                        $query->bindValue(':est_tec_grado_academico_tipo_id', 2); // del primer periodo academico
                        $query->bindValue(':cantidad', 0);                
                        $query->execute();
    
                        //femeninos
                        $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_docente_administrativo(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_cargo_tipo_id, est_tec_periodo_academico_tipo_id, cantidad, est_tec_grado_academico_tipo_id)'
                                    . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_cargo_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                        $query->bindValue(':gestion_tipo_id', $gestion);
                        $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                        $query->bindValue(':genero_tipo_id', 2);
                        $query->bindValue(':est_tec_cargo_tipo_id', $data[$i]['id']);
                        $query->bindValue(':est_tec_periodo_academico_tipo_id', $j);
                        $query->bindValue(':est_tec_grado_academico_tipo_id', 2); // del primer periodo academico
                        $query->bindValue(':cantidad', 0);                
                        $query->execute();
    
                    }
    
                }

            }
        }
    }


    public function verifica_get_univ_universidad_carrera_egresados($carrera_id, $nro_periodos, $gestion){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        $sql = "select count(*) as existe from est_tec_instituto_carrera_estudiante_egresado_titulado
        where est_tec_instituto_carrera_id = ".$carrera_id." and gestion_tipo_id = ". $gestion;
        
        $stmt = $db->prepare($sql);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $total_filas = $po[0]['existe'];

        //dump($total_filas); die;
        if($total_filas == 0){
            //creamos las filas en est_tec_instituto_carrera_estudiante_estado

            $sql = "select * from est_tec_egresado_titulados_tipo";
            $stmt = $db->prepare($sql);
            $params = array();
            $stmt->execute($params);
            $data = $stmt->fetchAll();
           
            for($i = 0; $i < sizeof($data); $i++ ){
                // periodo academico 1
                for($j = 1; $j <= 1; $j++ ){

                    //masculinos
                    $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_estudiante_egresado_titulado(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_egresado_titulados_tipo_id, est_tec_periodo_academico_tipo_id, cantidad,est_tec_grado_academico_tipo_id)'
                                . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_egresado_titulados_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 1);
                    $query->bindValue(':est_tec_egresado_titulados_tipo_id', $data[$i]['id']);
                    $query->bindValue(':est_tec_periodo_academico_tipo_id', $j);
                    $query->bindValue(':est_tec_grado_academico_tipo_id', 1); // del primer periodo academico
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();

                    //femeninos
                    $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_estudiante_egresado_titulado(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_egresado_titulados_tipo_id, est_tec_periodo_academico_tipo_id, cantidad, est_tec_grado_academico_tipo_id)'
                                . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_egresado_titulados_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 2);
                    $query->bindValue(':est_tec_egresado_titulados_tipo_id', $data[$i]['id']);
                    $query->bindValue(':est_tec_periodo_academico_tipo_id', $j);
                    $query->bindValue(':est_tec_grado_academico_tipo_id', 1); // del primer periodo academico
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();

                }

            }
            
            if($nro_periodos == 6){
                 // generar otros 12 para el periodo academico 2

                 for($i = 0; $i < sizeof($data); $i++ ){
                    // periodo academico 1
                    for($j = 1; $j <= 1; $j++ ){
    
                     //masculinos
                     $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_estudiante_egresado_titulado(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_egresado_titulados_tipo_id, est_tec_periodo_academico_tipo_id, cantidad,est_tec_grado_academico_tipo_id)'
                     . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_egresado_titulados_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 1);
                    $query->bindValue(':est_tec_egresado_titulados_tipo_id', $data[$i]['id']);
                    $query->bindValue(':est_tec_periodo_academico_tipo_id', $j);
                    $query->bindValue(':est_tec_grado_academico_tipo_id', 2); // del primer periodo academico
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();

                    //femeninos
                    $query = $em->getConnection()->prepare('INSERT INTO est_tec_instituto_carrera_estudiante_egresado_titulado(gestion_tipo_id, est_tec_instituto_carrera_id, genero_tipo_id, est_tec_egresado_titulados_tipo_id, est_tec_periodo_academico_tipo_id, cantidad, est_tec_grado_academico_tipo_id)'
                                . 'VALUES(:gestion_tipo_id, :est_tec_instituto_carrera_id,:genero_tipo_id,:est_tec_egresado_titulados_tipo_id,:est_tec_periodo_academico_tipo_id, :cantidad, :est_tec_grado_academico_tipo_id)');
                    $query->bindValue(':gestion_tipo_id', $gestion);
                    $query->bindValue(':est_tec_instituto_carrera_id', $carrera_id);
                    $query->bindValue(':genero_tipo_id', 2);
                    $query->bindValue(':est_tec_egresado_titulados_tipo_id', $data[$i]['id']);
                    $query->bindValue(':est_tec_periodo_academico_tipo_id', $j);
                    $query->bindValue(':est_tec_grado_academico_tipo_id', 2); // del primer periodo academico
                    $query->bindValue(':cantidad', 0);                
                    $query->execute();
    
                    }
    
                }

            }
            
        }
    }

    public function get_univ_matricula_nacionalidad_beca_tipo($carrera_id, $nro_periodos, $gestion, $periodo=1){
        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        $periodosql = " and est_tec_grado_academico_tipo_id in (1) ";
        if($periodo == 2){
            $periodosql = " and est_tec_grado_academico_tipo_id in (2)  ";
            //dump($periodosql); die; 
        }
       
        $query ="SELECT
            est_tec_instituto_carrera_estudiante_nacionalidad.id, 
            est_tec_instituto_carrera_estudiante_nacionalidad.gestion_tipo_id, 
            est_tec_instituto_carrera_estudiante_nacionalidad.est_tec_instituto_carrera_id, 
            est_tec_instituto_carrera_estudiante_nacionalidad.genero_tipo_id, 
            est_tec_instituto_carrera_estudiante_nacionalidad.est_tec_periodo_academico_tipo_id, 
            est_tec_instituto_carrera_estudiante_nacionalidad.cantidad, 
            est_tec_instituto_carrera_estudiante_nacionalidad.est_tec_matricula_nacionalidad_beca_tipo_id, 
            est_tec_matricula_nacionalidad_beca_tipo.nacionalidad_beca
        FROM
            est_tec_instituto_carrera_estudiante_nacionalidad
            INNER JOIN
            est_tec_matricula_nacionalidad_beca_tipo
            ON 
                est_tec_instituto_carrera_estudiante_nacionalidad.est_tec_matricula_nacionalidad_beca_tipo_id = est_tec_matricula_nacionalidad_beca_tipo.id
        WHERE
            est_tec_instituto_carrera_id = ".$carrera_id." and gestion_tipo_id = ". $gestion . $periodosql ." order by est_tec_matricula_nacionalidad_beca_tipo.nacionalidad_beca";
        /*if($periodo == 2){
        dump($query);die;
        }*/
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        //dump($data); die;
        $filasaux = array();
        for($i = 0; $i < sizeof($data); $i++ ){
            array_push($filasaux,$data[$i]['est_tec_matricula_nacionalidad_beca_tipo_id'] );
        }
        
        $rowsaux = array_unique($filasaux);
        $filas = array();
        $rows = array();
        foreach ($rowsaux as $valor) {
            array_push($rows, $valor);
        }
        //dump($nro_periodos);die;  3:anual 6 semestral

        $rowsaux = array();
        for($i = 0; $i < sizeof($rows); $i++ ){

            for($j = 0; $j < sizeof($data); $j++ ){
                if ($data[$j]['est_tec_matricula_nacionalidad_beca_tipo_id']  == $rows[$i]){
                    array_push($rowsaux, $data[$j]);
                }
            }

            //dump($rowsaux); die;

            //beca tipo 1
            $m1 = 0; $f1 = 0; $t1 = 0;
            $m2 = 0; $f2 = 0; $t2 = 0;
            $m3 = 0; $f3 = 0; $t3 = 0;
            $m4 = 0; $f4 = 0; $t4 = 0;
            $m5 = 0; $f5 = 0; $t5 = 0;
            $m6 = 0; $f6 = 0; $t6 = 0;

            $m7 = 0; $f7 = 0; $t7 = 0;
            $m8 = 0; $f8 = 0; $t8 = 0;
            $m9 = 0; $f9 = 0; $t9 = 0;
            $m10 = 0; $f10 = 0; $t10 = 0;
            $m11 = 0; $f11 = 0; $t11 = 0;
            $m12 = 0; $f12 = 0; $t12 = 0;


            $idm1= 0; $idf1 = 0;
            $idm2 = 0; $idf2 = 0;
            $idm3 = 0; $idf3 = 0;
            $idm4 = 0; $idf4 = 0;
            $idm5 = 0; $idf5 = 0;
            $idm6 = 0; $idf6 = 0;

            $idm7= 0; $idf7 = 0;
            $idm8 = 0; $idf8 = 0;
            $idm9 = 0; $idf9 = 0;
            $idm10 = 0; $idf10 = 0;
            $idm11 = 0; $idf11 = 0;
            $idm12 = 0; $idf12 = 0;

            for($k = 0; $k < sizeof($rowsaux); $k++ ){               
                $matricula = $rowsaux[$k]['nacionalidad_beca'];   
                
                if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 1){
                    $m1 = $rowsaux[$k]['cantidad'];
                    $idm1 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 1){
                    $f1  = $rowsaux[$k]['cantidad'];
                    $idf1 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 2){
                    $m2 = $rowsaux[$k]['cantidad'];
                    $idm2 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 2){
                    $f2  = $rowsaux[$k]['cantidad'];
                    $idf2 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 3){
                    $m3 = $rowsaux[$k]['cantidad'];
                    $idm3 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 3){
                    $f3  = $rowsaux[$k]['cantidad'];
                    $idf3 =  $rowsaux[$k]['id'];
                }

                if($nro_periodos == 6){ //semestral se debn mostrar 12 ?
                    if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 4){
                        $m4  = $rowsaux[$k]['cantidad'];
                        $idm4 =  $rowsaux[$k]['id'];
                    }
                    if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 4){
                        $f4  = $rowsaux[$k]['cantidad'];
                        $idf4 =  $rowsaux[$k]['id'];
                    }   

                    if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 5){
                        $m5  = $rowsaux[$k]['cantidad'];
                        $idm5 =  $rowsaux[$k]['id'];
                    }
                    if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 5){
                        $f5  = $rowsaux[$k]['cantidad'];
                        $idf5 =  $rowsaux[$k]['id'];
                    }   

                    if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 6){
                        $m6  = $rowsaux[$k]['cantidad'];
                        $idm6 =  $rowsaux[$k]['id'];
                    }
                    if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 6){
                        $f6  = $rowsaux[$k]['cantidad'];
                        $idf6 =  $rowsaux[$k]['id'];
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
                'total2' => $m2 + $f2,

                'm3'  => $m3,
                'idm3'  => $idm3,
                'f3'  => $f3,
                'idf3'  => $idf3,
                'total3' => $m3 + $f3,

                'm4'  => $m4,
                'idm4'  => $idm4,
                'f4'  => $f4,
                'idf4'  => $idf4,
                'total4' => $m4 + $f4,

                'm5'  => $m5,
                'idm5'  => $idm5,
                'f5'  => $f5,
                'idf5'  => $idf5,
                'total5' => $m5 + $f5,

                'm6'  => $m6,
                'idm6'  => $idm6,
                'f6'  => $f6,
                'idf6'  => $idf6,
                'total6' => $m6 + $f6,

                'm7'  => $m7,
                'idm7'  => $idm7,
                'f7'  => $f7,
                'idf7'  => $idf7,
                'total7' => $m7 + $f7,

                'm8'  => $m8,
                'idm8'  => $idm8,
                'f8'  => $f8,
                'idf8'  => $idf8,
                'total8' => $m8 + $f8,

                'm9'  => $m9,
                'idm9'  => $idm9,
                'f9'  => $f9,
                'idf9'  => $idf9,
                'total9' => $m9 + $f9,

                'm10'  => $m10,
                'idm10'  => $idm10,
                'f10'  => $f10,
                'idf10'  => $idf10,
                'total10' => $m10 + $f10,

                'm11'  => $m11,
                'idm11'  => $idm11,
                'f11'  => $f11,
                'idf11'  => $idf11,
                'total11' => $m11 + $f11,

                'm12'  => $m12,
                'idm12'  => $idm12,
                'f12'  => $f12,
                'idf12'  => $idf12,
                'total12' => $m12 + $f12,

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

    public function get_univ_universidad_carrera_estudiante_estado($carrera_id, $nro_periodos, $gestion, $periodo=1){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        $periodosql = " and est_tec_grado_academico_tipo_id in (1) ";
        if($periodo == 2){
            $periodosql = " and est_tec_grado_academico_tipo_id in (2)  ";
            //dump($periodosql); die; 
        }

        $query ="
        SELECT
            est_tec_instituto_carrera_estudiante_estado.id, 
            est_tec_instituto_carrera_estudiante_estado.gestion_tipo_id, 
            est_tec_instituto_carrera_estudiante_estado.est_tec_instituto_carrera_id, 
            est_tec_instituto_carrera_estudiante_estado.genero_tipo_id, 
            est_tec_instituto_carrera_estudiante_estado.est_tec_estadomatricula_tipo_id, 
            est_tec_instituto_carrera_estudiante_estado.est_tec_periodo_academico_tipo_id, 
            est_tec_instituto_carrera_estudiante_estado.cantidad, 
            est_tec_estadomatricula_tipo.estadomatricula
        FROM
            est_tec_instituto_carrera_estudiante_estado
            INNER JOIN
            est_tec_estadomatricula_tipo
            ON 
                est_tec_instituto_carrera_estudiante_estado.est_tec_estadomatricula_tipo_id = est_tec_estadomatricula_tipo.id
            where est_tec_instituto_carrera_id = ".$carrera_id." and gestion_tipo_id = ". $gestion.  $periodosql.  " order by est_tec_estadomatricula_tipo.estadomatricula";        

        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        //dump($data); die;
        $filasaux = array();
        for($i = 0; $i < sizeof($data); $i++ ){
             array_push($filasaux,$data[$i]['est_tec_estadomatricula_tipo_id'] );
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
                if ($data[$j]['est_tec_estadomatricula_tipo_id']  == $rows[$i]){
                    array_push($rowsaux, $data[$j]);
                }
            }

            //beca tipo 1
            $m1 = 0; $f1 = 0; $t1 = 0;
            $m2 = 0; $f2 = 0; $t2 = 0;
            $m3 = 0; $f3 = 0; $t3 = 0;
            $m4 = 0; $f4 = 0; $t4 = 0;
            $m5 = 0; $f5 = 0; $t5 = 0;
            $m6 = 0; $f6 = 0; $t6 = 0;

            $m7 = 0; $f7 = 0; $t7 = 0;
            $m8 = 0; $f8 = 0; $t8 = 0;
            $m9 = 0; $f9 = 0; $t9 = 0;
            $m10 = 0; $f10 = 0; $t10 = 0;
            $m11 = 0; $f11 = 0; $t11 = 0;
            $m12 = 0; $f12 = 0; $t12 = 0;


            $idm2 = 0; $idf2 = 0;
            $idm1= 0; $idf1 = 0;
            $idm3 = 0; $idf3 = 0;
            $idm4 = 0; $idf4 = 0;
            $idm5 = 0; $idf5 = 0;
            $idm6 = 0; $idf6 = 0;

            $idm7= 0; $idf7 = 0;
            $idm8 = 0; $idf8 = 0;
            $idm9 = 0; $idf9 = 0;
            $idm10 = 0; $idf10 = 0;
            $idm11 = 0; $idf11 = 0;
            $idm12 = 0; $idf12 = 0;

            for($k = 0; $k < sizeof($rowsaux); $k++ ){               
                $matricula = $rowsaux[$k]['estadomatricula'];

                if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 1){
                    $m1  = $rowsaux[$k]['cantidad'];
                    $idm1 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 1){
                    $f1  = $rowsaux[$k]['cantidad'];
                    $idf1 =  $rowsaux[$k]['id'];
                }

                if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 2){
                    $m2  = $rowsaux[$k]['cantidad'];
                    $idm2 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 2){
                    $f2  = $rowsaux[$k]['cantidad'];
                    $idf2 =  $rowsaux[$k]['id'];
                }   

                if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 3){
                    $m3  = $rowsaux[$k]['cantidad'];
                    $idm3 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 3){
                    $f3  = $rowsaux[$k]['cantidad'];
                    $idf3 =  $rowsaux[$k]['id'];
                }   

                if($nro_periodos == 6){
                    if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 4){
                        $m4  = $rowsaux[$k]['cantidad'];
                        $idm4 =  $rowsaux[$k]['id'];
                    }
                    if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 4){
                        $f4  = $rowsaux[$k]['cantidad'];
                        $idf4 =  $rowsaux[$k]['id'];
                    }   
                    if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 5){
                        $m5  = $rowsaux[$k]['cantidad'];
                        $idm5 =  $rowsaux[$k]['id'];
                    }
                    if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 5){
                        $f5  = $rowsaux[$k]['cantidad'];
                        $idf5 =  $rowsaux[$k]['id'];
                    }   
                    if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 6){
                        $m6  = $rowsaux[$k]['cantidad'];
                        $idm6 =  $rowsaux[$k]['id'];
                    }
                    if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 6){
                        $f6  = $rowsaux[$k]['cantidad'];
                        $idf6 =  $rowsaux[$k]['id'];
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
                'total2' => $m2 + $f2,

                'm3'  => $m3,
                'idm3'  => $idm3,
                'f3'  => $f3,
                'idf3'  => $idf3,
                'total3' => $m3 + $f3,

                'm4'  => $m4,
                'idm4'  => $idm4,
                'f4'  => $f4,
                'idf4'  => $idf4,
                'total4' => $m4 + $f4,

                'm5'  => $m5,
                'idm5'  => $idm5,
                'f5'  => $f5,
                'idf5'  => $idf5,
                'total5' => $m5 + $f5,

                'm6'  => $m6,
                'idm6'  => $idm6,
                'f6'  => $f6,
                'idf6'  => $idf6,
                'total6' => $m6 + $f6,

                'm7'  => $m7,
                'idm7'  => $idm7,
                'f7'  => $f7,
                'idf7'  => $idf7,
                'total7' => $m7 + $f7,

                'm8'  => $m8,
                'idm8'  => $idm8,
                'f8'  => $f8,
                'idf8'  => $idf8,
                'total8' => $m8 + $f8,

                'm9'  => $m9,
                'idm9'  => $idm9,
                'f9'  => $f9,
                'idf9'  => $idf9,
                'total9' => $m9 + $f9,

                'm10'  => $m10,
                'idm10'  => $idm10,
                'f10'  => $f10,
                'idf10'  => $idf10,
                'total10' => $m10 + $f10,

                'm11'  => $m11,
                'idm11'  => $idm11,
                'f11'  => $f11,
                'idf11'  => $idf11,
                'total11' => $m11 + $f11,

                'm12'  => $m12,
                'idm12'  => $idm12,
                'f12'  => $f12,
                'idf12'  => $idf12,
                'total12' => $m12 + $f12,
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

    public function get_univ_universidad_carrera_docente_administrativo($carrera_id, $nro_periodos, $gestion, $periodo=1){
        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        $periodosql = " and est_tec_grado_academico_tipo_id in (1) ";
        if($periodo == 2){
            $periodosql = " and est_tec_grado_academico_tipo_id in (2)  ";
            //dump($periodosql); die; 
        }

        $query ="
        SELECT
            est_tec_instituto_carrera_docente_administrativo.id, 
            est_tec_instituto_carrera_docente_administrativo.gestion_tipo_id, 
            est_tec_instituto_carrera_docente_administrativo.est_tec_instituto_carrera_id, 
            est_tec_instituto_carrera_docente_administrativo.genero_tipo_id, 
            est_tec_instituto_carrera_docente_administrativo.est_tec_cargo_tipo_id, 
            est_tec_instituto_carrera_docente_administrativo.est_tec_periodo_academico_tipo_id, 
            est_tec_instituto_carrera_docente_administrativo.cantidad, 
            est_tec_cargo_tipo.cargo
        FROM
            est_tec_instituto_carrera_docente_administrativo
            INNER JOIN
            est_tec_cargo_tipo
            ON 
            est_tec_instituto_carrera_docente_administrativo.est_tec_cargo_tipo_id = est_tec_cargo_tipo.id and est_tec_cargo_tipo.es_docente = true 
            where est_tec_instituto_carrera_id = ".$carrera_id." and gestion_tipo_id = ". $gestion . $periodosql ." order by  est_tec_cargo_tipo.cargo";        

        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        //dump($data); die;
        $filasaux = array();
        for($i = 0; $i < sizeof($data); $i++ ){
             array_push($filasaux,$data[$i]['est_tec_cargo_tipo_id'] );
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
                if ($data[$j]['est_tec_cargo_tipo_id']  == $rows[$i]){
                    array_push($rowsaux, $data[$j]);
                }
            }

           
            $m1 = 0; $f1 = 0; $t1 = 0;
            $m2 = 0; $f2 = 0; $t2 = 0;

            $m3 = 0; $f3 = 0; $t3 = 0;
            $m4 = 0; $f4 = 0; $t4 = 0;
            $m5 = 0; $f5 = 0; $t5 = 0;
            $m6 = 0; $f6 = 0; $t6 = 0;

            $m7 = 0; $f7 = 0; $t7 = 0;
            $m8 = 0; $f8 = 0; $t8 = 0;
            $m9 = 0; $f9 = 0; $t9 = 0;
            $m10 = 0; $f10 = 0; $t10 = 0;
            $m11 = 0; $f11 = 0; $t11 = 0;
            $m12 = 0; $f12 = 0; $t12 = 0;


            $idm2 = 0; $idf2 = 0;
            $idm1= 0; $idf1 = 0;
            $idm3= 0; $idf3 = 0;
            $idm4= 0; $idf4 = 0;
            $idm5= 0; $idf5 = 0;
            $idm6= 0; $idf6 = 0;

            $idm7= 0; $idf7 = 0;
            $idm8= 0; $idf8 = 0;
            $idm9= 0; $idf9 = 0;
            $idm10= 0; $idf10 = 0;
            $idm11= 0; $idf11 = 0;
            $idm12= 0; $idf12 = 0;

            for($k = 0; $k < sizeof($rowsaux); $k++ ){               
                $matricula = $rowsaux[$k]['cargo'];

                if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 1){
                    $m1  = $rowsaux[$k]['cantidad'];
                    $idm1 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 1){
                    $f1  = $rowsaux[$k]['cantidad'];
                    $idf1 =  $rowsaux[$k]['id'];
                }

                if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 2){
                    $m2  = $rowsaux[$k]['cantidad'];
                    $idm2 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 2){
                    $f2  = $rowsaux[$k]['cantidad'];
                    $idf2 =  $rowsaux[$k]['id'];
                }   

                if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 3){
                    $m3  = $rowsaux[$k]['cantidad'];
                    $idm3 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 3){
                    $f3  = $rowsaux[$k]['cantidad'];
                    $idf3 =  $rowsaux[$k]['id'];
                }   

                if($nro_periodos == 6){
                    if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 4){
                        $m4  = $rowsaux[$k]['cantidad'];
                        $idm4 =  $rowsaux[$k]['id'];
                    }
                    if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 4){
                        $f4  = $rowsaux[$k]['cantidad'];
                        $idf4 =  $rowsaux[$k]['id'];
                    }   
                    if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 5){
                        $m5  = $rowsaux[$k]['cantidad'];
                        $idm5 =  $rowsaux[$k]['id'];
                    }
                    if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 5){
                        $f5  = $rowsaux[$k]['cantidad'];
                        $idf5 =  $rowsaux[$k]['id'];
                    }   
                    if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 6){
                        $m6  = $rowsaux[$k]['cantidad'];
                        $idm6 =  $rowsaux[$k]['id'];
                    }
                    if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 6){
                        $f6  = $rowsaux[$k]['cantidad'];
                        $idf6 =  $rowsaux[$k]['id'];
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
                'total2' => $m2 + $f2,

                'm3'  => $m3,
                'idm3'  => $idm3,
                'f3'  => $f3,
                'idf3'  => $idf3,
                'total3' => $m3 + $f3,

                'm4'  => $m4,
                'idm4'  => $idm4,
                'f4'  => $f4,
                'idf4'  => $idf4,
                'total4' => $m4 + $f4,

                'm5'  => $m5,
                'idm5'  => $idm5,
                'f5'  => $f5,
                'idf5'  => $idf5,
                'total5' => $m5 + $f5,

                'm6'  => $m6,
                'idm6'  => $idm6,
                'f6'  => $f6,
                'idf6'  => $idf6,
                'total6' => $m6 + $f6,

                'm7'  => $m7,
                'idm7'  => $idm7,
                'f7'  => $f7,
                'idf7'  => $idf7,
                'total7' => $m7 + $f7,

                'm8'  => $m8,
                'idm8'  => $idm8,
                'f8'  => $f8,
                'idf8'  => $idf8,
                'total8' => $m8 + $f8,

                'm9'  => $m9,
                'idm9'  => $idm9,
                'f9'  => $f9,
                'idf9'  => $idf9,
                'total9' => $m9 + $f9,

                'm10'  => $m10,
                'idm10'  => $idm10,
                'f10'  => $f10,
                'idf10'  => $idf10,
                'total10' => $m10 + $f10,

                'm11'  => $m11,
                'idm11'  => $idm11,
                'f11'  => $f11,
                'idf11'  => $idf11,
                'total11' => $m11 + $f11,

                'm12'  => $m12,
                'idm12'  => $idm12,
                'f12'  => $f12,
                'idf12'  => $idf12,
                'total12' => $m12 + $f12,
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

    public function get_univ_universidad_carrera_egresados($carrera_id, $nro_periodos, $gestion, $periodo=1){
        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        $periodosql = " and est_tec_grado_academico_tipo_id in (1) ";
        if($periodo == 2){
            $periodosql = " and est_tec_grado_academico_tipo_id in (2)  ";
            //dump($periodosql); die; 
        }

        $query ="
        SELECT
            est_tec_instituto_carrera_estudiante_egresado_titulado.id, 
            est_tec_instituto_carrera_estudiante_egresado_titulado.gestion_tipo_id, 
            est_tec_instituto_carrera_estudiante_egresado_titulado.est_tec_instituto_carrera_id, 
            est_tec_instituto_carrera_estudiante_egresado_titulado.genero_tipo_id, 
            est_tec_instituto_carrera_estudiante_egresado_titulado.est_tec_egresado_titulados_tipo_id, 
            est_tec_instituto_carrera_estudiante_egresado_titulado.est_tec_periodo_academico_tipo_id, 
            est_tec_instituto_carrera_estudiante_egresado_titulado.cantidad, 
            est_tec_egresado_titulados_tipo.estado_final as cargo
        FROM
        est_tec_instituto_carrera_estudiante_egresado_titulado
            INNER JOIN
            est_tec_egresado_titulados_tipo
            ON 
            est_tec_instituto_carrera_estudiante_egresado_titulado.est_tec_egresado_titulados_tipo_id = est_tec_egresado_titulados_tipo.id
            where est_tec_instituto_carrera_id = ".$carrera_id." and gestion_tipo_id = ". $gestion . $periodosql ." order by  est_tec_egresado_titulados_tipo.estado_final";        

       
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        //dump($data); die;
        $filasaux = array();
        for($i = 0; $i < sizeof($data); $i++ ){
             array_push($filasaux,$data[$i]['est_tec_egresado_titulados_tipo_id'] );
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
                if ($data[$j]['est_tec_egresado_titulados_tipo_id']  == $rows[$i]){
                    array_push($rowsaux, $data[$j]);
                }
            }

           
            $m1 = 0; $f1 = 0; $t1 = 0;
            $m2 = 0; $f2 = 0; $t2 = 0;

            $m3 = 0; $f3 = 0; $t3 = 0;
            $m4 = 0; $f4 = 0; $t4 = 0;
            $m5 = 0; $f5 = 0; $t5 = 0;
            $m6 = 0; $f6 = 0; $t6 = 0;

            $m7 = 0; $f7 = 0; $t7 = 0;
            $m8 = 0; $f8 = 0; $t8 = 0;
            $m9 = 0; $f9 = 0; $t9 = 0;
            $m10 = 0; $f10 = 0; $t10 = 0;
            $m11 = 0; $f11 = 0; $t11 = 0;
            $m12 = 0; $f12 = 0; $t12 = 0;


            $idm2 = 0; $idf2 = 0;
            $idm1= 0; $idf1 = 0;
            $idm3= 0; $idf3 = 0;
            $idm4= 0; $idf4 = 0;
            $idm5= 0; $idf5 = 0;
            $idm6= 0; $idf6 = 0;

            $idm7= 0; $idf7 = 0;
            $idm8= 0; $idf8 = 0;
            $idm9= 0; $idf9 = 0;
            $idm10= 0; $idf10 = 0;
            $idm11= 0; $idf11 = 0;
            $idm12= 0; $idf12 = 0;

            for($k = 0; $k < sizeof($rowsaux); $k++ ){               
                $matricula = $rowsaux[$k]['cargo'];

                if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 1){
                    $m1  = $rowsaux[$k]['cantidad'];
                    $idm1 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 1){
                    $f1  = $rowsaux[$k]['cantidad'];
                    $idf1 =  $rowsaux[$k]['id'];
                }

                if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 2){
                    $m2  = $rowsaux[$k]['cantidad'];
                    $idm2 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 2){
                    $f2  = $rowsaux[$k]['cantidad'];
                    $idf2 =  $rowsaux[$k]['id'];
                }   

                if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 3){
                    $m3  = $rowsaux[$k]['cantidad'];
                    $idm3 =  $rowsaux[$k]['id'];
                }
                if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 3){
                    $f3  = $rowsaux[$k]['cantidad'];
                    $idf3 =  $rowsaux[$k]['id'];
                }   

                if($nro_periodos == 6){
                    if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 4){
                        $m4  = $rowsaux[$k]['cantidad'];
                        $idm4 =  $rowsaux[$k]['id'];
                    }
                    if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 4){
                        $f4  = $rowsaux[$k]['cantidad'];
                        $idf4 =  $rowsaux[$k]['id'];
                    }   
                    if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 5){
                        $m5  = $rowsaux[$k]['cantidad'];
                        $idm5 =  $rowsaux[$k]['id'];
                    }
                    if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 5){
                        $f5  = $rowsaux[$k]['cantidad'];
                        $idf5 =  $rowsaux[$k]['id'];
                    }   
                    if($rowsaux[$k]['genero_tipo_id'] == 1 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 6){
                        $m6  = $rowsaux[$k]['cantidad'];
                        $idm6 =  $rowsaux[$k]['id'];
                    }
                    if($rowsaux[$k]['genero_tipo_id'] == 2 and $rowsaux[$k]['est_tec_periodo_academico_tipo_id'] == 6){
                        $f6  = $rowsaux[$k]['cantidad'];
                        $idf6 =  $rowsaux[$k]['id'];
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
                'total2' => $m2 + $f2,

                'm3'  => $m3,
                'idm3'  => $idm3,
                'f3'  => $f3,
                'idf3'  => $idf3,
                'total3' => $m3 + $f3,

                'm4'  => $m4,
                'idm4'  => $idm4,
                'f4'  => $f4,
                'idf4'  => $idf4,
                'total4' => $m4 + $f4,

                'm5'  => $m5,
                'idm5'  => $idm5,
                'f5'  => $f5,
                'idf5'  => $idf5,
                'total5' => $m5 + $f5,

                'm6'  => $m6,
                'idm6'  => $idm6,
                'f6'  => $f6,
                'idf6'  => $idf6,
                'total6' => $m6 + $f6,

                'm7'  => $m7,
                'idm7'  => $idm7,
                'f7'  => $f7,
                'idf7'  => $idf7,
                'total7' => $m7 + $f7,

                'm8'  => $m8,
                'idm8'  => $idm8,
                'f8'  => $f8,
                'idf8'  => $idf8,
                'total8' => $m8 + $f8,

                'm9'  => $m9,
                'idm9'  => $idm9,
                'f9'  => $f9,
                'idf9'  => $idf9,
                'total9' => $m9 + $f9,

                'm10'  => $m10,
                'idm10'  => $idm10,
                'f10'  => $f10,
                'idf10'  => $idf10,
                'total10' => $m10 + $f10,

                'm11'  => $m11,
                'idm11'  => $idm11,
                'f11'  => $f11,
                'idf11'  => $idf11,
                'total11' => $m11 + $f11,

                'm12'  => $m12,
                'idm12'  => $idm12,
                'f12'  => $f12,
                'idf12'  => $idf12,
                'total12' => $m12 + $f12,
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
                $query ="update est_tec_instituto_carrera_estudiante_nacionalidad set cantidad = ? where id = ?";
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
                $query ="update est_tec_instituto_carrera_estudiante_estado set cantidad = ? where id = ?";
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
                $query ="update est_tec_instituto_carrera_docente_administrativo set cantidad = ? where id = ?";
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


    public function statsTituladosSaveAction(Request $request){

        //dump('here'); die;

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
                $query ="update est_tec_instituto_carrera_estudiante_egresado_titulado set cantidad = ? where id = ?";
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
        
        //dump($carrera_id, $gestion_id); die;

        try {              

            //update al registro, solo se cambia cantidad
            $query ="update est_tec_instituto_carrera_ctr set est_tec_estadocarrera_tipo_id = 2, justificacion = ? where est_tec_instituto_carrera_id = ? and gestion_tipo_id = ?";
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

    
}
