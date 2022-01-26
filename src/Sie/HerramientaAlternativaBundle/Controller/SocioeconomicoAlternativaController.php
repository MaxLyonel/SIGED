<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\SocioeconomicoAlternativa;
use Sie\AppWebBundle\Form\SocioeconomicoAlternativaType;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\Estudiante;

/**
 * SocioeconomicoAlternativa controller.
 *
 */
class SocioeconomicoAlternativaController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');

        $aInfoUeducativa = unserialize($infoUe);
        $aInfoStudent = json_decode($infoStudent, TRUE);

        $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfoId']['iecId']);
        $ieducativaId = $iec->getInstitucioneducativa()->getId();
        $gestion = $iec->getGestionTipo()->getId();

        $idInscripcion = $aInfoStudent['eInsId'];
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

        $estudiante = array(
            'codigoRude' => $aInfoStudent['codigoRude'],
            'estudiante' => $aInfoStudent['nombre'] . ' ' . $aInfoStudent['paterno'] . ' ' . $aInfoStudent['materno'],
            'estadoMatricula' => $inscripcion->getEstadomatriculaTipo()->getEstadomatricula()
        );

        //Información de la institución educativa
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

        $query = $repository->createQueryBuilder('i')
                ->select('i.id ieducativaId, i.institucioneducativa ieducativa, d.id distritoId, d.distrito distrito, dp.id departamentoId, dp.departamento departamento, de.dependencia dependencia, jg.cordx cordx, jg.cordy cordy, st.id sucId')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'isuc', 'WITH', 'isuc.institucioneducativa = i.id')
                ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'jg', 'WITH', 'i.leJuridicciongeografica = jg.id')
                ->innerJoin('SieAppWebBundle:DistritoTipo', 'd', 'WITH', 'jg.distritoTipo = d.id')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dp', 'WITH', 'd.departamentoTipo = dp.id')
                ->innerJoin('SieAppWebBundle:DependenciaTipo', 'de', 'WITH', 'i.dependenciaTipo = de.id')
                ->innerJoin('SieAppWebBundle:SucursalTipo', 'st', 'WITH', 'isuc.sucursalTipo = st.id')
                ->where('i.id = :ieducativa')
                ->andWhere('isuc.id = :sucursal')
                ->setParameter('ieducativa', $ieducativaId)
                ->setParameter('sucursal', $this->session->get('ie_suc_id'))
                ->getQuery();

        $institucion = $query->getOneOrNullResult();

        //Información de la/el estudiante
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $aInfoStudent['codigoRude']));

        //Datos Socioeconómicos
        $socioeconomico = $em->getRepository('SieAppWebBundle:SocioeconomicoAlternativa')->findOneBy(array('estudianteInscripcion' => $aInfoStudent['eInsId']));

        if ($socioeconomico) {

            return $this->render('SieHerramientaAlternativaBundle:SocioeconomicoAlternativa:edit.html.twig', array(
                        'socioeconomico' => $socioeconomico,
                        'institucion' => $institucion,
                        'estudiante' => $estudiante,
                        'student' => $student,
                        'form' => $this->editForm($idInscripcion, $gestion, $socioeconomico)->createView(),
            ));
        } else {

            return $this->render('SieHerramientaAlternativaBundle:SocioeconomicoAlternativa:new.html.twig', array(
                        'socioeconomico' => $socioeconomico,
                        'institucion' => $institucion,
                        'estudiante' => $estudiante,
                        'student' => $student,
                        'form' => $this->newForm($idInscripcion, $gestion)->createView(),
            ));
        }
    }

    /*
     * formulario de nuevo maestro
     */

    private function newForm($idInscripcion, $gestion) {
        $em = $this->getDoctrine()->getManager();

        $atenmedica = $em->getRepository('SieAppWebBundle:AtencionmedicaTipo')->findAll();
        
        $atenmedicaArray = array();
        foreach ($atenmedica as $a) {
            $atenmedicaArray[$a->getIdAtencionmedica()] = $a->getDescAtencionmedica();
        }
        
        $sangreTipo = $em->getRepository('SieAppWebBundle:SangreTipo')->findAll();
        
        $sangreTipoArray = array();
        foreach ($sangreTipo as $s) {
            $sangreTipoArray[$s->getId()] = $s->getGrupoSanguineo();
        }
        
        $discapacidadTipo = $em->getRepository('SieAppWebBundle:DiscapacidadTipo')->findAll();
        
        $discapacidadTipoArray = array();
        foreach ($discapacidadTipo as $d) {
            $discapacidadTipoArray[$d->getId()] = $d->getOrigendiscapacidad();
        }
        
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_info_maestro_create'))
                ->add('estudianteInscripcion', 'hidden', array('data' => $idInscripcion))
                ->add('gestionId', 'hidden', array('data' => $gestion))
                //->add('atenmedicaTipoId', 'choice', array('label' => 'Atención Médica', 'required' => true, 'choices' => $atenmedicaArray, 'attr' => array('class' => 'form-control')))
                //->add('sangreTipoId', 'choice', array('label' => 'Tipo de Sangre', 'required' => true, 'choices' => $sangreTipoArray, 'attr' => array('class' => 'form-control')))
                //->add('dicapacidadId', 'choice', array('label' => 'Discapacidad', 'required' => true, 'choices' => $discapacidadTipoArray, 'attr' => array('class' => 'form-control')))
                ->add('direccionZona', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
                ->add('direccionCalle', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
                ->add('direccionNro', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
                ->add('direccionTelefono', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
                ->add('direccionCelular', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
                ->add('nroHijos', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
                //->add('seguro')
                //->add('empleo')
//                ->add('motivoInterrupcion')
//                ->add('aniosInterrupcion')
//                ->add('ultimoCurso')
//                ->add('estadoCivilId')
//                ->add('modalidadId')
//                ->add('usuarioId')
//                ->add('fechaLastUpdate')
//                ->add('etniaTipo')
//                ->add('idiomaTipo')
//                ->add('idiomaTipo2')
//                ->add('idiomaTipo3')
//                ->add('idiomaTipo4')
//                ->add('idiomaTipo5')
//                ->add('idiomaTipo6')
                /* ----------------------------------------------                
                  ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                  ->add('gestion', 'hidden', array('data' => $gestion))
                  ->add('persona', 'hidden', array('data' => $idPersona))
                  ->add('funcion', 'choice', array('label' => 'Función que desempeña (cargo)', 'required' => true, 'choices' => $cargosArray, 'attr' => array('class' => 'form-control')))
                  ->add('financiamiento', 'choice', array('label' => 'Fuente de Financiamiento', 'required' => true, 'choices' => $financiamientoArray, 'attr' => array('class' => 'form-control')))
                  ->add('formacion', 'choice', array('label' => 'Último grado de formación alcanzado', 'required' => true, 'choices' => $formacionArray, 'attr' => array('class' => 'form-control')))
                  ->add('formacionDescripcion', 'text', array('label' => 'Descripción del último grado de formación alcanzado', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '90')))
                  ->add('normalista', 'checkbox', array('required' => false, 'label' => 'Normalista', 'attr' => array('class' => 'checkbox')))
                  ->add('item', 'text', array('label' => 'Número de Item', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'pattern' => '[0-9]{1,10}')))
                  ->add('idiomaOriginario', 'entity', array('class' => 'SieAppWebBundle:IdiomaMaterno', 'data' => $em->getReference('SieAppWebBundle:IdiomaMaterno', 97), 'label' => 'Actualmente que idioma originario esta estudiando', 'required' => false, 'attr' => array('class' => 'form-control')))
                  ->add('leeEscribeBraile', 'checkbox', array('required' => false, 'label' => 'Lee y Escribe en Braille', 'attr' => array('class' => 'checkbox')))
                  --------------------------------------------- */
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary', 'disabled' => 'disabled')))
                ->getForm();

        return $form;
    }

    /**
     * Obtiene el formulario para realizar la modificación
     * @param Request $request
     */
    public function resultAction(Request $request) {

        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');

        $aInfoUeducativa = unserialize($infoUe);
        $aInfoStudent = json_decode($infoStudent, TRUE);


        $em = $this->getDoctrine()->getManager();


        //Información de la institución educativa
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

        $query = $repository->createQueryBuilder('i')
                ->select('i.id ieducativaId, i.institucioneducativa ieducativa, d.id distritoId, d.distrito distrito, dp.id departamentoId, dp.departamento departamento, de.dependencia dependencia, jg.cordx cordx, jg.cordy cordy')
                ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'jg', 'WITH', 'i.leJuridicciongeografica = jg.id')
                ->innerJoin('SieAppWebBundle:DistritoTipo', 'd', 'WITH', 'jg.distritoTipo = d.id')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dp', 'WITH', 'd.departamentoTipo = dp.id')
                ->innerJoin('SieAppWebBundle:DependenciaTipo', 'de', 'WITH', 'i.dependenciaTipo = de.id')
                ->where('i.id = :ieducativa')
                ->setParameter('ieducativa', $sie)
                ->getQuery();

        $institucion = $query->getOneOrNullResult();

        //Información de la/el estudiante
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));

        //Verifica si el estudiente existe
        if ($student) {

            //Verifica si la UE existe
            if ($institucion) {

                //Información de inscripción de la/el estudiante
                $repository = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');

                $query = $repository->createQueryBuilder('i')
                        ->select('i.id insId, nt.nivel nivel, gt.grado grado, pt.paralelo paralelo, tt.turno turno')
                        ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'i.estudiante = e.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'ic', 'WITH', 'i.institucioneducativaCurso = ic.id')
                        ->innerJoin('SieAppWebBundle:NivelTipo', 'nt', 'WITH', 'ic.nivelTipo = nt.id')
                        ->innerJoin('SieAppWebBundle:GradoTipo', 'gt', 'WITH', 'ic.gradoTipo = gt.id')
                        ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'ic.paraleloTipo = pt.id')
                        ->innerJoin('SieAppWebBundle:TurnoTipo', 'tt', 'WITH', 'ic.turnoTipo = tt.id')
                        ->where('ic.institucioneducativa = :ieducativa')
                        ->andWhere('e.codigoRude = :rude')
                        ->andWhere('ic.gestionTipo = :gestion')
                        ->setParameter('ieducativa', $sie)
                        ->setParameter('rude', $rude)
                        ->setParameter('gestion', $gestion)
                        ->getQuery();

                $inscription = $query->getOneOrNullResult();

                //Verifica si el estudiante cuenta con inscripción en la UE y n la gestión ingresada en el formulario de búsqueda
                if ($inscription) {

                    $socioeconomico = $em->getRepository('SieAppWebBundle:SocioeconomicoAlternativa')->findOneBy(array('estudianteInscripcion' => $inscription['insId']));

                    if ($socioeconomico) {

                        //Si cuenta con datos socioeconómicos en la gestión se actualiza la información de los datos socioeconómicos
                        $idEstudiante = $student->getId();

                        $repository = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion');

                        $query = $repository->createQueryBuilder('apo')
                                ->where('apo.estudianteInscripcion = :idInscripcion')
                                ->andWhere('apo.esValidado > :validado')
                                ->setParameter('idInscripcion', $inscription['insId'])
                                ->setParameter('validado', 'f')
                                ->getQuery();

                        $apoderados = $query->getResult();

                        $id = $socioeconomico->getId();

                        $em = $this->getDoctrine()->getManager();

                        $entity = $em->getRepository('SieAppWebBundle:SocioeconomicoAlternativa')->find($id);

                        if (!$entity) {
                            throw $this->createNotFoundException('Unable to find SocioeconomicoAlternativa entity.');
                        }

                        $editForm = $this->createEditForm($entity);

                        return $this->render('SieAppWebBundle:SocioeconomicoAlternativa:edit.html.twig', array(
                                    'entity' => $entity,
                                    'institucion' => $institucion,
                                    'student' => $student,
                                    'inscription' => $inscription,
                                    'edit_form' => $editForm->createView(),
                                    'sie' => $sie,
                                    'rude' => $rude,
                                    'gestion' => $gestion,
                                    'apoderados' => $apoderados,
                        ));
                    } else {

                        //Si no cuenta con datos socioeconómicos en la gestión se crea un nuevo registro en datos socioeconómicos
//                        $entity = new SocioeconomicoAlternativa();
//
//                        $form = $this->createCreateForm($entity);
//
//                        return $this->render('SieAppWebBundle:SocioeconomicoAlternativa:new.html.twig', array(
//                                    'entity' => $entity,
//                                    'institucion' => $institucion,
//                                    'student' => $student,
//                                    'inscription' => $inscription,
//                                    'form' => $form->createView(),
//                                    'sie' => $sie,
//                                    'rude' => $rude,
//                                    'gestion' => $gestion,
//                        ));

                        $message = "Estudiante con RUDEAL " . $rude . " no cuenta con datos socioeconómicos para la gestión " . $gestion;
                        $this->addFlash('notiext', $message);
                        return $this->redirectToRoute('socioeconomicoalt');
                    }
                } else {
                    $message = "Estudiante con RUDEAL " . $rude . " no cuenta con inscripción en la UE " . $sie . " para la gestión " . $gestion;
                    $this->addFlash('notiext', $message);
                    return $this->redirectToRoute('socioeconomicoalt');
                }
            } else {
                $message = "Institución Educativa " . $sie . " no se encuentra registrada";
                $this->addFlash('notiext', $message);
                return $this->redirectToRoute('socioeconomicoalt');
            }
        } else {
            $message = "Estudiante con RUDEAL " . $rude . " no se encuentra registrado";
            $this->addFlash('notiext', $message);
            return $this->redirectToRoute('socioeconomicoregular');
        }
    }

    /**
     * Creates a new SocioeconomicoAlternativa entity.
     *
     */
    public function createAction(Request $request) {
        $entity = new SocioeconomicoAlternativa();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('socioeconomicoregular_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:SocioeconomicoAlternativa:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a SocioeconomicoAlternativa entity.
     *
     * @param SocioeconomicoAlternativa $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(SocioeconomicoAlternativa $entity) {
        $form = $this->createForm(new SocioeconomicoAlternativaType(), $entity, array(
            'action' => $this->generateUrl('socioeconomicoregular_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new SocioeconomicoAlternativa entity.
     *
     */
    public function newAction() {
        $entity = new SocioeconomicoAlternativa();
        $form = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:SocioeconomicoAlternativa:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a SocioeconomicoAlternativa entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:SocioeconomicoAlternativa')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SocioeconomicoAlternativa entity.');
        }

        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:SocioeconomicoAlternativa:show.html.twig', array(
                    'entity' => $entity,
                        //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing SocioeconomicoAlternativa entity.
     *
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:SocioeconomicoAlternativa')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SocioeconomicoAlternativa entity.');
        }

        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:SocioeconomicoAlternativa:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                        //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a SocioeconomicoAlternativa entity.
     *
     * @param SocioeconomicoAlternativa $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(SocioeconomicoAlternativa $entity) {
        $form = $this->createForm(new SocioeconomicoAlternativaType(), $entity, array(
            'action' => $this->generateUrl('socioeconomicoregular_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar información'));

        return $form;
    }

    /**
     * Edits an existing SocioeconomicoAlternativa entity.
     *
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:SocioeconomicoAlternativa')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SocioeconomicoAlternativa entity.');
        }

        //$deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $message = "Se ha hactualizado correctamente la información ingresada en el formulario RUDE";
            $this->addFlash('goodext', $message);
            return $this->redirectToRoute('socioeconomicoregular');
            //return $this->redirect($this->generateUrl('socioeconomicoregular_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:SocioeconomicoAlternativa:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                        //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a SocioeconomicoAlternativa entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:SocioeconomicoAlternativa')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find SocioeconomicoAlternativa entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('socioeconomicoregular'));
    }

    /**
     * Creates a form to delete a SocioeconomicoAlternativa entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('socioeconomicoregular_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

}
