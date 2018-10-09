<?php

namespace Sie\PnpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Sie\PnpBundle\Form\XlsType;
use Sie\PnpBundle\Form\PersonaType;
use Sie\PnpBundle\Form\FacilitadorType;
use Sie\PnpBundle\Form\RegistrarCursoType;
use Sie\PnpBundle\Form\MunicipioFiltroType;
use Sie\PnpBundle\Form\CursoType;
use Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\MaestroInscripcion;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoDatos;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\PersonaCarnetControl;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\PnpReconocimientoSaberes;
use Sie\AppWebBundle\Entity\UsuarioRol;
use Sie\AppWebBundle\Entity\InstitucioneducativaSucursal;
use Sie\AppWebBundle\Entity\DiscapacidadTipo;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Sie\AppWebBundle\Entity\AltModuloemergente;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAlternativa;

//use Sie\AppWebBundle\Entity\PnpSerialRude;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

use Sie\AppWebBundle\Form\BuscarPersonaType;

class DefaultController extends Controller
{
    public function __construct() {
        $this->session = new Session();
    }
    
    public function StringBetween($str,$from,$to)
    {
        //$sub = substr($str, strpos($str,$from)+strlen($from),strlen($str));
        //return substr($sub,2,strpos($sub,$to)-2);
        
        $a = substr($str,strpos($str,$from)-1,1);
        if ($a == '$'){
            $sub = substr($str, strpos($str,$from)+strlen($from),strlen($str));
            return substr($sub,2,strpos($sub,$to)-2);
        }
        else
            return '';
    }
    
    public function indexAction()
    {
        $form = $this->createForm(new XlsType(), null, array('action' => $this->generateUrl('sie_pnp_xls_form')));
        
        return $this->render('SiePnpBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

    public function pnp_indexAction()
    {   
        return $this->render('SiePnpBundle:Default:pnp_index.html.twig');
    }

    
    public function reportesfiltroAction()
    {
        $form = $this->createForm(new MunicipioFiltroType(), null, array('action' => $this->generateUrl('sie_pnp_reportesejecutar')));
        
        return $this->render('SiePnpBundle:Default:municipiofiltro.html.twig', array('form' => $form->createView()));
    }
    
    public function reportesprovinciaAction($ieid)
    {
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "
                    select b.lugar as provincia

                    from estudiante_inscripcion a 
                            inner join institucioneducativa_curso b on a.institucioneducativa_curso_id = b.id 
                            inner join estudiante c on a.estudiante_id = c.id

                    where b.institucioneducativa_id = '".$ieid."'

                    GROUP BY provincia

                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["provincia"] = $p["provincia"];            
            $filas[] = $datos_filas;
        }
//            
//            
//            
//              $sql = "SELECT distinct(terrain_id) FROM Partie;";

//    $rsm = new ResultSetMapping;
//    $rsm->addScalarResult('terrain_id', 'terrain_id');
//    $query = $this->_em->createNativeQuery($sql, $rsm);
//    var_dump($query->getSQL());
//    var_dump($query->getResult());
//    return $query->getResult();
//            
//            
//            
//            
//            
//        }
        
//        $em = $this->getDoctrine()->getManager();
//        $query = $em->createQuery('SELECT a.lugar as provincia
//                                  FROM Sie\AppWebBundle\Entity\InstitucioneducativaCurso a
//                                  WHERE a.institucioneducativa=:id     
//                                  GROUP by a.lugar'
//                                  );
//        $query->setParameter('id', $ieid);
//        $filas = $query->getResult();

        
        
        
//        $em = $this->getDoctrine()->getManager();
//        $entity = $em->getRepository('SenapeSabivBundle:Provincia')->findByIddepartamento($id);
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('message' => new JsonEncoder()));
        $jsonContent = $serializer->serialize($filas, 'json');
        echo $jsonContent;
        exit;
        
        //print_r($filas[3] );
        //die;
    }
    
    public function buscarprovinciaAction($ieid)
    {
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "
                    select id,lugar as provincia
                    from lugar_tipo 
                    where lugar_tipo.lugar_tipo_id = '".$ieid."'
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["id"] = $p["id"];            
            $datos_filas["provincia"] = $p["provincia"];            
            $filas[] = $datos_filas;
        }

        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('message' => new JsonEncoder()));
        $jsonContent = $serializer->serialize($filas, 'json');
        echo $jsonContent;
        exit;
    }

    public function buscarmunicipioAction($ieid)
    {
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "
                    select id,lugar as provincia
                    from lugar_tipo 
                    where lugar_tipo.lugar_tipo_id = '".$ieid."'
                    order by lugar 
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["idd"] = $p["idd"];            
            $datos_filas["provincia"] = $p["provincia"];            
            $filas[] = $datos_filas;
        }
        
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('message' => new JsonEncoder()));
        $jsonContent = $serializer->serialize($filas, 'json');
        echo $jsonContent;
        exit;
    }
    
    public function reportesmunicipioAction($ieid, $provincia)
    {
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "
                    select  a.lugar as municipio

                    from estudiante_inscripcion a 
                            inner join institucioneducativa_curso b on a.institucioneducativa_curso_id = b.id 

                    where
                    b.institucioneducativa_id = ".$ieid." and
                    b.lugar = '".$provincia."'

                    GROUP BY municipio

                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["municipio"] = $p["municipio"];            
            $filas[] = $datos_filas;
        }
        
//        $em = $this->getDoctrine()->getManager();
//        $entity = $em->getRepository('SenapeSabivBundle:Provincia')->findByIddepartamento($id);
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('message' => new JsonEncoder()));
        $jsonContent = $serializer->serialize($filas, 'json');
        echo $jsonContent;
        exit;
        
        //print_r($filas[3] );
        //die;
    }
    
    public function reportesfiltroejecutarAction(Request $request)    {                
        
        $em = $this->getDoctrine()->getManager();        
        //$em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        $form = $request->get('sie_pnp_municipio_filtro');
//        print_r($form['depto']);
//        die;
        //TOTAL CONSOLIDACION
        $query = "
                    select 

                            case b.institucioneducativa_id when '80480300' then 'Chuquisaca' when '80730794' then 'La Paz' when '80980569' then 'Cochabamba' when '81230297' then 'Oruro' when '81480201' then 'Potosi'
                                    when '81730264' then 'Tarija' when '81981501' then 'Santa Cruz' when '82230130' then 'Beni' when '82480050' then 'Pando' end as depto,

                     b.lugar as provincia, a.lugar as municipio, b.id as cursoid,
                     'Primaria comunitaria vocacional' as nivel, b.ciclo_tipo_id as parte, b.grado_tipo_id as bloque,
                     b.fecha_inicio, b.fecha_fin, a.id as inscripcionid, b.facilitador as facilitadorci, a.facilitadorcurso as facilitador,
                     c.codigo_rude, c.carnet_identidad, c.nombre, c.paterno, c.materno, d.genero, c.fecha_nacimiento,
                     date_part('year',age( c.fecha_nacimiento )) as edadactual

                    from estudiante_inscripcion a 
                            inner join institucioneducativa_curso b on a.institucioneducativa_curso_id = b.id 
                            inner join estudiante c on a.estudiante_id = c.id
                            inner join genero_tipo d on c.genero_tipo_id = d.id 

                    where b.institucioneducativa_id = '".$form['depto']."'
                    and b.lugar = '".$form['provincia']."' and a.lugar = '".$form['municipio']."'

                    order by depto, provincia, municipio, ciclo_tipo_id, grado_tipo_id, nombre, paterno, materno, genero, edadactual

                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["provincia"] = $p["provincia"];
            $datos_filas["municipio"] = $p["municipio"];
            $datos_filas["cursoid"] = $p["cursoid"];
            $datos_filas["nivel"] = $p["nivel"];
            $datos_filas["parte"] = $p["parte"];
            $datos_filas["bloque"] = $p["bloque"];
            $datos_filas["fecha_inicio"] = $p["fecha_inicio"];
            $datos_filas["fecha_fin"] = $p["fecha_fin"];
            $datos_filas["inscripcionid"] = $p["inscripcionid"];
            $datos_filas["facilitadorci"] = $p["facilitadorci"];
            $datos_filas["facilitador"] = $p["facilitador"];
            $datos_filas["codigo_rude"] = $p["codigo_rude"];
            $datos_filas["carnet_identidad"] = $p["carnet_identidad"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["genero"] = $p["genero"];            
            $datos_filas["fecha_nacimiento"] = $p["fecha_nacimiento"];
            $datos_filas["edadactual"] = $p["edadactual"];
            $filas[] = $datos_filas;
        } 
        
        return $this->render('SiePnpBundle:Estadisticas:reporte.html.twig', array('filas' => $filas));
    }
    
    
    public function estadisticasAction()    {        
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        //LISTA DE NOTAS
//        $query = "select 
//                       case institucioneducativa_id when '80480300' then '1:Chuquisaca' when '80730794' then '2:La Paz' when '80980569' then '3:Cochabamba' when '81230297' then '4:Oruro' when '81480201' then '5:Potosi'
//                when '81730264' then '6:Tarija' when '81981501' then '7:Santa Cruz' when '82230130' then '8:Beni' when '82480050' then '9:Pando' end as depto, b.gestion_tipo_id,
//                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 1
//                                THEN 1
//                                ELSE 0 
//                             END       )
//                        AS Masculino,
//                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 2
//                                THEN 1
//                                ELSE 0 
//                             END       )
//                        AS Femenino,
//                        SUM(CASE WHEN b.nivel_tipo_id is not null
//                                THEN 1
//                                ELSE 0 
//                             END       )
//                        AS Total
//
//                from 
//
//                institucioneducativa_curso b inner join estudiante_inscripcion c on b.id=c.institucioneducativa_curso_id 
//                inner join estudiante e on c.estudiante_id = e.id
//                inner join estadomatricula_tipo d on c.estadomatricula_tipo_id=d.id
//                inner join estadomatricula_tipo f on c.estadomatricula_inicio_tipo_id=f.id
//                where institucioneducativa_id IN ('82230130')
//                and b.nivel_tipo_id = '12'
//                group by depto, b.gestion_tipo_id
//                order by b.gestion_tipo_id
//                ";
//        $stmt = $db->prepare($query);
//        $params = array();
//        $stmt->execute($params);
//        $po = $stmt->fetchAll();
//        $filas = array();
//        $datos_filas = array();
////        print_r(count($po));
////        die;
//        foreach ($po as $p) {
//            $datos_filas["depto"] = $p["depto"];
//            $datos_filas["gestion_tipo_id"] = $p["gestion_tipo_id"];
//            $datos_filas["masculino"] = $p["masculino"];            
//            $datos_filas["femenino"] = $p["femenino"];
//            $datos_filas["total"] = $p["total"];
//            $filas[] = $datos_filas;
//        }  
        //print_r($filas[2]);
        //die; 
        
        
        
        //TOTAL CONSOLIDACION
        $query = "
                    select
                        case institucioneducativa_id when '80480300' then 'BO-H' when '80730794' then 'BO-L' when '80980569' then 'BO-C' when '81230297' then 'BO-O' when '81480201' then 'BO-P'
                        when '81730264' then 'BO-T' when '81981501' then 'BO-S' when '82230130' then 'BO-B' when '82480050' then 'BO-N' end as v,

                        case institucioneducativa_id when '80480300' then 'Chuquisaca' when '80730794' then 'La Paz' when '80980569' then 'Cochabamba' when '81230297' then 'Oruro' when '81480201' then 'Potosí'
                        when '81730264' then 'Tarija' when '81981501' then 'Santa Cruz' when '82230130' then 'Beni' when '82480050' then 'Pando' end as f,

                        SUM(CASE WHEN b.nivel_tipo_id is not null
                                THEN 1
                                ELSE 0 
                        END)
                        AS t,


                        to_char(SUM(CASE WHEN b.nivel_tipo_id is not null
                                THEN 1
                                ELSE 0 
                        END       )
                        ,'FM999,FM999')   AS d

                from 

                institucioneducativa_curso b inner join estudiante_inscripcion c on b.id=c.institucioneducativa_curso_id 
                inner join estudiante e on c.estudiante_id = e.id
                inner join estadomatricula_tipo d on c.estadomatricula_tipo_id=d.id
                inner join estadomatricula_tipo f on c.estadomatricula_inicio_tipo_id=f.id

                where institucioneducativa_id IN ('80480300','80730794','80980569','81230297','81480201','81730264','81981501','82230130','82480050')
                and b.nivel_tipo_id = '312'
                group by institucioneducativa_id, f

                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["v"] = $p["v"];
            $datos_filas["f"] = $p["f"];
            $datos_filas["t"] = $p["t"];
            $datos_filas["d"] = $p["d"];
            $filas[] = $datos_filas;
        } 

        //DEPARTAMENTO GESTION
        $query = "
                select
                        case institucioneducativa_id when '80480300' then 'Chuquisaca' when '80730794' then 'La Paz' when '80980569' then 'Cochabamba' when '81230297' then 'Oruro' when '81480201' then 'Potosí'
                        when '81730264' then 'Tarija' when '81981501' then 'Santa Cruz' when '82230130' then 'Beni' when '82480050' then 'Pando' end as depto,

                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 2
                                THEN 1
                                ELSE 0 
                        END) AS femenino,

                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 1
                                THEN 1
                                ELSE 0 
                        END)AS masculino,

                        SUM(CASE WHEN b.nivel_tipo_id is not null
                                THEN 1
                                ELSE 0 
                        END)  AS total

                from 

                institucioneducativa_curso b inner join estudiante_inscripcion c on b.id=c.institucioneducativa_curso_id 
                inner join estudiante e on c.estudiante_id = e.id
                inner join estadomatricula_tipo d on c.estadomatricula_tipo_id=d.id
                inner join estadomatricula_tipo f on c.estadomatricula_inicio_tipo_id=f.id

                where institucioneducativa_id IN ('80480300','80730794','80980569','81230297','81480201','81730264','81981501','82230130','82480050')
                and b.nivel_tipo_id = '312'
                group by institucioneducativa_id, depto
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filasgestion = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["femenino"] = $p["femenino"];
            $datos_filas["masculino"] = $p["masculino"];
            $datos_filas["total"] = $p["total"];
            $filasgestion[] = $datos_filas;
        }
        
        
        
        //DEPARTAMENTO GESTION
        //chuquisaca
        $query = "
                select 
                       case institucioneducativa_id when '80480300' then '1:Chuquisaca' when '80730794' then '2:La Paz' when '80980569' then '3:Cochabamba' when '81230297' then '4:Oruro' when '81480201' then '5:Potosi'
                when '81730264' then '6:Tarija' when '81981501' then '7:Santa Cruz' when '82230130' then '8:Beni' when '82480050' then '9:Pando' end as depto, b.gestion_tipo_id,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 1
                                THEN 1
                                ELSE 0 
                             END       )
                        AS masculino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 2
                                THEN 1
                                ELSE 0 
                             END       )
                        AS femenino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null
                                THEN 1
                                ELSE 0 
                             END       )
                        AS total

                from 

                institucioneducativa_curso b inner join estudiante_inscripcion c on b.id=c.institucioneducativa_curso_id 
                inner join estudiante e on c.estudiante_id = e.id
                inner join estadomatricula_tipo d on c.estadomatricula_tipo_id=d.id
                inner join estadomatricula_tipo f on c.estadomatricula_inicio_tipo_id=f.id
                where institucioneducativa_id IN ('80480300')
                and b.nivel_tipo_id = '312'
                group by depto, b.gestion_tipo_id
                order by b.gestion_tipo_id
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filaschu = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["gestion_tipo_id"] = $p["gestion_tipo_id"];
            $datos_filas["femenino"] = $p["femenino"];
            $datos_filas["masculino"] = $p["masculino"];
            $datos_filas["total"] = $p["total"];
            $filaschu[] = $datos_filas;
        }
        
        //lp
        $query = "
                select 
                       case institucioneducativa_id when '80480300' then '1:Chuquisaca' when '80730794' then '2:La Paz' when '80980569' then '3:Cochabamba' when '81230297' then '4:Oruro' when '81480201' then '5:Potosi'
                when '81730264' then '6:Tarija' when '81981501' then '7:Santa Cruz' when '82230130' then '8:Beni' when '82480050' then '9:Pando' end as depto, b.gestion_tipo_id,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 1
                                THEN 1
                                ELSE 0 
                             END       )
                        AS masculino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 2
                                THEN 1
                                ELSE 0 
                             END       )
                        AS femenino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null
                                THEN 1
                                ELSE 0 
                             END       )
                        AS total

                from 

                institucioneducativa_curso b inner join estudiante_inscripcion c on b.id=c.institucioneducativa_curso_id 
                inner join estudiante e on c.estudiante_id = e.id
                inner join estadomatricula_tipo d on c.estadomatricula_tipo_id=d.id
                inner join estadomatricula_tipo f on c.estadomatricula_inicio_tipo_id=f.id
                where institucioneducativa_id IN ('80730794')
                and b.nivel_tipo_id = '312'
                group by depto, b.gestion_tipo_id
                order by b.gestion_tipo_id
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filaslp = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["gestion_tipo_id"] = $p["gestion_tipo_id"];
            $datos_filas["femenino"] = $p["femenino"];
            $datos_filas["masculino"] = $p["masculino"];
            $datos_filas["total"] = $p["total"];
            $filaslp[] = $datos_filas;
        }
        
        //cocha
        $query = "
                select 
                       case institucioneducativa_id when '80480300' then '1:Chuquisaca' when '80730794' then '2:La Paz' when '80980569' then '3:Cochabamba' when '81230297' then '4:Oruro' when '81480201' then '5:Potosi'
                when '81730264' then '6:Tarija' when '81981501' then '7:Santa Cruz' when '82230130' then '8:Beni' when '82480050' then '9:Pando' end as depto, b.gestion_tipo_id,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 1
                                THEN 1
                                ELSE 0 
                             END       )
                        AS masculino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 2
                                THEN 1
                                ELSE 0 
                             END       )
                        AS femenino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null
                                THEN 1
                                ELSE 0 
                             END       )
                        AS total

                from 

                institucioneducativa_curso b inner join estudiante_inscripcion c on b.id=c.institucioneducativa_curso_id 
                inner join estudiante e on c.estudiante_id = e.id
                inner join estadomatricula_tipo d on c.estadomatricula_tipo_id=d.id
                inner join estadomatricula_tipo f on c.estadomatricula_inicio_tipo_id=f.id
                where institucioneducativa_id IN ('80980569')
                and b.nivel_tipo_id = '312'
                group by depto, b.gestion_tipo_id
                order by b.gestion_tipo_id
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filasco = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["gestion_tipo_id"] = $p["gestion_tipo_id"];
            $datos_filas["femenino"] = $p["femenino"];
            $datos_filas["masculino"] = $p["masculino"];
            $datos_filas["total"] = $p["total"];
            $filasco[] = $datos_filas;
        }
        
        //oruro
        $query = "
                select 
                       case institucioneducativa_id when '80480300' then '1:Chuquisaca' when '80730794' then '2:La Paz' when '80980569' then '3:Cochabamba' when '81230297' then '4:Oruro' when '81480201' then '5:Potosi'
                when '81730264' then '6:Tarija' when '81981501' then '7:Santa Cruz' when '82230130' then '8:Beni' when '82480050' then '9:Pando' end as depto, b.gestion_tipo_id,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 1
                                THEN 1
                                ELSE 0 
                             END       )
                        AS masculino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 2
                                THEN 1
                                ELSE 0 
                             END       )
                        AS femenino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null
                                THEN 1
                                ELSE 0 
                             END       )
                        AS total

                from 

                institucioneducativa_curso b inner join estudiante_inscripcion c on b.id=c.institucioneducativa_curso_id 
                inner join estudiante e on c.estudiante_id = e.id
                inner join estadomatricula_tipo d on c.estadomatricula_tipo_id=d.id
                inner join estadomatricula_tipo f on c.estadomatricula_inicio_tipo_id=f.id
                where institucioneducativa_id IN ('81230297')
                and b.nivel_tipo_id = '312'
                group by depto, b.gestion_tipo_id
                order by b.gestion_tipo_id
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filasor = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["gestion_tipo_id"] = $p["gestion_tipo_id"];
            $datos_filas["femenino"] = $p["femenino"];
            $datos_filas["masculino"] = $p["masculino"];
            $datos_filas["total"] = $p["total"];
            $filasor[] = $datos_filas;
        }
        
        //potosi
        $query = "
                select 
                       case institucioneducativa_id when '80480300' then '1:Chuquisaca' when '80730794' then '2:La Paz' when '80980569' then '3:Cochabamba' when '81230297' then '4:Oruro' when '81480201' then '5:Potosi'
                        when '81730264' then '6:Tarija' when '81981501' then '7:Santa Cruz' when '82230130' then '8:Beni' when '82480050' then '9:Pando' end as depto, b.gestion_tipo_id,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 1
                                THEN 1
                                ELSE 0 
                             END       )
                        AS masculino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 2
                                THEN 1
                                ELSE 0 
                             END       )
                        AS femenino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null
                                THEN 1
                                ELSE 0 
                             END       )
                        AS total

                from 

                institucioneducativa_curso b inner join estudiante_inscripcion c on b.id=c.institucioneducativa_curso_id 
                inner join estudiante e on c.estudiante_id = e.id
                inner join estadomatricula_tipo d on c.estadomatricula_tipo_id=d.id
                inner join estadomatricula_tipo f on c.estadomatricula_inicio_tipo_id=f.id
                where institucioneducativa_id IN ('81480201')
                and b.nivel_tipo_id = '312'
                group by depto, b.gestion_tipo_id
                order by b.gestion_tipo_id
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filaspt = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["gestion_tipo_id"] = $p["gestion_tipo_id"];
            $datos_filas["femenino"] = $p["femenino"];
            $datos_filas["masculino"] = $p["masculino"];
            $datos_filas["total"] = $p["total"];
            $filaspt[] = $datos_filas;
        }
        
        //tarija
        $query = "
                select 
                       case institucioneducativa_id when '80480300' then '1:Chuquisaca' when '80730794' then '2:La Paz' when '80980569' then '3:Cochabamba' when '81230297' then '4:Oruro' when '81480201' then '5:Potosi'
                        when '81730264' then '6:Tarija' when '81981501' then '7:Santa Cruz' when '82230130' then '8:Beni' when '82480050' then '9:Pando' end as depto, b.gestion_tipo_id,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 1
                                THEN 1
                                ELSE 0 
                             END       )
                        AS masculino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 2
                                THEN 1
                                ELSE 0 
                             END       )
                        AS femenino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null
                                THEN 1
                                ELSE 0 
                             END       )
                        AS total

                from 

                institucioneducativa_curso b inner join estudiante_inscripcion c on b.id=c.institucioneducativa_curso_id 
                inner join estudiante e on c.estudiante_id = e.id
                inner join estadomatricula_tipo d on c.estadomatricula_tipo_id=d.id
                inner join estadomatricula_tipo f on c.estadomatricula_inicio_tipo_id=f.id
                where institucioneducativa_id IN ('81730264')
                and b.nivel_tipo_id = '312'
                group by depto, b.gestion_tipo_id
                order by b.gestion_tipo_id
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filastj = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["gestion_tipo_id"] = $p["gestion_tipo_id"];
            $datos_filas["femenino"] = $p["femenino"];
            $datos_filas["masculino"] = $p["masculino"];
            $datos_filas["total"] = $p["total"];
            $filastj[] = $datos_filas;
        }
        
        //santa cruz
        $query = "
                select 
                       case institucioneducativa_id when '80480300' then '1:Chuquisaca' when '80730794' then '2:La Paz' when '80980569' then '3:Cochabamba' when '81230297' then '4:Oruro' when '81480201' then '5:Potosi'
                        when '81730264' then '6:Tarija' when '81981501' then '7:Santa Cruz' when '82230130' then '8:Beni' when '82480050' then '9:Pando' end as depto, b.gestion_tipo_id,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 1
                                THEN 1
                                ELSE 0 
                             END       )
                        AS masculino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 2
                                THEN 1
                                ELSE 0 
                             END       )
                        AS femenino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null
                                THEN 1
                                ELSE 0 
                             END       )
                        AS total

                from 

                institucioneducativa_curso b inner join estudiante_inscripcion c on b.id=c.institucioneducativa_curso_id 
                inner join estudiante e on c.estudiante_id = e.id
                inner join estadomatricula_tipo d on c.estadomatricula_tipo_id=d.id
                inner join estadomatricula_tipo f on c.estadomatricula_inicio_tipo_id=f.id
                where institucioneducativa_id IN ('81981501')
                and b.nivel_tipo_id = '312'
                group by depto, b.gestion_tipo_id
                order by b.gestion_tipo_id
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filassc = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["gestion_tipo_id"] = $p["gestion_tipo_id"];
            $datos_filas["femenino"] = $p["femenino"];
            $datos_filas["masculino"] = $p["masculino"];
            $datos_filas["total"] = $p["total"];
            $filassc[] = $datos_filas;
        }
        
        //beni
        $query = "
                select 
                       case institucioneducativa_id when '80480300' then '1:Chuquisaca' when '80730794' then '2:La Paz' when '80980569' then '3:Cochabamba' when '81230297' then '4:Oruro' when '81480201' then '5:Potosi'
                        when '81730264' then '6:Tarija' when '81981501' then '7:Santa Cruz' when '82230130' then '8:Beni' when '82480050' then '9:Pando' end as depto, b.gestion_tipo_id,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 1
                                THEN 1
                                ELSE 0 
                             END       )
                        AS masculino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 2
                                THEN 1
                                ELSE 0 
                             END       )
                        AS femenino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null
                                THEN 1
                                ELSE 0 
                             END       )
                        AS total

                from 

                institucioneducativa_curso b inner join estudiante_inscripcion c on b.id=c.institucioneducativa_curso_id 
                inner join estudiante e on c.estudiante_id = e.id
                inner join estadomatricula_tipo d on c.estadomatricula_tipo_id=d.id
                inner join estadomatricula_tipo f on c.estadomatricula_inicio_tipo_id=f.id
                where institucioneducativa_id IN ('82230130')
                and b.nivel_tipo_id = '312'
                group by depto, b.gestion_tipo_id
                order by b.gestion_tipo_id
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filasbn = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["gestion_tipo_id"] = $p["gestion_tipo_id"];
            $datos_filas["femenino"] = $p["femenino"];
            $datos_filas["masculino"] = $p["masculino"];
            $datos_filas["total"] = $p["total"];
            $filasbn[] = $datos_filas;
        }
        
        //pando
        $query = "
                select 
                       case institucioneducativa_id when '80480300' then '1:Chuquisaca' when '80730794' then '2:La Paz' when '80980569' then '3:Cochabamba' when '81230297' then '4:Oruro' when '81480201' then '5:Potosi'
                        when '81730264' then '6:Tarija' when '81981501' then '7:Santa Cruz' when '82230130' then '8:Beni' when '82480050' then '9:Pando' end as depto, b.gestion_tipo_id,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 1
                                THEN 1
                                ELSE 0 
                             END       )
                        AS masculino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null and e.genero_tipo_id = 2
                                THEN 1
                                ELSE 0 
                             END       )
                        AS femenino,
                        SUM(CASE WHEN b.nivel_tipo_id is not null
                                THEN 1
                                ELSE 0 
                             END       )
                        AS total

                from 

                institucioneducativa_curso b inner join estudiante_inscripcion c on b.id=c.institucioneducativa_curso_id 
                inner join estudiante e on c.estudiante_id = e.id
                inner join estadomatricula_tipo d on c.estadomatricula_tipo_id=d.id
                inner join estadomatricula_tipo f on c.estadomatricula_inicio_tipo_id=f.id
                where institucioneducativa_id IN ('82480050')
                and b.nivel_tipo_id = '312'
                group by depto, b.gestion_tipo_id
                order by b.gestion_tipo_id
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filaspn = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["gestion_tipo_id"] = $p["gestion_tipo_id"];
            $datos_filas["femenino"] = $p["femenino"];
            $datos_filas["masculino"] = $p["masculino"];
            $datos_filas["total"] = $p["total"];
            $filaspn[] = $datos_filas;
        }
        
//// Conocer cuanto falta
       // Cantidades totales
        $lp_ct=9470; // La Paz
        $co_ct=5598; // Cochabamba
        $ch_ct=6166; // Chuquisaca
        $sc_ct=7380; // Santa Cruz
        $or_ct=3445; // Oruro
        $pt_ct=4565; // Potosi 6121
        $be_ct=4268; //Beni
        $pa_ct=540;  // Pando
        $ta_ct=1950; //Tarija
        $to_ct=$lp_ct + $co_ct + $ch_ct + $sc_ct + $or_ct + $pt_ct + $be_ct + $pa_ct + $ta_ct;
       // Archivos Subidos
        $query = "
                select depto,count(depto) as cantidad from ( 
select persona.carnet, persona.nombre, persona.paterno, persona.materno,
                    institucioneducativa_curso.fecha_inicio, institucioneducativa_curso.fecha_fin,
                    institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id,
            

                    CASE
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80480300 THEN
                                    'CHUQUISACA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80730794 THEN
                                    'LA PAZ'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80980569 THEN
                                    'COCHABAMBA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81230297 THEN
                                    'ORURO'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81480201 THEN
                                    'POTOSI'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81730264 THEN
                                    'TARIJA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81981501 THEN
                                    'SANTA CRUZ'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 82230130 THEN
                                    'BENI'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 82480050 THEN
                                    'PANDO'                         
                              END AS depto,
                         institucioneducativa_curso.lugar

                from institucioneducativa_curso 
                inner join maestro_inscripcion 
                on institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion .id
                inner join persona 
                on maestro_inscripcion .persona_id = persona.id

            

                order by                 
                institucioneducativa_curso.fecha_inicio,
                institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id) as t1
GROUP BY depto
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filaspn = array();
        $datos_filas = array();
        $to=0;
        foreach ($po as $p) {
            if($p["depto"]=='LA PAZ') {$lp_cs=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='SANTA CRUZ') {$sc_cs=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='ORURO') {$or_cs=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='POTOSI') {$pt_cs=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='TARIJA') {$ta_cs=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='BENI') {$be_cs=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='COCHABAMBA') {$co_cs=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='CHUQUISACA') {$ch_cs=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='PANDO') {$pa_cs=$p["cantidad"]; $to=$to+$p["cantidad"];}
        }
        $to_cs=$to;
//Cantidad de subidos real
        $query = "
                select depto,count(depto) as  cantidad from ( 
select DISTINCT persona.carnet, persona.nombre, persona.paterno, persona.materno,
                    institucioneducativa_curso.fecha_inicio, institucioneducativa_curso.fecha_fin,
                    institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id,
            

                    CASE
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80480300 THEN
                                    'CHUQUISACA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80730794 THEN
                                    'LA PAZ'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80980569 THEN
                                    'COCHABAMBA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81230297 THEN
                                    'ORURO'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81480201 THEN
                                    'POTOSI'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81730264 THEN
                                    'TARIJA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81981501 THEN
                                    'SANTA CRUZ'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 82230130 THEN
                                    'BENI'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 82480050 THEN
                                    'PANDO'                         
                              END AS depto,
                         institucioneducativa_curso.lugar

                from institucioneducativa_curso 
                inner join maestro_inscripcion 
                on institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion .id
                inner join persona 
                on maestro_inscripcion .persona_id = persona.id

            

                order by                 
                institucioneducativa_curso.fecha_inicio,
                institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id) as t1
GROUP BY depto
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filaspn = array();
        $datos_filas = array();
        $to=0;
        foreach ($po as $p) {
            if($p["depto"]=='LA PAZ') {$lp_cv=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='SANTA CRUZ') {$sc_cv=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='ORURO') {$or_cv=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='POTOSI') {$pt_cv=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='TARIJA') {$ta_cv=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='BENI') {$be_cv=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='COCHABAMBA') {$co_cv=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='CHUQUISACA') {$ch_cv=$p["cantidad"]; $to=$to+$p["cantidad"];}
            if($p["depto"]=='PANDO') {$pa_cv=$p["cantidad"]; $to=$to+$p["cantidad"];}
        }
        $to_cv=$to;
        
//////////////// consolidado en una matriz
        $consolidado = array(
            array('dpto'=>'LA PAZ','ct'=> $lp_ct,'cs'=>$lp_cs,'cv'=>$lp_cv,'cf'=>($lp_ct-$lp_cv),'cd'=>($lp_cs-$lp_cv)),
            array('dpto'=>'ORURO', 'ct'=>$or_ct,'cs'=>$or_cs,'cv'=>$or_cv,'cf'=>($or_ct-$or_cv),'cd'=>($or_cs-$or_cv)),
            array('dpto'=>'COCHABAMBA', 'ct'=>$co_ct,'cs'=>$co_cs,'cv'=>$co_cv,'cf'=>($co_ct-$co_cv),'cd'=>($co_cs-$co_cv)),
            array('dpto'=>'SANTA CRUZ', 'ct'=>$sc_ct,'cs'=>$sc_cs,'cv'=>$sc_cv,'cf'=>($sc_ct-$sc_cv),'cd'=>($sc_cs-$sc_cv)),
            array('dpto'=>'TARIJA', 'ct'=>$ta_ct,'cs'=>$ta_cs,'cv'=>$ta_cv,'cf'=>($ta_ct-$ta_cv),'cd'=>($ta_cs-$ta_cv)),
            array('dpto'=>'POTOSI', 'ct'=>$pt_ct,'cs'=>$pt_cs,'cv'=>$pt_cv,'cf'=>($pt_ct-$pt_cv),'cd'=>($pt_cs-$pt_cv)),
            array('dpto'=>'BENI', 'ct'=>$be_ct,'cs'=>$be_cs,'cv'=>$be_cv,'cf'=>($be_ct-$be_cv),'cd'=>($be_cs-$be_cv)),
            array('dpto'=>'PANDO', 'ct'=>$pa_ct,'cs'=>$pa_cs,'cv'=>$pa_cv,'cf'=>($pa_ct-$pa_cv),'cd'=>($pa_cs-$pa_cv)),
            array('dpto'=>'CHUQUISACA', 'ct'=>$ch_ct,'cs'=>$ch_cs,'cv'=>$ch_cv,'cf'=>($ch_ct-$ch_cv),'cd'=>($ch_cs-$ch_cv)),
            array('dpto'=>'TOTAL', 'ct'=>$to_ct,'cs'=>$to_cs,'cv'=>$to_cv,'cf'=>($to_ct-$to_cv),'cd'=>($to_cs-$to_cv)),
        );
   
        return $this->render('SiePnpBundle:Estadisticas:nacionales.html.twig', array(
            'filas' => $filas, 
            'filasgestion' => $filasgestion, 
            'filaschu' => $filaschu,
            'filaslp' => $filaslp,
            'filasco' => $filasco,
            'filasor' => $filasor,
            'filaspt' => $filaspt,
            'filastj' => $filastj,
            'filassc' => $filassc,
            'filasbn' => $filasbn,
            'filaspn' => $filaspn,
            'consolidado'=>$consolidado
        ));
    }
    
    public function personasinsertAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('sie_pnp_persona');
            
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('persona');");
            $query->execute();

            $newpersona = new Persona();
            $newpersona->setActivo(1);
            $newpersona->setCarnet($form['carnetIdentidad']);
            $newpersona->setCelular($form['celular']);
            $newpersona->setComplemento('');
            $newpersona->setCorreo(mb_strtolower($form['correo'], "utf-8"));
            $newpersona->setDireccion(mb_strtoupper($form['direccion'], "utf-8"));
            $newpersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneById(0));
            $newpersona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
            $newpersona->setFoto('');
            $newpersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['genero']));
            $newpersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById(97));
            $newpersona->setLibretaMilitar('');
            $newpersona->setPaterno(mb_strtoupper($form['paterno'], "utf-8"));
            $newpersona->setMaterno(mb_strtoupper($form['materno'], "utf-8"));
            $newpersona->setNombre(mb_strtoupper($form['nombre'], "utf-8"));
            $newpersona->setPasaporte('');
            $newpersona->setEsvigente('t');
            $newpersona->setRda($form['rda']);
            $newpersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById(7));
            $newpersona->setSegipId(0);

            //$newpersona->setEsvigente('t');
            //$newpersona->setEsvigenteApoderado(0);
            
            $em->persist($newpersona);
            
            $em->flush();
            $personacarnetcontrol = new PersonaCarnetControl();
            $personacarnetcontrol->setPersona($newpersona);
            $personacarnetcontrol->setCarnet($form['carnetIdentidad']);
            $em->persist($personacarnetcontrol);
            $em->flush();
                        
            $em->getConnection()->commit();
            
            //$this->session->getFlashBag()->add('success', 'Proceso realizado exitosamente.');        
            
            return $this->redirect($this->generateUrl('sie_pnp_index'));
            } 
        catch (Exception $ex) {
            $em->getConnection()->rollback();

            $this->session->getFlashBag()->add('error', 'Proceso detenido. Se ha detectado inconsistencia de datos.'.$ex);
            return $this->redirect($this->generateUrl('sie_pnp_index'));
        }
    }
    
    public function vernotasAction($idinscripcion,$id_curso)
    {
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findOneByinstitucioneducativaCurso($id_curso);
                $plan=$result->getPlancurricularTipoId();
        if($plan==1)$order="ORDER BY asignatura_tipo.asignatura, nota_tipo.orden";
        if($plan==2)$order="order by  asignatura_tipo.asignatura_boletin";
        //LISTA DE NOTAS
         $query = "SELECT
                      estudiante_nota.id,
                      asignatura_tipo.asignatura,
                      nota_tipo.nota_tipo, 
                      estudiante_nota.nota_cuantitativa,
                      estudiante_inscripcion.estadomatricula_tipo_id,
                      estadomatricula_tipo.estadomatricula,
                      estudiante.nombre,
                      estudiante.paterno,
                      estudiante.materno,
                      estudiante.carnet_identidad,
                      estudiante.complemento,
                      estudiante_nota_cualitativa.nota_cualitativa
                    FROM 
                      public.estudiante
                    inner join estudiante_inscripcion on estudiante.id =estudiante_inscripcion.estudiante_id
                    inner join estudiante_asignatura on estudiante_asignatura.estudiante_inscripcion_id=estudiante_inscripcion.id
                    inner join institucioneducativa_curso_oferta on institucioneducativa_curso_oferta.id=estudiante_asignatura.institucioneducativa_curso_oferta_id
                    inner join asignatura_tipo on institucioneducativa_curso_oferta.asignatura_tipo_id = asignatura_tipo.id
                    inner join estudiante_nota on estudiante_nota.estudiante_asignatura_id = estudiante_asignatura.id
                    inner join nota_tipo on  estudiante_nota.nota_tipo_id = nota_tipo.id
                    inner join estadomatricula_tipo on estudiante_inscripcion.estadomatricula_tipo_id=estadomatricula_tipo.id
                    left join estudiante_nota_cualitativa on estudiante_nota_cualitativa.estudiante_inscripcion_id=estudiante_inscripcion.id 
                    WHERE
                      estudiante_inscripcion.id  = ".$idinscripcion."
                    $order";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        $cant_notas=0;
        foreach ($po as $p) {
            $cant_notas++;
            $datos_filas["asignatura"] = $p["asignatura"];
            $datos_filas["nota_tipo"] = $p["nota_tipo"];
            $datos_filas["nota_cuantitativa"] = $p["nota_cuantitativa"];
            if($p["nota_cualitativa"]=="")
                $datos_filas["nota_cualitativa"] = "";
            else
                $datos_filas["nota_cualitativa"] = $p["nota_cualitativa"];
            $filas[] = $datos_filas;
        } 

        if($plan==1)
            return $this->render('SiePnpBundle:Default:vernotas.html.twig', array('notas' => $filas,'cant_notas'=>$cant_notas));
        elseif($plan==2){
             $query = "SELECT ico.id from institucioneducativa_curso ic
            join institucioneducativa_curso_oferta ico on ico.insitucioneducativa_curso_id=ic.id
            where ic.id=$id_curso and ico.asignatura_tipo_id=2012
            ";
            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $po = $stmt->fetchAll();
            
            foreach ($po as $p) {
                $id_ico = $p["id"];
            }
            $modulo_emergente=$em->getRepository('SieAppWebBundle:AltModuloemergente')->findOneByInstitucioneducativaCursoOferta($id_ico);
            return $this->render('SiePnpBundle:Default:vernotas_p2.html.twig', array('notas' => $filas,'cant_notas'=>$cant_notas,'modulo_emergente'=>$modulo_emergente));
        }
    }
    
    public function cursolistarAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        //LISTA DE ESTUDIANTES
        $query = "SELECT 
                      estudiante.id as estudiante_id,
                      estudiante.codigo_rude, 
                      estudiante.carnet_identidad, 
                      estudiante.complemento,
                      estudiante.paterno, 
                      estudiante.materno, 
                      estudiante.nombre, 
                      estudiante.fecha_nacimiento, 
                      estudiante.genero_tipo_id,
                      genero_tipo.genero,
                      estudiante.observacionadicional,
                      estudiante_inscripcion.estadomatricula_tipo_id as matricula_estado_id,
                      estadomatricula_tipo.estadomatricula,
                      estudiante_inscripcion.id as inscripcion_id
                    FROM 
                      estudiante INNER JOIN estudiante_inscripcion ON estudiante.id = estudiante_inscripcion.estudiante_id
                      INNER JOIN genero_tipo ON genero_tipo.id = estudiante.genero_tipo_id
                      INNER JOIN estadomatricula_tipo ON estadomatricula_tipo.ID = estudiante_inscripcion.estadomatricula_inicio_tipo_id
                      INNER JOIN institucioneducativa_curso ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
                    WHERE
                      institucioneducativa_curso.id = ".$id;
        
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["codigo_rude"] = $p["codigo_rude"];
            $datos_filas["carnet_identidad"] = $p["carnet_identidad"];
            $datos_filas["complemento"] = $p["complemento"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["fecha_nacimiento"] = $p["fecha_nacimiento"];
            $datos_filas["genero"] = $p["genero"];
            $datos_filas["observacionadicional"] = $p["observacionadicional"];
            $datos_filas["estadomatricula"] = $p["estadomatricula"];
            $datos_filas["inscripcion_id"] = $p["inscripcion_id"];
            $filas[] = $datos_filas;
        }
        
        //DATOS GENERALES DEL CURSO TRIGO
        //DATOS GENERALES DEL CURSO
        $query = "SELECT
                    institucioneducativa_curso.id,
                    lt3.lugar as departamento,
                    lt2.lugar as provincia,
                    lt1.lugar as municipio,
                    icd.localidad,
                      institucioneducativa_curso.institucioneducativa_id, 
                      institucioneducativa_curso.gestion_tipo_id, 
                      institucioneducativa_curso.nivel_tipo_id, 
                      institucioneducativa_curso.ciclo_tipo_id, 
                      institucioneducativa_curso.grado_tipo_id,
                      institucioneducativa_curso.nro_materias,
                      institucioneducativa_curso.fecha_fin, 
                      institucioneducativa_curso.fecha_inicio,
                      institucioneducativa_curso.lugartipo_id as departamento_id,
                      institucioneducativa_curso.facilitador,
                      persona.nombre,
                      persona.paterno,
                      persona.materno,
                      persona.carnet,
                      persona.complemento,
                      icd.plancurricular_tipo_id,
                      ct.ciclo as nciclo,
                      gt.grado as ngrado
                    FROM 
                      institucioneducativa_curso 
                      LEFT JOIN estudiante_inscripcion ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
                      INNER JOIN lugar_tipo ON lugar_tipo.id = institucioneducativa_curso.lugartipo_id
                      INNER JOIN maestro_inscripcion ON institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion.id
                      INNER JOIN persona ON maestro_inscripcion.persona_id = persona.id
                      INNER JOIN ciclo_tipo ct ON institucioneducativa_curso.ciclo_tipo_id=ct.id
                      INNER JOIN grado_tipo gt ON institucioneducativa_curso.grado_tipo_id=gt.id
                      INNER JOIN institucioneducativa_curso_datos as icd ON icd.institucioneducativa_curso_id=institucioneducativa_curso.id
                      join lugar_tipo lt1 on lt1.id=icd.lugar_tipo_id_seccion
    join lugar_tipo lt2 on lt2.id=lt1.lugar_tipo_id
    join lugar_tipo lt3 on lt3.id=lt2.lugar_tipo_id
                    WHERE
                      institucioneducativa_curso.id = ".$id."

                   GROUP BY
                      institucioneducativa_curso.id,
                      institucioneducativa_curso.institucioneducativa_id, 
                      institucioneducativa_curso.gestion_tipo_id, 
                      institucioneducativa_curso.ciclo_tipo_id, 
                      institucioneducativa_curso.grado_tipo_id,
                      institucioneducativa_curso.nro_materias,
                      institucioneducativa_curso.fecha_fin, 
                      institucioneducativa_curso.fecha_inicio,
                      institucioneducativa_curso.lugartipo_id,
                      lugar_tipo.lugar,
                      institucioneducativa_curso.lugar,
                      estudiante_inscripcion.lugar, 
                      estudiante_inscripcion.lugarcurso,
                      institucioneducativa_curso.facilitador,
                      persona.nombre,
                      persona.paterno,
                      persona.materno,
                      persona.carnet,
                      persona.complemento,
                      icd.plancurricular_tipo_id,
                      ct.ciclo,
                      gt.grado,
                      lt3.lugar,lt2.lugar,lt1.lugar,icd.localidad";
        
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        
        $filasdos = array();
        $datos_filasdos = array();
        foreach ($po as $p) {
            $datos_filasdos["id"] = $p["id"];
            $datos_filasdos["institucioneducativa_id"] = $p["institucioneducativa_id"];
            $datos_filasdos["gestion_tipo_id"] = $p["gestion_tipo_id"];
            $datos_filasdos["ciclo_tipo_id"] = $p["ciclo_tipo_id"];
            $datos_filasdos["grado_tipo_id"] = $p["grado_tipo_id"];
            $datos_filasdos["nro_materias"] = $p["nro_materias"];
            $datos_filasdos["fecha_fin"] = $p["fecha_fin"];
            $datos_filasdos["fecha_inicio"] = $p["fecha_inicio"];
            $datos_filasdos["departamento"] = $p["departamento"];
            $datos_filasdos["provincia"] = $p["provincia"];
            $datos_filasdos["municipio"] = $p["municipio"];
            $datos_filasdos["localidad"] = $p["localidad"];
            $datos_filasdos["facilitador"] = $p["facilitador"];
            $datos_filasdos["nombre"] = $p["nombre"];
            $datos_filasdos["paterno"] = $p["paterno"];
            $datos_filasdos["materno"] = $p["materno"];
            $datos_filasdos["carnet"] = $p["carnet"];
            $datos_filasdos["complemento"] = $p["complemento"];
            $datos_filasdos["nciclo"] = $p["nciclo"];
            $datos_filasdos["ngrado"] = $p["ngrado"];
            $datos_filasdos["plan"] = $p["plancurricular_tipo_id"];
            if ($p["ngrado"]=="Primero")$datos_filasdos["ngrado"]=1;
            if ($p["ngrado"]=="Segundo")$datos_filasdos["ngrado"]=2;
            $filasdos[] = $datos_filasdos;
        }
        
        $this->session->getFlashBag()->add('success', 'Proceso realizado exitosamente.');
        return $this->render('SiePnpBundle:Default:cursolista.html.twig', array('estudiantes' => $filas, 'datosentity' => $filasdos));
    }
    
      public function cursoidAction() {
        $form = $this->createForm(new CursoType(), null, array('action' => $this->generateUrl('sie_pnp_resultado_curso_listado_id'), 'method' => 'POST',));        
        return $this->render('SiePnpBundle:Default:cursoid.html.twig', array(           
            'form'   => $form->createView(),
        ));
    }
    
    public function cursolistarbusquedaAction(Request $request)
    {
        
        $form = $request->get('pnp_curso_id_form');
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        //LISTA DE ESTUDIANTES
        //LISTA DE ESTUDIANTES
        $query = "SELECT 
                      estudiante.id as estudiante_id,
                      estudiante.codigo_rude, 
                      estudiante.carnet_identidad, 
                      estudiante.complemento,
                      estudiante.paterno, 
                      estudiante.materno, 
                      estudiante.nombre, 
                      estudiante.fecha_nacimiento, 
                      estudiante.genero_tipo_id,
                      genero_tipo.genero,
                      estudiante.observacionadicional,
                      estudiante_inscripcion.estadomatricula_tipo_id as matricula_estado_id,
                      estadomatricula_tipo.estadomatricula,
                      estudiante_inscripcion.id as inscripcion_id
                    FROM 
                      estudiante INNER JOIN estudiante_inscripcion ON estudiante.id = estudiante_inscripcion.estudiante_id
                      INNER JOIN genero_tipo ON genero_tipo.id = estudiante.genero_tipo_id
                      INNER JOIN estadomatricula_tipo ON estadomatricula_tipo.ID = estudiante_inscripcion.estadomatricula_tipo_id
                      INNER JOIN institucioneducativa_curso ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
                    WHERE
                      institucioneducativa_curso.id = ".$form['id'];
        
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["codigo_rude"] = $p["codigo_rude"];
            $datos_filas["carnet_identidad"] = $p["carnet_identidad"];
            $datos_filas["complemento"] = $p["complemento"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["fecha_nacimiento"] = $p["fecha_nacimiento"];
            $datos_filas["genero"] = $p["genero"];
            $datos_filas["observacionadicional"] = $p["observacionadicional"];
            $datos_filas["estadomatricula"] = $p["estadomatricula"];
            $datos_filas["inscripcion_id"] = $p["inscripcion_id"];
            $filas[] = $datos_filas;
        }
        
        //DATOS GENERALES DEL CURSO
        //DATOS GENERALES DEL CURSO
        $query = "SELECT
              institucioneducativa_curso.id, 
                      institucioneducativa_curso.institucioneducativa_id, 
                      institucioneducativa_curso.gestion_tipo_id, 
                      institucioneducativa_curso.nivel_tipo_id, 
                      institucioneducativa_curso.ciclo_tipo_id, 
                      institucioneducativa_curso.grado_tipo_id,
                      institucioneducativa_curso.nro_materias,
                      institucioneducativa_curso.fecha_fin, 
                      institucioneducativa_curso.fecha_inicio,
                      institucioneducativa_curso.lugartipo_id as departamento_id,
                      lugar_tipo.lugar as departamento,
                      institucioneducativa_curso.lugar as provincia,
                      estudiante_inscripcion.lugar as municipio, 
                      estudiante_inscripcion.lugarcurso as localidad,
                      institucioneducativa_curso.facilitador,
                      persona.nombre,
                      persona.paterno,
                      persona.materno,
                      persona.carnet,
                      persona.complemento
                    FROM 
                      institucioneducativa_curso INNER JOIN estudiante_inscripcion ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
                      INNER JOIN lugar_tipo ON lugar_tipo.id = institucioneducativa_curso.lugartipo_id
                      INNER JOIN maestro_inscripcion ON institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion.id
                      INNER JOIN persona ON maestro_inscripcion.persona_id = persona.id
                    WHERE
                      institucioneducativa_curso.id = ".$form['id']."

                   GROUP BY
                      institucioneducativa_curso.id,
                      institucioneducativa_curso.institucioneducativa_id, 
                      institucioneducativa_curso.gestion_tipo_id, 
                      institucioneducativa_curso.ciclo_tipo_id, 
                      institucioneducativa_curso.grado_tipo_id,
                      institucioneducativa_curso.nro_materias,
                      institucioneducativa_curso.fecha_fin, 
                      institucioneducativa_curso.fecha_inicio,
                      institucioneducativa_curso.lugartipo_id,
                      lugar_tipo.lugar,
                      institucioneducativa_curso.lugar,
                      estudiante_inscripcion.lugar, 
                      estudiante_inscripcion.lugarcurso,
                      institucioneducativa_curso.facilitador,
                      persona.nombre,
                      persona.paterno,
                      persona.materno,
                      persona.carnet,
                      persona.complemento";
        
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        
        $filasdos = array();
        $datos_filasdos = array();
        foreach ($po as $p) {
            $datos_filasdos["id"] = $p["id"];
            $datos_filasdos["institucioneducativa_id"] = $p["institucioneducativa_id"];
            $datos_filasdos["gestion_tipo_id"] = $p["gestion_tipo_id"];
            $datos_filasdos["ciclo_tipo_id"] = $p["ciclo_tipo_id"];
            $datos_filasdos["grado_tipo_id"] = $p["grado_tipo_id"];
            $datos_filasdos["nro_materias"] = $p["nro_materias"];
            $datos_filasdos["fecha_fin"] = $p["fecha_fin"];
            $datos_filasdos["fecha_inicio"] = $p["fecha_inicio"];
            $datos_filasdos["departamento"] = $p["departamento"];
            $datos_filasdos["provincia"] = $p["provincia"];
            $datos_filasdos["municipio"] = $p["municipio"];
            $datos_filasdos["localidad"] = $p["localidad"];
            $datos_filasdos["facilitador"] = $p["facilitador"];
            $datos_filasdos["nombre"] = $p["nombre"];
            $datos_filasdos["paterno"] = $p["paterno"];
            $datos_filasdos["materno"] = $p["materno"];
            $datos_filasdos["carnet"] = $p["carnet"];
            $datos_filasdos["complemento"] = $p["complemento"];
            $filasdos[] = $datos_filasdos;
        }
        return $this->render('SiePnpBundle:Default:cursolista.html.twig', array('estudiantes' => $filas, 'datosentity' => $filasdos));
    }
    
    public function xlsAction(Request $request)
    {        
        $form_x = $request->get('sie_pnp_xls_form');        
        $xls = $form_x['xls'];        
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante',30);");
        $query->execute();
        //$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('persona',30);");
        $query->execute();
        $em->getConnection()->beginTransaction();
        
        //BUSCAMOS E INSERTAMOS PERSONAS
        /*for ($i = 20; $i <= 30; $i++) {
                $celda = 'AG'.$i;
                print_r($this->StringBetween($xls, $celda, '$%$').' - ');
        }
    
        //print_r($this->StringBetween($xls, 'P21', '$%$'));
        die;
        */
        $idcursooferta = array();
    
        $facilitador  = $form_x['facilitador'];
        $facilitadorci = $form_x['facilitadorci'];        
        $gestion = $form_x['gestion'];
        $mes = $form_x['mes'];
        $dia = $form_x['dia'];
        $gestionfin = $form_x['gestionfin'];
        $mesfin = $form_x['mesfin'];
        $diafin = $form_x['diafin'];
        
        $departamento = $form_x['departamento'];
        //die($departamento);
        switch ($departamento) {
            case 2://CHUQUISACA
                $ie = '80480300';
                $rude = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('1');
                break;
            case 3://LA PAZ
                $ie = '80730794';
                $rude = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('2');
                break;
            case 4://COCHABAMBA
                $ie = '80980569';
                $rude = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('3');
                break;
            case 5://ORURO
                $ie = '81230297';
                $rude = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('4');
                break;
            case 6://POTOSI
                $ie = '81480201';
                $rude = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('5');
                break;
            case 7://TARIJA
                $ie = '81730264';
                $rude = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('6');
                break;
            case 8://SANTA CRUZ
                $ie = '81981501';
                $rude = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('7');
                break;
            case 9://BENI
                $ie = '82230130';
                $rude = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('8');
                break;
            case 10://PANDO
                $ie = '82480050';
                $rude = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('9');
                break;
        }
        $provincia = $form_x['provincia'];
        $municipio = $form_x['municipio'];
        $localidad = $form_x['localidad'];

        if ($form_x['typexls'] == 'B1_P1'){
            $rupid = '1';
            $materias = array('2000','2002','2005','2006','2007');
            $nroMaterias = '4';
            $ciclo = '1';//BLOQUE
            $grado = '1';//PARTE
            $colfn = 'AJ';//FECHA DE NACIMIENTO
            $colestado = 'AG';//ESTADO
            $iniciofil = '21';//INICIO DE FILAS DE PARTICIPANTES
        }
        else{
            if ($form_x['typexls'] == 'B1_P2'){
                $rupid = '2';
                $materias = array('2000','2001','2002','2003','2004','2005','2007');
                $nroMaterias = '6';
                $ciclo = '1';//BLOQUE
                $grado = '2';//PARTE
                $colfn = 'AT';//FECHA DE NACIMIENTO
                $colestado = 'AQ';//ESTADO
                $iniciofil = '21';//INICIO DE FILAS DE PARTICIPANTES
            }
            else{
                if ($form_x['typexls'] == 'B2_P1'){                    
                    $rupid = '3';
                    $materias = array('2000','2001','2002','2003','2004','2007');
                    $nroMaterias = '5';
                    $ciclo = '2';//BLOQUE
                    $grado = '1';//PARTE
                    $colfn = 'AO';//FECHA DE NACIMIENTO
                    $colestado = 'AL';//ESTADO
                    $iniciofil = '20';//INICIO DE FILAS DE PARTICIPANTES
                }
                else{
                    if ($form_x['typexls'] == 'B2_P2'){
                        $rupid = '4';
                        $materias = array('2000','2002','2006','2007');
                        $nroMaterias = '3';
                        $ciclo = '2';//BLOQUE
                        $grado = '2';//PARTE
                        $colfn = 'AE';//FECHA DE NACIMIENTO
                        $colestado = 'AB';//ESTADO
                        $iniciofil = '21';//INICIO DE FILAS DE PARTICIPANTES
                    }
                }
            }
        }
        
        try {
            $personacontrol = $em->getRepository('SieAppWebBundle:PersonaCarnetControl')->findOneByCarnet($facilitadorci); 
            
            if ($personacontrol){
                $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($personacontrol->getPersona()->getId());

                if ($persona){//MAESTRO INSCRIPCION
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('maestro_inscripcion');");
                    $query->execute();

                    $maestroinscripcion = new MaestroInscripcion();
                    $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->find(14));
                    $maestroinscripcion->setEspecialidadTipo($em->getRepository('SieAppWebBundle:EspecialidadMaestroTipo')->findOneById(0));
                    $maestroinscripcion->setEstadomaestro($em->getRepository('SieAppWebBundle:EstadomaestroTipo')->findOneById(1));
                    $maestroinscripcion->setEstudiaiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById('48'));
                    $maestroinscripcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById('0'));
                    $maestroinscripcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById('0'));
                    $maestroinscripcion->setFormaciondescripcion('');
                  //  $maestroinscripcion->setFormacion2Tipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById('0'));
                    //$maestroinscripcion->setFormaciondescripcion2('');
                    $maestroinscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion));
                    //$maestroinscripcion->setIdiomaMaterno(null);
                    $maestroinscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie));
                    //$maestroinscripcion->setInstitucioneducativaSucursalI('');
                    //$maestroinscripcion->setLeeescribebraile('');
                    //$maestroinscripcion->setNormalista((isset('');
                    $maestroinscripcion->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(5));
                    $maestroinscripcion->setPersona($em->getRepository('SieAppWebBundle:Persona')->findOneById($persona));
                   // $maestroinscripcion->setPersona($persona);
                    $maestroinscripcion->setRdaPlanillasId(0);
                    //$maestroinscripcion->setItem('');
                    $maestroinscripcion->setRef(0);

                    $em->persist($maestroinscripcion);
                    $em->flush();
                   // echo "holas1";die;    

                }
                else{
                    $em->getConnection()->rollback();
                    $this->session->getFlashBag()->add('error', 'Proceso detenido, no se encuentra el CI : '.$facilitadorci.' ingresado para el facilitador.');
                    return $this->render('SiePnpBundle:Default:result.html.twig');
                }
            }
            else{
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->add('error', 'No se encuentra el CI : '.$facilitadorci.' ingresado para el facilitador. Ingrese los siquientes datos y vuelva a subir el archivo Rup-4, no olvide que si no tiene RDA colocar 0');
                $form = $this->createForm(new PersonaType(array('ci' => $facilitadorci)), null, array('action' => $this->generateUrl('sie_pnp_persona_insert'), 'method' => 'POST',));        
                return $this->render('SiePnpBundle:Persona:new.html.twig', array(           
                    'form'   => $form->createView(),
                ));
            }
          
            //INSTITUCIONEDUCATIVA CURSO
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso');");
            $query->execute();
            $nuevo_curso = new InstitucioneducativaCurso();
            $nuevo_curso->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion));
            $nuevo_curso->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie));
            $nuevo_curso->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->findOneById(0));
            $nuevo_curso->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(5));//5 PERIODO TIPO MODULAR
            $nuevo_curso->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById(312));//312 NIVEL PRIMARIA PNP
            $nuevo_curso->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->findOneById($ciclo));
            $nuevo_curso->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->findOneById($grado));
            $nuevo_curso->setParaleloTipo   ($em->getRepository('SieAppWebBundle:ParaleloTipo')->findOneById('1'));//1 PARALELO A
            $nuevo_curso->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneById('0'));
            $nuevo_curso->setMultigrado(0);
            $nuevo_curso->setDesayunoEscolar(0);
            $nuevo_curso->setModalidadEnsenanza(0);
            $nuevo_curso->setIdiomaMasHabladoTipoId(48);//48 CASTELLANO
            $nuevo_curso->setIdiomaRegHabladoTipoId(0);
            $nuevo_curso->setIdiomaMenHabladoTipoId(0);
            $nuevo_curso->setPriLenEnsenanzaTipoId(48);
            $nuevo_curso->setSegLenEnsenanzaTipoId(0);
            $nuevo_curso->setTerLenEnsenanzaTipoId(1);
            $nuevo_curso->setFinDesEscolarTipoId(4);
            $nuevo_curso->setNroMaterias($nroMaterias);
            $nuevo_curso->setLugartipoId($departamento);
            $nuevo_curso->setLugar($provincia);
            $nuevo_curso->setFacilitador($facilitadorci);
            $nuevo_curso->setConsolidado(1);
            $nuevo_curso->setPeriodicidadTipoId(1111100);
            $nuevo_curso->setNotaPeriodoTipo($em->getRepository('SieAppWebBundle:NotaPeriodoTipo')->find(5));//5 NOTA TIPO MODULAR
            $nuevo_curso->setFechaInicio(\DateTime::createFromFormat('d/m/Y', $dia.'/'.$mes.'/'.$gestion));
            $nuevo_curso->setFechaFin(\DateTime::createFromFormat('d/m/Y', $diafin.'/'.$mesfin.'/'.$gestionfin));            
            $nuevo_curso->setMaestroInscripcionAsesor($maestroinscripcion);
            
            $em->persist($nuevo_curso);
            $em->flush(); 
            //CURSO OFERTA
            $cant_materias = count($materias);
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');");
            $query->execute();
            foreach ($materias as $mat){
                $newArea = new InstitucioneducativaCursoOferta();
                $newArea->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($mat));
                $newArea->setInsitucioneducativaCurso($nuevo_curso);                    
               // $newArea->setMaestroInscripcion($maestroinscripcion);                    
                $em->persist($newArea);
                $em->flush(); 
                array_push($idcursooferta, $newArea->getId());
            }

                                  
            //INFORMACION DE PARTICIPANTES-INSCRIPCION-NOTAS
            //INFORMACION DE PARTICIPANTES-INSCRIPCION-NOTAS
            //INFORMACION DE PARTICIPANTES-INSCRIPCION-NOTAS
            //INFORMACION DE PARTICIPANTES-INSCRIPCION-NOTAS
            for ($i = $iniciofil; $i <= 61; $i++) {
                $paterno = $this->StringBetween($xls, 'B'.$i, '$%$');
                $materno = $this->StringBetween($xls, 'C'.$i, '$%$');
                $nombre = $this->StringBetween($xls, 'D'.$i, '$%$');
                
                //CONTROL DE FILAS
                if (($nombre == '')&&($paterno == '')){
                   break;
                }
                
                //FECHA DE NACIMIENTO
                $fechanac = explode("/", $this->StringBetween($xls, $colfn.$i, '$%$'));
                $fechanacval = false;
                $edad = $this->StringBetween($xls, 'G'.$i, '$%$');
                
                if (sizeof($fechanac) == 3){
                    $mesnac = $fechanac[0];
                    $dianac = $fechanac[1];                    
                    $anionac = '19'.$fechanac[2];
                    //prueba
                    //echo $mesnac+" "+$dianac+" "+$anionac+" 1 ";
                    //die;

                    $fechanacval = true;
                    }
                else{
                    $dianac = '1';
                    $mesnac = '1';
                    $anionac = intval($gestion) - intval($edad);
                    $anionac = (string)$anionac;
                    //prueba
                    //echo $mesnac+" "+$dianac+" "+$anionac+" 2 ";
                    //die;
                    //die($anionac);
                }
                //print_r($this->StringBetween($xls, $colfn.$i, '$%$').' - '.$dianac.'/'.$mesnac.'/'.$anionac);
                //die;
                
                //GENERO
                if ($this->StringBetween($xls, 'H'.$i, '$%$') == 'F'){
                    $genero = '2';
                }else{
                    $genero = '1';
                }
                
                $estado = '62';
                //ESTADO$str = strtolower($str);
                if (strtolower($this->StringBetween($xls, $colestado.$i, '$%$') == 'd')){//DESINCORPORADO
                    $estado = '61';
                }
                if (strtolower($this->StringBetween($xls, $colestado.$i, '$%$') == 'c')){//EN CLASES
                    $estado = '62';
                }
                if (strtolower($this->StringBetween($xls, $colestado.$i, '$%$') == 'i')){//INCORPORADO
                    $estado = '63';
                }
                if (strtolower($this->StringBetween($xls, $colestado.$i, '$%$') == 'r')){//REINCORPORADO
                    $estado = '65';
                }
                //die($estado);
                
                //COLUMNA E CI PARA TODOS LOS RUPES
                $celda = 'E'.$i;
                $ci  = $this->StringBetween($xls, $celda, '$%$');                
                   
                if(preg_match("/[0-9][0-9][0-9][0-9]/",substr($ci, 0, 4))){
                      $num = 1;
                }else{
                      $num = 0;
                }
 
                //GENERA RUDE
                $seqrude = (string)$rude->getSeqrude()+1;
                $codrude = $ie.$gestion.str_pad($seqrude, 6, "0", STR_PAD_LEFT);
                                
                //INSERTAR ESTUDIANTE
                if ((strlen($ci) >= 4) && ($num == 1)){
                    $estudiante = $this->getDoctrine()->getRepository('SieAppWebBundle:Estudiante')->findOneByCarnetIdentidad($ci);
                    if (!$estudiante){
                        $persona = $this->getDoctrine()->getRepository('SieAppWebBundle:Persona')->findBy(array('carnet' => $ci));
                        $encontrado = false;
                        if (count($persona) > 1){
                            foreach ($persona as $personaval){
                                if( (substr($personaval->getPaterno(), 0, 2) === substr($paterno, 0, 2)) &&
                                    (substr($personaval->getMaterno(), 0, 2) === substr($materno, 0, 2)) &&
                                    (substr($personaval->getNombre(), 0, 2) === substr($nombre, 0, 2)) ){
                                    //$persona = $this->getDoctrine()->getRepository('SieAppWebBundle:Persona')->find($personaval->getId());
                                    $persona = $personaval;
                                    //print_r($personaval->getId().$persona->getCarnet());
                                    //die;
                                    $encontrado = true;
                                    break;
                                }
                            }
                        }
                        
                        //CONTROLAR DATOS DE LAS PERSONAS
                        if ($persona && $encontrado){//EXISTE PERSONA Y COPIA PERSONA A ESTUDIANTE
                            //print_r($persona->getCarnet());
                            //die('d');
                            
                            $estudiante = new Estudiante();
                            $estudiante->setCodigoRude($codrude);                
                            $estudiante->setCarnetIdentidad($persona->getCarnet());                
                            $estudiante->setPaterno($persona->getPaterno());
                            $estudiante->setMaterno($persona->getMaterno());
                            $estudiante->setNombre($persona->getNombre());
                            $estudiante->setGeneroTipo($this->getDoctrine()->getRepository('SieAppWebBundle:GeneroTipo')->find($genero));
                            $estudiante->setEstadoCivil($this->getDoctrine()->getRepository('SieAppWebBundle:EstadoCivilTipo')->find($persona->getEstadoCivilTipo()));
                            $estudiante->setLugarNacTipo($this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find('1'));                    
                            $estudiante->setOficialia('');
                            $estudiante->setLibro('');
                            $estudiante->setPartida('');
                            $estudiante->setFolio('');
                            //$estudiante->setSangreTipoId($this->getDoctrine()->getRepository('SieAppWebBundle:SangreTipo')->find($persona->getSangreTipo())->getId());
                            $estudiante->setIdiomaMaternoId('0');
                            $estudiante->setSegipId('0');
                            $estudiante->setComplemento('');
                            $estudiante->setBolean(false);
                            ////GESTION FECHAS                            
                            if ($persona->getFechaNacimiento()->format('Y')=='1900'){
                                $estudiante->setFechaNacimiento(\DateTime::createFromFormat('d/m/Y', $dianac.'/'.$mesnac.'/'.$anionac));
                                $fechabool = true;
                            }else{
                                $estudiante->setFechaNacimiento($persona->getFechaNacimiento());
                                $fechabool = false;
                            }                                               
                            $estudiante->setFechaModificacion(new \DateTime('now'));
                            $estudiante->setCorreo('');
                            $estudiante->setPaisTipo($this->getDoctrine()->getRepository('SieAppWebBundle:PaisTipo')->find('1'));
                            $estudiante->setLocalidadNac($this->getDoctrine()->getRepository('SieAppWebBundle:Persona')->find('1'));
                            //$estudiante->setFoto();
                            //$estudiante->setCelular('');
                            //$estudiante->setResolucionaprovatoria('');
                            //$estudiante->setCarnetCodepedis('');
                            $estudiante->setObservacionadicional($this->StringBetween($xls, 'I'.$i, '$%$').'|'.$this->StringBetween($xls, 'J'.$i, '$%$').'|'.$this->StringBetween($xls, 'K'.$i, '$%$'));                            
                            //$estudiante->setCarnetIbc('');
                            //$estudiante->setLibretaMilitar('');
                            $em->persist($estudiante);
                            $em->flush();

                            //ACTUALIZA FECHA NACIMIENTO - GENERO EN PERSONA
                            $persona->setGeneroTipo($this->getDoctrine()->getRepository('SieAppWebBundle:GeneroTipo')->find($genero));
                            if ($fechabool){
                                $persona->setFechaNacimiento(\DateTime::createFromFormat('d/m/Y', $dianac.'/'.$mesnac.'/'.$anionac));
                            }
                            $em->persist($persona);
                            $em->flush();
                            
                            //ACTUALIZA SECUANCIA DE RUDES
                            $rude->setSeqrude($seqrude);
                            $em->persist($rude);
                            $em->flush();
                        }//END IF EXISTE PERSONA
                    else{
                        //CREA NUEVO ESTUDIANTE CON CUALQUIER CI QUE TENGA EL DATO NUMERICO
                        //die($ci.' - '.$this->StringBetween($xls, 'D'.$i, '$%$'));
                        $estudiante = new Estudiante();
                        $estudiante->setCodigoRude($codrude);                
                        $estudiante->setCarnetIdentidad($ci);
                        $estudiante->setPaterno($paterno);
                        $estudiante->setMaterno($materno);
                        $estudiante->setNombre($nombre);
                        $estudiante->setGeneroTipo($this->getDoctrine()->getRepository('SieAppWebBundle:GeneroTipo')->find($genero));                        
                        $estudiante->setEstadoCivil($this->getDoctrine()->getRepository('SieAppWebBundle:EstadoCivilTipo')->find('0'));
                        $estudiante->setLugarNacTipo($this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find('1'));                    
                        $estudiante->setOficialia('');
                        $estudiante->setLibro('');
                        $estudiante->setPartida('');
                        $estudiante->setFolio('');
                        //$estudiante->setSangreTipoId($this->getDoctrine()->getRepository('SieAppWebBundle:SangreTipo')->find('7')->getId());
                        $estudiante->setIdiomaMaternoId('0');
                        $estudiante->setSegipId('0');
                        $estudiante->setComplemento('');
                        $estudiante->setBolean(false);
                        ////GESTION FECHAS
                        $estudiante->setFechaNacimiento(\DateTime::createFromFormat('d/m/Y', $dianac.'/'.$mesnac.'/'.$anionac));                        
                        $estudiante->setFechaModificacion(new \DateTime('now'));
                        $estudiante->setCorreo('');
                        $estudiante->setPaisTipo($this->getDoctrine()->getRepository('SieAppWebBundle:PaisTipo')->find('1'));
                        //$estudiante->setLocalidadNac($this->getDoctrine()->getRepository('SieAppWebBundle:Persona')->find($persona->getEstadoSangreTipo()));
                        //$estudiante->setFoto();
                        //$estudiante->setCelular('');
                        //$estudiante->setResolucionaprovatoria('');
                        //$estudiante->setCarnetCodepedis('');
                        $estudiante->setObservacionadicional($this->StringBetween($xls, 'I'.$i, '$%$').'|'.$this->StringBetween($xls, 'J'.$i, '$%$').'|'.$this->StringBetween($xls, 'K'.$i, '$%$'));                        
                        //$estudiante->setCarnetIbc('');
                        //$estudiante->setLibretaMilitar('');
                        $em->persist($estudiante);
                        
                        //ACTUALIZA SECUANCIA DE RUDES
                        $rude->setSeqrude($seqrude);
                        $em->persist($rude);
                        $em->flush();
                        
                        }//EN ELSE NO ENCUENTRA EL CI
                    }//END IF NO EXISTE ESTUDIANTE
                    else{// ACTUALIZA OBSERVACIONES ADICIONALES EN EL ESTUDIANTE YA EXISTENTE                        
                        $estudiante->setObservacionadicional($this->StringBetween($xls, 'I'.$i, '$%$').'|'.$this->StringBetween($xls, 'J'.$i, '$%$').'|'.$this->StringBetween($xls, 'K'.$i, '$%$'));                    
                        $em->persist($estudiante);
                        $em->flush();
                    }
                }//END IF CI VALIDO PARA LA BUSQUEDA
                else{ 
                    //CREA NUEVO ESTUDIANTE CON CI VACIO O NO VALIDO EJM: NO TIENE
                    //die($ci.' - '.$this->StringBetween($xls, 'D'.$i, '$%$'));
                    $estudiante = new Estudiante();
                    $estudiante->setCodigoRude($codrude);                
                    $estudiante->setCarnetIdentidad($ci);
                    $estudiante->setPaterno($paterno);
                    $estudiante->setMaterno($materno);
                    $estudiante->setNombre($nombre);
                    $estudiante->setGeneroTipo($this->getDoctrine()->getRepository('SieAppWebBundle:GeneroTipo')->find($genero)); 
                    $estudiante->setEstadoCivil($this->getDoctrine()->getRepository('SieAppWebBundle:EstadoCivilTipo')->find('0'));
                    $estudiante->setLugarNacTipo($this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find('1'));                    
                    $estudiante->setOficialia('');
                    $estudiante->setLibro('');
                    $estudiante->setPartida('');
                    $estudiante->setFolio('');
                    //$estudiante->setSangreTipoId($this->getDoctrine()->getRepository('SieAppWebBundle:SangreTipo')->find('7')->getId());
                    $estudiante->setIdiomaMaternoId('0');
                    $estudiante->setSegipId('0');
                    $estudiante->setComplemento('');
                    $estudiante->setBolean(false);
                    ////GESTION FECHAS
                    $estudiante->setFechaNacimiento(\DateTime::createFromFormat('d/m/Y', $dianac.'/'.$mesnac.'/'.$anionac));
                    $estudiante->setFechaModificacion(new \DateTime('now'));
                    $estudiante->setCorreo('');
                    $estudiante->setPaisTipo($this->getDoctrine()->getRepository('SieAppWebBundle:PaisTipo')->find('1'));
                    //$estudiante->setLocalidadNac($this->getDoctrine()->getRepository('SieAppWebBundle:Persona')->find($persona->getEstadoSangreTipo()));
                    //$estudiante->setFoto();
                    //$estudiante->setCelular('');
                    //$estudiante->setResolucionaprovatoria('');
                    //$estudiante->setCarnetCodepedis('');
                    $estudiante->setObservacionadicional($this->StringBetween($xls, 'I'.$i, '$%$').'|'.$this->StringBetween($xls, 'J'.$i, '$%$').'|'.$this->StringBetween($xls, 'K'.$i, '$%$'));                    
                    //$estudiante->setCarnetIbc('');
                    $estudiante->setLugarProvNacTipo($this->getDoctrine()->getRepository('SieAppWebBundle:LugarTipo')->find('1'));
                    //$estudiante->setLibretaMilitar('');
                    $em->persist($estudiante);
                    $em->flush();
                    
                    //ACTUALIZA SECUANCIA DE RUDES
                    $rude->setSeqrude($seqrude);
                    $em->persist($rude);
                    $em->flush();                    
                }//END ID INSERTAR ESTUDIANTE
                
                if($estudiante){
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion',30);");
                    $query->execute();
                    //INSERTA INSCRIPCIÓN ESTUDIANTE 
                    $inscripcion = new EstudianteInscripcion();
                    $inscripcion->setNumMatricula(0);
                    $inscripcion->setObservacionId(0);
                    $inscripcion->setObservacion(0);
                    //$inscripcion->setFechaInscripcion(\DateTime::createFromFormat('d/m/y', $fechainicio));
                    $inscripcion->setApreciacionFinal('');
                    $inscripcion->setOperativoId(1);
                    $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById($estado));
                    $inscripcion->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById('66'));
                    $inscripcion->setEstudiante($estudiante);
                    $inscripcion->setInstitucioneducativaCurso($nuevo_curso);
                    $inscripcion->setFechaRegistro(new \DateTime('now'));                        
                    $inscripcion->setLugar($municipio);
                    $inscripcion->setLugarcurso($localidad);
                    $inscripcion->setFacilitadorcurso($facilitador);
                    //$inscripcion->setFechaInicio(\DateTime::createFromFormat('d/m/y', $fechainicio));
                    //$inscripcion->setFechaFin(\DateTime::createFromFormat('d/m/y', $fechafin));
                    $em->persist($inscripcion);
                    $em->flush();

                    //INSERTA ESTUDIANTE ASIGNATURAS - NOTAS
                    $cant_cursooferta=count($idcursooferta);
                    foreach ($idcursooferta as $curoferid){
                        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura',$cant_cursooferta);");
                        $query->execute();
                        $curofe = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($curoferid);
                        $estAsignaturaNew = new EstudianteAsignatura();
                        $estAsignaturaNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                        //$estAsignaturaNew->setFerchaLastUpdate(new \DateTime('now'));
                        //$estAsignaturaNew->setVersion(0);
                        //$estAsignaturaNew->setRevisionId(0);
                        $estAsignaturaNew->setEstudianteInscripcion($inscripcion);
                        $estAsignaturaNew->setInstitucioneducativaCursoOferta($curofe);
                        //$estAsignaturaNew->setAsignaturaTipo($curofe->getAsignaturaTipo());
                        $em->persist($estAsignaturaNew);
                        $em->flush();
                        $cant_notas=$cant_cursooferta*5;
                        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota',$cant_notas);");
                        $query->execute();

                        //ESTUDIANTE NOTAS
                        //2000 MATEMATICAS PARA TODOS LOS RUPS L
                        if ($curofe->getAsignaturaTipo()->getId() == '2000'){
                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('12'));//6 TRABAJOS GRUPALES
                            if ($nota = $this->StringBetween($xls, 'L'.$i, '$%$')){
                                $notafinal = $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $notafinal = 0;
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'L'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);

                            $em->flush();                

                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('13'));//7 TRABAJOS INDIVIDUALES
                            if ($nota = $this->StringBetween($xls, 'M'.$i, '$%$')){
                                $notafinal = $notafinal + $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'M'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            
                            $em->flush();

                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('14'));//8 PRUEBA FINAL
                            if ($nota = $this->StringBetween($xls, 'N'.$i, '$%$')){
                                $notafinal = $notafinal + $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'N'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();

                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('15'));//9 ASISTENCIA
                            if ($nota = $this->StringBetween($xls, 'O'.$i, '$%$')){
                                $notafinal = $notafinal + $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'O'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();
                            
                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('16'));//16 NOTA FINAL MATERIA
                            $registro_nota->setNotaCuantitativa($notafinal);
                            $promedio_final = $notafinal;
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'O'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();
                        }
                        //2001 CASTELLANO 2006 LENGUA CASTELLANO PARA TODOS LOS RUPS Q
                        if (($curofe->getAsignaturaTipo()->getId() == '2001') || ($curofe->getAsignaturaTipo()->getId() == '2006')){
                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('12'));//6 TRABAJOS GRUPALES
                            if ($nota = $this->StringBetween($xls, 'Q'.$i, '$%$')){
                                $notafinal = $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $notafinal = 0;
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'Q'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();                

                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('13'));//7 TRABAJOS INDIVIDUALES
                            if ($nota = $this->StringBetween($xls, 'R'.$i, '$%$')){
                             $notafinal = $notafinal + $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'R'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();

                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('14'));//8 PRUEBA FINAL
                            if ($nota = $this->StringBetween($xls, 'S'.$i, '$%$')){
                                $notafinal = $notafinal + $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'S'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();

                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('15'));//9 ASISTENCIA
                            if ($nota = $this->StringBetween($xls, 'T'.$i, '$%$')){
                                $notafinal = $notafinal + $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'T'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();
                            
                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('16'));//16 NOTA FINAL MATERIA
                            $registro_nota->setNotaCuantitativa($notafinal);
                            $promedio_final = $promedio_final + $notafinal;
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'O'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();
                        }
                        //2002 LENGUA ORIGINARIA PARA TODOS LOS RUPS V
                        if ($curofe->getAsignaturaTipo()->getId() == '2002'){
                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('12'));//6 TRABAJOS GRUPALES
                            if ($nota = $this->StringBetween($xls, 'V'.$i, '$%$')){
                                $notafinal = $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $notafinal = 0;
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'V'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();                

                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('13'));//7 TRABAJOS INDIVIDUALES
                            if ($nota = $this->StringBetween($xls, 'W'.$i, '$%$')){
                                $notafinal = $notafinal + $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'W'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();

                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('14'));//8 PRUEBA FINAL
                            if ($nota = $this->StringBetween($xls, 'X'.$i, '$%$')){
                                $notafinal = $notafinal + $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'X'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();

                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('15'));//9 ASISTENCIA
                            if ($nota = $this->StringBetween($xls, 'Y'.$i, '$%$')){
                                $notafinal = $notafinal + $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'Y'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();
                            
                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('16'));//16 NOTA FINAL MATERIA
                            $registro_nota->setNotaCuantitativa($notafinal);
                            $promedio_final = $promedio_final + $notafinal;
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'O'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();
                        }
                        //2003 GEOGRAFIA PARA LOS RUPS 2 Y 3 AA
                        if ($curofe->getAsignaturaTipo()->getId() == '2003'){
                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('12'));//6 TRABAJOS GRUPALES
                            if ($nota = $this->StringBetween($xls, 'AA'.$i, '$%$')){
                                $notafinal = $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $notafinal = 0;
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AA'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();                

                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('13'));//7 TRABAJOS INDIVIDUALES
                            if ($nota = $this->StringBetween($xls, 'AB'.$i, '$%$')){
                                $notafinal = $notafinal + $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AB'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();

                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('14'));//8 PRUEBA FINAL
                            if ($nota = $this->StringBetween($xls, 'AC'.$i, '$%$')){
                                $notafinal = $notafinal + $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AC'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();

                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('15'));//9 ASISTENCIA
                            if ($nota = $this->StringBetween($xls, 'AD'.$i, '$%$')){
                                $notafinal = $notafinal + $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AD'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();
                            
                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('16'));//16 NOTA FINAL MATERIA
                            $registro_nota->setNotaCuantitativa($notafinal);
                            $promedio_final = $promedio_final + $notafinal;
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'O'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();
                        }
                        //2004 HISTORIA PARA LOS RUPS 2 Y 3 AF
                        if ($curofe->getAsignaturaTipo()->getId() == '2004'){
                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('12'));//6 TRABAJOS GRUPALES
                            if ($nota = $this->StringBetween($xls, 'AF'.$i, '$%$')){
                                $notafinal = $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $notafinal = 0;
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AF'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();                

                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('13'));//7 TRABAJOS INDIVIDUALES
                            if ($nota = $this->StringBetween($xls, 'AG'.$i, '$%$')){
                                $notafinal = $notafinal + $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AG'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();

                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('14'));//8 PRUEBA FINAL
                            if ($nota = $this->StringBetween($xls, 'AH'.$i, '$%$')){
                                $notafinal = $notafinal + $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AH'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();

                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('15'));//9 ASISTENCIA
                            if ($nota = $this->StringBetween($xls, 'AI'.$i, '$%$')){
                                $notafinal = $notafinal + $nota;
                                $registro_nota->setNotaCuantitativa(round($nota));
                            }else{
                                $registro_nota->setNotaCuantitativa('0');
                            }
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AI'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();
                            
                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('16'));//16 NOTA FINAL MATERIA
                            $registro_nota->setNotaCuantitativa($notafinal);
                            $promedio_final = $promedio_final + $notafinal;
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'O'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);
                            $em->flush();
                        }
                        //2005 CIENCIAS NATURALES CELDAS SEGUN RUP SOLO 1 Y 2
                        if ($curofe->getAsignaturaTipo()->getId() == '2005'){
                            if ($rupid == '1'){
                                $registro_nota = new EstudianteNota();
                                $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                                $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                                $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('12'));//6 TRABAJOS GRUPALES
                                if ($nota = $this->StringBetween($xls, 'AA'.$i, '$%$')){
                                    $notafinal = $nota;
                                    $registro_nota->setNotaCuantitativa(round($nota));
                                }else{
                                    $notafinal = 0;
                                    $registro_nota->setNotaCuantitativa('0');
                                }
                                //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AA'.$i, '$%$'));
                                $registro_nota->setUsuarioId($this->session->get('userId'));
                                $em->persist($registro_nota);
                                
                                $em->flush();                

                                $registro_nota = new EstudianteNota();
                                $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                                $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                                $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('13'));//7 TRABAJOS INDIVIDUALES
                                if ($nota = $this->StringBetween($xls, 'AB'.$i, '$%$')){
                                    $notafinal = $notafinal + $nota;
                                    $registro_nota->setNotaCuantitativa(round($nota));
                                }else{
                                    $registro_nota->setNotaCuantitativa('0');
                                }
                                //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AB'.$i, '$%$'));
                                $registro_nota->setUsuarioId($this->session->get('userId'));
                                $em->persist($registro_nota);
                                $em->flush();

                                $registro_nota = new EstudianteNota();
                                $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                                $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                                $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('14'));//8 PRUEBA FINAL
                                if ($nota = $this->StringBetween($xls, 'AC'.$i, '$%$')){
                                    $notafinal = $notafinal + $nota;
                                    $registro_nota->setNotaCuantitativa(round($nota));
                                }else{
                                    $registro_nota->setNotaCuantitativa('0');
                                }
                                //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AC'.$i, '$%$'));
                                $registro_nota->setUsuarioId($this->session->get('userId'));
                                $em->persist($registro_nota);
                                $em->flush();

                                $registro_nota = new EstudianteNota();
                                $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                                $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                                $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('15'));//9 ASISTENCIA
                                if ($nota = $this->StringBetween($xls, 'AD'.$i, '$%$')){
                                    $notafinal = $notafinal + $nota;
                                    $registro_nota->setNotaCuantitativa(round($nota));
                                }else{
                                    $registro_nota->setNotaCuantitativa('0');
                                }
                                //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AD'.$i, '$%$'));
                                $registro_nota->setUsuarioId($this->session->get('userId'));
                                $em->persist($registro_nota);
                                $em->flush();
                                
                                $registro_nota = new EstudianteNota();
                                $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                                $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                                $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('16'));//16 NOTA FINAL MATERIA
                                $registro_nota->setNotaCuantitativa($notafinal);
                                $promedio_final = $promedio_final + $notafinal;
                                //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'O'.$i, '$%$'));
                                $registro_nota->setUsuarioId($this->session->get('userId'));
                                $em->persist($registro_nota);
                                $em->flush();
                            }
                            if ($rupid == '2'){
                                $registro_nota = new EstudianteNota();
                                $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                                $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                                $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('12'));//6 TRABAJOS GRUPALES
                                if ($nota = $this->StringBetween($xls, 'AK'.$i, '$%$')){
                                    $notafinal = $nota;
                                    $registro_nota->setNotaCuantitativa(round($nota));
                                }else{
                                    $notafinal = 0;
                                    $registro_nota->setNotaCuantitativa('0');
                                }
                                //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AK'.$i, '$%$'));
                                $registro_nota->setUsuarioId($this->session->get('userId'));
                                $em->persist($registro_nota);
                                $em->flush();                

                                $registro_nota = new EstudianteNota();
                                $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                                $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                                $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('13'));//7 TRABAJOS INDIVIDUALES
                                if ($nota = $this->StringBetween($xls, 'AL'.$i, '$%$')){
                                    $notafinal = $notafinal + $nota;
                                    $registro_nota->setNotaCuantitativa(round($nota));
                                }else{
                                    $registro_nota->setNotaCuantitativa('0');
                                }
                                //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AL'.$i, '$%$'));
                                $registro_nota->setUsuarioId($this->session->get('userId'));
                                $em->persist($registro_nota);
                                $em->flush();

                                $registro_nota = new EstudianteNota();
                                $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                                $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                                $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('14'));//8 PRUEBA FINAL
                                if ($nota = $this->StringBetween($xls, 'AM'.$i, '$%$')){
                                    $notafinal = $notafinal + $nota;
                                    $registro_nota->setNotaCuantitativa(round($nota));
                                }else{
                                    $registro_nota->setNotaCuantitativa('0');
                                }
                                //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AM'.$i, '$%$'));
                                $registro_nota->setUsuarioId($this->session->get('userId'));
                                $em->persist($registro_nota);
                                $em->flush();

                                $registro_nota = new EstudianteNota();
                                $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                                $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                                $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('15'));//9 ASISTENCIA
                                if ($nota = $this->StringBetween($xls, 'AN'.$i, '$%$')){
                                    $notafinal = $notafinal + $nota;
                                    $registro_nota->setNotaCuantitativa(round($nota));
                                }else{
                                    $registro_nota->setNotaCuantitativa('0');
                                }
                                //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AN'.$i, '$%$'));
                                $registro_nota->setUsuarioId($this->session->get('userId'));
                                $em->persist($registro_nota);
                                $em->flush();
                                
                                $registro_nota = new EstudianteNota();
                                $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                                $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                                $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('16'));//16 NOTA FINAL MATERIA
                                $registro_nota->setNotaCuantitativa($notafinal);
                                $promedio_final = $promedio_final + $notafinal;
                                //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'O'.$i, '$%$'));
                                $registro_nota->setUsuarioId($this->session->get('userId'));
                                $em->persist($registro_nota);
                                $em->flush();
                            }
                        }//ULTIMA MATERIA
                        
                        //2007 PROMEDIO FINAL CELDAS SEGUN RUP
                        if ($curofe->getAsignaturaTipo()->getId() == '2007'){
                            if ($rupid == '1'){
                                $promedio_final = $promedio_final/4;
                            }
                            if ($rupid == '2'){
                                $promedio_final = $promedio_final/6;
                            }
                            if ($rupid == '3'){
                                $promedio_final = $promedio_final/5;
                            }
                            if ($rupid == '4'){
                                $promedio_final = $promedio_final/3;
                            }
                            $registro_nota = new EstudianteNota();
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                            
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find('17'));// PROMEDIO FINAL
                            
                            $registro_nota->setNotaCuantitativa(round($promedio_final));
                            $ver=round($promedio_final);             
                            //$registro_nota->setNotaCuantitativa($this->StringBetween($xls, 'AA'.$i, '$%$'));
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $em->persist($registro_nota);

                            $em->flush();

                        }//PROMEDIO FINAL

                    }//END FOR CURSO OFERTA                    
                }//IF EXISTE ESTUDIANTE    
            }//END FOR 30 A 40 FILAS//316087
            $em->getConnection()->commit();  

            //$em->getConnection()->rollback();
            //$this->session->getFlashBag()->add('success', 'Proceso realizado exitosamente. Id Curso :'.$nuevo_curso->getId());
            return $this->redirect($this->generateUrl('sie_pnp_curso_listado', array('id' => $nuevo_curso->getId())));
            //return $this->render('SiePnpBundle:Default:result.html.twig');
        }
        catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->session->getFlashBag()->add('error', 'Proceso detenido, se ha detectado inconsistencia de datos.');
            return $this->render('SiePnpBundle:Default:result.html.twig');
            }
    }
    
    public function totalesgestiondeptoAction()
    {
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        //LISTA DE TOTALES POR GESTION DEPTO PARTE Y BLOQUE
        $query = "
                SELECT                  
                  CASE
                    WHEN institucioneducativa_curso.institucioneducativa_id = 80480300 THEN
                        'CHUQUISACA'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 80730794 THEN
                        'LA PAZ'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 80980569 THEN
                        'COCHABAMBA'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 81230297 THEN
                        'ORURO'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 81480201 THEN
                        'POTOSI'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 81730264 THEN
                        'TARIJA'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 81981501 THEN
                        'SANTA CRUZ'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 82230130 THEN
                        'BENI'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 82480050 THEN
                        'PANDO'                         
                  END AS depto,
          institucioneducativa_curso.gestion_tipo_id as gestion,
                  count(CASE 
                    WHEN (institucioneducativa_curso.ciclo_tipo_id = 1) and (institucioneducativa_curso.grado_tipo_id = 1) THEN
            1                   
                  END) AS parte1bloque1,

          count(CASE 
                    WHEN (institucioneducativa_curso.ciclo_tipo_id = 1) and (institucioneducativa_curso.grado_tipo_id = 2) THEN
            1                   
                  END) AS parte1bloque2,

                  count(CASE 
                    WHEN (institucioneducativa_curso.ciclo_tipo_id = 2) and (institucioneducativa_curso.grado_tipo_id = 1) THEN
            1                   
                  END) AS parte2bloque1,

                  count(CASE 
                    WHEN (institucioneducativa_curso.ciclo_tipo_id = 2) and (institucioneducativa_curso.grado_tipo_id = 2) THEN
            1                   
                  END) AS parte2bloque2 
                  
                FROM 
                  public.institucioneducativa_curso
                WHERE
                  institucioneducativa_curso.institucioneducativa_id in ('80480300','80730794','80980569','81230297','81480201','81730264','81981501','82230130','82480050')
                  and institucioneducativa_curso.nivel_tipo_id = 312
                GROUP BY
                  institucioneducativa_curso.gestion_tipo_id,
                  institucioneducativa_curso.institucioneducativa_id
                ORDER BY 
                  institucioneducativa_curso.institucioneducativa_id,
                  institucioneducativa_curso.gestion_tipo_id
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["gestion"] = $p["gestion"];
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["parte1bloque1"] = $p["parte1bloque1"];
            $datos_filas["parte1bloque2"] = $p["parte1bloque2"];
            $datos_filas["parte2bloque1"] = $p["parte2bloque1"];
            $datos_filas["parte2bloque2"] = $p["parte2bloque2"];
            $filas[] = $datos_filas;
        }        
        return $this->render('SiePnpBundle:Default:totales.html.twig', array('totales' => $filas));
        
    }
    
    public function facilitadorbusquedacarnetAction() {
        $form = $this->createForm(new FacilitadorType(), null, array('action' => $this->generateUrl('sie_pnp_resultado_facilitador_carnet'), 'method' => 'POST',));        
        return $this->render('SiePnpBundle:Default:facilitadorcarnet.html.twig', array(           
            'form'   => $form->createView(),
        ));
    }
    
    public function cursosporfacilitadorAction($id,Request $request)
    {
        $form = $this->createForm(new FacilitadorType());
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $data = $form->getData();
            $ci=$data['ci'];
        }
        else{
            $ci=$id;
        }
      
        {

        
            $em = $this->getDoctrine()->getManager();
            $db = $em->getConnection();
            //LISTA DE TOTALES POR GESTION DEPTO PARTE Y BLOQUE
            $query = "
                    select persona.carnet,persona.complemento, persona.nombre, persona.paterno, persona.materno,ct.ciclo as nciclo,gt.grado as ngrado,
                        institucioneducativa_curso.fecha_inicio, institucioneducativa_curso.fecha_fin,
                        institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id,
                        institucioneducativa_curso.id,icd.plancurricular_tipo_id,

                        CASE
                                    WHEN institucioneducativa_curso.institucioneducativa_id = 80480300 THEN
                                        'CHUQUISACA'
                                    WHEN institucioneducativa_curso.institucioneducativa_id = 80730794 THEN
                                        'LA PAZ'
                                    WHEN institucioneducativa_curso.institucioneducativa_id = 80980569 THEN
                                        'COCHABAMBA'
                                    WHEN institucioneducativa_curso.institucioneducativa_id = 81230297 THEN
                                        'ORURO'
                                    WHEN institucioneducativa_curso.institucioneducativa_id = 81480201 THEN
                                        'POTOSI'
                                    WHEN institucioneducativa_curso.institucioneducativa_id = 81730264 THEN
                                        'TARIJA'
                                    WHEN institucioneducativa_curso.institucioneducativa_id = 81981501 THEN
                                        'SANTA CRUZ'
                                    WHEN institucioneducativa_curso.institucioneducativa_id = 82230130 THEN
                                        'BENI'
                                    WHEN institucioneducativa_curso.institucioneducativa_id = 82480050 THEN
                                        'PANDO'                         
                                  END AS depto,
                             institucioneducativa_curso.lugar

                    from institucioneducativa_curso 
                    inner join maestro_inscripcion 
                    on institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion .id
                    inner join persona 
                    on maestro_inscripcion .persona_id = persona.id
                    INNER JOIN ciclo_tipo ct ON institucioneducativa_curso.ciclo_tipo_id=ct.id
                    INNER JOIN grado_tipo gt ON institucioneducativa_curso.grado_tipo_id=gt.id
                    INNER JOIN institucioneducativa_curso_datos as icd ON icd.institucioneducativa_curso_id=institucioneducativa_curso.id

                    where 
                    persona.carnet = '".$ci."'

                    order by                 
                    institucioneducativa_curso.fecha_inicio,
                    institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id
                    ";
            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $po = $stmt->fetchAll();
            $filas = array();
            $datos_filas = array();
            foreach ($po as $p) {
                $datos_filas["carnet"] = $p["carnet"];
                $datos_filas["complemento"] = $p["complemento"];
                $datos_filas["nombre"] = $p["nombre"];
                $datos_filas["paterno"] = $p["paterno"];
                $datos_filas["materno"] = $p["materno"];
                $datos_filas["fecha_inicio"] = $p["fecha_inicio"];
                $datos_filas["fecha_fin"] = $p["fecha_fin"];
                $datos_filas["ciclo_tipo_id"] = $p["ciclo_tipo_id"];
                $datos_filas["grado_tipo_id"] = $p["grado_tipo_id"];
                $datos_filas["id"] = $p["id"];
                $datos_filas["depto"] = $p["depto"];
                $datos_filas["nciclo"] = $p["nciclo"];
                $datos_filas["ngrado"] = $p["ngrado"];
                $datos_filas["plan"] = $p["plancurricular_tipo_id"];
                if ($p["ngrado"]=="Primero")$datos_filas["ngrado"]=1;
                if ($p["ngrado"]=="Segundo")$datos_filas["ngrado"]=2;
                $datos_filas["lugar"] = $p["lugar"];
                $filas[] = $datos_filas;
            }        
            return $this->render('SiePnpBundle:Default:cursosporfacilitador.html.twig', array('totales' => $filas));
        }    
    }
     
    public function listararchivosAction($id, Request $request)
    {
        $form = $this->createForm(new FacilitadorType());
        
        $data = $form->getData();
        switch ($id) {
            case 1:$id=80480300;break;
            case 2:$id=80730794;break;
            case 3:$id=80980569;break;
            case 4:$id=81230297;break;
            case 5:$id=81480201;break;
            case 6:$id=81730264;break;
            case 7:$id=81981501;break;
            case 8:$id=82230130;break;
            case 9:$id=82480050;break;
            default:
                $id=0;
                break;
        }

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

               
        //LISTA DE TOTALES POR GESTION DEPTO PARTE Y BLOQUE
        $query = "
                select persona.carnet,persona.complemento, persona.nombre, persona.paterno, persona.materno,
                    institucioneducativa_curso.fecha_inicio, institucioneducativa_curso.fecha_fin,
                    institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id,
                    institucioneducativa_curso.id,

                    CASE
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80480300 THEN
                                    'CHUQUISACA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80730794 THEN
                                    'LA PAZ'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80980569 THEN
                                    'COCHABAMBA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81230297 THEN
                                    'ORURO'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81480201 THEN
                                    'POTOSI'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81730264 THEN
                                    'TARIJA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81981501 THEN
                                    'SANTA CRUZ'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 82230130 THEN
                                    'BENI'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 82480050 THEN
                                    'PANDO'                         
                              END AS depto,
                         institucioneducativa_curso.lugar,icd.plancurricular_tipo_id,ct.ciclo as nciclo,gt.grado as ngrado

                from institucioneducativa_curso 
                inner join maestro_inscripcion 
                on institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion .id
                inner join persona 
                on maestro_inscripcion .persona_id = persona.id
                inner join institucioneducativa_curso_datos icd 
                on icd.institucioneducativa_curso_id=institucioneducativa_curso.id
                INNER JOIN ciclo_tipo ct ON institucioneducativa_curso.ciclo_tipo_id=ct.id
                INNER JOIN grado_tipo gt ON institucioneducativa_curso.grado_tipo_id=gt.id

                where 
                institucioneducativa_curso.institucioneducativa_id = $id

                order by                 
                institucioneducativa_curso.fecha_inicio,
                institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["carnet"] = $p["carnet"];
            $datos_filas["complemento"] = $p["complemento"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["fecha_inicio"] = $p["fecha_inicio"];
            $datos_filas["fecha_fin"] = $p["fecha_fin"];
            $datos_filas["ciclo_tipo_id"] = $p["ciclo_tipo_id"];
            $datos_filas["grado_tipo_id"] = $p["grado_tipo_id"];
            $datos_filas["id"] = $p["id"];
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["lugar"] = $p["lugar"];
            $datos_filas["nciclo"] = $p["nciclo"];
            $datos_filas["ngrado"] = $p["ngrado"];
            $datos_filas["plan"] = $p["plancurricular_tipo_id"];
            if ($p["ngrado"]=="Primero")$datos_filas["ngrado"]=1;
            if ($p["ngrado"]=="Segundo")$datos_filas["ngrado"]=2;
            $filas[] = $datos_filas;
        }        
        return $this->render('SiePnpBundle:Default:listararchivos.html.twig', array('totales' => $filas,'idd'=>$id));
    }
    
    public function imprimirconsolidadoAction($id,$esactivo, Request $request)
    {
        $porciones = explode("|", $id);
        $id=$porciones[0];
        $b=$porciones[1];
        $p=$porciones[2];
        $arch = 'PNP_CONSOLIDADO_' . $id . '_' . date('Ymd') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        //plan 1
        if($b==1 and $p==1)
            if($esactivo==0)//PDF SIN VALOR LEGAL
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_lst_NoLegalEstudiantesBoletinCentralizador_1_1_v1.rptdesign&__format=pdf&&curso_id=' . $id . '&&__format=pdf&'));
            else //PDF CON VALOR LEGAL
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_lst_EstudiantesBoletinCentralizador_1_1_v1.rptdesign&__format=pdf&&curso_id=' . $id . '&&__fimprimirconsolidadoormat=pdf&'));
        elseif($b==1 and $p==2)
            if($esactivo==0)//PDF SIN VALOR LEGAL
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_lst_NoLegalEstudiantesBoletinCentralizador_1_2_v1.rptdesign&__format=pdf&&curso_id=' . $id . '&&__format=pdf&'));
            else //PDF CON VALOR LEGAL
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_lst_EstudiantesBoletinCentralizador_1_2_v1.rptdesign&__format=pdf&&curso_id=' . $id . '&&__format=pdf&'));
        elseif($b==2 and $p==1)
            if($esactivo==0)//PDF SIN VALOR LEGAL
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_lst_NoLegalEstudiantesBoletinCentralizador_2_1_v1.rptdesign&__format=pdf&&curso_id=' . $id . '&&__format=pdf&'));
            else //PDF CON VALOR LEGAL
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_lst_EstudiantesBoletinCentralizador_2_1_v1.rptdesign&__format=pdf&&curso_id=' . $id . '&&__format=pdf&'));
        elseif($b==2 and $p==2)
            if($esactivo==0)//PDF SIN VALOR LEGAL
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_lst_NoLegalEstudiantesBoletinCentralizador_2_2_v1.rptdesign&__format=pdf&&curso_id=' . $id . '&&__format=pdf&'));
            else //PDF CON VALOR LEGAL
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_lst_EstudiantesBoletinCentralizador_2_2_v1.rptdesign&__format=pdf&&curso_id=' . $id . '&&__format=pdf&'));
        //plan 2
        else
            if($esactivo==0)//PDF SIN VALOR LEGAL
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_lst_NoLegalEstudiantesBoletinCentralizador_p2_v1.rptdesign&__format=pdf&&curso_id=' . $id . '&&__format=pdf&'));
            else //PDF CON VALOR LEGAL
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_lst_EstudiantesBoletinCentralizador_p2_v1.rptdesign&__format=pdf&&curso_id=' . $id . '&&__format=pdf&'));

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function imprimirboletaelectronicaAction($id, Request $request)
    {
        $porciones = explode("|", $id);
        $id=$porciones[0];
        $b=$porciones[1];
        $p=$porciones[2];
        $arch = 'PNP_LIBRETA_ELECTRONICA_' . $id . '_' . date('Ymd') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        //plan 1
        if($b==1 and $p==1)
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_libreta_electronica_1_1_v1.rptdesign&__format=pdf&&estudiante_inscripcion_id=' . $id . '&&__format=pdf&'));
        elseif($b==1 and $p==2)
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_libreta_electronica_1_2_v1.rptdesign&__format=pdf&&estudiante_inscripcion_id=' . $id . '&&__format=pdf&'));
        elseif($b==2 and $p==1)
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_libreta_electronica_2_1_v1.rptdesign&__format=pdf&&estudiante_inscripcion_id=' . $id . '&&__format=pdf&'));
        elseif($b==2 and $p==2)
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_libreta_electronica_2_2_v1.rptdesign&__format=pdf&&estudiante_inscripcion_id=' . $id . '&&__format=pdf&'));
        //plan 2
        else
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_libreta_electronica_p2_v1.rptdesign&__format=pdf&&estudiante_inscripcion_id=' . $id . '&&__format=pdf&'));

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function listarfacilitadoresAction($id, Request $request)
    {
        
        $form = $this->createForm(new FacilitadorType());
        
        $data = $form->getData();
        switch ($id) {
            case 1:$id=80480300;break;
            case 2:$id=80730794;break;
            case 3:$id=80980569;break;
            case 4:$id=81230297;break;
            case 5:$id=81480201;break;
            case 6:$id=81730264;break;
            case 7:$id=81981501;break;
            case 8:$id=82230130;break;
            case 9:$id=82480050;break;
            default:
                $id=0;
                break;
        }

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

               
        //LISTA DE FACILITADORES
        $query = "
               select DISTINCT on (persona.carnet) persona.carnet,persona.complemento, persona.nombre, persona.paterno, persona.materno, persona.fecha_nacimiento, persona.rda,
                    CASE
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80480300 THEN
                                    'CHUQUISACA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80730794 THEN
                                    'LA PAZ'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80980569 THEN
                                    'COCHABAMBA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81230297 THEN
                                    'ORURO'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81480201 THEN
                                    'POTOSI'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81730264 THEN
                                    'TARIJA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81981501 THEN
                                    'SANTA CRUZ'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 82230130 THEN
                                    'BENI'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 82480050 THEN
                                    'PANDO'                         
                              END AS depto,
                         institucioneducativa_curso.lugar

                from institucioneducativa_curso 
                inner join maestro_inscripcion 
                on institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion .id
                inner join persona 
                on maestro_inscripcion .persona_id = persona.id

                where 
                institucioneducativa_curso.institucioneducativa_id = $id

                order by                 
                persona.carnet";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["carnet"] = $p["carnet"];
            $datos_filas["complemento"] = $p["complemento"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["fecha_nacimiento"] = $p["fecha_nacimiento"];
            $datos_filas["rda"] = $p["rda"];
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["lugar"] = $p["lugar"];
            $filas[] = $datos_filas;
        }        
        return $this->render('SiePnpBundle:Default:listarfacilitadores.html.twig', array('totales' => $filas,'idd'=>$id));
    }   

    public function eliminarduplicadosAction($id,$id_eliminar, Request $request)
    {
         //Conocer el departamento del usuario para que solo pueda ver de su departamento
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        $userId = $this->session->get('userId');
         $query = "
               SELECT lt.lugar as lugar
               FROM lugar_tipo lt,
               usuario_rol ur 
               WHERE ur.lugar_tipo_id=lt.id and ur.esactivo=true and ur.usuario_id=$userId";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $lugar_usuario = $p["lugar"];
        }

        $lugar_usuario=strtoupper($lugar_usuario);
       
        switch ($lugar_usuario) {
            case 'CHUQUISACA': $id=80480300; break;
            case 'LA PAZ': $id=80730794; break;
            case 'COCHABAMBA': $id=80980569; break;
            case 'ORURO': $id=81230297; break;
            case 'POTOSI': $id=81480201; break;
            case 'TARIJA': $id=81730264; break;
            case 'SANTA CRUZ': $id=81981501; break;
            case 'BENI': $id=82230130; break;
            case 'PANDO': $id=82480050; break;
            default: $id=0; break;
        }

        if($id==0)$where="";else $where="where institucioneducativa_curso.institucioneducativa_id = $id";


        if($id_eliminar!=0){
            // id de la tabla institucion_educativa
            $institucioneducativa_curso_id=$id_eliminar;    
            $em = $this->getDoctrine()->getManager();
            
            $em->getConnection()->beginTransaction();

            try {
                // id de la tabla maestro_inscripcion
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($institucioneducativa_curso_id);
                $maestroinscripcion_id=$result->getMaestroInscripcionAsesor()->getId();

                // id de la tabla institucion_curso_oferta
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findByinsitucioneducativaCurso($institucioneducativa_curso_id);
                $institucioneducativa_curso_oferta_id = array();
                foreach ($result as $results) {
                    $institucioneducativa_curso_oferta_id[]=$results->getId();
                }
                // id de la tabla institucion_curso_datos
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findByinstitucioneducativaCurso($institucioneducativa_curso_id);
                $institucioneducativa_curso_datos_id = array();
                foreach ($result as $results) {
                    $institucioneducativa_curso_datos_id[]=$results->getId();
                }
                //id de la tabla institucioneducativa_curso_oferta_maestro
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('maestroInscripcion' => $maestroinscripcion_id, 'institucioneducativaCursoOferta' => $institucioneducativa_curso_oferta_id )); 
                $institucioneducativa_curso_oferta_maestro_id = array();
                foreach ($result as $results) {
                    $institucioneducativa_curso_oferta_maestro_id[]=$results->getId();
                }
                // id de la tabla estudiante_inscripcion
                $result=$em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findByinstitucioneducativaCurso($institucioneducativa_curso_id);
                $estudiante_inscripcion_id = array();
                foreach ($result as $results) {
                    $estudiante_inscripcion_id[]=$results->getId();
                }
                // nuevo id de la tabla apodearado inscripcion
                $result=$em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findByestudianteInscripcion($estudiante_inscripcion_id);
                $estudiante_apoderado_inscripcion_id = array();
                foreach ($result as $results) {
                    $estudiante_apoderado_inscripcion_id[]=$results->getId();
                }
                // id de la tabla estudiante_asignatura
                $result=$em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findByestudianteInscripcion($estudiante_inscripcion_id);
                $estudiante_asignatura_id = array();
                foreach ($result as $results) {
                    $estudiante_asignatura_id[]=$results->getId();
                }
                // id de la tabla estudiante_nota
                $result=$em->getRepository('SieAppWebBundle:EstudianteNota')->findByestudianteAsignatura($estudiante_asignatura_id);
                $estudiante_nota_id = array();
                foreach ($result as $results) {
                    $estudiante_nota_id[]=$results->getId();
                }
                ///////////plan 2
                // id estduoante_socieconomico_alternativa 
                $result=$em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAlternativa')->findByestudianteIEstudianteInscripcionSocnscripcion($estudiante_inscripcion_id);
                $estudiante_inscripcion_socioeconomico_alternativa_id = array();
                foreach ($result as $results) {
                    $estudiante_inscripcion_socioeconomico_alternativa_id[]=$results->getId();
                }
                
                 //PLAN 2
                //id alt_moduloemergente
                $query = "SELECT ico.id from institucioneducativa_curso ic
                join institucioneducativa_curso_oferta ico on ico.insitucioneducativa_curso_id=ic.id
                where ic.id=$institucioneducativa_curso_id and ico.asignatura_tipo_id=2012
                ";
                $stmt = $db->prepare($query);
                $params = array();
                $stmt->execute($params);
                $po = $stmt->fetchAll();
                
                foreach ($po as $p) {
                    $id_ico = $p["id"];
                }

                $result=$em->getRepository('SieAppWebBundle:AltModuloemergente')->findByinstitucioneducativaCursoOferta($id_ico);
                $alt_moduloemergente_id = array();
                foreach ($result as $results) {
                    $alt_moduloemergente_id[]=$results->getId();
                }   

                //id estudiante_nota_cualitativa
                $result=$em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findByestudianteInscripcion($estudiante_inscripcion_id);
                $estudiante_nota_cualitativa_id = array();
                foreach ($result as $results) {
                    $estudiante_nota_cualitativa_id[]=$results->getId();
                }
                ////////////////ELMINANDO
                // Eliminar Notas de los estudiantes
                $result=$em->getRepository('SieAppWebBundle:EstudianteNota')->findById($estudiante_nota_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }
                $em->flush();
                // Eliminar Asignaturas
                $result=$em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findById($estudiante_asignatura_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                // Eliminar Apoderado inscripcion
                $result=$em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findById($estudiante_apoderado_inscripcion_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                //////////ELMINAR PLAN 2
                //1.- estudiante_inscripcion_socioeconomico_alternativa
                $result=$em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAlternativa')->findById($estudiante_inscripcion_socioeconomico_alternativa_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                // 2.- alt_modulo_emergente
                $result=$em->getRepository('SieAppWebBundle:AltModuloemergente')->findById($alt_moduloemergente_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                //3.- estudiante_nota_cualitativa
                $result=$em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findById($estudiante_nota_cualitativa_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                //////////////////FIN PLAN 2
                // Eliminar Inscripciones
                $result=$em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findById($estudiante_inscripcion_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                // Eliminar Institucioneducativa Curso Oferta Maestro
                 $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findById($institucioneducativa_curso_oferta_maestro_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                // Eliminar Institucion educativa Curso Datos
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findById($institucioneducativa_curso_datos_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                // Eliminar Institucion educativa Curso Oferta
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findById($institucioneducativa_curso_oferta_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                // Eliminar Institucion educativa Curso
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findById($institucioneducativa_curso_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                // Eliminar Maestro Inscripcion
               /* $result=$em->getRepository('SieAppWebBundle:MaestroInscripcion')->findById($maestroinscripcion_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }   */        
                $em->flush();    
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Registro Eliminado con exito.'
                    );      
                $em->getConnection()->commit();
            }
            catch (Exception $e) {
                 $em->getConnection()->rollback();
                 $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Existio un problema al Eliminar.'
                    );      
                throw $e;
           }
        }

        //echo '<script>alert("holas: "+ '.$maestroinscripcion_id.');</script>';

        $form = $this->createForm(new FacilitadorType());
        $dep=$id;
        $data = $form->getData();

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

               
        //LISTA DE TOTALES POR GESTION DEPTO PARTE Y BLOQUE DUPLICADOS
        $query = "
                select persona.carnet,persona.complemento, persona.nombre, persona.paterno, persona.materno,
                    institucioneducativa_curso.fecha_inicio, institucioneducativa_curso.fecha_fin,
                    institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id,
                    institucioneducativa_curso.id,icd.plancurricular_tipo_id,ct.ciclo as nciclo,gt.grado as ngrado,

                    CASE
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80480300 THEN
                                    'CHUQUISACA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80730794 THEN
                                    'LA PAZ'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80980569 THEN
                                    'COCHABAMBA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81230297 THEN
                                    'ORURO'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81480201 THEN
                                    'POTOSI'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81730264 THEN
                                    'TARIJA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81981501 THEN
                                    'SANTA CRUZ'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 82230130 THEN
                                    'BENI'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 82480050 THEN
                                    'PANDO'                         
                              END AS depto,
                         institucioneducativa_curso.lugar,icd.esactivo

                from institucioneducativa_curso 
                inner join maestro_inscripcion 
                on institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion .id
                inner join institucioneducativa_curso_datos icd 
                on icd.institucioneducativa_curso_id=institucioneducativa_curso.id
                inner join persona 
                on maestro_inscripcion .persona_id = persona.id
                INNER JOIN ciclo_tipo ct ON institucioneducativa_curso.ciclo_tipo_id=ct.id
                INNER JOIN grado_tipo gt ON institucioneducativa_curso.grado_tipo_id=gt.id
                                inner join 

(select carnet,complemento,nombre,paterno,materno,fecha_inicio,fecha_fin,ciclo_tipo_id,grado_tipo_id,depto,lugar    
from (
 select persona.carnet,persona.complemento, persona.nombre, persona.paterno, persona.materno,
                    institucioneducativa_curso.fecha_inicio, institucioneducativa_curso.fecha_fin,
                    institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id,
                    institucioneducativa_curso.id,

                    CASE
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80480300 THEN
                                    'CHUQUISACA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80730794 THEN
                                    'LA PAZ'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80980569 THEN
                                    'COCHABAMBA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81230297 THEN
                                    'ORURO'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81480201 THEN
                                    'POTOSI'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81730264 THEN
                                    'TARIJA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81981501 THEN
                                    'SANTA CRUZ'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 82230130 THEN
                                    'BENI'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 82480050 THEN
                                    'PANDO'                         
                              END AS depto,
                         institucioneducativa_curso.lugar

                from institucioneducativa_curso 
                inner join maestro_inscripcion 
                on institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion .id
                inner join persona 
                on maestro_inscripcion .persona_id = persona.id   
                $where              
) t1
group by carnet,complemento,nombre,paterno,materno,fecha_inicio,fecha_fin,ciclo_tipo_id,grado_tipo_id,depto,lugar
having count(*)>1 ) t2 
on t2.carnet=persona.carnet and t2.complemento=persona.complemento and t2.nombre=persona.nombre and t2.paterno=persona.paterno and t2.materno=persona.materno

and t2.fecha_inicio=institucioneducativa_curso.fecha_inicio and t2.fecha_fin=institucioneducativa_curso.fecha_fin AND
t2.ciclo_tipo_id=institucioneducativa_curso.ciclo_tipo_id and t2.grado_tipo_id= institucioneducativa_curso.grado_tipo_id
and depto=t2.depto and t2.lugar=institucioneducativa_curso.lugar
order by t2.fecha_inicio";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["carnet"] = $p["carnet"];
            $datos_filas["complemento"] = $p["complemento"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["fecha_inicio"] = $p["fecha_inicio"];
            $datos_filas["fecha_fin"] = $p["fecha_fin"];
            $datos_filas["ciclo_tipo_id"] = $p["ciclo_tipo_id"];
            $datos_filas["grado_tipo_id"] = $p["grado_tipo_id"];
            $datos_filas["id"] = $p["id"];
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["lugar"] = $p["lugar"];
            $datos_filas["esactivo"] = $p["esactivo"];
            $datos_filas["nciclo"] = $p["nciclo"];
            $datos_filas["ngrado"] = $p["ngrado"];
            $datos_filas["plan"] = $p["plancurricular_tipo_id"];
            if ($p["ngrado"]=="Primero")$datos_filas["ngrado"]=1;
            if ($p["ngrado"]=="Segundo")$datos_filas["ngrado"]=2;
            $filas[] = $datos_filas;
        } 
       
        return $this->render('SiePnpBundle:Default:eliminarduplicados.html.twig', array('totales' => $filas,'idd'=>$id,'id'=>$dep,'lugar_usuario'=>$lugar_usuario));
    }

       public function cursovacioAction($id_eliminar, Request $request)
    {
         //Conocer el departamento del usuario para que solo pueda eliminar de su departamento
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        $userId = $this->session->get('userId');
         $query = "
               SELECT lt.lugar as lugar
               FROM lugar_tipo lt,
               usuario_rol ur 
               WHERE ur.lugar_tipo_id=lt.id and ur.esactivo=true and ur.usuario_id=$userId";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $lugar_usuario = $p["lugar"];
        }
         $lugar_usuario=strtoupper($lugar_usuario);
       
       
        //echo "<script>alert('$lugar_usuario')</script>";

        if($id_eliminar!=0){
            // id de la tabla institucion_educativa
            $institucioneducativa_curso_id=$id_eliminar;    
            $em = $this->getDoctrine()->getManager();
            
            $em->getConnection()->beginTransaction();

            try {
                // id de la tabla maestro_inscripcion
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($institucioneducativa_curso_id);
                $maestroinscripcion_id=$result->getMaestroInscripcionAsesor()->getId();
                // id de la tabla institucion_curso_oferta
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findByinsitucioneducativaCurso($institucioneducativa_curso_id);
                $institucioneducativa_curso_oferta_id = array();
                foreach ($result as $results) {
                    $institucioneducativa_curso_oferta_id[]=$results->getId();
                }
                //id de la tabla institucioneducativa_curso_oferta_maestro
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('maestroInscripcion' => $maestroinscripcion_id, 'institucioneducativaCursoOferta' => $institucioneducativa_curso_oferta_id )); 
                $institucioneducativa_curso_oferta_maestro_id = array();
                foreach ($result as $results) {
                    $institucioneducativa_curso_oferta_maestro_id[]=$results->getId();
                }
                // id de la tabla estudiante_inscripcion
                $result=$em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findByinstitucioneducativaCurso($institucioneducativa_curso_id);
                $estudiante_inscripcion_id = array();
                foreach ($result as $results) {
                    $estudiante_inscripcion_id[]=$results->getId();
                }
                // id de la tabla estudiante_asignatura
                $result=$em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findByestudianteInscripcion($estudiante_inscripcion_id);
                $estudiante_asignatura_id = array();
                foreach ($result as $results) {
                    $estudiante_asignatura_id[]=$results->getId();
                }
                // id de la tabla estudiante_nota
                $result=$em->getRepository('SieAppWebBundle:EstudianteNota')->findByestudianteAsignatura($estudiante_asignatura_id);
                $estudiante_nota_id = array();
                foreach ($result as $results) {
                    $estudiante_nota_id[]=$results->getId();
                }
                //PLAN 2
                //id alt_moduloemergente

                $result=$em->getRepository('SieAppWebBundle:AltModuloemergente')->findByinstitucioneducativaCurso($institucioneducativa_curso_id);
                $alt_moduloemergente_id = array();
                foreach ($result as $results) {
                    $alt_moduloemergente_id[]=$results->getId();
                }   
                ////////////////ELMINANDO
                //plan 2 eliminando modulo emergente
                $result=$em->getRepository('SieAppWebBundle:AltModuloemergente')->findById($alt_moduloemergente_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }
                $em->flush();
                // Eliminar Notas de los estudiantes
                $result=$em->getRepository('SieAppWebBundle:EstudianteNota')->findById($estudiante_nota_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }
                $em->flush();
                // Eliminar Asignaturas
                $result=$em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findById($estudiante_asignatura_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                // Eliminar Inscripciones
                $result=$em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findById($estudiante_inscripcion_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                // Eliminar Institucioneducativa Curso Oferta Maestro
                 $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findById($institucioneducativa_curso_oferta_maestro_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                // Eliminar Institucion educativa Curso Oferta
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findById($institucioneducativa_curso_oferta_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                // Eliminar Institucion educativa Curso
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findById($institucioneducativa_curso_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                // Eliminar Maestro Inscripcion
                $result=$em->getRepository('SieAppWebBundle:MaestroInscripcion')->findById($maestroinscripcion_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }           
                $em->flush();    
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Registro Eliminado con exito.'
                    );      
                $em->getConnection()->commit();
            }
            catch (Exception $e) {
                 $em->getConnection()->rollback();
                 $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Existio un problema al Eliminar.'
                    );      
                throw $e;
           }
        }

        //echo '<script>alert("holas: "+ '.$maestroinscripcion_id.');</script>';

        $form = $this->createForm(new FacilitadorType());
        $data = $form->getData();
        

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

               
        //LISTA DE TOTALES POR GESTION DEPTO PARTE Y BLOQUE DUPLICADOS
        $query = "
                select * from(
select t1.id,t1.carnet,t1.nombre,t1.paterno,t1.materno, t1.fecha_inicio,t1.fecha_fin,t1.ciclo_tipo_id,t1.grado_tipo_id,
t1.departamento,t1.provincia,count(t2.nombre) 
from ( 
select institucioneducativa_curso.id, 
                      institucioneducativa_curso.institucioneducativa_id, 
                      institucioneducativa_curso.gestion_tipo_id, 
                      institucioneducativa_curso.nivel_tipo_id, 
                      institucioneducativa_curso.ciclo_tipo_id, 
                      institucioneducativa_curso.grado_tipo_id,
                      institucioneducativa_curso.nro_materias,
                      institucioneducativa_curso.fecha_fin, 
                      institucioneducativa_curso.fecha_inicio,
                      institucioneducativa_curso.lugartipo_id as departamento_id,
                      lugar_tipo.lugar as departamento,
                      institucioneducativa_curso.lugar as provincia,
                      estudiante_inscripcion.lugar as municipio, 
                      estudiante_inscripcion.lugarcurso as localidad,
                      institucioneducativa_curso.facilitador,
                      persona.nombre,
                      persona.paterno,
                      persona.materno,
                      persona.carnet
                    FROM 
                      institucioneducativa_curso INNER JOIN estudiante_inscripcion ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
                      INNER JOIN lugar_tipo ON lugar_tipo.id = institucioneducativa_curso.lugartipo_id
                      INNER JOIN maestro_inscripcion ON institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion.id
                      INNER JOIN persona ON maestro_inscripcion.persona_id = persona.id


                   GROUP BY
                      institucioneducativa_curso.id,
                      institucioneducativa_curso.institucioneducativa_id, 
                      institucioneducativa_curso.gestion_tipo_id, 
                      institucioneducativa_curso.ciclo_tipo_id, 
                      institucioneducativa_curso.grado_tipo_id,
                      institucioneducativa_curso.nro_materias,
                      institucioneducativa_curso.fecha_fin, 
                      institucioneducativa_curso.fecha_inicio,
                      institucioneducativa_curso.lugartipo_id,
                      lugar_tipo.lugar,
                      institucioneducativa_curso.lugar,
                      estudiante_inscripcion.lugar, 
                      estudiante_inscripcion.lugarcurso,
                      institucioneducativa_curso.facilitador,
                      persona.nombre,
                      persona.paterno,
                      persona.materno,
                      persona.carnet) as t1 left join (
    SELECT 
                                                estudiante.id as estudiante_id,
                                                institucioneducativa_curso.id,
                                                estudiante.codigo_rude, 
                                                estudiante.carnet_identidad, 
                                                estudiante.paterno, 
                                                estudiante.materno, 
                                                estudiante.nombre, 
                                                estudiante.fecha_nacimiento, 
                                                estudiante.genero_tipo_id,
                                                genero_tipo.genero,
                                                estudiante.observacionadicional,
                                                estudiante_inscripcion.estadomatricula_tipo_id as matricula_estado_id,
                                                estadomatricula_tipo.estadomatricula,
                                                estudiante_inscripcion.id as inscripcion_id
                                            FROM 
                                                estudiante INNER JOIN estudiante_inscripcion ON estudiante.id = estudiante_inscripcion.estudiante_id
                                                INNER JOIN genero_tipo ON genero_tipo.id = estudiante.genero_tipo_id
                                                INNER JOIN estadomatricula_tipo ON estadomatricula_tipo.ID = estudiante_inscripcion.estadomatricula_inicio_tipo_id
                                                INNER JOIN institucioneducativa_curso ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
                            ) as t2 on t1.id=t2.id
GROUP BY t1.id,t1.carnet,t1.nombre,t1.paterno,t1.materno, t1.fecha_inicio,t1.fecha_fin,t1.ciclo_tipo_id,t1.grado_tipo_id,
t1.departamento,t1.provincia ORDER BY count) as tt1 where count=0 and $where";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["carnet"] = $p["carnet"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["fecha_inicio"] = $p["fecha_inicio"];
            $datos_filas["fecha_fin"] = $p["fecha_fin"];
            $datos_filas["ciclo_tipo_id"] = $p["ciclo_tipo_id"];
            $datos_filas["grado_tipo_id"] = $p["grado_tipo_id"];
            $datos_filas["id"] = $p["id"];
            $datos_filas["dep"] = $p["departamento"];
            $datos_filas["lugar"] = $p["provincia"];
            $filas[] = $datos_filas;
        } 
       
        return $this->render('SiePnpBundle:Default:cursovacio.html.twig', array('totales' => $filas,'lugar_usuario'=>$lugar_usuario));
    }

    public function cursovacionewAction($id_eliminar, Request $request)
    {
         //Conocer el departamento del usuario para que solo pueda eliminar de su departamento
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        $userId = $this->session->get('userId');
         $query = "
               SELECT lt.lugar as lugar
               FROM lugar_tipo lt,
               usuario_rol ur 
               WHERE ur.lugar_tipo_id=lt.id and ur.esactivo=true and ur.usuario_id=$userId";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $lugar_usuario = $p["lugar"];
        }
    
        $lugar_usuario=strtoupper($lugar_usuario);
         switch ($lugar_usuario) {
            case 'CHUQUISACA': $id_curso_ver=80480300; break;
            case 'LA PAZ': $id_curso_ver=80730794; break;
            case 'COCHABAMBA': $id_curso_ver=80980569; break;
            case 'ORURO': $id_curso_ver=81230297; break;
            case 'POTOSI': $id_curso_ver=81480201; break;
            case 'TARIJA': $id_curso_ver=81730264; break;
            case 'SANTA CRUZ': $id_curso_ver=81981501; break;
            case 'BENI': $id_curso_ver=82230130; break;
            case 'PANDO': $id_curso_ver=82480050; break;
            default: $id_curso_ver=0; break;
        }
        
            
        if($id_curso_ver==0)$where="";else $where="and institucioneducativa_curso.institucioneducativa_id = $id_curso_ver";
        //echo "<script>alert('$lugar_usuario')</script>";


        if($id_eliminar!=0){
            // id de la tabla institucion_educativa
            $institucioneducativa_curso_id=$id_eliminar;    
            $em = $this->getDoctrine()->getManager();
            
            $em->getConnection()->beginTransaction();

            try {
                // id de la tabla maestro_inscripcion
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($institucioneducativa_curso_id);
                $maestroinscripcion_id=$result->getMaestroInscripcionAsesor()->getId();
                // id de la tabla institucion_curso_oferta
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findByinsitucioneducativaCurso($institucioneducativa_curso_id);
                $institucioneducativa_curso_oferta_id = array();
                foreach ($result as $results) {
                    $institucioneducativa_curso_oferta_id[]=$results->getId();
                }
                //id de la tabla institucioneducativa_curso_oferta_maestro
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('maestroInscripcion' => $maestroinscripcion_id, 'institucioneducativaCursoOferta' => $institucioneducativa_curso_oferta_id )); 
                $institucioneducativa_curso_oferta_maestro_id = array();
                foreach ($result as $results) {
                    $institucioneducativa_curso_oferta_maestro_id[]=$results->getId();
                }

                ////////////////////NUEVO
                //id de la tabla institucioneducativa_curso_datos
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findOneByinstitucioneducativaCurso($institucioneducativa_curso_id);
                $institucioneducativa_curso_datos_id=$result->getId();
                ///////////////////FIN NUEV

                // id de la tabla estudiante_inscripcion
                $result=$em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findByinstitucioneducativaCurso($institucioneducativa_curso_id);
                $estudiante_inscripcion_id = array();
                foreach ($result as $results) {
                    $estudiante_inscripcion_id[]=$results->getId();
                }
                // id de la tabla estudiante_asignatura
                $result=$em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findByestudianteInscripcion($estudiante_inscripcion_id);
                $estudiante_asignatura_id = array();
                foreach ($result as $results) {
                    $estudiante_asignatura_id[]=$results->getId();
                }
                // id de la tabla estudiante_nota
                $result=$em->getRepository('SieAppWebBundle:EstudianteNota')->findByestudianteAsignatura($estudiante_asignatura_id);
                $estudiante_nota_id = array();
                foreach ($result as $results) {
                    $estudiante_nota_id[]=$results->getId();
                }
                ////////////////ELMINANDO
                // Eliminar Notas de los estudiantes
                $result=$em->getRepository('SieAppWebBundle:EstudianteNota')->findById($estudiante_nota_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }
                $em->flush();
                // Eliminar Asignaturas
                $result=$em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findById($estudiante_asignatura_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                // Eliminar Inscripciones
                $result=$em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findById($estudiante_inscripcion_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                // Eliminar Institucioneducativa Curso Oferta Maestro
                 $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findById($institucioneducativa_curso_oferta_maestro_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();

                 //PLAN 2
                //id alt_moduloemergente
                $query = "SELECT ico.id from institucioneducativa_curso ic
                join institucioneducativa_curso_oferta ico on ico.insitucioneducativa_curso_id=ic.id
                where ic.id=$institucioneducativa_curso_id and ico.asignatura_tipo_id=2012
                ";
                $stmt = $db->prepare($query);
                $params = array();
                $stmt->execute($params);
                $po = $stmt->fetchAll();
                
                foreach ($po as $p) {
                    $id_ico = $p["id"];
                }

                $result=$em->getRepository('SieAppWebBundle:AltModuloemergente')->findByinstitucioneducativaCursoOferta($id_ico);
                $alt_moduloemergente_id = array();
                foreach ($result as $results) {
                    $alt_moduloemergente_id[]=$results->getId();
                }   
                ////////////////ELMINANDO
                //plan 2 eliminando modulo emergente
                $result=$em->getRepository('SieAppWebBundle:AltModuloemergente')->findById($alt_moduloemergente_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }
                $em->flush();

                // Eliminar Institucion educativa Curso Oferta
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findById($institucioneducativa_curso_oferta_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();

                //////////////////////Inicio Nuevo
                //eliminar institucion educativa curso datos
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findById($institucioneducativa_curso_datos_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                /////////////////////fin Nuevo

                // Eliminar Institucion educativa Curso
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findById($institucioneducativa_curso_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }          
                $em->flush();
                // Eliminar Maestro Inscripcion
               /* $result=$em->getRepository('SieAppWebBundle:MaestroInscripcion')->findById($maestroinscripcion_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }           */
                $em->flush();    
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Registro Eliminado con exito.'
                    );      
                $em->getConnection()->commit();
            }
            catch (Exception $e) {
                 $em->getConnection()->rollback();
                 $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Existio un problema al Eliminar.'
                    );      
                throw $e;
           }
        }

        //echo '<script>alert("holas: "+ '.$maestroinscripcion_id.');</script>';

        $form = $this->createForm(new FacilitadorType());
        $data = $form->getData();
        

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

               
        //LISTA DE TOTALES POR GESTION DEPTO PARTE Y BLOQUE DUPLICADOS
        $query = "
               select * from(
                select t1.id,t1.carnet,t1.complemento,t1.nombre,t1.paterno,t1.materno, t1.fecha_inicio,t1.fecha_fin,t1.ciclo_tipo_id,t1.grado_tipo_id,
                t1.departamento,t1.provincia,count(t2.nombre),t1.plancurricular_tipo_id,t1.nciclo,t1.ngrado
                from (
                select institucioneducativa_curso.id, 
                institucioneducativa_curso.institucioneducativa_id, 
                institucioneducativa_curso.gestion_tipo_id, 
                institucioneducativa_curso.nivel_tipo_id, 
                institucioneducativa_curso.ciclo_tipo_id, 
                institucioneducativa_curso.grado_tipo_id,
                institucioneducativa_curso.nro_materias,
                institucioneducativa_curso.fecha_fin, 
                institucioneducativa_curso.fecha_inicio,
                institucioneducativa_curso.lugartipo_id as departamento_id,
                lugar_tipo.lugar as departamento,
                institucioneducativa_curso.lugar as provincia,
                lt.lugar as municipio,
                icd.localidad as localidad,
                institucioneducativa_curso.facilitador,
                persona.nombre,
                persona.paterno,
                persona.materno,
                persona.carnet,
                persona.complemento,
                                icd.plancurricular_tipo_id,
                                ct.ciclo as nciclo,
                                gt.grado as ngrado
                FROM 
                institucioneducativa_curso 
                INNER JOIN institucioneducativa_curso_datos as icd ON icd.institucioneducativa_curso_id=institucioneducativa_curso.id
                INNER JOIN lugar_tipo as lt ON lt.id = icd.lugar_tipo_id_seccion 
                INNER JOIN lugar_tipo ON lugar_tipo.id = institucioneducativa_curso.lugartipo_id
                INNER JOIN maestro_inscripcion ON institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion.id
                INNER JOIN persona ON maestro_inscripcion.persona_id = persona.id
                                INNER JOIN ciclo_tipo ct ON institucioneducativa_curso.ciclo_tipo_id=ct.id
                                INNER JOIN grado_tipo gt ON institucioneducativa_curso.grado_tipo_id=gt.id
                where 1=1 $where

                GROUP BY
                                icd.plancurricular_tipo_id,
                                ct.ciclo, gt.grado ,
                institucioneducativa_curso.id,
                institucioneducativa_curso.institucioneducativa_id, 
                institucioneducativa_curso.gestion_tipo_id, 
                institucioneducativa_curso.ciclo_tipo_id, 
                institucioneducativa_curso.grado_tipo_id,
                institucioneducativa_curso.nro_materias,
                institucioneducativa_curso.fecha_fin, 
                institucioneducativa_curso.fecha_inicio,
                institucioneducativa_curso.lugartipo_id,
                lugar_tipo.lugar,
                institucioneducativa_curso.lugar,
                lt.lugar,
                icd.localidad,
                institucioneducativa_curso.facilitador,
                persona.nombre,
                persona.paterno,
                persona.materno,
                persona.carnet,
                persona.complemento) as t1 left join (
                SELECT 
                estudiante.id as estudiante_id,
                institucioneducativa_curso.id,
                estudiante.codigo_rude, 
                estudiante.carnet_identidad,
                estudiante.complemento,
                estudiante.paterno, 
                estudiante.materno, 
                estudiante.nombre, 
                estudiante.fecha_nacimiento, 
                estudiante.genero_tipo_id,
                genero_tipo.genero,
                estudiante.observacionadicional,
                estudiante_inscripcion.estadomatricula_tipo_id as matricula_estado_id,
                estadomatricula_tipo.estadomatricula,
                estudiante_inscripcion.id as inscripcion_id
                                
                FROM 
                estudiante INNER JOIN estudiante_inscripcion ON estudiante.id = estudiante_inscripcion.estudiante_id
                INNER JOIN genero_tipo ON genero_tipo.id = estudiante.genero_tipo_id
                INNER JOIN estadomatricula_tipo ON estadomatricula_tipo.ID = estudiante_inscripcion.estadomatricula_inicio_tipo_id
                INNER JOIN institucioneducativa_curso ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
                ) as t2 on t1.id=t2.id
                GROUP BY t1.id,t1.carnet,t1.complemento,t1.plancurricular_tipo_id,t1.nombre,t1.paterno,t1.materno, t1.fecha_inicio,t1.fecha_fin,t1.ciclo_tipo_id,t1.grado_tipo_id,
                t1.departamento,t1.provincia ,t1.nciclo,t1.ngrado ORDER BY count) as tt1 where count=0";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["carnet"] = $p["carnet"];
            $datos_filas["complemento"] = $p["complemento"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["fecha_inicio"] = $p["fecha_inicio"];
            $datos_filas["fecha_fin"] = $p["fecha_fin"];
            $datos_filas["ciclo_tipo_id"] = $p["ciclo_tipo_id"];
            $datos_filas["grado_tipo_id"] = $p["grado_tipo_id"];
            $datos_filas["id"] = $p["id"];
            $datos_filas["dep"] = $p["departamento"];
            $datos_filas["lugar"] = $p["provincia"];
            $datos_filas["nciclo"] = $p["nciclo"];
            $datos_filas["ngrado"] = $p["ngrado"];
            $datos_filas["plan"] = $p["plancurricular_tipo_id"];
                if ($p["ngrado"]=="Primero")$datos_filas["ngrado"]=1;
                if ($p["ngrado"]=="Segundo")$datos_filas["ngrado"]=2;
            $filas[] = $datos_filas;
        } 
       
        return $this->render('SiePnpBundle:Default:cursovacionew.html.twig', array('totales' => $filas,'lugar_usuario'=>$lugar_usuario));
    }


 public function listararchivos_editAction(Request $request)
    {
        $form = $this->createForm(new FacilitadorType());
        
        $data = $form->getData();
        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $userId = $this->session->get('userId');
         $query = "
               SELECT lt.lugar as lugar
               FROM lugar_tipo lt,
               usuario_rol ur 
               WHERE ur.lugar_tipo_id=lt.id and ur.esactivo=true and ur.usuario_id=$userId";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $lugar_usuario = $p["lugar"];
        }
        $lugar_usuario=strtoupper($lugar_usuario);
       
        switch ($lugar_usuario) {
            case 'CHUQUISACA': $id=80480300; break;
            case 'LA PAZ': $id=80730794; break;
            case 'COCHABAMBA': $id=80980569; break;
            case 'ORURO': $id=81230297; break;
            case 'POTOSI': $id=81480201; break;
            case 'TARIJA': $id=81730264; break;
            case 'SANTA CRUZ': $id=81981501; break;
            case 'BENI': $id=82230130; break;
            case 'PANDO': $id=82480050; break;
            default: $id=0; break;
        }
        //print_r($lugar_usuario); die;      
        //LISTA DE TOTALES POR GESTION DEPTO PARTE Y BLOQUE
        $query = "
                select persona.carnet, persona.nombre, persona.paterno, persona.materno,
                    institucioneducativa_curso.fecha_inicio, institucioneducativa_curso.fecha_fin,
                    institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id,
                    institucioneducativa_curso.id,

                    CASE
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80480300 THEN
                                    'CHUQUISACA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80730794 THEN
                                    'LA PAZ'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 80980569 THEN
                                    'COCHABAMBA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81230297 THEN
                                    'ORURO'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81480201 THEN
                                    'POTOSI'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81730264 THEN
                                    'TARIJA'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 81981501 THEN
                                    'SANTA CRUZ'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 82230130 THEN
                                    'BENI'
                                WHEN institucioneducativa_curso.institucioneducativa_id = 82480050 THEN
                                    'PANDO'                         
                              END AS depto,
                         institucioneducativa_curso.lugar

                from institucioneducativa_curso 
                inner join maestro_inscripcion 
                on institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion .id
                inner join persona 
                on maestro_inscripcion .persona_id = persona.id

                where 
                institucioneducativa_curso.institucioneducativa_id = $id

                order by                 
                institucioneducativa_curso.fecha_inicio,
                institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["carnet"] = $p["carnet"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["fecha_inicio"] = $p["fecha_inicio"];
            $datos_filas["fecha_fin"] = $p["fecha_fin"];
            $datos_filas["ciclo_tipo_id"] = $p["ciclo_tipo_id"];
            $datos_filas["grado_tipo_id"] = $p["grado_tipo_id"];
            $datos_filas["id"] = $p["id"];
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["lugar"] = $p["lugar"];
            $filas[] = $datos_filas;
        }        
        return $this->render('SiePnpBundle:Default:listararchivos_edit.html.twig', array('totales' => $filas,'idd'=>$id));
    }

     public function listararchivos_editnewAction($id,$gestion,Request $request)
    {
        $form = $this->createForm(new FacilitadorType());
        $data = $form->getData();
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $usuario_id = $this->session->get('userId');
        $rol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneByUsuario($usuario_id);    
        $rol=$rol->getRolTipo()->getId();
        /////SACAMOS LA GESTION INICIAL Y FINAL
        $query = "SELECT min((extract(year from ic.fecha_fin))) as gestion_ini,
                        max((extract(year from ic.fecha_fin))) as gestion_fin 
                        FROM institucioneducativa_curso ic
                        join institucioneducativa_curso_datos icd on ic.id=icd.institucioneducativa_curso_id";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        foreach ($po as $p) {
            $gestion_ini_t = $p["gestion_ini"];
            $gestion_fin_t = $p["gestion_fin"];
        } 
             
        ///////MODOFICAR FECHA //
        $userId = $this->session->get('userId');
        if($request->getMethod()=="POST") {
            $curso_id=$request->get("curso_id");
            $fecha_inicio=$request->get("fecha_inicio");
            $fecha_fin=$request->get("fecha_fin");
            $gestion_g= substr($fecha_fin,-4);

            $institucion_educativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneById($curso_id);
            $ie=$institucion_educativa->getInstitucioneducativa()->getId();
            $id_maestro_inscripcion=$institucion_educativa->getMaestroInscripcionAsesor()->getId();


            $le_jurisdiccion_g=$em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ie);
            $le_jurisdiccion_g=$le_jurisdiccion_g->getLeJuridicciongeografica()->getId();

            $institucioneducativa_sucursal_id=$em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array(
                'institucioneducativa'  => $ie,
                'gestionTipo' => $gestion_g,
                'leJuridicciongeografica'=> $le_jurisdiccion_g,
                'periodoTipoId'=>5));

            if($institucioneducativa_sucursal_id)
                $institucioneducativa_sucursal_id=$institucioneducativa_sucursal_id->getId();
            else{
                 $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal');");
                    $query->execute();
                    $institucioneducativa_sucursal_id = new InstitucioneducativaSucursal();
                    $institucioneducativa_sucursal_id->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find(0));
                    $institucioneducativa_sucursal_id->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie));
                    $institucioneducativa_sucursal_id->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion_g));
                    $institucioneducativa_sucursal_id->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($le_jurisdiccion_g));  
                    $institucioneducativa_sucursal_id->setCodCerradaId(10);       
                    $institucioneducativa_sucursal_id->setPeriodoTipoId(5);       
                    $institucioneducativa_sucursal_id->setNombreSubcea("PNP");

                    $em->persist($institucioneducativa_sucursal_id);
                    $em->flush(); 
            }
        //$maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($institucioneducativa_sucursal_id));

        // buscar si existe ese maestro inscipcion


            $product = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($id_maestro_inscripcion);
            $persona=$product->getPersona()->getId();
            
             $maestroinscripcion=$em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array(
                'cargoTipo'  => 14, 'institucioneducativa' => $ie, 'gestionTipo'=>$gestion_g, 'persona'=>$persona,'periodoTipo'=>5,'institucioneducativaSucursal'=>$institucioneducativa_sucursal_id
            ));
            if (!$maestroinscripcion){
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('maestro_inscripcion');");
                $query->execute();
                $maestroinscripcion = new MaestroInscripcion();
                $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->find(14));
                $maestroinscripcion->setEspecialidadTipo($em->getRepository('SieAppWebBundle:EspecialidadMaestroTipo')->findOneById(0));
                $maestroinscripcion->setEstadomaestro($em->getRepository('SieAppWebBundle:EstadomaestroTipo')->findOneById(1));
                $maestroinscripcion->setEstudiaiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById('48'));
                $maestroinscripcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById('0'));
                $maestroinscripcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById('0'));
                $maestroinscripcion->setFormaciondescripcion('');
                $maestroinscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion_g));
                $maestroinscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie));
                $maestroinscripcion->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(5));
                $maestroinscripcion->setPersona($em->getRepository('SieAppWebBundle:Persona')->findOneById($persona));
                //$maestroinscripcion->setPersona($persona);
                $maestroinscripcion->setRdaPlanillasId(0);
                $maestroinscripcion->setRef(0);
                $maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($institucioneducativa_sucursal_id));
                $em->persist($maestroinscripcion);
                $em->flush(); 
                ///////$id_maestroinscripcion=$maestroinscripcion->getId();
            }
            
            $product = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneById($curso_id);
            $product->setFechaInicio(\DateTime::createFromFormat('d/m/Y', $fecha_inicio));
            $product->setFechaFin(\DateTime::createFromFormat('d/m/Y', $fecha_fin));
            $product->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion_g));
            $product->setMaestroInscripcionAsesor($maestroinscripcion);
            $em->flush();
        }
/////////////////////////////////////
        
        if($id!=0){
            $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findOneByinstitucioneducativaCurso($id);
        $plan=$result->getPlancurricularTipoId();
            //VER SU CUMPLE LOS REQUESITIVOS PARA CERRAR EL CURSO
            $curso_ok=0;
            // 1.- QUE LA CANTIDAD DE ESTUDIANTES NO SEAN MENOR Q 8
            $cant_est=0;
            $estudiantes = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findByinstitucioneducativaCurso($id);
            $cant_est=count($estudiantes);//cantidad de estudiantes
            if($cant_est>=4 or $rol=8 or $rol=21)//probar deberia ser 8 CAMBIAR!!!!!!
            // 2.- QUE SI UN ESTUDIANTE TIENE NOTA NO DEBE TONAR NOTA 0, OSEA TODO 0 O NADA DE NOTA 0
            {
                //sacamos a todos los estudiantes_inscripcion_id
                $estudiante_inscripcion_id=array();
                foreach ($estudiantes as $estudiante) {
                    $estudiante_inscripcion_id[]=$estudiante->getId();
                }
                if($plan==1){
                //vemos de cada estudiantes si tiene notas puro 0 o con notas para el plan 1
                    foreach ($estudiante_inscripcion_id as $ei_id) {
                        $query = "
                        select en.nota_cuantitativa
                        from estudiante_asignatura ea, estudiante_nota en
                        where ea.id=en.estudiante_asignatura_id 
                        and ea.estudiante_inscripcion_id='$ei_id'";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $po = $stmt->fetchAll();
                        $nota = array();
                        $datos_filas = array();
                        foreach ($po as $p) {
                            $nota[] = $p["nota_cuantitativa"];
                        }
                        /////ver si las notas son 0
                        //print_r($nota);die;
                        $cant_ceros=0;
                        foreach ($nota as $not) {
                            if($not==0)$cant_ceros++;
                        }
                        //$cant_ceros=substr_count(implode(',', array_values($nota)), 0);
                        $cant_notas=count($nota);
                        //echo $cant_ceros;die;
                        $notas_bien=0;
                        if($cant_ceros==$cant_notas){$notas_bien=1; goto salto;}
                        if($cant_ceros==0){$notas_bien=1; goto salto;}
                    }
                }
                else{//plan 2
                    $notas_bien=1;
                }
                //vemos si todos los estudiantes estan bieno mal
                salto:
                if($notas_bien==1)
                {
                    //Ver la fecha de cerrar no debe ser igual a la fecha fin del curso
                    $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($id);
                        $fecha_fin_curso=$result->getFechaFin();
                        $hoy=date("Y-m-d");
                        $fecha_fin_curso->format("Y-m-d");
                        if ($fecha_fin_curso<$hoy){
                            $this->get('session')->getFlashBag()->add(
                            'error',
                            'Curso no se puede cerrar ya que la fecha final del curso es menor que la de hoy!!!.'
                    ); 
                        }
                        else{
                            
                            if(strlen($userId)<2){
                                $this->get('session')->getFlashBag()->add(
                                'error',
                                'Perdió su sesión por mucho tiempo de inactividad, vuelva a entrar al Sistema!!!.'
                                );  
                            }
                            else{
                                //recoger el plan para que pida el nombre     
                                if($plan == 2){
                                     $query = "SELECT ico.id from institucioneducativa_curso ic
                                        join institucioneducativa_curso_oferta ico on ico.insitucioneducativa_curso_id=ic.id
                                        where ic.id=$id and ico.asignatura_tipo_id=2012
                                        ";
                                        $stmt = $db->prepare($query);
                                        $params = array();
                                        $stmt->execute($params);
                                        $po = $stmt->fetchAll();
                                        
                                        foreach ($po as $p) {
                                            $id_ico = $p["id"];
                                        }
                                    $modulo_emergente=$em->getRepository('SieAppWebBundle:AltModuloemergente')->findOneByInstitucioneducativaCursoOferta($id_ico);
                                    $nom_me=$modulo_emergente->getModuloEmergente();
                                    if($nom_me == "VACIO"){
                                        $this->get('session')->getFlashBag()->add(
                                    'error',
                                    'Colocar Nombre al Área de Formación para la Vida!!.'
                                    );           
                                    }
                                    else{
                                    $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findOneByinstitucioneducativaCurso($id);
                                    $obs_anterior=$result->getObs();
                                    $obsnew = explode("-", $obs_anterior);   
                                    $obsnew= $obsnew[0];
                                    $obs_nuevo=$obsnew."-".$userId;
                                    $result->setObs($obs_nuevo);
                                    $result->setEsactivo(true);
                                    $result->setFechaCerrar(new \DateTime('now'));
                                    $em->flush();    
                                    }
                                }
                                else{
                                    $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findOneByinstitucioneducativaCurso($id);
                                    $obs_anterior=$result->getObs();
                                    $obsnew = explode("-", $obs_anterior);   
                                    $obsnew= $obsnew[0];
                                    $obs_nuevo=$obsnew."-".$userId;
                                    $result->setObs($obs_nuevo);
                                    $result->setEsactivo(true);
                                    $result->setFechaCerrar(new \DateTime('now'));
                                    $em->flush();
                                }
                            }
                        }
                }  
                else
                {
                    //echo "notas mal";die;
                    $this->get('session')->getFlashBag()->add(
                    'error',
                    'Curso no se puede cerrar ya que existe estudiantes con calificaciones junto a calificaciones nulas.'
                    ); 
                }
            } 
            else
            {
                //echo "pocos estudiantes";die;//Estudiantes menores a 8
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'Curso no se puede cerrar ya que existe pocos estudiantes!!!.'
                    ); 
            } 
        }

        $query = "
               SELECT lt.lugar as lugar
               FROM lugar_tipo lt,
               usuario_rol ur 
               WHERE ur.lugar_tipo_id=lt.id and ur.esactivo=true and ur.usuario_id=$userId";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $lugar_usuario = $p["lugar"];
        }
        $lugar_usuario=strtoupper($lugar_usuario);
       
        switch ($lugar_usuario) {
            case 'CHUQUISACA': $id=80480300; break;
            case 'LA PAZ': $id=80730794; break;
            case 'COCHABAMBA': $id=80980569; break;
            case 'ORURO': $id=81230297; break;
            case 'POTOSI': $id=81480201; break;
            case 'TARIJA': $id=81730264; break;
            case 'SANTA CRUZ': $id=81981501; break;
            case 'BENI': $id=82230130; break;
            case 'PANDO': $id=82480050; break;
            default: $id=0; break;
        }
        //dump($id);die;

        ////////CONOCER EL NOMBRE DE USUARIO Y EL ROL SI ES PEDAGOGO O CONSOLIDADOR
        $usu=$em->getRepository('SieAppWebBundle:Usuario')->findOneById($userId);
        $usuario_idd=$usu->getId();
        $username = $usu->getUsername();        
        $roluser = $this->session->get('roluser');




        if($id==0)$consulta="";else $consulta="AND institucioneducativa_curso.institucioneducativa_id = $id";
        //print_r($lugar_usuario); die;      
        //LISTA DE TOTALES POR GESTION DEPTO PARTE Y BLOQUE
                $query = "
               SELECT carnet, complemento,nombre,paterno,materno,fecha_inicio,fecha_fin,ciclo_tipo_id,grado_tipo_id,id,depto,provincia,lugar,localidad,obs,esactivo,count(*) as est,SUM(CASE WHEN estadomatricula_tipo_id=62
            THEN 1
            ELSE 0
    END) as est_aprob,nciclo,ngrado,plan
from (
select persona.carnet,persona.complemento, persona.nombre, persona.paterno, persona.materno,
institucioneducativa_curso.fecha_inicio, institucioneducativa_curso.fecha_fin,
institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id,
institucioneducativa_curso.id,ct.ciclo as nciclo,gt.grado as ngrado,iecd.plancurricular_tipo_id as plan,

CASE
        WHEN institucioneducativa_curso.institucioneducativa_id = 80480300 THEN
                'CHUQUISACA'
        WHEN institucioneducativa_curso.institucioneducativa_id = 80730794 THEN
                'LA PAZ'
        WHEN institucioneducativa_curso.institucioneducativa_id = 80980569 THEN
                'COCHABAMBA'
        WHEN institucioneducativa_curso.institucioneducativa_id = 81230297 THEN
                'ORURO'
        WHEN institucioneducativa_curso.institucioneducativa_id = 81480201 THEN
                'POTOSI'
        WHEN institucioneducativa_curso.institucioneducativa_id = 81730264 THEN
                'TARIJA'
        WHEN institucioneducativa_curso.institucioneducativa_id = 81981501 THEN
                'SANTA CRUZ'
        WHEN institucioneducativa_curso.institucioneducativa_id = 82230130 THEN
                'BENI'
        WHEN institucioneducativa_curso.institucioneducativa_id = 82480050 THEN
                'PANDO'                         
    END AS depto,
institucioneducativa_curso.lugar as provincia,llt.lugar,iecd.localidad,iecd.obs,iecd.esactivo,ei.estadomatricula_tipo_id

from institucioneducativa_curso 
inner join maestro_inscripcion 
on institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion .id
inner join persona 
on maestro_inscripcion .persona_id = persona.id
inner join institucioneducativa_curso_datos as iecd
on iecd.institucioneducativa_curso_id=institucioneducativa_curso.id
inner join lugar_tipo as llt
on llt.id=iecd.lugar_tipo_id_seccion
left join estudiante_inscripcion ei
on ei.institucioneducativa_curso_id=institucioneducativa_curso.id
inner join ciclo_tipo ct ON institucioneducativa_curso.ciclo_tipo_id=ct.id
inner join grado_tipo gt ON institucioneducativa_curso.grado_tipo_id=gt.id
where 
date_part('year', institucioneducativa_curso.fecha_fin) = $gestion $consulta

) as t1
GROUP BY carnet, complemento,nombre,paterno,materno,fecha_inicio,fecha_fin,ciclo_tipo_id,grado_tipo_id,id,depto,provincia,lugar,localidad,obs,esactivo,nciclo,ngrado,plan
order by                 
fecha_inicio,
ciclo_tipo_id, grado_tipo_id 
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        /////vemos el rol y si es 29 pedagogo solo puede ver sus cursos creados, si es 8 jefe informatico no pasa nada y si es 21 consolidador tampoco
        if($roluser==29)
        {
            foreach ($po as $p) {
            //////de obs ver quien creo el curso y quien lo cerro
            $cadena = $p["obs"];
            $opciones = explode("-", $cadena);
            $usu=$em->getRepository('SieAppWebBundle:Usuario')->findOneById(intval($opciones[0]));
            $username_a = $usu->getUsername();
            if($username_a == $username){
                $datos_filas["usu_a"] = $usu->getUsername();
                $datos_filas["plan"] = $p["plan"];
                $datos_filas["carnet"] = $p["carnet"];
                $datos_filas["complemento"] = $p["complemento"];
                $datos_filas["nombre"] = $p["nombre"];
                $datos_filas["paterno"] = $p["paterno"];
                $datos_filas["materno"] = $p["materno"];
                $datos_filas["fecha_inicio"] = $p["fecha_inicio"];
                $datos_filas["fecha_fin"] = $p["fecha_fin"];
                $datos_filas["nciclo"] = $p["nciclo"];
                $datos_filas["ngrado"] = $p["ngrado"];
                if ($p["ngrado"]=="Primero")$datos_filas["ngrado"]=1;
                if ($p["ngrado"]=="Segundo")$datos_filas["ngrado"]=2;
                $datos_filas["ciclo_tipo_id"] = $p["ciclo_tipo_id"];
                $datos_filas["grado_tipo_id"] = $p["grado_tipo_id"];
                $datos_filas["id"] = $p["id"];
                $datos_filas["depto"] = $p["depto"];
                $datos_filas["provincia"] = $p["provincia"];
                $datos_filas["lugar"] = $p["lugar"];
                $datos_filas["localidad"] = $p["localidad"];
                if(isset($opciones[1])){
                    $usu=$em->getRepository('SieAppWebBundle:Usuario')->findOneById(intval($opciones[1]));
                    $datos_filas["usu_c"] = $usu->getUsername();
                }
                else
                    $datos_filas["usu_c"] = 0;

                $datos_filas["esactivo"]=$p["esactivo"];
                $datos_filas["est"]=$p["est"];
                $datos_filas["est_aprob"]=$p["est_aprob"];
                $filas[] = $datos_filas;
                }
            }
        }
        else
        {
            foreach ($po as $p) {
            $datos_filas["plan"] = $p["plan"];
            $datos_filas["carnet"] = $p["carnet"];
            $datos_filas["complemento"] = $p["complemento"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["fecha_inicio"] = $p["fecha_inicio"];
            $datos_filas["fecha_fin"] = $p["fecha_fin"];
            $datos_filas["ciclo_tipo_id"] = $p["ciclo_tipo_id"];
            $datos_filas["grado_tipo_id"] = $p["grado_tipo_id"];
            $datos_filas["nciclo"] = $p["nciclo"];
            $datos_filas["ngrado"] = $p["ngrado"];
            if ($p["ngrado"]=="Primero")$datos_filas["ngrado"]=1;
            if ($p["ngrado"]=="Segundo")$datos_filas["ngrado"]=2;
            $datos_filas["id"] = $p["id"];
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["provincia"] = $p["provincia"];
            $datos_filas["lugar"] = $p["lugar"];
            $datos_filas["localidad"] = $p["localidad"];
            //////de obs ver quien creo el curso y quien lo cerro
            $cadena = $p["obs"];
            $opciones = explode("-", $cadena);
            
            $usu=$em->getRepository('SieAppWebBundle:Usuario')->findOneById(intval($opciones[0]));
            $datos_filas["usu_a"] = $usu->getUsername();

            if(isset($opciones[1])){
                $usu=$em->getRepository('SieAppWebBundle:Usuario')->findOneById(intval($opciones[1]));
                $datos_filas["usu_c"] = $usu->getUsername();
            }
            else
                $datos_filas["usu_c"] = 0;

            $datos_filas["esactivo"]=$p["esactivo"];
            $datos_filas["est"]=$p["est"];
            $datos_filas["est_aprob"]=$p["est_aprob"];
            $filas[] = $datos_filas;
            }
        }
            ///////////////// SI GESTION ES 0 ENTONCES HACEMOS EL REPORTE
        $reporte_c=array();
        if ($gestion==0){
            /////VEMOS EL ROL Y DEPENDIENDO HACEMOS EL QUERY///
            $q1=$q1w="";
            ///SI ES INFORMATICO DEPARTAMNTEA
            if ($roluser==21){$q1="ic.institucioneducativa_id,";$q1w="where ic.institucioneducativa_id=$id";}
            if ($roluser==29){$q1="ic.institucioneducativa_id,split_part(icd.obs, '-', 1),";$q1w="where ic.institucioneducativa_id=$id and split_part(icd.obs, '-', 1)='$usuario_idd'";}

            $query = "
            select $q1 extract(year from fecha_fin) as gestion,icd.esactivo,COUNT(*) as cantidad
            from institucioneducativa_curso_datos icd
            join institucioneducativa_curso ic on icd.institucioneducativa_curso_id=ic.id
            join lugar_tipo lt1 on lt1.id=icd.lugar_tipo_id_seccion
            $q1w
            group by $q1 extract(year from fecha_fin),icd.esactivo
            order by gestion,icd.esactivo
                ";
            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $po = $stmt->fetchAll();
            
            $datos_filas = array();
            $reporte=array();
            
            $cerrado=0;$abierto=0;$total_c=0;$abierto_t=0;$cerrado_t=0;$total_t=0;
            
            for($gg=$gestion_ini_t;$gg<=$gestion_fin_t;$gg++){
                foreach ($po as $p) {
                    if($gg==$p["gestion"]){
                        if($p["esactivo"]==1) $cerrado=$p["cantidad"]; else $abierto=$p["cantidad"];
                        $total_c=$cerrado+$abierto;
                    }
                }
                $reporte["gestion"]=$gg;
                $reporte["abierto"]=$abierto;
                $abierto_t=$abierto_t+$abierto;
                $reporte["cerrado"]=$cerrado;
                $cerrado_t=$cerrado_t+$cerrado;
                $reporte["total_c"]=$total_c;
                $total_t=$total_t+$total_c;
                if($total_c!=0)
                    $reporte_c[]=$reporte;
                $cerrado=0;$abierto=0;$total_c=0;
            }
            $reporte["gestion"]="Total";
            $reporte["abierto"]=$abierto_t;
            $reporte["cerrado"]=$cerrado_t;
            $reporte["total_c"]=$total_t;
            $reporte_c[]=$reporte;
        }
       
          
        return $this->render('SiePnpBundle:Default:listararchivos_editnew.html.twig', array(
            'totales' => $filas,
            'reporte_c'=>$reporte_c,
            'idd'=>$id,
            'gestion_ini_t'=>$gestion_ini_t,
            'gestion_fin_t'=>$gestion_fin_t,
            'gestion'=> $gestion,
            'roluser'=>$roluser));
    }


    public function cursolistar_editAction($id,Request $request)
    {
        if($request->getMethod()=="POST") {
            $idd=$request->get("idd");
            $nota=$request->get("nota");    
            $estado=$request->get("estado");
            $idinscripcion=$request->get("idinscripcion");
            $cant = count($idd);
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                for($i=0;$i<$cant;$i++){
                    $product = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneById($idd[$i]);
                    $product->setNotaCuantitativa($nota[$i]);
                    //$product->setNotaCuantitativa(0);
                    $em->flush();
                } 
                    $estado = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById($estado);
                    $result=$em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($idinscripcion);
                    $result->setEstadomatriculaTipo($estado);
                    $em->flush();
                    $em->getConnection()->commit();
            } catch (Exception $e) {
                $em->getConnection()->rollBack();
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Existio un problema al Editar las notas.'
                    );      
                throw $e;
            }
        }
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        //LISTA DE ESTUDIANTES
        //LISTA DE ESTUDIANTES
        $query = "SELECT 
                      estudiante.id as estudiante_id,
                      estudiante.codigo_rude, 
                      estudiante.carnet_identidad, 
                      estudiante.paterno, 
                      estudiante.materno, 
                      estudiante.nombre, 
                      estudiante.fecha_nacimiento, 
                      estudiante.genero_tipo_id,
                      genero_tipo.genero,
                      estudiante.observacionadicional,
                      estudiante_inscripcion.estadomatricula_tipo_id as matricula_estado_id,
                      estadomatricula_tipo.estadomatricula,
                      estudiante_inscripcion.id as inscripcion_id
                    FROM 
                      estudiante INNER JOIN estudiante_inscripcion ON estudiante.id = estudiante_inscripcion.estudiante_id
                      INNER JOIN genero_tipo ON genero_tipo.id = estudiante.genero_tipo_id
                      INNER JOIN estadomatricula_tipo ON estadomatricula_tipo.ID = estudiante_inscripcion.estadomatricula_inicio_tipo_id
                      INNER JOIN institucioneducativa_curso ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
                    WHERE
                      institucioneducativa_curso.id = ".$id;
        
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["codigo_rude"] = $p["codigo_rude"];
            $datos_filas["carnet_identidad"] = $p["carnet_identidad"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["fecha_nacimiento"] = $p["fecha_nacimiento"];
            $datos_filas["genero"] = $p["genero"];
            $datos_filas["observacionadicional"] = $p["observacionadicional"];
            $datos_filas["estadomatricula"] = $p["estadomatricula"];
            $datos_filas["inscripcion_id"] = $p["inscripcion_id"];
            $filas[] = $datos_filas;
        }
        
        //DATOS GENERALES DEL CURSO
        //DATOS GENERALES DEL CURSO
        $query = "SELECT
              institucioneducativa_curso.id, 
                      institucioneducativa_curso.institucioneducativa_id, 
                      institucioneducativa_curso.gestion_tipo_id, 
                      institucioneducativa_curso.nivel_tipo_id, 
                      institucioneducativa_curso.ciclo_tipo_id, 
                      institucioneducativa_curso.grado_tipo_id,
                      institucioneducativa_curso.nro_materias,
                      institucioneducativa_curso.fecha_fin, 
                      institucioneducativa_curso.fecha_inicio,
                      institucioneducativa_curso.lugartipo_id as departamento_id,
                      lugar_tipo.lugar as departamento,
                      institucioneducativa_curso.lugar as provincia,
                      estudiante_inscripcion.lugar as municipio, 
                      estudiante_inscripcion.lugarcurso as localidad,
                      institucioneducativa_curso.facilitador,
                      persona.nombre,
                      persona.paterno,
                      persona.materno,
                      persona.carnet
                    FROM 
                      institucioneducativa_curso INNER JOIN estudiante_inscripcion ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
                      INNER JOIN lugar_tipo ON lugar_tipo.id = institucioneducativa_curso.lugartipo_id
                      INNER JOIN maestro_inscripcion ON institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion.id
                      INNER JOIN persona ON maestro_inscripcion.persona_id = persona.id
                    WHERE
                      institucioneducativa_curso.id = ".$id."

                   GROUP BY
                      institucioneducativa_curso.id,
                      institucioneducativa_curso.institucioneducativa_id, 
                      institucioneducativa_curso.gestion_tipo_id, 
                      institucioneducativa_curso.ciclo_tipo_id, 
                      institucioneducativa_curso.grado_tipo_id,
                      institucioneducativa_curso.nro_materias,
                      institucioneducativa_curso.fecha_fin, 
                      institucioneducativa_curso.fecha_inicio,
                      institucioneducativa_curso.lugartipo_id,
                      lugar_tipo.lugar,
                      institucioneducativa_curso.lugar,
                      estudiante_inscripcion.lugar, 
                      estudiante_inscripcion.lugarcurso,
                      institucioneducativa_curso.facilitador,
                      persona.nombre,
                      persona.paterno,
                      persona.materno,
                      persona.carnet";
        
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        
        $filasdos = array();
        $datos_filasdos = array();
        foreach ($po as $p) {
            $datos_filasdos["id"] = $p["id"];
            $datos_filasdos["institucioneducativa_id"] = $p["institucioneducativa_id"];
            $datos_filasdos["gestion_tipo_id"] = $p["gestion_tipo_id"];
            $datos_filasdos["ciclo_tipo_id"] = $p["ciclo_tipo_id"];
            $datos_filasdos["grado_tipo_id"] = $p["grado_tipo_id"];
            $datos_filasdos["nro_materias"] = $p["nro_materias"];
            $datos_filasdos["fecha_fin"] = $p["fecha_fin"];
            $datos_filasdos["fecha_inicio"] = $p["fecha_inicio"];
            $datos_filasdos["departamento"] = $p["departamento"];
            $datos_filasdos["provincia"] = $p["provincia"];
            $datos_filasdos["municipio"] = $p["municipio"];
            $datos_filasdos["localidad"] = $p["localidad"];
            $datos_filasdos["facilitador"] = $p["facilitador"];
            $datos_filasdos["nombre"] = $p["nombre"];
            $datos_filasdos["paterno"] = $p["paterno"];
            $datos_filasdos["materno"] = $p["materno"];
            $datos_filasdos["carnet"] = $p["carnet"];
            $filasdos[] = $datos_filasdos;
        }
        
        $this->session->getFlashBag()->add('success', 'Proceso realizado exitosamente.');
        return $this->render('SiePnpBundle:Default:cursolista_edit.html.twig', array('estudiantes' => $filas, 'datosentity' => $filasdos));
    }
        ////listado del nuevo listar edit para la gestion 2016 adelante
    public function cursolistar_editnewAction($id,$val,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $userId = $this->session->get('userId');

        
        ////////cerrar curso
        if ($val==6){
            $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findOneByinstitucioneducativaCurso($id);
        $plan=$result->getPlancurricularTipoId();
             //VER SU CUMPLE LOS REQUESITIVOS PARA CERRAR EL CURSO
            $curso_ok=0;
            // 1.- QUE LA CANTIDAD DE ESTUDIANTES NO SEAN MENOR Q 8
            $cant_est=0;
            $estudiantes = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findByinstitucioneducativaCurso($id);
            $cant_est=count($estudiantes);//cantidad de estudiantes
            if($cant_est>=4 or $rol=8 or $rol=21)//probar deberia ser 8 CAMBIAR!!!!!!
            // 2.- QUE SI UN ESTUDIANTE TIENE NOTA NO DEBE TONAR NOTA 0, OSEA TODO 0 O NADA DE NOTA 0
            {
                //sacamos a todos los estudiantes_inscripcion_id
                $estudiante_inscripcion_id=array();
                foreach ($estudiantes as $estudiante) {
                    $estudiante_inscripcion_id[]=$estudiante->getId();
                }
                //vemos de cada estudiantes si tiene notas puro 0 o con notas
                if($plan==1){
                    foreach ($estudiante_inscripcion_id as $ei_id) {
                        $query = "
                        select en.nota_cuantitativa
                        from estudiante_asignatura ea, estudiante_nota en
                        where ea.id=en.estudiante_asignatura_id 
                        and ea.estudiante_inscripcion_id='$ei_id'";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $po = $stmt->fetchAll();
                        $nota = array();
                        $datos_filas = array();
                        foreach ($po as $p) {
                            $nota[] = $p["nota_cuantitativa"];
                        }
                        /////ver si las notas son 0
                        //print_r($nota);die;
                        $cant_ceros=0;
                        foreach ($nota as $not) {
                            if($not==0)$cant_ceros++;
                        }
                        //$cant_ceros=substr_count(implode(',', array_values($nota)), 0);
                        $cant_notas=count($nota);
                        //echo $cant_ceros;die;
                        $notas_bien=0;
                        if($cant_ceros==$cant_notas){$notas_bien=1; goto salto;}
                        if($cant_ceros==0){$notas_bien=1; goto salto;}
                    }
                }
                else {//plan 2
                   $notas_bien=1;
                }
                //vemos si todos los estudiantes estan bieno mal
                salto:
                if($notas_bien==1)
                {
                    //Ver la fecha de cerrar no debe ser igual a la fecha fin del curso
                    $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($id);
                        $fecha_fin_curso=$result->getFechaFin();
                        $hoy=date("Y-m-d");
                        $fecha_fin_curso->format("Y-m-d");
                        if ($fecha_fin_curso<$hoy){
                            $this->get('session')->getFlashBag()->add(
                            'error',
                            'Curso no se puede cerrar ya que la fecha final del curso es menor que la de hoy!!!.'
                    ); 
                        }
                        else{
                            
                            if(strlen($userId)<2){
                                $this->get('session')->getFlashBag()->add(
                                'error',
                                'Perdió su sesión por mucho tiempo de inactividad, vuelva a entrar al Sistema!!!.'
                                );  
                            }
                            else{
                                if($plan == 2){
                                     $query = "SELECT ico.id from institucioneducativa_curso ic
                                        join institucioneducativa_curso_oferta ico on ico.insitucioneducativa_curso_id=ic.id
                                        where ic.id=$id and ico.asignatura_tipo_id=2012
                                        ";
                                        $stmt = $db->prepare($query);
                                        $params = array();
                                        $stmt->execute($params);
                                        $po = $stmt->fetchAll();
                                        
                                        foreach ($po as $p) {
                                            $id_ico = $p["id"];
                                        }
                                    $modulo_emergente=$em->getRepository('SieAppWebBundle:AltModuloemergente')->findOneByInstitucioneducativaCursoOferta($id_ico);
                                    $nom_me=$modulo_emergente->getModuloEmergente();
                                    if($nom_me == "VACIO"){
                                        $this->get('session')->getFlashBag()->add(
                                    'error',
                                    'Colocar Nombre al Módulo Emergente!!!.'
                                    );           
                                    }
                                    else{
                                    $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findOneByinstitucioneducativaCurso($id);
                                    $obs_anterior=$result->getObs();
                                    $obsnew = explode("-", $obs_anterior);   
                                    $obsnew= $obsnew[0];
                                    $obs_nuevo=$obsnew."-".$userId;
                                    $result->setObs($obs_nuevo);
                                    $result->setEsactivo(true);
                                    $result->setFechaCerrar(new \DateTime('now'));
                                    $em->flush();    
                                    }
                                }
                                else{
                                    $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findOneByinstitucioneducativaCurso($id);
                                    $obs_anterior=$result->getObs();
                                    $obsnew = explode("-", $obs_anterior);   
                                    $obsnew= $obsnew[0];
                                    $obs_nuevo=$obsnew."-".$userId;
                                    $result->setObs($obs_nuevo);
                                    $result->setEsactivo(true);
                                    $result->setFechaCerrar(new \DateTime('now'));
                                    $em->flush();
                                }
                            }
                        }
                }  
                else
                {
                    //echo "notas mal";die;
                    $this->get('session')->getFlashBag()->add(
                    'error',
                    'Curso no se puede cerrar ya que existe estudiantes con calificaciones junto a calificaciones nulas.'
                    ); 
                }
            } 
            else
            {
                //echo "pocos estudiantes";die;//Estudiantes menores a 8
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'Curso no se puede cerrar ya que existe pocos estudiantes!!!.'
                    ); 
            } 
        }

        if($request->getMethod()=="POST") {
            ///modulo emergente
            if ($val==5){
                $modulo_emergente_id=$request->get("modulo_emergente_id");
                $modulo_emergente=$request->get("mmodulo_emergente"); 
                $modulo_emergente_horas=$request->get("modulo_emergente_horas");
                $rcurso_id=$request->get("rcurso_id");
                $product=$em->getRepository('SieAppWebBundle:AltModuloemergente')->findOneById($modulo_emergente_id);
                $product->setModuloEmergente($modulo_emergente);
                $product->setFechaModificacion(new \DateTime('now'));
                $usuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneById($this->session->get('userId'));
                $product->setUsuario($usuario);
                $em->flush();

                $product=$em->getRepository('SieAppWebBundle:institucionEducativaCurso')->findOneById($rcurso_id);
                $product->setDuracionhoras($modulo_emergente_horas);
                $em->flush();
            }
            if ($val == 1) { 
                $estudiante_id=$request->get("estudiante_id");
                $fecha_nac=$request->get("fecha_nac"); 
                $genero=$request->get("genero");
                $discapacidad=$request->get("discapacidad");
                $plan=$request->get("plan");
                
                
                if($genero != 1 and $genero != 2) $genero=3;
                
                $alfabetizado=$request->get("alfabetizado"); 
                $idioma=$request->get("idioma");
                $ocupacion=$request->get("ocupacion");
                $observacionadicional_n=$alfabetizado."|".$idioma."|".$ocupacion;
                
                $em = $this->getDoctrine()->getManager();
                $product = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($estudiante_id);
                $product->setFechaNacimiento(\DateTime::createFromFormat('d/m/Y', $fecha_nac));
                $genero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($genero);
                $product->setGeneroTipo($genero);
                $product->setObservacionadicional($observacionadicional_n);
                $em->flush();

                if($plan==2){
                    //obtener estudiante_inscripcion_id
                     $estudiante_inscripcion_id = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy([
                        'institucioneducativaCurso' => $id,
                        'estudiante' => $estudiante_id,
                    ]);
                     $estudiante_inscripcion_id=$estudiante_inscripcion_id->getId();

                    $estinscsocalt = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAlternativa')->findOneByestudianteInscripcion($estudiante_inscripcion_id);
                    $estinscsocalt->setSeccionivDiscapacitadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->findOneById($discapacidad));
                    $estinscsocalt->setSeccionvOtroTrabajo($ocupacion);
                    $estinscsocalt->setFechaModificacion(new \DateTime('now'));
                    $em->flush();
                }
            }
            elseif($val == 2){
                echo "entro";die;
            }
            elseif ($val==0) {
                $plan=$request->get("plan");
                $idd=$request->get("idd");
                $nota=$request->get("nota");    
                $estado=$request->get("estado");
                $idinscripcion=$request->get("idinscripcion");
                $cant = count($idd);
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                try {
                    for($i=0;$i<$cant;$i++){
                        $product = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneById($idd[$i]);
                        $product->setNotaCuantitativa($nota[$i]);
                        //$product->setNotaCuantitativa(0);
                        $em->flush();
                    } 
                        $estado = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById($estado);
                        $result=$em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($idinscripcion);
                        $result->setEstadomatriculaTipo($estado);
                        $em->flush();
                        ///plan 2 guardar nota cualitativa
                        if($plan==2){
                            $nota_cualitativa=$request->get("nota_cualitativa");
                            $enc=$em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneByestudianteInscripcion($idinscripcion);
                            $enc->setNotaCualitativa($nota_cualitativa);
                            $em->flush();
                        }
                        $this->get('session')->getFlashBag()->add(
                        'success',
                        'Notas Modificadas con exito.'
                        );  
                        $em->getConnection()->commit();
                }
                catch (Exception $e) {
                    $em->getConnection()->rollBack();
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Existio un problema al Editar las notas.'
                        );      
                    throw $e;
                }
            } 
        }

        //CONOCER SI EL CURSO ESTA CERRADO 1 Cerrado 0 Abierto
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findOneByinstitucioneducativaCurso($id);
            $esactivo=$result->getEsactivo();

        if($esactivo==1)$esactivo=1;else $esactivo=0;

        //LISTA DE ESTUDIANTES
        //LISTA DE ESTUDIANTES
        $query = "SELECT 
                      estudiante.id as estudiante_id,
                      estudiante.codigo_rude, 
                      estudiante.carnet_identidad, 
                      estudiante.complemento,
                      estudiante.paterno, 
                      estudiante.materno, 
                      estudiante.nombre, 
                      estudiante.fecha_nacimiento, 
                      estudiante.genero_tipo_id,
                      genero_tipo.genero,
                      genero_tipo.id as id_genero,
                      estudiante.observacionadicional,
                      estudiante_inscripcion.estadomatricula_tipo_id as matricula_estado_id,
                      estadomatricula_tipo.estadomatricula,
                      estudiante_inscripcion.id as inscripcion_id,
                      eisa.seccioniv_discapacitad_tipo_id as discapacidad_id,
                      dt.origendiscapacidad as discapacidad_nombre
                    FROM 
                      estudiante INNER JOIN estudiante_inscripcion ON estudiante.id = estudiante_inscripcion.estudiante_id
                      INNER JOIN genero_tipo ON genero_tipo.id = estudiante.genero_tipo_id
                      INNER JOIN estadomatricula_tipo ON estadomatricula_tipo.ID = estudiante_inscripcion.estadomatricula_inicio_tipo_id
                      INNER JOIN institucioneducativa_curso ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
                      LEFT JOIN estudiante_inscripcion_socioeconomico_alternativa eisa on eisa.estudiante_inscripcion_id=estudiante_inscripcion.id
                      LEFT JOIN discapacidad_tipo dt on dt.id=eisa.seccioniv_discapacitad_tipo_id
                    WHERE
                      institucioneducativa_curso.id = ".$id;
        
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["fecha_nacimiento"] = $p["fecha_nacimiento"];
            //////////VER SI LA FECHA ESTA ERRORNEA 01/01/2008 al 01/01/2015

            $fecha_nac= $p["fecha_nacimiento"];
            list($g_n,$m_n,$d_n) = explode("-", $fecha_nac);
            if($d_n==1 and $m_n==1 and $g_n >= 2008 and $g_n <= 2015)
                $datos_filas["fecha_b"]=0; else $datos_filas["fecha_b"]=1;
            $datos_filas["codigo_rude"] = $p["codigo_rude"];
            switch (substr($p["codigo_rude"], 0, 8)) {
                case '80480300': $datos_filas["pnp"]=1;break; 
                case '80730794': $datos_filas["pnp"]=1;break;
                case '80980569': $datos_filas["pnp"]=1;break;
                case '81230297': $datos_filas["pnp"]=1;break;
                case '81480201': $datos_filas["pnp"]=1;break;
                case '81730264': $datos_filas["pnp"]=1;break;
                case '81981501': $datos_filas["pnp"]=1;break;
                case '82230130': $datos_filas["pnp"]=1;break;
                case '82480050': $datos_filas["pnp"]=1;break;
                default: $datos_filas["pnp"]=0;break;
            }
            $datos_filas["carnet_identidad"] = $p["carnet_identidad"];
            $datos_filas["complemento"] = $p["complemento"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["genero"] = $p["genero"];
            $datos_filas["id_genero"] = $p["id_genero"];
            $datos_filas["observacionadicional"] = $p["observacionadicional"];
            $datos_filas["estadomatricula"] = $p["matricula_estado_id"];
            $datos_filas["inscripcion_id"] = $p["inscripcion_id"];
            $datos_filas["estudiante_id"] = $p["estudiante_id"];
            if ($p["discapacidad_id"]==""){
              $datos_filas["discapacidad_id"] =0;  
              $datos_filas["discapacidad_nombre"] = "No";
            }
            else{
                $datos_filas["discapacidad_id"] = $p["discapacidad_id"];
                $datos_filas["discapacidad_nombre"] = $p["discapacidad_nombre"];
            }
            $filas[] = $datos_filas;
        }
        
        //DATOS GENERALES DEL CURSO
        //DATOS GENERALES DEL CURSO
        $query = "SELECT
              institucioneducativa_curso.id, 
                      institucioneducativa_curso.institucioneducativa_id, 
                      institucioneducativa_curso.gestion_tipo_id, 
                      institucioneducativa_curso.nivel_tipo_id, 
                      institucioneducativa_curso.ciclo_tipo_id, 
                      institucioneducativa_curso.grado_tipo_id,
                      institucioneducativa_curso.nro_materias,
                      institucioneducativa_curso.fecha_fin, 
                      institucioneducativa_curso.fecha_inicio,
                      institucioneducativa_curso.lugartipo_id as departamento_id,
                      lugar_tipo.lugar as departamento,
                      institucioneducativa_curso.lugar as provincia,
                      lt.lugar as municipio,
                      icd.localidad as localidad,
                      institucioneducativa_curso.facilitador,
                      persona.nombre,
                      persona.paterno,
                      persona.materno,
                      persona.carnet,
                      persona.complemento,
                      ct.ciclo as nciclo,
                      gt.grado as ngrado,
                      icd.plancurricular_tipo_id,
                      institucioneducativa_curso.duracionhoras
                    FROM 
                      institucioneducativa_curso 
                      INNER JOIN institucioneducativa_curso_datos as icd ON icd.institucioneducativa_curso_id=institucioneducativa_curso.id
                      INNER JOIN lugar_tipo as lt ON lt.id = icd.lugar_tipo_id_seccion 
                      INNER JOIN lugar_tipo ON lugar_tipo.id = institucioneducativa_curso.lugartipo_id
                      INNER JOIN maestro_inscripcion ON institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion.id
                      INNER JOIN persona ON maestro_inscripcion.persona_id = persona.id
                      INNER JOIN ciclo_tipo ct ON institucioneducativa_curso.ciclo_tipo_id=ct.id
                      INNER JOIN grado_tipo gt ON institucioneducativa_curso.grado_tipo_id=gt.id
                    WHERE
                      institucioneducativa_curso.id = ".$id."

                   GROUP BY
                      institucioneducativa_curso.id,
                      institucioneducativa_curso.institucioneducativa_id, 
                      institucioneducativa_curso.gestion_tipo_id, 
                      institucioneducativa_curso.ciclo_tipo_id, 
                      institucioneducativa_curso.grado_tipo_id,
                      institucioneducativa_curso.nro_materias,
                      institucioneducativa_curso.fecha_fin, 
                      institucioneducativa_curso.fecha_inicio,
                      institucioneducativa_curso.lugartipo_id,
                      lugar_tipo.lugar,
                      institucioneducativa_curso.lugar,
                      lt.lugar,
                      icd.localidad,
                      institucioneducativa_curso.facilitador,
                      persona.nombre,
                      persona.paterno,
                      persona.materno,
                      persona.carnet,
                      persona.complemento,
                      ct.ciclo,
                      gt.grado,
                      icd.plancurricular_tipo_id";
        
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        
        $filasdos = array();
        $datos_filasdos = array();
        foreach ($po as $p) {
            $datos_filasdos["id"] = $p["id"];
            $id_archivo=$p["id"];
            $datos_filasdos["institucioneducativa_id"] = $p["institucioneducativa_id"];
            $datos_filasdos["gestion_tipo_id"] = $p["gestion_tipo_id"];
            $datos_filasdos["ciclo_tipo_id"] = $p["ciclo_tipo_id"];
            $datos_filasdos["grado_tipo_id"] = $p["grado_tipo_id"];
            $datos_filasdos["nciclo"] = $p["nciclo"];
            $datos_filasdos["ngrado"] = $p["ngrado"];
            if ($p["ngrado"]=="Primero")$datos_filasdos["ngrado"]=1;
            if ($p["ngrado"]=="Segundo")$datos_filasdos["ngrado"]=2;
            $datos_filasdos["nro_materias"] = $p["nro_materias"];
            $datos_filasdos["fecha_fin"] = $p["fecha_fin"];
            $datos_filasdos["fecha_inicio"] = $p["fecha_inicio"];
            $datos_filasdos["departamento"] = $p["departamento"];
            $datos_filasdos["provincia"] = $p["provincia"];
            $datos_filasdos["municipio"] = $p["municipio"];
            $datos_filasdos["localidad"] = $p["localidad"];
            $datos_filasdos["facilitador"] = $p["facilitador"];
            $datos_filasdos["nombre"] = $p["nombre"];
            $datos_filasdos["paterno"] = $p["paterno"];
            $datos_filasdos["materno"] = $p["materno"];
            $datos_filasdos["carnet"] = $p["carnet"];
            $datos_filasdos["complemento"] = $p["complemento"];
            $plan=$p["plancurricular_tipo_id"];
            $duracionhoras = $p["duracionhoras"];
            $filasdos[] = $datos_filasdos;
        }

        // id institucion educativa curso oferta
        $query = "SELECT ico.id from institucioneducativa_curso ic
        join institucioneducativa_curso_oferta ico on ico.insitucioneducativa_curso_id=ic.id
        where ic.id=$id and ico.asignatura_tipo_id=2012
        ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        
        foreach ($po as $p) {
            $id_ico = $p["id"];
        }

        $discapacidades= $this->getDoctrine()->getRepository('SieAppWebBundle:DiscapacidadTipo')->findAll();
        ///sacamos el modulo emergente
        $modulo_emergente=$em->getRepository('SieAppWebBundle:AltModuloemergente')->findOneByInstitucioneducativaCursoOferta($id_ico);

        if(!$modulo_emergente){
            $modulo_emergente = array(
                "id" => 0,
                "moduloEmergente" => "VACIO",
            );
        }
        //$this->session->getFlashBag()->add('success', 'Proceso realizado exitosamente.');
        return $this->render('SiePnpBundle:Default:cursolista_editnew.html.twig', array('estudiantes' => $filas, 'plan'=>$plan,'datosentity' => $filasdos,'esactivo'=>$esactivo,'id_archivo'=>$id_archivo,'discapacidades'=>$discapacidades,'modulo_emergente'=>$modulo_emergente,'duracionhoras'=>$duracionhoras));
    }
    

    public function buscarestudianteAction($ci,$curso_id,$complemento,$rude)
    {
        $opcion = substr($ci, -2);    // devuelve "ef" //
        $ci = substr($ci, 0, -2);
        $reconocimiento_saberes=0;//si tiene reconocimiento de saberes 0 no 1 si
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        //////discapacidades obtener
        $discapacidades= $this->getDoctrine()->getRepository('SieAppWebBundle:DiscapacidadTipo')->findAll();
        
        if($rude==0)
            if($complemento=='0'){
                $where="estudiante.carnet_identidad = '$ci' AND (estudiante.complemento = '' OR estudiante.complemento is null)";
            }
            else{
                $where="estudiante.carnet_identidad = '$ci' AND estudiante.complemento = '$complemento'";
            }
        else 
            $where="estudiante.codigo_rude = '$ci'";
        /////////////////INICAL conocer fecha inicial del curso para permitir o no inscribir
        $gestion_ini= $this->getDoctrine()->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($curso_id);
        $gestion_ini=$gestion_ini->getFechaInicio();
        $gestion_ini= date_format($gestion_ini,"Y");
        $usuario_id = $this->session->get('userId');
        $rol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneByUsuario($usuario_id);    
        $rol=$rol->getRolTipo()->getId();
        
        if($gestion_ini<=2015 or $rol==8){////////como es 2009-2015 no debe entrar por los controles o si mi usuario ingrsa
            $po = array();
            $po=$this->retornar_estudianteAction($where);
            $filas = array();
            $datos_filas = array();
            $exxx=0;//si es estudiante
            foreach ($po as $p) {
                $filas['rude'] = $p["codigo_rude"];
                $filas['paterno'] = $p["paterno"];
                $filas['materno'] = $p["materno"];
                $filas['nombre'] = $p["nombre"];
                $filas['fecha_nac'] = $p["fecha_nacimiento"];
                $filas['genero'] = $p["genero"];
                $filas['ci'] = $p["carnet_identidad"];
                $filas['complemento'] = $p["complemento"];
                $obs_adicional=$p["observacionadicional"];
                if ( $obs_adicional != "") {
                    $porciones = explode("|", $obs_adicional);
                    $filas['alfabetizado'] = $porciones[0];
                    $filas['idioma'] = $porciones[1];
                    $filas['ocupacion'] = $porciones[2];    
                }
                else{
                    $filas['alfabetizado'] = "";
                    $filas['idioma'] = "";
                    $filas['ocupacion'] = "";    
                } 
                $exxx=1;
            }
            //print_r($filas);die;
            //echo '<div class="alert alert-success">El Estudiante '.$nombre.' '.$paterno.' '.$materno.' con CI: '.$ci.' con fecha de nacimiento: '.$fecha_nac.' puede ser registrao a este curso.</div>';
            if ($exxx==1){
                /*if($obs_adicional!=""){
                    return $this->render('SiePnpBundle:Default:mostrarestudiante.html.twig', array('filas'=>$filas,'curso_id'=>$curso_id,'gestion_ini'=>$gestion_ini)); die;
                }
                else{*/
                    return $this->render('SiePnpBundle:Default:mostrarestudiantes.html.twig', array('discapacidades'=>$discapacidades,'filas'=>$filas,'curso_id'=>$curso_id,'opcion'=>2,'gestion_ini'=>$gestion_ini));  die;          
                /*}*/
            }
            else{
                if($rude==1){
                    echo '<div class="alert alert-danger"><strong>Error, </strong>El Estudiante CODIGO RUDE '.$ci.' No existe.</div>'; die; 
                }
                else
                {
                    echo '<div class="alert alert-danger"><strong>Error, </strong>El Estudiante con  CI o CODIGO RUDE '.$ci.' No existe.</div>'; die;   
                }
                /*$servicioPersona = $this->get('sie_app_web.persona');
                $persona = $servicioPersona->buscarPersona($ci,$complemento,0);    
                if($persona->type_msg === "success"){   
                    $filas = array();
                    $filas['id'] = $persona->result[0]->id;
                    $filas['paterno'] = $persona->result[0]->paterno;
                    $filas['materno'] = $persona->result[0]->materno;
                    $filas['nombre'] = $persona->result[0]->nombre;
                    $fecha_nac=$persona->result[0]->fecha_nacimiento;
                    $filas['fecha_nac'] = $fecha_nac;
                    $filas['genero'] = $persona->result[0]->genero_tipo_id;
                    $filas['ci'] = $persona->result[0]->carnet;
                    $filas['complemento'] = $persona->result[0]->complemento;
                    return $this->render('SiePnpBundle:Default:mostrarestudiantes.html.twig', array('filas'=>$filas,'curso_id'=>$curso_id,'opcion'=>1,'gestion_ini'=>$gestion_ini));  die;
                    echo $curso_id;die;
                    die;
                }
                else{
                    echo '<div class="alert alert-danger">'.$persona->msg.'</div>';die;
                }*/
            }
        }


        /////////////////////////////////////////////FIN 

        $query = "SELECT
                      estudiante.id as estudiante_id,
                      estudiante.codigo_rude
                    FROM 
                      estudiante 
                    WHERE
                      $where";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        $cant=0;
        $estudiante_ex=0;
        foreach ($po as $p) {
            $rude = $p["codigo_rude"];
            $cant=1;
            $estudiante_id_rs=$p["estudiante_id"];
            $estudiante_existe=1;
        }   

        if($cant>0){

            ///Conocer si el estudiante tiene reconocimiento de saberes
        $curso_rs=0;

        $rec_sab = $this->getDoctrine()->getRepository('SieAppWebBundle:PnpReconocimientoSaberes')->findOneBy(array(
            'estudiante'  => $estudiante_id_rs, 'homologado' => true
        ));

        $rs_existe=0;
        $bloque_rs_toca=1;
        $parte_rs_toca=1;
        if($rec_sab){
            $curso_rs=$rec_sab->getCurso();
            if($curso_rs==2){$bloque_rs_toca=1;$parte_rs_toca=2;}
            elseif($curso_rs==3){$bloque_rs_toca=2;$parte_rs_toca=1;}
            elseif($curso_rs==5){$bloque_rs_toca=2;$parte_rs_toca=2;}
            elseif($curso_rs==6){$bloque_rs_toca=3;$parte_rs_toca=1;}
            $rs_existe=1;
        }
       
            $query = "SELECT
                      estudiante.id as estudiante_id,
                      estudiante.codigo_rude,
                      institucioneducativa_curso.ciclo_tipo_id,
                      institucioneducativa_curso.grado_tipo_id,
                      institucioneducativa_curso.fecha_fin,
                      institucioneducativa_curso.id as institucioneducativa_curso_id
                    FROM 
                      estudiante INNER JOIN estudiante_inscripcion ON estudiante.id = estudiante_inscripcion.estudiante_id
                      INNER JOIN genero_tipo ON genero_tipo.id = estudiante.genero_tipo_id
                      INNER JOIN estadomatricula_tipo ON estadomatricula_tipo.ID = estudiante_inscripcion.estadomatricula_inicio_tipo_id
                      INNER JOIN institucioneducativa_curso ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
                    WHERE
                      ($where) and 
                      (institucioneducativa_curso.institucioneducativa_id=80480300 or 
                      institucioneducativa_curso.institucioneducativa_id=80730794 or
                      institucioneducativa_curso.institucioneducativa_id=80980569 or
                      institucioneducativa_curso.institucioneducativa_id=81230297 or
                      institucioneducativa_curso.institucioneducativa_id=81480201 or 
                      institucioneducativa_curso.institucioneducativa_id=81730264 or 
                      institucioneducativa_curso.institucioneducativa_id=81981501 or 
                      institucioneducativa_curso.institucioneducativa_id=82230130 or 
                      institucioneducativa_curso.institucioneducativa_id=82480050 ) 
                    ORDER BY institucioneducativa_curso.fecha_fin asc";
            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $po = $stmt->fetchAll();
            $filas = array();
            $datos_filas = array();
            $cant=0;
            $bloque_e=1;
            $parte_e=1;
            $consulta1="";
            foreach ($po as $p) {
                $rude = $p["codigo_rude"];
                if($p["ciclo_tipo_id"]==34) $bloque_e=1;
                elseif($p["ciclo_tipo_id"]==35) $bloque_e=2;
                else $bloque_e=$p["ciclo_tipo_id"];

                if($p["grado_tipo_id"]==14 or $p["grado_tipo_id"]==16)$parte_e=1;
                elseif($p["grado_tipo_id"]==15 or $p["grado_tipo_id"]==17)$parte_e=2;
                else $parte_e=$p["grado_tipo_id"];

                $institucioneducativa_curso_id_e=$p["institucioneducativa_curso_id"];
                $consulta1="and institucioneducativa_curso.id='$institucioneducativa_curso_id_e'";
                $cant=1; 
            }
            
            //echo $institucioneducativa_curso_id_e;die;
            if($cant>0 or $estudiante_existe==1){
                //ver si le corresponde el curso donde se debea ingresar
              // return $this->redirect($this->generateUrl('sie_pnp_buscar_estudiantepnp'),array('ci'=>$ci,'bloque_e'=>$bloque_e,'parte_e'=>$parte_e,'opcion'=>$opcion,'curso_id'=>$curso_id));
               //$opcionnn=$this->estudianteenpnpAction($ci,$bloque_e,$parte_e,$opcion,$curso_id);  
               //die;
                //VER SI EL ESTUDIANTE TIENE ALGUN CURSO QUE ESTA INSCRITO Y ESTE CURSO ESTA ABIERTO
                $query = "SELECT e.id 
                from institucioneducativa_curso ic
                join estudiante_inscripcion ei on ei.institucioneducativa_curso_id=ic.id
                join estudiante e on e.id=ei.estudiante_id
                join institucioneducativa_curso_datos icd on icd.institucioneducativa_curso_id=ic.id
                where icd.esactivo=false and e.codigo_rude='$rude'";
                $stmt = $db->prepare($query);
                $params = array();
                $stmt->execute($params);
                $po = $stmt->fetchAll();
                $filas = array();
                $datos_filas = array();
                $curso_abierto=0;
                foreach ($po as $p) {
                    $curso_abierto=1;
                }
                if($curso_abierto==1){
                     echo '<div class="alert alert-danger"><strong>Error, </strong>El Estudiante con CI o CODIGO RUDE '.$ci.' No puede ser inscrito porque tiene cursos anteriores abiertos, primero debe cerrarlos!!!!.</div>'; die;
                }
                //funcion estudiante en PNP
                $em = $this->getDoctrine()->getManager();
                $db = $em->getConnection();
                //LISTA DE NOTAS
                $query = "SELECT carnet_estudiante, ciclo_tipo_id as bloque, grado_tipo_id as parte, avg(nota_cuantitativa) as nota  
                FROM
                (select persona.carnet, persona.nombre as nombre_facilitador, persona.paterno as paterno_facilitador, persona.materno as materno_facilitador,
                institucioneducativa_curso.fecha_inicio, institucioneducativa_curso.fecha_fin,
                institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id,
                institucioneducativa_curso.id,

                CASE
                            WHEN institucioneducativa_curso.institucioneducativa_id = 80480300 THEN
                                'CHUQUISACA'
                            WHEN institucioneducativa_curso.institucioneducativa_id = 80730794 THEN
                                'LA PAZ'
                            WHEN institucioneducativa_curso.institucioneducativa_id = 80980569 THEN
                                'COCHABAMBA'
                            WHEN institucioneducativa_curso.institucioneducativa_id = 81230297 THEN
                                'ORURO'
                            WHEN institucioneducativa_curso.institucioneducativa_id = 81480201 THEN
                                'POTOSI'
                            WHEN institucioneducativa_curso.institucioneducativa_id = 81730264 THEN
                                'TARIJA'
                            WHEN institucioneducativa_curso.institucioneducativa_id = 81981501 THEN
                                'SANTA CRUZ'
                            WHEN institucioneducativa_curso.institucioneducativa_id = 82230130 THEN
                                'BENI'
                            WHEN institucioneducativa_curso.institucioneducativa_id = 82480050 THEN
                                'PANDO'                         
                          END AS depto,
                     institucioneducativa_curso.lugar,
                estudiante_inscripcion.lugar as municipio,  
                estudiante_inscripcion.lugarcurso as localidad,
                estudiante_inscripcion.id as inscripcion_id,
                estudiante_inscripcion.estadomatricula_tipo_id as matricula_estado_id,estadomatricula_tipo.estadomatricula,estudiante.id as estudiante_id,
                  estudiante.codigo_rude, 
                  estudiante.carnet_identidad as carnet_estudiante, 
                  estudiante.paterno as paterno_estudiante, 
                  estudiante.materno as materno_estudiante, 
                  estudiante.nombre as nombre_estudiante, 
                  estudiante.fecha_nacimiento, 
                  estudiante.genero_tipo_id,
                  genero_tipo.genero,
                  estudiante.observacionadicional,
                  estudiante_inscripcion.estadomatricula_tipo_id as matricula_estado_id,
                  estadomatricula_tipo.estadomatricula,
                  estudiante_inscripcion.id as inscripcion_id,
                                        asignatura_tipo.asignatura,
                  nota_tipo.nota_tipo, 
                  estudiante_nota.nota_cuantitativa
                        
            from institucioneducativa_curso 
            inner join maestro_inscripcion 
            on institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion .id
            inner join persona 
            on maestro_inscripcion .persona_id = persona.id
                            INNER JOIN estudiante_inscripcion 
                            ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
            INNER JOIN estadomatricula_tipo 
                            ON estadomatricula_tipo.ID = estudiante_inscripcion.estadomatricula_tipo_id
                            inner join estudiante 
                            on estudiante.id = estudiante_inscripcion.estudiante_id
                            inner join genero_tipo 
                            ON genero_tipo.id = estudiante.genero_tipo_id                    
                            inner join public.estudiante_asignatura
                            on estudiante_asignatura.estudiante_inscripcion_id=estudiante_inscripcion.id
                            inner join public.estudiante_nota
                            on estudiante_nota.estudiante_asignatura_id = estudiante_asignatura.id
                            inner join public.institucioneducativa_curso_oferta
                            on institucioneducativa_curso_oferta.id=estudiante_asignatura.institucioneducativa_curso_oferta_id
            inner join public.asignatura_tipo
                            on institucioneducativa_curso_oferta.asignatura_tipo_id = asignatura_tipo.id
                            inner join public.nota_tipo
                            on estudiante_nota.nota_tipo_id = nota_tipo.id                    
               where  ($where) and nota_tipo='Nota Final' and ciclo_tipo_id=$bloque_e and grado_tipo_id=$parte_e $consulta1) as t1
                GROUP BY carnet_estudiante, ciclo_tipo_id, grado_tipo_id  
                  ";
                $stmt = $db->prepare($query);
                $params = array();
                $stmt->execute($params);
                $po = $stmt->fetchAll();
                $filas = array();
                $datos_filas = array();
                $nota=0;
                foreach ($po as $p) {
                    $nota = (int)$p["nota"];
                }   
                $paso=0;
                if($nota>35) $paso=1;

             
             $cursos = array(
                    array('curso'=>'1','bloque'=>'1','parte'=>'1'),
                    array('curso'=>'2','bloque'=>'1','parte'=>'2'),
                    array('curso'=>'3','bloque'=>'2','parte'=>'1'),
                    array('curso'=>'4','bloque'=>'2','parte'=>'2'),
                    array('curso'=>'5','bloque'=>'3','parte'=>'1'),
                );
                ///////si bloque 1 y parte 1 obtener de reconocimientos de sabaer su bloqye y parte
                if($rs_existe==1 and $bloque_e==1 and $parte_e==1){
                    $bloque_e=$bloque_rs_toca;
                    $parte_e=$parte_rs_toca;
                }        

                    foreach ($cursos as $c) {
                    if($c["bloque"]==$bloque_e and $c["parte"]==$parte_e)
                        $curso_c=$c["curso"]+$paso;
                } 

                foreach ($cursos as $c) {
                    if($c["curso"]==$curso_c){
                        $bloque_c=$c["bloque"];
                        $parte_c=$c["parte"];
                    }
                }

                if($bloque_c>2){
                    echo '<div class="alert alert-danger"><strong>Error, </strong>El Estudiante con CI o CODIGO RUDE '.$ci.' Finalizo los 4 módulos, por tanto no puede ser registrado en el curso.</div>'; die; 
                }
                else{
                    //Buscqueda si en el curso existe el mismo estudiante
                        $em = $this->getDoctrine()->getManager();
                        $db = $em->getConnection();
                        //LISTA DE ESTUDIANTES
                        //LISTA DE ESTUDIANTES
                        $query = "SELECT 
                                      estudiante.id as estudiante_id,
                                      estudiante.codigo_rude, 
                                      estudiante.carnet_identidad, 
                                      estudiante.paterno, 
                                      estudiante.materno, 
                                      estudiante.nombre, 
                                      estudiante.fecha_nacimiento, 
                                      estudiante.genero_tipo_id,
                                      genero_tipo.genero,
                                      estudiante.observacionadicional,
                                      estudiante_inscripcion.estadomatricula_tipo_id as matricula_estado_id,
                                      estadomatricula_tipo.estadomatricula,
                                      estudiante_inscripcion.id as inscripcion_id
                                    FROM 
                                      estudiante INNER JOIN estudiante_inscripcion ON estudiante.id = estudiante_inscripcion.estudiante_id
                                      INNER JOIN genero_tipo ON genero_tipo.id = estudiante.genero_tipo_id
                                      INNER JOIN estadomatricula_tipo ON estadomatricula_tipo.ID = estudiante_inscripcion.estadomatricula_inicio_tipo_id
                                      INNER JOIN institucioneducativa_curso ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
                                    WHERE
                                      institucioneducativa_curso.id = ".$curso_id;
                        
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $po = $stmt->fetchAll();
                        $filas = array();
                        $datos_filas = array();
                        $existe_estudiante=0;
                        foreach ($po as $p) {
                            if($ci==$p["carnet_identidad"] or $ci==$p["codigo_rude"])
                               $existe_estudiante=1; 
                        }
                        
                    if($existe_estudiante==1){
                    //si no existe en el curso saber si el curso es aquel que le corresponde
                    echo '<div class="alert alert-danger"><strong>Error, </strong>El Estudiante con CI o CODIGO RUDE '.$ci.' ya esta inscrito en este curso.</div>'; die; 
                    }
                    else{
                        $opcion_est="$bloque_c$parte_c";
                        if($opcion_est==$opcion){
                           //$this->registrarestudianteAction($ci,$bloque_c,$parte_c,$opcion,$curso_id);     
                        //funcion registrar estudiante
                            /////////////////MUESTRA AL ESTUDIANTE PARA PODER REGISTRAR

                            $po = array();
                            $po=$this->retornar_estudianteAction($where);
                            $filas = array();
                            $datos_filas = array();
                            foreach ($po as $p) {
                                $filas['rude'] = $p["codigo_rude"];
                                $filas['paterno'] = $p["paterno"];
                                $filas['materno'] = $p["materno"];
                                $filas['nombre'] = $p["nombre"];
                                $filas['fecha_nac'] = $p["fecha_nacimiento"];
                                $filas['genero'] = $p["genero"];
                                $filas['ci'] = $p["carnet_identidad"];
                                $filas['complemento'] = $p["complemento"];
                                $obs_adicional=$p["observacionadicional"];
                                if ( $obs_adicional != "") {
                                    $porciones = explode("|", $obs_adicional);
                                    $filas['alfabetizado'] = $porciones[0];
                                    $filas['idioma'] = $porciones[1];
                                    $filas['ocupacion'] = $porciones[2];    
                                }
                                else{
                                    $filas['alfabetizado'] = "";
                                    $filas['idioma'] = "";
                                    $filas['ocupacion'] = "";    
                                } 
                                
                            }
                            //print_r($filas);die;
                            //echo '<div class="alert alert-success">El Estudiante '.$nombre.' '.$paterno.' '.$materno.' con CI: '.$ci.' con fecha de nacimiento: '.$fecha_nac.' puede ser registrao a este curso.</div>';
                            
                        /*if($obs_adicional!=""){
                            return $this->render('SiePnpBundle:Default:mostrarestudiante.html.twig', array('filas'=>$filas,'curso_id'=>$curso_id,'gestion_ini'=>$gestion_ini)); 
                            die;
                        }
                        else{*/
                            return $this->render('SiePnpBundle:Default:mostrarestudiantes.html.twig', array('discapacidades'=>$discapacidades,'filas'=>$filas,'curso_id'=>$curso_id,'opcion'=>2,'gestion_ini'=>$gestion_ini));            
                        /*}*/

                         ////////   
                        }
                        else{
                            $curso_toca="";
                            if ($bloque_c==1 and $parte_c==1)$curso_toca="Primer";
                            if ($bloque_c==1 and $parte_c==2)$curso_toca="Tercer";
                            if ($bloque_c==2 and $parte_c==1)$curso_toca="Cuarto";
                            if ($bloque_c==2 and $parte_c==2)$curso_toca="Sexto";
                            echo '<div class="alert alert-danger"><strong>Error, </strong>El Estudiante con CI O CODIGO RUDE '.$ci.' le corresponde el: '.$curso_toca.' curso, y no este curso.</div>'; die; 
                        }
                    }
                }

                //////
            }
            else{
                    $anio_actual=date("Y");
                    $query = "SELECT estudiante.id as estudiante_id,estudiante.nombre,ic.nivel_tipo_id,
estudiante.codigo_rude,ei.fecha_inscripcion, extract(year from ei.fecha_inscripcion) as anio
from estudiante_inscripcion ei, estudiante,institucioneducativa_curso ic
where ($where) and
ic.id=ei.institucioneducativa_curso_id and estudiante.id=ei.estudiante_id and extract(year from ei.fecha_inscripcion) = '$anio_actual' and ic.nivel_tipo_id != 19";
                    $stmt = $db->prepare($query);
                    $params = array();
                    $stmt->execute($params);
                    $po = $stmt->fetchAll();
                    $filas = array();
                    $datos_filas = array();
                    $cant=0;
                    foreach ($po as $p) {
                        $rude = $p["codigo_rude"];
                        $cant=1;
                    }   
                    if($cant>0){
                        echo '<div class="alert alert-danger"><strong>Error, </strong>El Estudiante con CI o CODIGO RUDE '.$ci.' está registrado en el Sistema Regular o Alternativa.</div>'; die;                       }
                    else{                        

                        $query = "SELECT
                          estudiante.id as estudiante_id,
                          estudiante.codigo_rude,
                          estudiante.paterno,
                          estudiante.materno,
                          estudiante.nombre,
                          estudiante.fecha_nacimiento,
                          estudiante.carnet_identidad,
                          estudiante.observacionadicional
                        FROM 
                          estudiante
                        WHERE
                          $where";
                        $stmt = $db->prepare($query);
                        $params = array();
                        $stmt->execute($params);
                        $po = $stmt->fetchAll();
                        $filas = array();
                        $datos_filas = array();
                            foreach ($po as $p) {
                                $filas['rude'] = $p["codigo_rude"];
                                $filas['paterno'] = $p["paterno"];
                                $filas['materno'] = $p["materno"];
                                $filas['nombre'] = $p["nombre"];
                                $filas['fecha_nac'] = $p["fecha_nacimiento"];
                                $filas['ci'] = $p["carnet_identidad"];
                                $obs_adicional=$p["observacionadicional"];
                                if ( $obs_adicional != "") {
                                    $porciones = explode("|", $obs_adicional);
                                    $filas['alfabetizado'] = $porciones[0];
                                    $filas['idioma'] = $porciones[1];
                                    $filas['ocupacion'] = $porciones[2];    
                                }
                                else{
                                    $filas['alfabetizado'] = "";
                                    $filas['idioma'] = "";
                                    $filas['ocupacion'] = "";    
                                } 
                            }
                            //print(arg)_r($filas);die;
                            //echo '<div class="alert alert-success">El Estudiante '.$nombre.' '.$paterno.' '.$materno.' con CI: '.$ci.' con fecha de nacimiento: '.$fecha_nac.' puede ser registrao a este curso.</div>';
                        //vemos si tiene observacion adicional (Alfabetizado, idioma, Ocupacion)
                        /*if($obs_adicional!=""){
                            return $this->render('SiePnpBundle:Default:mostrarestudiante.html.twig', array('filas'=>$filas,'curso_id'=>$curso_id,'gestion_ini'=>$gestion_ini)); 
                            die;
                        }
                        else{*/
                            return $this->render('SiePnpBundle:Default:mostrarestudiantes.html.twig', array('discapacidades'=>$discapacidades,'filas'=>$filas,'curso_id'=>$curso_id,'opcion'=>2,'gestion_ini'=>$gestion_ini));            
                        /*}*/
          
                    }
                }
            }
        else{
            //////////NO EXISTE EN LA TABLA ESTUDIANTE, BUSCAR EN LA TABLA PERSONA
        //and (p.esvigenteApoderado=1 or p.esvigente=t)
    
            if($rude==1){
               echo '<div class="alert alert-danger"><strong>Error, </strong>El Estudiante CODIGO RUDE '.$ci.' No existe.</div>'; die;  
            }
            else
            {
                echo '<div class="alert alert-danger"><strong>Error, </strong>El Estudiante con CI o CODIGO RUDE '.$ci.' No existe.</div>'; die;     
            }
            /*else{ ya no buscar en la tabla persona
                $servicioPersona = $this->get('sie_app_web.persona');
                $persona = $servicioPersona->buscarPersona($ci,$complemento,0);    
                if($persona->type_msg === "success"){   
                    $filas = array();
                    $filas['id'] = $persona->result[0]->id;
                    $filas['paterno'] = $persona->result[0]->paterno;
                    $filas['materno'] = $persona->result[0]->materno;
                    $filas['nombre'] = $persona->result[0]->nombre;
                    $fecha_nac=$persona->result[0]->fecha_nacimiento;
                    $filas['fecha_nac'] = $fecha_nac;
                    $filas['genero'] = $persona->result[0]->genero_tipo_id;
                    $filas['ci'] = $persona->result[0]->carnet;
                    $filas['complemento'] = $persona->result[0]->complemento;
                    if($opcion==11)
                        return $this->render('SiePnpBundle:Default:mostrarestudiantes.html.twig', array('filas'=>$filas,'curso_id'=>$curso_id,'opcion'=>1,'gestion_ini'=>$gestion_ini)); 
                    else
                        echo '<div class="alert alert-danger"><strong>Error, </strong>El Estudiante CODIGO RUDE '.$ci.' le corresponde Primer curso     y no este curso.</div>'; die;     
                }
                else{
                    echo '<div class="alert alert-danger">'.$persona->msg.'</div>';die;
                    }
            }*/
        }       
        return $this->render('SiePnpBundle:Default:mostrarestudiante.html.twig', array('cant'=>$cant));
    }



   public function registrarestudiantecursoAction($rude,$curso_id,$tipo,Request $request){
        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $em->getConnection()->beginTransaction();

        /////////////////////////VEMOS LA GESTION DEL CURSO, YA QUE SI ES MENOR A 2016 REALIZAMOS
        //UNA INSERCION PARA RUP 4 SUBIDOS CON EXCEL CASO CONTRARIO LA INSERCION LA INSERCION ES DIFERENTE CON LA NUEVA TABLA INSTITUCONEDUCATIVA_CURSO_DATOS

        //SACAMOS LA FECHA DE INICIO DEL CURSO LA GESTION
        
        $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($curso_id);
                $gestion=$result->getGestionTipo();
                $anio=$result->getGestionTipo()->getId();
                $institucioneducativa_id=$result->getInstitucioneducativa()->getId();

        

        ///////////VER SI EL TIPO ES 0, ESTA EN LA TABLA ESTUDIANTE SI ES 1 EN LA TABLA PERSONA
        //POR TANTO DEBEMOS PRIMERO LLEVAR DE LA TABLA PERSONA A LA TABLA ESTUDIANTE PARA CONTINUAR CON LAS DEMAS INSERCIONES
        /*if($tipo==1){
        
            $id_persona=$rude;
             
            //obtener los tres valores y unirlos
             $alfabetizado=$request->get("alfabetizado");
             $idioma=$request->get("idioma");
             $ocupacion=$request->get("ocupacion");

             $observacionadicional=$alfabetizado.'|'.$idioma.'|'.$ocupacion;
            //BUSCAR VALORES

            switch ($institucioneducativa_id) {
            case 80480300://CHUQUISACA
                $ie = '80480300';
                $rude1 = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('1');
                break;
            case 80730794://LA PAZ
                $ie = '80730794';
                $rude1 = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('2');
                break;
            case 80980569://COCHABAMBA
                $ie = '80980569';
                $rude1 = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('3');
                break;
            case 81230297://ORURO
                $ie = '81230297';
                $rude1 = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('4');
                break;
            case 81480201://POTOSI
                $ie = '81480201';
                $rude1 = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('5');
                break;
            case 81730264://TARIJA
                $ie = '81730264';
                $rude1 = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('6');
                break;
            case 81981501://SANTA CRUZ
                $ie = '81981501';
                $rude1 = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('7');
                break;
            case 82230130://BENI
                $ie = '82230130';
                $rude1 = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('8');
                break;
            case 82480050://PANDO
                $ie = '82480050';
                $rude1 = $em->getRepository('SieAppWebBundle:PnpSerialRude')->find('9');
                break;
        }

            //GENERA RUDE
            $seqrude = (string)$rude1->getSeqrude()+1;
            $codrude = $ie.$anio.str_pad($seqrude, 6, "0", STR_PAD_LEFT);

            //BUSCA DATOS PERSONA    
            $persona = $this->getDoctrine()->getRepository('SieAppWebBundle:Persona')->findOneById($id_persona);
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante');")->execute();
            $estudiante = new Estudiante();
            $estudiante->setCodigoRude($codrude);
            $rude=$codrude;                
            $estudiante->setCarnetIdentidad($persona->getCarnet());                
            $estudiante->setPaterno($persona->getPaterno());
            $estudiante->setMaterno($persona->getMaterno());
            $estudiante->setNombre($persona->getNombre());
            //$genero=$persona->getGeneroTipo()->getId();

            //$estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById(3));//
            //echo $genero;die;
            $estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($persona->getGeneroTipo()->getId()));
            $estudiante->setEstadoCivil($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find($persona->getEstadoCivilTipo()->getId()));
            $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('1'));                    
            $estudiante->setOficialia('');
            $estudiante->setLibro('');
            $estudiante->setPartida('');
            $estudiante->setFolio('');
            //$estudiante->setSangreTipoId($this->getDoctrine()->getRepository('SieAppWebBundle:SangreTipo')->find($persona->getSangreTipo())->getId());
            $estudiante->setIdiomaMaternoId('0');
            $estudiante->setSegipId($persona->getSegipId());
            $estudiante->setComplemento($persona->getComplemento());
            $estudiante->setBolean(false);
            $estudiante->setFechaNacimiento($persona->getFechaNacimiento());                                               
            $estudiante->setFechaModificacion(new \DateTime('now'));
            $estudiante->setCorreo('');
            $estudiante->setPaisTipo($this->getDoctrine()->getRepository('SieAppWebBundle:PaisTipo')->find('1'));
            $estudiante->setLocalidadNac($this->getDoctrine()->getRepository('SieAppWebBundle:Persona')->find('1'));
            //$estudiante->setFoto();
            //$estudiante->setCelular('');
            //$estudiante->setResolucionaprovatoria('');
            //$estudiante->setCarnetCodepedis('');
            $estudiante->setObservacionadicional($observacionadicional);                            
            //$estudiante->setCarnetIbc('');
            //$estudiante->setLibretaMilitar('');
            $em->persist($estudiante);
            $em->flush();

            //ACTUALIZA SECUANCIA DE RUDES
            $rude1->setSeqrude($seqrude);
            $em->persist($rude1);
            $em->flush();
        } */ 
        ////////////////////DATOS
        $estado=61; //62 En Clase 
        ////si la gestion es menor a 2016 los datos lugar,LugarCurso,Facilitadorcurso 

        /*if($anio < 2016){
                // id de la tabla estudiante_inscripcion para traer el lugar, lugarcurso y facilitadorcurso
            //**********************TENER CUIDADO AL REGISTRAR NUEVO ESTUDIANTE EN UN CURSO PQ LOS DATOS SALDRIAN AL CREAR EL CURSO
            $result=$em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findByinstitucioneducativaCurso($curso_id);
            foreach ($result as $results) {
                $municipio=$results->getLugar();
                $localidad=$results->getLugarCurso();
                $facilitador=$results->getfacilitadorcurso();
            }
            
        }*/
        //else{
            //primero obtenemos municipio y localidad
            $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findByinstitucioneducativaCurso($curso_id);
            foreach ($result as $results) {
                $municipio=$results->getLugarTipoSeccion();
                $localidad=$results->getLocalidad();
                $plan=$results->getPlancurricularTipoId();
            }

            //obtenemos el nombre del facilitador
            
            $query = "SELECT 
              p.paterno, p.materno, p.nombre
            FROM 
              persona p
              INNER JOIN maestro_inscripcion as mi ON p.id=mi.persona_id 
              INNER JOIN institucioneducativa_curso as ic ON mi.id=ic.maestro_inscripcion_id_asesor
            WHERE
              ic.id = ".$curso_id;

            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $po = $stmt->fetchAll();
            $filas = array();
            $datos_filas = array();
            $existe_estudiante=0;
            foreach ($po as $p) {
                $paterno=$p["paterno"];
                $materno=$p["materno"];
                $nombre=$p["nombre"];
            }
            $facilitador="$nombre $paterno $materno";
        

        try {/////insertar carnet_identidad
                
                //////////////////////AUMENTAR, SI ES TIPO 2, ES ESTUDIANTE PERO NO TIENE LA PARTE DE ALFABETIZADO, OCUPACION POR TANTO SE DEBE PRIMERO ACTUALIZAR AL ESTUDIANTE ESA PARTE
                
                $alfabetizado=$request->get("alfabetizado");
                $idioma=$request->get("idioma");
                $ocupacion=$request->get("ocupacion");
                $discapacidad=$request->get("discapicidad");

                $observacionadicional=$alfabetizado.'|'.$idioma.'|'.$ocupacion;
                $result=$em->getRepository('SieAppWebBundle:Estudiante')->findOneBycodigoRude($rude);
                $result->setObservacionadicional($observacionadicional);
                $em->flush();
                $lugar_llenar=$result->getLocalidadNac();
            

                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');")->execute();
                $inscripcion = new EstudianteInscripcion();
                $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById($estado));
                //if($tipo==0 or $tipo==2)
                $inscripcion->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->findOneBycodigoRude($rude));
                //else $inscripcion->setEstudiante($estudiante);
                $inscripcion->setNumMatricula(0);
                $inscripcion->setObservacionId(0);
                $inscripcion->setObservacion(0);
                $inscripcion->setOperativoId(1);
                $inscripcion->setFechaRegistro(new \DateTime('now'));
                $inscripcion->setApreciacionFinal('');
                $inscripcion->setLugar($municipio);
                $inscripcion->setLugarcurso($localidad);
                $inscripcion->setFacilitadorcurso($facilitador);
                $inscripcion->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($curso_id));
                $inscripcion->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById('66'));
                $em->persist($inscripcion);
                $em->flush();
                /////////////OCUPACION Y DISCAPACDAD SI EL PLAN 2
                if ($plan==2){
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_socioeconomico_alternativa');")->execute();
                    $estinscsocalt = new EstudianteInscripcionSocioeconomicoAlternativa();
                    $estinscsocalt->setEstudianteInscripcion($inscripcion);

                    $estinscsocalt->setSeccionivDiscapacitadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->findOneById($discapacidad));
                    $estinscsocalt->setSeccionvOtroTrabajo($ocupacion);
                    $estinscsocalt->setLugar($lugar_llenar);
                    $estinscsocalt->setFechaRegistro(new \DateTime('now'));
                    $estinscsocalt->setFecha(new \DateTime('now'));
                    $em->persist($estinscsocalt);
                    $em->flush();
                }
                ///////////////////FIN

                if ($plan==1){
                    /////buscar las id en la tabla institucion educativa curso oferta para las materias para luego registrar en estudiante asignatura 
                    $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findByinsitucioneducativaCurso($curso_id);
                    foreach ($result as $results) {
                    ///obtenemos el id del cusro oferta y su registro como symfony acepta
                        $institucioneducativa_curso_oferta_id=$results->getId();
                        ///////registramos estudiante asignatura

                        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');")->execute();
                        $estAsignaturaNew = new EstudianteAsignatura();
                        $estAsignaturaNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                        $estAsignaturaNew->setEstudianteInscripcion($inscripcion);
                        $estAsignaturaNew->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($institucioneducativa_curso_oferta_id));
                        $em->persist($estAsignaturaNew);
                        $em->flush();

                        $materia_id=$results->getAsignaturaTipo()->getId();
                        //////registramos 6 notas por materia (12:Trabajo Grupal,13:Trabajo Individual,14:Prueba Final,15:Asistencia,16:Nota Final)
                        if($materia_id!=2007) //2007 prueba final solo debe tener una nota promedio final
                        {
                            for($i=12;$i<=16;$i++){
                                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();
                                $registro_nota = new EstudianteNota();
                                $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($i));
                                $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                                $registro_nota->setNotaCuantitativa('0');
                                $registro_nota->setUsuarioId($this->session->get('userId'));
                                $registro_nota->setFechaRegistro(new \DateTime('now'));                       
                                $em->persist($registro_nota);
                                $em->flush();       
                            }
                        }
                        else{
                            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();
                            $registro_nota = new EstudianteNota();
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(17));
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setNotaCuantitativa('0');
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                       
                            $em->persist($registro_nota);
                            $em->flush();  
                        }
                    }
                }
                elseif($plan==2){
                    /////buscar las id en la tabla institucion educativa curso oferta para las materias para luego registrar en estudiante asignatura 
                    $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findByinsitucioneducativaCurso($curso_id);
                    foreach ($result as $results) {
                    ///obtenemos el id del cusro oferta y su registro como symfony acepta
                        $institucioneducativa_curso_oferta_id=$results->getId();
                        ///////registramos estudiante asignatura

                        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');")->execute();
                        $estAsignaturaNew = new EstudianteAsignatura();
                        $estAsignaturaNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                        $estAsignaturaNew->setEstudianteInscripcion($inscripcion);
                        $estAsignaturaNew->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($institucioneducativa_curso_oferta_id));
                        $em->persist($estAsignaturaNew);
                        $em->flush();

                        $materia_id=$results->getAsignaturaTipo()->getId();
                        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();
                        $registro_nota = new EstudianteNota();
                        $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(16));//NOTA FINAL
                        $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                        $registro_nota->setNotaCuantitativa('0');
                        $registro_nota->setUsuarioId($this->session->get('userId'));
                        $registro_nota->setFechaRegistro(new \DateTime('now'));                       
                        $em->persist($registro_nota);
                        $em->flush();
                    }
                    //nota cualitativa
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota_cualitativa');")->execute();
                    $registro_nota_cualitativa = new EstudianteNotaCualitativa();
                    $registro_nota_cualitativa->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(16));//NOTA FINAL 
                    $registro_nota_cualitativa->setEstudianteInscripcion($inscripcion);
                    $registro_nota_cualitativa->setEstudianteInscripcion($inscripcion);
                    $registro_nota_cualitativa->setNotaCuantitativa(0);
                    $registro_nota_cualitativa->setUsuarioId($this->session->get('userId'));
                    $registro_nota_cualitativa->setFechaRegistro(new \DateTime('now'));
                    $em->persist($registro_nota_cualitativa);
                    $em->flush();

                }                    
                     
            $this->get('session')->getFlashBag()->add(
                'success',
                'Estudiante registrado con exito.'
                );      
            $em->getConnection()->commit();
            }
            catch (Exception $e) {
                 $em->getConnection()->rollback();
                 $this->get('error')->getFlashBag()->add(
                    'notice',
                    'Existio un problema al insertar al estudiante.'
                    );      
                throw $e;
           }
        return $this->redirectToRoute('sie_pnp_curso_listado_editnew',array('id'=>$curso_id));
   }

   public function elminarregistroestudiantecursoAction($estudiante_inscripcion_id,$curso_id,Request $request){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        
        $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findOneByinstitucioneducativaCurso($curso_id);
        $plan=$result->getPlancurricularTipoId();

            
        // id de la tabla estudiante_asignatura
        $result=$em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findByestudianteInscripcion($estudiante_inscripcion_id);
        $estudiante_asignatura_id = array();
        foreach ($result as $results) {
            $estudiante_asignatura_id[]=$results->getId();
        }
        // id de la tabla estudiante_nota
        $result=$em->getRepository('SieAppWebBundle:EstudianteNota')->findByestudianteAsignatura($estudiante_asignatura_id);
        $estudiante_nota_id = array();
        foreach ($result as $results) {
            $estudiante_nota_id[]=$results->getId();
        }
        // nuevo id de la tabla apoderado inscripcion
        $result=$em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findByestudianteInscripcion($estudiante_inscripcion_id);
        $estudiante_apoderado_inscripcion_id = array();
        foreach ($result as $results) {
            $estudiante_apoderado_inscripcion_id[]=$results->getId();
        }
        if($plan==2){
            // id de la tabla estudiante_nota
            $result=$em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findByestudianteInscripcion($estudiante_inscripcion_id);
            $estudiante_nota_cualitativa_id = array();
            foreach ($result as $results) {
                $estudiante_nota_cualitativa_id[]=$results->getId();
            }   
            //id de la tabla estudiante_inscripción_socioeconomico_alternativa
            $result=$em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAlternativa')->findByestudianteInscripcion($estudiante_inscripcion_id);
            $estudiante_inscripcion_socioeconomico_alternativa_id = array();
            foreach ($result as $results) {
                $estudiante_inscripcion_socioeconomico_alternativa_id[]=$results->getId();
            }
        }

        $em->getConnection()->beginTransaction();
        try {
            ////////////////ELMINANDO
            // Eliminar Notas de los estudiantes
            $result=$em->getRepository('SieAppWebBundle:EstudianteNota')->findById($estudiante_nota_id);
            foreach ($result as $element) {
                $em->remove($element);
            }
            $em->flush();
            // Eliminar Asignaturas
            $result=$em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findById($estudiante_asignatura_id);
            foreach ($result as $element) {
                $em->remove($element);
            }          
            $em->flush();
            // Eliminar Apoderado Inscripcion
             $result=$em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findById($estudiante_apoderado_inscripcion_id);
            foreach ($result as $element) {
                $em->remove($element);
            }          
            $em->flush();
            // Eliminar Inscripciones
            $result=$em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findById($estudiante_inscripcion_id);
            foreach ($result as $element) {
                $em->remove($element);
            }
            /////plan 2
            if($plan==2){
                //eliminar nota cualitativa
                $result=$em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findById($estudiante_nota_cualitativa_id);
                foreach ($result as $element) {
                    $em->remove($element);
                }  
                //eliminar estudiante inscripcion socioeconomico alternativa
                $result=$em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAlternativa')->findById($estudiante_inscripcion_socioeconomico_alternativa_id);
                foreach ($result as $element) {
                    $em->remove($element);
                } 
            }

            $em->flush();
            $this->get('session')->getFlashBag()->add(
                    'success',
                    'elminación de registro con exito.'
                    );      
            $em->getConnection()->commit();
        }
        catch (Exception $e) {
            $em->getConnection()->rollback();
            $this->get('error')->getFlashBag()->add(
            'notice',
            'Existio un problema al elminar el registro.'
            );      
            throw $e;
        }        

        $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($curso_id);
            $anio=$result->getGestionTipo()->getId();
        /*if($anio < 2016)
            return $this->redirectToRoute('sie_pnp_curso_listado_edit',array('id'=>$curso_id));
        else */
            return $this->redirectToRoute('sie_pnp_curso_listado_editnew',array('id'=>$curso_id));
   }

    public function vernotas_editAction($idinscripcion,$id_curso)
    {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findOneByinstitucioneducativaCurso($id_curso);
                $plan=$result->getPlancurricularTipoId();

        if($plan==1)$order="ORDER BY asignatura_tipo.asignatura, nota_tipo.orden";
        if($plan==2)$order="order by  asignatura_tipo.asignatura_boletin";
        //LISTA DE NOTAS
        $query = "SELECT
                      estudiante_nota.id,
                      asignatura_tipo.asignatura,
                      nota_tipo.nota_tipo, 
                      estudiante_nota.nota_cuantitativa,
                      estudiante_inscripcion.estadomatricula_tipo_id,
                      estadomatricula_tipo.estadomatricula,
                      estudiante.nombre,
                      estudiante.paterno,
                      estudiante.materno,
                      estudiante.carnet_identidad,
                      estudiante.complemento,
                      estudiante_nota_cualitativa.nota_cualitativa
                    FROM 
                      public.estudiante
                    inner join estudiante_inscripcion on estudiante.id =estudiante_inscripcion.estudiante_id
                    inner join estudiante_asignatura on estudiante_asignatura.estudiante_inscripcion_id=estudiante_inscripcion.id
                    inner join institucioneducativa_curso_oferta on institucioneducativa_curso_oferta.id=estudiante_asignatura.institucioneducativa_curso_oferta_id
                    inner join asignatura_tipo on institucioneducativa_curso_oferta.asignatura_tipo_id = asignatura_tipo.id
                    inner join estudiante_nota on estudiante_nota.estudiante_asignatura_id = estudiante_asignatura.id
                    inner join nota_tipo on  estudiante_nota.nota_tipo_id = nota_tipo.id
                    inner join estadomatricula_tipo on estudiante_inscripcion.estadomatricula_tipo_id=estadomatricula_tipo.id
                    left join estudiante_nota_cualitativa on estudiante_nota_cualitativa.estudiante_inscripcion_id=estudiante_inscripcion.id 
                    WHERE
                      estudiante_inscripcion.id  = ".$idinscripcion."
                    $order";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        $cant_notas=0;
        foreach ($po as $p) {
            $cant_notas++;
            $datos_filas["id"] = $p["id"];
            $datos_filas["asignatura"] = $p["asignatura"];
            $datos_filas["nota_tipo"] = $p["nota_tipo"];
            $datos_filas["nota_cuantitativa"] = $p["nota_cuantitativa"];
            if($p["nota_cualitativa"]=="")
                $datos_filas["nota_cualitativa"] = "";
            else
                $datos_filas["nota_cualitativa"] = $p["nota_cualitativa"];
            $datos_filas["estadomatricula_tipo_id"] = $p["estadomatricula_tipo_id"];
            $datos_filas["estadomatricula"] = $p["estadomatricula"];
            $carnet= $p["carnet_identidad"];
            $complemento= $p["complemento"];
            $nombre= $p["nombre"] . " " . $p["paterno"] . " " . $p["materno"];
            $filas[] = $datos_filas;
        } 


        if($plan==1)
            return $this->render('SiePnpBundle:Default:vernotas_edit.html.twig', array('notas' => $filas,'cant_notas'=>$cant_notas,'id_curso'=>$id_curso,'idinscripcion'=>$idinscripcion,'carnet'=>$carnet,'complemento'=>$complemento,'nombre'=>$nombre));
        if($plan==2){
            //recoger nombre modulo emergente 

            $query = "SELECT ico.id from institucioneducativa_curso ic
            join institucioneducativa_curso_oferta ico on ico.insitucioneducativa_curso_id=ic.id
            where ic.id=$id_curso and ico.asignatura_tipo_id=2012
            ";
            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $po = $stmt->fetchAll();
            
            foreach ($po as $p) {
                $id_ico = $p["id"];
            }
            $modulo_emergente=$em->getRepository('SieAppWebBundle:AltModuloemergente')->findOneByInstitucioneducativaCursoOferta($id_ico);

            return $this->render('SiePnpBundle:Default:vernotas_edit_p2.html.twig', array('notas' => $filas,'cant_notas'=>$cant_notas,'id_curso'=>$id_curso,'idinscripcion'=>$idinscripcion,'carnet'=>$carnet,'complemento'=>$complemento,'nombre'=>$nombre,'modulo_emergente'=>$modulo_emergente));
        }
    }

public function registrar_cursoAction(Request $request, $plan){
     
    if($request->getMethod()=="POST") {
        $form = $request->get('sie_pnp_registrar_curso_nuevo');
        $em = $this->getDoctrine()->getManager(); 
        $userId = $this->session->get('userId');
        $fecha_inicio= $form['fecha_inicio'];
        $fecha_fin= $form['fecha_fin'];
        $plan= $form['plan'];// 1 antiguo 2 nuevo
        $gestion= substr($fecha_fin,-4);
        $gestion_i= substr($fecha_inicio,-4);
        //buscar departamento codigo del curso
        switch ($form['departamento'])  {
            case 2: $ie=80480300; break;
            case 31654: $ie=80480300; break;
            case 3: $ie=80730794; break;
            case 31655: $ie=80730794; break;
            case 4: $ie=80980569; break;
            case 31656: $ie=80980569; break;
            case 5: $ie=81230297; break;
            case 31657: $ie=81230297; break;
            case 6: $ie=81480201; break;
            case 31658: $ie=81480201; break;
            case 7: $ie=81730264; break;
            case 31659: $ie=81730264; break;
            case 8: $ie=81981501; break;
            case 31660: $ie=81981501; break;
            case 9: $ie=82230130; break;
            case 31661: $ie=82230130; break;
            case 10: $ie=82480050; break;
            case 31662: $ie=82480050; break;
            default: $ie=0; break;
        }
        ///////////////////// sacar datos para sucursal nuevo 2018
        //
        $le_jurisdiccion_g=$em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ie);
        $le_jurisdiccion_g=$le_jurisdiccion_g->getLeJuridicciongeografica()->getId();
        
        $institucioneducativa_sucursal_id=$em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array(
            'institucioneducativa'  => $ie,
            'gestionTipo' => $gestion,
            'leJuridicciongeografica'=> $le_jurisdiccion_g,
            'periodoTipoId'=>5));

        if($institucioneducativa_sucursal_id)
            $institucioneducativa_sucursal_id=$institucioneducativa_sucursal_id->getId();
        else{
             $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal');");
                $query->execute();
                $institucioneducativa_sucursal_id = new InstitucioneducativaSucursal();
                $institucioneducativa_sucursal_id->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find(0));
                $institucioneducativa_sucursal_id->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie));
                $institucioneducativa_sucursal_id->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion));
                $institucioneducativa_sucursal_id->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($le_jurisdiccion_g));  
                $institucioneducativa_sucursal_id->setCodCerradaId(10);       
                $institucioneducativa_sucursal_id->setPeriodoTipoId(5);       
                $institucioneducativa_sucursal_id->setNombreSubcea("PNP");  

                $em->persist($institucioneducativa_sucursal_id);
                $em->flush(); 
        }
        //$maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($institucioneducativa_sucursal_id));
        //////////////////////////////

        $persona_id=$request->get("persona_id");
        $ciclo=$form['bloque'];
        $grado=$form['parte'];
        $modulo=0;/////////modulo 1 2 3 4 PLAN 1      5 6 PLAN 2
        //////////
        if($ciclo==1 and $grado==1){$nroMaterias=4;$modulo=1;}//PLAN 1 B1P1
        if($ciclo==1 and $grado==2){$nroMaterias=6;$modulo=2;}//PLAN 1 B1P2
        if($ciclo==2 and $grado==1){$nroMaterias=5;$modulo=3;}//PLAN 1 B2P1
        if($ciclo==2 and $grado==2){$nroMaterias=3;$modulo=4;}//PLAN 1 B2P2
        if($grado>=14 and $grado <=17){$nroMaterias=5;$modulo=5;}//PLAN 2 SEM 1,2,3,4
        
        ///sacando datos para gurdar        
        // id de la tabla maestro_inscripcion
        //$result=$em->getRepository('SieAppWebBundle:PersonaCarnetControl')->findOneByCarnet($ci);
        //$persona_id=$result->getPersona()->getId();

        
        $persona=$em->getRepository('SieAppWebBundle:Persona')->findOneById($persona_id);
        $ci=$persona->getCarnet();       
        $result=$em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['provincia']);
        $provincia=$result->getLugar();

        /////por la gestion ver que materias para la institucion curso oferta
        //si es 2016 las materias tradicionales y si es mayor las materias son diferentes
        //solo se trabajara con 2016 ya que la nueva etapa esta en arreglos//// OJO CAMBIO PNP PNP
        //////////////////////materias/////////////////////////
            if($modulo==1)//PLAN 1 B1P1
                $materias = array('2000','2002','2005','2006','2007');
            elseif($modulo==2)//PLAN 1 B1P2
                $materias = array('2000','2001','2002','2003','2004','2005','2007');
            elseif($modulo==3)//PLAN 1 B2P1
                $materias = array('2000','2001','2002','2003','2004','2007');
            elseif($modulo==4)//PLAN 1 B2P2
                $materias = array('2000','2002','2006','2007');
            elseif($modulo==5)//PLAN 2 SEM 1 (lenguaje, sociales, naturales, mate, emerg1)
                $materias = array('2008','2009','2010','2011','2012');
        
        $em->getConnection()->beginTransaction();
        try { 
            //Maestro Inscripcion
            ///1 buscar si existe ese maestro inscripcion con:cargo_tipo_id,  institucioneducativa_id, gestion_tipo_id, persona_id, periodo_tipo_id, institucioneducativa_sucursal_id
            $maestroinscripcion=$em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array(
                'cargoTipo'  => 14, 'institucioneducativa' => $ie, 'gestionTipo'=>$gestion, 'persona'=>$persona,'periodoTipo'=>5,'institucioneducativaSucursal'=>$institucioneducativa_sucursal_id
            ));
            if (!$maestroinscripcion){
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('maestro_inscripcion');");
                $query->execute();
                $maestroinscripcion = new MaestroInscripcion();
                $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->find(14));
                $maestroinscripcion->setEspecialidadTipo($em->getRepository('SieAppWebBundle:EspecialidadMaestroTipo')->findOneById(0));
                $maestroinscripcion->setEstadomaestro($em->getRepository('SieAppWebBundle:EstadomaestroTipo')->findOneById(1));
                $maestroinscripcion->setEstudiaiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById('48'));
                $maestroinscripcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById('0'));
                $maestroinscripcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById('0'));
                $maestroinscripcion->setFormaciondescripcion('');
                $maestroinscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion));
                $maestroinscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie));
                $maestroinscripcion->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(5));
                //$maestroinscripcion->setPersona($persona);
                $maestroinscripcion->setPersona($em->getRepository('SieAppWebBundle:Persona')->findOneById($persona));
                $maestroinscripcion->setRdaPlanillasId(0);
                $maestroinscripcion->setRef(0);
                $maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($institucioneducativa_sucursal_id));
                $em->persist($maestroinscripcion);
                $em->flush(); 
                ///////$id_maestroinscripcion=$maestroinscripcion->getId();
            }

            

            //Institucioneducativa_cursoInstitucioneducativaSucursal
            
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso');");
            $query->execute();
            $nuevo_curso = new InstitucioneducativaCurso();
            $nuevo_curso->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion));
            $nuevo_curso->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie));
            $nuevo_curso->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->findOneById(0));
            $nuevo_curso->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(5));//5 PERIODO TIPO MODULAR
            $nuevo_curso->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById(312));//312 NIVEL PRIMARIA PNP
            $nuevo_curso->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->findOneById($ciclo));
            $nuevo_curso->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->findOneById($grado));
            $nuevo_curso->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->findOneById('1'));//1 PARALELO A
            $nuevo_curso->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneById('0'));
            $nuevo_curso->setMultigrado(0);
            $nuevo_curso->setDesayunoEscolar(0);
            $nuevo_curso->setModalidadEnsenanza(0);
            $nuevo_curso->setIdiomaMasHabladoTipoId(48);//48 CASTELLANO
            $nuevo_curso->setIdiomaRegHabladoTipoId(0);
            $nuevo_curso->setIdiomaMenHabladoTipoId(0);
            $nuevo_curso->setPriLenEnsenanzaTipoId(48);
            $nuevo_curso->setSegLenEnsenanzaTipoId(0);
            $nuevo_curso->setTerLenEnsenanzaTipoId(1);
            $nuevo_curso->setFinDesEscolarTipoId(4);
            $nuevo_curso->setNroMaterias($nroMaterias);
            $nuevo_curso->setLugartipoId($form['departamento']);
            $nuevo_curso->setLugar($provincia);
            $nuevo_curso->setFacilitador($ci);
            $nuevo_curso->setConsolidado(1);
            $nuevo_curso->setPeriodicidadTipoId(1111100);
            $nuevo_curso->setNotaPeriodoTipo($em->getRepository('SieAppWebBundle:NotaPeriodoTipo')->find(5));//5 NOTA TIPO MODULAR
            $nuevo_curso->setFechaInicio(\DateTime::createFromFormat('d/m/Y', $fecha_inicio));
            $nuevo_curso->setFechaFin(\DateTime::createFromFormat('d/m/Y', $fecha_fin));
            $nuevo_curso->setFecharegistroCuso(new \DateTime('now'));      
            $nuevo_curso->setMaestroInscripcionAsesor($maestroinscripcion); 
            $nuevo_curso->setObs('1');    
            $em->persist($nuevo_curso);
            $em->flush(); 
            $curso_id=$nuevo_curso->getId();
            //institucion educativa datos
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_datos');");
            $query->execute();
            $nuevo_curso_datos = new InstitucioneducativaCursoDatos();
            //$nuevo_curso_datos->setLugarTipoSeccion($form['municipio']);
            $nuevo_curso_datos->setLugarTipoSeccion($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio']));
            $nuevo_curso_datos->setLocalidad($form['localidad']);
            $nuevo_curso_datos->setEsactivo(false);
            $nuevo_curso_datos->setObs($userId);
            $nuevo_curso_datos->setPlancurricularTipoId($plan);
            $nuevo_curso_datos->setInstitucioneducativaCurso($nuevo_curso);
            $em->persist($nuevo_curso_datos);
            $em->flush(); 
            
            //CURSO OFERTA
            $cant_materias = count($materias);
            foreach ($materias as $mat){
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');");
                $query->execute();
                $newArea = new InstitucioneducativaCursoOferta();
                $newArea->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($mat));
                $newArea->setInsitucioneducativaCurso($nuevo_curso);                    
               // $newArea->setMaestroInscripcion($maestroinscripcion);                    
                $em->persist($newArea);
                $em->flush();
            }

            //SI PLAN ES 2 REGISTRAR MODULO EMERGENTE CON NOMBRE VACIO en la tabla alt_moduloemergente
            if($plan==2){
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('alt_moduloemergente');");
                $query->execute();
                $newAltModuloemergente = new AltModuloEmergente();
                $newAltModuloemergente->setInstitucioneducativaCursoOferta($newArea);
                $newAltModuloemergente->setModuloEmergente("VACIO");
                $newAltModuloemergente->setFechaRegistro(new \DateTime('now'));
                $em->persist($newAltModuloemergente);
                $em->flush();
            }
                        
            $this->get('session')->getFlashBag()->add(
                    'success',
                'Curso registrado con exito.'
                );      
            $em->getConnection()->commit();
        }
        catch (Exception $e) {
                 $em->getConnection()->rollback();
                 $this->get('session')->getFlashBag()->add(
                    'error',
                    'Existio un problema al registrar el Curso.'
                    );      
                throw $e;
        }
        return $this->redirectToRoute('sie_pnp_curso_listado_editnew',array('id'=>$curso_id));
    }

    $formBuscarPersona = $this->createForm(new BuscarPersonaType(array('opcion'=>1)), null, array('action' => $this->generateUrl('sie_usuario_persona_buscar_carnet'), 'method' => 'POST',));
    

     return $this->render('SiePnpBundle:Default:registrar_curso.html.twig', array(   
            'formBuscarPersona'   => $formBuscarPersona->createView(),
            'plan' => $plan,
        ));
}

public function buscar_facilitadoresAction(Request $request){
    $filas=0;
    $ver=0;
    if($request->getMethod()=="POST") {
        $ver=1;
        $nombre=$request->get("nombre");
        $apellido1=$request->get("apellido1");
        $apellido2=$request->get("apellido2");
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        //LISTA DE NOTAS
        $query = "SELECT
                      carnet,complemento,nombre,paterno,materno,fecha_nacimiento,rda  
                    FROM 
                     persona
                    WHERE 
                      nombre like '%$nombre%' and paterno like '%$apellido1%' and materno like '%$apellido2%'
                    GROUP BY carnet,complemento,nombre,paterno,materno,fecha_nacimiento,rda 
                      ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["carnet"] = $p["carnet"];
            $datos_filas["complemento"] = $p["complemento"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["fecha_nacimiento"] = $p["fecha_nacimiento"];
            $datos_filas["rda"] = $p["rda"];
            $filas[] = $datos_filas;
        } 
        
    } 

    return $this->render('SiePnpBundle:Default:buscar_facilitadores.html.twig',array('ver'=>$ver,'filas'=>$filas));
}

public function buscar_facilitadorAction($ci,$complemento,$extranjero,$plan,Request $request){
    $em = $this->getDoctrine()->getManager();
    $db = $em->getConnection();
    $servicioPersona = $this->get('sie_app_web.persona');
    $persona = $servicioPersona->buscarPersona($ci,$complemento,$extranjero);
    //dump($persona);die;
    if($persona->type_msg === "success"){   
        $persona_id = $persona->result[0]->id; 
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($persona_id);
        $userId = $this->session->get('userId');
        $query = "
               SELECT lt.lugar as lugar
               FROM lugar_tipo lt,
               usuario_rol ur 
               WHERE ur.lugar_tipo_id=lt.id and ur.esactivo=true and ur.usuario_id=$userId";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $lugar_usuario = $p["lugar"];
        }
        $lugar_usuario=strtoupper($lugar_usuario);
        switch ($lugar_usuario) {
            case 'CHUQUISACA':{$nombre="CHUQUISACA";$lugar_tipo_id=31654;$ie=80480300;}break;
            case 'LA PAZ':{$nombre="LA PAZ";$lugar_tipo_id=31655;$ie=80730794;}break;
            case 'COCHABAMBA':{$nombre="COCHABAMBA";$lugar_tipo_id=31656;$ie=80980569;}break;
            case 'ORURO':{$nombre="ORURO";$lugar_tipo_id=31657;$ie=81230297;}break;
            case 'POTOSI':{$nombre="POTOSI";$lugar_tipo_id=31658;$ie=81480201;}break;
            case 'TARIJA':{$nombre="TARIJA";$lugar_tipo_id=31659;$ie=81730264;}break;
            case 'SANTA CRUZ':{$nombre="SANTA CRUZ";$lugar_tipo_id=31660;$ie=81981501;}break;
            case 'BENI':{$nombre="BENI";$lugar_tipo_id=31661;$ie=82230130;}break;
            case 'PANDO':{$nombre="PANDO";$lugar_tipo_id=31662;$ie=82480050;}break;
            default:
                $lugar_tipo_id=1;
                $nombre="Bolivia";
                break;
        }
        ///////////////TODOS LOS CURSOS DEL FACILITADOR DESDE EL 2015 MENOS P2B2
        $filas = array();
        $filas=$this->retornar_archivos_personaAction($persona_id,$ie);
        $form = $this->createForm(new RegistrarCursoType(array('lugar_tipo_id'=>$lugar_tipo_id,'nombre'=>$nombre,'plan'=>$plan)), null, array('action' => $this->generateUrl('sie_pnp_registrar_curso'), 'method' => 'POST',));
    //return $this->render('SiePnpBundle:Default:municipiofiltro.html.twig', array('form' => $form->createView()));

        return $this->render('SiePnpBundle:Default:registrar_curso_form.html.twig',array('persona_id'=>$persona_id,'persona'=>$persona,'filas'=>$filas,'plan'=>$plan,'form'=>$form->createView()));

    }
    else{
        echo '<div class="alert alert-danger">'.$persona->msg.'</div>';
        die;
    }
}                   


public function registrar_facilitadorAction($ci){
    $this->session->getFlashBag()->add('error', 'No se encuentra el CI : '.$ci.'. Ingrese los siquientes datos y vuelva a registrar. Si es estudiante escribir Rda = 0');
        $form = $this->createForm(new PersonaType(array('ci' => $ci)), null, array('action' => $this->generateUrl('sie_pnp_persona_insert'), 'method' => 'POST',));        
        return $this->render('SiePnpBundle:Persona:new.html.twig', array(           
            'form'   => $form->createView(),
            ));
}


public function verificar_formAction($id_curso){
    $em = $this->getDoctrine()->getManager();
    $db = $em->getConnection();
    $userId = $this->session->get('userId');
        $query = "
               SELECT lt.lugar as lugar
               FROM lugar_tipo lt,
               usuario_rol ur 
               WHERE ur.lugar_tipo_id=lt.id and ur.esactivo=true and ur.usuario_id=$userId";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $lugar_usuario = $p["lugar"];
        }
        $lugar_usuario=strtoupper($lugar_usuario);
        switch ($lugar_usuario) {
            case 'CHUQUISACA':{$nombre="CHUQUISACA";$lugar_tipo_id=31654;$ie=80480300;}break;
            case 'LA PAZ':{$nombre="LA PAZ";$lugar_tipo_id=31655;$ie=80730794;}break;
            case 'COCHABAMBA':{$nombre="COCHABAMBA";$lugar_tipo_id=31656;$ie=80980569;}break;
            case 'ORURO':{$nombre="ORURO";$lugar_tipo_id=31657;$ie=81230297;}break;
            case 'POTOSI':{$nombre="POTOSI";$lugar_tipo_id=31658;$ie=81480201;}break;
            case 'TARIJA':{$nombre="TARIJA";$lugar_tipo_id=31659;$ie=81730264;}break;
            case 'SANTA CRUZ':{$nombre="SANTA CRUZ";$lugar_tipo_id=31660;$ie=81981501;}break;
            case 'BENI':{$nombre="BENI";$lugar_tipo_id=31661;$ie=82230130;}break;
            case 'PANDO':{$nombre="PANDO";$lugar_tipo_id=31662;$ie=82480050;}break;
            default:
                $lugar_tipo_id=1;
                $nombre="Bolivia";
                break;
        }    
    $institucion_educativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findOneByInstitucioneducativaCurso($id_curso);
    $plan=$institucion_educativa->getPlancurricularTipoId();
    if($institucion_educativa){
        $form = $this->createForm(new RegistrarCursoType(array('lugar_tipo_id'=>$lugar_tipo_id,'nombre'=>$nombre,'plan'=>$plan)), null, array('action' => $this->generateUrl('sie_pnp_crear_curso_automatico'), 'method' => 'POST',));
        ////que si esta en la tabla
        return $this->render('SiePnpBundle:Default:registrar_curso_automatico2.html.twig',array('id_curso'=>$id_curso,'op'=>1,'plan'=>$plan,'form'=>$form->createView()));
    } else{
        $form = $this->createForm(new RegistrarCursoType(array('lugar_tipo_id'=>$lugar_tipo_id,'nombre'=>$nombre,'plan'=>$plan)), null, array('action' => $this->generateUrl('sie_pnp_crear_curso_automatico'), 'method' => 'POST',));
        ////que no esta en la tabla
        return $this->render('SiePnpBundle:Default:registrar_curso_automatico2.html.twig',array('id_curso'=>$id_curso,'op'=>2,'plan'=>$plan,'form'=>$form->createView()));
    }
}

 public function buscar_historial_estudiante_ciAction(Request $request) {
        $sesion = $request->getSession();
        $form=$request->get("form_est");
        $ci=$form['ci'];
        $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Estudiante');
        $query = $repository->createQueryBuilder('e')
                ->where('e.carnetIdentidad =:carnetIdentidad')
                ->orwhere(('e.codigoRude =:carnetIdentidad'))
                ->setParameter('carnetIdentidad',  $ci)
                ->orderBy('e.paterno, e.materno, e.nombre', 'ASC')
                ->getQuery();
        $entities = $query->getResult();
        if (!$entities) {

            $message = "Busqueda no encontrada...";
            $this->addFlash('warningstudent', $message);
            return $this->redirectToRoute('sie_pnp_buscar_historial_estudiante');
        }

        $message = 'Resultado de la busqueda...';
        $this->addFlash('successstudent', $message);

        return $this->render('SiePnpBundle:Default:resultstudent.html.twig',array('entities'=>$entities));
    }



 /**
     * Lists all Estudiante entities.
     *
     */
    public function buscar_historial_estudianteAction(Request $request) {
        // data es un array con claves 'name', 'email', y 'message'
        return $this->render('SiePnpBundle:Default:searchstudent.html.twig',array('form'=>$this->createSearchForm()->createView()));
}

    /**
     * Creates a form to search the users of student selected
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm() {
        $estudiante = new Estudiante();
        $form = $this->createFormBuilder($estudiante)
                ->setAction($this->generateUrl('sie_pnp_buscar_historial_estudiante_result'))
                ->add('paterno', 'text', array('required' => false, 'invalid_message' => 'Campo obligatorio', 'attr' => array('pattern' => '[a-zñ A-ZÑ]{1,25}', 'style' => 'text-transform:uppercase', 'class' => 'form-control')))
                ->add('materno', 'text', array('required' => false, 'invalid_message' => 'Campo obligatorio', 'attr' => array('pattern' => '[a-zñ A-ZÑ]{1,25}', 'style' => 'text-transform:uppercase', 'class' => 'form-control')))
                ->add('nombre', 'text', array('required' => false, 'invalid_message' => 'Campor obligatorio', 'attr' => array('pattern' => '[a-zñ A-ZÑ]{1,25}', 'style' => 'text-transform:uppercase', 'class' => 'form-control')))
                ->add('lookfor', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $form;
    }

    /**
     * Find the student.
     *
     */
    public function buscar_historial_estudiante_resultAction(Request $request) {
        $sesion = $request->getSession();
        $form = $request->get('form');

        $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Estudiante');

        $query = $repository->createQueryBuilder('e')
                ->where('e.paterno like :paterno')
                ->andWhere('upper(e.materno) like :materno')
                ->andWhere('upper(e.nombre) like :nombre')
                ->setParameter('paterno', '%' . mb_strtoupper($form['paterno'], 'utf8') . '%')
                ->setParameter('materno', '%' . mb_strtoupper($form['materno'], 'utf8') . '%')
                ->setParameter('nombre', '%' . mb_strtoupper($form['nombre'], 'utf8') . '%')
                ->orderBy('e.paterno, e.materno, e.nombre', 'ASC')
                ->getQuery();
        $entities = $query->getResult();
        if (!$entities) {

            $message = "Busqueda no encontrada...";
            $this->addFlash('warningstudent', $message);
            return $this->redirectToRoute('sie_pnp_buscar_historial_estudiante');
        }

        $message = 'Resultado de la busqueda...';
        $this->addFlash('successstudent', $message);

        return $this->render('SiePnpBundle:Default:resultstudent.html.twig',array('entities'=>$entities));
    }

    public function buscar_historial_estudiante_result_listAction(Request $request, $idStudent) {
        $em = $this->getDoctrine()->getManager();

        $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($idStudent);
       
        $rec_sab = $this->getDoctrine()->getRepository('SieAppWebBundle:PnpReconocimientoSaberes')->findOneBy(array(
            'estudiante'  => $idStudent, 'homologado' => true
        ));

        if($rec_sab)$res_sab_ex=1;else $res_sab_ex=0;

        //$objInscriptions = $em->getRepository('SieAppWebBundle:Estudiante')->getHistoryInscription($idStudent);
        $db = $em->getConnection();
        $query = "select gt.gestion as gestion,i.id as sie,i.institucioneducativa,n.nivel,g.grado,
                p.paralelo,t.turno,m.estadomatricula,iec.ciclo_tipo_id as bloque,iec.grado_tipo_id as parte,ei.id as estudiante_inscripcion_id,iec.id as institucioneducativa_curso_id
                from institucioneducativa i,institucioneducativa_curso iec, estudiante e, estudiante_inscripcion ei, gestion_tipo gt,nivel_tipo n,grado_tipo g,paralelo_tipo p,turno_tipo t,estadomatricula_tipo m
            where i.id=iec.institucioneducativa_id and ei.institucioneducativa_curso_id=iec.id and 
            ei.estudiante_id=e.id and gt.id=iec.gestion_tipo_id and n.id=iec.nivel_tipo_id and
            g.id=iec.grado_tipo_id and p.id=iec.paralelo_tipo_id and t.id=iec.turno_tipo_id and
            m.id=ei.estadomatricula_tipo_id and e.id='$idStudent'";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $objInscriptions = array();
        $datos_filas = array();
        $cant_notas=0;
        foreach ($po as $p) {
            $cant_notas++;
            $datos_filas["estudiante_inscripcion_id"]=$p["estudiante_inscripcion_id"];
            $datos_filas["gestion"] = $p["gestion"];
            $datos_filas["sie"] = $p["sie"];
            $datos_filas["institucioneducativa"] = $p["institucioneducativa"];
            $datos_filas["nivel"] = $p["nivel"];
            $datos_filas["grado"] = $p["grado   "];
            $datos_filas["paralelo"] = $p["paralelo"];
            $datos_filas["turno"] = $p["turno"];
            $datos_filas["estadoMatricula"] = $p["estadomatricula"];
            $datos_filas["bloque"] = $p["bloque"];
            $datos_filas["parte"] = $p["parte"];
            $datos_filas["institucioneducativa_curso_id"] = $p["institucioneducativa_curso_id"];
            $objInscriptions[] = $datos_filas;
        } 

        return $this->render('SiePnpBundle:Default:buscar_historial_estudiante_result_list.html.twig',array(
            'datastudent'=>$student,
            'dataInscription' => $objInscriptions,
            'res_sab_ex'=>$res_sab_ex));
        die;
    }


public function crear_curso_automaticoAction(Request $request){
    if($request->getMethod()=="POST") {
        $op=$request->get("op");
        $plan=$request->get("plan");
        $id_curso=$request->get("id_curso");
        $form = $request->get('sie_pnp_registrar_curso_nuevo');
        $fecha_inicio= $form['fecha_inicio'];
        $fecha_fin= $form['fecha_fin'];
        $userId = $this->session->get('userId');
        $em = $this->getDoctrine()->getManager(); 
        ///datos de la institucionEducativaCurso: id del maestro_inscripcion,bloque,parte,ci
        $institucion_educativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneById($id_curso);
        $id_maestro_inscripcion=$institucion_educativa->getMaestroInscripcionAsesor()->getId();
        $bloque_actual=$institucion_educativa->getCicloTipo()->getId();
        $parte_actual=$institucion_educativa->getGradoTipo()->getId();
        $ci=$institucion_educativa->getFacilitador();
        $ie=$institucion_educativa->getInstitucioneducativa()->getId();
        //buscamos el curso siguiente dependendiendo del bloque y parte numeor de materias y el modulo dependiendo de la gestion
        $gestion= substr($fecha_fin,-4);
        //plan antiguo
        if($bloque_actual==1 and  $parte_actual==1){$bloque_nuevo=1;$parte_nuevo=2;$nroMaterias=6;$modulo=2;}
        elseif($bloque_actual==1 and  $parte_actual==2){$bloque_nuevo=2;$parte_nuevo=1;$nroMaterias=5;$modulo=3;}
        elseif($bloque_actual==2 and  $parte_actual==1){$bloque_nuevo=2;$parte_nuevo=2;$nroMaterias=3;$modulo=4;}
        //plan nuevo
        elseif($parte_actual==14){$bloque_nuevo=34;$parte_nuevo=15;$nroMaterias=5;$modulo=12;}
        elseif($parte_actual==15){$bloque_nuevo=35;$parte_nuevo=16;$nroMaterias=5;$modulo=13;}
        elseif($parte_actual==16){$bloque_nuevo=35;$parte_nuevo=17;$nroMaterias=3;$modulo=14;}
        else {$bloque_nuevo="";$parte_nuevo="";}
        //Sacar la fecha, si fecha menor o igual a 2019 sacamos las materias
        $gestion= substr($fecha_fin,-4);
        if($modulo==1)
            $materias = array('2000','2002','2005','2006','2007');
        elseif($modulo==2)
            $materias = array('2000','2001','2002','2003','2004','2005','2007');
        elseif($modulo==3)
            $materias = array('2000','2001','2002','2003','2004','2007');
        elseif($modulo==4)
            $materias = array('2000','2002','2006','2007');
        elseif($modulo == 12 or $modulo == 13 or $modulo == 14 )
            $materias = array('2008','2009','2010','2011','2012');

        ///datos de la tabla maestro_inscripcion: institucion_educativa_id, persona_id
        $maestro_inscripcion=$em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($id_maestro_inscripcion);
        $ie=$maestro_inscripcion->getInstitucioneducativa()->getId();
        $id_persona=$maestro_inscripcion->getPersona()->getId();

        //echo $id_curso." ".$id_maestro_inscripcion." ".$ie." ".$id_persona." ".$ci;die;   
        if($op == 1){
            //Esta en la Tabla institucion educativa curso datos, solo es sacar los datos
            //id del departamento
            $departamento=$institucion_educativa->getLugarTipoId();
            //nombre de la provincia
            $provincia_nombre=$institucion_educativa->getLugar();
            //de la tabla institucion educativa datos: municipio y localidad
            $ie_datos=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->findOneByinstitucioneducativaCurso($id_curso);
                $municipio=$ie_datos->getLugarTipoSeccion()->getId();
                $localidad=$ie_datos->getLocalidad();
            //echo "op=".$op." id_curso=".$id_curso." fec_ini= ".$fecha_inicio." fec_fin= ".$fecha_fin." ".$municipio." ".$localidad;die;
        }
        else{
            //No esta en la tabla, se debe guardar estos datos en la tabla instticuioneducativa_curso_datos
            $departamento=$form['departamento'];
            $provincia=$form['provincia'];
            $municipio=$form['municipio'];
            $localidad=$form['localidad'];
            $result=$em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($provincia);
                $provincia_nombre=$result->getLugar();
            //echo "op=".$op." id_curso=".$id_curso." fec_ini= ".$fecha_inicio." fec_fin= ".$fecha_fin." depa= ".$departamento." prov= ".$provincia." mun= ".$municipio." loc= ".$localidad;die;
        }
        //municipio pero en nombre para la tabla estudiante_inscripcion y nombre del facilitador
        $result=$em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($municipio);
            $municipio_nombre=$result->getLugar();

         //obtenemos el nombre del facilitador
            $db = $em->getConnection();
            $query = "SELECT 
              p.paterno, p.materno, p.nombre
            FROM 
              persona p
              INNER JOIN maestro_inscripcion as mi ON p.id=mi.persona_id 
              INNER JOIN institucioneducativa_curso as ic ON mi.id=ic.maestro_inscripcion_id_asesor
            WHERE
              ic.id = ".$id_curso;

            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $po = $stmt->fetchAll();
            $filas = array();
            $datos_filas = array();
            $existe_estudiante=0;
            foreach ($po as $p) {
                $paterno=$p["paterno"];
                $materno=$p["materno"];
                $nombre=$p["nombre"];
            }
            $facilitador_nombre="$nombre $paterno $materno";

        ////////////JURISDICCION ///
        $le_jurisdiccion_g=$em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ie);
        $le_jurisdiccion_g=$le_jurisdiccion_g->getLeJuridicciongeografica()->getId();

        $institucioneducativa_sucursal_id=$em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array(
            'institucioneducativa'  => $ie,
            'gestionTipo' => $gestion,
            'leJuridicciongeografica'=> $le_jurisdiccion_g,
            'periodoTipoId'=>5));

        if($institucioneducativa_sucursal_id)
            $institucioneducativa_sucursal_id=$institucioneducativa_sucursal_id->getId();
        else{
             $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal');");
                $query->execute();
                $institucioneducativa_sucursal_id = new InstitucioneducativaSucursal();
                $institucioneducativa_sucursal_id->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find(0));
                $institucioneducativa_sucursal_id->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie));
                $institucioneducativa_sucursal_id->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion));
                $institucioneducativa_sucursal_id->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($le_jurisdiccion_g));  
                $institucioneducativa_sucursal_id->setCodCerradaId(10);       
                $institucioneducativa_sucursal_id->setPeriodoTipoId(5);
                $institucioneducativa_sucursal_id->setNombreSubcea("PNP");       

                $em->persist($institucioneducativa_sucursal_id);
                $em->flush(); 
        }
        //$maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($institucioneducativa_sucursal_id));
        /////////////////////////////

        $em->getConnection()->beginTransaction();
        try {
            //Maestro Inscripcion
                // automatico
            ///1 buscar si existe ese maestro inscripcion con:cargo_tipo_id,  institucioneducativa_id, gestion_tipo_id, persona_id, periodo_tipo_id, institucioneducativa_sucursal_id
            $maestroinscripcion=$em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array(
                'cargoTipo'  => 14, 'institucioneducativa' => $ie, 'gestionTipo'=>$gestion, 'persona'=>$id_persona,'periodoTipo'=>5,'institucioneducativaSucursal'=>$institucioneducativa_sucursal_id
            ));
            if (!$maestroinscripcion){
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('maestro_inscripcion');");
                $query->execute();
                $maestroinscripcion = new MaestroInscripcion();
                $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->find(14));
                $maestroinscripcion->setEspecialidadTipo($em->getRepository('SieAppWebBundle:EspecialidadMaestroTipo')->findOneById(0));
                $maestroinscripcion->setEstadomaestro($em->getRepository('SieAppWebBundle:EstadomaestroTipo')->findOneById(1));
                $maestroinscripcion->setEstudiaiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById('48'));
                $maestroinscripcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById('0'));
                $maestroinscripcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById('0'));
                $maestroinscripcion->setFormaciondescripcion('');
                $maestroinscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion));
                $maestroinscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie));
                $maestroinscripcion->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(5));
                $maestroinscripcion->setPersona($em->getRepository('SieAppWebBundle:Persona')->findOneById($id_persona));
                //$maestroinscripcion->setPersona($persona);
                $maestroinscripcion->setRdaPlanillasId(0);
                $maestroinscripcion->setRef(0);
                $maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($institucioneducativa_sucursal_id));
                $em->persist($maestroinscripcion);
                $em->flush(); 
                ///////$id_maestroinscripcion=$maestroinscripcion->getId();
            }
           

            //Institucioneducativa_curso
            
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso');");
            $query->execute();
            $nuevo_curso = new InstitucioneducativaCurso();
            $nuevo_curso->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion));
            $nuevo_curso->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie));
            $nuevo_curso->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->findOneById(0));
            $nuevo_curso->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(5));//5 PERIODO TIPO MODULAR
            $nuevo_curso->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById(312));//312 NIVEL PRIMARIA PNP
            $nuevo_curso->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->findOneById($bloque_nuevo));
            $nuevo_curso->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->findOneById($parte_nuevo));
            $nuevo_curso->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->findOneById('1'));//1 PARALELO A
            $nuevo_curso->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneById('0'));
            $nuevo_curso->setMultigrado(0);
            $nuevo_curso->setDesayunoEscolar(0);
            $nuevo_curso->setModalidadEnsenanza(0);
            $nuevo_curso->setIdiomaMasHabladoTipoId(48);//48 CASTELLANO
            $nuevo_curso->setIdiomaRegHabladoTipoId(0);
            $nuevo_curso->setIdiomaMenHabladoTipoId(0);
            $nuevo_curso->setPriLenEnsenanzaTipoId(48);
            $nuevo_curso->setSegLenEnsenanzaTipoId(0);
            $nuevo_curso->setTerLenEnsenanzaTipoId(1);
            $nuevo_curso->setFinDesEscolarTipoId(4);
            $nuevo_curso->setNroMaterias($nroMaterias);
            $nuevo_curso->setLugartipoId($departamento);
            $nuevo_curso->setLugar($provincia_nombre);
            $nuevo_curso->setFacilitador($ci);
            $nuevo_curso->setConsolidado(1);
            $nuevo_curso->setPeriodicidadTipoId(1111100);
            $nuevo_curso->setNotaPeriodoTipo($em->getRepository('SieAppWebBundle:NotaPeriodoTipo')->find(5));//5 NOTA TIPO MODULAR
            $nuevo_curso->setFechaInicio(\DateTime::createFromFormat('d/m/Y', $fecha_inicio));
            $nuevo_curso->setFechaFin(\DateTime::createFromFormat('d/m/Y', $fecha_fin));            
            $nuevo_curso->setMaestroInscripcionAsesor($maestroinscripcion);
            $nuevo_curso->setObs('1');    
            $em->persist($nuevo_curso);
            $em->flush(); 
            $curso_new_id=$nuevo_curso->getId();
            

            //institucion educativa datos
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_datos');");
            $query->execute();
            $nuevo_curso_datos = new InstitucioneducativaCursoDatos();
            //$nuevo_curso_datos->setLugarTipoSeccion($form['municipio']);
            $nuevo_curso_datos->setLugarTipoSeccion($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($municipio));
            $nuevo_curso_datos->setLocalidad($localidad);
            $nuevo_curso_datos->setEsactivo(false);
            $nuevo_curso_datos->setObs($userId);
            $nuevo_curso_datos->setPlancurricularTipoId($plan);
            $nuevo_curso_datos->setInstitucioneducativaCurso($nuevo_curso);
            $em->persist($nuevo_curso_datos);
            $em->flush(); 
            ////SI NO EXISTE EN LA TABLA GUARDAR EN LA TABLA EL CURSO ANTERIOR
            if($op==0){
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_datos');");
                $query->execute();
                $nuevo_curso_datos = new InstitucioneducativaCursoDatos();
            //$nuevo_curso_datos->setLugarTipoSeccion($form['municipio']);
                $nuevo_curso_datos->setLugarTipoSeccion($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($municipio));
                $nuevo_curso_datos->setLocalidad($localidad);
                $nuevo_curso_datos->setEsactivo(true);
                $nuevo_curso_datos->setInstitucioneducativaCurso($id_curso);
                $nuevo_curso_datos->setPlancurricularTipoId(1);
                $em->persist($nuevo_curso_datos);
                $em->flush();
            }
            
            //CURSO OFERTA
            $cant_materias = count($materias);
            
            foreach ($materias as $mat){
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');");
                $query->execute();
                $newArea = new InstitucioneducativaCursoOferta();
                $newArea->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($mat));
                $newArea->setInsitucioneducativaCurso($nuevo_curso);                    
               // $newArea->setMaestroInscripcion($maestroinscripcion);                    
                $em->persist($newArea);
                $em->flush(); 
            }
            //SI PLAN ES 2 REGISTRAR MODULO EMERGENTE CON NOMBRE VACIO en la tabla alt_moduloemergente
            if($plan==2){
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('alt_moduloemergente');");
                $query->execute();
                $newAltModuloemergente = new AltModuloEmergente();
                $newAltModuloemergente->setInstitucioneducativaCursoOferta($newArea);
                $newAltModuloemergente->setModuloEmergente("VACIO");
                $newAltModuloemergente->setFechaRegistro(new \DateTime('now'));
                $em->persist($newAltModuloemergente);
                $em->flush();
            }

            //////////////////////BUSCAMOS A TODOS LOS ESTUDIANTES INSCRITOS EN EL CURSO ANTERIOR PARA REGISTRAR EN EL NUEVO CURSO
            $estado=61; //62 En Clase 
            $estudiantes_inscripciones=$em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findByinstitucioneducativaCurso($id_curso);
            foreach ($estudiantes_inscripciones as $estudiante_inscripcion) {
                $aprobo=1;
                $id_estudiante=$estudiante_inscripcion->getEstudiante()->getId();
                $id_estudiante_inscripcion=$estudiante_inscripcion->getId();
                $matricula_estado_id=$estudiante_inscripcion->getEstadomatriculaTipo()->getId();
                if($matricula_estado_id==61)$aprobo=0;//ctv
                // id de la tabla estudiante_asignatura
                
                if($aprobo==1){//Aprobo por esa razon se lo registra al estudiante en estudiantes inscripcion
                    //ESTUDIANTE INSCRIPCION
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
                    $query->execute();
                    $inscripcion = new EstudianteInscripcion();
                    $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->    findOneById($estado));
                    $inscripcion->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->findOneById($id_estudiante));
                    $inscripcion->setNumMatricula(0);
                    $inscripcion->setObservacionId(0);
                    $inscripcion->setObservacion(0);
                    $inscripcion->setOperativoId(1);
                    $inscripcion->setFechaRegistro(new \DateTime('now'));
                    $inscripcion->setApreciacionFinal('');
                    $inscripcion->setLugar($municipio_nombre);
                    $inscripcion->setLugarcurso($localidad);
                    $inscripcion->setFacilitadorcurso($facilitador_nombre);
                    $inscripcion->setInstitucioneducativaCurso($nuevo_curso);
                    $inscripcion->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById('66'));
                    $em->persist($inscripcion);
                    $em->flush();

                    /////////////OCUPACION Y DISCAPACDAD SI EL PLAN 2
                    if ($plan==2){
                        //sacamos los datos anteriores para registrar en el  nuevo
                        $result=$em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAlternativa')->findOneByestudianteInscripcion($id_estudiante_inscripcion);
                        $discapacidad=$result->getSeccionivDiscapacitadTipo()->getId();
                        $ocupacion=$result->getSeccionvOtroTrabajo();
                        $lugar_llenar=$result->getLugar();
                        
                        //guardamos los datos
                        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_socioeconomico_alternativa');")->execute();
                        $estinscsocalt = new EstudianteInscripcionSocioeconomicoAlternativa();
                        $estinscsocalt->setEstudianteInscripcion($inscripcion);
                        $estinscsocalt->setSeccionivDiscapacitadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->findOneById($discapacidad));
                        $estinscsocalt->setSeccionvOtroTrabajo($ocupacion);
                        $estinscsocalt->setLugar($lugar_llenar);
                        $estinscsocalt->setFechaRegistro(new \DateTime('now'));
                        $estinscsocalt->setFecha(new \DateTime('now'));
                        $em->persist($estinscsocalt);
                        $em->flush();
                    }
                ///////////////////FIN

                    /////buscar las id en la tabla institucion educativa curso oferta para las materias para luego registrar en estudiante asignatura 
                    if ($plan==1){
                        /////buscar las id en la tabla institucion educativa curso oferta para las materias para luego registrar en estudiante asignatura 
                        $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findByinsitucioneducativaCurso($nuevo_curso);
                        foreach ($result as $results) {
                            ///obtenemos el id del cusro oferta y su registro como symfony acepta
                            $institucioneducativa_curso_oferta_id=$results->getId();
                            ///////registramos estudiante asignatura

                            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');")->execute();
                            $estAsignaturaNew = new EstudianteAsignatura();
                            $estAsignaturaNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                            $estAsignaturaNew->setEstudianteInscripcion($inscripcion);
                            $estAsignaturaNew->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($institucioneducativa_curso_oferta_id));
                            $em->persist($estAsignaturaNew);
                            $em->flush();

                            $materia_id=$results->getAsignaturaTipo()->getId();
                            //////registramos 6 notas por materia (12:Trabajo Grupal,13:Trabajo Individual,14:Prueba Final,15:Asistencia,16:Nota Final)
                            if($materia_id!=2007) //2007 prueba final solo debe tener una nota promedio final
                            {
                                for($i=12;$i<=16;$i++){
                                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();
                                    $registro_nota = new EstudianteNota();
                                    $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($i));
                                    $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                                    $registro_nota->setNotaCuantitativa('0');
                                    $registro_nota->setUsuarioId($this->session->get('userId'));
                                    $registro_nota->setFechaRegistro(new \DateTime('now'));                       
                                    $em->persist($registro_nota);
                                    $em->flush();       
                                }
                            }
                            else{
                                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();
                                $registro_nota = new EstudianteNota();
                                $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(17));
                                $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                                $registro_nota->setNotaCuantitativa('0');
                                $registro_nota->setUsuarioId($this->session->get('userId'));
                                $registro_nota->setFechaRegistro(new \DateTime('now'));                       
                                $em->persist($registro_nota);
                                $em->flush();  
                            }
                        }
                    }
                    elseif($plan==2){
                        /////buscar las id en la tabla institucion educativa curso oferta para las materias para luego registrar en estudiante asignatura 
                        $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findByinsitucioneducativaCurso($nuevo_curso);
                        foreach ($result as $results) {
                        ///obtenemos el id del cusro oferta y su registro como symfony acepta
                            $institucioneducativa_curso_oferta_id=$results->getId();
                            ///////registramos estudiante asignatura

                            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');")->execute();
                            $estAsignaturaNew = new EstudianteAsignatura();
                            $estAsignaturaNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                            $estAsignaturaNew->setEstudianteInscripcion($inscripcion);
                            $estAsignaturaNew->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($institucioneducativa_curso_oferta_id));
                            $em->persist($estAsignaturaNew);
                            $em->flush();

                            $materia_id=$results->getAsignaturaTipo()->getId();
                            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();
                            $registro_nota = new EstudianteNota();
                            $registro_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(16));//NOTA FINAL
                            $registro_nota->setEstudianteAsignatura($estAsignaturaNew);
                            $registro_nota->setNotaCuantitativa('0');
                            $registro_nota->setUsuarioId($this->session->get('userId'));
                            $registro_nota->setFechaRegistro(new \DateTime('now'));                       
                            $em->persist($registro_nota);
                            $em->flush();
                        }
                        //nota cualitativa
                        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota_cualitativa');")->execute();
                        $registro_nota_cualitativa = new EstudianteNotaCualitativa();
                        $registro_nota_cualitativa->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(16));//NOTA FINAL 
                        $registro_nota_cualitativa->setEstudianteInscripcion($inscripcion);
                        $registro_nota_cualitativa->setEstudianteInscripcion($inscripcion);
                        $registro_nota_cualitativa->setNotaCuantitativa(0);
                        $registro_nota_cualitativa->setUsuarioId($this->session->get('userId'));
                        $registro_nota_cualitativa->setFechaRegistro(new \DateTime('now'));
                        $em->persist($registro_nota_cualitativa);
                        $em->flush();

                    }
                }
            }

            $this->get('session')->getFlashBag()->add(
                    'notice',
                'Curso creado con exito.'
                );      
            $em->getConnection()->commit();
        }
        catch (Exception $e) {
                 $em->getConnection()->rollback();
                 $this->get('session')->getFlashBag()->add(
                    'error',
                    'Existio un problema al registrar el Curso.'
                    );      
                throw $e;
        }
        return $this->redirectToRoute('sie_pnp_curso_listado_editnew',array('id'=>$curso_new_id));

    }   
}

public function abrir_cursoAction(Request $request){
    if($request->getMethod()=="POST") {
        $cursos=$request->get("cursos");
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "SELECT id,obs,esactivo
                  FROM institucioneducativa_curso_datos 
                  WHERE institucioneducativa_curso_id 
                  IN ($cursos)";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $em->getConnection()->beginTransaction();
        try{
            foreach ($po as $p) {
                $id = $p["id"];
                $obs = $p["obs"];
                $esactivo = $p["esactivo"];
                $obsnew = explode("-", $obs);   
                $obsnew= $obsnew[0];
                $result=$em->getRepository('SieAppWebBundle:InstitucioneducativaCursoDatos')->find($id);
                $result->setObs($obsnew);
                $result->setEsactivo(false);
                $em->flush();     
                }
                 $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Curso(s) Abierto(s) con Exito!.'
                    ); 
                $em->getConnection()->commit();
            }
            catch (Exception $e) {
                $em->getConnection()->rollBack();
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'Existio un problema al Editar las notas.'
                    );      
                throw $e;
                    return $this->render('SiePnpBundle:Default:abrir_curso.html.twig');
            }
    }

     return $this->render('SiePnpBundle:Default:abrir_curso.html.twig');
}

public function cambiar_facilitadorAction(Request $request){
    if($request->getMethod()=="POST") {
        $curso_id=$request->get("curso_id");
        $facilitador_id=$request->get("facilitador_id");
        $facilitador_ci=$request->get("facilitador_ci");
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $em->getConnection()->beginTransaction();
        try{ 
            $institucion_educativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneById($curso_id);
            $ie=$institucion_educativa->getInstitucioneducativa()->getId();
            $id_maestro_inscripcion=$institucion_educativa->getMaestroInscripcionAsesor()->getId();
            $fecha_fin=$institucion_educativa->getFechaFin();
            $gestion_g=$fecha_fin->format("Y");

            $le_jurisdiccion_g=$em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ie);
            $le_jurisdiccion_g=$le_jurisdiccion_g->getLeJuridicciongeografica()->getId();

            $institucioneducativa_sucursal_id=$em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array(
                'institucioneducativa'  => $ie,
                'gestionTipo' => $gestion_g,
                'leJuridicciongeografica'=> $le_jurisdiccion_g,
                'periodoTipoId'=>5));

        if($institucioneducativa_sucursal_id)
            $institucioneducativa_sucursal_id=$institucioneducativa_sucursal_id->getId();
        else{
             $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal');");
                $query->execute();
                $institucioneducativa_sucursal_id = new InstitucioneducativaSucursal();
                $institucioneducativa_sucursal_id->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find(0));
                $institucioneducativa_sucursal_id->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie));
                $institucioneducativa_sucursal_id->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion_g));
                $institucioneducativa_sucursal_id->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($le_jurisdiccion_g));  
                $institucioneducativa_sucursal_id->setCodCerradaId(10);       
                $institucioneducativa_sucursal_id->setPeriodoTipoId(5);
                $institucioneducativa_sucursal_id->setNombreSubcea("PNP");       

                $em->persist($institucioneducativa_sucursal_id);
                $em->flush(); 
        }
        //$maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($institucioneducativa_sucursal_id));

        // buscar si existe ese maestro inscipcion

            $persona=$em->getRepository('SieAppWebBundle:Persona')->find($facilitador_id);
            
             $maestroinscripcion=$em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array(
                'cargoTipo'  => 14, 'institucioneducativa' => $ie, 'gestionTipo'=>$gestion_g, 'persona'=>$persona,'periodoTipo'=>5,'institucioneducativaSucursal'=>$institucioneducativa_sucursal_id
            ));
            if (!$maestroinscripcion){
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('maestro_inscripcion');");
                $query->execute();
                $maestroinscripcion = new MaestroInscripcion();
                $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->find(14));
                $maestroinscripcion->setEspecialidadTipo($em->getRepository('SieAppWebBundle:EspecialidadMaestroTipo')->findOneById(0));
                $maestroinscripcion->setEstadomaestro($em->getRepository('SieAppWebBundle:EstadomaestroTipo')->findOneById(1));
                $maestroinscripcion->setEstudiaiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById('48'));
                $maestroinscripcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById('0'));
                $maestroinscripcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById('0'));
                $maestroinscripcion->setFormaciondescripcion('');
                $maestroinscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion_g));
                $maestroinscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie));
                $maestroinscripcion->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(5));
                $maestroinscripcion->setPersona($em->getRepository('SieAppWebBundle:Persona')->findOneById($persona));
                //$maestroinscripcion->setPersona($persona);
                $maestroinscripcion->setRdaPlanillasId(0);
                $maestroinscripcion->setRef(0);
                $maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($institucioneducativa_sucursal_id));
                $em->persist($maestroinscripcion);
                $em->flush(); 
                ///////$id_maestroinscripcion=$maestroinscripcion->getId();
            }

            
            $institucion_educativa->setFacilitador($facilitador_ci);
            $institucion_educativa->setMaestroInscripcionAsesor($maestroinscripcion);
            $em->flush();     
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Cambio de facilitador a curso correcto!!!'
                ); 
                $em->getConnection()->commit();
        }
        catch (Exception $e) {
            $em->getConnection()->rollBack();
            $this->get('session')->getFlashBag()->add(
                'error',
                'Existio un problema al cambiar de facilitador al curso'
                );      
            throw $e;
        }
        return $this->render('SiePnpBundle:Default:cambiar_facilitador.html.twig');
    }
    return $this->render('SiePnpBundle:Default:cambiar_facilitador.html.twig');
}

public function cambiar_facilitador_encontradoAction(Request $request,$ci,$complemento,$curso_id){
    $em = $this->getDoctrine()->getManager();
    $db = $em->getConnection();
    //BUSCAR USUARIO Y REVISAR SU DEPARTAMENTO PARA MIRAR SOLO SU DEPARTAMNETO
    $userId = $this->session->get('userId');
     $query = "
           SELECT lt.lugar as lugar
           FROM lugar_tipo lt,
           usuario_rol ur 
           WHERE ur.lugar_tipo_id=lt.id and ur.esactivo=true and ur.usuario_id=$userId";
    $stmt = $db->prepare($query);
    $params = array();
    $stmt->execute($params);
    $po = $stmt->fetchAll();
    $filas = array();
    $datos_filas = array();
    foreach ($po as $p) {
        $lugar_usuario = $p["lugar"];
    }

    $lugar_usuario=strtoupper($lugar_usuario);
   
    switch ($lugar_usuario) {
        case 'CHUQUISACA': $id_dep=80480300; break;
        case 'LA PAZ': $id_dep=80730794; break;
        case 'COCHABAMBA': $id_dep=80980569; break;
        case 'ORURO': $id_dep=81230297; break;
        case 'POTOSI': $id_dep=81480201; break;
        case 'TARIJA': $id_dep=81730264; break;
        case 'SANTA CRUZ': $id_dep=81981501; break;
        case 'BENI': $id_dep=82230130; break;
        case 'PANDO': $id_dep=82480050; break;
        default: $id_dep=0; break;
    }
    //BUSCAR CURSO
    if($id_dep==0)$where="";else $where="and ic.institucioneducativa_id=$id_dep";
    $curso_existe=0;

    $query = "SELECT ic.id,lt3.lugar as depto,lt2.lugar as provincia,lt1.lugar as municipio,
    icd.localidad,p.carnet,p.complemento,p.nombre,p.paterno,p.materno,
    ic.fecha_inicio,ic.fecha_fin,ic.ciclo_tipo_id as bloque, 
    ic.grado_tipo_id as parte,icd.esactivo,icd.plancurricular_tipo_id,ct.ciclo as nciclo,gt.grado as ngrado
    FROM institucioneducativa_curso ic
    join institucioneducativa_curso_datos icd on icd.institucioneducativa_curso_id=ic.id
    join maestro_inscripcion mi on ic.maestro_inscripcion_id_asesor=mi.id
    join persona p on p.id=mi.persona_id
    join lugar_tipo lt1 on lt1.id=icd.lugar_tipo_id_seccion
    join lugar_tipo lt2 on lt2.id=lt1.lugar_tipo_id
    join lugar_tipo lt3 on lt3.id=lt2.lugar_tipo_id
    INNER JOIN ciclo_tipo ct ON ic.ciclo_tipo_id=ct.id
    INNER JOIN grado_tipo gt ON ic.grado_tipo_id=gt.id
    where icd.institucioneducativa_curso_id=$curso_id $where ";
    $stmt = $db->prepare($query);
    $params = array();
    $stmt->execute($params);
    $po = $stmt->fetchAll();
    $curso = array();
    foreach ($po as $p) {
        $curso["carnet"] = $p["carnet"];
        $curso["nciclo"] = $p["nciclo"];
        $curso["ngrado"] = $p["ngrado"];
        $curso["plan"] = $p["plancurricular_tipo_id"];
        if ($p["ngrado"]=="Primero")$curso["ngrado"]=1;
        if ($p["ngrado"]=="Segundo")$curso["ngrado"]=2;
        $curso["complemento"] = $p["complemento"];
        $curso["nombre"] = $p["nombre"];
        $curso["paterno"] = $p["paterno"];
        $curso["materno"] = $p["materno"];
        $curso["fecha_inicio"] = $p["fecha_inicio"];
        $curso["fecha_fin"] = $p["fecha_fin"];
        $curso["bloque"] = $p["bloque"];
        $curso["parte"] = $p["parte"];
        $curso["id"] = $p["id"];
        $curso["depto"] = $p["depto"];
        $curso["provincia"] = $p["provincia"];
        $curso["municipio"] = $p["municipio"];
        $curso["localidad"] = $p["localidad"];
        $curso["esactivo"] = $p["esactivo"];
      
        $curso_existe=1;
    }
    //BUSCAR PERSONA
    $servicioPersona = $this->get('sie_app_web.persona');
    $persona = $servicioPersona->buscarPersona($ci,$complemento,0);  
    $facilitador_existe=0; 
    $facilitador = array(); 
    if($persona->type_msg === "success"){   
        $facilitador = array();
        $facilitador['id'] = $persona->result[0]->id;
        $facilitador['paterno'] = $persona->result[0]->paterno;
        $facilitador['materno'] = $persona->result[0]->materno;
        $facilitador['nombre'] = $persona->result[0]->nombre;
        $fecha_nac=$persona->result[0]->fecha_nacimiento;
        $facilitador['fecha_nac'] = $fecha_nac;
        $facilitador['genero'] = $persona->result[0]->genero_tipo_id;
        $facilitador['ci'] = $persona->result[0]->carnet;
        $facilitador['complemento'] = $persona->result[0]->complemento;
        $facilitador_existe=1;    
    }
    /*
    else{
        echo '<div class="alert alert-danger">'.$persona->msg.'</div>';die;
    }
    */
    return $this->render('SiePnpBundle:Default:cambiar_facilitador_encontrado.html.twig',array('curso'=>$curso,'curso_existe'=>$curso_existe,'facilitador'=>$facilitador,'facilitador_existe'=>$facilitador_existe));
}


/////////////////////////////////busquedas//////////////////////
// buscar datos estudiantes
    public function retornar_estudianteAction($where){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "SELECT
                      estudiante.id as estudiante_id,
                      estudiante.codigo_rude, 
                      estudiante.carnet_identidad, 
                      estudiante.complemento, 
                      estudiante.paterno, 
                      estudiante.materno, 
                      estudiante.nombre, 
                      estudiante.fecha_nacimiento, 
                      estudiante.genero_tipo_id,
                      genero_tipo.genero,
                      estudiante.observacionadicional,
                      estudiante_inscripcion.estadomatricula_tipo_id as matricula_estado_id,
                      estadomatricula_tipo.estadomatricula,
                      estudiante_inscripcion.id as inscripcion_id,
                      institucioneducativa_curso.ciclo_tipo_id,
                      institucioneducativa_curso.grado_tipo_id
                    FROM 
                      estudiante 
                      LEFT JOIN estudiante_inscripcion ON estudiante.id = estudiante_inscripcion.estudiante_id
                      LEFT JOIN genero_tipo ON genero_tipo.id = estudiante.genero_tipo_id
                      LEFT JOIN estadomatricula_tipo ON estadomatricula_tipo.ID = estudiante_inscripcion.estadomatricula_inicio_tipo_id
                      LEFT JOIN institucioneducativa_curso ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
                    WHERE
                     $where";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        return $po;
    }

    // buscar datos persona
    public function retornar_personaAction($ci){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = "SELECT
                  p.id,p.carnet as carnet_identidad,p.rda,p.paterno,p.materno,p.nombre,p.fecha_nacimiento,g.genero
                  from persona p,genero_tipo g
                  where g.id=p.genero_tipo_id and
                  p.carnet='$ci' and p.esvigente='t' order by p.id desc limit 1
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        return $po;
    }

    //buscar archivos
     public function retornar_archivos_personaAction($persona_id,$ie){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
    $query = "
            SELECT persona.carnet,persona.complemento, persona.nombre, persona.paterno, persona.materno,
                institucioneducativa_curso.fecha_inicio, institucioneducativa_curso.fecha_fin,
                institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id,
                                ct.ciclo,gt.grado,
                institucioneducativa_curso.id,

                CASE
                    WHEN institucioneducativa_curso.institucioneducativa_id = 80480300 THEN
                        'CHUQUISACA'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 80730794 THEN
                        'LA PAZ'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 80980569 THEN
                        'COCHABAMBA'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 81230297 THEN
                        'ORURO'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 81480201 THEN
                        'POTOSI'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 81730264 THEN
                        'TARIJA'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 81981501 THEN
                        'SANTA CRUZ'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 82230130 THEN
                        'BENI'
                    WHEN institucioneducativa_curso.institucioneducativa_id = 82480050 THEN
                        'PANDO'                         
                  END AS depto,
             institucioneducativa_curso.lugar,icd.esactivo

            from institucioneducativa_curso 
            inner join maestro_inscripcion 
            on institucioneducativa_curso.maestro_inscripcion_id_asesor = maestro_inscripcion .id
            inner join persona 
            on maestro_inscripcion .persona_id = persona.id
            inner join institucioneducativa_curso_datos icd on icd.institucioneducativa_curso_id=institucioneducativa_curso.id
            join ciclo_tipo ct on  institucioneducativa_curso.ciclo_tipo_id=ct.id
                        join grado_tipo gt on institucioneducativa_curso.grado_tipo_id=gt.id

            where 
            persona.id = $persona_id and institucioneducativa_curso.institucioneducativa_id =$ie

            order by                 
            institucioneducativa_curso.fecha_inicio,
            institucioneducativa_curso.ciclo_tipo_id, institucioneducativa_curso.grado_tipo_id
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["carnet"] = $p["carnet"];
            $datos_filas["complemento"] = $p["complemento"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["fecha_inicio"] = $p["fecha_inicio"];
            $datos_filas["fecha_fin"] = $p["fecha_fin"];
            $datos_filas["ciclo_tipo_id"] = $p["ciclo_tipo_id"];
            $datos_filas["grado_tipo_id"] = $p["grado_tipo_id"];
            $datos_filas["ciclo"] = $p["ciclo"];
            $datos_filas["grado"] = $p["grado"];
            $datos_filas["id"] = $p["id"];
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["lugar"] = $p["lugar"];
            $datos_filas["esactivo"] = $p["esactivo"];
            $filas[] = $datos_filas;
        }        
        return $filas;
    }
    //buscar archivos de 2015 para adelante
}