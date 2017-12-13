<?php

namespace Sie\DgesttlaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Form\InstitucioneducativaType;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\EspecialAreaTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaAreaEspecialAutorizado;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InstitucioneducativaNivelAutorizado;
use Sie\AppWebBundle\Entity\InstitucioneducativaSucursal;

/**
 * Institucioneducativa Controller.
 *
 */
class InstitucioneducativaController extends Controller {

      public $session;
      /**
       * the class constructor
       */
      public function __construct() {
          //init the session values
          $this->session = new Session();
      }

    /**
     * Show the forms to search
     *
     */
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Creación de formularios de busqueda por codigo SIE o nombre de institucion educativa
        $formInstitucioneducativaId = $this->createSearchFormInstitucioneducativaId();

        return $this->render('SieDgesttlaBundle:InstitucionEducativa:search.html.twig', array(
            'formInstitucioneducativaId' => $formInstitucioneducativaId->createView()
        ));
    }

    /**
     * Creates a form to search the users of student selected
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchFormInstitucioneducativa() {
    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('dgesttla_inst_result'))
    			->add('tipo_search', 'hidden', array('data' => 'institucioneducativa'))
                ->add('institucioneducativa', 'text', array('required' => true, 'invalid_message' => 'Campo obligatorio'))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    private function createSearchFormInstitucioneducativaId() {

    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('dgesttla_inst_result'))
    	->add('tipo_search', 'hidden', array('data' => 'institucioneducativaId'))
    	->add('institucioneducativaId', 'text', array('required' => true,'invalid_message' => 'Campo obligatorio', 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
    	->add('buscarId', 'submit', array('label' => 'Buscar'))
    	->getForm();
    	return $form;
    }

    /**
     * Find the institucion educativa.
     *
     */
    public function findieducativaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        
        $form = $request->get('form');
        $ieducativa_id = $form['institucioneducativaId'];
        
        $query = $em->createQuery(
                'SELECT ie
            FROM SieAppWebBundle:Institucioneducativa ie
            WHERE ie.id = :id
            and ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
            and ie.institucioneducativaTipo in (:ieTipo)
            ORDER BY ie.id')
            ->setParameter('id', $ieducativa_id)
            ->setParameter('ieAcreditacion', 1)
            ->setParameter('ieTipo', array(7,8,9));

        $entities = $query->getResult();

        if (!$entities) {
        	$this->get('session')->getFlashBag()->add('searchErr', 'No se encontró la información...');

        	$formInstitucioneducativaId = $this->createSearchFormInstitucioneducativaId();
        	return $this->render('SieDgesttlaBundle:Institucioneducativa:search.html.twig', array(
                'formInstitucioneducativaId' => $formInstitucioneducativaId->createView())
            );
        }

        return $this->render('SieDgesttlaBundle:Institucioneducativa:resultinseducativa.html.twig', array(
            'entities' => $entities)
        );
    }

    /**
     * Find the institucion educativa.
     *
     */
    public function gralesieducativaAction(Request $request) {
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        
        $ieducativa_id = $request->get('institucioneducativaId');
        $gestion_id = $request->get('gestionId');
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $ieducativa_id));

        $this->session->set('idGestion', $gestion_id);
        $this->session->set('idInstitucion', $ieducativa_id);
        $this->session->set('ie_id', $ieducativa_id);
        $this->session->set('ie_nombre', $institucioneducativa->getInstitucioneducativa());       

        return $this->render('SieDgesttlaBundle:Institucioneducativa:gralesinseducativa.html.twig', array(
            'institucioneducativa' => $institucioneducativa,
            'gestion' => $gestion_id)
        );
    }

    public function carrerasieducativaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        
        $ieducativa_id = $request->get('institucioneducativaId');
        $gestion_id = $request->get('gestionId');
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $ieducativa_id));
        
        $repository = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada');
        
        $query = $repository->createQueryBuilder('ca')
            ->select('at.id as atId, at.areaFormacion as atAreaFormacion, ct.id as ctId, ct.nombre as ctCarrera, re.id as reId, re.regimenEstudio reRegimenEstudio, rc.tiempoEstudio as rcTiempoEstudio')
            ->innerJoin('SieAppWebBundle:TtecCarreraTipo', 'ct', 'WITH', 'ca.ttecCarreraTipo = ct.id')
            ->innerJoin('SieAppWebBundle:TtecResolucionCarrera', 'rc', 'WITH', 'rc.ttecInstitucioneducativaCarreraAutorizada = ca.id')
            ->innerJoin('SieAppWebBundle:TtecAreaFormacionTipo', 'at', 'WITH', 'ct.ttecAreaFormacionTipo = at.id')
            ->innerJoin('SieAppWebBundle:TtecregimenEstudioTipo', 're', 'WITH', 'rc.ttecRegimenEstudioTipo = re.id')
            ->where('ca.institucioneducativa = :institucionId')
            ->setParameter('institucionId', $ieducativa_id)
            ->getQuery();

        $carreras = $query->getResult();

        return $this->render('SieDgesttlaBundle:Institucioneducativa:index.html.twig', array(
            'institucioneducativa' => $institucioneducativa,
            'gestion' => $gestion_id,
            'entities' => $carreras)
        );
    }

    /**
     * Find the institucion educativa.
     *
     */
    public function gralescarreraAction(Request $request) {
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        
        $carrera_id = $request->get('carreraId');
        $ieducativa_id = $request->get('institucioneducativaId');
        $gestion_id = $request->get('gestionId');
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $ieducativa_id));
        $carrera = $em->getRepository('SieAppWebBundle:TtecCarreraTipo')->findOneBy(array('id' => $carrera_id));

        $this->session->set('idGestion', $gestion_id);
        $this->session->set('idInstitucion', $ieducativa_id);
        $this->session->set('ie_id', $ieducativa_id);
        $this->session->set('ie_nombre', $institucioneducativa->getInstitucioneducativa());
        $this->session->set('idCarrera', $carrera_id);

        return $this->render('SieDgesttlaBundle:Institucioneducativa:gralescarrera.html.twig', array(
            'institucioneducativa' => $institucioneducativa,
            'gestion' => $gestion_id,
            'carrera' => $carrera
        ));
    }

    /**
     * Displays a form to create a new Institucioneducativa entity.
     *
     */

    public function newAction(Request $request) {
    	$institucioneducativaTipoId = $request->get('institucioneducativaTipoId');
    	$request->getSession()->set('institucioneducativaTipoId', $institucioneducativaTipoId);

    	$formulario = $this->createNewForm($institucioneducativaTipoId);
    	return $this->render('SieRueBundle:RegistroInstitucionEducativa:new.html.twig', array('form' => $formulario->createView()));
    }

    private function createNewForm($InstitucioneducativaTipoId) {

    	$em = $this->getDoctrine()->getManager();

    	if ($InstitucioneducativaTipoId == 1) {

    		$query = $em->createQuery(
    				'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:NivelTipo tn
						WHERE tn.id in (:id)
						ORDER BY tn.id ASC'
    				)->setParameter('id', array(11,12,13));
    				$niveles = $query->getResult();
    				$nivelesArray = array();
    				for($i=0;$i<count($niveles);$i++){
    					$nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
                    }
            $query = $em->createQuery(
                'SELECT DISTINCT iet.id,iet.descripcion
                    FROM SieAppWebBundle:InstitucioneducativaTipo iet
                    WHERE iet.id in (:id)
                    ORDER BY iet.id ASC'
                )->setParameter('id', array(1));
                $tipos = $query->getResult();
                $tiposArray = array();
                for($i=0;$i<count($tipos);$i++){
                    $tiposArray[$tipos[$i]['id']] = $tipos[$i]['descripcion'];
                }


    	}
    	elseif ($InstitucioneducativaTipoId == 2) {
    		$query = $em->createQuery(
    				'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:NivelTipo tn
						WHERE tn.id in (:id)
						ORDER BY tn.id ASC'
    				)->setParameter('id', array(201,202,203,204,205,206,207,208,211,212,213,214,215,216,217,218,219,220));
    				$niveles = $query->getResult();
    				$nivelesArray = array();
    				for($i=0;$i<count($niveles);$i++){
    					$nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
                    }
            $query = $em->createQuery(
                'SELECT DISTINCT iet.id,iet.descripcion
                    FROM SieAppWebBundle:InstitucioneducativaTipo iet
                    WHERE iet.id in (:id)
                    ORDER BY iet.id ASC'
                )->setParameter('id', array(2,5,6));
                $tipos = $query->getResult();
                $tiposArray = array();
                for($i=0;$i<count($tipos);$i++){
                    $tiposArray[$tipos[$i]['id']] = $tipos[$i]['descripcion'];
                }

    	}
    	elseif ($InstitucioneducativaTipoId == 4) {

    		$query = $em->createQuery(
    				'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:NivelTipo tn
						WHERE tn.id in (:id)
						ORDER BY tn.id ASC'
    				)->setParameter('id', array(11,12,405));
    				$niveles = $query->getResult();
    				$nivelesArray = array();
    				for($i=0;$i<count($niveles);$i++){
    					$nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
    				}

    				//carga araas de especial autorizadas

    				$query = $em->createQuery(
    						'SELECT DISTINCT ta.id,ta.areaEspecial
                        FROM SieAppWebBundle:EspecialAreaTipo ta
                        ORDER BY ta.id');
    				$areas = $query->getResult();
    				$areasArray = array();
    				for($i=0;$i<count($areas);$i++){
    					$areasArray[$areas[$i]['id']] = $areas[$i]['areaEspecial'];
                    }
                    $query = $em->createQuery(
                        'SELECT DISTINCT iet.id,iet.descripcion
                            FROM SieAppWebBundle:InstitucioneducativaTipo iet
                            WHERE iet.id in (:id)
                            ORDER BY iet.id ASC'
                        )->setParameter('id', array(4));
                        $tipos = $query->getResult();
                        $tiposArray = array();
                        for($i=0;$i<count($tipos);$i++){
                            $tiposArray[$tipos[$i]['id']] = $tipos[$i]['descripcion'];
                        }

        }
        
        $query = $em->createQuery(
            'SELECT DISTINCT dt.id,dt.dependencia
                FROM SieAppWebBundle:DependenciaTipo dt
                WHERE dt.id in (:id)
                ORDER BY dt.id ASC'
            )->setParameter('id', array(1,2,3));
            $dependencias = $query->getResult();
            $dependenciasArray = array();
            for($i=0;$i<count($dependencias);$i++){
                $dependenciasArray[$dependencias[$i]['id']] = $dependencias[$i]['dependencia'];
            }

            $query = $em->createQuery(
                'SELECT DISTINCT et.id,et.estadoinstitucion
                    FROM SieAppWebBundle:EstadoinstitucionTipo et
                    WHERE et.id in (:id)
                    ORDER BY et.id ASC'
                )->setParameter('id', array(10,19));
                $estados = $query->getResult();
                $estadosArray = array();
                for($i=0;$i<count($estados);$i++){
                    $estadosArray[$estados[$i]['id']] = $estados[$i]['estadoinstitucion'];
                }

                $query = $em->createQuery(
                    'SELECT DISTINCT ct.id,ct.convenio
                        FROM SieAppWebBundle:ConvenioTipo ct
                        WHERE ct.id not in (:id)
                        ORDER BY ct.id ASC'
                    )->setParameter('id', array(99));
                    $convenios = $query->getResult();
                    $conveniosArray = array();
                    for($i=0;$i<count($convenios);$i++){
                        $conveniosArray[$convenios[$i]['id']] = $convenios[$i]['convenio'];
                    }


    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('rue_create'))
    	->add('institucionEducativa', 'text', array('label' => 'Nombre','required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '69', 'style' => 'text-transform:uppercase')))
    	->add('fechaResolucion', 'text', array('label' => 'Fecha de resolución','required' => true, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
    	->add('nroResolucion', 'text', array('label' => 'Resolución','required' => true,'attr' => array('class' => 'form-control', 'autocomplete' => 'off', 'maxlength' => '44', 'style' => 'text-transform:uppercase')))
        ->add('dependenciaTipo', 'choice', array('label' => 'Dependencia', 'disabled' => false,'choices' => $dependenciasArray, 'attr' => array('class' => 'form-control')))
        ->add('convenioTipo', 'choice', array('label' => 'Convenio', 'disabled' => false,'choices' => $conveniosArray, 'attr' => array('class' => 'form-control')))
        ->add('estadoInstitucionTipo', 'choice', array('label' => 'Estado', 'disabled' => false,'choices' => $estadosArray, 'attr' => array('class' => 'form-control')))
    	->add('institucionEducativaTipo', 'choice', array('label' => 'Tipo', 'disabled' => false,'choices' => $tiposArray, 'attr' => array('class' => 'form-control')))
    	->add('obsRue', 'text', array('label' => 'Observaciones','required' => false,'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase', 'maxlength' => '44')))
    	->add('desUeAntes', 'text', array('label' => 'Nombre anterior','required' => false,'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
    	->add('leJuridicciongeograficaId', 'text', array('label' => 'Código LE','required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{8,17}', 'maxlength' => '8')))
    	->add('departamento', 'text', array('label' => 'Departamento', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('provincia', 'text', array('label' => 'Provincia', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('municipio', 'text', array('label' => 'Municipio', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('canton', 'text', array('label' => 'Cantón', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('localidad', 'text', array('label' => 'Localidad', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('zona', 'text', array('label' => 'Zona', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('direccion', 'text', array('label' => 'Dirección', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('distrito', 'text', array('label' => 'Distrito', 'disabled' => true, 'attr' => array('class' => 'form-control')))

    	->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));

		if ($InstitucioneducativaTipoId == 4) {
			$form->add('areaEspecialTipo', 'choice', array('label' => 'Areas', 'choices'=>$areasArray,  'required' => false, 'multiple' => true,'expanded' => true,'attr' => array('class' => 'form-control')));
			$form->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices'=>$nivelesArray,  'required' => false  , 'multiple' => true,'expanded' => true,'attr' => array('class' => 'form-control')));
		}
		elseif ($InstitucioneducativaTipoId == 1 or $InstitucioneducativaTipoId == 2 or $entity->getInstitucioneducativaTipo()->getId() == 5) {
			$form->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices'=>$nivelesArray,  'required' => false  , 'multiple' => true,'expanded' => true,'attr' => array('class' => 'form-control')));
		}

		return $form->getForm();

// 		->add('orgCurricularTipo', 'entity', array('label' => 'Org. Curricular','class' => 'SieAppWebBundle:OrgcurricularTipo', 'property' => 'orgcurricula', 'attr' => array('class' => 'form-control')))
// 		->add('id', 'text', array('label' => 'Codigo RUE','required' => true, 'pattern' => '[0-9]{8,17}', 'max_length' => '8'))

    }

    public function createAction(Request $request) {
    	try {
    		$em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
    		$form = $request->get('form');

    		// Validar la ie no esta registrada
    		$buscar_institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array(
    				'institucioneducativa' => $form['institucionEducativa'],
    				'fechaResolucion' => new \DateTime($form['fechaResolucion']),
    		));
    		if ($buscar_institucion) {
    			$this->get('session')->getFlashBag()->add('registroInstitucionError', 'La institución educativa ya existe en el sistema');
    			return $this->redirect($this->generateUrl('rue'));
    		}




//     		$em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa');")->execute();
    		$em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_nivel_autorizado');")->execute();
//     		$em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_area_especial_autorizado');")->execute();

    		$query = $em->getConnection()->prepare('SELECT get_genera_codigo_ue(:codle)');
    		$query->bindValue(':codle', $form['leJuridicciongeograficaId']);
    		$query->execute();
    		$codigoue = $query->fetchAll();
//     		            dump($codigoue);die;

    		// Registramos el local
    		$entity = new Institucioneducativa();
    		$entity->setId($codigoue[0]["get_genera_codigo_ue"]);
//     		echo "registrado";
//     		die;
    		//REgistro de institucion educativa
            $ieducativatipo = $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->findOneById($form['institucionEducativaTipo']);

    		$entity->setInstitucioneducativa(mb_strtoupper($form['institucionEducativa'], 'utf-8'));
    		$entity->setFechaResolucion(new \DateTime($form['fechaResolucion']));
    		$entity->setFechaCreacion(new \DateTime('now'));
    		$entity->setNroResolucion(mb_strtoupper($form['nroResolucion'], 'utf-8'));
    		$entity->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($form['dependenciaTipo']));
    		$entity->setConvenioTipo(($form['convenioTipo'] != "") ? $em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById($form['convenioTipo']) : null);
    		$entity->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById($form['estadoInstitucionTipo']));
    		$entity->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->findOneById($form['institucionEducativaTipo']));
    		$entity->setObsRue(mb_strtoupper($form['obsRue'], 'utf-8'));
    		$entity->setDesUeAntes(mb_strtoupper($form['desUeAntes'], 'utf-8'));
    		$entity->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($form['leJuridicciongeograficaId']));
    		$entity->setOrgcurricularTipo($ieducativatipo->getOrgcurricularTipo());
            $entity->setInstitucioneducativaAcreditacionTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaAcreditacionTipo')->find(1));

    		$em->persist($entity);
    		$em->flush();

            //elimina los niveles
            $nivelesElim = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa' => $entity->getId()));

            if($nivelesElim){
                foreach ($nivelesElim as $nivel) {
                    $em->remove($nivel);
                }
                $em->flush();
            }
            //elimina las areas
            $areasElim = $em->getRepository('SieAppWebBundle:InstitucioneducativaAreaEspecialAutorizado')->findBy(array('institucioneducativa' => $entity->getId()));

            if($areasElim) {
                foreach ($areasElim as $area) {
                    $em->remove($area);
                }
                $em->flush();
            }

    		if ($form['institucionEducativaTipo'] == 1 or $form['institucionEducativaTipo'] == 2) {
    			//adiciona niveles nuevos
    			//$niveles = $form['nivelTipo'];
                $niveles = (isset($form['nivelTipo']))?$form['nivelTipo']:array();

    			for($i=0;$i<count($niveles);$i++){

    				$nivel = new InstitucioneducativaNivelAutorizado();
    				$nivel->setFechaRegistro(new \DateTime('now'));
    				//$nivel->setGestionTipoId($this->session->get('currentyear'));
    				$nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($niveles[$i]));
    				$nivel->setInstitucioneducativa($entity);
    				$em->persist($nivel);
    			}

    			$em->flush();
    		}
    		elseif ($form['institucionEducativaTipo'] == 4) {

    			//adiciona areas nuevas
    			$areas = $form['areaEspecialTipo'];

    			for($i=0;$i<count($areas);$i++){

    				$area = new InstitucioneducativaAreaEspecialAutorizado();
    				$area->setFechaRegistro(new \DateTime('now'));
    				$area->setEspecialAreaTipo($em->getRepository('SieAppWebBundle:EspecialAreaTipo')->findOneById($areas[$i]));
                    $area->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($entity->getId()));
    				$em->persist($area);
    			}
    			$em->flush();

    			//adiciona niveles nuevos
    			//$niveles = $form['nivelTipo'];
                $nivel = new InstitucioneducativaNivelAutorizado();
                $nivel->setFechaRegistro(new \DateTime('now'));
                //$nivel->setGestionTipoId($this->session->get('currentyear'));
                $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById(6));
                $nivel->setInstitucioneducativa($entity);
                $em->persist($nivel);

                $niveles = (isset($form['nivelTipo']))?$form['nivelTipo']:array();

    			for($i=0;$i<count($niveles);$i++){

    				$nivel = new InstitucioneducativaNivelAutorizado();
    				$nivel->setFechaRegistro(new \DateTime('now'));
    				//$nivel->setGestionTipoId($this->session->get('currentyear'));
    				$nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($niveles[$i]));
    				$nivel->setInstitucioneducativa($entity);
    				$em->persist($nivel);
    			}
    			$em->flush();

    		}

        // Try and commit the transaction
        $em->getConnection()->commit();

    		$this->get('session')->getFlashBag()->add('mensaje', 'La institución educativa fue registrada correctamente: '  .  $entity->getId());
    		return $this->redirect($this->generateUrl('rue'));
    	} catch (Exception $ex) {
        $em->getConnection()->rollback();
    		$this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar la institución educativa.');
    		return $this->redirect($this->generateUrl('rue_new'));
    	}
    }













    /**
     * Finds and displays a Institucioneducativa entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institucioneducativa entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieRueBundle:RegistroInstitucionEducativa:show.html.twig', array(
                    'entity' => $entity,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Institucioneducativa entity.
     *
     */
    public function editAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRue'));

        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar la Institución Educativa.');
        }

//         $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRue'));
//         $estadoActivo = array('1' => 'Si', '0' => 'No');

        //carga araas de especial autorizadas

        if ($entity->getInstitucioneducativaTipo()->getId() == 1) {

        	$query = $em->createQuery(
        			'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:NivelTipo tn
						WHERE tn.id in (:id)
						ORDER BY tn.id ASC'
        			)->setParameter('id', array(11,12,13));
        			$niveles = $query->getResult();
        			$nivelesArray = array();
        			for($i=0;$i<count($niveles);$i++){
        				$nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
        			}

        	$query = $em->createQuery(
        			'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:InstitucioneducativaNivelAutorizado ien
                        JOIN ien.nivelTipo tn
                        WHERE ien.institucioneducativa = :id
                        ORDER BY tn.id')
        			    ->setParameter('id',$entity->getId());
        			 $niveles = $query->getResult();
        			 $nivelesInstitucionArray = array();
        			 for($i=0;$i<count($niveles);$i++){
        			   	$nivelesInstitucionArray[] = $niveles[$i]['id'];
                     }
                     
                     $query = $em->createQuery(
                        'SELECT DISTINCT iet.id,iet.descripcion
                            FROM SieAppWebBundle:InstitucioneducativaTipo iet
                            WHERE iet.id in (:id)
                            ORDER BY iet.id ASC'
                        )->setParameter('id', array(1));
                        $tipos = $query->getResult();
                        $tiposArray = array();
                        for($i=0;$i<count($tipos);$i++){
                            $tiposArray[$tipos[$i]['id']] = $tipos[$i]['descripcion'];
                        }


        }

        elseif ($entity->getInstitucioneducativaTipo()->getId() == 2 || $entity->getInstitucioneducativaTipo()->getId() == 5 || $entity->getInstitucioneducativaTipo()->getId() == 6) {
        	$query = $em->createQuery(
        			'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:NivelTipo tn
						WHERE tn.id in (:id)
						ORDER BY tn.id ASC'
        			)->setParameter('id', array(201,202,203,204,205,206,207,208,211,212,213,214,215,216,217,218,219,220));
        			$niveles = $query->getResult();
        			$nivelesArray = array();
        			for($i=0;$i<count($niveles);$i++){
        				$nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
        			}

        	$query = $em->createQuery(
        			'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:InstitucioneducativaNivelAutorizado ien
                        JOIN ien.nivelTipo tn
                        WHERE ien.institucioneducativa = :id
                        ORDER BY tn.id')
        	            ->setParameter('id',$entity->getId());
        	         $niveles = $query->getResult();
        	         $nivelesInstitucionArray = array();
        	         for($i=0;$i<count($niveles);$i++){
        	           	$nivelesInstitucionArray[] = $niveles[$i]['id'];
                     }
                     
                     $query = $em->createQuery(
                        'SELECT DISTINCT iet.id,iet.descripcion
                            FROM SieAppWebBundle:InstitucioneducativaTipo iet
                            WHERE iet.id in (:id)
                            ORDER BY iet.id ASC'
                        )->setParameter('id', array(2,5,6));
                        $tipos = $query->getResult();
                        $tiposArray = array();
                        for($i=0;$i<count($tipos);$i++){
                            $tiposArray[$tipos[$i]['id']] = $tipos[$i]['descripcion'];
                        }
        }

        elseif ($entity->getInstitucioneducativaTipo()->getId() == 4) {

        	$query = $em->createQuery(
        			'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:NivelTipo tn
						WHERE tn.id in (:id)
						ORDER BY tn.id ASC'
        			)->setParameter('id', array(11,12,405));
        			$niveles = $query->getResult();
        			$nivelesArray = array();
        			for($i=0;$i<count($niveles);$i++){
        				$nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
        			}

        			$query = $em->createQuery(
        					'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:InstitucioneducativaNivelAutorizado ien
                        JOIN ien.nivelTipo tn
                        WHERE ien.institucioneducativa = :id
                        ORDER BY tn.id')
        	                        ->setParameter('id',$entity->getId());
        	                        $niveles = $query->getResult();
        	                        $nivelesInstitucionArray = array();
        	                        for($i=0;$i<count($niveles);$i++){
        	                        	$nivelesInstitucionArray[] = $niveles[$i]['id'];
        	                        }



        	$query = $em->createQuery(
        			'SELECT DISTINCT ta.id,ta.areaEspecial
                        FROM SieAppWebBundle:EspecialAreaTipo ta
                        ORDER BY ta.id');
        	$areas = $query->getResult();
        	$areasArray = array();
        	for($i=0;$i<count($areas);$i++){
        		$areasArray[$areas[$i]['id']] = $areas[$i]['areaEspecial'];
        	}


        	$query = $em->createQuery(
        			'SELECT DISTINCT ta.id,ta.areaEspecial
                        FROM SieAppWebBundle:InstitucioneducativaAreaEspecialAutorizado iea
                        JOIN iea.especialAreaTipo ta
                        WHERE iea.institucioneducativa = :id
                        ORDER BY ta.id')
        	            ->setParameter('id',$entity->getId());
        	$areas = $query->getResult();
        	$areasInstitucionArray = array();
        	for($i=0;$i<count($areas);$i++){
        	   	$areasInstitucionArray[] = $areas[$i]['id'];
            }
            
            $query = $em->createQuery(
                'SELECT DISTINCT iet.id,iet.descripcion
                    FROM SieAppWebBundle:InstitucioneducativaTipo iet
                    WHERE iet.id in (:id)
                    ORDER BY iet.id ASC'
                )->setParameter('id', array(4));
                $tipos = $query->getResult();
                $tiposArray = array();
                for($i=0;$i<count($tipos);$i++){
                    $tiposArray[$tipos[$i]['id']] = $tipos[$i]['descripcion'];
                }

        }

        $query = $em->createQuery(
            'SELECT DISTINCT dt.id,dt.dependencia
                FROM SieAppWebBundle:DependenciaTipo dt
                WHERE dt.id in (:id)
                ORDER BY dt.id ASC'
            )->setParameter('id', array(1,2,3));
            $dependencias = $query->getResult();
            $dependenciasArray = array();
            for($i=0;$i<count($dependencias);$i++){
                $dependenciasArray[$dependencias[$i]['id']] = $dependencias[$i]['dependencia'];
            }

            $query = $em->createQuery(
                'SELECT DISTINCT et.id,et.estadoinstitucion
                    FROM SieAppWebBundle:EstadoinstitucionTipo et
                    WHERE et.id in (:id)
                    ORDER BY et.id ASC'
                )->setParameter('id', array(10,19));
                $estados = $query->getResult();
                $estadosArray = array();
                for($i=0;$i<count($estados);$i++){
                    $estadosArray[$estados[$i]['id']] = $estados[$i]['estadoinstitucion'];
                }

                $query = $em->createQuery(
                    'SELECT DISTINCT ct.id,ct.convenio
                        FROM SieAppWebBundle:ConvenioTipo ct
                        WHERE ct.id not in (:id)
                        ORDER BY ct.id ASC'
                    )->setParameter('id', array(99));
                    $convenios = $query->getResult();
                    $conveniosArray = array();
                    for($i=0;$i<count($convenios);$i++){
                        $conveniosArray[$convenios[$i]['id']] = $convenios[$i]['convenio'];
                    }



//         $areas = $em->getRepository('SieAppWebBundle:AreaEspecialTipo')->findAll();
//         dump($areasInstitucionArray);die;
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('rue_update'))
        ->add('idRue', 'hidden', array('data' => $entity->getId()))
        ->add('rue', 'text', array('label' => 'Codigo RUE', 'data' => $entity->getId(), 'disabled' => true,'attr' => array('class' => 'form-control')))
//         ->add('fechaCreacion', 'text', array('label' => 'Fecha de creacion', 'data' => $entity->getFechaCreacion(), 'disabled' => true))
        ->add('institucionEducativa', 'text', array('label' => 'Nombre', 'data' => $entity->getInstitucioneducativa(), 'required' => false, 'attr' => array('class' => 'form-control', 'maxlength' => '69', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
        ->add('fechaResolucion', 'text', array('label' => 'Fecha resolucion', 'data' => $entity->getFechaResolucion() ? $entity->getFechaResolucion()->format('d-m-Y') : $entity->getFechaResolucion(), 'required' => true, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
        ->add('nroResolucion', 'text', array('label' => 'Resolucion', 'data' => $entity->getNroResolucion(), 'required' => false, 'attr' => array('class' => 'form-control',  'autocomplete' => 'off', 'style' => 'text-transform:uppercase', 'maxlength' => '44')))
        ->add('dependenciaTipo', 'choice', array('label' => 'Dependencia','data' => $entity->getDependenciaTipo() ? $entity->getDependenciaTipo()->getId() : 0, 'required' => false, 'choices' => $dependenciasArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
        ->add('convenioTipo', 'choice', array('label' => 'Convenio','data' => $entity->getConvenioTipo() ? $entity->getConvenioTipo()->getId() : 0, 'required' => false, 'choices' => $conveniosArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
        ->add('estadoTipo', 'choice', array('label' => 'Estado','data' => $entity->getEstadoinstitucionTipo() ? $entity->getEstadoinstitucionTipo()->getId() : 0, 'required' => false, 'choices' => $estadosArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
        //->add('institucionTipo', 'entity', array('label' => 'Tipo', 'disabled' => false, 'class' => 'SieAppWebBundle:InstitucioneducativaTipo', 'property' => 'descripcion', 'data' => $em->getReference('SieAppWebBundle:InstitucioneducativaTipo', $entity->getInstitucioneducativaTipo()->getId()), 'required' => true, 'attr' => array('class' => 'form-control')))
        ->add('institucionTipo', 'choice', array('label' => 'Tipo','data' => $entity->getInstitucioneducativaTipo() ? $entity->getInstitucioneducativaTipo()->getId() : 0, 'required' => false, 'choices' => $tiposArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
        ->add('obsRue', 'text', array('label' => 'Observaciones', 'data' => $entity->getObsRue(), 'required' => false, 'attr' => array('class' => 'form-control',  'autocomplete' => 'off', 'style' => 'text-transform:uppercase', 'maxlength' => '44')))
        ->add('desUeAntes', 'text', array('label' => 'Nombre anterior', 'data' => $entity->getDesUeAntes(), 'required' => false, 'attr' => array('class' => 'form-control',  'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))

        ->add('leJuridicciongeograficaId', 'text', array('label' => 'Codigo LE','required' => true, 'data' => $entity->getLeJuridicciongeografica()->getId(), 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{8,17}', 'maxlength' => '8')))
        ->add('departamento', 'text', array('label' => 'Departamento', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('provincia', 'text', array('label' => 'Provincia', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('municipio', 'text', array('label' => 'Municipio', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('canton', 'text', array('label' => 'Canton', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('localidad', 'text', array('label' => 'Localidad', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('zona', 'text', array('label' => 'Zona', 'data' => $entity->getLeJuridicciongeografica()->getZona(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('direccion', 'text', array('label' => 'Direccion', 'data' => $entity->getLeJuridicciongeografica()->getDireccion(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('distrito', 'text', array('label' => 'Distrito', 'data' => $entity->getLeJuridicciongeografica()->getDistritoTipo()->getDistrito(), 'disabled' => true, 'attr' => array('class' => 'form-control')))

        ->add('guardar', 'submit', array('label' => 'Guardar'));


        if ($entity->getInstitucioneducativaTipo()->getId() == 4) {
        	$form->add('areaEspecialTipo', 'choice', array('label' => 'Areas de especial', 'choices'=>$areasArray,  'required' => true,'data' => $areasInstitucionArray  , 'multiple' => true,'expanded' => true));
        	$form->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices'=>$nivelesArray,  'required' => true  , 'multiple' => true,'expanded' => true,'data' => $nivelesInstitucionArray));
        }
        elseif ( $entity->getInstitucioneducativaTipo()->getId() == 1 or $entity->getInstitucioneducativaTipo()->getId() == 2 or $entity->getInstitucioneducativaTipo()->getId() == 5 or $entity->getInstitucioneducativaTipo()->getId() == 6) {
        	$form->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices'=>$nivelesArray,  'required' => true  , 'multiple' => true,'expanded' => true,'data' => $nivelesInstitucionArray));
        }
        elseif ($entity->getInstitucioneducativaTipo()->getId() == 0) {
            $nivelesArray = array();
            $nivelesInstitucionArray = array();
            $form->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices'=>$nivelesArray,  'required' => true  , 'multiple' => true,'expanded' => true,'data' => $nivelesInstitucionArray));
        }


        return $this->render('SieRueBundle:RegistroInstitucionEducativa:edit.html.twig', array('entity' => $entity,'form' => $form->getForm()->createView()));



//         $editForm = $this->createEditForm($entity);
//         $deleteForm = $this->createDeleteForm($request->get('id'));

//         return $this->render('SieRueBundle:RegistroInstitucionEducativa:edit.html.twig', array(
//                     'entity' => $entity,
//                     'edit_form' => $editForm->createView(),
//                     'delete_form' => $deleteForm->createView(),
//         ));
    }

    /**
     * Creates a form to edit a Institucioneducativa entity.
     *
     * @param Institucioneducativa $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Institucioneducativa $entity) {
        $form = $this->createForm(new InstitucioneducativaType(), $entity, array(
            'action' => $this->generateUrl('rue_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    public function buscaredificioAction($idLe) {
//echo "dfsd";die;


    	$em = $this->getDoctrine()->getManager();
    	$edificio = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($idLe);

    	$departamento = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar() : "";
    	$provincia = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar() : "";
    	$municipio = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugar() : "";
    	$canton = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugar() : "";
    	$localidad = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugar() : "";
    	$zona = ($edificio) ? $edificio->getZona() : "";
    	$direccion = ($edificio) ? $edificio->getDireccion() : "";
    	$distrito = ($edificio) ? $edificio->getDistritoTipo()->getDistrito() : "";

    	$response = new JsonResponse();
    	return $response->setData(array(
    			'departamento' => $departamento,
    			'provincia' => $provincia,
    			'municipio' => $municipio,
    			'canton' => $canton,
    			'localidad' => $localidad,
    			'zona'=>$zona,
    			'direccion' => $direccion,
    			'distrito' => $distrito
    	));

    }

    /**
     * index the request
     * @param Request $request
     * @return obj with the selected request 
     */
     public function infoInstitutoAction(Request $request) {
        
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $ieducativa_id = $request->get('institucioneducativaId');
        $gestion_id = $request->get('gestionId');

        if(!$ieducativa_id || !$gestion_id) {
            $ieducativa_id = $this->session->get('idInstitucion');
            $gestion_id = $this->session->get('idGestion');
        }

         //set Institucioneducativasucursal
        $objInfoSucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findBy(array(
            'institucioneducativa'=>$ieducativa_id,
            'gestionTipo'=>$gestion_id,
        ));
  
        if(!$objInfoSucursal){
            $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
            $query = $repository->createQueryBuilder('iesuc')
                ->select('max(iesuc.id)')
                ->getQuery();
    
            $maxiesuc = $query->getResult();
            $codigosuc= $maxiesuc[0][1] + 1;
            $codigole= $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ieducativa_id)->getLeJuridicciongeografica();
    
            // Registramos la sucursal
            $entity = new InstitucioneducativaSucursal();
            $entity->setId($codigosuc);
            $entity->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ieducativa_id));
            $entity->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion_id));
            $entity->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($codigole));
            $entity->setTelefono1("");
            $entity->setEmail("");
            $entity->setDireccion("");
            $entity->setZona("");
            $entity->setNombreSubcea("");
            $entity->setCodCerradaId(10);
            $entity->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneById(0));
            $entity->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->findOneById(0));
            $em->persist($entity);
            $em->flush();
            $em->getConnection()->commit();
        }

        $objInfoSucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findBy(array(
            'institucioneducativa'=>$ieducativa_id,
            'gestionTipo'=>$gestion_id,
        ));

        $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');

        $query = $repository->createQueryBuilder('jg')
                ->select('lt4.codigo AS codigo_departamento,
                        lt4.lugar AS departamento,
                        lt3.codigo AS codigo_provincia,
                        lt3.lugar AS provincia,
                        lt2.codigo AS codigo_seccion,
                        lt2.lugar AS seccion,
                        lt1.codigo AS codigo_canton,
                        lt1.lugar AS canton,
                        lt.codigo AS codigo_localidad,
                        lt.lugar AS localidad,
                        orgt.orgcurricula,
                        dept.dependencia,
                        jg.id AS codigo_le,
                        inst.id,
                        inst.institucioneducativa,
                        lt.area2001,
                        estt.estadoinstitucion,
                        inss.direccion')
                ->join('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.leJuridicciongeografica = jg.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'inss', 'WITH', 'inss.institucioneducativa = inst.id')
                ->innerJoin('SieAppWebBundle:EstadoinstitucionTipo', 'estt', 'WITH', 'inst.estadoinstitucionTipo = estt.id')
                ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
                ->join('SieAppWebBundle:DependenciaTipo', 'dept', 'WITH', 'inst.dependenciaTipo = dept.id')
                ->where('inst.id = :idInstitucion')
                ->andWhere('inss.gestionTipo in (:gestion)')
                ->setParameter('idInstitucion', $ieducativa_id)
                ->setParameter('gestion', $gestion_id)
                ->getQuery();

        $ubicacionUe = $query->getResult();

        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ieducativa_id);


        return $this->render($this->session->get('pathSystem') . ':Institucioneducativa:lookforinseducativa.html.twig', array(
                    'ieducativa' => $objInfoSucursal,
                    'institucioneducativa' => $institucion,
                    'gestion' => $gestion_id,
                    'ubicacion' => $ubicacionUe[0],
                    'form' => $this->editSucursalForm($ieducativa_id, $gestion_id, $objInfoSucursal[0]->getId())->createView(),
        ));
    }

    public function editSucursalForm($idInstitucion, $gestion, $insSuc) {
        $em = $this->getDoctrine()->getManager();
        $sucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($insSuc);

        $query = $em->createQuery(
            'SELECT tt FROM SieAppWebBundle:TurnoTipo tt
            ORDER BY tt.turno');

        $turnos = $query->getResult();
        $turnosArray = array();
        foreach ($turnos as $t) {
            $turnosArray[$t->getId()] = $t->getTurno();
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('dgesttla_inst_info_update'))
                ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('idSucursal', 'hidden', array('data' => $insSuc))
                ->add('telefono1', 'text', array('label' => 'Teléfono 1', 'data' => $sucursal->getTelefono1(), 'attr' => array('class' => 'form-control')))
                ->add('fax', 'text', array('label' => 'Fax', 'required' => false, 'data' => $sucursal->getFax(), 'attr' => array('class' => 'form-control')))
                ->add('telefono2', 'text', array('label' => 'Teléfono 2', 'required' => false, 'data' => $sucursal->getTelefono2(), 'attr' => array('class' => 'form-control')))
                ->add('referenciaTelefono2', 'text', array('label' => 'Pertenece a (cargo o relación)', 'data' => $sucursal->getReferenciaTelefono2(), 'attr' => array('class' => 'form-control')))
                ->add('email', 'text', array('label' => 'Correo electrónico del Instituto', 'required' => false, 'data' => $sucursal->getEmail(), 'attr' => array('class' => 'form-control')))
                ->add('casilla', 'text', array('label' => 'Casilla postal del Instituto', 'required' => false, 'data' => $sucursal->getCasilla(), 'attr' => array('class' => 'form-control')))
                ->add('turno', 'choice', array('label' => 'Turno', 'required' => true, 'choices' => $turnosArray, 'data' => $sucursal->getTurnoTipo() ? $sucursal->getTurnoTipo()->getId() : 0, 'attr' => array('class' => 'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

        return $form;
    }

    public function updateAction(Request $request) {
        
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {

            $form = $request->get('form');
            
            $institucion = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($form['idSucursal']);

            $institucion->setTelefono1($form['telefono1']);
            $institucion->setTelefono2($form['telefono2']);
            $institucion->setFax($form['fax']);
            $institucion->setCasilla($form['casilla']);
            $institucion->setReferenciaTelefono2(mb_strtoupper($form['referenciaTelefono2']), 'utf-8');
            $institucion->setEmail(mb_strtolower($form['email']), 'utf-8');
            $institucion->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneById($form['turno']));

            $em->persist($institucion);
            $em->flush();
            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('updateOk', 'Datos actualizados correctamente');
            return $this->redirect($this->generateUrl('dgesttla_inst_info'));
         } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    public function calendarioAction(Request $request) {
        
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $ieducativa_id = $request->getSession()->get('idInstitucion');
        $gestion_id = $request->getSession()->get('idGestion');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $calendario = $em->getRepository('SieAppWebBundle:TtecCalendarioOperativo')->findBy(array('institucioneducativa' => $ieducativa_id, 'gestionTipo' => $gestion_id));
        $institucion = $em->getRepository('SieAppWebBundle:TtecCalendarioOperativo')->findBy(array('institucioneducativa' => $ieducativa_id, 'gestionTipo' => $gestion_id));
dump($calendario);die;
        return $this->render($this->session->get('pathSystem') . ':Institucioneducativa:calendario_index.html.twig', array(
            'institucion' => $institucion,
            'gestion' => $gestion_id,
            'calendario' => $calendario
        ));
    }    

}
