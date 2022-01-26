<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\BonojuancitoInstitucioneducativa;

/**
 * Validacion Controller.
 */
class ValidacionInstitucionEducativaController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * Muestra el listado de Procesos de Validación
     */
    public function listadoAction(Request $request) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        
        $gestionActual = date_format($fechaActual,'Y');

//        $rol_usuario = $this->session->get('roluser');
//        if ($rol_usuario != '8') {
//            return $this->redirect($this->generateUrl('principal_web'));
//        }

        $usuario_id = $this->session->get('userId');

        $em = $this->getDoctrine()->getManager();

        $usuario_rol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneByUsuario($usuario_id);
        $lugar_tipo = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($usuario_rol->getLugarTipo());

        $em = $this->getDoctrine()->getManager();

        /*
         * listado de unidades educativas por distrito  comomparado entre la geston actual y anterior
         */
        $query = $em->getConnection()->prepare("
                select bjpie1.institucioneducativa_id, bjpie1.institucioneducativa, bjpie1.gestion_anterior, bjpie1.gestion_actual, coalesce(bjpie2.cantidad_estudiantes,0) as cantidad_estudiantes, bjpie2.institucioneducativa_id_nueva, bjpie2.obs,  bjpiet.id as validacion_id, bjpiet.bonojuancito_institucioneducativa as validacion, bjpie2.tipo as tipo from (
                select bjpie.institucioneducativa_id, ie.institucioneducativa, SUM(case when bjpie.gestion_tipo_id = ((:igestion::INT)-1) and esactivo = 't' then 1 else 0 end) as gestion_anterior, SUM(case when  bjpie.gestion_tipo_id = :igestion::INT and esactivo = 't' then 1 else 0 end) as gestion_actual 
                from bonojuancito_institucioneducativa as bjpie
                inner join institucioneducativa as ie on ie.id = bjpie.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) lt on lt.id = jg.lugar_tipo_id_distrito
                where lt.id = :icoddis
                group by bjpie.institucioneducativa_id, ie.institucioneducativa
                ) as bjpie1
                left join (select * from bonojuancito_institucioneducativa where gestion_tipo_id = :igestion::INT) as bjpie2 on bjpie1.institucioneducativa_id = bjpie2.institucioneducativa_id
                left join bonojuancito_institucioneducativa_tipo as bjpiet on bjpiet.id = bjpie2.bonojuancito_institucioneducativa_tipo_id
                order by bjpie1.institucioneducativa_id
            ");
        $query->bindValue(":icoddis", $lugar_tipo->getId());
        $query->bindValue(":igestion", $gestionActual);
        $query->execute();
        $resultado = $query->fetchAll();


        /*
         * listado de observaciones para validacion
         */
        $query = $em->getConnection()->prepare("
                select * from bonojuancito_institucioneducativa_tipo
            ");
        $query->execute();
        $resultadoValidacionTipo = $query->fetchAll();

        return $this->render('SieAppWebBundle:ValidacionInstitucionEducativa:index.html.twig', array(
                    'entityUE' => $resultado,
                    'entityValidacion' => $resultadoValidacionTipo,
                    'gestionActual' => $gestionActual,
                    'gestionAnterior' => ($gestionActual - 1),
        ));
    }

    public function listadoSaveAction(Request $request) {
        if ($request->isMethod('POST')) {
            $gestion = $request->get('gestion');
            $sie = $request->get('sie');
            $cantidad = $request->get('cantidad');
            $obs = $request->get('obs');
            $tipo = $request->get('tipo');
            $sienuevo = $request->get('sienuevo');
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {               
                
                $entityBjpInstitucionEducativa = $em->getRepository('SieAppWebBundle:BonojuancitoInstitucioneducativa')->findOneBy(array('institucioneducativa' => $sie, 'gestionTipoId' => $gestion));
                $entityBjpInstitucionEducativaTipo = $em->getRepository('SieAppWebBundle:BonojuancitoInstitucioneducativaTipo')->findOneBy(array('id' => $tipo));

                /*
                 * Se registra la validacion realizada
                 */
                if(!$entityBjpInstitucionEducativa){
                    $entityBjpInstitucionEducativa = new BonojuancitoInstitucioneducativa();                    
                    $entityInstitucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $sie));
                    $entityBjpInstitucionEducativa->setInstitucioneducativa($entityInstitucionEducativa);
                    $entityBjpInstitucionEducativa->setGestionTipoId($gestion);                    
                }

                $entityBjpInstitucionEducativa->setCantidadEstudiantes($cantidad);
                if($tipo == 3){
                    $entityBjpInstitucionEducativa->setInstitucioneducativaIdNueva($sienuevo);
                } 
                $entityBjpInstitucionEducativa->setObs($obs);
                $entityBjpInstitucionEducativa->setBonojuancitoInstitucioneducativaTipo($entityBjpInstitucionEducativaTipo);
                if($tipo == 1 or $tipo == 3){
                    $entityBjpInstitucionEducativa->setEsactivo('0');
                } else {
                    $entityBjpInstitucionEducativa->setEsactivo('1');
                }
                $em->persist($entityBjpInstitucionEducativa);
                $em->flush();   
                                
                $em->getConnection()->commit();
                $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Registro guardado exitosamente'));
            } catch (Exception $e) {
                $msg = "Dificultades al ingresar registro, intente nuevamente"; 
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg)); 
            }
        } else {
            $msg = "Datos no validos, intente nuevamente"; 
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));  
        }

        return $this->redirectToRoute('sie_app_bjp_validacion_ie_lista_paso_1');
                
        //dump($resultado);die;
    }

    public function impresionListaUEPdfAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $em = $this->getDoctrine()->getManager();
        $gestionActual = date_format($fechaActual,'Y');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        /*
         * listado de unidades educativas por distrito  comomparado entre la geston actual y anterior
         */
        /*
        $query = $em->getConnection()->prepare("
                select bjpie1.institucioneducativa_id, bjpie1.institucioneducativa, bjpie1.gestion_anterior, bjpie1.gestion_actual, coalesce(bjpie2.cantidad_estudiantes,0) as cantidad_estudiantes, bjpie2.institucioneducativa_id_nueva, bjpie2.obs,  bjpiet.id as validacion_id, bjpiet.bonojuancito_institucioneducativa as validacion from (
                select bjpie.institucioneducativa_id, ie.institucioneducativa, SUM(case when bjpie.gestion_tipo_id = ((:igestion::INT)-1) and esactivo = 't' then 1 else 0 end) as gestion_anterior, SUM(case when  bjpie.gestion_tipo_id = :igestion::INT and esactivo = 't' then 1 else 0 end) as gestion_actual 
                from bonojuancito_institucioneducativa as bjpie
                inner join institucioneducativa as ie on ie.id = bjpie.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) lt on lt.id = jg.lugar_tipo_id_distrito
                where lt.id = :icoddis
                group by bjpie.institucioneducativa_id, ie.institucioneducativa
                ) as bjpie1
                left join (select * from bonojuancito_institucioneducativa where gestion_tipo_id = :igestion::INT) as bjpie2 on bjpie1.institucioneducativa_id = bjpie2.institucioneducativa_id
                left join bonojuancito_institucioneducativa_tipo as bjpiet on bjpiet.id = bjpie2.bonojuancito_institucioneducativa_tipo_id
                order by bjpie1.institucioneducativa_id
            ");
        $query->bindValue(":icoddis", $lugar_tipo->getId());
        $query->bindValue(":igestion", $gestionActual);
        $query->execute();
        $resultado = $query->fetchAll();
        */
        $usuario_rol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneByUsuario($id_usuario);
        $lugar_tipo = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($usuario_rol->getLugarTipo());
        $coddis = $lugar_tipo->getId();
        $arch = $coddis.'_'.$gestionActual.'_DIPLOMA_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents('http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/reg_dj_UnidadesEducativasValidadas_distrito_v1_rcm.rptdesign&__format=pdf&coddis='.$coddis.'&codges='.$gestionActual));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;    
    }

}
