<?php

namespace Sie\AppWebBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use GuzzleHttp\Client;

class Segip {

	protected $em;
    protected $router;
    
	public function __construct(EntityManager $entityManager, Router $router) {
		$this->em = $entityManager;
        $this->router = $router;
        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'http://100.0.100.116',
        ]);

        /*TOKEN DE CONEXIÓN*/
        $this->token = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiI0M2t2ZVlDUmRxWGpFMW5QRHZYVWs1MW05OWEyOTJvQSIsInVzZXIiOiJzaXN0ZW1hc19kZXYiLCJleHAiOjE2MjUzNTAwMjB9.GmV5nakrvPSdrQq3TUAVwATtr4icHGE2Y2bj7w4qwSc';
        /*TOKEN DE CONEXIÓN*/
    }
    
    public function estadoServicio() {

        $response = $this->client->request(
            'GET', 
            'desarrollo/segip/v2/status', 
            ['headers' => ['Accept' => 'application/json', 'Authorization' => $this->token], 
            ['debug' => true]])->getBody()->getContents();

        $responseDecode = json_decode($response, true);

        $response = false;

        if($responseDecode){
            $response = true;
        }

		return $response;
	}

    public function buscarPersona($carnet, $complemento, $fechaNac) {

        $fechaNac = date('d/m/Y', strtotime($fechaNac));

        $url = 'desarrollo/segip/v2/personas/'.$carnet.'?fecha_nacimiento='.$fechaNac;

        if($complemento != ''){
            $url = 'segip/v2/personas/'.$carnet.'?fecha_nacimiento='.$fechaNac.'&complemento='.$complemento;
        }

        $response = $this->client->request(
            'GET', 
            $url, 
            ['headers' => ['Accept' => 'application/json', 'Authorization' => $this->token], 
            ['debug' => true]])->getBody()->getContents();

        $responseDecode = json_decode($response, true);

		return $responseDecode;
	}

    public function verificarPersona($carnet, $complemento, $paterno, $materno, $nombre, $fechaNac) {

        $fechaNac = date('d/m/Y', strtotime($fechaNac));

        $url = 'desarrollo/segip/v2/personas/'.$carnet.'?fecha_nacimiento='.$fechaNac.'&primer_apellido='.$paterno.'&segundo_apellido='.$materno.'&nombre='.$nombre;

        if($complemento != ''){
            $url = 'segip/v2/personas/'.$carnet.'?fecha_nacimiento='.$fechaNac.'&primer_apellido='.$paterno.'&segundo_apellido='.$materno.'&nombre='.$nombre.'&complemento='.$complemento;
        }

        $response = $this->client->request(
            'GET', 
            $url, 
            ['headers' => ['Accept' => 'application/json', 'Authorization' => $this->token], 
            ['debug' => true]])->getBody()->getContents();

        $responseDecode = json_decode($response, true);

        if ($responseDecode['ConsultaDatoPersonaEnJsonResult']['EsValido'] === "true") {
            $persona = $responseDecode['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson'];
            $resultado = true;
        } else {
            $persona = 'null';
            $resultado = false;
        }

        if($persona == 'null') {
            $resultado = false;
        }

        return $resultado;
    }
}