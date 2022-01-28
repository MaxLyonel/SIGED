<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;

/**
 * EstudianteInscripcion controller.
 *
 */
class AdicionNotasController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request, $op) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            // generar los titulos para los diferentes sistemas

            $this->session = new Session();
            $tipoSistema = $request->getSession()->get('sysname');

            ////////////////////////////////////////////////////
            $rolUsuario = $this->session->get('roluser');

            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['codigoRude']));
                if(!$estudiante){
                    $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante con el código ingresado no existe!');
                    return $this->render('SieRegularBundle:AdicionNotas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                $inscripcion = $em->createQuery(
                    'SELECT ei FROM SieAppWebBundle:EstudianteInscripcion ei
                    LEFT JOIN ei.estudiante e
                    LEFT JOIN ei.institucioneducativaCurso iec
                    WHERE e.codigoRude = :rude
                    AND iec.institucioneducativa = :idInstitucion')
                    ->setParameter('rude',$form['codigoRude'])
                    ->setParameter('idInstitucion',$form['idInstitucion'])
                    ->getResult();
                if(!$inscripcion){
                    $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante no cuenta con inscripcion en la unidad educativa y la gestion seleccionada');
                    return $this->render('SieRegularBundle:AdicionNotas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
//                if($form['gestion'] >= 2015){
//                    $this->get('session')->getFlashBag()->add('noSearch', 'La gestión ingresada no es válida!');
//                    return $this->render('SieRegularBundle:AdicionNotas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
//                }
                $this->session->set('idEstudiante', $estudiante->getId());
                $this->session->set('idGestion',$form['gestion']);
                $this->session->set('idInstitucion',$form['idInstitucion']);
                $idEstudiante = $estudiante->getId();
                $gestion = $form['gestion'];
                $idInstitucion = $form['idInstitucion'];
            } else {
                /*
                 * Verificamos si se tiene que mostrar el formulario de busqueda
                 */
                if($op == 'search'){
                    return $this->render('SieRegularBundle:AdicionNotas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                /*
                 * Verificar si existe la session de la persona y gestion
                 */
                if($this->session->get('idEstudiante')){
                    $idEstudiante = $this->session->get('idEstudiante');
                    $gestion = $this->session->get('idGestion');
                    $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($idEstudiante);
                    $idInstitucion = $this->session->get('idInstitucion');
                }else{
                    return $this->render('SieRegularBundle:AdicionNotas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
            }

            /*
             * Listamos las asignaturas del estudiante
             */
            $query = $em->createQuery(
                    'SELECT ea FROM SieAppWebBundle:EstudianteAsignatura ea
                    JOIN ea.estudianteInscripcion ei
                    JOIN ea.institucioneducativaCursoOferta ieco
                    JOIN ei.estudiante e
                    JOIN ei.institucioneducativaCurso iec
                    WHERE e.id = :idEstudiante
                    AND iec.gestionTipo = :gestion
                    AND iec.institucioneducativa = :idInstitucion
                    AND ei.estadomatriculaTipo IN (:estadoMatricula)
                    ORDER BY ieco.asignaturaTipo')
                    ->setParameter('idEstudiante', $idEstudiante)
                    ->setParameter('estadoMatricula', array(4,5,10))
                    ->setParameter('gestion', $gestion)
                    ->setParameter('idInstitucion',$idInstitucion);

            $asignaturas = $query->getResult();
            // Verificamos si el estudiante tiene asignaturas asignadas
            if(!$asignaturas){
                $this->get('session')->getFlashBag()->add('noSearch', 'El estudiante no cuenta con áreas, primero debe registrar las áreas del estudiante.');
                return $this->render('SieRegularBundle:AdicionNotas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
            //VAlidamos que la gestion sea del 2014 para arriba
            if($gestion < 2014){
                $this->get('session')->getFlashBag()->add('noSearch', 'Solo se pueden modificar notas de la gestión 2014 o superior!');
                return $this->render('SieRegularBundle:AdicionNotas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }

            // Obtenemos el curso en el que esta inscrito el estudiante
            // Obtenemos la cantidad de notas y el tipo de nota (Bimestre, Trimestre, etc)
            // 
            $curso = $em->createQueryBuilder()     
                    ->select('iec.id,npt.notaPeriodoTipo, npt.periodomes')
                    ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                    ->leftJoin('SieAppWebBundle:Estudiante','e','WITH','ei.estudiante = e.id')
                    ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ei.institucioneducativaCurso = iec.id')
                    ->leftJoin('SieAppWebBundle:NotaPeriodoTipo','npt','WITH','iec.notaPeriodoTipo = npt.id')
                    ->where('e.id = :idEstudiante')
                    ->andWhere('iec.institucioneducativa = :idInstitucion')
                    ->andWhere('iec.gestionTipo = :gestion')
                    ->setParameter('idEstudiante',$idEstudiante)
                    ->setParameter('idInstitucion',$idInstitucion)
                    ->setParameter('gestion',$gestion)
                    ->orderBy('e.paterno,e.materno,e.nombre')
                    ->getQuery()
                    ->getResult();            

            // Obtenemos la cantidad de notas que tiene el curso y el tipo de nota
            $tipo_nota = $curso[0]['notaPeriodoTipo'];
            $cantidad_notas = $curso[0]['periodomes'];

            // Obtenemos el id de inscripcion del estudiante
            foreach ($asignaturas as $a){
                $idInscripcionEstudiante = $a->getEstudianteInscripcion()->getId();
            }
            
            /*
            // Obtenemos los datos de inscripcion y de institucioneducativa curso y eobtenermos el nivel
            */
            $inscripcionEst = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcionEstudiante);
            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcionEst->getInstitucioneducativaCurso());
            $idNivel = $institucionCurso->getNivelTipo()->getId();
            /*
            / Buscamos las notas por asignatura
            */
            $notasArray = array();
            $cont = 0;
            foreach ($asignaturas as $a){
                $cont_notas = 0;
                $notasArray[$cont] = array('asignatura'=>$a->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getAsignatura(),'codigo'=>$a->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId());
                for($i=1;$i<=$cantidad_notas;$i++){
                    $notas = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>$i));
                    if($notas){
                        if($idNivel == 11){
                            $notasArray[$cont]['notas'][] = array('notaTipo'=>$i,'idEstudianteAsignatura'=>$a->getId(),'idEstudianteNota'=>$notas->getId(),'bimestre'=>$i." ".$tipo_nota,'nota'=>$notas->getNotaCualitativa(),'id'=>$cont."-".$i);
                        }else{
                            $notasArray[$cont]['notas'][] = array('notaTipo'=>$i,'idEstudianteAsignatura'=>$a->getId(),'idEstudianteNota'=>$notas->getId(),'bimestre'=>$i." ".$tipo_nota,'nota'=>$notas->getNotaCuantitativa(),'id'=>$cont."-".$i);
                        }
                        $cont_notas++;
                    }else{
                        if($idNivel == 11){
                            $notasArray[$cont]['notas'][] = array('notaTipo'=>$i,'idEstudianteAsignatura'=>$a->getId(),'idEstudianteNota'=>'ninguno','bimestre'=>$i." ".$tipo_nota,'nota'=>'Ninguna','id'=>$cont."-".$i);
                        }else{
                            $notasArray[$cont]['notas'][] = array('notaTipo'=>$i,'idEstudianteAsignatura'=>$a->getId(),'idEstudianteNota'=>'ninguno','bimestre'=>$i." ".$tipo_nota,'nota'=>0,'id'=>$cont."-".$i);
                        }
                        $cont_notas++;
                    }
                }
                if($cont_notas == $cantidad_notas and $idNivel != 11){
                    $notaPromedio = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>5));
                    if($notaPromedio){
                        $notasArray[$cont]['notas'][] = array('notaTipo'=>$i,'idEstudianteAsignatura'=>$a->getId(),'idEstudianteNota'=>$notaPromedio->getId(),'bimestre'=>'Promedio','nota'=>$notaPromedio->getNotaCuantitativa(),'id'=>$cont."-5");
                    }else{
                        $notasArray[$cont]['notas'][] = array('notaTipo'=>$i,'idEstudianteAsignatura'=>$a->getId(),'idEstudianteNota'=>'ninguno','bimestre'=>'Promedio','nota'=>'0','id'=>$cont."-5");
                    }
                }
                $cont++;
            }

            /*
            * Creamos los titulos de las notas, para la mostrar la tabla de calificaciones
            */
            $titulos_notas = array();
            $titulos = $notasArray[0]['notas'];
            for($i=0;$i<count($titulos);$i++){
                $titulos_notas[] = array('titulo'=>$titulos[$i]['bimestre']);
            }
            /* 
             * Creamos la variable de session del nivel
             */ 
            $this->session->set('idNivel',$idNivel);
            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:AdicionNotas:index.html.twig', array(
                        'asignaturas' => $notasArray, 'gestion' => $gestion, 'estudiante'=>$estudiante,
                        'titulos_notas' =>$titulos_notas, 'curso'=>$institucionCurso
            ));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }
    
    /*
     * Formulario de busqueda de maestro
     */
    public function formSearch($gestion){
        try{
            $gestiones = array($gestion=>$gestion,$gestion-1=>$gestion-1);
            return $this->createFormBuilder()
                    ->setAction($this->generateUrl('adicionNotas'))
                    ->add('codigoRude','text',array('label'=>'Código Rude','attr'=>array('class'=>'form-control jnumbersletters','placeholder'=>'Código Rude','pattern'=>'[0-9A-Z]{11,20}','autocomplete'=>'off','maxlength'=>'20')))
                    ->add('idInstitucion','text',array('label'=>'Sie','attr'=>array('class'=>'form-control jnumbers','placeholder'=>'Sie Unidad Educativa' ,'pattern'=>'[0-9]{6,8}','autocomplete'=>'off','maxlength'=>'8')))
                    ->add('gestion','choice',array('label'=>'Gestión','choices'=>$gestiones,'attr'=>array('class'=>'form-control')))
                    ->add('buscar','submit',array('attr'=>array('class'=>'btn btn-primary')))
                    ->getForm();
        }catch(Exception $ex){

        }
    }

    public function createAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $this->session = new Session();
            
            /*
             * Obtenemos los valores enviados desde el formulario
             */
            $notaTipo = $request->get('notaTipo');
            $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
            $idEstudianteNota = $request->get('idEstudianteNota');
            $nota = $request->get('nota');
            $nivel = $this->session->get('idNivel');
            
            // Obtenemos los checks marcados de las notas a adicionar
            $notasAdicionar = $request->get('notasAdicionar');
            if(count($notasAdicionar)==0){
                $notasAdicionar = array();
            }

            /*
             * Recorremos el array de notas para verificar si los datos son numeros
             * y si comprenden entre los 1 a 100 SI EL NIVEL ES PRIMARIA O SECUNDARIA
             */
            if($nivel != 11){
                for($i=0;$i<count($nota);$i++){
                    if(!is_numeric($nota[$i]) or $nota[$i]<0 or $nota[$i]>100){
                      $this->get('session')->getFlashBag()->add('newError', 'Las calificaciones ingresadas no son válidas, intentelo nuevamente.');
                      return $this->redirect($this->generateUrl('adicionNotas',array('op'=>'result')));
                    }
                }
            }
            // Reiniciamos la secuencia del id tabla
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();

            // Creamos la variable para ver si hay notas que registrar
            $notas_registrar = 0;
            for($i=0;$i<count($idEstudianteNota);$i++){
                if($idEstudianteNota[$i] == "ninguno"){
                    // Verificamos si el tipo de nota esta marcado para ser adicionado
                    if(in_array($notaTipo[$i],$notasAdicionar)){
                        /**
                         * Registramos la nueva nota en cero 
                         */
                        $newNota = new EstudianteNota();
                        if($nivel != 11){
                            $newNota->setNotaCuantitativa(0);
                            $newNota->setNotaCualitativa('');
                        }else{
                            $newNota->setNotaCuantitativa(0);
                            $newNota->setNotaCualitativa('Ninguna');
                        }
                        $newNota->setFechaRegistro(new \DateTime('now'));
                        $newNota->setFechaModificacion(new \DateTime('now'));
                        $newNota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($notaTipo[$i]));
                        $newNota->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                        $newNota->setUsuarioId($this->session->get('userId'));
                        $em->persist($newNota);
                        $em->flush();
                        // Aumentamos la variable que controla el registro de calificaciones
                        
                        $notas_registrar++;
                    }
                }
            }
            
            $em->getConnection()->commit();
            
            if($notas_registrar == 0){
                $this->get('session')->getFlashBag()->add('newError', 'No hay notas que registrar');
                return $this->redirect($this->generateUrl('adicionNotas',array('op'=>'result')));
            }
            /*
             * Creamos el mensaje del proceso y redireccionamos a la pagina principal de notas
             */
            $this->get('session')->getFlashBag()->add('newOk', 'Las calificaciones se registraron correctamente, ahora puede modificar las calificaciones mediante el módulo de ');
            return $this->redirect($this->generateUrl('adicionNotas',array('op'=>'result')));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
            echo "Error: ".$ex->getMessage();
        }
        
    }
}
