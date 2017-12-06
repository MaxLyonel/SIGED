<?php

namespace Sie\GisBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    public $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function loginAction(Request $request){
        $this->session->set('pathSystem', "SieGisBundle");
        return $this->render('SieGisBundle:Login:login.html.twig',array(
            'last_username'=>$this->get('request')->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'=>$this->get('request')->getSession()->get(SecurityContext::AUTHENTICATION_ERROR)
        ));
    }

    public function indexAction()
    {
        return $this->render('SieGisBundle:Default:index.html.twig');
    }

    public function searchingAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $tipo = $request->get('tipo');
            switch ($tipo) {
                case 'sie':
                    $sie = $request->get('sie');

                    $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
                    $institucioneducativa = $repository->createQueryBuilder('ie')
                                    ->where('ie.id = :sie')
                                    ->setParameter('sie',$sie)
                                    ->getQuery()
                                    ->getResult();

                    $array = array();
                    $array = "";
                    foreach ($institucioneducativa as $ie) {
                        $array[$ie->getId()] = array(
                            'sie'=>$ie->getId(),
                            'institucioneducativa'=>$ie->getInstitucioneducativa(),
                            'departamento'=>$ie->getLeJuridicciongeografica()->getDistritoTipo()->getDepartamentoTipo()->getDepartamento()
                        );
                    }

                    break;
                case 'nombre':
                    $institucion = mb_strtoupper($request->get('institucion'),'utf-8');

                    $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
                    /*$institucioneducativa = $repository->createQueryBuilder('ie')
                                    ->select('ie')
                                    ->leftJoin('ie.leJuridicciongeografica','jg')
                                    ->leftJoin('jg.distritoTipo','dist')
                                    ->leftJoin('dist.departamentoTipo','dep')
                                    ->where('ie.institucioneducativa LIKE :texto')
                                    ->setParameter('texto','%'.$institucion.'%')
                                    ->addOrderBy('dep.id','DESC')
                                    ->getQuery()
                                    ->getResult();*/
                    $institucioneducativa = $em->createQueryBuilder()
                                            ->select('ie')
                                            ->from('SieAppWebBundle:Institucioneducativa','ie')
                                            ->innerJoin('SieAppWebBundle:JurisdiccionGeografica','jg','with','ie.leJuridicciongeografica = jg.id')
                                            ->innerJoin('SieAppWebBundle:DistritoTipo','dist','with','jg.distritoTipo = dist.id')
                                            ->innerJoin('SieAppWebBundle:DepartamentoTipo','dep','with','dist.departamentoTipo = dep.id')
                                            ->where('ie.institucioneducativa LIKE :texto')
                                            ->orderBy('dep.id','ASC')
                                            ->setParameter('texto','%'.$institucion.'%')
                                            ->getQuery()
                                            ->getResult();
                    //dump($institucioneducativa);die;
                    $array = array();
                    $array = "";
                    foreach ($institucioneducativa as $ie) {
                        $array[] = array(
                            'sie'=>$ie->getId(),
                            'institucioneducativa'=>$ie->getInstitucioneducativa(),
                            'departamento'=>$ie->getLeJuridicciongeografica()->getDistritoTipo()->getDepartamentoTipo()->getDepartamento(),
                            'zona'=>$ie->getLeJuridicciongeografica()->getZona(),
                            'direccion'=>$ie->getLeJuridicciongeografica()->getDireccion()
                        );
                    }

                    break;
                case 'edificio':
                    $edificio = $request->get('edificio');

                    $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');
                    $jurisdiccionGeografica = $repository->createQueryBuilder('jg')
                                    ->where('jg.id = :edificio')
                                    ->setParameter('edificio',$edificio)
                                    ->getQuery()
                                    ->getResult();

                    $array = array();
                    $array = "";
                    foreach ($jurisdiccionGeografica as $jg) {
                        $array[$jg->getId()] = array(
                            'edificio'=>$jg->getId(),
                            'departamento'=>$jg->getDistritoTipo()->getDepartamentoTipo()->getDepartamento(),
                            'zona'=>$jg->getZona(),
                            'direccion'=>$jg->getDireccion()
                        );
                    }
                    break;
            }

            $response = new JsonResponse();
            $response->setData($array);
            return $response;
        } catch (Exception $e) {

        }
    }

    public function existeUsuarioTuicion($edificioId){
        $em = $this->getDoctrine()->getManager();
        // Verificamos si existe el usuario
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return false;
        }else{
            $query = $em->getConnection()->prepare('SELECT get_le_tuicion(:usuarioId:: INT, :edificioId:: INT, :rolId::INT)');
            $query->bindValue(':usuarioId', $this->session->get('userId'));
            $query->bindValue(':edificioId', $edificioId);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            $tuicion = $aTuicion[0]['get_le_tuicion'];

            if($tuicion){
                // verificamos si el edificio tiene asociado ues legalmente establecidas
                $institucionesEducativas = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findBy(array('leJuridicciongeografica'=>$edificioId, 'institucioneducativaAcreditacionTipo'=>1));
                if(count($institucionesEducativas)<=0){
                    $tuicion = false;
                }
            }

            return $tuicion;
        }
    }

    public function seleccionarUnidadAction(Request $request){
        try {
            $idie = $request->get('ie');
            $em = $this->getDoctrine()->getManager();
            $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($idie);

            // Verificamos si tiene coordenadas
            if($ie->getLeJuridicciongeografica()->getCordx()){
                $coordenadas = true;
                $latitud = $ie->getLeJuridicciongeografica()->getCordx();
                $longitud = $ie->getLeJuridicciongeografica()->getCordy();
            }else{
                $coordenadas = false;
                $latitud = 'Ninguno';
                $longitud = 'Ninguno';
            }

            // Verificamos si tiene registro de lugar tipo censo 2012
            $existe = false;
            if($ie->getLeJuridicciongeografica()->getLugarTipoIdLocalidad2012()){
                $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id'=>$ie->getLeJuridicciongeografica()->getLugarTipoIdLocalidad2012(),'lugarNivel'=>11));
                if($comunidad){
                    $existe = true;
                }
            }

            // Verificamos si tiene registro de lugar tipo censo 2012
            if ($existe) {
                $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->find($comunidad->getLugarTipo());
                $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->find($municipio->getLugarTipo());
                $departamento = $em->getRepository('SieAppWebBundle:LugarTipo')->find($provincia->getLugarTipo());

                $departamento = $departamento->getLugar();
                $provincia = $provincia->getLugar();
                $municipio = $municipio->getLugar();

                $idComunidad = $comunidad->getId();
                $comunidad = $comunidad->getLugar();

            }else{
                $departamento = $ie->getLeJuridicciongeografica()->getDistritoTipo()->getDepartamentoTipo()->getDepartamento();
                $provincia = 'Ninguno';
                $municipio = 'Ninguno';
                $comunidad = 'Ninguno';

                $idComunidad = 'Ninguno';
            }

            $actualizar = $this->existeUsuarioTuicion($ie->getLeJuridicciongeografica()->getId());

            $array = array(
                'id'=>$ie->getId(),
                'institucioneducativa'=>$ie->getInstitucioneducativa(),
                'codEdificio'=>$ie->getLeJuridicciongeografica()->getId(),
                'coordenadas'=>$coordenadas,
                'latitud'=>$latitud,
                'longitud'=>$longitud,
                'departamento'=>$departamento,
                'provincia'=>$provincia,
                'municipio'=>$municipio,
                'comunidad'=>$comunidad,
                'idComunidad'=>$idComunidad,
                'zona'=>$ie->getLeJuridicciongeografica()->getZona(),
                'direccion'=>$ie->getLeJuridicciongeografica()->getDireccion(),
                'actualizar'=>$actualizar,
                'fechaModificacionLocalizacion'=>($ie->getLeJuridicciongeografica()->getFechaModificacionLocalizacion())?$ie->getLeJuridicciongeografica()->getFechaModificacionLocalizacion()->format('d-m-Y'):'',
                'fechaModificacionCoordenada'=>($ie->getLeJuridicciongeografica()->getFechaModificacionCoordenada())?$ie->getLeJuridicciongeografica()->getFechaModificacionCoordenada()->format('d-m-Y'):'',
                'validacion'=>($ie->getLeJuridicciongeografica()->getValidacionGeograficaTipo())?$ie->getLeJuridicciongeografica()->getValidacionGeograficaTipo()->getId():0,
                'validacionMsg'=>($ie->getLeJuridicciongeografica()->getValidacionGeograficaTipo())?$ie->getLeJuridicciongeografica()->getValidacionGeograficaTipo()->getDescripcion():'Sin validar'
            );

            $response = new JsonResponse();
            $response->setData($array);
            return $response;
        } catch (Exception $e) {

        }
    }

    public function seleccionarEdificioAction(Request $request){
        try {
            $edificio = $request->get('edificio');
            $em = $this->getDoctrine()->getManager();
            $edificio = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($edificio);

            // Verificamos si tiene coordenadas
            if($edificio->getCordx()){
                $coordenadas = true;
                $latitud = $edificio->getCordx();
                $longitud = $edificio->getCordy();
            }else{
                $coordenadas = false;
                $latitud = 'Ninguno';
                $longitud = 'Ninguno';
            }

            // Verificamos si tiene registro de lugar tipo censo 2012
            $existe = false;
            if($edificio->getLugarTipoIdLocalidad2012()){
                $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id'=>$edificio->getLugarTipoIdLocalidad2012(),'lugarNivel'=>11));
                if($comunidad){
                    $existe = true;
                }
            }

            if ($existe) {
                $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->find($comunidad->getLugarTipo());
                $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->find($municipio->getLugarTipo());
                $departamento = $em->getRepository('SieAppWebBundle:LugarTipo')->find($provincia->getLugarTipo());

                $departamento = $departamento->getLugar();
                $provincia = $provincia->getLugar();
                $municipio = $municipio->getLugar();

                $idComunidad = $comunidad->getId();
                $comunidad = $comunidad->getLugar();
                
            }else{
                $departamento = $edificio->getDistritoTipo()->getDepartamentoTipo()->getDepartamento();
                $provincia = 'Ninguno';
                $municipio = 'Ninguno';
                $comunidad = 'Ninguno';

                $idComunidad = 'Ninguno';
            }

            $unidadesEducativas = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findBy(array('leJuridicciongeografica'=>$edificio->getId()));
            $arrayUes = array();
            foreach ($unidadesEducativas as $ue) {
                $arrayUes[$ue->getId()] = array('id'=>$ue->getId(), 'institucioneducativa'=>$ue->getInstitucioneducativa());
            }

            $actualizar = $this->existeUsuarioTuicion($edificio->getId());

            $array = array(
                'codEdificio'=>$edificio->getId(),
                'coordenadas'=>$coordenadas,
                'latitud'=>$latitud,
                'longitud'=>$longitud,
                'departamento'=>$departamento,
                'provincia'=>$provincia,
                'municipio'=>$municipio,
                'comunidad'=>$comunidad,
                'idComunidad'=>$idComunidad,
                'zona'=>$edificio->getZona(),
                'direccion'=>$edificio->getDireccion(),
                'unidadesEducativas'=>$arrayUes,
                'actualizar'=>$actualizar,
                'fechaModificacionLocalizacion'=>($edificio->getFechaModificacionLocalizacion())?$edificio->getFechaModificacionLocalizacion()->format('d-m-Y'):'',
                'fechaModificacionCoordenada'=>($edificio->getFechaModificacionCoordenada())?$edificio->getFechaModificacionCoordenada()->format('d-m-Y'):'',
                'validacion'=>($edificio->getValidacionGeograficaTipo())?$edificio->getValidacionGeograficaTipo()->getId():0,
                'validacionMsg'=>($edificio->getValidacionGeograficaTipo())?$edificio->getValidacionGeograficaTipo()->getDescripcion():'Sin validar'
            );

            $response = new JsonResponse();
            $response->setData($array);
            return $response;
        } catch (Exception $e) {

        }
    }

    public function editgeograficaAction(Request $request){
        try {
            $codEdificio = $request->get('codEdificio');
            $em = $this->getDoctrine()->getManager();
            $jurisdiccion = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($codEdificio);

            $existe = false;
            if($jurisdiccion->getLugarTipoIdLocalidad2012()){
                $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id'=>$jurisdiccion->getLugarTipoIdLocalidad2012(),'lugarNivel'=>11));
                if($comunidad){
                    $existe = true;
                }
            }

            if($existe){

                $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->find($comunidad->getLugarTipo());
                $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->find($municipio->getLugarTipo());
                $departamento = $em->getRepository('SieAppWebBundle:LugarTipo')->find($provincia->getLugarTipo());

                // Arrays
                $departamentos = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel'=>8));
                $depArray = array();
                foreach ($departamentos as $d) {
                    if($d->getid() == $departamento->getId()){
                        $depArray[$d->getId()] = $d->getLugar();
                    }
                }
                $provincias = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$departamento->getId(),'lugarNivel'=>9));
                $provArray = array();
                foreach ($provincias as $p) {
                    $provArray[$p->getId()] = $p->getLugar();
                }
                $municipios = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$provincia->getId(),'lugarNivel'=>10));
                $muniArray = array();
                foreach ($municipios as $m) {
                    $muniArray[$m->getId()] = $m->getLugar();
                }
                $comunidades = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$municipio->getId(),'lugarNivel'=>11));
                $comuArray = array();
                foreach ($comunidades as $c) {
                    $comuArray[$c->getId()] = $c->getLugar().' ( P:'.$c->getPoblacion().' )';;
                }
                /*$localidades = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$comunidad->getId(),'lugarNivel'=>12));
                $localArray = array();
                foreach ($localidades as $l) {
                    $localArray[$l->getId()] = $l->getLugar();
                }*/
            }else{
                //$localidad = null;
                $comunidad = null;
                $municipio = null;
                $provincia = null;

                $departamento = $jurisdiccion->getDistritoTipo()->getDepartamentoTipo()->getId();
                $departamento = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('lugarNivel'=>8,'codigo'=>'0'.$departamento));
                // Arrays
                $departamentos = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel'=>8));
                $depArray = array();
                foreach ($departamentos as $d) {
                    if($d->getLugar() != 'NINGUNO'){
                        $depArray[$d->getId()] = $d->getLugar();
                    }
                }
                $provincias = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$departamento->getId(),'lugarNivel'=>9));
                $provArray = array();
                foreach ($provincias as $p) {
                    $provArray[$p->getId()] = $p->getLugar();
                }

                $muniArray = null;
                $comuArray = null;
                $localArray = null;
            }

            $form = $this->createFormBuilder()
                        ->add('codigoEdificio', 'hidden', array('data' => $jurisdiccion->getId()))
                        ->add('departamento', 'choice', array('label' => 'Departamento', 'choices' => $depArray,'data'=>($departamento)?$departamento->getId():'','attr' => array('class' => 'form-control jupper', 'onchange' => 'listarProvincias(this.value);','required' => true,'disabled'=>true)))
                        ->add('provincia', 'choice', array('label' => 'Provincia', 'choices' => $provArray, 'data'=>($provincia)?$provincia->getId():'','empty_value'=>'Seleccionar...','attr' => array('class' => 'form-control jupper', 'onchange' => 'listarMunicipios(this.value)','required' => true)))
                        ->add('municipio', 'choice', array('label' => 'Municipio', 'choices' => $muniArray,'data'=>($municipio)?$municipio->getId():'','empty_value'=>'Seleccionar...','attr' => array('class' => 'form-control jupper', 'onchange' => 'listarComunidades(this.value)','required' => true)))
                        ->add('comunidad', 'choice', array('label' => 'Comunidad', 'choices' => $comuArray,'data'=>($comunidad)?$comunidad->getId():'','empty_value'=>'Seleccionar...', 'attr' => array('class' => 'form-control jupper','required' => true)))
                        ->getForm();

            return $this->render('SieGisBundle:Default:modalEditGeografica.html.twig', array('jurisdiccion'=>$jurisdiccion, 'form'=>$form->createView()));
        } catch (Exception $e) {

        }
    }

    public function editgeoreferencialAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $codEdificio = $request->get('codEdificio');
            $jurisdiccion = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($codEdificio);
            // Creacion del poligono
            $cordsPoligono = array(
                array('lat'=>-10.945663,'lng'=>-69.570245),
                array('lat'=>-9.666271,'lng'=>-65.413040),
                array('lat'=>-13.810883,'lng'=>-60.483735),
                array('lat'=>-18.223593,'lng'=>-57.450316),
                array('lat'=>-22.235052,'lng'=>-62.632406),
                array('lat'=>-22.829719,'lng'=>-67.839376),
                array('lat'=>-17.258244,'lng'=>-69.665117)
            );
            $cordsPoligono = array();

            $latitudMunicipio = -16.76031273193217;
            $longitudMunicipio = -64.56658868750003;

            if($jurisdiccion->getCordx()){
                $coordenadas = true;
                $latitud = $jurisdiccion->getCordx();
                $longitud = $jurisdiccion->getCordy();
            }else{
                $coordenadas = false;
                $latitud = 'Ninguno';
                $longitud = 'Ninguno';

                // Obtener las coordenadas del municipio al que pertenece el edificio
                if ($jurisdiccion->getLugarTipoIdLocalidad2012()) {
                    $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->find($jurisdiccion->getLugarTipoIdLocalidad2012());
                    $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->find($comunidad->getLugarTipo());

                    $coordenadaMunicipio = $em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeograficaMunicipioCentroide')->findOneBy(array('lugarTipoMunicipio2012'=>$municipio->getId()));
                    if($coordenadaMunicipio){
                        $latitudMunicipio = $coordenadaMunicipio->getLatitudX();
                        $longitudMunicipio = $coordenadaMunicipio->getLongitudY();
                    }
                }
            }

            $array = array(
                'codEdificio'=>$jurisdiccion->getId(),
                'coordenadas'=>$coordenadas,
                'latitud'=>$latitud,
                'longitud'=>$longitud,
                'cordsPoligono'=>$cordsPoligono,
                'latitudMunicipio'=>$latitudMunicipio,
                'longitudMunicipio'=>$longitudMunicipio
            );

            $response = new JsonResponse();
            $response->setData($array);
            return $response;

        } catch (Exception $e) {

        }
    }

    public function updategeoreferencialAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $user = $this->container->get('security.context')->getToken()->getUser();
            $codEdificio = $request->get('codEdificio');
            $latitud = $request->get('latitud');
            $longitud = $request->get('longitud');

            $actualizarGeografica = $request->get('actualizarGeografica');
            $idComunidad = $request->get('idComunidad');
            //dump($idComunidad);die;

            $jurisdiccion = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($codEdificio);
            $regAnterior = clone $jurisdiccion;

            // Verificamos si se debe actualizar la ubicacion geografica
            if($actualizarGeografica == true){
                $jurisdiccion->setLugarTipoIdLocalidad2012($idComunidad);
                $jurisdiccion->setFechaModificacionLocalizacion(new \DateTime('now'));
            }

            $existe = false;
            if($jurisdiccion->getLugarTipoIdLocalidad2012()){
                $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idComunidad);
                if($comunidad){
                    $existe = true;
                }
            }

            if($existe){
                $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->find($comunidad->getLugarTipo());
                //dump($municipio);die;
                /* validamos que el punto este dentro de la comunidad y bolivia */
                $query = $em->getConnection()->prepare('SELECT sp_punto_en_poligono(:comunidadId:: VARCHAR, :latitud:: VARCHAR, :longitud::VARCHAR)');
                $query->bindValue(':comunidadId', $municipio->getId());
                $query->bindValue(':latitud', $latitud);
                $query->bindValue(':longitud', $longitud);
                $query->execute();
                $valido = $query->fetchAll();

                $valido = $valido[0]['sp_punto_en_poligono'];

                if ($valido) {
                    $jurisdiccion->setCordx($latitud);
                    $jurisdiccion->setCordy($longitud);
                    $jurisdiccion->setFechaModificacionCoordenada(new \DateTime('now'));
                    $jurisdiccion->setUsuarioId($user->getId());
                    $em->flush();

                    /**
                     * Registro de log transaccion
                     */
                    $this->get('funciones')->setLogTransaccion(
                        $jurisdiccion->getId(),
                        'jurisdiccion_geografica',
                        'U',
                        'Update Coordenada',
                        $jurisdiccion,
                        $regAnterior,
                        'GIS',
                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                    );
                    if($actualizarGeografica == true){
                        return new JsonResponse(array('status'=>200,'msg'=>'Los datos fueron actualizadas correctamente.'));
                    }else{
                        return new JsonResponse(array('status'=>200,'msg'=>'Las coordenadas fueron actualizadas correctamente.'));
                    }
                }else{
                    return new JsonResponse(array('status'=>500,'msg'=>'Las coordenadas actuales no son válidas, la ubicación debe estar próximo al municipio de '.$municipio->getLugar().'.'));
                }
            }else{
                $query = $em->getConnection()->prepare('SELECT sp_punto_en_bolivia(:latitud:: VARCHAR, :longitud::VARCHAR)');
                $query->bindValue(':latitud', $latitud);
                $query->bindValue(':longitud', $longitud);
                $query->execute();
                $valido = $query->fetchAll();

                $valido = $valido[0]['sp_punto_en_bolivia'];

                if ($valido) {
                    $jurisdiccion->setCordx($latitud);
                    $jurisdiccion->setCordy($longitud);
                    //$jurisdiccion->setValidacionGeograficaTipo($em->getRepository('SieAppWebBundle:ValidacionGeograficaTipo')->find(1));
                    $jurisdiccion->setFechaModificacionCoordenada(new \DateTime('now'));
                    $jurisdiccion->setUsuarioId($user->getId());
                    $em->flush();

                    /**
                     * Registro de log transaccion
                     */
                    $this->get('funciones')->setLogTransaccion(
                        $jurisdiccion->getId(),
                        'jurisdiccion_geografica',
                        'U',
                        'Update Coordenada',
                        $jurisdiccion,
                        $regAnterior,
                        'GIS',
                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                    );

                    return new JsonResponse(array('status'=>200,'msg'=>'Las coordenadas fueron actualizadas correctamente.'));
                }else{
                    return new JsonResponse(array('status'=>500,'msg'=>'Las coordenadas actuales no son válidas, la ubicación debe estar dentro del territorio Boliviano.'));
                }
            }
            return new JsonResponse(array('status'=>500,'msg'=>'Las coordenadas ingresadas no son válidas.'));

        } catch (Exception $e) {
            return new JsonResponse(array('status'=>500,'msg'=>'No se pudieron actualizar las coordenadas'));
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarProvinciasAction($dpto) {
        try {
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery(
                            'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                    ->setParameter('lt1', $dpto);
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
    public function listarMunicipiosAction($prov) {
        try {
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery(
                            'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarTipo = :lt1
                    ORDER BY lt.id')
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
    public function listarComunidadesAction($mun) {
        try {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQuery(
                            'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                    ->setParameter('lt1', $mun);
            $cantones = $query->getResult();

            $cantonesArray = array();
            foreach ($cantones as $c) {
                $cantonesArray[$c->getId()] = $c->getLugar().' ( P:'.$c->getPoblacion().' )';
            }

            $response = new JsonResponse();
            return $response->setData(array('listacomunidades' => $cantonesArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function imprimirReporteAction(Request $request){
        try {
            $codEdificio = $request->get('codEdificio');
            $em = $this->getDoctrine()->getManager();
            //$em->getConnection()->beginTransaction();

            $jurisdiccion = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($codEdificio);
            $jurisdiccion->setValidacionGeograficaTipo($em->getRepository('SieAppWebBundle:ValidacionGeograficaTipo')->find(1));
            $jurisdiccion->setFechaRegistro(new \DateTime('now'));
            $em->persist($jurisdiccion);
            $em->flush();

            //$em->getConnection()->commit();

            //return $this->redirectToRoute('download_reporte_gis', array('codEdificio'=>$codEdificio));
            return $this->redirect($this->generateUrl('download_reporte_gis', array('codEdificio' => $codEdificio)));

        } catch (Exception $e) {
            $em->getConnection()->rollback();
        }
    }

}
