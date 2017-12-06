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

        $formInstitucioneducativa       = $this->createSearchFormInstitucioneducativa();
        $formInstitucioneducativaId     = $this->createSearchFormInstitucioneducativaId();
		$formInstitucioneducativaTipo   = $this->createSearchFormInstitucioneducativaTipo();
		$formInstitucioneducativaEstado = $this->createSearchFormInstitucioneducativaEstado();


        return $this->render('SieRieBundle:ConsultaPublica:search.html.twig', array(
                                                                            			'formInstitucioneducativa' => $formInstitucioneducativa->createView(), 
                                                                                        'formInstitucioneducativaId' => $formInstitucioneducativaId->createView(),
                                                                                        'formInstitucioneducativaTipo' => $formInstitucioneducativaTipo->createView(),
                                                                                        'formInstitucioneducativaEstado' => $formInstitucioneducativaEstado->createView()));
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
        switch($form['tipo_search'])
        {
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
                        
                    switch($form['departamento'])
                    {
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
                
                    switch($form['departamento'])
                    {
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

        if (!$entities) {
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
        return $this->render('SieRieBundle:ConsultaPublica:show.html.twig', array('entity' => $entity, 'ofertas' => $ofertas, 'historiales' => $historiales));
    }

    /* 
     * Obtiene Departamentos de Bolivia 
    */  
    public function obtieneDepartamentos(){
        $em = $this->getDoctrine()->getManager();
        $depArray = array();        
        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' => 1));
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
                ORDER BY eie.id ASC'
            )->setParameter('id', array(10,19));
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
     public function listadoOfertaAcademica($idRie, $esVigente){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT a
                                     FROM SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada a 
                                     JOIN a.institucioneducativa b
                                     JOIN a.ttecCarreraTipo c
                                    WHERE b.id = :idRie
                                      AND a.esVigente = :esVigente
                                    ORDER BY c.nombre ASC');                       
        $query->setParameter('idRie', $idRie);
        $query->setParameter('esVigente', $esVigente);
        $datos = $query->getResult(); 

        $list = array();                                  
        foreach($datos as $dato)
        {
            $query = $em->createQuery('SELECT a
                                         FROM SieAppWebBundle:TtecResolucionCarrera a 
                                        WHERE a.ttecInstitucioneducativaCarreraAutorizada = :idCaAutorizada 
                                     ORDER BY a.id DESC');                       
            $query->setParameter('idCaAutorizada', $dato->getId());
            $query->setMaxResults(1);
            $resolucion = $query->getResult();   

            $list[] = array(
                                'id' => $dato->getId(),
                                'carrera' => $dato->getTtecCarreraTipo()->getNombre(),
                                'esenviado' => $dato->getEsEnviado(),
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

}
