<?php

namespace Sie\AppWebBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

use Sie\AppWebBundle\Entity\PreinsPersona; 
use Sie\AppWebBundle\Entity\PreinsEstudiante; 
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Entity\PreinsEstudianteInscripcion; 
use Sie\AppWebBundle\Entity\PreinsApoderadoInscripcion; 
use Sie\AppWebBundle\Entity\PreinsEstudianteInscripcionJustificativo; 
use Sie\AppWebBundle\Entity\PreinsEstudianteInscripcionHermanos; 

class PreInscriptionController extends Controller
{
 /*   public function indexAction()
    {
        return $this->render('SieAppWebBundle:PreInscription:index.html.twig', array(
                // ...
            ));    }
*/



    public $session;

    public function __construct() {
        $this->session = new Session();
    }
    
    public function indexAction() {
 
        return $this->render('SieAppWebBundle:PreInscription:index.html.twig');
    }

    public function buscarAction(Request $request){
        $response = new JsonResponse();
        $dataParent = $request->get('dataParent', null);
        $opcion = $request->get('opcion', null);
        //$forign =($dataParent['forign']=='true')?1:2;
        
        $em = $this->getDoctrine()->getManager();
        // VALIDAMOS DATOS DEL ESTUDIANTE
        $arrStudent = array();
        $arrJustify = array();
        $carnet= $dataParent['carnet'];
         // buil the person data
        $arrParametros = array(
            'complemento'=>$dataParent['complemento'],
            'primer_apellido'=>$dataParent['paterno'],
            'segundo_apellido'=>$dataParent['materno'],
            'nombre'=>$dataParent['nombre'],
            'fecha_nacimiento'=>$dataParent['fechaNacimiento']
          );
        //$arrParametros['extranjero'] =NULL;  
        if(isset($dataParent['forign'])  && $dataParent['forign']=='true'){
            $arrParametros['extranjero'] = 'e';
        }
          
        // do the validation on segip
        $answerSegip = true;
        $message = "";
        $arrJjustify = array();
        // dump($carnet);
        // dump($arrParametros);
        $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $carnet,$arrParametros,'prod', 'academico');
        // dump($answerSegip);die;
        //$answerSegip = true;
        if($answerSegip){   
            $status='success';
            $code=200;
            $message='registrado correctamente';
            $swdata = $answerSegip;       
        
            $allJustify = $this->get('funciones')->getAllJustify();   
            
            foreach ($allJustify as $value) {
                $arrJustify[]=array('id' => $value->getId(),'justificativo' => $value->getJustificativo() );
            }
        }else{
            $status='warning';
            $code=404;
            $message='validacion segip: Datos Equivocados';     
            $swdata = $answerSegip;       
        }     
      
        return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'status'=>$status,
                'code'=>$code,
                'message'=>$message,                
                'swdata'=>$swdata,                
                'dataParent'=>$dataParent,
                'dataJustify'=>$arrJustify,
            )
        ]);
    }

    public function getUEsAction(Request $request){
        $response = new JsonResponse();
        //get send values
        $idDepto = $request->get('departamento');
        $gestion = $this->session->get('currentyear');
        $uespreInscription = $this->get('funciones')->getUEspreInscription($idDepto, $gestion);  
        $arrUes = array();
        if(sizeof($uespreInscription)>0){
            foreach ($uespreInscription as $value) {
                $arrUes[]=array('id'=>$value['id'], 'institucioneducativa'=> $value['institucioneducativa']);
            }

            $status='success';
            $code=200;
            $message='data encontrada correctamente';
            $swues = true;               

        }else{

            $status='error';
            $code=404;
            $message='No se tienen Unidad Educativas de alta demanda';     
            $swues = false;             

        }

       return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'status'=>$status,
                'code'=>$code,
                'message'=>$message,                
                'swues'=>$swues,                
                'dataUEs'=>$arrUes,
            )
        ]);          
    }
    public function chooseUEAction(Request $request){
        
        $response = new JsonResponse();
        //get send values
        $idDepto = $request->get('departamento');
        $sie = $request->get('sie');
        $gestion = $this->session->get('currentyear');        

        $uepreInscription = $this->get('funciones')->chooseUE($idDepto, $sie, $gestion);   
// dump($uepreInscription);die;
        $arrUe = array();
        if(sizeof($uepreInscription)>0){
            foreach ($uepreInscription as $value) {
                
                $arrUe[]=array(
                    'sie'=>$value['institucioneducativa_id'], 
                    'institucioneducativa'=> $value['institucioneducativa'],
                    'dependencia'=> $value['dependencia'],
                    'descripcion'=> $value['descripcion'],
                    'estadoinstitucion'=> $value['estadoinstitucion'],
                    'departamento'=> $value['departamento'],
                    'distrito'=> $value['distrito'],
                    'gestion'=> $value['gestion_tipo_id'],
                );
            }

            $status='success';
            $code=200;
            $message='data encontrada correctamente';
            $swues = true;               

        }else{

            $status='error';
            $code=404;
            $message='No se tienen Unidad Educativas de alta demanda';     
            $swues = false;             

        }
        


       return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'status'=>$status,
                'code'=>$code,
                'message'=>$message,                
                'swues'=>$swues,                
                'dataUnEd'=>$arrUe,
            )
        ]);         


    }

    public function  getLevelUEAction(Request $request){

        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();

        //get send values
        $idDepto = $request->get('departamento');
        $sie = $request->get('sie');
        $gestion = $this->session->get('currentyear');        

        $objLevels = $this->get('funciones')->getLevelUE($sie, $gestion);
        $aniveles = array();
        if($objLevels){

            foreach ($objLevels as $nivel) {
                // $aniveles[] = array('id'=> $nivel[1], 'level'=>$em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel());
                $aniveles[] = array('id'=> $nivel['nivel_tipo_id'], 'level'=>$em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel['nivel_tipo_id'])->getNivel());
            }   

            $status='success';
            $code=200;
            $message='data encontrada correctamente';
            $swlevel = true;  
        }else{
            $status='error';
            $code=404;
            $message='data NO encontrada correctamente';     
            $swlevel = false;  
        }        

       

      return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'status'=>$status,
                'code'=>$code,
                'message'=>$message,                
                'swlevel'=>$swlevel,                
                'dataLevel'=>$aniveles,
            )
        ]);             

    }

    public function  getGradosUEAction(Request $request){

        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();

        //get send values
        $idDepto = $request->get('departamento');
        $sie = $request->get('sie');
        $idnivel = $request->get('nivel');
        $gestion = $this->session->get('currentyear');        
// dump($sie);
// dump($idnivel);
// dump($gestion);
// die;
        $em = $this->getDoctrine()->getManager();
        //get grado
        $agrados = array();
        $entity = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.gradoTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :idnivel')
                ->andwhere('iec.gestionTipoId = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('idnivel', $idnivel)
                ->setParameter('gestion', $gestion)
                ->distinct()
                ->orderBy('iec.gradoTipo', 'ASC')
                ->getQuery();
        $aGrados = $query->getResult();

        $agrados = array();
        
        foreach ($aGrados as $grado) {
            if($idnivel == 12){
                $agrados[$grado[1]] = array('id'=>$grado[1], 'grado'=>$em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado() );
            }
            if($idnivel == 13){
                $agrados[$grado[1]] = array('id'=>$grado[1], 'grado'=>$em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado() );
            }
            if($idnivel == 11){
                $agrados[$grado[1]] = array('id'=>$grado[1], 'grado'=>$em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado() );
            }            
        }


        $status='success';
        $code=200;
        $message='data encontrada correctamente';
        $swgrado = true;  

      return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'status'=>$status,
                'code'=>$code,
                'message'=>$message,                
                'swgrado'=>$swgrado,                
                'dataGrados'=>$agrados,
            )
        ]);  


      return $response;        
           

    }    

    public function findBrotherAction(Request $request){

        $response = new JsonResponse();
        //get send values
        
        $codigoRude = $request->get('codigoRude'); 
        $gestion = $this->session->get('currentyear');
        $sie = $request->get('sie'); 

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('e.paterno,e.materno,e.nombre, ei.id')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('e.codigoRude = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.institucioneducativa = :sie')
                ->setParameter('sie', $sie)
                ->setParameter('id', $codigoRude)
                ->setParameter('gestion', $gestion)
                ->orderBy('iec.gestionTipo', 'DESC')
                ->getQuery();
                // dump($query->getSQL());die;
            $objInfoInscription = $query->getResult();
            
            if(sizeof($objInfoInscription)>=1){

                $status='success';
                $code=200;
                $message='data encontrada correctamente';
                $swbrother = true;

                $arrBrother[] = array(
                    'paterno'=>$objInfoInscription[0]['paterno'],
                    'materno'=>$objInfoInscription[0]['materno'],
                    'nombre'=>$objInfoInscription[0]['nombre'],
                    'idEstIns'=>$objInfoInscription[0]['id'],
                );

            }
            else{
                $status='error';
                $code=404;
                $message='data NO encontrada correctamente';     
                $swbrother = false; 
                $arrBrother = false;
            }
            // dump($arrBrother);die;
            // dump($arrBrother);die;
            $arrResponse = array(
                        'status'=>$status,
                        'code'=>$code,
                        'message'=>$message,                
                        'swbrother'=>$swbrother,                
                        'dataBrother'=>$arrBrother,
                    );
            // dupm($arrResponse)
              return $response->setData([
                    'status'=>'success',
                    'datos'=>$arrResponse
                ]);              
    }


    public function saveAllDataAction(Request $request){

        $dataParent = $request->get('dataParent');
        $addrressParent = $request->get('addrressParent');
        $ueInfo = $request->get('ueInfo');
        $brother = $request->get('brother');
        $student = $request->get('student');
        $justify = $request->get('justify');
     //dump($dataParent);
     //dump($addrressParent);
     //dump($ueInfo);
     // dump($brother);
     //dump($student);
     //dump($justify);
    

        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction(); 

        try {
            ////////////////////////////////////////
            // this is to the new person request////
            ////////////////////////////////////////


            $newPreinsPersona = $em->getRepository('SieAppWebBundle:PreinsPersona')->findOneBy(array('carnet' => $dataParent['carnet'], 'complemento'=>$dataParent['complemento'] ));

            
            if(sizeof($newPreinsPersona)>0){


            }else{

                $newPreinsPersona = new PreinsPersona();
                $newPreinsPersona->setCarnet($dataParent['carnet']);
                $newPreinsPersona->setComplemento(mb_strtoupper($dataParent['complemento'], 'utf-8'));
                $newPreinsPersona->setPaterno(mb_strtoupper($dataParent['paterno'], 'utf-8'));
                $newPreinsPersona->setMaterno(mb_strtoupper($dataParent['materno'], 'utf-8'));
                $newPreinsPersona->setNombre(mb_strtoupper($dataParent['nombre'], 'utf-8'));
                $newPreinsPersona->setFechaNacimiento(new \DateTime($dataParent['fechaNacimiento']));  
                $newPreinsPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($dataParent['genero']));  
                $newPreinsPersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find(0));  

                $residencia = explode('/',$addrressParent['dirresidencia']);
                $campos = array('setZona','setAvenida','setCalle','setNumero','setCelular');
                if(count($residencia) > 0)
                {
                    for($i = 0; $i < count($residencia); $i++)
                    {
                        $campo = $campos[$i];
                        $newPreinsPersona->{$campo}(mb_strtoupper($residencia[$i], 'utf-8'));
                    }
                }

                $newPreinsPersona->setNomLugTrab(mb_strtoupper($addrressParent['lugarTrabajo'], 'utf-8'));
                $newPreinsPersona->setMunLugTrab(mb_strtoupper($addrressParent['municipio'], 'utf-8'));
                $newPreinsPersona->setZonaLugTrab(mb_strtoupper($addrressParent['zona'], 'utf-8'));
                $newPreinsPersona->setAvenidaLugTrab(mb_strtoupper($addrressParent['avenida'], 'utf-8'));
                $newPreinsPersona->setCelularLugTrab($addrressParent['fono']);
                $newPreinsPersona->setSegipId(1);

                $em->persist($newPreinsPersona);
            }

            ////////////////////////////////////////
            // this is to the new student////
            ////////////////////////////////////////
            //  
            $newPreinsEstudiante = new PreinsEstudiante();
            $newPreinsEstudiante->setCarnetIdentidad($student['carnet']);
            $newPreinsEstudiante->setComplemento('');
            $newPreinsEstudiante->setPaterno(mb_strtoupper($student['paterno'], 'utf-8'));
            $newPreinsEstudiante->setMaterno(mb_strtoupper($student['materno'], 'utf-8'));
            $newPreinsEstudiante->setNombre(mb_strtoupper($student['nombre'], 'utf-8'));
            $newPreinsEstudiante->setFechaNacimiento(new \DateTime(str_replace('/', '-', $student['fechaNacimiento'])));

            $newPreinsEstudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($student['genero']));
            $newPreinsEstudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find(0));

            $newPreinsEstudiante->setLocalidadNac(mb_strtoupper($student['lugarNacimiento'], 'utf-8'));

            $em->persist($newPreinsEstudiante);

            ////////////////////////////////////////
            // this is to the new PreinsEstudianteInscripcion////
            ////////////////////////////////////////
            $newPreinsEstudianteInscripcion = new PreinsEstudianteInscripcion();
            $newPreinsEstudianteInscripcion->setPreinsEstudiante($em->getRepository('SieAppWebBundle:PreinsEstudiante')->find($newPreinsEstudiante->getId()));  
            $ojbInstCursoCupo = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->findOneBy(array('institucioneducativa' => $ueInfo['sie'], 'nivelTipo'=>$ueInfo['nivel'], 'gradoTipo'=>$ueInfo['grado'],'gestionTipoId'=>$this->session->get('currentyear')));
            
            $newPreinsEstudianteInscripcion->setPreinsInstitucioneducativaCursoCupo($em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->find($ojbInstCursoCupo->getId()));  
            $newPreinsEstudianteInscripcion->setMunicipioVive(mb_strtoupper($student['municipio'], 'utf-8'));
            $newPreinsEstudianteInscripcion->setZonaVive(mb_strtoupper($student['zona'], 'utf-8'));
            $newPreinsEstudianteInscripcion->setAvenidaVive(mb_strtoupper($student['avenida'], 'utf-8'));
            $newPreinsEstudianteInscripcion->setCelular($student['fono']);            
            $newPreinsEstudianteInscripcion->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(1));  

            $newPreinsEstudianteInscripcion->setFechaInscripcion(new \DateTime('now'));
            $newPreinsEstudianteInscripcion->setFechaRegistro(new \DateTime('now'));

            $em->persist($newPreinsEstudianteInscripcion);
            ////////////////////////////////////////
            // this is to the new  PreinsApoderadoInscripcion////
            ////////////////////////////////////////
            $newPreinsApoderadoInscripcion = new PreinsApoderadoInscripcion();
            $newPreinsApoderadoInscripcion->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($dataParent['parentesco']));
            $newPreinsApoderadoInscripcion->setPreinsPersona($em->getRepository('SieAppWebBundle:PreinsPersona')->find($newPreinsPersona->getId()));
            $newPreinsApoderadoInscripcion->setPreinsEstudianteInscripcion($em->getRepository('SieAppWebBundle:PreinsEstudianteInscripcion')->find($newPreinsEstudianteInscripcion->getId()));
            $newPreinsApoderadoInscripcion->setFechaRegistro(new \DateTime('now'));

            $em->persist($newPreinsApoderadoInscripcion);

            ////////////////////////////////////////
            // this is to the new  PreinsApoderadoInscripcion////
            ////////////////////////////////////////
            if(sizeof($justify)>0){
                foreach ($justify as $value) {
                    $newPreinsEstudianteInscripcionJustificativo = new PreinsEstudianteInscripcionJustificativo();

                    $newPreinsEstudianteInscripcionJustificativo->setPreinsEstudianteInscripcion($em->getRepository('SieAppWebBundle:PreinsEstudianteInscripcion')->find($newPreinsEstudianteInscripcion->getId()));
                    $newPreinsEstudianteInscripcionJustificativo->setPreinsJustificativoTipo($em->getRepository('SieAppWebBundle:PreinsJustificativoTipo')->find($value));

                    $newPreinsEstudianteInscripcionJustificativo->setFechaInscripcion(new \DateTime('now'));
                    $newPreinsEstudianteInscripcionJustificativo->setFechaRegistro(new \DateTime('now'));                    

                    $em->persist($newPreinsEstudianteInscripcionJustificativo);

                }            
            }

            ////////////////////////////////////////
            // this is to the new  PreinsEstudianteInscripcionHermanos////
            ////////////////////////////////////////
            if($brother=='false'){

            }else{

                if( sizeof($brother)>0 && $student['codigoRude']!=''){

                    $newPreinsEstudianteInscripcionHermanos = new PreinsEstudianteInscripcionHermanos();

                    $newPreinsEstudianteInscripcionHermanos->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($brother[0]['idEstIns']));
                    $newPreinsEstudianteInscripcionHermanos->setPreinsEstudianteInscripcion($em->getRepository('SieAppWebBundle:PreinsEstudianteInscripcion')->find($newPreinsEstudianteInscripcion->getId()));

                    $newPreinsEstudianteInscripcionHermanos->setFechaRegistro(new \DateTime('now'));

                    $em->persist($newPreinsEstudianteInscripcionHermanos);            

                }
            }


            // save all data
            $em->flush();            
            // Try and commit the transaction
            $em->getConnection()->commit();    

            $status='success';
            $code=200;
            $message='registrado correctamente...';
            $swdata = true;       
    
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $status='warning';
            $code=404;
            $message='No registrado...';     
            $swdata = false; 

        }

        return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'status'=>$status,
                'code'=>$code,
                'message'=>$message,                
                'swdata'=>$swdata,  
                'numTramite'=>$newPreinsEstudianteInscripcion->getId(),  
                'urlreporte'=> $this->generateUrl('preinscription_preinspdf', array('idTramite'=>$newPreinsEstudianteInscripcion->getId()))              

            )
        ]);        
    }

    public function preinspdfAction(Request $request, $idTramite){
            $pdf=$this->container->getParameter('urlreportweb') . 'reg_preins_formulario.rptdesign&__format=pdf'.'&preinscripcion='.$idTramite;
            //$pdf='http://127.0.0.1:63170/viewer/preview?_report=D%3A\workspaces\workspace_especial\bono-bjp\reg_lst_EstudiantesApoderados_Benef_UnidadEducativa_v1_EEA.rptdesign&_format=pdf'.'&ue='.$sie.'&gestion='.$gestion;
            
            $status = 200;  
            $arch           = 'DECLARACION_PREINSCRIPCIÓN-'.date('Y').'_'.date('YmdHis').'.pdf';
            $response       = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($pdf));
            $response->setStatusCode($status);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
    }

    public function registryClaimAction(Request $request){
        //ini var
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();   
        //get the send values
        $persona = $request->get('persona');
        $student = $request->get('student');

        try {
          
            if($persona['swchangeTutor'] == 'true'){

                $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($student['estId']);
                $newObjClaim = new ObservadosBono();
                $newObjClaim->setGestionTipoId(2020);
                $newObjClaim->setInstitucioneducativaId($student['institucioneducativaId']);
                $newObjClaim->setIde($student['estId']);
                $newObjClaim->setCodigoRude($em->getRepository('SieAppWebBundle:Estudiante')->find($student['estId'])->getCodigoRude());
                $newObjClaim->setCarnetIdentidad($objStudent->getCarnetIdentidad());
                $newObjClaim->setComplemento($objStudent->getComplemento());
                $newObjClaim->setPaterno($student['paterno']);
                $newObjClaim->setMaterno($student['materno']);
                $newObjClaim->setNombre($student['nombre']);
                $newObjClaim->setGeneroTipoId($objStudent->getGeneroTipo()->getId());
                $newObjClaim->setFechaNacimiento(new \DateTime($objStudent->getFechaNacimiento()->format('Y-m-d')));
                $newObjClaim->setSegipId($objStudent->getSegipId());
                $newObjClaim->setIdins($student['estInsId']);
                $newObjClaim->setObs('NUEVO SOLICITUD CAMBIO DE TUTOR ');
                $newObjClaim->setTipoServicioId(14);
                $newObjClaim->setTipoReclamo($em->getRepository('SieAppWebBundle:TiposReclamos')->find(17));
                $newObjClaim->setEstado('A');

                $em->persist($newObjClaim);
                

            }
            /*
            if($student['idObsBono']!=''){
                $ObjClaim = $em->getRepository('SieAppWebBundle:ObservadosBono')->find($student['idObsBono']);
                $ObjClaim->setEstado('B');
                $em->persist($ObjClaim);
            }
            */  
            // get info about the UE
            $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');
            $query = $repository->createQueryBuilder('jg')
                    ->select('lt4.codigo AS codigo_departamento,
                            lt4.lugar AS departamento,
                            lt3.codigo AS codigo_provincia,
                            lt3.lugar AS provincia,
                            lt2.codigo AS codigo_seccion,
                            lt2.lugar AS seccion,
                            lt1.codigo AS codigo_canton,
                            lt1.lugar AS canton,
                            lt.codigo AS codigo_localidad,
                            lt.lugar AS localidad,
                            dist.id AS codigo_distrito,
                            dist.distrito,
                            orgt.orgcurricula,
                            dept.dependencia,
                            jg.id AS codigo_le,
                            inst.id,
                            inst.institucioneducativa,
                            lt.area2001,
                            estt.estadoinstitucion,
                            jg.direccion,
                            jg.zona')
                    ->join('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.leJuridicciongeografica = jg.id')
                    ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
                    ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
                    ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
                    ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
                    ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'inss', 'WITH', 'inss.institucioneducativa = inst.id')
                    ->innerJoin('SieAppWebBundle:EstadoinstitucionTipo', 'estt', 'WITH', 'inst.estadoinstitucionTipo = estt.id')
                    ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
                    ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
                    ->join('SieAppWebBundle:DependenciaTipo', 'dept', 'WITH', 'inst.dependenciaTipo = dept.id')
                    ->where('inst.id = :idInstitucion')
                    ->andWhere('inss.gestionTipo in (:gestion)')
                    ->setParameter('idInstitucion', $student['institucioneducativaId'])
                    ->setParameter('gestion', '2020')
                    ->getQuery();

            $ubicationUE = $query->getSingleResult();            

            
            $objReclamoTitular = new ReclamadoresTitulares();
            $objReclamoTitular->setReclamoId(1);
            $objReclamoTitular->setEstudianteId($student['estId']);
            $objReclamoTitular->setNombre(mb_strtoupper($persona['nombre'], 'utf-8'));
            $objReclamoTitular->setPaterno(mb_strtoupper($persona['paterno'], 'utf-8'));
            $objReclamoTitular->setMaterno(mb_strtoupper($persona['materno'], 'utf-8'));
            $objReclamoTitular->setCarnet($persona['ci']);
            $objReclamoTitular->setComp(isset($persona['complemento']) ? $persona['complemento']:'');
            $objReclamoTitular->setTelefono($persona['celular']);
            $objReclamoTitular->setCorreo($persona['correo']);
            $objReclamoTitular->setUsuarioReg(1);
            $objReclamoTitular->setFechaReg(new \DateTime('now'));
            $objReclamoTitular->setUsuarioMod(1);
            $objReclamoTitular->setEstado('A');

            $em->persist($objReclamoTitular);

            $objReclamo = new Reclamos();
            $objReclamo->setCodigoServicio($em->getRepository('SieAppWebBundle:TiposServicios')->find(14));
            $objReclamo->setFechaReclamo(new \DateTime('now'));
            $objReclamo->setHoraReclamo(new \DateTime('now'));
            $objReclamo->setPersonaId(0);// get in?
            $objReclamo->setApodCarnet('');
            $objReclamo->setApodTipodoc('CI');
            $objReclamo->setApodFechanac(new \DateTime('now'));// get in?
            $objReclamo->setIdTipoReclamo($em->getRepository('SieAppWebBundle:TiposReclamos')->find($persona['tipoReclamo']));
            $objReclamo->setEstudianteId($student['estId']);//get in?
            $objReclamo->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($student['estInsId']));
            $objReclamo->setCodigoRude($student['codigoRudeId']);
            $objReclamo->setOrigenPago('');
            $objReclamo->setDepartamentoTipo($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($ubicationUE['codigo_departamento']) );
            $objReclamo->setReclamoEstado('V');//empty
            $objReclamo->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($student['institucioneducativaId']));
            $objReclamo->setUsuarioReg($em->getRepository('SieAppWebBundle:BonoUsuarios')->find(2));
            $objReclamo->setFechaReg(new \DateTime('now'));
            $objReclamo->setEstado('A');

            $em->persist($objReclamo);
            $em->flush(); 
            // Try and commit the transaction
            $em->getConnection()->commit();

            $arrResponse = array(
                'status'=>'success',
                'code'=>'200',
                'message'=>'registrado correctamente',
            );

           

            
        } catch (Exception $e) {

            $em->getConnection()->rollback();
            $em->close();
             $arrResponse = array(
                'status'=>'error',
                'code'=>'500',
                'message'=>'no registrado',
            );
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";            
            
        }

            return $response->setData([
                'status'=>'success',
                'datos'=> $arrResponse
            ]);   


         
    }

    public function buscarPrevAction(Request $request){
        $response = new JsonResponse();
        $apoderado = $request->get('apoderado', null);
        $estudiante = $request->get('estudiante', null);
        $nivel = $estudiante['nivel'];
        $grado = $estudiante['grado'];
        $opcion = $request->get('opcion', null);

        // VALIDAMOS LOS DATOS DEL APODERADO
        if ($apoderado == null) {
            return $response->setData([
                'status'=>'error',
                // 'msg'=>'El número de carnet y/o complemento del apoderado no es válido',
                'msg'=>'Los datos del apoderado no son válidos'
            ]);
        }

        $em = $this->getDoctrine()->getManager();
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
            'carnet'=>$apoderado['carnet'],
            'complemento'=>mb_strtoupper($apoderado['complemento'],'utf-8')
        ));

        if (!is_object($persona)) {
            return $response->setData([
                'status'=>'error',
                // 'msg'=>'Los datos de carnet y/o complemento del apoderado no son válidos o no se encuentra registrado en el sistema',
                'msg'=>'Los datos del apoderado no son válidos'
            ]);
        }

        $validarSegip = $this->validarSegip($persona, $persona->getCarnet());
        if (!$validarSegip) {
            return $response->setData([
                'status'=>'error',
                // 'msg'=>'Los datos de carnet y/o complemento del apoderado no son válidos o no se encuentra registrado en el sistema',
                'msg'=>'Los datos del apoderado no son válidos'
            ]);
        }

        // VALIDAMOS DATOS DEL ESTUDIANTE
        
        switch ($opcion) {
            case 1:
                $codigoRude = mb_strtoupper($estudiante['codigoRude']);
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$codigoRude));
                break;
            case 2:
                $carnet = $estudiante['carnet'];
                $complemento = $estudiante['complemento'];
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('carnetIdentidad'=>$carnet, 'complemento'=>$complemento));
                break;

        }

        if (!is_object($estudiante)) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'Los datos del estudiante no son válidos'
            ]);
        }

        // VERIFICAMOS SI LA OPCION ES POR CARNET PARA VALIDAR CON EL SEGIP
        if ($opcion == 2) {
            $validarSegip = $this->validarSegip($estudiante, $estudiante->getCarnetIdentidad());
            if (!$validarSegip) {
                return $response->setData([
                    'status'=>'error',
                    'msg'=>'Los datos del estudiante no son válidos'
                ]);
            }
        }

        // VALIDAMOS SI EL APODERADO TIENE ALGUN PARENTESCO CON EL ESTUDIANTE
        $apoderadoInscripcion = $em->createQueryBuilder()
                                ->select('ai')
                                ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                                ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                                ->innerJoin('SieAppWebBundle:ApoderadoInscripcion','ai','with','ai.estudianteInscripcion = ei.id')
                                ->innerJoin('SieAppWebBundle:Persona','p','with','ai.persona = p.id')
                                ->where('e.id = :idEstudiante')
                                ->andWhere('p.id = :idPersona')
                                ->andWhere('gt.id in (:gestiones)')
                                ->setParameter('idEstudiante', $estudiante->getId())
                                ->setParameter('idPersona', $persona->getId())
                                ->setParameter('gestiones', array(2019,2020))
                                ->getQuery()
                                ->getResult();
        
        //VERIFICAMOS SI NO EXISTE REGISTRO DEL APODERADO
        if (count($apoderadoInscripcion)==0) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'Usted no se encuentra registrado como apoderado del estudiante, para su registro comuníquese con la Unidad Educativa del estudiante'
            ]);
        }

        // BUSCAMOS UNA INSCRIPCION CON ESTADO EFECTIVO EN LA GESTION 2020
        $inscripcionesEfectivas = $em->createQueryBuilder()
                            ->select('ei.id, nt.id as idNivel, grat.id as idGrado')
                            ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                            ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                            ->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
                            ->innerJoin('SieAppWebBundle:GradoTipo','grat','with','iec.gradoTipo = grat.id')
                            ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                            ->where('e.id = :idEstudiante')
                            ->andWhere('ei.estadomatriculaTipo = 4')
                            ->andWhere('gt.id = 2020')
                            ->setParameter('idEstudiante', $estudiante->getId())
                            ->getQuery()
                            ->getResult();


        if (count($inscripcionesEfectivas)==0) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'El estudiante no cuenta con una inscripción efectiva en la presente gestión'
            ]);
        }

        // VALIDAMOS SI EL ESTUDIANTE TIENE UNA INSCRIPCION CON EL NIVEL Y GRADO ESPECIFICADO
        // EN EL CASO DE QUE LA BUSQUEDA SE HAYA HECHO CON DATOS DEL ESTUDIANTE OPCION 3
        if ($opcion == 3) {
            $cont = 0; // variable que indica que no cumple con el nivel ni grado
            foreach ($inscripcionesEfectivas as $ie) {
                if ($nivel == $ie['idNivel'] && $grado == $ie['idGrado']) {
                    $cont++;
                }
            }

            if ($cont == 0) {
                return $response->setData([
                    'status'=>'error',
                    'msg'=>'El estudiante no cuenta con una inscripción en el nivel y grado indicado'
                ]);
            }
        }

        // VERIFICAMOS SI ESTA HABILITADO PARA REALIZAR EL COBRO DEL BONO
        $bono = $em->getRepository('SieAppWebBundle:BfEstudiantesValidados')->findOneBy(array(
            'codigoRude'=>$estudiante->getCodigoRude()
        ));

        if (!is_object($bono)) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'El estudiante no es beneficiario del bono familia, comuníquese con la Unidad Educativa del estudiante para solucionar el caso'
            ]);
        }

        return $response->setData([
            'status'=>'success',
            'msg'=>'todo bien',
            'datos'=>array(
                'msg'=>'El estudiante sí es beneficiario del bono familia, su persona está habilitada para realizar el cobro',
                'apoderadoCarnet'       =>$persona->getCarnet(),
                'apoderadoComplemento'  =>$persona->getComplemento(),
                'estudianteCodigoRude'  =>$estudiante->getCodigoRude()
            )
        ]);
    }

    private function validarSegip($persona, $carnet){
        $datos = array(
            'complemento'=>mb_strtoupper($persona->getComplemento(), 'utf-8'),
            // 'primer_apellido'=>mb_strtoupper($persona->getPaterno(), 'utf-8'),
            // 'segundo_apellido'=>mb_strtoupper($persona->getMaterno(), 'utf-8'),
            // 'nombre'=>mb_strtoupper($persona->getNombre(), 'utf-8'),
            // 'fecha_nacimiento'=>$persona->getFechaNacimiento()->format('d-m-Y')
        );

        $resultadoPersona = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($carnet,$datos,'prod','academico');
        if($resultadoPersona){
            return true;
        }

        return false;
    }    
    public function verificacion_datosAction(Request $request){
        $ci = $request->get('ci');
        $compl = $request->get('compl');

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:PreinsApoderadoInscripcion');
        $query = $entity->createQueryBuilder('ai')
                ->select('ai')
                ->leftjoin('SieAppWebBundle:PreinsPersona', 'p', 'WITH', 'p.id=ai.preinsPersona')
                ->where('p.carnet = :carnet')
                ->andwhere('p.complemento = :complemento')
                ->setParameter('carnet', $ci)
                ->setParameter('complemento', $compl)
                ->getQuery();
        $lista_estado = $query->getResult();
        // $lista_estado = $query->fetchAll();
        // dump($lista_estado); exit();
        return $this->render('SieAppWebBundle:PreInscription:listar_busqueda_preinscripcion.html.twig',array
        (
            'lista_estado'=>$lista_estado
        ));
        // return $this->render('SieAppWebBundle:PreInscription:index.html.twig');

    }  


}
