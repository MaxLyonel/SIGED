<?php

namespace Sie\RueBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Form\InstitucioneducativaType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\JurisdiccionGeografica;

/**
 * Institucioneducativa controller.
 *
 */
class LocalEducativoController extends Controller {

    /**
     * locales educativos  entities.
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
        $formLe = $this->createSearchFormLe();
        $formLeId = $this->createSearchFormLeId();


        return $this->render('SieRueBundle:LocalEducativo:search.html.twig', array('formLe' => $formLe->createView(), 'formLeId' => $formLeId->createView()));
    }

    /**
     * Creates a form to search the users of student selected
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchFormLe() {
    	$em = $this->getDoctrine()->getManager();
    	$dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' => 1));
    	$depArray = array();
    	foreach ($dep as $de) {
    		$depArray[$de->getId()] = $de->getLugar();
    	}

    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('le_result'))
    			->add('tipo_search', 'hidden', array('data' => 'le'))
    			->add('departamento', 'choice', array('label' => 'Departamento:', 'required' => true,'choices'=>$depArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    			->add('provincia', 'choice', array('label' => 'Provincia:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                ->add('localidad', 'text', array('required' => true, 'invalid_message' => 'Campo obligatorio'))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    private function createSearchFormLeId() {

    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('le_result'))
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
    public function findleAction(Request $request) {
    	$form = $request->get('form');
    	$em = $this->getDoctrine()->getManager();
    	if ($form['tipo_search'] == 'le') {
    		$query = $em->createQuery(
    				'SELECT le
                FROM SieAppWebBundle:JurisdiccionGeografica le
                JOIN le.lugarTipoLocalidad lo
    			JOIN lo.lugarTipo ca
    			JOIN ca.lugarTipo se
    			JOIN se.lugarTipo pr
                WHERE  pr.id = :idProvincia
    			AND	UPPER(lo.lugar) like :localidad
          and le.juridiccionAcreditacionTipo = :jacreditation
                ORDER BY le.id')
                        ->setParameter('idProvincia',$form['provincia'])
    		                ->setParameter('localidad','%' . strtoupper($form['localidad']) . '%')
                        ->setParameter('jacreditation',1)
                        ;
    	}
    	else {
    		$query = $em->createQuery(
    				'SELECT le
                FROM SieAppWebBundle:JurisdiccionGeografica le
                WHERE le.id = :id
                and le.juridiccionAcreditacionTipo = :jacreditation
                ORDER BY le.id')
    		                ->setParameter('id', $form['leId'])
                        ->setParameter('jacreditation',1)
                        ;
    	}

    	$entities = $query->getResult();


        if (!$entities) {
        	$this->get('session')->getFlashBag()->add('msgSearch', 'No se encontro la información...');

        	$formLe = $this->createSearchFormLe();
        	$formLeId = $this->createSearchFormLeId();
        	return $this->render('SieRueBundle:LocalEducativo:search.html.twig', array('formLe' => $formLe->createView(), 'formLeId' => $formLeId->createView()));

        }

        return $this->render('SieRueBundle:LocalEducativo:resultle.html.twig', array('entities' => $entities));

    }


    /**
     * Displays a form to create a new Institucioneducativa entity.
     *
     */

    public function newAction() {

    	$form = $this->createNewForm();
    	return $this->render('SieRueBundle:LocalEducativo:new.html.twig', array('form' => $form->createView()));
    }

    private function createNewForm() {
    	$em = $this->getDoctrine()->getManager();

    	$em = $this->getDoctrine()->getManager();
    	$dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' => 1));

    	$depArray = array();
    	foreach ($dep as $de) {
    		$depArray[$de->getId()] = $de->getLugar();
    	}

    	$query = $em->createQuery(
    			'SELECT de
                FROM SieAppWebBundle:DepartamentoTipo de
                WHERE  de.id > 0
                ORDER BY de.id');

    	         $dep = $query->getResult();

    	         $depDistritoArray = array();
    	         foreach ($dep as $de) {
    	         	$depDistritoArray[$de->getId()] = $de->getDepartamento();
    	         }

    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('le_create'))
    	->add('departamento', 'choice', array('label' => 'Departamento:', 'required' => true,'choices'=>$depArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('provincia', 'choice', array('label' => 'Provincia:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('municipio', 'choice', array('label' => 'Municipio:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('canton', 'choice', array('label' => 'Cantón:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('localidad', 'choice', array('label' => 'Localidad:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('zona', 'text', array('label' => 'Zona:', 'required' => true, 'attr' => array('class' => 'form-control jupper', 'autocomplete' => 'off','maxlength'=>'255','placeholder'=>'Ingresar la zona...')))
    	->add('direccion', 'text', array('label' => 'Dirección:', 'required' => true, 'attr' => array('class' => 'form-control jupper', 'autocomplete' => 'off','maxlength'=>'255','placeholder'=>'Ingresar la dirección...')))
    	->add('departamentoDistrito', 'choice', array('label' => 'Departamento:', 'required' => true,'choices'=>$depDistritoArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('distrito', 'choice', array('label' => 'Distrito:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
    	->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
		return $form->getForm();

    }

    public function createAction(Request $request) {
    	try {
    		$em = $this->getDoctrine()->getManager();
    		$form = $request->get('form');

    		$sec = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['municipio']);
    		$secCod = $sec->getCodigo();
    		$proCod = $sec->getLugarTipo()->getCodigo();
    		$depCod = $sec->getLugarTipo()->getLugarTipo()->getCodigo();

    		$dis = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($form['distrito']);

    		$distrito = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('lugarNivel' => 7, 'codigo' => $dis->getId()));
//     		dump($distrito);die;

            $query = $em->getConnection()->prepare('SELECT get_genera_codigo_le(:dep,:pro,:sec)');
            $query->bindValue(':dep', $depCod);
            $query->bindValue(':pro', $proCod);
            $query->bindValue(':sec', $secCod);
            $query->execute();
            $codigolocal = $query->fetchAll();
//             dump($codigolocal);die;
            // Registramos el local
    		$entity = new JurisdiccionGeografica();
            $entity->setId($codigolocal[0]["get_genera_codigo_le"]);
//             dump($entity);die;

            $entity->setLugarTipoLocalidad($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['localidad']));
            $entity->setLugarTipoIdDistrito($distrito->getId());
            $entity->setDistritoTipo($dis);
            $entity->setZona(mb_strtoupper($form['zona'], 'utf-8'));
            $entity->setDireccion(mb_strtoupper($form['direccion'], 'utf-8'));
            $entity->setJuridiccionAcreditacionTipo($em->getRepository('SieAppWebBundle:JurisdiccionGeograficaAcreditacionTipo')->find(1));
//             $mensaje = 'El local educativo fue registrada correctamente  con el codigo:  ' .  $entity->getId();
//             dump($mensaje);die;

            $em->persist($entity);

    		$em->flush();

    		$this->get('session')->getFlashBag()->add('mensaje', 'El local educativo fue registrada correctamente  con el codigo:  ' .  $entity->getId() );
    		return $this->redirect($this->generateUrl('le'));
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
//         dump($request->get('idLe'));die;
        $entity = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($request->get('idLe'));

        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar el local educativo.');
        }

        //ubicacion geografica

        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' => 1));
        $depArray = array();
        foreach ($dep as $de) {
        	$depArray[$de->getId()] = $de->getLugar();
        }
        //         dump($inscripcioie;

        $pro = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $entity->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getid()));

        $proArray = array();
        foreach ($pro as $p) {
        	$proArray[$p->getid()] = $p->getlugar();
        }

        $sec = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 3, 'lugarTipo' => $entity->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getid()));

        $secArray = array();
        foreach ($sec as $s) {
        	$secArray[$s->getid()] = $s->getlugar();
        }

        $can = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 4, 'lugarTipo' => $entity->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getid()));

        $canArray = array();
        foreach ($can as $c) {
        	$canArray[$c->getid()] = $c->getlugar();
        }

        $loc = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 5, 'lugarTipo' => $entity->getLugarTipoLocalidad()->getLugarTipo()->getid()));

        $locArray = array();
        foreach ($loc as $l) {
        	$locArray[$l->getid()] = $l->getlugar();
        }




        $query = $em->createQuery(
        		'SELECT de
                FROM SieAppWebBundle:DepartamentoTipo de
                WHERE  de.id > 0
                ORDER BY de.id');

        $dep = $query->getResult();
        //     	         dump($dep);die;
        $depDistritoArray = array();
        foreach ($dep as $de) {
        	$depDistritoArray[$de->getId()] = $de->getDepartamento();
        }


        $dis = $em->getRepository('SieAppWebBundle:DistritoTipo')->findBy(array('departamentoTipo' =>  $entity->getDistritoTipo()->getDepartamentoTipo()->getId()));
        $query = $em->createQuery(
            'SELECT d
            FROM SieAppWebBundle:DistritoTipo d
            WHERE  d.departamentoTipo = :departamento AND d.id NOT IN (:cabeceras)
            ORDER BY d.id')
            ->setParameter('departamento', $entity->getDistritoTipo()->getDepartamentoTipo()->getId())
            ->setParameter('cabeceras', array(1000,2000,3000,4000,5000,6000,7000,8000,9000));

        $dis = $query->getResult();

        $distrito = array();
        foreach ($dis as $d) {
        	$distrito[$d->getid()] = $d->getDistrito();
        }

        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('le_update'))
        ->add('idLe', 'hidden', array('data' => $entity->getId()))
        ->add('codLe', 'text', array('label' => 'Código LE', 'disabled' => true, 'data' => $entity->getId(), 'attr' => array('class' => 'form-control')))
        ->add('departamento', 'choice', array('label' => 'Departamento', 'required' => true,'choices'=>$depArray ,'data' => $entity->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId(),'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
        ->add('provincia', 'choice', array('label' => 'Provincia', 'required' => true,'choices'=>$proArray ,'data' => $entity->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId(), 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
        ->add('municipio', 'choice', array('label' => 'Municipio', 'required' => true,'choices'=>$secArray ,'data' => $entity->getLugarTipoLOcalidad()->getLugarTipo()->getLugarTipo()->getId(), 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
        ->add('canton', 'choice', array('label' => 'Canton', 'required' => true,'choices'=>$canArray ,'data' => $entity->getLugarTipoLocalidad()->getLugarTipo()->getId(), 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
        ->add('localidad', 'choice', array('label' => 'Localidad', 'required' => true,'choices'=>$locArray ,'data' => $entity->getLugarTipoLocalidad()->getId(), 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
        ->add('zona', 'text', array('label' => 'Zona', 'required' => true, 'data' => $entity->getZona(), 'attr' => array('class' => 'form-control jupper', 'autocomplete' => 'off','maxlength'=>'255')))
        ->add('direccion', 'text', array('label' => 'Direccion', 'required' => true, 'data' => $entity->getDireccion(), 'attr' => array('class' => 'form-control jupper', 'autocomplete' => 'off','maxlength'=>'255')))
        ->add('departamentoDistrito', 'choice', array('label' => 'Departamento', 'required' => true,'choices'=>$depDistritoArray ,'data' => $entity->getDistritoTipo()->getDepartamentoTipo()->getId(),'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
        ->add('distrito', 'choice', array('label' => 'Distrito ', 'required' => true,'choices'=>$distrito ,'data' => $entity->getDistritoTipo()->getId(), 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))

        ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));

        return $this->render('SieRueBundle:LocalEducativo:edit.html.twig', array('entity' => $entity,'form' => $form->getForm()->createView()));


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
//     	dump($form);die;
    	$entity = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($form['idLe']);

    	$dis = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($form['distrito']);

    	$distrito = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('lugarNivel' => 7, 'codigo' => $dis->getId()));
    	//     		dump($distrito);die;

    	$entity->setLugarTipoLocalidad($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['localidad']));
    	$entity->setLugarTipoIdDistrito($distrito->getId());
    	$entity->setDistritoTipo($dis);
    	$entity->setZona(mb_strtoupper($form['zona'], 'utf-8'));
        $entity->setDireccion(mb_strtoupper($form['direccion'], 'utf-8'));
    	//             $mensaje = 'El local educativo fue registrada correctamente  con el codigo:  ' .  $entity->getId();
    	//             dump($mensaje);die;

    	$em->persist($entity);

    	$em->flush();

    	$this->get('session')->getFlashBag()->add('mensaje', 'El local educativo fue registrada correctamente' );
    	return $this->redirect($this->generateUrl('le'));

    	} catch (Exception $ex) {
    		$this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar el local educativo' );
    		return $this->redirect($this->generateUrl('le'));
    	}

    }


//     public function updateAction(Request $request, $id) {
//         $em = $this->getDoctrine()->getManager();

//         $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);

//         if (!$entity) {
//             throw $this->createNotFoundException('Unable to find Institucioneducativa entity.');
//         }

//         $deleteForm = $this->createDeleteForm($id);
//         $editForm = $this->createEditForm($entity);
//         $editForm->handleRequest($request);

//         if ($editForm->isValid()) {
//             $em->flush();

//             return $this->redirect($this->generateUrl('rue_edit', array('id' => $id)));
//         }

//         return $this->render('SieRueBundle:RegistroInstitucionEducativa:edit.html.twig', array(
//                     'entity' => $entity,
//                     'edit_form' => $editForm->createView(),
//                     'delete_form' => $deleteForm->createView(),
//         ));
//     }

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

//     public function viewAction(Request $request) {
//         //$sesion = $this->getRequest()->getSession();

//         $sesion = new Session();
//         $em = $this->getDoctrine()->getManager();
//         $form = $request->get('form');

//         if ($request->getMethod() == 'POST') {
//             $sesion->set('institucioneducativa', $form['institucioneducativa']);
//         }
//         $form['institucioneducativa'] = ($sesion->get('institucioneducativa')) ? $sesion->get('institucioneducativa') : $form['institucioneducativa'];
//         $insEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
//         if ($insEducativa) {

//             $entities = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario' => $sesion->get('userId2')));
//             //get the lugar tipo para el usuario loggeado
//             $alugarTipo = array();
//             foreach ($entities as $entity) {
//                 if ($entity->getLugarTipo())
//                     $alugarTipo[] = $entity->getLugarTipo()->getId();
//             }
//             //verificamos si tiene tuicion para buscar sus usuarios
//             $entities = $em->getRepository('SieAppWebBundle:LugarTipo');
//             $query = $entities->createQueryBuilder('lt')
//                     ->select('lt')
//                     ->LeftJoin('SieAppWebBundle:JurisdiccionGeografica', 'jg', 'WITH', 'lt.id=jg.lugarTipoLocalidad')
//                     ->LeftJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'jg.id=ie.leJuridicciongeografica')
//                     ->where('ie.id = :sie')
//                     ->setParameter('sie', $form['institucioneducativa'])
//                     ->getQuery();
//             $lugares = $query->getResult();

//             //paso inicial para hacer la busqueda de la tuiion, obtenemos el id de lugar
//             foreach ($lugares as $lugar) {
//                 $lugarTipoId = $lugar->getLugarTipo()->getId();
//             }
//             // creamos una bandera para la busqueda de la tuicion
//             $sw = 0;
//             if (in_array($lugarTipoId, $alugarTipo))
//                 $sw = 1;
//             //realizamos la busqueda del id de lugar para determinar la tuicion
//             while (!$sw && $lugarTipoId) {
//                 $newEntity = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lugarTipoId);
//                 if (in_array($newEntity->getId(), $alugarTipo)) {
//                     $sw = 1;
//                 } else {
//                     $lugarTipoId = (is_object($newEntity->getLugarTipo())) ? $newEntity->getLugarTipo()->getId() : 0;
//                 }
//             }
//             // no tiene tuicion mostramos la opcion de busqueda
//             if ($sw) {
//                 $sesion->getFlashBag()->add('notice', 'El usuario no tiene tuicion para completar la busqueda...');
//                 return $this->render('SieRueBundle:RegistroInstitucionEducativa:search.html.twig', array(
//                             'form' => $this->createSearchFormSie()->createView(),
//                 ));
//             }
//             //si tenemos tuicion, realizamos la busqueda de los usuarios dependiendo del rol **para el caso distrital no problem
//             //necesitamos ADD el rol para obtener los directores y secretarias -- esto hasta que tengamos datos
//             $qusers = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
//             $query = $qusers->createQueryBuilder('mi')
//                     ->select('mi.id as maestroInsId,p.id as personId, p.paterno, p.materno, p.nombre', 'IDENTITY( mi.rolTipo) as rolId', '( ct.cargo) as cargo')
//                     ->LeftJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona=p.id')
//                     ->leftjoin('SieAppWebBundle:CargoTipo','ct', 'WITH','mi.cargoTipo = ct.id')
//                     ->where('mi.institucioneducativa = :sie')
//                     ->andwhere('mi.gestionTipo = :gestion')
//                     ->setParameter('sie', $form['institucioneducativa'])
//                     ->setParameter('gestion', $sesion->get('currentyear'))
//                     ->getQuery();
//             $personal = $query->getResult();
// //            echo "<pre>";
// //            print_r($personal);
// //            echo "</pre>";
// //            die;
//             return $this->render('SieRueBundle:RegistradoInstitucionEducativa:view.html.twig', array('institucion' => $insEducativa, 'personal' => $personal));
//         } else {
//             $sesion->getFlashBag()->add('msgSearch', 'No existe informaciÃ³n para el cÃ³digo introducido...');
//             return $this->render('SieRueBundle:RegistroInstitucionEducativa:search.html.twig', array(
//                         'form' => $this->createSearchFormSie()->createView(),
//             ));
//         }



// //        print_r($lugarTipoId);
//         //tiene tuicion obtenemos sus usuarios dependiendo del user loggeado



//         if ($request->getMethod() == 'POST' or ! ($sesion->get('institucioneducativa'))) {
//             if ($request->getMethod() == 'POST') {
//                 $form = $request->get('form');
//                 $form['institucioneducativa'];
//                 $institucion = $form['institucioneducativa'];
//                 $sesion->set('institucioneducativa', $institucion);
//             } else {
//                 $institucion = $sesion->get('institucioneducativa');
//             }
//             $em = $this->getDoctrine()->getManager();
//             $result = $em->getRepository('SieAppWebBundle:UsuarioInstitucioneducativa')->findOneBy(array('usuario' => 8050342, 'institucioneducativa' => $institucion)); // funcion para ver la tuicion del usuario sobre la unidad educativa
//             if (!$result) { // si tiene tuicion
//                 $this->get('session')->getFlashBag()->add('msgSearch', 'No tiene permisos');
//                 return $this->redirect($this->generateUrl('rue'));
//             }
//             $insEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);

//             $query = $em->createQuery(
//                             'SELECT mains, per, carT FROM SieAppWebBundle:MaestroInscripcion mains
//                     JOIN mains.persona per
//                     JOIN mains.cargoTipo carT
//                     WHERE mains.institucioneducativa = :insEdu'
//                     )->setParameter('insEdu', $institucion);

//             $personal = $query->getResult();

//             //$personal = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('institucioneducativa'=>$form['institucioneducativa'],'gestionTipo'=>2015));
//             if (!$personal) {
//                 echo "no hay";
//                 die;
//                 $personal = array();
//             }
//             return $this->render('SieRueBundle:RegistroInstitucionEducativa:view.html.twig', array('institucion' => $insEducativa, 'personal' => $personal));
//         }
//         return $this->redirect($this->generateUrl('rue'));
//     }






    public function buscaredificioAction($idLe) {
//echo "dfsd";die;


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



    public function provinciasAction($idDepartamento) {
    	$em = $this->getDoctrine()->getManager();
    	$prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $idDepartamento));
        $provincia = array();
        //$provincia[-1] = '-Todos-';
    	foreach ($prov as $p) {
            if ($p->getLugar() != "NO EXISTE EN CNPV 2001"){
                $provincia[$p->getid()] = $p->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('provincia' => $provincia));
    }

    public function municipiosAction($idProvincia) {
    	$em = $this->getDoctrine()->getManager();
    	$mun = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 3, 'lugarTipo' => $idProvincia));
        $municipio = array();
        //$municipio[-1] = '-Todos-';
    	foreach ($mun as $m) {
            if ($m->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $municipio[$m->getid()] = $m->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('municipio' => $municipio));
    }

    public function cantonesAction($idMunicipio) {
    	$em = $this->getDoctrine()->getManager();
    	$can = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 4, 'lugarTipo' => $idMunicipio));
        $canton = array();
        //$canton[-1] = '-Todos-';
    	foreach ($can as $c) {
            if ($c->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $canton[$c->getid()] = $c->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('canton' => $canton));
    }

    public function localidadesAction($idCanton) {
    	$em = $this->getDoctrine()->getManager();
    	$loc = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 5, 'lugarTipo' => $idCanton));
        $localidad = array();
        //$localidad[-1] = '-Todos-';
    	foreach ($loc as $l) {
            if ($l->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $localidad[$l->getid()] = $l->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('localidad' => $localidad));
    }

    public function distritosAction($idDepartamento) {
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
            foreach ($distrito as $c) {
                $distritoArray[$c->getId()] = $c->getDistrito();
            }

    	$response = new JsonResponse();
    	return $response->setData(array('distrito' => $distritoArray));
    }



}
