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
use Sie\AppWebBundle\Entity\MaestroInscripcion;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoDatos;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\LugarTipo;
use Sie\AppWebBundle\Entity\PersonaCarnetControl;
use Sie\AppWebBundle\Entity\Persona;
//use Sie\AppWebBundle\Entity\PnpSerialRude;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class ReporteController extends Controller
{
    public function __construct() {
        $this->session = new Session();
    }
    
    
  
//////////////Reporte General 
public function reporte_generalAction($nivel_ini,$lugar,$nivel_fin,Request $request){
//
    $em = $this->getDoctrine()->getManager();
    $db = $em->getConnection();

    // $query = "SELECT min((extract(year from ic.fecha_fin))) as gestion_ini,
    //                     max((extract(year from ic.fecha_fin))) as gestion_fin 
    //                     FROM institucioneducativa_curso ic
    //                     join institucioneducativa_curso_datos icd on ic.id=icd.institucioneducativa_curso_id";
    //     $stmt = $db->prepare($query);
    //     $params = array();
    //     $stmt->execute($params);
    //     $po = $stmt->fetchAll();
        $filas = array();
        // foreach ($po as $p) {
        //     $gestion_ini_t = $p["gestion_ini"];
        //     $gestion_fin_t = $p["gestion_fin"];
        // } 
        $gestion_ini_t = 2009;
        $gestion_fin_t = 2023;
    $result=$em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lugar);
    $lugar_nombre=$result->getLugar();
    $plan=3;
    if($request->getMethod()=="POST") {
        $gestion_ini=$request->get("gestion_ini");
        $gestion_fin=$request->get("gestion_fin");
        $m_option=$request->get("m_option");
        $plan=$request->get("plan");
        if($m_option==2)//graduados option 1 todos
            $graduado="AND estadomatricula_tipo_id=62";
        else
            $graduado="";
        //plan
        if($plan==3)
            $graduado="$graduado";
        elseif ($plan==2) $graduado="$graduado AND (bloque=34 OR bloque=35)";
        else $graduado="$graduado AND (bloque=1 OR bloque=2)";

    }
    else{
        $graduado="AND estadomatricula_tipo_id=62";
        $m_option=2;
        ///// sacar gestion_ini y gestion_fin por defecto y el id_lugar bolivia id=1
        // $query = "SELECT min((extract(year from ic.fecha_fin))) as gestion_ini,
        //                 max((extract(year from ic.fecha_fin))) as gestion_fin 
        //                 FROM institucioneducativa_curso ic
        //                 join institucioneducativa_curso_datos icd on ic.id=icd.institucioneducativa_curso_id";
        // $stmt = $db->prepare($query);
        // $params = array();
        // $stmt->execute($params);
        // $po = $stmt->fetchAll();
        $filas = array();
        // foreach ($po as $p) {
            // $gestion_ini = $p["gestion_ini"];
            // $gestion_fin = $p["gestion_fin"];
        // } 
            $gestion_ini = 2009;
            $gestion_fin = 2023;
    }
    /////////////// REVISAR DE DONDE ESTA VINVIENDO Y QUE QUIERE VER
    $select1=$where=$select2_groupby=$groupby2="";
    if($nivel_ini==1 and $nivel_fin==1){//inicio-Departamentos
        $select1="id_dep as id,dep as lugar";
        $select2_groupby="id_dep,dep,bloque,parte";
        $where="";
        $groupby2="id_dep,dep";
    }
    if($nivel_ini==1 and $nivel_fin==2){//Departamento-Provincia
        $select1="id_dep,dep,id_prov as id,prov as lugar";
        $select2_groupby="id_dep,dep,id_prov,prov,bloque,parte";
        $where=" AND id_dep =  $lugar";
        $groupby2="id_dep,dep,id_prov,prov";
    }
    if($nivel_ini==2 and $nivel_fin==3){//Provincia-Municipio
        $select1="id_dep,dep,id_prov,prov,id_mun as id,mun as lugar";
        $select2_groupby="id_dep,dep,id_prov,prov,id_mun,mun,bloque,parte";
        $where=" AND id_prov =  $lugar";
        $groupby2="id_dep,dep,id_prov,prov,id_mun,mun";
    }
    if($nivel_ini==1 and $nivel_fin==3){//Departamento-Municipio
        $select1="id_dep,dep,id_prov,prov,id_mun as id,mun as lugar";
        $select2_groupby="id_dep,dep,id_prov,prov,id_mun,mun,bloque,parte";
        $where=" AND id_dep =  $lugar";
        $groupby2="id_dep,dep,id_prov,prov,id_mun,mun";
    }
    /////////////// TABLA 1 Calcular por departamento cantidad de graduados por bloque y parte 
    $query = "SELECT $select1,
                sum(case when (bloque=1 and parte=1) or parte=14 THEN cantidad ELSE 0 END) as b1p1,
                sum(case when (bloque=1 and parte=2) or parte=15 THEN cantidad ELSE 0 END) as b1p2,
                sum(case when (bloque=2 and parte=1) or parte=16 THEN cantidad ELSE 0 END) as b2p1,
                sum(case when (bloque=2 and parte=2) or parte=17 THEN cantidad ELSE 0 END) as b2p2,
                sum(cantidad) as total
                FROM
                (
                SELECT *,count(*) as cantidad 
                FROM
                (
                SELECT $select2_groupby
                FROM vw_pnp_reporte
                WHERE gestion_fin BETWEEN $gestion_ini AND $gestion_fin $graduado $where
                ) t1
                GROUP BY $select2_groupby
                )t2 
                GROUP BY $groupby2 
                ORDER BY lugar";
    $stmt = $db->prepare($query);
    $params = array();
    $stmt->execute($params);
    $po = $stmt->fetchAll();
    $tabla1 = array();
    $datos_filas = array();
    foreach ($po as $p) {
        $datos_filas["id"] = $p["id"];
        $datos_filas["lugar"] = $p["lugar"];
        $datos_filas["b1p1"] = $p["b1p1"];
        $datos_filas["b1p2"] = $p["b1p2"];
        $datos_filas["b2p1"] = $p["b2p1"];
        $datos_filas["b2p2"] = $p["b2p2"];
        $datos_filas["total"] = $p["total"];
        $tabla1[] = $datos_filas;
    }
    /////////////////////////TOTAL T1/////////////
    $query = "SELECT 
            sum(case when (bloque=1 and parte=1) or parte=14 THEN cantidad ELSE 0 END) as b1p1,
            sum(case when (bloque=1 and parte=2) or parte=15 THEN cantidad ELSE 0 END) as b1p2,
            sum(case when (bloque=2 and parte=1) or parte=16 THEN cantidad ELSE 0 END) as b2p1,
            sum(case when (bloque=2 and parte=2) or parte=17 THEN cantidad ELSE 0 END) as b2p2,
            sum(cantidad) as total
            FROM(
            SELECT *,count(*) as cantidad 
            FROM(
            SELECT bloque,parte
            FROM vw_pnp_reporte
            WHERE gestion_fin BETWEEN $gestion_ini AND $gestion_fin $graduado $where
            ) t1
            GROUP BY bloque,parte
            )t2 ";
    $stmt = $db->prepare($query);
    $params = array();
    $stmt->execute($params);
    $po = $stmt->fetchAll();
    $tabla1t = array();
    $datos_filas = array();
    foreach ($po as $p) {
        $datos_filas["b1p1"] = $p["b1p1"];
        $datos_filas["b1p2"] = $p["b1p2"];
        $datos_filas["b2p1"] = $p["b2p1"];
        $datos_filas["b2p2"] = $p["b2p2"];
        $datos_filas["total"] = $p["total"];
        $total_g = $p["total"];  
        $tabla1t[] = $datos_filas;
    }
    /////////////////////////////
    /////////////// TABLA 2 y 3 Calcular por departamento cantidad de graduados por bloque y parte MASCULINO 
    $query = "SELECT $select1,genero,
                sum(case when (bloque=1 and parte=1) or parte=14 THEN cantidad ELSE 0 END) as b1p1,
                sum(case when (bloque=1 and parte=2) or parte=15 THEN cantidad ELSE 0 END) as b1p2,
                sum(case when (bloque=2 and parte=1) or parte=16 THEN cantidad ELSE 0 END) as b2p1,
                sum(case when (bloque=2 and parte=2) or parte=17 THEN cantidad ELSE 0 END) as b2p2,
                sum(cantidad) as total
                FROM
                (
                SELECT *,count(*) as cantidad 
                FROM
                (
                SELECT $select2_groupby,genero
                FROM vw_pnp_reporte
                WHERE gestion_fin BETWEEN $gestion_ini AND $gestion_fin $graduado $where
                ) t1
                GROUP BY $select2_groupby,genero
                )t2 
                GROUP BY $groupby2,genero 
                order by lugar";
    $stmt = $db->prepare($query);
    $params = array();
    $stmt->execute($params);
    $po = $stmt->fetchAll();
    $tabla2 = array();
    $tabla3 = array();
    $datos_filas = array();
    foreach ($po as $p) {
        $datos_filas["id"] = $p["id"];
        $datos_filas["lugar"] = $p["lugar"];
        $datos_filas["b1p1"] = $p["b1p1"];
        $datos_filas["b1p2"] = $p["b1p2"];
        $datos_filas["b2p1"] = $p["b2p1"];
        $datos_filas["b2p2"] = $p["b2p2"];
        $datos_filas["total"] = $p["total"];
        if($p["genero"]==1)
            $tabla2[] = $datos_filas;
        else
            $tabla3[] = $datos_filas;
    }
    /////////////////////////TOTAL T2 y T3/////////////
    $query = "SELECT genero,
            sum(case when (bloque=1 and parte=1) or parte=14 THEN cantidad ELSE 0 END) as b1p1,
            sum(case when (bloque=1 and parte=2) or parte=15 THEN cantidad ELSE 0 END) as b1p2,
            sum(case when (bloque=2 and parte=1) or parte=16 THEN cantidad ELSE 0 END) as b2p1,
            sum(case when (bloque=2 and parte=2) or parte=17 THEN cantidad ELSE 0 END) as b2p2,
            sum(cantidad) as total
            FROM(
            SELECT *,count(*) as cantidad 
            FROM(
            SELECT genero,bloque,parte
            FROM vw_pnp_reporte
            WHERE gestion_fin BETWEEN $gestion_ini AND $gestion_fin $graduado $where
            ) t1
            GROUP BY genero,bloque,parte
            )t2 GROUP BY genero";
    $stmt = $db->prepare($query);
    $params = array();
    $stmt->execute($params);
    $po = $stmt->fetchAll();
    $tabla2t = array();
    $tabla3t = array();
    $datos_filas = array();
    $total_g_m=0;
    $total_g_f=0;
    foreach ($po as $p) {
        $datos_filas["b1p1"] = $p["b1p1"];
        $datos_filas["b1p2"] = $p["b1p2"];
        $datos_filas["b2p1"] = $p["b2p1"];
        $datos_filas["b2p2"] = $p["b2p2"];
        $datos_filas["total"] = $p["total"];
        if($p["genero"]==1){
            $total_g_m = $p["total"];  
            $tabla2t[] = $datos_filas;  
        }
        else{
        $total_g_f = $p["total"];  
        $tabla3t[] = $datos_filas;
        }
    }    
    

    /////////////////////////////
    return $this->render('SiePnpBundle:Reporte:reporte_general.html.twig',
        array('gestion_ini'=>$gestion_ini,
            'gestion_fin'=>$gestion_fin,
            'gestion_ini_t'=>$gestion_ini_t,
            'gestion_fin_t'=>$gestion_fin_t,
            'tabla1'=>$tabla1,
            'tabla1t'=>$tabla1t,
            'total_g'=>$total_g,
            'tabla2'=>$tabla2,
            'tabla2t'=>$tabla2t,
            'total_g_m'=>$total_g_m,
            'tabla3'=>$tabla3,
            'tabla3t'=>$tabla3t,
            'total_g_f'=>$total_g_f,
            'm_option'=>$m_option,
            'nivel_ini'=>$nivel_ini,
            'nivel_fin'=>$nivel_fin,
            'lugar_nombre'=>$lugar_nombre,
            'lugar'=>$lugar,
            'plan'=>$plan,
            ));
}
///////////////////////////////////////////////////////777
////////////////////////////REPORTE POR GESTION
public function reporte_por_gestionAction($nivel_ini,$lugar,$nivel_fin,Request $request){

    $em = $this->getDoctrine()->getManager();
    $em = $this->getDoctrine()->getManager();
    $db = $em->getConnection();

    // $query = "SELECT min((extract(year from ic.fecha_fin))) as gestion_ini,
    //                     max((extract(year from ic.fecha_fin))) as gestion_fin 
    //                     FROM institucioneducativa_curso ic
    //                     join institucioneducativa_curso_datos icd on ic.id=icd.institucioneducativa_curso_id";
    //     $stmt = $db->prepare($query);
    //     $params = array();
    //     $stmt->execute($params);
    //     $po = $stmt->fetchAll();
        $filas = array();
        // foreach ($po as $p) {
        //     $gestion_ini_t = $p["gestion_ini"];
        //     $gestion_fin_t = $p["gestion_fin"];
        // } 
    $gestion_ini_t = 2009;
    $gestion_fin_t = 2023;

    $plan=3;
    $result=$em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lugar);
    $lugar_nombre=$result->getLugar();
    if($request->getMethod()=="POST") {
        $gestion_ini=$request->get("gestion_ini");
        $gestion_fin=$request->get("gestion_fin");
        $m_option=$request->get("m_option");
        $plan=$request->get("plan");
        if($m_option==2)//graduados option 1 todos
            $graduado="AND estadomatricula_tipo_id=62";
        else
            $graduado="";

         //plan
        if($plan==3)
            $graduado="$graduado";
        elseif ($plan==2) $graduado="$graduado AND (bloque=34 OR bloque=35)";
        else $graduado="$graduado AND (bloque=1 OR bloque=2)";
    }
    else{
        $graduado="AND estadomatricula_tipo_id=62";
        $m_option=2;
        ///// sacar gestion_ini y gestion_fin por defecto y el id_lugar bolivia id=1
        // $query = "SELECT min((extract(year from ic.fecha_fin))) as gestion_ini,
        //                 max((extract(year from ic.fecha_fin))) as gestion_fin 
        //                 FROM institucioneducativa_curso ic
        //                 join institucioneducativa_curso_datos icd on ic.id=icd.institucioneducativa_curso_id";
        // $stmt = $db->prepare($query);
        // $params = array();
        // $stmt->execute($params);
        // $po = $stmt->fetchAll();
        $filas = array();
        // foreach ($po as $p) {
        //     $gestion_ini = $p["gestion_ini"];
        //     $gestion_fin = $p["gestion_fin"];
        // }
            $gestion_ini = 2009;
            $gestion_fin = 2023;
    }
    /////////////// REVISAR DE DONDE ESTA VINVIENDO Y QUE QUIERE VER
    if($nivel_ini==1 and $nivel_fin==1){//inicio-Departamentos
        $select1="id_dep as id,dep as lugar";
        $select2_groupby="id_dep,dep,bloque,parte";
        $where="";
        $groupby2="id_dep,dep";
    }
    if($nivel_ini==1 and $nivel_fin==2){//Departamento-Provincia
        $select1="id_dep,dep,id_prov as id,prov as lugar";
        $select2_groupby="id_dep,dep,id_prov,prov,bloque,parte";
        $where=" AND id_dep =  $lugar";
        $groupby2="id_dep,dep,id_prov,prov";
    }
    if($nivel_ini==2 and $nivel_fin==3){//Provincia-Municipio
        $select1="id_dep,dep,id_prov,prov,id_mun as id,mun as lugar";
        $select2_groupby="id_dep,dep,id_prov,prov,id_mun,mun,bloque,parte";
        $where=" AND id_prov =  $lugar";
        $groupby2="id_dep,dep,id_prov,prov,id_mun,mun";
    }
    if($nivel_ini==1 and $nivel_fin==3){//Departamento-Municipio
        $select1="id_dep,dep,id_prov,prov,id_mun as id,mun as lugar";
        $select2_groupby="id_dep,dep,id_prov,prov,id_mun,mun,bloque,parte";
        $where=" AND id_dep =  $lugar";
        $groupby2="id_dep,dep,id_prov,prov,id_mun,mun";
    }
    ///////////////GUARDAMOS LOS DEPARTAMENTOS Y EL ID
    $query = "SELECT $select1,
            sum(case when (bloque=1 and parte=1) or parte=14 THEN cantidad ELSE 0 END) as b1p1,
            sum(case when (bloque=1 and parte=2) or parte=15 THEN cantidad ELSE 0 END) as b1p2,
            sum(case when (bloque=2 and parte=1) or parte=16 THEN cantidad ELSE 0 END) as b2p1,
            sum(case when (bloque=2 and parte=2) or parte=17 THEN cantidad ELSE 0 END) as b2p2,
            sum(cantidad) as total
            FROM
            (
            SELECT *,count(*) as cantidad 
            FROM(
            SELECT $select2_groupby
            FROM vw_pnp_reporte
            WHERE 1=1 $graduado $where
            ) t1
            GROUP BY $select2_groupby
            )t2 
            GROUP BY $groupby2
            order by lugar";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $tabla11 = array();
        $tabla12 = array();
        $tabla21 = array();
        $tabla22 = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["id"] = $p["id"];
            $datos_filas["lugar"] = $p["lugar"];
            for($i=$gestion_ini;$i<=$gestion_fin;$i++){
                $datos_filas[$i] = 0;
            }
            $tabla11[] = $datos_filas;
            $tabla12[] = $datos_filas;
            $tabla21[] = $datos_filas;
            $tabla22[] = $datos_filas;
        }
        $cantidad=count($tabla11);
        //print_r($tabla11[1]["id"]);die;
    /////////////// TABLA 1 Calcular en un bucle cada año
    for($i=$gestion_ini;$i<=$gestion_fin;$i++){
        $query = "SELECT $select1,
            sum(case when (bloque=1 and parte=1) or parte=14 THEN cantidad ELSE 0 END) as b1p1,
            sum(case when (bloque=1 and parte=2) or parte=15 THEN cantidad ELSE 0 END) as b1p2,
            sum(case when (bloque=2 and parte=1) or parte=16 THEN cantidad ELSE 0 END) as b2p1,
            sum(case when (bloque=2 and parte=2) or parte=17 THEN cantidad ELSE 0 END) as b2p2,
            sum(cantidad) as total
            FROM
            (
            SELECT *,count(*) as cantidad 
            FROM(
            SELECT $select2_groupby
            FROM vw_pnp_reporte
            WHERE gestion_fin = $i $graduado $where
            ) t1
            GROUP BY $select2_groupby
            )t2 
            GROUP BY $groupby2
            order by lugar";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $datos_filas = array();
        foreach ($po as $p) {
            for($j=0;$j<$cantidad;$j++){
                if($p["id"]==$tabla11[$j]["id"]){ 
                    $tabla11[$j][$i] = $p["b1p1"]; 
                }
                if($p["id"]==$tabla12[$j]["id"]){ 
                    $tabla12[$j][$i] = $p["b1p2"]; 
                }
                if($p["id"]==$tabla21[$j]["id"]){ 
                    $tabla21[$j][$i] = $p["b2p1"]; 
                }
                if($p["id"]==$tabla22[$j]["id"]){ 
                    $tabla22[$j][$i] = $p["b2p2"]; 
                }             
            }
        }    
    }

    ////////////////////SUMAR TOTALES
    $total11=$total12=$total21=$total22=0;
    for($j=0;$j<$cantidad;$j++){
        for($i=$gestion_ini;$i<=$gestion_fin;$i++){
            $total11=$total11+$tabla11[$j][$i];
            $total12=$total12+$tabla12[$j][$i];
            $total21=$total21+$tabla21[$j][$i];
            $total22=$total22+$tabla22[$j][$i];
        }
        $tabla11[$j]["total"] = $total11;
        $tabla12[$j]["total"] = $total12;
        $tabla21[$j]["total"] = $total21;
        $tabla22[$j]["total"] = $total22;
        $total11=$total12=$total21=$total22=0;
    }
    /////////////////////////TOTAL TABLA/////////////
    $suma_t11=$suma_t12=$suma_t21=$suma_t22=0;
    for($i=$gestion_ini;$i<=$gestion_fin;$i++){
        $query = "SELECT 
                sum(case when (bloque=1 and parte=1) or parte=14 THEN cantidad ELSE 0 END) as b1p1,
                sum(case when (bloque=1 and parte=2) or parte=15 THEN cantidad ELSE 0 END) as b1p2,
                sum(case when (bloque=2 and parte=1) or parte=16 THEN cantidad ELSE 0 END) as b2p1,
                sum(case when (bloque=2 and parte=2) or parte=17 THEN cantidad ELSE 0 END) as b2p2,
                sum(cantidad) as total
                FROM
                (
                SELECT *,count(*) as cantidad 
                FROM(
                SELECT bloque,parte
                FROM vw_pnp_reporte
                WHERE gestion_fin = $i $graduado $where
                ) t1
                GROUP BY bloque,parte
                )t2 ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $tabla1t = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $tabla11t[$i]= $p["b1p1"];
            $tabla12t[$i]= $p["b1p2"];
            $tabla21t[$i]= $p["b2p1"];
            $tabla22t[$i]= $p["b2p2"];
            $suma_t11=$suma_t11+$p["b1p1"];
            $suma_t12=$suma_t12+$p["b1p2"];
            $suma_t21=$suma_t21+$p["b2p1"];
            $suma_t22=$suma_t22+$p["b2p2"];
        }
    }
    $tabla11t["total"]=$suma_t11;
    $tabla12t["total"]=$suma_t12;;
    $tabla21t["total"]=$suma_t21;;
    $tabla22t["total"]=$suma_t22;;
    /////////////////////////////
    return $this->render('SiePnpBundle:Reporte:reporte_por_gestion.html.twig',
        array('gestion_ini'=>$gestion_ini,
            'gestion_fin'=>$gestion_fin,
            'gestion_ini_t'=>$gestion_ini_t,
            'gestion_fin_t'=>$gestion_fin_t,
            'tabla11'=>$tabla11,
            'tabla12'=>$tabla12,
            'tabla21'=>$tabla21,
            'tabla22'=>$tabla22,
            'tabla11t'=>$tabla11t,
            'tabla12t'=>$tabla12t,
            'tabla21t'=>$tabla21t,
            'tabla22t'=>$tabla22t,
            'm_option'=>$m_option,
            'nivel_ini'=>$nivel_ini,
            'nivel_fin'=>$nivel_fin,
            'lugar_nombre'=>$lugar_nombre,
            'lugar'=>$lugar,
            'plan'=>$plan,
            ));
}
/////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////
public function reporte_graduadosAction(Request $request){
    $em = $this->getDoctrine()->getManager();
    $db = $em->getConnection();
    $userId = $this->session->get('userId');
    $roluser = $this->session->get('roluser');
     if(!$userId){
            return $this->redirectToRoute('logout');
        }
    $query = "
               SELECT lt.lugar as lugar
               FROM lugar_tipo lt,
               usuario_rol ur 
               WHERE ur.lugar_tipo_id=lt.id and ur.usuario_id=$userId";
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
            case 'CHUQUISACA':{$nombre_lugar="CHUQUISACA";$lugar_tipo_id=31654;$ie=80480300;}break;
            case 'LA PAZ':{$nombre_lugar="LA PAZ";$lugar_tipo_id=31655;$ie=80730794;}break;
            case 'COCHABAMBA':{$nombre_lugar="COCHABAMBA";$lugar_tipo_id=31656;$ie=80980569;}break;
            case 'ORURO':{$nombre_lugar="ORURO";$lugar_tipo_id=31657;$ie=81230297;}break;
            case 'POTOSI':{$nombre_lugar="POTOSI";$lugar_tipo_id=31658;$ie=81480201;}break;
            case 'TARIJA':{$nombre_lugar="TARIJA";$lugar_tipo_id=31659;$ie=81730264;}break;
            case 'SANTA CRUZ':{$nombre_lugar="SANTA CRUZ";$lugar_tipo_id=31660;$ie=81981501;}break;
            case 'BENI':{$nombre_lugar="BENI";$lugar_tipo_id=31661;$ie=82230130;}break;
            case 'PANDO':{$nombre_lugar="PANDO";$lugar_tipo_id=31662;$ie=82480050;}break;
            default:
                $lugar_tipo_id=1;$nombre_lugar="Bolivia";$ie=0;
                break;
        }
        $inicio=$fin="";$opc=0;
        if($request->getMethod()=="POST") { 
        /////datos    
            $lugar_tipo_id=$request->get("lugar_tipo_id");
            $inicio=$request->get("inicio");
            $fin=$request->get("fin");
            $opc=$request->get("opc");
            if($lugar_tipo_id==1){$select="dep"; $where="";}else {$select="mun"; $where="and id_dep=$lugar_tipo_id";}
            $where2="";
            if($opc==0)$where2="and esactivo = true";if($opc==1)$where2="and esactivo = false";
            $query = "
               SELECT $select as nombre,
                SUM(CASE WHEN bloque = 1 and parte = 1 THEN 1 ELSE 0 END    ) as b1p1,
                SUM(CASE WHEN bloque = 1 and parte = 2 THEN 1 ELSE 0 END    ) as b1p2,
                SUM(CASE WHEN bloque = 2 and parte = 1 THEN 1 ELSE 0 END    ) as b2p1,
                SUM(CASE WHEN bloque = 2 and parte = 2 THEN 1 ELSE 0 END    ) as b2p2,
                SUM(CASE WHEN parte = 14 THEN 1 ELSE 0 END  ) as aes1,
                SUM(CASE WHEN parte = 15 THEN 1 ELSE 0 END  ) as aes2,
                SUM(CASE WHEN parte = 16 THEN 1 ELSE 0 END  ) as aas1,
                SUM(CASE WHEN parte = 17 THEN 1 ELSE 0 END  ) as aas2,
                SUM(CASE WHEN (bloque = 1 and parte = 1) or parte = 14 THEN 1 ELSE 0 END    ) as seg,
                SUM(CASE WHEN (bloque = 1 and parte = 2) or parte = 15 THEN 1 ELSE 0 END    ) as ter,
                SUM(CASE WHEN (bloque = 2 and parte = 1) or parte = 16 THEN 1 ELSE 0 END    ) as qui,
                SUM(CASE WHEN (bloque = 2 and parte = 2) or parte = 17 THEN 1 ELSE 0 END    ) as sex
                from vw_pnp_reporte
                where 
                fecha_fin  BETWEEN '$inicio' AND '$fin'
                and estadomatricula_tipo_id=62 
                $where $where2
                group by $select
                order by $select";
                $stmt = $db->prepare($query);
                $params = array();
                $stmt->execute($params);
                $po = $stmt->fetchAll();
                $filas = array();
                $datos_filas = array();
                $b1p1=$b1p2=$b2p1=$b2p2=$aes1=$aes2=$aas1=$aas2=$seg=$ter=$qui=$sex=0;
                foreach ($po as $p) {
                    $datos_filas["nombre"] = $p["nombre"];
                    $datos_filas["b1p1"] = $p["b1p1"];$b1p1+= $p["b1p1"];
                    $datos_filas["b1p2"] = $p["b1p2"];$b1p2+= $p["b1p2"];
                    $datos_filas["b2p1"] = $p["b2p1"];$b2p1+= $p["b2p1"];
                    $datos_filas["b2p2"] = $p["b2p2"];$b2p2+= $p["b2p2"];
                    $datos_filas["aes1"] = $p["aes1"];$aes1+= $p["aes1"];
                    $datos_filas["aes2"] = $p["aes2"];$aes2+= $p["aes2"];
                    $datos_filas["aas1"] = $p["aas1"];$aas1+= $p["aas1"];
                    $datos_filas["aas2"] = $p["aas2"];$aas2+= $p["aas2"];
                    $datos_filas["seg"] = $p["seg"];$seg+= $p["seg"];
                    $datos_filas["ter"] = $p["ter"];$ter+= $p["ter"];
                    $datos_filas["qui"] = $p["qui"];$qui+= $p["qui"];
                    $datos_filas["sex"] = $p["sex"];$sex+= $p["sex"];
                    $filas[] = $datos_filas;
                }
                $datos_filas["nombre"] = "Total";
                $datos_filas["b1p1"] = $b1p1;
                $datos_filas["b1p2"] = $b1p2;
                $datos_filas["b2p1"] = $b2p1;
                $datos_filas["b2p2"] = $b2p2;
                $datos_filas["aes1"] = $aes1;
                $datos_filas["aes2"] = $aes2;
                $datos_filas["aas1"] = $aas1;
                $datos_filas["aas2"] = $aas2;
                $datos_filas["seg"] = $seg;
                $datos_filas["ter"] = $ter;
                $datos_filas["qui"] = $qui;
                $datos_filas["sex"] = $sex;
                $filas[] = $datos_filas;
            }
     return $this->render('SiePnpBundle:Reporte:reporte_graduados.html.twig',
        array('nombre_lugar'=>$nombre_lugar,
            'lugar_tipo_id'=>$lugar_tipo_id, 
            'inicio'=>$inicio, 
            'fin'=>$fin,
            'opc'=>$opc,
            'filas'=>$filas,
            'roluser'=>$roluser,
            ));
}


/////////////////////////////////busquedas//////////////////////
// buscar datos estudiantes
    public function retornar_estudianteAction($ci){
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
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
                      estudiante_inscripcion.id as inscripcion_id,
                      institucioneducativa_curso.ciclo_tipo_id,
                      institucioneducativa_curso.grado_tipo_id
                    FROM 
                      estudiante INNER JOIN estudiante_inscripcion ON estudiante.id = estudiante_inscripcion.estudiante_id
                      INNER JOIN genero_tipo ON genero_tipo.id = estudiante.genero_tipo_id
                      INNER JOIN estadomatricula_tipo ON estadomatricula_tipo.ID = estudiante_inscripcion.estadomatricula_inicio_tipo_id
                      INNER JOIN institucioneducativa_curso ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
                    WHERE
                      estudiante.carnet_identidad = '$ci' or estudiante.codigo_rude='$ci' ";
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
        return $filas;
    }
    //buscar archivos de 2015 para adelante
}