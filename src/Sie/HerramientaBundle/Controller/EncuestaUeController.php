<?php

namespace Sie\HerramientaBundle\Controller;

use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use Sie\ProcesosBundle\Controller\TramiteRueController;
use Sie\AppWebBundle\Controller\WfTramiteController;
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Sie\AppWebBundle\Entity\InstitucioneducativaEncuesta;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnicoTipo;
use Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo;
use Sie\AppWebBundle\Entity\GestionTipo;
use Sie\AppWebBundle\Entity\TramiteDetalle;
use Sie\AppWebBundle\Entity\Tramite;
use Sie\AppWebBundle\Entity\TramiteTipo;
use Sie\AppWebBundle\Entity\RealizaPagoTipo;
use Sie\AppWebBundle\Entity\ProveedorTipo;




/**
 * EncuestaUeController
 *
 */
class EncuestaUeController extends Controller {
    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
    }
    public function indexAction (Request $request) { //dump("encuentassss");die;
        $this->session  = $request->getSession();
        $id_usuario     = $this->session->get('userId');
        $id_rol         = $this->session->get('roluser');
        $ie_id          = $this->session->get('ie_id');
        $gestion = $request->getSession()->get('currentyear'); 
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare("SELECT id, proveedor FROM proveedor_tipo ORDER BY 1");
        $query->execute();
        $proveedor = $query->fetchAll(); 

        $query = $em->getConnection()->prepare("SELECT id, entidad FROM realiza_pago_tipo ORDER BY 1");
        $query->execute();
        $realizaPago = $query->fetchAll(); 
       //dump($proveedor);die;
        $verificarUe = $this->Validacion($ie_id,$gestion);

       
        return $this->render('SieHerramientaBundle:EncuestaUe:index.html.twig',
            array(
                'estado' => $verificarUe,
                'ie_id'=> $ie_id ,
                'gestion'=>$gestion,
                'proveedores'=>$proveedor,
                'realizaPago'=>$realizaPago
                
            ));
        
    }
    public function Validacion ($ie_id,$gestion) {  //dump($ie_id);die;     
        $gestion = 2018; ////BORRAR
        $verificaUe = 0;
        $em = $this->getDoctrine()->getManager();
        //verificamos que la UE sea publica y de regular y q e este abierta y que n haya realizado la encuents
        $query = $em->getConnection()->prepare("SELECT COUNT(*) as ue_valido
            FROM institucioneducativa ue  --INNER JOIN institucioneducativa_encuesta en ON  ue.id =en.institucioneducativa_id
            WHERE  ue.dependencia_tipo_id in(1,2,5)  
            and   ue.estadoinstitucion_tipo_id = 10 and ue.institucioneducativa_tipo_id = 1 and ue.id = $ie_id");      
        $query->execute();
        $ue_valida= $query->fetch();
        $ue_valida = $ue_valida['ue_valido'];
        //vaidamos que la ue no haya realizadosu encuesta
        $query = $em->getConnection()->prepare("SELECT COUNT(*) as ue_encuesta_valido from institucioneducativa_encuesta ue 
        WHERE  ue.institucioneducativa_id = $ie_id");      
        $query->execute();
        $ue_encuesta_valida= $query->fetch(); 
        $ue_encuesta_valida = $ue_encuesta_valida['ue_encuesta_valido'];//dump($ue_encuesta_valida);die;

        
        $query = $em->getConnection()->prepare("SELECT COUNT(*) as director_valido from maestro_inscripcion m
            join usuario u on m.persona_id=u.persona_id
            where m.institucioneducativa_id= $ie_id and m.gestion_tipo_id=$gestion and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
            $query->execute();
            $director = $query->fetch();
            $director_valido = $director['director_valido'];        
        if($ue_valida==0 or $director_valido == 0 or $ue_encuesta_valida >0 ) {
            $verificaUe = 1;
        }       
        return $verificaUe;        
    }

    public function guardarRespuestaAction(Request $request) {//dump($request);die;      
        
        $foto1 = $request->files->get('foto1');
        $type = pathinfo($foto1, PATHINFO_EXTENSION);
        $foto1 = 'data:image/' . $type . ';base64,' . base64_encode($foto1);
        
        $foto2 = $request->get('foto2');
        $type = pathinfo($foto2, PATHINFO_EXTENSION);
        $foto2 = 'data:image/' . $type . ';base64,' . base64_encode($foto2);

        //$em = $this->getDoctrine()->getManager();
        $em = $this->getDoctrine()->getManager();
        //$em->getConnection()->beginTransaction();
       
        //$gestion=2019;
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->get('ie_id'));
        //dump($request->get('proveedores'));die;
        $realia=$em->getRepository('SieAppWebBundle:ProveedorTipo')->find($request->get('proveedores'));
        try {
                $entity = new InstitucioneducativaEncuesta();
                $entity->setInstitucionEducativa($institucioneducativa);
                $entity->setTieneSala($request->get('tieneSala'));
                $entity->setCantidadSala($request->get('cantSalas'));
                $entity->setCantidadComputadora($request->get('cantComputadoras'));
                $entity->setAnioAdquisicion($request->get('anioAdqui'));

                $entity->setTienePiso($request->get('respuestaPiso'));

                $entity->setTieneInternet($request->get('tieneInternet'));
                $entity->setCostoInternet($request->get('costo'));
                
                $entity->setProveedorUe($em->getRepository('SieAppWebBundle:ProveedorTipo')->find($request->get('proveedores'))); //$request->get('proveedores')
                $entity->setProveedorOtro($request->get('otroProveedor'));

                //$entity->setRealizaPago($em->getRepository('SieAppWebBundle:RealizaPagoTipo')->find($request->get('pago')));
                $entity->setRealizaPago($em->getRepository('SieAppWebBundle:ProveedorTipo')->find($request->get('pago')));
                $entity->setPagoOtro($request->get('realizaPagoOtro'));

                $entity->setTieneSenal($request->get('tieneInternetZona'));
                $entity->setProveedorZona($em->getRepository('SieAppWebBundle:ProveedorTipo')->find($request->get('proveedorZona')));                
               //$entity->setProveedorZonaOtro( $em->getRepository('SieAppWebBundle:ProveedorTipo')->find($request->get('otroProveedorZona'));
                
               $entity->setFoto1($foto1);
               $entity->setFoto2($foto2);
               $entity->setObservacion($request->get('observacion'));
               $entity->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($request->get('gestion')));
               $entity->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));            
            $em->persist($entity);
            $em->flush();
            $res=1;
           // $em->getConnection()->commit();
        } catch (Exception $exceptione) {
           $res=2;
        }

        return  new JsonResponse(array('estado' => $res));       

    }
    
    
    
}
