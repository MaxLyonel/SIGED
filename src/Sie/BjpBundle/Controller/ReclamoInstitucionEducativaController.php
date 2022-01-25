<?php

namespace Sie\BjpBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Form\InstitucioneducativaType;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\EspecialAreaTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaAreaEspecialAutorizado;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InstitucioneducativaNivelAutorizado;

/**
 * Institucioneducativa controller.
 *
 */
class ReclamoInstitucionEducativaController extends Controller {

      public $session;
      /**
       * the class constructor
       */
      public function __construct() {
          //init the session values
          $this->session = new Session();
          $this->session->set('layout', 'layoutBjp.html.twig');
      } 

    /**
     * Lists all Institucioneducativa entities.
     *
     */
        public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        } 
        // data es un array con claves 'name', 'email', y 'message'

        /* return $this->render('SieAppWebBundle:Institucioneducativa:searchieducativa.html.twig', array(
          'form' => $this->createSearchForm()->createView(),
          )); */

        // Creacion de formularios de busqueda por codigo rue o nombre de institucion educativa
        $formInstitucioneducativa = $this->createSearchFormInstitucioneducativa();
        $formInstitucioneducativaId = $this->createSearchFormInstitucioneducativaId();


        return $this->render('SieBjpBundle:RegistroInstitucionEducativa:search.html.twig', array('formInstitucioneducativa' => $formInstitucioneducativa->createView(), 'formInstitucioneducativaId' => $formInstitucioneducativaId->createView()));
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
    	->setAction($this->generateUrl('bjp_reclamo_result'))
    			->add('tipo_search', 'hidden', array('data' => 'institucioneducativa'))
                ->add('institucioneducativa', 'text', array('required' => true, 'invalid_message' => 'Campo obligatorio'))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    private function createSearchFormInstitucioneducativaId() {

    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('bjp_reclamo_result'))
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
    	$form = $request->get('form');
    	$em = $this->getDoctrine()->getManager();
    	if ($form['tipo_search'] == 'institucioneducativa') {
    		$query = $em->createQuery(
    				'SELECT ie
                FROM SieAppWebBundle:Institucioneducativa ie
                WHERE UPPER(ie.institucioneducativa) like :id
                and ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
                ORDER BY ie.id')
    		                ->setParameter('id','%' . strtoupper($form['institucioneducativa']) . '%')
                        ->setParameter('ieAcreditacion', 1)
                        ;
    	}
    	else {
    		$query = $em->createQuery(
    				'SELECT ie
                FROM SieAppWebBundle:Institucioneducativa ie
                WHERE ie.id = :id
                and ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
                ORDER BY ie.id')
    		                ->setParameter('id', $form['institucioneducativaId'])
                        ->setParameter('ieAcreditacion', 1)
                        ;
    	}

    	$entities = $query->getResult();


        if (!$entities) {
        	$this->get('session')->getFlashBag()->add('msgSearch', 'No se encontro la información...');

        	$formInstitucioneducativa = $this->createSearchFormInstitucioneducativa();
        	$formInstitucioneducativaId = $this->createSearchFormInstitucioneducativaId();
        	return $this->render('SieBjpBundle:RegistroInstitucionEducativa:search.html.twig', array('formInstitucioneducativa' => $formInstitucioneducativa->createView(), 'formInstitucioneducativaId' => $formInstitucioneducativaId->createView()));

        }

        return $this->render('SieBjpBundle:RegistroInstitucionEducativa:resultinseducativa.html.twig', array('entities' => $entities));


    }


    /**
     * Displays a form to create a new Institucioneducativa entity.
     *
     */

    public function newAction(Request $request) {
    	$institucioneducativaTipoId = $request->get('institucioneducativaTipoId');
    	//$request->getSession()->set('institucioneducativaTipoId', $institucioneducativaTipoId);

    	$formulario = $this->createNewForm($institucioneducativaTipoId);
    	return $this->render('SieBjpBundle:RegistroInstitucionEducativa:new.html.twig', array('form' => $formulario->createView()));
    }

    private function createNewForm($InstitucioneducativaTipoId) {
//     	$entity = new Institucioneducativa();
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


    	}
    	elseif ($InstitucioneducativaTipoId == 2) {
    		$query = $em->createQuery(
    				'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:NivelTipo tn
						WHERE tn.id in (:id)
						ORDER BY tn.id ASC'
    				)->setParameter('id', array(201,202,203,204,205,206,207,208,211,212,213,214,215,216,217,218));
    				$niveles = $query->getResult();
    				$nivelesArray = array();
    				for($i=0;$i<count($niveles);$i++){
    					$nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
    				}

    	}
    	elseif ($InstitucioneducativaTipoId == 4) {

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

    	}


    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('bjp_reclamo_create'))
    	->add('institucionEducativa', 'text', array('label' => 'Nombre','required' => true,'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
    	->add('fechaResolucion', 'text', array('label' => 'Fecha de resolucion','required' => true, 'attr' => array('placeholder' => 'dd-mm-YYYY', 'data-date-end-date'=>'0d')))
    	->add('nroResolucion', 'text', array('label' => 'Resolucion','required' => true,'attr' => array('class' => 'form-control', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
    	->add('dependenciaTipo', 'entity', array('label' => 'Dependencia','required' => true,'class' => 'SieAppWebBundle:DependenciaTipo', 'property' => 'dependencia', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
    	->add('convenioTipo', 'entity', array('label' => 'Convenio','required' => true,'class' => 'SieAppWebBundle:ConvenioTipo', 'property' => 'convenio', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))

    	->add('estadoInstitucionTipo', 'entity', array('label' => 'Estado','required' => true,'class' => 'SieAppWebBundle:EstadoinstitucionTipo', 'property' => 'estadoinstitucion', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
    	->add('institucionEducativaTipo', 'entity', array('label' => 'Tipo', 'disabled' => false,'class' => 'SieAppWebBundle:InstitucioneducativaTipo','data' => $em->getReference('SieAppWebBundle:InstitucioneducativaTipo', $InstitucioneducativaTipoId), 'property' => 'descripcion', 'attr' => array('class' => 'form-control')))
    	->add('obsRue', 'text', array('label' => 'Observaciones','required' => false,'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
    	->add('desUeAntes', 'text', array('label' => 'Nombre anterior','required' => false,'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
    	->add('leJuridicciongeograficaId', 'text', array('label' => 'Codigo LE','required' => true, 'pattern' => '[0-9]{8,17}', 'max_length' => '8'))
    	->add('departamento', 'text', array('label' => 'Departamento', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('provincia', 'text', array('label' => 'Provincia', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('municipio', 'text', array('label' => 'Municipio', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('canton', 'text', array('label' => 'Canton', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('localidad', 'text', array('label' => 'Localidad', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('zona', 'text', array('label' => 'Zona', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('direccion', 'text', array('label' => 'Direccion', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('distrito', 'text', array('label' => 'Distrito', 'disabled' => true, 'attr' => array('class' => 'form-control')))

    	->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));

		if ($InstitucioneducativaTipoId == 4) {
			$form->add('areaEspecialTipo', 'choice', array('label' => 'Areas', 'choices'=>$areasArray,  'required' => false, 'multiple' => true,'expanded' => true,'attr' => array('class' => 'form-control')));
			$form->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices'=>$nivelesArray,  'required' => false  , 'multiple' => true,'expanded' => true,'attr' => array('class' => 'form-control')));
		}
		elseif ($InstitucioneducativaTipoId == 1 or $InstitucioneducativaTipoId == 2) {
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
    			$this->get('session')->getFlashBag()->add('registroInstitucionError', 'La institucion educativa ya existe en el sistema');
    			return $this->redirect($this->generateUrl('ReclamoInstitucionEducativa'));
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
    		$entity->setInstitucioneducativa($form['institucionEducativa']);
    		$entity->setFechaResolucion(new \DateTime($form['fechaResolucion']));
    		$entity->setFechaCreacion(new \DateTime('now'));
    		$entity->setNroResolucion($form['nroResolucion']);
    		$entity->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($form['dependenciaTipo']));
    		$entity->setConvenioTipo(($form['convenioTipo'] != "") ? $em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById($form['convenioTipo']) : null);
    		$entity->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById($form['estadoInstitucionTipo']));
    		$entity->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->findOneById($form['institucionEducativaTipo']));
    		$entity->setObsRue($form['obsRue']);
    		$entity->setDesUeAntes($form['desUeAntes']);
    		$entity->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($form['leJuridicciongeograficaId']));
    		$entity->setOrgcurricularTipo($em->getRepository('SieAppWebBundle:OrgcurricularTipo')->findOneById(1));
        $entity->setInstitucioneducativaAcreditacionTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaAcreditacionTipo')->find(1));
//     		dump($entity);die;

    		$em->persist($entity);
    		$em->flush();


    		if ($form['institucionEducativaTipo'] == 1 or $form['institucionEducativaTipo'] == 2) {
    			//adiciona niveles nuevos
    			//$niveles = $form['nivelTipo'];
          $niveles = (isset($form['nivelTipo']))?$form['nivelTipo']:array();
    			for($i=0;$i<count($niveles);$i++){

    				$nivel = new InstitucioneducativaNivelAutorizado();
    				$nivel->setFechaRegistro(new \DateTime('now'));
    				$nivel->setGestionTipoId($this->session->get('currentyear'));
    				$nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($niveles[$i]));
    				$nivel->setInstitucioneducativa($entity);
    				$em->persist($nivel);
    			}
    			$em->flush();
    		}
    		elseif ($form['institucionEducativaTipo'] == 4) {
    			//adiciona areas nuevas
    			$areas = $form['areaEspecialTipo'];
    			//     		dump($areas);die;
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
          $niveles = (isset($form['nivelTipo']))?$form['nivelTipo']:array();
    			for($i=0;$i<count($niveles);$i++){

    				$nivel = new InstitucioneducativaNivelAutorizado();
    				$nivel->setFechaRegistro(new \DateTime('now'));
    				$nivel->setGestionTipoId($this->session->get('currentyear'));
    				$nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($niveles[$i]));
    				$nivel->setInstitucioneducativa($entity);
    				$em->persist($nivel);
    			}
    			$em->flush();

    		}

        // Try and commit the transaction
        $em->getConnection()->commit();

    		$this->get('session')->getFlashBag()->add('mensaje', 'La institucion educativa fue registrada correctamente'  .  $entity->getId());
    		return $this->redirect($this->generateUrl('ReclamoInstitucionEducativa'));
    	} catch (Exception $ex) {
        $em->getConnection()->rollback();
    		$this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar la institucion educativa');
    		return $this->redirect($this->generateUrl('bjp_reclamo_new'));
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

        return $this->render('SieBjpBundle:RegistroInstitucionEducativa:show.html.twig', array(
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
            throw $this->createNotFoundException('No se puede encontrar la institucion educativa.');
        }

//         $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRue'));
//         $estadoActivo = array('1' => 'Si', '0' => 'No');
// dump($entity);die;

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



        }

        elseif ($entity->getInstitucioneducativaTipo()->getId() == 2) {
        	$query = $em->createQuery(
        			'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:NivelTipo tn
						WHERE tn.id in (:id)
						ORDER BY tn.id ASC'
        			)->setParameter('id', array(201,202,203,204,205,206,207,208,211,212,213,214,215,216,217,218));
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
        }

        elseif ($entity->getInstitucioneducativaTipo()->getId() == 4) {

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

        }




//         $areas = $em->getRepository('SieAppWebBundle:AreaEspecialTipo')->findAll();
//         dump($areasInstitucionArray);die;


        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('bjp_reclamo_update'))
        ->add('idRue', 'hidden', array('data' => $entity->getId()))
        ->add('rue', 'text', array('label' => 'Codigo RUE', 'data' => $entity->getId(), 'disabled' => true))
//         ->add('fechaCreacion', 'text', array('label' => 'Fecha de creacion', 'data' => $entity->getFechaCreacion(), 'disabled' => true))
        ->add('institucionEducativa', 'text', array('label' => 'Nombre', 'data' => $entity->getInstitucioneducativa(), 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => "[a-zñA-ZÑ0-9.\s'Ã¡Ã©Ã­Ã³ÃºÃ�Ã‰Ã�Ã“Ãš]{2,40}", 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
        ->add('fechaResolucion', 'text', array('label' => 'Fecha resolucion', 'data' => $entity->getFechaResolucion()->format('d-m-Y'), 'required' => true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off', 'data-date-end-date'=>'0d')))
        ->add('nroResolucion', 'text', array('label' => 'Resolucion', 'data' => $entity->getNroResolucion(), 'required' => false, 'attr' => array('class' => 'form-control',  'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
        ->add('dependenciaTipo', 'entity', array('label' => 'Dependencia', 'class' => 'SieAppWebBundle:DependenciaTipo', 'property' => 'dependencia', 'data' => $em->getReference('SieAppWebBundle:DependenciaTipo', $entity->getDependenciaTipo()->getId()), 'required' => true, 'attr' => array('class' => 'form-control')))
        ->add('convenioTipo', 'entity', array('label' => 'Convenio','required' => false,'class' => 'SieAppWebBundle:ConvenioTipo', 'property' => 'convenio', 'data' => $em->getReference('SieAppWebBundle:ConvenioTipo', $entity->getConvenioTipo()->getId()), 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
        ->add('estadoTipo', 'entity', array('label' => 'Estado', 'class' => 'SieAppWebBundle:EstadoinstitucionTipo', 'property' => 'estadoinstitucion', 'data' => $em->getReference('SieAppWebBundle:EstadoinstitucionTipo', $entity->getEstadoinstitucionTipo()->getId()), 'required' => true, 'attr' => array('class' => 'form-control')))
        ->add('institucionTipo', 'entity', array('label' => 'Tipo', 'disabled' => false, 'class' => 'SieAppWebBundle:InstitucioneducativaTipo', 'property' => 'descripcion', 'data' => $em->getReference('SieAppWebBundle:InstitucioneducativaTipo', $entity->getInstitucioneducativaTipo()->getId()), 'required' => true, 'attr' => array('class' => 'form-control')))
        ->add('obsRue', 'text', array('label' => 'Observaciones', 'data' => $entity->getObsRue(), 'required' => false, 'attr' => array('class' => 'form-control',  'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
        ->add('desUeAntes', 'text', array('label' => 'Nombre anterior', 'data' => $entity->getDesUeAntes(), 'required' => false, 'attr' => array('class' => 'form-control',  'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))

        ->add('leJuridicciongeograficaId', 'text', array('label' => 'Codigo LE','required' => true, 'data' => $entity->getLeJuridicciongeografica()->getId(), 'pattern' => '[0-9]{8,17}', 'max_length' => '8'))
        ->add('departamento', 'text', array('label' => 'Departamento', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('provincia', 'text', array('label' => 'Provincia', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('municipio', 'text', array('label' => 'Municipio', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('canton', 'text', array('label' => 'Canton', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('localidad', 'text', array('label' => 'Localidad', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('zona', 'text', array('label' => 'Zona', 'data' => $entity->getLeJuridicciongeografica()->getZona(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('direccion', 'text', array('label' => 'Direccion', 'data' => $entity->getLeJuridicciongeografica()->getDireccion(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('distrito', 'text', array('label' => 'Distrito', 'data' => $entity->getLeJuridicciongeografica()->getDistritoTipo()->getDistrito(), 'disabled' => true, 'attr' => array('class' => 'form-control')))

        ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));


        if ($entity->getInstitucioneducativaTipo()->getId() == 4) {
        	$form->add('areaEspecialTipo', 'choice', array('label' => 'Areas de especial', 'choices'=>$areasArray,  'required' => true,'data' => $areasInstitucionArray  , 'multiple' => true,'expanded' => true,'attr' => array('class' => 'form-control')));
        	$form->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices'=>$nivelesArray,  'required' => true  , 'multiple' => true,'expanded' => true,'data' => $nivelesInstitucionArray,'attr' => array('class' => 'form-control')));
        }
        elseif ($entity->getInstitucioneducativaTipo()->getId() == 1 or $entity->getInstitucioneducativaTipo()->getId() == 2) {
        	$form->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices'=>$nivelesArray,  'required' => true  , 'multiple' => true,'expanded' => true,'data' => $nivelesInstitucionArray,'attr' => array('class' => 'form-control')));

        }


//         $form->getForm();

        return $this->render('SieBjpBundle:RegistroInstitucionEducativa:edit.html.twig', array('entity' => $entity,'form' => $form->getForm()->createView()));



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
            'action' => $this->generateUrl('bjp_reclamo_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Institucioneducativa entity.
     *
     */

    public function updateAction(Request $request) {
    	$this->session = new Session();
    	$form = $request->get('form');

    	//valida fecha de resolucion
//     	$fechaResolucion = new \DateTime($form['fechaResolucion']);

//     	if ($fechaResolucion > new \DateTime('now')) {
//     		$this->get('session')->getFlashBag()->add('registroInstitucionError', 'La fecha de resolucion es mayor que la fecha actual');
// //     		$this->redirect($request->server->get('HTTP_REFERER'));
// //     		return $this->redirect($request->getReferer());
// //     		return $this->redirectToRoute('rue_edit');
//     		return $this->redirect($request->server->get('HTTP_REFERER'));
//     	}
//     	dump($fechaResolucion);die;
    	/*
    	 * Actualizacion de datos personales / persona
    	 */
    	$em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();


      try {
        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_nivel_autorizado');")->execute();
  //     	$em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_area_especial_autorizado');")->execute();

        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idRue']);
  //     	$entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRue'));

        $entity->setInstitucioneducativa($form['institucionEducativa']);
        $entity->setFechaResolucion(new \DateTime($form['fechaResolucion']));
        $entity->setNroResolucion($form['nroResolucion']);
        $entity->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($form['dependenciaTipo']));
        $entity->setConvenioTipo(($form['convenioTipo'] != "") ? $em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById($form['convenioTipo']) : null);
        $entity->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById($form['estadoTipo']));
        $entity->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->findOneById($form['institucionTipo']));
        $entity->setObsRue($form['obsRue']);
        $entity->setDesUeAntes($form['desUeAntes']);
        $entity->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($form['leJuridicciongeograficaId']));

        $em->persist($entity);
        $em->flush();


        if ($form['institucionTipo'] == 1 or $form['institucionTipo'] == 2) {
          //elimina los niveles
          $niveles = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa' => $entity->getId()));
          foreach ($niveles as $nivel) {
            $em->remove($nivel);
          }
          $em->flush();

          //adiciona niveles nuevos
          $niveles = (isset($form['nivelTipo']))?$form['nivelTipo']:array();

          for($i=0;$i<count($niveles);$i++){

            $nivel = new InstitucioneducativaNivelAutorizado();
            $nivel->setFechaRegistro(new \DateTime('now'));
            $nivel->setGestionTipoId($this->session->get('currentyear'));
            $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($niveles[$i]));
            $nivel->setInstitucioneducativa($entity);
            $em->persist($nivel);
          }
          $em->flush();


        }
        elseif ($form['institucionTipo'] == 4) {
          //elimina las areas
          $areas = $em->getRepository('SieAppWebBundle:InstitucioneducativaAreaEspecialAutorizado')->findBy(array('institucioneducativa' => $entity->getId()));
          foreach ($areas as $area) {
            $em->remove($area);
          }
          $em->flush();

          //adiciona areas nuevas
          $areas = $form['areaEspecialTipo'];
  //     		dump($areas);die;
          $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_area_especial_autorizado');")->execute();
          for($i=0;$i<count($areas);$i++){

            $area = new InstitucioneducativaAreaEspecialAutorizado();
            $area->setFechaRegistro(new \DateTime('now'));
            $area->setEspecialAreaTipo($em->getRepository('SieAppWebBundle:EspecialAreaTipo')->findOneById($areas[$i]));
            $area->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($entity->getId()));
            $em->persist($area);
          }
          $em->flush();
          //elimina los niveles
          $niveles = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa' => $entity->getId()));
          foreach ($niveles as $nivel) {
            $em->remove($nivel);
          }
          $em->flush();

          //adiciona niveles nuevos
          //$niveles = $form['nivelTipo'];
          $niveles = (isset($form['nivelTipo']))?$form['nivelTipo']:array();
          for($i=0;$i<count($niveles);$i++){

            $nivel = new InstitucioneducativaNivelAutorizado();
            $nivel->setFechaRegistro(new \DateTime('now'));
            $nivel->setGestionTipoId($this->session->get('currentyear'));
            $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($niveles[$i]));
            $nivel->setInstitucioneducativa($entity);
            $em->persist($nivel);
          }
          $em->flush();

        }


        // Try and commit the transaction
        $em->getConnection()->commit();
        return $this->redirect($this->generateUrl('ReclamoInstitucionEducativa'));

      } catch (Exception $e) {
        $em->getConnection()->rollback();
        return $this->redirect($this->generateUrl('ReclamoInstitucionEducativa'));
      }






    }




    /**
     * Deletes a Institucioneducativa entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Institucioneducativa entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('ReclamoInstitucionEducativa'));
    }

    /**
     * Creates a form to delete a Institucioneducativa entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('bjp_reclamo_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }



    public function viewAction(Request $request) {

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



}
