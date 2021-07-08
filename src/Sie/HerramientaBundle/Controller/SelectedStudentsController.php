<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteQuipus;


class SelectedStudentsController extends Controller{

    public $session;
    public $swConfirmGlobal;
    public $urlreport;
    public $arrUeMaleFemale;
    
    public $ue_validas;


    public function __construct() {
        $this->session = new Session();
        $this->swConfirmGlobal = false;
        $this->urlreport = false;
        $this->arrUeMaleFemale = array('1'=>'Masculino-Femenino','2'=>'Masculinos','3'=>'Femeninos');

    }

    public function indexAction(){
        $ie_id=$this->session->get('ie_id');
        if($ie_id){
            $swregistry = false;
            $id_usuario = $this->session->get('userId');
     		if (!isset($id_usuario)) {
                return $this->redirect($this->generateUrl('login'));
            }        
            
            $studentsQuipus = $this->getStudentsQuipus($this->session->get('ie_id'));
            //dump($studentsQuipus);die;
            if($studentsQuipus){
            	$swregistry = true;
            }

            return $this->render('SieHerramientaBundle:SelectedStudents:index.html.twig',array(
            	'studentsQuipus'=>$studentsQuipus,
            	'swregistry'=> $swregistry,
            	'swConfirmGlobal'=>$this->swConfirmGlobal,
                'banFM'=>$this->ueInformationReg($ie_id)['ban_h_m'],
                'labelFM'=>$this->arrUeMaleFemale[$this->ueInformationReg($ie_id)['ban_h_m']],

            ));
        }else{
            return $this->redirect($this->generateUrl('login'));
        }

    }

    private function ueInformationReg($sie){
        $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare("
                    select a.institucioneducativa_id
                    ,case (case when sum(case when c.genero_tipo_id=1 then 1 else 0 end)>0 then '1'else '0' end 
                    ||case when sum(case when c.genero_tipo_id=2 then 1 else 0 end)>0 then '1'else '0' end) when '11' then 1 when '10' then 2 when '01' then 3 end as ban_h_m
                    from institucioneducativa_curso a
                        inner join estudiante_inscripcion b on a.id=b.institucioneducativa_curso_id
                            inner join estudiante c on b.estudiante_id=c.id
                    where a.gestion_tipo_id=2021 and institucioneducativa_id=". $sie ." and b.estadomatricula_tipo_id=4
                    group by a.institucioneducativa_id                
                ");

            $query->execute();
            $dato = $query->fetchAll();
            if(sizeof($dato)>0){
                return $dato[0];
            }else{
                return 0;
            }
    }

    public function loadDataAction( Request $request ){
    	$response = new JsonResponse();
        $estudiante = $request->get('estudiante', null);
        $opcion = $request->get('opcion', null);

        $em = $this->getDoctrine()->getManager();

    	$studentsQuipus = $this->getStudentsQuipus($this->session->get('ie_id'));

    	return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'arrStudents'=>$studentsQuipus,
                'swConfirm'=>(sizeof($studentsQuipus)==2)?true:false,
                'swConfirmGlobal'=>$this->swConfirmGlobal,
                // 'banFM'=>$this->ueInformationReg($ie_id)['ban_h_m'],
                // 'labelFM'=>$this->arrUeMaleFemale[$this->ueInformationReg($ie_id)['ban_h_m']],                
                'urlreport'=>$this->urlreport,
            )
        ]);

    }
    private function getStudentsQuipus($sie){
    	$em = $this->getDoctrine()->getManager();
    	$studentsQuipus = $em->getRepository('SieAppWebBundle:EstudianteQuipus')->findBy(array('institucioneducativa'=>$sie));
//dump($studentsQuipus);die;
    	$arrStudent = array();
    	if($studentsQuipus){
    		foreach ($studentsQuipus as $value) {
    			if($value->getEstado()){
    				$this->swConfirmGlobal = true;
    			}else{
    				$this->swConfirmGlobal = false;
    			}

		    	$arrStudent[] = array(
		            'id'=>$value->getId(),
		            'nombre'=>$value->getNombre(),
		            'paterno'=>$value->getPaterno(),
		            'materno'=>$value->getMaterno(),
		            'carnet'=>$value->getCarnetIdentidad(),
		            'idGenero'=>$value->getGeneroTipoId(),
		            'genero'=>$em->getRepository('SieAppWebBundle:GeneroTipo')->find($value->getGeneroTipoId())->getGenero() ,
		            'estInsId'=>$value->getEstudianteInscripcion()->getId(),
		            //'estInsId'=>$inscripcionesEfectivas[0]['studentInscriptionId'],
		            'institucioneducativaId'=>$value->getInstitucioneducativa()->getId(),
		            'institucioneducativa'=>$em->getRepository('SieAppWebBundle:Institucioneducativa')->find($value->getInstitucioneducativa()->getId())->getInstitucioneducativa() ,
		            'codigoRude' => $value->getCodigoRude(),
                    'fechaNac' => $value->getFechaNacimiento()->format('Y-m-d'),
		            'nivel'=>$em->getRepository('SieAppWebBundle:NivelTipo')->find($value->getNivelTipoId())->getNivel(),
		            'grado'=>$em->getRepository('SieAppWebBundle:GradoTipo')->find($value->getGradoTipoId())->getGrado() ,
		            'paralelo'=>$em->getRepository('SieAppWebBundle:ParaleloTipo')->find($value->getParaleloTipoId())->getParalelo()  ,
		            'nivelId'=>$value->getNivelTipoId(),
					'gradoId'=>$value->getGradoTipoId(),
					'paraleloId'=>$value->getParaleloTipoId(),
		        );
    		}
    		if($this->swConfirmGlobal){
    			$this->urlreport =  $this->generateUrl('donwload_ddjjStudentsQuipus', array('sie'=>$this->session->get('ie_id')));
    		}
    	}
    	return($arrStudent);
    }



    public function buscarAction(Request $request){
        
        $response = new JsonResponse();
        $estudiante = $request->get('estudiante', null);

        $banFM = $estudiante['banFM'];
// dump($estudiante['banFM']);die;
        $opcion = $request->get('opcion', null);

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
        $swStudent = true;
        if (!is_object($estudiante)) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'Los datos del estudiante no son válidos'
            ]);
			$swStudent = false;
        }

		$studentsQuipus = $this->getStudentsQuipus($this->session->get('ie_id'));        
		// to do the validation 
		if (sizeof($studentsQuipus)>0 ) {
            if($banFM==1){
                if(sizeof($studentsQuipus)==2) {
                    return $response->setData([
                        'status'=>'error',
                        'msg'=>'El registro de estudiantes esta completo'
                    ]);
                    $swStudent = false;
                }
                if($estudiante->getGeneroTipo()->getId() == $studentsQuipus[0]['idGenero']){
                    return $response->setData([
                        'status'=>'error',
                        'msg'=>'Ya registro un estudiante con genero '.$estudiante->getGeneroTipo()->getGenero()
                    ]);
                    $swStudent = false;
                }
            }else{
                 if(sizeof($studentsQuipus)==2) {
                    return $response->setData([
                        'status'=>'error',
                        'msg'=>'El registro de estudiantes esta completo'
                    ]);
                    $swStudent = false;
                }
            }

			
        }

        // BUSCAMOS UNA INSCRIPCION CON ESTADO EFECTIVO EN LA GESTION 2021
        $inscripcionesEfectivas = $em->createQueryBuilder()
                            ->select('ei.id as studentInscriptionId, nt.id as idNivel, grat.id as idGrado, IDENTITY(e.generoTipo) as idGenero,IDENTITY(iec.paraleloTipo) as idParalelo,IDENTITY(iec.cicloTipo) as idCiclo,
                             IDENTITY(iec.institucioneducativa) as institucioneducativaId, inst.institucioneducativa' )
                            ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                            ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa','inst','with','iec.institucioneducativa = inst.id')
                            ->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
                            ->innerJoin('SieAppWebBundle:GradoTipo','grat','with','iec.gradoTipo = grat.id')
                            ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                            ->where('e.id = :idEstudiante')
                            ->andWhere('ei.estadomatriculaTipo = :matricula')
                            ->andWhere('iec.gestionTipo = :gestion')
                            ->andWhere('inst.institucioneducativaTipo IN (:tipeInst)')
                            ->andWhere('inst.id IN (:sie)')
                            //->andWhere('iec.nivelTipo = :level')
                            //->andWhere('iec.gradoTipo IN (:grado)')
                            ->setParameter('idEstudiante', $estudiante->getId())
                            ->setParameter('matricula',4)
                            ->setParameter('gestion',$this->session->get('currentyear'))
                            ->setParameter('tipeInst', array(1))
                            ->setParameter('sie', $this->session->get('ie_id'))
                            //->setParameter('level', 13)
                            //->setParameter('grado', array(1,2,3,4))
                            ;
		$inscripcionesEfectivas = $inscripcionesEfectivas->getQuery();
		//dump($inscripcionesEfectivas->getSQL());die;
		$inscripcionesEfectivas = $inscripcionesEfectivas->getResult();
		//dump($inscripcionesEfectivas);die;
        $arrInfoInscription = array();
        if (count($inscripcionesEfectivas)==0) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'El estudiante no cuenta inscripcion en la Unidad Educativa de codigo SIE: '.$this->session->get('ie_id')
            ]);
        }else{
        	$arrInfoInscription = array();
        }
      

        $swDoneCash = true;

        $arrStudent = array(
            'nombre'=>$estudiante->getNombre(),
            'paterno'=>$estudiante->getPaterno(),
            'materno'=>$estudiante->getMaterno(),
            'fechaNacimiento'=>$estudiante->getFechaNacimiento()->format('Y-m-d'),
            'carnet'=>$estudiante->getCarnetIdentidad(),
            'idGenero'=>$estudiante->getGeneroTipo()->getId(),
            'genero'=>$em->getRepository('SieAppWebBundle:GeneroTipo')->find($estudiante->getGeneroTipo()->getId())->getGenero() ,
            'estId'=>$estudiante->getId(),
            'estInsId'=>$inscripcionesEfectivas[0]['studentInscriptionId'],
            'institucioneducativaId'=>$inscripcionesEfectivas[0]['institucioneducativaId'],
            'institucioneducativa'=>$inscripcionesEfectivas[0]['institucioneducativa'],
            'codigoRude' => $estudiante->getCodigoRude(),
            'nivelId'=>$inscripcionesEfectivas[0]['idNivel'],
            'nivel'=>$em->getRepository('SieAppWebBundle:NivelTipo')->find($inscripcionesEfectivas[0]['idNivel'])->getNivel(),
            'gradoId'=>$inscripcionesEfectivas[0]['idGrado'],
            'grado'=>$em->getRepository('SieAppWebBundle:GradoTipo')->find($inscripcionesEfectivas[0]['idGrado'])->getGrado() ,
            'paraleloId'=>$inscripcionesEfectivas[0]['idParalelo'],
            'paralelo'=>$em->getRepository('SieAppWebBundle:ParaleloTipo')->find($inscripcionesEfectivas[0]['idParalelo'])->getParalelo()  ,
            'cicloId'=>$inscripcionesEfectivas[0]['idCiclo'],
        );          
		
        return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'swStudent'=>$swStudent,
                'arrStudent'=>$arrStudent,
            )
        ]);
    }

    public function registryStudentAction(Request $request){
    	$response = new JsonResponse();
        $estudiante = $request->get('estudiante', null);
        
        //dump($estudiante);die;
        $opcion = $request->get('opcion', null);
        $em = $this->getDoctrine()->getManager();

        $objStudentQuipus = new EstudianteQuipus();
        $objStudentQuipus->setFechaRegistro(new \DateTime('now'));
        $objStudentQuipus->setCodigoRude($estudiante['codigoRude']);
        $objStudentQuipus->setPaterno(mb_strtoupper($estudiante['paterno'], 'utf-8'));
        $objStudentQuipus->setMaterno(mb_strtoupper($estudiante['materno'], 'utf-8'));
        $objStudentQuipus->setNombre(mb_strtoupper($estudiante['nombre'], 'utf-8'));                        
        $objStudentQuipus->setCarnetIdentidad($estudiante['carnet']);
        $objStudentQuipus->setComplemento($estudiante['complemento']);
        $objStudentQuipus->setOrgcurricularTipoId(1);
        $objStudentQuipus->setFechaNacimiento(new \DateTime($estudiante['fechaNacimiento']));
        //$objStudentQuipus->setFechaNacimiento(new \DateTime('now'));
        $objStudentQuipus->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($estudiante['institucioneducativaId']) );
        $objStudentQuipus->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')) );
        $objStudentQuipus->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($estudiante['estInsId']) );
        $objStudentQuipus->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($estudiante['estId']) );

        $objStudentQuipus->setGeneroTipoId($estudiante['idGenero']);
        $objStudentQuipus->setNivelTipoId($estudiante['nivelId']);
        $objStudentQuipus->setGradoTipoId($estudiante['gradoId']);
        $objStudentQuipus->setParaleloTipoId($estudiante['paraleloId']);
        $objStudentQuipus->setTurnoTipoId($estudiante['cicloId']);
        
        $objStudentQuipus->setEstado('f');
        
        $em->persist($objStudentQuipus);
		$em->flush();

		$studentsQuipus = $this->getStudentsQuipus($this->session->get('ie_id'));	


        return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'swregistry'=>true,                
                'arrStudents'=>$studentsQuipus,
                'swConfirm'=>(sizeof($studentsQuipus)>=1)?true:false,
            )
        ]);		


    }
    public function removeStudentAction(Request $request){

    	$response = new JsonResponse();
        $id = $request->get('id', null);
        
        $em = $this->getDoctrine()->getManager();    	

            $objStudentQuipus = $em->getRepository('SieAppWebBundle:EstudianteQuipus')->find($id);
            if($objStudentQuipus){
              $em->remove($objStudentQuipus);
              $em->flush();
            }              

		$studentsQuipus = $this->getStudentsQuipus($this->session->get('ie_id'));	

        return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'swregistry'=>true,                
                'arrStudents'=>$studentsQuipus,
                'swConfirm'=>(sizeof($studentsQuipus)>=1)?true:false,
            )
        ]);	             	


    }

    public function closeRegistryAction(Request $request){

    	$response = new JsonResponse();
        $estudiantes = $request->get('estudiantes', null);
        
        $em = $this->getDoctrine()->getManager();    	

        foreach ($estudiantes as $value) {
        	
        	$objStudentQuipus = $em->getRepository('SieAppWebBundle:EstudianteQuipus')->find($value['id']);
            if($objStudentQuipus){
              $objStudentQuipus->setEstado('t');            
            }
        }
  		$em->flush();
            

		$studentsQuipus = $this->getStudentsQuipus($this->session->get('ie_id'));	

        return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'swregistry'=>true,                
                'arrStudents'=>$studentsQuipus,
                'swConfirm'=>false,
                'swConfirmGlobal'=>true,
                'urlreport'=> $this->generateUrl('donwload_ddjjStudentsQuipus', array('sie'=>$this->session->get('ie_id'))),
            )
        ]);	             	


    }    
    

}
