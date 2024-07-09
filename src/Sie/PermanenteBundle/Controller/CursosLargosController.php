<?php

namespace Sie\PermanenteBundle\Controller;

use Sie\AppWebBundle\Entity\EstudianteNota;
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

class CursosLargosController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct() {
        //init the ses                      sion values
        $this->session = new Session();

    }

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
        $querya = $em->getConnection()->prepare('select h.id as iecid, psat.id as subareaid,psat.sub_area as subarea,ppt.id as programaid,ppt.programa,
        --a.id as programaid, a.facultad_area as programa,
        d.id as acreditacionid, d.acreditacion as acreditacion,b.id as cursolargoid,b.especialidad as cursolargo,pt.id as paraleloId, pt.paralelo, picc.esabierto
        from superior_facultad_area_tipo a
        inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
        inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
        inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
        inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id
        inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id
        inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
        inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id = g.id
        inner join superior_turno_tipo q on h.turno_tipo_id = q.id
        inner join permanente_institucioneducativa_cursocorto picc on picc.institucioneducativa_curso_id = h.id
        inner join permanente_programa_tipo ppt on ppt.id =picc.programa_tipo_id
        inner join permanente_sub_area_tipo psat on psat.id = picc.sub_area_tipo_id
        inner join paralelo_tipo pt on pt.id=h.paralelo_tipo_id
        where f.gestion_tipo_id=:gestion and f.institucioneducativa_id=:sie
        and f.sucursal_tipo_id=:suc --and f.periodo_tipo_id=1
		and a.id =40  and  h.nivel_tipo_id =231
        ');
        //$querya->bindValue(':nivel', 231);
        $querya->bindValue(':sie', $sie);
        $querya->bindValue(':gestion', $gestion);
        $querya->bindValue(':suc', $suc);
         //    $querya->bindValue(':periodo', $periodo);
        $querya->execute();

        $objUeducativa= $querya->fetchAll();
        //dump($objUeducativa);die;
        $exist = true;
        $aInfoUnidadEductiva = array();
        if ($objUeducativa) {
            foreach ($objUeducativa as $uEducativa) {

                $sinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('subarea' => $uEducativa['subarea'],'programa' => $uEducativa['programa'], 'cursolargo' => $uEducativa['cursolargo'], 'acreditacion' => $uEducativa['acreditacion'], 'paralelo' => $uEducativa['paralelo'],
                    'ueducativaInfoId' => array('subareaid' => $uEducativa['subareaid'],'programaid' => $uEducativa['programaid'], 'cursolargoid' => $uEducativa['cursolargoid'], 'acreditacionid' => $uEducativa['acreditacionid'], 'iecid' => $uEducativa['iecid'], 'iecId' => $uEducativa['iecid'], 'paraleloId' => $uEducativa['paraleloid'],'esabierto'=> $uEducativa['esabierto'])
                )));

                $aInfoUnidadEductiva[$uEducativa['esabierto']][$uEducativa['subarea']][$uEducativa['programa']][$uEducativa['cursolargo']] [$uEducativa['acreditacion']][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa, 'esabierto'=>$uEducativa['esabierto'], 'iecId'=> $uEducativa['iecid']);

            }
         //dump($aInfoUnidadEductiva);die;
        } else {
            $message = 'No existe información del Centro de Educación  para la gestión seleccionada.';
            $this->addFlash('warninresult', $message);
            $exist = false;
        }

        $areatematica = $em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->findAll();


        return $this->render('SiePermanenteBundle:CursosLargos:index.html.twig', array(
            'exist' => $exist,
            'aInfoUnidadEductiva' => $aInfoUnidadEductiva,
            'areatematica' => $areatematica,
            'gestion' =>$gestion
           // 'cursosLargos' => $cursosLargos


        ));
    }

    public function newCursoLargoAction(Request $request){
            // dump($request);die;
        try {

            $this->session = $request->getSession();
            $sie= $this->session->get('ie_id');
            $id_usuario = $this->session->get('userId');
            //validation if the user is logged
            if (!isset($id_usuario)) {
                return $this->redirect($this->generateUrl('login'));
            }

            //llama a los campos de las tablas para mostrar en la vista
            $em = $this->getDoctrine()->getManager();
            $subarea = $em->getRepository('SieAppWebBundle:PermanenteSubAreaTipo')->findAll();
            $areatematica = $em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->findAll();
            //$poblacion = $em->getRepository('SieAppWebBundle:PermanentePoblacionTipo')->findAll();
            $organizacion = $em->getRepository('SieAppWebBundle:PermanenteOrganizacionTipo')->findAll();
            $programa = $em->getRepository('SieAppWebBundle:PermanenteProgramaTipo')->findAll();
            $cursosCortos = $em->getRepository('SieAppWebBundle:PermanenteCursocortoTipo')->findAll();
            $turno = $em->getRepository('SieAppWebBundle:TurnoTipo')->findAll();
           // $especialidad= $em->getRepository('SieAppWebBundle:SuperiorFacultadAreaTipo')->findAll();
            $gestion = $this->session->get('ie_gestion');

            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare('select * from superior_facultad_area_tipo	
                where institucioneducativa_tipo_id =5');
            $query->execute();
            $progArea= $query->fetchAll();
      
            $progAreaArray = array();
            foreach ($progArea as $value) {
                $progAreaArray[$value['id']] =$value['facultad_area'];
            }

            $organizacionArray = array();
            foreach ($organizacion as $value) {
                $organizacionArray[$value->getId()] = $value->getOrganizacion();
            }

            $subareaArray = array();
           
            foreach ($subarea as $value) {
                    
                    if (($value->getId() != 0 && $value->getEsActivo() === true)) {
                        if (($value->getId() != 9 && $gestion < 2023)) {
                            $subareaArray[$value->getId()] = $value->getSubArea();
                        }
                        if (($gestion >= 2023)) {
                            $subareaArray[$value->getId()] = $value->getSubArea();
                        }
                }
            }     
           
            $programaArray = array();
            foreach ($programa as $value) {
                if (($value->getId() ==1 )||($value->getId() ==2 )||($value->getId() ==3 ))
                {
                    $programaArray[$value->getId()] = $value->getPrograma();
                }
            }

            $areatematicaArray = array();
            foreach ($areatematica as $value) {
                $areatematicaArray[$value->getId()] = $value->getAreatematica();
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

            $turnoArray = array();
            foreach ($turno as $value) {
                if ($value->getId() == 0)
                {

                }else {
                    $turnoArray[$value->getId()] = $value->getTurno();
                }
            }

            $query = $em->createQuery(
                'SELECT p FROM SieAppWebBundle:ParaleloTipo p
                WHERE p.id != :id'
                )->setParameter('id',0);
            $paralelos_result = $query->getResult();
            $paralelos = array();
            foreach ($paralelos_result as $p){
                $paralelos[$p->getId()] = $p->getParalelo();
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


            $sie= $this->session->get('ie_id'); 
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare('	select distinct sest.id , sest.especialidad
				from institucioneducativa ined
					inner join superior_institucioneducativa_acreditacion sia on ined.id = sia.institucioneducativa_id
						inner join superior_acreditacion_especialidad sae on sia.acreditacion_especialidad_id =sae.id
								inner join superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id = sest.id
										inner join superior_facultad_area_tipo sfat on sfat.id = sest.superior_facultad_area_tipo_id
											where ined.id=:sie and sfat.id =40
        ');
            $query->bindValue(':sie', $sie);
            $query->execute();
            $espUE= $query->fetchAll();
            
            $espUEArray = array();
            foreach ($espUE as $value) {

                $espUEArray[$value['id']] =$value['especialidad'];
            }
            
            $prov = array();
            $muni = array();
            $pob = array();
            $esp = array();
            $niv = array();
            $hrs = array();
            // Dibuja la Vista para la cracion de un nuevo curso
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_per_cursos_largos_create'))
                ->add('subarea', 'choice', array('required' => true, 'choices' => $subareaArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('paralelo','choice',array('label'=>'Paralelo','choices'=>$paralelos,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
              //  ->add('areatematica', 'choice', array('required' => true, 'choices' => $areatematicaArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                //->add('programa', 'choice', array('label' => 'Programa', 'required' => true, 'choices' => $programaArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('especialidad', 'choice', array( 'required' => true, 'choices' => $espUEArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarNiveles(this.value)')))
                ->add('nivel', 'choice', array( 'required' => true, 'choices' => $niv, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'mostrarHoras(this.value)')))
                ->add('horas', 'text', array( 'label' => 'horas','required' => false, 'attr' => array('class' => 'form-control','readonly' => true)))
                ->add('organizacion', 'choice', array( 'required' => true, 'choices' => $organizacionArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control','onchange' => 'mostrarPobDetalleCL(this.value)')))
                ->add('poblacion', 'choice', array( 'required' => true, 'choices' => $pob, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('departamento', 'choice', array('label' => 'Departamento', 'required' => true, 'choices' => $dptoNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarProvincias(this.value)')))
                ->add('provincia', 'choice', array('label' => 'Provincia', 'required' => true, 'choices' => $prov, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarMunicipios(this.value)')))
                ->add('municipio', 'choice', array('label' => 'Municipio', 'required' => true, 'choices' => $muni, 'empty_value' => 'Seleccionar...','attr' => array('class' => 'form-control')))
                ->add('fechaInicio', 'datetime', array('widget' => 'single_text','date_format' => 'dd-MM-yyyy','attr' => array('class' => 'form-control calendario')))
                ->add('fechaFin', 'datetime', array('widget' => 'single_text','date_format' => 'dd-MM-yyyy','attr' => array('class' => 'form-control calendario')))
                ->add('turno', 'choice', array( 'required' => true, 'choices' => $turnoArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
           //     ->add('horas', 'text', array( 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true, 'pattern' => '[0-9]{1,2}', 'maxlength' => '2')))
                ->add('lugar', 'text', array( 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
                //->add('pobdetalle', 'text', array( 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
            //                ->add('pobobs', 'textarea', array( 'required' => false, 'attr' => array('class' => 'form-control','readonly' => true)))
                ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'disbled' => true)))
                ->getForm();
            return $this->render('SiePermanenteBundle:CursosLargos:new.html.twig', array(
                'form' => $form->createView()
            ));
        }
        catch (Exception $ex)
        {

        }
    }
    public function validaHorasCurso($institucion,$nivel,$especialidad,$gestion){     
        //Funcion que permite obtener la suma de todas las horas registradas         
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select nivel.*, v.idsae, v.idacr, v.modulo, v.idmodulo, v.horas, coalesce(v.tothoras,0) as tothoras, v.idspm, v.cantidad from (
            select distinct on (sae.id, sest.id ,sat.id ) sae.id, sest.id as idespecialidad,sat.id as idacreditacion, sest.especialidad, sat.acreditacion
            , sia.id as idsia, sip.id as idsip
            from superior_acreditacion_especialidad sae
            inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
            inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
            inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
            inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
            inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
            --	inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
            --		inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
            where sat.id in (1,20,32) and sfat.id=40 and sest.id=:esp
            and sia.gestion_tipo_id=:gestion
            and sia.institucioneducativa_id =:sie and sat.id =:niv
            )as nivel
        left join (
            select idsae,idacr
            --,idespecialidad,especialidad,idacreditacion,acreditacion,idsia,idsip 
            , string_agg(modulo, ',') as modulo, string_agg(idmodulo::character varying, ',') as idmodulo, string_agg(horas::character varying, ',')as horas, sum(horas) as tothoras, string_agg(idsmp::character varying, ',')as idspm,COUNT (idmodulo) AS cantidad
            from(select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacr, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
            from superior_acreditacion_especialidad sae
            inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
            inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
            inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
            inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
            inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
            left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
            left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
            where sat.id in (1,20,32) and sfat.id=40 and sest.id=:esp
            and sia.gestion_tipo_id=:gestion
            and sia.institucioneducativa_id =:sie and sat.id =:niv
            and smt.esvigente =true
            ) dat
            group by  idsae,idespecialidad,especialidad,idacr,acreditacion,idsia,idsip
            )as v on v.idacr = nivel.idacreditacion ");
        //$query->bindValue(':suc', $sucursal);
        $query->bindValue(':esp', (int)$especialidad);
        $query->bindValue(':sie', (int)$institucion);
        $query->bindValue(':niv', (int)$nivel);
        $query->bindValue(':gestion', (int)$gestion);
        $query->execute();
        $totalHoras= $query->fetch();       
        return $totalHoras;
    }
    public function createCursoLargoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        //LLama a variables de Sesion
        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_subcea');
        $periodo = $this->session->get('ie_per_cod'); 
        //recibe los datos del formulario de vista
        $form = $request->get('form'); //dump($form['horas']);die;
        //validar que el curso tenga las horas completas para cada el nivel seleccionado
        $horas = $this->validaHorasCurso($institucion,$form['nivel'],$form['especialidad'],$gestion);
        $horasTotal = (int)$horas["tothoras"];
        $horasSol = $form['horas'];
        $horasSolicitado= (int)$form['horas'];        
        if($horasSolicitado == $horasTotal ){ //dump("siii");die;//si el nivel seleccionado cumple con el total de horas
            $query = $em->getConnection()->prepare("
            select b.id as especialidadid, b.especialidad as especialidad,d.id as acreditacionid,d.acreditacion as acreditacion,c.id espacredid,e.id as supinsacredid, g.id  as supinstperiodoid
                from superior_facultad_area_tipo a  
                inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id
                inner join superior_institucioneducativa_periodo g on e.id = g.superior_institucioneducativa_acreditacion_id
                where e.institucioneducativa_id =".$institucion." and f.sucursal_tipo_id=".$sucursal."
                and d.id= ".$form['nivel']." and b.id=".$form['especialidad']." and f.gestion_tipo_id =".$gestion."::double precision ");
            $query->execute();
            $idpersup= $query->fetchAll();
            $em->getConnection()->beginTransaction();
            
            //Invoca a una funcion de Base de Datos Necesaria para cualquier INSERT, para que se reinicie la secuencia de ingreso de datos
            //  $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso');")->execute();
            // Realiza un INSERT para la creacion de un curso nuevo con los datos extraidos de la vista
            if (count($idpersup)>1){ 
                $this->get('session')->getFlashBag()->add('advertencia', 'Inconsistencia en la especialidad, se sugiere verificar la malla curricular.');
                return $this->redirect($this->generateUrl('herramienta_per_cursos_largos_index'));
            }

            try{
                
                $institucioncurso = new  InstitucioneducativaCurso();
                $institucioncurso ->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(231));
                $institucioncurso ->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find(0));
                $institucioncurso ->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(99));
                $institucioncurso ->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find($form['paralelo']));
                $institucioncurso ->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                $institucioncurso ->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find($this->session->get('ie_subcea')));
                $institucioncurso ->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
                $institucioncurso ->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find($this->session->get('ie_per_cod')));
                $institucioncurso ->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneBy(array('id' => $form['turno'])));
                $institucioncurso ->setDuracionhoras($form['horas']);
                $institucioncurso ->setSuperiorInstitucioneducativaPeriodo($em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->findOneBy(array('id' => $idpersup[0]['supinstperiodoid'])));
                $institucioncurso ->setFechaInicio(new \DateTime($form['fechaInicio']));
                $institucioncurso ->setFechaFin(new \DateTime($form['fechaFin']));
                $em->persist($institucioncurso); 
                $em->flush(); 
            // $em->flush($institucioncurso);

            /*  if($form['programa']==40)
                {
                    $programaid =1;
                }elseif($form['programa']==41)
                {
                    $programaid =2;
                }elseif($form['programa']==42)
                {
                    $programaid =4;
                } */                
            
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('permanente_institucioneducativa_cursocorto');")->execute();
                $institucioncursocorto = new PermanenteInstitucioneducativaCursocorto();
                $institucioncursocorto  ->setInstitucioneducativaCurso($institucioncurso);
                $institucioncursocorto  ->setSubAreaTipo($em->getRepository('SieAppWebBundle:PermanenteSubAreaTipo')->findOneBy(array('id' => $form['subarea'])));
                $institucioncursocorto  ->setProgramaTipo($em->getRepository('SieAppWebBundle:PermanenteProgramaTipo')->findOneBy(array('id' => 0)));
                $institucioncursocorto  ->setAreatematicaTipo($em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->find(10));
                $institucioncursocorto  ->setCursocortoTipo($em->getRepository('SieAppWebBundle:PermanenteCursocortoTipo')->find(0));
                $institucioncursocorto  ->setEsabierto(true);
                $institucioncursocorto  ->setPoblacionTipo($em->getRepository('SieAppWebBundle:PermanentePoblacionTipo')->findOneBy(array('id' => $form['poblacion'])));
                $institucioncursocorto  ->setLugarTipoDepartamento($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['departamento'])));
                $institucioncursocorto  ->setLugarTipoProvincia($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['provincia'])));
                $institucioncursocorto  ->setLugarTipoMunicipio($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['municipio'])));
                $institucioncursocorto  ->setLugarDetalle($form['lugar']);
                $em->persist($institucioncursocorto);
                $em->flush();              
            
                $query = $em->getConnection()->prepare('
                    select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
                    from superior_acreditacion_especialidad sae
                    inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
                    inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
                    inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
                    inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                    inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                    inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                    inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                    where sat.id =:niv and sfat.id=40 and sest.id=:esp 
                ');
                $query->bindValue(':niv', $form['nivel']);
                $query->bindValue(':esp', $form['especialidad']);
                $query->execute();
                $listamodulos= $query->fetchAll(); //dump($listamodulos);die;
                $i =0;
                $modulosArray = array();                
                //validar que el curso tenga modulos creados en la malla curricular
                if($listamodulos){
                    foreach ($listamodulos as $value) {
                        $modulosArray[$i] =$value['idsmp'];
                    // $em->getConnection()->beginTransaction();
                        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();
                        $institucioncursoferta = new InstitucioneducativaCursoOferta();
                        $institucioncursoferta ->setInsitucioneducativaCurso($institucioncurso);
                        $institucioncursoferta ->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find(2));
                        $institucioncursoferta->setSuperiorModuloPeriodo($em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->findOneBy(array('id' =>$value['idsmp'])));
                        $em->persist($institucioncursoferta);
                        //$em->flush($institucioncursoferta);
                        $i++;
                    } 
                    $em->flush();
                    //$em->flush($institucioncursoferta);
                    $em->getConnection()->commit();
                    $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
                    return $this->redirect($this->generateUrl('herramienta_per_cursos_largos_index'));
                }
                else{ //En caso de que la mencion asignada al curso no tenga modulos creados
                    $this->get('session')->getFlashBag()->add('advertencia', 'La mención seleccionada no tiene módulos registrados, se sugiere verificar la malla curricular.');
                    return $this->redirect($this->generateUrl('herramienta_per_cursos_largos_index'));
                }
            }catch(Exception $ex){
                $em->getConnection()->rollback();
                $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
                return $this->redirect($this->generateUrl('herramienta_per_cursos_largos_index'));
            }
        }else{//si el nivel seleccionado no cumple con el total de horas
            $this->get('session')->getFlashBag()->add('newError', 'Debe completar el registro de todos lo módulos para la especialidad solicitada. Se recomienda verificar la Malla Curricular.');
            return $this->redirect($this->generateUrl('herramienta_per_cursos_largos_index'));
        }
    }

    public function editCursoLargoAction(Request $request){
      //Recibe los datos de informacion para su edicion
      //  dump($request);die;
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
       // dump($aInfoUeducativa);die;
        $idcurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
        $idesp = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['cursolargoid'];
        $idnivel = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['acreditacionid'];
        $espNombre = $aInfoUeducativa['ueducativaInfo']['cursolargo'];
        $nivEsp= $aInfoUeducativa['ueducativaInfo']['acreditacion'];
        $paralelo = $aInfoUeducativa['ueducativaInfo']['paralelo'];
      //  dump($idcurso);die;
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
            $organizacion = $em->getRepository('SieAppWebBundle:PermanenteOrganizacionTipo')->findAll();
            $programa = $em->getRepository('SieAppWebBundle:PermanenteProgramaTipo')->findAll();
            $cursosCortos = $em->getRepository('SieAppWebBundle:PermanenteCursocortoTipo')->findAll();
            $turno = $em->getRepository('SieAppWebBundle:TurnoTipo')->findAll();
            $institucioncurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idcurso);
           $institucioncursocorto = $em->getRepository('SieAppWebBundle:PermanenteInstitucioneducativaCursocorto')->findOneBy(array('institucioneducativaCurso'=>$idcurso));
           // $especialidadTipo = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->findOneBy(array('id' =>$idesp));
           // $espNombre =$especialidadTipo->getEspecialidad();
            $deptoid=$institucioncursocorto->getLugarTipoDepartamento()->getId();
            $provid=$institucioncursocorto->getLugarTipoProvincia()->getId();
            $munid=$institucioncursocorto->getLugarTipoMunicipio()->getId();
            $lugar=$institucioncursocorto->getLugarDetalle();
            $pobdetalle=$institucioncursocorto->getPoblacionDetalle();
            $idpob=$institucioncursocorto->getPoblacionTipo()->getId();
            $pobNombre = $em->getRepository('SieAppWebBundle:PermanentePoblacionTipo')->findOneBy(array('id'=>$idpob));
            if($pobNombre){
                    $pobNombre=$pobNombre->getPoblacion();
            }else{
                    $pobNombre='';
            }
            $organizacion = $em->getRepository('SieAppWebBundle:PermanenteOrganizacionTipo')->findAll();
          //  $pobdetalle
            $arraypob= array();
            $pobobservacion=$em->getRepository('SieAppWebBundle:PermanentePoblacionTipo')->find($institucioncursocorto->getPoblacionTipo()->getId());

            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare('select organizacion_tipo_id from permanente_poblacion_tipo
                        where id=:pobla
            ');
            $query->bindValue(':pobla', $idpob);
            $query->execute();
            $idorg= $query->fetch();
            //dump($idorg);die;
            $query = $em->getConnection()->prepare('select * from permanente_poblacion_tipo
                        where organizacion_tipo_id=:pobla
            ');
            $query->bindValue(':pobla', $idorg['organizacion_tipo_id']);
            $query->execute();
            $poblaciones= $query->fetchAll();

            $query = $em->createQuery(
                                                    'SELECT p FROM SieAppWebBundle:ParaleloTipo p
                                                    WHERE p.id != :id'
                                                    )->setParameter('id',0);
                $paralelos_result = $query->getResult();
                $paralelos = array();
                foreach ($paralelos_result as $p){
                    $paralelos[$p->getId()] = $p->getParalelo();
                }
            //   dump($idorg['organizacion_tipo_id']);die;

            $poblacionesArray = array();
            foreach ($poblaciones as $value) {
                $poblacionesArray[$value['id']] =$value['poblacion'];
                //$poblacionesArray[$c->getId()] = $c->get();
            }

            $sie= $this->session->get('ie_id');

            $query = $em->getConnection()->prepare('
							select sae.id,sat.id as acreditacionid, sat.acreditacion 
							from superior_acreditacion_especialidad sae
									inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
											inner join superior_institucioneducativa_acreditacion sia on sia.acreditacion_especialidad_id = sae.id
												inner join institucioneducativa ie on sia.institucioneducativa_id = ie.id
											where sae.superior_especialidad_tipo_id = :espec
											and ie.id=:sie
        ');
            $query->bindValue(':sie', $sie);
            $query->bindValue(':espec', $idesp);
            $query->execute();
            $nivel= $query->fetchAll();

            $nivelArray = array();
            foreach ($nivel as $value) {

                $nivelArray[$value['acreditacionid']] =$value['acreditacion'];
            }
            $subareaArray = array();

            /* foreach ($subarea as $value) {
                if (($value->getId() == 5  )||($value->getId() == 6 )) {
                    $subareaArray[$value->getId()] = $value->getSubArea();
                }
            } */
            foreach ($subarea as $value) {
                if (($value->getId() != 0 && $value->getEsActivo() === true)) {
                $subareaArray[$value->getId()] = $value->getSubArea();
                }
            }    


            $programaArray = array();
            foreach ($programa as $value) {
                if (($value->getId() ==1 )||($value->getId() ==2 ))
                {
                    $programaArray[$value->getId()] = $value->getPrograma();
                }
            }

            $areatematicaArray = array();
            foreach ($areatematica as $value) {
                $areatematicaArray[$value->getId()] = $value->getAreatematica();
            }

            $organizacionArray = array();
            foreach ($organizacion as $value) {
                $organizacionArray[$value->getId()] = $value->getOrganizacion();
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
            $sie= $this->session->get('ie_id');
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare('	select distinct sest.id , sest.especialidad
				from institucioneducativa ined
					inner join superior_institucioneducativa_acreditacion sia on ined.id = sia.institucioneducativa_id
		                inner join superior_acreditacion_especialidad sae on sia.acreditacion_especialidad_id =sae.id
								inner join superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id = sest.id
										inner join superior_facultad_area_tipo sfat on sfat.id = sest.superior_facultad_area_tipo_id
											where ined.id=:sie and sfat.id =40
        ');
            $query->bindValue(':sie', $sie);
            $query->execute();
            $espUE= $query->fetchAll();

            $espUEArray = array();
            foreach ($espUE as $value) {

                $espUEArray[$value['id']] =$value['especialidad'];
            }
            $prov = array();
            $muni = array();
            $niv = array();
            // Dibuja el formulario con los datos Seleccionados Anteriormente
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_per_cursos_largos_update'))
                ->add('idCursosCortos', 'hidden', array('data' => $idcurso))
                ->add('idCursosCortosA', 'hidden', array('data' => $idcurso))
                ->add('nivel', 'hidden', array('data' => 231))
                ->add('ciclo', 'hidden', array('data' => 0))
                ->add('grado', 'hidden', array('data' => 99))
                //->add('paralelo','choice',array('label'=>'Paralelo','choices'=>$paralelos,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
               // ->add('paralelo', 'hidden', array('data' => 1))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('sucursal', 'hidden', array('data' => $sucursal))
                ->add('institucion', 'hidden', array('data' => $institucion))
                ->add('periodo', 'hidden', array('data' => $periodo))
                ->add('turno', 'choice', array( 'required' => true, 'choices' => $turnoArray, 'data' => $institucioncurso->getTurnoTipo()->getId() , 'attr' => array('class' => 'form-control', 'data-placeholder' => 'Seleccionar...', 'data-placeholder' => 'Seleccionar...')))
                ->add('horas', 'text', array( 'required' => true, 'data' => $institucioncurso->getDuracionhoras(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control','readonly' => true)))
                ->add('fechaInicio', 'date', array('widget' => 'single_text','format' => 'dd-MM-yyyy','data' => new \DateTime($institucioncurso->getFechaInicio()->format('d-m-Y')), 'required' => false, 'attr' => array('class' => 'form-control calendario')))
                ->add('fechaFin', 'date', array('widget' => 'single_text','format' => 'dd-MM-yyyy','data' => new \DateTime($institucioncurso->getFechaFin()->format('d-m-Y')), 'required' => false, 'attr' => array('class' => 'form-control calendario')))
                ->add('subarea', 'choice', array('required' => true, 'choices' => $subareaArray, 'data' => $institucioncursocorto->getSubareaTipo()->getId() , 'attr' => array('class' => 'form-control', 'data-placeholder' => 'Seleccionar...', 'data-placeholder' => 'Seleccionar...')))
                //->add('programa', 'choice', array('required' => true, 'choices' => $programaArray, 'data' => $institucioncursocorto->getProgramaTipo()->getId() , 'attr' => array('class' => 'form-control', 'data-placeholder' => 'Seleccionar...', 'data-placeholder' => 'Seleccionar...')))
               // ->add('especialidad', 'choice', array( 'required' => true, 'choices' => $espUEArray, 'data' => $idesp,'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control','readonly' => true, 'onchange' => 'listarNiveles(this.value)')))
               // ->add('nivel', 'choice', array( 'required' => true, 'choices' => $nivelArray,'data' => $idnivel , 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control','readonly' => true, 'onchange' => 'mostrarHoras(this.value)')))
                ->add('nivel', 'text', array( 'required' => true, 'data' => $nivEsp, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control','readonly' => true)))
                ->add('especialidad', 'text', array( 'required' => true, 'data' => $espNombre, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control','readonly' => true)))
                ->add('paralelo', 'text', array( 'required' => true, 'data' => $paralelo, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control','readonly' => true)))
                //->add('areatematica', 'choice', array('required' => true, 'choices' => $areatematicaArray, 'data' => $institucioncursocorto->getAreatematicaTipo()->getId() , 'attr' => array('class' => 'chosen-select form-control', 'data-placeholder' => 'Seleccionar...', 'data-placeholder' => 'Seleccionar...')))
                ->add('poblacion', 'choice', array( 'required' => true, 'choices' => $poblacionesArray, 'data' => $institucioncursocorto->getPoblacionTipo()->getId() , 'attr' => array('class' => 'form-control', 'data-placeholder' => 'Seleccionar...')))
                //    ->add('poblacion', 'choice', array( 'required' => true, 'choices' => $poblacionesArray, 'data' => $idpob , 'attr' => array('class' => 'form-control', 'data-placeholder' => 'Seleccionar...')))
                //      ->add('cursosCortos', 'choice', array( 'required' => true, 'choices' => $cursosCortosArray, 'data' => $institucioncursocorto->getCursocortoTipo()->getId() , 'attr' => array('class' => 'chosen-select col-lg-10')))
                //->add('pobdetalle', 'text', array( 'required' => true, 'data' => $pobdetalle,'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('organizacion', 'choice', array( 'required' => true, 'choices' => $organizacionArray,'data' =>  $idorg['organizacion_tipo_id'],'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control','onchange' => 'mostrarPobDetalleCL(this.value)')))
                //->add('pobobs', 'textarea', array( 'required' => false, 'data'=> $pobobservacion->getObs(),'attr' => array('class' => 'form-control','readonly' => true)))
                ->add('departamento', 'choice', array('label' => 'departamento', 'required' => true, 'choices' => $dptoNacArray,'data' => $institucioncursocorto->getLugarTipoDepartamento()->getId(),  'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarProvincias(this.value)')))
                ->add('provincia', 'choice', array('label' => 'Provincia', 'required' => true, 'choices' => $provinciasArray,'data' => $institucioncursocorto->getLugarTipoProvincia()->getId(),  'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarMunicipios(this.value)')))
                ->add('municipio', 'choice', array('label' => 'Municipio', 'required' => true, 'choices' => $municipiosArray, 'data' => $institucioncursocorto->getLugarTipoMunicipio()->getId(),'empty_value' => 'Seleccionar...','attr' => array('class' => 'form-control')))
                ->add('lugar', 'text', array( 'required' => true, 'data' => $lugar, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'disbled' => true)))
                ->getForm();
            return $this->render('SiePermanenteBundle:CursosLargos:edit.html.twig', array(

                'form' => $form->createView()

            ));
        }
        catch (Exception $ex)
        {
        }
    }

    public function updateCursoLargoAction(Request $request){
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
            $institucioncurso ->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(231));
            $institucioncurso ->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find(0));
            $institucioncurso ->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(99));
            //$institucioncurso ->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find(1));
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
            //$institucioncursocorto  ->setProgramaTipo($em->getRepository('SieAppWebBundle:PermanenteProgramaTipo')->findOneBy(array('id' => $form['programa'])));
            $institucioncursocorto  ->setProgramaTipo($em->getRepository('SieAppWebBundle:PermanenteProgramaTipo')->findOneBy(array('id' => 0)));                                      
            //$institucioncursocorto  ->setAreatematicaTipo($em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->findOneBy(array('id' => $form['areatematica'])));
           // $institucioncursocorto  ->setCursocortoTipo($em->getRepository('SieAppWebBundle:PermanenteCursocortoTipo')->findOneBy(array('id' => $form['cursosCortos'])));
            $institucioncursocorto  ->setPoblacionTipo($em->getRepository('SieAppWebBundle:PermanentePoblacionTipo')->findOneBy(array('id' => $form['poblacion'])));
            $institucioncursocorto  ->setLugarTipoDepartamento($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['departamento'])));
            $institucioncursocorto  ->setLugarTipoProvincia($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['provincia'])));
            $institucioncursocorto  ->setLugarTipoMunicipio($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['municipio'])));
            $institucioncursocorto  ->setLugarDetalle($form['lugar']);
          //              $institucioncursocorto  ->setPoblacionDetalle($form['pobdetalle']);
            $em->persist($institucioncursocorto);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            return $this->redirect($this->generateUrl('herramienta_per_cursos_largos_index'));
        }
        catch  (Exception $ex)
        {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_per_cursos_largos_index'));
        }

    }

    public function deleteCursoLargoAction(Request $request){
        //create the DB conexion
        //dump($request);die;
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

        } catch (Exception $ex) {
            return $ex;
        }
    }

///Modificado 2021
    public function showModulosAction(Request $request){
     
        $em = $this->getDoctrine()->getManager();
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);

        $aInfoUeducativaCurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId'];
        $idcurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
        $idacreditacion=$aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['acreditacionid'];
        $idespecialidad=$aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['cursolargoid'];
        $paraleloId=$aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['paraleloId'];
        $sie= $this->session->get('ie_id');
        $gestion=$this->session->get('ie_gestion');  //dump($idcurso);die;
        try{
             /* $query = $em->getConnection()->prepare('
            		select iec.id as idcurso, smp.id as idsmp,smp.horas_modulo as horas, smt.id as idsmt, smt.modulo, ieco.id as idieco, iecom.id as idiecom
                    from institucioneducativa_curso iec
                    inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id = iec.id
                    inner join superior_modulo_periodo smp on smp.id = ieco.superior_modulo_periodo_id
                    inner join superior_modulo_tipo smt on smt.id= smp.superior_modulo_tipo_id
                    left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
                    where iec.id = :idcurso
                    ');
            $query->bindValue(':idcurso', $idcurso);
            $query->execute();
            $listamodcurso= $query->fetchAll();  */          
            //Preguntamos si el centro tiene facilitadires registrados a la especialidad.
            //dump($idacreditacion,$idespecialidad);
             /* dump($idacreditacion);
            dump($idespecialidad);
            dump($sie);die; */

            /* $query = $em->getConnection()->prepare('select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo
                ,ieco.id as idieco, iecom.id as idiecom 
                from superior_acreditacion_especialidad sae
                inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
                inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
                inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
                inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                inner JOIN institucioneducativa_curso_oferta ieco on  smp.id = ieco.superior_modulo_periodo_id
                inner JOIN institucioneducativa_curso_oferta_maestro iecom on ieco.id = iecom.institucioneducativa_curso_oferta_id
                where sat.id =:idacreditacion and sfat.id=40 and sest.id=:idespecialidad and sia.institucioneducativa_id=:sie');
                $query->bindValue(':sie', $sie);
                $query->bindValue(':idacreditacion', $idacreditacion);
                $query->bindValue(':idespecialidad', $idespecialidad);
                $query->execute();
                $facilitadores= $query->fetchAll(); //dump($facilitadores);die;
           
            if($facilitadores){ //dump("tiene faci");die;
                $query = $em->getConnection()->prepare('select  smp.id as idsmp, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip,smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo,
                ieco.id as idieco,iecom.id as idiecom
                from superior_acreditacion_especialidad sae
                inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
                inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
                inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
                inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                inner JOIN institucioneducativa_curso_oferta ieco on  smp.id = ieco.superior_modulo_periodo_id
                inner JOIN institucioneducativa_curso_oferta_maestro iecom on ieco.id = iecom.institucioneducativa_curso_oferta_id
                where sat.id =:idacreditacion and sfat.id=40 and sest.id=:idespecialidad and sia.institucioneducativa_id=:sie
                ORDER BY smp.id');
                $query->bindValue(':sie', $sie);
                $query->bindValue(':idacreditacion', $idacreditacion);
                $query->bindValue(':idespecialidad', $idespecialidad);
                $query->execute();
                $listamodcurso= $query->fetchAll(); dump($listamodcurso);die;
            }else{ //dump($idacreditacion,$idespecialidad);die;
                $query = $em->getConnection()->prepare('select  smp.id as idsmp, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip,smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo,
                ieco.id as idieco,iecom.id as idiecom
                from superior_acreditacion_especialidad sae
                inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
                inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
                inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
                inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                left JOIN institucioneducativa_curso_oferta ieco on  smp.id = ieco.superior_modulo_periodo_id
                left JOIN institucioneducativa_curso_oferta_maestro iecom on ieco.id = iecom.institucioneducativa_curso_oferta_id
                where sat.id =:idacreditacion and sfat.id=40 and sest.id=:idespecialidad and sia.institucioneducativa_id=:sie
                ORDER BY smp.id ');
                $query->bindValue(':sie', $sie);
                $query->bindValue(':idacreditacion', $idacreditacion);
                $query->bindValue(':idespecialidad', $idespecialidad);
                $query->execute();
                $listamodcurso= $query->fetchAll();//dump($listamodcurso); die; 
            }  */
            // dump($idacreditacion);
            // dump($idespecialidad);
            // dump($paraleloId);
            // die;

            $query = $em->getConnection()->prepare('select  smp.id as idsmp, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip,smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo,
                ieco.id as idieco,iecom.id as idiecom
                from superior_acreditacion_especialidad sae
                inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
                inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
                inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
                inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                left JOIN institucioneducativa_curso_oferta ieco on  smp.id = ieco.superior_modulo_periodo_id
                left JOIN institucioneducativa_curso_oferta_maestro iecom on ieco.id = iecom.institucioneducativa_curso_oferta_id
                left JOIN institucioneducativa_curso iec on  ieco.insitucioneducativa_curso_id = iec.id
                where sat.id =:idacreditacion and sfat.id=40 and sest.id=:idespecialidad and sia.institucioneducativa_id=:sie and iec.paralelo_tipo_id=:paraleloId 
                ORDER BY smp.id ');
                $query->bindValue(':sie', $sie);
                $query->bindValue(':idacreditacion', $idacreditacion);
                $query->bindValue(':idespecialidad', $idespecialidad);
                $query->bindValue(':paraleloId', $paraleloId);
                $query->execute();
                $listamodcurso= $query->fetchAll();
            //dump($listamodcurso); die;       
            //Obtiene el listado de facilitadores registrados
            $query = $em->getConnection()->prepare('
            select a.id, a.persona_id, a.institucioneducativa_id,a.gestion_tipo_id,a.es_vigente_administrativo,
            b.id as idformacion, b.formacion,(c.paterno||\' \'||c.materno||\' \'||c.nombre) as nombre, c.carnet
            from maestro_inscripcion a
	        inner join formacion_tipo b on b.id =a.formacion_tipo_id
		    inner join persona c on c.id = a.persona_id
            where a.institucioneducativa_id=:sie
						and a.gestion_tipo_id =:gestion
						and a.es_vigente_administrativo=true
                        and periodo_tipo_id=1
						and a.cargo_tipo_id =0
            ');
            $query->bindValue(':sie',$sie);
            $query->bindValue(':gestion', $gestion);
            $query->execute();  
            $listamaestro= $query->fetchAll(); 
            $listamaestroArray = array();
                foreach ($listamaestro as $value) {
                    $listamaestroArray[$value['id']] =$value['nombre'];
                } 
             //dump($listamaestroArray);die;          
            $form = $this->createFormBuilder()
            ->add('cursoscortos', 'hidden', array('data' => $idcurso))
            ->getForm();
                //dump($listamodcurso,$listamaestroArray,$infoUe);die;
            return $this->render('SiePermanenteBundle:CursosLargos:modulos.html.twig', array(
                'form' => $form->createView(),
                'lstmod'=> $listamodcurso,
                'maestro'=> $listamaestroArray,
                'infoUe'=>$infoUe
            ));
        }catch (Exception $ex) {
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!' . $ex));
        } 
    }
        
    public function areamaestroAction(Request $request) {
       // dump($request);die;
        $ieco = $request->get('idco');
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);

        $sie = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        $em = $this->getDoctrine()->getManager();

        $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($ieco);
        
        $notaT = $em->getRepository('SieAppWebBundle:NotaTipo')->find(0);

        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro');
        $query = $repository->createQueryBuilder('iecom')
                ->where('iecom.institucioneducativaCursoOferta = :curso')
                //->andWhere('iecom.notaTipo = :nota')
                ->setParameter('curso', $cursoOferta)
                //->setParameter('nota', $notaT)
                ->getQuery();

        $maestrosMateria = $query->getResult();
        
        $arrayMaestros = array();

        if ($maestrosMateria) {
            foreach ($maestrosMateria as $mm) {
                $arrayMaestros[] = array(
                    'id' => $mm->getId(),
                    'idmi' => $mm->getMaestroInscripcion()->getId(),
                    'horas' => $mm->getHorasMes(),
                    'idNotaTipo' => 0,
                    'periodo' => $periodo,
                    'idco' => $ieco);
            }
        } else {
            $arrayMaestros[] = array(
                'id' => 'nuevo',
                'idmi' => '',
                'horas' => '',
                'idNotaTipo' => 0,
                'periodo' => $periodo,
                'idco' => $ieco);
        }

        $query = $em->createQuery(
                        'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.rolTipo IN (:id) ORDER BY ct.id')
                ->setParameter('id', array(2));
        $cargosArray = $query->getResult();

        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');

        $query = $repository->createQueryBuilder('mi')
                ->select('mi')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
                ->innerJoin('SieAppWebBundle:FormacionTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'isuc', 'WITH', 'mi.institucioneducativaSucursal = isuc.id')
                ->innerJoin('SieAppWebBundle:CargoTipo', 'ct', 'with', 'mi.cargoTipo = ct.id')
                ->innerJoin('SieAppWebBundle:RolTipo', 'rt', 'with', 'ct.rolTipo = rt.id')
                ->where('mi.institucioneducativa = :idInstitucion')
                ->andWhere('mi.gestionTipo = :gestion')
                ->andWhere('mi.cargoTipo IN (:cargos)')
                ->andWhere('mi.periodoTipo = :periodo')
                ->andWhere('mi.institucioneducativaSucursal = :sucursal')
                ->andWhere('mi.esVigenteAdministrativo = :vigente')
                ->setParameter('idInstitucion', $sie)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargos', $cargosArray)
                ->setParameter('periodo', $periodo)
                ->setParameter('sucursal', $sucursal)
                ->setParameter('vigente', 't')
                ->orderBy('p.paterno')
                ->addOrderBy('p.materno')
                ->addOrderBy('p.nombre')
                ->getQuery();

        $maestros = $query->getResult();

        return $this->render($this->session->get('pathSystem') . ':CursosLargos:maestros.html.twig', array(
                    'maestrosCursoOferta' => $arrayMaestros,
                    'maestros' => $maestros,
                    'ieco' => $ieco,
                    'operativo' => $periodo)
        );
    }
     public function maestrosAsignarAction(Request $request){
        //dump($request);die;
        $iecom = $request->get('iecom');
        $ieco = $request->get('ieco');
        $idmi = $request->get('idmi');
        $idnt = $request->get('idnt');
        // $horas = $request->get('horas');
        
        $em = $this->getDoctrine()->getManager();
        // $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta_maestro');")->execute();
        for($i=0;$i<count($iecom);$i++){
            $horasNum = 0;
            if($iecom[$i] == 'nuevo' and $idmi[$i] != ''){
                $newCOM = new InstitucioneducativaCursoOfertaMaestro();
                $newCOM->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($ieco[$i]));
                $newCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idmi[$i]));
                $newCOM->setHorasMes($horasNum);
                $newCOM->setFechaRegistro(new \DateTime('now'));
                $newCOM->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idnt[$i]));
                $newCOM->setEsVigenteMaestro('t');
                $em->persist($newCOM);
                $em->flush();
            }else{
                if($idmi[$i] != ''){
                    $updateCOM = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->find($iecom[$i]);
                    $updateCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idmi[$i]));
                    $updateCOM->setHorasMes($horasNum);
                    $updateCOM->setFechaModificacion(new \DateTime('now'));
                    $updateCOM->setEsVigenteMaestro('t');
                    $em->flush();
                }
            }
        }
        $response = new JsonResponse();
        return $response->setData(array('ieco'=>$ieco[0]));
    }

    public function addModulosAction (request $request){


        $form = $request->get('form');
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $aInfoUeducativaCurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId'];
        $idcurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
        $date = date('Y/m/d' );
        $date2 = date("Y-m-d",strtotime($date));
        //$sie= $this->session->get('ie_id');
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //dump($form);die;
        if (isset($form['recsabe'])){

            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();
            $institucioncursoferta = new InstitucioneducativaCursoOferta();
            $institucioncursoferta ->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('id' => $idcurso)));
            $institucioncursoferta ->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find(2));
            $institucioncursoferta->setSuperiorModuloPeriodo($em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->findOneBy(array('id' =>$form['modulos'])));
            $em->persist($institucioncursoferta);
            $em->flush($institucioncursoferta);

            $em->getConnection()->commit();
        }else{

            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();
            $institucioncursoferta = new InstitucioneducativaCursoOferta();
            $institucioncursoferta ->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('id' => $idcurso)));
            //$institucioncursoferta ->setHorasmes($horas);
            $institucioncursoferta ->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find(2));
            $institucioncursoferta->setSuperiorModuloPeriodo($em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->findOneBy(array('id' =>$form['modulos'])));
            $em->persist($institucioncursoferta);
            $em->flush($institucioncursoferta);
           // $em->getConnection()->commit();
          //  dump($institucioncursoferta);
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta_maestro');")->execute();
            $institucioncursofermaestro = new InstitucioneducativaCursoOfertaMaestro();
            $institucioncursofermaestro  ->setInstitucioneducativaCursoOferta($institucioncursoferta);
         //   $institucioncursofermaestro  ->setHorasMes($horas);
            $institucioncursofermaestro  ->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('id' => $form['maestros'])));

            $em->persist($institucioncursofermaestro);
        //    dump($institucioncursofermaestro);die;
            $em->flush();

            $em->getConnection()->commit();
        }
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $sie= $this->session->get('ie_id');
        $gestion=$this->session->get('ie_gestion');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('
                select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
                from superior_acreditacion_especialidad sae
                        inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
                            inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
                                inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
                                    inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                                            inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                                                inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                                                    inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                                    where sat.id =:acred and sfat.id=40 and sest.id=:esp  and smp.id not in (
					select smp.id as idsmp
					from institucioneducativa_curso iec
							inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
									inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
											inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
														left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
																left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
																		left join persona per on per.id = mi.persona_id
																		where iec.id=:idcurso)
        ');
        $query->bindValue(':acred', $aInfoUeducativaCurso['acreditacionid']);
        $query->bindValue(':esp', $aInfoUeducativaCurso ['cursolargoid']);
        $query->bindValue(':idcurso',$idcurso);
        $query->execute();
        $listamodulos= $query->fetchAll();
        //dump($listamodulos);die;

        $listamodulosArray = array();
        foreach ($listamodulos as $value) {
            $listamodulosArray[$value['idsmp']] =$value['modulo'];
        }
        // dump($listamodulosArray);die;
        //    dump($listamodulos);
        $query = $em->getConnection()->prepare('
            select a.id as idmaesins , a.persona_id, a.institucioneducativa_id,a.gestion_tipo_id,a.es_vigente_administrativo,
            b.id as idformacion, b.formacion,(c.paterno||\' \'||c.materno||\' \'||c.nombre) as nombre, c.carnet
            from maestro_inscripcion a
	        inner join formacion_tipo b on b.id =a.formacion_tipo_id
		    inner join persona c on c.id = a.persona_id
            where a.institucioneducativa_id=:sie
						and a.gestion_tipo_id =:gestion
						and a.es_vigente_administrativo=true
                        and periodo_tipo_id=1
						and a.cargo_tipo_id =0
        ');
        $query->bindValue(':sie',$sie);
        $query->bindValue(':gestion', $gestion);
        $query->execute();
        $listamaestro= $query->fetchAll();
        $listamaestroArray = array();
        foreach ($listamaestro as $value) {
            $listamaestroArray[$value['idmaesins']] =$value['nombre'];
        }
        $query = $em->getConnection()->prepare('
            		select ieco.id as idieco, smp.id as idsmp, smp.horas_modulo, smt.id as idsmt, smt.modulo,iecom.id as idiecom,mi.id as idmi, per.id as idper,
            		(per.paterno||\' \'||per.materno||\' \'||per.nombre) as nombre
            		--per.nombre, per.paterno,per.materno
					from institucioneducativa_curso iec
							inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
									inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
											inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
														left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
																left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
																		left join persona per on per.id = mi.persona_id
											where iec.id=:idcurso
											order by iecom.id desc
        ');
        $query->bindValue(':idcurso',$idcurso);
        $query->execute();
        $listamodcurso= $query->fetchAll();

        $form = $this->createFormBuilder()
            // ->setAction($this->generateUrl('herramienta_per_asignar_modulos'))
            ->add('modulos', 'choice', array('label' => 'Modulos', 'required' => true, 'choices' => $listamodulosArray,  'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('maestros', 'choice', array('label' => 'Facilitadores', 'required' => true, 'choices' => $listamaestroArray, 'empty_value' => 'Seleccionar...','attr' => array('class' => 'form-control')))
            ->add('recsabe', 'checkbox', array('label' => 'Rec. de Saberes','required'=>false))
            ->add('guardar', 'button', array('label' => 'añadir', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true, 'onclick'=>'addModulo();')))
            ->getForm();


        return $this->render('SiePermanenteBundle:CursosLargos:listamodulos.html.twig', array(

            'form' => $form->createView(),
            'lstmod'=> $listamodcurso,
            'infoUe'=>$infoUe
        ));

    }

    public function deleteModuloCLAction (request $request){

        $em = $this->getDoctrine()->getManager();
        $ieco= $request->get('ieco');
        $infoUe = $request->get('infoUe');
       // dump($ieco);die;
        $aInfoUeducativa = unserialize($infoUe);

        $aInfoUeducativaCurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId'];
        $idcurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
            //        dump($aInfoUeducativa);dump($aInfoUeducativaCurso);
        $gestion = $this->session->get('ie_gestion');

        $iecofermaes = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findOneBy(array('institucioneducativaCursoOferta'=>$ieco));
        //$iecofer = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('id'=>$ieco));->findOneBy(array('institucioneducativaCursoOferta'=>$ieco));
            //    dump($iecofermaes);die;


        try {

            $em->getConnection()->beginTransaction();
            if($iecofermaes)
            {
              //   $iecofermaestro = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->find($iecofermaes['id']);
                $em->remove($iecofermaes);
                $em->flush();
            }
            //            $em->getConnection()->beginTransaction();
            $iecofer = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($ieco);
            $em->remove($iecofer);
            $em->flush();
            $em->getConnection()->commit();

            $this->session = $request->getSession();
            $id_usuario = $this->session->get('userId');
            $sie= $this->session->get('ie_id');
            $gestion=$this->session->get('ie_gestion');
            //validation if the user is logged
            if (!isset($id_usuario)) {
                return $this->redirect($this->generateUrl('login'));
            }
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare('
                select sae.id as idsae, sest.id as idespecialidad,sest.especialidad,sat.id as idacreditacion, sat.acreditacion, sia.id as idsia, sip.id as idsip, smp.id as idsmp, smp.horas_modulo as horas, smt.id as idmodulo,smt.modulo 
                from superior_acreditacion_especialidad sae
                        inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
                            inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
                                inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
                                    inner join superior_institucioneducativa_acreditacion sia on sae.id  =sia.acreditacion_especialidad_id
                                            inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
                                                inner join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
                                                    inner join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
                                    where sat.id =:acred and sfat.id=40 and sest.id=:esp  and smp.id not in (
					select smp.id as idsmp
					from institucioneducativa_curso iec
							inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
									inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
											inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
														left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
																left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
																		left join persona per on per.id = mi.persona_id
																		where iec.id=:idcurso)
        ');
            $query->bindValue(':acred', $aInfoUeducativaCurso['acreditacionid']);
            $query->bindValue(':esp', $aInfoUeducativaCurso ['cursolargoid']);
            $query->bindValue(':idcurso',$idcurso);
            $query->execute();
            $listamodulos= $query->fetchAll();
            //dump($listamodulos);die;

            $listamodulosArray = array();
            foreach ($listamodulos as $value) {
                $listamodulosArray[$value['idsmp']] =$value['modulo'];
            }

            $query = $em->getConnection()->prepare('
                select a.id as idmaesins , a.persona_id, a.institucioneducativa_id,a.gestion_tipo_id,a.es_vigente_administrativo,
                b.id as idformacion, b.formacion,(c.paterno||\' \'||c.materno||\' \'||c.nombre) as nombre, c.carnet
                from maestro_inscripcion a
                inner join formacion_tipo b on b.id =a.formacion_tipo_id
                inner join persona c on c.id = a.persona_id
                where a.institucioneducativa_id=:sie
                    and a.gestion_tipo_id =:gestion
                    and a.es_vigente_administrativo=true
                    and periodo_tipo_id=1
                    and a.cargo_tipo_id =0
            ');
            $query->bindValue(':sie',$sie);
            $query->bindValue(':gestion', $gestion);
            $query->execute();
            $listamaestro= $query->fetchAll();
            $listamaestroArray = array();
            foreach ($listamaestro as $value) {
                $listamaestroArray[$value['idmaesins']] =$value['nombre'];
            }
            //dump($listamaestroArray);die;

            $query = $em->getConnection()->prepare('
            		select ieco.id as idieco, smp.id as idsmp, smp.horas_modulo, smt.id as idsmt, smt.modulo,iecom.id as idiecom,mi.id as idmi, per.id as idper,
            		(per.paterno||\' \'||per.materno||\' \'||per.nombre) as nombre
            		--per.nombre, per.paterno,per.materno
					from institucioneducativa_curso iec
							inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
									inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
											inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
														left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
																left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
																		left join persona per on per.id = mi.persona_id
											where iec.id=:idcurso
											order by iecom.id desc
            ');
            $query->bindValue(':idcurso',$idcurso);
            $query->execute();
            $listamodcurso= $query->fetchAll();
            // dump($listamodcurso);die;
            $form = $this->createFormBuilder()
                // ->setAction($this->generateUrl('herramienta_per_asignar_modulos'))
                ->add('modulos', 'choice', array('label' => 'Modulos', 'required' => true, 'choices' => $listamodulosArray,  'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('maestros', 'choice', array('label' => 'Facilitadores', 'required' => true, 'choices' => $listamaestroArray, 'empty_value' => 'Seleccionar...','attr' => array('class' => 'form-control')))
                ->add('recsabe', 'checkbox', array('label' => 'Rec. de Saberes','required'=>false))
                ->add('guardar', 'button', array('label' => 'añadir', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true, 'onclick'=>'addModulo();')))
                ->getForm();
            return $this->render('SiePermanenteBundle:CursosLargos:listamodulos.html.twig', array(

                'form' => $form->createView(),
                'lstmod'=> $listamodcurso,
                'infoUe'=>$infoUe
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            //            return $this->render('SieHerramientaBundle:InfoEstudianteAreas:index.html.twig',$data);
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!' . $ex));
        }


    }

    public function deleteModuloMaestroAction (request $request){
        
        $em = $this->getDoctrine()->getManager();
        $infoUe = $request->get('infoUe');
        $ieco = $request->get('idco');
        //dump($ieco);die;
        $aInfoUeducativa = unserialize($infoUe);
        $aInfoUeducativaCurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId'];
        $idcurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
        $sie= $this->session->get('ie_id');
        $gestion=$this->session->get('ie_gestion');
        try{
        $em->getConnection()->beginTransaction();
        $iecofermaes = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findOneBy(array('institucioneducativaCursoOferta'=>$ieco));
        $em->remove($iecofermaes);
        $em->flush();
        

            $query = $em->getConnection()->prepare('
            		select iec.id as idcurso, smp.id as idsmp,smp.horas_modulo as horas, smt.id as idsmt, smt.modulo, ieco.id as idieco, iecom.id as idiecom , iecom.id as idiecom
                    from institucioneducativa_curso iec
                    inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id = iec.id
                    inner join superior_modulo_periodo smp on smp.id = ieco.superior_modulo_periodo_id
                    inner join superior_modulo_tipo smt on smt.id= smp.superior_modulo_tipo_id
                    left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
                    where iec.id = :idcurso
                    ');
             $query->bindValue(':idcurso', $idcurso);
            $query->execute();
            $listamodcurso= $query->fetchAll();

            $query = $em->getConnection()->prepare('
            select a.id, a.persona_id, a.institucioneducativa_id,a.gestion_tipo_id,a.es_vigente_administrativo,
            b.id as idformacion, b.formacion,(c.paterno||\' \'||c.materno||\' \'||c.nombre) as nombre, c.carnet
            from maestro_inscripcion a
	        inner join formacion_tipo b on b.id =a.formacion_tipo_id
		    inner join persona c on c.id = a.persona_id
            where a.institucioneducativa_id=:sie
						and a.gestion_tipo_id =:gestion
						and a.es_vigente_administrativo=true
                        and periodo_tipo_id=1
						and a.cargo_tipo_id =0
            ');
            $query->bindValue(':sie',$sie);
            $query->bindValue(':gestion', $gestion);
            $query->execute();  
            $listamaestro= $query->fetchAll();
            $listamaestroArray = array();
                foreach ($listamaestro as $value) {
                    $listamaestroArray[$value['id']] =$value['nombre'];
                }
            // dump($listamaestroArray);die;
            $form = $this->createFormBuilder()
            ->add('cursoscortos', 'hidden', array('data' => $idcurso))
            ->getForm();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            return $this->render('SiePermanenteBundle:CursosLargos:modulos.html.twig', array(
                'form' => $form->createView(),
                'lstmod'=> $listamodcurso,
                'maestro'=> $listamaestroArray,
                'infoUe'=>$infoUe
            ));
        }catch (Exception $ex) {
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!' . $ex));
        } 
    }

    public function asignarModulosAction(Request $request){
       // dump($request);die;
        ///Recibe los datos del formulario editar
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
            $institucioncurso ->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(231));
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
            //$institucioncursocorto  ->setAreatematicaTipo($em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->findOneBy(array('id' => $form['areatematica'])));
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

    public function addModulosEstudianteAction (request $request){
       // dump($request);die;

        $em = $this->getDoctrine()->getManager();

        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');

        $aInfoUeducativa = array();//unserialize($infoUe);
        $aInfoStudent = json_decode($infoStudent, TRUE);
        $aInfoUeducativa = $this->getCourseInfo($infoUe);
        $aInfoUeducativaCurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId'];
        $idcurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
        // dump($aInfoUeducativa);die;
        $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid']);
        $ieducativaId = $iec->getInstitucioneducativa()->getId();
        $gestion = $iec->getGestionTipo()->getId();

        $idInscripcion = $aInfoStudent['eInsId'];
        //dump($idInscripcion);die;
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $idestudiante= $inscripcion->getEstudiante()->getId();

        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' =>$idestudiante ));


        $query = $em->getConnection()->prepare('
            		select ieco.id as idieco, smp.id as idsmp, smp.horas_modulo, smt.id as idsmt, smt.modulo,iecom.id as idiecom,mi.id as idmi, per.id as idper, (per.paterno||\' \'||per.materno||\' \'||per.nombre) as nombre, x.esaid,x.esinsid,x.esacursoid, x.esnotaid,x.esnota
				--	per.nombre, per.paterno,per.materno
					from institucioneducativa_curso iec
							inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
									inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
											inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
														left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
																left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
																		left join persona per on per.id = mi.persona_id
															left join (select iecoo.id as iecoid,esa.id as esaid, esins.id esinsid, iecoo.insitucioneducativa_curso_id as esacursoid, esnot.id as esnotaid, esnot.nota_cuantitativa as esnota
																			from estudiante_asignatura esa 
																				left join estudiante_inscripcion esins on esa.estudiante_inscripcion_id = esins.id
																					left join institucioneducativa_curso_oferta iecoo on esa.institucioneducativa_curso_oferta_id =iecoo.id
																								left join estudiante_nota esnot on esnot.estudiante_asignatura_id =esa.id
																			--	where esa.institucioneducativa_curso_oferta_id=54812055
																	where esa.estudiante_inscripcion_id=:idei
																			) x on x.iecoid =ieco.id
											where iec.id=:idcurso
											order by iecom.id desc
        ');
        $query->bindValue(':idei',$idInscripcion);
        $query->bindValue(':idcurso',$idcurso);
        $query->execute();
        $listamodcurso= $query->fetchAll();

         //dump($listamodcurso);die;
        return $this->render('SiePermanenteBundle:CursosLargos:modulosEstudiante.html.twig', array(

        //            'form' => $form->createView(),
            'idinscripcion'=>$idInscripcion,
            'lstmod'=> $listamodcurso,
            'infoUe'=>$infoUe,
            'curso'=>$aInfoUeducativa['ueducativaInfo'],
            'estudiante'=>$estudiante,
            'idcurso'=>$idcurso

        ));



    }

    public function insModEstAction (request $request){ 

        $date = date('Y/m/d' );
        $date2 = date("Y-m-d",strtotime($date));
        //   dump($date);dump($date2);die;
        $idieco = $request->get('idieco');
        $idestins = $request->get('idestins');
        $idcurso = $request->get('idcurso');
        $gestion = $this->session->get('ie_gestion');
        //   dump($idieco);die;
        $em = $this->getDoctrine()->getManager();


        $em->getConnection()->beginTransaction();
        try {
            $estasignatura = new  EstudianteAsignatura();
            $estasignatura ->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
            $estasignatura ->setFechaRegistro(new \DateTime($date2));
            $estasignatura ->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $idestins)));
            $estasignatura ->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findOneBy(array('id' => $idieco)));
            $estasignatura ->setEstudianteasignaturaEstado($em->getRepository('SieAppWebBundle:EstudianteasignaturaEstado')->find(4));
            $em->persist($estasignatura);
            $em->flush($estasignatura);
            //DUMP($estasignatura);DIE;



            $em->getConnection()->commit();
            $query = $em->getConnection()->prepare('
            	select ieco.id as idieco, smp.id as idsmp, smp.horas_modulo, smt.id as idsmt, smt.modulo,iecom.id as idiecom,mi.id as idmi, per.id as idper, (per.paterno||\' \'||per.materno||\' \'||per.nombre) as nombre, x.esaid,x.esinsid,x.esacursoid, x.esnotaid,x.esnota
				--	per.nombre, per.paterno,per.materno
					from institucioneducativa_curso iec
							inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
									inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
											inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
														left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
																left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
																		left join persona per on per.id = mi.persona_id
															left join (select iecoo.id as iecoid,esa.id as esaid, esins.id esinsid, iecoo.insitucioneducativa_curso_id as esacursoid, esnot.id as esnotaid, esnot.nota_cuantitativa as esnota
																			from estudiante_asignatura esa 
																				left join estudiante_inscripcion esins on esa.estudiante_inscripcion_id = esins.id
																					left join institucioneducativa_curso_oferta iecoo on esa.institucioneducativa_curso_oferta_id =iecoo.id
																								left join estudiante_nota esnot on esnot.estudiante_asignatura_id =esa.id
																			--	where esa.institucioneducativa_curso_oferta_id=54812055
																	where esa.estudiante_inscripcion_id=:idei
																			) x on x.iecoid =ieco.id
											where iec.id=:idcurso
											order by iecom.id desc
        ');
            $query->bindValue(':idei',$idestins);
            $query->bindValue(':idcurso',$idcurso);
            $query->execute();
            $listamodcurso= $query->fetchAll();
           // $this->get('session')->getFlashBag()
            //->add('newOk', 'Los datos fueron actualizados correctamente.');
            return $this->render('SiePermanenteBundle:CursosLargos:listaModEstudiante.html.twig', array(

            //            'form' => $form->createView(),
                'idinscripcion'=>$idestins,
                'lstmod'=> $listamodcurso,
             //   'infoUe'=>$infoUe,
            //    'estudiante'=>$estudiante,
                'idcurso'=>$idcurso

            ));
        }
        catch  (Exception $ex)
        {
            $em->getConnection()->rollback();
          //  $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_per_cursos_cortos_index'));
        }
        $query = $em->getConnection()->prepare('
          select ieco.id as idieco, smp.id as idsmp, smp.horas_modulo, smt.id as idsmt, smt.modulo,iecom.id as idiecom,mi.id as idmi, per.id as idper, (per.paterno||\' \'||per.materno||\' \'||per.nombre) as nombre, x.esaid,x.esinsid,x.esacursoid, x.esnotaid,x.esnota
				--	per.nombre, per.paterno,per.materno
					from institucioneducativa_curso iec
							inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
									inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
											inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
														left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
																left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
																		left join persona per on per.id = mi.persona_id
															left join (select iecoo.id as iecoid,esa.id as esaid, esins.id esinsid, iecoo.insitucioneducativa_curso_id as esacursoid, esnot.id as esnotaid, esnot.nota_cuantitativa as esnota
																			from estudiante_asignatura esa 
																				left join estudiante_inscripcion esins on esa.estudiante_inscripcion_id = esins.id
																					left join institucioneducativa_curso_oferta iecoo on esa.institucioneducativa_curso_oferta_id =iecoo.id
																								left join estudiante_nota esnot on esnot.estudiante_asignatura_id =esa.id
																			--	where esa.institucioneducativa_curso_oferta_id=54812055
																	where esa.estudiante_inscripcion_id=:idei
																			) x on x.iecoid =ieco.id
											where iec.id=:idcurso
											order by iecom.id desc
        ');
        $query->bindValue(':idcurso',$idcurso);
        $query->execute();
        $listamodcurso= $query->fetchAll();

        //  dump($iec);dump($idInscripcion);dump($inscripcion);dump($estudiante);dump($listamodcurso);die;
        $form = $this->createFormBuilder()
            // ->setAction($this->generateUrl('herramienta_per_asignar_modulos'))
            //   ->add('modulos', 'choice', array('label' => 'Modulos', 'required' => true, 'choices' => $listamodulosArray,  'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('cursoscortos', 'hidden', array('data' => $idcurso))
            // ->add('recsabe', 'checkbox', array('label' => 'Rec. de Saberes','required'=>false))
            //   ->add('guardar', 'button', array('label' => 'añadir', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true, 'onclick'=>'addModulo();')))
            ->getForm();
        return $this->render('SiePermanenteBundle:CursosLargos:modulosEstudiante.html.twig', array(

            'form' => $form->createView(),
            'idinscripcion'=>$idInscripcion,
            'lstmod'=> $listamodcurso,
            'infoUe'=>$infoUe,
            'estudiante'=>$estudiante
        ));



    }

    public function delModEstAction (request $request){
       // dump($request);die;
        $date = date('Y/m/d' );
        $date2 = date("Y-m-d",strtotime($date));
        //   dump($date);dump($date2);die;
        $idieco = $request->get('idieco');
        $idestins = $request->get('idestins');
        $idcurso = $request->get('idcurso');

        $gestion = $this->session->get('ie_gestion');
        //   dump($idieco);die;
        $em = $this->getDoctrine()->getManager();


        $em->getConnection()->beginTransaction();
        try {
            $em->getConnection()->commit();
            $query = $em->getConnection()->prepare('
            	select ea.id as idesa, en.id as idnota
                from estudiante_asignatura ea 
		        inner join estudiante_inscripcion ei on ei.id=ea.estudiante_inscripcion_id
				inner join institucioneducativa_curso_oferta ieco on ea.institucioneducativa_curso_oferta_id = ieco.id
						left join estudiante_nota en on en.estudiante_asignatura_id = ea.id
				where ei.id=:idei and  ea.institucioneducativa_curso_oferta_id =:idieco
            ');
            $query->bindValue(':idei',$idestins);
            $query->bindValue(':idieco',$idieco);
            $query->execute();
            $borrar= $query->fetch();
         //   dump($borrar);die;
            $em->getConnection()->beginTransaction();
            if($borrar['idnota'] == null)
            {
                $esasig = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($borrar['idesa']);
                $em->remove($esasig);
                $em->flush();
                $em->getConnection()->commit();
            }else{
                $esnota=$em->getRepository('SieAppWebBundle:EstudianteNota')->find($borrar['idnota']);
                $esasig = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($borrar['idesa']);

                $em->remove($esnota);
                $em->remove($esasig);

                $em->flush();
                $em->getConnection()->commit();
            }

            $query = $em->getConnection()->prepare('
            	select ieco.id as idieco, smp.id as idsmp, smp.horas_modulo, smt.id as idsmt, smt.modulo,iecom.id as idiecom,mi.id as idmi, per.id as idper, (per.paterno||\' \'||per.materno||\' \'||per.nombre) as nombre, x.esaid,x.esinsid,x.esacursoid, x.esnotaid,x.esnota
				--	per.nombre, per.paterno,per.materno
					from institucioneducativa_curso iec
							inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
									inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
											inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
														left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
																left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
																		left join persona per on per.id = mi.persona_id
															left join (select iecoo.id as iecoid,esa.id as esaid, esins.id esinsid, iecoo.insitucioneducativa_curso_id as esacursoid, esnot.id as esnotaid, esnot.nota_cuantitativa as esnota
																			from estudiante_asignatura esa 
																				left join estudiante_inscripcion esins on esa.estudiante_inscripcion_id = esins.id
																					left join institucioneducativa_curso_oferta iecoo on esa.institucioneducativa_curso_oferta_id =iecoo.id
																								left join estudiante_nota esnot on esnot.estudiante_asignatura_id =esa.id
																			--	where esa.institucioneducativa_curso_oferta_id=54812055
																	where esa.estudiante_inscripcion_id=:idei
																			) x on x.iecoid =ieco.id
											where iec.id=:idcurso
											order by iecom.id desc
        ');
            $query->bindValue(':idei',$idestins);
            $query->bindValue(':idcurso',$idcurso);
            $query->execute();
            $listamodcurso= $query->fetchAll();
            // $this->get('session')->getFlashBag()
            //->add('newOk', 'Los datos fueron actualizados correctamente.');
            return $this->render('SiePermanenteBundle:CursosLargos:listaModEstudiante.html.twig', array(

            //            'form' => $form->createView(),
                'idinscripcion'=>$idestins,
                'lstmod'=> $listamodcurso,
                //   'infoUe'=>$infoUe,
                //    'estudiante'=>$estudiante,
                'idcurso'=>$idcurso

            ));
        }
        catch  (Exception $ex)
        {
            $em->getConnection()->rollback();
            //  $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_per_cursos_cortos_index'));
        }
        $query = $em->getConnection()->prepare('
            		select ieco.id as idieco, smp.id as idsmp, smp.horas_modulo, smt.id as idsmt, smt.modulo,iecom.id as idiecom,mi.id as idmi, per.id as idper,
            		(per.paterno||\' \'||per.materno||\' \'||per.nombre) as nombre, esa.id as esaid, esins.id as esins
            		--per.nombre, per.paterno,per.materno
					from institucioneducativa_curso iec
							inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
									inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
											inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
														left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
																left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
																	left join persona per on per.id = mi.persona_id
																		left join estudiante_asignatura esa on esa.institucioneducativa_curso_oferta_id = ieco.id
																				left join estudiante_inscripcion esins on esa.estudiante_inscripcion_id = esins.id
											where iec.id=:idcurso
											order by iecom.id desc
        ');
        $query->bindValue(':idcurso',$idcurso);
        $query->execute();
        $listamodcurso= $query->fetchAll();

        //  dump($iec);dump($idInscripcion);dump($inscripcion);dump($estudiante);dump($listamodcurso);die;
        $form = $this->createFormBuilder()
            // ->setAction($this->generateUrl('herramienta_per_asignar_modulos'))
            //   ->add('modulos', 'choice', array('label' => 'Modulos', 'required' => true, 'choices' => $listamodulosArray,  'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('cursoscortos', 'hidden', array('data' => $idcurso))

            ->getForm();
        return $this->render('SiePermanenteBundle:CursosLargos:modulosEstudiante.html.twig', array(

            'form' => $form->createView(),
            'idinscripcion'=>$idInscripcion,
            'lstmod'=> $listamodcurso,
            'infoUe'=>$infoUe,
            'estudiante'=>$estudiante
        ));



    }

    public function showMaestroCursoLargoAction(Request $request){
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

    public function addMaestroCursoLargoAction(Request $request){

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
                //->setAction($this->generateUrl('herramienta_per_cursos_cortos_add_maestro'))
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
        }
        catch  (Exception $ex)
        {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->render('SiePermanenteBundle:CursosCortos:showMaestro.html.twig');
        }
    }

    public function deleteMaestroCursoLargoAction(Request $request){
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
                //->setAction($this->generateUrl('herramienta_per_cursos_cortos_add_maestro'))
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
            //return $this->render('SieHerramientaBundle:InfoEstudianteAreas:index.html.twig',$data);
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!' . $ex));
        }
    }

    public function showEstudianteCursoLargoAction(Request $request){
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

    public function mostrarPobDetalleCLAction($pob) {
        try {

            $em = $this->getDoctrine()->getManager();

            $query = $em->getConnection()->prepare('select * from permanente_poblacion_tipo
                            where organizacion_tipo_id=:pobla
            ');
            $query->bindValue(':pobla', $pob);
            $query->execute();
            $poblaciones= $query->fetchAll();

          //dump($poblaciones);die;
            $poblacionesArray = array();
            foreach ($poblaciones as $value) {
                $poblacionesArray[$value['id']] =$value['poblacion'];
                //$poblacionesArray[$c->getId()] = $c->get();
            }


            $response = new JsonResponse();
            return $response->setData(array('poblaciones' => $poblacionesArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function mostrarCursolargoAction(Request $request) {
        //
        // dump ($request);die;
        $em = $this->getDoctrine()->getManager();

        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $dataUe=(unserialize($infoUe));

        $exist = true;
        $idcurso=$aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
   
        //  dump ($dataUe);die;
        $objStudents = array();

        $query = $em->getConnection()->prepare('
                    select c.id as idcurso, b.id as idestins,d.id as estadomatriculaid,d.estadomatricula AS estadomatricula, b.estudiante_id as idest, a .codigo_rude as codigorude, a.carnet_identidad as carnet,a.paterno,a.materno,a.nombre,a.fecha_nacimiento as fechanacimiento, e.genero 
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

      

        $query = $em->getConnection()->prepare('
            select a.id as iecid, a.periodo_tipo_id, a.grado_tipo_id, a.gestion_tipo_id, a.nivel_tipo_id, a.turno_tipo_id,tt.turno,a.fecha_inicio,a.fecha_fin,a.duracionhoras,
            b.esabierto, b.lugar_tipo_departamento_id as deptoid,depto.lugar as departamento,  b.lugar_tipo_provincia_id as provid,prov.lugar as provincia,  b.lugar_tipo_municipio_id as munid,mun.lugar as municipio, b.lugar_detalle as comunidad,b.poblacion_detalle,
            c.areatematica, d.poblacion,pot.organizacion,e.programa, f.sub_area, g.cursocorto,
            h.id as codofermaes,h.horasmes, 
            i.maestro_inscripcion_id,
            k.paterno,k.materno,k.nombre,
            m.id as cursolargoid,m.sub_area_tipo_id,m.programa_tipo_id, m.areatematica_tipo_id,m.cursocorto_tipo_id,
            sip.id as superid,
            sia.id as siaid,
            sae.id as saeid,
            sat.acreditacion,
            sespt.especialidad,
            sfat.facultad_area as areaprograma
            FROM
            institucioneducativa_curso a 
            left JOIN permanente_institucioneducativa_cursocorto b on a.id= b.institucioneducativa_curso_id
            left join permanente_area_tematica_tipo c on b.areatematica_tipo_id =c.id
            left join permanente_poblacion_tipo d on b.poblacion_tipo_id = d.id
            left join permanente_organizacion_tipo pot on pot.id = d.organizacion_tipo_id
            left join permanente_programa_tipo e on b.programa_tipo_id=e.id
            left join permanente_sub_area_tipo f on b.sub_area_tipo_id= f.id
            left join permanente_cursocorto_tipo g on cursocorto_tipo_id = g.id
            left join institucioneducativa_curso_oferta h on  a.id = h.insitucioneducativa_curso_id	
            left join institucioneducativa_curso_oferta_maestro i on h.id = i.institucioneducativa_curso_oferta_id
            left join maestro_inscripcion j on i.maestro_inscripcion_id = j.id
            left join persona k on j.persona_id =k.id
            left join permanente_institucioneducativa_cursocorto m on m.institucioneducativa_curso_id = a.id
            inner join superior_institucioneducativa_periodo sip on a.superior_institucioneducativa_periodo_id = sip.id
            inner join lugar_tipo depto on depto.id= b.lugar_tipo_departamento_id  
            inner join lugar_tipo prov on prov.id = b.lugar_tipo_provincia_id
            inner join lugar_tipo mun on mun.id = b.lugar_tipo_municipio_id
            inner join turno_tipo tt on tt.id= a.turno_tipo_id
            inner join superior_periodo_tipo spt on spt.id  = sip.superior_periodo_tipo_id
            inner join superior_institucioneducativa_acreditacion sia on sia.id = sip.superior_institucioneducativa_acreditacion_id
            inner join institucioneducativa ie on ie.id =sia.institucioneducativa_id
            inner join superior_acreditacion_especialidad sae on sae.id = sia.acreditacion_especialidad_id
            inner join superior_acreditacion_tipo sat on sat.id = sae.superior_acreditacion_tipo_id
            inner join superior_especialidad_tipo sespt on sespt.id = sae.superior_especialidad_tipo_id
            inner join superior_facultad_area_tipo sfat on sfat.id = sespt.superior_facultad_area_tipo_id
            where  a.nivel_tipo_id= 231 and a.id=:curso
                ');
        $query->bindValue(':curso', $idcurso);
        $query->execute();
        $cursosLargos= $query->fetchAll();
        //  dump($cursosLargos);die;

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
        return $this->render('SiePermanenteBundle:CursosLargos:seeCursolargo.html.twig', array(
            'objStudents' => $objStudents,
            'objx' => $estadomatriculaArray,
            'form' => $form->createView(),
            'exist' => $exist,
            'cursolargo'=>$cursosLargos,
            'existins' => $existins,
            'infoUe' => $infoUe,
            'dataUe' => $dataUe,
            'totalInscritos'=>count($objStudents)

        ));
    }

    public function mostrarInscripcionAction(Request $request) {
        //
        $em = $this->getDoctrine()->getManager();

        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = array();// unserialize($infoUe);
        $dataUe=array();//(unserialize($infoUe));
       // dump ($aInfoUeducativa);die;
        $exist = true;
        $idcurso= $request->get('infoUe');// $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
          // dump ($idcurso);die;
        $objStudents = array();

        $query = $em->getConnection()->prepare('
                select c.id as idcurso, b.id as idestins,d.id as estadomatriculaid, d.estadomatricula AS estadomatricula, b.estudiante_id as idest, a .codigo_rude as codigorude, a.carnet_identidad as carnet, a.complemento, a.paterno,a.materno,a.nombre,a.fecha_nacimiento as fechanacimiento, e.genero 
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

         //  dump($objStudents);die;

        $query = $em->getConnection()->prepare('
                	
	        select a.id as iecid, a.periodo_tipo_id, a.grado_tipo_id, a.gestion_tipo_id, a.nivel_tipo_id, a.turno_tipo_id,tt.turno,a.fecha_inicio,a.fecha_fin,a.duracionhoras,
                        b.esabierto, b.lugar_tipo_departamento_id as deptoid,depto.lugar as departamento,  b.lugar_tipo_provincia_id as provid,prov.lugar as provincia,  b.lugar_tipo_municipio_id as munid,mun.lugar as municipio, b.lugar_detalle as comunidad,b.poblacion_detalle,
                        c.areatematica, d.poblacion,e.programa, f.sub_area, g.cursocorto,
                        h.id as codofermaes,h.horasmes,
                        i.maestro_inscripcion_id,
                        k.paterno,k.materno,k.nombre,
                        m.id as cursolargoid,m.sub_area_tipo_id,m.programa_tipo_id, m.areatematica_tipo_id,m.cursocorto_tipo_id,
												sip.id as superid,
												sia.id as siaid,
												sae.id as saeid,
												sat.acreditacion,
												sespt.especialidad,
												sfat.facultad_area as areaprograma
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
																														inner join superior_institucioneducativa_periodo sip on a.superior_institucioneducativa_periodo_id = sip.id
																																inner join lugar_tipo depto on depto.id= b.lugar_tipo_departamento_id  
																																	inner join lugar_tipo prov on prov.id = b.lugar_tipo_provincia_id
																																		inner join lugar_tipo mun on mun.id = b.lugar_tipo_municipio_id
																																inner join turno_tipo tt on tt.id= a.turno_tipo_id
																																		inner join superior_periodo_tipo spt on spt.id  = sip.superior_periodo_tipo_id
																																			inner join superior_institucioneducativa_acreditacion sia on sia.id = sip.superior_institucioneducativa_acreditacion_id
																																				inner join institucioneducativa ie on ie.id =sia.institucioneducativa_id
																																					inner join superior_acreditacion_especialidad sae on sae.id = sia.acreditacion_especialidad_id
																																						inner join superior_acreditacion_tipo sat on sat.id = sae.superior_acreditacion_tipo_id
																																							inner join superior_especialidad_tipo sespt on sespt.id = sae.superior_especialidad_tipo_id
																																								inner join superior_facultad_area_tipo sfat on sfat.id = sespt.superior_facultad_area_tipo_id
                    where  a.nivel_tipo_id= 231 and a.id=:curso
	        ');
        $query->bindValue(':curso', $idcurso);
        $query->execute();

        $cursosLargos= $query->fetchAll();
        //  dump($cursosLargos);die;

        if (count($objStudents) > 0){
            $existins = true;
        }
        else {
            $existins = false;
        }
        $estadomatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findAll();
        $estadomatriculaArray = array();

        $query = $em->getConnection()->prepare('
            		select ieco.id as idieco, smp.id as idsmp, smp.horas_modulo, smt.id as idsmt, smt.modulo,iecom.id as idiecom,mi.id as idmi, per.id as idper,
            		(per.paterno||\' \'||per.materno||\' \'||per.nombre) as nombre
            		--per.nombre, per.paterno,per.materno
					from institucioneducativa_curso iec
							inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
									inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
											inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
														left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
																left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
																		left join persona per on per.id = mi.persona_id
											where iec.id=:idcurso
											order by iecom.id desc
        ');
        $query->bindValue(':idcurso',$idcurso);
        $query->execute();
        $listamodcurso= $query->fetchAll();
               // dump($objStudents);die;

        $form = $this->createFormBuilder()
            ->add('matricula', 'choice', array('required' => false, 'choices' => $estadomatriculaArray,  'attr' => array('class' => 'form-control')))
            ->getForm();
        //   dump($dataUe);
        //   dump($objStudents);die;
        // get the infor about the operative
        $swInscription  = $this->getOperativeData($sw=false, $idcurso);
        $swCalification = false;
        if(!$swInscription){
            $swCalification =  $this->getOperativeData(!$swInscription, $idcurso);
        }
        

        
        return $this->render('SiePermanenteBundle:CursosLargos:seeInscritos.html.twig', array(
            'objStudents' => $objStudents,
            'objx' => $estadomatriculaArray,
            'form' => $form->createView(),
            'exist' => $exist,
            'lstmod'=>$listamodcurso,
            'cursolargo'=>$cursosLargos,
            'existins' => $existins,
            'infoUe' => $infoUe,
            'dataUe' => $dataUe,
            'swInscription' => $swInscription,
            'swCalification' => $swCalification,
            'totalInscritos'=>count($objStudents)

        ));
    }
    private function getOperativeData($sw, $idcurso){
        
        $em = $this->getDoctrine()->getManager();

        $institucioncursocorto=$em->getRepository('SieAppWebBundle:PermanenteInstitucioneducativaCursocorto')->findOneBy(array('institucioneducativaCurso'=>$idcurso));

        $today = date('d-m-Y');
        $swOpe = false;  

        if( sizeof($institucioncursocorto)>0 && $institucioncursocorto->getEsabierto()){
            $query = $em->getConnection()->prepare('
                select a.fecha_inicio,a.fecha_fin, sat.acreditacion           
                FROM institucioneducativa_curso a  
                inner join superior_institucioneducativa_periodo sip on a.superior_institucioneducativa_periodo_id = sip.id
                inner join turno_tipo tt on tt.id= a.turno_tipo_id
                inner join superior_periodo_tipo spt on spt.id  = sip.superior_periodo_tipo_id
                inner join superior_institucioneducativa_acreditacion sia on sia.id = sip.superior_institucioneducativa_acreditacion_id
                inner join institucioneducativa ie on ie.id =sia.institucioneducativa_id
                inner join superior_acreditacion_especialidad sae on sae.id = sia.acreditacion_especialidad_id
                inner join superior_acreditacion_tipo sat on sat.id = sae.superior_acreditacion_tipo_id
                inner join superior_especialidad_tipo sespt on sespt.id = sae.superior_especialidad_tipo_id
                inner join superior_facultad_area_tipo sfat on sfat.id = sespt.superior_facultad_area_tipo_id
                where  a.nivel_tipo_id= 231 and a.id=:idcurso
            ');
            $query->bindValue(':idcurso',$idcurso);
            $query->execute();
            $objRequest= $query->fetch();
          
                if(sizeof($objRequest)>0){
                    // get the acreditacion to set months
                    $monthsInscription  = ($objRequest['acreditacion'] == 'TÉCNICO BÁSICO' || $objRequest['acreditacion'] == 'TÉCNICO AUXILIAR')?3:5;
                    $monthsNotas  = ($objRequest['acreditacion'] == 'TÉCNICO BÁSICO' || $objRequest['acreditacion'] == 'TÉCNICO AUXILIAR')?4:6;
                    $f_ini = date('d-m-Y', strtotime($objRequest['fecha_inicio']));
                    if(!$sw){
                        $f_limit = date("d-m-Y", strtotime($f_ini."+".$monthsInscription." month") );
                    }else{
                        $f_ini = date("d-m-Y", strtotime($f_ini."+".$monthsInscription." month") );
                        $f_limit = date("d-m-Y", strtotime($f_ini."+".$monthsNotas." month") );
                    }
                    //compare the limit ini and end operatvie
                    if(strtotime($f_ini)<= strtotime($today)  && strtotime($today) <= strtotime($f_limit)){
                        $swOpe = true;
                    }

                }else{
                    // no data
                }            

        }else{
            $swOpe = false;
        }


        
        return(($swOpe));

    }
    public function seeStudentsAction(Request $request) {
        //
        $em = $this->getDoctrine()->getManager();

        $infoUe = $request->get('infoUe');
       // $aInfoUeducativa = unserialize($infoUe);
     //   $dataUe=(unserialize($infoUe));
        $exist = true;
        $idcurso= $infoUe;
      // dump ($idcurso);die;
        $objStudents = array();

        $query = $em->getConnection()->prepare('
                select c.id as idcurso, b.id as idestins,d.id as estadomatriculaid, d.estadomatricula AS estadomatricula, b.estudiante_id as idest, a .codigo_rude as codigorude, a.carnet_identidad as carnet, a.complemento, a.paterno,a.materno,a.nombre,a.fecha_nacimiento as fechanacimiento, e.genero 

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
       // dump($objStudents);die;
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
        $querya->bindValue(':nivel', 231);
        $querya->bindValue(':idcurso', $idcurso);
        $querya->execute();
        $cursoLargo= $querya->fetchAll();



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
            'cursolargo'=>$cursoLargo,
            'existins' => $existins,
            'infoUe' => $infoUe,
           // 'dataUe' => $dataUe,
            'totalInscritos'=>count($objStudents)

        ));
    }

    public function removeStudentsCLAction(Request $request) {
              // dump($request);die;
        $infoUe =$request->get('infoUe');
        $aInfoUeducativa = array();//unserialize($infoUe);
        $dataUe=array();//(unserialize($infoUe));

        $idinscripcion = $request->get('idestins');
        $idcurso = $request->get('idcurso');
            //   dump($idinscripcion);die;
        $exist = true;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            // dump($idinscripcion);
            //cambiar para el nevio rude
            //to remove all info about RUDE
            $objRude = $em->getRepository('SieAppWebBundle:Rude')->findOneBy(array('estudianteInscripcion' => $idinscripcion ));

            if($objRude){

                $objRudeAbandono = $em->getRepository('SieAppWebBundle:RudeAbandono')->findBy(array('rude' => $objRude->getId() ));

                foreach ($objRudeAbandono as $element) {
                    $em->remove($element);
                }
                $em->flush();


                $objRudeAccesoInternet = $em->getRepository('SieAppWebBundle:RudeAccesoInternet')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeAccesoInternet as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeActividad = $em->getRepository('SieAppWebBundle:RudeActividad')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeActividad as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeCentroSalud = $em->getRepository('SieAppWebBundle:RudeCentroSalud')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeCentroSalud as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeDiscapacidadGrado = $em->getRepository('SieAppWebBundle:RudeDiscapacidadGrado')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeDiscapacidadGrado as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeEducacionDiversa = $em->getRepository('SieAppWebBundle:RudeEducacionDiversa')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeEducacionDiversa as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeIdioma = $em->getRepository('SieAppWebBundle:RudeIdioma')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeIdioma as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeMedioTransporte = $em->getRepository('SieAppWebBundle:RudeMedioTransporte')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeMedioTransporte as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeMediosComunicacion = $em->getRepository('SieAppWebBundle:RudeMediosComunicacion')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeMediosComunicacion as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeRecibioPago = $em->getRepository('SieAppWebBundle:RudeRecibioPago')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeRecibioPago as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeServicioBasico = $em->getRepository('SieAppWebBundle:RudeServicioBasico')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeServicioBasico as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeTurnoTrabajo = $em->getRepository('SieAppWebBundle:RudeTurnoTrabajo')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeTurnoTrabajo as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeMesesTrabajados = $em->getRepository('SieAppWebBundle:RudeMesesTrabajados')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeMesesTrabajados as $element) {
                    $em->remove($element);
                }
                $em->flush();

            }
            if ($objRude) {
                $em->remove($objRude);
                $em->flush();
            }
            $inscripcionestudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idinscripcion);
            $em->remove($inscripcionestudiante);
            $em->flush();           

            $em->getConnection()->commit();

            $query = $em->getConnection()->prepare('
                select c.id as idcurso, b.id as idestins,d.id as estadomatriculaid, d.estadomatricula AS estadomatricula, b.estudiante_id as idest, a .codigo_rude as codigorude, a.carnet_identidad as carnet,a.paterno,a.materno,a.nombre,a.fecha_nacimiento as fechanacimiento, e.genero 

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
            $querya->bindValue(':nivel', 231);
            $querya->bindValue(':idcurso', $idcurso);
            $querya->execute();

            $cursosLargos= $querya->fetchAll();
            //  dump($cursoCorto);die;

            $estadomatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findAll();
            $estadomatriculaArray = array();
            foreach($estadomatricula as $value){
                if( ($value->getId()==3)||($value->getId()==4)||($value->getId()==5))
                {
                    $estadomatriculaArray[$value->getId()] = $value->getEstadomatricula();
                }

            }
            $query = $em->getConnection()->prepare('
            select ieco.id as idieco, smp.id as idsmp, smp.horas_modulo, smt.id as idsmt, smt.modulo,iecom.id as idiecom,mi.id as idmi, per.id as idper,
            		(per.paterno||\' \'||per.materno||\' \'||per.nombre) as nombre
            		--per.nombre, per.paterno,per.materno
					from institucioneducativa_curso iec
							inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
									inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
											inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
														left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
																left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
																		left join persona per on per.id = mi.persona_id
											where iec.id=:idcurso
											order by iecom.id desc
            ');
            $query->bindValue(':idcurso',$idcurso);
            $query->execute();
            $listamodcurso= $query->fetchAll();
            // dump($listamodcurso);die;
            if (count($objStudents) > 0){
                $existins = true;
            }
            else {
                $existins = false;
            }

            //           data = $this->getAreas($infoUe);
            return $this->render('SiePermanenteBundle:CursosLargos:seeInscritosTabla.html.twig', array(
                'objStudents' => $objStudents,
                'exist' => $exist,
                'objx' => $estadomatriculaArray,
                'cursolargo'=>$cursosLargos,
                'lstmod'=>$listamodcurso,
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

    public function closeInscriptionCLAction(Request $request) {


        $infoUe = $request->get('infoUe');

        $aInfoUeducativa = array();//unserialize($infoUe);
       // dump($request);die;
        $dataUe=array();//(unserialize($infoUe));
        $idcurso = $request->get('infoUe');
        $aInfoUeducativa = $this->getCourseInfo($infoUe);
           //dump($aInfoUeducativa);die;
        $acreditacionid = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['acreditacionid'];
        //dump($acreditacionid);die;
        $exist=true;
        try {

            $idcurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
            $em = $this->getDoctrine()->getManager();


            $query = $em->getConnection()->prepare('
                select c.id as idcurso, b.id as idestins,d.id as estadomatriculaid, d.estadomatricula  AS estadomatricula, b.estudiante_id as idest, a .codigo_rude as codigorude, a.carnet_identidad as carnet,a.paterno,a.materno,a.nombre,a.fecha_nacimiento as fechanacimiento, e.genero, a.complemento

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
          //  dump($objStudents);
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
            $querya->bindValue(':nivel', 231);
            $querya->bindValue(':idcurso', $idcurso);
            $querya->execute();

            $cursoLargo= $querya->fetchAll();
             //dump($objStudents);die;


            if (count($objStudents) > 0){
                $existins = true;
            }
            else {
                $existins = false;
            }
            foreach($objStudents as $value){
              //dump($value);die;
                $abandono=false;
                $aprueba = false;
                $no_incorporado = false;
                $apbhoras = false;
                $totalhoras=0;
                    $querya = $em->getConnection()->prepare('
                        select ei.id as idesins,ea.id as idesasig,smp.horas_modulo, en.nota_cuantitativa
                    from estudiante_asignatura ea
                        inner join estudiante_inscripcion ei on ei.id= ea.estudiante_inscripcion_id
                            inner join 	institucioneducativa_curso_oferta ieco on ieco.id = ea.institucioneducativa_curso_oferta_id
                                inner join superior_modulo_periodo smp on smp.id=ieco.superior_modulo_periodo_id
                                	left join estudiante_nota en on en.estudiante_asignatura_id =ea.id
                                    where ei.id =:idei
                    ');

                    $querya->bindValue(':idei', $value['idestins']);
                    $querya->execute();
                    $materias= $querya->fetchAll();
                    foreach($materias as $mat)
                    {
                        if($mat['nota_cuantitativa'] > 50)
                        {
                            $aprueba = true;
                        }else{
                            if ($mat['nota_cuantitativa'] != null || $mat['nota_cuantitativa'] != 0){
                                $abandono=true;
                                
                            }else{
                                $no_incorporado=true;
                            }
                            $aprueba = false;
                        }


                        $totalhoras += $mat['horas_modulo'];
                    }
                        //   dump($totalhoras);
                    if($acreditacionid==1){
                        if($totalhoras>=500){
                            $apbhoras = true;
                        }else{
                            $apbhoras = false;
                        }
                    }elseif($acreditacionid==20)
                    {
                        if($totalhoras>=500){
                            $apbhoras = true;
                        }else{
                            $apbhoras = false;
                        }

                    }elseif($acreditacionid==32)
                    {
                        if($totalhoras>=1000){
                            $apbhoras = true;
                        }else{
                            $apbhoras = false;
                        }
                    }
                        //dump()
                    if($no_incorporado)
                    {
                        $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $value['idestins']));
                        $estudianteInscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(6));
                        //  dump($estudianteInscripcion);die;
                        $em->persist($estudianteInscripcion);
                        $em->flush();
                    }                    
                    if($abandono)
                    {
                        $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $value['idestins']));
                        $estudianteInscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(3));
                        //  dump($estudianteInscripcion);die;
                        $em->persist($estudianteInscripcion);
                        $em->flush();
                    }
                    if($aprueba){
                        if($aprueba && $apbhoras){
                           
                            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $value['idestins']));
                            $estudianteInscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(76));
                            //dump($estudianteInscripcion);die;
                            $em->persist($estudianteInscripcion);
                            $em->flush();
                        }else{
                            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $value['idestins']));
                            $estudianteInscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(22));
                            //dump($estudianteInscripcion);die;
                            $em->persist($estudianteInscripcion);
                            $em->flush();
                        }
                    }
             //       dump($estudianteInscripcion);

            //    dump($objStudents);die;
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
            $em->getConnection()->beginTransaction();
            $institucioncursocorto=$em->getRepository('SieAppWebBundle:PermanenteInstitucioneducativaCursocorto')->findOneBy(array('institucioneducativaCurso'=>$idcurso));
            $institucioncursocorto  ->setEsabierto(false);
            $em->persist($institucioncursocorto);
            $em->flush();
            $em->getConnection()->commit();
        //    dump($objStudents);die;
            $query = $em->getConnection()->prepare('
            	select ieco.id as idieco, smp.id as idsmp, smp.horas_modulo, smt.id as idsmt, smt.modulo,iecom.id as idiecom,mi.id as idmi, per.id as idper,
            	(per.paterno||\' \'||per.materno||\' \'||per.nombre) as nombre
            	--per.nombre, per.paterno,per.materno
					from institucioneducativa_curso iec
							inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
									inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
											inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
														left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
																left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
																		left join persona per on per.id = mi.persona_id
											where iec.id=:idcurso
											order by iecom.id desc
        ');
            $query->bindValue(':idcurso',$idcurso);
            $query->execute();
            $listamodcurso= $query->fetchAll();
            $form = $this->createFormBuilder()
                ->add('matricula', 'choice', array('required' => false, 'choices' => $estadomatriculaArray,  'attr' => array('class' => 'form-control')))
                ->getForm();

            // get the infor about the operative
            $swInscription  = $this->getOperativeData($sw=false, $idcurso);
            $swCalification = false;
            if(!$swInscription){
                $swCalification =  $this->getOperativeData(!$swInscription, $idcurso);
            }                

            return $this->render('SiePermanenteBundle:CursosLargos:seeInscritos.html.twig', array(
                'objStudents' => $objStudents,
                'exist' => $exist,
                'objx' => $estadomatriculaArray,
                'form' => $form->createView(),
                'lstmod'=>$listamodcurso,
                'cursolargo'=>$cursoLargo,
                'existins' => $existins,
                'infoUe' => $infoUe,
                'swInscription' => $swInscription,
                'swCalification' => $swCalification,
                'dataUe' => $dataUe,
                'totalInscritos'=>count($objStudents)
            ));



           //   dump ($objStudents);die;

            //dump ($infoUe);
            //dump ($dataUe);die;
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function changeEstadoAction(Request $request){

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

    public function listaCursoAction(Request $request){
        try
        {
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_per_show_list_curso_corto'))
                // ->add('fechaInicio','text',array('label'=>'Fecha Inicio','attr'=>array('class'=>'form-control calendario','autocomplete'=>'off')))
                //->add('curso', 'text', array( 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('fechaInicio', 'datetime', array('widget' => 'single_text','date_format' => 'dd-MM-yyyy','attr' => array('class' => 'form-control calendario')))
                ->add('fechaFin', 'datetime', array('widget' => 'single_text','date_format' => 'dd-MM-yyyy','attr' => array('class' => 'form-control calendario')))
                 //->add('mostrar', 'submit', array('label' => 'Mostrar ', 'attr' => array('class' => 'btn btn-primary', 'disbled' => true)))
                ->getForm();

            return $this->render('SiePermanenteBundle:CursosCortos:listCursos.html.twig', array(

                'form' => $form->createView()
            ));
        }catch (Exception $ex)
        {

        }

    }

    public function openCursoAction(Request $request){
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

    public function listarEspecialidadesAction($progarea) {
        //  dump($progareao);die;
        try {
            $sie= $this->session->get('ie_id');
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare('	select distinct sest.id , sest.especialidad
				from institucioneducativa ined
					inner join superior_institucioneducativa_acreditacion sia on ined.id = sia.institucioneducativa_id
						inner join superior_acreditacion_especialidad sae on sia.acreditacion_especialidad_id =sae.id
								inner join superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id = sest.id
										inner join superior_facultad_area_tipo sfat on sfat.id = sest.superior_facultad_area_tipo_id
											where ined.id=:sie and sfat.id =40
        ');
            $query->bindValue(':sie', $sie);
            $query->execute();
            $espUE= $query->fetchAll();

            $espUEArray = array();
            foreach ($espUE as $value) {

                $espUEArray[$value['id']] =$value['especialidad'];
            }

            $response = new JsonResponse();
            return $response->setData(array('especialidadlista' => $espUEArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function listarNivelesAction($espec) {
        //  dump($progareao);die;
        try {
            $sie= $this->session->get('ie_id');
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare('
							select sae.id,sat.id as acreditacionid, sat.acreditacion 
							from superior_acreditacion_especialidad sae
									inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
											inner join superior_institucioneducativa_acreditacion sia on sia.acreditacion_especialidad_id = sae.id
												inner join institucioneducativa ie on sia.institucioneducativa_id = ie.id
											where sae.superior_especialidad_tipo_id = :espec
											and ie.id=:sie
        ');
            $query->bindValue(':sie', $sie);
            $query->bindValue(':espec', $espec);
            $query->execute();
            $nivel= $query->fetchAll();

            $nivelArray = array();
            foreach ($nivel as $value) {

                $nivelArray[$value['acreditacionid']] =$value['acreditacion'];
            }

            $response = new JsonResponse();
            return $response->setData(array('nivellista' => $nivelArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function mostrarHorasAction($niv) {
            //dump($niv);die;
            try {

                $em = $this->getDoctrine()->getManager();

                if($niv==1)
                {
                $horas=500;
                }elseif($niv==20)
                {
                    $horas=500;
                }elseif($niv==32)
                {
                    $horas=1000;
                }
                //   dump($poblaciones);die;


                $response = new JsonResponse();
                return $response->setData(array('mostrarhoras' => $horas));
            } catch (Exception $ex) {
                //$em->getConnection()->rollback();
            }
    }



    private function getCourseInfo($idcurso){

        $em = $this->getDoctrine()->getManager();

        // $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $sie= $this->session->get('ie_id');
        $gestion=$this->session->get('ie_gestion');
        $suc=$this->session->get('ie_subcea');
        $periodo=$this->session->get('ie_per_cod');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $querya = $em->getConnection()->prepare('select h.id as iecid, psat.id as subareaid,psat.sub_area as subarea,ppt.id as programaid,ppt.programa,
        --a.id as programaid, a.facultad_area as programa,
        d.id as acreditacionid, d.acreditacion as acreditacion,b.id as cursolargoid,b.especialidad as cursolargo,pt.id as paraleloId, pt.paralelo, picc.esabierto
        from superior_facultad_area_tipo a
        inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
        inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
        inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
        inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id
        inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id
        inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
        inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id = g.id
        inner join superior_turno_tipo q on h.turno_tipo_id = q.id
        inner join permanente_institucioneducativa_cursocorto picc on picc.institucioneducativa_curso_id = h.id
        inner join permanente_programa_tipo ppt on ppt.id =picc.programa_tipo_id
        inner join permanente_sub_area_tipo psat on psat.id = picc.sub_area_tipo_id
        inner join paralelo_tipo pt on pt.id=h.paralelo_tipo_id
        where f.gestion_tipo_id=:gestion and f.institucioneducativa_id=:sie and h.id =:idcurso
        and f.sucursal_tipo_id=:suc --and f.periodo_tipo_id=1
        and a.id =40  and  h.nivel_tipo_id =231
        ');
        //$querya->bindValue(':nivel', 231);
        $querya->bindValue(':sie', $sie);
        $querya->bindValue(':gestion', $gestion);
        $querya->bindValue(':suc', $suc);
        $querya->bindValue(':idcurso', $idcurso);
         //    $querya->bindValue(':periodo', $periodo);
        $querya->execute();

        $objUeducativa= $querya->fetchAll();
        // dump($objUeducativa);die;
        $exist = true;
        $aInfoUnidadEductiva = array();
        $sinfoUeducativa = array();
        if (sizeof($objUeducativa)>0) {
             $objUeducativa =  $objUeducativa[0];

                $sinfoUeducativa = (array(
                    'ueducativaInfo' => array('subarea' => $objUeducativa['subarea'],'programa' => $objUeducativa['programa'], 'cursolargo' => $objUeducativa['cursolargo'], 'acreditacion' => $objUeducativa['acreditacion'], 'paralelo' => $objUeducativa['paralelo'],
                    'ueducativaInfoId' => array('subareaid' => $objUeducativa['subareaid'],'programaid' => $objUeducativa['programaid'], 'cursolargoid' => $objUeducativa['cursolargoid'], 'acreditacionid' => $objUeducativa['acreditacionid'], 'iecid' => $objUeducativa['iecid'], 'paraleloId' => $objUeducativa['paraleloid'],'esabierto'=> $objUeducativa['esabierto'])
                )));

                // $aInfoUnidadEductiva[$uEducativa['esabierto']][$uEducativa['subarea']][$uEducativa['programa']][$uEducativa['cursolargo']] [$uEducativa['acreditacion']][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa, 'esabierto'=>$uEducativa['esabierto'], 'iecId'=> $uEducativa['iecid']);

         //   dump($aInfoUnidadEductiva);die;
        } else {
            $message = 'No existe información del Centro de Educación  para la gestión seleccionada.';
            $this->addFlash('warninresult', $message);
            $exist = false;
        }

        return $sinfoUeducativa;

    }




    public function showNotasCLAction (request $request){
         // dump($request);die;

        $em = $this->getDoctrine()->getManager();

        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');

        $aInfoUeducativa = array();//unserialize($infoUe);
        $aInfoStudent = json_decode($infoStudent, TRUE);

        $aInfoUeducativa = $this->getCourseInfo($infoUe);
        // dump($aInfoUeducativa);
        // die;
        $aInfoUeducativaCurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId'];
        $idcurso = $request->get('infoUe');// $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
        // dump($aInfoUeducativa);die;
        $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idcurso);
        $ieducativaId = $iec->getInstitucioneducativa()->getId();
        $gestion = $iec->getGestionTipo()->getId();

        $idInscripcion = $aInfoStudent['eInsId'];
        //dump($idInscripcion);die;
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $idestudiante= $inscripcion->getEstudiante()->getId();

        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' =>$idestudiante ));


        $query = $em->getConnection()->prepare('
					select ieco.id as idieco, smp.id as idsmp, smp.horas_modulo, smt.id as idsmt, smt.modulo,iecom.id as idiecom,mi.id as idmi, per.id as idper, (per.paterno||\' \'||per.materno||\' \'||per.nombre) as nombre, x.esaid,x.esinsid,x.esacursoid, x.esnotaid,x.esnota
				--	per.nombre, per.paterno,per.materno
					from institucioneducativa_curso iec
							inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
									inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
											inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
														left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
																left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
																		left join persona per on per.id = mi.persona_id
															inner join (select iecoo.id as iecoid,esa.id as esaid, esins.id esinsid, iecoo.insitucioneducativa_curso_id as esacursoid, esnot.id as esnotaid, esnot.nota_cuantitativa as esnota
																			from estudiante_asignatura esa 
																				inner join estudiante_inscripcion esins on esa.estudiante_inscripcion_id = esins.id
																					inner join institucioneducativa_curso_oferta iecoo on esa.institucioneducativa_curso_oferta_id =iecoo.id
																								left join estudiante_nota esnot on esnot.estudiante_asignatura_id =esa.id
																			--	where esa.institucioneducativa_curso_oferta_id=54812055
																	where esa.estudiante_inscripcion_id=:idei
																			) x on x.iecoid =ieco.id
											where iec.id=:idcurso
											order by iecom.id desc
        ');
        $query->bindValue(':idei',$idInscripcion);
        $query->bindValue(':idcurso',$idcurso);
        $query->execute();
        $listamodcurso= $query->fetchAll();

        //dump($estudiante);die;
        return $this->render('SiePermanenteBundle:CursosLargos:notasEstudiante.html.twig', array(

        //            'form' => $form->createView(),
            'idinscripcion'=>$idInscripcion,
            'lstmod'=> $listamodcurso,
            'infoUe'=>$infoUe,
            'curso'=>$aInfoUeducativa['ueducativaInfo'],
            'estudiante'=>$estudiante,
            'idcurso'=>$idcurso

        ));



    }

    public function updateNotasCLAction (request $request){
           // dump($request);

        $em = $this->getDoctrine()->getManager();

        $infoUe = $request->get('infoUe');
        $form = $request->get('form');
        $esinsid= $form['esinsid'];
        $idesasig= $form['esaid'];
        $idieco= $form['idieco'];
        $idnota= $form['esnotaid'];
        $nota= $form['nota'];
        $userId = $this->session->get('userId');
        $aInfoUeducativa =  $infoUe;
        //$aInfoUeducativaCurso = $aInfoUeducativa['ueducativaInfo']['ueducativaInfoId'];
        $idcurso = $infoUe;
        $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idcurso);
        $ieducativaId = $iec->getInstitucioneducativa()->getId();
        $gestion = $iec->getGestionTipo()->getId();


        //dump($idInscripcion);die;
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($esinsid);
        $idestudiante= $inscripcion->getEstudiante()->getId();

        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' =>$idestudiante ));
     //   DUMP($esinsid);DUMP($idcurso);DIE;
        $em->getConnection()->beginTransaction();
        if (($idnota)==null){

            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();
            $estudiantenota = new EstudianteNota();
            $estudiantenota ->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(41));
            $estudiantenota->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idesasig));
            $estudiantenota ->setNotaCuantitativa($nota);
            $estudiantenota ->setNotaCualitativa(mb_strtoupper($nota,'utf-8'));
            $estudiantenota->setRecomendacion('');
            $estudiantenota->setUsuarioId($this->session->get('userId'));
            $estudiantenota->setFechaRegistro(new \DateTime('now'));
            $estudiantenota->setFechaModificacion(new \DateTime('now'));
            $estudiantenota->setObs('');
            $em->persist($estudiantenota);

            $em->flush($estudiantenota);

            $em->getConnection()->commit();
        //    dump($estudiantenota);die;
        }
        else{
        //dump($idnota);die;
            $updateNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($idnota);
            if($updateNota) {
                $updateNota->setNotaCuantitativa($nota);
                $updateNota->setNotaCualitativa(mb_strtoupper($nota, 'utf-8'));
                $updateNota->setUsuarioId($this->session->get('userId'));
                $updateNota->setFechaModificacion(new \DateTime('now'));
                $em->flush();
                $em->getConnection()->commit();
                    }
        }
           // DUMP($esinsid);DUMP($idcurso);DIE;
            $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('
					select ieco.id as idieco, smp.id as idsmp, smp.horas_modulo, smt.id as idsmt, smt.modulo,iecom.id as idiecom,mi.id as idmi, per.id as idper, (per.paterno||\' \'||per.materno||\' \'||per.nombre) as nombre, x.esaid,x.esinsid,x.esacursoid, x.esnotaid,x.esnota
				--	per.nombre, per.paterno,per.materno
					from institucioneducativa_curso iec
							inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
									inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
											inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
														left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
																left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
																		left join persona per on per.id = mi.persona_id
															inner join (select iecoo.id as iecoid,esa.id as esaid, esins.id esinsid, iecoo.insitucioneducativa_curso_id as esacursoid, esnot.id as esnotaid, esnot.nota_cuantitativa as esnota
																			from estudiante_asignatura esa 
																				inner join estudiante_inscripcion esins on esa.estudiante_inscripcion_id = esins.id
																					inner join institucioneducativa_curso_oferta iecoo on esa.institucioneducativa_curso_oferta_id =iecoo.id
																								left join estudiante_nota esnot on esnot.estudiante_asignatura_id =esa.id
																			--	where esa.institucioneducativa_curso_oferta_id=54812055
																	where esa.estudiante_inscripcion_id=:idei
																			) x on x.iecoid =ieco.id
											where iec.id=:idcurso
											order by iecom.id desc
        ');

        $query->bindValue(':idei',$esinsid);
        $query->bindValue(':idcurso',$idcurso);
        $query->execute();
        $listamodcurso= $query->fetchAll();

        //dump($listamodcurso);die;
        return $this->render('SiePermanenteBundle:CursosLargos:notasEstudianteTabla.html.twig', array(

            //            'form' => $form->createView(),
            'idinscripcion'=>$esinsid,
            'lstmod'=> $listamodcurso,
            'infoUe'=>$infoUe,
            'estudiante'=>$estudiante,
            'idcurso'=>$idcurso

        ));





    }

    public function saveModulosLoteAction(Request $request) {
        //dump($request);die;
        $em = $this->getDoctrine()->getManager();

        $infoUe = $request->get('infoUe');
       
        //$aInfoUeducativa = unserialize($infoUe);
        $aInfoUeducativa = $this->getCourseInfo($infoUe);
        // dump($aInfoUeducativa);die;
       // $dataUe=(unserialize($infoUe));

        $date = date('Y/m/d' );
        $date2 = date("Y-m-d",strtotime($date));
        //   dump($date);dump($date2);die;
        $idieco = $request->get('idieco');
        $idestins = $request->get('idestins');
     //   $idcurso = $request->get('idcurso');
        $gestion = $this->session->get('ie_gestion');
        // dump ($aInfoUeducativa);die;
        $exist = true;
        $idcurso=$aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
        //  dump ($dataUe);die;
        $objStudents = array();

        $query = $em->getConnection()->prepare('
                select c.id as idcurso, b.id as idestins,d.id as estadomatriculaid,d.estadomatricula  AS estadomatricula, b.estudiante_id as idest, a .codigo_rude as codigorude, a.carnet_identidad as carnet,a.paterno,a.materno,a.nombre,a.fecha_nacimiento as fechanacimiento, e.genero, a.complemento
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

     //   $objStudentsArray = array();


          //dump($objStudents);die;

        $query = $em->getConnection()->prepare('
	    select a.id as iecid, a.periodo_tipo_id, a.grado_tipo_id, a.gestion_tipo_id, a.nivel_tipo_id, a.turno_tipo_id,tt.turno,a.fecha_inicio,a.fecha_fin,a.duracionhoras,
                        b.esabierto, b.lugar_tipo_departamento_id as deptoid,depto.lugar as departamento,  b.lugar_tipo_provincia_id as provid,prov.lugar as provincia,  b.lugar_tipo_municipio_id as munid,mun.lugar as municipio, b.lugar_detalle as comunidad,b.poblacion_detalle,
                        c.areatematica, d.poblacion,e.programa, f.sub_area, g.cursocorto,
                        h.id as codofermaes,h.horasmes, 
                        i.maestro_inscripcion_id,
                        k.paterno,k.materno,k.nombre,
                        m.id as cursolargoid,m.sub_area_tipo_id,m.programa_tipo_id, m.areatematica_tipo_id,m.cursocorto_tipo_id,
												sip.id as superid,
												sia.id as siaid,
												sae.id as saeid,
												sat.acreditacion,
												sespt.especialidad,
												sfat.facultad_area as areaprograma
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
                                                                                        inner join superior_institucioneducativa_periodo sip on a.superior_institucioneducativa_periodo_id = sip.id
                                                                                                inner join lugar_tipo depto on depto.id= b.lugar_tipo_departamento_id  
                                                                                                    inner join lugar_tipo prov on prov.id = b.lugar_tipo_provincia_id
                                                                                                        inner join lugar_tipo mun on mun.id = b.lugar_tipo_municipio_id
                                                                                                inner join turno_tipo tt on tt.id= a.turno_tipo_id
                                                                                                        inner join superior_periodo_tipo spt on spt.id  = sip.superior_periodo_tipo_id
                                                                                                            inner join superior_institucioneducativa_acreditacion sia on sia.id = sip.superior_institucioneducativa_acreditacion_id
                                                                                                                inner join institucioneducativa ie on ie.id =sia.institucioneducativa_id
                                                                                                                    inner join superior_acreditacion_especialidad sae on sae.id = sia.acreditacion_especialidad_id
                                                                                                                        inner join superior_acreditacion_tipo sat on sat.id = sae.superior_acreditacion_tipo_id
                                                                                                                            inner join superior_especialidad_tipo sespt on sespt.id = sae.superior_especialidad_tipo_id
                                                                                                                                inner join superior_facultad_area_tipo sfat on sfat.id = sespt.superior_facultad_area_tipo_id
                    where  a.nivel_tipo_id= 231 and a.id=:curso
	        ');
        $query->bindValue(':curso', $idcurso);
        $query->execute();
        $cursosLargos= $query->fetchAll();
        //  dump($cursosLargos);die;

        if (count($objStudents) > 0){
            $existins = true;
        }
        else {
            $existins = false;
        }
        $estadomatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findAll();
        $estadomatriculaArray = array();

        $query = $em->getConnection()->prepare('
            select ieco.id as idieco, smp.id as idsmp, smp.horas_modulo, smt.id as idsmt, smt.modulo,iecom.id as idiecom,mi.id as idmi, per.id as idper,
            (per.paterno||\' \'||per.materno||\' \'||per.nombre) as nombre
            from institucioneducativa_curso iec
            inner join institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id=iec.id
            inner join superior_modulo_periodo smp on ieco.superior_modulo_periodo_id = smp.id
            inner join superior_modulo_tipo smt on smp.superior_modulo_tipo_id = smt.id
            left join institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id
            left join maestro_inscripcion mi on mi.id = iecom.maestro_inscripcion_id
            left join persona per on per.id = mi.persona_id
            where iec.id=:idcurso
            order by iecom.id desc
        ');
        $query->bindValue(':idcurso',$idcurso);
        $query->execute();
        $listamodcurso= $query->fetchAll();
         //dump($listamodcurso);die;
        foreach ($objStudents as $estud) {
            $inscripcion= $estud['idestins'];
                foreach($listamodcurso as $mod){
                    $cursoOferta = $mod['idieco'];
                   // dump($inscripcion);dump($cursoOferta);die;

                    $query = $em->getConnection()->prepare('
                        select es.id as idestasig 
                        from estudiante_asignatura es
                        inner join estudiante_inscripcion ei on ei.id= es.estudiante_inscripcion_id
                        inner join 	institucioneducativa_curso_oferta ieco on ieco.id = es.institucioneducativa_curso_oferta_id
                        where ei.id =:idei and ieco.id =:idieco');
                    $query->bindValue(':idei',$inscripcion);
                    $query->bindValue(':idieco',$cursoOferta);
                    $query->execute();
                    $idasignatura= $query->fetch();

                    if($idasignatura){

                    }else{
                        $estasignatura = new  EstudianteAsignatura();
                    $estasignatura ->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                    $estasignatura ->setFechaRegistro(new \DateTime($date2));
                    $estasignatura ->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $inscripcion)));
                    $estasignatura ->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findOneBy(array('id' => $cursoOferta)));
                    $estasignatura ->setEstudianteasignaturaEstado($em->getRepository('SieAppWebBundle:EstudianteasignaturaEstado')->find(4));
                    $em->persist($estasignatura);
                    $em->flush($estasignatura);
                    }

                }
        }



        $form = $this->createFormBuilder()
            ->add('matricula', 'choice', array('required' => false, 'choices' => $estadomatriculaArray,  'attr' => array('class' => 'form-control')))
            ->getForm();
        //   dump($dataUe);
        //   dump($objStudents);die;
        // get the infor about the operative
        $swInscription  = $this->getOperativeData($sw=false, $idcurso);
        $swCalification = false;
        if(!$swInscription){
            $swCalification =  $this->getOperativeData(!$swInscription, $idcurso);
        }
             
        return $this->render('SiePermanenteBundle:CursosLargos:seeInscritos.html.twig', array(
            'objStudents' => $objStudents,
            'objx' => $estadomatriculaArray,
            'form' => $form->createView(),
            'exist' => $exist,
            'lstmod'=>$listamodcurso,
            'cursolargo'=>$cursosLargos,
            'existins' => $existins,
            'swInscription' => $swInscription,
            'swCalification' => $swCalification,
            'infoUe' => $infoUe,
           // 'dataUe' => $dataUe,
            'totalInscritos'=>count($objStudents)

        ));

    }

}