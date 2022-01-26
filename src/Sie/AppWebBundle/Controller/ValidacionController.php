<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Validacion Controller.
 */
class ValidacionController extends Controller {

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
    public function indexAction(Request $request) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

//        $rol_usuario = $this->session->get('roluser');
//        if ($rol_usuario != '8') {
//            return $this->redirect($this->generateUrl('principal_web'));
//        }

        $usuario_id = $this->session->get('userId');

        $em = $this->getDoctrine()->getManager();

        $usuario_rol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneByUsuario($usuario_id);
        $lugar_tipo = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($usuario_rol->getLugarTipo());

        $repository = $em->getRepository('SieAppWebBundle:ValidacionProceso');

        $query = $repository->createQueryBuilder('vp')
                ->select('vp.institucionEducativaId')
                ->where('vp.esActivo = :activo')
                ->setParameter('activo', 't')
                ->distinct()
                ->getQuery();

        $validacionProceso = $query->getResult();

        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

        $query = $repository->createQueryBuilder('ins')
                ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'jg', 'WITH', 'ins.leJuridicciongeografica = jg.id')
                ->where('ins.id in (:ieducativa)')
                ->andWhere('jg.distritoTipo = :distrito')
                ->setParameter('ieducativa', $validacionProceso)
                ->setParameter('distrito', $lugar_tipo->getCodigo())
                ->getQuery();

        $ieducativa = $query->getResult();

        return $this->render('SieAppWebBundle:Validacion:index.html.twig', array(
                    'ieducativa' => $ieducativa,
                    'distrito' => $lugar_tipo
        ));
    }

    public function detalleAction(Request $request, $vpId) {
        $em = $this->getDoctrine()->getManager();

        $validacionProceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($vpId);
        $validacionReglaTipo = $em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById($validacionProceso->getValidacionReglaTipo()->getId());

        return $this->render('SieAppWebBundle:Validacion:detalle.html.twig', array(
                    'validacionReglaTipo' => $validacionReglaTipo
        ));
    }

    public function validarTodoAction(Request $request) {

        //$form = 'getForm';
        $gestion = '2016';
        $em = $this->getDoctrine()->getManager();

        /*
         * sp_valida_calidad_curso_oferta_obs1(icodue character varying, igestion character varying)
         */
        $query = $em->getConnection()->prepare("SELECT sp_valida_calidad_curso_oferta_obs1(:icodue, :igestion)");
        $query->bindValue(":icodue", "''");
        $query->bindValue(":igestion", $gestion);
        $query->execute();
        $resultado = $query->fetchAll();
        
        dump($resultado);die;
    }

}
