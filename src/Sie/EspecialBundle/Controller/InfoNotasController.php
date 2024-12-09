<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;

/**
 * areas especial controller.
 *
 */
class InfoNotasController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Formulario para el registro de notas - modal
     */
    public function indexAction(Request $request) { 
        try {
           // dump($request);die;
            $idInscripcionEspecial = $request->get('idInscripcionEspecial');
            $em = $this->getDoctrine()->getManager();
            $inscripcionEspecial = $em->getRepository('SieAppWebBundle:EstudianteInscripcionEspecial')->find($idInscripcionEspecial);

            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($inscripcionEspecial->getEstudianteInscripcion()->getId());
            $idInscripcion = $inscripcion->getId();

            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcion->getInstitucioneducativaCurso()->getId());
            $cursoEspecial = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array('institucioneducativaCurso'=>$curso->getId()));

            $sie = $curso->getInstitucioneducativa()->getId();
            $gestion = $curso->getGestionTipo()->getId();
            $nivel = $curso->getNivelTipo()->getId();
            $grado = $curso->getgradoTipo()->getId();
            $gestionActual = $this->session->get('currentyear');

            $operativo = ($gestion == 2020)?'4':$this->operativo($sie,$gestion);
            $notas = null;
            $vista = 1;
            $discapacidad = $cursoEspecial->getEspecialAreaTipo()->getId();
            $progserv = '';
            $servicio = '';
            $desc_programa = $cursoEspecial->getEspecialProgramaTipo()->getPrograma();
            $desc_servicio = $cursoEspecial->getEspecialServicioTipo()->getServicio();
            $progserv = $cursoEspecial->getEspecialProgramaTipo()->getId();
            $servicio = $cursoEspecial->getEspecialServicioTipo()->getId();
            $momento = $cursoEspecial->getEspecialMomentoTipo()->getId();
            $programa = $cursoEspecial->getEspecialProgramaTipo()->getId();
            $progserv = $cursoEspecial->getEspecialServicioTipo()->getId();
            $seguimiento = false;
            $actualizarMatricula = false;
            $estadosMatricula = null;
            $idNota = 0;
            // dump($discapacidad);
            //dump($programa);
            //dump($nivel);die;
            //dump($operativo);die;
            switch ($discapacidad) {
                case 1: // Auditiva
                        //dump($programa);dump($servicio); dump($progserv);dump($nivel); die;
                        if($nivel == 403 or $nivel == 404 ){//Verificar el seguimiento para 19
                            //dump("1");die;
                            if($progserv == 19) { 
                                $notas = $this->get('notas')->especial_auditiva($idInscripcion,$operativo);
                                $template = 'especialAuditiva';
                                $actualizarMatricula = false;
                                $seguimiento = true;
                                if($operativo >= 3 or $gestion < $gestionActual){
                                    $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(10,78)));
                                }
                            } else{ 
                                //$operativo = 1; se forzo porque aun no habia registro de operativo
                                $notas = $this->get('notas')->regularEspecial($idInscripcion,$operativo);
                                if($notas['tipoNota'] == 'Trimestre'){
                                    $template = 'trimestral';
                                }else{
                                    if($gestion>=2021)
                                        $template = 'regularEspecial';
                                    else
                                        $template = 'regular';
                                }
                                
                                $actualizarMatricula = true;
                                if(in_array($nivel, array(1,11,403))){
                                    $actualizarMatricula = false;
                                }
                                if($operativo >= 3 or $gestion < $gestionActual){
                                    $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(5,11)));
                                }
                                
                            }
                        }
                        if(in_array($nivel, array(410,411)) and $gestion > 2019 and $gestion < 2023){ 
                            $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo, $idNota);
                            $template = 'especialSeguimiento';
                            $actualizarMatricula = false;
                            $seguimiento = true;
                            if($operativo >= 3 or $gestion < $gestionActual){
                                $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(10,78)));
                            }
                        }
                        
                        if($gestion >2023 and (($nivel ==411 and  in_array($programa, array(22,41,43,19,39))) or ($servicio==40 and $nivel==410 )) ){   
                            //identificamos la nota a evaluar
                            $idNota = 55;
                            if(in_array($programa, array(19,41,39))){
                                $idNota = 54; // 2 Semestre
                            }
                            if(in_array($programa, array(43))){
                                $idNota = 8; // 3 Trimesyte
                            }
                            $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo, $idNota);
                            $template = 'especialModular';
                            $actualizarMatricula = false;
                            $seguimiento = true;

                        }
                        if($gestion > 2023 and ($nivel == 411 and in_array($programa, array(44,46)))  ){ // programas y notas semestrales   
                            //semestralTrimestralSeguimiento
                            //CONTENIDO-RESULTADO-RECOMENDACION
                            $idNota = 54; // Segundo Semestre
                            $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo, $idNota);
                            $template = 'especialSeguimientoSemestral';
                            $actualizarMatricula = false;
                            $seguimiento = true;
                        }
                       //dump($notas);die;
                        
                        break;
                case 2: // Visual
                        if($nivel == 411 and in_array($programa, array(7,8,25,26))){ 
                            if($gestion < 2020){
                                if($notas['tipoNota'] == 'Trimestre'){
                                    $notas = $this->get('notas')->especial_cualitativo($idInscripcion,$operativo);
                                    $template = 'especialCualitativoTrimestral';
                                }else{
                                    $notas = $this->get('notas')->especial_cualitativo($idInscripcion,$operativo);
                                    $template = 'especialCualitativo';    
                                }
                                $actualizarMatricula = false;                                
                                if($operativo >= 4 or $gestion < $gestionActual){
                                    $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(70,71,72,73)));
                                }
                            }else{
                                $actualizarMatricula = false; 
                                $notas = $this->get('notas')->especial_cualitativo_visual($idInscripcion,$operativo);   //por Etapas
                                $template = 'especialCualitativoVisual';
                                $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(78,79)));
                                //dump($notas);die;
                            } 
                        }
                        if($gestion > 2023 and ($nivel == 411 and in_array($programa, array(47,48))) or ($nivel == 410 and in_array($progserv, array(35,36,37,38))) ){ // programas visual y notas semestrales   
                            //semestralTrimestralSeguimiento
                            //CONTENIDO-RESULTADO-RECOMENDACION
                            $idNota = 54; // 2 Semestre
                            $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo, $idNota);
                            $template = 'especialSeguimientoSemestral';
                            $actualizarMatricula = false;
                            $seguimiento = true;
                        }
                        break;
                case 3: //Intelectual
                case 5: //Multiple
                case 12: //Autista
                        switch ($nivel) {
                            case 400:
                            case 401:
                            case 408:
                            case 402:
                            case 412:
                                if($grado <= 6){
                                    $notas = $this->get('notas')->especial_cualitativoEsp($idInscripcion,$operativo);
                                   // dump($notas);die;
                                    if($gestion < 2020){
                                        if($notas['tipoNota'] == 'Trimestre'){
                                            $template = 'especialCualitativoTrimestral';
                                        }else{
                                            $template = 'especialCualitativo';
                                        }
                                        $actualizarMatricula = false;
                                        if($operativo >= 3 or $gestion < $gestionActual){
                                            $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(70,71,72,73)));
                                        }
                                    }else{
                                        $actualizarMatricula = false;
                                        $template = 'especialCualitativo1';
                                       // dump($gestion); dump($gestionActual);die;
                                        if($operativo >= 3 or $gestion < $gestionActual){
                                            
                                            $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(5,28)));
                                        }
                                    }
                                }
                                
                                break;
                            case 410:  // Programas
                            case 411:  //Servicios
                                if($gestion >2019){ 
                                    if($programa==37){ //ITINERARIOS - Area-Objeivos,Evaluados-Trimestral
                                        $idNota = 8 ; //3 TRIMESTRE
                                        $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo, $idNota);
                                        $template = 'especialModular';
                                        $actualizarMatricula = false;
                                    }
                                    if($programa==38){   //Transición a la vida adulta 
                                        $idNota = 54; // 2 Semestre Contenidos-Objetivos-Resultados --TODO
                                        $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo, $idNota);
                                        $template = 'especialSeguimientoSemestral';
                                        $actualizarMatricula = false;
                                        $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(47,78)));
                                    }
                                    $seguimiento = true; //false = ya no guardan, true boton guardar
                                }
                                break;
                            case 409:  //Atencion temprana
                                if($gestion >2023){  //idem juntar
                                    $idNota = 54 ; //2 SEMESTRE
                                    $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo, $idNota);
                                    $template = 'especialModular';
                                    $actualizarMatricula = true;
                                    $seguimiento = true;
                                }
                        }
                        
                        break;
                case 4: // Fisico -motora
               // case 6: // Fisico -motora
                    if($gestion >2019 && $gestion <2024){
                        $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo, $idNota);
                        $template = 'especialSeguimiento';
                        $actualizarMatricula = false;
                        $seguimiento = true;
                        if($operativo >= 3 or $gestion < $gestionActual){
                            $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(10,78,79))); //--
                        }
                    }
                    if($gestion >2023){
                        $idNota = 54 ; //2 SEMESTRE
                        $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo, $idNota);
                        $template = 'especialModular';
                        $actualizarMatricula = false;
                        $seguimiento = true;
                    }
                    break;
                case 7: //  talento extraordinario
                case 6: //Dificultades en el aprendizaje
                    if($gestion >2019){
                        $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo, $idNota);
                        $template = 'especialTalento';
                        $actualizarMatricula = false;
                        $seguimiento = true;
                            if($operativo >= 3 or $gestion < $gestionActual){
                                $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(10,78,79))); //--
                           }
                    }
                     break;
                case 8: // Sordeceguera
                        break;

                case 9: // Problemas emocionales
                        break;
                case 10: // Mental-Psiquica
                        if($gestion <2024 && $nivel == 411 ){ // programas visual y notas semestrales   
                        $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo, $idNota);
                        $template = 'especialSemestre'; //prog/ser con contenidos-resultados-recomendaciones
                        $actualizarMatricula = false;
                        $seguimiento = true;
                        if($operativo >= 3 or $gestion < $gestionActual){
                            $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(78,79,80)));
                            }
                        }
                        if($gestion >2023 and $nivel ==411  ){   
                            $idNota = 54; // 2 Semestre
                            $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo, $idNota);
                            $template = 'especialModular';
                            $actualizarMatricula = false;
                            $seguimiento = true;
                        }                          
                        break;
                        
                case 100: // Modalidad Indirecta
                        break;
                
            }
            //SERVICIOS - APOYO TECNICO PEDAGOGICO PARA TODAS LAS DISCAPACIDADES
            if($nivel==410 and $gestion>2023 and $servicio==20){
                $idNota = 54;//prog/ser con contenidos-resultados-recomendaciones
                $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo, $idNota);            
                $template = 'especialSeguimientoSemestral'; 
                $seguimiento = true;
                $actualizarMatricula = false;
                $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(47,78)));
            }
            $descNota = $em->getRepository('SieAppWebBundle:NotaTipo')->find($idNota);
        // dump($notas);
         //  dump($notas["cualitativas"]);die;
         // dump($template); 
         // die;
           //dump($estadosMatricula);die;
            if($notas){
                return $this->render('SieEspecialBundle:InfoNotas:notas.html.twig',array(
                    'notas'=>$notas,
                    'inscripcion'=>$inscripcion,
                    'vista'=>$vista,
                    'template'=>$template,
                    'actualizar'=>$actualizarMatricula,
                    'operativo'=>$operativo,
                    'estadosMatricula'=>$estadosMatricula,
                    'discapacidad'=>$discapacidad,
                    'desc_discapacidad'=>$cursoEspecial->getEspecialAreaTipo()->getAreaEspecial(),
                    'progserv'=>$progserv,
                    'desc_programa'=>$desc_programa,
                    'servicio'=>$servicio,
                    'desc_servicio'=>$desc_servicio,
                    'seguimiento'=>$seguimiento,
                    'idNota'=>$idNota,
                    'descNota'=>$descNota,
                    'infoUe'=>$request->get('infoUe')
                ));
            }else{
                return $this->render('SieEspecialBundle:InfoNotas:notas.html.twig',array('iesp'=>$inscripcionEspecial, 'notas'=>$notas));
            }

        } catch (Exception $e) {
            return null;
        }
    }

    public function createUpdateAction(Request $request){ 
        try {
            $idInscripcion = $request->get('idInscripcion');
            $discapacidad = $request->get('discapacidad');
            $em = $this->getDoctrine()->getManager();
         //dump($request);die;
            // Registramos las notas
            $gestion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion)->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            
            if($discapacidad == 2 and $gestion > 2019 and $request->get('nivel') == 411){ //visual //programas
                if($request->get('notaCualitativa') == 80){ //extendido 
                    $idEstadoMatriicula = 79; //prosigue
                }else{
                    $idEstadoMatriicula = $request->get('notaCualitativa');
                }
               
                if($request->get('tipoNota')=='Etapa'){ 
                    $this->get('notas')->especialVisualRegistro($request, $discapacidad);
                }
            } 
           // dump($request);die;
           //dump($request->get('actualizar'));
            $this->get('notas')->especialRegistro($request, $discapacidad);
            // Verificamos si se actualizara el estado de matrícula
            if($request->get('actualizar') == true  or $request->get('actualizar') == 1 ){ 
                $this->get('notas')->actualizarEstadoMatriculaEspecial($idInscripcion);
            }
            //actualizamos estado segun el ultimo subestado
           // dump($request->get('nuevoEstadomatricula'. $request->get('idNotaTipo')[0]));die;
           $idEstadoMatriicula ='';
            $tipos_notas = $request->get('id_nota'); //dump($tipos_notas);die;
                if($tipos_notas!=''){ 
                    $ultimo_estado = $request->get('estado'.$tipos_notas[count($tipos_notas)-1]);
                    if($ultimo_estado!='')
                        $idEstadoMatriicula =  $ultimo_estado;
                    $ultimo_estado_nota = $request->get('nuevoEstado'.$tipos_notas[count($tipos_notas)-1]);
                    if($ultimo_estado_nota!='')
                        $idEstadoMatriicula =  $ultimo_estado_nota;
                   // dump($ultimo_estado_nota);
                }
                if($request->get('nuevoEstadomatricula')!='' && $request->get('nuevoEstadomatricula')!=0){
                    $idEstadoMatriicula =  $request->get('nuevoEstadomatricula');}
                if($request->get('nuevoEstadomatricula'. $request->get('idNotaTipo')[0])!='' && $request->get('nuevoEstadomatricula'. $request->get('idNotaTipo')[0])!=0){
                    $idEstadoMatriicula = $request->get('nuevoEstadomatricula'. $request->get('idNotaTipo')[0]);
                }
                //dump($idEstadoMatriicula);die;
            if($idEstadoMatriicula){ 
                $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($idEstadoMatriicula));
                $em->flush();  
            }              
            // Actualizar estado de matricula de los notas que son cualitativas siempre 
            // y cuando esten en el cuarto modulo
            
            if(($request->get('operativo') >= 3 and $request->get('actualizar') == false and ($discapacidad <> 2 or ($discapacidad == 2 and $gestion  < 2020 ))) or (in_array($discapacidad, array(4,6,7)) and $request->get('actualizar') == false) or ($request->get('nivel') == 410 and $request->get('actualizar') == false) or ($request->get('nivel') == 411 and $discapacidad == 1 and $request->get('actualizar') == false)){
               
                $idEstadoMatriicula = $request->get('nuevoEstadomatricula');
                
                if($idEstadoMatriicula=='' && $this->session->get('roluser')==8){ //solo el administrador sie podra poner efectivo
                    $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                    $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
                    $em->flush();
                }
                
                if($idEstadoMatriicula){ 
                    $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                    $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($idEstadoMatriicula));
                    $em->flush();
                    if(($discapacidad == 3 or $discapacidad == 5) and $gestion > 2019 and $idEstadoMatriicula == 5){
                        $notaCualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$inscripcion->getId()));
                        $notacArray = json_decode($notaCualitativa->getNotaCualitativa(),true);
                        $notacArray['promovido'] = mb_strtoupper($request->get('promovido'),'utf-8');
                        $notaCualitativa->setNotaCualitativa(json_encode($notacArray));
                        $em->flush();
                        //dump($notaCualitativa);die;
                    }

                }
            }
            
            if($request->get('operativo') < 3 and (($discapacidad == 5 or $discapacidad == 4) and ($request->get('nivel') == 410 or $request->get('nivel') == 411)) and $gestion > 2020){
                
                $idEstadoMatriicula = $request->get('nuevoEstadomatricula'); 
                if($idEstadoMatriicula){
                    $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                    $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($idEstadoMatriicula));
                    $em->flush();
                }
             }
             
            return new JsonResponse(array('msg'=>'ok'));
        } catch (Exception $e) {
            return new JsonResponse(array('msg'=>'error'));
        }
    }

    public function operativo($sie,$gestion){
        $em = $this->getDoctrine()->getManager();
        // Obtenemos el operativo para bloquear los controles
        $registroOperativo = $em->createQueryBuilder()
                        ->select('rc.bim1,rc.bim2,rc.bim3')
                        ->from('SieAppWebBundle:RegistroConsolidacion','rc')
                        ->where('rc.unidadEducativa = :ue')
                        ->andWhere('rc.gestion = :gestion')
                        ->setParameter('ue',$sie)
                        ->setParameter('gestion',$gestion)
                        ->getQuery()
                        ->getResult();
        //dump($registroOperativo);die;
        if(!$registroOperativo){
            // Si no existe es operativo inicio de gestion
            $operativo = 0;
        }else{
            if($registroOperativo[0]['bim1'] == 0 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 ){
                $operativo = 1; // Primer Trimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 ){
                $operativo = 2; // Segundo Trimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] == 0 ){
                $operativo = 3; // Tercer Trimestre
            }
            
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 ){
                $operativo = 3; // Fin de gestion o cerrado
            }
        }
        return $operativo;
    }

    public function operativoAntes171222021($sie,$gestion){
        $em = $this->getDoctrine()->getManager();
        // Obtenemos el operativo para bloquear los controles
        $registroOperativo = $em->createQueryBuilder()
                        ->select('rc.bim1,rc.bim2,rc.bim3,rc.bim4')
                        ->from('SieAppWebBundle:RegistroConsolidacion','rc')
                        ->where('rc.unidadEducativa = :ue')
                        ->andWhere('rc.gestion = :gestion')
                        ->setParameter('ue',$sie)
                        ->setParameter('gestion',$gestion)
                        ->getQuery()
                        ->getResult();
        //dump($registroOperativo);die;
        if(!$registroOperativo){
            // Si no existe es operativo inicio de gestion
            $operativo = 0;
        }else{
            if($registroOperativo[0]['bim1'] == 0 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 1; // Primer Trimestre Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 2; // Segundo trimestre Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 3; // Tercer Trimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 4; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] >= 1){
                $operativo = 5; // Fin de gestion o cerrado
            }
        }
        return $operativo;
    }


    public function especialEtapasVisualAction(Request $request){
        
        $arrInfoUe = unserialize($request->get('infoUe'));
        $arrInfoStudent = json_decode($request->get('infoStudent'),true);
        //dump($arrInfoStudent,$arrInfoUe);die;
        $sie = $arrInfoUe['requestUser']['sie'];
        $estInsId = $arrInfoStudent['estInsId'];
        $em = $this->getDoctrine()->getManager();
        
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($estInsId);
        
        $query = $em->getConnection()->prepare("select enc.id as id_nota_cualitativa,nota_tipo_id,nota_cualitativa::json->>'etapa' as etapa, emt.estadomatricula,'Del '|| split_part(nota_cualitativa::json->>'fechaEtapa', '-', 1)||' al '||split_part(nota_cualitativa::json->>'fechaEtapa', '-', 2) as fecha_etapa
                                                from estudiante_nota_cualitativa enc
                                                join estadomatricula_tipo emt on (enc.nota_cualitativa::json->>'estadoEtapa')::INTEGER=emt.id
                                                WHERE estudiante_inscripcion_id=". $estInsId ."
                                                ORDER BY nota_tipo_id");
                                                
       /* $query = $em->getConnection()->prepare("select nota_tipo_id  from estudiante_nota_cualitativa enc  
                                                WHERE estudiante_inscripcion_id=". $estInsId ."
                                                ORDER BY nota_tipo_id ASC");*/
        $query->execute();
        $etapas = $query->fetchAll();
        $max  = 0;
        $min = min(array_column($etapas, 'nota_tipo_id'));
        if(count($etapas)>=5)
            $max = $min + 4;

        if($inscripcion){
            return $this->render('SieEspecialBundle:InfoNotas:etapas.html.twig',array('inscripcion'=>$inscripcion,'ueducativaInfo'=>$arrInfoUe['ueducativaInfo'],'etapas'=>$etapas,'max'=>$max,'min'=>$min,'infoUe'=>$request->get('infoUe'),'infoStudent'=>$request->get('infoStudent')));
        }else{
            return $this->render('SieEspecialBundle:InfoNotas:etapas.html.twig',array('inscripcion'=>$inscripcion));
        }
    }

    public function habilitarEspecialEtapasVisualAction(Request $request, $id){
        
        $em = $this->getDoctrine()->getManager();
        $cualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($id);

        $dato =  json_decode($cualitativa->getNotaCualitativa());
        $etapa = $dato->etapa;
        $fechaEtapa = $dato->fechaEtapa;

        $ajusteNotaCualitativa = array('etapa'=>$etapa,'estadoEtapa'=>79,'fechaEtapa'=>$fechaEtapa);
        $cualitativa->setNotaCualitativa(json_encode($ajusteNotaCualitativa));

        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($cualitativa->getEstudianteInscripcion()->getId());
        $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(79));

        $em->flush();

        return $this->redirect($this->generateUrl('herramienta_especial_buscar_centro'));
    }

    public function especialDownloadLibretaAction(Request $request){
        
        $arrInfoUe = unserialize($request->get('infoUe'));
        $arrInfoStudent = json_decode($request->get('infoStudent'),true);
        //dump($arrInfoStudent);die;
        //dump($request);die;
        $sie = $arrInfoUe['requestUser']['sie'];
        $gestion = $arrInfoUe['requestUser']['gestion'];
        $estInsId = $arrInfoStudent['estInsId'];
        $estId = $arrInfoStudent['id'];
        $areaEspecialId = $arrInfoUe['ueducativaInfoId']['areaEspecialId'];
        $programa = $arrInfoUe['ueducativaInfoId']['programaId'];
        $servicio = $arrInfoUe['ueducativaInfoId']['servicioId'];
        $data = $arrInfoStudent['estInsId'] .'|'. $arrInfoStudent['codigoRude'] .'|'.$arrInfoUe['requestUser']['sie'].'|'.$arrInfoUe['requestUser']['gestion'].'|'.$arrInfoUe['ueducativaInfoId']['nivelId'].'|'.$arrInfoUe['ueducativaInfoId']['turnoId'].'|'.$arrInfoUe['ueducativaInfoId']['paraleloId'].'|'.$arrInfoStudent['estInsEspId'];
        $link = 'http://libreta.minedu.gob.bo/lib/'.$this->getLinkEncript($data);
        switch ($areaEspecialId){
            case 1: //AUDITIVA
                $nivelId = $arrInfoUe['ueducativaInfoId']['nivelId'];
               // dump($nivelId);
                if ($nivelId == 403) {
                    $archivo = "libreta_auditiva_inicial_v1_amg.rptdesign";
                    $nombre = 'libreta_especial_auditiva_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
                } 
                if ($nivelId == 404) {
                    $archivo = "libreta_auditiva_primaria_v1_amg.rptdesign";
                    $nombre = 'libreta_especial_auditiva_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
                }
                if($gestion >2023 and in_array($programa, array(19,39,41))) {   //formato semestral con asignaturas
                    $archivo = "esp_informe_semestral_asignaturas.rptdesign";
                    $nombre = 'esp_informe_semestral_auditiva_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
                }
                if($gestion >2023 and in_array($programa, array(22,40))) {   //formato modular con asignaturas
                    $archivo = "esp_informe_modular_asignaturas.rptdesign";
                    $nombre = 'esp_informe_modular_auditiva_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
                }
                if($gestion >2023 and in_array($programa, array(44,46))) {   //formato semestral con contenidos
                    $archivo = "esp_est_Informe_semestral_contenidos.rptdesign";
                    $nombre = 'esp_informe_semestral_contenidos_auditiva_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
                }
                if ($gestion >2023 and $programa == 43) {
                    $archivo = "libreta_intelectual_itinerarios_cvm.rptdesign";
                    $nombre = 'esp_informe_trimestral_itinerarios_auditiva_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
                }
                if ($gestion >2023 and $servicio == 20) { //APOYO TECNICO PEDAGOGICO para todas las areas
                    $archivo = "esp_est_Informe_semestral_atp.rptdesign";
                    $nombre = 'esp_informe_semestral_atp_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
                }
                    $report = $this->container->getParameter('urlreportweb') . $archivo . '&inscripid=' . $estInsId . '&codue=' . $sie. '&lk='. $link . '&&__format=pdf&';
                break;
            case 2: //VISUAL
                
                if($gestion <2024) {   //formato por etapas
                    $idNotaTipo = $request->get('idNotaTipo');
                    $archivo = "esp_est_LibretaEscolar_Visual_v2_pvc.rptdesign";
                    $nombre = 'libreta_especial_visual_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
                   // $data = $arrInfoStudent['estInsId'] .'|'. $arrInfoStudent['codigoRude'] .'|'.$arrInfoUe['requestUser']['sie'].'|'.$arrInfoUe['requestUser']['gestion'].'|'.$arrInfoUe['ueducativaInfoId']['nivelId'].'|'.$arrInfoUe['ueducativaInfoId']['turnoId'].'|'.$arrInfoUe['ueducativaInfoId']['paraleloId'].'|'.$arrInfoStudent['estInsEspId'];
                   // $link = 'http://libreta.minedu.gob.bo/lib/'.$this->getLinkEncript($data);
                    $report = $this->container->getParameter('urlreportweb') . $archivo . '&inscripid=' . $estInsId . '&codue=' . $sie. '&idnotatipo=' . $idNotaTipo .'&lk='. $link . '&&__format=pdf&';
                }
                
                if($gestion >2023 and (in_array($servicio, array(35,36,37,38)) or in_array($programa, array(47,48)))) {   //formato semestral con asignaturas
                    $archivo = "esp_est_Informe_semestral_contenidos.rptdesign";
                    $nombre = 'esp_informe_semestral_contenidos_visual_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
                    $report = $this->container->getParameter('urlreportweb') . $archivo . '&inscripid=' . $estInsId . '&codue=' . $sie .'&lk='. $link . '&&__format=pdf&';
                }
                if($gestion >2023 and  in_array($programa, array(7,8,25,26))) {   //formato por etapas
                    $idNotaTipo = $request->get('idNotaTipo');
                    $max = $request->get('max');
                    $min = $request->get('min');
                    $ind = $min;
                    if($max>0){
                        $ind = $max; 
                    }
                    $ind1 = $ind+1;
                    $ind2 = $ind+2;
                    $ind3 = $ind+3;
                    $archivo = "esp_est_Informe_Visual_Multiple_etapas_cvm.rptdesign";
                    $nombre = 'informe_especial_visual_etapas_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
                   // $data = $arrInfoStudent['estInsId'] .'|'. $arrInfoStudent['codigoRude'] .'|'.$arrInfoUe['requestUser']['sie'].'|'.$arrInfoUe['requestUser']['gestion'].'|'.$arrInfoUe['ueducativaInfoId']['nivelId'].'|'.$arrInfoUe['ueducativaInfoId']['turnoId'].'|'.$arrInfoUe['ueducativaInfoId']['paraleloId'].'|'.$arrInfoStudent['estInsEspId'];
                   // $link = 'http://libreta.minedu.gob.bo/lib/'.$this->getLinkEncript($data);
                    $report = $this->container->getParameter('urlreportweb') . $archivo . '&inscripid=' . $estInsId . '&codue=' . $sie. '&idnotatipo=' . $idNotaTipo .'&lk='. $link . '&codue=' . $sie.'&ind=' . $ind.'&ind1=' . $ind1.'&ind2=' . $ind2.'&ind3='.$ind3.'&&__format=pdf&';
                }
                break;
            case 3:
            case 5:
            case 12: //INTELCTUAL, TEA
                //dump($arrInfoStudent['estInsId']);die;
                if ($programa == 37) {
                    $archivo = "libreta_intelectual_itinerarios_cvm.rptdesign";
                }else{
                    $archivo = "esp_est_LibretaEscolar_Intelectual_Multiple_v1_pvc.rptdesign";
                }
                if ($programa == 28) {
                    $archivo = "esp_informe_semestral_asignaturas.rptdesign";
                }
                if ($programa == 38) {
                    $archivo = "esp_est_Informe_semestral_contenidos.rptdesign";
                }
                $nombre = 'libreta_especial_intelectual_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
               // $data = $arrInfoStudent['estInsId'] .'|'. $arrInfoStudent['codigoRude'] .'|'.$arrInfoUe['requestUser']['sie'].'|'.$arrInfoUe['requestUser']['gestion'].'|'.$arrInfoUe['ueducativaInfoId']['nivelId'].'|'.$arrInfoUe['ueducativaInfoId']['turnoId'].'|'.$arrInfoUe['ueducativaInfoId']['paraleloId'].'|'.$arrInfoStudent['estInsEspId'];
                //$link = 'http://libreta.minedu.gob.bo/lib/'.$this->getLinkEncript($data);
                $report = $this->container->getParameter('urlreportweb') . $archivo . '&inscripid=' . $estInsId . '&codue=' . $sie .'&lk='. $link . '&&__format=pdf&';
                break;
            case 7: //talento
            case 6: //dificultades
                //dump($arrInfoStudent['estInsId']);die;
                $archivo = "esp_est_LibretaEscolar_Talento.rptdesign";
                $nombre = 'libreta_especial_talento_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
                //$data = $arrInfoStudent['estInsId'] .'|'. $arrInfoStudent['codigoRude'] .'|'.$arrInfoUe['requestUser']['sie'].'|'.$arrInfoUe['requestUser']['gestion'].'|'.$arrInfoUe['ueducativaInfoId']['nivelId'].'|'.$arrInfoUe['ueducativaInfoId']['turnoId'].'|'.$arrInfoUe['ueducativaInfoId']['paraleloId'].'|'.$arrInfoStudent['estInsEspId'];
                //$link = 'http://libreta.minedu.gob.bo/lib/'.$this->getLinkEncript($data);
                $report = $this->container->getParameter('urlreportweb') . $archivo . '&estudianteid=' . $estId . '&inscripid=' . $estInsId . '&codue=' . $sie .'&areaid=' . $areaEspecialId .'&lk='. $link . '&&__format=pdf';
                break;
            case 4: //fisico-motora TEMPRANA      
                if($gestion >2023 and in_array($programa, array(28))) {   //formato semestral con asignaturas
                    $archivo = "esp_informe_semestral_asignaturas.rptdesign";
                    $nombre = 'esp_informe_semestral_fisicomotora_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
                } 
                $report = $this->container->getParameter('urlreportweb') . $archivo . '&inscripid=' . $estInsId . '&codue=' . $sie. '&lk='. $link . '&&__format=pdf&';
            break;
            case 10: //mental psiquica      
                $archivo = "esp_informe_semestral_asignaturas.rptdesign";
                $nombre = 'esp_informe_semestral_contenidos_mentalpsiquica_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';   
                $report = $this->container->getParameter('urlreportweb') . $archivo . '&inscripid=' . $estInsId . '&codue=' . $sie. '&lk='. $link . '&&__format=pdf&';
            break;
        }

        if ($gestion >2023 and $servicio == 20) { //APOYO TECNICO PEDAGOGICO para todas las areas
            $archivo = "esp_est_Informe_semestral_atp.rptdesign";
            $nombre = 'esp_informe_semestral_atp_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
            $report = $this->container->getParameter('urlreportweb') . $archivo . '&inscripid=' . $estInsId . '&codue=' . $sie. '&lk='. $link . '&&__format=pdf&';
        }

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $nombre ));
        $response->setContent(file_get_contents($report));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    private function getLinkEncript($datos){

        $codes = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz+/';
        // Encriptamos los datos
        $result = "";
        $a = 0;
        $b = 0;
        for($i=0;$i<strlen($datos);$i++){
            //$x = strpos($codes, $datos[$i]) ;
            $x = ord($datos[$i]) ;
            $b = $b * 256 + $x;
            $a = $a + 8;
  
            while ( $a >= 6) {
                $a = $a - 6;
                $x = floor($b/(1 << $a));
                $b = $b % (1 << $a);
                $result = $result.''.substr($codes, $x,1);
            }
        }
        if($a > 0){
            $x = $b << (6 - $a);
            $result = $result.''.substr($codes, $x,1);
        }
        return $result;
    }


}
