<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\UnificacionRude;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;

/**
 * HistoryInscription controller.
 *
 */
class UnificacionRudeController extends Controller {

    private $session;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Lists all Estudiante entities.
     *
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        //check if the user is logged
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $sesion = $request->getSession();
        $sesion->set('procesocalidadid', '0');
        $sesion->set('procesocalidadrude', '0');
        $sesion->set('procesocalidadgestion', '0'); 
        
        return $this->render($this->session->get('pathSystem').':UnificacionRude:index.html.twig', array(
                    'vala' => '',
                    'valb' => '',
                    'onoffcalidad' => 'f'
            ));
    }
    
    public function indexcalidadAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        //check if the user is logged
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
                
        $form = $request->get('form');
                
        $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idProceso']);
        $query = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findBy(array('obs' => $vproceso->getObs(), 'validacionReglaTipo' => $vproceso->getValidacionReglaTipo(), 'gestionTipo' => $form['gestion']));
        $rudeOne = $query[0]->getLlave();
        $rudeTwo = $query[1]->getLlave();

        $sesion = $request->getSession();
        $sesion->set('procesocalidadid', '0');
        $sesion->set('procesocalidadrude', '0');
        $sesion->set('procesocalidadgestion', '0'); 
        $sesion->set('procesocalidadid', $form['idProceso']);
        $sesion->set('procesocalidadrude', $query[0]->getLlave());
        $sesion->set('procesocalidadgestion', $form['gestion']);     
        
        return $this->render($this->session->get('pathSystem').':UnificacionRude:index.html.twig', array(
                    'vala' => $rudeOne,
                    'valb' => $rudeTwo,
                    'onoffcalidad' => 'v'
            ));
    }
    
    public function unificarverhistorialAction($rudea, $rudeb) {
        $em = $this->getDoctrine()->getManager();
        //check if the user is logged
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $val = 1;
        //dump(app.session.get('roluser')); die;
        if ($this->session->get('roluser') == 8){
            $val = 0;
        }
                                    
        $rudea = trim($rudea);
        $rudeb = trim($rudeb);
        if ($rudea == $rudeb) {
            $message = 'El códigos rudes deben ser distintos.';            
            $this->addFlash('notihistory', $message);            
            return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
        }

        //VERIFICANDO QUE LOS RUDE NO TENGA TRAMITES EN DIPLOMAS
        $sqla = "select * from tramite a 
        inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
        inner join estudiante c on b.estudiante_id = c.id
        where c.codigo_rude = '$rudea'
        and a.esactivo is true";
        $queryverdipa = $em->getConnection()->prepare($sqla);
        $queryverdipa->execute();
        $dataInscriptionJsonVerDipa = $queryverdipa->fetchAll();

        $sqlb = "select * from tramite a 
        inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
        inner join estudiante c on b.estudiante_id = c.id
        where c.codigo_rude = '$rudeb'
        and a.esactivo is true";
        $queryverdipb = $em->getConnection()->prepare($sqlb);
        $queryverdipb->execute();
        $dataInscriptionJsonVerDipb = $queryverdipb->fetchAll();
        
        if (((count($dataInscriptionJsonVerDipa) > 0) || (count($dataInscriptionJsonVerDipb) > 0)) && ($val == 1)) {
            $message = 'No se podra realizar la unificación por que ya se inicio tramite de Diplomas.';
            $this->addFlash('notihistory', $message);            
            return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
        }

        $queryver = $em->getConnection()->prepare("select * from get_estudiantes_verificacion_historiales('" . $rudea . "','" . $rudeb . "');");
        $queryver->execute();
        $dataInscriptionJsonVer = $queryver->fetchAll();
        if ((count($dataInscriptionJsonVer) > 0) && ($val == 1)){
           $message = 'Los códigos rudes no deben tener inscripciones en la misma gestion con el mismo estado matricula.';            
            $this->addFlash('notihistory', $message);            
            return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
        }
             
        $query = $em->getConnection()->prepare("select * from get_estudiante_historial_json('" . $rudea . "');");
        $query->execute();
        $dataInscriptionJsona = $query->fetchAll();
        
        if ( count($dataInscriptionJsona) == 0) {
            $message = 'Estudiante con rude ' . $rudea . ' no presenta registro de inscripciones';
            $this->addFlash('notihistory', $message);            
            return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
        }

        foreach ($dataInscriptionJsona as $key => $inscription) {            
            $dataInscriptiona [] = json_decode($inscription['get_estudiante_historial_json'], true);
        }
        
        $query = $em->getConnection()->prepare("select * from get_estudiante_historial_json('" . $rudeb . "');");
        $query->execute();
        $dataInscriptionJsonb = $query->fetchAll();
        
        if ( count($dataInscriptionJsonb) == 0) {
            $message = 'Estudiante con rude ' . $rudeb . ' no presenta registro de inscripciones';
            $this->addFlash('notihistory', $message);            
            return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
        }

        foreach ($dataInscriptionJsonb as $key => $inscription) {            
            $dataInscriptionb [] = json_decode($inscription['get_estudiante_historial_json'], true);
        }
        
        return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulthistoriales.html.twig', array(
                    'datastudenta' => $dataInscriptiona,
                    'datastudentb' => $dataInscriptionb                    
        ));

    }
    
    public function unificarvercorincAction($rudecor, $rudeinc) {
        $em = $this->getDoctrine()->getManager();
        //check if the user is logged
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $val = 1;
        //dump(app.session.get('roluser')); die;
        if ($this->session->get('roluser') == 8){
            $val = 0;
        }
                                    
        $rudecor = trim($rudecor);
        $rudeinc = trim($rudeinc);

        $queryver = $em->getConnection()->prepare("select * from get_estudiantes_verificacion_historiales_gestion('" . $rudeinc . "','" . $rudecor . "','2017');");
        $queryver->execute();
        $dataInscriptionJsonVer = $queryver->fetchAll();
        //dump($dataInscriptionJsonVer);die;
        if ((count($dataInscriptionJsonVer) == 0)  && ($val == 1)){
           $message = 'El historial de los códigos rudes deben tener coherencia en los estados para la presente gestión.';            
            $this->addFlash('notihistory', $message);            
            return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
        }

        $query = $em->getConnection()->prepare("select * from get_estudiante_historial_json('" . $rudecor . "');");
        $query->execute();
        $dataInscriptionJson = $query->fetchAll();
        foreach ($dataInscriptionJson as $key => $inscription) {            
            $dataInscriptioncor [] = json_decode($inscription['get_estudiante_historial_json'], true);
        }
        
        $query = $em->getConnection()->prepare("select * from get_estudiante_historial_json('" . $rudeinc . "');");
        $query->execute();
        $dataInscriptionJsonb = $query->fetchAll();
        foreach ($dataInscriptionJsonb as $key => $inscriptionb) {            
            $dataInscriptioninc [] = json_decode($inscriptionb['get_estudiante_historial_json'], true);
        }
      
        return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulthistorialescorinc.html.twig', array(
                    'datastudentcor' => $dataInscriptioncor,
                    'datastudentinc' => $dataInscriptioninc                   
        ));

    }
    
    public function unificarAction(Request $request, $rudeinc, $rudecor) {        
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);
        
        //SELECCION DE REGISTROS  
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $studentinc = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudeinc));
        $studentcor = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudecor));

        $inscripinco = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('estudiante' => $studentinc), array('fechaInscripcion'=>'DESC'));
        $inscripcorr = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('estudiante' => $studentcor), array('fechaInscripcion'=>'DESC'));
//            dump($inscripinc); die;
//            $apodeinc = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $inscripinc));        
//            $apodecorr = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $inscripinc));        
                
        //UPDATES
        try {
            $sesion = $request->getSession();
            //ESTUDIANTE INSCRIPCION
            if (($inscripinco) && ($studentcor)) {
                foreach ($inscripinco as $inscrip) { // A LAS INSCRIPCIONES INCORRECTAS SE LE ASIGNA EL ESTUDIANTE CORRECTO
                    //REGISTRANDO ESTADO ANTES DE LOS CAMBIOS EN EL LOG DE TRANBASCCIONES
                    //($key,$tabla,$tipoTransaccion,$ip,$usuarioId,$observacion,$valorNuevo,$valorAnt,$sistema,$archivo){
                    $antes = $inscrip; 

                    //$arrValNew = $this->getDataLogSave($valueResp['inscripcionidValido']);
                    //$arrValOld = $this->getDataLogSave($valueResp['inscripcionidInValido']);
                    
                    $inscrip->setEstudiante($studentcor);
                    $em->persist($inscrip);
                    $em->flush();     
                    
                    $resp = $defaultController->setLogTransaccion(
                            $inscrip->getId(),              //key
                            'estudiante_inscripcion',       //tabla
                            'U',                            //accion
                            json_encode(array('browser' => $_SERVER['HTTP_USER_AGENT'], 'ip' => $_SERVER['REMOTE_ADDR'])), //ip
                            $this->session->get('userId'),  //usuarioid
                            '',                             //observaciones
                            json_encode($this->getDataLogSave($inscrip->getId())),            //valor nuevo
                            json_encode($this->getDataLogSave($antes->getId())),            //valor anterior
                            'SIGED', json_encode(array('file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__))//controlador
                    );
                }
            }

            //ESTUDIANTE APODERADOS
//                if (($apodeinc) && ($studentcor)) {
//                    foreach ($inscripcorr as $inscrip) {
//                        $apode->setEstudianteInscripcion($inscripcorr);
//                        $em->persist($apode);
//                        $em->flush();                
//                    }
//                }

            //ELIMINANDO ESTUDIANTE INCORRECTO          
            if ($studentinc) {
                //$em->remove($studentinc);
                //$em->flush();

                //REGISTRANDO CAMBIO DE ESTADO EN CONTROL DE CALIDAD
                $antes = $studentinc->getId(); 
                $arrResult = $this->getDataLogEstudiante($antes);                
                
                $resp = $defaultController->setLogTransaccion(
                            $antes,              //key
                            'estudiante',       //tabla
                            'D',                            //accion
                            json_encode(array('browser' => $_SERVER['HTTP_USER_AGENT'], 'ip' => $_SERVER['REMOTE_ADDR'])), //ip
                            $this->session->get('userId'),  //usuarioid
                            '',                             //observaciones
                            '',                             //valor nuevo
                            json_encode($arrResult),            //valor anterior
                            'SIGED', json_encode(array('file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__))//controlador
                    );
            }

            //guardaro de log antiguo de datos de unificacion
            $cursoinco = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripinco[0]->getInstitucioneducativaCurso()->getId());
            $cursocorr = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcorr[0]->getInstitucioneducativaCurso()->getId());
            $ur = new UnificacionRude();
            $ur->setRudeinco($rudeinc);
            $ur->setRudecorr($rudecor);                
            $ur->setEstadomatriculaTipoInco($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($inscripinco[0]->getEstadomatriculaTipo()->getId()));
            $ur->setEstadomatriculaTipoCorr($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($inscripcorr[0]->getEstadomatriculaTipo()->getId()));
            $ur->setGestionTipoCorr($em->getRepository('SieAppWebBundle:GestionTipo')->find($cursocorr->getGestionTipo()->getId()));
            $ur->setGestionTipoInco($em->getRepository('SieAppWebBundle:GestionTipo')->find($cursoinco->getGestionTipo()->getId()));
            $ur->setSiecorr($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($cursoinco->getInstitucioneducativa()->getId()));
            $ur->setSieinco($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($cursoinco->getInstitucioneducativa()->getId()));                
            $ur->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($this->session->get('userId')));
            $ur->setFechaRegistro(new \DateTime('now'));
            $em->persist($ur);
            $em->flush();
            
            if ($sesion->get('procesocalidadid') != '0' ){
                $valproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($sesion->get('procesocalidadid'));
                $valproceso->setEsActivo('true');
                $em->persist($valproceso);
                $em->flush();
            }
            
            //COMMIT DE TODA LA TRANSACCION
            $em->getConnection()->commit();
            
            if ($sesion->get('procesocalidadid')  == '0'){
                //$sesion->set('procesocalidadid', $form['idProceso']);
                //$sesion->set('procesocalidadrude', $query[0]->getLlave());
                //$sesion->set('procesocalidadgestion', $form['gestion']);                  
                
                $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_est_dos_RUDES (:tipo, :rude, :param, :gestion)');
                $query->bindValue(':tipo', '2');
                $query->bindValue(':rude', $sesion->get('procesocalidadrude'));
                $query->bindValue(':param', '');
                $query->bindValue(':gestion', $sesion->get('procesocalidadgestion'));
                $query->execute();
                $resultado = $query->fetchAll();
            }
            
            //MOSTRAMOS NUEVO HISTORIAL
            $query = $em->getConnection()->prepare("select * from get_estudiante_historial_json('" . $rudecor . "');");
            $query->execute();
            $dataInscriptionJsonb = $query->fetchAll();
            foreach ($dataInscriptionJsonb as $key => $inscriptionb) {            
                $dataInscriptioncor [] = json_decode($inscriptionb['get_estudiante_historial_json'], true);
            }
                        
            $this->session->getFlashBag()->add('success', 'Proceso realizado exitosamente. Historial del rude :' . $rudecor);
            return $this->render($this->session->get('pathSystem') . ':UnificacionRude:result.html.twig', array(
                    'datastudentcor' => $dataInscriptioncor,                   
            ));            
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->add('notihistory', $ex->getMessage());
            $sw = false;
            return $this->render($this->session->get('pathSystem').':UnificacionRude:index.html.twig', array(                
                'datastudentinc' => array(),
                'datastudentcor' => array(),
            ));
        }
             
    }    
    
    private function getDataLogSave($idInscription){
        $em = $this->getDoctrine()->getManager();
        $objInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscription);
        $arrResult = [];
        if (isset($objInscription)) {
            $arrResult['Id'] = $objInscription->getId();
            $arrResult['matriculaTipo'] = $objInscription->getEstadomatriculaTipo()->getId();
            $arrResult['institucioneducativaCurso'] = $objInscription->getInstitucioneducativaCurso()->getId();
            //$arrResult['estadomatriculaTipo'] = $objInscription->getEstadomatriculaTipo()->getId();
            $arrResult['estudiante'] = $objInscription->getEstudiante()->getId();
        }
        return $arrResult;
    }
    
    private function getDataLogEstudiante($idEstudiante){        
        $em = $this->getDoctrine()->getManager();
        $objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->find($idEstudiante);
        $arrResult = [];        
        if (isset($objInscription)) {
            $arrResult['Id'] = $objInscription->getId();
            $arrResult['codigo_rude'] = $objInscription->getCodigoRude();
            $arrResult['carnet_identidad'] = $objInscription->getCarnetIdentidad();
            $arrResult['paterno'] = $objInscription->getPaterno();
            $arrResult['materno'] = $objInscription->getMaterno();
            $arrResult['nombre'] = $objInscription->getNombre();
            $arrResult['fecha_nacimiento'] = $objInscription->getFechaNacimiento();
        }
        return $arrResult;
    }
}
