<?php

namespace Sie\RueBundle\Controller;

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
class RegistroInstitucionEducativaController extends Controller {

      public $session;
      /**
       * the class constructor
       */
      public function __construct() {
          //init the session values
          $this->session = new Session();
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


        return $this->render('SieRueBundle:RegistroInstitucionEducativa:search.html.twig', array('formInstitucioneducativa' => $formInstitucioneducativa->createView(), 'formInstitucioneducativaId' => $formInstitucioneducativaId->createView()));
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
    	->setAction($this->generateUrl('rue_result'))
    			->add('tipo_search', 'hidden', array('data' => 'institucioneducativa'))
                ->add('institucioneducativa', 'text', array('required' => true, 'invalid_message' => 'Campo obligatorio'))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    private function createSearchFormInstitucioneducativaId() {

    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('rue_result'))
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
        	return $this->render('SieRueBundle:RegistroInstitucionEducativa:search.html.twig', array('formInstitucioneducativa' => $formInstitucioneducativa->createView(), 'formInstitucioneducativaId' => $formInstitucioneducativaId->createView()));

        }

        return $this->render('SieRueBundle:RegistroInstitucionEducativa:resultinseducativa.html.twig', array('entities' => $entities));


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
						WHERE tn.id between 200 and 299 or tn.id = 413 
						ORDER BY tn.id ASC'
                );
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
    				)->setParameter('id', array(11,12,232,203,204,205)); //11,12,405
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
            $entity->setFechaRegistro(new \DateTime('now'));
            
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
        //dump($entity->getInstitucioneducativaTipo()->getId());die;
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
						WHERE tn.id between 200 and 299
						ORDER BY tn.id ASC'
        			);
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
        			)->setParameter('id', array(11,12,232,203,204,205)); //11,12,405
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
            )->setParameter('id', array(1,2,3,5));
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
        $formularios = array();
        $certificados = array();

        return $this->render('SieRueBundle:RegistroInstitucionEducativa:edit.html.twig', array('entity' => $entity, 'formularios' => $formularios, 'certificados' => $certificados,'form' => $form->getForm()->createView()));



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

    /**
     * Edits an existing Institucioneducativa entity.
     *
     */

    public function updateAction(Request $request) {
    	$this->session = new Session();
        $form = $request->get('form');
        $sw = true;
        $em = $this->getDoctrine()->getManager();
        
        if ($form['estadoTipo'] == 19) {
            // Validar estudiantes efectivos en la UE
            $query = $em->getConnection()->prepare("SELECT
                c.institucioneducativa_id,
                c.turno_tipo_id,
                c.nivel_tipo_id,
                c.grado_tipo_id,
                c.paralelo_tipo_id,
                a.codigo_rude,
                a.paterno,
                a.materno,
                a.nombre,
                b.estadomatricula_tipo_id
                FROM
                estudiante a
                INNER JOIN estudiante_inscripcion b ON b.estudiante_id = a.id
                INNER JOIN institucioneducativa_curso c ON b.institucioneducativa_curso_id = c.id
                WHERE
                c.gestion_tipo_id = ".$this->session->get('currentyear')." AND
                c.institucioneducativa_id = ".$form['idRue']." AND
                b.estadomatricula_tipo_id = 4;");
            $query->execute();
            $efectivos = $query->fetchAll();
            if(count($efectivos)>0) {
                $sw = false;
            }
        }
        if ($sw){
            $em->getConnection()->beginTransaction();
            try {
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_nivel_autorizado');")->execute();
                $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idRue']);
                $entity->setInstitucioneducativa(mb_strtoupper($form['institucionEducativa'], 'utf-8'));
                $entity->setFechaResolucion(new \DateTime($form['fechaResolucion']));
                $entity->setNroResolucion(mb_strtoupper($form['nroResolucion'], 'utf-8'));
                $entity->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($form['dependenciaTipo']));
                $entity->setConvenioTipo(($form['convenioTipo'] != "") ? $em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById($form['convenioTipo']) : null);
                $entity->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById($form['estadoTipo']));
                $entity->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->findOneById($form['institucionTipo']));
                $entity->setObsRue(mb_strtoupper($form['obsRue'], 'utf-8'));
                $entity->setDesUeAntes(mb_strtoupper($form['desUeAntes'], 'utf-8'));
                $entity->setFechaModificacion(new \DateTime('now'));
                $entity->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($form['leJuridicciongeograficaId']));

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

                if ($form['institucionTipo'] == 1 or $form['institucionTipo'] == 2 or $form['institucionTipo'] == 5 or $form['institucionTipo'] == 6) {

                //adiciona niveles nuevos
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
                elseif ($form['institucionTipo'] == 4) {

                //adiciona areas nuevas
                $areas = $form['areaEspecialTipo'];

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

                $em->getConnection()->commit();
                return $this->redirect($this->generateUrl('rue'));

            } catch (Exception $e) {
                $em->getConnection()->rollback();
                return $this->redirect($this->generateUrl('rue'));
            }   
        } else {
            $this->get('session')->getFlashBag()->add('mensaje', 'La institución educativa: '.$form['idRue'].'-'.$form['institucionEducativa'].', cuenta con estudiantes efectivos. No es posible el "cierre" de la misma.');
            return $this->redirect($this->generateUrl('rue'));
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

        return $this->redirect($this->generateUrl('rue'));
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
                        ->setAction($this->generateUrl('rue_delete', array('id' => $id)))
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
/*SELECT
c.institucioneducativa_id,
c.turno_tipo_id,
c.nivel_tipo_id,
c.grado_tipo_id,
c.paralelo_tipo_id,
a.codigo_rude,
a.paterno,
a.materno,
a.nombre,
b.estadomatricula_tipo_id
FROM
estudiante a
INNER JOIN estudiante_inscripcion b ON b.estudiante_id = a."id"
INNER JOIN institucioneducativa_curso c ON b.institucioneducativa_curso_id = c.id
WHERE
c.gestion_tipo_id = 2019 AND
c.institucioneducativa_id = 40730328 AND
b.estadomatricula_tipo_id = 4;*/