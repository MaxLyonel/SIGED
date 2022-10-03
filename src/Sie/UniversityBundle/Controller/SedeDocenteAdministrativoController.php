<?php

namespace Sie\UniversityBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\UnivSedeSucursal;
use Sie\AppWebBundle\Entity\UnivJurisdiccionGeografica;
use Sie\AppWebBundle\Entity\UnivUniversidadSedeDocenteAdm;

use Symfony\Component\HttpFoundation\JsonResponse;

class SedeDocenteAdministrativoController extends Controller
{
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request)
    {
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = $fechaActual->format('Y');
        
        $id_usuario = $this->session->get('userId');
        //$response = new JsonResponse();
        $estado = true;
        $msg = '';
        $editar = true;

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
            
        $em = $this->getDoctrine()->getManager();

        $form = $request->get('form');
        if(isset($form['gestion'])){
            $gestionId = ($form['gestion']);
        } else {
            $gestionId = $gestionActual-1;
        }  
        $data = (unserialize(hex2bin($form['data'])));
        $form = $data;
        //dump($form);die;
        if(isset($form['sedeId'])){
            $sedeId = ($form['sedeId']);
        } else {
            $sedeId = 0;
        }      

        if(!$this->tuisionSede($sedeId,$id_usuario)){            
            $estado = false;
            $msg = 'No esta como usuario en la sede seleccionada, comuniquese con su administrador';
            return $response->setData(array('estado' => $estado, 'msg' => $msg));
        }

        if(!$this->tuisionSede($sedeId,$id_usuario)){
            return $this->render('SieAppWebBundle:Login:login4.html.twig',array(
                'last_username'=>$this->get('request')->getSession()->get(SecurityContext::LAST_USERNAME),
                'error'=>array('message'=>'¡El usuario no se encuenta registrado para acceder a la sede/subsede seleccionada!')
            ));  
        }

        $entityUnivSedeActual = $em->getRepository('SieAppWebBundle:UnivSede')->findOneBy(array('id' => $sedeId));
        $titulo = $entityUnivSedeActual->getUnivUniversidad()->getUniversidad();
        $subtitulo = $entityUnivSedeActual->getSede();

        $sedeSucursalEntity = $em->getRepository('SieAppWebBundle:UnivSedeSucursal')->findBy(array('univSede'=>$sedeId, 'gestionTipo'=>$gestionId));
        
        $info = bin2hex(serialize(array('sedeId'=>$sedeId,'gestionId'=>$gestionId)));

        $baseData = array('sedeId'=>$sedeId,'yearSelected'=>$gestionId);

        $query = $em->getConnection()->prepare('
            select coalesce(usda.cantidad,0) as cantidad, est.* from (select * from univ_universidad_sede_docente_adm where univ_sede_id = :sede_id and gestion_tipo_id = :gestion_id) as usda
            RIGHT JOIN (
            select ct.id as cargo_tipo_id, ct.cargo, gt.id as genero_tipo_id, gt.genero 
            from univ_cargo_tipo as ct 
            cross join genero_tipo as gt
            ) as est on est.cargo_tipo_id = usda.univ_cargo_tipo_id and est.genero_tipo_id = usda.genero_tipo_id
            order by est.cargo_tipo_id, est.genero_tipo_id
        ');
        $query->bindValue(':sede_id', $sedeId);
        $query->bindValue(':gestion_id', $gestionId);
        $query->execute();
        $universidadSedeDocenteAdmEntity = $query->fetchAll();

        $array = array();
        foreach ($universidadSedeDocenteAdmEntity as $dato) {
            $info = bin2hex(serialize(array('sedeId'=>$sedeId,'gestionId'=>$gestionId,'cargoId'=>$dato['cargo_tipo_id'], 'generoId'=>$dato['genero_tipo_id'])));
            if(isset($array['TOTAL'][$dato['genero']])){
                $array['TOTAL'][$dato['genero']] = $array['TOTAL'][$dato['genero']] + $dato['cantidad'];
            } else {
                $array['TOTAL'][$dato['genero']] = $dato['cantidad'];
            }
            $array[$dato['cargo']][$dato['genero']] = array('cantidad'=>$dato['cantidad'], 'data'=>$info);
        }  

        $univRegistroConsolidacionEntity = $em->getRepository('SieAppWebBundle:UnivRegistroConsolidacion')->findBy(array('univSede' => $sedeId),array('gestionTipo'=>'DESC'));
        $gestiones = array();
        foreach ($univRegistroConsolidacionEntity as $registro) {
            $gestiones[] = $registro->getGestionTipo();       
        }

        //dump($gestiones);die;
        //$cantidadDocentesAdministrativos = $this->cantidadSedeDocenteAdministrativo($sedeId,$gestionId);

        return $this->render('SieUniversityBundle:SedeDocenteAdministrativo:index.html.twig', array(
            'titulo' => "Reporte",
            'subtitulo' => "Docentes y/o Administrativos",
            'gestiones' => $gestiones,
            'datos' => $array,
            'data' => array('info'=>$info, 'gestion'=>$gestionId) ,
            'editar' => $editar,
            'opeStatus' => $this->get('univfunctions')->getOperativeStatus($baseData)
        ));

    }


    public function tuisionSede($sede, $usuarioId)
    {    
        $em = $this->getDoctrine()->getManager();
        $entityUnivSede = $em->getRepository('SieAppWebBundle:UnivSede')->findOneBy(array('usuario' => $usuarioId, 'id' => $sede));
        if (count($entityUnivSede)>0){
            return true;
        } else {
            return false;
        }
    }    

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que visualiza el formulario para guardar o modificar la asignacion del maestro
    // PARAMETROS: datos
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function formAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $response = new JsonResponse();

        // $info = $request->get('info');
        // if($info != ""){
        //     $info = json_decode(base64_decode($info), true);
        // }

        //dump($request->get('data'));die;
        $info = $request->get('data');
        if($info != ""){
            $info = unserialize(hex2bin($info));
        }

        $sedeId = $info['sedeId'];
        $gestionId = $info['gestionId'];

        $id_usuario = $this->session->get('userId');
        $estado = true; 
        $msg = "";

        if($id_usuario == ""){             
            $estado = false;   
            $msg = "Su sesión finalizo, ingrese nuevamente";      
            return $response->setData(array('estado' => $estado, 'msg' => $msg));
        }

        if(!$this->tuisionSede($sedeId,$id_usuario)){            
            $estado = false;
            $msg = 'No esta como usuario en la sede seleccionada, comuniquese con su administrador';
            return $response->setData(array('estado' => $estado, 'msg' => $msg));
        }


        
        $em = $this->getDoctrine()->getManager();
        $sedeSucursalEntity = $em->getRepository('SieAppWebBundle:UnivSedeSucursal')->findBy(array('univSede'=>$sedeId, 'gestionTipo'=>$gestionId));
        //dump($sedeSucursalEntity, $sedeSucursalEntity[0]->getUnivSede()->getUnivJuridicciongeografica()->getLugarTipoLocalidad2012()->getLugarTipo());die;
        if (count($sedeSucursalEntity) > 0){
            $sedeSucursalArray = $sedeSucursalEntity[0];
            $sedeSucursalId = $sedeSucursalArray->getId();
            $telefono = $sedeSucursalArray->getTelefono1();
            $celular = $sedeSucursalArray->getTelefono2();
            $referenciaCelular = $sedeSucursalArray->getReferenciaTelefono2();
            $correo = $sedeSucursalArray->getEmail();
            $inicioCalendarioAcademico = $sedeSucursalArray->getInicioCalendarioAcademico();
            $fax = $sedeSucursalArray->getFax();
            $casilla = $sedeSucursalArray->getCasilla();
            $sitio = $sedeSucursalArray->getSitioWeb();
            $departamento = $sedeSucursalArray->getUnivSede()->getUnivJuridicciongeografica()->getLugarTipoLocalidad2012()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
            $provincia = $sedeSucursalArray->getUnivSede()->getUnivJuridicciongeografica()->getLugarTipoLocalidad2012()->getLugarTipo()->getLugarTipo()->getId();
            $municipio = $sedeSucursalArray->getUnivSede()->getUnivJuridicciongeografica()->getLugarTipoLocalidad2012()->getLugarTipo()->getId();
            $comunidad = $sedeSucursalArray->getUnivSede()->getUnivJuridicciongeografica()->getLugarTipoLocalidad2012()->getId();
            $latitud = $sedeSucursalArray->getUnivSede()->getUnivJuridicciongeografica()->getCordx();
            $longitud = $sedeSucursalArray->getUnivSede()->getUnivJuridicciongeografica()->getCordy();
            $zona = $sedeSucursalArray->getUnivSede()->getUnivJuridicciongeografica()->getZona();
            $direccion = $sedeSucursalArray->getUnivSede()->getUnivJuridicciongeografica()->getDireccion();
        } else {
            $sedeSucursalId = 0;
            $telefono = "";
            $celular = "";
            $referenciaCelular = "";
            $correo = "";
            $inicioCalendarioAcademico = $fechaActual;
            $fax = "";
            $casilla = "";
            $sitio = "";
            $departamento = -1;
            $provincia = -1;
            $municipio = -1;
            $comunidad = -1;            
            $latitud = "-63.588653";
            $longitud = "-16.290154";
            $zona = "";
            $direccion = ""; 	
        }

        $info = base64_encode(json_encode(array('sedeId'=>$sedeId,'gestionId'=>$gestionId,'sedeSucursalId'=>$sedeSucursalId)));
        $info = bin2hex(serialize(array('sedeId'=>$sedeId,'gestionId'=>$gestionId,'sedeSucursalId'=>$sedeSucursalId)));

        $datos = array(
            'data'=>$info,
            'telefono'=>$telefono,
            'celular'=>$celular,
            'referenciaCelular'=>$referenciaCelular,
            'correo'=>$correo,
            'inicioCalendarioAcademico'=>$inicioCalendarioAcademico->format('d-m-Y'),
            'fax'=>$fax,
            'casilla'=>$casilla,
            'sitio'=>$sitio,
            'departamento'=>array('lugar'=>array(), 'id'=>$departamento),
            'provincia'=>array('lugar'=>array(), 'id'=>$provincia),
            'municipio'=>array('lugar'=>array(), 'id'=>$municipio),
            'comunidad'=>array('lugar'=>array(), 'id'=>$comunidad),
            'latitud'=>$latitud,
            'longitud'=>$longitud,
            'zona'=>$zona,
            'direccion'=>$direccion
        );

        $routing = "";
        $formSedeSucursal = $this->getFormSedeSucursal($routing, $datos);

        //dump($datos);die;
        //dump($sedeId,$info, $formSedeSucursal);die;
        $entityUnivSede = $em->getRepository('SieAppWebBundle:UnivSede')->findOneBy(array('id' => $sedeId));
        $sedeDetalle = array(
            'nombre'=>$entityUnivSede->getSede(),
            'resolucionMinisterial'=>$entityUnivSede->getResolucionMinisterial(),
            'decretoSupremo'=>$entityUnivSede->getResolucionSuprema(),
            'naturalezaJuridica'=>$entityUnivSede->getNaturalezaJuridica(),
            'estado'=>'',
            'latitud'=>$latitud,
            'longitud'=>$longitud
        );

        $arrayFormulario = array('estado'=>$estado, 'msg'=>$msg, 'titulo' => "Modificación de Sede / Sub Sede", 'form'=>$formSedeSucursal, 'sedeDetalle'=>$sedeDetalle);
                
        return $this->render('SieUniversityBundle:Sede:form.html.twig', $arrayFormulario);

    }



    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que visualiza el formulario para guardar o modificar la asignacion del maestro
    // PARAMETROS: datos
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function reporteDocenteAdministrativoAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $response = new JsonResponse();

        // $info = $request->get('info');
        // if($info != ""){
        //     $info = json_decode(base64_decode($info), true);
        // }

        $info = $request->get('data');
        if($info != ""){
            $info = unserialize(hex2bin($info));
        }  

        $sedeId = $info['sedeId'];
        $gestionId = $info['gestionId'];

        $id_usuario = $this->session->get('userId');
        $estado = true; 
        $msg = "";

        if($id_usuario == ""){             
            $estado = false;   
            $msg = "Su sesión finalizo, ingrese nuevamente";      
            return $response->setData(array('estado' => $estado, 'msg' => $msg));
        }

        if(!$this->tuisionSede($sedeId,$id_usuario)){            
            $estado = false;
            $msg = 'No esta como usuario en la sede seleccionada, comuniquese con su administrador';
            return $response->setData(array('estado' => $estado, 'msg' => $msg));
        }
        
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare('
            select coalesce(usda.cantidad,0) as cantidad, est.* from (select * from univ_universidad_sede_docente_adm where univ_sede_id = :sede_id and gestion_tipo_id = :gestion_id) as usda
            RIGHT JOIN (
            select ct.id as cargo_tipo_id, ct.cargo, gt.id as genero_tipo_id, gt.genero 
            from univ_cargo_tipo as ct 
            cross join genero_tipo as gt
            ) as est on est.cargo_tipo_id = usda.univ_cargo_tipo_id and est.genero_tipo_id = usda.genero_tipo_id
            order by est.cargo_tipo_id, est.genero_tipo_id
        ');
        $query->bindValue(':sede_id', $sedeId);
        $query->bindValue(':gestion_id', $gestionId);
        $query->execute();
        $universidadSedeDocenteAdmEntity = $query->fetchAll();

        $array = array();
        foreach ($universidadSedeDocenteAdmEntity as $dato) {
            //$info = base64_encode(json_encode(array('sedeId'=>$sedeId,'gestionId'=>$gestionId,'cargoId'=>$dato['cargo_tipo_id'], 'generoId'=>$dato['genero_tipo_id'])));
            $info = bin2hex(serialize(array('sedeId'=>$sedeId,'gestionId'=>$gestionId,'cargoId'=>$dato['cargo_tipo_id'], 'generoId'=>$dato['genero_tipo_id'])));
            $array[$dato['cargo']][$dato['genero']] = array('cantidad'=>$dato['cantidad'], 'data'=>$info);
        }  

        $arrayFormulario = array('estado'=>$estado, 'msg'=>$msg, 'titulo' => "Modificación de cantidad de Docentres / Administrativos", 'datos'=>$array);
                
        return $this->render('SieUniversityBundle:Sede:formDocAdm.html.twig', $arrayFormulario);

    }


    public function saveAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $response = new JsonResponse();

        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();

        $id_usuario = $this->session->get('userId');
        $estado = true; 
        $msg = "";

        if($id_usuario == ""){             
            $estado = false;   
            $msg = "Su sesión finalizo, ingrese nuevamente";      
            return $response->setData(array('estado' => $estado, 'msg' => $msg));
        }
       
        $em = $this->getDoctrine()->getManager();
        $cantidadDocentesAdministrativos = 0;

        $em->getConnection()->beginTransaction();
        try {     
            $sedeId = 0;
            $gestionId = 0;
            foreach ($form as $clave => $valor) {
                //$info = json_decode(base64_decode($clave), true);
               
                $info = unserialize(hex2bin($clave));
                $cantidad = $valor;
                $sedeId = $info['sedeId'];
                $gestionId = $info['gestionId'];
                $cargoId = $info['cargoId'];
                $generoId = $info['generoId'];

                if(!$this->tuisionSede($sedeId,$id_usuario)){            
                    $estado = false;
                    $msg = 'No esta como usuario en la sede seleccionada, comuniquese con su administrador';
                    $em->getConnection()->rollback();
                    return $response->setData(array('estado' => $estado, 'msg' => $msg));
                }

                $UniversidadDocenteAdministrativoEntity = $em->getRepository('SieAppWebBundle:UnivUniversidadSedeDocenteAdm')->findBy(array('univSede'=>$sedeId, 'gestionTipo'=>$gestionId, 'generoTipo'=>$generoId, 'univCargoTipo'=>$cargoId));
                if(count($UniversidadDocenteAdministrativoEntity) <= 0){             
                    $UniversidadDocenteAdministrativoEntity = new UnivUniversidadSedeDocenteAdm();
                    $UniversidadDocenteAdministrativoEntity->setUnivSede($em->getRepository('SieAppWebBundle:UnivSede')->find($sedeId));
                    $UniversidadDocenteAdministrativoEntity->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestionId));
                    $UniversidadDocenteAdministrativoEntity->setFechaCreacion(new \DateTime('now'));
                } else {
                    $UniversidadDocenteAdministrativoEntity = $UniversidadDocenteAdministrativoEntity[0];
                }
                $UniversidadDocenteAdministrativoEntity->setCantidad($cantidad);
                $UniversidadDocenteAdministrativoEntity->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($generoId));
                $UniversidadDocenteAdministrativoEntity->setUnivCargoTipo($em->getRepository('SieAppWebBundle:UnivCargoTipo')->find($cargoId));
                $UniversidadDocenteAdministrativoEntity->setFechaActualizacion(new \DateTime('now'));
                $em->persist($UniversidadDocenteAdministrativoEntity);
            }
            $em->flush();
            $em->getConnection()->commit();
            $msg  = 'Datos registrados correctamente';
            $cantidadDocentesAdministrativos = $this->cantidadSedeDocenteAdministrativo($sedeId,$gestionId);
           
            return $response->setData(array('estado' => true, 'msg' => $msg, 'cantidad' => $cantidadDocentesAdministrativos));
        } catch (\Doctrine\ORM\NoResultException $ex) {
            $em->getConnection()->rollback();
            $msg  = 'Error al realizar el registro, intente nuevamente';
            return $response->setData(array('estado' => false, 'msg' => $msg, 'cantidad' => $cantidadDocentesAdministrativos));
        }       
    }


    public function cantidadSedeDocenteAdministrativo($sedeId,$gestionId){    
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('
            select sum(coalesce(usda.cantidad,0)) as cantidad from (select * from univ_universidad_sede_docente_adm where univ_sede_id = :sede_id and gestion_tipo_id = :gestion_id) as usda
            RIGHT JOIN (
            select ct.id as cargo_tipo_id, ct.cargo, gt.id as genero_tipo_id, gt.genero 
            from univ_cargo_tipo as ct 
            cross join genero_tipo as gt
            ) as est on est.cargo_tipo_id = usda.univ_cargo_tipo_id and est.genero_tipo_id = usda.genero_tipo_id
        ');
        $query->bindValue(':sede_id', $sedeId);
        $query->bindValue(':gestion_id', $gestionId);
        $query->execute();
        $docentesAdministrativos = $query->fetchAll();

        if (count($docentesAdministrativos) > 0){
            $docentesAdministrativos = $docentesAdministrativos[0]['cantidad'];
        }       
        return $docentesAdministrativos;  
    }
}
