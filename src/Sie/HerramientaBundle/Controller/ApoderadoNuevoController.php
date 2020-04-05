<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use Sie\AppWebBundle\Entity\ApoderadoInscripcion;
use Sie\AppWebBundle\Entity\ApoderadoInscripcionDatos;
use Sie\AppWebBundle\Entity\Persona;

/**
 * Apoderado2020 Controller
 */
class ApoderadoNuevoController extends Controller {

    public $session;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Muestra el listado de Menús
     */
    public function indexAction (Request $request) {
        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');

        $aInfoUeducativa = unserialize($infoUe);
        $aInfoStudent = json_decode($infoStudent, TRUE);

        $idInscripcion = $aInfoStudent['eInsId'];

        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

        return $this->render('SieHerramientaBundle:ApoderadoNuevo:index.html.twig', array(
            'idInscripcion'=>$idInscripcion,
            'inscripcion'=>$inscripcion
        ));
    }

    public function apoderadosAction($idInscripcion){
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        if (!is_object($inscripcion)) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'La inscripción no existe.'
            ]);
        }

        $apoderados2019 = $this->obtenerApoderados($inscripcion->getEstudiante()->getId(), 2019);
        $apoderados2020 = $this->obtenerApoderados($inscripcion->getEstudiante()->getId(), 2020);

        $expedidos = $em->createQueryBuilder()
                        ->select('dt.id, dt.sigla')
                        ->from('SieAppWebBundle:DepartamentoTipo','dt')
                        ->where('dt.id > 0')
                        ->addOrderBy('dt.id','asc')
                        ->getQuery()
                        ->getResult();

        $generos = $em->createQueryBuilder()
                        ->select('gt.id, gt.genero')
                        ->from('SieAppWebBundle:GeneroTipo','gt')
                        ->where('gt.id in (1,2)')
                        ->addOrderBy('gt.id','asc')
                        ->getQuery()
                        ->getResult();

        $ocupaciones = $em->createQueryBuilder()
                        ->select('aot.id, aot.ocupacion')
                        ->from('SieAppWebBundle:ApoderadoOcupacionTipo','aot')
                        ->where('aot.id in (:ids)')
                        ->setParameter('ids', $this->obtenerCatalogo('apoderado_ocupacion_tipo'))
                        ->addOrderBy('aot.id','asc')
                        ->getQuery()
                        ->getResult();

        $instrucciones = $em->createQueryBuilder()
                        ->select('it.id, it.instruccion')
                        ->from('SieAppWebBundle:InstruccionTipo','it')
                        ->where('it.id in (:ids)')
                        ->setParameter('ids', $this->obtenerCatalogo('instruccion_tipo'))
                        ->addOrderBy('it.id','asc')
                        ->getQuery()
                        ->getResult();

        $parentescoTutor = $em->createQueryBuilder()
                        ->select('at.id, at.apoderado')
                        ->from('SieAppWebBundle:ApoderadoTipo','at')
                        ->where('at.id not in (0,1,2,9,10,11,12)')
                        ->addOrderBy('at.id','asc')
                        ->getQuery()
                        ->getResult();

        $parentescoPadre = $em->createQueryBuilder()
                        ->select('at.id, at.apoderado')
                        ->from('SieAppWebBundle:ApoderadoTipo','at')
                        ->where('at.id in (1)')
                        ->addOrderBy('at.id','asc')
                        ->getQuery()
                        ->getResult();

        $parentescoMadre = $em->createQueryBuilder()
                        ->select('at.id, at.apoderado')
                        ->from('SieAppWebBundle:ApoderadoTipo','at')
                        ->where('at.id in (2)')
                        ->addOrderBy('at.id','asc')
                        ->getQuery()
                        ->getResult();

        $extranjeros = array(
                            array('id'=>0,'extranjero'=>'NO'),
                            array('id'=>1,'extranjero'=>'SI')
                        );

        return $response->setData([
            'status'=>'success',
            'apoderados2019'=>$apoderados2019,
            'apoderados2020'=>$apoderados2020,
            'expedidos'=>$expedidos,
            'generos'=>$generos,
            'ocupaciones'=>$ocupaciones,
            'instrucciones'=>$instrucciones,
            'parentescoTutor'=>$parentescoTutor,
            'parentescoPadre'=>$parentescoPadre,
            'parentescoMadre'=>$parentescoMadre,
            'extranjeros'=>$extranjeros,
        ]);
    }

    private function obtenerApoderados($idEstudiante, $gestion){
        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->createQueryBuilder()
                        ->select('ei')
                        ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                        ->where('ei.estudiante = :idEstudiante')
                        ->andWhere('iec.gestionTipo = :gestion')
                        ->setParameter('idEstudiante', $idEstudiante)
                        ->setParameter('gestion', $gestion)
                        ->addOrderBy('ei.id','DESC')
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getResult();

        $apoderados = [];

        if (count($inscripcion) > 0) {
            $apoderados = $em->createQueryBuilder()
                            ->select('
                                ai.id,
                                p.carnet,
                                p.complemento,
                                dt.sigla as expedido,
                                p.paterno, 
                                p.materno, 
                                p.nombre,
                                p.fechaNacimiento,
                                gt.genero,
                                p.correo,
                                p.celular,
                                at.id as tipo, 
                                at.apoderado,
                                aot.ocupacion,
                                aid.empleo as ocupacionOtro,
                                it.instruccion,
                                p.esExtranjero as extranjero,
                                aid.obs as lugar
                            ')
                            ->from('SieAppWebBundle:ApoderadoInscripcion','ai')
                            ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','ai.estudianteInscripcion = ei.id')
                            ->innerJoin('SieAppWebBundle:Persona','p','with','ai.persona = p.id')
                            ->innerJoin('SieAppWebBundle:DepartamentoTipo','dt','with','p.expedido = dt.id')
                            ->innerJoin('SieAppWebBundle:GeneroTipo','gt','with','p.generoTipo = gt.id')
                            ->innerJoin('SieAppWebBundle:ApoderadoTipo','at','with','ai.apoderadoTipo = at.id')
                            ->innerJoin('SieAppWebBundle:ApoderadoInscripcionDatos','aid','with','aid.apoderadoInscripcion = ai.id')
                            ->leftJoin('SieAppWebBundle:ApoderadoOcupacionTipo','aot','with','aid.ocupacionTipo = aot.id')
                            ->leftJoin('SieAppWebBundle:InstruccionTipo','it','with','aid.instruccionTipo = it.id')
                            ->where('ei.id = :idInscripcion')
                            ->andWhere('p.segipId = 1')
                            ->setParameter('idInscripcion', $inscripcion[0]->getId())
                            ->getQuery()
                            ->getResult();
            
            if (count($apoderados) > 0 or $gestion == 2020) {
                $padre = '';
                $madre = '';
                $tutor = '';
                foreach ($apoderados as $ap) {
                    $ap['fechaNacimiento'] = $ap['fechaNacimiento']->format('d-m-Y');
                    switch ($ap['tipo']) {
                        case 1: $padre = $ap; break;
                        case 2: $madre = $ap; break;
                        default: $tutor = $ap; break;
                    }
                }

                $apoderados = array(
                    $padre,
                    $madre,
                    $tutor
                );

            }
        }

        return $apoderados;
    }

    public function validarAction(Request $request){
        $response = new JsonResponse();
        $apoderado = $request->get('apoderado');
        
        $validado = $this->validarSegip($apoderado);
        
        return $response->setData($validado);
    }

    private function validarSegip($apoderado){
        if($apoderado['carnet'] != '' && $apoderado['nombres'] != '' && $apoderado['fechaNacimiento'] != ''){
            $em = $this->getDoctrine()->getManager();
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy([
                'carnet'=>$apoderado['carnet'],
                'complemento'=>mb_strtoupper($apoderado['complemento'], 'utf-8'),
                'paterno'=>mb_strtoupper($apoderado['paterno'], 'utf-8'),
                'materno'=>mb_strtoupper($apoderado['materno'], 'utf-8'),
                'nombre'=>mb_strtoupper($apoderado['nombres'], 'utf-8'),
                'fechaNacimiento'=>new \DateTime($apoderado['fechaNacimiento'])
            ]);

            if (is_object($persona) && $persona->getSegipId() == 1) {
                return [
                    'status'=>'success',
                    'validado'=>true,
                    'msg'=>'Los datos fueron validados correctamente BD'
                ];
            }else{
                $datos = array(
                    'complemento'=>mb_strtoupper($apoderado['complemento'], 'utf-8'),
                    'primer_apellido'=>mb_strtoupper($apoderado['paterno'], 'utf-8'),
                    'segundo_apellido'=>mb_strtoupper($apoderado['materno'], 'utf-8'),
                    'nombre'=>mb_strtoupper($apoderado['nombres'], 'utf-8'),
                    'fecha_nacimiento'=>$apoderado['fechaNacimiento']
                );

                $resultadoPersona = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($apoderado['carnet'],$datos,'prod','academico');
                if($resultadoPersona){
                    return [
                        'status'=>'success',
                        'validado'=>true,
                        'msg'=>'Los datos fueron validados correctamente SEGIP'
                    ];
                } else {
                    return [
                        'status'=>'error',
                        'validado'=>false,
                        'msg'=>'Los datos ingresados no son correctos, verifique e intente nuevamente'
                    ];
                }
            }
        }
        
        return [
            'status'=>'error',
            'validado'=>false,
            'msg'=>'Debe completar todos los datos para realizar la validación'
        ];
    }

    public function obtenerCatalogo($tabla){
        $em = $this->getDoctrine()->getManager();
        $gestion = 2019;
        /**
         * OBTENER CATALOGO
         */
        $catalogo = $em->createQueryBuilder()
                            ->select('rc')
                            ->from('SieAppWebBundle:RudeCatalogo','rc')
                            ->where('rc.gestionTipo = :gestion')
                            ->andWhere('rc.institucioneducativaTipo = 1')
                            ->andWhere('rc.nombreTabla = :tabla')
                            ->setParameter('gestion', $gestion)
                            ->setParameter('tabla', $tabla)
                            ->getQuery()
                            ->getResult();
        $ids = [];
        foreach ($catalogo as $c) {
            $ids[] = $c->getLlaveTabla();
        }

        return $ids;
    }

    public function registrarAction(Request $request){
        $response = new JsonResponse();
        $apoderado = $request->get('apoderado');
        $idInscripcion = $request->get('idInscripcion');
        $validado = $request->get('validado');

        // $validacion = $this->validarSegip($apoderado);
        // $validado = $validacion['validado'];
        
        if($apoderado['carnet'] == '' or $apoderado['nombres'] == '' or $apoderado['fechaNacimiento'] == '' or $apoderado['expedido'] == '' or $apoderado['genero'] == '' or $apoderado['celular'] == '' or $apoderado['ocupacion'] == '' or $apoderado['instruccion'] == '' or $apoderado['parentesco'] == '' or $apoderado['extranjero'] == ''){
            return $response->setData([
                'status'=>'error',
                'registrado'=>false,
                'msg'=>'Complete los campos requeridos *'
            ]);
        }
        
        if ($validado) {
            $em = $this->getDoctrine()->getManager();
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy([
                'carnet'=>$apoderado['carnet'],
                'complemento'=>mb_strtoupper($apoderado['complemento'], 'utf-8'),
                'paterno'=>mb_strtoupper($apoderado['paterno'], 'utf-8'),
                'materno'=>mb_strtoupper($apoderado['materno'], 'utf-8'),
                'nombre'=>mb_strtoupper($apoderado['nombres'], 'utf-8'),
                'fechaNacimiento'=>new \DateTime($apoderado['fechaNacimiento'])
            ]);

            if (is_object($persona) && $persona->getSegipId() == 1) {
                // ACTUALIZAMOS LOS DATOS DE LA PERSONA
                $persona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($apoderado['expedido']));
                $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($apoderado['genero']));
                $persona->setCorreo($apoderado['correo']);
                $persona->setCelular($apoderado['celular']);
                $persona->setEsExtranjero($apoderado['extranjero']);
                $em->flush();
                
            }else{
                $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy([
                    'carnet'=>$apoderado['carnet'],
                    'complemento'=>mb_strtoupper($apoderado['complemento'], 'utf-8')
                ]);

                if (is_object($persona)) {
                    return $response->setData([
                        'status'=>'error',
                        'registrado'=>false,
                        'msg'=>'El número de carnet ya esta registrado con otros datos'
                    ]);
                }else{
                    $persona = new Persona();
                    $persona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find(98));
                    $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($apoderado['genero']));
                    $persona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find(7));
                    $persona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find(0));
                    $persona->setCarnet($apoderado['carnet']);
                    $persona->setComplemento(mb_strtoupper($apoderado['complemento'],'utf-8'));
                    $persona->setCelular($apoderado['celular']);
                    $persona->setRda(0);
                    $persona->setPaterno(mb_strtoupper($apoderado['paterno'],'utf-8'));
                    $persona->setMaterno(mb_strtoupper($apoderado['materno'],'utf-8'));
                    $persona->setNombre(mb_strtoupper($apoderado['nombre'],'utf-8'));
                    $persona->setFechaNacimiento(new \DateTime($apoderado['fechaNacimiento']));
                    $persona->setSegipId(1);
                    $persona->setEsExtranjero($apoderado['extranjero']);
                    $em->persist($persona);
                    $em->flush();
                }
            }

            switch($apoderado['parentesco']) {
                case 1: $parentesco = array(1); break;
                case 2: $parentesco = array(2); break;
                default: $parentesco = array(3,4,5,6,7,8,9,10,11,12,13); break;
            }

            $apoderadoInscripcion = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findOneBy(array(
                'estudianteInscripcion'=>$idInscripcion,
                'apoderadoTipo'=>$parentesco
            ));

            if (!is_object($apoderadoInscripcion)) {
                // REGISTRAMOS LA RELACION APODERADO ESTUDIANTE
                $newApoderadoInscripcion = new ApoderadoInscripcion();
                $newApoderadoInscripcion->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($apoderado['parentesco']));
                $newApoderadoInscripcion->setPersona($persona);
                $newApoderadoInscripcion->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                $newApoderadoInscripcion->setEsValidado(1);
                $em->persist($newApoderadoInscripcion);
                $em->flush();

                $idApoderadoInscripcion = $newApoderadoInscripcion->getId();
            }else{
                // ACTUALIZAMOS LA RELACION APODERADO ESTUDIANTE
                $apoderadoInscripcion->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($apoderado['parentesco']));
                $apoderadoInscripcion->setPersona($persona);
                $apoderadoInscripcion->setEsValidado(1);
                $em->persist($apoderadoInscripcion);
                $em->flush();

                $idApoderadoInscripcion = $apoderadoInscripcion->getId();
            }

            $apoderadoInscripcionDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findOneBy(array(
                'apoderadoInscripcion'=>$idApoderadoInscripcion
            ));

            if (!is_object($apoderadoInscripcionDatos)) {
                // REGISTRAMOS LOS DATOS DEL APODERADO
                $apoderadoInscripcionDatos = new ApoderadoInscripcionDatos();
                $apoderadoInscripcionDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find(98));
                $apoderadoInscripcionDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($apoderado['instruccion']));
                $apoderadoInscripcionDatos->setApoderadoInscripcion($em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($idApoderadoInscripcion));
                $apoderadoInscripcionDatos->setTelefono($apoderado['telefono']);
                $apoderadoInscripcionDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($apoderado['ocupacion']));
                if ($apoderado['ocupacion'] == 10035) { // OTRA OCUPACION
                    $apoderadoInscripcionDatos->setEmpleo(mb_strtoupper($apoderado['ocupacionOtro'],'utf-8'));    
                }else{
                    $apoderadoInscripcionDatos->setEmpleo('');
                }
                $apoderadoInscripcionDatos->setObs(mb_strtoupper($apoderado['lugar'],'utf-8'));
                $em->persist($apoderadoInscripcionDatos);
                $em->flush();

            }else{
                // ACTUALIZAMOS LOS DATOS DEL APODERADO
                $apoderadoInscripcionDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($apoderado['instruccion']));
                $apoderadoInscripcionDatos->setTelefono($apoderado['telefono']);
                $apoderadoInscripcionDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($apoderado['ocupacion']));
                if ($apoderado['ocupacion'] == 10035) { // OTRA OCUPACION
                    $apoderadoInscripcionDatos->setEmpleo(mb_strtoupper($apoderado['ocupacionOtro'],'utf-8'));    
                }else{
                    $apoderadoInscripcionDatos->setEmpleo('');
                }
                $apoderadoInscripcionDatos->setObs(mb_strtoupper($apoderado['lugar'],'utf-8'));
                $em->persist($apoderadoInscripcionDatos);
                $em->flush();
            }

            return $response->setData([
                'status'=>'success',
                'registrado'=>true,
                'msg'=>'El apoderado fue registrado correctamente'
            ]);

        }

        return $response->setData([
            'status'=>'error',
            'registrado'=>false,
            'msg'=>'No se pudo registrar al apoderado'
        ]);
    }

    public function eliminarAction($idApoderado){
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $apoderadoInscripcion = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($idApoderado);
        if (is_object($apoderadoInscripcion)) {
            $apoderadoDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findOneBy(array('apoderadoInscripcion'=>$apoderadoInscripcion->getId()));
            if (is_object($apoderadoDatos)) {
                $em->remove($apoderadoDatos);
                $em->flush();
            }
            $em->remove($apoderadoInscripcion);
            $em->flush();

            return $response->setData([
                'status'=>'success',
                'msg'=>'Apoderdo eliminado exitosamente'
            ]);
        }

        return $response->setData([
            'status'=>'error',
            'msg'=>'No se pudo eliminar el apoderado'
        ]);
    }
}
