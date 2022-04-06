<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\EstudiantePersonaDiplomatico;
use Sie\AppWebBundle\Entity\Estudiante; 
use Sie\AppWebBundle\Entity\EstudianteDiplomatico; 


class RegisterForeignerPersonController extends Controller{

	public $session;
    public function __construct() {
        $this->session = new Session();
    }	
    
    /*public function indexAction(){
        return $this->render('SieHerramientaBundle:RegisterForeignerPerson:index.html.twig', array(
                // ...
            ));    
    }*/
    public function loadDataAction( Request $request ){
    	$response = new JsonResponse();
        $estudiante = $request->get('estudiante', null);
        $opcion = $request->get('opcion', null);

        $em = $this->getDoctrine()->getManager();

    	$arrPais = array();
        $entity = $em->getRepository('SieAppWebBundle:PaisTipo');
           $query = $entity->createQueryBuilder('pt')
                    ->orderBy('pt.pais', 'ASC')
                    ->getQuery();
        $objPais = $query->getResult();
           // $objPais = $em->getRepository('SieAppWebBundle:PaisTipo')->findAll(array('pais'=>'ASC'));
        foreach ($objPais as $value) {
            $arrPais[]=array('paisId'=>$value->getId(), 'pais'=>$value->getPais());
        }


    	$arrGenero = array(	);
         $objGenero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
          foreach ($objGenero as $value) {
              if($value->getId()<3){
                  $arrGenero[] = array('generoId' => $value->getId(),'genero' => $value->getGenero());
              }
          }        

    	return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'arrPais'=>$arrPais,
                'arrGenero'=>$arrGenero,
                'swregistry'=>1,
            )
        ]);

    }    
    public function indexAction(){
        //se deshabilito la inscripcion de extranjeros
        //return $this->redirect($this->generateUrl('login'));      //dcastillo       

        $ie_id=$this->session->get('ie_id');
        
            $swregistry = false;
            $id_usuario = $this->session->get('userId');
     		if (!isset($id_usuario)) {
                return $this->redirect($this->generateUrl('login'));
            }        

            return $this->render('SieHerramientaBundle:RegisterForeignerPerson:index.html.twig',array(
            	

            ));
        
    }

    public function registerAction(Request $request){

        $sesion = $request->getSession();
        $usuarioId = $sesion->get('userId');
        $em = $this->getDoctrine()->getManager();

                        
        $datos = $request->get('datos');    	
        $file = $request->get('informe');            
        $form = json_decode($request->get('datos'), true);

        //dump(isset($_FILES['informe']));die;


        if(isset($_FILES['informe']) == false){
            //dump('no file'); die;

            $status = 404;
            $message = "Falta el Archivo !";
            $code = 3; // id del registro insertado

            $arrResponse = array(
            'existe'          => -1,
            'code'            => $code,
            'message'         => $message,                
            );               
            
            $response = new JsonResponse();
            $response->setStatusCode(200);
            $response->setData($arrResponse);
            return $response;
            
        }else{
            // hay archivo, se procede

            $file = $_FILES['informe'];
            $type = $file['type'];
            $size = $file['size'];
            $tmp_name = $file['tmp_name'];
            $name = $file['name'];
            $extension = explode('.', $name);
            $extension = $extension[count($extension)-1];
            $new_name =  $usuarioId.'_'.date('YmdHis').'.'.$extension;
            // GUARDAMOS EL ARCHIVO
            $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/insExtranjeros/' .date('Y');

            if (!file_exists($directorio)) {
                mkdir($directorio, 0775, true);
            }
            $directoriomove = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/insExtranjeros/' .date('Y').'/'. $usuarioId;
            if (!file_exists($directoriomove)) {
                mkdir($directoriomove, 0775, true);
            }

            $archivador = $directoriomove.'/'.$new_name;           
            if(!move_uploaded_file($tmp_name, $archivador)){
                $em->getConnection()->rollback();
                echo 'Excepción capturada: ', $ex->getMessage(), "\n";
            }

            $RAW_QUERY = "
            SELECT id as estudiante_id FROM estudiante 
            where codigo_rude = '" .$form['rude'] ."'" ;              
            $statement = $em->getConnection()->prepare($RAW_QUERY);
            $statement->execute();
            $result = $statement->fetchAll();
            $student = $result;


            //dump($student); die;
            $estudiante = $student[0]['estudiante_id']; // 29838390;
            $pais_tipo_id = $form['paisId'];
            $nro_documento = $form['numero_documento']; //'A006312-2';
            $embajada = $form['embajada']; //'Embajada Alemana';
            $pasaporte = $form["nro_pasaporte"]; //'210736581';
            $cargo = $form['cargo']; //'Hija del representante de la ONU';
            $vigencia = date($form["fecha_vencimiento"]); //date('2022-03-21');
            $categoria_documento = 'A';

            $created_user_id = $usuarioId;
            $created_at = date('Y-m-d h:i:s');
            $update_user_id = $usuarioId;
            $update_at = date('Y-m-d h:i:s');

            //si ya existe

            /*$RAW_QUERY = '
            SELECT count(*) as existe FROM estudiante_diplomatico 
            where estudiante_id = ' .$estudiante ;  */
            
            $RAW_QUERY = "
            SELECT count(*) as existe FROM estudiante_diplomatico 
            where estudiante_id = " .$estudiante . " and nro_documento = '" . $form['numero_documento'] . "'";  
            
            $statement = $em->getConnection()->prepare($RAW_QUERY);
            $statement->execute();
            $result = $statement->fetchAll();
            $existe_result = $result;
                   
            if($existe_result[0]['existe'] != "0"){
               // ya esta inscrito
               
                $status = 404;
                $message = "Registro ya existe !";
                $code = 2; // id del registro insertado

                $arrResponse = array(
                'existe'          => 1,
                'code'            => $code,
                'message'         => $message,                
                );               
                
                $response = new JsonResponse();
                $response->setStatusCode(200);
                $response->setData($arrResponse);
                return $response;
            }
            
            
            $queryInsert = $em->getConnection()->prepare(
                    "INSERT INTO estudiante_diplomatico( estudiante_id, pais_tipo_id, nro_documento, embajada, pasaporte, cargo, vigencia, "                
                    . "categoria_documento, created_user_id, created_at, update_user_id, update_at, documento_path) "
                    . "VALUES(:estudiante_id, :pais_tipo_id, :nro_documento, :embajada, :pasaporte,"
                    . ":cargo, :vigencia, :categoria_documento, :created_user_id, :created_at, "
                    . ":update_user_id, :update_at, :documento_path)");

            $queryInsert->bindValue(':estudiante_id', $estudiante);
            //$queryInsert->bindValue(':pais_tipo_id', (string) $max);
            $queryInsert->bindValue(':pais_tipo_id', $pais_tipo_id);
            $queryInsert->bindValue(':nro_documento', $nro_documento);
            $queryInsert->bindValue(':embajada', $embajada);
            $queryInsert->bindValue(':pasaporte', $pasaporte);
            $queryInsert->bindValue(':cargo', $cargo);
            $queryInsert->bindValue(':vigencia', $vigencia);
            $queryInsert->bindValue(':categoria_documento', $categoria_documento);
            $queryInsert->bindValue(':created_user_id', $created_user_id);
            $queryInsert->bindValue(':created_at', $created_at);
            $queryInsert->bindValue(':update_user_id', $update_user_id);
            $queryInsert->bindValue(':update_at', $update_at);
            $queryInsert->bindValue(':documento_path', $archivador);
            $queryInsert->execute();        

            //dump('save'); die;
            //TODO: poner en un try ..ctach
            $status = 200;
            $message = "Registro Exitoso !";
            $code = 1; // id del registro insertado

            $arrResponse = array(
            'existe'          => 0,
            'code'            => $code,
            'message'         => $message,                
            );
            
            $response = new JsonResponse();
            $response->setStatusCode(200);
            $response->setData($arrResponse);
    
            return $response;

        }       

    }

    public function register2Action(Request $request){

    	
        $response = new JsonResponse();
        $form = json_decode($request->get('datos'), true);
        $opcion = $request->get('opcion', null);    	
        //dump($form);die;        
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        
        try {
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('persona');");
            $query->execute();

            $newpersona = new Persona();            
            $newpersona->setPaterno(mb_strtoupper($form['paterno'], "utf-8"));
            $newpersona->setMaterno(mb_strtoupper($form['materno'], "utf-8"));    
            $newpersona->setNombre(mb_strtoupper($form['nombre'], "utf-8"));    
            $newpersona->setCarnet($form['run']);  
            $newpersona->setComplemento(mb_strtoupper(NULL, "utf-8"));
            //$newpersona->setCorreo(mb_strtolower($form['correo'], "utf-8"));
            //$fecha = str_pad($form['fechaNacimiento']['day'], 2, '0', STR_PAD_LEFT).'/'.str_pad($form['fechaNacimiento']['month'], 2, '0', STR_PAD_LEFT).'/'.$form['fechaNacimiento']['year'];
            $newpersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find(0));
            $newpersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById(0));
            $newpersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneById(0));
            $newpersona->setRda('0');
            $newpersona->setSegipId('0');
            $newpersona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
            
            $newpersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['generoId']));
            $newpersona->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->findOneById($form['paisId']));
            $newpersona->setActivo('1');
            $newpersona->setEsVigente('1');
            $newpersona->setEsvigenteApoderado('1');
            $newpersona->setCountEdit('1');
            $newpersona->setEsExtranjero('1');
            $newpersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find(0));
            $em->persist($newpersona);
            
            $estudiantePersonaDiplomatico = new EstudiantePersonaDiplomatico();
            $estudiantePersonaDiplomatico->setLugar($form['lugarNacimiento']); 
            $estudiantePersonaDiplomatico->setDocumentoNumero($form['run']);
            $estudiantePersonaDiplomatico->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($newpersona->getId()));
            $estudiantePersonaDiplomatico->setFechaRegistro(new \DateTime('now'));
            $estudiantePersonaDiplomatico->setDocumentoTipo($em->getRepository('SieAppWebBundle:DocumentoTipo')->find($form['typeofregister']));

            if(isset($_FILES['informe'])){
                $file = $_FILES['informe'];

                $type = $file['type'];
                $size = $file['size'];
                $tmp_name = $file['tmp_name'];
                $name = $file['name'];
                $extension = explode('.', $name);
                $extension = $extension[count($extension)-1];
                $new_name = date('YmdHis').'.'.$extension;

                // GUARDAMOS EL ARCHIVO
                $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/personDiploExtran/' .date('Y');
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0775, true);
                }

                $directoriomove = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/personDiploExtran/' .date('Y').'/'.$newpersona->getId();
                if (!file_exists($directoriomove)) {
                    mkdir($directoriomove, 0775, true);
                }

                $archivador = $directoriomove.'/'.$new_name;
                //unlink($archivador);
            if(!move_uploaded_file($tmp_name, $archivador)){
                $em->getConnection()->rollback();
                    $response->setStatusCode(500);
                    return $response;
            }
            $estudiantePersonaDiplomatico->setdocumentoPath($archivador);
            
                //     // CREAMOS LOS DATOS DE LA IMAGEN
                $informe = array(
                  'name' => $name,
                  'type' => $type,
                  'tmp_name' => 'nueva_ruta',
                  'size' => $size,
                  'new_name' => $new_name
                );
              }else{
                  $informe = null;
                  $archivador = 'empty';
              }

            
            //$newpersonahistorico->setFechaActualizacion(new \DateTime());
            $em->persist($estudiantePersonaDiplomatico);
            

            $em->flush();
            $em->getConnection()->commit();

            return $response->setData([
                'status'=>'success',
                'datos'=>array(
                    'newperson'=>$form,
                    'swregistry'=>1,
                )
            ]);            
            } 
        catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Proceso detenido! se ha detectado inconsistencia de datos!'.$ex)); 
        }    	



    }

    public function lookforrudeAction(Request $request){
	    //dump($request);die;
	    //create db conexion
	    $em = $this->getDoctrine()->getManager();
	    $response = new JsonResponse();
   
         // get the send values 
	    $rude = $request->get('rude');
	    $arrGenero = array();
	    $arrPais = array();
	    $withoutcifind=0;
   	    $arrayCondition['codigoRude'] = $rude;
        
        // find the student by arrayCondition
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy($arrayCondition);
        
        $existStudent = false;
        $arrStudentExist = array();

        if(sizeof($objStudent)>0){
            $existStudent=true;
            
            if(sizeof($objStudent)>0){
    
                $arrStudentExist[] = array(
                        'paterno'=>$objStudent->getPaterno(),
                        'materno'=>$objStudent->getMaterno(),
                        'nombre'=>$objStudent->getNombre(),
                        'carnet'=>$objStudent->getCarnetIdentidad(),
                        'complemento'=>$objStudent->getComplemento(),
                        'fecNac'=>$objStudent->getFechaNacimiento()->format('d-m-Y') ,
                        'fecnacfind'=>$objStudent->getFechaNacimiento()->format('d-m-Y') ,
                        'rude'=>$objStudent->getCodigoRude() ,
                        'idStudent'=>$objStudent->getId() ,
                );
                    
    
            }

            // $studentId = $objStudent->getId();
            $existStudent = true;
            $status = 'success';
            $code = 200;
            $message = "Estudiante existe";
            $swcreatestudent = false;


        }else{
            //no existe

            $existStudent = false;
            $status = 'error';
            $code = 404;
            $message = "Estudiante no existe";
            $swcreatestudent = false;

        }
 
  
         $arrResponse = array(
          'status'          => $status,
          'code'            => $code,
          'message'         => $message,
          'swcreatestudent' => $swcreatestudent,
          'arrGenero' => $arrGenero,
          'arrPais' => $arrPais,
          'arrStudentExist' => $arrStudentExist,
          'existStudent' => $existStudent,
          'swhomonimo' => $withoutcifind,
  
        );
  
        $response->setStatusCode(200);
        $response->setData($arrResponse);
  
        return $response;

    }

}
