<?php

namespace Sie\UsuariosBundle\Controller;

use Proxies\__CG__\Sie\AppWebBundle\Entity\DepartamentoTipo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\PersonaHistorico;
use Sie\AppWebBundle\Entity\ValidacionProceso;
use Sie\UsuariosBundle\Form\PersonaType;
use Sie\UsuariosBundle\Form\PersonaSegipType;
use Sie\UsuariosBundle\Form\PersonaApropiacionType;
use Sie\UsuariosBundle\Form\UploadFotoPersonaType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Form\BuscarPersonaType;

class PersonaController extends Controller
{
    private $session;
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }
    
    public function personanewAction($ci, $complemento) {        
        $form = $this->createForm(new PersonaType(), null, array('method' => 'POST',));
        $form->get('carnet')->setData($ci);
        if ($complemento === '-1'){
            $complemento = '';
        }
        $form->get('complemento')->setData($complemento);
        $form->get('idpersona')->setData($ci);
        return $this->render('SieUsuariosBundle:Persona:new.html.twig', array(           
            'form'   => $form->createView(),
            'accion' => 'new',
            'ci' => $ci,
            'complemento' => $complemento,
        ));
    }
    
    public function personainsertAction(Request $request) {
        $form = $request->get('sie_usuarios_persona_edit');
 
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse();
        try {
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('persona');");
            $query->execute();

            $newpersona = new Persona();            
            $newpersona->setPaterno(mb_strtoupper($form['paterno'], "utf-8"));
            $newpersona->setMaterno(mb_strtoupper($form['materno'], "utf-8"));    
            $newpersona->setNombre(mb_strtoupper($form['nombre'], "utf-8"));    
            $newpersona->setCarnet($form['carnet']);  
            $newpersona->setComplemento(mb_strtoupper($form['complemento'], "utf-8"));
            $newpersona->setCorreo(mb_strtolower($form['correo'], "utf-8"));
            $fecha = str_pad($form['fechaNacimiento']['day'], 2, '0', STR_PAD_LEFT).'/'.str_pad($form['fechaNacimiento']['month'], 2, '0', STR_PAD_LEFT).'/'.$form['fechaNacimiento']['year'];
            $newpersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById(0));
            $newpersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById(0));
            $newpersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneById(0));
            $newpersona->setRda('0');
            $newpersona->setSegipId('0');
            $newpersona->setFechaNacimiento(\DateTime::createFromFormat('d/m/Y', $fecha));
            $newpersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['generoTipo']));
            $newpersona->setCorreo(mb_strtolower($form['correo'], "utf-8"));            
            $newpersona->setActivo('1');
            $newpersona->setEsVigente('1');
            $newpersona->setEsvigenteApoderado('1');
            $newpersona->setCountEdit('1');
            $newpersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($form['departamentoTipo']));
            $em->persist($newpersona);
            $em->flush();
            
            $newpersonahistorico = new PersonaHistorico();
            $newpersonahistorico->setCarnet($form['carnet']); 
            $newpersonahistorico->setComplemento(mb_strtoupper($form['complemento'], "utf-8"));
            $newpersonahistorico->setNombre(mb_strtoupper($form['nombre'], "utf-8"));    
            $newpersonahistorico->setPaterno(mb_strtoupper($form['paterno'], "utf-8"));
            $newpersonahistorico->setMaterno(mb_strtoupper($form['materno'], "utf-8"));              
            $newpersonahistorico->setFechaNacimiento(\DateTime::createFromFormat('d/m/Y', $fecha));            
            $newpersonahistorico->setGeneroTipoId($form['generoTipo']);
            $newpersonahistorico->setCorreo(mb_strtolower($form['correo'], "utf-8"));            
            $newpersonahistorico->setUsuario($em->getRepository('SieAppWebBundle:usuario')->find($this->session->get('userId')));
            $newpersonahistorico->setFechaActualizacion(new \DateTime());
            $em->persist($newpersonahistorico);
            $em->flush();
            $em->getConnection()->commit();
//            return $response->setData(array('mensaje' => 'El proceso de insercion de personas se encuentra temporalmente en mantenimiento.'));
            return $response->setData(array('mensaje' => 'Proceso realizado exitosamente.','personaid' => $newpersona->getId()));
            } 
        catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!'.$ex)); 
        }
    }
    
    public function personaeditAction($personaid) {        
        $persona = $this->getDoctrine()->getRepository('SieAppWebBundle:Persona')->find($personaid);        
        $carnet = $persona->getCarnet();
        $complemento = $persona->getComplemento();
        $paterno = $persona->getPaterno();
        $materno = $persona->getMaterno();
        $nombre = $persona->getNombre();        
        $fechaNac = $persona->getFechaNacimiento();
        $fechaNacString = $fechaNac->format('d-m-Y');
        $env = 'prod';
        $sistema = 'alternativa';        
        //dump($carnet);dump($nombre);dump($paterno); die();

        $segipId = $persona->getSegipId();

        //dump($segipId); die();
        if ($segipId == '0') {
            if($carnet){
                $arrParametros = array(      
                    'complemento'=>$complemento,
                    'primer_apellido'=>$paterno,
                    'segundo_apellido'=>$materno,
                    'nombre'=>$nombre,
                    'fecha_nacimiento'=>$fechaNacString); 
                //$segipId = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $carnet,$arrParametros, $env, $sistema); 
                $segipId = false;
                
                if ($segipId == true) {
                    $em = $this->getDoctrine()->getManager();
                    $persona->setSegipId('1');                    
                    $em->persist($persona);
                    $em->flush();
                }
            }
        } else {
            $segipId = true;
        }     
        
        if ($segipId == true){
            $form = $this->createForm(new PersonaSegipType(), null, array('method' => 'POST',));
        } else {
            $form = $this->createForm(new PersonaType(), null, array('method' => 'POST',));
        }
        
        $form->get('idpersona')->setData($personaid);
        $form->get('carnet')->setData($persona->getCarnet());
        $form->get('paterno')->setData($persona->getPaterno());
        $form->get('materno')->setData($persona->getMaterno());
        $form->get('nombre')->setData($persona->getNombre());
        $form->get('complemento')->setData($persona->getComplemento());

        /*$datetime = $persona->getFechaNacimiento();
        $year = $datetime->format('Y');
        $datetimeact = new \DateTime();
        $yearact = $datetimeact->format('Y');
        $valfecha = intval($yearact) - intval($year);
        $fechaEditOn = '0';
        $form->get('fechaEditOn')->setData('0');
        if ($valfecha > 100){
            $fechaEditOn = 'true';
            $form->get('fechaEditOn')->setData('true');
        }*/
                
        $form->get('fechaNacimiento')->setData($persona->getFechaNacimiento());
        $form->get('generoTipo')->setData($persona->getGeneroTipo());
        $form->get('correo')->setData($persona->getCorreo());
        $form->get('departamentoTipo')->setData($persona->getExpedido());
                
        return $this->render('SieUsuariosBundle:Persona:edit.html.twig', array(
                    'form' => $form->createView(),
                    'ci' => $persona->getCarnet(),
                    'complemento' => $persona->getComplemento(),
                    'count_edit' => $persona->getCountEdit(),
                    'segipId' => $segipId,
        ));        
    }
    
    public function personaupdateAction(Request $request) {        
        $form = $request->get('sie_usuarios_persona_edit');

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $persona = $em->getRepository('SieAppWebBundle:Persona')->find($form['idpersona']);
        $response = new JsonResponse();
        try {
            //SÓLO MODIFICA GÉNERO, FECHA DE NACIMIENTO, EXPEDIDO Y CORREO ELECTRÓNICO
            if ($persona->getSegipId() == 1){
                $fechaNac = str_pad($form['fechaNacimiento']['day'], 2, '0', STR_PAD_LEFT).'/'.str_pad($form['fechaNacimiento']['month'], 2, '0', STR_PAD_LEFT).'/'.$form['fechaNacimiento']['year'];
                $fechaNac = \DateTime::createFromFormat('d/m/Y', $fechaNac);
                $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['generoTipo']));
                $persona->setCorreo($form['correo']);
                $persona->setFechaNacimiento($fechaNac);
                $persona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneById($form['departamentoTipo']));
                $em->persist($persona);
                $em->flush();
                $em->getConnection()->commit();
                return $response->setData(array('mensaje' => 'Proceso realizado exitosamente.'));
            } else {
                $carnet = $form['carnet'];
                $complemento = mb_strtoupper($form['complemento'], "utf-8");             
                $paterno = mb_strtoupper($form['paterno'], "utf-8");
                $materno = mb_strtoupper($form['materno'], "utf-8");
                $nombre = mb_strtoupper($form['nombre'], "utf-8");
                $fechaNac = str_pad($form['fechaNacimiento']['day'], 2, '0', STR_PAD_LEFT).'/'.str_pad($form['fechaNacimiento']['month'], 2, '0', STR_PAD_LEFT).'/'.$form['fechaNacimiento']['year'];
                $fechaNac = \DateTime::createFromFormat('d/m/Y', $fechaNac);
                $generoTipo = $form['generoTipo'];
                $correo = $form['correo'];
                $segipId = false;
                //COMPRUEBA DATOS DE FORMULARIO CON SEGIP
                if($carnet){
                    $arrParametros = array(      
                        'complemento'=>$complemento,
                        'primer_apellido'=>$paterno,
                        'segundo_apellido'=>$materno,
                        'nombre'=>$nombre,
                        'fecha_nacimiento'=>$fechaNac); 
                    //$segipId = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $carnet,$arrParametros, $env, $sistema); 
                    $segipId = true;
                }
                //SI ES CORRECTO MODIFICA O CREA NUEVO
                if ($segipId == true){
                    //****COMPRUEBA SI TIENE HISTORIAL */
                    //**** EN CASO DE CONTAR CON HISTORIAL CREA UNO NUEVO ANALIZAR UNIFICACION DE REGISTRO*/ 

                    $obs = '';
                    $db = $em->getConnection();            
                    $query = "  select a.id from 
                                estudiante_apoderado a
                                inner join persona b on a.persona_id = b.id
                                where b.carnet = ? and b.complemento = ?                                
                                limit 1";
                    $stmt = $db->prepare($query);
                    $params = array($form['carnet'], mb_strtoupper($form['complemento'], "utf-8"));
                    $stmt->execute($params);
                    $po = $stmt->fetchAll();                    
                    if (count($po) == 1){                    
                        $obs = 'apoderado'; 
                    }

                    $query = "  select a.id from
                                maestro_inscripcion a
                                inner join persona b on a.persona_id = b.id
                                where b.carnet = ? and b.complemento = ?                                
                                limit 1";
                    $stmt = $db->prepare($query);
                    $params = array($form['carnet'], mb_strtoupper($form['complemento'], "utf-8"));
                    $stmt->execute($params);
                    $po = $stmt->fetchAll();                    
                    if (count($po) == 1){
                        $obs = 'maestro';
                    }

                    if ($obs != ''){
                        //*** CAMBIO DE DATO DEL CARNET ANTERIOR*/
                        $persona->setCarnet($persona->getCarnet().'±');
                        $persona->setSegipId('0');
                        $em->persist($persona);
                        $em->flush();
                        
                        //*** ENVIO DE REGISTRO A CALIDAD */                
                        $validacionproceso = new ValidacionProceso(); 
                        $validacionproceso->setFechaProceso(new \DateTime());
                        $validacionproceso->setValidacionReglaTipo($em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById(47));
                        $validacionproceso->setLlave($persona->getCarnet());
                        $validacionproceso->setGestiontipo($em->getRepository('SieAppWebBundle:ValidacionReglaTipo')->findOneById(2018));
                        $validacionproceso->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(3));
                        $validacionproceso->setEsActivo('1');
                        $validacionproceso->setObs('Docente');
                        $validacionproceso->setInstitucionEducativaId('0');
                        $validacionproceso->setLugarTipoIdDistrito('0');
                        $validacionproceso->setSolucionTipoId('0');
                        $validacionproceso->setOmitido('0');
                        $em->persist($validacionproceso);
                        $em->flush();

                        //***** NUEVO REGISTRO */
                        $newpersona = new Persona();            
                        $newpersona->setPaterno($paterno);
                        $newpersona->setMaterno($materno);
                        $newpersona->setNombre($nombre);
                        $newpersona->setCarnet($carnet);  
                        $newpersona->setComplemento($complemento);                        
                        $newpersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById(0));
                        $newpersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById(0));
                        $newpersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneById(0));
                        $newpersona->setRda('0');
                        $newpersona->setSegipId('1');            
                        $newpersona->setFechaNacimiento($fechaNac);
                        $newpersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($generoTipo));
                        $newpersona->setCorreo($correo);                        
                        $newpersona->setActivo('0');
                        $newpersona->setEsVigente('0');
                        $newpersona->setEsvigenteApoderado('0');
                        $newpersona->setCountEdit('0');
                        $em->persist($newpersona);
                        $em->flush();
                    } else {
                        //***** ACTUALIZACION DE DATOS */
                        $persona->setPaterno($paterno);
                        $persona->setMaterno($materno);
                        $persona->setNombre($nombre);
                        //$persona->setCarnet($form['carnet'], "utf-8");
                        //$persona->setComplemento(mb_strtoupper($form['complemento'], "utf-8"));                                
                        $persona->setFechaNacimiento($fechaNac);
                        $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($generoTipo));
                        $persona->setCorreo($correo);
                        $persona->setSegipId('1');                    
                        $em->persist($persona);
                        $em->flush();
                    }

                    $newpersonahistorico = new PersonaHistorico();
                    $newpersonahistorico->setCarnet($form['carnet']); 
                    $newpersonahistorico->setComplemento(mb_strtoupper($form['complemento'], "utf-8"));
                    $newpersonahistorico->setNombre($nombre);    
                    $newpersonahistorico->setPaterno($paterno);
                    $newpersonahistorico->setMaterno($materno);              
                    $newpersonahistorico->setFechaNacimiento($fechaNac);
                    $newpersonahistorico->setGeneroTipoId($generoTipo);
                    $newpersonahistorico->setCorreo($correo);
                    $newpersonahistorico->setUsuario($em->getRepository('SieAppWebBundle:usuario')->find($this->session->get('userId')));
                    $newpersonahistorico->setFechaActualizacion(new \DateTime());
                    $em->persist($newpersonahistorico);
                    $em->flush();

                    $em->getConnection()->commit();
                    return $response->setData(array('mensaje' => 'Proceso realizado exitosamente.'));                
                } else {
                    return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!'.$ex));                
                    $em->getConnection()->rollback();
                }                
            }
        } 
        catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!'.$ex));                
        }
        
    }
    
    public function personaactedicionAction($personaid) {  
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $persona = $em->getRepository('SieAppWebBundle:Persona')->find($personaid);
        $response = new JsonResponse();
        try {
            $persona->setCountEdit('1');            
            $em->persist($persona);
            $em->flush();
            $em->getConnection()->commit();
            return $response->setData(array('mensaje' => 'Proceso realizado exitosamente.'));                
            } 
        catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!'.$ex));                
        }
    }
    
    public function personauploadfotoAction(Request $request) {            
        $form = $this->createForm(new UploadFotoPersonaType());        
        $form->handleRequest($request);
        if ($form->isValid()) {             
            $data = $form->getData();
            $entity = $this->getDoctrine()->getRepository('SieAppWebBundle:Persona')->find($data['personaid']); 
            $file = $data['foto']; 
            $extension = $file->guessExtension();
                if (!$extension) {
                    $extension = 'bin';
                }
            
            $filename = md5(uniqid()) . '.' . $extension;
            $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/personasfotos';
            $file->move($adjuntoDir, $filename);            
            $this->session->set('userfoto',$filename);                    
            $entity->setFoto($filename);
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();            
        }
        $this->session->getFlashBag()->add('success', 'Proceso realizado exitosamente.');        
        if ($this->session->get('roluser') == '9'){//UNIDAD EDUCATIVA
            return $this->redirect($this->generateUrl('sie_usuario_persona_show',array('ie_id' => $this->session->get('ie_id'), 'ie_nombre' => $this->session->get('ie_nombre') )));
        }
        //NACIONAL - DEPARTAMENTO - DISTRITO 
        if (($this->session->get('roluser') == '8') || ($this->session->get('roluser') == '7') || ($this->session->get('roluser') == '10')) {       
            return $this->redirect($this->generateUrl('sie_usuarios_homepage'));
        }
    }

    public function apropiacionpersonaAction() {

        $formBuscarPersona = $this->createForm(new BuscarPersonaType(array('opcion'=>1)), null, array('action' => $this->generateUrl('sie_usuario_persona_buscar_carnet'), 'method' => 'POST',));

        return $this->render('SieUsuariosBundle:Default:usuariocarnet.html.twig', array(           
            'formBuscarPersona'   => $formBuscarPersona->createView(),            
        ));
        
    }

    public function apropiacionpersonabuscarAction($ci,$complemento,$extranjero,Request $request){
        $em = $this->getDoctrine()->getManager();    
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $servicioPersona = $this->get('sie_app_web.persona');
        $persona = $servicioPersona->buscarPersona($ci,$complemento,$extranjero);
            if($persona->type_msg === "success"){   
                $persona_id = $persona->result[0]->id;
                $personaresult = $persona->result[0];
                $form = $this->createForm(new PersonaApropiacionType(), null, array('method' => 'POST',));
                $form->get('idpersona')->setData($persona->result[0]->id);
                $form->get('carnet')->setData($ci);
                if ($complemento === '0'){
                $complemento = '';
                }
                $form->get('complemento')->setData($complemento);
                
                return $this->render('SieUsuariosBundle:Persona:apropiaciondecarnet.html.twig',array('persona'=>$personaresult, 'form'   => $form->createView(),));

            }else{
                echo '<div class="alert alert-danger">'.$persona->msg.'</div>';
                die;
            }
    }

    public function apropiacionpersonainsertarAction(Request $request) {        
        $form = $request->get('sie_usuarios_persona_apropiacion');
        //dump($form); die;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $response = new JsonResponse();
        try {
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('persona');");
            $query->execute();
            $personaobs = $em->getRepository('SieAppWebBundle:Persona')->find($form['idpersona']);
            $personaobs->setCarnet('9-'.$form['carnet']);
            $em->persist($personaobs);
            $em->flush();
            
            $newpersona = new Persona();            
            $newpersona->setPaterno(mb_strtoupper($form['paterno'], "utf-8"));
            $newpersona->setMaterno(mb_strtoupper($form['materno'], "utf-8"));    
            $newpersona->setNombre(mb_strtoupper($form['nombre'], "utf-8"));    
            $newpersona->setCarnet($form['carnet']);  
            $newpersona->setComplemento(mb_strtoupper($form['complemento'], "utf-8"));
            $newpersona->setCorreo(mb_strtolower($form['correo'], "utf-8"));
            $fecha = str_pad($form['fechaNacimiento']['day'], 2, '0', STR_PAD_LEFT).'/'.str_pad($form['fechaNacimiento']['month'], 2, '0', STR_PAD_LEFT).'/'.$form['fechaNacimiento']['year'];         
            $newpersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById(0));
            $newpersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById(0));
            $newpersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneById(0));
            $newpersona->setRda('0');
            $newpersona->setSegipId('0');            
            $newpersona->setFechaNacimiento(\DateTime::createFromFormat('d/m/Y', $fecha));
            $newpersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['generoTipo']));
            $newpersona->setCorreo(mb_strtolower($form['correo'], "utf-8"));            
            $newpersona->setActivo('1');
            $newpersona->setEsVigente('1');
            $newpersona->setEsvigenteApoderado('1');
            $newpersona->setCountEdit('1');
            $em->persist($newpersona);
            $em->flush();
            
            $newpersonahistorico = new PersonaHistorico();
            $newpersonahistorico->setCarnet($form['carnet']); 
            $newpersonahistorico->setComplemento(mb_strtoupper($form['complemento'], "utf-8"));
            $newpersonahistorico->setNombre(mb_strtoupper($form['nombre'], "utf-8"));    
            $newpersonahistorico->setPaterno(mb_strtoupper($form['paterno'], "utf-8"));
            $newpersonahistorico->setMaterno(mb_strtoupper($form['materno'], "utf-8"));              
            $newpersonahistorico->setFechaNacimiento(\DateTime::createFromFormat('d/m/Y', $fecha));            
            $newpersonahistorico->setGeneroTipoId($form['generoTipo']);
            $newpersonahistorico->setCorreo(mb_strtolower($form['correo'], "utf-8"));            
            $newpersonahistorico->setUsuario($em->getRepository('SieAppWebBundle:usuario')->find($this->session->get('userId')));
            $newpersonahistorico->setFechaActualizacion(new \DateTime());
            $em->persist($newpersonahistorico);
            $em->flush();
            $em->getConnection()->commit();
            return $response->setData(array('mensaje' => 'Proceso realizado exitosamente.','personaid' => $newpersona->getId()));
            } 
        catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!'.$ex)); 
        }
    }



}
