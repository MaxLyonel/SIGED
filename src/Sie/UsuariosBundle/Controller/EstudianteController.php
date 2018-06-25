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
    
    public function busquedadatosAction(Request $request) {  
        $form = $request->get('busquedaDatosForm');
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
//        dump($po);
//        die;
//        if (!$po) {//MAS BUSQUEDAS
//            
//            
//            
//        }
        return $this->render('SieUsuariosBundle:Estudiante:estudianteslista.html.twig', array(
                    'po' => $po,));
    }
    
    public function siguientedatosAction(Request $request) {
        $datbas = $request->query->all();
//        dump($datbas);
//        die;
        $em = $this->getDoctrine()->getManager();
        $pt = $em->getRepository('SieAppWebBundle:PaisTipo')->findAll();
        return $this->render('SieUsuariosBundle:Estudiante:siguientedatos.html.twig', array(
                    'pt' => $pt, 'datbas' => $datbas));
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
    
    public function estudianteinsertrudeAction(Request $request) {
        $em = $this->getDoctrine()->getManager();        
        $em->getConnection()->beginTransaction();
        $data = $request->request->all();
        $form = $data['busquedaDatosTotForm'];
        $response = new JsonResponse();
//        dump(date('Y'));
//        die;        
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
                $codigoRude = $codigorude[0]["get_estudiante_nuevo_rude"];
                $estudiante->setCodigoRude($codigoRude);
                $estudiante->setCarnetIdentidad($form['InputCi']);            
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
                $estudiante->setComplemento('');
                $estudiante->setSegipId('11');
                $em->persist($estudiante);
                $em->flush();

                $UsuarioGeneracionRude = new UsuarioGeneracionRude();
                $UsuarioGeneracionRude->setUsuarioId($this->session->get('userId'));
                $UsuarioGeneracionRude->setFechaRegistro(new \DateTime('now'));
                $aDatosCreacion = array('sie' => $form['Sie'], 'rude' => $codigoRude);
                $UsuarioGeneracionRude->setDatosCreacion(serialize($aDatosCreacion));
                $em->persist($UsuarioGeneracionRude);
                $em->flush();

                $em->getConnection()->commit();
                return $response->setData(array('mensaje' => '¡Proceso realizado exitosamente! Código rude generado: '.$codigoRude));
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
