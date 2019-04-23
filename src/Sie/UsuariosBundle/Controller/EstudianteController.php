<?php
namespace Sie\UsuariosBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\UsuarioGeneracionRude;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class EstudianteController extends Controller
{
    private $session;
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }
    
    public function estudiantenewAction() {
        return $this->render('SieUsuariosBundle:Estudiante:new.html.twig');
    }

    private function  validationSegip($form){

        $answerSegip = 2;
        if($form['InputCarnet']){

            $arrParametros = array(
                'complemento'=>$form['InputComplemento'],
                'primer_apellido'=>$form['InputPaterno'],
                'segundo_apellido'=>$form['InputMaterno'],
                'nombre'=>$form['InputNombre'],
                'fecha_nacimiento'=>$form['fechaNacimiento']['day'].'-'.$form['fechaNacimiento']['mount'].'-'.$form['fechaNacimiento']['year']
            );

            $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $form['InputCarnet'],$arrParametros,'prod', 'academico');

        }

        return $answerSegip;

    }
    
    public function busquedadatosAction(Request $request) {  
        $form = $request->get('busquedaDatosForm');
        
        $swValidationSegip = $this->validationSegip($form);
        $po = '';
        $swObservation = false;
        //required CI to alterntiva no CEPEAD
        if($this->session->get('directorAlternativa')){
            if(!$form['InputCarnet']){
                $message = 'Los campos Carnete de Identidad y/o Complemento son requeridos';
                $this->addFlash('warningRUDE', $message);
                // dump('no tiene CI');die;
                $swObservation = true;
              return $this->render('SieUsuariosBundle:Estudiante:estudianteslista.html.twig', array(
                    'po' => $po,
                    'swValidationSegip' => $swValidationSegip,
                    'swObservation' => $swObservation,

                ));
            }
        }

        // dump($this->session->get('directorAlternativa'));
        // dump($form);
        // die;



       if($swValidationSegip){
            //        str_pad($form['fechaNacimiento']['day'], 2, '0', STR_PAD_LEFT)
            $fechanac = $form['fechaNacimiento']['year'].'-'.str_pad($form['fechaNacimiento']['mount'], 2, '0', STR_PAD_LEFT).'-'.str_pad($form['fechaNacimiento']['day'], 2, '0', STR_PAD_LEFT);
    //        dump($fechanac);
    //        die;
            $em = $this->getDoctrine()->getManager();
            //$em = $this->getDoctrine()->getEntityManager();
            $db = $em->getConnection();        
            if (($form['InputPaterno'] != '') && ($form['InputMaterno'] != '')){        
                $query = "  select * 
                            from estudiante a
                            where 
                            a.nombre like '%".$form['InputNombre']."%' and a.paterno = '".$form['InputPaterno']."' and a.materno = '".$form['InputMaterno']."' and a.fecha_nacimiento = '".$fechanac."'";
            }
            if ($form['InputMaterno'] == ''){
                $query = "  select * 
                            from estudiante a
                            where 
                            a.nombre like '%".$form['InputNombre']."%' and a.paterno = '".$form['InputPaterno']."' and a.fecha_nacimiento = '".$fechanac."'";
            }
            if ($form['InputPaterno'] == ''){
                $query = "  select * 
                            from estudiante a
                            where 
                            a.nombre like '%".$form['InputNombre']."%' and a.materno = '".$form['InputMaterno']."' and a.fecha_nacimiento = '".$fechanac."'";
            }        
            
            $stmt = $db->prepare($query);
            $params = array();
            $stmt->execute($params);
            $po = $stmt->fetchAll();

       }

//        dump($po);
//        die;
//        if (!$po) {//MAS BUSQUEDAS
//            
//            
//            
//        }
        return $this->render('SieUsuariosBundle:Estudiante:estudianteslista.html.twig', array(
                    'po' => $po,
                    'swValidationSegip' => $swValidationSegip,
                    'swObservation' => $swObservation,

                ));
    }
    
    public function siguientedatosAction(Request $request) {

        $datbas = $request->query->all();
//        dump($datbas);
//        die;
        $em = $this->getDoctrine()->getManager();
        $pt = $em->getRepository('SieAppWebBundle:PaisTipo')->findBy([], ['id' => 'ASC']);
        $ex = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findBy([], ['id' => 'ASC']);
        //$pt = $em->getRepository('SieAppWebBundle:PaisTipo')->findAll();
        return $this->render('SieUsuariosBundle:Estudiante:siguientedatos.html.twig', array(
                    'pt' => $pt, 'datbas' => $datbas,'ex'=>$ex
                ));
    }
       
    public function deptoshowAction(){
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $query = "
                    select id, lugar as departamento
                    from lugar_tipo
                    where lugar_nivel_id = 1
                    order by id
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();        
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('message' => new JsonEncoder()));
        $jsonContent = $serializer->serialize($po, 'json');
        echo $jsonContent;
        exit;
    }
    
    public function provinciashowAction($id){
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $query = "
                    select id, lugar as provincia
                    from lugar_tipo
                    where (lugar_nivel_id = 2) and (lugar_tipo_id = ".$id.")
                    order by id
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();        
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('message' => new JsonEncoder()));
        $jsonContent = $serializer->serialize($po, 'json');
        echo $jsonContent;
        exit;
    }

    private function validateOnAlternativa($form){
        //create db conexion 
        $em = $this->getDoctrine()->getManager();
        $validationAlternativa = false;
        $objInstitucioneducativaAlt = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array(
        'id'=> $form['Sie'],
        'institucioneducativaTipo'=>2
            ));
        if($objInstitucioneducativaAlt && $form['Sie']!='80730796'){
            $validationAlternativa = true;
        }

        return $validationAlternativa;
    }
    
    public function estudianteinsertrudeAction(Request $request) { 
    
        $em = $this->getDoctrine()->getManager();        
        $em->getConnection()->beginTransaction();
        $data = $request->request->all();
        $form = $data['busquedaDatosTotForm'];
        // dump($form);die;
        $response = new JsonResponse();
        //check if the SIE is on ALTERNATIVA
        $swOnAlternativa = $this->validateOnAlternativa($form);
        if($swOnAlternativa && $form['InputCi']==''){
          return $response->setData(array('error'=>true,'mensaje' => '¡Proceso detenido! Los campos Carnet de Identidad y/o complemento son requeridos!')); 
        }

        //dump($form); die;
        $sieentiy = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['Sie']);
        if ($sieentiy){
            
            try {
                $fechanac = $form['Year'].'-'.str_pad($form['Mount'], 2, '0', STR_PAD_LEFT).'-'.str_pad($form['Day'], 2, '0', STR_PAD_LEFT);

                $estudiante = new Estudiante();
                // Generamos el rude del estudiante
                $query = $em->getConnection()->prepare('SELECT get_estudiante_nuevo_rude(:sie::VARCHAR,:gestion::VARCHAR)');
                $query->bindValue(':sie', $form['Sie']);            
                $query->bindValue(':gestion', date('Y'));
                $query->execute();
                $codigorude = $query->fetchAll();
                
                /*if ($codigorude[0]["get_estudiante_nuevo_rude"] == "") {
                    $query = $em->getConnection()->prepare('SELECT get_estudiante_nuevo_rude(:sie::VARCHAR,:gestion::VARCHAR)');
                    $query->bindValue(':sie', $form['Sie']);            
                    $query->bindValue(':gestion', $form['Year']);
                    $query->execute();
                    $codigorude = $query->fetchAll();
                }*/

                $codigoRude = $codigorude[0]["get_estudiante_nuevo_rude"];
                //dump($codigorude[0]["get_estudiante_nuevo_rude"]);die;
                $estudiante->setCodigoRude($codigoRude);
                $estudiante->setCarnetIdentidad($form['InputCi']);
                $estudiante->setComplemento(mb_strtoupper($form['InputComplemento'], 'utf-8'));
                $estudiante->setPaterno($form['Paterno']);
                $estudiante->setMaterno($form['Materno']);
                $estudiante->setNombre($form['Nombre']);                        
                $estudiante->setFechaNacimiento(new \DateTime($fechanac));            
                $estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['Genero']));
                $estudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($form['Pais']));
                if ($form['Pais'] === '1'){                    
                    $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['Depto']));
                    $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['Provincia']));
                    $estudiante->setLocalidadNac($form['Localidad']);
                }
                else{//EN CASO DE QUE EL PAIS NO SEA BOLIVIA
                    $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
                    $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
                    $estudiante->setLocalidadNac('');
                }
                //$estudiante->setComplemento('');
                //added validation segip by krlos
                if($form['swValidationSegip']==1){
                    $valSegip = 1;
                    // $messageValSegip = 'SI VALIDADO SEGIP';
                }else{
                    $valSegip = 0;
                    // $messageValSegip = 'NO VALIDADO SEGIP';
                }

                $estudiante->setSegipId($valSegip);
                // $estudiante->setObservacion($messageValSegip);
                
                $estudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($form['Expedido']));
                $em->persist($estudiante);
                $em->flush();

                $UsuarioGeneracionRude = new UsuarioGeneracionRude();
                $UsuarioGeneracionRude->setUsuarioId($this->session->get('userId'));
                $UsuarioGeneracionRude->setFechaRegistro(new \DateTime('now'));
                $aDatosCreacion = array('sie' => $form['Sie'], 'rude' => $codigoRude);
                $UsuarioGeneracionRude->setDatosCreacion(serialize($aDatosCreacion));
                $em->persist($UsuarioGeneracionRude);
                $em->flush();

                $this->get('funciones')->setLogTransaccion(
                     $estudiante->getId(),
                     'estudiante',
                     'C',
                     '',
                     $estudiante,
                     '',
                     'USUARIOS',
                     json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                 );

                $em->getConnection()->commit();
                return $response->setData(array('error'=>false,'mensaje' => '¡Proceso realizado exitosamente! Código rude generado: '.$codigoRude));
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
                return $response->setData(array('mensaje' => '¡Proceso detenido! Se ha detectado inconsistencia de datos!'.$ex)); 
            }
        }
        else{
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => '¡Proceso detenido! No existe el codigo Sie ingresado!'));
        }   
    }
}
