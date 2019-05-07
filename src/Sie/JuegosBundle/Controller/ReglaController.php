<?php

namespace Sie\JuegosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

use Sie\JuegosBundle\Controller\EstudianteInscripcionJuegosController as estudianteInscripcionJuegosController;

class ReglaController extends Controller
{
	public $session;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        //$this->aCursos = $this->fillCursos();
    }

    //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida la clasificacion de un estudiante deportistas gestion, prueba y fase
    // PARAMETROS: estudianteInscripcionId, gestionId, pruebaId, faseId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function valEstudianteClasificacionJuegos($estudianteInscripcionId, $gestionId, $pruebaId, $faseId, $equipoId, $lugarTipoId, $posicion){            
        try{
            // verifica si ya cuenta con una inscripcion en la prueba, gestion y fase seleccionada            
            $estudianteInscripcionJuegosController = new estudianteInscripcionJuegosController();
            $estudianteInscripcionJuegosController->setContainer($this->container);
            //dump($estudianteInscripcionId);dump($pruebaId);dump($gestionId);dump($faseId);die;
            $estudianteInscripcionJuegos = $estudianteInscripcionJuegosController->getEstudianteInscripcionGestionFasePrueba($estudianteInscripcionId, $pruebaId, $gestionId, $faseId);
            //dump($estudianteInscripcionJuegos[0]->getEstudianteInscripcion()->getEstudiante()->getNombre());die;
            
            $em = $this->getDoctrine()->getManager();
            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
            $estudianteNombre = $estudianteInscripcion->getEstudiante()->getNombre();
            $estudiantePaterno = $estudianteInscripcion->getEstudiante()->getPaterno();
            $estudianteMaterno = $estudianteInscripcion->getEstudiante()->getMaterno();
            $estudianteNombreApellido = $estudianteNombre . ' ' . $estudiantePaterno . ' ' . $estudianteMaterno;

            //dump($estudianteInscripcionId);
            if (count($estudianteInscripcionJuegos)>0){
                //dump('ya tiene la prueba seleccionada');die;
                return array('0' => false, '1' => $estudianteNombreApellido . ' ya registrado en la prueba seleccionada');
            }
            
            $msg = $this->valDisciplinaParticipacion($estudianteInscripcionId, $gestionId, $pruebaId, $faseId); 
                                     
            if (!$msg[0]){
                //dump('no tiene disciplina');die;
                $pruebaEntity = $em->getRepository('SieAppWebBundle:JdpPruebaTipo')->findOneBy(array('id' => $pruebaId));
                $disciplinaId = $pruebaEntity->getDisciplinaTipo()->getId();
                $disciplinaEstudianteInscripcion = $estudianteInscripcionJuegosController->getDisciplinaEstudianteInscripcion($estudianteInscripcionId, $gestionId, $faseId);
                
                $msg1 = array('0' => false, '1' => $estudianteNombreApellido.' no puede registrarse en otras disciplinas');
                foreach ($disciplinaEstudianteInscripcion as $disciplina) {
                    $disciplinaInscripcionId = $disciplina->getId();
                    if ($disciplinaInscripcionId == $disciplinaId){
                        $msg1 = array('0' => true, '1' => '');
                    }
                }
                if(!$msg1[0]){
                    return array('0' => $msg1[0], '1' => $estudianteNombreApellido.' '.$msg1[1]);
                    //return $estudianteNombreApellido.' '.$msg;                    
                }                
            }
            
            $msg = $this->valPruebaParticipacion($estudianteInscripcionId, $gestionId, $pruebaId, $faseId);  

            if (!$msg[0]){
                //dump('no tiene prueba');die;
                $msg1 = $this->valConjuntoPruebaParticipacion($estudianteInscripcionId, $gestionId, $pruebaId, $faseId);
                if (!$msg1[0]){
                    //dump($msg[1]);die;
                    return array('0' => $msg1[0], '1' => $estudianteNombreApellido.' '.$msg1[1]);
                }                
                $msg = array('0' => true, '1' => '');
            }

            if ($msg[0]){
                //dump('no tiene prueba');die;
                // $msg1 = $this->valConjuntoPruebaParticipacion($estudianteInscripcionId, $gestionId, $pruebaId, $faseId);
                // if (!$msg1[0]){
                //     //dump($msg[1]);die;
                //     return $msg1;
                // }
                
                $msg2 = $this->valPruebaRegla($estudianteInscripcionId, $gestionId, $pruebaId, $faseId, $equipoId, $posicion, $lugarTipoId);
  
                if (!$msg2[0]){
                    //dump($msg[1]);die;
                    return array('0' => $msg2[0], '1' => $estudianteNombreApellido.' '.$msg2[1]);
                }
                
                $msg = array('0' => true, '1' => '');
            }
            
            if ($msg[0] and $msg[1] == ''){
                $msg = array('0' => $msg[0], '1' => $estudianteNombreApellido.' '.$msg[1]);
            }
            return $msg;
        } catch (Exception $e) {
            return array('0' => false, '1' => 'Error al procesar la información, intente nuevamente');
        }
    }



    //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida la inscripcion de un estudiante deportistas gestion, prueba y fase
    // PARAMETROS: estudianteInscripcionId, gestionId, pruebaId, faseId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function valEstudianteInscripcionJuegos($estudianteInscripcionId, $gestionId, $pruebaId, $faseId, $equipoId, $posicion, $lugarTipoId){            
        try{
            // verifica si ya cuenta con una inscripcion en la prueba, gestion y fase seleccionada            
            $estudianteInscripcionJuegosController = new estudianteInscripcionJuegosController();
            $estudianteInscripcionJuegosController->setContainer($this->container);
            $estudianteInscripcionJuegos = $estudianteInscripcionJuegosController->getEstudianteInscripcionGestionFasePrueba($estudianteInscripcionId, $pruebaId, $gestionId, $faseId);
            //dump($estudianteInscripcionJuegos[0]->getEstudianteInscripcion()->getEstudiante()->getNombre());die;

            $em = $this->getDoctrine()->getManager();
            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
            $estudianteNombre = $estudianteInscripcion->getEstudiante()->getNombre();
            $estudiantePaterno = $estudianteInscripcion->getEstudiante()->getPaterno();
            $estudianteMaterno = $estudianteInscripcion->getEstudiante()->getMaterno();
            $estudianteNombreApellido = $estudianteNombre . ' ' . $estudiantePaterno . ' ' . $estudianteMaterno;

            //dump($estudianteInscripcionId);
            if (count($estudianteInscripcionJuegos)>0){
                //dump('ya tiene la prueba seleccionada');die;
                return array('0' => false, '1' => $estudianteNombreApellido . ' ya registrado en la prueba seleccionada');
            }
            
            $msg = $this->valDisciplinaParticipacion($estudianteInscripcionId, $gestionId, $pruebaId, $faseId); 
                                     
            if (!$msg[0]){
                //dump('no tiene disciplina');die;
                $pruebaEntity = $em->getRepository('SieAppWebBundle:JdpPruebaTipo')->findOneBy(array('id' => $pruebaId));
                $disciplinaId = $pruebaEntity->getDisciplinaTipo()->getId();
                $disciplinaEstudianteInscripcion = $estudianteInscripcionJuegosController->getDisciplinaEstudianteInscripcion($estudianteInscripcionId, $gestionId, $faseId);
                
                $msg1 = array('0' => false, '1' => $estudianteNombreApellido.' no puede registrarse en otras disciplinas');
                foreach ($disciplinaEstudianteInscripcion as $disciplina) {
                    $disciplinaInscripcionId = $disciplina->getId();
                    if ($disciplinaInscripcionId == $disciplinaId){
                        $msg1 = array('0' => true, '1' => '');
                    }
                }
                if(!$msg1[0]){
                    return array('0' => $msg1[0], '1' => $estudianteNombreApellido.' '.$msg1[1]);
                    //return $estudianteNombreApellido.' '.$msg;                    
                }                
            }
            
            $msg = $this->valPruebaParticipacion($estudianteInscripcionId, $gestionId, $pruebaId, $faseId);   

            if (!$msg[0]){
                //dump('no tiene prueba');die;
                $msg1 = $this->valConjuntoPruebaParticipacion($estudianteInscripcionId, $gestionId, $pruebaId, $faseId);
                if (!$msg1[0]){
                    //dump($msg[1]);die;
                    return array('0' => $msg1[0], '1' => $estudianteNombreApellido.' '.$msg1[1]);
                }                
                $msg = array('0' => true, '1' => '');
            }

            if ($msg[0]){
                //dump('no tiene prueba');die;
                // $msg1 = $this->valConjuntoPruebaParticipacion($estudianteInscripcionId, $gestionId, $pruebaId, $faseId);
                // if (!$msg1[0]){
                //     //dump($msg[1]);die;
                //     return $msg1;
                // }
                
                $msg2 = $this->valPruebaRegla($estudianteInscripcionId, $gestionId, $pruebaId, $faseId, $equipoId, $posicion, $lugarTipoId);
                if (!$msg2[0]){
                    //dump($msg[1]);die;
                    return array('0' => $msg2[0], '1' => $estudianteNombreApellido.' '.$msg2[1]);
                }
                
                $msg = array('0' => true, '1' => '');
            }
            
            if ($msg[0] and $msg[1] == ''){
                $msg = array('0' => $msg[0], '1' => $estudianteNombreApellido.' '.$msg[1]);
            }
            return $msg;
        } catch (Exception $e) {
            return array('0' => false, '1' => 'Error al procesar la información, intente nuevamente');
        }
    }

    //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida la cantidad de participaciones permitidas en las disciplinas segun gestion y fase
    // PARAMETROS: estudianteInscripcionId, gestionId, pruebaId, faseId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function valDisciplinaParticipacion($estudianteInscripcionId, $gestionId, $pruebaId, $faseId){        
        $em = $this->getDoctrine()->getManager();

        $pruebaEntity = $em->getRepository('SieAppWebBundle:JdpPruebaTipo')->findOneBy(array('id' => $pruebaId));
        $disciplinaId = $pruebaEntity->getDisciplinaTipo()->getId();   
        $pruebaParticipacionId = $pruebaEntity->getPruebaParticipacionTipo()->getId();    
        $disciplinaParticipacionId = $pruebaEntity->getDisciplinaTipo()->getDisciplinaParticipacionTipo()->getId();   
        $disciplinaParticipacionNombre = $pruebaEntity->getDisciplinaTipo()->getDisciplinaParticipacionTipo()->getDisciplinaParticipacion(); 

        $disciplinaCantidad = $this->getDisciplinaParticipacionMaximo();
        $disciplinaParticipacionCantidad = $pruebaEntity->getDisciplinaTipo()->getDisciplinaParticipacionTipo()->getCantidad();
        
        $estudianteInscripcionJuegosController = new estudianteInscripcionJuegosController();
        $estudianteInscripcionJuegosController->setContainer($this->container);
        $listaDisciplinaEstudiante = $estudianteInscripcionJuegosController->valDisciplinaEstudianteInscripcion($estudianteInscripcionId, $gestionId, $faseId);
        $cantidadDisciplinaEstudiante = count($listaDisciplinaEstudiante);

        $listaDisciplinaParticipacionEstudiante = $estudianteInscripcionJuegosController->valDisciplinaParticipacionEstudianteInscripcion($estudianteInscripcionId, $disciplinaParticipacionId, $gestionId, $faseId);
        $cantidadDisciplinaParticipacionEstudiante = count($listaDisciplinaParticipacionEstudiante);
        
        $msg = array('0' => true, '1' => '');

        //dump($cantidadDisciplinaEstudiante);dump($disciplinaCantidad);

        if($cantidadDisciplinaEstudiante >= $disciplinaCantidad){
            $msg = array('0' => false, '1' => 'ya cuenta con la cantidad maxima de disciplinas permitidas');
        }

        //dump($cantidadDisciplinaParticipacionEstudiante);dump($disciplinaParticipacionCantidad);die;

        if($cantidadDisciplinaParticipacionEstudiante >= $disciplinaParticipacionCantidad){
            $msg = array('0' => false, '1' => 'ya cuenta con la cantidad maxima de participaciones permitidas en la Disciplina '.$disciplinaParticipacionNombre.', intente nuevamente');
        }
        return $msg;
    }

    //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida la cantidad de participaciones permitidas en las pruebas segun gestion y fase
    // PARAMETROS: estudianteInscripcionId, gestionId, pruebaId, faseId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function valPruebaParticipacion($estudianteInscripcionId, $gestionId, $pruebaId, $faseId){        
        $em = $this->getDoctrine()->getManager();

        $pruebaEntity = $em->getRepository('SieAppWebBundle:JdpPruebaTipo')->findOneBy(array('id' => $pruebaId));
        $disciplinaId = $pruebaEntity->getDisciplinaTipo()->getId();   
        $disciplinaNombre = $pruebaEntity->getDisciplinaTipo()->getDisciplina();   
        $pruebaParticipacionId = $pruebaEntity->getPruebaParticipacionTipo()->getId();   
        $pruebaParticipacionCantidad = $pruebaEntity->getPruebaParticipacionTipo()->getCantidad();   
        $pruebaParticipacionNombre = $pruebaEntity->getPruebaParticipacionTipo()->getDisciplinaParticipacion();    

        $estudianteInscripcionJuegosController = new estudianteInscripcionJuegosController();
        $estudianteInscripcionJuegosController->setContainer($this->container);
        $listaPruebaEstudiante = $estudianteInscripcionJuegosController->valPruebaParticipacionEstudianteInscripcion($estudianteInscripcionId, $pruebaParticipacionId, $gestionId, $faseId, $disciplinaId);
        $cantidadPruebaEstudiante = count($listaPruebaEstudiante);
        
        // dump($cantidadPruebaEstudiante);dump($pruebaParticipacionCantidad);die;
        if($cantidadPruebaEstudiante < $pruebaParticipacionCantidad){
            return array('0' => true, '1' => '');
        } else {
            $cantidadDisciplinaPruebaParticipacion = $this->getDisciplinaPruebaParticipacion($disciplinaId, $pruebaParticipacionId);
            // dump($cantidadDisciplinaPruebaParticipacion);dump($cantidadPruebaEstudiante);die;
            
            if ($cantidadPruebaEstudiante < $cantidadDisciplinaPruebaParticipacion){
                return array('0' => true, '1' => '');
            } else {
                return array('0' => false, '1' => 'ya cuenta con la cantidad maxima de participaciones permitidas en la Prueba '.$pruebaParticipacionNombre.', intente nuevamente');
            }
        }
    }

    //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida la cantidad de pruebas en la que puede participar dentro de una disciplina segun gestion y fase
    // PARAMETROS: estudianteInscripcionId, gestionId, pruebaId, faseId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function valConjuntoPruebaParticipacion($estudianteInscripcionId, $gestionId, $pruebaId, $faseId){        
        $cantidadPruebaConjunto = 0;
        $cantidadPruebaEstudiante = 0;
        $em = $this->getDoctrine()->getManager();

        $modalidadPruebaEntity = $em->getRepository('SieAppWebBundle:JdpModalidadPrueba')->findOneBy(array('pruebaTipo' => $pruebaId));
        if(count($modalidadPruebaEntity)>0){
            $modalidadId = $modalidadPruebaEntity->getModalidadTipo()->getId();  
            $disciplinaId = $modalidadPruebaEntity->getPruebaTipo()->getDisciplinaTipo()->getId(); 
            $disciplinaNombre = $modalidadPruebaEntity->getPruebaTipo()->getDisciplinaTipo()->getDisciplina();   
            $generoId = $modalidadPruebaEntity->getPruebaTipo()->getGeneroTipo()->getId();   

            // $conjuntoPruebaEntity = $em->getRepository('SieAppWebBundle:JdpPruebaTipo')->findOneBy(array('disciplinaTipo' => $disciplinaId, 'generoTipo' => $generoId));
            $conjuntoPruebaEntity= $this->getDoctrine()->getRepository('SieAppWebBundle:JdpModalidadPrueba');
            $query = $conjuntoPruebaEntity->createQueryBuilder('mp')
                ->innerJoin('SieAppWebBundle:JdpPruebaTipo','pt','WITH','pt.id = mp.pruebaTipo')
                ->where('pt.disciplinaTipo = :codDisciplina')
                ->andwhere('mp.modalidadTipo = :codModalidad')
                ->andwhere('pt.generoTipo = :codGenero')
                ->setParameter('codDisciplina', $disciplinaId)
                ->setParameter('codModalidad', $modalidadId)
                ->setParameter('codGenero', $generoId)
                ->getQuery();
            $conjuntoPruebaEntity = $query->getResult();
            // dump($conjuntoPruebaEntity);die;

            $estudianteInscripcionJuegosController = new estudianteInscripcionJuegosController();
            $estudianteInscripcionJuegosController->setContainer($this->container);
            $listaPruebaEstudiante = $estudianteInscripcionJuegosController->getEstudianteInscripcionGestionFaseDisciplinaGeneroModalidad($estudianteInscripcionId, $disciplinaId, $gestionId, $faseId, $generoId, $modalidadId);

            //dump(count($conjuntoPruebaEntity));dump(count($listaPruebaEstudiante));dump($modalidadPruebaEntity);dump($conjuntoPruebaEntity);dump($listaPruebaEstudiante);die;
            $cantidadPruebaConjunto = count($conjuntoPruebaEntity);
            $cantidadPruebaEstudiante = count($listaPruebaEstudiante);

            // dump($cantidadPruebaEstudiante);dump($cantidadPruebaConjunto);
            if($cantidadPruebaEstudiante < $cantidadPruebaConjunto){
                return array('0' => true, '1' => '');
            } else {
                return array('0' => false, '1' => 'ya cuenta con la cantidad maxima de pruebas permitidas dentro de una disciplina');
            }
        }  else {
            return array('0' => false, '1' => 'no cuenta con mas pruebas por registrase en la disciplina');
        }    

        
    }

        //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida la prueba en funcion a la regla según institucion, gestion, prueba y fase
    // PARAMETROS: estudianteInscripcionId, gestionId, pruebaId, faseId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function valPruebaRegla($estudianteInscripcionId, $gestionId, $pruebaId, $faseId, $equipoId, $posicion, $entidadUsuarioId){               
        $pruebaReglaEntity = $this->getPruebaRegla($gestionId, $faseId, $pruebaId);

        if(count($pruebaReglaEntity)<=0){
            return array('0' => false, '1' => 'La prueba no cuenta con una norma definida, comuniquese con su administrador');
        }

        $iniGestionId = $pruebaReglaEntity->getIniGestionTipoId();
        $finGestionId = $pruebaReglaEntity->getFinGestionTipoId();
        $cupoInscripcion = $pruebaReglaEntity->getCupoInscripcion();
        $cupoPresentacion = $pruebaReglaEntity->getCupoPresentacion();
        $pruebaParticipacionId = $pruebaReglaEntity->getPruebaTipo()->getPruebaParticipacionTipo()->getId();
        $pruebaNombre = $pruebaReglaEntity->getPruebaTipo()->getPrueba();
        
        $em = $this->getDoctrine()->getManager();
        $estudianteInscripcionEntity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));

        $estadoMatriculaInicioId = $estudianteInscripcionEntity->getEstadomatriculaInicioTipo()->getId();
        if($estadoMatriculaInicioId == 9 or $estadoMatriculaInicioId == 15){
            return array('0' => false, '1' => 'no puede registrar a estudiantes con traslado');
        }

        $institucioneducativaCursoEntity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('id' => $estudianteInscripcionEntity->getInstitucioneducativaCurso()->getId()));
        
        switch ($faseId) {
            case 2:
                $institucionId = $this->session->get('roluserlugarid');
                $xCupo = 1;
                if ($entidadUsuarioId == 31642){ // MAGDALENA/ BAURES/ HUACARAJE
                    $xCupo = 3;
                }
                if ($entidadUsuarioId == 31637){
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31639){
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31640  ){
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31552  ){
                    $xCupo = 3;
                }
                if ($entidadUsuarioId == 31553  ){
                    $xCupo = 3;
                }
                if ($entidadUsuarioId == 31613  ){
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31590  ){
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31612  ){
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31610  ){
                    $xCupo = 3;
                }
                if ($entidadUsuarioId == 31617  ){
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31554  ){
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31363  ){
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31564  ){
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31458  ){
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31459  ){
                    $xCupo = 3;
                }
                if ($entidadUsuarioId == 79356  ){
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31508  ){
                    $xCupo = 3;
                }
                if ($entidadUsuarioId == 31622  ){ // SANTA CRUZ 1
                    $xCupo = 7;
                }
                if ($entidadUsuarioId == 31623  ){ // SANTA CRUZ 2
                    $xCupo = 6;
                }
                if ($entidadUsuarioId == 31624  ){ // SANTA CRUZ 3
                    $xCupo = 5;
                }
                if ($entidadUsuarioId == 79359  ){ // PLAN TRES MILL (SANTA CRUZ 4)
                    $xCupo = 6;
                }
                if ($entidadUsuarioId == 31504  ){
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31505  ){
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31530  ){
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31455  ){ // LA PAZ 1
                    $xCupo = 3;
                }
                if ($entidadUsuarioId == 31456  ){ // LA PAZ 2
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31457  ){ // LA PAZ 3
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31395  ){  // ACHACACHI
                    $xCupo = 5;
                }
                if ($entidadUsuarioId == 31426  ){  // CAJUATA
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31398  ){  //  CAQUIAVIRI
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31454  ){  // CARANAVI
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31448  ){  // CHARAZANI
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31443  ){  // COLQUENCHA
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31397  ){  // CORO CORO
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31403  ){  // PUERTO ACOSTA
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31451  ){  // SAN PEDRO DE CURACHUARA
                    $xCupo = 3;
                }
                if ($entidadUsuarioId == 31450  ){  // SAN PEDRO DE TIQUINA
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31411  ){  // TACACOMA
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 31422  ){  // YACO MALLA
                    $xCupo = 2;
                }
                if ($entidadUsuarioId == 99226  ){  // VILLA ABECIA - LAS CARRERAS
                    $xCupo = 2;
                }
                $cupoPresentacion = $cupoPresentacion * $xCupo;
                if($pruebaParticipacionId == 2){   
                    $cupoInscripcion = $cupoInscripcion * $xCupo;
                }
                break;
            case 3:
                $institucionId = $this->session->get('roluserlugarid');
                break;
            case 4:
                $institucionId = $this->session->get('roluserlugarid');
                break;
            default:                
                $institucionId = $institucioneducativaCursoEntity->getInstitucioneducativa()->getId();
                break;
        }
        $estudianteFechaNacimiento = $estudianteInscripcionEntity->getEstudiante()->getFechaNacimiento();
        $estudianteGestionNacimiento = date_format($estudianteFechaNacimiento,'Y');


        $estudianteInscripcionJuegosController = new estudianteInscripcionJuegosController();
        $estudianteInscripcionJuegosController->setContainer($this->container);
        if($pruebaParticipacionId == 1){   
            $listaEquipoEstudiantePruebaInstitucion = $estudianteInscripcionJuegosController->getEquipoEstudianteInscripcionInstitucionGestionFasePrueba($institucionId, $pruebaId, $gestionId, $faseId, $equipoId, $posicion);
            //dump($listaEquipoEstudiantePruebaInstitucion);die;
            $cantidadListaEquipoEstudiantePruebaInstitucion = count($listaEquipoEstudiantePruebaInstitucion);

            //dump($cantidadListaEquipoEstudiantePruebaInstitucion);dump($cupoInscripcion);die;
            if($cantidadListaEquipoEstudiantePruebaInstitucion >= $cupoInscripcion){
                return array('0' => false, '1' => 'no puede registrar a mas estudiantes en el equipo'.$equipoId.' ('.$pruebaNombre.')');
            }

            $listaEquipoPruebaInstitucion = $estudianteInscripcionJuegosController->getEquipoInscripcionInstitucionGestionFasePrueba($institucionId, $pruebaId, $gestionId, $faseId, $posicion);
            $cantidadListaEquipoPruebaInstitucion = count($listaEquipoPruebaInstitucion);
            //dump($cantidadListaEquipoPruebaInstitucion);dump($cupoPresentacion);die;

            if($cantidadListaEquipoPruebaInstitucion > $cupoPresentacion){
                return array('0' => false, '1' => 'no puede registrar a mas equipos en la prueba '.$pruebaNombre);
            } else {
                //dump($cantidadListaEquipoPruebaInstitucion);dump($cupoPresentacion);die();
                if($cantidadListaEquipoPruebaInstitucion == $cupoPresentacion){
                    $listaEquipoExiste = false;
                    foreach ($listaEquipoPruebaInstitucion as $listaEquipo) {
                        $listaEquipoId = $listaEquipo['equipoId'];
                        if ($listaEquipoId == $equipoId){
                            $listaEquipoExiste = true;
                        }
                    }
                    if(!$listaEquipoExiste){
                        return array('0' => false, '1' => 'no puede registrar a mas equipos en la prueba '.$pruebaNombre);
                    } 
                } 
            }          
        } else {
            $listaPruebaInstitucion = $estudianteInscripcionJuegosController->getEstudianteInscripcionInstitucionGestionFasePrueba($institucionId, $pruebaId, $gestionId, $faseId, $posicion);
            $cantidadListaPruebaInstitucion = count($listaPruebaInstitucion);
            //dump($institucionId);die;
            if($cantidadListaPruebaInstitucion >= $cupoInscripcion){
                return array('0' => false, '1' => 'no puede registrar a mas estudiantes en '.$pruebaNombre);
            }
        }

        if($estudianteGestionNacimiento < $iniGestionId or $estudianteGestionNacimiento > $finGestionId){
            return array('0' => false, '1' => 'Año de nacimiento fuera del rango permitido ('.$iniGestionId.' - '.$finGestionId.')');
        }

        return $msg = array('0' => true, '1' => ''); 
    }


    /**
     * busca datos sobre la regla de la prueba
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function getPruebaRegla($gestion,$fase,$prueba) {
        $em = $this->getDoctrine()->getManager();
        $entity= $this->getDoctrine()->getRepository('SieAppWebBundle:JdpPruebaRegla');
        $query = $entity->createQueryBuilder('pr')
            ->innerJoin('SieAppWebBundle:JdpPruebaTipo','pt','WITH','pt.id = pr.pruebaTipo')
            ->innerJoin('SieAppWebBundle:GestionTipo','gt','WITH','gt.id = pr.gestionTipo')
            ->innerJoin('SieAppWebBundle:JdpFaseTipo','ft','WITH','ft.id = pr.faseTipo')
            ->where('pt.id = :codPrueba')
            ->andwhere('gt.id = :codGestion')
            ->andwhere('ft.id = :codFase')
            ->setParameter('codPrueba', $prueba)
            ->setParameter('codGestion', $gestion)
            ->setParameter('codFase', $fase)
            ->getQuery();
        $entity = $query->getResult();
        if (count($entity) > 0){
            return $entity[0]; 
        } else {
            return $entity; 
        }               
    }

    /**
     * busca el maximo id equipo creado
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function getDisciplinaParticipacionMaximo() {
        $em = $this->getDoctrine()->getManager();
        $entity= $this->getDoctrine()->getRepository('SieAppWebBundle:JdpDisciplinaParticipacionTipo');
        $query = $entity->createQueryBuilder('dpt')
            ->select('sum(dpt.cantidad) as cantidad')
            ->getQuery();
        $entity = $query->getResult();
        if (count($entity) > 0){
            return $entity[0]['cantidad']; 
        } else {
            return 0; 
        }               
    }

    /**
     * busca el maximo id equipo creado
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function getDisciplinaPruebaParticipacion($disciplinaId, $pruebaParticipacionId) {
        $em = $this->getDoctrine()->getManager();
        $entity= $this->getDoctrine()->getRepository('SieAppWebBundle:JdpDisciplinaPruebaParticipacion');
        $query = $entity->createQueryBuilder('dpp')
            ->select('dpp.cantidad as cantidad')
            ->where('dpp.disciplinaTipo = :codDisciplina')
            ->andwhere('dpp.pruebaParticipacionTipo = :codPruebaParticipacion')
            ->setParameter('codDisciplina', $disciplinaId)
            ->setParameter('codPruebaParticipacion', $pruebaParticipacionId)
            ->getQuery();
        $entity = $query->getResult();
        if (count($entity) > 0){
            return $entity[0]['cantidad']; 
        } else {
            return 0; 
        }               
    }

    
}
