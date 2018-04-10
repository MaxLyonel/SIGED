<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaPeriodo;
use Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaAcreditacion;
use Sie\AppWebBundle\Entity\SuperiorModuloPeriodo;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * EstudianteInscripcion controller.
 *
 */
class CursoAlternativaController extends Controller {

    public $session;

    /**
     * the class constructorv§
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        return $this->render('SieHerramientaAlternativaBundle:CursoAlternativa:index.html.twig');        
    }
    
    public function areachangeAction(Request $request, $areacod) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
                
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        
        $query = "  select b.id as id, b.especialidad as especialidad
                        from superior_facultad_area_tipo a
                        inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id

                        where 
                        a.codigo = '".$areacod."'
                        and b.id not in ('312','313','238','224','210','315')
                        order by a.codigo, b.codigo";
        
        $niveles= $db->prepare($query);
        $params = array();
        $niveles->execute($params);
        $niv = $niveles->fetchAll();    
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('message' => new JsonEncoder()));
        $jsonContent = $serializer->serialize($niv, 'json');
        echo $jsonContent;
        exit;
    }
    
    public function nivelchangeAction(Request $request, $nivelid) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
//        dump($nivelid);
//        die;
                
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        
        $query = "  select d.id as id, d.acreditacion as acreditacion
                    from superior_facultad_area_tipo a
                    inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                    inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                    inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                    where 
                    a.codigo in ('18','19','20','21','22','23','24','25','15')
                    and b.id = '".$nivelid."'
                    and b.id not in ('312','313','238','224','210','315')
                    and d.id not in ('59')
                    order by a.codigo, b.codigo, d.codigo";        
        $niveles= $db->prepare($query);
        $params = array();
        $niveles->execute($params);
        $niv = $niveles->fetchAll();    
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('message' => new JsonEncoder()));
        $jsonContent = $serializer->serialize($niv, 'json');
        echo $jsonContent;
        exit;
    }
    
    public function turnochangeAction(Request $request, $areacod, $nivelid, $etapaid, $turnoid) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }                
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        
        $query = "  select cast(p.id as integer) as id, p.paralelo, g.id as siepid
                     from superior_facultad_area_tipo a  
                        inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                            inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                                inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                                    inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                                        inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id 
                                            inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id 
                                                inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id=g.id                                     
                                                    inner join paralelo_tipo p on h.paralelo_tipo_id=p.id                    
                    where f.gestion_tipo_id=".$this->session->get('ie_gestion')." and f.institucioneducativa_id=".$this->session->get('ie_id')."  
                    and f.sucursal_tipo_id=".$this->session->get('ie_subcea')." and f.periodo_tipo_id=".$this->session->get('ie_per_cod')."
                    and a.codigo = ".$areacod." and b.id = ".$nivelid." and d.id = ".$etapaid." 
                    and h.turno_tipo_id = ".$turnoid."
                    order by id desc";
       
        $cursos= $db->prepare($query);
        $params = array();
        $cursos->execute($params);
        $cur = $cursos->fetchAll();
        //dump($query); die;
        if ($cur){                                  
            $count = count($cur);           
            $primerparalelo = $cur[$count-1]['id'];
            if ($primerparalelo <> '1'){                
                $para = $em->getRepository('SieAppWebBundle:ParaleloTipo')->find(intval($primerparalelo) - 1);
                $array[0] = ["id" => $para->getId().','.$cur[0]['siepid'],"paralelo" => $para->getParalelo()];   
            }
            else{
                $paraleloId = $cur[0]['id'];
                $para = $em->getRepository('SieAppWebBundle:ParaleloTipo')->find(intval($paraleloId) + 1);
                $array[0] = ["id" => $para->getId().','.$cur[0]['siepid'],"paralelo" => $para->getParalelo()];
            }
            
        }
        else{
            $para = $em->getRepository('SieAppWebBundle:ParaleloTipo')->find('1');
            $array[0] = ["id" => $para->getId().',-1',"paralelo" => $para->getParalelo()];
        }        
        
        
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('message' => new JsonEncoder()));
        $jsonContent = $serializer->serialize($array, 'json');
        echo $jsonContent;
        exit;
    }
    
    public function newAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $var = $request->request->all();
        $response = new JsonResponse();
        
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        
        $dat = explode(",", $var['paralelocod']);
//        $dat[0]; // paralelo
//        $dat[1]; // siepid        
//        dump($dat);
//        die;
        $em->getConnection()->beginTransaction();
        try {
//            dump($dat[1]);
//            die;
            if ($dat[1] == '-1'){
                $query = "
                            select c.id
                            from superior_facultad_area_tipo a 
                                inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                                    inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                                        inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                            where 
                             a.codigo = ".$var['areacod']." and b.id = ".$var['nivelcod']." and d.id = ".$var['etapacod']."

                        ";
                $sae= $db->prepare($query);
                $params = array();
                $sae->execute($params);
                $saeid = $sae->fetchAll();                
                
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_institucioneducativa_acreditacion');")->execute();
                $siea = new SuperiorInstitucioneducativaAcreditacion();                
                $siea->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
                $siea->setAcreditacionEspecialidad($em->getRepository('SieAppWebBundle:SuperiorAcreditacionEspecialidad')->find($saeid['0']['id']));
                $siea->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                $siea->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($this->session->get('ie_suc_id')));
                $siea->setSuperiorTurnoTipo($em->getRepository('SieAppWebBundle:SuperiorTurnoTipo')->find($var['turnocod']));
                $em->persist($siea);
                $em->flush();
                
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_institucioneducativa_periodo');")->execute();
                $siep = new SuperiorInstitucioneducativaPeriodo();                
                $siep->setSuperiorInstitucioneducativaAcreditacion($siea);
                $siep->setSuperiorPeriodoTipo($em->getRepository('SieAppWebBundle:SuperiorPeriodoTipo')->find('2'));              
                $em->persist($siep);
                $em->flush();
                
                if ($var['areacod'] == '15'){//id 13 HUMANISTICA
                    //MATEMATICAS
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                    $smp = new SuperiorModuloPeriodo();
                    $smp->setSuperiorModuloTipo($em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find('52419'));
                    $smp->setInstitucioneducativaPeriodo($siep);
                    $smp->setHorasModulo('0');
                    $em->persist($smp);
                    $em->flush();

                    //LENGUAJE
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                    $smp = new SuperiorModuloPeriodo();
                    $smp->setSuperiorModuloTipo($em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find('52420'));
                    $smp->setInstitucioneducativaPeriodo($siep);
                    $smp->setHorasModulo('0');
                    $em->persist($smp);
                    $em->flush();

                    //CIENCIAS SOCIALES
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                    $smp = new SuperiorModuloPeriodo();
                    $smp->setSuperiorModuloTipo($em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find('52421'));
                    $smp->setInstitucioneducativaPeriodo($siep);
                    $smp->setHorasModulo('0');
                    $em->persist($smp);
                    $em->flush();

                    //CIENCIAS NATURALES
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                    $smp = new SuperiorModuloPeriodo();
                    $smp->setSuperiorModuloTipo($em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find('52422'));
                    $smp->setInstitucioneducativaPeriodo($siep);
                    $smp->setHorasModulo('0');
                    $em->persist($smp);
                    $em->flush();

                    //IDIOMA ORIGINARIO
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_modulo_periodo');")->execute();
                    $smp = new SuperiorModuloPeriodo();
                    $smp->setSuperiorModuloTipo($em->getRepository('SieAppWebBundle:SuperiorModuloTipo')->find('52426'));
                    $smp->setInstitucioneducativaPeriodo($siep);
                    $smp->setHorasModulo('0');
                    $em->persist($smp);
                    $em->flush();
                }
//                else{//TECNICA

//                }
            }
            else{
                //$siea = $em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion')->find($saerow['0']['siaid']);
                $siep = $em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->find($dat[1]);
            }
            
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso');")->execute();
            $iec = new InstitucioneducativaCurso();
            $iec->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find('4'));
            $iec->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find('0'));
            $iec->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find('99'));
            $iec->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find($dat[0]));
            $iec->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
            $iec->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find('15'));
            $iec->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find('0'));
            $iec->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find($var['turnocod']));
            $iec->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
            $iec->setSuperiorInstitucioneducativaPeriodo($siep);
            $em->persist($iec);
            $em->flush();

            $em->getConnection()->commit();
            return $response->setData(array('mensaje'=>'¡Proceso realizado exitosamente!'));
            
        } catch (Exception $ex) {                       
            $em->getConnection()->rollback();            
            return $response->setData(array('mensaje'=>'Proceso detenido.'.$ex));
        }        
    }    
    
    public function cursosduplicadosAction() {              
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();        
        $query = "  select *
		from
			(select h.institucioneducativa_id, y.institucioneducativa, a.codigo as nivel, b.id as saeid, b.codigo as ciclo_id,  b.especialidad as ciclo,d.id as satId, d.codigo as grado_id,d.acreditacion as grado
			   ,h.grado_tipo_id                          
                          ,x.paralelo
                          ,h.turno_tipo_id as turnoCurso, count(h.id) as count
                          , array_agg(cast(h.id as varchar)) as iec
                                              
                           from superior_facultad_area_tipo a  
                              inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                                  inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                                      inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                                          inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                                              inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id 
                                                  inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id 
                                                      inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id=g.id
							inner join institucioneducativa y on h.institucioneducativa_id = y.id
                                                          inner join paralelo_tipo x on h.paralelo_tipo_id = x.id
                          where f.gestion_tipo_id=2016 and f.periodo_tipo_id=2
			group by h.institucioneducativa_id, y.institucioneducativa, a.codigo, b.id, b.codigo,  b.especialidad,d.id, d.codigo,d.acreditacion
			   ,h.grado_tipo_id                          
                          ,x.paralelo
                          ,h.turno_tipo_id	
                         order by h.turno_tipo_id, b.codigo) a
                         where a.count = 2";       
        $cursos= $db->prepare($query);
        $params = array();
        $cursos->execute($params);
        $cur = $cursos->fetchAll();
//        dump($cur);
//        die;        
        if ($cur){            
            $em->getConnection()->beginTransaction();
            try {
                foreach ($cur as $curso) {
                    $idcursos = $curso['iec'];
                    $porciones = explode(",", $idcursos);
                    
                    $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find(substr($porciones[0],1));
                                        
                    $query = "  select * from institucioneducativa_curso a
                                    where a.superior_institucioneducativa_periodo_id = '".$iec->getSuperiorInstitucioneducativaPeriodo()->getId()."'
                                    order by a.paralelo_tipo_id desc";       
                    $cursossp= $db->prepare($query);
                    $params = array();
                    $cursossp->execute($params);
                    $cursp = $cursossp->fetchAll();
                    
//                    dump($cursp);
//                    die;
                    
                    $newidpara = $cursp[0]['paralelo_tipo_id'];
                    $par = $em->getRepository('SieAppWebBundle:ParaleloTipo')->find($newidpara + 1);                    
                    $iec->setParaleloTipo($par);
//                    dump($par);
//                    die;
                    $em->persist($iec);
                    $em->flush();
                }
                $em->getConnection()->rollback();
                $message = '¡Proceso realizado exitosamente!';
            } catch (Exception $ex) {                       
                $em->getConnection()->rollback();            
                $message = 'Proceso detenido.';
            }
        }
        else{
            $message = 'No se detectó ningun curso duplicado. ;o)';
        }        
        $this->addFlash('messagecurdup', $message);
        return $this->render($this->session->get('pathSystem') . ':CursoAlternativa:respuestacursoduplicados.html.twig');
    }
    
    public function cursodeleteAction(Request $request) {
        //create the DB conexion
        $em= $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        //get the send values
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
//          dump($aInfoUeducativa['ueducativaInfoId']['nivelId']);die;
        $iecid = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        //dump($iecid);die;        
        $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecid);
        $response = new JsonResponse();
        //dump(count($iec)); die;        
        
        //dump(count($iecpercount));die;
        $em->getConnection()->beginTransaction();
        try {
            $iecpercount = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBySuperiorInstitucioneducativaPeriodo($iec->getSuperiorInstitucioneducativaPeriodo()->getId());    
            $dupcursover = $this->verificarcursoduplicado($aInfoUeducativa, $aInfoUeducativa['ueducativaInfoId']['iecId']);
            if ($dupcursover != '-1'){
                $iecdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($dupcursover);
                //BUSCANDO E ELIMINANDO CURSO OFERTA
                $iecofercurdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findByInsitucioneducativaCurso($dupcursover);
                if (count($iecofercurdup) > 0){ 
                    foreach ($iecofercurdup as $iecofercurduprow) {
                        //BUSCANDO E ELIMINANDO CURSO OFERTA MAESTRO
                        $iecofermaescurdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findByInstitucioneducativaCursoOferta($iecofercurduprow->getId());
                        if (count($iecofermaescurdup) > 0){ 
                            foreach ($iecofermaescurdup as $iecofermaescurduprow) {
                                $em->remove($iecofermaescurduprow);
                                $em->flush(); 
                            }
                        }    
                        $em->remove($iecofercurduprow);
                        $em->flush();             
                    }
                }

                $em->remove($iecdup);
                $em->flush();
            }else{
                    if (count($iecpercount) == 1 ){//BORRA  MODULO PERIODO Y ACREDITACION PUESTO QUE ES EL ULTIMO CURSO              
                        $smp = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->findByInstitucioneducativaPeriodo($iec->getSuperiorInstitucioneducativaPeriodo());
                        //dump($smp); die;
                        if ($aInfoUeducativa['ueducativaInfoId']['nivelId'] == '15'){
                            foreach ($smp as $smprow) {
                                $em->remove($smprow);
                                $em->flush();                
                            }
                        }
                        else{
                            if (count($smp) > 0){   
                                $em->getConnection()->rollback();
                                return $response->setData(array('mensaje'=>'No se puede eliminar el curso, la especialidad aun tiene registro de módulos.'));
                            }
                        }                
        //                dump($smp); die;                
                        $siep = $em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->find($iec->getSuperiorInstitucioneducativaPeriodo());
                        $sieca = $em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion')->find($siep->getSuperiorInstitucioneducativaAcreditacion());    
                        
                        //BUSCANDO E ELIMINANDO CURSO OFERTA
                        $iecofercurdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findByInsitucioneducativaCurso($iecid);
                        if (count($iecofercurdup) > 0){ 
                            foreach ($iecofercurdup as $iecofercurduprow) {
                                //BUSCANDO E ELIMINANDO CURSO OFERTA MAESTRO
                                $iecofermaescurdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findByInstitucioneducativaCursoOferta($iecofercurduprow->getId());
                                if (count($iecofermaescurdup) > 0){ 
                                    foreach ($iecofermaescurdup as $iecofermaescurduprow) {
                                        $em->remove($iecofermaescurduprow);
                                        $em->flush(); 
                                    }
                                }    
                                $em->remove($iecofercurduprow);
                                $em->flush();             
                            }
                        }
                        
                        //BORRA EL CURSO                        
                        $em->remove($iec);
                        $em->remove($siep);
                        $em->remove($sieca);
                        $em->flush();
                    }

                    if (count($iecpercount) > 1 ){//SOLO BORRA EL CURSO                        
                        //BUSCANDO E ELIMINANDO CURSO OFERTA
                        $iecofercurdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findByInsitucioneducativaCurso($iecid);
                        if (count($iecofercurdup) > 0){ 
                            foreach ($iecofercurdup as $iecofercurduprow) {
                                //BUSCANDO E ELIMINANDO CURSO OFERTA MAESTRO
                                $iecofermaescurdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findByInstitucioneducativaCursoOferta($iecofercurduprow->getId());
                                if (count($iecofermaescurdup) > 0){ 
                                    foreach ($iecofermaescurdup as $iecofermaescurduprow) {
                                        $em->remove($iecofermaescurduprow);
                                        $em->flush(); 
                                    }
                                }    
                                $em->remove($iecofercurduprow);
                                $em->flush();             
                            }
                        }

                        $em->remove($iec);
                        $em->flush();
                    }
            }            
            $em->getConnection()->commit();
            return $response->setData(array('mensaje'=>'¡Proceso realizado exitosamente!'));
        } catch (Exception $ex) {                       
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje'=>'Proceso detenido.'.$ex));
        }
    }

    public function verificarcursoduplicado($aInfoUeducativa, $idcurso) {
        //dump($aInfoUeducativa);die;
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();            
        $query = " select h.id as idcurso, f.gestion_tipo_id,f.periodo_tipo_id,e.institucioneducativa_id, stt.turno_superior, a.codigo as nivel_id, a.facultad_area,b.codigo as ciclo_id,b.especialidad,d.codigo as grado_id,d.acreditacion
                    ,pt.id as paralelo_id, pt.paralelo
                    --,j.codigo_rude, j.fecha_nacimiento,date_part('year',age(j.fecha_nacimiento)) as edad ,j.genero_tipo_id
                    from superior_facultad_area_tipo a  
                        inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                            inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                                inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                                    inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                                inner join	superior_turno_tipo stt on stt.id = e.superior_turno_tipo_id
                                        inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id			
                                            inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id 
                                                inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id=g.id 
                                    inner join paralelo_tipo pt on pt.id = h.paralelo_tipo_id
                                        --inner join estudiante_inscripcion i on h.id=i.institucioneducativa_curso_id 
                                            --inner join estudiante j on i.estudiante_id=j.id
                                                                        inner join superior_turno_tipo z on h.turno_tipo_id=z.id 
                                                inner join paralelo_tipo p on h.paralelo_tipo_id=p.id
                                                    inner join turno_tipo q on h.turno_tipo_id=q.id
                    ------
                    where f.gestion_tipo_id in (".$this->session->get('ie_gestion').") and f.periodo_tipo_id in ('".$this->session->get('ie_per_cod')."')
                    and h.institucioneducativa_id = '".$this->session->get('ie_id')."'
                    and a.codigo  = ".$aInfoUeducativa['ueducativaInfoId']['nivelId']."
                    and b.codigo  = ".$aInfoUeducativa['ueducativaInfoId']['cicloId']."
                    and d.codigo  = ".$aInfoUeducativa['ueducativaInfoId']['gradoId']."
                    and stt.turno_superior = '".$aInfoUeducativa['ueducativaInfo']['turno']."'
                    and pt.id = '".$aInfoUeducativa['ueducativaInfoId']['paraleloId']."'
                    and f.sucursal_tipo_id = ".$this->session->get('ie_subcea')."
                    ------
                    group by h.id, f.gestion_tipo_id,f.periodo_tipo_id,e.institucioneducativa_id,stt.turno_superior,a.codigo, a.facultad_area,b.codigo,b.especialidad,d.codigo,d.acreditacion,pt.id,pt.paralelo";
//        print_r($query); die;
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        //dump($po); die;     
        if (count($po) == 0){
            return '-1';
        }
        if (count($po) == 1){
            return '-1';
        }
        if (count($po) > 1){
            $idcur = '0';
            foreach($po as $reg){
                if ( $reg['idcurso'] != $idcurso)
                    $idcur = $reg['idcurso'];
            }
            return $idcur;
        }

    }
    
    public function periodosdintintosAction() {              
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();        
        $query = "  select smp.id, smp.institucioneducativa_periodo_id, iec.superior_institucioneducativa_periodo_id 
                    from superior_modulo_periodo smp
                    inner join institucioneducativa_curso_oferta ieco
                    on ieco.superior_modulo_periodo_id = smp.id
                    inner join institucioneducativa_curso iec on iec.id = ieco.insitucioneducativa_curso_id
                    inner join superior_institucioneducativa_periodo siep on siep.id =  iec.superior_institucioneducativa_periodo_id
                    where smp.institucioneducativa_periodo_id <> iec.superior_institucioneducativa_periodo_id 
                    group by smp.id, smp.institucioneducativa_periodo_id, iec.superior_institucioneducativa_periodo_id 
                    order by smp.institucioneducativa_periodo_id, iec.superior_institucioneducativa_periodo_id
                    ";       
        $cursos= $db->prepare($query);
        $params = array();
        $cursos->execute($params);
        $cur = $cursos->fetchAll();
//        dump($cur);
//        die;
        if ($cur){            
            $em->getConnection()->beginTransaction();
            try {
                foreach ($cur as $curso) {                                       
                    $smp = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->find($curso['id']);                    
                    $smp->setInstitucioneducativaPeriodo($em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->find($curso['superior_institucioneducativa_periodo_id']));
                    $em->persist($smp);
                    $em->flush();
                }
                $em->getConnection()->commit();
                $message = '¡Proceso realizado exitosamente!';
            } catch (Exception $ex) {                       
                $em->getConnection()->rollback();            
                $message = 'Proceso detenido.';
            }
        }
        else{
            $message = 'No se detectó diferencias entre oferta y modulo. ;o)';
        }    
        $this->addFlash('messagecurdup', $message);
        return $this->render($this->session->get('pathSystem') . ':CursoAlternativa:respuestacursoduplicados.html.twig');
    }
    
    public function auxunoAction() {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();  //TRAMITE DUPLICADOS      
        $query = "  select institucioneducativa_sucursal_id, count(*) as count, array_agg(cast(iest.id as varchar)) as ids

                    from institucioneducativa_sucursal ies 
                    inner join institucioneducativa_sucursal_tramite iest on ies.id = iest.institucioneducativa_sucursal_id


                    group by institucioneducativa_sucursal_id
                    order by count
                    ";       
        $cursos= $db->prepare($query);
        $params = array();
        $cursos->execute($params);
        $cur = $cursos->fetchAll();
//        dump($cur);
//        die;
        if ($cur){            
            $em->getConnection()->beginTransaction();
            try {
                foreach ($cur as $curso) {    
                    $idsdel = $curso['ids'];                    
                    $count = $curso['count'];                                     
                    if (intval($count) > 1) {            
                        $letters = array('{', '}');
                        $fruit   = array('', '');                        
                        $output  = str_replace($letters, $fruit, $idsdel);
                        $porciones = explode(",", $output);
                        $i = 1;
//                        dump(count($porciones));
//                        die;
                        while ($i <= count($porciones) - 1){                            
//                            dump($output.' '.$porciones[$i]);
//                            die;
                            $smp = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalTramite')->find($porciones[$i]);
//                            dump($smp);
//                            die;
                            $em->remove($smp);
                            $em->flush();
                            $i = $i + 1;
                        }   
                    }
                }
                $em->getConnection()->commit();
                $message = '¡Proceso realizado exitosamente!';
            } catch (Exception $ex) {                       
                $em->getConnection()->rollback();            
                $message = 'Proceso detenido.';
            }
        }
        else{
            $message = 'Resultado de la busqueda vacio. ;o)';
        }    
        $this->addFlash('messagecurdup', $message);
        return $this->render($this->session->get('pathSystem') . ':CursoAlternativa:respuestacursoduplicados.html.twig');
    }
}
