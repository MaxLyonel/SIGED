<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Entity\InstitucioneducativaSucursalTramite;
use Sie\AppWebBundle\Entity\InstitucioneducativaSucursal;
use Sie\AppWebBundle\Entity\Consolidacion;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\JurisdiccionGeografica;

/**
 * Cepead Controller
 */
class MaestroCepeadController extends Controller {

    public $session;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * index the request
     * @param Request $request
     * @return obj with the selected request 
     */
    public function indexAction() {
        $historialForm = $this->historialceasForm($this->session->get('roluser'),$this->session->get('ie_id'));
        //dump($historialForm);die;
        return $this->render($this->session->get('pathSystem') . ':MaestroCepead:seleccionarcea.html.twig',array(
            'form'=>$historialForm->createView(),
        ));
    }    



       
    //** NO BORRAR, LISTA LAS GESTIONES */

    public function historialceasForm($rol,$ie_id)
    {   
        $form = $this->createFormBuilder();
        if($rol==9 or $rol==10 or $rol==2){
            $gestion = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getGestionCea($ie_id);
            $subcea = $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->getSubceaGestion($ie_id,'');
           // dump($gestion);die;
            $gestionesArray = array();
            $subceasArray = array();
            //$provincia[-1] = '-Todos-';
    	    foreach ($gestion as $g) {
               // dump($g);die;
                $gestionesArray[$g['gestion']] = $g['gestion'];
            }
            foreach ($subcea as $sc) {
                //dump($sc);die;
                $subceasArray[$sc['sucursal']] = $sc['sucursal'];
            }
            $form=$form
                ->add('codsie','text',array('label'=>'Cod. SIE:','data'=>$ie_id,'read_only'=>true,'attr'=>array('class'=>'form-control')))
                ->add('gestion','choice',array('label'=>'Gestión:','required'=>true,'data'=>(new \DateTime())->format('Y'),'choices'=>$gestionesArray,'empty_value' => 'Todas','attr'=>array('class'=>'form-control')))
                ->add('subcea','choice',array('label'=>'Sucursal:','required'=>true,'choices'=>$subceasArray,'empty_value' => 'Todas','attr'=>array('class'=>'form-control')));
            
            //->setAction($this->generateUrl('sie_herramienta_alternativa_cepead_profes_observados'));
            }else{
            $form=$form
                ->add('codsie','text',array('label'=>'Cod. SIE:', 'attr'=>array('maxlength' => '8','class'=>'form-control')))
                ->add('gestion','choice',array('label'=>'Gestión:','required'=>true,'empty_value' => 'Todas','attr'=>array('class'=>'form-control')))
                ->add('subcea','choice',array('label'=>'Sucursal:','required'=>true,'empty_value' => 'Todas','attr'=>array('class'=>'form-control')));
            
            
        }
       // ->add($this->generateUrl('herramientalt_ceducativa_estadistiscas_cierre'))
        $form=$form
            ->add('semestre','entity',array('label'=>'Semestre:','required'=>false,'class'=>'SieAppWebBundle:PeriodoTipo','query_builder'=>function(EntityRepository $p){
                return $p->createQueryBuilder('p')->where('p.id=2 or p.id=3');},'property'=>'periodo','empty_value' => 'Todas','attr'=>array('class'=>'form-control')))
            ->add('buscar', 'submit', array('label'=> 'Buscar', 'attr'=>array('class'=>'form-control btn btn-success')))
            ->setAction($this->generateUrl('sie_herramienta_alternativa_cepead_profes_observados'))
            ->getForm();
        return $form;
    }









/*
    public function indexAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validationremoveInscriptionAction if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        dump($request);
        //die;


       dump($this->session->get('ie_gestion'));
               // die;
                $form = $request->get('form');
                if ( ($this->session->get('ie_gestion')) && ($form['Inputrude']) ){
                    $em = $this->getDoctrine()->getManager();
                    //$em = $this->getDoctrine()->getEntityManager();
                    $db = $em->getConnection();
                    $query = "
                        select  DISTINCT f.gestion_tipo_id,e.institucioneducativa_id,f.sucursal_tipo_id,a.codigo as nivel_id,a.facultad_area as nivel,b.codigo as ciclo_id,b.especialidad as ciclo,d.codigo as grado_id,d.acreditacion as grado
                        ,case f.periodo_tipo_id when 3 then 'SEGUNDO SEMESTRE' when 2 then 'PRIMER SEMESTRE' end as periodo
                        ,h.turno_tipo_id,x.paralelo, tt.turno
                        ,j.codigo_rude,j.paterno,j.materno,j.nombre, p.rda,m.normalista, m.estadomaestro_id, m.rol_tipo_id,m.cargo_tipo_id, case j.genero_tipo_id when 1 then 'Masculino' when 2 then 'Femenino' end as genero,j.fecha_nacimiento,j.carnet_identidad
                        ,case j.lugar_nac_tipo_id when 1 then 'CH' when 2 then 'LPZ' when 3 then 'CBB' when 4 then 'OR' when 5 then 'PTS' when 6 then 'TAR' when 7 then 'STZ' when 8 then 'BEN' when 9 then 'PND' end as lugar_nac
        
                                 from superior_facultad_area_tipo a
                                    inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                                        inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                                            inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                                                inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id
                                                    inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id
                                                        inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
                                                            inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id=g.id
                                                                inner join paralelo_tipo x on h.paralelo_tipo_id = x.id
                                                                inner join turno_tipo tt on h.turno_tipo_id=tt.id
                                                                inner join estudiante_inscripcion i on h.id=i.institucioneducativa_curso_id
                                                                    inner join estudiante j on i.estudiante_id=j.id
        
                                                                    INNER JOIN persona p on p.carnet=j.carnet_identidad
                                                                    INER JOIN maestro_inscripcion m on m.persona_id=p.id
                                where f.gestion_tipo_id=".$this->session->get('ie_gestion')." 
                                and f.institucioneducativa_id=".$this->session->get('ie_id')."
                                and f.sucursal_tipo_id=".$this->session->get('ie_subcea')." 
                                and f.periodo_tipo_id=".$this->session->get('ie_per_cod')." 
                                and m.cargo_tipo_id=0";
        
                  
                  
                  
                  
                  
                                $stmt = $db->prepare($query);
                    dump($query);
                   // die;
        
                    $params = array();
                    $stmt->execute($params);
                    $po = $stmt->fetchAll();
                    
                    
                    if (!$po) {
                        return $this->render($this->session->get('pathSystem') . ':Estudiante:alumnodirectfindrude.html.twig', array(
                            'personas' => $po,
                            'mensaje' => 'NO SE HA ENCUENTRA PRESENTE INSCRIPCIONES EN ESTE PERIODO PARA EL RUDE :'.$form['Inputrude'],
                        ));
                    }
                    else{
                        return $this->render($this->session->get('pathSystem') . ':Estudiante:alumnodirectfindrude.html.twig', array(
                            'personas' => $po,
                            'mensaje' => 'Inscripciones actuales para el rude :'.$form['Inputrude'],
                        ));
                    }
                }
                else{
                    $po = array();
                    return $this->render($this->session->get('pathSystem') . ':Estudiante:alumnodirectfindrude.html.twig', array(
                            'personas' => $po,
                            'mensaje' => '¡AUN NO SE HA SELECCIONADO CEA - GESTION - PERIODO - RUDE!',
                        ));
                }
            }
 */




    
    public function ProfesObservadosAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validationremoveInscriptionAction if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
       /* $form=$request->get('form');
        dump($form);
        die;*/
        

       dump($this->session->get('ie_gestion'));
               // die;
                $form = $request->get('form');
              //  if ( ($this->session->get('ie_gestion')) && ($form['Inputrude']) ){
                  if($form){
                    $em = $this->getDoctrine()->getManager();
                    //$em = $this->getDoctrine()->getEntityManager();
                    $db = $em->getConnection();
                    $query = "
                    select f.gestion_tipo_id,e.institucioneducativa_id,f.sucursal_tipo_id,a.codigo as nivel_id,a.facultad_area as nivel,b.codigo as ciclo_id,b.especialidad as ciclo,d.codigo as grado_id,d.acreditacion as grado
                    ,case f.periodo_tipo_id when 3 then 'SEGUNDO SEMESTRE' when 2 then 'PRIMER SEMESTRE' end as periodo
                    ,h.turno_tipo_id,x.paralelo, tt.turno
                    ,j.codigo_rude,j.paterno,j.materno,j.nombre, p.rda, p.activo, p.esvigente, case j.genero_tipo_id when 1 then 'Masculino' when 2 then 'Femenino' end as genero,j.fecha_nacimiento,j.carnet_identidad
                    ,case j.lugar_nac_tipo_id when 1 then 'CH' when 2 then 'LPZ' when 3 then 'CBB' when 4 then 'OR' when 5 then 'PTS' when 6 then 'TAR' when 7 then 'STZ' when 8 then 'BEN' when 9 then 'PND' end as lugar_nac

                     from superior_facultad_area_tipo a
                        inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                            inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                                inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                                    inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id
                                        inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id
                                            inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
                                                inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id=g.id
                                                    inner join paralelo_tipo x on h.paralelo_tipo_id = x.id
                                                    inner join turno_tipo tt on h.turno_tipo_id=tt.id
                                                    inner join estudiante_inscripcion i on h.id=i.institucioneducativa_curso_id
                                                        inner join estudiante j on i.estudiante_id=j.id
                                                                                        

                                                                                                                INNER JOIN persona p on p.carnet=j.carnet_identidad
                                                                                                            

                                                                                                                where f.gestion_tipo_id=".$form['gestion']." 
                                                                                                                and f.institucioneducativa_id=".$form['codsie']."
                                                                                                                and f.sucursal_tipo_id=".$form['subcea']." 
                                                                                                                and f.periodo_tipo_id=".$form['semestre']." 
                                            and p.rda!=0
                                        
                                        
                                            
                                            ORDER BY b.especialidad, d.acreditacion,j.paterno, j.materno, j.nombre";
                  
                  
                                $stmt = $db->prepare($query);
                  //  dump($query);
                   // die;
        
                    $params = array();
                    $stmt->execute($params);
                    $po = $stmt->fetchAll();

                   //   dump($po);
                  //  die;


                    $query2 = "
                   
                  
                                            select  DISTINCT f.gestion_tipo_id,e.institucioneducativa_id,f.sucursal_tipo_id,a.codigo as nivel_id,a.facultad_area as nivel,b.codigo as ciclo_id,b.especialidad as ciclo,d.codigo as grado_id,d.acreditacion as grado
                                            ,case f.periodo_tipo_id when 3 then 'SEGUNDO SEMESTRE' when 2 then 'PRIMER SEMESTRE' end as periodo
                                            ,h.turno_tipo_id,x.paralelo, tt.turno
                                            ,j.codigo_rude,j.paterno,j.materno,j.nombre, p.rda, case m.estadomaestro_id when 1 then 'Activo' end as normalista, case j.genero_tipo_id when 1 then 'Masculino' when 2 then 'Femenino' end as genero,j.fecha_nacimiento,j.carnet_identidad
                                            ,case j.lugar_nac_tipo_id when 1 then 'CH' when 2 then 'LPZ' when 3 then 'CBB' when 4 then 'OR' when 5 then 'PTS' when 6 then 'TAR' when 7 then 'STZ' when 8 then 'BEN' when 9 then 'PND' end as lugar_nac
                            
                                                     from superior_facultad_area_tipo a
                                                        inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                                                            inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                                                                inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                                                                    inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id
                                                                        inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id
                                                                            inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
                                                                                inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id=g.id
                                                                                    inner join paralelo_tipo x on h.paralelo_tipo_id = x.id
                                                                                    inner join turno_tipo tt on h.turno_tipo_id=tt.id
                                                                                    inner join estudiante_inscripcion i on h.id=i.institucioneducativa_curso_id
                                                                                        inner join estudiante j on i.estudiante_id=j.id
                            
                                                                                        INNER JOIN persona p on p.carnet=j.carnet_identidad
                                                                                        INNER JOIN maestro_inscripcion m on m.persona_id=p.id
                                                                                        where f.gestion_tipo_id=".$form['gestion']." 
                                                                                        and f.institucioneducativa_id=".$form['codsie']."
                                                                                        and f.sucursal_tipo_id=".$form['subcea']." 
                                                                                        and f.periodo_tipo_id=".$form['semestre']." 
                                                                                        and m.cargo_tipo_id=0
                            
                                      
                                                                                        ORDER BY b.especialidad, d.acreditacion,j.paterno, j.materno, j.nombre";
                                      
                  




                  
                                $stmt2 = $db->prepare($query2);
                    //dump($query2);
                 //die;
        
                    $params2 = array();
                    $stmt2->execute($params2);
                    $po2 = $stmt2->fetchAll();
                   // dump($po);
                  //  dump($po2);
                 //  die; 
                    

                 $query3 = "
                   
                  
               

                                                             select  DISTINCT f.gestion_tipo_id,e.institucioneducativa_id,f.sucursal_tipo_id,a.codigo as nivel_id,a.facultad_area as nivel,b.codigo as ciclo_id,b.especialidad as ciclo,d.codigo as grado_id,d.acreditacion as grado
                        ,case f.periodo_tipo_id when 3 then 'SEGUNDO SEMESTRE' when 2 then 'PRIMER SEMESTRE' end as periodo
                        ,h.turno_tipo_id,x.paralelo, tt.turno
                        ,j.codigo_rude,j.paterno,j.materno,j.nombre, p.rda, m.estadomaestro_id,m.cargo_tipo_id, case j.genero_tipo_id when 1 then 'Masculino' when 2 then 'Femenino' end as genero,j.fecha_nacimiento,j.carnet_identidad
                        ,case j.lugar_nac_tipo_id when 1 then 'CH' when 2 then 'LPZ' when 3 then 'CBB' when 4 then 'OR' when 5 then 'PTS' when 6 then 'TAR' when 7 then 'STZ' when 8 then 'BEN' when 9 then 'PND' end as lugar_nac
                         from superior_facultad_area_tipo a
                            inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                                inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                                    inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                                        inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id
                                            inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id
                                                inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
                                                    inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id=g.id
                                                        inner join paralelo_tipo x on h.paralelo_tipo_id = x.id
                                                        inner join turno_tipo tt on h.turno_tipo_id=tt.id
                                                        inner join estudiante_inscripcion i on h.id=i.institucioneducativa_curso_id
                                                            inner join estudiante j on i.estudiante_id=j.id
INNER JOIN persona p on p.carnet=j.carnet_identidad
INNER JOIN maestro_inscripcion m on m.persona_id=p.id
INNER JOIN (select  DISTINCT j.codigo_rude,e.institucioneducativa_id,f.sucursal_tipo_id
                         from superior_facultad_area_tipo a
                            inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                                inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                                    inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                                        inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id
                                            inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id
                                                inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id
                                                    inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id=g.id
                                                        inner join paralelo_tipo x on h.paralelo_tipo_id = x.id
                                                        inner join turno_tipo tt on h.turno_tipo_id=tt.id
                                                        inner join estudiante_inscripcion i on h.id=i.institucioneducativa_curso_id
                                                            inner join estudiante j on i.estudiante_id=j.id
INNER JOIN persona p on p.carnet=j.carnet_identidad
INNER JOIN maestro_inscripcion m on m.persona_id=p.id
where f.gestion_tipo_id=".$form['gestion']."
and f.institucioneducativa_id='80730796' 
and f.sucursal_tipo_id=".$form['subcea']."
and f.periodo_tipo_id=".$form['semestre']." 

) n on j.codigo_rude=n.codigo_rude and e.institucioneducativa_id<>n.institucioneducativa_id
where 
f.gestion_tipo_id=".$form['gestion']."

ORDER BY b.especialidad, d.acreditacion,j.paterno, j.materno, j.nombre";





     $stmt3 = $db->prepare($query3);
//dump($query2);
//die;

$params3 = array();
$stmt3->execute($params3);
$po3 = $stmt3->fetchAll();
// dump($po);
//  dump($po2);
//  die; 











                    if ($po or $po2 or $po3) {
                        return $this->render($this->session->get('pathSystem') . ':MaestroCepead:observado.html.twig', array(
                            'personas' => $po,
                            'personas2' => $po2,
                            'personas3' => $po3,


                           // 'mensaje' => 'NO SE HA ENCUENTRA PRESENTE INSCRIPCIONES EN ESTE PERIODO PARA EL RUDE :'.$form['Inputrude'],
                        ));
                    }
                    /*else{
                        return $this->render($this->session->get('pathSystem') . ':Estudiante:alumnodirectfindrude.html.twig', array(
                            'personas' => $po,
                            'mensaje' => 'Inscripciones actuales para el rude :'.$form['Inputrude'],
                        ));
                    }*/
                }
                else{
                    $po = array();
                    return $this->render($this->session->get('pathSystem') . ':Estudiante:alumnodirectfindrude.html.twig', array(
                            'personas' => $po,
                            'mensaje' => '¡AUN NO SE HA SELECCIONADO CEA - GESTION - PERIODO - RUDE!',
                        ));
                }
            }
 


}