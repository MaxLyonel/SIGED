<?php
namespace Sie\RieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\JurisdiccionGeografica;

use Sie\RieBundle\Form\JurisdiccionGeograficaType;
use Sie\AppWebBundle\Form\InstitucioneducativaType;

/**
 * Institucioneducativa controller.
 *
 */
class LocalEducativoController extends Controller {

    /**
     * locales educativos  entities.
     *
     */
    public function indexAction(Request $request){
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)){
            return $this->redirect($this->generateUrl('login'));
        }
        // Creacion de formularios de busqueda por codigo rie o nombre de institucion educativa
        $formLe = $this->createSearchFormLe();
        $formLeId = $this->createSearchFormLeId();
        
        return $this->render('SieRieBundle:LocalEducativo:search.html.twig', array('formLe' => $formLe->createView(), 'formLeId' => $formLeId->createView()));
    }

    /**
     * Creates a form to search the users of student selected
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchFormLe(){
    	$em = $this->getDoctrine()->getManager();
    	$dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' => 1));
    	$depArray = array();
    	foreach ($dep as $de){
    		$depArray[$de->getId()] = $de->getLugar();
    	}

    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('lei_result'))
    			->add('tipo_search', 'hidden', array('data' => 'le'))
    			->add('departamento', 'choice', array('label' => 'Departamento:', 'required' => true,'choices'=>$depArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    			->add('provincia', 'choice', array('label' => 'Provincia:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                ->add('localidad', 'text', array('required' => false))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    private function createSearchFormLeId(){
    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('lei_result'))
    	->add('tipo_search', 'hidden', array('data' => 'leId'))
    	->add('leId', 'text', array('required' => true,'invalid_message' => 'Campo obligatorio', 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
    	->add('buscarId', 'submit', array('label' => 'Buscar'))
    	->getForm();
    	return $form;
    }

    /**
     * Busca local educativo.
     *
     */
    public function findleAction(Request $request){
      $sesion = $request->getSession();
      $id_usuario = $sesion->get('userId');
      if (!isset($id_usuario)){
          return $this->redirect($this->generateUrl('login'));
      }
    	$form = $request->get('form');
        $em = $this->getDoctrine()->getManager();

        switch($form['tipo_search'])
        {
            case 'le':
                $provincia = $form['provincia'];
                $localidad = $form['localidad'];
                if(empty($form['localidad'])){ //no existe localidad
                    $db = $em->getConnection();
                    $query = "SELECT COUNT(ie.id) AS instituto, lt4.lugar AS departamento, lt3.lugar AS provincia, lt2.lugar AS municipio, lt1.lugar AS canton, lt.lugar AS localidad, jg.*
                               FROM jurisdiccion_geografica jg 
                          LEFT JOIN lugar_tipo AS lt ON lt.id = jg.lugar_tipo_id_localidad
                          LEFT JOIN lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
                          LEFT JOIN lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
                          LEFT JOIN lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
                          LEFT JOIN lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
                          LEFT JOIN institucioneducativa ie
                                 ON jg.id = ie.le_juridicciongeografica_id
                              WHERE juridiccion_acreditacion_tipo_id = 1
                                AND lt3.id = ".$provincia."
                           GROUP BY jg.id, lt.lugar, lt1.lugar, lt2.lugar, lt3.lugar, lt4.lugar 
                           ORDER BY instituto ASC";
                    $stmt = $db->prepare($query);
                    $params = array();
                    $stmt->execute($params);
                    $entities = $stmt->fetchAll();                    

                } else {
                    $db = $em->getConnection();
                    $query = "SELECT COUNT(ie.id) AS instituto, lt4.lugar AS departamento, lt3.lugar AS provincia, lt2.lugar AS municipio, lt1.lugar AS canton, lt.lugar AS localidad, jg.*
                               FROM jurisdiccion_geografica jg 
                          LEFT JOIN lugar_tipo AS lt ON lt.id = jg.lugar_tipo_id_localidad
                          LEFT JOIN lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
                          LEFT JOIN lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
                          LEFT JOIN lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
                          LEFT JOIN lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
                          LEFT JOIN institucioneducativa ie
                                 ON jg.id = ie.le_juridicciongeografica_id
                              WHERE juridiccion_acreditacion_tipo_id = 1
                                AND lt3.id = ".$provincia."
                                AND lt.lugar LIKE '%".$localidad."%' 
                           GROUP BY jg.id, lt.lugar, lt1.lugar, lt2.lugar, lt3.lugar, lt4.lugar 
                           ORDER BY instituto ASC";
                    $stmt = $db->prepare($query);
                    $params = array();
                    $stmt->execute($params);
                    $entities = $stmt->fetchAll();   
                }
            break;

            //opcion de local educativo por 
            case 'leId':   
                    $db = $em->getConnection();
                    $query = "SELECT COUNT(ie.id) AS instituto,lt4.lugar AS departamento, lt3.lugar AS provincia, lt2.lugar AS municipio, lt1.lugar AS canton, lt.lugar AS localidad, jg.*
                                FROM jurisdiccion_geografica jg 
                           LEFT JOIN lugar_tipo AS lt ON lt.id = jg.lugar_tipo_id_localidad
                           LEFT JOIN lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
                           LEFT JOIN lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
                           LEFT JOIN lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
                           LEFT JOIN lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
                           LEFT JOIN institucioneducativa ie
                                  ON jg.id = ie.le_juridicciongeografica_id
                               WHERE jg.id = ".$form['leId']."
                                AND juridiccion_acreditacion_tipo_id = 1
                            GROUP BY jg.id, lt.lugar, lt1.lugar, lt2.lugar, lt3.lugar, lt4.lugar 
                            ORDER BY instituto ASC";
                    $stmt = $db->prepare($query);
                    $params = array();
                    $stmt->execute($params);
                    $entities = $stmt->fetchAll();
            break;
        }

        if (!$entities){
        	$this->get('session')->getFlashBag()->add('msgSearch', 'No se encontro la información...');
        	$formLe = $this->createSearchFormLe();
        	$formLeId = $this->createSearchFormLeId();
        	return $this->render('SieRieBundle:LocalEducativo:search.html.twig', array('formLe' => $formLe->createView(), 'formLeId' => $formLeId->createView()));
        }
        return $this->render('SieRieBundle:LocalEducativo:resultle.html.twig', array('entities' => $entities));
    }
    
    /* 
    * Formulario de nuevo local educativo
    */    
    public function newAction(Request $request){
      $sesion = $request->getSession();
      $id_usuario = $sesion->get('userId');
      if (!isset($id_usuario)){
          return $this->redirect($this->generateUrl('login'));
      }
    	$form = $this->createNewForm();
    	return $this->render('SieRieBundle:LocalEducativo:new.html.twig', array('form' => $form->createView()));
    }

    /**
     * Displays a form to create a new Institucioneducativa entity.
     *
     */
    private function createNewForm(){
    	$em = $this->getDoctrine()->getManager();
    	$em = $this->getDoctrine()->getManager();
    	$dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' => 1));
    	$depArray = array();
    	foreach ($dep as $de){
    		$depArray[$de->getId()] = $de->getLugar();
    	}

    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('lei_create'))
    	->add('departamento', 'choice', array('label' => 'Departamento:', 'required' => true,'choices'=>$depArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('provincia', 'choice', array('label' => 'Provincia:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('municipio', 'choice', array('label' => 'Municipio:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('canton', 'choice', array('label' => 'Cantón:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('localidad', 'choice', array('label' => 'Localidad:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('zona', 'text', array('label' => 'Zona:', 'required' => true, 'attr' => array('class' => 'form-control jupper', 'autocomplete' => 'off','maxlength'=>'255','placeholder'=>'Ingresar la zona...')))
    	->add('direccion', 'text', array('label' => 'Dirección:', 'required' => true, 'attr' => array('class' => 'form-control jupper', 'autocomplete' => 'off','maxlength'=>'255','placeholder'=>'Ingresar la dirección...')))
    	->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
		return $form->getForm();
        
    }

    /**
     * Guarda los datos del nuevo local educativo
     *
     */
    public function createAction(Request $request) {
    	try{
    		$em = $this->getDoctrine()->getManager();
            $form = $request->get('form');
            $sec = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['municipio']);
            
    		$secCod = $sec->getCodigo();
    		$proCod = $sec->getLugarTipo()->getCodigo();
            $depCod = $sec->getLugarTipo()->getLugarTipo()->getCodigo();
            
            $query = $em->getConnection()->prepare('SELECT get_genera_codigo_le(:dep,:pro,:sec)');
            $query->bindValue(':dep', $depCod);
            $query->bindValue(':pro', $proCod);
            $query->bindValue(':sec', $secCod);
            $query->execute();
            $codigolocal = $query->fetchAll();
            
            $entity = new JurisdiccionGeografica();
            $entity->setId($codigolocal[0]["get_genera_codigo_le"]);
            $entity->setLugarTipoLocalidad($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['localidad']));
            $entity->setLugarTipoIdDistrito(31352); // Se coloco ese codigo pq se refiere a NINGUNO:31352
            //$entity->setDistritoTipoId(1);   // no toma en cuenta el distrito id
            $entity->setZona(mb_strtoupper($form['zona'], 'utf-8'));
            $entity->setDireccion(mb_strtoupper($form['direccion'], 'utf-8'));
            $entity->setJuridiccionAcreditacionTipo($em->getRepository('SieAppWebBundle:JurisdiccionGeograficaAcreditacionTipo')->find(1));
            $em->persist($entity);
    		$em->flush();
    		$this->get('session')->getFlashBag()->add('mensaje', 'El local educativo fue registrada correctamente  con el codigo:  ' .  $entity->getId() );
            return $this->redirect($this->generateUrl('lei'));
                  
    	} catch (Exception $ex) {
    		$this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar el local educativo' );
    		return $this->redirect($this->generateUrl('le_new'));
        }
    }

    /**
     * Finds and displays a Institucioneducativa entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
        if (!$entity){
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
        //dump($request->get('idLe'));die;

        $entity = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($request->get('idLe'));

        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar el local educativo.');
        }

        //ubicacion geografica
        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' => 1));
        $depArray = array();
        foreach ($dep as $de){
        	$depArray[$de->getId()] = $de->getLugar();
        }

        $pro = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $entity->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getid()));

        $proArray = array();
        foreach ($pro as $p){
        	$proArray[$p->getid()] = $p->getlugar();
        }

        $sec = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 3, 'lugarTipo' => $entity->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getid()));

        $secArray = array();
        foreach ($sec as $s){
        	$secArray[$s->getid()] = $s->getlugar();
        }

        $can = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 4, 'lugarTipo' => $entity->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getid()));

        $canArray = array();
        foreach ($can as $c){
        	$canArray[$c->getid()] = $c->getlugar();
        }

        $loc = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 5, 'lugarTipo' => $entity->getLugarTipoLocalidad()->getLugarTipo()->getid()));

        $locArray = array();
        foreach ($loc as $l){
        	$locArray[$l->getid()] = $l->getlugar();
        }

        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('lei_update'))
        ->add('idLe', 'hidden', array('data' => $entity->getId()))
        ->add('codLe', 'text', array('label' => 'Código LE', 'disabled' => true, 'data' => $entity->getId(), 'attr' => array('class' => 'form-control')))
        ->add('departamento', 'choice', array('label' => 'Departamento', 'required' => true,'choices'=>$depArray ,'data' => $entity->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId(),'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
        ->add('provincia', 'choice', array('label' => 'Provincia', 'required' => true,'choices'=>$proArray ,'data' => $entity->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId(), 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
        ->add('municipio', 'choice', array('label' => 'Municipio', 'required' => true,'choices'=>$secArray ,'data' => $entity->getLugarTipoLOcalidad()->getLugarTipo()->getLugarTipo()->getId(), 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
        ->add('canton', 'choice', array('label' => 'Cantón', 'required' => true,'choices'=>$canArray ,'data' => $entity->getLugarTipoLocalidad()->getLugarTipo()->getId(), 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
        ->add('localidad', 'choice', array('label' => 'Localidad', 'required' => true,'choices'=>$locArray ,'data' => $entity->getLugarTipoLocalidad()->getId(), 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
        ->add('zona', 'text', array('label' => 'Zona', 'required' => true, 'data' => $entity->getZona(), 'attr' => array('class' => 'form-control jupper', 'autocomplete' => 'off','maxlength'=>'255')))
        ->add('direccion', 'text', array('label' => 'Dirección', 'required' => true, 'data' => $entity->getDireccion(), 'attr' => array('class' => 'form-control jupper', 'autocomplete' => 'off','maxlength'=>'255')))
        ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));

        return $this->render('SieRieBundle:LocalEducativo:edit.html.twig', array('entity' => $entity,'form' => $form->getForm()->createView()));
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
    	try {
    	$this->session = new Session();
    	$form = $request->get('form');
    	/*
    	 * Actualizacion de datos local educativo
    	 */
    	$em = $this->getDoctrine()->getManager();
    	$entity = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($form['idLe']);

    	$entity->setLugarTipoLocalidad($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['localidad']));
        $entity->setLugarTipoIdDistrito(31352); // Se coloco ese codigo pq se refiere a NINGUNO:31352
    	//$entity->setDistritoTipo($dis);
    	$entity->setZona(mb_strtoupper($form['zona'], 'utf-8'));
        $entity->setDireccion(mb_strtoupper($form['direccion'], 'utf-8'));
    	$em->persist($entity);
    	$em->flush();
    	$this->get('session')->getFlashBag()->add('mensaje', 'Los datos de local educativo fueron correctamente modificados' );
    	return $this->redirect($this->generateUrl('lei'));

    	}catch (Exception $ex){
    		$this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar el local educativo' );
    		return $this->redirect($this->generateUrl('lei'));
    	}
    }

    /**
     * Deletes a Institucioneducativa entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if($form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);

            if(!$entity){
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

    public function buscaredificioAction($idLe){
    	$em = $this->getDoctrine()->getManager();
    	$edificio = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($idLe);

    	$departamento = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar() : "";
    	$provincia = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar() : "";
    	$municipio = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugar() : "";
    	$canton = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugar() : "";
    	$localidad = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugar() : "";
    	$zona = "";
    	$direccion = "";
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

    public function provinciasAction($idDepartamento){
    	$em = $this->getDoctrine()->getManager();
    	$prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $idDepartamento));
    	$provincia = array();
    	foreach($prov as $p){
            if($p->getLugar() != "NO EXISTE EN CNPV 2001"){
                $provincia[$p->getid()] = $p->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('provincia' => $provincia));
    }

    public function municipiosAction($idProvincia){
    	$em = $this->getDoctrine()->getManager();
    	$mun = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 3, 'lugarTipo' => $idProvincia));
    	$municipio = array();
    	foreach($mun as $m){
            if($m->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $municipio[$m->getid()] = $m->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('municipio' => $municipio));
    }

    public function cantonesAction($idMunicipio){
    	$em = $this->getDoctrine()->getManager();
    	$can = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 4, 'lugarTipo' => $idMunicipio));
    	$canton = array();
    	foreach($can as $c){
            if($c->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $canton[$c->getid()] = $c->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('canton' => $canton));
    }

    public function localidadesAction($idCanton){
    	$em = $this->getDoctrine()->getManager();
    	$loc = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 5, 'lugarTipo' => $idCanton));
    	$localidad = array();
    	foreach($loc as $l) {
            if($l->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $localidad[$l->getid()] = $l->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('localidad' => $localidad));
    }

    public function distritosAction($idDepartamento){
        $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery(
                                        'SELECT dt
                                           FROM SieAppWebBundle:DistritoTipo dt
                                          WHERE dt.id NOT IN (:ids)
                                            AND dt.departamentoTipo = :dpto
                                       ORDER BY dt.id')
                    ->setParameter('ids', array(1000,2000,3000,4000,5000,6000,7000,8000,9000))
                    ->setParameter('dpto', $idDepartamento);
            $distrito = $query->getResult();

            $distritoArray = array();
            foreach($distrito as $c){
                $distritoArray[$c->getId()] = $c->getDistrito();
            }

    	$response = new JsonResponse();
    	return $response->setData(array('distrito' => $distritoArray));
    }

    /***
     * Verifica si el local educativo tiene asignado una institucion educativa.
     * En caso de Verdad no puede realizarse ninguna modificacion 
     */ // REVISAR OJO 

    public function obtieneControlEdicion($idLe){
        $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $repository->createQueryBuilder('ie')
                            ->join('ie.leJuridicciongeografica', 'le')
                            ->where('le.id = :idLocal')
                            ->setParameter('idLocal', $idLe)
                            ->getQuery();
        $dato = $query->getResult();
        if($dato)
            return 0;
        else
            return 1;
    }
}
