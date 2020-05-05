<?php

namespace Sie\UsuariosBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

use Sie\UsuariosBundle\Form\UsuarioNewType;
use Sie\UsuariosBundle\Form\UsuarioShowType;
use Sie\UsuariosBundle\Form\UsuarioResetType;
use Sie\UsuariosBundle\Form\PersonaCarnetType;
use Sie\UsuariosBundle\Form\UploadFotoPersonaType;

use Sie\AppWebBundle\Entity\Usuario;
use Sie\AppWebBundle\Entity\UsuarioRol;

use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    //EJEMPLO DE CI DUPLICADO 34523
    //EJEMPLO DE CI SIN DUPLICADOS 356597
    private $session;    
    /**
     * the class constructor
     */
    public function __construct() {        
        $this->session = new Session();        
    }
    
    private function generarpassword() {
        //$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $alphabet = "0123456789";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 6; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        
        return implode($pass);
    }
    
    private function generarusername($persona) {        
        return substr($persona->getCarnet());
    }

    public function indexAction(){
        //return $this->redirectToRoute('principal_web');
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        /////APODERADO////
//        if ($this->session->get('roluser') == '3'){
//            return $this->redirectToRoute('sie_usuario_persona_unidades',array('usuarioid' => $id_usuario));
//        }
                
        /////DIRECTOR////
        if ($this->session->get('roluser') == '9'){
            //die('d');
            return $this->redirectToRoute('sie_usuario_persona_unidades',array('usuarioid' => $id_usuario));
        }
        ////MAESTRO DE UNIDAD EDUCATIVA////
        if ($this->session->get('roluser') == '2'){
                $usuariodatos = $this->getDoctrine()->getRepository('SieAppWebBundle:Usuario')->getFindByUserPersolRol($this->session->get('userId'));
                $usuariodatosrol = $this->getDoctrine()->getRepository('SieAppWebBundle:Usuario')->getFindByUserPersolAndRol($this->session->get('userId'),$this->session->get('roluser'));
                $p = $this->getDoctrine()->getRepository('SieAppWebBundle:Persona')->find($usuariodatos[0]['personaid']);
                $datos_filas[0]["id"] = $p->getId();
                $datos_filas[0]["carnet"] = $p->getCarnet();
                $datos_filas[0]["nombre"] = $p->getNombre();      
                $datos_filas[0]["paterno"] = $p->getPaterno();
                $datos_filas[0]["materno"] = $p->getMaterno();
                $datos_filas[0]["fecha_nacimiento"] = $p->getFechaNacimiento();
                $datos_filas[0]["complemento"] = $p->getComplemento();
                $datos_filas[0]["genero"] = $p->getGeneroTipo()->getGenero();
                $datos_filas[0]["libreta_militar"] = $p->getLibretaMilitar();
                $datos_filas[0]["pasaporte"] = $p->getPasaporte();
                $datos_filas[0]["estado_civil"] = $p->getEstadoCivilTipo()->getEstadocivil();
                $datos_filas[0]["idioma"] = $p->getIdiomaMaterno()->getIdiomaMaterno();
                $datos_filas[0]["count_edit"] = $p->getCountEdit();
                $datos_filas[0]["foto"] = $p->getFoto(); 
                
                $depentity = $this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find($usuariodatosrol[0]['lugar_tipoid']); 
                $dep2 = $this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find($depentity->getlugarTipo()->getId());                
                $this->session->set('dep_id',$dep2->getId());
                $this->session->set('dep_nombre',$dep2->getLugar());                    
                $this->session->set('dis_id',$usuariodatosrol[0]['lugar_tipoid']);                
                $this->session->set('dis_cod',$usuariodatosrol[0]['discod']);
                $this->session->set('dis_nombre',$usuariodatosrol[0]['lugar']);
                
                $form = $this->createForm(new UploadFotoPersonaType(), null, array('action' => $this->generateUrl('sie_persona_uploadfoto'), 'method' => 'POST',));                
//                print_r($usuariodatos[0]['personaid']);
                $form->get('personaid')->setData($usuariodatos[0]['personaid']);
                return $this->render('SieUsuariosBundle:Default:usuariomostrar.html.twig', array('personas' => $datos_filas,'usuariodatos' => $usuariodatos,'form' => $form->createView())); 
            
        }

        //NACIONAL - DEPARTAMENTO - DISTRITO 
//        print_r($this->session->get('userId'));
//        die;
        if ( ($this->session->get('roluser') == '3') || ($this->session->get('roluser') == '31') ||
             ($this->session->get('roluser') == '8') || ($this->session->get('roluser') == '7') || 
             ($this->session->get('roluser') == '10') || ($this->session->get('roluser') == '30') || 
             ($this->session->get('roluser') == '21') || ($this->session->get('roluser') == '41') || ($this->session->get('roluser') == '29') ) {                      
            $usuariopersona = $this->getDoctrine()->getRepository('SieAppWebBundle:Usuario')->getFindByUserPerson($this->session->get('userId'));
//            dump($usuariodatos);
//            die;
            if ((count($usuariopersona) == 0) || (count($usuariopersona) > 1)){//USUARIO SIN PERSONA O CON MAS DE UNA PERSONA
                $datos_filas[0]["id"] = "";
                $datos_filas[0]["carnet"] = "";
                $datos_filas[0]["nombre"] = "";      
                $datos_filas[0]["paterno"] = "";
                $datos_filas[0]["materno"] = "";
                $datos_filas[0]["fecha_nacimiento"] = "";
                $datos_filas[0]["complemento"] = "";
                $datos_filas[0]["genero"] = "";
                $datos_filas[0]["libreta_militar"] = "";
                $datos_filas[0]["pasaporte"] = "";
                $datos_filas[0]["estado_civil"] = "";
                $datos_filas[0]["idioma"] = "";
                $datos_filas[0]["count_edit"] = "4";
                $datos_filas[0]["foto"] = "";
                $form = $this->createForm(new UploadFotoPersonaType(), null, array('action' => $this->generateUrl('sie_persona_uploadfoto'), 'method' => 'POST',));                
                $this->session->getFlashBag()->add('message', 'La cuenta de usuario tiene '.count($usuariodatos).' persona/s(carnet/s) asignadas.');
                return $this->render('SieUsuariosBundle:Default:usuariomostrar.html.twig', array('personas' => $datos_filas,'usuariodatos' => $usuariodatos,'form' => $form->createView())); 
            }
            if (count($usuariopersona) == 1){//CORRECTO  
                $usuariodatos = $this->getDoctrine()->getRepository('SieAppWebBundle:Usuario')->getFindByUserPersolRol($this->session->get('userId'));
                $usuariodatosrol = $this->getDoctrine()->getRepository('SieAppWebBundle:Usuario')->getFindByUserPersolAndRol($this->session->get('userId'),$this->session->get('roluser'));
                //dump($usuariodatos);die;
                $p = $this->getDoctrine()->getRepository('SieAppWebBundle:Persona')->find($usuariodatos[0]['personaid']);
                $datos_filas[0]["id"] = $p->getId();
                $datos_filas[0]["carnet"] = $p->getCarnet();
                $datos_filas[0]["nombre"] = $p->getNombre();      
                $datos_filas[0]["paterno"] = $p->getPaterno();
                $datos_filas[0]["materno"] = $p->getMaterno();
                $datos_filas[0]["fecha_nacimiento"] = $p->getFechaNacimiento();
                $datos_filas[0]["complemento"] = $p->getComplemento();
                $datos_filas[0]["genero"] = $p->getGeneroTipo()->getGenero();
                $datos_filas[0]["libreta_militar"] = $p->getLibretaMilitar();
                $datos_filas[0]["pasaporte"] = $p->getPasaporte();
                $datos_filas[0]["estado_civil"] = $p->getEstadoCivilTipo()->getEstadocivil();
                $datos_filas[0]["idioma"] = $p->getIdiomaMaterno()->getIdioma();
                $datos_filas[0]["count_edit"] = $p->getCountEdit();
                $datos_filas[0]["foto"] = $p->getFoto();                
                //NACIONAL
                if (($this->session->get('roluser') == '8') || ($this->session->get('roluser') == '30') 
                    || ($this->session->get('roluser') == '41') || ($this->session->get('roluser') == '31')) { 
                    $this->session->set('dep_id','0');
                    $this->session->set('dep_nombre','');
                    $this->session->set('dis_id','0');                
                    $this->session->set('dis_cod','');
                    $this->session->set('dis_nombre','');
                    $this->session->set('ie_activo','0');
                    $this->session->set('ie_id','');
                    $this->session->set('ie_nombre','');
                }
                //DEPARTAMENTAL
                if ($this->session->get('roluser') == '7'){
                    $dep2 = $this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find($usuariodatosrol[0]['lugar_tipoid']);                
                    $this->session->set('dep_id',$dep2->getId());
                    $this->session->set('dep_nombre',$dep2->getLugar());                    
                    $this->session->set('dis_id','');                
                    $this->session->set('dis_cod','');
                    $this->session->set('dis_nombre','');
                    $this->session->set('ie_activo','0');
                    $this->session->set('ie_id','');
                    $this->session->set('ie_nombre','');
                }
                //DISTRITAL
                if ($this->session->get('roluser') == '10'){ 
                    $depentity = $this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find($usuariodatosrol[0]['lugar_tipoid']); 
                    $dep2 = $this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find($depentity->getlugarTipo()->getId());                
                    $this->session->set('dep_id',$dep2->getId());
                    $this->session->set('dep_nombre',$dep2->getLugar());                    
                    $this->session->set('dis_id',$usuariodatosrol[0]['lugar_tipoid']);                
                    $this->session->set('dis_cod',$usuariodatosrol[0]['discod']);
                    $this->session->set('dis_nombre',$usuariodatosrol[0]['lugar']);
                    $this->session->set('ie_activo','0');
                    $this->session->set('ie_id','');
                    $this->session->set('ie_nombre','');
                }
                //APODERADO
                if ($this->session->get('roluser') == '3'){ 
                    $this->session->set('dep_id','0');
                    $this->session->set('dep_nombre','');
                    $this->session->set('dis_id','0');                
                    $this->session->set('dis_cod','');
                    $this->session->set('dis_nombre','');
                    $this->session->set('ie_activo','0');
                    $this->session->set('ie_id','');
                    $this->session->set('ie_nombre','');
                }
                //PNP INFORMATICO DEPARTAMENTAL
                if ($this->session->get('roluser') == '21'){
                    $dep2 = $this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find($usuariodatosrol[0]['lugar_tipoid']);                
                    $this->session->set('dep_id',$dep2->getId());
                    $this->session->set('dep_nombre',$dep2->getLugar());                    
                    $this->session->set('dis_id','');                
                    $this->session->set('dis_cod','');
                    $this->session->set('dis_nombre','');
                    $this->session->set('ie_activo','0');
                    $this->session->set('ie_id','');
                    $this->session->set('ie_nombre','');
                }
                 //PNP PEDAGOGO
                if ($this->session->get('roluser') == '29'){
                    $dep2 = $this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find($usuariodatosrol[0]['lugar_tipoid']);                
                    $this->session->set('dep_id',$dep2->getId());
                    $this->session->set('dep_nombre',$dep2->getLugar());                    
                    $this->session->set('dis_id','');                
                    $this->session->set('dis_cod','');
                    $this->session->set('dis_nombre','');
                    $this->session->set('ie_activo','0');
                    $this->session->set('ie_id','');
                    $this->session->set('ie_nombre','');
                }
                $form = $this->createForm(new UploadFotoPersonaType(), null, array('action' => $this->generateUrl('sie_persona_uploadfoto'), 'method' => 'POST',));                
//                print_r($usuariodatos[0]['personaid']);
                $form->get('personaid')->setData($usuariodatos[0]['personaid']);
                return $this->render('SieUsuariosBundle:Default:usuariomostrar.html.twig', array('personas' => $datos_filas,'usuariodatos' => $usuariodatos,'form' => $form->createView())); 
            }            
        }
    }

    //PRINCIPAL MOSTRAR DATOS USUARIO PERSONA
    //PRINCIPAL MOSTRAR DATOS USUARIO PERSONA
    //PRINCIPAL MOSTRAR DATOS USUARIO PERSONA
    //PRINCIPAL MOSTRAR DATOS USUARIO PERSONA 
    public function mostrarUsuarioPersonaAction($ie_id, $ie_nombre) {
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection(); 
        
        $query = "
            SELECT            
              cargo_tipo.cargo, c.idioma, b.estado_civil, a.genero, persona.*
            FROM 
              institucioneducativa INNER JOIN maestro_inscripcion ON institucioneducativa.id = maestro_inscripcion.institucioneducativa_id  
              INNER JOIN persona ON persona.id = maestro_inscripcion.persona_id  
              INNER JOIN public.cargo_tipo ON maestro_inscripcion.cargo_tipo_id = cargo_tipo.id 
              INNER JOIN genero_tipo a ON a.id = persona.genero_tipo_id
              INNER JOIN estado_civil_tipo b on b.id = persona.estadocivil_tipo_id
              INNER JOIN idioma_tipo c ON c.id = persona.idioma_materno_id
            WHERE
              (institucioneducativa.id = '".$ie_id."') and 
              (maestro_inscripcion.gestion_tipo_id = '2018') and
              (maestro_inscripcion.es_vigente_administrativo = '1') and
              ((cargo_tipo.id = '1') or (cargo_tipo.id = '12'))

            GROUP BY
              c.idioma, 
              b.estado_civil,
              a.genero,
              cargo_tipo.cargo,     
              persona.id, 
              maestro_inscripcion.institucioneducativa_id,
              institucioneducativa.institucioneducativa
            ORDER BY persona.nombre, persona.paterno, persona.materno
            ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);        
        $po = $stmt->fetchAll();
        $countdir = count($po);
        //dump($po);        die;

        if (($this->session->get('roluser') == '8')||($this->session->get('roluser') == '7')){//NACIONAL - DEPARTAMENTAL
            //ESTABLECER PARAMETROS DE LA UNIDAD EDUCATIVA SELECCIONADA PARA TRABAJAR DESDE EL DISTRITAL
            $this->session->set('ie_activo','1');
            $this->session->set('ie_id',$ie_id);
            $this->session->set('ie_nombre',$ie_nombre);
            
            //ESTABLECER PARAMETROS DE LA UNIDAD EDUCATIVA SELECCIONADA PARA TRABAJAR                
            $query = "SELECT get_ie_distrito_id(".$ie_id.");";
            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $podis = $stmt->fetchAll();
            foreach ($podis as $p){
                $lugarestipoid = $p["get_ie_distrito_id"];           
            }
            $lugarids = explode(",", $lugarestipoid);
            $dep_id = substr($lugarids[1],0,strlen($lugarids[1])-1);
            $dis_id = substr($lugarids[0],1,strlen($lugarids[0]));
            $this->session->set('dep_id',$dep_id);
            $this->session->set('dep_nombre',$this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find($dep_id) );
            $this->session->set('dis_id',$dis_id);
            $this->session->set('dis_nombre',$this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find($dis_id) );
                        
            if ($countdir == 1){//CORRECTO 
                $this->session->getFlashBag()->add('success', 'La unidad educativa no presenta observaciones de Director - Usuario.');                
                return $this->redirectToRoute('sie_usuarios_personas_ie');      
            } 
            
//            dump($countdir);
//            die;

            if (($countdir == 0) || ($countdir > 1)) {//ERROR             
                $this->session->getFlashBag()->add('error', 'Existe '.$countdir.' registros con el cargo de cargo de: "DIRECTOR/A" O "DIRECTOR ENCARGADO" en la unidad educativa!');
                return $this->redirectToRoute('sie_usuarios_personas_ie');                
            }
        }
        
        if ($this->session->get('roluser') == '10'){//DISTRITO
            //ESTABLECER PARAMETROS DE LA UNIDAD EDUCATIVA SELECCIONADA PARA TRABAJAR DESDE EL DISTRITAL               
            $query = "SELECT get_ie_distrito_id(".$ie_id.");";
            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $podis = $stmt->fetchAll();
            foreach ($podis as $p){
                $lugarestipoid = $p["get_ie_distrito_id"];           
            }
            $lugarids = explode(",", $lugarestipoid);
            $dep_id = substr($lugarids[1],0,strlen($lugarids[1])-1);
            $dis_id = substr($lugarids[0],1,strlen($lugarids[0]));
            $this->session->set('dep_id',$dep_id);
            $this->session->set('dep_nombre',$this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find($dep_id) );
            $this->session->set('dis_id',$dis_id);
            $this->session->set('dis_nombre',$this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find($dis_id) );
            
            $this->session->set('ie_activo','1');
            $this->session->set('ie_id',$ie_id);
            $this->session->set('ie_nombre',$ie_nombre);
                        
            if ($countdir == 1){//CORRECTO
                $this->session->getFlashBag()->add('success', 'La unidad educativa no presenta observaciones de Director - Usuario.');                
                return $this->redirectToRoute('sie_usuarios_personas_ie');      
            } 

            if (($countdir == 0) || ($countdir > 1)) {//ERROR              
                 $this->session->getFlashBag()->add('error', 'Existe '.$countdir.' registros con el cargo de cargo de director en la unidad educativa!');
                return $this->redirectToRoute('sie_usuarios_personas_ie');                
            }
        }
        
        if ($this->session->get('roluser') == '9') {//DIRECTOR            
            //ESTABLECER PARAMETROS DE LA UNIDAD EDUCATIVA SELECCIONADA PARA TRABAJAR
            $query = "SELECT get_ie_distrito_id(".$ie_id.");";
            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $podis = $stmt->fetchAll();
            foreach ($podis as $p){
                $lugarestipoid = $p["get_ie_distrito_id"];           
            }
            $lugarids = explode(",", $lugarestipoid);
            $dep_id = substr($lugarids[1],0,strlen($lugarids[1])-1);
            $dis_id = substr($lugarids[0],1,strlen($lugarids[0]));
            $this->session->set('dep_id',$dep_id);
            $this->session->set('dep_nombre',$this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find($dep_id) );
            $this->session->set('dis_id',$dis_id);
            $this->session->set('dis_nombre',$this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find($dis_id) );
            
            $this->session->set('ie_activo','1');
            $this->session->set('ie_id',$ie_id);
            $this->session->set('ie_nombre',$ie_nombre);
            //dump($countdir);die();
            //ESTABLECER PARAMETROS DE LA UNIDAD EDUCATIVA SELECCIONADA PARA TRABAJAR               
            if ($countdir == 1){//CORRECTO                 
                $form = $this->createForm(new UploadFotoPersonaType(), null, array('action' => $this->generateUrl('sie_persona_uploadfoto'), 'method' => 'POST',));                
                $form->get('personaid')->setData($po[0]['id']);
                $usuariodatos = $this->getDoctrine()->getRepository('SieAppWebBundle:Usuario')->getFindByUserPersolRol($this->session->get('userId')); 
                return $this->render('SieUsuariosBundle:Default:usuariomostrar.html.twig', array('personas' => $po,'usuariodatos' => $usuariodatos,'form' => $form->createView()));       
            } 

            if (($countdir == 0) || ($countdir > 1)) {//ERROR   
                $po = array();
                $accion = 'stopall';
                $this->session->getFlashBag()->add('error', 'Existe '.$countdir.' registros con el cargo de cargo de director en la unidad educativa! comuniquese con su tec. distrital en el módulo Gestión Administrativos');
                //$this->session->getFlashBag()->add($resultService->type_msg, $resultService->msg);
                return $this->render('SieUsuariosBundle:Busquedas:usuario.html.twig', array(
                    'po'   => $po,                   
                    'accion' => $accion,
                ));
                
                //die('h');            
                /*$this->session->set('ie_activo','0');
                 $this->session->getFlashBag()->add('error', 'Existe '.$countdir.' registros con el cargo de cargo de director en la unidad educativa! comuniquese con su tec. distrital en el módulo Gestión Administrativos');
                return $this->render('SieUsuariosBundle:Listas:iedirectores.html.twig', array('personas' => array()));*/
            }
        }              
    }
    
    //ONCHANGE COMBO ROL USUARIO FORM NEW
    //ONCHANGE COMBO ROL USUARIO FORM NEW
    //ONCHANGE COMBO ROL USUARIO FORM NEW
    public function rollugartipoAction($ids, $depid, $disid) {
        $em = $this->getDoctrine()->getManager();
        
        //SELECCIONA IDs DE LOS TIPOS DE ROLES PARA EL NIVEL DE USUARIO
        $query = $em->createQuery('SELECT a.id, a.rol, b.id as lugniveltip, b.nivel as nivel  FROM SieAppWebBundle:RolTipo a join a.lugarNivelTipo b WHERE a.id IN ('.$ids.') ORDER BY a.id');
        $roltiplug = $query->getResult();         
        $distrito = '';
        $departamento = '';
        if ($depid !== '')
            { 
            $querydis = $em->createQuery("SELECT a FROM SieAppWebBundle:LugarTipo a WHERE a.id = ".$disid);
            $distrito = $querydis->getResult();        
            
            $querydep = $em->createQuery("SELECT a FROM SieAppWebBundle:LugarTipo a WHERE a.id = ".$depid);
            $departamento = $querydep->getResult();
            }
        //dump($roltiplug);die;      
        return $this->render('SieUsuariosBundle:Usuario:OnChangerolLugarTipo.html.twig', array(
                'roltiplug'   => $roltiplug,
                'distrito'   => $distrito,
                'departamento'   => $departamento,
                'depid'   => $depid,
        ));
    }    
    //ONCHANGE COMBO ROL USUARIO FORM EDIT MODAL
    //ONCHANGE COMBO ROL USUARIO FORM EDIT MODAL
    //ONCHANGE COMBO ROL USUARIO FORM EDIT MODAL

    public function usuariorolesAction($usuarioid) {        
        //SELECIONA DATOS DE LOS OBJETOS ROL DEL USUARIO OBJETIVO
        $usuariodatos = $this->getDoctrine()->getRepository('SieAppWebBundle:Usuario')->getFindByUserPersolRol($usuarioid);         
        //LEYENDO ROLES QUE PUEDE CREAR EL USUARIO        
        $idsrol = $this->getDoctrine()->getRepository('SieAppWebBundle:RolRolesAsignacion')->getFindRolesByUsername($this->session->get('roluser'),'');        
        //$separado_por_comas = implode(",", $idsrol);
        $roluserstr = '';
        foreach ($idsrol as $rol){
            //$roluserstr[] = $rol['id'];
            $roluserstr = $roluserstr.','.$rol['id'];
        }
        //$subsistemas = $this->getDoctrine()->getRepository('SieAppWebBundle:SistemaTipo')->findAll();
        //dump($usuariodatos);die;
        
        //dump($subsistemas);die();
        
        return $this->render('SieUsuariosBundle:Usuario:OnChangerolLugarTipoEdit.html.twig', array(
                'roltiplug'   => $usuariodatos,
                'roluserstr'   => $roluserstr,
                //'subsistemas'   => $subsistemas,
        ));
    }
    
    public function usershowAction($usuarioid) {
        $em = $this->getDoctrine()->getManager();        
        
        
        //LEE TODOS LOS ROLES PARA MOSTRAR CUALQUIERA
        $idsrol = '';
        $rolesasignar = $em->getRepository('SieAppWebBundle:RolRolesAsignacion')->findAll();
        foreach ($rolesasignar as $roles){
            $idsrol[] = $roles->getRoles()->getId();
        }        
        
        $form = $this->createForm(new UsuarioShowType(array('rolids' => $idsrol)), null, array('method' => 'POST',));        
             
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuarioid);
        $rolesusuario = $em->getRepository('SieAppWebBundle:UsuarioRol')->findByUsuario($usuario);
        $persona = $em->getRepository('SieAppWebBundle:Persona')->find($usuario->getPersona());
        
        $rolesids = '';
        foreach ($rolesusuario as $rol){
            $rolesids = $rolesids.$rol->getRolTipo()->getId().',';
        }
        $rolesids = substr($rolesids, 0, strlen($rolesids)-1);
        
        $form->get('usuario')->setData($usuario->getUsername());
        $form->get('password')->setData('*********************');        
        
        return $this->render('SieUsuariosBundle:Usuario:usuarioShowFormOld.html.twig', array(
            'rolesids'   => $rolesids,
            'persona'   => $persona,
            'form'   => $form->createView(),
        ));
    }
    
//    public function usershowcarnetAction($carnet) {
//        $form = $this->createForm(new PersonaCarnetType());
//        
//        if ($form->isValid()) {
//            
//            $em = $this->getDoctrine()->getManager();
//            $em = $this->getDoctrine()->getEntityManager();
//            $db = $em->getConnection();            
//            $query = "
//                SELECT               
//                  persona.id as personaid,
//                  persona.carnet, 
//                  persona.nombre, 
//                  persona.paterno, 
//                  persona.materno,              
//                  persona.fecha_nacimiento,
//                  usuario.username,
//                  usuario.id as usuarioid,
//                  usuario.esactivo
//                FROM 
//                  persona LEFT JOIN public.usuario
//                        ON usuario.persona_id = persona.id  
//                WHERE    
//                  persona.carnet = '".$carnet."'
//                GROUP BY
//                  usuario.id, 
//                  persona.id,
//                  persona.carnet, 
//                  persona.nombre, 
//                  persona.paterno, 
//                  persona.materno,              
//                  usuario.username
//                ORDER BY persona.nombre, persona.paterno, persona.materno
//                ";
//            $stmt = $db->prepare($query);
//            $params = array();
//            $stmt->execute($params);
//            $po = $stmt->fetchAll();
//
//            $filas = array();
//            $datos_filas = array();
//
//            foreach ($po as $p){
//                $datos_filas["personaid"] = $p["personaid"];
//                $datos_filas["carnet"] = $p["carnet"];                
//                $datos_filas["nombre"] = $p["nombre"];
//                $datos_filas["paterno"] = $p["paterno"];  
//                $datos_filas["materno"] = $p["materno"];
//                $datos_filas["fecha_nacimiento"] = $p["fecha_nacimiento"];
//                $datos_filas["username"] = $p["username"];  
//                $datos_filas["usuarioid"] = $p["usuarioid"];  
//                $datos_filas["esactivo"] = $p["esactivo"];  
//                $filas[] = $datos_filas;
//            }
//            
//            if (count($filas) > 0){
//                if (count($filas) > 1){//VARIAS PERSONAS CON EL MISMO CI ***CON O SIN*** USUARIO             
//                    return $this->render('SieUsuariosBundle:Listas:listaduplicadoscarnet.html.twig', array(
//                        'personas'   => $filas,
//                    ));
//                }
//                else{//UNA SOLA PERSONA CON UN CI ***CON*** USUARIO
//                    if ($filas[0]['username'] != '')
//                        {                        
//                        return $this->render('SieUsuariosBundle:Listas:datospersona.html.twig', array(           
//                            'personas'   => $filas,
//                        ));
//                        }
//                    else{//UNA SOLA PERSONA PARA UN CI ***SIN*** USUARIO
//                        //$url = 'sie_usuarios_personas_ie/ie/'.$ie;
//                        //$this->session->set('routing', $url);
//                        return $this->render('SieUsuariosBundle:Listas:personausernew.html.twig', array(           
//                            'personas'   => $filas,
//                        ));
//                    }
//                }
//            }
//            else{//LLEVAR AL MODULO DE CREAR PERSONA
//                $this->session->getFlashBag()->add('message', 'No se encuentra a datos registrado para el CI :'.$carnet.'. Complete los siguientes datos.');
//                return $this->redirectToRoute('sie_usuario_persona_new',array('ci' => $carnet));
//            }
//        }
//        else{
//            $this->session->getFlashBag()->add('error', 'Proceso detenido. Se ha detectado inconsistencia de datos.');
//            return $this->redirectToRoute('sie_usuarios_homepage');
//        }
//    }
    
    public function rollugardisidAction($id, $d) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT a FROM SieAppWebBundle:LugarTipo a join a.lugarTipo b WHERE b.id = ".$id." ORDER BY a.lugar");
        $dislugid = $query->getResult();

        return $this->render('SieUsuariosBundle:Usuario:disLugTipo.html.twig', array(
            'dislugid'   => $dislugid,
            'd'   => $d,
        ));
    }
    
    public function usuarionuevoAction($personaid) {        
        $em = $this->getDoctrine()->getManager();        
        $persona = $em->getRepository('SieAppWebBundle:Persona')->find($personaid);
        
        //LEYENDO ROLES QUE PUEDE CREAR EL USUARIO        
        $idsrol = $em->getRepository('SieAppWebBundle:RolRolesAsignacion')->getFindRolesByUsername($this->session->get('roluser'),$this->session->get('rol_ids'));
                
        $form = $this->createForm(new UsuarioNewType(array('rolids' => $idsrol)), null, array(
        //'action' => $this->generateUrl('sie_usuarios_insert'),
        'method' => 'POST',));        
        $form->get('accion')->setData('userinsert');
        $form->get('usuario')->setData(trim($persona->getCarnet().$persona->getComplemento()));
        //$form->get('password')->setData($this->generarpassword()); 
        $form->get('idpersona')->setData($personaid); 
//        dump($this->session->get('dis_id'));
//        die;
        $form->get('depid')->setData($this->session->get('dep_id'));
        $form->get('disid')->setData($this->session->get('dis_id'));
                
        $form->get('lugtipids')->setData('');
                        
        return $this->render('SieUsuariosBundle:Usuario:usuarioForm.html.twig', array(
            'rolesids'  => '',
            'persona'   => $persona,
            'form'      => $form->createView(),
            'accion'    => 'new',
        )); 
    }
    
    public function userinsertAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $data = $request->get('sie_usuarios_form');
        $response = new JsonResponse();
//        print_r($data['maestroinsid']);
//        die('g');
        try {
            if ($data['accion'] === 'userinsert'){    
                $persona = $this->getDoctrine()->getRepository('SieAppWebBundle:Persona')->find($data['idpersona']);
                $usuario = new Usuario();
                $usuario->setPersona($persona);
                $usuario->setUsername(trim($persona->getCarnet().$persona->getComplemento()));
                $usuario->setEsactivo('true');
                $usuario->setPassword(md5(trim($persona->getCarnet().$persona->getComplemento())));
                $usuario->setFechaRegistro(new \DateTime('now'));
                $usuario->setPassword2('nuevo_en_espera');
                $em->persist($usuario);
                $em->flush();
                
                $persona->setActivo('1');
                $persona->setEsVigente('1');
                $persona->setEsvigenteApoderado('1');
                $em->persist($persona);
                $em->flush();
            }
            if ($data['accion'] === 'new'){
                $persona = $this->getDoctrine()->getRepository('SieAppWebBundle:Persona')->find($data['idpersona']);
                $usuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('persona'=>$data['idpersona']));
            }
//            if ($data['accion'] === 'update'){    
//                $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($data['idusuario']);
//                //ELIMINA ROLES ANTERIORES
//                $rolesusuario = $em->getRepository('SieAppWebBundle:UsuarioRol')->findByUsuario($usuario);
//                
//                foreach ($rolesusuario as $value) {                    
//                    $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->find($value->getId());
//                    $em->remove($usuariorol);
//                    $em->flush();
//                }
//            }
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('usuario_rol');");
            $query->execute();

            $form_x = $request->get('sie_usuarios_form');
            $multiple = $form_x['rolTipo'];
                        
            $lugids = explode(",", $data['lugtipids']);
            $i = 0;
            
            foreach ($multiple as $value) {
                $rolTipo = $this->getDoctrine()->getRepository('SieAppWebBundle:RolTipo')->find($value);
                //dump($rolTipo);die;
                $usuariorol = new UsuarioRol();
                $usuariorol->setRolTipo($rolTipo);
                $usuariorol->setUsuario($usuario);
                $usuariorol->setEsactivo('true');
                $usuariorol->setLugarTipo($this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find($lugids[$i]));                
                //$usuariorol->setCircunscripcionTipo();
                if (($rolTipo->getId() == '7') || ($rolTipo->getId() == '8') || ($rolTipo->getId() == '20')){
                    $usuariorol->setSubSistema('');
                }else{
                    $usuariorol->setSubSistema($rolTipo->getSubSistema());
                }

                $em->persist($usuariorol);
                $em->flush();
                $i = $i + 1;
            }
            
            if ($data['accion'] === 'userinsert'){
                
                $this->get('funciones')->setLogTransaccion(
                    $usuario->getId(),
                    'usuario',
                    'C',
                    'Nuevo Usuario',
                    $usuario,
                    $usuario,
                    'DefaultController/userinsert',
                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                );
                
                $em->getConnection()->commit();
                return $response->setData(array('accion' => $data['accion'], 'mensaje' => 'Proceso realizado exitosamente.','username' => $persona->getCarnet(),'usuarioid' =>$usuario->getId()));
                }
            if ($data['accion'] === 'new'){
//                if (($maestroOn == '1') && ($this->session->get('ie_activo') == '1')){//SI ES DIRECTOR CAMBIA SU CARGO A ES DIRECTOR
//                    $maestroinscr = $this->getDoctrine()->getRepository('SieAppWebBundle:MaestroInscripcion')->find($data['maestroinsid']);//es_vigente_administrativo
//                    $maestroinscr->setEsvigenteAdministrativo('1');
//                    $maestroinscr->setCargoTipo($this->getDoctrine()->getRepository('SieAppWebBundle:CargoTipo')->find('1'));//DIRECTOR
//                    $em->persist($usuariorol);
//                    $em->flush();
//                }
                $em->getConnection()->commit();
                return $response->setData(array('accion' => $data['accion'], 'mensaje' => 'Proceso realizado exitosamente.'));
                }
            if ($data['accion'] === 'update'){
                $em->getConnection()->commit();
                return $response->setData(array('accion' => $data['accion'], 'mensaje' => 'Proceso realizado exitosamente.'));
                }
            } 
        catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!'.$ex));
            }
    }
    
    public function userroleditAction($usuarioid) {
        $em = $this->getDoctrine()->getManager();        
        
        //LEYENDO ROLES QUE PUEDE CREAR EL USUARIO
        $idsrol = '';
        $idsrol = $em->getRepository('SieAppWebBundle:RolRolesAsignacion')->getFindByNotUserRolesId($this->session->get('roluser'), $usuarioid);        
        $form = $this->createForm(new UsuarioNewType(array('rolids' => $idsrol)), null, array(
        //'action' => $this->generateUrl('sie_usuarios_rol_update'),
        'method' => 'POST',));        
             
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuarioid);
        $rolesusuario = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario' => $usuario));        
        $persona = $em->getRepository('SieAppWebBundle:Persona')->find($usuario->getPersona());
        
        $rolesids = '';
        $lugarrolids = '';
        foreach ($rolesusuario as $rol){            
            $rolesids = $rolesids.$rol->getRolTipo()->getId().',';
            if($rol->getLugarTipo()){
                $lugarrolids = $lugarrolids.$rol->getLugarTipo()->getId().',';
            }
            else{                
                $lugarrolids = $lugarrolids.'1,';
            }    
            
        }
        $rolesids = substr($rolesids, 0, strlen($rolesids)-1);
        $lugarrolids = substr($lugarrolids, 0, strlen($lugarrolids)-1);
        
        $form->get('accion')->setData('update');
        $form->get('usuario')->setData($usuario->getUsername());
        //$form->get('password')->setData('*********************');
        $form->get('idpersona')->setData($persona->getId());
        $form->get('idusuario')->setData($usuario->getId());
        $form->get('depid')->setData($this->session->get('dep_id'));
        $form->get('disid')->setData($this->session->get('dis_id'));
        $form->get('lugtipids')->setData($lugarrolids);
                
        return $this->render('SieUsuariosBundle:Usuario:usuarioForm.html.twig', array(
            'rolesids'   => $rolesids,
            'persona'   => $persona,
            'idsrol'   => $idsrol,            
            'form'   => $form->createView(),
        ));
    }
    
    public function userresetAction($usuarioid) {
        $em = $this->getDoctrine()->getManager();
     
        $form = $this->createForm(new UsuarioResetType(), null, array(
            //'action' => $this->generateUrl('sie_usuarios_reset_update'),
            'method' => 'POST',));
        
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuarioid);
        $persona = $em->getRepository('SieAppWebBundle:Persona')->find($usuario->getPersona());
        
        $form->get('usuario')->setData($usuario->getUsername());
        //$form->get('password')->setData($this->generarpassword());
        $form->get('idpersona')->setData($persona->getId());
        $form->get('idusuario')->setData($usuario->getId());
        
        return $this->render('SieUsuariosBundle:Usuario:usuarioResetForm.html.twig', array(
            'persona'   => $persona,
            'form'   => $form->createView(),
        ));
    }
    
    public function userresetupdateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $data = $request->get('sie_usuarios_reset_form');
        $response = new JsonResponse();
        try {
            $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($data['idusuario']);
            $usuario->setPassword2($usuario->getPassword());
            $usuario->setPassword(md5($usuario->getUsername()));
            $usuario->setFechaRegistro(new \DateTime('now'));
            $usuario->setEstadopassword('2');
            $em->persist($usuario);
            $em->flush();

            //$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('usuario_rol');");
            //$query->execute();

            $this->get('funciones')->setLogTransaccion(
                $data['idusuario'],
                'usuario',
                'U',
                'Reset_En_Espera',
                $usuario,
                $usuario,
                'DefaultController/userresetupdate',
                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );

            $em->getConnection()->commit();
            return $response->setData(array('mensaje' => 'Proceso realizado exitosamente.'));
            }
        catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Error en el registro.'));
            }
    }
    
    /*public function userdisableAction($usuarioid, $usuariorolid) {
        $em = $this->getDoctrine()->getManager();                     
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse();        
        try {
            $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuarioid);
            $esActivoUsuario = $usuario->getEsActivo();
            if ($esActivoUsuario == 'true'){
                $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findByUsuario($usuarioid);
                foreach ($usuariorol as $p){
                    $p->setEsActivo('false');
                    $em->persist($p);
                    $em->flush();      
                }
                $usuario->setEsActivo('false');            
            }
            else{
                /*$usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findByUsuario($usuarioid);
                foreach ($usuariorol as $p){
                    $p->setEsActivo('true');
                    $em->persist($p);
                    $em->flush();                               
                $usuario->setEsActivo('true');               
            }
            $em->persist($usuario);
            $em->flush();            
            $em->getConnection()->commit();            
            return $response->setData(array(
                'mensaje'=>'Proceso realizado exitosamente.',
                'usuariorol_id'=>$usuariorolid,
                'usuariorolesActivo'=>$em->getRepository('SieAppWebBundle:UsuarioRol')->find($usuariorolid)->getEsActivo(),
                'usuarioid'=>$usuario->getId(),
                'usuarioesActivo'=>$usuario->getEsActivo(),
                ));
            } 
        catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!'));           
            }
    }*/
    
    public function userchangestateAction($usuarioid) {        
        $em = $this->getDoctrine()->getManager();                     
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse();        
        try {
            $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuarioid);            
            $esActivoUsuario = $usuario->getEsActivo();
            if ($esActivoUsuario == 'true')
                {
                $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findByUsuario($usuarioid);
                foreach ($usuariorol as $p){
                    $p->setEsActivo('false');
                    $em->persist($p);
                    $em->flush();      
                }
                $usuario->setEsActivo('false');
                $obs = 'Usuario Desactivado'.' por rol:'.$this->session->get('roluser');         
                }
            else
                {
                $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findByUsuario($usuarioid);
                foreach ($usuariorol as $p){
                    $p->setEsActivo('true');
                    $em->persist($p);
                    $em->flush();      
                }
                $usuario->setEsActivo('true'); 
                $obs = 'Usuario Activado'.' por rol:'.$this->session->get('roluser');                        
            }
            $em->persist($usuario);
            $em->flush();            
            $em->getConnection()->commit(); 
            
            $this->get('funciones')->setLogTransaccion(
                $usuarioid,
                'usuario',
                'U',
                $obs,
                $usuario,
                $usuario,
                'DefaultController/userchangestate',
                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );            

            return $response->setData(array(
                'mensaje'=>'Proceso realizado exitosamente.',                
                'usuarioid'=>$usuario->getId(),
                'usuarioesActivo'=>$usuario->getEsActivo(),
                ));
            } 
        catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!'));           
            }
    }
    
    public function userroldisableAction($usuarioid, $usuariorolid) {
        $em = $this->getDoctrine()->getManager();                     
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse();        
        try {
            $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->find($usuariorolid);
            $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuarioid);
            $esActivoRol = $usuariorol->getEsActivo();                    

            if ($esActivoRol == 'true')
                {
                $usuariorol->setEsActivo('false');
                $em->flush();            
                }
            else
                {
                $usuariorol->setEsActivo('true'); 
                $em->flush();               
                $usuario->setEsActivo('true');  
                $em->flush();
                }
            
            $em->getConnection()->commit();
            return $response->setData(array(
                'mensaje'=>'Proceso realizado exitosamente.',
                'usuariorol_id'=>$usuariorol->getId(),
                'usuariorolesActivo'=>$usuariorol->getEsActivo(),
                'usuarioid'=>$usuarioid,
                'usuarioesActivo'=>$em->getRepository('SieAppWebBundle:Usuario')->find($usuarioid)->getEsActivo(),
                ));          
            } 
        catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!'));           
            }

    }
    
    public function usernameupdateAction($usuarioid) {        
        $em = $this->getDoctrine()->getManager();                     
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse();        
        try {
            $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuarioid);
            $persona = $em->getRepository('SieAppWebBundle:Persona')->find($usuario->getPersona()->getId());
            $username = $persona->getCarnet().$persona->getComplemento();
            $personausuarios = $em->getRepository('SieAppWebBundle:Usuario')->findByUsername($username);
            if (sizeof($personausuarios) == 0) {
                $persona->setEsVigente('1');
                $persona->setEsvigenteApoderado('1'); 
                $em->persist($persona);
                $em->flush(); 
                $usuario->setUsername(trim($persona->getCarnet().$persona->getComplemento()));   
                $em->persist($usuario);
                $em->flush();            
                $em->getConnection()->commit();           
                return $response->setData(array('mensaje'=>'Proceso realizado exitosamente.', 'usuarioid'=>$usuario->getId(), 'username'=>$usuario->getUsername(),));                
            }
            if (sizeof($personausuarios) > 0) {
                return $response->setData(array('mensaje'=>'¡Proceso detenido! Se ha detectado inconsistencia de datos. Y se le comunica que ya existe el usuario :'.$persona->getCarnet().' asignada a otra persona. Debe corregir esta observación con urgencia con su técnico SIE para precautelar su responsabilidad en la información asignada a su persona.', 'usuarioid'=>$usuario->getId(), 'username'=>$usuario->getUsername(),));
            }
        } 
        catch (Exception $ex) {            
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje'=>'Proceso detenido! se ha detectado inconsistencia de datos!', 'usuarioid'=>$usuario->getId(), 'username'=>$usuario->getUsername(),));
            //return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!'));           
        }
    }

    public function usernameupdateloginAction($usuarioid) {        
        $em = $this->getDoctrine()->getManager();                     
        $em->getConnection()->beginTransaction();        
        try {
            $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuarioid);
            $persona = $em->getRepository('SieAppWebBundle:Persona')->find($usuario->getPersona()->getId());
            $username = $persona->getCarnet().$persona->getComplemento();
            $personausuarios = $em->getRepository('SieAppWebBundle:Usuario')->findByUsername($username);
            if (sizeof($personausuarios) == 0) {
                $usuario->setUsername(trim($persona->getCarnet().$persona->getComplemento()));   
                $em->persist($usuario);
                $em->flush();            
                $em->getConnection()->commit();           
                return $this->redirect($this->generateURL('logout'));
            }
            if (sizeof($personausuarios) > 0) {
                return $this->redirect($this->generateURL('logout'));
            }            
        } 
        catch (Exception $ex) {            
            $em->getConnection()->rollback();
            return $this->redirect($this->generateURL('logout'));
        }
    }
    
    public function userrollocacionAction($usuariorolid, $lugar_tipoid) {
        $em = $this->getDoctrine()->getManager();                     
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse();        
        try {
            $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->find($usuariorolid);
            $usuariorol->setLugarTipo($this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find($lugar_tipoid));                    
      
            $em->persist($usuariorol);
            $em->flush();
            
            $em->getConnection()->commit();
            return $response->setData(array(
                'mensaje'=>'Proceso realizado exitosamente.',
                'usuariorol_id'=>$usuariorol->getId(),                
                ));          
            } 
        catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!'));           
            }

    }

    public function userrolsubsistemaAction($usuariorolid, $value) {        
        $em = $this->getDoctrine()->getManager();                     
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse();
        try {
            $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->find($usuariorolid);
            $usuariorol->setSubSistema($value);
      
            $em->persist($usuariorol);
            $em->flush();
            
            $em->getConnection()->commit();
            return $response->setData(array(
                'mensaje'=>'Proceso realizado exitosamente.',                
                ));          
            } 
        catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!'));           
            }

    }
    
    public function estadisticasusuariosAction() {
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection(); 
        
        $query = "
                select * from rol_tipo rt inner join (
                select  b.rol_tipo_id, count(b.rol_tipo_id) as a
                from usuario a inner join usuario_rol b on a.id = b.usuario_id
                where a.esactivo is true
                and b.esactivo is true
                group by b.rol_tipo_id) b on rt.id = b.rol_tipo_id
                order by a desc
            ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);        
        $po = $stmt->fetchAll();
        
        return $this->render('SieUsuariosBundle:Default:usuarioestadisticas.html.twig', array(
            'estadisticas'   => $po,
        )); 
        
//        dump($po);
//        die;
    }
    
}
