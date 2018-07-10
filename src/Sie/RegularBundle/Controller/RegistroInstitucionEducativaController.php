<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\InstitucioneducativaSucursal;
use Sie\AppWebBundle\Entity\JurisdiccionGeografica;
use Sie\AppWebBundle\Form\InstitucioneducativaType;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\EspecialAreaTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaAreaEspecialAutorizado;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InstitucioneducativaNivelAutorizado;
use Sie\AppWebBundle\Entity\BonojuancitoInstitucioneducativa;

/**
 * Institucioneducativa controller.
 *
 */
class RegistroInstitucionEducativaController extends Controller {
    public $session;

    public function __construct(){
        $this->session = new Session();
    }

    /**
     * Lists all Institucioneducativa entities.
     *
     */
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $id_lugar = $sesion->get('roluserlugarid');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // data es un array con claves 'name', 'email', y 'message'

        /* return $this->render('SieAppWebBundle:Institucioneducativa:searchieducativa.html.twig', array(
          'form' => $this->createSearchForm()->createView(),
          )); */

        // Creacion de formularios de busqueda por codigo rue o nombre de institucion educativa
        $formInstitucioneducativa = $this->createSearchFormInstitucioneducativa();
        $formInstitucioneducativaId = $this->createSearchFormInstitucioneducativaId();


        return $this->render('SieRegularBundle:RegistroInstitucionEducativa:search.html.twig', array(
                'formInstitucioneducativa' => $formInstitucioneducativa->createView(),
                'formInstitucioneducativaId' => $formInstitucioneducativaId->createView(),
                'gestion' => $this->session->get('currentyear'),
                'roluserlugarid' => $id_lugar
        ));
    }

    /**
     * Creates a form to search the users of student selected
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchFormInstitucioneducativa() {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('bjp_rue_result'))
                ->add('tipo_search', 'hidden', array('data' => 'institucioneducativa'))
                ->add('institucioneducativa', 'text', array('required' => true, 'invalid_message' => 'Campo obligatorio'))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    private function createSearchFormInstitucioneducativaId() {

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('bjp_rue_result'))
                ->add('tipo_search', 'hidden', array('data' => 'institucioneducativaId'))
                ->add('institucioneducativaId', 'text', array('required' => true, 'invalid_message' => 'Campo obligatorio', 'attr' => array('autocomplete' => 'on', 'maxlength' => 9)))
                ->add('buscarId', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * Find the institucion educativa.
     *
     */
    public function findieducativaAction(Request $request) {
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT iat FROM SieAppWebBundle:InstitucioneducativaAcreditacionTipo iat WHERE iat.id = :id')
        ->setParameter('id', 0);
        $tiposie = $query->getResult();

        if ($form['tipo_search'] == 'institucioneducativa') {
            $query = $em->createQuery(
                            'SELECT ie
                FROM SieAppWebBundle:Institucioneducativa ie
                WHERE UPPER(ie.institucioneducativa) like :id
                AND ie.institucioneducativaAcreditacionTipo in (:tiposacred)
                ORDER BY ie.id')
                    ->setParameter('id', '%' . strtoupper($form['institucioneducativa']) . '%')
                    ->setParameter('tiposacred', $tiposie);
        } else {
            $query = $em->createQuery(
                            'SELECT ie
                FROM SieAppWebBundle:Institucioneducativa ie
                WHERE ie.id = :id
                AND ie.institucioneducativaAcreditacionTipo in (:tiposacred)
                ORDER BY ie.id')
                    ->setParameter('id', $form['institucioneducativaId'])
                    ->setParameter('tiposacred', $tiposie);
        }

        $entities = $query->getResult();


        if (!$entities) {
            $this->get('session')->getFlashBag()->add('mensaje', 'No se encontró la información...');

            $formInstitucioneducativa = $this->createSearchFormInstitucioneducativa();
            $formInstitucioneducativaId = $this->createSearchFormInstitucioneducativaId();
            return $this->render('SieRegularBundle:RegistroInstitucionEducativa:search.html.twig', array(
                    'formInstitucioneducativa' => $formInstitucioneducativa->createView(), 
                    'formInstitucioneducativaId' => $formInstitucioneducativaId->createView(),
                    'gestion' => $this->session->get('currentyear'),
                    'roluserlugarid' => $this->session->get('roluserlugarid')
            ));
        }

        return $this->render('SieRegularBundle:RegistroInstitucionEducativa:resultinseducativa.html.twig', array(
                'entities' => $entities,
                'form' => $this->formGestionUE($entities[0])->createView(),
                'gestion' => $this->session->get('currentyear'),
                'roluserlugarid' => $this->session->get('roluserlugarid')
        ));
    }

    /**
    * create form to send GestionUesprocesoApertura to do the inscription
    *
    */
    private function formGestionUE($objInstitucion){
      $arrDataUe = array('sie'=>$objInstitucion->getId(), 'leJuridicciongeograficaId'=>$objInstitucion->getLeJuridicciongeografica()->getId());
      return $this->createFormBuilder()
                  ->setAction($this->generateUrl('gestionUesprocesoApertura_index'))
                  //->add('dataUe', 'text', array('data'=>json_encode($arrDataUe)))
                  ->add('open', 'submit', array('label'=>'Inscripción', 'attr'=>array('class'=>'btn btn-link')))
                  ->getForm();
    }

    /**
     * Displays a form to create a new Institucioneducativa entity.
     *
     */
    public function newAction(Request $request) {
        $institucioneducativaTipoId = $request->get('institucioneducativaTipoId');
        $request->getSession()->set('institucioneducativaTipoId', $institucioneducativaTipoId);

        $formulario = $this->createNewForm($institucioneducativaTipoId);
        return $this->render('SieRegularBundle:RegistroInstitucionEducativa:new.html.twig', array('form' => $formulario->createView()));
    }

    private function createNewForm($InstitucioneducativaTipoId) {
//     	$entity = new Institucioneducativa();
        $em = $this->getDoctrine()->getManager();

        if ($InstitucioneducativaTipoId == 1) {

            $query = $em->createQuery(
                            'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:NivelTipo tn
						WHERE tn.id in (:id)
						ORDER BY tn.id ASC'
                    )->setParameter('id', array(11, 12, 13));
            $niveles = $query->getResult();
            $nivelesArray = array();
            for ($i = 0; $i < count($niveles); $i++) {
                $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
            }
        } elseif ($InstitucioneducativaTipoId == 2) {
            $query = $em->createQuery(
                            'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:NivelTipo tn
						WHERE tn.id in (:id)
						ORDER BY tn.id ASC'
                    )->setParameter('id', array(201, 202, 203, 204, 205, 206, 207, 208, 211, 212, 213, 214, 215, 216, 217, 218));
            $niveles = $query->getResult();
            $nivelesArray = array();
            for ($i = 0; $i < count($niveles); $i++) {
                $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
            }
        } elseif ($InstitucioneducativaTipoId == 4) {

            $query = $em->createQuery(
                            'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:NivelTipo tn
						WHERE tn.id in (:id)
						ORDER BY tn.id ASC'
                    )->setParameter('id', array(11, 12, 13));
            $niveles = $query->getResult();
            $nivelesArray = array();
            for ($i = 0; $i < count($niveles); $i++) {
                $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
            }

            //carga araas de especial autorizadas

            $query = $em->createQuery(
                    'SELECT DISTINCT ta.id,ta.areaEspecial
                        FROM SieAppWebBundle:EspecialAreaTipo ta
                        ORDER BY ta.id');
            $areas = $query->getResult();
            $areasArray = array();
            for ($i = 0; $i < count($areas); $i++) {
                $areasArray[$areas[$i]['id']] = $areas[$i]['areaEspecial'];
            }
        }

        $query = $em->createQuery('SELECT iat FROM SieAppWebBundle:InstitucioneducativaAcreditacionTipo iat WHERE iat.id = :tipo')
                ->setParameter('tipo', 0);
        $tiposacreditacion = $query->getResult();
        $tiposacreditacionArray = array();
        foreach ($tiposacreditacion as $c) {
            $tiposacreditacionArray[$c->getId()] = $c->getInstitucioneducativaAcreditacion();
        }

        $query = $em->createQuery('SELECT jat FROM SieAppWebBundle:JurisdiccionGeograficaAcreditacionTipo jat WHERE jat.id <> :tipo')
                ->setParameter('tipo', 1);
        $tiposacreditacionle = $query->getResult();
        $tiposacreditacionleArray = array();
        foreach ($tiposacreditacionle as $c) {
            $tiposacreditacionleArray[$c->getId()] = $c->getJurisdiccionGeograficaAcreditacion();
        }


        $query = $em->createQuery('SELECT iet FROM SieAppWebBundle:InstitucioneducativaTipo iet WHERE iet.id = :idtie')
                ->setParameter('idtie', $InstitucioneducativaTipoId);
        $tiposie = $query->getResult();
        $tiposieArray = array();
        foreach ($tiposie as $c) {
            $tiposieArray[$c->getId()] = $c->getDescripcion();
        }

        $prov = array();
        $muni = array();
        $cantn = array();
        $locald = array();
        $distrt = array();

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('bjp_rue_create'))
                ->add('institucionEducativa', 'text', array('label' => 'Nombre Institución Educativa', 'required' => true, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
                ->add('telefono', 'text', array('label' => 'Nro. de Teléfono', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
                ->add('correo', 'text', array('label' => 'Correo Electrónico', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
                ->add('dependenciaTipo', 'entity', array('label' => 'Dependencia', 'required' => true, 'class' => 'SieAppWebBundle:DependenciaTipo', 'property' => 'dependencia', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('convenioTipo', 'entity', array('label' => 'Convenio', 'required' => true, 'class' => 'SieAppWebBundle:ConvenioTipo', 'property' => 'convenio', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('tipoAcreditacion', 'choice', array('label' => 'Acreditación Tipo', 'required' => true, 'choices' => $tiposacreditacionArray, 'attr' => array('class' => 'form-control')))
                ->add('tipoAcreditacionle', 'choice', array('label' => 'Acreditación Tipo', 'required' => true, 'choices' => $tiposacreditacionleArray, 'attr' => array('class' => 'form-control')))
                ->add('estadoInstitucionTipo', 'entity', array('label' => 'Estado', 'required' => true, 'class' => 'SieAppWebBundle:EstadoinstitucionTipo', 'property' => 'estadoinstitucion', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('institucionEducativaTipo', 'choice', array('label' => 'Tipo', 'disabled' => false, 'choices' => $tiposieArray, 'attr' => array('class' => 'form-control')))
                ->add('obsRue', 'text', array('label' => 'Observaciones', 'required' => false, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
                ->add('desUeAntes', 'text', array('label' => 'Nombre anterior', 'required' => false, 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
                ->add('departamento', 'entity', array('label' => 'Departamento', 'disabled' => false, 'required' => true, 'class' => 'SieAppWebBundle:DepartamentoTipo', 'property' => 'departamento', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarProvincias(this.value); listarDistritos(this.value)')))
                ->add('provincia', 'choice', array('label' => 'Provincia', 'disabled' => false, 'required' => true, 'choices' => $prov, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarMunicipios(this.value)')))
                ->add('municipio', 'choice', array('label' => 'Municipio', 'disabled' => false, 'required' => true, 'choices' => $muni, 'empty_value' => 'Seleccionar...','attr' => array('class' => 'form-control', 'onchange' => 'listarCantones(this.value)')))
                ->add('canton', 'choice', array('label' => 'Cantón', 'disabled' => false, 'required' => true, 'choices' => $cantn, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarLocalidades(this.value)')))
                ->add('localidad', 'choice', array('label' => 'Localidad', 'disabled' => false, 'required' => true, 'choices' => $locald, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('zona', 'text', array('label' => 'Zona', 'disabled' => false, 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('direccion', 'text', array('label' => 'Dirección', 'disabled' => false, 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('distrito', 'choice', array('label' => 'Distrito', 'disabled' => false, 'required' => true, 'choices' => $distrt, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));

        if ($InstitucioneducativaTipoId == 4) {
            $form->add('areaEspecialTipo', 'choice', array('label' => 'Areas', 'choices' => $areasArray, 'required' => false, 'multiple' => true, 'expanded' => true, 'attr' => array('class' => 'form-control')));
            $form->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices' => $nivelesArray, 'required' => false, 'multiple' => true, 'expanded' => true, 'attr' => array('class' => 'form-control')));
        } elseif ($InstitucioneducativaTipoId == 1 or $InstitucioneducativaTipoId == 2) {
            $form->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices' => $nivelesArray, 'required' => false, 'multiple' => true, 'expanded' => true, 'attr' => array('class' => 'form-control')));
        }

        return $form->getForm();

// 		->add('orgCurricularTipo', 'entity', array('label' => 'Org. Curricular','class' => 'SieAppWebBundle:OrgcurricularTipo', 'property' => 'orgcurricula', 'attr' => array('class' => 'form-control')))
// 		->add('id', 'text', array('label' => 'Codigo RUE','required' => true, 'pattern' => '[0-9]{8,17}', 'max_length' => '8'))
    }

    public function createAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $em->getConnection()->beginTransaction();
        try {
            /* Validar la ie no esta registrada
            $buscar_institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array(
                'institucioneducativa' => $form['institucionEducativa']));
            if ($buscar_institucion) {
                $this->get('session')->getFlashBag()->add('mensaje', 'La institución educativa ya existe en el sistema');
                return $this->redirect($this->generateUrl('bjp_rue'));
            }*/

            $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

            $query = $repository->createQueryBuilder('ie')
                ->select('max(ie.id)')
                ->getQuery();

            $maxie = $query->getResult();

            $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');

            $query = $repository->createQueryBuilder('jg')
                ->select('max(jg.id)')
                ->getQuery();

            $maxle = $query->getResult();

            $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');

            $query = $repository->createQueryBuilder('iesuc')
                ->select('max(iesuc.id)')
                ->getQuery();

            $maxiesuc = $query->getResult();

            $repository = $em->getRepository('SieAppWebBundle:BonojuancitoInstitucioneducativa');

            $query = $repository->createQueryBuilder('iebj')
                ->select('max(iebj.id)')
                ->getQuery();

            $maxiebj = $query->getResult();

            $codigoue = $maxie[0][1] + 1;
            $codigole = $maxle[0][1] + 1;
            $codigosuc= $maxiesuc[0][1] + 1;
            $codigobj= $maxiebj[0][1] + 1;

            //Registramos el LEducativo

            $entity = new JurisdiccionGeografica();
            $entity->setId($codigole);
            $entity->setDistritoTipo($em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($form['distrito']));
            $entity->setLugarTipoLocalidad($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['localidad']));
            $entity->setLugarTipoIdDistrito($form['distrito']);
            $entity->setJuridiccionAcreditacionTipo($em->getRepository('SieAppWebBundle:JurisdiccionGeograficaAcreditacionTipo')->findOneById($form['tipoAcreditacionle']));
            $entity->setDireccion(mb_strtoupper($form['direccion'], 'utf-8'));
            $entity->setZona(mb_strtoupper($form['zona'], 'utf-8'));
            $em->persist($entity);
            $em->flush();

            // Registramos la IEducativa
            $entity = new Institucioneducativa();
            $entity->setId($codigoue);
            $entity->setInstitucioneducativa(mb_strtoupper($form['institucionEducativa'], 'utf-8'));
            $entity->setFechaCreacion(new \DateTime('now'));
            $entity->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($form['dependenciaTipo']));
            $entity->setConvenioTipo(($form['convenioTipo'] != "") ? $em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById($form['convenioTipo']) : null);
            $entity->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById($form['estadoInstitucionTipo']));
            $entity->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->findOneById($form['institucionEducativaTipo']));
            $entity->setInstitucioneducativaAcreditacionTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaAcreditacionTipo')->findOneById($form['tipoAcreditacion']));
            $entity->setObsRue(mb_strtoupper($form['obsRue']), 'utf-8');
            $entity->setDesUeAntes(mb_strtoupper($form['desUeAntes'], 'utf-8'));
            $entity->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($codigole));
            $entity->setOrgcurricularTipo($em->getRepository('SieAppWebBundle:OrgcurricularTipo')->findOneById(1));
            $em->persist($entity);
            $em->flush();

        
            // Registramos la sucursal
            $query = $em->getConnection()->prepare("select * from sp_genera_institucioneducativa_sucursal('".$entity->getId()."','0','".$this->session->get('currentyear')."','1')");
            $query->execute();            

            // Registramos la ue en bonojuancito_unidadeducativa
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('bonojuancito_institucioneducativa');")->execute();
            $entity = new BonojuancitoInstitucioneducativa();
            $entity->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($codigoue));
            $entity->setGestionTipoId($this->session->get('currentyear'));
            $entity->setCantidadEstudiantes(0);
            $entity->setEsactivo(1);
            $entity->setBonojuancitoInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:BonojuancitoInstitucioneducativaTipo')->findOneById(2));
            $em->persist($entity);
            $em->flush();

            // Registramos los niveles
            if ($form['institucionEducativaTipo'] == 1 or $form['institucionEducativaTipo'] == 2) {
                //adiciona niveles nuevos
                $niveles = $form['nivelTipo'];

                for ($i = 0; $i < count($niveles); $i++) {

                    $nivel = new InstitucioneducativaNivelAutorizado();
                    $nivel->setFechaRegistro(new \DateTime('now'));
                    //$nivel->setGestionTipoId($this->session->get('currentyear'));
                    $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($niveles[$i]));
                    $nivel->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($codigoue));
                    $em->persist($nivel);
                }
                $em->flush();
            } elseif ($form['institucionEducativaTipo'] == 4) {
                //adiciona areas nuevas
                $areas = $form['areaEspecialTipo'];
                //     		dump($areas);die;
                for ($i = 0; $i < count($areas); $i++) {
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_nivel_autorizado');")->execute();
                    $area = new InstitucioneducativaAreaEspecialAutorizado();
                    $area->setFechaRegistro(new \DateTime('now'));
                    $area->setEspecialAreaTipo($em->getRepository('SieAppWebBundle:EspecialAreaTipo')->findOneById($areas[$i]));
                    $area->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($codigoue));
                    $em->persist($area);
                }
                $em->flush();

                //adiciona niveles nuevos
                $niveles = $form['nivelTipo'];

                for ($i = 0; $i < count($niveles); $i++) {
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_nivel_autorizado');")->execute();
                    $nivel = new InstitucioneducativaNivelAutorizado();
                    $nivel->setFechaRegistro(new \DateTime('now'));
                    $nivel->setGestionTipoId($this->session->get('currentyear'));
                    $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($niveles[$i]));
                    $nivel->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($codigoue));
                    $em->persist($nivel);
                }
                $em->flush();
            }

            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('mensaje', 'La institución educativa fue registrada correctamente: ' . $codigoue . ' - '. mb_strtoupper($form['institucionEducativa'], 'utf-8'));
            return $this->redirect($this->generateUrl('bjp_rue'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar la Institución Educativa');
            return $this->redirect($this->generateUrl('bjp_rue'));
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

        return $this->render('SieRegularBundle:RegistroInstitucionEducativa:show.html.twig', array(
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
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idInstitucion'));

        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar la institucion educativa.');
        }

        //carga araas de especial autorizadas
        if ($entity->getInstitucioneducativaTipo()->getId() == 1) {

            $query = $em->createQuery(
                            'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:NivelTipo tn
						WHERE tn.id in (:id)
						ORDER BY tn.id ASC'
                    )->setParameter('id', array(11, 12, 13));
            $niveles = $query->getResult();
            $nivelesArray = array();
            for ($i = 0; $i < count($niveles); $i++) {
                $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
            }

            $query = $em->createQuery(
                            'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:InstitucioneducativaNivelAutorizado ien
                        JOIN ien.nivelTipo tn
                        WHERE ien.institucioneducativa = :id
                        ORDER BY tn.id')
                    ->setParameter('id', $entity->getId());
            $niveles = $query->getResult();
            $nivelesInstitucionArray = array();
            for ($i = 0; $i < count($niveles); $i++) {
                $nivelesInstitucionArray[] = $niveles[$i]['id'];
            }
        } elseif ($entity->getInstitucioneducativaTipo()->getId() == 2) {
            $query = $em->createQuery(
                            'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:NivelTipo tn
						WHERE tn.id in (:id)
						ORDER BY tn.id ASC'
                    )->setParameter('id', array(201, 202, 203, 204, 205, 206, 207, 208, 211, 212, 213, 214, 215, 216, 217, 218));
            $niveles = $query->getResult();
            $nivelesArray = array();
            for ($i = 0; $i < count($niveles); $i++) {
                $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
            }


            $query = $em->createQuery(
                            'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:InstitucioneducativaNivelAutorizado ien
                        JOIN ien.nivelTipo tn
                        WHERE ien.institucioneducativa = :id
                        ORDER BY tn.id')
                    ->setParameter('id', $entity->getId());
            $niveles = $query->getResult();
            $nivelesInstitucionArray = array();
            for ($i = 0; $i < count($niveles); $i++) {
                $nivelesInstitucionArray[] = $niveles[$i]['id'];
            }
        } elseif ($entity->getInstitucioneducativaTipo()->getId() == 4) {

            $query = $em->createQuery(
                            'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:NivelTipo tn
						WHERE tn.id in (:id)
						ORDER BY tn.id ASC'
                    )->setParameter('id', array(11, 12, 13));
            $niveles = $query->getResult();
            $nivelesArray = array();
            for ($i = 0; $i < count($niveles); $i++) {
                $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
            }

            $query = $em->createQuery(
                            'SELECT DISTINCT tn.id,tn.nivel
                        FROM SieAppWebBundle:InstitucioneducativaNivelAutorizado ien
                        JOIN ien.nivelTipo tn
                        WHERE ien.institucioneducativa = :id
                        ORDER BY tn.id')
                    ->setParameter('id', $entity->getId());
            $niveles = $query->getResult();
            $nivelesInstitucionArray = array();
            for ($i = 0; $i < count($niveles); $i++) {
                $nivelesInstitucionArray[] = $niveles[$i]['id'];
            }



            $query = $em->createQuery(
                    'SELECT DISTINCT ta.id,ta.areaEspecial
                        FROM SieAppWebBundle:EspecialAreaTipo ta
                        ORDER BY ta.id');
            $areas = $query->getResult();
            $areasArray = array();
            for ($i = 0; $i < count($areas); $i++) {
                $areasArray[$areas[$i]['id']] = $areas[$i]['areaEspecial'];
            }


            $query = $em->createQuery(
                            'SELECT DISTINCT ta.id,ta.areaEspecial
                        FROM SieAppWebBundle:InstitucioneducativaAreaEspecialAutorizado iea
                        JOIN iea.especialAreaTipo ta
                        WHERE iea.institucioneducativa = :id
                        ORDER BY ta.id')
                    ->setParameter('id', $entity->getId());
            $areas = $query->getResult();
            $areasInstitucionArray = array();
            for ($i = 0; $i < count($areas); $i++) {
                $areasInstitucionArray[] = $areas[$i]['id'];
            }
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('bjp_rue_update'))
                ->add('idRue', 'hidden', array('data' => $entity->getId()))
                ->add('rue', 'text', array('label' => 'Código RUE', 'data' => $entity->getId(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('institucionEducativa', 'text', array('label' => 'Nombre', 'data' => $entity->getInstitucioneducativa(), 'required' => false, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('dependenciaTipo', 'entity', array('label' => 'Dependencia', 'class' => 'SieAppWebBundle:DependenciaTipo', 'property' => 'dependencia', 'data' => $em->getReference('SieAppWebBundle:DependenciaTipo', $entity->getDependenciaTipo()->getId()), 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('convenioTipo', 'entity', array('label' => 'Convenio', 'required' => false, 'class' => 'SieAppWebBundle:ConvenioTipo', 'property' => 'convenio', 'data' => $em->getReference('SieAppWebBundle:ConvenioTipo', $entity->getConvenioTipo()->getId()), 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('estadoTipo', 'entity', array('label' => 'Estado', 'class' => 'SieAppWebBundle:EstadoinstitucionTipo', 'property' => 'estadoinstitucion', 'data' => $em->getReference('SieAppWebBundle:EstadoinstitucionTipo', $entity->getEstadoinstitucionTipo()->getId()), 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('institucionTipo', 'entity', array('label' => 'Tipo', 'disabled' => false, 'class' => 'SieAppWebBundle:InstitucioneducativaTipo', 'property' => 'descripcion', 'data' => $em->getReference('SieAppWebBundle:InstitucioneducativaTipo', $entity->getInstitucioneducativaTipo()->getId()), 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('obsRue', 'text', array('label' => 'Observaciones', 'data' => $entity->getObsRue(), 'required' => false, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('desUeAntes', 'text', array('label' => 'Nombre anterior', 'data' => $entity->getDesUeAntes(), 'required' => false, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('leJuridicciongeograficaId', 'text', array('label' => 'Código LE', 'required' => true, 'data' => $entity->getLeJuridicciongeografica()->getId(), 'pattern' => '[0-9]{8,17}', 'max_length' => '8', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('departamento', 'text', array('label' => 'Departamento', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('provincia', 'text', array('label' => 'Provincia', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('municipio', 'text', array('label' => 'Municipio', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('canton', 'text', array('label' => 'Cantón', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('localidad', 'text', array('label' => 'Localidad', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('zona', 'text', array('label' => 'Zona', 'data' => $entity->getLeJuridicciongeografica()->getZona(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('direccion', 'text', array('label' => 'Dirección', 'data' => $entity->getLeJuridicciongeografica()->getDireccion(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('distrito', 'text', array('label' => 'Distrito', 'data' => $entity->getLeJuridicciongeografica()->getDistritoTipo()->getDistrito(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));


        if ($entity->getInstitucioneducativaTipo()->getId() == 4) {
            $form->add('areaEspecialTipo', 'choice', array('label' => 'Areas de especial', 'choices' => $areasArray, 'required' => true, 'data' => $areasInstitucionArray, 'multiple' => true, 'expanded' => true, 'attr' => array('class' => 'form-control')));
            $form->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices' => $nivelesArray, 'required' => true, 'multiple' => true, 'expanded' => true, 'data' => $nivelesInstitucionArray, 'attr' => array('class' => 'form-control')));
        } elseif ($entity->getInstitucioneducativaTipo()->getId() == 1 or $entity->getInstitucioneducativaTipo()->getId() == 2) {
            $form->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices' => $nivelesArray, 'required' => true, 'multiple' => true, 'expanded' => true, 'data' => $nivelesInstitucionArray, 'attr' => array('class' => 'form-control')));
        }


//         $form->getForm();

        return $this->render('SieRegularBundle:RegistroInstitucionEducativa:edit.html.twig', array('entity' => $entity, 'form' => $form->getForm()->createView()));



//         $editForm = $this->createEditForm($entity);
//         $deleteForm = $this->createDeleteForm($request->get('id'));
//         return $this->render('SieRegularBundle:RegistroInstitucionEducativa:edit.html.twig', array(
//                     'entity' => $entity,
//                     'edit_form' => $editForm->createView(),
//                     'delete_form' => $deleteForm->createView(),
//         ));
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
            'action' => $this->generateUrl('bjp_rue_update', array('id' => $entity->getId())),
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
        $this->session = new Session();
        $form = $request->get('form');
        //valida fecha de resolucion
//     	$fechaResolucion = new \DateTime($form['fechaResolucion']);
//     	if ($fechaResolucion > new \DateTime('now')) {
//     		$this->get('session')->getFlashBag()->add('registroInstitucionError', 'La fecha de resolucion es mayor que la fecha actual');
// //     		$this->redirect($request->server->get('HTTP_REFERER'));
// //     		return $this->redirect($request->getReferer());
// //     		return $this->redirectToRoute('bjp_rue_edit');
//     		return $this->redirect($request->server->get('HTTP_REFERER'));
//     	}
//     	dump($fechaResolucion);die;
        /*
         * Actualizacion de datos personales / persona
         */
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_nivel_autorizado');")->execute();
//     	$em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_area_especial_autorizado');")->execute();

        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idRue']);
//     	$entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRue'));

        $entity->setInstitucioneducativa(mb_strtoupper($form['institucionEducativa'], 'utf-8'));
        $entity->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($form['dependenciaTipo']));
        $entity->setConvenioTipo(($form['convenioTipo'] != "") ? $em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById($form['convenioTipo']) : null);
        $entity->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById($form['estadoTipo']));
        $entity->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->findOneById($form['institucionTipo']));
        $entity->setObsRue(mb_strtoupper($form['obsRue'], 'utf-8'));
        $entity->setDesUeAntes(mb_strtoupper($form['desUeAntes'], 'utf-8'));
        $em->persist($entity);
        $em->flush();


        if ($form['institucionTipo'] == 1 or $form['institucionTipo'] == 2) {
            //elimina los niveles
            $niveles = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa' => $entity->getId()));
            foreach ($niveles as $nivel) {
                $em->remove($nivel);
            }
            $em->flush();

            //adiciona niveles nuevos
            $niveles = $form['nivelTipo'];

            for ($i = 0; $i < count($niveles); $i++) {

                $nivel = new InstitucioneducativaNivelAutorizado();
                $nivel->setFechaRegistro(new \DateTime('now'));
                $nivel->setGestionTipoId($this->session->get('currentyear'));
                $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($niveles[$i]));
                $nivel->setInstitucioneducativa($entity);
                $em->persist($nivel);
            }
            $em->flush();
        } elseif ($form['institucionTipo'] == 4) {
            //elimina las areas
            $areas = $em->getRepository('SieAppWebBundle:InstitucioneducativaAreaEspecialAutorizado')->findBy(array('institucioneducativa' => $entity->getId()));
            foreach ($areas as $area) {
                $em->remove($area);
            }
            $em->flush();

            //adiciona areas nuevas
            $areas = $form['areaEspecialTipo'];
//     		dump($areas);die;
            for ($i = 0; $i < count($areas); $i++) {

                $area = new InstitucioneducativaAreaEspecialAutorizado();
                $area->setFechaRegistro(new \DateTime('now'));
                $area->setEspecialAreaTipo($em->getRepository('SieAppWebBundle:EspecialAreaTipo')->findOneById($areas[$i]));
                $area->setInstitucioneducativa($entity);
                $em->persist($area);
            }
            $em->flush();
            //elimina los niveles
            $niveles = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa' => $entity->getId()));
            foreach ($niveles as $nivel) {
                $em->remove($nivel);
            }
            $em->flush();

            //adiciona niveles nuevos
            $niveles = $form['nivelTipo'];

            for ($i = 0; $i < count($niveles); $i++) {

                $nivel = new InstitucioneducativaNivelAutorizado();
                $nivel->setFechaRegistro(new \DateTime('now'));
                $nivel->setGestionTipoId($this->session->get('currentyear'));
                $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($niveles[$i]));
                $nivel->setInstitucioneducativa($entity);
                $em->persist($nivel);
            }
            $em->flush();
        }

        $this->get('session')->getFlashBag()->add('mensaje', 'La institución educativa fue actualizada correctamente: ' . $form['idRue'] .' - '. mb_strtoupper($form['institucionEducativa'], 'utf-8'));

        return $this->redirect($this->generateUrl('bjp_rue'));
    }

    /**
     * Deletes a Institucioneducativa entity.
     *
     */
    public function deleteAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idInstitucion'));
            $gestion = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($this->session->get('currentyear'));

            if ($institucioneducativa) {
                $institucionNivelAutorizado = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('gestionTipoId' => $this->session->get('currentyear'), 'institucioneducativa' => $institucioneducativa->getId()));

                $institucionSucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findBy(array('gestionTipo' => $gestion->getId(), 'institucioneducativa' => $institucioneducativa->getId()));

                $jurisdiccion = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($request->get('idInstitucionLe'));

                if ($institucionNivelAutorizado) {
                    foreach ($institucionNivelAutorizado as $id) {
                        $em->remove($em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findOneById($id->getId()));
                        $em->flush();
                    }
                }

                if ($institucionSucursal) {
                    foreach ($institucionSucursal as $id) {
                        $em->remove($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($id->getId()));
                        $em->flush();
                    }
                }

                $em->remove($institucioneducativa);
                $em->flush();

                if ($jurisdiccion) {
                    $em->remove($jurisdiccion);
                    $em->flush();
                }

                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('mensaje', 'El registro fue eliminado exitosamente');

                return $this->redirect($this->generateUrl('bjp_rue'));
            }
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('mensaje', 'El registro no se pudo eliminar');
            return $this->redirect($this->generateUrl('bjp_rue'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    public function buscaredificioAction($idLe) {

        $em = $this->getDoctrine()->getManager();
        $edificio = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($idLe);

        $departamento = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar() : "";
        $provincia = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar() : "";
        $municipio = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugar() : "";
        $canton = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugar() : "";
        $localidad = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugar() : "";
        $zona = ($edificio) ? $edificio->getZona() : "";
        $direccion = ($edificio) ? $edificio->getDireccion() : "";
        $distrito = ($edificio) ? $edificio->getDistritoTipo()->getDistrito() : "";

        $response = new JsonResponse();
        return $response->setData(array(
                    'departamento' => $departamento,
                    'provincia' => $provincia,
                    'municipio' => $municipio,
                    'canton' => $canton,
                    'localidad' => $localidad,
                    'zona' => $zona,
                    'direccion' => $direccion,
                    'distrito' => $distrito
        ));
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarprovinciasAction($dpto) {
        try {
            $em = $this->getDoctrine()->getManager();


            $query = $em->createQuery(
                            'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                    ->setParameter('nivel', 2)
                    ->setParameter('lt1', $dpto + 1);
            $provincias = $query->getResult();

            $provinciasArray = array();
            foreach ($provincias as $c) {
                $provinciasArray[$c->getId()] = $c->getLugar();
            }

            $response = new JsonResponse();
            return $response->setData(array('listaprovincias' => $provinciasArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarmunicipiosAction($prov) {
        try {
            $em = $this->getDoctrine()->getManager();


            $query = $em->createQuery(
                            'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                    ->setParameter('nivel', 3)
                    ->setParameter('lt1', $prov);
            $municipios = $query->getResult();

            $municipiosArray = array();
            foreach ($municipios as $c) {
                $municipiosArray[$c->getId()] = $c->getLugar();
            }

            $response = new JsonResponse();
            return $response->setData(array('listamunicipios' => $municipiosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarcantonesAction($muni) {
        try {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQuery(
                            'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                    ->setParameter('nivel', 4)
                    ->setParameter('lt1', $muni);
            $cantones = $query->getResult();

            $cantonesArray = array();
            foreach ($cantones as $c) {
                $cantonesArray[$c->getId()] = $c->getLugar();
            }

            $response = new JsonResponse();
            return $response->setData(array('listacantones' => $cantonesArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarlocalidadesAction($cantn) {
        try {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQuery(
                            'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                    ->setParameter('nivel', 5)
                    ->setParameter('lt1', $cantn);
            $localidades = $query->getResult();

            $localidadesArray = array();
            foreach ($localidades as $c) {
                $localidadesArray[$c->getId()] = $c->getLugar();
            }

            $response = new JsonResponse();
            return $response->setData(array('listalocalidades' => $localidadesArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listardistritosAction($dpto) {
        try {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQuery(
                            'SELECT dt
                    FROM SieAppWebBundle:DistritoTipo dt
                    WHERE dt.id NOT IN (:ids)
                    AND dt.departamentoTipo = :dpto
                    ORDER BY dt.id')
                    ->setParameter('ids', array(1000,2000,3000,4000,5000,6000,7000,8000,9000))
                    ->setParameter('dpto', $dpto);
            $distritos = $query->getResult();

            $distritosArray = array();
            foreach ($distritos as $c) {
                $distritosArray[$c->getId()] = $c->getDistrito();
            }

            $response = new JsonResponse();
            return $response->setData(array('listadistritos' => $distritosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

}
