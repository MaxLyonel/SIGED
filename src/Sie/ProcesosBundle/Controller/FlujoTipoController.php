<?php

namespace Sie\ProcesosBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

use Sie\AppWebBundle\Entity\FlujoTipo;
use Sie\AppWebBundle\Form\FlujoTipoType;


/**
 * FlujoTipo controller.
 *
 */
class FlujoTipoController extends Controller
{

    public $session;
 
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }
    /**
     * Lists all FlujoTipo entities.
     *
     */
    public function indexAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:FlujoTipo')->findBy(array(),array('id'=>'ASC'));

        return $this->render('SieProcesosBundle:FlujoTipo:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new FlujoTipo entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new FlujoTipo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();   
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('flujo_tipo');")->execute();
            $query = $em->getConnection()->prepare("select flujo from flujo_tipo where flujo='" . $entity->getFlujo() ."'");
            $query->execute();
            $proceso = $query->fetchAll();
            if(!$proceso){
                $em->persist($entity);
                $em->flush();
                $mensaje = 'El proceso ' . $entity->getFlujo() . ' se registró con éxito';
                $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje);
            }else{
                $mensaje = 'El proceso ' . $entity->getFlujo() . ' ya está registrado';
                $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje);
            }

            return $this->redirect($this->generateUrl('flujotipo'));
        }

        return $this->render('SieProcesosBundle:FlujoTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a FlujoTipo entity.
     *
     * @param FlujoTipo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(FlujoTipo $entity)
    {
        $form = $this->createForm(new FlujoTipoType(), $entity, array(
            'action' => $this->generateUrl('flujotipo_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new FlujoTipo entity.
     *
     */
    public function newAction()
    {
        $entity = new FlujoTipo();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieProcesosBundle:FlujoTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a FlujoTipo entity.
     *
     */
    public function verFlujoAction(Request $request, $id)
    {
        
        $data = $this->listarF($id);
        //dump($data);die;
        if($data == false)
        {
            $mensaje = 'El proceso no tiene tareas';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);

            return $this->redirect($this->generateUrl('flujotipo'));
        }else{
            return $this->render('SieProcesosBundle:FlujoTipo:verFlujo.html.twig',$data);
        }
        
        
    }

    /**
     * Displays a form to edit an existing FlujoTipo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:FlujoTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FlujoTipo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieProcesosBundle:FlujoTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a FlujoTipo entity.
    *
    * @param FlujoTipo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(FlujoTipo $entity)
    {
        $form = $this->createForm(new FlujoTipoType(), $entity, array(
            'action' => $this->generateUrl('flujotipo_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing FlujoTipo entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:FlujoTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FlujoTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $mensaje = 'El proceso se modificó con éxito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);

            return $this->redirect($this->generateUrl('flujotipo'));
        }

        return $this->render('SieProcesosBundle:FlujoTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a FlujoTipo entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        //$form = $this->createDeleteForm($id);
        //$form->handleRequest($request);

        //if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:FlujoTipo')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find FlujoTipo entity.');
            }
            $repository = $em->getRepository('SieAppWebBundle:Tramite');
                $query = $repository->createQueryBuilder('t')
                    ->select('t')
                    ->innerJoin('SieAppWebBundle:flujotipo', 'ft', 'WITH', 't.flujoTipo = ft.id')
                    ->where('ft.id = '. $id)
                    ->setMaxResults(1)
                    ->getQuery();
                $tramites = $query->getResult();
                
            //dump(strpos($entity->getObs(),"ACTIVO"));die;
            if(strpos($entity->getObs(),"ACTIVO")!==false)
            {
                $mensaje = 'No se puede eliminar el proceso, pues está ACTIVO';
                $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje);    
            }elseif($tramites){
                $mensaje = 'No se puede eliminar el proceso, pues cuenta con TRÁMITES';
                $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje);    
            }else{
                //dump($tramites);die;
                $query = $em->getConnection()->prepare("delete from wf_tarea_compuerta where flujo_proceso_id in (select id from flujo_proceso where flujo_tipo_id=". $id . ")");
                $query->execute();
                $query = $em->getConnection()->prepare("delete from wf_pasos_flujo_proceso where flujo_proceso_id in (select id from flujo_proceso where flujo_tipo_id=". $id . ")");
                $query->execute();
                $query = $em->getConnection()->prepare("delete from flujo_proceso where flujo_tipo_id=" . $id);
                $query->execute();
                $em->remove($entity);
                $em->flush();
                $mensaje = 'El proceso se eliminó con éxito';
                $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje);
            }
        return $this->redirect($this->generateUrl('flujotipo'));
    }

    /**
     * Creates a form to delete a FlujoTipo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('flujotipo_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    public function listarF($flujotipo)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT f.flujo,fp.id, p.proceso_tipo,r.id as idrol,r.rol,fp.orden, fp.es_evaluacion,fp.variable_evaluacion, wftc.condicion, wfc.nombre, wftc.condicion_tarea_siguiente, fp.plazo, fp.tarea_ant_id, fp.tarea_sig_id
        FROM 
          flujo_tipo f join flujo_proceso fp on f.id = fp.flujo_tipo_id
          join proceso_tipo p on p.id = fp.proceso_id
          left join wf_tarea_compuerta wftc on wftc.flujo_proceso_id = fp.id
          left join wf_compuerta wfc on wftc.wf_compuerta_id=wfc.id
          join rol_tipo r on fp.rol_tipo_id=r.id
          WHERE f.id='. $flujotipo .' order by fp.orden');
        $query->execute();
        $arrDataCondicion = $query->fetchAll();
        $data['flujo']=$arrDataCondicion;
        if($arrDataCondicion)
        {
            $data['nombre']=$arrDataCondicion[0]['flujo'];
            $data['rol']=$arrDataCondicion[0]['idrol'];
            return $data;
        }else{
            return false;
        }

        
    }
}
