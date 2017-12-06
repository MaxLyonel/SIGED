<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class EstadisticasController extends Controller
{
    private $session;
    
    public function __construct() {        
        $this->session = new Session();        
    }
    
    public function indexAction(){
        $id_usuario = $this->session->get('userId');
       
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieHerramientaAlternativaBundle:Default:index.html.twig');
    }
    
    public function deptoestadsiticasAction(){
        $id_usuario = $this->session->get('userId');
       
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieHerramientaAlternativaBundle:Default:index.html.twig');
    }
    
    public function ceasstdcierreAction(Request $request) {
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //$sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $query = "   select  distrito_cod, des_dis, count(distinct institucioneducativa_id) as centros, sum(cast(primaria as integer)) as primaria,sum(cast(secundaria as integer)) as secundaria,sum(cast(basico as integer)) as basico,sum(cast(auxiliar as integer)) as auxiliar,sum(cast(medio as integer))as medio from (
                        select distrito_cod, des_dis,institucioneducativa_id,nivel_id,facultad_area,ciclo_id,especialidad, grado_id, acreditacion,
                        case
                           when nivel_id ='15' and ciclo_id = '1' then
                                    cast(sum(totins) as character varying)
                           else '0'      
                        end as primaria,

                        case  
                           when nivel_id ='15' and ciclo_id = '2' then
                                    cast(sum(totins) as character varying)
                           else '0'     
                        end as secundaria,

                        case  
                           when nivel_id in ('18','19','20','21','22','23','24','25') and grado_id = '1' then
                                    cast(sum(totins) as character varying)
                           else '0'     
                        end as basico,

                        case 
                           when nivel_id in ('18','19','20','21','22','23','24','25') and grado_id = '2' then 	 
                                    cast(sum(totins) as character varying)
                           else '0'      
                        end as auxiliar,

                        case
                           when nivel_id in ('18','19','20','21','22','23','24','25') and grado_id = '3' then	 
                                    cast(sum(totins) as character varying)
                           else '0'     
                        end as medio
                        from (
                        select distrito_cod,des_dis,ieue,institucioneducativa,gestion_tipo_id,periodo_tipo_id,institucioneducativa_id,nivel_id,facultad_area,ciclo_id,especialidad, grado_id, acreditacion, sum(matricula) as totins from(
                                select * from (                                   
                                                           select
                                                            k.lugar,
                                                            b.distrito_cod,            
                                                            b.des_dis,
                                                            d.id as ieue,	
                                                            d.institucioneducativa
                                                            from jurisdiccion_geografica a 
                                                                    inner join (
                                                                            select id, lugar_tipo_id, codigo as distrito_cod, lugar as des_dis 
                                                                                    from lugar_tipo
                                                                            where lugar_nivel_id=7 
                                                                    ) b 	
                                                                    on a.lugar_tipo_id_distrito = b.id
                                                                    inner join (

                                                                            select a.id, a.institucioneducativa, a.le_juridicciongeografica_id				
                                                                            from institucioneducativa a
                                                                                inner join institucioneducativa_sucursal z on a.id = z.institucioneducativa_id 
                                                                                inner join institucioneducativa_sucursal_tramite w on w.institucioneducativa_sucursal_id = z.id                      
                                                                            where a.orgcurricular_tipo_id = 2
                                                                            and z.gestion_tipo_id = '2016'
                                                                            and z.periodo_tipo_id = '2'
                                                                            and w.tramite_estado_id in (8,14) 
                                                                    ) d	
                                                                    on a.id=d.le_juridicciongeografica_id
                                                                    inner join lugar_tipo k on k.id = b.lugar_tipo_id
                                                            group by k.id, k.lugar, b.distrito_cod, b.des_dis, d.id, d.institucioneducativa
                                                            order by k.lugar, d.id) auxuno

                        inner join 
                        (                              
                        select gestion_tipo_id,periodo_tipo_id,institucioneducativa_id,nivel_id,facultad_area,ciclo_id,especialidad,grado_id,acreditacion,count(codigo_rude) as matricula from (
                        select f.gestion_tipo_id,f.periodo_tipo_id,e.institucioneducativa_id,a.codigo as nivel_id, a.facultad_area,b.codigo as ciclo_id,b.especialidad,d.codigo as grado_id,d.acreditacion,j.codigo_rude
                         from superior_facultad_area_tipo a  
                            inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                                inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                                    inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                                        inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                                            inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id 
                                                inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id 
                                                    inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id=g.id 
                                                        inner join estudiante_inscripcion i on h.id=i.institucioneducativa_curso_id 
                                                            inner join estudiante j on i.estudiante_id=j.id
                                                                inner join superior_modulo_periodo k on g.id=k.institucioneducativa_periodo_id
                                                                        inner join institucioneducativa_curso_oferta m on m.superior_modulo_periodo_id=k.id  
                                                                        and m.insitucioneducativa_curso_id=h.id 
                                                                            inner join estudiante_asignatura n on n.institucioneducativa_curso_oferta_id=m.id 
                                                                            and n.estudiante_inscripcion_id=i.id 
                                                                                inner join estudiante_nota o on o.estudiante_asignatura_id=n.id 
                        inner join paralelo_tipo p on h.paralelo_tipo_id=p.id
                        inner join turno_tipo q on h.turno_tipo_id=q.id
                        where f.gestion_tipo_id=2016 and f.periodo_tipo_id=2
                        group by f.gestion_tipo_id,f.periodo_tipo_id,e.institucioneducativa_id,a.codigo, a.facultad_area,b.codigo,b.especialidad,d.codigo,d.acreditacion,j.codigo_rude
                        )ad
                        group by gestion_tipo_id,periodo_tipo_id,institucioneducativa_id,nivel_id,facultad_area,ciclo_id,especialidad,grado_id,acreditacion
                        ) auxdos ON auxuno.ieue = auxdos.institucioneducativa_id
                        where lugar = 'Pando'
                        order by lugar,distrito_cod,ieue,nivel_id,ciclo_id,grado_id
                        )baseuno
                        group by distrito_cod,des_dis,ieue,institucioneducativa,gestion_tipo_id,periodo_tipo_id,institucioneducativa_id,nivel_id,facultad_area,ciclo_id,especialidad, grado_id, acreditacion
                        order by gestion_tipo_id,distrito_cod,des_dis, ieue, nivel_id,ciclo_id,grado_id
                        ) abfr
                        group by distrito_cod, des_dis,institucioneducativa_id,nivel_id,facultad_area,ciclo_id,especialidad, grado_id, acreditacion
                        order by distrito_cod,des_dis
                        ) baseuno
                        group by distrito_cod, des_dis";        
        $porcentajes = $db->prepare($query);
        $params = array();
        $porcentajes->execute($params);
        $po = $porcentajes->fetchAll();
        //$po = array();                
//        dump($potot);
//        die;
        
        return $this->render($this->session->get('pathSystem') . ':Estadisticas:estadisticasinscritosniveldistrito.html.twig', array(
                'entities' => $po,
            ));
    } 
    
}