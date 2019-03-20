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
    // Funcion que valida la inscripcion de un estudiante deportistas gestion, prueba y fase
    // PARAMETROS: estudianteInscripcionId, gestionId, pruebaId, faseId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function valEstudianteInscripcionJuegos($estudianteInscripcionId, $gestionId, $pruebaId, $faseId, $equipoId){            
        try{
            // verifica si ya cuenta con una inscripcion en la prueba, gestion y fase seleccionada            
            $estudianteInscripcionJuegosController = new estudianteInscripcionJuegosController();
            $estudianteInscripcionJuegosController->setContainer($this->container);
            $estudianteInscripcionJuegos = $estudianteInscripcionJuegosController->getEstudianteInscripcionGestionFasePrueba($estudianteInscripcionId, $pruebaId, $gestionId, $faseId);
            //dump($estudianteInscripcionId);
            if (count($estudianteInscripcionJuegos)>0){
                //dump('ya tiene la prueba seleccionada');die;
                return array('0' => false, '1' => 'Estudiante ya registrado en la prueba seleccionada');
            }
            
            $msg = $this->valDisciplinaParticipacion($estudianteInscripcionId, $gestionId, $pruebaId, $faseId);     
                                    
            if (!$msg[0]){
                //dump('no tiene disciplina');die;
                $em = $this->getDoctrine()->getManager();
                $pruebaEntity = $em->getRepository('SieAppWebBundle:JdpPruebaTipo')->findOneBy(array('id' => $pruebaId));
                $disciplinaId = $pruebaEntity->getDisciplinaTipo()->getId();
                $disciplinaEstudianteInscripcion = $estudianteInscripcionJuegosController->getDisciplinaEstudianteInscripcion($estudianteInscripcionId, $gestionId, $faseId);
                
                $msg1 = array('0' => false, '1' => 'Ya no puede registrarse en otras disciplinas');
                foreach ($disciplinaEstudianteInscripcion as $disciplina) {
                    $disciplinaInscripcionId = $disciplina->getId();
                    if ($disciplinaInscripcionId == $disciplinaId){
                        $msg1 = array('0' => true, '1' => '');
                    }
                }
                if(!$msg1[0]){
                    return $msg;
                }                
            }
            
            $msg = $this->valPruebaParticipacion($estudianteInscripcionId, $gestionId, $pruebaId, $faseId);   

            if (!$msg[0]){
                //dump('no tiene prueba');die;
                $msg1 = $this->valConjuntoPruebaParticipacion($estudianteInscripcionId, $gestionId, $pruebaId, $faseId);
                if (!$msg1[0]){
                    //dump($msg[1]);die;
                    return $msg1;
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
                
                $msg2 = $this->valPruebaRegla($estudianteInscripcionId, $gestionId, $pruebaId, $faseId, $equipoId);
                if (!$msg2[0]){
                    //dump($msg[1]);die;
                    return $msg2;
                }
                
                $msg = array('0' => true, '1' => '');
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
            $msg = array('0' => false, '1' => 'El estudiante ya cuenta con la cantidad maxima de disciplinas permitidas');
        }

        //dump($cantidadDisciplinaParticipacionEstudiante);dump($disciplinaParticipacionCantidad);die;

        if($cantidadDisciplinaParticipacionEstudiante >= $disciplinaParticipacionCantidad){
            $msg = array('0' => false, '1' => 'El estudiante ya cuenta con la cantidad maxima de participaciones permitidas en la Disciplina '.$disciplinaParticipacionNombre.', intente nuevamente');
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
            return array('0' => false, '1' => 'El estudiante ya cuenta con la cantidad maxima de participaciones permitidas en la Prueba '.$pruebaParticipacionNombre.', intente nuevamente');
            if ($cantidadPruebaEstudiante < $cantidadDisciplinaPruebaParticipacion){
                return array('0' => true, '1' => '');
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

            $conjuntoPruebaEntity = $em->getRepository('SieAppWebBundle:JdpPruebaTipo')->findOneBy(array('disciplinaTipo' => $disciplinaId, 'generoTipo' => $generoId));
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

            $estudianteInscripcionJuegosController = new estudianteInscripcionJuegosController();
            $estudianteInscripcionJuegosController->setContainer($this->container);
            $listaPruebaEstudiante = $estudianteInscripcionJuegosController->getEstudianteInscripcionGestionFaseDisciplinaGeneroModalidad($estudianteInscripcionId, $disciplinaId, $gestionId, $faseId, $generoId, $modalidadId);

            //dump(count($conjuntoPruebaEntity));dump(count($listaPruebaEstudiante));dump($modalidadPruebaEntity);dump($conjuntoPruebaEntity);dump($listaPruebaEstudiante);die;
            $cantidadPruebaConjunto = count($conjuntoPruebaEntity);
            $cantidadPruebaEstudiante = count($listaPruebaEstudiante);

            //dump($cantidadPruebaEstudiante);dump($cantidadPruebaConjunto);
            if($cantidadPruebaEstudiante < $cantidadPruebaConjunto){
                return array('0' => true, '1' => '');
            } else {
                return array('0' => false, '1' => 'El estudiante ya cuenta con la cantidad maxima de pruebas permitidas dentro de una disciplina');
            }
        }  else {
            return array('0' => true, '1' => '');
        }    

        
    }

        //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida la prueba en funcion a la regla según institucion, gestion, prueba y fase
    // PARAMETROS: estudianteInscripcionId, gestionId, pruebaId, faseId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function valPruebaRegla($estudianteInscripcionId, $gestionId, $pruebaId, $faseId, $equipoId){               
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
        $institucioneducativaCursoEntity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('id' => $estudianteInscripcionEntity->getInstitucioneducativaCurso()->getId()));
        $institucionId = $institucioneducativaCursoEntity->getInstitucioneducativa()->getId();
        $estudianteFechaNacimiento = $estudianteInscripcionEntity->getEstudiante()->getFechaNacimiento();
        $estudianteGestionNacimiento = date_format($estudianteFechaNacimiento,'Y');


        $estudianteInscripcionJuegosController = new estudianteInscripcionJuegosController();
        $estudianteInscripcionJuegosController->setContainer($this->container);
        if($pruebaParticipacionId == 1){   
            $listaEquipoEstudiantePruebaInstitucion = $estudianteInscripcionJuegosController->getEquipoEstudianteInscripcionInstitucionGestionFasePrueba($institucionId, $pruebaId, $gestionId, $faseId, $equipoId);
            $cantidadListaEquipoEstudiantePruebaInstitucion = count($listaEquipoEstudiantePruebaInstitucion);

            //dump($cantidadListaEquipoEstudiantePruebaInstitucion);dump($cupoInscripcion);die;
            if($cantidadListaEquipoEstudiantePruebaInstitucion >= $cupoInscripcion){
                return array('0' => false, '1' => 'No puede registrar a mas estudiantes en el equipo'.$equipoId.' ('.$pruebaNombre.')');
            }

            $listaEquipoPruebaInstitucion = $estudianteInscripcionJuegosController->getEquipoInscripcionInstitucionGestionFasePrueba($institucionId, $pruebaId, $gestionId, $faseId);
            $cantidadListaEquipoPruebaInstitucion = count($listaEquipoPruebaInstitucion);
            //dump($cantidadListaEquipoPruebaInstitucion);dump($cupoPresentacion);die;

            if($cantidadListaEquipoPruebaInstitucion > $cupoPresentacion){
                return array('0' => false, '1' => 'No puede registrar a mas equipos en la prueba '.$pruebaNombre);
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
                        return array('0' => false, '1' => 'No puede registrar a mas equipos en la prueba '.$pruebaNombre);
                    } 
                } 
            }          
        } else {
            $listaPruebaInstitucion = $estudianteInscripcionJuegosController->getEstudianteInscripcionInstitucionGestionFasePrueba($institucionId, $pruebaId, $gestionId, $faseId);
            $cantidadListaPruebaInstitucion = count($listaPruebaInstitucion);

            if($cantidadListaPruebaInstitucion >= $cupoInscripcion){
                return array('0' => false, '1' => 'No puede registrar a mas estudiantes en '.$pruebaNombre);
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
