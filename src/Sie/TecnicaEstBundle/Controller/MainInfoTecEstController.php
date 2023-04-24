<?php

namespace Sie\TecnicaEstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use Sie\AppWebBundle\Entity\EstTecAutoridadInstituto;
use Sie\AppWebBundle\Entity\Persona;


class MainInfoTecEstController extends Controller{
    public $session;
    public $idInstitucion;
    public $router;
    public $baseData;
    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
        $this->baseData = array('sedeId' => $this->session->get('sedeId'),  'userId' => $this->session->get('userId'));
    }
    /** krlos
     * create close operativo form
     * @return type obj form
     */    
    public function indexAction(){
        
    	$em      = $this->getDoctrine()->getManager();
        $db = $em->getConnection();

        //para habilitar el menu de adminstrador
        $userId =  $this->session->get('userId');
        //dump($userId); die;
        $query = "select count(*) from usuario_rol where rol_tipo_id  = 20 and usuario_id = " . $userId;
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $esadministrador = $po[0]['count'];
        if($esadministrador == 1){
            $this->session->set('roluser', 20); // 20 es de consultas
        }

    	$data    = bin2hex(serialize($this->baseData));
    	$objSede = $em->getRepository('SieAppWebBundle:EstTecSede')->find($this->baseData['sedeId']);
        $enablePersonalStaffOption = ($objSede->getEstTecSedeTipo()->getId()==1)?true:false;
        
        if (!isset($this->baseData['sedeId'])) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieTecnicaEstBundle:MainInfoEstTec:index.html.twig', array(
            'tuicion'                   => true,
            'enablePersonalStaffOption' => $enablePersonalStaffOption,
            'uni_staff'          		=> $this->buildOptionUni('tecest_staff_index', 'Personal Directivo', $data)->createView(),
            'uni_infosede'       		=> $this->buildOptionUni('sie_tecest_sede_index', 'Ubicación Geográfica Sede/Sub Sede', $data)->createView(),
            'uni_statisticssede' 		=> $this->buildOptionUni('sie_tecest_sede_docenteadministrativo_index', 'Estadística de Personal', $data)->createView(),
            'uni_statistics'     		=> $this->buildOptionUni('tecest_carreras_index', 'Estadística de Estudiantes', $data)->createView(),
            'closeform'            => $this->buildOptionUni('tecest_maininfotecest_closeope', 'Cerrar operativo', $data)->createView(),            

            ));    
    }

    /** krlos
     * create close operativo form
     * @return type obj form
     */
    private function buildOptionUni($goToPath, $nextButton, $data) {
        $form =  $this->createFormBuilder()
                        ->setAction($this->generateUrl($goToPath))
                        ->add('data', 'hidden', array('data' => $data));
        $form =$form->add('next', 'submit', array('label' => "$nextButton", 'attr' => array('class' => 'btn btn-primary btn-md btn-block')));
        $form = $form->getForm();
        return $form;
    }    

    /** krlos
     * the method to decrypt
     */
    private function kdecrypt($data){
        $data = hex2bin($data);
        return unserialize($data);
    }
    /** krlos
     * the method to close ope
     */
    public function closeOpeAction(Request $request){
        $form = $request->get('form');
        $dataform = $this->kdecrypt($form['data']);
        // $data    = bin2hex(serialize($form['data']));
        ////////////////////////
        $data=null;
        $status= 404;
        $msj='Ocurrio un error, por favor vuelva a intentarlo.';
        $reporte = '';
        $observations = null;
        try{

            $observations = null;
            $em = $this->getDoctrine()->getManager();
            $db = $em->getConnection();
            $query = 'select * from sp_validacion_institutos_tecnicos_estadistico_web(?);';
            $stmt = $db->prepare($query);
            $params = array($dataform['sedeId']);
            $stmt->execute($params);
            $observations = $stmt->fetchAll();
            // are there observations?
            if($observations == null){
                $data=null;
                $status= 200;
                $msj='Cierre correcto! No existen inconsistencias en el cierre...';
                // miss save data about the operative to the universite data
                // close oall year to the UnivSedeId
                $objOperative = $em->getRepository('SieAppWebBundle:UnivRegistroConsolidacion')->findBy(array('univSede'=>$dataform['sedeId']));
                if(sizeof($objOperative)>0){
                    foreach ($objOperative as $value) {
                        $value->setActivo(0);
                        $em->persist($value);
                    }
                    $em->flush();
                }
            }
            else{
                $data=null;
                $status= 200;
                $msj='No se puedo cerrar el operativo, todavia tiene inconsistencias.';
            }
        }catch(Exception $e){
            $data=null;
            $status= 404;
            $msj='Ocurrio un error al cerrar el operativo, por favor vuelva a intentarlo.';
        }
        $response = new JsonResponse($data,$status);
        $response->headers->set('Content-Type', 'application/json');
        $allData = array('data'=>$data,'status'=>$status,'msj'=>$msj,'observations'=>$observations);
        // dump($allData);die;
        return $response->setData($allData);
        ////////////////////////


    }    

}
