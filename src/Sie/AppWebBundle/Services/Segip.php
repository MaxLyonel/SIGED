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
            //'base_uri' => 'http://100.0.100.116',
            //'base_uri' => 'http://localhost:1336'
            'base_uri' => 'http://100.0.101.79:1336'
        ]);

        /*TOKEN DE CONEXIÓN*/
        /*$this->sistemas_dev = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJXREdHc3VvQ1dVS1VFdDgxWFdnRk9NSnlBdzVRZDBxQyIsInVzZXIiOiJzaWVhY2FkZW1pY28iLCJleHAiOjE2MjUyMzg0MjB9.mZAIX3k76FkMxLKH8BlJ5CiGPlKEyKAFrsLTYG21Bqs';
        $this->sieacademico = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJXREdHc3VvQ1dVS1VFdDgxWFdnRk9NSnlBdzVRZDBxQyIsInVzZXIiOiJzaWVhY2FkZW1pY28iLCJleHAiOjE2MjUyMzg0MjB9.mZAIX3k76FkMxLKH8BlJ5CiGPlKEyKAFrsLTYG21Bqs';
        $this->siealternativa = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJXREdHc3VvQ1dVS1VFdDgxWFdnRk9NSnlBdzVRZDBxQyIsInVzZXIiOiJzaWVhY2FkZW1pY28iLCJleHAiOjE2MjUyMzg0MjB9.mZAIX3k76FkMxLKH8BlJ5CiGPlKEyKAFrsLTYG21Bqs';*/
        /*TOKEN DE CONEXIÓNAUX*/
        $this->sistemas_dev = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiI0M2t2ZVlDUmRxWGpFMW5QRHZYVWs1MW05OWEyOTJvQSIsInVzZXIiOiJzaXN0ZW1hc19kZXYiLCJleHAiOjE2MjUzNTAwMjB9.GmV5nakrvPSdrQq3TUAVwATtr4icHGE2Y2bj7w4qwSc';
        $this->sieacademico = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJXREdHc3VvQ1dVS1VFdDgxWFdnRk9NSnlBdzVRZDBxQyIsInVzZXIiOiJzaWVhY2FkZW1pY28iLCJleHAiOjE2MjUyMzg0MjB9.mZAIX3k76FkMxLKH8BlJ5CiGPlKEyKAFrsLTYG21Bqs';
        $this->siealternativa = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJ5U0JFcTBQNHVxeFR0UkNoczUxalg4WExaMWVybzJ6YiIsInVzZXIiOiJzaWVhbHRlcm5hdGl2YSIsImV4cCI6MTYzNzU5MDI2MH0.yOcs6xVNlFgUQPjYd3F8OCxUB7uARgeiVDyiO043lQE';
        /*TOKEN DE CONEXIÓN*/
    }
    
    public function estadoServicio($env, $sistema)
    {
        $token = $this->getToken($env,$sistema);
        $response = $this->client->request(
            'GET', 
            $this->getUrlBase($env).'status', 
            ['headers' => ['Accept' => 'application/json', 'Authorization' => $token], 
            ['debug' => true]])->getBody()->getContents();

        $responseDecode = json_decode($response, true);
        $response = false;
        if($responseDecode)
        {
            $response = true;
        }
		return $response;
	}

    public function buscarPersona($carnet, $complemento, $fechaNac, $env, $sistema) {

        $token = $this->getToken($env,$sistema);
        $fechaNac = date('d/m/Y', strtotime($fechaNac));

        $url = $this->getUrlBase($env).'personas/'.$carnet.'?fecha_nacimiento='.$fechaNac;

        if($complemento != ''){
            $url = $this->getUrlBase($env).'personas/'.$carnet.'?fecha_nacimiento='.$fechaNac.'&complemento='.$complemento;
        }

        $response = $this->client->request(
            'GET', 
            $url, 
            ['headers' => ['Accept' => 'application/json', 'Authorization' => $token], 
            ['debug' => true]])->getBody()->getContents();

        $responseDecode = json_decode($response, true);

		return $responseDecode;
	}


    //funcion modificada el 26/05/2021
    public function verificarPersona($carnet, $complemento, $paterno, $materno, $nombre, $fechaNac, $env, $sistema)
    {
    	//funcion modificada el 26/05/2021
    	$opcional= array(
    		'ci' => $carnet,
    		'complemento' => $complemento,
    		'primer_apellido' => $paterno,
    		'segundo_apellido' => $materno,
    		'nombre' => $nombre,
    		'fecha_nacimiento' => $fechaNac,
    	);
    	return $this->verificarPersonaPorCarnet($carnet, $opcional, $env, $sistema);
    	/*
        $token = $this->getToken($env,$sistema);
        $fechaNac = date('d/m/Y', strtotime($fechaNac));

        $url = $this->getUrlBase($env).'personas/'.$carnet.'?fecha_nacimiento='.$fechaNac.'&primer_apellido='.$paterno.'&segundo_apellido='.$materno.'&nombre='.$nombre;

        if($complemento != ''){
            $url = $this->getUrlBase($env).'personas/'.$carnet.'?fecha_nacimiento='.$fechaNac.'&primer_apellido='.$paterno.'&segundo_apellido='.$materno.'&nombre='.$nombre.'&complemento='.$complemento;
        }

        $response = $this->client->request(
            'GET', 
            $url, 
            ['headers' => ['Accept' => 'application/json', 'Authorization' => $token], 
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
        */
    }

    //funcion modificada el 28/06/2021
    public function buscarPersonaPorCarnet($carnet, $opcional, $env, $sistema)
    {
    	//funcion modificada el 28/06/2021
    	$resultadoValidacion = $this->verificarPersonaPorCarnet($carnet, $opcional, $env, $sistema);
    	$persona = array();

    	if($resultadoValidacion)
    	{
    		$opcional['ci']=$carnet;
    		$camposMinimosRequeridos = array_keys($opcional);
    		list($lista_campo,$tipo_persona,$rawDatosEnvidosSegip) = $this->getJsonDatosVerificacion($opcional,$camposMinimosRequeridos);
			
    		$persona['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson']=$lista_campo;
    		return $persona;
    	}
    	else
    		return null;
    	/*
        $c = 0;
        $query = '';

        $parametros = array();
        foreach ($opcional as $key => $value) {
            if(($key != 'entorno' && $key != '_token' && $key != 'carnet') && ($value != '')){
                $parametros[$key] = $value;
            }
            
        }

        foreach ($parametros as $key => $value) {
            if ($key == 'fecha_nacimiento') {
                $value = date('d/m/Y', strtotime($value));
            }
            if ($c == 0) {
                $query = '?'.$key.'='.$value;
            } else{
                $query = $query.'&'.$key.'='.$value;
            }
            $c++;
        }

        $token = $this->getToken($env,$sistema);

        $url = $this->getUrlBase($env).'personas/'.$carnet.$query;

        $response = $this->client->request(
            'GET', 
            $url, 
            ['headers' => ['Accept' => 'application/json', 'Authorization' => $token], 
            ['debug' => true]])->getBody()->getContents();

        $responseDecode = json_decode($response, true);

        return $responseDecode;
        */
    }

	//funcion modificada el 28/06/2021
	public function verificarPersonaPorCarnet($carnet, $opcional, $env, $sistema)
	{
		
		//funcion modificada el 28/06/2021
		$resultado = false;
		$token = $this->getToken($env,$sistema);
		try
		{
			$datosVerificacion = $opcional;
			$datosVerificacion = new \ArrayObject($opcional);
			$datosVerificacion['ci']=$carnet;
			//dump($datosVerificacion); die;
			list($lista_campo,$tipo_persona,$rawDatosEnvidosSegip) = $this->getJsonDatosVerificacion($datosVerificacion);
			/*dump($lista_campo);
			dump($tipo_persona);
			dump($rawDatosEnvidosSegip);
			die;*/

			//DATOS DE PRUEBA, QUITAR EN PRODUCCION
			//$lista_campo='{"ComplementoVisible":"1","NumeroDocumento":"13814118","Complemento":"1F","Nombres":"JHONSON JOEL","PrimerApellido":"QUEZO","SegundoApellido":"MANUELO","FechaNacimiento":"04/08/2007","LugarNacimientoPais":"BOLIVIA","LugarNacimientoDepartamento":"LA PAZ","LugarNacimientoProvincia":"MURILLO","LugarNacimientoLocalidad":"EL ALTO"}';
			//$respuestaJson = array();
			//$respuestaJson["ComplementoVisible"]="1";
			//$respuestaJson["NumeroDocumento"]="7000001";
			//$respuestaJson["Complemento"]="1P";
			//$respuestaJson["Nombres"]="ANGEL";
			//$respuestaJson["PrimerApellido"]="VILLANUEVA";
			//$respuestaJson["SegundoApellido"]="PEDRAZA";
			//$respuestaJson["FechaNacimiento"]="01/01/1998";
			//$lista_campo = json_encode($respuestaJson,JSON_UNESCAPED_SLASHES);
			//$tipo_persona = 1;//1: nacional, 2:extranjero
			//DATOS DE PRUEBA, QUITAR EN PRODUCCION
			$url = $this->getUrlBase($env)."personas/contrastacion/?lista_campo=$lista_campo&tipo_persona=$tipo_persona";
			//dump($url); die;
			$response = $this->client->request(
			    'GET', 
			    $url, 
			    ['headers' => ['Accept' => 'application/json', 'Authorization' => $token],
			    ['debug' => true]])->getBody()->getContents();
			//Obtenemos la respuesta del segip
			$resultado = $this->obtenerValidacionDeSegip($response,$rawDatosEnvidosSegip);
			//dump($resultado); die; //dcastillo para verificar el resultado de nacionla y extranjero
		}
		catch(Exception $e)
		{
			return false;
		}
		return $resultado;
	}

	/*Nueva funcion 26/08/2021*/
	private function getJsonDatosVerificacion($datosVerificacion,$camposMinimosRequeridos=array('ci','complemento','fecha_nacimiento'))
	{
		$respuestaJson = array();
		$tipo_persona = 1; //nacional he aqui el error
		$datosMinimos = 0;

		/**
		 * dcastillo: lo que venga del form 1 NACIONAL 2 EXTRANJERO
		 * que ya viene en datosVerificacion, si no existe hasta que se adecuen todos los formularios
		 * pregunta si existe y se queda con 1
		 */


		if (isset($datosVerificacion['tipo_persona'])) {
			$tipo_persona = $datosVerificacion['tipo_persona'];
		}
		

		foreach ($datosVerificacion as $key => $valor)
		{
			switch ($key)
			{
				case 'ci':
					$newKey="NumeroDocumento";
					//$datosMinimos ++;
				break;
				case 'complemento':
					$newKey="Complemento";
					$respuestaJson['ComplementoVisible']=($valor=='')?0:1;
					//$datosMinimos ++;
				break;
				case 'primer_apellido':
					$newKey="PrimerApellido";
				break;
				case 'segundo_apellido':
					$newKey="SegundoApellido";
				break;
				case 'nombre':
					$newKey="Nombres";
				break;
				case 'fecha_nacimiento':
					$newKey="FechaNacimiento";
					$valor = str_replace('-','/',$valor);
					//$datosMinimos++;
				break;
				default:
					$newKey=null;
					$valor = null;
				break;
			}
			if($newKey)
				$respuestaJson[$newKey]=trim($valor);
			if (in_array($key, $camposMinimosRequeridos))
			{
				$datosMinimos++;
			}
		}
		//verificacion de si extranjero, aqui nunca entra, no importa ya viene del form
		/*commente by krlos ... why set tipo_persona =2 ???, if foreign a value is sent(e,E,True...), empty in the other case*/
		if(array_key_exists('extranjero', $datosVerificacion)==true){
			// BUG asignacion de director
			// if($datosVerificacion['extranjero'] == 'E'){
				$tipo_persona=2; //extranjero
			// }
		}
			

		//verificacion de si NO tiene complemento
		if(array_key_exists('complemento', $datosVerificacion)==false)
			$respuestaJson['ComplementoVisible']=0;

		//esto verifica que por lo menos se recibio el ci, el complemento , la fecha de nacimiento o al menos otros 3 campos
		if($datosMinimos <3 || count($datosVerificacion)<3)
			$respuestaJson = array();

		return array(json_encode($respuestaJson,JSON_UNESCAPED_SLASHES),$tipo_persona,$respuestaJson);
	}

	/*Nueva funcion 26/08/2021*/
	private function obtenerValidacionDeSegip($respuestaSegip,$datosEnviadosSegip)	
	{
		$esCorrecto = true;
		/*
		'{
			"ConsultaDatoPersonaContrastacionResult":
			{
				"EsValido":"true",
				"Mensaje":"La consulta se realizó satisfactoriamente",
				"TipoMensaje":"Correcto",
				"CodigoRespuesta":"2",
				"CodigoUnico":"hUlLUuzX-5457274",
				"DescripcionRespuesta":"Se encontró un registro",
				"ContrastacionEnFormatoJson":"{\"LugarNacimientoPais\":2,\"LugarNacimientoDepartamento\":2,\"LugarNacimientoProvincia\":2,\"LugarNacimientoLocalidad\":2,\"ComplementoVisible\":1,\"NumeroDocumento\":1,\"Complemento\":1,\"Nombres\":1,\"PrimerApellido\":1,\"SegundoApellido\":1,\"FechaNacimiento\":1}"
			}
		}';
		*/
		try
		{
			$respustaSegipJsonDecode = json_decode($respuestaSegip);
			
			if(property_exists($respustaSegipJsonDecode,'ConsultaDatoPersonaContrastacionResult'))
			{
				$respuestaTmp = $respustaSegipJsonDecode->ConsultaDatoPersonaContrastacionResult;
				if(property_exists($respuestaTmp,'EsValido') && property_exists($respuestaTmp,'CodigoRespuesta') && property_exists($respuestaTmp,'ContrastacionEnFormatoJson'))
				{
					if($respuestaTmp->EsValido == true)
					{
						if($respuestaTmp->CodigoRespuesta==2)
						{
							$datosRecibidosSegip = $respuestaTmp->ContrastacionEnFormatoJson;
							$verificarDatosPersona= $this->verificacionDatosEnviadosYRecibidos($datosRecibidosSegip,$datosEnviadosSegip);
							if($verificarDatosPersona)
								$esCorrecto = true;
							else
								$esCorrecto = false;
						}
						else
						{
							$esCorrecto = false;
						}
					}
					else
					{
						$esCorrecto = false;
					}
				}
				else
				{
					$esCorrecto = false;
				}
			}
			else
			{
				$esCorrecto = false;
			}
		}
		catch(Exception $e)
		{
			return false;
		}

		return $esCorrecto;
	}

	private function retirarCamposVacios($datosEnviadosSegip)
	{
		$return = array();
		if(count($datosEnviadosSegip)>0)
		{
			foreach ($datosEnviadosSegip as $key => $value)
			{
				if($key != 'ComplementoVisible' && $key != 'Complemento')
				{
					if($value == '')
					{
						unset($datosEnviadosSegip[$key]);
					}
				}
			}
			$return = $datosEnviadosSegip;
		}
		return $return;
	}

	/*Nueva funcion 26/08/2021*/
	private function verificacionDatosEnviadosYRecibidos($datosRecibidosSegip,$datosEnviadosSegip)
	{
		$datosValidos =true;
		$datosEnviadosSegip_copy = $datosEnviadosSegip;

		// retiramos los campos con valor vacio que se envian al segip, sin tomar en cuenta el Complemento y ComplementoVisible
		// Esto funciona pero solo se retornara los campos DISTINTOS DE VACIOS ... por favor considerarlo 
		//$datosEnviadosSegip = $this->retirarCamposVacios($datosEnviadosSegip);

		if(count($datosEnviadosSegip)>=3)//verificamos que al menos se envien 3 datos
		{
			try
			{
				$datosRecibidosSegip_decode=json_decode($datosRecibidosSegip);
				foreach ($datosEnviadosSegip as $key => $value)
				{
					if($key != 'ComplementoVisible' && $key != 'Complemento')
					{
						if($datosRecibidosSegip_decode->{$key} != 1 )
						{
							$datosValidos = false;
							break;
						}
					}
				}

				//verificamos el complemento
				if(array_key_exists('Complemento', $datosEnviadosSegip)==true)
				{
					$tmpComplementoEnviado = $datosEnviadosSegip['Complemento'];
					$tmpComplementoRecibido = $datosRecibidosSegip_decode->Complemento;
					if(strlen($tmpComplementoEnviado)!=0)
					{
						if($tmpComplementoRecibido!=1)
						{
							$datosValidos = false;
						}
					}
				}
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		else
		{
			$datosValidos= false;
		}
		return $datosValidos;
	}

    private function getToken($env, $sistema){
        if ($env == 'dev') {
            $token = $this->sistemas_dev;
        } else {
            switch ($sistema) {
                case 'academico':
                    $token = $this->sieacademico;
                    break;

                case 'alternativa':
                    $token = $this->siealternativa;
                    break;                    
                
                default:
                    $token = $this->sistemas_dev;
                    break;
            }
        }
        return $token;
    }

    private function getUrlBase($env){
        if ($env == 'dev') {
            $url = 'desarrollo/segip/v2/';
        } else {
            $url = 'segip/v2/';
        }
        return $url;
    }
}