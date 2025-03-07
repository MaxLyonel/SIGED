<?php


namespace Sie\HerramientaBundle\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Rude;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegHablaFrec;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegNacion;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegInternet;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\RudeDiscapacidadGrado;
use Sie\AppWebBundle\Entity\RudeIdioma;
use Sie\AppWebBundle\Entity\RudeCentroSalud;
use Sie\AppWebBundle\Entity\RudeServicioBasico;
use Sie\AppWebBundle\Entity\RudeAccesoInternet;
use Sie\AppWebBundle\Entity\RudeMesesTrabajados;
use Sie\AppWebBundle\Entity\RudeActividad;
use Sie\AppWebBundle\Entity\RudeTurnoTrabajo;
use Sie\AppWebBundle\Entity\RudeRecibioPago;
use Sie\AppWebBundle\Entity\RudeMedioTransporte;
use Sie\AppWebBundle\Entity\RudeAbandono;
// use Sie\AppWebBundle\Entity\RudeApoderadoInscripcion;
use Sie\AppWebBundle\Entity\ApoderadoInscripcion;
use Sie\AppWebBundle\Entity\ApoderadoInscripcionDatos;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\EstadoCivilTipo;
use Sie\AppWebBundle\Entity\ObservacionExtranjeroTipo;
use Sie\AppWebBundle\Entity\RudeObservacionExtranjero;
use Sie\AppWebBundle\Entity\RudeExtranjero;

use Sie\AppWebBundle\Entity\EstudiantePersonaDiplomatico;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * SocioeconomicoAlternativa controller.
 *
 */
class InfoEstudianteRudeNuevoController extends Controller {

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

        $infoUe = $request->get('infoUe'); //? información de la unidad educativa
        $infoStudent = $request->get('infoStudent'); //? información del estudiante
        $editar = $request->get('editar');

        $aInfoUeducativa = unserialize($infoUe);
        $aInfoStudent = json_decode($infoStudent, TRUE);

        $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfoId']['iecId']);
        $sie = $iec->getInstitucioneducativa()->getId();
        $gestion = $iec->getGestionTipo()->getId();

        $idInscripcion = $aInfoStudent['eInsId'];
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $estudiante = $inscripcion->getEstudiante();

        $rude = $em->getRepository('SieAppWebBundle:Rude')->findOneBy(array(
            'estudianteInscripcion'=>$inscripcion->getId()
        ));
        // dump($estudiante);
        // dump($idInscripcion);
        // dump($rude);die;
        //obtenemos los datos de la tabla estado civil tipo
        $estadoCivilData = $em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findAll();
        if(!is_object($rude)){
            /**
             * OBTENEMOS UN REGISTRO ANTERIOR
             */
            $rudeAnterior = $em->createQueryBuilder()
                        ->select('r')
                        ->from('SieAppWebBundle:Rude','r')
                        ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','r.estudianteInscripcion = ei.id')
                        ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                        ->where('e.id = :estudiante')
                        ->setParameter('estudiante', $estudiante->getId())
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getResult();

            if(count($rudeAnterior) == 1){

                $rudeAnterior = $rudeAnterior[0];

                $rude = clone $rudeAnterior;
                $rude->setEstudianteInscripcion($inscripcion);
                $em->persist($rude);
                $em->flush();

            }else{
                
                $jg = $em->createQueryBuilder()
                            ->select('jg')
                            ->from('SieAppWebBundle:JurisdiccionGeografica','jg')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','ie.leJuridicciongeografica = jg.id')
                            ->where('ie.id = :sie')
                            ->setParameter('sie', $sie)
                            ->getQuery()
                            ->getResult();

                $direccion = 'ZONA '.$jg[0]->getZona().' '.$jg[0]->getDireccion();

                $rude = new Rude();
                $rude->setEstudianteInscripcion($inscripcion);
                $rude->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find(1));
                $rude->setFechaRegistro(new \DateTime('now'));
                $rude->setLugarRegistroRude($direccion);
                $em->persist($rude);
                $em->flush();
            }
            
        }
        $rude_ext = $em->getRepository('SieAppWebBundle:RudeExtranjero')->findOneBy(array('rude'=>$rude->getId()));
        
        // OBTENER APODERADOS DEL ESTUDIANTE
        // PADRE
        $padre = $this->obtenerApoderado($idInscripcion,array(1));
        $formPadre = $this->createFormApoderado($rude, $idInscripcion, $padre[0]); 
        
        // MADRE
        //dump($formPadre);die;
        $madre = $this->obtenerApoderado($idInscripcion,array(2));
        $formMadre = $this->createFormApoderado($rude, $idInscripcion, $madre[0]);
        
        // TUTOR
        $tutor = $this->obtenerApoderado($idInscripcion,$this->obtenerCatalogo($rude, 'apoderado_tipo'));
        $formTutor = $this->createFormApoderado($rude, $idInscripcion, $tutor[0]);
                
        // TUTOR EXTRAORDINARIO
        $tutorExt = $this->obtenerApoderado($idInscripcion,array(14));
        $formTutorExt = $this->createFormApoderado($rude, $idInscripcion, $tutorExt[0]);
        $ayudaComplemento = ["Complementito","Contenido del complemento, no se refiere al lugar de expedición del documento."];
        $swextranjero = $estudiante->getPaisTipo()->getId();
        // dump($ayudaComplemento);
        // dump($formTutor);
        // dump($formTutorExt);die;
        $obsexttipo = $em->getRepository('SieAppWebBundle:ObservacionExtranjeroTipo')->findAll();
        $rudeObs = $em->getRepository('SieAppWebBundle:RudeObservacionExtranjero')->findBy(['rude' => $rude->getId(),]);
        return $this->render('SieHerramientaBundle:InfoEstudianteRudeNuevo:index.html.twig', [
            'sie'=>$sie,
            'estudiante'=>$estudiante,
            'formEstudiante'=>$this->createFormEstudiante($rude, $estudiante, $rude_ext)->createView(),
            'formDireccion'=>$this->createFormDireccion($rude)->createView(),
            'formSocioeconomico'=>$this->createFormSocioeconomico($rude)->createView(),
            'formConQuienVive'=>$this->createFormConQuienVive($rude, $rude_ext)->createView(),
            'formPadre'=>$formPadre->createView(),
            'formMadre'=>$formMadre->createView(),
            'formTutor'=>$formTutor->createView(),
            'formTutorExt'=>$formTutorExt->createView(),
            'padre'=>$padre[0],
            'madre'=>$madre[0],
            'tutor'=>$tutor[0],
            'te_tutor'=>$tutorExt[0],
            'ayudaComplemento'=>$ayudaComplemento,
            'formLugar'=>$this->createFormLugar($rude)->createView(),
            'inscripcion'=>$inscripcion,
            'rude'=>$rude,
            'swext'=>$swextranjero,
            'rudeobs'=>$rudeObs,
            'obsexttipo'=>$obsexttipo,
            'estadoCivilData'=>$estadoCivilData,
        ]);
    }

    /**
     * DATOS DE LA O EL ESTUDIANTE
     */
    private function createFormEstudiante($rude, $e, $rude_ext){

        $em = $this->getDoctrine()->getManager();
        $pais = $e->getPaisTipo()->getId();
        // dump($e->getPaisTipo()); die;
        if($pais == 1){
            $departamento = '79354';
            $provincia = 11;
            if($e->getLugarNacTipo() != null){
                $departamento = $e->getLugarNacTipo()->getId();
            }
            if($e->getLugarProvNacTipo() != null){
                $provincia = $e->getLugarProvNacTipo()->getId();
            }
        }else{
            $departamento = '79354';
            $provincia = 11;
        }
        $departamentos = array();
        $provincias = array();

        if($pais == 1){
            $condition = array('lugarNivel' => 1, 'paisTipoId' => $pais);
            $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy($condition);
            foreach ($dep as $d) {
                $departamentos[$d->getId()] = $d->getLugar(); //? obtiene departamentos
            }

            $prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $departamento));
            foreach ($prov as $p) {
                $provincias[$p->getid()] = $p->getlugar();
            }
        }

        // DISCAPACIDAD DEL ESTUDIANTE
        $discapacidadEstudiante = $em->getRepository('SieAppWebBundle:RudeDiscapacidadGrado')->findOneBy(array('rude'=>$rude->getId()));
        $gradosArray = array();
        if($discapacidadEstudiante){
            // SI LA DISCAPACDIDAD ES VISUAL ENTONCES SOLO MOSTRAMOS CIEETOS GRADOS DE DISCAPACIDAD
            if($discapacidadEstudiante->getDiscapacidadTipo()->getId() == 10){
                $gradoDiscapacidad = $em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->findBy(array('id'=>array(6,5)));
            }else{
                $gradoDiscapacidad = $em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->findBy(array('id'=>array(1,2,7,8)));
            }

            foreach ($gradoDiscapacidad as $gd) {
                $gradosArray[] = $gd->getId();
            }
        }

        // LUGAR DE NACIMIENTO
        $departamentoNacimiento = $em->getRepository('SieAppWebBundle:LugarTipo')->find($departamento);
        $provinciaNacimiento = $em->getRepository('SieAppWebBundle:LugarTipo')->find($provincia);
        // dump($rude_ext->getCodigoDocumento());
        // dump($rude_ext);
        // die;
        $form = $this->createFormBuilder()
            ->add('rudeId', 'hidden', array('data' => $rude->getId(),'mapped'=>false))
            ->add('estudianteId', 'hidden', array('data' => $e->getId()))
            ->add('pais', 'entity', array(
                    'class' => 'SieAppWebBundle:PaisTipo',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('p')
                                ->where('p.id != 0')
                                ->orderBy('p.pais', 'ASC')
                        ;
                    },
                    'empty_value' => 'Seleccionar...',
                    'required'=>true,
                    'property' => 'pais',
                    'data'=>$e->getPaisTipo()
                ))
            ->add('departamento', 'choice', array('required'=>false,'empty_value'=>'Seleccionar', 'choices'=>$departamentos, 'data'=>$departamento))
            ->add('provincia', 'choice', array('required'=>false,'empty_value'=>'Seleccionar', 'choices'=>$provincias, 'data'=>$provincia))
            ->add('localidadNac', 'text', array('required'=>false,'required'=>true, 'data'=>$e->getLocalidadNac()))

            ->add('codigoRude', 'text', array('required' => false, 'data'=>$e->getCodigoRude()))
            ->add('carnet', 'text', array('required' => false, 'data'=>$e->getCarnetIdentidad()))
            ->add('complemento', 'text', array('required' => false, 'data'=>$e->getComplemento()))
            ->add('fechaNacimiento', 'text', array('required' => false, 'data'=>$e->getFechaNacimiento()->format('d-m-Y')))
            ->add('sexo', 'entity', array(
                    'class' => 'SieAppWebBundle:GeneroTipo',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('gt')
                                ->where('gt.id != 3');
                    },
                    'empty_value' => 'Seleccionar...',
                    'required' => true,
                    'data'=>$e->getGeneroTipo()
                ))
            ->add('oficialia', 'text', array('required' => false, 'data'=>$e->getOficialia()))
            ->add('libro', 'text', array('required' => false, 'data'=>$e->getLibro()))
            ->add('partida', 'text', array('required' => false, 'data'=>$e->getPartida()))
            ->add('folio', 'text', array('required' => false, 'data'=>$e->getFolio()))

            ->add('extCi', 'checkbox',  array('mapped'=>false, 'required'=>false, 'label' => false,'attr'=> array('checked' => $rude_ext ? $rude_ext->getCiExtranjero() : false)))
            ->add('extCiDip', 'checkbox',  array('mapped'=>false, 'required'=>false, 'label' => false,'attr'=> array('checked'=>$rude_ext ? $rude_ext->getCiDiplomatico() : false)))
            ->add('extCn', 'checkbox',  array('mapped'=>false, 'required'=>false, 'label' => false,'attr'=> array('checked'=>$rude_ext ? $rude_ext->getCnExtranjero() : false)))
            ->add('extDni', 'checkbox',  array('mapped'=>false, 'required'=>false, 'label' => false,'attr'=> array('checked'=>$rude_ext ? $rude_ext->getDni() : false)))
            ->add('extPas', 'checkbox',  array('mapped'=>false, 'required'=>false, 'label' => false,'attr'=> array('checked'=>$rude_ext ? $rude_ext->getPasaporte() : false)))
            ->add('extDecJur', 'checkbox',  array('mapped'=>false, 'required'=>false, 'label' => false,'attr'=> array('checked'=>$rude_ext ? $rude_ext->getDeclaracion() : false)))
            ->add('extCodDoc', 'text', array('required' => false, 'data'=>$rude_ext ? $rude_ext->getCodigoDocumento() : ''))
            ->add('extArchDecJur', FileType::class, [
                'label' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Por favor, sube un archivo PDF, JPEG o PNG.',
                    ]),
                ],
                'attr' => [
                    'accept' => '.pdf,.jpeg,.jpg,.png',
                    'id' => 'extArchDecJur',
                ],
            ])
            
            ->add('tieneDiscapacidad', 'choice', array(
                    'choices'=>array(true=>'Si', false=>'No'),
                    'data'=> ($discapacidadEstudiante)? true: false,
                    'required'=>true,
                    'multiple'=>false,
                    'empty_value'=>false,
                    'expanded'=>true
                ))
            ->add('carnetIbc', 'text', array('required' => false, 'data'=>$e->getCarnetIbc()))
            ->add('discapacidadId', 'hidden', array('required' => false, 'data'=>($discapacidadEstudiante)?$discapacidadEstudiante->getId():'nuevo'))
            ->add('discapacidad', 'entity', array(
                    'class' => 'SieAppWebBundle:DiscapacidadTipo',
                    'query_builder' => function (EntityRepository $e) use ($rude) {
                        return $e->createQueryBuilder('dt')
                                ->where('dt.id in (:ids)')
                                ->setParameter('ids', $this->obtenerCatalogo($rude, 'discapacidad_tipo'));
                    },
                    'empty_value' => 'Seleccionar...',
                    'required' => true,
                    'data'=>($discapacidadEstudiante)?$discapacidadEstudiante->getDiscapacidadTipo():'',
                    'mapped'=>false
                ))
            ->add('gradoDiscapacidad', 'entity', array(
                    'class' => 'SieAppWebBundle:GradoDiscapacidadTipo',
                    'query_builder' => function (EntityRepository $e) use ($gradosArray) {
                        return $e->createQueryBuilder('gdt')
                                ->where('gdt.id in (:ids)')
                                ->setParameter('ids', $gradosArray);
                    },
                    'empty_value' => 'Seleccionar...',
                    'required' => true,
                    'data'=>($discapacidadEstudiante)?$discapacidadEstudiante->getGradoDiscapacidadTipo():''
                ))
            ->add('departamentoNacimiento', 'hidden', array('data'=>$departamentoNacimiento->getLugar()))
            ->add('provinciaNacimiento', 'hidden', array('data'=>$provinciaNacimiento->getLugar()))
            ->getForm();

        return $form;
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
     * GUARDAR DATOS DEL FORMULARIO ESTUDIANTE
     */

    public function saveFormEstudianteAction(Request $request){

        $data = $request->request->all();
        $form = $data['form'];
        $files = $request->files->all();
        $archivo = $files['form']['extArchDecJur'];
        $sie = $this->session->get('ie_id');
        $gestion = $this->session->get('currentyear');
        // dump($this->session->all());
        $em = $this->getDoctrine()->getManager();
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['estudianteId']);
        $rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['rudeId']);
        //Para guardar datos del la discapacidad en la tabla Rude
        $rude->setTieneDiscapacidad($form['tieneDiscapacidad']); //? actualiza el campo tiene discapacidad (RUDE)

        $carnetIbc=empty($form['carnetIbc'])?0:1;
        $rude->setTieneCarnetDiscapacidad($carnetIbc); //? actualizar el capo tiene carnet discapacidad (RUDE)

            if(isset($form['oficialia'])){
                $estudiante->setOficialia($form['oficialia']); //? (ESTUDIANTE)
            }
            if(isset($form['libro'])){
                $estudiante->setLibro($form['libro']);
            }
            if(isset($form['partida'])){
                $estudiante->setPartida($form['partida']);
            }
            if(isset($form['folio'])){                
                $estudiante->setFolio($form['folio']);               
            }
            $cedulaTipo = ($estudiante->getPaisTipo()->getId()==1)?1:2;
            $estudiante->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find($cedulaTipo));

        $em->persist($estudiante);
        $em->flush();
        //EXTRANJEROS
        $rudeext = $em->getRepository('SieAppWebBundle:RudeExtranjero')->findBy(array('rude' => $form['rudeId']));
        // dump($rudeext);
        if ( count($rudeext) == 0 and (isset($form['extCi']) or isset($form['extCiDip']) or  isset($form['extCn']) or  isset($form['extDni']) or  isset($form['extPas']) or  isset($form['extDecJur']) or  isset($form['extCodDoc']))){
            $rudeext = new RudeExtranjero();
            $rudeext->setRude($em->getRepository('SieAppWebBundle:Rude')->find($form['rudeId']));
            $rudeext->setCiExtranjero(isset($form['extCi']) ? 1:0);
            $rudeext->setCiDiplomatico(isset($form['extCiDip']) ? 1:0);
            $rudeext->setCnExtranjero(isset($form['extCn']) ? 1:0);
            $rudeext->setDni(isset($form['extDni']) ? 1:0);
            $rudeext->setPasaporte(isset($form['extPas']) ? 1:0);
            $rudeext->setDeclaracion(isset($form['extDecJur']) ? 1:0);
            
            if ($archivo !== null) {
                $nArcMemo = $this->guardarArch($sie, $form['rudeId'], $gestion, 'RUDE_EXT', $archivo);
                $rudeext->setArchivo($nArcMemo);
            } else {
                $rudeext->setArchivo('');
            }
            $rudeext->setCodigoDocumento($form['extCodDoc']);
            $em->persist($rudeext);
            $em->flush();
        } else {
            $rudeext = reset($rudeext);

            // Configuración común
            $rudeext->setCiExtranjero(isset($form['extCi']) ? 1 : 0);
            $rudeext->setCiDiplomatico(isset($form['extCiDip']) ? 1 : 0);
            $rudeext->setCnExtranjero(isset($form['extCn']) ? 1 : 0);
            $rudeext->setDni(isset($form['extDni']) ? 1 : 0);
            $rudeext->setPasaporte(isset($form['extPas']) ? 1 : 0);
            $rudeext->setDeclaracion(isset($form['extDecJur']) ? 1:0);
            
            if ($archivo !== null) {
                $nArcMemo = $this->guardarArch($sie, $form['rudeId'], $gestion, 'RUDE_EXT', $archivo);
                $rudeext->setArchivo($nArcMemo);
            } else {
                $rudeext->setArchivo('');
            }
            $rudeext->setCodigoDocumento($form['extCodDoc']);
        
            // Persistir y flush
            $em->persist($rudeext);
            $em->flush();
        }

        // DISCAPACIDADES
        if($form['tieneDiscapacidad'] == true){
            // Si tiene discapacidad lo registramos o Actualizamos
            $discapacidadId = $form['discapacidadId'];
            if($discapacidadId == 'nuevo'){
                $discapacidad = new RudeDiscapacidadGrado();
                $discapacidad->setRude($em->getRepository('SieAppWebBundle:Rude')->find($form['rudeId']));
                $discapacidad->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->find($form['discapacidad']));
                $discapacidad->setGradoDiscapacidadTipo($em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->find($form['gradoDiscapacidad']));
                $discapacidad->setFechaRegistro(new \DateTime('now'));
                $em->persist($discapacidad);
                $em->flush();
            }else{
                $discapacidad = $em->getRepository('SieAppWebBundle:RudeDiscapacidadGrado')->find($discapacidadId);
                if($discapacidad){
                    $discapacidad->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->find($form['discapacidad']));
                    $discapacidad->setGradoDiscapacidadTipo($em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->find($form['gradoDiscapacidad']));
                    $discapacidad->setFechaModificacion(new \DateTime('now'));
                    $em->persist($discapacidad);
                    $em->flush();
                }
            }

            $estudiante->setCarnetIbc($form['carnetIbc']);
            $em->flush($estudiante);
        }else{
            // Si no titne discapacidad lo eliminamos
            $eliminar = $em->createQueryBuilder()
                ->delete('')
                ->from('SieAppWebBundle:RudeDiscapacidadGrado','rdg')
                ->where('rdg.rude = :rudeId')
                ->setParameter('rudeId', $form['rudeId'])
                ->getQuery()
                ->getResult();

            $estudiante->setCarnetIbc('');
            $em->flush($estudiante);
        }

        // Registro de paso 1
        if($rude->getRegistroFinalizado() < 1){
            $rude->setRegistroFinalizado(1);
            $em->flush();
        }
        

        $response = new JsonResponse();
        return $response->setData(['msg'=>true]);
    }


    private function guardarArch($sie, $codigoRude, $gestion, $prefijo, $file )
    {
        if ($file !== null) {
            $type = $file->getMimeType();
            $size = $file->getSize();
            $name = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $new_name = $prefijo . date('YmdHis') .$codigoRude. '.' . $extension;

            // GUARDAR EL ARCHIVO
            $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/rude_decjurada/' . $gestion . '/' . $sie . '/' . $codigoRude;
            if (!file_exists($directorio)) {
                mkdir($directorio, 0777, true);
            }

            $archivador = $directorio . '/' . $new_name;

            if (!$file->move($directorio, $new_name)) {
                return null;
            }

            // CREAR DATOS DEL ARCHIVO
            $informe = array(
                'name' => $name,
                'type' => $type,
                'tmp_name' => 'nueva_ruta',
                'size' => $size,
                'new_name' => $new_name
            );

            return $new_name;
        } else {
            return null;
        }
    }
    /**
     * CREAR FORMULARIO DE DIRECCION
     */
    public function createFormDireccion($rude){
        // DIRECCION
        $em = $this->getDoctrine()->getManager();

        if($rude->getMunicipioLugarTipo() != null){
                $lt5_id = $rude->getMunicipioLugarTipo()->getLugarTipo();
                dump($lt5_id);
                $lt4_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt5_id)->getLugarTipo();
                dump($lt4_id);
                $lt3_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt4_id)->getLugarTipo();
                dump($lt3_id);
                die;
                $m_id = $rude->getMunicipioLugarTipo()->getId();
                $p_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt5_id)->getId();
                $d_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt4_id)->getId();
        }else{
            $m_id = 0;
            $p_id = 0;
            $d_id = 0;
        }
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

        $form = $this->createFormBuilder($rude)
                    ->add('id','hidden')
                    ->add('departamentoDir', 'choice', array('data' => $d_id - 1, 'label' => 'Departamento', 'required' => true, 'choices' => $dptoArray, 'empty_value' => 'Seleccionar...','mapped'=>false))
                    ->add('provinciaDir', 'choice', array('data' => $p_id, 'label' => 'Provincia', 'required' => true, 'choices' => $provArray, 'empty_value' => 'Seleccionar...','mapped'=>false))
                    ->add('municipioLugarTipo', 'choice', array('data' => $m_id, 'label' => 'Municipio', 'required' => true, 'choices' => $muniArray, 'empty_value' => 'Seleccionar...','mapped'=>false))
                    ->add('localidad')
                    ->add('zona')
                    ->add('avenida')
                    ->add('numero')
                    ->add('celular')
                    ->add('telefonoFijo')
                    ->getForm();

        return $form;
    }

    public function saveFormDireccionAction(Request $request){
        $form = $request->get('form');
        // dump((integer)$form['idLugar']);die;
        $em = $this->getDoctrine()->getManager();

        $rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['id']);

        $rude->setMunicipioLugarTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find((integer)$form['municipioLugarTipo']));
        $rude->setLocalidad($form['localidad'] ? mb_strtoupper($form['localidad'], 'utf-8') : '');
        $rude->setZona($form['zona'] ? mb_strtoupper($form['zona'], 'utf-8') : '');
        $rude->setAvenida($form['avenida'] ? mb_strtoupper($form['avenida'], 'utf-8') : '');
        $rude->setNumero($form['numero'] ? $form['numero'] : '');
        $rude->setCelular($form['celular'] ? $form['celular'] : '');
        $rude->setTelefonoFijo($form['telefonoFijo'] ? $form['telefonoFijo'] : '');

        // // Actualizamos el estado de registro, en que paso esta el usuario
        if($rude->getRegistroFinalizado() < 2){
            $rude->setRegistroFinalizado(2);
        }

        $em->persist($rude);
        $em->flush();

        $response = new JsonResponse();
        return $response->setData(['msg'=>true]);
    }

    /**
     * CREAR FORMULARIO DE DATOS SOCIOECONOMICOS
     */
    private function createFormSocioeconomico($rude){

        // dump($rude);die;
        $em = $this->getDoctrine()->getManager();

        $idiomas = $em->getRepository('SieAppWebBundle:RudeIdioma')->findBy(array('rude' => $rude));
        $idiomaNines = $em->createQueryBuilder()
                        ->select('ri')
                        ->from('SieAppWebBundle:RudeIdioma','ri')
                        ->where('ri.rude = :rude')
                        ->andWhere('ri.hablaTipo = 1')
                        ->setParameter('rude', $rude)
                        ->getQuery()
                        ->getResult();
        if(count($idiomaNines)>0){
            $idiomaNines = $idiomaNines[0];
        }

        $idiomasHablados = $em->createQueryBuilder()
                        ->select('ri')
                        ->from('SieAppWebBundle:RudeIdioma','ri')
                        ->where('ri.rude = :rudeId')
                        ->andWhere('ri.hablaTipo = 2')
                        ->orderBy('ri.id','asc')//esta linea para correjir el orden inverso de los idiomas
                        ->setParameter('rudeId', $rude->getId())
                        ->getQuery()
                        ->getResult();
        // dump($idiomasHablados);die;
        $idiomasArray = array();
        $cont = 1;
        foreach ($idiomasHablados as $value) {
            // $idioma_aux = $em->getRepository('SieAppWebBundle:IdiomaTipo')->find($value->getIdiomaTipo()->getId());
            $idiomasArray[$cont] = $value->getIdiomaTipo()->getId();//$idioma_aux->getId();
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

        // NACIONES ORIGINARIAS
        $naciones = $em->getRepository('SieAppWebBundle:NacionOriginariaTipo')->findAll();
        $arrayNaciones = [];
        foreach ($naciones as $nacion) {
            $arrayNaciones[$nacion->getId()] = $nacion->getNacionOriginaria();
        }

        // CENTROS DE SALUD
        $centros = $em->getRepository('SieAppWebBundle:CentroSaludTipo')->findAll();
        $centrosEstudiante = $em->getRepository('SieAppWebBundle:RudeCentroSalud')->findBy(array('rude'=>$rude));
        $arrayCentros = [];
        foreach ($centrosEstudiante as $ce) {
            $arrayCentros[] = $ce->getCentroSaludTipo()->getId();
        }

        // SERVICIOS BASICOS
        $servicios = $em->getRepository('SieAppWebBundle:ServicioBasicoTipo')->findAll();
        foreach ($servicios as $s) {
            $servicioEstudiante = $em->getRepository('SieAppWebBundle:RudeServicioBasico')->findOneBy(array('rude'=>$rude, 'servicioBasicoTipo'=>$s->getId()));
            if($servicioEstudiante){
                $tiene = true;
            }else{
                $tiene = false;
            }
            switch ($s->getId()) {
                case 1: $agua = $tiene; break;
                case 2: $banio = $tiene; break;
                case 3: $alcantarillado = $tiene; break;
                case 4: $energiaElectrica = $tiene; break;
                case 5: $recojoBasura = $tiene; break;
            }
        }

        // ACCESO A INTERNET
        $accesoInternet = $em->getRepository('SieAppWebBundle:AccesoInternetTipo')->findAll();
        $accesoInternetEstudiante = $em->getRepository('SieAppWebBundle:RudeAccesoInternet')->findBy(array('rude'=>$rude));
        $arrayAccesoInternet = [];
        foreach ($accesoInternetEstudiante as $aie) {
            $arrayAccesoInternet[] = $aie->getAccesoInternetTipo()->getId();
        }

        // MESES TRABAJO
        $meses = $em->getRepository('SieAppWebBundle:MesTipo')->findAll();
        $mesesEstudiante = $em->getRepository('SieAppWebBundle:RudeMesesTrabajados')->findBy(array('rude'=>$rude));
        $arrayMeses = [];
        foreach ($mesesEstudiante as $me) {
            $arrayMeses[] = $me->getMesTipo()->getId();
        }

        // ACTIVIDADES DEL ESTUDIANTE
        $actividades = $em->getRepository('SieAppWebBundle:ActividadTipo')->findAll();
        $actividadesEstudiante = $em->getRepository('SieAppWebBundle:RudeActividad')->findBy(array('rude'=>$rude));
        $arrayActividades = [];
        foreach ($actividadesEstudiante as $ae) {
            $arrayActividades[] = $ae->getActividadTipo()->getId();
        }

        // OTRA ACTIVIDAD
        $actividadOtro = $em->getRepository('SieAppWebBundle:RudeActividad')->findOneBy(array('rude'=>$rude, 'actividadTipo'=>3));

        // TURNO TRABAJO
        $turnos = $em->getRepository('SieAppWebBundle:TurnoTipo')->findAll();
        $turnosEstudiante = $em->getRepository('SieAppWebBundle:RudeTurnoTrabajo')->findBy(array('rude'=>$rude));
        $arrayTurnos = [];
        foreach ($turnosEstudiante as $te) {
            $arrayTurnos[] = $te->getTurnoTipo()->getId();
        }

        // TIPOS DE PAGO
        $pagosEstudiante = $em->getRepository('SieAppWebBundle:RudeRecibioPago')->findBy(array('rude'=>$rude));
        $arrayPagos = [];
        foreach ($pagosEstudiante as $pe) {
            $arrayPagos[] = $pe->getPagoTipo()->getId();
        }

        // COMO LLEGA ESTUDAINTE MEDIO TRANSPORTE
        $medioTransporteEstudiante = $em->getRepository('SieAppWebBundle:RudeMedioTransporte')->findBy(array('rude'=>$rude));
        $arrayMedioTransporte = [];
        foreach ($medioTransporteEstudiante as $te) {
            $arrayMedioTransporte[] = $te->getMedioTransporteTipo()->getId();
        }

        // OTRO MEDIO DE TRANSPORTE
        $medioTransporteOtro = $em->getRepository('SieAppWebBundle:RudeMedioTransporte')->findOneBy(array('rude'=>$rude, 'medioTransporteTipo'=>3));

        // COMO LLEGA ESTUDAINTE MEDIO TRANSPORTE
        $abandonoEstudiante = $em->getRepository('SieAppWebBundle:RudeAbandono')->findBy(array('rude'=>$rude));
        $arrayAbandono = [];
        foreach ($abandonoEstudiante as $ae) {
            $arrayAbandono[] = $ae->getAbandonoTipo()->getId();
        }

        // ABANDONO OTRO
        $abandonoOtro = $em->getRepository('SieAppWebBundle:RudeAbandono')->findOneBy(array('rude'=>$rude, 'abandonoTipo'=>12));
        $form = $this->createFormBuilder($rude)
                    ->add('id', 'hidden')
                    // 4.1 IDIOMA Y PERTENENCIA
                    ->add('idiomaNines', 'entity', array(
                            'class' => 'SieAppWebBundle:IdiomaTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('it')
                                        ->where('it.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'idioma_tipo'))
                                        ->orderBy('it.idioma', 'ASC')
                                ;
                            },
                            'empty_value' => 'Seleccionar...',
                            'required'=>true,
                            'data'=>$em->getReference('SieAppWebBundle:IdiomaTipo', ($idiomaNines)?$idiomaNines->getIdiomaTipo()->getId():0),
                            'mapped'=>false
                        ))
                    ->add('idioma1', 'entity', array(
                            'class' => 'SieAppWebBundle:IdiomaTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('it')
                                        ->where('it.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'idioma_tipo'))
                                        ->orderBy('it.idioma', 'ASC')
                                ;
                            },
                            'empty_value' => 'Seleccionar...',
                            'required'=>true,
                            'data'=>$em->getReference('SieAppWebBundle:IdiomaTipo', $idioma1),
                            'mapped'=>false
                        ))
                    ->add('idioma2', 'entity', array(
                            'class' => 'SieAppWebBundle:IdiomaTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('it')
                                        ->where('it.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'idioma_tipo'))
                                        ->orderBy('it.idioma', 'ASC')
                                ;
                            },
                            'empty_value' => 'Seleccionar...',
                            'required'=>false,
                            'data'=>$em->getReference('SieAppWebBundle:IdiomaTipo', $idioma2),
                            'mapped'=>false
                        ))
                    ->add('idioma3', 'entity', array(
                            'class' => 'SieAppWebBundle:IdiomaTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('it')
                                        ->where('it.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'idioma_tipo'))
                                        ->orderBy('it.idioma', 'ASC')
                                ;
                            },
                            'empty_value' => 'Seleccionar...',
                            'required'=>false,
                            'data'=>$em->getReference('SieAppWebBundle:IdiomaTipo', $idioma3),
                            'mapped'=>false
                        ))
                    ->add('nacionOriginariaTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:NacionOriginariaTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('na')
                                        ->where('na.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'nacion_originaria_tipo'))
                                        ->orderBy('na.nacionOriginaria', 'ASC')
                                ;
                            },
                            'empty_value' => 'Seleccionar...',
                            'multiple'=>false,
                            'property'=>'nacionOriginaria',
                            'required'=>true
                        ))
                    // 4.2 SALUD DEL ESTUDIANTE
                    ->add('centroSalud', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false,
                            'expanded'=>true
                        ))
                    ->add('acudioCentro', 'entity', array(
                            'class' => 'SieAppWebBundle:CentroSaludTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('cst')
                                        ->where('cst.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'centro_salud_tipo'))
                                        ->orderBy('cst.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Seleccionar...',
                            'multiple'=>true,
                            'property'=>'descripcion',
                            'required'=>true,
                            'data'=>$em->getRepository('SieAppWebBundle:CentroSaludTipo')->findBy(array('id'=>$arrayCentros)),
                            'mapped'=>false
                        ))
                    ->add('cantidadCentroSaludTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:CantidadCentroSaludTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('cs')
                                        ->where('cs.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'cantidad_centro_salud_tipo'))
                                        ->orderBy('cs.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Seleccionar...',
                            'required'=>true
                        ))
                    ->add('seguroSalud', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false,
                            'expanded'=>true
                        ))
                    // 4.3 ACCESO A SERVICIOS BASICOS
                    ->add('agua', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false,
                            'expanded'=>true,
                            'mapped'=>false,
                            'data'=>$agua
                        ))
                    ->add('banio', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false,
                            'expanded'=>true,
                            'mapped'=>false,
                            'data'=>$banio
                        ))
                    ->add('alcantarillado', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false,
                            'expanded'=>true,
                            'mapped'=>false,
                            'data'=>$alcantarillado
                        ))
                    ->add('energiaElectrica', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false,
                            'expanded'=>true,
                            'mapped'=>false,
                            'data'=>$energiaElectrica
                        ))
                    ->add('recojoBasura', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false,
                            'expanded'=>true,
                            'mapped'=>false,
                            'data'=>$recojoBasura
                        ))
                    ->add('viviendaOcupaTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:ViviendaOcupaTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('vo')
                                        ->where('vo.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'vivienda_ocupa_tipo'))
                                        ->orderBy('vo.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Seleccionar...',
                            'multiple'=>false,
                            'property'=>'descripcionViviendaOcupa',
                            'required'=>true
                        ))
                    // 4.4 ACCESO A INTERNET
                    ->add('accesoInternet', 'entity', array(
                            'class' => 'SieAppWebBundle:AccesoInternetTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('ai')
                                        ->where('ai.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'acceso_internet_tipo'))
                                        ->orderBy('ai.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Seleccionar...',
                            'multiple'=>true,
                            'property'=>'descripcionAccesoInternet',
                            'required'=>true,
                            'data'=>$em->getRepository('SieAppWebBundle:AccesoInternetTipo')->findBy(array('id'=>$arrayAccesoInternet)),
                            'mapped'=>false
                        ))
                    ->add('frecuenciaUsoInternetTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:FrecuenciaUsoInternetTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('fi')
                                        ->where('fi.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'frecuencia_uso_internet_tipo'))
                                        ->orderBy('fi.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Seleccionar...',
                            'multiple'=>false,
                            'property'=>'descripcionFrecuenciaInternet',
                            'required'=>false
                        ))
                    // 4.5 ACTIVIDAD LABORAL
                    ->add('trabajoGestionPasada', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No', null=>'Ns/Nr'),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>null,
                            'expanded'=>true
                        ))
                    ->add('mesesTrabajados', 'entity', array(
                            'class' => 'SieAppWebBundle:MesTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('mt')
                                        ->where('mt.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'mes_tipo'))
                                        ->orderBy('mt.id', 'ASC')
                                ;
                            },
                            'multiple'=>true,
                            'property'=>'mes',
                            'required'=>false,
                            'data'=>$em->getRepository('SieAppWebBundle:MesTipo')->findBy(array('id'=>$arrayMeses)),
                            'mapped'=>false,
                            'expanded'=>false
                        ))
                    ->add('actividades', 'entity', array(
                            'class' => 'SieAppWebBundle:ActividadTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('at')
                                        ->where('at.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'actividad_tipo'))
                                        ->orderBy('at.id', 'ASC')
                                ;
                            },
                            'multiple'=>true,
                            'property'=>'descripcionOcupacion',
                            'required'=>false,
                            'data'=>$em->getRepository('SieAppWebBundle:ActividadTipo')->findBy(array('id'=>$arrayActividades)),
                            'mapped'=>false,
                            'expanded'=>false
                        ))
                    ->add('actividadOtro', 'text', array('mapped'=>false, 'required'=>false, 'data'=> ($actividadOtro)?$actividadOtro->getActividadOtro():''))
                    ->add('turnosTrabajo', 'entity', array(
                            'class' => 'SieAppWebBundle:TurnoTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('tt')
                                        ->where('tt.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'turno_tipo'))
                                        ->orderBy('tt.id', 'ASC')
                                ;
                            },
                            'multiple'=>true,
                            'property'=>'turno',
                            'required'=>false,
                            'data'=>$em->getRepository('SieAppWebBundle:TurnoTipo')->findBy(array('id'=>$arrayTurnos)),
                            'mapped'=>false,
                            'expanded'=>false
                        ))
                    ->add('frecuenciaTrabajoTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:FrecuenciaTrabajoTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('ftt')
                                        ->where('ftt.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'frecuencia_trabajo_tipo'))
                                        ->orderBy('ftt.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Seleccionar...',
                            'multiple'=>false,
                            'property'=>'descripcionFrecuenciaTrabajo',
                            'required'=>false
                        ))
                    ->add('respuestaPago', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No', null=>'Ns/Nr'),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>null,
                            'expanded'=>true
                        ))
                    ->add('recibioTipoPago', 'entity', array(
                            'class' => 'SieAppWebBundle:PagoTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('pt')
                                        ->where('pt.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'pago_tipo'))
                                        ->orderBy('pt.id', 'ASC')
                                ;
                            },
                            'multiple'=>true,
                            'property'=>'descripcionPago',
                            'required'=>false,
                            'data'=>$em->getRepository('SieAppWebBundle:PagoTipo')->findBy(array('id'=>$arrayPagos)),
                            'mapped'=>false,
                            'expanded'=>false
                        ))
                    ->add('medioTransporte', 'entity', array(
                            'class' => 'SieAppWebBundle:MedioTransporteTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('mt')
                                        ->where('mt.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'medio_transporte_tipo'))
                                        ->orderBy('mt.id', 'ASC')
                                ;
                            },
                            'multiple'=>true,
                            'property'=>'descripcionMedioTrasnporte',
                            'required'=>true,
                            'data'=>$em->getRepository('SieAppWebBundle:MedioTransporteTipo')->findBy(array('id'=>$arrayMedioTransporte)),
                            'mapped'=>false,
                            'expanded'=>false
                        ))
                    ->add('medioTransporteOtro', 'text', array('mapped'=>false, 'required'=>false, 'data'=> ($medioTransporteOtro)?'hola':''))
                    ->add('tiempoTransporte', 'entity', array(
                            'class' => 'SieAppWebBundle:TiempoMaximoTrayectoTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('tmt')
                                        ->where('tmt.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'tiempo_maximo_trayecto_tipo'))
                                        ->orderBy('tmt.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Seleccionar...',
                            'multiple'=>false,
                            'property'=>'descripcionTiempoMaxTipo',
                            'required'=>true,
                            'mapped'=>false,
                            'data'=>(count($medioTransporteEstudiante) > 0)?$em->getRepository('SieAppWebBundle:TiempoMaximoTrayectoTipo')->find($medioTransporteEstudiante[0]->getTiempoMaximoTrayectoTipo()->getId()):''
                        ))
                    ->add('tieneAbandono', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false,
                            'expanded'=>true
                        ))
                    ->add('abandono', 'entity', array(
                            'class' => 'SieAppWebBundle:AbandonoTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('at')
                                        ->where('at.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'abandono_tipo'))
                                        ->orderBy('at.id', 'ASC')
                                ;
                            },
                            'multiple'=>true,
                            'property'=>'descripcionAbandono',
                            'required'=>true,
                            'data'=>$em->getRepository('SieAppWebBundle:AbandonoTipo')->findBy(array('id'=>$arrayAbandono)),
                            'mapped'=>false,
                            'expanded'=>false
                        ))
                    ->add('abandonoOtro', 'text', array('mapped'=>false, 'required'=>false, 'data'=> ($abandonoOtro)?$abandonoOtro->getAbandonoOtro():''))

                    ->getForm();

        return $form;
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

    public function cargarGradoDiscapacidadAction(Request $request){
        $idDiscapacidad = $request->get('discapacidad');
        $em = $this->getDoctrine()->getManager();
        // SI LA DISCAPACIDAD ES VISUAL = 10
        if($idDiscapacidad == 10){
            $gradoDiscapacidad = $em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->findBy(array('id'=>array(6,5)));
        }else{
            if($idDiscapacidad != ""){
                $gradoDiscapacidad = $em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->findBy(array('id'=>array(1,2,7,8)));
            }else{
                $gradoDiscapacidad = null;
            }
        }

        $gradosArray = array();
        foreach ($gradoDiscapacidad as $gd) {
            $gradosArray[$gd->getId()] = $gd->getGradoDiscapacidad();
        }

        $response = new JsonResponse();
        return $response->setData(array('gradosDiscapacidad' => $gradosArray));
    }

    /**
     * DATOS SOCIOECONOMICOS
     */
    public function saveFormSocioeconomicosAction(Request $request){
        $form = $request->get('form');
        // dump($form);die;
        $em = $this->getDoctrine()->getManager();
        $rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['id']);

        /**
         * REGISTRO DE IDIOMAs
         */
        // ELIMINAMOS LOS IDIOMAS
        $eliminarIdiomas = $em->createQueryBuilder()
                        ->delete('')
                        ->from('SieAppWebBundle:RudeIdioma','rid')
                        ->where('rid.rude = :rude')
                        ->setParameter('rude', $rude)
                        ->getQuery()
                        ->getResult();

        // REGISTRAMOS LOS IDIOMAS
        $rudeIdioma = new RudeIdioma();
        if($form['idiomaNines']){
            $rudeIdioma->setRude($rude);
            $rudeIdioma->setIdiomaTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idiomaNines'] ? $form['idiomaNines'] : 0));
            $rudeIdioma->setHablaTipo($em->getRepository('SieAppWebBundle:HablaTipo')->find(1));
            $rudeIdioma->setFechaRegistro(new \DateTime('now'));
            $rudeIdioma->setFechaModificacion(new \DateTime('now'));
            $em->persist($rudeIdioma);
            $em->flush();
        }
        
        $rudeIdioma = new RudeIdioma();
        if($form['idioma1']){
            $rudeIdioma->setRude($rude);
            $rudeIdioma->setIdiomaTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idioma1'] ? $form['idioma1'] : 0));
            $rudeIdioma->setHablaTipo($em->getRepository('SieAppWebBundle:HablaTipo')->find(2));//habla FRECUENTE
            $rudeIdioma->setFechaRegistro(new \DateTime('now'));
            $rudeIdioma->setFechaModificacion(new \DateTime('now'));
            $em->persist($rudeIdioma);
            $em->flush();
        }
        $rudeIdioma = new RudeIdioma();
        if($form['idioma2']){
            $rudeIdioma->setRude($rude);
            $rudeIdioma->setIdiomaTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idioma2'] ? $form['idioma2'] : 0));
            $rudeIdioma->setHablaTipo($em->getRepository('SieAppWebBundle:HablaTipo')->find(3));//habla MEDIANAMENTE FRECUENTE
            $rudeIdioma->setFechaRegistro(new \DateTime('now'));
            $rudeIdioma->setFechaModificacion(new \DateTime('now'));
            $em->persist($rudeIdioma);
            $em->flush();
        }
        $rudeIdioma = new RudeIdioma();
        if($form['idioma3']){
            $rudeIdioma->setRude($rude);
            $rudeIdioma->setIdiomaTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idioma3'] ? $form['idioma3'] : 0));
            $rudeIdioma->setHablaTipo($em->getRepository('SieAppWebBundle:HablaTipo')->find(4));//habla MENOS FRECUENTE
            $rudeIdioma->setFechaRegistro(new \DateTime('now'));
            $rudeIdioma->setFechaModificacion(new \DateTime('now'));
            $em->persist($rudeIdioma);
            $em->flush();
        }

        $rude->setNacionOriginariaTipo($em->getRepository('SieAppWebBundle:NacionOriginariaTipo')->find($form['nacionOriginariaTipo']));
        $rude->setCentroSalud($form['centroSalud']);
        
        /**
         * ACUDIO CENTRO
         */
        // ELIMINAMOS LOS CENTROS A LOS QUE ACUDIO
        $eliminarCentros = $em->createQueryBuilder()
                        ->delete('')
                        ->from('SieAppWebBundle:RudeCentroSalud','rcs')
                        ->where('rcs.rude = :rude')
                        ->setParameter('rude', $rude)
                        ->getQuery()
                        ->getResult();
        // REGISTRAMOS LOS CENTROS
        if(isset($form['acudioCentro'])){
            $acudioCentro = $form['acudioCentro'];
            for ($i=0; $i < count($acudioCentro); $i++) { 
                $centroEstudiante = new RudeCentroSalud();
                $centroEstudiante->setRude($rude);
                $centroEstudiante->setCentroSaludTipo($em->getRepository('SieAppWebBundle:CentroSaludTipo')->find($acudioCentro[$i]));
                $centroEstudiante->setFechaRegistro(new \DateTime('now'));
                $em->persist($centroEstudiante);
                $em->flush();
            }
        }
        if(isset($form['acudioCentro'])){
            $rude->setCantidadCentroSaludTipo($em->getRepository('SieAppWebBundle:CantidadCentroSaludTipo')->find($form['cantidadCentroSaludTipo']));
        }else{
             $rude->setCantidadCentroSaludTipo(null);
        }

        $rude->setSeguroSalud($form['seguroSalud']);

        /**
         * SERVICIOS
         */
        // ELIMINAMOS LOS SERVICIOS
        $eliminarServicios = $em->createQueryBuilder()
                        ->delete('')
                        ->from('SieAppWebBundle:RudeServicioBasico','rsb')
                        ->where('rsb.rude = :rude')
                        ->setParameter('rude', $rude)
                        ->getQuery()
                        ->getResult();
        // REGISTRAMOS LOS SERVICIOS
        if($form['agua']){
            $servicio = new RudeServicioBasico();
            $servicio->setRude($rude);
            $servicio->setServicioBasicoTipo($em->getRepository('SieAppWebBundle:ServicioBasicoTipo')->find(($form['agua'])?1:null));
            $servicio->setFechaRegistro(new \DateTime('now'));
            $em->persist($servicio);
            $em->flush();
        }
        if($form['banio']){
            $servicio = new RudeServicioBasico();
            $servicio->setRude($rude);
            $servicio->setServicioBasicoTipo($em->getRepository('SieAppWebBundle:ServicioBasicoTipo')->find(($form['banio'])?2:null));
            $servicio->setFechaRegistro(new \DateTime('now'));
            $em->persist($servicio);
            $em->flush();
        }
        if($form['alcantarillado']){
            $servicio = new RudeServicioBasico();
            $servicio->setRude($rude);
            $servicio->setServicioBasicoTipo($em->getRepository('SieAppWebBundle:ServicioBasicoTipo')->find(($form['alcantarillado'])?3:null));
            $servicio->setFechaRegistro(new \DateTime('now'));
            $em->persist($servicio);
            $em->flush();
        }
        if($form['energiaElectrica']){
            $servicio = new RudeServicioBasico();
            $servicio->setRude($rude);
            $servicio->setServicioBasicoTipo($em->getRepository('SieAppWebBundle:ServicioBasicoTipo')->find(($form['energiaElectrica'])?4:null));
            $servicio->setFechaRegistro(new \DateTime('now'));
            $em->persist($servicio);
            $em->flush();
        }
        if($form['recojoBasura']){
            $servicio = new RudeServicioBasico();
            $servicio->setRude($rude);
            $servicio->setServicioBasicoTipo($em->getRepository('SieAppWebBundle:ServicioBasicoTipo')->find(($form['recojoBasura'])?5:null));
            $servicio->setFechaRegistro(new \DateTime('now'));
            $em->persist($servicio);
            $em->flush();
        }

        ////////////////

        $rude->setViviendaOcupaTipo($em->getRepository('SieAppWebBundle:ViviendaOcupaTipo')->find($form['viviendaOcupaTipo']));

        /**
         * ACCESO INTERNET
         */
        // ELIMINAMOS LOS ACCESOS A INTERNET
        $eliminarInternet = $em->createQueryBuilder()
                        ->delete('')
                        ->from('SieAppWebBundle:RudeAccesoInternet','rai')
                        ->where('rai.rude = :rude')
                        ->setParameter('rude', $rude)
                        ->getQuery()
                        ->getResult();
        // REGISTRAMOS LOS CENTROS
        if(isset($form['accesoInternet'])){
            $accesoInternet = $form['accesoInternet'];
            for ($i=0; $i < count($accesoInternet); $i++) { 
                $internetEstudiante = new RudeAccesoInternet();
                $internetEstudiante->setRude($rude);
                $internetEstudiante->setAccesoInternetTipo($em->getRepository('SieAppWebBundle:AccesoInternetTipo')->find($accesoInternet[$i]));
                $internetEstudiante->setFechaRegistro(new \DateTime('now'));
                $em->persist($internetEstudiante);
                $em->flush();
            }

            ///////////////
            /// VERIFICAMOS SI TIENE ACCESO A INTERNET
            if(in_array(4, $accesoInternet)){
                $rude->setFrecuenciaUsoInternetTipo(null);
            }else{
                $rude->setFrecuenciaUsoInternetTipo($em->getRepository('SieAppWebBundle:FrecuenciaUsoInternetTipo')->find($form['frecuenciaUsoInternetTipo']));
            }
        }
        


        $rude->setTrabajoGestionPasada(($form['trabajoGestionPasada'] != "")?$form['trabajoGestionPasada']:null);

        /**
         * MESES DE TRABAJO
         */
        // ELIMINAMOS LOS MESES DE TRABAJO
        $eliminarMesesTrabajo = $em->createQueryBuilder()
                        ->delete('')
                        ->from('SieAppWebBundle:RudeMesesTrabajados','rmt')
                        ->where('rmt.rude = :rude')
                        ->setParameter('rude', $rude)
                        ->getQuery()
                        ->getResult();
        // SI TRABAJO LA GESTION PASADA Y TIENE MESES TRABAJADOS
        // REGISTRAMOS LOS MESES DE TRABAJO
        if($form['trabajoGestionPasada'] == true and isset($form['mesesTrabajados'])){
            $mesesTrabajados = $form['mesesTrabajados'];
            for ($i=0; $i < count($mesesTrabajados); $i++) { 
                $mesesTrabajoEstudiante = new RudeMesesTrabajados();
                $mesesTrabajoEstudiante->setRude($rude);
                $mesesTrabajoEstudiante->setMesTipo($em->getRepository('SieAppWebBundle:MesTipo')->find($mesesTrabajados[$i]));
                $mesesTrabajoEstudiante->setFechaRegistro(new \DateTime('now'));
                $em->persist($mesesTrabajoEstudiante);
                $em->flush();
            }
        }

        /**
         * ACTIVIDADES TRABAJO
         */
        // ELIMINAMOS LAS ACTIVIDADES
        $eliminarActividades = $em->createQueryBuilder()
                        ->delete('')
                        ->from('SieAppWebBundle:RudeActividad','ra')
                        ->where('ra.rude = :rude')
                        ->setParameter('rude', $rude)
                        ->getQuery()
                        ->getResult();
        // SI TRABAJO LA GESTION PASADA Y TIENE ACTIVIDADES
        // REGISTRAMOS LAS ACTIVIDADES
        if($form['trabajoGestionPasada'] == true and isset($form['actividades'])){
            $actividades = $form['actividades'];
            for ($i=0; $i < count($actividades); $i++) { 
                $actividadEstudiante = new RudeActividad();
                $actividadEstudiante->setRude($rude);
                $actividadEstudiante->setActividadTipo($em->getRepository('SieAppWebBundle:ActividadTipo')->find($actividades[$i]));
                $actividadEstudiante->setFechaRegistro(new \DateTime('now'));
                $em->persist($actividadEstudiante);
                $em->flush();

                // REGISTRAMOS OTRA ACTIVIDAD
                if($actividades[$i] == 13){
                    $actividadEstudiante->setObs($form['actividadOtro']);
                }
            }
        }

        /**
         * TURNOS DE TRABAJO
         */
        // ELIMINAMOS LOS TURNOS DE TRABAJO
        $eliminarTurnos = $em->createQueryBuilder()
                        ->delete('')
                        ->from('SieAppWebBundle:RudeTurnoTrabajo','rtt')
                        ->where('rtt.rude = :rude')
                        ->setParameter('rude', $rude)
                        ->getQuery()
                        ->getResult();
        // SI TRABAJO LA GESTION PASADA Y TIENE TURNOS DE TRABAJO
        // REGISTRAMOS TURNOS DE TRABAJO
        if($form['trabajoGestionPasada'] == true and isset($form['turnosTrabajo'])){
            $turnosTrabajo = $form['turnosTrabajo'];
            for ($i=0; $i < count($turnosTrabajo); $i++) { 
                $turnoTrabajoEstudiante = new RudeTurnoTrabajo();
                $turnoTrabajoEstudiante->setRude($rude);
                $turnoTrabajoEstudiante->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find($turnosTrabajo[$i]));
                $turnoTrabajoEstudiante->setFechaRegistro(new \DateTime('now'));
                $em->persist($turnoTrabajoEstudiante);
                $em->flush();
            }
        }

        /////////
        // SI TRABAJO LA GESTION PASADA
        if($form['trabajoGestionPasada'] == true){
            $rude->setFrecuenciaTrabajoTipo($em->getRepository('SieAppWebBundle:FrecuenciaTrabajoTipo')->find($form['frecuenciaTrabajoTipo']));
            $rude->setRespuestaPago(($form['respuestaPago'] != "")?$form['respuestaPago']:null);
        }else{
            $rude->setFrecuenciaTrabajoTipo(null);
            $rude->setRespuestaPago(false);
        }

        /**
         * TIPOS DE PAGO
         */
        // ELIMINAMOS LOS TIPOS DE PAGO
        $eliminarPagos = $em->createQueryBuilder()
                        ->delete('')
                        ->from('SieAppWebBundle:RudeRecibioPago','rrp')
                        ->where('rrp.rude = :rude')
                        ->setParameter('rude', $rude)
                        ->getQuery()
                        ->getResult();
        // SI TRABAJO LA GESTION PASADA Y RECIBIO PAGO
        // REGISTRAMOS LSO TIPOS DE PAGO 
        if(isset($form['recibioTipoPago']) and isset($form['respuestaPago']) and $form['respuestaPago'] == true){
            $recibioTipoPago = $form['recibioTipoPago'];
            for ($i=0; $i < count($recibioTipoPago); $i++) { 
                $pagoEstudiante = new RudeRecibioPago();
                $pagoEstudiante->setRude($rude);
                $pagoEstudiante->setPagoTipo($em->getRepository('SieAppWebBundle:PagoTipo')->find($recibioTipoPago[$i]));
                $pagoEstudiante->setFechaRegistro(new \DateTime('now'));
                $em->persist($pagoEstudiante);
                $em->flush();
            }
        }

        /**
         * MEDIOS DE TRANSPORTE
         */
        // ELIMINAMOS LOS MEDIOS DE TRANSPORTE
        $eliminarMediosTransporte = $em->createQueryBuilder()
                        ->delete('')
                        ->from('SieAppWebBundle:RudeMedioTransporte','rmt')
                        ->where('rmt.rude = :rude')
                        ->setParameter('rude', $rude)
                        ->getQuery()
                        ->getResult();
        // REGISTRAMOS LOS MEDIOS DE TRANSPORTE
        $medioTransporte = $form['medioTransporte'];
        for ($i=0; $i < count($medioTransporte); $i++) { 
            $medioTransporteEstudiante = new RudeMedioTransporte();
            $medioTransporteEstudiante->setRude($rude);
            $medioTransporteEstudiante->setMedioTransporteTipo($em->getRepository('SieAppWebBundle:MedioTransporteTipo')->find($medioTransporte[$i]));
            $medioTransporteEstudiante->setFechaRegistro(new \DateTime('now'));

            // VERIFICAMOS SI SE TRATA DE OTRO MEDIO DE TRASNPORTE
            if($medioTransporte[$i] == 4){
                $medioTransporteEstudiante->setLlegaOtro($form['medioTransporteOtro']);
            }

            // REGISTRAMOS EL TIEMPO DE TRANSPORTE
            $medioTransporteEstudiante->setTiempoMaximoTrayectoTipo($em->getRepository('SieAppWebBundle:TiempoMaximoTrayectoTipo')->find($form['tiempoTransporte']));
            $em->persist($medioTransporteEstudiante);
            $em->flush();
        }

        ///////////////////////
        
        $rude->setTieneAbandono($form['tieneAbandono']);
        
        /**
         * ABANDONO
         */
        // ELIMINAMOS LOS REGISTROS DE ABANDONO
        $eliminarAbandono = $em->createQueryBuilder()
                        ->delete('')
                        ->from('SieAppWebBundle:RudeAbandono','ra')
                        ->where('ra.rude = :rude')
                        ->setParameter('rude', $rude)
                        ->getQuery()
                        ->getResult();
        // REGISTRAMOS LAS CAUSAS DE ABANDONO
        if(isset($form['abandono']) and $form['abandono'] == true){
            $abandono = $form['abandono'];
            for ($i=0; $i < count($abandono); $i++) { 
                $abandonoEstudiante = new RudeAbandono();
                $abandonoEstudiante->setRude($rude);
                $abandonoEstudiante->setAbandonoTipo($em->getRepository('SieAppWebBundle:AbandonoTipo')->find($abandono[$i]));
                $abandonoEstudiante->setFechaRegistro(new \DateTime('now'));

                // VERIFICAMOS SI SE TRATA DE OTRO MEDIO DE TRASNPORTE
                if($abandono[$i] == 12){
                    $abandonoEstudiante->setAbandonoOtro($form['abandonoOtro']);
                }
                $em->persist($abandonoEstudiante);
                $em->flush();
            }
        }

        // Registro paso 3
        if($rude->getRegistroFinalizado() < 3){
            $rude->setRegistroFinalizado(3);
        }

        $em->flush();

        $response = new JsonResponse();
        return $response->setData(['msg'=>true]);
    }

    /**
     * FORMULARIO CON QUIEN VIVE EL ESTUDAINTE
     */
    public function createFormConQuienVive($rude , $rude_ext){
        $em = $this->getDoctrine()->getManager();
        // $form = $this->createFormBuilder($rude)
        $form = $this->createFormBuilder()
                ->add('id', 'hidden',  array('data' => $rude->getId(),'mapped'=>false))
                ->add('viveHabitualmenteTipo', 'entity', array(
                        'class' => 'SieAppWebBundle:ViveHabitualmenteTipo',
                        'query_builder' => function (EntityRepository $e) {
                            return $e->createQueryBuilder('vat')
                                    ->where('vat.esVigente = true')
                                    ->orderBy('vat.id', 'ASC')
                            ;
                        },
                        'empty_value' => 'Seleccionar...',
                        'required'=>true,
                        'data'=> ($rude->getViveHabitualmenteTipo())?$em->getReference('SieAppWebBundle:ViveHabitualmenteTipo', $rude->getViveHabitualmenteTipo()->getId()):'',
                        'mapped'=>false
                    ))
                ->add('centroAcogida', 'text', array('required' => false, 'data'=>$rude_ext ? $rude_ext->getCentroAcogida() : ''))
                ->getForm();
        return $form;
    }

    public function saveFormConQuienViveAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['id']);
        $rude->setViveHabitualmenteTipo($em->getRepository('SieAppWebBundle:ViveHabitualmenteTipo')->find($form['viveHabitualmenteTipo']));
        $em->flush();

        $response = new JsonResponse();
        return $response->setData(['msg'=>'ok']);
    }

    /**
     * FUNCIONES APODERADO
     * Tab 6 - Tutor
     */
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
                        p.celular,
                        p.segipId,
                        p.apellidoEsposo,
                        ct.id as cedulaTipoId,
                        IDENTITY(p.estadocivilTipo) as estado_civil, 
                        dt.id as expedido,
                        gt.id as genero, 
                        p.correo, 
                        aid.id as idDatos, 
                        im.id as idiomaMaterno, 
                        it.id as instruccionTipo, 
                        aid.empleo, 
                        aid.telefono,
                        aot.id as ocupacion,
                        aid.obs,
                        aid.institucionTrabaja')
                    ->innerJoin('SieAppWebBundle:Persona','p','with','ai.persona = p.id')
                    ->innerJoin('SieAppWebBundle:GeneroTipo','gt','with','p.generoTipo = gt.id')
                    ->innerJoin('SieAppWebBundle:ApoderadoTipo','at','with','ai.apoderadoTipo = at.id')
                    ->leftJoin('SieAppWebBundle:ApoderadoInscripcionDatos','aid','with','aid.apoderadoInscripcion = ai.id')
                    ->leftJoin('SieAppWebBundle:IdiomaTipo','im','with','aid.idiomaMaterno = im.id')
                    ->leftJoin('SieAppWebBundle:InstruccionTipo','it','with','aid.instruccionTipo = it.id')
                    ->leftJoin('SieAppWebBundle:ApoderadoOcupacionTipo','aot','with','aid.ocupacionTipo = aot.id')
                    ->leftJoin('SieAppWebBundle:DepartamentoTipo','dt','with','p.expedido = dt.id')
                    ->leftJoin('SieAppWebBundle:CedulaTipo','ct','with','p.cedulaTipo = ct.id')
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
                'apellidoEsposo'=>null,
                'estado_civil'=>null,
                'expedido'=>null,
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
                'corregirFecha'=>false,
                'institucionTrabaja'=>null,
                'tipoApoderado'=>$tipoApoderado
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
            $apoderado[0]['tipoApoderado'] = $tipoApoderado;
        }

        // VERIFICAMOS SI EL CARNET NO ES VALIDO CON VALOR SC
        $pos = strpos($apoderado[0]['carnet'], 'SC');
        if($pos === false){
            $apoderado[0]['carnetRequerido'] = true;
        }else{
            // $apoderado[0]['carnet'] = '';
            $apoderado[0]['carnetRequerido'] = false;
        }

        return $apoderado;
    }

    public function createFormApoderado($rude, $idInscripcion, $datos){

        // $idInscripcion = $inscripcion->getId();

        // $padreTutor = $this->obtenerApoderado($idInscripcion,array(1,3,4,5,6,7,8,9,10,11,12,13))[0];
        // dump($datos); 
        // dump($datos['apoderadoTipo']);die;
        $tipoApoderado = $datos['tipoApoderado'];

        $em = $this->getDoctrine()->getManager();

        // DEFINICION DE GENEROS POR TIPO DE APODERADO
        if (in_array(1, $tipoApoderado)) {
            $generos = [1];
        } else {
            if (in_array(2, $tipoApoderado)) {
                $generos = [2];
            } else {
                $generos = [1,2];
            }            
        }
        
        

        $form = $this->createFormBuilder($datos)
                    ->add('idInscripcion', 'hidden', array('data' => $idInscripcion,'mapped'=>false))
                    ->add('rudeId', 'hidden', array('data' => $rude->getId(),'mapped'=>false))
                    ->add('id', 'hidden', array('required' => true))
                    ->add('apoderadoTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:ApoderadoTipo',
                            'query_builder' => function (EntityRepository $e) use ($tipoApoderado) {
                                return $e->createQueryBuilder('at')
                                        ->where('at.id in (:ids)')
                                        ->setParameter('ids', $tipoApoderado)
                                        ->orderBy('at.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Seleccionar...',
                            'required'=>true,
                            'data'=> ($datos['apoderadoTipo'] != null)? $em->getReference('SieAppWebBundle:ApoderadoTipo', $datos['apoderadoTipo']): ''
                        ))
                    ->add('idPersona', 'hidden', array('required' => true))
                    ->add('nombre', 'text', array('required' => true))
                    ->add('paterno', 'text', array('required' => false))
                    ->add('materno', 'text', array('required' => false))
                    ->add('carnet', 'text', array('required' => true))
                    ->add('complemento', 'text', array('required' => false))
                    ->add('expedido', 'entity', array(
                            'class' => 'SieAppWebBundle:DepartamentoTipo',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('dt')
                                        ->where('dt.id not in (0)')
                                        ->orderBy('dt.id', 'ASC')
                                ;
                            },
                            'property'=>'sigla',
                            'empty_value' => 'Seleccionar...',
                            'data'=>($datos['expedido'] != null)?$em->getReference('SieAppWebBundle:DepartamentoTipo', $datos['expedido']):''
                        ))
                    ->add('fechaNacimiento', 'text', array('required' => true))
                    // ->add('telefono', 'text', array('required' => false))
                    ->add('segipId', 'hidden', array('required' => true))
                    ->add('genero', 'entity', array(
                            'class' => 'SieAppWebBundle:GeneroTipo',
                            'query_builder' => function (EntityRepository $e) use ($generos){
                                return $e->createQueryBuilder('gt')
                                        ->where('gt.id in (:ids)')
                                        ->setParameter('ids', $generos)
                                        ->orderBy('gt.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Selecionar...',
                            'required'=>true,
                            'data'=>($datos['genero'] != null)?$em->getReference('SieAppWebBundle:GeneroTipo', $datos['genero']):''
                        ))
                    ->add('correo', 'text', array('required' => false))
                    ->add('celular', 'text', array('required' => false))
                    ->add('telefono', 'text', array('required' => false))
                    ->add('idDatos', 'hidden', array('required' => true))
                    ->add('idiomaMaterno', 'entity', array(
                            'class' => 'SieAppWebBundle:IdiomaTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude) {
                                return $e->createQueryBuilder('it')
                                        ->where('it.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'idioma_tipo'))
                                        ->orderBy('it.idioma','ASC');
                            },
                            'empty_value' => 'Selecionar...',
                            'required'=>true,
                            'data'=>($datos['idiomaMaterno'] != null)?$em->getReference('SieAppWebBundle:IdiomaTipo', $datos['idiomaMaterno']):''
                        ))
                    ->add('ocupacion', 'entity', array(
                            'class' => 'SieAppWebBundle:ApoderadoOcupacionTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude) {
                                return $e->createQueryBuilder('aot')
                                        ->where('aot.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'apoderado_ocupacion_tipo'))
                                        ->orderBy('aot.ocupacion','ASC');
                            },
                            'empty_value' => 'Selecionar...',
                            'required'=>true,
                            'property'=>'ocupacion',
                            'data'=>($datos['ocupacion'] != null)?$em->getReference('SieAppWebBundle:ApoderadoOcupacionTipo', ($datos['ocupacion'])?$datos['ocupacion']:10035):''
                        ))
                    ->add('empleo', 'text', array('required' => true))
                    ->add('instruccionTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:InstruccionTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude) {
                                return $e->createQueryBuilder('it')
                                        ->where('it.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'instruccion_tipo'))
                                        ->orderBy('it.id','ASC');
                            },
                            'empty_value' => 'Selecionar...',
                            'required'=>true,
                            'data'=>($datos['instruccionTipo'] != null)?$em->getReference('SieAppWebBundle:InstruccionTipo', $datos['instruccionTipo']):''
                        ))
                    ->add('cedulaTipoId', 'hidden', array('required' => true))
                    ->add('institucionTrabaja', 'text', array('required' => false))
                    ->getForm();
                    
        return $form;
    }

    /**
     * FUNCIONES PARA REGISTRO DE TUTORES
     */
    /*
    * FUNCION AJAX PARA BUSCAR PERSONA APODERADO
    */
    public function buscarPersonaAction(Request $request)
    { 
        try
        {
            $carnet = $request->get('carnet');
            $complemento = $request->get('complemento');
            // $complemento = ($request->get('complemento') != "")?$request->get('complemento'):0;
            $paterno = $request->get('paterno');
            $materno = $request->get('materno');
            $nombre = $request->get('nombre');
            $fechaNacimiento = $request->get('fechaNacimiento');
            $esExtranjero=filter_var($request->get('esExtranjero'),FILTER_SANITIZE_NUMBER_INT);
            $documentoNro=$request->get('documentoNro');
            $extranjero_segip=$request->get('extranjero_segip');
            //dump($extranjero_segip); die;
            $data=array();
            if($esExtranjero==0)//es nacional
            {
                $parametros = array(
                    'complemento'=>$complemento,
                    'primer_apellido'=>$paterno,
                    'segundo_apellido'=>$materno,
                    'nombre'=>$nombre,
                    'fecha_nacimiento'=>$fechaNacimiento
                );
                if ($extranjero_segip=='true') $parametros['extranjero'] = '2';

                // $respuesta = $this->get('sie_app_web.segip')->verificarPersona($carnet, $complemento, $paterno, $materno, $nombre, $fechaNacimiento, 'prod', 'academico');
                $persona = $this->get('sie_app_web.segip')->buscarPersonaPorCarnet($carnet, $parametros, 'prod', 'academico');

                // dump($persona);
                // die;
                // dump($persona['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson']);die;

                //$respuesta = false;
                // $persona = $persona['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson'];
                // if($persona == null){
                //     dump('no existe');
                // }else{
                //     dump('existe');
                // }

                if(isset($persona['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson']) && $persona['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson'] !== "null")
                {
                    // dump($persona);die;
                    // $persona = $this->get('sie_app_web.segip')->buscarPersonaPorCarnet($carnet, $parametros, 'dev', 'academico');
                    // $persona = 
                    $persona = json_decode($persona['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson'], true);
                    // dump($persona);die;
                    $data['status'] = 200;
                    $data['persona'] = array(
                        'id'=>'segip',
                        'carnet'=> $persona['NumeroDocumento'],
                        'complemento'=> ($persona['ComplementoVisible'] == 1)? $persona['Complemento']:'',
                        'paterno'=> $persona['PrimerApellido'],
                        'materno'=> $persona['SegundoApellido'],
                        'nombre'=> $persona['Nombres'],
                        'fecha_nacimiento'=> $persona['FechaNacimiento'],
                        'cedula_tipo_id'=> 1, //si es nacional
                    );
                }
                else
                {
                    $data['status'] = 404;
                }

                // $data['status'] = 200;
                // $data['persona'] = array(
                //     'id'=>'23519419',
                //     'carnet'=> '8260138',
                //     'complemento'=> '',
                //     'paterno'=> 'QUISPEC',
                //     'materno'=> 'CHOQUE',
                //     'nombre'=> 'JHONNY',
                //     'fecha_nacimiento'=> '20-01-1990'
                // );
            
            }
            else // es extranjero
            {
                $personaExtranjera=$this->buscarPersonaExtranjero($documentoNro);
                if($personaExtranjera)
                {
                    $data['status'] = 200;
                    $data['persona'] = array(
                        'id'=>'extranjero',//REVISAR--------
                        'carnet'=> $personaExtranjera->getCarnet(),
                        'complemento'=> $personaExtranjera->getComplemento(),
                        'paterno'=> $personaExtranjera->getPaterno(),
                        'materno'=> $personaExtranjera->getMaterno(),
                        'nombre'=> $personaExtranjera->getNombre(),
                        ///'fecha_nacimiento'=> $personaExtranjera->getFechaNacimiento()
                        'fecha_nacimiento'=> $personaExtranjera->getFechaNacimiento()->format('d-m-Y'),
                        'cedula_tipo_id'=> 2 // si es extranjero
                    );
                }
                else
                {
                    $data['status'] = 404;
                }
            }

            $response = new JsonResponse();
            $response->setData($data);
            
            return $response;
        } catch (Exception $e) {
            
        }

    }

    private function buscarPersonaExtranjero($documentoNro)
    {
        $em = $this->getDoctrine()->getManager();
        $extranjero = $em->getRepository('SieAppWebBundle:EstudiantePersonaDiplomatico')->findOneBy(array('documentoNumero'=>$documentoNro));

        if($extranjero)
        {
            $extranjero=$extranjero->getPersona();
            if($extranjero)
            {
                $persona=$em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('id'=>$extranjero->getId()));
                return $persona;
            }
            else
                return false;
        }
        else
            return false;
    }

    public function saveFormApoderadoAction(Request $request)
    { 
        /*
         ////////////////////////////////////////////////////////////////////
         ///////////// Registro de apoderado PADRE, MADRE Y TUTOR  /////////////////
         ////////////////////////////////////////////////////////////////////
        */
        $form = $request->get('form');
        $idApoderadoInscripcion = $form['id'];
        $idPersona = $form['idPersona'];
        
        
        $em = $this->getDoctrine()->getManager();

        $rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['rudeId']);

        $tipo = $request->get('tipo');

       
        if($tipo == 'padre'){
            $parentesco = array(1);
            $tiene = $request->get('p_tienePadre');
        }
        if($tipo == 'madre'){
            $parentesco = array(2);
            $tiene = $request->get('m_tieneMadre');
        }
        if($tipo == 'tutor'){
            $parentesco = $this->obtenerCatalogo($rude, 'apoderado_tipo');
            $tiene = $request->get('t_tieneTutor');
        }

        if($tipo == 'te_tutor'){
            $parentesco = array(14);
            $tiene = $request->get('te_tieneTutor');
        }

        
        // expedido
        if (empty($form['expedido']) && strlen($form['expedido'])==0) {
            $expedido = 0;
        }else{
            $expedido = $form['expedido'];
        }

        // VERIFICAMOS SI EL ESTUDIANTE TIENE // PADRE // MADRE // O TUTOR // O EXTRAORDINARIO
        if($tiene == 1)
        {
            // VERIFICAMOS SI LA PERSONA ES NUEVA SIN CARNET
            // O ES NUEVO PERO VALIDADO POR EL SEGIP
            
            if($form['idPersona'] == 'nuevo' or $form['idPersona'] == 'segip' or $form['idPersona'] == 'extranjero')
            {   
                // PREGUNTAMOS SI EL CARNET NO ESTA VACIO
                // ENTONCES EL DATO VIENE DEL SERVICIO SEGIP
                if($form['carnet'] != "")
                {
                    // BUSCAMOS LA PERSONA EN LA BASE DE DATOS
                    $form['paterno'] = str_replace('--', '', $form['paterno']);
                    $form['materno'] = str_replace('--', '', $form['materno']);

                    //verificamos si son personas extranjeras
                    $personaExtranjera = $this->buscarPersonaExtranjero($form['carnet']);
                    if($personaExtranjera &&  $form['idPersona'] == 'extranjero')//personas extranjeras
                    {
                        $persona=$personaExtranjera;
                    }
                    else //personas nacionales
                    {
                        /*
                        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                            'carnet'=>$form['carnet'],
                            'complemento'=>mb_strtoupper($form['complemento'],'utf-8'),
                            'paterno'=>mb_strtoupper($form['paterno'],'utf-8'),
                            'materno'=>mb_strtoupper($form['materno'],'utf-8'),
                            'nombre'=>mb_strtoupper($form['nombre'],'utf-8'),
                            'fechaNacimiento'=>new \DateTime($form['fechaNacimiento'])
                        ));
                        // VERIFICAMOS SI EL REGISTRO NO EXISTE EN LA BASE DE DATOS
                        if (!$persona)
                        {
                            // BUSCAMOS NUEVAMENTE A LA PERSONA SIN COMPLEMENTO EN LA BASE DE DATOS
                            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                                'carnet'=>$form['carnet'],
                                'paterno'=>mb_strtoupper($form['paterno'],'utf-8'),
                                'materno'=>mb_strtoupper($form['materno'],'utf-8'),
                                'nombre'=>mb_strtoupper($form['nombre'],'utf-8'),
                                'fechaNacimiento'=>new \DateTime($form['fechaNacimiento'])
                            ));
                            // SI EXISTE LA PERSONA ENTONCES LE AGREGAMOS EL COMPLEMENTO
                            if ($persona)
                            {
                                //Correcion del bug cuando existe dos personas que tienen el mismo CI pero son distitas en fecha 19/03/2021
                                $objPersonasConMismoCarnet=$em->getRepository('SieAppWebBundle:Persona')->findBy(array('carnet'=>$persona->getCarnet()));
                                if($objPersonasConMismoCarnet)
                                {
                                    foreach ($objPersonasConMismoCarnet as $value)
                                    {
                                        if($persona->getId()!= $value->getId() && $value->getSegipId()==0)
                                        {
                                            $value->setCarnet($value->getCarnet().'±');
                                            $em->flush();
                                        }
                                    }
                                }
                                $persona->setComplemento(mb_strtoupper($form['complemento'],'utf-8'));
                            }
                            else
                            {
                                //Validacion para evitar que se vuelva a registrar con Carnet=$form['carnet'] y complemento=±,
                                //validacion creada con apoyo del lic Carlos y lic Erik el 12/03/2021
                                $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                                    'complemento'=>mb_strtoupper($form['complemento'],'utf-8'),
                                    'carnet'=>$form['carnet'],
                                    'fechaNacimiento'=>new \DateTime($form['fechaNacimiento']),
                                    'segipId'=>'1'
                                ));
                                if($persona!=NULL)
                                {
                                    $persona->setCarnet($form['carnet']);
                                    //$persona->setComplemento(mb_strtoupper($form['complemento'],'utf-8'));
                                    $persona->setPaterno(mb_strtoupper($form['paterno'],'utf-8'));
                                    $persona->setMaterno(mb_strtoupper($form['materno'],'utf-8'));
                                    $persona->setNombre(mb_strtoupper($form['nombre'],'utf-8'));
                                    $persona->setFechaNacimiento=new \DateTime($form['fechaNacimiento']);
                                }
                                else
                                {
                                    //Validacion para corregir si la fecha de nacimiento de una persona validad por el segip, es diferente en la BD el 22/03/2021
                                    $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                                        'paterno'=>mb_strtoupper($form['paterno'],'utf-8'),
                                        'materno'=>mb_strtoupper($form['materno'],'utf-8'),
                                        'nombre'=>mb_strtoupper($form['nombre'],'utf-8'),
                                        'segipId'=>'1'));
                                    if($persona!=NULL)
                                    {
                                        $persona->setFechaNacimiento=new \DateTime($form['fechaNacimiento']);
                                    }
                                    else
                                    {
                                        $persona=null;
                                    }
                                }
                            }
                        }
                        else
                        {
                            // SI EL REGISTRO EXISTE EN LA BASE DE DATOS
                            // VERIFICAMOS SI ESTA VALIDADO POR EL SEGIP, SI NO ESTA VALIDADO LO VALIDAMOS
                            if($persona->getSegipId() != 1){
                                $persona->setSegipId(1);
                            }
                        }
                       */
                      //persona con segip 1
                      //$persona = $this->buscarPersona($form,true,1);//busqueda de persona con carnet y segip =1
                      
                      $persona = $this->get('buscarpersonautils')->buscarPersona($form,$conCI=true, $segipId=1);
                      
                    }
                }
                else
                {
                    /*
                    // SI EL DATO VIENE SIN CARNET
                    // BUSCAMOS PERSONA POR SUS DATOS PERSONALES EN LA BASE DE DATOS
                    $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                        'paterno'=>mb_strtoupper($form['paterno'],'utf-8'),
                        'materno'=>mb_strtoupper($form['materno'],'utf-8'),
                        'nombre'=>mb_strtoupper($form['nombre'],'utf-8'),
                        'fechaNacimiento'=>new \DateTime($form['fechaNacimiento'])
                    ));
                    // VERIFICAMOS SI EXISTE LA PERSONA
                    if($persona){
                        // BUSCAMOS EL TEXTO SC "SIN CARNET" EN EL CAMPO CARNET
                        $pos = strpos($persona->getCarnet(), 'SC');
                        // SI NO SE ENCUENTRA EL TEXTO
                        // LA PERSONA ENCONTRADA CUENTA CON CARNET
                        // ENTONCES DECLARAMOS LA VARABLE PERSONA COMO NULL PARA HACER EL REGISTRO
                        // DE LA PERSONA SIN CARNET
                        if($pos === false){
                            $persona == null;
                        }
                    }else{
                        $persona = null;
                    }
                    */
                   //$persona = $this->buscarPersona($form,false,1);//busqueda de persona sin carnet y segip =1
                   $persona = $this->get('buscarpersonautils')->buscarPersona($form,$conCI=false, $segipId=1);
                }

                
                // VERIFICAMOS SI LA PERSONA EXISTE
                if($persona)
                {   
                    
                    // SI EXISTE LA PERSONA SOLO ACTUALIZAMOS:
                    // FECHA DE NACIMIENTO
                    // CELULAR
                    // EXPEDIDO
                    $persona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
                    $persona->setCelular($form['celular']);
                    $persona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedido));

                    if(isset($form['estado_civil']))
                        $persona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find($form['estado_civil']));

                    if(isset($form['apellido_esposo']))
                        $persona->setApellidoEsposo(mb_strtoupper($form['apellido_esposo'],'utf-8'));
                    if(isset($form['cedulaTipoId']))
                        $persona->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find($form['cedulaTipoId']));
                     
                    $persona->setCorreo($form['correo']);
                    $em->flush();
                    $idPersona = $persona->getId();
                }
                else
                {
                    /*
                    // SI LA PERSONA NO EXISTE
                    // PREGUNTAMOS SI EL CARNET NO ESTA VACIO PARA BUSCAR LAS COINCIDENCIAS DE CARNET EN LA BASE DE DATOS
                    if($form['carnet'] != ""){
                        // BUSCAMOS SI EL NUMERO DE CARNET Y COMPLEMENTO ESTA REGISTRADO CON OTRA PERSONA
                        $personaAnterior = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                            'carnet'=>$form['carnet'],
                            'complemento'=>mb_strtoupper($form['complemento'],'utf-8')
                        ));

                        // VERIFICAMOS SI EXISTE OTRA PERSONA CON EL MISMO CARNET Y COMPLEMENTO
                        // SI ENCUENTA ACTUALIZAMOS EL NUMERO DE CARNET CON EL CARACTER ESPECIAL
                        if($personaAnterior)
                        {
                            // VERIFICAMOS SI EL REGISTRO ENCONTRADO NO ESTA VALIDADO POR EL SEGIP
                            // PARA ACTUALIZAR SU NUERO DE CARNET Y AGREGARLE EL CARACTER ESPECIAL (±)
                            if ($personaAnterior->getSegipId() != 1)
                            {
                                $personaAnterior->setCarnet($personaAnterior->getCarnet().'±');
                                $em->flush();
                            }
                            else
                            {
                                // SI EL CARNET ESTA OCUPADO POR OTRA PERSONA Y TAMBIEN VALIDADO POR EL SEGIP
                                // NO REALIZAMOS EL REGISTRO Y MANDAMOS UN ERROR DE CARNET DUPLICADO
                                $response = new JsonResponse();
                                return $response->setData([
                                    'status'=>500,
                                    'msg'=>'Datos personales observado . No se pudo realizar el registro, comuníquese con su técnico de Distrito'
                                ]);
                            }
                        }
                    }
                    else
                    {
                        // LISTAMOS LOS REGISTROS SIN CARNET Y
                        // GENERAMOS UN CARNET FICTICIO PARA EL NUEVO REGISTRO
                        $personasSinCarnet = $em->createQueryBuilder()
                                            ->select('p')
                                            ->from('SieAppWebBundle:Persona','p')
                                            ->where('p.carnet like :palabra')
                                            ->setParameter('palabra','SC%')
                                            ->getQuery()
                                            ->getResult();

                        $form['carnet'] = 'SC'. (count($personasSinCarnet) + 1);
                    }
                    // REGISTRAMOS LOS DATOS DE LA PERSONA
                    $nuevaPersona = new Persona();
                    //$nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find(98));
                    $nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find(0));
                    $nuevaPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['genero']));
                    $nuevaPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find(7));
                    $nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find(0));
                    $nuevaPersona->setCarnet($form['carnet']);
                    $nuevaPersona->setComplemento(mb_strtoupper($form['complemento'],'utf-8'));
                    $nuevaPersona->setCelular($form['celular']);
                    $nuevaPersona->setRda(0);
                    $nuevaPersona->setPaterno(mb_strtoupper($form['paterno'],'utf-8'));
                    $nuevaPersona->setMaterno(mb_strtoupper($form['materno'],'utf-8'));
                    $nuevaPersona->setNombre(mb_strtoupper($form['nombre'],'utf-8'));
                    if(isset($form['estado_civil'])){
                        $nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find($form['estado_civil']));
                    }
                    if(isset($form['apellido_esposo'])){
                        $nuevaPersona->setApellidoEsposo(mb_strtoupper($form['apellido_esposo'],'utf-8'));
                    }                    
                    $nuevaPersona->setCorreo($form['correo']);

                    $nuevaPersona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
                    if($form['idPersona'] == 'segip'){
                        $nuevaPersona->setSegipId(1);
                    }else{
                        $nuevaPersona->setSegipId(0);
                    }
                    $nuevaPersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedido));
                    $em->persist($nuevaPersona);
                    $em->flush();

                    $idPersona = $nuevaPersona->getId();
                    
                    // REGISTRAMOS LOS DATOS DE LA PERSONA
                    $nuevaPersona = new Persona();
                    //$nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find(98));
                    $nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find(0));
                    $nuevaPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['genero']));
                    $nuevaPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find(7));
                    $nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find(0));
                    $nuevaPersona->setCarnet($form['carnet']);
                    $nuevaPersona->setComplemento(mb_strtoupper($form['complemento'],'utf-8'));
                    $nuevaPersona->setCelular($form['celular']);
                    $nuevaPersona->setRda(0);
                    $nuevaPersona->setPaterno(mb_strtoupper($form['paterno'],'utf-8'));
                    $nuevaPersona->setMaterno(mb_strtoupper($form['materno'],'utf-8'));
                    $nuevaPersona->setNombre(mb_strtoupper($form['nombre'],'utf-8'));
                    if(isset($form['estado_civil']))
                    {
                        $nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find($form['estado_civil']));
                    }
                    if(isset($form['apellido_esposo']))
                    {
                        $nuevaPersona->setApellidoEsposo(mb_strtoupper($form['apellido_esposo'],'utf-8'));
                    }                    
                    $nuevaPersona->setCorreo($form['correo']);

                    $nuevaPersona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
                    if($form['idPersona'] == 'segip'){
                        $nuevaPersona->setSegipId(1);
                    }else{
                        $nuevaPersona->setSegipId(0);
                    }
                    $nuevaPersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedido));
                    $em->persist($nuevaPersona);
                    $em->flush();

                    $idPersona = $nuevaPersona->getId();
                    */
                    
                    $fecha = str_replace('-','/',$form['fechaNacimiento']);
                    $complemento = $form['complemento'] == ''? '':$form['complemento'];
                    $arrayDatosPersona = array(
                        //'carnet'=>$form['carnet'],
                        'complemento'=>$complemento,
                        'paterno'=>$form['paterno'],
                        'materno'=>$form['materno'],
                        'nombre'=>$form['nombre'],
                        'fecha_nacimiento' => $fecha
                    );
                    if(isset($form['extranjero']))
                        $arrayDatosPersona['extranjero'] = 'extranjero';
                    
                    
                    /*$personaValida = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($form['carnet'], $arrayDatosPersona, 'prod', 'academico');

                    if($personaValida)//verificamos que se persona valida por segip
                    {*/
                        // REGISTRAMOS LOS DATOS DE LA PERSONA
                        $nuevaPersona = new Persona();
                        //$nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find(98));
                        $nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find(0));
                        $nuevaPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['genero']));
                        $nuevaPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find(7));
                        $nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find(0));
                        $nuevaPersona->setCarnet($form['carnet']);
                        $nuevaPersona->setComplemento(mb_strtoupper($form['complemento'],'utf-8'));
                        $nuevaPersona->setCelular($form['celular']);
                        $nuevaPersona->setRda(0);
                        $nuevaPersona->setPaterno(mb_strtoupper($form['paterno'],'utf-8'));
                        $nuevaPersona->setMaterno(mb_strtoupper($form['materno'],'utf-8'));
                        $nuevaPersona->setNombre(mb_strtoupper($form['nombre'],'utf-8'));
                        if(isset($form['cedulaTipoId']))
						{
							$nuevaPersona->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find($form['cedulaTipoId']));
						}
                        if(isset($form['estado_civil']))
                        {
                            $nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find($form['estado_civil']));
                        }
                        if(isset($form['apellido_esposo']))
                        {
                            $nuevaPersona->setApellidoEsposo(mb_strtoupper($form['apellido_esposo'],'utf-8'));
                        }
                        $nuevaPersona->setCorreo($form['correo']);
                        $nuevaPersona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
                        /*if($form['idPersona'] == 'segip'){
                            $nuevaPersona->setSegipId(1);
                        }else{
                            $nuevaPersona->setSegipId(1);
                        }*/
                        $nuevaPersona->setSegipId(1);
                        $nuevaPersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedido));
                        $em->persist($nuevaPersona);
                        $em->flush();
                        $idPersona = $nuevaPersona->getId();
                    /*}else{
                        $response = new JsonResponse();
                        return $response->setData([
                            'status'=>404,
                            'msg'=>'Los datos no son validos según SEGIP.'
                        ]);
                    }*/
                }
            }
            else
            {
                /*----------  SI LA PERSONA EXISTE EN LA BASE DIRECTAMENTE  ----------*/
                // MODIFICAMOS LOS DATOS DE LA PERSONA
                $actualizarPersona = $em->getRepository('SieAppWebBundle:Persona')->find($form['idPersona']);
                if($actualizarPersona)
                {
                    // ACTUALIZAMOS LOS DATOS DE LA PERSONA
                    $actualizarPersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedido));
                    $actualizarPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['genero']));
                    $actualizarPersona->setCorreo($form['correo']);
                    $actualizarPersona->setCelular($form['celular']);
                    if(isset($form['cedulaTipoId']))
					{
						$actualizarPersona->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find($form['cedulaTipoId']));
					}
                    if(isset($form['estado_civil']))
                    {
                        $actualizarPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find($form['estado_civil']));
                    }
                    if(isset($form['apellido_esposo']))
                    {
                        $actualizarPersona->setApellidoEsposo(mb_strtoupper($form['apellido_esposo'],'utf-8'));
                    }
                    $em->flush();
                }
                $idPersona = $actualizarPersona->getId();
            }


            // Verficamos si el registro de apoderado es nuevo
            // if($form['id'] == 'nuevo'){
            //     $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_apoderado_inscripcion');")->execute();
            //     $nuevoApoderado = new RudeApoderadoInscripcion();
            //     $nuevoApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['apoderadoTipo']));
            //     $nuevoApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
            //     $nuevoApoderado->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['idInscripcion']));
            //     $nuevoApoderado->setIdiomaMaternoTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idiomaMaterno']));
            //     $nuevoApoderado->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($form['instruccionTipo']));
            //     $nuevoApoderado->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($form['ocupacion']));
            //     $nuevoApoderado->setObs(mb_strtoupper($form['obs'],'utf-8'));
            //     $em->persist($nuevoApoderado);
            //     $em->flush();

            //     $idApoderadoInscripcion = $nuevoApoderado->getId();
            // }else{

            //     $actualizarApoderado = $em->getRepository('SieAppWebBundle:RudeApoderadoInscripcion')->find($form['id']);
            //     if($actualizarApoderado){
            //         $actualizarApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['apoderadoTipo']));
            //         $actualizarApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
            //         $actualizarApoderado->setIdiomaMaternoTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idiomaMaterno']));
            //         $actualizarApoderado->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($form['instruccionTipo']));
            //         $actualizarApoderado->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($form['ocupacion']));
            //         $actualizarApoderado->setObs(mb_strtoupper($form['obs'],'utf-8'));
            //         $em->flush();
            //     }
            //     $idApoderadoInscripcion = $actualizarApoderado->getId();
            // }

            
            // VERFICAMOS SI EL REGISTRO DE APODERADO ES NUEVO LO REGISTRAMOS
            if($form['id'] == 'nuevo'){
                
                    $nuevoApoderado = new ApoderadoInscripcion();
                    $nuevoApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['apoderadoTipo']));
                    $nuevoApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
                    $nuevoApoderado->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['idInscripcion']));
                    $nuevoApoderado->setFechaRegistro(new \DateTime('now'));
                    $em->persist($nuevoApoderado);
                    $em->flush();

                    $idApoderadoInscripcion = $nuevoApoderado->getId();
                
            }else{
                // ACTUALIZAMOS EL DATO DE INSCRIPCION DEL APODERADO
                $actualizarApoderado = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($form['id']);
                if($actualizarApoderado){
                    $actualizarApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['apoderadoTipo']));
                    $actualizarApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
                    $em->flush();
                }
                $idApoderadoInscripcion = $actualizarApoderado->getId();
            }

            
            // VERIFICAMOS SI EL REGISTRO DE DATOS DE APODERADO ES NUEVO
            
            if($form['idDatos'] == 'nuevo'){
                
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('apoderado_inscripcion_datos');")->execute();
                    $nuevoApoderadoDatos = new ApoderadoInscripcionDatos();
                    $nuevoApoderadoDatos->setApoderadoInscripcion($em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($idApoderadoInscripcion));
                    $nuevoApoderadoDatos->setTelefono($form['telefono']);
                    $nuevoApoderadoDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($form['ocupacion']));
                    $nuevoApoderadoDatos->setEmpleo(mb_strtoupper($form['empleo'],'utf-8'));
                    
                    if ($form['apoderadoTipo'] == 14){
                        $nuevoApoderadoDatos->setInstitucionTrabaja(mb_strtoupper($form['institucionTrabaja'],'utf-8'));
                        $nuevoApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find(0));
                        $nuevoApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find(0));
                    } else {
                        $nuevoApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idiomaMaterno']));
                        $nuevoApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($form['instruccionTipo']));
                    }
                    // $nuevoApoderadoDatos->setObs(mb_strtoupper($form['obs'],'utf-8'));
                    $em->persist($nuevoApoderadoDatos);
                    $em->flush();

                    $idApoderadoInscripcionDatos = $nuevoApoderadoDatos->getId();
               
            }else{
                // ACTUALIZAMOS EL REGISTRO DE DATOS DEL APODERADO
                $actualizarApoderadoDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->find($form['idDatos']);
                if($actualizarApoderadoDatos){
                    // $actualizarApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idiomaMaterno']));
                    // $actualizarApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($form['instruccionTipo']));
                    $actualizarApoderadoDatos->setTelefono($form['telefono']);
                    $actualizarApoderadoDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($form['ocupacion']));
                    $actualizarApoderadoDatos->setEmpleo(mb_strtoupper($form['empleo'],'utf-8'));

                    if ($form['apoderadoTipo'] == 14){
                        $actualizarApoderadoDatos->setInstitucionTrabaja(mb_strtoupper($form['institucionTrabaja'],'utf-8'));
                        $actualizarApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find(0));
                        $actualizarApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find(0));
                    } else {
                        $actualizarApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idiomaMaterno']));
                        $actualizarApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($form['instruccionTipo']));
                    }

                    // if ($form['apoderadoTipo'] == 14) 
                    
                    // $actualizarApoderadoDatos->setInstitucionTrabaja(mb_strtoupper($form['institucionTrabaja'],'utf-8'));
                    // $actualizarApoderadoDatos->setObs(mb_strtoupper($form['obs'],'utf-8'));
                    $em->flush();

                    $idApoderadoInscripcionDatos = $actualizarApoderadoDatos->getId();
                }
            }

        }
        else
        {

            // SI SE INDICA QUE EL ESTUDIANTE NO TIENE ASIGNADO EL APODERADO
            // SE ELIMINA EL REGISTRO DE APODERADO 
            
            // $apod = $em->getRepository('SieAppWebBundle:RudeApoderadoInscripcion')->findBy(array('estudianteInscripcion'=>$form['idInscripcion'],'apoderadoTipo'=>$parentesco));
            $apod = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion'=>$form['idInscripcion'],'apoderadoTipo'=>$parentesco));
            foreach ($apod as $a) {
                $eliminar = $em->createQueryBuilder()
                    ->delete('')
                    ->from('SieAppWebBundle:ApoderadoInscripcionDatos','aid')
                    ->where('aid.apoderadoInscripcion = :apoderadoInscripcion')
                    ->setParameter('apoderadoInscripcion', $a->getId())
                    ->getQuery()
                    ->getResult();

                $em->remove($a);
                $em->flush();
            }

            $idApoderadoInscripcion = 'nuevo';
            $idApoderadoInscripcionDatos = 'nuevo';
            $idPersona = 'nuevo';
        }

        // AGREGAMOS LA VALIDACION EN EL UTIMO PASO DE ASIGNAR TUTOR
        // PARA QUE SE VALIDE QUE SE REGISTREN LOS DATOS DEL PADRE, MADRE O TUTOR
        // SEGUN LO QUE SE INDICO EN EL APARTADO - CON QUIEN VIVE


        if($tipo == 'tutor' or $tipo == 'te_tutor' ){
            // APODERADOS QUE TIENE ACTUALMENTE REGISTRADOS EL ESTUDIANTE
            $apoderados = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array(
                'estudianteInscripcion'=>$form['idInscripcion']
            ));
            $tiposApoderados = [];
            foreach ($apoderados as $ap) {
                $tiposApoderados[] = $ap->getApoderadoTipo()->getId();
            }


            $catalogo = $this->obtenerCatalogo($rude, 'apoderado_tipo');
            $variable = (in_array(3, $this->obtenerCatalogo($rude, 'apoderado_tipo')))?true:false;

            // VALIDACION CON QUIEN VIVE EL ESTUDIANTE
            $status = 200;
            if($rude->getViveHabitualmenteTipo() != null){
                switch ($rude->getViveHabitualmenteTipo()->getId()) {
                    case 1: // PADRE Y MADRE
                        if(!in_array(1, $tiposApoderados) or !in_array(2, $tiposApoderados) ){
                            $msg = "Debe registrar los datos del padre y la madre";
                            $status = 500;
                        }
                        break;
                    case 2: // SOLO PADRE 
                        if(!in_array(1, $tiposApoderados)){
                            $msg = "Debe registrar los datos del padre";
                            $status = 500;
                        }
                        break;
                    case 3: // SOLO MADRE
                        if(!in_array(2, $tiposApoderados) ){
                            $msg = "Debe registrar los datos de la madre";
                            $status = 500;
                        }
                        break;
                    case 4: // TUTOR
                        $apoderadosTutores = $this->obtenerCatalogo($rude, 'apoderado_tipo');
                        $tieneApoderado = false;
                        foreach ($apoderadosTutores as $key => $value) {
                            if (in_array($value, $tiposApoderados )) {
                                $tieneApoderado = true;
                            }
                        }

                        if(!$tieneApoderado){
                            $msg = "Debe registrar datos del tutor";
                            $status = 500;
                        }
                        break;
                    case 6: // TUTOR EXTRAORDINARIO
                        if(!in_array(14, $tiposApoderados) ){
                            $msg = "Debe registrar los datos del tutor extraordinario";
                            $status = 500;
                        }
                        break;
                }
            }else{
                $status = 500;
                $msg = "Debe especificar con quien vive el o la estudiante!!!";
            }

            if($status == 500){
                $response = new JsonResponse();
                return $response->setData([
                    'status'=>$status,
                    'msg'=>$msg
                ]);
            }else{
                // HACEMOS EL REGISTRO PASO 4 
                // Y HABILITAMOS EL SIGUINTE PASO
                if($rude->getRegistroFinalizado() < 4){
                    $rude->setRegistroFinalizado(4);
                    $em->flush();
                }
            }
        }

        $response = new JsonResponse();
        return $response->setData([
            'status'=>200,
            'id'=>$idApoderadoInscripcion,
            'idDatos'=>$idApoderadoInscripcionDatos,
            'idPersona'=>$idPersona
        ]);
    }

   
    public function createFormLugar($rude){
        if($rude->getFechaRegistroRude()){
            $fecha = $rude->getFechaRegistroRude()->format('d-m-Y');
        }else{
            $fecha = '';
        }
        $form = $this->createFormBuilder($rude)
                    ->add('id', 'hidden')
                    ->add('lugarRegistroRude', 'text', array('required' => true))
                    ->add('fechaRegistroRude', 'text', array('required' => true, 'data'=>$fecha))             
                    ->getForm();

        return $form;
    }

    public function saveFormLugarAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['id']);
        $rude->setLugarRegistroRude(mb_strtoupper($form['lugarRegistroRude'],'utf-8'));
        $rude->setFechaRegistroRude(new \DateTime($form['fechaRegistroRude']));
        // Registro paso 5
        if($rude->getRegistroFinalizado() < 5){
            $rude->setRegistroFinalizado(5);
        }

        $em->flush();

        $response = new JsonResponse();
        return $response->setData(['msg'=>'ok']);
    }

    public function obtenerCatalogo($rude, $tabla){
        $em = $this->getDoctrine()->getManager();
        $gestion = $rude->getEstudianteInscripcion()->getInstitucioneducativaCurso()->getGestionTipo()->getId();
        //dump($gestion);die;
        // $gestion = 2019;
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

    /*
     * GUARDAR OBSERVACIÓN PARA EXTRANJEROS
     */

     public function saveExtObsAction(Request $request){
        $rudeId = $request->get('rudeId');
        $obsExtTipo = $request->get('obsExpTipo');
        $obsExtJust = $request->get('obsExpJust');
        $em = $this->getDoctrine()->getManager();
        $rudeObs = $em->getRepository('SieAppWebBundle:RudeObservacionExtranjero')->findBy(['rude' => $rudeId,]);
        if (!$rudeObs){
            $newRudeObsExt = new RudeObservacionExtranjero();
            $newRudeObsExt->setRude($em->getRepository('SieAppWebBundle:Rude')->find($rudeId));
            $newRudeObsExt->setObservacionExtranjeroTipo($em->getRepository('SieAppWebBundle:ObservacionExtranjeroTipo')->find($obsExtTipo));
            $newRudeObsExt->setObservacionOtro($obsExtJust);
            $newRudeObsExt->setFechaRegistro(new \DateTime('now'));
            $em->persist($newRudeObsExt);
            $em->flush();
            $response = new JsonResponse();
            return $response->setData(['msg'=>'ok']);
        } else {
            $response = new JsonResponse();
            return $response->setData(['msg'=>'Ya Justificó']);
        }
     }
}
