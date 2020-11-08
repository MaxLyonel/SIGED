<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;


class PrintRecordCardController extends Controller
{
    /*public function indexAction()
    {
        return $this->render('SieHerramientaAlternativaBundle:PrintRecordCard:index.html.twig', array(
                // ...
            ));    }*/

    public $session;

    public function __construct() {
        $this->session = new Session();
    }
    
    public function indexAction() {
 
        return $this->render('SieHerramientaAlternativaBundle:PrintRecordCard:index.html.twig');
    }

    public function buscarAction(Request $request){
        $response = new JsonResponse();
        $estudiante = $request->get('estudiante', null);
        $opcion = $request->get('opcion', null);
        $gestionSelected = $estudiante['gestion'];
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
                            ->select('ei.id, nt.id as idNivel, grat.id as idGrado, IDENTITY(iec.institucioneducativa) as institucioneducativaId, (iec.id) as iecId')
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
                            ->setParameter('idEstudiante', $estudiante->getId())
                            ->setParameter('tipeInst', array(2))
                            ->setParameter('year', $gestionSelected)
                            ->getQuery()
                            ->getResult();

        
        if (count($inscripcionesEfectivas)==0) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'El estudiante no cuenta con una inscripción efectiva en la presente gestión'
            ]);
        }
        $arrUrlRerport = array();
        
        $periodoCode =1;
        foreach ($inscripcionesEfectivas as $value) {
        	
        	//$periodoCode++;
        	$arrDataCentro = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getInfoAbouttheCourse($value['iecId']);
        	$arrUrlRerport[]=array('urlreport'=> $this->generateUrl('herramienta_alter_printrecordcard', array('eInsId'=>$value['id'],'nivel'=>$arrDataCentro[0]['nivelId'],'sie'=>$value['institucioneducativaId'],'gestion'=>$gestionSelected,'subcea'=>$arrDataCentro[0]['sucursalId'],'periodo'=>$arrDataCentro[0]['periodoId'],'iecid'=>$value['iecId'])));
        	
        }
     

        $arrStudent = array(
            'nombre'=>$estudiante->getNombre(),
            'paterno'=>$estudiante->getPaterno(),
            'materno'=>$estudiante->getMaterno(),
            'estId'=>$estudiante->getId(),
            'estInsId'=>$inscripcionesEfectivas[0]['id'],
            'institucioneducativaId'=>$inscripcionesEfectivas[0]['institucioneducativaId'],
            'codigoRude' => $estudiante->getCodigoRude(),
            //'idObsBono' => sizeof($objObservationsStudent)>0?$objObservationsStudent[0]->getId():''
        );          
      
        return $response->setData([
            'status'=>'success',
            'datos'=>array(
   
                'arrStudent'=>$arrStudent,
                //'arrToPrint'=>$arrToPrint,
                'arrUrlRerport'=>$arrUrlRerport,

               
            )
        ]);
    }


}
