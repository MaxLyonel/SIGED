<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;

/**
 * PlataformaMoodleController.
 *
 */
class PlataformaMoodleController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
        $this->client = new Client([
            'base_uri' => 'http://190.129.122.22:5000',
        ]);
    }

    public function indexAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        // Validamos si el usuario ha iniciado sesión
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la sesión
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieRegularBundle:PlataformaMoodle:index.html.twig');
    }

    public function getDepartamentosAction(Request $request){
        $response = new JsonResponse();

        $subsistema = $request->get('subsistema');
        $url = 'plataforma/getDepartamentos/'.$subsistema;

        $result = $this->client->request(
            'GET', 
            $url,
            ['headers' => ['Accept' => 'application/json'],
            ['debug' => true]])->getBody()->getContents();

        $resultDecode = json_decode($result, true);

        $response->setStatusCode(200);
        $response->setData($resultDecode);
        
        return $response;
    }

    public function getDistritosAction(Request $request){
        $response = new JsonResponse();

        $subsistema = $request->get('subsistema');
        $departamento = $request->get('departamento');

        $url = 'plataforma/getDistritos/'.$subsistema.'/'.$departamento;

        $result = $this->client->request(
            'GET', 
            $url,
            ['headers' => ['Accept' => 'application/json'],
            ['debug' => true]])->getBody()->getContents();

        $resultDecode = json_decode($result, true);

        $response->setStatusCode(200);
        $response->setData($resultDecode);
        
        return $response;
    }

    public function verificarUsuarioAction(Request $request){
        $response = new JsonResponse();

        $subsistema = $request->get('subsistema');
        $departamento = $request->get('departamento_id');
        $distrito = $request->get('distrito_id');
        $usuario = $request->get('username');

        $url = 'plataforma/verificarUsuarioExiste';
        $parametros = array(
            'form_params' => array(
                'subsistema' => $subsistema,
                'departamento_id' => $departamento,
                'distrito_id' => $distrito,
                'username' => $usuario
            )
        );

        $result = $this->client->request(
            'POST', 
            $url,
            $parametros,
            ['headers' => ['Accept' => 'application/json'],
            ['debug' => true]])->getBody()->getContents();

        $resultDecode = json_decode($result, true);

        $response->setStatusCode(200);
        $response->setData($resultDecode);
        
        return $response;
    }

    public function actualizarContraseniaAction(Request $request){
        $response = new JsonResponse();

        $subsistema = $request->get('subsistema');
        $departamento = $request->get('departamento_id');
        $distrito = $request->get('distrito_id');
        $usuario = $request->get('username');

        $url = 'plataforma/actualizarContrasenia';
        $parametros = array(
            'form_params' => array(
                'subsistema' => $subsistema,
                'departamento_id' => $departamento,
                'distrito_id' => $distrito,
                'username' => $usuario
            )
        );

        $result = $this->client->request(
            'POST', 
            $url,
            $parametros,
            ['headers' => ['Accept' => 'application/json'],
            ['debug' => true]])->getBody()->getContents();

        $resultDecode = json_decode($result, true);

        $response->setStatusCode(200);
        $response->setData($resultDecode);
        
        return $response;
    }
}