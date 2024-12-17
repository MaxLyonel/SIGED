<?php

namespace Sie\HerramientaBundle\Controller;


use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Controller\WfTramiteController;
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLogTipo;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteInscripcionHumnisticoTecnico;

/**
 * BTH Regularizacion TTG TTE mediante RM controller.
 *
 */
class BthRegularizacionTtgTteController extends Controller {
    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
    }
    public function indexAction (Request $request) 
    {   
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $gestion = $em->getRepository('SieAppWebBundle:GestionTipo')->findBy(['id' => [2021, 2022]]);
        return $this->render('SieHerramientaBundle:BthRegularizacionTtgTte:index.html.twig', array('gestion' => $gestion));
    }
    public function busquedaSieAction (Request $request)
    {   
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $data = $request->request->all();
        $sie = $data["sie"];
		$sie = filter_var($sie,FILTER_SANITIZE_NUMBER_INT);
		$sie = intval(is_numeric($sie)?$sie:-1);
        
        $gestion = $data["gestion"];
		$gestion = filter_var($gestion,FILTER_SANITIZE_NUMBER_INT);
		$gestion = intval(is_numeric($gestion)?$gestion:-1);
        
        $em = $this->getDoctrine()->getManager();

        $institucion = array();
        $turno = array();
        $status = 200;
        $msj = '';
        // if ($gestion == 2022){
        //     $ueAutorizada= [81390028]; 
        // }
        
        // if ($gestion == 2021){
        //     $ueAutorizada= [81110030,62460063]; 
        // }
        $query = $em->getConnection()->prepare("
            select institucioneducativa_id, gestion_tipo_id, grados, especialidades 
            from tmp_bth_regularizacion_cal_tt t
            where t.institucioneducativa_id = ".$sie."
            and t.gestion_tipo_id = ".$gestion.";");
        $query->execute();
        $ueAutorizada = $query->fetchAll();

        if (count($ueAutorizada)==0){
            $status = 400;
            $msj = 'La Unidad Educativa con codigo SIE : '.$sie.' no esta autorizada para esa gestión. Favor verificar...';
            return new JsonResponse(array(
                'status' => $status,
                'institucion' => $institucion,
                'turno' => $turno,
                'msj' => $msj,
            ));
        }

        $grados = $ueAutorizada[0]['grados'];
        // $ueAutorizada= [81430046,81430004,81390028,81390007,81400072,51450055,71380001,81110030]; 
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($sie);
        $institucion = array(
            'sie'=>$institucioneducativa->getId(),
            'institucioneducativa' =>$institucioneducativa->getInstitucioneducativa(),
            'distrito'=>$institucioneducativa->getLeJuridicciongeografica()->getDistritoTipo()->getDistrito(),
        );
        $grados = 
        $query = $em->getConnection()->prepare("
            select distinct ic.grado_tipo_id gradoId, gt.grado 
            from institucioneducativa_curso ic 
            inner join institucioneducativa_curso_oferta ico on ic.id = ico.insitucioneducativa_curso_id 
            inner join nivel_tipo nt on ic.nivel_tipo_id = nt.id
            inner join grado_tipo gt on ic.grado_tipo_id = gt.id
            where ic.institucioneducativa_id = ".$sie." 
            and ic.gestion_tipo_id = ".$gestion." 
            and ic.grado_tipo_id in (".$grados.")
            and ic.nivel_tipo_id = 13
            and ico.asignatura_tipo_id in (1038,1039)
            order by ic.grado_tipo_id;");
        $query->execute();
        $grado = $query->fetchAll();
        
        if (count($grado)==0){
            $institucion = array();
            $status = 400;
            $msj = 'La Unidad Educativa con codigo SIE : '.$sie.' no presenta el área ttg o tte a regularizar. Favor verificar...';
            return new JsonResponse(array(
                'status' => $status,
                'institucion' => $institucion,
                'grado' => $grado,
                'msj' => $msj,
            ));
        }
        
        return new JsonResponse(array(
            'status' => $status,
            'institucion' => $institucion,
            'grado' => $grado,
            'msj' => $msj,
        ));
    }

    public function listaParalelosAction(Request $request) {
        // $turnoId = $request->get('turno');       
        $nivelId = $request->get('nivel');        
        $gradoId = $request->get('grado');        
        $institucion = $request->get('sie');
        $gestion = $request->get('gestion');  
        $response = new JsonResponse();
        return $response->setData(array(
                'paralelos' => $this->getParaleloInstitucionEducativaCurso($institucion, $gestion, $nivelId, $gradoId),
            ));
    }

    public function getParaleloInstitucionEducativaCurso($institucionEducativaId, $gestionId, $nivelId, $gradoId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct pt.id, pt.paralelo")
                ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'pt.id = iec.paraleloTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                // ->andWhere('iec.turnoTipo = :turnoId')
                ->andWhere('iec.nivelTipo = :nivelId')
                ->andWhere('iec.gradoTipo = :gradoId')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                // ->setParameter('turnoId', $turnoId)
                ->setParameter('nivelId', $nivelId)
                ->setParameter('gradoId', $gradoId)
                ->orderBy('pt.id', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    public function listaTurnosAction(Request $request) {
        // $turnoId = $request->get('turno');       
        $nivelId = $request->get('nivel');   
        $gradoId = $request->get('grado');
        $paraleloId = $request->get('paralelo');           
        $institucion = $request->get('sie');
        $gestion = $request->get('gestion');  
        
        $response = new JsonResponse();
        return $response->setData(array(
                'turnos' => $this->getTurnoInstitucionEducativaCurso($institucion, $gestion, $paraleloId, $gradoId, $nivelId),
            ));
    }

    public function getTurnoInstitucionEducativaCurso($institucionEducativaId, $gestionId, $paraleloId, $gradoId, $nivelId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct tt.id, tt.turno")
                ->innerJoin('SieAppWebBundle:TurnoTipo', 'tt', 'WITH', 'tt.id = iec.turnoTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                ->andWhere('iec.nivelTipo = :nivelId')
                ->andWhere('iec.gradoTipo = :gradoId')
                ->andWhere('iec.paraleloTipo = :paraleloId')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                ->setParameter('nivelId', $nivelId)
                ->setParameter('gradoId', $gradoId)
                ->setParameter('paraleloId', $paraleloId)
                ->orderBy('tt.id', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    public function busquedaEstudiantesAction(Request $request){
        
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $data = $request->request->all();
        $sie = $data["sie"];
		$sie = filter_var($sie,FILTER_SANITIZE_NUMBER_INT);
		$sie = intval(is_numeric($sie)?$sie:-1);
        
        $gestion = $data["gestion"];
		$gestion = filter_var($gestion,FILTER_SANITIZE_NUMBER_INT);
		$gestion = intval(is_numeric($gestion)?$gestion:-1);

        $turno = $data["turno"];
		$turno = filter_var($turno,FILTER_SANITIZE_NUMBER_INT);
		$turno = intval(is_numeric($turno)?$turno:-1);
        
        $nivel = $data["nivel"];
		$nivel = filter_var($nivel,FILTER_SANITIZE_NUMBER_INT);
		$nivel = intval(is_numeric($nivel)?$nivel:-1);
        
        $grado = $data["grado"];
		$grado = filter_var($grado,FILTER_SANITIZE_NUMBER_INT);
		$grado = intval(is_numeric($grado)?$grado:-1);

        $paralelo = $data["paralelo"];
		$paralelo = filter_var($paralelo,FILTER_SANITIZE_NUMBER_INT);
		$paralelo = is_numeric($paralelo)?$paralelo:-1;

        $status = 200;
        $msj = '';
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select distinct ei.id eiId, e.codigo_rude, e.nombre, e.paterno, e.materno, e.carnet_identidad, e.complemento, ico.id icoId, ea.id eaId 
            from estudiante e 
            inner join estudiante_inscripcion ei on ei.estudiante_id = e.id
            inner join institucioneducativa_curso ic on ic.id = ei.institucioneducativa_curso_id 
            inner join institucioneducativa_curso_oferta ico on ic.id = ico.insitucioneducativa_curso_id 
            left join estudiante_asignatura ea on ei.id = ea.estudiante_inscripcion_id and ico.id = ea.institucioneducativa_curso_oferta_id
            where ic.institucioneducativa_id = ".$sie."
            and ei.estadomatricula_tipo_id = 5
            and ic.gestion_tipo_id = ".$gestion."
            and ic.nivel_tipo_id = ".$nivel."
            and ic.grado_tipo_id = ".$grado."
            and ic.paralelo_tipo_id = '".$paralelo."'
            and ic.turno_tipo_id = ".$turno."
            and ico.asignatura_tipo_id in (1038,1039)
            and ea.id is null ;");
        $query->execute();
        $estudiante = $query->fetchAll();
        $curso = array();
        $especialidad = array();
        
        if (count($estudiante)==0){
            $status=400;
            $msj = "No tienes estudiantes a regularizar";
        } else {
            $query = $em->getConnection()->prepare("
                select distinct ic.gestion_tipo_id gestion, ic.nivel_tipo_id nivelId, nt.nivel, ic.turno_tipo_id turnoId, tt.turno, ic.grado_tipo_id gradoId, gt.grado,
                ic.paralelo_tipo_id paraleloId, pt.paralelo, ico.asignatura_tipo_id asignaturaId, at2.asignatura, ico.id icoId
                from institucioneducativa_curso ic 
                inner join institucioneducativa_curso_oferta ico on ic.id = ico.insitucioneducativa_curso_id 
                inner join nivel_tipo nt on ic.nivel_tipo_id = nt.id 
                inner join turno_tipo tt on ic.turno_tipo_id = tt.id
                inner join grado_tipo gt on ic.grado_tipo_id = gt.id 
                inner join paralelo_tipo pt on ic.paralelo_tipo_id = pt.id 
                inner join asignatura_tipo at2 on ico.asignatura_tipo_id = at2.id
                where ic.institucioneducativa_id = ".$sie."
                and ic.gestion_tipo_id = ".$gestion."
                and ic.grado_tipo_id = ".$grado."
                and ic.nivel_tipo_id = ".$nivel."
                and ic.turno_tipo_id = ".$turno."
                and ic.paralelo_tipo_id = '".$paralelo."'
                and ico.asignatura_tipo_id in (1038,1039)
                limit 1;");
            $query->execute();
            $curso = $query->fetchAll();
            $curso = $curso[0];
        } 

        if ($grado == 5){
            $query = $em->getConnection()->prepare("
                select id, especialidad 
                from especialidad_tecnico_humanistico_tipo etht 
                where etht.id in (
                select CAST(unnest(string_to_array(especialidades,',')) as int) esp 
                from tmp_bth_regularizacion_cal_tt tbrct
                where tbrct.institucioneducativa_id= ".$sie."
                and tbrct.gestion_tipo_id = ".$gestion."
                order by 1);");
            $query->execute();
            $especialidad = $query->fetchAll();
        }
        
        return $this->render('SieHerramientaBundle:BthRegularizacionTtgTte:datoEst.html.twig', array('status' => $status, 'msj' => $msj, 'estudiante' => $estudiante, 'curso' => $curso, 'especialidad' => $especialidad));

    }

    public function GuardaCalificacionesAction(Request $request){
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $data = json_decode($request->getContent(), true);
        $msj = '';
        $status = 200;
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $em->getConnection()->beginTransaction();   
        foreach ($data as $estudiante) {
            $gestionTipo =  $em->getRepository('SieAppWebBundle:GestionTipo')->find($estudiante['gestion']);
            $institucioneducativaCursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($estudiante['icoid']);
            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($estudiante['estudianteId']);
            
            $especialidadTipo='';
            $institucionHumanisticoEsp='';
            
            if ($institucioneducativaCursoOferta == null || $estudianteInscripcion == null) {
                $em->getConnection()->rollback();
                $msj = 'No se cuenta con alguna inscripcion o curso oferta. Comuniquese con su técnico';
                $status = 400; 
                return $response->setData(array('msj'=>$msj,
										'status'=>$status,
									));
            }

            $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array(
                'gestionTipo'=> $estudiante['gestion'],
                'estudianteInscripcion'=>$estudiante['estudianteId'],
                'institucioneducativaCursoOferta'=>$estudiante['icoid']
            ));
             
            if($estudianteAsignatura==null)
            {
                $estudianteAsignatura = new EstudianteAsignatura();
                $estudianteAsignatura->setGestionTipo($gestionTipo);
                $estudianteAsignatura->setFechaRegistro(new \DateTime(date('Y-m-d')));
                $estudianteAsignatura->setEstudianteInscripcion($estudianteInscripcion);
                $estudianteAsignatura->setInstitucioneducativaCursoOferta($institucioneducativaCursoOferta);
                $em->persist($estudianteAsignatura);
            }
			$notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->find(6);
			$estudianteNota 	= new EstudianteNota();
			$estudianteNota->setNotaTipo($notaTipo);
			$estudianteNota->setEstudianteAsignatura($estudianteAsignatura);
			$estudianteNota->setNotaCuantitativa($estudiante['tri1']);
			$estudianteNota->setNotaCualitativa('');
			$estudianteNota->setUsuarioId($this->session->get('userId'));
			$estudianteNota->setFechaRegistro(new \DateTime(date('Y-m-d')));
			$estudianteNota->setFechaModificacion(new \DateTime(date('Y-m-d')));
			$em->persist($estudianteNota);

            $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->find(7);
			$estudianteNota 	= new EstudianteNota();
			$estudianteNota->setNotaTipo($notaTipo);
			$estudianteNota->setEstudianteAsignatura($estudianteAsignatura);
			$estudianteNota->setNotaCuantitativa($estudiante['tri2']);
			$estudianteNota->setNotaCualitativa('');
			$estudianteNota->setUsuarioId($this->session->get('userId'));
			$estudianteNota->setFechaRegistro(new \DateTime(date('Y-m-d')));
			$estudianteNota->setFechaModificacion(new \DateTime(date('Y-m-d')));
			$em->persist($estudianteNota);

            $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->find(8);
			$estudianteNota 	= new EstudianteNota();
			$estudianteNota->setNotaTipo($notaTipo);
			$estudianteNota->setEstudianteAsignatura($estudianteAsignatura);
			$estudianteNota->setNotaCuantitativa($estudiante['tri3']);
			$estudianteNota->setNotaCualitativa('');
			$estudianteNota->setUsuarioId($this->session->get('userId'));
			$estudianteNota->setFechaRegistro(new \DateTime(date('Y-m-d')));
			$estudianteNota->setFechaModificacion(new \DateTime(date('Y-m-d')));
			$em->persist($estudianteNota);

            $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->find(9);
			$estudianteNota 	= new EstudianteNota();
			$estudianteNota->setNotaTipo($notaTipo);
			$estudianteNota->setEstudianteAsignatura($estudianteAsignatura);
			$estudianteNota->setNotaCuantitativa($estudiante['promedio']);
			$estudianteNota->setNotaCualitativa('');
			$estudianteNota->setUsuarioId($this->session->get('userId'));
			$estudianteNota->setFechaRegistro(new \DateTime(date('Y-m-d')));
			$estudianteNota->setFechaModificacion(new \DateTime(date('Y-m-d')));
			$em->persist($estudianteNota);

            if (isset($estudiante['especialidad'])){
                $especialidadTipo = $em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($estudiante['especialidad']);
                $institucionHumanisticoEsp = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->findOneBy(array(
                    'institucioneducativa'=>$estudiante['sie'],
                    'gestionTipo'=> $estudiante['gestion'],
                    'especialidadTecnicoHumanisticoTipo'=>$estudiante['especialidad']
                ));  
                
                $estInscHumTec	= new EstudianteInscripcionHumnisticoTecnico();
                
                $estInscHumTec->setInstitucioneducativaHumanistico($institucionHumanisticoEsp);
                $estInscHumTec->setEstudianteInscripcion($estudianteInscripcion);
                $estInscHumTec->setEspecialidadTecnicoHumanisticoTipo($especialidadTipo);
                $estInscHumTec->setHoras(0);
                $estInscHumTec->setEsvalido(false);
                $estInscHumTec->setObservacion('REGULARIZADO RM 394/2023');
                $estInscHumTec->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));
                $em->persist($estInscHumTec);
                
            }
        }
        $em->flush();
        $em->getConnection()->commit();
		$msj = 'Los datos fueron guardados correctamente.';
        return $response->setData(array('msj'=>$msj,
										'status'=>$status,
									));
    }

}