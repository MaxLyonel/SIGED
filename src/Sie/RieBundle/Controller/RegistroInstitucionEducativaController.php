<?php

namespace Sie\RieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\EspecialAreaTipo; //luego borrar
use Sie\AppWebBundle\Entity\TtecAreaFormacionTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaAreaEspecialAutorizado;
use Sie\AppWebBundle\Entity\InstitucioneducativaNivelAutorizado;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaAreaFormacionAutorizado;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaSede;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaHistorico;
use Sie\AppWebBundle\Form\InstitucioneducativaType;


/**
 * Institucioneducativa controller.
 *
 */
class RegistroInstitucionEducativaController extends Controller {

      public $session;
      /**
       * the class constructor
       */
      public function __construct() {
          //init the session values
          $this->session = new Session();
      }

     

    /**
     * Muestra formulario de Busqueda de la institución educativa
     */
    public function indexAction(Request $request) { 
        $em = $this->getDoctrine()->getManager();
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // Creacion de formularios de busqueda por codigo rie o nombre de institucion educativa
        $formInstitucioneducativa = $this->createSearchFormInstitucioneducativa();
        $formInstitucioneducativaId = $this->createSearchFormInstitucioneducativaId();
        return $this->render('SieRieBundle:RegistroInstitucionEducativa:search.html.twig', array('formInstitucioneducativa' => $formInstitucioneducativa->createView(), 'formInstitucioneducativaId' => $formInstitucioneducativaId->createView()));
    }

    /**
     * Muestra listado de institutos técnicos/tecnológicos
     */    
    public function listActionAntes(Request $request){

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');        
        $id_lugar = $sesion->get('roluserlugarid');
        $id_rol = $sesion->get('roluser');        
        $em = $this->getDoctrine()->getManager();

        $lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($id_lugar);
        

        $query = $em->createQuery('SELECT se
            FROM SieAppWebBundle:TtecInstitucioneducativaSede se
            INNER JOIN se.institucioneducativa ie 
            WHERE ie.institucioneducativaTipo in (:idTipo)
            AND ie.estadoinstitucionTipo in (:idEstado)
            AND se.estado = :estadoSede
            ORDER BY ie.id ')
            ->setParameter('idTipo', array(7, 8, 9,11,12,13))
            ->setParameter('idEstado', 10)
            ->setParameter('estadoSede', TRUE);
        
        $entities = $query->getResult();
        //dump($entities);die;
        return $this->render('SieRieBundle:RegistroInstitucionEducativa:list.html.twig', array(
            'entities' => $entities,
            'lugarUsuario' => intval($lugar->getCodigo())
        ));
    }
        /**
     * Muestra listado de institutos técnicos/tecnológicos con fechas de caducidad
     */    
    public function listAction(Request $request){

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');        
        $id_lugar = $sesion->get('roluserlugarid');
       // dump($id_usuario);
        $id_rol = $sesion->get('roluser');        
        $em = $this->getDoctrine()->getManager();
        // para tecnicos departamentales 
        
        $lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($id_lugar);
        $departamento = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('codigo' => $lugar->getCodigo(), 'lugarNivel'=> 1));
        
       /* $regionesArray = array();
        $region = $em->getRepository('SieAppWebBundle:TtecEncargadoRegion')->findOneBy(array('usuarioId' => $id_usuario));
        
        if(!$region->getEstado())
            $regionesArray =  explode(',', $region->getRegion()); //los departamentos asignados
        else*/
            $regionesArray = array(2,3,4,5,6,7,8,9,10);

        if($id_rol==7)
            $regionesArray = str_split($departamento->getId());  //para el departamental

        $qb = $em->createQueryBuilder();
        $qb
                ->select("ie.id, se.sede, iea.id as acreditacion, lt5.lugar, ie.institucioneducativa, ie.nroResolucion, ie.fechaResolucion, dt.id as dependenciaId, dt.dependencia, it.descripcion, jg.id as local_ie ")
                ->from('SieAppWebBundle:TtecInstitucioneducativaSede', 'se')
                ->innerjoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = se.institucioneducativa')
                ->innerJoin('SieAppWebBundle:JurisdiccionGeografica','jg','WITH','jg.id = ie.leJuridicciongeografica') 
                ->innerJoin('SieAppWebBundle:DependenciaTipo','dt','WITH','dt.id = ie.dependenciaTipo') 
                ->innerJoin('SieAppWebBundle:InstitucioneducativaAcreditacionTipo','iea','WITH','iea.id = ie.institucioneducativaAcreditacionTipo') 
                ->innerJoin('SieAppWebBundle:InstitucioneducativaTipo','it','WITH','it.id = ie.institucioneducativaTipo') 
                ->innerJoin('SieAppWebBundle:LugarTipo','lt1','WITH','lt1.id = jg.lugarTipoLocalidad')        
                ->innerJoin('SieAppWebBundle:LugarTipo','lt2','WITH','lt2.id = lt1.lugarTipo')        
                ->innerJoin('SieAppWebBundle:LugarTipo','lt3','WITH','lt3.id = lt2.lugarTipo')       
                ->innerJoin('SieAppWebBundle:LugarTipo','lt4','WITH','lt4.id = lt3.lugarTipo')       
                ->innerJoin('SieAppWebBundle:LugarTipo','lt5','WITH','lt5.id = lt4.lugarTipo') 
                ->where('ie.institucioneducativaTipo in (:idTipo)')
                ->andwhere('ie.estadoinstitucionTipo in (:idEstado)')
                ->andwhere('se.estado = :estadoSede')
                ->andwhere('lt5.id in (:regiones)')
                ->setParameter('idTipo', array(7, 8, 9,11,12,13))
                ->setParameter('idEstado',10)
                ->setParameter('estadoSede', TRUE)
                ->setParameter('regiones', $regionesArray)
                
        ;
        $entities =  $qb->getQuery()->getResult();
        //dump($entities);die;
        $lista = array();
        
        for($i=0; $i<count($entities); $i++){
            $alerta = 0;
            $fecha_alerta = '';
            $fecha_inicio = '';
            $fecha_alerta_leve = '';
            $vcmto = "";
            if($entities[$i]["dependenciaId"] == 3 and $entities[$i]["acreditacion"]==2){ 
              
                $id =  $entities[$i]["id"];
                $ratificacion = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaHistorico')->createQueryBuilder('h')
                ->select('r.fechaFin')
                ->innerJoin('SieAppWebBundle:TtecInstitucioneducativaRatificacion', 'r', 'with', 'h.id = r.ttecInstitucioneducativaHistorico')
                ->where ('h.institucioneducativa = '. $id)
                ->orderBy('r.fechaFin','DESC')
                ->getQuery()
                ->getResult();
                $fechaSistema =  $entities[$i]["fechaResolucion"];
                
                $fecha = date("Y-m-d",strtotime($fechaSistema->format('Y-m-d')."+ 6 year")); 
                if($ratificacion){
                    $fechaSistema =  $ratificacion[0]["fechaFin"];
                    $fecha = date("Y-m-d",strtotime($fechaSistema->format('Y-m-d')."+ 6 year")); 
                   // $fecha = $ratificacion[0]["fechaFin"];
                }   
                   // $fecha_6nios = date("Y-m-d",strtotime($fecha->format('Y-m-d')."+ 6 year")); 
                   // dump($fecha_6nios);
                    //$fecha_inicio = date("Y-m-d",strtotime($fecha_6nios->format('Y-m-d')."- 6 month")); // iniciar tramite 180 dias antes
                    $fecha_inicio = date("Y-m-d",strtotime($fecha."- 6 month")); // iniciar tramite 180 dias antes
                    //dump($fecha_inicio);die;
                    $fecha_alerta = date("Y-m-d",strtotime($fecha."- 7 month"));  // alertar 210 dias antes
                    $fecha_alerta_leve = date("Y-m-d",strtotime($fecha."- 8 month"));  // alertar 240 dias antes
                    $hoy = Date("Y-m-d");
                    
                    if($hoy >= $fecha_alerta_leve && $hoy <= $fecha_alerta)
                        $alerta = 1; //alerta naranja leve
                    
                    if($hoy > $fecha_alerta)
                        $alerta = 2; //alerta roja 
                    if($hoy > $fecha)
                        $alerta = 3; //alerta roja 
                    if($alerta>0)
                      $vcmto = "Iniciar ".$fecha_alerta_leve;
            }
            $lista[$i] = array(
                'id' => $entities[$i]["id"],
                'departamento' => $entities[$i]["lugar"],
                'denominacion'=> $entities[$i]["institucioneducativa"],
                'acreditacion'=> $entities[$i]["acreditacion"],
                'sede' => $entities[$i]["sede"],
                'rm'=> $entities[$i]["nroResolucion"],
                'fecha'=>$entities[$i]["fechaResolucion"],
                'caracter'=> $entities[$i]["dependencia"],
                'tipo'=>$entities[$i]["descripcion"],
                'codigoLe' => $entities[$i]["local_ie"],
                'alerta' => $alerta,
                'vcmto' =>$vcmto,
            );
            
        }
       //dump($lista);die;    
        
        //die;
        return $this->render('SieRieBundle:RegistroInstitucionEducativa:list.html.twig', array(
            'entities' => $lista,
            'lugarUsuario' => ''
        ));
    }

     /**
     * Muestra formulario de mostrar de Institución Educativa 
     */
    public function showAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)){
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));
        $niveles = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa'=>$entity->getId()));
        $OfertaController = new  OfertaAcademicaController();
        $OfertaController->setContainer($this->container);
        $carreras = $OfertaController->listadoOfertaAcademica($entity->getId());
        $CursoController = new  CursoCapacitacionController();
        $CursoController->setContainer($this->container);
        $cursos = $CursoController->listadoCursosCapacitacion($entity->getId());
       //dump($carreras);die;
        return $this->render('SieRieBundle:RegistroInstitucionEducativa:show.html.twig', array('entity' => $entity, 'niveles' => $niveles,'carreras' => $carreras,'cursos' => $cursos));
    }

    /**
     * Formulario de Busqueda por Nombre de ITT 
     */
     private function createSearchFormInstitucioneducativa() {
    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('rie_result'))
    			->add('tipo_search', 'hidden', array('data' => 'institucioneducativa'))
                ->add('institucioneducativa', 'text', array('required' => true, 'invalid_message' => 'Campo obligatorio'))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * Formulario de Busqueda por Codigo RIE
     */    
    private function createSearchFormInstitucioneducativaId() {

    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('rie_result'))
    	->add('tipo_search', 'hidden', array('data' => 'institucioneducativaId'))
    	->add('institucioneducativaId', 'text', array('required' => true,'invalid_message' => 'Campo obligatorio', 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
    	->add('buscarId', 'submit', array('label' => 'Buscar'))
    	->getForm();
    	return $form;
    }

     /**
     * Resultado de Busqueda por Codigo RIE / Nombre ITT
     */ 
     public function findinstitucionAction(Request $request){
    	$form = $request->get('form');
        $em = $this->getDoctrine()->getManager();

    	if ($form['tipo_search'] == 'institucioneducativa'){
    		$query = $em->createQuery(
                                        'SELECT ie
                                           FROM SieAppWebBundle:Institucioneducativa ie
                                          WHERE UPPER(ie.institucioneducativa) LIKE :id
                                            AND ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
                                       ORDER BY ie.id')
    		                ->setParameter('id','%' . strtoupper($form['institucioneducativa']) . '%')
                        ->setParameter('ieAcreditacion', 2);
    	}else{
    		$query = $em->createQuery(
    				'SELECT ie
                            FROM SieAppWebBundle:Institucioneducativa ie
                            WHERE ie.id = :id
                            and ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
                            ORDER BY ie.id')
    		                ->setParameter('id', $form['institucioneducativaId'])
                            ->setParameter('ieAcreditacion', 2);
    	}

    	$entities = $query->getResult();

        if(!$entities){
        	$this->get('session')->getFlashBag()->add('msgSearch', 'No se encontro la información...');
        	$formInstitucioneducativa = $this->createSearchFormInstitucioneducativa();
        	$formInstitucioneducativaId = $this->createSearchFormInstitucioneducativaId();
        	return $this->render('SieRieBundle:RegistroInstitucionEducativa:search.html.twig', array('formInstitucioneducativa' => $formInstitucioneducativa->createView(), 'formInstitucioneducativaId' => $formInstitucioneducativaId->createView()));
        }

        return $this->render('SieRieBundle:RegistroInstitucionEducativa:resultinseducativa.html.twig', array('entities' => $entities));
    }

    /**
     * Muestra formulario de registro de Institución Educativa 
     */
    public function newAction(Request $request){
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)){
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        // Obteniendo array de Dependencia(Caracter Juridico) | Estado | Tipo | Nivel | AreaFormacion
        $dependenciasArray = $this->obtieneDependenciaArray();
        $tiposArray        = $this->obtieneTipoInstitucionArray();                                          
        $nivelesArray      = $this->obtieneNivelArray();                        
        $datoSede          = $this->obtieneDatosSedeArray($request->get('idRie'), 'new');

        if($datoSede == 0){ return $this->redirect($this->generateUrl('rie_list')); }

        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('rie_create'))
        ->add('idRieSede', 'hidden', array('data' => $datoSede['codSede']))
        ->add('institucionEducativa', 'text', array('label' => 'Denominación del Instituto','required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '150', 'style' => 'text-transform:uppercase')))
        ->add('fechaResolucion', 'text', array('label' => 'Fecha de resolución','required' => true, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
        ->add('fechaResolucionFin', 'text', array('label' => 'Fecha de resolución Fin', 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
        ->add('nroResolucion', 'text', array('label' => 'Resolución Ministerial Vigente','required' => true,'attr' => array('data-mask'=>'0000/0000', 'placeholder'=>'0000/YYYY', 'class' => 'form-control', 'autocomplete' => 'off', 'maxlength' => '44', 'style' => 'text-transform:uppercase')))
        ->add('dependenciaTipo', 'choice', array('label' => 'Carácter Jurídico', 'disabled' => false,'choices' => $dependenciasArray, 'attr' => array('class' => 'form-control','data-bind'=>'selectedOptions: dependencia')))
        ->add('institucionEducativaTipo', 'choice', array('label' => 'Tipo de Institución', 'disabled' => false,'choices' => $tiposArray, 'attr' => array('class' => 'form-control')))
        ->add('obsRue', 'text', array('label' => 'Observación', 'required' => false, 'attr' => array('class' => 'form-control', 'maxlength'=>'190')))
        ->add('leJuridicciongeograficaId', 'text', array('label' => 'Código LE','required' => true, 'attr' => array('listactplaceholder'=>'########', 'class' => 'form-control', 'pattern' => '[0-9]{8,17}', 'maxlength' => '8')))
        ->add('departamento', 'text', array('label' => 'Departamento', 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('provincia', 'text', array('label' => 'Provincia', 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('municipio', 'text', array('label' => 'Municipio', 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('canton', 'text', array('label' => 'Cantón', 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('localidad', 'text', array('label' => 'Localidad', 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('zona', 'text', array('label' => 'Zona', 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('direccion', 'text', array('label' => 'Dirección', 'disabled' => true, 'attr' => array('class' => 'form-control')))
        ->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices'=>$nivelesArray,  'required' => false  , 'multiple' => true,'expanded' => true,'attr' => array('class' => 'form-control')))
        ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
    	return $this->render('SieRieBundle:RegistroInstitucionEducativa:new.html.twig', array('form' => $form->getForm()->createView(), 'datoSede' => $datoSede));
    }

    /**
     * Guarda datos del formulario registro de Institución Educativa
     */
    public function createAction(Request $request) {
    	try {

            $sesion = $request->getSession();
            $id_usuario = $sesion->get('userId');
            if (!isset($id_usuario)){
                return $this->redirect($this->generateUrl('login'));
            }

    		$em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
    		$form = $request->get('form');
            
            $form['fechaResolucion']=date("Y-m-d",strtotime($form['fechaResolucion']));
            
            $buscar_institucion = $this->validarInstitucionEducativa($form);
    		if($buscar_institucion != 99){ // En caso de existir el instituto no se puede guardar
    			$this->get('session')->getFlashBag()->add('msgSearch', 'La institución educativa se encuentra registrada, con código(s) RIE : '.$buscar_institucion);
    			return $this->redirect($this->generateUrl('rie_list'));
            }

            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_nivel_autorizado');")->execute();
    		$query = $em->getConnection()->prepare('SELECT get_genera_codigo_ue(:codle)');
    		$query->bindValue(':codle', $form['leJuridicciongeograficaId']);
    		$query->execute();
    		$codigoue = $query->fetchAll();

    		$entity = new Institucioneducativa();
    		$entity->setId($codigoue[0]["get_genera_codigo_ue"]);
            $ieducativatipo = $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->findOneById($form['institucionEducativaTipo']);

            //datos del instituto tecnico/tecnologico
    		$entity->setInstitucioneducativa(mb_strtoupper($form['institucionEducativa'], 'utf-8'));
    		$entity->setFechaResolucion(new \DateTime($form['fechaResolucion']));
            if(array_key_exists('fechaResolucionFin',$form))
            {
                $entity->setFechaResolucionFin(new \DateTime($form['fechaResolucionFin']));
            }
    		$entity->setFechaCreacion(new \DateTime('now'));
            $entity->setFechaRegistro(new \DateTime('now'));
    		$entity->setNroResolucion(mb_strtoupper($form['nroResolucion'], 'utf-8'));
    		$entity->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($form['dependenciaTipo']));
            $entity->setConvenioTipo($em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById(0));
    		$entity->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById(10));
    		$entity->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->findOneById($form['institucionEducativaTipo']));
            $entity->setObsRue(mb_strtoupper($form['obsRue'], 'utf-8'));
    		$entity->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($form['leJuridicciongeograficaId']));
    		$entity->setOrgcurricularTipo($ieducativatipo->getOrgcurricularTipo());
            $entity->setInstitucioneducativaAcreditacionTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaAcreditacionTipo')->find(3));
            $em->persist($entity);
    		$em->flush();

            //datos de sede y subsede del instituto tt
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_institucioneducativa_sede');")->execute();
            switch($form['idRieSede']){
                case 0: //caso Sede
                        $sede = new TtecInstitucioneducativaSede();
                        $sede->setInstitucioneducativa($entity);
                        $sede->setSede($codigoue[0]["get_genera_codigo_ue"]); 
                        $sede->setEstado(true);
                        $sede->setFechaRegistro(new \DateTime('now'));
                        $em->persist($sede);
                        $em->flush();
                    break;

                default: //caso Subsede
                        $sede = new TtecInstitucioneducativaSede();
                        $sede->setInstitucioneducativa($entity);
                        $sede->setSede($form['idRieSede']); 
                        $sede->setEstado(true);
                        $sede->setFechaRegistro(new \DateTime('now'));
                        $em->persist($sede);
                        $em->flush();
                    break;
            }

            //eliminando niveles
            $niveles = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa' => $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($entity->getId())));
            foreach($niveles as $nivel){
                $em->remove($nivel);            
            }
            
            //adicionando niveles
            $niveles = (isset($form['nivelTipo']))?$form['nivelTipo']:array();
            for($i=0;$i<count($niveles);$i++){
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_institucioneducativa_area_formacion_autorizado');")->execute();
                $nivel = new InstitucioneducativaNivelAutorizado();
                $nivel->setFechaRegistro(new \DateTime('now'));
                $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($niveles[$i]));
                $nivel->setInstitucioneducativa($entity);
                $em->persist($nivel);
            }
            $em->flush();
            
            //elimina areas de formacion
            $areasFormElim = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaAreaFormacionAutorizado')->findBy(array('institucioneducativa' => $entity->getId()));
            if($areasFormElim){
                foreach($areasFormElim as $area){
                    $em->remove($area);
                }
                $em->flush();
            }

            $areas = (isset($form['areaFormacionTipo']))?$form['areaFormacionTipo']:array();
            for($i=0;$i<count($areas);$i++){
                $areaf = new TtecInstitucioneducativaAreaFormacionAutorizado();
                $areaf->setFechaRegistro(new \DateTime('now'));
                $areaf->setInstitucioneducativa($entity);
                $areaf->setTtecAreaFormacionTipo($em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findOneById($areas[$i])); 
                $em->persist($areaf);
            }    
            $em->flush();

                // Try and commit the transaction
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('mensaje', 'La institución educativa fue registrada correctamente: '  .  $entity->getId());
                return $this->redirect($this->generateUrl('rie_list'));
            }catch (Exception $ex){
                $em->getConnection()->rollback();
                $this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar la institución educativa.');
                return $this->redirect($this->generateUrl('rie_new'));
            }
    }

    /**
     * Muestra formulario de edicion de Institución Educativa 
     */
     public function editAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)){
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));

        if(!$entity){
            throw $this->createNotFoundException('No se puede encontrar la Institución Educativa.');
        }

        $dependenciasArray = $this->obtieneDependenciaArray();
        $estadosArray      = $this->obtieneEstadoInstitucionArray();
        $tiposArray        = $this->obtieneTipoInstitucionArray();                                          
        $nivelesArray      = $this->obtieneNivelArray(); 
        $areafArray        = $this->obtieneAreaFormacionArray($entity->getInstitucioneducativaTipo()->getId());
        $nivelesInstitucionArray = $this->obtieneInstitucionNivelArray($entity->getId());
        $areformInstitucionArray = $this->obtieneInstitucionAreaFormArray($entity->getId());
        $datoSede                = $this->obtieneDatosSedeArray($request->get('idRie'), 'edit');

        if($datoSede == 0){ return $this->redirect($this->generateUrl('rie_list')); }

        $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('rie_update'))
                    ->add('idRie', 'hidden', array('data' => $entity->getId()))
                    ->add('idRieSede', 'hidden', array('data' => $datoSede['codSede']))
                    ->add('rie', 'text', array('label' => 'Código RIE', 'data' => $entity->getId(), 'disabled' => true,'attr' => array('class' => 'form-control')))
                    ->add('institucionEducativa', 'text', array('label' => 'Denominación del Instituto', 'data' => $entity->getInstitucioneducativa(), 'required' => false, 'attr' => array('class' => 'form-control', 'maxlength' => '150', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                    ->add('fechaResolucion', 'text', array('label' => 'Fecha de resolucion', 'data' => $entity->getFechaResolucion() ? $entity->getFechaResolucion()->format('d-m-Y') : $entity->getFechaResolucion(), 'required' => true, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'dd-mm-yyyy')))
                    ->add('nroResolucion', 'text', array('label' => 'Resolución Ministerial Vigente', 'data' => $entity->getNroResolucion(), 'required' => false, 'attr' => array('data-mask'=>'0000/0000', 'placeholder'=>'0000/YYYY', 'class' => 'form-control',  'autocomplete' => 'off', 'style' => 'text-transform:uppercase', 'maxlength' => '44')))
                    ->add('dependenciaTipo', 'choice', array('label' => 'Carácter Jurídico', 'disabled' => false, 'data' => $entity->getDependenciaTipo()->getId(), 'choices' => $dependenciasArray, 'attr' => array('class' => 'form-control')))
                    ->add('estadoInstitucionTipo', 'choice', array('label' => 'Estado', 'disabled' => false, 'data' => $entity->getEstadoinstitucionTipo()->getId(), 'choices' => $estadosArray, 'attr' => array('class' => 'form-control')))
                    ->add('institucionEducativaTipo', 'choice', array('label' => 'Tipo de Institución','disabled' => false, 'data' => $entity->getInstitucioneducativaTipo()->getId(), 'choices' => $tiposArray, 'attr' => array('class' => 'form-control')))        
                    ->add('obsRue', 'text', array('label' => 'Observación', 'data'=> $entity->getObsRue(), 'required' => false, 'attr' => array('class' => 'form-control')))
                    ->add('leJuridicciongeograficaId', 'text', array('label' => 'Codigo LE','required' => true, 'data' => $entity->getLeJuridicciongeografica()->getId(), 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{8,17}', 'maxlength' => '8')))
                    ->add('departamento', 'text', array('label' => 'Departamento', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                    ->add('provincia', 'text', array('label' => 'Provincia', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                    ->add('municipio', 'text', array('label' => 'Municipio', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                    ->add('canton', 'text', array('label' => 'Cantón', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                    ->add('localidad', 'text', array('label' => 'Localidad', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugar(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                    ->add('zona', 'text', array('label' => 'Zona', 'data' => $entity->getLeJuridicciongeografica()->getZona(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                    ->add('direccion', 'text', array('label' => 'Dirección', 'data' => $entity->getLeJuridicciongeografica()->getDireccion(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                    ->add('nivelTipo', 'choice', array('label' => 'Niveles', 'choices'=>$nivelesArray,  'required' => false  , 'multiple' => true,'expanded' => true,'data' => $nivelesInstitucionArray))
                    ->add('areaFormacionTipo', 'choice', array('label' => 'Area de Formación', 'choices'=>$areafArray,  'required' => false  , 'multiple' => true,'expanded' => true, 'data' => $areformInstitucionArray))
                    ->add('guardar', 'submit', array('label' => 'Guardar'));

        return $this->render('SieRieBundle:RegistroInstitucionEducativa:edit.html.twig', array('entity' => $entity, 'form' => $form->getForm()->createView(), 'datoSede' => $datoSede));
    }
    

    /**
     * Guarda datos de formulario de edicion de Institución Educativa 
     */
     public function updateAction(Request $request) {
    	$this->session = new Session();
    	$form = $request->get('form');

    	$em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try{
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_nivel_autorizado');")->execute();
            $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idRie']);
            $entity->setInstitucioneducativa(mb_strtoupper($form['institucionEducativa'], 'utf-8'));
            $entity->setFechaResolucion(new \DateTime($form['fechaResolucion']));
            $entity->setFechaModificacion(new \DateTime('now'));
            $entity->setNroResolucion(mb_strtoupper($form['nroResolucion'], 'utf-8'));
            $entity->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($form['dependenciaTipo']));
            $entity->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById($form['estadoInstitucionTipo']));
            $entity->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->findOneById($form['institucionEducativaTipo']));
            $entity->setObsRue(mb_strtoupper($form['obsRue'], 'utf-8'));
            $entity->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($form['leJuridicciongeograficaId']));
            $em->persist($entity);
            $em->flush();

            //eliminando niveles
            $niveles = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa' => $em->getRepository('SieAppWebBundle:Institucioneducativa')->findById($entity->getId())));
            foreach($niveles as $nivel){
                $em->remove($nivel);            
            }
            
            //adicionando niveles
            $niveles = (isset($form['nivelTipo']))?$form['nivelTipo']:array();
            for($i=0;$i<count($niveles);$i++){
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_institucioneducativa_area_formacion_autorizado');")->execute();
                $nivel = new InstitucioneducativaNivelAutorizado();
                $nivel->setFechaRegistro(new \DateTime('now'));
                $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($niveles[$i]));
                $nivel->setInstitucioneducativa($entity);
                $em->persist($nivel);
            }
            $em->flush();

            //elimina areas de formacion
            $areasFormElim = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaAreaFormacionAutorizado')->findBy(array('institucioneducativa' => $entity->getId()));
            if($areasFormElim){
                foreach($areasFormElim as $area){
                    $em->remove($area);
                }
                $em->flush();
            }

            $areas = (isset($form['areaFormacionTipo']))?$form['areaFormacionTipo']:array();
            for($i=0;$i<count($areas);$i++){
                $areaf = new TtecInstitucioneducativaAreaFormacionAutorizado();
                $areaf->setFechaRegistro(new \DateTime('now'));
                $areaf->setInstitucioneducativa($entity);
                $areaf->setTtecAreaFormacionTipo($em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findOneById($areas[$i])); 
                $em->persist($areaf);
            }    
            $em->flush();

            // Try and commit the transaction
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('mensaje', 'Los datos fueron modificados correctamente ');
            $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idRie']);
            return $this->redirect($this->generateUrl('rie_list'));

      }catch(Exception $e){
        $em->getConnection()->rollback();
        return $this->redirect($this->generateUrl('rie_list'));
      }
    }
    
    /**
     * Eliminación Lógica de Institucion Educativa (estado:eliminado)
     */
    public function deleteAction(Request $request){
        $em = $this->getDoctrine()->getManager();
       
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));
        
        if(!$entity){
            throw $this->createNotFoundException('No se puede encontrar la Institución Educativa.');
        }

        $nivelesArray      = $this->obtieneNivelArray(); 
        $areafArray        = $this->obtieneAreaFormacionArray($entity->getInstitucioneducativaTipo()->getId());
        $nivelesInstitucionArray = $this->obtieneInstitucionNivelArray($entity->getId());
        $areformInstitucionArray = $this->obtieneInstitucionAreaFormArray($entity->getId());
        $datoSede                = $this->obtieneDatosSedeArray($request->get('idRie'), 'edit');
       
        if($datoSede == 0){ return $this->redirect($this->generateUrl('rie_list')); }

        if($eliminar = $this->validarEliminacionInstituto($entity->getId())){ //opcion 1
            $this->get('session')->getFlashBag()->add('msgSearch', ' ¿ Esta seguro de eliminar el Instituto ?. Verifique los datos antes de realizar la acción');
        }else{ //opcion 0
            $this->get('session')->getFlashBag()->add('msgSearch', ' El instituto tiene subsedes ó tiene carreras autorizadas, no podrá eliminarlo. Consulte con el administrador');
        }
        //dump($request->get('idRie'));
        //dump($entity);die;
        $form = $this->createFormBuilder()
        //->setAction($this->generateUrl('rie_delete_itt'))
        ->add('idRie', 'hidden', array('data' => $entity->getId()))
        ->add('idRieSede', 'hidden', array('data' => $datoSede['codSede']))
        ->add('rie', 'text', array('label' => 'Código RIE', 'data' => $entity->getId(), 'attr' => array('class' => 'form-control', 'readonly' => true)))
        ->add('institucionEducativa', 'text', array('label' => 'Denominación del Instituto', 'data' => $entity->getInstitucioneducativa(), 'attr' => array('class' => 'form-control', 'readonly' => true)))
        ->add('fechaResolucion', 'text', array('label' => 'Fecha de resolucion', 'data' => $entity->getFechaResolucion() ? $entity->getFechaResolucion()->format('d-m-Y') : $entity->getFechaResolucion(), 'attr' => array('class' => 'form-control', 'readonly' => true)))
        ->add('nroResolucion', 'text', array('label' => 'Resolución Ministerial Vigente', 'data' => $entity->getNroResolucion(), 'attr' => array('data-mask'=>'0000/0000', 'placeholder'=>'0000/YYYY', 'class' => 'form-control', 'readonly' => true)))
        ->add('dependenciaTipo', 'text', array('label' => 'Carácter Jurídico', 'data' => $entity->getDependenciaTipo()->getDependencia(), 'attr' => array('class' => 'form-control', 'readonly' => true)))
        ->add('estadoInstitucionTipo', 'text', array('label' => 'Estado', 'data' => $entity->getEstadoinstitucionTipo()->getEstadoinstitucion(), 'attr' => array('class' => 'form-control', 'readonly' => true)))
        ->add('institucionEducativaTipo', 'text', array('label' => 'Tipo de Institución', 'data' => $entity->getInstitucioneducativaTipo()->getDescripcion(), 'attr' => array('class' => 'form-control', 'readonly' => true)))        
        ->add('obsRue', 'text', array('label' => 'Observación', 'data'=> $entity->getObsRue(), 'attr' => array('class' => 'form-control', 'readonly' => true)))
        ->add('leJuridicciongeograficaId', 'text', array('label' => 'Codigo LE', 'data' => $entity->getLeJuridicciongeografica()->getId(), 'attr' => array('class' => 'form-control', 'readonly' => true)))
        ->add('departamento', 'text', array('label' => 'Departamento', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar(),  'attr' => array('class' => 'form-control', 'readonly' => true)))
        ->add('provincia', 'text', array('label' => 'Provincia', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar(),  'attr' => array('class' => 'form-control', 'readonly' => true)))
        ->add('municipio', 'text', array('label' => 'Municipio', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugar(), 'attr' => array('class' => 'form-control', 'readonly' => true)))
        ->add('canton', 'text', array('label' => 'Cantón', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugar(), 'attr' => array('class' => 'form-control', 'readonly' => true)))
        ->add('localidad', 'text', array('label' => 'Localidad', 'data' => $entity->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugar(), 'attr' => array('class' => 'form-control', 'readonly' => true)))
        ->add('zona', 'text', array('label' => 'Zona', 'data' => $entity->getLeJuridicciongeografica()->getZona(), 'attr' => array('class' => 'form-control', 'readonly' => true)))
        ->add('direccion', 'text', array('label' => 'Dirección', 'data' => $entity->getLeJuridicciongeografica()->getDireccion(), 'attr' => array('class' => 'form-control', 'readonly' => true)))
        ->add('nivelTipo', 'choice', array('label' => 'Niveles', 'disabled' => true, 'choices'=>$nivelesArray,  'multiple' => true, 'expanded' => true,'data' => $nivelesInstitucionArray, 'attr' => array('class' => 'form-control', 'readonly' => true)))
        ->add('areaFormacionTipo', 'choice', array('label' => 'Area de Formación', 'disabled' => true, 'choices'=>$areafArray,  'required' => false  , 'multiple' => true,'expanded' => true, 'data' => $areformInstitucionArray));
        //->add('guardar', 'submit', array('label' => 'Eliminar Instituto'));
        return $this->render('SieRieBundle:RegistroInstitucionEducativa:delete.html.twig', array('entity' => $entity, 'form' => $form->getForm()->createView(), 'datoSede' => $datoSede, 'eliminar' => $eliminar));
    }

    /***
     * elimina el estado de ITT Estado = 11 (ELIMINADO)
     */
    public function deleteittAction(Request $request){
    	$em = $this->getDoctrine()->getManager();
        
        $estado = $em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById(11);

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idRie'));
        $entity->setEstadoinstitucionTipo($estado);
        $entity->setObsRue('ELIMINADO POR ERROR DE REGISTRO  usuario:'.$id_usuario);
        $em->persist($entity);
        $em->flush();
        return $this->redirect($this->generateUrl('rie_list'));
    }

    /***
     * Obtiene Arrays de Datos de la Sede del ITT
     */ 
    public function obtieneDatosSedeArray($idRie, $opcion){
        $em = $this->getDoctrine()->getManager();
        $sedeArray = array();
        switch($opcion){
            case 'new':
                switch($idRie){
                    case 0:
                        $sedeArray = array('titulo' => ' SEDE', 'codSede' => '', 'nombreSede' => '');
                    break;

                    default:
                        $datos = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($idRie);
                        if(!empty($datos))
                            $sedeArray = array('titulo' => ' SUBSEDE', 'codSede' => $datos->getId(), 'nombreSede' => $datos->getInstitucioneducativa());
                        else
                            return 0;
                    break;
                }    
                break;

            case 'edit':
                $datos = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaSede')->findOneBy(array('institucioneducativa' => $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($idRie)));
                
                if(!empty($datos)){
                    if($datos->getSede() != $idRie){
                        $dat = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($datos->getSede());
                        $sedeArray = array('titulo' => ' SUBSEDE', 'codSede' => $dat->getId(), 'nombreSede' => $dat->getInstitucioneducativa());                        
                    }else{
                        $sedeArray = array('titulo' => ' SEDE', 'codSede' => '', 'nombreSede' => '');
                    } 
                }
                else{
                    return 0;
                }
                break;
        }
        return $sedeArray;        
    }

    /***
     * Obtiene Arrays de Dependencias(Caracter Juridico)
     */ 
    public function obtieneDependenciaArray(){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                                'SELECT DISTINCT dt.id,dt.dependencia
                                    FROM SieAppWebBundle:DependenciaTipo dt
                                    WHERE dt.id in (:id)
                                    ORDER BY dt.id ASC'
                                )->setParameter('id', array(1,2,3));
                                $dependencias = $query->getResult();
                                $dependenciasArray = array();
                                for($i=0;$i<count($dependencias);$i++){
                                    $dependenciasArray[$dependencias[$i]['id']] = $dependencias[$i]['dependencia'];
                                }
        return $dependenciasArray;
    }
    
    /***
     * Obtiene Arrays de Estado de la Institucion
     */ 
    public function obtieneEstadoInstitucionArray(){
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
                'SELECT DISTINCT et.id,et.estadoinstitucion
                    FROM SieAppWebBundle:EstadoinstitucionTipo et
                    WHERE et.id in (:id)
                    ORDER BY et.id ASC'
                )->setParameter('id', array(10,19));
            $estados = $query->getResult();
            $estadosArray = array();
            for($i=0;$i<count($estados);$i++){
                $estadosArray[$estados[$i]['id']] = $estados[$i]['estadoinstitucion'];
            }        
        return $estadosArray;     
    }
    
    /***
     * Obtiene Arrays de Tipo de Institucion
     */ 
    public function obtieneTipoInstitucionArray(){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT DISTINCT iet.id,iet.descripcion
                FROM SieAppWebBundle:InstitucioneducativaTipo iet
                WHERE iet.id in (:id)
                ORDER BY iet.id ASC'
            )->setParameter('id', array(7, 8, 9,11,12,13));
            $tipos = $query->getResult();
            $tiposArray = array();
            $tiposArray[0] = 'Seleccionar Tipo de Instituto...';
            for($i=0;$i<count($tipos);$i++){
                $tiposArray[$tipos[$i]['id']] = $tipos[$i]['descripcion'];
            }    
        return $tiposArray;   
    }

    /***
     * Obtiene Array de Niveles 
     */
    public function obtieneNivelArray(){
        $em = $this->getDoctrine()->getManager();
        $datos = $em->getRepository('SieAppWebBundle:NivelTipo')->findBy(array('codOrgCurr' => $em->getRepository('SieAppWebBundle:OrgcurricularTipo')->find('3')));
        $nuevoArray = array();
        foreach($datos as $dato){
            $nuevoArray[$dato->getId()] = $dato->getNivel();
        }
        return $nuevoArray;    
    }

    /*** 
     * Obtiene Arrays de Area de Formación
     */ 
    public function obtieneAreaFormacionArray($id){
        
        $em = $this->getDoctrine()->getManager();
        switch($id){
            case '999': //instituto técnico Y técnologico
                $nuevoArray = array();
                //areas técnicas
                $datos = $em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findBy(array('institucioneducativaTipo' => $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find(7)));
                foreach($datos as $dato){
                    $nuevoArray[$dato->getId()] = $dato->getAreaFormacion();
                }
                //areas tecnológicas
                $datos = $em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findBy(array('institucioneducativaTipo' => $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find(8)));
                foreach($datos as $dato){
                    $nuevoArray[$dato->getId()] = $dato->getAreaFormacion();
                }
            break;
            case '11': //CENTROS de capacitación artistica
            case '12': //instituto de formación artistica
            case '13': //escuelas
                $datos = $em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findBy(array('institucioneducativaTipo' => $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find(12)));
                $nuevoArray = array();
                foreach($datos as $dato){
                    $nuevoArray[$dato->getId()] = $dato->getAreaFormacion();
                }
            break;
            default: //instituto técnico ó tecnológico
                $datos = $em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findAll();
                $nuevoArray = array();
                foreach($datos as $dato){
                    $nuevoArray[$dato->getId()] = $dato->getAreaFormacion();
                }
               
            break;
        }
        return $nuevoArray; 
    }

    /***
     * Obtiene Array de Niveles de la Institucion
     */
     public function obtieneInstitucionNivelArray($id){
        $em = $this->getDoctrine()->getManager();
        $datos = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa' => $em->getRepository('SieAppWebBundle:Institucioneducativa')->findById($id)));
        $nuevoArray = array();
        foreach($datos as $dato){
            $nuevoArray[] = $dato->getNivelTipo()->getId();
        }
        return $nuevoArray; 
    }

    /***
     * Obtiene Array de Areas de Formacion de la Institucion
     */
     public function obtieneInstitucionAreaFormArray($id){
        $em = $this->getDoctrine()->getManager();
        $datos = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaAreaFormacionAutorizado')->findBy(array('institucioneducativa' => $em->getRepository('SieAppWebBundle:Institucioneducativa')->findById($id)));
        $nuevoArray = array();
        foreach($datos as $dato){
            $nuevoArray[] = $dato->getTtecAreaFormacionTipo()->getId();
        }
        return $nuevoArray; 
    }

    /*** 
     * Busca datos del Local Educativo
     */
    public function buscaredificioAction($idLe){
    	$em = $this->getDoctrine()->getManager();
    	$edificio = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($idLe);

    	$departamento = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar() : "";
    	$provincia = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar() : "";
    	$municipio = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugar() : "";
    	$canton = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugar() : "";
    	$localidad = ($edificio) ? $edificio->getLugarTipoLocalidad()->getLugar() : "";
    	$zona = ($edificio) ? $edificio->getZona() : "";
    	$direccion = ($edificio) ? $edificio->getDireccion() : "";

    	$response = new JsonResponse();
    	return $response->setData(array(
    			'departamento' => $departamento,
    			'provincia' => $provincia,
    			'municipio' => $municipio,
    			'canton' => $canton,
    			'localidad' => $localidad,
    			'zona'=>$zona,
    			'direccion' => $direccion
    	));
    }

    /*** 
     * Busca areas de formacion segun Tecnica/Tecnologica
     */
    public function buscarareafAction($idArea){
        $areasArray = $this->obtieneAreaFormacionArray($idArea);    
        $response = new JsonResponse();
        return $response->setData(array('areasArray' => $areasArray));
    }

    /*** 
     * Busca areas de formacion segun Tecnica/Tecnologica(Tipo) y Codigo de Institucion(RIE)
     */    
    public function buscarareafeditAction($idArea, $idRie){
        $em = $this->getDoctrine()->getManager();
        $areasArray = $this->obtieneAreaFormacionArray($idArea);    
        $nuevoArray = array();
        $i = 0; 
        foreach($areasArray as $id => $valor){
            $areas = $em->getRepository('SieAppWebBundle:TtecInstitucioneducativaAreaFormacionAutorizado')->findBy(array('ttecAreaFormacionTipo' => $em->getRepository('SieAppWebBundle:TtecAreaFormacionTipo')->findOneById($id), 'institucioneducativa' => $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($idRie)));
            $nuevoArray[$i] = array('id' => $id, 'area' => $valor, 'elegido' => 0); 
            foreach($areas as $area){
                if($area->getTtecAreaFormacionTipo()->getId() == $id){
                    $nuevoArray[$i] = array('id' => $id, 'area' => $valor, 'elegido' => 1); 
                }   
            }
            $i++;    
        }

        $response = new JsonResponse();
        return $response->setData(array('areasArray' => $nuevoArray, 'idArea' => $idArea, 'idRie' => $idRie));
    }

    public function validarInstitucionEducativa($form){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT a
                                    FROM SieAppWebBundle:Institucioneducativa a 
                                    JOIN a.institucioneducativaTipo b
                                    JOIN a.dependenciaTipo c
                                    JOIN a.estadoinstitucionTipo d                                        
                                    JOIN a.leJuridicciongeografica e
                                WHERE b.id = :idTipo
                                    AND c.id = :idDependencia
                                    AND d.id = :idEstado
                                    AND e.id = :idLocal
                                    AND a.institucioneducativa = :institucion
                                    AND a.nroResolucion = :resolucion 
                                    AND a.obsRue = :obsrue 
                                    AND a.fechaResolucion = :fecharesol'); 
        $query->setParameter('idTipo', $form['institucionEducativaTipo']);
        $query->setParameter('idDependencia', $form['dependenciaTipo']);
        $query->setParameter('idEstado', 10);
        $query->setParameter('idLocal', $form['leJuridicciongeograficaId']);
        $query->setParameter('institucion', $form['institucionEducativa']);
        $query->setParameter('resolucion', $form['nroResolucion']);
        $query->setParameter('obsrue', $form['obsRue']);
        $query->setParameter('fecharesol', $form['fechaResolucion']);
        $datos = $query->getResult(); 
        $itt = '';
        if($datos){
            foreach($datos as $dato){
                $itt.= $dato->getId().' - '; 
            }
            $itt = substr($itt, 0, strlen($itt) - 3);
            return $itt;
        } 
        else{
            return 99;
        }
    }

    /*** 
     * Funcion de validación para eliminar un instituto
     * 1: se puede eliminar
     * 0: no se puede eliminar
     */
    function validarEliminacionInstituto($idRie){
        //validar si tiene carreras autorizadas
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT a
                                     FROM SieAppWebBundle:TtecInstitucioneducativaCarreraAutorizada a 
                                     JOIN a.institucioneducativa b
                                     JOIN a.ttecCarreraTipo c
                                    WHERE b.id = :idRie');                       
        $query->setParameter('idRie', $idRie);
        $datos = $query->getResult();  
        
        if($datos){ //Tiene datos - no se puede eliminar';
            return 0;
        }else{
            //validar si es sede y tiene subsedes
            $query = $em->createQuery('SELECT a
                                        FROM SieAppWebBundle:TtecInstitucioneducativaSede a 
                                        JOIN a.institucioneducativa b
                                       WHERE a.sede = :idSede');                       
            $query->setParameter('idSede', $idRie);
            $subsedes = $query->getResult(); 
            if(count($subsedes) > 1){
                return 0;
            }else{
                return 1;
            }
        }
    }

    /*** 
     * Funcion que genera el nombre de mes en literal
     */  
    function nombremes($mes){
        setlocale(LC_TIME, 'spanish');  
        $nombre=strftime("%B",mktime(0, 0, 0, $mes, 1, 2000)); 
        return $nombre;
    }
}
