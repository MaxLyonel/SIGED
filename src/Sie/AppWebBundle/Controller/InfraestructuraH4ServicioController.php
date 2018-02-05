<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH4Servicio;
use Sie\AppWebBundle\Form\InfraestructuraH4ServicioType;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InfraestructuraH4ServicioBateriaBanos;
use Sie\AppWebBundle\Form\InfraestructuraH4ServicioBateriaBanosType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InfraestructuraH4ServicioVestuarios;
use Sie\AppWebBundle\Form\InfraestructuraH4ServicioVestuariosType;


/**
 * InfraestructuraH4Servicio controller.
 *
 */
class InfraestructuraH4ServicioController extends Controller
{

    /**
     * Lists all InfraestructuraH4Servicio entities.
     *
     */
    public function indexAction(){

        //get values to send to create o edit
        $infraestructuraJuridiccionGeograficaId = 10303;
        // 26753
        // create the db conexion
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4Servicio')->findOneBy(array(
            'infraestructuraJuridiccionGeografica'=> $infraestructuraJuridiccionGeograficaId
        ));

        if ($entity) {
            // if exist then, edit the values
            return $this->redirect($this->generateUrl('infraestructurah4servicio_edit', array('id' => $entity->getId())));

        } else {
            // no exist so create the new data
             return $this->redirect($this->generateUrl('infraestructurah4servicio_new'));
        }

        // // dump($entity);die;

        // $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH4Servicio')->findBy(array(), array('id'=>'DESC'),10);

        // return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:index.html.twig', array(
        //     'entities' => $entities,
        // ));
    }
    /**
     * Creates a new InfraestructuraH4Servicio entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH4Servicio();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        // var_dump($form->isValid());die;
        if (!$form->isValid()) {

            // $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h4_servicio');");
            // $query->execute();

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('infraestructurah4servicio_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH4Servicio entity.
     *
     * @param InfraestructuraH4Servicio $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH4Servicio $entity)
    {
        $form = $this->createForm(new InfraestructuraH4ServicioType(), $entity, array(
            'action' => $this->generateUrl('infraestructurah4servicio_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH4Servicio entity.
     *
     */
    public function newAction()
    {
        $entity = new InfraestructuraH4Servicio();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a InfraestructuraH4Servicio entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4Servicio')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH4Servicio entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH4Servicio entity.
     *
     */
    public function editAction($id){
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4Servicio')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH4Servicio entity.');
        }

        $editForm = $this->createEditForm($entity);
        // $deleteForm = $this->createDeleteForm($id);
        // look for the bateras de bano data to draw in the view
        $entityBateriaBanos = $em->getRepository('SieAppWebBundle:InfraestructuraH4ServicioBateriaBanos')->findBy(array(
            // 'infraestructuraH4Servicio' => $id
            'infraestructuraH4Servicio' => $id
        ));
        // look for the vestuario data to draw in the view
        $entityVestuarios = $em->getRepository('SieAppWebBundle:InfraestructuraH4ServicioVestuarios')->findBy(array(
            // 'infraestructuraH4Servicio' => $id
            'infraestructuraH4Servicio' => 18
        ));
        
        if (!$entityBateriaBanos) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH4ServicioBateriaBanos entity.');
        }
        // create new enttity about bateria bano
        // $entityNewBateriaBanos = new InfraestructuraH4ServicioBateriaBanos();
        // $editFormBateriaBano = $this->createCreateFormBateriaBano($entityNewBateriaBanos,$id);


        return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:edit.html.twig', array(
            'entity'      => $entity,
            'entityBateriaBanos'      => $entityBateriaBanos,
            'entityVestuarios'      => $entityVestuarios,
            'form'   => $editForm->createView(),
            'idInfraestructuraH4Servicio'   => $id

            // 'edit_form'   => $editFormBateriaBano->createView(),
            // 'delete_form' => $deleteForm->createView(),
        ));
    }

    public function newBateriasBanoAction(Request $request){

        
        //get the send data
        $idInfraestructuraH4Servicio = $request->get('idInfraestructuraH4Servicio');
        
         // create new enttity about bateria bano
        $entityNewBateriaBanos = new InfraestructuraH4ServicioBateriaBanos();
        $editFormBateriaBano = $this->createCreateFormBateriaBano($entityNewBateriaBanos,$idInfraestructuraH4Servicio);

        return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:createBateriasBano.html.twig', array(
            'edit_form'   => $editFormBateriaBano->createView(),
        ));

    }

    public function saveNewBateriaBanoAction(Request $request){
        //get the send values
        // dump($request);
        $form = $request->get('sie_appwebbundle_infraestructurah4serviciobateriabanos');
        $em = $this->getDoctrine()->getManager();
      
        // $entity->setN5BanioConagua
        // $entity->setN5BanioSinagua       
        // $entity->setFecharegistro($form['']);
        // $entity->setN5EsFuncionaAmbiente($form['']);
        // $entity->setEstadoTipo($form['']);
        // $entity->setN52TieneRevestMaterTipo($form['']);
        // $entity->setN5CieloEstadogeneralTipo($form['']);
        // $entity->setN5PisoEstadogeneralTipo($form['']);
        // $entity->setN5TechoEstadogeneralTipo($form['']);
        // $entity->setN5ParedEstadogeneralTipo($form['']);
        // $entity->setInfraestructuraH4Servicio
        
        try {
              $entity = new InfraestructuraH4ServicioBateriaBanos();

                $entity->setN5AmbienteBanoTipo($em->getRepository('SieAppWebBundle:InfraestructuraH4BanoTipo')->find($form['n5AmbienteBanoTipo']));
                $entity->setN5Areatotalm2($form['n5Areatotalm2']);
                $entity->setN5LetrinaFunciona($form['n5LetrinaFunciona']);
                $entity->setN5LetrinaNofunciona($form['n5LetrinaNofunciona']);
                $entity->setN5InodoroFunciona($form['n5InodoroFunciona']);
                $entity->setN5InodoroNofunciona($form['n5InodoroNofunciona']);
                $entity->setN5UrinarioFunciona($form['n5UrinarioFunciona']);
                $entity->setN5UrinarioNofunciona($form['n5UrinarioNofunciona']);
                $entity->setN5LavamanoFunciona($form['n5LavamanoFunciona']);
                $entity->setN5LavamanoNofunciona($form['n5LavamanoNofunciona']);
                $entity->setN5DuchaFunciona($form['n5DuchaFunciona']);
                $entity->setN5DuchaNofunciona($form['n5DuchaNofunciona']);
                $entity->setN52EsTieneCieloFalso($form['n52EsTieneCieloFalso']);
                $entity->setN52TieneCieloFalsoCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n52TieneCieloFalsoCaracTipo']));
                $entity->setN52EsTieneMuros($form['n52EsTieneMuros']);
                $entity->setN52TieneMurosMaterTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosMaterialTipo')->find($form['n52TieneMurosMaterTipo']));
                $entity->setN52TieneMurosCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosCaracTipo')->find($form['n52TieneMurosCaracTipo']));
                $entity->setN52EsTienePuerta($form['n52EsTienePuerta']);
                $entity->setN521SeguroTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasSeguroTipo')->find($form['n521SeguroTipo']));
                $entity->setN522AbreTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasAbreTipo')->find($form['n522AbreTipo']));
                $entity->setN52EsTieneRevest($form['n52EsTieneRevest']);
                $entity->setN52TieneRevestCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n52TieneRevestCaracTipo']));
                $entity->setN52EsTieneVentana($form['n52EsTieneVentana']);
                $entity->setN52TieneVentanaCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenVentanasCaracTipo')->find($form['n52TieneVentanaCaracTipo']));
                $entity->setN52EsTienePiso($form['n52EsTienePiso']);
                $entity->setN52TienePisoMaterTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPisosMaterialTipo')->find($form['n52TienePisoMaterTipo']) );
                $entity->setN52TienePisoCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n52TienePisoCaracTipo']));
                $entity->setN52EsTieneTecho($form['n52EsTieneTecho']);
                $entity->setInfraestructuraH4Servicio($em->getRepository('SieAppWebBundle:InfraestructuraH4Servicio')->find($form['idInfraestructuraH4Servicio']));

                 $entity->setN5BanioConagua(1);
                 $entity->setN5BanioSinagua(1);
            $em->persist($entity);
            $em->flush();


            $entityBateriaBanos = $em->getRepository('SieAppWebBundle:InfraestructuraH4ServicioBateriaBanos')->findBy(array(
            // 'infraestructuraH4Servicio' => $id
            'infraestructuraH4Servicio' => $form['idInfraestructuraH4Servicio']
            ));
        // create new enttity about bateria bano
        // $entityNewBateriaBanos = new InfraestructuraH4ServicioBateriaBanos();
        // $editFormBateriaBano = $this->createCreateFormBateriaBano($entityNewBateriaBanos,$id);


        return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:bateriabanos.html.twig', array(
                 'entityBateriaBanos'      => $entityBateriaBanos,
            
        ));


        } catch (Exception $e) {
            
        }
        
        
    }

     /**
     * Creates a form to create a InfraestructuraH4ServicioBateriaBanos entity.
     *
     * @param InfraestructuraH4ServicioBateriaBanos $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateFormBateriaBano(InfraestructuraH4ServicioBateriaBanos $entity, $id){
        $form = $this->createForm(new InfraestructuraH4ServicioBateriaBanosType(), $entity, array(
            // 'action' => $this->generateUrl('infraestructurah4serviciobateriabanos_create'),
            // 'method' => 'POST',
        ));

        // $form->add('submit', 'submit', array('label' => 'Guardar'));
        $form->add('idInfraestructuraH4Servicio', 'text', array('mapped'=>false,'label' => 'id', 'data'=>$id));

        return $form;
    }

    /**
    * Creates a form to edit a InfraestructuraH4ServicioBateriaBanos entity.
    *
    * @param InfraestructuraH4ServicioBateriaBanos $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditFormBateriaBano(InfraestructuraH4ServicioBateriaBanos $entity)
    {
        $form = $this->createForm(new InfraestructuraH4ServicioBateriaBanosType(), $entity, array(
            'action' => $this->generateUrl('infraestructurah4serviciobateriabanos_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('button', 'button', array('label' => 'Actualizar'));

        return $form;
    }

    /**
    * Creates a form to edit a InfraestructuraH4Servicio entity.
    *
    * @param InfraestructuraH4Servicio $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH4Servicio $entity)
    {
        $form = $this->createForm(new InfraestructuraH4ServicioType(), $entity, array(
            'action' => $this->generateUrl('infraestructurah4servicio_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * [newVestuariosAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function newVestuariosAction(Request $request){

        $idInfraestructuraH4Servicio = $request->get('idInfraestructuraH4Servicio');
        //get the send data
        $idInfraestructuraH4Servicio = $request->get('idInfraestructuraH4Servicio');
        
         // create new enttity about bateria bano
        $entity = new InfraestructuraH4ServicioVestuarios();
        $form   = $this->createCreateVestuariosForm($entity, $idInfraestructuraH4Servicio);

        return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:newVestuario.html.twig', array(
            'entity' => $entity,
            'new_form'   => $form->createView(),
        ));

    }
    /**
     * [saveNewVestuarioAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
     public function saveNewVestuarioAction(Request $request){
        //get the send values
        
        // dump($request);
        $form = $request->get('sie_appwebbundle_infraestructurah4serviciovestuarios');
        // dump($form);
        // die;
        $em = $this->getDoctrine()->getManager();
           
        try {
              $entity = new InfraestructuraH4ServicioVestuarios();

                $entity->setN6ServicioAmbienteTipo($em->getRepository('SieAppWebBundle:InfraestructuraH4BanoTipo')->find($form['n6ServicioAmbienteTipo']));
                $entity->setN6Areatotalm2($form['n6Areatotalm2']);
                $entity->setN6EsFuncionaAmbiente($form['n6EsFuncionaAmbiente']);
                $entity->setN62EsTieneTecho($form['n62EsTieneTecho']);
                $entity->setN62EsTieneCieloFalso($form['n62EsTieneCieloFalso']);
                $entity->setN62TieneCieloFalsoCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n62TieneCieloFalsoCaracTipo']));
                
                $entity->setN62EsTieneMuros($form['n62EsTieneMuros']);
                $entity->setN62TieneMurosCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosCaracTipo')->find($form['n62TieneMurosCaracTipo']));
                $entity->setN62TieneMurosMaterTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosMaterialTipo')->find($form['n62TieneMurosMaterTipo']));

                $entity->setN62EsTienePuerta($form['n62EsTienePuerta']);
                $entity->setN62EsTieneRevest($form['n62EsTieneRevest']);
                $entity->setN62TieneRevestMaterTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenRevestimientoMaterialTipo')->find($form['n62TieneRevestMaterTipo']));
                $entity->setN62TieneRevestCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n62TieneRevestCaracTipo']));
                $entity->setN62EsTieneVentana($form['n62EsTieneVentana']);
                $entity->setN62TieneVentanaCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenVentanasCaracTipo')->find($form['n62TieneVentanaCaracTipo']));
                $entity->setN62EsTienePiso($form['n62EsTienePiso']);
                $entity->setN62TienePisoMaterTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPisosMaterialTipo')->find($form['n62TienePisoMaterTipo']));
                $entity->setN62TienePisoCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n62TienePisoCaracTipo']) );
                // $entity->setN5DuchaFunciona($form['n5DuchaFunciona']);
                
                $entity->setInfraestructuraH4Servicio($em->getRepository('SieAppWebBundle:InfraestructuraH4Servicio')->find($form['idInfraestructuraH4Servicio']));

                
            $em->persist($entity);
            $em->flush();


            $entityVestuarios = $em->getRepository('SieAppWebBundle:InfraestructuraH4ServicioBateriaBanos')->findBy(array(
            // 'infraestructuraH4Servicio' => $id
            'infraestructuraH4Servicio' => $form['idInfraestructuraH4Servicio']
            ));
        // create new enttity about bateria bano
        // $entityNewBateriaBanos = new InfraestructuraH4ServicioBateriaBanos();
        // $editFormBateriaBano = $this->createCreateFormBateriaBano($entityNewBateriaBanos,$id);
        return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:vestuarios.html.twig', array(
                 'entityVestuarios'      => $entityVestuarios,
            
        ));

        } catch (Exception $e) {
            
        }
        
    }

      /**
     * Creates a form to create a InfraestructuraH4ServicioVestuarios entity.
     *
     * @param InfraestructuraH4ServicioVestuarios $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateVestuariosForm(InfraestructuraH4ServicioVestuarios $entity, $id)
    {
        $form = $this->createForm(new InfraestructuraH4ServicioVestuariosType(), $entity, array(
            // 'action' => $this->generateUrl('infraestructurah4serviciovestuarios_create'),
            'method' => 'POST',
        ));

        // $form->add('submit', 'submit', array('label' => 'Guardar'));
        $form->add('idInfraestructuraH4Servicio', 'text', array('mapped'=>false,'label' => 'id', 'data'=>$id));

        return $form;
    }







    /**
     * Edits an existing InfraestructuraH4Servicio entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4Servicio')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH4Servicio entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infraestructurah4servicio_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH4Servicio entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4Servicio')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH4Servicio entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infraestructurah4servicio'));
    }

    /**
     * Creates a form to delete a InfraestructuraH4Servicio entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infraestructurah4servicio_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
