<?php

namespace Sie\OlimpiadasBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sie\AppWebBundle\Entity\OlimEstudianteInscripcion;
use Sie\AppWebBundle\Entity\OlimInscripcionGrupoProyecto;
use Sie\AppWebBundle\Form\OlimEstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;


class DepurationController extends Controller{

	 private $sesion;

    public function __construct(){
        $this->session = new Session();
    }

    public function indexAction(){
    	 //get the session user
         $id_usuario = $this->session->get('userId');
         //validation if the user is logged
         if (!isset($id_usuario)) {
             return $this->redirect($this->generateUrl('login'));
         }
        return $this->render('SieOlimpiadasBundle:Depuration:index.html.twig', array(
                'form' => $this->findOlimStudentForm()->createView()
            ));    
    }

    private function findOlimStudentForm(){
    	return  $this->createFormBuilder()
    	            ->add('codigoRude', 'text', array('attr'=>array('value'=>'', 'maxlength'=>20)))    			 		            
		            ->add('sendData', 'button', array('label'=>'Continuar', 'attr'=>array('onclick'=>'findOlimStudent()')))
		            ->getForm()
        ;
    }

    public function findAction(Request $request){
    	 //get the session user
         $id_usuario = $this->session->get('userId');
         //validation if the user is logged
         if (!isset($id_usuario)) {
             return $this->redirect($this->generateUrl('login'));
         }
    	// create db conexio
    	$em = $this->getDoctrine()->getManager();
    	//get the data send
    	$codigoRude = $request->get('codigoRude');
    	if($codigoRude != ''){
    		return $this->redirectToRoute('olimindepurations_getOlimInscriptions', array('codigoRude' => $codigoRude));
    	}else{
    		die('no codigo rude');
    	}

    	

   
    }

    public function getOlimInscriptionsAction(Request $request){

    	// create db conexio
    	$em = $this->getDoctrine()->getManager();
    	//get the send values
    	$codigoRude = $request->get('codigoRude');
    	

    	 	//look for the estudent
    	$objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$codigoRude));
    	
    		$array = null;
			$currentInscription = null;
			$tutor = null;

    	// check if exist student
    	if($objStudent){
    		//get the last inscription
    		$currentInscription = $em->createQueryBuilder()
									->select('ei')
									->from('SieAppWebBundle:EstudianteInscripcion','ei')
									->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
									->innerJoin('SieAppWebBundle:EstadomatriculaTipo','emt','with','ei.estadomatriculaTipo = emt.id')
									->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
									->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
									->where('e.id = :idEstudiante')
									->andWhere('gt.id = :gestion')
									->andWhere('emt.id IN (:estados)')
									->setParameter('idEstudiante', $objStudent->getId())
									->setParameter('gestion', date('Y'))
									->setParameter('estados', array(4,5,11,55))
									->setMaxResults(1)
									->getQuery()
									->getSingleResult();
								
			// has inscription
		    if($currentInscription){
		    	//get the inscription on olimpiada
		    		$inscripcionesOlim = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->findBy(array(
						'estudianteInscripcion'=>$currentInscription->getId()
					));

					$cont = 0;
					foreach ($inscripcionesOlim as $io) {
						$array[$cont]['inscripcion'] = $io;
						$array[$cont]['regla'] = $io->getOlimReglasOlimpiadasTipo();
						// NIVELES
						$primaria = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
							'olimReglasOlimpiadasTipo'=>$io->getOlimReglasOlimpiadasTipo()->getId(), 'nivelTipo'=>12
						), array('gradoTipo'=>'ASC'));
						$secundaria = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo')->findBy(array(
							'olimReglasOlimpiadasTipo'=>$io->getOlimReglasOlimpiadasTipo()->getId(), 'nivelTipo'=>13
						), array('gradoTipo'=>'ASC'));

						$array[$cont]['primaria'] = $primaria;
						$array[$cont]['secundaria'] = $secundaria;

						if($io->getOlimReglasOlimpiadasTipo()->getModalidadParticipacionTipo()->getId() == 1){
							// INDIVIDUAL
							$array[$cont]['tipo'] = 'Individual';
							$array[$cont]['tutor'] = $io->getOlimTutor();

						}else{
							// GRUPO
							// dump('Grupo');
							$array[$cont]['tipo'] = 'Grupo';
							$grupo = $em->createQueryBuilder()
										->select('ogp')
										->from('SieAppWebBundle:OlimEstudianteInscripcion','oei')
										->leftJoin('SieAppWebBundle:OlimInscripcionGrupoProyecto','oigp','with','oigp.olimEstudianteInscripcion = oei.id')
										->leftJoin('SieAppWebBundle:OlimGrupoProyecto','ogp','with','oigp.olimGrupoProyecto = ogp.id')
										->where('oei.id = :olimInscripcion')
										->setParameter('olimInscripcion', $io->getId())
										->getQuery()
										->getResult();

							$array[$cont]['grupo'] = $grupo;
						}
						// INACRIPCION SUPERIOR
						$inscripcionSuperior = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcionCursoSuperior')->findOneBy(array(
							'olimEstudianteInscripcion'=>$io->getId()
						));

						$array[$cont]['superior'] = $inscripcionSuperior;

						$cont++;
					}

					// dump($array);die;


		    }

    	}

    	return $this->render('SieOlimpiadasBundle:Depuration:find.html.twig', array(
				// 'form'=>$form->createView(),
				'estudiante'=>$objStudent,
				'inscripcionActual'=>$currentInscription,
				'tutor'=>$tutor,
				'array'=>$array
			));
    }



    public function removeOlimInscriptionAction(Request $request){
    	 //get the session user
         $id_usuario = $this->session->get('userId');
         //validation if the user is logged
         if (!isset($id_usuario)) {
             return $this->redirect($this->generateUrl('login'));
         }
    	//get the send values
    	$codigoRude = $request->get('codigoRude');
    	$olimStudentInscriptionId = $request->get('olimStudentInscriptionId');
    	//create db conexion
    	$em = $this->getDoctrine()->getManager();
    	$objOlimStudentInscriptionId = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($olimStudentInscriptionId);
    	$em->remove($objOlimStudentInscriptionId);
    	$em->flush();
		return $this->redirectToRoute('olimindepurations_getOlimInscriptions', array('codigoRude' => $codigoRude));

    }

  

}
