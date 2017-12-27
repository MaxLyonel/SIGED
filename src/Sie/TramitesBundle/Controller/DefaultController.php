<?php

namespace Sie\TramitesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;

use Sie\TramitesBundle\Controller\ReporteController as reporteTramiteController;

class DefaultController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Controlador que muestra la información del usuario
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function perfilAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestion = $request->get('gestion');
        if($gestion==""){
            $gestion = $gestionActual->format('Y');
        }

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        //get the roles info
        $aRoles = $this->getUserRoles($id_usuario);
        $this->session->set('roluser', $aRoles);
        
        $sistemaTipoId = array(10); // ID DEL SISTEMA TRAMITE


        $em = $this->getDoctrine()->getManager();

        $sesion->set('aMenu', null);
        //****************
        //****SE GENERAN LOS MENUS PARA EL SIGEN EN BASE AL ID DEL ROL DEL USUARIO
        $query = $em->getConnection()->prepare('SELECT get_objeto_menu_usuario (:usuario_id::INT)');
        $query->bindValue(':usuario_id', $id_usuario);
        //$query->bindValue(':sistema_tipo_id', '{3,10}');
        $query->execute();
        $aMenuUser = $query->fetchAll();      


        if (sizeof($aMenuUser) > 0) {            
                foreach ($aMenuUser as $m) {
                    $menu = $m['get_objeto_menu_usuario'];
                    $menu = str_replace(array('(', ')', '"'), '', $menu);
                    $element = explode(',', $menu);

                    $aBuildMenu[] = array(
                        'sistema_tipo_id'=>$element[0],
                        'sistema'=>$element[1],
                        'objeto_tipo_id' => $element[2],
                        'objeto_tipo_icono'=>$element[3],
                        'icono' => $element[4],
                        'menu_tipo_id' => $element[5],
                        'nombre' => $element[6],
                        'menu_tipo_icono'=>$element[7],
                        'ruta' => $element[8],
                        'obs' => $element[9],
                        'menu_objeto_id' => $element[10],
                        'menu_objeto_esactivo'=>$element[11],
                        'permiso_id' => $element[12],
                        'permiso' => $element[13],
                        '_create' => $element[14],
                        '_read' => $element[15],
                        '_delete' => $element[16],
                        '_update' => $element[17],
                        'rol_permiso_id' => $element[18],
                        'rol_tipo_id' => $element[19],
                        'rol' => $element[20],
                        'objeto_tipo_activo' => $element[21],
                    );
                }
            $i = 0;
            $limit = count($aBuildMenu);
            $optionMenu = array();
            while ($i < $limit) {
                $optionMenu[$aBuildMenu[$i]['objeto_tipo_icono']][] = array('label' => $aBuildMenu[$i]['nombre'], 'status' => $aBuildMenu[$i]['menu_objeto_esactivo'], 'ruta' => $aBuildMenu[$i]['ruta'],'icono'=>$aBuildMenu[$i]['menu_tipo_icono']);
                $i++;
            }
            //set some values fot the view template
            //$sesion->set('aMenuOption', $aBuildMenu);
            $sesion->set('aMenu', $optionMenu);
        }        
        //****FIN SE GENERAN LOS MENUS PARA EL SIGED EN BASE AL ID DEL ROL DEL USUARIO
        //****************

        $usuarioEntity = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $id_usuario));
        $personaNombre = $usuarioEntity->getPersona()->getNombre()." ".$usuarioEntity->getPersona()->getPaterno()." ".$usuarioEntity->getPersona()->getMaterno();
        $personaCedula = $usuarioEntity->getPersona()->getCarnet();
        $personaCelular = $usuarioEntity->getPersona()->getCelular();
        $personaCorreo = $usuarioEntity->getPersona()->getCorreo();
        $personaFechaNacimiento = $usuarioEntity->getPersona()->getFechaNacimiento();
        $usuarioNombre = $usuarioEntity->getUsername();
        $usuarioRegistro = $usuarioEntity->getFechaRegistro();

        $reporteTramiteController = new reporteTramiteController();
        $reporteTramiteController->setContainer($this->container);
        //$reporteCertificadoTecnicoAlternativaNacional = $reporteTramiteController->certificadoTecnicoAlternativaNacional($gestionActual->format('Y'));
        $reporteCertificadoTecnicoAlternativaNacional = $reporteTramiteController->certificadoTecnicoAlternativaNacional($gestion);

        $entityGestion = $this->getGestiones(2016);


        $datosUsuario = array('personaNombre'=>$personaNombre,'personaCedula'=>$personaCedula,'personaFechaNacimiento'=>$personaFechaNacimiento,'usuarioNombre'=>$usuarioNombre,'usuarioRegistro'=>$usuarioRegistro,'personaCorreo'=>$personaCorreo,'personaCelular'=>$personaCelular);
        return $this->render($this->session->get('pathSystem') . ':Default:perfil.html.twig', array(
            'gestiones' => $entityGestion,
            'gestion' => $gestion,
            'titulo' => 'Perfil',
            'subtitulo' => 'Usuario',
            'datosUsuario' => $datosUsuario,
            'reporte' => $reporteCertificadoTecnicoAlternativaNacional,
            ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Funcion que muestra los roles del usuario
    // PARAMETROS: gestionId (gestion cuando se inicio el sistema)
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    function getGestiones($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:GestionTipo');
        $query = $entity->createQueryBuilder('gt')
                ->where('gt.id >= :id')
                ->setParameter('id', $id)
                ->orderBy('gt.id','desc')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $exc) {
            return array();
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Funcion que muestra las gestiones 
    // PARAMETROS: usuarioId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    function getUserRoles($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Usuario');
        $query = $entity->createQueryBuilder('u')
                ->select('rt.id, rt.rol, lt.id as rollugarid')
                ->leftJoin('SieAppWebBundle:UsuarioRol', 'ur', 'WITH', 'u.id=ur.usuario')
                ->leftJoin('SieAppWebBundle:RolTipo', 'rt', 'WITH', 'ur.rolTipo=rt.id')
                ->innerJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'ur.lugarTipo=lt.id')
                ->where('u.id = :id')
                ->andwhere('ur.esactivo = true')
                ->setParameter('id', $id)
                ->getQuery();
        //print_r($query);die;
        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $exc) {
            //echo $exc->getTraceAsString();
            return array();
        }
    }
    

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Funcion que valida si cuenta con un rol determina
    // PARAMETROS: usuarioId, rolId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    function isRolUsuario($usuarioId,$rolId) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:UsuarioRol');
        $query = $entity->createQueryBuilder('ur')
                ->where('ur.rolTipo in (:rolId)')
                ->andWhere('ur.usuario = :usuarioId')
                ->andwhere('ur.esactivo = true')
                ->setParameter('rolId', $rolId)
                ->setParameter('usuarioId', $usuarioId)
                ->getQuery();
        try {
            $query->getResult();
            if(count($query->getResult())>0){
                return true;
            } else {
                return false;
            }
        } catch (\Doctrine\ORM\NoResultException $exc) {
            return false;
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Controlador que muestra la vista de acuerdo al rol de usuario logueado
    // PARAMETROS: POR POST, roluser
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        $em = $this->getDoctrine()->getManager();
        /*
         * se obtiene datos del mensaje que sera mostrado al iniciar sessión
         */
        $query = $em->getConnection()->prepare('Select max(id),mensaje,titulo,fecha_ini, fecha_fin, estado, tipo_user, mensaje_d from mensaje_tramite GROUP BY id,mensaje,titulo,fecha_ini, fecha_fin, estado, tipo_user, mensaje_d');
        $query->execute();
        $d = $query->fetchAll();
        $this->session->set('mensaje_tramite', $d); //se envia datos en variable de session
//        print_r($request->getMethod());die;
        if ($request->getMethod() == 'POST') {
            /*
             * 
             */
            if ($request->get('form')) {
                $post = $request->get('form');
                $aRoles = $post['roluser'];
                $rol[0] = array('id' => $aRoles);
                $this->session->set('roluser', $rol);
                switch ($aRoles) {
                    case '7':
                        return $this->render('SieTramitesBundle:Tramite:BusquedaTramiteCertificado.html.twig', array(
                                    'form' => $this->creaFormularioBuscadorSeguimiento('sie_tramite_seguimiento_busqueda_detalle', '', '', 1)->createView()
                                    , 'titulo' => 'Técnico SIE Departamental'
                                    , 'subtitulo' => 'Certificación Técnica'
                        ));

                        break;
                    case '8':
                        return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                                    'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 13)->createView()
                                    , 'titulo' => 'Técnico Nacional'
                                    , 'subtitulo' => 'Certificación Técnica'
                        ));

                        break;
                    case '10':
                        return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                                    'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 13)->createView()
                                    , 'titulo' => 'Técnico SIE Distrital'
                                    , 'subtitulo' => 'Certificación Técnica'
                        ));

                        break;

                    case '13':
                        return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                                    'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 13)->createView()
                                    , 'titulo' => 'Recepción Distrito'
                                    , 'subtitulo' => 'Certificación Técnica'
                        ));

                        break;

                    case '14':
                        return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                                    'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 14)->createView()
                                    , 'titulo' => 'Recepción Departamento'
                                    , 'subtitulo' => 'Certificación Técnica'
                        ));
                        break;

                    case '15':
                        return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                                    'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 15)->createView()
                                    , 'titulo' => 'Autorización de Trámites'
                                    , 'subtitulo' => 'Certificación Técnica'
                        ));
                        break;

                    case '16':
                        return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                                    'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 16)->createView()
                                    , 'titulo' => 'Impresión Departamento'
                                    , 'subtitulo' => 'Certificación Técnica'
                        ));
                        break;

                    case '40':
                        return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                                    'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 16)->createView()
                                    , 'titulo' => 'Impresión Departamento'
                                    , 'subtitulo' => 'Certificación Técnica'
                        ));
                        break;

                    case '17':
                        return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                                    'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 17)->createView()
                                    , 'titulo' => 'Legalización de Trámites'
                                    , 'subtitulo' => 'Certificación Técnica'
                        ));
                        break;
                }
                return $this->render('SieTramitesBundle:Default:index.html.twig');
            }
        } else {

            $aRoles = $this->session->get('roluser');
            if (is_array($aRoles)) {
                $aRoles = $aRoles[0]['id'];
            }
            $rol[0] = array('id' => $aRoles);
            $this->session->set('roluser', $rol);
            switch ($aRoles) {
                case '7':
                    return $this->render('SieTramitesBundle:Tramite:BusquedaTramiteCertificado.html.twig', array(
                                'form' => $this->creaFormularioBuscadorSeguimiento('sie_tramite_seguimiento_busqueda_detalle', '', '', 1)->createView()
                                , 'titulo' => 'Técnico SIE Departamental'
                                , 'subtitulo' => 'Certificación Técnica'
                    ));

                    break;
                case '8':
                    return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                                'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 13)->createView()
                                , 'titulo' => 'Técnico Nacional'
                                , 'subtitulo' => 'Certificación Técnica'
                    ));

                    break;
                case '10':
                    return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                                'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 13)->createView()
                                , 'titulo' => 'Técnico SIE Distrital'
                                , 'subtitulo' => 'Certificación Técnica'
                    ));

                    break;

                case '13':
                    return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                                'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 13)->createView()
                                , 'titulo' => 'Recepción Distrito'
                                , 'subtitulo' => 'Certificación Técnica'
                    ));

                    break;

                case '14':
                    return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                                'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 14)->createView()
                                , 'titulo' => 'Recepción Departamento'
                                , 'subtitulo' => 'Certificación Técnica'
                    ));
                    break;

                case '15':
                    return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                                'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 15)->createView()
                                , 'titulo' => 'Autorización de Trámites'
                                , 'subtitulo' => 'Certificación Técnica'
                    ));
                    break;

                case '16':
                    return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                                'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 14)->createView()
                                , 'titulo' => 'Entrega Departamento'
                                , 'subtitulo' => 'Certificación Técnica'
                    ));
                    break;

                case '17':
                    return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                                'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 17)->createView()
                                , 'titulo' => 'Legalización de Trámites'
                                , 'subtitulo' => 'Certificación Técnica'
                    ));
                    break;
            }
            return $this->render('SieTramitesBundle:Default:index.html.twig');
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Controlador que crea los formularios deacuerdo deacuerdo al identificador enviado
    // PARAMETROS: identificador =1(gestiones anteriores), identificador =0(formularios de listado), 
    // identificador <> 0 o 1 para buscador de formularios 
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************

    private function creaFormularioBuscador($routing, $value1, $value2, $value3, $valor4, $identificador) {
        $especialidad = array();
        $nivel = array();
        /*
         * se creara formularios de acuerdo al indicador
         * identificador = 0 (formulario para listados)
         * identificador = 1 (formulario para gestiones anteriores)
         * identificador > 1 (formulario de busquedas)
         */
        if ($identificador == 0) {
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl($routing))
                    ->add('sies', 'text', array('label' => 'SIE', 'attr' => array('placeholder' => 'C.E.A', 'required' => true, 'value' => $value1, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                    ->add('gestiones', 'entity', array('empty_value' => 'Seleccione Gestión', 'data' => $value2, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'limpiar_dato_sie()'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                        'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('gt')
                            ->where('gt.id > 2015')
                            ->orderBy('gt.id', 'DESC');
                },
                    ))
                    ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                    ->add('lista', 'choice', array('label' => 'Lista', 'empty_value' => 'Seleccionar',
                        'choices' => array('16' => 'Recepción Departamento'
                            , '17' => 'Autorización'
                            , '18' => 'Impresión'
                            , '19' => 'Certificado Asigando'
                            , '21' => 'Entrega Distrito'
                            , '15' => 'Trámite Observado'
                        ),
                        'attr' => array('onchange' => 'buscarBoton(this.value, 0)')))
                    ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                    ->add('search', 'submit', array('label' => ' Buscar', 'attr' => array('class' => 'btn btn-blue')))
                    ->getForm();
        } else {
            if ($identificador == 1) {
                $especialidad = Array();
                $form = '';
                $em = $this->getDoctrine()->getManager();
                $form = $this->createFormBuilder()
                        ->setAction($this->generateUrl($routing))
                        ->add('rudeal', 'text', array('label' => 'RUDEAL', 'attr' => array('placeholder' => 'RUDEAL', 'required' => true, 'onblur' => 'verificarEstudiante(this.value)', 'class' => 'form-control', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('nombre_student', 'text', array('label' => 'nombre', 'attr' => array('placeholder' => 'Nombre Estudiante', 'disabled' => 'disabled', 'class' => 'form-control', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('sies', 'text', array('label' => 'SIE', 'attr' => array('placeholder' => 'Código SIE', 'onkeyup' => 'llenar_ue(this.value)', 'required' => true, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('ue', 'text', array('label' => 'C.E.A.', 'attr' => array('placeholder' => 'C.E.A', 'required' => true, 'value' => $value1, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('gestiones', 'entity', array('empty_value' => 'Seleccione Gestión', 'data' => $value2, 'attr' => array('class' => 'form-control', 'required' => true), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                            'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->where('gt.id >= 1900 and gt.id <= 2013')
                                ->orderBy('gt.id', 'DESC');
                    },
                        ))
                        ->add('grado', 'choice', array('empty_value' => 'Seleccione Grado', 'attr' => array('class' => 'form-control', 'required' => true),
                            'choices' => array('1' => 'Técnico Básico', '2' => 'Técnico Auxiliar'),
                        ))
                        ->add('gestiones', 'entity', array('empty_value' => 'Seleccione Gestión', 'data' => $value2, 'attr' => array('class' => 'form-control', 'required' => true), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                            'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->where('gt.id >= 1900 and gt.id <= 2012')
                                ->orderBy('gt.id', 'DESC');
                    },
                        ))
                        ->add('nivel', 'entity', array('empty_value' => 'Seleccione Area', 'attr' => array('class' => 'form-control', 'onchange' => 'llenarEspecialidad(this.value)', 'required' => true), 'class' => 'Sie\AppWebBundle\Entity\SuperiorFacultadAreaTipo', 'property' => 'facultad_area',
                            'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('nv')
                                ->where('nv.id >= 16')
                                ->andwhere('nv.id <= 23')
                                ->orderBy('nv.id', 'ASC');
                    },
                        ))
                        ->add('especialidad', 'choice', array('label' => 'especialidad', 'choices' => $especialidad, 'attr' => array('class' => 'form-control', 'onchange' => 'listarNivel(this.value)', 'required' => true)))
                        ->add('certificado', 'text', array('label' => 'Nro. Certificado', 'attr' => array('placeholder' => 'Nro. Certificado', 'required' => true, 'class' => 'form-control', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('registrar', 'submit', array('label' => ' Registrar', 'attr' => array('class' => 'btn btn-blue')))
                        ->getForm();
            } else {
                $form = '';
                $form = $this->createFormBuilder()
                        ->setAction($this->generateUrl($routing))
                        ->add('gestiones', 'entity', array('empty_value' => 'Seleccione Gestión', 'data' => $value2, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'limpiar_dato_sie()'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                            'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->where('gt.id > 2015')
                                ->orderBy('gt.id', 'DESC');
                    },
                        ))
                        ->add('sies', 'text', array('label' => 'SIE', 'attr' => array('placeholder' => 'Código C.E.A', 'required' => true, 'value' => $value1, 'class' => 'form-control', 'onchange' => 'listar_especialidad(this.value)', 'onkeypress' => 'return solonumeros(event)', 'pattern' => '[0-9]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('especialidad', 'choice', array('label' => 'especialidad', 'empty_value' => 'Seleccione Especialidad', 'choices' => $especialidad, 'attr' => array('class' => 'form-control', 'onchange' => 'listarNivel(this.value)', 'required' => true)))
                        ->add('nivel', 'choice', array('label' => 'nivel', 'choices' => $nivel, 'attr' => array('class' => 'form-control', 'empty_value' => 'Seleccione Nivel', 'required' => true, 'onchange' => 'buscarBoton(this.value,1)')))
                        ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                        ->add('search', 'submit', array('label' => ' Buscar', 'attr' => array('class' => 'btn btn-blue')))
                        ->getForm();
            }
        }
        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Controlador que lista las especialidades deacuerdo a un determinado centro de educacion alternativa
    // PARAMETROS: por POST sie_listar y gestion_listar
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************

    public function listarEspecialidadesAction() {
        try {
            $em = $this->getDoctrine()->getManager();
            $queryEntidad = $em->getConnection()->prepare(
                    'SELECT distinct ciclo, especialidad_id
                            FROM vm_estudiantes_tecnica_alternativa
                            WHERE institucioneducativa_id = ' . $_POST['sie_listar'] . ' AND
                            gestion_tipo_id =' . $_POST['gestion_listar'] . ' ORDER BY especialidad_id ASC');
            $queryEntidad->execute();
            $especialidad = $queryEntidad->fetchAll();
            $response = new JsonResponse();
            return $response->setData(array('listaespecialidades' => $especialidad));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Controlador que lista los niveles deacuerdo a una determinada especialidad
    // PARAMETROS: por POST sie_listar, gestion_listar y especialidad
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function listarNivelAction() {

        try {
            $em = $this->getDoctrine()->getManager();
            $queryEntidad = $em->getConnection()->prepare(
                    'SELECT distinct(grado), grado_id FROM vm_estudiantes_tecnica_alternativa WHERE 
                            institucioneducativa_id = ' . $_POST['sie_listar'] . ' 
                            AND gestion_tipo_id = ' . $_POST['gestion_listar'] . ' AND especialidad_id = ' . $_POST['especialidad'] . ' ORDER BY grado_id ASC');

            $queryEntidad->execute();
            $nivel_select = $queryEntidad->fetchAll();
            $response = new JsonResponse();
            return $response->setData(array('listanivel' => $nivel_select));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Controlador que verifica si la especialidad esta acreditada por el rue
    // PARAMETROS: por POST sie
    // RESULTADO: retornado por json
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function verificarRueAction(Request $request) {
        $sie = $_POST['sie'];
        try {
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare('
            select institucioneducativa_id, b.institucioneducativa, cod_org_curr_id, b.dependencia_tipo_id, desc_departamento
            , desc_provincia, desc_seccion, desc_canton, desc_localidad, tipo_area, distrito, dependencia,
             tb, ta, tm
            from vw_alternativa_rue a 
            inner join (
                    select *
                    from institucioneducativa 
                    where institucioneducativa_acreditacion_tipo_id=1 ) b on a.cod_ue_id=b.id 
                            inner join dependencia_tipo d on a.cod_dependencia_id=d.id
                            inner join orgcurricular_tipo e on e.id=a.cod_org_curr_id
                            inner join convenio_tipo f on f.id=a.cod_convenio_id 
                            inner join (
                                        select institucioneducativa_id
                                        ,sum(case when nivel_tipo_id = 203 then 1 end) as tb
                                        ,sum(case when nivel_tipo_id = 204 then 2 end) as ta
                                        ,sum(case when nivel_tipo_id = 205 then 3 end) as tm
                                        from institucioneducativa_nivel_autorizado
                                        group by institucioneducativa_id
                                        ) c on a.cod_ue_id=c. institucioneducativa_id 

                    WHERE b.orgcurricular_tipo_id = 2 and b.id = :sie::int
                ');
            $query->bindValue(':sie', $sie);
            $query->execute();
            $datos = $query->fetchAll();
            $response = new JsonResponse();
            return $response->setData(array('rue' => $datos));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Controlador que obtiene los datos de todos los módulos de un estudiante/participante
    // PARAMETROS: por POST sie, estudiante_id, gestion, nivel, especialidad.
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function mostrarAction() {
        try {
            $em = $this->getDoctrine()->getManager();
            $queryEntidad = $em->getConnection()->prepare(
                    'SELECT   codigo_rude, especialidad, especialidad_id, carnet_identidad, paterno, materno, nombre, fecha_nacimiento, depto_nacimiento, grado,  grado_id, estudiante_id, ciclo,
                    ( select sum(s.carga_horaria) from 
                    (select sum(DISTINCT horas_modulo) carga_horaria, modulo
                        from vm_estudiantes_tecnica_alternativa 
                        where especialidad_id = :esp::INT
                            AND estudiante_id = :estudiante::INT
                            AND
                                CASE 
                                    WHEN gestion_tipo_id <= 2015
                                    THEN nota_cuantitativa >=36
                                    ELSE nota_cuantitativa >=51
                                END
                        group by  estudiante_id, modulo  ORDER BY modulo)s) carga_horaria
                    FROM vm_estudiantes_tecnica_alternativa 
                    WHERE 
                    institucioneducativa_id=:sie  and
                    grado_id<= :nivel::INT AND especialidad_id = :esp::INT  AND estudiante_id = :estudiante::INT AND gestion_tipo_id <= :gestion::INT

                    GROUP BY codigo_rude, especialidad, especialidad_id, carnet_identidad, paterno, materno, nombre, fecha_nacimiento, depto_nacimiento, grado, grado_id, estudiante_id, ciclo
                    ORDER BY paterno, materno, nombre');

            $queryEntidad->bindValue(':sie', $_POST['sies']);
            $queryEntidad->bindValue(':estudiante', $_POST['estudiante']);
            $queryEntidad->bindValue(':nivel', $_POST['nivel']);
            $queryEntidad->bindValue(':gestion', $_POST['gestiones']);
            $queryEntidad->bindValue(':esp', $_POST['esp']);
            $queryEntidad->execute();
            $datosTabla['general'] = $queryEntidad->fetchAll();
            $queryHomologacion = $em->getConnection()->prepare(
                    'select sum(carga_horaria) carga_horaria from homologacion where ciclo_id = :esp AND estudiante_id = :estudiante');
            $queryHomologacion->bindValue(':estudiante', $_POST['estudiante']);
            $queryHomologacion->bindValue(':esp', $_POST['esp']);
            $queryHomologacion->execute();
            $datosTabla['homologacion'] = $queryHomologacion->fetchAll();
            $response = new JsonResponse();

            return $response->setData(array('resultadoDatos' => $datosTabla));
        } catch (Exception $ex) {
            
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Controlador que crea el formulario de seguimiento por rude y tramite de acuerdo al identificador
    // PARAMETROS: identificador= 1(busqueda por número trámite, identificador = 0 (Busqueda por código RUDEAL))
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    private function creaFormularioBuscadorSeguimiento($routing, $value1, $value2, $identificador) {

        if ($identificador == 1) {
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl($routing))
                    ->add('tramite', 'text', array('label' => 'Nro. Trámite', 'attr' => array('value' => $value1, 'placeholder' => 'Número de Trámite', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{1,8}', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                    ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                    ->add('search1', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                    ->getForm();
        } else {
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl($routing))
                    ->add('tramite', 'text', array('label' => 'Rude', 'invalid_message' => 'campo obligatorio', 'attr' => array('placeholder' => 'Código RUDEAL', 'style' => 'text-transform:uppercase', 'maxlength' => 20, 'required' => true, 'class' => 'form-control')))
                    ->add('identificador', 'hidden', array('attr' => array('value' => $identificador)))
                    ->add('search1', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                    ->getForm();
        }

        return $form;
    }

    public function historial_notasAction() {
        try {
            $em = $this->getDoctrine()->getManager();
            $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($_POST['estudiante_id']);
            $query = $em->getConnection()->prepare(
                    'select DISTINCT codigo_rude, paterno, materno, nombre,nota_cuantitativa, horas_modulo, especialidad_id, especialidad, ciclo, modulo, gestion_tipo_id, periodo, grado, institucioneducativa, sucursal_tipo_id
                    from vm_estudiantes_tecnica_alternativa a
                    inner join institucioneducativa i ON a.institucioneducativa_id = i.id
                    where estudiante_id = :id and especialidad_id = :esp and nota_cuantitativa >0
                    order by gestion_tipo_id, periodo ASC');

            $query->bindValue(':id', $_POST['estudiante_id']);
            $query->bindValue(':esp', $_POST['especialidad']);
            $query->execute();
            $entity = $query->fetchAll();
//            $response = new JsonResponse();
            return $this->render('SieTramitesBundle:Tramite:resultHistory.html.twig', array(
                        'datastudent' => $student,
                        'dataInscription' => $entity
            ));
//            return $response->setData(array('historialnotas' => $historial));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Controlador que busca los datos de los estudiante/participantes de acuerdo al sie, 
    // la especialidad y el nivel
    // PARAMETROS: por post $request (sie, especialidad, gestiones, nivel, identificador, lista)
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function buscaUnidadEducativaAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $sie = 0;
        $ges = 0;
        $identificador = 0;
        $lista = '';
        $save = $this->session->get('save');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if (!isset($save)) {
            return $this->redirectToRoute('sie_tramites_homepage');
        }

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        if ($this->session->get('save')) {
            $form['sies'] = $this->session->get('datosBusqueda')['sie'];
            $form['gestiones'] = $this->session->get('datosBusqueda')['gestion'];
            $form['especialidad'] = $this->session->get('datosBusqueda')['especialidad'];
            $form['identificador'] = $this->session->get('datosBusqueda')['identificador'];
            $form['nivel'] = $this->session->get('datosBusqueda')['nivel'];
            if ($this->session->get('datosBusqueda')['identificador'] == 0) {
                $form['lista'] = $this->session->get('datosBusqueda')['lista'];
            }
        }
        if ($form) {
            $sie = $form['sies'];
            $ges = $form['gestiones'];
            $identificador = $form['identificador'];
            if ($identificador != 0) {
                $esp = $form['especialidad'];
                $niv = $form['nivel'];
            }
            if ($identificador == 0) {
                $lista = $form['lista'];
            }
            $query = $em->getConnection()->prepare('Select * from institucioneducativa where id = :sie::INT');
            $query->bindValue(':sie', $sie);
            $query->execute();
            $institucionEducativa = $query->fetchAll();
            if (!$institucionEducativa) {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe Unidad Educativa'));
                if ($identificador == 13) {
                    $retorna = $this->redirectToRoute('sie_tramite_recepcion_distrito');
                } elseif ($identificador == 14) {
                    $retorna = $this->redirectToRoute('sie_tramite_recepcion_departamento');
                } elseif ($identificador == 15) {
                    $retorna = $this->redirectToRoute('sie_tramite_autorizacion');
                } elseif ($identificador == 16) {
                    $retorna = $this->redirectToRoute('sie_tramite_impresion');
                } elseif ($identificador == 17) {
                    $retorna = $this->redirectToRoute('sie_tramite_entrega_departamento');
                } elseif ($identificador == 18) {
                    $retorna = $this->redirectToRoute('sie_tramite_entrega_distrito');
                } elseif ($identificador == 0) {
                    $retorna = $this->redirectToRoute('sie_tramite_impresion_listados_detalle');
                } else {
                    $retorna = $this->redirectToRoute('sie_tramites_homepage');
                }
                return $retorna;
            }

            if ($identificador != 0) {
                switch ($niv) {
                    case '1':
                        $flujos = array('id' => 1, 'grado' => 'TÉCNICO BÁSICO');
                        break;
                    case '2':
                        $flujos = array('id' => 2, 'grado' => 'TÉCNICO AUXILIAR');
                        break;
                    case '3':
                        $flujos = array('id' => 3, 'grado' => 'TÉCNICO MEDIO');
                        break;
                }
            } else {
                $niv = null;
                $esp = null;
            }

            $bachilleres = $this->buscaEstudiantesUnidadEducativa($sie, $ges, $niv, $esp, $identificador, $lista);

            if ($identificador == 13) {
                $subtitulo = "Recepcion de Trámite - Distrito";
            } elseif ($identificador == 14) {
                $subtitulo = "Recepcion  de Trámite - Departamental";
            } elseif ($identificador == 15) {
                $subtitulo = "Autorización Trámite Certificado Técnico";
            } elseif ($identificador == 16) {
                $subtitulo = "Impresión Certificado Técnico";
            } elseif ($identificador == 17) {
                $subtitulo = "Entrega de Certificado Técnico - Departamental";
            } elseif ($identificador == 18) {
                $subtitulo = "Entrega de Certificado Técnico - Distrito";
            } elseif ($identificador == 0) {
                $subtitulo = "Impresión Listados";
            }
            if ($identificador != 0) {
                switch ($niv) {
                    case '1':
                        $nivel = 'Técnico Básico';
                        break;
                    case '2':
                        $nivel = 'Técnico Auxiliar';
                        break;
                    case '3':
                        $nivel = 'Técnico Medio';
                        break;
                }
            }
            if ($identificador == 0) {
                $esp = null;
                $nivel = null;

                switch ($lista) {
                    case 16:
                        $l = 'Recepción Distrito';
                        break;
                    case 17:
                        $l = 'Recepción Departamento';
                        break;
                    case 18:
                        $l = 'Autorización';
                        break;
                    case 19:
                        $l = 'Impresión';
                        break;
                    case 21:
                        $l = 'Entrega Distrito';
                        break;
                    case 15:
                        $l = 'Trámite Observado';
                        break;
                }

                return $this->render('SieTramitesBundle:Tramite:ListaTramitesImpresion.html.twig', array(
                            'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', $sie, $ges, null, null, $identificador)->createView()
                            , 'bachilleres' => $bachilleres
                            , 'unidadEducativa' => $institucionEducativa
                            , 'gestion' => $ges
                            , 'titulo' => $institucionEducativa[0]['id'] . ' - ' . $institucionEducativa[0]['institucioneducativa']
                            , 'subtitulo' => $subtitulo
                            , 'identificador' => $identificador
                            , 'listaDesc' => $l
                            , 'listas' => $lista
                ));
            }
            if ($identificador == 13) {
                $tramiteEspecialidad = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->findOneBy(array('id' => $esp));
                return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                            'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', $sie, $ges, $niv, $esp, $identificador)->createView()
                            , 'bachilleres' => $bachilleres
                            , 'unidadEducativa' => $institucionEducativa
                            , 'gestion' => $ges
                            , 'nivel' => $niv
                            , 'nivel_desc' => $nivel
                            , 'especialidad' => $esp
                            , 'especialidad_desc' => $tramiteEspecialidad->getEspecialidad()
                            , 'sie' => $sie
                            , 'titulo' => $institucionEducativa[0]['id'] . ' - ' . $institucionEducativa[0]['institucioneducativa']
                            , 'subtitulo' => $subtitulo
                            , 'identificador' => $identificador
                            , 'flujos' => $flujos
                ));
            }
            if ($identificador == 14 || $identificador == 15 || $identificador == 17 || $identificador == 18) {
                $tramiteEspecialidad = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->findOneBy(array('id' => $esp));
//                print_r($identificador); die;
                return $this->render('SieTramitesBundle:Tramite:ListaTramitesUnidadEducativa.html.twig', array(
                            'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', $sie, $ges, $niv, $esp, $identificador)->createView()
                            , 'bachilleres' => $bachilleres
                            , 'unidadEducativa' => $institucionEducativa
                            , 'gestion' => $ges
                            , 'nivel' => $niv
                            , 'nivel_desc' => $nivel
                            , 'especialidad' => $esp
                            , 'especialidad_desc' => $tramiteEspecialidad->getEspecialidad()
                            , 'sie' => $sie
                            , 'titulo' => $institucionEducativa[0]['id'] . ' - ' . $institucionEducativa[0]['institucioneducativa']
                            , 'subtitulo' => $subtitulo
                            , 'identificador' => $identificador
                ));
            }
            if ($identificador == 16) {
                $tramiteEspecialidad = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->findOneBy(array('id' => $esp));
                $query = $em->getConnection()->prepare('select gestion, serie from series_gestion a where cast(a.gestion as int) <= cast(extract(year from now()) as int) ORDER BY a.gestion asc');
                $query->execute();
                $arraySerie = $query->fetchAll();
                $c = 0;
                foreach ($arraySerie as $value) {
                    $dato[$c] = array('value' => str_replace('A-', '', $value['serie']), 'serie' => str_replace('A-', '', $value['serie']), 'gestion' => $value['gestion']);
                    $c+=1;
                }
                $entitySerie = $dato;
                $query = $em->getConnection()->prepare("select * from gestion_tipo where id >=2013 order by id desc");
                $query->execute();
                $entityGestion = $query->fetchAll();

                return $this->render('SieTramitesBundle:Tramite:ListaTramitesUnidadEducativa.html.twig', array(
                            'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', $sie, $ges, $niv, $esp, $identificador)->createView()
                            , 'bachilleres' => $bachilleres
                            , 'unidadEducativa' => $institucionEducativa
                            , 'gestion' => $ges
                            , 'VariasGestion' => $entityGestion
                            , 'nivel' => $niv
                            , 'nivel_desc' => $nivel
                            , 'especialidad' => $esp
                            , 'especialidad_desc' => $tramiteEspecialidad->getEspecialidad()
                            , 'sie' => $sie
                            , 'titulo' => $institucionEducativa[0]['id'] . ' - ' . $institucionEducativa[0]['institucioneducativa']
                            , 'subtitulo' => $subtitulo
                            , 'identificador' => $identificador
                            , 'gestiones' => $entityGestion
                            , 'series' => $entitySerie
                ));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar la busqueda, intente nuevamente'));
            return $this->redirectToRoute('sie_tramite_busca_ue');
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Metodo que nos permite imprimir en formato pdf el listado de los estudiantes/participantes, de acuerdo 
    // al tipo de flujo que se elija
    // PARAMETROS: por post $request (sie, gestion  y lista)
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function impresionListadoPdfAction(Request $request) {
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $lista = $request->get('lista');

        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'Reporte_' . $sie . '_' . date('YmdHis') . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_lst_estudiante_tipoflujo_v1_pmm.rptdesign&sie=' . $sie . '&gestiones=' . $gestion . '&flujos=' . $lista . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // funcion que ejecuta consultas de busqueda de datos de los estudiante/participantes de acuerdo 
    // al identificador.
    // PARAMETROS: sie, gestion, nivel, especialidad, identificador, lista
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function buscaEstudiantesUnidadEducativa($sie, $gestion, $nivel, $especialidad, $identificador, $lista) {
        $em = $this->getDoctrine()->getManager();
        if ($identificador == 0 and $lista < 100) {
            $query = $em->getConnection()->prepare("
                SELECT DISTINCT t.id tramite_id, e.paterno, e.materno, e.nombre, e.codigo_rude,
                CASE
                        WHEN e.complemento = '' THEN e.carnet_identidad ELSE e.carnet_identidad || '-' || e.complemento
                END carnet_identidad, e.fecha_nacimiento, lt1.lugar depto_nacimiento,

                CASE 
                        WHEN ta.nivel = 1 THEN 'Técnico Básico'
                        WHEN ta.nivel = 2 THEN 'Técnico Auxiliar'
                        WHEN ta.nivel = 3 THEN 'Técnico Medio'
                END grado,
                se.id especialidad_id, se.especialidad

                FROM tramite t 
                inner join  tramite_alternativa ta on ta.tramite_id = t.id
                inner join tramite_detalle td on td.tramite_id = t.id 
                inner join estudiante e on e.id = ta.estudiante
                inner join superior_especialidad_tipo se On se.id = ta.esp
                inner join institucioneducativa ie on ie.id = ta.institucioneducativa
                inner join jurisdiccion_geografica jg on jg.id = ie.le_juridicciongeografica_id
                inner join lugar_tipo lt1 on lt1.id = e.lugar_nac_tipo_id
                inner join lugar_tipo lt2 on lt2.id = lt1.lugar_tipo_id
                where 
                        td.tramite_estado_id = 1 and
                        td.flujo_proceso_id = :flujo and 
                        ta.institucioneducativa = :sie and 
                        t.gestion_id = :gestion

                order by e.paterno, e.materno, e.nombre ASC 
            ");
            $query->bindValue(':sie', $sie);
            $query->bindValue(':gestion', $gestion);
            $query->bindValue(':flujo', $lista);
            $query->execute();
            $entity = $query->fetchAll();
            return $entity;
        }

        if ($identificador == 13) {
            $entity = array();

            $query = $em->getConnection()->prepare(
                    '
                    SELECT DISTINCT 
			j.id estudiante_id,
			h.gestion_tipo_id,
			e.institucioneducativa_id,
			a.codigo AS nivel_id,
			a.facultad_area AS nivel,
			d.codigo AS grado_id,
			d.acreditacion AS grado,
			j.codigo_rude,
			j.paterno,
			j.materno,
			j.nombre,
			b.id AS especialidad_id,
			b.especialidad,
			j.genero_tipo_id,
			j.fecha_nacimiento,
			j.carnet_identidad,
			lt2.codigo AS depto_nacimiento_id,
			lt2.lugar AS depto_nacimiento
                    FROM ((((((((((((((((((((superior_facultad_area_tipo a
                             JOIN superior_especialidad_tipo b ON ((a.id = b.superior_facultad_area_tipo_id)))
                             JOIN superior_acreditacion_especialidad c ON ((b.id = c.superior_especialidad_tipo_id)))
                             JOIN superior_acreditacion_tipo d ON ((c.superior_acreditacion_tipo_id = d.id)))
                             JOIN superior_institucioneducativa_acreditacion e ON ((e.acreditacion_especialidad_id = c.id)))
                             JOIN institucioneducativa_sucursal f ON ((e.institucioneducativa_sucursal_id = f.id)))
                             JOIN superior_institucioneducativa_periodo g ON ((g.superior_institucioneducativa_acreditacion_id = e.id)))
                             JOIN institucioneducativa_curso h ON ((h.superior_institucioneducativa_periodo_id = g.id)))
                             JOIN estudiante_inscripcion i ON ((h.id = i.institucioneducativa_curso_id)))
                             JOIN estudiante j ON ((i.estudiante_id = j.id)))
                             JOIN superior_modulo_periodo k ON ((g.id = k.institucioneducativa_periodo_id)))
                             JOIN superior_modulo_tipo l ON ((l.id = k.superior_modulo_tipo_id)))
                             JOIN institucioneducativa_curso_oferta m ON (((m.superior_modulo_periodo_id = k.id) AND (m.insitucioneducativa_curso_id = h.id))))
                             JOIN estudiante_asignatura n ON (((n.institucioneducativa_curso_oferta_id = m.id) AND (n.estudiante_inscripcion_id = i.id))))
                             JOIN estudiante_nota o ON ((o.estudiante_asignatura_id = n.id)))
                             JOIN paralelo_tipo p ON (((h.paralelo_tipo_id)::text = (p.id)::text)))
                             JOIN turno_tipo q ON ((h.turno_tipo_id = q.id)))
                             JOIN nota_tipo r ON ((o.nota_tipo_id = r.id)))
                             JOIN periodo_tipo s ON ((s.id = f.periodo_tipo_id)))
                             LEFT JOIN lugar_tipo lt1 ON ((lt1.id = j.lugar_prov_nac_tipo_id)))
                             LEFT JOIN lugar_tipo lt2 ON ((lt2.id = lt1.lugar_tipo_id)))

                    WHERE ((a.codigo >= 18) AND (a.codigo <= 25))
                            and h.institucioneducativa_id= :sie
                            and b.id = :esp
                            and d.codigo = :nivel
                            and h.gestion_tipo_id = :gestion
                            and j.id  not in (select DISTINCT e.id
                                            from estudiante e
                                                    inner join tramite_alternativa tv on tv.estudiante = e.id
                                                    inner join tramite_detalle td on td.tramite_id = tv.tramite_id 
                                            where td.flujo_proceso_id in (16,17,18,19) and td.tramite_estado_id = 1 and tv.esp = :esp and tv.nivel = :nivel)
                            ORDER BY j.paterno, j.materno, j.nombre');
            $query->bindValue(':sie', $sie);
            $query->bindValue(':esp', $especialidad);
            $query->bindValue(':nivel', $nivel);
            $query->bindValue(':gestion', $gestion);

            $query->execute();
            $entity = $query->fetchAll();
            return $entity;
        }
        if ($identificador == 14) {
            $query = $em->getConnection()->prepare("
                        SELECT td.tramite_id, alt.estudiante_id, alt.codigo_rude, alt.institucioneducativa_id, alt.gestion_tipo_id, 
                        alt.paterno, alt.materno, alt.nombre, alt.carnet_identidad, alt.fecha_nacimiento, alt.depto_nacimiento, alt.especialidad_id, 
                        alt.especialidad, alt.grado, alt.grado_id, t.id, td.flujo_proceso_id,td.tramite_estado_id,t.tramite_tipo, flujo_tipo_id
                        FROM tramite t
                        INNER JOIN tramite_detalle td ON t.id = td.tramite_id
                        INNER JOIN vm_estudiantes_tecnica_alternativa alt ON alt.estudiante_id = t.estudiante_inscripcion_id
                        WHERE td.flujo_proceso_id  = 16 AND td.tramite_estado_id =1 AND alt.institucioneducativa_id = :sie::INT AND alt.grado_id = :nivel::INT AND alt.especialidad_id = :esp::INT AND alt.gestion_tipo_id = :gestion::INT
                        GROUP BY td.tramite_id, alt.estudiante_id, alt.codigo_rude, alt.institucioneducativa_id, alt.gestion_tipo_id, alt.paterno, alt.materno, alt.nombre, alt.carnet_identidad, alt.fecha_nacimiento, alt.depto_nacimiento, alt.especialidad_id, alt.especialidad, alt.grado, alt.grado_id,  t.id, td.flujo_proceso_id,td.tramite_estado_id,t.tramite_tipo, flujo_tipo_id
                        ORDER BY alt.paterno, alt.materno, alt.nombre, alt.codigo_rude ASC
                    ");
            $query->bindValue(':sie', $sie);
            $query->bindValue(':gestion', $gestion);
            $query->bindValue(':esp', $especialidad);
            $query->bindValue(':nivel', $nivel);
            if ($identificador == 0 and $lista < 100) {
                $query->bindValue(':flujo', $lista);
            }
            $query->execute();
            $entity = $query->fetchAll();
            return $entity;
        }
        if ($identificador == 15) {
            $query = $em->getConnection()->prepare("
                            select distinct t.id tramite_id, e.paterno, e.materno, e.nombre, e.codigo_rude, e.carnet_identidad,
                    e.fecha_nacimiento, lt1.lugar depto_nacimiento, ie.id institucioneducativa_id, ie.institucioneducativa centros,
                    case 
                                            when tv.nivel = 1 then 'Técnico Básico'
                                            when tv.nivel = 2 then 'Técnico Auxiliar'
                                            when tv.nivel = 3 then 'Técnico Medio'
                    end grado, tv.nivel grado_id, e.id estudiante_id, se.id especialidad_id, se.especialidad especialidad, 
                    td.flujo_proceso_id, td.tramite_estado_id
                    FROM
                    estudiante e 
                    inner join tramite_alternativa tv on tv.estudiante = e.id
                    inner join institucioneducativa ie on ie.id = tv.institucioneducativa
                    inner join tramite t on t.id = tv.tramite_id
                    inner join tramite_detalle td on td.tramite_id = t.id
                    inner join superior_especialidad_tipo se on se.id =tv.esp
                    inner join lugar_tipo lt1 on lt1.id = e.lugar_nac_tipo_id
                    inner join lugar_tipo lt2 on lt2.id = lt1.lugar_tipo_id
                    where 
                    td.flujo_proceso_id = 17
                    and td.tramite_estado_id = 1 and 
                    tv.institucioneducativa = :sie
                    and tv.esp = :esp
                    and tv.nivel = :nivel
                    and t.gestion_id = :gestion
                    group BY
                    t.id, e.paterno, e.materno, e.nombre, e.codigo_rude, e.carnet_identidad,
                    e.fecha_nacimiento, lt1.lugar,ie.id, ie.institucioneducativa,
                    tv.nivel, tv.nivel, e.id, se.id, se.especialidad, 
                    td.flujo_proceso_id, td.tramite_estado_id
                    order by e.paterno, e.materno, e.nombre
                    ");
            $query->bindValue(':sie', $sie);
            $query->bindValue(':gestion', $gestion);
            $query->bindValue(':esp', $especialidad);
            $query->bindValue(':nivel', $nivel);
            if ($identificador == 0 and $lista < 100) {
                $query->bindValue(':flujo', $lista);
            }
            $query->execute();
            $entity = $query->fetchAll();
            return $entity;
        }
        if ($identificador == 16) {
            $query = $em->getConnection()->prepare("
                    select distinct  ta.estudiante estudiante_id, td.flujo_proceso_id, initcap(e.paterno) paterno, initcap(e.materno) materno, initcap(e.nombre) nombre,
                    e.codigo_rude, e.carnet_identidad,  e.fecha_nacimiento, 
                    CASE
                            WHEN lt4.lugar = 'NINGUNO' THEN '' ELSE lt4.lugar
                    END depto_nacimiento ,
                    t.tramite_tipo,
                    ta.tramite_id, nivel as GRADO,  se.especialidad, 
                    initcap(ie.institucioneducativa) centro, ta.institucioneducativa, lt2.lugar,  lt1.lugar distrito
                    from tramite_alternativa ta 
                    INNER JOIN tramite t ON t.id = ta.tramite_id
                    inner join tramite_detalle td ON td.tramite_id = ta.tramite_id
                    inner join estudiante e on ta.estudiante  = e.id 
                    INNER JOIN institucioneducativa ie ON ie.id = ta.institucioneducativa
                    inner join superior_especialidad_tipo se ON se.id = ta.esp
                    inner join jurisdiccion_geografica jg on jg.id = ie.le_juridicciongeografica_id
                    inner join lugar_tipo lt1 on lt1.id = jg.lugar_tipo_id_distrito
                    inner join lugar_tipo lt2 on lt2.id = lt1.lugar_tipo_id
                    inner join lugar_tipo lt3 on lt3.id = e.lugar_prov_nac_tipo_id
                    inner join lugar_tipo lt4 on lt4.id = lt3.lugar_tipo_id
                    WHERE 
                    ta.institucioneducativa = :sie
                    and ta.esp = :esp
                    and ta.nivel not in  (3)
                    AND t.tramite_tipo not in (8)
                    AND td.flujo_proceso_id = 18
                    AND td.tramite_estado_id = 1
                    and ta.nivel = :nivel
                    AND t.gestion_id = :gestion
                    order by distrito, centro, especialidad, paterno, materno, nombre
                    ");
            $query->bindValue(':sie', $sie);
            $query->bindValue(':gestion', $gestion);
            $query->bindValue(':esp', $especialidad);
            $query->bindValue(':nivel', $nivel);
            if ($identificador == 0 and $lista < 100) {
                $query->bindValue(':flujo', $lista);
            }
            $query->execute();
            $entity = $query->fetchAll();
            return $entity;
        }
        if ($identificador == 17) {
            $query = $em->getConnection()->prepare("
                        SELECT DISTINCT t.id tramite_id, e.paterno, e.materno, e.nombre, e.codigo_rude, e.carnet_identidad,
                        e.fecha_nacimiento, lt1.lugar depto_nacimiento, ie.id institucioneducativa_id, ie.institucioneducativa centro,
                        case 
                                when tv.nivel = 1 then 'Técnico Básico'
                                when tv.nivel = 2 then 'Técnico Auxiliar'
                                when tv.nivel = 3 then 'Técnico Medio'
                        end grado, tv.nivel grado_id, e.id estudiante_id, se.id especialidad_id, se.especialidad especialidad, 
                        td.flujo_proceso_id, td.tramite_estado_id, iec.gestion_tipo_id
                         FROM
                        estudiante e 
                        inner join estudiante_inscripcion ei on ei.estudiante_id = e.id
                        inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
                        inner join institucioneducativa ie on ie.id = iec.institucioneducativa_id
                        inner join tramite_alternativa tv on tv.estudiante = e.id
                        inner join tramite t on t.id = tv.tramite_id
                        inner join tramite_detalle td on td.tramite_id = t.id
                        inner join superior_especialidad_tipo se on se.id =tv.esp
                        inner join lugar_tipo lt1 on lt1.id = e.lugar_nac_tipo_id
                        inner join lugar_tipo lt2 on lt2.id = lt1.lugar_tipo_id
                        WHERE 
                        td.flujo_proceso_id = 19
                        and td.tramite_estado_id = 1 and 
                        ie.id = :sie
                        and tv.esp = :esp
                        and tv.nivel = :nivel
                        and tv.nivel not in  (3)
                        AND t.tramite_tipo not in (8)
                        and iec.gestion_tipo_id = :gestion
                        GROUP BY
                        t.id, e.paterno, e.materno, e.nombre, e.codigo_rude, e.carnet_identidad,
                        e.fecha_nacimiento, lt1.lugar, ie.id, ie.institucioneducativa,
                        tv.nivel, tv.nivel, e.id, se.id, se.especialidad, 
                        td.flujo_proceso_id, td.tramite_estado_id, iec.gestion_tipo_id
                        order by e.paterno, e.materno, e.nombre
                    ");
            $query->bindValue(':sie', $sie);
            $query->bindValue(':gestion', $gestion);
            $query->bindValue(':esp', $especialidad);
            $query->bindValue(':nivel', $nivel);
            if ($identificador == 0 and $lista < 100) {
                $query->bindValue(':flujo', $lista);
            }
            $query->execute();
            $entity = $query->fetchAll();
            return $entity;
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Controlador que registra el tramite en las tablas Tramite_alternativa, y en la tabla Tramite, 
    // en caso de que exista un registro en la tabla tramite_alternativa con los mismos datos de la busqueda,
    // no se creara un registro duplicado, una vez creado el registro se enviaran datos a la funcion procesaTramite
    // en la cual, en primera instancia creara un registro con el flujo_proceso_id =16 en la tabla tramite_detalle
    // PARAMETROS: Request (estudiantes"estudiantes seleccionados", gestion, especialidad, sie, nivel)
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function registraTramiteCertificadoAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $sesion = $request->getSession();
        $usuarioId = $sesion->get('userId');
        //valida si el usuario ha iniciado sesión
        if (!isset($usuarioId)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();

            /*
             * Recupera datos del formulario
             */

            $estudiantes = $request->get('estudiantes');
            $estud = explode(',', $request->get('estudiante'));
            if (sizeof($estudiantes) > sizeof($estud)) {
                $estudiantes = $estudiantes;
            } else {
                $estudiantes = $estud;
            }
            $gestion = $request->get('gestion');
            $ue = $request->get('sie');
            $identificador = $request->get('identificador');
            $especialidad = $request->get('especialidad');
            $nivel = $request->get('nivel');

            if ($identificador == 13) {
                $retorna = 'sie_tramite_recepcion_distrito';
            }
            if ($identificador == 14) {
                $retorna = 'sie_tramite_recepcion_departamento';
            }
            if ($identificador == 15) {
                $retorna = 'sie_tramite_autorizacion';
            }
            if ($identificador == 17) {
                $retorna = 'sie_tramite_entrega_departamento';
            }

            /*
             * Verifica si el estudiante figura como promovido
             */

            if (isset($_POST['botonAceptar'])) {
                $flujoSeleccionado = 'Adelante';
            }
            if (isset($_POST['botonDevolver'])) {
                $flujoSeleccionado = 'Atras';
            }

            if (isset($_POST['botonEntregar'])) {
                $flujoSeleccionado = 'Finalizar';
            }
            try {
                /*
                 * Denine el tipo de tramite y flujo que se aplicara al trámite
                 */
                switch ($request->get('nivel')) {
                    case '1':
                        $tramiteTipoId = '6';
                        break;
                    case '2':
                        $tramiteTipoId = '7';
                        break;
                    case '3':
                        $tramiteTipoId = '8';
                        break;
                }
                $flujoTipoId = 4;

                /*
                 * Recorre lista de estudiantes que ingresan su trámite
                 */
                foreach ($estudiantes as $estudiante) {
                    $estudianteInscripcionId = (Int) $estudiante;
                    $em = $this->getDoctrine()->getManager();
                    $entityTramiteTipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('id' => $tramiteTipoId));
                    $entityFlujoTipo = $em->getRepository('SieAppWebBundle:FlujoTipo')->findOneBy(array('id' => $flujoTipoId));
                    $entityTramAlternativa = $em->getRepository('SieAppWebBundle:TramiteAlternativa');
                    $query4 = $entityTramAlternativa->createQueryBuilder('ta')
                            ->where('ta.estudiante= :codEstudiante')
                            ->andwhere('ta.esp = :codEsp')
                            ->andwhere('ta.nivel = :codNivel')
                            ->orderBy('ta.estudiante', 'ASC')
                            ->setParameter('codEstudiante', $estudianteInscripcionId)
                            ->setParameter('codEsp', $especialidad)
                            ->setParameter('codNivel', $nivel);
                    $entityTramAlternativa = $query4->getQuery()->getResult();
                    $queryPeriodo = $em->getConnection()->prepare('SELECT  
                                        MAX(f.periodo_tipo_id)
                                        FROM (((((((((((((((superior_facultad_area_tipo a
                                          JOIN superior_especialidad_tipo b ON ((a.id = b.superior_facultad_area_tipo_id)))
                                          JOIN superior_acreditacion_especialidad c ON ((b.id = c.superior_especialidad_tipo_id)))
                                          JOIN superior_acreditacion_tipo d ON ((c.superior_acreditacion_tipo_id = d.id)))
                                          JOIN superior_institucioneducativa_acreditacion e ON ((e.acreditacion_especialidad_id = c.id)))
                                          JOIN institucioneducativa_sucursal f ON ((e.institucioneducativa_sucursal_id = f.id)))
                                          JOIN superior_institucioneducativa_periodo g ON ((g.superior_institucioneducativa_acreditacion_id = e.id)))
                                          JOIN institucioneducativa_curso h ON ((h.superior_institucioneducativa_periodo_id = g.id)))
                                          JOIN estudiante_inscripcion i ON ((h.id = i.institucioneducativa_curso_id)))
                                          JOIN estudiante j ON ((i.estudiante_id = j.id)))
                                          JOIN superior_modulo_periodo k ON ((g.id = k.institucioneducativa_periodo_id)))
                                          JOIN superior_modulo_tipo l ON ((l.id = k.superior_modulo_tipo_id)))
                                          JOIN institucioneducativa_curso_oferta m ON (((m.superior_modulo_periodo_id = k.id) AND (m.insitucioneducativa_curso_id = h.id))))
                                          JOIN periodo_tipo s ON ((s.id = f.periodo_tipo_id)))
                                          LEFT JOIN lugar_tipo lt1 ON ((lt1.id = j.lugar_prov_nac_tipo_id)))
                                          LEFT JOIN lugar_tipo lt2 ON ((lt2.id = lt1.lugar_tipo_id)))
                                       WHERE h.gestion_tipo_id = :gestion  and j.id = :estudiante and b.id = :esp
                                     group by (f.periodo_tipo_id)
                                     limit 1');
                    $queryPeriodo->bindValue(':estudiante', $estudianteInscripcionId);
                    $queryPeriodo->bindValue(':gestion', $gestion);
                    $queryPeriodo->bindValue(':esp', (int) $especialidad);
                    $queryPeriodo->execute();
                    $periodo = $queryPeriodo->fetchAll();
                    if (sizeof($entityTramAlternativa) == 0) {
                        $query = $em->getConnection()->prepare('select max(id) as id from tramite ');
                        $query->execute();
                        $maxId = $query->fetchAll();
                        $max = $maxId[0]['id'] + 1;
                        $queryInsert = $em->getConnection()->prepare("INSERT INTO tramite(tramite, estudiante_inscripcion_id, flujo_tipo_id, "
                                . "tramite_tipo, fecha_tramite, fecha_registro, fecha_fin, esactivo, gestion_id, "
                                . "apoderado_inscripcion_id, maestro_inscripcion_id, institucioneducativa_id) "
                                . "VALUES(:tramite, :estudiante, :flujo_tipo, :tramite_tipo, :fecha_tramite,"
                                . ":fecha_registro, :fecha_fin, :esactivo, :gestion_id, :apoderado_inscripcion, "
                                . ":maestro_inscripcion, :institucioneducativa_id)");
                        $queryInsert->bindValue(':tramite', (string) $max);
                        $queryInsert->bindValue(':estudiante', $estudianteInscripcionId);
                        $queryInsert->bindValue(':flujo_tipo', $entityFlujoTipo->getId());
                        $queryInsert->bindValue(':tramite_tipo', $entityTramiteTipo->getId());
                        $queryInsert->bindValue(':fecha_tramite', date('Y-m-d'));
                        $queryInsert->bindValue(':fecha_registro', date('Y-m-d'));
                        $queryInsert->bindValue(':fecha_fin', null);
                        $queryInsert->bindValue(':esactivo', "t");
                        $queryInsert->bindValue(':gestion_id', (int) $gestion);
                        $queryInsert->bindValue(':apoderado_inscripcion', null);
                        $queryInsert->bindValue(':maestro_inscripcion', null);
                        $queryInsert->bindValue(':institucioneducativa_id', (int) $ue);
                        $queryInsert->execute();
                        $query = $em->getConnection()->prepare('select max(id) as id from tramite ');
                        $query->execute();
                        $maxId = $query->fetchAll();
                        $max = $maxId[0]['id'];
                        $queryTramiteAlt = $em->getConnection()->prepare('INSERT INTO tramite_alternativa(estudiante, esp, nivel,  institucioneducativa, tramite_id, fecha) '
                                . 'VALUES( :estudiante, :esp, :nivel,  :institucion, :tramite, :fecha)');
                        $queryTramiteAlt->bindValue(':estudiante', $estudianteInscripcionId);
                        $queryTramiteAlt->bindValue(':esp', (int) $especialidad);
                        $queryTramiteAlt->bindValue(':nivel', $nivel);
                        $queryTramiteAlt->bindValue(':institucion', (int) $ue);
                        $queryTramiteAlt->bindValue(':tramite', $max);
                        $queryTramiteAlt->bindValue(':fecha', date('Y-m-d h:i:s'));
                        $queryTramiteAlt->execute();
                        $tramiteId = $max;
                    } else {

                        $tramiteId = $entityTramAlternativa[0]->getTramiteId();
                    }
                    $error = $this->procesaTramite($tramiteId, $usuarioId, $flujoSeleccionado, '', $estudianteInscripcionId, $especialidad, $nivel, $ue, $periodo[0]['max']);
                }
                $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Trámite registrado'));
                $this->session->set('save', true);
                $this->session->set('datosBusqueda', array('sie' => $ue, 'gestion' => $gestion, 'identificador' => $identificador, 'especialidad' => $especialidad, 'nivel' => $nivel, 'tipoTramite' => $tramiteTipoId));
                return $this->redirectToRoute('sie_tramite_busca_ue');
            } catch (Exception $e) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al ingresar registro, intente nuevamente'));
                return $this->redirectToRoute($retorna);
            }
            $valores = array('sie' => $ue, 'gestion' => $gestion, 'identificador' => $identificador, 'especialidad' => $especialidad, 'nivel' => $nivel, 'tipoTramite' => $tramiteTipoId);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Datos invalidos, intente nuevamente'));
            $valores = array('sie' => 0, 'gestion' => 0, 'especialidad' => 0, 'identificador' => 0, 'nivel' => 0);
            return $this->redirectToRoute('sie_tramite_busca_ue');
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // esta funcion registra el tramite en la tabla tramite_detalle, en la cual se recorrera el flujo que se muestra 
    // en al tabla flujo_tipo a partir del item 15, dentro esta funcion se podra pasar de una instancia a otra o anular
    // el tramite, la cual su flujo del tramite cambiara a 15.
    // PARAMETROS: Request (estudiantes"estudiantes seleccionados", gestion, especialidad, sie, nivel)
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    private function procesaTramite($tramiteId, $usuarioId, $flujoSeleccionado, $obs, $estudianteInscripcionId, $especialidad, $nivel, $ue, $periodo) {

        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $em = $this->getDoctrine()->getManager();
        $tramiteAnulado = 0;
        /*
         * Forma las entidades para ingresar sus valores (solo en caso de campos relacionados) - Tramite
         */

        $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));
        $entityTramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle');
        $query = $entityTramiteDetalle->createQueryBuilder('td')
                ->where('td.tramite = :codTramite')
                ->orderBy('td.id', 'DESC')
                ->setParameter('codTramite', $tramiteId)
                ->setMaxResults('1');
        $entityTramiteDetalle = $query->getQuery()->getResult();
        $entityTramiteEstado = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => 1));
        $entityUsuarioRemitente = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));
        $entityFlujoProceso = $em->getRepository('SieAppWebBundle:FlujoProceso');

        if ($entityTramiteDetalle) {
            /*
             * Extrae la posicion del flujo que debe seguir
             */
            $entityFlujoProcesoDetalle = $em->getRepository('SieAppWebBundle:FlujoProcesoDetalle');
            $query2 = $entityFlujoProcesoDetalle->createQueryBuilder('fpd')
                    ->where('fpd.id = :codFlujoProceso')
                    ->setParameter('codFlujoProceso', $entityTramiteDetalle[0]->getFlujoProceso()->getId())
                    ->setMaxResults('1');
            $entityFlujoProcesoDetalle = $query2->getQuery()->getResult();

            /*
             * Extrae la posicion inicial del flujo actual
             */
            $entityFlujoInicio = $em->getRepository('SieAppWebBundle:FlujoProceso');
            $query3 = $entityFlujoInicio->createQueryBuilder('fp')
                    ->where('fp.flujoTipo = :codFlujo')
                    ->orderBy('fp.orden', 'ASC')
                    ->setParameter('codFlujo', $entityTramite->getFlujoTipo()->getId())
                    ->setMaxResults('1');
            $entityFlujoInicio = $query3->getQuery()->getResult();

            if ($flujoSeleccionado == 'Adelante') {
                $entityTramiteEstadoSiguiente = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => 3));
                $query = $entityFlujoProceso->createQueryBuilder('fp')
                        ->where('fp.id = :codFlujoProceso')
                        ->orderBy('fp.obs', 'ASC')
                        ->setParameter('codFlujoProceso', $entityFlujoProcesoDetalle[0]->getFlujoProcesoSig()->getId())
                        ->setMaxResults('1');
            } else {
                if ($flujoSeleccionado == 'Anular') {
                    $entityTramiteEstadoSiguiente = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => 2));
                    $query = $entityFlujoProceso->createQueryBuilder('fp')
                            ->where('fp.id = :codFlujoProceso')
                            ->orderBy('fp.obs', 'ASC')
                            ->setParameter('codFlujoProceso', $entityFlujoInicio[0]->getId())
                            ->setMaxResults('1');
                } else {
                    if ($flujoSeleccionado == 'Finalizar') {
                        $entityTramiteEstadoSiguiente = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => 3));
                        /*
                         * Extrae la posicion final del flujo actual
                         */
                        $query = $entityFlujoProceso->createQueryBuilder('fp')
                                ->where('fp.flujoTipo = :codFlujo')
                                ->orderBy('fp.orden', 'DESC')
                                ->setParameter('codFlujo', $entityTramite->getFlujoTipo()->getId())
                                ->setMaxResults('1');
                    } else {
                        $entityTramiteEstadoSiguiente = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => 4));
                        $query = $entityFlujoProceso->createQueryBuilder('fp')
                                ->where('fp.id = :codFlujoProceso')
                                ->orderBy('fp.obs', 'ASC')
                                ->setParameter('codFlujoProceso', $entityFlujoProcesoDetalle[0]->getFlujoProcesoAnt()->getId())
                                ->setMaxResults('1');
                    }
                }
            }
        } else {

            $query = $entityFlujoProceso->createQueryBuilder('fp')
                    ->where('fp.flujoTipo = :codFlujoTipo')
                    ->andWhere('fp.orden <> 0 ')
                    ->orderBy('fp.orden', 'ASC')
                    ->setParameter('codFlujoTipo', $entityTramite->getFlujoTipo())
                    ->setMaxResults('1');
        }
        /*
         *  VERIFICACION SI EN TRAMITE_DETALLE SE ENCUENTRA REGISTRADO LA MISMA INSTANCIA QUE SE DESEA INSERTAR
         */

        $entityFlujoProceso = $query->getQuery()->getResult();
        $entityTramDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle');
        $query3 = $entityTramDetalle->createQueryBuilder('td')
                ->where('td.tramite= :codTramite')
                ->andwhere('td.flujoProceso = :codFlujoProceso')
                ->andwhere('td.tramiteEstado = :codTramiteEstado')
                ->orderBy('td.flujoProceso', 'ASC')
                ->setParameter('codTramite', $entityTramite->getId())
                ->setParameter('codTramiteEstado', $entityTramiteEstado->getId())
                ->setParameter('codFlujoProceso', $entityFlujoProceso[0]->getId());
        $entityTramDetalle = $query3->getQuery()->getResult();
        if (sizeof($entityTramDetalle) == 0) {
            /*
             * Define el conjunto de valores a ingresar - Tramite Detalle
             */
            $em->getConnection()->beginTransaction();
            $entityTramiteDetalleNew = new TramiteDetalle();
            $entityTramiteDetalleNew->setUsuarioRemitente($entityUsuarioRemitente);
            if ($entityTramiteDetalle) {
                $entityTramiteDetalleNew->setTramiteDetalle($entityTramiteDetalle[0]);
                if ($flujoSeleccionado == 'Adelante') {
                    $entityTramiteDetalleNew->setObs('TRÁMITE - PROCESADO');
                } else {
                    if ($flujoSeleccionado == 'Anular') {
                        $entityTramiteDetalleNew->setObs('TRÁMITE - ANULADO');
                    } else {
                        $entityTramiteDetalleNew->setObs('TRÁMITE - ENTREGADO');
                    }
                }
            } else {
                $entityTramiteDetalleNew->setObs('TRÁMITE - RECEPCION DISTRITO');
            }
            $entityTramiteDetalleNew->setTramite($entityTramite);
            $entityTramiteDetalleNew->setTramiteEstado($entityTramiteEstado);
            $entityTramiteDetalleNew->setFechaRegistro($fechaActual);
            $entityTramiteDetalleNew->setFechaEnvio($fechaActual);
            $entityTramiteDetalleNew->setFechaModificacion($fechaActual);
            $entityTramiteDetalleNew->setFlujoProceso($entityFlujoProceso[0]);

            /*
             * Define el conjunto de valores a ingresar - Tramite Detalle Anterior
             */
            if ($entityTramiteDetalle) {
                $entityTramiteDetalle[0]->setUsuarioDestinatarioId($usuarioId);
                $entityTramiteDetalle[0]->setFechaModificacion($fechaActual);
                $entityTramiteDetalle[0]->setTramiteEstado($entityTramiteEstadoSiguiente);
                $em->persist($entityTramiteDetalle[0]);
                $em->flush();
            }

            $em->persist($entityTramiteDetalleNew);
            $em->flush();

            if ($entityFlujoProceso[0]->getOrden() == 0) {
                $entityTramite->setEsactivo('0');
                $em->persist($entityTramite);
                $em->flush();
            }
            $em->getConnection()->commit();
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Esta funcion registra el tramite en la tabla tramite_alternativa
    // PARAMETROS: Request (estudiantes"estudiantes seleccionados", gestion, especialidad, sie, nivel)
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function insertTramiteAlternativa($tramiteId, $estudianteInscripcionId, $especialidad, $nivel, $periodo, $ue) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $consulta = $em->getConnection()->prepare('select * from tramite_alternativa where estudiante = :estudiante and tramite_id = :tramite '
                . 'and esp = :esp and nivel = :nivel and institucioneducativa = :sie');
        $consulta->bindValue(':tramite', $tramiteId);
        $consulta->bindValue(':estudiante', $estudianteInscripcionId);
        $consulta->bindValue(':esp', $especialidad);
        $consulta->bindValue(':nivel', $nivel);
        $consulta->bindValue(':sie', $ue);
        $consulta->execute();
        $dConsulta = $consulta->fetchAll();
        $entityTramiteAlternativa = new TramiteAlternativa();
        if (count($dConsulta) == 0) {
            $entityTramiteAlternativa->setEstudiante($estudianteInscripcionId);
            $entityTramiteAlternativa->setEsp($especialidad);
            $entityTramiteAlternativa->setNivel($nivel);
            $entityTramiteAlternativa->setPeriodo($periodo);
            $entityTramiteAlternativa->setInstitucioneducativa($ue);
            $entityTramiteAlternativa->setTramiteId($tramiteId);
            $entityTramiteAlternativa->setFecha($fechaActual);
            $em->persist($entityTramiteAlternativa);
            $em->flush();
        }
        $em->getConnection()->commit();
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Este controller genera un archivo en formato pdf de los datos que fueron enviados al a bandeja de 
    // ventanilla departamento, se imprimira todos los estudiantes/participantes que esten con el flujo_proceso_id 
    // 16 y el tramite_estado_id = 1
    // PARAMETROS: Request (sie, especialidad, gestion)
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function printReportesDistritoPdfAction(Request $request) {
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $esp = $request->get('especialidad');
        $estudiantes = $request->get('ids');
        $arch = $sie . '_distrito_alternativa_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
//        print_r($gestion); die;
//        dump($this->container->getParameter('urlreportweb') . 'alt_tec_list_estudiante_distritoaprovados_v1_pmm.rptdesign&ue=' . $sie . '&esp=' . $esp . '&gestion=' . $gestion . '&&__format=pdf&'); die;
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_list_estudiante_distritoaprovados_v1_pmm.rptdesign&ue=' . $sie . '&esp=' . $esp . '&gestion=' . $gestion . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Este controller genera un archivo en formato pdf de los datos que fueron enviados al a bandeja de 
    // ventanilla departamento, se imprimira todos los estudiantes/participantes que esten con el flujo_proceso_id 
    // 16 y el tramite_estado_id = 1
    // PARAMETROS: Request (sie, especialidad, gestion)
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function printReportesDepartamentalPdfAction(Request $request) {
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $esp = $request->get('esp');
        $arch = $sie . '_departamental_alternativa_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_lst_estudiante_departamentalaprobado_v1_pmm.rptdesign&sie=' . $sie . '&esp=' . $esp . '&gestion=' . $gestion . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Este controller Verifica si la Unidad Educativa esta habilitada para la entrega de diplomas
    // PARAMETROS: Request (sie, especialidad, gestion)
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************    
    private function verificaNivelUnidadEducativa($ue) {
        $em = $this->getDoctrine()->getManager();
        /*
         * Verifica si la Unidad Educativa puede otorgar diplomas de bachiller o registrar para titulos tecnico medio alternativa
         */
        $query = $em->getConnection()->prepare("
                select * from institucioneducativa as ie 
                inner join (select * from institucioneducativa_nivel_autorizado where nivel_tipo_id in (13,15) or nivel_tipo_id > 17) as iena on iena.institucioneducativa_id = ie.id
                where ie.id = :sie::INT       
                ");
        $query->bindValue(':sie', $ue);
        $query->execute();
        $entity = $query->fetchAll();

        if (count($entity) > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Este controller Verifica si la Unidad Educativa esta habilitada para la entrega de certificados, y 
    // el registro del cambio de instancia.
    // PARAMETROS: Request (sie, especialidad, gestion)
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************    

    public function procesaTramiteCertificadoAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        $this->session->set('fecha_registro', date('d/m/Y', strtotime($request->get('fecha'))));

        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //valida si el usuario ha iniciado sesión
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();


        try {
            /*
             * Recupera datos del formulario
             */
            if ($request->isMethod('POST')) {
                $estudiantes = $request->get('estudiantes');
                $gestion = $request->get('gestion');
                $ue = $request->get('sie');
                $nivel = $request->get('nivel');
                $esp = $request->get('especialidad');
                $identificador = $request->get('identificador');
                $obs = '';

                /*
                 * Extrae en codigo de departamento del usuario de acuerdo al rol ya que el rol 40 representa
                 * a impresion alternativa
                 */

                if ($identificador == 16) {
                    $query = $em->getConnection()->prepare("
                    select lt1.lugar_tipo_id, lt1.lugar, lt1.codigo
                    from usuario_rol u
                    inner join lugar_tipo lt1 on lt1.id = u.lugar_tipo_id
                    where u.usuario_id = " . $id_usuario . " and rol_tipo_id = 40
                    limit 1
                ");
                } else {
                    $query = $em->getConnection()->prepare("
                    select lt1.lugar_tipo_id, lt1.lugar, lt1.codigo
                    from usuario_rol u
                    inner join lugar_tipo lt1 on lt1.id = u.lugar_tipo_id
                    where u.usuario_id = " . $id_usuario . " 
                    limit 1");
                }
                $query->execute();
                $entityUsuario = $query->fetchAll();

                $query = $em->getConnection()->prepare("
                    select MIN(lt1.id)departamento
                    from lugar_tipo lt1
                    where lt1.lugar ='" . $entityUsuario[0]['lugar'] . "'
                    limit 1
                ");
                $query->execute();

                $entityLugar = $query->fetchAll();
                $departamentoUsuario = 0;
                if (count($entityUsuario) > 0) {
//                    $departamentoUsuario = $entityUsuario[0]["lugar_tipo_id"];
                    $departamentoU = $entityUsuario[0]["lugar"];
                    $departamentoUsuario = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('departamento' => $departamentoU));
                    $departamentoUserLugar = $departamentoUsuario->getId();
                }
                if ($identificador == 13) {
                    $retorna = 'sie_tramite_recepcion_distrito';
                } elseif ($identificador == 14) {
                    $retorna = 'sie_tramite_recepcion_departamento';
                } elseif ($identificador == 15) {
                    $retorna = 'sie_tramite_autorizacion';
                } elseif ($identificador == 16) {
                    $retorna = 'sie_tramite_impresion';
                } elseif ($identificador == 17) {
                    $retorna = 'sie_tramite_entrega_departamento';
                } elseif ($identificador == 18) {
                    $retorna = 'sie_tramite_entrega_distrito';
                } else {
                    $retorna = 'sie_tramites_homepage';
                }

                /*
                 * Verifica si la Unidad Educativa esta habilitada para la entrega de certificados
                 */

                $habilitado = $this->verificaNivelUnidadEducativa($ue);

                if ($habilitado == 0) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'La unidad educativa ' . $ue . ' no puede extender diplomas de bachiller'));
                    return $this->redirectToRoute($retorna);
                }

                /*
                 * Verifica si preciono en boton adelante o atras
                 */
                if (isset($_POST['botonAceptar'])) {
                    $flujoSeleccionado = 'Adelante';
                }
                if (isset($_POST['botonDevolver'])) {
                    $flujoSeleccionado = 'Atras';
                    $obs = $request->get('obs');
                }
                if (isset($_POST['botonAnular'])) {
                    $flujoSeleccionado = 'Anular';
                }
                if (isset($_POST['botonEntregar'])) {
                    $flujoSeleccionado = 'Finalizar';
                }

                /*
                 * Halla cantidad de registros a procesar
                 */
                $cantidadEstudiantes = sizeof($estudiantes);

                /*
                 * Verifica si el rango de serie esta disponible para la impresion de certificados 
                 */
                if ($identificador == 16 and $flujoSeleccionado == 'Adelante') {
                    $numeroSerie = $request->get('numeroSerie');
                    $numSerieIni = $numeroSerie;
                    $numSerieFin = $numeroSerie + $cantidadEstudiantes;

                    $tipoSerie = $request->get('serie');
                    $gestion = $request->get('gestion');
                    $fecha = new \DateTime($request->get('fecha'));

                    $posicionArray = 0;

                    $seriesArray[] = array();
                    for ($i = $numeroSerie; $i < ($numeroSerie + $cantidadEstudiantes); $i++) {
                        $seriesArray[$posicionArray] = $i;
                        $posicionArray = $posicionArray + 1;
                    }
                    $rangoDisponible = $this->serieRangoDisponible($cantidadEstudiantes, $seriesArray, $tipoSerie, $gestion, $departamentoUserLugar);
                    if ($rangoDisponible != "") {
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $rangoDisponible));
                        $this->session->set('save', true);
                        $this->session->set('datosBusqueda', array('sie' => $ue, 'nivel' => $nivel, 'especialidad' => $esp, 'gestion' => $gestion, 'identificador' => $identificador));
                        return $this->redirectToRoute('sie_tramite_busca_ue');
                    }
                }
                /*
                 * Recorre lista de estudiantes que ingresan su trámite y sus documentos en caso de realizar la impresion
                 */
                $countSerieArray = 0;
                foreach ($estudiantes as $estudiante) {
                    $est = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $estudiante));
                    $student = $est->getEstudianteInscripcion()->getId();
                    $tramiteId = (Int) $estudiante;
                    $periodo = '';

                    $error = $this->procesaTramite($tramiteId, $id_usuario, $flujoSeleccionado, $obs, $student, $esp, $nivel, $ue, $periodo);
                    $insertTablaAlternativa = $this->insertTramiteAlternativa($tramiteId, $student, $esp, $nivel, $periodo, $ue);

                    switch ($nivel) {
                        case '1':
                            $documentoTipo = 6;
                            break;
                        case '2':
                            $documentoTipo = 7;
                            break;
                        case '3':
                            $documentoTipo = 8;
                            break;
                    }
                    $hi = 0;
                    if ($identificador == 16 and $flujoSeleccionado == 'Adelante') {
                        $documentosGenerados[$countSerieArray] = $this->generaDocumento($tramiteId, $id_usuario, $documentoTipo, $seriesArray[$countSerieArray], $tipoSerie, $gestion, $fecha);
                        $countSerieArray = $countSerieArray + 1;
                    }

                    /*
                     * Anula documento certificado
                     */
                    if ($identificador == 17 and $flujoSeleccionado == 'Atras') {
                        $error = $this->anulaDocumento($tramiteId, $obs);
                    }
                }

                $em->getConnection()->commit();
                $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => sizeof($estudiantes) . ' Trámite(s) procesado(s)'));
                $this->session->set('save', true);
                $this->session->set('datosBusqueda', array('sie' => $ue, 'gestion' => $gestion, 'nivel' => $nivel, 'especialidad' => $esp, 'identificador' => $identificador));
            } else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Formulario enviado'));
            }
            return $this->redirectToRoute('sie_tramite_busca_ue');
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al ingresar registro, intente nuevamente'));
            return $this->redirectToRoute($retorna);
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Asigna los números de serie a los estudiantes/participantes que son seleccionados, una vez seleccionados
    // se obtiene los datos del tramite, y se los registra en la tabla "DOCUMENTO".
    // PARAMETROS: Request (sie, especialidad, gestion)
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************    
    private function generaDocumento($tramiteId, $usuarioId, $documentoTipo, $numeroSerie, $tipoSerie, $gestion, $fecha) {
        /*
         * Define la zona horaria y halla la fecha actual
         */

        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $em = $this->getDoctrine()->getManager();

        /*
         * Halla datos del tramite en el cual se trabaja
         */
        $entityTramite = $em->getRepository('SieAppWebBundle:Tramite');
        $query = $entityTramite->createQueryBuilder('t')
                ->leftJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = t.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->leftJoin('SieAppWebBundle:JurisdiccionGeografica', 'jg', 'WITH', 'jg.id = ie.leJuridicciongeografica')
                ->leftJoin('SieAppWebBundle:DistritoTipo', 'dt', 'WITH', 'dt.id = jg.distritoTipo')
                ->leftJoin('SieAppWebBundle:DepartamentoTipo', 'det', 'WITH', 'det.id = dt.departamentoTipo')
                ->where('t.id = :codTramite')
                ->setParameter('codTramite', $tramiteId)
                ->setMaxResults('1');
        $entityTramite = $query->getQuery()->getResult();

        if (count($entityTramite) > 0) {
            /*
             * Define el conjunto de valores a ingresar - Documento
             */
            $em->getConnection()->beginTransaction();
            $entityDocumentoTipo = $em->getRepository('SieAppWebBundle:DocumentoTipo')->findOneBy(array('id' => $documentoTipo));
            if ($gestion > 2014) {
                $entityDocumentoSerie = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => str_pad($numeroSerie . $tipoSerie, 10, "0", STR_PAD_LEFT)));
            } else {
                $entityDocumentoSerie = $em->getRepository('SieAppWebBundle:DocumentoSerie')->findOneBy(array('id' => $numeroSerie . $tipoSerie));
            }
            $entityDocumentoEstado = $em->getRepository('SieAppWebBundle:DocumentoEstado')->findOneBy(array('id' => 1));
            $entityDocumento = new Documento();
            $entityDocumento->setDocumento('');
            $entityDocumento->setDocumentoTipo($entityDocumentoTipo);
            $entityDocumento->setObs($entityDocumentoTipo->getDocumentoTipo() . ' generado');
            $entityDocumento->setDocumentoSerie($entityDocumentoSerie);
            $entityDocumento->setUsuarioId($usuarioId);
            $entityDocumento->setFechaImpresion($fecha);
            $entityDocumento->setFechaRegistro($fechaActual);
            $entityDocumento->setTramite($entityTramite[0]);
            $entityDocumento->setDocumentoEstado($entityDocumentoEstado);
            $em->persist($entityDocumento);
            $em->flush();
            $em->getConnection()->commit();
            return $entityDocumento->getId();
        } else {
            throw new Exception('Se ha producido un error muy grave.');
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Funcion que nos permite ver si los numeros de serie estan disponibles, esta funcion verifica en primera
    // instancia, de que no este el rango de numeros asignados en la tabla "DOCUMENTO" y que esten creados 
    // en la tabla "DOCUMENTO_SERIE"
    // PARAMETROS: $cantidadEstudiantes, $numerosSerie, $tipoSerie, $gestion, $departamentoUsuario
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************        
    private function serieRangoDisponible($cantidadEstudiantes, $numerosSerie, $tipoSerie, $gestion, $departamentoUsuario) {
        $em = $this->getDoctrine()->getManager();

        $series = '';
        $i = 0;

        foreach ($numerosSerie as $numeroSerie) {
            $i+=1;
            if ($gestion > 2014) {
                if (empty($series)) {
                    $serie[$i] = "'" . str_pad($numeroSerie . $tipoSerie, 10, "0", STR_PAD_LEFT) . "'";
                } else {
                    $serie[$i] = "'" . str_pad($numeroSerie . $tipoSerie, 10, "0", STR_PAD_LEFT) . "'";
                }
            } else {
                if (empty($series)) {
                    $serie[$i] = "'" . $numeroSerie . $tipoSerie . "'";
                } else {
                    $serie[$i] = "'" . $numeroSerie . $tipoSerie . "'";
                }
            }
        }
        $series = implode(", ", $serie);


        if ($cantidadEstudiantes > 0) {
            /*
             * Verifica si los numeros de serie no estan asignados en la tabla "DOCUMENTO" y que se
             * se encuentren registrados en la tabla "DOCUMENTO_SERIE"
             */
            $query = $em->getConnection()->prepare("
                select ds.id as id from documento_serie as ds where ds.esanulado = 'false' 
                and ds.departamento_tipo_id = " . $departamentoUsuario . " 
                and gestion_id = " . $gestion . " 
                and not exists 
                (select * from documento as d where d.documento_serie_id = ds.id) 
                and ds.id in (" . $series . ")
            ");
            $query->execute();
            $entity = $query->fetchAll();
//            var_dump(count($entity) === sizeof($numerosSerie)); die;
            if (count($entity) > 0) {
                if (count($entity) === sizeof($numerosSerie)) {
                    $return = "";
                } else {
                    $seriesNoDisponibles = "";
                    foreach ($numerosSerie as $numSer) {
                        $disponible = "";
                        $noDisponible = "";
                        for ($i = 0; $i < count($entity); $i++) {
                            if ((string) ($entity[$i]["id"]) === (string) ($numSer . $tipoSerie)) {
                                $disponible = $entity[$i]["id"];
                            }
                        }
                        if ($disponible === "") {
                            if ($seriesNoDisponibles == "") {
                                $seriesNoDisponibles = "'" . $numSer . $tipoSerie . "'";
                            } else {
                                $seriesNoDisponibles = $seriesNoDisponibles . ",'" . $numSer . $tipoSerie . "'";
                            }
                        }
                    }
                    $this->session->set('save', false);
                    $return = "De los número de serie solicitados (" . $series . "), no se ecuentra disponible el número de serie (" . $seriesNoDisponibles . ")";
                }
            } else {
                $this->session->set('save', false);
                $return = "No tiene asignado los Números de Serie (" . $series . ") en la gestión " . $gestion . " o el numero de serie ya fue utilizada";
            }
        } else {
            $this->session->set('save', false);
            $return = "No seleccionaron Estudiantes/Participantes, favor de intentar nuevamente";
        }
        return $return;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Funcion que nos permite ANULAR un tramite que se le genero un documento de serie, 
    // PARAMETROS: $cantidadEstudiantes, $numerosSerie, $tipoSerie, $gestion, $departamentoUsuario
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************    
    private function anulaDocumento($tramiteId, $obs) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d H:i:s'));
        $em = $this->getDoctrine()->getManager();

        /*
         * Halla datos del tramite en el cual se trabaja
         */
        $entityTramite = $em->getRepository('SieAppWebBundle:Documento');
        $query = $entityTramite->createQueryBuilder('d')
                ->where('d.tramite = :codTramite')
                ->andWhere('d.documentoTipo = :codTramiteTipo')
                ->orderBy('d.id', 'desc')
                ->setParameter('codTramite', $tramiteId)
                ->setParameter('codTramiteTipo', 1)
                ->setMaxResults('1');
        $entityDocumento = $query->getQuery()->getResult();
        if (count($entityDocumento) > 0) {
            /*
             * Actualiza el estado del documento a "Anulado"
             */
            $obsEntity = (' Documento anulado por ' . $obs . ' el ' . date_format($fechaActual, "Y/m/d - H:i:s") . '');
            $entityDocumentoEstado = $em->getRepository('SieAppWebBundle:DocumentoEstado')->findOneBy(array('id' => 2));
            $entityDocumento[0]->setDocumentoEstado($entityDocumentoEstado);
            $entityDocumento[0]->setObs($obsEntity);
            $em->persist($entityDocumento[0]);
            $em->flush();
            $return = "";
        } else {
            $return = "Trámite no encontrado, intente nuevamente";
        }
        return $return;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Funcion que nos permite Generar el certificado Técnico Básico y Técnico Auxiliar en formato PDF, en 
    // este metodo obtiene el departamento del centro de educacion alternativa, y es enviado como variable
    // para generacion del pdf
    // PARAMETROS: gestion, sie, nivel, especialidad, identificador
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************      
    public function impresionCertificadoPdfAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        /*
         * Recupera datos del formulario
         */
        $fecha = $this->session->get('fecha_registro');
        $gestion = $request->get('gestion');
        $ue = $request->get('sie');
        $nivel = $request->get('nivel');
        $especialidad = $request->get('especialidad');
        $identificador = $request->get('identificador');
        $form = $request->get('form');
        if ($form) {
            $ue = $form['sie'];
            $nivel = $form['nivel'];
            $gestion = $form['gestion'];
            $identificador = $form['identificador'];
            $especialidad = $form['especialidad'];
        } else {
            $tipoImpresion = 1;
        }


        /*
         * Halla el departamento de la Unidad Educativa
         */
        $query = $em->getConnection()->prepare("
                select ie.id, lt2.lugar departamento, lt2.id
                from institucioneducativa as ie
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join lugar_tipo lt1 ON lt1.id = jg.lugar_tipo_id_distrito
                inner join lugar_tipo lt2 on lt2.id = lt1.lugar_tipo_id
                where ie.id=  :sie::INT       
                ");
        $query->bindValue(':sie', $ue);
        $query->execute();
        $entityDepto = $query->fetchAll();
        $query = $em->getConnection()->prepare("
                select id, lugar 
                from lugar_tipo 
                where lugar = '" . $entityDepto[0]['departamento'] . "' and "
                . "id = (select min(id) "
                . "from lugar_tipo "
                . "where lugar = '" . $entityDepto[0]['departamento'] . "'  ) 
                    group by id, lugar 
                ");
        $query->execute();
        $entityD = $query->fetchAll();
        $depto = $entityD[0]['id'];

        switch ($nivel) {
            case 1:
                $n = 6;
                break;
            case 2:
                $n = 7;
                break;
            case 3:
                $n = 8;
                break;
        }
        $arch = $ue . '_' . $gestion . '_CERTIFICADOS_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_cert_estudiante_tec_basico_tec_auxiliar.rptdesign&sie=' . $ue . '&gestion=' . $gestion . '&esp=' . $especialidad . '&nivel=' . $n . '&departamento=' . $depto . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Funcion que nos permite Generar el certificado Técnico Básico y Técnico Auxiliar en formato PDF, en 
    // este metodo se obtiene el departamento del centro de educacion alternativa, en este reporte se generará
    // con el carnet de identidad.
    // PARAMETROS: gestion, sie, nivel, especialidad, identificador
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************    
    public function impresionCertificadoPdfCIAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        /*
         * Recupera datos del formulario
         */
        $fecha = $this->session->get('fecha_registro');
        $gestion = $request->get('gestion');
        $ue = $request->get('sie');
        $nivel = $request->get('nivel');
        $especialidad = $request->get('especialidad');
        $identificador = $request->get('identificador');
        $form = $request->get('form');
        if ($form) {
            $ue = $form['sie'];
            $nivel = $form['nivel'];
            $gestion = $form['gestion'];
            $identificador = $form['identificador'];
            $especialidad = $form['especialidad'];
        } else {
            $tipoImpresion = 1;
        }
        /*
         * Halla el departamento de la Unidad Educativa
         */
        $query = $em->getConnection()->prepare("
                select ie.id, lt2.lugar departamento, lt2.id
                from institucioneducativa as ie
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join lugar_tipo lt1 ON lt1.id = jg.lugar_tipo_id_distrito
                inner join lugar_tipo lt2 on lt2.id = lt1.lugar_tipo_id
                where ie.id=  :sie::INT       
                ");
        $query->bindValue(':sie', $ue);
        $query->execute();
        $entityDepto = $query->fetchAll();
        $query = $em->getConnection()->prepare("
                select id, lugar from lugar_tipo where lugar = '" . $entityDepto[0]['departamento'] . "' and id = (select min(id) from lugar_tipo where lugar = '" . $entityDepto[0]['departamento'] . "'  ) group by id, lugar 
                ");
        $query->execute();
        $entityD = $query->fetchAll();
        $depto = $entityD[0]['id'];

        switch ($nivel) {
            case 1:
                $n = 6;
                break;
            case 2:
                $n = 7;
                break;
            case 3:
                $n = 8;
                break;
        }

        $arch = $ue . '_' . $gestion . '_CERTIFICADOS_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_tec_cert_estudiante_tec_basico_tec_auxiliar_con_ci.rptdesign&sie=' . $ue . '&gestion=' . $gestion . '&esp=' . $especialidad . '&nivel=' . $n . '&departamento=' . $depto . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Metodo que nos enviara a la vista "generarSeries.html.twig", enviando el formulario creado, el titulo
    // y el subtitulo de la vista.
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function generarSeriesCertificadosAction(Request $request) {

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //valida si el usuario se encuentra logueado
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieTramitesBundle:Tramite:generarSeries.html.twig', array(
                    'titulo' => 'Generador de Número de Series'
                    , 'subtitulo' => 'Certificación Técnica'
                    , 'form' => $this->crearFormulario('tramite_verificar_generar', '', '', '')->createView()
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Metodo que crea el formulario para la generacion de numeros de serie
    // PARAMETROS: routing (ruta del formulario)
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function crearFormulario($routing, $value1, $value2, $value3) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('select gestion, serie from series_gestion a where cast(a.gestion as int) <= cast(extract(year from now()) as int) ORDER BY a.gestion asc');
        $query->execute();
        $arraySerie = $query->fetchAll();
        $c = 0;
        foreach ($arraySerie as $value) {

            $dato[$value['serie']] = $value['serie'];
        }
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('num_inicial', 'text', array('label' => 'Número Inicial', 'attr' => array('placeholder' => 'Número Inicial', 'class' => 'form-control', 'pattern' => '[0-9]{1,8}', 'maxlength' => '8', 'autocomplete' => 'off')))
                ->add('num_final', 'text', array('label' => 'Número Final', 'attr' => array('placeholder' => 'Número Final', 'class' => 'form-control', 'pattern' => '[0-9]{1,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'onblur' => 'num_mayor()')))
                ->add('series', 'choice', array('label' => 'Series', 'empty_value' => 'Series',
                    'choices' => $dato,
                ))
                ->add('gestiones', 'entity', array('empty_value' => 'Gestión',
                    'attr' => array('class' => 'form-control', 'value' => $value1), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('gt')
                        ->where('gt.id > 2012')
                        ->orderBy('gt.id', 'DESC');
            },
                ))
                ->add('departamento', 'choice', array('empty_value' => 'Departamento', 'label' => 'Departamento',
                    'choices' => array('1' => 'Chuquisaca',
                        '2' => 'La Paz',
                        '3' => 'Cochabamba',
                        '4' => 'Oruro',
                        '5' => 'Potosi',
                        '6' => 'Tarija',
                        '7' => 'Santa Cruz',
                        '8' => 'Beni',
                        '9' => 'Pando'),
                ))
                ->add('niveles', 'choice', array('label' => 'nivel', 'empty_value' => 'Nivel', 'choices' => array('1' => 'Técnico Básico', '2' => 'Técnico Auxiliar'), 'attr' => array('class' => 'form-control', 'onchange' => 'verificarSelect()')))
                ->add('verificarBtn', 'submit', array('label' => ' Verificar y Generar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();

        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Metodo que nos permite verificar si los numeros de serie estan creados en la tabla "Documento_serie"
    // en caso de que no se los creara
    // PARAMETROS: identificador= 1(busqueda por número trámite, identificador = 0 (Busqueda por código RUDEAL))
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function verificarGenerarAction(Request $request) {
        $form = $request->get('form');
        $ini = $form['num_inicial'];
        $fin = $form['num_final'];
        $c = 0;
        $em = $this->getDoctrine()->getManager();
        for ($x = $ini; $x <= $fin; $x++) {
            $c+=1;
            $arraySerie[$c] = $x;
        }
        $tipoSerie = $form['series'];
        $gestion = $form['gestiones'];
        $departamento = $form['departamento'];
        $nivel = $form['niveles'];
        switch ($nivel) {
            case 1:
                $n = 'Técnico Básico';
                break;
            case 2:
                $n = 'Técnico Auxiliar';
                break;
            case 3:
                $n = 'Técnico Medio';
                break;
        }
        //Se verifica la disponibilidad de los numeros de serie, se verifica en las tablas documento y documento_serie.
        $disponibles = $this->verificarDisponibleSerie($arraySerie, $tipoSerie, $gestion, $departamento);
        if ($c == sizeof($disponibles)) {
            $f = 0;
            $entity = $em->getConnection()->prepare('SELECT departamento lugar  FROM departamento_tipo depto WHERE depto.id = ' . $departamento);
            $entity->execute();
            $entityDepto = $entity->fetchAll();
            foreach ($disponibles as $d) {
                $entityGestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestion));
                $d = trim(str_replace("'", " ", $d));
                try {

                    $query = $em->getConnection()->prepare('INSERT INTO documento_serie(id, gestion_id, departamento_tipo_id, esanulado, observacion_anulado, obs)'
                            . 'VALUES(:id, :gestion,:departamento,:esanulado,:observacion, :obs)');
                    $query->bindValue(':id', $d);
                    $query->bindValue(':gestion', $gestion);
                    $query->bindValue(':departamento', $departamento); //departamento introducido por formulario
                    $query->bindValue(':esanulado', 'f');
                    $query->bindValue(':observacion', 'false');
                    $query->bindValue(':obs', (string) $request->get('form')['niveles']);
                    $query->execute();
                } catch (Exception $e) {
//                    $em->getConnection()->rollback();
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Este valor ya fue registrado anteriormente!!'));


                    return $this->render('SieTramitesBundle:Tramite:generarSeriesResultado.html.twig', array(
                                'titulo' => 'Generador de Número de Series'
                                , 'subtitulo' => 'Certificación Técnica'
                                , 'disponibles' => ''
//                                , 'departamento' => $entityDepto[0]['lugar']//generado deacuerdo al formulario con la tabla lugar tipo
                                , 'form' => $this->crearFormulario('tramite_verificar_generar', '', '', '')->createView()));
                }
            }
            $this->session->getFlashBag()->set('success', array('title' => 'Exito!!', 'message' => 'Se generaron todos los Números de Serie correctamente. '));
            $sesion = $request->getSession();
            $id_usuario = $sesion->get('userId');
            $gestionActual = new \DateTime("Y");
            $this->session->set('save', false);
            if (!isset($id_usuario)) {
                return $this->redirect($this->generateUrl('login'));
            }
            $f = 0;
            foreach ($disponibles as $d) {
                $f+=1;
                $disp[$f] = array('id' => $f, 'series' => $d, 'gestion' => $gestion, 'depto' => $entityDepto[0]['lugar'], 'obs' => $n);
            }
            return $this->render('SieTramitesBundle:Tramite:generarSeriesResultado.html.twig', array(
                        'titulo' => 'Generador de Número de Series'
                        , 'subtitulo' => 'Certificación Técnica'
                        , 'disponibles' => $disp
//                        , 'departamento' => ''
                        , 'form' => $this->crearFormulario('tramite_verificar_generar', '', '', '')->createView()));
        } else {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getConnection()->prepare('SELECT departamento lugar FROM departamento_tipo depto WHERE depto.id = ' . $departamento);
            $entity->execute();
            $entityDepto = $entity->fetchAll();
            if (sizeof($disponibles) == 0) {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Estos números de serie se encuentran ya registrados!! '));
                return $this->render('SieTramitesBundle:Tramite:generarSeriesResultado.html.twig', array(
                            'titulo' => 'Generador de Número de Series'
                            , 'subtitulo' => 'Certificación Técnica'
                            , 'disponibles' => ''
                            , 'form' => $this->crearFormulario('tramite_verificar_generar', '', '', '')->createView()));
            }
            $f = 0;

            foreach ($disponibles as $d) {
                $f+=1;
                $disp[$f] = array('id' => $f, 'series' => $d, 'gestion' => $gestion, 'depto' => $entityDepto[0]['lugar'], 'obs' => $n);
            }
            foreach ($disponibles as $d) {
                $entityGestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestion));
                $d = trim(str_replace("'", " ", $d));
                try {
                    $query = $em->getConnection()->prepare('INSERT INTO documento_serie(id, gestion_id, departamento_tipo_id, esanulado, observacion_anulado, obs)'
                            . 'VALUES(:id, :gestion,:departamento,:esanulado,:observacion, :obs)');
                    $query->bindValue(':id', $d);
                    $query->bindValue(':gestion', $gestion);
                    $query->bindValue(':departamento', $departamento);
                    $query->bindValue(':esanulado', 'f');
                    $query->bindValue(':observacion', 'false');
                    $query->bindValue(':obs', (string) $request->get('form')['niveles']);
                    $query->execute();
                } catch (Exception $e) {
                    $em->getConnection()->rollback();
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Este valor ya fue registrado anteriormente!!'));
                    return $this->render('SieTramitesBundle:Tramite:generarSeriesResultado.html.twig', array(
                                'titulo' => 'Generador de Número de Series'
                                , 'subtitulo' => 'Certificación Técnica'
                                , 'disponibles' => ''
                                , 'form' => $this->crearFormulario('tramite_verificar_generar', '', '', '')->createView()));
                }
            }
            return $this->render('SieTramitesBundle:Tramite:generarSeriesResultado.html.twig', array(
                        'titulo' => 'Generador de Número de Series'
                        , 'subtitulo' => 'Certificación Técnica'
                        , 'disponibles' => $disp
                        , 'form' => $this->crearFormulario('tramite_verificar_generar', '', '', '')->createView()));
        }
    }

    public function verificarDisponibleSerie($arraySerie = null, $tipoSerie = null, $gestion = null, $departamento = null) {
        $em = $this->getDoctrine()->getManager();
        $series = '';

        $i = 0;
        foreach ($arraySerie as $numeroSerie) {
            $i+=1;
            if ($gestion > 2014) {
                if (empty($series)) {
                    $serie[$i] = "'" . str_pad($numeroSerie . $tipoSerie, 10, "0", STR_PAD_LEFT) . "'";
                } else {
                    $serie[$i] = "'" . str_pad($numeroSerie . $tipoSerie, 10, "0", STR_PAD_LEFT) . "'";
                }
            } else {
                if (empty($series)) {
                    $serie[$i] = "'" . $numeroSerie . $tipoSerie . "'";
                } else {
                    $serie[$i] = "'" . $numeroSerie . $tipoSerie . "'";
                }
            }
        }

        /*
         * Verifica si todos los series estan disponibles
         */
        $sArray = $serie;

        $c = 0;
        $arraySer = array();
        foreach ($sArray as $numeroSerie) {
            $query = $em->getConnection()->prepare("
                select * from 
                documento_serie 
                where
                id like " . $numeroSerie . "
                and gestion_id = :gestion
                and departamento_tipo_id = :depto
            ");
            $query->bindValue(':gestion', $gestion);
            $query->bindValue(':depto', $departamento);
            $query->execute();
            $entity = $query->fetchAll();

            if (sizeof($entity) == 0) {
                $c+=1;
                $arraySer[$c] = $numeroSerie;
            }
        }
        if (sizeof($arraySer) > 0) {
            $return = $arraySer;
        } else {
            $return = null;
        }
        return $return;
    }

    public function verificarDisponible($arraySerie = null, $tipoSerie = null, $gestion = null, $departamento = null) {
        $em = $this->getDoctrine()->getManager();
        $series = '';

        $i = 0;
        foreach ($arraySerie as $numeroSerie) {
            $i+=1;
            if ($gestion > 2014) {
                if (empty($series)) {
                    $serie[$i] = "'" . str_pad($numeroSerie . $tipoSerie, 10, "0", STR_PAD_LEFT) . "'";
                } else {
                    $serie[$i] = "'" . str_pad($numeroSerie . $tipoSerie, 10, "0", STR_PAD_LEFT) . "'";
                }
            } else {
                if (empty($series)) {
                    $serie[$i] = "'" . $numeroSerie . $tipoSerie . "'";
                } else {
                    $serie[$i] = "'" . $numeroSerie . $tipoSerie . "'";
                }
            }
        }

        /*
         * Verifica si todos los series estan disponibles
         */
        $sArray = $serie;
        $c = 0;
        $arraySer = array();
        foreach ($sArray as $numeroSerie) {
            $query = $em->getConnection()->prepare("
                select * from 
                documento_serie 
                where
                id like " . $numeroSerie . "
                and gestion_id = :gestion
                and departamento_tipo_id = :depto
            ");
            $query->bindValue(':gestion', $gestion);
            $query->bindValue(':depto', $departamento);
            $query->execute();
            $entity = $query->fetchAll();
            if (sizeof($entity) == 0) {
                $c+=1;
                $arraySer[$c] = $numeroSerie;
            }
        }
        if (sizeof($arraySer) > 0) {
            $return = $arraySer;
        }
//        else {
//            $this->session->set('save', false);
//            $return = "No tiene asignado los Números de Serie (" . $series . ") en la gestión " . $gestion . " o el numero de serie ya fue utilizada";
//        }

        return $return;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Metodo que obtiene las especialidades del sub sistema de Educacion Alternativa
    // PARAMETROS: por post "id"
    // returna objeto json 
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function listarHEspAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $queryEntidad = $em->getConnection()->prepare(
                    'SELECT
                    a.id id, a.especialidad esp
                    FROM
                    superior_especialidad_tipo a
                    INNER JOIN superior_facultad_area_tipo b ON a.superior_facultad_area_tipo_id = b.id
                    INNER JOIN superior_acreditacion_especialidad c ON c.superior_especialidad_tipo_id = a.id
                    WHERE
                    b.id  in (16,17,18,19,20,21,22,23,38) and
                    b.id = :id and a.especialidad IS NOT NULL
                    GROUP BY
                    a.id, a.especialidad
                    ORDER BY 
                    a.especialidad
                    ');
            $queryEntidad->bindValue(':id', $_POST['id']);
            $queryEntidad->execute();
            $especialidad = $queryEntidad->fetchAll();
            $response = new JsonResponse();
            return $response->setData(array('especialidades' => $especialidad));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function listarUEAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $queryEntidad = $em->getConnection()->prepare(
                    '
                        SELECT * 
                        FROM institucioneducativa a
                        WHERE a.id = :sie
                    ');
            $queryEntidad->bindValue(':sie', $_POST['sies']);
            $queryEntidad->execute();
            $sie = $queryEntidad->fetchAll();
            $response = new JsonResponse();
            return $response->setData(array('ue' => $sie));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function verificarStudentAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare(
                '
                        SELECT * 
                        FROM estudiante a
                        WHERE a.codigo_rude = :rudeal
                    ');
        $queryEntidad->bindValue(':rudeal', $request->get('rudeal'));
        $queryEntidad->execute();
        $sie = $queryEntidad->fetchAll();
        $response = new JsonResponse();
        return $response->setData(array('students' => $sie));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Metodo que nos permite insertar datos de estudiantes/participantes que culminaron un nivel completo
    // 
    // PARAMETROS: identificador= 1(busqueda por número trámite, identificador = 0 (Busqueda por código RUDEAL))
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function GuardarGestionAnteriorAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $queryId = $em->getConnection()->prepare('SELECT MAX(id) id FROM homologacion');
        $queryId->execute();
        $id = $queryId->fetchAll();
        $ids = 0;

        if (sizeof($id) == 0) {
            $ids = 1;
        } else {
            $ids = $id[0]['id'] + 1;
        }
        /*
         * se obtiene los datos del estudiante de acuerdo al RUDEAL
         */
        $queryEntidad = $em->getConnection()->prepare(
                '
                        SELECT * 
                        FROM estudiante a
                        WHERE a.codigo_rude = :rudeal
                    ');
        $queryEntidad->bindValue(':rudeal', $request->get('form')['rudeal']);
        $queryEntidad->execute();
        $estudiante_id = $queryEntidad->fetchAll();
        $estudiante_id = $estudiante_id[0]['id'];
        $nombre_ue = $request->get('nombre_ue');
        switch ($request->get('form')['grado']) {
            case '1':
                $cargaHoraria = 800;
                break;
            case '2':
                $cargaHoraria = 1200;
                Break;
        }
        $queryVerificar = $em->getConnection()->prepare('SELECT * FROM homologacion WHERE rudeal = :rudeal AND grado_id = :grado AND nro_certificado =:certificado');
        $queryVerificar->bindValue(':rudeal', $request->get('form')['rudeal']);
        $queryVerificar->bindValue(':grado', (int) $request->get('form')['grado']);
        $queryVerificar->bindValue(':certificado', $request->get('form')['certificado']);
        $queryVerificar->execute();
        $ver = $queryVerificar->fetchAll();
        if (sizeof($ver) == 0) {
            $query = $em->getConnection()->prepare('
            INSERT INTO homologacion(institucioneducativa_id, institucioneducativa, gestion_id, rudeal, nivel_id, ciclo_id, grado_id, carga_horaria, nro_certificado, estudiante_id, usuario_id, fecha_reg)
            VALUES(:sie, :ue, :gestion, :rudeal, :nivel_id, :ciclo, :grado, :carga_horaria, :certificado, :estudiante, :usuario, :fecha)
                ');



            $query->bindValue(':sie', (int) $request->get('form')['sies']);
            $query->bindValue(':ue', $nombre_ue);
            $query->bindValue(':gestion', (int) $request->get('form')['gestiones']);
            $query->bindValue(':rudeal', $request->get('form')['rudeal']);
            $query->bindValue(':grado', (int) $request->get('form')['grado']);
            $query->bindValue(':nivel_id', (int) $request->get('form')['nivel']);
            $query->bindValue(':ciclo', (int) $request->get('form')['especialidad']);
            $query->bindValue(':carga_horaria', (int) $cargaHoraria);
            $query->bindValue(':estudiante', (int) $estudiante_id);
            $query->bindValue(':certificado', $request->get('form')['certificado']);
            $query->bindValue(':usuario', $id_usuario);
            $query->bindValue(':fecha', date('Y-m-d'));
            $query->execute();
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('success', array('title' => 'Exito!!', 'message' => 'Se Registro Correctamente'));
            return $this->redirectToRoute('sie_tramite_homologacion');
            return $this->redirect($this->generateUrl('sie_tramite_homologacion'));
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Estos datos ya se encuentran Registrados'));
            return $this->redirectToRoute('sie_tramite_homologacion');
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Metodo que redirecciona a la vista que nos mostrara el formulario para registrar las gestiones anteriores
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function busquedaCertificadoAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieTramitesBundle:Tramite:BusquedaTramiteCertificado.html.twig', array(
                    'form' => $this->creaFormularioBuscadorSeguimiento('sie_tramite_seguimiento_busqueda_detalle', '', '', 1)->createView()
                    , 'titulo' => 'Búsqueda'
                    , 'subtitulo' => 'Certificación Técnica'
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Metodo que nos permite obtener los datos a detalle del trámite que se busca, mostrandonos la instancia 
    // en la que se encuentra, mediante el id del tramite
    // PARAMETROS: identificador= 1(busqueda por número trámite, identificador = 0 (Busqueda por código RUDEAL))
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function busquedaCertificadoDetalleAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $form = $request->get('form');
        if ($form) {
            $tramite = $form['tramite'];
            $identificador = $form['identificador'];
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare("SELECT DISTINCT t.id tramite_id, e.paterno, e.materno, e.nombre, e.codigo_rude,
                                CASE
                                        WHEN e.complemento = '' THEN e.carnet_identidad ELSE e.carnet_identidad || '-' || e.complemento
                                END carnet_identidad, e.fecha_nacimiento, depto_tipo.departamento depto_nacimiento,
                                CASE 
                                        WHEN ta.nivel = 1 THEN 'Técnico Básico'
                                        WHEN ta.nivel = 2 THEN 'Técnico Auxiliar'
                                        WHEN ta.nivel = 3 THEN 'Técnico Medio'
                                END grado,
                                se.id especialidad_id, se.especialidad, ie.institucioneducativa centro, ie.id sie,
                                p.nombre || ' ' || p.paterno || ' ' || p.materno usuario_remitente, ft.flujo flujo_tramite,
                                CASE 
                                        WHEN td.flujo_proceso_id = 15  THEN 'Bandeja Distrito'
                                        WHEN td.flujo_proceso_id = 16  THEN 'Bandeja Recepción Departamento'
                                        WHEN td.flujo_proceso_id = 17  THEN 'Bandeja Autorización'
                                        WHEN td.flujo_proceso_id = 18  THEN 'Bandeja Impresión'
                                        WHEN td.flujo_proceso_id = 19  THEN 'Certificado Impreso'
                                        WHEN td.flujo_proceso_id = 21  THEN 'Bandeja Entrega Distrito'
                                END proceso, td.flujo_proceso_id, td.tramite_estado_id, td.obs comentario, td.fecha_modificacion fecha_envio
                                FROM tramite t 
                                inner join  tramite_alternativa ta on ta.tramite_id = t.id
                                inner join tramite_detalle td on td.tramite_id = t.id 
                                inner join estudiante e on e.id = ta.estudiante
                                inner join superior_especialidad_tipo se On se.id = ta.esp
                                inner join institucioneducativa ie on ie.id = ta.institucioneducativa
                                inner join jurisdiccion_geografica jg on jg.id = ie.le_juridicciongeografica_id
                                inner join distrito_tipo dt on dt.id = jg.distrito_tipo_id
                                inner join departamento_tipo depto_tipo on depto_tipo.id = dt.departamento_tipo_id
                                inner join usuario u on u.id = td.usuario_remitente_id
                                inner join persona p on u.persona_id = p.id
                                inner join flujo_tipo ft on ft.id = t.flujo_tipo_id
                                where 
                                        t.id = :tramiteId
                                order by td.flujo_proceso_id, e.paterno, e.materno, e.nombre ASC");
            $query->bindValue(':tramiteId', $tramite);
            $query->execute();
            $entity = $query->fetchAll();
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar la busqueda, intente nuevamente'));
            return $this->redirectToRoute('sie_tramite_seguimiento_busqueda');
        }
        return $this->render('SieTramitesBundle:Tramite:BusquedaTramiteCertificado.html.twig', array(
                    'form' => $this->creaFormularioBuscadorSeguimiento('sie_tramite_seguimiento_busqueda', '', '', 1)->createView()
                    , 'entities' => $entity
                    , 'titulo' => 'Seguimiento'
                    , 'subtitulo' => 'Certificación Técnica'
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Metodo que nos permite obtener los datos a detalle del trámite que se busca, mostrandonos la instancia 
    // en la que se encuentra mediante el código RUDEAL
    // PARAMETROS: identificador= 1(busqueda por número trámite, identificador = 0 (Busqueda por código RUDEAL))
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************
    public function busquedaCertificadoRudeDetalleAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $form = $request->get('form');
        if ($form) {
            $rude = $form['tramite'];
            $identificador = $form['identificador'];
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare("SELECT DISTINCT t.id tramite_id, e.paterno, e.materno, e.nombre, e.codigo_rude,
                                CASE
                                        WHEN e.complemento = '' THEN e.carnet_identidad ELSE e.carnet_identidad || '-' || e.complemento
                                END carnet_identidad, e.fecha_nacimiento, depto_tipo.departamento depto_nacimiento,
                                CASE 
                                        WHEN ta.nivel = 1 THEN 'Técnico Básico'
                                        WHEN ta.nivel = 2 THEN 'Técnico Auxiliar'
                                        WHEN ta.nivel = 3 THEN 'Técnico Medio'
                                END grado,
                                se.id especialidad_id, se.especialidad, ie.institucioneducativa centro, ie.id sie,
                                p.nombre || ' ' || p.paterno || ' ' || p.materno usuario_remitente, ft.flujo flujo_tramite,
                                CASE 
                                        WHEN td.flujo_proceso_id = 15  THEN 'Bandeja Distrito'
                                        WHEN td.flujo_proceso_id = 16  THEN 'Bandeja Recepción Departamento'
                                        WHEN td.flujo_proceso_id = 17  THEN 'Bandeja Autorización'
                                        WHEN td.flujo_proceso_id = 18  THEN 'Bandeja Impresión'
                                        WHEN td.flujo_proceso_id = 19  THEN 'Certificado Impreso'
                                        WHEN td.flujo_proceso_id = 21  THEN 'Bandeja Entrega Distrito'
                                END proceso, td.flujo_proceso_id, td.tramite_estado_id, td.obs comentario, td.fecha_modificacion fecha_envio
                                FROM tramite t 
                                inner join  tramite_alternativa ta on ta.tramite_id = t.id
                                inner join tramite_detalle td on td.tramite_id = t.id 
                                inner join estudiante e on e.id = ta.estudiante
                                inner join superior_especialidad_tipo se On se.id = ta.esp
                                inner join institucioneducativa ie on ie.id = ta.institucioneducativa
                                inner join jurisdiccion_geografica jg on jg.id = ie.le_juridicciongeografica_id
                                inner join distrito_tipo dt on dt.id = jg.distrito_tipo_id
                                inner join departamento_tipo depto_tipo on depto_tipo.id = dt.departamento_tipo_id
                                inner join usuario u on u.id = td.usuario_remitente_id
                                inner join persona p on u.persona_id = p.id
                                inner join flujo_tipo ft on ft.id = t.flujo_tipo_id
                                where 
                                        e.codigo_rude = '" . $rude . "'
                                order by td.flujo_proceso_id, e.paterno, e.materno, e.nombre ASC");
            $query->execute();
            $entity = $query->fetchAll();
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar la busqueda, intente nuevamente'));
            return $this->redirectToRoute('sie_tramite_seguimiento_busqueda');
        }
        return $this->render('SieTramitesBundle:Tramite:BusquedaTramiteCertificado.html.twig', array(
                    'form' => $this->creaFormularioBuscadorSeguimiento('sie_tramite_seguimiento_busqueda_rude_detalle', '', '', 2)->createView()
                    , 'entities' => $entity
                    , 'titulo' => 'Seguimiento'
                    , 'subtitulo' => 'Certificación Técnica'
        ));
    }
    
    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Metodo que redirecciona a la vista que nos mostrara el formulario para la busqueda del trámite mediante
    // el código RUDEAL
    // FECHA DE ACTUALIZACION:  31-01-2017
    // AUTOR: PMEAVE
    //****************************************************************************************************

    public function busquedaCertificadoRudeAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieTramitesBundle:Tramite:BusquedaTramiteCertificado.html.twig', array(
                    'form' => $this->creaFormularioBuscadorSeguimiento('sie_tramite_seguimiento_busqueda_rude_detalle', '', '', 2)->createView()
                    , 'titulo' => 'Búsqueda'
                    , 'subtitulo' => 'Certificación Técnica'
        ));
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//    RECEPCION DISTRITO - IDENTIFICADOR 13
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function recepcionDistritoAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 13)->createView()
                    , 'titulo' => 'Recepción Distrito'
                    , 'subtitulo' => 'Certificación Técnica'
        ));
        //return $this->render('SieDiplomaBundle:Proceso:recepcionDistrito.html.twig');
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//// RECIBIR DEPARTAMENTO - IDENTIFICADOR 14
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function recepcionDepartamentoAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 14)->createView()
                    , 'titulo' => 'Recepción Departamento'
                    , 'subtitulo' => 'Certificación Técnica'
        ));
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// AUTORIZACION TRAMITE - IDENTIFICADOR 15
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function autorizacionTramiteAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 15)->createView()
                    , 'titulo' => 'Autorización'
                    , 'subtitulo' => 'Certificación Técnica'
        ));
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPRESION CERTIFICADOS - IDENTIFICADOR 16
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function impresionCertificadoAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 16)->createView()
                    , 'titulo' => 'Impresión'
                    , 'subtitulo' => 'Certificación Técnica'
        ));
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ENTREGA DOCUMENTO - IDENTIFICADOR 17
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function entregaDepartamentoAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 17)->createView()
                    , 'titulo' => 'Entrega Departamento'
                    , 'subtitulo' => 'Certificación Técnica'
        ));
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ENTREGA DOCUMENTO - IDENTIFICADOR 18
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function entregaDistritoAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieTramitesBundle:Tramite:ListaEstudiantesUnidadEducativa.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 17)->createView()
                    , 'titulo' => 'Entrega Distrito'
                    , 'subtitulo' => 'Certificación Técnica'
        ));
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPRESIÓN LISTADO - IDENTIFICADOR 0
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function impresionListadoAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieTramitesBundle:Tramite:ListaTramitesImpresion.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_tramite_busca_ue', '', $gestionActual, '', '', 0)->createView()
                    , 'titulo' => 'Impresión Listado'
                    , 'subtitulo' => 'Certificación Técnica'
        ));
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPRESIÓN LISTADO - IDENTIFICADOR 0
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function homologacionAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieTramitesBundle:Tramite:GestionesAnteriores.html.twig', array(
                    'form' => $this->creaFormularioBuscador('sie_homologacion_guardar', '', $gestionActual, '', '', 1)->createView()
                    , 'titulo' => 'Registro Gestiones Anteriores'
                    , 'subtitulo' => 'Certificación Técnica'
        ));
    }

    public function datosCertificadoAction(Request $request) {
        $tramites = $request->get('tramite');
        $gestionActual = new \DateTime("Y");

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
                        select initcap(e.nombre) nombre, initcap(e.paterno)paterno, initcap(e.materno) materno, e.codigo_rude,
                        CASE 
                                        WHEN e.complemento = '' or e.complemento = null THEN e.carnet_identidad ELSE e.carnet_identidad||'-'||e.complemento
                        END carnet_identidad, e.fecha_nacimiento, t.id tramite, d.id serie, se.especialidad,
                        CASE 
                                        WHEN ta.nivel = 1 THEN 'Técnico Básico'
                                        WHEN ta.nivel = 2 THEN 'Técnico Auxiliar'
                                        WHEN ta.nivel = 3 THEN 'Técnico Medio'
                        END nivel, ie.id sie, upper(ie.institucioneducativa) centro, t.gestion_id
                        from tramite t 
                        inner join tramite_alternativa ta on ta.tramite_id = t.id
                        inner join tramite_detalle td on td.tramite_id = t.id
                        inner join documento d on d.tramite_id = t.id
                        inner join estudiante e on e.id = ta.estudiante
                        inner join superior_especialidad_tipo se on se.id = ta.esp
                        inner join institucioneducativa ie on ie.id = ta.institucioneducativa
                        where td.flujo_proceso_id = 19 and td.tramite_estado_id = 1 and t.id = '" . $tramites . "'");
        $query->execute();
        $entity = $query->fetchAll();

        return $this->render('SieTramitesBundle:Tramite:DatosEstudiante.html.twig', array(
                    'titulo' => 'Datos Certificado'
                    , 'subtitulo' => 'Estudiante/Participante'
                    , 'datos' => $entity
        ));
    }

}
