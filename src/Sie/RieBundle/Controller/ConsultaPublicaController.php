<?php
namespace Sie\RieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\TtecAreaFormacionTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaAreaEspecialAutorizado;
use Sie\AppWebBundle\Entity\InstitucioneducativaNivelAutorizado;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaAreaFormacionAutorizado;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaSede;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaCarreraAutorizada;
use Sie\AppWebBundle\Entity\TtecResolucionCarrera;
use Sie\AppWebBundle\Entity\TtecResolucionTipo;
use Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo;
use Sie\AppWebBundle\Entity\TtecCarreraTipo;
use Sie\AppWebBundle\Entity\TtecRegimenEstudioTipo;
use Sie\AppWebBundle\Entity\TtecEstadoCarreraTipo;

use Sie\AppWebBundle\Form\InstitucioneducativaType;

/**
 * Institucioneducativa controller.
 *
 */
class ConsultaPublicaController extends Controller {

      public $session;
      /**
       * the class constructor
       */
      public function __construct() {
          //init the session values
          $this->session = new Session();
      }

    /**
     * Formularios de búsqueda de institutos
     */
    public function indexAction() {
        /*
        $formInstitucioneducativa       = $this->createSearchFormInstitucioneducativa();
        $formInstitucioneducativaId     = $this->createSearchFormInstitucioneducativaId();
		$formInstitucioneducativaTipo   = $this->createSearchFormInstitucioneducativaTipo();
		$formInstitucioneducativaEstado = $this->createSearchFormInstitucioneducativaEstado();


        return $this->render('SieRieBundle:ConsultaPublica:search.html.twig', array(
                                                                            			'formInstitucioneducativa' => $formInstitucioneducativa->createView(), 
                                                                                        'formInstitucioneducativaId' => $formInstitucioneducativaId->createView(),
                                                                                        'formInstitucioneducativaTipo' => $formInstitucioneducativaTipo->createView(),
                                                                                        'formInstitucioneducativaEstado' => $formInstitucioneducativaEstado->createView()));
        */   
        
        return $this->render('SieRieBundle:ConsultaPublica:index.html.twig');
    }

    /* 
     * Creando formulario para busqueda por nombre del instituto
    */      
    private function createSearchFormInstitucioneducativa() {
    	$form = $this->createFormBuilder()
                                ->setAction($this->generateUrl('consulta_rie_result'))
                                        ->add('tipo_search', 'hidden', array('data' => 'institucioneducativa'))
                                        ->add('institucioneducativa', 'text', array('required' => true, 'invalid_message' => 'Campo obligatorio'))
                                        ->add('buscar', 'submit', array('label' => 'Buscar'))
                                        ->getForm();
        return $form;
    }    

    /* 
     * Creando formulario para busqueda por codigo RIE del instituto
    */      
    private function createSearchFormInstitucioneducativaId() {
        $form = $this->createFormBuilder()
                                        ->setAction($this->generateUrl('consulta_rie_result'))
                                        ->add('tipo_search', 'hidden', array('data' => 'institucioneducativaId'))
                                        ->add('institucioneducativaId', 'text', array('required' => true,'invalid_message' => 'Campo obligatorio', 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
                                        ->add('buscarId', 'submit', array('label' => 'Buscar'))
                                        ->getForm();
        return $form;
    }
        
    /* 
     * Creando formulario para busqueda por Departamento y Tipo de Institucion
    */      
    private function createSearchFormInstitucioneducativaTipo() {
        $em = $this->getDoctrine()->getManager();

        $depArray   = $this->obtieneDepartamentos();
        $tiposArray = $this->obtieneTipoInstituto();

        $form = $this->createFormBuilder()
                        ->setAction($this->generateUrl('consulta_rie_result'))
                        ->add('tipo_search', 'hidden', array('data' => 'institucioneducativaTipo'))
                        ->add('departamento', 'choice', array('label' => 'Departamento', 'required' => true,'choices'=>$depArray ,'empty_value' => 'Seleccionar departamento...'))
                        ->add('institucioneducativaTipo', 'choice', array('label' => 'Tipo', 'disabled' => false,'choices' => $tiposArray,'empty_value' => 'Seleccionar tipo...', 'attr' => array('class' => 'form-control')))
                        ->add('buscarTipo', 'submit', array('label' => 'Buscar'))
                        ->getForm();
        return $form;
    }
        
    /* 
     * Creando formulario para busqueda por Departamento y Estado
    */     
    private function createSearchFormInstitucioneducativaEstado() {
        $em = $this->getDoctrine()->getManager();

        $depArray = $this->obtieneDepartamentos();
        $estadosArray = $this->obtieneEstadoInstitucion();            

        $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('consulta_rie_result'))
                    ->add('tipo_search', 'hidden', array('data' => 'institucioneducativaEstado'))
                    ->add('departamento', 'choice', array('label' => 'Departamento', 'required' => true,'choices'=>$depArray ,'empty_value' => 'Seleccionar departamento...'))
                    ->add('institucioneducativaEstado', 'choice', array('label' => 'Estado', 'disabled' => false,'choices' => $estadosArray,'empty_value' => 'Seleccionar estado...', 'attr' => array('class' => 'form-control')))
                    ->add('buscarEstado', 'submit', array('label' => 'Buscar'))
                    ->getForm();
        return $form;
    }

    /**
     *  Resultado de consulta de institutos tecnicos/tecnologicos
     */
     public function findinstitutoAction(Request $request) {
		$form = $request->get('form');		
        $em = $this->getDoctrine()->getManager();
        //opciones de consulta
        switch($form['tipo_search']){
            //opcion1: busqueda por nombre de itt
            case 'institucioneducativa':

                    $query = $em->createQuery('SELECT ie
                                                FROM SieAppWebBundle:Institucioneducativa ie
                                                WHERE UPPER(ie.institucioneducativa) LIKE :id
                                                AND ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
                                                AND ie.institucioneducativaTipo IN (:ieTipo) 
                                                ORDER BY ie.id')
                                        ->setParameter('id','%' . strtoupper($form['institucioneducativa']) . '%')
                                        ->setParameter('ieTipo', array(7,8,9))
                                        ->setParameter('ieAcreditacion', 1);
                break;

            //opcion2: busqueda por codigo de itt
            case 'institucioneducativaId':
                    $query = $em->createQuery(
                            'SELECT ie
                                FROM SieAppWebBundle:Institucioneducativa ie
                                WHERE ie.id = :id
                                AND ie.institucioneducativaTipo IN (:ieTipo)
                                AND ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
                                ORDER BY ie.id')
                                ->setParameter('id', $form['institucioneducativaId'])
                                ->setParameter('ieTipo', array(7,8,9))
                                ->setParameter('ieAcreditacion', 1);
                break;

            //opcion3: busqueda por tipo de itt                
            case 'institucioneducativaTipo':
                        
                    switch($form['departamento']){
                        case 1:  // Es todo el Pais : Bolivia
                                $query = $em->createQuery(
                                    'SELECT ie
                                        FROM SieAppWebBundle:Institucioneducativa ie
                                        JOIN ie.leJuridicciongeografica le
                                        JOIN le.lugarTipoLocalidad lo
                                        JOIN lo.lugarTipo ca
                                        JOIN ca.lugarTipo se
                                        JOIN se.lugarTipo pr
                                        JOIN pr.lugarTipo de
                                        JOIN ie.institucioneducativaTipo ti
                                        WHERE de.id in ( :id )
                                        AND ti.id = :tipoId
                                        AND ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
                                        ORDER BY de.id')
                                                ->setParameter('id', array(2,3,4,5,6,7,8,9,10))
                                                ->setParameter('tipoId', $form['institucioneducativaTipo'])
                                                ->setParameter('ieAcreditacion', 1);
                            break;

                        default: //Es un departamento en particular
                            //Buscando Ambos tipos de instituto (Tecnico y Tecnologico)
                            if($form['institucioneducativaTipo'] == 0){ 
                                $query = $em->createQuery(
                                    'SELECT ie
                                        FROM SieAppWebBundle:Institucioneducativa ie
                                        JOIN ie.leJuridicciongeografica le
                                        JOIN le.lugarTipoLocalidad lo
                                        JOIN lo.lugarTipo ca
                                        JOIN ca.lugarTipo se
                                        JOIN se.lugarTipo pr
                                        JOIN pr.lugarTipo de
                                        WHERE de.id = :id
                                        AND ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
                                        AND ie.institucioneducativaTipo IN (:ieTipo) 
                                        ORDER BY ie.id')
                                            ->setParameter('id', $form['departamento'])
                                            ->setParameter('ieTipo', array(7,8,9))
                                            ->setParameter('ieAcreditacion', 1);    

                            } else {
                                $query = $em->createQuery(
                                    'SELECT ie
                                        FROM SieAppWebBundle:Institucioneducativa ie
                                        JOIN ie.leJuridicciongeografica le
                                        JOIN le.lugarTipoLocalidad lo
                                        JOIN lo.lugarTipo ca
                                        JOIN ca.lugarTipo se
                                        JOIN se.lugarTipo pr
                                        JOIN pr.lugarTipo de
                                        JOIN ie.institucioneducativaTipo ti
                                        JOIN ie.estadoinstitucionTipo et
                                        WHERE de.id = :id
                                        AND ti.id = :tipoId
                                        AND ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
                                        AND et.id = :idEstado
                                        ORDER BY ie.id')
                                            ->setParameter('id', $form['departamento'])
                                            ->setParameter('tipoId', $form['institucioneducativaTipo'])
                                            ->setParameter('ieAcreditacion', 1)
                                            ->setParameter('idEstado', 10);                        
                            }        
                            break;
                    }
                break;

            case 'institucioneducativaEstado':
                
                    switch($form['departamento']){
                        case 1:  // Es todo el Pais : Bolivia 
                                $query = $em->createQuery(
                                    'SELECT ie
                                        FROM SieAppWebBundle:Institucioneducativa ie
                                        JOIN ie.leJuridicciongeografica le
                                        JOIN le.lugarTipoLocalidad lo
                                        JOIN lo.lugarTipo ca
                                        JOIN ca.lugarTipo se
                                        JOIN se.lugarTipo pr
                                        JOIN pr.lugarTipo de
                                        JOIN ie.estadoinstitucionTipo eit
                                        WHERE de.id in ( :id )
                                        AND eit.id = :estadoId
                                        AND ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
                                        AND ie.institucioneducativaTipo IN (:ieTipo) 
                                        ORDER BY de.id')
                                                ->setParameter('id', array(2,3,4,5,6,7,8,9,10))
                                                ->setParameter('estadoId', $form['institucioneducativaEstado'])
                                                ->setParameter('ieTipo', array(7,8,9))
                                                ->setParameter('ieAcreditacion', 1);
                            break;

                        default: //Es un departamento en particular
                                $query = $em->createQuery(
                                        'SELECT ie
                                            FROM SieAppWebBundle:Institucioneducativa ie
                                            JOIN ie.leJuridicciongeografica le
                                            JOIN le.lugarTipoLocalidad lo
                                            JOIN lo.lugarTipo ca
                                            JOIN ca.lugarTipo se
                                            JOIN se.lugarTipo pr
                                            JOIN pr.lugarTipo de
                                            JOIN ie.estadoinstitucionTipo eit
                                            WHERE de.id = :id
                                            AND eit.id = :estadoId
                                            AND ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
                                            AND ie.institucioneducativaTipo IN (:ieTipo) 
                                            ORDER BY ie.id')
                                                    ->setParameter('id', $form['departamento'])
                                                    ->setParameter('estadoId', $form['institucioneducativaEstado'])
                                                    ->setParameter('ieTipo', array(7,8,9))
                                                    ->setParameter('ieAcreditacion', 1);
                            break;
                    }               
                break;
                                
        }
		$entities = $query->getResult();

        if (!$entities){
        	$this->get('session')->getFlashBag()->add('msgSearch', 'No se encontró la información.');

        	$formInstitucioneducativa = $this->createSearchFormInstitucioneducativa();
        	$formInstitucioneducativaId = $this->createSearchFormInstitucioneducativaId();
			$formInstitucioneducativaTipo = $this->createSearchFormInstitucioneducativaTipo();
			$formInstitucioneducativaEstado = $this->createSearchFormInstitucioneducativaEstado();

        	return $this->render('SieRieBundle:ConsultaPublica:search.html.twig', array(
                                                                                                    'formInstitucioneducativa' => $formInstitucioneducativa->createView(), 
                                                                                                    'formInstitucioneducativaId' => $formInstitucioneducativaId->createView(), 
                                                                                                    'formInstitucioneducativaTipo' => $formInstitucioneducativaTipo->createView(),
                                                                                                    'formInstitucioneducativaEstado' => $formInstitucioneducativaEstado->createView()));
        }
        return $this->render('SieRieBundle:ConsultaPublica:resultinstituto.html.twig', array('entities' => $entities));
    }

    /* 
     * Muestra datos del instituto tecnico/tecnologicos
     */  
    public function showAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));
        $historiales = $this->obtieneHistorial($entity->getId());
        $ofertas = $this->listadoOfertaAcademica($entity->getId(), TRUE);
        //$em->getRepository('SieAppWebBundle:Juridicciongeografica')->findOneById($request->get('idRie'))
        return $this->render('SieRieBundle:ConsultaPublica:show.html.twig', array('entity' => $entity, 'ofertas' => $ofertas, 'historiales' => $historiales));
    }


    /**
     * Obtiene Listado de Institutos de forma general
     */
    public function listAction(){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT se
                                        FROM SieAppWebBundle:TtecInstitucioneducativaSede se
                                        JOIN se.institucioneducativa ie 
                                    WHERE ie.institucioneducativaTipo in (:idTipo)
                                        AND ie.estadoinstitucionTipo in (:idEstado)
                                        AND se.estado = :estadoSede
                                ORDER BY ie.id ')
                                    ->setParameter('idTipo', array(7, 8, 9))
                                    ->setParameter('idEstado', 10)
                                    ->setParameter('estadoSede', TRUE);        
        $entities = $query->getResult(); 
        return $this->render('SieRieBundle:ConsultaPublica:list.html.twig', array('entities' => $entities));
    }

    /**
     * Obtiene detalle del instituto seleccionado
     */
    public function detalleittAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $ofertas = $this->listadoOfertaAcademica($request->get('idRie'));
        $cursos = $this->listadoCursosCapacitacion($request->get('idRie'));
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));
        $local = $this->datoslocal($entity->getLeJuridicciongeografica()->getId());

        return $this->render('SieRieBundle:ConsultaPublica:show.html.twig', array('entity' => $entity, 'ofertas' => $ofertas, 'cursos' => $cursos, 'local' => $local));
    }

    /**
     * Obtiene asignaciones que tiene un edificio educativo
     */
    public function edificioAction(){
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('consulta_rie_edificio_lista'))
                ->add('codigoLe', 'text', array('label' => 'Código de Local Educativo', 'required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '8') ))
                ->add('guardar', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')));
            
        return $this->render('SieRieBundle:ConsultaPublica:edificio.html.twig', array('form' => $form->getForm()->createView()));
    }

    /**
     * Resultado de búsqueda de edificios educativos, asignados a institutos
     */
    public function edificiolistAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $listado = null;
        if(is_numeric($form['codigoLe'])){
            $local = $this->datoslocal($form['codigoLe']);
            $listado = $this->obtieneListadoInstitutosLocal($form['codigoLe']);
        }
        if($listado){
            return $this->render('SieRieBundle:ConsultaPublica:edificiolist.html.twig', array('listado' => $listado, 'local' => $local ));
        }else{
            $this->get('session')->getFlashBag()->add('mensaje', 'No se encontró la información del local educativo.');
            return $this->redirect($this->generateUrl('consulta_rie_edificio'));
        }
    }

    /**
     * Obtiene dato de la actual ubicación geografica
     */
    public function ubicaciongAction(){
        $em = $this->getDoctrine()->getManager();
        $depArray = $this->obtieneDepartamentos();
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('consulta_rie_pdf_ubicacion_geo'))
            ->add('departamento', 'choice', array('label' => 'Departamento:', 'required' => true, 'choices'=>$depArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
            ->add('provincia', 'choice', array('label' => 'Provincia:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
            ->add('municipio', 'choice', array('label' => 'Municipio:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
            ->add('canton', 'choice', array('label' => 'Cantón:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
            ->add('localidad', 'choice', array('label' => 'Localidad:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
            ->add('guardar', 'submit', array('label' => 'DESCARGAR FORMULARIO PDF', 'attr' => array('class' => 'btn btn-primary')));     
        return $this->render('SieRieBundle:ConsultaPublica:ubicaciong.html.twig', array('form' => $form->getForm()->createView()));
    }


    /**
     * Obtiene datos de la nueva ubicación
     */
    public function nuevaubicacionAction(){
        $em = $this->getDoctrine()->getManager();
        
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('consulta_rie_nuevaubicacion_dato'))
                ->add('codigoLe', 'text', array('label' => 'Código de Local Educativo', 'required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '8') ))
                ->add('guardar', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')));
        
        return $this->render('SieRieBundle:ConsultaPublica:nuevaubicacion.html.twig', array('form' => $form->getForm()->createView()));
    }    

    /**
     * Resultado de nueva ubicacion geografica
     */
    public function nuevaubicaciondatoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
    
        $local = null;
        if(is_numeric($form['codigoLe'])){
            $local = $this->datoslocal($form['codigoLe']);
        }
        if($local){
            $depArray = $this->obtieneDepartamentos();
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('consulta_rie_pdf_nueva_ubicacion'))
                ->add('codigoLe', 'hidden', array( 'data'=>$form['codigoLe'] ))
                ->add('departamento', 'choice', array('label' => 'Departamento:', 'required' => true, 'choices'=>$depArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                ->add('provincia', 'choice', array('label' => 'Provincia:', 'required' => true,  'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                ->add('municipio', 'choice', array('label' => 'Municipio:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                ->add('canton', 'choice', array('label' => 'Cantón:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                ->add('localidad', 'choice', array('label' => 'Localidad:', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                ->add('guardar', 'submit', array('label' => 'DESCARGAR FORMULARIO PDF', 'attr' => array('class' => 'btn btn-primary')));  

            return $this->render('SieRieBundle:ConsultaPublica:nuevaubicaciondato.html.twig', array('form' => $form->getForm()->createView(), 'local' => $local));
        }else{
            $this->get('session')->getFlashBag()->add('mensaje', 'No se encontró la información del local educativo.');
            return $this->redirect($this->generateUrl('consulta_rie_nuevaubicacion'));
        }
    }    
    
    /**
     * Generando Formulario de Institutos asignados a un local educativo
     */
    public function pdfEdificioInstitutoAction(Request $request){
        $idLe = $request->get('idLe');
        $local = $this->datoslocal($idLe);
        $listado = $this->obtieneListadoInstitutosLocal($idLe);

		$custom_layout = array(150, 200);
		$pdf = $this->container->get("white_october.tcpdf")->create('PORTRAIT', PDF_UNIT, $custom_layout, true,'UTF-8', false);
		$pdf->setAuthor('Ministerio de Educación');
		$pdf->SetTitle('FormularioInstituto');
		$pdf->SetSubject('');
		$pdf->SetPrintHeader(false);
		$pdf->SetFooterMargin(0);
		$resolution= array(210, 260);
        $pdf->AddPage('R', $resolution);
       
        // Image example with resizing
        
        $pdf->Image('images/logo_minedu.png', 10, 25, 35, 15, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
        $pdf->Ln(38);
        $pdf->SetFont('helvetica','B',14);        
        $pdf->Cell(0, 0, 'LOCAL EDUCATIVO', 0, 0, 'R', 0, '', 0);
        
        $pdf->Ln();
        //Grupo 1, codigo
        $pdf->SetFont('helvetica','',10);        
        $pdf->Cell(50, 0, 'Código de Local Educativo', 1, 0, '', 0, '', 0);
        $pdf->Cell(50, 0, $idLe, 1, 0, '', 0, '', 0);        

        //Grupo 2, datos ubicacion
        $pdf->Ln(6);
        $pdf->SetFont('helvetica','B',11);
        $pdf->Cell(0, 0, 'Ubicación Geográfica Actual SIE', 1, 0, 'L', 0, '', 0);

        $pdf->Ln(6);
        $pdf->SetFont('helvetica','',10);
        $pdf->MultiCell(40, 0, 'Departamento ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $local[0]['cod_dep'], 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $local[0]['departamento'], 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $pdf->MultiCell(40, 0, 'Provincia ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0,  $local[0]['cod_pro'], 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $local[0]['provincia'], 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $pdf->MultiCell(40, 0, 'Municipio ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0,  $local[0]['cod_mun'], 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $local[0]['municipio'], 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $pdf->MultiCell(40, 0, 'Cantón ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0,  $local[0]['cod_can'], 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $local[0]['canton'], 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $pdf->MultiCell(40, 0, 'Localidad ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0,  $local[0]['cod_loc'], 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $local[0]['localidad'], 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        // Grupo 3, lista institutos
        $pdf->Ln(3);
        $pdf->SetFont('helvetica','B',11);
        $pdf->Cell(0, 0, 'Lista de Institutos Técnicos y Tecnológicos', 1, 0, 'L', 0, '', 0);

        $pdf->Ln(6);
        $pdf->MultiCell(25, 0, 'Cod. RIE', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T'); 
        $pdf->MultiCell(128, 0, 'Denominación', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');      
        $pdf->MultiCell(35, 0, 'Resolución', 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $pdf->SetFont('helvetica','',10);
        foreach($listado as $fila) {
            $pdf->MultiCell(25, 0, $fila->getId(), 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T'); 
            $pdf->MultiCell(128, 0, $fila->getInstitucioneducativa(), 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');      
            $pdf->MultiCell(35, 0, $fila->getNroresolucion(), 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');
        }
        $pdf->Ln(3);
        $pdf->SetFont('helvetica','',8);
        $pdf->MultiCell(0, 0, 'Quienes firmamos  el presente documento declaramos que los datos son verídicos y auténticos. De no serlo nos someteremos a las sanciones establecidas por Ley.', 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T'); 
        
        $pdf->Ln(3);
        $pdf->MultiCell(94, 20, 'Responsable de I.T.T.', 1, 'L', 0, 0, '', '', true, 0, false, true, 20, 'M');      
        $pdf->MultiCell(94, 20, 'Nombre y Firma', 1, 'C', 0, 1, '', '', true, 0, false, true, 20, 'B');

        $pdf->MultiCell(94, 20, 'VoBo Responsable de D.D.E.', 1, 'L', 0, 0, '', '', true, 0, false, true, 20, 'M');      
        $pdf->MultiCell(94, 20, 'Nombre y Firma', 1, 'C', 0, 1, '', '', true, 0, false, true, 20, 'B');

        $pdf->MultiCell(94, 20, 'Lugar y fecha', 1, 'L', 0, 0, '', '', true, 0, false, true, 20, 'M');      
        $pdf->MultiCell(94, 20, '', 1, 'C', 0, 1, '', '', true, 0, false, true, 20, 'B');

        $pdf->SetFont('helvetica','',7);
        $pdf->Cell(0, 0, date('d/m/Y H:i:s'), 0, 0, 'R', 0, '', 0);
       

        $pdf->Output('formLocalEducativo-'.$idLe.'.pdf', 'D');        
    }

    /**
     * Generando Formulario de datos de localización geografica
     */
    public function pdfUbicacionGeoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $custom_layout = array(150, 200);
		$pdf = $this->container->get("white_october.tcpdf")->create('PORTRAIT', PDF_UNIT, $custom_layout, true,'UTF-8', false);
		$pdf->setAuthor('Ministerio de Educación');
		$pdf->SetTitle('formUbicacionGeografica');
		$pdf->SetSubject('');
		$pdf->SetPrintHeader(false);
		$pdf->SetFooterMargin(0);
		$resolution= array(210, 260);
        $pdf->AddPage('R', $resolution);
       
        // Image example with resizing
        
        $pdf->Image('images/logo_minedu.png', 10, 25, 35, 15, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
        $pdf->Ln(38);
        $pdf->SetFont('helvetica','B',14);        
        $pdf->Cell(0, 0, 'ACTUALIZACIÓN GEOGRÁFICA', 0, 0, 'R', 0, '', 0);
        
        //Grupo 1, tit
        $pdf->Ln(6);
        $pdf->SetFont('helvetica','B',11);
        $pdf->Cell(0, 0, 'Ubicación Geográfica', 1, 0, 'L', 0, '', 0);

        $pdf->Ln(6);

        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['departamento']);
        $pdf->SetFont('helvetica','',10);
        $pdf->MultiCell(40, 0, 'Departamento ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $dep->getCodigo(), 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $dep->getLugar(), 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $pro = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['provincia']);
        $pdf->MultiCell(40, 0, 'Provincia ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $pro->getCodigo(), 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $pro->getLugar(), 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $mun = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['municipio']);
        $pdf->MultiCell(40, 0, 'Municipio ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $mun->getCodigo(), 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $mun->getLugar(), 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $can = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['canton']);
        $pdf->MultiCell(40, 0, 'Cantón ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $can->getCodigo(), 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $can->getLugar(), 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $loc = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['localidad']);
        $pdf->MultiCell(40, 0, 'Localidad ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $loc->getCodigo(), 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $loc->getLugar(), 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');


        //grupo firmas
        $pdf->Ln(3);
        $pdf->SetFont('helvetica','',8);
        $pdf->MultiCell(0, 0, 'Quienes firmamos  el presente documento declaramos que los datos son verídicos y auténticos. De no serlo nos someteremos a las sanciones establecidas por Ley.', 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T'); 
        
        $pdf->Ln(3);
        $pdf->MultiCell(94, 20, 'Responsable de I.T.T.', 1, 'L', 0, 0, '', '', true, 0, false, true, 20, 'M');      
        $pdf->MultiCell(94, 20, 'Nombre y Firma', 1, 'C', 0, 1, '', '', true, 0, false, true, 20, 'B');

        $pdf->MultiCell(94, 20, 'VoBo Responsable de D.D.E.', 1, 'L', 0, 0, '', '', true, 0, false, true, 20, 'M');      
        $pdf->MultiCell(94, 20, 'Nombre y Firma', 1, 'C', 0, 1, '', '', true, 0, false, true, 20, 'B');

        $pdf->MultiCell(94, 20, 'Lugar y fecha', 1, 'L', 0, 0, '', '', true, 0, false, true, 20, 'M');      
        $pdf->MultiCell(94, 20, '', 1, 'C', 0, 1, '', '', true, 0, false, true, 20, 'B');

        $pdf->SetFont('helvetica','',7);
        $pdf->Cell(0, 0, date('d/m/Y H:i:s'), 0, 0, 'R', 0, '', 0);

        $pdf->Output('formUbicacionGeografica'.'.pdf', 'D');
    }

    /**
     * Generando Formulario de la nueva ubicacion geografica
     */
    public function pdfNuevaUbicacionAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $local = $this->datoslocal($form['codigoLe']);

       // var_dump($form);
      //  die;

        $custom_layout = array(150, 200);
		$pdf = $this->container->get("white_october.tcpdf")->create('PORTRAIT', PDF_UNIT, $custom_layout, true,'UTF-8', false);
		$pdf->setAuthor('Ministerio de Educación');
		$pdf->SetTitle('formNuevaUbicacionGeografica');
		$pdf->SetSubject('');
		$pdf->SetPrintHeader(false);
		$pdf->SetFooterMargin(0);
		$resolution= array(210, 260);
        $pdf->AddPage('R', $resolution);
       
        // Image example with resizing
        
        $pdf->Image('images/logo_minedu.png', 10, 25, 35, 15, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);
        $pdf->Ln(38);
        $pdf->SetFont('helvetica','B',14);        
        $pdf->Cell(0, 0, 'ACTUALIZACIÓN GEOGRÁFICA SIE - 2001', 0, 0, 'R', 0, '', 0);

        $pdf->Ln();
        //Grupo 1, codigo
        $pdf->SetFont('helvetica','',10);        
        $pdf->Cell(50, 0, 'Código de Local Educativo', 1, 0, '', 0, '', 0);
        $pdf->Cell(50, 0, $local[0]['id'], 1, 0, '', 0, '', 0);   

        //Grupo 2, tit
        $pdf->Ln(6);
        $pdf->SetFont('helvetica','B',11);
        $pdf->Cell(0, 0, 'Ubicación Geográfica  Actual SIE', 1, 0, 'L', 0, '', 0);

        $pdf->Ln(6);

        $pdf->SetFont('helvetica','',10);
        $pdf->MultiCell(40, 0, 'Departamento ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $local[0]['cod_dep'], 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $local[0]['departamento'], 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $pdf->MultiCell(40, 0, 'Provincia ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $local[0]['cod_pro'], 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $local[0]['provincia'], 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $pdf->MultiCell(40, 0, 'Municipio ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $local[0]['cod_mun'], 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $local[0]['municipio'], 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $pdf->MultiCell(40, 0, 'Cantón ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $local[0]['cod_can'], 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $local[0]['canton'], 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $pdf->MultiCell(40, 0, 'Localidad ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $local[0]['cod_loc'], 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $local[0]['localidad'], 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        //Grupo 3, tit
        $pdf->Ln(6);
        $pdf->SetFont('helvetica','B',11);
        $pdf->Cell(0, 0, 'Ubicación Geográfica Censo INE 2001', 1, 0, 'L', 0, '', 0);

        $pdf->Ln(6);

        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['departamento']);
        $pdf->SetFont('helvetica','',10);
        $pdf->MultiCell(40, 0, 'Departamento ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $dep->getCodigo(), 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $dep->getLugar(), 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $pro = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['provincia']);
        $pdf->MultiCell(40, 0, 'Provincia ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $pro->getCodigo(), 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $pro->getLugar(), 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $mun = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['municipio']);
        $pdf->MultiCell(40, 0, 'Municipio ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $mun->getCodigo(), 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $mun->getLugar(), 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $can = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['canton']);
        $pdf->MultiCell(40, 0, 'Cantón ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $can->getCodigo(), 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $can->getLugar(), 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        $loc = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['localidad']);
        $pdf->MultiCell(40, 0, 'Localidad ', 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(30, 0, $loc->getCodigo(), 1, 'L', 0, 0, '', '', true, 0, false, true, 10, 'T');
        $pdf->MultiCell(118, 0, $loc->getLugar(), 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T');

        //grupo firmas
        $pdf->Ln(3);
        $pdf->SetFont('helvetica','',8);
        $pdf->MultiCell(0, 0, 'Quienes firmamos  el presente documento declaramos que los datos son verídicos y auténticos. De no serlo nos someteremos a las sanciones establecidas por Ley.', 1, 'L', 0, 1, '', '', true, 0, false, true, 10, 'T'); 
        
        $pdf->Ln(3);
        $pdf->MultiCell(94, 20, 'Responsable de I.T.T.', 1, 'L', 0, 0, '', '', true, 0, false, true, 20, 'M');      
        $pdf->MultiCell(94, 20, 'Nombre y Firma', 1, 'C', 0, 1, '', '', true, 0, false, true, 20, 'B');

        $pdf->MultiCell(94, 20, 'VoBo Responsable de D.D.E.', 1, 'L', 0, 0, '', '', true, 0, false, true, 20, 'M');      
        $pdf->MultiCell(94, 20, 'Nombre y Firma', 1, 'C', 0, 1, '', '', true, 0, false, true, 20, 'B');

        $pdf->MultiCell(94, 20, 'Lugar y fecha', 1, 'L', 0, 0, '', '', true, 0, false, true, 20, 'M');      
        $pdf->MultiCell(94, 20, '', 1, 'C', 0, 1, '', '', true, 0, false, true, 20, 'B');

        $pdf->SetFont('helvetica','',7);
        $pdf->Cell(0, 0, date('d/m/Y H:i:s'), 0, 0, 'R', 0, '', 0);

        $pdf->Output('FormularioNuevaUbicacion'.'.pdf', 'D');
    }    
    /**
     * ***********************************************************************************************
     * Funciones de consultas para obtención de datos
     */

    /**
     * Obtención de listado de institutos de una local educativo 
     */
    public function obtieneListadoInstitutosLocal($codLe){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT ie
                                    FROM SieAppWebBundle:Institucioneducativa ie
                                    JOIN ie.leJuridicciongeografica ed
                                WHERE ed.id = :idLe 
                                    AND ie.institucioneducativaTipo in (:idTipo)')
        ->setParameter('idLe', $codLe)
        ->setParameter('idTipo', array(7, 8, 9));        
        $listado = $query->getResult(); 
        return $listado;
    }

    /* 
     * Obtiene Departamentos de Bolivia 
    */  
    public function obtieneDepartamentos(){
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->createQuery('SELECT lu
                                     FROM SieAppWebBundle:LugarTipo lu
                                    WHERE lu.lugarNivel = :lugar
                                      AND lu.paisTipoId = :pais
                                 ORDER BY lu.lugar ')
                        ->setParameter('lugar', 1)
                        ->setParameter('pais', 1);        
        $dep = $query->getResult();         

        $depArray = array();
        foreach ($dep as $de) {
            $depArray[$de->getId()] = $de->getLugar();
        }
        return $depArray;
    }

    /* 
     * Obtiene Tipo de Instituto (Tecnico/Tecnologico)
    */  
    public function obtieneTipoInstituto(){
        $em = $this->getDoctrine()->getManager();  
        $tiposArray = array();
        $query = $em->createQuery(
                            'SELECT DISTINCT iet.id,iet.descripcion
                                        FROM SieAppWebBundle:InstitucioneducativaTipo iet
                                       WHERE iet.id in (:id)
                                    ORDER BY iet.id ASC'
            )->setParameter('id', array(7,8));
            $tipos = $query->getResult();
            for($i=0;$i<count($tipos);$i++){
                $tiposArray[$tipos[$i]['id']] = $tipos[$i]['descripcion'];
            }
            $tiposArray[0] = 'Educación Superior Técnica y Tecnológica';

        return $tiposArray;
    }

    /* 
     * Obtiene Estado de Instituto (Tecnico/Tecnologico)
    */  
    public function obtieneEstadoInstitucion(){
        $em = $this->getDoctrine()->getManager();
        $estadosArray = array();
        $query = $em->createQuery(
                        'SELECT DISTINCT eie.id,eie.estadoinstitucion
                                    FROM SieAppWebBundle:EstadoinstitucionTipo eie
                                   WHERE eie.id in (:id)
                                ORDER BY eie.id ASC')
                        ->setParameter('id', array(10,19));
            $estados = $query->getResult();
            $estadosArray = array();
            for($i=0;$i<count($estados);$i++){
                $estadosArray[$estados[$i]['id']] = $estados[$i]['estadoinstitucion'];
            }
        return $estadosArray;    
    }
    
    /***
     * Obtiene Historial del instituto
     */
     function obtieneHistorial($idRie){
        $em = $this->getDoctrine()->getManager(); 
        $query = $em->createQuery('SELECT a
                                     FROM SieAppWebBundle:TtecInstitucioneducativaHistorico a 
                                     JOIN a.institucioneducativa b
                                    WHERE b.id = :idRie
                                 ORDER BY a.fechaResolucion DESC');                       
        $query->setParameter('idRie', $idRie);
        $historicos = $query->getResult();         
        return $historicos;        
    }

    /***
     * Obtiene un array con datos del listado de carreras autorizadas
     */
    public function listadoOfertaAcademica($idRie){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "SELECT autorizado.id AS id, carrera.id AS idcarrera, carrera.nombre AS carr 
                    FROM ttec_institucioneducativa_carrera_autorizada AS autorizado
                    INNER JOIN ttec_carrera_tipo AS carrera ON autorizado.ttec_carrera_tipo_id = carrera.id 
                    INNER JOIN institucioneducativa AS instituto ON autorizado.institucioneducativa_id = instituto.id 
                    INNER JOIN ttec_area_formacion_tipo AS area ON carrera.ttec_area_formacion_tipo_id = area.id
                    WHERE instituto.id = '".$idRie."'  AND area.id <> 200 ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params); 
        $listado = $stmt->fetchAll();

        $list = array();                                  
        foreach($listado as $li){
            $query = $em->createQuery('SELECT a
                                         FROM SieAppWebBundle:TtecResolucionCarrera a 
                                        WHERE a.ttecInstitucioneducativaCarreraAutorizada = :idCaAutorizada 
                                     ORDER BY a.fecha DESC');                       
            $query->setParameter('idCaAutorizada', $li['id']);
            $query->setMaxResults(1);
            $resolucion = $query->getResult();   

            $list[] = array(
                                'id' => $li['id'],
                                'idcarrera' => $li['idcarrera'],
                                'carrera' => $li['carr'],
                                'idresolucion' => ($resolucion) ? $resolucion[0]->getId():"0",
                                'resolucion' => ($resolucion) ? $resolucion[0]->getNumero():" ",
                                'fecharesol' => ($resolucion) ? $resolucion[0]->getFecha():" ",
                                'nivelformacion' => ($resolucion) ? $resolucion[0]->getNivelTipo()->getNivel():" ",
                                'tiempoestudio' => ($resolucion) ? $resolucion[0]->getTiempoEstudio():" ",
                                'regimen' =>  ($resolucion) ? $resolucion[0]->getTtecRegimenEstudioTipo()->getRegimenEstudio():" ",
                                'cargahoraria' => ($resolucion) ? $resolucion[0]->getCargaHoraria():" ",
                                'operacion' => ($resolucion) ? $resolucion[0]->getOperacion():" "
                            );
        }                                    
        return $list;
    } 

    /***
     * Obtiene un array con datos del listado de carreras autorizadas
     */
    public function listadoCursosCapacitacion($idRie){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "SELECT autorizado.id AS id, carrera.id AS idcarrera, carrera.nombre AS carr, autorizado.es_vigente AS vigente 
                    FROM ttec_institucioneducativa_carrera_autorizada AS autorizado
                    INNER JOIN ttec_carrera_tipo AS carrera ON autorizado.ttec_carrera_tipo_id = carrera.id 
                    INNER JOIN institucioneducativa AS instituto ON autorizado.institucioneducativa_id = instituto.id 
                    INNER JOIN ttec_area_formacion_tipo AS area ON carrera.ttec_area_formacion_tipo_id = area.id
                    WHERE instituto.id = '".$idRie."' AND area.id = 200  ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params); 
        $listado = $stmt->fetchAll();

        $list = array();                                  
        foreach($listado as $li)
        {
            $query = $em->createQuery('SELECT a
                                         FROM SieAppWebBundle:TtecResolucionCarrera a 
                                        WHERE a.ttecInstitucioneducativaCarreraAutorizada = :idCaAutorizada 
                                     ORDER BY a.fecha DESC');                       
            $query->setParameter('idCaAutorizada', $li['id']);
            $query->setMaxResults(1);
            $resolucion = $query->getResult();   

            $list[] = array(
                                'id' => $li['id'],
                                'idcarrera' => $li['idcarrera'],
                                'carrera' => $li['carr'],
                                'vigente' => $li['vigente'],
                                'idresolucion' => ($resolucion) ? $resolucion[0]->getId():"0",
                                'resolucion' => ($resolucion) ? $resolucion[0]->getNumero():" ",
                                'fecharesol' => ($resolucion) ? $resolucion[0]->getFecha():" ",
                                'nivelformacion' => ($resolucion) ? $resolucion[0]->getNivelTipo()->getNivel():" ",
                                'tiempoestudio' => ($resolucion) ? $resolucion[0]->getTiempoEstudio():" ",
                                'regimen' =>  ($resolucion) ? $resolucion[0]->getTtecRegimenEstudioTipo()->getRegimenEstudio():" ",
                                'cargahoraria' => ($resolucion) ? $resolucion[0]->getCargaHoraria():" ",
                                'operacion' => ($resolucion) ? $resolucion[0]->getOperacion():" "
                            );
        }                                    
        return $list;
    }    
    
    /**
     * Obtiene Datos de la ubicacion geografica a partir del codigo LE
     */
    public function datoslocal($idLe){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "SELECT COUNT(ie.id) AS instituto,lt4.lugar AS departamento, lt3.lugar AS provincia, lt2.lugar AS municipio, lt1.lugar AS canton, lt.lugar AS localidad, jg.*, lt4.codigo AS cod_dep, lt3.codigo AS cod_pro, lt2.codigo AS cod_mun, lt1.codigo AS cod_can, lt.codigo AS cod_loc
                    FROM jurisdiccion_geografica jg 
               LEFT JOIN lugar_tipo AS lt ON lt.id = jg.lugar_tipo_id_localidad
               LEFT JOIN lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
               LEFT JOIN lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
               LEFT JOIN lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
               LEFT JOIN lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
               LEFT JOIN institucioneducativa ie
                      ON jg.id = ie.le_juridicciongeografica_id
                   WHERE jg.id = ".$idLe."
                    AND juridiccion_acreditacion_tipo_id = 1
                GROUP BY jg.id, lt.lugar, lt1.lugar, lt2.lugar, lt3.lugar, lt4.lugar, lt4.codigo, lt3.codigo, lt2.codigo, lt1.codigo, lt.codigo 
                ORDER BY instituto ASC";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $entities = $stmt->fetchAll();  
        return $entities;      
    }
    
}

