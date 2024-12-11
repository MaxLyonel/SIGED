<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\RegistroConsolidacion;
use Sie\AppWebBundle\Entity\EstudianteInscripcionHumnisticoTecnico;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * InfoEstudianteAreasEstudiante controller.
 */
class InfoEstudianteAreasEstudianteController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * [indexAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function indexAction(Request $request){

        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');
        $infoStudent = json_decode($infoStudent, true);

        $idInscripcion = $infoStudent['eInsId'];

        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

        $areas = $this->get('areasEstudiante')->areasEstudiante($idInscripcion);

        return $this->render('SieHerramientaBundle:InfoEstudianteAreasEstudiante:index.html.twig',array(
                'areas'=>$areas,
                'inscripcion'=>$inscripcion
        ));
    }

    public function deleteAction(Request $request){
        $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
        $em = $this->getDoctrine()->getManager();
        $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura);
        
        $inscripcion = $estudianteAsignatura->getEstudianteInscripcion();
        $gestion = $estudianteAsignatura->getGestionTipo()->getId();
        $idInscripcion = $inscripcion->getId();
        
        $data = [];
        
        if($estudianteAsignatura){
            // VERIFICAMOS SI EL AREA NO TIENE NOTAS
            $notas = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura'=>$idEstudianteAsignatura));
            
            if($notas){
                if($this->session->get('roluser') == 8 or $gestion == 2020){
                    // ELIMINAMOS LA MATERIA AUN SI TIENE NOTAS SOLO PARA TECNICO NACIONAL

                    // ELIMINAMOS LA ESPECIALIDAD SI LA MATERIA ES TECNICA ESPECIALIZADA
                    $codigoAsignatura = $estudianteAsignatura->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId();
                    if($codigoAsignatura == 1039){
                        $eliminarEspecialidad = $em->createQueryBuilder()
                                        ->delete('')
                                        ->from('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico','eiht')
                                        ->where('eiht.estudianteInscripcion = :idEstudianteInscripcion')
                                        ->setParameter('idEstudianteInscripcion', $idInscripcion)
                                        ->getQuery()
                                        ->getResult();
                    }

                    // ELIMINAMOS LAS NOTAS
                    $eliminarNotas = $em->createQueryBuilder()
                                    ->delete('')
                                    ->from('SieAppWebBundle:EstudianteNota','en')
                                    ->where('en.estudianteAsignatura = :idEstudianteAsignatura')
                                    ->setParameter('idEstudianteAsignatura', $idEstudianteAsignatura)
                                    ->getQuery()
                                    ->getResult();
                    

                    $eliminar = $this->get('areasEstudiante')->delete($idEstudianteAsignatura);
                    if($eliminar){                                     
                        // SE ACTUALIZA EL ESTADO DE MATRICULA SI CORRESPONDE
                        $actualizarEstadoMatricula = $this->get('notas')->actualizarEstadoMatricula($idInscripcion);         
                        $data = array(
                            'status'=>200,
                            'type'=>'success',
                            'msg'=> 'Área  eliminada correctamente.'
                        );
                    }else{
                        $data = array(
                            'status'=>500,
                            'type'=>'danger',
                            'msg'=> 'Error al eliminar el área del estudiante.'
                        );
                    }

                }else{
                    // SI EL USUARIO NO ES NACIONAL NO SE PUEDE ELIMINAR
                    $data = array(
                        'status'=>500,
                        'type'=>'warning',
                        'msg'=> 'No se puede eliminar el área. porque ya cuenta con notas.'
                    );
                }
            }else{

                // ELIMINAMOS LA ESPECIALIDAD SI LA MATERIA ES TECNICA ESPECIALIZADA
                $codigoAsignatura = $estudianteAsignatura->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId();
                if($codigoAsignatura == 1039){
                    $eliminarEspecialidad = $em->createQueryBuilder()
                                    ->delete('')
                                    ->from('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico','eiht')
                                    ->where('eiht.estudianteInscripcion = :idEstudianteInscripcion')
                                    ->setParameter('idEstudianteInscripcion', $idInscripcion)
                                    ->getQuery()
                                    ->getResult();
                }

                $eliminar = $this->get('areasEstudiante')->delete($idEstudianteAsignatura);
                if($eliminar){
                    // SE ACTUALIZA EL ESTADO DE MATRICULA SI CORRESPONDE
                    $actualizarEstadoMatricula = $this->get('notas')->actualizarEstadoMatricula($idInscripcion);
                    $data = array(
                        'status'=>200,
                        'type'=>'success',
                        'msg'=> 'Área  eliminada correctamente.'
                    );
                }else{
                    $data = array(
                        'status'=>500,
                        'type'=>'danger',
                        'msg'=> 'Error al eliminar el área del estudiante.'
                    );
                }
            }

        }else{
            $data = array(
                'status'=>500,
                'type'=>'danger',
                'msg'=> 'Error al eliminar el área del estudiante.'
            );
        }

        $areas = $this->get('areasEstudiante')->areasEstudiante($idInscripcion);

        return $this->render('SieHerramientaBundle:InfoEstudianteAreasEstudiante:index.html.twig',array(
                'areas'=>$areas,
                'inscripcion'=>$inscripcion,
                'data'=>$data
        ));
    }

    public function newAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $idCursoOferta = $request->get('idco');
            $idInscripcion = $request->get('idInscripcion');

            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            
            $data = [];
            $areas = $this->get('areasEstudiante')->areasEstudiante($idInscripcion);

            // VERIFICAMOS SI LA MATERIA QUE SE QUIERE AGREGAR YA ESTA ASIGNADA AL ESTUDIANTE
            $area = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array(
                'estudianteInscripcion'=>$idInscripcion,
                'institucioneducativaCursoOferta'=>$idCursoOferta
            ));
            
            if($area){
                // SI EL ESTUDIANTE YA TIENE EL AREA
                $data = array(
                    'status'=>500,
                    'type'=>'warning',
                    'msg'=> 'El estudiante ya cuenta con el área.'
                );
            }else{

                //VERIFICAMOS SI LA ESPECIALIDAD YA FUE ELIMINADA PREVIAMENTE
                // SI ES ASI ENTONCES NO LE PERMITIMOS AGREGAR NUEVAMENTE LA ESPECIALIDAD
                $eliminado = $em->getRepository('SieAppWebBundle:BthEstudianteInscripcionGestionEspecialidad')->findOneBy(array(
                    'estudianteInscripcion'=>$idInscripcion,
                    'operativoGestionEspecialidadTipo'=>3
                ));
                if ($eliminado) {

                    $data = array(
                        'status'=>500,
                        'type'=>'warning',
                        'msg'=> 'No puede agregar la especialidad debido a que ya fue eliminada.'
                    );

                }else{

                    $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
                    $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
                    $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta);
                    $idAsignatura = $cursoOferta->getAsignaturaTipo()->getId();

                    // VERIFICAMOS SI LA MATERIA ES ESPECIALIZADA
                    $registrarEspecialidad = false;
                    $especialidadesUe = [];
                    if ($idAsignatura == 1039) {
                        $registrarEspecialidad = true;
                        $especialidadesUnidadEducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->findBy(array(
                            'institucioneducativa'=>$sie,
                            'gestionTipo'=>$gestion
                        ));
                        foreach ($especialidadesUnidadEducativa as $e) {
                            $especialidadesUe[] = array(
                                'ueespid'=>$e->getId().'_'.$idCursoOferta,
                                'especialidad'=>$e->getEspecialidadTecnicoHumanisticoTipo()->getEspecialidad()
                            );
                        }
                    }

                    // VERIFICAMOS SI SE DEBE LLENAR NOTAS
                    $llenarNotas = $this->get('notas')->llenarNotasMateriaAntes($idInscripcion, $idCursoOferta);

                    if ((count($llenarNotas['cuantitativas']) > 0 or count($llenarNotas['cualitativas']) > 0 ) or $registrarEspecialidad ){
                        
                        // SI SE TIENE QUE LLENAR NOTAS LE MANDAMOS UNA VISTA DONDE REGISTRE LAS NOTAS
                        return $this->render('SieHerramientaBundle:InfoEstudianteAreasEstudiante:completarNotas.html.twig',array(
                            'areas'=>$areas,
                            'inscripcion'=>$inscripcion,
                            'data'=>$llenarNotas,
                            'registrarEspecialidad'=>$registrarEspecialidad,
                            'especialidadesUe'=>$especialidadesUe,
                            'idCursoOferta'=>$idCursoOferta
                        ));
                    }else{
                        // SI NO HAY NOTAS QUE REGISTRAR, REGISTRAMOS LA MATERIA AL ESTUDIANTE
                        $nuevaArea = $this->get('areasEstudiante')->nuevo($idCursoOferta, $idInscripcion, $gestion);
                        if($nuevaArea){
                            // SI TODO SE REALIZO CON EXITO
                            $data = array(
                                'status'=>200,
                                'type'=>'success',
                                'msg'=> 'El área se agregó correctamente.'
                            );

                            $areas = $this->get('areasEstudiante')->areasEstudiante($idInscripcion);


                        }else{
                            // SI OCURRIO UN ERROR AL AGREGAR EL AREA
                            $data = array(
                                'status'=>500,
                                'type'=>'danger',
                                'msg'=> 'Ocurrió un error al agregar el área.'
                            );
                        }
                    }
                }
            }

            $em->getConnection()->commit();

            return $this->render('SieHerramientaBundle:InfoEstudianteAreasEstudiante:index.html.twig',array(
                    'areas'=>$areas,
                    'inscripcion'=>$inscripcion,
                    'data'=>$data
            ));

        } catch (Exception $e) {
            $em->getConnection()->rollback();
        }        
    }

    /**
     * Funcion para registrar la materia y las notas que faltan
     * @param  Request  $request [description]
     * @param  integer  $idIns              [id de inscripcion]
     * @param  array    $idInscripcion      [array con los ids de inscripcion]
     * @param  array    $gestion            [array gestiones]
     * @param  array    $idco               [array ids curso oferta]
     * @param  array    $idNotaTipo         [array ids de nota tipo]
     * @param  array    $nota               [array de notas]
     * @return [view]                       [vista de listado de areas del estudiante]
     */
    public function completarNotasAction(Request $request){
        try {

            $idIns = $request->get('idIns');
            // OBTENEMOS LOS ARRAYS DE DATOS
            $idInscripcion = $request->get('idInscripcion');
            $gestion = $request->get('gestion');
            $idco = $request->get('idco');
            $idNotaTipo = $request->get('idNotaTipo');
            $nota = $request->get('nota');

            $idieeht = $request->get('idieeht');

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            // dump($idieeht);

            // dump($idco);
            
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idIns);

            // VERIFICAMOS SI EXISTE LA ESPECIALIDAD PARA REGISTRARLO
            if (isset($idieeht)) {
                $data = explode('_', $idieeht);

                $idieeht = $data[0];
                $idCursoOferta = $data[1];

                $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

                // REGSITRAMOS LA MATERIA ESPECIALIZADA AL ESTUDIANTE
                $estudianteAsignatura = $this->get('areasEstudiante')->nuevo($idCursoOferta, $idIns, $gestion);

                $objInfoCourse = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcion->getInstitucioneducativaCurso());
                if($objInfoCourse->getNivelTipo()->getId() == 13 && $objInfoCourse->getGradoTipo()->getId() == 5 ){                    
                    $query = $em->getConnection()->prepare('SELECT * from sp_genera_migracion_notas_ttg_tte_2019_2020(:iestudiante_id::VARCHAR, :iinstitucioneducativa_id ::VARCHAR)');
                    $query->bindValue(':iestudiante_id', $inscripcion->getEstudiante()->getId());
                    $query->bindValue(':iinstitucioneducativa_id', $objInfoCourse->getInstitucioneducativa()->getId());
                    $query->execute();
                }

                // VERIFICAMOS SI YA TIENE REGISTRADO LA ESPECIALIDAD
                $especialidadEstudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array(
                    'estudianteInscripcion'=>$idIns
                ));

                // ELIMINAMOS LAS ESPECIALIDADES REGISTRADAS AL ESTUDIANTE
                if($especialidadEstudiante){
                    $eliminar = $em->createQueryBuilder()
                                ->delete('')
                                ->from('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico','eiht')
                                ->where('eiht.estudianteInscripcion = :idInscripcion')
                                ->setParameter('idInscripcion', $idIns)
                                ->getQuery()
                                ->getResult();
                }
                
                // REGISTRAMOS LA NUEVA ESPECIALIDAD                    
                $institucionEspecialidad = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->find($idieeht);
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_humnistico_tecnico');");
                $query->execute();
                $especialidadEstudiante = new EstudianteInscripcionHumnisticoTecnico();
                $especialidadEstudiante->setInstitucioneducativaHumanisticoId($institucionEspecialidad->getId());
                $especialidadEstudiante->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idIns));
                $especialidadEstudiante->setEspecialidadTecnicoHumanisticoTipo($em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($institucionEspecialidad->getEspecialidadTecnicoHumanisticoTipo()->getId()));
                $especialidadEstudiante->setHoras(0);
                $especialidadEstudiante->setObservacion('NUEVO._.');
                $em->persist($especialidadEstudiante);
                $em->flush();
            }

            // VERIFICAMOS SI EXISTEN MATERIAS POR REGISTRAR
            if (isset($idco)) {
                for ($i=0; $i < count($idco); $i++) {
                    // OBTENEMOS LA MATERIA SI EXISTIERA
                    $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array('estudianteInscripcion'=>$idInscripcion[$i], 'institucioneducativaCursoOferta'=>$idco[$i]));
                    if(!$estudianteAsignatura){
                        // REGISTRO DE LA MATERIA AL ESTUDIANTE
                        $estudianteAsignatura = $this->get('areasEstudiante')->nuevo($idco[$i], $idInscripcion[$i], $gestion[$i]);
                    }
                    
                    // OBTENEMOS LA NOTA SI EXISTIERA
                    $estudianteNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$estudianteAsignatura, 'notaTipo'=>$idNotaTipo[$i]));
                    if(!$estudianteNota){
                        // REGISTRAMOS LA NOTA
                        $estudianteNota = $this->get('notas')->registrarNota($idNotaTipo[$i], $estudianteAsignatura->getId(), $nota[$i], '');
                        // SE CALCULA EL PROMEDIO SI CORRESPONDE
                        $promedio = $this->get('notas')->calcularPromedioBimestral($estudianteAsignatura->getId());
                        // SE ACTUALIZA EL ESTADO DE MATRICULA SI CORRESPONDE
                        $actualizarEstadoMatricula = $this->get('notas')->actualizarEstadoMatricula($idIns);
                    }
                }
            }

            // OBTENEMOS LAS AREAS DEL ESTUDIANTE Y EL REGISTRO DE INSCRIPCION
            $areas = $this->get('areasEstudiante')->areasEstudiante($idIns);

            $data = array(
                'status'=>200,
                'type'=>'success',
                'msg'=> 'El área se agregó correctamente.'
            );

            $em->getConnection()->commit();

            // SE ACTUALIZA EL ESTADO DE MATRICULA SI CORRESPONDE
            $actualizarEstadoMatricula = $this->get('notas')->actualizarEstadoMatricula($idIns);
            return $this->render('SieHerramientaBundle:InfoEstudianteAreasEstudiante:index.html.twig',array(
                    'areas'=>$areas,
                    'inscripcion'=>$inscripcion,
                    'data'=>$data
            ));

        } catch (Exception $e) {
            $em->getConnection()->rollback();
        }
    }



    /**
     * FUNCIONES PARA VALIDAR QUE LA MATERIA ESPECIALIZADA DEL ESTUDIANTE TENGA REGISTRADA EL NOMBRE DE LA ESPECILIDAD
     */

    public function especialidadVerificarAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $idInscripcion = $request->get('idInscripcion');
        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');
        $arrInfoUe = unserialize($infoUe);

        // VERIFICAMOS SI EL ESTUDIANTE TIENE LA MATERIA ESPECIALIZADA
        $materiaEspecializada = $em->createQueryBuilder()
                        ->select('at.id, at.asignatura')
                        ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                        ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','with','ea.estudianteInscripcion = ei.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','with','ea.institucioneducativaCursoOferta = ieco.id')
                        ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','with','ieco.asignaturaTipo = at.id')
                        ->where('ei.id = :idInscripcion')
                        ->andWhere('at.id = 1039')
                        ->setParameter('idInscripcion', $idInscripcion)
                        ->getQuery()
                        ->getResult();

        if($materiaEspecializada){
            // VERIFICAMOS SI YA TIENE REGISTRADO LA ESPECIALIDAD
            $especialidadEstudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array(
                'estudianteInscripcion'=>$idInscripcion
            ));

            // SI EL ESTUDIANTE YA TIENE ESPECIALIDAD REGISTRADA
            if(!$especialidadEstudiante){
                $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
                //added funciont to migrate notas by krlos
                $objInfoCourse = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcion->getInstitucioneducativaCurso());
                if($objInfoCourse->getNivelTipo()->getId() == 13 && $objInfoCourse->getGradoTipo()->getId() == 5 && $arrInfoUe['requestUser']['gestion'] == 2020){                    
                    $query = $em->getConnection()->prepare('SELECT * from sp_genera_migracion_notas_ttg_tte_2019_2020(:iestudiante_id::VARCHAR, :iinstitucioneducativa_id ::VARCHAR)');
                    $query->bindValue(':iestudiante_id', $inscripcion->getEstudiante()->getId());
                    $query->bindValue(':iinstitucioneducativa_id', $objInfoCourse->getInstitucioneducativa()->getId());
                    $query->execute();
                }

                $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
                // OBTENEMOS LAS ESPECIALIDADES DE LA UNIDAD EDUCATIVA
                $especialidadesUe = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->findBy(array(
                    'institucioneducativa'=>$sie,
                    'gestionTipo'=>$gestion
                ));
                $arrayEsp = [];
                foreach ($especialidadesUe as $eue) {
                    $arrayEsp[$eue->getId()] = $eue->getEspecialidadTecnicoHumanisticoTipo()->getEspecialidad();
                }
                $form = $this->createFormBuilder()
                            ->add('idInscripcion', 'hidden', array('data' => $idInscripcion))
                            ->add('infoUe', 'hidden', array('data' => $infoUe))
                            ->add('infoStudent', 'hidden', array('data' => $infoStudent))
                            ->add('idieeht', 'choice', array('required' => true, 'choices'=>$arrayEsp, 'empty_value'=>'Seleccionar...', 'attr'=>array('class'=>'form-control')))
                            ->getForm();

                return $this->render('SieHerramientaBundle:InfoEstudianteAreasEstudiante:especialidad.html.twig',array(
                    'inscripcion'=>$inscripcion,
                    'form'=>$form->createView()
                ));   
            }else{

            }
        }else{
            // SI NO TIENE LA MATERIA ESPECIALIZADA ENTONCES PASA LA VALIDACION
            $response = new JsonResponse();
            return $response->setData(array(
                'validado'=>true,
                'idInscripcion'=>$idInscripcion,
                'infoUe'=>$infoUe,
                'infoStudent'=>$infoStudent
            ));
        }

        $response = new JsonResponse();
        return $response->setData(array(
            'validado'=>true,
            'idInscripcion'=>$idInscripcion,
            'infoUe'=>$infoUe,
            'infoStudent'=>$infoStudent
        ));
       
    }

    public function especialidadRegistrarAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $infoUe = $form['infoUe'];
        $infoStudent = $form['infoStudent'];
        $idInscripcion = $form['idInscripcion'];
        $idieeht = $form['idieeht'];
        $arrInfoUe = unserialize($infoUe);


        $institucionEspecialidad = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->find($idieeht);

        $especialidadEstudiante = new EstudianteInscripcionHumnisticoTecnico();
        $especialidadEstudiante->setInstitucioneducativaHumanisticoId($institucionEspecialidad->getId());
        $especialidadEstudiante->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
        $especialidadEstudiante->setEspecialidadTecnicoHumanisticoTipo($em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($institucionEspecialidad->getEspecialidadTecnicoHumanisticoTipo()->getId()));
        $especialidadEstudiante->setHoras(0);
        $especialidadEstudiante->setObservacion('NUEVO._..');
        $em->persist($especialidadEstudiante);
        $em->flush();

        $response = new JsonResponse();
        return $response->setData(array(
            'idInscripcion'=>$idInscripcion,
            'infoUe'=>$infoUe,
            'infoStudent'=>$infoStudent,
            'gestion'=>$arrInfoUe['requestUser']['gestion']

        ));

    }
}
