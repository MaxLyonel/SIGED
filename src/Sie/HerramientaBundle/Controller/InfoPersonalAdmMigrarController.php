<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Entity\MaestroInscripcion;
use Sie\AppWebBundle\Entity\MaestroInscripcionIdioma;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoValidacionpersonal;

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoPersonalAdmMigrarController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * list of request
     *
     */
    public function indexAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        ////////////////////////////////////////////////////
        $em = $this->getDoctrine()->getManager();

        $institucion = $this->session->get('ie_id');
        $gestion = $request->getSession()->get('currentyear') - 1;

        /*
         * verificamos si tiene tuicion
         */
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $this->session->get('ie_id'));
        $query->bindValue(':rolId', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();

        if ($aTuicion[0]['get_ue_tuicion']) {
            // creamos variables de sesion de la institucion educativa y gestion
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);
        } else {
            //$this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
            //return $this->redirect($this->generateUrl('herramienta_info_personal_adm_migrar_index'));
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);
        }

        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($institucion, $request->getSession()->get('currentyear'));
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        $gestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($request->getSession()->get('currentyear'));
        $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->findOneById($operativo);
        $rolTipo = $em->getRepository('SieAppWebBundle:RolTipo')->findOneById(9);

        $consol_gest_pasada = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('gestion' => $request->getSession()->get('currentyear') - 1 , 'unidadEducativa' => $institucion, 'bim4' => '1'));
        $consol_gest_pasada2 = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('gestion' => $request->getSession()->get('currentyear') - 1 , 'unidadEducativa' => $institucion, 'bim4' => '2'));
        
        if(!($consol_gest_pasada or $consol_gest_pasada2)){
            $gestion = $request->getSession()->get('currentyear') - 1;
            $request->getSession()->set('idGestion', $gestion);
            $activar_acciones = true;
            return $this->redirect($this->generateUrl('herramienta_info_personal_adm_index'));
        }

        $validacion_personal_aux = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoValidacionpersonal')->findOneBy(array('gestionTipo' => $gestionTipo, 'institucioneducativa' => $institucioneducativa, 'notaTipo' => $notaTipo, 'rolTipo' => $rolTipo));
        
        $activar_acciones = true;
        if($validacion_personal_aux){
            $gestion = $request->getSession()->get('currentyear');
            $request->getSession()->set('idGestion', $gestion);
            $activar_acciones = false;
            return $this->redirect($this->generateUrl('herramienta_info_personal_adm_index'));
        }

        /*
         * lista de personal registrados en la unidad educativa
         */
        $query = $em->createQuery(
                        'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.rolTipo IN (:id) ORDER BY ct.cargo')
                ->setParameter('id', array(5, 9));

        $cargos = $query->getResult();
        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getId();
        }

        $query = $em->createQuery(
                        'SELECT mi, per, ft FROM SieAppWebBundle:MaestroInscripcion mi
                    INNER JOIN mi.persona per
                    INNER JOIN mi.formacionTipo ft
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    AND mi.cargoTipo IN (:cargos)
                    AND mi.esVigenteAdministrativo = :esvigente
                    ORDER BY per.paterno, per.materno, per.nombre')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $request->getSession()->get('currentyear'))
                ->setParameter('cargos', $cargosArray)
                ->setParameter('esvigente', 't');
        $personal_aux = $query->getResult();

        $personal_auxArray = array();
        if ($personal_aux) {
            
            foreach ($personal_aux as $key => $value) {
                $personal_auxArray [$value->getPersona()->getId()] = $value->getPersona()->getId();
            }

            $query = $em->createQuery(
                'SELECT mi, per, ft FROM SieAppWebBundle:MaestroInscripcion mi
                INNER JOIN mi.persona per
                INNER JOIN mi.formacionTipo ft
                WHERE mi.institucioneducativa = :idInstitucion
                AND mi.gestionTipo = :gestion
                AND mi.cargoTipo IN (:cargos)
                AND per.id NOT IN (:personas)
                AND mi.esVigenteAdministrativo = :esvigente
                ORDER BY per.paterno, per.materno, per.nombre')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargos', $cargosArray)
            ->setParameter('personas', $personal_auxArray)
            ->setParameter('esvigente', 't');
        } else {
            $query = $em->createQuery(
                'SELECT mi, per, ft FROM SieAppWebBundle:MaestroInscripcion mi
                INNER JOIN mi.persona per
                INNER JOIN mi.formacionTipo ft
                WHERE mi.institucioneducativa = :idInstitucion
                AND mi.gestionTipo = :gestion
                AND mi.cargoTipo IN (:cargos)
                AND mi.esVigenteAdministrativo = :esvigente
                ORDER BY per.paterno, per.materno, per.nombre')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargos', $cargosArray)
            ->setParameter('esvigente', 't');
        }

        $personal = $query->getResult();

        $query = $em->createQuery(
            'SELECT count(mi.id) FROM SieAppWebBundle:MaestroInscripcion mi
            WHERE mi.institucioneducativa = :idInstitucion
            AND mi.gestionTipo = :gestion
            AND mi.cargoTipo IN (:cargos)')
        ->setParameter('idInstitucion', $institucion)
        ->setParameter('gestion', $gestion)
        ->setParameter('cargos', array(1,12));
        $contador = $query->getResult();

        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');

        $query = $repository->createQueryBuilder('mi')
            ->select('p.id perId, p.carnet, p.paterno, p.materno, p.nombre, mi.id miId, mi.fechaRegistro, mi.fechaModificacion, ft.formacion')
            ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
            ->innerJoin('SieAppWebBundle:FormacionTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
            ->leftJoin('SieAppWebBundle:MaestroInscripcionIdioma', 'maii', 'WITH', 'maii.maestroInscripcion = mi.id')
            ->where('mi.institucioneducativa = :idInstitucion')
            ->andWhere('mi.gestionTipo = :gestion')
            ->andWhere('mi.cargoTipo IN (:cargos)')
            ->andWhere('maii.id IS NULL')
            ->andWhere('mi.esVigenteAdministrativo = :esvigente')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargos', $cargosArray)
            ->setParameter('esvigente', 't')
            ->distinct()
            ->orderBy('p.paterno')
            ->addOrderBy('p.materno')
            ->addOrderBy('p.nombre')
            ->getQuery(); 
        
        $personal_no_idioma = $query->getResult();

        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);

        if (!$activar_acciones) {
            return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdm:index.html.twig', array(
                    'personal' => $personal,
                    'institucion' => $institucion,
                    'gestion' => $gestion,
                    'contador' => $contador[0][1],
                    'activar_acciones' => $activar_acciones,
                    'personal_no_idioma' => $personal_no_idioma
            ));
        } else {
            return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdm:index_migrar.html.twig', array(
                    'personal' => $personal,
                    'institucion' => $institucion,
                    'gestion' => $gestion,
                    'contador' => $contador[0][1],
                    'personal_aux' => $personal_aux,
                    'gestion_aux' => $request->getSession()->get('currentyear'),
                    'activar_acciones' => $activar_acciones,
            ));
        }
    }

    public function ratificarAdmAction(Request $request) {
        $ratificarMaestro = $request->get('ratificarMaestro');

        $sie = $ratificarMaestro['sie'];
        $gestion = $ratificarMaestro['gestion'];

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($sie);
        $gestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion);

        $maestrosArray = array();

        $contador = 0;

        if($ratificarMaestro){
            foreach($ratificarMaestro as $key=>$value) {
                $contador++;
                if($contador > 2){
                    $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($key);
                    $maestro_inscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('persona' => $persona, 'institucioneducativa' => $institucioneducativa, 'gestionTipo' => $gestionTipo, 'esVigenteAdministrativo' => true));
                    $gestionTipo_aux = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($request->getSession()->get('currentyear'));
                    $maestro_inscripcion_aux = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('persona' => $persona, 'institucioneducativa' => $institucioneducativa, 'gestionTipo' => $gestionTipo_aux, 'esVigenteAdministrativo' => true));
                    if(!$maestro_inscripcion_aux){
                        $maestrosArray[$key] = $maestro_inscripcion;
                    }
                }
            }
        }

        $gestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($request->getSession()->get('currentyear'));
        
        foreach ($maestrosArray as $key => $maestro_inscripcion) {
            //Registrar maestro_inscriocion gestión actual
            //$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('maestro_inscripcion');")->execute();
            $maestro_inscripcion_aux = clone $maestro_inscripcion;
            $maestro_inscripcion_aux->setGestionTipo($gestionTipo);
            $maestro_inscripcion_aux->setFechaRegistro(new \DateTime('now'));
            $maestro_inscripcion_aux->setItem($maestro_inscripcion->getItem() ? $maestro_inscripcion->getItem() : 0);
            $maestro_inscripcion_aux->setEstudiaiomaMaterno($maestro_inscripcion->getEstudiaiomaMaterno() ? $maestro_inscripcion->getEstudiaiomaMaterno() : $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById(0));
            $maestro_inscripcion_aux->setFechaModificacion(null);
            $maestro_inscripcion_aux->setEsVigenteAdministrativo(true);
            $em->persist($maestro_inscripcion_aux);
            $em->flush();

            //Registrar maestro_inscripcion_idioma gestión actual
            $maestro_inscripcion_idioma = $em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findBy(array('maestroInscripcion' => $maestro_inscripcion));

            foreach ($maestro_inscripcion_idioma as $key => $value) {
                $maestro_inscripcion_idioma_aux = clone $value;
                $maestro_inscripcion_idioma_aux->setMaestroInscripcion($maestro_inscripcion_aux);
                $em->persist($maestro_inscripcion_idioma_aux);
                $em->flush();
            }
        }

        $em->getConnection()->commit();
        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('maestro_inscripcion');")->execute();

        $request->getSession()->set('idInstitucion', $sie);
        $request->getSession()->set('idGestion', $request->getSession()->get('currentyear'));

        $message = 'Se realizó el registro satisfactoriamente.';
        $this->addFlash('newOk', $message);
        return $this->redirect($this->generateUrl('herramienta_info_personal_adm_migrar_index'));
    }

    public function admDDJJAction(Request $request) {
        $form = $request->get('ddjjMaestro');

        $sie = $form['sie'];
        $gestion = $form['gestion'];

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie, $gestion);
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($sie);
        $gestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion);
        $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->findOneById($operativo);
        $rolTipo1 = $em->getRepository('SieAppWebBundle:RolTipo')->findOneById(5);
        $rolTipo2 = $em->getRepository('SieAppWebBundle:RolTipo')->findOneById(9);
        $cargo = 1;

        $arch = 'DECLARACION_JURADA_ADMINISTRATIVOS' . $sie . '_' . $gestion . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_maestroayadministrativo_validacion_v1.rptdesign&codue='.$sie.'&gestion='.$gestion.'&cargo='.$cargo.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        $validacion_personal_aux1 = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoValidacionpersonal')->findOneBy(array('gestionTipo' => $gestionTipo, 'institucioneducativa' => $institucioneducativa, 'notaTipo' => $notaTipo, 'rolTipo' => $rolTipo1));

        $validacion_personal_aux2 = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoValidacionpersonal')->findOneBy(array('gestionTipo' => $gestionTipo, 'institucioneducativa' => $institucioneducativa, 'notaTipo' => $notaTipo, 'rolTipo' => $rolTipo2));

        if($validacion_personal_aux1){
            $validacion_personal_aux1->setFechaModificacion(new \DateTime('now'));
            $em->persist($validacion_personal_aux1);
            $em->flush();
        } else {
            $validacion_personal = new InstitucioneducativaOperativoValidacionpersonal();
            $validacion_personal->setGestionTipo($gestionTipo);
            $validacion_personal->setInstitucioneducativa($institucioneducativa);
            $validacion_personal->setNotaTipo($notaTipo);
            $validacion_personal->setRolTipo($rolTipo1);
            $validacion_personal->setFechaRegistro(new \DateTime('now'));
            $validacion_personal->setFechaModificacion(null);
            $validacion_personal->setObs("Migración de datos gestión " . $gestion);
            $validacion_personal->setOrigen($_SERVER['HTTP_USER_AGENT']. ' - '. $_SERVER['REMOTE_ADDR']);
            $em->persist($validacion_personal);
            $em->flush();
        }

        if($validacion_personal_aux2){
            $validacion_personal_aux2->setFechaModificacion(new \DateTime('now'));
            $em->persist($validacion_personal_aux2);
            $em->flush();
        } else {
            $validacion_personal = new InstitucioneducativaOperativoValidacionpersonal();
            $validacion_personal->setGestionTipo($gestionTipo);
            $validacion_personal->setInstitucioneducativa($institucioneducativa);
            $validacion_personal->setNotaTipo($notaTipo);
            $validacion_personal->setRolTipo($rolTipo2);
            $validacion_personal->setFechaRegistro(new \DateTime('now'));
            $validacion_personal->setFechaModificacion(null);
            $validacion_personal->setObs("Validación de datos gestión " . $gestion);
            $validacion_personal->setOrigen($_SERVER['HTTP_USER_AGENT']. ' - '. $_SERVER['REMOTE_ADDR']);
            $em->persist($validacion_personal);
            $em->flush();
        }

        $em->getConnection()->commit();
        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_operativo_validacionpersonal');")->execute();
        
        $message = 'Se ha descargado la Declaración Jurada satisfactoriamente.';
        $this->addFlash('newOk', $message);
        
        return $response;

    }

    public function ratificarAdmCurrentAction(Request $request) {
        $form = $request->get('ratificarMaestroG');

        $sie = $form['sie'];
        $gestion = $form['gestion'];

        $gestion = $request->getSession()->get('currentyear');
        $request->getSession()->set('idGestion', $gestion);

        return $this->redirect($this->generateUrl('herramienta_info_personal_adm_index'));
    }
}
