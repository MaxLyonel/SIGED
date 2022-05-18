<?php

namespace Sie\UniversityBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\UnivSedeSucursal;
use Sie\AppWebBundle\Entity\UnivJurisdiccionGeografica;

use Symfony\Component\HttpFoundation\JsonResponse;

class SedeController extends Controller
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
        $editar = false;

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
            
        $em = $this->getDoctrine()->getManager();

        $form = $request->get('form');
        if(isset($form['sede'])){
            $sedeId = base64_decode($form['sede']);
        } else {
            $sedeId = 0;
        }
        if(isset($form['gestion'])){
            $gestionId = base64_decode($form['gestion']);
        } else {
            $gestionId = $gestionActual;
        }        


        $this->session->set('sedeId', $sedeId);

        $entityUnivSedeActual = $em->getRepository('SieAppWebBundle:UnivSede')->findOneBy(array('id' => $sedeId));
        $titulo = $entityUnivSedeActual->getUnivUniversidad()->getUniversidad();
        $subtitulo = $entityUnivSedeActual->getSede();

        $sedeSucursalEntity = $em->getRepository('SieAppWebBundle:UnivSedeSucursal')->findBy(array('univSede'=>$sedeId, 'gestionTipo'=>$gestionId));
        
        $info = base64_encode(json_encode(array('sedeId'=>$sedeId,'gestionId'=>$gestionId)));

        if (count($sedeSucursalEntity) > 0){
            $editar = true;
            $sedeSucursalArray = $sedeSucursalEntity[0];
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

        $datos = array(
            'info'=>$info,
            'telefono'=>$telefono,
            'celular'=>$celular,
            'referenciaCelular'=>$referenciaCelular,
            'correo'=>$correo,
            'inicioCalendarioAcademico'=>$inicioCalendarioAcademico->format('d-m-Y'),
            'fax'=>$fax,
            'casilla'=>$casilla,
            'sitio'=>$sitio,
            'departamento'=>array('lugar'=>$em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $departamento)), 'id'=>$departamento),
            'provincia'=>array('lugar'=>$em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $provincia)), 'id'=>$provincia),
            'municipio'=>array('lugar'=>$em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $municipio)), 'id'=>$municipio),
            'comunidad'=>array('lugar'=>$em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $comunidad)), 'id'=>$comunidad),
            'latitud'=>$latitud,
            'longitud'=>$longitud,
            'zona'=>$zona,
            'direccion'=>$direccion,
            'gestion'=>$gestionId
        );
        
        $entity = $em->getRepository('SieAppWebBundle:GestionTipo');
        $query = $entity->createQueryBuilder('gt')
                ->orderBy('gt.id', 'DESC')
                ->setMaxResults(2)
                ->getQuery();
        $gestiones = $query->getResult();
        
        return $this->render('SieUniversityBundle:Sede:index.html.twig', array(
            'sede' => $entityUnivSedeActual,
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'gestiones' => $gestiones,
            'datos' => $datos,
            'editar' => $editar
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

    public function getFormSedeSucursal($routing, $datos){
        $em = $this->getDoctrine()->getManager();

        $departamentoEntity = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel'=>8,'paisTipoId'=>1), array('id' => 'ASC'));
        $departamentoArray = array();
        foreach ($departamentoEntity as $fila) {
            if($fila->getCodigo() != "0"){
                $departamentoArray[$fila->getId()] = $fila->getLugar();
            }            
        }
        $datos['departamento']['lugar'] = $departamentoArray;

        $provinciaEntity = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel'=>9,'paisTipoId'=>1,'lugarTipo'=>$datos['departamento']['id']), array('lugar' => 'ASC'));
        $provinciaArray = array();
        foreach ($provinciaEntity as $fila) {
            $provinciaArray[$fila->getId()] = $fila->getLugar();
        }
        $datos['provincia']['lugar'] = $provinciaArray;

        $municipioEntity = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel'=>10,'paisTipoId'=>1,'lugarTipo'=>$datos['provincia']['id']), array('lugar' => 'ASC'));
        $municipioArray = array();
        foreach ($municipioEntity as $fila) {
            $municipioArray[$fila->getId()] = $fila->getLugar();
        }
        $datos['municipio']['lugar'] = $municipioArray;

        $localidadEntity = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel'=>11,'paisTipoId'=>1,'lugarTipo'=>$datos['municipio']['id']), array('lugar' => 'ASC'));
        $localidadArray = array();
        foreach ($localidadEntity as $fila) {
            $localidadArray[$fila->getId()] = $fila->getLugar();
        }
        $datos['comunidad']['lugar'] = $localidadArray;
        
        $form = $this->createFormBuilder($datos)
        ->add('info', 'hidden', array('label' => 'Info', 'attr' => array('value' => $datos['info'])))
        ->add('telefono', 'text', array('label' => 'Nro. de Teléfono', 'required' => false, 'attr' => array('value' => $datos['telefono'], 'autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}', 'placeholder' => '########')))
        ->add('celular', 'text', array('label' => 'Nro. de Celular', 'required' => false, 'attr' => array('value' => $datos['celular'], 'autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}', 'placeholder' => '########')))
        ->add('referenciaCelular', 'text', array('label' => 'Referencia de celular', 'required' => false, 'attr' => array('value' => $datos['referenciaCelular'], 'autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}', 'placeholder' => '########')))
        ->add('correo', 'email', array('label' => 'Correo Electrónico', 'required' => false, 'attr' => array('value'=>$datos['correo'],'autocomplete' => 'off', 'class' => 'form-control jemail')))
        ->add('inicioCalendarioAcademico', 'text', array('label' => 'Inicio calendario académico (Ej.: 01-01-2022)', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $datos['inicioCalendarioAcademico'], 'style' => 'text-transform:uppercase', 'placeholder' => 'DD-MM-YYYY' , 'maxlength' => 10, 'required' => true)))
        ->add('fax','text',array('label'=>'Fax','required'=>false,'data'=>$datos['fax'],'disabled'=>false,'attr'=>array('class'=>'form-control jnumbers','autocomplete'=>'off','maxlength'=>'10')))
        ->add('casilla','text',array('label'=>'Casilla','required'=>false,'data'=>$datos['casilla'],'disabled'=>false,'attr'=>array('class'=>'form-control jnumbers','maxlength'=>'8','autocomplete'=>'off')))        
        ->add('sitioWeb', 'url', array('label' => 'Sitio Web', 'invalid_message' => 'campo obligatorio', 'attr' => array('value'=>$datos['sitio'], 'style' => 'text-transform:uppercase', 'placeholder' => 'Sitio Web' , 'maxlength' => 100, 'required' => true)))
        ->add('departamento', 'choice', array('label' => 'Departamento', 'choices' => $datos['departamento']['lugar'], 'data' => $datos['departamento']['id'], 'empty_value' => 'Seleccione Departamento', 'attr' => array('class' => 'form-control', 'onchange' => 'listarProvincia(this.value)', 'required' => true)))
        ->add('provincia', 'choice', array('label' => 'Provincia', 'choices' => $datos['provincia']['lugar'], 'data' => $datos['provincia']['id'], 'empty_value' => 'Seleccione Provincia', 'attr' => array('class' => 'form-control', 'onchange' => 'listarMunicipio(this.value)', 'required' => true)))
        ->add('municipio', 'choice', array('label' => 'Municipio', 'choices' => $datos['municipio']['lugar'], 'data' => $datos['municipio']['id'], 'empty_value' => 'Seleccione Municipio', 'attr' => array('class' => 'form-control', 'onchange' => 'listarComunidad(this.value)', 'required' => true)))
        ->add('comunidad', 'choice', array('label' => 'Comunidad', 'choices' => $datos['comunidad']['lugar'], 'data' => $datos['comunidad']['id'], 'empty_value' => 'Seleccione Comunidad', 'attr' => array('class' => 'form-control', 'required' => true)))
        ->add('latitud', 'text', array('label' => 'Latitud', 'required' => false, 'attr' => array('value' => $datos['latitud'], 'class' => 'form-control datocea', 'style' => 'pointer-events:none')))
        ->add('longitud', 'text', array('label' => 'Longitud', 'required' => false, 'attr' => array('value' => $datos['longitud'], 'class' => 'form-control datocea', 'style' => 'pointer-events:none')))
        ->add('zona', 'text', array('label' => 'Zona:', 'required' => false, 'attr' => array('value' => $datos['zona'], 'class' => 'form-control datocea', 'style' => 'text-transform:uppercase')))
        ->add('direccion', 'text', array('label' => 'Dirección:', 'required' => false, 'attr' => array('value' => $datos['direccion'], 'class' => 'form-control datocea', 'style' => 'text-transform:uppercase')))
        ->getForm()->createView();
        return $form;
    }


    public function listaProvinciaAction(Request $request){    
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $lugarId = $request->get('departamento');
  
        // / get provincias
        $objeto = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 9, 'lugarTipo' => $lugarId));
  
        $array = array();
        foreach ($objeto as $dato) {
            $array[] = array('id'=>$dato->getid(), 'nombre'=>$dato->getlugar());
        }  
        $response->setStatusCode(200);
        $response->setData(array(
            'lista' => $array,
        ));
       
        return $response;  
    }

    public function listaMunicipioAction(Request $request){    
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $lugarId = $request->get('provincia');
  
        // / get provincias
        $objeto = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 10, 'lugarTipo' => $lugarId));
  
        $array = array();
        foreach ($objeto as $dato) {
            $array[] = array('id'=>$dato->getid(), 'nombre'=>$dato->getlugar());
        }    
        $response->setStatusCode(200);
        $response->setData(array(
            'lista' => $array,
        ));
       
        return $response;  
    }

    public function listaComunidadAction(Request $request){    
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $lugarId = $request->get('municipio');
  
        // / get provincias
        $objeto = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 11, 'lugarTipo' => $lugarId));
  
        $array = array();
        foreach ($objeto as $dato) {
            $array[] = array('id'=>$dato->getid(), 'nombre'=>$dato->getlugar());
        }  
        $response->setStatusCode(200);
        $response->setData(array(
            'lista' => $array,
        ));
       
        return $response;  
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

        $info = $request->get('info');
        if($info != ""){
            $info = json_decode(base64_decode($info), true);
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

        $datos = array(
            'info'=>$info,
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
    public function updateAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $response = new JsonResponse();

        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();

        $info = $form['info'];
        if($info != ""){
            $info = json_decode(base64_decode($info), true);
        }

        $sedeId = $info['sedeId'];
        $gestionId = $info['gestionId'];
        $sedeSucursalId = $info['sedeSucursalId'];

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
        $em->getConnection()->beginTransaction();
        try {        
            $sedeSucursalEntity = $em->getRepository('SieAppWebBundle:UnivSedeSucursal')->findBy(array('id'=>$sedeSucursalId, 'univSede'=>$sedeId, 'gestionTipo'=>$gestionId));
            if(count($sedeSucursalEntity) <= 0){             
                $sedeSucursalEntity = new UnivSedeSucursal();
                $sedeSucursalEntity->setUnivSede($em->getRepository('SieAppWebBundle:UnivSede')->find($sedeId));
                $sedeSucursalEntity->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestionId));
                $sedeSucursalEntity->setFechaRegistro(new \DateTime('now'));
            } else {
                $sedeSucursalEntity = $sedeSucursalEntity[0];
            }
            $sedeSucursalEntity->setTelefono1($form['telefono']);
            $sedeSucursalEntity->setTelefono2($form['celular']);
            $sedeSucursalEntity->setReferenciaTelefono2($form['referenciaCelular']);
            $sedeSucursalEntity->setEmail($form['correo']);
            $sedeSucursalEntity->setInicioCalendarioAcademico(new \DateTime($form['inicioCalendarioAcademico']));    
            $sedeSucursalEntity->setFax($form['fax']);
            $sedeSucursalEntity->setCasilla($form['casilla']);
            $sedeSucursalEntity->setSitioWeb($form['sitioWeb']); 
            $sedeSucursalEntity->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->find(10));
            $sedeSucursalEntity->setFechaModificacion(new \DateTime('now'));
            $em->persist($sedeSucursalEntity);

            $sedeEntity = $em->getRepository('SieAppWebBundle:UnivSede')->find($sedeId);
            $univJuridicciongeograficaEntity = $sedeEntity->getUnivJuridicciongeografica();
            if(count($univJuridicciongeograficaEntity) <= 0){             
                $univJuridicciongeograficaEntity = new UnivJurisdiccionGeografica();
                $univJuridicciongeograficaEntity->setFechaRegistro(new \DateTime('now'));
            } 
            $univJuridicciongeograficaEntity->setLugarTipoLocalidad2012($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['comunidad']));
            $univJuridicciongeograficaEntity->setObs("");
            $univJuridicciongeograficaEntity->setCordx($form['longitud']);
            $univJuridicciongeograficaEntity->setCordy($form['latitud']);
            $univJuridicciongeograficaEntity->setDireccion($form['direccion']);
            $univJuridicciongeograficaEntity->setZona($form['zona']);
            $univJuridicciongeograficaEntity->setFechaModificacion(new \DateTime('now'));
            $em->persist($univJuridicciongeograficaEntity);

            if(count($sedeEntity->getUnivJuridicciongeografica()) <= 0){             
                $sedeEntity->setUnivJuridicciongeografica($univJuridicciongeograficaEntity);
                $sedeEntity->setFechaModificacion(new \DateTime('now'));
                $em->persist($sedeEntity);
            } 

            $em->flush();
            $em->getConnection()->commit();
            $msg  = 'Datos registrados correctamente';
            return $response->setData(array('estado' => true, 'msg' => $msg));
        } catch (\Doctrine\ORM\NoResultException $ex) {
            $em->getConnection()->rollback();
            $msg  = 'Error al realizar el registro, intente nuevamente';
            return $response->setData(array('estado' => false, 'msg' => $msg));
        }       

    }

}
