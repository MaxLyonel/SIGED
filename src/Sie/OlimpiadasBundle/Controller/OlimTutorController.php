<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\OlimTutor;
use Sie\AppWebBundle\Form\OlimTutorType;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\OlimDirectorInscripcionDatos;

/**
 * OlimTutor controller.
 *
 */
class OlimTutorController extends Controller{
    
    private $sesion;

    public function __construct(){
        $this->session = new Session();
    }
    /**
     * Lists all OlimTutor entities.
     *
     */
    public function indexAction(Request $request){
        

        if($this->session->get('ie_id') <= 0){
            //call find sie template
            $modeview = 1;
        }else{
            $modeview = 0;
           // return $this->redirectToRoute('olimtutor_listTutorBySie');
        }

        return $this->render('SieOlimpiadasBundle:OlimTutor:index.html.twig', array(
                'form'=>$this->formFindTutor()->createView(),
                'modeview' => $modeview
            ));
       
    }

    private function formFindTutor(){
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('olimtutor_listTutorBySie'))
            ->setMethod('POST')
            ->add('sie', 'text', array('attr'=>array('value'=>$this->session->get('ie_id'))))
            ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestion', 'choices' => array($this->session->get('currentyear')=>$this->session->get('currentyear')), 'attr' => array()))
            ->add('sendData', 'submit', array('label'=>'Continuar'))
            ->getForm()
        ;
    }

    public function listTutorBySieAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        //data ue
        if($request->getMethod()=='POST'){
                // get the send data
                $form = $request->get('form');
                //dump($form);die;
                    if(isset($form['newtutor'])){
                                $arrSendData = json_decode($form['newtutor'],true);
                                // dump($arrSendData);die;
                            // udpate the indice
                            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_tutor');");
                            $query->execute();
                            // create the object to do the save
                            $entity = new OlimTutor();
                            $entity->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($form['personaid']));
                            $entity->setTelefono1($form['telefono1']);
                            $entity->setTelefono2($form['telefono2']);
                            $entity->setCorreoElectronico($form['correoElectronico']);
                            $entity->setFechaRegistro(new \Datetime('now'));
                            $entity->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']));
                            $entity->setOlimRegistroOlimpiada($em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($arrSendData['olimRegistroOlimpiadaId']));
                            // $entity->setMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($data['olimMateria']));   
                            
                            // if($data['category']!='')           
                            //     $entity->setCategoriaTipo($em->getRepository('SieAppWebBundle:OlimCategoriaTipo')->find($data['category']));   
                            // $entity->setPeriodoTipo($em->getRepository('SieAppWebBundle:OlimPeriodoTipo')->find(1));   
                            $entity->setGestionTipoId($form['gestion']);   

                            $em->persist($entity);
                            $em->flush();
                    }
                    if(isset($form['editutor'])) {
                        $this->saveUpdateDataTutor($form);
                    }
                    if(isset($form['deletetutor'])) {
                        $this->deleteDataTutor($form);
                    }
                    if(isset($form['datosId'])) {
                        
                        if($form['datosId'] > 0){
                            $datosId = $form['datosId'];
                            $datosDirector = $em->getRepository('SieAppWebBundle:OlimDirectorInscripcionDatos')->findOneById($datosId);
                        }
                        else{
                            $maestroId = $form['maestroId'];
                            $maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($maestroId);
                            $datosDirector = new OlimDirectorInscripcionDatos();
                            $datosDirector->setMaestroInscripcion($maestroInscripcion);
                            $datosDirector->setFechaRegistro(new \Datetime('now'));
                        }
                        
                        $datosDirector->setEsVigente(true);
                        $datosDirector->setTelefono1($form['telefono1']);
                        $datosDirector->setTelefono2($form['telefono2']);
                        $datosDirector->setCorreoElectronico($form['correoElectronico']);
                        
                        $em->persist($datosDirector);
                        $em->flush();
                    }
                    
                    $formDirector = $request->get('sie_appwebbundle_olimdirectorinscripciondatos');
                    if(is_array($formDirector)){
                        //udpate the director info
                        $form = $formDirector;
                        
                    }

                    $institucioneducativa = $form['sie'];
                    $gestion = $form['gestion'];
                         
        }else{
            //set teh institucioneducativa variable
            $institucioneducativa = $this->session->get('ie_id');
            $gestion = $this->session->get('currentyear');
        }

        // array_pop($form);

        $em = $this->getDoctrine()->getManager();
        $objOlimRegistroOlimpiada = $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->findOneBy(array(
            'gestionTipo'=>$form['gestion']
        ));
        
        // dump($objOlimRegistroOlimpiada);
        // $entities = $em->getRepository('SieAppWebBundle:OlimTutor')->findBy(array(
        //     'institucioneducativa'  => $institucioneducativa,
        //     'olimRegistroOlimpiada' => $objOlimRegistroOlimpiada->getId()
        // ), array('id'=>'DESC'));
        $form['olimRegistroOlimpiadaId'] = $objOlimRegistroOlimpiada->getId();
        
        $datainscription = array('sie'=>$institucioneducativa, 'OlimRegistroOlimpiadaId'=>$objOlimRegistroOlimpiada->getId());
        $entities = $this->get('olimfunctions')->getTutores2($datainscription);
                
        // $director = $this->getDirectorInfo($form);
        $director = $this->get('olimfunctions')->getDirectorInfo($form);
        // dump($director);die;
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucioneducativa);
        //dump($director);die;
        return $this->render('SieOlimpiadasBundle:OlimTutor:listTutor.html.twig', array(
            'entities' => $entities,
            'formNewTutor' => $this->formNewTutor(json_encode($form))->createView(),
            'director' => $director,
            'institucion' => $institucion,
            'gestion' => $gestion,
            'jsonData' => json_encode($form) 
        ));
    }

/*    private function getDirectorInfo($data){
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $repositorio = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $repositorio->createQueryBuilder('a')
            ->select('a.id as maestroId, b.id as personaId, c.id as datosId, b.carnet, b.paterno, b.materno, b.nombre, c.telefono1, c.telefono2, c.correoElectronico')
            ->innerjoin('SieAppWebBundle:Persona', 'b', 'WITH', 'a.persona = b.id')
            ->innerjoin('SieAppWebBundle:OlimDirectorInscripcionDatos', 'c', 'WITH', 'c.maestroInscripcion=a.id')
            ->where('a.gestionTipo = :gestion')
            ->andwhere('a.institucioneducativa = :sie')
            ->andwhere('a.esVigenteAdministrativo = :vigente')
            ->setParameter('gestion', $data['gestion'])
            ->setParameter('sie', $data['sie'])
            ->setParameter('vigente', 't')
            ->getQuery();
        return  $query->getSingleResult();
    }*/

    private function formNewTutor($data){
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('olimtutor_newTutor'))
            ->setMethod('POST')
            ->add('data', 'hidden', array('attr'=>array('value'=>$data)))
            ->add('newTutor', 'submit', array('label'=> 'Nuevo Tutor', 'attr'=>array('class'=>'btn btn-info btn-md')))
            ->getForm()
            ;
    }

    public function newTutorAction (Request $request){
        //got the send data
        $form = $request->get('form');
        array_pop(($form));
        // dump($form['data']);die;
        return $this->render('SieOlimpiadasBundle:OlimTutor:newTutor.html.twig', array(
            'form' => $this->formLookForTutor($form['data'])->createView(),

        ));

    }

    private function formLookForTutor($data){
        return $this->createFormBuilder()
            ->add('carnet', 'text', array('label'=>'CI:'))
            ->add('complemento', 'text', array('label'=>'Complemento:'))
            ->add('fechanacimiento', 'text', array('label'=>'Fecha Nacimiento:'))
            ->add('data', 'hidden', array('attr'=>array('value'=>$data)))
            ->add('buscar', 'button', array('label'=>'Buscar', 'attr'=>array('onclick'=>'lookForTutor()')))
            ->getForm()
            ;
    }

    public function resultTutoresAction(Request $request){
        
        //get the send data
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        $arrForm = json_decode($form['data'],true);
        $tutorFound = true;
        
        // $resultTutores = $this->get('olimfunctions')->lookForTutores($form);
        $resultTutores = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                'carnet'=> $form['carnet'],
                'complemento'=>$form['complemento'],
                'fechaNacimiento'=> new \DateTime($form['fechanacimiento'])
            ));
        if(!$resultTutores){
            $tutorFound = false;
        }
        // dump($resultTutores);die;
        return $this->render('SieOlimpiadasBundle:OlimTutor:resultTutores.html.twig', array(
            'entity'       => $resultTutores,
            'jsonDataInscription' => $form['data'],
            'sieSelected' => $arrForm['sie'],
            'gestionSelected' => $arrForm['gestion'],
            'form' => $this->formResultTutor($form['data'])->createView(),
            'tutorFound' => $tutorFound
        ));
    }

    private function formResultTutor($data){
        
        $arrData = json_decode($data, true);
        // dump($arrData);die;
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('olimtutor_listTutorBySie'))
            ->setMethod('POST')
            ->add('telefono1', 'text')
            ->add('telefono2', 'text')
            ->add('correoElectronico', 'text')
            ->add('sie', 'hidden', array('attr'=>array('value'=>$arrData['sie'])))
            ->add('gestion', 'hidden', array('attr'=>array('value'=>$arrData['gestion'])))
            ->add('newtutor', 'hidden', array('data'=>$data))
            ->add('registrar', 'submit')

            ->getForm()
            ;

    }



///////////////////////////////////////////////////////////////////the new
///////////////////////////////////////////////////////////////////the new
///////////////////////////////////////////////////////////////////the new
///////////////////////////////////////////////////////////////////the new
///////////////////////////////////////////////////////////////////the new

    /**
     * [listTutorAction description]
     * @return [type] [description]
     */
    public function listTutorAction(Request $request){
        // get send data
        $jsonDataInscription = $request->get('datainscription');
        $arrDataInscription = json_decode($jsonDataInscription, true);
        
        $em = $this->getDoctrine()->getManager();
        // $entities = $em->getRepository('SieAppWebBundle:OlimTutor')->findAll();
        $entities =  $this->get('olimfunctions')->getTutores($arrDataInscription);
        
        return $this->render('SieOlimpiadasBundle:OlimTutor:listTutor.html.twig', array(
            'entities' => $entities,
            'jsonDataInscription' => $jsonDataInscription,
        ));
    }

    public function addTutorAction(Request $request){
        //get the send values
        $jsonDataInscription = $request->get('jsonDataInscription');
    // dump($jsonDataInscription);die;

        return $this->render('SieOlimpiadasBundle:OlimTutor:addTutor.html.twig', array(
            'form' => $this->createAddTutorForm($jsonDataInscription)->createView()
        ));
    }
    /**
     * [createAddTutorForm description]
     * @param  [type] $jsonDataInscription [description]
     * @return [type]                      [description]
     */
    private function createAddTutorForm($jsonDataInscription){
        return $this->createFormBuilder()
            ->add('carnet', 'text', array('label'=>'CI:'))
            ->add('complemento', 'text', array('label'=>'Complemento:'))
            ->add('fechanacimiento', 'text', array('label'=>'Fecha Nacimiento:'))
            ->add('jsonDataInscription', 'text', array('data'=>$jsonDataInscription))
            ->add('buscar', 'button', array('label'=>'Buscar', 'attr'=>array('onclick'=>'buscarTutor()')))
            ->getForm()
            ;

    }
    /**
     * [buscarTutorAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function buscarTutorAction(Request $request){
        //get the send values
        $form = $request->get('form');
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        //get the tutorse
        $resultTutores = $this->get('olimfunctions')->lookForTutores($form);
        
        return $this->render('SieOlimpiadasBundle:OlimTutor:buscarTutor.html.twig', array(
            'resultTutores'       => $resultTutores,
            'jsonDataInscription' => $form['jsonDataInscription'],
            // 'form' => $this->buscarTutorForm($form['jsonDataInscription'])->createView()
        ));

    }
    /**
     * [buscarTutorForm description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function buscarTutorForm($data){
        return $this->createFormBuilder()
                ->add('telefono1', 'text')
                ->add('telefono2', 'text')
                ->add('correoElectronico', 'text')
                ->add('personaid', 'hidden')
                ->add('data', 'hidden', array('data'=>$data))
                ->add('registrar','button', array('label'=>'Registrar', 'attr'=>array('onclick'=>'registroTutor()')))
                ->getForm()
                ;
    }

    public function registrarTutorAction(Request $request){
        //get the send values
        $form = $request->get('form');
        $data = json_decode($form['data'],true);
        // dump($data);
        // dump($form);
        // die;
        //create db conexion 
        $em = $this->getDoctrine()->getManager();
        // udpate the indice
        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_tutor');");
        $query->execute();
        // create the object to do the save
        $entity = new OlimTutor();
        $entity->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($form['personaid']));
        $entity->setTelefono1($form['telefono1']);
        $entity->setTelefono2($form['telefono2']);
        $entity->setCorreoElectronico($form['correoElectronico']);
        $entity->setFechaRegistro(new \Datetime('now'));
        $entity->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($data['institucionEducativa']));
        $entity->setMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($data['olimMateria']));   
        
        if($data['category']!='')           
            $entity->setCategoriaTipo($em->getRepository('SieAppWebBundle:OlimCategoriaTipo')->find($data['category']));   
        $entity->setPeriodoTipo($em->getRepository('SieAppWebBundle:OlimPeriodoTipo')->find(1));   
        $entity->setGestionTipoId($data['gestion']);   

        $em->persist($entity);
        $em->flush();

        //get values to todo the view
        $jsonDataInscription = $form['data'];
        $arrDataInscription = json_decode($jsonDataInscription, true);
        //get tutores
        $entities =  $this->get('olimfunctions')->getTutores($arrDataInscription);
        
        return $this->render('SieOlimpiadasBundle:OlimTutor:listTutor.html.twig', array(
            'entities' => $entities,
            'jsonDataInscription' => $jsonDataInscription,
        ));
    }
    /**
     * [selectTypeInscriptionAction description] by krlos
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function selectTypeInscriptionAction(Request $request){
        //ge the send data
        $jsonDataInscription = $request->get('jsonDataInscription');
        $tutorid = $request->get('tutorid');
        //ge the inscription data
        $arrDataInscription = json_decode($jsonDataInscription, true);
        // dump($arrDataInscription);die;
        switch ($arrDataInscription['olimMateria']) {
            //common inscription
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
                # code...
                return $this->redirectToRoute('oliminscriptions_commonareainscription', array('jsonDataInscription'=> $jsonDataInscription,'tutorid'=>$tutorid));
                break;
            //informatica inscription
            case 8:
                # code...
                dump('info');
                break;
            // robotica inscription
            case 10:
                # code...
                return $this->redirectToRoute('oliminscriptions_robotica');
                break;
            // feria inscription
            case 11:
                # code...
                dump('feria');
                break;
            
            default:
                # code...
                break;
        }

        

    }

    public function updateDataTutorAction(Request $request){
        $em = $this->getDoctrine()->getManager();                                                                                                                                                  
        //get the send data
        $form = $request->get('form');
        
        $personaid = $form['personaid'];
        $olimtutorid = $form['olimtutorid'];
        $olimregistroolimpiadaid = $form['olimregistroolimpiadaid'];

        $olimTutor = $em->getRepository('SieAppWebBundle:OlimTutor')->find($olimtutorid);
        $dataTutorPersona = $em->getRepository('SieAppWebBundle:Persona')->find($personaid);
        
        return $this->render('SieOlimpiadasBundle:OlimTutor:updateDataTutor.html.twig', array(
            'form'=> $this->updateDataTutorForm($olimTutor, $form['jsonData'])->createView(),
            'dataTutorPersona'=>$dataTutorPersona,
        ));

    }

    private function updateDataTutorForm($olimTutor, $jsonData){
        $arrData = json_decode($jsonData, true);

        return $this->createFormBuilder()
            ->setAction($this->generateUrl('olimtutor_listTutorBySie'))
            ->add('telefono1', 'text', array('required' => true, 'data'=>$olimTutor->getTelefono1()))
            ->add('telefono2', 'text', array('required' => false, 'data'=>$olimTutor->getTelefono2()))
            ->add('correoElectronico', 'text', array('required' => true, 'data'=>$olimTutor->getCorreoElectronico()))
            ->add('editutor', 'hidden', array('data'=>$olimTutor->getId()))
            ->add('olimtutorid', 'hidden', array('data'=>$olimTutor->getId()))
            ->add('sie', 'hidden', array('data'=>$arrData['sie'] , 'mapped'=>false))
            ->add('gestion', 'hidden', array('data'=>$arrData['gestion'] , 'mapped'=>false))
            ->add('jsonData', 'hidden', array('data'=>$jsonData, 'mapped'=>false))
            ->add('update','submit', array('label'=>'Actualizar'))
            ->getForm();

    }

     private function saveUpdateDataTutor($form){
        //create db conexion 
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            // create the object to do the save
            $entity = $em->getRepository('SieAppWebBundle:OlimTutor')->find($form['olimtutorid']);

            $entity->setTelefono1($form['telefono1']);
            $entity->setTelefono2($form['telefono2']);
            $entity->setCorreoElectronico($form['correoElectronico']);
            $entity->setFechaModificacion(new \Datetime('now'));

            $em->persist($entity);
            $em->flush();
            $em->getConnection()->commit();
            return true;
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
        
        return false;
    }

    /**
     * Deletes a OlimTutor entity.
     *
     */
    public function deleteDataTutor($form)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            
            $olimTutor = $em->getRepository('SieAppWebBundle:OlimTutor')->find($form['olimtutorid']);

            if ($olimTutor) {
                $olimEstudianteInscripcion = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->findBy(array('olimTutor' => $olimTutor->getId()));
                if ($olimEstudianteInscripcion) {
                    return false;
                }

                $em->remove($olimTutor);
                $em->flush();
                $em->getConnection()->commit();
                return true;
            }
            
            return false;
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
        
        return false;
    }






    ///////////////////////////////////////////////////////////////////////////////////////



    /**
     * Creates a new OlimTutor entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new OlimTutor();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('olimtutor_show', array('id' => $entity->getId())));
        }

        return $this->render('SieOlimpiadasBundle:OlimTutor:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a OlimTutor entity.
     *
     * @param OlimTutor $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(OlimTutor $entity)
    {
        $form = $this->createForm(new OlimTutorType(), $entity, array(
            'action' => $this->generateUrl('olimtutor_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new OlimTutor entity.
     *
     */
    public function newAction()
    {
        $entity = new OlimTutor();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieOlimpiadasBundle:OlimTutor:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a OlimTutor entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimTutor')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimTutor entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieOlimpiadasBundle:OlimTutor:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing OlimTutor entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimTutor')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimTutor entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieOlimpiadasBundle:OlimTutor:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a OlimTutor entity.
    *
    * @param OlimTutor $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(OlimTutor $entity)
    {
        $form = $this->createForm(new OlimTutorType(), $entity, array(
            'action' => $this->generateUrl('olimtutor_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing OlimTutor entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimTutor')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimTutor entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('olimtutor_edit', array('id' => $id)));
        }

        return $this->render('SieOlimpiadasBundle:OlimTutor:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a OlimTutor entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:OlimTutor')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OlimTutor entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('olimtutor'));
    }

    /**
     * Creates a form to delete a OlimTutor entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('olimtutor_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
