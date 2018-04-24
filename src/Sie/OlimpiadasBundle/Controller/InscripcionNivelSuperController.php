<?php

namespace Sie\OlimpiadasBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sie\AppWebBundle\Entity\OlimEstudianteInscripcion;
use Sie\AppWebBundle\Entity\OlimEstudianteInscripcionCursoSuperior;
use Sie\AppWebBundle\Entity\OlimInscripcionGrupoProyecto;
use Sie\AppWebBundle\Form\OlimEstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * OlimEstudianteInscripcion controller.
 *
 */
/**
 * Author: Carlos Pacha Cordova <pckrlos@gmail.com>
 * Description:This is a class for load the inscription data; if exist in a particular proccess
 * Date: 03-04-2018
 *
 *
 * OlimEstudianteInscripcionController.php
 *
 * Email bugs/suggestions to <pckrlos@gmail.com>
 */

class InscripcionNivelSuperController extends Controller{

    private $sesion;

    public function __construct(){
        $this->session = new Session();
    }

    private function doExternalInscriptionForm($jsonDataInscription){
        return $this->createFormBuilder()
                ->add('jsonDataInscription','hidden', array('data'=>$jsonDataInscription) )
                ->add('doInscription', 'button', array('label'=>'Inscribir','attr'=>array('class'=>'btn btn-warning btn-xs')))
                ->getForm()
                ;

    }

    /*ublic function indexAction()
    {
        return $this->render('SieOlimpiadasBundle:InscripcionNivelSuper:index.html.twig', array(
                // ...
            ));    }

    public function findSudentInscriptionAction()
    {
        return $this->render('SieOlimpiadasBundle:InscripcionNivelSuper:findSudentInscription.html.twig', array(
                // ...
            ));    }*/


   

    /**
     * [selectInscriptionAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function indexAction(Request $request){
        // get the send values
        $form = $request->get('form');
        // dump($form);die;
        $objTutorSelected = $this->get('olimfunctions')->getTutor2($form);
        // dump($objTutorSelected);die;
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:specialInscription.html.twig', array(
            'form' => $this->specialInscriptionForm($form)->createView(),
            'tutor' => $objTutorSelected,
            'cancelform' => $this->cancelForm($form)->createView(),

        ));
    }



    private function specialInscriptionForm($data){
        // dump($data);die;
        $jsondata = json_encode($data);
        $arrAreas = $this->get('olimfunctions')->getAllowedAreasByOlim();
        // dump($arrAreas);die;
        $newform = $this->createFormBuilder()
                ->setAction($this->generateUrl('oliminscriptionsnivelsuperior_findStudentRule'))
                ->add('codigoRude', 'text', array('label'=>'Codigo Rude', ))
                ->add('olimtutorid', 'hidden', array('data'=>$data['olimtutorid']))
                ->add('jsondata', 'hidden', array('data'=>$jsondata))
                ->add('gestion', 'hidden', array('mapped' => false, 'label' => 'Gestion', 'attr' => array('class' => 'form-control', 'value'=>$data['gestiontipoid'])))
                ->add('buscar', 'submit', array('label'=>'Buscar', )) 
                ;
        // if($this->session->get('roluser')==8){
            $newform = $newform
                ->add('institucionEducativa', 'hidden', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control', 'value'=>$data['institucioneducativaid'])))
                ;
        // }

        $newform = $newform->getForm();
        return $newform;

    }


    public function findStudentRuleAction(Request $request){
        // dump($request);die;
        //create db conexino
        $em = $this->getDoctrine()->getManager();
        // get the send data
        $form = $request->get('form');
        // dump($form);die;
        $form['gestiontipoid']= $form['gestion'];
        $arrData = array('institucioneducativaid' => $form['institucionEducativa'], 'gestiontipoid'=> $form['gestion']);
        // validate the student, if is bolivian or doble_nacionalidad
        $objStudent = $this->get('olimfunctions')->validateStudent($form);
        if(!$objStudent){

            $form = json_decode($form['jsondata'], true) ;
            $objTutorSelected = $this->get('olimfunctions')->getTutor2($form);
        // dump($objTutorSelected);die;
            $message = "Estudiante no cuenta con CI o no es Boliviano o no tiene doble nacionalidad";
            $this->addFlash('warningInscriptionSuperior', $message);
            return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:specialInscription.html.twig', array(
                'form' => $this->specialInscriptionForm($form)->createView(),
                'tutor' => $objTutorSelected,
                'cancelform' => $this->cancelForm($arrData)->createView(),

            ));

        }

        // get the olim inforamtion
        $objOlimRegistroOlimpiada = $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->findOneBy(array(
            'gestionTipo'=>$form['gestion']
        ));
        // get the materias into olim
        $objMaterias = $em->getRepository('SieAppWebBundle:OlimMateriaTipo')->findBy(array(
            'olimRegistroOlimpiada'=>$objOlimRegistroOlimpiada->getId()
        ));
        // dump($objMaterias);die;
        //get materia and category
        $objOlimMaterias = $this->getOlimMaterias($objMaterias);
        // dump($objOlimMaterias);die;
        $objStudentInscription = $this->get('olimfunctions')->lookForOlimStudentByRudeGestion($form);

        // dump(json_encode($objStudentInscription));die;
        $jsonDataInscription = json_encode($form);
        $objTutorSelected = $this->get('olimfunctions')->getTutor2(json_decode($form['jsondata'],true));
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:findStudentRule.html.twig', array(
            // 'inscripForm' => $this->doExternalInscriptionForm($jsonDataInscription)->createView(),
            'objStudentInscription' => $objStudentInscription,
            'entities' => $objOlimMaterias,
            'tutor' => $objTutorSelected,
            'dataOlimpiadas' => $form,
            'cpyobjStudentInscription' => json_encode($objStudentInscription) ,
            'cancelform' => $this->cancelForm($arrData)->createView(),
        ));
    }



  private function getOlimMaterias($entities){
    $em = $this->getDoctrine()->getManager();
        $array = array();
        $cont = 0;
        foreach ($entities as $en) {

            $array[$cont] = array(
                'id'=>$en->getId(),
                'materia'=>$en->getMateria(),
                'fechaInsIni'=>$en->getFechaInsIni()->format('d-m-Y'),
                'fechaInsFin'=>$en->getFechaInsFin()->format('d-m-Y')
            );

            $categorias = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->findBy(array('olimMateriaTipo'=>$en->getId()));
            if(count($categorias)>0){
                foreach ($categorias as $ca) {
                    $array[$cont]['categorias'][] = array(
                        'id'=>$ca->getId(),
                        'categoria'=>$ca->getCategoria(),
                        'modalidad'=>$ca->getModalidadParticipacionTipo()->getModalidad()
                    );
                    
                    
                }
            }else{
                $array[$cont]['categorias'] = array();
            }
            $cont++;
        }

        return $array;
    }

    public function selectInscriptionIndividualAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        // get the send values
        $form = $request->get('form'); 
        // dump($request);die;
        $idMateria = $form['idMateria'];
        $jsondata = $form['jsondata'];
        $arrData = json_decode($jsondata,true);
        // dump($arrData);die;
        if($idMateria){
            $this->session->set('idMateria', $idMateria);
        }

        $entities = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->findBy(array(
            'olimMateriaTipo' => $this->session->get('idMateria')
        ),array('id'=>'ASC'));

        $arrayPrimaria = array();
        $array = array();

        foreach ($entities as $en) {
            $primaria = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
                'olimReglasOlimpiadasTipo'=>$en->getId(),
                'nivelTipo'=>12
            ));
            $arrayPrimaria = array();
            foreach ($primaria as $p) {
                $arrayPrimaria[] = $p;
            }

            $secundaria = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
                'olimReglasOlimpiadasTipo'=>$en->getId(),
                'nivelTipo'=>13
            ));
            $arraySecundaria = array();
            foreach ($secundaria as $s) {
                $arraySecundaria[] = $s;
            }

            $array[] = array(
                'entidad'=>$en,
                'primaria'=>$arrayPrimaria,
                'secundaria'=>$arraySecundaria,
            );
        }
        $objTutorSelected = $this->get('olimfunctions')->getTutor2($arrData);
        $entities = $array;
        $arrInfoToDoInscription = array(
            'tutorInfo' => $objTutorSelected,
            'materiaid' => $idMateria,
            'data' => json_decode($form['jsondata'], true),
            'StudentInfo' => json_decode($form['cpyobjStudentInscription'],true),
        );
        $jsonInfoToDoInscription  = json_encode($arrInfoToDoInscription);
        //get the discapacidad
        $objDiscapacidad = $em->getRepository('SieAppWebBundle:OlimDiscapacidadTipo')->findAll();
        return $this->render('SieOlimpiadasBundle:InscripcionNivelSuper:selectInscriptionIndividual.html.twig', array(
            'entities' => $entities,
            'tutor' => $objTutorSelected,
            'jsonInfoToDoInscription' => $jsonInfoToDoInscription,
            'objStudentInscription' => json_decode($form['cpyobjStudentInscription'],true) ,
            'objDiscapacidad' => $objDiscapacidad,
            'cancelform' => $this->cancelForm($arrData)->createView(),
            // 'olimpiada' => $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($arrData['olimregistroolimpiadaid']),
            // 'materia' => $em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($this->session->get('idMateria'))
        ));
    }


    private function cancelForm($data){
        
        return $this->createFormBuilder()
                ->setAction($this->generateUrl('olimtutor_listTutorBySie'))                
                ->add('sie', 'hidden', array('data'=>$data['institucioneducativaid'] , 'mapped'=>false))
                ->add('gestion', 'hidden', array('data'=>$data['gestiontipoid'] , 'mapped'=>false))
                ->add('submit', 'submit', array('label' => 'Cancelar', 'attr'=>array('class'=>'btn btn-danger btn-xs')))
                ->getForm()
                ;          
    }




    public function inscripcionsavesuperiorAction(Request $request){
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        // get the send values
        $form = $request->get('form');
        $arrInfoToDoInscription = json_decode($form['jsonInfoToDoInscription'], true);
        // dump($arrInfoToDoInscription);
        // dump($form);
        // die;

        try {

            $objOLimStudentInscription = new OlimEstudianteInscripcion();
            $objOLimStudentInscription->setTelefonoEstudiante($form['fono']);
            $objOLimStudentInscription->setCorreoEstudiante($form['email']);
            $objOLimStudentInscription->setFechaRegistro(new \DateTime('now'));
            $objOLimStudentInscription->setUsuarioRegistroId($this->session->get('userId'));
            $objOLimStudentInscription->setOlimReglasOlimpiadasTipo($em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->find($form['category']) );
            // $objOLimStudentInscription->setCategoriaTipo($em->getRepository('SieAppWebBundle:OlimCategoriaTipo')->find($val['fono']));
            $objOLimStudentInscription->setMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($arrInfoToDoInscription['materiaid']));
            $objOLimStudentInscription->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:OlimDiscapacidadTipo')->find($form['discapacidad']));
            $objOLimStudentInscription->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrInfoToDoInscription['StudentInfo']['estinsid']));
            $objOLimStudentInscription->setOlimTutor($em->getRepository('SieAppWebBundle:OlimTutor')->find($arrInfoToDoInscription['data']['olimtutorid']));
            $objOLimStudentInscription->setGestionTipoId($arrInfoToDoInscription['data']['gestiontipoid']);

            $em->persist($objOLimStudentInscription);
            $em->flush();

            $OlimEstudianteInscripcionCursoSuperior = new OlimEstudianteInscripcionCursoSuperior();
            $OlimEstudianteInscripcionCursoSuperior->setOlimEstudianteInscripcion($em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($objOLimStudentInscription->getId()));
            $OlimEstudianteInscripcionCursoSuperior->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find($form['nivel']));
            $OlimEstudianteInscripcionCursoSuperior->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($form['grado']));
            $OlimEstudianteInscripcionCursoSuperior->setFechaRegistro(new \DateTime('now'));
            $OlimEstudianteInscripcionCursoSuperior->setUsuarioRegistroId($this->session->get('userId'));
            
            $em->persist($OlimEstudianteInscripcionCursoSuperior);
            $em->flush();

            $em->getConnection()->commit();

            $form = $arrInfoToDoInscription['data'];
            $objTutorSelected = $this->get('olimfunctions')->getTutor2($form);
        // dump($objTutorSelected);die;
            $message = "Inscripcion realizada correctamente";
            $this->addFlash('goodInscriptionSuperior', $message);
            return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:specialInscription.html.twig', array(
                'form' => $this->specialInscriptionForm($form)->createView(),
                'tutor' => $objTutorSelected

            ));


            
            
        } catch (Exception $e) {
             $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";   
            
        }


    }
 


    



}
