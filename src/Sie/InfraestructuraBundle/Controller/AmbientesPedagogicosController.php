<?php

namespace Sie\InfraestructuraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\InfraestructuraH4Ambientepedagogico;

class AmbientesPedagogicosController extends Controller
{
	public $session;
	public function __construct() {
        $this->session = new Session();
    }

    public function indexAction() {
    	$infraJuridiccionGeograficaId = $this->session->get('infJurGeoId');
    	$em = $this->getDoctrine()->getManager();
    	// $em->getConnection()->beginTransaction();
    	try {
    		$ambientes = $em->getRepository('SieAppWebBundle:InfraestructuraH4Ambientepedagogico')->findby(array('infraestructuraJuridiccionGeografica'=>$infraJuridiccionGeograficaId));
				// dump($ambientes);die;
    		return $this->render('SieInfraestructuraBundle:AmbientesPedagogicos:index.html.twig',array(
					'ambientes'=>$ambientes,
					'form' => $this->formAdicionar($infraJuridiccionGeograficaId)->createView(),
					'formMain' => $this->createCreateFormMain($infraJuridiccionGeograficaId)->createView()

				));
    	} catch (Exception $e) {

    	}
    }
		/**
		 * [formAdicionar description]
		 * @param  [type] $infraJuridiccionGeograficaId [description]
		 * @return [type]                               [description]
		 */
		private function formAdicionar($infraJuridiccionGeograficaId){
			$form = $this->createFormBuilder()
										->add('infraJuridiccionGeograficaId', 'hidden', array('data'=>$infraJuridiccionGeograficaId))
										->add('adicionar', 'button', array('label'=>'Adicionar', 'attr'=>array('class'=>'', 'onclick'=>'adicionar()' )))
										->getForm()
										;
			return $form;
		}
		/**
		 * Creates a form to create a InfraestructuraH5AmbienteadministrativoAmbiente entity.
		 *
		 * @param InfraestructuraH5AmbienteadministrativoAmbiente $entity The entity
		 *
		 * @return \Symfony\Component\Form\Form The form
		 */
		private function createCreateFormMain($infraJuridiccionGeograficaId){
				$form = $this->createFormBuilder()
					->setAction($this->generateUrl('infra_ambientesPedagogicos_infosave'))
					->add('save', 'submit', array('label' => 'Guardar Cambios', 'attr'=>array('class'=>'btn btn-success-alt')))
					->add('infraJuridiccionGeograficaId', 'hidden', array('data'=>$infraJuridiccionGeograficaId))
					->getForm();
				return $form;
		}
		/**
		 * [adicionarAction add one element]
		 * @param  Request $request [data send]
		 * @return [type]           [a form]
		 */
    public function adicionarAction(Request $request){
			//get the data Send
			$form = $request->get('form');

        $form = $this->createFormBuilder()
                    ->add('ambienteTipo', 'entity', array('label' => 'ambiente', 'class'=>'SieAppWebBundle:InfraestructuraH4AmbienteTipo', 'required' => true,'attr' => array('class' => 'form-control jupper')))
                    ->add('ancho','text',array('label'=>'Ancho','attr'=> array('class'=>'form-control')))
                    ->add('largo','text',array('label'=>'Largo','attr'=> array('class'=>'form-control')))
                    ->add('alto','text',array('label'=>'Alto','attr'=> array('class'=>'form-control')))
                    ->add('estadoGeneral', 'entity', array('label' => 'General del ambiente', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'required' => true,'attr' => array('class' => 'form-control')))
                    ->add('iluminacionNatural', 'entity', array('label' => 'Iluminación natural', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'required' => true,'attr' => array('class' => 'form-control')))
                    ->add('iluminacionElectrica', 'entity', array('label' => 'Iluminación eléctrica', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'required' => true,'attr' => array('class' => 'form-control')))
                    ->add('seguridad', 'entity', array('label' => 'Seguridad', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'required' => true,'attr' => array('class' => 'form-control')))
                    ->add('piso', 'entity', array('label' => 'Piso', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'required' => true,'attr' => array('class' => 'form-control')))
                    ->add('paredes', 'entity', array('label' => 'Paredes', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'required' => true,'attr' => array('class' => 'form-control')))
                    ->add('techo', 'entity', array('label' => 'Techo', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'required' => true,'attr' => array('class' => 'form-control')))
                    ->add('ventanas', 'entity', array('label' => 'Ventanas', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'required' => true,'attr' => array('class' => 'form-control')))
                    ->add('puertas', 'entity', array('label' => 'Puertas', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'required' => true,'attr' => array('class' => 'form-control')))
                    ->add('pintura', 'entity', array('label' => 'Pintura', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'required' => true,'attr' => array('class' => 'form-control')))
                    ->add('cieloRaso', 'entity', array('label' => 'Cielo raso', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'required' => true,'attr' => array('class' => 'form-control')))
                    ->add('capacidad','text',array('label'=>'Capacidad','attr'=> array('class'=>'form-control')))
                    ->add('uso', 'entity', array('label' => 'Uso del ambiente', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'required' => true,'attr' => array('class' => 'form-control')))
										->add('infraJuridiccionGeograficaId', 'hidden', array('data'=>$form['infraJuridiccionGeograficaId']))
										->add('save', 'button', array('label'=>'Guardar', 'attr'=>array('class'=>'btn btn-primary', 'onclick'=>'saveAmbientesPedagogicos()')))
                    ->getForm();
        return $this->render('SieInfraestructuraBundle:AmbientesPedagogicos:adicionar.html.twig',array('form'=>$form->createView()));
    }
		public function infoSaveAction(Request $request){
			$this->messageFlash('success', 'Los datos han sido guardados correctamente...');
			return $this->redirect($this->generateUrl('infra_info_acceder'));
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
		 * [saveAmbientesPedagogicosAction description]
		 * @param  Request $request [description]
		 * @return [type]           [description]
		 */
		public function saveAmbientesPedagogicosAction(Request $request){
			//ge the send data
			$form = $request->get('form');
			//  dump($form);die;
			$em = $this->getDoctrine()->getManager();
			$em->getConnection()->beginTransaction();
			$infraJuridiccionGeograficaId = $this->session->get('infJurGeoId');
			//dump($request);die;

			try {
				$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('Infraestructura_H4_Ambientepedagogico');")->execute();
				//save new row AmbientesPedagogicos
				$infraestructuraH4AmbientepedagogicoObj = new InfraestructuraH4Ambientepedagogico();
				$infraestructuraH4AmbientepedagogicoObj->setN11AmbienteTipo( $em->getRepository('SieAppWebBundle:InfraestructuraH4AmbienteTipo')->find($form['ambienteTipo']) );
				$infraestructuraH4AmbientepedagogicoObj->setN12AmbienteAnchoMts($form['ancho']);
				$infraestructuraH4AmbientepedagogicoObj->setN12AmbienteLargoMts($form['largo']);
				$infraestructuraH4AmbientepedagogicoObj->setN12AmbienteAltoMts($form['alto']);
				$infraestructuraH4AmbientepedagogicoObj->setN14CapacidadAmbiente($form['capacidad']);
			  $infraestructuraH4AmbientepedagogicoObj->setFecharegistro(new \DateTime('now'));
				 $infraestructuraH4AmbientepedagogicoObj->setInfraestructuraJuridiccionGeografica( $em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($infraJuridiccionGeograficaId) );
				 $infraestructuraH4AmbientepedagogicoObj->setN13CielorasoEstadogeneralTipo( $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($form['cieloRaso']) );
				 $infraestructuraH4AmbientepedagogicoObj->setN13PinturaEstadogeneralTipo( $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($form['pintura']) );
				 $infraestructuraH4AmbientepedagogicoObj->setN13PuertasEstadogeneralTipo( $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($form['puertas']) );
				 $infraestructuraH4AmbientepedagogicoObj->setN13VentanasEstadogeneralTipo( $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($form['ventanas']) );
				 $infraestructuraH4AmbientepedagogicoObj->setN13TechoEstadogeneralTipo( $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($form['techo']) );
				 $infraestructuraH4AmbientepedagogicoObj->setN13ParedEstadogeneralTipo( $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($form['paredes']) );
				 $infraestructuraH4AmbientepedagogicoObj->setN13PisoEstadogeneralTipo( $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($form['piso']) );
				 $infraestructuraH4AmbientepedagogicoObj->setN13SeguridadEstadogeneralTipo( $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($form['seguridad']) );
				 $infraestructuraH4AmbientepedagogicoObj->setN13IluminacionelectricaEstadogeneralTipo( $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($form['iluminacionElectrica']) );
				 $infraestructuraH4AmbientepedagogicoObj->setN13IluminacionnaturalEstadogeneralTipo( $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($form['estadoGeneral']) );
				 $infraestructuraH4AmbientepedagogicoObj->setN13AmbienteEstadogeneralTipo( $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($form['estadoGeneral']) );
				 $infraestructuraH4AmbientepedagogicoObj->setN15UsoOrgcurricularTipo( $em->getRepository('SieAppWebBundle:InfraestructuraH4OrgcurricularTipo')->find(1) );

				$em->persist($infraestructuraH4AmbientepedagogicoObj);
				$em->flush();
				// Try and commit the transaction
				$em->getConnection()->commit();

				$ambientes = $em->getRepository('SieAppWebBundle:InfraestructuraH4Ambientepedagogico')->findby(array('infraestructuraJuridiccionGeografica'=>$infraJuridiccionGeograficaId));
				return $this->render('SieInfraestructuraBundle:AmbientesPedagogicos:index.html.twig',array(
					'ambientes'=>$ambientes,
					'form' => $this->formAdicionar($infraJuridiccionGeograficaId)->createView(),
					'formMain' => $this->createCreateFormMain($infraJuridiccionGeograficaId)->createView()

				));

			} catch (Exception $e) {
				$em->getConnection()->rollback();
				echo 'Excepción capturada: ', $ex->getMessage(), "\n";
			}

		}

		public function deleteAmbientesPedagogicosAction(Request $request){
			//get the data send
			$datajson = $request->get('datajson');
			$data = json_decode($datajson,true);
			// create db conexion
			$em = $this->getDoctrine()->getManager();
			$em->getConnection()->beginTransaction();

			// dump($data);die;s
			try {
				$objAmbPedagogicoRemove =  $em->getRepository('SieAppWebBundle:InfraestructuraH4Ambientepedagogico')->find($data['ambientepedagogicoId']);
				$em->remove($objAmbPedagogicoRemove);
				$em->flush();
				$em->getConnection()->commit();

				$infraJuridiccionGeograficaId = $this->session->get('infJurGeoId');
				$ambientes = $em->getRepository('SieAppWebBundle:InfraestructuraH4Ambientepedagogico')->findby(array('infraestructuraJuridiccionGeografica'=>$infraJuridiccionGeograficaId));
				return $this->render('SieInfraestructuraBundle:AmbientesPedagogicos:index.html.twig',array(
					'ambientes'=>$ambientes,
					'form' => $this->formAdicionar($infraJuridiccionGeograficaId)->createView(),
					'formMain' => $this->createCreateFormMain($infraJuridiccionGeograficaId)->createView()

				));
			} catch (Exception $e) {
				$em->getConnection()->rollback();
				echo 'Excepción capturada: ', $ex->getMessage(), "\n";
			}


		}




}
