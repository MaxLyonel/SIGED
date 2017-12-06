<?php

namespace Sie\AppWebBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use GuzzleHttp\Client;

class Persona {

	protected $em;
    protected $router;
    
	public function __construct(EntityManager $entityManager, Router $router) {
		$this->em = $entityManager;
		$this->router = $router;
	}

    public function buscarPersona($carnet, $complemento, $extranjero) {

        /***NUEVO TOKEN A LA FECHA 25/09/2017*/
        $token = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6OTI1MDYxNjQsImlhdCI6MTUwODk0NTUyNCwiZXhwIjoxNTQwNDgxNTI0fQ.uPQ2qUoB3NBA2zlOswQ8LLqEtQVGJts5jBHfhh_9kPU';

        /***NUEVO TOKEN A LA FECHA 16/6/2017*/
        

        $client = new Client([
        // Base URI is used with relative requests
        //'base_uri' => 'http://172.20.16.242:8080',
        'base_uri' => 'http://api.sie.gob.bo',
        // You can set any number of default request options.
        //'timeout'  => 3.0,
        ]);

        $response = $client->request('GET', '/persona/getPersonaByCarnetComplementoExtranjero/'.$carnet.'/'.$complemento.'/'.$extranjero.'', ['headers' => ['Accept' => 'application/json', 'Authorization' => $token], ['debug' => true]])->getBody()->getContents();
        $responseDecode = json_decode($response);

        $DatosPersonaDecode = $responseDecode;
        $response = $DatosPersonaDecode;
		return $response;
	}

    public function BuscarPersonaPorCarnetComplementoFechaNacimiento($carnet, $complemento, $fechaNacimiento) {
        /***NUEVO TOKEN A LA FECHA 25/09/2017*/
        $token = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6OTI1MDYxNjQsImlhdCI6MTUwODk0NTUyNCwiZXhwIjoxNTQwNDgxNTI0fQ.uPQ2qUoB3NBA2zlOswQ8LLqEtQVGJts5jBHfhh_9kPU';

        $client = new Client([
        // Base URI is used with relative requests
        //.'base_uri' => 'http://172.20.16.242:8080',
        'base_uri' => 'http://api.sie.gob.bo',
        // You can set any number of default request options.
        //'timeout'  => 3.0,
        ]);

        $response = $client->request('GET', '/persona/getPersonaByCarnetComplementoFechaNacimiento/'.$carnet.'/'.$complemento.'/'.$fechaNacimiento.'', ['headers' => ['Accept' => 'application/json', 'Authorization' => $token], ['debug' => true]])->getBody()->getContents();
        $responseDecode = json_decode($response);

        $DatosPersonaDecode = $responseDecode;
        $response = $DatosPersonaDecode;
		return $response;
	}

    public function registrarPersona($carnet, $complemento, $fechanacimiento, $paterno, $materno, $nombre, $genero) {
        /***NUEVO TOKEN A LA FECHA 25/09/2017*/
        $token = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6OTI1MDYxNjQsImlhdCI6MTUwODk0NTUyNCwiZXhwIjoxNTQwNDgxNTI0fQ.uPQ2qUoB3NBA2zlOswQ8LLqEtQVGJts5jBHfhh_9kPU';

        $client = new Client([
        // Base URI is used with relative requests
        //'base_uri' => 'http://172.20.16.242:8080',
        'base_uri' => 'http://api.sie.gob.bo',
        // You can set any number of default request options.
        //'timeout'  => 3.0,
        ]);

        $response = $client->request('POST', '/persona/setPersonaInsertar/'.$carnet.'/'.$complemento.'/'.$fechanacimiento.'/'.$paterno.'/'.$materno.'/'.$nombre.'/'.$genero.'', ['headers' => ['Accept' => 'application/json', 'Authorization' => $token], ['debug' => true]])->getBody()->getContents();
        $responseDecode = json_decode($response);

        $DatosPersonaDecode = $responseDecode;
        $response = $DatosPersonaDecode;
        return $response;
    }

    public function actualizarPersona($id, $carnet, $complemento, $fechanacimiento, $paterno, $materno, $nombre, $genero) {
        /***NUEVO TOKEN A LA FECHA 25/09/2017*/
        $token = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6OTI1MDYxNjQsImlhdCI6MTUwODk0NTUyNCwiZXhwIjoxNTQwNDgxNTI0fQ.uPQ2qUoB3NBA2zlOswQ8LLqEtQVGJts5jBHfhh_9kPU';

        $client = new Client([
        // Base URI is used with relative requests
        //'base_uri' => 'http://172.20.16.242:8080',
        'base_uri' => 'http://api.sie.gob.bo',
        // You can set any number of default request options.
        //'timeout'  => 3.0,
        ]);

        $response = $client->request('POST', '/persona/setPersonaActualizar/'.$id.'/'.$carnet.'/'.$complemento.'/'.$fechanacimiento.'/'.$paterno.'/'.$materno.'/'.$nombre.'/'.$genero.'', ['headers' => ['Accept' => 'application/json', 'Authorization' => $token], ['debug' => true]])->getBody()->getContents();
        $responseDecode = json_decode($response);

        $DatosPersonaDecode = $responseDecode;
        $response = $DatosPersonaDecode;
        return $response;
    }

}