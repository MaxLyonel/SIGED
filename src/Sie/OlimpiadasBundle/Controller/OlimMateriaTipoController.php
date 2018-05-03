<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\OlimMateriaTipo;
use Sie\AppWebBundle\Entity\OlimReglasOlimpiadasTipo;
use Sie\AppWebBundle\Form\OlimMateriaTipoType;
use Sie\AppWebBundle\Form\OlimReglasOlimpiadasTipoType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * OlimMateriaTipo controller.
 *
 */
class OlimMateriaTipoController extends Controller
{
    private $session;

    public function __construct(){
        $this->session = new Session();
    }
    /**
     * Lists all OlimMateriaTipo entities.
     *
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->get('id');

        if($id != null){
            $this->session->set('idOlimpiada', $id);
        }else{
            if($this->session->get('idOlimpiada') != null){
                $id = $this->session->get('idOlimpiada');
            }
        }

        $entities = $em->getRepository('SieAppWebBundle:OlimMateriaTipo')->findBy(array(
            'olimRegistroOlimpiada'=>$id
        ), array('materia'=>'ASC'));
        $array = array();
        $cont = 0;
        foreach ($entities as $en) {

            $array[$cont] = array(
                'id'=>$en->getId(),
                'materia'=>$en->getMateria(),
                'fechaInsIni'=>$en->getFechaInsIni()->format('d-m-Y'),
                'fechaInsFin'=>$en->getFechaInsFin()->format('d-m-Y')
            );

            $categorias = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->findBy(
                array('olimMateriaTipo'=>$en->getId()),
                array('id'=>'ASC')
            );
            if(count($categorias)>0){
                foreach ($categorias as $ca) {
                    $array[$cont]['categorias'][] = array(
                        'id'=>$ca->getId(),
                        'categoria'=>$ca->getCategoria(),
                        'modalidad'=>$ca->getModalidadParticipacionTipo()->getModalidad()
                    );
                }
            }else{
                $array[$cont]['categorias'] = array();
            }
            $cont++;
        }

        //dump($array);die;

        // Listado de categorias
        //$categorias = $em->getRepo

        return $this->render('SieOlimpiadasBundle:OlimMateriaTipo:index.html.twig', array(
            'entities' => $array,
            'olimpiada' => $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($this->session->get('idOlimpiada')),
        ));
    }
    /**
     * Creates a new OlimMateriaTipo entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new OlimMateriaTipo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_materia_tipo');")->execute();
            $entity->setOlimRegistroOlimpiada($em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($this->session->get('idOlimpiada')));
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('olimmateriatipo'));
        }

        return $this->render('SieOlimpiadasBundle:OlimMateriaTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a OlimMateriaTipo entity.
     *
     * @param OlimMateriaTipo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(OlimMateriaTipo $entity)
    {
        $form = $this->createForm(new OlimMateriaTipoType(), $entity, array(
            'action' => $this->generateUrl('olimmateriatipo_create'),
            'method' => 'POST',
        ));

        // $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new OlimMateriaTipo entity.
     *
     */
    public function newAction()
    {
        $entity = new OlimMateriaTipo();
        $form   = $this->createCreateForm($entity);
        $em = $this->getDoctrine()->getManager();
        return $this->render('SieOlimpiadasBundle:OlimMateriaTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'olimpiada' => $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($this->session->get('idOlimpiada')),
        ));
    }

    /**
     * Finds and displays a OlimMateriaTipo entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimMateriaTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieOlimpiadasBundle:OlimMateriaTipo:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing OlimMateriaTipo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimMateriaTipo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        $this->session->set('materiaId', $id);

        return $this->render('SieOlimpiadasBundle:OlimMateriaTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'olimpiada' => $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($this->session->get('idOlimpiada')),
        ));
    }

    /**
    * Creates a form to edit a OlimMateriaTipo entity.
    *
    * @param OlimMateriaTipo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(OlimMateriaTipo $entity)
    {
        $form = $this->createForm(new OlimMateriaTipoType(), $entity, array(
            'action' => $this->generateUrl('olimmateriatipo_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        // $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing OlimMateriaTipo entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimMateriaTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('olimmateriatipo_edit', array('id' => $id)));
        }

        return $this->render('SieOlimpiadasBundle:OlimMateriaTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a OlimMateriaTipo entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OlimMateriaTipo entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('olimmateriatipo'));
    }

    /**
     * Creates a form to delete a OlimMateriaTipo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('olimmateriatipo_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Funciones AJAX para reglas
     */
    
    public function getReglas(){

    }
    
    public function createReglaAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $materia = $em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('materiaId'));

        $arrayPri = array();
        $arraySec = array();
        if($request->get('id')){
            $entity = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->find($request->get('id'));
            $primariaSel = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
                'nivelTipo'=>2
            ));
            foreach ($primariaSel as $ps) {
                $arrayPri[] = $ps->getGradoTipo()->getId(); 
            }
            $secundariaSel = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
                'nivelTipo'=>3
            ));
            foreach ($secundariaSel as $ss) {
                $arraySec[] = $ss->getGradoTipo()->getId(); 
            }
        }else{
            $entity = new OlimReglasOlimpiadasTipo();
        }

        // Niveles
        $niveles = $em->getRepository('SieAppWebBundle:NivelTipo')->findBy(array('id'=>array(2,3)));
        $grados = $em->getRepository('SieAppWebBundle:GradoTipo')->findBy(array('id'=>array(1,2,3,4,5,6)));

        $primaria = array();
        $secundaria = array();
        foreach ($grados as $g) {
            $primaria[$g->getId()] = $g->getGrado();
            $secundaria[$g->getId()] = $g->getGrado();
        }

        $form = $this->createForm(new OlimReglasOlimpiadasTipoType, $entity, array(
            'categoriaId'=>1,
            'categoria'=>'holaadsf',
            'primaria'=>$primaria,
            'secundaria'=>$secundaria,
            'primariaSelected'=>$arrayPri,
            'secundariaSelected'=>$arraySec
        ));
        return $this->render('SieOlimpiadasBundle:OlimMateriaTipo:reglaModal.html.twig', array(
            'form'=>$form->createView()
        ));
    }

    public function getReglasAction(){
        $reglas = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->findBy(array('olimMateriaTipo'=>$this->session->get('materiaId')));
        $array = array();
        if(count($reglas)>0){
            foreach ($reglas as $re) {
                if($re->getOlimCategoriaTipo()){
                    $categoriaId = $re->getOlimCategoriaTipo()->getId();
                    $categoria = $re->getOlimCategoriaTipo()->getCategoria();
                }else{
                    $categoriaId = '';
                    $categoria = '';
                }

                $arrayGrados = array();
                for($i = 2; $i <= 3; $i++){
                    for($j = 1; $j <= 6; $j++){
                        $grado = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
                            'olimReglasOlimpiadasTipo'=>$re->getId(),
                            'nivelTipo'=>$i,
                            'gradoTipo'=>$j
                        ));

                        if($grado){
                            $arrayGrados[] = true;
                        }else{
                            $arrayGrados[] = false;
                        }
                    }
                }

                $array[] = array(
                    'id'=>$re->getId(),
                    'categoriaId'=>$categoriaId,
                    'categoria'=>$categoria,
                    'modalidad'=>($re->getModalidadParticipacionTipo())?$re->getModalidadParticipacionTipo()->getId():'',
                    'modalidadNombre'=>($re->getModalidadParticipacionTipo())?$re->getModalidadParticipacionTipo()->getModalidad():'',
                    'numeroIntegrantes'=>($re->getModalidadNumeroIntegrantesTipo())?$re->getModalidadNumeroIntegrantesTipo()->getId():'',
                    'numeroIntegrantesNombre'=>($re->getModalidadNumeroIntegrantesTipo())?$re->getModalidadNumeroIntegrantesTipo()->getCondicion():'',
                    'cantidadEquipos'=>$re->getCantidadEquipos(),
                    'cantidadInscrito'=>$re->getCantidadInscritosGrado(),
                    'edadInicial'=>$re->getEdadInicial(),
                    'edadFinal'=>$re->getEdadFinal(),
                    'fechaComparacion'=>($re->getFechaComparacion())?$re->getFechaComparacion()->format('d-m-Y'):'',
                    'documento'=>$re->getSiSubirDocumento(),
                    'siNombreEquipo'=>$re->getSiNombreEquipo(),
                    'siNombreProyecto'=>$re->getSiNombreProyecto(),
                    'gestion'=>$re->getGestionTipoId(),
                    'periodo'=>$re->getPeriodoTipoId(),
                    'primaria1'=>$arrayGrados[0],
                    'primaria2'=>$arrayGrados[1],
                    'primaria3'=>$arrayGrados[2],
                    'primaria4'=>$arrayGrados[3],
                    'primaria5'=>$arrayGrados[4],
                    'primaria6'=>$arrayGrados[5],
                    'secundaria1'=>$arrayGrados[6],
                    'secundaria2'=>$arrayGrados[7],
                    'secundaria3'=>$arrayGrados[8],
                    'secundaria4'=>$arrayGrados[9],
                    'secundaria5'=>$arrayGrados[10],
                    'secundaria6'=>$arrayGrados[11]

                );
            }
        }

        $response = new JsonResponse();

        return $response->setData($array);
    }

}
