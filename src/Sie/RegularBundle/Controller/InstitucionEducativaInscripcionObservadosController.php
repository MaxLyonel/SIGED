<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;

/**
 * InstitucionEducativaInformacion controller.
 *
 */
class InstitucionEducativaInscripcionObservadosController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Formulario de Busqueda InstitucionEducativaInformacion.
     *
     */
    public function indexAction(Request $request) {
        //$userInfo  = $this->session->get('aUserInfologged');
        $id_usuario = $this->session->get('userId');
        $roliduser = $this->session->get('roluser');

        $em = $this->getDoctrine()->getManager();
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (($roliduser == '7') || ($roliduser == '8')) {
            return $this->render('SieRegularBundle:InstitucionEducativaInscripcionObservados:index.html.twig', array(
                   'form' => $this->creaFormularioBuscador('sie_ue_inscripcion_observados_busqueda', '', 0)->createView(),
            ));
        } 
            
        if ($roliduser == '10') {
            $rolesusuario = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario' => $id_usuario,'rolTipo' => '10')); 
            $lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->find($rolesusuario[0]->getLugarTipo()->getId());
            return $this->redirectToRoute('sie_ue_inscripcion_observados_busqueda_dis', array('dis' => $lugar->getCodigo()));
        } 
        
//        print_r($this->session->get('userName'));
//        die;
        
        if ($roliduser == '9') 
            {
            //{{ app.session.get('lastname') }}
            return $this->redirectToRoute('sie_ue_inscripcion_observados_busqueda_ie', array('ie' => $this->session->get('userName') ));            
            }
            
//            if (isset($userInfo[0]['id']))
//                {
//                return $this->redirectToRoute('sie_ue_inscripcion_observados_busqueda_ie', array('ie' => $userInfo[0]['id']));
//                }
//            else
//                {
//                return $this->redirectToRoute('sie_ue_inscripcion_observados_busqueda_ie', array('ie' => $userInfo[0]['id']));
//                }
//            }
    }

    private function creaFormularioBuscador($routing, $value1, $identificador) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('value' => $value1, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }

    public function buscainscripcionobservadosAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        // generar los titulos para los diferentes sistemas
        $rolUsuario = $this->session->get('roluser');

        $sie = 0;
        $identificador = 0;

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();        
        
        $form = $request->get('form');

        if ($form) {
            $sie = $form['sie'];
            
            $identificador = $form['identificador'];

            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
            $query->bindValue(':user_id', $id_usuario);
            $query->bindValue(':sie', $sie);
            $query->bindValue(':roluser', $rolUsuario);
            $query->execute();
            $aTuicion = $query->fetchAll();

            if (!$aTuicion[0]['get_ue_tuicion']) {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No tiene tuición sobre la Unidad Educativa'));
                return $this->redirectToRoute('sie_ue_informacion_observados');
            }

            $query = $em->getConnection()->prepare("
                            select c.institucioneducativa_id, b.paterno, b.materno, b.nombre, b.codigo_rude, o.obs, o.gestion_tipo_id, observacion_inscripcion_tipo_id, m.observacion, m.id
                            from institucioneducativa_curso c 					
				inner join estudiante_inscripcion a 
					on a.institucioneducativa_curso_id = c.id
				inner join estudiante b 
					on b.id = a.estudiante_id	
				inner join estudiante_inscripcion_observacion o 
					on o.estudiante_inscripcion_id = a.id
				inner join observacion_inscripcion_tipo m
					on o.observacion_inscripcion_tipo_id = m.id
                            where c.institucioneducativa_id = '" . $sie . "' and o.esactivo = true
                            order by m.peso, b.nombre, b.paterno, b.materno
                            ");

            $query->execute();
            $eoEntity = $query->fetchAll();
            
            $ieentity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
            return $this->render('SieRegularBundle:InstitucionEducativaInscripcionObservados:result.html.twig', array(
                        'sie' => $sie,
                        'uedesc' => $ieentity->getInstitucionEducativa(),
                        'eoEntity' => $eoEntity,
            ));
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => '¡Proceso Detenido! Se ha detectado inconsistencia de datos.'));
            return $this->redirectToRoute('sie_ue_informacion_observados');
        }
    }

    public function buscainscripcionobservadosieAction($ie) {
        //$userInfo  = $this->session->get('aUserInfologged');
        
        $id_usuario = $this->session->get('userId');
        
        $roliduser = $this->session->get('roluser');

        $em = $this->getDoctrine()->getManager();
        
        if (($roliduser == '7') || ($roliduser == '8')) {
            //validation if the user is logged
            if (!isset($id_usuario)) {
                return $this->redirect($this->generateUrl('login'));
            }

            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
            $query->bindValue(':user_id', $id_usuario);
            $query->bindValue(':sie', $ie);
            $query->bindValue(':roluser', $rolUsuario);
            $query->execute();
            $aTuicion = $query->fetchAll();

            if (!$aTuicion[0]['get_ue_tuicion']) {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No tiene tuición sobre la Unidad Educativa'));
                return $this->redirectToRoute('sie_ue_informacion_observados');
            }
        }
        
        if ($roliduser == '9') {           
           //$descue = $userInfo[0]['institucioneducativa']; 
           $entity = $this->getDoctrine()->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('userName'));
           $descue = $entity->getInstitucioneducativa(); 
        }
        
        if ($roliduser == '10') {
           $descue = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie); 
        }
        
        $query = $em->getConnection()->prepare("
                            select c.institucioneducativa_id, b.paterno, b.materno, b.nombre, b.codigo_rude, o.obs, o.gestion_tipo_id, observacion_inscripcion_tipo_id, m.observacion, m.id
                            from institucioneducativa_curso c 					
				inner join estudiante_inscripcion a 
					on a.institucioneducativa_curso_id = c.id
				inner join estudiante b 
					on b.id = a.estudiante_id	
				inner join estudiante_inscripcion_observacion o 
					on o.estudiante_inscripcion_id = a.id
				inner join observacion_inscripcion_tipo m
					on o.observacion_inscripcion_tipo_id = m.id
                            where c.institucioneducativa_id = '" . $ie . "' and o.esactivo = true
                            order by m.peso, b.nombre, b.paterno, b.materno
                            ");

        $query->execute();
        $eoEntity = $query->fetchAll();

        
        return $this->render('SieRegularBundle:InstitucionEducativaInscripcionObservados:result.html.twig', array(
                    'sie' => $ie,
                    'uedesc' => $descue,
                    'eoEntity' => $eoEntity,
        ));
    }
    
    public function buscainscripcionobservadosdisAction($dis) {
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();


        $query = $em->getConnection()->prepare("
                                select  d.id as cod_ue,		
                                    d.institucioneducativa,
                                    case substring(cast(b.cod_dis as varchar(4)),1,1) 
                                                    when '1' then 'CHUQUISACA' 
                                                    when '2' then 'LA PAZ' 
                                                    when '3' then 'COCHABAMBA' 
                                                    when '4' then 'ORURO' 
                                                    when '5' then 'PÓTOSI'
                                                    when '6' then 'TARIJA' 
                                                    when '7' then 'SANTA CRUZ' 
                                                    when '8' then 'BENI' 
                                                    when '9' then 'PANDO' end
                                    as depto,		
                                    b.cod_dis,
                                    b.des_dis

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
                                                            inner join (
                                                                    select distinct institucioneducativa_id 
                                                                    from institucioneducativa_curso
                                                                    inner join estudiante_inscripcion z on z.institucioneducativa_curso_id = institucioneducativa_curso.id
                                                                    inner join estudiante_inscripcion_observacion x on x.estudiante_inscripcion_id = z.id
                                                                    where nivel_tipo_id in (11,12,13)
                                                            ) b 
                                                    on a.id = b.institucioneducativa_id 
                                            ) d		
                                            on a.id=d.le_juridicciongeografica_id 

                                    where b.cod_dis = '".$dis."'
                            ");

        $query->execute();
        $eoEntity = $query->fetchAll();

//        print_r($eoEntity[0]);
//        die;
        
        return $this->render('SieRegularBundle:InstitucionEducativaInscripcionObservados:iesobservados.html.twig', array(
                    'eoEntity' => $eoEntity,
                    'discod' => $eoEntity[0]['cod_dis'],
                    'disdesc' => $eoEntity[0]['des_dis'],
                    'depto' => $eoEntity[0]['depto'],
        ));
    }
    
    public function buscainscripcionvalidarAction($rude, $inscripcionid) {
        $id_usuario = $this->session->get('userId');        
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT * from sp_corrije_inscripcion_estudiante_observaciones(:rude::VARCHAR, :inscripcionid ::VARCHAR)');
        $query->bindValue(':rude', $rude);
        $query->bindValue(':inscripcionid', $inscripcionid);
        $query->execute();

        return $this->redirectToRoute('history_inscription_quest', array('rude' => $rude));

    }

}
