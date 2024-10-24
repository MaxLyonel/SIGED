<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoModalidadAtencion;

/**
 * ModalidadCurso controller.
 *
 */
class ModalidadCursoController extends Controller {

    public $session;
    public $month;
    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request){

    	$infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        if($this->session->get('pathSystem')  == 'SiePermanenteBundle'){
            $cursoId = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecId'];
        }else {
            $cursoId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        }

        $week = date('W');

        $arrDateNow = array('mon'=>getDate()['mon'], 'week'=>$week, 'today' => date("M/Y"));
        
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($cursoId);
        if(!$entity){
        	$entity = new InstitucioneducativaCurso();
        }
        
        $objInstitucioneducativaCursoModalidadAtencion = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoModalidadAtencion')->findby(array('institucioneducativaCurso'=>$cursoId, 'mes'=>getDate()['mon'], 'semana'=>$week ));

        //$objInstitucioneducativaCursoModalidadAtencion = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoModalidadAtencion')->findby(array('institucioneducativaCurso'=>$cursoId, 'mes'=>3));
        $arrModalidades = array('option_1'=>false,'option_2'=>false,'option_3'=>false);
        if($objInstitucioneducativaCursoModalidadAtencion){
            foreach($objInstitucioneducativaCursoModalidadAtencion as $value){
                $arrModalidades['option_'.$value->getModalidadAtencionTipo()->getId()]=true;
            }
        }

        $form = $this->createFormCurso($entity);
		$form->handleRequest($request);
        //array('mon'=>getDate()['mon'], 'today' => date("M/Y"))
        
    	return $this->render('SieHerramientaBundle:ModalidadCurso:index.html.twig', array(
    		'curso' => $entity,
            'form' => $form->createView(),
            'arrModalidades'=>$arrModalidades,
    		'arrDate' => $arrDateNow
    	));
    }

    private function createFormCurso($entity){
    	$form = $this->createFormBuilder($entity)
    				->add('id','hidden',array('data'=>$entity->getId()))                    
                        ->getForm();
    	return $form;
    }

    public function saveAction(Request $request){
    	$em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        
        $objIecurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($form['id']);
    	if($objIecurso){
    		
            $objInstitucioneducativaCursoModalidadAtencion = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoModalidadAtencion')->findby(array('institucioneducativaCurso'=>$form['id'], 'mes'=>$form['mon'], 'semana'=>$form['week']));
            if($objInstitucioneducativaCursoModalidadAtencion){
                foreach ($objInstitucioneducativaCursoModalidadAtencion as $value){
                    $em->remove($value);
                }
                $em->flush();
            }
            $arrModalidades = $form['opcion'];
            if($arrModalidades !==NULL && count($arrModalidades)>0){
                foreach ($arrModalidades as $value){
                        $value=filter_var($value,FILTER_VALIDATE_INT);
                        $modalidadAtencion= new InstitucioneducativaCursoModalidadAtencion();
                        $modalidadAtencion->setFechaRegistro(new \DateTime());
                        $modalidadAtencion->setObservacion('no');
                        $modalidadAtencion->setMes($form['mon']);
                        $modalidadAtencion->setSemana($form['week']);
                        $modalidadAtencion->setInstitucioneducativaCurso($objIecurso);
                        $modalidadAtencion->setModalidadAtencionTipo($em->getRepository('SieAppWebBundle:ModalidadAtencionTipo')->find($value));
                        $em->persist($modalidadAtencion);
                        $tipos[]=$value;
                    
                }
                $em->flush();
            }
       
    		$em->flush();

    		$data = array(
    			'msg'=> 'Datos registrados correctamente',
    			'status' => 201
    		);
    	}else{
    		$data = array(
    			'msg' => 'Los datos ingresados son incorrectos',
    			'status' => 500
    		);
    	}

    	$response = new JsonResponse();

    	return $response->setData($data);
    }

    public function getModulos2024Action(Request $request){

        $institucion = $request->getSession()->get('ie_id');
        $sucursal = $request->getSession()->get('ie_subcea');

        /*dump($institucion);
        dump($sucursal);
        die; */
        
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
                
        $acreditacion = $aInfoUeducativa['ueducativaInfo']['grado'];
        $especialidad = $aInfoUeducativa['ueducativaInfo']['ciclo'];

        $ciclo    = $aInfoUeducativa['ueducativaInfo']['ciclo'];
        $nivel    = $aInfoUeducativa['ueducativaInfo']['nivel'];
        $grado    = $aInfoUeducativa['ueducativaInfo']['grado'];
        $paralelo = $aInfoUeducativa['ueducativaInfo']['paralelo'];
        $turno    = $aInfoUeducativa['ueducativaInfo']['turno'];


       /*dump($acreditacion);
        dump($especialidad);
        die; */

        /*
        array:2 [
            "ueducativaInfo" => array:6 [
                "ciclo" => "BELLEZA INTEGRAL"
                "nivel" => "SERVICIO"
                "superiorAcreditacionTipoId" => 1
                "grado" => "TÉCNICO BÁSICO"
                "paralelo" => "A"
                "turno" => "NOCHE"
            ]
            "ueducativaInfoId" => array:11 [
                "nivelId" => 22
                "cicloId" => 2
                "gradoId" => 1
                "turnoId" => 4
                "paraleloId" => "1"
                "iecId" => 167562291
                "setCodigo" => 2
                "satCodigo" => 1
                "sfatCodigo" => 22
                "setId" => 53
                "periodoId" => 3
            ]
            ]
        */
        

        //61880184

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("                        
        
        select distinct 
        modulo_tipo_id, modulo
        from        
       (
        WITH catalogo AS (select a.codigo as nivel_id, a.facultad_area,b.codigo as ciclo_id,b.especialidad,d.codigo as grado_id,d.acreditacion,c.id as superior_acreditacion_especialidad_id
        from superior_facultad_area_tipo a
            inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                    inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id)
        select distinct o.id as institucioneducativa_curso_oferta_id,k.*,i.periodo_tipo_id
        ,n.id as modulo_tipo_id,n.modulo,c.id
        from superior_institucioneducativa_acreditacion a
            inner join superior_institucioneducativa_periodo b on b.superior_institucioneducativa_acreditacion_id=a.id
                inner join institucioneducativa_curso c on c.superior_institucioneducativa_periodo_id=b.id
                    inner join institucioneducativa_sucursal i on a.institucioneducativa_sucursal_id=i.id
                        inner join catalogo k on a.acreditacion_especialidad_id=k.superior_acreditacion_especialidad_id
                            inner join superior_modulo_periodo m on m.institucioneducativa_periodo_id=b.id
                                inner join superior_modulo_tipo n on m.superior_modulo_tipo_id=n.id
                                    inner join institucioneducativa_curso_oferta o on o.insitucioneducativa_curso_id=c.id and o.superior_modulo_periodo_id=m.id
        where  i.gestion_tipo_id in (2023::double precision,2024::double precision) and  i.institucioneducativa_id= :sie and i.sucursal_tipo_id = :sucursal 
        and k.nivel_id in (15,18,19,20,21,22,23,24,25)
        and acreditacion = :acreditacion and especialidad = :especialidad
        order by modulo
        ) as data order by 2
        "); 
        $query->bindValue(':sie', $institucion);
        $query->bindValue(':sucursal', $sucursal);
        $query->bindValue(':acreditacion', $acreditacion);
        $query->bindValue(':especialidad', $especialidad);

        $query->execute();
        $modulos = $query->fetchAll(); 

        //dump($modulos); die;

        $response = new JsonResponse();
        return $response->setData(array('modulos' => $modulos, 'infoUe' => str_replace("\"", "#", $infoUe),'ciclo' =>$ciclo, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno  ));
    }

    public function saveModulos2024Action(Request $request){

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 
        $response = new JsonResponse();

        $valores = $request->request->all();
        /*dump($valores); 
        die;*/

        $info= '';

        foreach ($valores as $clave => $valor) {    
          
            if($clave == 'input_info'){
                $info = $valor;
            }
        }

        $info = str_replace("#", "\"",  $info);
        $aInfoUeducativa = unserialize($info);
        dump($aInfoUeducativa); die;

        // variables del curso
        $nivelId    = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $cicloId    = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
        $gradoId    = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turnoId    = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        $paraleloId = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
        $periodoId  = $aInfoUeducativa['ueducativaInfoId']['periodoId'];

        $arr=array();
        $modulos=array();

        // los cheks con el superior_modulo_tipo_id
        foreach ($valores as $clave => $valor) {    
            
            if($clave <> 'input_info'){

                if(substr($clave,0,5) == 'input'){
                    $aux = $clave;
                    $desde = strpos($aux, "_");
                    $id = substr($aux,$desde + 1, strlen($aux));

                    $item = array(
                        "gestion_tipo_id"           => 2024,
                        "institucioneducativa_id"   => $request->getSession()->get('ie_id'),
                        "sucursal_tipo_id"          => $request->getSession()->get('ie_suc_id'),
                        "periodo_tipo_id"           => $request->getSession()->get('ie_per_cod'),
                        "turno_tipo_id"             => $turnoId,
                        "paralelo_tipo_id"          => $paraleloId,
                        "nivel_id"                  => $nivelId,
                        "ciclo_id"                  => $cicloId,
                        "grado_id"                  => $gradoId,
                        "superior_modulo_tipo_id"   => $id,
                        "modulo"                    => 'mecanica'

                    );
                    
                    array_push($modulos,$item);

                }

            }
            
        }

        //array_push($arr,array("modulos" => $modulos));

        dump($modulos);
        dump(json_encode($modulos));
        die;

        /*
        me envias esto para hacer la funcion de registro de datos: me envias,
        (gestion_tipo_id,institucioneducativa_id,sucursal_tipo_id,periodo_tipo_id,turno_tipo_id,paralelo_tipo_id,nivel_id,ciclo_id,grado_id,id_modulo,modulo) en json

        */


    }

}
