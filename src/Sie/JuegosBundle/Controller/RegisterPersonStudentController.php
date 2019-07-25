<?php

namespace Sie\JuegosBundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sie\JuegosBundle\Controller\EstudianteInscripcionJuegosController as estudianteInscripcionJuegosController;
use Sie\JuegosBundle\Controller\ReglaController as reglaController;
use Sie\AppWebBundle\Entity\JdpEstudianteInscripcionJuegos as EstudianteInscripcionJuegos;
use Sie\AppWebBundle\Entity\JdpEquipoEstudianteInscripcionJuegos as EquipoEstudianteInscripcionJuegos;
use Sie\AppWebBundle\Entity\JdpPersonaInscripcionJuegos;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Config\Definition\Exception\Exception;

class RegisterPersonStudentController extends Controller{

	public $session;
	public $comisionTipo;
	public $currentyear;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->currentyear = $this->session->get('currentyear')-1;
        //$this->aCursos = $this->fillCursos();
    }
    
    public function indexAction(){

        return $this->render('SieJuegosBundle:RegisterPersonStudent:index.html.twig', array(
                'form'=> $this->creaFormularioBusqueda()->createView()
            ));    
    }

     private function creaFormularioBusqueda() {
        $form = $this->createFormBuilder()
                
                ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('placeholder' => 'Ingresar código SIE', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'onInput' => 'listar_nivel(this.value)', 'style' => 'text-transform:uppercase')))
                ->add('nivel', 'choice', array('label' => 'Nivel', 'empty_value' => 'Seleccione nivel', 'attr' => array('class' => 'form-control', 'disabled' => 'disabled', 'onchange' => 'listar_genero(this.value)', 'required' => true)))
                ->add('genero', 'choice', array('label' => 'Género', 'empty_value' => 'Seleccione genero', 'attr' => array('class' => 'form-control', 'disabled' => 'disabled', 'onchange' => 'listar_disciplina(this.value)', 'required' => true)))
                ->add('disciplina', 'choice', array('label' => 'Disciplina', 'empty_value' => 'Seleccione disciplina', 'attr' => array('class' => 'form-control', 'disabled' => 'disabled', 'onchange' => 'listar_prueba(this.value)', 'required' => true)))
                ->add('prueba', 'choice', array('label' => 'Prueba', 'empty_value' => 'Seleccione prueba', 'attr' => array('class' => 'form-control', 'disabled' => 'disabled', 'onchange' => 'listar_registrado(this.value)', 'required' => true)))

                ->getForm();
        return $form;
    }

    public function busquedaNivelAction(Request $request){

    	//get the send values
    	$sie = $request->get('sie');
    	$id_usuario = $this->session->get('userId');
        $id_rol = $this->session->get('roluser');
        $gestionActual = $this->currentyear;
        $response = new JsonResponse();
        //get the correct tucion data
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $id_usuario);
        $query->bindValue(':sie', $sie);
        $query->bindValue(':rolId', $id_rol);
        $query->execute();
        $aTuicion = $query->fetchAll();

        if (!$aTuicion[0]['get_ue_tuicion']){
            //$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No tiene tuición sobre la unidad educativa'));
            return $response->setData(array());
        }

        return $response->setData(array(
                'niveles' => $this->getNivelUnidadEducativa($sie,$gestionActual),
            	)); 
    }

    private function getNivelUnidadEducativa($institucionEducativaId, $gestionId){

    	$em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct nt.* from institucioneducativa_curso as iec
            inner join estudiante_inscripcion as ei on ei.institucioneducativa_curso_id = iec.id
            inner join estudiante as e on e.id = ei.estudiante_id
            inner join nivel_tipo as nt on nt.id = iec.nivel_tipo_id
            where iec.gestion_tipo_id = ".$gestionId." and iec.institucioneducativa_id = ".$institucionEducativaId." and iec.nivel_tipo_id in (12,13) order by nt.id
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        
        return $objEntidad;
    }

    public function busquedaGeneroAction(Request $request){
    	//get the send values
    	$sie = $request->get('sie');
    	$nivel = $request->get('nivel');
    	$gestionActual = $this->currentyear;

    	$response = new JsonResponse();

    	return $response->setData(array(
                'generos' => $this->getGeneroUnidadEducativa($sie,$gestionActual,$nivel),
            ));
    }

    private function getGeneroUnidadEducativa($institucionEducativaId, $gestionId, $nivelId){
    	$em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct gt.* from institucioneducativa_curso as iec
            inner join estudiante_inscripcion as ei on ei.institucioneducativa_curso_id = iec.id
            inner join estudiante as e on e.id = ei.estudiante_id
            inner join genero_tipo as gt on gt.id = e.genero_tipo_id
            where iec.gestion_tipo_id = ".$gestionId." and iec.institucioneducativa_id = ".$institucionEducativaId." and iec.nivel_tipo_id in (".$nivelId.") order by gt.id desc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;

    }

    public function busquedaGradoDisciplinaAction(Request $request){
    	//get the send values 
    	$sie = $request->get('sie');
    	$nivel = $request->get('nivel');
    	$genero = $request->get('genero');
    	$gestionActual = $this->currentyear;

		$response = new JsonResponse();

	    return $response->setData(array(
            'grados' => $this->getGradoNivelGeneroUnidadEducativa($sie,$gestionActual,$nivel,$genero),
            'disciplinas' => $this->getDisciplinaNivelGenero($nivel,$genero),
        ));


    }

    public function getGradoNivelGeneroUnidadEducativa($institucionEducativaId, $gestionId, $nivelId, $generoId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct gt.* from institucioneducativa_curso as iec
            inner join estudiante_inscripcion as ei on ei.institucioneducativa_curso_id = iec.id
            inner join estudiante as e on e.id = ei.estudiante_id
            inner join grado_tipo as gt on gt.id = iec.grado_tipo_id
            where iec.gestion_tipo_id = ".$gestionId." and e.genero_tipo_id = ".$generoId." and iec.institucioneducativa_id = ".$institucionEducativaId." and iec.nivel_tipo_id in (".$nivelId.") order by gt.id asc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    public function getDisciplinaNivelGenero($nivelId, $generoId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct dt.* from jdp_disciplina_tipo as dt
            inner join jdp_prueba_tipo as pt on pt.disciplina_tipo_id = dt.id
            where dt.nivel_tipo_id = ".$nivelId." and pt.genero_tipo_id = ".$generoId." and dt.estado = 'true' and pt.esactivo = 'true'
            order by dt.disciplina asc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    public function busquedaDisciplinaPruebaAction(Request $request){
    	//get the send values 
    	$genero = $request->get('genero');
    	$disciplina = $request->get('disciplina');

    	$gestionActual = $this->currentyear;

    	$response = new JsonResponse();

  		return $response->setData(array(
                'pruebas' => $this->getPruebaDisciplinaGenero($disciplina,$genero),
            ));

    }

    public function getPruebaDisciplinaGenero($disciplinaId, $generoId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select pt.* from jdp_prueba_tipo as pt
            where pt.disciplina_tipo_id = ".$disciplinaId." and pt.genero_tipo_id = ".$generoId." and pt.esactivo = 'true'
            order by pt.prueba asc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    public function busquedaUnidadeducativaPruebaDeportistaAction(Request $request){

    	//get the send values
    	$pruebaId = $request->get('prueba');
    	$sie = $request->get('sie');
    	$gestionActual = $this->currentyear;  	
    	$posicionId = null;
        $faseId = 1;

        $arrData = array(

        	'pruebaId' => $request->get('prueba'),
	    	'sie' => $request->get('sie'),
	    	'gestionActual' => $gestionActual,  	
	    	'posicionId' => null,
	        'faseId' => 1,
	        'nivelId' => $request->get('nivel'),
	        'disciplina' => $request->get('disciplina'),
	        'prueba' => $request->get('prueba'),
	        'genero' => $request->get('genero'),


        );
		return $this->redirectToRoute('RegisterPersonStudent_showGroupStudent', array('data'=>json_encode($arrData)));

	}


    public function showGroupStudentAction(Request $request){
    	//get the send values
    	$jsonData = $request->get('data');
    	$arrData = json_decode($jsonData, true);
// dump($arrData);die;
		$pruebaId      = $arrData['pruebaId'];
    	$sie           = $arrData['sie'];
    	$gestionActual = $arrData['gestionActual'];
    	$posicionId = $arrData['posicionId'];
        $faseId = $arrData['faseId'];
        $disciplinaId = $arrData['disciplina'];
        $pruebaId = $arrData['prueba'];
        $generoId = $arrData['genero'];


    	$em = $this->getDoctrine()->getManager();
        $ainscritos = array();
        $inscriptionId = array();
        $aInscritos = $this->getUnidadEducativaPruebaPosicion($sie,$gestionActual,$faseId,$pruebaId);
        $arrCouchs = $this->getCouchs($sie,$gestionActual,$faseId,$pruebaId);

        foreach ($aInscritos as $inscrito) {
            $inscritoId = (int)$inscrito[0]->getId();
            $inscritoEquipoId = (int)$inscrito['equipoId'];
            $inscritoNombre = $inscrito[0]->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$inscrito[0]->getEstudianteInscripcion()->getEstudiante()->getMaterno().' '.$inscrito[0]->getEstudianteInscripcion()->getEstudiante()->getNombre();
            // $ainscritos[$inscritoEquipoId][base64_encode($inscritoId )] = $inscritoNombre ;
            $ainscritos[$inscritoId]['participante'] = $inscritoNombre ;
            $inscriptionId['inscriptionId'][] = $inscritoId ;
         
        }

        $reglaController = new reglaController();
        $reglaController->setContainer($this->container);
        $pruebaRegla = $reglaController->getPruebaRegla($gestionActual,$faseId,$pruebaId);
        if(count($pruebaRegla)>0){
            $pruebaReglaCupoPresentacion = $pruebaRegla->getCupoPresentacion();
        } else {
            $pruebaReglaCupoPresentacion = 0;
        }
        // $pruebaReglaCupoPresentacion = 1;
        $aequipos = array();
        $equipoId = 0;
        $idinscription = array();
        $aequiposStudent = array();
        foreach ($aInscritos as $inscrito) {
            $inscritoEquipoId = (int)$inscrito['equipoId'];
            $inscritoEquipoNombre = $inscrito['equipoNombre'];
            // if($equipoId != $inscritoEquipoId and $inscritoEquipoId !== NULL and !is_null($inscritoEquipoId)){
            //     $equipoId = $inscritoEquipoId;
            //     $aequipos[base64_encode($inscritoEquipoId)] = $inscritoEquipoNombre;
                 
            // }

            // $arrIdInscription[]=$inscritoId;
            // $jsonIdInscription = json_encode($arrIdInscription);
                $inscritoId = (int)$inscrito[0]->getId();
	            $inscritoNombre = $inscrito[0]->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$inscrito[0]->getEstudianteInscripcion()->getEstudiante()->getMaterno().' '.$inscrito[0]->getEstudianteInscripcion()->getEstudiante()->getNombre();
	                $aequiposStudent[($inscritoEquipoId)][$inscritoId] = $inscritoNombre;
	                $aequiposIdInscription[($inscritoEquipoId)][$inscritoId] = $inscritoId;
	                //.'-'. $jsonIdInscription;
	                // $aequiposNew[($inscritoEquipoId)][$inscritoId] = 'link';
        }

      //this is the new to buil the team or indivual list
        $arrIdInscription=array();
        $arrNewTeam = array();
        foreach ($aequiposStudent as $key => $value) {

        	if($key !=0){
	        	foreach ($value as $idinscription => $student) {
		        	$arrIdInscription[]=$idinscription;
		            $jsonIdInscription = json_encode($arrIdInscription);
	        		$arrNewTeam[$key][]=array('student'=>$student);
	        	}
	        	$arrNewTeam[$key]['option'] = $jsonIdInscription;
	        	// array_push($arrNewTeam[$key], $jsonIdInscription);
	        	$arrIdInscription=array();
        	}else{
        		foreach ($value as $idinscription => $student) {
	        		$arrNewTeam[$key][]=array('student'=>$student, 'option'=>json_encode(array($idinscription)));
	        	}
        	}
        	
        }
// dump($arrNewTeam);
//         die;
		// dump($aequipos);die;
  //       if (count($aequipos) < $pruebaReglaCupoPresentacion){
  //           $aequipos[base64_encode(0)] = "Nuevo Equipo";
  //       }
       
  //       // $response = new JsonResponse();

  //       $entityTipoDisciplinaPrueba = $this->verificaTipoDisciplinaPrueba($pruebaId);

  //       if ($entityTipoDisciplinaPrueba['idTipoPrueba'] == 1){
  //           $arrInscriptionInfo = array(
  //               'participantes' => $ainscritos,
  //               'equipos' => $aequipos,
  //               'conjunto' => true,
  //               // 'posiciones' => $this->getPosicionNivel($_POST['prueba']),
  //           );
  //       } else {
  //           $arrInscriptionInfo = array(
  //               'participantes' => $ainscritos,
  //               'conjunto' => false,
  //               // 'posiciones' => $this->getPosicionNivel($_POST['prueba']),
  //           );
  //       }


		// // get data about the prueba_tipo
		$objPruebaTipo = $em->getRepository('SieAppWebBundle:JdpPruebaTipo')->findOneBy(array(
			'generoTipo'     => $generoId,
			'disciplinaTipo' => $disciplinaId,
		));
		// //get data about the comision_juegos
		$objComisionJuegos = $em->getRepository('SieAppWebBundle:JdpComisionJuegosCupo')->findOneBy(array(
			'pruebaTipo' => $objPruebaTipo->getId()
		));
		$arrComisionJuegos = array();
		if($objComisionJuegos){
			$arrComisionJuegos = array(
				'id' => $objComisionJuegos->getId(),
				'pruebaTipo' => $objComisionJuegos->getPruebaTipo()->getId(),
				'comisionTipo' => $objComisionJuegos->getComisionTipo()->getId(),
				'cupo' => $objComisionJuegos->getCupo(),
				'obs' => $objComisionJuegos->getObs(),
				'sie'           => $arrData['sie'],
				'gestionActual' => $arrData['gestionActual'],
				'faseId' => $arrData['faseId'],
				'pruebaId'      => $arrData['pruebaId'],
			);
		}

           return $this->render('SieJuegosBundle:RegisterPersonStudent:inscriptionInfo.html.twig', array(
                // 'form'=> $this->creaFormularioBusqueda()->createView(),
                // 'arrInscriptionInfo' => $arrInscriptionInfo,
                'mainData' => json_encode($arrComisionJuegos),
                'arrNewTeam' => $arrNewTeam,
                // 'couchs' => $arrCouchs,
                // 'objComisionJuegos' => $objComisionJuegos,
                // 'form' => $this->formLookForPerson(json_encode($inscriptionId), json_encode($arrComisionJuegos))->createView(),
                
            ));    

    }


    // private function formLookForPerson($data, $comisionJuegos){
    	
    // 	$arrComisionJuegos = json_decode($comisionJuegos,true);
    // 	$em = $this->getDoctrine()->getManager();
    // 	$this->comisionTipo = $arrComisionJuegos['comisionTipo'];
    //     return $this->createFormBuilder()
    //         ->add('carnet', 'text', array('label'=>'CI:', 'attr'=>array('class'=>'form-control','placeholder'=>'Carnet Identidad')))
    //         ->add('complemento', 'text', array('label'=>'Complemento:', 'attr'=>array('class'=>'form-control','placeholder'=>'Complemento')))
    //         // ->add('fechanacimiento', 'text', array('label'=>'Fecha Nacimiento:'))
    //         ->add('data',           'hidden', array('attr'=>array('value'=>$data)))
    //         ->add('comisionJuegos', 'hidden', array('attr'=>array('value'=>$comisionJuegos)))

    //         ->add('comisionTipo', 'entity', array('label' => 'Comision Tipo', 'attr' => array('class' => 'form-control'),
    //                     'mapped' => false, 'class' => 'SieAppWebBundle:JdpComisionTipo',
    //                     'query_builder' => function (EntityRepository $e) {
    //                 return $e->createQueryBuilder('jct')
    //                         ->where('jct.id = :id')
    //                         ->setParameter('id', $this->comisionTipo)
    //                         ->orderBy('jct.id', 'ASC')
    //                 ;
    //             }, 'property' => 'comision',
    //                     'data' => $em->getReference("SieAppWebBundle:JdpComisionTipo", $this->comisionTipo)
    //                 ))


    //         ->add('find', 'button', array('label'=>'Buscar', 'attr'=>array('onclick'=>'lookForPerson()','class'=>'btn btn-success')))
    //         ->getForm()
    //         ;
    // }




     public function getUnidadEducativaPruebaPosicion($sie,$gestionId,$faseId,$pruebaId) {
     	//, IDENTITY(jdppij.persona) as persona 
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
                ->select('eij, eeij.equipoId, eeij.equipoNombre')      
                ->innerJoin('SieAppWebBundle:JdpPruebaTipo', 'pt', 'WITH', 'pt.id = eij.pruebaTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:JdpFaseTipo','ft','WITH','ft.id = eij.faseTipo')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = eij.gestionTipo')
                ->leftJoin('SieAppWebBundle:JdpEquipoEstudianteInscripcionJuegos','eeij','WITH','eeij.estudianteInscripcionJuegos = eij.id')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')

                // ->leftJoin('SieAppWebBundle:JdpPersonaInscripcionJuegos', 'jdppij', 'WITH', 'eeij.id = jdppij.estudianteInscripcionJuegos')

                ->where('pt.id = :pruebaId')
                ->andWhere('gt.id = :gestionId')
                ->andWhere('ie.id = :ueId')
                ->andWhere('ft.id = :faseId')
                ->setParameter('ueId', $sie)
                ->setParameter('pruebaId', $pruebaId)
                ->setParameter('gestionId', $gestionId)
                ->setParameter('faseId', $faseId)
                ->orderBy('e.paterno, e.materno, e.nombre')
                ->getQuery();
                
        $inscritos = $query->getResult();

        return $inscritos;
    }

      public function getCouchs($sie,$gestionId,$faseId,$pruebaId) {
     	//, IDENTITY(jdppij.persona) as persona 
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
                ->select('jdppij')      
                ->innerJoin('SieAppWebBundle:JdpPruebaTipo', 'pt', 'WITH', 'pt.id = eij.pruebaTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:JdpFaseTipo','ft','WITH','ft.id = eij.faseTipo')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = eij.gestionTipo')
                ->leftJoin('SieAppWebBundle:JdpEquipoEstudianteInscripcionJuegos','eeij','WITH','eeij.estudianteInscripcionJuegos = eij.id')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->leftJoin('SieAppWebBundle:JdpPersonaInscripcionJuegos', 'jdppij', 'WITH', 'eij.id = jdppij.estudianteInscripcionJuegos')

                ->where('pt.id = :pruebaId')
                ->andWhere('gt.id = :gestionId')
                ->andWhere('ie.id = :ueId')
                ->andWhere('ft.id = :faseId')
                ->setParameter('ueId', $sie)
                ->setParameter('pruebaId', $pruebaId)
                ->setParameter('gestionId', $gestionId)
                ->setParameter('faseId', $faseId)
                ->orderBy('e.paterno, e.materno, e.nombre')
                ->getQuery();
                
        $inscritos = $query->getResult();
        
        //get couchs
        $arrCouchs = array();
        if($inscritos){

	        foreach ($inscritos as $key => $value) {
	        	
	        	if($value){
		        	$arrCouchs[$value->getPersona()->getCarnet()]['carnet'] = $value->getPersona()->getCarnet();
		        	$arrCouchs[$value->getPersona()->getCarnet()]['complemento'] = $value->getPersona()->getComplemento();
		        	$arrCouchs[$value->getPersona()->getCarnet()]['paterno'] = $value->getPersona()->getPaterno();
		        	$arrCouchs[$value->getPersona()->getCarnet()]['materno'] = $value->getPersona()->getMaterno();
		        	$arrCouchs[$value->getPersona()->getCarnet()]['nombre'] = $value->getPersona()->getNombre();
	        	}
	        }
        }

        return $arrCouchs;
    }

      public function verificaTipoDisciplinaPrueba($prueba){
        $repositoryVerInsPru = $this->getDoctrine()->getRepository('SieAppWebBundle:JdpPruebaTipo');
        $queryVerDisPru = $repositoryVerInsPru->createQueryBuilder('pt')
            ->innerJoin('SieAppWebBundle:JdpDisciplinaTipo','dt','WITH','dt.id = pt.disciplinaTipo')
            ->leftJoin('SieAppWebBundle:JdpDisciplinaParticipacionTipo','dpt','WITH','dpt.id = dt.disciplinaParticipacionTipo')
            ->leftJoin('SieAppWebBundle:JdpPruebaParticipacionTipo','ppt','WITH','ppt.id = pt.pruebaParticipacionTipo')
            ->where('pt.id = :codPrueba')
            ->setParameter('codPrueba', $prueba)
            ->getQuery();
        $verInsDisPru = $queryVerDisPru->getResult();

        if(count($verInsDisPru)>0){
            $verInsDisPru = $verInsDisPru[0];
            $idPrueba = $verInsDisPru->getId();        
            $idDisciplina = $verInsDisPru->getDisciplinaTipo()->getId();
            $prueba = $verInsDisPru->getPrueba();        
            $disciplina = $verInsDisPru->getDisciplinaTipo()->getDisciplina();
            $idTipoPrueba = $verInsDisPru->getPruebaParticipacionTipo()->getId();        
            $idTipoDisciplina = $verInsDisPru->getDisciplinaTipo()->getDisciplinaParticipacionTipo()->getId();
            $tipoPrueba = $verInsDisPru->getPruebaParticipacionTipo()->getDisciplinaParticipacion();        
            $tipoDisciplina = $verInsDisPru->getDisciplinaTipo()->getDisciplinaParticipacionTipo()->getDisciplinaParticipacion();
            $cantidadPrueba = $verInsDisPru->getPruebaParticipacionTipo()->getCantidad();        
            $cantidadDisciplina = $verInsDisPru->getDisciplinaTipo()->getDisciplinaParticipacionTipo()->getCantidad();
            return array('idTipoDisciplina'=>$idTipoDisciplina,'idTipoPrueba'=>$idTipoPrueba,'tipoDisciplina'=>$tipoDisciplina,'tipoPrueba'=>$tipoPrueba,'idPrueba'=>$idPrueba,'prueba'=>$prueba,'idDisciplina'=>$idDisciplina,'disciplina'=>$disciplina);
        } else {
            return array();
        }
    }

      public function getPosicionNivel($pruebaId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select dt.* from jdp_prueba_tipo as pt
            inner join jdp_disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id2
            where pt.id = ".$pruebaId." and pt.esactivo = 'true'
            order by dt.id asc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        $obj = array();
        if (count($objEntidad) > 0){
          if ($objEntidad[0]['nivel_tipo_id'] == 12){
            $obj = array(0 => array('id' => 1, 'posicion' => 'Primer lugar'));
          } else {
            $obj = array(0 => array('id' => 1, 'posicion' => 'Primer lugar'), 1 => array('id' => 2, 'posicion' => 'Segundo lugar'));
          }
        }
        return $obj;
    }


 

    public function openRegisterAction(Request $request){
    	// get the send values
    	$jsonData = base64_decode($request->get('jsondata'));
    	// $arrData = json_decode($jsonData,true);
    	//$arrCouchs = $this->getTheCouch(json_decode($jsonData,true));
    	// $showOptionRegister = sizeof($arrCouchs)>0?false:true;
        $showOptionRegister = true;
    	return $this->render('SieJuegosBundle:RegisterPersonStudent:openRegister.html.twig',array(
    		'jsondata' => $jsonData,
    		'showOptionRegister' => $showOptionRegister,
    		'form'=>$this->formOpenRegister($jsonData)->createView(),

    	));
    }

    private function formOpenRegister($jsonData){
    	
        return $this->createFormBuilder()
            ->add('carnet', 'text', array('label'=>'CI:', 'attr'=>array('class'=>'form-control','placeholder'=>'Carnet Identidad')))
            ->add('complemento', 'text', array('label'=>'Complemento:', 'attr'=>array('class'=>'form-control','placeholder'=>'Complemento','maxlength'=>2)))
            ->add('jsonIdInscription',           'hidden', array('attr'=>array('value'=>$jsonData)))     

            ->add('find', 'button', array('label'=>'Buscar', 'attr'=>array('onclick'=>'lookForPerson()','class'=>'btn btn-success')))
            ->getForm()
            ;
    }

    public function findPersonAction(Request $request){
    	//get the values send
    	$arrForm =  $request->get('form');
        // dump($arrForm);die;
        $arrInscripcion = json_decode($arrForm['jsonIdInscription'],true);
    	$carnet = $arrForm['carnet'];
    	$complemento =  $arrForm['complemento'];
        $inscripcionJuegosId =  $arrInscripcion[0];
    	//create db conexion 
    	$em = $this->getDoctrine()->getManager();
    	$arrConditionSql = array(
    		'carnet'      => $carnet,
    		'complemento' => $complemento
    	);

    	//check if the group has a couch

    	// $arrCouchs = $this->getTheCouch(json_decode($arrForm['jsonIdInscription'],true));
        // $showOptionRegister = sizeof($arrCouchs)>0?false:true;
        $showOptionRegister = true;
    	$arrData = array();
        $objPerson  = array();
        $arrayComision =  array();
    	if($showOptionRegister){
			$objPerson = $em->getRepository('SieAppWebBundle:Persona')->findOneBy($arrConditionSql);
			if($objPerson){        
                $objEstudianteInscripcionJuegos = $em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos')->findOneBy(array('id'=>$inscripcionJuegosId));
                $nivelId = $objEstudianteInscripcionJuegos->getPruebaTipo()->getDisciplinaTipo()->getNivelTipo()->getId();
                $arrayComision = $this->getComisionNivel($nivelId);
				$arrData['personId']=$objPerson->getId();
				// $arrData['comisionTipoId']=140;
				$arrData['form']= $arrForm;
			// dump(json_encode($arrData));die;
			}else{
				$message = 'Persona no encontrada';
				$this->addFlash('noCouch', $message);
			}

    	}else{
    		$message = 'Entrenador ya registrado...';
			$this->addFlash('noCouch', $message);
    	}

		return $this->render('SieJuegosBundle:RegisterPersonStudent:personData.html.twig',array(
			'dataPerson' => $objPerson,
			'form'=>$this->formFindPerson(json_encode($arrData), $arrayComision)->createView()
		));   	    
    	
    }


    private function formFindPerson($jsonData, $arrayComision){


    	return $this->createFormBuilder()
                ->add('dataToRegister','hidden',array('attr'=>array('value'=>$jsonData)))
                ->add('comision','choice',
                      array('label' => 'Comisión',
                            'choices' => ($arrayComision),
                            'attr' => array('class' => 'form-control col-lg-6 col-md-6 col-sm-6')))
    			->add('find', 'button', array('label'=>'Registrar', 'attr'=>array('onclick'=>'registerPerson()', 'class'=>'btn btn-info text-center btn-block col-lg-6 col-md-6 col-sm-6')))
            	->getForm();

    }



    /**
     * busca la comision ppor nivel
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function getComisionNivel($nivelId) {
        $comision = array();
        if ($nivelId == 12) {
            // $comision = array('139' => 'Acompañante Entrenador', '12' => 'Acompañante Maestro', '13' => 'Acompañante Padre de Familia', '102' => 'Acompañante Delegado');
            $comision = array('139' => 'Acompañante Entrenador');
        } else {
            // $comision = array('140' => 'Acompañante Entrenador', '141' => 'Acompañante Maestro', '142' => 'Acompañante Padre de Familia', '143' => 'Acompañante Delegado');
            $comision = array('140' => 'Acompañante Entrenador');
        }
        return $comision;              
    }


    public function registerPersonAction(Request $request){
    	// dump($request);die;
    	//get the send values
    	$form = $request->get('form');

    	$arrForm = json_decode($form['dataToRegister'],true);
    	
// dump($arrForm);die;
    	$personId = $arrForm['personId'];
    	$comisionTipoId = $arrForm['comisionTipoId'];
    	$arrIdInscription = json_decode($arrForm['form']['jsonIdInscription'],true);

    	// dump($arrForm);
    	// dump($arrIdInscription);
    	// die;

    	// $arrInscriptionInfo = json_decode($arrForm['form']['data'],true);
    	// //get info to load the all couchs 
    	// $arrLoadInfo = json_decode($form['dataToRegister'],true);
    	// $arrData = json_decode($arrLoadInfo['form']['comisionJuegos'],true);
    
    	//create db conexino
    	
    	$em = $this->getDoctrine()->getManager();
    	try {
			$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('jdp_persona_inscripcion_juegos');");
        	$query->execute();
    		foreach ($arrIdInscription as $key => $value) {
    			// dump($value.' '.$personId );
    			$objJdpPersonaInscripcion =  new JdpPersonaInscripcionJuegos();
    			$objJdpPersonaInscripcion->setEstudianteInscripcionJuegos($em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos')->find($value));
    			$objJdpPersonaInscripcion->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($personId));
    			$objJdpPersonaInscripcion->setComisionTipo($em->getRepository('SieAppWebBundle:JdpComisionTipo')->find($comisionTipoId));
    			$em->persist($objJdpPersonaInscripcion);
    			$em->flush();
    		    			# code...
    		}  
    		$arrTransactionData = array(
    			'message'=>'Datos registrados correctamente...',
    			'alertType'=>'success',
    		);

    	
    	} catch (Exception $e) {
    		$arrTransactionData = array(
    			'message'=>'Error en reporte'.$e,
    			'alertType'=>'danger',
    		);
    	}
    	
    	return $this->render('SieJuegosBundle:RegisterPersonStudent:registerPerson.html.twig',array(
    		'transactionData' => $arrTransactionData,
    	));
    }




    public function registerPersonJsonAction(Request $request){
    	// dump($request);die;
    	//get the send values
    	$form = $request->get('form');

    	$arrForm = json_decode($form['dataToRegister'],true);
    	
    	$personId = $arrForm['personId'];
    	// $comisionTipoId = $arrForm['comisionTipoId'];
        // dump($form['comision']);die;
    	$comisionTipoId = $form['comision'];
    	$arrIdInscription = json_decode($arrForm['form']['jsonIdInscription'],true);
    	
        $em = $this->getDoctrine()->getManager();
        
        $entityPersona = $em->getRepository('SieAppWebBundle:Persona')->find($personId);
        $nombreApellidoPersona = $entityPersona->getPaterno()." ".$entityPersona->getMaterno()." ".$entityPersona->getNombre();
        $entrenador = $nombreApellidoPersona;

    	// dump($arrForm);
    	// dump($arrIdInscription);
    	// die;

    	// $arrInscriptionInfo = json_decode($arrForm['form']['data'],true);
    	// //get info to load the all couchs 
    	// $arrLoadInfo = json_decode($form['dataToRegister'],true);
    	// $arrData = json_decode($arrLoadInfo['form']['comisionJuegos'],true);
    
    	//create db conexino
    	try {
			$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('jdp_persona_inscripcion_juegos');");
        	$query->execute();            
            foreach ($arrIdInscription as $key => $value) {
                $objJdpPersonaInscripcion = $em->getRepository('SieAppWebBundle:JdpPersonaInscripcionJuegos')->findOneBy(array('estudianteInscripcionJuegos' => $value, 'comisionTipo'=> $comisionTipoId));
                if(count($objJdpPersonaInscripcion) <= 0){
                    $objJdpPersonaInscripcion =  new JdpPersonaInscripcionJuegos();
                    $objJdpPersonaInscripcion->setEstudianteInscripcionJuegos($em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos')->find($value));
                    $objJdpPersonaInscripcion->setComisionTipo($em->getRepository('SieAppWebBundle:JdpComisionTipo')->find($comisionTipoId));
                } 
                $objJdpPersonaInscripcion->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($personId)); 			
    			$em->persist($objJdpPersonaInscripcion);
    			$em->flush();
    		    			# code...
    		}  
    		// $arrTransactionData = array(
    		// 	'message'=>'Datos registrados correctamente...',
    		// 	'alertType'=>'success',
    		// );
            $arrTransactionData = array('entrenador' => $entrenador, 'msg_correcto' => 'Datos registrados correctamente', 'msg_incorrecto' => '' );

    	
    	} catch (Exception $e) {
    		// $arrTransactionData = array(
    		// 	'message'=>'Error en reporte'.$e,
    		// 	'alertType'=>'danger',
    		// );
            $arrTransactionData = array('entrenador' => $entrenador, 'msg_correcto' => '', 'msg_incorrecto' => 'Error, intene nuevamente' );
    	}

        $response = new JsonResponse();
        return $response->setData($arrTransactionData); 
    	
    	// return $this->render('SieJuegosBundle:RegisterPersonStudent:registerPerson.html.twig',array(
    	// 	'transactionData' => $arrTransactionData,
    	// ));
    }


    public function showCouchAction(Request $request){
    	//get the send datas
    	$jsondata = base64_decode($request->get('jsondata')) ;
    	$jsonMainData = $request->get('jsonMainData');

    	$arrMainData = json_decode($jsonMainData, true);
    	
    	$arrCouchs = $this->getTheCouch(json_decode($jsondata,true));
    	
		$swShowCouch = sizeof($arrCouchs)>0?true:false;
    	return $this->render('SieJuegosBundle:RegisterPersonStudent:showCouch.html.twig',array(
			'dataPerson' => $arrCouchs,
			'swShowCouch' => $swShowCouch,
		));
    

    }

    private function getTheCouch($arrData){
    	// creaet db conexion
    	$em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:JdpPersonaInscripcionJuegos');
        $query = $entity->createQueryBuilder('jdppij')
                ->select('jdppij')      
                ->where('jdppij.estudianteInscripcionJuegos IN (:eijId)')
                ->setParameter('eijId', $arrData)
                ->getQuery();
                
        $objInscritos = $query->getResult();

        $arrCouchs = array(
        );
        $arrIdInscription = array();
        if(sizeof($objInscritos)>0){

	        foreach ($objInscritos as $key => $value) {
	        	
	        	$arrIdInscription[]=$value->getId();
		        $jsonIdInscription = json_encode($arrIdInscription);
	        	if($value){
		        	$arrCouchs['carnet'] = $value->getPersona()->getCarnet();
		        	$arrCouchs['complemento'] = $value->getPersona()->getComplemento();
		        	$arrCouchs['paterno'] = $value->getPersona()->getPaterno();
		        	$arrCouchs['materno'] = $value->getPersona()->getMaterno();
		        	$arrCouchs['nombre'] = $value->getPersona()->getNombre();
	        	}
	        }
	        $arrCouchs['idRemove'] = $jsonIdInscription;

        }

        return $arrCouchs;

    }

    public function getEquipoCouch($estudianteInscripcionJuegosId, $faseId){
    	// creaet db conexion
        $em = $this->getDoctrine()->getManager();
        
        $equipoEstudianteInscripcionJuegosEntity = $em->getRepository('SieAppWebBundle:JdpEquipoEstudianteInscripcionJuegos')->findOneBy(array('estudianteInscripcionJuegos' => $estudianteInscripcionJuegosId));                        
        //dump($equipoEstudianteInscripcionJuegosEntity);die;
        if(count($equipoEstudianteInscripcionJuegosEntity)>0){
            $equipoId = $equipoEstudianteInscripcionJuegosEntity->getEquipoId();
            $pruebaId = $equipoEstudianteInscripcionJuegosEntity->getEstudianteInscripcionJuegos()->getPruebaTipo()->getId();
            //dump($equipoEstudianteInscripcionJuegosEntity);dump($equipoId);dump($pruebaId);

            $repositoryVerInsPru = $this->getDoctrine()->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos');
            $queryVerInsPru = $repositoryVerInsPru->createQueryBuilder('eij')
                ->select('distinct eeij.equipoId as equipoId, eeij.equipoNombre as equipoNombre, p.id as personaId, p.nombre, p.paterno, p.materno')
                ->innerJoin('SieAppWebBundle:JdpEquipoEstudianteInscripcionJuegos','eeij','WITH','eeij.estudianteInscripcionJuegos = eij.id')
                ->innerJoin('SieAppWebBundle:JdpPersonaInscripcionJuegos','pij','WITH','pij.estudianteInscripcionJuegos = eij.id')
                ->innerJoin('SieAppWebBundle:Persona','p','WITH','p.id = pij.persona')
                ->where('eeij.equipoId = :codEquipo')
                ->andwhere('eij.faseTipo = :codFase')
                ->andwhere('eij.pruebaTipo = :codPrueba')
                ->setParameter('codEquipo', $equipoId)
                ->setParameter('codFase', $faseId)
                ->setParameter('codPrueba', $pruebaId)
                ->getQuery();
            $verInsPru = $queryVerInsPru->getArrayResult();
        } else {
            $verInsPru = array();
        }
        
        //dump($verInsPru);die;
        if (count($verInsPru) > 0){
            return $verInsPru[0];
        } else {
            return array();
        }
    }


    public function removeCouchAction(Request $request){
    	//get the send data
    	$jsonIdRemove = base64_decode($request->get('jsonIdRemove'));
    	$arrIdRemove = json_decode($jsonIdRemove, true);
    	$em = $this->getDoctrine()->getManager();

    	try {
	    	foreach ($arrIdRemove as $key => $value) {
	    		# code...
	    		$objJdpPersonaInscripcionJuegos = $em->getRepository('SieAppWebBundle:JdpPersonaInscripcionJuegos')->find($value);
	    		$em->remove($objJdpPersonaInscripcionJuegos);
	    		$em->flush();
	    	}
	    	die('correct');
	    	return array('error'=>false, 'meesage'=>'correct');
    		
    	} catch (Exception $e) {
    		die('error code');
    	}
    }




    

}
