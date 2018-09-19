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
class InfoEstudianteRudeController extends Controller {

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

        // $estudiante = array(
        //     'codigoRude' => $aInfoStudent['codigoRude'],
        //     'estudiante' => $aInfoStudent['nombre'] . ' ' . $aInfoStudent['paterno'] . ' ' . $aInfoStudent['materno'],
        //     'estadoMatricula' => $inscripcion->getEstadomatriculaTipo()->getEstadomatricula()
        // );
        $estudiante = $inscripcion->getEstudiante();

        $socioeconomico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegular')->findOneBy(array(
            'estudianteInscripcion'=>$inscripcion->getId()
        ));

        if(!is_object($socioeconomico)){

            /**
             * OBTENEMOS UN REGISTRO ANTERIOR
             */
            $socioeconomicoAnterior = $em->createQueryBuilder()
                        ->select('eisr')
                        ->from('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegular','eisr')
                        ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','eisr.estudianteInscripcion = ei.id')
                        ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                        ->where('e.id = :estudiante')
                        ->setParameter('estudiante', $estudiante->getId())
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getResult();

            if(count($socioeconomicoAnterior) == 1){

                $socioeconomicoAnterior = $socioeconomicoAnterior[0];

                $socioeconomico = clone $socioeconomicoAnterior;
                $socioeconomico->setEstudianteInscripcion($inscripcion);
                $em->persist($socioeconomico);
                $em->flush();

                $regNacion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegNacion')->findBy(array('estudianteInscripcionSocioeconomicoRegular'=>$socioeconomicoAnterior->getId()));
                foreach ($regNacion as $rn) {
                    $nacion = clone $rn;
                    $nacion->setEstudianteInscripcionSocioeconomicoRegular($socioeconomico);
                    $em->persist($nacion);
                    $em->flush();
                }
                $regInternet = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternet')->findBy(array('estudianteInscripcionSocioeconomicoRegular'=>$socioeconomicoAnterior->getId()));
                foreach ($regInternet as $ri) {
                    $internet = clone $ri;
                    $internet->setEstudianteInscripcionSocioeconomicoRegular($socioeconomico);
                    $em->persist($internet);
                    $em->flush();
                }
                $regHablaFrec = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegHablaFrec')->findBy(array('estudianteInscripcionSocioeconomicoRegular'=>$socioeconomicoAnterior->getId()));
                foreach ($regHablaFrec as $rhf) {
                    $habla = clone $rhf;
                    $habla->setEstudianteInscripcionSocioeconomicoRegular($socioeconomico);
                    $em->persist($habla);
                    $em->flush();
                }

                $apoderadoInscripcion = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion'=>$socioeconomicoAnterior->getEstudianteInscripcion()->getId()));

                if(count($apoderadoInscripcion)>0){

                    foreach ($apoderadoInscripcion as $ai) {
                        $apoderado = clone $ai;
                        $apoderado->setEstudianteInscripcion($inscripcion);
                        $em->persist($apoderado);
                        $em->flush();

                        $apoderadoInscripcionDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findOneBy(array('apoderadoInscripcion'=>$ai->getId()));
                        if($apoderadoInscripcionDatos){
                            $apoderadoDatos = clone $apoderadoInscripcionDatos;
                            $apoderadoDatos->setApoderadoInscripcion($apoderado);
                            $em->persist($apoderadoDatos);
                            $em->flush();
                        }
                    }
                }


            }else{
                $jg = $em->createQueryBuilder()
                            ->select('jg')
                            ->from('SieAppWebBundle:JurisdiccionGeografica','jg')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','ie.leJuridicciongeografica = jg.id')
                            ->where('ie.id = :sie')
                            ->setParameter('sie', $ieducativaId)
                            ->getQuery()
                            ->getResult();

                $direccion = 'ZONA '.$jg[0]->getZona().' '.$jg[0]->getDireccion();

                $socioeconomico = new EstudianteInscripcionSocioeconomicoRegular();
                $socioeconomico->setEstudianteInscripcion($inscripcion);
                $socioeconomico->setRegistroFinalizado(0);
                $socioeconomico->setFecha(new \DateTime('now'));
                $socioeconomico->setFechaRegistro(new \DateTime('now'));
                $socioeconomico->setLugar($direccion);
                $em->persist($socioeconomico);
                $em->flush();
            }
            
        }

        // OBTENER APODERADOS
        // PADRE O TUTOR
        $padreTutor = $this->obtenerApoderado($idInscripcion,array(1,3,4,5,6,7,8,9,10,11,12,13));
        $formApoderado = $this->createFormApoderado($socioeconomico, $idInscripcion, $padreTutor[0]);
        // MADRE
        $madre = $this->obtenerApoderado($idInscripcion,array(2));
        $formMadre = $this->createFormApoderado($socioeconomico, $idInscripcion, $madre[0]);
        
        $ayudaComplemento = ["Complementito","Contenido del complemento, no se refiere al lugar de expedición del documento."];

        return $this->render('SieHerramientaBundle:InfoEstudianteRude:index.html.twig', [
            'ie'=>$this->getData($iec->getInstitucioneducativa()),
            'socioeconomico'=>$socioeconomico,
            'form1'=>$this->createForm1($socioeconomico)->createView(),
            'form2'=>$this->createForm2($socioeconomico, $estudiante)->createView(),
            'estudiante'=>$estudiante,
            'inscripcion'=>$inscripcion,
            'form4'=>$this->createForm4($socioeconomico)->createView(),
            'form5'=>$this->createForm5($socioeconomico)->createView(),
            'formApoderado'=>$formApoderado->createView(),
            'formMadre'=>$formMadre->createView(),
            'padreTutor'=>$padreTutor,
            'madre'=>$madre,
            'ayudaComplemento'=>$ayudaComplemento,
            'form7'=>$this->createForm7($socioeconomico)->createView()
        ]);
    }

    /**
     * PASO 1: DATOS DE LA UE
     */
    private function getData($ie){
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        $institucion = $repository->createQueryBuilder('i')
                        ->select('i.id ieducativaId, i.institucioneducativa ieducativa, d.id distritoId, d.distrito distrito, dp.id departamentoId, dp.departamento departamento, de.dependencia dependencia, jg.cordx cordx, jg.cordy cordy')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'isuc', 'WITH', 'isuc.institucioneducativa = i.id')
                        ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'jg', 'WITH', 'i.leJuridicciongeografica = jg.id')
                        ->innerJoin('SieAppWebBundle:DistritoTipo', 'd', 'WITH', 'jg.distritoTipo = d.id')
                        ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dp', 'WITH', 'd.departamentoTipo = dp.id')
                        ->innerJoin('SieAppWebBundle:DependenciaTipo', 'de', 'WITH', 'i.dependenciaTipo = de.id')
                        ->where('i.id = :ieducativa')
                        ->setParameter('ieducativa', $ie->getId())
                        ->getQuery()
                        ->getResult();

        $institucion = $institucion[0];
        return $institucion;
    }

    /**
     * PASO 1
     */
    private function createForm1($socioeconomico){
        $form = $this->createFormBuilder($socioeconomico)
                    ->add('id', 'hidden')
                    ->getForm();

        return $form;
    }
    public function saveForm1Action(Request $request){

        $form = $request->get('form');

        $em = $this->getDoctrine()->getManager();
        $socioeconomico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegular')->find($form['id']);
        if($socioeconomico->getRegistroFinalizado() < 2){
            $socioeconomico->setRegistroFinalizado(2);
        }
        $em->persist($socioeconomico);
        $em->flush();

        $response = new JsonResponse();
        return $response->setData([
            'paso'=>2
        ]);
    }

    /**
     * PASO 2: DATOS DE LA ESTUDIANTE
     */

    private function createForm2($socioeconomico, $e){
        $em = $this->getDoctrine()->getManager();
        $pais = $e->getPaisTipo()->getId();
        $departamento = $e->getLugarNacTipo()->getId();
        $provincia = $e->getLugarProvNacTipo()->getId();

        $departamentos = array();
        $provincias = array();

        if($pais == 1){
            $condition = array('lugarNivel' => 1, 'paisTipoId' => $pais);
            $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy($condition);
            foreach ($dep as $d) {
                $departamentos[$d->getId()] = $d->getLugar();
            }

            $prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $departamento));
            foreach ($prov as $p) {
                $provincias[$p->getid()] = $p->getlugar();
            }
        }

        $form = $this->createFormBuilder()
                    // ->setAction($this->generateUrl('info_estudiante_rude_save_form2'))
                    ->add('socioeconomicoId', 'hidden', array('data' => $socioeconomico->getId(),'mapped'=>false))
                    ->add('estudianteId', 'hidden', array('data' => $e->getId()))
                    ->add('paterno', 'text', array('required' => false, 'data' => $e->getPaterno()))
                    ->add('materno', 'text', array('required' => false, 'data' => $e->getMaterno()))
                    ->add('nombre', 'text', array('required' => false, 'data'=> $e->getNombre()))
                    ->add('pais', 'entity', array(
                            'class' => 'SieAppWebBundle:PaisTipo',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('p')
                                        ->where('p.id != 0')
                                        ->orderBy('p.pais', 'ASC')
                                ;
                            },
                            'empty_value' => 'Selecionar...',
                            'required'=>true,
                            'property' => 'pais',
                            'data'=>$e->getPaisTipo()
                        ))
                    ->add('departamento', 'choice', array('required'=>false,'empty_value'=>'Seleccionar', 'choices'=>$departamentos, 'data'=>$departamento))
                    ->add('provincia', 'choice', array('required'=>false,'empty_value'=>'Seleccionar', 'choices'=>$provincias, 'data'=>$provincia))
                    ->add('localidad', 'text', array('required'=>false,'required'=>true, 'data'=>$e->getLocalidadNac()))

                    ->add('rude', 'text', array('required' => false, 'data'=>$e->getCodigoRude()))
                    ->add('carnet', 'text', array('required' => false, 'data'=>$e->getCarnetIdentidad()))
                    ->add('complemento', 'text', array('required' => false, 'data'=>$e->getComplemento()))
                    ->add('fechaNacimiento', 'text', array('required' => false, 'data'=>$e->getFechaNacimiento()->format('d-m-Y')))
                    ->add('sexo', 'entity', array(
                            'class' => 'SieAppWebBundle:GeneroTipo',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('gt')
                                        ->where('gt.id != 3');
                            },
                            'empty_value' => 'Selecionar...',
                            'required' => true,
                            'data'=>$e->getGeneroTipo()
                        ))
                    ->add('oficialia', 'text', array('required' => false, 'data'=>$e->getOficialia()))
                    ->add('libro', 'text', array('required' => false, 'data'=>$e->getLibro()))
                    ->add('partida', 'text', array('required' => false, 'data'=>$e->getPartida()))
                    ->add('folio', 'text', array('required' => false, 'data'=>$e->getFolio()))

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
     * GUARDAR DATOS DEL FORMULARIO 2
     */

    public function saveForm2Action(Request $request){

        $form = $request->get('form');

        // dump($request);die;

        $em = $this->getDoctrine()->getManager();
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['estudianteId']);
        // dump($estudiante);die;
        /**
         * REGISTRO DE CARNET DE IDENTIDAD
         */
        $carnet = $form['carnet'];
        if(isset($carnet) and $carnet != ''){
            $complemento = $form['complemento'];
            $estudiante->setCarnetIdentidad($carnet);
            $estudiante->setComplemento( mb_strtoupper($complemento, 'utf-8'));
        }

        $estudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->findOneBy(array('id' => $form['pais'])));
        $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['departamento'] ? $form['departamento'] : null)));
        $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['provincia'] ? $form['provincia'] : null)));
        $estudiante->setLocalidadNac(mb_strtoupper($form['localidad'],'utf-8'));
        $estudiante->setOficialia($form['oficialia']);
        $estudiante->setLibro($form['libro']);
        $estudiante->setPartida($form['partida']);
        $estudiante->setFolio($form['folio']);
        $estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneBy(array('id' => $form['sexo'])));
        $em->persist($estudiante);
        $em->flush();

        // Socioeconomico
        $socioeconomico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegular')->find($form['socioeconomicoId']);
        if($socioeconomico->getRegistroFinalizado() < 3){
            $socioeconomico->setRegistroFinalizado(3);
        }
        $em->persist($socioeconomico);
        $em->flush();

        $response = new JsonResponse();
        return $response->setData(['msg'=>true]);
    }

    /**
     * PASO 4
     */
    public function createForm4($socioeconomico){
        // DIRECCION
        $em = $this->getDoctrine()->getManager();

        if($socioeconomico->getSeccionivLocalidadTipo() != null){

                $lt5_id = $socioeconomico->getSeccionivLocalidadTipo()->getLugarTipo();
                $lt4_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt5_id)->getLugarTipo();
                $lt3_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt4_id)->getLugarTipo();

                $m_id = $socioeconomico->getSeccionivLocalidadTipo()->getId();
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

        $form = $this->createFormBuilder($socioeconomico)
                    ->add('id','hidden')
                    ->add('departamentoDir', 'choice', array('data' => $d_id - 1, 'label' => 'Departamento', 'required' => true, 'choices' => $dptoArray, 'empty_value' => 'Seleccionar...','mapped'=>false))
                    ->add('provinciaDir', 'choice', array('data' => $p_id, 'label' => 'Provincia', 'required' => true, 'choices' => $provArray, 'empty_value' => 'Seleccionar...','mapped'=>false))
                    ->add('municipioDir', 'choice', array('data' => $m_id, 'label' => 'Municipio', 'required' => true, 'choices' => $muniArray, 'empty_value' => 'Seleccionar...','mapped'=>false))
                    ->add('seccionivDescLocalidad')
                    ->add('seccionivZona')
                    ->add('seccionivAvenida')
                    ->add('seccionivNumero')
                    ->add('seccionivTelefonocelular')
                    ->getForm();

        return $form;
    }

    /**
     * PASO 5
     */
    private function createForm5($socioeconomico){

        // dump($socioeconomico);die;
        $em = $this->getDoctrine()->getManager();

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

        // NACIONES ORIGINARIAS
        $naciones = $em->getRepository('SieAppWebBundle:NacionOriginariaTipo')->findAll();
        $arrayNaciones = [];
        foreach ($naciones as $nacion) {
            $arrayNaciones[$nacion->getId()] = $nacion->getNacionOriginaria();
        }

        // OBTENER NACIONES ORIGINARIAS DEL ESTUDIANTE
        $arrayNaciones = [];
        if(is_object($socioeconomico)){
            $naciones = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegNacion')->findBy(array(
                'estudianteInscripcionSocioeconomicoRegular'=>$socioeconomico->getId()
            ));
            foreach ($naciones as $n) {
                $arrayNaciones[] = $n->getNacionOriginariaTipo()->getId();
            }
        }

        // MEDIOS DE ACCESO A INTERNET DEL ESTUDIANTE
        $arrayAccesos = [];
        if(is_object($socioeconomico)){
            $accesoInternet = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternet')->findBy(array(
                'estudianteInscripcionSocioeconomicoRegular'=>$socioeconomico->getId()
            ));
            foreach ($accesoInternet as $ai) {
                $arrayAccesos[] = $ai->getEstudianteInscripcionSocioeconomicoRegInternetTipo()->getId();
            }
        }

        // Clasificador de discapacidades
        $especialArea = $em->getRepository('SieAppWebBundle:DiscapacidadTipo')->findBy(array('id'=>array(2,3,4,5,6,7,8,9,10)));
        $especialAreaArray = array();
        foreach ($especialArea as $ea) {
            $especialAreaArray[$ea->getId()] = $ea->getOrigendiscapacidad();
        }
        

        $form = $this->createFormBuilder($socioeconomico)
                    ->add('id', 'hidden')
                    ->add('seccionvHablaNinezTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:IdiomaTipo',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('it')
                                        ->where('it.id not in (:ids)')
                                        ->setParameter('ids', [0,97,98])
                                        ->orderBy('it.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Selecionar...',
                            'required'=>true
                        ))
                    ->add('idioma1', 'entity', array(
                            'class' => 'SieAppWebBundle:IdiomaTipo',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('it')
                                        ->where('it.id not in (:ids)')
                                        ->setParameter('ids', [0,97,98])
                                        ->orderBy('it.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Selecionar...',
                            'required'=>true,
                            'data'=>$em->getReference('SieAppWebBundle:IdiomaTipo', $idioma1),
                            'mapped'=>false
                        ))
                    ->add('idioma2', 'entity', array(
                            'class' => 'SieAppWebBundle:IdiomaTipo',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('it')
                                        ->where('it.id not in (:ids)')
                                        ->setParameter('ids', [0,97,98])
                                        ->orderBy('it.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Selecionar...',
                            'required'=>false,
                            'data'=>$em->getReference('SieAppWebBundle:IdiomaTipo', $idioma2),
                            'mapped'=>false
                        ))
                    ->add('idioma3', 'entity', array(
                            'class' => 'SieAppWebBundle:IdiomaTipo',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('it')
                                        ->where('it.id not in (:ids)')
                                        ->setParameter('ids', [0,97,98])
                                        ->orderBy('it.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Selecionar...',
                            'required'=>false,
                            'data'=>$em->getReference('SieAppWebBundle:IdiomaTipo', $idioma3),
                            'mapped'=>false
                        ))
                    ->add('nacionOriginaria', 'entity', array(
                            'class' => 'SieAppWebBundle:NacionOriginariaTipo',
                            'empty_value' => 'Selecionar...',
                            'multiple'=>false,
                            'property'=>'nacionOriginaria',
                            'required'=>false,
                            'data'=>$em->getRepository('SieAppWebBundle:NacionOriginariaTipo')->findBy(array('id'=>$arrayNaciones)),
                            'mapped'=>false
                        ))
                    ->add('seccionvEstudianteEscentroSalud', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'data'=>$socioeconomico->getSeccionvEstudianteEscentroSalud(),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false
                        ))
                    ->add('seccionvCantCentrosaludTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegCantCentrosaludTipo',
                            'property'=>'cantCentrosalud',
                            'empty_value' => 'Selecionar...',
                            'required'=>true
                        ))
                    ->add('seccionvEstudianteEsdiscapacidadSensorialComunicacion', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'data'=>$socioeconomico->getSeccionvEstudianteEsdiscapacidadSensorialComunicacion(),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false
                        ))
                    ->add('seccionvEstudianteEsdiscapacidadMotriz', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'data'=>$socioeconomico->getSeccionvEstudianteEsdiscapacidadMotriz(),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false
                        ))
                    ->add('seccionvEstudianteEsdiscapacidadMental', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'data'=>$socioeconomico->getSeccionvEstudianteEsdiscapacidadMental(),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false
                        ))
                    ->add('seccionvEstudianteDiscapacidadOtro','choice', array(
                            'choices'=>$especialAreaArray,
                            'data'=>$socioeconomico->getSeccionvEstudianteDiscapacidadOtro(),
                            'multiple'=>false,
                            'required'=>false,
                            'empty_value'=>''
                        ))
                    ->add('seccionvDiscapacidadTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegDiscapacidadTipo',
                            'property'=>'discapacitadTipo',
                            'empty_value' => 'Selecionar...',
                            'required'=>true
                        ))
                    ->add('seccionvAguaprovieneTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegAguaprovieneTipo',
                            'property'=>'guaproviene',
                            'empty_value' => 'Selecionar...',
                            'required'=>true
                        ))
                    ->add('seccionvEstudianteEsEnergiaelectrica', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'data'=>$socioeconomico->getSeccionvEstudianteEsEnergiaelectrica(),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false
                        ))
                    ->add('seccionvDesagueTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegDesagueTipo',
                            'property'=>'desague',
                            'empty_value' => 'Selecionar...',
                            'required'=>true
                        ))
                    ->add('seccionvActividadTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegActividadTipo',
                            'property'=>'actividad',
                            'empty_value' => 'Selecionar...',
                            'required'=>true
                        ))
                    ->add('seccionvEstudianteCuantosdiastrabajo')
                    ->add('seccionvEstudianteEspagoTrabajorealizado', 'choice', array(
                            'choices'=>array(true=>'Si', false=>'No'),
                            'data'=>$socioeconomico->getSeccionvEstudianteEspagoTrabajorealizado(),
                            'required'=>true,
                            'multiple'=>false,
                            'empty_value'=>false
                        ))

                    // ->add('accesoInternet', 'choice', array('mapped'=>false))
                    ->add('accesoInternet', 'entity', array(
                            'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternetTipo',
                            'empty_value' => 'Selecionar...',
                            'multiple'=>true,
                            'property'=>'accesointernetTipo',
                            'required'=>true,
                            'data'=>$em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternetTipo')->findBy(array('id'=>$arrayAccesos)),
                            'mapped'=>false
                        ))
                    
                    ->add('seccionvFrecInternetTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegFrecInternetTipo',
                            'property'=>'internet',
                            'empty_value' => 'Selecionar...',
                            'required'=>true
                        ))
                    ->add('seccionvLlegaTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegLlegaTipo',
                            'property'=>'llega',
                            'empty_value' => 'Selecionar...',
                            'required'=>true
                        ))
                    ->add('seccionvTiempotransTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegTiempotransTipo',
                            'property'=>'tiempoTransporte',
                            'empty_value' => 'Selecionar...',
                            'required'=>true
                        ))

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

    public function saveForm4Action(Request $request){
        $form = $request->get('form');

        $em = $this->getDoctrine()->getManager();
        $socioeconomico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegular')->find($form['id']);
        $socioeconomico->setSeccionivLocalidadTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['municipioDir']));
        $socioeconomico->setSeccionivDescLocalidad($form['seccionivDescLocalidad'] ? mb_strtoupper($form['seccionivDescLocalidad'], 'utf-8') : '');
        
        
        $socioeconomico->setSeccionivZona($form['seccionivZona'] ? mb_strtoupper($form['seccionivZona'], 'utf-8') : '');
        $socioeconomico->setSeccionivAvenida($form['seccionivAvenida'] ? mb_strtoupper($form['seccionivAvenida'], 'utf-8') : '');
        $socioeconomico->setSeccionivNumero($form['seccionivNumero'] ? $form['seccionivNumero'] : 0);
        $socioeconomico->setSeccionivTelefonocelular($form['seccionivTelefonocelular'] ? $form['seccionivTelefonocelular'] : 0);

        // Actualizamos el estado de registro, en que paso esta el usuario
        if($socioeconomico->getRegistroFinalizado() < 3){
            $socioeconomico->setRegistroFinalizado(3);
        }

        $em->persist($socioeconomico);
        $em->flush();

        $response = new JsonResponse();
        return $response->setData(['msg'=>true]);
    }

    /**
     * [saveForm5Action TAB 5 - ASPECTOS SOCIALES]
     */
    public function saveForm5Action(Request $request){
        $form = $request->get('form');

        $em = $this->getDoctrine()->getManager();
        $socioeconomico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegular')->find($form['id']);

        $socioeconomico->setSeccionvHablaNinezTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->find($form['seccionvHablaNinezTipo']));
        $socioeconomico->setSeccionvEstudianteEscentroSalud($form['seccionvEstudianteEscentroSalud']);
        $socioeconomico->setSeccionvCantCentrosaludTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegCantCentrosaludTipo')->find($form['seccionvCantCentrosaludTipo']));
        $socioeconomico->setSeccionvEstudianteEsdiscapacidadSensorialComunicacion($form['seccionvEstudianteEsdiscapacidadSensorialComunicacion']);
        $socioeconomico->setSeccionvEstudianteEsdiscapacidadMotriz($form['seccionvEstudianteEsdiscapacidadMotriz']);
        $socioeconomico->setSeccionvEstudianteEsdiscapacidadMental($form['seccionvEstudianteEsdiscapacidadMental']);
        $socioeconomico->setSeccionvEstudianteDiscapacidadOtro(mb_strtoupper($form['seccionvEstudianteDiscapacidadOtro'],'utf-8'));
        if(isset($form['seccionvDiscapacidadTipo'])){
            $socioeconomico->setSeccionvDiscapacidadTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegDiscapacidadTipo')->find($form['seccionvDiscapacidadTipo']));
        }else{
            $socioeconomico->setSeccionvDiscapacidadTipo(null);
        }
        $socioeconomico->setSeccionvAguaProvieneTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegAguaprovieneTipo')->find($form['seccionvAguaprovieneTipo']));
        $socioeconomico->setSeccionvEstudianteEsEnergiaelectrica($form['seccionvEstudianteEsEnergiaelectrica']);
        $socioeconomico->setSeccionvDesagueTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegDesagueTipo')->find($form['seccionvDesagueTipo']));
        $socioeconomico->setSeccionvActividadTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegActividadTipo')->find($form['seccionvActividadTipo']));
        if($form['seccionvEstudianteCuantosdiastrabajo'] == ""){ $dias = 0;}else{ $dias = $form['seccionvEstudianteCuantosdiastrabajo'];}
        $socioeconomico->setSeccionvEstudianteCuantosdiastrabajo($dias);
        $socioeconomico->setSeccionvEstudianteEspagoTrabajorealizado($form['seccionvEstudianteEspagoTrabajorealizado']);
        $socioeconomico->setSeccionvFrecInternetTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegFrecInternetTipo')->find($form['seccionvFrecInternetTipo']));
        $socioeconomico->setSeccionvLlegaTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegLlegaTipo')->find($form['seccionvLlegaTipo']));
        $socioeconomico->setSeccionvTiempotransTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegTiempotransTipo')->find($form['seccionvTiempotransTipo']));
        // $socioeconomico->setLugar(mb_strtoupper($form['lugarRegistro'],'utf-8'));
        // $socioeconomico->setFecha(new \DateTime($form['fechaRegistro']));
        //$socioeconomico->setFechaRegistro(new \DateTime($form['fechaRegistro']));
        $socioeconomico->setFechaModificacion(new \DateTime('now'));
        // $socioeconomico->setRegistroFinalizado(1); // para verificar el estado del registro
        
        // Actualizamos el estado de registro, en que paso esta el usuario
        if($socioeconomico->getRegistroFinalizado() < 4){
            $socioeconomico->setRegistroFinalizado(4);
        }

        $em->persist($socioeconomico);
        $em->flush();

        /*Registro de idiomas del estudiante*/
        /*eliminar idiomas*/
        $socioinscripcionhablaDelete = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegHablaFrec')->findBy(array('estudianteInscripcionSocioeconomicoRegular' => $socioeconomico));
        
        foreach ($socioinscripcionhablaDelete as $value) {
            $em->remove($value);
        }
        $em->flush();
        // REgistrar idiomas
        
        $socioinscripcionhabla = new EstudianteInscripcionSocioeconomicoRegHablaFrec();
        if($form['idioma1']){
            $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegular($socioeconomico);
            $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegHablaFrecTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['idioma1'] ? $form['idioma1'] : 0));
            $socioinscripcionhabla->setFechaRegistro(new \DateTime('now'));
            $socioinscripcionhabla->setFechaModificacion(new \DateTime('now'));
            $em->persist($socioinscripcionhabla);
            $em->flush();
        }
        $socioinscripcionhabla = new EstudianteInscripcionSocioeconomicoRegHablaFrec();
        if($form['idioma2']){
            $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegular($socioeconomico);
            $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegHablaFrecTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['idioma2'] ? $form['idioma2'] : 0));
            $socioinscripcionhabla->setFechaRegistro(new \DateTime('now'));
            $socioinscripcionhabla->setFechaModificacion(new \DateTime('now'));
            $em->persist($socioinscripcionhabla);
            $em->flush();
        }
        $socioinscripcionhabla = new EstudianteInscripcionSocioeconomicoRegHablaFrec();
        if($form['idioma3']){
            $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegular($socioeconomico);
            $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoRegHablaFrecTipo($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById($form['idioma3'] ? $form['idioma3'] : 0));
            $socioinscripcionhabla->setFechaRegistro(new \DateTime('now'));
            $socioinscripcionhabla->setFechaModificacion(new \DateTime('now'));
            $em->persist($socioinscripcionhabla);
            $em->flush();
        }

        /* Registro de nacion originaria */
        // Eliminar naciones originarias
        $nacionoriginariaDelete = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegNacion')->findBy(array('estudianteInscripcionSocioeconomicoRegular' => $socioeconomico));
        
        foreach ($nacionoriginariaDelete as $value) {
            $em->remove($value);
        }
        $em->flush();
        // Registrar naciones originarias

        if($form['nacionOriginaria']){
            $arrayNacion = $form['nacionOriginaria'];
            for ($i=0; $i < count($form['nacionOriginaria']) ; $i++) { 
                $newNacion = new EstudianteInscripcionSocioeconomicoRegNacion();
                $newNacion->setNacionOriginariaTipo($em->getRepository('SieAppWebBundle:NacionOriginariaTipo')->find($arrayNacion[$i]));
                $newNacion->setEstudianteInscripcionSocioeconomicoRegular($socioeconomico);
                $newNacion->setFechaRegistro(new \DateTime('now'));
                $newNacion->setFechaModificacion(new \DateTime('now'));
                $em->persist($newNacion);
                $em->flush();
            }
        }

        /* Registro de accesos a internet */
        // Eliminacion de accesos a internet
        $accesoInternetDelete = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternet')->findBy(array('estudianteInscripcionSocioeconomicoRegular' => $socioeconomico));
        
        foreach ($accesoInternetDelete as $value) {
            $em->remove($value);
        }
        $em->flush();
        // Registro de accesos a internet

        if($form['accesoInternet']){
            $accesoInternet = $form['accesoInternet'];
            for ($i=0; $i < count($accesoInternet) ; $i++) { 
                $newAccesoInternet = new EstudianteInscripcionSocioeconomicoRegInternet();
                $newAccesoInternet->setEstudianteInscripcionSocioeconomicoRegInternetTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternetTipo')->find($accesoInternet[$i]));
                $newAccesoInternet->setEstudianteInscripcionSocioeconomicoRegular($socioeconomico);
                $newAccesoInternet->setFechaRegistro(new \DateTime('now'));
                $newAccesoInternet->setFechaModificacion(new \DateTime('now'));
                $em->persist($newAccesoInternet);
                $em->flush();
            }
        }

        $response = new JsonResponse();
        return $response->setData(['msg'=>true]);
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
                'corregirFecha'=>false,
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
        // dump($apoderado);die;

        return $apoderado;
    }

    public function createFormApoderado($socioeconomico, $idInscripcion, $datos){

        // $idInscripcion = $inscripcion->getId();

        // $padreTutor = $this->obtenerApoderado($idInscripcion,array(1,3,4,5,6,7,8,9,10,11,12,13))[0];

        // dump($datos['tipoApoderado']);die;
        $tipoApoderado = $datos['tipoApoderado'];

        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder($datos)
                    ->add('idInscripcion', 'hidden', array('data' => $idInscripcion,'mapped'=>false))
                    ->add('socioeconomicoId', 'hidden', array('data' => $socioeconomico->getId(),'mapped'=>false))
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
                    ->add('fechaNacimiento', 'text', array('required' => true))
                    ->add('telefono', 'text', array('required' => false))
                    ->add('segipId', 'hidden', array('required' => true))
                    ->add('genero', 'entity', array(
                            'class' => 'SieAppWebBundle:GeneroTipo',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('gt')
                                        ->where('gt.id not in (:ids)')
                                        ->setParameter('ids', [3])
                                        ->orderBy('gt.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Selecionar...',
                            'required'=>true,
                            'data'=>($datos['genero'] != null)?$em->getReference('SieAppWebBundle:GeneroTipo', $datos['genero']):''
                        ))
                    ->add('correo', 'text', array('required' => false))
                    ->add('idDatos', 'hidden', array('required' => true))
                    ->add('idiomaMaterno', 'entity', array(
                            'class' => 'SieAppWebBundle:IdiomaMaterno',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('im')
                                        ->where('im.id not in (:ids)')
                                        ->setParameter('ids', [0])
                                        ->orderBy('im.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Selecionar...',
                            'required'=>true,
                            'data'=>($datos['idiomaMaterno'] != null)?$em->getReference('SieAppWebBundle:IdiomaMaterno', $datos['idiomaMaterno']):''
                        ))
                    ->add('ocupacion', 'entity', array(
                            'class' => 'SieAppWebBundle:ApoderadoOcupacionTipo',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('ot')
                                        ->where('ot.esVigente = :vigente')
                                        ->setParameter('vigente', true)
                                        ->orderBy('ot.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Selecionar...',
                            'required'=>true,
                            'property'=>'ocupacion',
                            'data'=>($datos['ocupacion'] != null)?$em->getReference('SieAppWebBundle:ApoderadoOcupacionTipo', ($datos['ocupacion'])?$datos['ocupacion']:10035):''
                        ))
                    ->add('obs', 'text', array('required' => true))
                    ->add('instruccionTipo', 'entity', array(
                            'class' => 'SieAppWebBundle:InstruccionTipo',
                            'query_builder' => function (EntityRepository $e) {
                                return $e->createQueryBuilder('it')
                                        ->where('it.id not in (:ids)')
                                        ->setParameter('ids', [0,1])
                                        ->orderBy('it.id', 'ASC')
                                ;
                            },
                            'empty_value' => 'Selecionar...',
                            'required'=>true,
                            'data'=>($datos['instruccionTipo'] != null)?$em->getReference('SieAppWebBundle:InstruccionTipo', $datos['instruccionTipo']):''
                        ))

                    ->getForm();

        return $form;
    }

    /**
     * FUNCIONES PARA REGISTRO DE TUTORES
     */
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

            $parametros = array(
                'complemento'=>$complemento,
                'primer_apellido'=>$paterno,
                'segundo_apellido'=>$materno,
                'nombre'=>$nombre,
                'fecha_nacimiento'=>$fechaNacimiento
            );

            // $respuesta = $this->get('sie_app_web.segip')->verificarPersona($carnet, $complemento, $paterno, $materno, $nombre, $fechaNacimiento, 'prod', 'academico');
            $persona = $this->get('sie_app_web.segip')->buscarPersonaPorCarnet($carnet, $parametros, 'dev', 'academico');

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
            // dump($persona);die;

            if($persona['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson'] !== "null"){
                // dump($persona);die;
                // $persona = $this->get('sie_app_web.segip')->buscarPersonaPorCarnet($carnet, $parametros, 'dev', 'academico');
                // $persona = 
                $persona = json_decode($persona['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson'], true);

                // dump($persona['Nombres']);die;

                $data['status'] = 200;
                $data['persona'] = array(
                    'carnet'=> $persona['NumeroDocumento'],
                    'complemento'=> $persona['Complemento'],
                    'paterno'=> $persona['PrimerApellido'],
                    'materno'=> $persona['SegundoApellido'],
                    'nombre'=> $persona['Nombres'],
                    'fecha_nacimiento'=> $persona['FechaNacimiento']
                );
            }else{
                $data['status'] = 404;
            }

            // dump($data);die;

            $response = new JsonResponse();
            $response->setData($data);
            return $response;
        } catch (Exception $e) {
            
        }
    }

    public function saveFormApoderadoAction(Request $request){
        /*
         //////////////////////////////////////////////////////////////////////////
         /////////////////// Registro de apoderado TUTOR O PADRE  /////////////////
         //////////////////////////////////////////////////////////////////////////
        */
        $form = $request->get('form');

        $idApoderadoInscripcion = $form['id'];
        $idPersona = $form['idPersona'];
        $idApoderadoDatos = $form['idDatos'];

        $em = $this->getDoctrine()->getManager();

        $tipo = $request->get('tipo');
        if($tipo == 'apoderado'){
            $parentesco = array(1,3,4,5,6,7,8,9,10,11,12,13,0);
            $tiene = $request->get('t_tieneTutor');
        }
        if($tipo == 'madre'){
            $parentesco = array(2);
            $tiene = $request->get('m_tieneMadre');

            if($request->get('actualizar') == 'true'){
                $socioeconomico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegular')->find($form['socioeconomicoId']);
                if($socioeconomico->getRegistroFinalizado() < 5){
                    $socioeconomico->setRegistroFinalizado(5);
                }
                $em->persist($socioeconomico);
                $em->flush();
            }
        }

        if($tiene == 1){

            // Verificamos si la persona es nueva
            if($form['idPersona'] == 'nuevo'){

                $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                    'carnet'=>$form['carnet'],
                    'complemento'=>$form['complemento'],
                    'paterno'=>$form['paterno'],
                    'materno'=>$form['materno'],
                    'nombre'=>$form['nombre']
                ));

                // VERIFICAMOS SI LA PERSONA EXISTE
                if($persona){
                    // SI EXISTE LA PERSONA SOLO ACTUALIZAMOS SU FECHA DE NACIMIENTO
                    $persona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
                    $em->flush();

                    $idPersona = $persona->getId();

                }else{

                    // VERIFICAMOS SI EL CARNET YA ESTA OCUPADO
                    $personaAnterior = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                        'carnet'=>$form['carnet'],
                        'complemento'=>$form['complemento']
                    ));

                    if($personaAnterior){
                        // SI EXISTE LA PERSONA PERO SUS DATOS NO SON IGUALES
                        // ACTUALIZAMOS EL NUMERO DE CARNET CON EL CARACTER ESPECIAL
                        $personaAnterior->setCarnet($persona->getCarnet().'±');
                        $em->flush();
                    }

                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('persona');")->execute();
                    $nuevaPersona = new Persona();
                    $nuevaPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($form['idiomaMaterno']));
                    $nuevaPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['genero']));
                    $nuevaPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find(7));
                    $nuevaPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find(0));
                    $nuevaPersona->setCarnet($form['carnet']);
                    $nuevaPersona->setComplemento($form['complemento']);
                    $nuevaPersona->setRda(0);
                    $nuevaPersona->setPaterno(mb_strtoupper($form['paterno'],'utf-8'));
                    $nuevaPersona->setMaterno(mb_strtoupper($form['materno'],'utf-8'));
                    $nuevaPersona->setNombre(mb_strtoupper($form['nombre'],'utf-8'));
                    $nuevaPersona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
                    $nuevaPersona->setSegipId(20);

                    $em->persist($nuevaPersona);
                    $em->flush();

                    $idPersona = $nuevaPersona->getId();
                }
                
            }else{

                // Modificamos los datos de la persona
                $actualizarPersona = $em->getRepository('SieAppWebBundle:Persona')->find($form['idPersona']);
                if($actualizarPersona){
                    // Actualizmos los datos de la persona
                    $actualizarPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['genero']));
                    $actualizarPersona->setCorreo($form['correo']);
                    $em->flush();
                }

                $idPersona = $actualizarPersona->getId();
            }

            // Verficamos si el registro de apoderado es nuevo
            if($form['id'] == 'nuevo'){
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('apoderado_inscripcion');")->execute();
                $nuevoApoderado = new ApoderadoInscripcion();
                $nuevoApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['apoderadoTipo']));
                $nuevoApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
                $nuevoApoderado->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['idInscripcion']));
                $em->persist($nuevoApoderado);
                $em->flush();

                $idApoderadoInscripcion = $nuevoApoderado->getId();
            }else{

                $actualizarApoderado = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($form['id']);
                if($actualizarApoderado){
                    $actualizarApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['apoderadoTipo']));
                    $actualizarApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($idPersona));
                    $em->flush();
                }
                $idApoderadoInscripcion = $actualizarApoderado->getId();
            }

            // Verificamos si el registro de datos de apoderado es nuevo
            if($form['idDatos'] == 'nuevo'){
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('apoderado_inscripcion_datos');")->execute();
                $nuevoApoderadoDatos = new ApoderadoInscripcionDatos();
                $nuevoApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($form['idiomaMaterno']));
                $nuevoApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($form['instruccionTipo']));
                $nuevoApoderadoDatos->setApoderadoInscripcion($em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($idApoderadoInscripcion));
                $nuevoApoderadoDatos->setTelefono($form['telefono']);
                $nuevoApoderadoDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($form['ocupacion']));
                $nuevoApoderadoDatos->setObs(mb_strtoupper($form['obs'],'utf-8'));
                $em->persist($nuevoApoderadoDatos);
                $em->flush();

                $idApoderadoDatos = $nuevoApoderadoDatos->getId();
            }else{
                $actualizarApoderadoDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->find($form['idDatos']);
                if($actualizarApoderadoDatos){
                    $actualizarApoderadoDatos->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find($form['idiomaMaterno']));
                    $actualizarApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($form['instruccionTipo']));
                    $actualizarApoderadoDatos->setTelefono($form['telefono']);
                    $actualizarApoderadoDatos->setOcupacionTipo($em->getRepository('SieAppWebBundle:ApoderadoOcupacionTipo')->find($form['ocupacion']));
                    $actualizarApoderadoDatos->setObs(mb_strtoupper($form['obs'],'utf-8'));
                    $em->flush();

                    $idApoderadoDatos = $actualizarApoderadoDatos->getId();
                }
            }
        }else{
            
            $apod = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion'=>$form['idInscripcion'],'apoderadoTipo'=>$parentesco));
            

            foreach ($apod as $a) {
                $apoderadoDatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findOneBy(array('apoderadoInscripcion'=>$a->getId()));
                if($apoderadoDatos){
                    $em->remove($apoderadoDatos);
                    $em->flush();
                }
                $em->remove($a);
                $em->flush();
            }

            $idApoderadoInscripcion = 'nuevo';
            $idPersona = 'nuevo';
            $idApoderadoDatos = 'nuevo';
        }



        $response = new JsonResponse();
        return $response->setData([
            'id'=>$idApoderadoInscripcion,
            'idPersona'=>$idPersona,
            'idDatos'=>$idApoderadoDatos
        ]);
    }

    public function createForm7($socioeconomico){
        if($socioeconomico->getFecha()){
            $fecha = $socioeconomico->getFecha()->format('d-m-Y');
        }else{
            $fecha = '';
        }
        $form = $this->createFormBuilder($socioeconomico)
                    ->add('id', 'hidden')
                    ->add('lugar', 'text', array('required' => true))
                    ->add('fecha', 'text', array('required' => false, 'data'=>$fecha))             
                    ->getForm();

        return $form;
    }

    public function saveForm7Action(Request $request){
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $socioeconomico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegular')->find($form['id']);
        $socioeconomico->setLugar(mb_strtoupper($form['lugar'],'utf-8'));
        $socioeconomico->setFecha(new \DateTime($form['fecha']));
        if($socioeconomico->getRegistroFinalizado() < 6){
            $socioeconomico->setRegistroFinalizado(6);
        }
        $em->persist($socioeconomico);
        $em->flush();

        $response = new JsonResponse();
        return $response->setData(['msg'=>'ok']);
    }
}
