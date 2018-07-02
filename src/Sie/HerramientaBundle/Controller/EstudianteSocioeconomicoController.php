<?php


namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegular;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegHablaFrec;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegNacion;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegInternet;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\PersonaDocumento;
use Sie\AppWebBundle\Entity\ApoderadoInscripcion;
use Sie\AppWebBundle\Entity\ApoderadoInscripcionDatos;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * SocioeconomicoAlternativa controller.
 *
 */
class EstudianteSocioeconomicoController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');
        $editar = $request->get('editar');

        $aInfoUeducativa = unserialize($infoUe);
        $aInfoStudent = json_decode($infoStudent, TRUE);

        //dump($aInfoUeducativa);die;

        $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfoId']['iecId']);
        $ieducativaId = $iec->getInstitucioneducativa()->getId();
        $gestion = $iec->getGestionTipo()->getId();

        $idInscripcion = $aInfoStudent['eInsId'];
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

        $estudiante = array(
            'codigoRude' => $aInfoStudent['codigoRude'],
            'estudiante' => $aInfoStudent['nombre'] . ' ' . $aInfoStudent['paterno'] . ' ' . $aInfoStudent['materno'],
            'estadoMatricula' => $inscripcion->getEstadomatriculaTipo()->getEstadomatricula()
        );

        // Registro de apoderados
        /*$apoderados = $em->createQueryBuilder()
                        ->select('p.carnet, p.paterno, p.materno, p.nombre, im.idiomaMaterno, aid.empleo, it.instruccion, at.apoderado as parentesco')
                        ->from('SieAppWebBundle:ApoderadoInscripcion','ai')
                        ->leftJoin('SieAppWebBundle:ApoderadoInscripcionDatos','aid','with','aid.apoderadoInscripcion = ai.id')
                        ->leftJoin('SieAppWebBundle:Persona','p','with','ai.persona = p.id')
                        ->leftJoin('SieAppWebBundle:ApoderadoTipo','at','with','ai.apoderadoTipo = at.id')
                        ->leftJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','ai.estudianteInscripcion = ei.id')
                        ->leftJoin('SieAppWebBundle:IdiomaMaterno','im','with','aid.idiomaMaterno = im.id')
                        ->leftJoin('SieAppWebBundle:InstruccionTipo','it','with','aid.instruccionTipo = it.id')
                        ->where('ei.id = :idInscripcion')
                        ->setParameter('idInscripcion',$idInscripcion)
                        ->getQuery()
                        ->getResult();*/

        /**
        *  Apoderados
        */
        // Padre o cualquier tutor
        $padreTutor = $this->obtenerApoderado($idInscripcion,array(1,3,4,5,6,7,8,9,10,11,12,13));
        //dump($padreTutor);
        // Madre
        $madre = $this->obtenerApoderado($idInscripcion, array(2));
        //dump($madre);die;

        $generoTipo = $em->getRepository('SieAppWebBundle:GeneroTipo')->findBy(array('id'=>array(1,2)));
        $generoTipoMadre = $em->getRepository('SieAppWebBundle:GeneroTipo')->findBy(array('id'=>array(2)));
        $idiomaMaternoTipo = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findAll();


        // Ocupaciones de tutor y de madre
        $repository = $em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo');
        $ocupacionTutorTipo = $repository->createQueryBuilder('aat')
                        ->where('aat.id = :id')
                        ->setParameter('id', $padreTutor[0]['ocupacion'])
                        ->getQuery()
                        ->getResult();

        $ocupacionMadreTipo = $repository->createQueryBuilder('aat')
                        ->where('aat.id = :id')
                        ->setParameter('id', $madre[0]['ocupacion'])
                        ->getQuery()
                        ->getResult();

        $ocupacionTipo = $em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->findBy(array('esVigente'=>true));

        //////////////////

        $instruccionTipo = $em->getRepository('SieAppWebBundle:InstruccionTipo')->findBy(array('id'=>array(2,3,4,5,6,7,8,9,10,11,99)));
        $parentescoTipoPadreTutor = $em->getRepository('SieAppWebBundle:ApoderadoTipo')->findBy(array('id'=>array(1,3,4,5,6,7,11,12,13)));  
        $parentescoTipoMadre = $em->getRepository('SieAppWebBundle:ApoderadoTipo')->findBy(array('id'=>array(2)));  
        //die;

        /*if(count($apoderados) == 0){
            $apoderados = null;
        }*/

        //Información de la institución educativa
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        //dump($ieducativaId);die;

        $institucion = $repository->createQueryBuilder('i')
                ->select('i.id ieducativaId, i.institucioneducativa ieducativa, d.id distritoId, d.distrito distrito, dp.id departamentoId, dp.departamento departamento, de.dependencia dependencia, jg.cordx cordx, jg.cordy cordy, st.id sucId')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'isuc', 'WITH', 'isuc.institucioneducativa = i.id')
                ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'jg', 'WITH', 'i.leJuridicciongeografica = jg.id')
                ->innerJoin('SieAppWebBundle:DistritoTipo', 'd', 'WITH', 'jg.distritoTipo = d.id')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dp', 'WITH', 'd.departamentoTipo = dp.id')
                ->innerJoin('SieAppWebBundle:DependenciaTipo', 'de', 'WITH', 'i.dependenciaTipo = de.id')
                ->innerJoin('SieAppWebBundle:SucursalTipo', 'st', 'WITH', 'isuc.sucursalTipo = st.id')
                ->where('i.id = :ieducativa')
                ->andWhere('st.id = :sucursal')
                ->setParameter('ieducativa', $ieducativaId)
                ->setParameter('sucursal', 0)
                ->getQuery()
                ->getResult();

        //dump($institucion);die;
        $institucion = $institucion[0];

        //Información de la/el estudiante
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $aInfoStudent['codigoRude']));

        $procedencia = $this->obtenerProcedencia($student->getCodigoRude(),$gestion);

        //Datos Socioeconómicos
        $socioeconomico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegular')->findOneBy(array('estudianteInscripcion' => $aInfoStudent['eInsId']));

        if ($socioeconomico) {
            //return $this->redirect($this->generateUrl('herramienta_alter_cursos_index'));
            if($editar == 0){
                return $this->render('SieHerramientaBundle:EstudianteSocioeconomico:opciones.html.twig', array('infoUe'=>$infoUe,'infoStudent'=>$infoStudent,'student'=>$student,'inscripcion'=>$inscripcion,'cierreOperativoRude'=>$this->verificarCierreOperativoRude($ieducativaId,$gestion,4),'socioeconomico'=>$socioeconomico));
            }else{
                return $this->render('SieHerramientaBundle:EstudianteSocioeconomico:edit.html.twig', array(
                        'socioeconomico' => $socioeconomico,
                        'institucion' => $institucion,
                        'estudiante' => $estudiante,
                        'student' => $student,
                        'inscripcion' => $inscripcion,
                        'form' => $this->editForm($idInscripcion, $gestion, $socioeconomico, $infoUe, $infoStudent)->createView(),
                        'procedencia'=>$procedencia,
                        'padreTutor'=>$padreTutor,
                        'madre'=>$madre,
                        'generoTipo'=>$generoTipo,
                        'generoTipoMadre'=>$generoTipoMadre,
                        'idiomaMaternoTipo'=>$idiomaMaternoTipo,
                        'ocupacionTutorTipo'=>$ocupacionTutorTipo,
                        'ocupacionMadreTipo'=>$ocupacionMadreTipo,
                        'ocupacionTipo'=>$ocupacionTipo,
                        'instruccionTipo'=>$instruccionTipo,
                        'parentescoTipoPadreTutor'=>$parentescoTipoPadreTutor,
                        'parentescoTipoMadre'=>$parentescoTipoMadre

                ));
            }
        } else {
            /* OPCION PARA OBTENER LOS DATOS DE UNA GESTION ANTERIOR */

            return $this->render('SieHerramientaBundle:EstudianteSocioeconomico:new.html.twig', array(
                        'socioeconomico' => $socioeconomico,
                        'institucion' => $institucion,
                        'estudiante' => $estudiante,
                        'student' => $student,
                        'inscripcion' => $inscripcion,
                        'form' => $this->newForm($idInscripcion, $gestion, $infoUe, $infoStudent)->createView(),
                        'procedencia'=>$procedencia,
                        'padreTutor'=>$padreTutor,
                        'madre'=>$madre,
                        'generoTipo'=>$generoTipo,
                        'generoTipoMadre'=>$generoTipoMadre,
                        'idiomaMaternoTipo'=>$idiomaMaternoTipo,
                        'ocupacionTutorTipo'=>$ocupacionTutorTipo,
                        'ocupacionMadreTipo'=>$ocupacionMadreTipo,
                        'ocupacionTipo'=>$ocupacionTipo,
                        'instruccionTipo'=>$instruccionTipo,
                        'parentescoTipoPadreTutor'=>$parentescoTipoPadreTutor,
                        'parentescoTipoMadre'=>$parentescoTipoMadre
            ));
        }
    }

    public function obtenerApoderado($idInscripcion, Array $tipoApoderado){
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion');

        $apoderado = $repository->createQueryBuilder('ai')
                    ->select('
                        ai.id, 
                        at.id as apoderadoTipo, 
                        p.id as idPersona, 
                        p.nombre, 
                        p.paterno, 
                        p.materno, 
                        p.carnet,
                        p.complemento,
                        p.fechaNacimiento,
                        p.segipId, 
                        gt.id as genero, 
                        p.correo, 
                        aid.id as idDatos, 
                        im.id as idiomaMaterno, 
                        it.id as instruccionTipo, 
                        aid.empleo, 
                        aid.telefono,
                        aot.id as ocupacion,
                        aid.obs')
                    ->innerJoin('SieAppWebBundle:Persona','p','with','ai.persona = p.id')
                    ->innerJoin('SieAppWebBundle:GeneroTipo','gt','with','p.generoTipo = gt.id')
                    ->innerJoin('SieAppWebBundle:ApoderadoTipo','at','with','ai.apoderadoTipo = at.id')
                    ->leftJoin('SieAppWebBundle:ApoderadoInscripcionDatos','aid','with','aid.apoderadoInscripcion = ai.id')
                    ->leftJoin('SieAppWebBundle:IdiomaMaterno','im','with','aid.idiomaMaterno = im.id')
                    ->leftJoin('SieAppWebBundle:InstruccionTipo','it','with','aid.instruccionTipo = it.id')
                    ->leftJoin('SieAppWebBundle:ApoderadoOcupacionTipo','aot','with','aid.ocupacionTipo = aot.id')
                    ->where('ai.estudianteInscripcion = :idInscripcion')
                    ->andWhere('at.id in (:tipoApoderado)')
                    ->setParameter('idInscripcion',$idInscripcion)
                    ->setParameter('tipoApoderado',$tipoApoderado)
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getResult();

                    //dump($apoderado);die;

        if(!$apoderado){
            $apoderado[] = array(
                'id'=>'nuevo',
                'apoderadoTipo'=>null,
                'idPersona'=>'nuevo',
                'nombre'=>null,
                'paterno'=>null,
                'materno'=>null,
                'carnet'=>null,
                'complemento'=>null,
                'fechaNacimiento'=>null,
                'segipId'=>null,
                'genero'=>null,
                'correo'=>null,
                'idDatos'=>'nuevo',
                'idiomaMaterno'=>null,
                'instruccionTipo'=>null,
                'empleo'=>null,
                'telefono'=>null,
                'ocupacion'=>null,
                'obs'=>null,
                'foto'=>null,
                'corregirFecha'=>false
            );
        }else{
            // Verificamos si los ids de los registros son nulos para ponerles el valor nuevo
            if(is_null($apoderado[0]['id'])){$apoderado[0]['id'] = 'nuevo'; }
            if(is_null($apoderado[0]['idPersona'])){$apoderado[0]['idPersona'] = 'nuevo'; }
            if(is_null($apoderado[0]['idDatos'])){$apoderado[0]['idDatos'] = 'nuevo'; }
            // Formateamos la fecha en d-m-Y
            $apoderado[0]['fechaNacimiento'] = date_format($apoderado[0]['fechaNacimiento'],'d-m-Y');
            // Validamos si la fecha de nacimiento tiene formato correcto y si es una fecha valida
            $anioActual = date('Y');
            $fechaNacimiento = $apoderado[0]['fechaNacimiento'];
            $anioNacimiento = explode('-',$fechaNacimiento);

            // Verificamos si es una fecha valida
            if(checkdate($anioNacimiento[1], $anioNacimiento[0], $anioNacimiento[2])){
                $anioNacimiento = $anioNacimiento[2];
                $corregirFecha = false;
                $diferenciaAnios = $anioActual - $anioNacimiento;
                if($diferenciaAnios > 100 or $diferenciaAnios < 15){
                    $corregirFecha = true;
                }
            }else{
                $corregirFecha = true;
            }
            // Verificamos si la persona tiene fotografia de ci
            $foto = $em->getRepository('SieAppWebBundle:PersonaDocumento')->findOneBy(array('personaId'=>$apoderado[0]['idPersona'],'documento'=>10));
            if($foto){
                $apoderado[0]['foto'] = $foto->getRuta();
            }else{
                $apoderado[0]['foto'] = null;
            }
            $apoderado[0]['corregirFecha'] = $corregirFecha;
        }

        return $apoderado;
    }

    public function verificarCierreOperativoRude($sie,$gestion,$operativoRude){
        $operativoRude = 4;
        $em = $this->getDoctrine()->getManager();
        $operativoRude = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findOneBy(array(
            'institucioneducativa' => $sie,
            'gestionTipoId'  => $gestion,
            'institucioneducativaOperativoLogTipo' => $operativoRude
        ));
        if($operativoRude){
            return true;
        }else{
            return false;
        }
    }

    /*
     * formulario de editar rudeal
     */

    private function newForm($idInscripcion, $gestion, $infoUe, $infoStudent) {
        $em = $this->getDoctrine()->getManager();

        //dump($idInscripcion, $gestion);die;

        $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $idInscripcion));
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' => $estudianteInscripcion->getEstudiante()));

        $paisNac = $estudiante->getPaisTipo();
        $dptoNac = $estudiante->getLugarNacTipo();
        $provNac = $estudiante->getLugarProvNacTipo();
        $localidadNac = $estudiante->getLocalidadNac();

        if($paisNac->getId() == 0 or $paisNac->getId() == null){
            $paisNac =  $em->getRepository('SieAppWebBundle:PaisTipo')->findOneBy(array('id' => 1));
        }

        //Lugar de Nacimiento
        $query = $em->createQuery(
                        'SELECT p
                FROM SieAppWebBundle:PaisTipo p
                WHERE p.id != 0
                ORDER BY p.id');
        $paisNacE = $query->getResult();

        $paisNacArray = array();
        foreach ($paisNacE as $value) {
            $paisNacArray[$value->getId()] = $value->getPais();
        }

        $query = $em->createQuery(
                        'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
                ->setParameter('nivel', 1)
                ->setParameter('lt1', $paisNac);
        $dptoNacE = $query->getResult();

        $dptoNacArray = array();
        foreach ($dptoNacE as $value) {
            $dptoNacArray[$value->getId()] = $value->getLugar();
        }

        $query = $em->createQuery(
                        'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
                ->setParameter('nivel', 2)
                ->setParameter('lt1', $dptoNac);
        $provNacE = $query->getResult();

        $provNacArray = array();
        foreach ($provNacE as $value) {
            $provNacArray[$value->getId()] = $value->getLugar();
        }

        $genero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findBy(array('id'=>array(1,2)));
        $generoArray = array();
        foreach($genero as $value){
            $generoArray[$value->getId()] = $value->getGenero();
        }

        $prov = array();
        $muni = array();
        // $cantn = array();
        // $locald = array();

        // Clasificador de discapacidades
        $especialArea = $em->getRepository('SieAppWebBundle:DiscapacidadTipo')->findBy(array('id'=>array(2,3,4,5,6,7,8,9,10)));
        $especialAreaArray = array();
        foreach ($especialArea as $ea) {
            $especialAreaArray[$ea->getId()] = $ea->getOrigendiscapacidad();
        }
        
        $form = $this->createFormBuilder()
                //->setAction($this->generateUrl('estudianteSocioeconomico_create'))
                ->add('estudianteInscripcion', 'hidden', array('data' => $idInscripcion))
                ->add('gestionId', 'hidden', array('data' => $gestion))
                ->add('infoUe', 'hidden', array('data' => $infoUe))
                ->add('infoStudent', 'hidden', array('data' => $infoStudent))

                //Datos del estudiante
                ->add('seccioniiPais', 'choice', array('data' => $paisNac ? $paisNac->getId() : 0, 'label' => 'Pais', 'required' => true, 'choices' => $paisNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','onchange'=>'dep(this.value)','title'=>'2.2. LUGAR DE NACIMIENTO - Pais')))
                ->add('seccioniiDepartamento', 'choice', array('data' => $dptoNac ? $dptoNac->getId() : 0, 'label' => 'Departamento', 'required' => false, 'choices' => $dptoNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper', 'onchange' => 'prov(this.value);','title'=>'2.2. LUGAR DE NACIMIENTO - Departamento')))
                ->add('seccioniiProvincia', 'choice', array('data' => $provNac ? $provNac->getId() : 0, 'label' => 'Provincia', 'required' => false, 'choices' => $provNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'2.2. LUGAR DE NACIMIENTO - Provincia')))
                ->add('seccioniiLocalidad', 'text', array('data' => $localidadNac,'required' => false, 'attr' => array('class' => 'form-control jupper','title'=>'2.2. LUGAR DE NACIMIENTO - Localidad')))
                // ->add('carnet', 'text', array('data' => $estudiante->getCarnetIdentidad(),'required' => true, 'attr' => array('class' => 'form-control jupper','title'=>'2.4. Carnet de identidad')))
                ->add('seccioniiOficialia', 'text', array('data' => $estudiante->getOficialia(), 'required' => false, 'attr' => array('class' => 'form-control jupper','maxlength'=>15,'title'=>'2.7. CERTIFICADO DE NACIMIENTO - Oficialia')))
                ->add('seccioniiLibro', 'text', array('data' => $estudiante->getLibro(), 'required' => false, 'attr' => array('class' => 'form-control jupper','maxlength'=>10)))
                ->add('seccioniiPartida', 'text', array('data' => $estudiante->getPartida(), 'required' => false, 'attr' => array('class' => 'form-control jupper','maxlength'=>10)))
                ->add('seccioniiFolio', 'text', array('data' => $estudiante->getFolio(), 'required' => false, 'attr' => array('class' => 'form-control jupper','maxlength'=>10)))
                ->add('seccioniiGenero', 'choice', array('data' => $estudiante->getGeneroTipo() ? $estudiante->getGeneroTipo()->getId() : 0, 'required' => false, 'choices' => $generoArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'2.6. SEXO')))

                // IV Direccion actual del estudiante 
                
                ->add('departamento', 'entity', array('label' => 'Departamento', 'required' => true, 'class' => 'SieAppWebBundle:DepartamentoTipo', 'property' => 'departamento', 'empty_value' => 'Seleccionar...',
                'query_builder' => function(EntityRepository $er){
                        return $er->createQueryBuilder('d')
                                ->where('d.id not in (:ids)')
                                ->setParameter('ids',array(0));
                     }, 'attr' => array('class' => 'form-control jupper', 'onchange' => 'listarProvincias(this.value);','title'=>'4. Departamento')))
                ->add('provincia', 'choice', array('label' => 'Provincia', 'required' => true, 'choices' => $prov, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper', 'onchange' => 'listarMunicipios(this.value)','title'=>'4. Provincia')))
                ->add('municipio', 'choice', array('label' => 'Municipio', 'required' => true, 'choices' => $muni, 'empty_value' => 'Seleccionar...','attr' => array('class' => 'form-control jupper', 'onchange' => 'listarCantones(this.value)','title'=>'4. Municipio')))
                // ->add('canton', 'choice', array('label' => 'Cantón', 'required' => true, 'choices' => $cantn, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper', 'onchange' => 'listarLocalidades(this.value)','title'=>'4. Canton')))
                // ->add('localidad', 'choice', array('label' => 'Localidad', 'required' => true, 'choices' => $locald, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'4. Localidad')))
                ->add('localidad', 'text', array('required' => true, 'attr' => array('class' => 'form-control jupper','maxlength'=>50,'title'=>'4. Localidad')))
                ->add('seccioniiiZona', 'text', array('required' => false, 'attr' => array('class' => 'form-control jupper','maxlength'=>50,'title'=>'4. Zona')))
                ->add('seccioniiiAvenida', 'text', array('required' => false, 'attr' => array('class' => 'form-control jupper','maxlength'=>50,'title'=>'4. Avenida')))
                ->add('seccioniiiNumero', 'text', array('data' => '', 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{0,5}', 'maxlength' => '5','title'=>'4. Numero de vivienda')))
                ->add('seccioniiiTelefonocelular', 'text', array('required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{0,8}', 'maxlength' => '8')))


                // V Aspectos Sociales
                // 5.1

                ->add('seccionvIdiomaNines', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'empty_value' => 'Seleccionar...',
                    'query_builder' => function(EntityRepository $er){
                        return $er->createQueryBuilder('idin')
                                ->where('idin.id not in (:ids)')
                                ->setParameter('ids',array(0,97,98));
                     }, 'attr' => array('class' => 'form-control jupper','title'=>'5.1.1. ¿Cual es el idioma que aprendio a hablar en su niñez la o el estudiante?')))

                ->add('seccionvIdioma1', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'empty_value' => 'Seleccionar...', 
                    'query_builder' => function(EntityRepository $er){
                        return $er->createQueryBuilder('idin')
                                ->where('idin.id not in (:ids)')
                                ->setParameter('ids',array(0,97,98));
                     },'attr' => array('class' => 'form-control jupper','title'=>'5.1.2. ¿Qué idiomas habla frecuentemente la o el estudiante? ')))
                ->add('seccionvIdioma2', 'entity', array('required' => false, 'label' => false, 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'empty_value' => 'Seleccionar...',
                    'query_builder' => function(EntityRepository $er){
                        return $er->createQueryBuilder('idin')
                                ->where('idin.id not in (:ids)')
                                ->setParameter('ids',array(0,97,98));
                     }, 'attr' => array('class' => 'form-control jupper')))
                ->add('seccionvIdioma3', 'entity', array('required' => false, 'label' => false, 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'empty_value' => 'Seleccionar...',
                    'query_builder' => function(EntityRepository $er){
                        return $er->createQueryBuilder('idin')
                                ->where('idin.id not in (:ids)')
                                ->setParameter('ids',array(0,97,98));
                     }, 'attr' => array('class' => 'form-control jupper')))

                ->add('seccionvEstudianteNacionoriginariaTipo', 'entity', array('multiple'=>true,'label' => false, 'required' => true, 'class' => 'SieAppWebBundle:NacionOriginariaTipo', 'property' => 'nacion_originaria', 'empty_value' => 'Seleccionar...', 
                    'query_builder' => function(EntityRepository $er){
                        return $er->createQueryBuilder('naor')
                                ->where('naor.id not in (:ids)')
                                ->setParameter('ids',array(0));
                     }, 'attr' => array('class' => 'js-example-basic-multiple jupper','style'=>'width:100%','title'=>'5.1.3. ¿Pertenece a alguna nación, pueblo indígena originario campesino o afroboliviano?')))

                // 5.2 Salud

                ->add('seccionvEstudianteEscentroSalud', 'choice', array('required' => true, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
                ->add('seccionvCantCentroSalud', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegCantCentrosaludTipo', 'property' => 'cantCentrosalud', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'5.2.2. ¿Cuántas veces fue la o el estudiante al centro de salud el año pasado?')))
                ->add('seccionvEstudianteDiscSensorial', 'choice', array('required' => true, 'label' => 'Sensorial y de la comunicación', 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
                ->add('seccionvEstudianteDiscMotriz', 'choice', array('required' => true, 'label' => 'Motriz', 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
                ->add('seccionvEstudianteDiscMental', 'choice', array('required' => true, 'label' => 'Mental', 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
                ->add('seccionvEstudianteDiscOtro','choice',array('label'=>'Otro','choices'=>$especialAreaArray,'empty_value'=>'Seleccionar...','required'=>false, 'attr'=>array('class'=>'form-control jupper','maxlength'=>50)))
                ->add('seccionvDiscTipo', 'entity', array('required' => false, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegDiscapacidadTipo', 'property' => 'discapacitadTipo', 'query_builder' => function(EntityRepository $er){
                        return $er->createQueryBuilder('regdis')
                                ->where('regdis.id not in (:ids)')
                                ->setParameter('ids',array(0));
                     }, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper')))

                // 5.3 Acceso a servicios basicos
                ->add('seccionvAguaProvieneTipo', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegAguaprovieneTipo', 'property' => 'guaproviene', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'5.3.1. El agua de su casa proviene de:')))
                ->add('seccionvEsenergiaelectrica', 'choice', array('required' => true, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
                ->add('seccionvDesagueTipo', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegDesagueTipo', 'property' => 'desague', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'5.3.3. El baño, water o letrina de su casa tiene desague a')))
                 // 5.4 Empleo
                ->add('seccionvActividadTipo', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegActividadTipo', 'property' => 'actividad', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'5.4.1. ¿Realizó la o el estudiante en los últimos 3 meses alguna de las siguientes actividades?')))
                ->add('seccionvDiasTrabajo','text',array('required'=>false,'data'=>0,'label'=>false, 'attr'=>array('class'=>'form-control jupper','maxlength'=>1)))
                ->add('seccionvRecibePago', 'choice', array('required' => true, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))

                ->add('seccionvInternetTipo', 'entity', array('multiple'=>true,'required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternetTipo', 'property' => 'accesointernetTipo', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'js-example-basic-multiple jupper','style'=>'width:100%','title'=>'5.5.1. La o el estudiante accede a Internet en:')))
                ->add('seccionvFrecInternet', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegFrecInternetTipo', 'property' => 'internet', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'5.5.2. ¿Con qué frecuencia la o el estudiante accede a internet?')))
                ->add('seccionvLlegaTipo', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegLlegaTipo', 'property' => 'llega', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'5.5.3. ¿Cómo llega la o el estudiante a la Unidad Educativa?')))
                ->add('seccionvTiempotrans', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegTiempotransTipo', 'property' => 'tiempoTransporte', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'5.5.4. En el medio de transporte señalado ¿Cuál es el tiempo máximo que demora en llegar de su casa a la Unidad Educativa o viceversa?')))

                // VI DATOS DEL PADRE
                ->add('lugarRegistro','text',array('required'=>true, 'data'=>$this->obtenerDepartamentoUe($estudianteInscripcion->getId()),'label'=>false, 'attr'=>array('class'=>'form-control jupper','maxlength'=>50,'title'=>'Lugar de registro')))
                ->add('fechaRegistro','text',array('required'=>true,'label'=>false, 'data'=> date('d-m-Y'), 'attr'=>array('class'=>'form-control jupper','maxlength'=>50,'title'=>'Fecha de registro')))

                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

        return $form;
    }

    /*
     * formulario de editar rudeal
     */

    private function editform($idInscripcion, $gestion, $socioeconomico, $infoUe, $infoStudent) {
        $em = $this->getDoctrine()->getManager();

        $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $idInscripcion));
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' => $estudianteInscripcion->getEstudiante()));

        $paisNac = $estudiante->getPaisTipo();
        $dptoNac = $estudiante->getLugarNacTipo();
        $provNac = $estudiante->getLugarProvNacTipo();
        $localidadNac = $estudiante->getLocalidadNac();

        if($paisNac->getId() == 0 or $paisNac->getId() == null){
            $paisNac =  $em->getRepository('SieAppWebBundle:PaisTipo')->findOneBy(array('id' => 1));
        }


        $lt5_id = $socioeconomico->getSeccionivLocalidadTipo()->getLugarTipo();
        $lt4_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt5_id)->getLugarTipo();
        $lt3_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt4_id)->getLugarTipo();

        $m_id = $socioeconomico->getSeccionivLocalidadTipo()->getId();
        $p_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt5_id)->getId();
        $d_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt4_id)->getId();

        

        $genero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findBy(array('id'=>array(1,2)));
        $generoArray = array();
        foreach($genero as $value){
            $generoArray[$value->getId()] = $value->getGenero();
        }

        // NACIMIENTO
        // Lugar de Nacimiento
        $query = $em->createQuery(
                        'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
                ->setParameter('nivel', 1)
                ->setParameter('lt1', $paisNac);
        $dptoNacE = $query->getResult();

        $dptoNacArray = array();
        foreach ($dptoNacE as $value) {
            $dptoNacArray[$value->getId()] = $value->getLugar();
        }

        $query = $em->createQuery(
                        'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
                ->setParameter('nivel', 2)
                ->setParameter('lt1', $dptoNac);
        $provNacE = $query->getResult();

        $provNacArray = array();
        foreach ($provNacE as $value) {
            $provNacArray[$value->getId()] = $value->getLugar();
        }

        $query = $em->createQuery(
                        'SELECT p
                FROM SieAppWebBundle:PaisTipo p
                WHERE p.id != 0
                ORDER BY p.id');
        $paisNacE = $query->getResult();

        $paisNacArray = array();
        foreach ($paisNacE as $value) {
            $paisNacArray[$value->getId()] = $value->getPais();
        }

        // DIRECCION

        $dpto = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findBy(array('id'=>array(1,2,3,4,5,6,7,8,9)));
        $dptoArray = array();
        foreach($dpto as $value){
            $dptoArray[$value->getId()] = $value->getDepartamento();
        }

        $query = $em->createQuery(
                        'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
                ->setParameter('nivel', 2)
                ->setParameter('lt1', $d_id);
        $prov = $query->getResult();

        $provArray = array();
        foreach ($prov as $value) {
            $provArray[$value->getId()] = $value->getLugar();
        }
        

        $query = $em->createQuery(
                        'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
                ->setParameter('nivel', 3)
                ->setParameter('lt1', $p_id);
        $muni = $query->getResult();

        $muniArray = array();
        foreach ($muni as $value) {
            $muniArray[$value->getId()] = $value->getLugar();
        }

        // $query = $em->createQuery(
        //                 'SELECT lt
        //         FROM SieAppWebBundle:LugarTipo lt
        //         WHERE lt.lugarNivel = :nivel
        //         AND lt.lugarTipo = :lt1
        //         ORDER BY lt.id')
        //         ->setParameter('nivel', 4)
        //         ->setParameter('lt1', $m_id);
        // $cantn = $query->getResult();

        // $cantnArray = array();
        // foreach ($cantn as $value) {
        //     $cantnArray[$value->getId()] = $value->getLugar();
        // }

        // $query = $em->createQuery(
        //                 'SELECT lt
        //         FROM SieAppWebBundle:LugarTipo lt
        //         WHERE lt.lugarNivel = :nivel
        //         AND lt.lugarTipo = :lt1
        //         ORDER BY lt.id')
        //         ->setParameter('nivel', 5)
        //         ->setParameter('lt1', $c_id);
        // $locald = $query->getResult();

        // $localdArray = array();
        // foreach ($locald as $value) {
        //     $localdArray[$value->getId()] = $value->getLugar();
        // }

        /*idiomas*/
        $idiomas = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegHablaFrec')->findBy(array('estudianteInscripcionSocioeconomicoRegular' => $socioeconomico));
        
        $idiomasArray = array();
        $cont = 1;
        foreach ($idiomas as $value) {
            $idioma_aux = $em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($value->getEstudianteInscripcionSocioeconomicoRegHablaFrecTipo()->getId());
            $idiomasArray[$cont] = $idioma_aux->getId();
            $cont++;
        }

        switch(count($idiomasArray)){
            case 1: $idioma1 = $idiomasArray[1];
                    $idioma2 = 0;
                    $idioma3 = 0;
                    break;
            case 2: $idioma1 = $idiomasArray[1];
                    $idioma2 = $idiomasArray[2];
                    $idioma3 = 0;
                    break;
            case 3: $idioma1 = $idiomasArray[1];
                    $idioma2 = $idiomasArray[2];
                    $idioma3 = $idiomasArray[3];
                    break;
            default:
                    $idioma1 = 0;
                    $idioma2 = 0;
                    $idioma3 = 0;
                    break;
        }

        // NAciones a las que pertenece el estudiante
        $naciones = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegNacion')->findBy(array('estudianteInscripcionSocioeconomicoRegular' => $socioeconomico));
        $nacionesArray = array();
        foreach ($naciones as $nac) {
            $nacionesArray[] = $nac->getNacionOriginariaTipo()->getId();
        }
        $nacionesArray = $em->createQueryBuilder()
                            ->select('norit')
                            ->from('SieAppWebBundle:NacionOriginariaTipo','norit')
                            ->where('norit.id in (:ids)')
                            ->setParameter('ids',$nacionesArray)
                            ->getQuery()
                            ->getResult();

        // Acceso a internet
        $internet = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternet')->findBy(array('estudianteInscripcionSocioeconomicoRegular' => $socioeconomico));
        $internetArray = array();
        foreach ($internet as $inte) {
            $internetArray[] = $inte->getEstudianteInscripcionSocioeconomicoRegInternetTipo()->getId();
        }
        $internetArray = $em->createQueryBuilder()
                            ->select('eisit')
                            ->from('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternetTipo','eisit')
                            ->where('eisit.id in (:ids)')
                            ->setParameter('ids',$internetArray)
                            ->getQuery()
                            ->getResult();

        // Clasificador de discapacidades
        $especialArea = $em->getRepository('SieAppWebBundle:DiscapacidadTipo')->findBy(array('id'=>array(2,3,4,5,6,7,8,9,10)));
        $especialAreaArray = array();
        foreach ($especialArea as $ea) {
            $especialAreaArray[$ea->getId()] = $ea->getOrigendiscapacidad();
        }


        $form = $this->createFormBuilder()
                ->add('estudianteInscripcion', 'hidden', array('data' => $idInscripcion))
                ->add('gestionId', 'hidden', array('data' => $gestion))
                ->add('infoUe', 'hidden', array('data' => $infoUe))
                ->add('infoStudent', 'hidden', array('data' => $infoStudent))
                ->add('socioeconomico', 'hidden', array('data' => $socioeconomico->getId()))

                //Datos del estudiante
                ->add('seccioniiPais', 'choice', array('data' => $paisNac ? $paisNac->getId() : 0, 'label' => 'Pais', 'required' => true, 'choices' => $paisNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper', 'onchange' => 'dep(this.value);','title'=>'2.2. LUGAR DE NACIMIENTO - Pais')))
                ->add('seccioniiDepartamento', 'choice', array('data' => $dptoNac ? $dptoNac->getId() : 0, 'label' => 'Departamento', 'required' => false, 'choices' => $dptoNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper', 'onchange' => 'prov(this.value);','title'=>'2.2. LUGAR DE NACIMIENTO - Departamento')))
                ->add('seccioniiProvincia', 'choice', array('data' => $provNac ? $provNac->getId() : 0, 'label' => 'Provincia', 'required' => false, 'choices' => $provNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'2.2. LUGAR DE NACIMIENTO - Provincia')))
                ->add('seccioniiLocalidad', 'text', array('data' => $localidadNac,'required' => false, 'attr' => array('class' => 'form-control jupper','maxlength'=>50,'title'=>'2.2. LUGAR DE NACIMIENTO - Localidad')))
                ->add('seccioniiOficialia', 'text', array('data' => $estudiante->getOficialia(), 'required' => false, 'attr' => array('class' => 'form-control jupper','maxlength'=>15,'title'=>'2.7. CERTIFICADO DE NACIMIENTO - Oficialia')))
                ->add('seccioniiLibro', 'text', array('data' => $estudiante->getLibro(), 'required' => false, 'attr' => array('class' => 'form-control jupper','maxlength'=>10,'title'=>'2.7. CERTIFICADO DE NACIMIENTO - Libro Nº')))
                ->add('seccioniiPartida', 'text', array('data' => $estudiante->getPartida(), 'required' => false, 'attr' => array('class' => 'form-control jupper','maxlength'=>10,'title'=>'2.7. CERTIFICADO DE NACIMIENTO - Partida Nº')))
                ->add('seccioniiFolio', 'text', array('data' => $estudiante->getFolio(), 'required' => false, 'attr' => array('class' => 'form-control jupper','maxlength'=>10,'title'=>'2.7. CERTIFICADO DE NACIMIENTO - Folio Nº')))
                ->add('seccioniiGenero', 'choice', array('data' => $estudiante->getGeneroTipo() ? $estudiante->getGeneroTipo()->getId() : 0, 'required' => false, 'choices' => $generoArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','maxlength'=>10,'title'=>'2.6. SEXO')))
                //end
                
                ->add('departamento', 'choice', array('data' => $d_id - 1, 'label' => 'Departamento', 'required' => true, 'choices' => $dptoArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper', 'onchange' => 'listarProvincias(this.value);','title'=>'4. Departamento')))
                ->add('provincia', 'choice', array('data' => $p_id, 'label' => 'Provincia', 'required' => true, 'choices' => $provArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper', 'onchange' => 'listarMunicipios(this.value)','title'=>'4. Provincia')))
                ->add('municipio', 'choice', array('data' => $m_id, 'label' => 'Municipio', 'required' => true, 'choices' => $muniArray, 'empty_value' => 'Seleccionar...','attr' => array('class' => 'form-control jupper', 'onchange' => 'listarCantones(this.value)','title'=>'4. Municipio')))
                // ->add('canton', 'choice', array('data' => $c_id, 'label' => 'Cantón', 'required' => true, 'choices' => $cantnArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper', 'onchange' => 'listarLocalidades(this.value)','title'=>'4. Canton')))
                // ->add('localidad', 'choice', array('data' => $l_id, 'label' => 'Localidad', 'required' => true, 'choices' => $localdArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'4. Localidad')))
                ->add('localidad', 'text', array('data' => $socioeconomico->getSeccionivDescLocalidad(), 'required' => true, 'attr' => array('class' => 'form-control jupper','maxlength'=>50,'title'=>'4. Localidad')))
                ->add('seccioniiiZona', 'text', array('data' => $socioeconomico->getSeccionivZona(), 'required' => false, 'attr' => array('class' => 'form-control jupper','maxlength'=>50,'title'=>'4. Zona')))
                ->add('seccioniiiAvenida', 'text', array('data' => $socioeconomico->getSeccionivAvenida(),'required' => false, 'attr' => array('class' => 'form-control jupper','maxlength'=>50, 'title'=>'4. Avenida')))
                ->add('seccioniiiNumero', 'text', array('data' => $socioeconomico->getSeccionivNumero(), 'required' => false, 'attr' => array('class' => 'form-control jupper', 'pattern' => '[0-9]{0,5}', 'maxlength' => '5','title'=>'4. Número')))
                ->add('seccioniiiTelefonocelular', 'text', array('data' => $socioeconomico->getSeccionivTelefonocelular(), 'required' => false, 'attr' => array('class' => 'form-control jupper', 'pattern' => '[0-9]{0,8}', 'maxlength' => '8')))

                // V Aspectos Sociales
                // 5.1

                ->add('seccionvIdiomaNines', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'data'=> $em->getReference('SieAppWebBundle:IdiomaTipo',$socioeconomico->getSeccionvHablaNinezTipo()->getId()),'empty_value' => 'Seleccionar...', 
                    'query_builder' => function(EntityRepository $er){
                        return $er->createQueryBuilder('idin')
                                ->where('idin.id not in (:ids)')
                                ->setParameter('ids',array(0,97,98));
                     }, 'attr' => array('class' => 'form-control jupper','title'=>'5.1.1. ¿Cual es el idioma que aprendio a hablar en su niñez la o el estudiante?')))

                ->add('seccionvIdioma1', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'data'=> $em->getReference('SieAppWebBundle:IdiomaTipo',$idioma1),'empty_value' => 'Seleccionar...',
                    'query_builder' => function(EntityRepository $er){
                        return $er->createQueryBuilder('idin')
                                ->where('idin.id not in (:ids)')
                                ->setParameter('ids',array(0,97,98));
                     }, 'attr' => array('class' => 'form-control jupper','title'=>'5.1.2. ¿Qué idiomas habla frecuentemente la o el estudiante? ')))
                ->add('seccionvIdioma2', 'entity', array('required' => false, 'label' => false, 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'data'=> $em->getReference('SieAppWebBundle:IdiomaTipo',$idioma2),'empty_value' => 'Seleccionar...', 
                    'query_builder' => function(EntityRepository $er){
                        return $er->createQueryBuilder('idin')
                                ->where('idin.id not in (:ids)')
                                ->setParameter('ids',array(0,97,98));
                     }, 'attr' => array('class' => 'form-control jupper')))
                ->add('seccionvIdioma3', 'entity', array('required' => false, 'label' => false, 'class' => 'SieAppWebBundle:IdiomaTipo', 'property' => 'idioma', 'data'=> $em->getReference('SieAppWebBundle:IdiomaTipo',$idioma3),'empty_value' => 'Seleccionar...', 
                    'query_builder' => function(EntityRepository $er){
                        return $er->createQueryBuilder('idin')
                                ->where('idin.id not in (:ids)')
                                ->setParameter('ids',array(0,97,98));
                     }, 'attr' => array('class' => 'form-control jupper')))

                ->add('seccionvEstudianteNacionoriginariaTipo','entity',array('data'=>$nacionesArray, 'multiple'=>true,'label' => false, 'required' => true, 'class' => 'SieAppWebBundle:NacionOriginariaTipo', 'property' => 'nacion_originaria', 
                    'query_builder' => function(EntityRepository $er){
                        return $er->createQueryBuilder('naor')
                                ->where('naor.id not in (:ids)')
                                ->setParameter('ids',array(0));
                     }, 'attr' => array('class' => 'js-example-basic-multiple jupper','style'=>'width:100%','title'=>'5.1.3. ¿Pertenece a alguna nación, pueblo indígena originario campesino o afroboliviano?')))

                // 5.2 Salud

                ->add('seccionvEstudianteEscentroSalud', 'choice', array('required' => true, 'data'=>$socioeconomico->getSeccionvEstudianteEscentroSalud(), 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
                ->add('seccionvCantCentroSalud', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegCantCentrosaludTipo', 'property' => 'cantCentrosalud', 'data'=>$em->getReference('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegCantCentrosaludTipo',$socioeconomico->getSeccionvCantCentrosaludTipo()->getId()) , 'attr' => array('class' => 'form-control jupper','title'=>'5.2.2. ¿Cuántas veces fue la o el estudiante al centro de salud el año pasado?')))
                ->add('seccionvEstudianteDiscSensorial', 'choice', array('required' => true, 'data'=>$socioeconomico->getSeccionvEstudianteEsdiscapacidadSensorialComunicacion(), 'label' => 'Sensorial y de la comunicación', 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
                ->add('seccionvEstudianteDiscMotriz', 'choice', array('required' => true, 'data'=>$socioeconomico->getSeccionvEstudianteEsdiscapacidadMotriz(), 'label' => 'Motriz', 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
                ->add('seccionvEstudianteDiscMental', 'choice', array('required' => true, 'data'=>$socioeconomico->getSeccionvEstudianteEsdiscapacidadMental(), 'label' => 'Mental', 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
                ->add('seccionvEstudianteDiscOtro','choice',array('label'=>'Otro', 'choices'=>$especialAreaArray, 'empty_data'=>'Seleccionar...' ,'data'=>$socioeconomico->getSeccionvEstudianteDiscapacidadotro(),'required'=>false, 'attr'=>array('class'=>'form-control jupper','maxlength'=>50)))
                ->add('seccionvDiscTipo', 'entity', array('required' => false, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegDiscapacidadTipo', 'property' => 'discapacitadTipo', 'query_builder' => function(EntityRepository $er){
                        return $er->createQueryBuilder('regdis')
                                ->where('regdis.id not in (:ids)')
                                ->setParameter('ids',array(0));
                     }, 'data'=>$em->getReference('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegDiscapacidadTipo',($socioeconomico->getSeccionvDiscapacidadTipo())?$socioeconomico->getSeccionvDiscapacidadTipo()->getId():1), 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'5.2.4. Su discapacidad es:')))

                // 5.3 Acceso a servicios basicos
                ->add('seccionvAguaProvieneTipo', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegAguaprovieneTipo', 'property' => 'guaproviene','data'=>$em->getReference('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegAguaprovieneTipo', $socioeconomico->getSeccionvAguaprovieneTipo()->getId()) , 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'5.3.1. El agua de su casa proviene de:')))
                ->add('seccionvEsenergiaelectrica', 'choice', array('required' => true, 'label' => false, 'data'=> $socioeconomico->getSeccionvEstudianteEsEnergiaelectrica() , 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
                ->add('seccionvDesagueTipo', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegDesagueTipo', 'property' => 'desague', 'data'=>$em->getReference('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegDesagueTipo', ($socioeconomico->getSeccionvDesagueTipo())?$socioeconomico->getSeccionvDesagueTipo()->getId():1 ) , 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'5.3.3. El baño, water o letrina de su casa tiene desague a')))
                 // 5.4 Empleo
                ->add('seccionvActividadTipo', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegActividadTipo', 'property' => 'actividad', 'data'=>$em->getReference('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegActividadTipo',$socioeconomico->getSeccionvActividadTipo()->getId()) , 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'5.4.1. ¿Realizó la o el estudiante en los últimos 3 meses alguna de las siguientes actividades?')))
                ->add('seccionvDiasTrabajo','text',array('required'=>false,'data'=>$socioeconomico->getSeccionvEstudianteCuantosdiastrabajo(),'label'=>false, 'attr'=>array('class'=>'form-control jupper','maxlength'=>1,'title'=>'5.4.2. La semana pasada ¿Cuántos dias trabajo o ayudo a la familia la o el estudiante?')))
                ->add('seccionvRecibePago', 'choice', array('required' => true, 'label' => false, 'data'=>$socioeconomico->getSeccionvEstudianteEspagoTrabajorealizado() ,'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))

                ->add('seccionvInternetTipo', 'entity', array('data'=>$internetArray,'multiple'=>true,'required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternetTipo', 'property' => 'accesointernetTipo', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'js-example-basic-multiple jupper','style'=>'width:100%','title'=>'5.5.1. La o el estudiante accede a Internet en:')))
                ->add('seccionvFrecInternet', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegFrecInternetTipo', 'property' => 'internet','data'=>$em->getReference('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegFrecInternetTipo', $socioeconomico->getSeccionvFrecInternetTipo()->getId()) ,'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'5.5.2. ¿Con qué frecuencia la o el estudiante accede a internet?')))
                ->add('seccionvLlegaTipo', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegLlegaTipo', 'property' => 'llega','data'=>$em->getReference('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegLlegaTipo', $socioeconomico->getSeccionvLlegaTipo()->getId()) ,'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'5.5.3. ¿Cómo llega la o el estudiante a la Unidad Educativa?')))
                ->add('seccionvTiempotrans', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegTiempotransTipo', 'property' => 'tiempoTransporte', 'data'=>$em->getReference('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegTiempotransTipo', $socioeconomico->getSeccionvTiempotransTipo()->getId()) , 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control jupper','title'=>'5.5.4. En el medio de transporte señalado ¿Cuál es el tiempo máximo que demora en llegar de su casa a la Unidad Educativa o viceversa?')))
               
               ->add('lugarRegistro','text',array('required'=>true,'label'=>false,'data'=>$socioeconomico->getLugar(), 'attr'=>array('class'=>'form-control jupper','maxlength'=>50,'title'=>'Lugar de registro')))
                ->add('fechaRegistro','text',array('required'=>true,'label'=>false, 'data'=>$socioeconomico->getFecha()->format('d-m-Y'),'attr'=>array('class'=>'form-control jupper','maxlength'=>50,'title'=>'Fecha de registro')))
                
                ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'disbled' => true)))
                ->getForm();

        return $form;
    }

    /**
     * Creates a new SocioeconomicoAlternativa entity.
     *
     */
    public function createAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            $form = $request->get('form');

            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $form['estudianteInscripcion']));

            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' => $estudianteInscripcion->getEstudiante()));

            $procedencia = $this->obtenerProcedencia($estudiante->getCodigoRude(),$form['gestionId']);
            $estudianteInscripcion->setCodUeProcedenciaId($procedencia['sie']);

            /**
             * REGISTRO DE CARNET DE IDENTIDAD
             */
            $carnet = $request->get('carnet');
            if(isset($carnet) and $carnet != ''){
                $complemento = $request->get('complemento');
                $estudiante->setCarnetIdentidad($carnet);
                $estudiante->setComplemento($complemento);
            }

            $estudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->findOneBy(array('id' => $form['seccioniiPais'])));
            $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['seccioniiDepartamento'] ? $form['seccioniiDepartamento'] : null)));
            $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['seccioniiProvincia'] ? $form['seccioniiProvincia'] : null)));
            $estudiante->setLocalidadNac(mb_strtoupper($form['seccioniiLocalidad'],'utf-8'));
            $estudiante->setOficialia($form['seccioniiOficialia']);
            $estudiante->setLibro($form['seccioniiLibro']);
            $estudiante->setPartida($form['seccioniiPartida']);
            $estudiante->setFolio($form['seccioniiFolio']);
            $estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneBy(array('id' => $form['seccioniiGenero'])));
            $em->persist($estudiante);
            $em->flush();
            
            // Registro datos socioeconomicos

            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_socioeconomico_regular');")->execute();
            $socioinscripcion = new EstudianteInscripcionSocioeconomicoRegular();
            $socioinscripcion->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($form['estudianteInscripcion']));

            $socioinscripcion->setSeccionivLocalidadTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['municipio']));
            $socioinscripcion->setSeccionivDescLocalidad($form['localidad'] ? mb_strtoupper($form['localidad'], 'utf-8') : '');
            
            
            $socioinscripcion->setSeccionivZona($form['seccioniiiZona'] ? mb_strtoupper($form['seccioniiiZona'], 'utf-8') : '');
            $socioinscripcion->setSeccionivAvenida($form['seccioniiiAvenida'] ? mb_strtoupper($form['seccioniiiAvenida'], 'utf-8') : '');
            $socioinscripcion->setSeccionivNumero($form['seccioniiiNumero'] ? $form['seccioniiiNumero'] : 0);
            $socioinscripcion->setSeccionivTelefonocelular($form['seccioniiiTelefonocelular'] ? $form['seccioniiiTelefonocelular'] : 0);
            $socioinscripcion->setSeccionvHablaNinezTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['seccionvIdiomaNines']));
            $socioinscripcion->setSeccionvEstudianteEscentroSalud($form['seccionvEstudianteEscentroSalud']);
            $socioinscripcion->setSeccionvCantCentrosaludTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegCantCentrosaludTipo')->find($form['seccionvCantCentroSalud']));
            $socioinscripcion->setSeccionvEstudianteEsdiscapacidadSensorialComunicacion($form['seccionvEstudianteDiscSensorial']);
            $socioinscripcion->setSeccionvEstudianteEsdiscapacidadMotriz($form['seccionvEstudianteDiscMotriz']);
            $socioinscripcion->setSeccionvEstudianteEsdiscapacidadMental($form['seccionvEstudianteDiscMental']);
            $socioinscripcion->setSeccionvEstudianteDiscapacidadOtro(mb_strtoupper($form['seccionvEstudianteDiscOtro'],'utf-8'));
            if(isset($form['seccionvDiscTipo'])){
                $socioinscripcion->setSeccionvDiscapacidadTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegDiscapacidadTipo')->find($form['seccionvDiscTipo']));
            }else{
                $socioinscripcion->setSeccionvDiscapacidadTipo(null);
            }
            $socioinscripcion->setSeccionvAguaProvieneTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegAguaprovieneTipo')->find($form['seccionvAguaProvieneTipo']));
            $socioinscripcion->setSeccionvEstudianteEsEnergiaelectrica($form['seccionvEsenergiaelectrica']);
            $socioinscripcion->setSeccionvDesagueTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegDesagueTipo')->find($form['seccionvDesagueTipo']));
            $socioinscripcion->setSeccionvActividadTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegActividadTipo')->find($form['seccionvActividadTipo']));
            if($form['seccionvDiasTrabajo'] == ""){ $dias = 0;}else{ $dias = $form['seccionvDiasTrabajo'];}
            $socioinscripcion->setSeccionvEstudianteCuantosdiastrabajo($dias);
            $socioinscripcion->setSeccionvEstudianteEspagoTrabajorealizado($form['seccionvRecibePago']);
            $socioinscripcion->setSeccionvFrecInternetTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegFrecInternetTipo')->find($form['seccionvFrecInternet']));
            $socioinscripcion->setSeccionvLlegaTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegLlegaTipo')->find($form['seccionvLlegaTipo']));
            $socioinscripcion->setSeccionvTiempotransTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegTiempotransTipo')->find($form['seccionvTiempotrans']));
            $socioinscripcion->setLugar(mb_strtoupper($form['lugarRegistro'],'utf-8'));
            $socioinscripcion->setFecha(new \DateTime($form['fechaRegistro']));
            $socioinscripcion->setFechaRegistro(new \DateTime('now'));
            $socioinscripcion->setFechaModificacion(new \DateTime('now'));
            $socioinscripcion->setRegistroFinalizado(1); // para verificar el estado del registro
            
            $em->persist($socioinscripcion);
            $em->flush();

            /*Registro de idiomas del estudiante*/
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_socioeconomico_reg_habla_frec');")->execute();
            $socioinscripcionhabla = new EstudianteInscripcionSocioeconomicoRegHablaFrec();
            if($form['seccionvIdioma1']){
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegular($socioinscripcion);
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegHablaFrecTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['seccionvIdioma1'] ? $form['seccionvIdioma1'] : 0));
                $socioinscripcionhabla->setFechaRegistro(new \DateTime('now'));
                $em->persist($socioinscripcionhabla);
                $em->flush();
            }
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_socioeconomico_reg_habla_frec');")->execute();
            $socioinscripcionhabla = new EstudianteInscripcionSocioeconomicoRegHablaFrec();
            if($form['seccionvIdioma2']){
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegular($socioinscripcion);
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegHablaFrecTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['seccionvIdioma2'] ? $form['seccionvIdioma2'] : 0));
                $socioinscripcionhabla->setFechaRegistro(new \DateTime('now'));
                $em->persist($socioinscripcionhabla);
                $em->flush();
            }
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_socioeconomico_reg_habla_frec');")->execute();
            $socioinscripcionhabla = new EstudianteInscripcionSocioeconomicoRegHablaFrec();
            if($form['seccionvIdioma3']){
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegular($socioinscripcion);
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegHablaFrecTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['seccionvIdioma3'] ? $form['seccionvIdioma3'] : 0));
                $socioinscripcionhabla->setFechaRegistro(new \DateTime('now'));
                $em->persist($socioinscripcionhabla);
                $em->flush();
            }

            /* Registro de nacion originaria */

            if($form['seccionvEstudianteNacionoriginariaTipo']){
                $arrayNacion = $form['seccionvEstudianteNacionoriginariaTipo'];
                for ($i=0; $i < count($form['seccionvEstudianteNacionoriginariaTipo']) ; $i++) {
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_socioeconomico_reg_nacion');")->execute();
                    $newNacion = new EstudianteInscripcionSocioeconomicoRegNacion();
                    $newNacion->setNacionOriginariaTipo($em->getRepository('SieAppWebBundle:NacionOriginariaTipo')->find($arrayNacion[$i]));
                    $newNacion->setEstudianteInscripcionSocioeconomicoRegular($socioinscripcion);
                    $newNacion->setFechaRegistro(new \DateTime('now'));
                    $em->persist($newNacion);
                    $em->flush();
                }
            }

            /* Registro de accesos a internet */

            if($form['seccionvInternetTipo']){
                $accesoInternet = $form['seccionvInternetTipo'];
                for ($i=0; $i < count($accesoInternet) ; $i++) { 
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_socioeconomico_reg_internet');")->execute();
                    $newAccesoInternet = new EstudianteInscripcionSocioeconomicoRegInternet();
                    $newAccesoInternet->setEstudianteInscripcionSocioeconomicoRegInternetTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternetTipo')->find($accesoInternet[$i]));
                    $newAccesoInternet->setEstudianteInscripcionSocioeconomicoRegular($socioinscripcion);
                    $newAccesoInternet->setFechaRegistro(new \DateTime('now'));
                    $em->persist($newAccesoInternet);
                    $em->flush();
                }
            }

            /*
             //////////////////////////////////////////////////////////////////////////
             /////////////////// Registro de apoderado TUTOR O PADRE  /////////////////
             //////////////////////////////////////////////////////////////////////////
            */

            if($request->get('t_tieneTutor') == 1){

                // Verificamos si la persona es nueva
                if($request->get('t_idPersona') == 'nuevo'){

                    $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                        'carnet'=>$request->get('t_carnet'),
                        'complemento'=>$request->get('t_complemento'),
                        'paterno'=>$request->get('t_paterno'),
                        'materno'=>$request->get('t_materno'),
                        'nombre'=>$request->get('t_nombre')
                    ));

                    // VERIFICAMOS SI LA PERSONA EXISTE
                    if($persona){
                        // SI EXISTE LA PERSONA SOLO ACTUALIZAMOS SU FECHA DE NACIMIENTO
                        $persona->setFechaNacimiento(new \DateTime($request->get('t_fechaNacimiento')));
                        $em->flush();

                        $idPersona = $persona->getId();

                    }else{

                        // VERIFICAMOS SI EL CARNET YA ESTA OCUPADO
                        $personaAnterior = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                            'carnet'=>$request->get('t_carnet'),
                            'complemento'=>$request->get('t_complemento')
                        ));

                        if($personaAnterior){
                            // SI EXISTE LA PERSONA PERO SUS DATOS NO SON IGUALES
                            // ACTUALIZAMOS EL NUMERO DE CARNET CON EL CARACTER ESPECIAL
                            $personaAnterior->setCarnet($personaAnterior->getCarnet().'±');
                            $em->flush();
                        }

                        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('persona');")->execute();
                        $nuevaPersona = new Persona();
                        $nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($request->get('t_idioma')));
                        $nuevaPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($request->get('t_genero')));
                        $nuevaPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find(7));
                        $nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find(0));
                        $nuevaPersona->setCarnet($request->get('t_carnet'));
                        $nuevaPersona->setRda(0);
                        $nuevaPersona->setPaterno(mb_strtoupper($request->get('t_paterno'),'utf-8'));
                        $nuevaPersona->setMaterno(mb_strtoupper($request->get('t_materno'),'utf-8'));
                        $nuevaPersona->setNombre(mb_strtoupper($request->get('t_nombre'),'utf-8'));
                        $nuevaPersona->setFechaNacimiento(new \DateTime($request->get('t_fechaNacimiento')));
                        $nuevaPersona->setSegipId(20);

                        $em->persist($nuevaPersona);
                        $em->flush();

                        $idPersona = $nuevaPersona->getId();
                    }
                    
                }else{

                    // Modificamos los datos de la persona
                    $actualizarPersona = $em->getRepository('SieAppWebBundle:Persona')->find($request->get('t_idPersona'));
                    if($actualizarPersona){
                        // Actualizmos los datos de la persona
                        $actualizarPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($request->get('t_genero')));
                        $actualizarPersona->setCorreo($request->get('t_correo'));
                        $em->flush();
                    }

                    $idPersona = $actualizarPersona->getId();
                }

                // Verficamos si el registro de apoderado es nuevo
                if($request->get('t_id') == 'nuevo'){
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('apoderado_inscripcion');")->execute();
                    $nuevoApoderado = new ApoderadoInscripcion();
                    $nuevoApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($request->get('t_parentesco')));
                    $nuevoApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
                    $nuevoApoderado->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['estudianteInscripcion']));
                    $em->persist($nuevoApoderado);
                    $em->flush();

                    $idApoderadoInscripcion = $nuevoApoderado->getId();
                }else{

                    $actualizarApoderado = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($request->get('t_id'));
                    if($actualizarApoderado){
                        $actualizarApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($request->get('t_parentesco')));
                        $actualizarApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
                        $em->flush();
                    }
                    $idApoderadoInscripcion = $actualizarApoderado->getId();
                }

                // Verificamos si el registro de datos de apoderado es nuevo
                if($request->get('t_idDatos') == 'nuevo'){
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('apoderado_inscripcion_datos');")->execute();
                    $nuevoApoderadoDatos = new ApoderadoInscripcionDatos();
                    $nuevoApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($request->get('t_idioma')));
                    $nuevoApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($request->get('t_instruccion')));
                    $nuevoApoderadoDatos->setApoderadoInscripcion($em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($idApoderadoInscripcion));
                    $nuevoApoderadoDatos->setTelefono($request->get('t_telefono'));
                    //$nuevoApoderadoDatos->setTieneocupacion($request->get('t_empleo'));
                    //$nuevoApoderadoDatos->setActividadTipo($em->getRepository('SieAppWebBundle:ApoderadoActividadTipo')->find($request->get('t_actividad')));
                    $nuevoApoderadoDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($request->get('t_ocupacion')));
                    $nuevoApoderadoDatos->setObs(mb_strtoupper($request->get('t_ocupacionOtro'),'utf-8'));
                    $em->persist($nuevoApoderadoDatos);
                    $em->flush();
                }else{
                    $actualizarApoderadoDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->find($request->get('t_idDatos'));
                    if($actualizarApoderadoDatos){
                        $actualizarApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($request->get('t_idioma')));
                        $actualizarApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($request->get('t_instruccion')));
                        $actualizarApoderadoDatos->setTelefono($request->get('t_telefono'));
                        //$actualizarApoderadoDatos->setTieneocupacion($request->get('t_empleo'));
                        //$actualizarApoderadoDatos->setActividadTipo($em->getRepository('SieAppWebBundle:ApoderadoActividadTipo')->find($request->get('t_actividad')));
                        $actualizarApoderadoDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($request->get('t_ocupacion')));
                        $actualizarApoderadoDatos->setObs(mb_strtoupper($request->get('t_ocupacionOtro'),'utf-8'));
                        $em->flush();
                    }
                }
            }else{
                $apod = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion'=>$estudianteInscripcion->getId(),'apoderadoTipo'=>array(1,3,4,5,6,7,8,9,10,11,12,13,0)));
                foreach ($apod as $a) {
                    $apoderadoDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findOneBy(array('apoderadoInscripcion'=>$a->getId()));
                    if($apoderadoDatos){
                        $em->remove($apoderadoDatos);
                        $em->flush();
                    }
                    $em->remove($a);
                    $em->flush();
                }
            }

            /*
             //////////////////////////////////////////////////////////////////////////
             /////////////////// Registro de la MADRE  ////////////////////////////////
             //////////////////////////////////////////////////////////////////////////
            */

            if($request->get('m_tieneMadre') == 1){

                // Verificamos si la persona es nueva
                if($request->get('m_idPersona') == 'nuevo'){

                    
                    $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                        'carnet'=>$request->get('m_carnet'),
                        'complemento'=>$request->get('m_complemento'),
                        'paterno'=>$request->get('m_paterno'),
                        'materno'=>$request->get('m_materno'),
                        'nombre'=>$request->get('m_nombre')
                    ));

                    // VERIFICAMOS SI LA PERSONA EXISTE
                    if($persona){
                        // SI EXISTE LA PERSONA SOLO ACTUALIZAMOS SU FECHA DE NACIMIENTO
                        $persona->setFechaNacimiento(new \DateTime($request->get('m_fechaNacimiento')));
                        $em->flush();

                        $idPersona = $persona->getId();

                    }else{

                        // VERIFICAMOS SI EL CARNET YA ESTA OCUPADO
                        $personaAnterior = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                            'carnet'=>$request->get('m_carnet'),
                            'complemento'=>$request->get('m_complemento')
                        ));

                        if($personaAnterior){
                            // SI EXISTE LA PERSONA PERO SUS DATOS NO SON IGUALES
                            // ACTUALIZAMOS EL NUMERO DE CARNET CON EL CARACTER ESPECIAL
                            $personaAnterior->setCarnet($personaAnterior->getCarnet().'±');
                            $em->flush();
                        }

                        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('persona');")->execute();
                        $nuevaPersona = new Persona();
                        $nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($request->get('m_idioma')));
                        $nuevaPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($request->get('m_genero')));
                        $nuevaPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find(7));
                        $nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find(0));
                        $nuevaPersona->setCarnet($request->get('m_carnet'));
                        $nuevaPersona->setComplemento($request->get('m_complemento'));
                        $nuevaPersona->setRda(0);
                        $nuevaPersona->setPaterno(mb_strtoupper($request->get('m_paterno'),'utf-8'));
                        $nuevaPersona->setMaterno(mb_strtoupper($request->get('m_materno'),'utf-8'));
                        $nuevaPersona->setNombre(mb_strtoupper($request->get('m_nombre'),'utf-8'));
                        $nuevaPersona->setFechaNacimiento(new \DateTime($request->get('m_fechaNacimiento')));
                        $nuevaPersona->setSegipId(20);
                        $em->persist($nuevaPersona);
                        $em->flush();

                        $idPersona = $nuevaPersona->getId();

                    }



                }else{

                    // Modificamos los datos de la persona
                    $actualizarPersona = $em->getRepository('SieAppWebBundle:Persona')->find($request->get('m_idPersona'));
                    if($actualizarPersona){
                        // Actualizmos los datos de la persona
                        $actualizarPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($request->get('m_genero')));
                        $actualizarPersona->setCorreo($request->get('m_correo'));
                        $em->flush();
                    }

                    $idPersona = $actualizarPersona->getId();
                }

                // Verficamos si el registro de apoderado es nuevo
                if($request->get('m_id') == 'nuevo'){
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('apoderado_inscripcion');")->execute();
                    $nuevoApoderado = new ApoderadoInscripcion();
                    $nuevoApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($request->get('m_parentesco')));
                    $nuevoApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
                    $nuevoApoderado->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['estudianteInscripcion']));
                    $em->persist($nuevoApoderado);
                    $em->flush();

                    $idApoderadoInscripcion = $nuevoApoderado->getId();
                }else{

                    $actualizarApoderado = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($request->get('m_id'));
                    if($actualizarApoderado){
                        $actualizarApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($request->get('m_parentesco')));
                        $actualizarApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
                        $em->flush();
                    }
                    $idApoderadoInscripcion = $actualizarApoderado->getId();
                }

                // Verificamos si el registro de datos de apoderado es nuevo
                if($request->get('m_idDatos') == 'nuevo'){
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('apoderado_inscripcion_datos');")->execute();
                    $nuevoApoderadoDatos = new ApoderadoInscripcionDatos();
                    $nuevoApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($request->get('m_idioma')));
                    $nuevoApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($request->get('m_instruccion')));
                    $nuevoApoderadoDatos->setApoderadoInscripcion($em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($idApoderadoInscripcion));
                    $nuevoApoderadoDatos->setTelefono($request->get('m_telefono'));
                    //$nuevoApoderadoDatos->setTieneocupacion($request->get('m_empleo'));
                    //$nuevoApoderadoDatos->setActividadTipo($em->getRepository('SieAppWebBundle:ApoderadoActividadTipo')->find($request->get('m_actividad')));
                    $nuevoApoderadoDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($request->get('m_ocupacion')));
                    $nuevoApoderadoDatos->setObs(mb_strtoupper($request->get('m_ocupacionOtro'),'utf-8'));
                    $em->persist($nuevoApoderadoDatos);
                    $em->flush();
                }else{
                    $actualizarApoderadoDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->find($request->get('m_idDatos'));
                    if($actualizarApoderadoDatos){
                        $actualizarApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($request->get('m_idioma')));
                        $actualizarApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($request->get('m_instruccion')));
                        $actualizarApoderadoDatos->setTelefono($request->get('m_telefono'));
                        //$actualizarApoderadoDatos->setTieneocupacion($request->get('m_empleo'));
                        //$actualizarApoderadoDatos->setActividadTipo($em->getRepository('SieAppWebBundle:ApoderadoActividadTipo')->find($request->get('m_actividad')));
                        $actualizarApoderadoDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($request->get('m_ocupacion')));
                        $actualizarApoderadoDatos->setObs(mb_strtoupper($request->get('m_ocupacionOtro'),'utf-8'));
                        $em->flush();
                    }
                }

                
            }else{
                $apod = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion'=>$estudianteInscripcion->getId(),'apoderadoTipo'=>array(2)));
                foreach ($apod as $a) {
                    $apoderadoDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findOneBy(array('apoderadoInscripcion'=>$a->getId()));
                    if($apoderadoDatos){
                        $em->remove($apoderadoDatos);
                        $em->flush();
                    }
                    $em->remove($a);
                    $em->flush();
                }
            }

            
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente.');
            return $this->render('SieHerramientaBundle:EstudianteSocioeconomico:opciones.html.twig', array('infoUe'=>$form['infoUe'],'infoStudent'=>$form['infoStudent'],'student'=>$estudiante,'inscripcion'=>$estudianteInscripcion,'cierreOperativoRude'=>$this->verificarCierreOperativoRude($estudianteInscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getid(),$form['gestionId'],4),'actualizar'=>true, 'socioeconomico'=>$socioinscripcion));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron registrados.');
            return $this->render('SieHerramientaBundle:EstudianteSocioeconomico:opciones.html.twig', array('infoUe'=>$form['infoUe'],'infoStudent'=>$form['infoStudent'],'student'=>$estudiante,'inscripcion'=>$estudianteInscripcion,'cierreOperativoRude'=>$this->verificarCierreOperativoRude($estudianteInscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getid(),$form['gestionId'],4)));
        }
        
    }

    /**
     * Edits an existing SocioeconomicoAlternativa entity.
     *
     */
    public function updateAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            $form = $request->get('form');

            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $form['estudianteInscripcion']));

            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' => $estudianteInscripcion->getEstudiante()));

            $procedencia = $this->obtenerProcedencia($estudiante->getCodigoRude(),$form['gestionId']);
            $estudianteInscripcion->setCodUeProcedenciaId($procedencia['sie']);

            /**
             * REGISTRO DE CARNET DE IDENTIDAD
             */
            $carnet = $request->get('carnet');
            if(isset($carnet) and $carnet != ''){
                $complemento = $request->get('complemento');
                $estudiante->setCarnetIdentidad($carnet);
                $estudiante->setComplemento($complemento);
            }

            $estudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->findOneBy(array('id' => $form['seccioniiPais'])));
            $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['seccioniiDepartamento'] ? $form['seccioniiDepartamento'] : null)));
            $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['seccioniiProvincia'] ? $form['seccioniiProvincia'] : null)));
            $estudiante->setLocalidadNac(mb_strtoupper($form['seccioniiLocalidad'],'utf-8'));
            $estudiante->setOficialia(mb_strtoupper($form['seccioniiOficialia'],'utf-8'));
            $estudiante->setLibro(mb_strtoupper($form['seccioniiLibro'],'utf-8'));
            $estudiante->setPartida(mb_strtoupper($form['seccioniiPartida'],'utf-8'));
            $estudiante->setFolio(mb_strtoupper($form['seccioniiFolio'],'utf-8'));
            $estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneBy(array('id' => $form['seccioniiGenero'])));
            $em->persist($estudiante);
            $em->flush();

            $socioId = $form['socioeconomico'];

            $socioinscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegular')->findOneById($socioId);
            $socioinscripcion->setSeccionivLocalidadTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['municipio']));
            $socioinscripcion->setSeccionivDescLocalidad($form['localidad'] ? mb_strtoupper($form['localidad'], 'utf-8') : '');
            $socioinscripcion->setSeccionivZona($form['seccioniiiZona'] ? mb_strtoupper($form['seccioniiiZona'], 'utf-8') : '');
            $socioinscripcion->setSeccionivAvenida($form['seccioniiiAvenida'] ? mb_strtoupper($form['seccioniiiAvenida'], 'utf-8') : '');
            $socioinscripcion->setSeccionivNumero($form['seccioniiiNumero'] ? $form['seccioniiiNumero'] : 0);
            $socioinscripcion->setSeccionivTelefonocelular($form['seccioniiiTelefonocelular'] ? $form['seccioniiiTelefonocelular'] : 0);
            $socioinscripcion->setSeccionvHablaNinezTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['seccionvIdiomaNines']));
            $socioinscripcion->setSeccionvEstudianteEscentroSalud($form['seccionvEstudianteEscentroSalud']);
            $socioinscripcion->setSeccionvCantCentrosaludTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegCantCentrosaludTipo')->find($form['seccionvCantCentroSalud']));
            $socioinscripcion->setSeccionvEstudianteEsdiscapacidadSensorialComunicacion($form['seccionvEstudianteDiscSensorial']);
            $socioinscripcion->setSeccionvEstudianteEsdiscapacidadMotriz($form['seccionvEstudianteDiscMotriz']);
            $socioinscripcion->setSeccionvEstudianteEsdiscapacidadMental($form['seccionvEstudianteDiscMental']);
            $socioinscripcion->setSeccionvEstudianteDiscapacidadOtro(mb_strtoupper($form['seccionvEstudianteDiscOtro'],'utf-8'));
            if(isset($form['seccionvDiscTipo'])){
                $socioinscripcion->setSeccionvDiscapacidadTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegDiscapacidadTipo')->find($form['seccionvDiscTipo']));
            }else{
                $socioinscripcion->setSeccionvDiscapacidadTipo(null);
            }
            $socioinscripcion->setSeccionvAguaProvieneTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegAguaprovieneTipo')->find($form['seccionvAguaProvieneTipo']));
            $socioinscripcion->setSeccionvEstudianteEsEnergiaelectrica($form['seccionvEsenergiaelectrica']);
            $socioinscripcion->setSeccionvDesagueTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegDesagueTipo')->find($form['seccionvDesagueTipo']));
            $socioinscripcion->setSeccionvActividadTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegActividadTipo')->find($form['seccionvActividadTipo']));
            if($form['seccionvDiasTrabajo'] == ""){ $dias = 0;}else{ $dias = $form['seccionvDiasTrabajo'];}
            $socioinscripcion->setSeccionvEstudianteCuantosdiastrabajo($dias);
            $socioinscripcion->setSeccionvEstudianteEspagoTrabajorealizado($form['seccionvRecibePago']);
            $socioinscripcion->setSeccionvFrecInternetTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegFrecInternetTipo')->find($form['seccionvFrecInternet']));
            $socioinscripcion->setSeccionvLlegaTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegLlegaTipo')->find($form['seccionvLlegaTipo']));
            $socioinscripcion->setSeccionvTiempotransTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegTiempotransTipo')->find($form['seccionvTiempotrans']));
            $socioinscripcion->setLugar(mb_strtoupper($form['lugarRegistro'],'utf-8'));
            $socioinscripcion->setFecha(new \DateTime($form['fechaRegistro']));
            //$socioinscripcion->setFechaRegistro(new \DateTime($form['fechaRegistro']));
            $socioinscripcion->setFechaModificacion(new \DateTime('now'));
            $socioinscripcion->setRegistroFinalizado(1); // para verificar el estado del registro
            $em->persist($socioinscripcion);
            $em->flush();



            /*Registro de idiomas del estudiante*/
            /*eliminar idiomas*/
            $socioinscripcionhablaDelete = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegHablaFrec')->findBy(array('estudianteInscripcionSocioeconomicoRegular' => $socioinscripcion));
            
            foreach ($socioinscripcionhablaDelete as $value) {
                $em->remove($value);
            }
            $em->flush();
            // REgistrar idiomas
            
            $socioinscripcionhabla = new EstudianteInscripcionSocioeconomicoRegHablaFrec();
            if($form['seccionvIdioma1']){
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegular($socioinscripcion);
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegHablaFrecTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['seccionvIdioma1'] ? $form['seccionvIdioma1'] : 0));
                $socioinscripcionhabla->setFechaRegistro(new \DateTime('now'));
                $socioinscripcionhabla->setFechaModificacion(new \DateTime('now'));
                $em->persist($socioinscripcionhabla);
                $em->flush();
            }
            $socioinscripcionhabla = new EstudianteInscripcionSocioeconomicoRegHablaFrec();
            if($form['seccionvIdioma2']){
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegular($socioinscripcion);
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegHablaFrecTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['seccionvIdioma2'] ? $form['seccionvIdioma2'] : 0));
                $socioinscripcionhabla->setFechaRegistro(new \DateTime('now'));
                $socioinscripcionhabla->setFechaModificacion(new \DateTime('now'));
                $em->persist($socioinscripcionhabla);
                $em->flush();
            }
            $socioinscripcionhabla = new EstudianteInscripcionSocioeconomicoRegHablaFrec();
            if($form['seccionvIdioma3']){
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegular($socioinscripcion);
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegHablaFrecTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['seccionvIdioma3'] ? $form['seccionvIdioma3'] : 0));
                $socioinscripcionhabla->setFechaRegistro(new \DateTime('now'));
                $socioinscripcionhabla->setFechaModificacion(new \DateTime('now'));
                $em->persist($socioinscripcionhabla);
                $em->flush();
            }

            /* Registro de nacion originaria */
            // Eliminar naciones originarias
            $nacionoriginariaDelete = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegNacion')->findBy(array('estudianteInscripcionSocioeconomicoRegular' => $socioinscripcion));
            
            foreach ($nacionoriginariaDelete as $value) {
                $em->remove($value);
            }
            $em->flush();
            // Registrar naciones originarias

            if($form['seccionvEstudianteNacionoriginariaTipo']){
                $arrayNacion = $form['seccionvEstudianteNacionoriginariaTipo'];
                for ($i=0; $i < count($form['seccionvEstudianteNacionoriginariaTipo']) ; $i++) { 
                    $newNacion = new EstudianteInscripcionSocioeconomicoRegNacion();
                    $newNacion->setNacionOriginariaTipo($em->getRepository('SieAppWebBundle:NacionOriginariaTipo')->find($arrayNacion[$i]));
                    $newNacion->setEstudianteInscripcionSocioeconomicoRegular($socioinscripcion);
                    $newNacion->setFechaRegistro(new \DateTime('now'));
                    $newNacion->setFechaModificacion(new \DateTime('now'));
                    $em->persist($newNacion);
                    $em->flush();
                }
            }

            /* Registro de accesos a internet */
            // Eliminacion de accesos a internet
            $accesoInternetDelete = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternet')->findBy(array('estudianteInscripcionSocioeconomicoRegular' => $socioinscripcion));
            
            foreach ($accesoInternetDelete as $value) {
                $em->remove($value);
            }
            $em->flush();
            // Registro de accesos a internet

            if($form['seccionvInternetTipo']){
                $accesoInternet = $form['seccionvInternetTipo'];
                for ($i=0; $i < count($accesoInternet) ; $i++) { 
                    $newAccesoInternet = new EstudianteInscripcionSocioeconomicoRegInternet();
                    $newAccesoInternet->setEstudianteInscripcionSocioeconomicoRegInternetTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternetTipo')->find($accesoInternet[$i]));
                    $newAccesoInternet->setEstudianteInscripcionSocioeconomicoRegular($socioinscripcion);
                    $newAccesoInternet->setFechaRegistro(new \DateTime('now'));
                    $newAccesoInternet->setFechaModificacion(new \DateTime('now'));
                    $em->persist($newAccesoInternet);
                    $em->flush();
                }
            }


            /*
             //////////////////////////////////////////////////////////////////////////
             /////////////////// Registro de apoderado TUTOR O PADRE  /////////////////
             //////////////////////////////////////////////////////////////////////////
            */

            if($request->get('t_tieneTutor') == 1){

                // Verificamos si la persona es nueva
                if($request->get('t_idPersona') == 'nuevo'){

                    $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                        'carnet'=>$request->get('t_carnet'),
                        'complemento'=>$request->get('t_complemento'),
                        'paterno'=>$request->get('t_paterno'),
                        'materno'=>$request->get('t_materno'),
                        'nombre'=>$request->get('t_nombre')
                    ));

                    // VERIFICAMOS SI LA PERSONA EXISTE
                    if($persona){
                        // SI EXISTE LA PERSONA SOLO ACTUALIZAMOS SU FECHA DE NACIMIENTO
                        $persona->setFechaNacimiento(new \DateTime($request->get('t_fechaNacimiento')));
                        $em->flush();

                        $idPersona = $persona->getId();

                    }else{

                        // VERIFICAMOS SI EL CARNET YA ESTA OCUPADO
                        $personaAnterior = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                            'carnet'=>$request->get('t_carnet'),
                            'complemento'=>$request->get('t_complemento')
                        ));

                        if($personaAnterior){
                            // SI EXISTE LA PERSONA PERO SUS DATOS NO SON IGUALES
                            // ACTUALIZAMOS EL NUMERO DE CARNET CON EL CARACTER ESPECIAL
                            $personaAnterior->setCarnet($persona->getCarnet().'±');
                            $em->flush();
                        }

                        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('persona');")->execute();
                        $nuevaPersona = new Persona();
                        $nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($request->get('t_idioma')));
                        $nuevaPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($request->get('t_genero')));
                        $nuevaPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find(7));
                        $nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find(0));
                        $nuevaPersona->setCarnet($request->get('t_carnet'));
                        $nuevaPersona->setComplemento($request->get('t_complemento'));
                        $nuevaPersona->setRda(0);
                        $nuevaPersona->setPaterno(mb_strtoupper($request->get('t_paterno'),'utf-8'));
                        $nuevaPersona->setMaterno(mb_strtoupper($request->get('t_materno'),'utf-8'));
                        $nuevaPersona->setNombre(mb_strtoupper($request->get('t_nombre'),'utf-8'));
                        $nuevaPersona->setFechaNacimiento(new \DateTime($request->get('t_fechaNacimiento')));
                        $nuevaPersona->setSegipId(20);

                        $em->persist($nuevaPersona);
                        $em->flush();

                        $idPersona = $nuevaPersona->getId();
                    }
                    
                }else{

                    // Modificamos los datos de la persona
                    $actualizarPersona = $em->getRepository('SieAppWebBundle:Persona')->find($request->get('t_idPersona'));
                    if($actualizarPersona){
                        // Actualizmos los datos de la persona
                        $actualizarPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($request->get('t_genero')));
                        $actualizarPersona->setCorreo($request->get('t_correo'));
                        $em->flush();
                    }

                    $idPersona = $actualizarPersona->getId();
                }

                // Verficamos si el registro de apoderado es nuevo
                if($request->get('t_id') == 'nuevo'){
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('apoderado_inscripcion');")->execute();
                    $nuevoApoderado = new ApoderadoInscripcion();
                    $nuevoApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($request->get('t_parentesco')));
                    $nuevoApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
                    $nuevoApoderado->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['estudianteInscripcion']));
                    $em->persist($nuevoApoderado);
                    $em->flush();

                    $idApoderadoInscripcion = $nuevoApoderado->getId();
                }else{

                    $actualizarApoderado = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($request->get('t_id'));
                    if($actualizarApoderado){
                        $actualizarApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($request->get('t_parentesco')));
                        $actualizarApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
                        $em->flush();
                    }
                    $idApoderadoInscripcion = $actualizarApoderado->getId();
                }

                // Verificamos si el registro de datos de apoderado es nuevo
                if($request->get('t_idDatos') == 'nuevo'){
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('apoderado_inscripcion_datos');")->execute();
                    $nuevoApoderadoDatos = new ApoderadoInscripcionDatos();
                    $nuevoApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($request->get('t_idioma')));
                    $nuevoApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($request->get('t_instruccion')));
                    $nuevoApoderadoDatos->setApoderadoInscripcion($em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($idApoderadoInscripcion));
                    $nuevoApoderadoDatos->setTelefono($request->get('t_telefono'));
                    //$nuevoApoderadoDatos->setTieneocupacion($request->get('t_empleo'));
                    //$nuevoApoderadoDatos->setActividadTipo($em->getRepository('SieAppWebBundle:ApoderadoActividadTipo')->find($request->get('t_actividad')));
                    $nuevoApoderadoDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($request->get('t_ocupacion')));
                    $nuevoApoderadoDatos->setObs(mb_strtoupper($request->get('t_ocupacionOtro'),'utf-8'));
                    $em->persist($nuevoApoderadoDatos);
                    $em->flush();
                }else{
                    $actualizarApoderadoDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->find($request->get('t_idDatos'));
                    if($actualizarApoderadoDatos){
                        $actualizarApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($request->get('t_idioma')));
                        $actualizarApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($request->get('t_instruccion')));
                        $actualizarApoderadoDatos->setTelefono($request->get('t_telefono'));
                        //$actualizarApoderadoDatos->setTieneocupacion($request->get('t_empleo'));
                        //$actualizarApoderadoDatos->setActividadTipo($em->getRepository('SieAppWebBundle:ApoderadoActividadTipo')->find($request->get('t_actividad')));
                        $actualizarApoderadoDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($request->get('t_ocupacion')));
                        $actualizarApoderadoDatos->setObs(mb_strtoupper($request->get('t_ocupacionOtro'),'utf-8'));
                        $em->flush();
                    }
                }
            }else{
                $apod = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion'=>$estudianteInscripcion->getId(),'apoderadoTipo'=>array(1,3,4,5,6,7,8,9,10,11,12,13,0)));
                foreach ($apod as $a) {
                    $apoderadoDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findOneBy(array('apoderadoInscripcion'=>$a->getId()));
                    if($apoderadoDatos){
                        $em->remove($apoderadoDatos);
                        $em->flush();
                    }
                    $em->remove($a);
                    $em->flush();
                }
            }

            /*
             //////////////////////////////////////////////////////////////////////////
             /////////////////// Registro de la MADRE  ////////////////////////////////
             //////////////////////////////////////////////////////////////////////////
            */

            if($request->get('m_tieneMadre') == 1){

                // Verificamos si la persona es nueva
                if($request->get('m_idPersona') == 'nuevo'){

                    
                    $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                        'carnet'=>$request->get('m_carnet'),
                        'complemento'=>$request->get('m_complemento'),
                        'paterno'=>$request->get('m_paterno'),
                        'materno'=>$request->get('m_materno'),
                        'nombre'=>$request->get('m_nombre')
                    ));

                    // VERIFICAMOS SI LA PERSONA EXISTE
                    if($persona){
                        // SI EXISTE LA PERSONA SOLO ACTUALIZAMOS SU FECHA DE NACIMIENTO
                        $persona->setFechaNacimiento(new \DateTime($request->get('m_fechaNacimiento')));
                        $em->flush();

                        $idPersona = $persona->getId();

                    }else{

                        // VERIFICAMOS SI EL CARNET YA ESTA OCUPADO
                        $personaAnterior = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                            'carnet'=>$request->get('m_carnet'),
                            'complemento'=>$request->get('m_complemento')
                        ));

                        if($personaAnterior){
                            // SI EXISTE LA PERSONA PERO SUS DATOS NO SON IGUALES
                            // ACTUALIZAMOS EL NUMERO DE CARNET CON EL CARACTER ESPECIAL
                            $personaAnterior->setCarnet($persona->getCarnet().'±');
                            $em->flush();
                        }

                        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('persona');")->execute();
                        $nuevaPersona = new Persona();
                        $nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($request->get('m_idioma')));
                        $nuevaPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($request->get('m_genero')));
                        $nuevaPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find(7));
                        $nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find(0));
                        $nuevaPersona->setCarnet($request->get('m_carnet'));
                        $nuevaPersona->setComplemento($request->get('m_complemento'));
                        $nuevaPersona->setRda(0);
                        $nuevaPersona->setPaterno(mb_strtoupper($request->get('m_paterno'),'utf-8'));
                        $nuevaPersona->setMaterno(mb_strtoupper($request->get('m_materno'),'utf-8'));
                        $nuevaPersona->setNombre(mb_strtoupper($request->get('m_nombre'),'utf-8'));
                        $nuevaPersona->setFechaNacimiento(new \DateTime($request->get('m_fechaNacimiento')));
                        $nuevaPersona->setSegipId(20);
                        $em->persist($nuevaPersona);
                        $em->flush();

                        $idPersona = $nuevaPersona->getId();

                    }

                }else{

                    // Modificamos los datos de la persona
                    $actualizarPersona = $em->getRepository('SieAppWebBundle:Persona')->find($request->get('m_idPersona'));
                    if($actualizarPersona){
                        // Actualizmos los datos de la persona
                        $actualizarPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($request->get('m_genero')));
                        $actualizarPersona->setCorreo($request->get('m_correo'));
                        $em->flush();
                    }

                    $idPersona = $actualizarPersona->getId();
                }

                // Verficamos si el registro de apoderado es nuevo
                if($request->get('m_id') == 'nuevo'){
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('apoderado_inscripcion');")->execute();
                    $nuevoApoderado = new ApoderadoInscripcion();
                    $nuevoApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($request->get('m_parentesco')));
                    $nuevoApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
                    $nuevoApoderado->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['estudianteInscripcion']));
                    $em->persist($nuevoApoderado);
                    $em->flush();

                    $idApoderadoInscripcion = $nuevoApoderado->getId();
                }else{

                    $actualizarApoderado = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($request->get('m_id'));
                    if($actualizarApoderado){
                        $actualizarApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($request->get('m_parentesco')));
                        $actualizarApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
                        $em->flush();
                    }
                    $idApoderadoInscripcion = $actualizarApoderado->getId();
                }

                // Verificamos si el registro de datos de apoderado es nuevo
                if($request->get('m_idDatos') == 'nuevo'){
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('apoderado_inscripcion_datos');")->execute();
                    $nuevoApoderadoDatos = new ApoderadoInscripcionDatos();
                    $nuevoApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($request->get('m_idioma')));
                    $nuevoApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($request->get('m_instruccion')));
                    $nuevoApoderadoDatos->setApoderadoInscripcion($em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($idApoderadoInscripcion));
                    $nuevoApoderadoDatos->setTelefono($request->get('m_telefono'));
                    //$nuevoApoderadoDatos->setTieneocupacion($request->get('m_empleo'));
                    //$nuevoApoderadoDatos->setActividadTipo($em->getRepository('SieAppWebBundle:ApoderadoActividadTipo')->find($request->get('m_actividad')));
                    $nuevoApoderadoDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($request->get('m_ocupacion')));
                    $nuevoApoderadoDatos->setObs(mb_strtoupper($request->get('m_ocupacionOtro'),'utf-8'));
                    $em->persist($nuevoApoderadoDatos);
                    $em->flush();
                }else{
                    $actualizarApoderadoDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->find($request->get('m_idDatos'));
                    if($actualizarApoderadoDatos){
                        $actualizarApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($request->get('m_idioma')));
                        $actualizarApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($request->get('m_instruccion')));
                        $actualizarApoderadoDatos->setTelefono($request->get('m_telefono'));
                        //$actualizarApoderadoDatos->setTieneocupacion($request->get('m_empleo'));
                        //$actualizarApoderadoDatos->setActividadTipo($em->getRepository('SieAppWebBundle:ApoderadoActividadTipo')->find($request->get('m_actividad')));
                        $actualizarApoderadoDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($request->get('m_ocupacion')));
                        $actualizarApoderadoDatos->setObs(mb_strtoupper($request->get('m_ocupacionOtro'),'utf-8'));
                        $em->flush();
                    }
                }
            }else{
                $apod = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion'=>$estudianteInscripcion->getId(),'apoderadoTipo'=>array(2)));
                foreach ($apod as $a) {
                    $apoderadoDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findOneBy(array('apoderadoInscripcion'=>$a->getId()));
                    if($apoderadoDatos){
                        $em->remove($apoderadoDatos);
                        $em->flush();
                    }
                    $em->remove($a);
                    $em->flush();
                }
            }

            
            
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            return $this->render('SieHerramientaBundle:EstudianteSocioeconomico:opciones.html.twig', array('infoUe'=>$form['infoUe'],'infoStudent'=>$form['infoStudent'],'student'=>$estudiante,'inscripcion'=>$estudianteInscripcion,'cierreOperativoRude'=>$this->verificarCierreOperativoRude($estudianteInscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getid(),$form['gestionId'],4),'actualizar'=>true,'socieconomico'=>$socioinscripcion));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron actualizados.');
            return $this->render('SieHerramientaBundle:EstudianteSocioeconomico:opciones.html.twig', array('infoUe'=>$form['infoUe'],'infoStudent'=>$form['infoStudent'],'student'=>$estudiante,'inscripcion'=>$estudianteInscripcion,'cierreOperativoRude'=>$this->verificarCierreOperativoRude($estudianteInscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getid(),$form['gestionId'],4)));
        }     
    }

    /*
     * Funciones para actualizar lugar de nacimiento
    */
    public function departamentosNacAction($pais){
        $em = $this->getDoctrine()->getManager();

        $condition = array('lugarNivel' => 1, 'paisTipoId' => $pais);

        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy($condition);
        $departamento = array();
        foreach ($dep as $d) {
            $departamento[$d->getId()] = $d->getLugar();
        }
        $dto = $departamento;
        $response = new JsonResponse();
        return $response->setData(array('departamento' => $dto));
    }

    public function provinciasNacAction($departamento) {
        $em = $this->getDoctrine()->getManager();
        $prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $departamento));
        $provincia = array();
        foreach ($prov as $p) {
            $provincia[$p->getid()] = $p->getlugar();
        }
        $response = new JsonResponse();
        return $response->setData(array('provincia' => $provincia));
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarprovinciasAction($dpto) {
        try {
            $em = $this->getDoctrine()->getManager();


            $query = $em->createQuery(
                            'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                    ->setParameter('nivel', 2)
                    ->setParameter('lt1', $dpto + 1);
            $provincias = $query->getResult();

            $provinciasArray = array();
            foreach ($provincias as $c) {
                $provinciasArray[$c->getId()] = $c->getLugar();
            }

            $response = new JsonResponse();
            return $response->setData(array('listaprovincias' => $provinciasArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarmunicipiosAction($prov) {
        try {
            $em = $this->getDoctrine()->getManager();


            $query = $em->createQuery(
                            'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                    ->setParameter('nivel', 3)
                    ->setParameter('lt1', $prov);
            $municipios = $query->getResult();

            $municipiosArray = array();
            foreach ($municipios as $c) {
                $municipiosArray[$c->getId()] = $c->getLugar();
            }

            $response = new JsonResponse();
            return $response->setData(array('listamunicipios' => $municipiosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarcantonesAction($muni) {
        try {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQuery(
                            'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                    ->setParameter('nivel', 4)
                    ->setParameter('lt1', $muni);
            $cantones = $query->getResult();

            $cantonesArray = array();
            foreach ($cantones as $c) {
                $cantonesArray[$c->getId()] = $c->getLugar();
            }

            $response = new JsonResponse();
            return $response->setData(array('listacantones' => $cantonesArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarlocalidadesAction($cantn) {
        try {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQuery(
                            'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                    ->setParameter('nivel', 5)
                    ->setParameter('lt1', $cantn);
            $localidades = $query->getResult();

            $localidadesArray = array();
            foreach ($localidades as $c) {
                $localidadesArray[$c->getId()] = $c->getLugar();
            }

            $response = new JsonResponse();
            return $response->setData(array('listalocalidades' => $localidadesArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     
    public function listarDepartamentosNacAction($pais) {
        try {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQuery(
                            'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                    ->setParameter('nivel', 1)
                    ->setParameter('lt1', $pais);
            $departamentos = $query->getResult();

            $departamentosArray = array();
            foreach ($departamentos as $c) {
                $departamentosArray[$c->getId()] = $c->getLugar();
            }

            dump($departamentosArray);die;

            $response = new JsonResponse();
            return $response->setData(array('listadptosnac' => $departamentosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }
    */

    public function obtenerProcedencia($rude, $gestion){
        $em = $this->getDoctrine()->getManager();
        $procedencia = $em->createQueryBuilder()
                        ->select('ie.id, ie.institucioneducativa')
                        ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                        ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                        ->innerJoin('SieAppWebBundle:EstadomatriculaTipo','emt','with','ei.estadomatriculaTipo = emt.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                        ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
                        ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                        ->where('e.codigoRude = :rude')
                        ->andWhere('gt.id = :gestion')
                        ->andWhere('emt.id in (:ids)')
                        ->setParameter('rude',$rude)
                        ->setParameter('gestion',$gestion-1)
                        ->setParameter('ids',array(5,11,55))
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getResult();

        if($procedencia){
            $arrayProcedencia = array('sie'=>$procedencia[0]['id'],'institucion'=>$procedencia[0]['institucioneducativa']);
        }else{
            $arrayProcedencia = array('sie'=>0,'institucion'=>'');
        }

        return $arrayProcedencia;
    }

    /*
    * FUNCION AJAX PARA BUSCAR PERSONA APODERADO
    */
    public function buscarPersonaAction(Request $request){
        try {
            $carnet = $request->get('carnet');
            $complemento = $request->get('complemento');
            // $complemento = ($request->get('complemento') != "")?$request->get('complemento'):0;
            $paterno = $request->get('paterno');
            $materno = $request->get('materno');
            $nombre = $request->get('nombre');
            $fechaNacimiento = $request->get('fechaNacimiento');
            

            $respuesta = $this->get('sie_app_web.segip')->verificarPersona($carnet, $complemento, $paterno, $materno, $nombre, $fechaNacimiento);

            //dump($respuesta);die;

            $respuesta = false;

            if($respuesta){
                $data['status'] = 200;
                $data['persona'] = array(
                    'carnet'=> $carnet,
                    'complemento'=> $complemento,
                    'paterno'=> $paterno,
                    'materno'=> $materno,
                    'nombre'=> $nombre,
                    'fecha_nacimiento'=> $fechaNacimiento
                );
            }else{
                $data['status'] = 404;
            }

            $response = new JsonResponse();
            $response->setData($data);
            return $response;
        } catch (Exception $e) {
            
        }
    }

    /*
    * FUNCION AJAX PARA BUSCAR ACTIVIDADES DE ACUERDO A SI TRABAJA O NO
    
    public function buscarActividadAction(Request $request){
        try {
            $trabaja = $request->get('trabaja');
            
            $em = $this->getDoctrine()->getManager();
            if($trabaja == 1){
                $repository = $em->getRepository('SieAppWebBundle:ApoderadoActividadTipo');
                $apoderadoActividad = $repository->createQueryBuilder('aat')
                                ->where('aat.id not in (:ids)')
                                ->setParameter('ids', array(2000,2001,2002,2003,2004))
                                ->getQuery()
                                ->getResult();
            }else{
                $repository = $em->getRepository('SieAppWebBundle:ApoderadoActividadTipo');
                $apoderadoActividad = $repository->createQueryBuilder('aat')
                                ->where('aat.id in (:ids)')
                                ->setParameter('ids', array(2000,2001,2002,2003,2004))
                                ->getQuery()
                                ->getResult();
            }

                        
            $array = array();
            foreach ($apoderadoActividad as $aa) {
                $array[$aa->getId()] = $aa->getActividad();
            }
            
            $response = new JsonResponse();
            $response->setData($array);
            return $response;
        } catch (Exception $e) {
            
        }
    }*/

    /*
    * FUNCION AJAX PARA BUSCAR ACTIVIDADES
    */
    public function buscarOcupacionesAction(Request $request){
        try {
            $texto = mb_strtoupper($request->get('texto'),'utf-8');
            
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo');
            $apoderadoActividad = $repository->createQueryBuilder('aat')
                            ->where('aat.ocupacion LIKE :texto')
                            ->setParameter('texto','%'.$texto.'%')
                            //->setMaxResults(50)
                            ->getQuery()
                            ->getResult();


                        
            $array = array();
            $array[null] = 'Seleccionar...';
            foreach ($apoderadoActividad as $aa) {
                $array[$aa->getId()] = $aa->getOcupacion();
            }
            
            $response = new JsonResponse();
            $response->setData($array);
            return $response;
        } catch (Exception $e) {
            
        }
    }

    private function obtenerDepartamentoUe($inscripcionId){
        $em = $this->getDoctrine()->getManager();
        return 'LA PAZ';
    }

}
