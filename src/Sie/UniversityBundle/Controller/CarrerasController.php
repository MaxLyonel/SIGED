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
       
        $form = $request->get('form');
        
        $data = ($this->kdecrypt($form['data']));

        //dump($data); die;
        // univalle sede central = 62
        
        $sedeId = $data['sedeId'];
        $userId = $data['userId'];


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
        $carreras_post = $em->getRepository('SieAppWebBundle:UnivUniversidadCarrera')->findBy(array('univSede' => $entityUnivSedeCentral, 'univNivelAcademicoTipo' => $entityPostgrado), array('carrera'=> 'ASC'));
        
        $niveles = $em->getRepository('SieAppWebBundle:UnivNivelAcademicoTipo')->findAll();       
        $modalidad = $em->getRepository('SieAppWebBundle:UnivModalidadEnsenanzaTipo')->findAll();       
        $grado_academico = $em->getRepository('SieAppWebBundle:UnivClaGradoAcademico')->findAll();       
        $regimen_estudios = $em->getRepository('SieAppWebBundle:UnivregimenEstudiosTipo')->findAll();       
        $periodo_academico = $em->getRepository('SieAppWebBundle:UnivPeriodoAcademicoTipo')->findAll();       
       
        return $this->render('SieUniversityBundle:Carreras:index.html.twig', array(
            'sedes' => $entityUnivSede,
            'central' => $entityUniv[0],
            'titulo' => "Carreras",
            'carreras' => $carreras,      
            'carreras_post' => $carreras_post,      
            'niveles' => $niveles,           
            'modalidad' => $modalidad,           
            'grado_academico' => $grado_academico,           
            'regimen_estudios' => $regimen_estudios,           
            'periodo_academico' => $periodo_academico,   
            'total_carreras' => $total_carreras,
            'total_carreras_pre' => $total_carreras_pre,
            'total_carreras_post' => $total_carreras_post

        ));
        
    }


    public function infoAction(Request $request)
    {

        $response = new JsonResponse();

        $carrera_id = $request->query->get('carrera_id');
        $gestion = $request->query->get('gestion');

        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        //$carrera_id = 1582;

        $entityUnivSede = $em->getRepository('SieAppWebBundle:UnivSede')->findBy(array('usuario' => $id_usuario));

        //TODO: JALAR DE LA SESION ?
        $sedeId = $this->session->get('sedeId');

        $entityUnivSedeCentral = $em->getRepository('SieAppWebBundle:UnivSede')->findById($sedeId); //43

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

        if( $carrera_id == 635) {
            $nro_periodos = 1;
        }
        
        $entityUniv = $em->getRepository('SieAppWebBundle:UnivUniversidad')->findById(1);
        
        $gestiones = $em->getRepository('SieAppWebBundle:UnivRegistroConsolidacion')->findAll();      

        $arrData = array('sedeId'=> $sedeId);
        //$arrData = array('sedeId'=> $request->get(64));
        $gestiones = $this->get('univfunctions')->getAllOperative($arrData);
        //dump($gestiones); die;

        $generos = $em->getRepository('SieAppWebBundle:UnivClaGenero')->findAll();   

        if($nro_periodos == 1) {
            $periodos = $em->getRepository('SieAppWebBundle:UnivPeriodoAcademicoTipo')->findById(1);     
        }else{
            $periodos = $em->getRepository('SieAppWebBundle:UnivPeriodoAcademicoTipo')->findAll();    
        }

        $matriculas = $em->getRepository('SieAppWebBundle:UnivMatriculaNacionalidadBecaTipo')->findAll();      
        $matriculasestado = $em->getRepository('SieAppWebBundle:UnivEstadomatriculaTipo')->findAll();      
        $cargos = $em->getRepository('SieAppWebBundle:UnivCargoTipo')->findAll();      


        //datos estudiantes por genero
        $filas = array();
        
        $fila = array(
            'id' => 5,
            'matricula' => "MATRICULADOS EXTRANJEROS",
            'm1'  => 5,
            'f1'  => 6,
            'total1' => 11,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila2 = array(
            'id' => 6,
            'matricula' => "MATRICULADOS NACIONALES",
            'm1'  => 15,
            'f1'  => 1,
            'total1' => 16,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila4 = array(
            'id' => 6,
            'matricula' => "OTRAS BECAS",
            'm1'  => 15,
            'f1'  => 1,
            'total1' => 16,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila5 = array(
            'id' => 6,
            'matricula' => "ESTUDIANTES CON BECAS SOCIALES",
            'm1'  => 15,
            'f1'  => 1,
            'total1' => 16,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila3 = array(   
            'id' => 0,
            'matricula' => "TOTALES",        
            'm1'  => 50,
            'f1'  => 9,
            'total1' => 37,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );

        array_push($filas, $fila);
        array_push($filas, $fila2);
        array_push($filas, $fila4);
        array_push($filas, $fila5);
        array_push($filas, $fila3);


        //datos estudiantes por genero
        $cargos_array = array();
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
        array_push($cargos_array, $fila4);

        //datos estudiantes por tipo de matricula
        $tipo_matricula_array = array();
        $fila = array(
            'id' => 5,
            'matricula' => "MATRICULADO",
            'm1'  => 5,
            'f1'  => 6,
            'total1' => 11,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila2 = array(
            'id' => 6,
            'matricula' => "MATRICULADO NUEVO",
            'm1'  => 15,
            'f1'  => 1,
            'total1' => 16,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila3 = array(
            'id' => 6,
            'matricula' => "EGRESADO",
            'm1'  => 15,
            'f1'  => 1,
            'total1' => 16,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila4 = array(
            'id' => 6,
            'matricula' => "DESERCION",
            'm1'  => 15,
            'f1'  => 1,
            'total1' => 16,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        $fila4 = array(
            'id' => 6,
            'matricula' => "TITULADO",
            'm1'  => 15,
            'f1'  => 1,
            'total1' => 16,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );
        /*$fila4 = array(   
            'id' => 0,
            'matricula' => "TOTALES",        
            'm1'  => 20,
            'f1'  => 7,
            'total1' => 27,
            'm2'  => 0,
            'f2'  => 0,
            'total2' => 0
        );*/

        array_push($tipo_matricula_array, $fila);
        array_push($tipo_matricula_array, $fila2);
        array_push($tipo_matricula_array, $fila3);
        array_push($tipo_matricula_array, $fila4);
        array_push($tipo_matricula_array, $fila5);


       
        $data = array(            
            'periodos' => $nro_periodos,
            'array_estudiantes' => $filas,
            'array_cargos' => $cargos_array,
            'array_tipo_matricula' => $tipo_matricula_array
        );

       

        
        return $this->render('SieUniversityBundle:Carreras:info.html.twig', array(
            'carrera_id' => 100,
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
            'data' => $data

        ));
        

    }

    public function newAction(Request $request)
    {
        date_default_timezone_set('America/La_Paz');
        $response = new JsonResponse();
        
       
        //$sedeId = 43;

        $sedeId = $this->session->get('sedeId');

        $nivel_academico_id = $request->get('nivel_academico_id');
        $modalidad_id = $request->get('modalidad_id');
        $regimen_id = $request->get('regimen_id');
        $grado_id = $request->get('grado_id');
        $duracion = $request->get('duracion');
        $duracion_anios = $request->get('duracion_anios');

        $fecha_apertura_tmp = $request->get('fecha_apertura');
        //15/12/2003
       
        $fecha_apertura = date('Y-m-d', strtotime($fecha_apertura_tmp));

        $em = $this->getDoctrine()->getManager();

        $carreraEntity = new UnivUniversidadCarrera();
       

        $carreraEntity->setUnivSede($em->getRepository('SieAppWebBundle:UnivSede')->find($sedeId));
        $carreraEntity->setUnivAreaConocimiento($request->get('area_conocimiento'));

        $carreraEntity->setUnivNivelAcademicoTipo($em->getRepository('SieAppWebBundle:UnivNivelAcademicoTipo')->find($nivel_academico_id));
        $carreraEntity->setUnivRegimenEstudiosTipo($em->getRepository('SieAppWebBundle:UnivRegimenEstudiosTipo')->find($regimen_id));
        $carreraEntity->setUnivModalidadEnsenanzaTipo($em->getRepository('SieAppWebBundle:UnivModalidadEnsenanzaTipo')->find($modalidad_id));

        $carreraEntity->setCarrera($request->get('carrera'));
        $carreraEntity->setResolucion($request->get('resolucion'));  
        
        //fecha ??
        //rm_apertura ??
        //dump($fecha_apertura); die;
        $carreraEntity->setFechaApertura(new \DateTime( $fecha_apertura));
        
        $carreraEntity->setUnivGradoAcademicoTipo($em->getRepository('SieAppWebBundle:UnivGradoacademicoTipo')->find($grado_id));

        $carreraEntity->setDuracion($request->get('duracion'));
        $carreraEntity->setDuracionAnios($request->get('duracion_anios'));

        //$univJuridicciongeograficaEntity->setFechaRegistro(new \DateTime('now'));
       
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
        $sedeId = 43;
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
       
        $carreraEntity = $em->getRepository('SieAppWebBundle:UnivUniversidadCarrera')->find($carrera_id);       

        $carreraEntity->setUnivSede($em->getRepository('SieAppWebBundle:UnivSede')->find($sedeId));
        $carreraEntity->setUnivAreaConocimiento($request->get('edit_area_conocimiento'));

        $carreraEntity->setUnivNivelAcademicoTipo($em->getRepository('SieAppWebBundle:UnivNivelAcademicoTipo')->find($nivel_academico_id));
        $carreraEntity->setUnivRegimenEstudiosTipo($em->getRepository('SieAppWebBundle:UnivRegimenEstudiosTipo')->find($regimen_id));
        $carreraEntity->setUnivModalidadEnsenanzaTipo($em->getRepository('SieAppWebBundle:UnivModalidadEnsenanzaTipo')->find($modalidad_id));

        $carreraEntity->setCarrera($request->get('edit_carrera'));
        $carreraEntity->setResolucion($request->get('edit_resolucion'));  
        
        //fecha ??
        //rm_apertura ??
        //dump($fecha_apertura); die;
        $carreraEntity->setFechaApertura(new \DateTime( $fecha_apertura));
        
        $carreraEntity->setUnivGradoAcademicoTipo($em->getRepository('SieAppWebBundle:UnivGradoacademicoTipo')->find($grado_id));

        $carreraEntity->setDuracion($request->get('edit_duracion'));
        $carreraEntity->setDuracionAnios($request->get('edit_duracion_anios'));

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


    
}
