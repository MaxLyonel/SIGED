<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEspecial;
use Sie\AppWebBundle\Entity\SocioeconomicoEspecial;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoEspecial;
use Sie\AppWebBundle\DBAL\Types\ModalidadAtencionTipo;
use Sie\AppWebBundle\Entity\EspecialAreaTipo;
use Sie\AppWebBundle\DBAL\Types\DiscapacidadEspecialTipo;

use Sie\AppWebBundle\DBAL\Types\GradoParentescoTipo;
use Sie\AppWebBundle\DBAL\Types\GradoTalentoTipo;
use Sie\AppWebBundle\DBAL\Types\OrigenDiscapacidadTipo;
use Sie\AppWebBundle\DBAL\Types\TalentoTipo;
use Sie\AppWebBundle\DBAL\Types\ViveConTipo;
use Sie\AppWebBundle\DBAL\Types\DeteccionTipo;
use Sie\AppWebBundle\DBAL\Types\DificultadAprendizajeTipo;

/**
 * EstudianteInscripcion controller.
 *
 */
class EstudianteInscripcionEspecialController extends Controller {

    public $session;
    public $idInstitucion;
    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request, $op) {
        try {
            // generar los titulos para los diferentes sistemas
            $this->session = new Session();
            $tipoSistema = $request->getSession()->get('sysname');
            switch ($tipoSistema) {
                case 'REGULAR': $this->session->set('tituloTipo', 'Estudiantes Inscritos');
                    break;
                case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Personal Administrativo');
                    break;
                default: $this->session->set('tituloTipo', 'Estudiantes Inscritos');
                    break;
            }

            ////////////////////////////////////////////////////
            $em = $this->getDoctrine()->getManager();

            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                /*
                 * verificamos si existe la unidad educativa
                 */
                $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);

                if (!$institucioneducativa) {
                    $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                    return $this->render('SieEspecialBundle:EstudianteInscripcionEspecial:search.html.twig', array('iesec' => null, 'form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }

                /*
                * verificamos si tiene tuicion
                */
//                $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
//                $query->bindValue(':user_id', $this->session->get('userId'));
//                $query->bindValue(':sie', $form['institucioneducativa']);
//                $query->bindValue(':rolId', $this->session->get('roluser'));
//                $query->execute();
//                $aTuicion = $query->fetchAll();
//
//                if ($aTuicion[0]['get_ue_tuicion']){
                    $institucion = $form['institucioneducativa'];
                    $gestion = $form['gestion'];
                    $area = $form['area'];

                    $ies = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'sucursalTipo' => 0));

                        $iesec = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalEspecialCierre')->findOneBy(array('institucioneducativaSucursal' => $ies, 'esactivo' => 't'));
//                }else{
//                    $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
//                    return $this->render('SieAppWebBundle:EstudianteInscripcion:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
//                }

            } else {
                        $funcion = new FuncionesController();
                        $institucion = $funcion->ObtenerUnidadEducativaAction($request->getSession()->get('userId'), $request->getSession()->get('currentyear')); //5484231);
                        $gestion = $request->getSession()->get('currentyear');
                        $area = $request->getSession()->get('idArea');
                        $request->getSession()->set('idInstitucion', $institucion);
                        $request->getSession()->set('idGestion', $gestion);
                        $request->getSession()->set('idArea', $area);

                        $ies = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'sucursalTipo' => 0));

                        $iesec = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalEspecialCierre')->findOneBy(array('institucioneducativaSucursal' => $ies, 'esactivo' => 't'));

                        return $this->render('SieEspecialBundle:EstudianteInscripcionEspecial:search.html.twig', array(
                                'form' => $this->formSearch($request->getSession()->get('currentyear'))->createView(),
                                'iesec' => $iesec
                        ));
                
            }

            if($iesec){
                return $this->redirect($this->generateUrl('estudianteinscripcion_especial'));
            }

            //return $this->redirect($this->generateUrl('estudianteinscripcion_especial'));
            //$institucion = '40730421';
            //$gestion = '2014';

            // creamos variables de sesion de la institucion educativa y gestion
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);
            $request->getSession()->set('idArea', $area);

            // Lista de cursos institucioneducativa
            $query = $em->createQuery(
                    'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
            		JOIN iec.institucioneducativaCurso ie
                    WHERE ie.institucioneducativa = :idInstitucion
                    AND ie.gestionTipo = :gestion
            		AND iec.especialAreaTipo = :area
                    AND ie.nivelTipo IN (:niveles)
                    ORDER BY ie.turnoTipo, ie.nivelTipo, iec.especialAreaTipo, ie.gradoTipo,iec.especialServicioTipo,iec.especialProgramaTipo, ie.paraleloTipo')
                    ->setParameter('idInstitucion', $institucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('area', $area)
                    ->setParameter('niveles',array(401,402,403,404,405,406,407,408,409,410,411,999));

            $cursos = $query->getResult();

            /*
             * Guardamos la consulta en un array ordenado
             */
            $cursosArray = array();
            foreach ($cursos as $cur) {
                $cursosArray[] = array(
                    'turno' => $cur->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno(),
                    'nivel' => $cur->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(),
                    'grado' => $cur->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(),
                	'area' => $cur->getEspecialAreaTipo()->getAreaEspecial(),
                    'paralelo' => $cur->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(),
                    'idInstitucion'=>$cur->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId(),
                    'idGestion'=>$cur->getInstitucioneducativaCurso()->getGestionTipo()->getId(),
                    'idInstitucionCurso'=>$cur->getId(),
                    'idNivel' => $cur->getInstitucioneducativaCurso()->getNivelTipo()->getId(),
                    'idTurno' => $cur->getInstitucioneducativaCurso()->getTurnoTipo()->getId(),
                	'idArea' => $cur->getEspecialAreaTipo()->getId(),
                	'servicio' => $cur->getEspecialServicioTipo()->getServicio(),
                	'programa' => $cur->getEspecialProgramaTipo()->getPrograma(),
                	'idServicio' => $cur->getEspecialServicioTipo()->getId(),
                	'idPrograma' => $cur->getEspecialProgramaTipo()->getId(),
                	'idGrado' => $cur->getInstitucioneducativaCurso()->getGradoTipo()->getId(),
                	'tecnica' => $cur->getEspecialNivelTecnicoTipo()->getNivelTecnico() . "-" .  $cur->getEspecialTecnicaEspecialidadTipo()->getEspecialidad(),
                );
            }

//             dump($cursosArray);die;
            for ($i = 0; $i < count($cursosArray); $i++) {
                //$inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso'=>$cursosArray[$i]['idInstitucionCurso']));
                $query = $em->createQuery(
                        'SELECT ei FROM SieAppWebBundle:EstudianteInscripcionEspecial ei
                		JOIN ei.estudianteInscripcion i
                        JOIN i.estudiante e
                        WHERE ei.institucioneducativaCursoEspecial = :curso
                        ORDER BY e.paterno, e.materno, e.nombre')
                        ->setParameter('curso',$cursosArray[$i]['idInstitucionCurso']);

                $inscritos = $query->getResult();
                if(count($inscritos)>0){
                    for($j=0;$j<count($inscritos);$j++){
                        $cursosArray[$i]['inscritos'][] = array(
                            'rude'=>$inscritos[$j]->getEstudianteInscripcion()->getEstudiante()->getCodigoRude(),
                            'ci'=>$inscritos[$j]->getEstudianteInscripcion()->getEstudiante()->getCarnetIdentidad(),
                            'paterno'=>$inscritos[$j]->getEstudianteInscripcion()->getEstudiante()->getPaterno(),
                            'materno'=>$inscritos[$j]->getEstudianteInscripcion()->getEstudiante()->getMaterno(),
                            'nombre'=>$inscritos[$j]->getEstudianteInscripcion()->getEstudiante()->getNombre(),
                            'fecha'=>$inscritos[$j]->getEstudianteInscripcion()->getEstudiante()->getFechaNacimiento()->format('d-m-Y'),
                            'idInscripcion'=>$inscritos[$j]->getId()
                            );
                    }
                }else{
                    $cursosArray[$i]['inscritos'] = null;//array('campo'=>'','codigo'=>'','asignatura'=>'adfdasf','maestro'=>'dafdsaf');
                }
            }

            $est = array();
            $est = $cursosArray;
            $niveles = array();
            if(count($est)>0){
                $tn = $est[0]['turno'] . $est[0]['nivel'];
                $n = $est[0]['nivel'];
                $c = 0;
                $niveles[0] = array($n,array('0'=>$est[0]));

                for ($j = 1; $j < count($est); $j++) {
                    if ($est[$j]['turno'] . $est[$j]['nivel'] == $tn) {
                        $niveles[$c][1][] = $est[$j];
                    }else{
                        $c++;
                        $tn = $est[$j]['turno'] . $est[$j]['nivel'];
                        $n =  $est[$j]['nivel'];
                        $niveles[$c] = array($n,array('0'=>$est[$j]));
                    }
                }
            }

            $est = $niveles;

            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
            $area = $em->getRepository('SieAppWebBundle:EspecialAreaTipo')->findOneById($area);

            return $this->render('SieEspecialBundle:EstudianteInscripcionEspecial:index.html.twig', array(
                        'inscritos' => $est, 'institucion' => $institucion, 'gestion' => $gestion , 'area' => $area
            ));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SieEspecialBundle:EstudianteInscripcionEspecial:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }
    }

    private function formSearch($gestionactual) {
    	$em = $this->getDoctrine()->getManager();
    	$query = $em->createQuery(
    			'SELECT a FROM SieAppWebBundle:EspecialAreaTipo a
                                    WHERE a.id IN (:id)'
    			)->setParameter('id',array(1,2,3,4,5,6,7,8,9,100));
    			$areas_result = $query->getResult();
    			$areas = array();
    			foreach ($areas_result as $a){
    				$areas[$a->getId()] = $a->getAreaEspecial();
    			}

    			$gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1, $gestionactual - 2 => $gestionactual - 2, $gestionactual - 3 => $gestionactual - 3);
    			$form = $this->createFormBuilder()
    			->setAction($this->generateUrl('estudianteinscripcion_especial'))
    			->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 9)))
    			->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
    			->add('area','choice',array('label'=>'Area de Especial','required' => true, 'choices' => $areas,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
    			->add('buscar', 'submit', array('label' => 'Buscar'))
    			->getForm();
    			return $form;
    }


    /**
     * Displays a form to create a new EstudianteInscripcion entity.
     *
     */
    public function newAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        // Datos de la institucion educativa
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->getSession()->get('idInstitucion'));
		// Datos del curso
        $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneById($request->get('idInstitucionCurso'));

        $area = $em->getRepository('SieAppWebBundle:EspecialAreaTipo')->findOneById($request->getSession()->get('idArea'));
        //Sucurales
        $idIns = $institucion->getId();
        $idGestion = $request->getSession()->get('idGestion');
        $query = $em->createQuery(
                        'SELECT suc, st FROM SieAppWebBundle:InstitucioneducativaSucursal suc
                    JOIN suc.sucursalTipo st
                    WHERE suc.institucioneducativa = :idInstitucion
                    AND suc.gestionTipo = :gestion')
                ->setParameter('idInstitucion', $idIns)
                ->setParameter('gestion', $idGestion);

        $sucursales = $query->getResult();
        $sucursal = array();
        foreach ($sucursales as $s) {
            $sucursal[$s->getSucursalTipo()->getId()] = $s->getSucursalTipo()->getId();
        }
        // Sie el array esta vacio o no tiene sucursales llenamos uno por defecto
        if (empty($sucursal)) {
            $sucursal[0] = 0;
        }
        // Creacion de formularios para la inscripcion con rude y sin rude
        if ($request->getSession()->get('idArea') == 6 or $request->getSession()->get('idArea') == 7) {
        	$formConRude = $this->newFormConRude($institucion, $sucursal, $idGestion, $area, $institucionCurso);
        	return $this->render('SieEspecialBundle:EstudianteInscripcionEspecial:new.html.twig', array('formConRude' => $formConRude->createView()));

        }
        else {
        	$formConRude = $this->newFormConRude($institucion, $sucursal, $idGestion, $area, $institucionCurso);
        	$formSinRude = $this->newFormSinRude($institucion, $sucursal, $idGestion, $area, $institucionCurso);


        	return $this->render('SieEspecialBundle:EstudianteInscripcionEspecial:new.html.twig', array('formConRude' => $formConRude->createView(), 'formSinRude' => $formSinRude->createView()));

        }
    }

    private function newFormConRude($institucion, $sucursal, $gestion, $area, $institucionCurso) {

        $em = $this->getDoctrine()->getManager();
//         $query = $em->createQuery(
//                 'SELECT DISTINCT tt.id,tt.turno
//                 FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
//                 JOIN iec.turnoTipo tt
//                 WHERE iec.institucioneducativa = :id
//         		AND iec.areaEspecialTipo = :area
//                 AND iec.gestionTipo = :gestion
//                 ORDER BY tt.id')
//                 ->setParameter('id',$institucion->getId())
//                 ->setParameter('area',$area->getId())
//                 ->setParameter('gestion',$gestion);
//         $turnos = $query->getResult();
//         $turnosArray = array();
//         for($i=0;$i<count($turnos);$i++){
//             $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
//         }

        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' => 1));
        $depArray = array();
        foreach ($dep as $de) {
        	$depArray[$de->getId()] = $de->getLugar();
        }

        if ($area->getId() == 1) {
        	$discapacidadArray = [
        			DiscapacidadEspecialTipo::AUDITIVA => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::AUDITIVA)
        	];
        }
        elseif ($area->getId() == 2) {
        	$discapacidadArray = [
        			DiscapacidadEspecialTipo::VISUAL => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::VISUAL)
        	];
        }
        elseif ($area->getId() == 3) {
        	$discapacidadArray = [
        			DiscapacidadEspecialTipo::INTELECTUAL => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::INTELECTUAL),
        			DiscapacidadEspecialTipo::SINDROME_DE_DOWN => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::SINDROME_DE_DOWN),
        			DiscapacidadEspecialTipo::AUTISMO => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::AUTISMO)
        	];
        }
        elseif ($area->getId() == 4) {
        	$discapacidadArray = [
        			DiscapacidadEspecialTipo::FISICO_MOTORA => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::FISICO_MOTORA)
        	];
        }
        elseif ($area->getId() == 5) {
        	$discapacidadArray = [
        			DiscapacidadEspecialTipo::MULTIPLE => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::MULTIPLE)
        	];
        }
        elseif ($area->getId() == 8) {
        	$discapacidadArray = [
        			DiscapacidadEspecialTipo::SORDOCEGUERA => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::SORDOCEGUERA)
        	];
        }
        else {
        	$discapacidadArray = [
        			DiscapacidadEspecialTipo::NINGUNO => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::NINGUNO)
        	];
        }

$form = $this->createFormBuilder()
                        ->setAction($this->generateUrl('estudianteinscripcion_especial_create'))
                        ->add('tipo_inscripcion', 'hidden', array('data' => 'conRude'))
                        ->add('c_idInstitucion', 'hidden', array('data' => $institucion->getId()))
                        ->add('c_idArea','hidden',array('data'=> $area->getId()))
                        ->add('c_gestion', 'hidden', array('data' => $gestion))
                        ->add('c_idInstitucionCurso', 'hidden', array('data' => $institucionCurso->getId()))
                        ->add('c_codigoIns', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'data' => $institucion->getId(), 'attr' => array('class' => 'form-control')))
                        ->add('c_nombreIns', 'text', array('label' => 'Nombre Institución Educativa', 'disabled' => true, 'data' => $institucion->getInstitucioneducativa(), 'attr' => array('class' => 'form-control')))
                        ->add('c_rude', 'text', array('label' => 'Código RUDE', 'required' => true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('c_paterno', 'text', array('label' => 'Paterno', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_materno', 'text', array('label' => 'Materno', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_nombre', 'text', array('label' => 'Nombre(s)', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'disabled' => true, 'attr' => array('class' => 'form-control')))

                        ->add('c_carnetIdentidad', 'text', array('label' => 'Carnet de identidad', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_oficialia', 'text', array('label' => 'Oficialia', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_libro', 'text', array('label' => 'Libro', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_partida', 'text', array('label' => 'Partida', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_folio', 'text', array('label' => 'Folio', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_pais', 'text', array('label' => 'Pais', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_departamento', 'text', array('label' => 'Departamento', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_provincia', 'text', array('label' => 'Provincia', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_localidad', 'text', array('label' => 'Localidad', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_carnetCodepedis', 'text', array('label' => 'Carnet Codepedis', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_carnetIbc', 'text', array('label' => 'Carnet I.B.C.', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_generoTipo', 'text', array('label' => 'Género', 'disabled' => true, 'attr' => array('class' => 'form-control')))


                        ->add('c_area', 'text', array('label' => 'Area de Especial','data'=>$area->getAreaEspecial(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_turno', 'text', array('label' => 'Turno','data'=>$institucionCurso->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_nivel', 'text', array('label' => 'Nivel','data'=>$institucionCurso->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_grado', 'text', array('label' => 'Grado','data'=>$institucionCurso->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_paralelo', 'text', array('label' => 'Paralelo','data'=>$institucionCurso->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_servicio', 'text', array('label' => 'Servicio','data'=>$institucionCurso->getEspecialServicioTipo()->getServicio(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_programa', 'text', array('label' => 'Programa','data'=>$institucionCurso->getEspecialProgramaTipo()->getPrograma(), 'disabled' => true, 'attr' => array('class' => 'form-control')))

                        ->add('c_periodo', 'entity', array('label' => 'Periodo', 'class' => 'SieAppWebBundle:PeriodoTipo','data'=>$em->getReference('SieAppWebBundle:PeriodoTipo',1) , 'property' => 'periodo','required'=>true,'disabled'=>true , 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('c_fechaInscripcion', 'text', array('label' => 'Fecha de Inscripción', 'required' => true, 'attr' => array('class' => 'form-control')))
//                         ->add('c_modalidadAtencion', 'choice', array('label' => 'Modalidad de atencion','choices'=>ModalidadAtencionTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))


                        ->add('c_domicilioDepartamento', 'choice', array('label' => 'Departamento', 'required' => true,'choices'=>$depArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                        ->add('c_domicilioProvincia', 'choice', array('label' => 'Provincia', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                        ->add('c_domicilioMunicipio', 'text', array('label' => 'Municipio', 'required' => true, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'100')))
                        ->add('c_domicilioLocalidad', 'text', array('label' => 'Localidad', 'required' => true, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'100')))
                        ->add('c_direccionZona', 'text', array('label' => 'Zona', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('c_direccionCalle', 'text', array('label' => 'Avenida/Calle', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('c_direccionNro', 'text', array('label' => 'Nro. vivienda', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('c_direccionTelefono', 'text', array('label' => 'Telefono', 'required' => false, 'attr' => array('class' => 'form-control jnumbers', 'autocomplete' => 'off','maxlength'=>'15')))
                        ->add('c_direccionCelular', 'text', array('label' => 'Celular', 'required' => false, 'attr' => array('class' => 'form-control jnumbers', 'autocomplete' => 'off','maxlength'=>'15')))
                        ->add('c_idiomaTipo1Id', 'entity', array('label' => 'Idioma o lengua 1', 'required' => true, 'empty_value' => 'Seleccionar..', 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'attr' => array('class' => 'form-control jupper')))
                        ->add('c_idiomaTipo2Id', 'entity', array('label' => 'Idioma o lengua 2', 'required' => false, 'empty_value' => 'Seleccionar..', 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'attr' => array('class' => 'form-control jupper')))
                        ->add('c_idiomaTipo3Id', 'entity', array('label' => 'Idioma o lengua 3', 'required' => false, 'empty_value' => 'Seleccionar..', 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'attr' => array('class' => 'form-control jupper')))
                        ->add('c_etniaTipoId', 'entity', array('label' => 'Identificacion etnica', 'required' => true, 'empty_value' => 'Seleccionar..', 'class' => 'SieAppWebBundle:EtniaTipo', 'property' => 'etnia', 'attr' => array('class' => 'form-control jupper')))
                        ->add('c_viveCon', 'choice', array('label' => 'El estudiante vive principalmente con','choices'=>ViveConTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_seguro', 'text', array('label' => 'Nombre del seguro de salud', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('c_medicacion', 'text', array('label' => 'Indique medicacion(es)', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('c_sangreTipoId', 'entity', array('label' => 'Tipo de Sangre', 'required' => true, 'class' => 'SieAppWebBundle:SangreTipo', 'property' => 'grupoSanguineo', 'attr' => array('class' => 'form-control jupper')))
                        ->add('c_guardar', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-red')));

					if ($area->getId() == 6) {
						$form->add('c_dificultadAprendizaje', 'choice', array('label' => 'Tipos de dificultad en el aprendizaje','choices'=>DificultadAprendizajeTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));
						$form->add('c_codigoPedagogico', 'text', array('label' => 'Codigo evaluacion', 'required' => false, 'attr' => array('class' => 'form-control')));
						$form->add('c_evaluacionPedagogica', 'text', array('label' => 'Evaluacion pedagogica', 'required' => false, 'attr' => array('class' => 'form-control')));
						$form->add('c_evaluacionUnidadEducativa', 'text', array('label' => 'Unidad edcuativa', 'required' => false, 'attr' => array('class' => 'form-control')));
						$form->add('c_evaluacionEscolaridad', 'text', array('label' => 'Escolaridad', 'required' => false, 'attr' => array('class' => 'form-control')));
						$form->add('c_parienteDiscapacidad', 'text', array('label' => 'Nombre del pariente con discapacidad', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')));
						$form->add('c_gradoParentesco', 'choice', array('label' => 'Grado de parentesco','choices'=>GradoParentescoTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));


					}
					elseif ($area->getId() == 7) {

						$form->add('c_informePsicopedagogico', 'text', array('label' => 'Informe psicopedagogico', 'required' => false, 'attr' => array('class' => 'form-control')));
						$form->add('c_codigoPsicopedagogico', 'text', array('label' => 'Codigo informe', 'required' => false, 'attr' => array('class' => 'form-control')));
						$form->add('c_deteccion', 'choice', array('label' => 'Detectado en','choices'=>DeteccionTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));
						$form->add('c_gradoTalento', 'choice', array('label' => 'Grado de talento','choices'=>GradoTalentoTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));
						$form->add('c_talento', 'choice', array('label' => 'Tipo de talento extraordinario','choices'=>TalentoTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));

					}

					else {
						$form->add('c_discapacidadEspecial', 'choice', array('label' => 'Area de discapacidad','choices'=>$discapacidadArray , 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')));
						$form->add('c_gradoDiscapacidad', 'choice', array('label' => 'Grado de discapacidad' , 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')));
						$form->add('c_origenDiscapacidad', 'choice', array('label' => 'Discapacidad que presenta es','choices'=>OrigenDiscapacidadTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')));
						$form->add('c_parienteDiscapacidad', 'text', array('label' => 'Nombre del pariente con discapacidad', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')));
						$form->add('c_gradoParentesco', 'choice', array('label' => 'Grado de parentesco','choices'=>GradoParentescoTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));

					}

                        return $form->getForm();


//                         ->add('c_servicio', 'entity', array('label' => 'Servicios','class' => 'SieAppWebBundle:ServicioTipo', 'property' => 'servicio','expanded' => true,'multiple' => true, 'group_by' => 'areaEspecialTipo.areaEspecial'))
//                         ->add('c_programa', 'entity', array('label' => 'Programas','class' => 'SieAppWebBundle:ProgramaTipo', 'property' => 'programa','expanded' => true,'multiple' => true, 'group_by' => 'areaEspecialTipo.areaEspecial'))

//                         ->add('c_turno', 'choice', array('label' => 'Turno','choices'=>$turnosArray , 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
//                         ->add('c_nivel','choice',array('label'=>'Nivel','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
//                         ->add('c_grado','choice',array('label'=>'Grado','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
//                         ->add('c_paralelo','choice',array('label'=>'Paralelo','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))

    }

    private function newFormSinRude($institucion, $sucursal, $gestion, $area, $institucionCurso) {
        $em = $this->getDoctrine()->getManager();
//         $query = $em->createQuery(
//                 'SELECT DISTINCT tt.id,tt.turno
//                 FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
//                 JOIN iec.turnoTipo tt
//                 WHERE iec.institucioneducativa = :id
//         		AND iec.areaEspecialTipo = :area
//                 AND iec.gestionTipo = :gestion
//                 ORDER BY tt.id')
//                 ->setParameter('id',$institucion->getId())
//                 ->setParameter('area',$area->getId())
//                 ->setParameter('gestion',$gestion);
//         $turnos = $query->getResult();
//         $turnosArray = array();
//         for($i=0;$i<count($turnos);$i++){
//             $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
//         }

        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' => 1));
        $depArray = array();
        foreach ($dep as $de) {
        	$depArray[$de->getId()] = $de->getLugar();
        }

            if ($area->getId() == 1) {
        	$discapacidadArray = [
        			DiscapacidadEspecialTipo::AUDITIVA => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::AUDITIVA)
        	];
        }
        elseif ($area->getId() == 2) {
        	$discapacidadArray = [
        			DiscapacidadEspecialTipo::VISUAL => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::VISUAL)
        	];
        }
        elseif ($area->getId() == 3) {
        	$discapacidadArray = [
        			DiscapacidadEspecialTipo::INTELECTUAL => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::INTELECTUAL),
        			DiscapacidadEspecialTipo::SINDROME_DE_DOWN => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::SINDROME_DE_DOWN),
        			DiscapacidadEspecialTipo::AUTISMO => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::AUTISMO)
        	];
        }
        elseif ($area->getId() == 4) {
        	$discapacidadArray = [
        			DiscapacidadEspecialTipo::FISICO_MOTORA => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::FISICO_MOTORA)
        	];
        }
        elseif ($area->getId() == 5) {
        	$discapacidadArray = [
        			DiscapacidadEspecialTipo::MULTIPLE => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::MULTIPLE)
        	];
        }
        elseif ($area->getId() == 8) {
        	$discapacidadArray = [
        			DiscapacidadEspecialTipo::SORDOCEGUERA => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::SORDOCEGUERA)
        	];
        }
        else {
        	$discapacidadArray = [
        			DiscapacidadEspecialTipo::NINGUNO => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::NINGUNO)
        	];
        }

//         ->add('rude', 'text', array('label' => 'Código RUDE', 'required' => true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))

        $form = $this->createFormBuilder()
                        ->setAction($this->generateUrl('estudianteinscripcion_especial_create'))
                        ->add('tipo_inscripcion', 'hidden', array('data' => 'sinRude'))
                        ->add('idInstitucion', 'hidden', array('data' => $institucion->getId()))
                        ->add('idArea','hidden',array('data'=> $area->getId()))
                        ->add('gestion', 'hidden', array('data' => $gestion))
                        ->add('idInstitucionCurso', 'hidden', array('data' => $institucionCurso->getId()))
                        ->add('codigoIns', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'data' => $institucion->getId(), 'attr' => array('class' => 'form-control')))
                        ->add('nombreIns', 'text', array('label' => 'Nombre Institución Educativa', 'disabled' => true, 'data' => $institucion->getInstitucioneducativa(), 'attr' => array('class' => 'form-control')))
                        ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad', 'required' => false, 'attr' => array('class' => 'form-control jnumbers', 'pattern' => '[0-9]{4,10}', 'autocomplete' => 'off','maxlength'=>'10')))
                        ->add('paterno', 'text', array('label' => 'Paterno', 'required' => false, 'attr' => array('class' => 'form-control jletters jupper', 'pattern' => '[a-zA-Z\Ññ ]{2,45}', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('materno', 'text', array('label' => 'Materno', 'required' => false, 'attr' => array('class' => 'form-control jletters jupper', 'pattern' => '[a-zA-Z\Ññ ]{2,45}', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('nombre', 'text', array('label' => 'Nombre(s)', 'required' => true, 'attr' => array('class' => 'form-control jletters jupper', 'pattern' => '[a-zA-Z\Ññ ]{2,85}', 'autocomplete' => 'off','maxlength'=>'85')))
                        ->add('oficialia', 'text', array('label' => 'Oficialia', 'required' => false, 'attr' => array('class' => 'form-control jupper', 'autocomplete' => 'off','maxlength'=>'25', 'pattern' => '[0-9a-zA-Z\-/]{2,45}')))
                        ->add('libro', 'text', array('label' => 'Libro', 'required' => false, 'attr' => array('class' => 'form-control jupper', 'autocomplete' => 'off','maxlength'=>'25', 'pattern' => '[0-9a-zA-Z\-/]{2,45}')))
                        ->add('partida', 'text', array('label' => 'Partida', 'required' => false, 'attr' => array('class' => 'form-control jupper', 'autocomplete' => 'off','maxlength'=>'25', 'pattern' => '[0-9a-zA-Z\-/]{2,45}')))
                        ->add('folio', 'text', array('label' => 'Folio', 'required' => false, 'attr' => array('class' => 'form-control jupper', 'autocomplete' => 'off','maxlength'=>'25', 'pattern' => '[0-9a-zA-Z\-/]{2,45}')))
                        ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('class' => 'form-control jupper', 'autocomplete' => 'off','pattern' => '[0-9]{1}[a-zA-Z]{1}','maxlength'=>'2')))
                        ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'required' => true, 'attr' => array('placeholder' => 'dd-mm-YYYY', 'class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('pais', 'entity', array('label' => 'Pais', 'required' => true, 'class' => 'SieAppWebBundle:PaisTipo', 'property' => 'pais', 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                        ->add('departamento', 'choice', array('label' => 'Departamento', 'required' => false, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                        ->add('provincia', 'choice', array('label' => 'Provincia', 'required' => false, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                        ->add('localidad', 'text', array('label' => 'Localidad', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'100')))
                        ->add('carnetCodepedis', 'text', array('label' => 'Carnet Codepedis', 'required' => false, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off','maxlength'=>'15', 'pattern' => '[0-9a-zA-Z\-/]{2,45}')))
                        ->add('carnetIbc', 'text', array('label' => 'Carnet I.B.C.', 'required' => false, 'attr' => array('class' => 'form-control jnumbers', 'autocomplete' => 'off','maxlength'=>'15')))
                        ->add('generoTipo', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'property' => 'genero', 'attr' => array('class' => 'form-control jupper')))
                        ->add('area', 'text', array('label' => 'Area de Especial','data'=>$area->getAreaEspecial(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('turno', 'text', array('label' => 'Turno','data'=>$institucionCurso->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('nivel', 'text', array('label' => 'Nivel','data'=>$institucionCurso->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('grado', 'text', array('label' => 'Grado','data'=>$institucionCurso->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('paralelo', 'text', array('label' => 'Paralelo','data'=>$institucionCurso->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('servicio', 'text', array('label' => 'Servicio','data'=>$institucionCurso->getEspecialServicioTipo()->getServicio(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('programa', 'text', array('label' => 'Programa','data'=>$institucionCurso->getEspecialProgramaTipo()->getPrograma(), 'disabled' => true, 'attr' => array('class' => 'form-control')))

                        ->add('periodo', 'entity', array('label' => 'Periodo','disabled'=>true,'class' => 'SieAppWebBundle:PeriodoTipo','data'=>$em->getReference('SieAppWebBundle:PeriodoTipo',1) ,'property' => 'periodo', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('fechaInscripcion', 'text', array('label' => 'Fecha de Inscripción', 'required' => true, 'attr' => array('class' => 'form-control')))
//                         ->add('modalidadAtencion', 'choice', array('label' => 'Modalidad de atencion','choices'=>ModalidadAtencionTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))

                        ->add('domicilioDepartamento', 'choice', array('label' => 'Departamento', 'required' => true,'choices'=>$depArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                        ->add('domicilioProvincia', 'choice', array('label' => 'Provincia', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                        ->add('domicilioMunicipio', 'text', array('label' => 'Municipio', 'required' => true, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'100')))
                        ->add('domicilioLocalidad', 'text', array('label' => 'Localidad', 'required' => true, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'100')))
                        ->add('direccionZona', 'text', array('label' => 'Zona', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('direccionCalle', 'text', array('label' => 'Avenida/Calle', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('direccionNro', 'text', array('label' => 'Nro. vivienda', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('direccionTelefono', 'text', array('label' => 'Telefono', 'required' => false, 'attr' => array('class' => 'form-control jnumbers', 'autocomplete' => 'off','maxlength'=>'15')))
                        ->add('direccionCelular', 'text', array('label' => 'Celular', 'required' => false, 'attr' => array('class' => 'form-control jnumbers', 'autocomplete' => 'off','maxlength'=>'15')))
                        ->add('idiomaTipo1Id', 'entity', array('label' => 'Idioma o lengua 1','empty_value' => 'Seleccionar..', 'required' => true, 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'attr' => array('class' => 'form-control jupper')))
                        ->add('idiomaTipo2Id', 'entity', array('label' => 'Idioma o lengua 2','empty_value' => 'Seleccionar..', 'required' => false, 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'attr' => array('class' => 'form-control jupper')))
                        ->add('idiomaTipo3Id', 'entity', array('label' => 'Idioma o lengua 3','empty_value' => 'Seleccionar..', 'required' => false, 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'attr' => array('class' => 'form-control jupper')))
                        ->add('etniaTipoId', 'entity', array('label' => 'Identificacion etnica','empty_value' => 'Seleccionar..', 'required' => true, 'class' => 'SieAppWebBundle:EtniaTipo', 'property' => 'etnia', 'attr' => array('class' => 'form-control jupper')))
                        ->add('viveCon', 'choice', array('label' => 'El estudiante vive principalmente con','choices'=>ViveConTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')))
                        ->add('seguro', 'text', array('label' => 'Nombre del seguro de salud', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('medicacion', 'text', array('label' => 'Indique medicacion(es)', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('sangreTipoId', 'entity', array('label' => 'Tipo de Sangre', 'required' => true, 'class' => 'SieAppWebBundle:SangreTipo', 'property' => 'grupoSanguineo', 'attr' => array('class' => 'form-control jupper')))

                        ->add('guardar', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-red')));

                        if ($area->getId() == 6) {
                        	$form->add('dificultadAprendizaje', 'choice', array('label' => 'Tipos de dificultad de aprendizaje','choices'=>DificultadAprendizajeTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('codigoPedagogico', 'text', array('label' => 'Codigo evaluacion', 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('evaluacionPedagogica', 'text', array('label' => 'Evaluacion pedagogica', 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('evaluacionUnidadEducativa', 'text', array('label' => 'Unidad edcuativa', 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('evaluacionEscolaridad', 'text', array('label' => 'Escolaridad', 'required' => false, 'attr' => array('class' => 'form-control')));
                        $form->add('parienteDiscapacidad', 'text', array('label' => 'Nombre del pariente con discapacidad', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')));
                        $form->add('gradoParentesco', 'choice', array('label' => 'Grado de parentesco','choices'=>GradoParentescoTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));
                        }
                        elseif ($area->getId() == 7) {
                        	$form->add('informePsicopedagogico', 'text', array('label' => 'Informe psicopedagogico', 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('codigoPsicopedagogico', 'text', array('label' => 'Codigo informe', 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('deteccion', 'choice', array('label' => 'Detectado en','choices'=>DeteccionTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('gradoTalento', 'choice', array('label' => 'Grado de talento','choices'=>GradoTalentoTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('talento', 'choice', array('label' => 'Tipo de talento extraordinario','choices'=>TalentoTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));


                        }

                        else {
                        	$form->add('discapacidadEspecial', 'choice', array('label' => 'Area de discapacidad','choices'=>$discapacidadArray , 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')));
                        	$form->add('gradoDiscapacidad', 'choice', array('label' => 'Grado de discapacidad' , 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')));
                        	$form->add('origenDiscapacidad', 'choice', array('label' => 'Discapacidad que presenta es','choices'=>OrigenDiscapacidadTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('parienteDiscapacidad', 'text', array('label' => 'Nombre del pariente con discapacidad', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')));
                        	$form->add('gradoParentesco', 'choice', array('label' => 'Grado de parentesco','choices'=>GradoParentescoTipo::getChoices() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));

                        }




                        return $form->getForm();
                        //                         ->add('turno', 'choice', array('label' => 'Turno', 'choices' => $turnosArray, 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        //                         ->add('nivel', 'choice', array('label' => 'Nivel', 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        //                         ->add('grado', 'choice', array('label' => 'Grado', 'required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        //                         ->add('paralelo', 'choice', array('label' => 'Paralelo', 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))

    }

    /**
     * Creates a new EstudianteInscripcion entity.
     *
     */
    public function createAction(Request $request) {
        try {
            $form = $request->get('form');
            $em = $this->getDoctrine()->getManager();

            if ($form['tipo_inscripcion'] == 'conRude') {
                // Inscripcion de estudiante con rude
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneByCodigoRude($form['c_rude']);
//                 dump($estudiante);die;
                if ($estudiante) {

                	$query = $em->createQuery(
                			'SELECT ei FROM SieAppWebBundle:EstudianteInscripcionEspecial ei
                    JOIN ei.institucioneducativaCursoEspecial ce
                	JOIN ce.institucioneducativaCurso cu
                	JOIN ei.estudianteInscripcion es
                	JOIN ei.socioeconomicoEspecial se
                    WHERE es.estudiante = :idEstudiante
                    AND se.id > 0 AND cu.gestionTipo = :gestion')->setMaxResults(1)
                	                    ->setParameter('idEstudiante', $estudiante->getId())
                	                    ->setParameter('gestion', $request->getSession()->get('idGestion'));
                	                    $inscripcion = $query->getResult();

                	if ($inscripcion) {
                		$inscripcion = $query->getSingleResult();
                	}
//                 	                    dump($inscripcion);die;
//                 	                    $inscripcion = $query->getSingleResult();
//                 	                    dump($inscripcion);die;
// //                 	                    dump($inscripcion->getSocioeconomicoEspecial()->getId());die;
                	if ($inscripcion) {
                		if ($inscripcion->getSocioeconomicoEspecial()->getId()>0) {
                			$socioeconomico = $em->getRepository('SieAppWebBundle:SocioeconomicoEspecial')->findOneById($inscripcion->getSocioeconomicoEspecial()->getId());
                		}
                		else {
                			$socioeconomico = new SocioeconomicoEspecial();
                		}

                	}
                	else {
                		$socioeconomico = new SocioeconomicoEspecial();
                	}
                	$em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');")->execute();
                	$em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_especial');")->execute();

                	$socioeconomico->setDomicilioDepartamento($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['c_domicilioDepartamento']));
                	$socioeconomico->setDomicilioProvincia($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['c_domicilioProvincia']));
                	$socioeconomico->setDomicilioMunicipio($form['c_domicilioMunicipio']);
                	$socioeconomico->setDomicilioLocalidad($form['c_domicilioLocalidad']);
                	$socioeconomico->setDireccionZona($form['c_direccionZona']);
                	$socioeconomico->setDireccionCalle($form['c_direccionCalle']);
                	$socioeconomico->setDireccionNro($form['c_direccionNro']);
                	$socioeconomico->setDireccionTelefono($form['c_direccionTelefono']);
                	$socioeconomico->setDireccionCelular($form['c_direccionCelular']);
                	$socioeconomico->setIdioma1Tipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['c_idiomaTipo1Id']));
                	$socioeconomico->setIdioma2Tipo(($form['c_idiomaTipo2Id'] != "") ? $em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['c_idiomaTipo2Id']) : null);
                	$socioeconomico->setIdioma3Tipo(($form['c_idiomaTipo3Id'] != "") ? $em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['c_idiomaTipo3Id']) : null);
                	$socioeconomico->setEtniaTipo($em->getRepository('SieAppWebBundle:EtniaTipo')->findOneById($form['c_etniaTipoId']));
                	$socioeconomico->setViveCon($form['c_viveCon']);
                	$socioeconomico->setSeguro($form['c_seguro']);
                	$socioeconomico->setMedicacion($form['c_medicacion']);
                	$socioeconomico->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById($form['c_sangreTipoId']));

                	$em->persist($socioeconomico);
                    $em->flush();
                    /* Obteniendo el id del curso */
                    $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneById($form['c_idInstitucionCurso']);

                    $inscripcionsie = new EstudianteInscripcion();
                    $inscripcionsie->setNumMatricula(0);
                    $inscripcionsie->setFechaInscripcion(new \DateTime($form['c_fechaInscripcion']));
                    $inscripcionsie->setOperativoId(1);
                    $inscripcionsie->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById(1));
                    $inscripcionsie->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById(4));
                    $inscripcionsie->setEstudiante($estudiante);
                    $inscripcionsie->setFechaRegistro(new \DateTime('now'));
                    $inscripcionsie->setInstitucioneducativaCurso($institucionCurso->getInstitucioneducativaCurso());

                    $em->persist($inscripcionsie);
                    $em->flush();



                    $inscripcion = new EstudianteInscripcionEspecial();

                    if ($form['c_idArea'] != 6 and $form['c_idArea'] != 7) {
	                    $inscripcion->setDiscapacidad($form['c_discapacidadEspecial']);
	                    $inscripcion->setGradoDiscapacidadTipo($em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->findOneById($form['c_gradoDiscapacidad']));
	                    $inscripcion->setTipoDiscapacidad($form['c_origenDiscapacidad']);
	                    $socioeconomico->setParienteDiscapacidad($form['c_parienteDiscapacidad']);
	                    $socioeconomico->setGradoParentesco($form['c_gradoParentesco']);

                    }
                    if ($form['c_idArea'] == 6) {

	                    $inscripcion->setDificultadAprendizaje($form['c_dificultadAprendizaje']);
	                    $inscripcion->setCodigoPedagogico($form['c_codigoPedagogico']);
	                    $inscripcion->setEvaluacionPedagogica($form['c_evaluacionPedagogica']);
	                    $inscripcion->setEvaluacionUnidadEducativa($form['c_evaluacionUnidadEducativa']);
	                    $inscripcion->setEvaluacionEscolaridad($form['c_evaluacionEscolaridad']);
	                    $socioeconomico->setParienteDiscapacidad($form['c_parienteDiscapacidad']);
	                    $socioeconomico->setGradoParentesco($form['c_gradoParentesco']);

                    }

                    if ($form['c_idArea'] == 7) {
	                    $inscripcion->setInformePsicopedagogicoTalento($form['c_informePsicopedagogico']);
	                    $inscripcion->setCodigoPsicopedagogico($form['c_codigoPsicopedagogico']);
	                    $inscripcion->setDeteccionTalento($form['c_deteccion']);
	                    $inscripcion->setGradoTalento($form['c_gradoTalento']);
	                    $inscripcion->setTipoTalento($form['c_talento']);
                    }

                    $em->persist($socioeconomico);
                    $em->flush();
//                     dump($inscripcionsie);die;
                    $socioeconomico = $em->getRepository('SieAppWebBundle:SocioeconomicoEspecial')->findOneById($socioeconomico);
                    $inscripcionsie = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcionsie);
//                     dump($socioeconomico);die;
                    $inscripcion->setSocioeconomicoEspecial($socioeconomico);
                    $inscripcion->setEstudianteInscripcion($inscripcionsie);
//                     dump($inscripcion);die;

//                     $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array(
//                                         'gestionTipo'=>$form['c_gestion'],
//                                         'institucioneducativa'=>$form['c_idInstitucion'],
//                                         'turnoTipo'=>$form['c_turno'],
//                     		            'areaEspecialTipo'=>$form['c_idArea'],
//                                         'nivelTipo'=>$form['c_nivel'],
//                                         'gradoTipo'=>$form['c_grado'],
//                                         'paraleloTipo'=>$form['c_paralelo']
//                                     ));
                    /**
                     * Si no existe el curso seleccionado, redireccionamos y creamos el mensaje de error
                     */
                    if(!$institucionCurso){
                        $this->get('session')->getFlashBag()->add('errorEstudiante', 'El codigo RUDE ingresado no es válido, intentelo de nuevo.');
                        return $this->redirect($this->generateUrl('estudianteinscripcion_especial_new'));
                    }

                    $inscripcion->setInstitucioneducativaCursoEspecial($institucionCurso);

                    $em->persist($inscripcion);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('registroConRudeOk', 'El Estudiante fue inscrito correctamente');
                    return $this->redirect($this->generateUrl('estudianteinscripcion_especial',array('op'=>'result')));
                } else {
                    $this->get('session')->getFlashBag()->add('errorEstudiante', 'El codigo RUDE ingresado no es válido, intentelo de nuevo.');
                    return $this->redirect($this->generateUrl('estudianteinscripcion_especial_new'));
                }
            } else {
                // inscripcion de estudiante sin Rude
                //
                // Verificamos si los datos del nuevo estudiante coinciden con alguno de la base
                $estudiante = null;
                if($form['carnetIdentidad'] != ""){
                    $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('carnetIdentidad' => $form['carnetIdentidad'], 'fechaNacimiento' => new \DateTime($form['fechaNacimiento'])));
                }

                if (!$estudiante) {

                    // Generamos el rude del estudiante
                    $query = $em->getConnection()->prepare('SELECT get_estudiante_nuevo_rude(:sie::VARCHAR,:gestion::VARCHAR)');
                    $query->bindValue(':sie', $form['idInstitucion']);
                    $query->bindValue(':gestion', $form['gestion']);
                    $query->execute();
                    $codigorude = $query->fetchAll();

//                     $codigo = $codigorude[0];
//                     $codigorude = $form['idInstitucion'].$form['gestion'];//$codigo['fuc_codigoestudiante'];
                    // Registramos al estudiante
                    $newEstudiante = new Estudiante();
                    $newEstudiante->setCarnetCodepedis($form['carnetCodepedis']);
                    $newEstudiante->setCarnetIbc($form['carnetIbc']);
                    $newEstudiante->setCarnetIdentidad($form['carnetIdentidad']);
//                     $newEstudiante->setCelular($form['direccionCelular']);
//                     $newEstudiante->setCodigoRude($form['rude']);

                    $newEstudiante->setCodigoRude($codigorude[0]["get_estudiante_nuevo_rude"]);
//                     dump($newEstudiante);die;
                    $newEstudiante->setComplemento(mb_strtoupper($form['complemento'],'utf-8'));
                    $newEstudiante->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
                    $newEstudiante->setFolio($form['folio']);
                    $newEstudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['generoTipo']));
                    $newEstudiante->setLibro($form['libro']);
                    $newEstudiante->setLocalidadNac($form['localidad']);
                    $newEstudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['departamento'] ? $form['departamento'] : 0));
                    $newEstudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['provincia'] ? $form['provincia'] : 0));
                    $newEstudiante->setPaterno(mb_strtoupper($form['paterno'],'utf-8'));
                    $newEstudiante->setMaterno(mb_strtoupper($form['materno'],'utf-8'));
                    $newEstudiante->setNombre(mb_strtoupper($form['nombre'],'utf-8'));
                    $newEstudiante->setOficialia($form['oficialia']);
                    $newEstudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->findOneById($form['pais']));
                    $newEstudiante->setPartida($form['partida']);
                    $newEstudiante->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById($form['sangreTipoId']));
//                     $newEstudiante->setSangreTipoId($form['sangreTipoId']);
                                    //dump($newEstudiante);die;
                    $em->persist($newEstudiante);
                    $em->flush();

                    $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($newEstudiante->getId());
                }

                $query = $em->createQuery(
                		'SELECT ei FROM SieAppWebBundle:EstudianteInscripcionEspecial ei
                    JOIN ei.institucioneducativaCursoEspecial ce
                	JOIN ce.institucioneducativaCurso cu
                	JOIN ei.estudianteInscripcion es
                	JOIN ei.socioeconomicoEspecial se
                    WHERE es.estudiante = :idEstudiante
                    AND se.id > 0 AND cu.gestionTipo = :gestion')->setMaxResults(1)
                                    ->setParameter('idEstudiante', $estudiante->getId())
                                    ->setParameter('gestion', $request->getSession()->get('idGestion'));
                                    $inscripcion = $query->getResult();
//                 dump($inscripcion);die;
                if ($inscripcion) {
                  	$inscripcion = $query->getSingleResult();
                }




//                 $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionEspecial')->getEstudianteInscripcion()->findOneByEstudiante(array(
//                 		'estudiante'=>$estudiante->getId(),
//                 		'gestionTipo'=>$request->getSession()->get('idGestion')
//                 ));

//                 dump($inscripcion);die;

                if ($inscripcion) {
                	if ($inscripcion->getSocioeconomicoEspecial()->getId()>0) {
                		$socioeconomico = $em->getRepository('SieAppWebBundle:SocioeconomicoEspecial')->findOneById($inscripcion->getSocioeconomicoEspecial()->getId());
                	}
                	else {
                		$socioeconomico = new SocioeconomicoEspecial();
                	}


                }
                else {
                	$socioeconomico = new SocioeconomicoEspecial();
                }
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');")->execute();
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_especial');")->execute();

                $socioeconomico->setDomicilioDepartamento($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['domicilioDepartamento']));
                $socioeconomico->setDomicilioProvincia($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['domicilioProvincia']));
                $socioeconomico->setDomicilioMunicipio($form['domicilioMunicipio']);
                $socioeconomico->setDomicilioLocalidad($form['domicilioLocalidad']);
                $socioeconomico->setDireccionZona($form['direccionZona']);
                $socioeconomico->setDireccionCalle($form['direccionCalle']);
                $socioeconomico->setDireccionNro($form['direccionNro']);
                $socioeconomico->setDireccionTelefono($form['direccionTelefono']);
                $socioeconomico->setDireccionCelular($form['direccionCelular']);
                $socioeconomico->setIdioma1Tipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['idiomaTipo1Id']));
                $socioeconomico->setIdioma2Tipo(($form['idiomaTipo2Id'] != "") ? $em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['idiomaTipo2Id']) : null);
                $socioeconomico->setIdioma3Tipo(($form['idiomaTipo3Id'] != "") ? $em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['idiomaTipo3Id']) : null);
                $socioeconomico->setEtniaTipo($em->getRepository('SieAppWebBundle:EtniaTipo')->findOneById($form['etniaTipoId']));
                $socioeconomico->setViveCon($form['viveCon']);
                $socioeconomico->setSeguro($form['seguro']);
                $socioeconomico->setMedicacion($form['medicacion']);
                $socioeconomico->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById($form['sangreTipoId']));

                $em->persist($socioeconomico);
                $em->flush();
                /* Obteniendo el id del curso */
                $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneById($form['idInstitucionCurso']);
//                 dump( $institucionCurso->getInstitucioneducativaCurso()->getId());die;



                $query = $em->createQuery(
                		'SELECT ei FROM SieAppWebBundle:EstudianteInscripcion ei
                    WHERE ei.institucioneducativaCurso = :idCurso
                	AND   ei.estudiante = :idEstudiante')->setMaxResults(1)
                          ->setParameter('idCurso', $institucionCurso->getInstitucioneducativaCurso()->getId())
                	      ->setParameter('idEstudiante', $estudiante->getId());

               $inscripcionsie = $query->getResult();
// 				dump($inscripcionsie);die;

                if ($inscripcionsie) {
                	$inscripcionsie = $query->getSingleResult();

                } else {
                	$inscripcionsie = new EstudianteInscripcion();
                	$inscripcionsie->setNumMatricula(0);
                	$inscripcionsie->setFechaInscripcion(new \DateTime($form['fechaInscripcion']));
                	$inscripcionsie->setOperativoId(1);
                	$inscripcionsie->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById(1));
                	$inscripcionsie->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById(4));
                	$inscripcionsie->setEstudiante($estudiante);
                	$inscripcionsie->setFechaRegistro(new \DateTime('now'));
                	$inscripcionsie->setInstitucioneducativaCurso($institucionCurso->getInstitucioneducativaCurso());


                	$em->persist($inscripcionsie);
                	$em->flush();

                }

//                 dump($inscripcionsie);die;



                $inscripcion = new EstudianteInscripcionEspecial();

                if ($form['idArea'] != 6 and $form['idArea'] != 7) {

                $inscripcion->setDiscapacidad($form['discapacidadEspecial']);
                $inscripcion->setGradoDiscapacidadTipo($em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->findOneById($form['gradoDiscapacidad']));
                $inscripcion->setTipoDiscapacidad($form['origenDiscapacidad']);
                $socioeconomico->setParienteDiscapacidad($form['parienteDiscapacidad']);
                $socioeconomico->setGradoParentesco($form['gradoParentesco']);

                }
                if ($form['idArea'] == 6 ) {

                $inscripcion->setDificultadAprendizaje($form['dificultadAprendizaje']);
                $inscripcion->setCodigoPedagogico($form['codigoPedagogico']);
                $inscripcion->setEvaluacionPedagogica($form['evaluacionPedagogica']);
                $inscripcion->setEvaluacionUnidadEducativa($form['evaluacionUnidadEducativa']);
                $inscripcion->setEvaluacionEscolaridad($form['evaluacionEscolaridad']);
                $socioeconomico->setParienteDiscapacidad($form['parienteDiscapacidad']);
                $socioeconomico->setGradoParentesco($form['gradoParentesco']);

                }
                if ($form['idArea'] == 7 ) {
                $inscripcion->setInformePsicopedagogicoTalento($form['informePsicopedagogico']);
                $inscripcion->setCodigoPsicopedagogico($form['codigoPsicopedagogico']);
                $inscripcion->setDeteccionTalento($form['deteccion']);
                $inscripcion->setGradoTalento($form['gradoTalento']);
                $inscripcion->setTipoTalento($form['talento']);
                }

                $em->persist($socioeconomico);
                $em->flush();

//                 dump($inscripcionsie);die;
                $socioeconomico = $em->getRepository('SieAppWebBundle:SocioeconomicoEspecial')->findOneById($socioeconomico);
                $inscripcionsie = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcionsie);
//                 $inscripcionsieFin = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcionsie);

//                 dump($inscripcionsieFin);die;
                $inscripcion->setSocioeconomicoEspecial($socioeconomico);
                $inscripcion->setEstudianteInscripcion($inscripcionsie);





//                 $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array(
//                                     'gestionTipo'=>$form['gestion'],
//                                     'institucioneducativa'=>$form['idInstitucion'],
//                                     'turnoTipo'=>$form['turno'],
//                 		            'areaEspecialTipo'=>$form['idArea'],
//                                     'nivelTipo'=>$form['nivel'],
//                                     'gradoTipo'=>$form['grado'],
//                                     'paraleloTipo'=>$form['paralelo']
//                                 ));
                /**
                 * Si no existe el curso seleccionado, redireccionamos y creamos el mensaje de error
                 */
                if(!$institucionCurso){
                    $this->get('session')->getFlashBag()->add('errorEstudiante', 'No se pudo realizar la inscripcion en el curso seleccionado, intentelo nuevamente.');
                    return $this->redirect($this->generateUrl('estudianteinscripcion_new'));
                }
                $inscripcion->setInstitucioneducativaCursoEspecial($institucionCurso);
//                 $inscripcion->setFechaRegistro(new \DateTime('now'));

                $em->persist($inscripcion);
                $em->flush();

                $this->get('session')->getFlashBag()->add('registroConRudeOk', 'El Estudiante fue inscrito correctamente');
                return $this->redirect($this->generateUrl('estudianteinscripcion_especial',array('op'=>'result')));
            }
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('errorInscripcion', 'No se pudo realizar la inscripción');
            return $this->redirect($this->generateUrl('estudianteinscripcion_especial_new'));
        }
    }

    public function editAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionEspecial')->findOneById($request->get('idInscripcion'));

//             dump($request->get('idInscripcion'));die;
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($inscripcion->getInstitucioneducativaCursoEspecial()->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId());
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($inscripcion->getEstudianteInscripcion()->getEstudiante());
            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->find($inscripcion->getInstitucioneducativaCursoEspecial()->getId());

            $formulario = $this->editForm($inscripcion,$institucion,$estudiante,$institucionCurso);
            return $this->render('SieEspecialBundle:EstudianteInscripcionEspecial:edit.html.twig',array('formConRude'=>$formulario->createView()));

        } catch (Exception $ex) {

        }
    }

    private function editForm($inscripcion,$institucion,$estudiante,$institucionCurso){
        $this->session = new Session();

        $em = $this->getDoctrine()->getManager();
        /**
         * Listamos los turnos
         */
        $query = $em->createQuery(
                'SELECT DISTINCT tt.id,tt.turno
                FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
        		JOIN iec.institucioneducativaCurso ie
                JOIN ie.turnoTipo tt
                WHERE ie.institucioneducativa = :id
        		AND iec.especialAreaTipo = :area
                AND ie.gestionTipo = :gestion
                ORDER BY tt.id')
                ->setParameter('id',$institucion->getId())
                ->setParameter('area',$institucionCurso->getEspecialAreaTipo()->getId())
                ->setParameter('gestion',$this->session->get('idGestion'));
        $turnos = $query->getResult();
        $turnosArray = array();
        for($i=0;$i<count($turnos);$i++){
            $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
        }

        /**
         * Listamos las areas
         *
         */
//         $query = $em->createQuery(
//                 'SELECT DISTINCT at.id,at.areaEspecial
//                 FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
//                 JOIN iec.areaEspecialTipo at
//                 WHERE iec.institucioneducativa = :id
//                 AND iec.turnoTipo = :turno
//         		AND iec.gestionTipo = :gestion
//                 ORDER BY at.id')
//                 ->setParameter('id',$institucion->getId())
//                 ->setParameter('turno',$institucionCurso->getTurnoTipo()->getId())
//                 ->setParameter('gestion',$this->session->get('idGestion'));
//         $areas = $query->getResult();
//         $areasArray = array();
//         for($i=0;$i<count($areas);$i++){
//             $areasArray[$areas[$i]['id']] = $areas[$i]['areaEspecial'];
//         }
        /**
         * Listamos los niveles del turno seleccionado
         */
        $query = $em->createQuery(
                'SELECT DISTINCT nt.id,nt.nivel
                FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
        		JOIN iec.institucioneducativaCurso ie
                JOIN ie.nivelTipo nt
                WHERE ie.institucioneducativa = :id
                AND ie.gestionTipo = :gestion
                AND ie.turnoTipo = :turno
        		AND iec.especialAreaTipo = :area
                ORDER BY nt.id')
                ->setParameter('id',$institucion->getId())
                ->setParameter('gestion',$this->session->get('idGestion'))
                ->setParameter('area',$institucionCurso->getEspecialAreaTipo()->getId())
                ->setParameter('turno',$institucionCurso->getInstitucioneducativaCurso()->getTurnoTipo()->getId());

        $niveles = $query->getResult();
        $nivelesArray = array();
        for($i=0;$i<count($niveles);$i++){
            $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
        }

        /**
         * Listamos los grados segun el turno y nivel
         */
        $query = $em->createQuery(
                'SELECT DISTINCT gt.id,gt.grado
                FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
        		JOIN iec.institucioneducativaCurso ie
                JOIN ie.gradoTipo gt
                WHERE ie.institucioneducativa = :id
                AND ie.gestionTipo = :gestion
                AND ie.turnoTipo = :turno
        		AND iec.especialAreaTipo = :area
                AND ie.nivelTipo = :nivel
                ORDER BY gt.id')
                ->setParameter('id',$institucion->getId())
                ->setParameter('gestion',$this->session->get('idGestion'))
                ->setParameter('turno',$institucionCurso->getInstitucioneducativaCurso()->getTurnoTipo()->getId())
                ->setParameter('area',$institucionCurso->getEspecialAreaTipo()->getId())
                ->setParameter('nivel',$institucionCurso->getInstitucioneducativaCurso()->getNivelTipo()->getId());
        $grados = $query->getResult();
        $gradosArray = array();
        for($i=0;$i<count($grados);$i++){
            $gradosArray[$grados[$i]['id']] = $grados[$i]['grado'];
        }

        //lista de servicios

        $query = $em->createQuery(
        		'SELECT DISTINCT st.id,st.servicio
                FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
                JOIN iec.especialServicioTipo st
        		JOIN iec.institucioneducativaCurso ie
                WHERE ie.institucioneducativa = :id
                AND ie.gestionTipo = :gestion
                AND ie.turnoTipo = :turno
        		AND iec.especialAreaTipo = :area
                AND ie.nivelTipo = :nivel
                ORDER BY st.id')
                        ->setParameter('id',$institucion->getId())
                        ->setParameter('gestion',$this->session->get('idGestion'))
                        ->setParameter('turno',$institucionCurso->getInstitucioneducativaCurso()->getTurnoTipo()->getId())
                        ->setParameter('area',$institucionCurso->getEspecialAreaTipo()->getId())
                        ->setParameter('nivel',$institucionCurso->getInstitucioneducativaCurso()->getNivelTipo()->getId());
        $servicios = $query->getResult();
        $serviciosArray = array();
        for($i=0;$i<count($servicios);$i++){
           	$serviciosArray[$servicios[$i]['id']] = $servicios[$i]['servicio'];
        }

        //lista de programas


        $query = $em->createQuery(
        		'SELECT DISTINCT pt.id,pt.programa
                FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
                JOIN iec.especialProgramaTipo pt
        		JOIN iec.institucioneducativaCurso ie
                WHERE ie.institucioneducativa = :id
                AND ie.gestionTipo = :gestion
                AND ie.turnoTipo = :turno
        		AND iec.especialAreaTipo = :area
                AND ie.nivelTipo = :nivel
                ORDER BY pt.id')
                        ->setParameter('id',$institucion->getId())
                        ->setParameter('gestion',$this->session->get('idGestion'))
                        ->setParameter('turno',$institucionCurso->getInstitucioneducativaCurso()->getTurnoTipo()->getId())
                        ->setParameter('area',$institucionCurso->getEspecialAreaTipo()->getId())
                        ->setParameter('nivel',$institucionCurso->getInstitucioneducativaCurso()->getNivelTipo()->getId());
        $programas = $query->getResult();
        $programasArray = array();
        for($i=0;$i<count($programas);$i++){
           	$programasArray[$programas[$i]['id']] = $programas[$i]['programa'];
        }


        /**
         * Listamos los paralelos segun el turno nivel y grado
         */
        $query = $em->createQuery(
                'SELECT DISTINCT pt.id,pt.paralelo
                FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
        		JOIN iec.institucioneducativaCurso ie
        		JOIN ie.paraleloTipo pt
                WHERE ie.institucioneducativa = :id
                AND ie.gestionTipo = :gestion
                AND ie.turnoTipo = :turno
        		AND iec.especialAreaTipo = :area
                AND ie.nivelTipo = :nivel
                AND ie.gradoTipo = :grado
                ORDER BY pt.id')
                ->setParameter('id',$institucion->getId())
                ->setParameter('gestion',$this->session->get('idGestion'))
                ->setParameter('turno',$institucionCurso->getInstitucioneducativaCurso()->getTurnoTipo()->getId())
                ->setParameter('area',$institucionCurso->getEspecialAreaTipo()->getId())
                ->setParameter('nivel',$institucionCurso->getInstitucioneducativaCurso()->getNivelTipo()->getId())
                ->setParameter('grado',$institucionCurso->getInstitucioneducativaCurso()->getGradoTipo()->getId());
        $paralelos = $query->getResult();
        $paralelosArray = array();
        for($i=0;$i<count($paralelos);$i++){
            $paralelosArray[$paralelos[$i]['id']] = $paralelos[$i]['paralelo'];
        }


        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' => 1));
        $depArray = array();
        foreach ($dep as $de) {
        	$depArray[$de->getId()] = $de->getLugar();
        }
//         dump($inscripcioie;
        $pro = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $inscripcion->getSocioeconomicoEspecial()->getDomicilioDepartamento()->getid()));

        $proArray = array();
        foreach ($pro as $p) {
        	$proArray[$p->getid()] = $p->getlugar();
        }

        //lista grados de discapcidad

        $query = $em->createQuery(
        		'SELECT DISTINCT gt.id,gt.gradoDiscapacidad
                FROM SieAppWebBundle:EstudianteInscripcionEspecial ei
                JOIN ei.gradoDiscapacidadTipo gt
                WHERE ei.id = :id
                ORDER BY gt.id')
                        ->setParameter('id',$inscripcion->getId());
                        $gradosDiscapacidad = $query->getResult();
                        $gradosDiscapacidadArray = array();
                        for($i=0;$i<count($gradosDiscapacidad);$i++){
                        	$gradosDiscapacidadArray[$gradosDiscapacidad[$i]['id']] = $gradosDiscapacidad[$i]['gradoDiscapacidad'];
                        }

                        if ($institucionCurso->getEspecialAreaTipo()->getId() == 1) {
                        	$discapacidadArray = [
                        			DiscapacidadEspecialTipo::AUDITIVA => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::AUDITIVA)
                        	];
                        }
                        elseif ($institucionCurso->getEspecialAreaTipo()->getId() == 2) {
                        	$discapacidadArray = [
                        			DiscapacidadEspecialTipo::VISUAL => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::VISUAL)
                        	];
                        }
                        elseif ($institucionCurso->getEspecialAreaTipo()->getId() == 3) {
                        	$discapacidadArray = [
                        			DiscapacidadEspecialTipo::INTELECTUAL => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::INTELECTUAL),
                        			DiscapacidadEspecialTipo::SINDROME_DE_DOWN => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::SINDROME_DE_DOWN),
                        			DiscapacidadEspecialTipo::AUTISMO => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::AUTISMO)
                        	];
                        }
                        elseif ($institucionCurso->getEspecialAreaTipo()->getId() == 4) {
                        	$discapacidadArray = [
                        			DiscapacidadEspecialTipo::FISICO_MOTORA => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::FISICO_MOTORA)
                        	];
                        }
                        elseif ($institucionCurso->getEspecialAreaTipo()->getId() == 5) {
                        	$discapacidadArray = [
                        			DiscapacidadEspecialTipo::MULTIPLE => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::MULTIPLE)
                        	];
                        }
                        elseif ($institucionCurso->getEspecialAreaTipo()->getId() == 8) {
                        	$discapacidadArray = [
                        			DiscapacidadEspecialTipo::SORDOCEGUERA => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::SORDOCEGUERA)
                        	];
                        }
                        else {
                        	$discapacidadArray = [
                        			DiscapacidadEspecialTipo::NINGUNO => DiscapacidadEspecialTipo::getReadableValue(DiscapacidadEspecialTipo::NINGUNO)
                        	];
                        }


        $form = $this->createFormBuilder()
                        ->setAction($this->generateUrl('estudianteinscripcion_especial_update'))
                        ->add('idInscripcion', 'hidden', array('data' => $inscripcion->getId()))
                        ->add('idInstitucion','hidden',array('data'=> $institucion->getId()))
                        ->add('idEstudiante','hidden',array('data'=> $estudiante->getId()))
                        ->add('idCurso','hidden',array('data'=> $institucionCurso->getId()))
                        ->add('idArea','hidden',array('data'=> $institucionCurso->getEspecialAreaTipo()->getId()))
                        ->add('gestion','hidden',array('data'=>$this->session->get('idGestion')))
                        ->add('codigoIns', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'data' => $institucion->getId(), 'attr' => array('class' => 'form-control')))
                        ->add('nombreIns', 'text', array('label' => 'Nombre Institución Educativa', 'disabled' => true, 'data' => $institucion->getInstitucioneducativa(), 'attr' => array('class' => 'form-control')))
                        ->add('rude', 'text', array('label' => 'Código RUDE','data'=>$estudiante->getCodigoRude(), 'required' => true,'disabled'=>true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('paterno', 'text', array('label' => 'Paterno','data'=>$estudiante->getPaterno(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('materno', 'text', array('label' => 'Materno','data'=>$estudiante->getMaterno(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('nombre', 'text', array('label' => 'Nombre(s)','data'=>$estudiante->getNombre(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento','data'=>$estudiante->getFechaNacimiento()->format('d-m-Y'), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('area', 'text', array('label' => 'Areas de Especial','data'=>$institucionCurso->getEspecialAreaTipo()->getAreaEspecial(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('turno', 'choice', array('label' => 'Turno', 'choices' => $turnosArray,'data'=>$institucionCurso->getInstitucioneducativaCurso()->getTurnoTipo()->getId(),'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('nivel', 'choice', array('label' => 'Nivel','choices'=>$nivelesArray,'data'=>$institucionCurso->getInstitucioneducativaCurso()->getNivelTipo()->getId(),'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('grado', 'choice', array('label' => 'Grado','choices'=>$gradosArray, 'data'=>$institucionCurso->getInstitucioneducativaCurso()->getGradoTipo()->getId(),'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('paralelo', 'choice', array('label' => 'Paralelo', 'choices' => $paralelosArray, 'data'=>$institucionCurso->getInstitucioneducativaCurso()->getParaleloTipo()->getId(),'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('servicio', 'choice', array('label' => 'Servicio','choices'=>$serviciosArray, 'data'=>$institucionCurso->getEspecialServicioTipo()->getId(),'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('programa', 'choice', array('label' => 'Programa','choices'=>$programasArray, 'data'=>$institucionCurso->getEspecialProgramaTipo()->getId(),'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))

                        ->add('periodo', 'entity', array('label' => 'Periodo', 'class' => 'SieAppWebBundle:PeriodoTipo', 'property' => 'periodo', 'data'=>$em->getReference('SieAppWebBundle:PeriodoTipo',$institucionCurso->getInstitucioneducativaCurso()->getPeriodoTipo()->getId()),'empty_value' => 'Seleccionar...', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('fechaInscripcion', 'text', array('label' => 'Fecha de Inscripción','data'=>$inscripcion->getEstudianteInscripcion()->getFechaInscripcion()->format('d-m-Y'), 'disabled' => true, 'attr' => array('class' => 'form-control')))
//                         ->add('modalidadAtencion', 'choice', array('label' => 'Modalidad de atencion', 'choices' => ModalidadAtencionTipo::getChoices(),'data'=> $inscripcion->getModalidadAtencion(),'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))

                        ->add('domicilioDepartamento', 'choice', array('label' => 'Departamento', 'required' => true,'choices'=>$depArray ,'data'=> $inscripcion->getSocioeconomicoEspecial()->getDomicilioDepartamento()->getId(),'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                        ->add('domicilioProvincia', 'choice', array('label' => 'Provincia', 'required' => true,'choices'=>$proArray,'data'=> $inscripcion->getSocioeconomicoEspecial()->getDomicilioProvincia()->getId(), 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))

                        ->add('domicilioMunicipio', 'text', array('label' => 'Municipio','data'=> $inscripcion->getSocioeconomicoEspecial()->getDomicilioMunicipio(), 'required' => true, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'100')))
                        ->add('domicilioLocalidad', 'text', array('label' => 'Localidad','data'=> $inscripcion->getSocioeconomicoEspecial()->getDomicilioLocalidad(), 'required' => true, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'100')))
                        ->add('direccionZona', 'text', array('label' => 'Zona','data'=> $inscripcion->getSocioeconomicoEspecial()->getDireccionZona(), 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('direccionCalle', 'text', array('label' => 'Avenida/Calle','data'=> $inscripcion->getSocioeconomicoEspecial()->getDireccionCalle(), 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('direccionNro', 'text', array('label' => 'Nro. vivienda','data'=> $inscripcion->getSocioeconomicoEspecial()->getDireccionNro(), 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('direccionTelefono', 'text', array('label' => 'Telefono','data'=> $inscripcion->getSocioeconomicoEspecial()->getDireccionTelefono(), 'required' => false, 'attr' => array('class' => 'form-control jnumbers', 'autocomplete' => 'off','maxlength'=>'15')))
                        ->add('direccionCelular', 'text', array('label' => 'Celular','data'=> $inscripcion->getSocioeconomicoEspecial()->getDireccionCelular(), 'required' => false, 'attr' => array('class' => 'form-control jnumbers', 'autocomplete' => 'off','maxlength'=>'15')))
                        ->add('idiomaTipo1Id', 'entity', array('label' => 'Idioma o lengua 1','empty_value' => 'Seleccionar..', 'required' => true,'data'=> $inscripcion->getSocioeconomicoEspecial()->getIdioma1Tipo(), 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'attr' => array('class' => 'form-control jupper')))
                        ->add('idiomaTipo2Id', 'entity', array('label' => 'Idioma o lengua 2','empty_value' => 'Seleccionar..', 'required' => false,'data'=> $inscripcion->getSocioeconomicoEspecial()->getIdioma2Tipo(), 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'attr' => array('class' => 'form-control jupper')))
                        ->add('idiomaTipo3Id', 'entity', array('label' => 'Idioma o lengua 3','empty_value' => 'Seleccionar..', 'required' => false,'data'=> $inscripcion->getSocioeconomicoEspecial()->getIdioma3Tipo(), 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'attr' => array('class' => 'form-control jupper')))
                        ->add('etniaTipoId', 'entity', array('label' => 'Identificacion etnica','empty_value' => 'Seleccionar..', 'required' => true,'data'=> $inscripcion->getSocioeconomicoEspecial()->getEtniaTipo(), 'class' => 'SieAppWebBundle:EtniaTipo', 'property' => 'etnia', 'attr' => array('class' => 'form-control jupper')))
                        ->add('viveCon', 'choice', array('label' => 'El estudiante vive principalmente con','choices'=>ViveConTipo::getChoices() ,'data'=> $inscripcion->getSocioeconomicoEspecial()->getViveCon(), 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')))
                        ->add('seguro', 'text', array('label' => 'Nombre seguro de salud','data'=> $inscripcion->getSocioeconomicoEspecial()->getSeguro(), 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('medicacion', 'text', array('label' => 'Indique medicacion(es)','data'=> $inscripcion->getSocioeconomicoEspecial()->getMedicacion(), 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')))
                        ->add('sangreTipoId', 'entity', array('label' => 'Tipo de Sangre', 'required' => true, 'data'=> $inscripcion->getSocioeconomicoEspecial()->getSangreTipo(), 'class' => 'SieAppWebBundle:SangreTipo', 'property' => 'grupoSanguineo', 'attr' => array('class' => 'form-control jupper')))
                        ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-red')));


                        if ($institucionCurso->getEspecialAreaTipo()->getId() == 6) {
                        	$form->add('dificultadAprendizaje', 'choice', array('label' => 'Tipos de dificultad de aprendizaje','choices'=>DificultadAprendizajeTipo::getChoices() ,'data'=> $inscripcion->getDificultadAprendizaje(), 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('codigoPedagogico', 'text', array('label' => 'Codigo evaluacion','data'=> $inscripcion->getCodigoPedagogico(), 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('evaluacionPedagogica', 'text', array('label' => 'Evaluacion pedagogica','data'=> $inscripcion->getEvaluacionPedagogica(), 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('evaluacionUnidadEducativa', 'text', array('label' => 'Unidad edcuativa','data'=> $inscripcion->getEvaluacionUnidadEducativa(), 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('evaluacionEscolaridad', 'text', array('label' => 'Escolaridad','data'=> $inscripcion->getEvaluacionEscolaridad(), 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('parienteDiscapacidad', 'text', array('label' => 'Nombre pariente con discapacidad','data'=> $inscripcion->getSocioeconomicoEspecial()->getParienteDiscapacidad(), 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')));
                        	$form->add('gradoParentesco', 'choice', array('label' => 'Grado de parentesco','choices'=>GradoParentescoTipo::getChoices() ,'data'=> $inscripcion->getSocioeconomicoEspecial()->getGradoParentesco(), 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));

                        }
                        elseif ($institucionCurso->getEspecialAreaTipo()->getId() == 7) {
                        	$form->add('informePsicopedagogico', 'text', array('label' => 'Informe psicopedagogico','data'=> $inscripcion->getInformePsicopedagogicoTalento(), 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('codigoPsicopedagogico', 'text', array('label' => 'Codigo informe','data'=> $inscripcion->getCodigoPsicopedagogico(), 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('deteccion', 'choice', array('label' => 'Detectado en','choices'=>DeteccionTipo::getChoices() ,'data'=> $inscripcion->getDeteccionTalento(), 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('gradoTalento', 'choice', array('label' => 'Grado de talento','choices'=>GradoTalentoTipo::getChoices() ,'data'=> $inscripcion->getGradoTalento(), 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('talento', 'choice', array('label' => 'Tipo de talento extraordinario','choices'=>TalentoTipo::getChoices() ,'data'=> $inscripcion->getTipoTalento(), 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));

                        }
                        else {
                        	$form->add('discapacidadEspecial', 'choice', array('label' => 'Area de discapacidad','choices'=>$discapacidadArray,'data'=> $inscripcion->getDiscapacidad() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('gradoDiscapacidad', 'choice', array('label' => 'Grado de discapacidad','choices'=>$gradosDiscapacidadArray ,'data'=> $inscripcion->getGradoDiscapacidadTipo() ? $inscripcion->getGradoDiscapacidadTipo()->getId() : '', 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('origenDiscapacidad', 'choice', array('label' => 'Discapacidad que presenta es','choices'=>OrigenDiscapacidadTipo::getChoices(),'data'=> $inscripcion->getTipoDiscapacidad() , 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));
                        	$form->add('parienteDiscapacidad', 'text', array('label' => 'Nombre pariente con discapacidad','data'=> $inscripcion->getSocioeconomicoEspecial()->getParienteDiscapacidad(), 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off','maxlength'=>'45')));
                        	$form->add('gradoParentesco', 'choice', array('label' => 'Grado de parentesco','choices'=>GradoParentescoTipo::getChoices() ,'data'=> $inscripcion->getSocioeconomicoEspecial()->getGradoParentesco(), 'empty_value' => 'Seleccionar...', 'required' => false, 'attr' => array('class' => 'form-control')));

                        }

                        	return $form->getForm();
    }

    public function updateAction(Request $request){
        try{

            $form = $request->get('form');
            $em = $this->getDoctrine()->getManager();
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionEspecial')->find($form['idInscripcion']);

            /* Obteniendo el id del curso */

            //dump($form);die;

            $query = $em->createQuery(
            		'SELECT iec
                FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
        		JOIN iec.institucioneducativaCurso ie
                WHERE ie.institucioneducativa = :id
                AND ie.gestionTipo = :gestion
                AND ie.turnoTipo = :turno
        		AND iec.especialAreaTipo = :area
                AND ie.nivelTipo = :nivel
                AND ie.gradoTipo = :grado
                AND iec.especialServicioTipo = :servicio
                AND iec.especialProgramaTipo = :programa
                AND ie.paraleloTipo = :paralelo')
                            ->setParameter('id',$form['idInstitucion'])
                            ->setParameter('gestion',$form['gestion'])
                            ->setParameter('turno',$form['turno'])
                            ->setParameter('area',$form['idArea'])
                            ->setParameter('nivel',$form['nivel'])
                            ->setParameter('grado',$form['grado'])
                            ->setParameter('servicio',$form['servicio'])
                            ->setParameter('programa',$form['programa'])
                            ->setParameter('paralelo',$form['paralelo']);
                            $institucionCurso = $query->getSingleResult();
            //dump($institucionCurso);die;

            /**
             * Si no existe el curso seleccionado, redireccionamos y creamos el mensaje de error
             */
            if(!$institucionCurso){
                $this->get('session')->getFlashBag()->add('updateError', 'No se pudieron modificar los datos, intentelo nuevamente.');
                return $this->redirect($this->generateUrl('estudianteinscripcion_especial',array('op'=>'result')));
            }

            //Si existe un cambio en los atos de inscripción
            if($form['idCurso'] != $institucionCurso->getId())
            {
                //Actualizar InstitucioneducativaCursoEspecial en EstudianteInscripcion
                $inscripcion->setInstitucioneducativaCursoEspecial($institucionCurso);

                //Eliminar calificaciones y Asignaturas
                $objEstAsig = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion' => $form['idInscripcion'], 'gestionTipo' => $form['gestion']));
                if($objEstAsig){
                    foreach ($objEstAsig as $elementAsig) {
                         $objEstAsigNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura' => $elementAsig));
                         if($objEstAsigNota){
                            foreach ($objEstAsigNota as $elementNota) {
                                $em->remove($elementNota);
                            }
                         }
                        $em->remove($elementAsig);
                    }
                    $em->flush();
                }

            }


//             $inscripcion->setModalidadAtencion($form['modalidadAtencion']);


            	if ($inscripcion->getSocioeconomicoEspecial()->getId()>0) {
            		$socioeconomico = $em->getRepository('SieAppWebBundle:SocioeconomicoEspecial')->findOneById($inscripcion->getSocioeconomicoEspecial()->getId());
            	}
            	else {
            		$socioeconomico = new SocioeconomicoEspecial();
            		$socioeconomico->setDomicilioDepartamento($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['domicilioDepartamento']));
            		$socioeconomico->setDomicilioProvincia($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['domicilioProvincia']));
            		$socioeconomico->setDomicilioMunicipio($form['domicilioMunicipio']);
            		$socioeconomico->setDomicilioLocalidad($form['domicilioLocalidad']);
            		$socioeconomico->setDireccionZona($form['direccionZona']);
            		$socioeconomico->setDireccionCalle($form['direccionCalle']);
            		$socioeconomico->setDireccionNro($form['direccionNro']);
            		$socioeconomico->setDireccionTelefono($form['direccionTelefono']);
            		$socioeconomico->setDireccionCelular($form['direccionCelular']);
            		$socioeconomico->setIdioma1Tipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['idiomaTipo1Id']));
            		$socioeconomico->setIdioma2Tipo(($form['idiomaTipo2Id'] != "") ? $em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['idiomaTipo2Id']) : null);
            		$socioeconomico->setIdioma3Tipo(($form['idiomaTipo3Id'] != "") ? $em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['idiomaTipo3Id']) : null);
            		$socioeconomico->setEtniaTipo($em->getRepository('SieAppWebBundle:EtniaTipo')->findOneById($form['etniaTipoId']));
            		$socioeconomico->setViveCon($form['viveCon']);
            		$socioeconomico->setSeguro($form['seguro']);
            		$socioeconomico->setMedicacion($form['medicacion']);
            		$socioeconomico->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById($form['sangreTipoId']));

            		$em->persist($socioeconomico);
            		$em->flush();

            		$inscripcion->setSocioeconomicoEspecial($socioeconomico);
            		$em->flush();
            	}



            if ($form['idArea'] != 6 and $form['idArea'] != 7) {
	            $inscripcion->setDiscapacidad($form['discapacidadEspecial']);
	            $inscripcion->setGradoDiscapacidadTipo($em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->findOneById($form['gradoDiscapacidad']));
	            $inscripcion->setTipoDiscapacidad($form['origenDiscapacidad']);
	            $inscripcion->getSocioeconomicoEspecial()->setParienteDiscapacidad($form['parienteDiscapacidad']);
	            $inscripcion->getSocioeconomicoEspecial()->setGradoParentesco($form['gradoParentesco']);
            }
            if ($form['idArea'] == 6 ) {

	            $inscripcion->setDificultadAprendizaje($form['dificultadAprendizaje']);
	            $inscripcion->setCodigoPedagogico($form['codigoPedagogico']);
	            $inscripcion->setEvaluacionPedagogica($form['evaluacionPedagogica']);
	            $inscripcion->setEvaluacionUnidadEducativa($form['evaluacionUnidadEducativa']);
	            $inscripcion->setEvaluacionEscolaridad($form['evaluacionEscolaridad']);
	            $inscripcion->getSocioeconomicoEspecial()->setParienteDiscapacidad($form['parienteDiscapacidad']);
	            $inscripcion->getSocioeconomicoEspecial()->setGradoParentesco($form['gradoParentesco']);

            }
            if ($form['idArea'] == 7 ) {
	            $inscripcion->setInformePsicopedagogicoTalento($form['informePsicopedagogico']);
	            $inscripcion->setCodigoPsicopedagogico($form['codigoPsicopedagogico']);
	            $inscripcion->setDeteccionTalento($form['deteccion']);
	            $inscripcion->setGradoTalento($form['gradoTalento']);
	            $inscripcion->setTipoTalento($form['talento']);
            }

            $inscripcion->getSocioeconomicoEspecial()->setDomicilioDepartamento($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['domicilioDepartamento']));
            $inscripcion->getSocioeconomicoEspecial()->setDomicilioProvincia($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['domicilioProvincia']));
            $inscripcion->getSocioeconomicoEspecial()->setDomicilioMunicipio($form['domicilioMunicipio']);
            $inscripcion->getSocioeconomicoEspecial()->setDomicilioLocalidad($form['domicilioLocalidad']);
            $inscripcion->getSocioeconomicoEspecial()->setDireccionZona($form['direccionZona']);
            $inscripcion->getSocioeconomicoEspecial()->setDireccionCalle($form['direccionCalle']);
            $inscripcion->getSocioeconomicoEspecial()->setDireccionNro($form['direccionNro']);
            $inscripcion->getSocioeconomicoEspecial()->setDireccionTelefono($form['direccionTelefono']);
            $inscripcion->getSocioeconomicoEspecial()->setDireccionCelular($form['direccionCelular']);
            $inscripcion->getSocioeconomicoEspecial()->setIdioma1Tipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['idiomaTipo1Id']));
            $inscripcion->getSocioeconomicoEspecial()->setIdioma2Tipo(($form['idiomaTipo2Id']!="") ? $em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['idiomaTipo2Id']) : null);
            $inscripcion->getSocioeconomicoEspecial()->setIdioma3Tipo(($form['idiomaTipo3Id']!="") ? $em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['idiomaTipo3Id']) : null);
            $inscripcion->getSocioeconomicoEspecial()->setEtniaTipo($em->getRepository('SieAppWebBundle:EtniaTipo')->findOneById($form['etniaTipoId']));
            $inscripcion->getSocioeconomicoEspecial()->setViveCon($form['viveCon']);
            $inscripcion->getSocioeconomicoEspecial()->setSeguro($form['seguro']);
            $inscripcion->getSocioeconomicoEspecial()->setMedicacion($form['medicacion']);
            $inscripcion->getSocioeconomicoEspecial()->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById($form['sangreTipoId']));

            $em->flush();


            $this->get('session')->getFlashBag()->add('updateOk', 'El registro fue modificado correctamente');
            return $this->redirect($this->generateUrl('estudianteinscripcion_especial',array('op'=>'result')));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('updateError', 'Error al modificar el registro');
            return $this->redirect($this->generateUrl('estudianteinscripcion_especial',array('op'=>'result')));
        }
    }

    public function deleteAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionEspecial')->findOneById($request->get('idInscripcion'));
            $inscripcionsie = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcion->getEstudianteInscripcion()->getId());

            $em->remove($inscripcion);
            $em->flush();
            $em->remove($inscripcionsie);
            $em->flush();


            /*
            * verificamos si existen estudiante inscritos en el curso anterior del estudiante
            * si no hay estudiantes inscritos eliminamos el curso
            */
//             $estudiantesAnteriorCurso = $em->getRepository('SieAppWebBundle:EstudianteInscripcionEspecial')->findBy(array('institucioneducativaCursoEspecial'=>$inscripcion->getInstitucioneducativaCursoEspecial()));
//             if(!$estudiantesAnteriorCurso){
//                 $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array('institucioneducativa'=>$inscripcion->getInstitucioneducativa(),'gestionTipo'=>$inscripcion->getGestionTipo(),'areaEspecialTipo'=>$inscripcion->getAreaEspecialTipo(),'nivelTipo'=>$inscripcion->getNivelTipo(),'gradoTipo'=>$inscripcion->getGradoTipo(),'paraleloTipo'=>$inscripcion->getParaleloTipo()));
//                 $em->remove($institucionCurso);
//                 $em->flush();
//             }

            /*
             * verificamos si existen estudiante inscritos asociados a datos socioeconomicos de la inscripcion que se elimina
             * si no hay mas isncritos se elimina la parte socioeconomica
             */
            $inscripcionesSocioeconomico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionEspecial')->findOneBySocioeconomicoEspecial($inscripcion->getSocioeconomicoEspecial()->getId());
            if(!$inscripcionesSocioeconomico){
            	$socioeconomicoEspecial = $em->getRepository('SieAppWebBundle:SocioeconomicoEspecial')->findOneById($inscripcion->getSocioeconomicoEspecial()->getId());
            	$em->remove($socioeconomicoEspecial);
            	$em->flush();
            }


            $this->get('session')->getFlashBag()->add('deleteOk', 'El registro fue eliminado exitosamente');
            return $this->redirect($this->generateUrl('estudianteinscripcion_especial',array('op'=>'result')));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('deleteError', 'Error al eliminar el registro');
            return $this->redirect($this->generateUrl('estudianteinscripcion_especial'));
        }
    }

    public function listargradosAction($nivel) {
        $em = $this->getDoctrine()->getManager();
        //$dep = $em->getRepository('SieAppWebBundle:GradoTipo')->findAll();
        if ($nivel == 11) {
            $query = $em->createQuery(
                            'SELECT gt
                            FROM SieAppWebBundle:GradoTipo gt
                            WHERE gt.id IN (:id)
                            ORDER BY gt.id ASC'
                    )->setParameter('id', array(1, 2));
        } else {
            $query = $em->createQuery(
                            'SELECT gt
                            FROM SieAppWebBundle:GradoTipo gt
                            WHERE gt.id IN (:id)
                            ORDER BY gt.id ASC'
                    )->setParameter('id', array(1, 2, 3, 4, 5, 6));
        }
        $gra = $query->getResult();
        $lista = array();
        foreach ($gra as $gr) {
            $lista[$gr->getId()] = $gr->getGrado();
        }
        $list = $lista;
        $response = new JsonResponse();
        return $response->setData(array('listagrados' => $list));
    }

    public function buscarestudiantesinstitucionAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                        'SELECT ei, e
                    FROM SieAppWebBundle:EstudianteInscripcion ei
                    JOIN ei.estudiante e')
                ->setMaxResults(100);
        $estudiantes = $query->getResult();

        $lista = array();
        foreach ($estudiantes as $est) {
            $lista[] = $est->getEstudiante()->getCodigoRude();
        }
        $list = $lista;
        //print_r($list);die;
        $response = new JsonResponse();
        return $response->setData($list);
    }

    /**
     * Funciones para cargar los turnos de la unidad educativa
     */
    public function cargarTurnosAction($idInstitucion,$gestion,$area){
    	$em = $this->getDoctrine()->getManager();
    	$query = $em->createQuery(
    			'SELECT DISTINCT tt.id,tt.turno
                FROM SieAppWebBundle:InstitucioneducativaCurso iec
                JOIN iec.institucioneducativaCurso ie
                JOIN ie.turnoTipo tt
                WHERE ie.institucioneducativa = :id
    			AND iec.especialAreaTipo = : area
                AND ie.gestionTipo = :gestion
                ORDER BY tt.id')
                    ->setParameter('id',$idInstitucion)
                    ->setParameter('area',$area)
                    ->setParameter('gestion',$gestion);
                    $turnos = $query->getResult();
                    $turnosArray = array();
                    for($i=0;$i<count($turnos);$i++){
                    	$turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
                    }
                    $response = new JsonResponse();
                    return $response->setData(array('turnos' => $turnosArray));
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function cargarAreasAction($idInstitucion,$gestion){
    	$em = $this->getDoctrine()->getManager();
    	$query = $em->createQuery(
    			'SELECT DISTINCT ae.id,ae.areaEspecial
                FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
                JOIN iec.especialAreaTipo ae
    			JOIN iec.institucioneducativaCurso ie
                WHERE ie.institucioneducativa = :id
                AND ie.gestionTipo = :gestion
                ORDER BY ae.id')
                    ->setParameter('id',$idInstitucion)
                    ->setParameter('gestion',$gestion);
                    $areas = $query->getResult();
                    $areasArray = array();
                    for($i=0;$i<count($areas);$i++){
                    	$areasArray[$areas[$i]['id']] = $areas[$i]['areaEspecial'];
                    }
                    //print_r($nivelesArray);die;
                    $response = new JsonResponse();
                    return $response->setData(array('areas' => $areasArray));
    }
    public function cargarNivelesAction($idInstitucion,$gestion,$turno,$area){
    	$em = $this->getDoctrine()->getManager();
    	$query = $em->createQuery(
    			'SELECT DISTINCT nt.id,nt.nivel
                FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
                JOIN iec.nivelTipo nt
    			JOIN iec.institucioneducativaCurso ie
                WHERE ie.institucioneducativa = :id
                AND ie.gestionTipo = :gestion
                AND ie.turnoTipo = :turno
                AND iec.especialAreaTipo = :area
    			ORDER BY nt.id')
                    ->setParameter('id',$idInstitucion)
                    ->setParameter('gestion',$gestion)
                    ->setParameter('turno',$turno)
                    ->setParameter('area',$area);

                    $niveles = $query->getResult();
                    $nivelesArray = array();
                    for($i=0;$i<count($niveles);$i++){
                    	$nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
                    }
                    //print_r($nivelesArray);die;
                    $response = new JsonResponse();
                    return $response->setData(array('niveles' => $nivelesArray));
    }
    public function cargarGradosAction($idInstitucion,$gestion,$turno,$area,$nivel){
    	$em = $this->getDoctrine()->getManager();
    	$query = $em->createQuery(
    			'SELECT DISTINCT gt.id,gt.grado
                FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
                JOIN iec.institucioneducativaCurso ie
                JOIN ie.gradoTipo gt
                WHERE ie.institucioneducativa = :id
                AND ie.gestionTipo = :gestion
                AND ie.turnoTipo = :turno
                AND iec.especialAreaTipo = :area
    			AND ie.nivelTipo = :nivel
                ORDER BY gt.id')
                    ->setParameter('id',$idInstitucion)
                    ->setParameter('gestion',$gestion)
                    ->setParameter('turno',$turno)
                    ->setParameter('area',$area)
                    ->setParameter('nivel',$nivel);
                    $grados = $query->getResult();
                    $gradosArray = array();
                    for($i=0;$i<count($grados);$i++){
                    	$gradosArray[$grados[$i]['id']] = $grados[$i]['grado'];
                    }
                    //print_r($nivelesArray);die;
                    $response = new JsonResponse();
                    return $response->setData(array('grados' => $gradosArray));
    }
    public function cargarServiciosAction($idInstitucion,$gestion,$turno,$area,$nivel,$grado){
    	$em = $this->getDoctrine()->getManager();
    	$query = $em->createQuery(
    			'SELECT DISTINCT st.id,st.servicio
                FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
                JOIN iec.especialServicioTipo st
    			JOIN iec.institucioneducativaCurso ie
                WHERE ie.institucioneducativa = :id
                AND ie.gestionTipo = :gestion
                AND ie.turnoTipo = :turno
                AND iec.especialAreaTipo = :area
    			AND ie.nivelTipo = :nivel
    			AND ie.gradoTipo = :grado
                ORDER BY st.id')
                    ->setParameter('id',$idInstitucion)
                    ->setParameter('gestion',$gestion)
                    ->setParameter('turno',$turno)
                    ->setParameter('area',$area)
                    ->setParameter('nivel',$nivel)
                    ->setParameter('grado',$grado);
                    $servicios = $query->getResult();
                    $serviciosArray = array();
                    for($i=0;$i<count($servicios);$i++){
                    	$serviciosArray[$servicios[$i]['id']] = $servicios[$i]['servicio'];
                    }
                    //print_r($nivelesArray);die;
                    $response = new JsonResponse();
                    return $response->setData(array('servicios' => $serviciosArray));
    }

    public function cargarProgramasAction($idInstitucion,$gestion,$turno,$area,$nivel,$grado,$servicio){
    	$em = $this->getDoctrine()->getManager();
    	$query = $em->createQuery(
    			'SELECT DISTINCT pt.id,pt.programa
                FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
                JOIN iec.especialProgramaTipo pt
    			JOIN iec.institucioneducativaCurso ie
                WHERE ie.institucioneducativa = :id
                AND ie.gestionTipo = :gestion
                AND ie.turnoTipo = :turno
                AND iec.especialAreaTipo = :area
    			AND ie.nivelTipo = :nivel
    			AND ie.gradoTipo = :grado
    			AND iec.especialServicioTipo = :servicio
    			ORDER BY pt.id')
                    ->setParameter('id',$idInstitucion)
                    ->setParameter('gestion',$gestion)
                    ->setParameter('turno',$turno)
                    ->setParameter('area',$area)
                    ->setParameter('nivel',$nivel)
                    ->setParameter('grado',$grado)
                    ->setParameter('servicio',$servicio);
                    $programas = $query->getResult();
                    $programasArray = array();
                    for($i=0;$i<count($programas);$i++){
                    	$programasArray[$programas[$i]['id']] = $programas[$i]['programa'];
                    }
                    //print_r($nivelesArray);die;
                    $response = new JsonResponse();
                    return $response->setData(array('programas' => $programasArray));
    }

    public function cargarParalelosAction($idInstitucion,$gestion,$turno,$area,$nivel,$grado,$servicio,$programa){
    	$em = $this->getDoctrine()->getManager();
    	$query = $em->createQuery(
    			'SELECT DISTINCT pt.id,pt.paralelo
                FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
    			JOIN iec.institucioneducativaCurso ie
                JOIN ie.paraleloTipo pt
                WHERE ie.institucioneducativa = :id
                AND ie.gestionTipo = :gestion
                AND ie.turnoTipo = :turno
                AND iec.especialAreaTipo = :area
    			AND ie.nivelTipo = :nivel
                AND ie.gradoTipo = :grado
                AND iec.especialServicioTipo = :servicio
                AND iec.especialProgramaTipo = :programa
    			ORDER BY pt.id')
                    ->setParameter('id',$idInstitucion)
                    ->setParameter('gestion',$gestion)
                    ->setParameter('turno',$turno)
                    ->setParameter('area',$area)
                    ->setParameter('nivel',$nivel)
                    ->setParameter('grado',$grado)
                    ->setParameter('servicio',$servicio)
                    ->setParameter('programa',$programa);
                    $paralelos = $query->getResult();
                    $paralelosArray = array();
                    for($i=0;$i<count($paralelos);$i++){
                    	$paralelosArray[$paralelos[$i]['id']] = $paralelos[$i]['paralelo'];
                    }
                    //print_r($nivelesArray);die;
                    $response = new JsonResponse();
                    return $response->setData(array('paralelos' => $paralelosArray));
    }


    public function buscarEstudianteAction($id,$gestion) {
    	//echo "dfsd";die;

    	$em = $this->getDoctrine()->getManager();
    	$query = $em->createQuery(
    			'SELECT est
                FROM SieAppWebBundle:Estudiante est
                WHERE est.codigoRude = :codigo')
    	    			->setParameter('codigo',$id);
    	$estudiante = $query->getSingleResult();


//     	$estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneByCodigoRude($id);

    	$paterno = ($estudiante) ? $estudiante->getPaterno() : "";
    	$materno = ($estudiante) ? $estudiante->getMaterno() : "";
    	$nombre = ($estudiante) ? $estudiante->getNombre() : "";
    	$carnet = ($estudiante) ? $estudiante->getCarnetIdentidad() : "";
    	$codigo = ($estudiante) ? $estudiante->getCodigoRude() : "";
    	$celular = ($estudiante) ? $estudiante->getCelular() : "";
    	$fechaNacimiento = ($estudiante) ? $estudiante->getFechaNacimiento()->format('d-m-Y') : "";

    	$oficialia = ($estudiante) ? $estudiante->getOficialia() : "";
    	$libro = ($estudiante) ? $estudiante->getLibro() : "";
    	$partida = ($estudiante) ? $estudiante->getPartida() : "";
    	$folio = ($estudiante) ? $estudiante->getFolio() : "";
    	$pais = ($estudiante) ? $estudiante->getPaisTipo()->getPais() : "";
    	$departamento = ($estudiante->getLugarNacTipo()) ? $estudiante->getLugarNacTipo()->getLugar() : "";
    	$provincia = ($estudiante->getLugarProvNacTipo()) ? $estudiante->getLugarProvNacTipo()->getLugar() : "";
    	$localidad = ($estudiante) ? $estudiante->getLocalidadNac() : "";
    	$carnetCodepedis = ($estudiante) ? $estudiante->getCarnetCodepedis() : "";
    	$carnetIbc = ($estudiante) ? $estudiante->getCarnetIbc() : "";
    	$genero = ($estudiante) ? $estudiante->getGeneroTipo()->getGenero() : "";




        if ($estudiante) {
        	$query = $em->createQuery(
        		'SELECT DISTINCT so.id
                FROM SieAppWebBundle:EstudianteInscripcionEspecial ei
        		JOIN ei.estudianteInscripcion i
                JOIN ei.institucioneducativaCursoEspecial iec
        		JOIN ei.socioeconomicoEspecial so
        		JOIN iec.institucioneducativaCurso ie
                WHERE i.estudiante = :id
        		AND ie.gestionTipo = :gestion')
        	                ->setParameter('id',$estudiante->getId())
        	                ->setParameter('gestion',$gestion);
        	                $socioeconomico = $query->getResult();
        	    if ($socioeconomico) {
        	    	$socioeconomicoId = $em->getRepository('SieAppWebBundle:SocioeconomicoEspecial')->findOneById($socioeconomico[0]['id']);
	        	    $domicilioDepartamento = ($socioeconomicoId) ? $socioeconomicoId->getDomicilioDepartamento()->getId() : "";
	        	    $domicilioProvincia = ($socioeconomicoId) ? $socioeconomicoId->getDomicilioProvincia()->getId() : "";
	        	    $domicilioMunicipio = ($socioeconomicoId) ? $socioeconomicoId->getDomicilioMunicipio() : "";
	        	    $domicilioLocalidad = ($socioeconomicoId) ? $socioeconomicoId->getDomicilioLocalidad() : "";
	        	    $direccionZona = ($socioeconomicoId) ? $socioeconomicoId->getDireccionZona() : "";
	        	    $direccionCalle = ($socioeconomicoId) ? $socioeconomicoId->getDireccionCalle() : "";
	        	    $direccionNro = ($socioeconomicoId) ? $socioeconomicoId->getDireccionNro() : "";
	        	    $direccionTelefono = ($socioeconomicoId) ? $socioeconomicoId->getDireccionTelefono() : "";
	        	    $direccionCelular = ($socioeconomicoId) ? $socioeconomicoId->getDireccionCelular() : "";
	        	    $direccionIdioma1 = ($socioeconomicoId->getIdioma1Tipo()) ? $socioeconomicoId->getIdioma1Tipo()->getId() : "";
	        	    $direccionIdioma2 = ($socioeconomicoId->getIdioma2Tipo()) ? $socioeconomicoId->getIdioma2Tipo()->getId() : "";
	        	    $direccionIdioma3 = ($socioeconomicoId->getIdioma3Tipo()) ? $socioeconomicoId->getIdioma3Tipo()->getId() : "";
	        	    $etniaTipo = ($socioeconomicoId) ? $socioeconomicoId->getEtniaTipo()->getId() : "";
	        	    $viveCon = ($socioeconomicoId) ? $socioeconomicoId->getViveCon() : "";
	        	    $parienteDiscapacidad = ($socioeconomicoId) ? $socioeconomicoId->getParienteDiscapacidad() : "";
	        	    $gradoParentesco = ($socioeconomicoId) ? $socioeconomicoId->getGradoParentesco() : "";
	        	    $seguro = ($socioeconomicoId) ? $socioeconomicoId->getSeguro() : "";
	        	    $medicacion = ($socioeconomicoId) ? $socioeconomicoId->getMedicacion() : "";
	        	    $sangreTipo = ($socioeconomicoId) ? $socioeconomicoId->getSangreTipo()->getId() : "";
        	    }
        	    else {
	        	    $domicilioDepartamento = "";
	        	    $domicilioProvincia = "";
	        	    $domicilioMunicipio = "";
	        	    $domicilioLocalidad = "";
	        	    $direccionZona = "";
	        	    $direccionCalle = "";
	        	    $direccionNro = "";
	        	    $direccionTelefono = "";
	        	    $direccionCelular = "";
	        	    $direccionIdioma1 = "";
	        	    $direccionIdioma2 = "";
	        	    $direccionIdioma3 = "";
	        	    $etniaTipo = "";
	        	    $viveCon = "";
	        	    $parienteDiscapacidad = "";
	        	    $gradoParentesco = "";
	        	    $seguro = "";
	        	    $medicacion = "";
	        	    $sangreTipo = "";
        	    }



        }
        else {
        	    $domicilioDepartamento = "";
        	    $domicilioProvincia = "";
        	    $domicilioMunicipio = "";
        	    $domicilioLocalidad = "";
        	    $direccionZona = "";
        	    $direccionCalle = "";
        	    $direccionNro = "";
        	    $direccionTelefono = "";
        	    $direccionCelular = "";
        	    $direccionIdioma1 = "";
        	    $direccionIdioma2 = "";
        	    $direccionIdioma3 = "";
        	    $etniaTipo = "";
        	    $viveCon = "";
        	    $parienteDiscapacidad = "";
        	    $gradoParentesco = "";
        	    $seguro = "";
        	    $medicacion = "";
        	    $sangreTipo = "";
        }



//     	$direccionZona = ("XX");


    	$response = new JsonResponse();
    	return $response->setData(array(
    			'paterno' => $paterno,
    			'materno' => $materno,
    			'nombre' => $nombre,
    			'carnet' => $carnet,
    			'codigo' => $codigo,
    			'celular'=>$celular,
    			'fechaNacimiento' => $fechaNacimiento,
    			'oficialia' => $oficialia ,
    			'libro' => $libro,
    			'partida' => $partida,
    			'folio' => $folio,
    			'pais' => $pais,
    			'departamento' => $departamento,
    			'provincia' => $provincia,
    			'localidad' => $localidad,
    			'carnetCodepedis' => $carnetCodepedis,
    			'carnetIbc' => $carnetIbc,
    			'genero' => $genero,

    			'domicilioDepartamento' => $domicilioDepartamento,
    			'domicilioProvincia' => $domicilioProvincia,
    			'domicilioMunicipio' => $domicilioMunicipio,
    			'domicilioLocalidad' => $domicilioLocalidad,
    			'direccionZona' => $direccionZona,
    			'direccionCalle' => $direccionCalle,
    			'direccionNro' => $direccionNro,
    			'direccionTelefono' => $direccionTelefono,
    			'direccionCelular' => $direccionCelular,
    			'direccionIdioma1' => $direccionIdioma1,
    			'direccionIdioma2' => $direccionIdioma2,
    			'direccionIdioma3' => $direccionIdioma3,
    			'etniaTipo' => $etniaTipo,
    			'viveCon' => $viveCon,
    			'parienteDiscapacidad' => $parienteDiscapacidad,
    			'gradoParentesco' => $gradoParentesco,
    			'seguro' => $seguro,
    			'medicacion' => $medicacion,
    			'sangreTipo' => $sangreTipo
    	));
    }

    /**
     * carga grados de discapacidad de la discapacidad del estudiante
     */

    public function cargarGradosDiscapacidadAction($discapacidad){
    	$em = $this->getDoctrine()->getManager();

    	if ($discapacidad == "AUDITIVA") {
    	    		$query = $em->createQuery(
    	    				'SELECT DISTINCT gt.id,gt.gradoDiscapacidad
    	                FROM SieAppWebBundle:GradoDiscapacidadTipo gt
    	                WHERE gt.id IN (:id)
    	                ORDER BY gt.id')
    	    		                ->setParameter('id',array(1,2,3,4));
    	}
    	elseif ($discapacidad == "VISUAL") {
    		$query = $em->createQuery(
    				'SELECT DISTINCT gt.id,gt.gradoDiscapacidad
    	                FROM SieAppWebBundle:GradoDiscapacidadTipo gt
    	                WHERE gt.id IN (:id)
    	                ORDER BY gt.id')
    	                ->setParameter('id',array(5,6));

    	}
    	elseif ($discapacidad == "INTELECTUAL" or $discapacidad == "SINDROME DE DOWN" or $discapacidad == "FISICO-MOTORA") {
    		$query = $em->createQuery(
    				'SELECT DISTINCT gt.id,gt.gradoDiscapacidad
    	                FROM SieAppWebBundle:GradoDiscapacidadTipo gt
    	                WHERE gt.id IN (:id)
    	                ORDER BY gt.id')
    	                ->setParameter('id',array(1,2,7,8));

    	}
    	elseif ($discapacidad == "AUTISMO") {
    		$query = $em->createQuery(
    				'SELECT DISTINCT gt.id,gt.gradoDiscapacidad
    	                FROM SieAppWebBundle:GradoDiscapacidadTipo gt
    	                WHERE gt.id IN (:id)
    	                ORDER BY gt.id')
    	                ->setParameter('id',array(9,10,11,12));
    	}
    	elseif ($discapacidad == "MULTIPLE" or $discapacidad == "SORDOCEGUERA") {
    		$query = $em->createQuery(
    				'SELECT DISTINCT gt.id,gt.gradoDiscapacidad
    	                FROM SieAppWebBundle:GradoDiscapacidadTipo gt
    	                WHERE gt.id IN (:id)
    	                ORDER BY gt.id')
    	                ->setParameter('id',array(7,8));
    	}
    	else  {
    		$query = $em->createQuery(
    				'SELECT DISTINCT gt.id,gt.gradoDiscapacidad
    	                FROM SieAppWebBundle:GradoDiscapacidadTipo gt
    	                WHERE gt.id IN (:id)
    	                ORDER BY gt.id')
    	                ->setParameter('id',array(0));
    	}


        $grados = $query->getResult();
        $gradosArray = array();
        for($i=0;$i<count($grados);$i++){
           	$gradosArray[$grados[$i]['id']] = $grados[$i]['gradoDiscapacidad'];
        }
        $response = new JsonResponse();
        return $response->setData(array('grados' => $gradosArray));
    }

}
