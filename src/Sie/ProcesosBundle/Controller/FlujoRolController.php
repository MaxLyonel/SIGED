<?php

namespace Sie\ProcesosBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

use Sie\AppWebBundle\Entity\FlujoProceso;
use Sie\AppWebBundle\Entity\FlujoTipo;
use Sie\AppWebBundle\Entity\ProcesoTipo;
use Sie\AppWebBundle\Entity\RolTipo;

/**
 * FlujoTipo controller.
 *
 */
class FlujoRolController extends Controller
{
    public $session;
 
    /**
     * the class constructor
     */
    public function __construct() {
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
        
        $flujoRolForm = $this->createFlujoRolForm(); 
        
        return $this->render('SieProcesosBundle:FlujoRol:index.html.twig', array(
            'form' => $flujoRolForm->createView(),
        ));
        
    }

    public function createFlujorolForm()
    {
        $form = $this->createFormBuilder()
            //->setAction($this->generateUrl('flujoproceso_guardar'))
            ->add('proceso','entity',array('label'=>'Proceso','required'=>true,'class'=>'SieAppWebBundle:FlujoTipo','property'=>'flujo','empty_value' => 'Seleccionar proceso'))
            
            ->getForm();
        return $form;
    }

    public function listaProcesoRolAction(Request $request)
    {
        $flujotipo=$request->get('flujotipo');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT 
        r.rol,p.proceso_tipo
        FROM 
        flujo_tipo f join flujo_proceso fp on f.id = fp.flujo_tipo_id
        join proceso_tipo p on p.id = fp.proceso_id
        join rol_tipo r on fp.rol_tipo_id=r.id
        WHERE f.id='.$flujotipo.' order by r.rol');
        $query->execute();
        $arrDataRoles = $query->fetchAll();
        $data['roles']=$arrDataRoles;
        //dump($data);die;
        return $this->render('SieProcesosBundle:FlujoRol:tablaRoles.html.twig',$data);
    }
    public function listaRolProcesoAction(Request $request)
    {
        $rol_id=$request->get('rol');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT f.flujo,p.proceso_tipo    
        FROM 
        flujo_tipo f join flujo_proceso fp on f.id = fp.flujo_tipo_id
        join proceso_tipo p on p.id = fp.proceso_id
        join rol_tipo r on fp.rol_tipo_id=r.id
        WHERE r.id='. $rol_id .' order by f.flujo');
        $query->execute();
        $arrDataProcecesos = $query->fetchAll();
        $data['procesos']=$arrDataProcecesos;
        //dump($data);die;
        return $this->render('SieProcesosBundle:FlujoRol:tablaProceso.html.twig',$data);
    }
    public function FlujoProcesoRolAction(Request $request)
    {
        $rolprocesoForm = $this->createRolProcesoForm(); 
        return $this->render('SieProcesosBundle:FlujoRol:rol.html.twig', array(
            'form' => $rolprocesoForm->createView(),
        ));
       
    }
    public function createRolProcesoForm()
    {
        $form = $this->createFormBuilder()
            //->setAction($this->generateUrl('flujoproceso_guardar'))
            ->add('rol','entity',array('label'=>'Rol','required'=>true,'class'=>'SieAppWebBundle:RolTipo','property'=>'rol','empty_value' => 'Seleccionar rol'))
            ->getForm();
        return $form;
    }

    
}