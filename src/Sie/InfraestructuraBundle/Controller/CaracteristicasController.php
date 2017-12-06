<?php

namespace Sie\InfraestructuraBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InfraestructuraH2Caracteristica;
use Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificados;

class CaracteristicasController extends Controller
{
	public $session;
    public $infJurGeoId;
	public function __construct() {
        $this->session = new Session();
        $this->infJurGeoId = $this->session->get('infJurGeoId');
    }

    public function indexAction()
    {
    	$infJurGeoId = $this->session->get('infJurGeoId');
    	$em = $this->getDoctrine()->getManager();
    	$em->getConnection()->beginTransaction();
    	try {
            $infH2Car = $em->getRepository('SieAppWebBundle:InfraestructuraH2Caracteristica')->findOneBy(array('infraestructuraJuridiccionGeografica'=>$infJurGeoId));
            if(!$infH2Car){
                $infH2Car = new InfraestructuraH2Caracteristica();
                $id = 'new';
            }else{
                $id = $infH2Car->getId();
            }

            $form = $this->createCaracteriticasForm($infH2Car, $id);

            return $this->render('SieInfraestructuraBundle:Caracteristicas:index.html.twig',array('form'=>$form->createView()));

    	} catch (Exception $e) {

    	}
    }

    public function createCaracteriticasForm($entity, $id){
        $em = $this->getDoctrine()->getManager();
        $infrJurGeo = $em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($this->infJurGeoId);
        $cordX = $infrJurGeo->getJuridiccionGeografica()->getCordX();
        $cordY = $infrJurGeo->getJuridiccionGeografica()->getCordY();

        $jurisdiccion = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($infrJurGeo->getJuridiccionGeografica()->getId());
        $latitud = $jurisdiccion->getCordx();
        $longitud = $jurisdiccion->getCordy();

        $localidad = $em->getRepository('SieAppWebBundle:LugarTipo')->find($jurisdiccion->getLugarTipoIdLocalidad2012());
        $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->find($localidad->getLugarTipo());
        $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->find($comunidad->getLugarTipo());
        $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->find($municipio->getLugarTipo());
        $departamento = $em->getRepository('SieAppWebBundle:LugarTipo')->find($provincia->getLugarTipo());

        // Arrays
        $departamentos = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel'=>8));
        $depArray = array();
        foreach ($departamentos as $d) {
            $depArray[$d->getId()] = $d->getLugar();
        }
        $provincias = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$departamento->getId(),'lugarNivel'=>9));
        $provArray = array();
        foreach ($provincias as $p) {
            $provArray[$p->getId()] = $p->getLugar();
        }
        $municipios = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$provincia->getId(),'lugarNivel'=>10));
        $muniArray = array();
        foreach ($municipios as $m) {
            $muniArray[$m->getId()] = $m->getLugar();
        }
        $comunidades = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$municipio->getId(),'lugarNivel'=>11));
        $comuArray = array();
        foreach ($comunidades as $c) {
            $comuArray[$c->getId()] = $c->getLugar();
        }
        $localidades = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$comunidad->getId(),'lugarNivel'=>12));
        $localArray = array();
        foreach ($localidades as $l) {
            $localArray[$l->getId()] = $l->getLugar();
        }

        $form = $this->createFormBuilder($entity)
                        ->setAction($this->generateUrl('infra_caracteristicas_create_update'))
                        ->add('id','hidden',array('data'=>$id,'mapped'=>false))  
                        /*
                         * 1. Caracteristicas del terreno
                         */
                        ->add('n11Areaconstruida','text',array('label'=>'Área construida m2','attr'=> array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','onkeyup'=>'totalArea();','title'=>'1.1 Área construida m2')))
                        ->add('n11Arearecreacion','text',array('label'=>'Área de recreación m2','attr'=> array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','onkeyup'=>'totalArea();','title'=>'1.1 Área de recreación m2')))
                        ->add('n11Areanoconstruida','text',array('label'=>'Área no construida m2','attr'=> array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','onkeyup'=>'totalArea();','title'=>'1.1 Área no construida m2')))
                        ->add('n11Areacultivo','text',array('label'=>'Área de cultivo (aire libre) m2','attr'=> array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','onkeyup'=>'totalArea();','title'=>'1.1 Área de cultivo (aire libre) m2')))
                        ->add('n11Areacriaanimales','text',array('label'=>'Área de cria de animales (aire libre) m2','attr'=> array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','onkeyup'=>'totalArea();','title'=>'1.1 Área de cria de animales (aire libre) m2')))
                        ->add('n12TopografiaTipo', 'entity', array('label' => 'Fuente eléctrica', 'class'=>'SieAppWebBundle:InfraestructuraH2TopografiaTipo','empty_value'=>'Seleccionar...' ,'required' => true,'attr' => array('class' => 'form-control jupper','title'=>'1.2 Topografía')))
                        ->add('n13MuroperimetralArea','text',array('label'=>'Perímetro del área','attr'=> array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','title'=>'1.3 Perímetro del área')))
                        ->add('n13MuroperimetralAvanceTipo', 'entity', array('label' => '¿Esta amurallada la edificación?', 'class'=>'SieAppWebBundle:InfraestructuraGenAvanceTipo', 'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                                return $er->createQueryBuilder('e')
                                        ->where('e.id in (:ids)')
                                        ->setParameter('ids', array(1,2,3));
                            },'empty_value'=>'Seleccionar...','required' => true,'attr' => array('class' => 'form-control jupper','title'=>'1.3 ¿Esta amurallada la edificación?')))
                        ->add('n13MuroperimetralActual','text',array('label'=>'Muro actual','attr'=> array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','title'=>'1.3 Muro actual')))
                        ->add('n14ConEsRiesgoInundacion','checkbox',array('label'=>'Inundación','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n14ConEsRiesgoRiada','checkbox',array('label'=>'Riadas','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n14ConEsRiesgoTormenta','checkbox',array('label'=>'Tormentas','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n14ConEsRiesgoIncendio','checkbox',array('label'=>'Incendios','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n14ConEsRiesgoGranizo','checkbox',array('label'=>'Granizos/lluvias','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n14ConEsRiesgoHumedad','checkbox',array('label'=>'Humedad y/o filtración','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n14ConEsRiesgoBarranco','checkbox',array('label'=>'Proximidad a barrancos','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n14ConEsRiesgoContaminacion','checkbox',array('label'=>'Ductos y/o contaminación','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n14ConEsRiesgoDeslizamiento','checkbox',array('label'=>'Deslizamientos y/o quebradas','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n14ConEsRiesgoSifonamiento','checkbox',array('label'=>'Hundimientos o sifonamientos','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n14ConEsRiesgoTerremoto','checkbox',array('label'=>'Terremoto o fallas geológicas','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n14ConEsRiesgoNoexiste','checkbox',array('label'=>'No existe ningún riesgo','required'=>false,'attr'=>array('class'=>'')))

                        ->add('n15CulEsRiesgoInundacion','checkbox',array('label'=>'Inundación','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n15CulEsRiesgoRiada','checkbox',array('label'=>'Riadas','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n15CulEsRiesgoTormenta','checkbox',array('label'=>'Tormentas','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n15CulEsRiesgoIncendio','checkbox',array('label'=>'Incendios','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n15CulEsRiesgoGranizo','checkbox',array('label'=>'Granizos/lluvias','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n15CulEsRiesgoHumedad','checkbox',array('label'=>'Humedad y/o filtración','required'=>false,'attr'=>array('class'=>'')))
                        //->add('n15culTrueno','checkbox',array('label'=>'Truenos','attr'=>array('class'=>'form-control')))
                        //->add('n15culHelada','checkbox',array('label'=>'Heladas/nevadas','attr'=>array('class'=>'form-control')))
                        //->add('n15culSequia','checkbox',array('label'=>'Sequias/erosión','attr'=>array('class'=>'form-control')))
                        ->add('n15CulEsRiesgoBarranco','checkbox',array('label'=>'Proximidad a barrancos','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n15CulEsRiesgoContaminacion','checkbox',array('label'=>'Ductos y/o contaminación','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n15CulEsRiesgoDeslizamiento','checkbox',array('label'=>'Deslizamientos y/o quebradas','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n15CulEsRiesgoSifonamiento','checkbox',array('label'=>'Hundimientos o sifonamientos','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n15CulEsRiesgoTerremoto','checkbox',array('label'=>'Terremoto o fallas geológicas','required'=>false,'attr'=>array('class'=>'')))
                        ->add('n15CulEsRiesgoNoexiste','checkbox',array('label'=>'No existe ningún riesgo','required'=>false,'attr'=>array('class'=>'')))
                        
                        /*
                         * 2. Caracterisiticas de la edificacion
                         */
                        ->add('n21EsConstruidaEducativo','choice',array(
                                'label'=>'',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('title'=>'2.1 ¿La edificación fue construida para ser edificio educativo (EE)?')
                        ))
                        ->add('n22AnioConstruccion','text',array('label'=>'','attr'=> array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','title'=>'2.2 Año de construcción')))
                        ->add('n23AnioRefaccion','text',array('label'=>'','attr'=> array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','title'=>'2.3 Año de refacción')))
                        ->add('n24AnioAmpliacion','text',array('label'=>'','attr'=> array('class'=>'form-control','maxlength'=>4,'autocomplete'=>'off','title'=>'2.4 Año última ampliación')))
                        ->add('n25PropiedadTipo', 'entity', array('label' => 'Medio de Agua', 'class'=>'SieAppWebBundle:InfraestructuraH2PropiedadTipo','empty_value'=>'Seleccionar...', 'required' => true,'attr' => array('class' => 'form-control jupper','title'=>'2.5 Tipo de propiedad')))
                        ->add('n26Razonsocial','text',array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>100,'autocomplete'=>'off','title'=>'2.6 Razón social (Nombre del propietario o institución)')))
                        ->add('n27EstadogeneralTipo', 'entity', array('label' => '', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'empty_value'=>'Seleccionar...' , 'required' => true,'attr' => array('class' => 'form-control jupper','title'=>'2.7 Estado general del edificio')))
                        ->add('n28TechoEstadogeneralTipo', 'entity', array('label' => 'Techos', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'empty_value'=>'Seleccionar...' ,'required' => true,'attr' => array('class' => 'form-control jupper','title'=>'2.8 Techos')))
                        ->add('n28ParedEstadogeneralTipo', 'entity', array('label' => 'Paredes', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'empty_value'=>'Seleccionar...' ,'required' => true,'attr' => array('class' => 'form-control jupper','title'=>'2.8 Paredes')))
                        ->add('n28PisoEstadogeneralTipo', 'entity', array('label' => 'Pisos', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'empty_value'=>'Seleccionar...' ,'required' => true,'attr' => array('class' => 'form-control jupper','title'=>'2.8 Pisos')))
                        ->add('n28CieloEstadogeneralTipo', 'entity', array('label' => 'Cielo raso', 'class'=>'SieAppWebBundle:InfraestructuraGenEstadogeneralTipo', 'empty_value'=>'Seleccionar...' ,'required' => true,'attr' => array('class' => 'form-control jupper','title'=>'2.8 Cielo raso')))
                        ->add('n29DocumentoTipo', 'entity', array('label' => 'Tipo de documentación de mayor validez legal', 'class'=>'SieAppWebBundle:InfraestructuraH2DocumentoTipo', 'empty_value'=>'Seleccionar...' ,'required' => true,'attr' => array('class' => 'form-control jupper','title'=>'2.9 Tipo de documentación de mayor validez legal')))
                        ->add('n29DocumentoObs','textarea',array('label'=>'Anote el número de Folio Real o Tarjeta computarizada y sus observaciones acerca de la documentación que certifica la propiedad del edificio','required'=>false,'attr'=> array('class'=>'form-control jupper','style'=>'height:200px; resize:vertical','maxlength'=>200,'autocomplete'=>'off','title'=>'2.9 Documento Observación')))
                        ->add('n29DocumentoNroPartida','text',array('label'=>'Nro. de partida DD.RR.','attr'=> array('class'=>'form-control jupper','maxlength'=>50,'autocomplete'=>'off','title'=>'2.9 Nro. de partida DD.RR. ')))
                        ->add('n29EsPlanoAprobado','choice',array(
                                'label'=>'¿Tiene planos aprobados? (por la alcaldia)',
                                'choices'=> array('1'=>'Si','0'=>'No'),
                                'required'=>true,
                                'multiple'=>false,
                                'expanded'=>true,
                                'attr'=>array('title'=>'2.9 ¿Tiene planos aprobados? (por la alcaldia)')
                        ))
                        /*
                         * 3. Bloques edificados
                         */

                        /*
                         * 4 Eliminacion de basura
                         */
                        ->add('n41latitud','text',array('label'=>'Latitud','mapped'=>false,'data'=>$cordX,'attr'=>array('class'=>'form-control','readonly'=>true)))
                        ->add('n41longitud','text',array('label'=>'Longitud','mapped'=>false,'data'=>$cordY,'attr'=>array('class'=>'form-control','readonly'=>true)))

                        // Para el mapa
                        ->add('departamento', 'choice', array('label' => 'Departamento','mapped'=>false,'disabled' => false, 'required' => true, 'choices' => $depArray,'data'=>$departamento->getId(),'attr' => array('class' => 'form-control jupper', 'onchange' => 'listarProvincias(this.value);')))
                        ->add('provincia', 'choice', array('label' => 'Provincia', 'mapped'=>false,'disabled' => false, 'required' => true, 'choices' => $provArray, 'data'=>$provincia->getId(),'attr' => array('class' => 'form-control jupper', 'onchange' => 'listarMunicipios(this.value)')))
                        ->add('municipio', 'choice', array('label' => 'Municipio', 'mapped'=>false,'disabled' => false, 'required' => true, 'choices' => $muniArray,'data'=>$municipio->getId(),'attr' => array('class' => 'form-control jupper', 'onchange' => 'listarComunidades(this.value)')))
                        ->add('comunidad', 'choice', array('label' => 'Cantón', 'mapped'=>false,'disabled' => false, 'required' => true, 'choices' => $comuArray,'data'=>$comunidad->getId(), 'attr' => array('class' => 'form-control jupper', 'onchange' => 'listarLocalidades(this.value)')))
                        ->add('localidad', 'choice', array('label' => 'Localidad', 'mapped'=>false,'disabled' => false, 'required' => true, 'choices' => $localArray,'data'=>$localidad->getId(),'attr' => array('class' => 'form-control jupper', 'onchange'=>'verificarLocalidad()')))


                        ->add('guardar','submit',array('label'=>'Guardar Cambios','attr'=>array('class'=>'btn btn-success-alt')))

                        ->getForm();

        return $form;
    }

    public function createUpdateAction(Request $request){
        try{
            $infJurGeoId = $this->session->get('infJurGeoId');
            $em = $this->getDoctrine()->getManager();
            $formulario = $request->get('form');
            if($formulario['id'] == 'new'){ 
                $entity = new InfraestructuraH2Caracteristica();
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h2_caracteristica');")->execute();
            }else{
                $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH2Caracteristica')->find($formulario['id']);
            }

            $form = $this->createCaracteriticasForm($entity,$formulario['id']);
            $form->handleRequest($request);

            if ($form->isValid()) {

                if($formulario['id'] == 'new'){
                    $entity->setInfraestructuraJuridiccionGeografica($em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($infJurGeoId));
                    $entity->setFecharegistro(new \DateTime('now'));
                }
                $em->persist($entity);
                $em->flush();

                // REgistro de bloques edificados

                $bloquesEdificados = $em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificados')->findBy(array('infraestructuraH2Caracteristica' => $entity->getId()));
                if ($bloquesEdificados) {
                    foreach ($bloquesEdificados as $be) {
                        $em->remove($em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificados')->find($be->getId()));
                        $em->flush();
                    }
                }


                $nombreBloque = $request->get('nombreBloque');
                $area = $request->get('area');
                $nroPlantas = $request->get('nroPlantas');
                $pisos= $request->get('pisos');
                $paredes = $request->get('paredes');
                $cielos = $request->get('cielos');
                $techos = $request->get('techos');
                $nroAmbPed = $request->get('nroAmbPed');
                $nroAmbNoPed = $request->get('nroAmbNoPed');
                $nroTotalAmb = $request->get('nroTotalAmb');

                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h2_caracteristica_edificados');")->execute();
                for ($i=0; $i < count($nombreBloque); $i++) { 
                    $newBloque  = new InfraestructuraH2CaracteristicaEdificados();
                    $newBloque->setInfraestructuraH2caracteristica($entity);
                    $newBloque->setN3NombreBloque(mb_strtoupper($nombreBloque[$i]),'utf-8');
                    $newBloque->setN3AreaM2($area[$i]);
                    $newBloque->setN3Numeroplantas($nroPlantas[$i]);
                    $newBloque->setN3NumeroAmbientesPedagogicos($nroAmbPed[$i]);
                    $newBloque->setN3NumeroNoAmbientesPedagogicos($nroAmbNoPed[$i]);
                    $newBloque->setN3NumeroTotalPedagogicos($nroTotalAmb[$i]);
                    $newBloque->setN3PisoMaterialTipo($em->getRepository('SieAppWebBundle:InfraestructuraH2PisoMaterialTipo')->find($pisos[$i]));
                    $newBloque->setN3CieloMaterialTipo($em->getRepository('SieAppWebBundle:InfraestructuraH2CieloMaterialTipo')->find($cielos[$i]));
                    $newBloque->setN3ParedMaterialTipo($em->getRepository('SieAppWebBundle:InfraestructuraH2ParedMaterialTipo')->find($paredes[$i]));
                    $newBloque->setN3TechoMaterialTipo($em->getRepository('SieAppWebBundle:InfraestructuraH2TechoMaterialTipo')->find($techos[$i]));
                    $em->persist($newBloque);
                    $em->flush();
                }


                return $this->redirectToRoute('infra_info_acceder');
            }

            return $this->render('SieInfraestructuraBundle:Caracteristicas:index.html.twig',array('form'=>$form->createView()));

        }catch(Exception $e){

        }
        
    }

    public function bloquesEdificadosAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $id = $request->get('id');

            if($id != 'new'){
                $bloquesEdificados = $em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificados')->findBy(array('infraestructuraH2Caracteristica'=>$id));
                $bloques = array();
                foreach ($bloquesEdificados as $be) {
                    $bloques[] = array(
                        'nombre'=>$be->getN3NombreBloque(),
                        'area'=>$be->getN3AreaM2(),
                        'nroPlantas'=>$be->getN3Numeroplantas(),
                        'nroAmbPed'=>$be->getN3NumeroAmbientesPedagogicos(),
                        'nroAmbNoPed'=>$be->getN3NumeroNoAmbientesPedagogicos(),
                        'nroTotalAmb'=>$be->getN3NumeroTotalPedagogicos(),
                        'piso'=>$be->getN3PisoMaterialTipo()->getId(),
                        'cielo'=>$be->getN3CieloMaterialTipo()->getId(),
                        'pared'=>$be->getN3ParedMaterialTipo()->getId(),
                        'techo'=>$be->getN3TechoMaterialTipo()->getId(),
                    );
                }
            }else{
                $bloques = null;
            }

            $cieloTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH2CieloMaterialTipo')->findAll();
            $cielo = array();
            foreach ($cieloTipo as $c) {
                $cielo[$c->getId()] = $c->getInfraestructuraCieloMaterialTipo();
            }
            $pisoTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH2PisoMaterialTipo')->findAll();
            $piso = array();
            foreach ($pisoTipo as $p) {
                $piso[$p->getId()] = $p->getInfraestructuraPisoMaterial();
            }
            $paredTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH2ParedMaterialTipo')->findAll();
            $pared = array();
            foreach ($paredTipo as $pa) {
                $pared[$pa->getId()] = $pa->getInfraestructuraParedMaterialTipo();
            }
            $techoTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH2TechoMaterialTipo')->findAll();
            $techo = array();
            foreach ($techoTipo as $t) {
                $techo[$t->getId()] = $t->getInfraestructuraTechoMaterialTipo();
            }

            $response = new JsonResponse();
            $em->getConnection()->commit();
            return $response->setData(array(
                'bloques' => $bloques,
                'cielo'=>$cielo,
                'piso'=>$piso,
                'pared'=>$pared,
                'techo'=>$techo
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }
}
