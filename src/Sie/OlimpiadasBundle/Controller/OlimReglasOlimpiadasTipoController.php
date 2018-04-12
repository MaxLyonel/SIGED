<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\OlimReglasOlimpiadasTipo;
use Sie\AppWebBundle\Entity\OlimReglasOlimpiadasNivelGradoTipo;
use Sie\AppWebBundle\Form\OlimReglasOlimpiadasTipoType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * OlimReglasOlimpiadasTipo controller.
 *
 */
class OlimReglasOlimpiadasTipoController extends Controller
{

    private $session;

    public function __construct(){
        $this->session = new Session();
    }

    /**
     * Lists all OlimReglasOlimpiadasTipo entities.
     *
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $idMateria = $request->get('idMateria');

        if($idMateria){
            $this->session->set('idMateria', $idMateria);
        }

        $entities = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->findBy(array(
            'olimMateriaTipo' => $this->session->get('idMateria')
        ),array('id'=>'ASC'));

        $arrayPrimaria = array();
        $array = array();

        foreach ($entities as $en) {
            $primaria = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
                'olimReglasOlimpiadasTipo'=>$en->getId(),
                'nivelTipo'=>12
            ));
            $arrayPrimaria = array();
            foreach ($primaria as $p) {
                $arrayPrimaria[] = $p;
            }

            $secundaria = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
                'olimReglasOlimpiadasTipo'=>$en->getId(),
                'nivelTipo'=>13
            ));
            $arraySecundaria = array();
            foreach ($secundaria as $s) {
                $arraySecundaria[] = $s;
            }

            $array[] = array(
                'entidad'=>$en,
                'primaria'=>$arrayPrimaria,
                'secundaria'=>$arraySecundaria,
            );
        }

        $entities = $array;

        return $this->render('SieOlimpiadasBundle:OlimReglasOlimpiadasTipo:index.html.twig', array(
            'entities' => $entities,
            'olimpiada' => $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($this->session->get('idOlimpiada')),
            'materia' => $em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria'))
        ));
    }
    /**
     * Creates a new OlimReglasOlimpiadasTipo entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new OlimReglasOlimpiadasTipo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_reglas_olimpiadas_tipo');")->execute();
            $entity->setOlimMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria')));
            $entity->setFechaRegistro(new \DateTime('now'));
            $entity->setUsuarioRegistroId($this->session->get('userId'));
            $em->persist($entity);
            $em->flush();

            /**
             * Registro de niveles
             */
            $this->createNiveles($entity, $request);
            

            return $this->redirect($this->generateUrl('olimreglasolimpiadastipo'));
        }

        return $this->render('SieOlimpiadasBundle:OlimReglasOlimpiadasTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'olimpiada' => $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($this->session->get('idOlimpiada')),
            'materia' => $em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria'))
        ));
    }

    /**
     * Creates a form to create a OlimReglasOlimpiadasTipo entity.
     *
     * @param OlimReglasOlimpiadasTipo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(OlimReglasOlimpiadasTipo $entity)
    {
        $form = $this->createForm(new OlimReglasOlimpiadasTipoType(), $entity, array(
            'action' => $this->generateUrl('olimreglasolimpiadastipo_create'),
            'method' => 'POST',
            'niveles' => $this->niveles()
        ));

        return $form;
    }

    /**
     * Displays a form to create a new OlimReglasOlimpiadasTipo entity.
     *
     */
    public function newAction()
    {
        $entity = new OlimReglasOlimpiadasTipo();
        $form   = $this->createCreateForm($entity);
        $em = $this->getDoctrine()->getManager();

        return $this->render('SieOlimpiadasBundle:OlimReglasOlimpiadasTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'olimpiada' => $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($this->session->get('idOlimpiada')),
            'materia' => $em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria'))
        ));
    }

    /**
     * Finds and displays a OlimReglasOlimpiadasTipo entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimReglasOlimpiadasTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieOlimpiadasBundle:OlimReglasOlimpiadasTipo:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing OlimReglasOlimpiadasTipo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimReglasOlimpiadasTipo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieOlimpiadasBundle:OlimReglasOlimpiadasTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'olimpiada' => $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($this->session->get('idOlimpiada')),
            'materia' => $em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria'))
        ));
    }

    /**
    * Creates a form to edit a OlimReglasOlimpiadasTipo entity.
    *
    * @param OlimReglasOlimpiadasTipo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(OlimReglasOlimpiadasTipo $entity)
    {
        $form = $this->createForm(new OlimReglasOlimpiadasTipoType(), $entity, array(
            'action' => $this->generateUrl('olimreglasolimpiadastipo_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'niveles' => $this->niveles($entity->getId())
        ));

        return $form;
    }
    /**
     * Edits an existing OlimReglasOlimpiadasTipo entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimReglasOlimpiadasTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $entity->setFechaModificacion(new \DateTime('now'));
            $entity->setUsuarioModificacionId($this->session->get('userId'));
            $em->flush();

            $this->createNiveles($entity, $request);

            return $this->redirect($this->generateUrl('olimreglasolimpiadastipo'));
        }

        return $this->render('SieOlimpiadasBundle:OlimReglasOlimpiadasTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a OlimReglasOlimpiadasTipo entity.
     *
     */
    public function deleteAction(Request $request)
    {
            $id = $request->get('id');
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OlimReglasOlimpiadasTipo entity.');
            }

            $em->remove($entity);
            $em->flush();
        

        return $this->redirect($this->generateUrl('olimreglasolimpiadastipo'));
    }

    /**
     * Creates a form to delete a OlimReglasOlimpiadasTipo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('olimreglasolimpiadastipo_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    public function niveles($categoria = null){
        
        if($categoria != null){
            $em = $this->getDoctrine()->getManager();
            for ($i=1; $i <=6 ; $i++) { 
                $primaria = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
                    'olimReglasOlimpiadasTipo'=>$categoria,
                    'nivelTipo'=>12,
                    'gradoTipo'=>$i
                ));
                if($primaria){
                    $array[] = true;
                }else{
                    $array[] = false;
                }
            }
            for ($i=1; $i <=6 ; $i++) { 
                $secundaria = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
                    'olimReglasOlimpiadasTipo'=>$categoria,
                    'nivelTipo'=>13,
                    'gradoTipo'=>$i
                ));
                if($secundaria){
                    $array[] = true;
                }else{
                    $array[] = false;
                }
            }
        }else{
            for ($i=0; $i <=11 ; $i++) { 
                $array[] = false;
            }
        }

        return $array;
    }

    private function createNiveles($entity, $request){
        $em = $this->getDoctrine()->getManager();

        $niveles = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
            'olimReglasOlimpiadasTipo'=>$entity->getId()
        ));
        foreach ($niveles as $n) {
            $em->remove($n);
            $em->flush();
        }
        if(isset($request->get('sie_appwebbundle_olimreglasolimpiadastipo')['pri1'])){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_reglas_olimpiadas_nivel_grado_tipo');")->execute();
            $nivelGrado = new OlimReglasOlimpiadasNivelGradoTipo();
            $nivelGrado->setOlimReglasOlimpiadasTipo($entity);
            $nivelGrado->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(12));
            $nivelGrado->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(1));
            $nivelGrado->setEsVigente(true);
            $nivelGrado->setOlimMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria')));
            $em->persist($nivelGrado);
            $em->flush();
        }
        if(isset($request->get('sie_appwebbundle_olimreglasolimpiadastipo')['pri2'])){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_reglas_olimpiadas_nivel_grado_tipo');")->execute();
            $nivelGrado = new OlimReglasOlimpiadasNivelGradoTipo();
            $nivelGrado->setOlimReglasOlimpiadasTipo($entity);
            $nivelGrado->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(12));
            $nivelGrado->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(2));
            $nivelGrado->setEsVigente(true);
            $nivelGrado->setOlimMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria')));
            $em->persist($nivelGrado);
            $em->flush();
        }
        if(isset($request->get('sie_appwebbundle_olimreglasolimpiadastipo')['pri3'])){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_reglas_olimpiadas_nivel_grado_tipo');")->execute();
            $nivelGrado = new OlimReglasOlimpiadasNivelGradoTipo();
            $nivelGrado->setOlimReglasOlimpiadasTipo($entity);
            $nivelGrado->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(12));
            $nivelGrado->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(3));
            $nivelGrado->setEsVigente(true);
            $nivelGrado->setOlimMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria')));
            $em->persist($nivelGrado);
            $em->flush();
        }
        if(isset($request->get('sie_appwebbundle_olimreglasolimpiadastipo')['pri4'])){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_reglas_olimpiadas_nivel_grado_tipo');")->execute();
            $nivelGrado = new OlimReglasOlimpiadasNivelGradoTipo();
            $nivelGrado->setOlimReglasOlimpiadasTipo($entity);
            $nivelGrado->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(12));
            $nivelGrado->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(4));
            $nivelGrado->setEsVigente(true);
            $nivelGrado->setOlimMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria')));
            $em->persist($nivelGrado);
            $em->flush();
        }
        if(isset($request->get('sie_appwebbundle_olimreglasolimpiadastipo')['pri5'])){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_reglas_olimpiadas_nivel_grado_tipo');")->execute();
            $nivelGrado = new OlimReglasOlimpiadasNivelGradoTipo();
            $nivelGrado->setOlimReglasOlimpiadasTipo($entity);
            $nivelGrado->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(12));
            $nivelGrado->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(5));
            $nivelGrado->setEsVigente(true);
            $nivelGrado->setOlimMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria')));
            $em->persist($nivelGrado);
            $em->flush();
        }
        if(isset($request->get('sie_appwebbundle_olimreglasolimpiadastipo')['pri6'])){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_reglas_olimpiadas_nivel_grado_tipo');")->execute();
            $nivelGrado = new OlimReglasOlimpiadasNivelGradoTipo();
            $nivelGrado->setOlimReglasOlimpiadasTipo($entity);
            $nivelGrado->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(12));
            $nivelGrado->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(6));
            $nivelGrado->setEsVigente(true);
            $nivelGrado->setOlimMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria')));
            $em->persist($nivelGrado);
            $em->flush();
        }

        if(isset($request->get('sie_appwebbundle_olimreglasolimpiadastipo')['sec1'])){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_reglas_olimpiadas_nivel_grado_tipo');")->execute();
            $nivelGrado = new OlimReglasOlimpiadasNivelGradoTipo();
            $nivelGrado->setOlimReglasOlimpiadasTipo($entity);
            $nivelGrado->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(13));
            $nivelGrado->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(1));
            $nivelGrado->setEsVigente(true);
            $nivelGrado->setOlimMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria')));
            $em->persist($nivelGrado);
            $em->flush();
        }
        if(isset($request->get('sie_appwebbundle_olimreglasolimpiadastipo')['sec2'])){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_reglas_olimpiadas_nivel_grado_tipo');")->execute();
            $nivelGrado = new OlimReglasOlimpiadasNivelGradoTipo();
            $nivelGrado->setOlimReglasOlimpiadasTipo($entity);
            $nivelGrado->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(13));
            $nivelGrado->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(2));
            $nivelGrado->setEsVigente(true);
            $nivelGrado->setOlimMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria')));
            $em->persist($nivelGrado);
            $em->flush();
        }
        if(isset($request->get('sie_appwebbundle_olimreglasolimpiadastipo')['sec3'])){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_reglas_olimpiadas_nivel_grado_tipo');")->execute();
            $nivelGrado = new OlimReglasOlimpiadasNivelGradoTipo();
            $nivelGrado->setOlimReglasOlimpiadasTipo($entity);
            $nivelGrado->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(13));
            $nivelGrado->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(3));
            $nivelGrado->setEsVigente(true);
            $nivelGrado->setOlimMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria')));
            $em->persist($nivelGrado);
            $em->flush();
        }
        if(isset($request->get('sie_appwebbundle_olimreglasolimpiadastipo')['sec4'])){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_reglas_olimpiadas_nivel_grado_tipo');")->execute();
            $nivelGrado = new OlimReglasOlimpiadasNivelGradoTipo();
            $nivelGrado->setOlimReglasOlimpiadasTipo($entity);
            $nivelGrado->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(13));
            $nivelGrado->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(4));
            $nivelGrado->setEsVigente(true);
            $nivelGrado->setOlimMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria')));
            $em->persist($nivelGrado);
            $em->flush();
        }
        if(isset($request->get('sie_appwebbundle_olimreglasolimpiadastipo')['sec5'])){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_reglas_olimpiadas_nivel_grado_tipo');")->execute();
            $nivelGrado = new OlimReglasOlimpiadasNivelGradoTipo();
            $nivelGrado->setOlimReglasOlimpiadasTipo($entity);
            $nivelGrado->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(13));
            $nivelGrado->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(5));
            $nivelGrado->setEsVigente(true);
            $nivelGrado->setOlimMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria')));
            $em->persist($nivelGrado);
            $em->flush();
        }
        if(isset($request->get('sie_appwebbundle_olimreglasolimpiadastipo')['sec6'])){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_reglas_olimpiadas_nivel_grado_tipo');")->execute();
            $nivelGrado = new OlimReglasOlimpiadasNivelGradoTipo();
            $nivelGrado->setOlimReglasOlimpiadasTipo($entity);
            $nivelGrado->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(13));
            $nivelGrado->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(6));
            $nivelGrado->setEsVigente(true);
            $nivelGrado->setOlimMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria')));
            $em->persist($nivelGrado);
            $em->flush();
        }

    }
}
