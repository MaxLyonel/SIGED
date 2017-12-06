<?php

namespace Sie\InfraestructuraBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InfraestructuraH3Servicio;
use Sie\AppWebBundle\Entity\InfraestructuraH3ServicioSaneamiento;

class ServiciosController extends Controller
{
	public $session;
	public function __construct() {
        $this->session = new Session();
    }

    public function indexAction()
    {
    	$infJurGeoId = $this->session->get('infJurGeoId');
    	$em = $this->getDoctrine()->getManager();
    	$em->getConnection()->beginTransaction();
    	try {

            $infH3Ser = $em->getRepository('SieAppWebBundle:InfraestructuraH3Servicio')->findOneBy(array('infraestructuraJuridiccionGeografica'=>$infJurGeoId));
            //dump($infH3Ser);die;
            if(!$infH3Ser){
                $infH3Ser = new InfraestructuraH3Servicio();
                $id = 'new';
            }else{
                $id = $infH3Ser->getId();
            }



            $form = $this->createServiciosForm($infH3Ser,$id);

            return $this->render('SieInfraestructuraBundle:Servicios:index.html.twig',array('form'=>$form->createView()));

    	} catch (Exception $e) {

    	}
    }

    public function createServiciosForm($entity,$id){

        $form = $this->createFormBuilder($entity)
                        ->setAction($this->generateUrl('infra_servicios_create_update'))
                        ->add('id','hidden',array('data'=>$id,'mapped'=>false))
                        /*
                         * 1. Disponibilidad de energia electrica
                         */
                        ->add('n11EsEnergiaelectrica','choice',array(
                                //'label'=>'Si',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisEletrica();','title'=>'1.1 ¿El edificio educativo dispone de energía eléctrica?')
                        ))
                        ->add('n12FuenteElectricaTipo', 'entity', array('label' => 'Fuente eléctrica', 'class'=>'SieAppWebBundle:InfraestructuraH3FuenteElectricaTipo', 'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                                return $er->createQueryBuilder('e')
                                        ->where('e.id in (:ids)')
                                        ->setParameter('ids', array(0,1,2,3,4,5,6));
                            },'empty_value'=>'Seleccionar...','required' => true,'attr' => array('class' => 'form-control jupper','onchange'=>'fuenteElectricaOtro(this.value)','title'=>'1.2 Fuente Eléctrica')))
                        ->add('n12FuenteElectricaOtro','text',array('label'=>'Otro','required'=>false,'attr'=> array('class'=>'form-control jupper','maxlength'=>50,'readonly'=>true,'title'=>'1.2. Otro (Especificar)')))
                        ->add('n13DisponibilidadTipo', 'entity', array('label' => 'Disponibilidad de servicio', 'class'=>'SieAppWebBundle:InfraestructuraH3DisponibilidadTipo', 'required' => true,'attr' => array('class' => 'form-control','title'=>'1.3 Disponibilidad de servicio')))
                        ->add('n14NumeroAmbientesPedagogicos','text',array('label'=>'Ambientes Pedagogicos','attr'=>array('class'=>'form-control','maxlength'=>4,'title'=>'1.4 Ambientes Pedagogicos')))
                        ->add('n14NumeroAmbientesNoPedagogicos','text',array('label'=>'Ambientes no pedagógicos (menos Baños)','attr'=> array('class'=>'form-control','maxlength'=>4,'title'=>'1.4 Ambientes no pedagógicos (menos Baños)')))
                        ->add('n14NumeroBanios','text',array('label'=>'Baños','attr'=> array('class'=>'form-control','maxlength'=>4,'title'=>'1.4 Baños')))

                        ->add('n15NumeroMedidores','text',array('label'=>'Medidores','attr'=> array('class'=>'form-control','maxlength'=>4,'title'=>'1.5 ¿Cuántos medidores de consumo eléctrico existen en el Edificio Educativo?')))
                        ->add('n16NumeroMedidoresFuncionan','text',array('label'=>'¿Funcionan?','attr'=> array('class'=>'form-control','maxlength'=>4,'title'=>'1.6 ¿Funcionan?')))
                        ->add('n16NumeroMedidoresNoFuncionan','text',array('label'=>'¿No funcionan?','attr'=> array('class'=>'form-control','maxlength'=>4,'title'=>'1.6 ¿No funcionan?')))
                        /*
                         * 2. Disponibilidad de servicio agua
                         */
                        ->add('n21EsDiponeAgua','choice',array(
                                'label'=>'Servicio Agua',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisAgua();','title'=>'2.1 ¿El edificio educativo dispone de servicio de agua?')
                        ))
                        ->add('n22MedioAguaTipo', 'entity', array('label' => 'Medio de Agua', 'class'=>'SieAppWebBundle:InfraestructuraH3MedioAguaTipo', 'required' => true,'attr' => array('class' => 'form-control jupper','onchange'=>'suministroAguaOtro(this.value)','title'=>'2.2 El medio o sistema de suministro de agua es por:')))
                        ->add('n22MedioAguaOtro','text',array('label'=>'Otro','attr'=> array('class'=>'form-control jupper','maxlength'=>100,'readonly'=>true,'title'=>'2.2 Otro (Especificar)')))
                        ->add('n23EsCuentaTanqueAgua','choice',array(
                                'label'=>'2.3 ¿Tanque de almacenamiento de agua?',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('title'=>'2.3 ¿Tanque de almacenamiento de agua?')
                        ))
                        ->add('n24EsCuentaBombaAgua','choice',array(
                                'label'=>'2.4 ¿Bomba de agua?',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('title'=>'2.4 ¿Bomba de agua?')
                        ))
                        ->add('n25EsCuentaRedAgua','choice',array(
                                'label'=>'2.5 ¿Red interna de distribución de agua?',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('title'=>'2.5 ¿Red interna de distribución de agua?')
                        ))
                        ->add('n26NumeroAmbientesAgua','text',array('label'=>'Ambientes Agua','attr'=> array('class'=>'form-control','maxlength'=>4,'title'=>'2.6 ¿Número de ambientes que cuentan con servicio de agua, pero no son baños?')))
                        ->add('n27AccesoAguaTipo', 'entity', array('label' => 'Acceso agua', 'class'=>'SieAppWebBundle:InfraestructuraH3AccesoAguaTipo', 'required' => true,'attr' => array('class' => 'form-control jupper','title'=>'2.7 El acceso al servicio de provisión de agua es:')))
                        ->add('n28UsoAguaTipo', 'entity', array('label' => 'Uso agua', 'class'=>'SieAppWebBundle:InfraestructuraH3UsoAguaTipo', 'required' => true,'attr' => array('class' => 'form-control jupper','title'=>'2.8 El agua del EE, es utilizada mayormente para:')))
                        ->add('n29PurificadorAguaTipo', 'entity', array('label' => 'Purificador agua', 'class'=>'SieAppWebBundle:InfraestructuraH3PurificadorAguaTipo', 'required' => true,'attr' => array('class' => 'form-control jupper','title'=>'2.9 ¿Utiliza algún tipo de purificador de agua?')))
                        /*
                         * 3. Saneamiento
                         */
                        ->add('n31EsInstalacionSaneamiento','choice',array(
                                'label'=>'Saneamiento',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisSaneamiento()','title'=>'3.1 ¿El edificio educativo cuenta con instalaciones de saneamiento (baños)?')
                        ))
                        ->add('n33ExcretasEsAlcantarillado','checkbox',array('label'=>'Red de alcantarillado','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n33ExcretasEsSeptica','checkbox',array('label'=>'Cámara séptica','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n33ExcretasEsPozo','checkbox',array('label'=>'Pozo de absorción','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n33ExcretasEsSuperficie','checkbox',array('label'=>'Superficie (calle/quebrada/rio)','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n33ExcretasEsOtro','checkbox',array('label'=>'Otro','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n33ExcretasEsNosabe','checkbox',array('label'=>'No sabe','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n34EsBuenascondiciones','choice',array(
                                'label'=>'Buenas condiciones de luz natural (Se tiene visibilidad con la puerta cerrada)',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('title'=>'3.4 Buenas condiciones de luz natural (Se tiene visibilidad con la puerta cerrada)')
                        ))
                        ->add('n34EsBuenasventilacion','choice',array(
                                'label'=>'Buena ventilación',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('title'=>'3.4 Buena ventilación')
                        ))
                        ->add('n34EsPrivacidad','choice',array(
                                'label'=>'Privacidad (cierre seguro y la construcción garantiza privacidad)',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('title'=>'3.4 Privacidad (cierre seguro y la construcción garantiza privacidad)')
                        ))
                        /*
                         * 4 Eliminacion de basura
                         */
                        ->add('n41EliminacionBasuraTipo', 'entity', array('label' => '', 'class'=>'SieAppWebBundle:InfraestructuraH3EliminacionBasuraTipo', 'required' => true,'attr' => array('class' => 'form-control jupper','onchange'=>'eliminacionBasuraOtro(this.value)','title'=>'4.1 ¿Como se realiza la eliminación de basura?')))
                        ->add('n41EliminacionBasuraOtro','text',array('label'=>'Otro','required'=>false,'attr'=>array('class'=>'form-control jupper','maxlength'=>100,'readonly'=>true,'title'=>'4.1 Otro (Especificar)')))
                        ->add('n42PeriodicidadTipo', 'entity', array('label' => '', 'class'=>'SieAppWebBundle:InfraestructuraH3PeriodicidadTipo', 'required' => true,'attr' => array('class' => 'form-control jupper','title'=>'4.2 Periodicidad de la eliminación de basura')))
                        /*
                         * 5. Otros servicios
                         */
                        ->add('n51EsCentroSalud','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisSalud();','title'=>'5.1 ¿Existe un centro de salud próximo?')
                        ))
                        ->add('n51MetrosCentroSalud','text',array('label'=>'Metros','attr'=>array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','title'=>'5.1 Metros')))
                        ->add('n52EsCentroPolicial','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisPolicial();','title'=>'5.2 ¿Existe un centro policial próximo?')
                        ))
                        ->add('n52MetrosCentroPolicial','text',array('label'=>'Metros','attr'=>array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','title'=>'5.2 Metros')))
                        ->add('n53EsCentroEsparcimiento','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisEsparcimiento();','title'=>'5.3 ¿Existe un centro de esparcimiento próximo?')
                        ))
                        ->add('n53MetrosCentroEsparcimiento','text',array('label'=>'Metros','attr'=>array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','title'=>'5.3 Metros')))
                        ->add('n54EsCentroCultural','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisCultura();','title'=>'5.4 ¿Existe un centro de cultura público próximo?')
                        ))
                        ->add('n54MetrosCentroCultural','text',array('label'=>'Metros','attr'=>array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','title'=>'5.4 Metros')))
                        ->add('n55EsCentroIglesia','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisIglesia();','title'=>'5.5 ¿Existe una iglesia próxima?')
                        ))
                        ->add('n55MetrosCentroIglesia','text',array('label'=>'Metros','attr'=>array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','title'=>'5.5 Metros')))
                        ->add('n56EsCentroInternet','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisInternet();','title'=>'5.6 ¿Existe algún centro de acceso a internet próximo?')
                        ))
                        ->add('n56MetrosCentroInternet','text',array('label'=>'Metros','attr'=>array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','title'=>'5.6 Metros')))
                        ->add('n57EsCentroCorreo','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisCorreo();','title'=>'5.7 ¿Existe un centro de correo próximo?')
                        ))
                        ->add('n57MetrosCentroCorreo','text',array('label'=>'Metros','attr'=>array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','title'=>'5.7 Metros')))
                        ->add('n58EsCentroTelefono','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisTelefono();','title'=>'5.8 ¿Existe teléfono público próximo?')
                        ))
                        ->add('n58MetrosCentroTelefono','text',array('label'=>'Metros','attr'=>array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','title'=>'5.8 Metros')))
                        ->add('n59EsCentroNucleoEducativo','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisNucleo();','title'=>'5.9 ¿La central del núcleo educativo esta próxima?')
                        ))
                        ->add('n59MetrosCentroNucleoEducativo','text',array('label'=>'Metros','attr'=>array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','title'=>'5.9 Metros')))
                        ->add('n510EsCentroRadiocomunicacion','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisRadiocomunicacion();','title'=>'5.10 ¿Existe un correo de radiocomunicación próximo?')
                        ))
                        ->add('n510MetrosCentroRadiocomunicacion','text',array('label'=>'Metros','attr'=>array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','title'=>'5.10 Metros')))
                        ->add('n511EsServicioEnfermeria','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('title'=>'5.11 ¿Servicio de enfermeria?')
                        ))
                        ->add('n512EsServicioInternet','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('title'=>'5.12 ¿Servicio de internet?')
                        ))
                        ->add('n513EsServicioTelecentro','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('title'=>'5.13 ¿Telecentro?')
                        ))
                        ->add('n514EsServicioGas','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('title'=>'5.14 ¿Gas?')
                        ))
                        ->add('n515EsSenalDiscapacidad','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisSenalizacion();','title'=>'5.15 ¿Cuenta con señalización vial para personas con discapacidad?')
                        ))
                        ->add('n515EsSenaliomaTipo', 'entity', array('label' => '', 'class'=>'SieAppWebBundle:InfraestructuraH3IdiomaTipo', 'required' => true,'attr' => array('class' => 'form-control jupper','title'=>'5.15 Idioma señalización')))
                        ->add('n516EsFuncionaCee','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('title'=>'5.16 ¿Funciona en Centro de Educación Especial?')
                        ))
                        ->add('n517EsRampasacceso','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisRampas();','title'=>'5.17 ¿Posee rampas de acceso par silla de ruedas?')
                        ))
                        ->add('n517RampasaccesoEnlugarTipo', 'entity', array('label' => '', 'class'=>'SieAppWebBundle:InfraestructuraH3EnlugarTipo', 'required' => true,'attr' => array('class' => 'form-control jupper','title'=>'5.17 En donde?')))
                        ->add('n518EsGuiasDiscapicidadVisual','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('onchange'=>'enaDisPasamanos();','title'=>'5.18 ¿Posee pasamanos/guias para personas con discapacidad visual?')
                        ))
                        ->add('n518GuiasDiscapicidadEnlugarTipo', 'entity', array('label' => '', 'class'=>'SieAppWebBundle:InfraestructuraH3EnlugarTipo', 'required' => true,'attr' => array('class' => 'form-control jupper','title'=>'5.18 En donde?')))
                        ->add('n519EsAmbientesPedagogicosDiscapacidad','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'empty_data'=>null,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('title'=>'5.19 ¿Posee ambientes pedagógicos apropiados para estudiantes con discapacidad?')
                        ))

                        ->add('guardar','submit',array('label'=>'Guardar Cambios','attr'=>array('class'=>'btn btn-success-alt')))

                        ->getForm();

        return $form;
    }

    public function createUpdateAction(Request $request){
        try{
            $infJurGeoId = $this->session->get('infJurGeoId');
            $em = $this->getDoctrine()->getManager();
            $formulario = $request->get('form');
            //dump($request);die;
            
            if($formulario['id'] == 'new'){ 
                $entity = new InfraestructuraH3Servicio();
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h3_servicio');")->execute();
            }else{
                $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH3Servicio')->find($formulario['id']);
            }

            $form = $this->createServiciosForm($entity,$formulario['id']);
            $form->handleRequest($request);

            if ($form->isValid()) {

                if($formulario['id'] == 'new'){

                    $entity->setInfraestructuraJuridiccionGeografica($em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($infJurGeoId));
                    
                    $entity->setFecharegistro(new \DateTime('now'));

                }
                /*
                    VERFICAMOS SI ALGUNAS VARIABLES NO EXISTEN
                */
                // 1
                if(!isset($formulario['n12FuenteElectricaTipo'])){
                    $entity->setN12FuenteElectricaTipo($em->getRepository('SieAppWebBundle:InfraestructuraH3FuenteElectricaTipo')->find(0));
                }
                if(!isset($formulario['n13DisponibilidadTipo'])){
                    $entity->setN13DisponibilidadTipo($em->getRepository('SieAppWebBundle:InfraestructuraH3DisponibilidadTipo')->find(0));
                }

                // 2
                if(!isset($formulario['n22MedioAguaTipo'])){
                    $entity->setN22MedioAguaTipo($em->getRepository('SieAppWebBundle:InfraestructuraH3MedioAguaTipo')->find(0));
                }
                if(!isset($formulario['n23EsCuentaTanqueAgua'])){
                    $entity->setN23EsCuentaTanqueAgua(null);
                }
                if(!isset($formulario['n24EsCuentaBombaAgua'])){
                    $entity->setN24EsCuentaBombaAgua(null);
                }
                if(!isset($formulario['n25EsCuentaRedAgua'])){
                    $entity->setN25EsCuentaRedAgua(null);
                }
                if(!isset($formulario['n27AccesoAguaTipo'])){
                    $entity->setN27AccesoAguaTipo($em->getRepository('SieAppWebBundle:InfraestructuraH3AccesoAguaTipo')->find(0));  
                }
                if(!isset($formulario['n28UsoAguaTipo'])){
                    $entity->setN28UsoAguaTipo($em->getRepository('SieAppWebBundle:InfraestructuraH3UsoAguaTipo')->find(0));
                }
                if(!isset($formulario['n29PurificadorAguaTipo'])){
                    $entity->setN29PurificadorAguaTipo($em->getRepository('SieAppWebBundle:InfraestructuraH3PurificadorAguaTipo')->find(0));
                }

                // 3
                if(!isset($formulario['n34EsBuenascondiciones'])){
                    $entity->setN34EsBuenascondiciones(null);
                }
                if(!isset($formulario['n34EsBuenasventilacion'])){
                    $entity->setN34EsBuenasventilacion(null);
                }
                if(!isset($formulario['n34EsPrivacidad'])){
                    $entity->setN34EsPrivacidad(null);
                }

                // 5
                if(!isset($formulario['n515EsSenaliomaTipo'])){
                    $entity->setN515EsSenaliomaTipo($em->getRepository('SieAppWebBundle:InfraestructuraH3IdiomaTipo')->find(0));
                }
                if(!isset($formulario['n517RampasaccesoEnlugarTipo'])){
                    $entity->setN517RampasaccesoEnlugarTipo($em->getRepository('SieAppWebBundle:InfraestructuraH3EnlugarTipo')->find(0));
                }
                if(!isset($formulario['n518GuiasDiscapicidadEnlugarTipo'])){
                    $entity->setN518GuiasDiscapicidadEnlugarTipo($em->getRepository('SieAppWebBundle:InfraestructuraH3EnlugarTipo')->find(0));
                }

                $em->persist($entity);
                $em->flush();

                // REgistro de baterias de Baños

                $saneamiento = $em->getRepository('SieAppWebBundle:InfraestructuraH3ServicioSaneamiento')->findBy(array('infraestructuraH3Servicio' => $entity->getId()));
                if ($saneamiento) {
                    foreach ($saneamiento as $s) {
                        $em->remove($em->getRepository('SieAppWebBundle:InfraestructuraH3ServicioSaneamiento')->find($s->getId()));
                        $em->flush();
                    }
                }


                $banioTipo = $request->get('banioTipo');
                $area = $request->get('area');
                $pisos= $request->get('pisos');
                $paredes = $request->get('paredes');
                $cielos = $request->get('cielos');
                $techos = $request->get('techos');

                $banioConagua = $request->get('banioConagua');
                $banioSinagua = $request->get('banioSinagua');
                $literaFunciona = $request->get('literaFunciona');
                $literaNoFunciona = $request->get('literaNoFunciona');
                $inodoroFunciona = $request->get('inodoroFunciona');
                $inodoroNoFunciona = $request->get('inodoroNoFunciona');
                $urinarioFunciona = $request->get('urinarioFunciona');
                $urinarioNoFunciona = $request->get('urinarioNoFunciona');
                $lavamanoFunciona = $request->get('lavamanoFunciona');
                $lavamanoNoFunciona = $request->get('lavamanoNoFunciona');
                $duchaFunciona = $request->get('duchaFunciona');
                $duchaNoFunciona = $request->get('duchaNoFunciona');

                /*dump($paredes);
                dump($pisos);
                dump($cielos);
                dump($techos);

                //die;*/


                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h3_servicio_saneamiento');")->execute();
                for ($i=0; $i < count($banioTipo); $i++) { 
                    $newServSan  = new InfraestructuraH3ServicioSaneamiento();
                    $newServSan->setInfraestructuraH3Servicio($entity);
                    $newServSan->setN3servicioBanioTipo($em->getRepository('SieAppWebBundle:InfraestructuraH3BanoTipo')->find($banioTipo[$i]));
                    $newServSan->setN3Areatotalm2($area[$i]);
                    $newServSan->setN3ParedEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($paredes[$i]));
                    $newServSan->setN3TechoEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($techos[$i]));
                    $newServSan->setN3PisoEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($pisos[$i]));
                    $newServSan->setN3CieloEstadogeneralTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->find($cielos[$i]));

                    $newServSan->setN3BanioConagua($banioConagua[$i]);
                    $newServSan->setN3BanioSinAgua($banioSinagua[$i]);
                    $newServSan->setN3LiteraFunciona($literaFunciona[$i]);
                    $newServSan->setN3LiteraNofunciona($literaNoFunciona[$i]);
                    $newServSan->setN3InodoroFunciona($inodoroFunciona[$i]);
                    $newServSan->setN3InodoroNoFunciona($inodoroNoFunciona[$i]);
                    $newServSan->setN3UrinarioFunciona($urinarioFunciona[$i]);
                    $newServSan->setN3UrinarioNofunciona($urinarioNoFunciona[$i]);
                    $newServSan->setN3LavamanoFunciona($lavamanoFunciona[$i]);
                    $newServSan->setN3LavamanoNofunciona($lavamanoNoFunciona[$i]);
                    $newServSan->setN3DuchaFunciona($duchaFunciona[$i]);
                    $newServSan->setN3DuchaNofunciona($duchaNoFunciona[$i]);

                    $newServSan->setObs('');
                    $newServSan->setFecharegistro(new \DateTime('now'));

                    $em->persist($newServSan);
                    $em->flush();
                }


                return $this->redirectToRoute('infra_info_acceder');
            }

            return $this->render('SieInfraestructuraBundle:Servicios:index.html.twig',array('form'=>$form->createView()));

        }catch(Exception $e){

        }
        
    }

    public function bateriasbanosAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $id = $request->get('id');
            $banios = array();
            if($id != 'new'){
                $bateriasbanios = $em->getRepository('SieAppWebBundle:InfraestructuraH3ServicioSaneamiento')->findBy(array('infraestructuraH3Servicio'=>$id));
                foreach ($bateriasbanios as $be) {
                    $banios[] = array(
                        'banioTipo'=>$be->getN3ServicioBanioTipo()->getId(),
                        'area'=>$be->getN3Areatotalm2(),
                        'piso'=>$be->getN3PisoEstadogeneralTipo()->getId(),
                        'cielo'=>$be->getN3CieloEstadogeneralTipo()->getId(),
                        'pared'=>$be->getN3ParedEstadogeneralTipo()->getId(),
                        'techo'=>$be->getN3TechoEstadogeneralTipo()->getId(),
                        'banioConagua' => $be->getN3BanioConagua(),
                        'banioSinagua' => $be->getN3BanioSinagua(),
                        'literaFunciona' => $be->getN3LiteraFunciona(),
                        'literaNoFunciona' => $be->getN3LiteraNoFunciona(),
                        'inodoroFunciona' => $be->getN3InodoroFunciona(),
                        'inodoroNoFunciona' => $be->getN3InodoroNoFunciona(),
                        'urinarioFunciona' => $be->getN3UrinarioFunciona(),
                        'urinarioNoFunciona' => $be->getN3UrinarioNoFunciona(),
                        'lavamanoFunciona' => $be->getN3LavamanoFunciona(),
                        'lavamanoNoFunciona' => $be->getN3LavamanoNoFunciona(),
                        'duchaFunciona' => $be->getN3DuchaFunciona(),
                        'duchaNoFunciona' => $be->getN3DuchaNoFunciona(),
                    );
                }
            }else{
                $banios = null;
            }

            $servicioBanioTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH3BanoTipo')->findAll();
            $banioTipo = array();
            foreach ($servicioBanioTipo as $st) {
                $banioTipo[$st->getId()] = $st->getInfraestructuraBano();
            }

            $estadoGeneral = $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadogeneralTipo')->findAll();
            $estado = array();
            foreach ($estadoGeneral as $eg) {
                $estado[$eg->getId()] = $eg->getInfraestructuraEstadogeneral();
            }

            $response = new JsonResponse();
            $em->getConnection()->commit();
            return $response->setData(array(
                'banios' => $banios,
                'banioTipo'=>$banioTipo,
                'estado'=>$estado
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }
}
