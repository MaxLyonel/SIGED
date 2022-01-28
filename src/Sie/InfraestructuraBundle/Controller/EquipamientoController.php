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
use Sie\AppWebBundle\Entity\InfraestructuraH6ServicioAlimentacionTipo;
use Sie\AppWebBundle\Entity\InfraestructuraH6MobiliarioTipo;
use Sie\AppWebBundle\Entity\InfraestructuraH6MobiliarioAdicionalTipo;
use Sie\AppWebBundle\Entity\InfraestructuraH6EquipamientoVivienda;
use Sie\AppWebBundle\Entity\InfraestructuraH6EquipamientoDormitorio;
use Sie\AppWebBundle\Entity\InfraestructuraH6EquipamientoAlimentacion;
use Sie\AppWebBundle\Entity\InfraestructuraH6EquipamientoPedagogico;
use Sie\AppWebBundle\Entity\InfraestructuraH6EquipamientoPedagogicoAdicional;

use Sie\AppWebBundle\Form\InfraestructuraH5AmbienteadministrativoAmbienteType;


class EquipamientoController extends Controller
{
	public $session;
	public function __construct() {
        $this->session = new Session();
    }
		/**
		 * [indexAction description]
		 * @return [type] [description]
		 */
    public function indexAction(){
    	$infraJuridiccionGeograficaId = $this->session->get('infJurGeoId');
    	$em = $this->getDoctrine()->getManager();
    	$em->getConnection()->beginTransaction();
    	try {
    		$equipamiento = $em->getRepository('SieAppWebBundle:InfraestructuraH6Equipamiento')->findOneBy(array('infraestructuraJuridiccionGeografica'=>$infraJuridiccionGeograficaId));

            $viviendas = null;
            $dormitorios = null;
            $alimentacion = null;
            $mobiliario = null;
            $mobiliarioAdicional = null;

            if($equipamiento){
								$id = $equipamiento->getId();
                $viviendas = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoVivienda')->findby(array('infraestructuraH6Equipamiento'=>$equipamiento->getId()));
                $dormitorios = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoDormitorio')->findby(array('infraestructuraH6Equipamiento'=>$equipamiento->getId()));
                $alimentacion = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoAlimentacion')->findby(array('infraestructuraH6Equipamiento'=>$equipamiento->getId()));
                $mobiliario = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoPedagogico')->findby(array('infraestructuraH6Equipamiento'=>$equipamiento->getId()));
                $mobiliarioAdicional = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoPedagogicoAdicional')->findby(array('infraestructuraH6Equipamiento'=>$equipamiento->getId()));

            }else{
							$id = 'new';
						}
				$form = $this->createCreateFormMain($id,$infraJuridiccionGeograficaId);
    		return $this->render('SieInfraestructuraBundle:Equipamiento:index.html.twig',array(
                'viviendas'=>$viviendas,
                'dormitorios'=>$dormitorios,
                'alimentacion'=>$alimentacion,
                'moviliario'=>$mobiliario,
                'moviliarioAdicional'=>$mobiliarioAdicional,
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
							->setAction($this->generateUrl('infra_equipamiento_infosave'))
							->add('save', 'submit', array('label' => 'Guardar Cambios', 'attr'=>array('class'=>'btn btn-success-alt')))
							->add('infraJuridiccionGeograficaId', 'hidden', array('data'=>$infraJuridiccionGeograficaId))
							->add('id', 'hidden', array('data'=>$id))
							->getForm();
						return $form;
				}
				/**
				 * [infoCompleteAction description]
				 * @param  Request $request [description]
				 * @return [type]           [description]
				 */
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
					//edit or new rows
					// if($id != 'new'){
					// 	//find compete info
						// $ambientes    = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoVivienda')->findby(array('infraestructuraH5Ambienteadministrativo'=>$id));

						$dataVivienda = array();
						$dataDormitorios = array();
						$dataAlimientacion = array();
						$dataMobiliario = array();
						$dataMobiliariosAdicional = array();
						$arrAmbientes = array();
						$arrEquipamiento = array();
						$arrMaterialPiso = array();
						$arrMaterialPared = array();
						$arrTecho = array();
						$arrEstadogeneral = array();
						$arrServicio = array();
						$arrMobiliario = array();
						$arrMobiliarioAdi = array();


							if($id != 'new'){
								$viviendas = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoVivienda')->findby(array('infraestructuraH6Equipamiento'=>$id));
								if($viviendas){
									foreach ($viviendas as $vivienda) {
										# code...
										$dataVivienda[$vivienda->getId()][]=$vivienda->getN1ParedEstadogeneralTipo()->getId();
										$dataVivienda[$vivienda->getId()][]=$vivienda->getN1TechoEstadogeneralTipo()->getId();
										$dataVivienda[$vivienda->getId()][]=$vivienda->getN1PisoEstadogeneralTipo()->getId();
										$dataVivienda[$vivienda->getId()][]=$vivienda->getN1CieloEstadogeneralTipo()->getId();
										$dataVivienda[$vivienda->getId()][]=$vivienda->getN1Numeroambientes();
										$dataVivienda[$vivienda->getId()][]=$vivienda->getN1Numerohambientes();
										$dataVivienda[$vivienda->getId()][]=$vivienda->getn1EsBanio();
										$dataVivienda[$vivienda->getId()][]=$vivienda->getN1EsDucha();
										$dataVivienda[$vivienda->getId()][]=$vivienda->getN1EsCocina();
										$dataVivienda[$vivienda->getId()][]=$vivienda->getN1Aream2();
									}
								}

								$dormitorios = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoDormitorio')->findby(array('infraestructuraH6Equipamiento'=>$id));

								if($dormitorios){
									foreach ($dormitorios as $dormitorio) {
										# code...
										$dataDormitorios[$dormitorio->getId()][]=$dormitorio->getN2DormitorioGeneroTipoId();
										$dataDormitorios[$dormitorio->getId()][]=$dormitorio->getN2CantidadDormitorios();
										$dataDormitorios[$dormitorio->getId()][]=$dormitorio->getN2ParedEstadogeneralTipo()->getId();
										$dataDormitorios[$dormitorio->getId()][]=$dormitorio->getN2TechoEstadogeneralTipo()->getId();
										$dataDormitorios[$dormitorio->getId()][]=$dormitorio->getN2PisoEstadogeneralTipo()->getId();
										$dataDormitorios[$dormitorio->getId()][]=$dormitorio->getN2CieloEstadogeneralTipo()->getId();
										$dataDormitorios[$dormitorio->getId()][]=$dormitorio->getN2Camaliteras();
										$dataDormitorios[$dormitorio->getId()][]=$dormitorio->getN2Camasimples();
										$dataDormitorios[$dormitorio->getId()][]=$dormitorio->getN2Camaotro();
										$dataDormitorios[$dormitorio->getId()][]=$dormitorio->getN1Aream2();
									}
								}

									$alimentacion = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoAlimentacion')->findby(array('infraestructuraH6Equipamiento'=>$id));


									if($alimentacion){
										foreach ($alimentacion as $alimento) {

										$dataAlimientacion[$alimento->getId()][]=$alimento->getN3ServicioAlimentacionTipo()->getId();
										$dataAlimientacion[$alimento->getId()][]=$alimento->getN3NroAmbientes();
										$dataAlimientacion[$alimento->getId()][]=$alimento->getN3AmbientesEstadogeneralTipo()->getId();
										}
									}

							//
								$mobiliarios = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoPedagogico')->findby(array('infraestructuraH6Equipamiento'=>$id));

								if($mobiliarios){
									foreach ($mobiliarios as $mobiliario) {

										$dataMobiliario[$mobiliario->getId()][]=$mobiliario->getN4MobiliarioTipo()->getId();
										$dataMobiliario[$mobiliario->getId()][]=$mobiliario->getN4NumeroAula();
										$dataMobiliario[$mobiliario->getId()][]=$mobiliario->getN4NumeroTaller();
										$dataMobiliario[$mobiliario->getId()][]=$mobiliario->getN4NumeroLaboratorio();
										$dataMobiliario[$mobiliario->getId()][]=$mobiliario->getN4NumeroBiblioteca();
										$dataMobiliario[$mobiliario->getId()][]=$mobiliario->getN4NumeroSala();
										$dataMobiliario[$mobiliario->getId()][]=$mobiliario->getN4MobiliarioEstadogeneralTipo()->getId();
									}
								}

							//
							$mobiliariosAdicional = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoPedagogicoAdicional')->findby(array('infraestructuraH6Equipamiento'=>$id));

								if($mobiliariosAdicional){
									foreach ($mobiliariosAdicional as $mobiliarioAdicional) {
										# code...
										$dataMobiliariosAdicional[$mobiliarioAdicional->getId()][]=$mobiliarioAdicional->getN5MobiliarioAdicionalTipo()->getId();
										$dataMobiliariosAdicional[$mobiliarioAdicional->getId()][]=$mobiliarioAdicional->getN5Cantidad();
										$dataMobiliariosAdicional[$mobiliarioAdicional->getId()][]=$mobiliarioAdicional->getN5EquipamientoAdicionaEstadogeneralTipo()->getId();

									}
								}



							}else{

							}


							//get InfraestructuraH5AmbienteadministrativoTipo info clafificator
							$infraestructuraH5AmbienteadministrativoTipoObj = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteadministrativoTipo')->findAll();

							foreach ($infraestructuraH5AmbienteadministrativoTipoObj as $value) {
								$arrAmbientes[$value->getId()] = $value->getInfraestructuraAmbiente();
							}
							//get InfraestructuraInmobiliario type
							$infraestructurah5inmobiliariotipoObj = $em->getRepository('SieAppWebBundle:InfraestructuraH5InmobiliarioTipo')->findAll();

							foreach ($infraestructurah5inmobiliariotipoObj as $value) {
								$arrEquipamiento[$value->getId()] = $value->getInfraestructuraInmobiliario();
							}

							// $pisoTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH2PisoMaterialTipo')->findAll();
							$pisoTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH5MaterialPisoTipo')->findAll();
							foreach ($pisoTipo as $piso) {
									// $arrMaterialPiso[$piso->getId()] = $piso->getInfraestructuraPisoMaterial();
									$arrMaterialPiso[$piso->getId()] = $piso->getInfraestructuraMaterialPiso();
							}
							$paredTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH2ParedMaterialTipo')->findAll();

							foreach ($paredTipo as $pared) {
									$arrMaterialPared[$pared->getId()] = $pared->getInfraestructuraParedMaterialTipo();
							}
							$techoTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH2TechoMaterialTipo')->findAll();
							foreach ($techoTipo as $techo) {
									$arrTecho[$techo->getId()] = $techo->getInfraestructuraTechoMaterialTipo();
							}

							$estadogeneralTipo = $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->findAll();
							foreach ($estadogeneralTipo as $estadogeneralT) {
									$arrEstadogeneral[$estadogeneralT->getId()] = $estadogeneralT->getInfraestructuraEstadogeneral();
							}
							$servicioTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH6ServicioAlimentacionTipo')->findAll();
							foreach ($servicioTipo as $servicio) {
									$arrServicio[$servicio->getId()] = $servicio->getInfraestructuraServicioAlimentacion();
							}
							$mobiliarioTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH6MobiliarioTipo')->findAll();
							foreach ($mobiliarioTipo as $mobiliario) {
									$arrMobiliario[$mobiliario->getId()] = $mobiliario->getInfraestructuraMobiliario();
							}
							$mobiliarioAdiTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH6MobiliarioAdicionalTipo')->findAll();
							foreach ($mobiliarioAdiTipo as $mobiliarioAdi) {
									$arrMobiliarioAdi[$mobiliarioAdi->getId()] = $mobiliarioAdi->getInfraestructuraMobiliarioAdicional();
							}

					//return the values to build
					$response = new JsonResponse();
					return $response->setData(array(
						'ambientesAdm'    => $arrAmbientes,
						'equipamientosAdm'=> $arrEquipamiento,
						'materialpiso' => $arrMaterialPiso,
						'estadoGeneral' => $arrEstadogeneral,
						'yesnobool' => array('0'=>'No', '1'=>'Si'),
						'generoDataType' => array('1'=>'Femenino', '2'=>'Masculino'),
						'servicioTipo' => $arrServicio,
						'mobiliarioTipo' => $arrMobiliario,
						'arrMobiliarioAdi' => $arrMobiliarioAdi,
						'dataVivienda' => $dataVivienda,
						'dataDormitorios' => $dataDormitorios,
						'dataAlimientacion' => $dataAlimientacion,
						'dataMobiliario' => $dataMobiliario,
						'dataMobiliariosAdicional' => $dataMobiliariosAdicional,

					));
				}
				/**
				 * [infoSaveAction description]
				 * @param  Request $request [description]
				 * @return [type]           [description]
				 */
				public function infoSaveAction(Request $request){

					//get the values send
					$form = $request->get('form');
					$formVivienda = $request->get('formVivienda');
					$formDormitorios = $request->get('formDormitorios');
					$formServicios = $request->get('formServicios');
					$formMobiliario = $request->get('formMobiliario');
					$formMobiliarioAdicional = $request->get('formMobiliarioAdicional');
					// create DB conexion
					$em = $this->getDoctrine()->getManager();
					$em->getConnection()->beginTransaction();

					try {
						if($form['id']=='new'){
							// save the new info on forms
							$this->saveAmbientesAdministrativosRecreativos($form, $formVivienda, $formDormitorios, $formServicios, $formMobiliario, $formMobiliarioAdicional);
						}else{
							// remove the all data
							$this->removeAmbientesAdministrativosRecreativos($form);
							// save the new info on form
							$this->saveAmbientesAdministrativosRecreativos($form, $formVivienda, $formDormitorios, $formServicios, $formMobiliario, $formMobiliarioAdicional);
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

					$viviendas = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoVivienda')->findby(array('infraestructuraH6Equipamiento'=>$id));
					$dormitorios = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoDormitorio')->findby(array('infraestructuraH6Equipamiento'=>$id));
					$alimentacion = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoAlimentacion')->findby(array('infraestructuraH6Equipamiento'=>$id));
					$mobiliario = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoPedagogico')->findby(array('infraestructuraH6Equipamiento'=>$id));
					$mobiliarioAdicional = $em->getRepository('SieAppWebBundle:InfraestructuraH6EquipamientoPedagogicoAdicional')->findby(array('infraestructuraH6Equipamiento'=>$id));

					//remove elements if exists
					if($viviendas)
						$this->removeObjectData($viviendas);
					if($dormitorios)
						$this->removeObjectData($dormitorios);
					if($alimentacion)
						$this->removeObjectData($alimentacion);
					if($mobiliario)
						$this->removeObjectData($mobiliario);
					if($mobiliarioAdicional)
						$this->removeObjectData($mobiliarioAdicional);
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

				/**
				 * [saveAmbientesAdministrativosRecreativos description]
				 * @param  [type] $form                    [description]
				 * @param  [type] $formVivienda            [description]
				 * @param  [type] $formDormitorios         [description]
				 * @param  [type] $formServicios           [description]
				 * @param  [type] $formMobiliario          [description]
				 * @param  [type] $formMobiliarioAdicional [description]
				 * @return [type]                          [description]
				 */
				private function saveAmbientesAdministrativosRecreativos($form, $formVivienda, $formDormitorios, $formServicios, $formMobiliario, $formMobiliarioAdicional){
					//creat DB conexion
					$em = $this->getDoctrine()->getManager();

					$objViviendas = null;
					$objDormitorios = null;
					$objServicios = null;
					$objMobiliario = null;
					$objMobiliarioAdicional = null;

					$id = $form['id'];
					if($id == 'new'){
						$objEquipamientoNew = new InfraestructuraH6Equipamiento();
						$objEquipamientoNew->setObs('new');
						$objEquipamientoNew->setFecharegistro(new \DateTime('now'));
						$objEquipamientoNew->setInfraestructuraJuridiccionGeografica($em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($form['infraJuridiccionGeograficaId']));
						$em->persist($objEquipamientoNew);
						$em->flush();
						$id = $objEquipamientoNew->getId();
					}

					// existe info $formVivienda save the data
					if($formVivienda){
						$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('Infraestructura_H6_Equipamiento_Vivienda');")->execute();
						foreach ($formVivienda as $key => $value) {
							$objViviendaNew = new InfraestructuraH6EquipamientoVivienda();
							# code...
							$objViviendaNew->setN1ParedEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($value[0]));
							$objViviendaNew->setN1TechoEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($value[1]));
							$objViviendaNew->setN1PisoEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($value[2]));
							$objViviendaNew->setN1CieloEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($value[3]));
							$objViviendaNew->setN1Numeroambientes($value[4]);
							$objViviendaNew->setN1Numerohambientes($value[5]);
							$objViviendaNew->setn1EsBanio($value[6]);
							$objViviendaNew->setN1EsDucha($value[7]);
							$objViviendaNew->setN1EsCocina($value[8]);
							$objViviendaNew->setN1Aream2($value[9]);
							$objViviendaNew->setFecharegistro(new \DateTime('now'));
							$objViviendaNew->setInfraestructuraH6Equipamiento($em->getRepository('SieAppWebBundle:InfraestructuraH6Equipamiento')->find($id));
							$em->persist($objViviendaNew);
							$em->flush();
						}
					}

					// existe info $formDormitorios save the data
					if($formDormitorios){
						$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('Infraestructura_H6_Equipamiento_Dormitorio');")->execute();
						foreach ($formDormitorios as $key => $dormitorio) {
							$objEquipamiento = new InfraestructuraH6EquipamientoDormitorio();
							# code...

							$objEquipamiento->setN2DormitorioGeneroTipoId($dormitorio[0]);
							$objEquipamiento->setN2CantidadDormitorios($dormitorio[1]);
							$objEquipamiento->setN2ParedEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($dormitorio[2]));
							$objEquipamiento->setN2TechoEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($dormitorio[3]));
							$objEquipamiento->setN2PisoEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($dormitorio[4]));
							$objEquipamiento->setN2CieloEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($dormitorio[5]));
							$objEquipamiento->setN2Camaliteras($dormitorio[6]);
							$objEquipamiento->setN2Camasimples($dormitorio[7]);
							$objEquipamiento->setN2Camaotro($dormitorio[8]);
							$objEquipamiento->setN1Aream2($dormitorio[9]);
							# set malo data
							$objEquipamiento->setFecharegistro(new \DateTime('now'));
							# set malo data
							$objEquipamiento->setInfraestructuraH6Equipamiento($em->getRepository('SieAppWebBundle:InfraestructuraH6Equipamiento')->find($id));
							$em->persist($objEquipamiento);
							$em->flush();
						}
					}

					// // existe info $formCanchas save the data
					if($formServicios){
						$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('Infraestructura_H6_Equipamiento_Alimentacion');")->execute();
						foreach ($formServicios as $key => $servicio) {
							$objServicios = new InfraestructuraH6EquipamientoAlimentacion();
							# set setInfraestructuraH5Ambienteadministrativo data
							$objServicios->setInfraestructuraH6Equipamiento($em->getRepository('SieAppWebBundle:InfraestructuraH6Equipamiento')->find($id));
							$objServicios->setN3ServicioAlimentacionTipo($em->getRepository('SieAppWebBundle:InfraestructuraH6ServicioAlimentacionTipo')->find($servicio[0]));
							$objServicios->setN3NroAmbientes($servicio[1]);
							$objServicios->setN3AmbientesEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($servicio[2]));
							$objServicios->setFecharegistro(new \DateTime('now'));
							$em->persist($objServicios);
							$em->flush();
						}
					}
					// // existe info $formCanchas save the data
					if($formMobiliario){
						$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('Infraestructura_H6_Equipamiento_Pedagogico');")->execute();
						foreach ($formMobiliario as $key => $mobiliario) {
							$objMobiliario = new InfraestructuraH6EquipamientoPedagogico();
							$objMobiliario->setInfraestructuraH6Equipamiento($em->getRepository('SieAppWebBundle:InfraestructuraH6Equipamiento')->find($id));
							$objMobiliario->setN4MobiliarioTipo($em->getRepository('SieAppWebBundle:InfraestructuraH6MobiliarioTipo')->find($mobiliario[0]));
							$objMobiliario->setN4NumeroAula($mobiliario[1]);
							$objMobiliario->setN4NumeroTaller($mobiliario[2]);
							$objMobiliario->setN4NumeroLaboratorio($mobiliario[3]);
							$objMobiliario->setN4NumeroBiblioteca($mobiliario[4]);
							$objMobiliario->setN4NumeroSala($mobiliario[5]);
							$objMobiliario->setN4MobiliarioEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($mobiliario[6]));
							$objMobiliario->setFecharegistro(new \DateTime('now'));
							$em->persist($objMobiliario);
							$em->flush();
						}
					}
					if($formMobiliarioAdicional){
						$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('Infraestructura_H6_Equipamiento_Pedagogico_Adicional');")->execute();
						foreach ($formMobiliarioAdicional as $key => $mobiliarioAdi) {
							$objMobiliarioAdicional = new InfraestructuraH6EquipamientoPedagogicoAdicional();
							$objMobiliarioAdicional->setInfraestructuraH6Equipamiento($em->getRepository('SieAppWebBundle:InfraestructuraH6Equipamiento')->find($id));
							$objMobiliarioAdicional->setN5MobiliarioAdicionalTipo($em->getRepository('SieAppWebBundle:InfraestructuraH6MobiliarioAdicionalTipo')->find($mobiliarioAdi[0]));
							$objMobiliarioAdicional->setN5Cantidad($mobiliarioAdi[1]);
							$objMobiliarioAdicional->setN5EquipamientoAdicionaEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($mobiliarioAdi[2]));
							$objMobiliarioAdicional->setFecharegistro(new \DateTime('now'));
							$em->persist($objMobiliarioAdicional);
							$em->flush();
						}
					}
					return 'ok';
				}

}
