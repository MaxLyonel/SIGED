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
        $rol = $this->session->get('roluser');
        //$id_sistema = $this->get('funciones')->obtieneSistema($this->session->get('pathSystem'));
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if($request->getMethod() == 'POST' and $rol == 10){
            $form = $request->get('form');
            //dump($form);die;
            if($form['idRegla'] == 8){
                $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['rude']));
                if(!$student){
                    $mensaje = 'Rudes no encontrados para unificar.';
                    $this->addFlash('notihistory', $message);
                    $vala = '';
                    $valb = '';
                }else{
                    $students = $em->createQueryBuilder()
                                ->select('DISTINCT e.codigoRude')
                                ->from('SieAppWebBundle:Estudiante','e')
                                ->where('e.paterno = :paterno')
                                ->andWhere('e.materno = :materno')
                                ->andWhere('e.nombre = :nombre')
                                ->andWhere('e.fechaNacimiento = :fechaNacimiento')
                                ->setParameter('paterno', $student->getPaterno())
                                ->setParameter('materno', $student->getMaterno())
                                ->setParameter('nombre', $student->getNombre())
                                ->setParameter('fechaNacimiento', $student->getFechaNacimiento())
                                ->getQuery()
                                ->getResult();
                    if(count($students)<=1){
                        $message = 'No existen dos rudes para Unificar.';
                        $this->addFlash('notihistory', $message);            
                        $vala = '';
                        $valb = '';
                    }else{
                        $vala = $students[0]['codigoRude'];
                        $valb = $students[1]['codigoRude'];
                    }
                }
            }elseif($form['idRegla'] == 24 or $form['idRegla'] == 26 or $form['idRegla'] == 25){
                $rude = explode("|", $form['rude']);
                $studenta = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$rude[0]));
                $studentb = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$rude[1]));
                if($studenta and $studentb){
                    $vala = $rude[0];
                    $valb = $rude[1];
                }elseif($studenta){
                    $vala = $rude[0];
                    $valb = '';
                    $message = 'El código rude: '.$rude[1].' no existe.';
                    $this->addFlash('notihistory', $message);            
                }elseif($studentb){
                    $vala = '';
                    $valb = $rude[1];
                    $message = 'El código rude: '.$rude[0].' no existe.';
                    $this->addFlash('notihistory', $message);            
                }else{
                    $vala = '';
                    $valb = '';
                    $message = 'Los códigos rude: '.$rude[0].' y '. $rude[1] .' no existen.';
                    $this->addFlash('notihistory', $message);            
                }
            }else{
                $message = 'Inconsistencia no encontrada.';
                $this->addFlash('notihistory', $message);            
                $vala = '';
                $valb = '';
            }
            $onoffcalidad = 't';
        }elseif($rol == 8 or $rol == 7){
            $vala = '';
            $valb = '';
            $onoffcalidad = 'f';
        }else{
            $vala = '';
            $valb = '';
            $onoffcalidad = 't';
        }
        $sesion = $request->getSession();
        //$sesion->set('procesocalidadid', '0');
        //$sesion->set('procesocalidadrude', '0');
        //$sesion->set('procesocalidadgestion', '0'); 
        
        return $this->render('SieRegularBundle:UnificacionRude:index.html.twig', array(            
                    'vala' => $vala,
                    'valb' => $valb,
                    'onoffcalidad' => $onoffcalidad,
            ));
    }
    
    public function unificarverhistorialAction($rudea, $rudeb, $onoffcalidad) {
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
            $message = 'Los códigos rudes deben ser distintos.';            
            $this->addFlash('notihistory', $message);            
            return $this->render('SieRegularBundle:UnificacionRude:resulterror.html.twig' );
        }
        //*******VERIFICANDO QUE LOS RUDES NO SEAN EL MISMO

        //*******VERIFICANDO LA EXISTENCIA DEL AMBOS RUDES
        $studenta = array();
        $studentb = array();
        $studenta = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudea));
        //dump($studenta);die;
        if (!$studenta) {
            $message = 'El código '.$rudea.' no existe.';
            $this->addFlash('notihistory', $message);            
            return $this->render('SieRegularBundle:UnificacionRude:resulterror.html.twig' );
        } else {
            $studentb = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudeb));
            if (!$studentb) {
                $message = 'El código '.$rudeb.' no existe.';
                $this->addFlash('notihistory', $message);            
                return $this->render('SieRegularBundle:UnificacionRude:resulterror.html.twig' );
            }
        }
        //*******VERIFICANDO LA EXISTENCIA DEL AMBOS RUDES
        
        $validado = 0;        
        //*******VERIFICANDO QUE LOS DATOS NOMBRES APELLIDOS SEAN IGUALES        
        if  (      (trim($studenta->getNombre()) == trim($studentb->getNombre()))
                && (trim($studenta->getPaterno()) == trim($studentb->getPaterno()))
                && (trim($studenta->getMaterno()) == trim($studentb->getMaterno()))
                && (trim($studenta->getgeneroTipo()) == trim($studentb->getgeneroTipo()))
                && (trim($studenta->getfechaNacimiento()->format('Y-m-d')) == trim($studentb->getfechaNacimiento()->format('Y-m-d')))
            ) {
            $validado = 1;
        }elseif($onoffcalidad == "t") {
            $validado = 1;
        }
        else {
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
                return $this->render('SieRegularBundle:UnificacionRude:resulterror.html.twig' );
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
            $tramitea=$this->get('seguimiento')->getDiploma($rudea);
            $tramiteb=$this->get('seguimiento')->getDiploma($rudeb);

            $arrudea = array();
            $arrudeb = array();
            $message = null;

            if ($tramitea && $tramiteb) {
                $message = 'Ambos rudes cuentan con tramite de diplomas, por lo que no pueden ser unificados.</br>Para poder unificar, debe anular el/los trámite(s) de Diplomas en legalizaciones correspondiente a uno de los rudes.';
                $this->addFlash('notihistory', $message);
                return $this->render('SieRegularBundle:UnificacionRude:resulterror.html.twig' );
            } else {
                if ($tramitea == false  && $tramiteb == true) {
                    $arrudea['Diplomas'] = 0;
                    $arrudeb['Diplomas'] = 1;

                    //return $this->redirectToRoute('unificacion_ver_cor_inc', array('rudecor' => $rudeb,'rudeinc' => $rudea));
                }
                if ($tramitea == true  && $tramiteb == false) {
                    $arrudea['Diplomas'] = 1;
                    $arrudeb['Diplomas'] = 0;
                }
            }
            //*******VERIFICANDO QUE ALGUNO DE LOS RUDE NO TENGA TRAMITES EN DIPLOMAS

            //*******VERIFICANDO QUE ALGUNO DE LOS RUDE NO TENGA DATOS EN JUEGOS
            $juegosa=$this->get('seguimiento')->getJuegos($rudea);
            $juegosb=$this->get('seguimiento')->getJuegos($rudeb);
            
            if ($juegosa && $juegosb) {
                $message = 'Ambos rudes cuentan con historial en juegos.';
                $this->addFlash('notihistory', $message);
                return $this->render('SieRegularBundle:UnificacionRude:resulterror.html.twig' );
            } else {
                if ($juegosa == false  && $juegosb == true) {
                    $arrudea['Juegos'] = 0;
                    $arrudeb['Juegos'] = 1;
                }
                if ($juegosa == true  && $juegosb == false) {
                    $arrudea['Juegos'] = 1;
                    $arrudeb['Juegos'] = 0;
                }
            }
            //*******VERIFICANDO QUE ALGUNO DE LOS RUDE NO TENGA DATOS EN JUEGOS

            //*******VERIFICANDO QUE ALGUNO DE LOS RUDE NO TENGA DATOS EN OLIMPIADAS
            $olimpiadasa=$this->get('seguimiento')->getOlimpiadas($rudea);
            $olimpiadasb=$this->get('seguimiento')->getOlimpiadas($rudeb);

            if ($olimpiadasa && $olimpiadasb) {
                $message = 'Ambos rudes cuentan con historial en olimpiadas.';
                $this->addFlash('notihistory', $message);
                return $this->render('SieRegularBundle:UnificacionRude:resulterror.html.twig' );
            } else {
                if ($olimpiadasa == false  && $olimpiadasb == true) {
                    $arrudea['Olimpiadas'] = 0;
                    $arrudeb['olimpiadas'] = 1;
                }
                if ($olimpiadasa == true  && $olimpiadasb == false) {
                    $arrudea['Olimpiadas'] = 1;
                    $arrudeb['Olimpiadas'] = 0;
                }
            }
            //*******VERIFICANDO QUE ALGUNO DE LOS RUDE NO TENGA DATOS EN OLIMPIADAS

            //*******VERIFICANDO QUE ALGUNO DE LOS RUDES NO TENGA DATOS EN BACHILER DESTACADO
            $bdestacadoa=$this->get('seguimiento')->getBachillerDestacado($rudea);
            $bdestacadob=$this->get('seguimiento')->getBachillerDestacado($rudeb);
            //dump($bdestacadoa,$bdestacadob);die;
            if ($bdestacadoa && $bdestacadob) {
                $message = 'Ambos rudes cuentan con historial en Bachiller Destacado, por lo que no pueden unificarse';
                $this->addFlash('notihistory', $message);            
                return $this->render('SieRegularBundle:UnificacionRude:resulterror.html.twig' );
            } else {
                if ($bdestacadoa == false  && $bdestacadob == true) {
                    $arrudea['Bachiller Destacado'] = 0;
                    $arrudeb['Bachiller Destacado'] = 1;
                }
                if ($bdestacadoa == true  && $bdestacadob == false) {
                    $arrudea['Bachiller Destacado'] = 1;
                    $arrudeb['Bachiller Destacado'] = 0;
                }
            }
            if (count($arrudea)>0 or count($arrudeb)>0){
                //dump($arrudea,$arrudeb);die;
                if(in_array(1,$arrudea) and in_array(1,$arrudeb)){
                    $msg = '<strong>RUDE:</strong> '. $rudea;
                    foreach($arrudea as $key => $valor ){
                        if($valor == 1){
                            $msg = $msg . '</br>-' . $key . '</br>';
                        }
                    }
                    $msg = $msg . '<strong>RUDE:</strong> ' . $rudeb;
                    foreach($arrudeb as $key => $valor ){
                        if($valor == 1){
                            $msg = $msg . '</br>-' . $key . '</br>';
                        }
                    }
                    $this->addFlash('notihistory', 'Ambos rudes cuentan con historial:</br></br>'. $msg .'</br>Por lo que no pueden unificarse.</br>Si uno de los rudes cuenta con trámite en Diplomas, para poder unificar, debe anular el/los trámite(s) de Diplomas en legalizaciones.');
                    return $this->render('SieRegularBundle:UnificacionRude:resulterror.html.twig' );
                }else{
                    if(in_array(1,$arrudea)){
                        $rudecor =$rudea;
                        $rudeinc =$rudeb;
                        $array = $arrudea;
                    }else{
                        $rudecor =$rudeb;
                        $rudeinc =$rudea;
                        $array = $arrudeb;
                    }
                    $msg = "";
                    foreach($array as $key => $valor ){
                        if($valor == 1){
                            $msg = $msg . '</br>-' . $key . '</br>';
                        }
                    }
                    $this->addFlash('autoselcorr', 'Se ha seleccionado al rude con historial en:'. $msg .' Como el rude correcto.'); 
                    return $this->redirectToRoute('unificacion_ver_cor_inc', array('rudecor' => $rudecor,'rudeinc' => $rudeinc));
                }
                
            }
            if($studenta->getSegipId() == 1 or $studentb->getSegipId() == 1){
                if ($studenta->getSegipId() == 1){
                    $rudecor =$rudea;
                    $rudeinc =$rudeb;
                }
                if ($studentb->getSegipId() == 1){
                    $rudecor =$rudeb;
                    $rudeinc =$rudea;
                }
                $this->addFlash('autoselcorr', 'Se ha seleccionado al rude con el Carnet de Identidad validado por Segip como el rude correcto.'); 
                return $this->redirectToRoute('unificacion_ver_cor_inc', array('rudecor' => $rudecor,'rudeinc' => $rudeinc));
            }

            //*******FIN VERIFICANDO QUE ALGUNO DE LOS RUDES NO TENGA DATOS EN BACHILER DESTACADO
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

        return $this->render('SieRegularBundle:UnificacionRude:resulthistoriales.html.twig', array(
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
        //dump($rudecor,$rudeinc);die;
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
        //dump($dataInscriptionCorr,$dataInscriptionIncc);die;               
        //********** SE VERIFICA QUE LOS HISTORIALES NO CUENTEN CON ESTADOS SIMILARES EN LA MISMA GESTION
        
        $validado = 1;
        //********** SE VERIFICA QUE LOS HISTORIALES NO CUENTEN CON ESTADOS SIMILARES EN LA MISMA GESTION
        $validaEstadosRegular = $this->getVerificaEstadosGestion($rudecor,$rudeinc);

        if (count($validaEstadosRegular) > 0) {
            $message = "¡Proceso de dentenido! se ha detectado inconsistencia de datos :".$validaEstadosRegular[0]['subsistema']." ".$validaEstadosRegular[0]['observacion']." ".$validaEstadosRegular[0]['gestion'];
            $this->addFlash('notihistory', $message);
            $validado = 0;
            //return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
        }

        //********** SE VERIFICA QUE LOS HISTORIALES NO CUENTEN CON DOBLES INSCRIPCIONES EN LA MISMA UE Y GESTION
        $validaDobleInscripcionRegular = $this->getVerificaDobleInscripcion($rudecor,$rudeinc);
        //dump($validaDobleInscripcionRegular);die;

        if (count($validaDobleInscripcionRegular) > 0) {
            $message = "¡Proceso de dentenido! se ha detectado inconsistencia de datos :".$validaDobleInscripcionRegular[0]['subsistema']." ".$validaDobleInscripcionRegular[0]['observacion']." en la gestión: ".$validaDobleInscripcionRegular[0]['gestion'];
            $this->addFlash('notihistory', $message);
            $validado = 0;
            //return $this->render($this->session->get('pathSystem') . ':UnificacionRude:resulterror.html.twig' );
        }

        //*********** PARA ALTERNATIVA SE VERIFICA QUE ULTIMOS GRADOS TENGAN COERENCIA
        $sqlInc = "SELECT max(anio)as maxanio
                    FROM
                    (SELECT 
                    gestion, nivel, ciclo, grado,
                    case when ciclo = '1' and grado = '1' then '1' else
                    case when ciclo = '1' and grado = '2' then '2' else 
                    case when ciclo = '2' and grado = '1' then '3' else
                    case when ciclo = '2' and grado = '2' then '4' else 
                    case when ciclo = '2' and grado = '3' then '5' end end end end
                    end as anio
                    FROM (
                    SELECT
                    gestion_tipo_id_raep as gestion, superior_facultad_area_tipo_a as nivel, 
                    superior_especialidad_tipo_id_a as ciclo, superior_acreditacion_tipo_id_a as grado
                    FROM sp_genera_estudiante_historial('".$rudeinc."') 
                    WHERE superior_facultad_area_tipo_a = 15
                    ) a
                    ) b";
        $queryveraltInc = $em->getConnection()->prepare($sqlInc);
        $queryveraltInc->execute();
        $maxanioInc = $queryveraltInc->fetchAll();
        if ($maxanioInc){
            $maxInc = $maxanioInc[0]['maxanio'];
        } else {
            $maxInc = '0';
        }

        $sqlCor = "SELECT max(anio)as maxanio
                    FROM
                    (SELECT 
                    gestion, nivel, ciclo, grado,
                    case when ciclo = '1' and grado = '1' then '1' else
                    case when ciclo = '1' and grado = '2' then '2' else 
                    case when ciclo = '2' and grado = '1' then '3' else
                    case when ciclo = '2' and grado = '2' then '4' else 
                    case when ciclo = '2' and grado = '3' then '5' end end end end
                    end as anio
                    FROM (
                    SELECT
                    gestion_tipo_id_raep as gestion, superior_facultad_area_tipo_a as nivel, 
                    superior_especialidad_tipo_id_a as ciclo, superior_acreditacion_tipo_id_a as grado
                    FROM sp_genera_estudiante_historial('".$rudecor."') 
                    WHERE superior_facultad_area_tipo_a = 15
                    ) a
                    ) b";
        $queryveraltCor = $em->getConnection()->prepare($sqlCor);
        $queryveraltCor->execute();
        $maxanioCor = $queryveraltCor->fetchAll();
        if ($maxanioCor){
            $maxCor = $maxanioCor[0]['maxanio'];
        } else {
            $maxCor = '0';
        }

        if ($validado == 0){
            if (($maxInc != '0') && ($maxCor != '0')){
                if ($maxanioCor < $maxanioInc){
                    $message = "¡Proceso de dentenido! se ha detectado inconsistencia de datos :".$validaEstadosRegular[0]['subsistema']." ".$validaEstadosRegular[0]['observacion']." ".$validaEstadosRegular[0]['gestion'];
                    $this->addFlash('notihistory', $message);
                    $validado = 0;
                }
            }
        }   

        return $this->render('SieRegularBundle:UnificacionRude:resulthistorialescorinc.html.twig', array(                   
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

    public function getVerificaEstadosGestion($rudecor,$rudeinc){
        
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select cast('Regular' as varchar) as subsistema, cast('Mismo estado en la misma gestión' as varchar) as observacion, gestion_rude_b as gestion, estadomatricula_rude_b as estadomatricula from (
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
            AND b.estadomatricula_rude_b = c.estadomatricula_rude_c) regular");

        $query->execute();
        return $query->fetchAll();
    }

    public function getVerificaDobleInscripcion($rudecor,$rudeinc){
        
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select cast('Regular' as varchar) as subsistema, cast('Doble inscripcion en la misma Unidad Educativa ' as varchar) as observacion, gestion_rude_b as gestion,institucioneducativa_id_c as institucioneducativa_id,institucioneducativa_c as institucioneducativa,estadomatricula_rude_b,estadomatricula_rude_c,codigo_rude_b,codigo_rude_c
        from (
        select gestion_tipo_id_raep as gestion_rude_b, estadomatricula_fin_r as estadomatricula_rude_b,institucioneducativa_id_raep as institucioneducativa_id_b,institucioneducativa_raep as institucioneducativa_b,codigo_rude_raep as codigo_rude_b
        from sp_genera_estudiante_historial('". $rudecor ."') 
        where institucioneducativa_tipo_id_raep = 1) b 
        INNER JOIN
        (select gestion_tipo_id_raep as gestion_rude_c, estadomatricula_fin_r as estadomatricula_rude_c,institucioneducativa_id_raep as institucioneducativa_id_c,institucioneducativa_raep as institucioneducativa_c,codigo_rude_raep as codigo_rude_c
        from sp_genera_estudiante_historial('". $rudeinc ."') 
        where institucioneducativa_tipo_id_raep = 1
        ) c  ON b.gestion_rude_b = c.gestion_rude_c AND b.institucioneducativa_id_b=c.institucioneducativa_id_c");

        $query->execute();
        return $query->fetchAll();
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

        $studentDatPerInco = $em->getRepository('SieAppWebBundle:EstudianteDatopersonal')->findBy(array('estudiante' => $studentinc));        
        $studentPnpRecSabInco = $em->getRepository('SieAppWebBundle:PnpReconocimientoSaberes')->findBy(array('estudiante' => $studentinc));

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
                foreach ($inscripinco as $inscrip) {
                    $antes = $inscrip;                    
                    $inscrip->setEstudiante($studentcor);
                    //$em->persist($inscrip);
                    $em->flush();
                    //******EL DATO DEL ESTUDIANTE DESTACADO ES LLEVADO JUNTO CON LA INSCRIPCION
                    //$studentDestacInco = $em->getRepository('SieAppWebBundle:EstudianteDestacado')->findBy(array('estudianteInscripcion' => $inscrip));

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
            
            //***********ESTUDIANTE DATOS PERSONALES
            if ($studentDatPerInco) {
                foreach ($studentDatPerInco as $stuDatPer) {
                    $stuDatPer->setEstudiante($studentcor);
                    $em->persist($stuDatPer);
                    $em->flush();
                }                    
            }
            //***********PNP RECONOCIMIENTO SABERES
            if ($studentPnpRecSabInco) {
                foreach ($studentPnpRecSabInco as $stuPnpinco) {
                    $stuPnpinco->setEstudiante($studentcor);
                    $em->persist($stuPnpinco);
                    $em->flush();
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
            
            //guardado de log antiguo de datos de unificacion            
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

            //****PARA EL PROCESO DE CALIDAD******//
            $valproceso = $em->createQueryBuilder()
                                ->select('v')
                                ->from('SieAppWebBundle:ValidacionProceso','v')
                                ->where('v.esActivo is false')
                                ->where('v.validacionReglaTipo = 8 and v.solucionTipoId = 2 and (v.llave = :llave1 or v.llave = :llave2)')
                                ->orWhere('v.validacionReglaTipo in (24,25,26) and v.solucionTipoId = 0 and (v.llave = :llave3 or v.llave = :llave4)')
                                ->setParameter('llave1', $rudecor)
                                ->setParameter('llave2', $rudeinc)
                                ->setParameter('llave3', $rudecor.'|'.$rudeinc)
                                ->setParameter('llave4', $rudeinc.'|'.$rudecor)
                                ->getQuery()
                                ->getResult();
            
            if($valproceso){
                foreach($valproceso as $v){
                    $v->setEsActivo(true);
                    $em->flush();
                }
                $gestion = $valproceso[0]->getGestionTipo()->getId();
            }else{
                $gestion = $inscripcorr[0]->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            }
            
            //**PARA LA REGLA 8 La/el estudiante cuenta con más de un RUDE***/
            $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_est_dos_RUDES (:tipo, :rude, :param, :gestion)');
            $query->bindValue(':tipo', '2');
            $query->bindValue(':rude', $rudecor);
            $query->bindValue(':param', '');
            $query->bindValue(':gestion', $gestion);
            $query->execute();
            $resultado = $query->fetchAll();
            
            //**PARA LA REGLA 24 Cedula de identidad duplicada con nombres parecidos***/
            /* $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_cedula_duplicada_nom_iguales (:tipo, :validacion_proceso_id)');
            $query->bindValue(':tipo', '2');
            $query->bindValue(':validacion_proceso_id', $rudecor);
            $query->execute();
            $resultado = $query->fetchAll(); */
            
            //**PARA LA REGLA 26 Estudiantes con similitud de nombres y fecha de nacimiento***/
            $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_similitud_nombres_certf_nac (:tipo, :codigo_rude)');
            $query->bindValue(':tipo', '2');
            $query->bindValue(':codigo_rude', $rudecor);
            $query->execute();
            $resultado = $query->fetchAll();
            //****FIN DEL PROCESO DE CALIDAD******//
            
            /*** PARA EL BONO JUANCITOPINTO***/
            $bonojuancitopinto = $em->createQueryBuilder()
                                ->select('b')
                                ->from('SieAppWebBundle:BonojuancitoEstudianteValidacion','b')
                                ->where('b.codigoRude = :rude')
                                ->setParameter('rude', $rudeinc)
                                ->getQuery()
                                ->getResult();

            if($bonojuancitopinto){
                foreach($bonojuancitopinto as $b){
                    $b->setObs(json_encode(array('id_unificacion'=>$ur->getId(),'obs'=>mb_strtoupper('UNIFICACION RUDE', 'UTF-8'))));
                    $em->flush();
                }
            }
            /*** FIN DEL BONO JUANCITOPINTO***/
            //COMMIT DE TODA LA TRANSACCION
            $em->getConnection()->commit();
            //dump($valproceso,$resultado);die;
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
            $msg = "</br>";
            if($valproceso){
                $msg = "La inconsistencia de Calidad fué subsanada.</br>";
            }
                                    
            $this->session->getFlashBag()->add('success', 'Proceso realizado exitosamente.'. $msg .' Historial del rude :' . $rudecor);
            return $this->render('SieRegularBundle:UnificacionRude:result.html.twig', array(                    
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
                return $this->render('SieRegularBundle:UnificacionRude:resulterror.html.twig' );
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
