<?php

namespace Sie\JuegosBundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sie\AppWebBundle\Entity\EstudianteInscripcionJuegos;
use Sie\AppWebBundle\Entity\EstudianteDatopersonal;
use Sie\AppWebBundle\Entity\ComisionJuegosDatos;
use Sie\AppWebBundle\Form\ComisionJuegosDatosType;
use Sie\AppWebBundle\Form\ComisionJuegosEntrenadorType;
use Sie\AppWebBundle\Form\ComisionDepartamentalJuegosDatosType;
use Sie\AppWebBundle\Form\ComisionNacionalJuegosDatosType;
use Sie\AppWebBundle\Form\EstudianteJuegosFotoType;
use Sie\AppWebBundle\Form\ComisionJuegosFotoType;

class ComisionController extends Controller {

    public $session;
    public $idInstitucion;
    private $nivelId;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        //$this->aCursos = $this->fillCursos();
    }

    public function registroAcompananteIndexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestion = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $fase = 3; // nombre de la fase actual

        $objEntidad = $this->buscaEntidadFase($fase,$id_usuario);
        if (!$objEntidad) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información del Distrito'));
            return $this->redirect($this->generateUrl('sie_juegos_homepage'));
        }

        $codigoEntidad = $objEntidad[0]['id'];

        $exist = true;

        $query = $em->getConnection()->prepare("
                    select distinct nt.id as nivelid, nt.nivel as nivel, dt.id as disciplinaid, dt.disciplina as disciplina, pt.id as pruebaid, pt.prueba as prueba, gt.id as generoid, gt.genero as genero 
                    from (select * from estudiante_inscripcion_juegos where fase_tipo_id = 4 and gestion_tipo_id = ".$gestion.") as eij
                        inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                        inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                        inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                        inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                        inner join lugar_tipo as lt1 on lt1.id = jg.lugar_tipo_id_localidad
                        inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                        inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                        inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                        inner join lugar_tipo as lt5 on lt5.id = lt4.lugar_tipo_id
                    inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
                    inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                    inner join nivel_tipo as nt on nt.id = dt.nivel_tipo_id
                    inner join genero_tipo as gt on gt.id = pt.genero_tipo_id
                    where lt5.codigo = '".$codigoEntidad."' --and nt.id = 13
                    order by nt.id, dt.id, pt.id, gt.id
                ");
        $query->execute();
        $entityNiveles = $query->fetchAll();
        if ($entityNiveles) {
            $aInfoDeportes = array();
            foreach ($entityNiveles as $uDeporte) {
                //get the literal data of unidad educativa
                $sinfoDeportes = serialize(array(
                    'deportesInfo' => array('nivel' => $uDeporte['nivel'], 'disciplina' => $uDeporte['disciplina'], 'prueba' => $uDeporte['prueba'], 'genero' => $uDeporte['genero']),
                    'deportesInfoId' => array('nivelId' => $uDeporte['nivelid'], 'disciplinaId' => $uDeporte['disciplinaid'], 'pruebaId' => $uDeporte['pruebaid'], 'generoId' => $uDeporte['generoid']),
                    'requestUser' => array('codigoEntidad' => $codigoEntidad, 'gestion' => $gestion, 'fase' => $fase)
                ));

                //send the values to the next steps
                $aInfoDeportes[$uDeporte['nivel']][$uDeporte['disciplina']][$uDeporte['prueba']][$uDeporte['genero']] = array('infoDeportes' => $sinfoDeportes);
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información de las Disciplinas y Pruebas'));
            return $this->redirect($this->generateUrl('sie_juegos_homepage'));
        }

        return $this->render($this->session->get('pathSystem') . ':Comision:index.html.twig', array(
                    'infoEntidad' => $objEntidad,
                    'infoNiveles' => $aInfoDeportes,
                    'gestion' => $gestion,
                    'exist' => $exist
        ));
    }

    public function registroDepartamentalIndexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestion = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $fase = 3;

        $objEntidad = $this->buscaEntidadFase($fase,$id_usuario);
        $codigoEntidad = $objEntidad[0]['id'];

        $objDelegados = $this->listaDelegadosDepartamentalesPorFaseGestion($codigoEntidad, $gestion, 4);

        $entityDatos = new ComisionJuegosDatos();
        $form = $this->createDelegacionDepartamentalForm($entityDatos);

        $exist = true;

        return $this->render($this->session->get('pathSystem') . ':Comision:seeDepartamental.html.twig', array(
                    'form' => $form->createView(),
                    'objDelegados' => $objDelegados,
                    'objEntidad' => $objEntidad,
                    'gestion' => $gestion,                    
                    'exist' => $exist,                    
                    'codigoEntidad' => $codigoEntidad,
                    'fase' => 4,
                    'gestion' => $gestion,
        ));
    }

    public function registroNacionalIndexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestion = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $fase = 4;

        $objEntidad = $this->buscaEntidadFase($fase,$id_usuario);
        $codigoEntidad = $objEntidad[0]['id'];

        //$objDelegados = $this->listaDelegadosNacionalesPorFaseGestion($codigoEntidad, $gestion, 4);

        $entityDatos = new ComisionJuegosDatos();
        $form = $this->createDelegacionNacionalForm($entityDatos);

        $exist = true;

        return $this->render($this->session->get('pathSystem') . ':Comision:indexNacional.html.twig', array(
                    'form' => $form->createView(),
                    //'objDelegados' => $objDelegados,
                    'objEntidad' => $objEntidad,
                    'gestion' => $gestion,                    
                    'exist' => $exist,                    
                    'codigoEntidad' => $codigoEntidad,
                    'fase' => 4,
        ));
    }


    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function listaAcompananteAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        //get the info ue
        $infoDeporte = $request->get('infoDeportes');
        $ainfoDeporte = unserialize($infoDeporte);
        //get the values throght the infoUe
        $codigoEntidad = $ainfoDeporte['requestUser']['codigoEntidad'];
        $gestion = $ainfoDeporte['requestUser']['gestion'];
        $fase = $ainfoDeporte['requestUser']['fase'];
        //$iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivelId = $ainfoDeporte['deportesInfoId']['nivelId'];
        $disciplinaId = $ainfoDeporte['deportesInfoId']['disciplinaId'];
        $pruebaId = $ainfoDeporte['deportesInfoId']['pruebaId'];
        $generoId = $ainfoDeporte['deportesInfoId']['generoId'];
        $nivel = $ainfoDeporte['deportesInfo']['nivel'];
        $disciplina = $ainfoDeporte['deportesInfo']['disciplina'];
        $prueba = $ainfoDeporte['deportesInfo']['prueba'];
        $genero = $ainfoDeporte['deportesInfo']['genero'];
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        //die($codigoEntidad." - ".$gestion." - ".$fase." - ".$nivelId." - ".$disciplinaId." - ".$pruebaId." - ".$generoId);    
        $objDelegados = $this->listaDelegadosPorFaseNivelDisciplinaPruebaGenero($codigoEntidad, $gestion, $fase, $nivelId, $disciplinaId, $pruebaId, $generoId);

        $exist = true;
        $aData = array();
        //check if the data exist

        if ($objDelegados) {
            //$objDelegados = $objDelegados[0];
            $aData = serialize(array('codigoEntidad' => $codigoEntidad, 'nivel' => $nivelId, 'disciplina' => $disciplinaId, 'prueba' => $pruebaId, 'genero' => $generoId));
            $exist = true;
        } else {
            //$message = 'No existen estudiantes inscritos...';
            //$this->addFlash('warninsueall', $message);
            $aData = serialize(array('codigoEntidad' => $codigoEntidad, 'nivel' => $nivelId, 'disciplina' => $disciplinaId, 'prueba' => $pruebaId, 'genero' => $generoId));
            $objDelegados = array();
            $exist = true;
        }
        $entityDatos = new ComisionJuegosDatos();
        $form = $this->createDelegacionEstudianteForm($entityDatos);

        return $this->render($this->session->get('pathSystem') . ':Comision:seeDelegados.html.twig', array(
                    'form' => $form->createView(),
                    'objDelegados' => $objDelegados,
                    'codigoEntidad' => $codigoEntidad,
                    'nivel' => $nivel,
                    'gestion' => $gestion,
                    'aData' => $aData,
                    'disciplina' => $disciplina,
                    'prueba' => $prueba,
                    'genero' => $genero,
                    'infoDeporte' => $infoDeporte,
                    'fase' => $fase,
                    'exist' => $exist
        ));
    }

    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function listaAcompananteSaveAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //if ($request->isMethod('POST')) {

        //$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Registro de delegados cerrado'));  
        //return $this->redirect($this->generateUrl('sie_juegos_comision_acompanante_lista_index'));
        
        /*
         * Recupera datos del formulario
         */
        //get the info ue
        $infoDeporte = $request->get('infoDeportes');
        $ainfoDeporte = unserialize($infoDeporte);
        //get the values throght the infoUe
        $codigoEntidad = $ainfoDeporte['requestUser']['codigoEntidad'];
        $gestion = $ainfoDeporte['requestUser']['gestion'];
        $fase = $ainfoDeporte['requestUser']['fase'];
        //$iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivelId = $ainfoDeporte['deportesInfoId']['nivelId'];
        $disciplinaId = $ainfoDeporte['deportesInfoId']['disciplinaId'];
        $pruebaId = $ainfoDeporte['deportesInfoId']['pruebaId'];
        $generoId = $ainfoDeporte['deportesInfoId']['generoId'];
        $nivel = $ainfoDeporte['deportesInfo']['nivel'];
        $disciplina = $ainfoDeporte['deportesInfo']['disciplina'];
        $prueba = $ainfoDeporte['deportesInfo']['prueba'];
        $genero = $ainfoDeporte['deportesInfo']['genero'];

        $query = $em->getConnection()->prepare("select * from fase_tipo where id = ".$fase);
        $query->execute();
        $faseTipoEntity = $query->fetchAll();

        if($nivelId == 12 and !$faseTipoEntity[0]['esactivo_primaria']){
            $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Primario concluyeron")); 
            return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
        }
        if($nivelId == 13 and !$faseTipoEntity[0]['esactivo_secundaria']){
            $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Secundaria concluyeron")); 
            return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
        }


        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            $entityComisionJuegosDatos = new ComisionJuegosDatos();                      
            $form = $this->createDelegacionEstudianteForm($entityComisionJuegosDatos);
            $form->handleRequest($request);
            $comisionId = $entityComisionJuegosDatos->getComisionTipoId();
            $posicion = $entityComisionJuegosDatos->getPosicion();
            $confirmaRegistro = $this->verificaCupoDelegadosPorEntidadPruebaPosicion($codigoEntidad, $gestion, $fase, $nivelId, $disciplinaId, $pruebaId, $generoId, $comisionId, $posicion);

            if ($confirmaRegistro != ""){            
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $confirmaRegistro));  
                return $this->redirect($this->generateUrl('sie_juegos_comision_acompanante_lista_index'));
            } 

            if ($form->isValid()) {
                if (null != $form->get('foto')->getData()) {
                    $file = $entityComisionJuegosDatos->getFoto();

                    $filename = md5(uniqid()) . '.' . $file->guessExtension();
                    
                    $filesize = $file->getClientSize();
                    if ($filesize/1024 < 501) {
                        $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/fotos_juegos';
                        $file->move($adjuntoDir, $filename);

                        $entityComisionJuegosDatos->setFoto($filename);
                        
                    } else {
                        $this->get('session')->getFlashBag()->set(
                            'danger',
                            array(
                                'title' => 'Alerta!',
                                'message' => 'Fotografia muy grande, favor ingresar una fotografía que no exceda los 500KiB.'
                            )
                        );
                        return $this->redirect($this->generateUrl('sie_juegos_comision_acompanante_lista_index'));  
                    } 

                    
                }
            }                
            
            $em = $this->getDoctrine()->getManager();
            $entityPruebaTipo = $em->getRepository('SieAppWebBundle:PruebaTipo')->findOneBy(array('id' => $pruebaId));

            $entityComisionJuegosDatos->setDepartamentoTipo($codigoEntidad);
            $entityComisionJuegosDatos->setPruebaTipo($entityPruebaTipo);
            $entityComisionJuegosDatos->setGestionTipoId($gestionActual);                                                          
            
            $em = $this->getDoctrine()->getManager(); 
            $em->persist($entityComisionJuegosDatos);
            $em->flush(); 

            $em->getConnection()->commit();
            $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Registro guardado de forma correcta'));  
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Problemas al guardar el registro, intente nuevamente'));  
        } 

        return $this->redirect($this->generateUrl('sie_juegos_comision_acompanante_lista_index'));
    }
    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function registroDepartamentalSaveAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //if ($request->isMethod('POST')) {


        //$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Registro de delegados cerrado'));  
        //return $this->redirect($this->generateUrl('sie_juegos_comision_acompanante_lista_index'));
        
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            $codigoEntidad = $request->get('entidad');
            $gestion = $request->get('gestion');
            $fase = $request->get('fase');
            $entityComisionJuegosDatos = new ComisionJuegosDatos();                      
            $form = $this->createDelegacionDepartamentalForm($entityComisionJuegosDatos);
            $form->handleRequest($request);
            $comisionId = $entityComisionJuegosDatos->getComisionTipoId();

            $entityComisionTipo = $em->getRepository('SieAppWebBundle:ComisionTipo')->findOneBy(array('id' => $comisionId));

            $nivel = $entityComisionTipo->getNivelTipoId();

            $query = $em->getConnection()->prepare("select * from fase_tipo where id = ".$fase);
            $query->execute();
            $faseTipoEntity = $query->fetchAll();


            if($nivel == 12 and !$faseTipoEntity[0]['esactivo_primaria']){
                $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Primario concluyeron")); 
                return $this->redirectToRoute('sie_juegos_comision_departamental_lista_index');
            }
            if($nivel == 13 and !$faseTipoEntity[0]['esactivo_secundaria']){
                $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Secundaria concluyeron")); 
                return $this->redirectToRoute('sie_juegos_comision_departamental_lista_index');
            }

            $confirmaRegistro = $this->verificaCupoDelegadosDepartamentalesPorEntidad($codigoEntidad, $gestion, $fase, $comisionId);

            if ($confirmaRegistro != ""){            
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $confirmaRegistro));  
                return $this->redirect($this->generateUrl('sie_juegos_comision_departamental_lista_index'));
            } 

            if ($form->isValid()) {
                if (null != $form->get('foto')->getData()) {
                    $file = $entityComisionJuegosDatos->getFoto();

                    $filename = md5(uniqid()) . '.' . $file->guessExtension();
                    
                    $filesize = $file->getClientSize();
                    if ($filesize/1024 < 501) {
                        $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/fotos_juegos';
                        $file->move($adjuntoDir, $filename);

                        $entityComisionJuegosDatos->setFoto($filename);
                        
                    } else {
                        $this->get('session')->getFlashBag()->set(
                            'danger',
                            array(
                                'title' => 'Alerta!',
                                'message' => 'Fotografia muy grande, favor ingresar una fotografía que no exceda los 500KiB.'
                            )
                        );
                        return $this->redirect($this->generateUrl('sie_juegos_comision_departamental_lista_index'));  
                    } 

                    
                }
            }

            if($nivel == 12){
                $PruebaTipo = 0;
            }
            if($nivel == 13){
                $PruebaTipo = 0;
            }

            $em = $this->getDoctrine()->getManager();
            $entityPruebaTipo = $em->getRepository('SieAppWebBundle:PruebaTipo')->findOneBy(array('id' => 0));
            $entityFaseTipo = $em->getRepository('SieAppWebBundle:FaseTipo')->findOneBy(array('id' => 4));

            $entityComisionJuegosDatos->setDepartamentoTipo($codigoEntidad);
            $entityComisionJuegosDatos->setPruebaTipo($entityPruebaTipo);
            $entityComisionJuegosDatos->setGestionTipoId($gestionActual); 
            $entityComisionJuegosDatos->setObs('');   
            $entityComisionJuegosDatos->setFaseTipo($entityFaseTipo);  
            
            $em = $this->getDoctrine()->getManager(); 
            $em->persist($entityComisionJuegosDatos);
            $em->flush(); 

            $em->getConnection()->commit();
            $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Registro guardado de forma correcta'));  
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Problemas al guardar el registro, intente nuevamente'));  
        } 

        return $this->redirect($this->generateUrl('sie_juegos_comision_departamental_lista_index'));
    }

    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function acreditacionRegistroImpresionAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //if ($request->isMethod('POST')) {

        $objEntidad = $this->buscaEntidadFase(4,$id_usuario);        
        $codigoEntidad = $objEntidad[0]['id'];
        $id = $request->get('id');
        $comisionId = $request->get('comision');    

        switch ($comisionId) {
            case 101:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_depotista_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 111:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_cultural_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 112:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_cultural_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 118:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_prensa_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 119:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_salud_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 120:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_seguridad_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 121:           
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_invitado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 122:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_apoyo_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 131:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_apoyo_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 140:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 141:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 147:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 148:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 149:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 150:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 151:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 152:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 153:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 154:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 142:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 143:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 123:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_apoyo_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 146:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_organizador_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 115:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_organizador_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 10:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_organizador_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 132:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_organizador_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 133:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_organizador_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 134:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_organizador_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 117:              
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_comitetecnico_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 70:           
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_invitado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 30:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_juez_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 144:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_juez_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 40:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_prensa_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 50:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_salud_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 60:          
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_seguridad_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 126:        
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_apoyo_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 111:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_danza_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 122:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_danza_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 12:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 13:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 102:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 11:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 20:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_comitetecnico_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 103:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 104:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 105:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 106:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 107:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 108:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 109:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 110:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 110:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 145:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_hospedaje_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 155:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_hospedaje_v1.rptdesign&__format=pdf&id='.$id;
                break;
            case 156:         
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_delegado_v1.rptdesign&__format=pdf&id='.$id;
                break;
            default:           
                $reporte = $this->container->getParameter('urlreportweb') . 'jdp_crd_depotista_v1.rptdesign&__format=pdf&id='.$id;
                break;
        }


        $arch = $id.'_'.$gestionActual.'_JUEGOS_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($reporte));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response; 

        
    }

    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function acreditacionRegistroEditaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //if ($request->isMethod('POST')) {
        
        $comision = $request->get('comision');  
        $objEntidad = $this->buscaEntidadFase(4,$id_usuario);      
        $codigoEntidad = $objEntidad[0]['id'];
        $id = $request->get('id'); 
        $estudiante = $request->get('estudiante');     

         $em = $this->getDoctrine()->getManager();
        if($comision==101){
            $entityEstudianteJuegos = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos')->findOneBy(array('id'=>$id));
            $estudianteId = $entityEstudianteJuegos->getEstudianteInscripcion()->getEstudiante()->getId();
            $entityDatos = new EstudianteDatopersonal();
            $formFoto = $this->createCreateEstudianteFotoForm($entityDatos);
        } else {      
            $estudianteId = 0;
            $entityDatos = $em->getRepository('SieAppWebBundle:ComisionJuegosDatos')->findOneBy(array('id'=>$id));
            $formFoto = $this->createCreateComisionFotoForm($entityDatos);
        }        

        $entityDatos = new ComisionJuegosDatos();
        $form = $this->createDelegacionNacionalForm($entityDatos);

        $exist = true;

        return $this->render($this->session->get('pathSystem') . ':Comision:indexNacional.html.twig', array(
                    'form' => $form->createView(),
                    'formFoto' => $formFoto->createView(),
                        'objEntidad' => $objEntidad,
                        'gestion' => $gestionActual,  
                        'estudiante' => $estudianteId,                   
                        'exist' => $exist,       
                        'inscripcion' =>  $id,
                        'codigoEntidad' => $codigoEntidad,
                        'fase' => 4,
        ));    
    }

    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function delegadoRegistroEditaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //if ($request->isMethod('POST')) {
        
        $fase = $request->get('fase'); 
        $comision = $request->get('comision');  
        $objEntidad = $this->buscaEntidadFase($fase,$id_usuario);      
        $codigoEntidad = $objEntidad[0]['id'];
        $id = $request->get('id'); 
        $estudiante = $request->get('estudiante');     

         $em = $this->getDoctrine()->getManager();
        if($comision==101){
            $entityEstudianteJuegos = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos')->findOneBy(array('id'=>$id));
            $estudianteId = $entityEstudianteJuegos->getEstudianteInscripcion()->getEstudiante()->getId();
            $entityDatos = new EstudianteDatopersonal();
            $formFoto = $this->createCreateEstudianteFotoForm($entityDatos);
        } else {      
            $estudianteId = 0;
            $entityDatos = $em->getRepository('SieAppWebBundle:ComisionJuegosDatos')->findOneBy(array('id'=>$id));
            $formFoto = $this->createCreateComisionFotoForm($entityDatos);
        }        

        $entityDatos = new ComisionJuegosDatos();
        $form = $this->createDelegacionNacionalForm($entityDatos);

        $exist = true;

        return $this->render($this->session->get('pathSystem') . ':Comision:indexNacional.html.twig', array(
                    'form' => $form->createView(),
                    'formFoto' => $formFoto->createView(),
                        'objEntidad' => $objEntidad,
                        'gestion' => $gestionActual,  
                        'estudiante' => $estudianteId,                   
                        'exist' => $exist,       
                        'inscripcion' =>  $id,
                        'codigoEntidad' => $codigoEntidad,
                        'fase' => 4,
        ));  
    }

    private function createCreateEstudianteFotoForm(EstudianteDatopersonal $entity)
    { 
        //$entity->setPruebaTipo();
        $form = $this->createForm(new EstudianteJuegosFotoType(), $entity, array(
            'action' => $this->generateUrl('sie_juegos_acreditacion_registro_save_estudiante'),
            'method' => 'POST', 
        ));  
        return $form;
        
    }

    private function createCreateComisionFotoForm(ComisionJuegosDatos $entity)
    { 
        //$entity->setPruebaTipo();
        $form = $this->createForm(new ComisionJuegosFotoType(), $entity, array(
            'action' => $this->generateUrl('sie_juegos_acreditacion_registro_save_comision'),
            'method' => 'POST', 
        ));   
        return $form;
        
    }



    private function createCreateComisionDelegadoFotoForm(ComisionJuegosDatos $entity)
    { 
        //$entity->setPruebaTipo();
        $form = $this->createForm(new ComisionJuegosFotoType(), $entity, array(
            'action' => $this->generateUrl('sie_juegos_acreditacion_registro_save_delegado'),
            'method' => 'POST', 
        ));   
        return $form;
        
    }

    public function acreditacionRegistroSaveEstudianteAction(Request $request)
    { 
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 
        //if ($request->isMethod('POST')) {
        $em = $this->getDoctrine()->getManager();

        $estudianteId = $request->get('id');
        $inscripcionId = $request->get('estudiante');
        //$entityEstudianteDatopersonal = new EstudianteDatopersonal();
        $entityEstudianteDatopersonal = $em->getRepository('SieAppWebBundle:EstudianteDatopersonal')->findOneBy(array('estudiante' => $inscripcionId,'gestionTipo' => $gestionActual));
             
        $form = $this->createCreateEstudianteFotoForm($entityEstudianteDatopersonal);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if (null != $form->get('foto')->getData()) {
                $file = $entityEstudianteDatopersonal->getFoto();

                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                
                $filesize = $file->getClientSize();
                if ($filesize/1024 < 501) {
                    $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/fotos_juegos';
                    $file->move($adjuntoDir, $filename);

                    $entityEstudianteDatopersonal->setFoto($filename);
                    
                } else {
                    $this->get('session')->getFlashBag()->set(
                        'danger',
                        array(
                            'title' => 'Alerta!',
                            'message' => 'Fotografia muy grande, favor ingresar una fotografía que no exceda los 500KiB.'
                        )
                    );   
                    return $this->redirect($this->generateUrl('sie_juegos_comision_nacional_lista_index')); 
                }              
            }
        }                        
        
        $em = $this->getDoctrine()->getManager();
        $entityEstudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' => $inscripcionId));
        $entityGestion = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestionActual));

        //$entityEstudianteDatopersonal->setEstudiante($entityEstudiante);
        //$entityEstudianteDatopersonal->setEstatura($estatura);
        //$entityEstudianteDatopersonal->setTalla($talla);
        //$entityEstudianteDatopersonal->setPeso($peso);  
        //$entityEstudianteDatopersonal->setGestionTipo($entityGestion);                                                              
        $em = $this->getDoctrine()->getManager(); 
        $em->persist($entityEstudianteDatopersonal);
        $em->flush(); 

        $this->get('session')->getFlashBag()->set(
            'success',
            array(
                'title' => 'correcto!',
                'message' => 'Fotografia registrada'
            )
        );  
        
        return $this->redirect($this->generateUrl('sie_juegos_comision_nacional_lista_index'));   
    }


    public function acreditacionRegistroSaveComisionAction(Request $request)
    { 
        
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 
        //if ($request->isMethod('POST')) {
        $em = $this->getDoctrine()->getManager();
        
        $estudianteId = $request->get('id');
        $inscripcionId = $request->get('inscripcion');

        $entityEstudianteDatopersonal = $em->getRepository('SieAppWebBundle:ComisionJuegosDatos')->findOneBy(array('id' => $inscripcionId));                 
        $form = $this->createCreateComisionFotoForm($entityEstudianteDatopersonal);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (null != $form->get('foto')->getData()) {
                $file = $entityEstudianteDatopersonal->getFoto();

                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                
                $filesize = $file->getClientSize();
                if ($filesize/1024 < 501) {
                    $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/fotos_juegos';
                    $file->move($adjuntoDir, $filename);

                    $entityEstudianteDatopersonal->setFoto($filename);
                    
                } else {
                    $this->get('session')->getFlashBag()->set(
                        'danger',
                        array(
                            'title' => 'Alerta!',
                            'message' => 'Fotografia muy grande, favor ingresar una fotografía que no exceda los 500KiB.'
                        )
                    );   
                    return $this->redirect($this->generateUrl('sie_juegos_comision_nacional_lista_index')); 
                }              
            }
        }                        
        
        //$entityEstudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' => $estudianteId));

        //$entityEstudianteDatopersonal->setEstudiante($entityEstudiante);
        //$entityEstudianteDatopersonal->setEstatura($estatura);
        //$entityEstudianteDatopersonal->setTalla($talla);
        //$entityEstudianteDatopersonal->setPeso($peso);   
        $em->persist($entityEstudianteDatopersonal);
        $em->flush(); 

        $this->get('session')->getFlashBag()->set(
            'success',
            array(
                'title' => 'correcto!',
                'message' => 'Fotografia registrada'
            )
        );  
        
        return $this->redirect($this->generateUrl('sie_juegos_comision_nacional_lista_index'));   
    }



    public function acreditacionRegistroSaveDelegadoAction(Request $request)
    { 
        
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 
        //if ($request->isMethod('POST')) {
        $em = $this->getDoctrine()->getManager();
        
        $estudianteId = $request->get('id');
        $inscripcionId = $request->get('inscripcion');
    

        $entityEstudianteDatopersonal = $em->getRepository('SieAppWebBundle:ComisionJuegosDatos')->findOneBy(array('id' => $inscripcionId));                 
        $form = $this->createCreateComisionDelegadoFotoForm($entityEstudianteDatopersonal);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (null != $form->get('foto')->getData()) {
                $file = $entityEstudianteDatopersonal->getFoto();

                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                
                $filesize = $file->getClientSize();
                if ($filesize/1024 < 501) {
                    $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/fotos_juegos';
                    $file->move($adjuntoDir, $filename);

                    $entityEstudianteDatopersonal->setFoto($filename);
                    
                } else {
                    $this->get('session')->getFlashBag()->set(
                        'danger',
                        array(
                            'title' => 'Alerta!',
                            'message' => 'Fotografia muy grande, favor ingresar una fotografía que no exceda los 500KiB.'
                        )
                    );   
                    return $this->redirect($this->generateUrl('sie_juegos_comision_nacional_lista_index')); 
                }              
            }
        }                        
        
        //$entityEstudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' => $estudianteId));
        //$entityEstudianteDatopersonal->setEstudiante($entityEstudiante);
        //$entityEstudianteDatopersonal->setEstatura($estatura);
        //$entityEstudianteDatopersonal->setTalla($talla);
        //$entityEstudianteDatopersonal->setPeso($peso);   
        $em->persist($entityEstudianteDatopersonal);
        $em->flush(); 

        $this->get('session')->getFlashBag()->set(
            'success',
            array(
                'title' => 'correcto!',
                'message' => 'Fotografia registrada'
            )
        );  
        
        return $this->redirect($this->generateUrl('sie_juegos_comision_entrenador_f3_lista_index'));   
    }
    
    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function acreditacionRegistroEliminaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //if ($request->isMethod('POST')) {

        $objEntidad = $this->buscaEntidadFase(4,$id_usuario);        
        $codigoEntidad = $objEntidad[0]['id'];
        
        $id = $request->get('id');
        $comision = $request->get('comision');  

        $em = $this->getDoctrine()->getManager();
        $entityDatos = $em->getRepository('SieAppWebBundle:ComisionJuegosDatos')->findOneBy(array('id'=>$id));
        $em->remove($entityDatos);
        $em->flush();
        $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Registro eliminado de forma correcta'));    
        return $this->redirect($this->generateUrl('sie_juegos_comision_nacional_lista_index'));     
    }

    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function acreditacionBuscaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //if ($request->isMethod('POST')) {

        $objEntidad = $this->buscaEntidadFase(4,$id_usuario);        
        $codigoEntidad = $objEntidad[0]['id'];
        $cedulaRude = $request->get('ci');

        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                select * from (
                select ejd.id, ct.id as comision_id, ct.comision, ejd.carnet_identidad as documento, ejd.nombre||' '||ejd.paterno||' '||ejd.materno as nombre
                from comision_juegos_datos as ejd
                inner join comision_tipo as ct on ct.id = ejd.comision_tipo_id
                inner join genero_tipo as gt on gt.id = ejd.genero_tipo
                inner join departamento_tipo as det on det.id = departamento_tipo
                left join prueba_tipo as pt on pt.id = ejd.prueba_tipo_id
                left join disciplina_tipo as dt on dt.id =  pt.disciplina_tipo_id
                left join genero_tipo as gtp on gtp.id = pt.genero_tipo_id
                where ejd.gestion_tipo_id = ".$gestionActual." and ejd.carnet_identidad like '%".$cedulaRude."%' and ejd.fase_tipo_id = 4 and ct.nivel_tipo_id = 12
                --and ejd.comision_tipo_id in (101,111,115,132,133,134,117,121,30,118,119,120,131,122,12,13,102,11,103,104,105,106,107,108,109,110)
                union all
                select eij.id, case eij.prueba_tipo_id when 0 then 111 else 101 end as comision_id, case eij.prueba_tipo_id when 0 then 'Estudiante - Danza' else 'Estudiante Deportista' end as comision, e.carnet_identidad||' - '||e.codigo_rude as documento, e.nombre||' '||e.paterno||' '||e.materno as nombre 
                from estudiante_inscripcion_juegos as eij
                inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id =  pt.disciplina_tipo_id
                inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                inner join estudiante as e on e.id = ei.estudiante_id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                where eij.gestion_tipo_id = ".$gestionActual." and (e.codigo_rude like '%".$cedulaRude."%' or e.carnet_identidad like '%".$cedulaRude."%') and eij.fase_tipo_id = 4 and iec.nivel_tipo_id = 12
                ) as v
                order by id desc
            ");
        $queryEntidad->execute();
        $objListado = $queryEntidad->fetchAll();

        if (count($objListado) < 1){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No registrado, intente nuevamente'));  
            return $this->redirect($this->generateUrl('sie_juegos_comision_nacional_lista_index'));
        }

        $entityDatos = new ComisionJuegosDatos();
        $form = $this->createDelegacionNacionalForm($entityDatos);

        $exist = true;

        return $this->render($this->session->get('pathSystem') . ':Comision:indexNacional.html.twig', array(
                    'form' => $form->createView(),
                        'objDelegados' => $objListado,
                        'objEntidad' => $objEntidad,
                        'gestion' => $gestionActual,                    
                        'exist' => $exist,                    
                        'codigoEntidad' => $codigoEntidad,
                        'fase' => 4,
        ));
    }

    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function registroNacionalFormSaveAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //if ($request->isMethod('POST')) {
        
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            $codigoEntidad = $request->get('entidad');
            $gestion = $request->get('gestion');
            $fase = $request->get('fase');
            $entityComisionJuegosDatos = new ComisionJuegosDatos();                      
            $form = $this->createDelegacionNacionalForm($entityComisionJuegosDatos);
            $form->handleRequest($request);
            $comisionId = $entityComisionJuegosDatos->getComisionTipoId();

            $nivel = $entityComisionJuegosDatos->getPruebaTipo()->getDisciplinaTipo()->getNivelTipo()->getId();
            $departamento = $entityComisionJuegosDatos->getDepartamentoTipo()->getId();

            //$confirmaRegistro = $this->verificaCupoDelegadosDepartamentalesPorEntidad($codigoEntidad, $gestion, $fase, $comisionId);

            //if ($confirmaRegistro != ""){            
            //    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $confirmaRegistro));  
            //    return $this->redirect($this->generateUrl('sie_juegos_comision_nacional_lista_index'));
            //} 

            if ($form->isValid()) {
                if (null != $form->get('foto')->getData()) {
                    $file = $entityComisionJuegosDatos->getFoto();

                    $filename = md5(uniqid()) . '.' . $file->guessExtension();
                    
                    $filesize = $file->getClientSize();
                    if ($filesize/1024 < 501) {
                        $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/fotos_juegos';
                        $file->move($adjuntoDir, $filename);

                        $entityComisionJuegosDatos->setFoto($filename);
                        
                    } else {
                        $this->get('session')->getFlashBag()->set(
                            'danger',
                            array(
                                'title' => 'Alerta!',
                                'message' => 'Fotografia muy grande, favor ingresar una fotografía que no exceda los 500KiB.'
                            )
                        );
                        return $this->redirect($this->generateUrl('sie_juegos_comision_nacional_lista_index'));  
                    } 

                    
                }
            }                
            
            $em = $this->getDoctrine()->getManager();
            //$entityPruebaTipo = $em->getRepository('SieAppWebBundle:PruebaTipo')->findOneBy(array('id' => 0));

            $entityComisionJuegosDatos->setDepartamentoTipo($departamento);
            //$entityComisionJuegosDatos->setPruebaTipo($entityPruebaTipo);
            $entityComisionJuegosDatos->setGestionTipoId($gestionActual);    

            $entityFaseTipo = $em->getRepository('SieAppWebBundle:FaseTipo')->findOneBy(array('id' => 4));
            $entityComisionJuegosDatos->setFaseTipo($entityFaseTipo);                                                        

            $em = $this->getDoctrine()->getManager(); 
            $em->persist($entityComisionJuegosDatos);
            $em->flush(); 

            $em->getConnection()->commit();
            $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Registro guardado de forma correcta'));  
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Problemas al guardar el registro, intente nuevamente'));  
        } 

        return $this->redirect($this->generateUrl('sie_juegos_comision_nacional_lista_index'));
    }

    /**
     * get request
     * @param type $request, $departamento, $gestion, $fase, $nivel, $disciplina, $prueba, $genero 
     * return list of students
     */
    public function listaDelegadosPorFaseNivelDisciplinaPruebaGenero($codigoEntidad, $gestion, $fase, $nivelId, $disciplinaId, $pruebaId, $generoId) {
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                select ejd.id, ".$fase." as fase, ejd.carnet_identidad, ejd.nombre, ejd.paterno, ejd.materno, ct.comision, dt.disciplina, pt.prueba, gtp.genero as genero_prueba, ejd.foto, ejd.posicion
                from comision_juegos_datos as ejd
                inner join comision_tipo as ct on ct.id = ejd.comision_tipo_id
                inner join genero_tipo as gt on gt.id = ejd.genero_tipo
                left join departamento_tipo as det on det.id = departamento_tipo
                inner join prueba_tipo as pt on pt.id = ejd.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id =  pt.disciplina_tipo_id
                inner join genero_tipo as gtp on gtp.id = pt.genero_tipo_id
                inner join institucioneducativa as ie on ie.id = ejd.institucioneducativa_id 
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id 
                inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito 
                inner join lugar_tipo as ltl on ltl.id = jg.lugar_tipo_id_localidad 
                inner join lugar_tipo as lt1 on lt1.id = ltl.lugar_tipo_id 
                inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id 
                inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id 
                inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id 
                where (case ".$fase." when 1 then  lt.id = ".$codigoEntidad." when 2 then jg.circunscripcion_tipo_id = ".$codigoEntidad." when 3 then det.id = ".$codigoEntidad." else true end) and ejd.gestion_tipo_id = ".$gestion." and pt.id = ".$pruebaId." and ejd.fase_tipo_id = ".($fase+1)."
                order by dt.disciplina, pt.prueba, gtp.genero, nombre, paterno, materno
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    /**
     * get request
     * @param type $request, $departamento, $gestion, $fase, $nivel, $disciplina, $prueba, $genero 
     * return list of students
     */
    public function listaDelegadosClasificadosPorFaseNivelDisciplinaPruebaGenero($codigoEntidad, $gestion, $fase, $nivelId, $disciplinaId, $pruebaId, $generoId) {
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                select ejd.id, ".$fase." as fase, ejd.carnet_identidad, ejd.nombre, ejd.paterno, ejd.materno, ct.id as comisionId, ct.comision, dt.disciplina, pt.prueba, gtp.genero as genero_prueba, ejd.foto, ejd.posicion
                from comision_juegos_datos as ejd
                inner join comision_tipo as ct on ct.id = ejd.comision_tipo_id
                inner join genero_tipo as gt on gt.id = ejd.genero_tipo
                left join departamento_tipo as det on det.id = departamento_tipo
                inner join prueba_tipo as pt on pt.id = ejd.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id =  pt.disciplina_tipo_id
                inner join genero_tipo as gtp on gtp.id = pt.genero_tipo_id
                inner join institucioneducativa as ie on ie.id = ejd.institucioneducativa_id 
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id 
                inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito 
                inner join lugar_tipo as ltl on ltl.id = jg.lugar_tipo_id_localidad 
                inner join lugar_tipo as lt1 on lt1.id = ltl.lugar_tipo_id 
                inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id 
                inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id 
                inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id 
                where (case ".$fase." when 2 then  lt.id = ".$codigoEntidad." when 3 then jg.circunscripcion_tipo_id = ".$codigoEntidad." when 4 then det.id = ".$codigoEntidad." else true end) and ejd.gestion_tipo_id = ".$gestion." and pt.id = ".$pruebaId." and ejd.fase_tipo_id = ".($fase)."
                order by dt.disciplina, pt.prueba, gtp.genero, nombre, paterno, materno

            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    /**
     * get request
     * @param type $request, $departamento, $gestion, $fase, $nivel, $disciplina, $prueba, $genero 
     * return list of students
     */
    public function listaDelegadosClasificadoPorFaseNivelDisciplinaPruebaGenero($codigoEntidad, $gestion, $fase, $nivelId, $disciplinaId, $pruebaId, $generoId) {
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                select ejd.id, ".$fase." as fase, ejd.carnet_identidad, ejd.nombre, ejd.paterno, ejd.materno, ct.comision, dt.disciplina, pt.prueba, gtp.genero as genero_prueba, ejd.foto, ejd.posicion
                from comision_juegos_datos as ejd
                inner join comision_tipo as ct on ct.id = ejd.comision_tipo_id
                inner join genero_tipo as gt on gt.id = ejd.genero_tipo
                left join departamento_tipo as det on det.id = departamento_tipo
                inner join prueba_tipo as pt on pt.id = ejd.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id =  pt.disciplina_tipo_id
                inner join genero_tipo as gtp on gtp.id = pt.genero_tipo_id
                inner join institucioneducativa as ie on ie.id = ejd.institucioneducativa_id 
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id 
                inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito 
                inner join lugar_tipo as ltl on ltl.id = jg.lugar_tipo_id_localidad 
                inner join lugar_tipo as lt1 on lt1.id = ltl.lugar_tipo_id 
                inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id 
                inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id 
                inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id 
                inner join (
                    select distinct eiec.institucioneducativa_id, eeij.prueba_tipo_id, eeij.fase_tipo_id, eeij.posicion from estudiante_inscripcion_juegos as eeij
                    inner join estudiante_inscripcion as eei on eei.id = eeij.estudiante_inscripcion_id
                    inner join institucioneducativa_curso as eiec on eiec.id = eei.institucioneducativa_curso_id
                    where eeij.gestion_tipo_id = ".$gestion." and eeij.prueba_tipo_id = ".$pruebaId." and eeij.fase_tipo_id = ".($fase+1)."
                ) as e on e.institucioneducativa_id = ie.id and e.prueba_tipo_id = ejd.prueba_tipo_id --and e.fase_tipo_id = ejd.fase_tipo_id --and e.posicion = ejd.posicion
                where (case ".$fase." when 1 then  lt.id = ".$codigoEntidad." when 2 then jg.circunscripcion_tipo_id = ".$codigoEntidad." when 3 then det.id = ".$codigoEntidad." else true end) and ejd.gestion_tipo_id = ".$gestion." and pt.id = ".$pruebaId." and ejd.fase_tipo_id = ".($fase)."
                order by dt.disciplina, pt.prueba, gtp.genero, nombre, paterno, materno

            ");

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }



    /**
     * get request
     * @param type $request, $departamento, $gestion, $fase, $nivel, $disciplina, $prueba, $genero 
     * return list of students
     */
    public function setEstudianteEntrenador($codigoEntidad, $gestion, $fase, $pruebaId) {
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                select distinct ei.id as estudiante_inscripcion_id, eij.gestion_tipo_id, eij.prueba_tipo_id, eij.fase_tipo_id, ie.id as institucioneducativa_id, eij.posicion
                from estudiante_inscripcion_juegos as eij 
                inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id 
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id 
                inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id 
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id 
                where eij.gestion_tipo_id = ".$gestion." and eij.prueba_tipo_id = ".$pruebaId." and eij.fase_tipo_id = ".($fase+1)." and ie.id = ".$codigoEntidad."
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }



    /**
     * get request
     * @param type $request, $departamento, $gestion, $fase, $nivel, $disciplina, $prueba, $genero 
     * return list of students
     */
    public function listaDelegadosDepartamentalesPorFaseGestion($codigoEntidad, $gestion, $fase) {

        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                select ejd.id, 3 as fase, ejd.carnet_identidad, ejd.nombre, ejd.paterno, ejd.materno, ct.comision, dt.disciplina, pt.prueba, gtp.genero as genero_prueba, ejd.foto, ejd.posicion
                from comision_juegos_datos as ejd
                inner join comision_tipo as ct on ct.id = ejd.comision_tipo_id
                inner join fase_tipo as ft on ft.id = ejd.fase_tipo_id
                inner join genero_tipo as gt on gt.id = ejd.genero_tipo
                inner join departamento_tipo as det on det.id = departamento_tipo
                inner join prueba_tipo as pt on pt.id = ejd.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id =  pt.disciplina_tipo_id
                inner join genero_tipo as gtp on gtp.id = pt.genero_tipo_id
                where det.id = ".$codigoEntidad." and ejd.gestion_tipo_id = ".$gestion." and ejd.fase_tipo_id = 4 and (ejd.prueba_tipo_id is null or ejd.prueba_tipo_id = 0)
                and ct.nivel_tipo_id = (case ft.esactivo_secundaria when 't' then 13 else 12 end)
                order by dt.disciplina, pt.prueba, gtp.genero, nombre, paterno, materno

            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }


    /**
     * get request
     * @param type $request, $departamento, $gestion, $fase, $nivel, $disciplina, $prueba, $genero 
     * return list of students
     */
    public function listaDelegadosNacionalesPorFaseGestion($codigoEntidad, $gestion, $fase) {

        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                select ejd.id, 3 as fase, ejd.carnet_identidad, ejd.nombre, ejd.paterno, ejd.materno, ct.comision, dt.disciplina, pt.prueba, gtp.genero as genero_prueba, ejd.foto, ejd.obs, ejd.celular, ejd.posicion
                from comision_juegos_datos as ejd
                inner join comision_tipo as ct on ct.id = ejd.comision_tipo_id
                inner join fase_tipo as ft on ft.id = ejd.fase_tipo_id
                inner join genero_tipo as gt on gt.id = ejd.genero_tipo
                inner join departamento_tipo as det on det.id = departamento_tipo
                inner join prueba_tipo as pt on pt.id = ejd.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id =  pt.disciplina_tipo_id
                inner join genero_tipo as gtp on gtp.id = pt.genero_tipo_id
                where det.id = ".$codigoEntidad." and ejd.gestion_tipo_id = ".$gestion." -- and ct.nivel_tipo_id in (13)
                and dt.nivel_tipo_id = (case ft.esactivo_secundaria when 't' then 13 else 12 end)
                order by dt.disciplina, pt.prueba, gtp.genero, nombre, paterno, materno
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function buscaEntidadFase($fase,$usuario) {

        //get db connexion
        $em = $this->getDoctrine()->getManager();

        if ($fase == 1){
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.id, lt.lugar as nombre from usuario as u
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$usuario." and rol_tipo_id = 10
                ");
        }
        if ($fase == 2){
            $queryEntidad = $em->getConnection()->prepare("
                    select ct.id, ct.circunscripcion as nombre from usuario as u
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join circunscripcion_tipo as ct on ct.id = ur.circunscripcion_tipo_id
                    where u.id = ".$usuario." and rol_tipo_id = 6
                ");
        }
        if ($fase == 3){
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.codigo as id, lt.lugar as nombre from usuario as u
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$usuario." and (rol_tipo_id = 7 or rol_tipo_id = 28)
                ");
        }        
        if ($fase == 4){
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.codigo as id, lt.lugar as nombre from usuario as u
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$usuario." and (rol_tipo_id = 8)
                ");
        }
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();

        return $objEntidad;
    }


    /**
     * get Form
     * @param type $entity
     * return form
     */
    private function createDelegacionEstudianteForm(ComisionJuegosDatos $entity)
    { 
        //$entity->setPruebaTipo();
        $form = $this->createForm(new ComisionJuegosDatosType(), $entity, array(
            'action' => $this->generateUrl('sie_juegos_comision_acompanante_lista_registro_save'),
            'method' => 'POST', 
        ));
        //->add('submit', 'submit',array('label' => 'Guardar'));    
        return $form;
        
    }

    /**
     * get Form
     * @param type $entity
     * return form
     */
    private function createDelegacionDepartamentalForm(ComisionJuegosDatos $entity)
    { 
        //$entity->setPruebaTipo();
        $form = $this->createForm(new ComisionDepartamentalJuegosDatosType(), $entity, array(
            'action' => $this->generateUrl('sie_juegos_comision_departamental_lista_registro_save'),
            'method' => 'POST', 
        ));
        //->add('submit', 'submit',array('label' => 'Guardar'));    
        return $form;
        
    }

    /**
     * get Form
     * @param type $entity
     * return form
     */
    private function createDelegacionNacionalForm(ComisionJuegosDatos $entity)
    { 
        //$entity->setPruebaTipo();
        $form = $this->createForm(new ComisionNacionalJuegosDatosType(), $entity, array(
            'action' => $this->generateUrl('sie_juegos_comision_nacional_form_save'),
            'method' => 'POST', 
        ));
        //->add('submit', 'submit',array('label' => 'Guardar'));    
        return $form;
        
    }

    public function listaAcompananteEliminaAction(Request $request) {
        if ($request->isMethod('POST')) {
            // Recupera datos del formulario            
            $inscripcion = $request->get('id');
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Envío de datos incorrecto, intente nuevamente'));  
            return $this->redirect($this->generateUrl('sie_juegos_comision_acompanante_lista_index'));
        }


        //$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Registro de delegados cerrado'));  
        //return $this->redirect($this->generateUrl('sie_juegos_comision_acompanante_lista_index'));

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $respuesta = array();
        try{
            $entityDatos = $em->getRepository('SieAppWebBundle:ComisionJuegosDatos')->findOneBy(array('id'=>$inscripcion));
            $nivel = $entityDatos->getPruebaTipo()->getDisciplinaTipo()->getNivelTipo()->getId();

            $fase = $entityDatos->getFaseTipo()->getId();   
            $query = $em->getConnection()->prepare("select * from fase_tipo where id = ".$fase);
            $query->execute();
            $faseTipoEntity = $query->fetchAll();

            if(!$faseTipoEntity[0]['esactivo_primaria'] and $nivel == 12){
                $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Primario concluyeron")); 
                return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
            }
            if(!$faseTipoEntity[0]['esactivo_secundaria'] and $nivel == 13){
                $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Secundaria concluyeron")); 
                return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
            }
            if ($entityDatos) {
                $em->remove($entityDatos);
                $em->flush();
                $em->getConnection()->commit();
                $respuesta = array('0'=>true);
                $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Registro eliminado de forma correcta'));  
            }
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('5', array('title' => 'Error', 'message' => 'Registro no eliminado, intente nuevamente'));  
            $respuesta = array();
        }
        $response = new JsonResponse();
        //return $response->setData(array('aregistro' => $respuesta));
        return $this->redirect($this->generateUrl('sie_juegos_comision_acompanante_lista_index'));
    }


    public function listaDepartamentalEliminaAction(Request $request) {
        if ($request->isMethod('POST')) {
            // Recupera datos del formulario            
            $inscripcion = $request->get('id');
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Envío de datos incorrecto, intente nuevamente'));  
            return $this->redirect($this->generateUrl('sie_juegos_comision_acompanante_lista_index'));
        }

        
        //$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Registro de delegados cerrado'));  
        //return $this->redirect($this->generateUrl('sie_juegos_comision_acompanante_lista_index'));

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $respuesta = array();
        try{
            $entityDatos = $em->getRepository('SieAppWebBundle:ComisionJuegosDatos')->findOneBy(array('id'=>$inscripcion));
            $comision = $entityDatos->getComisionTipoId();  

            $entityComision = $em->getRepository('SieAppWebBundle:ComisionTipo')->findOneBy(array('id'=>$comision));
            $nivel = $entityComision->getNivelTipoId();  

            $fase = $entityDatos->getFaseTipo()->getId();   
            $query = $em->getConnection()->prepare("select * from fase_tipo where id = ".$fase);
            $query->execute();
            $faseTipoEntity = $query->fetchAll();

            if(!$faseTipoEntity[0]['esactivo_primaria'] and $nivel == 12){
                $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Primario concluyeron")); 
                return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
            }
            if(!$faseTipoEntity[0]['esactivo_secundaria'] and $nivel == 13){
                $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Secundaria concluyeron")); 
                return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
            }
            if ($entityDatos) {
                $em->remove($entityDatos);
                $em->flush();
                $em->getConnection()->commit();
                $respuesta = array('0'=>true);
                $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Registro eliminado de forma correcta'));  
            }
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('5', array('title' => 'Error', 'message' => 'Registro no eliminado, intente nuevamente'));  
            $respuesta = array();
        }
        $response = new JsonResponse();
        //return $response->setData(array('aregistro' => $respuesta));
        return $this->redirect($this->generateUrl('sie_juegos_comision_departamental_lista_index'));
    }



    public function listaNacionalEliminaAction(Request $request) {
        if ($request->isMethod('POST')) {
            // Recupera datos del formulario            
            $inscripcion = $request->get('id');
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Envío de datos incorrecto, intente nuevamente'));  
            return $this->redirect($this->generateUrl('sie_juegos_comision_nacional_lista_index'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $respuesta = array();
        try{
            $entityDatos = $em->getRepository('SieAppWebBundle:ComisionJuegosDatos')->findOneBy(array('id'=>$inscripcion));
            $nivel = $entityDatos->getPruebaTipo()->getDisciplinaTipo()->getNivelTipo()->getId();
            
            $fase = $entityDatos->getFaseTipo()->getId();   
            $query = $em->getConnection()->prepare("select * from fase_tipo where id = ".$fase);
            $query->execute();
            $faseTipoEntity = $query->fetchAll();

            if(!$faseTipoEntity[0]['esactivo_primaria'] and $nivel == 12){
                $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Primario concluyeron")); 
                return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
            }
            if(!$faseTipoEntity[0]['esactivo_secundaria'] and $nivel == 13){
                $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Secundaria concluyeron")); 
                return $this->redirectToRoute('sie_juegos_inscripcion_fp_index');
            }
            if ($entityDatos) {
                $em->remove($entityDatos);
                $em->flush();
                $em->getConnection()->commit();
                $respuesta = array('0'=>true);
                $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Registro eliminado de forma correcta'));  
            }
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('5', array('title' => 'Error', 'message' => 'Registro no eliminado, intente nuevamente'));  
            $respuesta = array();
        }
        $response = new JsonResponse();
        //return $response->setData(array('aregistro' => $respuesta));
        return $this->redirect($this->generateUrl('sie_juegos_comision_nacional_lista_index'));
    }

/**
     * get request
     * @param type $request, $departamento, $gestion, $fase, $nivel, $disciplina, $prueba, $genero 
     * return list of students
     */
    public function verificaCupoDelegadosDepartamentalesPorEntidad($codigoEntidad, $gestion, $fase, $comisionId) {
        switch ($comisionId) {
            case 103:          
                $cupoTotal = 1;
                break;
            case 104:              
                $cupoTotal = 1;
                break;
            case 105:              
                $cupoTotal = 1;
                break;
            case 106:               
                $cupoTotal = 1;
                break;
            case 107:              
                $cupoTotal = 1;
                break;
            case 11:              
                $cupoTotal = 1;
                break;
            case 108:                
                $cupoTotal = 1;
                break;
            case 109:               
                $cupoTotal = 1;
                break;
            case 110:              
                $cupoTotal = 3;
                break;
            case 122:              
                $cupoTotal = 2;
                break;
            case 146:          
                $cupoTotal = 1;
                break;
            case 147:              
                $cupoTotal = 1;
                break;
            case 148:              
                $cupoTotal = 1;
                break;
            case 149:               
                $cupoTotal = 1;
                break;
            case 150:              
                $cupoTotal = 1;
                break;
            case 151:              
                $cupoTotal = 1;
                break;
            case 152:                
                $cupoTotal = 1;
                break;
            case 153:               
                $cupoTotal = 3;
                break;
            case 154:              
                $cupoTotal = 1;
                break;
            case 156:              
                $cupoTotal = 1;
                break;
            case 122:              
                $cupoTotal = 2;
                break;
            case 123:              
                $cupoTotal = 1;
                break;
            default:              
                $cupoTotal = 0;
                break;
        }
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $queryEntidad= $em->getConnection()->prepare("
                select ct.id as comision_id, ct.comision, cjd.paterno, cjd.materno, cjd.nombre from comision_juegos_datos as cjd
                inner join prueba_tipo as pt on pt.id = cjd.prueba_tipo_id
                inner join comision_tipo as ct on ct.id = cjd.comision_tipo_id
                inner join genero_tipo as gt on gt.id = pt.genero_tipo_id
                inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                where cjd.comision_tipo_id = ".$comisionId." and cjd.gestion_tipo_id = ".$gestion." and cjd.departamento_tipo = ".$codigoEntidad."
            ");            
        
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();

        $mensaje = "";
        if(count($objEntidad) >= $cupoTotal ){
            $mensaje = "No es posible incluir mas delegados en la comisión de ".$objEntidad[0]["comision"];
        }
        return $mensaje;
    }

    /**
     * get request
     * @param type $request, $departamento, $gestion, $fase, $nivel, $disciplina, $prueba, $genero 
     * return list of students
     */
    public function verificaCupoDelegadosPorEntidadPruebaPosicion($codigoEntidad, $gestion, $fase, $nivelId, $disciplinaId, $pruebaId, $generoId, $comisionId, $posicion) {
        switch ($disciplinaId) {
            case 3:
                if ($codigoEntidad == 9) {
                    $cupoMaxMaestro = 1;
                    $cupoMaxDelegado = 1;
                    $cupoMaxApoderado = 1;            
                    $cupoTotal = 2;
                } else {
                    $cupoMaxMaestro = 1;
                    $cupoMaxDelegado = 1;
                    $cupoMaxApoderado = 1;            
                    $cupoTotal = 2;
                }
                break;
            case 4:
                if ($codigoEntidad == 9) {
                    $cupoMaxMaestro = 1;
                    $cupoMaxDelegado = 1;
                    $cupoMaxApoderado = 1;             
                    $cupoTotal = 2;
                } else {
                    $cupoMaxMaestro = 1;
                    $cupoMaxDelegado = 1;
                    $cupoMaxApoderado = 1;             
                    $cupoTotal = 2;
                }
                break;
            case 5:
                if ($codigoEntidad == 9) {
                    $cupoMaxMaestro = 1;
                    $cupoMaxDelegado = 1;
                    $cupoMaxApoderado = 1;            
                    $cupoTotal = 2;
                } else {
                    $cupoMaxMaestro = 1;
                    $cupoMaxDelegado = 1;
                    $cupoMaxApoderado = 1;            
                    $cupoTotal = 2;
                }
                break;
            case 7:
                if ($codigoEntidad == 9) {
                    $cupoMaxMaestro = 1;
                    $cupoMaxDelegado = 1;
                    $cupoMaxApoderado = 1;             
                    $cupoTotal = 2;
                } else {
                    $cupoMaxMaestro = 1;
                    $cupoMaxDelegado = 1;
                    $cupoMaxApoderado = 1;              
                    $cupoTotal = 2;
                }
                break;
            default:
                $cupoMaxMaestro = 1;
                $cupoMaxDelegado = 1;
                $cupoMaxApoderado = 1;               
                $cupoTotal = 1;
                break;
        }

        

        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $queryEntidadPrueba = $em->getConnection()->prepare("
            select prueba_id, prueba, genero_id, genero
            , SUM(case comision_id when 12 then 1 else 0 end) as cantidad_maestro 
            , SUM(case comision_id when 13 then 1 else 0 end) as cantidad_apoderado 
            , SUM(case comision_id when 102 then 1 else 0 end) as cantidad_delegado
            , SUM(cantidad) as cantidad_total
            from (
            select pt.id as prueba_id, pt.prueba, gt.id as genero_id, gt.genero, ct.id as comision_id, ct.comision, count(ct.id) as cantidad from comision_juegos_datos as cjd
            inner join prueba_tipo as pt on pt.id = cjd.prueba_tipo_id
            inner join comision_tipo as ct on ct.id = cjd.comision_tipo_id
            inner join genero_tipo as gt on gt.id = pt.genero_tipo_id
            where cjd.prueba_tipo_id = ".$pruebaId." and cjd.gestion_tipo_id = ".$gestion." and cjd.departamento_tipo = ".$codigoEntidad." and cjd.posicion = ".$posicion."
            group by pt.id, pt.prueba, gt.id, gt.genero, ct.id, ct.comision
            ) as v
            group by prueba_id, prueba, genero_id, genero
        ");
        $queryEntidadPrueba->execute();
        $objEntidadPrueba = $queryEntidadPrueba->fetchAll();

        if ($posicion==1){
            $posicionDescripcion = "Primer Lugar";
        } else {
            $posicionDescripcion = "Segundo Lugar";
        }

        //print_r($objEntidadPrueba);
        $mensaje = "";
        if(count($objEntidadPrueba) > 0){
            if ($objEntidadPrueba[0]["cantidad_maestro"]>=$cupoMaxMaestro and $comisionId == 12){
                $mensaje = "No es posible incluir mas maestros(as) para la prueba de ".$objEntidadPrueba[0]["prueba"]." - ".$objEntidadPrueba[0]["genero"].", ya registro ".$objEntidadPrueba[0]["cantidad_maestro"]." maestros(as)";
            }   
            if ($objEntidadPrueba[0]["cantidad_delegado"]>=$cupoMaxDelegado and $comisionId == 102){
                $mensaje = "No es posible incluir mas delegados(as) para la prueba de ".$objEntidadPrueba[0]["prueba"]." - ".$objEntidadPrueba[0]["genero"].", ya registro ".$objEntidadPrueba[0]["cantidad_delegado"]." delegados";
            }         
            if ($objEntidadPrueba[0]["cantidad_apoderado"]>=$cupoMaxApoderado and $comisionId == 13){
                $mensaje = "No es posible incluir mas padres/madres de familia para la prueba de ".$objEntidadPrueba[0]["prueba"]." - ".$objEntidadPrueba[0]["genero"].", ya registro ".$objEntidadPrueba[0]["cantidad_apoderado"]." padres/madres de familia";
            }       
            if ($objEntidadPrueba[0]["cantidad_total"]>=$cupoTotal){
                $mensaje = "No es posible incluir mas acompañantes en la prueba de ".$objEntidadPrueba[0]["prueba"]." - ".$posicionDescripcion;
            } 
        } else {
            if (0>=$cupoMaxMaestro and $comisionId == 12){
                $mensaje = "No es posible incluir mas maestros(as) para la prueba seleccionada";
            }   
            if (0>=$cupoMaxDelegado and $comisionId == 102){
                $mensaje = "No es posible incluir mas delegados(as) para la prueba seleccionada";
            }         
            if (0>=$cupoMaxApoderado and $comisionId == 13){
                $mensaje = "No es posible incluir mas padres/madres de familia para la prueba seleccionada";
            }  
        }
        return "no es posible incluir acompañantes";
    }

    /**
     * get request
     * @param type $request, $departamento, $gestion, $fase, $nivel, $disciplina, $prueba, $genero 
     * return list of students
     */
    public function verificaCupoDelegadosEntidadPruebaPosicion($codigoEntidad, $gestion, $fase, $nivelId, $disciplinaId, $pruebaId, $generoId, $comisionId, $posicion) {
        switch ($disciplinaId) {
            case 3:
                $cupoMaxEntrenador = 1;
                $cupoMaxMaestro = 1;
                $cupoMaxDelegado = 1;
                $cupoMaxApoderado = 1;            
                $cupoTotal = 2;
                break;
            case 4:
                $cupoMaxEntrenador = 1;
                $cupoMaxMaestro = 1;
                $cupoMaxDelegado = 1;
                $cupoMaxApoderado = 1;            
                $cupoTotal = 2;
                break;
            case 5:
                $cupoMaxEntrenador = 1;
                $cupoMaxMaestro = 1;
                $cupoMaxDelegado = 1;
                $cupoMaxApoderado = 1;            
                $cupoTotal = 2;
                break;
            case 7:
                $cupoMaxEntrenador = 1;
                $cupoMaxMaestro = 1;
                $cupoMaxDelegado = 1;
                $cupoMaxApoderado = 1;            
                $cupoTotal = 2;
                break;
            case 14:
                $cupoMaxEntrenador = 1;
                $cupoMaxMaestro = 1;
                $cupoMaxDelegado = 1;
                $cupoMaxApoderado = 1;               
                $cupoTotal = 3;
                break;
            case 15:
                $cupoMaxEntrenador = 1;
                $cupoMaxMaestro = 1;
                $cupoMaxDelegado = 1;
                $cupoMaxApoderado = 1;                
                $cupoTotal = 3;
                break;
            case 16:
                $cupoMaxEntrenador = 1;
                $cupoMaxMaestro = 1;
                $cupoMaxDelegado = 1;
                $cupoMaxApoderado = 1;                 
                $cupoTotal = 3;
                break;
            case 17:
                $cupoMaxEntrenador = 1;
                $cupoMaxMaestro = 1;
                $cupoMaxDelegado = 1;
                $cupoMaxApoderado = 1;              
                $cupoTotal = 3;
                break;            
            default:
                $cupoMaxEntrenador = 1;
                $cupoMaxMaestro = 1;
                $cupoMaxDelegado = 1;
                $cupoMaxApoderado = 1;             
                $cupoTotal = 1;
                break;
        }
        //get db connexion


        $em = $this->getDoctrine()->getManager();
        $queryEntidadPrueba = $em->getConnection()->prepare("
            select disciplina_id as prueba_id, disciplina as prueba, 'Masculino/Femenino' as genero
            , SUM(case comision_id when 12 then 1 else 0 end) as cantidad_primaria_maestro 
            , SUM(case comision_id when 13 then 1 else 0 end) as cantidad_primaria_apoderado 
            , SUM(case comision_id when 102 then 1 else 0 end) as cantidad_primaria_delegado
            , SUM(case comision_id when 139 then 1 else 0 end) as cantidad_primaria_entrenador
            , SUM(case comision_id when 141 then 1 else 0 end) as cantidad_secundaria_maestro 
            , SUM(case comision_id when 142 then 1 else 0 end) as cantidad_secundaria_apoderado 
            , SUM(case comision_id when 143 then 1 else 0 end) as cantidad_secundaria_delegado
            , SUM(case comision_id when 140 then 1 else 0 end) as cantidad_secundaria_entrenador
            , SUM(case comision_id when 12 then 1 when 13 then 1 when 102 then 1 when 139 then 1 else 0 end) as cantidad_primaria
            , SUM(case comision_id when 140 then 1 when 141 then 1 when 142 then 1 when 143 then 1 else 0 end) as cantidad_secundaria
            , SUM(cantidad) as cantidad_total
            from (
            select dt.id as disciplina_id, dt.disciplina, pt.id as prueba_id, pt.prueba, gt.id as genero_id, gt.genero, ct.id as comision_id, ct.comision, count(ct.id) as cantidad 
            from comision_juegos_datos as cjd
            inner join prueba_tipo as pt on pt.id = cjd.prueba_tipo_id
            inner join comision_tipo as ct on ct.id = cjd.comision_tipo_id
            inner join genero_tipo as gt on gt.id = pt.genero_tipo_id
            inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
            where pt.id = ".$pruebaId." and cjd.gestion_tipo_id = ".$gestion." and cjd.departamento_tipo = ".$codigoEntidad." and cjd.posicion = ".$posicion." and cjd.fase_tipo_id = 4
            group by dt.id, dt.disciplina, pt.id, pt.prueba, gt.id, gt.genero, ct.id, ct.comision
            ) as v
            group by disciplina_id, disciplina
        ");    
        $queryEntidadPrueba->execute();
        $objEntidadPrueba = $queryEntidadPrueba->fetchAll();      
        
        $mensaje = "";
        if(count($objEntidadPrueba) > 0){
            if ($nivelId = 12){
                if ($objEntidadPrueba[0]["cantidad_primaria_maestro"]>=$cupoMaxMaestro and $comisionId == 12){
                    $mensaje = "No es posible incluir mas maestros(as) para la prueba de ".$objEntidadPrueba[0]["prueba"]." - ".$objEntidadPrueba[0]["genero"].", ya registro ".$objEntidadPrueba[0]["cantidad_primaria_maestro"]." maestros(as)";
                }   
                if ($objEntidadPrueba[0]["cantidad_primaria_delegado"]>=$cupoMaxDelegado and $comisionId == 102){
                    $mensaje = "No es posible incluir mas delegados(as) para la prueba de ".$objEntidadPrueba[0]["prueba"]." - ".$objEntidadPrueba[0]["genero"].", ya registro ".$objEntidadPrueba[0]["cantidad_primaria_delegado"]." delegados";
                }         
                if ($objEntidadPrueba[0]["cantidad_primaria_apoderado"]>=$cupoMaxApoderado and $comisionId == 13){
                    $mensaje = "No es posible incluir mas padres/madres de familia para la prueba de ".$objEntidadPrueba[0]["prueba"]." - ".$objEntidadPrueba[0]["genero"].", ya registro ".$objEntidadPrueba[0]["cantidad_primaria_apoderado"]." padres/madres de familia";
                }       
                if ($objEntidadPrueba[0]["cantidad_primaria_entrenador"]>=$cupoMaxEntrenador and $comisionId == 139){
                    $mensaje = "No es posible incluir mas entrenadores para la prueba de ".$objEntidadPrueba[0]["prueba"]." - ".$objEntidadPrueba[0]["genero"].", ya registro ".$objEntidadPrueba[0]["cantidad_primaria_entrenador"]." entrenadores";
                }   
                if ($objEntidadPrueba[0]["cantidad_primaria"]>=$cupoTotal){
                    $mensaje = "No es posible incluir mas acompañantes para la prueba de ".$objEntidadPrueba[0]["prueba"]." - ".$objEntidadPrueba[0]["genero"].", ya registro ".$objEntidadPrueba[0]["cantidad_primaria"]." acompañantes";
                }  
            }  else { 
                if ($objEntidadPrueba[0]["cantidad_secundaria_maestro"]>=$cupoMaxMaestro and $comisionId == 141){
                    $mensaje = "No es posible incluir mas maestros(as) para la prueba de ".$objEntidadPrueba[0]["prueba"]." - ".$objEntidadPrueba[0]["genero"].", ya registro ".$objEntidadPrueba[0]["cantidad__secundariamaestro"]." maestros(as)";
                }   
                if ($objEntidadPrueba[0]["cantidad_secundaria_delegado"]>=$cupoMaxDelegado and $comisionId == 143){
                    $mensaje = "No es posible incluir mas delegados(as) para la prueba de ".$objEntidadPrueba[0]["prueba"]." - ".$objEntidadPrueba[0]["genero"].", ya registro ".$objEntidadPrueba[0]["cantidad_secundaria_delegado"]." delegados";
                }         
                if ($objEntidadPrueba[0]["cantidad_secundaria_apoderado"]>=$cupoMaxApoderado and $comisionId == 142){
                    $mensaje = "No es posible incluir mas padres/madres de familia para la prueba de ".$objEntidadPrueba[0]["prueba"]." - ".$objEntidadPrueba[0]["genero"].", ya registro ".$objEntidadPrueba[0]["cantidad_secundaria_apoderado"]." padres/madres de familia";
                }       
                if ($objEntidadPrueba[0]["cantidad_secundaria_entrenador"]>=$cupoMaxEntrenador and $comisionId == 140){
                    $mensaje = "No es posible incluir mas entrenadores para la prueba de ".$objEntidadPrueba[0]["prueba"]." - ".$objEntidadPrueba[0]["genero"].", ya registro ".$objEntidadPrueba[0]["cantidad_secundaria_entrenador"]." entrenadores";
                }   
                if ($objEntidadPrueba[0]["cantidad_secundaria"]>=$cupoTotal){
                    $mensaje = "No es posible incluir mas acompañantes para la prueba de ".$objEntidadPrueba[0]["prueba"]." - ".$objEntidadPrueba[0]["genero"].", ya registro ".$objEntidadPrueba[0]["cantidad_secundaria"]." acompañantes";
                }  
            }   
        } 
        return $mensaje;
    }
    
    public function listaDeportistasClasificadosDescargaPdfAction($fase,$usuario) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $em = $this->getDoctrine()->getManager();

        $objEntidad = $this->buscaEntidadFase($fase,$usuario);

        $codigoEntidad = $objEntidad[0]['id'];

        $arch = $codigoEntidad.'_'.$gestionActual.'_JUEGOS_F'.$fase.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        if($fase == 1){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesJuegos_Participaciones_f1_v1.rptdesign&__format=pdf&coddis='.$codigoEntidad.'&codges='.$gestionActual));
        }
        if($fase == 2){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesJuegos_Participaciones_f2_v1.rptdesign&__format=pdf&codcir='.$codigoEntidad.'&codges='.$gestionActual));
        }
        if($fase == 3){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesJueogs_Participaciones_foto_v1.rptdesign&__format=pdf&depto='.$codigoEntidad.'&codges='.$gestionActual));
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function listaEntrenadorPrintAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $em = $this->getDoctrine()->getManager();

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        $fase = $request->get('fase');

        $objEntidad = $this->buscaEntidadFase($fase,$id_usuario);

        $codigoEntidad = $objEntidad[0]['id'];

        $arch = $codigoEntidad.'_'.$gestionActual.'_JUEGOS_F'.$fase.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        if($fase == 1){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesAcompañantesJuegos_f1_v1.rptdesign&__format=pdf&coddep='.$codigoEntidad.'&codges='.$gestionActual));
        }
        if($fase == 2){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesAcompañantesJuegos_f2_v1.rptdesign&__format=pdf&coddep='.$codigoEntidad.'&codges='.$gestionActual));
        }
        if($fase == 3){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesAcompañantesJuegos_f3_v1.rptdesign&__format=pdf&coddep='.$codigoEntidad.'&codges='.$gestionActual));
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function listaEntrenadorPrintXlsAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $em = $this->getDoctrine()->getManager();

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        $fase = $request->get('fase');

        $objEntidad = $this->buscaEntidadFase($fase,$id_usuario);

        $codigoEntidad = $objEntidad[0]['id'];

        $arch = $codigoEntidad.'_'.$gestionActual.'_JUEGOS_F'.$fase.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        if($fase == 1){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesAcompañantesJuegos_f1_v1.rptdesign&__format=xls&coddep='.$codigoEntidad.'&codges='.$gestionActual));
        }
        if($fase == 2){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesAcompañantesJuegos_f2_v1.rptdesign&__format=xls&coddep='.$codigoEntidad.'&codges='.$gestionActual));
        }
        if($fase == 3){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesAcompañantesJuegos_f3_v1.rptdesign&__format=xls&coddep='.$codigoEntidad.'&codges='.$gestionActual));
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function listaNacionalPrintAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $em = $this->getDoctrine()->getManager();

        $codigo = $request->get('id');

        $em = $this->getDoctrine()->getManager();
        $queryComision = $em->getConnection()->prepare("
                select ejd.carnet_identidad, ejd.nombre, ejd.paterno, ejd.materno, ct.id as comision_id, ct.comision, dt.disciplina, pt.prueba, gtp.genero as genero_prueba, ejd.foto, ejd.obs, det.id as departamento_id, det.departamento, ejd.posicion
                from comision_juegos_datos as ejd
                inner join comision_tipo as ct on ct.id = ejd.comision_tipo_id
                inner join genero_tipo as gt on gt.id = ejd.genero_tipo
                inner join departamento_tipo as det on det.id = departamento_tipo
                inner join prueba_tipo as pt on pt.id = ejd.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id =  pt.disciplina_tipo_id
                inner join genero_tipo as gtp on gtp.id = pt.genero_tipo_id
                where ejd.id = ".$codigo."
                order by dt.disciplina, pt.prueba, gtp.genero, nombre, paterno, materno
            ");
        $queryComision->execute();
        $objComision = $queryComision->fetchAll();

        if (!$objComision) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información del Delegado'));
            return $this->redirect($this->generateUrl('sie_juegos_comision_nacional_lista_index'));
        }

        $codigoEntidad = $objComision[0]['departamento_id'];
        $comision = $objComision[0]['comision_id'];

        $arch = $codigoEntidad.'_'.$gestionActual.'_JUEGOS_F3_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        if($comision == 10){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_crd_organizadorfotojuegos_primariaRUDE_foto_v2.rptdesign&__format=pdf&codigo='.$codigo.'&codges='.$gestionActual));
        }
        if($comision == 20 or $comision == 30){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_crd_delegadofotojuegos_primariaRUDE_foto_v2.rptdesign&__format=pdf&codigo='.$codigo.'&codges='.$gestionActual));
        }
        if($comision == 40 or $comision == 50 or $comision == 60 or $comision == 70){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_crd_delegadosinfotojuegos_primariaRUDE_foto_v2.rptdesign&__format=pdf&codigo='.$codigo.'&codges='.$gestionActual));
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function listaEstudianteAcompañantePrintAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $em = $this->getDoctrine()->getManager();

        $tipo = $request->get('tipo');        

        $arch = $gestionActual.'_JUEGOS_F3_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        if($tipo == 1){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_crd_estudiantejuegos_primariaRUDE_foto_v2.rptdesign&__format=pdf'));
        }
        if($tipo == 2){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_crd_delegadojuegos_primariaRUDE_foto_v2.rptdesign&__format=pdf'));
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function registroEntrenadorF1IndexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestion = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $fase = 1; // nombre de la fase actual

        $objEntidad = $this->buscaEntidadFase($fase,$id_usuario);
        if (!$objEntidad) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información del Distrito'));
            return $this->redirect($this->generateUrl('sie_juegos_homepage'));
        }

        $codigoEntidad = $objEntidad[0]['id'];

        $exist = true;

        $query = $em->getConnection()->prepare("
                    select distinct nt.id as nivelid, nt.nivel as nivel, dt.id as disciplinaid, dt.disciplina as disciplina, pt.id as pruebaid, pt.prueba as prueba, gt.id as generoid, gt.genero as genero 
                    from (select * from estudiante_inscripcion_juegos where fase_tipo_id = ".($fase+1)." and gestion_tipo_id = ".$gestion.") as eij
                        inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                        inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                        inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                        inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                        inner join lugar_tipo as lt1 on lt1.id = jg.lugar_tipo_id_localidad
                        inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                        inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                        inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                        inner join lugar_tipo as lt5 on lt5.id = lt4.lugar_tipo_id
                        inner join lugar_tipo as lt6 on lt6.id = jg.lugar_tipo_id_distrito
                    inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
                    inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                    inner join nivel_tipo as nt on nt.id = dt.nivel_tipo_id
                    inner join genero_tipo as gt on gt.id = pt.genero_tipo_id
                    where lt6.id = ".$codigoEntidad." --and nt.id = 13
                    order by nt.id, dt.id, pt.id, gt.id
                ");
        $query->execute();
        $entityNiveles = $query->fetchAll();
        if ($entityNiveles) {
            $aInfoDeportes = array();
            foreach ($entityNiveles as $uDeporte) {
                //get the literal data of unidad educativa
                $sinfoDeportes = serialize(array(
                    'deportesInfo' => array('nivel' => $uDeporte['nivel'], 'disciplina' => $uDeporte['disciplina'], 'prueba' => $uDeporte['prueba'], 'genero' => $uDeporte['genero']),
                    'deportesInfoId' => array('nivelId' => $uDeporte['nivelid'], 'disciplinaId' => $uDeporte['disciplinaid'], 'pruebaId' => $uDeporte['pruebaid'], 'generoId' => $uDeporte['generoid']),
                    'requestUser' => array('codigoEntidad' => $codigoEntidad, 'gestion' => $gestion, 'fase' => $fase)
                ));

                //send the values to the next steps
                $aInfoDeportes[$uDeporte['nivel']][$uDeporte['disciplina']][$uDeporte['prueba']][$uDeporte['genero']] = array('infoDeportes' => $sinfoDeportes);
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información de las Disciplinas y Pruebas'));
            return $this->redirect($this->generateUrl('sie_juegos_homepage'));
        }

        return $this->render($this->session->get('pathSystem') . ':Comision:entrenadorIndex.html.twig', array(
                    'infoEntidad' => $objEntidad,
                    'infoNiveles' => $aInfoDeportes,
                    'gestion' => $gestion,
                    'exist' => $exist
        ));
    }


    public function registroEntrenadorF2IndexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestion = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $fase = 2; // nombre de la fase anterior

        $objEntidad = $this->buscaEntidadFase($fase,$id_usuario);
        if (!$objEntidad) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información del Distrito'));
            return $this->redirect($this->generateUrl('sie_juegos_homepage'));
        }

        $codigoEntidad = $objEntidad[0]['id'];

        $exist = true;

        $query = $em->getConnection()->prepare("
            select distinct nt.id as nivelid, nt.nivel as nivel, dt.id as disciplinaid, dt.disciplina as disciplina, pt.id as pruebaid, pt.prueba as prueba, gt.id as generoid, gt.genero as genero 
            from (select * from estudiante_inscripcion_juegos where fase_tipo_id = ".($fase+1)." and gestion_tipo_id = ".$gestion.") as eij
                inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join lugar_tipo as lt1 on lt1.id = jg.lugar_tipo_id_localidad
                inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                inner join lugar_tipo as lt5 on lt5.id = lt4.lugar_tipo_id
                inner join lugar_tipo as lt6 on lt6.id = jg.lugar_tipo_id_distrito
            inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
            inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
            inner join nivel_tipo as nt on nt.id = dt.nivel_tipo_id
            inner join genero_tipo as gt on gt.id = pt.genero_tipo_id
            where jg.circunscripcion_tipo_id = ".$codigoEntidad." --and nt.id = 13
            order by nt.id, dt.id, pt.id, gt.id
        ");
        $query->execute();
        $entityNiveles = $query->fetchAll();
        if ($entityNiveles) {
            $aInfoDeportes = array();
            foreach ($entityNiveles as $uDeporte) {
                //get the literal data of unidad educativa
                $sinfoDeportes = serialize(array(
                    'deportesInfo' => array('nivel' => $uDeporte['nivel'], 'disciplina' => $uDeporte['disciplina'], 'prueba' => $uDeporte['prueba'], 'genero' => $uDeporte['genero']),
                    'deportesInfoId' => array('nivelId' => $uDeporte['nivelid'], 'disciplinaId' => $uDeporte['disciplinaid'], 'pruebaId' => $uDeporte['pruebaid'], 'generoId' => $uDeporte['generoid']),
                    'requestUser' => array('codigoEntidad' => $codigoEntidad, 'gestion' => $gestion, 'fase' => $fase)
                ));

                //send the values to the next steps
                $aInfoDeportes[$uDeporte['nivel']][$uDeporte['disciplina']][$uDeporte['prueba']][$uDeporte['genero']] = array('infoDeportes' => $sinfoDeportes);
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información de las Disciplinas y Pruebas'));
            return $this->redirect($this->generateUrl('sie_juegos_homepage'));
        }

        return $this->render($this->session->get('pathSystem') . ':Comision:entrenadorClasificacionIndex.html.twig', array(
                    'infoEntidad' => $objEntidad,
                    'infoNiveles' => $aInfoDeportes,
                    'gestion' => $gestion,
                    'exist' => $exist,
                    'fase' => $fase
        ));
    }

    public function registroEntrenadorF3IndexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestion = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $fase = 3; // nombre de la fase anterior

        $objEntidad = $this->buscaEntidadFase($fase,$id_usuario);
        if (!$objEntidad) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información del Distrito'));
            return $this->redirect($this->generateUrl('sie_juegos_homepage'));
        }

        $codigoEntidad = $objEntidad[0]['id'];

        $exist = true;

        $query = $em->getConnection()->prepare("
            select distinct nt.id as nivelid, nt.nivel as nivel, dt.id as disciplinaid, dt.disciplina as disciplina, pt.id as pruebaid, pt.prueba as prueba, gt.id as generoid, gt.genero as genero 
            from (select * from estudiante_inscripcion_juegos where fase_tipo_id = ".($fase+1)." and gestion_tipo_id = ".$gestion.") as eij
                inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join lugar_tipo as lt1 on lt1.id = jg.lugar_tipo_id_localidad
                inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                inner join lugar_tipo as lt5 on lt5.id = lt4.lugar_tipo_id
                inner join lugar_tipo as lt6 on lt6.id = jg.lugar_tipo_id_distrito
            inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
            inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
            inner join nivel_tipo as nt on nt.id = dt.nivel_tipo_id
            inner join genero_tipo as gt on gt.id = pt.genero_tipo_id
            where lt5.codigo = '".$codigoEntidad."' --and nt.id = 13
            order by nt.id, dt.id, pt.id, gt.id
        ");
        $query->execute();
        $entityNiveles = $query->fetchAll();
        if ($entityNiveles) {
            $aInfoDeportes = array();
            foreach ($entityNiveles as $uDeporte) {
                //get the literal data of unidad educativa
                $sinfoDeportes = serialize(array(
                    'deportesInfo' => array('nivel' => $uDeporte['nivel'], 'disciplina' => $uDeporte['disciplina'], 'prueba' => $uDeporte['prueba'], 'genero' => $uDeporte['genero']),
                    'deportesInfoId' => array('nivelId' => $uDeporte['nivelid'], 'disciplinaId' => $uDeporte['disciplinaid'], 'pruebaId' => $uDeporte['pruebaid'], 'generoId' => $uDeporte['generoid']),
                    'requestUser' => array('codigoEntidad' => $codigoEntidad, 'gestion' => $gestion, 'fase' => $fase)
                ));

                //send the values to the next steps
                $aInfoDeportes[$uDeporte['nivel']][$uDeporte['disciplina']][$uDeporte['prueba']][$uDeporte['genero']] = array('infoDeportes' => $sinfoDeportes);
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información de las Disciplinas y Pruebas'));
            return $this->redirect($this->generateUrl('sie_juegos_homepage'));
        }

        return $this->render($this->session->get('pathSystem') . ':Comision:entrenadorClasificacionIndex.html.twig', array(
                    'infoEntidad' => $objEntidad,
                    'infoNiveles' => $aInfoDeportes,
                    'gestion' => $gestion,
                    'exist' => $exist,
                    'fase' => $fase
        ));
    }

    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function listaEntrenadorAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        //get the info ue
        $infoDeporte = $request->get('infoDeportes');
        $ainfoDeporte = unserialize($infoDeporte);

        //get the values throght the infoUe
        $codigoEntidad = $ainfoDeporte['requestUser']['codigoEntidad'];
        $gestion = $ainfoDeporte['requestUser']['gestion'];
        $fase = $ainfoDeporte['requestUser']['fase'];
        //$iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivelId = $ainfoDeporte['deportesInfoId']['nivelId'];
        $disciplinaId = $ainfoDeporte['deportesInfoId']['disciplinaId'];
        $pruebaId = $ainfoDeporte['deportesInfoId']['pruebaId'];
        $generoId = $ainfoDeporte['deportesInfoId']['generoId'];
        $nivel = $ainfoDeporte['deportesInfo']['nivel'];
        $disciplina = $ainfoDeporte['deportesInfo']['disciplina'];
        $prueba = $ainfoDeporte['deportesInfo']['prueba'];
        $genero = $ainfoDeporte['deportesInfo']['genero'];
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        //die($codigoEntidad." - ".$gestion." - ".$fase." - ".$nivelId." - ".$disciplinaId." - ".$pruebaId." - ".$generoId);    
        $objDelegados = $this->listaDelegadosPorFaseNivelDisciplinaPruebaGenero($codigoEntidad, $gestion, $fase, $nivelId, $disciplinaId, $pruebaId, $generoId);

        $exist = true;
        $aData = array();
        //check if the data exist

        if ($objDelegados) {
            //$objDelegados = $objDelegados[0];
            $aData = serialize(array('codigoEntidad' => $codigoEntidad, 'nivel' => $nivelId, 'disciplina' => $disciplinaId, 'prueba' => $pruebaId, 'genero' => $generoId));
            $exist = true;
        } else {
            //$message = 'No existen estudiantes inscritos...';
            //$this->addFlash('warninsueall', $message);
            $aData = serialize(array('codigoEntidad' => $codigoEntidad, 'nivel' => $nivelId, 'disciplina' => $disciplinaId, 'prueba' => $pruebaId, 'genero' => $generoId));
            $objDelegados = array();
            $exist = true;
        }
        $entityDatos = new ComisionJuegosDatos();
        $form = $this->createEntrenadorEstudianteForm($entityDatos,$nivelId,$disciplinaId);

        return $this->render($this->session->get('pathSystem') . ':Comision:seeEntrenadores.html.twig', array(
                    'form' => $form->createView(),
                    'objDelegados' => $objDelegados,
                    'codigoEntidad' => $codigoEntidad,
                    'nivel' => $nivel,
                    'gestion' => $gestion,
                    'aData' => $aData,
                    'disciplina' => $disciplina,
                    'prueba' => $prueba,
                    'genero' => $genero,
                    'infoDeporte' => $infoDeporte,
                    'fase' => $fase,
                    'exist' => $exist
        ));
    }


    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function listaEntrenadorClasificacionAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        //get the info ue
        $infoDeporte = $request->get('infoDeportes');
        $ainfoDeporte = unserialize($infoDeporte);
        //dump($ainfoDeporte);die;

        //get the values throght the infoUe
        $codigoEntidad = $ainfoDeporte['requestUser']['codigoEntidad'];
        $gestion = $ainfoDeporte['requestUser']['gestion'];
        $fase = $ainfoDeporte['requestUser']['fase'];
        //$iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivelId = $ainfoDeporte['deportesInfoId']['nivelId'];
        $disciplinaId = $ainfoDeporte['deportesInfoId']['disciplinaId'];
        $pruebaId = $ainfoDeporte['deportesInfoId']['pruebaId'];
        $generoId = $ainfoDeporte['deportesInfoId']['generoId'];
        $nivel = $ainfoDeporte['deportesInfo']['nivel'];
        $disciplina = $ainfoDeporte['deportesInfo']['disciplina'];
        $prueba = $ainfoDeporte['deportesInfo']['prueba'];
        $genero = $ainfoDeporte['deportesInfo']['genero'];
        //get db connexion
        $em = $this->getDoctrine()->getManager();

        //die($codigoEntidad." - ".$gestion." - ".$fase." - ".$nivelId." - ".$disciplinaId." - ".$pruebaId." - ".$generoId);    
        $objDelegados = $this->listaDelegadosClasificadoPorFaseNivelDisciplinaPruebaGenero($codigoEntidad, $gestion, $fase, $nivelId, $disciplinaId, $pruebaId, $generoId);

        $objDelegadosRegistrados = $this->listaDelegadosClasificadosPorFaseNivelDisciplinaPruebaGenero($codigoEntidad, $gestion, ($fase+1), $nivelId, $disciplinaId, $pruebaId, $generoId);

        $exist = true;
        $aData = array();
        //check if the data exist

        if ($objDelegados) {
            //$objDelegados = $objDelegados[0];
            $aData = serialize(array('codigoEntidad' => $codigoEntidad, 'nivel' => $nivelId, 'disciplina' => $disciplinaId, 'prueba' => $pruebaId, 'genero' => $generoId));
            $exist = true;
        } else {
            //$message = 'No existen estudiantes inscritos...';
            //$this->addFlash('warninsueall', $message);
            $aData = serialize(array('codigoEntidad' => $codigoEntidad, 'nivel' => $nivelId, 'disciplina' => $disciplinaId, 'prueba' => $pruebaId, 'genero' => $generoId));
            $objDelegados = array();
            $exist = true;
        }
        $entityDatos = new ComisionJuegosDatos();
        $form = $this->createEntrenadorEstudianteForm($entityDatos,$nivelId,$disciplinaId);

        $id = $request->get('id');
        // dump($id);die;
        if(isset($id)){
            $entityDatos = $em->getRepository('SieAppWebBundle:ComisionJuegosDatos')->findOneBy(array('id'=>$id));
            $formFoto = $this->createCreateComisionDelegadoFotoForm($entityDatos);
            $formFoto = $formFoto->createView();

            return $this->render($this->session->get('pathSystem') . ':Comision:seeEntrenadoresClasificacion.html.twig', array(
                'form' => $form->createView(),
                'formFoto' => $formFoto,
                'delegado' => $id,
                'objDelegados' => $objDelegados,
                'objDelegadosRegistrados' => $objDelegadosRegistrados,
                'codigoEntidad' => $codigoEntidad,
                'nivel' => $nivel,
                'gestion' => $gestion,
                'aData' => $aData,
                'disciplina' => $disciplina,
                'prueba' => $prueba,
                'genero' => $genero,
                'infoDeporte' => $infoDeporte,
                'fase' => $fase,
                'exist' => $exist
            ));
        } else {
            return $this->render($this->session->get('pathSystem') . ':Comision:seeEntrenadoresClasificacion.html.twig', array(
                'form' => $form->createView(),
                'objDelegados' => $objDelegados,
                'objDelegadosRegistrados' => $objDelegadosRegistrados,
                'codigoEntidad' => $codigoEntidad,
                'nivel' => $nivel,
                'gestion' => $gestion,
                'aData' => $aData,
                'disciplina' => $disciplina,
                'prueba' => $prueba,
                'genero' => $genero,
                'infoDeporte' => $infoDeporte,
                'fase' => $fase,
                'exist' => $exist
            ));
        }
    }

    /**
     * get Form
     * @param type $entity
     * return form
     */
    private function createEntrenadorEstudianteForm(ComisionJuegosDatos $entity, $nivelId, $disciplinaId)
    {         
        //$entity->setPruebaTipo();
        $form = $this->createForm(new ComisionJuegosEntrenadorType(array('nivelId' => $nivelId, 'disciplinaId' => $disciplinaId)), $entity, array(
            'action' => $this->generateUrl('sie_juegos_comision_entrenador_lista_registro_save'),
            'method' => 'POST', 
        ));
        //->add('submit', 'submit',array('label' => 'Guardar'));    
        return $form;
        
    }

    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function listaEntrenadorSaveAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //if ($request->isMethod('POST')) {

        //$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Registro de delegados cerrado'));  
        //return $this->redirect($this->generateUrl('sie_juegos_comision_acompanante_lista_index'));
        
        /*
         * Recupera datos del formulario
         */
        //get the info ue
        $infoDeporte = $request->get('infoDeportes');
        $ainfoDeporte = unserialize($infoDeporte);
        //get the values throght the infoUe
        $codigoEntidad = $ainfoDeporte['requestUser']['codigoEntidad'];
        $gestion = $ainfoDeporte['requestUser']['gestion'];
        $fase = $ainfoDeporte['requestUser']['fase'];
        //$iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivelId = $ainfoDeporte['deportesInfoId']['nivelId'];
        $disciplinaId = $ainfoDeporte['deportesInfoId']['disciplinaId'];
        $pruebaId = $ainfoDeporte['deportesInfoId']['pruebaId'];
        $generoId = $ainfoDeporte['deportesInfoId']['generoId'];
        $nivel = $ainfoDeporte['deportesInfo']['nivel'];
        $disciplina = $ainfoDeporte['deportesInfo']['disciplina'];
        $prueba = $ainfoDeporte['deportesInfo']['prueba'];
        $genero = $ainfoDeporte['deportesInfo']['genero'];

        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare("select * from fase_tipo where id = ".($fase+1));
        $query->execute();
        $faseTipoEntity = $query->fetchAll();

        if(count($faseTipoEntity)<1){
            $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "No se cuenta habilitado la inscripcion de deportistas para la Primera Fase")); 
            return $this->redirectToRoute('sie_juegos_comision_entrenador_f'.$fase .'_lista_index');
        }

        if(!$faseTipoEntity[0]['esactivo_primaria'] and $nivelId == 12){
            $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Primario concluyeron")); 
            return $this->redirectToRoute('sie_juegos_comision_entrenador_f'.$fase .'_lista_index');
        }
        if(!$faseTipoEntity[0]['esactivo_secundaria'] and $nivelId == 13){
            $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Secundaria concluyeron")); 
            return $this->redirectToRoute('sie_juegos_comision_entrenador_f'.$fase .'_lista_index');
        }
                
        $em->getConnection()->beginTransaction();

        try {
            $entityComisionJuegosDatos = new ComisionJuegosDatos();                      
            $form = $this->createEntrenadorEstudianteForm($entityComisionJuegosDatos,$nivelId,$disciplinaId);
            $form->handleRequest($request);
            $comisionId = $entityComisionJuegosDatos->getComisionTipoId();
            $posicion = $entityComisionJuegosDatos->getPosicion();


            if ($fase == 1){
                $query = $em->getConnection()->prepare("
                    select ie.id as institucioneducativa_id, lt4.codigo as departamento_codigo from estudiante_inscripcion_juegos as eij 
                    inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                    inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
                    inner join lugar_tipo as ltl on ltl.id = jg.lugar_tipo_id_localidad
                    inner join lugar_tipo as lt1 on lt1.id = ltl.lugar_tipo_id
                    inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                    inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                    inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                    where lt.id = ".$codigoEntidad." and eij.prueba_tipo_id = ".$pruebaId." and eij.gestion_tipo_id = ".$gestion." and eij.fase_tipo_id = ".($fase+1)." and eij.posicion = ".$posicion."
                ");
            } 

            if ($fase == 2){
                $query = $em->getConnection()->prepare("
                    select ie.id as institucioneducativa_id, lt4.codigo as departamento_codigo from estudiante_inscripcion_juegos as eij 
                    inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                    inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
                    inner join lugar_tipo as ltl on ltl.id = jg.lugar_tipo_id_localidad
                    inner join lugar_tipo as lt1 on lt1.id = ltl.lugar_tipo_id
                    inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                    inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                    inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                    where jg.circunscripcion_tipo_id = ".$codigoEntidad." and eij.prueba_tipo_id = ".$pruebaId." and eij.gestion_tipo_id = ".$gestion." and eij.fase_tipo_id = ".($fase+1)." and eij.posicion = ".$posicion."
                ");
            }
            if ($fase == 3){
                $query = $em->getConnection()->prepare("
                    select ie.id as institucioneducativa_id, lt4.codigo as departamento_codigo from estudiante_inscripcion_juegos as eij 
                    inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                    inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                    inner join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                    inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                    inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                    inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                    where lt4.codigo = '".$codigoEntidad."' and eij.prueba_tipo_id = ".$pruebaId." and eij.gestion_tipo_id = ".$gestion." and eij.fase_tipo_id = ".($fase+1)." and eij.posicion = ".$posicion."
                ");
            }
            $query->execute();
            $estudianteJuegosEntity = $query->fetchAll();

            if (count($estudianteJuegosEntity) < 0){            
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'La prueba seleccionada no cuenta con un estudiante deportista registrado, intente nuevamente'));  
                return $this->redirect($this->generateUrl('sie_juegos_comision_entrenador_f'.$fase .'_lista_index'));
            } 

            $ue = $estudianteJuegosEntity[0]["institucioneducativa_id"];
            $depto = $estudianteJuegosEntity[0]["departamento_codigo"];
                      
            if ($fase == 3){
                $confirmaRegistro = $this->verificaCupoDelegadosEntidadPruebaPosicion($codigoEntidad, $gestion, ($fase+1), $nivelId, $disciplinaId, $pruebaId, $generoId, $comisionId, $posicion);
            } else {
                $confirmaRegistro = $this->verificaCupoEntrenadorPorEntidadPruebaPosicion($ue, $gestion, ($fase+1), $nivelId, $disciplinaId, $pruebaId, $generoId, $comisionId, $posicion, $id_usuario);
            }

            if ($confirmaRegistro != ""){            
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $confirmaRegistro));  
                return $this->redirect($this->generateUrl('sie_juegos_comision_entrenador_f'.$fase .'_lista_index'));
            } 

            if ($form->isValid()) {
                if (null != $form->get('foto')->getData()) {
                    $file = $entityComisionJuegosDatos->getFoto();

                    $filename = md5(uniqid()) . '.' . $file->guessExtension();
                    
                    $filesize = $file->getClientSize();
                    if ($filesize/1024 < 501) {
                        $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/fotos_juegos';
                        $file->move($adjuntoDir, $filename);

                        $entityComisionJuegosDatos->setFoto($filename);
                        
                    } else {
                        $this->get('session')->getFlashBag()->set(
                            'danger',
                            array(
                                'title' => 'Alerta!',
                                'message' => 'Fotografia muy grande, favor ingresar una fotografía que no exceda los 500KiB.'
                            )
                        );
                        return $this->redirect($this->generateUrl('sie_juegos_comision_entrenador_f'.$fase .'_lista_index'));  
                    } 
                }
            }                
            
            $entityPruebaTipo = $em->getRepository('SieAppWebBundle:PruebaTipo')->findOneBy(array('id' => $pruebaId));
            $entityFaseTipo = $em->getRepository('SieAppWebBundle:FaseTipo')->findOneBy(array('id' => ($fase+1)));
            $entityInstitucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $ue));

            $entityComisionJuegosDatos->setDepartamentoTipo($depto);
            $entityComisionJuegosDatos->setPruebaTipo($entityPruebaTipo);
            $entityComisionJuegosDatos->setFaseTipo($entityFaseTipo);
            $entityComisionJuegosDatos->setGestionTipoId($gestionActual); 
            $entityComisionJuegosDatos->setInstitucioneducativa($entityInstitucioneducativa);                                                          
            
            $em = $this->getDoctrine()->getManager(); 
            $em->persist($entityComisionJuegosDatos);
            $em->flush(); 

            $em->getConnection()->commit();
            $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Registro guardado de forma correcta'));  
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Problemas al guardar el registro, intente nuevamente'));  
        } 

        return $this->redirect($this->generateUrl('sie_juegos_comision_entrenador_f'.$fase .'_lista_index'));
    }


    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function listaEntrenadorClasificaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //if ($request->isMethod('POST')) {

        //$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Registro de delegados cerrado'));  
        //return $this->redirect($this->generateUrl('sie_juegos_comision_acompanante_lista_index'));
        
        /*
         * Recupera datos del formulario
         */
        //get the info ue

        $em = $this->getDoctrine()->getManager();

        if ($request->isMethod('POST')) {
            // Recupera datos del formulario            
            $inscripcion = $request->get('id');        
            $fase = $request->get('fase');
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Envío de datos incorrecto, intente nuevamente'));  
            return $this->redirect($this->generateUrl('sie_juegos_homepage'));
        }

        $query = $em->getConnection()->prepare("select * from fase_tipo where id = ".($fase+1));
        $query->execute();
        $faseTipoEntity = $query->fetchAll();

        if(count($faseTipoEntity)<1){
            $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "No se cuenta habilitado la inscripcion de deportistas para la Primera Fase")); 
            return $this->redirectToRoute('sie_juegos_comision_entrenador_f'.$fase .'_lista_index');
        }
                
        $em->getConnection()->beginTransaction();

        try {
            $entityComisionJuegosDatos = $em->getRepository('SieAppWebBundle:ComisionJuegosDatos')->findOneBy(array('id' => $inscripcion));
            $sie = $entityComisionJuegosDatos->getInstitucioneducativa()->getId();
            $gestion = $entityComisionJuegosDatos->getGestionTipoId();
            $fase = $entityComisionJuegosDatos->getFaseTipo()->getId();
            $pruebaId = $entityComisionJuegosDatos->getPruebaTipo()->getId();
            $deptoId = $entityComisionJuegosDatos->getDepartamentoTipo();
            $nivelId = $entityComisionJuegosDatos->getPruebaTipo()->getDisciplinaTipo()->getNivelTipo()->getId();
            $disciplinaId = $entityComisionJuegosDatos->getPruebaTipo()->getDisciplinaTipo()->getId();
            $generoId = $entityComisionJuegosDatos->getPruebaTipo()->getGeneroTipo()->getId();
            $comisionId = $entityComisionJuegosDatos->getComisionTipoId();
            $foto = $entityComisionJuegosDatos->getFoto();


            if(!$faseTipoEntity[0]['esactivo_primaria'] and $nivelId == 12){
                $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Primario concluyeron")); 
                return $this->redirectToRoute('sie_juegos_comision_entrenador_f'.$fase .'_lista_index');
            }
            if(!$faseTipoEntity[0]['esactivo_secundaria'] and $nivelId == 13){
                $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Secundaria concluyeron")); 
                return $this->redirectToRoute('sie_juegos_comision_entrenador_f'.$fase .'_lista_index');
            }

            $carnet = $entityComisionJuegosDatos->getCarnetIdentidad();
            $nombre = $entityComisionJuegosDatos->getNombre();
            $paterno = $entityComisionJuegosDatos->getPaterno();
            $materno = $entityComisionJuegosDatos->getMaterno();
            $celular = $entityComisionJuegosDatos->getCelular();
            $correo = $entityComisionJuegosDatos->getCorreo();
            $comisionId = $entityComisionJuegosDatos->getComisionTipoId();
            $generoPersonaId = $entityComisionJuegosDatos->getGeneroTipo();
            $esEntrenador = $entityComisionJuegosDatos->getEsentrenador();
            $avc = $entityComisionJuegosDatos->getAvc();


            $entityEstudiantesEntrenador = $this->setEstudianteEntrenador($sie, $gestion, $fase, $pruebaId);
            
            if (count($entityEstudiantesEntrenador) <= 0){            
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No se encontro los deportistas del entrenador, intente nuevamente'));  
                return $this->redirect($this->generateUrl('sie_juegos_comision_entrenador_f'.$fase .'_lista_index'));
            } 

            $comisionId = $entityComisionJuegosDatos->getComisionTipoId();

            $posicion = $entityEstudiantesEntrenador[0]['posicion'];

            $confirmaRegistro = $this->verificaCupoEntrenadorPorEntidadPruebaPosicion($sie, $gestion, ($fase+1), $nivelId, $disciplinaId, $pruebaId, $generoId, $comisionId, $posicion, $id_usuario);

            if ($confirmaRegistro != ""){            
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $confirmaRegistro));  
                return $this->redirect($this->generateUrl('sie_juegos_comision_entrenador_f'.$fase .'_lista_index'));
            } 
                       
            $entityPruebaTipo = $em->getRepository('SieAppWebBundle:PruebaTipo')->findOneBy(array('id' => $pruebaId));
            $entityFaseTipo = $em->getRepository('SieAppWebBundle:FaseTipo')->findOneBy(array('id' => ($fase+1)));
            $entityInstitucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $sie));

            $entityComisionJuegosDatosNew = new ComisionJuegosDatos();

            $entityComisionJuegosDatosNew->setDepartamentoTipo($deptoId);
            $entityComisionJuegosDatosNew->setPruebaTipo($entityPruebaTipo);
            $entityComisionJuegosDatosNew->setFaseTipo($entityFaseTipo);
            $entityComisionJuegosDatosNew->setGestionTipoId($gestionActual); 
            $entityComisionJuegosDatosNew->setInstitucioneducativa($entityInstitucioneducativa);  
            $entityComisionJuegosDatosNew->setFoto($foto);  
            $entityComisionJuegosDatosNew->setCarnetIdentidad($carnet);  
            $entityComisionJuegosDatosNew->setNombre($nombre);  
            $entityComisionJuegosDatosNew->setPaterno($paterno);  
            $entityComisionJuegosDatosNew->setMaterno($materno);  
            $entityComisionJuegosDatosNew->setCelular($celular);  
            $entityComisionJuegosDatosNew->setCorreo($correo);  
            $entityComisionJuegosDatosNew->setComisionTipoId($comisionId);  
            $entityComisionJuegosDatosNew->setEsentrenador($esEntrenador);  
            $entityComisionJuegosDatosNew->setAvc($avc);
            $entityComisionJuegosDatosNew->setGeneroTipo($generoPersonaId);  
            $entityComisionJuegosDatosNew->setPosicion($posicion);                                                           
            
            $em = $this->getDoctrine()->getManager(); 
            $em->persist($entityComisionJuegosDatosNew);
            $em->flush(); 

            $em->getConnection()->commit();
            $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Registro guardado de forma correcta'));  
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Problemas al guardar el registro, intente nuevamente'));  
        } 

        return $this->redirect($this->generateUrl('sie_juegos_comision_entrenador_f'.$fase .'_lista_index'));
    }

    /**
     * get request
     * @param type $request, $departamento, $gestion, $fase, $nivel, $disciplina, $prueba, $genero 
     * return list of students
     */
    public function verificaCupoEntrenadorPorEntidadPruebaPosicion($codigoEntidad, $gestion, $fase, $nivelId, $disciplinaId, $pruebaId, $generoId, $comisionId, $posicion, $id_usuario) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad= $em->getConnection()->prepare("
            select pt.id as prueba_id, pt.prueba, gt.id as genero_id, gt.genero, ct.id as comision_id, ct.comision from comision_juegos_datos as cjd
            inner join prueba_tipo as pt on pt.id = cjd.prueba_tipo_id
            inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
            inner join comision_tipo as ct on ct.id = cjd.comision_tipo_id
            inner join genero_tipo as gt on gt.id = pt.genero_tipo_id
            where cjd.prueba_tipo_id = ".$pruebaId." and cjd.gestion_tipo_id = ".$gestion." and posicion = ".$posicion." and cjd.institucioneducativa_id = ".$codigoEntidad."
            and fase_tipo_id = ".$fase." and cjd.comision_tipo_id = ".$comisionId."
        ");            
        
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();

        $entidadUsuario = $this->buscaEntidadFase(($fase-1),$id_usuario);

        $entidadUsuarioId =  $entidadUsuario[0]['id'];

        //print_r($objEntidadPrueba);
        $mensaje = "";

        $xCupo = 1;
        if ($fase == 2){
            if ($entidadUsuarioId == 31642){ // MAGDALENA/ BAURES/ HUACARAJE
                  $xCupo = 3;
              }
              if ($entidadUsuarioId == 31637){
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31639){
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31640  ){
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31552  ){
                  $xCupo = 3;
              }
              if ($entidadUsuarioId == 31553  ){
                  $xCupo = 3;
              }
              if ($entidadUsuarioId == 31613  ){
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31590  ){
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31612  ){
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31610  ){
                  $xCupo = 3;
              }
              if ($entidadUsuarioId == 31617  ){
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31554  ){
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31363  ){
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31564  ){
                  $xCupo = 2;
              }
  
              if ($entidadUsuarioId == 31458  ){
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31459  ){
                  $xCupo = 3;
              }
              if ($entidadUsuarioId == 79356  ){
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31508  ){
                  $xCupo = 3;
              }
              if ($entidadUsuarioId == 31622  ){ // SANTA CRUZ 1
                  $xCupo = 7;
              }
              if ($entidadUsuarioId == 31623  ){ // SANTA CRUZ 2
                  $xCupo = 6;
              }
              if ($entidadUsuarioId == 31624  ){ // SANTA CRUZ 3
                  $xCupo = 5;
              }
              if ($entidadUsuarioId == 79359  ){ // PLAN TRES MILL (SANTA CRUZ 4)
                  $xCupo = 6;
              }
              if ($entidadUsuarioId == 31504  ){
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31505  ){
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31530  ){
                  $xCupo = 2;
              }
  
              if ($entidadUsuarioId == 31455  ){ // LA PAZ 1
                  $xCupo = 3;
              }
              if ($entidadUsuarioId == 31456  ){ // LA PAZ 2
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31457  ){ // LA PAZ 3
                  $xCupo = 2;
              }
  
              if ($entidadUsuarioId == 31395  ){  // ACHACACHI
                  $xCupo = 5;
              }
              if ($entidadUsuarioId == 31426  ){  // CAJUATA
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31398  ){  //  CAQUIAVIRI
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31454  ){  // CARANAVI
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31448  ){  // CHARAZANI
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31443  ){  // COLQUENCHA
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31397  ){  // CORO CORO
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31403  ){  // PUERTO ACOSTA
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31451  ){  // SAN PEDRO DE CURACHUARA
                  $xCupo = 3;
              }
              if ($entidadUsuarioId == 31450  ){  // SAN PEDRO DE TIQUINA
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31411  ){  // TACACOMA
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 31422  ){  // YACO MALLA
                  $xCupo = 2;
              }
              if ($entidadUsuarioId == 99226  ){  // VILLA ABECIA - LAS CARRERAS
                  $xCupo = 2;
              }
          }

        if(count($objEntidad) >= $xCupo){
            $mensaje = "No es posible incluir mas entrenadores para la prueba seleccionada"; 
        }
        return $mensaje;
    }


    public function listaEntrenadorEliminaAction(Request $request) {
        if ($request->isMethod('POST')) {
            // Recupera datos del formulario            
            $inscripcion = $request->get('id');        
            $fase = $request->get('fase');
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Envío de datos incorrecto, intente nuevamente'));  
            return $this->redirect($this->generateUrl('sie_juegos_homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $respuesta = array();
        try{
            $entityDatos = $em->getRepository('SieAppWebBundle:ComisionJuegosDatos')->findOneBy(array('id'=>$inscripcion));
            $nivel = $entityDatos->getPruebaTipo()->getDisciplinaTipo()->getNivelTipo()->getId();
            $fase = $entityDatos->getFaseTipo()->getId();            
            if ($entityDatos) {
                $em->remove($entityDatos);
                $em->flush();
                $em->getConnection()->commit();
                $respuesta = array('0'=>true);
                $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Registro eliminado de forma correcta'));  
            }
            $response = new JsonResponse();
            return $this->redirect($this->generateUrl('sie_juegos_comision_entrenador_f'.($fase-1).'_lista_index'));
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('5', array('title' => 'Error', 'message' => 'Registro no eliminado, intente nuevamente'));  
            $respuesta = array();
            $response = new JsonResponse();
            return $this->redirect($this->generateUrl('sie_juegos_homepage'));
        }
    }
}
