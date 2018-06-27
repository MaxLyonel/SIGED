<?php

namespace Sie\PermanenteBundle\Controller;

use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoCorto;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Sie\AppWebBundle\Entity\PermanenteCursocortoTipo;
use Sie\AppWebBundle\Entity\PermanenteInstitucioneducativaCursocorto;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * EstudianteInscripcion controller.
 *
 */
class CursosCortosController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();

    }

    /**
     * list of request
     *
     */
    public function indexAction(Request $request) {
      //  dump("asdsad");die();
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
       $sie= $this->session->get('ie_id');
       $gestion=$this->session->get('ie_gestion');
       $suc=$this->session->get('ie_subcea');
       $periodo=$this->session->get('ie_per_cod');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $exist = true;
        $em = $this->getDoctrine()->getManager();
        $querya = $em->getConnection()->prepare('select a.id as iecid, c.id as programaId, c.programa, d.id as subareaId, d.sub_area as subarea,e.id as areatematicaId,e.areatematica,f.id as cursocortoId, f.cursocorto,g.id as paraleloId, g.paralelo, b.esabierto

            from institucioneducativa_curso a
                inner join permanente_institucioneducativa_cursocorto b on a.id=b.institucioneducativa_curso_id
                    inner join permanente_programa_tipo c on c.id =b.programa_tipo_id
                        inner join permanente_sub_area_tipo d on d.id = b.sub_area_tipo_id
                            inner join permanente_area_tematica_tipo e on e.id = b.areatematica_tipo_id
                                inner join permanente_cursocorto_tipo f on f.id = b.cursocorto_tipo_id
                                    inner join paralelo_tipo g on g.id=a.paralelo_tipo_id 
                                        inner join institucioneducativa h on h.id = a.institucioneducativa_id
                                            
            where a.nivel_tipo_id =:nivel
            and a.institucioneducativa_id = :sie
            and a.gestion_tipo_id =:gestion
            and a.sucursal_tipo_id =:suc
            and a.periodo_tipo_id = :periodo

        ');
        $querya->bindValue(':nivel', 230);
        $querya->bindValue(':sie', $sie);
        $querya->bindValue(':gestion', $gestion);
        $querya->bindValue(':suc', $suc);
        $querya->bindValue(':periodo', $periodo);
        $querya->execute();

        $objUeducativa= $querya->fetchAll();

        $exist = true;
        $aInfoUnidadEductiva = array();
        if ($objUeducativa) {
            foreach ($objUeducativa as $uEducativa) {

                $sinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('programa' => $uEducativa['programa'], 'subarea' => $uEducativa['subarea'], 'areatematica' => $uEducativa['areatematica'], 'cursocorto' => $uEducativa['cursocorto'],
                    'ueducativaInfoId' => array('programaid' => $uEducativa['programaid'], 'subareaId' => $uEducativa['subareaid'], 'areatematicaid' => $uEducativa['areatematicaid'], 'cursocortoid' => $uEducativa['cursocortoid'], 'iecid' => $uEducativa['iecid'],'esabierto'=> $uEducativa['esabierto'])
                )));

                $aInfoUnidadEductiva[$uEducativa['esabierto']][$uEducativa['subarea']][$uEducativa['programa']][$uEducativa['areatematica']][$uEducativa['cursocorto']] [$uEducativa['iecid']] = array('infoUe' => $sinfoUeducativa, 'esabierto'=>$uEducativa['esabierto']);

            }
           // dump($aInfoUnidadEductiva);die;
        } else {
            $message = 'No existe información del Centro de Educación  para la gestión seleccionada.';
            $this->addFlash('warninresult', $message);
            $exist = false;
        }

        $areatematica = $em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->findAll();

        $query = $em->getConnection()->prepare('
                select a.id, a.periodo_tipo_id, a.grado_tipo_id, a.gestion_tipo_id, a.nivel_tipo_id, a.turno_tipo_id,a.fecha_inicio,a.fecha_fin,a.duracionhoras,
                        b.esabierto, 
                        c.areatematica, d.poblacion,e.programa, f.sub_area, g.cursocorto,
                        h.id as codofermaes,h.horasmes, 
                        i.maestro_inscripcion_id,
                        k.paterno,k.materno,k.nombre,
                        m.id as percursocorid,m.sub_area_tipo_id,m.programa_tipo_id, m.areatematica_tipo_id,m.cursocorto_tipo_id
                    FROM
                        institucioneducativa_curso a 
                            left JOIN permanente_institucioneducativa_cursocorto b on a.id= b.institucioneducativa_curso_id
	                              left join permanente_area_tematica_tipo c on b.areatematica_tipo_id =c.id
		                              left join permanente_poblacion_tipo d on b.poblacion_tipo_id = d.id
			                                left join permanente_programa_tipo e on b.programa_tipo_id=e.id
				                                    left join permanente_sub_area_tipo f on b.sub_area_tipo_id= f.id
					                                  left join permanente_cursocorto_tipo g on cursocorto_tipo_id = g.id
						                                  left join institucioneducativa_curso_oferta h on  a.id = h.insitucioneducativa_curso_id	
							                                  left join institucioneducativa_curso_oferta_maestro i on h.id = i.institucioneducativa_curso_oferta_id
								                                  left join maestro_inscripcion j on i.maestro_inscripcion_id = j.id
									                                    left join persona k on j.persona_id =k.id
										                                    left join permanente_institucioneducativa_cursocorto m on m.institucioneducativa_curso_id = a.id
                                                
                where  a.nivel_tipo_id= :nivel
        ');
        $query->bindValue(':nivel', 230);
        $query->execute();

        $cursosCortos= $query->fetchAll();
          // dump($aInfoUnidadEductiva);die;

        return $this->render('SiePermanenteBundle:CursosCortos:index.html.twig', array(
            'exist' => $exist,
            'aInfoUnidadEductiva' => $aInfoUnidadEductiva,
            'areatematica' => $areatematica,
            'cursosCortos' => $cursosCortos


        ));
    }

    public function newCursoCortoAction(Request $request){

        try {
            $this->session = $request->getSession();
            $id_usuario = $this->session->get('userId');
            //validation if the user is logged
            if (!isset($id_usuario)) {
                return $this->redirect($this->generateUrl('login'));
            }

            //llama a los campos de las tablas para mostrar en la vista
            $em = $this->getDoctrine()->getManager();
            $subarea = $em->getRepository('SieAppWebBundle:PermanenteSubAreaTipo')->findAll();
            $areatematica = $em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->findAll();
            $poblacion = $em->getRepository('SieAppWebBundle:PermanentePoblacionTipo')->findAll();
            $programa = $em->getRepository('SieAppWebBundle:PermanenteProgramaTipo')->findAll();
            $cursosCortos = $em->getRepository('SieAppWebBundle:PermanenteCursocortoTipo')->findAll();
            $turno = $em->getRepository('SieAppWebBundle:TurnoTipo')->findAll();

            $subareaArray = array();

            foreach ($subarea as $value) {
                if ($value->getId() == 1 ) {
                    $subareaArray[$value->getId()] = $value->getSubArea();
                }
            }

            $areatematicaArray = array();
            foreach ($areatematica as $value) {
                $areatematicaArray[$value->getId()] = $value->getAreatematica();
            }

            $programaArray = array();
            foreach ($programa as $value) {
                if (($value->getId() == 0)||($value->getId() ==1 )||($value->getId() ==2 ))
                {

                }else
                {
                    $programaArray[$value->getId()] = $value->getPrograma();
                }
            }
            $poblacionArray = array();
            foreach ($poblacion as $value) {
                $poblacionArray[$value->getId()] = $value->getPoblacion();
            }
            $cursosCortosArray = array();
            foreach ($cursosCortos as $value) {
                $cursosCortosArray[$value->getId()] = $value->getCursocorto();
            }

            $turnoArray = array();
            foreach ($turno as $value) {
                if ($value->getId() == 0)
                {

                }else {
                $turnoArray[$value->getId()] = $value->getTurno();
                }
            }

            $paisNac =  $em->getRepository('SieAppWebBundle:PaisTipo')->findOneBy(array('id' => 1));
            $query = $em->createQuery(
                'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
                ->setParameter('nivel', 8)
                ->setParameter('lt1', $paisNac);
            $dptoNacE = $query->getResult();

            $dptoNacArray = array();
            foreach ($dptoNacE as $value) {
                if( $value->getId()== 11)
                {

                }else {
                    $dptoNacArray[$value->getId()] = $value->getLugar();
                }
            }
            $prov = array();
            $muni = array();
            // Dibuja la Vista para la cracion de un nuevo curso
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_per_cursos_cortos_create'))
                ->add('subarea', 'choice', array('required' => true, 'choices' => $subareaArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('areatematica', 'choice', array('required' => true, 'choices' => $areatematicaArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('programa', 'choice', array('required' => true, 'choices' => $programaArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('cursosCortos', 'choice', array( 'required' => true, 'choices' => $cursosCortosArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'chosen-select col-lg-10')))
                ->add('poblacion', 'choice', array( 'required' => true, 'choices' => $poblacionArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control','onchange' => 'mostrarPobDetalle(this.value)')))
                ->add('departamento', 'choice', array('label' => 'Departamento', 'required' => true, 'choices' => $dptoNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarProvincias(this.value)')))
                ->add('provincia', 'choice', array('label' => 'Provincia', 'required' => true, 'choices' => $prov, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarMunicipios(this.value)')))
                ->add('municipio', 'choice', array('label' => 'Municipio', 'required' => true, 'choices' => $muni, 'empty_value' => 'Seleccionar...','attr' => array('class' => 'form-control')))
                ->add('fechaInicio', 'datetime', array('widget' => 'single_text','date_format' => 'dd-MM-yyyy','attr' => array('class' => 'form-control calendario')))
                ->add('fechaFin', 'datetime', array('widget' => 'single_text','date_format' => 'dd-MM-yyyy','attr' => array('class' => 'form-control calendario')))
                ->add('turno', 'choice', array( 'required' => true, 'choices' => $turnoArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('horas', 'text', array( 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true, 'pattern' => '[0-9]{1,2}', 'maxlength' => '2')))
                ->add('lugar', 'text', array( 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('pobdetalle', 'text', array( 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('pobobs', 'textarea', array( 'required' => false, 'attr' => array('class' => 'form-control','readonly' => true)))
                ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'disbled' => true)))
                ->getForm();
            return $this->render('SiePermanenteBundle:CursosCortos:new.html.twig', array(
                'cursos'=>$cursosCortos,
                'form' => $form->createView()
            ));
        }
        catch (Exception $ex)
        {

        }
    }

    public function createCursoCortoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //LLama a variables de Sesion
        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_subcea');
        $periodo = $this->session->get('ie_per_cod');

        try {
           //recibe los datos del formulario de vista
            $form = $request->get('form');
            //Invoca a una funcion de Base de Datos Necesaria para cualquier INSERT, para que se reinicie la secuencia de ingreso de datos
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso');")->execute();
            // Realiza un INSERT para la creacion de un curso nuevo con los datos extraidos de la vista
            $institucioncurso = new InstitucioneducativaCurso();
            $institucioncurso ->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(230));
            $institucioncurso ->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find(0));
            $institucioncurso ->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(99));
            $institucioncurso ->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find(1));
            $institucioncurso ->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
            $institucioncurso ->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find($this->session->get('ie_subcea')));
            $institucioncurso ->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
            $institucioncurso ->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find($this->session->get('ie_per_cod')));
            $institucioncurso ->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneBy(array('id' => $form['turno'])));
            $institucioncurso ->setDuracionhoras($form['horas']);
            $institucioncurso ->setFechaInicio(new \DateTime($form['fechaInicio']));
            $institucioncurso ->setFechaFin(new \DateTime($form['fechaFin']));

            $em->persist($institucioncurso);
            $em->flush($institucioncurso);

            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('permanente_institucioneducativa_cursocorto');")->execute();
            $institucioncursocorto = new PermanenteInstitucioneducativaCursocorto();
            $institucioncursocorto  ->setInstitucioneducativaCurso($institucioncurso);
            $institucioncursocorto  ->setSubAreaTipo($em->getRepository('SieAppWebBundle:PermanenteSubAreaTipo')->findOneBy(array('id' => $form['subarea'])));
            $institucioncursocorto  ->setProgramaTipo($em->getRepository('SieAppWebBundle:PermanenteProgramaTipo')->findOneBy(array('id' => $form['programa'])));
            $institucioncursocorto  ->setAreatematicaTipo($em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->findOneBy(array('id' => $form['areatematica'])));
            $institucioncursocorto  ->setCursocortoTipo($em->getRepository('SieAppWebBundle:PermanenteCursocortoTipo')->findOneBy(array('id' => $form['cursosCortos'])));
            $institucioncursocorto  ->setEsabierto(true);
            $institucioncursocorto  ->setPoblacionTipo($em->getRepository('SieAppWebBundle:PermanentePoblacionTipo')->findOneBy(array('id' => $form['poblacion'])));
            $institucioncursocorto  ->setLugarTipoDepartamento($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['departamento'])));
            $institucioncursocorto  ->setLugarTipoProvincia($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['provincia'])));
            $institucioncursocorto  ->setLugarTipoMunicipio($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['municipio'])));
            $institucioncursocorto  ->setLugarDetalle($form['lugar']);
            $institucioncursocorto  ->setPoblacionDetalle($form['pobdetalle']);
            $em->persist($institucioncursocorto);
            $em->flush();

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            return $this->redirect($this->generateUrl('herramienta_per_cursos_cortos_index'));
        }
        catch  (Exception $ex)
        {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_per_cursos_cortos_index'));
        }
    }

    public function editCursoCortoAction(Request $request){
      //Recibe los datos de informacion para su edicion
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $idcurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
        try {
            $this->session = $request->getSession();
            $id_usuario = $this->session->get('userId');
            //validation if the user is logged
            if (!isset($id_usuario)) {
                return $this->redirect($this->generateUrl('login'));
            }

            $institucion = $this->session->get('ie_id');
            $gestion = $this->session->get('ie_gestion');
            $sucursal = $this->session->get('ie_subcea');
            $periodo = $this->session->get('ie_per_cod');
            //Busca Todos los datos de las tablas de la BD para mostrarlos en la vista
            $em = $this->getDoctrine()->getManager();
            $subarea = $em->getRepository('SieAppWebBundle:PermanenteSubAreaTipo')->findAll();
            $areatematica = $em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->findAll();
            $poblacion = $em->getRepository('SieAppWebBundle:PermanentePoblacionTipo')->findAll();
            $programa = $em->getRepository('SieAppWebBundle:PermanenteProgramaTipo')->findAll();
            $cursosCortos = $em->getRepository('SieAppWebBundle:PermanenteCursocortoTipo')->findAll();
            $turno = $em->getRepository('SieAppWebBundle:TurnoTipo')->findAll();
            $institucioncurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idcurso);
            $institucioncursocorto = $em->getRepository('SieAppWebBundle:PermanenteInstitucioneducativaCursocorto')->findOneBy(array('institucioneducativaCurso'=>$idcurso));
            $deptoid=$institucioncursocorto->getLugarTipoDepartamento()->getId();
            $provid=$institucioncursocorto->getLugarTipoProvincia()->getId();
            $munid=$institucioncursocorto->getLugarTipoMunicipio()->getId();
            $lugar=$institucioncursocorto->getLugarDetalle();
            $pobdetalle=$institucioncursocorto->getPoblacionDetalle();

            $arraypob= array();
            $pobobservacion=$em->getRepository('SieAppWebBundle:PermanentePoblacionTipo')->find($institucioncursocorto->getPoblacionTipo()->getId());

            $subareaArray= array();
            foreach ($subarea as $value) {
                if ($value->getId() == 1) {
                    $subareaArray[$value->getId()] = $value->getSubArea();
                }
            }
            $areatematicaArray = array();
            foreach ($areatematica as $value) {
                $areatematicaArray[$value->getId()] = $value->getAreatematica();
            }
            $programaArray = array();
            foreach ($programa as $value) {
                if (($value->getId() == 0)||($value->getId() ==1 )||($value->getId() ==2 ))
                {
                }else
                {
                    $programaArray[$value->getId()] = $value->getPrograma();
                }
            }
            $poblacionArray = array();
            foreach ($poblacion as $value) {
                $poblacionArray[$value->getId()] = $value->getPoblacion();
            }
            $cursosCortosArray = array();
            foreach ($cursosCortos as $value) {
                $cursosCortosArray[$value->getId()] = $value->getCursocorto();
            }
            $turnoArray = array();
            foreach ($turno as $value) {
                if ($value->getId() == 0)
                {

                }else {
                    $turnoArray[$value->getId()] = $value->getTurno();
                }
            }

            //Lugar de Nacimiento

            $paisNac =  $em->getRepository('SieAppWebBundle:PaisTipo')->findOneBy(array('id' => 1));

            $query = $em->createQuery(
                'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
                ->setParameter('nivel', 8)
                ->setParameter('lt1', $paisNac);
            $dptoNacE = $query->getResult();

            $dptoNacArray = array();
            foreach ($dptoNacE as $value) {
                if( $value->getId()== 11)
                {

                }else {
                    $dptoNacArray[$value->getId()] = $value->getLugar();
                }
            }

            $query = $em->createQuery(
                'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                ->setParameter('nivel', 9)
                ->setParameter('lt1', $deptoid);
            $provincias = $query->getResult();

            $provinciasArray = array();
            foreach ($provincias as $c) {
                $provinciasArray[$c->getId()] = $c->getLugar();
            }
            $query = $em->createQuery(
                'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                ->setParameter('nivel', 10)
                ->setParameter('lt1', $provid);
            $municipios = $query->getResult();

            $municipiosArray = array();
            foreach ($municipios as $c) {
                $municipiosArray[$c->getId()] = $c->getLugar();
            }

            $prov = array();
            $muni = array();

            // Dibuja el formulario con los datos Seleccionados Anteriormente
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_per_cursos_cortos_update'))
                ->add('idCursosCortos', 'hidden', array('data' => $idcurso))
                ->add('idCursosCortosA', 'hidden', array('data' => $idcurso))
                ->add('nivel', 'hidden', array('data' => 230))
                ->add('ciclo', 'hidden', array('data' => 0))
                ->add('grado', 'hidden', array('data' => 99))
                ->add('paralelo', 'hidden', array('data' => 1))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('sucursal', 'hidden', array('data' => $sucursal))
                ->add('institucion', 'hidden', array('data' => $institucion))
                ->add('periodo', 'hidden', array('data' => $periodo))
                ->add('turno', 'choice', array( 'required' => true, 'choices' => $turnoArray, 'data' => $institucioncurso->getTurnoTipo()->getId() , 'attr' => array('class' => 'chosen-select form-control', 'data-placeholder' => 'Seleccionar...', 'data-placeholder' => 'Seleccionar...')))
                ->add('horas', 'text', array( 'required' => true, 'data' => $institucioncurso->getDuracionhoras(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control','pattern' => '[0-9]{1,2}', 'maxlength' => '2')))
                ->add('fechaInicio', 'date', array('widget' => 'single_text','format' => 'dd-MM-yyyy','data' => new \DateTime($institucioncurso->getFechaInicio()->format('d-m-Y')), 'required' => false, 'attr' => array('class' => 'form-control calendario')))
                ->add('fechaFin', 'date', array('widget' => 'single_text','format' => 'dd-MM-yyyy','data' => new \DateTime($institucioncurso->getFechaFin()->format('d-m-Y')), 'required' => false, 'attr' => array('class' => 'form-control calendario')))
                ->add('subarea', 'choice', array('required' => true, 'choices' => $subareaArray, 'data' => $institucioncursocorto->getSubareaTipo()->getId() , 'attr' => array('class' => 'chosen-select form-control', 'data-placeholder' => 'Seleccionar...', 'data-placeholder' => 'Seleccionar...')))
                ->add('programa', 'choice', array('required' => true, 'choices' => $programaArray, 'data' => $institucioncursocorto->getProgramaTipo()->getId() , 'attr' => array('class' => 'chosen-select form-control', 'data-placeholder' => 'Seleccionar...', 'data-placeholder' => 'Seleccionar...')))
                ->add('areatematica', 'choice', array('required' => true, 'choices' => $areatematicaArray, 'data' => $institucioncursocorto->getAreatematicaTipo()->getId() , 'attr' => array('class' => 'chosen-select form-control', 'data-placeholder' => 'Seleccionar...', 'data-placeholder' => 'Seleccionar...')))
                ->add('poblacion', 'choice', array( 'required' => true, 'choices' => $poblacionArray, 'data' => $institucioncursocorto->getPoblacionTipo()->getId() , 'attr' => array('class' => 'chosen-select form-control','onchange' => 'mostrarPobDetalle(this.value)', 'data-placeholder' => 'Seleccionar...')))
                ->add('cursosCortos', 'choice', array( 'required' => true, 'choices' => $cursosCortosArray, 'data' => $institucioncursocorto->getCursocortoTipo()->getId() , 'attr' => array('class' => 'chosen-select col-lg-10')))
                ->add('pobdetalle', 'text', array( 'required' => true, 'data' => $pobdetalle,'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('pobobs', 'textarea', array( 'required' => false, 'data'=> $pobobservacion->getObs(),'attr' => array('class' => 'form-control','readonly' => true)))
                ->add('departamento', 'choice', array('label' => 'departamento', 'required' => true, 'choices' => $dptoNacArray,'data' => $institucioncursocorto->getLugarTipoDepartamento()->getId(),  'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarProvincias(this.value)')))
                ->add('provincia', 'choice', array('label' => 'Provincia', 'required' => true, 'choices' => $provinciasArray,'data' => $institucioncursocorto->getLugarTipoProvincia()->getId(),  'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarMunicipios(this.value)')))
                ->add('municipio', 'choice', array('label' => 'Municipio', 'required' => true, 'choices' => $municipiosArray, 'data' => $institucioncursocorto->getLugarTipoMunicipio()->getId(),'empty_value' => 'Seleccionar...','attr' => array('class' => 'form-control')))
                ->add('lugar', 'text', array( 'required' => true, 'data' => $lugar, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'disbled' => true)))
                ->getForm();
            return $this->render('SiePermanenteBundle:CursosCortos:edit.html.twig', array(

                'form' => $form->createView()

            ));
        }
        catch (Exception $ex)
        {
        }
    }

    public function updateCursoCortoAction(Request $request){
       //Recibe los datos del formulario editar
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_subcea');
        $periodo = $this->session->get('ie_per_cod');

       $institucioncurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('id' => $form['idCursosCortos']));
        $institucioncursocorto = $em->getRepository('SieAppWebBundle:PermanenteInstitucioneducativaCursocorto')->findOneBy(array('institucioneducativaCurso' => $form['idCursosCortos']));
        $idcurso = $institucioncurso->getId();

        //realiza el update de 2 tablas (InstitucioneducativaCurso,PermanenteInstitucioneducativaCursocorto)
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');
            $institucioncurso ->getInstitucioneducativa($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('id' => $form['idCursosCortos'])));
            $institucioncurso ->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(230));
            $institucioncurso ->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find(0));
            $institucioncurso ->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(99));
            $institucioncurso ->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find(1));
            $institucioncurso ->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
            $institucioncurso ->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find($this->session->get('ie_subcea')));
            $institucioncurso ->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
            $institucioncurso ->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find($this->session->get('ie_per_cod')));
            $institucioncurso ->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneBy(array('id' => $form['turno'])));
            $institucioncurso ->setDuracionhoras($form['horas']);
            $institucioncurso ->setFechaInicio(new \DateTime($form['fechaInicio']));
            $institucioncurso ->setFechaFin(new \DateTime($form['fechaFin']));

            $em->persist($institucioncurso);

            $em->flush($institucioncurso);
            $institucioncursocorto  ->setInstitucioneducativaCurso($institucioncurso);
            $institucioncursocorto  ->setSubAreaTipo($em->getRepository('SieAppWebBundle:PermanenteSubAreaTipo')->findOneBy(array('id' => $form['subarea'])));
            $institucioncursocorto  ->setProgramaTipo($em->getRepository('SieAppWebBundle:PermanenteProgramaTipo')->findOneBy(array('id' => $form['programa'])));
            $institucioncursocorto  ->setAreatematicaTipo($em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->findOneBy(array('id' => $form['areatematica'])));
            $institucioncursocorto  ->setCursocortoTipo($em->getRepository('SieAppWebBundle:PermanenteCursocortoTipo')->findOneBy(array('id' => $form['cursosCortos'])));
            $institucioncursocorto  ->setPoblacionTipo($em->getRepository('SieAppWebBundle:PermanentePoblacionTipo')->findOneBy(array('id' => $form['poblacion'])));
            $institucioncursocorto  ->setLugarTipoDepartamento($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['departamento'])));
            $institucioncursocorto  ->setLugarTipoProvincia($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['provincia'])));
            $institucioncursocorto  ->setLugarTipoMunicipio($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['municipio'])));
            $institucioncursocorto  ->setLugarDetalle($form['lugar']);
            $institucioncursocorto  ->setPoblacionDetalle($form['pobdetalle']);
            $em->persist($institucioncursocorto);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            return $this->redirect($this->generateUrl('herramienta_per_cursos_cortos_index'));
        }
        catch  (Exception $ex)
        {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_per_cursos_cortos_index'));
        }

    }

    public function deleteCursoCortoAction(Request $request){
        //create the DB conexion
        $em= $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        //get the send values
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
//          dump($aInfoUeducativa['ueducativaInfoId']['nivelId']);die;
        $iecid = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
        //dump($iecid);die;
        $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecid);
        $response = new JsonResponse();
        //dump(count($iec)); die;

        //dump(count($iecpercount));die;
        $em->getConnection()->beginTransaction();
        try {
                $iecdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecid);

                //BUSCANDO E ELIMINANDO CURSO OFERTA
                $iecofercurdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findByInsitucioneducativaCurso($iecid);

                if (count($iecofercurdup) > 0){
                    foreach ($iecofercurdup as $iecofercurduprow) {
                        //BUSCANDO E ELIMINANDO CURSO OFERTA MAESTRO
                        $iecofermaescurdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findByInstitucioneducativaCursoOferta($iecofercurduprow->getId());
                        if (count($iecofermaescurdup) > 0){
                            foreach ($iecofermaescurdup as $iecofermaescurduprow) {
                                $em->remove($iecofermaescurduprow);
                                $em->flush();
                            }
                        }
                        $em->remove($iecofercurduprow);
                        $em->flush();
                    }
                }

            $iecest=$em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso'=>$iecid));
          // dump($iecest);die;

                foreach ($iecest as $iecestins) {
                $em->remove($iecestins);
                $em->flush();
            }

            $iecper=$em->getRepository('SieAppWebBundle:PermanenteInstitucioneducativaCursocorto')->findOneBy(array('institucioneducativaCurso'=>$iecid));
            $em->remove($iecper);
            $em->flush();


            $em->remove($iecdup);
            $em->flush();


            $em->getConnection()->commit();

            return $response->setData(array('mensaje'=>'¡Proceso realizado exitosamente!'));

             // return $this->redirect($this->generateUrl('herramienta_per_cursos_cortos_index'));

             // return $this->redirectToRoute('herramienta_per_cursos_cortos_index');
             // return $this->render('SiePermanenteBundle:CursosCortos:index.html.twig');



        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function showMaestroCursoCortoAction(Request $request){
        //dump($request);die;
        $infoUe = $request->get('infoUe');
        // dump($infoUe);die;
        $aInfoUeducativa = unserialize($infoUe);
        $idcurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
       // $idcurso=$request->get('idcurso');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $periodo = $this->session->get('ie_per_cod');
        $areatematica = $em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->findAll();
        $institucioncurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idcurso);
        $query = $em->getConnection()->prepare('
                select a.id , a.persona_id, a.institucioneducativa_id,a.gestion_tipo_id,a.es_vigente_administrativo,
                      b.id as idformacion, b.formacion,
                      c.paterno,c.materno,c.nombre,c.carnet
                      from maestro_inscripcion a
	                      inner join formacion_tipo b on b.id =a.formacion_tipo_id
		                      inner join persona c on c.id = a.persona_id
              
                where a.institucioneducativa_id=:idcea and a.gestion_tipo_id = :gestion and a.es_vigente_administrativo=true 
                and periodo_tipo_id=:periodo and a.cargo_tipo_id =0 and c.id not in (select f.id
                    from institucioneducativa_curso a
	                    inner join permanente_institucioneducativa_cursocorto x on x.institucioneducativa_curso_id = a.id
		                      inner join permanente_cursocorto_tipo y on y.id= x.cursocorto_tipo_id	
	                              inner join institucioneducativa_curso_oferta b on b.insitucioneducativa_curso_id = a.id
		                              inner join institucioneducativa_curso_oferta_maestro c on c.institucioneducativa_curso_oferta_id = b.id
			                                inner join maestro_inscripcion d on d.id = c.maestro_inscripcion_id
	                                               inner join formacion_tipo e on e.id =d.formacion_tipo_id
		                                                  inner join persona f on f.id = d.persona_id
			
                    where a.institucioneducativa_id=:idcea AND d.gestion_tipo_id =:gestion AND a.id =:curso )
	    ');
        $query->bindValue(':idcea', $institucion);
        $query->bindValue(':gestion', $gestion);
        $query->bindValue(':curso', $idcurso);
        $query->bindValue(':periodo', $periodo);
        $query->execute();

        $maestros= $query->fetchAll();
//dump($maestros);die;

        $querya = $em->getConnection()->prepare('
              select a.id, a.institucioneducativa_id,a.duracionhoras,
                    y.cursocorto,
                    b.id as idoferta,b.horasmes,
                    c.id as idofermaes,
                    d.id as idmaestro, d.persona_id, d.institucioneducativa_id,d.gestion_tipo_id,d.es_vigente_administrativo,
                    e.id as idformacion, e.formacion,
                    f.paterno,f.materno,f.nombre,f.carnet
                    from institucioneducativa_curso a
	                    inner join permanente_institucioneducativa_cursocorto x on x.institucioneducativa_curso_id = a.id
		                      inner join permanente_cursocorto_tipo y on y.id= x.cursocorto_tipo_id	
	                              inner join institucioneducativa_curso_oferta b on b.insitucioneducativa_curso_id = a.id
		                              inner join institucioneducativa_curso_oferta_maestro c on c.institucioneducativa_curso_oferta_id = b.id
			                                inner join maestro_inscripcion d on d.id = c.maestro_inscripcion_id
	                                               inner join formacion_tipo e on e.id =d.formacion_tipo_id
		                                                  inner join persona f on f.id = d.persona_id
			
                    where a.institucioneducativa_id=:idcea AND d.gestion_tipo_id = :gestion AND a.id = :curso 
');
             $querya->bindValue(':idcea', $institucion);
             $querya->bindValue(':curso', $idcurso);
             $querya->bindValue(':gestion', $gestion);
             $querya->execute();
            $maestrosins =$querya->fetchAll();
//dump($maestrosins);die;


        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_per_cursos_cortos_add_maestro'))
          //  ->add('maestro', 'hidden', array('data' => $maestros))
            ->add('gestion', 'hidden', array('data' => $gestion))
            ->add('cursoscortos', 'hidden', array('data' => $idcurso))
            ->add('horas', 'text', array( 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control','pattern' => '[0-9]{1,2}', 'maxlength' => '2')))
            //->add('busqueda', 'text', array( 'required' => false, 'attr' => array('autocomplete' => 'on', 'class' => 'form-control')))

            ->getForm();

       // dump($maestros);die;
       // dump($form);die;

        return $this->render('SiePermanenteBundle:CursosCortos:showMaestro.html.twig', array(
           'maestro' => $maestros,
           'maestroins' => $maestrosins,
           'institucioncurso'=>$institucioncurso,
           'infoUe'=>$infoUe,
           'form' => $form->createView()
        ));

}

    public function addMaestroCursoCortoAction(Request $request){

        //get send valus
    //dump($request);die;
        $infoUe = $request->get('infoUe');
        $form =  $request->get('form');
     //   dump($form);die;
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $institucioncurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('id' => $form['cursoscortos']));
        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $periodo = $this->session->get('ie_per_cod');
        $horas = $form['horas'];
      //  dump($horas);die;
        try
        {
         $form = $request->get('form');
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();

            $institucioncursoferta = new InstitucioneducativaCursoOferta();
            $institucioncursoferta ->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('id' => $form['cursoscortos'])));
            $institucioncursoferta ->setHorasmes($horas);
            $institucioncursoferta ->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find(1));

            $em->persist($institucioncursoferta);
           // dump($institucioncursoferta);die;
            $em->flush($institucioncursoferta);
            //dump($institucioncurso);die;

            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta_maestro');")->execute();
            $institucioncursofermaestro = new InstitucioneducativaCursoOfertaMaestro();

            $institucioncursofermaestro  ->setInstitucioneducativaCursoOferta($institucioncursoferta);
            $institucioncursofermaestro  ->setHorasMes($horas);
            $institucioncursofermaestro  ->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('id' => $form['facilitador'])));

            $em->persist($institucioncursofermaestro);

           // dump($institucioncursofermaestro);die;
            $em->flush();

            $em->getConnection()->commit();
            $idcurso=$form['cursoscortos'];

            $query = $em->getConnection()->prepare('
                select a.id , a.persona_id, a.institucioneducativa_id,a.gestion_tipo_id,a.es_vigente_administrativo,
                      b.id as idformacion, b.formacion,
                      c.paterno,c.materno,c.nombre,c.carnet
                      from maestro_inscripcion a
	                      inner join formacion_tipo b on b.id =a.formacion_tipo_id
		                      inner join persona c on c.id = a.persona_id
              
                where a.institucioneducativa_id=:idcea and a.gestion_tipo_id = :gestion and a.es_vigente_administrativo=true 
                and periodo_tipo_id=:periodo and a.cargo_tipo_id =0 and c.id not in (select f.id
                    from institucioneducativa_curso a
	                    inner join permanente_institucioneducativa_cursocorto x on x.institucioneducativa_curso_id = a.id
		                      inner join permanente_cursocorto_tipo y on y.id= x.cursocorto_tipo_id	
	                              inner join institucioneducativa_curso_oferta b on b.insitucioneducativa_curso_id = a.id
		                              inner join institucioneducativa_curso_oferta_maestro c on c.institucioneducativa_curso_oferta_id = b.id
			                                inner join maestro_inscripcion d on d.id = c.maestro_inscripcion_id
	                                               inner join formacion_tipo e on e.id =d.formacion_tipo_id
		                                                  inner join persona f on f.id = d.persona_id
			
                    where a.institucioneducativa_id=:idcea AND d.gestion_tipo_id =:gestion AND a.id =:curso )
	    ');
            $query->bindValue(':idcea', $institucion);
            $query->bindValue(':gestion', $gestion);
            $query->bindValue(':curso', $idcurso);
            $query->bindValue(':periodo', $periodo);
            $query->execute();

            $maestros= $query->fetchAll();
            $querya = $em->getConnection()->prepare('
              select a.id, a.institucioneducativa_id,a.duracionhoras,
                    y.cursocorto,
                    b.id as idoferta,b.horasmes,
                    c.id as idofermaes,
                    d.id as idmaestro, d.persona_id, d.institucioneducativa_id,d.gestion_tipo_id,d.es_vigente_administrativo,
                    e.id as idformacion, e.formacion,
                    f.paterno,f.materno,f.nombre,f.carnet
                    from institucioneducativa_curso a
	                    inner join permanente_institucioneducativa_cursocorto x on x.institucioneducativa_curso_id = a.id
		                      inner join permanente_cursocorto_tipo y on y.id= x.cursocorto_tipo_id	
	                              inner join institucioneducativa_curso_oferta b on b.insitucioneducativa_curso_id = a.id
		                              inner join institucioneducativa_curso_oferta_maestro c on c.institucioneducativa_curso_oferta_id = b.id
			                                inner join maestro_inscripcion d on d.id = c.maestro_inscripcion_id
	                                               inner join formacion_tipo e on e.id =d.formacion_tipo_id
		                                                  inner join persona f on f.id = d.persona_id
			
                    where a.institucioneducativa_id=:idcea AND d.gestion_tipo_id = :gestion AND a.id = :curso 
');
            $querya->bindValue(':idcea', $institucion);
            $querya->bindValue(':curso', $idcurso);
            $querya->bindValue(':gestion', $gestion);
            $querya->execute();
            $maestrosins =$querya->fetchAll();
//dump($maestrosins);die;
            $form = $this->createFormBuilder()
//                ->setAction($this->generateUrl('herramienta_per_cursos_cortos_add_maestro'))
                //  ->add('maestro', 'hidden', array('data' => $maestros))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('cursoscortos', 'hidden', array('data' => $idcurso))
                ->add('horas', 'text', array( 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control','pattern' => '[0-9]{1,2}', 'maxlength' => '2')))

                //->add('busqueda', 'text', array( 'required' => false, 'attr' => array('autocomplete' => 'on', 'class' => 'form-control')))

                ->getForm();


            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            return $this->render('SiePermanenteBundle:CursosCortos:listMaestro.html.twig',array(
                'maestro' => $maestros,
                'maestroins' => $maestrosins,
                'institucioncurso'=>$institucioncurso,
                'infoUe'=>$infoUe,
                'form' => $form->createView()
            ));
//            $response = new JsonResponse();
//            return $response->setData(array(
//                'infoUe'=>$infoUe
//            ));
        }
        catch  (Exception $ex)
        {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->render('SiePermanenteBundle:CursosCortos:showMaestro.html.twig');
        }
    }

    public function deleteMaestroCursoCortoAction(Request $request){
       // dump($request);die;
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $dataUe=(unserialize($infoUe));
        $exist=true;
        $idoferta =  $request->get('idoferta');
        $idofermaes =  $request->get('idofermaes');
        $idcurso =  $request->get('idcurso');
        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $periodo = $this->session->get('ie_per_cod');

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $institucioncurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idcurso);
        try {

            $iecoen = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idoferta);
            $iecoma = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->find($idofermaes);

            $em->remove($iecoma);

            $em->remove($iecoen);

            $em->flush();

            $em->getConnection()->commit();

            $query = $em->getConnection()->prepare('
                select a.id , a.persona_id, a.institucioneducativa_id,a.gestion_tipo_id,a.es_vigente_administrativo,
                      b.id as idformacion, b.formacion,
                      c.paterno,c.materno,c.nombre,c.carnet
                      from maestro_inscripcion a
	                      inner join formacion_tipo b on b.id =a.formacion_tipo_id
		                      inner join persona c on c.id = a.persona_id
              
                where a.institucioneducativa_id=:idcea and a.gestion_tipo_id = :gestion and a.es_vigente_administrativo=true 
                and periodo_tipo_id=:periodo and a.cargo_tipo_id =0 and c.id not in (select f.id
                    from institucioneducativa_curso a
	                    inner join permanente_institucioneducativa_cursocorto x on x.institucioneducativa_curso_id = a.id
		                      inner join permanente_cursocorto_tipo y on y.id= x.cursocorto_tipo_id	
	                              inner join institucioneducativa_curso_oferta b on b.insitucioneducativa_curso_id = a.id
		                              inner join institucioneducativa_curso_oferta_maestro c on c.institucioneducativa_curso_oferta_id = b.id
			                                inner join maestro_inscripcion d on d.id = c.maestro_inscripcion_id
	                                               inner join formacion_tipo e on e.id =d.formacion_tipo_id
		                                                  inner join persona f on f.id = d.persona_id
			
                    where a.institucioneducativa_id=:idcea AND d.gestion_tipo_id =:gestion AND a.id =:curso )
	    ');
            $query->bindValue(':idcea', $institucion);
            $query->bindValue(':gestion', $gestion);
            $query->bindValue(':curso', $idcurso);
            $query->bindValue(':periodo', $periodo);
            $query->execute();

            $maestros= $query->fetchAll();
            $querya = $em->getConnection()->prepare('
              select a.id, a.institucioneducativa_id,a.duracionhoras,
                    y.cursocorto,
                    b.id as idoferta,b.horasmes,
                    c.id as idofermaes,
                    d.id as idmaestro, d.persona_id, d.institucioneducativa_id,d.gestion_tipo_id,d.es_vigente_administrativo,
                    e.id as idformacion, e.formacion,
                    f.paterno,f.materno,f.nombre,f.carnet
                    from institucioneducativa_curso a
	                    inner join permanente_institucioneducativa_cursocorto x on x.institucioneducativa_curso_id = a.id
		                      inner join permanente_cursocorto_tipo y on y.id= x.cursocorto_tipo_id	
	                              inner join institucioneducativa_curso_oferta b on b.insitucioneducativa_curso_id = a.id
		                              inner join institucioneducativa_curso_oferta_maestro c on c.institucioneducativa_curso_oferta_id = b.id
			                                inner join maestro_inscripcion d on d.id = c.maestro_inscripcion_id
	                                               inner join formacion_tipo e on e.id =d.formacion_tipo_id
		                                                  inner join persona f on f.id = d.persona_id
			
                    where a.institucioneducativa_id=:idcea AND d.gestion_tipo_id = :gestion AND a.id = :curso 
');
            $querya->bindValue(':idcea', $institucion);
            $querya->bindValue(':curso', $idcurso);
            $querya->bindValue(':gestion', $gestion);
            $querya->execute();
            $maestrosins =$querya->fetchAll();

            $form = $this->createFormBuilder()
//                ->setAction($this->generateUrl('herramienta_per_cursos_cortos_add_maestro'))
                //  ->add('maestro', 'hidden', array('data' => $maestros))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('cursoscortos', 'hidden', array('data' => $idcurso))
                ->add('horas', 'text', array( 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control','pattern' => '[0-9]{1,2}', 'maxlength' => '2')))

                //->add('busqueda', 'text', array( 'required' => false, 'attr' => array('autocomplete' => 'on', 'class' => 'form-control')))

                ->getForm();


            //$data = $this->getAreas($infoUe);
            return $this->render('SiePermanenteBundle:CursosCortos:listMaestro.html.twig',array(
                'maestro' => $maestros,
                'maestroins' => $maestrosins,
                'institucioncurso'=>$institucioncurso,
                'infoUe'=>$infoUe,
                'form' => $form->createView()
            ));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
//            return $this->render('SieHerramientaBundle:InfoEstudianteAreas:index.html.twig',$data);
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!' . $ex));
        }
     }

    public function showEstudianteCursoCortoAction(Request $request){
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $areatematica = $em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->findAll();
        return $this->render('SiePermanenteBundle:CursosCortos:showEstudiante.html.twig.html.twig', array(
            'areatematica' => $areatematica

        ));
    }

    public function listarprovinciasAction($dpto) {

       // dump($dpto);die;

        try {
            $em = $this->getDoctrine()->getManager();


            $query = $em->createQuery(
                'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                ->setParameter('nivel', 9)
                ->setParameter('lt1', $dpto);
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

    public function listarmunicipiosAction($prov) {
        try {
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery(
                    'SELECT lt
                        FROM SieAppWebBundle:LugarTipo lt
                        WHERE lt.lugarNivel = :nivel
                        AND lt.lugarTipo = :lt1
                        ORDER BY lt.id')
                ->setParameter('nivel', 10)
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

    public function mostrarPobDetalleAction($pob) {
        try {

            $em = $this->getDoctrine()->getManager();

            $query = $em->getConnection()->prepare('select obs from permanente_poblacion_tipo
                              where id=:pobla                  
        ');
            $query->bindValue(':pobla', $pob);
            $query->execute();
           $poblaciones= $query->fetch();

        //   dump($poblaciones);die;


            $response = new JsonResponse();
            return $response->setData(array('poblaciones' => $poblaciones));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function seeStudentsAction(Request $request) {
        //
        $em = $this->getDoctrine()->getManager();

        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $dataUe=(unserialize($infoUe));
        $exist = true;
        $idcurso=$aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
      //  dump ($idcurso);die;
        $objStudents = array();

        $query = $em->getConnection()->prepare('
                select c.id as idcurso, b.id as idestins,d.id as estadomatriculaid,CASE d.estadomatricula when \'EFECTIVO\' THEN \'EFECTIVO\' when \'RETIRADO\' THEN \'RETIRADO\' when \'CONCLUIDO PERMANENTE\' THEN \'CONCLUIDO\' END AS estadomatricula, b.estudiante_id as idest, a .codigo_rude as codigorude, a.carnet_identidad as carnet,a.paterno,a.materno,a.nombre,a.fecha_nacimiento as fechanacimiento, e.genero 

                from estudiante a
                    inner join estudiante_inscripcion b on b.estudiante_id =a.id
                        inner join institucioneducativa_curso c on b.institucioneducativa_curso_id = c.id 
                            inner join estadomatricula_tipo d on d.id = b.estadomatricula_tipo_id
                                inner join genero_tipo e on a.genero_tipo_id = e.id

                
                where c.id =:idcurso      

               
        ');
        $query->bindValue(':idcurso', $idcurso);
        $query->execute();
        $objStudents= $query->fetchAll();
        $querya = $em->getConnection()->prepare('
                 select a.id, a.periodo_tipo_id, a.grado_tipo_id, a.gestion_tipo_id, a.nivel_tipo_id, a.turno_tipo_id,a.fecha_inicio,a.fecha_fin,a.duracionhoras,
                        b.esabierto, 
                        c.areatematica, d.poblacion,e.programa, f.sub_area, g.cursocorto,
                        h.id as codofermaes,h.horasmes, 
                        i.maestro_inscripcion_id,
                        k.paterno,k.materno,k.nombre,
                        m.id as percursocorid,m.sub_area_tipo_id,m.programa_tipo_id, m.areatematica_tipo_id,m.cursocorto_tipo_id,
                        n.turno
                    FROM
                        institucioneducativa_curso a 
                            left JOIN permanente_institucioneducativa_cursocorto b on a.id= b.institucioneducativa_curso_id
	                              left join permanente_area_tematica_tipo c on b.areatematica_tipo_id =c.id
		                              left join permanente_poblacion_tipo d on b.poblacion_tipo_id = d.id
			                                left join permanente_programa_tipo e on b.programa_tipo_id=e.id
				                                    left join permanente_sub_area_tipo f on b.sub_area_tipo_id= f.id
					                                  left join permanente_cursocorto_tipo g on cursocorto_tipo_id = g.id
						                                  left join institucioneducativa_curso_oferta h on  a.id = h.insitucioneducativa_curso_id	
							                                  left join institucioneducativa_curso_oferta_maestro i on h.id = i.institucioneducativa_curso_oferta_id
								                                  left join maestro_inscripcion j on i.maestro_inscripcion_id = j.id
									                                    left join persona k on j.persona_id =k.id
										                                    left join permanente_institucioneducativa_cursocorto m on m.institucioneducativa_curso_id = a.id
															left join turno_tipo n on a.turno_tipo_id =n.id
                                                
                where  a.nivel_tipo_id= :nivel and a.id=:idcurso
                
        ');
        $querya->bindValue(':nivel', 230);
        $querya->bindValue(':idcurso', $idcurso);
        $querya->execute();
        $cursoCorto= $querya->fetchAll();

      //  dump($cursoCorto);die;

        if (count($objStudents) > 0){
            $existins = true;
        }
        else {
            $existins = false;
        }
        $estadomatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findAll();
        $estadomatriculaArray = array();
        foreach($estadomatricula as $value){
            if( ($value->getId()==3)||($value->getId()==4)||($value->getId()==75))
            {
                if($value->getId()==75)
                {
                    $estadomatriculaArray[$value->getId()] ='CONCLUIDO';;
                }
                else
                {
                    $estadomatriculaArray[$value->getId()] = $value->getEstadomatricula();
                }


            }

        }
       // dump($objStudents);die;

        $form = $this->createFormBuilder()
            ->add('matricula', 'choice', array('required' => false, 'choices' => $estadomatriculaArray,  'attr' => array('class' => 'form-control')))
            ->getForm();
        //   dump($dataUe);
        //   dump($objStudents);die;
        return $this->render('SiePermanenteBundle:InfoEstudianteRequest:seeStudents.html.twig', array(
            'objStudents' => $objStudents,
            'objx' => $estadomatriculaArray,
            'form' => $form->createView(),
            'exist' => $exist,
            'cursocorto'=>$cursoCorto,
            'existins' => $existins,
            'infoUe' => $infoUe,
            'dataUe' => $dataUe,
            'totalInscritos'=>count($objStudents)

        ));
    }

    public function removeStudentsAction(Request $request) {
     //   dump($request);die;

        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $dataUe=(unserialize($infoUe));

        $idinscripcion = $request->get('idestins');
        $idcurso = $request->get('idcurso');
     //   dump($idinscripcion);die;
//
//        $idoferta =  $request->get('infoUe');
//
        $exist = true;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {

            $inscripcionestudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idinscripcion);

            $em->remove($inscripcionestudiante);
            $em->flush();

            $em->getConnection()->commit();

            $query = $em->getConnection()->prepare('
                select c.id as idcurso, b.id as idestins,d.id as estadomatriculaid,CASE d.estadomatricula when \'EFECTIVO\' THEN \'EFECTIVO\' when \'RETIRADO\' THEN \'RETIRADO\' when \'CONCLUIDO PERMANENTE\' THEN \'CONCLUIDO\' END AS estadomatricula, b.estudiante_id as idest, a .codigo_rude as codigorude, a.carnet_identidad as carnet,a.paterno,a.materno,a.nombre,a.fecha_nacimiento as fechanacimiento, e.genero 

                from estudiante a
                    inner join estudiante_inscripcion b on b.estudiante_id =a.id
                        inner join institucioneducativa_curso c on b.institucioneducativa_curso_id = c.id 
                            inner join estadomatricula_tipo d on d.id = b.estadomatricula_tipo_id
                                inner join genero_tipo e on a.genero_tipo_id = e.id

                
                where c.id =:idcurso      

               
        ');
            $query->bindValue(':idcurso', $idcurso);
            $query->execute();
            $objStudents= $query->fetchAll();
            $querya = $em->getConnection()->prepare('
                 select a.id, a.periodo_tipo_id, a.grado_tipo_id, a.gestion_tipo_id, a.nivel_tipo_id, a.turno_tipo_id,a.fecha_inicio,a.fecha_fin,a.duracionhoras,
                        b.esabierto, 
                        c.areatematica, d.poblacion,e.programa, f.sub_area, g.cursocorto,
                        h.id as codofermaes,h.horasmes, 
                        i.maestro_inscripcion_id,
                        k.paterno,k.materno,k.nombre,
                        m.id as percursocorid,m.sub_area_tipo_id,m.programa_tipo_id, m.areatematica_tipo_id,m.cursocorto_tipo_id,
                        n.turno
                    FROM
                        institucioneducativa_curso a 
                            left JOIN permanente_institucioneducativa_cursocorto b on a.id= b.institucioneducativa_curso_id
	                              left join permanente_area_tematica_tipo c on b.areatematica_tipo_id =c.id
		                              left join permanente_poblacion_tipo d on b.poblacion_tipo_id = d.id
			                                left join permanente_programa_tipo e on b.programa_tipo_id=e.id
				                                    left join permanente_sub_area_tipo f on b.sub_area_tipo_id= f.id
					                                  left join permanente_cursocorto_tipo g on cursocorto_tipo_id = g.id
						                                  left join institucioneducativa_curso_oferta h on  a.id = h.insitucioneducativa_curso_id	
							                                  left join institucioneducativa_curso_oferta_maestro i on h.id = i.institucioneducativa_curso_oferta_id
								                                  left join maestro_inscripcion j on i.maestro_inscripcion_id = j.id
									                                    left join persona k on j.persona_id =k.id
										                                    left join permanente_institucioneducativa_cursocorto m on m.institucioneducativa_curso_id = a.id
															left join turno_tipo n on a.turno_tipo_id =n.id
                                                
                where  a.nivel_tipo_id= :nivel and a.id=:idcurso
                
        ');
            $querya->bindValue(':nivel', 230);
            $querya->bindValue(':idcurso', $idcurso);
            $querya->execute();

            $cursoCorto= $querya->fetchAll();
            //  dump($cursoCorto);die;

            $estadomatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findAll();
            $estadomatriculaArray = array();
            foreach($estadomatricula as $value){
                if( ($value->getId()==3)||($value->getId()==4)||($value->getId()==5))
                {
                    $estadomatriculaArray[$value->getId()] = $value->getEstadomatricula();
                }

            }

            if (count($objStudents) > 0){
                $existins = true;
            }
            else {
                $existins = false;
            }

           //$data = $this->getAreas($infoUe);
            return $this->render('SiePermanenteBundle:InfoEstudianteRequest:seeStudents.html.twig', array(
                'objStudents' => $objStudents,
                'exist' => $exist,
                'objx' => $estadomatriculaArray,
                'cursocorto'=>$cursoCorto,
                'existins' => $existins,
                  'infoUe' => $infoUe,
                  'dataUe' => $dataUe,
                'totalInscritos'=>count($objStudents)
            ));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
//            return $this->render('SieHerramientaBundle:InfoEstudianteAreas:index.html.twig',$data);
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!' . $ex));
        }
    }

    public function closeInscriptionAction(Request $request) {

        //  dump($request);die;
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $dataUe=(unserialize($infoUe));
        $exist=true;
        try {

            $idcurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $em->getConnection()->commit();

            $institucioncursocorto=$em->getRepository('SieAppWebBundle:PermanenteInstitucioneducativaCursocorto')->findOneBy(array('institucioneducativaCurso'=>$idcurso));
            $institucioncursocorto  ->setEsabierto(false);
            $em->persist($institucioncursocorto);
            $em->flush();


            $query = $em->getConnection()->prepare('
                select c.id as idcurso, b.id as idestins,d.id as estadomatriculaid,CASE d.estadomatricula when \'EFECTIVO\' THEN \'EFECTIVO\' when \'RETIRADO\' THEN \'RETIRADO\' when \'CONCLUIDO PERMANENTE\' THEN \'CONCLUIDO\' END AS estadomatricula, b.estudiante_id as idest, a .codigo_rude as codigorude, a.carnet_identidad as carnet,a.paterno,a.materno,a.nombre,a.fecha_nacimiento as fechanacimiento, e.genero 

                from estudiante a
                    inner join estudiante_inscripcion b on b.estudiante_id =a.id
                        inner join institucioneducativa_curso c on b.institucioneducativa_curso_id = c.id 
                            inner join estadomatricula_tipo d on d.id = b.estadomatricula_tipo_id
                                inner join genero_tipo e on a.genero_tipo_id = e.id

                
                where c.id =:idcurso      

               
        ');
            $query->bindValue(':idcurso', $idcurso);
            $query->execute();
            $objStudents= $query->fetchAll();
            $querya = $em->getConnection()->prepare('
                 select a.id, a.periodo_tipo_id, a.grado_tipo_id, a.gestion_tipo_id, a.nivel_tipo_id, a.turno_tipo_id,a.fecha_inicio,a.fecha_fin,a.duracionhoras,
                        b.esabierto, 
                        c.areatematica, d.poblacion,e.programa, f.sub_area, g.cursocorto,
                        h.id as codofermaes,h.horasmes, 
                        i.maestro_inscripcion_id,
                        k.paterno,k.materno,k.nombre,
                        m.id as percursocorid,m.sub_area_tipo_id,m.programa_tipo_id, m.areatematica_tipo_id,m.cursocorto_tipo_id,
                        n.turno
                    FROM
                        institucioneducativa_curso a 
                            left JOIN permanente_institucioneducativa_cursocorto b on a.id= b.institucioneducativa_curso_id
	                              left join permanente_area_tematica_tipo c on b.areatematica_tipo_id =c.id
		                              left join permanente_poblacion_tipo d on b.poblacion_tipo_id = d.id
			                                left join permanente_programa_tipo e on b.programa_tipo_id=e.id
				                                    left join permanente_sub_area_tipo f on b.sub_area_tipo_id= f.id
					                                  left join permanente_cursocorto_tipo g on cursocorto_tipo_id = g.id
						                                  left join institucioneducativa_curso_oferta h on  a.id = h.insitucioneducativa_curso_id	
							                                  left join institucioneducativa_curso_oferta_maestro i on h.id = i.institucioneducativa_curso_oferta_id
								                                  left join maestro_inscripcion j on i.maestro_inscripcion_id = j.id
									                                    left join persona k on j.persona_id =k.id
										                                    left join permanente_institucioneducativa_cursocorto m on m.institucioneducativa_curso_id = a.id
															left join turno_tipo n on a.turno_tipo_id =n.id
                                                
                where  a.nivel_tipo_id= :nivel and a.id=:idcurso
                
        ');
            $querya->bindValue(':nivel', 230);
            $querya->bindValue(':idcurso', $idcurso);
            $querya->execute();

            $cursoCorto= $querya->fetchAll();
            // dump($objStudents);die;

            if (count($objStudents) > 0){
                $existins = true;
            }
            else {
                $existins = false;
            }
            foreach($objStudents as $value){
              // dump($value);die;

                if($value['estadomatriculaid']==4)
                {
                    $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $value['idestins']));
                    $estudianteInscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(75));
                   //dump($estudianteInscripcion);die;
                    $em->persist($estudianteInscripcion);
                    $em->flush();
                }

            }

            $estadomatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findAll();
            $estadomatriculaArray = array();
            foreach($estadomatricula as $value){
                if( ($value->getId()==3)||($value->getId()==4)||($value->getId()==75))
                {
                    if($value->getId()==75)
                    {
                        $estadomatriculaArray[$value->getId()] ='CONCLUIDO';
                    }
                    else
                    {
                        $estadomatriculaArray[$value->getId()] = $value->getEstadomatricula();
                    }
                }

            }

       //    dump($objStudents);die;
            return $this->render('SiePermanenteBundle:InfoEstudianteRequest:seeStudents.html.twig', array(
                'objStudents' => $objStudents,
                'exist' => $exist,
                'objx' => $estadomatriculaArray,
                'cursocorto'=>$cursoCorto,
                'existins' => $existins,
                'infoUe' => $infoUe,
                'dataUe' => $dataUe,
                'totalInscritos'=>count($objStudents)
            ));



           //   dump ($objStudents);die;

//            dump ($infoUe);
//            dump ($dataUe);die;
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function changeEstadoAction(Request $request)
    {

        $matricula = $request->get('matricula');
        $idestins = $request->get('idestins');

        if($matricula=='CONCLUIDO')
        {
            $matricula='CONCLUIDO PERMANENTE';
        }
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $em->getConnection()->commit();
        $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idestins);
        $idmatricula=$em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneBy(array('estadomatricula'=>$matricula));
        $estudianteInscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneBy(array('id' => $idmatricula)));

        $em->persist($estudianteInscripcion);
        $em->flush();
     //   dump($estudianteInscripcion);die;

        $response = new JsonResponse();
        return $response->setData(array('mensaje' => 'Estado del Participante Cambiado!'));
       // return $response->setData(array('mensaje'=>'¡Proceso realizado exitosamente!'));

    }

    public function listaCursoAction(Request $request)
    {
        try
        {
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_per_show_list_curso_corto'))

                // ->add('fechaInicio','text',array('label'=>'Fecha Inicio','attr'=>array('class'=>'form-control calendario','autocomplete'=>'off')))
//                ->add('curso', 'text', array( 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('fechaInicio', 'datetime', array('widget' => 'single_text','date_format' => 'dd-MM-yyyy','attr' => array('class' => 'form-control calendario')))
                ->add('fechaFin', 'datetime', array('widget' => 'single_text','date_format' => 'dd-MM-yyyy','attr' => array('class' => 'form-control calendario')))
//                 ->add('mostrar', 'submit', array('label' => 'Mostrar ', 'attr' => array('class' => 'btn btn-primary', 'disbled' => true)))
                ->getForm();

            return $this->render('SiePermanenteBundle:CursosCortos:listCursos.html.twig', array(

                'form' => $form->createView()
            ));
        }catch (Exception $ex)
        {

        }

    }

    public function showlistaCursoAction(Request $request)
    {
        //obtenemos los campos enviados
        $form = $request->get('form');
//
        $institucion = $this->session->get('ie_id');
        try
        {

           $fechaini = $form['fechaInicio'] ;
            $fechafin = $form['fechaFin'] ;

         //   dump($fechaini);die;
               //new \DateTime($->format('d-m-Y'));

            $em = $this->getDoctrine()->getManager();
            $querya = $em->getConnection()->prepare('
                 select distinct a.id, a.periodo_tipo_id, a.grado_tipo_id, a.gestion_tipo_id, a.nivel_tipo_id, a.turno_tipo_id,a.fecha_inicio,a.fecha_fin,a.duracionhoras,
                        b.esabierto, 
                        c.areatematica, d.poblacion,e.programa, f.sub_area, g.cursocorto,
                        m.id as percursocorid,m.sub_area_tipo_id,m.programa_tipo_id, m.areatematica_tipo_id,m.cursocorto_tipo_id,
                        n.turno
                    FROM
                        institucioneducativa_curso a 
                            left JOIN permanente_institucioneducativa_cursocorto b on a.id= b.institucioneducativa_curso_id
	                              left join permanente_area_tematica_tipo c on b.areatematica_tipo_id =c.id
		                              left join permanente_poblacion_tipo d on b.poblacion_tipo_id = d.id
			                                left join permanente_programa_tipo e on b.programa_tipo_id=e.id
				                                    left join permanente_sub_area_tipo f on b.sub_area_tipo_id= f.id
					                                  left join permanente_cursocorto_tipo g on cursocorto_tipo_id = g.id
						                              left join permanente_institucioneducativa_cursocorto m on m.institucioneducativa_curso_id = a.id
											left join turno_tipo n on a.turno_tipo_id =n.id
                                                
                where  a.nivel_tipo_id= :nivel and a.institucioneducativa_id =:idcea AND a.fecha_inicio BETWEEN :fechaini AND :fechafin
        ');
            $querya->bindValue(':nivel', 230);
            $querya->bindValue(':idcea', $institucion);
            $querya->bindValue(':fechaini', $fechaini);
            $querya->bindValue(':fechafin', $fechafin);
            $querya->execute();
            $cursoCorto= $querya->fetchAll();

            //dump($cursoCorto);die;
            return $this->render('SiePermanenteBundle:CursosCortos:listCursosCortos.html.twig', array(
                 'cursocorto'=>$cursoCorto,
                'fechaInicio'=>$fechaini,
                'fechaFin'=>$fechafin

            ));

        }catch (Exception $ex)
        {

        }

    }

    public function openCursoAction(Request $request)
    {
        $em= $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        //get the send values
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
//          dump($aInfoUeducativa['ueducativaInfoId']['nivelId']);die;
        $iecid = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
        //dump($iecid);die;
        //$iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecid);
        $em->getConnection()->beginTransaction();
        try
        {
            $institucioncursocorto=$em->getRepository('SieAppWebBundle:PermanenteInstitucioneducativaCursocorto')->findOneBy(array('institucioneducativaCurso'=>$iecid));
            $institucioncursocorto->setEsabierto(true);
            $em->persist($institucioncursocorto);
            $em->flush();
            $response = new JsonResponse();

            $em->getConnection()->commit();

            return $response->setData(array('mensaje'=>'¡Proceso realizado exitosamente!'));
        }
       catch (Exception $ex)
       {
           return $ex;
       }
    }

}
