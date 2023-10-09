<?php

namespace Sie\UsuariosBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\UsuariosBundle\Form\PersonaUsuarioType;
use Sie\UsuariosBundle\Form\PersonaCarnetType;
use Sie\AppWebBundle\Form\BuscarPersonaType;
use Sie\AppWebBundle\Form\BuscarPersonaTypev2;
use Sie\AppWebBundle\Form\BuscarPersonaNacionalType;
use Sie\AppWebBundle\Form\BuscarPersonaExtranjeroType;
use Sie\AppWebBundle\Form\BuscarPersonaSinCarnetType;
use Sie\UsuariosBundle\Form\CodIEType;

class BusquedasController extends Controller
{
    private $session;
    public $arrUserAllow;
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->arrUserAllow = array(5609814,3625644,5062963,8335918,5727128,4300231,1314301,3295554,1897494);

    }
    
    public function codiepersonasAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new CodIEType());
        $form->handleRequest($request);
        
        if ($form->isValid()) {      
            $data = $form->getData();
            
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $data['ue']);
            $query->bindValue(':roluser', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();
            //dump($aTuicion[0]['get_ue_tuicion']);die;
            if ($aTuicion[0]['get_ue_tuicion'] == false){
                $this->session->getFlashBag()->add('message', 'No tiene tuición sobre la institución educatia o no existe codigo SIE :'.$data['ue']);
                return $this->redirectToRoute('sie_usuarios_homepage');
            }else{
                $ie = $this->getDoctrine()->getRepository('SieAppWebBundle:Institucioneducativa')->find($data['ue']);
                return $this->redirectToRoute('sie_usuario_persona_show', array('ie_id' => $data['ue'],'ie_nombre' => $ie->getInstitucioneducativa()));
            }
            /*
            if (!$ie){
                
            }
            else{
                
            }*/
        }
        else{
            $this->session->getFlashBag()->add('error', 'Proceso detenido. Se ha detectado inconsistencia de datos.');
            return $this->redirectToRoute('sie_usuarios_homepage');
        }
    }

    
    public function formcarnetAction() {

        $us = array(8,31,7);
        if(!(in_array($this->session->get('roluser'), $us))){
            if(!in_array($this->session->get('userName'), $this->arrUserAllow)  ){
                return $this->redirectToRoute('sie_usuarios_homepage');
            }
        }

        $formBuscarPersona = $this->createForm(new BuscarPersonaType(array('opcion'=>0)), null, array('action' => $this->generateUrl('sie_usuario_persona_buscar_carnet'), 'method' => 'POST',));

        return $this->render('SieUsuariosBundle:Default:usuariocarnet.html.twig', array(           
            'formBuscarPersona'   => $formBuscarPersona->createView(),            
        ));
    }
    
    public function carnetpersonabuscarAction(Request $request) {
        //  dump('ok');die;
        $formNacional = $this->createForm(new BuscarPersonaTypev2(array('opcion'=>0)));
        // dump($formNacional);
        $formNacional->handleRequest($request);
        // dump($formNacional);die;
        if ($formNacional->isValid())
        {
            $persona = $formNacional->getData();
            /*
            $data = $formNacional->getData();
            $servicioPersona = $this->get('sie_app_web.persona');
            if($data['complemento']=="")
            {
                $resultService = $servicioPersona->buscarPersona($data['ci'], '0', $data['extranjero']);            
                //$data['complemento'] = 0;
            }
            else
            {
                $resultService = $servicioPersona->buscarPersona($data['ci'], $data['complemento'], $data['extranjero']);
            }
            */
            
            $fecha = str_replace('-','/',$persona['fecha_nacimiento']);
            $complemento = $persona['complemento'] == null? '':$persona['complemento'];
            $arrayDatosPersona = array(
                //'carnet'=>$form['carnet'],
                'complemento'=>$complemento,
                'fecha_nacimiento' => $fecha
            );
            // dump($persona['extranjero']); die;
            
            if($persona['extranjero']=='1')
                $arrayDatosPersona['tipo_persona']='2';
            // dump($arrayDatosPersona['extranjero']); die;

            $personaValida = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($persona['ci'], $arrayDatosPersona, 'prod', 'academico');
            // dump($personaValida); die;
            if( $personaValida )
            {
                $arrayDatosPersona['carnet']=$persona['ci'];
                unset($arrayDatosPersona['fecha_nacimiento']);
                $arrayDatosPersona['fechaNacimiento'] = str_replace('/','-',$persona['fecha_nacimiento']);
                $rolid = $this->session->get('roluser');
                $personaEncontrada = $this->get('buscarpersonautils')->buscarPersonav2($arrayDatosPersona,$conCI=true, $segipId=1);
                
                $us = array(7,8,31); //solo los que pueden crear usuarios
                
                if((in_array($this->session->get('roluser'), $us)) and (count($personaEncontrada)>0)){
                    $idperson = $personaEncontrada->getId();                   
                    $em = $this->getDoctrine()->getManager();
                    $db = $em->getConnection();
                    $query = '  select ur.*
                                from usuario u 
                                inner join usuario_rol ur on u.id = ur.usuario_id 
                                where u.persona_id = :personaId
                                and ur.rol_tipo_id not in (
                                select rol_tuicion_id
                                from rol_tuicion rt
                                where rt.rol_tipo_id = :rolTipoId)
                                and ur.esactivo = true ';
                        $stmt = $db->prepare($query);
                        $stmt->bindValue(':personaId', $idperson);
                        $stmt->bindValue(':rolTipoId', $this->session->get('roluser'));
                        //$params = array($idperson);
                        $stmt->execute();
                        $po=$stmt->fetchAll();
                    if (count($po) >= 1){
                        $this->session->getFlashBag()->add('error', 'Proceso detenido. No tiene tuicion sobre estos datos de esa persona. ');
                        return $this->redirectToRoute('sie_usuarios_homepage');
                    }
                 } 
                
                if($personaEncontrada != null) //ALTERNATIVA A resultService->type_msg === 'success'
                {
                    $idperson = $personaEncontrada->getId();

                    $em = $this->getDoctrine()->getManager();
                    $db = $em->getConnection();
                    $query = '
                        select 
                                a.id as usuarioid, a.username, a.esactivo as usuarioesactivo,
                                d.id as personaid, d.carnet, d.complemento, d.paterno, d.materno, d.nombre,
                                array_agg(cast(b.id as varchar)) as usuario_rol_id, 
                                cast(array_agg(cast(c.id as varchar)) as text) as rol_ids,
                                array_agg(cast(c.rol as varchar)) as roles,
                                array_agg(cast(b.esactivo as varchar)) as roles_estado,
                                array_agg(cast(e.id as varchar)) as jurisdiccin_lugar_id,
                                array_agg(cast(e.lugar as varchar)) as jurisdiccion,
                                array_agg(cast(f.id as varchar)) as cobertura_cod,
                                array_agg(cast(f.nivel as varchar)) as cobertura,
                                array_agg(cast(b.sub_sistema as varchar)) as subsistema
                        from usuario a 
                                inner join usuario_rol b on a.id = b.usuario_id
                                inner join rol_tipo c on b.rol_tipo_id = c.id
                                inner join lugar_tipo e on b.lugar_tipo_id = e.id
                                inner join lugar_nivel_tipo f on e.lugar_nivel_id = f.id
                                inner join persona d on a.persona_id = d.id
                        where d.id = ?
                        group by a.id, a.username, a.esactivo,d.id, d.carnet, d.complemento, d.paterno, d.materno, d.nombre
                        order by a.esactivo';
                    $stmt = $db->prepare($query);
                    $params = array($idperson);
                    $stmt->execute($params);
                    $po=$stmt->fetchAll();
                    // dump($this->session->get('roluser'));
                    // dump($idperson); die;
                    if (count($po) === 1)//PERSONA SEGIP CON UN USUARIO CORRECTAMENTE ENCONTRADOS
                    {
                        $query = "select a.roltipoid, b.rol_tuicion_id
                                from (
                                select a.rol_tipo_id as roltipoid from usuario_rol a
                                    inner join usuario b on a.usuario_id = b.id 
                                    where b.persona_id = ? and a.esactivo is true) a
                                left join (
                                select * 
                                from rol_tuicion b 
                                where b.rol_tipo_id = ?) b on  a.roltipoid = b.rol_tuicion_id 
                                where  b.rol_tuicion_id is null";
                        $stmt = $db->prepare($query);
                        $params = array($idperson,$this->session->get('roluser'));
                        $stmt->execute($params);
                        $potuision = $stmt->fetchAll(); 

                        //VERIFICANDO TUISION JURISDICCIÓN
                        // dump($idperson);
                        // dump($this->session->get('roluser'));
                        // dump($potuision);die;
                        if (count($potuision) == 0)
                        {
                            $accion = 'okuser';
                            
                        }
                        else
                        {
                            $accion = 'sintuision';
                        }

                        //VERIFICANDO USUARIO Y CARNET COMPLEMENTO PARA MENSAJE
                        $usercarnet = $po[0]['carnet'].$po[0]['complemento'];
                        $username = $po[0]['carnet'];
                        
                        //dump($usercarnet);dump($username);die;

                        if ($usercarnet <> $username)
                        {
                            $this->session->getFlashBag()->add('warning', 'El nombre de usuario no coincide con el número de carnet y complemento');    
                        }
                        else
                        {
                            $this->session->getFlashBag()->add('success', 'El numero de carnet y su usuario no presentan problemas.');
                        }
                        
                        return $this->render('SieUsuariosBundle:Busquedas:usuario.html.twig', array(
                            'accion' => $accion,
                            'po' => $po,
                            'rolid' => $rolid,
                        ));
                    }

                    if (count($po) === 0) //PERSONA SEGIP SIN USUARIO
                    {
                        $accion = 'newuser';
                        $this->session->getFlashBag()->add('warning', 'El número de carnet no cuenta con usuario.');
                        return $this->render('SieUsuariosBundle:Busquedas:usuario.html.twig', array(
                            'accion' => $accion,
                            //'po' => $resultService->result,
                            'po' => array($personaEncontrada),
                            'rolid' => $rolid,
                        ));
                    }

                    if (count($po) > 1) //PERSONA CON MAS DE UN USUARIO
                    {
                        $accion = 'stop';
                        $this->session->getFlashBag()->add('error', 'El numero de carnet cuenta con mas de un usuario.');
                        return $this->render('SieUsuariosBundle:Busquedas:usuario.html.twig', array(
                            'accion' => $accion,
                            'po'   => $po,
                            'rolid' => $rolid,
                        ));
                    }
                }
                else
                {
                    $po = array("ci" => trim($persona['ci']), "complemento" => trim($persona['complemento']),);
                    $accion = 'newperson';
                    $this->session->getFlashBag()->add('message', 'El numero de carnet no tiene ningun registro en el SIGED.');
                    return $this->render('SieUsuariosBundle:Busquedas:usuario.html.twig', array(
                           //'personas'   => $po,
                           //'ci'   => trim($data['ci']),
                           //'complemento'   => trim($data['complemento']),
                           'accion' => $accion,
                           'po'   => $po,
                           'rolid' => $rolid,
                    ));
                }
            }
            else
            {
                $this->session->getFlashBag()->add('error', 'Proceso detenido. Los datos de la persona no son validos según validacion de SEGIP, por favor revise los campos escritos.');
                return $this->redirectToRoute('sie_usuarios_homepage');
            }

        /*
            if($resultService->type_msg === "success")
            {
                $idperson = $resultService->result[0]->id;
                //dump($resultService->result);die;
                $em = $this->getDoctrine()->getManager();
                //$em = $this->getDoctrine()->getEntityManager();
                $db = $em->getConnection();                
                $query = "
                        select 
                                a.id as usuarioid, a.username, a.esactivo as usuarioesactivo,
                                d.id as personaid, d.carnet, d.complemento, d.paterno, d.materno, d.nombre,
                                array_agg(cast(b.id as varchar)) as usuario_rol_id,	
                                cast(array_agg(cast(c.id as varchar)) as text) as rol_ids,
                                array_agg(cast(c.rol as varchar)) as roles,
                                array_agg(cast(b.esactivo as varchar)) as roles_estado,
                                array_agg(cast(e.id as varchar)) as jurisdiccin_lugar_id,
                                array_agg(cast(e.lugar as varchar)) as jurisdiccion,
                                array_agg(cast(f.id as varchar)) as cobertura_cod,
                                array_agg(cast(f.nivel as varchar)) as cobertura,
                                array_agg(cast(b.sub_sistema as varchar)) as subsistema
                        from usuario a 
                                inner join usuario_rol b on a.id = b.usuario_id
                                inner join rol_tipo c on b.rol_tipo_id = c.id
                                inner join lugar_tipo e on b.lugar_tipo_id = e.id
                                inner join lugar_nivel_tipo f on e.lugar_nivel_id = f.id
                                inner join persona d on a.persona_id = d.id
                        where d.id = '".$idperson."'
                                group by a.id, a.username, a.esactivo,
                                d.id, d.carnet, d.complemento, d.paterno, d.materno, d.nombre
                        order by a.esactivo";
                $stmt = $db->prepare($query);
                $params = array();
                $stmt->execute($params);
                $po = $stmt->fetchAll();
                
                $accion = 'undefined';
                if (count($po) === 1)
                {//PERSONA SEGIP CON UN USUARIO CORRECTAMENTE ENCONTRADOS
                    //VERIFICANDO TUISION DE ROLES DEL USUARIO
                    $query = "
                                select * from (
                                    select c.id 
                                    from rol_tipo c 
                                    where c.id not in(
                                    select roles as id from rol_roles_asignacion b where b.rol_id = '".$this->session->get('roluser')."'
                                    ) ) abc 
                                    inner join (
                                        select a.rol_tipo_id as roltipoid from usuario_rol a
                                        inner join usuario b on a.usuario_id = b.id 
                                        where b.persona_id = '".$idperson."' and a.esactivo is true
                                    ) xyz
                                    on abc.id = xyz.roltipoid";
                    $stmt = $db->prepare($query);
                    $params = array();
                    $stmt->execute($params);
                    $potuision = $stmt->fetchAll(); 
                    
                    //VERIFICANDO TUISION JURISDICCIÓN                                        
                    //dump($this->session->get('roluserlugarid'));dump($po);die;
                    
                    if (count($potuision) > 0)
                    {
                        $accion = 'okuser';
                        
                    }
                    else
                    {
                        $accion = 'sintuision';
                    }

                    //VERIFICANDO USUARIO Y CARNET COMPLEMENTO PARA MENSAJE
                    $usercarnet = $po[0]['carnet'].$po[0]['complemento'];                    
                    $username = $po[0]['carnet'];

                    //dump($usercarnet);dump($username);die;

                    if ($usercarnet <> $username)
                    {
                        $this->session->getFlashBag()->add('warning', 'El nombre de usuario no coincide con el número de carnet y complemento');    
                    }
                    else
                    {
                        $this->session->getFlashBag()->add('success', 'El numero de carnet y su usuario no presentan problemas.');
                    }
                    
                    return $this->render('SieUsuariosBundle:Busquedas:usuario.html.twig', array(
                        'accion' => $accion,
                        'po' => $po,
                    ));
                }                
                if (count($po) === 0)
                {//PERSONA SEGIP SIN USUARIO
                    $accion = 'newuser';
                    $this->session->getFlashBag()->add('warning', 'El número de carnet no cuenta con usuario.');
                    return $this->render('SieUsuariosBundle:Busquedas:usuario.html.twig', array(                            
                        'accion' => $accion,
                        'po' => $resultService->result,
                    ));                     
                }
                if (count($po) > 1)
                {//PERSONA CON MAS DE UN USUARIO
                    $accion = 'stop';
                    $this->session->getFlashBag()->add('error', 'El numero de carnet cuenta con mas de un usuario.');
                    return $this->render('SieUsuariosBundle:Busquedas:usuario.html.twig', array(
                        'accion' => $accion,
                        'po'   => $po,
                    ));
                }
            }
            if(($resultService->type_msg === "danger") || ($resultService->type_msg === "warning") || ($resultService->type_msg === "info"))
            {
                if($resultService->result === "null")
                {
                        $po = array("ci" => trim($data['ci']), "complemento" => trim($data['complemento']),);
                        //dump($po);die;
                        $accion = 'newperson';
                        $this->session->getFlashBag()->add('message', 'El numero de carnet no tiene ningun registro en el SIGED.');
                        return $this->render('SieUsuariosBundle:Busquedas:usuario.html.twig', array(           
                               //'personas'   => $po,
                               //'ci'   => trim($data['ci']),
                               //'complemento'   => trim($data['complemento']),
                               'accion' => $accion,
                               'po'   => $po,
                        ));

                }
                $po = array();
                $accion = 'stopall';
                $this->session->getFlashBag()->add($resultService->type_msg, $resultService->msg);
                return $this->render('SieUsuariosBundle:Busquedas:usuario.html.twig', array(
                    'po'   => $po,                   
                    'accion' => $accion,
                ));
            }
        */
       
        }
        else
        {
            $this->session->getFlashBag()->add('error', 'Proceso detenido.');
            return $this->redirectToRoute('sie_usuarios_homepage');
        }
    }
    
    public function formusuarioAction() {

        if(!($this->session->get('roluser') == 8)){
            if(!in_array($this->session->get('userName'), $this->arrUserAllow)  ){
                return $this->redirectToRoute('sie_usuarios_homepage');
            }
        }        
        $form = $this->createForm(new PersonaUsuarioType(), null, array('action' => $this->generateUrl('sie_usuario_resultado_nombreusuario'), 'method' => 'POST',));        
        return $this->render('SieUsuariosBundle:Busquedas:personausuario.html.twig', array(           
            'form'   => $form->createView(),
        ));
    }
    
    public function resultadobusquedausuarioAction(Request $request) {
        $form = $this->createForm(new PersonaUsuarioType());
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();             
            $em = $this->getDoctrine()->getManager();
            //$em = $this->getDoctrine()->getEntityManager();
            $db = $em->getConnection();  
            
            $query = " SELECT               
                            persona.id as personaid,
                            persona.carnet,
                            persona.complemento,
                            persona.nombre, 
                            persona.paterno, 
                            persona.materno,              
                            persona.fecha_nacimiento,
                            persona.count_edit,                  
                            usuario.username,
                            usuario.id as usuarioid,
                            usuario.esactivo as usuarioesactivo,
                            array_agg(cast(usuario_rol.rol_tipo_id as varchar)) as roles,
                            array_agg(cast(rol_tipo.rol as varchar)) as roles_txt,
                            array_agg(cast(usuario_rol.lugar_tipo_id as varchar)) as rol_lugar_id
                            FROM 
                            persona LEFT JOIN public.usuario
                                    ON usuario.persona_id = persona.id
                                    INNER JOIN public.usuario_rol
                                    ON usuario_rol.usuario_id = usuario.id
                                    INNER JOIN public.rol_tipo
                                    ON usuario_rol.rol_tipo_id = rol_tipo.id
                            WHERE    
                                (usuario.username = '".trim($data['username'])."')
                            GROUP BY
                            usuario.id, 
                            persona.id,
                            persona.carnet, 
                            persona.nombre, 
                            persona.paterno, 
                            persona.materno,              
                            usuario.username
                            ORDER BY persona.nombre, persona.paterno, persona.materno ";
            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $po = $stmt->fetchAll();
            $accion = 'undefined';
            //dump($po);die;
            if (count($po) ==  1){
                    //SE ENCONTRO UN USUARIO
                    $accion = 'userfound';
                    $this->session->getFlashBag()->add('warning', 'Se le informa que la opción "Busqueda por Nombre de Usuario", eventualmente desaparecera. Utilice la opción de "Busqueda por Carnet" principalmente.');
                    return $this->render('SieUsuariosBundle:Busquedas:usuario.html.twig', array(                            
                        'accion' => $accion,
                        'po' => $po[0],
                    ));                
            }else{
                /*if (count($po) > 1){//VARIAS PERSONAS CON EL MISMO USUARIO
                    $accion = 'stop';
                    $this->session->getFlashBag()->add('danger', 'Existen duplicados de usuario o varias personas registradas al mismo usuario.');
                    return $this->render('SieUsuariosBundle:Busquedas:personalistabusqueda.html.twig', array(
                        'personas'   => $po,
                        'accion' => $accion,
                    ));
                }*/
                
                //NO EXISTE EL USUARIO
                $accion = 'sinusuario';
                $this->session->getFlashBag()->add('warning', '¡No existe el nombre de usuario!');
                return $this->render('SieUsuariosBundle:Busquedas:usuario.html.twig', array(                            
                    'accion' => $accion,
                    'po' => array(),
                ));  
               
                /*$query = "                SELECT      
                                persona.id as personaid,
                                persona.carnet,
                                persona.complemento, 
                                persona.nombre, 
                                persona.paterno, 
                                persona.materno,              
                                persona.fecha_nacimiento,
                                persona.count_edit,                  
                                usuario.username,
                                usuario.id as usuarioid,
                                usuario.esactivo as usuarioesactivo,
                                array_agg(cast(usuario_rol.rol_tipo_id as varchar)) as roles
                                FROM 
                                persona LEFT JOIN public.usuario
                                        ON usuario.persona_id = persona.id
                                        LEFT JOIN public.usuario_rol
                                                ON usuario_rol.usuario_id = usuario.id
                                WHERE    
                                    (usuario.username = '".trim($data['username'])."')
                                and (persona.segip_id > 0)
                                GROUP BY
                                usuario.id, 
                                persona.id,
                                persona.carnet, 
                                persona.nombre, 
                                persona.paterno, 
                                persona.materno,              
                                usuario.username
                                ORDER BY persona.nombre, persona.paterno, persona.materno";
                $stmt = $db->prepare($query);
                $params = array();
                $stmt->execute($params);
                $po = $stmt->fetchAll();
                $accion = 'undefined';
                //dump(count($po));die;               
                if (count($po) > 0){
                    if (count($po) > 1){//VARIAS PERSONAS CON EL MISMO USUARIO 18500121K
                        $accion = 'stop';
                        $this->session->getFlashBag()->add('danger', 'Existen duplicados de usuario o varias personas registradas al mismo usuario con inconsistencia de roles.');                    
                        return $this->render('SieUsuariosBundle:Busquedas:personalistabusquedasinrol.html.twig', array(           
                            'personas'   => array(),
                            'accion' => $accion,
                        ));
                    }
                    else{//BIEN UN SOLO USUARIO   
                        $accion = 'okuser';
                        $this->session->getFlashBag()->add('message', 'Existe un usuario registrado sin asignación de roles.');                    
                        return $this->render('SieUsuariosBundle:Busquedas:personalistabusquedasinrol.html.twig', array(           
                            'personas'   => $po,
                            'accion' => $accion,
                        ));
                    }
                }
                else{//USUARIOS ACTIVOS CON PERSONAS ¡NO VIGENTES!
                    $query = "SELECT               
                                persona.id as personaid,
                                persona.carnet,
                                persona.complemento,
                                persona.nombre, 
                                persona.paterno, 
                                persona.materno,              
                                persona.fecha_nacimiento,
                                persona.count_edit,                  
                                usuario.username,
                                usuario.id as usuarioid,
                                usuario.esactivo as usuarioesactivo,
                                array_agg(cast(usuario_rol.rol_tipo_id as varchar)) as roles
                                FROM 
                                persona LEFT JOIN public.usuario
                                        ON usuario.persona_id = persona.id
                                        LEFT JOIN public.usuario_rol
                                        ON usuario_rol.usuario_id = usuario.id
                                WHERE    
                                    (usuario.username = '".trim($data['username'])."')
                                GROUP BY
                                usuario.id, 
                                persona.id,
                                persona.carnet, 
                                persona.nombre, 
                                persona.paterno, 
                                persona.materno,              
                                usuario.username
                                ORDER BY persona.nombre, persona.paterno, persona.materno";
                    $stmt = $db->prepare($query);
                    $params = array();
                    $stmt->execute($params);
                    $po = $stmt->fetchAll();
                    $accion = 'undefined';

                    if (count($po) > 0){
                        if (count($po) > 1){//VARIAS PERSONAS CON EL MISMO USUARIO
                            $accion = 'stop';
                            $this->session->getFlashBag()->add('danger', 'Existen duplicados de usuario o varias personas no vigentes registradas al mismo usuario.');                    
                            return $this->render('SieUsuariosBundle:Busquedas:personalistabusquedausuariopersonanovign.html.twig', array(           
                                'personas'   => array(),
                                'accion' => $accion,
                            ));
                        }
                        else{//BIEN UN SOLO USUARIO   
                            $accion = 'okuser';
                            $this->session->getFlashBag()->add('message', 'Existe un usuario registrado a una sola persona pero que no esta vigente.');
                            return $this->render('SieUsuariosBundle:Busquedas:personalistabusquedausuariopersonanovign.html.twig', array(           
                                'personas'   => $po,
                                'accion' => $accion,
                            ));
                        }
                    }
                    else{
                        $this->session->getFlashBag()->add('message', 'El nombre de usuario no se encuentra registrado.');
                        return $this->render('SieUsuariosBundle:Busquedas:personalistabusquedasinrol.html.twig', array(           
                            'personas'   => array(),
                            'accion' => $accion,
                        ));
                    }*/
                }                             
        }else{
            $this->session->getFlashBag()->add('error', 'Proceso detenido. Se ha detectado inconsistencia de datos.');
            return $this->redirectToRoute('sie_usuarios_homepage');
        }
    }

    public function formcodieAction() {

        $arrRolAllow = array(7,8,31,10);
        if(!in_array($this->session->get('roluser'), $arrRolAllow)){
                    if(!in_array($this->session->get('userName'), $this->arrUserAllow)  ){
                return $this->redirectToRoute('sie_usuarios_homepage');
            }
        }
        $form = $this->createForm(new CodIEType(), null, array('action' => $this->generateUrl('sie_usuario_cod_ie_buscar'), 'method' => 'POST',));        
        return $this->render('SieUsuariosBundle:Default:codie.html.twig', array(           
            'form'   => $form->createView(),
        ));
    }
       
    //**LISTADO DE UNIDADES DONDE LA PERSONA ESTA COMO DIRECTOR
    public function resultadopersonaunidadesAction(Request $request, $usuarioid) {
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $query = "
            SELECT 
              usuario.id as usuarioid, 
              persona.id as personaid,
              cargo_tipo.cargo, 
              maestro_inscripcion.institucioneducativa_id,
              institucioneducativa.institucioneducativa
            FROM 
	      institucioneducativa INNER JOIN maestro_inscripcion
                    ON institucioneducativa.id = maestro_inscripcion.institucioneducativa_id  
              INNER JOIN persona
                    ON persona.id = maestro_inscripcion.persona_id  
              INNER JOIN public.cargo_tipo
                    ON maestro_inscripcion.cargo_tipo_id = cargo_tipo.id
              LEFT JOIN public.usuario
                    ON usuario.persona_id = persona.id  
            WHERE    
              (usuario.id = '".$usuarioid."') and              
              (maestro_inscripcion.gestion_tipo_id = '".$sesion->get('currentyear')."') and
              (maestro_inscripcion.es_vigente_administrativo = true) and
              ((cargo_tipo.id = '1') or (cargo_tipo.id = '12'))
            GROUP BY
              cargo_tipo.cargo, 
              usuario.id, 
              persona.id, 
              maestro_inscripcion.institucioneducativa_id,
              institucioneducativa.institucioneducativa
            ORDER BY persona.nombre, persona.paterno, persona.materno
            ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);        
        $po = $stmt->fetchAll();

        //dump(count($po)); die;
        if (count($po) == 0) {//ERROR
            $sesion->set('ie_activo','0');
            $this->session->getFlashBag()->add('message', 'No se encuentra unidades educativa en donde este como Es_Vigente_Director en la presente gestión. Comuniquese con su técnico distrital.');
            return $this->render('SieUsuariosBundle:Listas:personaunidades.html.twig', array(            
                'personas' => array(),            
            ));
        }
        if (count($po) == 1) { //CORRECTOR
            //dump($po[0]['institucioneducativa_id']); die;
            return $this->redirectToRoute('sie_usuario_persona_show',array('ie_id' => $po[0]['institucioneducativa_id'], 'ie_nombre' => $po[0]['institucioneducativa']));      
        }
        if (count($po) > 1) {
            $sesion->set('ie_activo','0');
            $this->session->getFlashBag()->add('success', 'Resultado de la busqueda!');
            return $this->render('SieUsuariosBundle:Listas:personaunidades.html.twig', array(            
                'personas' => $po,            
            ));
        }
    }        
    
    public function apoderadosieAction() {

        if(!($this->session->get('roluser') == 8)){
            if(!in_array($this->session->get('userName'), $this->arrUserAllow)  ){
                return $this->redirectToRoute('sie_usuarios_homepage');
            }
        }

        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();            
        $query = "
                    SELECT z.id as usuarioid, z.username, z.esactivo,
                           k.id as personaid,
                           k.nombre,
                           k.paterno,
                           k.materno,
                           k.carnet,
                           k.complemento,
                           array_agg( cast((apoderado_tipo.apoderado) as varchar) || ' :' || cast((h.codigo_rude) as varchar) || '-' || cast((h.nombre) as varchar) || ' ' || cast((h.paterno) as varchar)   ) as estudiante

                    FROM estudiante_inscripcion b
                            INNER JOIN estudiante h on h.id = b.estudiante_id
                            INNER JOIN institucioneducativa_curso ON b.institucioneducativa_curso_id = institucioneducativa_curso.id 
                            INNER JOIN apoderado_inscripcion ON apoderado_inscripcion.estudiante_inscripcion_id = b.id
                            INNER JOIN apoderado_tipo on apoderado_inscripcion.apoderado_tipo_id = apoderado_tipo.id
                            INNER JOIN persona k on k.id = apoderado_inscripcion.persona_id
                            LEFT JOIN usuario z ON z.persona_id = k.id 

                    WHERE (institucioneducativa_curso.institucioneducativa_id = '".$this->session->get('ie_id')."') 
                            and (institucioneducativa_curso.gestion_tipo_id = '2018')
                    GROUP BY 
                            z.id, z.username, z.esactivo,
                            k.id, k.carnet, k.nombre, k.paterno, k.materno,
                            apoderado_tipo.apoderado
                            
                    ORDER BY k.paterno, k.materno, k.nombre";

        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();    
        //dump($po);die;
        if (!$po) {
            $this->session->getFlashBag()->add('error', 'No se encontro apoderados asociadas a la institucion educativa en la presente gestión.');
            //RENDERIZADO        
            return $this->render('SieUsuariosBundle:Listas:apoderadosie.html.twig', array(
                'depid' => '',
                'disid' => '',
                'personas' => $po,
            ));
        }        
        
        //RENDERIZADO        
        return $this->render('SieUsuariosBundle:Listas:apoderadosie.html.twig', array(
            'depid' => $this->session->get('dep_id'),
            'disid' => $this->session->get('dis_id'),
            'personas' => $po,
        ));
    }   
    
}
