<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\CdlClubLectura;


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

            $jsonDataRegister = json_encode( array(
                            'mainsid'     =>$selectPromotor['miId'],
                            'iesucursalId'=>$arrDataUe['iesucursalId'],
                            'institucioneducativa'         => $arrDataUe['institucioneducativa'],
                            'gestionTipo'     => $arrDataUe['gestionTipo']
                        ));

          }else{
            //the person in not in this UE
            $message = 'Datos No encontrados';
            $typeMessage = 'warning';
            $jsonDataRegister = json_encode( array(
                            'iesucursalId'=>$arrDataUe['iesucursalId'],
                            'institucioneducativa'         => $arrDataUe['institucioneducativa'],
                            'gestionTipo'     => $arrDataUe['gestionTipo']
                        ));
          }
          $this->addFlash('messagePromotor', $message);

          


        return $this->render('SieHerramientaBundle:Promotor:findPromotor.html.twig', array(
                'flagPromotor' => $sw,
                'promotorData' => $selectPromotor,
                'typeMessage'  => $typeMessage,
                'form'         => $this->registerPromotorForm($jsonDataRegister)->createView()
            ));   

    }

    private function registerPromotorForm($jsonDataRegister){
         return $this->createFormBuilder()
            ->add('nombreclub', 'text', array('attr'=>array('label' => 'nombre del club','value'=>'', 'maxlength'=>32, 'class'=>'form-control','style'=>"text-transform:uppercase", 'placeholder' => 'REGISTRE NOMBRE DEL CLUB')))
            ->add('jsonDataRegister', 'hidden', array('attr'=>array('value'=>$jsonDataRegister, )))
            ->add('registerData', 'button', array('label'=>'Registrar','attr'=>array('class'=>'btn btn-info', 'onclick'=>'registerPromotor()')))
            ->getForm();
    }


    public function registerpromotorAction(Request $request){
        //get the send data
        $form = $request->get('form');
        $arrDataRegister = json_decode($form['jsonDataRegister'],true);
        
        //creete db conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        
        try {   
            // save the promotro
            $objNewCdl = new CdlClubLectura();
            $objNewCdl->setNombreClub(mb_strtoupper($form['nombreclub'], 'utf8') );
            $objNewCdl->setMaestroinscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($arrDataRegister['mainsid']));
            $objNewCdl->setInstitucioneducativasucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($arrDataRegister['iesucursalId']));
            $em->persist($objNewCdl);
            $em->flush();
            
            $em->getConnection()->commit();
            $message = 'Promotor registrado';
            $this->addFlash('savepromotor',$message);

            
        } catch (Exception $e) {

            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
            
        }

        $objPromotor = $this->getDataPromotor($arrDataRegister['iesucursalId']);

        return $this->render('SieHerramientaBundle:Promotor:registerpromotor.html.twig', array(
                'objPromotor'  => $objPromotor,
                'jsonDataUe' => $form['jsonDataRegister'],
                'typeMessage' => 'success',
            ));    


    }

    public function removepromotorAction(Request $request){
        //get the send data 
        $cdlId = $request->get('cdlId');
        $jsonDataUe = $request->get('jsonDataUe');
        $arrDataRegister = json_decode($jsonDataUe,true);
        // create db conexion
        $em = $this->getDoctrine()->getManager();

        // check if the club does not have students
        // dump(sizeof($this->integrantes($cdlId)) );
        $typeMessage = 'warning';
        if( sizeof($this->integrantes($cdlId))>0 || sizeof($this->eventos($cdlId))>0  ){
            $message = 'Registro no eliminado, el club ya cuenta con registros (estudiantes y eventos)';
            $typeMessage = 'warning';
        }else{
            try {
                $objCdl = $em->getRepository('SieAppWebBundle:CdlClubLectura')->find($cdlId);
                $em->remove($objCdl);
                $em->flush();
                $message = 'Promotor elminado';
                $typeMessage = 'success';
                
            } catch (Exception $e) {
                echo 'Excepción capturada: ', $ex->getMessage(), "\n"; 
            }

        }

        $this->addFlash('savepromotor',$message);
        
        $objPromotor = $this->getDataPromotor($arrDataRegister['iesucursalId']);

        return $this->render('SieHerramientaBundle:Promotor:registerpromotor.html.twig', array(
                'objPromotor'  => $objPromotor,
                'jsonDataUe' => $jsonDataUe,
                'typeMessage' => $typeMessage,
            ));    
    }

    public function integrantes($idClubLectura){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:CdlClubLectura');
        $integrantes = $entity->createQueryBuilder('ccl')
                        ->select('ci.id, e.codigoRude, e.paterno, e.materno, e.nombre, ccl.id as cdl')
                        ->innerJoin('SieAppWebBundle:CdlIntegrantes','ci','with','ci.cdlClubLectura = ccl.id')
                        ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','ci.estudianteInscripcion = ei.id')
                        ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                        ->where('ccl.id = :idClub')
                        ->setParameter('idClub', $idClubLectura)
                        ->getQuery()
                        ->getResult();

        return $integrantes;
    }
    public function eventos($idClubLectura){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:CdlClubLectura');
        $eventos = $entity->createQueryBuilder('ccl')
                        ->select('ccl')
                        ->innerJoin('SieAppWebBundle:CdlEventos','ce','with','ccl.id = ce.cdlClubLectura')
                        ->where('ccl.id = :idClub')
                        ->setParameter('idClub', $idClubLectura)
                        ->getQuery()
                        ->getResult();

        return $eventos;
    }





    public function editpromotorAction(Request $request){
    
        //get the send data 
        $cdlId = $request->get('cdlId');
        $jsonDataUe = $request->get('jsonDataUe');
        
        // create db Conexion
        $em =  $this->getDoctrine()->getManager();
        // get the CDL info
        // $objCdl = $em->getRepository('SieAppWebBundle:CdlClubLectura')->find($cdlId);
        //get info about the  maestro 
        $selectPromotor = $this->getEditPromotor($cdlId);
        $arrDataUe = json_decode($jsonDataUe,true);
        $arrDataUe['cdlId'] = $selectPromotor[0]['id'];
        $jsonDataUe = json_encode($arrDataUe);
        
        // $objPerson = $em->getRepository('SieAppWebBundle:Persona')->find($objMaestro->getPersonaId());
        
        return $this->render('SieHerramientaBundle:Promotor:editpromotor.html.twig', array(
            
                'flagPromotor' => true,
                'promotorData' => $selectPromotor[0],
                'typeMessage'  => 'success',
                'form' => $this->editPromotorForm($selectPromotor[0]['nombreClub'],$jsonDataUe)->createView(),
                
            ));    

    }

    //get all promotores by UE sucursal
    private function getEditPromotor($cdlId){
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:CdlClubLectura');
        $query = $entity->createQueryBuilder('cdl')
                ->select('cdl.id, cdl.nombreClub, p.paterno, p.materno,p.nombre, p.carnet, p.complemento')
                ->leftjoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'cdl.maestroinscripcion = mi.id')
                ->leftjoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
                ->where('cdl.id = :cdlId')
                ->setParameter('cdlId', $cdlId)
                // ->orderBy('iec.gestionTipo', 'DESC')
                ->getQuery();

            $objPromotor = $query->getResult();
            if(sizeof($objPromotor)>=1)
              return $objPromotor;
            else
              return false;

    }

    private function editPromotorForm($nombreClub,$jsonDataRegister){
         return $this->createFormBuilder()
            ->add('nombreclub', 'text', array('attr'=>array('label' => 'nombre del club','value'=>$nombreClub, 'maxlength'=>32, 'class'=>'form-control','style'=>"text-transform:uppercase", 'placeholder' => 'REGISTRE NOMBRE DEL CLUB')))
            ->add('jsonDataRegister', 'hidden', array('attr'=>array('value'=>$jsonDataRegister, )))
            ->add('registerData', 'button', array('label'=>'Actualizar','attr'=>array('class'=>'btn btn-info', 'onclick'=>'updatePromotor()')))
            ->getForm();
    }

    
    public function updatepromotorAction(Request $request){
        
        //get the send data
        $form = $request->get('form');        
        $arrDataRegister = json_decode($form['jsonDataRegister'],true);
        //creete db conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        
        try {   
            // update the CDL datas promotro
            $objNewCdl = $em->getRepository('SieAppWebBundle:CdlClubLectura')->find($arrDataRegister['cdlId']);
            $objNewCdl->setNombreClub(mb_strtoupper($form['nombreclub'], 'utf8') );
            $em->persist($objNewCdl);
            $em->flush();
            
            $em->getConnection()->commit();
            $message = 'Datos actualizados correctamente';
            $this->addFlash('savepromotor',$message);

            
        } catch (Exception $e) {

            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
            
        }

        $objPromotor = $this->getDataPromotor($arrDataRegister['iesucursalId']);

        return $this->render('SieHerramientaBundle:Promotor:registerpromotor.html.twig', array(
                'objPromotor'  => $objPromotor,
                'jsonDataUe' => $form['jsonDataRegister'],
                'typeMessage'  => 'success',
            ));    


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
