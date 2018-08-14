<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;


use Doctrine\DBAL\Types\Type;
Type::overrideType('datetime', 'Doctrine\DBAL\Types\VarDateTimeType');

/**
 * OlimRobotica controller.
 *
 */
class OlimRoboticaController extends Controller{

    public function indexaction(Request $request){
        $form = $this->createFormulario();
        $form->handleRequest($request);

        if($form->isSubmitted()){

            $em = $this->getDoctrine()->getManager();
            $formulario = $request->get('form');

            $estudiante = $em->createQueryBuilder()
                ->select('e')
                ->from('SieAppWebBundle:Estudiante','e')
                ->where('e.carnetIdentidad = :carnet')
                ->orWhere('e.codigoRude = :rude')
                ->andWhere('e.complemento = :complemento')
                ->andWhere('e.fechaNacimiento = :fechaNacimiento')
                ->setParameter('carnet', $formulario['carnetEstudiante'])
                ->setParameter('rude', $formulario['carnetEstudiante'])
                ->setParameter('complemento', $formulario['complementoEstudiante'])
                ->setParameter('fechaNacimiento', new \DateTime($formulario['fechaNacimientoEstudiante']))
                ->getQuery()
                ->getResult();

                if(count($estudiante) > 1){
                    return $this->render('SieOlimpiadasBundle:OlimRobotica:index.html.twig', array(
                        'form'=>$form->createView(),
                        'estudiante'=>null,
                        'tutor'=>null,
                        'array'=>null,
                        'mensaje'=>'La/el estudiante cuenta con más de un código RUDE, intente la búsqueda ingresando el código RUDE correcto en lugar del número de Carnet de Identidad.'
                    ));
                } else {
                    $estudiante = $estudiante[0];
                }
                

            $tutor = $em->createQueryBuilder()
                ->select('p')
                ->from('SieAppWebBundle:Persona','p')
                ->where('p.carnet = :carnet')
                ->andWhere('p.complemento = :complemento')
                ->setParameter('carnet', $formulario['carnetTutor'])
                ->setParameter('complemento', $formulario['complementoTutor'])
                ->getQuery()
                ->getOneOrNullResult();

            $array = null;
            $inscripcionActual = null;

            if($estudiante && $tutor){
                // OBTENEMOS LA INSCRIPCION DEL ESTUDIANTE EN EL SIGED
                $inscripcionActual = $em->createQueryBuilder()
                    ->select('ei.id')
                    ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                    ->where('ei.estudiante = :idEstudiante')
                    ->andWhere('iec.gestionTipo = :gestion')
                    ->andWhere('ei.estadomatriculaTipo IN (:estados)')
                    ->setParameter('idEstudiante', $estudiante->getId())
                    ->setParameter('gestion', date('Y'))
                    ->setParameter('estados', array(4,5,11,55))
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getResult();

                if($inscripcionActual){
                    // OBTENEMOS EL GRUPO DE INSCRIPCIÓN
                    $grupoProyecto = $em->createQueryBuilder()
                    ->select('ogp.id')
                    ->from('SieAppWebBundle:OlimTutor','ot')
                    ->innerJoin('SieAppWebBundle:OlimGrupoProyecto','ogp','with','ogp.olimTutor = ot.id')
                    ->innerJoin('SieAppWebBundle:OlimInscripcionGrupoProyecto','oigp','with','oigp.olimGrupoProyecto = ogp.id')
                    ->innerJoin('SieAppWebBundle:OlimEstudianteInscripcion','oei','with','oigp.olimEstudianteInscripcion = oei.id')
                    ->where('ot.persona = :idPersona')
                    ->andWhere('ot.gestionTipoId = :gestion')
                    ->andWhere('oei.estudianteInscripcion = :inscripcion')
                    ->setParameter('idPersona', $tutor->getId())
                    ->setParameter('gestion', date('Y'))
                    ->setParameter('inscripcion', $inscripcionActual[0])
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getResult();

                    if($grupoProyecto){
                        // OBTENEMOS LAS INSCRIPCIONES EN OLIMPIADAS
                        $inscripcionesOlim = $em->createQueryBuilder()
                            ->select('oei')
                            ->from('SieAppWebBundle:OlimEstudianteInscripcion','oei')
                            ->leftJoin('SieAppWebBundle:OlimInscripcionGrupoProyecto','oigp','with','oigp.olimEstudianteInscripcion = oei.id')
                            ->leftJoin('SieAppWebBundle:OlimGrupoProyecto','ogp','with','oigp.olimGrupoProyecto = ogp.id')
                            ->where('ogp.id = :grupoProyecto')
                            ->setParameter('grupoProyecto', $grupoProyecto[0]['id'])
                            ->getQuery()
                            ->getResult();
                    } else {
                        return $this->render('SieOlimpiadasBundle:OlimRobotica:index.html.twig', array(
                            'form'=>$form->createView(),
                            'estudiante'=>null,
                            'tutor'=>null,
                            'array'=>null,
                            'mensaje'=>'Los datos ingresados son incorrectos, verifique la información e intente nuevamente.'
                        ));
                    }

                    $cont = 0;
                    foreach ($inscripcionesOlim as $io) {
                        $array[$cont]['inscripcion'] = $io;
                        $array[$cont]['regla'] = $io->getOlimReglasOlimpiadasTipo();
                        // ESTUDIANTES
                        $estudianteOlim = $em->createQueryBuilder()
                            ->select('e')
                            ->from('SieAppWebBundle:Estudiante','e')
                            ->leftJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','ei.estudiante = e.id')
                            ->leftJoin('SieAppWebBundle:OlimEstudianteInscripcion','oei','with','oei.estudianteInscripcion = ei.id')
                            ->where('oei.id = :oeinscrip')
                            ->setParameter('oeinscrip', $io)
                            ->getQuery()
                            ->getResult();
                        $array[$cont]['estudiante'] = $estudianteOlim[0];
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

                        //----------------------------------------------------------------------
                        // VERIFICAR SI SE DEBE MOSTRAR LA SELECCION DE MODALIDAD DE EVALUACION
                        //----------------------------------------------------------------------

                        $olimEstudianteNota = $em->createQueryBuilder()
                                            ->select('oenp')
                                            ->from('SieAppWebBundle:OlimEstudianteNotaPrueba','oenp')
                                            ->innerJoin('SieAppWebBundle:OlimEtapaTipo','oet','with','oenp.olimEtapaTipo = oet.id')
                                            ->where('oenp.olimEstudianteInscripcion = :idInscripcion')
                                            ->setParameter('idInscripcion', $io->getId())
                                            ->orderBy('oet.id','DESC')
                                            ->setMaxResults(1)
                                            ->getQuery()
                                            ->getResult();

                        $mostrarSeleccion = false;
                        $registroNota = null;
                        $modalidades = null;

                        if(count($olimEstudianteNota)>0){

                            $siguienteEtapa = $olimEstudianteNota[0]->getOlimEtapaTipo()->getId() + 1;
                            
                            // FECHAS DE LA ETAPA
                            $fechas = $em->getRepository('SieAppWebBundle:OlimEtapaPeriodo')->findOneBy([
                                'olimRegistroOlimpiada'=>$io->getOlimReglasOlimpiadasTipo()->getOlimMateriaTipo()->getOlimRegistroOlimpiada()->getId(),
                                'olimEtapaTipo'=>$siguienteEtapa
                            ]);

                            // OBTENEMOS LA FECHA ACTUAL
                            $fechaActual = new \DateTime(date('d-m-Y'));

                            if($fechas){

                                if($fechaActual >= $fechas->getFechaInicio() and $fechaActual <= $fechas->getFechaFin()){

                                    $mostrarSeleccion = true;
                                    $registroNota = $olimEstudianteNota[0];
                                    $modalidades = $em->getRepository('SieAppWebBundle:OlimModalidadPruebaTipo')->findBy(
                                        [
                                        'olimRegistroOlimpiada'=> $io->getOlimReglasOlimpiadasTipo()->getOlimMateriaTipo()->getOlimRegistroOlimpiada()->getId()
                                        ],['olimModalidadTipo'=>'ASC']
                                    );
                                }

                            }

                        }

                        $array[$cont]['mostrar'] = $mostrarSeleccion;
                        $array[$cont]['registroNota'] = $registroNota;
                        $array[$cont]['modalidades'] = $modalidades;


                        $cont++;
                    }
                } else {
                    return $this->render('SieOlimpiadasBundle:OlimRobotica:index.html.twig', array(
                        'form'=>$form->createView(),
                        'estudiante'=>null,
                        'tutor'=>null,
                        'array'=>null,
                        'mensaje'=>'Los datos ingresados son incorrectos, verifique la información e intente nuevamente.'
                    ));
                }

                $reglasTipo = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->findBy(array('olimMateriaTipo' => 8));

                $categorias = array();
                foreach ($reglasTipo as $key => $value) {
                    $categorias[$value->getId()] = $value->getCategoria();
                }

                $formCategoria = $this->createFormularioCategoria($categorias,$grupoProyecto,$array[0]['regla']->getId());

                return $this->render('SieOlimpiadasBundle:OlimRobotica:index.html.twig', array(
                    'form'=>$form->createView(),
                    'estudiante'=>$estudiante,
                    'tutor'=>$tutor,
                    'array'=>$array,
                    'formCategoria'=>$formCategoria->createView(),
                    'mensaje'=>'La búsqueda se ha completado con éxito.'
                ));
            }

            return $this->render('SieOlimpiadasBundle:OlimRobotica:index.html.twig', array(
                'form'=>$form->createView(),
                'estudiante'=>$estudiante,
                'tutor'=>$tutor,
                'array'=>$array,
                'mensaje'=>'Los datos ingresados son incorrectos, verifique la información e intente nuevamente.'
            ));
            
        }

        return $this->render('SieOlimpiadasBundle:OlimRobotica:index.html.twig', array('form'=>$form->createView()));
    }

    private function createFormulario(){
        $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('olimrobotica_index'))
                    ->add('carnetEstudiante', 'text', array('required' => true))
                    ->add('complementoEstudiante', 'text', array('required' => false))
                    ->add('fechaNacimientoEstudiante', 'text', array('required' => true))
                    ->add('carnetTutor', 'text', array('required' => true))
                    ->add('complementoTutor', 'text', array('required' => false))
                    ->add('fechaNacimientoTutor', 'text', array('required' => false))
                    ->getForm();

        return $form;
    }

    private function createFormularioCategoria($categorias,$grupoProyecto,$categoriaActual){
        $form = $this->createFormBuilder()
                    ->add('grupoProyectoId', 'hidden', array('data' => $grupoProyecto[0]['id']))
                    ->add('reglasTipo', 'choice', array('label' => 'Modificar categoría:', 'required' => true, 'choices' => $categorias, 'data' => $categoriaActual))
                    ->getForm();

        return $form;
    }

    public function actualizarCategoriaAction($grupoProyectoId, $categoriaId){
        $em = $this->getDoctrine()->getManager();
        $grupoProyecto = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($grupoProyectoId);

        $reglaTipo = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->find($categoriaId);
        $fechaComparacion = $reglaTipo->getFechaComparacion()->format('d-m-Y');
        $edadInicial = $reglaTipo->getEdadInicial();
        $edadFinal = $reglaTipo->getEdadFinal();

        $grupoProyectoInscr = $em->getRepository('SieAppWebBundle:OlimInscripcionGrupoProyecto')->findBy(array('olimGrupoProyecto' => $grupoProyecto));

        if($grupoProyectoInscr){
            $cont = 1;
            $estudianteValido = array();
            foreach ($grupoProyectoInscr as $key => $value) {
                $estudianteInscr = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($value->getOlimEstudianteInscripcion());

                $fechaNacimientoEstudiante = $estudianteInscr->getEstudianteInscripcion()->getEstudiante()->getFechaNacimiento();

                $edadEstudiante = $this->get('olimfunctions')->getYearsOldsStudent($fechaNacimientoEstudiante->format('d-m-Y'), $fechaComparacion);

                $aniosEstudiante = $edadEstudiante[0];

                if($aniosEstudiante >= $edadInicial && $aniosEstudiante <= $edadFinal){
                    $estudianteValido[$cont]=$value;
                }
                $cont++;
            }
            
            $a = intval(count($grupoProyectoInscr));
            $b = intval(count($estudianteValido));
            
            if($a == $b){
                foreach ($grupoProyectoInscr as $key => $value) {
                    $estudianteInscr = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($value->getOlimEstudianteInscripcion());
                    $estudianteInscr->setOlimReglasOlimpiadasTipo($reglaTipo);
                    $em->persist($estudianteInscr);
                    $em->flush();
                    $grupoProyecto->setFechaConfirmacion(new \DateTime(date('d-m-Y H:i:s')));
                    $em->persist($grupoProyecto);
                    $em->flush();
                    $status = 200;
                    $mensaje = "Confirmación realizada exitosamente.";
                }
            } else {
                $status = 500;
                $mensaje = "No cumple con las reglas establecidas para la categoría.";
            }
        } else {
            $status = 500;
            $mensaje = "Ocurrió un error interno, intente nuevamente.";
        }

        $response = new JsonResponse();

        return $response->setData([
            'status'=> $status,
            'mensaje' => $mensaje
        ]);
    }
}