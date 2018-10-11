<?php

namespace Sie\ProcesosBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

use Sie\AppWebBundle\Entity\FlujoProceso;
use Sie\AppWebBundle\Entity\FlujoTipo;
use Sie\AppWebBundle\Entity\ProcesoTipo;
use Sie\AppWebBundle\Entity\RolTipo;
use Sie\AppWebBundle\Entity\WfAsignacionTareaTipo;
use Sie\AppWebBundle\Entity\WfCompuerta;
use Sie\AppWebBundle\Entity\WfPasosFlujoProceso;
use Sie\AppWebBundle\Entity\WfPasosTipo;
use Sie\AppWebBundle\Entity\WfTareaCompuerta;

use Sie\AppWebBundle\Form\FlujoProcesoType;


/**
 * FlujoTipo controller.
 *
 */
class FlujoSeguimientoController extends Controller
{
    public $session;
 
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }
    
    public function indexAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
       
        $flujoSeguimientoForm = $this->createFlujoSeguimientoForm(); 
        
        return $this->render('SieProcesosBundle:FlujoSeguimiento:index.html.twig', array(
            'form' => $flujoSeguimientoForm->createView(),
        ));
        
    }

    public function createFlujoSeguimientoForm()
    {
        $form = $this->createFormBuilder()
            //->setAction($this->generateUrl('flujoproceso_guardar'))
            ->add('proceso','entity',array('label'=>'Proceso','required'=>true,'class'=>'SieAppWebBundle:FlujoTipo','property'=>'flujo','empty_value' => 'Seleccionar proceso'))
            ->add('tramite','text',array('label'=>'Tramite','required'=>true,'attr' => array('placeholder'=>'Nro. de trámite')))
            ->getForm();
        return $form;
    }

    public function verFlujoAction(Request $request )
    {
        //dump($request);die;
        $form = $request->get('form');
        //dump($id);die;
        $data = $this->listarF($form['proceso'],$form['tramite']);
        //dump($data);die;
        if (($form['proceso'] == 5 && !$data['nombre_ie']) || ($form['proceso'] == 14 && !$data['nombre_ie']) || ($form['proceso'] == 6 && !$data['estudiante']) || ($form['proceso'] == 7 && !$data['estudiante'])) 
        {
            //dump($data['nombre_ie']);die;
            $mensaje = 'Número de tramite es incorrecto';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
        }
        return $this->render('SieProcesosBundle:FlujoSeguimiento:flujo.html.twig',$data);
        
    }
        
    public function listarF($flujotipo,$tramite)
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * TRAMITE RUE
         */
        if($flujotipo == 5) 
        {
            $query = $em->getConnection()->prepare('select p.id, p.flujo,d.institucioneducativa, p.proceso_tipo, p.orden, p.es_evaluacion,p.variable_evaluacion, p.condicion, p.nombre,d.valor_evaluacion, p.condicion_tarea_siguiente, p.plazo, p.tarea_ant_id, p.tarea_sig_id, p.rol_tipo_id,d.id as td_id,d.tramite_id, d.flujo_proceso_id,d.fecha_registro,d.usuario_remitente_id,d.usuario_destinatario_id
        from
        (SELECT 
          fp.id, f.flujo, p.proceso_tipo, fp.orden, fp.es_evaluacion,fp.variable_evaluacion, wftc.condicion, wfc.nombre, wftc.condicion_tarea_siguiente, fp.plazo, fp.tarea_ant_id, fp.tarea_sig_id, fp.rol_tipo_id
        FROM 
          flujo_tipo f join flujo_proceso fp on f.id = fp.flujo_tipo_id
          join proceso_tipo p on p.id = fp.proceso_id
          left join wf_tarea_compuerta wftc on wftc.flujo_proceso_id = fp.id
          left join wf_compuerta wfc on wftc.wf_compuerta_id=wfc.id
        WHERE 
           f.id='. $flujotipo .' order by fp.orden)p
        LEFT JOIN
        (SELECT 
          t1.id,t1.tramite_id, t1.flujo_proceso_id,t1.fecha_registro,t1.usuario_remitente_id,t1.usuario_destinatario_id,i.institucioneducativa,t1.valor_evaluacion
        FROM 
          tramite_detalle t1 join tramite t on t1.tramite_id=t.id
          join institucioneducativa i on t.institucioneducativa_id=i.id
        where t1.tramite_id='. $tramite .' order by t1.id)d
        ON p.id=d.flujo_proceso_id order by p.orden,d.fecha_registro');
        
        }
        /**
         * TRAMITE RITT NUEVOS
         */
        if($flujotipo == 14)
        {
            //dump($nombre_instituto);die;
            $query = $em->getConnection()->prepare('select p.id, p.flujo, p.proceso_tipo, p.orden, p.es_evaluacion,p.variable_evaluacion, p.condicion, p.nombre,d.valor_evaluacion, p.condicion_tarea_siguiente, p.plazo, p.tarea_ant_id, p.tarea_sig_id, p.rol_tipo_id,d.id as td_id,d.tramite_id, d.flujo_proceso_id,d.fecha_registro,d.usuario_remitente_id,d.usuario_destinatario_id
        from
        (SELECT 
          fp.id, f.flujo, p.proceso_tipo, fp.orden, fp.es_evaluacion,fp.variable_evaluacion, wftc.condicion, wfc.nombre, wftc.condicion_tarea_siguiente, fp.plazo, fp.tarea_ant_id, fp.tarea_sig_id, fp.rol_tipo_id
        FROM 
          flujo_tipo f join flujo_proceso fp on f.id = fp.flujo_tipo_id
          join proceso_tipo p on p.id = fp.proceso_id
          left join wf_tarea_compuerta wftc on wftc.flujo_proceso_id = fp.id
          left join wf_compuerta wfc on wftc.wf_compuerta_id=wfc.id
        WHERE 
           f.id='. $flujotipo .' order by fp.orden)p
        LEFT JOIN
        (SELECT 
          t1.id,t1.tramite_id, t1.flujo_proceso_id,t1.fecha_registro,t1.usuario_remitente_id,t1.usuario_destinatario_id,t1.valor_evaluacion
        FROM 
          tramite_detalle t1 join tramite t on t1.tramite_id=t.id
        where t1.tramite_id='. $tramite .' order by t1.id)d
        ON p.id=d.flujo_proceso_id order by p.orden,d.fecha_registro');
        
        }
        /**
         * TRAMITE PARA ESTUDIANTES
         */
        if($flujotipo == 6 || $flujotipo == 7 )
        {
            $query = $em->getConnection()->prepare("select p.id, p.flujo,d.estudiante, p.proceso_tipo, p.orden, p.es_evaluacion,p.variable_evaluacion, p.condicion, p.nombre,d.valor_evaluacion, p.condicion_tarea_siguiente, p.plazo, p.tarea_ant_id, p.tarea_sig_id, p.rol_tipo_id,d.id as td_id,d.tramite_id, d.flujo_proceso_id,d.fecha_registro,d.usuario_remitente_id,d.usuario_destinatario_id
            from
            (SELECT 
              fp.id, f.flujo, p.proceso_tipo, fp.orden, fp.es_evaluacion,fp.variable_evaluacion, wftc.condicion, wfc.nombre, wftc.condicion_tarea_siguiente, fp.plazo, fp.tarea_ant_id, fp.tarea_sig_id, fp.rol_tipo_id
            FROM 
              flujo_tipo f join flujo_proceso fp on f.id = fp.flujo_tipo_id
              join proceso_tipo p on p.id = fp.proceso_id
              left join wf_tarea_compuerta wftc on wftc.flujo_proceso_id = fp.id
              left join wf_compuerta wfc on wftc.wf_compuerta_id=wfc.id
            WHERE 
               f.id=". $flujotipo .")p
            LEFT JOIN
            (SELECT 
              t1.id,t1.tramite_id, t1.flujo_proceso_id,t1.fecha_registro,t1.usuario_remitente_id,t1.usuario_destinatario_id,(e.nombre||' '||e.paterno||' '||e.materno)as estudiante,t1.valor_evaluacion
            FROM 
              tramite_detalle t1 join tramite t on t1.tramite_id=t.id
              join estudiante_inscripcion ei on t.estudiante_inscripcion_id=ei.id
              join estudiante e on ei.estudiante_id=e.id
            where t1.tramite_id=". $tramite .")d ON p.id=d.flujo_proceso_id order by p.orden,d.fecha_registro
            ");
        }
        $query->execute();
        $arrData = $query->fetchAll();
        //dump($arrData);die;
        $data['flujo']=$arrData;
        $data['flujotipo'] = $flujotipo;
        if($flujotipo == 5)
        {
            $data['nombre']=$arrData[0]['institucioneducativa'];
        }elseif($flujotipo == 6 || $flujotipo == 7 ){
            $data['nombre'] = $arrData[0]['estudiante'];
        }elseif($flujotipo == 14){
            $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
                ->select('wf')
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = wf.tramite')
                ->where('t.id =' . $tramite)
                ->getQuery()
                ->getResult();
            //dump($wfSolicitudTramite);die;
            $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
            $nombre_instituto = $datos[10]['nom_instituto'];
            $data['nombre'] = $nombre_instituto;
        }
        return $data;
    }
    
}