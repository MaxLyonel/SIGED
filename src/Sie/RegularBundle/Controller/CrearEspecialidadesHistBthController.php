<?php
namespace Sie\RegularBundle\Controller;

use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Controller\WfTramiteController;
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Sie\AppWebBundle\Entity\EstudianteInscripcionHumnisticoTecnico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLogTipo;
// use Sie\AppWebBundle\Entity\RehabilitacionBth;

/**
 * ChangeMatricula controller.
 *
 */
class CrearEspecialidadesHistBthController extends Controller {
    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
    }
    public function indexAction (Request $request) { 

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT id,gestion from gestion_tipo ORDER BY gestion DESC");
        $query->execute();
        $lista_gestion = $query->fetchAll();
        return $this->render('SieRegularBundle:CrearEspecialidadesBTH:index.html.twig',array(
            'lista_gestion' => $lista_gestion,
            )
        );
    }
    public function buscar_historial_especialidades_bthAction(Request $request){
        $codigo_sie = $request->get('codigo_sie');
        $idgestion = $request->get('idgestion');

        $em = $this->getDoctrine()->getManager();
        $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($codigo_sie);
        // $idInstitucion = $ie->getInstitucioneducativa();
        // var_dump($idInstitucion); exit();
        // var_dump($ie->getId()); exit();

        //mostrar las especialidades de respectiva gestion
        $historial_especialidad = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->getSpecialityByUnidadEducativa($codigo_sie,$idgestion);
        // dump($historial_especialidad); exit();

        $query = $em->getConnection()->prepare("SELECT id,especialidad FROM especialidad_tecnico_humanistico_tipo  ORDER BY especialidad ASC");
        $query->execute();
        $lista_especialidad= $query->fetchAll();

        return $this->render('SieRegularBundle:CrearEspecialidadesBTH:buscar_historial_especialidades_bth.html.twig',array(
            'gestion' => $idgestion,
            'codigo_sie' => $codigo_sie,
            'institucioneducativa_id' => $ie->getId(),
            'nombre_institucion' => $ie->getInstitucioneducativa(),
            'lista_especialidad' => $lista_especialidad,
            'historial_especialidad' => $historial_especialidad
            )
        );
    }
    public function guardar_nueva_especialidad_bthAction(Request $request){
        $gestion_tipo_id = $request->get('gestion_tipo_id');
        $institucioneducativa_id = $request->get('institucioneducativa_id');
        $especialidad_tecnico_humanistico_tipo_id = $request->get('especialidad_tecnico_humanistico_tipo_id');


        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("  SELECT ins.id FROM institucioneducativa_especialidad_tecnico_humanistico AS ins
        WHERE ins.institucioneducativa_id = ".$institucioneducativa_id."  AND ins.gestion_tipo_id = ".$gestion_tipo_id." AND ins.especialidad_tecnico_humanistico_tipo_id=".$especialidad_tecnico_humanistico_tipo_id." LIMIT 1 ");
        $query->execute();
        $valor = $query->fetchAll();

        $response = new JsonResponse();
        $response->setStatusCode(200);
        if($valor){
            $response->setData(array(0 =>1));
            return $response;
        }else{
            $GuardarEspecialidad = new InstitucioneducativaEspecialidadTecnicoHumanistico();
            $GuardarEspecialidad->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion_tipo_id));
            // $GuardarEspecialidad->setGestionTipo($gestion_tipo_id);
            $GuardarEspecialidad->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucioneducativa_id));
            // $GuardarEspecialidad->setInstitucioneducativa($institucioneducativa_id);
            $GuardarEspecialidad->setFechaRegistro(new \DateTime('now'));
            $GuardarEspecialidad->setEspecialidadTecnicoHumanisticoTipo($em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($especialidad_tecnico_humanistico_tipo_id));
            // $GuardarEspecialidad->setEspecialidadTecnicoHumanisticoTipo($especialidad_tecnico_humanistico_tipo_id);
            $em->persist($GuardarEspecialidad);
            $em->flush();
            $response->setData(array(0 =>0));
            return $response;
        }
    }
    public function funcion_eliminar_esp_bth_validarAction(Request $request){
        $idInstitucioned = $request->get('id');
        // var_dump($idInstitucioned);

        $em = $this->getDoctrine()->getManager();
        $valor = $em->createQuery('SELECT ieco FROM SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico ieco
        WHERE ieco.institucioneducativaHumanisticoId = :institucioneducativaHumanisticoId ')
        ->setParameter('institucioneducativaHumanisticoId', $idInstitucioned)
        ->getResult();
        /*var_dump($valor);
        exit();*/

        $response = new JsonResponse();
        $response->setStatusCode(200);
        if($valor){
            $response->setData(array(0 =>1));
        }else{
            $em->createQuery('DELETE FROM SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico en WHERE en.id = :idinst')->setParameter('idinst', $idInstitucioned)->execute();
            $response->setData(array(0 =>0));
        }
        return $response;
    }
    
}
