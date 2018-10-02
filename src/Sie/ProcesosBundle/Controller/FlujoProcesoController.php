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
class FlujoProcesoController extends Controller
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
        $procesoForm = $this->createProcesoForm(); 
        $pasosForm = $this->createPasosForm(); 
        $condicionForm = $this->createCondicionForm(); 
        //return $this->render('SieProcesosBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieProcesosBundle:FlujoProceso:index.html.twig', array(
            'form' => $procesoForm->createView(),
            'form_pasos' => $pasosForm->createView(),
            'form_condicion' => $condicionForm->createView()
        ));
        
    }

    public function createProcesoForm()
    {
        $form = $this->createFormBuilder()
            //->setAction($this->generateUrl('flujoproceso_guardar'))
            ->add('proceso','entity',array('label'=>'Proceso','required'=>true,'class'=>'SieAppWebBundle:FlujoTipo','property'=>'flujo','empty_value' => 'Seleccionar proceso'))
            ->add('tarea','entity',array('label'=>'Tarea','required'=>true,'class'=>'SieAppWebBundle:ProcesoTipo','property'=>'proceso_tipo','empty_value' => 'Seleccionar tarea'))
            ->add('rol','entity',array('label'=>'Tipo de rol','required'=>true,'class'=>'SieAppWebBundle:RolTipo','property'=>'rol','empty_value' => 'Seleccionar rol'))
            ->add('observacion','text',array('label'=>'Observación'))
            ->add('asignacion','entity',array('label'=>'Tipo de asignación de tarea','required'=>true,'class'=>'SieAppWebBundle:WfAsignacionTareaTipo','property'=>'nombre','empty_value' => 'Seleccionar asignacion'))
            ->add('evaluacion','choice',array('label'=>'Evaluación','required'=>true,'choices'=>array(true => 'SI',false => 'NO'),'empty_value' => '¿Tiene evaluacion?'))
            ->add('varevaluacion','text',array('label'=>'Variable a evaluar','required'=>false))
            ->add('tareaant','choice',array('label'=>'Tarea','required'=>false,'empty_value' => 'Seleccionar tarea anterior'))
            ->add('tareasig','choice',array('label'=>'Tarea','required'=>false,'empty_value' => 'Seleccionar tarea siguiente'))
            ->add('plazo','text',array('label'=>'Plazo','required'=>false))
            ->getForm();
        return $form;
    }

    public function createPasosForm()
    {
        $form = $this->createFormBuilder()
            //->setAction($this->generateUrl('flujoproceso_guardar_pasos'))
            ->add('ptarea','choice',array('label'=>'Tarea','required'=>true,'empty_value' => 'Seleccionar tarea'))
            ->add('nombre','text',array('label'=>'nombre','required'=>true))
            ->add('posicion','text',array('label'=>'posicion','required'=>true))
            ->add('tipopaso','entity',array('label'=>'Tipo de asignación de tarea','required'=>true,'class'=>'SieAppWebBundle:WfPasosTipo','property'=>'nombre','empty_value' => 'Seleccionar tipo de paso'))
            ->getForm();
        return $form;
    }
    public function createCondicionForm()
    {
        $form = $this->createFormBuilder()
            ->add('ctarea','choice',array('label'=>'Tarea','required'=>true,'empty_value' => 'Seleccionar tarea'))
            ->add('condiciones','choice',array('label'=>'Condición','required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'empty_value' => 'Seleccione condición'))
            ->add('ctareasig','choice',array('label'=>'Tarea','required'=>true,'empty_value' => 'Seleccionar tarea'))
            ->add('tipocondicion','entity',array('label'=>'Tipo de asignación de tarea','required'=>true,'class'=>'SieAppWebBundle:WfCompuerta','property'=>'nombre','empty_value' => 'Seleccionar tipo de compuerta'))
            ->getForm();
        return $form;
    }
    
    public function guardarTareaAction(Request $request )
    {
        //dump($request);die;
        $flujoproceso=new FlujoProceso();
        $flujotipo=new FlujoTipo();
        $roltipo=new RolTipo();
        $wfasignacion=new WfAsignacionTareaTipo();
        $form = $request->get('form');
        //dump($form);die;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $query = $em->getConnection()->prepare("select * from flujo_proceso where flujo_tipo_id=". $form['proceso'] ." and proceso_id=". $form['tarea']);
        $query->execute();
        $tarea = $query->fetchAll();
        if(!$tarea){
            $flujotipo = $em->getRepository('SieAppWebBundle:FlujoTipo')->find($form['proceso']);
            if($flujotipo->getObs()=="ACTIVO"){
                $mensaje = 'No puede adicionar tareas, pues el proceso se encuentra activo';
                $request->getSession()
                        ->getFlashBag()
                        ->add('error', $mensaje);
            }else{
                $roltipo=$em->getRepository('SieAppWebBundle:RolTipo')->find($form['rol']);
                $wfasignacion=$em->getRepository('SieAppWebBundle:WfAsignacionTareaTipo')->find($form['asignacion']);
                $procesotipo=$em->getRepository('SieAppWebBundle:ProcesoTipo')->find($form['tarea']);
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('flujo_proceso');")->execute();
                $flujoproceso->setProceso($procesotipo);
                $flujoproceso->setObs($form['observacion']);
                $query1 = $em->getConnection()->prepare('SELECT fp.orden FROM flujo_proceso fp WHERE fp.flujo_tipo_id='. $form['proceso'] .' ORDER BY fp.orden desc limit 1');
                $query1->execute();
                $orden = $query1->fetchAll();
                //if ($form['orden']=="")
                //{
                //    $flujoproceso->setOrden(null);    
                //}else{
                //dump($orden);die;
                if(!$orden){
                    $flujoproceso->setOrden(1);
                }else{
                    $flujoproceso->setOrden($orden[0]['orden']+1);
                }
                $flujoproceso->setEsEvaluacion($form['evaluacion']);
                if ($form['plazo']=="")
                {
                    $flujoproceso->setPlazo(null);    
                }else
                {
                    $flujoproceso->setPlazo((int)$form['plazo']);    
                }
                if (!$orden)
                {
                    $flujoproceso->setTareaAntId(null);
                    $flujoproceso->setTareaSigId(null);    
                }else{
                    $flujoproceso->setTareaAntId((int)$form['tareaant']);
                    if($form['tareasig']=="")
                    {
                        $flujoproceso->setTareaSigId(null);        
                    }
                    else{
                        $flujoproceso->setTareaSigId((int)$form['tareasig']);
                    }
                }
                $flujoproceso->setVariableEvaluacion($form['varevaluacion']);
                $flujoproceso->setWfAsignacionTareaTipo($wfasignacion);
                $flujoproceso->setFlujoTipo($flujotipo);
                $flujoproceso->setRolTipo($roltipo);
                //dump($flujoproceso);die;
                $em->persist($flujoproceso);
                $em->flush();
                $em->getConnection()->commit();
                if ($orden)
                {
                    //$fp = $em->getRepository('SieAppWebBundle:FlujoProceso')->findBy(array('tareaAntId'=>''));
                    //dump($form);die;
                    $query=$em->getConnection()->prepare('select id from flujo_proceso where tarea_ant_id='.$form['tareaant']);
                    $query->execute();
                    $fp = $query->fetchAll();
                    //dump($fp);die;
                    $flujop = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($form['tareaant']);
                    if($flujop->getEsEvaluacion()==TRUE)
                    {
                        $id=$flujop->getId();
                    }else{
                        $id= $fp[0]['id'];
                    }
                    $flujop->setTareaSigId($id);
                    $em->persist($flujop);
                    $em->flush();
                    //$em->getConnection()->commit();
                }
                //dump($flujoproceso);die;
                //$em->getConnection()->commit();
                $mensaje = 'La tarea fué registrada con éxito';
                $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje);
                //dump($request);die;
                //$form=$request->get('form');
                //****LISTAR TAREAS***
            }
        }else{
            $mensaje = 'La tarea ya fué registrada';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
        }
        $data = $this->listarT($form['proceso']);
        return $this->render('SieProcesosBundle:FlujoProceso:tablaTarea.html.twig',$data);
        //return new Response($form['observacion']);

    }
    public function guardarPasosAction(Request $request )
    {
        //dump($request);die;
        $flujoproceso=new FlujoProceso();
        $wfpasostipo=new WfPasosTipo();
        $wfpasosflujoproceso=new WfPasosFlujoProceso();
        $form = $request->get('form');
        //dump($form);die;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($form['ptarea']);
        if($flujoproceso->getFlujoTipo()->getObs()=="ACTIVO"){
            $mensaje = 'No puede guardar pasos, pues el proceso se encuentra activo';
            $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje);
        }else{
            $wfpasostipo=$em->getRepository('SieAppWebBundle:WfPasosTipo')->find($form['tipopaso']);
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('wf_pasos_flujo_proceso');")->execute();
            $wfpasosflujoproceso->setWfPasosTipo($wfpasostipo);
            $wfpasosflujoproceso->setFlujoProceso($flujoproceso);
            $wfpasosflujoproceso->setNombre($form['nombre']);
            $wfpasosflujoproceso->setPosicion((int)$form['posicion']);
            //dump(wf)
            $em->persist($wfpasosflujoproceso);
            $em->flush();
            $em->getConnection()->commit();
            //dump($flujoproceso);die;
            $mensaje = 'El Paso fué registrado con éxito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
            //dump($request);die;
        }
        $data = $this->listarP($flujoproceso->getFlujoTipo()->getId());
        return $this->render('SieProcesosBundle:FlujoProceso:tablaPasos.html.twig',$data);
        //return new Response($form['observacion']);

    }
    public function guardarCondicionAction(Request $request )
    {
        //dump($request);die;
        $flujoproceso=new FlujoProceso();
        $wfcompuerta=new WfCompuerta();
        $wftareacompuerta=new WfTareaCompuerta();
        $form = $request->get('form');
        //dump($form);die;
        
        //dump($flujotipo);die;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($form['ctarea']);
        if($flujoproceso->getFlujoTipo()->getObs()=="ACTIVO"){
            $mensaje = 'No puede guardar condicionales, pues el proceso se encuentra activo';
            $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje);
        }else{
            $wfcompuerta=$em->getRepository('SieAppWebBundle:WfCompuerta')->find($form['tipocondicion']);
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('wf_tarea_compuerta');")->execute();
            $wftareacompuerta->setFlujoProceso($flujoproceso);
            $wftareacompuerta->setWfCompuerta($wfcompuerta);
            $wftareacompuerta->setCondicion($form['condiciones']);
            $wftareacompuerta->setCondicionTareaSiguiente($form['ctareasig']);
            //dump(wf)
            $em->persist($wftareacompuerta);
            $em->flush();
            $em->getConnection()->commit();
            //dump($flujoproceso);die;
            $mensaje = 'La condicion fué registrada con éxito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
            //dump($request);die;
        }
        $data = $this->listarC($flujoproceso->getFlujoTipo()->getId());
        //dump($data);die;
        return $this->render('SieProcesosBundle:FlujoProceso:tablaCondicion.html.twig',$data);
        //return new Response($form['observacion']);

    }
    public function cargarTareasAction(Request $request)
    {
        //dump($request);die;
        $flujotipo =$request->get('flujotipo');
        $paso= $request->get('paso');
        //dump($flujotipo);die;
        $em = $this->getDoctrine()->getManager();
        //get grado
        //$tareas = array();
        $ctareasArray = array();
        if($paso==3)
        {
            $query1 = $em->getConnection()->prepare('SELECT
            fp.id,
            pt.proceso_tipo
            FROM
            flujo_proceso fp JOIN proceso_tipo pt on fp.proceso_id=pt.id
            WHERE fp.es_evaluacion=TRUE AND flujo_tipo_id='.$flujotipo.' ORDER BY fp.id');
            $query1->execute();
            $ctareas = $query1->fetchAll();
            for($i=0;$i<count($ctareas);$i++)
            {
                $ctareasArray[$ctareas[$i]['id']] = $ctareas[$i]['proceso_tipo'];
            }
        }
        $query2 = $em->getConnection()->prepare('SELECT
        fp.id,
        pt.proceso_tipo
        FROM
        flujo_proceso fp JOIN proceso_tipo pt on fp.proceso_id=pt.id
        WHERE flujo_tipo_id='.$flujotipo.' ORDER BY fp.id');
        $query2->execute();
        $tareas = $query2->fetchAll();
        $tareasArray = array();
        for($i=0;$i<count($tareas);$i++)
        {
            $tareasArray[$tareas[$i]['id']] = $tareas[$i]['proceso_tipo'];
        }
        
        //dump($tareas);die;
        $response = new JsonResponse();
        return $response->setData(array('tareas' => $tareasArray,'ctareas' => $ctareasArray));
        
    }
    public function listarTareasAction(Request $request )
    {
        //dump($request);die;
        $flujotipo = $request->get('flujotipo');
        $data = $this->listarT($flujotipo);
        return $this->render('SieProcesosBundle:FlujoProceso:tablaTarea.html.twig',$data);
    }
    public function listarPasosAction(Request $request )
    {
        //dump($request);die;
        $flujotipo = $request->get('flujotipo');
        $data = $this->listarP($flujotipo);
        //dump($data);die;
        return $this->render('SieProcesosBundle:FlujoProceso:tablaPasos.html.twig',$data);
    }
    
    public function listarCondicionAction(Request $request )
    {
        //dump($request);die;
        $flujotipo = $request->get('flujotipo');
        $data = $this->listarC($flujotipo);
        return $this->render('SieProcesosBundle:FlujoProceso:tablaCondicion.html.twig',$data);
    }

    public function listarFlujoAction(Request $request )
    {
        //dump($request);die;
        $flujotipo = $request->get('flujotipo');
        $data = $this->listarF($flujotipo);
        return $this->render('SieProcesosBundle:FlujoProceso:tablaFlujo.html.twig',$data);
    }

    public function finalizarAction(Request $request )
    {
        //dump($request);die;
        $id = $request->get('flujotipo');
                //dump($id);die;
        $em = $this->getDoctrine()->getManager();
        $flujotipo = $em->getRepository('SieAppWebBundle:FlujoTipo')->find($id);
        //dump($flujotipo);die;
        if(strpos($flujotipo->getObs(),"ACTIVO")!==false){
            $mensaje = 'El proceso ya se encuentra activo';
            $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje);
        }else{
            
            if($flujotipo->getObs()=="")
            {
                $flujotipo->setObs('ACTIVO');    
            }else{
                $flujotipo->setObs($flujotipo->getObs().', ACTIVO');
            }
            $em->persist($flujotipo);
            $em->flush();
            //$em->getConnection()->commit();
            $mensaje = 'Proceso activado';
            $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje);
        }
        $data = $this->listarF($id);
        //dump($data);die;
        return $this->render('SieProcesosBundle:FlujoProceso:tablaFlujo.html.twig',$data);
    }
    
    public function listarT($flujotipo)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT
        fp.flujo_tipo_id,
        fp.id,
        fp.obs,
        fp.orden,
        fp.es_evaluacion,
        fp.plazo,
        fp.tarea_ant_id,
        fp.tarea_sig_id,
        fp.variable_evaluacion,
        fp.rol_tipo_id,
        fp.wf_asignacion_tarea_tipo_id,
        pt.proceso_tipo,
        wfa.nombre,
        rt.rol
        FROM
        flujo_proceso fp
        INNER JOIN proceso_tipo pt ON fp.proceso_id = pt."id"
        INNER JOIN rol_tipo rt ON fp.rol_tipo_id = rt."id"
        LEFT JOIN wf_asignacion_tarea_tipo wfa ON fp.wf_asignacion_tarea_tipo_id = wfa."id"
        WHERE fp.flujo_tipo_id='. $flujotipo . ' ORDER BY fp.id');
        $query->execute();
        $arrDataTareas = $query->fetchAll();
        $data['tareas']=$arrDataTareas;
        return $data;
    }
    public function listarP($flujotipo)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT
        fp.id,
        pt.proceso_tipo,
        wffp.nombre as paso,
        wffp.posicion,
        wfpt.nombre as tipo_paso
        FROM
        flujo_proceso fp
        JOIN wf_pasos_flujo_proceso wffp ON wffp.flujo_proceso_id = fp.id
        JOIN wf_pasos_tipo wfpt ON wffp.wf_pasos_tipo_id = wfpt.id
        JOIN proceso_tipo pt ON fp.proceso_id = pt.id
        WHERE fp.flujo_tipo_id='.$flujotipo.'
        ORDER BY fp.id,wffp.posicion');
        $query->execute();
        $arrDataPasos = $query->fetchAll();
        $data['pasos']=$arrDataPasos;
        return $data;
    }
    public function listarC($flujotipo)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT
        fp.id,
        pt.proceso_tipo,
        wftc.condicion,
        wftc.condicion_tarea_siguiente,
        wfc.nombre as tipo_compuerta
        FROM flujo_proceso fp JOIN proceso_tipo pt ON fp.proceso_id = pt.id
        JOIN wf_tarea_compuerta wftc ON wftc.flujo_proceso_id = fp.id 
        JOIN wf_compuerta wfc ON wftc.wf_compuerta_id = wfc.id
        WHERE fp.flujo_tipo_id='. $flujotipo .' ORDER BY fp.id');
        $query->execute();
        $arrDataCondicion = $query->fetchAll();
        $data['condiciones']=$arrDataCondicion;
        return $data;
    }
    public function listarF($flujotipo)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT fp.id, p.proceso_tipo,r.rol,fp.orden, fp.es_evaluacion,fp.variable_evaluacion, wftc.condicion, wfc.nombre, wftc.condicion_tarea_siguiente, fp.plazo, fp.tarea_ant_id, fp.tarea_sig_id
        FROM 
          flujo_tipo f join flujo_proceso fp on f.id = fp.flujo_tipo_id
          join proceso_tipo p on p.id = fp.proceso_id
          left join wf_tarea_compuerta wftc on wftc.flujo_proceso_id = fp.id
          left join wf_compuerta wfc on wftc.wf_compuerta_id=wfc.id
          join rol_tipo r on fp.rol_tipo_id=r.id
          WHERE f.id='. $flujotipo .' order by fp.orden');
        $query->execute();
        $arrDataCondicion = $query->fetchAll();
        $data['flujo']=$arrDataCondicion;
        return $data;
    }

    public function formularioNuevoAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $formularioForm = $this->crearFormularioForm(); 
        //$em = $this->getDoctrine()->getManager();
        //$wfpasosflujoproceso = $em->getRepository('SieAppWebBundle:WfPasosFlujoProceso')->find(1);
        //dump($wfpasosflujoproceso->getNombre());die;
        return $this->render('SieProcesosBundle:FlujoProceso:formularioNuevo.html.twig', array(
            'form' => $formularioForm->createView(),
        ));
    }
    public function crearFormularioForm()
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('flujoproceso_formulario_guardar'))
            ->add('label','text',array('label'=>'Label'))
            ->add('nombre','text',array('label'=>'Nombre'))
            ->add('ruta','text',array('label'=>'Ruta'))
            ->add('tipo','choice',array('label'=>'Tipo de campo','required'=>true,'choices'=>array('text' => 'text','hidden' => 'hidden','entity' => 'entity','choice' => 'choice','button' => 'button','submit' => 'submit','textarea' => 'textarea'),'empty_value' => 'Seleccione tipo de campo'))
            ->add('guardar','submit',array('label'=>'Adicionar'))
            ->getForm();
        return $form;
    }

    public function formularioGuardarAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //dump($request->get('form'));die;
        $form = $request->get('form');
        /*$formulario = '$this->createFormBuilder()'.
            '->setAction($this->generateUrl(' . "'" . $form['ruta'] . "'" . '))'.
            '->add('. "'" . $form['nombre']. "'". ',' . "'". $form['tipo'] . "'" . ',array('. "'label'=>" ."'". $form['label']. "'" . '))'.
            '->getForm();';*/
        $formulario = $this->createFormBuilder()
            ->setAction($this->generateUrl($form['ruta']))
            ->add($form['nombre'],$form['tipo'],array('label'=>$form['label']))
            ->getForm();
        dump($formulario);die; 
        //$wfpasosflujoproceso = new WfPasosFlujoProceso();
        $em = $this->getDoctrine()->getManager();
        $wfpasosflujoproceso = $em->getRepository('SieAppWebBundle:WfPasosFlujoProceso')->find(1);
        $wfpasosflujoproceso->setNombre($formulario);
        $em->flush();
        return false;
    }
    
}