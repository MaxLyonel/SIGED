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
 * Institucioneducativa Controller
 */
class CensoController extends Controller {

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
    public function indexAction(Request $request) {
        //get the session's values
        /*dump($request->getSession()->get('ie_id'));
        dump($request->getSession()->get('ie_per_cod'));
        dump($request->getSession()->get('userId'));        
        die;*/
       
        $institucion = $request->getSession()->get('ie_id');
        $id_usuario  = $request->getSession()->get('userId');
        $periodo     = $request->getSession()->get('ie_per_cod');
        $gestion     = 2024;

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
     
        //40730321 61470045
        //esto era 1s
        /*$query = $em->getConnection()->prepare("            
           select b.*, 
            (select sum(beneficio) from censo_alternativa_beneficiarios_detalle d where d.beneficiario_id = b.id group by d.beneficiario_id) as beneficio
            from censo_alternativa_beneficiarios b where cea = :institucion order by paterno, materno, nombres 
 
        "); */

        // para 2s solo los que tienen saldo > 0 es decir 30 - totalasignado1s >0
        // y solo los que tengas estudiante_inscripcion_id del segundo semestre
        $query = $em->getConnection()->prepare("            
           select b.*, 
           saldo_beneficio	as beneficio 
            from censo_alternativa_beneficiarios b where cea = :institucion and saldo_beneficio > 0 and estudiante_inscripcion_s2 is not null  order by paterno, materno, nombres 
 
        "); 

        // para prueba
        //$institucion = 30680017;

        $query->bindValue(':institucion', $institucion);
        $query->execute();
        $beneficiarios = $query->fetchAll(); 

        //dump($beneficiarios); die;

        return $this->render($this->session->get('pathSystem') . ':Censo:index.html.twig', array(
            'beneficiarios' => $beneficiarios,
            'institucion' => $institucion,
            'gestion' => $gestion,
            'periodo' =>  $periodo
        ));
        
    }

    public function getNotasAction(Request $request){
        //dump($request); die;
        //dump($request->get('rude')); die;

        $saldo = 0;

        $rude = $request->get('rude');
        $beneficiario_id = $request->get('row');
        $institucion = $request->getSession()->get('ie_id');
        $sucursal = $request->getSession()->get('ie_subcea');

        $habilitado = true;

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("                        
            select imprime_ddjj_s2 as habilitado from censo_alternativa_beneficiarios where rudeal = :rude
        "); 
        $query->bindValue(':rude', $rude);
        $query->execute();
        $operativo = $query->fetchAll(); 
        if($operativo[0]['habilitado'] == true){
            // ya ha cerrado e impreso
            $habilitado = false;
        }

        //´para 2 semestre, ver el saldo disponible 
        //$beneficiario_id = 9986;
        /*$query = $em->getConnection()->prepare("                        
            select (30 - coalesce(sum(beneficio),0)) as saldo from censo_alternativa_beneficiarios_detalle where beneficiario_id = :beneficiario_id
        "); */

        $query = $em->getConnection()->prepare("                        
            select saldo_beneficio  as saldo from censo_alternativa_beneficiarios where id = :beneficiario_id
        "); 


        $query->bindValue(':beneficiario_id', $beneficiario_id);
        $query->execute();
        $saldodisponible = $query->fetchAll(); 
        $saldo = $saldodisponible[0]['saldo'] ;


        
        /*con notas 1s
        $query = $em->getConnection()->prepare("            
            WITH catalogo AS (select a.codigo as nivel_id, a.facultad_area,b.codigo as ciclo_id,b.especialidad,d.codigo as grado_id,d.acreditacion,c.id as superior_acreditacion_especialidad_id
from superior_facultad_area_tipo a
	inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
		inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
			inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id)
select k.*,h.codigo_rude,h.paterno,h.materno,h.nombre,j.estudianteasignatura_estado_id,i.periodo_tipo_id
,n.id,n.modulo,o.nota_cuantitativa,c.paralelo_tipo_id,c.turno_tipo_id, o.id as enid 
from superior_institucioneducativa_acreditacion a
	inner join superior_institucioneducativa_periodo b on b.superior_institucioneducativa_acreditacion_id=a.id
		inner join institucioneducativa_curso c on c.superior_institucioneducativa_periodo_id=b.id
			inner join estudiante_inscripcion d on c.id=d.institucioneducativa_curso_id
				inner join estudiante h on d.estudiante_id=h.id
					inner join institucioneducativa_sucursal i on a.institucioneducativa_sucursal_id=i.id
						inner join estudiante_asignatura j on d.id=j.estudiante_inscripcion_id
							inner join catalogo k on a.acreditacion_especialidad_id=k.superior_acreditacion_especialidad_id
								inner join institucioneducativa_curso_oferta l on j.institucioneducativa_curso_oferta_id=l.id
									inner join superior_modulo_periodo m on l.superior_modulo_periodo_id=m.id
										inner join superior_modulo_tipo n on m.superior_modulo_tipo_id=n.id
											inner join estudiante_nota o on j.id=o.estudiante_asignatura_id
where  i.gestion_tipo_id=2024::double precision and  i.institucioneducativa_id=:sie  and i.sucursal_tipo_id = :sucursal and i.periodo_tipo_id=3  and codigo_rude = :rude

        "); */

        $query = $em->getConnection()->prepare("            
            WITH catalogo AS (select a.codigo as nivel_id, a.facultad_area,b.codigo as ciclo_id,b.especialidad,d.codigo as grado_id,d.acreditacion,c.id as superior_acreditacion_especialidad_id
from superior_facultad_area_tipo a
	inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
		inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
			inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id)
select k.*,h.codigo_rude,h.paterno,h.materno,h.nombre,j.estudianteasignatura_estado_id,i.periodo_tipo_id
,n.id,n.modulo,c.paralelo_tipo_id,c.turno_tipo_id
from superior_institucioneducativa_acreditacion a
	inner join superior_institucioneducativa_periodo b on b.superior_institucioneducativa_acreditacion_id=a.id
		inner join institucioneducativa_curso c on c.superior_institucioneducativa_periodo_id=b.id
			inner join estudiante_inscripcion d on c.id=d.institucioneducativa_curso_id
				inner join estudiante h on d.estudiante_id=h.id
					inner join institucioneducativa_sucursal i on a.institucioneducativa_sucursal_id=i.id
						inner join estudiante_asignatura j on d.id=j.estudiante_inscripcion_id
							inner join catalogo k on a.acreditacion_especialidad_id=k.superior_acreditacion_especialidad_id
								inner join institucioneducativa_curso_oferta l on j.institucioneducativa_curso_oferta_id=l.id
									inner join superior_modulo_periodo m on l.superior_modulo_periodo_id=m.id
										inner join superior_modulo_tipo n on m.superior_modulo_tipo_id=n.id
											
where  i.gestion_tipo_id=2024::double precision and  i.institucioneducativa_id=:sie  and i.sucursal_tipo_id = :sucursal and i.periodo_tipo_id=3  and codigo_rude = :rude
");    

        // para prueba
        //$institucion = 30680017;

        //$rude = '81720068200836';
        $query->bindValue(':rude', $rude);
        $query->bindValue(':sie', $institucion);
        $query->bindValue(':sucursal', $sucursal);
        $query->execute();
        $notas = $query->fetchAll(); 

        //dump($notas); die;

        $response = new JsonResponse();
        return $response->setData(array('notas' => $notas, 'bid' => $beneficiario_id, 'habilitado' => $habilitado,'saldo' => $saldo));


    }

    public function getModulosAction(Request $request){
        

        $rude = $request->get('rude');
        $beneficiario_id = $request->get('row');
        
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("                        
            select distinct 
            --sia.*,
            --sae.*
            --sip.*
            smt.*
            --smp.*
            from  superior_institucioneducativa_acreditacion sia 
            inner join superior_acreditacion_especialidad sae on sia.acreditacion_especialidad_id = sae.id 
            inner join superior_especialidad_tipo set2 on sae.superior_especialidad_tipo_id = set2.id
            inner join superior_institucioneducativa_periodo sip on sia.id = sip.superior_institucioneducativa_acreditacion_id
            left join superior_modulo_periodo smp on smp.institucioneducativa_periodo_id = sip.id
            left join superior_modulo_tipo smt on smt.id =smp.superior_modulo_tipo_id
            inner join superior_facultad_area_tipo sfat on set2.superior_facultad_area_tipo_id = sfat.id
            where sia.gestion_tipo_id = 2023
            and sfat.codigo in ('18','19','20','21','22','23','24','25')
            and sia.institucioneducativa_id = 40730321
            and sae.superior_especialidad_tipo_id =16
            order by 2

        "); 
        //$query->bindValue(':rude', $rude);
        $query->execute();
        $notas = $query->fetchAll(); 

        //dump($notas); die;

        $response = new JsonResponse();
        return $response->setData(array('modulos' => $notas, 'bid' => $beneficiario_id));


    }


    public function saveNotasAction1s(Request $request){
        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 
        $response = new JsonResponse();

        $valores = $request->request->all();
        /*dump($valores); 
        die;*/

        $beneficiario_id = 0;
        //$operacion = 0;
        $operacion = 1;

        foreach ($valores as $clave => $valor) {    
          
            if($clave == 'bid'){
                $beneficiario_id = $valor;
            }
        }

        /*foreach ($valores as $clave => $valor) {    
            if($clave == 'operacion'){
                //dump($clave); dump($valor); die;
                $operacion = $valor;
            }
        }*/

        // TODO: obtener estos valores
       // $ei_id = 120524895;
        //$eid = 36449173;
        //$sie = 81880094;

        $sie = $request->getSession()->get('ie_id');
        $id_usuario  = $request->getSession()->get('userId');

        $query = $em->getConnection()->prepare("select estudiante_inscripcion_s1,estudiante_id from censo_alternativa_beneficiarios where id = :beneficiario_id");                
        $query->bindValue(':beneficiario_id', $beneficiario_id);
        $query->execute();
        $totales = $query->fetchAll();                        
        
        $ei_id = $totales[0]['estudiante_inscripcion_s1'];
        $eid = $totales[0]['estudiante_id'];


        foreach ($valores as $clave => $valor) {    
            
            if(substr($clave,0,5) == 'input'){

                $aux = $clave;
                $desde = strpos($aux, "_");
                $enid = substr($aux,$desde + 1, strlen($aux));

                $estudiante_nota_id = $enid; 
                $puntos = $valor; 

                
                if($puntos > 0){

                    $query = $em->getConnection()->prepare("select sum(beneficio) as total_beneficio from censo_alternativa_beneficiarios_detalle where periodo_id = 2 and beneficiario_id = :beneficiario_id");                
                    $query->bindValue(':beneficiario_id', $beneficiario_id);
                    $query->execute();
                    $totales = $query->fetchAll();                        
                    $total=$totales[0]['total_beneficio'];

                    if($total + $puntos <= 30){

                        $query ="insert into censo_alternativa_beneficiarios_detalle (beneficiario_id, estudiante_nota_id, periodo_id, beneficio, fecha_asignacion, usuario_id, operacion ) VALUES (?, ?, 2, ?, now(), 1, ?)";            
    
                        $stmt = $db->prepare($query);
                        $params = array($beneficiario_id, $estudiante_nota_id, $puntos, $operacion );
                        $stmt->execute($params);       


                        //actualizamos los saldos totales
                        $query = $em->getConnection()->prepare("select sum(beneficio) as total_beneficio from censo_alternativa_beneficiarios_detalle where beneficiario_id = :beneficiario_id");                
                        $query->bindValue(':beneficiario_id', $beneficiario_id);
                        $query->execute();
                        $totales = $query->fetchAll();     
                        /*dump($totales);
                        dump($totales[0]['total_beneficio']);
                        die;*/
                        $total=$totales[0]['total_beneficio'];

                        $query ="update censo_alternativa_beneficiarios set total_asignado_1s = ?  where id = ?";                
                        $stmt = $db->prepare($query);
                        $params = array($total,$beneficiario_id );
                        $stmt->execute($params); 

                        $query ="update censo_alternativa_beneficiarios set saldo_beneficio = total_beneficio - (total_asignado_1s + total_asignado_2s)  where id = ?";                
                        $stmt = $db->prepare($query);
                        $params = array($beneficiario_id );
                        $stmt->execute($params); 

                        //tabla generica censo_beneficiarios
                        //existe ?
                        
                        //TODO tener inscripcion_id
                        $query = $em->getConnection()->prepare("select count(id) as existe from censo_beneficiario where nivel_tipo_id = 15 and grado_tipo_id = 99 and censo_tabla_id = :beneficiario_id and estudiante_inscripcion_id = :ei_id");                
                        $query->bindValue(':beneficiario_id', $beneficiario_id);
                        $query->bindValue(':ei_id', $ei_id);
                        $query->execute();
                        $totales = $query->fetchAll();                        
                        $existe=$totales[0]['existe'];

                        if($existe == 0){
                            //se inserta
                            //INSERT INTO "public"."censo_beneficiario" ("id", "estudiante_id", "estudiante_inscripcion_id", "institucioneducativa_id", "nivel_tipo_id", "grado_tipo_id", "censo_tabla_id", "archivo", "usuario_id", "fecha_registro", "fecha_modificacion", "observacion") VALUES (2, 36449173, 120524895, 81880094, 15, 99, 1, 't', NULL, '2024-09-06 09:38:36', '2024-09-06 09:38:38', NULL);

                            $query ="insert into censo_beneficiario (estudiante_id, estudiante_inscripcion_id, institucioneducativa_id, nivel_tipo_id, grado_tipo_id, censo_tabla_id, archivo, usuario_id, fecha_registro, fecha_modificacion, observacion) VALUES (?, ?, ?, 15, 99, ?, NULL, ?, now(), now(), NULL)";                
                            $stmt = $db->prepare($query);
                            $params = array($eid,$ei_id,$sie,$beneficiario_id, $id_usuario);
                            $stmt->execute($params);     
                        }
                    
                    }else{


                        $msg  = 'La informacion no se pudo registrar !!';
                        return $response->setData(array('estado' => false, 'msg' => $msg, 'cantidad' => 0));

                        

                    }

                    
                }
                
            }
        }       
        
        $msg  = 'Datos registrados correctamente';
        return $response->setData(array('estado' => true, 'msg' => $msg, 'cantidad' => 0));
    }

    public function saveNotasAction(Request $request){
        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 
        $response = new JsonResponse();

        $valores = $request->request->all();
       
        //TODO: validar que el el total2s + total 1s no exceda los 15

        $sie = $request->getSession()->get('ie_id');
        $id_usuario  = $request->getSession()->get('userId');

      

        $bid = 0;
        foreach ($valores as $clave => $valor) {  

            if($clave == 'bid'){
                $beneficiario_id = $valor;
            }else{

                if(substr($clave,0,5) == 'input'){

                    $aux = $clave;
                    $desde = strpos($aux, "_");
                    $id = substr($aux,$desde + 1, strlen($aux));
    
                    $modulo_tipo_id = $id; 
                    $puntos = $valor; 

                    if($puntos > 0){

                        $query = $em->getConnection()->prepare("select estudiante_inscripcion_s2,estudiante_id from censo_alternativa_beneficiarios where id = :beneficiario_id");                
                        $query->bindValue(':beneficiario_id', $beneficiario_id);
                        $query->execute();
                        $totales = $query->fetchAll();                        
                        
                        $ei_id = $totales[0]['estudiante_inscripcion_s2'];
                        $eid = $totales[0]['estudiante_id'];

                        

                        $query ="insert into censo_alternativa_beneficiarios_detalle (beneficiario_id, modulo_tipo_id, periodo_id, beneficio, fecha_asignacion, usuario_id, operacion ) VALUES (?, ?, 3, ?, now(), 1, ?)";            
    
                        $stmt = $db->prepare($query);
                        $params = array($beneficiario_id, $modulo_tipo_id, $puntos, 1 );
                        $stmt->execute($params);       


                        //actualizamos los saldos totales
                        $query = $em->getConnection()->prepare("select sum(beneficio) as total_beneficio from censo_alternativa_beneficiarios_detalle where periodo_id = 3 and  beneficiario_id = :beneficiario_id");                
                        $query->bindValue(':beneficiario_id', $beneficiario_id);
                        $query->execute();
                        $totales = $query->fetchAll();     
                        /*dump($totales);
                        dump($totales[0]['total_beneficio']);
                        die;*/
                        $total=$totales[0]['total_beneficio'];

                        $query ="update censo_alternativa_beneficiarios set total_asignado_2s = ?  where id = ?";                
                        $stmt = $db->prepare($query);
                        $params = array($total,$beneficiario_id );
                        $stmt->execute($params); 


                        $query ="update censo_alternativa_beneficiarios set saldo_beneficio = total_beneficio - (total_asignado_1s + total_asignado_2s)  where id = ?";                
                        $stmt = $db->prepare($query);
                        $params = array($beneficiario_id );
                        $stmt->execute($params); 

                        

                        //TABLA GENERICA
                        $query = $em->getConnection()->prepare("select count(id) as existe from censo_beneficiario where nivel_tipo_id = 15 and grado_tipo_id = 99 and censo_tabla_id = :beneficiario_id and estudiante_inscripcion_id = :ei_id");                
                        $query->bindValue(':beneficiario_id', $beneficiario_id);
                        $query->bindValue(':ei_id', $ei_id);
                        $query->execute();
                        $totales = $query->fetchAll();                        
                        $existe=$totales[0]['existe'];
                        
                        if($existe == 0){
                            //se inserta
                            //INSERT INTO "public"."censo_beneficiario" ("id", "estudiante_id", "estudiante_inscripcion_id", "institucioneducativa_id", "nivel_tipo_id", "grado_tipo_id", "censo_tabla_id", "archivo", "usuario_id", "fecha_registro", "fecha_modificacion", "observacion") VALUES (2, 36449173, 120524895, 81880094, 15, 99, 1, 't', NULL, '2024-09-06 09:38:36', '2024-09-06 09:38:38', NULL);

                            $query ="insert into censo_beneficiario (estudiante_id, estudiante_inscripcion_id, institucioneducativa_id, nivel_tipo_id, grado_tipo_id, censo_tabla_id, archivo, usuario_id, fecha_registro, fecha_modificacion, observacion) VALUES (?, ?, ?, 15, 99, ?, NULL, ?, now(), now(), NULL)";                
                            $stmt = $db->prepare($query);
                            $params = array($eid,$ei_id,$sie,$beneficiario_id, $id_usuario);
                            $stmt->execute($params);     
                        }
                            


                    }

                }


            }


        }


        $msg  = 'Datos registrados correctamente';
        return $response->setData(array('estado' => true, 'msg' => $msg, 'cantidad' => 0));    

    }


    public function ddjjPrintAction(Request $request)
    {

       

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        $cea = $request->getSession()->get('ie_id');

          // para prueba
          //$cea = 30680017;
          
        $cea_nombre = $request->getSession()->get('ie_nombre');


        // cerrar este cea

        $query ="update censo_alternativa_beneficiarios set imprime_ddjj_s2 = true  where cea = ?";                
        $stmt = $db->prepare($query);
        $params = array($cea);
        $stmt->execute($params); 

       


        // fin cerrar cea

        $pdf = $this->container->get("white_october.tcpdf")->create(
            'PORTRATE', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true
        );
        // $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetAuthor('Adal');
        $pdf->SetTitle('Acta Supletorio');
        $pdf->SetSubject('Report PDF');
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(true, -10);
        // $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 058', PDF_HEADER_STRING, array(10,10,0), array(255,255,255));
        $pdf->SetKeywords('TCPDF, PDF, ACTA SUPLETORIO');
        $pdf->setFontSubsetting(true);
        $pdf->SetMargins(10, 10, 10, true);
        $pdf->SetAutoPageBreak(true, 8);

        $pdf->SetFont('helvetica', '', 9, '', true);
        $pdf->startPageGroup();
        $pdf->AddPage('P', array(215.9, 274.4));//'P', 'LETTER'
       

        $pdf->Image('images/logo-min-edu.png', 4, 4, 85, 25, '', '', '', false, 0, '', false, false, 0);

          


        $cabecera = '<br/><br/><br/><br/><br/><br/><br/><table border="0" style="font-size: 8.5px">';
        $cabecera .='<tr>';          
        $cabecera .='<td  align="center"><h2>ASIGNACION BENEFICIO CENSO NACIONAL DE POBLACIÓN Y VIVIENDA SEGUNDO SEMESTRE - 2024</h2></td>';           
        $cabecera .='</tr>';
        $cabecera .='<tr>';
            $cabecera .='<td   align="center"><b>DETALLE DE ASIGNACION DE PUNTOS A PARTICIPANTES SEGUN LISTADO OFICIAL INE</b></td>';          
        $cabecera .='</tr>';
        $cabecera .='</table><br/><br/>';

      

        $query = $em->getConnection()->prepare("
            select distinct beneficiario_id, paterno, materno, nombres, rudeal, carnet, complemento, departamento, distrito
            from censo_alternativa_beneficiarios_detalle d
            inner join censo_alternativa_beneficiarios b on b.id = d.beneficiario_id
            where b.cea = :cea and d.periodo_id = 3
            order by 2,3,4        
        ");     
        $query->bindValue(':cea', $cea);
        $query->execute();
        $participantes = $query->fetchAll();    
        //dump($participantes); die;   
        
        
        //distrito y depto
        $query = $em->getConnection()->prepare("
        select lt.lugar, lt.lugar_tipo_id, lt2.lugar as depto
        from lugar_tipo lt
        inner join lugar_tipo lt2 on lt2.id = lt.lugar_tipo_id
        where lt.id in
        (
        select lugar_tipo_id_distrito from jurisdiccion_geografica where id in ( select le_juridicciongeografica_id from institucioneducativa where id = :cea )
        )         
        ");     
        $query->bindValue(':cea', $cea);
        $query->execute();
        $distritodepto = $query->fetchAll();  

        $departamento =  $distritodepto[0]['depto']; // $participantes[0]['departamento'];
        $distrito =  $distritodepto[0]['lugar']; // 'Distrito'; // $participantes[0]['distrito'];


        $datoscea = '
            <table border="0.5" cellpadding="1.5" width="100%" style="font-size: 8.5px"><thead>
            <tr>
                <th width="15%" style="background-color:#ddd;"><strong>CEA</strong></th>
                <th width="50%">'. $cea_nombre .'</th>
                <th width="15%" style="background-color:#ddd;"><strong>CODIGO SIE</strong></th>
                <th width="20%" align="center">' . $cea . '</th>
            </tr></thead>
            <tbody>
            <tr>
                <td width="15%" style="background-color:#ddd;"><strong>GESTIÓN</strong></td>
                <td width="50%">2024</td>
                <td width="15%" style="background-color:#ddd;"><strong>PERIODO</strong></td>
                <td width="20%" align="center">SEGUNDO SEMESTRE</td>
            </tr>
            <tr>
                <td width="15%" style="background-color:#ddd;"><strong>DEPARTAMENTO</strong></td>
                <td width="50%">'. strtoupper($departamento) .'</td>
                <td width="15%" style="background-color:#ddd;"><strong>DISTRITO</strong></td>
                <td width="20%" align="center">'. strtoupper($distrito) .'</td>
            </tr>
            </tbody>
            </table>
            <br/><br/>
        ';
       

        $tabla_beneficiarios = '';

        for ($index=0; $index < count($participantes) ; $index++) { 
            
            $beneficiario_id = $participantes[$index]['beneficiario_id'];
            $participante_nombre = $participantes[$index]['paterno'] . " " . $participantes[$index]['materno'] . " " . $participantes[$index]['nombres'];

            $filas_array = $this->generafilas($beneficiario_id);
            $info =  $filas_array['especialidad'] . " - " . $filas_array['acreditacion'];

            //dump($info); die;

            $tabla_beneficiarios = $tabla_beneficiarios . '
            <table border="0.5" width="100%" cellpadding="1.5" style="font-size: 8.5px">
                <thead> 
                    <tr>
                        <th colspan="2"><strong>'. $participante_nombre .' </strong> | RUDE: <strong>'. $participantes[$index]['rudeal'] .'</strong> | C.I.: <strong>'. $participantes[$index]['carnet'] .'</strong></th>
                    </tr>
                    <tr>
                        <th colspan="2"><strong>'.  $info .'</strong></th>
                    </tr>
                </thead>
                <tbody>';

           
            
            $tabla_beneficiarios = $tabla_beneficiarios . $filas_array['filas'] .  '</tbody>
            </table> <br/><br/>
            ';


        }

       

        $fecha = date('Y-m-d H:i:s');


        $tabla_firma = '<i>(*) El presente documento constituye una declaracion jurada para efectos consiguientes.<br/>Fecha de Impresión: ' .$fecha . '<i><br/><br/><br/><br/><br/><br/><br/><br/><br/>
        <table border="0" width="100%" cellpadding="2" style="font-size: 8.5px">
        <thead>
        <tr>
            <th  width="10%"></th>
            <th  width="25%" align="center">------------------------------</th>
            <th  width="25%" align="center">------------------------------</th>
            <th  width="25%" align="center">------------------------------</th>
            <th  width="10%"></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td  width="10%"></td>
            <td  width="25%" align="center">FIRMA Y SELLO DIRECTOR CEA</td>
            <td  width="25%" align="center">FIRMA Y SELLO DIRECTOR DISTRITO</td>
            <td  width="25%" align="center">FIRMA Y SELLO DIRECTOR DEPARTAMENTAL</td>
            <td  width="10%"></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        
        </tbody>
        </table>
       
        ';

        $reporte = $cabecera . $datoscea . $tabla_beneficiarios . $tabla_firma;

        //dump($reporte); die;
      
        $pdf->writeHTML($reporte, true, false, true, false, '');

        $pdf->Output("Detalle_Censo_". $cea. "_" .date('YmdHis').".pdf", 'D');

    }

    public function generaFilas($beneficiario_id){

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        $filas = '
        <tr style="background-color:#ddd;" >
            <td width="85%" align="center">Modulo</td>
            <td width="15%" align="center">Puntos Beneficio</td>
        </tr>';

        //dump($beneficiario_id);die;

        $query = $em->getConnection()->prepare("
           

            SELECT distinct 
                censo_alternativa_beneficiarios.id, 
                censo_alternativa_beneficiarios.carnet, 
                censo_alternativa_beneficiarios.complemento, 
                censo_alternativa_beneficiarios.rudeal, 
                censo_alternativa_beneficiarios.paterno, 
                censo_alternativa_beneficiarios.materno, 
                censo_alternativa_beneficiarios.nombres, 
                censo_alternativa_beneficiarios.certificado_cpv, 
                censo_alternativa_beneficiarios_detalle.estudiante_nota_id, 
                censo_alternativa_beneficiarios_detalle.periodo_id, 
                censo_alternativa_beneficiarios_detalle.beneficio,
				censo_alternativa_beneficiarios_detalle.modulo_tipo_id,
                superior_modulo_tipo.modulo,
				set.especialidad,
                'codigo' as codigo, 
               sat.acreditacion
            FROM
                censo_alternativa_beneficiarios
                INNER JOIN
                censo_alternativa_beneficiarios_detalle
                ON 
                    censo_alternativa_beneficiarios.id = censo_alternativa_beneficiarios_detalle.beneficiario_id                            
               
                INNER JOIN
                superior_modulo_tipo
                ON 
                    superior_modulo_tipo.id = censo_alternativa_beneficiarios_detalle.modulo_tipo_id
								
                    inner join institucioneducativa_curso iec on iec.id = censo_alternativa_beneficiarios.institucioneducativa_curso_id_s2
                    inner join superior_institucioneducativa_periodo siep on siep.id = iec.superior_institucioneducativa_periodo_id
                    inner join superior_institucioneducativa_acreditacion  siea on siea.id = siep.superior_institucioneducativa_acreditacion_id
                    inner join superior_acreditacion_especialidad sae on sae.id = siea.acreditacion_especialidad_id
                    inner join superior_acreditacion_tipo sat on sat.id = sae.superior_acreditacion_tipo_id
                    inner join superior_especialidad_tipo set on set.id = sae.superior_especialidad_tipo_id 
           
            WHERE	
         censo_alternativa_beneficiarios.id = :beneficiario_id        
        ");                
        $query->bindValue(':beneficiario_id', $beneficiario_id);
        $query->execute();
        $modulos = $query->fetchAll(); 

        $total_beneficio = 0;
        $especialidad = '';
        $acreditacion = '';



        for ($index=0; $index < count($modulos) ; $index++) { 

            $especialidad = $modulos[$index]['especialidad'] ;
            $acreditacion = $modulos[$index]['acreditacion'] ;

            $total_beneficio = $total_beneficio + $modulos[$index]['beneficio'] ;

            $filas = $filas . '
                 <tr>
                    <td>' .$modulos[$index]['modulo'] . '</td>
                    <td align="center">' .$modulos[$index]['beneficio'] . '</td>
                </tr>
            ';
        }

        $filas = $filas . '<tr style="background-color:#ddd;">
            <td>TOTAL BENEFICIO SEGUNDO SEMESTRE</td>
            <td align="center">' . $total_beneficio . '</td>
        </tr>';

        //return $filas;
        return array("filas"=>$filas,"especialidad"=>$especialidad,"acreditacion"=>$acreditacion);

    }

    public function infoEstudianteAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 
        //$rude = $request->get('rude');
        
        $codigo_rude = $request->get('rude'); //"4073019620074741";
        $query = $em->getConnection()->prepare("
            select codigo_rude, carnet_identidad, complemento, paterno, materno, nombre
            from estudiante 
            WHERE	
            codigo_rude = :codigo_rude
        ");                
        $query->bindValue(':codigo_rude', $codigo_rude);
        $query->execute();
        $datos = $query->fetchAll(); 

        $encontrado = true;
        if (count($datos) == 0){
            $encontrado = false;
        }

        $response = new JsonResponse();
        return $response->setData(array('info' => $datos, 'encontrado' => $encontrado));

    }

    public function saveEstudianteAction(Request $request){

        //dump($request); die; 
        $rude = $request->get('rude');
        
        //dump($rude);die;
        
        $certificadocpv = $request->get('certificadocpv');
        $cea = $request->getSession()->get('ie_id');

        $response = new JsonResponse();
        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        $query = $em->getConnection()->prepare("
            select codigo_rude, carnet_identidad, complemento, paterno, materno, nombre
            from estudiante 
            WHERE	
            codigo_rude = :rude
        ");                
        $query->bindValue(':rude', $rude);
        $query->execute();
        $datos = $query->fetchAll(); 

        $carnet = $datos[0]['carnet_identidad'];
        $paterno = $datos[0]['paterno'];
        $materno = $datos[0]['materno'];
        $nombre = $datos[0]['nombre'];


        $query ="insert into censo_alternativa_beneficiarios (cea,rudeal, carnet, paterno, materno, nombres, certificado_cpv ) VALUES (?, ?, ?, ?, ?, ?, ?)";            
    
        $stmt = $db->prepare($query);
        $params = array($cea, $rude, $carnet, $paterno, $materno, $nombre, $certificadocpv );
        $stmt->execute($params);    


        $query ="
           update censo_alternativa_beneficiarios a
set estudiante_inscripcion_s1=b.estudiante_inscripcion_id,institucioneducativa_curso_id_s1=b.institucioneducativa_curso_id
from (
WITH catalogo AS (select a.codigo as nivel_id, a.facultad_area,b.codigo as ciclo_id,b.especialidad,d.codigo as grado_id,d.acreditacion,c.id as superior_acreditacion_especialidad_id
from superior_facultad_area_tipo a
	inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
		inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
			inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id)
select l.id as censo_alternativa_beneficiarios_id,d.id as estudiante_inscripcion_id,c.id as institucioneducativa_curso_id
from superior_institucioneducativa_acreditacion a
	inner join superior_institucioneducativa_periodo b on b.superior_institucioneducativa_acreditacion_id=a.id
		inner join institucioneducativa_curso c on c.superior_institucioneducativa_periodo_id=b.id
			inner join estudiante_inscripcion d on c.id=d.institucioneducativa_curso_id
				inner join estudiante h on d.estudiante_id=h.id
					inner join institucioneducativa_sucursal i on a.institucioneducativa_sucursal_id=i.id
						inner join catalogo k on a.acreditacion_especialidad_id=k.superior_acreditacion_especialidad_id
							inner join public.censo_alternativa_beneficiarios l on h.id=l.estudiante_id
where  i.gestion_tipo_id=2024::double precision and i.periodo_tipo_id=2
and k.nivel_id in (15,18,19,20,21,22,23,24,25)) b where a.id=b.censo_alternativa_beneficiarios_id;


UPDATE censo_alternativa_beneficiarios 
SET estudiante_id 
    = ( select id from estudiante where codigo_rude = censo_alternativa_beneficiarios.rudeal
      )
			where censo_alternativa_beneficiarios.id >= 9976;
			


UPDATE censo_alternativa_beneficiarios  set periodo = 'PRIMER SEMESTRE'
where censo_alternativa_beneficiarios.id >= 9976


        ";                
        $stmt = $db->prepare($query);        
        $stmt->execute(); 

        $msg  = 'Datos registrados correctamente';
        return $response->setData(array('estado' => true, 'msg' => $msg, 'cantidad' => 0));
        

    }

}
