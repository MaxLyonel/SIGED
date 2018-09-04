<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\UnificacionRude;
use Sie\AppWebBundle\Entity\EstudianteBack;
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
        //$sesion->set('procesocalidadid', '0');
        //$sesion->set('procesocalidadrude', '0');
        //$sesion->set('procesocalidadgestion', '0'); 
        
        return $this->render($this->session->get('pathSystem').':UnificacionRude:index.html.twig', array(
                    'vala' => '',
                    'valb' => '',
                    'onoffcalidad' => 'f'
            ));
    }
    
    public function indexcalidadAction(Request $request) {
        /*$em = $this->getDoctrine()->getManager();
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
            ));*/
    }
    
    public function unificarverhistorialAction($rudea, $rudeb) {
        $em = $this->getDoctrine()->getManager();
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $val = 1;
        if ($this->session->get('roluser') == 8){
            $val = 0;
        }
       
        //*******VERIFICANDO QUE LOS RUDES NO SEAN EL MISMO
        $rudea = trim($rudea);
        $rudeb = trim($rudeb);
        if ($rudea == $rudeb) {
            $message = 'El códigos rudes deben ser distintos.';            
            $this->addFlash('notihistory', $message);            
            return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
        }
        //*******VERIFICANDO QUE LOS RUDES NO SEAN EL MISMO

        //*******VERIFICANDO LA EXISTENCIA DEL AMBOS RUDES
        $studenta = array();
        $studentb = array();
        $studenta = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudea));
        if (!$studenta) {
            $message = 'El código '.$rudea.' no existe.';
            $this->addFlash('notihistory', $message);            
            return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
        } else {
            $studentb = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudeb));
            if (!$studentb) {
                $message = 'El código '.$rudeb.' no existe.';
                $this->addFlash('notihistory', $message);            
                return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
            }
        }
        //*******VERIFICANDO LA EXISTENCIA DEL AMBOS RUDES
        
        $validado = 0;        
        //*******VERIFICANDO QUE LOS DATOS NOMBRES APELLIDOS SEAN IGUALES        
        if  (      ($studenta->getNombre() == $studentb->getNombre())
                && ($studenta->getPaterno() == $studentb->getPaterno())
                && ($studenta->getMaterno() == $studentb->getMaterno())
                && ($studenta->getgeneroTipo() == $studentb->getgeneroTipo())
                && ($studenta->getfechaNacimiento() == $studentb->getfechaNacimiento())
            ) {
            $validado = 1;
        } else {
            $validado = 0;
            $message = 'Los datos personales no son los mismo.';
            $this->addFlash('validacionerror', $message);  
        }
        //*******VERIFICANDO QUE LOS DATOS NOMBRES APELLIDOS SEAN IGUALES


        if ($validado == 1){
            //*******VERIFICANDO QUE ALGUN RUDE ESTE VACIO AUTOMATICAMENTE LO MARCA COMO INCORRECTO
            $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $rudea . "') order by gestion_tipo_id_raep desc;");
            $query->execute();               
            $dataInscriptiona = $query->fetchAll();
            $countHistorya = count($dataInscriptiona);

            $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $rudeb . "') order by gestion_tipo_id_raep desc;");
            $query->execute();               
            $dataInscriptionb = $query->fetchAll();
            $countHistoryb = count($dataInscriptionb);
            
            if (($countHistorya == 0) && ($countHistoryb == 0)) {
                $message = 'Ninguno de los rudes presenta historial.';
                $this->addFlash('notihistory', $message);            
                return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
            } else {
                if (($countHistorya == 0) && ($countHistoryb > 0)) {
                    $this->addFlash('autoselcorr', 'Se ha seleccionado el rude sin historial como el rude incorrecto.'); 
                    return $this->redirectToRoute('unificacion_ver_cor_inc', array('rudecor' => $rudeb,'rudeinc' => $rudea));
                }
                if (($countHistoryb == 0) && ($countHistorya > 0)) {
                    $this->addFlash('autoselcorr', 'Se ha seleccionado el rude sin historial como el rude incorrecto.'); 
                    return $this->redirectToRoute('unificacion_ver_cor_inc', array('rudecor' => $rudea,'rudeinc' => $rudeb));
                }
            }//*******VERIFICANDO QUE ALGUN RUDE ESTE VACIO AUTOMATICAMENTE LO MARCA COMO INCORRECTO

            //*******VERIFICANDO QUE ALGUNO DE LOS RUDE NO TENGA TRAMITES EN DIPLOMAS
            $sqla = "select * from tramite a 
            inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
            inner join estudiante c on b.estudiante_id = c.id
            where c.codigo_rude = '$rudea'
            and a.esactivo is true";
            
            $queryverdipa = $em->getConnection()->prepare($sqla);
            $queryverdipa->execute();
            $dataInscriptionJsonVerDipa = $queryverdipa->fetchAll();
            $countDipa = count($dataInscriptionJsonVerDipa);

            $sqlb = "select * from tramite a 
            inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
            inner join estudiante c on b.estudiante_id = c.id
            where c.codigo_rude = '$rudeb'
            and a.esactivo is true";
            $queryverdipb = $em->getConnection()->prepare($sqlb);
            $queryverdipb->execute();
            $dataInscriptionJsonVerDipb = $queryverdipb->fetchAll();
            $countDipb = count($dataInscriptionJsonVerDipb);

            if (($countDipa > 0) && ($countDipb > 0)) {
                $message = 'Ambos rudes cuentan con tramine de diplomas.';
                $this->addFlash('notihistory', $message);            
                return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
            } else {
                if (($countDipa == 0) && ($countDipb > 0)) {
                    $this->addFlash('autoselcorr', 'Se ha seleccionado el rude con tramites en diplomas como el rude correcto.'); 
                    return $this->redirectToRoute('unificacion_ver_cor_inc', array('rudecor' => $rudeb,'rudeinc' => $rudea));
                }
                if (($countDipb == 0) && ($countDipa > 0)) {
                    $this->addFlash('autoselcorr', 'Se ha seleccionado el rude con tramites en diplomas como el rude correcto.'); 
                    return $this->redirectToRoute('unificacion_ver_cor_inc', array('rudecor' => $rudea,'rudeinc' => $rudeb));
                }
            }
            //*******VERIFICANDO QUE ALGUNO DE LOS RUDE NO TENGA TRAMITES EN DIPLOMAS

            //*******VERIFICANDO QUE ALGUNO DE LOS RUDE NO TENGA DATOS EN JUEGOS
            $sqla = "select * from estudiante_inscripcion_juegos a 
            inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
            inner join estudiante c on b.estudiante_id = c.id
            where c.codigo_rude = '$rudea'";
            $queryverJuea = $em->getConnection()->prepare($sqla);
            $queryverJuea->execute();
            $dataInscriptionJsonJuea = $queryverJuea->fetchAll();
            $countJuea = count($dataInscriptionJsonJuea);
            
            $sqlb = "select * from estudiante_inscripcion_juegos a 
            inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
            inner join estudiante c on b.estudiante_id = c.id
            where c.codigo_rude = '$rudeb'";
            $queryverJueb = $em->getConnection()->prepare($sqlb);
            $queryverJueb->execute();
            $dataInscriptionJsonJueb = $queryverJueb->fetchAll();
            $countJueb = count($dataInscriptionJsonJueb);           

            if (($countJuea > 0) && ($countJueb > 0)) {
                $message = 'Ambos rudes cuentan con historial en juegos.';
                $this->addFlash('notihistory', $message);            
                return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
            } else {
                if (($countJuea == 0) && ($countJueb > 0)) {
                    $this->addFlash('autoselcorr', 'Se ha seleccionado el rude con historial en juegos como el rude correcto.'); 
                    return $this->redirectToRoute('unificacion_ver_cor_inc', array('rudecor' => $rudeb,'rudeinc' => $rudea));
                }
                if (($countJuea == 0) && ($countJueb > 0)) {
                    $this->addFlash('autoselcorr', 'Se ha seleccionado el rude con historial en juegos como el rude correcto.'); 
                    return $this->redirectToRoute('unificacion_ver_cor_inc', array('rudecor' => $rudea,'rudeinc' => $rudeb));
                }
            }
            //*******VERIFICANDO QUE ALGUNO DE LOS RUDE NO TENGA DATOS EN JUEGOS

            //*******VERIFICANDO QUE ALGUNO DE LOS RUDE NO TENGA DATOS EN OLIMPIADAS
            $sqla = "select * from olim_estudiante_inscripcion a 
            inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
            inner join estudiante c on b.estudiante_id = c.id
            where c.codigo_rude = '$rudea' ";
            $queryverOlma = $em->getConnection()->prepare($sqla);
            $queryverOlma->execute();
            $dataInscriptionJsonOlma = $queryverOlma->fetchAll();
            $countOlma = count($dataInscriptionJsonOlma);

            $sqlb = "select * from olim_estudiante_inscripcion a 
            inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
            inner join estudiante c on b.estudiante_id = c.id
            where c.codigo_rude = '$rudeb' ";
            $queryverOlmb = $em->getConnection()->prepare($sqlb);
            $queryverOlmb->execute();
            $dataInscriptionJsonOlmb = $queryverOlmb->fetchAll();
            $countOlmb = count($dataInscriptionJsonOlmb);

            if (($countOlma > 0) && ($countOlmb > 0)) {
                $message = 'Ambos rudes cuentan con historial en olimpiadas.';
                $this->addFlash('notihistory', $message);            
                return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
            } else {
                if (($countOlma == 0) && ($countOlmb > 0)) {
                    $this->addFlash('autoselcorr', 'Se ha seleccionado el rude con historial en olimpiadas como el rude correcto.'); 
                    return $this->redirectToRoute('unificacion_ver_cor_inc', array('rudecor' => $rudeb,'rudeinc' => $rudea));
                }
                if (($countOlma == 0) && ($countOlmb > 0)) {
                    $this->addFlash('autoselcorr', 'Se ha seleccionado el rude con historial en olimpiadas como el rude correcto.'); 
                    return $this->redirectToRoute('unificacion_ver_cor_inc', array('rudecor' => $rudea,'rudeinc' => $rudeb));
                }
            }
            //*******VERIFICANDO QUE ALGUNO DE LOS RUDE NO TENGA DATOS EN OLIMPIADAS
        }

        $dataInscriptionaR = array();
        $dataInscriptionaA = array();
        $dataInscriptionaE = array();
        $dataInscriptionaP = array();
        $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $rudea . "') order by gestion_tipo_id_raep desc;");
        $query->execute();               
        $dataInscriptiona = $query->fetchAll();
        foreach ($dataInscriptiona as $key => $inscriptiona) {
            switch ($inscriptiona['institucioneducativa_tipo_id_raep']) {
                case '1':
                    $dataInscriptionaR[$key] = $inscriptiona;
                break;
                case '2':
                    $dataInscriptionaA[$key] = $inscriptiona;
                break;
                case '4':
                    $dataInscriptionaE[$key] = $inscriptiona;
                break;
                case '5':
                    $dataInscriptionaP[$key] = $inscriptiona;
                break;
            }
        }

        $dataInscriptionbR = array();
        $dataInscriptionbA = array();
        $dataInscriptionbE = array();
        $dataInscriptionbP = array();
        $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $rudeb . "') order by gestion_tipo_id_raep desc;");
        $query->execute();               
        $dataInscriptionb = $query->fetchAll();
        foreach ($dataInscriptionb as $key => $inscriptionb) {
            switch ($inscriptionb['institucioneducativa_tipo_id_raep']) {
                case '1':
                    $dataInscriptionbR[$key] = $inscriptionb;
                break;
                case '2':
                    $dataInscriptionbA[$key] = $inscriptionb;
                break;
                case '4':
                    $dataInscriptionbE[$key] = $inscriptionb;
                break;
                case '5':
                    $dataInscriptionbP[$key] = $inscriptionb;
                break;
            }
        }

        return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulthistoriales.html.twig', array(
                    'validado' => $validado,

                    'studenta' => $studenta,
                    'dataInscriptionaR' => $dataInscriptionaR,
                    'dataInscriptionaA' => $dataInscriptionaA,
                    'dataInscriptionaE' => $dataInscriptionaE,
                    'dataInscriptionaP' => $dataInscriptionaP,
                    'studentb' => $studentb,
                    'dataInscriptionbR' => $dataInscriptionbR,
                    'dataInscriptionbA' => $dataInscriptionbA,
                    'dataInscriptionbE' => $dataInscriptionbE,
                    'dataInscriptionbP' => $dataInscriptionbP
        ));
    }
    
    public function unificarvercorincAction($rudecor, $rudeinc) {
        $em = $this->getDoctrine()->getManager();        
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $val = 1;        
        if ($this->session->get('roluser') == 8){
            $val = 0;
        }
        $rudecor = trim($rudecor);
        $rudeinc = trim($rudeinc);

        //*******VERIFICANDO QUE LOS RUDE NO TENGA TRAMITES EN DIPLOMAS
        /*$sqla = "select * from tramite a 
        inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
        inner join estudiante c on b.estudiante_id = c.id
        where c.codigo_rude = '$rudecor'
        and a.esactivo is true";
        $queryverdipa = $em->getConnection()->prepare($sqla);
        $queryverdipa->execute();
        $dataInscriptionJsonVerDipa = $queryverdipa->fetchAll();

        $sqlb = "select * from tramite a 
        inner join estudiante_inscripcion b on a.estudiante_inscripcion_id = b.id
        inner join estudiante c on b.estudiante_id = c.id
        where c.codigo_rude = '$rudecor'
        and a.esactivo is true";
        $queryverdipb = $em->getConnection()->prepare($sqlb);
        $queryverdipb->execute();
        $dataInscriptionJsonVerDipb = $queryverdipb->fetchAll();
        
        if (((count($dataInscriptionJsonVerDipa) > 0) || (count($dataInscriptionJsonVerDipb) > 0)) && ($val == 1)) {
            $message = 'No se podra realizar la unificación por que ya se inicio tramite de Diplomas.';
            $this->addFlash('notihistory', $message);            
            return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
        }*/
        //*******VERIFICANDO QUE LOS RUDE NO TENGA TRAMITES EN DIPLOMAS


        $studentCorr = array();
        $studentIncc = array();

        $studentCorr = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudecor));
        $studentIncc = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudeinc));

        $dataInscriptionCorrR = array();
        $dataInscriptionCorrA = array();
        $dataInscriptionCorrE = array();
        $dataInscriptionCorrP = array();

        $dataInscriptionInccR = array();
        $dataInscriptionInccA = array();
        $dataInscriptionInccE = array();
        $dataInscriptionInccP = array();

        $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $rudecor . "') order by gestion_tipo_id_raep desc;");
        $query->execute();               
        $dataInscriptionCorr = $query->fetchAll();
        foreach ($dataInscriptionCorr as $key => $inscriptionCorr) {
            switch ($inscriptionCorr['institucioneducativa_tipo_id_raep']) {
                case '1':
                    $dataInscriptionCorrR[$key] = $inscriptionCorr;
                break;
                case '2':
                    $dataInscriptionCorrA[$key] = $inscriptionCorr;
                break;
                case '4':
                    $dataInscriptionCorrE[$key] = $inscriptionCorr;
                break;
                case '5':
                    $dataInscriptionCorrP[$key] = $inscriptionCorr;
                break;
            }
        }

        $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $rudeinc . "') order by gestion_tipo_id_raep desc;");
        $query->execute();               
        $dataInscriptionIncc = $query->fetchAll();
        foreach ($dataInscriptionIncc as $key => $inscriptionIncc) {
            switch ($inscriptionIncc['institucioneducativa_tipo_id_raep']) {
                case '1':
                    $dataInscriptionInccR[$key] = $inscriptionIncc;
                break;
                case '2':
                    $dataInscriptionInccA[$key] = $inscriptionIncc;
                break;
                case '4':
                    $dataInscriptionInccE[$key] = $inscriptionIncc;
                break;
                case '5':
                    $dataInscriptionInccP[$key] = $inscriptionIncc;
                break;
            }
        }
        
        $validado = 1;
        
        //********** SE VERIFICA QUE LOS HISTORIALES NO CUENTEN CON ESTADOS SIMILARES EN LA MISMA GESTION
        $sqlb = "select cast('Regular' as varchar) as subsistema, cast('Mismo estado en la misma gestión' as varchar) as observacion, gestion_rude_b as gestion, estadomatricula_rude_b as estadomatricula from (
            select * from (            
            select gestion_tipo_id_raep as gestion_rude_b, estadomatricula_tipo_id_fin_r as estadomatricula_rude_b
            from sp_genera_estudiante_historial('".$rudeinc."') 
            where institucioneducativa_tipo_id_raep = 1
            and estadomatricula_tipo_id_fin_r not in ('6','9')
            ) b 
        INNER JOIN
            (
            select gestion_tipo_id_raep as gestion_rude_c, estadomatricula_tipo_id_fin_r as estadomatricula_rude_c
            from sp_genera_estudiante_historial('".$rudecor."') 
            where institucioneducativa_tipo_id_raep = 1
            and estadomatricula_tipo_id_fin_r not in ('6','9')
            ) c 
            ON b.gestion_rude_b = c.gestion_rude_c
            AND b.estadomatricula_rude_b = c.estadomatricula_rude_c) regular";
        
        
        $queryverdipb = $em->getConnection()->prepare($sqlb);
        $queryverdipb->execute();
        $dataInscriptionJsonVerDipb = $queryverdipb->fetchAll();
        //********** SE VERIFICA QUE LOS HISTORIALES NO CUENTEN CON ESTADOS SIMILARES EN LA MISMA GESTION
        $validado = 0;
        if (count($dataInscriptionJsonVerDipb) > 0) {
            $message = "¡Proceso de dentenido! se ha detectado inconsistencia de datos :".$dataInscriptionJsonVerDipb[0]['subsistema']." ".$dataInscriptionJsonVerDipb[0]['observacion']." ".$dataInscriptionJsonVerDipb[0]['gestion'];
            $this->addFlash('notihistory', $message);
            //return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
        } else {
            $validado = 1;
        }

        return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulthistorialescorinc.html.twig', array(                   
                    'validado' => $validado,

                    'studentCorr' => $studentCorr,
                    'dataInscriptionCorrR' => $dataInscriptionCorrR,
                    'dataInscriptionCorrA' => $dataInscriptionCorrA,
                    'dataInscriptionCorrE' => $dataInscriptionCorrE,
                    'dataInscriptionCorrP' => $dataInscriptionCorrP,

                    'studentIncc' => $studentIncc,
                    'dataInscriptionInccR' => $dataInscriptionInccR,
                    'dataInscriptionInccA' => $dataInscriptionInccA,
                    'dataInscriptionInccE' => $dataInscriptionInccE,
                    'dataInscriptionInccP' => $dataInscriptionInccP
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

        //*************GENERANDO BACKUP DEL RUDE INCORRECTO
        $sqlb = "select * from sp_genera_repaldo_rude('".$rudeinc."')";
        $queryverdipb = $em->getConnection()->prepare($sqlb);
        $queryverdipb->execute();
        $dataInscriptionJsonVerDipb = $queryverdipb->fetchAll();
        //*************GENERANDO BACKUP DEL RUDE INCORRECTO
                
        //UPDATES
        try {
            $sesion = $request->getSession();
            //***********ESTUDIANTE INSCRIPCION            
            if (($inscripinco) && ($studentcor)) {
                foreach ($inscripinco as $inscrip) { // A LAS INSCRIPCIONES INCORRECTAS SE LE ASIGNA EL ESTUDIANTE CORRECTO                    
                    $antes = $inscrip;                    
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

            //**********ESTUDIANTE APODERADOS
            /*if (($apodeinc) && ($studentcor)) {
                foreach ($inscripcorr as $inscrip) {
                    $apode->setEstudianteInscripcion($inscripcorr);
                    $em->persist($apode);
                    $em->flush();                
                }
            }*/

            //***********REGISTRANDO CAMBIO DE ESTADO EN CONTROL DE CALIDAD       
            if ($studentinc) {
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
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcorr[0]->getInstitucioneducativaCurso()->getId());
            $ur = new UnificacionRude();
            $ur->setRudeinco($rudeinc);
            $ur->setRudecorr($rudecor);                
            $ur->setEstadomatriculaTipoInco($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($inscripcorr[0]->getEstadomatriculaTipo()->getId()));
            $ur->setEstadomatriculaTipoCorr($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($inscripcorr[0]->getEstadomatriculaTipo()->getId()));
            $ur->setGestionTipoCorr($em->getRepository('SieAppWebBundle:GestionTipo')->find($curso->getGestionTipo()->getId()));
            $ur->setGestionTipoInco($em->getRepository('SieAppWebBundle:GestionTipo')->find($curso->getGestionTipo()->getId()));
            $ur->setSiecorr($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($curso->getInstitucioneducativa()->getId()));
            $ur->setSieinco($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($curso->getInstitucioneducativa()->getId()));                
            $ur->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($this->session->get('userId')));
            $ur->setFechaRegistro(new \DateTime('now'));
            $em->persist($ur);
            $em->flush();
            
            //ELIMINANDO RUDE ANTIGUO            
            $em->remove($studentinc);
            $em->flush();

            //COMMIT DE TODA LA TRANSACCION
            $em->getConnection()->commit();
            
            //******PARA EL PROCESO DE CALIDAD
            /*if ($sesion->get('procesocalidadid') != '0' ){
                $valproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($sesion->get('procesocalidadid'));
                $valproceso->setEsActivo('true');
                $em->persist($valproceso);
                $em->flush();
            }
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
            }*/
            //******PARA EL PROCESO DE CALIDAD
            
            $studentCorr = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudecor));            

            $dataInscriptionCorrR = array();
            $dataInscriptionCorrA = array();
            $dataInscriptionCorrE = array();
            $dataInscriptionCorrP = array();

            $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $rudecor . "') order by gestion_tipo_id_raep desc;");
            $query->execute();               
            $dataInscriptionCorr = $query->fetchAll();
            foreach ($dataInscriptionCorr as $key => $inscriptionCorr) {
                switch ($inscriptionCorr['institucioneducativa_tipo_id_raep']) {
                    case '1':
                        $dataInscriptionCorrR[$key] = $inscriptionCorr;
                    break;
                    case '2':
                        $dataInscriptionCorrA[$key] = $inscriptionCorr;
                    break;
                    case '4':
                        $dataInscriptionCorrE[$key] = $inscriptionCorr;
                    break;
                    case '5':
                        $dataInscriptionCorrP[$key] = $inscriptionCorr;
                    break;
                }
            }
                                    
            $this->session->getFlashBag()->add('success', 'Proceso realizado exitosamente. Historial del rude :' . $rudecor);
            return $this->render($this->session->get('pathSystem') . ':UnificacionRude:result.html.twig', array(                    
                    'studentCorr' => $studentCorr,
                    'dataInscriptionCorrR' => $dataInscriptionCorrR,
                    'dataInscriptionCorrA' => $dataInscriptionCorrA,
                    'dataInscriptionCorrE' => $dataInscriptionCorrE,
                    'dataInscriptionCorrP' => $dataInscriptionCorrP,                
            ));            
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $message = 'Se ha detectado inconsistencia de datos. Comuniquese con la nacional para resolver el caso.';
                $this->addFlash('notihistory', $message);            
                return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
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
