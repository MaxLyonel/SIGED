<?php

namespace Sie\RueBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Symfony\Component\HttpFoundation\Response;
/**
 * Institucioneducativa controller.
 *
 */
class ConsultaInstitucionEducativaController extends Controller {

    /**
     * Lists all Institucioneducativa entities.
     *
     */
    public function indexAction(Request $request) {
//         $sesion = $request->getSession();
//         $id_usuario = $sesion->get('userId');
//         if (!isset($id_usuario)) {
//             return $this->redirect($this->generateUrl('login'));
//         }
        // data es un array con claves 'name', 'email', y 'message'

        /* return $this->render('SieAppWebBundle:Institucioneducativa:searchieducativa.html.twig', array(
          'form' => $this->createSearchForm()->createView(),
          )); */

        // Creacion de formularios de busqueda por codigo rue o nombre de institucion educativa
        $formInstitucioneducativa = $this->createSearchFormInstitucioneducativa();
        $formInstitucioneducativaId = $this->createSearchFormInstitucioneducativaId();
		$formInstitucioneducativaTipo = $this->createSearchFormInstitucioneducativaTipo();
		$formInstitucioneducativaEstado = $this->createSearchFormInstitucioneducativaEstado();


        return $this->render('SieRueBundle:ConsultaInstitucionEducativa:search.html.twig', array(
			'formInstitucioneducativa' => $formInstitucioneducativa->createView(), 
			'formInstitucioneducativaId' => $formInstitucioneducativaId->createView(),
			'formInstitucioneducativaTipo' => $formInstitucioneducativaTipo->createView(),
			'formInstitucioneducativaEstado' => $formInstitucioneducativaEstado->createView()));
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
    	->setAction($this->generateUrl('consulta_rue_result'))
    			->add('tipo_search', 'hidden', array('data' => 'institucioneducativa'))
                ->add('institucioneducativa', 'text', array('required' => true, 'invalid_message' => 'Campo obligatorio'))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    private function createSearchFormInstitucioneducativaId() {

    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('consulta_rue_result'))
    	->add('tipo_search', 'hidden', array('data' => 'institucioneducativaId'))
    	->add('institucioneducativaId', 'text', array('required' => true,'invalid_message' => 'Campo obligatorio', 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
    	->add('buscarId', 'submit', array('label' => 'Buscar'))
    	->getForm();
    	return $form;
    }

    private function createSearchFormInstitucioneducativaTipo() {
    	$em = $this->getDoctrine()->getManager();

    	$dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' => 1));
    	$depArray = array();
    	foreach ($dep as $de) {
    		$depArray[$de->getId()] = $de->getLugar();
		}
		
		$query = $em->createQuery(
			'SELECT DISTINCT iet.id,iet.descripcion
				FROM SieAppWebBundle:InstitucioneducativaTipo iet
				WHERE iet.id in (:id)
				ORDER BY iet.id ASC'
			)->setParameter('id', array(1,2,4,5,6));
			$tipos = $query->getResult();
			$tiposArray = array();
			for($i=0;$i<count($tipos);$i++){
				$tiposArray[$tipos[$i]['id']] = $tipos[$i]['descripcion'];
			}

    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('consulta_rue_result'))
    	->add('tipo_search', 'hidden', array('data' => 'institucioneducativaTipo'))
		->add('departamento', 'choice', array('label' => 'Departamento', 'required' => true,'choices'=>$depArray ,'empty_value' => 'Seleccionar departamento...'))
		->add('institucioneducativaTipo', 'choice', array('label' => 'Tipo', 'disabled' => false,'choices' => $tiposArray,'empty_value' => 'Seleccionar tipo...', 'attr' => array('class' => 'form-control')))
		->add('buscarTipo', 'submit', array('label' => 'Buscar'))
    	->getForm();
    	return $form;
    }

	private function createSearchFormInstitucioneducativaEstado() {
    	$em = $this->getDoctrine()->getManager();

    	$dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' => 1));
    	$depArray = array();
    	foreach ($dep as $de) {
    		$depArray[$de->getId()] = $de->getLugar();
		}
		
		$query = $em->createQuery(
			'SELECT DISTINCT eie.id,eie.estadoinstitucion
				FROM SieAppWebBundle:EstadoinstitucionTipo eie
				WHERE eie.id in (:id)
				ORDER BY eie.id ASC'
			)->setParameter('id', array(10,19));
			$estados = $query->getResult();
			$estadosArray = array();
			for($i=0;$i<count($estados);$i++){
				$estadosArray[$estados[$i]['id']] = $estados[$i]['estadoinstitucion'];
			}

    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('consulta_rue_result'))
    	->add('tipo_search', 'hidden', array('data' => 'institucioneducativaEstado'))
		->add('departamento', 'choice', array('label' => 'Departamento', 'required' => true,'choices'=>$depArray ,'empty_value' => 'Seleccionar departamento...'))
		->add('institucioneducativaEstado', 'choice', array('label' => 'Estado', 'disabled' => false,'choices' => $estadosArray,'empty_value' => 'Seleccionar estado...', 'attr' => array('class' => 'form-control')))
		->add('buscarEstado', 'submit', array('label' => 'Buscar'))
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
    	elseif ($form['tipo_search'] == 'institucioneducativaId') {
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
    	elseif ($form['tipo_search'] == 'institucioneducativaTipo') {

			if($form['departamento']!=1){
			$query = $em->createQuery(
						'SELECT ie
					FROM SieAppWebBundle:Institucioneducativa ie
					JOIN ie.leJuridicciongeografica le
					JOIN le.lugarTipoLocalidad lo
					JOIN lo.lugarTipo ca
					JOIN ca.lugarTipo se
					JOIN se.lugarTipo pr
					JOIN pr.lugarTipo de
					JOIN ie.institucioneducativaTipo ti
					WHERE de.id = :id
					AND ti.id = :tipoId
				and ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
					ORDER BY ie.id')
							->setParameter('id', $form['departamento'])
							->setParameter('tipoId', $form['institucioneducativaTipo'])
							->setParameter('ieAcreditacion', 1)
						;
			}else{
			$query = $em->createQuery(
						'SELECT ie
					FROM SieAppWebBundle:Institucioneducativa ie
					JOIN ie.leJuridicciongeografica le
					JOIN le.lugarTipoLocalidad lo
					JOIN lo.lugarTipo ca
					JOIN ca.lugarTipo se
					JOIN se.lugarTipo pr
					JOIN pr.lugarTipo de
					JOIN ie.institucioneducativaTipo ti
					WHERE de.id in ( :id )
					AND ti.id = :tipoId
				and ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
					ORDER BY de.id')
							->setParameter('id', array(2,3,4,5,6,7,8,9,10))
							->setParameter('tipoId', $form['institucioneducativaTipo'])
							->setParameter('ieAcreditacion', 1)
						;
			}

		}
		elseif ($form['tipo_search'] == 'institucioneducativaEstado') {
			
			if($form['departamento']!=1){
				$query = $em->createQuery(
							'SELECT ie
						FROM SieAppWebBundle:Institucioneducativa ie
						JOIN ie.leJuridicciongeografica le
						JOIN le.lugarTipoLocalidad lo
						JOIN lo.lugarTipo ca
						JOIN ca.lugarTipo se
						JOIN se.lugarTipo pr
						JOIN pr.lugarTipo de
						JOIN ie.estadoinstitucionTipo eit
						WHERE de.id = :id
						AND eit.id = :estadoId
				and ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
						ORDER BY ie.id')
								->setParameter('id', $form['departamento'])
								->setParameter('estadoId', $form['institucioneducativaEstado'])
							->setParameter('ieAcreditacion', 1)
							;
			
			}else{
				$query = $em->createQuery(
							'SELECT ie
						FROM SieAppWebBundle:Institucioneducativa ie
						JOIN ie.leJuridicciongeografica le
						JOIN le.lugarTipoLocalidad lo
						JOIN lo.lugarTipo ca
						JOIN ca.lugarTipo se
						JOIN se.lugarTipo pr
						JOIN pr.lugarTipo de
						JOIN ie.estadoinstitucionTipo eit
						WHERE de.id in ( :id )
						AND eit.id = :estadoId
					and ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
						ORDER BY de.id')
								->setParameter('id', array(2,3,4,5,6,7,8,9,10))
								->setParameter('estadoId', $form['institucioneducativaEstado'])
								->setParameter('ieAcreditacion', 1)
							;
				}

		}

		$entities = $query->getResult();

        if (!$entities) {
        	$this->get('session')->getFlashBag()->add('msgSearch', 'No se encontró la información.');

        	$formInstitucioneducativa = $this->createSearchFormInstitucioneducativa();
        	$formInstitucioneducativaId = $this->createSearchFormInstitucioneducativaId();
			$formInstitucioneducativaTipo = $this->createSearchFormInstitucioneducativaTipo();
			$formInstitucioneducativaEstado = $this->createSearchFormInstitucioneducativaEstado();

        	return $this->render('SieRueBundle:ConsultaInstitucionEducativa:search.html.twig', array(
				'formInstitucioneducativa' => $formInstitucioneducativa->createView(), 
				'formInstitucioneducativaId' => $formInstitucioneducativaId->createView(), 
				'formInstitucioneducativaTipo' => $formInstitucioneducativaTipo->createView(),
				'formInstitucioneducativaEstado' => $formInstitucioneducativaEstado->createView()));

        }

        return $this->render('SieRueBundle:ConsultaInstitucionEducativa:resultinseducativa.html.twig', array('entities' => $entities));
    }


    public function localizacionAction() {
    	$em = $this->getDoctrine()->getManager();
    	$dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' => 1));
    	$depArray = array();
    	foreach ($dep as $de) {
    		$depArray[$de->getId()] = $de->getLugar();
    	}


    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('consulta_rue_formulario'))
    	->add('localizacion', 'hidden', array('data' => 'localizacionLocalidad'))
    	->add('departamento', 'choice', array('label' => 'Departamento', 'required' => true,'choices'=>$depArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'jupper')))
    	->add('provincia', 'choice', array('label' => 'Provincia', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'jupper')))
    	->add('municipio', 'choice', array('label' => 'Municipio', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'jupper')))
    	->add('canton', 'choice', array('label' => 'Canton', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'jupper')))
    	->add('localidad', 'choice', array('label' => 'Localidad', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'jupper')))
    	->add('formulario', 'submit', array('label' => 'Formulario'));


    	$formLe = $this->createFormBuilder()
    	->setAction($this->generateUrl('consulta_rue_formulario'))
    	->add('localizacion', 'hidden', array('data' => 'localizacionLe'))
    	->add('leJuridicciongeograficaId', 'text', array('label' => 'Codigo LE','required' => true, 'pattern' => '[0-9]{8,17}', 'max_length' => '8'))
    	->add('l_departamento', 'text', array('label' => 'Departamento', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('l_provincia', 'text', array('label' => 'Provincia', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('l_municipio', 'text', array('label' => 'Municipio', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('l_canton', 'text', array('label' => 'Canton', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('l_localidad', 'text', array('label' => 'Localidad', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('le_departamento', 'choice', array('label' => 'Departamento', 'required' => true,'choices'=>$depArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('le_provincia', 'choice', array('label' => 'Provincia', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('le_municipio', 'choice', array('label' => 'Municipio', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('le_canton', 'choice', array('label' => 'Canton', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('le_localidad', 'choice', array('label' => 'Localidad', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))

    	->add('l_formulario', 'submit', array('label' => 'Formulario'));


    	$formRueLe = $this->createFormBuilder()
    	->setAction($this->generateUrl('consulta_rue_le_formulario'))
    	->add('r_localizacion', 'hidden', array('data' => 'localizacionRueLe'))
    	->add('r_leJuridicciongeograficaId', 'text', array('label' => 'Codigo LE','required' => true, 'pattern' => '[0-9]{8,17}', 'max_length' => '8'))
    	->add('r_departamento', 'text', array('label' => 'Departamento', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('r_provincia', 'text', array('label' => 'Provincia', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('r_municipio', 'text', array('label' => 'Municipio', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('r_canton', 'text', array('label' => 'Canton', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('r_localidad', 'text', array('label' => 'Localidad', 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('r_formulario', 'submit', array('label' => 'Formulario'));


    	return $this->render('SieRueBundle:ConsultaInstitucionEducativa:localizacion.html.twig', array('form' => $form->getForm()->createView(),'formLe' => $formLe->getForm()->createView(),'formRueLe' => $formRueLe->getForm()->createView()));


    }



    public function formularioAction(Request $request) {
    	$form = $request->get('form');
//     	$em = $this->getDoctrine()->getManager();
    	$response = new Response();
    	$response->headers->set('Content-type', 'application/pdf');
		
    	if ($form['localizacion'] == 'localizacionLe') {
    		$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'formulario_le_' . $form['leJuridicciongeograficaId'] . '.pdf'));
    		$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_frm_actualizaciongeografica_ue_v1.rptdesign&le=' . $form['leJuridicciongeograficaId'] . '&localidad=' . $form['le_localidad'] . '&&__format=pdf&'));

    	}
    	else {
    		$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'formulario_localidad' . $form['localidad'] . '.pdf'));
    		$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_frm_actualizaciongeografica_nueva_ue_v1.rptdesign&localidad=' . $form['localidad'] . '&&__format=pdf&'));

    	}

    	$response->setStatusCode(200);
    	$response->headers->set('Content-Transfer-Encoding', 'binary');
    	$response->headers->set('Pragma', 'no-cache');
    	$response->headers->set('Expires', '0');
    	return $response;

    }

    public function formularioleAction(Request $request) {
    	$form = $request->get('form');
    	//     	$em = $this->getDoctrine()->getManager();
    	$response = new Response();
    	$response->headers->set('Content-type', 'application/pdf');
  		$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'formulario_rue_le_' . $form['r_leJuridicciongeograficaId'] . '.pdf'));
   		$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_actualizacion_geografica_le_v1.rptdesign&le=' . $form['r_leJuridicciongeograficaId'] . '&&__format=pdf&'));
    	$response->setStatusCode(200);
    	$response->headers->set('Content-Transfer-Encoding', 'binary');
    	$response->headers->set('Pragma', 'no-cache');
    	$response->headers->set('Expires', '0');
    	return $response;

    }


    public function showAction(Request $request) {
    	$em = $this->getDoctrine()->getManager();
    	$entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRue'));

    	if (!$entity) {
    		throw $this->createNotFoundException('No se puede encontrar la institucion educativa.');
    	}

		$areasArray = array();
		$nivelesArray = array();
		$areas = array();
		$niveles = array();

		//Información de la institución educativa
		$repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

		$query = $repository->createQueryBuilder('i')
				->select('i.id ieducativaId, i.institucioneducativa ieducativa, d.id distritoId, d.distrito distrito, dp.id departamentoId, dp.departamento departamento, de.dependencia dependencia, jg.cordx cordx, jg.cordy cordy')
				->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'jg', 'WITH', 'i.leJuridicciongeografica = jg.id')
				->innerJoin('SieAppWebBundle:DistritoTipo', 'd', 'WITH', 'jg.distritoTipo = d.id')
				->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dp', 'WITH', 'd.departamentoTipo = dp.id')
				->innerJoin('SieAppWebBundle:DependenciaTipo', 'de', 'WITH', 'i.dependenciaTipo = de.id')
				->where('i.id = :ieducativa')
				->setParameter('ieducativa', $request->get('idRue'))
				->getQuery();

		$institucion = $query->getOneOrNullResult();

    	//Carga áraas de especial autorizadas
    	if ($entity->getInstitucioneducativaTipo()->getId() == 1) {
    		$query = $em->createQuery(
				'SELECT DISTINCT tn.id,tn.nivel
					FROM SieAppWebBundle:NivelTipo tn
					WHERE tn.id in (:id)
					ORDER BY tn.id ASC'
				)
				->setParameter('id', array(11,12,13));
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
				$nivelesInstitucionArray[$i] = $niveles[$i]['id'];
			}
			
    	} elseif ($entity->getInstitucioneducativaTipo()->getId() == 2 or $entity->getInstitucioneducativaTipo()->getId() == 5 or $entity->getInstitucioneducativaTipo()->getId() == 6) {
    		$query = $em->createQuery(
				'SELECT DISTINCT tn.id,tn.nivel
					FROM SieAppWebBundle:NivelTipo tn
					WHERE tn.id in (:id)
					ORDER BY tn.id ASC'
				)
				->setParameter('id', array(201,202,203,204,205,206,207,208,211,212,213,214,215,216,217,218));
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
				ORDER BY tn.id'
				)
				->setParameter('id',$entity->getId());
			$niveles = $query->getResult();
			$nivelesInstitucionArray = array();
			for($i=0;$i<count($niveles);$i++){
				$nivelesInstitucionArray[] = $niveles[$i]['id'];
			}
    	} elseif ($entity->getInstitucioneducativaTipo()->getId() == 4) {
    		$query = $em->createQuery(
				'SELECT DISTINCT tn.id,tn.nivel
					FROM SieAppWebBundle:NivelTipo tn
					WHERE tn.id in (:id)
					ORDER BY tn.id ASC'
				)
				->setParameter('id', array(11,12,405));
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
				ORDER BY tn.id'
				)
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
				ORDER BY ta.id'
				)
				->setParameter('id',$entity->getId());
			$areas = $query->getResult();
			$areasInstitucionArray = array();
			for($i=0;$i<count($areas);$i++){
				$areasInstitucionArray[] = $areas[$i]['id'];
			}
    	}

    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('rue_update'))
    	->add('idRue', 'hidden', array('data' => $entity->getId()))
    	->add('rue', 'text', array('label' => 'Codigo RUE', 'data' => $entity->getId(), 'disabled' => true))
    	//         ->add('fechaCreacion', 'text', array('label' => 'Fecha de creacion', 'data' => $entity->getFechaCreacion(), 'disabled' => true))
    	->add('institucionEducativa', 'text', array('label' => 'Nombre', 'data' => $entity->getInstitucioneducativa(), 'disabled' => true))
    	->add('fechaResolucion', 'text', array('label' => 'Fecha resolucion', 'data' => $entity->getFechaResolucion()->format('d-m-Y'), 'disabled' => true))
    	->add('nroResolucion', 'text', array('label' => 'Resolucion', 'data' => $entity->getNroResolucion(), 'disabled' => true))
    	->add('dependenciaTipo', 'text', array('label' => 'Dependencia', 'data' => $entity->getDependenciaTipo()->getDependencia(), 'disabled' => true))
    	->add('convenioTipo', 'text', array('label' => 'Convenio', 'data' =>  $entity->getConvenioTipo()->getConvenio(), 'disabled' => true ))
    	->add('estadoTipo', 'text', array('label' => 'Estado', 'data' =>  $entity->getEstadoinstitucionTipo()->getEstadoinstitucion(), 'disabled' => true))
    	->add('institucionTipo', 'text', array('label' => 'Tipo', 'data' => $entity->getInstitucioneducativaTipo()->getDescripcion(), 'disabled' => true))
    	->add('obsRue', 'text', array('label' => 'Observaciones', 'data' => $entity->getObsRue(), 'disabled' => true))
    	->add('desUeAntes', 'text', array('label' => 'Nombre anterior', 'data' => $entity->getDesUeAntes(), 'disabled' => true))
    	->add('rueUe', 'text', array('label' => 'Registro RUE', 'data' => $entity->getRueUe(), 'disabled' => true))

    	->add('leJuridicciongeograficaId', 'text', array('label' => 'Codigo LE','data' => $entity->getLeJuridicciongeografica()->getId(), 'disabled' => true))
    	->add('departamento', 'text', array('label' => 'Departamento', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('provincia', 'text', array('label' => 'Provincia', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('municipio', 'text', array('label' => 'Municipio', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('canton', 'text', array('label' => 'Canton', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('localidad', 'text', array('label' => 'Localidad', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('zona', 'text', array('label' => 'Zona', 'data' => $entity->getLeJuridicciongeografica()->getZona(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('direccion', 'text', array('label' => 'Direccion', 'data' => $entity->getLeJuridicciongeografica()->getDireccion(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
    	->add('distrito', 'text', array('label' => 'Distrito', 'data' => $entity->getLeJuridicciongeografica()->getDistritoTipo()->getDistrito(), 'disabled' => true, 'attr' => array('class' => 'form-control')));
    	if ($entity->getInstitucioneducativaTipo()->getId() == 4) {
    		$form->add('areaEspecialTipo', 'choice', array('label' => 'Areas de especial', 'choices'=>$areasArray,  'data' => $areasInstitucionArray  , 'multiple' => true,'expanded' => true, 'disabled' => true,'attr' => array('class' => 'form-control')));
    		$form->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices'=>$nivelesInstitucionArray,  'multiple' => true,'expanded' => true,'data' => $nivelesInstitucionArray, 'disabled' => true,'attr' => array('class' => 'form-control')));
    	}
    	elseif ($entity->getInstitucioneducativaTipo()->getId() == 1 or $entity->getInstitucioneducativaTipo()->getId() == 2 or $entity->getInstitucioneducativaTipo()->getId() == 5 or $entity->getInstitucioneducativaTipo()->getId() == 6) {
    		$form->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices'=>$nivelesInstitucionArray,  'multiple' => true,'expanded' => true,'data' => $nivelesInstitucionArray, 'disabled' => true,'attr' => array('class' => 'form-control')));

		}

    	return $this->render('SieRueBundle:ConsultaInstitucionEducativa:show.html.twig', array(
			'entity' => $entity,
			//'form' => $form->getForm()->createView(),
			'institucion' => $institucion,
			'areas' => $areas,
			'niveles' => $niveles,
		));
	}
	
	public function guiaAction(Request $request) {
		
		return $this->render('SieRueBundle:ConsultaInstitucionEducativa:guia_rapida.html.twig', array());
	}
}
