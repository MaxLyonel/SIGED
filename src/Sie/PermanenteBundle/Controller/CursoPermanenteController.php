<?php

namespace Sie\PermanenteBundle\Controller;

use Sie\AppWebBundle\Entity\PermanenteAreaTematicaTipo;
use Sie\AppWebBundle\Entity\PermanenteProgramaTipo;
use Sie\AppWebBundle\Entity\SuperiorAcreditacionEspecialidad;
use Sie\AppWebBundle\Entity\SuperiorEspecialidadTipo;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Sie\AppWebBundle\Entity\PermanenteCursocortoTipo;
use Sie\AppWebBundle\Entity\PermanenteInstitucioneducativaCursocorto;
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
class CursoPermanenteController extends Controller
{

    public $session;

    /**
     * the class constructorv§
     */
    public function __construct()
    {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction(Request $request)
    {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SiePermanenteBundle:CursoPermanente:index.html.twig');
    }

    public function areachangeAction(Request $request, $areacod)
    {
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
                        a.codigo = '" . $areacod . "'
                        and b.id not in ('312','313','238','224','210','315')
                        order by a.codigo, b.codigo";

        $niveles = $db->prepare($query);
        $params = array();
        $niveles->execute($params);
        $niv = $niveles->fetchAll();
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('message' => new JsonEncoder()));
        $jsonContent = $serializer->serialize($niv, 'json');
        echo $jsonContent;
        exit;
    }

    public function nivelchangeAction(Request $request, $nivelid)
    {
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
                    and b.id = '" . $nivelid . "'
                    and b.id not in ('312','313','238','224','210','315')
                    and d.id not in ('59')
                    order by a.codigo, b.codigo, d.codigo";
        $niveles = $db->prepare($query);
        $params = array();
        $niveles->execute($params);
        $niv = $niveles->fetchAll();
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('message' => new JsonEncoder()));
        $jsonContent = $serializer->serialize($niv, 'json');
        echo $jsonContent;
        exit;
    }

    public function turnochangeAction(Request $request, $areacod, $nivelid, $etapaid, $turnoid)
    {
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
                    where f.gestion_tipo_id=" . $this->session->get('ie_gestion') . " and f.institucioneducativa_id=" . $this->session->get('ie_id') . "  
                    and f.sucursal_tipo_id=" . $this->session->get('ie_subcea') . " and f.periodo_tipo_id=" . $this->session->get('ie_per_cod') . "
                    and a.codigo = " . $areacod . " and b.id = " . $nivelid . " and d.id = " . $etapaid . " 
                    and h.turno_tipo_id = " . $turnoid . "
                    order by id desc";

        $cursos = $db->prepare($query);
        $params = array();
        $cursos->execute($params);
        $cur = $cursos->fetchAll();
        //dump($query); die;
        if ($cur) {
            $count = count($cur);
            $primerparalelo = $cur[$count - 1]['id'];
            if ($primerparalelo <> '1') {
                $para = $em->getRepository('SieAppWebBundle:ParaleloTipo')->find(intval($primerparalelo) - 1);
                $array[0] = ["id" => $para->getId() . ',' . $cur[0]['siepid'], "paralelo" => $para->getParalelo()];
            } else {
                $paraleloId = $cur[0]['id'];
                $para = $em->getRepository('SieAppWebBundle:ParaleloTipo')->find(intval($paraleloId) + 1);
                $array[0] = ["id" => $para->getId() . ',' . $cur[0]['siepid'], "paralelo" => $para->getParalelo()];
            }

        } else {
            $para = $em->getRepository('SieAppWebBundle:ParaleloTipo')->find('1');
            $array[0] = ["id" => $para->getId() . ',-1', "paralelo" => $para->getParalelo()];
        }


        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('message' => new JsonEncoder()));
        $jsonContent = $serializer->serialize($array, 'json');
        echo $jsonContent;
        exit;
    }

    public function newAction(Request $request)
    {
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
            if ($dat[1] == '-1') {
                $query = "
                            select c.id
                            from superior_facultad_area_tipo a 
                                inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id
                                    inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id
                                        inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id
                            where 
                             a.codigo = " . $var['areacod'] . " and b.id = " . $var['nivelcod'] . " and d.id = " . $var['etapacod'] . "

                        ";
                $sae = $db->prepare($query);
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

                if ($var['areacod'] == '15') {//id 13 HUMANISTICA
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
            } else {
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
            return $response->setData(array('mensaje' => '¡Proceso realizado exitosamente!'));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido.' . $ex));
        }
    }

    public function cursosduplicadosAction()
    {
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
        $cursos = $db->prepare($query);
        $params = array();
        $cursos->execute($params);
        $cur = $cursos->fetchAll();
//        dump($cur);
//        die;        
        if ($cur) {
            $em->getConnection()->beginTransaction();
            try {
                foreach ($cur as $curso) {
                    $idcursos = $curso['iec'];
                    $porciones = explode(",", $idcursos);

                    $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find(substr($porciones[0], 1));

                    $query = "  select * from institucioneducativa_curso a
                                    where a.superior_institucioneducativa_periodo_id = '" . $iec->getSuperiorInstitucioneducativaPeriodo()->getId() . "'
                                    order by a.paralelo_tipo_id desc";
                    $cursossp = $db->prepare($query);
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
        } else {
            $message = 'No se detectó ningun curso duplicado. ;o)';
        }
        $this->addFlash('messagecurdup', $message);
        return $this->render($this->session->get('pathSystem') . ':CursoAlternativa:respuestacursoduplicados.html.twig');
    }

    public function cursodeleteAction(Request $request)
    {
        //create the DB conexion
        $em = $this->getDoctrine()->getManager();
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
            if ($dupcursover != '-1') {
                $iecdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($dupcursover);
                //BUSCANDO E ELIMINANDO CURSO OFERTA
                $iecofercurdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findByInsitucioneducativaCurso($dupcursover);
                if (count($iecofercurdup) > 0) {
                    foreach ($iecofercurdup as $iecofercurduprow) {
                        //BUSCANDO E ELIMINANDO CURSO OFERTA MAESTRO
                        $iecofermaescurdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findByInstitucioneducativaCursoOferta($iecofercurduprow->getId());
                        if (count($iecofermaescurdup) > 0) {
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
            } else {
                if (count($iecpercount) == 1) {//BORRA  MODULO PERIODO Y ACREDITACION PUESTO QUE ES EL ULTIMO CURSO
                    $smp = $em->getRepository('SieAppWebBundle:SuperiorModuloPeriodo')->findByInstitucioneducativaPeriodo($iec->getSuperiorInstitucioneducativaPeriodo());
                    //dump($smp); die;
                    if ($aInfoUeducativa['ueducativaInfoId']['nivelId'] == '15') {
                        foreach ($smp as $smprow) {
                            $em->remove($smprow);
                            $em->flush();
                        }
                    } else {
                        if (count($smp) > 0) {
                            $em->getConnection()->rollback();
                            return $response->setData(array('mensaje' => 'No se puede eliminar el curso, la especialidad aun tiene registro de módulos.'));
                        }
                    }
                    //                dump($smp); die;
                    $siep = $em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo')->find($iec->getSuperiorInstitucioneducativaPeriodo());
                    $sieca = $em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion')->find($siep->getSuperiorInstitucioneducativaAcreditacion());

                    //BUSCANDO E ELIMINANDO CURSO OFERTA
                    $iecofercurdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findByInsitucioneducativaCurso($iecid);
                    if (count($iecofercurdup) > 0) {
                        foreach ($iecofercurdup as $iecofercurduprow) {
                            //BUSCANDO E ELIMINANDO CURSO OFERTA MAESTRO
                            $iecofermaescurdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findByInstitucioneducativaCursoOferta($iecofercurduprow->getId());
                            if (count($iecofermaescurdup) > 0) {
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

                if (count($iecpercount) > 1) {//SOLO BORRA EL CURSO
                    //BUSCANDO E ELIMINANDO CURSO OFERTA
                    $iecofercurdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findByInsitucioneducativaCurso($iecid);
                    if (count($iecofercurdup) > 0) {
                        foreach ($iecofercurdup as $iecofercurduprow) {
                            //BUSCANDO E ELIMINANDO CURSO OFERTA MAESTRO
                            $iecofermaescurdup = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findByInstitucioneducativaCursoOferta($iecofercurduprow->getId());
                            if (count($iecofermaescurdup) > 0) {
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
            return $response->setData(array('mensaje' => '¡Proceso realizado exitosamente!'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido.' . $ex));
        }
    }

    public function verificarcursoduplicado($aInfoUeducativa, $idcurso)
    {
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
                    where f.gestion_tipo_id in (" . $this->session->get('ie_gestion') . ") and f.periodo_tipo_id in ('" . $this->session->get('ie_per_cod') . "')
                    and h.institucioneducativa_id = '" . $this->session->get('ie_id') . "'
                    and a.codigo  = " . $aInfoUeducativa['ueducativaInfoId']['nivelId'] . "
                    and b.codigo  = " . $aInfoUeducativa['ueducativaInfoId']['cicloId'] . "
                    and d.codigo  = " . $aInfoUeducativa['ueducativaInfoId']['gradoId'] . "
                    and stt.turno_superior = '" . $aInfoUeducativa['ueducativaInfo']['turno'] . "'
                    and pt.id = '" . $aInfoUeducativa['ueducativaInfoId']['paraleloId'] . "'
                    and f.sucursal_tipo_id = " . $this->session->get('ie_subcea') . "
                    ------
                    group by h.id, f.gestion_tipo_id,f.periodo_tipo_id,e.institucioneducativa_id,stt.turno_superior,a.codigo, a.facultad_area,b.codigo,b.especialidad,d.codigo,d.acreditacion,pt.id,pt.paralelo";
//        print_r($query); die;
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        //dump($po); die;     
        if (count($po) == 0) {
            return '-1';
        }
        if (count($po) == 1) {
            return '-1';
        }
        if (count($po) > 1) {
            $idcur = '0';
            foreach ($po as $reg) {
                if ($reg['idcurso'] != $idcurso)
                    $idcur = $reg['idcurso'];
            }
            return $idcur;
        }

    }

    public function periodosdintintosAction()
    {
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
        $cursos = $db->prepare($query);
        $params = array();
        $cursos->execute($params);
        $cur = $cursos->fetchAll();
//        dump($cur);
//        die;
        if ($cur) {
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
        } else {
            $message = 'No se detectó diferencias entre oferta y modulo. ;o)';
        }
        $this->addFlash('messagecurdup', $message);
        return $this->render($this->session->get('pathSystem') . ':CursoAlternativa:respuestacursoduplicados.html.twig');
    }

    public function auxunoAction()
    {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();  //TRAMITE DUPLICADOS      
        $query = "  select institucioneducativa_sucursal_id, count(*) as count, array_agg(cast(iest.id as varchar)) as ids

                    from institucioneducativa_sucursal ies 
                    inner join institucioneducativa_sucursal_tramite iest on ies.id = iest.institucioneducativa_sucursal_id


                    group by institucioneducativa_sucursal_id
                    order by count
                    ";
        $cursos = $db->prepare($query);
        $params = array();
        $cursos->execute($params);
        $cur = $cursos->fetchAll();
//        dump($cur);
//        die;
        if ($cur) {
            $em->getConnection()->beginTransaction();
            try {
                foreach ($cur as $curso) {
                    $idsdel = $curso['ids'];
                    $count = $curso['count'];
                    if (intval($count) > 1) {
                        $letters = array('{', '}');
                        $fruit = array('', '');
                        $output = str_replace($letters, $fruit, $idsdel);
                        $porciones = explode(",", $output);
                        $i = 1;
//                        dump(count($porciones));
//                        die;
                        while ($i <= count($porciones) - 1) {
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
        } else {
            $message = 'Resultado de la busqueda vacio. ;o)';
        }
        $this->addFlash('messagecurdup', $message);
        return $this->render($this->session->get('pathSystem') . ':CursoAlternativa:respuestacursoduplicados.html.twig');
    }


    public function admincursosAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $cursosCortos = $em->getRepository('SieAppWebBundle:PermanenteCursocortoTipo')->findAll();
        $areatematica = $em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->findAll();
        $programa = $em->getRepository('SieAppWebBundle:PermanenteProgramaTipo')->findAll();
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_per_add_curso_nuevo'))
            ->add('curso', 'text', array('required' => true, 'attr' => array('class' => 'form-control', 'enabled' => true)))
            ->add('programa', 'text', array('required' => true, 'attr' => array('class' => 'form-control', 'enabled' => true)))
            ->add('area', 'text', array('required' => true, 'attr' => array('class' => 'form-control', 'enabled' => true)))
            // ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'disbled' => true)))
            ->getForm();

        return $this->render('SiePermanenteBundle:CursoPermanente:admincursos.html.twig', array(
            'cursoscortos' => $cursosCortos,
            'programaper' => $programa,
            'areatematicaper' => $areatematica,
            'form' => $form->createView()
        ));
    }

    public function addProgramaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $form = $request->get('form');
        $programa = $form['programa'];
        $query = $em->getConnection()->prepare('
        select * from permanente_programa_tipo where programa =:programa
        ');
        $query->bindValue(':programa', $programa);
        $query->execute();

        $programafinal = $query->fetchAll();
        //   dump($programafinal);die;
        $reinicio = true;
        if ($programafinal == null || $programafinal == "") {
            $programatipo = new PermanenteProgramaTipo();
            $programatipo->setPrograma($form['programa']);
            //   dump($programatipo);die;
            $em->persist($programatipo);
            $em->flush();

            $em->getConnection()->commit();

            $response = new JsonResponse();
            return $response->setData(array('mensaje' => 'Guardado con exito, Nuevo Programa añadido',
                'reinicio' => $reinicio));
        } else {
            $reinicio = false;
            $response = new JsonResponse();
            return $response->setData(array('mensaje' => 'Error al guardar, El nombre del Programa ya Existe',
                'reinicio' => $reinicio
            ));
        }
    }

    public function addAreaTematicaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $form = $request->get('form');
        $area = $form['area'];
        $query = $em->getConnection()->prepare('
        select * from permanente_area_tematica_tipo where areatematica =:area
        ');
        $query->bindValue(':area', $area);
        $query->execute();

        $areafinal = $query->fetchAll();
        //   dump($programafinal);die;
        $reinicio = true;
        if ($areafinal == null || $areafinal == "") {
            $areatipo = new PermanenteAreaTematicaTipo();
            $areatipo->setAreatematica($form['area']);
            //   dump($programatipo);die;
            $em->persist($areatipo);
            $em->flush();

            $em->getConnection()->commit();

            $response = new JsonResponse();
            return $response->setData(array('mensaje' => 'Guardado con exito, Nueva Area Tematica añadida',
                'reinicio' => $reinicio));
        } else {
            $reinicio = false;
            $response = new JsonResponse();
            return $response->setData(array('mensaje' => 'Error al guardar, El nombre del Area Tematica ya Existe',
                'reinicio' => $reinicio
            ));
        }
    }




    public function showCursoNuevoAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_per_add_curso_nuevo'))

            ->add('curso', 'text', array('required' => true, 'attr' => array('class' => 'form-control', 'enabled' => true)))
            ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true)))
            ->getForm();
        return $this->render('SiePermanenteBundle:CursoPermanente:newcursos.html.twig', array(

            'form' => $form->createView()

        ));


    }

    public function addCursoNuevoAction(Request $request)
    {

        //dump($request);die;

        $em = $this->getDoctrine()->getManager();


        $form = $request->get('form');
        $curso = $form['curso'];

        $query = $em->getConnection()->prepare('
        select * from permanente_cursocorto_tipo where cursocorto =:curso
        ');
        $query->bindValue(':curso', $curso);
        $query->execute();
        $cursofinal = $query->fetchAll();
        //dump($cursofinal);die;
        // $reinicio = true;
        $em->getConnection()->beginTransaction();
        if ($cursofinal == null || $cursofinal == "") {
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('permanente_cursocorto_tipo');")->execute();

            $cursocortotipo = new PermanenteCursocortoTipo();
            $cursocortotipo->setCursocorto($form['curso']);
            // dump($cursocortotipo);die;
            $em->persist($cursocortotipo);
            $em->flush();

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        } else {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        }


        // return $response->setData(array('mensaje'=>'¡Proceso realizado exitosamente!'));


        //     return $response->setData(array(0=>array('id'=>$idcurso, 'nombre'=>$nombrecurso)));
//        return $response->setData(array(
//            'curso' => array('id'=>$idcurso, 'nombre'=>$nombrecurso),
//        ));
    }

    public function showCursoEditAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $idc = $request->get('form');
        $cursocorto =$em->getRepository('SieAppWebBundle:PermanenteCursocortoTipo')->find($idc);
        //$nombrecurso=$em->getRepository('SieAppWebBundle:PermanenteCursocortoTipo')->findOneBy($cursocorto->getCursocorto());
        $nombrecurso=$cursocorto->getCursocorto();
    //dump($qwe);die;

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_per_edit_curso_nuevo'))
            ->add('idc', 'hidden', array('data' => $idc))
            ->add('curso', 'text', array('required' => true, 'data' =>$nombrecurso ,'attr' => array('class' => 'form-control', 'enabled' => true)))
            ->add('guardarb', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'disbled' => true)))
            ->getForm();
        return $this->render('SiePermanenteBundle:CursoPermanente:editcursos.html.twig', array(

            'form' => $form->createView()

        ));


    }
    public function editCursoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $curso=$form['curso'];
        $idcurso=$form['idc'];
        //
        //dump($form);die;

        $em->getConnection()->beginTransaction();
        if ($curso != null || $curso != "") {
            $cursocorto =$em->getRepository('SieAppWebBundle:PermanenteCursocortoTipo')->find($idcurso);
            $cursocorto->setCursocorto($curso);
            //  dump($cursocorto);die;
            $em->persist($cursocorto);
            $em->flush();

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        } else {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        }







    }
    public function deleteCursoAction(Request $request)
    {
      //  dump($request);die;
        $em = $this->getDoctrine()->getManager();

        $idc = $request->get('form');

        $em->getConnection()->beginTransaction();
        $reinicio = true;

        $query = $em->getConnection()->prepare('
        select * from permanente_institucioneducativa_cursocorto where cursocorto_tipo_id =:curso
        ');

        $query->bindValue(':curso', $idc);
        $query->execute();
        $cursofinal = $query->fetchAll();
       // dump($cursofinal);die;
        if($cursofinal==null || $cursofinal=='')
        {

            $cursocorto =$em->getRepository('SieAppWebBundle:PermanenteCursocortoTipo')->find($idc);
           // dump($cursocorto);die;
            $em->remove($cursocorto);
           // $em->persist($institucioncursocorto);
            $em->flush();
            $em->getConnection()->commit();
            //$em->getConnection()->commit();
            $response = new JsonResponse();
                    return $response->setData(array('mensaje' => 'Curso Eliminado',
                'reinicio' => $reinicio));
        }
        else
        {$em->getConnection()->rollback();
            $reinicio = false;
            $response = new JsonResponse();
            return $response->setData(array('mensaje' => 'Error al eliminar el curso, Verifique que no se este utilizando por un CEA',
                'reinicio' => $reinicio));

        }

    }

    public function showProgramaNuevoAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_per_add_programa'))

            ->add('programa', 'text', array('required' => true, 'attr' => array('class' => 'form-control', 'enabled' => true)))
            ->add('guardarb', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true)))
            ->getForm();
        return $this->render('SiePermanenteBundle:CursoPermanente:newprogramas.html.twig', array(

            'form' => $form->createView()

        ));


    }
    public function addProgramaNuevoAction(Request $request)
    {

        //dump($request);die;

        $em = $this->getDoctrine()->getManager();


        $form = $request->get('form');
        $programa = $form['programa'];

        $query = $em->getConnection()->prepare('
        select * from permanente_programa_tipo where programa =:programa
        ');
        $query->bindValue(':programa', $programa);
        $query->execute();
        $programafinal = $query->fetchAll();
        //dump($cursofinal);die;
        // $reinicio = true;
        $em->getConnection()->beginTransaction();
        if ($programafinal == null || $programafinal == "") {
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('permanente_programa_tipo');")->execute();

            $programatipo = new PermanenteProgramaTipo();
            $programatipo->setPrograma($form['programa']);
            // dump($cursocortotipo);die;
            $em->persist($programatipo);
            $em->flush();

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        } else {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        }


        // return $response->setData(array('mensaje'=>'¡Proceso realizado exitosamente!'));


        //     return $response->setData(array(0=>array('id'=>$idcurso, 'nombre'=>$nombrecurso)));
//        return $response->setData(array(
//            'curso' => array('id'=>$idcurso, 'nombre'=>$nombrecurso),
//        ));
    }
    public function showProgramaEditAction(Request $request)
    {
       // dump($request);die;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $idc = $request->get('form');
        $programa =$em->getRepository('SieAppWebBundle:PermanenteProgramaTipo')->find($idc);
        //$nombrecurso=$em->getRepository('SieAppWebBundle:PermanenteCursocortoTipo')->findOneBy($cursocorto->getCursocorto());
        $nombreprograma=$programa->getPrograma();
        //dump($qwe);die;

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_per_edit_programa'))
            ->add('idc', 'hidden', array('data' => $idc))
            ->add('programa', 'text', array('required' => true, 'data' =>$nombreprograma ,'attr' => array('class' => 'form-control', 'enabled' => true)))
            ->add('guardarb', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'disbled' => true)))
            ->getForm();
        return $this->render('SiePermanenteBundle:CursoPermanente:editprogramas.html.twig', array(

            'form' => $form->createView()

        ));


    }
    public function editProgramaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $programa=$form['programa'];
        $idprograma=$form['idc'];
        //
        //dump($form);die;

        $em->getConnection()->beginTransaction();
        if ($programa != null || $programa != "") {
            $programafinal =$em->getRepository('SieAppWebBundle:PermanenteProgramaTipo')->find($idprograma);
            $programafinal->setPrograma($programa);
            //  dump($cursocorto);die;
            $em->persist($programafinal);
            $em->flush();

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        } else {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        }







    }
    public function deleteProgramaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $idc = $request->get('form');

//        $em->getConnection()->beginTransaction();
        $reinicio = true;
//dump( $idc);die;
        $query = $em->getConnection()->prepare('
        select * from permanente_institucioneducativa_cursocorto where programa_tipo_id =:programa
        ');

        $query->bindValue(':programa', $idc);
        $query->execute();
        $programafinal = $query->fetchAll();
      //   dump($programafinal);die;

        if($programafinal==null || $programafinal=='')
        {
            $programas=$em->getRepository('SieAppWebBundle:PermanenteProgramaTipo')->find($idc);

            $em->remove($programas);
            $em->flush();
            $em->getConnection()->commit();
          //  dump($programas);die;
           // $em->remove($programas);
          //  $em->persist($programas);
         //   $em->flush();
//            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('mensaje' => 'Programa Eliminado',
                'reinicio' => $reinicio));
        }
        else
        {
//            $em->getConnection()->rollback();
            $reinicio = false;
            $response = new JsonResponse();
            return $response->setData(array('mensaje' => 'Error al eliminar el programa, Verifique que no se este utilizando por un CEA',
                'reinicio' => $reinicio));

        }

    }


    public function showAreaTemNuevoAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_per_add_areatem'))

            ->add('area', 'text', array('required' => true, 'attr' => array('class' => 'form-control', 'enabled' => true)))
            ->add('guardarc', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true)))
            ->getForm();
        return $this->render('SiePermanenteBundle:CursoPermanente:newareas.html.twig', array(

            'form' => $form->createView()

        ));
    }

    public function adminEspecialidadesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = " 	select ---sae.id, 
                      sest.id, sest.especialidad,
                                sum (case when sat.id = 1 then 1 else 0 end) tecnicobasico,
                                sum (case when sat.id = 20 then 1 else 0 end) tecnicoauxiliar,
                                sum (case when sat.id = 32 then 1 else 0 end) tecnicomedio
                            from superior_acreditacion_especialidad sae
		                      inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			                    inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				                    inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					        where sat.id in (1,20,32) and sfat.id=40
                            group by 
                            sest.id, sest.especialidad
                            order by sest.especialidad ";
        $especialidades = $db->prepare($query);
        $params = array();
        $especialidades->execute($params);
        $esp = $especialidades->fetchAll();

        if (count($esp) > 0){
            $existesp = true;
        }
        else {
            $existesp = false;
        }
        // dump($esp);die;
        return $this->render('SiePermanenteBundle:CursoPermanente:adminespecialidades.html.twig', array(
            'especialidades' => $esp,
            'cantesp'=>count($esp),
            'existeesp'=>$existesp

        ));
    }




    public function showEspecialidadNuevoAction(Request $request)
    {
        $form = $this->createFormBuilder()
           // ->setAction($this->generateUrl('herramienta_per_add_areatem'))

            ->add('especialidad', 'text', array('required' => true, 'attr' => array('class' => 'form-control', 'enabled' => true,'style' => 'text-transform:uppercase')))
            ->add('tecbas', 'checkbox', array('label' => 'Tecnico Basico','required'=>false))
            ->add('tecaux', 'checkbox', array('label' => 'Tecnico Auxiliar','required'=>false))
            ->add('tecmed', 'checkbox', array('label' => 'Tecnico Medio','required'=>false))
            ->add('guardar', 'button', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true, 'onclick'=>'guardarEspecialidadP();')))
            ->getForm();
        return $this->render('SiePermanenteBundle:CursoPermanente:nuevaespecialidad.html.twig', array(

            'form' => $form->createView()

        ));
    }

    public function createEspecialidadNuevoAction(Request $request)
    {
              //  dump($request);die;
        $form = $request->get('form');
      //  dump($form);
        if (isset($form['tecbas'])){
            $tecbas = 1;
        }else{
            $tecbas = 0;
        }
        if (isset($form['tecaux'])){
            $tecaux = 1;
        }else{
            $tecaux = 0;
        }
        if (isset($form['tecmed'])){
            $tecmed = 1;
        }else{
            $tecmed = 0;
        }
        $especialidad = strtoupper($form['especialidad']);     // dump($tecbas); dump($tecaux); dump($tecmed);die;
       // dump($request);die;
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_especialidad_tipo');")->execute();
            $especialidadTipo = new SuperiorEspecialidadTipo();
            $especialidadTipo ->setCodigo(50);
            $especialidadTipo ->setEspecialidad($especialidad);
            $especialidadTipo ->setSuperiorFacultadAreaTipo($em->getRepository('SieAppWebBundle:SuperiorFacultadAreaTipo')->find(40));
            $em->persist($especialidadTipo);
            $em->flush($especialidadTipo);

           //dump($especialidadTipo);die;
            if($tecbas==1)
            {
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_acreditacion_especialidad');")->execute();
                $acreditacionEspecialidad= new SuperiorAcreditacionEspecialidad();
                $acreditacionEspecialidad ->setSuperiorEspecialidadTipo($especialidadTipo);
                $acreditacionEspecialidad ->setSuperiorAcreditacionTipo($em->getRepository('SieAppWebBundle:SuperiorAcreditacionTipo')->find(1));
                $em->persist($acreditacionEspecialidad);
                $em->flush($acreditacionEspecialidad);

            }
            if($tecaux==1)
            {
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_acreditacion_especialidad');")->execute();
                $acreditacionEspecialidad= new SuperiorAcreditacionEspecialidad();
                $acreditacionEspecialidad ->setSuperiorEspecialidadTipo($especialidadTipo);
                $acreditacionEspecialidad ->setSuperiorAcreditacionTipo($em->getRepository('SieAppWebBundle:SuperiorAcreditacionTipo')->find(20));
                $em->persist($acreditacionEspecialidad);
                $em->flush($acreditacionEspecialidad);
            }
            if($tecmed==1)
            {
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_acreditacion_especialidad');")->execute();
                $acreditacionEspecialidad= new SuperiorAcreditacionEspecialidad();
                $acreditacionEspecialidad ->setSuperiorEspecialidadTipo($especialidadTipo);
                $acreditacionEspecialidad ->setSuperiorAcreditacionTipo($em->getRepository('SieAppWebBundle:SuperiorAcreditacionTipo')->find(32));
                $em->persist($acreditacionEspecialidad);
                $em->flush($acreditacionEspecialidad);
            }

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            $db = $em->getConnection();
            $query = " 	select ---sae.id, 
                      sest.id, sest.especialidad,
                                sum (case when sat.id = 1 then 1 else 0 end) tecnicobasico,
                                sum (case when sat.id = 20 then 1 else 0 end) tecnicoauxiliar,
                                sum (case when sat.id = 32 then 1 else 0 end) tecnicomedio
                            from superior_acreditacion_especialidad sae
		                      inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			                    inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				                    inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					        where sat.id in (1,20,32) and sfat.id=40
                            group by 
                            sest.id, sest.especialidad
                            order by sest.especialidad ";
            $especialidades = $db->prepare($query);
            $params = array();
            $especialidades->execute($params);
            $esp = $especialidades->fetchAll();

            if (count($esp) > 0){
                $existesp = true;
            }
            else {
                $existesp = false;
            }
            // dump($esp);die;
            return $this->render('SiePermanenteBundle:CursoPermanente:listEspecialidades.html.twig', array(
                'especialidades' => $esp,
                'cantesp'=>count($esp),
                'existeesp'=>$existesp

            ));


//            return $this->redirect($this->generateUrl('herramienta_per_cursos_cortos_index'));

        }
        catch(Exception $ex)
        {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        }
//dump($especialidad);die;
        // return $this->render('SiePermanenteBundle:CursoPermanente:nuevaespecialidad.html.twig', array(

           // 'form' => $form->createView()

     //   ));
    }

    public function showEspecialidadEditAction(Request $request)
    {  $em = $this->getDoctrine()->getManager();
       // dump($request);die;
        $idesp = $request->get('idesp');
        $especialidad=$em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->findOneById($idesp);

        $esp=$especialidad->getEspecialidad();
        //dump($esp);die;
        $tecbas = $request->get('tecbas');
        $tecaux = $request->get('tecaux');
        $tecmed = $request->get('tecmed');
        if($tecbas==1){$tb=true;}else{$tb=false;}
        if($tecaux==1){$ta=true;}else{$ta=false;}
        if($tecmed==1){$tm=true;}else{$tm=false;}

        $form = $this->createFormBuilder()
            // ->setAction($this->generateUrl('herramienta_per_add_areatem'))

            ->add('especialidad', 'text', array('required' => true,'data' => $especialidad->getEspecialidad(), 'attr' => array('class' => 'form-control', 'enabled' => true,'style' => 'text-transform:uppercase')))
            ->add('tecbas', 'checkbox', array('label' => 'Tecnico Basico','data'=>$tb,'required'=>false))
            ->add('tecaux', 'checkbox', array('label' => 'Tecnico Auxiliar','data'=>$ta,'required'=>false))
            ->add('tecmed', 'checkbox', array('label' => 'Tecnico Medio','data'=>$tm,'required'=>false))
            ->add('guardar', 'button', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'enabled' => true, 'onclick'=>'updateEspecialidad();')))
            ->add('idesp', 'hidden', array('data' => $idesp))
            ->getForm();
        return $this->render('SiePermanenteBundle:CursoPermanente:editarespecialidad.html.twig', array(

            'form' => $form->createView()

        ));
    }

    public function updateEspecialidadEditAction(Request $request)
    {
        //  dump($request);die;
        $form = $request->get('form');
        // dump($form);
        if (isset($form['tecbas'])){
            $tecbas = 1;
        }else{
            $tecbas = 0;
        }
        if (isset($form['tecaux'])){
            $tecaux = 1;
        }else{
            $tecaux = 0;
        }
        if (isset($form['tecmed'])){
            $tecmed = 1;
        }else{
            $tecmed = 0;
        }
        $especialidad = strtoupper($form['especialidad']);     // dump($tecbas); dump($tecaux); dump($tecmed);die;

     //   dump($especialidad);
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $especialidadTipo = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->findOneBy(array('id' => $form['idesp']));
          //  dump($especialidadTipo);die;
          //  $especialidadTipo ->getEspecialidadTipo ($em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->findOneBy(array('id' => $form['idesp'])));
           // $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_especialidad_tipo');")->execute();
       //  dump($especialidadTipo);die;
           // $especialidadTipo = new SuperiorEspecialidadTipo();
            $especialidadTipo ->setEspecialidad($especialidad);
            $em->persist($especialidadTipo);
            $em->flush($especialidadTipo);

         //   dump($especialidadTipo);
            $em = $this->getDoctrine()->getManager();
            $db = $em->getConnection();
            $query = " 	select ---sae.id, 
                      sest.id, sest.especialidad,
                                sum (case when sat.id = 1 then 1 else 0 end) tecnicobasico,
                                sum (case when sat.id = 20 then 1 else 0 end) tecnicoauxiliar,
                                sum (case when sat.id = 32 then 1 else 0 end) tecnicomedio
                            from superior_acreditacion_especialidad sae
		                      inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			                    inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				                    inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					        where sat.id in (1,20,32) and sfat.id=40 and sest.id= ".$form['idesp']."
                            group by 
                            sest.id, sest.especialidad
                            order by sest.especialidad ";
            $especialidades = $db->prepare($query);
            $params = array();
            $especialidades->execute($params);
            $esp = $especialidades->fetch();

          //  dump($esp);die;

            if($tecbas!=$esp['tecnicobasico'])
            {
                if($tecbas==1)// dehe hacer un insert
                {
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_acreditacion_especialidad');")->execute();
                    $acreditacionEspecialidad= new SuperiorAcreditacionEspecialidad();
                    $acreditacionEspecialidad ->setSuperiorEspecialidadTipo($especialidadTipo);
                    $acreditacionEspecialidad ->setSuperiorAcreditacionTipo($em->getRepository('SieAppWebBundle:SuperiorAcreditacionTipo')->find(1));
                    $em->persist($acreditacionEspecialidad);
                    $em->flush($acreditacionEspecialidad);
                }else // debe hacer un delete
                {
                    $query = " select * from  superior_acreditacion_especialidad 
                                where superior_especialidad_tipo_id = ".$form['idesp']."
                                and superior_acreditacion_tipo_id=1
				";
                    $acred = $db->prepare($query);
                    $params = array();
                    $acred->execute($params);
                    $acreditacionesp = $acred->fetch();
                    $acreditacionEspecialidad = $em->getRepository('SieAppWebBundle:SuperiorAcreditacionEspecialidad')->findOneBy(array('id' => $acreditacionesp['id']));
                    $em->remove($acreditacionEspecialidad);
                    $em->flush();

                }
            }
            if($tecaux!=$esp['tecnicoauxiliar'])
            {
                if($tecaux==1)// dehe hacer un insert
                {
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_acreditacion_especialidad');")->execute();
                    $acreditacionEspecialidad= new SuperiorAcreditacionEspecialidad();
                    $acreditacionEspecialidad ->setSuperiorEspecialidadTipo($especialidadTipo);
                    $acreditacionEspecialidad ->setSuperiorAcreditacionTipo($em->getRepository('SieAppWebBundle:SuperiorAcreditacionTipo')->find(20));
                    $em->persist($acreditacionEspecialidad);
                    $em->flush($acreditacionEspecialidad);
                }else // debe hacer un delete
                {
                    $query = " select * from  superior_acreditacion_especialidad 
                                where superior_especialidad_tipo_id = ".$form['idesp']."
                                and superior_acreditacion_tipo_id=20
				";
                    $acred = $db->prepare($query);
                    $params = array();
                    $acred->execute($params);
                    $acreditacionesp = $acred->fetch();
                    $acreditacionEspecialidad = $em->getRepository('SieAppWebBundle:SuperiorAcreditacionEspecialidad')->findOneBy(array('id' => $acreditacionesp['id']));
                    $em->remove($acreditacionEspecialidad);
                    $em->flush();

                }
            }
            if($tecmed!=$esp['tecnicomedio'])
            {
                if($tecmed==1)// dehe hacer un insert
                {
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('superior_acreditacion_especialidad');")->execute();
                    $acreditacionEspecialidad= new SuperiorAcreditacionEspecialidad();
                    $acreditacionEspecialidad ->setSuperiorEspecialidadTipo($especialidadTipo);
                    $acreditacionEspecialidad ->setSuperiorAcreditacionTipo($em->getRepository('SieAppWebBundle:SuperiorAcreditacionTipo')->find(32));
                    $em->persist($acreditacionEspecialidad);
                    $em->flush($acreditacionEspecialidad);
                }else // debe hacer un delete
                {
                    $query = " select * from  superior_acreditacion_especialidad 
                                where superior_especialidad_tipo_id = ".$form['idesp']."
                                and superior_acreditacion_tipo_id=32
				";
                    $acred = $db->prepare($query);
                    $params = array();
                    $acred->execute($params);
                    $acreditacionesp = $acred->fetch();
                    $acreditacionEspecialidad = $em->getRepository('SieAppWebBundle:SuperiorAcreditacionEspecialidad')->findOneBy(array('id' => $acreditacionesp['id']));
                    $em->remove($acreditacionEspecialidad);
                    $em->flush();

                }
            }

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            $db = $em->getConnection();
            $query = " 	select ---sae.id, 
                      sest.id, sest.especialidad,
                                sum (case when sat.id = 1 then 1 else 0 end) tecnicobasico,
                                sum (case when sat.id = 20 then 1 else 0 end) tecnicoauxiliar,
                                sum (case when sat.id = 32 then 1 else 0 end) tecnicomedio
                            from superior_acreditacion_especialidad sae
		                      inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			                    inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				                    inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					        where sat.id in (1,20,32) and sfat.id=40
                            group by 
                            sest.id, sest.especialidad
                            order by sest.especialidad ";
            $especialidades = $db->prepare($query);
            $params = array();
            $especialidades->execute($params);
            $esp = $especialidades->fetchAll();

            if (count($esp) > 0){
                $existesp = true;
            }
            else {
                $existesp = false;
            }
            // dump($esp);die;
            return $this->render('SiePermanenteBundle:CursoPermanente:listEspecialidades.html.twig', array(
                'especialidades' => $esp,
                'cantesp'=>count($esp),
                'existeesp'=>$existesp

            ));


//            return $this->redirect($this->generateUrl('herramienta_per_cursos_cortos_index'));

        }
        catch(Exception $ex)
        {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        }
//dump($especialidad);die;
        // return $this->render('SiePermanenteBundle:CursoPermanente:nuevaespecialidad.html.twig', array(

        // 'form' => $form->createView()

        //   ));
    }

    public function deleteEspAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $idc = $request->get('form');


        $reinicio = true;

        $query = $em->getConnection()->prepare('
        select * from permanente_institucioneducativa_cursocorto where areatematica_tipo_id =:area
        ');

        $query->bindValue(':area', $idc);
        $query->execute();
        $areafinal = $query->fetchAll();
        //   dump($programafinal);die;

        if($areafinal==null || $areafinal=='')
        {
            $areas=$em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->find($idc);

            $em->remove($areas);
            $em->flush();
            $em->getConnection()->commit();

            $response = new JsonResponse();
            return $response->setData(array('mensaje' => 'Area Tematica Eliminada',
                'reinicio' => $reinicio));
        }
        else
        {
//
            $reinicio = false;
            $response = new JsonResponse();
            return $response->setData(array('mensaje' => 'Error al eliminar el area tematica, Verifique que no se este utilizando por un CEA',
                'reinicio' => $reinicio));
        }

    }




    public function addAreaTemNuevoAction(Request $request)
    {

        //dump($request);die;

        $em = $this->getDoctrine()->getManager();


        $form = $request->get('form');
        $area = $form['area'];

        $query = $em->getConnection()->prepare('
        select * from permanente_area_tematica_tipo where areatematica =:area
        ');
        $query->bindValue(':area', $area);
        $query->execute();
        $areafinal = $query->fetchAll();
        //dump($cursofinal);die;
        // $reinicio = true;
        $em->getConnection()->beginTransaction();
        if ($areafinal == null || $areafinal == "") {
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('permanente_area_tematica_tipo');")->execute();

            $areatipo = new PermanenteAreaTematicaTipo();
            $areatipo->setAreatematica($form['area']);
            // dump($cursocortotipo);die;
            $em->persist($areatipo);
            $em->flush();

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        } else {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        }


        // return $response->setData(array('mensaje'=>'¡Proceso realizado exitosamente!'));


        //     return $response->setData(array(0=>array('id'=>$idcurso, 'nombre'=>$nombrecurso)));
//        return $response->setData(array(
//            'curso' => array('id'=>$idcurso, 'nombre'=>$nombrecurso),
//        ));
    }
    public function showAreaTemEditAction(Request $request)
    {
        // dump($request);die;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $idc = $request->get('form');
        $area =$em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->find($idc);
        //$nombrecurso=$em->getRepository('SieAppWebBundle:PermanenteCursocortoTipo')->findOneBy($cursocorto->getCursocorto());
        $nombrearea=$area->getAreatematica();
        //dump($qwe);die;

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_per_edit_areatem'))
            ->add('idc', 'hidden', array('data' => $idc))
            ->add('area', 'text', array('required' => true, 'data' =>$nombrearea ,'attr' => array('class' => 'form-control', 'enabled' => true)))
            ->add('guardarc', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'disbled' => true)))
            ->getForm();
        return $this->render('SiePermanenteBundle:CursoPermanente:editareas.html.twig', array(

            'form' => $form->createView()

        ));


    }
    public function editAreaTemAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $area=$form['area'];
        $idarea=$form['idc'];
        //
        //dump($form);die;

        $em->getConnection()->beginTransaction();
        if ($area != null || $area != "") {
            $areafinal =$em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->find($idarea);
            $areafinal->setAreatematica($area);
            //  dump($cursocorto);die;
            $em->persist($areafinal);
            $em->flush();

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        } else {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron guardados.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin'));
        }







    }
    public function deleteAreaTemAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $idc = $request->get('form');

//        $em->getConnection()->beginTransaction();
        $reinicio = true;
//dump( $idc);die;
        $query = $em->getConnection()->prepare('
        select * from permanente_institucioneducativa_cursocorto where areatematica_tipo_id =:area
        ');

        $query->bindValue(':area', $idc);
        $query->execute();
        $areafinal = $query->fetchAll();
        //   dump($programafinal);die;

        if($areafinal==null || $areafinal=='')
        {
            $areas=$em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->find($idc);

            $em->remove($areas);
            $em->flush();
            $em->getConnection()->commit();
            //  dump($programas);die;
            // $em->remove($programas);
            //  $em->persist($programas);
            //   $em->flush();
//            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('mensaje' => 'Area Tematica Eliminada',
                'reinicio' => $reinicio));
        }
        else
        {
//            $em->getConnection()->rollback();
            $reinicio = false;
            $response = new JsonResponse();
            return $response->setData(array('mensaje' => 'Error al eliminar el area tematica, Verifique que no se este utilizando por un CEA',
                'reinicio' => $reinicio));

        }

    }

    public function deleteEspecialidadAction(Request $request)
    {
         
        $idesp = $request->get('idesp');
       // dump($idesp);die;
        try{
            $em = $this->getDoctrine()->getManager();
         //   $em->getConnection()->beginTransaction();
            $especialidadTipo = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->findOneBy(array('id' =>$idesp));
            $especialidadNivel = $em->getRepository('SieAppWebBundle:SuperiorAcreditacionEspecialidad')->findBy(array('superiorEspecialidadTipo' =>$idesp));
            
            if (count($especialidadTipo) > 0){
                    $em->getConnection()->beginTransaction();
                foreach($especialidadNivel as $nivel)
                {
                        $em->remove($nivel);
                        $em->flush();
                }

                $em->remove($especialidadTipo);
                $em->flush();
                //dump($especialidadNivel);die;
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron eliminados correctamente.');
            }
           
            $em = $this->getDoctrine()->getManager();
            $db = $em->getConnection();
            $query = " 	select ---sae.id, 
                      sest.id, sest.especialidad,
                                sum (case when sat.id = 1 then 1 else 0 end) tecnicobasico,
                                sum (case when sat.id = 20 then 1 else 0 end) tecnicoauxiliar,
                                sum (case when sat.id = 32 then 1 else 0 end) tecnicomedio
                            from superior_acreditacion_especialidad sae
		                      inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id = sat.id
			                    inner join 	superior_especialidad_tipo sest on sae.superior_especialidad_tipo_id =sest.id
				                    inner join superior_facultad_area_tipo sfat on sest.superior_facultad_area_tipo_id = sfat.id
					        where sat.id in (1,20,32) and sfat.id=40
                            group by 
                            sest.id, sest.especialidad
                            order by sest.especialidad ";
            $especialidades = $db->prepare($query);
            $params = array();
            $especialidades->execute($params);
            $esp = $especialidades->fetchAll();

            if (count($esp) > 0){
                $existesp = true;
            }
            else {
                $existesp = false;
            }
            // dump($esp);die;
            return $this->render('SiePermanenteBundle:CursoPermanente:listEspecialidades.html.twig', array(
                'especialidades' => $esp,
                'cantesp'=>count($esp),
                'existeesp'=>$existesp,
                'mensaje' => 'Especialidad Eliminada'

            ));

        }
        catch(Exception $ex)
        {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron Eliminados.');
            return $this->redirect($this->generateUrl('herramienta_permanente_admin_especialidades'));
        }

    }




}
