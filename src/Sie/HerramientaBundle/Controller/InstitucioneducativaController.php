<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * Institucioneducativa Controller
 */
class InstitucioneducativaController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function calidadAction(Request $request) {
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

        $request->getSession()->set('idGestion',$request->getSession()->get('currentyear'));

        return $this->redirect($this->generateUrl('herramienta_ieducativa_index'));
    }

    /**
     * index the request
     * @param Request $request
     * @return obj with the selected request 
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

        $em = $this->getDoctrine()->getManager();

        if ($request->getMethod() == 'POST') {
            $form = $request->get('form');

            $institucion = $form['sie'];
            $gestion = $form['gestion'];
            // creamos variables de sesion de la institucion educativa y gestion
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);

            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);

            if (!$institucioneducativa) {
                $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                return $this->redirect($this->generateUrl('herramienta_ieducativa_index'));
            }
        } else {
            $institucion = $request->getSession()->get('ie_id');
            $gestion = $request->getSession()->get('idGestion');
        }

        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');

        $query = $repository->createQueryBuilder('inss')
                ->select('max(inss.gestionTipo)')
                ->where('inss.institucioneducativa = :idInstitucion')
                ->setParameter('idInstitucion', $institucion)
                ->getQuery();

        $inss = $query->getResult();

        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

        $query = $repository->createQueryBuilder('ie')
                ->select('ie, ies')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
                //->innerJoin('SieAppWebBundle:DependenciaTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
                ->where('ie.id = :idInstitucion')
                ->andWhere('ies.gestionTipo in (:gestion)')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $inss)
                ->getQuery();

        $infoUe = $query->getResult();

        $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');

        $query = $repository->createQueryBuilder('jg')
                ->select('lt4.codigo AS codigo_departamento,
                        lt4.lugar AS departamento,
                        lt3.codigo AS codigo_provincia,
                        lt3.lugar AS provincia,
                        lt2.codigo AS codigo_seccion,
                        lt2.lugar AS seccion,
                        lt1.codigo AS codigo_canton,
                        lt1.lugar AS canton,
                        lt.codigo AS codigo_localidad,
                        lt.lugar AS localidad,
                        dist.id AS codigo_distrito,
                        dist.distrito,
                        orgt.orgcurricula,
                        dept.dependencia,
                        jg.id AS codigo_le,
                        inst.id,
                        inst.institucioneducativa,
                        lt.area2001,
                        estt.estadoinstitucion,
                        inss.direccion')
                ->join('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.leJuridicciongeografica = jg.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'inss', 'WITH', 'inss.institucioneducativa = inst.id')
                ->innerJoin('SieAppWebBundle:EstadoinstitucionTipo', 'estt', 'WITH', 'inst.estadoinstitucionTipo = estt.id')
                ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
                ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
                ->join('SieAppWebBundle:DependenciaTipo', 'dept', 'WITH', 'inst.dependenciaTipo = dept.id')
                ->where('inst.id = :idInstitucion')
                ->andWhere('inss.gestionTipo in (:gestion)')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $inss)
                ->getQuery();

        $ubicacionUe = $query->getSingleResult();

        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);

        // Lista de cursos institucioneducativa
        $query = $em->createQuery(
                        'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    WHERE iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND iec.nivelTipo IN (:niveles)
                    ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('niveles', array(1, 2, 3, 11, 12, 13));

        $cursos = $query->getResult();

        /*
         * Listasmos los maestros inscritos en la unidad educativa
         */
        $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));

        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');

        $query = $repository->createQueryBuilder('mins')
                ->select('per.carnet, per.paterno, per.materno, per.nombre')
                ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
                ->where('mins.institucioneducativa = :idInstitucion')
                ->andWhere('mins.gestionTipo = :gestion')
                ->andWhere('mins.cargoTipo = :cargo')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargo', '1')
                ->setMaxResults(1)
                ->getQuery();

        $director = $query->getOneOrNullResult();


        $gestionSuc = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($inss[0][1]);
        $sucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestionSuc));

        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($institucion, $gestion);
        $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->findOneById($operativo);
        $control_operativo_menus = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus')->findOneBy(array('gestionTipoId' => $gestion, 'institucioneducativa' => $institucion, 'notaTipo' => $notaTipo));
        if($control_operativo_menus) {
            if($control_operativo_menus->getEstadoMenu() == 1) {
                return $this->redirect($this->generateUrl('sie_app_web_close_modules_index', array(
                    'sie' => $institucion->getId(),
                    'gestion' => $gestion,
                    'operativo' => $operativo
                )));
            }
        }

        return $this->render($this->session->get('pathSystem') . ':Institucioneducativa:index.html.twig', array(
                    'ieducativa' => $infoUe,
                    'institucion' => $institucion,
                    'gestion' => $gestion,
                    'cursos' => $cursos,
                    'maestros' => $maestros,
                    'ubicacion' => $ubicacionUe,
                    'director' => $director,
                    //'form' => $this->editSucursalForm($request->getSession()->get('idInstitucion'), $request->getSession()->get('idGestion'), $sucursal->getId())->createView(),
        ));
    }

    private function editSucursalForm($idInstitucion, $gestion, $insSuc) {
        $em = $this->getDoctrine()->getManager();
        $sucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($insSuc);

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_info_ueducativa_update'))
                ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('idSucursal', 'hidden', array('data' => $insSuc))
                ->add('telefono1', 'text', array('label' => 'Número', 'data' => $sucursal->getTelefono1(), 'attr' => array('class' => 'form-control')))
                ->add('fax', 'text', array('label' => 'Fax', 'data' => $sucursal->getFax(), 'attr' => array('class' => 'form-control')))
                ->add('telefono2', 'text', array('label' => 'Número', 'data' => $sucursal->getTelefono2(), 'attr' => array('class' => 'form-control')))
                ->add('referenciaTelefono2', 'text', array('label' => 'Pertenece a (cargo o relación)', 'data' => $sucursal->getReferenciaTelefono2(), 'attr' => array('class' => 'form-control')))
                ->add('email', 'text', array('label' => 'Correo electrónico de la U.E.', 'data' => $sucursal->getEmail(), 'attr' => array('class' => 'form-control')))
                ->add('casilla', 'text', array('label' => 'Casilla postal de la U.E.', 'data' => $sucursal->getCasilla(), 'attr' => array('class' => 'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

        return $form;
    }

}
