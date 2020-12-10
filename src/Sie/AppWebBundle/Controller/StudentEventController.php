<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteInscripcionAcreditacion; 


class StudentEventController extends Controller
{
    /*public function indexAction()
    {
        return $this->render('SieAppWebBundle:StudentEvent:index.html.twig', array(
                // ...
            ));    }
            */
 
    public $session;

    public function __construct() {
        $this->session = new Session();
    }
    
    public function indexAction() {
 
        return $this->render('SieAppWebBundle:StudentEvent:index.html.twig');
    }

    public function buscarAction(Request $request){

        $response = new JsonResponse();
        $estudiante = $request->get('estudiante', null);
        $opcion = $request->get('opcion', null);
        $gestionSelected = $this->session->get('currentyear');
        $em = $this->getDoctrine()->getManager();
        // VALIDAMOS DATOS DEL ESTUDIANTE
        $arrStudent = array();
        switch ($opcion) {
            case 1:
                $codigoRude = mb_strtoupper($estudiante['codigoRude']);
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$codigoRude));
                $codigoRude = $estudiante->getCodigoRude();
                break;
            case 2:
                $carnet = $estudiante['carnet'];
                $complemento = $estudiante['complemento'];
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('carnetIdentidad'=>$carnet, 'complemento'=>$complemento));
                $codigoRude = $estudiante->getCodigoRude();
                break;
        }

        if (!is_object($estudiante)) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'Los datos del estudiante no son válidos'
            ]);
        }


        // BUSCAMOS UNA INSCRIPCION CON ESTADO EFECTIVO EN LA GESTION 2020
        $inscripcionesEfectivas = $em->createQueryBuilder()
                            ->select('ei.id, nt.id as idNivel, grat.id as idGrado, IDENTITY(iec.institucioneducativa) as institucioneducativaId, (iec.id) as iecId, 
                            	IDENTITY(iec.nivelTipo) as nivelId,IDENTITY(iec.gradoTipo) as gradoId,IDENTITY(iec.paraleloTipo) as paraleloId,IDENTITY(iec.turnoTipo) as turnoId
                            	')
                            ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                            ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa','inst','with','iec.institucioneducativa = inst.id')
                            ->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
                            ->innerJoin('SieAppWebBundle:GradoTipo','grat','with','iec.gradoTipo = grat.id')
                            ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                            ->where('e.id = :idEstudiante')
                            //->andWhere('ei.estadomatriculaTipo = 4')
                            ->andWhere('gt.id = :year')
                            ->andWhere('inst.institucioneducativaTipo IN (:tipeInst)')
                            
                            //->andWhere('nt.id = :nivelId')
                            //->andWhere('grat.id = :gradoId')
                            

                            ->setParameter('idEstudiante', $estudiante->getId())
                            ->setParameter('tipeInst', array(1))
                            ->setParameter('year', $gestionSelected)
                            //->setParameter('nivelId', 13)
                            //->setParameter('gradoId', 6)
                            ->getQuery()
                            ->getResult();


        if (count($inscripcionesEfectivas)==0) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'El estudiante no cuenta con una inscripción efectiva en la presente gestión'
            ]);
        }
        $arrUrlRerport = array();
        // get info about the UE
		$objInstitucionEdu = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($inscripcionesEfectivas[0]['institucioneducativaId']);
		if(is_object($objInstitucionEdu)){
			$arrUE = array('sie'=>$objInstitucionEdu->getId(), 'nameue'=>$objInstitucionEdu->getInstitucioneducativa());	
		}else{
			$arrUE = array();
		}
	

	// get info about the studnet
        $arrStudent = array(
            'nombre'=>$estudiante->getNombre(),
            'paterno'=>$estudiante->getPaterno(),
            'materno'=>$estudiante->getMaterno(),
            'estId'=>$estudiante->getId(),
            'estInsId'=>$inscripcionesEfectivas[0]['id'],
            'institucioneducativaId'=>$inscripcionesEfectivas[0]['institucioneducativaId'],
            'codigoRude' => $estudiante->getCodigoRude(),
            'carnet' => $estudiante->getCarnetIdentidad(),

            'nivel'=>$em->getRepository('SieAppWebBundle:NivelTipo')->find($inscripcionesEfectivas[0]['nivelId'])->getNivel(),
            'grado'=>$em->getRepository('SieAppWebBundle:GradoTipo')->find($inscripcionesEfectivas[0]['gradoId'])->getGrado(),
            'paralelo'=>$em->getRepository('SieAppWebBundle:ParaleloTipo')->find($inscripcionesEfectivas[0]['paraleloId'])->getParalelo(),
            'turno'=>$em->getRepository('SieAppWebBundle:TurnoTipo')->find($inscripcionesEfectivas[0]['turnoId'])->getTurno(),
            
            //'idObsBono' => sizeof($objObservationsStudent)>0?$objObservationsStudent[0]->getId():''
        );     

        $objInscriptionEvent = $em->getRepository('SieAppWebBundle:EstudianteInscripcionAcreditacion')->findOneBy(array('estudiante'=>$estudiante->getId()));
        
        if(sizeof($objInscriptionEvent)>0){
        	$saveGood = true;
        	$message = 'Estudiante ya cuenta con inscripción';
        }else{
        	$saveGood = false;
        	$message = '';
        }

        //dump($objInscriptionEvent);die;

     
        return $response->setData([
            'status'=>'success',
            'datos'=>array(
   				'saveGood'=>$saveGood,
   				'messageInscription'=>$message,
                'arrStudent'=>$arrStudent,
                'objUE'=>$arrUE,
                //'saveGood'=>true,

               
            )
        ]);
    }

	public function toregisterAction(Request $request){
		$response = new JsonResponse();
		$em = $this->getDoctrine()->getManager();
		$estudiante = $request->get('estudiante', null);

		$newInscriptionEvent = new EstudianteInscripcionAcreditacion();
		$newInscriptionEvent->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($estudiante['estId']));
		$newInscriptionEvent->setFechaRegistro(new \DateTime('now'));

		$em->persist($newInscriptionEvent);
        $em->flush();

        return $response->setData([
            'status'=>'success',
            'datos'=>array(
   				'messageInscription' => 'Estudiante Acreditado...',
                'saveGood'=>true,

               
            )
        ]);        

	}

}
