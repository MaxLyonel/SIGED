<?php

namespace Sie\AppWebBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use GuzzleHttp\Client;

class Agetic {

	protected $em;
    protected $router;
    
	public function __construct(EntityManager $entityManager, Router $router) {
		$this->em = $entityManager;
        $this->router = $router;
        $this->client = new Client([
            'base_uri' => 'https://ws.agetic.gob.bo',
        ]);

        /*TOKEN DE CONEXIÓN*/
        $this->token_sistemas = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiI0MGQ2ZTNlNDZhODQ0NGQ1ODljMTUyZDUzODhlYzE3YiIsInVzZXIiOiJqaW1lcWUtbWluZWR1LWRpczEtbWlnMSIsImV4cCI6MTY1OTE5MzYyMH0.klutn0SgCoYpiL5aiuy07gY7jNhoU_V0xncU2nht1lI';
        /*TOKEN DE CONEXIÓN*/
    }
    
    public function estadoServicioDiscapacidad() {
        $estado = false;
        $response = $this->client->request(
            'GET',
            '/unidadDiscapacidad/v1/estado',
            ['headers' => ['Accept' => 'application/json', 'Authorization' => $this->token_sistemas],
            ['debug' => true]]);
        
        if($response->getStatusCode() === 200) {
            $body = json_decode($response->getBody()->getContents(), true);
            if($body["estado"] === "El servicio de Unidad de Discapacidad v1 se encuentra disponible"){
                $estado = true;
            }
        }

		return $estado;
	}

    public function buscarCertificadoDiscapacidad($carnet) {
        $body = null;
        $response = $this->client->request(
            'GET',
            '/unidadDiscapacidad/v1/certificadoDiscapacidad/'.$carnet,
            ['headers' => ['Accept' => 'application/json', 'Authorization' => $this->token_sistemas], 
            ['debug' => true]]);

        if($response->getStatusCode() === 200) {
            $body = json_decode($response->getBody()->getContents(), true);
        }
        
        return $body;
	}
}