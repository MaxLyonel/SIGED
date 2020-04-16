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
class ApoderadoBonoFamiliaController extends Controller {

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

        return $this->render('SieHerramientaBundle:ApoderadoBonoFamilia:index.html.twig', array(
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

        $apoderados = $this->obtenerApoderados($inscripcion->getEstudiante()->getId());
        $apoderados2019 = $apoderados['apoderados2019'];
        $apoderados2020 = $apoderados['apoderados2020'];

        if (count($apoderados2020) > 0) {
            $apoderadoBonoFamilia = $apoderados2020[0];
        }else{
            $apoderadoBonoFamilia = '';
        }

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

        $paises = $em->createQueryBuilder()
                        ->select('pt.id, pt.pais')
                        ->from('SieAppWebBundle:PaisTipo','pt')
                        ->where('pt.id not in (0)')
                        ->addOrderBy('pt.id','asc')
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

        // $parentescoTutor = $em->createQueryBuilder()
        //                 ->select('at.id, at.apoderado')
        //                 ->from('SieAppWebBundle:ApoderadoTipo','at')
        //                 ->where('at.id not in (0,1,2,9,10,11,12)')
        //                 ->addOrderBy('at.id','asc')
        //                 ->getQuery()
        //                 ->getResult();

        // $parentescoPadre = $em->createQueryBuilder()
        //                 ->select('at.id, at.apoderado')
        //                 ->from('SieAppWebBundle:ApoderadoTipo','at')
        //                 ->where('at.id in (1)')
        //                 ->addOrderBy('at.id','asc')
        //                 ->getQuery()
        //                 ->getResult();

        // $parentescoMadre = $em->createQueryBuilder()
        //                 ->select('at.id, at.apoderado')
        //                 ->from('SieAppWebBundle:ApoderadoTipo','at')
        //                 ->where('at.id in (2)')
        //                 ->addOrderBy('at.id','asc')
        //                 ->getQuery()
        //                 ->getResult();

        $parentescos = $em->createQueryBuilder()
                        ->select('at.id, at.apoderado')
                        ->from('SieAppWebBundle:ApoderadoTipo','at')
                        ->where('at.id in (1,2,3,4,5,6,7,8)')
                        ->addOrderBy('at.id','asc')
                        ->getQuery()
                        ->getResult();

        $extranjeros = array(
                            array('id'=>1,'extranjero'=>'NO'),
                            array('id'=>2,'extranjero'=>'SI')
                        );



        return $response->setData([
            'status'=>'success',
            'apoderados2019'=>$apoderados2019,
            'apoderadoBonoFamilia'=>$apoderadoBonoFamilia,
            'expedidos'=>$expedidos,
            'generos'=>$generos,
            'paises'=>$paises,
            'ocupaciones'=>$ocupaciones,
            'instrucciones'=>$instrucciones,
            // 'parentescoTutor'=>$parentescoTutor,
            // 'parentescoPadre'=>$parentescoPadre,
            // 'parentescoMadre'=>$parentescoMadre,
            'parentescos'=>$parentescos,
            'extranjeros'=>$extranjeros,
        ]);
    }

    private function obtenerApoderados($idEstudiante){
        $em = $this->getDoctrine()->getManager();

        $apoderados = $em->createQueryBuilder()
                        ->select('
                            ai.id,
                            p.carnet,
                            p.complemento,
                            dt.id as expedido,
                            dt.sigla as expedidoText,
                            p.paterno, 
                            p.materno, 
                            p.nombre as nombres,
                            p.fechaNacimiento,
                            gt.id as genero,
                            gt.genero as generoText,
                            p.correo,
                            p.celular,
                            at.id as parentesco,
                            at.apoderado as parentescoText,
                            aot.id as ocupacion,
                            aot.ocupacion as ocupacionText,
                            aid.empleo as ocupacionOtro,
                            it.id as instruccion,
                            it.instruccion as instruccionText,
                            p.esExtranjero as extranjero,
                            p.localidadNac as lugar,
                            pt.id as pais,
                            pt.pais as paisText,
                            gest.id as gestion,
                            p.id as idPersona
                        ')
                        ->from('SieAppWebBundle:ApoderadoInscripcion','ai')
                        ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','ai.estudianteInscripcion = ei.id')
                        ->innerJoin('SieAppWebBundle:Persona','p','with','ai.persona = p.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                        ->innerJoin('SieAppWebBundle:GestionTipo','gest','with','iec.gestionTipo = gest.id')
                        ->innerJoin('SieAppWebBundle:DepartamentoTipo','dt','with','p.expedido = dt.id')
                        ->innerJoin('SieAppWebBundle:GeneroTipo','gt','with','p.generoTipo = gt.id')
                        ->innerJoin('SieAppWebBundle:ApoderadoTipo','at','with','ai.apoderadoTipo = at.id')
                        ->leftJoin('SieAppWebBundle:ApoderadoInscripcionDatos','aid','with','aid.apoderadoInscripcion = ai.id')
                        ->leftJoin('SieAppWebBundle:ApoderadoOcupacionTipo','aot','with','aid.ocupacionTipo = aot.id')
                        ->leftJoin('SieAppWebBundle:InstruccionTipo','it','with','aid.instruccionTipo = it.id')
                        ->leftJoin('SieAppWebBundle:PaisTipo','pt','with','p.paisTipo = pt.id')
                        ->where('ei.estudiante = :idEstudiante')
                        ->andWhere('ai.esValidado = 1')
                        ->andWhere('p.segipId = 1')
                        ->setParameter('idEstudiante', $idEstudiante)
                        ->orderBy('ai.id','DESC')
                        ->getQuery()
                        ->getResult();
        
        $arrayPersonas = [];
        $arrayApoderados2019 = [];
        $arrayApoderados2020 = [];
        foreach ($apoderados as $ap) {
            if (!in_array($ap['idPersona'], $arrayPersonas)) {
                $ap['fechaNacimiento'] = $ap['fechaNacimiento']->format('d-m-Y');
                $ap['extranjero'] = ($ap['extranjero'] == true)?2:1;
                if ($ap['gestion'] == 2020) {
                    $arrayApoderados2020[] = $ap;
                }else{
                    $arrayApoderados2019[] = $ap;
                }
            }
            $arrayPersonas[] = $ap['idPersona'];
        }

        return array('apoderados2019'=>$arrayApoderados2019, 'apoderados2020'=>$arrayApoderados2020);
    }

    public function habilitarAction(Request $request){
        $response = new JsonResponse();
        $idApoderado = $request->get('idApoderado', null);
        $idInscripcion = $request->get('idInscripcion', null);

        if ($idApoderado == null || $idInscripcion == null) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'No se pudo validar el apoderado'
            ]);
        }

        $em = $this->getDoctrine()->getManager();
        $apoderadoInscripcion = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($idApoderado);
        if (!is_object($apoderadoInscripcion)) {
            return $response->setData([
                'status'=>'error',
                'msg'=>'No se pudo validar el apoderado'
            ]);
        }

        $verificamosRegistroParecido = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findOneBy(array(
            'apoderadoTipo'=>$apoderadoInscripcion->getApoderadoTipo()->getId(),
            'persona'=>$apoderadoInscripcion->getPersona()->getId(),
            'estudianteInscripcion'=>$idInscripcion,
        ));

        if (is_object($verificamosRegistroParecido)) {
            $verificamosRegistroParecido->setEsValidado(1);
            $em->flush();
        }else{
            $nuevoApoderado = new ApoderadoInscripcion();
            $nuevoApoderado->setApoderadoTipo($apoderadoInscripcion->getApoderadoTipo());
            $nuevoApoderado->setPersona($apoderadoInscripcion->getPersona());
            $nuevoApoderado->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
            $nuevoApoderado->setObs('');
            $nuevoApoderado->setEsValidado(1);
            $em->persist($nuevoApoderado);
            $em->flush();
        }

        return $response->setData([
            'status'=>'success',
            'msg'=>'Apoderado habilitado exitosamente'
        ]);
    }

    public function validarAction(Request $request){
        $response = new JsonResponse();
        $apoderado = $request->get('apoderado');
        
        $validado = $this->validarSegip($apoderado);
        
        return $response->setData($validado);
    }

    private function validarSegip($apoderado){
        if($apoderado['carnet'] != '' && $apoderado['nombres'] != '' && $apoderado['fechaNacimiento'] != ''){
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
        
        // if($apoderado['carnet'] == '' or $apoderado['nombres'] == '' or $apoderado['fechaNacimiento'] == '' or $apoderado['expedido'] == '' or $apoderado['genero'] == '' or $apoderado['celular'] == '' or $apoderado['ocupacion'] == '' or $apoderado['instruccion'] == '' or $apoderado['parentesco'] == '' or $apoderado['extranjero'] == ''){
        if($apoderado['carnet'] == '' or $apoderado['nombres'] == '' or $apoderado['fechaNacimiento'] == '' or $apoderado['celular'] == '' or $apoderado['parentesco'] == '' or $apoderado['extranjero'] == ''){
            return $response->setData([
                'status'=>'error',
                'registrado'=>false,
                'msg'=>'Complete los campos requeridos *'
            ]);
        }
        
        if ($validado) {

            $em = $this->getDoctrine()->getManager();

            $apoderado['extranjero'] = ($apoderado['extranjero'] == 1)?false:true;

            // BUSCAMOS A LA PERSONA EN LA BASE DE DATOS CON LOS DATOS VALIDADOS
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy([
                'carnet'=>$apoderado['carnet'],
                'complemento'=>mb_strtoupper($apoderado['complemento'], 'utf-8'),
                'paterno'=>mb_strtoupper($apoderado['paterno'], 'utf-8'),
                'materno'=>mb_strtoupper($apoderado['materno'], 'utf-8'),
                'nombre'=>mb_strtoupper($apoderado['nombres'], 'utf-8'),
                'fechaNacimiento'=>new \DateTime($apoderado['fechaNacimiento'])
            ]);

            if (is_object($persona)) {
                
                if (is_null($persona->getPaisTipo()) ) {
                    $codpais = 'NULL';
                }else{
                    $codpais = $persona->getPaisTipo()->getId();
                }
                $datosAnteriores = array(
                    'celular'=>$persona->getCelular(),
                    'es_extranjero'=>$persona->getEsExtranjero(),
                    'pais_tipo'=>$codpais ,
                    'localidad_nac'=>$persona->getLocalidadNac(),
                    'segip_id'=>$persona->getSegipId()
                );

                // ACTUALIZAMOS LOS DATOS DE LA PERSONA
                if ($persona->getSegipId() == 0) {
                    $persona->setSegipId(1);
                }
                // $persona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($apoderado['expedido']));
                $persona->setCelular($apoderado['celular']);
                $persona->setEsExtranjero($apoderado['extranjero']);
                $persona->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($apoderado['pais']));
                $persona->setLocalidadNac(mb_strtoupper($apoderado['lugar'], 'utf-8'));
                $em->flush();

                $datosNuevos = array(
                    'celular'=>$persona->getCelular(),
                    'es_extranjero'=>$persona->getEsExtranjero(),
                    'pais_tipo'=>$persona->getPaisTipo()->getId(),
                    'localidad_nac'=>$persona->getLocalidadNac(),
                    'segip_id'=>$persona->getSegipId()
                );

                $this->get('funciones')->setLogTransaccion(
                                        $persona->getId(),
                                        'persona',
                                        'U',
                                        '',
                                        $datosNuevos,
                                        $datosAnteriores,
                                        'Academico',
                                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ )));

            }else{
                // BUSCAMOS A LA PERSONA EN LA BASE DE DATOS CON LOS DATOS VALIDADOS MENOS LA FECHA DE NACIMIENTO
                $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy([
                    'carnet'=>$apoderado['carnet'],
                    'complemento'=>mb_strtoupper($apoderado['complemento'], 'utf-8'),
                    'paterno'=>mb_strtoupper($apoderado['paterno'], 'utf-8'),
                    'materno'=>mb_strtoupper($apoderado['materno'], 'utf-8'),
                    'nombre'=>mb_strtoupper($apoderado['nombres'], 'utf-8')
                ]);

                if (is_object($persona)) {

                    $fechaNacimiento = ($persona->getFechaNacimiento())?$persona->getFechaNacimiento()->format('d-m-Y'):'';

                    $datosAnteriores = array(
                        'fecha_nacimiento'=>$fechaNacimiento,
                        'celular'=>$persona->getCelular(),
                        'es_extranjero'=>$persona->getEsExtranjero(),
                        'pais_tipo'=>$persona->getPaisTipo()->getId(),
                        'localidad_nac'=>$persona->getLocalidadNac(),
                        'segip_id'=>$persona->getSegipId()
                    );

                    // ACTUALIZAMOS LOS DATOS DE LA PERSONA
                    if ($persona->getSegipId() == 0) {
                        $persona->setSegipId(1);
                    }
                    $persona->setFechaNacimiento(new \DateTime($apoderado['fechaNacimiento']));
                    // $persona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($apoderado['expedido']));
                    $persona->setCelular($apoderado['celular']);
                    $persona->setEsExtranjero($apoderado['extranjero']);
                    $persona->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($apoderado['pais']));
                    $persona->setLocalidadNac(mb_strtoupper($apoderado['lugar'], 'utf-8'));
                    $em->flush();

                    $datosAnteriores = array(
                        'fecha_nacimiento'=>$persona->getFechaNacimiento()->format('d-m-Y'),
                        'celular'=>$persona->getCelular(),
                        'es_extranjero'=>$persona->getEsExtranjero(),
                        'pais_tipo'=>$persona->getPaisTipo()->getId(),
                        'localidad_nac'=>$persona->getLocalidadNac(),
                        'segip_id'=>$persona->getSegipId()
                    );

                    $this->get('funciones')->setLogTransaccion(
                                            $persona->getId(),
                                            'persona',
                                            'U',
                                            '',
                                            $datosNuevos,
                                            $datosAnteriores,
                                            'Academico',
                                            json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ )));

                }else{
                    // BUSCAMOS A LA PERSONA EN LA BASE DE DATOS CON LOS DATOS VALIDADOS MENOS EL COMPLEMENTO
                    $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy([
                        'carnet'=>$apoderado['carnet'],
                        'paterno'=>mb_strtoupper($apoderado['paterno'], 'utf-8'),
                        'materno'=>mb_strtoupper($apoderado['materno'], 'utf-8'),
                        'nombre'=>mb_strtoupper($apoderado['nombres'], 'utf-8'),
                        'fechaNacimiento'=>new \DateTime($apoderado['fechaNacimiento'])
                    ]);

                    if (is_object($persona)) {

                        $datosAnteriores = array(
                            'complemento'=>$persona->getComplemento(),
                            'celular'=>$persona->getCelular(),
                            'es_extranjero'=>$persona->getEsExtranjero(),
                            'pais_tipo'=>$persona->getPaisTipo()->getId(),
                            'localidad_nac'=>$persona->getLocalidadNac(),
                            'segip_id'=>$persona->getSegipId()
                        );

                        // ACTUALIZAMOS LOS DATOS DE LA PERSONA
                        if ($persona->getSegipId() == 0) {
                            $persona->setSegipId(1);
                        }
                        $persona->setComplemento(mb_strtoupper($apoderado['complemento'], 'utf-8'));
                        // $persona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($apoderado['expedido']));
                        $persona->setCelular($apoderado['celular']);
                        $persona->setEsExtranjero($apoderado['extranjero']);
                        $persona->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($apoderado['pais']));
                        $persona->setLocalidadNac(mb_strtoupper($apoderado['lugar'], 'utf-8'));
                        $em->flush();

                        $datosNuevos = array(
                            'complemento'=>$persona->getComplemento(),
                            'celular'=>$persona->getCelular(),
                            'es_extranjero'=>$persona->getEsExtranjero(),
                            'pais_tipo'=>$persona->getPaisTipo()->getId(),
                            'localidad_nac'=>$persona->getLocalidadNac(),
                            'segip_id'=>$persona->getSegipId()
                        );

                        $this->get('funciones')->setLogTransaccion(
                                                $persona->getId(),
                                                'persona',
                                                'U',
                                                '',
                                                $datosNuevos,
                                                $datosAnteriores,
                                                'Academico',
                                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ )));

                    }else{
                        // BUSCAMOS A LA PERSONA EN LA BASE DE DATOS SOLO CON EL CARNET
                        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy([
                            'carnet'=>$apoderado['carnet']
                        ]);
                        
                        if (is_object($persona)) {

                            if ($persona->getSegipId() == 1) {
                                // SI EL NUMERO DE CARNET YA ESTA OCUPADO Y EL REGISTRO EN LA BASE DE DATOS TIENE VALIDACION SEGIP 1
                                // MANDAMOS UN MENSAJE INDICANDO QUE EL USUARIO SE COMUNIQUE CON EL MINISTERIO DE EDUCACION O CON EL SEGIP
                                return $response->setData([
                                    'status'=>'error',
                                    'registrado'=>false,
                                    'msg'=>'No se pudo realizar el regsitro, el número de carnet ya esta registrado con otros datos, comuniquese con el Ministerio de Educación o con el Segip para solucionar el problema'
                                ]);
                            }

                            $datosAnteriores = array(
                                'carnet'=>$persona->getCarnet()
                            );
                            
                            $persona->setCarnet($persona->getCarnet().'±');
                            $em->flush();

                            $datosNuevos = array(
                                'carnet'=>$persona->getCarnet()
                            );

                            $this->get('funciones')->setLogTransaccion(
                                                    $persona->getId(),
                                                    'persona',
                                                    'U',
                                                    '',
                                                    $datosNuevos,
                                                    $datosAnteriores,
                                                    'Academico',
                                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ )));
                                
                        }

                        $persona = null;

                        $persona = new Persona();
                        $persona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find(98));
                        // $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($apoderado['genero']));
                        $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find(3));
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
                        $persona->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($apoderado['pais']));
                        $persona->setLocalidadNac(mb_strtoupper($apoderado['lugar'], 'utf-8'));
                        $em->persist($persona);
                        $em->flush();

                        $datosNuevos = array(
                            'idioma_materno'=>$persona->getIdiomaMaterno()->getId(),
                            'genero_tipo'=>$persona->getGeneroTipo()->getId(),
                            'sangre_tipo'=>$persona->getSangreTipo()->getId(),
                            'estado_civil'=>$persona->getEstadocivilTipo()->getId(),
                            'carnet'=>$persona->getCarnet(),
                            'complemento'=>$persona->getComplemento(),
                            'celular'=>$persona->getCelular(),
                            'rda'=>$persona->getRda(),
                            'paterno'=>$persona->getPaterno(),
                            'materno'=>$persona->getMaterno(),
                            'nombre'=>$persona->getNombre(),
                            'fecha_nacimiento'=>$persona->getFechaNacimiento()->format('d-m-Y'),
                            'segip_id'=>$persona->getSegipId(),
                            'es_extranjero'=>$persona->getEsExtranjero(),
                            'pais_tipo'=>$persona->getPaisTipo()->getId(),
                            'localidad_nac'=>$persona->getLocalidadNac()
                        );

                        $this->get('funciones')->setLogTransaccion(
                                                $persona->getId(),
                                                'persona',
                                                'C',
                                                '',
                                                $datosNuevos,
                                                '',
                                                'Academico',
                                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ )));
                    }
                }
            }

            switch($apoderado['parentesco']) {
                case 1: $parentesco = array(1); break;
                case 2: $parentesco = array(2); break;
                default: $parentesco = array(3,4,5,6,7,8,9,10,11,12,13); break;
            }

            $apoderadoInscripcion = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findOneBy(array(
                'estudianteInscripcion'=>$idInscripcion,
                'persona'=>$persona->getId()
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

                // if ($apoderadoInscripcion->getApoderadoTipo()->getId() == $apoderado['parentesco']) {
                    // ACTUALIZAMOS LA RELACION APODERADO ESTUDIANTE
                    $apoderadoInscripcion->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($apoderado['parentesco']));
                    $apoderadoInscripcion->setPersona($persona);
                    $apoderadoInscripcion->setEsValidado(1);
                    $em->persist($apoderadoInscripcion);
                    $em->flush();

                    $idApoderadoInscripcion = $apoderadoInscripcion->getId();
                // }else{
                //     return $response->setData([
                //         'status'=>'error',
                //         'registrado'=>false,
                //         'msg'=>'La persona ya fue registrada como apoderado'
                //     ]);
                // }
            }

            // $apoderadoInscripcionDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findOneBy(array(
            //     'apoderadoInscripcion'=>$idApoderadoInscripcion
            // ));

            // if (!is_object($apoderadoInscripcionDatos)) {
            //     // REGISTRAMOS LOS DATOS DEL APODERADO
            //     $apoderadoInscripcionDatos = new ApoderadoInscripcionDatos();
            //     $apoderadoInscripcionDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find(98));
            //     $apoderadoInscripcionDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($apoderado['instruccion']));
            //     $apoderadoInscripcionDatos->setApoderadoInscripcion($em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($idApoderadoInscripcion));
            //     $apoderadoInscripcionDatos->setTelefono($apoderado['telefono']);
            //     $apoderadoInscripcionDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($apoderado['ocupacion']));
            //     if ($apoderado['ocupacion'] == 10035) { // OTRA OCUPACION
            //         $apoderadoInscripcionDatos->setEmpleo(mb_strtoupper($apoderado['ocupacionOtro'],'utf-8'));    
            //     }else{
            //         $apoderadoInscripcionDatos->setEmpleo('');
            //     }
            //     // $apoderadoInscripcionDatos->setObs(mb_strtoupper($apoderado['lugar'],'utf-8'));
            //     $em->persist($apoderadoInscripcionDatos);
            //     $em->flush();

            // }else{
            //     // ACTUALIZAMOS LOS DATOS DEL APODERADO
            //     $apoderadoInscripcionDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($apoderado['instruccion']));
            //     $apoderadoInscripcionDatos->setTelefono($apoderado['telefono']);
            //     $apoderadoInscripcionDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($apoderado['ocupacion']));
            //     if ($apoderado['ocupacion'] == 10035) { // OTRA OCUPACION
            //         $apoderadoInscripcionDatos->setEmpleo(mb_strtoupper($apoderado['ocupacionOtro'],'utf-8'));    
            //     }else{
            //         $apoderadoInscripcionDatos->setEmpleo('');
            //     }
            //     // $apoderadoInscripcionDatos->setObs(mb_strtoupper($apoderado['lugar'],'utf-8'));
            //     $em->persist($apoderadoInscripcionDatos);
            //     $em->flush();
            // }

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
            // $apoderadoDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findOneBy(array('apoderadoInscripcion'=>$apoderadoInscripcion->getId()));
            // if (is_object($apoderadoDatos)) {
            //     $em->remove($apoderadoDatos);
            //     $em->flush();
            // }
            // $em->remove($apoderadoInscripcion);
            // $em->flush();
            
            $apoderadoInscripcion->setEsValidado(0);
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
