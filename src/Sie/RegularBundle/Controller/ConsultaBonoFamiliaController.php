<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteNota;

/**
 * EstudianteInscripcion controller.
 *
 */
class ConsultaBonoFamiliaController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
    }
    
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $niveles = $em->createQueryBuilder()
                    ->select('nt.id, nt.nivel')
                    ->from('SieAppWebBundle:NivelTipo','nt')
                    ->where('nt.id in (11,12,13)')
                    ->getQuery()
                    ->getResult();

        return $this->render('SieRegularBundle:ConsultaBonoFamilia:index.html.twig', array(
            'niveles'=> $niveles
        ));
    }

    public function cargarAction(){
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $niveles = $em->createQueryBuilder()
                    ->select('nt.id, nt.nivel')
                    ->from('SieAppWebBundle:NivelTipo','nt')
                    ->where('nt.id in (11,12,13)')
                    ->getQuery()
                    ->getResult();

        $grados = $em->createQueryBuilder()
                    ->select('gt.id, gt.grado')
                    ->from('SieAppWebBundle:GradoTipo','gt')
                    ->where('gt.id in (1,2,3,4,5,6)')
                    ->getQuery()
                    ->getResult();

        return $response->setData([
            'niveles'=>$niveles,
            'grados'=>$grados,
        ]);
    }

    public function buscarAction(Request $request){
        $response = new JsonResponse();
        $apoderado = $request->get('apoderado', null);
        $estudiante = $request->get('estudiante', null);
        $nivel = $estudiante['nivel'];
        $grado = $estudiante['grado'];
        $opcion = $request->get('opcion', null);

        $em = $this->getDoctrine()->getManager();
        // VALIDAMOS DATOS DEL ESTUDIANTE
        
        switch ($opcion) {
            case 1:
                $codigoRude = mb_strtoupper($estudiante['codigoRude']);
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$codigoRude));
                break;
            case 2:
                $carnet = $estudiante['carnet'];
                $complemento = $estudiante['complemento'];
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('carnetIdentidad'=>$carnet, 'complemento'=>$complemento));
                break;
            case 3:
                $nombre = mb_strtoupper($estudiante['nombre'], 'utf-8');
                $paterno = mb_strtoupper($estudiante['paterno'], 'utf-8');
                $materno = mb_strtoupper($estudiante['materno'], 'utf-8');
                $fechaNacimiento = $estudiante['fechaNacimiento'];

                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array(
                    'nombre'=>$nombre,
                    'paterno'=>$paterno,
                    'materno'=>$materno,
                    'fechaNacimiento'=>new \DateTime($fechaNacimiento)
                ));
                break;
        }

        if (!is_object($estudiante)) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'Los datos del estudiante no son válidos'
            ]);
        }

        // VERIFICAMOS SI LA OPCION ES POR CARNET PARA VALIDAR CON EL SEGIP
        if ($opcion == 2) {
            $validarSegip = $this->validarSegip($estudiante, $estudiante->getCarnetIdentidad());
            if (!$validarSegip) {
                return $response->setData([
                    'status'=>'error',
                    'msg'=>'Los datos del estudiante no son válidos'
                ]);
            }
        }

        // BUSCAMOS UNA INSCRIPCION CON ESTADO EFECTIVO EN LA GESTION 2020
        $inscripcionesEfectivas = $em->createQueryBuilder()
                            ->select('ei.id, nt.id as idNivel, grat.id as idGrado')
                            ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                            ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                            ->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
                            ->innerJoin('SieAppWebBundle:GradoTipo','grat','with','iec.gradoTipo = grat.id')
                            ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                            ->where('e.id = :idEstudiante')
                            ->andWhere('ei.estadomatriculaTipo = 4')
                            ->andWhere('gt.id = 2020')
                            ->setParameter('idEstudiante', $estudiante->getId())
                            ->getQuery()
                            ->getResult();


        if (count($inscripcionesEfectivas)==0) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'El estudiante no cuenta con una inscripción efectiva en la presente gestión'
            ]);
        }

        // VALIDAMOS SI EL ESTUDIANTE TIENE UNA INSCRIPCION CON EL NIVEL Y GRADO ESPECIFICADO
        // EN EL CASO DE QUE LA BUSQUEDA SE HAYA HECHO CON DATOS DEL ESTUDIANTE OPCION 3
        if ($opcion == 3) {
            $cont = 0; // variable que indica que no cumple con el nivel ni grado
            foreach ($inscripcionesEfectivas as $ie) {
                if ($nivel == $ie['idNivel'] && $grado == $ie['idGrado']) {
                    $cont++;
                }
            }

            if ($cont == 0) {
                return $response->setData([
                    'status'=>'error',
                    'msg'=>'El estudiante no cuenta con una inscripción en el nivel y grado indicado'
                ]);
            }
        }

        // VERIFICAMOS SI ESTA HABILITADO PARA REALIZAR EL COBRO DEL BONO
        $bono = $em->getRepository('SieAppWebBundle:BfEstudiantesValidados')->findOneBy(array(
            'codigoRude'=>$estudiante->getCodigoRude()
        ));

        if (!is_object($bono)) {
            // return $response->setData([
            //     'status'=>'success',
            //     'msg'=>'El estudiante no es beneficiario del bono familia, comuniquese con la Unidad Educativa del estudiante para solucionar el caso'
            // ]);
            $msgEstudiante = 'El estudiante no es beneficiario del bono familia, comuniquese con la Unidad Educativa del estudiante para solucionar el caso';
            $statusEstudiante = 'warning';
        }else{
            $msgEstudiante = 'El estudiante si es beneficiario del bono familia';
            $statusEstudiante = 'success';
        }

        /*----------  VALIDACION DE APODERADO  ----------*/
        

        // VALIDAMOS LOS DATOS DEL APODERADO
        if ($apoderado == null) {
            return $response->setData([
                'status'=>'success',
                'datos'=>array(
                    'statusApoderado'=>'warning',
                    'msgApoderado'=>'',
                    'statusEstudiante'=>$statusEstudiante,
                    'msgEstudiante'=>$msgEstudiante,
                )
            ]);
        }

        if ($apoderado['carnet'] == '') {
            return $response->setData([
                'status'=>'success',
                'datos'=>array(
                    'statusApoderado'=>'warning',
                    'msgApoderado'=>'',
                    'statusEstudiante'=>$statusEstudiante,
                    'msgEstudiante'=>$msgEstudiante,
                )
            ]);
        }

        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
            'carnet'=>$apoderado['carnet'],
            'complemento'=>mb_strtoupper($apoderado['complemento'],'utf-8')
        ));

        if (!is_object($persona)) {
            return $response->setData([
                'status'=>'success',
                'datos'=>array(
                    'statusApoderado'=>'warning',
                    'msgApoderado'=>'Los datos del apoderado no son válidos',
                    'statusEstudiante'=>$statusEstudiante,
                    'msgEstudiante'=>$msgEstudiante,
                )
            ]);
        }

        $validarSegip = $this->validarSegip($persona, $persona->getCarnet());
        if (!$validarSegip) {
            return $response->setData([
                'status'=>'success',
                'datos'=>array(
                    'statusApoderado'=>'warning',
                    'msgApoderado'=>'Los datos del apoderado no son válidos',
                    'statusEstudiante'=>$statusEstudiante,
                    'msgEstudiante'=>$msgEstudiante,
                )
            ]);
        }

        // VALIDAMOS SI EL APODERADO TIENE ALGUN PARENTESCO CON EL ESTUDIANTE
        $apoderadoInscripcion = $em->createQueryBuilder()
                                ->select('ai')
                                ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                                ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                                ->innerJoin('SieAppWebBundle:ApoderadoInscripcion','ai','with','ai.estudianteInscripcion = ei.id')
                                ->innerJoin('SieAppWebBundle:Persona','p','with','ai.persona = p.id')
                                ->where('e.id = :idEstudiante')
                                ->andWhere('p.id = :idPersona')
                                ->andWhere('gt.id in (:gestiones)')
                                ->setParameter('idEstudiante', $estudiante->getId())
                                ->setParameter('idPersona', $persona->getId())
                                ->setParameter('gestiones', array(2019,2020))
                                ->getQuery()
                                ->getResult();
        
        //VERIFICAMOS SI NO EXISTE REGISTRO DEL APODERADO
        if (count($apoderadoInscripcion)==0) {
            return $response->setData([
                'status'=>'success',
                'datos'=>array(
                    'statusApoderado'=>'warning',
                    'msgApoderado'=>'El apoderado no se encuentra registrado como apoderado del estudiante, para su registro debe comunicarse con la Unidad Educativa del estudiante',
                    'statusEstudiante'=>$statusEstudiante,
                    'msgEstudiante'=>$msgEstudiante,
                )
            ]);
        }

        

        return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'statusApoderado'=>'success',
                'msgApoderado'=>'El apoderado si esta habilitado para realizar el cobro del bono',
                'statusEstudiante'=>$statusEstudiante,
                'msgEstudiante'=>$msgEstudiante,
            )
        ]);
    }

    public function buscarPrevAction(Request $request){
        $response = new JsonResponse();
        $apoderado = $request->get('apoderado', null);
        $estudiante = $request->get('estudiante', null);
        $nivel = $estudiante['nivel'];
        $grado = $estudiante['grado'];
        $opcion = $request->get('opcion', null);

        // VALIDAMOS LOS DATOS DEL APODERADO
        if ($apoderado == null) {
            return $response->setData([
                'status'=>'error',
                // 'msg'=>'El número de carnet y/o complemento del apoderado no es válido',
                'msg'=>'Los datos del apoderado no son válidos'
            ]);
        }

        $em = $this->getDoctrine()->getManager();
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
            'carnet'=>$apoderado['carnet'],
            'complemento'=>mb_strtoupper($apoderado['complemento'],'utf-8')
        ));

        if (!is_object($persona)) {
            return $response->setData([
                'status'=>'error',
                // 'msg'=>'Los datos de carnet y/o complemento del apoderado no son válidos o no se encuentra registrado en el sistema',
                'msg'=>'Los datos del apoderado no son válidos'
            ]);
        }

        $validarSegip = $this->validarSegip($persona, $persona->getCarnet());
        if (!$validarSegip) {
            return $response->setData([
                'status'=>'error',
                // 'msg'=>'Los datos de carnet y/o complemento del apoderado no son válidos o no se encuentra registrado en el sistema',
                'msg'=>'Los datos del apoderado no son válidos'
            ]);
        }

        // VALIDAMOS DATOS DEL ESTUDIANTE
        
        switch ($opcion) {
            case 1:
                $codigoRude = mb_strtoupper($estudiante['codigoRude']);
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$codigoRude));
                break;
            case 2:
                $carnet = $estudiante['carnet'];
                $complemento = $estudiante['complemento'];
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('carnetIdentidad'=>$carnet, 'complemento'=>$complemento));
                break;
            case 3:
                $nombre = mb_strtoupper($estudiante['nombre'], 'utf-8');
                $paterno = mb_strtoupper($estudiante['paterno'], 'utf-8');
                $materno = mb_strtoupper($estudiante['materno'], 'utf-8');
                $fechaNacimiento = $estudiante['fechaNacimiento'];

                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array(
                    'nombre'=>$nombre,
                    'paterno'=>$paterno,
                    'materno'=>$materno,
                    'fechaNacimiento'=>new \DateTime($fechaNacimiento)
                ));
                break;
        }

        if (!is_object($estudiante)) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'Los datos del estudiante no son válidos'
            ]);
        }

        // VERIFICAMOS SI LA OPCION ES POR CARNET PARA VALIDAR CON EL SEGIP
        if ($opcion == 2) {
            $validarSegip = $this->validarSegip($estudiante, $estudiante->getCarnetIdentidad());
            if (!$validarSegip) {
                return $response->setData([
                    'status'=>'error',
                    'msg'=>'Los datos del estudiante no son válidos'
                ]);
            }
        }

        // VALIDAMOS SI EL APODERADO TIENE ALGUN PARENTESCO CON EL ESTUDIANTE
        $apoderadoInscripcion = $em->createQueryBuilder()
                                ->select('ai')
                                ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                                ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                                ->innerJoin('SieAppWebBundle:ApoderadoInscripcion','ai','with','ai.estudianteInscripcion = ei.id')
                                ->innerJoin('SieAppWebBundle:Persona','p','with','ai.persona = p.id')
                                ->where('e.id = :idEstudiante')
                                ->andWhere('p.id = :idPersona')
                                ->andWhere('gt.id in (:gestiones)')
                                ->setParameter('idEstudiante', $estudiante->getId())
                                ->setParameter('idPersona', $persona->getId())
                                ->setParameter('gestiones', array(2019,2020))
                                ->getQuery()
                                ->getResult();
        
        //VERIFICAMOS SI NO EXISTE REGISTRO DEL APODERADO
        if (count($apoderadoInscripcion)==0) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'Usted no se encuentra registrado como apoderado del estudiante, para su registro comuniquese con la Unidad Educativa del estudiante'
            ]);
        }

        // BUSCAMOS UNA INSCRIPCION CON ESTADO EFECTIVO EN LA GESTION 2020
        $inscripcionesEfectivas = $em->createQueryBuilder()
                            ->select('ei.id, nt.id as idNivel, grat.id as idGrado')
                            ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                            ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                            ->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
                            ->innerJoin('SieAppWebBundle:GradoTipo','grat','with','iec.gradoTipo = grat.id')
                            ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                            ->where('e.id = :idEstudiante')
                            ->andWhere('ei.estadomatriculaTipo = 4')
                            ->andWhere('gt.id = 2020')
                            ->setParameter('idEstudiante', $estudiante->getId())
                            ->getQuery()
                            ->getResult();


        if (count($inscripcionesEfectivas)==0) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'El estudiante no cuenta con una inscripción efectiva en la presente gestión'
            ]);
        }

        // VALIDAMOS SI EL ESTUDIANTE TIENE UNA INSCRIPCION CON EL NIVEL Y GRADO ESPECIFICADO
        // EN EL CASO DE QUE LA BUSQUEDA SE HAYA HECHO CON DATOS DEL ESTUDIANTE OPCION 3
        if ($opcion == 3) {
            $cont = 0; // variable que indica que no cumple con el nivel ni grado
            foreach ($inscripcionesEfectivas as $ie) {
                if ($nivel == $ie['idNivel'] && $grado == $ie['idGrado']) {
                    $cont++;
                }
            }

            if ($cont == 0) {
                return $response->setData([
                    'status'=>'error',
                    'msg'=>'El estudiante no cuenta con una inscripción en el nivel y grado indicado'
                ]);
            }
        }

        // VERIFICAMOS SI ESTA HABILITADO PARA REALIZAR EL COBRO DEL BONO
        $bono = $em->getRepository('SieAppWebBundle:BfEstudiantesValidados')->findOneBy(array(
            'codigoRude'=>$estudiante->getCodigoRude()
        ));

        if (!is_object($bono)) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'El estudiante no es beneficiario del bono familia, comuniquese con la Unidad Educativa del estudiante para solucionar el caso'
            ]);
        }

        return $response->setData([
            'status'=>'success',
            'msg'=>'todo bien',
            'datos'=>array(
                'msg'=>'El estudiante si es beneficiario del bono familia, su persona esta habilitada para realizar el cobro',
                'apoderadoCarnet'=>$persona->getCarnet(),
                'apoderadoComplemento'=>$persona->getComplemento(),
                'estudianteCodigoRude'=>$estudiante->getCodigoRude()
            )
        ]);
    }

    private function validarSegip($persona, $carnet){
        $datos = array(
            'complemento'=>mb_strtoupper($persona->getComplemento(), 'utf-8'),
            // 'primer_apellido'=>mb_strtoupper($persona->getPaterno(), 'utf-8'),
            // 'segundo_apellido'=>mb_strtoupper($persona->getMaterno(), 'utf-8'),
            // 'nombre'=>mb_strtoupper($persona->getNombre(), 'utf-8'),
            // 'fecha_nacimiento'=>$persona->getFechaNacimiento()->format('d-m-Y')
        );

        $resultadoPersona = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($carnet,$datos,'prod','academico');
        if($resultadoPersona){
            return true;
        }

        return false;
    }
}
