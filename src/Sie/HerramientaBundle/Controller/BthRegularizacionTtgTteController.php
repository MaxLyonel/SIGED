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
        if ($gestion == 2022){
            $ueAutorizada= [81390028]; 
        }
        
        if ($gestion == 2021){
            $ueAutorizada= [81110030,62460063]; 
        }
        // $ueAutorizada= [81430046,81430004,81390028,81390007,81400072,51450055,71380001,81110030]; 
        $status = 200;
        $msj = '';
        if (!in_array($sie,$ueAutorizada)){
            $status = 400;
            $msj = 'La Unidad Educativa con codigo SIE : '.$sie.' no esta autorizada para esa gestión. Favor verificar...';
            return new JsonResponse(array(
                'status' => $status,
                'institucion' => $institucion,
                'turno' => $turno,
                'msj' => $msj,
            ));
        }

        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($sie);
        $institucion = array(
            'sie'=>$institucioneducativa->getId(),
            'institucioneducativa' =>$institucioneducativa->getInstitucioneducativa(),
            'distrito'=>$institucioneducativa->getLeJuridicciongeografica()->getDistritoTipo()->getDistrito(),
        );
        $query = $em->getConnection()->prepare("
            select distinct ic.turno_tipo_id turnoId, tt.turno 
            from institucioneducativa_curso ic 
            inner join institucioneducativa_curso_oferta ico on ic.id = ico.insitucioneducativa_curso_id 
            inner join nivel_tipo nt on ic.nivel_tipo_id = nt.id
            inner join turno_tipo tt on ic.turno_tipo_id = tt.id
            where ic.institucioneducativa_id = ".$sie." 
            and ic.gestion_tipo_id = ".$gestion." 
            and ic.grado_tipo_id in (3,4,5)
            and ic.nivel_tipo_id = 13
            and ico.asignatura_tipo_id in (1038,1039);");
        $query->execute();
        $turno = $query->fetchAll();
        
        if (count($turno)==0){
            $institucion = array();
            $status = 400;
            $msj = 'La Unidad Educativa con codigo SIE : '.$sie.' no presenta el área ttg o tte a regularizar. Favor verificar...';
            return new JsonResponse(array(
                'status' => $status,
                'institucion' => $institucion,
                'turno' => $turno,
                'msj' => $msj,
            ));
        }
        
        return new JsonResponse(array(
            'status' => $status,
            'institucion' => $institucion,
            'turno' => $turno,
            'msj' => $msj,
        ));
        // return $this->render('SieHerramientaBundle:BthRegularizacionTtgTte:datoUe.html.twig', 
        //     array('institucion' => $institucioneducativa,
        //     ));
    }

    public function listaGradosAction(Request $request) {
        $turnoId = $request->get('turno');       
        $nivelId = $request->get('nivel');        
        $institucion = $request->get('sie');
        $gestion = $request->get('gestion');  
        
        $response = new JsonResponse();
        return $response->setData(array(
                'grados' => $this->getGradoInstitucionEducativaCurso($institucion, $gestion, $turnoId, $nivelId),
            ));
    }

    public function getGradoInstitucionEducativaCurso($institucionEducativaId, $gestionId, $turnoId, $nivelId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct gt.id, gt.grado")
                ->innerJoin('SieAppWebBundle:GradoTipo', 'gt', 'WITH', 'gt.id = iec.gradoTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                ->andWhere('iec.turnoTipo = :turnoId')
                ->andWhere('iec.nivelTipo = :nivelId')
                ->andWhere('gt.id in (3,4,5)')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                ->setParameter('turnoId', $turnoId)
                ->setParameter('nivelId', $nivelId)
                ->orderBy('gt.id', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    public function listaParalelosAction(Request $request) {
        $turnoId = $request->get('turno');       
        $nivelId = $request->get('nivel');        
        $gradoId = $request->get('grado');        
        $institucion = $request->get('sie');
        $gestion = $request->get('gestion');  
        $response = new JsonResponse();
        return $response->setData(array(
                'paralelos' => $this->getParaleloInstitucionEducativaCurso($institucion, $gestion, $turnoId, $nivelId, $gradoId),
            ));
    }

    public function getParaleloInstitucionEducativaCurso($institucionEducativaId, $gestionId, $turnoId, $nivelId, $gradoId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct pt.id, pt.paralelo")
                ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'pt.id = iec.paraleloTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                ->andWhere('iec.turnoTipo = :turnoId')
                ->andWhere('iec.nivelTipo = :nivelId')
                ->andWhere('iec.gradoTipo = :gradoId')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                ->setParameter('turnoId', $turnoId)
                ->setParameter('nivelId', $nivelId)
                ->setParameter('gradoId', $gradoId)
                ->orderBy('pt.id', 'ASC');
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
            and ic.turno_tipo_id = ".$turno."
            and ic.grado_tipo_id = ".$grado."
            and ic.paralelo_tipo_id = '".$paralelo."'
            and ico.asignatura_tipo_id in (1038,1039)
            and ea.id is null ;");
        $query->execute();
        $estudiante = $query->fetchAll();
        $curso = array();
        
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
        
        return $this->render('SieHerramientaBundle:BthRegularizacionTtgTte:datoEst.html.twig', array('status' => $status, 'msj' => $msj, 'estudiante' => $estudiante, 'curso' => $curso));

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
        }
        $em->flush();
        $em->getConnection()->commit();
		$msj = 'Los datos fueron guardados correctamente.';
        return $response->setData(array('msj'=>$msj,
										'status'=>$status,
									));
    }

}