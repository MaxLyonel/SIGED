<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class PromotorController extends Controller{
    public $session;
     /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }
    
    public function indexAction(Request $reuest){
       //get the ini values by sie and current year

        $arrCondition = array(
                                'sie'     => $this->session->get('ie_id'),
                                'gestion' => $this->session->get('currentyear')
                            );

        
        // dump($arrCondition);die;
        // check if the user is a director
        if($this->session->get('ie_id')>0){
            return $this->redirectToRoute('aca_promotor_listPromotor', array('dataue' =>  base64_encode(json_encode($arrCondition))  ) );
        }else{
            return $this->redirectToRoute('aca_promotor_selectsie');
        }     
        
    }

    public function selectsieAction(Request  $request){

        //look for the all promotores to this UE
        return $this->render('SieHerramientaBundle:Promotor:selectsie.html.twig', array(
                'form' => $this->formFindPromotor()->createView(),
            ));    

    }

    private function formFindPromotor(){
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('aca_promotor_listPromotor'))
            ->setMethod('POST')
            ->add('sie', 'text', array('attr'=>array('value'=>'', 'maxlength'=>8, 'class'=>'form-control')))
            ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestion', 'choices' => array($this->session->get('currentyear')=>$this->session->get('currentyear')), 'attr' => array('class'=>'form-control')))
            ->add('sendData', 'submit', array('label'=>'Continuar','attr'=>array('class'=>'btn btn-success')))
            ->getForm()
        ;
    }

    public function listPromotorAction(Request  $request){
        //get the data to send 
        $data = $request->request->all();
        if(!$data){
            //get the send data by GET
            $dataue     = $request->get('dataue');
            $jsonDataUe = base64_decode($dataue);
            $form = json_decode($jsonDataUe, true);
            
        }else{
            //get the send data by POST
            $form = $request->get('form');
        }
        //get sie and year data
        $sie     = $form['sie'];
        $gestion = $form['gestion'];
        //create db conexion 
        $em = $this->getDoctrine()->getManager();
        // get info about the UE 
        $objUe = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array(
            'institucioneducativa' => $sie,
            'gestionTipo'          => $gestion,
        ));        
        $arrDataUe = array( 
                            'institucioneducativa' => $sie,
                            'gestionTipo'          => $gestion,
                            'iesucursalId'         => $objUe->getId(),

                          );  
        //look for the all promotores to this UE
        // $objPromotor = $em->getRepository('SieAppWebBundle:CdlClubLectura')->findBy(array(
        //     'institucioneducativasucursal' => $objUe->getId()
        // ));

        $objPromotor = $this->getDataPromotor($objUe->getId());

        return $this->render('SieHerramientaBundle:Promotor:listPromotor.html.twig', array(
                'objPromotor'  => $objPromotor,
                'jsonDataUe' => json_encode($arrDataUe),
            ));    

    }
    //get all promotores by UE sucursal
    private function getDataPromotor($iesucursal){
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:CdlClubLectura');
        $query = $entity->createQueryBuilder('cdl')
                ->select('cdl.id, cdl.nombreClub, p.paterno, p.materno,p.nombre, p.carnet')
                ->leftjoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'cdl.maestroinscripcion = mi.id')
                ->leftjoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
                ->where('cdl.institucioneducativasucursal = :iesucursal')
                ->setParameter('iesucursal', $iesucursal)
                // ->orderBy('iec.gestionTipo', 'DESC')
                ->getQuery();

            $objPromotor = $query->getResult();
            if(sizeof($objPromotor)>=1)
              return $objPromotor;
            else
              return false;

    }

    public function newpromotorAction(Request $request){
        
        //get the send datas
        $jsonDataUe = $request->get('jsonDataUe');

        
        return $this->render('SieHerramientaBundle:Promotor:newpromotor.html.twig', array(
            'form' => $this->formNewPromotor($jsonDataUe)->createView(),
                
            ));    

    }

    private function formNewPromotor($iesucursalId){
        return $this->createFormBuilder()
            ->add('ci', 'text', array('attr'=>array('label' => 'Carnet Identidad','value'=>'', 'maxlength'=>8, 'class'=>'form-control', 'placeholder' => 'Carnet Identidad')))
            ->add('complemento', 'text', array('mapped' => false, 'label' => 'Complemento','required'=>false ,'attr' => array('class'=>'form-control','maxlength'=>2, 'placeholder' => 'Complemento')))
            ->add('jsonDataUe', 'hidden', array('attr'=>array('value'=>$iesucursalId, )))
            ->add('findData', 'button', array('label'=>'Buscar','attr'=>array('class'=>'btn btn-info', 'onclick'=>'findPromotor()')))
            ->getForm();
    }

    public function findpromotorAction(Request $request){
        //get the send data 
        $form = $request->get('form');
        $jsonDataUe = $form['jsonDataUe'];
        $arrDataUe = json_decode($jsonDataUe, true);
        
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        // find the persona
        $objPerson = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
            'carnet'      => $form['ci'],
            'complemento' => $form['complemento']

        ));

        // check if the person is on this UE

        //get all about the cargos
        $queryCargos = $em->createQuery(
                'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                     WHERE ct.rolTipo = 2');
        $cargos = $queryCargos->getResult();
        $cargosArray = array();

        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getId();
        }

        $institucion = $arrDataUe['institucioneducativa'];
        $gestion = $arrDataUe['gestionTipo'];
        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $repository->createQueryBuilder('mi')
                ->select('p.id perId, p.carnet, p.complemento, p.paterno, p.materno, p.nombre, mi.id miId, mi.fechaRegistro, mi.fechaModificacion, mi.esVigenteAdministrativo, ft.formacion')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
                ->innerJoin('SieAppWebBundle:FormacionTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
                ->where('mi.institucioneducativa = :idInstitucion')
                ->andWhere('mi.gestionTipo = :gestion')
                ->andWhere('mi.cargoTipo IN (:cargos)')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargos', $cargosArray)
                ->distinct()
                ->orderBy('p.paterno')
                ->addOrderBy('p.materno')
                ->addOrderBy('p.nombre')
                ->getQuery();

        $objMaestro = $query->getResult();
        $sw = false;
        $selectPromotor='';
         while (($arrMaestro = current($objMaestro)) !== FALSE && !$sw) {
            // dump($arrMaestro['carnet'].' '.$arrMaestro['complemento']);
              // $arrMaestro['perId']
              //dump($arrLevel);
              if ($arrMaestro['perId'] == $objPerson->getId() ) {
                  $sw = true;
                  $selectPromotor = $arrMaestro;
              }
              next($objMaestro);
          }
          //check if the person in on this UE
          if($sw){
            //register if the person in on this UE
            $message = 'Datos encontrados';
            $typeMessage = 'success';

          }else{
            //the person in not in this UE
            $message = 'Datos No encontrados';
            $typeMessage = 'warning';
          }
          $this->addFlash('messagePromotor', $message);
// dump($selectPromotor);die;
// dump($arrDataUe);
// dump($selectPromotor);
// die;

        return $this->render('SieHerramientaBundle:Promotor:findPromotor.html.twig', array(
                'flagPromotor' => $sw,
                'promotorData' => $selectPromotor,
                'typeMessage'  => $typeMessage,
                'form'         => $this->registerPromotorForm()->createView()
            ));   

    }

    private function registerPromotorForm(){
         return $this->createFormBuilder()
            
            ->add('nombreclub', 'text', array('attr'=>array('value'=>'4343', )))
            ->add('mainsid', 'text', array('attr'=>array('value'=>'4343', )))
            ->add('iesucursalId', 'text', array('attr'=>array('value'=>'4343', )))
            ->add('findData', 'button', array('label'=>'Buscar','attr'=>array('class'=>'btn btn-info', 'onclick'=>'registerPromotor()')))
            ->getForm();
    }










    public function findAction(){

        return $this->render('SieHerramientaBundle:Promotor:find.html.twig', array(
                // ...
            ));    
    }

    public function registerAction(){

        return $this->render('SieHerramientaBundle:Promotor:register.html.twig', array(
                // ...
            ));    
    }

}
