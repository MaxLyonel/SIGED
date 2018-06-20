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
        $this->token = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJjODY5N25tY1M3TzViTmoxMFBEelI4OFY0QmtDQmlrVyIsInVzZXIiOiJhZmllbmdvIiwiZXhwIjoxNjE5OTgyMzAwfQ.sq_5MqNRGWl8x4DypeHJo-9_trLLFrYvF933B_8Niis';
        /*TOKEN DE CONEXIÓN*/
    }
    
    public function estadoServicio() {

        $response = $this->client->request(
            'GET', 
            'segip/v2/status', 
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

        $url = 'segip/v2/personas/'.$carnet.'?fecha_nacimiento='.$fechaNac;

        if($complemento != ''){
            $url = 'segip/v2/personas/'.$carnet.'?fecha_nacimiento='.$fechaNac.'&complemento='.$complemento;
        }

        $response = $this->client->request(
            'GET', 
            $url, 
            ['headers' => ['Accept' => 'application/json', 'Authorization' => $this->token], 
            ['debug' => true]])->getBody()->getContents();

        $responseDecode = json_decode($response, true);

        $response = array();

        foreach($responseDecode as $value) {
            $response = [
                /*'EsValido' => $value['EsValido'],
                'Mensaje' => $value['Mensaje'],
                'TipoMensaje' => $value['TipoMensaje'],
                'CodigoRespuesta' => $value['CodigoRespuesta'],
                'CodigoUnico' => $value['CodigoUnico'],
                'DescripcionRespuesta' => $value['DescripcionRespuesta'],*/
                'DatosPersonaEnFormatoJson' => $value['DatosPersonaEnFormatoJson']
            ];
        }

        $persona = $response['DatosPersonaEnFormatoJson'];

        if($persona == 'null') {
            $fechaNac = new \DateTime($fechaNac);
            $repository = $this->em->getRepository('SieAppWebBundle:Persona');
            $query = $repository->createQueryBuilder('p')
                ->select('p.id perId, p.complemento, p.carnet, p.paterno, p.materno, p.nombre, p.fechaNacimiento')
                ->where('p.carnet = :carnet')
                ->andWhere('p.fechaNacimiento = :fechaNacimiento')
                ->setParameter('carnet', $carnet)
                ->setParameter('fechaNacimiento', $fechaNac->format('d-m-Y'))
                ->getQuery();

            $persona = $query->getResult();
            dump($persona);
        } else {
            dump(json_decode($persona, true));
        }
        die;

		return $persona;
	}

}