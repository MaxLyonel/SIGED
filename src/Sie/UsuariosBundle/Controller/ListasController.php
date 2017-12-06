<?php

namespace Sie\UsuariosBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\UsuariosBundle\Form\ListaUsuarioPorRolType;

class ListasController extends Controller
{
    private $session;
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    public function listassegunrolAction(Request $request) {
        $em = $this->getDoctrine()->getManager();        
        //$persona = $em->getRepository('SieAppWebBundle:Persona')->find($personaid);
        
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        //LEYENDO ROLES QUE PUEDE CREAR EL USUARIO        
        $idsrol = $em->getRepository('SieAppWebBundle:RolRolesAsignacion')->getFindRolesByUsername($this->session->get('roluser'),$this->session->get('rol_ids'));
                
        $form = $this->createForm(new ListaUsuarioPorRolType(array('rolids' => $idsrol)), null, array(
        'action' => $this->generateUrl('sie_usuarios_listar_por_rol'),
        'method' => 'POST',)); 
                                
        return $this->render('SieUsuariosBundle:Default:listasporrol.html.twig', array(            
            'form'      => $form->createView(),
            'accion'    => 'new',
        ));     

    }


    public function usuarionaciolistaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();  

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $idsrol = $em->getRepository('SieAppWebBundle:RolRolesAsignacion')->getFindRolesByUsername($this->session->get('roluser'),$this->session->get('rol_ids'));
        $form = $this->createForm(new ListaUsuarioPorRolType(array('rolids' => $idsrol)));
        $form->handleRequest($request);                
        if ($form->isValid()) {
                $data = $form->getData();                           
                $idrol = $data['rolTipo'];
                //dump($data['rolTipo']->getRol());die;
                $idrol = strval($idrol->getId());                
                $em = $this->getDoctrine()->getManager();
                $db = $em->getConnection();            
                $query = "
                    select * from(
                            select *, replace(rol_ids,'{".$idrol."}','*')||replace(rol_ids,'{".$idrol.",','*')||replace(rol_ids,',".$idrol."}','*')||replace(rol_ids,',".$idrol.",','*') as aux
                                from (
                                
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
                                        --where a.username = 'lapaz2061'
                                                group by a.id, a.username, a.esactivo,			
                                                d.id, d.carnet, d.complemento, d.paterno, d.materno, d.nombre		
                                        order by a.esactivo
                                                
                                        ) abc
                                        
                            ) xyz
                            where xyz.aux like '%*%'";
                $stmt = $db->prepare($query);
                $params = array();
                $stmt->execute($params);
                $po = $stmt->fetchAll();
                return $this->render('SieUsuariosBundle:Listas:listausuarios.html.twig', array(                    
                    'po' => $po,
                    'titulo' => 'Listado de usuarios con rol de :'.$data['rolTipo']->getRol(),
                ));         
        }
    }

    public function listassegunjurisdiccionAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieUsuariosBundle:Default:listaporjurisdiccion.html.twig');
    }

    public function listassegunjurisdiccionnacionalAction(Request $request) {        
                $em = $this->getDoctrine()->getManager();

                $sesion = $request->getSession();
                $id_usuario = $sesion->get('userId');
                if (!isset($id_usuario)) {
                    return $this->redirect($this->generateUrl('login'));
                }
                  
                $em = $this->getDoctrine()->getManager();
                $db = $em->getConnection();            
                $query = "
                    select * from(
                            select  *, replace(jurisdiccin_lugar_id,'{1}','*')||replace(jurisdiccin_lugar_id,'{1,','*')||replace(jurisdiccin_lugar_id,',1}','*')||replace(jurisdiccin_lugar_id,',1,','*') as aux
                            from(
                                    select 
                                                            a.id as usuarioid, a.username, a.esactivo as usuarioesactivo,                    
                                                            d.id as personaid, d.carnet, d.complemento, d.paterno, d.materno, d.nombre,		 
                                                            array_agg(cast(b.id as varchar)) as usuario_rol_id,	
                                                            cast(array_agg(cast(c.id as varchar)) as text) as rol_ids,
                                                            array_agg(cast(c.rol as varchar)) as roles,
                                                            array_agg(cast(b.esactivo as varchar)) as roles_estado,
                                                            cast(array_agg(cast(e.id as varchar)) as text) as jurisdiccin_lugar_id,		
                                                            array_agg(cast(e.lugar as varchar)) as jurisdiccion,		
                                                            cast(array_agg(cast(f.id as varchar)) as text) as cobertura_cod,		
                                                            array_agg(cast(f.nivel as varchar)) as cobertura                    
                                                    from usuario a 
                                                            inner join usuario_rol b on a.id = b.usuario_id
                                                            inner join rol_tipo c on b.rol_tipo_id = c.id
                                                            inner join lugar_tipo e on b.lugar_tipo_id = e.id
                                                            inner join lugar_nivel_tipo f on e.lugar_nivel_id = f.id
                                                            inner join persona d on a.persona_id = d.id
                                                    group by a.id, a.username, a.esactivo,			
                                                            d.id, d.carnet, d.complemento, d.paterno, d.materno, d.nombre		
                                                    order by a.esactivo

                                        ) abc
                            ) xyz
                            where xyz.aux like '%*%'";
                $stmt = $db->prepare($query);
                $params = array();
                $stmt->execute($params);
                $po2 = $stmt->fetchAll();
                return $this->render('SieUsuariosBundle:Listas:listausuarios.html.twig', array(                    
                    'po' => $po2,
                    'titulo' => 'Listado de usuarios con jurisdiccion a nivel nacionales.',
                ));
    } 

    public function listassegunjurisdiccionidlugarAction(Request $request, $iddepto) {
                $em = $this->getDoctrine()->getManager();

                $sesion = $request->getSession();
                $id_usuario = $sesion->get('userId');
                if (!isset($id_usuario)) {
                    return $this->redirect($this->generateUrl('login'));
                }
                  
                $em = $this->getDoctrine()->getManager();
                $db = $em->getConnection();            
                $query = "
                    select * from(
                            select  *, replace(jurisdiccin_lugar_id,'{".$iddepto."}','*')||replace(jurisdiccin_lugar_id,'{".$iddepto.",','*')||replace(jurisdiccin_lugar_id,',".$iddepto."}','*')||replace(jurisdiccin_lugar_id,',".$iddepto.",','*') as aux
                            from(
                                    select 
                                                            a.id as usuarioid, a.username, a.esactivo as usuarioesactivo,                    
                                                            d.id as personaid, d.carnet, d.complemento, d.paterno, d.materno, d.nombre,		 
                                                            array_agg(cast(b.id as varchar)) as usuario_rol_id,	
                                                            cast(array_agg(cast(c.id as varchar)) as text) as rol_ids,
                                                            array_agg(cast(c.rol as varchar)) as roles,
                                                            array_agg(cast(b.esactivo as varchar)) as roles_estado,
                                                            cast(array_agg(cast(e.id as varchar)) as text) as jurisdiccin_lugar_id,		
                                                            array_agg(cast(e.lugar as varchar)) as jurisdiccion,		
                                                            cast(array_agg(cast(f.id as varchar)) as text) as cobertura_cod,		
                                                            array_agg(cast(f.nivel as varchar)) as cobertura                    
                                                    from usuario a 
                                                            inner join usuario_rol b on a.id = b.usuario_id
                                                            inner join rol_tipo c on b.rol_tipo_id = c.id
                                                            inner join lugar_tipo e on b.lugar_tipo_id = e.id
                                                            inner join lugar_nivel_tipo f on e.lugar_nivel_id = f.id
                                                            inner join persona d on a.persona_id = d.id
                                                    group by a.id, a.username, a.esactivo,			
                                                            d.id, d.carnet, d.complemento, d.paterno, d.materno, d.nombre		
                                                    order by a.esactivo

                                        ) abc
                            ) xyz
                            where xyz.aux like '%*%'";
                $stmt = $db->prepare($query);
                $params = array();
                $stmt->execute($params);
                $po2 = $stmt->fetchAll();
                //dump($po2);
                //die;
                return $this->render('SieUsuariosBundle:Listas:listausuariosincludeavanzado.html.twig', array(                    
                    'po' => $po2,
                    'titulo' => 'Listado de usuarios con jurisdiccion a nivel nacionales.',
                ));
    }

    public function usuariodeptolistaAction(Request $request) {   
        
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //$sesion->set('urlaux','sie_usuarios_depto_lista');
        $em = $this->getDoctrine()->getManager();
        $ieentity = $em->getRepository('SieAppWebBundle:Usuario')->getLugarNivelUsuarios('1');     
        return $this->render('SieUsuariosBundle:Listas:listausuarios.html.twig', array(            
            'personas' => $ieentity,
            'titulo' => 'Listado de usuarios con roles departamentales.',
        ));
    }
        
    public function usuariodislistaAction(Request $request) {      
        
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $ieentity = $em->getRepository('SieAppWebBundle:Usuario')->getLugarNivelUsuarios('7');     
        return $this->render('SieUsuariosBundle:Listas:listausuarios.html.twig', array(            
            'personas' => $ieentity,
            'titulo' => 'Listado de usuarios con roles departamentales.',
            'urlaux' => 'sie_usuarios_dis_lista',
        ));
    }
    
    public function usuariodepdislistaAction(Request $request) {      
        
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();            
        $query = "
                    select 
                            a.id as usuarioid, a.username, a.esactivo as usuarioesactivo,                    
                            d.id as personaid, d.carnet, d.complemento, d.paterno, d.materno, d.nombre,		 
                            b.id as usuario_rol_id,	
                            c.id as rol_ids,
                            c.rol as roles,
                            b.esactivo as roles_estado,
                            e.id as jurisdiccin_lugar_id,		
                            e.codigo as jurisdiccion_codigo,
                            e.codigo || '-' || e.lugar as jurisdiccion,		
                            f.id as cobertura_cod,		
                            f.nivel as cobertura,
                            b.sub_sistema as subsistema
                    from usuario a 
                            inner join usuario_rol b on a.id = b.usuario_id
                            inner join rol_tipo c on b.rol_tipo_id = c.id
                            inner join lugar_tipo e on b.lugar_tipo_id = e.id                                                
                            inner join lugar_nivel_tipo f on e.lugar_nivel_id = f.id
                            inner join persona d on a.persona_id = d.id
                    where e.lugar_tipo_id = ".$sesion->get('dep_id')."
                    and c.id = 10 
                    and b.esactivo is true
                            
                    order by e.codigo, d.paterno, d.materno, d.nombre";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po2 = $stmt->fetchAll();
        //$ieentity = $em->getRepository('SieAppWebBundle:Usuario')->getDisByDep($sesion->get('dep_id'));     
        //dump($po2);die;
        return $this->render('SieUsuariosBundle:Listas:listausuarios.html.twig', array(            
            'po' => $po2,
            'titulo' => 'Listado de usuarios con roles distritales.',
            'urlaux' => 'sie_usuarios_dis_lista',
        ));
    }

    public function usuariodepdislistaobsAction(Request $request) {      
        
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();            
        $query = "
                        select * from (
                                select  
                                        e.id as jurisdiccin_lugar_id,		
                                        e.codigo as jurisdiccion_codigo,
                                        e.lugar as jurisdiccion,		
                                        count(a.id)
                                from usuario a 
                                        inner join usuario_rol b on a.id = b.usuario_id
                                        inner join rol_tipo c on b.rol_tipo_id = c.id
                                        inner join lugar_tipo e on b.lugar_tipo_id = e.id                                                
                                        inner join lugar_nivel_tipo f on e.lugar_nivel_id = f.id
                                        inner join persona d on a.persona_id = d.id
                                where c.id = 10 
                                and b.esactivo is true
                        group by e.id, e.codigo, e.lugar) abc
                        where abc.count > 1
                        order by abc.jurisdiccion_codigo";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po2 = $stmt->fetchAll();
        //dump($po2);die;
        return $this->render('SieUsuariosBundle:Listas:distritoobs.html.twig', array(            
            'po' => $po2,
        ));
    }
    
    public function usuariosobsdistritoAction(Request $request, $idlugar)  {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();            
        $query = "
                                    select 
                                            a.id as usuarioid, a.username, a.esactivo as usuarioesactivo,                    
                                            d.id as personaid, d.carnet, d.complemento, d.paterno, d.materno, d.nombre,	
                                            e.id as jurisdiccin_lugar_id,		
                                            e.codigo as jurisdiccion_codigo,
                                            e.lugar as jurisdiccion
                                    from usuario a 
                                            inner join usuario_rol b on a.id = b.usuario_id
                                            inner join rol_tipo c on b.rol_tipo_id = c.id
                                            inner join lugar_tipo e on b.lugar_tipo_id = e.id                                                
                                            inner join lugar_nivel_tipo f on e.lugar_nivel_id = f.id
                                            inner join persona d on a.persona_id = d.id
                                    where c.id = 10 
                                        and b.esactivo is true
                                        and e.id = ".$idlugar."
                                    order by e.id";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po2 = $stmt->fetchAll();
        //dump($po2);die;
        return $this->render('SieUsuariosBundle:Listas:usuariosdistritoobs.html.twig', array(            
            'po' => $po2,
        ));
    }

    public function iecursosAction()  {
        $em = $this->getDoctrine()->getManager();        
        $query = $em->createQuery("SELECT b.turno as turnoTipo, c.nivel as nivelTipo, d.grado as gradoTipo, e.paralelo as paraleloTipo,
                                    c.id as nivelId, d.id as gradoId, b.id as turnoId, e.id as paraleloId
                                    FROM SieAppWebBundle:InstitucioneducativaCurso a join a.turnoTipo b join a.nivelTipo c join a.gradoTipo d join a.paraleloTipo e
                                    WHERE a.institucioneducativa = '".$this->session->get('ie_id')."' and a.gestionTipo = '2017'
                                    GROUP BY b.id, c.id, d.id, e.id ORDER BY b.id, c.id, d.id, e.id");
        $cursos = $query->getResult();
        if (!$cursos) {
            $this->session->getFlashBag()->add('error', 'No se encontro cursos asociadas a la institución educativa presente gestión.');
            
            return $this->render('SieUsuariosBundle:Listas:iecursos.html.twig', array(
                'cursos'   => $cursos,
                'gestion'   => '2017',
                'apoderados' => array(),
            ));
        }
        
        $apoderados = $this->apoderadosienivelresult($this->session->get('ie_id'),'2017',$cursos[0]['nivelId'],$cursos[0]['gradoId'],$cursos[0]['turnoId'],$cursos[0]['paraleloId']);
        if (!$apoderados) {
            //$this->session->getFlashBag()->add('error', 'No se encontro apoderados asociados al curso seleccionado. Verifique que se haya hecho la asignación de hijos correspondiente.');
            $sw = 'false';                        
            return $this->render('SieUsuariosBundle:Listas:iecursos.html.twig', array(
                'cursos'   => $cursos,
                'gestion'   => '2017',
                'apoderados' => $apoderados,
                'sw' => $sw,
            ));
        }
        $sw = 'true';                        
        return $this->render('SieUsuariosBundle:Listas:iecursos.html.twig', array(
            'cursos'   => $cursos,
            'gestion'   => '2017',
            'personas' => $apoderados,
            'sw' => $sw,
        ));
    }
    
    public function personasieAction() {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();            
        $query = "
            SELECT 
              usuario.id as usuarioid, 
              persona.id as personaid,
              persona.carnet,
              persona.complemento, 
              persona.nombre, 
              persona.paterno, 
              persona.materno, 
              persona.count_edit,              
              usuario.username,
              usuario.esactivo as usuarioesactivo,
              array_agg(cast(cargo_tipo.cargo as varchar)) as cargo
            FROM 
              maestro_inscripcion INNER JOIN persona
                    ON persona.id = maestro_inscripcion.persona_id  
              INNER JOIN public.cargo_tipo
                    ON maestro_inscripcion.cargo_tipo_id = cargo_tipo.id
              LEFT JOIN public.usuario
                    ON usuario.persona_id = persona.id  
            WHERE    
              (maestro_inscripcion.institucioneducativa_id = '".$this->session->get('ie_id')."') and 
              (maestro_inscripcion.gestion_tipo_id = '2017') and
              ((maestro_inscripcion.cargo_tipo_id = 1) or (maestro_inscripcion.cargo_tipo_id = 12) or (maestro_inscripcion.cargo_tipo_id = 0))
            GROUP BY
	      usuario.id, 
	      persona.id, 
              persona.carnet, 
              persona.nombre, 
              persona.paterno, 
              persona.materno, 
              persona.count_edit,              
              usuario.username,
              usuario.esactivo
            ORDER BY persona.nombre, persona.paterno, persona.materno ";

        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
//        dump($po);
//        die;
        if (!$po) {
            $this->session->getFlashBag()->add('error', 'No se encontro administrativos con usuarios asociadas a la institucion educativa en la presente gestión.');
            //RENDERIZADO        
            return $this->render('SieUsuariosBundle:Listas:personasie.html.twig', array(
                'depid' => '',
                'disid' => '',
                'personas' => $po,
            ));
        }        
    
        return $this->render('SieUsuariosBundle:Listas:personasie.html.twig', array(
            'depid' => $this->session->get('dep_id'),
            'disid' => $this->session->get('dis_id'),
            'personas' => $po,
        ));
    }
    
    //LISTA DE ALUMNOS POR NIVEL
    private function apoderadosienivelresult($ie,$gestion,$nivel,$grado,$turno,$paralelo) {
        $em = $this->getDoctrine()->getManager();
        $qr = "SELECT usuario.id as usuarioid, persona.id as personaid, usuario.username, usuario.esactivo, 
                    persona.carnet, persona.nombre, persona.paterno, persona.materno, persona.fecha_nacimiento, 
                    persona.esvigente as vigente, persona.count_edit, apoderado_tipo.apoderado, nivel_tipo.nivel, 
                    grado_tipo.grado, turno_tipo.turno, paralelo_tipo.paralelo 
                    FROM estudiante_inscripcion 
                    INNER JOIN apoderado_inscripcion ON apoderado_inscripcion.estudiante_inscripcion_id = estudiante_inscripcion.id
                    INNER JOIN apoderado_tipo on apoderado_inscripcion.apoderado_tipo_id = apoderado_tipo.id
                    INNER JOIN persona on persona.id = apoderado_inscripcion.persona_id
                    INNER JOIN institucioneducativa_curso ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id 
                    INNER JOIN nivel_tipo ON nivel_tipo.id = institucioneducativa_curso.nivel_tipo_id 
                    INNER JOIN grado_tipo ON grado_tipo.id = institucioneducativa_curso.grado_tipo_id 
                    INNER JOIN turno_tipo ON turno_tipo.id = institucioneducativa_curso.turno_tipo_id 
                    INNER JOIN paralelo_tipo ON paralelo_tipo.id = institucioneducativa_curso.paralelo_tipo_id 
                    LEFT JOIN usuario ON usuario.persona_id = persona.id 
                    WHERE    
                    (institucioneducativa_curso.institucioneducativa_id = '".$ie."') and
                    (institucioneducativa_curso.gestion_tipo_id = '".$gestion."') and
                    (institucioneducativa_curso.nivel_tipo_id = '".$nivel."') and
                    (institucioneducativa_curso.grado_tipo_id = '".$grado."') and
                    (institucioneducativa_curso.turno_tipo_id = '".$turno."') and
                    (institucioneducativa_curso.paralelo_tipo_id = '".$paralelo."') and
                    (apoderado_inscripcion.es_validado = 2)
                    GROUP BY persona.carnet, persona.nombre, persona.paterno, persona.materno, usuario.username, usuario.esactivo, usuario.id, persona.id, apoderado_tipo.apoderado, nivel_tipo.nivel, grado_tipo.grado, turno_tipo.turno, paralelo_tipo.paralelo ORDER BY persona.nombre, persona.paterno, persona.materno
            ";

        //print_r($qr);
        //die;        
  
        $query = $em->getConnection()->prepare($qr);
        $query->execute();
        $filas = $query->fetchAll();
        return $filas;        
    }
    
    public function apoderadosiegenerarAction() {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();            
        $query = "
                SELECT DISTINCT institucioneducativa_id
                FROM institucioneducativa_operativo_log
                WHERE institucioneducativa_operativo_log_tipo_id = 4
                AND gestion_tipo_id = 2017
                AND institucioneducativa_id = '".$this->session->get('ie_id')."' ";

        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
//        dump($po);
//        die;
        $countapo = '0';
        if (count($po) == 1) {
            $query = "
                SELECT 
                k.id as personaid,	            
                k.carnet,
                k.complemento,
                z.id as usuarioid
                        FROM estudiante_inscripcion b
                INNER JOIN estudiante h on h.id = b.estudiante_id
                        INNER JOIN institucioneducativa_curso ON b.institucioneducativa_curso_id = institucioneducativa_curso.id 
                        INNER JOIN apoderado_inscripcion ON apoderado_inscripcion.estudiante_inscripcion_id = b.id
                        INNER JOIN apoderado_tipo on apoderado_inscripcion.apoderado_tipo_id = apoderado_tipo.id
                        INNER JOIN persona k on k.id = apoderado_inscripcion.persona_id
                        LEFT JOIN usuario z ON z.persona_id = k.id 

                        WHERE (institucioneducativa_curso.institucioneducativa_id = '".$this->session->get('ie_id')."') 
                        and (institucioneducativa_curso.gestion_tipo_id = '2017') 
                        and k.segip_id in (1,2,3,4,5,6,7,8)
                        and z.username is null
                        
                        GROUP BY 
                z.id, z.username, z.esactivo,
                        k.id, k.carnet, k.nombre, k.paterno, k.materno
                        
                        ORDER BY k.carnet ";
            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $po = $stmt->fetchAll();
            //dump($po);die();
            $countapo = count($po);
            if ($countapo > 0) {
                $query = $em->getConnection()->prepare('SELECT * from sp_genera_usuarios_apoderados_sie(:ie::VARCHAR, :gestion ::VARCHAR)');
                $query->bindValue(':ie', $this->session->get('ie_id'));
                $query->bindValue(':gestion', '2017');       
                $query->execute();
            }
            $this->session->getFlashBag()->add('apoderadosgeneracion',$countapo);
        }else{
            $this->session->getFlashBag()->add('apoderadosgeneracionerror','La institución educativa aún no cerro operativo Rude.');
        }
        return $this->redirect($this->generateURL('sie_usuarios_apoderados_ie'));     
    }
    
    public function apoderadosienivelAction($gestion,$nivel,$grado,$turno,$paralelo) {
        $apoderados = $this->apoderadosienivelresult($this->session->get('ie_id'),$gestion,$nivel,$grado,$turno,$paralelo);
        if (!$apoderados) {            
            $sw = 'false';
            //$this->session->getFlashBag()->add('error', 'No se encontro apoderados asociados al curso seleccionado. Verifique que se haya hecho la asignación de hijos correspondiente.');
            return $this->render('SieUsuariosBundle:Listas:apoderadosienivel.html.twig', array(
                'personas' => $apoderados,
                'sw' => $sw,
            ));
        }
        $sw = 'true';
        return $this->render('SieUsuariosBundle:Listas:apoderadosienivel.html.twig', array(
            'personas' => $apoderados,
            'sw' => $sw,
        ));
    }
   
    //DISTRITO LISTAS DE DIRECTORES CON SUS UNIDADES EDUCATIVAS
    public function iedistritoAction()
    {
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();            
        $query = "
                    select            
                        d.id as cod_ue,		
                        d.institucioneducativa
                    
                    from jurisdiccion_geografica a 
                            inner join (
                                    select id,codigo as cod_dis,lugar as des_dis 
                                            from lugar_tipo
                                    where lugar_nivel_id=7 
                            ) b 	
                            on a.lugar_tipo_id_distrito = b.id
                            inner join (
                                    select a.id, a.institucioneducativa, a.le_juridicciongeografica_id
                                    from institucioneducativa a
                            ) d		
                            on a.id=d.le_juridicciongeografica_id
                            
                    where 
                        b.cod_dis = '".$this->session->get('dis_cod')."'             
                    order by d.id
             ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        //$this->session->set('rol_ids','9');
        return $this->render('SieUsuariosBundle:Listas:disiedir.html.twig', array(
                'entities' => $po,
            ));
    }
    
}

