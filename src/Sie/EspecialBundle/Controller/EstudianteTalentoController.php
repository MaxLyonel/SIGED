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
 * EstudianteTalento controller.
 *
 */
class EstudianteTalentoController extends Controller {

    public $session;
    public $idInstitucion;
    /**
     * Lista de estudiantes registrados para talento extraordinario
     *
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT * FROM estudiante_inscripcion_especial_talento');
        $query->execute();
        $estudiantes = $query->fetchAll();
        return $this->render('SieEspecialBundle:EstudianteTalento:index.html.twig', array('estudiantes' => $estudiantes));
    }

    public function dtlistAction(Request $request){
        $estudiantes = array(
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => true, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12')
        );
        return $this->render('SieEspecialBundle:EstudianteTalento:dt_list.html.twig', array(
            'estudiantes' => $estudiantes,
        ));
    }

    public function newAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT * FROM estudiante_inscripcion_especial_talento');
        $query->execute();
        $estudiantes = $query->fetchAll();
        return $this->render('SieEspecialBundle:EstudianteTalento:new.html.twig', array('estudiantes' => $estudiantes));
    }

    public function searchStudentAction(Request $request) {
        $rude = trim($request->get('rude'));
        $em = $this->getDoctrine()->getManager();
        $estudiante_result = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
        $estudiante = array();
        if (!empty($estudiante_result)){
            $einscripcion_result = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $estudiante_result));
            if (!empty($einscripcion_result)){g
                $iecurso_result = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('id' => $institucioneducativa_curso_id));
                dump($iecurso_result);die;
                $estudiante = array(
                    'id' => $estudiante_result->getId(),
                    'nombre' => $estudiante_result->getNombre(),
                    'paterno' => $estudiante_result->getPaterno(),
                    'materno' => $estudiante_result->getMaterno(),
                    'cedula' => $estudiante_result->getCarnetIdentidad(),
                    'complemento' => $estudiante_result->getComplemento(),
                    'fecha_nacimiento' => $estudiante_result->getFechaNacimiento(),
                    'estudiante_ins_id' => $einscripcion_result->getId(),
                );
            } else {
                $msg = 'noestins';
            }
        } else {
            $msg = 'noest';
        }
//        dump($estudiante->getId());die;
        /*$query = $em->getConnection()->prepare('SELECT id, nombre, paterno, materno, carnet_identidad, complemento, fecha_nacimiento FROM estudiante WHERE codigo_rude = :rude');
        $query->bindParam('rude', $rude);
        $query->execute();
//        $estudiante = $query->fetch();*/

        /*$query = $em->getConnection()->prepare('SELECT id FROM estudiante_inscripcion_especial_talento');
        $query->execute();*/
        //$estudiante = $query->fetch();
        $response = new JsonResponse();
//        $em->getConnection()->commit();
        return $response->setData(array('msg' => $msg, 'estudiante' => $estudiante));
    }

    public function indexeAction(Request $request, $op) {
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
            return $this->render('SieEspecialBundle:EstudianteTalento:index.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }
    }

}
