<?php

namespace Sie\InfraestructuraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteadministrativoTipo;
use Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteadministrativoPicina;
use Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteadministrativoColiseo;
use Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteadministrativoParque;
use Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteadministrativoPatio;
use Sie\AppWebBundle\Entity\InfraestructuraH5Ambienteadministrativo;
use Sie\AppWebBundle\Entity\InfraestructuraH5MaterialPisoTipo;
use Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo;
use Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteadministrativoInmobiliario;
use Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteadministrativoCancha;
use Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteadministrativoAmbiente;
use Sie\AppWebBundle\Form\InfraestructuraH5AmbienteadministrativoAmbienteType;


class AmbientesAdministrativosRecreativosController extends Controller
{
	public $session;
	public function __construct() {
        $this->session = new Session();
    }

    public function indexAction()
    {
    	$infraJuridiccionGeograficaId = $this->session->get('infJurGeoId');
    	$em = $this->getDoctrine()->getManager();
    	$em->getConnection()->beginTransaction();
    	try {
    		$ambienteAdministrativo = $em->getRepository('SieAppWebBundle:InfraestructuraH5Ambienteadministrativo')->findOneby(array('infraestructuraJuridiccionGeografica'=>$infraJuridiccionGeograficaId));
            $ambientes = null;
            $equipamiento = null;
            $canchas = null;
            $patios = null;
            $parques = null;
            $coliseos = null;
            $piscinas = null;
            if($ambienteAdministrativo){
							$id = $ambienteAdministrativo->getId();
                $ambientes = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoAmbiente')->findby(array('infraestructuraH5Ambienteadministrativo'=>$ambienteAdministrativo->getId()));
                $equipamiento = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoInmobiliario')->findby(array('infraestructuraH5Ambienteadministrativo'=>$ambienteAdministrativo->getId()));
                $canchas = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoCancha')->findby(array('infraestructuraH5Ambienteadministrativo'=>$ambienteAdministrativo->getId()));
                $patios = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoPatio')->findby(array('infraestructuraH5Ambienteadministrativo'=>$ambienteAdministrativo->getId()));
                $parques = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoParque')->findby(array('infraestructuraH5Ambienteadministrativo'=>$ambienteAdministrativo->getId()));
                $coliseos = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoColiseo')->findby(array('infraestructuraH5Ambienteadministrativo'=>$ambienteAdministrativo->getId()));
                $piscinas = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoPicina')->findby(array('infraestructuraH5Ambienteadministrativo'=>$ambienteAdministrativo->getId()));
            }else{
							$id = 'new';
						}

				$form = $this->createCreateFormMain($id,$infraJuridiccionGeograficaId);
    		return $this->render('SieInfraestructuraBundle:AmbientesAdministrativosRecreativos:index.html.twig',array(
                'ambientes'=>$ambientes,
                'equipamiento'=>$equipamiento,
                'canchas'=>$canchas,
                'patios'=>$patios,
                'parques'=>$parques,
                'coliseos'=>$coliseos,
                'piscinas'=>$piscinas,
								'form'   => $form->createView(),
            ));
    	} catch (Exception $e) {

    	}
    }

		/**
		 * Creates a form to create a InfraestructuraH5AmbienteadministrativoAmbiente entity.
		 *
		 * @param InfraestructuraH5AmbienteadministrativoAmbiente $entity The entity
		 *
		 * @return \Symfony\Component\Form\Form The form
		 */
		private function createCreateFormMain($id,$infraJuridiccionGeograficaId){
				$form = $this->createFormBuilder()
					->setAction($this->generateUrl('infra_ambientesAdministrativosRecreativos_infosave'))
					->add('save', 'submit', array('label' => 'Guardar Cambios', 'attr'=>array('class'=>'btn btn-success-alt')))
					->add('infraJuridiccionGeograficaId', 'hidden', array('data'=>$infraJuridiccionGeograficaId))
					->add('id', 'hidden', array('data'=>$id))
					->getForm();
				return $form;
		}

		public function infoCompleteAction(Request $request){
			//get the values send
			$id = $request->get('id');
			//crete DB conexion
			$em = $this->getDoctrine()->getManager();

			$ambientes = null;
			$equipamiento = null;
			$canchas = null;
			$patios = null;
			$parques = null;
			$coliseos = null;
			$piscinas = null;
			$dataAmbientes = array();
			$dataEquipamientos = array();
			$dataCanchas = array();
			$dataPatios = array();
			$dataParques = array();
			$dataColiseos = array();
			$dataPiscinas = array();
			//edit or new rows
			if($id != 'new'){
				//find compete info
					$ambientes    = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoAmbiente')->findby(array('infraestructuraH5Ambienteadministrativo'=>$id));
					
					if($ambientes){
						foreach ($ambientes as $ambiente) {
							# code...
							$dataAmbientes[$ambiente->getId()][]=$ambiente->getN11AmbienteadministrativoTipo()->getId();
							$dataAmbientes[$ambiente->getId()][]=$ambiente->getN11NumeroBueno();
							$dataAmbientes[$ambiente->getId()][]=$ambiente->getN11NumeroRegular();
							$dataAmbientes[$ambiente->getId()][]=$ambiente->getN11NumeroMalo();

						}
					}

					$equipamiento = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoInmobiliario')->findby(array('infraestructuraH5Ambienteadministrativo'=>$id));
					
					if($equipamiento){
						foreach ($equipamiento as $equipo) {
							# code...
							$dataEquipamientos[$equipo->getId()][]=$equipo->getN12InmobiliarioTipo()->getId();
							$dataEquipamientos[$equipo->getId()][]=$equipo->getN12NumeroBueno();
							$dataEquipamientos[$equipo->getId()][]=$equipo->getN12NumeroRegular();
							$dataEquipamientos[$equipo->getId()][]=$equipo->getN12NumeroMalo();

						}
					}

					$canchas  	  = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoCancha')->findby(array('infraestructuraH5Ambienteadministrativo'=>$id));
					
					if($canchas){
						foreach ($canchas as $cancha) {
							# code...
							$dataCanchas[$cancha->getId()][]=$cancha->getN21DeporteDestino();
							$dataCanchas[$cancha->getId()][]=$cancha->getN21MaterialPisoTipo()->getId();
							$dataCanchas[$cancha->getId()][]=$cancha->getN21PisoEstadogeneralTipo()->getId();
							$dataCanchas[$cancha->getId()][]=$cancha->getN21EsTechado();
							$dataCanchas[$cancha->getId()][]=$cancha->getN21EsGraderias();
							$dataCanchas[$cancha->getId()][]=$cancha->getN21CapacidadGraderias();
							$dataCanchas[$cancha->getId()][]=$cancha->getN21AreaCanchaMt2();
						}
					}

					$patios   	  = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoPatio')->findby(array('infraestructuraH5Ambienteadministrativo'=>$id));
					
					if($patios){
						foreach ($patios as $patio) {
							# code...
							$dataPatios[$patio->getId()][]=$patio->getN22MaterialPisoTipo()->getId();
							$dataPatios[$patio->getId()][]=$patio->getN22PisoEstadogeneralTipo()->getId();
							$dataPatios[$patio->getId()][]=$patio->getN22AreaCanchaMt2();
						}
					}

					$parques  	  = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoParque')->findby(array('infraestructuraH5Ambienteadministrativo'=>$id));
					
					if($parques){
						foreach ($parques as $parque) {
							# code...
							$dataParques[$parque->getId()][]=$parque->getN23MaterialPisoTipo()->getId();
							$dataParques[$parque->getId()][]=$parque->getN23PisoEstadogeneralTipo()->getId();
							$dataParques[$parque->getId()][]=$parque->getN23AreaCanchaMt2();
						}
					}
					$coliseos 	  = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoColiseo')->findby(array('infraestructuraH5Ambienteadministrativo'=>$id));
					
					if($coliseos){
						foreach ($coliseos as $coliseo) {
							# code...
							$dataColiseos[$coliseo->getId()][]=$coliseo->getN24MaterialPisoTipo()->getId();
							$dataColiseos[$coliseo->getId()][]=$coliseo->getN24PisoEstadogeneralTipo()->getId();
							$dataColiseos[$coliseo->getId()][]=$coliseo->getN24EsTechado();
							$dataColiseos[$coliseo->getId()][]=$coliseo->getN24Capacidad();
							$dataColiseos[$coliseo->getId()][]=$coliseo->getN24AreaMt2();
						}
					}
					$piscinas 	  = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoPicina')->findby(array('infraestructuraH5Ambienteadministrativo'=>$id));
					
					if($piscinas){
						foreach ($piscinas as $piscina) {
							# code...
							$dataPiscinas[$piscina->getId()][]=$piscina->getN25PredominanteMaterialTipo()->getId();
							$dataPiscinas[$piscina->getId()][]=$piscina->getN25PisoEstadogeneralTipo()->getId();
							$dataPiscinas[$piscina->getId()][]=$piscina->getN25EsTechado();
							$dataPiscinas[$piscina->getId()][]=$piscina->getN25Capacidad();
							$dataPiscinas[$piscina->getId()][]=$piscina->getN25AreaMt2();
						}
					}
			}else{
					// $bloquesEdificados = null;
			}



			//get InfraestructuraH5AmbienteadministrativoTipo info clafificator
			$infraestructuraH5AmbienteadministrativoTipoObj = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoTipo')->findAll();
			$arrAmbientes = array();
			foreach ($infraestructuraH5AmbienteadministrativoTipoObj as $value) {
				$arrAmbientes[$value->getId()] = $value->getInfraestructuraAmbiente();
			}
			//get InfraestructuraInmobiliario type
			$infraestructurah5inmobiliariotipoObj = $em->getRepository('SieAppWebBundle:InfraestructuraH5InmobiliarioTipo')->findAll();
			$arrEquipamiento = array();
			foreach ($infraestructurah5inmobiliariotipoObj as $value) {
				$arrEquipamiento[$value->getId()] = $value->getInfraestructuraInmobiliario();
			}

			//////

			// $pisoTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH2PisoMaterialTipo')->findAll();
			$pisoTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH5MaterialPisoTipo')->findAll();
			$arrMaterialPiso = array();
			foreach ($pisoTipo as $piso) {
					// $arrMaterialPiso[$piso->getId()] = $piso->getInfraestructuraPisoMaterial();
					$arrMaterialPiso[$piso->getId()] = $piso->getInfraestructuraMaterialPiso();
			}
			$paredTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH2ParedMaterialTipo')->findAll();
			$arrMaterialPared = array();
			foreach ($paredTipo as $pared) {
					$arrMaterialPared[$pared->getId()] = $pared->getInfraestructuraParedMaterialTipo();
			}
			$techoTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH2TechoMaterialTipo')->findAll();
			$arrTecho = array();
			foreach ($techoTipo as $techo) {
					$arrTecho[$techo->getId()] = $techo->getInfraestructuraTechoMaterialTipo();
			}

			$estadogeneralTipo = $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->findAll();
			$arrEstadogeneral = array();
			foreach ($estadogeneralTipo as $estadogeneralT) {
					$arrEstadogeneral[$estadogeneralT->getId()] = $estadogeneralT->getInfraestructuraEstadogeneral();
			}


			//return the values to build
			$response = new JsonResponse();
			return $response->setData(array(
				'ambientesAdm'    => $arrAmbientes,
				'equipamientosAdm'=> $arrEquipamiento,
				'materialpiso' => $arrMaterialPiso,
				'estadoGeneral' => $arrEstadogeneral,
				'yesnobool' => array('0'=>'No', '1'=>'Si'),
				'dataAmbientes' => $dataAmbientes,
				'dataEquipamientos' => $dataEquipamientos,
				'dataCanchas' => $dataCanchas,
				'dataPatios' => $dataPatios,
				'dataParques' => $dataParques,
				'dataColiseos' => $dataColiseos,
				'dataPiscinas' => $dataPiscinas,
			));
		}

		public function infoSaveAction(Request $request){

			//get the values send
			$form = $request->get('form');
			$formAmb = $request->get('formAmb');
			$formEquipa = $request->get('formEquipa');
			$formCanchas = $request->get('formCanchas');
			$formPatios = $request->get('formPatios');
			$formParques = $request->get('formParques');
			$formColiseos = $request->get('formColiseos');
			$formPiscinas = $request->get('formPiscinas');
			// create DB conexion
			$em = $this->getDoctrine()->getManager();
			$em->getConnection()->beginTransaction();

			try {
				if($form['id']=='new'){
					// save the new info on forms
					$this->saveAmbientesAdministrativosRecreativos($form, $formAmb, $formEquipa, $formCanchas, $formPatios,	$formParques,	$formColiseos,	$formPiscinas);
				}else{
					// remove the all data
					$this->removeAmbientesAdministrativosRecreativos($form);
					// save the new info on form
					$this->saveAmbientesAdministrativosRecreativos($form, $formAmb, $formEquipa, $formCanchas, $formPatios,	$formParques,	$formColiseos,	$formPiscinas);
				}
				$em->getConnection()->commit();
				$this->messageFlash('success', 'Los datos han sido guardados correctamente...');
				return $this->redirect($this->generateUrl('infra_info_acceder'));
			} catch (Exception $e) {
				$em->getConnection()->rollback();
				$this->messageFlash('danger', 'Error al intentar guardar '.$e);
				// echo 'error on this expresion '.$e;
			}
			die;
		}
		/**
		 * show the ERRROR message
		 * @param  [type] $typeMessage [type of message]
		 * @param  [type] $message     [message of errro]
		 * @return [type]              [description]
		 */
		private function messageFlash($typeMessage, $message){
			$this->session->set('messageInfra', $typeMessage);
			$this->addFlash($this->session->get('messageInfra'), $message);
			return 'krlos';
		}
		/**
		 * [removeAmbientesAdministrativosRecreativos description]
		 * @param  [type] $form [description]
		 * @return [type]       [description]
		 */
		private function removeAmbientesAdministrativosRecreativos($form){
			//create DB conexion
			$em =  $this->getDoctrine()->getManager();
			//get the data post send
			$id = $form['id'];

			$ambientes    = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoAmbiente')->findby(array('infraestructuraH5Ambienteadministrativo'=>$id));
			$equipamiento = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoInmobiliario')->findby(array('infraestructuraH5Ambienteadministrativo'=>$id));
			$canchas  	  = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoCancha')->findby(array('infraestructuraH5Ambienteadministrativo'=>$id));
			$patios   	  = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoPatio')->findby(array('infraestructuraH5Ambienteadministrativo'=>$id));
			$parques  	  = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoParque')->findby(array('infraestructuraH5Ambienteadministrativo'=>$id));
			$coliseos 	  = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoColiseo')->findby(array('infraestructuraH5Ambienteadministrativo'=>$id));
			$piscinas 	  = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoPicina')->findby(array('infraestructuraH5Ambienteadministrativo'=>$id));
			//remove elements if exists
			if($ambientes)
				$this->removeObjectData($ambientes);
			if($equipamiento)
				$this->removeObjectData($equipamiento);
			if($canchas)
				$this->removeObjectData($canchas);
			if($patios)
				$this->removeObjectData($patios);
			if($parques)
				$this->removeObjectData($parques);
			if($coliseos)
				$this->removeObjectData($coliseos);
			if($piscinas)
				$this->removeObjectData($piscinas);
		}
		/**
		 * [removeObjectData description]
		 * @param  [type] $objectData [description]
		 * @return [type]             [description]
		 */
		private function removeObjectData($objectData){
			//create DB conexion
			$em =  $this->getDoctrine()->getManager();
			foreach ($objectData as $value) {
				$em->remove($value);
			}
		}



		private function saveAmbientesAdministrativosRecreativos($form, $formAmb, $formEquipa, $formCanchas, $formPatios,	$formParques,	$formColiseos,	$formPiscinas){
			//creat DB conexion
			$em = $this->getDoctrine()->getManager();

			$objAmbientes = null;
			$objEquipamiento = null;
			$objCanchas = null;
			$objPatios = null;
			$objParques = null;
			$objColiseos = null;
			$objPiscinas = null;

			$id = $form['id'];
			if($id == 'new'){
				$objAmbienteAdministrativo = new InfraestructuraH5Ambienteadministrativo();
				$objAmbienteAdministrativo->setObs('new');
				$objAmbienteAdministrativo->setFecharegistro(new \DateTime('now'));
				$objAmbienteAdministrativo->setInfraestructuraJuridiccionGeografica($em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($form['infraJuridiccionGeograficaId']));
				$em->persist($objAmbienteAdministrativo);
				$em->flush();
				$id = $objAmbienteAdministrativo->getId();
			}

			// existe info formAmb save the data
			if($formAmb){
				$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('Infraestructura_H5_Ambienteadministrativo_Ambiente');")->execute();
				foreach ($formAmb as $key => $value) {
					$objAmbientes = new InfraestructuraH5AmbienteadministrativoAmbiente();
					# code...
					$objAmbientes->setN11AmbienteadministrativoTipo($em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoTipo')->find($value[0]));
					# set bueno data
					$objAmbientes->setN11NumeroBueno($value[1]);
					# set regular data
					$objAmbientes->setN11NumeroRegular($value[2]);
					# set malo data
					$objAmbientes->setN11NumeroMalo($value[3]);
					# set malo data
					$objAmbientes->setFecharegistro(new \DateTime('now'));
					# set malo data
					// $objAmbientes->setInfraestructuraH5Ambienteadministrativo($em->getRepository('SieAppWebBundle:InfraestructuraH5Ambienteadministrativo')->find($id));
					$objAmbientes->setInfraestructuraH5Ambienteadministrativo($em->getRepository('SieAppWebBundle:InfraestructuraH5Ambienteadministrativo')->find($id));
					$em->persist($objAmbientes);
					$em->flush();
				}
			}
			// existe info formEquipa save the data
			if($formEquipa){
				$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h5_ambienteadministrativo_inmobiliario');")->execute();
				foreach ($formEquipa as $key => $equipo) {
					$objEquipamiento = new InfraestructuraH5AmbienteadministrativoInmobiliario();
					# code...
					$objEquipamiento->setN12InmobiliarioTipo($em->getRepository('SieAppWebBundle:InfraestructuraH5InmobiliarioTipo')->find($equipo[0]));
					# set bueno data
					$objEquipamiento->setN12NumeroBueno($equipo[1]);
					# set regular data
					$objEquipamiento->setN12NumeroRegular($equipo[2]);
					# set malo data
					$objEquipamiento->setN12NumeroMalo($equipo[3]);
					# set malo data
					$objEquipamiento->setFecharegistro(new \DateTime('now'));
					# set malo data
					$objEquipamiento->setInfraestructuraH5Ambienteadministrativo($em->getRepository('SieAppWebBundle:InfraestructuraH5Ambienteadministrativo')->find($id));
					$em->persist($objEquipamiento);
					$em->flush();
				}
			}
			// existe info $formCanchas save the data
			if($formCanchas){
				$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('Infraestructura_H5_Ambienteadministrativo_Cancha');")->execute();
				foreach ($formCanchas as $key => $cancha) {
					$objCanchas = new InfraestructuraH5AmbienteadministrativoCancha();
					# set setInfraestructuraH5Ambienteadministrativo data
					$objCanchas->setInfraestructuraH5Ambienteadministrativo($em->getRepository('SieAppWebBundle:InfraestructuraH5Ambienteadministrativo')->find($id));
					# set destino data
					$objCanchas->setN21DeporteDestino($cancha[0]);
					# set setN21MaterialPisoTipo data
					$objCanchas->setN21MaterialPisoTipo($em->getRepository('SieAppWebBundle:InfraestructuraH5MaterialPisoTipo')->find($cancha[1]));
					# set setN21PisoEstadogeneralTipo data
					$objCanchas->setN21PisoEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($cancha[2]));
					# set es techado data
					$objCanchas->setN21EsTechado($cancha[3]);
					# set es graderia data
					$objCanchas->setN21EsGraderias($cancha[4]);
					# set graderias data
					$objCanchas->setN21CapacidadGraderias($cancha[5]);
					# set cancha metros2 data
					$objCanchas->setN21AreaCanchaMt2($cancha[6]);
					# set fecha data
					$objCanchas->setFecharegistro(new \DateTime('now'));
					$em->persist($objCanchas);
					$em->flush();
				}
			}
			// existe info $formCanchas save the data
			if($formPatios){
				$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('Infraestructura_H5_Ambienteadministrativo_Patio');")->execute();
				foreach ($formPatios as $key => $patio) {
					$objPatios = new InfraestructuraH5AmbienteadministrativoPatio();
					# set setInfraestructuraH5Ambienteadministrativo data
					// $objPatios->setInfraestructuraH5Ambienteadministrativo($em->getRepository('SieAppWebBundle:InfraestructuraH5Ambienteadministrativo')->find($objAmbienteAdministrativo->getId()));
					$objPatios->setInfraestructuraH5Ambienteadministrativo($em->getRepository('SieAppWebBundle:InfraestructuraH5Ambienteadministrativo')->find($id));
					# set setN21MaterialPisoTipo data
					$objPatios->setN22MaterialPisoTipo($em->getRepository('SieAppWebBundle:InfraestructuraH5MaterialPisoTipo')->find($patio[0]));
					# set setN21PisoEstadogeneralTipo data
					$objPatios->setN22PisoEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($patio[1]));
					# set destino data
					$objPatios->setN22AreaCanchaMt2($patio[2]);
					# set fecha data
					$objPatios->setFecharegistro(new \DateTime('now'));
					$em->persist($objPatios);
					$em->flush();
				}
			}
			if($formParques){
				$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('Infraestructura_H5_Ambienteadministrativo_Parque');")->execute();
				foreach ($formParques as $key => $parque) {
					$objParques = new InfraestructuraH5AmbienteadministrativoParque();
					# set setInfraestructuraH5Ambienteadministrativo data
					// $objPatios->setInfraestructuraH5Ambienteadministrativo($em->getRepository('SieAppWebBundle:InfraestructuraH5Ambienteadministrativo')->find($objAmbienteAdministrativo->getId()));
					$objParques->setInfraestructuraH5Ambienteadministrativo($em->getRepository('SieAppWebBundle:InfraestructuraH5Ambienteadministrativo')->find($id));
					# set setN21MaterialPisoTipo data
					$objParques->setN23MaterialPisoTipo($em->getRepository('SieAppWebBundle:InfraestructuraH5MaterialPisoTipo')->find($parque[0]));
					# set setN21PisoEstadogeneralTipo data
					$objParques->setN23PisoEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($parque[1]));
					# set destino data
					$objParques->setN23AreaCanchaMt2($parque[2]);
					# set fecha data
					$objParques->setFecharegistro(new \DateTime('now'));
					$em->persist($objParques);
					$em->flush();
				}
			}
			if($formColiseos){
				$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('Infraestructura_H5_Ambienteadministrativo_Coliseo');")->execute();
				foreach ($formColiseos as $key => $coliseo) {
					$objColiseos = new InfraestructuraH5AmbienteadministrativoColiseo();
					# set setInfraestructuraH5Ambienteadministrativo data
					// $objPatios->setInfraestructuraH5Ambienteadministrativo($em->getRepository('SieAppWebBundle:InfraestructuraH5Ambienteadministrativo')->find($objAmbienteAdministrativo->getId()));
					$objColiseos->setInfraestructuraH5Ambienteadministrativo($em->getRepository('SieAppWebBundle:InfraestructuraH5Ambienteadministrativo')->find($id));
					# set setN21MaterialPisoTipo data
					$objColiseos->setN24MaterialPisoTipo($em->getRepository('SieAppWebBundle:InfraestructuraH5MaterialPisoTipo')->find($coliseo[0]));
					# set setN21PisoEstadogeneralTipo data
					$objColiseos->setN24PisoEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($coliseo[1]));
					# set setN24EsTechado data
					$objColiseos->setN24EsTechado($coliseo[2]);
					# set setN24EsTechado data
					$objColiseos->setN24Capacidad($coliseo[3]);
					# set destino data
					$objColiseos->setN24AreaMt2($coliseo[4]);
					# set fecha data
					$objColiseos->setFecharegistro(new \DateTime('now'));
					$em->persist($objColiseos);
					$em->flush();
				}
			}
			if($formPiscinas){
				$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('Infraestructura_H5_Ambienteadministrativo_Picina');")->execute();
				foreach ($formPiscinas as $key => $piscina) {
					$objPiscinas = new InfraestructuraH5AmbienteadministrativoPicina();
					# set setInfraestructuraH5Ambienteadministrativo data
					// $objPatios->setInfraestructuraH5Ambienteadministrativo($em->getRepository('SieAppWebBundle:InfraestructuraH5Ambienteadministrativo')->find($objAmbienteAdministrativo->getId()));
					$objPiscinas->setInfraestructuraH5Ambienteadministrativo($em->getRepository('SieAppWebBundle:InfraestructuraH5Ambienteadministrativo')->find($id));
					# set setN21MaterialPisoTipo data
					$objPiscinas->setN25PredominanteMaterialTipo($em->getRepository('SieAppWebBundle:InfraestructuraH5MaterialTipo')->find($piscina[0]));
					# set setN21PisoEstadogeneralTipo data
					$objPiscinas->setN25PisoEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($piscina[1]));
					# set setN24EsTechado data
					$objPiscinas->setN25EsTechado($piscina[2]);
					# set setN24EsTechado data
					$objPiscinas->setN25Capacidad($piscina[3]);
					# set destino data
					$objPiscinas->setN25AreaMt2($piscina[4]);
					# set fecha data
					$objPiscinas->setFecharegistro(new \DateTime('now'));
					$em->persist($objPiscinas);
					$em->flush();

				}
			}
			return 'ok';
		}

//
// ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
//     public function equipamientoAdicionarAction(){
//         return $this->render('SieInfraestructuraBundle:AmbientesAdministrativosRecreativos:equipamientoAdicionar.html.twig');
//     }
//
//     public function canchaAdicionarAction(){
//         return $this->render('SieInfraestructuraBundle:AmbientesAdministrativosRecreativos:canchaAdicionar.html.twig');
//     }
//
//     public function patioAdicionarAction(){
//         return $this->render('SieInfraestructuraBundle:AmbientesAdministrativosRecreativos:patioAdicionar.html.twig');
//     }
//
//     public function parqueAdicionarAction(){
//         return $this->render('SieInfraestructuraBundle:AmbientesAdministrativosRecreativos:parqueAdicionar.html.twig');
//     }
//
//     public function coliseoAdicionarAction(){
//         return $this->render('SieInfraestructuraBundle:AmbientesAdministrativosRecreativos:coliseoAdicionar.html.twig');
//     }
//
//     public function piscinaAdicionarAction(){
//         return $this->render('SieInfraestructuraBundle:AmbientesAdministrativosRecreativos:piscinaAdicionar.html.twig');
//     }
//
//



}
