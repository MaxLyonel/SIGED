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
        $token = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6OTI1MDYxNjQsImlhdCI6MTU0MDQ5MTY3OSwiZXhwIjoxNTcyMDI3Njc5fQ.lf233gXhht6PCcs29WmKN9a2Sy-sj_buKtJVsbM_CPc';

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
        $token = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6OTI1MDYxNjQsImlhdCI6MTU0MDQ5MTY3OSwiZXhwIjoxNTcyMDI3Njc5fQ.lf233gXhht6PCcs29WmKN9a2Sy-sj_buKtJVsbM_CPc';

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
        $token = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6OTI1MDYxNjQsImlhdCI6MTU0MDQ5MTY3OSwiZXhwIjoxNTcyMDI3Njc5fQ.lf233gXhht6PCcs29WmKN9a2Sy-sj_buKtJVsbM_CPc';

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
        $token = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6OTI1MDYxNjQsImlhdCI6MTU0MDQ5MTY3OSwiZXhwIjoxNTcyMDI3Njc5fQ.lf233gXhht6PCcs29WmKN9a2Sy-sj_buKtJVsbM_CPc';

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

    public function BuscarPersonaPorCarnetComplemento($form) {
        $carnet = $form['carnet'];
        $complemento = ($form['complemento'] != "") ? mb_strtoupper($form['complemento'], 'utf-8') : 0;
        
        $repository = $this->em->getRepository('SieAppWebBundle:Persona');

        if($complemento == '0'){
            $query = $repository->createQueryBuilder('p')
                ->select('p')
                ->where('p.carnet = :carnet AND p.segipId >= :valor')
                ->setParameter('carnet', $carnet)
                ->setParameter('valor', 0)
                ->getQuery();
        }
        else{
            $query = $repository->createQueryBuilder('p')
                ->select('p')
                ->where('p.carnet = :carnet AND p.complemento = :complemento AND p.segipId >= :valor')
                ->setParameter('carnet', $carnet)
                ->setParameter('complemento', $complemento)
                ->setParameter('valor', 0)
                ->getQuery();
        }
        $personas = $query->getResult();

        $p = null;

        if ($personas) {
            if(is_array($personas)) {
                $p = array(
                    'personaId'=>$personas[0]->getId(),
                    'personaCarnet'=>$personas[0]->getCarnet(),
                    'personaComplemento'=>$personas[0]->getComplemento(),
                    'personaPaterno'=>$personas[0]->getPaterno(),
                    'personaMaterno'=>$personas[0]->getMaterno(),
                    'personaNombre'=>$personas[0]->getNombre(),
                    'personaFechaNac'=>$personas[0]->getFechaNacimiento(),
                );
            }
        }

        return($p);
    }

    public function BuscarPersonaPorCarnetComplementoSie($form) {
        $carnet = $form['carnet'];
        $complemento = ($form['complemento'] != "") ? mb_strtoupper($form['complemento'], 'utf-8') : 0;
        
        $repository = $this->em->getRepository('SieAppWebBundle:Persona');

        if($complemento == '0'){
            $query = $repository->createQueryBuilder('p')
                ->select('p')
                ->where('p.carnet = :carnet')
                ->setParameter('carnet', $carnet)
                ->getQuery();
        }
        else{
            $query = $repository->createQueryBuilder('p')
                ->select('p')
                ->where('p.carnet = :carnet AND p.complemento = :complemento')
                ->setParameter('carnet', $carnet)
                ->setParameter('complemento', $complemento)
                ->getQuery();
        }
        $personas = $query->getResult();

        $p = null;

        if ($personas) {
            if(is_array($personas)) {
                $p = array(
                    'personaId'=>$personas[0]->getId(),
                    'personaCarnet'=>$personas[0]->getCarnet(),
                    'personaComplemento'=>$personas[0]->getComplemento(),
                    'personaPaterno'=>$personas[0]->getPaterno(),
                    'personaMaterno'=>$personas[0]->getMaterno(),
                    'personaNombre'=>$personas[0]->getNombre(),
                    'personaFechaNac'=>$personas[0]->getFechaNacimiento(),
                    'personaSegipId'=>$personas[0]->getSegipId(),
                );
            }
        }

        return($p);
    }

}