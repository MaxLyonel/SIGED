<?php


namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
use Sie\AppWebBundle\Entity\RudeMediosComunicacion;
use Sie\AppWebBundle\Entity\RudeMedioTransporte;
use Sie\AppWebBundle\Entity\RudeAbandono;
use Sie\AppWebBundle\Entity\RudeApoderadoInscripcion;
use Sie\AppWebBundle\Entity\RudeEducacionDiversa;
use Sie\AppWebBundle\Entity\Persona;


use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 */
class EstudianteRudealController extends Controller {

    public $session;
    public $estado;
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
        $em->getConnection()->beginTransaction();

        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');
        $editar = $request->get('editar');

        $aInfoUeducativa = unserialize($infoUe);
        $aInfoStudent = json_decode($infoStudent, TRUE);
        

        $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfoId']['iecId']);
            $sie = $iec->getInstitucioneducativa()->getId();
        $gestion = $iec->getGestionTipo()->getId();

        $idInscripcion = $aInfoStudent['eInsId'];
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

        $estudiante = $inscripcion->getEstudiante();
        // Validacion de datos del estudiante
        // if()
        // FIND RUDE CURRENT GESTION
        $rude = $em->getRepository('SieAppWebBundle:Rude')->findOneBy(array(
            'estudianteInscripcion'=>$inscripcion->getId()
        ));
        // dump($inscripcion->getId());die;
        if(!is_object($rude)){

            try {
                /**
                 * OBTENEMOS UN REGISTRO ANTERIOR
                 */
                // dump("NO RUDEEEEEEEEEEEEEEEEEEEEEEEEEEE");die;
                // $rudeAnterior = $em->createQueryBuilder()
                //             ->select('r')
                //             ->from('SieAppWebBundle:Rude','r')
                //             ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','r.estudianteInscripcion = ei.id')
                //             ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                //             ->where('e.id = :estudiante')
                //             ->setParameter('estudiante', $estudiante->getId())
                //             //->setMaxResults(1)
                //             ->orderBy('r.id','desc')
                //             ->getQuery()
                //             ->getResult();

                // SOLO DE HASTA 3 PERIODOS O UN YEAR Y MEDIO DESDE LA ULTIMA INSCRIPCION

                $db = $em->getConnection();
                $query = "select ei.id, ic.gestion_tipo_id, r.id as rude_id from estudiante_inscripcion ei 
                            inner join institucioneducativa_curso ic on ic.id=ei.institucioneducativa_curso_id
                            inner join estudiante e on ei.estudiante_id=e.id
                            left join rude r on r.estudiante_inscripcion_id=ei.id 
                            where e.codigo_rude='".$estudiante->getCodigoRude()."'
                            group by 1,2,3
                            order by ei.id desc
                            limit 3";
                $stmt = $db->prepare($query);
                $params = array();
                $stmt->execute($params);
                $results = $stmt->fetchAll();
                
                $rudeAnterior = [];
                if( $results ){
                    
                    foreach( $results as $value ){
                        if( $value['rude_id'] !== null ){
                            $rudeAnterior = $em->createQueryBuilder()
                                ->select('r')
                                ->from('SieAppWebBundle:Rude','r')
                                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','r.estudianteInscripcion = ei.id')
                                ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                                ->where('r.id = :rude_id')
                                ->setParameter('rude_id', $value['rude_id'])
                                ->getQuery()
                                ->getResult();
                        }
                    }

                }

                if(count($rudeAnterior) == 1){
                    
                    $rudeAnterior = $rudeAnterior[0];
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude');")->execute();
                    $rude = clone $rudeAnterior;
                    $rude->setEstudianteInscripcion($inscripcion);
                    $rude->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find(2));
                    $em->persist($rude);
                    $em->flush();

                    // REGISTRO DE EDUCACION DIVERSA
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_educacion_diversa');")->execute();
                    $diversas = $em->getRepository('SieAppWebBundle:RudeEducacionDiversa')->findBy(array('rude'=>$rudeAnterior));
                    foreach ($diversas as $di) {
                        $newDiversa = clone $di;
                        $newDiversa->setRude($rude);
                        $em->persist($newDiversa);
                        $em->flush();
                    }

                    // REGISTRO DE DISCAPACIDADES
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_discapacidad_grado');")->execute();
                    $discapacidades = $em->getRepository('SieAppWebBundle:RudeDiscapacidadGrado')->findBy(array('rude'=>$rudeAnterior));
                    foreach ($discapacidades as $d) {
                        $newDiscapacidad = clone $d;
                        $newDiscapacidad->setRude($rude);
                        $em->persist($newDiscapacidad);
                        $em->flush();
                    }

                    // REGISTRO DE IDIOMAS
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_idioma');")->execute();
                    $idiomas = $em->getRepository('SieAppWebBundle:RudeIdioma')->findBy(array('rude'=>$rudeAnterior));
                    foreach ($idiomas as $i) {
                        $newIdioma = clone $i;
                        $newIdioma->setRude($rude);
                        $em->persist($newIdioma);
                        $em->flush();
                    }

                    // REGISTRO DE ACTIVIDADES OCUPACIONES
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_actividad');")->execute();
                    $actividades = $em->getRepository('SieAppWebBundle:RudeActividad')->findBy(array('rude'=>$rudeAnterior));
                    foreach ($actividades as $a) {
                        $newActividad = clone $a;
                        $newActividad->setRude($rude);
                        $em->persist($newActividad);
                        $em->flush();
                    }

                    // REGISTRO DE ACUDIO CENTRO SALUD
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_centro_salud');")->execute();
                    $centrosSalud = $em->getRepository('SieAppWebBundle:RudeCentroSalud')->findBy(array('rude'=>$rudeAnterior));
                    foreach ($centrosSalud as $cs) {
                        $newCentroSalud = clone $cs;
                        $newCentroSalud->setRude($rude);
                        $em->persist($newCentroSalud);
                        $em->flush();
                    }

                    // REGISTRO DE MEDIOS DE COMUNICACION
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_medios_comunicacion');")->execute();
                    $mediosComunicacion = $em->getRepository('SieAppWebBundle:RudeMediosComunicacion')->findBy(array('rude'=>$rudeAnterior));
                    foreach ($mediosComunicacion as $mc) {
                        $newMedioComunicacion = clone $mc;
                        $newMedioComunicacion->setRude($rude);
                        $em->persist($newMedioComunicacion);
                        $em->flush();
                    }

                    // REGISTRO DE MEDIOS DE TRANSPORTE
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_medio_transporte');")->execute();
                    $mediosTransporte = $em->getRepository('SieAppWebBundle:RudeMedioTransporte')->findBy(array('rude'=>$rudeAnterior));
                    foreach ($mediosTransporte as $mt) {
                        $newMedioTransporte = clone $mt;
                        $newMedioTransporte->setRude($rude);
                        $em->persist($newMedioTransporte);
                        $em->flush();
                    }

                    // REGISTRO DE MOTIVOS DE ABANDONO
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_abandono');")->execute();
                    $motivosAbandono = $em->getRepository('SieAppWebBundle:RudeAbandono')->findBy(array('rude'=>$rudeAnterior));
                    foreach ($motivosAbandono as $ma) {
                        $newMotivoAbandono = clone $ma;
                        $newMotivoAbandono->setRude($rude);
                        $em->persist($newMotivoAbandono);
                        $em->flush();
                    }

                }else{
                    // SE OBTIENE EL DEPARTAMENTO DE LA UNIDAD EDUCATIVA PARA REGISTRARLO EN LA DIRECCION DONDE SE LLENO EL FORMULARIO RUDEAL
                    $jg = $em->createQueryBuilder()
                                ->select('jg')
                                ->from('SieAppWebBundle:JurisdiccionGeografica','jg')
                                ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','ie.leJuridicciongeografica = jg.id')
                                ->where('ie.id = :sie')
                                ->setParameter('sie', $sie)
                                ->getQuery()
                                ->getResult();

                    $direccion = $jg[0]->getDistritoTipo()->getDepartamentoTipo()->getDepartamento();

                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude');")->execute();

                    // REALIZAMOS EL REGISTRO DEL NUEVO RUDE
                    $rude = new Rude();
                    $rude->setEstudianteInscripcion($inscripcion);
                    $rude->setFechaRegistro(new \DateTime('now'));
                    $rude->setLugarRegistroRude($direccion);
                    $rude->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find(2));
                    $rude->setRegistroFinalizado(0);
                    $em->persist($rude);
                    $em->flush();
                }

                $em->getConnection()->commit();

            } catch (Exception $e) {
                $em->getConnection()->rollback();
                echo "Se produjo un error al cargar los datos del Rudeal";
            }
        }

        // TEXTO DE AYUDA PARA EL COMPLEMENTO
        $ayudaComplemento = ["Complementito","Contenido del complemento, no se refiere al lugar de expedición del documento."];

        return $this->render('SieHerramientaAlternativaBundle:EstudianteRudeal:index.html.twig', [
            'sie'=>$sie,
            'estudiante'=>$estudiante,
            'formEstudiante'=>$this->createFormEstudiante($rude, $estudiante)->createView(),
            'formDireccion'=>$this->createFormDireccion($rude)->createView(),
            'formDiscapacidad'=>$this->createFormDiscapacidad($rude,$estudiante)->createView(),
            'formSocioeconomico'=>$this->createFormSocioeconomico($rude, $estudiante)->createView(),
            'ayudaComplemento'=>$ayudaComplemento,
            'formLugar'=>$this->createFormLugar($rude)->createView(),
            'inscripcion'=>$inscripcion,
            'rude'=>$rude
        ]);
    }

    /**
     * DATOS DE LA O EL ESTUDIANTE
     */
    private function createFormEstudiante($rude, $e){
        $em = $this->getDoctrine()->getManager();
        // $pais = $e->getPaisTipo()->getId();
        // $departamento = '';
        // $provincia = '';
        // if($e->getLugarNacTipo() != null){
        //     $departamento = $e->getLugarNacTipo()->getId();
        // }
        // if($e->getLugarProvNacTipo()){
        //     $provincia = $e->getLugarProvNacTipo()->getId();
        // }

        // $departamentos = array();
        // $provincias = array();

        // if($pais == 1){
        //     $condition = array('lugarNivel' => 1, 'paisTipoId' => $pais);
        //     $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy($condition);
        //     foreach ($dep as $d) {
        //         $departamentos[$d->getId()] = $d->getLugar();
        //     }

        //     $prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $departamento));
        //     foreach ($prov as $p) {
        //         $provincias[$p->getid()] = $p->getlugar();
        //     }
        // }

        // EDUCACION DIVERSA
        $diversa = $em->getRepository('SieAppWebBundle:RudeEducacionDiversa')->findOneBy(array('rude'=>$rude));
        if(!$diversa){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_educacion_diversa');")->execute();
            $newEducacionDiversa = new RudeEducacionDiversa();
            $newEducacionDiversa->setRude($rude);
            $newEducacionDiversa->setEducacionDiversaTipo($em->getRepository('SieAppWebBundle:EducacionDiversaTipo')->find(1));
            $newEducacionDiversa->setFechaRegistro(new \DateTime('now'));
            $em->persist($newEducacionDiversa);
            $em->flush();

            $diversa = $newEducacionDiversa;
        }

        // LUGAR DE NACIMIENTO

        $form = $this->createFormBuilder()
                    // ->setAction($this->generateUrl('info_estudiante_rude_save_form2'))
                    ->add('rudeId', 'hidden', array('data' => $rude->getId(),'mapped'=>false))
                    ->add('estudianteId', 'hidden', array('data' => $e->getId()))
                    ->add('carnet', 'text', array('required' => false, 'data'=>$e->getCarnetIdentidad()))
                    ->add('complemento', 'text', array('required' => false, 'data'=>$e->getCarnetIdentidad()))
                    ->add('expedido', 'entity', array(
                            'class' => 'SieAppWebBundle:DepartamentoTipo',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('dt')
                                        ->where('dt.id not in (0)');
                            },
                            'property'=>'sigla',
                            'empty_value' => 'Seleccionar...',
                            'required' => false,
                            'data'=>($e->getExpedido())?$e->getExpedido():'',
                            'mapped'=>false
                        ))
                    ->add('pasaporte', 'text', array('required' => false, 'data'=>$e->getPasaporte()))
                    ->add('estadoCivil', 'entity', array(
                            'class' => 'SieAppWebBundle:EstadoCivilTipo',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('ec')
                                        ->where('ec.esactivo = true');
                            },
                            'empty_value' => 'Seleccionar...',
                            'required' => true,
                            'data'=>($e->getEstadoCivil())?$e->getEstadoCivil():'',
                            'mapped'=>false
                        ))
                    ->add('cantHijos', 'text', array('required' => true, 'data'=>$rude->getCantHijos()))
                    ->add('esServicioMilitar', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'data'=> ($rude->getEsServicioMilitar())? true: false,
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false,
                            'expanded'=>true
                        ))
                    ->add('formacionEducativa', 'entity', array(
                            'class' => 'SieAppWebBundle:ServicioMilitarTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude) {
                                return $e->createQueryBuilder('smt')
                                        // ->where('smt.esActivo = true');
                                        ->where('smt.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'servicio_militar_tipo'));
                            },
                            'empty_value' => 'Seleccionar...',
                            'required' => true,
                            'data'=>($rude->getServicioMilitarTipo())?$rude->getServicioMilitarTipo():'',
                            'mapped'=>false
                        ))
                    // DIVERSA
                    ->add('diversaId', 'hidden', array('data'=>$diversa->getId()))
                    ->add('diversa', 'entity', array(
                            'class' => 'SieAppWebBundle:EducacionDiversaTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('ed')
                                        // ->where('ed.id in (1,2,3,4)');
                                        ->where('ed.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'educacion_diversa_tipo'));
                            },
                            'property'=>'educacionDiversa',
                            'required' => true,
                            'data'=>($diversa)?$diversa->getEducacionDiversaTipo():1,
                          
                        ))

                    ->getForm();

        return $form;
    }

    public function diversaAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $diversaId = $request->get('diversaId');
        $diversa = $request->get('diversa');

        $rudeEducacionDiversa = $em->getRepository('SieAppWebBundle:RudeEducacionDiversa')->find($diversaId);
        $fuerzaMilitar = null;
        $unidadMilitar = null;
        $unidadesMilitares = [];
        $lugarReclusion = null;
        $recintoPenitenciario = null;
        $recintosPenitenciarios = [];

        // dump($rudeEducacionDiversa);die;

        if($rudeEducacionDiversa->getEducacionDiversaTipo()->getId() == 2){
            if($rudeEducacionDiversa->getUnidadMilitarTipo()){
                $fuerzaMilitar = $rudeEducacionDiversa->getUnidadMilitarTipo()->getFuerzaMilitarTipo()->getId();
                $unidadMilitar = $rudeEducacionDiversa->getUnidadMilitarTipo()->getId();
                $unidadesMilitares = $em->getRepository('SieAppWebBundle:UnidadMilitarTipo')->findBy(array('fuerzaMilitarTipo'=>$rudeEducacionDiversa->getUnidadMilitarTipo()->getFuerzaMilitarTipo()->getId()));
            }
        }
        if($rudeEducacionDiversa->getEducacionDiversaTipo()->getId() == 3){
            if($rudeEducacionDiversa->getRecintoPenitenciarioTipo()){
                $lugarReclusion = $rudeEducacionDiversa->getRecintoPenitenciarioTipo()->getLugarReclusionTipo()->getId();
                $recintoPenitenciario = $rudeEducacionDiversa->getRecintoPenitenciarioTipo()->getId();
                $recintosPenitenciarios = $em->getRepository('SieAppWebBundle:RecintoPenitenciarioTipo')->findBy(array('lugarReclusionTipo'=>$rudeEducacionDiversa->getRecintoPenitenciarioTipo()->getLugarReclusionTipo()->getId()));
            }
        }

        $fuerzasMilitares = $em->getRepository('SieAppWebBundle:FuerzaMilitarTipo')->findAll();
        $lugaresReclusion = $em->getRepository('SieAppWebBundle:LugarReclusionTipo')->findAll();

        return $this->render('SieHerramientaAlternativaBundle:EstudianteRudeal:diversa.html.twig', array(
            'diversa'=>$diversa,
            'rudeEducacionDiversa'=>$rudeEducacionDiversa,
            'fuerzaMilitar'=>$fuerzaMilitar,
            'fuerzasMilitares'=>$fuerzasMilitares,
            'unidadMilitar'=>$unidadMilitar,
            'unidadesMilitares'=>$unidadesMilitares,
            'lugarReclusion'=>$lugarReclusion,
            'lugaresReclusion'=>$lugaresReclusion,
            'recintoPenitenciario'=>$recintoPenitenciario,
            'recintosPenitenciarios'=>$recintosPenitenciarios
        ));
    }

    public function cargarUnidadesMilitaresAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $fuerzaMilitar = $request->get('fuerzaMilitar');
        $query = $em->createQuery(
                        'SELECT umt
                        FROM SieAppWebBundle:UnidadMilitarTipo umt
                        WHERE umt.fuerzaMilitarTipo = :fuerzaMilitar
                        ORDER BY umt.id')
                        ->setParameter('fuerzaMilitar', $fuerzaMilitar);

        $unidadesMilitares = $query->getResult();

        $unidadesArray = array();
        foreach ($unidadesMilitares as $um) {
            $unidadesArray[$um->getId()] = $um->getUnidadMilitar();
        }

        $response = new JsonResponse();
        return $response->setData(array('listaUnidades' => $unidadesArray));
    }

    public function cargarRecintosPenitenciariosAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $lugarReclusion = $request->get('lugarReclusion');
        $query = $em->createQuery(
                        'SELECT rpt
                        FROM SieAppWebBundle:RecintoPenitenciarioTipo rpt
                        WHERE rpt.lugarReclusionTipo = :lugarReclusion
                        ORDER BY rpt.id')
                        ->setParameter('lugarReclusion', $lugarReclusion);

        $recintosPenitenciarios = $query->getResult();

        $recintosArray = array();
        foreach ($recintosPenitenciarios as $rp) {
            $recintosArray[$rp->getId()] = $rp->getRecintoPenitenciario();
        }

        $response = new JsonResponse();
        return $response->setData(array('listaRecintos' => $recintosArray));
    }


    /*
     * GUARDAR DATOS DEL FORMULARIO ESTUDIANTE
     */

    public function saveFormEstudianteAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            $form = $request->get('form');

            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['estudianteId']);
            $estudiante->setEstadoCivil($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find($form['estadoCivil']));

            // if(isset($form['carnet'])){
            //     $estudiante->setCarnetIdentidad($form['carnet']);
            //     $estudiante->setComplemento($form['complemento']);
            //     $estudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($form['expedido']));
            // }

            if(isset($form['expedido']) and $form['expedido'] != ''){
                $estudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($form['expedido']));   
            }

            $estudiante->setPasaporte($form['pasaporte']);

            $rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['rudeId']);
            $rude->setCantHijos($form['cantHijos']);
            $rude->setEsServicioMilitar($form['esServicioMilitar']);
            if($form['esServicioMilitar']){
                $rude->setServicioMilitarTipo($em->getRepository('SieAppWebBundle:ServicioMilitarTipo')->find($form['formacionEducativa']));
            }else{
                $rude->setServicioMilitarTipo(null);
            }

            // EDUCACION DIVERSA
            $rudeEducacionDiversa = $em->getRepository('SieAppWebBundle:RudeEducacionDiversa')->find($form['diversaId']);
            $rudeEducacionDiversa->setEducacionDiversaTipo($em->getRepository('SieAppWebBundle:EducacionDiversaTipo')->find(($form['diversa'] == null)?1:$form['diversa']));

            if($form['diversa'] == 2){            
                $rudeEducacionDiversa->setUnidadMilitarTipo($em->getRepository('SieAppWebBundle:UnidadMilitarTipo')->find($request->get('unidadMilitar')));
                $rudeEducacionDiversa->setRecintoPenitenciarioTipo(null);
            }else{
                if($form['diversa'] == 3){
                    $rudeEducacionDiversa->setUnidadMilitarTipo(null);
                    $rudeEducacionDiversa->setRecintoPenitenciarioTipo($em->getRepository('SieAppWebBundle:RecintoPenitenciarioTipo')->find($request->get('recintoPenitenciario')));
                }else{
                    $rudeEducacionDiversa->setUnidadMilitarTipo(null);
                    $rudeEducacionDiversa->setRecintoPenitenciarioTipo(null);
                }
            }
            /////////////////////////

            $em->flush();

            // ACTUALIZAMOS REGISTRO FINALIZADO DEL RUDE
            if($rude->getRegistroFinalizado() == null or $rude->getRegistroFinalizado() < 1){
                $rude->setRegistroFinalizado(1);
            }

            $em->flush();

            $em->getConnection()->commit();

            $response = new JsonResponse();
            return $response->setData(['msg'=>true]);
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            echo "Ocurrio en el registro";
        }
        
    }

    /**
     * CREAR FORMULARIO DE DIRECCION
     */
    public function createFormDireccion($rude){
        //dump($rude);die;
        // DIRECCION
        $em = $this->getDoctrine()->getManager();
        //dump($rude->getLocalidadLugarTipo());die;

        if($rude->getLocalidadLugarTipo() != null){

            // $lt5_id = $rude->getMunicipioLugarTipo()->getLugarTipo();
            // $lt4_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt5_id)->getLugarTipo();
            // $lt3_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt4_id)->getLugarTipo();

            // $m_id = $rude->getMunicipioLugarTipo()->getId();
            // $p_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt5_id)->getId();
            // $d_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt4_id)->getId();
            if ($rude->getLocalidadLugarTipo()->getId() == 0){
                $trozos = explode(",", $rude->getZona());
                $paisInmigrante_id = $em->getRepository('SieAppWebBundle:PaisTipo')->find((int)$trozos[0])->getId();
                $zonainmigrante = $trozos[2];
                //dump($trozos);die;
            }else{
                $lt5_id = $rude->getLocalidadLugarTipo()->getLugarTipo();
                $lt4_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt5_id)->getLugarTipo();
                $lt3_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt4_id)->getLugarTipo();
                $lt2_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt3_id)->getLugarTipo();
                $lt1_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt2_id)->getLugarTipo();
    
                $l_id = $rude->getLocalidadLugarTipo()->getId();
                $c_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt5_id)->getId();
                $m_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt4_id)->getId();
                $p_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt3_id)->getId();
                $d_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt2_id)->getId();
            }
        }else{
            // $m_id = 0;
            // $p_id = 0;
            // $d_id = 0;

            $l_id = 0;
            $c_id = 0;
            $m_id = 0;
            $p_id = 0;
            $d_id = 0;
        }

        $dpto = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findBy(array('id'=>array(1,2,3,4,5,6,7,8,9)));
        $dptoArray = array();
        foreach($dpto as $value){
            $dptoArray[$value->getId()] = $value->getDepartamento();
        }

        $pais = $em->getRepository('SieAppWebBundle:PaisTipo')->findAll();
        $paisArray = array();
        foreach($pais as $value){

            $paisArray[$value->getId()] = $value->getPais();
        }
        //dump($paisArray);die;
        if ($rude->getLocalidadLugarTipo() == null or $rude->getLocalidadLugarTipo()->getId() != 0){
            
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

            $query = $em->createQuery(
                'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
                ->setParameter('nivel', 4)
                ->setParameter('lt1', $m_id);
            $cantn = $query->getResult();
            $cantnArray = array();
            foreach ($cantn as $value) {
                $cantnArray[$value->getId()] = $value->getLugar();
            }

            $query = $em->createQuery(
                'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
                ->setParameter('nivel', 5)
                ->setParameter('lt1', $c_id);
                $locald = $query->getResult();
            $localdArray = array();
            foreach ($locald as $value) {
                $localdArray[$value->getId()] = $value->getLugar();
            }
        }else{
            $prov = array();
            $muni = array();
            $cantn = array();
            $locald = array();
        }
        $institucion = $this->session->get('ie_id');
        //$form = $this->createFormBuilder($rude)
        $form = $this->createFormBuilder()
                    ->add('id','hidden',array('data'=>$rude->getId()));
                    
                    if ($institucion == 80730796){
                        if ($rude->getLocalidadLugarTipo() !=null  and $rude->getLocalidadLugarTipo()->getId() == 0){
                            $form=$form
                            ->add('inmigrante', 'hidden', array('data'=>"SI" ))
                            ->add('ckInmigrante', CheckboxType::class, array('label'=>'Inmigrante','required' => false, 'attr' => array('onclick' => 'verInmigrante(this.value)','checked'   => 'checked')))
                            ->add('paisInmigrante', 'choice', array('data' => $paisInmigrante_id, 'label' => 'Pais', 'required' => true, 'choices' => $paisArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                            ->add('zonaInmigrante', 'text', array('data' => $trozos[2], 'required' => true, 'attr' => array('class' => 'form-control')))
                            ->add('departamentoDir', 'entity', array('label' => 'Departamento', 'required' => false, 'class' => 'SieAppWebBundle:DepartamentoTipo', 'property' => 'departamento', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarProvincias(this.value);')))
                            ->add('provinciaDir', 'choice', array('label' => 'Provincia', 'required' => false, 'choices' => $prov, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarMunicipios(this.value)')))
                            ->add('municipioDir', 'choice', array('label' => 'Municipio', 'required' => false, 'choices' => $muni, 'empty_value' => 'Seleccionar...','attr' => array('class' => 'form-control', 'onchange' => 'listarCantones(this.value)')))
                            ->add('cantonDir', 'choice', array('label' => 'Cantón', 'required' => false, 'choices' => $cantn, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarLocalidades(this.value)')))
                            ->add('localidadDir', 'choice', array('label' => 'Localidad', 'required' => false, 'choices' => $locald, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                            ->add('zona', 'text', array('required' => false, 'attr' => array('class' => 'form-control')));
                        }else{
                            $form=$form
                            ->add('inmigrante', 'hidden', array('data'=>"NO" ))
                            ->add('ckInmigrante', CheckboxType::class, array('label'=>'Inmigrante','required' => false, 'attr' => array('onclick' => 'verInmigrante(this.value)')))
                            ->add('paisInmigrante', 'choice', array('label' => 'Pais', 'required' => true, 'choices' => $paisArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                            ->add('zonaInmigrante', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
                            ->add('departamentoDir', 'choice', array('data' => $d_id - 1, 'label' => 'Departamento', 'required' => true, 'choices' => $dptoArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarProvincias(this.value);')))
                            ->add('provinciaDir', 'choice', array('data' => $p_id, 'label' => 'Provincia', 'required' => true, 'choices' => $provArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarMunicipios(this.value)')))
                            ->add('municipioDir', 'choice', array('data' => $m_id, 'label' => 'Municipio', 'required' => true, 'choices' => $muniArray, 'empty_value' => 'Seleccionar...','attr' => array('class' => 'form-control', 'onchange' => 'listarCantones(this.value)')))
                            ->add('cantonDir', 'choice', array('data' => $c_id, 'label' => 'Cantón', 'required' => true, 'choices' => $cantnArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarLocalidades(this.value)')))
                            ->add('localidadDir', 'choice', array('data' => $l_id, 'label' => 'Localidad', 'required' => true, 'choices' => $localdArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                            ->add('zona', 'text', array('data' => $rude->getZona(), 'required' => false, 'attr' => array('class' => 'form-control')));
                        }
                    }else{
                        $form=$form
                        ->add('departamentoDir', 'choice', array('data' => $d_id - 1, 'label' => 'Departamento', 'required' => true, 'choices' => $dptoArray, 'empty_value' => 'Seleccionar...','mapped'=>false))
                        ->add('provinciaDir', 'choice', array('data' => $p_id, 'label' => 'Provincia', 'required' => true, 'choices' => $provArray, 'empty_value' => 'Seleccionar...','mapped'=>false))
                        ->add('municipioDir', 'choice', array('data' => $m_id, 'label' => 'Municipio', 'required' => true, 'choices' => $muniArray, 'empty_value' => 'Seleccionar...','mapped'=>false))
                        ->add('cantonDir', 'choice', array('data' => $c_id, 'label' => 'Canton', 'required' => true, 'choices' => $cantnArray, 'empty_value' => 'Seleccionar...','mapped'=>false))
                        ->add('localidadDir', 'choice', array('data' => $l_id, 'label' => 'Localidad', 'required' => true, 'choices' => $localdArray, 'empty_value' => 'Seleccionar...','mapped'=>false))
                        // ->add('municipioLugarTipo', 'choice', array('data' => $m_id, 'label' => 'Municipio', 'required' => true, 'choices' => $muniArray, 'empty_value' => 'Seleccionar...','mapped'=>false))
                        // ->add('localidad')
                        ->add('zona','text',array('data' => $rude->getZona(), 'required' => false, 'attr' => array('class' => 'form-control')));
                    }
                    $form=$form
                    ->add('avenida','text',array('label'=>'Avenida / Calle','data'=>$rude->getAvenida(), 'required'=>false))
                    ->add('numero','text',array('label'=>'Numero','data'=>$rude->getNumero(), 'required'=>false))
                    ->add('celular','text',array('label'=>'Celular','data'=>$rude->getCelular(), 'required'=>false))
                    ->add('telefonoFijo','text',array('label'=>'Telefono','data'=>$rude->getTelefonoFijo(), 'required'=>false))
                    ->getForm();

        return $form;
    }

    public function saveFormDireccionAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            //dump($request);die;
            $form = $request->get('form');
            $institucion = $this->session->get('ie_id');
            // dump((integer)$form['idLugar']);die;

            $rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['id']);

            if(($institucion != 80730796) or  (($institucion == 80730796) and ($form['inmigrante'] == "NO"))){
                $rude->setLocalidadLugarTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find((integer)$form['localidadDir']));
                $rude->setZona($form['zona'] ? mb_strtoupper($form['zona'], 'utf-8') : '');
            }else{
                $rude->setLocalidadLugarTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(0));
                $rude->setZona(mb_strtoupper($form['paisInmigrante'].',INMIGRANTE,'.$form['zonaInmigrante'], 'utf-8'));
            }
            
            // $rude->setMunicipioLugarTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find((integer)$form['municipioLugarTipo']));
            // $rude->setLocalidad($form['localidad'] ? mb_strtoupper($form['localidad'], 'utf-8') : '');
            
            $rude->setAvenida($form['avenida'] ? mb_strtoupper($form['avenida'], 'utf-8') : '');
            $rude->setNumero($form['numero'] ? $form['numero'] : '');
            $rude->setCelular($form['celular'] ? $form['celular'] : '');
            $rude->setTelefonoFijo($form['telefonoFijo'] ? $form['telefonoFijo'] : '');

            // ACTUALIZAMOS REGISTRO FINALIZADO DEL RUDE
            if($rude->getRegistroFinalizado() < 2){
                $rude->setRegistroFinalizado(2);
            }

            $em->flush();

            $em->getConnection()->commit();

            $response = new JsonResponse();
            return $response->setData(['msg'=>true]);
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            echo "Error en el registro";
        }
        
        
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
    /**
     * formulario de discapacidad
     */
    public function createFormDiscapacidad($rude, $e){
        $em = $this->getDoctrine()->getManager();

        // DISCAPACIDAD
        // DISCAPACIDAD DEL ESTUDIANTE
        // $discapacidadEstudiante = $em->getRepository('SieAppWebBundle:RudeDiscapacidadGrado')->findOneBy(array('rude'=>$rude->getId()));
        $discapacidadEstudiante = $em->createQueryBuilder()
                                ->select('rdg')
                                ->from('SieAppWebBundle:RudeDiscapacidadGrado','rdg')
                                ->where('rdg.rude = :rude')
                                ->andWhere('rdg.discapacidadTipo != :discapacidad')
                                ->setParameter('rude', $rude)
                                ->setParameter('discapacidad', 10)
                                ->orderBy('rdg.id','desc')
                                ->setMaxResults(1)
                                ->getQuery()
                                ->getResult();
        if(count($discapacidadEstudiante)>0){
            $discapacidadEstudiante = $discapacidadEstudiante[0];
        }

        $discapacidadVisual = $em->getRepository('SieAppWebBundle:RudeDiscapacidadGrado')->findOneBy(array('rude'=>$rude->getId(), 'discapacidadTipo'=>10));

        // obtener CATALOGOS
        // $catalogos = $this->obtenerCatalogos($rude->getEstudianteInscripcion()->getInstitucioneducativaCurso()->getGestionTipo()->getId());

        $form = $this->createFormBuilder($rude)
                    ->add('id', 'hidden')
                    ->add('estudianteId', 'hidden', array('data'=>$e->getId(), 'mapped'=>false))
                    // DISCAPACIDAD
                    ->add('tieneCarnetDiscapacidad', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false,
                            'expanded'=>true
                        ))
                    ->add('carnetDiscapacidad', 'text', array('required' => false, 'mapped'=>false, 'data'=>$e->getCarnetCodepedis()))
                    ->add('discapacidad', 'entity', array(
                            'class' => 'SieAppWebBundle:DiscapacidadTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
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
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('gdt')
                                        ->where('gdt.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'grado_discapacidad_tipo'));
                            },
                            'empty_value' => 'Seleccionar...',
                            'required' => true,
                            'data'=>($discapacidadEstudiante)?$discapacidadEstudiante->getGradoDiscapacidadTipo():'',
                            'mapped'=>false
                        ))
                    // DISCAPACIDAD VISUAL
                    ->add('esDiscapacidadVisual', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false,
                            'expanded'=>true,
                            'mapped'=>false,
                            'data'=>($discapacidadVisual)?true:false,
                            'mapped'=>false
                        ))
                    ->add('carnetIbc', 'text', array('required' => false, 'mapped'=>false, 'data'=>$e->getCarnetIbc()))
                    ->add('gradoDiscapacidadVisual', 'entity', array(
                            'class' => 'SieAppWebBundle:GradoDiscapacidadTipo',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('gdt')
                                        ->where('gdt.id in (:ids)')
                                        ->setParameter('ids', [5,6]);
                            },
                            'empty_value' => 'Seleccionar...',
                            'required' => true,
                            'data'=>($discapacidadVisual)?$discapacidadVisual->getGradoDiscapacidadTipo():'',
                            'mapped'=>false
                        ))

                    ->getForm();

        return $form;
    }

    public function saveFormDiscapacidadAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');
            // dump($form);die;
            $rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['id']);
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['estudianteId']);

            // Si no tiene discapacidad lo eliminamos
            $eliminar = $em->createQueryBuilder()
                ->delete('')
                ->from('SieAppWebBundle:RudeDiscapacidadGrado','rdg')
                ->where('rdg.rude = :rudeId')
                ->setParameter('rudeId', $form['id'])
                ->getQuery()
                ->getResult();

            // DISCAPACIDADES
            $rude->setTieneCarnetDiscapacidad($form['tieneCarnetDiscapacidad']);

            if($form['tieneCarnetDiscapacidad'] == true){
                // REGISTRAMOS EL DOCUMENTO
                $estudiante->setCarnetcodepedis($form['carnetDiscapacidad']);
                // REGISTRAMOS LA DISCAPACIDAD y el grado
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_discapacidad_grado');")->execute();
                $discapacidad = new RudeDiscapacidadGrado();
                $discapacidad->setRude($rude);
                $discapacidad->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->find($form['discapacidad']));
                $discapacidad->setGradoDiscapacidadTipo($em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->find($form['gradoDiscapacidad']));
                $discapacidad->setFechaRegistro(new \DateTime('now'));
                $em->persist($discapacidad);
                $em->flush();

                // DISCAPACIDAD VISUAL
                if(isset($form['esDiscapacidadVisual']) and $form['esDiscapacidadVisual'] == true){
                    // REGISTRAMOS LA DISCAPACIDAD VISUAL
                    $discapacidad = new RudeDiscapacidadGrado();
                    $discapacidad->setRude($rude);
                    $discapacidad->setDiscapacidadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->find(10));
                    $discapacidad->setGradoDiscapacidadTipo($em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->find($form['gradoDiscapacidadVisual']));
                    $discapacidad->setFechaRegistro(new \DateTime('now'));
                    $em->persist($discapacidad);
                    $em->flush();

                    $estudiante->setCarnetIbc($form['carnetIbc']);
                    
                }else{
                    $estudiante->setCarnetIbc('');
                }

            }else{
                $estudiante->setCarnetcodepedis('');
                $estudiante->setCarnetIbc('');
            }

            // ACTUALIZAMOS REGISTRO FINALIZADO DEL RUDE
            if($rude->getRegistroFinalizado() < 3){
                $rude->setRegistroFinalizado(3);
            }

            $em->flush();

            $em->getConnection()->commit();

            $response = new JsonResponse();
            return $response->setData(['msg'=>true]);
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            echo "Error en el registro";
        }
        
        
    }

    /**
     * CREAR FORMULARIO DE DATOS SOCIOECONOMICOS
     */
    private function createFormSocioeconomico($rude, $e){

        // dump($rude);die;
        $em = $this->getDoctrine()->getManager();

        // IDIOMAS

        $idiomasHablados = $em->createQueryBuilder()
                        ->select('ri')
                        ->from('SieAppWebBundle:RudeIdioma','ri')
                        ->where('ri.rude = :rudeId')
                        ->andWhere('ri.hablaTipo = 2')
                        ->setParameter('rudeId', $rude->getId())
                        ->getQuery()
                        ->getResult();

        $idiomasArray = array();
        $cont = 1;
        foreach ($idiomasHablados as $value) {
            $idiomasArray[$cont] = $value->getIdiomaTipo()->getId();
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

        // ACTIVIDADES DEL ESTUDIANTE
        $actividadesEstudiante = $em->getRepository('SieAppWebBundle:RudeActividad')->findBy(array('rude'=>$rude));
        $arrayActividades = [];
        foreach ($actividadesEstudiante as $ae) {
            $arrayActividades[] = $ae->getActividadTipo()->getId();
        }

        // OTRA ACTIVIDAD
        $actividadOtro = $em->getRepository('SieAppWebBundle:RudeActividad')->findOneBy(array('rude'=>$rude, 'actividadTipo'=>3));

        // CENTROS DE SALUD
        $centrosEstudiante = $em->getRepository('SieAppWebBundle:RudeCentroSalud')->findBy(array('rude'=>$rude));
        // dump($centrosEstudiante);die;
        $arrayCentros = [];
        foreach ($centrosEstudiante as $ce) {
            $arrayCentros[] = $ce->getCentroSaludTipo()->getId();
        }

        // MEDIOS COMUNICACION
        $mediosComunicacionEstudiante = $em->getRepository('SieAppWebBundle:RudeMediosComunicacion')->findBy(array('rude'=>$rude));
        $arrayMediosComunicacion = [];
        foreach ($mediosComunicacionEstudiante as $mce) {
            $arrayMediosComunicacion[] = $mce->getMediosComunicacionTipo()->getId();
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

        $cea = $this->session->get('ie_id');
        if($cea == 80730796){
            $this->estado = false;
        }else{
            $this->estado = true;
        }
        // ABANDONO OTRO
        $abandonoOtro = $em->getRepository('SieAppWebBundle:RudeAbandono')->findOneBy(array('rude'=>$rude, 'abandonoTipo'=>12));
        $form = $this->createFormBuilder($rude)
                    ->add('id', 'hidden')
                    ->add('estudianteId', 'hidden', array('data'=>$e->getId(),'mapped'=>false))
                    // 4.1 IDIOMA Y PERTENENCIA
                    ->add('idioma1', 'entity', array(
                            'class' => 'SieAppWebBundle:IdiomaTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('it')
                                        ->where('it.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'idioma_tipo'))
                                        ->orderBy('it.idioma', 'ASC');
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
                                        ->orderBy('it.idioma', 'ASC');
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
                                        ->orderBy('it.idioma', 'ASC');
                            },
                            'empty_value' => 'Seleccionar...',
                            'required'=>false,
                            'data'=>$em->getReference('SieAppWebBundle:IdiomaTipo', $idioma3),
                            'mapped'=>false
                        ))
                    // NACION ORIGINARIA
                    ->add('esPertenceNacionOriginaria', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false,
                            'expanded'=>true
                        ))
                    ->add('nacionOriginariaTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:NacionOriginariaTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('no')
                                        ->where('no.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'nacion_originaria_tipo'));
                            },
                            'empty_value' => 'Seleccionar...',
                            'multiple'=>false,
                            'property'=>'nacionOriginaria',
                            'required'=>true
                        ))
                    // OCUPACION
                    ->add('tieneOcupacionTrabajo', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false,
                            'expanded'=>true
                        ))
                    ->add('actividades', 'entity', array(
                            'class' => 'SieAppWebBundle:ActividadTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('at')
                                        ->where('at.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'actividad_tipo'));
                            },
                            'multiple'=>true,
                            'property'=>'descripcionOcupacion',
                            'required'=>true,
                            'data'=>$em->getRepository('SieAppWebBundle:ActividadTipo')->findBy(array('id'=>$arrayActividades)),
                            'mapped'=>false,
                            'expanded'=>false
                        ))
                    ->add('actividadOtro', 'text', array('mapped'=>false, 'required'=>false, 'data'=> ($actividadOtro)?$actividadOtro->getActividadOtro():''))
                    //SALUD DEL ESTUDIANTE
                    ->add('seguroSalud', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false,
                            'expanded'=>true
                        ))
                    ->add('acudioCentro', 'entity', array(
                            'class' => 'SieAppWebBundle:CentroSaludTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('ct')
                                        ->where('ct.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'centro_salud_tipo'));
                            },
                            'empty_value' => 'Seleccionar...',
                            'multiple'=>false,
                            'property'=>'descripcion',
                            'required'=>false,
                            'data'=>($arrayCentros)? $em->getReference('SieAppWebBundle:CentroSaludTipo', $arrayCentros[0]):0,
                            'mapped'=>false
                        ))
                    ->add('sangreTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:SangreTipo',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('st')
                                        ->where('st.id not in (:ids)')
                                        ->setParameter('ids', [0])
                                        ->orderBy('st.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Seleccionar...',
                            'property'=>'grupoSanguineo',
                            'required'=>true,
                            'data'=> ($e->getSangreTipo())? $em->getReference('SieAppWebBundle:SangreTipo', $e->getSangreTipo()->getId()):'',
                            'mapped'=>false
                        ))
                    // MEDIOS DE COMUNICACION
                    ->add('medioComunicacion', 'entity', array(
                            'class' => 'SieAppWebBundle:MediosComunicacionTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('mct')
                                        ->where('mct.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'medios_comunicacion_tipo'));
                            },
                            'multiple'=>true,
                            'property'=>'descripcionMediosComunicacion',
                            'required'=>true,
                            'data'=>$em->getRepository('SieAppWebBundle:MediosComunicacionTipo')->findBy(array('id'=>$arrayMediosComunicacion)),
                            'mapped'=>false,
                            'expanded'=>false
                        ))
                    // MEDIO TRANSPORTE
                    ->add('medioTransporte', 'entity', array(
                            'class' => 'SieAppWebBundle:MedioTransporteTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('mtt')
                                        ->where('mtt.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'medio_transporte_tipo'));
                            },
                            'multiple'=>true,
                            'property'=>'descripcionMedioTrasnporte',
                            'required'=>$this->estado,
                            'data'=>$em->getRepository('SieAppWebBundle:MedioTransporteTipo')->findBy(array('id'=>$arrayMedioTransporte)),
                            'mapped'=>false,
                            'expanded'=>false
                        ))
                    // TIEMPO DE LLEGADA
                    ->add('tiempoLlegadaHoras', 'text', array('required' => true))
                    ->add('tiempoLlegadaMinutos', 'text', array('required' => true))
                    // MODALIDAD ESTUDIO
                    ->add('modalidadEstudioTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:ModalidadEstudioTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('met')
                                        ->where('met.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'modalidad_estudio_tipo'));
                            },
                            'empty_value' => 'Seleccionar...',
                            'multiple'=>false,
                            'property'=>'modalidadEstudio',
                            'required'=>true,
                            'data'=>($rude->getModalidadEstudioTipo())? $em->getRepository('SieAppWebBundle:ModalidadEstudioTipo')->find($rude->getModalidadEstudioTipo()->getId()):'',
                            'mapped'=>false
                        ))
                    ->add('abandono', 'entity', array(
                            'class' => 'SieAppWebBundle:AbandonoTipo',
                            'query_builder' => function (EntityRepository $e) use ($rude){
                                return $e->createQueryBuilder('at')
                                        ->where('at.id in (:ids)')
                                        ->setParameter('ids', $this->obtenerCatalogo($rude, 'abandono_tipo'))
                                        ->orderBy('at.id','asc');
                            },
                            'multiple'=>true,
                            'property'=>'descripcionAbandono',
                            'required'=>false,
                            'data'=>$em->getRepository('SieAppWebBundle:AbandonoTipo')->findBy(array('id'=>$arrayAbandono)),
                            'mapped'=>false,
                            'expanded'=>false
                        ))
                    ->add('abandonoOtro', 'text', array('mapped'=>false, 'required'=>false, 'data'=> ($abandonoOtro)?$abandonoOtro->getAbandonoOtro():''))

                    ->getForm();
        return $form;
    }

    /**
     * DATOS SOCIOECONOMICOS
     */
    public function saveFormSocioeconomicosAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');
            //dump($request);die;
            $rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['id']);
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['estudianteId']);
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
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_idioma');")->execute();
            $rudeIdioma = new RudeIdioma();
            if($form['idioma1']){
                $rudeIdioma->setRude($rude);
                $rudeIdioma->setIdiomaTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idioma1'] ? $form['idioma1'] : 0));
                $rudeIdioma->setHablaTipo($em->getRepository('SieAppWebBundle:HablaTipo')->find(2));
                $rudeIdioma->setFechaRegistro(new \DateTime('now'));
                $rudeIdioma->setFechaModificacion(new \DateTime('now'));
                $em->persist($rudeIdioma);
                $em->flush();
            }
            $rudeIdioma = new RudeIdioma();
            if($form['idioma2']){
                $rudeIdioma->setRude($rude);
                $rudeIdioma->setIdiomaTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idioma2'] ? $form['idioma2'] : 0));
                $rudeIdioma->setHablaTipo($em->getRepository('SieAppWebBundle:HablaTipo')->find(2));
                $rudeIdioma->setFechaRegistro(new \DateTime('now'));
                $rudeIdioma->setFechaModificacion(new \DateTime('now'));
                $em->persist($rudeIdioma);
                $em->flush();
            }
            $rudeIdioma = new RudeIdioma();
            if($form['idioma3']){
                $rudeIdioma->setRude($rude);
                $rudeIdioma->setIdiomaTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['idioma3'] ? $form['idioma3'] : 0));
                $rudeIdioma->setHablaTipo($em->getRepository('SieAppWebBundle:HablaTipo')->find(2));
                $rudeIdioma->setFechaRegistro(new \DateTime('now'));
                $rudeIdioma->setFechaModificacion(new \DateTime('now'));
                $em->persist($rudeIdioma);
                $em->flush();
            }

            $rude->setEsPertenceNacionOriginaria($form['esPertenceNacionOriginaria']);
            if($form['esPertenceNacionOriginaria']){
                $rude->setNacionOriginariaTipo($em->getRepository('SieAppWebBundle:NacionOriginariaTipo')->find($form['nacionOriginariaTipo']));
            }else{
                $rude->setNacionOriginariaTipo(null);
            }

            /**
             * OCUPACION
             */
            $rude->setTieneOcupacionTrabajo($form['tieneOcupacionTrabajo']);
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
            if($form['tieneOcupacionTrabajo'] == true and isset($form['actividades'])){
                $actividades = $form['actividades'];
                for ($i=0; $i < count($actividades); $i++) { 
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_actividad');")->execute();
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

            // SEGURO SALUD
            $rude->setSeguroSalud($form['seguroSalud']);
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
            if(!$form['seguroSalud'] and isset($form['acudioCentro'])){
                $acudioCentro = $form['acudioCentro'];
                //dump($acudioCentro);die;
                for ($i=0; $i < count($acudioCentro); $i++) { 
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_centro_salud');")->execute();
                    $centroEstudiante = new RudeCentroSalud();
                    $centroEstudiante->setRude($rude);
                    
                    $centroEstudiante->setCentroSaludTipo($em->getRepository('SieAppWebBundle:CentroSaludTipo')->find((int)$acudioCentro[$i]));
                    $centroEstudiante->setFechaRegistro(new \DateTime('now'));
                    $em->persist($centroEstudiante);
                    $em->flush();
                }
            }

            // REGISTRO DE TIPO DE SANGRE
            $estudiante->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find($form['sangreTipo']));

            /**
             * MEDIOS DE COMUNICACION
             */
            // ELIMINAMOS LOS MEDIOS DE COMUNICACION
            $eliminarMediosComunicacion = $em->createQueryBuilder()
                            ->delete('')
                            ->from('SieAppWebBundle:RudeMediosComunicacion','rmc')
                            ->where('rmc.rude = :rude')
                            ->setParameter('rude', $rude)
                            ->getQuery()
                            ->getResult();
            // REGISTRAMOS LOS MEDIOS DE TRANSPORTE
            $medioComunicacion = $form['medioComunicacion'];
            for ($i=0; $i < count($medioComunicacion); $i++) { 
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_medios_comunicacion');")->execute();
                $medioComunicacionEstudiante = new RudeMediosComunicacion();
                $medioComunicacionEstudiante->setRude($rude);
                $medioComunicacionEstudiante->setMediosComunicacionTipo($em->getRepository('SieAppWebBundle:MediosComunicacionTipo')->find($medioComunicacion[$i]));
                $medioComunicacionEstudiante->setFechaRegistro(new \DateTime('now'));
                $em->persist($medioComunicacionEstudiante);
                $em->flush();
            }

            /**
             * MEDIOS DE TRANSPORTE
             */
            // ELIMINAMOS LOS MEDIOS DE TRANSPORTE
            $eliminarMediosTransporte = $em->createQueryBuilder()
                            ->delete('')
                            ->from('SieAppWebBundle:RudeMedioTransporte','rmc')
                            ->where('rmc.rude = :rude')
                            ->setParameter('rude', $rude)
                            ->getQuery()
                            ->getResult();
            // REGISTRAMOS LOS MEDIOS DE TRANSPORTE
            if(array_key_exists('medioTransporte', $form)){
                $medioTransporte = $form['medioTransporte'];
                //dump($mediosTransporte);die;
                for ($i=0; $i < count($medioTransporte); $i++) { 
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_medio_transporte');")->execute();
                    $medioTransporteEstudiante = new RudeMedioTransporte();
                    $medioTransporteEstudiante->setRude($rude);
                    $medioTransporteEstudiante->setMedioTransporteTipo($em->getRepository('SieAppWebBundle:MedioTransporteTipo')->find($medioTransporte[$i]));
                    $medioTransporteEstudiante->setFechaRegistro(new \DateTime('now'));
                    $medioTransporteEstudiante->setTiempoMaximoTrayectoTipo(null);
                    $em->persist($medioTransporteEstudiante);
                    $em->flush();
                }
            }

            $rude->setTiempoLlegadaHoras($form['tiempoLlegadaHoras']);
            $rude->setTiempoLlegadaMinutos($form['tiempoLlegadaMinutos']);

            $rude->setModalidadEstudioTipo( $em->getRepository('SieAppWebBundle:ModalidadEstudioTipo')->find($form['modalidadEstudioTipo']));
            
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
            if(isset($form['abandono']) and $form['abandono'] != ""){
                $abandono = $form['abandono'];
                for ($i=0; $i < count($abandono); $i++) { 
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rude_abandono');")->execute();
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

            // ACTUALIZAMOS REGISTRO FINALIZADO DEL RUDE
            if($rude->getRegistroFinalizado() < 4){
                $rude->setRegistroFinalizado(4);
            }

            $em->flush();

            $em->getConnection()->commit();

            $response = new JsonResponse();
            return $response->setData(['msg'=>true]);
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            echo "Error en el registro";
        }
        
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
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');
            $rude = $em->getRepository('SieAppWebBundle:Rude')->find($form['id']);
            $rude->setLugarRegistroRude(mb_strtoupper($form['lugarRegistroRude'],'utf-8'));
            $rude->setFechaRegistroRude(new \DateTime($form['fechaRegistroRude']));
            
            // ACTUALIZAMOS REGISTRO FINALIZADO DEL RUDE
            if($rude->getRegistroFinalizado() < 5){
                $rude->setRegistroFinalizado(5);
            }
            $em->flush();

            $em->getConnection()->commit();

            $response = new JsonResponse();
            return $response->setData(['msg'=>'ok']);
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            echo "Error en el registro";
        }
        
    }

    public function obtenerCatalogo($rude, $tabla){
        $em = $this->getDoctrine()->getManager();
        $gestion = $rude->getEstudianteInscripcion()->getInstitucioneducativaCurso()->getGestionTipo()->getId();
        $gestion = 2019;
        /**
         * OBTENER CATALOGO
         */
        $catalogo = $em->createQueryBuilder()
                            ->select('rc')
                            ->from('SieAppWebBundle:RudeCatalogo','rc')
                            ->where('rc.gestionTipo = :gestion')
                            ->andWhere('rc.institucioneducativaTipo = 2')
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
}
