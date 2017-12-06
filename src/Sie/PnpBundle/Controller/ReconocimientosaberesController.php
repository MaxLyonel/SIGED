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
use Sie\AppWebBundle\Entity\PnpReconocimientoSaberes;
//use Sie\AppWebBundle\Entity\PnpSerialRude;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class ReconocimientosaberesController extends Controller
{
    public function __construct() {
        $this->session = new Session();
    }
    
    public function reconocimiento_saberesAction(Request $request){
        if($request->getMethod()=="POST") { 
        /////datos    
            $em = $this->getDoctrine()->getManager();
            $estudiante_id=$request->get("estudiante_id");
            $tipo=$request->get("tipo");
            $cod_ue=$request->get("ue");
            $curso=$request->get("curso");
            $alfabetizado=$request->get("alfabetizado");
            $idioma=$request->get("idioma");
            $ocupacion=$request->get("ocupacion");
            $observacionadicional=$alfabetizado.'|'.$idioma.'|'.$ocupacion;
        //meter datos
            try {
                $em->getConnection()->beginTransaction();
                $registrar=new PnpReconocimientoSaberes();
                if($tipo=="Estudiante"){
                    $registrar->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($estudiante_id));

                    //actualizamos observacionadicional
                    $estudiante_act = $em->getRepository('SieAppWebBundle:Estudiante')->find($estudiante_id);
                    $estudiante_act->setObservacionadicional($observacionadicional);
                    $em->flush();
                }
                else{///// como es persona debemos mover sus datos a estudiante y guardar el id en persona
                    //BUSCAR VALORES

                    switch ($cod_ue) {
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
                    $anio=date("Y");
                    $seqrude = (string)$rude1->getSeqrude()+1;
                    $codrude = $ie.$anio.str_pad($seqrude, 6, "0", STR_PAD_LEFT);

                    //BUSCA DATOS PERSONA    
                    $persona = $this->getDoctrine()->getRepository('SieAppWebBundle:Persona')->findOneById($estudiante_id);
                    $estudiante = new Estudiante();
                    $estudiante->setCodigoRude($codrude);
                    $rude=$codrude;                
                    $estudiante->setCarnetIdentidad($persona->getCarnet());                
                    $estudiante->setPaterno($persona->getPaterno());
                    $estudiante->setMaterno($persona->getMaterno());
                    $estudiante->setNombre($persona->getNombre());
                    $estudiante->setGeneroTipo($this->getDoctrine()->getRepository('SieAppWebBundle:GeneroTipo')->find($persona->getGeneroTipo()));
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

                    /////insertar el dato
                    $registrar->setEstudiante($estudiante);

                    //ACTUALIZA SECUANCIA DE RUDES
                    $rude1->setSeqrude($seqrude);
                    $em->persist($rude1);
                    $em->flush();
                }
                $registrar->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($cod_ue));
                $registrar->setCurso($curso);
                $registrar->setHomologado(false);
                $registrar->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($this->session->get('userId')));
                $registrar->setFechaCreacion(new \DateTime('now'));
                $em->persist($registrar);
                $em->flush();
                $this->session->getFlashBag()->add('success', 'Estudiante de Reconocimiento de Saberes registrado con exito.');
                $em->getConnection()->commit();
            }
             catch (Exception $e) {
                 $em->getConnection()->rollback();      
                $this->session->getFlashBag()->add('error', 'Existio un problema al registar al estudiante en Reconocimiento de Saberes.');
                throw $e;
           }
        }
        return $this->render('SiePnpBundle:Reconocimientosaberes:reconocimientosaberes.html.twig');
    }

    public function reconocimiento_saberes_validarAction($opcion,$reconocimiento_saberes_id,Request $request){
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        if($opcion==1){//Validar
            try{
                $em->getConnection()->beginTransaction();
                $reconocimiento_saberes = $em->getRepository('SieAppWebBundle:PnpReconocimientoSaberes')->find($reconocimiento_saberes_id);
                $reconocimiento_saberes->setHomologado(true);
                $reconocimiento_saberes->setUsuarioHomologado($em->getRepository('SieAppWebBundle:Usuario')->find($this->session->get('userId')));
                $reconocimiento_saberes->setFechaHomologacion(new \DateTime('now'));
                $em->flush();
                $this->session->getFlashBag()->add('success', 'Validación de Reconocimiento de Saberes con exito.');
                $em->getConnection()->commit();
            }catch(Exception $e) {
                 $em->getConnection()->rollback();      
                $this->session->getFlashBag()->add('error', 'Existio un problema al Validar Reconocimiento de Saberes.');
                throw $e;
           }
        }
        elseif($opcion==2){//Eliminar
            try{
                $em->getConnection()->beginTransaction();
                $reconocimiento_saberes = $em->getRepository('SieAppWebBundle:PnpReconocimientoSaberes')->find($reconocimiento_saberes_id);
                $em->remove($reconocimiento_saberes);
                $em->flush();
                $this->session->getFlashBag()->add('success', 'Eliminación de Reconocimiento de Saberes con exito.');
                $em->getConnection()->commit();
             }catch(Exception $e) {
                 $em->getConnection()->rollback();      
                $this->session->getFlashBag()->add('error', 'Existio un problema al Elimar el Reconocimiento de Saberes.');
                throw $e;
           }
        }
        $query = "SELECT 
                      e.id as estudiante_id,
                      e.codigo_rude, 
                      e.carnet_identidad,
                      e.complemento, 
                      e.paterno, 
                      e.materno, 
                      e.nombre, 
                      e.fecha_nacimiento,
                      rs.id as reconocimiento_saberes_id,
                      rs.institucioneducativa_id,
                      rs.curso,
                      rs.homologado,
                      rs.usuario_id,
                      rs.usuario_homologado_id,
                      rs.fecha_creacion,
                      rs.fecha_homologacion,
                      CASE
                            WHEN rs.institucioneducativa_id = 80480300 THEN
                                'CHUQUISACA'
                            WHEN rs.institucioneducativa_id = 80730794 THEN
                                'LA PAZ'
                            WHEN rs.institucioneducativa_id = 80980569 THEN
                                'COCHABAMBA'
                            WHEN rs.institucioneducativa_id = 81230297 THEN
                                'ORURO'
                            WHEN rs.institucioneducativa_id = 81480201 THEN
                                'POTOSI'
                            WHEN rs.institucioneducativa_id = 81730264 THEN
                                'TARIJA'
                            WHEN rs.institucioneducativa_id = 81981501 THEN
                                'SANTA CRUZ'
                            WHEN rs.institucioneducativa_id = 82230130 THEN
                                'BENI'
                            WHEN rs.institucioneducativa_id = 82480050 THEN
                                'PANDO'                         
                          END AS depto,
                      CASE
                            WHEN rs.curso = 2 THEN
                                'SEGUNDO'
                            WHEN rs.curso = 3 THEN
                                'TERCERO'
                            WHEN rs.curso = 5 THEN
                                'QUINTO'
                            WHEN rs.curso = 6 THEN
                                'SEXTO'                       
                          END AS curso_nombre
                    FROM 
                      estudiante e
                      INNER JOIN pnp_reconocimiento_saberes rs ON e.id = rs.estudiante_id
                      ";
        
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["reconocimiento_saberes_id"] = $p["reconocimiento_saberes_id"];
            $datos_filas["estudiante_id"] = $p["estudiante_id"];
            $datos_filas["codigo_rude"] = $p["codigo_rude"];
            $datos_filas["carnet_identidad"] = $p["carnet_identidad"];
            $datos_filas["complemento"] = $p["complemento"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["fecha_nacimiento"] = $p["fecha_nacimiento"];
            $datos_filas["institucioneducativa_id"] = $p["institucioneducativa_id"];
            $datos_filas["curso"] = $p["curso"];
            $datos_filas["homologado"] = $p["homologado"];
            $datos_filas["usuario_id"] = $p["usuario_id"];
            $datos_filas["usuario_homologado_id"] = $p["usuario_homologado_id"];
            $datos_filas["fecha_creacion"] = $p["fecha_creacion"];
            $datos_filas["fecha_homologacion"] = $p["fecha_homologacion"];
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["curso_nombre"] = $p["curso_nombre"];
            $filas[] = $datos_filas;
        }

        return $this->render('SiePnpBundle:Reconocimientosaberes:reconocimientosaberes_validar.html.twig', array(
            'filas'=>$filas
            )); 
    }
  /////////////////////////////////////////////////////////////////////////////////////////////////////
    public function buscar_estudianteAction($ci,$complemento,$rude,Request $request){
        //return $this->render('SiePnpBundle:Default:mostrarestudiante.html.twig', array('cant'=>$cant));
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $po = array();
        $userId = $this->session->get('userId');    
        /////////////conocer el departamento
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
                $lugar_tipo_id=1;
                $nombre_lugar="Bolivia";
                $ie="";
                break;
        }    

        
        ////////////saber si el estudiante tiene cursos
        if($rude==0)
            if($complemento=='0')
                $where="estudiante.carnet_identidad = '$ci' AND (estudiante.complemento = '' OR estudiante.complemento is null)";
            else
                $where="estudiante.carnet_identidad = '$ci' AND estudiante.complemento = '$complemento'";
        else $where="estudiante.codigo_rude = '$ci'";
        $po=$this->retornar_estudianteAction($ci,$where);
        $filas = array();
        $datos_filas = array();
        $estudiante_rec=0;//0 si puede ser reconocido 1 no puede
        foreach ($po as $p) {
            $nombre = $p["nombre"]." ".$p["paterno"]." ".$p["materno"];
            $estudiante_rec=1;
        }
        if($estudiante_rec==1){
        //si no existe en el curso saber si el curso es aquel que le corresponde
            echo '<div class="alert alert-danger"><strong>Error, </strong>El Estudiante con CI o CODIGO RUDE '.$ci.' con nombre '.$nombre.', no puede ser beneficiado con Reconocimiento de Saberes porque tiene registro en el SIE.</div>'; die; 
        }
        else
        {
             $query = "SELECT
                      estudiante.id as id,
                      estudiante.codigo_rude,
                      estudiante.complemento,
                      estudiante.paterno,
                      estudiante.materno,
                      estudiante.nombre,
                      estudiante.fecha_nacimiento,
                      estudiante.carnet_identidad,
                      estudiante.observacionadicional,
                      genero_tipo.genero
                    FROM 
                      estudiante
                    INNER JOIN genero_tipo ON genero_tipo.id = estudiante.genero_tipo_id
                    WHERE
                      $where";
                    $stmt = $db->prepare($query);
                    $params = array();
                    $stmt->execute($params);
                    $po = $stmt->fetchAll();
            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $po = $stmt->fetchAll();
            $result = array();
            $datos_filas = array();
            foreach ($po as $p) {
                $result['id'] = $p["id"];
                $result['paterno'] = $p["paterno"];
                $result['materno'] = $p["materno"];
                $result['nombre'] = $p["nombre"];
                $result['fecha_nac'] = $p["fecha_nacimiento"];
                $result['genero'] = $p["genero"];
                $result['ci'] = $p["carnet_identidad"];
                $result['complemento'] = $p["complemento"];
                $result['tipo'] = "Estudiante";
            }
            if(count($result)==0){//buscamos en persona 
                $servicioPersona = $this->get('sie_app_web.persona');
                $persona = $servicioPersona->buscarPersona($ci,$complemento,0);    
                $result = array();
                if($persona->type_msg === "success"){   
                    $result['id'] = $persona->result[0]->id;
                    $result['paterno'] = $persona->result[0]->paterno;
                    $result['materno'] = $persona->result[0]->materno;
                    $result['nombre'] = $persona->result[0]->nombre;
                    $fecha_nac=$persona->result[0]->fecha_nacimiento;
                    $result['fecha_nac'] = $fecha_nac;
                    $result['genero'] = $persona->result[0]->genero_tipo_id;
                    $result['ci'] = $persona->result[0]->carnet;
                    $result['complemento'] = $persona->result[0]->complemento;
                    $result['tipo'] = "Persona";
                }
            }
        ////////////Buscarmos en estudiante y persona y preguntamos si encontro
            if(count($result)>0){    
                ///encontramos al estudiante y preguntamos si esta registrado en la tabla
                $reconocimiento_saberes_probar = $em->getRepository('SieAppWebBundle:PnpReconocimientoSaberes')->findByEstudiante($result["id"]);
                if($reconocimiento_saberes_probar){
                    $nombre = $result["nombre"]." ".$result["paterno"]." ".$result["materno"];
                    echo '<div class="alert alert-danger"><strong>Error, </strong>El Estudiante con CI o CODIGO RUDE '.$result['ci'].' con nombre '.$nombre.', Ya esta Registrado en Reconocimiento de Saberes.</div>'; die; 
                }
                 
                    return $this->render('SiePnpBundle:Reconocimientosaberes:mostrar_estudiante.html.twig',array(
                        'result'=>$result,
                        'ci'=>$ci,
                        'ie'=>$ie,
                    ));
            }
            else
            {
            echo '<div class="alert alert-danger">'.$persona->msg.'</div>';die;
            }    
        }   
    }
    public function reconocimiento_saberes_listarAction(){
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();

         $userId = $this->session->get('userId');    
        /////////////conocer el departamento
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
                $lugar_tipo_id=1;
                $nombre_lugar="Bolivia";
                $ie="";
                break;
        }    
        if($nombre_lugar=="Bolivia"){
            $consulta="";
        }
        else{
            $consulta="WHERE rs.institucioneducativa_id=$ie";
        }


        $query = "SELECT 
                      e.id as estudiante_id,
                      e.codigo_rude, 
                      e.carnet_identidad, 
                      e.complemento,
                      e.paterno, 
                      e.materno, 
                      e.nombre, 
                      e.fecha_nacimiento,
                      rs.id as reconocimiento_saberes_id,
                      rs.institucioneducativa_id,
                      rs.curso,
                      rs.homologado,
                      rs.usuario_id,
                      rs.usuario_homologado_id,
                      rs.fecha_creacion,
                      rs.fecha_homologacion,
                      CASE
                            WHEN rs.institucioneducativa_id = 80480300 THEN
                                'CHUQUISACA'
                            WHEN rs.institucioneducativa_id = 80730794 THEN
                                'LA PAZ'
                            WHEN rs.institucioneducativa_id = 80980569 THEN
                                'COCHABAMBA'
                            WHEN rs.institucioneducativa_id = 81230297 THEN
                                'ORURO'
                            WHEN rs.institucioneducativa_id = 81480201 THEN
                                'POTOSI'
                            WHEN rs.institucioneducativa_id = 81730264 THEN
                                'TARIJA'
                            WHEN rs.institucioneducativa_id = 81981501 THEN
                                'SANTA CRUZ'
                            WHEN rs.institucioneducativa_id = 82230130 THEN
                                'BENI'
                            WHEN rs.institucioneducativa_id = 82480050 THEN
                                'PANDO'                         
                          END AS depto,
                      CASE
                            WHEN rs.curso = 2 THEN
                                'SEGUNDO'
                            WHEN rs.curso = 3 THEN
                                'TERCERO'
                            WHEN rs.curso = 5 THEN
                                'QUINTO'
                            WHEN rs.curso = 6 THEN
                                'SEXTO'                       
                          END AS curso_nombre
                    FROM 
                      estudiante e
                      INNER JOIN pnp_reconocimiento_saberes rs ON e.id = rs.estudiante_id 
                      $consulta
                      ";
        
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $filas = array();
        $datos_filas = array();
        foreach ($po as $p) {
            $datos_filas["reconocimiento_saberes_id"] = $p["reconocimiento_saberes_id"];
            $datos_filas["estudiante_id"] = $p["estudiante_id"];
            $datos_filas["codigo_rude"] = $p["codigo_rude"];
            $datos_filas["carnet_identidad"] = $p["carnet_identidad"];
            $datos_filas["complemento"] = $p["complemento"];
            $datos_filas["paterno"] = $p["paterno"];
            $datos_filas["materno"] = $p["materno"];
            $datos_filas["nombre"] = $p["nombre"];
            $datos_filas["fecha_nacimiento"] = $p["fecha_nacimiento"];
            $datos_filas["institucioneducativa_id"] = $p["institucioneducativa_id"];
            $datos_filas["curso"] = $p["curso"];
            $datos_filas["homologado"] = $p["homologado"];
            $datos_filas["usuario_id"] = $p["usuario_id"];
            $datos_filas["usuario_homologado_id"] = $p["usuario_homologado_id"];
            $datos_filas["fecha_creacion"] = $p["fecha_creacion"];
            $datos_filas["fecha_homologacion"] = $p["fecha_homologacion"];
            $datos_filas["depto"] = $p["depto"];
            $datos_filas["curso_nombre"] = $p["curso_nombre"];
            $filas[] = $datos_filas;
        }

        return $this->render('SiePnpBundle:Reconocimientosaberes:reconocimientosaberes_listar.html.twig', array(
            'filas'=>$filas
            )); 
    }

    public function imprimir_certificacionAction($id){
        $arch = 'PNP_RECONOCIMIENTO_SABERES_' . date('Ymd') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'pnp_reconocimiento_saberes_v1_ctv.rptdesign&__format=pdf&&pnp_reconocimiento_saberes_id=' . $id . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

/////////////////////////////////busquedas//////////////////////
// buscar datos estudiantes
    public function retornar_estudianteAction($ci,$where){
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $query = "SELECT
                      estudiante.id as id,
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
                      estudiante INNER JOIN estudiante_inscripcion ON estudiante.id = estudiante_inscripcion.estudiante_id
                      INNER JOIN genero_tipo ON genero_tipo.id = estudiante.genero_tipo_id
                      INNER JOIN estadomatricula_tipo ON estadomatricula_tipo.ID = estudiante_inscripcion.estadomatricula_inicio_tipo_id
                      INNER JOIN institucioneducativa_curso ON estudiante_inscripcion.institucioneducativa_curso_id = institucioneducativa_curso.id
                    WHERE
                      $where ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        return $po;
    }

    // buscar datos persona
    public function retornar_personaAction($ci){
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
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
        //$em = $this->getDoctrine()->getEntityManager();
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

