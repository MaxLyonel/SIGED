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
use Sie\AppWebBundle\Entity\Usuario;
use Sie\AppWebBundle\Entity\UsuarioRol;
use Sie\AppWebBundle\Entity\Persona;


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
            ->add('proceso','entity',array('label'=>'Proceso','required'=>true,'class'=>'SieAppWebBundle:FlujoTipo','query_builder'=>function(EntityRepository $ft){
                return $ft->createQueryBuilder('ft')->where('ft.id >4')->orderBy('ft.flujo','ASC');},'property'=>'flujo','empty_value' => 'Seleccionar proceso'))
            ->add('tarea','entity',array('label'=>'Tarea','required'=>true,'class'=>'SieAppWebBundle:ProcesoTipo','query_builder'=>function(EntityRepository $pt){
                return $pt->createQueryBuilder('pt')->orderBy('pt.procesoTipo','ASC');},'property'=>'proceso_tipo','empty_value' => 'Seleccionar tarea'))
            ->add('rol','entity',array('label'=>'Tipo de rol','required'=>true,'class'=>'SieAppWebBundle:RolTipo','query_builder'=>function(EntityRepository $rt){
                return $rt->createQueryBuilder('rt')->orderBy('rt.rol','ASC');},'property'=>'rol','empty_value' => 'Seleccionar rol'))
            ->add('observacion','text',array('label'=>'Observación', 'required'=>false))
            ->add('asignacion','entity',array('label'=>'Tipo de asignación de tarea','required'=>true,'class'=>'SieAppWebBundle:WfAsignacionTareaTipo','query_builder'=>function(EntityRepository $wfa){
                return $wfa->createQueryBuilder('wfa')->where('wfa.id =1 ');},'property'=>'nombre','empty_value' => 'Seleccionar asignacion'))
            ->add('evaluacion','choice',array('label'=>'Evaluación','required'=>true,'choices'=>array(true => 'SI',false => 'NO'),'empty_value' => '¿Tiene evaluacion?'))
            ->add('varevaluacion','text',array('label'=>'Variable a evaluar','required'=>false,'attr'=>array('style'=>'text-transform:uppercase')))
            ->add('tareaant','choice',array('label'=>'Tarea','required'=>false,'empty_value' => 'Seleccionar tarea anterior'))
            ->add('tareasig','choice',array('label'=>'Tarea','required'=>false,'empty_value' => 'Seleccionar tarea siguiente'))
            ->add('plazo','text',array('label'=>'Plazo','required'=>false,'attr'=>array('title'=>'solo números')))
            ->add('ruta','text',array('label'=>'Ruta de la tarea','required'=>false,'attr'=>array('style'=>'text-transform:lowercase')))
            ->add('rutaReporte','text',array('label'=>'Ruta del reporte','required'=>false,'attr'=>array('style'=>'text-transform:lowercase')))
            //->add('usuarios', 'choice', array('required'=>false, 'attr' => array('class' => 'form-control')))
            //->add('usuarios','choice',array('required'=>false,'multiple' => true,'expanded' => true,))
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
            ->add('tipocondicion','entity',array('label'=>'Tipo de asignación de tarea','required'=>true,'class'=>'SieAppWebBundle:WfCompuerta','query_builder'=>function(EntityRepository $wfc){
                return $wfc->createQueryBuilder('wfc')->where('wfc.id =3 ');},'property'=>'nombre','empty_value' => 'Seleccionar tipo de compuerta'))
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
                $flujoproceso->setVariableEvaluacion(strtoupper($form['varevaluacion']));
                $flujoproceso->setWfAsignacionTareaTipo($wfasignacion);
                $flujoproceso->setFlujoTipo($flujotipo);
                $flujoproceso->setRolTipo($roltipo);
                $flujoproceso->setRutaFormulario($form['ruta']);
                $flujoproceso->setRutaReporte($form['rutaReporte']);
                //dump($flujoproceso);die;
                $em->persist($flujoproceso);
                $em->flush();
                $em->getConnection()->commit();
                if($flujoproceso->getEsEvaluacion() == true){
                    $flujoproceso->setTareaSigId($flujoproceso->getId());
                    $em->flush();
                }
                if ($orden)
                {
                    $flujop = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($form['tareaant']);
                    if($flujop->getEsEvaluacion() == FALSE)
                    {
                        $flujop->setTareaSigId($flujoproceso->getId());
                        $em->flush();
                    }
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
        //$flujoproceso=new FlujoProceso();
        //$wfpasostipo=new WfPasosTipo();
        
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
            $wfpfp = $em->getRepository('SieAppWebBundle:WfPasosFlujoProceso')->createQueryBuilder('wfpfp')
                ->select('wfpfp')
                ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'WITH', 'wfpfp.flujoProceso = fp.id')
                ->where('fp.id ='. $flujoproceso->getId() )
                ->andWhere('wfpfp.posicion=' . $form['posicion'])
                ->getQuery()
                ->getResult();
            //dump($wfpfp);die;
            if($wfpfp){
                $mensaje = 'La posición ' . $form['posicion'] . ', ya fué registrada para la tarea "'. $flujoproceso->getProceso()->getProcesoTipo() . '"';
                $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje);    
            }else{
                $wfpasosflujoproceso=new WfPasosFlujoProceso();
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
            $wftareacompuerta->setCondicionTareaSiguiente($form['ctareasig']?$form['ctareasig']:null);
            //dump($wftareacompuerta);die;
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
        fp.ruta_formulario,
        fp.ruta_reporte,
        pt_a.proceso_tipo as tarea_ant,
        pt_s.proceso_tipo as tarea_sig,
        fp.variable_evaluacion,
        fp.rol_tipo_id,
        fp.wf_asignacion_tarea_tipo_id,
        pt.proceso_tipo,
        wfa.nombre,
        rt.rol
        FROM
        flujo_proceso fp
        INNER JOIN proceso_tipo pt ON fp.proceso_id = pt.id
        INNER JOIN rol_tipo rt ON fp.rol_tipo_id = rt.id
        LEFT JOIN wf_asignacion_tarea_tipo wfa ON fp.wf_asignacion_tarea_tipo_id = wfa.id
	    left JOIN flujo_proceso fp_a ON fp.tarea_ant_id = fp_a.id
	    left JOIN proceso_tipo pt_a ON fp_a.proceso_id = pt_a.id
	    left JOIN flujo_proceso fp_s ON fp.tarea_sig_id = fp_s.id
	    left JOIN proceso_tipo pt_s ON fp_s.proceso_id = pt_s.id
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
        wffp.id,
        fp.id as fp_id,
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
        //dump($data);die;
        return $data;
    }
    public function listarC($flujotipo)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT
        wftc.id,
        fp.id as fp_id,
        pt.proceso_tipo,
        wftc.condicion,
        ptc.proceso_tipo as condicion_tarea_sig,
        wfc.nombre as tipo_compuerta
        FROM flujo_proceso fp JOIN proceso_tipo pt ON fp.proceso_id = pt.id
        JOIN wf_tarea_compuerta wftc ON wftc.flujo_proceso_id = fp.id 
        JOIN wf_compuerta wfc ON wftc.wf_compuerta_id = wfc.id
        LEFT JOIN flujo_proceso fpc ON wftc.condicion_tarea_siguiente = fpc.id
    	LEFT JOIN proceso_tipo ptc ON fpc.proceso_id = ptc.id
        WHERE fp.flujo_tipo_id='. $flujotipo .' ORDER BY fp.id');
        $query->execute();
        $arrDataCondicion = $query->fetchAll();
        $data['condiciones']=$arrDataCondicion;
        //dump($data);die;
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
        //dump($formulario);die; 
        //$wfpasosflujoproceso = new WfPasosFlujoProceso();
        $em = $this->getDoctrine()->getManager();
        $wfpasosflujoproceso = $em->getRepository('SieAppWebBundle:WfPasosFlujoProceso')->find(1);
        $wfpasosflujoproceso->setNombre($formulario);
        $em->flush();
        return false;
    }

    function pasosDeleteAction(Request $request)
    {   
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:WfPasosFlujoProceso')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar la entidad WfPasosFlujoProceso.');
        }
        $repository = $em->getRepository('SieAppWebBundle:FlujoTipo');
            $query = $repository->createQueryBuilder('ft')
                ->select('ft')
                ->where('ft.id = '. $entity->getFlujoProceso()->getFlujoTipo()->getId())
                ->getQuery();
        $flujotipo = $query->getResult();
        //dump($flujotipo[0]->getObs());die;
        //dump(strpos($flujotipo->getObs(),"ACTIVO"));die;
        if(strpos($flujotipo[0]->getObs(),"ACTIVO")!==false)
        {
            $mensaje = 'No se puede eliminar el paso, pues tiene un proceso ACTIVO';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);    
        }else{
                //dump($tramites);die;
                $em->remove($entity);
                $em->flush();
                $mensaje = 'El paso se eliminó con éxito';
                $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje);
            }
        //return $this->redirect($this->generateUrl('flujotipo'));
        $data = $this->listarP($flujotipo[0]->getId());
        return $this->render('SieProcesosBundle:FlujoProceso:tablaPasos.html.twig',$data);
        
    }

    function condicionDeleteAction(Request $request)
    {   
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar la entidad WfTareaCompuerta.');
        }
        $repository = $em->getRepository('SieAppWebBundle:FlujoTipo');
            $query = $repository->createQueryBuilder('ft')
                ->select('ft')
                ->where('ft.id = '. $entity->getFlujoProceso()->getFlujoTipo()->getId())
                ->getQuery();
        $flujotipo = $query->getResult();
        //dump($flujotipo[0]->getObs());die;
        //dump(strpos($flujotipo->getObs(),"ACTIVO"));die;
        if(strpos($flujotipo[0]->getObs(),"ACTIVO")!==false)
        {
            $mensaje = 'No se puede eliminar la condición, pues tiene un proceso ACTIVO';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);    
        }else{
                //dump($tramites);die;
                $em->remove($entity);
                $em->flush();
                $mensaje = 'La condición se eliminó con éxito';
                $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje);
            }
        //return $this->redirect($this->generateUrl('flujotipo'));
        $data = $this->listarC($flujotipo[0]->getId());
        return $this->render('SieProcesosBundle:FlujoProceso:tablaCondicion.html.twig',$data);
        
    }

    function tareaDeleteAction(Request $request)
    {   
        //dump($request);die;
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar la entidad WfTareaCompuerta.');
        }
        /*$repository = $em->getRepository('SieAppWebBundle:FlujoTipo');
            $query = $repository->createQueryBuilder('ft')
                ->select('ft')
                ->where('ft.id = '. $entity->getFlujoTipo()->getId())
                ->getQuery();
        $flujotipo = $query->getResult();*/
        //dump($flujotipo[0]->getObs());die;
        //dump(strpos($flujotipo->getObs(),"ACTIVO"));die;
        //dump($entity->getFlujoTipo());die;
        $id_flujotipo = $entity->getFlujoTipo()->getId();
        if(strpos($entity->getFlujoTipo()->getObs(),"ACTIVO")!==false)
        {
            $mensaje = 'No se puede eliminar la tarea, pues tiene un proceso ACTIVO';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);    
        }else{
                //dump($tramites);die;
                $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->findBy(array('flujoProceso'=>$id));
                if($tramiteDetalle){
                    $mensaje = 'No se puede eliminar la tarea, pues tiene tramites asignados';
                    $request->getSession()
                        ->getFlashBag()
                        ->add('error', $mensaje);    
                }else{

                    $query = $em->getConnection()->prepare("delete from wf_tarea_compuerta where flujo_proceso_id in (select id from flujo_proceso where flujo_tipo_id=". $entity->getFlujoTipo()->getId() . " and orden>=". $entity->getOrden() .")");
                    $query->execute();
                    $query = $em->getConnection()->prepare("delete from wf_pasos_flujo_proceso where flujo_proceso_id in (select id from flujo_proceso where flujo_tipo_id=". $entity->getFlujoTipo()->getId() . " and orden>=". $entity->getOrden() .")");
                    $query->execute();
                    $query = $em->getConnection()->prepare("delete from wf_usuario_flujo_proceso where flujo_proceso_id in (select id from flujo_proceso where flujo_tipo_id=". $entity->getFlujoTipo()->getId() . " and orden>=". $entity->getOrden() .")");
                    $query->execute();
                    $query = $em->getConnection()->prepare("delete from flujo_proceso where flujo_tipo_id=". $entity->getFlujoTipo()->getId() . " and orden>=". $entity->getOrden());
                    $query->execute();
                    /*$em->remove($entity);
                    $em->flush();*/
                    $mensaje = 'Las tareas se elimaron con éxito';
                    $request->getSession()
                        ->getFlashBag()
                        ->add('exito', $mensaje);
                }
                
            }
        //return $this->redirect($this->generateUrl('flujotipo'));
        $data = $this->listarT($id_flujotipo);
        //dump($data);die;
        return $this->render('SieProcesosBundle:FlujoProceso:tablaTarea.html.twig',$data);
        
    }

    function editarPasosAction(Request $request)
    {   
        //dump($request);die;
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:WfPasosFlujoProceso')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar la entidad WfTareaCompuerta.');
        }
        $id_flujotipo = $entity->getFlujoProceso()->getFlujoTipo()->getId();
        if(strpos($entity->getFlujoProceso()->getFlujoTipo()->getObs(),"ACTIVO")!==false)
        {
            $mensaje = 'No se puede modificar los datos, pues tiene un proceso ACTIVO';
            $response = new JsonResponse();
            return $response->setData(array('mensaje' => $mensaje));
        }else{
            $form = $this->editarPasosForm($entity);
            return $this->render('SieProcesosBundle:FlujoProceso:editarPasos.html.twig',array(
                'form'=>$form->createView()));
        }
    }
    public function editarPasosForm($entity)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('SieAppWebBundle:FlujoProceso');
        $query = $repository->createQueryBuilder('fp')
            ->select('fp.id,pt.procesoTipo')
            ->innerJoin('SieAppWebBundle:flujoTipo', 'ft', 'WITH', 'fp.flujoTipo = ft.id')
            ->innerJoin('SieAppWebBundle:procesoTipo', 'pt', 'WITH', 'pt.id = fp.proceso')
            ->where('ft.id = '. $entity->getFlujoProceso()->getFlujoTipo()->getId())
            ->orderBy('fp.id','ASC')
            ->getQuery();
        $tarea = $query->getResult();
        $query = $em->getRepository('SieAppWebBundle:WfPasosTipo')->createQueryBuilder('wfp')
            ->select('wfp.id,wfp.nombre')
            ->getQuery();
        $pasos = $query->getResult();
        $tareasArray = array();
        $pasosArray = array();
        //dump($tarea);die;
        foreach($tarea as $t){
            $tareasArray[$t['id']]=$t['procesoTipo'];
        }
        foreach($pasos as $p){
            $pasosArray[$p['id']]=$p['nombre'];
        }
        //dump($entity->getWfPasosTipo()->getId());die;
        $form = $this->createFormBuilder()
            //->setAction($this->generateUrl('flujoproceso_guardar_pasos'))
            ->add('id','hidden',array('data'=>$entity->getId()))
            ->add('ptarea_edit','choice',array('label'=>'Tarea','required'=>true,'data'=>$entity->getFlujoProceso()->getId(),'choices'=>$tareasArray))
            ->add('nombre_edit','text',array('label'=>'nombre','required'=>true, 'data'=>$entity->getNombre()))
            ->add('posicion_edit','text',array('label'=>'posicion','required'=>true,'data'=>$entity->getPosicion()))
            ->add('tipopaso_edit','choice',array('label'=>'Tipo de asignación de tarea','required'=>true,'data'=>$entity->getWfPasosTipo()->getId(),'choices'=>$pasosArray))
            ->getForm();
        return $form;
    }
    function updatePasosAction(Request $request)
    {   
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:WfPasosFlujoProceso')->find($form['id']);
        //dump($entity);die;
        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar la entidad WfPasosFlujoProceso.');
        }
        if(strpos($entity->getFlujoProceso()->getFlujoTipo()->getObs(),"ACTIVO")!==false)
        {
            $mensaje = 'No se puede modificar el paso, pues tiene un proceso ACTIVO';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);    
        }else{
            //dump($entity->getFlujoProceso()->getId());die;
            $wfpfp = $em->getRepository('SieAppWebBundle:WfPasosFlujoProceso')->createQueryBuilder('wfpfp')
                ->select('wfpfp')
                ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'WITH', 'wfpfp.flujoProceso = fp.id')
                ->where('fp.id ='. $entity->getFlujoProceso()->getId())
                ->andWhere('wfpfp.posicion=' . $form['posicion_edit'])
                ->getQuery()
                ->getResult();
            //dump($wfpfp);die;
            if($wfpfp){
                $mensaje = 'La posición ' . $form['posicion_edit'] . ', ya fué registrada para la tarea "'. $entity->getFlujoProceso()->getProceso()->getProcesoTipo() . '"';
                $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje);    
            }else{
                $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($form['ptarea_edit']);
                $wfpasostipo = $em->getRepository('SieAppWebBundle:WfPasosTipo')->find($form['tipopaso_edit']);
                //dump($wfpasostipo);die;
                $entity->setNombre($form['nombre_edit']);
                
                $entity->setPosicion((int)$form['posicion_edit']);
                //dump($entity);die;
                $entity->setWfPasosTipo($wfpasostipo);
                $entity->setFlujoProceso($flujoproceso);
                //dump($entity);die;
                $em->flush();
                //dump($entity);die;
                $mensaje = 'Los datos se modificaron con éxito';
                $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje);
            }
                //dump($entity);die;
        }     
        //return $this->redirect($this->generateUrl('flujotipo'));
        $data = $this->listarP($entity->getFlujoProceso()->getFlujotipo()->getId());
        return $this->render('SieProcesosBundle:FlujoProceso:tablaPasos.html.twig',$data);
        
    }
    function editarTareaAction(Request $request)
    {   
        //dump($request);die;
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($id);
        //dump($entity->getProceso()->getProcesoTipo());die;
        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar la entidad FlujoProceso.');
        }
        $id_flujotipo = $entity->getFlujoTipo()->getId();
        if(strpos($entity->getFlujoTipo()->getObs(),"ACTIVO")!==false)
        {
            $mensaje = 'No se puede modificar los datos, pues tiene un proceso ACTIVO';
            $response = new JsonResponse();
            return $response->setData(array('mensaje' => $mensaje));   
        }else{
            $form = $this->editarTareaForm($entity);
            return $this->render('SieProcesosBundle:FlujoProceso:editarTarea.html.twig',array(
                'form'=>$form->createView()));
        }
    }

    public function editarTareaForm($entity)
    {
        //dump($entity);die;
        $em = $this->getDoctrine()->getManager();
        $rol = $em->getRepository('SieAppWebBundle:RolTipo')->findBy(array(),array('rol' => 'ASC'));
        $tipoasignacion = $em->getRepository('SieAppWebBundle:WfAsignacionTareaTipo')->find(1);
        $rolArray = array();
        //dump($rol);die;
        foreach($rol as $r){
            //dump($r);die;
            $rolArray[$r->getId()]=$r->getRol();
        }
        //dump($tipoasignacion);die;
        //$tipoasignacionArray = array();
        $tipoasignacionArray = array($tipoasignacion->getId()=>$tipoasignacion->getNombre());
        /* foreach($tipoasignacion as $t){
            $tipoasignacionArray[$t->getId()]=$t->getNombre();
        } */
        //dump($tipoasignacionArray);die;
        //dump($entity->getWfPasosTipo()->getId());die;
        $form = $this->createFormBuilder()
            //->setAction($this->generateUrl('flujoproceso_guardar_pasos'))
            ->add('id','hidden',array('data'=>$entity->getId()))
            ->add('rol_edit','choice',array('label'=>'Tipo de rol','required'=>true,'data'=>$entity->getRolTipo()->getId(),'choices'=>$rolArray))
            ->add('observacion_edit','text',array('label'=>'Observación', 'required'=>false,'data'=>$entity->getObs()))
            ->add('asignacion_edit','choice',array('label'=>'Tipo de asignación de tarea','required'=>true,'data'=>$entity->getWfAsignacionTareaTipo()->getId(),'choices'=>$tipoasignacionArray))
            ->add('evaluacion_edit','choice',array('label'=>'Evaluación','required'=>true,'data'=>$entity->getEsEvaluacion(),'choices'=>array(true => 'SI',false => 'NO')))
            ->add('varevaluacion_edit','text',array('label'=>'Variable a evaluar','required'=>false,'data'=>$entity->getVariableEvaluacion(),'attr'=>array('style'=>'text-transform:uppercase')))
            ->add('plazo_edit','text',array('label'=>'Plazo','required'=>false,'data'=>$entity->getPlazo()))
            ->add('ruta_edit','text',array('label'=>'Ruta de la tarea','required'=>false,'data'=>$entity->getRutaFormulario(),'attr'=>array('style'=>'text-transform:lowercase')))
            ->add('rutaReporte_edit','text',array('label'=>'Ruta del reporte:','required'=>false,'data'=>$entity->getRutaReporte(),'attr'=>array('style'=>'text-transform:lowercase')))
            ->getForm();
            //dump($form);die;
        
            return $form;
    }

    function updateTareaAction(Request $request)
    {   
        $form = $request->get('form');
        //dump($form);die;
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($form['id']);
        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar la entidad FlujoProceso.');
        }
        if(strpos($entity->getFlujoTipo()->getObs(),"ACTIVO")!==false)
        {
            $mensaje = 'No se puede modificar el paso, pues tiene un proceso ACTIVO';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);    
        }else{
                $rol = $em->getRepository('SieAppWebBundle:RolTipo')->find($form['rol_edit']);
                //dump($rol);die;
                $wfasignaciontareatipo = $em->getRepository('SieAppWebBundle:WfAsignacionTareaTipo')->find($form['asignacion_edit']);
                //dump($rol);die;
                $entity->setRolTipo($rol);
                $entity->setObs($form['observacion_edit']);
                if($form['plazo_edit']==""){
                    $entity->setPlazo(null);    
                }else{
                    $entity->setPlazo((int)$form['plazo_edit']);
                }
                $entity->setWfAsignacionTareaTipo($wfasignaciontareatipo);
                if($form['evaluacion_edit'] == 1){
                    $entity->setVariableEvaluacion(strtoupper($form['varevaluacion_edit']));
                    $entity->setTareaSigId($form['id']);
                }else{
                    $entity->setVariableEvaluacion("");
                    $query = $em->getConnection()->prepare("delete from wf_tarea_compuerta where flujo_proceso_id=" . $entity->getId());
                    $query->execute();
                }
                $entity->setEsEvaluacion($form['evaluacion_edit']);
                $entity->setRutaFormulario($form['ruta_edit']);
                $entity->setRutaReporte($form['rutaReporte_edit']);
                $em->flush();
                //dump($entity);die;
                $mensaje = 'Los datos se modificaron con éxito';
                $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje);
            }
        //return $this->redirect($this->generateUrl('flujotipo'));
        $data = $this->listarT($entity->getFlujoTipo()->getId());
        return $this->render('SieProcesosBundle:FlujoProceso:tablaTarea.html.twig',$data);
        
    }

    function editarCondicionAction(Request $request)
    {   
        //dump($request);die;
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->find($id);
        //dump($entity);die;
        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar la entidad WfTareaCompuerta.');
        }
        if(strpos($entity->getFlujoProceso()->getFlujoTipo()->getObs(),"ACTIVO")!==false)
        {
            $mensaje = 'No se puede modificar los datos, pues tiene un proceso ACTIVO';
            $response = new JsonResponse();
            return $response->setData(array('mensaje' => $mensaje));   
        }else{
            $form = $this->editarCondicionForm($entity);
            return $this->render('SieProcesosBundle:FlujoProceso:editarCondicion.html.twig',array(
                'form'=>$form->createView()));
        }
    }

    public function editarCondicionForm($entity)
    {
        //dump($entity->getEsEvaluacion());die;
        $ctareasArray = array();
        $tareasArray = array();
        $wfcompuertaArray = array();
        $em = $this->getDoctrine()->getManager();
        $ctarea = $em->getRepository('SieAppWebBundle:FlujoProceso')->createQueryBuilder('fp')
            ->select('fp.id,pt.procesoTipo')
            ->innerJoin('SieAppWebBundle:procesoTipo', 'pt', 'WITH', 'pt.id = fp.proceso')
            ->innerJoin('SieAppWebBundle:FlujoTipo', 'ft', 'WITH', 'ft.id = fp.flujoTipo')
            ->where('ft.id = '. $entity->getFlujoProceso()->getFlujoTipo()->getId())
            ->andWhere('fp.esEvaluacion = true')
            ->orderBy('fp.id','ASC')
            ->getQuery()
            ->getResult();
        //dump($ctarea);die;
        foreach($ctarea as $ct){
            $ctareasArray[$ct['id']] = $ct['procesoTipo'];
        }
        //dump($ctareasArray);die;
        $tarea = $em->getRepository('SieAppWebBundle:FlujoProceso')->createQueryBuilder('fp')
            ->select('fp.id,pt.procesoTipo')
            ->innerJoin('SieAppWebBundle:procesoTipo', 'pt', 'WITH', 'pt.id = fp.proceso')
            ->innerJoin('SieAppWebBundle:FlujoTipo', 'ft', 'WITH', 'ft.id = fp.flujoTipo')
            ->where('ft.id = '. $entity->getFlujoProceso()->getFlujoTipo()->getId())
            ->orderBy('fp.id','ASC')
            ->getQuery()
            ->getResult();
        //dump($tarea);die;
        foreach($tarea as $t){
            $tareasArray[$t['id']] = $t['procesoTipo'];
        }
        $wfc = $em->getRepository('SieAppWebBundle:WfCompuerta')->find(3);
        $wfcompuertaArray[$wfc->getId()] = $wfc->getNombre();
        /* $wfcompuerta = $em->getRepository('SieAppWebBundle:WfCompuerta')->findAll();
        foreach($wfcompuerta as $wfc){
            $wfcompuertaArray[$wfc->getId()] = $wfc->getNombre();
        } */
        $form = $this->createFormBuilder()
            ->add('id','hidden',array('data'=>$entity->getId()))
            ->add('ctarea_edit','choice',array('label'=>'Tarea','required'=>true,'data'=>$entity->getFlujoProceso()->getId(),'choices'=>$ctareasArray))
            ->add('condiciones_edit','choice',array('label'=>'Condición','required'=>true,'data'=>$entity->getCondicion(),'choices'=>array('SI' => 'SI','NO' => 'NO')))
            ->add('ctareasig_edit','choice',array('label'=>'Tarea','required'=>true,'data'=>$entity->getCondicionTareaSiguiente(),'choices'=>$tareasArray))
            ->add('tipocondicion_edit','choice',array('label'=>'Tipo condición','required'=>true,'data'=>$entity->getWfCompuerta()->getId(),'choices'=>$wfcompuertaArray))
            ->getForm();
        return $form;
    }

    function updateCondicionAction(Request $request)
    {   
        $form = $request->get('form');
        //dump($form);die;
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->find($form['id']);
        if (!$entity) {
            throw $this->createNotFoundException('No se puede encontrar la entidad FlujoProceso.');
        }
        if(strpos($entity->getFlujoProceso()->getFlujoTipo()->getObs(),"ACTIVO")!==false)
        {
            $mensaje = 'No se puede modificar el paso, pues tiene un proceso ACTIVO';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);    
        }else{
                $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($form['ctarea_edit']);
                //dump($rol);die;
                $wfcompuerta = $em->getRepository('SieAppWebBundle:WfCompuerta')->find($form['tipocondicion_edit']);
                //dump($rol);die;
                $entity->setFlujoProceso($flujoproceso);
                $entity->setWfCompuerta($wfcompuerta);
                $entity->setCondicion($form['condiciones_edit']);
                $entity->setCondicionTareaSiguiente($form['ctareasig_edit']);
                $em->flush();
                //dump($entity);die;
                $mensaje = 'Los datos se modificaron con éxito';
                $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje);
            }
        //return $this->redirect($this->generateUrl('flujotipo'));
        $data = $this->listarC($entity->getFlujoProceso()->getFlujoTipo()->getId());
        return $this->render('SieProcesosBundle:FlujoProceso:tablaCondicion.html.twig',$data);
    }

    public function buscarUsuariosAction(Request $request)
    {
        //dump($request);die;
        
    }
}