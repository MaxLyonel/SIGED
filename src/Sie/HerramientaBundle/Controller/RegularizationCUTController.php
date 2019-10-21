<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\BthEstudianteNivelacion;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;

/**
 * Author: krlos Pacha C. <pckrlos@cgmail.com>
 * Description: This is a class for reporting the student nivelation; if exist the correct permissions
 * Date: 23-11-2018
 *
 *
 * class: RegularizationCUTController
 *
 * Email bugs/suggestions to pckrlos@cgmail.com
 */
class RegularizationCUTController extends Controller{
    
    private $session;
    public $arrSie2018;
    
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->arrSie2018 = $this->reloadSie();
        
    }
    private function reloadSie(){
        return array(72210081,
                        40450009,
                        40450021,
                        70400021,
                        40450016,
                        40450028,
                        70440024,
                        70390009,
                        70390017,
                        70420068,
                        70420102,
                        70420125,
                        80480032,
                        80480204,
                        80480190,
                        80480154,
                        80480226,
                        80480218,
                        80480179,
                        80480076,
                        80480060,
                        70470019,
                        70470037,
                        70470001,
                        60420017,
                        70430024,
                        70480030,
                        70480037,
                        70480029,
                        70480012,
                        70480002,
                        70480020,
                        70890024,
                        70890008,
                        40870066,
                        40870033,
                        70900049,
                        70900070,
                        80950028,
                        60890197,
                        80920034,
                        80980308,
                        80980388,
                        80980404,
                        80980368,
                        80980218,
                        80980384,
                        70870049,
                        70960052,
                        70960098,
                        60970036,
                        60970035,
                        70870029,
                        70870043,
                        70870061,
                        70870051,
                        70870103,
                        80850059,
                        80850053,
                        80850060,
                        80830089,
                        80830092,
                        6090004,
                        60900053,
                        80830101,
                        70910012,
                        80870046,
                        80870016,
                        70940016,
                        70940025,
                        70940023,
                        60850013,
                        60850015,
                        60850020,
                        60850032,
                        60890094,
                        60890068,
                        60890078,
                        60890026,
                        60890213,
                        60890098,
                        60890095,
                        60890151,
                        60890054,
                        60890215,
                        60890117,
                        60890214,
                        60890112,
                        60890015,
                        60890155,
                        50730035,
                        50730012,
                        50730039,
                        50730027,
                        50730001,
                        70720001,
                        60620051,
                        80540173,
                        40710002,
                        80600005,
                        80600037,
                        80600019,
                        80600024,
                        80600006,
                        40730252,
                        40730196,
                        40730490,
                        40730153,
                        40730232,
                        40730202,
                        40730374,
                        40730544,
                        40730250,
                        40730521,
                        40730014,
                        40730016,
                        40730038,
                        40730039,
                        40730050,
                        40730095,
                        40730011,
                        40730027,
                        40730067,
                        40730073,
                        40730422,
                        40730518,
                        40730560,
                        40730563,
                        40730470,
                        40730590,
                        40730592,
                        40730083,
                        40730100,
                        40730394,
                        40730420,
                        40730040,
                        40730027,
                        40730061,
                        40730071,
                        40730567,
                        40730408,
                        40730488,
                        40730496,
                        40730501,
                        40730585,
                        40730587,
                        40730615,
                        40730638,
                        70680044,
                        70660021,
                        40640025,
                        70630001,
                        80660059,
                        80660184,
                        80660160,
                        80660103,
                        80660080,
                        80730122,
                        40730021,
                        80730047,
                        80730133,
                        80730104,
                        80730485,
                        80730067,
                        80730140,
                        80730036,
                        80730673,
                        80730460,
                        80730547,
                        80730694,
                        80730356,
                        80730712,
                        80730522,
                        80730775,
                        80730205,
                        80730280,
                        80730293,
                        80730194,
                        80730205,
                        80730736,
                        80730268,
                        80730190,
                        80730541,
                        80730268,
                        70620014,
                        70620066,
                        70620008,
                        70620005,
                        70620043,
                        70620010,
                        70620057,
                        80650007,
                        80650010,
                        80650017,
                        80650014,
                        80650035,
                        80650045,
                        80650013,
                        80650021,
                        60730046,
                        50630026,
                        50630042,
                        50630033,
                        50630062,
                        50630020,
                        50630057,
                        50630055,
                        50630041,
                        50630009,
                        50630057,
                        50630067,
                        50630032,
                        50630059,
                        40610052,
                        40610059,
                        40610062,
                        40610017,
                        40610038,
                        10710039,
                        70650001,
                        60660047,
                        30680001,
                        70610021,
                        70610015,
                        70610024,
                        71230018,
                        71230061,
                        81220073,
                        81210010,
                        81210035,
                        81200022,
                        81170016,
                        81170019,
                        81100028,
                        81230266,
                        71180014,
                        71180012,
                        81160001,
                        71200018,
                        62460063,
                        62480004,
                        62480019,
                        62470003,
                        82480053,
                        82480048,
                        82480037,
                        81840007,
                        81840013,
                        81840029,
                        81950008,
                        31920064,
                        31920010,
                        31920042,
                        31920010,
                        31920057,
                        71920002,
                        71920024,
                        71920030,
                        81860042,
                        81880087,
                        51920001,
                        51920007,
                        51020013,
                        41980095,
                        41980047,
                        41980035,
                        41980083,
                        41980070,
                        41980098,
                        41980052,
                        41980078,
                        61890094,
                        61890084,
                        61890120,
                        41890005,
                        6190103,
                        81890089,
                        81890021,
                        81890128,
                        81890086,
                        81890068,
                        81890035,
                        81890094,
                        81890057,
                        81890113,
                        81890013,
                        81970027,
                        71940001,
                        71940002,
                        71940003,
                        71940009,
                        71940016,
                        81980635,
                        81980635,
                        81960032,
                        81960010,
                        71950059,
                        71950037,
                        51950001,
                        71950045,
                        61880180,
                        61880182,
                        61880001,
                        61880156,
                        61880006,
                        61880147,
                        81870006,
                        81980111,
                        81980034,
                        81980124,
                        81981164,
                        81980854,
                        81981379,
                        71930078,
                        71930002,
                        81910008,
                        81910030,
                        81910031,
                        81910038,
                        81910103,
                        81970007,
                        81970017,
                        81970018,
                        81970109,
                        81970079,
                        81970012,
                        81970118,
                        81970054,
                        81970052,
                        81970022,
                        81970108,
                        81970115,
                        81970110,
                        81970130,
                        81970114,
                        81970116,
                        81970125,
                        81970063,
                        81970108,
                        81970124,
                        81970130,
                        81970100,
                        81970109,
                        81970012,
                        81970063,
                        81970107,
                        81970108,
                        81970018,
                        81970079,
                        81970105,
                        81720032,
                        81720095,
                        81680025,
                        71700028,
                        81700045,
                        81700043,
                        81700039,
                        81730171,
                        81730156,
                        81730172,
                        81710054,

                        80540072,
                        80540161,
                        80540155,
                        80540013,
                        80540011,
                        80540169,
                        80540157,
                        80540201,
                        80540162,
                        80540222,
                        80540166,
                        80540173,
                        80540163,
                        80540180,
                        80540008,
                        80710018,
                        80710032,
                        80710015,
                        60640007,
                        60640017,
                        60640033,
                        60640036,
                        60640004,
                        70710068,
                        20710016,
                        70600001,
                        70600030,
                        80680076,
                        80680060,
                        80680061,
                        40730560,
                        40730102,
                        40730134,
                        50630056,
                        50630002,                        


                    );
    }

    /**
     * Function index
     *
     * @author krlos Pacha C. <pckrlos@cgmail.com>
     * @access public
     * @param void
     * @return form
     */
    public function indexAction(){
        // create varialbes session
        $id_usuario = $this->session->get('userId');
        // create db conexion
        $em = $this->getDoctrine()->getManager();

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieHerramientaBundle:RegularizationCUT:index.html.twig', array(
            'form' => $this->createSearForm()->createView(),
                // ...
            ));    

    }

     /**
     * Function createSearForm
     *
     * @author krlos Pacha C. <pckrlos@cgmail.com>
     * @access private
     * @param void
     * @return form
     */
    private function createSearForm(){
        // array($this->session->get('currentyear')=>$this->session->get('currentyear'))
        return $this->createFormBuilder()
                // ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9a-zA-Z\sñÑ]{10,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestion', 'choices' => array(2019=>2019), 'attr' => array('class' => 'form-control')))
                ->add('buscar', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary', 'onclick'=>'gethistory();')))
                ->getForm();
    }


    /**
     * Function historyAction
     *
     * @author krlos Pacha C. <pckrlos@cgmail.com>
     * @access public
     * @param string codigoRude
     * @return form
     */
    public function historyAction(Request $request){
        
        //get send data
        $form = $request->get('form');
        
        if($form['codigoRude']){
            return $this->redirectToRoute('regularizacion_cut_listHistory', array('form'=> base64_encode(json_encode($form)) ));
        }else {
            return $this->redirectToRoute('regularizacion_cut_index');
        }
        //create db conexion
        
    }

    public function listHistoryAction(Request $request, $form){

        $em = $this->getDoctrine()->getManager();
        $swError = true;
        // $form['codigoRude'] = trim(base64_decode($codigoRude,true));
        $jsonForm = base64_decode($form ,true);
        $form = json_decode($jsonForm,true);
        // die;
        // check if the student exists
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>trim($form['codigoRude'])));        
        if($objStudent){
            // check if the student is in 6t
            // $dataStudentSixth      = $this->get('funciones')->getInscriptionBthByRude($objStudent->getCodigoRude());
            $objStudentInscription = $this->get('funciones')->getInscriptionBthByGestion($form);

            if($objStudentInscription){

                if($em->getRepository('SieAppWebBundle:BthEstudianteNivelacion')->findOneBy(array('estudianteInscripcion'=>$objStudentInscription[0]['estInsId']))){
                    $message = 'Registro ya realizado para la/el estudiante';
                    $this->addFlash('warningReg', $message);
                    $swError = false;
                }else{
                    // VALIDAR SI LA INSCRIPCION CORRESPONDE A UN SIE AUTORIZADO
                    $sie = $objStudentInscription[0]['sie'];
                    $gestion = $objStudentInscription[0]['gestion'];
                    
                    // $sieAutorizado = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array(
                    //     'institucioneducativaId'=>$sie,
                    //     'gestionTipoId'=>$gestion,
                    //     'institucioneducativaHumanisticoTecnicoTipo'=>1, // plena
                    //     // 'esimpreso'=>true
                    // ));
                    // check if the UE is 1 or 7 (plena or transformation bth)
                    $sieAutorizado = $em->createQueryBuilder()
                    ->select('ieht')
                    ->from('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico','ieht')
                    ->where('ieht.institucioneducativaId = :sie')
                    ->andwhere('ieht.gestionTipoId = :gestion')
                    ->andwhere('ieht.institucioneducativaHumanisticoTecnicoTipo IN (:typeUE) ')
                    ->setParameter('sie',$sie)
                    ->setParameter('gestion',$gestion)
                    ->setParameter('typeUE',array(1,7))
                    ->getQuery()
                    ->getResult();

                    if(sizeof($sieAutorizado)){
                    // if(in_array($sie, $this->arrSie2018)){

                        // check if the user has permissions
                        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
                        $query->bindValue(':user_id', $this->session->get('userId'));
                        $query->bindValue(':sie', $objStudentInscription[0]['sie']);
                        $query->bindValue(':rolId', $this->session->get('roluser'));
                        $query->execute();
                        $arrTuicion = $query->fetchAll();
                        // check if the user has permissions on the student
                        if ($arrTuicion[0]['get_ue_tuicion']) {

                               if(sizeof($objStudentInscription)>0){
                                    // check if the student is not avalible to BTH
                                    $objInscriptionTecnicoHumanistico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array(
                                      'estudianteInscripcion' => $objStudentInscription[0]['estInsId']
                                  ));
                                    // if($objInscriptionTecnicoHumanistico && $objInscriptionTecnicoHumanistico->getEsValido()==false){
                                    if(true){
                                        
                                    }else{
                                         $message = 'Estudiante habilitado para la descarga del CUT';
                                        $this->addFlash('warningReg', $message);
                                        $swError = false;
                                    }

                                }else{
                                    $message = 'El Estudiante no esta en el nivel requerido';
                                    $this->addFlash('warningReg', $message);
                                    $swError = false;

                                }

                        }else{
                             $message = 'No tiene tuición para continuar con el ragistro';
                            $this->addFlash('warningReg', $message);
                            $swError = false;

                        }
                        
                    }else{
                        $message = 'El codigo SIE no esta autorizado';
                        $this->addFlash('warningReg', $message);
                        $swError = false;
                    }
                }
            }else{
                $message = 'Estudiante no tiene inscripcion en la gestión seleccionada';
                $this->addFlash('warningReg', $message);
                $swError = false;
            }        

        }else{
            $message = 'No existe Estudiante';
            $this->addFlash('warningReg', $message);
            $swError = false;

        }

        // dump($objStudent);die;
        // dump($form);die;

        if ($swError) {
            //get current inscription
            $historyStudentInscription = $this->get('funciones')->getCurrentInscriptionStudentByRude($form);
            // dump($historyStudentInscription);
            // die;
            return $this->render('SieHerramientaBundle:RegularizationCUT:history.html.twig', array(
                'currentInscription' => $objStudentInscription[0]['estInsId'],
                'currentGestion' => $objStudentInscription[0]['gestion'],
                'currentSie' => $objStudentInscription[0]['sie'],            
                'arrhistory' => $historyStudentInscription,
                'student' => $objStudent,
                'swError' => $swError,            
                    // ...
                ));  
        }

        return $this->render('SieHerramientaBundle:RegularizationCUT:history.html.twig', array(
            'swError' => $swError,            
        ));  

    }

     /**
     * Function regularizationAction
     *
     * @author krlos Pacha C. <pckrlos@cgmail.com>
     * @access public
     * @param int estInsId
     * @return form
     */
    public function regularizationAction(Request  $request){
        //get send values
        $inscriptionIdSelected    = $request->get('inscriptionIdSelected');
        $currentInscriptionId     = $request->get('currentInscriptionId');
        $currentGestion           = $request->get('currentGestion');
        $currentSie               = $request->get('currentSie');
        
        // dump($estInsId);
        $arrSelectStudentInscription = $this->get('funciones')->getSelectStudentInscription($inscriptionIdSelected);
        $arrSelectStudentInscription[0]['currentInscriptionId'] = $request->get('currentInscriptionId');
        $arrSelectStudentInscription[0]['currentGestion']       = $request->get('currentGestion');
        $arrSelectStudentInscription[0]['currentSie']           = $request->get('currentSie');
        
        // dump($arrSelectStudentInscription);
        // die;
        // dump(sizeof($arrSelectStudentInscription) );
        if(sizeof($arrSelectStudentInscription) > 0 ){

        }else{
            dump('no cuenta con historial');
        }

        // die;
        return $this->render('SieHerramientaBundle:RegularizationCUT:regularization.html.twig', array(
            'form' => $this->createNivelationForm($arrSelectStudentInscription[0])->createView(),
            'dataregularization' => $arrSelectStudentInscription[0]
                // ...
        ));    
    }

     /**
     * Function createNivelationForm
     *
     * @author krlos Pacha C. <pckrlos@cgmail.com>
     * @access private
     * @param  array data
     * @return form
     */
    private function createNivelationForm($data){
        $arrTypeNivelation = array('test1', 'test2');
        
        $arrNota =array();
        $cc=1;
        while ($cc <= 100) {
            $arrNota[$cc]=$cc;
            $cc++;
        }        
        
        $form =  $this->createFormBuilder()
                ->add('data', 'hidden', array('mapped' => false,'data' => json_encode($data) , 'attr' => array()))
                // ->add('typeNivelation', 'choice', array('label'=>'Tipo de Nivelación','choices'=>$arrTypeNivelation ,'attr'=>array('class'=>'form-control input-sm')))
                ->add('nota', 'choice', array('label' => 'Nota','choices'=>$arrNota, 'attr'=>array('maxlength' => 3, 'class'=>'form-control input-sm')))
                ->add('observation', 'textarea', array('label' => 'Observación', 'attr'=>array('class'=>'form-control input-sm')))
                ;
        if($data['nivelId']==13 && $data['gradoId']>4){
            //get all speciality 
            $resultSpeciality = $this->get('funciones')->getSpeciality($data);
            // dump($resultSpeciality);die;
            $arrSpeciality = array();            
            if( $resultSpeciality ){
                foreach ($resultSpeciality as $key => $value) {
                    $arrSpeciality[$value->getId()] = strtoupper($value->getEspecialidad()) ;                
                }
            }else{
                $arrSpeciality[99] = 'NO CUENTA CON ESPECIALIDAD';
            }
            //set the asignatura tipo
            $asignaturaTipoId = 1039;
            
           
        }else{
            $arrSpeciality[99] = 'NO APLICA';
            //set the asignatura tipo
            $asignaturaTipoId = 1038;
        }

         $form = $form
                 ->add('speciality', 'choice', array('label'=>'Especialidad','choices'=>$arrSpeciality ,'attr'=>array('class'=>'form-control input-sm')))
                 ->add('asignaturaTipo', 'hidden', array('mapped' => false,'data' => $asignaturaTipoId , 'attr' => array()))
                 ;

        $form = $form 
                
                ->add('register', 'button', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-info', 'onclick'=>'registerNivelation();')))
                ->getForm()
                ;
        // dump($data);die;
        return $form;
    }

    public function registerNivelationAction(Request $request){

        //get send values
        $form     = $request->get('form');
        $arrData = json_decode($form['data'],true);
        // dump($form);
        // dump($arrData);
        
        // die;
        // get data student
        $dataStudent  = json_decode($form['data'],true);
        // dump($dataStudent);
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            // save nota
            $objEstudianteNotaCualitativa = new EstudianteNotaCualitativa();
            $objEstudianteNotaCualitativa->setNotaCuantitativa($form['nota']);
            $objEstudianteNotaCualitativa->setUsuarioId($this->session->get('userId'));
            $objEstudianteNotaCualitativa->setFechaRegistro(new \DateTime('now'));
            $objEstudianteNotaCualitativa->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrData['currentInscriptionId']));
            $objEstudianteNotaCualitativa->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(33));
            $em->persist($objEstudianteNotaCualitativa);
            $em->flush();
            // save regularization
            $objRegister = new BthEstudianteNivelacion();        
            $objRegister->setObs($form['observation']);
            $objRegister->setFechaRegistro(new \DateTime('now'));
            $objRegister->setEstudianteNotaCualitativa($em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($objEstudianteNotaCualitativa->getId()));
            $objRegister->setEspecialidadTecnicoHumanisticoTipo($em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($form['speciality']));
            $objRegister->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($arrData['gradoId']));
            $objRegister->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($form['asignaturaTipo']));
            $objRegister->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrData['currentInscriptionId']));
            $em->persist($objRegister);
            $em->flush();

            $em->getConnection()->commit();
            
        } catch (Exception $e) {
            $this->session->getFlashBag()->add('goodregister', 'Se presento algunos problemas...');        
            $em->getConnection()->rollback();
            // echo 'Excepción capturada: ', $ex->getMessage(), "\n"; 
        }

        $this->session->getFlashBag()->add('goodregister', 'Operacion realizada satisfactoriamente...');        
        return $this->render('SieHerramientaBundle:RegularizationCUT:registerNivelation.html.twig', array(
                // ...
        ));    


        
    }

}
