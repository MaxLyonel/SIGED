<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Sie\AppWebBundle\Entity\LugarTipo;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

class LugarTipoController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }
    /**
     * Listado de provincias
     * @param Request $request
     * @return array
     */
    public function provincias2012Action(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->provincias2012($request->get('departamento'));
        $response = new JsonResponse();
        return $response->setData(array('provincia' => $provincia));
    }
    /**
     * Listado de municipios
     * @param Request $request
     * @return array
     */
    public function municipios2012Action(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->municipios2012($request->get('provincia'));
        $response = new JsonResponse();
        return $response->setData(array('municipio' => $municipio));
    }
    /**
     * Listado de comunidades
     * @param Request $request
     * @return array
     */
    public function comunidades2012Action(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->comunidades2012($request->get('municipio'));
        $response = new JsonResponse();
        return $response->setData(array('comunidad' => $comunidad));
    }
    /**
     * Listado de provincias
     * @param Request $request
     * @return array
     */
    public function localidades2012Action(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $localidad = $em->getRepository('SieAppWebBundle:LugarTipo')->localidades2012($request->get('comunidad'));
        $response = new JsonResponse();
        return $response->setData(array('localidad' => $localidad));
    }
}
