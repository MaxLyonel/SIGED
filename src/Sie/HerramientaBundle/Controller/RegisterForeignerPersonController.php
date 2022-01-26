<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\EstudiantePersonaDiplomatico;


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
        return $this->redirect($this->generateUrl('login'));

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
    	
        $response = new JsonResponse();
        $form = json_decode($request->get('datos'), true);
        $opcion = $request->get('opcion', null);    	
// dump($form);die;
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

}
