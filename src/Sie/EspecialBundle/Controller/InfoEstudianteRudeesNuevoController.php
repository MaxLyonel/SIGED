<?php


namespace Sie\EspecialBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Rude;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegHablaFrec;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegNacion;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegInternet;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\RudeDiscapacidadGrado;
use Sie\AppWebBundle\Entity\RudeIdioma;
use Sie\AppWebBundle\Entity\RudeCentroSalud;
use Sie\AppWebBundle\Entity\RudeServicioBasico;
use Sie\AppWebBundle\Entity\RudeAccesoInternet;
use Sie\AppWebBundle\Entity\RudeMesesTrabajados;
use Sie\AppWebBundle\Entity\RudeActividad;
use Sie\AppWebBundle\Entity\RudeTurnoTrabajo;
use Sie\AppWebBundle\Entity\RudeRecibioPago;
use Sie\AppWebBundle\Entity\RudeMedioTransporte;
use Sie\AppWebBundle\Entity\RudeAbandono;
// use Sie\AppWebBundle\Entity\RudeApoderadoInscripcion;
use Sie\AppWebBundle\Entity\ApoderadoInscripcion;
use Sie\AppWebBundle\Entity\ApoderadoInscripcionDatos;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\EstadoCivilTipo;
use Sie\AppWebBundle\Entity\CedulaTipo;

use Sie\AppWebBundle\Entity\RudeDiscapcidadOrigen;
use Sie\AppWebBundle\Entity\EstudiantePersonaDiplomatico;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;


use Sie\AppWebBundle\Entity\RudeVive;
use Sie\AppWebBundle\Entity\RudeDificultadAprendizaje;
use Sie\AppWebBundle\Entity\RudeTalentoExtraordinario;
use Sie\AppWebBundle\Entity\RudeEstrategiaAtencionIntegral;
use Sie\AppWebBundle\Entity\RudeMediosComunicacion;
use Sie\AppWebBundle\Entity\RudeParienteDiscapacidad;
use Sie\AppWebBundle\Entity\RudeAtencionIndirecta;


/**
 * SocioeconomicoAlternativa controller.
 *
 */
class InfoEstudianteRudeesNuevoController extends Controller
{

	public $session;
	public $idInstitucion;

	/**
	 * the class constructor
	 */
	public function __construct()
	{
		//init the session values
		$this->session = new Session();
	}

	public function indexAction(Request $request)
	{
		
		$em = $this->getDoctrine()->getManager();
		$infoUe = $request->get('infoUe'); 
		$infoStudent = $request->get('infoStudent');
		$editar = $request->get('editar');
		$aInfoUeducativa = unserialize($infoUe);
		$aInfoStudent = json_decode($infoStudent, TRUE); 
		$iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfoId']['iecId']);
		$sie = $iec->getInstitucioneducativa()->getId();
		$gestion = $iec->getGestionTipo()->getId();
		$idInscripcion = $aInfoStudent['eInsId'];
		$inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
		$estudiante = $inscripcion->getEstudiante();
		$rude = $em->getRepository('SieAppWebBundle:Rude')->findOneBy(array('estudianteInscripcion'=>$inscripcion->getId()));
		
		
		//obtenemos los datos de la tabla estado civil tipo
		$estadoCivilData = $em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findAll();
		if(!is_object($rude)){
			/**
			 * OBTENEMOS UN REGISTRO ANTERIOR
			 */
			$rudeAnterior = $em->createQueryBuilder()
						->select('r')
						->from('SieAppWebBundle:Rude','r')
						->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','r.estudianteInscripcion = ei.id')
						->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
						->where('e.id = :estudiante')
						->setParameter('estudiante', $estudiante->getId())
						->setMaxResults(1)
						->getQuery()
						->getResult();			
			if(count($rudeAnterior) == 1){

				$rudeAnterior = $rudeAnterior[0];
				$rude = clone $rudeAnterior;
				$rude->setEstudianteInscripcion($inscripcion);
				$em->persist($rude);
				$em->flush();
			}else{
				$jg = $em->createQueryBuilder()
							->select('jg')
							->from('SieAppWebBundle:JurisdiccionGeografica','jg')
							->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','ie.leJuridicciongeografica = jg.id')
							->where('ie.id = :sie')
							->setParameter('sie', $sie)
							->getQuery()
							->getResult();
				$direccion = 'ZONA '.$jg[0]->getZona().' '.$jg[0]->getDireccion();
				$rude = new Rude();
				$rude->setEstudianteInscripcion($inscripcion);
				$rude->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find(1));
				$rude->setFechaRegistro(new \DateTime('now'));
				$rude->setLugarRegistroRude($direccion);
				$em->persist($rude);
				$em->flush();
			}
			
		}

		// OBTENER APODERADOS DEL ESTUDIANTE
		// PADRE
		$padre = $this->obtenerApoderado($idInscripcion,array(1));
		$formPadre = $this->createFormApoderado($rude, $idInscripcion, $padre[0]);
		// MADRE
		$madre = $this->obtenerApoderado($idInscripcion,array(2));
		$formMadre = $this->createFormApoderado($rude, $idInscripcion, $madre[0]);
		// TUTOR
		$tutor = $this->obtenerApoderado($idInscripcion,$this->obtenerCatalogo($rude, 'apoderado_tipo'));
		$formTutor = $this->createFormApoderado($rude, $idInscripcion, $tutor[0]);

		$ayudaComplemento = ["Complementito","Contenido del complemento, no se refiere al lugar de expedición del documento."];

		$sistema  = $this->session->get('pathSystem');		
		$vista = 'SieHerramientaBundle:InfoEstudianteRudeNuevo:index.html.twig';
		switch ($sistema)
		{
			case 'SieHerramientaBundle':
				$vista = 'SieHerramientaBundle:InfoEstudianteRudeNuevo:index.html.twig';
			break;
			case 'SieEspecialBundle':
				$vista = 'SieEspecialBundle:InfoEstudianteRudeNuevo:index.html.twig';
			break;
			default:
				$vista = 'SieHerramientaBundle:InfoEstudianteRudeNuevo:index.html.twig';
			break;
		}		
		$paralelo = $aInfoUeducativa['ueducativaInfo']['paralelo'];
		$areaEspecial = $aInfoUeducativa['ueducativaInfo']['areaEspecial'];
		$nivel= $aInfoUeducativa['ueducativaInfo']['nivel'];
		$grado= $aInfoUeducativa['ueducativaInfo']['grado'];
		$programa=$aInfoUeducativa['ueducativaInfo']['programa'];
		$servicio=$aInfoUeducativa['ueducativaInfo']['servicio'];
		$iecId=$aInfoUeducativa['ueducativaInfoId']['iecId'];
		
		//buscamos la especialidad en caso de formacion tecnica
		$especialidad='';
		
			$tecnica = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array('institucioneducativaCurso'=>$iecId));// dump($tecnica);die;
			$especialidadTipo = $em->getRepository('SieAppWebBundle:EspecialTecnicaEspecialidadTipo')->find($tecnica->getEspecialTecnicaEspecialidadTipo()); 
			$especialidad = $especialidadTipo->getEspecialidad(); 
		//Buscamos los servicios a los que se encuentre inscrito el estudiante
		
		$query2 = $em->getConnection()->prepare('select * FROM especial_servicio_tipo WHERE id in(
							SELECT especial_servicio_tipo_id from institucioneducativa_curso_especial WHERE especial_modalidad_tipo_id=3  AND institucioneducativa_curso_id in (
								SELECT id FROM institucioneducativa_curso WHERE gestion_tipo_id = '.$gestion.' and id in( SELECT institucioneducativa_curso_id from estudiante_inscripcion WHERE estudiante_id = '.$estudiante->getId().' ) and nivel_tipo_id = 410  )) 
								');
        $query2->execute();
        $serviciosArray = $query2->fetchAll();

		$query3 = $em->getConnection()->prepare('
		select a.area_especial , ce.especial_programa_tipo_id  as programa_id, t.programa , ce.especial_servicio_tipo_id  as servicio_id, s.servicio , ei.estudiante_id , ntt.nivel_tecnico, n.nivel, pt.paralelo, emt.modalidad, etet.especialidad , mo.momento, gt.grado,  tt.turno
		,ntt.nivel_tecnico, n.id, n.nivel, pt.paralelo, etet.especialidad ,gt.grado, gt.grado,  tt.turno 
                        from estudiante e,  estudiante_inscripcion ei, institucioneducativa_curso c , institucioneducativa_curso_especial ce, 
                        especial_area_tipo a, especial_programa_tipo t, especial_servicio_tipo s, nivel_tipo n, especial_nivel_tecnico_tipo ntt, paralelo_tipo pt, 
                        especial_tecnica_especialidad_tipo etet, especial_modalidad_tipo emt, especial_momento_tipo mo, grado_tipo gt, turno_tipo tt
                        where e.id=ei.estudiante_id and c.id=ei.institucioneducativa_curso_id and c.gestion_tipo_id = 2024
                        and c.id=ce.institucioneducativa_curso_id 
                       and a.id=ce.especial_area_tipo_id 
                       and t.id=ce.especial_programa_tipo_id 
                       and s.id=ce.especial_servicio_tipo_id 
                       and n.id=c.nivel_tipo_id
                       and ce.especial_nivel_tecnico_tipo_id = ntt.id
                       and pt.id=c.paralelo_tipo_id
                       and ntt.id=ce.especial_nivel_tecnico_tipo_id 
                       and etet.id=ce.especial_tecnica_especialidad_tipo_id 
                       and emt.id=ce.especial_modalidad_tipo_id 
                       and mo.id = ce.especial_momento_tipo_id 
                       and c.grado_tipo_id=gt.id
                       and c.turno_tipo_id=tt.id
                       and e.id ='.$estudiante->getId().' 
                       and c.gestion_tipo_id = '.$gestion.'
                       and ce.especial_modalidad_tipo_id<3 
					   and ei.id<>'.$idInscripcion.'
				');
		$query3->execute();
		$inscripcionesArray = $query3->fetchAll();

		//dump($queryEspecialidades);die;
		/*$serviciosArray = array();
		foreach ($queryEspecialidades as $gd) {
			$serviciosArray[] = $gd['servicio'];
		}
		dump($serviciosArray);die;*/
		 //inscripciones -directas-indirectas
		 //inscripciones indirectas
		
		return $this->render( $vista , [
			'sie'=>$sie,
			'estudiante'=>$estudiante,
			'formEstudiante'=>$this->createFormEstudiante($rude, $estudiante)->createView(),
			'formDireccion'=>$this->createFormDireccion($rude)->createView(),
			'formSocioeconomico'=>$this->createFormSocioeconomico($rude)->createView(),
			'formCaracteristicaParticular'=>$this->createFormCaracteristicaParticular($rude)->createView(),
			'formInscripcionActual'=>$this->createFormInscripcionActual($rude)->createView(),
			'formConQuienVive'=>$this->createFormConQuienVive($rude)->createView(),
			'formPadre'=>$formPadre->createView(),
			'formMadre'=>$formMadre->createView(),
			'formTutor'=>$formTutor->createView(),
			'padre'=>$padre[0],
			'madre'=>$madre[0],
			'tutor'=>$tutor[0],
			'ayudaComplemento'=>$ayudaComplemento,
			'formLugar'=>$this->createFormLugar($rude)->createView(),
			'inscripcion'=>$inscripcion,
			'rude'=>$rude,
			'estadoCivilData'=>$estadoCivilData,
			'aInfoUeducativa'=>$aInfoUeducativa,
			
			'areaEspecial'=>$areaEspecial,
			'paralelo'=>$paralelo,
			'nivel'=>$nivel,
			'grado'=>$grado,
			'programa'=>$programa,
			'servicio'=>$servicio,
			'especialidad'=>$especialidad,
			'serviciosArray'=>$serviciosArray,
			'inscripcionesArray'=>$inscripcionesArray

		]);
	}

	/**
	 * DATOS DE LA O EL ESTUDIANTE
	 */
	private function createFormEstudiante($rude, $e)
	{	
		$em = $this->getDoctrine()->getManager();
		$pais = $e->getPaisTipo()->getId();
		if($pais == 1){
			$departamento = '79354';
			$provincia = 11;
			if($e->getLugarNacTipo() != null){
				$departamento = $e->getLugarNacTipo()->getId();
			}
			if($e->getLugarProvNacTipo() != null){
				$provincia = $e->getLugarProvNacTipo()->getId();                
			}
		}else{
			$departamento = '79354';
			$provincia = 11;
		}
		$departamentos = array();
		$provincias = array();

		if($pais == 1){
			$condition = array('lugarNivel' => 1, 'paisTipoId' => $pais);
			$dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy($condition);
			foreach ($dep as $d) {
				$departamentos[$d->getId()] = $d->getLugar();
			}

			$prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $departamento));
			foreach ($prov as $p) {
				$provincias[$p->getid()] = $p->getlugar();
			}
		}
		//verificacion si el estudiante tiene CI
		$estudianteInscripcion = $em->getRepository('SieAppWebBundle:estudianteInscripcion')->findById(array($rude->getEstudianteInscripcion()->getId()));
		$estudianteCi=$estudianteInscripcion[0]->getEstudiante()->getCarnetIdentidad();
		$estudiantePasaporte=$estudianteInscripcion[0]->getEstudiante()->getPasaporte();
		
		$tieneCi = 0;
		$tienePasaporte=0;
		if($estudianteCi!=''){
			$tieneCi=1;
		}
		if($estudiantePasaporte!=''){
			$tienePasaporte=1;
		}
		// DISCAPACIDAD DEL ESTUDIANTE 
		
		$discapacidadEstudiante = $em->getRepository('SieAppWebBundle:RudeDiscapacidadGrado')->findOneBy(array('rude'=>$rude->getId()));
		//dumo($discapacidadEstudiante);die;
		$gradosArray = array();
		if($discapacidadEstudiante)
		{
			// SI LA DISCAPACDIDAD ES VISUAL ENTONCES SOLO MOSTRAMOS CIEETOS GRADOS DE DISCAPACIDAD
			if($discapacidadEstudiante->getDiscapacidadTipo()->getId() == 10){
				$gradoDiscapacidad = $em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->findBy(array('id'=>array(6,5)));
			}else{
				$gradoDiscapacidad = $em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->findBy(array('id'=>array(1,2,7,8)));
			}

			foreach ($gradoDiscapacidad as $gd) {
				$gradosArray[] = $gd->getId();
			}
		}

		// LUGAR DE NACIMIENTO
		$departamentoNacimiento = $em->getRepository('SieAppWebBundle:LugarTipo')->find($departamento);
		$provinciaNacimiento = $em->getRepository('SieAppWebBundle:LugarTipo')->find($provincia);
//dump($gradosArray);
//dump($discapacidadEstudiante);die;
		$form = $this->createFormBuilder()
					// ->setAction($this->generateUrl('info_estudiante_rude_save_form2'))
					->add('rudeId', 'hidden', array('data' => $rude->getId(),'mapped'=>false))
					->add('estudianteId', 'hidden', array('data' => $e->getId()))
					->add('pais', 'entity', array(
							'class' => 'SieAppWebBundle:PaisTipo',
							'query_builder' => function (EntityRepository $e) {
								return $e->createQueryBuilder('p')
										->where('p.id != 0')
										->orderBy('p.pais', 'ASC')
								;
							},
							'empty_value' => 'Seleccionar...',
							'required'=>true,
							'property' => 'pais',
							'data'=>$e->getPaisTipo()
						))
					->add('departamento', 'choice', array('required'=>false,'empty_value'=>'Seleccionar', 'choices'=>$departamentos, 'data'=>$departamento))
					->add('provincia', 'choice', array('required'=>false,'empty_value'=>'Seleccionar', 'choices'=>$provincias, 'data'=>$provincia))
					->add('localidadNac', 'text', array('required'=>false,'required'=>true, 'data'=>$e->getLocalidadNac()))

					->add('codigoRude', 'text', array('required' => false, 'data'=>$e->getCodigoRude()))
					->add('carnet', 'text', array('required' => false, 'data'=>$e->getCarnetIdentidad()))
					->add('complemento', 'text', array('required' => false, 'data'=>$e->getComplemento()))
					->add('fechaNacimiento', 'text', array('required' => false, 'data'=>$e->getFechaNacimiento()->format('d-m-Y')))
					->add('sexo', 'entity', array(
							'class' => 'SieAppWebBundle:GeneroTipo',
							'query_builder' => function (EntityRepository $e) {
								return $e->createQueryBuilder('gt')
										->where('gt.id != 3');
							},
							'empty_value' => 'Seleccionar...',
							'required' => true,
							'data'=>$e->getGeneroTipo()
						))
					->add('oficialia', 'text', array('required' => false, 'data'=>$e->getOficialia()))
					->add('libro', 'text', array('required' => false, 'data'=>$e->getLibro()))
					->add('partida', 'text', array('required' => false, 'data'=>$e->getPartida()))
					->add('folio', 'text', array('required' => false, 'data'=>$e->getFolio()))
					
				/*	->add('tieneDiscapacidad', 'choice', array(
							'choices'=>array(true=>'Si', false=>'No'),
							'data'=> ($discapacidadEstudiante)? true: false,
							'required'=>true,
							'multiple'=>false,
							'empty_value'=>false,
							'expanded'=>true
						))*/
					->add('carnetIbc', 'text', array('required' => false, 'data'=>$e->getCarnetIbc()))
					->add('carnetCodepedis', 'text', array('required' => false, 'data'=>$e->getCarnetCodepedis()))
					->add('discapacidadId', 'hidden', array('required' => false, 'data'=>($discapacidadEstudiante)?$discapacidadEstudiante->getId():'nuevo'))
					->add('discapacidad', 'entity', array(
							'class' => 'SieAppWebBundle:DiscapacidadTipo',
							'query_builder' => function (EntityRepository $e) use ($rude) {
								return $e->createQueryBuilder('dt')
										->where('dt.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'discapacidad_tipo'));
							},
							'empty_value' => 'Seleccione',
							'required' => false,
							'data'=>($discapacidadEstudiante)?$discapacidadEstudiante->getDiscapacidadTipo():'',
							'mapped'=>false
						))
					->add('gradoDiscapacidad', 'entity', array(
							'class' => 'SieAppWebBundle:GradoDiscapacidadTipo',
							'query_builder' => function (EntityRepository $e) use ($gradosArray) {
								return $e->createQueryBuilder('gdt')
										->where('gdt.id in (:ids)')
										->setParameter('ids', $gradosArray);
							},
							'empty_value' => 'Seleccionar...',
							'required' => false,
							'data'=>($discapacidadEstudiante)?$discapacidadEstudiante->getGradoDiscapacidadTipo():''
						))
					->add('departamentoNacimiento', 'hidden', array('data'=>$departamentoNacimiento->getLugar()))
					->add('provinciaNacimiento', 'hidden', array('data'=>$provinciaNacimiento->getLugar()))
					->add('tieneCi', 'hidden', array('data'=>$tieneCi))
					->add('tienePasaporte', 'hidden', array('data'=>$tienePasaporte))
					->getForm();
		//mp($form);die;
		return $form;
	}

	/*
	 * Funciones para actualizar lugar de nacimiento
	*/
	public function departamentosNacAction($pais)
	{
		$em = $this->getDoctrine()->getManager();
		$condition = array('lugarNivel' => 1, 'paisTipoId' => $pais);
		$dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy($condition);
		$departamento = array();
		foreach ($dep as $d)
		{
			$departamento[$d->getId()] = $d->getLugar();
		}
		$dto = $departamento;
		$response = new JsonResponse();
		return $response->setData(array('departamento' => $dto));
	}

	public function provinciasNacAction($departamento)
	{
		$em = $this->getDoctrine()->getManager();
		$prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $departamento));
		$provincia = array();
		foreach ($prov as $p)
		{
			$provincia[$p->getid()] = $p->getlugar();
		}
		$response = new JsonResponse();
		return $response->setData(array('provincia' => $provincia));
	}

	/*
	 * GUARDAR DATOS DEL FORMULARIO ESTUDIANTE
	 */

	public function saveFormEstudianteAction(Request $request)
	{
		
		$em = $this->getDoctrine()->getManager();
		$form = $request->get('form');// dump($form['tieneDiscapacidad']);die;
		//dump($form);die;
		$estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['estudianteId']);		
		$rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['rudeId']);
		if($form['discapacidadId']=='nuevo'){
			$tieneDiscapacidad=0;
		}else{$tieneDiscapacidad = 1; }
		//Se guardan los datos de Cert de nacimiento		
		$estudiante->setOficialia($form['oficialia']);
		$estudiante->setLibro($form['libro']);
		$estudiante->setPartida($form['partida']);
		$estudiante->setFolio($form['folio']);
		$estudiante->setCarnetIbc($form['carnetIbc']?$form['carnetIbc']:'');
		$estudiante->setCarnetCodepedis($form['carnetCodepedis']?$form['carnetCodepedis']:'');
		$rude->setTieneDiscapacidad($tieneDiscapacidad);
		$carnetIbc=empty($form['carnetIbc'])?0:1;
		$rude->setTieneCarnetDiscapacidad($carnetIbc);
		$rude->setTieneCi($form['tieneCi']);
		$em->persist($estudiante);
		$em->flush();
		
		// DISCAPACIDADES
		if( isset ($form['tieneDiscapacidad'])){
			if($form['tieneDiscapacidad'] == true){
				// Si tiene discapacidad lo registramos o Actualizamos			
				$discapacidadId = $form['discapacidadId'];
				if($discapacidadId == 'nuevo'){
					$discapacidad = new RudeDiscapacidadGrado();
					$discapacidad->setRude($em->getRepository('SieAppWebBundle:Rude')->find($form['rudeId']));
					$discapacidad->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->find(isset($form['discapacidad'])?$form['discapacidad']:0));
					$discapacidad->setGradoDiscapacidadTipo($em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->find(isset($form['gradoDiscapacidad'])?$form['gradoDiscapacidad']:0));
					$discapacidad->setFechaRegistro(new \DateTime('now'));
					$em->persist($discapacidad);
					$em->flush();
				}else{
					$discapacidad = $em->getRepository('SieAppWebBundle:RudeDiscapacidadGrado')->find($discapacidadId);
					if($discapacidad){
						$discapacidad->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->find($form['discapacidad']));
						$discapacidad->setGradoDiscapacidadTipo($em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->find($form['gradoDiscapacidad']));
						$discapacidad->setFechaModificacion(new \DateTime('now'));
						$em->persist($discapacidad);
						$em->flush();
					}
				}

				$estudiante->setCarnetIbc(isset($form['carnetIbc'])?$form['carnetIbc']:'');
				$em->flush($estudiante);
			}else{
				// Si no titne discapacidad lo eliminamos
				$eliminar = $em->createQueryBuilder()
					->delete('')
					->from('SieAppWebBundle:RudeDiscapacidadGrado','rdg')
					->where('rdg.rude = :rudeId')
					->setParameter('rudeId', $form['rudeId'])
					->getQuery()
					->getResult();

				$estudiante->setCarnetIbc('');
				$em->flush($estudiante);
			}
		}

		// Registro de paso 1
		if($rude->getRegistroFinalizado() < 1){
			$rude->setRegistroFinalizado(1);
			$em->flush();
		}
		

		$response = new JsonResponse();
		return $response->setData(['msg'=>true]);
	}

	/**
	 * CREAR FORMULARIO DE DIRECCION
	 */
	public function createFormDireccion($rude)
	{
		// DIRECCION
		$em = $this->getDoctrine()->getManager();
		if($rude->getMunicipioLugarTipo() != null)
		{
			$lt5_id = $rude->getMunicipioLugarTipo()->getLugarTipo();
			$lt4_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt5_id)->getLugarTipo();
			$lt3_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt4_id)->getLugarTipo();
			$m_id = $rude->getMunicipioLugarTipo()->getId();
			$p_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt5_id)->getId();
			$d_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt4_id)->getId();
		}
		else
		{
			$m_id = 0;
			$p_id = 0;
			$d_id = 0;
		}
		$dpto = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findBy(array('id'=>array(1,2,3,4,5,6,7,8,9)));
		$dptoArray = array();
		foreach($dpto as $value)
		{
			$dptoArray[$value->getId()] = $value->getDepartamento();
		}
		$query = $em->createQuery(
				'SELECT lt
				FROM SieAppWebBundle:LugarTipo lt
				WHERE lt.lugarNivel = :nivel
				AND lt.lugarTipo = :lt1
				ORDER BY lt.id')
				->setParameter('nivel', 2)
				->setParameter('lt1', $d_id);
		$prov = $query->getResult();

		$provArray = array();
		foreach ($prov as $value)
		{
			$provArray[$value->getId()] = $value->getLugar();
		}
		
		$query = $em->createQuery(
				'SELECT lt
				FROM SieAppWebBundle:LugarTipo lt
				WHERE lt.lugarNivel = :nivel
				AND lt.lugarTipo = :lt1
				ORDER BY lt.id')
				->setParameter('nivel', 3)
				->setParameter('lt1', $p_id);
		$muni = $query->getResult();

		$muniArray = array();
		foreach ($muni as $value)
		{
			$muniArray[$value->getId()] = $value->getLugar();
		}

		$form = $this->createFormBuilder($rude)
					->add('id','hidden')
					->add('departamentoDir', 'choice', array('data' => $d_id - 1, 'label' => 'Departamento', 'required' => true, 'choices' => $dptoArray, 'empty_value' => 'Seleccionar...','mapped'=>false))
					->add('provinciaDir', 'choice', array('data' => $p_id, 'label' => 'Provincia', 'required' => true, 'choices' => $provArray, 'empty_value' => 'Seleccionar...','mapped'=>false))
					->add('municipioLugarTipo', 'choice', array('data' => $m_id, 'label' => 'Municipio', 'required' => true, 'choices' => $muniArray, 'empty_value' => 'Seleccionar...','mapped'=>false))
					->add('localidad')
					->add('zona')
					->add('avenida')
					->add('numero')
					->add('celular')
					->add('telefonoFijo')
					->add('direccionProcedencia')
					->getForm();

		return $form;
	}

	public function saveFormDireccionAction(Request $request)
	{
		$form = $request->get('form');		
		$em = $this->getDoctrine()->getManager();
		$rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['id']);
		$rude->setMunicipioLugarTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find((integer)$form['municipioLugarTipo']));
		$rude->setLocalidad($form['localidad'] ? mb_strtoupper($form['localidad'], 'utf-8') : '');
		$rude->setZona($form['zona'] ? mb_strtoupper($form['zona'], 'utf-8') : '');
		$rude->setAvenida($form['avenida'] ? mb_strtoupper($form['avenida'], 'utf-8') : '');
		$rude->setNumero($form['numero'] ? $form['numero'] : '');
		$rude->setCelular($form['celular'] ? $form['celular'] : '');
		$rude->setTelefonoFijo($form['telefonoFijo'] ? $form['telefonoFijo'] : '');
		$rude->setDireccionProcedencia($form['direccionProcedencia'] ? $form['direccionProcedencia'] : '');

		// // Actualizamos el estado de registro, en que paso esta el usuario
		if($rude->getRegistroFinalizado() < 2){
			$rude->setRegistroFinalizado(2);
		}

		$em->persist($rude);
		$em->flush();

		$response = new JsonResponse();
		return $response->setData(['msg'=>true]);
	}

	/**
	 * CREAR FORMULARIO DE DATOS SOCIOECONOMICOS
	 */
	private function createFormSocioeconomico($rude)
	{
		
		$em = $this->getDoctrine()->getManager();

		$idiomas = $em->getRepository('SieAppWebBundle:RudeIdioma')->findBy(array('rude' => $rude));
		$idiomaNines = $em->createQueryBuilder()
						->select('ri')
						->from('SieAppWebBundle:RudeIdioma','ri')
						->where('ri.rude = :rude')
						->andWhere('ri.hablaTipo = 1')
						->setParameter('rude', $rude)
						->getQuery()
						->getResult();
		if(count($idiomaNines)>0){
			$idiomaNines = $idiomaNines[0];
		}
			
		/*$idiomasHablados = $em->createQueryBuilder()
						->select('ri')
						->from('SieAppWebBundle:RudeIdioma','ri')
						->where('ri.rude = :rudeId')
						->andWhere('ri.hablaTipo = 2')
						->orderBy('ri.id','asc')//esta linea para correjir el orden inverso de los idiomas
						->setParameter('rudeId', $rude->getId())
						->getQuery()
						->getResult();*/
		$idiomaMasFrecuente = $em->createQueryBuilder()
						->select('ri')
						->from('SieAppWebBundle:RudeIdioma','ri')
						->where('ri.rude = :rude')
						->andWhere('ri.hablaTipo = 2')
						->setParameter('rude', $rude)
						->getQuery()
						->getResult();
		if(count($idiomaMasFrecuente)>0){
			$idiomaMasFrecuente = $idiomaMasFrecuente[0];
		}
		$idiomaMedioFrecuente = $em->createQueryBuilder()
						->select('ri')
						->from('SieAppWebBundle:RudeIdioma','ri')
						->where('ri.rude = :rude')
						->andWhere('ri.hablaTipo = 3')
						->setParameter('rude', $rude)
						->getQuery()
						->getResult();
		if(count($idiomaMedioFrecuente)>0){
			$idiomaMedioFrecuente = $idiomaMedioFrecuente[0];
		}
		$idiomaMenosFrecuente = $em->createQueryBuilder()
						->select('ri')
						->from('SieAppWebBundle:RudeIdioma','ri')
						->where('ri.rude = :rude')
						->andWhere('ri.hablaTipo = 4')
						->setParameter('rude', $rude)
						->getQuery()
						->getResult();
		if(count($idiomaMenosFrecuente)>0){
			$idiomaMenosFrecuente = $idiomaMenosFrecuente[0];
		}			
		
		// NACIONES ORIGINARIAS
		$naciones = $em->getRepository('SieAppWebBundle:NacionOriginariaTipo')->findAll();
		$arrayNaciones = [];
		foreach ($naciones as $nacion) {
			$arrayNaciones[$nacion->getId()] = $nacion->getNacionOriginaria();
		}	
		// OTRO VIVE CON
		// $viveConOtro = $em->getRepository('SieAppWebBundle:rude_vive')->findOneBy(array('rude'=>$rude, 'medioTransporteTipo'=>14)); //PENDIENTE
		$viveConOtro = '';
		
		//viveCon
		$apoderados = $em->getRepository('SieAppWebBundle:ApoderadoTipo')->findAll();
		$rudeVive = $em->getRepository('SieAppWebBundle:RudeVive')->findBy(array('rude'=>$rude));
		$arrayRudeViveCon =[];
		foreach ($rudeVive as $ce) {
			$arrayRudeViveCon[] = $ce->getViveCon()->getId();
		}
		$rudeViveConOtro = $em->getRepository('SieAppWebBundle:RudeVive')->findBy(array('rude'=>$rude,'viveCon'=>14)); //dump($rudeViveConOtro);die;
		$viveConOtro = ($rudeViveConOtro) ?  $rudeViveConOtro[0]->getViveOtro() : $viveConOtro  ;
		//PARIENTE CON DISCAPACIDA
		
		$rudeParienteDiscapacidad = $em->getRepository('SieAppWebBundle:RudeParienteDiscapacidad')->findBy(array('rude'=>$rude));
		
		//dump($rudeParienteDiscapacidad[0]->getParienteTipo()->getId());die;
		$apoderadoTipo = $em->getRepository('SieAppWebBundle:ApoderadoTipo')->findAll();
		$arrayApoderadoDiscapacidad = [];
		foreach ($apoderadoTipo as $apo) {
			$arrayApoderadoDiscapacidad[$apo->getId()] = $apo->getApoderado();
		}//dump($cantidaPariente);die;
		//Recuperamos valores de los parientes con discapacidad	
		$cantidaPariente = count($rudeParienteDiscapacidad);
		
		if($cantidaPariente==0){
			$apoderadoDiscapacidad0=0;
			$apoderadoDiscapacidad1=0;
			$apoderadoDiscapacidad2=0;
			$discapacidad0=0;
			$discapacidad1=0;
			$discapacidad2=0;
		}
		elseif($cantidaPariente==1){
			$apoderadoDiscapacidad0=$rudeParienteDiscapacidad[0]->getParienteTipo()->getId();
			$apoderadoDiscapacidad1=0;
			$apoderadoDiscapacidad2=0;
			$discapacidad0=$rudeParienteDiscapacidad[0]->getDiscapacidadTipo()->getId();
			$discapacidad1=0;
			$discapacidad2=0;
		}
		elseif($cantidaPariente==2){
			$apoderadoDiscapacidad0=$rudeParienteDiscapacidad[0]->getParienteTipo()->getId();
			$apoderadoDiscapacidad1=$rudeParienteDiscapacidad[1]->getParienteTipo()->getId();
			$apoderadoDiscapacidad2=0;
			$discapacidad0=$rudeParienteDiscapacidad[0]->getDiscapacidadTipo()->getId();
			$discapacidad1=$rudeParienteDiscapacidad[1]->getDiscapacidadTipo()->getId();;
			$discapacidad2=0;
		
		}
		else{

			$apoderadoDiscapacidad0=$rudeParienteDiscapacidad[0]->getParienteTipo()->getId();
			$apoderadoDiscapacidad1=$rudeParienteDiscapacidad[1]->getParienteTipo()->getId();
			$apoderadoDiscapacidad2=$rudeParienteDiscapacidad[2]->getParienteTipo()->getId();
			$discapacidad0=$rudeParienteDiscapacidad[0]->getDiscapacidadTipo()->getId();
			$discapacidad1=$rudeParienteDiscapacidad[1]->getDiscapacidadTipo()->getId();
			$discapacidad2=$rudeParienteDiscapacidad[2]->getDiscapacidadTipo()->getId();
			
		}
	
		// CENTROS DE SALUD
		$centros = $em->getRepository('SieAppWebBundle:CentroSaludTipo')->findAll();
		$centrosEstudiante = $em->getRepository('SieAppWebBundle:RudeCentroSalud')->findBy(array('rude'=>$rude));
		$arrayCentros = [];
		foreach ($centrosEstudiante as $ce) {
			$arrayCentros[] = $ce->getCentroSaludTipo()->getId();
		}

		// SERVICIOS BASICOS
		$servicios = $em->getRepository('SieAppWebBundle:ServicioBasicoTipo')->findAll();
		foreach ($servicios as $s) {
			$servicioEstudiante = $em->getRepository('SieAppWebBundle:RudeServicioBasico')->findOneBy(array('rude'=>$rude, 'servicioBasicoTipo'=>$s->getId()));
			if($servicioEstudiante){
				$tiene = true;
			}else{
				$tiene = false;
			}
			switch ($s->getId()) {
				case 1: $agua = $tiene; break;
				case 2: $banio = $tiene; break;
				case 3: $alcantarillado = $tiene; break;
				case 4: $energiaElectrica = $tiene; break;
				case 5: $recojoBasura = $tiene; break;
			}
		}

		// ACCESO A INTERNET
		$accesoInternet = $em->getRepository('SieAppWebBundle:AccesoInternetTipo')->findAll();
		$accesoInternetEstudiante = $em->getRepository('SieAppWebBundle:RudeAccesoInternet')->findBy(array('rude'=>$rude));
		$arrayAccesoInternet = [];
		foreach ($accesoInternetEstudiante as $aie) {
			$arrayAccesoInternet[] = $aie->getAccesoInternetTipo()->getId();
		}

		// MESES TRABAJO
		$meses = $em->getRepository('SieAppWebBundle:MesTipo')->findAll();
		$mesesEstudiante = $em->getRepository('SieAppWebBundle:RudeMesesTrabajados')->findBy(array('rude'=>$rude));
		$arrayMeses = [];
		foreach ($mesesEstudiante as $me) {
			$arrayMeses[] = $me->getMesTipo()->getId();
		}

		// ACTIVIDADES DEL ESTUDIANTE
		$actividades = $em->getRepository('SieAppWebBundle:ActividadTipo')->findAll();
		$actividadesEstudiante = $em->getRepository('SieAppWebBundle:RudeActividad')->findBy(array('rude'=>$rude));
		$arrayActividades = [];
		foreach ($actividadesEstudiante as $ae) {
			$arrayActividades[] = $ae->getActividadTipo()->getId();
		}

		// OTRA ACTIVIDAD
		$actividadOtro = $em->getRepository('SieAppWebBundle:RudeActividad')->findOneBy(array('rude'=>$rude, 'actividadTipo'=>3));

		// TURNO TRABAJO
		$turnos = $em->getRepository('SieAppWebBundle:TurnoTipo')->findAll();
		$turnosEstudiante = $em->getRepository('SieAppWebBundle:RudeTurnoTrabajo')->findBy(array('rude'=>$rude));
		$arrayTurnos = [];
		foreach ($turnosEstudiante as $te) {
			$arrayTurnos[] = $te->getTurnoTipo()->getId();
		}

		// TIPOS DE PAGO
		$pagosEstudiante = $em->getRepository('SieAppWebBundle:RudeRecibioPago')->findBy(array('rude'=>$rude));
		$arrayPagos = [];
		foreach ($pagosEstudiante as $pe) {
			$arrayPagos[] = $pe->getPagoTipo()->getId();
		}

		// COMO LLEGA ESTUDAINTE MEDIO TRANSPORTE
		$medioTransporteEstudiante = $em->getRepository('SieAppWebBundle:RudeMedioTransporte')->findBy(array('rude'=>$rude));
		$arrayMedioTransporte = [];
		foreach ($medioTransporteEstudiante as $te) {
			$arrayMedioTransporte[] = $te->getMedioTransporteTipo()->getId();
		}

		// OTRO MEDIO DE TRANSPORTE
		$medioTransporteOtro = $em->getRepository('SieAppWebBundle:RudeMedioTransporte')->findOneBy(array('rude'=>$rude, 'medioTransporteTipo'=>3));

		// COMO LLEGA ESTUDAINTE MEDIO TRANSPORTE
		$abandonoEstudiante = $em->getRepository('SieAppWebBundle:RudeAbandono')->findBy(array('rude'=>$rude));
		$arrayAbandono = [];
		foreach ($abandonoEstudiante as $ae) {
			$arrayAbandono[] = $ae->getAbandonoTipo()->getId();
		}
		//$i = $em->getRepository('SieAppWebBundle:IdiomaTipo')->findBy(array('esVigente'=>true));
		//dump($i);die;
		// ABANDONO OTRO
		$abandonoOtro = $em->getRepository('SieAppWebBundle:RudeAbandono')->findOneBy(array('rude'=>$rude, 'abandonoTipo'=>12));
	
		$form = $this->createFormBuilder($rude)
					->add('id', 'hidden')
					// 4.1 IDIOMA Y PERTENENCIA
					->add('idiomaNines', 'entity', array(
							'class' => 'SieAppWebBundle:IdiomaTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('it')
										->where('it.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'idioma_tipo'))
										->orderBy('it.idioma', 'ASC')
								;
							},
							'empty_value' => 'Seleccionar...',
							'required'=>true,
							'data'=>$em->getReference('SieAppWebBundle:IdiomaTipo', ($idiomaNines)?$idiomaNines->getIdiomaTipo()->getId():0),
							'mapped'=>false
						))
					->add('idioma1', 'entity', array(
							'class' => 'SieAppWebBundle:IdiomaTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('it')
										->where('it.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'idioma_tipo'))
										->orderBy('it.idioma', 'ASC')
								;
							},
							'empty_value' => 'Seleccionar...',
							'required'=>true,
							'data'=>$em->getReference('SieAppWebBundle:IdiomaTipo', ($idiomaMasFrecuente)?$idiomaMasFrecuente->getIdiomaTipo()->getId():0),
							'mapped'=>false
						))
					->add('idioma2', 'entity', array(
							'class' => 'SieAppWebBundle:IdiomaTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('it')
										->where('it.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'idioma_tipo'))
										->orderBy('it.idioma', 'ASC')
								;
							},
							'empty_value' => 'Seleccionar...',
							'required'=>false,
							'data'=>$em->getReference('SieAppWebBundle:IdiomaTipo', ($idiomaMedioFrecuente)?$idiomaMedioFrecuente->getIdiomaTipo()->getId():0),
							'mapped'=>false
						))
					->add('idioma3', 'entity', array(
							'class' => 'SieAppWebBundle:IdiomaTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('it')
										->where('it.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'idioma_tipo'))
										->orderBy('it.idioma', 'ASC')
								;
							},
							'empty_value' => 'Seleccionar...',
							'required'=>false,
							'data'=>$em->getReference('SieAppWebBundle:IdiomaTipo', ($idiomaMenosFrecuente)?$idiomaMenosFrecuente->getIdiomaTipo()->getId():0),
							'mapped'=>false
						))
					->add('nacionOriginariaTipo', 'entity', array(
							'class' => 'SieAppWebBundle:NacionOriginariaTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('na')
										->where('na.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'nacion_originaria_tipo'))
										->orderBy('na.nacionOriginaria', 'ASC')
								;
							},
							'empty_value' => 'Seleccionar...',
							'multiple'=>false,
							'property'=>'nacionOriginaria',
							'required'=>true
						))
					// 3.2.1 VIVE CON
					->add('viveCon', 'entity', array(
							'class' => 'SieAppWebBundle:ApoderadoTipo',
							'query_builder' => function (EntityRepository $e) use ($rude)
							{
								return $e->createQueryBuilder('cst')
										->where('cst.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'apoderado_tipo'))
										->orderBy('cst.id', 'ASC')
								;
							},
							'empty_value' => 'Seleccionar...',
							'multiple'=>true,
							'property'=>'apoderado',
							'required'=>true,
							
							'data'=>$em->getRepository('SieAppWebBundle:ApoderadoTipo')->findBy(array('id'=>$arrayRudeViveCon)),
							'mapped'=>false
						))
						

						
					->add('viveConOtro', 'text', array('mapped'=>false, 'required'=>false, 'data'=> $viveConOtro ))		
					// 3.2.2 PARIENTES CON DISCAPACIDAD
					->add('tieneParientesDiscapacidad', 'choice', array(
					        'choice_attr' => function($val, $key, $index) {
					            return ['class' => 'option_tieneParientesDiscapacidad'];
					        },
							'choices'=>array(true=>'Si', false=>'No'),
							'required'=>false,
							'multiple'=>false,
							'empty_value'=>false,
							'expanded'=>true
						))			
						
					->add('parienteDiscapacidad0', 'entity', array(
						'class' => 'SieAppWebBundle:ApoderadoTipo',
						'query_builder' => function (EntityRepository $e) use ($rude){
							return $e->createQueryBuilder('cst')
									->where('cst.id in (:ids)')
									->setParameter('ids', $this->obtenerCatalogo($rude, 'apoderado_tipo'))
									->orderBy('cst.id', 'ASC')
							;
						},
						'empty_value' => 'Seleccionar...',
						'data'=>$em->getReference('SieAppWebBundle:ApoderadoTipo', $apoderadoDiscapacidad0),						
						'mapped'=>false,
						'required'=>false
						))
					->add('parienteDiscapacidad1', 'entity', array(
						'class' => 'SieAppWebBundle:ApoderadoTipo',
						'query_builder' => function (EntityRepository $e) use ($rude){
							return $e->createQueryBuilder('cst')
									->where('cst.id in (:ids)')
									->setParameter('ids', $this->obtenerCatalogo($rude, 'apoderado_tipo'))
									->orderBy('cst.id', 'ASC')
							;
						},
						'empty_value' => 'Seleccionar...',						
						'data'=>$em->getReference('SieAppWebBundle:ApoderadoTipo', $apoderadoDiscapacidad1),
						'mapped'=>false,
						'required'=>false
						))
					->add('parienteDiscapacidad2', 'entity', array(
						'class' => 'SieAppWebBundle:ApoderadoTipo',
						'query_builder' => function (EntityRepository $e) use ($rude){
							return $e->createQueryBuilder('cst')
									->where('cst.id in (:ids)')
									->setParameter('ids', $this->obtenerCatalogo($rude, 'apoderado_tipo'))
									->orderBy('cst.id', 'ASC')
							;
						},
						'empty_value' => 'Seleccionar...',						
						'data'=>$em->getReference('SieAppWebBundle:ApoderadoTipo', $apoderadoDiscapacidad2),
						'mapped'=>false,
						'required'=>false
						))
					
					->add('discapacidad0', 'entity', array(
						'class' => 'SieAppWebBundle:DiscapacidadTipo',
						'query_builder' => function (EntityRepository $e) use ($rude) {
							return $e->createQueryBuilder('dt')
									->where('dt.id in (:ids)')
									//->setParameter('ids', $this->obtenerCatalogo($rude, 'discapacidad_tipo'));
									->setParameter('ids', $this->obtenerCatalogoTipoDiscapacidad() );
						},
						'required'=>false,
						'empty_value' => 'Seleccionar...',						
						'data'=>$em->getReference('SieAppWebBundle:DiscapacidadTipo', $discapacidad0),						
						'mapped'=>false,
						
					))
					->add('discapacidad1', 'entity', array(
						'class' => 'SieAppWebBundle:DiscapacidadTipo',
						'query_builder' => function (EntityRepository $e) use ($rude) {
							return $e->createQueryBuilder('dt')
									->where('dt.id in (:ids)')
									//->setParameter('ids', $this->obtenerCatalogo($rude, 'discapacidad_tipo'));
									->setParameter('ids', $this->obtenerCatalogoTipoDiscapacidad() );
						},
						'empty_value' => 'Seleccionar...',
						'data'=>$em->getReference('SieAppWebBundle:DiscapacidadTipo', $discapacidad1),
						'mapped'=>false,
						'required'=>false
					))
					->add('discapacidad2', 'entity', array(
						'class' => 'SieAppWebBundle:DiscapacidadTipo',
						'query_builder' => function (EntityRepository $e) use ($rude) {
							return $e->createQueryBuilder('dt')
									->where('dt.id in (:ids)')									
									->setParameter('ids', $this->obtenerCatalogoTipoDiscapacidad() );
						},
						'empty_value' => 'Seleccionar...',
						'data'=>$em->getReference('SieAppWebBundle:DiscapacidadTipo', $discapacidad2),
						'mapped'=>false,
						'required'=>false
					))
										
						
					// 4.2 SALUD DEL ESTUDIANTE
					->add('centroSalud', 'choice', array(
							'choices'=>array(true=>'Si', false=>'No'),
							'required'=>true,
							'multiple'=>false,
							'empty_value'=>false,
							'expanded'=>true
						))
					->add('acudioCentro', 'entity', array(
							'class' => 'SieAppWebBundle:CentroSaludTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('cst')
										->where('cst.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'centro_salud_tipo'))
										->orderBy('cst.id', 'ASC')
								;
							},
							'empty_value' => 'Seleccionar...',
							'multiple'=>true,
							'property'=>'descripcion',
							'required'=>true,
							'data'=>$em->getRepository('SieAppWebBundle:CentroSaludTipo')->findBy(array('id'=>$arrayCentros)),
							'mapped'=>false
						))
					->add('cantidadCentroSaludTipo', 'entity', array(
							'class' => 'SieAppWebBundle:CantidadCentroSaludTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('cs')
										->where('cs.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'cantidad_centro_salud_tipo'))
										->orderBy('cs.id', 'ASC')
								;
							},
							'empty_value' => 'Seleccionar...',
							'required'=>true
						))
					->add('seguroSalud', 'choice', array(
							'choices'=>array(true=>'Si', false=>'No'),
							'required'=>true,
							'multiple'=>false,
							'empty_value'=>false,
							'expanded'=>true
						))
					->add('tieneMedicacionEnCee', 'choice', array(
							'choices'=>array(true=>'Si', false=>'No'),
							'required'=>true,
							'multiple'=>false,
							'empty_value'=>false,
							'expanded'=>true
						))
					// 4.3 ACCESO A SERVICIOS BASICOS
					->add('agua', 'choice', array(
							'choices'=>array(true=>'Si', false=>'No'),
							'required'=>true,
							'multiple'=>false,
							'empty_value'=>false,
							'expanded'=>true,
							'mapped'=>false,
							'data'=>$agua
						))
					->add('banio', 'choice', array(
							'choices'=>array(true=>'Si', false=>'No'),
							'required'=>true,
							'multiple'=>false,
							'empty_value'=>false,
							'expanded'=>true,
							'mapped'=>false,
							'data'=>$banio
						))
					->add('alcantarillado', 'choice', array(
							'choices'=>array(true=>'Si', false=>'No'),
							'required'=>true,
							'multiple'=>false,
							'empty_value'=>false,
							'expanded'=>true,
							'mapped'=>false,
							'data'=>$alcantarillado
						))
					->add('energiaElectrica', 'choice', array(
							'choices'=>array(true=>'Si', false=>'No'),
							'required'=>true,
							'multiple'=>false,
							'empty_value'=>false,
							'expanded'=>true,
							'mapped'=>false,
							'data'=>$energiaElectrica
						))
					->add('recojoBasura', 'choice', array(
							'choices'=>array(true=>'Si', false=>'No'),
							'required'=>true,
							'multiple'=>false,
							'empty_value'=>false,
							'expanded'=>true,
							'mapped'=>false,
							'data'=>$recojoBasura
						))
					->add('viviendaOcupaTipo', 'entity', array(
							'class' => 'SieAppWebBundle:ViviendaOcupaTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('vo')
										->where('vo.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'vivienda_ocupa_tipo'))
										->orderBy('vo.id', 'ASC')
								;
							},
							'empty_value' => 'Seleccionar...',
							'multiple'=>false,
							'property'=>'descripcionViviendaOcupa',
							'required'=>true
						))					
					// 4.5 ACTIVIDAD LABORAL
					->add('trabajoGestionPasada', 'choice', array(
							'choices'=>array(true=>'Si', false=>'No', null=>'Ns/Nr'),
							'required'=>true,
							'multiple'=>false,
							'empty_value'=>null,
							'expanded'=>true
						))
					->add('mesesTrabajados', 'entity', array(
							'class' => 'SieAppWebBundle:MesTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('mt')
										->where('mt.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'mes_tipo'))
										->orderBy('mt.id', 'ASC')
								;
							},
							'multiple'=>true,
							'property'=>'mes',
							'required'=>false,
							'data'=>$em->getRepository('SieAppWebBundle:MesTipo')->findBy(array('id'=>$arrayMeses)),
							'mapped'=>false,
							'expanded'=>false
						))
					->add('actividades', 'entity', array(
							'class' => 'SieAppWebBundle:ActividadTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('at')
										->where('at.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'actividad_tipo'))
										->orderBy('at.id', 'ASC')
								;
							},
							'multiple'=>true,
							'property'=>'descripcionOcupacion',
							'required'=>false,
							'data'=>$em->getRepository('SieAppWebBundle:ActividadTipo')->findBy(array('id'=>$arrayActividades)),
							'mapped'=>false,
							'expanded'=>false
						))
					->add('actividadOtro', 'text', array('mapped'=>false, 'required'=>false, 'data'=> ($actividadOtro)?$actividadOtro->getActividadOtro():''))
					->add('turnosTrabajo', 'entity', array(
							'class' => 'SieAppWebBundle:TurnoTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('tt')
										->where('tt.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'turno_tipo'))
										->orderBy('tt.id', 'ASC')
								;
							},
							'multiple'=>true,
							'property'=>'turno',
							'required'=>false,
							'data'=>$em->getRepository('SieAppWebBundle:TurnoTipo')->findBy(array('id'=>$arrayTurnos)),
							'mapped'=>false,
							'expanded'=>false
						))
					->add('frecuenciaTrabajoTipo', 'entity', array(
							'class' => 'SieAppWebBundle:FrecuenciaTrabajoTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('ftt')
										->where('ftt.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'frecuencia_trabajo_tipo'))
										->orderBy('ftt.id', 'ASC')
								;
							},
							'empty_value' => 'Seleccionar...',
							'multiple'=>false,
							'property'=>'descripcionFrecuenciaTrabajo',
							'required'=>false
						))
					->add('respuestaPago', 'choice', array(
							'choices'=>array(true=>'Si', false=>'No', null=>'Ns/Nr'),
							'required'=>true,
							'multiple'=>false,
							'empty_value'=>null,
							'expanded'=>true
						))
					->add('recibioTipoPago', 'entity', array(
							'class' => 'SieAppWebBundle:PagoTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('pt')
										->where('pt.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'pago_tipo'))
										->orderBy('pt.id', 'ASC')
								;
							},
							'multiple'=>true,
							'property'=>'descripcionPago',
							'required'=>false,
							'data'=>$em->getRepository('SieAppWebBundle:PagoTipo')->findBy(array('id'=>$arrayPagos)),
							'mapped'=>false,
							'expanded'=>false
						))
					->add('medioTransporte', 'entity', array(
							'class' => 'SieAppWebBundle:MedioTransporteTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('mt')
										->where('mt.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'medio_transporte_tipo'))
										->orderBy('mt.id', 'ASC')
								;
							},
							'multiple'=>true,
							'property'=>'descripcionMedioTrasnporte',
							'required'=>true,
							'data'=>$em->getRepository('SieAppWebBundle:MedioTransporteTipo')->findBy(array('id'=>$arrayMedioTransporte)),
							'mapped'=>false,
							'expanded'=>false
						))
					->add('medioTransporteOtro', 'text', array('mapped'=>false, 'required'=>false, 'data'=> ($medioTransporteOtro)?'hola':''))
					->add('tiempoTransporte', 'entity', array(
							'class' => 'SieAppWebBundle:TiempoMaximoTrayectoTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('tmt')
										->where('tmt.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'tiempo_maximo_trayecto_tipo'))
										->orderBy('tmt.id', 'ASC')
								;
							},
							'empty_value' => 'Seleccionar...',
							'multiple'=>false,
							'property'=>'descripcionTiempoMaxTipo',
							'required'=>true,
							'mapped'=>false,
							'data'=>(count($medioTransporteEstudiante) > 0)?$em->getRepository('SieAppWebBundle:TiempoMaximoTrayectoTipo')->find($medioTransporteEstudiante[0]->getTiempoMaximoTrayectoTipo()->getId()):''
						))
					->add('tieneAbandono', 'choice', array(
							'choices'=>array(true=>'Si', false=>'No'),
							'required'=>true,
							'multiple'=>false,
							'empty_value'=>false,
							'expanded'=>true
						))
					->add('abandono', 'entity', array(
							'class' => 'SieAppWebBundle:AbandonoTipo',
							'query_builder' => function (EntityRepository $e) use ($rude){
								return $e->createQueryBuilder('at')
										->where('at.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'abandono_tipo'))
										->orderBy('at.id', 'ASC')
								;
							},
							'multiple'=>true,
							'property'=>'descripcionAbandono',
							'required'=>true,
							'data'=>$em->getRepository('SieAppWebBundle:AbandonoTipo')->findBy(array('id'=>$arrayAbandono)),
							'mapped'=>false,
							'expanded'=>false
						))
					->add('abandonoOtro', 'text', array('mapped'=>false, 'required'=>false, 'data'=> ($abandonoOtro)?$abandonoOtro->getAbandonoOtro():''))

					->getForm();
					
				//dump($form);die;
		return $form;
	}

	/*
	 * Funciones para cargar los combos dependientes via ajax
	 */
	public function listarprovinciasAction($dpto)
	{
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
	public function listarmunicipiosAction($prov)
	{
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

	public function cargarGradoDiscapacidadAction(Request $request)
	{ 
		$idDiscapacidad = $request->get('discapacidad');
		$em = $this->getDoctrine()->getManager();
		// SI LA DISCAPACIDAD ES VISUAL = 10
		if(in_array($idDiscapacidad, array(3,2,5,40))){
		$gradoDiscapacidad = $em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')
			->findBy(array('id'=>array(1,2,7,8)));
			$gradosArray = array();
			foreach ($gradoDiscapacidad as $gd)
			{
				$gradosArray[$gd->getId()] = $gd->getGradoDiscapacidad();
			}	
		}
		
		else if($idDiscapacidad == 10) //visual
		{
			$gradoDiscapacidad = $em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')
			->findBy(array('id'=>array(5,6)));
			$gradosArray = array();
			foreach ($gradoDiscapacidad as $gd)
			{
				$gradosArray[$gd->getId()] = $gd->getGradoDiscapacidad();
			}
		}
		
		else if($idDiscapacidad == 4) //multiple
		{
			$gradoDiscapacidad = $em->getRepository('SieAppWebBundle:DiscapacidadTipo')
			->findBy(array('id'=>array(41,35,36,37,38,39)));
			foreach ($gradoDiscapacidad as $gd)
			{
				$gradosArray[$gd->getId()] = $gd->getOrigendiscapacidad();
			}
		}
		
		else
		{
			$gradoDiscapacidad = null;
		}
		$response = new JsonResponse();
		return $response->setData(array('gradosDiscapacidad' => $gradosArray));
		
	}
	public function cargarEstrategiasAction(Request $request)
	{ 
		$idTalento = $request->get('talento'); 
		$em = $this->getDoctrine()->getManager();

	 		$estrategias = $em->getRepository('SieAppWebBundle:EstrategiaAtencionIntegralTipo')
			->findBy(array('obs'=> $idTalento));
			$estrategiasArray = array();
			foreach ($estrategias as $gd)
			{
				$estrategiasArray[$gd->getId()] = $gd->getEstrategiaatencion();
			}
			//dump($estrategias);die;
		$response = new JsonResponse();
		return $response->setData(array('estrategiasIntegrales' => $estrategiasArray));
		
	}
	/**
	 * DATOS SOCIOECONOMICOS
	 */
	public function saveFormSocioeconomicosAction(Request $request)
	{
		$form = $request->get('form');
		$em = $this->getDoctrine()->getManager();
		$rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['id']);

		/**
		 * REGISTRO DE IDIOMAs
		 */
		// ELIMINAMOS LOS IDIOMAS
		$eliminarIdiomas = $em->createQueryBuilder()
						->delete('')
						->from('SieAppWebBundle:RudeIdioma','rid')
						->where('rid.rude = :rude')
						->setParameter('rude', $rude)
						->getQuery()
						->getResult();
		
		// REGISTRAMOS LOS IDIOMAS
		$rudeIdioma = new RudeIdioma();
		if($form['idiomaNines']){
			$rudeIdioma->setRude($rude);
			$rudeIdioma->setIdiomaTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idiomaNines'] ? $form['idiomaNines'] : 0));
			$rudeIdioma->setHablaTipo($em->getRepository('SieAppWebBundle:HablaTipo')->find(1));
			$rudeIdioma->setFechaRegistro(new \DateTime('now'));
			$rudeIdioma->setFechaModificacion(new \DateTime('now'));
			$em->persist($rudeIdioma);
			$em->flush();
		}
		
		$rudeIdioma = new RudeIdioma();
		if($form['idioma1']){
			$rudeIdioma->setRude($rude);
			$rudeIdioma->setIdiomaTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idioma1'] ? $form['idioma1'] : 0));
			$rudeIdioma->setHablaTipo($em->getRepository('SieAppWebBundle:HablaTipo')->find(2));//habla FRECUENTE
			$rudeIdioma->setFechaRegistro(new \DateTime('now'));
			$rudeIdioma->setFechaModificacion(new \DateTime('now'));
			$em->persist($rudeIdioma);
			$em->flush();
		}
		$rudeIdioma = new RudeIdioma();
		if($form['idioma2']){
			$rudeIdioma->setRude($rude);
			$rudeIdioma->setIdiomaTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idioma2'] ? $form['idioma2'] : 0));
			$rudeIdioma->setHablaTipo($em->getRepository('SieAppWebBundle:HablaTipo')->find(3));//habla MEDIANAMENTE FRECUENTE
			$rudeIdioma->setFechaRegistro(new \DateTime('now'));
			$rudeIdioma->setFechaModificacion(new \DateTime('now'));
			$em->persist($rudeIdioma);
			$em->flush();
		}
		$rudeIdioma = new RudeIdioma();
		if($form['idioma3']){
			$rudeIdioma->setRude($rude);
			$rudeIdioma->setIdiomaTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idioma3'] ? $form['idioma3'] : 0));
			$rudeIdioma->setHablaTipo($em->getRepository('SieAppWebBundle:HablaTipo')->find(4));//habla MENOS FRECUENTE
			$rudeIdioma->setFechaRegistro(new \DateTime('now'));
			$rudeIdioma->setFechaModificacion(new \DateTime('now'));
			$em->persist($rudeIdioma);
			$em->flush();
		}
		$rude->setNacionOriginariaTipo($em->getRepository('SieAppWebBundle:NacionOriginariaTipo')->find($form['nacionOriginariaTipo']));
		
		$eliminarViveCon = $em->createQueryBuilder()
						->delete('')
						->from('SieAppWebBundle:RudeVive','rv')
						->where('rv.rude = :rude')
						->setParameter('rude', $rude)
						->getQuery()
						->getResult();
					
		// REGISTRAMOS CON QUIEN VIVE
		
		if(isset($form['viveCon']))
		{
			$viveConOpcion = $form['viveCon'];
			for ($i=0; $i < count($viveConOpcion); $i++)
			{  
				$viveCon = new RudeVive();
				$viveCon->setRude($rude);
				if ($viveConOpcion[$i]==='14'){					
					$viveCon->setViveCon($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($viveConOpcion[$i]));
					$viveCon->setViveOtro($form['viveConOtro']);
				}else{
					$viveCon->setViveCon($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($viveConOpcion[$i]));
				}
				$viveCon->setViveCon($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($viveConOpcion[$i]));
				$viveCon->setFechaRegistro(new \DateTime('now'));
				$em->persist($viveCon);
				$em->flush();
			}
		}
		
		//PARIENTES CON DISCAPACIDAD
		
		
		$rude->setTieneParientesDiscapacidad($form['tieneParientesDiscapacidad']);
		$eliminarParientesDiscapacidad = $em->createQueryBuilder()
											->delete('')
	    									->from('SieAppWebBundle:RudeParienteDiscapacidad','rpd')
											->where('rpd.rude = :rude')
											->setParameter('rude', $rude)
											->getQuery()
											->getResult();
											
		if($form['tieneParientesDiscapacidad']=='1'){ 
			
			
			//$rudeParientesDiscapacidad= new RudeParienteDiscapacidad();
			//$rudeParientesDiscapacidad->setRude($rude);
			if($form['parienteDiscapacidad0'] and $form['discapacidad0'] ){
				$rudeParientesDiscapacidad= new RudeParienteDiscapacidad();
				$rudeParientesDiscapacidad->setRude($rude); 
				$rudeParientesDiscapacidad->setParienteTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['parienteDiscapacidad0']));
				$rudeParientesDiscapacidad->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->find($form['discapacidad0']));
				//$rudeParientesDiscapacidad->setNroCarnet($form['carnet0'] ? $form['carnet0']: '' );
				$rudeParientesDiscapacidad->setFechaRegistro(new \DateTime('now'));
				$em->persist($rudeParientesDiscapacidad);
			    $em->flush();
			}
			if($form['parienteDiscapacidad1'] and $form['discapacidad1']){
				$rudeParientesDiscapacidad= new RudeParienteDiscapacidad();
				$rudeParientesDiscapacidad->setRude($rude);
				$rudeParientesDiscapacidad->setParienteTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['parienteDiscapacidad1']));
				$rudeParientesDiscapacidad->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->find($form['discapacidad1']));
				//$rudeParientesDiscapacidad->setNroCarnet($form['carnet1'] ? $form['carnet1']: '');
				$rudeParientesDiscapacidad->setFechaRegistro(new \DateTime('now'));
				$em->persist($rudeParientesDiscapacidad);
			    $em->flush();
			}
			if($form['parienteDiscapacidad2'] and $form['discapacidad2']){
				$rudeParientesDiscapacidad= new RudeParienteDiscapacidad();
				$rudeParientesDiscapacidad->setRude($rude);
				$rudeParientesDiscapacidad->setParienteTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['parienteDiscapacidad2']));
				$rudeParientesDiscapacidad->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->find($form['discapacidad2']));
				//$rudeParientesDiscapacidad->setNroCarnet($form['carnet2'] ? $form['carnet2']: '');
				$rudeParientesDiscapacidad->setFechaRegistro(new \DateTime('now'));
				$em->persist($rudeParientesDiscapacidad);
			    $em->flush();
			}
		}
		//3.3 SEGURO DE SALUD
		$rude->setSeguroSalud($form['seguroSalud']);
		/**
		 * ACUDIO CENTRO
		 */
		// ELIMINAMOS LOS CENTROS A LOS QUE ACUDIO
		$eliminarCentros = $em->createQueryBuilder()
						->delete('')
						->from('SieAppWebBundle:RudeCentroSalud','rcs')
						->where('rcs.rude = :rude')
						->setParameter('rude', $rude)
						->getQuery()
						->getResult();
		// REGISTRAMOS LOS CENTROS
		if(isset($form['acudioCentro']))
		{
			$acudioCentro = $form['acudioCentro'];
			for ($i=0; $i < count($acudioCentro); $i++)
			{ 
				$centroEstudiante = new RudeCentroSalud();
				$centroEstudiante->setRude($rude);
				$centroEstudiante->setCentroSaludTipo($em->getRepository('SieAppWebBundle:CentroSaludTipo')->find($acudioCentro[$i]));
				$centroEstudiante->setFechaRegistro(new \DateTime('now'));
				$em->persist($centroEstudiante);
				$em->flush();
			}
		}
		$rude->setTieneMedicacionEnCee($form['tieneMedicacionEnCee']);
		// Registro paso 3
		if($rude->getRegistroFinalizado() < 3){
			$rude->setRegistroFinalizado(3);
		}
		$em->flush();
		$response = new JsonResponse();
		return $response->setData(['msg'=>true]);
	}

	// tab4 CARACTERISTICAS PARTICULARES
	public function createFormCaracteristicaParticular($rude)
	{ 
		$em = $this->getDoctrine()->getManager();

		// DISCAPACIDAD DEL ESTUDIANTE
		
		$discapacidadEstudiante = $em->getRepository('SieAppWebBundle:RudeDiscapacidadGrado')->findOneBy(array('rude'=>$rude->getId()));
		
		
		$banioAdaptado = '';
		// SERVICIOS BASICOS
		$servicios = $em->getRepository('SieAppWebBundle:ServicioBasicoTipo')->findAll();
		foreach ($servicios as $s)
		{
			$servicioEstudiante = $em->getRepository('SieAppWebBundle:RudeServicioBasico')->findOneBy(array('rude'=>$rude, 'servicioBasicoTipo'=>$s->getId()));
			$tiene = false;
			if($servicioEstudiante){
				$tiene = true;
			}
			switch ($s->getId()) {
				case 1: $agua = $tiene; break;
				case 2: $banio = $tiene; break;
				case 3: $alcantarillado = $tiene; break;
				case 4: $energiaElectrica = $tiene; break;
				case 5: $recojoBasura = $tiene; break;
				case 6: $banioAdaptado = $tiene; break;
			}
		}

		$telefono='';
		// TECNOLOGIAS DE COMUNICACION
		$mediosComunicacion = $em->getRepository('SieAppWebBundle:MediosComunicacionTipo')->findAll();
		foreach ($mediosComunicacion as $s)
		{
			$medioComunicacionEstudiante = $em->getRepository('SieAppWebBundle:RudeMediosComunicacion')->findOneBy(array('rude'=>$rude, 'mediosComunicacionTipo'=>$s->getId()));
			$tiene2 = false;
			if($medioComunicacionEstudiante)
			{
				$tiene2 = true;
			}
			
			switch ($s->getId())
			{
				case 1: $radio = $tiene2; break;
				case 2: $televisor = $tiene2; break;
				case 3: $computadora = $tiene2; break;
				case 4: $internet = $tiene2; break;
				case 5: $celular = $tiene2; break;
				case 6: $telefono = $tiene2; break;
			}
		}

		// DISCAPACIDAD ORIGIN
		$discapacidadOrigen_rude = $em->getRepository('SieAppWebBundle:RudeDiscapcidadOrigen')->findOneBy(array('rude'=>$rude->getId()));
		// DISCAPACIDAD TIPO Y GRADO Y PORCENTAJE 
		
		$discapacidadTipoGradoPorcentaje_rude = $em->getRepository('SieAppWebBundle:RudeDiscapacidadGrado')->findOneBy(array('rude'=>$rude->getId()));
		
		// DIFICULTAD APRENDIZAJE
		$dificultadAprendizaje_rude = $em->getRepository('SieAppWebBundle:RudeDificultadAprendizaje')->findBy(array('rude'=>$rude));
		$dificultadAprendizaje_array = [];
		foreach ($dificultadAprendizaje_rude as $i)
		{
			$dificultadAprendizaje_array[] = $i->getDificultadAprendizajeTipo()->getId();
		}

		//TALENTO EXTRAORDINARIO
		$talentoextraordinario_rude = $em->getRepository('SieAppWebBundle:RudeTalentoExtraordinario')->findOneBy(array('rude'=>$rude->getId()));


		// ESTRATEGIA DE ATENCION INTEGRAL
		$estrategiaAtencionIntegral_rude = $em->getRepository('SieAppWebBundle:RudeEstrategiaAtencionIntegral')->findBy(array('rude'=>$rude));
		$estrategiaAtencionIntegral_array = [];
		foreach ($estrategiaAtencionIntegral_rude as $i)
		{
			$estrategiaAtencionIntegral_array[] = $i->getEstrategiaAtencionIntegralTipo()->getId();
		}
//dump($estrategiaAtencionIntegral_array);die;
		// ACCESO A INTERNET
		$accesoInternet = $em->getRepository('SieAppWebBundle:AccesoInternetTipo')->findAll();
		$accesoInternetEstudiante = $em->getRepository('SieAppWebBundle:RudeAccesoInternet')->findBy(array('rude'=>$rude));
		$arrayAccesoInternet = [];
		foreach ($accesoInternetEstudiante as $aie)
		{
			$arrayAccesoInternet[] = $aie->getAccesoInternetTipo()->getId();
		}
		//dump($discapacidadTipoGradoPorcentaje_rude);die;
		if($discapacidadTipoGradoPorcentaje_rude){ 
			if($discapacidadTipoGradoPorcentaje_rude->getDiscapacidadTipo()->getId()==4){
				$gradosArray = array();
				$gradoDiscapacidad = $em->getRepository('SieAppWebBundle:DiscapacidadTipo')->findBy(array('id'=>array(41,35,36,37,38,39))); //array(2,7,9,99,32,33,34,8,35,36,37,38,39)
				foreach ($gradoDiscapacidad as $gd)
					{
						$gradosArray[] = $gd->getId();
					}
				$dataGrado = $discapacidadTipoGradoPorcentaje_rude->getDiscapacidadOtroGrado();
				$entity='DiscapacidadTipo';
			}else{
				$gradosArray = array();
				if($discapacidadEstudiante)
				{
					// SI LA DISCAPACDIDAD ES VISUAL ENTONCES SOLO MOSTRAMOS CIEETOS GRADOS DE DISCAPACIDAD
					if($discapacidadEstudiante->getDiscapacidadTipo()->getId() == 10)
					{
						$gradoDiscapacidad = $em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->findBy(array('id'=>array(6,5)));
					}
					else
					{
						$gradoDiscapacidad = $em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->findBy(array('id'=>array(1,2,7,8)));
					}

					foreach ($gradoDiscapacidad as $gd)
					{
						$gradosArray[] = $gd->getId();
					}
					//dump($gradosArray);die;
				}
				$dataGrado = $discapacidadTipoGradoPorcentaje_rude->getGradoDiscapacidadTipo();
				$entity='GradoDiscapacidadTipo';
			}
		}else{ 
			$gradosArray = array();
			$gradoDiscapacidad = $em->getRepository('SieAppWebBundle:DiscapacidadTipo')->findBy(array('id'=>array(999)));
			foreach ($gradoDiscapacidad as $gd)
					{
						$gradosArray[] = $gd->getId();
					}
			$dataGrado=0;
			$entity='DiscapacidadTipo';
		}
		
		//dump($discapacidadTipoGradoPorcentaje_rude->getDiscapacidadOtroGrado()->getId());die;
			
		$form = $this->createFormBuilder($rude)
			->add('id', 'hidden')
			->add('esEducacionEnCasa', 'choice', array(
					'choices'=>array(true=>'Si', false=>'No'),
					'required'=>true,
					'multiple'=>false,
					'empty_value'=>null,
					'expanded'=>true,
					'data' => $rude->getEsEducacionEnCasa(),
				))
			->add('discapacidadOrigen', 'entity', array(
					'class' => 'SieAppWebBundle:DiscapacidadOrigenTipo',
					'query_builder' => function (EntityRepository $e) use ($rude) {
						return $e->createQueryBuilder('dt')
								->where('dt.id in (:ids)')								
								->setParameter('ids', $this->obtenerCatalogoDiscapacidadOrigen() );
					},
					'empty_value' => 'Seleccionar...',
					'required' => false,
					'data'=>($discapacidadOrigen_rude)?$discapacidadOrigen_rude->getDiscapacidadOrigenTipo():'',
					'mapped'=>false
				))
			
			->add('discapacidad', 'entity', array(
					'class' => 'SieAppWebBundle:DiscapacidadTipo',
					'query_builder' => function (EntityRepository $e) use ($rude) {
						return $e->createQueryBuilder('dt')
								->where('dt.id in (:ids)')								
								->setParameter('ids', $this->obtenerCatalogoTipoDiscapacidad() );
					},
					'empty_value' => 'Seleccionar...',					
					'required' => false,					
					'data'=>isset($discapacidadTipoGradoPorcentaje_rude)?$discapacidadTipoGradoPorcentaje_rude->getDiscapacidadTipo():'',
					'mapped'=>false
				))
			->add('gradoDiscapacidad', 'entity', array(
				'class' => 'SieAppWebBundle:'.$entity,
				'query_builder' => function (EntityRepository $e) use ($gradosArray) {
					return $e->createQueryBuilder('gdt')
							->where('gdt.id in (:ids)')
							->setParameter('ids', $gradosArray);
				},
				'empty_value' => 'Seleccionar...',					
				'required' => false,
				'data'=>$dataGrado,
				'mapped'=>false,
				
			))				
			->add('otroGrado', 'text', array('mapped'=>false, 
				'required'=>false,
				'data'=>($discapacidadTipoGradoPorcentaje_rude)?$discapacidadTipoGradoPorcentaje_rude->getGradoOtro():'',))		
			->add('porcentaje', 'text', array(
				'required' => false, 
				'data'=>($discapacidadTipoGradoPorcentaje_rude)?$discapacidadTipoGradoPorcentaje_rude->getPorcentaje():'',
				'mapped'=>false, 
				'max_length'=> 3,
			))
			->add('dificultadAprendizaje', 'entity', array(
					'class' => 'SieAppWebBundle:DificultadAprendizajeTipo',
					'query_builder' => function (EntityRepository $e) use ($rude){
						return $e->createQueryBuilder('cst')
								->where('cst.id in (:ids)')
								->setParameter('ids', $this->obtenerCatalogo($rude, 'dificultad_aprendizaje_tipo'))
								->orderBy('cst.id', 'ASC');
					},
					'empty_value' => 'Seleccionar...',
					'multiple'=>true,
					'property'=>'dificultadaprendizaje',
					'required'=>false,
					'data'=>$em->getRepository('SieAppWebBundle:DificultadAprendizajeTipo')->findBy(array('id'=>$dificultadAprendizaje_array)),
					'mapped'=>false
				))
			->add('talentoExtraordinario', 'entity', array(
				'class' => 'SieAppWebBundle:TalentoExtraordinarioTipo',
				'query_builder' => function (EntityRepository $e) use ($rude) {
					return $e->createQueryBuilder('cst')
							->where('cst.id in (:ids)')
							->setParameter('ids', $this->obtenerCatalogo($rude, 'talento_extraordinario_tipo'))
							->orderBy('cst.id', 'ASC');
							
				},
				'empty_value' => 'Seleccionar...',
				'property'=>'talentoextraordinario',
				'required' => false,
				'data'=>($talentoextraordinario_rude)?$talentoextraordinario_rude->getTalentoExtraordinarioTipo():'',
				'mapped'=>false,
			))
			->add('coeficienteIntelectual', 'text', array(
				'required' => false, 
				'mapped'=>false,
				'data'=>($talentoextraordinario_rude)?$talentoextraordinario_rude->getCoeficienteintelectual():'',
				'max_length'=> 3,
			))
			->add('promedioCalificaciones', 'text', array(
				'required' => false, 
				'mapped'=>false,
				'data'=>($talentoextraordinario_rude)?$talentoextraordinario_rude->getPromediocalificaciones():'',
				'max_length'=> 3,
			))
			
			->add('especificoEn', 'text', array(
				'required' => false, 
				'mapped'=>false,
				'data'=>($talentoextraordinario_rude)?$talentoextraordinario_rude->getEspecificoEn():'',
			))
			->add('talentoOtro', 'text', array(
				'required' => false, 
				'mapped'=>false,
				'data'=>($talentoextraordinario_rude)?$talentoextraordinario_rude->getTalentoOtro():'',
			))
			
			->add('estrategiaAtencionIntegral', 'entity', array(
					'class' => 'SieAppWebBundle:EstrategiaAtencionIntegralTipo',
					'query_builder' => function (EntityRepository $e) use ($rude){
						return $e->createQueryBuilder('cst')
								->where('cst.obs in (:ids)')
								->setParameter('ids', [1,2])
								->orderBy('cst.id', 'ASC');
					},
					'empty_value' => 'Seleccionar...',
					'multiple'=>true,
					'property'=>'estrategiaatencion',
					'required'=>false,
					'data'=>$em->getRepository('SieAppWebBundle:EstrategiaAtencionIntegralTipo')->findBy(array('id'=>$estrategiaAtencionIntegral_array)),
					'mapped'=>false
			))
			->add('estrategiaOtro', 'text', array(
				'required' => false, 
				'mapped'=>false,
				//'data'=>($talentoextraordinario_rude)?$talentoextraordinario_rude->getPromediocalificaciones():'',
			))

			->add('agua', 'choice', array(
					'choices'=>array(true=>'Si', false=>'No'),
					'required'=>true,
					'multiple'=>false,
					'empty_value'=>false,
					'expanded'=>true,
					'mapped'=>false,
					'data'=>$agua
				))
			->add('banio', 'choice', array(
					'choices'=>array(true=>'Si', false=>'No'),
					'required'=>true,
					'multiple'=>false,
					'empty_value'=>false,
					'expanded'=>true,
					'mapped'=>false,
					'data'=>$banio
				))
			->add('alcantarillado', 'choice', array(
					'choices'=>array(true=>'Si', false=>'No'),
					'required'=>true,
					'multiple'=>false,
					'empty_value'=>false,
					'expanded'=>true,
					'mapped'=>false,
					'data'=>$alcantarillado
				))
			->add('energiaElectrica', 'choice', array(
					'choices'=>array(true=>'Si', false=>'No'),
					'required'=>true,
					'multiple'=>false,
					'empty_value'=>false,
					'expanded'=>true,
					'mapped'=>false,
					'data'=>$energiaElectrica
				))
			->add('recojoBasura', 'choice', array(
					'choices'=>array(true=>'Si', false=>'No'),
					'required'=>true,
					'multiple'=>false,
					'empty_value'=>false,
					'expanded'=>true,
					'mapped'=>false,
					'data'=>$recojoBasura
				))
			->add('banioAdaptado', 'choice', array(
					'choices'=>array(true=>'Si', false=>'No'),
					'required'=>true,
					'multiple'=>false,
					'empty_value'=>false,
					'expanded'=>true,
					'mapped'=>false,
					'data'=>$banioAdaptado
				))

			->add('radio', 'choice', array(
					'choices'=>array(true=>'Si', false=>'No'),
					'required'=>true,
					'multiple'=>false,
					'empty_value'=>false,
					'expanded'=>true,
					'mapped'=>false,
					'data'=>$radio
				))
			->add('televisor', 'choice', array(
					'choices'=>array(true=>'Si', false=>'No'),
					'required'=>true,
					'multiple'=>false,
					'empty_value'=>false,
					'expanded'=>true,
					'mapped'=>false,
					'data'=>$televisor
				))
			->add('computadora', 'choice', array(
					'choices'=>array(true=>'Si', false=>'No'),
					'required'=>true,
					'multiple'=>false,
					'empty_value'=>false,
					'expanded'=>true,
					'mapped'=>false,
					'data'=>$computadora
				))
			->add('internet', 'choice', array(
					'choices'=>array(true=>'Si', false=>'No'),
					'required'=>true,
					'multiple'=>false,
					'empty_value'=>false,
					'expanded'=>true,
					'mapped'=>false,
					'data'=>$internet
				))
			->add('celular', 'choice', array(
					'choices'=>array(true=>'Si', false=>'No'),
					'required'=>true,
					'multiple'=>false,
					'empty_value'=>false,
					'expanded'=>true,
					'mapped'=>false,
					'data'=>$celular
				))
			->add('telefono', 'choice', array(
					'choices'=>array(true=>'Si', false=>'No'),
					'required'=>true,
					'multiple'=>false,
					'empty_value'=>false,
					'expanded'=>true,
					'mapped'=>false,
					'data'=>$telefono,
				))

			->add('accesoInternet', 'entity', array(
					'class' => 'SieAppWebBundle:AccesoInternetTipo',
					'query_builder' => function (EntityRepository $e) use ($rude){
						return $e->createQueryBuilder('ai')
								->where('ai.id in (:ids)')
								->setParameter('ids', $this->obtenerCatalogo($rude, 'acceso_internet_tipo'))
								->orderBy('ai.id', 'ASC');
					},
					'empty_value' => 'Seleccionar...',
					'multiple'=>true,
					'property'=>'descripcionAccesoInternet',
					'required'=>true,
					'data'=>$em->getRepository('SieAppWebBundle:AccesoInternetTipo')->findBy(array('id'=>$arrayAccesoInternet)),
					'mapped'=>false
				))
			->add('frecuenciaUsoInternetTipo', 'entity', array(
					'class' => 'SieAppWebBundle:FrecuenciaUsoInternetTipo',
					'query_builder' => function (EntityRepository $e) use ($rude){
						return $e->createQueryBuilder('fi')
								->where('fi.id in (:ids)')
								->setParameter('ids', $this->obtenerCatalogo($rude, 'frecuencia_uso_internet_tipo'))
								->orderBy('fi.id', 'ASC');
					},
					'empty_value' => 'Seleccionar...',
					'multiple'=>false,
					'property'=>'descripcionFrecuenciaInternet',
					'required'=>false
				))
			
			->getForm();
			
		return $form;
	}

	public function saveFormCaracteristicaParticularAction(Request $request)
	{
		$form = $request->get('form');
		
		
		//dump($form['esEducacionEnCasa']);
		$em = $this->getDoctrine()->getManager();
		$rude = $em->getRepository('SieAppWebBundle:Rude')->findOneById($form['id']);
		//dump($rude);die;
		//4.1.1
		// ELIMINAMOS LOS DATOS DEL ORIGEN DE LA DISCAPACIDAD
		$discapacidadOrigen_rude = $em->getRepository('SieAppWebBundle:RudeDiscapcidadOrigen')->findOneBy(array('rude'=>$rude->getId()));
		if($discapacidadOrigen_rude)
		{
			$em->remove($discapacidadOrigen_rude);
			$em->flush();
		}
		
		if(isset($form['discapacidadOrigen'])){
		// CREAMOS LOS DATOS DEL ORIGEN DE LA DISCAPACIDAD
			if($form['discapacidadOrigen']!=""){
				$discapacidadOrigen_new = new RudeDiscapcidadOrigen();
				$discapacidadOrigen_new->setRude($rude);
				$discapacidadOrigen_new->setFechaRegistro(new \DateTime('now'));
				$discapacidadOrigen_new->setFechaModificacion(new \DateTime('now'));
				$discapacidadOrigen_new->setDiscapacidadOrigenTipo($em->getRepository('SieAppWebBundle:DiscapacidadOrigenTipo')->find($form['discapacidadOrigen']));
				$em->persist($discapacidadOrigen_new);
				$em->flush();		
			}
		}
		//4.1.2
		$rude->setEsEducacionEnCasa($form['esEducacionEnCasa']);
		
	//	$em->persist($rude);	
	//	$em->flush();
	//	dump($rude);die;
		//Registramos el tipo de discapacidad 
		//$rude->setDiscapacidadTipo($form['discapacidad']);
		//4.1.3
		// ELIMINAMOS LOS DATOS DEL GRADO DE LA DISCAPACIDAD
		$discapacidadGrado_rude = $em->getRepository('SieAppWebBundle:RudeDiscapacidadGrado')->findOneBy(array('rude'=>$rude->getId()));
		if($discapacidadGrado_rude)
		{
			$em->remove($discapacidadGrado_rude);
			$em->flush();
		}
		
		// CREAMOS LOS DATOS DEL GRADO DE LA DISCAPACIDAD 
		//dump($form);die;
		if(isset($form['discapacidad'])){
			if($form['discapacidad']!=""){ //dump($form);die;
				$discapacidadGrado_new = new RudeDiscapacidadGrado();
				$discapacidadGrado_new->setRude($rude);
				$discapacidadGrado_new->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->find($form['discapacidad']));
				if($form['discapacidad']==4){//INTELECTUAL FISICO/MOTORA MULTIPLE
					$discapacidadGrado_new->setDiscapacidadOtroGrado($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->find($form['gradoDiscapacidad']));
					$discapacidadGrado_new->setGradoOtro($form['otroGrado']?$form['otroGrado']:'');

				}else{
					$discapacidadGrado_new->setGradoDiscapacidadTipo($em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->find($form['gradoDiscapacidad']));
				}		
				
				if($form['porcentaje']!="")
					$discapacidadGrado_new->setPorcentaje($form['porcentaje']);
				$discapacidadGrado_new->setFechaRegistro(new \DateTime('now'));
				$discapacidadGrado_new->setFechaModificacion(new \DateTime('now'));
				$em->persist($discapacidadGrado_new);
				$em->flush();
			}
		}
		//4.2 
		// ELIMINAMOS LOS DATOS DE LA DIFICULTAD DE APRENDIZAJE
		$dificultadAprendizaje_rude = $em->getRepository('SieAppWebBundle:RudeDificultadAprendizaje')->findBy(array('rude'=>$rude->getId()));
		foreach($dificultadAprendizaje_rude as $i)
		{
			$em->remove($i);
			$em->flush();
		}
		// CREAMOS LOS DATOS DE LA DIFICULTAD DE APRENDIZAJE	
		if(isset($form['dificultadAprendizaje']))
		{
			foreach($form['dificultadAprendizaje'] as $i)
			{
				$dificultadAprendizaje_new = new RudeDificultadAprendizaje();
				$dificultadAprendizaje_new->setRude($rude);		
				$dificultadAprendizaje_new->setDificultadAprendizajeTipo($em->getRepository('SieAppWebBundle:DificultadAprendizajeTipo')->find($i));		
				$dificultadAprendizaje_new->setFechaRegistro(new \DateTime('now'));
				$dificultadAprendizaje_new->setFechaModificacion(new \DateTime('now'));
				$em->persist($dificultadAprendizaje_new);
				$em->flush();
			}	
		}
		
		
		//4.3
		// ELIMINAMOS LOS DATOS DE TALENTO EXTRAORDINARIO
		$talentoextraordinario_rude = $em->getRepository('SieAppWebBundle:RudeTalentoExtraordinario')->findOneBy(array('rude'=>$rude->getId()));
		if($talentoextraordinario_rude)
		{
			$em->remove($talentoextraordinario_rude);
			$em->flush();
		}
		
		// CREAMOS LOS DATOS DE TALENTO EXTRAORDINARIO
		if($form['talentoExtraordinario']!=""){
			$talentoextraordinario_new = new RudeTalentoExtraordinario();
			$talentoextraordinario_new->setRude($rude);
			$talentoextraordinario_new->setCoeficienteintelectual($form['coeficienteIntelectual']);
			$talentoextraordinario_new->setPromediocalificaciones($form['promedioCalificaciones']);
			$talentoextraordinario_new->setTalentoExtraordinarioTipo($em->getRepository('SieAppWebBundle:TalentoExtraordinarioTipo')->find($form['talentoExtraordinario']));
			$talentoextraordinario_new->setEspecificoEn($form['especificoEn']?$form['especificoEn']:'');
			$talentoextraordinario_new->setTalentoOtro($form['talentoOtro']?$form['talentoOtro']:'');
			$talentoextraordinario_new->setFechaRegistro(new \DateTime('now'));
			$talentoextraordinario_new->setFechaModificacion(new \DateTime('now'));
			$em->persist($talentoextraordinario_new);
			$em->flush();
		}
		
		$estrategiaAtencionIntegral_rude = $em->getRepository('SieAppWebBundle:RudeEstrategiaAtencionIntegral')->findBy(array('rude'=>$rude->getId()));
		foreach($estrategiaAtencionIntegral_rude as $i)
		{
			$em->remove($i);
			$em->flush();
		}
		if(isset($form['estrategiaAtencionIntegral'])){
			$estrategiaAtencionIntegral_new = new RudeEstrategiaAtencionIntegral();		
			foreach($form['estrategiaAtencionIntegral'] as $i)
			{
				$estrategiaAtencionIntegral_new = new RudeEstrategiaAtencionIntegral();
				$estrategiaAtencionIntegral_new->setRude($rude);
				$estrategiaAtencionIntegral_new->setEstrategiaAtencionIntegralTipo($em->getRepository('SieAppWebBundle:EstrategiaAtencionIntegralTipo')->find($i));
				if($i===99)
				{ //pendiente
					$dificultadAprendizaje_new->setEstrategiaOtro($form['estrategiaOtro']);
				}			
				$estrategiaAtencionIntegral_new->setFechaRegistro(new \DateTime('now'));
				$estrategiaAtencionIntegral_new->setFechaModificacion(new \DateTime('now'));
				$em->persist($estrategiaAtencionIntegral_new);
				$em->flush();
			}
		}
		//4.4
		// ELIMINAMOS LOS SERVICIOS
		$eliminarServicios = $em->createQueryBuilder()
						->delete('')
						->from('SieAppWebBundle:RudeServicioBasico','rsb')
						->where('rsb.rude = :rude')
						->setParameter('rude', $rude)
						->getQuery()
						->getResult();
		 // CREAMOS LOS SERVICIOS
		 if($form['agua'])
		 {
			$servicio = new RudeServicioBasico();
			$servicio->setRude($rude);
			$servicio->setServicioBasicoTipo($em->getRepository('SieAppWebBundle:ServicioBasicoTipo')->find(($form['agua'])?1:null));
			$servicio->setFechaRegistro(new \DateTime('now'));
			$em->persist($servicio);
			$em->flush();
		 }
		 if($form['banio'])
		 {
			$servicio = new RudeServicioBasico();
			$servicio->setRude($rude);
			$servicio->setServicioBasicoTipo($em->getRepository('SieAppWebBundle:ServicioBasicoTipo')->find(($form['banio'])?2:null));
			$servicio->setFechaRegistro(new \DateTime('now'));
			$em->persist($servicio);
			$em->flush();
		 }
		 if($form['alcantarillado'])
		 {
			$servicio = new RudeServicioBasico();
			$servicio->setRude($rude);
			$servicio->setServicioBasicoTipo($em->getRepository('SieAppWebBundle:ServicioBasicoTipo')->find(($form['alcantarillado'])?3:null));
			$servicio->setFechaRegistro(new \DateTime('now'));
			$em->persist($servicio);
			$em->flush();
		 }
		 if($form['energiaElectrica'])
		 {
			$servicio = new RudeServicioBasico();
			$servicio->setRude($rude);
			$servicio->setServicioBasicoTipo($em->getRepository('SieAppWebBundle:ServicioBasicoTipo')->find(($form['energiaElectrica'])?4:null));
			$servicio->setFechaRegistro(new \DateTime('now'));
			$em->persist($servicio);
			$em->flush();
		 }
		 if($form['recojoBasura'])
		 {
			$servicio = new RudeServicioBasico();
			$servicio->setRude($rude);
			$servicio->setServicioBasicoTipo($em->getRepository('SieAppWebBundle:ServicioBasicoTipo')->find(($form['recojoBasura'])?5:null));
			$servicio->setFechaRegistro(new \DateTime('now'));
			$em->persist($servicio);
			$em->flush();
		 }
		 if($form['banioAdaptado'])
		 {
			$servicio = new RudeServicioBasico();
			$servicio->setRude($rude);
			$servicio->setServicioBasicoTipo($em->getRepository('SieAppWebBundle:ServicioBasicoTipo')->find(($form['banioAdaptado'])?6:null));
			$servicio->setFechaRegistro(new \DateTime('now'));
			$em->persist($servicio);
			$em->flush();
		 }
		 
		//4.5
		// ELIMINAMOS LOS MEDIOS DE COMUNICACION
		$eliminarMediosComunicacion = $em->createQueryBuilder()
						->delete('')
						->from('SieAppWebBundle:RudeMediosComunicacion','rsb')
						->where('rsb.rude = :rude')
						->setParameter('rude', $rude)
						->getQuery()
						->getResult();
		 // CREAMOS LOS MEDIOS DE COMUNCACION
		 if($form['radio'])
		 {
			$servicio = new RudeMediosComunicacion();
			$servicio->setRude($rude);
			$servicio->setMediosComunicacionTipo($em->getRepository('SieAppWebBundle:MediosComunicacionTipo')->find(($form['radio'])?1:null));
			$servicio->setFechaRegistro(new \DateTime('now'));
			$servicio->setFechaModificacion(new \DateTime('now'));
			$em->persist($servicio);
			$em->flush();
		 }
		 if($form['televisor'])
		 {
			$servicio = new RudeMediosComunicacion();
			$servicio->setRude($rude);
			$servicio->setMediosComunicacionTipo($em->getRepository('SieAppWebBundle:MediosComunicacionTipo')->find(($form['televisor'])?2:null));
			$servicio->setFechaRegistro(new \DateTime('now'));
			$servicio->setFechaModificacion(new \DateTime('now'));
			$em->persist($servicio);
			$em->flush();
		 }
		 if($form['telefono'])
		 {
			$servicio = new RudeMediosComunicacion();
			$servicio->setRude($rude);
			$servicio->setMediosComunicacionTipo($em->getRepository('SieAppWebBundle:MediosComunicacionTipo')->find(($form['telefono'])?3:null));
			$servicio->setFechaRegistro(new \DateTime('now'));
			$servicio->setFechaModificacion(new \DateTime('now'));
			$em->persist($servicio);
			$em->flush();
		 }
		 if($form['celular'])
		 {
			$servicio = new RudeMediosComunicacion();
			$servicio->setRude($rude);
			$servicio->setMediosComunicacionTipo($em->getRepository('SieAppWebBundle:MediosComunicacionTipo')->find(($form['celular'])?4:null));
			$servicio->setFechaRegistro(new \DateTime('now'));
			$servicio->setFechaModificacion(new \DateTime('now'));
			$em->persist($servicio);
			$em->flush();
		 }
		 if($form['computadora'])
		 {
			$servicio = new RudeMediosComunicacion();
			$servicio->setRude($rude);
			$servicio->setMediosComunicacionTipo($em->getRepository('SieAppWebBundle:MediosComunicacionTipo')->find(($form['computadora'])?5:null));
			$servicio->setFechaRegistro(new \DateTime('now'));
			$servicio->setFechaModificacion(new \DateTime('now'));
			$em->persist($servicio);
			$em->flush();
		 }
		

		// ELIMINAMOS ACCESO A INTERNET
		$eliminarInternet = $em->createQueryBuilder()
						->delete('')
						->from('SieAppWebBundle:RudeAccesoInternet','rai')
						->where('rai.rude = :rude')
						->setParameter('rude', $rude)
						->getQuery()
						->getResult();
		// CREAMOS ACCESO A INTERNET
		if(isset($form['accesoInternet']))
		{
			$accesoInternet = $form['accesoInternet'];
			for ($i=0; $i < count($accesoInternet); $i++)
			{
				$internetEstudiante = new RudeAccesoInternet();
				$internetEstudiante->setRude($rude);
				$internetEstudiante->setAccesoInternetTipo($em->getRepository('SieAppWebBundle:AccesoInternetTipo')->find($accesoInternet[$i]));
				$internetEstudiante->setFechaRegistro(new \DateTime('now'));
				$em->persist($internetEstudiante);
				$em->flush();
			}
			// VERIFICAMOS SI TIENE ACCESO A INTERNET
			if(in_array(4, $accesoInternet))
				$rude->setFrecuenciaUsoInternetTipo(null);
			else
				$rude->setFrecuenciaUsoInternetTipo($em->getRepository('SieAppWebBundle:FrecuenciaUsoInternetTipo')->find($form['frecuenciaUsoInternetTipo']));
		}
		// Registro paso 4
		if($rude->getRegistroFinalizado() < 4){
			$rude->setRegistroFinalizado(4);
		}
		$em->persist($rude);
		$em->flush();
		//dump($rude->getEsEducacionEnCasa());die;
		$response = new JsonResponse();
		return $response->setData(['msg'=>true]);
	}


	public function createFormInscripcionActual($rude)
	{
		$em = $this->getDoctrine()->getManager();
		$atencionIndirecta = $em->getRepository('SieAppWebBundle:RudeAtencionIndirecta')->findOneBy(array('rude'=>$rude->getId()));
		//dump($atencionIndirecta);die;
			$form = $this->createFormBuilder($rude)
			->add('id', 'hidden')
			->add('nivel', 'text', array('mapped'=>false, 
				'required'=>false,
				'data'=>($atencionIndirecta)?$atencionIndirecta->getNivel():'',))		
			->add('grado', 'text', array(
				'required' => false, 
				'mapped'=>false,
				'data'=>($atencionIndirecta)?$atencionIndirecta->getGrado():'',))
			->add('institucion', 'text', array(
				'required' => false, 
				'data'=>($atencionIndirecta)?$atencionIndirecta->getInstitucion():'',
				'mapped'=>false, 	
			))->getForm();
		return $form;
	}

	public function saveFormInscripcionActualAction(Request $request)
	{
		$form = $request->get('form');
		$em = $this->getDoctrine()->getManager();
		$rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['id']);

		$em->persist($rude);
		if(isset($form['nivel']) || isset($form['grado']) || isset($form['institucion'])){
			$atencionIndirecta = $em->getRepository('SieAppWebBundle:RudeAtencionIndirecta')->findOneBy(array('rude'=>$rude->getId()));
			if(!$atencionIndirecta){
				$atencionIndirecta = new RudeAtencionIndirecta();
				$atencionIndirecta->setRude($rude);
			}
			$atencionIndirecta->setNivel($form['nivel']);
			$atencionIndirecta->setGrado($form['grado']);
			$atencionIndirecta->setInstitucion($form['institucion']);	
			$em->persist($atencionIndirecta);
			$em->flush();		
		}

		//$em->flush();

		// Registro paso 5
		if($rude->getRegistroFinalizado() < 5){
			$rude->setRegistroFinalizado(5);
		}

		$em->flush();

		$response = new JsonResponse();
		return $response->setData(['msg'=>true]);
	}
	/**
	 * FORMULARIO CON QUIEN VIVE EL ESTUDAINTE
	 */
	public function createFormConQuienVive($rude)
	{
		$em = $this->getDoctrine()->getManager();
		$form = $this->createFormBuilder($rude)
				->add('id', 'hidden')
				->add('viveHabitualmenteTipo', 'entity', array(
						'class' => 'SieAppWebBundle:ViveHabitualmenteTipo',
						'query_builder' => function (EntityRepository $e) {
							return $e->createQueryBuilder('vat')
									->where('vat.esVigente = true')
									->orderBy('vat.id', 'ASC')
							;
						},
						'empty_value' => 'Seleccionar...',
						'required'=>true,
						'data'=> ($rude->getViveHabitualmenteTipo())?$em->getReference('SieAppWebBundle:ViveHabitualmenteTipo', $rude->getViveHabitualmenteTipo()->getId()):'',
						'mapped'=>false
					))
				->getForm();
		return $form;
	}

	public function saveFormConQuienViveAction(Request $request)
	{ 
		$em = $this->getDoctrine()->getManager();
		$form = $request->get('form');
		$rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['id']);
		$rude->setViveHabitualmenteTipo($em->getRepository('SieAppWebBundle:ViveHabitualmenteTipo')->find($form['viveHabitualmenteTipo']));
		$em->flush();
		if($rude->getRegistroFinalizado() < 6){
			$rude->setRegistroFinalizado(6);
		}
		$response = new JsonResponse();
		return $response->setData(['msg'=>'ok']);
	}

	/**
	 * FUNCIONES APODERADO
	 * Tab 6 - Tutor
	 */
	public function obtenerApoderado($idInscripcion, Array $tipoApoderado)
	{
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion');

		$apoderado = $repository->createQueryBuilder('ai')
					->select('
						ai.id, 
						at.id as apoderadoTipo, 
						p.id as idPersona, 
						p.nombre, 
						p.paterno, 
						p.materno, 
						p.carnet,
						p.complemento,
						p.fechaNacimiento,
						p.celular,
						p.segipId,
						p.apellidoEsposo,
						ct.id as cedulaTipoId,
						IDENTITY(p.estadocivilTipo) as estado_civil, 
						dt.id as expedido,
						gt.id as genero, 
						p.correo, 
						aid.id as idDatos, 
						im.id as idiomaMaterno, 
						it.id as instruccionTipo, 
						aid.empleo, 
						aid.telefono,
						aot.id as ocupacion,
						aid.obs')
					->innerJoin('SieAppWebBundle:Persona','p','with','ai.persona = p.id')
					->innerJoin('SieAppWebBundle:GeneroTipo','gt','with','p.generoTipo = gt.id')
					->innerJoin('SieAppWebBundle:ApoderadoTipo','at','with','ai.apoderadoTipo = at.id')
					->leftJoin('SieAppWebBundle:ApoderadoInscripcionDatos','aid','with','aid.apoderadoInscripcion = ai.id')
					->leftJoin('SieAppWebBundle:IdiomaTipo','im','with','aid.idiomaMaterno = im.id')
					->leftJoin('SieAppWebBundle:InstruccionTipo','it','with','aid.instruccionTipo = it.id')
					->leftJoin('SieAppWebBundle:ApoderadoOcupacionTipo','aot','with','aid.ocupacionTipo = aot.id')
					->leftJoin('SieAppWebBundle:DepartamentoTipo','dt','with','p.expedido = dt.id')
					->leftJoin('SieAppWebBundle:CedulaTipo','ct','with','p.cedulaTipo = ct.id')
					->where('ai.estudianteInscripcion = :idInscripcion')
					->andWhere('at.id in (:tipoApoderado)')
					->setParameter('idInscripcion',$idInscripcion)
					->setParameter('tipoApoderado',$tipoApoderado)
					->setMaxResults(1)
					->getQuery()
					->getResult();

					//dump($apoderado);die;

		if(!$apoderado){
			$apoderado[] = array(
				'id'=>'nuevo',
				'apoderadoTipo'=>null,
				'idPersona'=>'nuevo',
				'nombre'=>null,
				'paterno'=>null,
				'materno'=>null,
				'carnet'=>null,
				'complemento'=>null,
				'fechaNacimiento'=>null,
				'segipId'=>null,
				'apellidoEsposo'=>null,
				'estado_civil'=>null,
				'expedido'=>null,
				'genero'=>null,
				'correo'=>null,
				'idDatos'=>'nuevo',
				'idiomaMaterno'=>null,
				'instruccionTipo'=>null,
				'empleo'=>null,
				'telefono'=>null,
				'ocupacion'=>null,
				'obs'=>null,
				'foto'=>null,
				'corregirFecha'=>false,
				'tipoApoderado'=>$tipoApoderado
			);
		}else{
			// Verificamos si los ids de los registros son nulos para ponerles el valor nuevo
			if(is_null($apoderado[0]['id'])){$apoderado[0]['id'] = 'nuevo'; }
			if(is_null($apoderado[0]['idPersona'])){$apoderado[0]['idPersona'] = 'nuevo'; }
			if(is_null($apoderado[0]['idDatos'])){$apoderado[0]['idDatos'] = 'nuevo'; }
			// Formateamos la fecha en d-m-Y
			$apoderado[0]['fechaNacimiento'] = date_format($apoderado[0]['fechaNacimiento'],'d-m-Y');
			// Validamos si la fecha de nacimiento tiene formato correcto y si es una fecha valida
			$anioActual = date('Y');
			$fechaNacimiento = $apoderado[0]['fechaNacimiento'];
			$anioNacimiento = explode('-',$fechaNacimiento);

			// Verificamos si es una fecha valida
			if(checkdate($anioNacimiento[1], $anioNacimiento[0], $anioNacimiento[2])){
				$anioNacimiento = $anioNacimiento[2];
				$corregirFecha = false;
				$diferenciaAnios = $anioActual - $anioNacimiento;
				if($diferenciaAnios > 100 or $diferenciaAnios < 15){
					$corregirFecha = true;
				}
			}else{
				$corregirFecha = true;
			}
			// Verificamos si la persona tiene fotografia de ci
			$foto = $em->getRepository('SieAppWebBundle:PersonaDocumento')->findOneBy(array('personaId'=>$apoderado[0]['idPersona'],'documento'=>10));
			if($foto){
				$apoderado[0]['foto'] = $foto->getRuta();
			}else{
				$apoderado[0]['foto'] = null;
			}
			$apoderado[0]['corregirFecha'] = $corregirFecha;
			$apoderado[0]['tipoApoderado'] = $tipoApoderado;
		}

		// VERIFICAMOS SI EL CARNET NO ES VALIDO CON VALOR SC
		$pos = strpos($apoderado[0]['carnet'], 'SC');
		if($pos === false){
			$apoderado[0]['carnetRequerido'] = true;
		}else{
			// $apoderado[0]['carnet'] = '';
			$apoderado[0]['carnetRequerido'] = false;
		}

		return $apoderado;
	}

	public function createFormApoderado($rude, $idInscripcion, $datos)
	{

		// $idInscripcion = $inscripcion->getId();

		// $padreTutor = $this->obtenerApoderado($idInscripcion,array(1,3,4,5,6,7,8,9,10,11,12,13))[0];

		// dump($datos['tipoApoderado']);die;
		$tipoApoderado = $datos['tipoApoderado'];
		//dump($tipoApoderado);
		$em = $this->getDoctrine()->getManager();
		
		// DEFINICION DE GENEROS POR TIPO DE APODERADO
		if (in_array(1, $tipoApoderado)) {
			$generos = [1];
		} else {
			if (in_array(2, $tipoApoderado)) {
				$generos = [2];
			} else {
				$generos = [1,2];
			}            
		}
		$generos = [1,2];
		//dump($generos);die;

		$form = $this->createFormBuilder($datos)
					->add('idInscripcion', 'hidden', array('data' => $idInscripcion,'mapped'=>false))
					->add('rudeId', 'hidden', array('data' => $rude->getId(),'mapped'=>false))
					->add('id', 'hidden', array('required' => true))
					->add('apoderadoTipo', 'entity', array(
							'class' => 'SieAppWebBundle:ApoderadoTipo',
							'query_builder' => function (EntityRepository $e) use ($tipoApoderado) {
								return $e->createQueryBuilder('at')
										->where('at.id in (:ids)')
										->setParameter('ids', $tipoApoderado)
										->orderBy('at.id', 'ASC')
								;
							},
							'empty_value' => 'Seleccionar...',
							'required'=>true,
							'data'=> ($datos['apoderadoTipo'] != null)? $em->getReference('SieAppWebBundle:ApoderadoTipo', $datos['apoderadoTipo']): ''
						))
					->add('idPersona', 'hidden', array('required' => true))
					->add('nombre', 'text')
					->add('paterno', 'text', array('required' => false))
					->add('materno', 'text', array('required' => false))
					->add('carnet', 'text')
					->add('complemento', 'text', array('required' => false))
					->add('expedido', 'entity', array(
							'class' => 'SieAppWebBundle:DepartamentoTipo',
							'query_builder' => function (EntityRepository $e) {
								return $e->createQueryBuilder('dt')
										->where('dt.id not in (0)')
										->orderBy('dt.id', 'ASC')
								;
							},
							'property'=>'sigla',
							'empty_value' => 'Seleccionar...',
							'data'=>($datos['expedido'] != null)?$em->getReference('SieAppWebBundle:DepartamentoTipo', $datos['expedido']):''
						))
					->add('fechaNacimiento', 'text', array('required' => true))
					// ->add('telefono', 'text', array('required' => false))
					->add('segipId', 'hidden', array('required' => true))
					->add('genero', 'entity', array(
							'class' => 'SieAppWebBundle:GeneroTipo',
							'query_builder' => function (EntityRepository $e) use ($generos){
								return $e->createQueryBuilder('gt')
										->where('gt.id in (:ids)')
										->setParameter('ids', $generos)
										->orderBy('gt.id', 'ASC')
								;
							},
							'empty_value' => 'Selecionar...',
							'required'=>true,
							'data'=>($datos['genero'] != null)?$em->getReference('SieAppWebBundle:GeneroTipo', $datos['genero']):''
						))
					->add('correo', 'text', array('required' => false))
					->add('celular', 'text', array('required' => false))
					->add('telefono', 'text', array('required' => false))
					->add('idDatos', 'hidden', array('required' => true))
					->add('idiomaMaterno', 'entity', array(
							'class' => 'SieAppWebBundle:IdiomaTipo',
							'query_builder' => function (EntityRepository $e) use ($rude) {
								return $e->createQueryBuilder('it')
										->where('it.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'idioma_tipo'))
										->orderBy('it.idioma','ASC');
							},
							'empty_value' => 'Selecionar...',
							'required'=>true,
							'data'=>($datos['idiomaMaterno'] != null)?$em->getReference('SieAppWebBundle:IdiomaTipo', $datos['idiomaMaterno']):''
						))
					->add('ocupacion', 'entity', array(
							'class' => 'SieAppWebBundle:ApoderadoOcupacionTipo',
							'query_builder' => function (EntityRepository $e) use ($rude) {
								return $e->createQueryBuilder('aot')
										->where('aot.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'apoderado_ocupacion_tipo'))
										->orderBy('aot.ocupacion','ASC');
							},
							'empty_value' => 'Selecionar...',
							'required'=>true,
							'property'=>'ocupacion',
							'data'=>($datos['ocupacion'] != null)?$em->getReference('SieAppWebBundle:ApoderadoOcupacionTipo', ($datos['ocupacion'])?$datos['ocupacion']:10035):''
						))
					->add('empleo', 'text', array('required' => true))
					->add('instruccionTipo', 'entity', array(
							'class' => 'SieAppWebBundle:InstruccionTipo',
							'query_builder' => function (EntityRepository $e) use ($rude) {
								return $e->createQueryBuilder('it')
										->where('it.id in (:ids)')
										->setParameter('ids', $this->obtenerCatalogo($rude, 'instruccion_tipo'))
										->orderBy('it.id','ASC');
							},
							'empty_value' => 'Selecionar...',
							'required'=>true,
							'data'=>($datos['instruccionTipo'] != null)?$em->getReference('SieAppWebBundle:InstruccionTipo', $datos['instruccionTipo']):''
						))
					->add('cedulaTipoId', 'hidden', array('required' => true))
					

					->getForm();

		return $form;
	}

	/**
	 * FUNCIONES PARA REGISTRO DE TUTORES
	 */
	/*
	* FUNCION AJAX PARA BUSCAR PERSONA APODERADO
	*/
	public function buscarPersonaAction(Request $request)
	{
		try
		{
			$carnet = $request->get('carnet');
			$complemento = $request->get('complemento');
			// $complemento = ($request->get('complemento') != "")?$request->get('complemento'):0;
			$paterno = $request->get('paterno');
			$materno = $request->get('materno');
			$nombre = $request->get('nombre');
			$fechaNacimiento = $request->get('fechaNacimiento');
			$esExtranjero=filter_var($request->get('esExtranjero'),FILTER_SANITIZE_NUMBER_INT);
			$documentoNro=$request->get('documentoNro');
			$extranjero_segip = $request->get('extranjero_segip');

			$data=array();
			if($esExtranjero==0)//es nacional
			{
				$parametros = array(
					'complemento'=>$complemento,
					'primer_apellido'=>$paterno,
					'segundo_apellido'=>$materno,
					'nombre'=>$nombre,
					'fecha_nacimiento'=>$fechaNacimiento
				);
				if ($extranjero_segip)  $parametros['extranjero'] = '2';

				// $respuesta = $this->get('sie_app_web.segip')->verificarPersona($carnet, $complemento, $paterno, $materno, $nombre, $fechaNacimiento, 'prod', 'academico');
				$persona = $this->get('sie_app_web.segip')->buscarPersonaPorCarnet($carnet, $parametros, 'prod', 'academico');

				// dump($persona);
				// die;
				// dump($persona['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson']);die;

				//$respuesta = false;
				// $persona = $persona['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson'];
				// if($persona == null){
				//     dump('no existe');
				// }else{
				//     dump('existe');
				// }

				if(isset($persona['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson']) && $persona['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson'] !== "null")
				{
					// dump($persona);die;
					// $persona = $this->get('sie_app_web.segip')->buscarPersonaPorCarnet($carnet, $parametros, 'dev', 'academico');
					// $persona = 
					$persona = json_decode($persona['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson'], true);
					// dump($persona);die;
					$data['status'] = 200;
					$data['persona'] = array(
						'id'=>'segip',
						'carnet'=> $persona['NumeroDocumento'],
						'complemento'=> ($persona['ComplementoVisible'] == 1)? $persona['Complemento']:'',
						'paterno'=> $persona['PrimerApellido'],
						'materno'=> $persona['SegundoApellido'],
						'nombre'=> $persona['Nombres'],
						'fecha_nacimiento'=> $persona['FechaNacimiento']
					);
				}
				else
				{
					$data['status'] = 404;
				}

				// $data['status'] = 200;
				// $data['persona'] = array(
				//     'id'=>'23519419',
				//     'carnet'=> '8260138',
				//     'complemento'=> '',
				//     'paterno'=> 'QUISPEC',
				//     'materno'=> 'CHOQUE',
				//     'nombre'=> 'JHONNY',
				//     'fecha_nacimiento'=> '20-01-1990'
				// );
			
			}
			else // es extranjero
			{
				$personaExtranjera=$this->buscarPersonaExtranjero($documentoNro);
				if($personaExtranjera)
				{
					$data['status'] = 200;
					$data['persona'] = array(
						'id'=>'extranjero',//REVISAR--------
						'carnet'=> $personaExtranjera->getCarnet(),
						'complemento'=> $personaExtranjera->getComplemento(),
						'paterno'=> $personaExtranjera->getPaterno(),
						'materno'=> $personaExtranjera->getMaterno(),
						'nombre'=> $personaExtranjera->getNombre(),
						///'fecha_nacimiento'=> $personaExtranjera->getFechaNacimiento()
						'fecha_nacimiento'=> $personaExtranjera->getFechaNacimiento()->format('d-m-Y')
					);
				}
				else
				{
					$data['status'] = 404;
				}
			}

			$response = new JsonResponse();
			$response->setData($data);
			return $response;
		} catch (Exception $e) {
			
		}
	}

	private function buscarPersonaExtranjero($documentoNro)
	{
		$em = $this->getDoctrine()->getManager();
		$extranjero = $em->getRepository('SieAppWebBundle:EstudiantePersonaDiplomatico')->findOneBy(array('documentoNumero'=>$documentoNro));

		if($extranjero)
		{
			$extranjero=$extranjero->getPersona();
			if($extranjero)
			{
				$persona=$em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('id'=>$extranjero->getId()));
				return $persona;
			}
			else
				return false;
		}
		else
			return false;
	}

	public function saveFormApoderadoEspecialAction(Request $request)
	{
			 
			
		/*
		 //////////////////////////////////////////////////////////////////////////
		 /////////////////// Registro de apoderado PADRE, MADRE Y TUTOR  /////////////////
		 //////////////////////////////////////////////////////////////////////////
		*/
		$form = $request->get('form');
		
		$idApoderadoInscripcion = $form['id'];
		$idPersona = $form['idPersona'];

		$em = $this->getDoctrine()->getManager();

		$rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['rudeId']);

		$tipo = $request->get('tipo');
		if($tipo == 'padre'){
			$parentesco = array(1);
			$tiene = $request->get('p_tienePadre');
		}
		if($tipo == 'madre'){
			$parentesco = array(2);
			$tiene = $request->get('m_tieneMadre');
		}
		if($tipo == 'tutor'){
			$parentesco = $this->obtenerCatalogo($rude, 'apoderado_tipo');
			$tiene = $request->get('t_tieneTutor');
		}

		// expedido
		if (empty($form['expedido']) && strlen($form['expedido'])==0) {
			$expedido = 0;
		}else{
			$expedido = $form['expedido'];
		}

		// VERIFICAMOS SI EL ESTUDIANTE TIENE // PADRE // MADRE // O TUTOR
		if($tiene == 1)
		{
			// VERIFICAMOS SI LA PERSONA ES NUEVA SIN CARNET
			// O ES NUEVO PERO VALIDADO POR EL SEGIP
			
			if($form['idPersona'] == 'nuevo' or $form['idPersona'] == 'segip' or $form['idPersona'] == 'extranjero')
			{
				// PREGUNTAMOS SI EL CARNET NO ESTA VACIO
				// ENTONCES EL DATO VIENE DEL SERVICIO SEGIP
				if($form['carnet'] != "")
				{
					// BUSCAMOS LA PERSONA EN LA BASE DE DATOS
					$form['paterno'] = str_replace('--', '', $form['paterno']);
					$form['materno'] = str_replace('--', '', $form['materno']);

					//verificamos si son personas extranjeras
					$personaExtranjera = $this->buscarPersonaExtranjero($form['carnet']);
					if($personaExtranjera &&  $form['idPersona'] == 'extranjero')//personas extranjeras
					{
						$persona=$personaExtranjera;
					}
					else //personas nacionales
					{
						/*
						$persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
							'carnet'=>$form['carnet'],
							'complemento'=>mb_strtoupper($form['complemento'],'utf-8'),
							'paterno'=>mb_strtoupper($form['paterno'],'utf-8'),
							'materno'=>mb_strtoupper($form['materno'],'utf-8'),
							'nombre'=>mb_strtoupper($form['nombre'],'utf-8'),
							'fechaNacimiento'=>new \DateTime($form['fechaNacimiento'])
						));
						// VERIFICAMOS SI EL REGISTRO NO EXISTE EN LA BASE DE DATOS
						if (!$persona)
						{
							// BUSCAMOS NUEVAMENTE A LA PERSONA SIN COMPLEMENTO EN LA BASE DE DATOS
							$persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
								'carnet'=>$form['carnet'],
								'paterno'=>mb_strtoupper($form['paterno'],'utf-8'),
								'materno'=>mb_strtoupper($form['materno'],'utf-8'),
								'nombre'=>mb_strtoupper($form['nombre'],'utf-8'),
								'fechaNacimiento'=>new \DateTime($form['fechaNacimiento'])
							));
							// SI EXISTE LA PERSONA ENTONCES LE AGREGAMOS EL COMPLEMENTO
							if ($persona)
							{
								//Correcion del bug cuando existe dos personas que tienen el mismo CI pero son distitas en fecha 19/03/2021
								$objPersonasConMismoCarnet=$em->getRepository('SieAppWebBundle:Persona')->findBy(array('carnet'=>$persona->getCarnet()));
								if($objPersonasConMismoCarnet)
								{
									foreach ($objPersonasConMismoCarnet as $value)
									{
										if($persona->getId()!= $value->getId() && $value->getSegipId()==0)
										{
											$value->setCarnet($value->getCarnet().'±');
											$em->flush();
										}
									}
								}
								$persona->setComplemento(mb_strtoupper($form['complemento'],'utf-8'));
							}
							else
							{
								//Validacion para evitar que se vuelva a registrar con Carnet=$form['carnet'] y complemento=±,
								//validacion creada con apoyo del lic Carlos y lic Erik el 12/03/2021
								$persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
									'complemento'=>mb_strtoupper($form['complemento'],'utf-8'),
									'carnet'=>$form['carnet'],
									'fechaNacimiento'=>new \DateTime($form['fechaNacimiento']),
									'segipId'=>'1'
								));
								if($persona!=NULL)
								{
									$persona->setCarnet($form['carnet']);
									//$persona->setComplemento(mb_strtoupper($form['complemento'],'utf-8'));
									$persona->setPaterno(mb_strtoupper($form['paterno'],'utf-8'));
									$persona->setMaterno(mb_strtoupper($form['materno'],'utf-8'));
									$persona->setNombre(mb_strtoupper($form['nombre'],'utf-8'));
									$persona->setFechaNacimiento=new \DateTime($form['fechaNacimiento']);
								}
								else
								{
									//Validacion para corregir si la fecha de nacimiento de una persona validad por el segip, es diferente en la BD el 22/03/2021
									$persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
										'paterno'=>mb_strtoupper($form['paterno'],'utf-8'),
										'materno'=>mb_strtoupper($form['materno'],'utf-8'),
										'nombre'=>mb_strtoupper($form['nombre'],'utf-8'),
										'segipId'=>'1'));
									if($persona!=NULL)
									{
										$persona->setFechaNacimiento=new \DateTime($form['fechaNacimiento']);
									}
									else
									{
										$persona=null;
									}
								}
							}
						}
						else
						{
							// SI EL REGISTRO EXISTE EN LA BASE DE DATOS
							// VERIFICAMOS SI ESTA VALIDADO POR EL SEGIP, SI NO ESTA VALIDADO LO VALIDAMOS
							if($persona->getSegipId() != 1){
								$persona->setSegipId(1);
							}
						}
					   */
					  //persona con segip 1
					  //$persona = $this->buscarPersona($form,true,1);//busqueda de persona con carnet y segip =1
					  $persona = $this->get('buscarpersonautils')->buscarPersona($form,$conCI=true, $segipId=1);
					}
				}
				else
				{
					/*
					// SI EL DATO VIENE SIN CARNET
					// BUSCAMOS PERSONA POR SUS DATOS PERSONALES EN LA BASE DE DATOS
					$persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
						'paterno'=>mb_strtoupper($form['paterno'],'utf-8'),
						'materno'=>mb_strtoupper($form['materno'],'utf-8'),
						'nombre'=>mb_strtoupper($form['nombre'],'utf-8'),
						'fechaNacimiento'=>new \DateTime($form['fechaNacimiento'])
					));
					// VERIFICAMOS SI EXISTE LA PERSONA
					if($persona){
						// BUSCAMOS EL TEXTO SC "SIN CARNET" EN EL CAMPO CARNET
						$pos = strpos($persona->getCarnet(), 'SC');
						// SI NO SE ENCUENTRA EL TEXTO
						// LA PERSONA ENCONTRADA CUENTA CON CARNET
						// ENTONCES DECLARAMOS LA VARABLE PERSONA COMO NULL PARA HACER EL REGISTRO
						// DE LA PERSONA SIN CARNET
						if($pos === false){
							$persona == null;
						}
					}else{
						$persona = null;
					}
					*/
				   //$persona = $this->buscarPersona($form,false,1);//busqueda de persona sin carnet y segip =1
				   $persona = $this->get('buscarpersonautils')->buscarPersona($form,$conCI=false, $segipId=1);
				}
				// VERIFICAMOS SI LA PERSONA EXISTE
				if($persona)
				{
					// SI EXISTE LA PERSONA SOLO ACTUALIZAMOS:
					// FECHA DE NACIMIENTO
					// CELULAR
					// EXPEDIDO
					$persona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
					$persona->setCelular($form['celular']);
					$persona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedido));

					if(isset($form['estado_civil']))
						$persona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find($form['estado_civil']));

					if(isset($form['apellido_esposo']))
						$persona->setApellidoEsposo(mb_strtoupper($form['apellido_esposo'],'utf-8'));

					$persona->setCorreo($form['correo']);
					if(isset($form['cedulaTipoId']))
					{
						$persona->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find($form['cedulaTipoId']));
					}
					$em->flush();
					$idPersona = $persona->getId();
				}
				else
				{
					/*
					// SI LA PERSONA NO EXISTE
					// PREGUNTAMOS SI EL CARNET NO ESTA VACIO PARA BUSCAR LAS COINCIDENCIAS DE CARNET EN LA BASE DE DATOS
					if($form['carnet'] != ""){
						// BUSCAMOS SI EL NUMERO DE CARNET Y COMPLEMENTO ESTA REGISTRADO CON OTRA PERSONA
						$personaAnterior = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
							'carnet'=>$form['carnet'],
							'complemento'=>mb_strtoupper($form['complemento'],'utf-8')
						));

						// VERIFICAMOS SI EXISTE OTRA PERSONA CON EL MISMO CARNET Y COMPLEMENTO
						// SI ENCUENTA ACTUALIZAMOS EL NUMERO DE CARNET CON EL CARACTER ESPECIAL
						if($personaAnterior)
						{
							// VERIFICAMOS SI EL REGISTRO ENCONTRADO NO ESTA VALIDADO POR EL SEGIP
							// PARA ACTUALIZAR SU NUERO DE CARNET Y AGREGARLE EL CARACTER ESPECIAL (±)
							if ($personaAnterior->getSegipId() != 1)
							{
								$personaAnterior->setCarnet($personaAnterior->getCarnet().'±');
								$em->flush();
							}
							else
							{
								// SI EL CARNET ESTA OCUPADO POR OTRA PERSONA Y TAMBIEN VALIDADO POR EL SEGIP
								// NO REALIZAMOS EL REGISTRO Y MANDAMOS UN ERROR DE CARNET DUPLICADO
								$response = new JsonResponse();
								return $response->setData([
									'status'=>500,
									'msg'=>'Datos personales observado . No se pudo realizar el registro, comuníquese con su técnico de Distrito'
								]);
							}
						}
					}
					else
					{
						// LISTAMOS LOS REGISTROS SIN CARNET Y
						// GENERAMOS UN CARNET FICTICIO PARA EL NUEVO REGISTRO
						$personasSinCarnet = $em->createQueryBuilder()
											->select('p')
											->from('SieAppWebBundle:Persona','p')
											->where('p.carnet like :palabra')
											->setParameter('palabra','SC%')
											->getQuery()
											->getResult();

						$form['carnet'] = 'SC'. (count($personasSinCarnet) + 1);
					}
					// REGISTRAMOS LOS DATOS DE LA PERSONA
					$nuevaPersona = new Persona();
					//$nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find(98));
					$nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find(0));
					$nuevaPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['genero']));
					$nuevaPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find(7));
					$nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find(0));
					$nuevaPersona->setCarnet($form['carnet']);
					$nuevaPersona->setComplemento(mb_strtoupper($form['complemento'],'utf-8'));
					$nuevaPersona->setCelular($form['celular']);
					$nuevaPersona->setRda(0);
					$nuevaPersona->setPaterno(mb_strtoupper($form['paterno'],'utf-8'));
					$nuevaPersona->setMaterno(mb_strtoupper($form['materno'],'utf-8'));
					$nuevaPersona->setNombre(mb_strtoupper($form['nombre'],'utf-8'));
					if(isset($form['estado_civil'])){
						$nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find($form['estado_civil']));
					}
					if(isset($form['apellido_esposo'])){
						$nuevaPersona->setApellidoEsposo(mb_strtoupper($form['apellido_esposo'],'utf-8'));
					}                    
					$nuevaPersona->setCorreo($form['correo']);

					$nuevaPersona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
					if($form['idPersona'] == 'segip'){
						$nuevaPersona->setSegipId(1);
					}else{
						$nuevaPersona->setSegipId(0);
					}
					$nuevaPersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedido));
					$em->persist($nuevaPersona);
					$em->flush();

					$idPersona = $nuevaPersona->getId();
					
					// REGISTRAMOS LOS DATOS DE LA PERSONA
					$nuevaPersona = new Persona();
					//$nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find(98));
					$nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find(0));
					$nuevaPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['genero']));
					$nuevaPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find(7));
					$nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find(0));
					$nuevaPersona->setCarnet($form['carnet']);
					$nuevaPersona->setComplemento(mb_strtoupper($form['complemento'],'utf-8'));
					$nuevaPersona->setCelular($form['celular']);
					$nuevaPersona->setRda(0);
					$nuevaPersona->setPaterno(mb_strtoupper($form['paterno'],'utf-8'));
					$nuevaPersona->setMaterno(mb_strtoupper($form['materno'],'utf-8'));
					$nuevaPersona->setNombre(mb_strtoupper($form['nombre'],'utf-8'));
					if(isset($form['estado_civil']))
					{
						$nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find($form['estado_civil']));
					}
					if(isset($form['apellido_esposo']))
					{
						$nuevaPersona->setApellidoEsposo(mb_strtoupper($form['apellido_esposo'],'utf-8'));
					}                    
					$nuevaPersona->setCorreo($form['correo']);

					$nuevaPersona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
					if($form['idPersona'] == 'segip'){
						$nuevaPersona->setSegipId(1);
					}else{
						$nuevaPersona->setSegipId(0);
					}
					$nuevaPersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedido));
					$em->persist($nuevaPersona);
					$em->flush();

					$idPersona = $nuevaPersona->getId();
					*/
					
					$fecha = str_replace('-','/',$form['fechaNacimiento']);
					$complemento = $form['complemento'] == ''? '':$form['complemento'];
					$arrayDatosPersona = array(
						//'carnet'=>$form['carnet'],
						'complemento'=>$complemento,
						'paterno'=>$form['paterno'],
						'materno'=>$form['materno'],
						'nombre'=>$form['nombre'],
						'fecha_nacimiento' => $fecha
					);
					if(isset($form['extranjero']))
						$arrayDatosPersona['extranjero'] = 'extranjero';
					
					$personaValida = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($form['carnet'], $arrayDatosPersona, 'prod', 'academico');

					if($personaValida)//verificamos que se persona valida por segip
					{
						// REGISTRAMOS LOS DATOS DE LA PERSONA
						$nuevaPersona = new Persona();
						//$nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find(98));
						$nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find(0));
						$nuevaPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['genero']));
						$nuevaPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find(7));
						$nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find(0));
						$nuevaPersona->setCarnet($form['carnet']);
						$nuevaPersona->setComplemento(mb_strtoupper($form['complemento'],'utf-8'));
						$nuevaPersona->setCelular($form['celular']);
						$nuevaPersona->setRda(0);
						$nuevaPersona->setPaterno(mb_strtoupper($form['paterno'],'utf-8'));
						$nuevaPersona->setMaterno(mb_strtoupper($form['materno'],'utf-8'));
						$nuevaPersona->setNombre(mb_strtoupper($form['nombre'],'utf-8'));
						if(isset($form['cedulaTipoId']))
						{
							$nuevaPersona->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find($form['cedulaTipoId']));
						}
						if(isset($form['estado_civil']))
						{
							$nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find($form['estado_civil']));
						}
						if(isset($form['apellido_esposo']))
						{
							$nuevaPersona->setApellidoEsposo(mb_strtoupper($form['apellido_esposo'],'utf-8'));
						}
						$nuevaPersona->setCorreo($form['correo']);
						$nuevaPersona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
						/*if($form['idPersona'] == 'segip'){
							$nuevaPersona->setSegipId(1);
						}else{
							$nuevaPersona->setSegipId(1);
						}*/
						$nuevaPersona->setSegipId(1);
						$nuevaPersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedido));
						$em->persist($nuevaPersona);
						$em->flush();
						$idPersona = $nuevaPersona->getId();
					}
					else
					{
						$response = new JsonResponse();
						return $response->setData([
							'status'=>404,
							'msg'=>'Los datos no son validos según SEGIP.'
						]);
					}
				}
			}
			else
			{
				/*----------  SI LA PERSONA EXISTE EN LA BASE DIRECTAMENTE  ----------*/
				// MODIFICAMOS LOS DATOS DE LA PERSONA
				$actualizarPersona = $em->getRepository('SieAppWebBundle:Persona')->find($form['idPersona']);
				if($actualizarPersona)
				{
					// ACTUALIZAMOS LOS DATOS DE LA PERSONA
					$actualizarPersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedido));
					$actualizarPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['genero']));
					$actualizarPersona->setCorreo($form['correo']);
					$actualizarPersona->setCelular($form['celular']);
					if(isset($form['cedulaTipoId']))
					{
						$actualizarPersona->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find($form['cedulaTipoId']));
					}
					

					if(isset($form['estado_civil']))
					{
						$actualizarPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find($form['estado_civil']));
					}
					if(isset($form['apellido_esposo']))
					{
						$actualizarPersona->setApellidoEsposo(mb_strtoupper($form['apellido_esposo'],'utf-8'));
					}
					$em->flush();
				}
				$idPersona = $actualizarPersona->getId();
			}


			// Verficamos si el registro de apoderado es nuevo
			// if($form['id'] == 'nuevo'){
			//     $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_apoderado_inscripcion');")->execute();
			//     $nuevoApoderado = new RudeApoderadoInscripcion();
			//     $nuevoApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['apoderadoTipo']));
			//     $nuevoApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
			//     $nuevoApoderado->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['idInscripcion']));
			//     $nuevoApoderado->setIdiomaMaternoTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idiomaMaterno']));
			//     $nuevoApoderado->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($form['instruccionTipo']));
			//     $nuevoApoderado->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($form['ocupacion']));
			//     $nuevoApoderado->setObs(mb_strtoupper($form['obs'],'utf-8'));
			//     $em->persist($nuevoApoderado);
			//     $em->flush();

			//     $idApoderadoInscripcion = $nuevoApoderado->getId();
			// }else{

			//     $actualizarApoderado = $em->getRepository('SieAppWebBundle:RudeApoderadoInscripcion')->find($form['id']);
			//     if($actualizarApoderado){
			//         $actualizarApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['apoderadoTipo']));
			//         $actualizarApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
			//         $actualizarApoderado->setIdiomaMaternoTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idiomaMaterno']));
			//         $actualizarApoderado->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($form['instruccionTipo']));
			//         $actualizarApoderado->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($form['ocupacion']));
			//         $actualizarApoderado->setObs(mb_strtoupper($form['obs'],'utf-8'));
			//         $em->flush();
			//     }
			//     $idApoderadoInscripcion = $actualizarApoderado->getId();
			// }



			// VERFICAMOS SI EL REGISTRO DE APODERADO ES NUEVO LO REGISTRAMOS
			if($form['id'] == 'nuevo'){
				$nuevoApoderado = new ApoderadoInscripcion();
				$nuevoApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['apoderadoTipo']));
				$nuevoApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
				$nuevoApoderado->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['idInscripcion']));
				$nuevoApoderado->setFechaRegistro(new \DateTime('now'));
				$em->persist($nuevoApoderado);
				$em->flush();

				$idApoderadoInscripcion = $nuevoApoderado->getId();
			}else{
				// ACTUALIZAMOS EL DATO DE INSCRIPCION DEL APODERADO
				$actualizarApoderado = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($form['id']);
				if($actualizarApoderado){
					$actualizarApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['apoderadoTipo']));
					$actualizarApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
					$em->flush();
				}
				$idApoderadoInscripcion = $actualizarApoderado->getId();
			}

			// VERIFICAMOS SI EL REGISTRO DE DATOS DE APODERADO ES NUEVO

			if($form['idDatos'] == 'nuevo'){
				$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('apoderado_inscripcion_datos');")->execute();
				$nuevoApoderadoDatos = new ApoderadoInscripcionDatos();
				$nuevoApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idiomaMaterno']));
				$nuevoApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($form['instruccionTipo']));
				$nuevoApoderadoDatos->setApoderadoInscripcion($em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($idApoderadoInscripcion));
				$nuevoApoderadoDatos->setTelefono($form['telefono']);
				$nuevoApoderadoDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($form['ocupacion']));
				$nuevoApoderadoDatos->setEmpleo(mb_strtoupper($form['empleo'],'utf-8'));
				// $nuevoApoderadoDatos->setObs(mb_strtoupper($form['obs'],'utf-8'));
				$em->persist($nuevoApoderadoDatos);
				$em->flush();

				$idApoderadoInscripcionDatos = $nuevoApoderadoDatos->getId();
			}else{
				// ACTUALIZAMOS EL REGISTRO DE DATOS DEL APODERADO
				$actualizarApoderadoDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->find($form['idDatos']);
				if($actualizarApoderadoDatos){
					$actualizarApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idiomaMaterno']));
					$actualizarApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($form['instruccionTipo']));
					$actualizarApoderadoDatos->setTelefono($form['telefono']);
					$actualizarApoderadoDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($form['ocupacion']));
					$actualizarApoderadoDatos->setEmpleo(mb_strtoupper($form['empleo'],'utf-8'));
					// $actualizarApoderadoDatos->setObs(mb_strtoupper($form['obs'],'utf-8'));
					$em->flush();

					$idApoderadoInscripcionDatos = $actualizarApoderadoDatos->getId();
				}
			}

		}
		else
		{

			// SI SE INDICA QUE EL ESTUDIANTE NO TIENE ASIGNADO EL APODERADO
			// SE ELIMINA EL REGISTRO DE APODERADO 
			
			// $apod = $em->getRepository('SieAppWebBundle:RudeApoderadoInscripcion')->findBy(array('estudianteInscripcion'=>$form['idInscripcion'],'apoderadoTipo'=>$parentesco));
			$apod = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion'=>$form['idInscripcion'],'apoderadoTipo'=>$parentesco));
			foreach ($apod as $a) {
				$eliminar = $em->createQueryBuilder()
					->delete('')
					->from('SieAppWebBundle:ApoderadoInscripcionDatos','aid')
					->where('aid.apoderadoInscripcion = :apoderadoInscripcion')
					->setParameter('apoderadoInscripcion', $a->getId())
					->getQuery()
					->getResult();

				$em->remove($a);
				$em->flush();
			}

			$idApoderadoInscripcion = 'nuevo';
			$idApoderadoInscripcionDatos = 'nuevo';
			$idPersona = 'nuevo';
		}

		// AGREGAMOS LA VALIDACION EN EL UTIMO PASO DE ASIGNAR TUTOR
		// PARA QUE SE VALIDE QUE SE REGISTREN LOS DATOS DEL PADRE, MADRE O TUTOR
		// SEGUN LO QUE SE INDICO EN EL APARTADO - CON QUIEN VIVE

		if($tipo == 'tutor'){
			// APODERADOS QUE TIENE ACTUALMENTE REGISTRADOS EL ESTUDIANTE
			$apoderados = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array(
				'estudianteInscripcion'=>$form['idInscripcion']
			));
			$tiposApoderados = [];
			foreach ($apoderados as $ap) {
				$tiposApoderados[] = $ap->getApoderadoTipo()->getId();
			}


			$catalogo = $this->obtenerCatalogo($rude, 'apoderado_tipo');
			$variable = (in_array(3, $this->obtenerCatalogo($rude, 'apoderado_tipo')))?true:false;

			// VALIDACION CON QUIEN VIVE EL ESTUDIANTE
			$status = 200;
			if($rude->getViveHabitualmenteTipo() != null){
				switch ($rude->getViveHabitualmenteTipo()->getId()) {
					case 1: // PADRE Y MADRE
						if(!in_array(1, $tiposApoderados) or !in_array(2, $tiposApoderados) ){
							$msg = "Debe registrar los datos del padre y la madre";
							$status = 500;
						}
						break;
					case 2: // SOLO PADRE 
						if(!in_array(1, $tiposApoderados)){
							$msg = "Debe registrar los datos del padre";
							$status = 500;
						}
						break;
					case 3: // SOLO MADRE
						if(!in_array(2, $tiposApoderados) ){
							$msg = "Debe registrar los datos de la madre";
							$status = 500;
						}
						break;
					case 4: // TUTOR
						$apoderadosTutores = $this->obtenerCatalogo($rude, 'apoderado_tipo');
						$tieneApoderado = false;
						foreach ($apoderadosTutores as $key => $value) {
							if (in_array($value, $tiposApoderados )) {
								$tieneApoderado = true;
							}
						}

						if(!$tieneApoderado){
							$msg = "Debe registrar datos del tutor";
							$status = 500;
						}
						break;
				}
			}else{
				$status = 500;
				$msg = "Debe especificar con quien vive el o la estudiante!!!";
			}

			if($status == 500){
				$response = new JsonResponse();
				return $response->setData([
					'status'=>$status,
					'msg'=>$msg
				]);
			}else{
				// HACEMOS EL REGISTRO PASO 4 
				// Y HABILITAMOS EL SIGUINTE PASO
				if($rude->getRegistroFinalizado() < 4){
					$rude->setRegistroFinalizado(4);
					$em->flush();
				}
			}
		}
		
		if($rude->getRegistroFinalizado() < 7){
			$rude->setRegistroFinalizado(7);
		}

		$em->flush();

		$response = new JsonResponse();
		return $response->setData([
			'status'=>200,
			'id'=>$idApoderadoInscripcion,
			'idDatos'=>$idApoderadoInscripcionDatos,
			'idPersona'=>$idPersona
		]);
	}

	public function createFormLugar($rude)
	{
		if($rude->getFechaRegistroRude()){
			$fecha = $rude->getFechaRegistroRude()->format('d-m-Y');
		}else{
			$fecha = '';
		}
		$form = $this->createFormBuilder($rude)
					->add('id', 'hidden')
					->add('lugarRegistroRude', 'text', array('required' => true))
					->add('fechaRegistroRude', 'text', array('required' => true, 'data'=>$fecha))             
					->getForm();

		return $form;
	}

	public function saveFormLugarAction(Request $request)
	{	
		$em = $this->getDoctrine()->getManager();
		$form = $request->get('form'); 
		$rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['id']);
		$rude->setLugarRegistroRude(mb_strtoupper($form['lugarRegistroRude'],'utf-8'));
		$rude->setFechaRegistroRude(new \DateTime($form['fechaRegistroRude']));
		// Registro paso 5
		//dump($rude->getRegistroFinalizado());die;
		if($rude->getRegistroFinalizado() <= 5){
			$rude->setRegistroFinalizado(5);
		}

		$em->flush();

		$response = new JsonResponse();
		return $response->setData(['msg'=>'ok']);
	}

	public function obtenerCatalogo($rude, $tabla)
	{	
		$em = $this->getDoctrine()->getManager();
		$gestion = $rude->getEstudianteInscripcion()->getInstitucioneducativaCurso()->getGestionTipo()->getId();
		$gestion = 2019;
		/**
		 * OBTENER CATALOGO
		 */
		switch ($tabla) {
			case 'apoderado_tipo':				
				$gestion = 2022;//Se lleno la tabla rude_catalogo para 2022
				$catalogo = $em->createQueryBuilder()
									->select('rc')
									->from('SieAppWebBundle:RudeCatalogo','rc')
									->where('rc.gestionTipo = :gestion')
									->andWhere('rc.institucioneducativaTipo = 4')
									->andWhere('rc.nombreTabla = :tabla')
									->setParameter('gestion', $gestion)
									->setParameter('tabla', $tabla)
									->getQuery()
									->getResult();
				$ids = [];
				foreach ($catalogo as $c) {
					$ids[] = $c->getLlaveTabla();
				}
			break;
			case 'dificultad_aprendizaje_tipo':
				$gestion = 2022;//Se lleno la tabla rude_catalogo para 2022
				$catalogo = $em->createQueryBuilder()
									->select('rc')
									->from('SieAppWebBundle:RudeCatalogo','rc')
									->where('rc.gestionTipo = :gestion')
									->andWhere('rc.institucioneducativaTipo = 4')
									->andWhere('rc.nombreTabla = :tabla')
									->andWhere('rc.esVigente = :vigente')
									->setParameter('gestion', $gestion)
									->setParameter('tabla', $tabla)
									->setParameter('vigente', 'TRUE')
									->getQuery()
									->getResult();
				$ids = [];
				foreach ($catalogo as $c) {
					$ids[] = $c->getLlaveTabla();
				}
			break;
			case 'talento_extraordinario_tipo':
				$gestion = 2022;//Se lleno la tabla rude_catalogo para 2022
				$catalogo = $em->createQueryBuilder()
									->select('rc')
									->from('SieAppWebBundle:RudeCatalogo','rc')
									->where('rc.gestionTipo = :gestion')
									->andWhere('rc.institucioneducativaTipo = 4')
									->andWhere('rc.nombreTabla = :tabla')
									->andWhere('rc.esVigente = :vigente')
									->setParameter('gestion', $gestion)
									->setParameter('tabla', $tabla)
									->setParameter('vigente', 'TRUE')
									->getQuery()
									->getResult();
				$ids = [];
				foreach ($catalogo as $c) {
					$ids[] = $c->getLlaveTabla();
				}
				break;
			case 'estrategia_atencion_integral_tipo':
				$gestion = 2022;//Se lleno la tabla rude_catalogo para 2022
				$catalogo = $em->createQueryBuilder()
									->select('rc')
									->from('SieAppWebBundle:RudeCatalogo','rc')
									->where('rc.gestionTipo = :gestion')
									->andWhere('rc.institucioneducativaTipo = 4')
									->andWhere('rc.nombreTabla = :tabla')
									->setParameter('gestion', $gestion)
									->setParameter('tabla', $tabla)
									->getQuery()
									->getResult();
				$ids = [];
				foreach ($catalogo as $c) {
					$ids[] = $c->getLlaveTabla();
				}
				break;
			case 'acceso_internet_tipo':
				$gestion = 2022;//Se lleno la tabla rude_catalogo para 2022
				$catalogo = $em->createQueryBuilder()
									->select('rc')
									->from('SieAppWebBundle:RudeCatalogo','rc')
									->where('rc.gestionTipo = :gestion')
									->andWhere('rc.institucioneducativaTipo = 4')
									->andWhere('rc.nombreTabla = :tabla')
									->setParameter('gestion', $gestion)
									->setParameter('tabla', $tabla)
									->getQuery()
									->getResult();
				$ids = [];
				foreach ($catalogo as $c) {
					$ids[] = $c->getLlaveTabla();
				}
				break;						
			default:				
				$catalogo = $em->createQueryBuilder()
									->select('rc')
									->from('SieAppWebBundle:RudeCatalogo','rc')
									->where('rc.gestionTipo = :gestion')
									->andWhere('rc.institucioneducativaTipo = 1')
									->andWhere('rc.nombreTabla = :tabla')
									->setParameter('gestion', $gestion)
									->setParameter('tabla', $tabla)
									->getQuery()
									->getResult();
				$ids = [];
				foreach ($catalogo as $c) {
					$ids[] = $c->getLlaveTabla();
				}
				break;
		}	
		
		return $ids;
	}

	public function obtenerCatalogoTipoDiscapacidad()
	{
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "select * from discapacidad_tipo where es_vigente='t' and origendiscapacidad in ('Auditiva','Visual','Intelectual','Física/Motora','Múltiple','Mental Psiquica');";
        $stmt = $db->prepare($query);
        //$params = array();
        //$stmt->execute($params);
        $stmt->execute();
        $catalogo=$stmt->fetchAll();

		$ids = [];
		foreach ($catalogo as $c)
		{
			$ids[] = $c['id'];
		}
		return $ids;
	}

	public function obtenerCatalogoDiscapacidadOrigen()
	{
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "
			select * from discapacidad_origen_tipo where id between 1 and 2 ORDER BY id asc
		";
        $stmt = $db->prepare($query);
        //$params = array();
        //$stmt->execute($params);
        $stmt->execute();
        $catalogo=$stmt->fetchAll();

		$ids = [];
		foreach ($catalogo as $c)
		{
			$ids[] = $c['id'];
		}
		return $ids;
	}
	/**
     * DESCARGA DE RUDE - ACADEMICO
     */
    public function downloadRudeEspecialAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        //get the data send to the report
        //get the values to build the report
        $codue = $request->get('codue');
        $rude = $request->get('rude');
        $gestion = $request->get('gestion');
        $eins = $request->get('eins');		
        $socioregIdEntity = $em->getRepository('SieAppWebBundle:Rude')->findOneByEstudianteInscripcion($eins);
		//	dump($socioregIdEntity->getMunicipioLugarTipo()->getId());die;
        //$idlocalidad = $socioregIdEntity->getMunicipioLugarTipo();
		$idlocalidad = $socioregIdEntity->getMunicipioLugarTipo()->getId();
        //dump($idlocalidad);die;
        $query = "select socioeconomico_lugar_recursivo(".$idlocalidad.");";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $countdir = count($po);

        $porciones = explode("|", $po[0]['socioeconomico_lugar_recursivo']);
		//dump($porciones);die;
        $dirProv = $porciones[3];
        $dirMun = $porciones[4];
       // dump($codue,$rude,$gestion,$eins ,$dirProv,$dirMun);die;
        //get the values of report
        //create the response object to down load the file
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'RUDE_' .$rude. '_' .$gestion. '.pdf'));
		if ($gestion<2022){
        	$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rude2017.rptdesign&codue=' . $codue .'&rude='. $rude .'&gestion=' . $gestion .'&eins='. $eins .'&dirMun='. $dirMun .'&dirProv='. $dirProv . '&&__format=pdf&'));
		}else{
        	$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rudeEs2022.rptdesign&codue=' . $codue .'&rude='. $rude .'&gestion=' . $gestion .'&eins='. $eins .'&dirMun='. $dirMun .'&dirProv='. $dirProv . '&&__format=pdf&'));
		}
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
	//Reporte Rude opcion 2
	public function downloadRudEEAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        //get the data send to the report
        //get the values to build the report
        $codue = $request->get('codue');
        $rude = $request->get('rude');
        $gestion = $request->get('gestion');
        $eins = $request->get('eins');		
        $socioregIdEntity = $em->getRepository('SieAppWebBundle:Rude')->findOneByEstudianteInscripcion($eins);		
		$idlocalidad = $socioregIdEntity->getMunicipioLugarTipo()->getId();
        //dump($idlocalidad);die;
        $query = "select socioeconomico_lugar_recursivo(".$idlocalidad.");";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $countdir = count($po);

        $porciones = explode("|", $po[0]['socioeconomico_lugar_recursivo']);
		//dump($porciones);die;
        $dirProv = $porciones[3];
        $dirMun = $porciones[4];
       // dump($codue,$rude,$gestion,$eins ,$dirProv,$dirMun);die;
        //////////////////TCPDF/////////////////////////		
	
		$gestionId = $this->session->get('currentyear');
		//RECUPERAMOS LOS DATOS DEL DIRECTOR  O ENCARGADO DL CENTRO
		
		$pdf = $this->container->get("white_october.tcpdf")->create(
			'PORTRATE', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true
		);      
		$pdf->SetAuthor('Lupita');
		$pdf->SetTitle('Reporte RUDEES');
		$pdf->SetSubject('Report PDF');
		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(true, -10);		
		$pdf->SetKeywords('TCPDF, PDF, COMPROBANTE');
		$pdf->setFontSubsetting(true);
		$pdf->SetMargins(10, 15, 10, true);
		$pdf->SetAutoPageBreak(true, 8);
		$pdf->SetFont('helvetica', '', 9, '', true);
		$pdf->AddPage('P', array(215, 325)); 
		$image_path = base64_decode('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');
		$pdf->Image('@'.$image_path, 9, 9, 30, 24, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);		
		$pdf->Ln(2);
		$pdf->SetFont('helvetica', 'B', 13);
		$pdf->Cell(0, 2, 'FORMULARIO RUDEES', 0, 1, 'C');
		$pdf->SetFont('helvetica', 'B', 11);
		$pdf->SetTextColor(55, 55, 55);
		$pdf->Cell(0, 2, 'REGISTRO ÚNICO DE ESTUDIANTES DE EDUCACIÓN ESPECIAL', 0, 1, 'C');
		$pdf->SetFont('helvetica', 'B', 8);		
		$pdf->Cell(0, 2, 'Resolución Ministerial Nº ....', 0, 1, 'C');
		$pdf->SetFont('helvetica', 'B', 5);	
		$pdf->Cell(0, 2, 'LA INFORMACIÓN RECABADA POR EL RUDEES SERÁ UTILIZADA ÚNICA Y EXCLUSIVAMENTE PARA FINES DE DISEÑO Y EJECUCIÓN DE POLÍTICAS PÚBLICAS EDUCATIVAS Y SOCIALES', 0, 1, 'C');
		$pdf->Ln(5);
		$pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(20, 5, '', 0, 0, 'C');
		$contenidoMencion='<table border="1"> ';
		$contenidoMencion.='<tr><td width="50%">&nbsp; Importante: El formulario debe ser llenado por el padre, madre o tutor, considerando lo siguiente:
			(*)Estos campos seran llenados por la Unidad Educativa
			(**)Estos campos requieren la presentación del documento al que hace referencia
			(?)En el reverso del formulario se incluyen aclaraciones de ayuda para el llenado de estos campos</td><td  width="50.4%">';
		$contenidoMencion.='Revise el manual del llenado, en el final del formulario.';
		$contenidoMencion.='</td></tr>';
		$contenidoMencion.='</table>';
		$pdf->writeHTML($contenidoMencion, true, false, true, false, '');
	    $pdf->Output("FormularioRudees".date('YmdHis').".pdf", 'I');    
		//$pdf->Output("FormularioRudees".date('YmdHis').".pdf", 'D');
		die();
    }
}
