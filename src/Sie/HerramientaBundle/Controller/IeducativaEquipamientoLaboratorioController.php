<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\EquipLaboFisiQuim;
use Sie\AppWebBundle\Entity\EquipLaboFisiQuimFotos;
use Symfony\Component\HttpFoundation\Response;

/**
 * Malla Curricular controller.
 *
 */
class IeducativaEquipamientoLaboratorioController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {    
        $sesion = $request->getSession();
        //dump($sesion->get('ie_id'));die; 
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $sie = "";

        return $this->render($this->session->get('pathSystem') . ':IeducativaEquipamientoLaboratorio:index.html.twig', array(
            'form' => $this->creaFormularioBusqueda('herramienta_ieducativa_equipamiento_laboratorio_detalle',$sie)->createView()
        ));
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    public function detalleAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = (int)$fechaActual->format('Y');

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $request->get('form');
        // dump($form['sie']);die;
        $sie = 0;

        if(!$form){
            $sie = $sesion->get('ie_id');
            // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            // return $this->redirect($this->generateUrl('herramienta_ieducativa_equipamiento_laboratorio_index'));
        } else {
            $sie = $form['sie'];
        }

        /*
        * verificamos si tiene tuicion
        */
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $sie);
        $query->bindValue(':rolId', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();

        if (!$aTuicion[0]['get_ue_tuicion']) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No tiene tuición sobre la unidad educativa'));
            return $this->redirect($this->generateUrl('herramienta_ieducativa_equipamiento_laboratorio_index'));
        }

        $entityIntitucionEducativa = $this->getInstitucionEducativa($sie);
        if (!$entityIntitucionEducativa) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'La unidad educativa '.$sie.' no pertence al Sub Sistema de Educación Regular'));
            return $this->redirect($this->generateUrl('herramienta_ieducativa_equipamiento_laboratorio_index'));
        }

        $entityInstitucionEducativaSecundaria = $this->getInstitucionEducativaSecundaria($sie);
        if (!$entityInstitucionEducativaSecundaria) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'La unidad educativa '.$sie.' no esta autorizado en el Nivel de Educación Secundaria Comunitaria Productiva'));
            return $this->redirect($this->generateUrl('herramienta_ieducativa_equipamiento_laboratorio_index'));
        }

        $entityInstitucionEducativaDependencia = $this->getInstitucionEducativaDependencia($sie);
        if (!$entityInstitucionEducativaDependencia) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'La unidad educativa '.$sie.' no cuenta con dependencia Fiscal o Convenio'));
            return $this->redirect($this->generateUrl('herramienta_ieducativa_equipamiento_laboratorio_index'));
        }

        do {
            $entityIntitucionEducativaGestion = $this->getInstitucionEducativaGestion($sie,$gestionActual);
            if (count($entityIntitucionEducativaGestion)>0){
                $gestionActual = $gestionActual;
            } else {
                $gestionActual = $gestionActual-1;
            }            
        } while (count($entityIntitucionEducativaGestion)==0 and $gestionActual > 2009);

        // $entityIntitucionEducativaGestion = $this->getInstitucionEducativaGestion($sie,$gestionActual);
        // if (!$entityIntitucionEducativaGestion){
        //     $entityIntitucionEducativaGestion = $this->getInstitucionEducativaGestion($sie,($gestionActual-1));
        //     $gestionActual = $gestionActual-1;
        // } 

        if (!$entityIntitucionEducativaGestion) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'La unidad educativa '.$sie.' no cuenta con registros desde la gestión 2010 en adelante'));
            return $this->redirect($this->generateUrl('herramienta_ieducativa_equipamiento_laboratorio_index'));
        }

        $entityInstitucionEducativaSecundariaGrados = $this->getInstitucionEducativaSecundariaGrados($sie,$gestionActual);

        $entityInstitucionEducativaEdificio = $this->getInstitucionEducativaEdificio($sie,$gestionActual);

        $entityInstitucionEducativaEquipoLaboratorio = $this->getInstitucionEducativaEquipoLaboratorio($sie);
        //dump($entityInstitucionEducativaEquipoLaboratorio);die;
        if ($entityInstitucionEducativaEquipoLaboratorio) {
            $this->session->getFlashBag()->set('warning', array('title' => 'Error', 'message' => 'La unidad educativa '.$sie.' ya registro su Fomulario para el Equipamiento de Laboratorio en fecha: '.$entityInstitucionEducativaEquipoLaboratorio[0]['fecha_modificacion']));
            return $this->render($this->session->get('pathSystem') . ':IeducativaEquipamientoLaboratorio:equipamientoLaboratorioVista.html.twig', array(
                'entity' => $entityIntitucionEducativaGestion[0]
                , 'entityGrado' => $entityInstitucionEducativaSecundariaGrados
                , 'entityEdificio' => $entityInstitucionEducativaEdificio
                , 'entityEquipoLaboratorio' => $entityInstitucionEducativaEquipoLaboratorio[0]
                , 'formPdf' => $this->creaFormularioVistaPdf($sie,$gestionActual)->createView()
            ));
        }

        //dump($entityInstitucionEducativaEdificio);die;
        return $this->render($this->session->get('pathSystem') . ':IeducativaEquipamientoLaboratorio:equipamientoLaboratorio.html.twig', array(
            'form' => $this->creaFormularioBusqueda('herramienta_ieducativa_equipamiento_laboratorio_detalle',$sie)->createView()
            , 'entity' => $entityIntitucionEducativaGestion[0]
            , 'entityGrado' => $entityInstitucionEducativaSecundariaGrados
            , 'entityEdificio' => $entityInstitucionEducativaEdificio
            , 'formLaboratorio' => $this->creaFormularioLaboratorio($sie,$gestionActual)->createView()
        ));
    }

    public function registroAction(Request $request){
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d g:i:s'));
        $gestionActual = (int)$fechaActual->format('Y');

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
       
        $form = $request->get('form');

        if(!$form){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirectToRoute('herramienta_ieducativa_equipamiento_laboratorio_detalle', ['form' => $formBusqueda], 307);
        }
        
        $sie= base64_decode($form['sie']);
        $ges = base64_decode($form['ges']);
        $foto = $request->files->get('form');
        $formBusqueda = array('sie'=>$sie);

        if(!$foto){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirectToRoute('herramienta_ieducativa_equipamiento_laboratorio_detalle', ['form' => $formBusqueda], 307);
        }

        $entityInstitucionEducativaEquipoLaboratorio = $this->getInstitucionEducativaEquipoLaboratorio($sie);
        if ($entityInstitucionEducativaEquipoLaboratorio) {
            $this->session->getFlashBag()->set('warning', array('title' => 'Error', 'message' => 'La unidad educativa '.$sie.' ya registro su Fomulario para el Equipamiento de Laboratorio en fecha: '.$entityInstitucionEducativaEquipoLaboratorio[0]['fecha_modificacion']));
            return $this->redirectToRoute('herramienta_ieducativa_equipamiento_laboratorio_detalle', ['form' => $formBusqueda], 307);
        }

        //dump($request->get('form'));die;
        $filename1 = "";
        if (null != $foto['foto61']) {
            $file = $foto['foto61'];
            // $file = $entityEstudianteDatopersonal->getFoto();
            $filename1 = $sie . '_General.' . $file->guessExtension();
            $filesize = $file->getClientSize();
            if ($filesize/1024 <= 5120) {                
                $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/equipamiento_laboratorio';
                $file->move($adjuntoDir, $filename1);
                if (!file_exists($adjuntoDir.'/'.$filename1)){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'La fotografía adjunta '.$file->getClientOriginalName().', no fue registrada.'));
                    return $this->redirectToRoute('herramienta_ieducativa_equipamiento_laboratorio_detalle', ['form' => $formBusqueda], 307);
                }                
            } else {                
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'La fotografía adjunta '.$file->getClientOriginalName().' excede el tamaño permitido, Fotografia muy grande, favor ingresar una fotografía que no exceda los 5MB.'));
                return $this->redirectToRoute('herramienta_ieducativa_equipamiento_laboratorio_detalle', ['form' => $formBusqueda], 307);
            } 
        }

        //dump($request->get('form'));die;
        $filename2 = "";
        if (null != $foto['foto62']) {
            $file = $foto['foto62'];
            // $file = $entityEstudianteDatopersonal->getFoto();
            $filename2 = $sie . '_Conexion.' . $file->guessExtension();
            $filesize = $file->getClientSize();
            if ($filesize/1024 < 5120) {                
                $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/equipamiento_laboratorio';
                $file->move($adjuntoDir, $filename2);
                if (!file_exists($adjuntoDir.'/'.$filename2)){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'La fotografía adjunta '.$file->getClientOriginalName().', no fue registrada.'));
                    return $this->redirectToRoute('herramienta_ieducativa_equipamiento_laboratorio_detalle', ['form' => $formBusqueda], 307);
                }                
            } else {                
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'La fotografía adjunta '.$file->getClientOriginalName().' excede el tamaño permitido, Fotografia muy grande, favor ingresar una fotografía que no exceda los 5MB.'));
                return $this->redirectToRoute('herramienta_ieducativa_equipamiento_laboratorio_detalle', ['form' => $formBusqueda], 307);
            } 
        } 

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            // $entityUsuarioRemitente = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $usuarioId));
            // $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $entityTramiteDetalle[0]->getTramite()->getId()));
            // $entityTramiteDetalleEstado = $em->getRepository('SieAppWebBundle:TramiteEstado')->findOneBy(array('id' => 1));
            // $entityFlujoProceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('id' => $flujoProcesoId));

            $entityEquipLaboFisiQuim = new EquipLaboFisiQuim();
            $entityEquipLaboFisiQuimFotos = new EquipLaboFisiQuimFotos();

            $entityInstitucionEducativaSecundariaGrados = $this->getInstitucionEducativaSecundariaGrados($sie,$ges);
            
            $entityEquipLaboFisiQuim->setSecciCantidadTotEstu($entityInstitucionEducativaSecundariaGrados['matricula']);
            $entityEquipLaboFisiQuim->setSecciCantidad1ersec($entityInstitucionEducativaSecundariaGrados['primero']);
            $entityEquipLaboFisiQuim->setSecciCantidad2dosec($entityInstitucionEducativaSecundariaGrados['segundo']);
            $entityEquipLaboFisiQuim->setSecciCantidad3ersec($entityInstitucionEducativaSecundariaGrados['tercero']);
            $entityEquipLaboFisiQuim->setSecciCantidad4tosec($entityInstitucionEducativaSecundariaGrados['cuarto']);
            $entityEquipLaboFisiQuim->setSecciCantidad5tosec($entityInstitucionEducativaSecundariaGrados['quinto']);
            $entityEquipLaboFisiQuim->setSecciCantidad6tosec($entityInstitucionEducativaSecundariaGrados['sexto']);
            $entityEquipLaboFisiQuim->setNombreAlcalde($form['nombreAlcalde']);
            $entityEquipLaboFisiQuim->setTelefonoAlcalde($form['telefonoAlcalde']);
            $entityEquipLaboFisiQuimConstruidaTipo = $em->getRepository('SieAppWebBundle:EquipLaboFisiQuimConstruidaTipo')->findOneBy(array('id' => $form['constructor']));
            $entityEquipLaboFisiQuim->setSeccivConstruidaTipo($entityEquipLaboFisiQuimConstruidaTipo);
            
            $radio42 = $request->get('radio42');
            $entityEquipLaboFisiQuim->setSeccivEsLabFisQuim($radio42);
            if ($radio42 == 'true' or $radio42 === true){
                $cantidadAmbiente = $form['cantidadAmbiente'];
                $entityEquipLaboFisiQuim->setSeccivCantAmb($cantidadAmbiente);
            } 
            
            $radio43 = $request->get('radio43');
            $entityEquipLaboFisiQuim->setSeccivCuentaMesones($radio43);
            if ($radio43 == 'true' or $radio43 === true){
                $esCeramica = $request->get('rad431');
                $entityEquipLaboFisiQuim->setSeccivEsMesonesCeramica($esCeramica);
            } 
            
            $radio44 = $request->get('radio44');
            $entityEquipLaboFisiQuim->setSeccivCuentaPiletas($radio44);
            if ($radio44 == 'true' or $radio44 === true){
                $cantidadPiletaLavadero = $form['cantidadPiletaLavadero'];
                $entityEquipLaboFisiQuim->setSeccivCantidadPiletas($cantidadPiletaLavadero);
            }
            
            $radio45 = $request->get('radio45');
            $entityEquipLaboFisiQuim->setSeccivCuentaInstElec($radio45);
            if ($radio45 == 'true' or $radio45 === true){
                $cantidadTomaCorriente = $form['cantidadTomaCorriente'];
                $entityEquipLaboFisiQuim->setSeccivCantidadTomaCorr($cantidadTomaCorriente);
            }

            $radio51 = $request->get('radio51');
            $entityEquipLaboFisiQuim->setSeccvCuentaEquipLabCiencias($radio51);
            if ($radio51 == 'true' or $radio51 === true){
                $gestionEquipado = $form['gestionEquipado'];
                $institucionEquipo = $form['institucionEquipo'];
                $cantidadEquipo = $form['cantidadEquipo'];
                $entityEquipLaboFisiQuim->setSeccvAnioEquipado($gestionEquipado);
                $entityEquipLaboFisiQuim->setSeccvInstitucionEquipo($institucionEquipo);
                $entityEquipLaboFisiQuim->setSeccvCantidadItems($cantidadEquipo);
            }

            $entityEquipLaboFisiQuim->setFechaRegistro($fechaActual);
            $entityEquipLaboFisiQuim->setFechaModificacion($fechaActual);

            $entityInstitucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $sie));
            $entityEquipLaboFisiQuim->setInstitucioneducativa($entityInstitucioneducativa);
            
            $em->persist($entityEquipLaboFisiQuim);

            if (null != $foto['foto61']) {
                $entityEquipLaboFisiQuimFotos = new EquipLaboFisiQuimFotos();
                $entityEquipLaboFisiQuimFotos->setFoto($filename1);
                $entityEquipLaboFisiQuimFotos->setEquipLaboFisiQuim($entityEquipLaboFisiQuim);
                $entityEquipLaboFisiQuimTipoFoto = $em->getRepository('SieAppWebBundle:EquipLaboFisiQuimTipoFoto')->findOneBy(array('id' => 1));
                $entityEquipLaboFisiQuimFotos->setTipoFoto($entityEquipLaboFisiQuimTipoFoto);
                $em->persist($entityEquipLaboFisiQuimFotos);
            }
    
            //dump($request->get('form'));die;
            if (null != $foto['foto62']) {
                $entityEquipLaboFisiQuimFotos = new EquipLaboFisiQuimFotos();
                $entityEquipLaboFisiQuimFotos->setFoto($filename2);
                $entityEquipLaboFisiQuimFotos->setEquipLaboFisiQuim($entityEquipLaboFisiQuim);
                $entityEquipLaboFisiQuimTipoFoto = $em->getRepository('SieAppWebBundle:EquipLaboFisiQuimTipoFoto')->findOneBy(array('id' => 2));
                $entityEquipLaboFisiQuimFotos->setTipoFoto($entityEquipLaboFisiQuimTipoFoto);
                $em->persist($entityEquipLaboFisiQuimFotos);
            } 

            $em->flush();          

            $em->getConnection()->commit();
            $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Registro guardado correctamente'));
            return $this->redirect($this->generateUrl('herramienta_ieducativa_equipamiento_laboratorio_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, intente nuevamente'));
            return $this->redirectToRoute('herramienta_ieducativa_equipamiento_laboratorio_detalle', ['form' => $formBusqueda], 307);
        }
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    private function getInstitucionEducativaGestion($sie, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select * from vw_institucion_educativa_gestion where institucioneducativa_id = ".$sie." and gestion_tipo_id = ".$gestion."
        ");
        $query->execute();
        $objeto = $query->fetchAll();
        return $objeto;
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    private function getInstitucionEducativa($sie) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select * from vw_institucion_educativa_gestion where institucioneducativa_id = ".$sie."
        ");
        $query->execute();
        $objeto = $query->fetchAll();
        return $objeto;
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    private function getInstitucionEducativaSecundaria($sie) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select * from vw_institucion_educativa_gestion where institucioneducativa_id = ".$sie." and niveles_id like '%13%'
        ");
        $query->execute();
        $objeto = $query->fetchAll();
        return $objeto;
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    private function getInstitucionEducativaDependencia($sie) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select * from vw_institucion_educativa_gestion where institucioneducativa_id = ".$sie." and dependencia_id in (1,2,5)
        ");
        $query->execute();
        $objeto = $query->fetchAll();
        return $objeto;
    }


    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    private function getInstitucionEducativaEquipoLaboratorio($sie) {
        $adjuntoDir = '/siged/web/uploads/equipamiento_laboratorio/';
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select elfq.*, elfqct.construccion, '".$adjuntoDir."'||elfqf1.foto as foto1, '".$adjuntoDir."'||elfqf2.foto as foto2 from equip_labo_fisi_quim as elfq 
            inner join equip_labo_fisi_quim_construida_tipo as elfqct on elfqct.id = elfq.secciv_construida_tipo_id
            left join equip_labo_fisi_quim_fotos as elfqf1 on elfqf1.equip_labo_fisi_quim_id = elfq.id and elfqf1.tipo_foto_id = 1
            left join equip_labo_fisi_quim_fotos as elfqf2 on elfqf2.equip_labo_fisi_quim_id = elfq.id and elfqf2.tipo_foto_id = 2
            where elfq.institucioneducativa_id = ".$sie."
        ");
        $query->execute();
        $objeto = $query->fetchAll();
        return $objeto;
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    private function getInstitucionEducativaSecundariaGrados($sie, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select institucioneducativa_id, gestion_tipo_id
            , sum(case grado_tipo_id when 1 then cantidad else 0 end) as primero
            , sum(case grado_tipo_id when 2 then cantidad else 0 end) as segundo
            , sum(case grado_tipo_id when 3 then cantidad else 0 end) as tercero
            , sum(case grado_tipo_id when 4 then cantidad else 0 end) as cuarto
            , sum(case grado_tipo_id when 5 then cantidad else 0 end) as quinto
            , sum(case grado_tipo_id when 6 then cantidad else 0 end) as sexto
            , sum(cantidad) as matricula
            from (
            SELECT iec1.grado_tipo_id AS grado_tipo_id, iec1.institucioneducativa_id, iec1.gestion_tipo_id, count(ei1.id) as cantidad
            FROM (institucioneducativa_curso iec1
            JOIN estudiante_inscripcion ei1 ON ((ei1.institucioneducativa_curso_id = iec1.id)))
            WHERE (-- (iec1.gestion_tipo_id IN ( date_part('year', current_date) )) AND 
            (iec1.nivel_tipo_id = ANY (ARRAY[13, 3])) and iec1.institucioneducativa_id = ".$sie." and iec1.gestion_tipo_id = ".$gestion." and ei1.estadomatricula_tipo_id in (4,5,10,11,55))
            GROUP BY iec1.institucioneducativa_id, iec1.gestion_tipo_id, grado_tipo_id 
            ) as v
            GROUP BY institucioneducativa_id, gestion_tipo_id        
        ");
        $query->execute();
        $objeto = $query->fetchAll();

        if($objeto){
            $objeto = $objeto[0];
        }
        return $objeto;
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    private function getInstitucionEducativaEdificio($sie, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select turno_id, replace(replace(replace(replace(turno, 'M'::text, 'Mañana'::text), 'T'::text, 'Tarde'::text), 'N'::text, 'Noche'::text), '-'::text, ', '::text) AS turno,
            institucioneducativa_id, institucioneducativa, gestion_tipo_id from (
                SELECT string_agg(((vv.turno_id)::character varying)::text, '-'::text ORDER BY vv.turno_id) AS turno_id,
                string_agg(vv.turno, '-'::text ORDER BY vv.turno_id) AS turno,
                ie.id as institucioneducativa_id, ie.institucioneducativa, vv.gestion_tipo_id
                FROM ( 
                    SELECT v.turno, CASE v.turno WHEN 'M'::text THEN 1 WHEN 'T'::text THEN 2 WHEN 'N'::text THEN 3 ELSE 0 END AS turno_id, v.institucioneducativa_id, v.gestion_tipo_id
                    FROM ( 
                        SELECT unnest(string_to_array(string_agg(DISTINCT (
                            CASE tt1.abrv
                                WHEN 'MTN'::text THEN 'M-T-N'::character varying
                                WHEN '.'::text THEN ''::character varying
                                WHEN 'MN'::text THEN 'M-N'::character varying
                                ELSE tt1.abrv
                            END)::text, '-'::text), '-'::text, ''::text)) AS turno,
                        iec1.institucioneducativa_id, iec1.gestion_tipo_id
                        FROM ((institucioneducativa_curso iec1
                        JOIN estudiante_inscripcion ei1 ON ((ei1.institucioneducativa_curso_id = iec1.id)))
                        JOIN turno_tipo tt1 ON ((tt1.id = iec1.turno_tipo_id)))
                        WHERE ( (iec1.gestion_tipo_id = ".$gestion.") AND (iec1.institucioneducativa_id IN ( 
                                select id from institucioneducativa 
                                where le_juridicciongeografica_id in (select le_juridicciongeografica_id from institucioneducativa where id = ".$sie.")
                                and institucioneducativa_acreditacion_tipo_id = 1 and estadoinstitucion_tipo_id = 10 )) AND 
                        (iec1.nivel_tipo_id = ANY (ARRAY[13, 3])))
                        GROUP BY iec1.institucioneducativa_id, iec1.gestion_tipo_id) v
                    GROUP BY v.institucioneducativa_id, v.gestion_tipo_id, v.turno
                    ORDER BY
                        CASE v.turno
                                WHEN 'M'::text THEN 1
                                WHEN 'T'::text THEN 2
                                WHEN 'N'::text THEN 3
                                ELSE 0
                        END
                ) vv
                JOIN institucioneducativa as ie on ie.id = vv.institucioneducativa_id
                where vv.institucioneducativa_id not in (".$sie.") and ie.orgcurricular_tipo_id = 1
                GROUP BY ie.id, ie.institucioneducativa, vv.gestion_tipo_id
            ) as vvv
        ");
        $query->execute();
        $objeto = $query->fetchAll();
        return $objeto;
    }

    private function creaFormularioBusqueda($routing, $sie) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('sie', 'text', array('label' => 'Código S.I.E.', 'attr' => array('value' => $sie, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Código de institución educativa', 'style' => 'text-transform:uppercase')))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }

    private function creaFormularioLaboratorio($sie, $ges)
    { 
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_ieducativa_equipamiento_laboratorio_registro'))
            ->add('sie', 'hidden', array('attr' => array('value' => base64_encode($sie))))
            ->add('ges', 'hidden', array('attr' => array('value' => base64_encode($ges))))
            ->add('constructor',
                'choice',  
                array('label' => '', 'empty_value' => 'SELECCIONE UNA INSTITUCIÓN', 
                    'choices' => array( 
                        '1' => 'UPRE - PROGRAMA BOLIVIA CAMBIA, EVO CUMPLE'
                        ,'2' => 'MUNICIPIO'
                        ,'3' => 'ONG'
                        ,'4' => 'OTRO'
                    ),
            ))
            ->add('cantidadAmbiente', 'number', array('label' => 'Cantidad de ambientes', 'attr' => array('value' => '', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{1,3}', 'maxlength' => '3', 'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Cantidad', 'style' => 'text-transform:uppercase')))
            ->add('cantidadPiletaLavadero', 'number', array('label' => 'Cantidad de de piletas y lavaderos', 'attr' => array('value' => '', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{1,3}', 'maxlength' => '3', 'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Cantidad', 'style' => 'text-transform:uppercase')))
            ->add('cantidadTomaCorriente', 'number', array('label' => 'Cantidad de toma corrientes', 'attr' => array('value' => '', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{1,3}', 'maxlength' => '3', 'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Cantidad', 'style' => 'text-transform:uppercase')))
            ->add('gestionEquipado', 'number', array('label' => 'Año en la que fue equipado', 'attr' => array('value' => '', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{1,4}', 'maxlength' => '4', 'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Año', 'style' => 'text-transform:uppercase')))
            ->add('institucionEquipo', 'text', array('label' => 'Institución que equipo', 'attr' => array('value' => '', 'class' => 'form-control', 'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Institución', 'style' => 'text-transform:uppercase')))
            ->add('cantidadEquipo', 'number', array('label' => 'Cantidad de equipos', 'attr' => array('value' => '', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{1,5}', 'maxlength' => '5', 'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Cantidad', 'style' => 'text-transform:uppercase')))
            ->add('nombreAlcalde', 'text', array('label' => 'Nombre del alcalde', 'attr' => array('value' => '', 'class' => 'form-control',  'autocomplete' => 'on', 'required' => true, 'placeholder' => 'Nombre', 'style' => 'text-transform:uppercase')))
            ->add('telefonoAlcalde', 'number', array('label' => 'Telefono del alcalde', 'attr' => array('value' => '', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'required' => false, 'placeholder' => 'Número de teléfono', 'style' => 'text-transform:uppercase')))
            ->add('foto61', 'file', array('label' => 'Fotografía (.bmp)', 'required' => true)) 
            ->add('foto62', 'file', array('label' => 'Fotografía (.bmp)', 'required' => true)) 
            ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-blue')))
            ->getForm();
        return $form;        
    }

    public function creaFormularioVistaPdf($sie, $ges)
    { 
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_ieducativa_equipamiento_laboratorio_vista_pdf'))  
            ->add('sie', 'hidden', array('attr' => array('value' => base64_encode($sie))))
            ->add('ges', 'hidden', array('attr' => array('value' => base64_encode($ges))))          
            ->add('download', 'submit', array('label' => 'Descargar Pdf', 'attr' => array('class' => 'btn btn-lilac')))
            ->getForm();
        return $form;        
    }

    public function vistaPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $form = $request->get('form');
            $sie = base64_decode($form['sie']);
            $ges = base64_decode($form['ges']);
            $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/equipamiento_laboratorio/';

            $arch = $sie.'_'.$ges.'_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_formulario_requerimiento_equipamiento_laboratorio_v1_rcm.rptdesign&sie='.$sie.'&dir='.$adjuntoDir.'&gestion='.$ges.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('herramienta_ieducativa_equipamiento_laboratorio_index');
        }
    }

}
