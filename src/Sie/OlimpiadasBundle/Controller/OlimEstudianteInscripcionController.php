<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sie\AppWebBundle\Entity\OlimEstudianteInscripcion;
use Sie\AppWebBundle\Form\OlimEstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * OlimEstudianteInscripcion controller.
 *
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
                ->add('buscar', 'button', array('label'=>'Buscar', 'attr'=>array('onclick'=>'openInscriptinoOlimpiadas();'), ))
                
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
        $entity = $em->getRepository('SieAppWebBundle:OlimCategoriaTipo');
        $query = $entity->createQueryBuilder('oct')
                ->select('oct.id, oct.categoria')
                ->where('oct.olimMateriaTipo = :materiaid')
                ->setParameter('materiaid', $idmateria)
                ->orderBy('oct.categoria', 'ASC')
                ->getQuery();
                // dump($query->getSQL());die;
        $objCategory = $query->getResult();
        
        if(sizeof($objCategory)>0){
            $arrCategory = array();
            foreach ($objCategory as $value) {
                $arrCategory[$value['id']] = $value['categoria'];
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

    /**
     * [getParaleloAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getParaleloAction(Request $request) {
        //get the send values
        $grado = $request->get('grado');
        $jsonRule = $request->get('jsonRule');
        $nivel = $request->get('nivel');
        $arrData = json_decode($jsonRule, true);
        $sie = $arrData['institucionEducativa'];
        $gestion = $arrData['gestion'];
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
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
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
        $paralelo = $request->get('paralelo');
        $grado = $request->get('grado');
        $jsonRule = $request->get('jsonRule');
        $nivel = $request->get('nivel');
        $arrData = json_decode($jsonRule, true);
        $sie = $arrData['institucionEducativa'];
        $gestion = $arrData['gestion'];
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
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
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
        //create db conexino
        $em = $this->getDoctrine()->getManager();
         //get the send values
        $turno = $request->get('turno');
        $paralelo = $request->get('paralelo');
        $grado = $request->get('grado');
        $jsonRule = $request->get('jsonRule');
        $nivel = $request->get('nivel');
        $arrData = json_decode($jsonRule, true);
        
        $sie = $arrData['institucionEducativa'];
        $gestion = $arrData['gestion'];
        //look for the id of institucioneducativa_curso
        $objInstitucionEducativaCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy( array(
             'nivelTipo' =>$nivel,
             'gradoTipo' => $grado,
             'paraleloTipo'=> $paralelo,
             'turnoTipo' => $turno, 
             'institucioneducativa' => $sie,
             'gestionTipo' => $gestion
        ));
        
        $objStudentsToOlimpiadas = $this->get('olimfunctions')->getStudentsToOlimpiadas($objInstitucionEducativaCurso->getId());
        // dump($objStudentsToOlimpiadas);
        $arrCorrectStudent = array();
        foreach ($objStudentsToOlimpiadas as $key => $value) {

            $newStudentDate = date('d-m-Y', strtotime($value['fecha_nacimiento']) );
            $value['fecha_nacimiento'] = $newStudentDate;
            $studentYearsOld = $this->get('olimfunctions')->getYearsOldsStudent($newStudentDate, '30-6-2018');
            $value['yearsOld'] = $studentYearsOld[0];
            $arrCorrectStudent[]=($value);
            

        }
        //get the discapacidad
        $objDiscapacidad = $em->getRepository('SieAppWebBundle:DiscapacidadTipo')->findAll();
        
        // dump($objDiscapacidad);
        // die;
        
        // dump($objStudentsToOlimpiadas);die;

        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:getStudents.html.twig', array(
            'objStudentsToOlimpiadas' => $arrCorrectStudent,
            'form' => $this->studentsRegisterform()->createView(),
            'objDiscapacidad' => $objDiscapacidad,

        ));
    }

    private function studentsRegisterform(){
        return $this->createFormBuilder()
                ->add('register', 'button', array('label'=>'Registrar', 'attr'=>array('class'=>'btn btn-success btn-xs', 'onclick'=>'studentsRegister()')))
                ->getForm()
                ;

    }

    public function studentsRegisterAction(Request $request){
        
        // create db conexioon
        $em =  $this->getDoctrine()->getManager();
        //get the send values
        $form = $request->get('form');
        // remove the las elemento of form array
        array_pop($form);
        //count the send elements
        //if everything ok on the rule do the save
        reset ($form);
        while($val = current($form)){
            if(isset($val['studentInscription'])){
                dump($val);   
                $objOLimStudentInscription = new OlimEstudianteInscripcion();
                $objOLimStudentInscription->setTelefonoEstudiante($val['fono']);
                $objOLimStudentInscription->setCorreoEstudiante($val['email']);
                $objOLimStudentInscription->setFechaRegistro(new \DateTime('now'));
                $objOLimStudentInscription->setUsuarioRegistroId($this->session->get('userId'));
                $objOLimStudentInscription->setReglasOlimpiadasTipo($em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->find($val['fono']) );
                $objOLimStudentInscription->setCategoriaTipo($em->getRepository('SieAppWebBundle:OlimCategoriaTipo')->find($val['fono']));
                $objOLimStudentInscription->setMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($val['fono']));
                $objOLimStudentInscription->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:OlimDiscapacidadTipo')->find($val['discapacidad']));
                $objOLimStudentInscription->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($val['studentInscription']));
                $objOLimStudentInscription->setGestionTipoId($val['fono']);
                $objOLimStudentInscription->setTelefonoEstudiante($val['fono']);
                $objOLimStudentInscription->setTelefonoEstudiante($val['fono']);


            }
            next($form);
        }

        // dump($form);

dump(sizeof($form));die;





    }



}
