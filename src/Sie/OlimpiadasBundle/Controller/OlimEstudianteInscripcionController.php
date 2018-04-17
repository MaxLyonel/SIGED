<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sie\AppWebBundle\Entity\OlimEstudianteInscripcion;
use Sie\AppWebBundle\Entity\OlimInscripcionGrupoProyecto;
use Sie\AppWebBundle\Form\OlimEstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * OlimEstudianteInscripcion controller.
 *
 */
/**
 * Author: Carlos Pacha Cordova <pckrlos@gmail.com>
 * Description:This is a class for load the inscription data; if exist in a particular proccess
 * Date: 03-04-2018
 *
 *
 * OlimEstudianteInscripcionController.php
 *
 * Email bugs/suggestions to <pckrlos@gmail.com>
 */

class OlimEstudianteInscripcionController extends Controller{

    private $sesion;

    public function __construct(){
        $this->session = new Session();
    }
    /**
     * Lists all OlimEstudianteInscripcion entities.
     *
     */
    public function indexAction(){
        
        $em = $this->getDoctrine()->getManager();

        // $entities = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->findAll();

        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:index.html.twig', array(
            'form' => $this->inscriptionForm()->createView(),
            // 'entities' => $entities,
        ));
    }

    /**
     * [inscriptionForm description]
     * @return [type] [description]
     */
    private function inscriptionForm(){
        
        $arrAreas = $this->get('olimfunctions')->getAllowedAreasByOlim();
        // dump($arrAreas);die;
        $newform = $this->createFormBuilder()
                ->add('olimMateria', 'choice', array('label'=>'materias','choices'=>$arrAreas,  'empty_value' => 'Seleccionar Materia'))
                ->add('category', 'choice', array('label'=>'categoria', ))
                ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestion', 'choices' => array($this->session->get('currentyear')=>$this->session->get('currentyear')), 'attr' => array('class' => 'form-control')))
                // ->add('buscar', 'button', array('label'=>'Buscar', 'attr'=>array('onclick'=>'openInscriptinoOlimpiadas();'), ))
                
                ;
        // if($this->session->get('roluser')==8){
            $newform = $newform
                ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control', 'value'=>$this->session->get('ie_id'))))
                ;
        // }

        $newform = $newform->getForm();
        return $newform;

    }
    /**
     * [getCategoryAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getCategoryAction(Request $request){
        // get the send values
        $idmateria = $request->get('idmateria');

        $em = $this->getDoctrine()->getManager();
        //get grado
        $agrados = array();
        $entity = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo');
        $query = $entity->createQueryBuilder('orot')
                // ->select('oct.id, oct.categoria')
                ->where('orot.olimMateriaTipo = :materiaid')
                ->setParameter('materiaid', $idmateria)
                ->orderBy('orot.categoria', 'ASC')
                ->getQuery();
        //         // dump($quer $entity = $em->getRepository('SieAppWebBundle:OlimCategoriaTipo');
        // $query = $entity->createQueryBuilder('oct')
        //         ->select('oct.id, oct.categoria')
        //         ->where('oct.olimMateriaTipo = :materiaid')
        //         ->setParameter('materiaid', $idmateria)
        //         ->orderBy('oct.categoria', 'ASC')
        //         ->getQuery();
        //         // dump($query->getSQL());die;
        $objCategory = $query->getResult();

        // dump($objCategory);die;
        $arrCategory = array();
        if(sizeof($objCategory)>0){
            
            foreach ($objCategory as $value) {
                if($value->getCategoria() == NULL)
                    $arrCategory[$value->getId()] = 'Sin categoria';
                else
                    $arrCategory[$value->getId()] = $value->getCategoria();

                // $arrCategory[$value['id']] = $value['categoria'];
                
                
            }    
        }else{
            // $arrCategory[1000]='No hay categoria';
        }
        
        // dumP($arrCategory);die;

        $response = new JsonResponse();
        return $response->setData(array('arrCategory' => $arrCategory));
    }
    
    /**
     * [openInscriptinoOlimpiadasAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function openInscriptinoOlimpiadasAction(Request $request){
        //create db conexion
        $em = $this->getDoctrine()->getManager();
        // get the send values
        $form = $request->get('form');
        // dump($form);die;
        $form['sie']= ($this->session->get('roluser')==8)?$form['institucionEducativa']:$this->session->get('ie_id');
        
           
        // dump($jsondataInscription);die;
        //get info about materia
        $entity = $em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($form['olimMateria']);
        $objInstitucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);
        $category = ($form['category'])?$em->getRepository('SieAppWebBundle:OlimCategoriaTipo')->find($form['category'])->getCategoria():'';
        
        // dump($objInstitucionEducativa);die;
        // render the view
        $jsondataInscription = json_encode($form) ;
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:openInscriptinoOlimpiadas.html.twig', array(
            'jsondataInscription'=>$jsondataInscription,
            'entity'=>$entity,
            'objInstitucionEducativa'=>$objInstitucionEducativa,
            'category'=>$category,
        ));
    }

    /**
     * [regDirectorAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function regDirectorAction(Request $request){

        dump($request) ;die;
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:openInscriptinoOlimpiadas.html.twig', array(
            'jsondataInscription'=>$jsondataInscription,
        ));
    }
    /**
     * Creates a new OlimEstudianteInscripcion entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new OlimEstudianteInscripcion();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('oliminscriptions_show', array('id' => $entity->getId())));
        }

        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a OlimEstudianteInscripcion entity.
     *
     * @param OlimEstudianteInscripcion $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(OlimEstudianteInscripcion $entity)
    {
        $form = $this->createForm(new OlimEstudianteInscripcionType(), $entity, array(
            'action' => $this->generateUrl('oliminscriptions_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new OlimEstudianteInscripcion entity.
     *
     */
    public function newAction(){
        $entity = new OlimEstudianteInscripcion();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }


    /**
     * Finds and displays a OlimEstudianteInscripcion entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimEstudianteInscripcion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:OlimEstudianteInscripcion:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing OlimEstudianteInscripcion entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimEstudianteInscripcion entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:OlimEstudianteInscripcion:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a OlimEstudianteInscripcion entity.
    *
    * @param OlimEstudianteInscripcion $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(OlimEstudianteInscripcion $entity)
    {
        $form = $this->createForm(new OlimEstudianteInscripcionType(), $entity, array(
            'action' => $this->generateUrl('oliminscriptions_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing OlimEstudianteInscripcion entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimEstudianteInscripcion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('oliminscriptions_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:OlimEstudianteInscripcion:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a OlimEstudianteInscripcion entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OlimEstudianteInscripcion entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('oliminscriptions'));
    }

    /**
     * Creates a form to delete a OlimEstudianteInscripcion entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('oliminscriptions_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }


    public function commonAreaInscriptionAction(Request $request){
        //get the send data
        $jsonDataInscription = $request->get('jsonDataInscription');
        $tutorid = $request->get('tutorid');
        $arrDataInscription = json_decode($jsonDataInscription,true);
        $arrDataInscription['tutorid'] = $tutorid;
        $jsonDataInscription = json_encode($arrDataInscription);
        // dump($jsonDataInscription);die;
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $objOlimTutor = $em->getRepository('SieAppWebBundle:OlimTutor')->find($tutorid);
        $objPersona = $em->getRepository('SieAppWebBundle:Persona')->find($objOlimTutor->getPersona());
        // dump($objPersona);
        // die;
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:commonAreaInscription.html.twig', array(
            'objPersona' => $objPersona,
            'objOlimTutor' => $objOlimTutor,
            'jsonDataInscription' => $jsonDataInscription,

        ));

    }

    public function doInscriptionOStudentAction(Request $request){
        // create db conexion
        $em=$this->getDoctrine()->getManager();
        //get the send data
        $jsonDataInscription = $request->get('jsonDataInscription');
        $arrDataInscription = json_decode($jsonDataInscription,true);
        
        $objRegla = $this->get('olimfunctions')->getReglaByMateriaCategoryGestion($arrDataInscription);
        $objNiveles = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array('olimReglasOlimpiadasTipo'=> $objRegla['id']));
        $arrNiveles = array();
        foreach ($objNiveles as $value) {
            $arrNiveles[$value->getNivelTipo()->getId()] = $value->getNivelTipo()->getNivel();
        }
        ksort($arrNiveles);
        // dump($arrNiveles);die;
        $arrDataInscription['idregla'] = $objRegla['id'];
        $jsonDataInscription = json_encode($arrDataInscription);
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:doInscriptionOStudent.html.twig',array(
            'form' => $this->formInscriptionOlim($arrNiveles, $jsonDataInscription)->createView(),
        ));
    }

    private function formInscriptionOlim($arrNiveles, $jsonDataInscription){
        // dump($arrDataInscription);die;
        // $jsonRule = json_encode( array(
        //             'materia'  => $arrDataInscription['olimMateria'],
        //             'category' => $arrDataInscription['category'],
        //             'gestion'  => $arrDataInscription['gestion']
        //         ));
                // dump($jsonRule);die;
        return $this->createFormBuilder()
                
                ->add('nivel', 'choice', array('choices'=>$arrNiveles,'empty_value' => 'Seleccionar...', 'attr'=>array('class'=>'form-control')))
                ->add('grado', 'choice', array('attr'=>array('class'=>'form-control')))
                ->add('paralelo', 'choice', array('attr'=>array('class'=>'form-control')))
                ->add('turno', 'choice', array('attr'=>array('class'=>'form-control')))
                ->add('jsonRule', 'hidden', array('data'=>$jsonDataInscription))
                // ->add('msateria','text', array('data'=>$arrDataInscription['olimMateria']))
                // ->add('category','text', array('data'=>$arrDataInscription['category']))
                // ->add('gestion','text', array('data'=>$arrDataInscription['gestion']))
                ->getForm();
    }

    /**
     * [getGradoAllowedAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getGradoAllowedAction(Request $request){
        // create db conexion 
        $em = $this->getDoctrine()->getManager();

        // get the send values
        $jsonRule = $request->get('jsonRule');
        $idnivel = $request->get('idnivel');
        $arrDataInscription = json_decode($jsonRule,true);
        $arrDataInscription['idnivel'] = $idnivel;
        $objGrados = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
            'olimReglasOlimpiadasTipo' => $arrDataInscription['idregla'],
            'nivelTipo'                => $arrDataInscription['idnivel'],
        ));
        $arrGrados = array();
        foreach ($objGrados as $value) {
            $arrGrados[$value->getGradoTipo()->getId()] = $value->getGradoTipo()->getGrado();
        }
        $response = new JsonResponse();
        return $response->setData(array('agrados' => $arrGrados));
    }

    public function listInscriptionAction(Request $request){
        // get the send values
        $form = $request->get('form');
        
        $objTutorSelected = $this->get('olimfunctions')->getTutor2($form);
        
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:listInscription.html.twig', array(
            'form' => $this->selectInscriptionForm($form)->createView(),
            'tutor' => $objTutorSelected
        ));
    }

    public function changeTutorAction(Request $request){
        // get the send values
        $form = $request->get('form');
        
        $objTutorSelected = $this->get('olimfunctions')->getTutor2($form);
        
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:changeTutor.html.twig', array(
            'form' => $this->selectInscriptionForm($form)->createView(),
            'tutor' => $objTutorSelected
        ));
    }

    public function listTemplateInscriptionAction(Request $request){
        
        // get the send data
        $materiaId = $request->get('materiaId');
        $categoryId = $request->get('categoryId');
        
        //create db conexion 
        $em = $this->getDoctrine()->getManager();
        $objRuleSelected = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->findOneBy(array(
            'olimMateriaTipo'=> $materiaId,
            'id' => $categoryId
        ));
        $arrDataInscription = array(
            'materiaId' => $request->get('materiaId'),
            'categoryId' => $request->get('categoryId'),
            'institucioneducativaid' => $request->get('sie'),
            'gestiontipoid' => $request->get('gestion'),
            'olimtutorid' => $request->get('olimtutorId'),
        );
        // if the getModalidadParticipacionTipo == 2, then show the informacion about the group
        if($objRuleSelected->getModalidadParticipacionTipo()->getId()==2){
            return $this->redirectToRoute('olimgrupoproyecto_showGroup', array('datainscription'=>json_encode($arrDataInscription)));
        }else{
        //if not show the common inscription 
            return $this->redirectToRoute('oliminscriptions_listInscriptionByTutor', array('datainscription'=>json_encode($arrDataInscription)));
        }

        // dump($objRuleSelected->getModalidadParticipacionTipo()->getId());die;
    }

    public function changeTutorTemplateAction(Request $request){
        
        // get the send data
        $materiaId = $request->get('materiaId');
        $categoryId = $request->get('categoryId');
        
        //create db conexion 
        $em = $this->getDoctrine()->getManager();
        $objRuleSelected = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->findOneBy(array(
            'olimMateriaTipo'=> $materiaId,
            'id' => $categoryId
        ));
        $arrDataInscription = array(
            'materiaId' => $request->get('materiaId'),
            'categoryId' => $request->get('categoryId'),
            'institucioneducativaid' => $request->get('sie'),
            'gestiontipoid' => $request->get('gestion'),
            'olimtutorid' => $request->get('olimtutorId'),
        );
        // if the getModalidadParticipacionTipo == 2, then show the informacion about the group
        if($objRuleSelected->getModalidadParticipacionTipo()->getId()==2){
            return $this->redirectToRoute('olimgrupoproyecto_showGroup', array('datainscription'=>json_encode($arrDataInscription)));
        }else{
        //if not show the common inscription 
            return $this->redirectToRoute('oliminscriptions_listInscriptionByTutorChange', array('datainscription'=>json_encode($arrDataInscription)));
        }

        // dump($objRuleSelected->getModalidadParticipacionTipo()->getId());die;
    }

    public function listInscriptionByTutorChangeAction(Request $request){
        
        //create db conexion
        $em = $this->getDoctrine()->getManager();
        
        //get the send values
        $jsonDataInscription = $request->get('datainscription');
        $arrDataInscription = json_decode($jsonDataInscription,true);
        //find information about the group in case if exists
        $objGroup = array();
        $groupId = false;
        if(isset($arrDataInscription['groupId'])){
            $groupId = $arrDataInscription['groupId'];
            $objGroup = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($groupId);
        }
        //get the rule data
        $regla = $this->get('olimfunctions')->getDataRule($arrDataInscription);
        
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:listCommonInscriptionTutor.html.twig', array(
               'form' => $this->CommonInscriptionListForm($arrDataInscription)->createView(),
               'groupId' => $groupId,
               'objGroup' => $objGroup,
               'regla' => $regla,

        ));
    }

    public function listInscriptionByTutorAction(Request $request){
        
        //create db conexion
        $em = $this->getDoctrine()->getManager();
        
        //get the send values
        $jsonDataInscription = $request->get('datainscription');
        $arrDataInscription = json_decode($jsonDataInscription,true);
        //find information about the group in case if exists
        $objGroup = array();
        $groupId = false;
        if(isset($arrDataInscription['groupId'])){
            $groupId = $arrDataInscription['groupId'];
            $objGroup = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($groupId);
        }
        //get the rule data
        $regla = $this->get('olimfunctions')->getDataRule($arrDataInscription);
        
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:listCommonInscription.html.twig', array(
               'form' => $this->CommonInscriptionListForm($arrDataInscription)->createView(),
               'groupId' => $groupId,
               'objGroup' => $objGroup,
               'regla' => $regla,

        ));
    }

    private function CommonInscriptionListForm($data){
        // dump($data);die;
        // $arrAreas = $this->get('olimfunctions')->getAllowedAreasByOlim();
        // dump($arrAreas);die;
        $arrNiveles = $this->getNivelesByCategory($data['categoryId']);
        $newform = $this->createFormBuilder()
                
                ->add('jsonData', 'hidden', array('data'=>json_encode($data),))
                ->add('olimMateria', 'hidden', array('data'=>$data['materiaId'],))
                ->add('category', 'hidden', array('data'=>$data['categoryId'], ))
                ->add('nivel', 'choice', array('label'=>'Nivel','choices'=>$arrNiveles,  'empty_value' => 'Seleccionar Nivel'))
                // ->add('nivel', 'choice', array('label'=>'Nivel', ))
                ->add('grado', 'choice', array('label'=>'Grado', ))
                // ->add('paralelo', 'choice', array('label'=>'Paralelo', ))
                // ->add('turno', 'choice', array('label'=>'Turno', ))
                ->add('olimtutorid', 'hidden', array('data'=>$data['olimtutorid']))
                ->add('gestion', 'hidden', array('mapped' => false, 'label' => 'Gestion', 'attr' => array('class' => 'form-control', 'value'=>$data['gestiontipoid'])))
                // ->add('buscar', 'button', array('label'=>'Cancelar', 'attr'=>array('onclick'=>'openInscriptinoOlimpiadas();'), )) 
                ;
        
            $newform = $newform
                ->add('institucionEducativa', 'hidden', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control', 'value'=>$data['institucioneducativaid'])))
                ;
        

        $newform = $newform->getForm();
        return $newform;

    }

    public function getListStudentsAction(Request $request){
        
        //get the send values
        $gradoId = $request->get('gradoId');
        $materiaId = $request->get('materiaId');
        $categoryId = $request->get('categoryId');
        $nivelId = $request->get('nivelId');
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $olimtutorid = $request->get('olimtutorid');        
        $jsonData = $request->get('jsonData');

        //create db conexion
        $em = $this->getDoctrine()->getManager();        
        /*$entity = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion');
        $query = $entity->createQueryBuilder('oei')
            ->select('oei.id olimestudianteid, oei.telefonoEstudiante, oei.correoEstudiante, e.codigoRude, e.carnetIdentidad, e.paterno, e.materno, e.nombre, odt.discapacidad, pt.paralelo')
            ->innerjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = oei.estudianteInscripcion')
            ->innerjoin('SieAppWebBundle:institucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
            ->innerjoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'e.id = ei.estudiante')
            ->leftjoin('SieAppWebBundle:OlimDiscapacidadTipo', 'odt', 'WITH', 'odt.id = oei.discapacidadTipo')
            ->innerjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'pt.id = iec.paraleloTipo')
            ->where('oei.olimTutor = :olimTutor')
            ->andWhere('oei.materiaTipo = :materiaTipo')
            ->andWhere('iec.nivelTipo = :nivelTipo')
            ->andWhere('iec.gradoTipo = :gradoTipo')
            ->setParameter('olimTutor', $olimtutorid)
            ->setParameter('materiaTipo', $materiaId)
            ->setParameter('nivelTipo', $nivelId)
            ->setParameter('gradoTipo', $gradoId)
            ->addOrderBy('e.paterno, e.materno, e.nombre')
            ->getQuery();

        $inscritos = $query->getResult();*/

        $query = $em->getConnection()->prepare("
            (select oei.id olimestudianteid, oei.telefono_estudiante, oei.correo_estudiante, e.codigo_rude, e.carnet_identidad, e.paterno, e.materno, e.nombre, odt.discapacidad, 'Grado regular' obs
            from olim_estudiante_inscripcion oei
            inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
            inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
            inner join estudiante e on e.id = ei.estudiante_id
            inner join olim_discapacidad_tipo odt on odt.id = oei.discapacidad_tipo_id
            where oei.olim_tutor_id = :olimTutor and
            oei.materia_tipo_id = :materiaTipo and
            iec.nivel_tipo_id = :nivelTipo and
            iec.grado_tipo_id = :gradoTipo)
            union all
            (select oei.id olimestudianteid, oei.telefono_estudiante, oei.correo_estudiante, e.codigo_rude, e.carnet_identidad, e.paterno, e.materno, e.nombre, odt.discapacidad, 'Grado superior' obs
            from olim_estudiante_inscripcion oei
            inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
            inner join olim_estudiante_inscripcion_curso_superior oeis on oei.id = oeis.olim_estudiante_inscripcion_id
            inner join estudiante e on e.id = ei.estudiante_id
            inner join olim_discapacidad_tipo odt on odt.id = oei.discapacidad_tipo_id
            where oeis.nivel_tipo_id = :nivelTipo and
            oeis.grado_tipo_id = :gradoTipo)
        ");
        $query->bindValue(':olimTutor', $olimtutorid);
        $query->bindValue(':materiaTipo', $materiaId);
        $query->bindValue(':nivelTipo', $nivelId);
        $query->bindValue(':gradoTipo', $gradoId);
        $query->execute();
        $inscritos = $query->fetchAll();
        
        
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:listCommonInscritos.html.twig', array(
            'inscritos' => $inscritos,
            'categoryId' => $categoryId,
            'materiaId'=> $materiaId,
            'nivelId' => $nivelId,
            'gradoId' => $gradoId,
            'olimtutorid' => $olimtutorid
        ));
    }

    public function getListStudentsTutorAction(Request $request){
        
        //get the send values
        $gradoId = $request->get('gradoId');
        $materiaId = $request->get('materiaId');
        $categoryId = $request->get('categoryId');
        $nivelId = $request->get('nivelId');
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $olimtutorid = $request->get('olimtutorid');        
        $jsonData = $request->get('jsonData');

        //create db conexion
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion');
        $query = $entity->createQueryBuilder('oei')
            ->select('oei.id olimestudianteid, oei.telefonoEstudiante, oei.correoEstudiante, e.codigoRude, e.carnetIdentidad, e.paterno, e.materno, e.nombre, odt.discapacidad, pt.paralelo')
            ->innerjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = oei.estudianteInscripcion')
            ->innerjoin('SieAppWebBundle:institucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
            ->innerjoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'e.id = ei.estudiante')
            ->leftjoin('SieAppWebBundle:OlimDiscapacidadTipo', 'odt', 'WITH', 'odt.id = oei.discapacidadTipo')
            ->innerjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'pt.id = iec.paraleloTipo')
            ->innerjoin('SieAppWebBundle:OlimTutor', 'ot', 'WITH', 'ot.id = oei.olimTutor')
            ->where('oei.olimTutor = :olimTutor')
            ->andWhere('oei.materiaTipo = :materiaTipo')
            ->andWhere('iec.nivelTipo = :nivelTipo')
            ->andWhere('iec.gradoTipo = :gradoTipo')
            ->setParameter('olimTutor', $olimtutorid)
            ->setParameter('materiaTipo', $materiaId)
            ->setParameter('nivelTipo', $nivelId)
            ->setParameter('gradoTipo', $gradoId)
            ->addOrderBy('e.paterno, e.materno, e.nombre')
            ->getQuery();
        $inscritos = $query->getResult();

        $discapacidad = $em->getRepository('SieAppWebBundle:OlimDiscapacidadTipo')->findAll();
        $discapacidadArray = array();

        foreach($discapacidad as $value){
            $discapacidadArray[$value->getId()] = $value->getDiscapacidad();
        }
        
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:listCommonInscritosTutor.html.twig', array(
            'inscritos' => $inscritos,
            'categoryId' => $categoryId,
            'materiaId'=> $materiaId,
            'nivelId' => $nivelId,
            'gradoId' => $gradoId,
            'olimtutorid' => $olimtutorid,
            'discapacidadArray' => $discapacidadArray
        ));
    }

    public function deleteInscriptionStudentAction(Request $request){
        //create db conexion
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        //get the send values
        $gradoId = $form['gradoId'];
        $materiaId = $form['materiaId'];
        $categoryId = $form['categoryId'];
        $nivelId = $form['nivelId'];
        $olimtutorid = $form['olimtutorid'];
        $olimestudianteid = $form['olimestudianteid'];

        $olimEstudianteInscripcion = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->findOneBy(array(
            'id' => $olimestudianteid
        ));

        if($olimEstudianteInscripcion){
            $em->getConnection()->beginTransaction();
            try {
                $olimEstudianteInscripcionSuperior = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcionCursoSuperior')->findBy(array(
                    'olimEstudianteInscripcion' => $olimEstudianteInscripcion
                ));
                if($olimEstudianteInscripcionSuperior){
                    foreach($olimEstudianteInscripcionSuperior as $value){
                        $em->remove($value);
                        $em->flush();
                    }
                }
                
                $olimInscripcionGrupoProyecto = $em->getRepository('SieAppWebBundle:OlimInscripcionGrupoProyecto')->findBy(array(
                    'olimEstudianteInscripcion' => $olimEstudianteInscripcion
                ));
                if($olimInscripcionGrupoProyecto){
                    foreach($olimInscripcionGrupoProyecto as $value){
                        $em->remove($value);
                        $em->flush();
                    }
                }
                $em->remove($olimEstudianteInscripcion);
                $em->flush();
                $em->getConnection()->commit();
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
            }
        }
        
        /*$entity = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion');
        $query = $entity->createQueryBuilder('oei')
            ->select('oei.id olimestudianteid, oei.telefonoEstudiante, oei.correoEstudiante, e.codigoRude, e.carnetIdentidad, e.paterno, e.materno, e.nombre, odt.discapacidad, pt.paralelo')
            ->innerjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = oei.estudianteInscripcion')
            ->innerjoin('SieAppWebBundle:institucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
            ->innerjoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'e.id = ei.estudiante')
            ->leftjoin('SieAppWebBundle:OlimDiscapacidadTipo', 'odt', 'WITH', 'odt.id = oei.discapacidadTipo')
            ->innerjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'pt.id = iec.paraleloTipo')
            ->where('oei.olimTutor = :olimTutor')
            ->andWhere('oei.materiaTipo = :materiaTipo')
            ->andWhere('iec.nivelTipo = :nivelTipo')
            ->andWhere('iec.gradoTipo = :gradoTipo')
            ->setParameter('olimTutor', $olimtutorid)
            ->setParameter('materiaTipo', $materiaId)
            ->setParameter('nivelTipo', $nivelId)
            ->setParameter('gradoTipo', $gradoId)
            ->addOrderBy('e.paterno, e.materno, e.nombre')
            ->getQuery();
        $inscritos = $query->getResult();*/

        $query = $em->getConnection()->prepare("
            (select oei.id olimestudianteid, oei.telefono_estudiante, oei.correo_estudiante, e.codigo_rude, e.carnet_identidad, e.paterno, e.materno, e.nombre, odt.discapacidad, 'Grado regular' obs
            from olim_estudiante_inscripcion oei
            inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
            inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
            inner join estudiante e on e.id = ei.estudiante_id
            inner join olim_discapacidad_tipo odt on odt.id = oei.discapacidad_tipo_id
            where oei.olim_tutor_id = :olimTutor and
            oei.materia_tipo_id = :materiaTipo and
            iec.nivel_tipo_id = :nivelTipo and
            iec.grado_tipo_id = :gradoTipo)
            union all
            (select oei.id olimestudianteid, oei.telefono_estudiante, oei.correo_estudiante, e.codigo_rude, e.carnet_identidad, e.paterno, e.materno, e.nombre, odt.discapacidad, 'Grado superior' obs
            from olim_estudiante_inscripcion oei
            inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
            inner join olim_estudiante_inscripcion_curso_superior oeis on oei.id = oeis.olim_estudiante_inscripcion_id
            inner join estudiante e on e.id = ei.estudiante_id
            inner join olim_discapacidad_tipo odt on odt.id = oei.discapacidad_tipo_id
            where oeis.nivel_tipo_id = :nivelTipo and
            oeis.grado_tipo_id = :gradoTipo)
        ");
        $query->bindValue(':olimTutor', $olimtutorid);
        $query->bindValue(':materiaTipo', $materiaId);
        $query->bindValue(':nivelTipo', $nivelId);
        $query->bindValue(':gradoTipo', $gradoId);
        $query->execute();
        $inscritos = $query->fetchAll();
        
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:listCommonInscritos.html.twig', array(
            'inscritos' => $inscritos,
            'categoryId' => $categoryId,
            'materiaId'=> $materiaId,
            'nivelId' => $nivelId,
            'gradoId' => $gradoId,
            'olimtutorid' => $olimtutorid
        ));
    }

    public function selectInscriptionAction(Request $request){
        // get the send values
        $form = $request->get('form');
        // dump($form);die;
        $objTutorSelected = $this->get('olimfunctions')->getTutor2($form);
        // dump($objTutorSelected);die;
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:selectInscription.html.twig', array(
            'form' => $this->selectInscriptionForm($form)->createView(),
            'tutor' => $objTutorSelected

        ));
    }



    private function selectInscriptionForm($data){
        // dump($data);die;
        $arrAreas = $this->get('olimfunctions')->getAllowedAreasByOlim();
        // dump($arrAreas);die;
        $newform = $this->createFormBuilder()
                ->add('olimMateria', 'choice', array('label'=>'materias','choices'=>$arrAreas,  'empty_value' => 'Seleccionar Materia'))
                ->add('category', 'choice', array('label'=>'categoria', ))
                // ->add('nivel', 'choice', array('label'=>'Nivel', ))
                // ->add('grado', 'choice', array('label'=>'Grado', ))
                // ->add('paralelo', 'choice', array('label'=>'Paralelo', ))
                // ->add('turno', 'choice', array('label'=>'Turno', ))
                ->add('olimtutorid', 'hidden', array('data'=>$data['olimtutorid']))
                ->add('gestion', 'hidden', array('mapped' => false, 'label' => 'Gestion', 'attr' => array('class' => 'form-control', 'value'=>$data['gestiontipoid'])))
                // ->add('buscar', 'button', array('label'=>'Cancelar', 'attr'=>array('onclick'=>'openInscriptinoOlimpiadas();'), )) 
                ;
        // if($this->session->get('roluser')==8){
            $newform = $newform
                ->add('institucionEducativa', 'hidden', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control', 'value'=>$data['institucioneducativaid'])))
                ;
        // }

        $newform = $newform->getForm();
        return $newform;

    }

    public function selectTemplateInscriptionAction(Request $request){
        // dump($request);die;
        // get the send data
        $materiaId = $request->get('materiaId');
        $categoryId = $request->get('categoryId');
        
        //create db conexion 
        $em = $this->getDoctrine()->getManager();
        $objRuleSelected = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->findOneBy(array(
            'olimMateriaTipo'=> $materiaId,
            'id' => $categoryId
        ));
        $arrDataInscription = array(
            'materiaId' => $request->get('materiaId'),
            'categoryId' => $request->get('categoryId'),
            'institucioneducativaid' => $request->get('sie'),
            'gestiontipoid' => $request->get('gestion'),
            'olimtutorid' => $request->get('olimtutorId'),
        );
        // if the getModalidadParticipacionTipo == 2, then show the informacion about the group
        if($objRuleSelected->getModalidadParticipacionTipo()->getId()==2){
            return $this->redirectToRoute('olimgrupoproyecto_showGroup', array('datainscription'=>json_encode($arrDataInscription)));
        }else{
        //if not show the common inscription 
            return $this->redirectToRoute('oliminscriptions_showOptionDoInscription', array('datainscription'=>json_encode($arrDataInscription)));
        }

        // dump($objRuleSelected->getModalidadParticipacionTipo()->getId());die;
    }

    public function showOptionDoInscriptionAction(Request $request){
        //create db conexion
        $em = $this->getDoctrine()->getManager();
        
        //get the send values
        $jsonDataInscription = $request->get('datainscription');
        $arrDataInscription = json_decode($jsonDataInscription,true);
        //find information about the group in case if exists
        $objGroup = array();
        $groupId = false;
        if(isset($arrDataInscription['groupId'])){
            $groupId = $arrDataInscription['groupId'];
            $objGroup = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($groupId);
        }
        //get the rule data
        $regla = $this->get('olimfunctions')->getDataRule($arrDataInscription);
        
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:commonInscription.html.twig', array(
               'form' => $this->CommonInscriptionForm($arrDataInscription)->createView(),
               'groupId' => $groupId,
               'objGroup' => $objGroup,
               'regla' => $regla,

        ));
    }


    private function CommonInscriptionForm($data){
        // dump($data);die;
        // $arrAreas = $this->get('olimfunctions')->getAllowedAreasByOlim();
        // dump($arrAreas);die;
        $arrNiveles = $this->getNivelesByCategory($data['categoryId']);
        $newform = $this->createFormBuilder()
                
                ->add('jsonData', 'hidden', array('data'=>json_encode($data),))
                ->add('olimMateria', 'hidden', array('data'=>$data['materiaId'],))
                ->add('category', 'hidden', array('data'=>$data['categoryId'], ))
                ->add('nivel', 'choice', array('label'=>'Nivel','choices'=>$arrNiveles,  'empty_value' => 'Seleccionar Materia'))
                // ->add('nivel', 'choice', array('label'=>'Nivel', ))
                ->add('grado', 'choice', array('label'=>'Grado', ))
                ->add('paralelo', 'choice', array('label'=>'Paralelo', ))
                ->add('turno', 'choice', array('label'=>'Turno', ))
                ->add('olimtutorid', 'hidden', array('data'=>$data['olimtutorid']))
                ->add('gestion', 'hidden', array('mapped' => false, 'label' => 'Gestion', 'attr' => array('class' => 'form-control', 'value'=>$data['gestiontipoid'])))
                // ->add('buscar', 'button', array('label'=>'Cancelar', 'attr'=>array('onclick'=>'openInscriptinoOlimpiadas();'), )) 
                ;
        
            $newform = $newform
                ->add('institucionEducativa', 'hidden', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control', 'value'=>$data['institucioneducativaid'])))
                ;
        

        $newform = $newform->getForm();
        return $newform;

    }

    private function getNivelesByCategory($categoryId){
        //create db conexion
        $em = $this->getDoctrine()->getManager();
        //get the levels        
         $objNiveles = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
            'olimReglasOlimpiadasTipo' => $categoryId,
        ));
         // dump($objNiveles);die;
        $arrNiveles = array();
        foreach ($objNiveles as $value) {    
            $arrNiveles[$value->getNivelTipo()->getId()] = $value->getNivelTipo()->getNivel();
        }
        ksort($arrNiveles);
        return $arrNiveles;
    }


    public function getNivelesAction(Request $request){
        //create db conexion
        $em = $this->getDoctrine()->getManager();
        //get the send values
        $materiaId = $request->get('materiaId');
        $categoryId = $request->get('categoryId');
        
        // dump($categoryId);

         $objNiveles = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
            'olimReglasOlimpiadasTipo' => $categoryId,
        ));
         dump($objNiveles);die;
        $arrNiveles = array();
        foreach ($objNiveles as $value) {    
            $arrNiveles[$value->getNivelTipo()->getId()] = $value->getNivelTipo()->getNivel();
        }
        
        ksort($arrNiveles);
        // dump($arrNiveles);die;
        $response = new JsonResponse();
        return $response->setData(array('arrNiveles' => $arrNiveles));
        dump($nivelId);die;
    }

  public function getGradosAction(Request $request){
    // dump($request);die;
        //create db conexion
        $em = $this->getDoctrine()->getManager();
        //get the send values
        $materiaId = $request->get('materiaId');
        $categoryId = $request->get('categoryId');
        $nivelId = $request->get('nivelId');

         $objNiveles = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
            'olimReglasOlimpiadasTipo' => $categoryId,
            'nivelTipo' => $nivelId,
        ));
         
        $arrGrados = array();
        foreach ($objNiveles as $value) {    
            $arrGrados[$value->getGradoTipo()->getId()] = $value->getGradoTipo()->getGrado();
        }
        
        ksort($arrGrados);
        // dump($arrGrados);die;
        $response = new JsonResponse();
        return $response->setData(array('arrGrados' => $arrGrados));
        dump($nivelId);die;
    }

      /**
     * [getParaleloAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getParaleloAction(Request $request) {
        // dump($request);
        // die;
        //get the send values        
        $gradoId = $request->get('gradoId');
        $materiaId = $request->get('materiaId');
        $categoryId = $request->get('categoryId');
        $nivelId = $request->get('nivelId');
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');;
        /*dump($sie);
        dump($nivelId);
        dump($gradoId);
        dump($gestion);*/
        $em = $this->getDoctrine()->getManager();
        //get grado
        $aparalelos = array();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.paraleloTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivelId)
                ->setParameter('grado', $gradoId)
                ->setParameter('gestion', $gestion)
                ->distinct()
                ->orderBy('iec.paraleloTipo', 'ASC')
                ->getQuery();
        $aParalelos = $query->getResult();

        foreach ($aParalelos as $paralelo) {
            $aparalelos[$paralelo[1]] = $em->getRepository('SieAppWebBundle:ParaleloTipo')->find($paralelo[1])->getParalelo();
        }

        $response = new JsonResponse();
        return $response->setData(array('aparalelos' => $aparalelos));
    }


    /**
     * [findturnoAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getTurnoAction(Request $request) {
        
        //get the send values     
        $paraleloId = $request->get('paraleloId');
        $gradoId = $request->get('gradoId');
        $materiaId = $request->get('materiaId');
        $categoryId = $request->get('categoryId');
        $nivelId = $request->get('nivelId');
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');;
        //create db conexion
        $em = $this->getDoctrine()->getManager();
        // get turnos
        $aturnos = array();

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.turnoTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andWhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivelId)
                ->setParameter('grado', $gradoId)
                ->setParameter('paralelo', $paraleloId)
                ->setParameter('gestion', $gestion)
                ->distinct()
                ->orderBy('iec.turnoTipo', 'ASC')
                ->getQuery();
        $aTurnos = $query->getResult();
        foreach ($aTurnos as $turno) {
            $aturnos[$turno[1]] = $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno[1])->getTurno();
        }

        $response = new JsonResponse();
        return $response->setData(array('aturnos' => $aturnos));
    }


    public function getStudentsAction(Request $request){
        // dump($request);die;
        //create db conexino
        $em = $this->getDoctrine()->getManager();
         //get the send values
        $turnoId = $request->get('turnoId');
        $paraleloId = $request->get('paraleloId');
        $gradoId = $request->get('gradoId');
        $materiaId = $request->get('materiaId');
        $categoryId = $request->get('categoryId');
        $nivelId = $request->get('nivelId');
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $olimtutorid = $request->get('olimtutorid');
        
        $jsonData = $request->get('jsonData');
        // dump($jsonData);die;

        //look for the id of institucioneducativa_curso
        $objInstitucionEducativaCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy( array(
             'nivelTipo' =>$nivelId,
             'gradoTipo' => $gradoId,
             'paraleloTipo'=> $paraleloId,
             'turnoTipo' => $turnoId, 
             'institucioneducativa' => $sie,
             'gestionTipo' => $gestion
        ));
        
        $objStudentsToOlimpiadas = $this->get('olimfunctions')->getStudentsToOlimpiadas($objInstitucionEducativaCurso->getId());
        // $objStudentsInOlimpiadas = $this->get('olimfunctions')->getStudentsInOlimpiadas($materiaId, $categoryId, $gestion);
        
        $objRules = $this->get('olimfunctions')->getDataRule(array('materiaId'=>$materiaId, 'categoryId'=>$categoryId));
        
        $fechaComparacion = $objRules->getFechaComparacion()->format('d-m-Y');
        $edadInicial = $objRules->getEdadInicial();
        $edadFinal = $objRules->getEdadFinal();

        $arrCorrectStudent = array();
        foreach ($objStudentsToOlimpiadas as $key => $value) {
            
            $newStudentDate = date('d-m-Y', strtotime($value['fecha_nacimiento']) );
            $value['fecha_nacimiento'] = $newStudentDate;
            $studentYearsOld = $this->get('olimfunctions')->getYearsOldsStudent($newStudentDate, $fechaComparacion);
            $value['yearsOld'] = $studentYearsOld[0];
            $yearOldStudent = $value['yearsOld'];
            //get students observation
            $studentObs = $this->get('seguimiento')->getStudentObservationQA(array('codigoRude'=>$value['codigo_rude'], 'gestion'=>$gestion));
            //get students registered
            $objStudentsInOlimpiadas = $this->get('olimfunctions')->getStudentsInOlimpiadas($materiaId, $categoryId, $gestion, $value['estinsid']);
            // dump($objStudentsInOlimpiadas);
            if($objStudentsInOlimpiadas || sizeof($studentObs)>0){
            }else{
                if(  $yearOldStudent >= $edadInicial && $yearOldStudent <= $edadFinal){
                $arrCorrectStudent[]=($value);    
                }
            }
            // dump($studentObs);

        }
        // die;
        //get the discapacidad
        $objDiscapacidad = $em->getRepository('SieAppWebBundle:DiscapacidadTipo')->findAll();
        
        // get the data to do the inscription 
        $jsonDataInscription = json_encode( array(

                'turnoId' => $request->get('turnoId'),
                'paraleloId' => $request->get('paraleloId'),
                'gradoId' => $request->get('gradoId'),
                'materiaId' => $request->get('materiaId'),
                'categoryId' => $request->get('categoryId'),
                'nivelId' => $request->get('nivelId'),
                'sie' => $request->get('sie'),
                'gestion' => $request->get('gestion'),
                'olimtutorid' => $request->get('olimtutorid'),
                'institucionEducativaCursoId' => $objInstitucionEducativaCurso->getId(),
        ));
        // dump($jsonDataInscription);die;
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:getStudents.html.twig', array(
            'objStudentsToOlimpiadas' => $arrCorrectStudent,
            'form' => $this->studentsRegisterform($jsonDataInscription, $jsonData)->createView(),
            'objDiscapacidad' => $objDiscapacidad,

        ));
    }

    private function studentsRegisterform($jsonDataInscription, $jsonData){
        $arrData = json_decode($jsonData,true);
        // dump($arrData);
        // dump($arrData['groupId']);
        // die;

        $form = $this->createFormBuilder()
                ->add('jsonDataInscription','hidden', array('data'=>$jsonDataInscription))
                ->add('jsonData','hidden', array('data'=>$jsonData));
        
        if(isset($arrData['groupId'])){
            $form = $form ->add('register', 'button', array('label'=>'Registrar', 'attr'=>array('class'=>'btn btn-success btn-xs', 'onclick'=>'studentsRegisterGroup()')));
        }else{
            $form = $form ->add('register', 'button', array('label'=>'Registrar', 'attr'=>array('class'=>'btn btn-success btn-xs', 'onclick'=>'studentsRegister()')));
        }

        
        $form = $form  ->getForm()
                ;


        return $form;

    }

    public function studentsRegisterAction(Request $request){
        
        // create db conexioon
        $em =  $this->getDoctrine()->getManager();
        //get the send values
        $form = $request->get('form');
        // dump($form);die;
        $jsonDataInscription = $form['jsonDataInscription'];
        $arrDataInscription = json_decode($jsonDataInscription, true);
    
        // remove the las elemento of form array
        array_pop($form);
        //count the send elements
        //if everything ok on the rule do the save
        
        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_estudiante_inscripcion');");
        $query->execute();
        reset ($form);
        while($val = current($form)){
            if(isset($val['studentInscription'])){
                // dump($val);   
                $objOLimStudentInscription = new OlimEstudianteInscripcion();
                $objOLimStudentInscription->setTelefonoEstudiante($val['fono']);
                $objOLimStudentInscription->setCorreoEstudiante($val['email']);
                $objOLimStudentInscription->setFechaRegistro(new \DateTime('now'));
                $objOLimStudentInscription->setUsuarioRegistroId($this->session->get('userId'));
                $objOLimStudentInscription->setOlimReglasOlimpiadasTipo($em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->find($arrDataInscription['categoryId']) );
                // $objOLimStudentInscription->setCategoriaTipo($em->getRepository('SieAppWebBundle:OlimCategoriaTipo')->find($val['fono']));
                $objOLimStudentInscription->setMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($arrDataInscription['materiaId']));
                $objOLimStudentInscription->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:OlimDiscapacidadTipo')->find($val['discapacidad']));
                $objOLimStudentInscription->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($val['studentInscription']));
                $objOLimStudentInscription->setOlimTutor($em->getRepository('SieAppWebBundle:OlimTutor')->find($arrDataInscription['olimtutorid']));
                $objOLimStudentInscription->setGestionTipoId($arrDataInscription['gestion']);
                
                $em->persist($objOLimStudentInscription);
                $em->flush();
            }
            next($form);
        }

        // dump($form);

        // dump(sizeof($form));die;
        echo('inscription DONE!!!!');die;
    }

    public function studentsRegisterGroupAction(Request $request){
          // create db conexioon
        $em =  $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //get the send values
        $form = $request->get('form');
        // dump($form);die;
        $jsonDataInscription = $form['jsonDataInscription'];
        $arrDataInscription = json_decode($jsonDataInscription, true);

        $jsonData = $form['jsonData'];
        $arrData = json_decode($jsonData, true);    
        // dump($arrData);
        // die;
        // remove the las elemento of form array
        array_pop($form);
        //count the send elements
        //if everything ok on the rule do the save
        
        try {

            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_estudiante_inscripcion');");
            $query->execute();
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_inscripcion_grupo_proyecto');");
            $query->execute();
            reset ($form);
            while($val = current($form)){
                if(isset($val['studentInscription'])){
                    // dump($val);   
                    $objOLimStudentInscription = new OlimEstudianteInscripcion();
                    $objOLimStudentInscription->setTelefonoEstudiante($val['fono']);
                    $objOLimStudentInscription->setCorreoEstudiante($val['email']);
                    $objOLimStudentInscription->setFechaRegistro(new \DateTime('now'));
                    $objOLimStudentInscription->setUsuarioRegistroId($this->session->get('userId'));
                    $objOLimStudentInscription->setOlimReglasOlimpiadasTipo($em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->find($arrDataInscription['categoryId']) );
                    // $objOLimStudentInscription->setCategoriaTipo($em->getRepository('SieAppWebBundle:OlimCategoriaTipo')->find($val['fono']));
                    $objOLimStudentInscription->setMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($arrDataInscription['materiaId']));
                    $objOLimStudentInscription->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:OlimDiscapacidadTipo')->find($val['discapacidad']));
                    $objOLimStudentInscription->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($val['studentInscription']));
                    $objOLimStudentInscription->setOlimTutor($em->getRepository('SieAppWebBundle:OlimTutor')->find($arrDataInscription['olimtutorid']));
                    $objOLimStudentInscription->setGestionTipoId($arrDataInscription['gestion']);
                    
                    $em->persist($objOLimStudentInscription);
                    $em->flush();

                    //save in group info
                    $objGroup = new OlimInscripcionGrupoProyecto();
                    $objGroup->setFechaRegistro(new \DateTime('now'));
                    $objGroup->setOlimGrupoProyecto($em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($arrData['groupId']));
                    $objGroup->setOlimEstudianteInscripcion($em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($objOLimStudentInscription->getId()));

                    $em->persist($objGroup);
                    $em->flush();

                }
                next($form);
            }

            $em->getConnection()->commit();

            // dump($form);

            // dump(sizeof($form));die;
            echo('inscription DONE!!!!');die;
                        
        } catch (Exception $e) {
             $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";                
        }            

    }

    public function doInscriptionGroupAction(Request $request){
        
        //get the send values
        $jsonDataInscription = $request->get('jsonDataInscription');
        $arrDataInscription = json_decode($jsonDataInscription,true);

        $arrDataInscription['groupId'] = $request->get('groupId');
        
        return $this->redirectToRoute('oliminscriptions_showOptionDoInscription', array('datainscription'=>json_encode($arrDataInscription)));
        
    }

    // public function createGroupAction(Request $request){

    //     return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:createGroup.html.twig', array(
    //     ));
    // }
    public function listInscritosGroupAction(Request $request){
        //get the send values 
        $groupId = $request->get('groupId');
        $roboticaOption = $request->get('robotica');
        $jsonDataInscription = $request->get('jsonDataInscription');
        $arrData = json_decode($jsonDataInscription, true);
        $arrData['groupId']= $groupId;
        $arrData['roboticaOption']= $roboticaOption;

        $jsonDataInscription = json_encode($arrData);

        // dump($jsonDataInscription);die;
        //create the db conexion
        return $this->redirectToRoute('oliminscriptions_viewListInscritosGroup', array('jsonDataInscription'=>$jsonDataInscription));
      
    }

    public function viewListInscritosGroupAction(Request $request){
        
        //get the send values
        $jsonDataInscription = $request->get('jsonDataInscription');
        $arrData = json_decode($jsonDataInscription, true);
        
        $groupId = $arrData['groupId'];
        $roboticaOption = $arrData['roboticaOption'];

        $em = $this->getDoctrine()->getManager();
        //get the studens
        $objStudentsInGroup = $this->get('olimfunctions')->getStudentsInGroup($groupId);
        // dump($objStudentsInGroup);die;
        //get the group info
        $objGroup = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($groupId);
        //get the rule data
        $regla = $this->get('olimfunctions')->getDataRule($arrData);

        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:listInscritosGroup.html.twig', array(
            'objStudentsInGroup' => $objStudentsInGroup,
            'roboticaOption'     => $roboticaOption,
            'objGroup'           => $objGroup,
            'jsonDataInscription'=> $jsonDataInscription,
            'roboticaOptionForm' => $this->roboticaOptionForm($jsonDataInscription)->createView(),
            'regla' => $regla,
        ));
    }

    private function roboticaOptionForm($data){

        return $this->createFormBuilder()
                ->add('jsonDataInscription', 'hidden', array('data'=> $data))
                ->getForm()
                ;
    }

    public function inscriptionExternaAction(Request $request){
        
        //get the send values
        $form = $request->get('form');
        $jsonDataInscription = $form['jsonDataInscription'];
        
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:inscriptionExterna.html.twig', array(
            'form'=>$this->inscriptionRoboticaForm($jsonDataInscription)->createView()
        ));
    }

    private function inscriptionRoboticaForm($jsonDataInscription){
        return $this->createFormBuilder()
                ->add('jsonDataInscription','hidden',array('data'=>$jsonDataInscription))
                ->add('codigoRude', 'text', array('label'=>'Codigo Rude'))
                ->getForm()
                ;
    }


    public function deleteInscriptionOlimpiadaAction(Request $request){
        //get the send values 
        $olimEstudianteInscripcionId = $request->get('olimEstudianteInscripcionId');
        $jsonDataInscription = $request->get('jsonDataInscription');
        $arrData = json_decode($jsonDataInscription,true);
        $arrData['olimEstudianteInscripcionId'] = $olimEstudianteInscripcionId;
        $jsonDataInscription = json_encode($arrData);
        $olimEstudianteInscripcionId = $request->get('olimEstudianteInscripcionId');
        try {
            // $em = $this->getDoctrine()->getManager();
            // $em->getConnection()->beginTransaction();
            //get rule inscription
            $objRuleInscription = $this->get('olimfunctions')->getDataRule($arrData);
            switch ($objRuleInscription->getModalidadNumeroIntegrantesTipo()->getId()) {
                case '1':
                    # individual
                    
                    break;
                case '2':
                    # hasta
                    # delete the students inscription
                    $this->deleteStudentInscription($jsonDataInscription);
                    
                    break;
                case '3':
                    # igual a
                    # delete all students and group
                    $this->deleteAllStudentInscription($jsonDataInscription);
                    // dump($objRuleInscription->getModalidadNumeroIntegrantesTipo()->getId());die;
                    break;
                
                default:
                    # code...
                    break;
            }

            // $objGrupoInfo = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')

            // $em->getConnection()->commit();
            return $this->redirectToRoute('oliminscriptions_viewListInscritosGroup', array('jsonDataInscription'=>$jsonDataInscription));
        } catch (Exception $e) {
            // $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
        /*$arrData = json_decode($jsonDataInscription, true);
        $arrData['groupId']= $groupId;
        $arrData['roboticaOption']= $roboticaOption;
        $jsonDataInscription = json_encode($arrData);*/
        // dump($jsonDataInscription);die;
        //create the db conexion
 
    }
    private function deleteStudentInscription($jsonDataInscription){

        // dump($jsonDataInscription);
        $arrDataInscription = json_decode($jsonDataInscription,true);
        // dump($arrDataInscription);
        //create db conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            // remove the inscription into the group
            $objInscriptionGrupoProyecto = $em->getRepository('SieAppWebBundle:OlimInscripcionGrupoProyecto')->findOneBy(array(
                'olimEstudianteInscripcion' => $arrDataInscription['olimEstudianteInscripcionId'],
                'olimGrupoProyecto' => $arrDataInscription['groupId'],
            ));
            $em->remove($objInscriptionGrupoProyecto);     
            //remove the inscription
            $objStudentInscription = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($arrDataInscription['olimEstudianteInscripcionId']);
            $em->remove($objStudentInscription);
            $em->flush();

            $em->getConnection()->commit();

            // dump($objStudentInscription);die;
            // dump($objInscriptionGrupoProyecto);
            // die;
        return 1; 
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
        
    }

    private function deleteAllStudentInscription($jsonDataInscription){

        // dump($jsonDataInscription);
        $arrDataInscription = json_decode($jsonDataInscription,true);
        // dump($arrDataInscription);die;
        //create db conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            // remove the inscription into the group
            $objInscriptionGrupoProyecto = $em->getRepository('SieAppWebBundle:OlimInscripcionGrupoProyecto')->findBy(array(
                'olimGrupoProyecto' => $arrDataInscription['groupId'],
            ));
            foreach ($objInscriptionGrupoProyecto as $value) {
                // dump($value->getOlimEstudianteInscripcion()->getId());
                
                $objStudentInscription = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($value->getOlimEstudianteInscripcion()->getId());
                $em->remove($objStudentInscription);

                $em->remove($value);     
                $em->flush();
            }

            $em->getConnection()->commit();

        return 1; 
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
        
    }

    public function lookForOlimStudentAction(Request $request){
        //get the send data
        $form = $request->get('form');
        $jsonDataInscription = $form['jsonDataInscription'];
        $arrDataInscription = json_decode($jsonDataInscription,true);
        $arrDataInscription['codigoRude'] = $form['codigoRude'];
        // dump($arrDataInscription);die;
        $objStudentInscription = $this->get('olimfunctions')->lookForOlimStudentByRudeGestion($arrDataInscription);
        $arrDataInscription['estinsid'] = $objStudentInscription['estinsid'];
        // dump($arrDataInscription);
        // dump($objStudentInscription);
        // die;
        $jsonDataInscription = json_encode($arrDataInscription);
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:lookForOlimStudent.html.twig', array(
            'inscripForm' => $this->doExternalInscriptionForm($jsonDataInscription)->createView(),
            'objStudentInscription' => $objStudentInscription,
        ));
        // die;
    }

    
    private function doExternalInscriptionForm($jsonDataInscription){
        return $this->createFormBuilder()
                ->add('fono', 'text')
                ->add('email', 'text')
                ->add('discapacidad', 'entity', array('class'=>'SieAppWebBundle:OlimDiscapacidadTipo', 'property'=>'discapacidad', 'empty_value'=>'Seleccionar...'))
                ->add('jsonDataInscription','text', array('data'=>$jsonDataInscription) )
                ->add('doInscription', 'button', array('label'=>'Inscribir','attr'=>array('class'=>'btn btn-warning btn-xs', 'onclick'=>'saveExternalInscription();')))
                ->getForm()
                ;

    }


    public function saveExternalInscriptionAction (Request $request){
        // create DB conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        // get the send valuess
        $form = $request->get('form');
        
        $jsonDataInscription = $form['jsonDataInscription'];
        $arrDataInscription = json_decode($jsonDataInscription, true);
        // dump($form);
        // dump($arrDataInscription);
        // die;
        try {

            $objOLimStudentInscription = new OlimEstudianteInscripcion();
            $objOLimStudentInscription->setTelefonoEstudiante($form['fono']);
            $objOLimStudentInscription->setCorreoEstudiante($form['email']);
            $objOLimStudentInscription->setFechaRegistro(new \DateTime('now'));
            $objOLimStudentInscription->setUsuarioRegistroId($this->session->get('userId'));
            $objOLimStudentInscription->setOlimReglasOlimpiadasTipo($em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->find($arrDataInscription['categoryId']) );
            // $objOLimStudentInscription->setCategoriaTipo($em->getRepository('SieAppWebBundle:OlimCategoriaTipo')->find($val['fono']));
            $objOLimStudentInscription->setMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($arrDataInscription['materiaId']));
            $objOLimStudentInscription->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:OlimDiscapacidadTipo')->find($form['discapacidad']));
            $objOLimStudentInscription->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrDataInscription['estinsid']));
            $objOLimStudentInscription->setOlimTutor($em->getRepository('SieAppWebBundle:OlimTutor')->find($arrDataInscription['olimtutorid']));
            $objOLimStudentInscription->setGestionTipoId($arrDataInscription['gestiontipoid']);
            
            $em->persist($objOLimStudentInscription);
            $em->flush();

            //save in group info
            $objGroup = new OlimInscripcionGrupoProyecto();
            $objGroup->setFechaRegistro(new \DateTime('now'));
            $objGroup->setOlimGrupoProyecto($em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($arrDataInscription['groupId']));
            $objGroup->setOlimEstudianteInscripcion($em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($objOLimStudentInscription->getId()));

            $em->persist($objGroup);
            $em->flush();

            $em->getConnection()->commit();
            
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";     
        }

         return $this->redirectToRoute('oliminscriptions_viewListInscritosGroup', array('jsonDataInscription'=>$jsonDataInscription));
      
    }







}
