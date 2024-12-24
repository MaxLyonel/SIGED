<?php

namespace Sie\EspecialBundle\Controller;

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
class InfoCentroController extends Controller {

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
                $this->get('session')->getFlashBag()->add('noSearch', 'El código ingresado no es válido');
                return $this->redirect($this->generateUrl('herramienta_especial_info_centro_index'));
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
                ->leftJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
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
                        jg.direccion,
                        jg.zona')
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

        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');

        $query = $repository->createQueryBuilder('mins')
                ->select('per.carnet, per.paterno, per.materno, per.nombre')
                ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
                ->where('mins.institucioneducativa = :idInstitucion')
                ->andWhere('mins.gestionTipo = :gestion')
                ->andWhere('mins.cargoTipo in (:cargo)')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargo', array(1,12))
                ->setMaxResults(1)
                ->getQuery();

        $director = $query->getOneOrNullResult();

        $gestionSuc = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($inss[0][1]);
        $sucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestionSuc));

        /*$operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($institucion, $gestion);
        $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->findOneById($operativo);
        $control_operativo_menus = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus')->findOneBy(array('gestionTipoId' => $gestion, 'institucioneducativa' => $institucion, 'notaTipo' => $notaTipo));
        if($control_operativo_menus) {
            if($control_operativo_menus->getEstadoMenu() == 1) {
                return $this->redirect($this->generateUrl('sie_app_web_close_modules_index', array(
                    'sie' => $institucion->getId(),
                    'gestion' => $gestion
                )));
            }
        }*/

        return $this->render($this->session->get('pathSystem') . ':Institucioneducativa:index.html.twig', array(
                    'ieducativa' => $infoUe,
                    'institucion' => $institucion,
                    'gestion' => $gestion,
                    //'cursos' => $cursos,
                    //'maestros' => $maestros,
                    'ubicacion' => $ubicacionUe,
                    'director' => $director,
                    'form' => $this->editSucursalForm($request->getSession()->get('idInstitucion'), $request->getSession()->get('idGestion'), $sucursal->getId())->createView(),
        ));
    }

    public function editSucursalForm($idInstitucion, $gestion, $insSuc) {
        $em = $this->getDoctrine()->getManager();
        $sucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($insSuc);

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_especial_info_ueducativa_update'))
                ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('idSucursal', 'hidden', array('data' => $insSuc))
                ->add('telefono1', 'text', array('label' => 'Teléfono 1', 'data' => $sucursal->getTelefono1(), 'attr' => array('class' => 'form-control')))
                ->add('fax', 'text', array('label' => 'Fax', 'required' => false, 'data' => $sucursal->getFax(), 'attr' => array('class' => 'form-control')))
                ->add('telefono2', 'text', array('label' => 'Teléfono 2', 'required' => false, 'data' => $sucursal->getTelefono2(), 'attr' => array('class' => 'form-control')))
                ->add('referenciaTelefono2', 'text', array('label' => 'Pertenece a (cargo o relación)', 'data' => $sucursal->getReferenciaTelefono2(), 'attr' => array('class' => 'form-control')))
                ->add('email', 'text', array('label' => 'Correo electrónico del (CEE)', 'required' => false, 'data' => $sucursal->getEmail(), 'attr' => array('class' => 'form-control')))
                ->add('casilla', 'text', array('label' => 'Casilla postal del (CEE)', 'required' => false, 'data' => $sucursal->getCasilla(), 'attr' => array('class' => 'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-facebook')))
                ->getForm();

        return $form;
    }

    public function updateAction(Request $request) {
        
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
        $em->getConnection()->beginTransaction();
        try {

            $form = $request->get('form');
            
            $institucion = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($form['idSucursal']);

            $institucion->setTelefono1($form['telefono1']);
            $institucion->setTelefono2($form['telefono2']);
            $institucion->setFax($form['fax']);
            $institucion->setCasilla($form['casilla']);
            $institucion->setReferenciaTelefono2(mb_strtoupper($form['referenciaTelefono2']), 'utf-8');
            $institucion->setEmail(mb_strtolower($form['email']), 'utf-8');

            $em->persist($institucion);
            $em->flush();
            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('updateOk', 'Datos modificados correctamente');
            return $this->redirect($this->generateUrl('herramienta_especial_info_centro_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function buscarCentroAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();

        return $this->render('SieEspecialBundle:Institucioneducativa:search.html.twig', array(
            'form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()
        ));
    }

    /*
     * Formulario de busqueda de institucion educativa
     */
    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1, $gestionactual - 2 => $gestionactual - 2, $gestionactual - 3 => $gestionactual - 3);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('info_especial_index'))
                ->add('sie', 'text', array('required' => true, 'attr' => array('autocomplete' => 'off', 'maxlength' => 8)))
                //->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * Buscar CEE para habilitar gestión
     */
    public function habilitarGestionSearchAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();

        return $this->render('SieEspecialBundle:Institucioneducativa:habilitar_search.html.twig', array(
            'form' => $this->formHabilitarSearch($request->getSession()->get('currentyear'))->createView()
        ));
    }

    /*
     * Formulario de búsqueda de institucion educativa
     */
    private function formHabilitarSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1, $gestionactual - 2 => $gestionactual - 2, $gestionactual - 3 => $gestionactual - 3, $gestionactual - 4 => $gestionactual - 4, $gestionactual - 5 => $gestionactual - 5);

        $form = $this->createFormBuilder()
                //->setAction($this->generateUrl('info_especial_index'))
                ->add('sie', 'text', array('required' => true, 'attr' => array('autocomplete' => 'off', 'maxlength' => 8)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'button', array('label' => 'Habilitar', 'attr' => array('class' => 'btn btn-facebook', 'onclick' => 'habilitarGestion()')))
                ->getForm();
        return $form;
    }

    /**
     * Buscar CEE para habilitar gestión
     */  
    public function gestionHabAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $form = $request->get('form');

        $institucion = $form['sie'];
        $gestion = $form['gestion'];

        $objCentro = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $institucion, 'institucioneducativaTipo' => 4));

        if($objCentro){
            // verificamos si tiene tuicion
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $institucion);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            if ($aTuicion[0]['get_ue_tuicion']) {
                //actualizamos valores en registro_consolidacion
                $em->getConnection()->beginTransaction();
                try{
                    $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa' => $institucion, 'gestion' => $gestion));
                    $registroConsol->setFecha(new \DateTime("now"));
                    $registroConsol->setBim1(0);
                    $registroConsol->setBim2(0);
                    $registroConsol->setBim3(0);
                    $registroConsol->setBim4(0);
                    $em->persist($registroConsol);
                    $em->flush();
                    $em->getConnection()->commit();

                    $resultado = "Se habilitó la gestión ".$gestion." para el Centro ".$institucion;
                } catch (Exception $e) {
                    $em->getConnection()->rollback();
                    $resultado = "No se pudo habilitar la gestión ".$gestion." para el Centro ".$institucion.", intente nuevamente";
                }
            } else {
                $resultado = "No tiene tuición sobre la unidad educativa";
            }
            
        } else {
            $resultado = "El código ingresado(".$institucion." no es válido o no corresponde a un Centro de Educación Especial";
        }

        return $this->render('SieEspecialBundle:Institucioneducativa:result_habilitar.html.twig', array(
            'resultado' => $resultado
        ));
    }

    public function consolidacionGestionAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $gestionactual = $this->session->get('currentyear');
        $roluser = $this->session->get('roluser');
        $roluserlugarid = $this->session->get('roluserlugarid');
        $bundle = $this->session->get('pathSystem');
    
        switch ($bundle) {
            case 'SieRegularBundle':
            case 'SieHerramientaBundle':
                $instipoid = 1;
                $mingestion = 2014;
                $title = 'Reporte de consolidación de operativos por gestión y Unidad Educativa';
                $label = 'Cantidad de Unidades Educativas que reportaron información';
                $label_distrito = 'Cantidad de Unidades Educativas que reportaron información en el distrito';
                break;
    
            case 'SieEspecialBundle':
                $instipoid = 4;
                $mingestion = 2013;
                $title = 'Reporte de cierre de operativos por gestión y Centro de Educación Especial';
                $label = 'Cantidad de Centros que reportaron matrícula';
                $label_distrito = 'Cantidad de Centros que reportaron matrícula en el distrito';
                break;
            
            default:
                $instipoid = 1;
                $mingestion = 2014;
                $title = 'Reporte de consolidación de operativos por gestión y Unidad Educativa';
                $label = 'Cantidad de Unidades Educativas que reportaron información';
                $label_distrito = 'Cantidad de Unidades Educativas que reportaron información en el distrito';
                break;
        }
        
        $consol = $this->get('sie_app_web.funciones')->reporteConsol($gestionactual, $roluser, $roluserlugarid, $instipoid);
    
        switch ($roluser) {
            case 20:
                $ues = $this->get('sie_app_web.funciones')->estadisticaConsolNal($gestionactual, $instipoid);
                break;

            case 8:
                $ues = $this->get('sie_app_web.funciones')->estadisticaConsolNal($gestionactual, $instipoid);
                break;
    
            case 7:
                $ues = $this->get('sie_app_web.funciones')->estadisticaConsolDptal($gestionactual, $roluserlugarid, $instipoid);
                break;
    
            case 10:
                $ues = $this->get('sie_app_web.funciones')->estadisticaConsolDtal($gestionactual, $roluserlugarid, $instipoid);
                break;
    
            default:
                $ues = null;
                break;
        }
    
        $gestiones = $em->getRepository('SieAppWebBundle:GestionTipo')->findBy(array(), array('id' => 'DESC'));
    
        $gestionesArray = array();
        
        foreach ($gestiones as $value) {
            if ($value->getId() >= $mingestion) {
                $gestionesArray[$value->getId()] = $value->getGestion();
            }
        }
        
        return $this->render($this->session->get('pathSystem') . ':Institucioneducativa:index_gestion.html.twig', array(
            'consol' => $consol,
            'gestiones' => $gestionesArray,
            'gestionactual' => $gestionactual,
            'title' => $title,
            'label' => $label,
            'label_distrito' => $label_distrito,
            'ues' => $ues,
            'instipoid' => $instipoid
        ));
    }

    public function listaSinNotasGestionAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $gestionactual = $this->session->get('currentyear');
        $roluser = $this->session->get('roluser');
        $roluserlugarid = $this->session->get('roluserlugarid');
        $bundle = $this->session->get('pathSystem');
        $instipoid = 4;
        $mingestion = 2013;
        $title = 'Detalle de calificaciones pendientes de Educación Especial';
        $label = 'Cantidad de Centros que reportaron matrícula';
        $label_distrito = 'Cantidad de Centros que reportaron matrícula en el distrito';
        $lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($roluserlugarid);
     
        switch ($roluser) {
            case '7':
                $where = "lt4.codigo = '".$lugar->getCodigo()."'";
                break;

            case '8':
                $where = '1 = 1';
                break;

            case '10':
                $where = "dt.id = '".$lugar->getCodigo()."'";
                break;

            case '20':
                $where = '1 = 1';
                break;
                
            default:
                $where = '1 = 0';
                break;
        }
       
        $query = $em->getConnection()->prepare("
        SELECT
        lt4.lugar AS departamento,
        inst.id as codigo_sie,
        inst.institucioneducativa,
         a.area_especial, count(ei.id) as total
        FROM registro_consolidacion rc
        INNER JOIN institucioneducativa inst ON rc.unidad_educativa = inst.id
        INNER JOIN jurisdiccion_geografica jg on jg.id = inst.le_juridicciongeografica_id
        inner join institucioneducativa_curso c  on c.institucioneducativa_id =inst.id
        inner join institucioneducativa_curso_especial ce on  c.id=ce.institucioneducativa_curso_id 
        inner join especial_area_tipo a on  a.id=ce.especial_area_tipo_id
        inner join especial_programa_tipo t on t.id=ce.especial_programa_tipo_id 
        inner join especial_servicio_tipo s on s.id=ce.especial_servicio_tipo_id 
        inner join nivel_tipo n on c.nivel_tipo_id=n.id
        inner join especial_modalidad_tipo m on  ce.especial_modalidad_tipo_id = m.id
        inner join estudiante_inscripcion ei on c.id=ei.institucioneducativa_curso_id
        inner join estudiante e on e.id=ei.estudiante_id
        inner join estudiante_inscripcion_especial eie on ei.id = eie.estudiante_inscripcion_id
        LEFT JOIN lugar_tipo lt ON lt.id = jg.lugar_tipo_id_localidad
        LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
        LEFT JOIN lugar_tipo lt2 ON lt2.id = lt1.lugar_tipo_id
        LEFT JOIN lugar_tipo lt3 ON lt3.id = lt2.lugar_tipo_id
        LEFT JOIN lugar_tipo lt4 ON lt4.id = lt3.lugar_tipo_id
        WHERE ".$where." AND rc.gestion = 2024 AND c.gestion_tipo_id = 2024  AND c.nivel_tipo_id<>405 AND
        rc.institucioneducativa_tipo_id = 4 AND inst.estadoinstitucion_tipo_id = 10
        and ei.estadomatricula_tipo_id not in (6,10)
        and t.id in (99,22,43,41,19,46,39,44,61,62,63,28,37,38,50,51,52,53,54,55,56,57,58,59,33,31,25,26,7,8,47,48)
        and m.id<3
        and ei.fecha_inscripcion between '2024-01-01' and '2024-12-31' 
       and (select max (nc.nota_tipo_id) from estudiante_nota_cualitativa nc where nc.estudiante_inscripcion_id=ei.id) is null
              and
             ((select  MAX  (nc.nota_tipo_id) from estudiante_nota nc, estudiante_asignatura ea where ea.estudiante_inscripcion_id=ei.id and ea.id=nc.estudiante_asignatura_id ) <8
                  )
             group by        lt4.lugar,  inst.id, inst.institucioneducativa,a.area_especial
        ORDER BY
        lt4.lugar, total desc
    ");
    
    $query->execute();
    $consol = $query->fetchAll();

    
    $query1 = $em->getConnection()->prepare("
    SELECT
    lt4.lugar AS departamento,
    inst.id as codigo_sie,
    inst.institucioneducativa,
     a.area_especial, count(ei.id) as total
    FROM registro_consolidacion rc
    INNER JOIN institucioneducativa inst ON rc.unidad_educativa = inst.id
    INNER JOIN jurisdiccion_geografica jg on jg.id = inst.le_juridicciongeografica_id
    inner join institucioneducativa_curso c  on c.institucioneducativa_id =inst.id
    inner join institucioneducativa_curso_especial ce on  c.id=ce.institucioneducativa_curso_id 
    inner join especial_area_tipo a on  a.id=ce.especial_area_tipo_id
    inner join especial_programa_tipo t on t.id=ce.especial_programa_tipo_id 
    inner join especial_servicio_tipo s on s.id=ce.especial_servicio_tipo_id 
    inner join nivel_tipo n on c.nivel_tipo_id=n.id
    inner join especial_modalidad_tipo m on  ce.especial_modalidad_tipo_id = m.id
    inner join estudiante_inscripcion ei on c.id=ei.institucioneducativa_curso_id
    inner join estudiante e on e.id=ei.estudiante_id
    inner join estudiante_inscripcion_especial eie on ei.id = eie.estudiante_inscripcion_id
    LEFT JOIN lugar_tipo lt ON lt.id = jg.lugar_tipo_id_localidad
    LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
    LEFT JOIN lugar_tipo lt2 ON lt2.id = lt1.lugar_tipo_id
    LEFT JOIN lugar_tipo lt3 ON lt3.id = lt2.lugar_tipo_id
    LEFT JOIN lugar_tipo lt4 ON lt4.id = lt3.lugar_tipo_id
    WHERE ".$where." AND rc.gestion = 2024 AND c.gestion_tipo_id = 2024  AND c.nivel_tipo_id<>405 AND
    rc.institucioneducativa_tipo_id = 4 AND inst.estadoinstitucion_tipo_id = 10
    and ei.estadomatricula_tipo_id not in (6,10)
 	and s.id in (38,37,36,35,20,8,9)
    and m.id<3
    and ei.fecha_inscripcion between '2024-01-01' and '2024-07-30' 
    and ei.estadomatricula_tipo_id<>78
    and (select max (nc.nota_tipo_id) from estudiante_nota_cualitativa nc where nc.estudiante_inscripcion_id=ei.id) <54
    group by        lt4.lugar,  inst.id, inst.institucioneducativa,a.area_especial
    ORDER BY
    lt4.lugar, total desc
");
$query1->execute();
$consol_servicios = $query1->fetchAll();

$query11 = $em->getConnection()->prepare("
SELECT
lt4.lugar AS departamento,
inst.id as codigo_sie,
inst.institucioneducativa,
 a.area_especial, count(ei.id) as total
FROM registro_consolidacion rc
INNER JOIN institucioneducativa inst ON rc.unidad_educativa = inst.id
INNER JOIN jurisdiccion_geografica jg on jg.id = inst.le_juridicciongeografica_id
inner join institucioneducativa_curso c  on c.institucioneducativa_id =inst.id
inner join institucioneducativa_curso_especial ce on  c.id=ce.institucioneducativa_curso_id 
inner join especial_area_tipo a on  a.id=ce.especial_area_tipo_id
inner join especial_programa_tipo t on t.id=ce.especial_programa_tipo_id 
inner join especial_servicio_tipo s on s.id=ce.especial_servicio_tipo_id 
inner join nivel_tipo n on c.nivel_tipo_id=n.id
inner join especial_modalidad_tipo m on  ce.especial_modalidad_tipo_id = m.id
inner join estudiante_inscripcion ei on c.id=ei.institucioneducativa_curso_id
inner join estudiante e on e.id=ei.estudiante_id
inner join estudiante_inscripcion_especial eie on ei.id = eie.estudiante_inscripcion_id
LEFT JOIN lugar_tipo lt ON lt.id = jg.lugar_tipo_id_localidad
LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
LEFT JOIN lugar_tipo lt2 ON lt2.id = lt1.lugar_tipo_id
LEFT JOIN lugar_tipo lt3 ON lt3.id = lt2.lugar_tipo_id
LEFT JOIN lugar_tipo lt4 ON lt4.id = lt3.lugar_tipo_id
WHERE ".$where." AND rc.gestion = 2024 AND c.gestion_tipo_id = 2024  AND c.nivel_tipo_id<>405 AND
rc.institucioneducativa_tipo_id = 4 AND inst.estadoinstitucion_tipo_id = 10
and ei.estadomatricula_tipo_id not in (6,10)
 and s.id in (38,37,36,35,20,8,9)
and m.id<3
and ei.estadomatricula_tipo_id<>78
and ei.fecha_inscripcion between '2024-08-01' and '2024-12-30' 
and (select max (nc.nota_tipo_id) from estudiante_nota_cualitativa nc where nc.estudiante_inscripcion_id=ei.id) is null
group by        lt4.lugar,  inst.id, inst.institucioneducativa,a.area_especial
ORDER BY
lt4.lugar, total desc
");
$query11->execute();
$consol_servicios2 = $query11->fetchAll();


$query2 = $em->getConnection()->prepare("
SELECT
lt4.lugar AS departamento,
inst.id as codigo_sie,
inst.institucioneducativa,
 a.area_especial, count(ei.id) as total
FROM registro_consolidacion rc
INNER JOIN institucioneducativa inst ON rc.unidad_educativa = inst.id
INNER JOIN jurisdiccion_geografica jg on jg.id = inst.le_juridicciongeografica_id
inner join institucioneducativa_curso c  on c.institucioneducativa_id =inst.id
inner join institucioneducativa_curso_especial ce on  c.id=ce.institucioneducativa_curso_id 
inner join especial_area_tipo a on  a.id=ce.especial_area_tipo_id
inner join especial_programa_tipo t on t.id=ce.especial_programa_tipo_id 
inner join especial_servicio_tipo s on s.id=ce.especial_servicio_tipo_id 
inner join nivel_tipo n on c.nivel_tipo_id=n.id
inner join especial_modalidad_tipo m on  ce.especial_modalidad_tipo_id = m.id
inner join estudiante_inscripcion ei on c.id=ei.institucioneducativa_curso_id
inner join estudiante e on e.id=ei.estudiante_id
inner join estudiante_inscripcion_especial eie on ei.id = eie.estudiante_inscripcion_id
LEFT JOIN lugar_tipo lt ON lt.id = jg.lugar_tipo_id_localidad
LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
LEFT JOIN lugar_tipo lt2 ON lt2.id = lt1.lugar_tipo_id
LEFT JOIN lugar_tipo lt3 ON lt3.id = lt2.lugar_tipo_id
LEFT JOIN lugar_tipo lt4 ON lt4.id = lt3.lugar_tipo_id
WHERE ".$where." AND rc.gestion = 2024 AND c.gestion_tipo_id = 2024  AND c.nivel_tipo_id<>405 AND
rc.institucioneducativa_tipo_id = 4 AND inst.estadoinstitucion_tipo_id = 10
and ei.estadomatricula_tipo_id not in (6,10)
 and t.id in (22,41,19,46,39,44,61,62,63,28,38,50,51,52,53,54,55,56,57,58,59,33,31,47,48)
and m.id<3
and ei.fecha_inscripcion between '2024-01-01' and '2024-07-30' 
	and ei.estadomatricula_tipo_id<>78
and (select max (nc.nota_tipo_id) from estudiante_nota_cualitativa nc where nc.estudiante_inscripcion_id=ei.id) <54
     group by        lt4.lugar,  inst.id, inst.institucioneducativa,a.area_especial
ORDER BY
lt4.lugar, total desc
");
$query2->execute();
$consol_programas = $query2->fetchAll();
//dump($consol_programas);die;
$query22 = $em->getConnection()->prepare("
SELECT
lt4.lugar AS departamento,
inst.id as codigo_sie,
inst.institucioneducativa,
 a.area_especial, count(ei.id) as total
FROM registro_consolidacion rc
INNER JOIN institucioneducativa inst ON rc.unidad_educativa = inst.id
INNER JOIN jurisdiccion_geografica jg on jg.id = inst.le_juridicciongeografica_id
inner join institucioneducativa_curso c  on c.institucioneducativa_id =inst.id
inner join institucioneducativa_curso_especial ce on  c.id=ce.institucioneducativa_curso_id 
inner join especial_area_tipo a on  a.id=ce.especial_area_tipo_id
inner join especial_programa_tipo t on t.id=ce.especial_programa_tipo_id 
inner join especial_servicio_tipo s on s.id=ce.especial_servicio_tipo_id 
inner join nivel_tipo n on c.nivel_tipo_id=n.id
inner join especial_modalidad_tipo m on  ce.especial_modalidad_tipo_id = m.id
inner join estudiante_inscripcion ei on c.id=ei.institucioneducativa_curso_id
inner join estudiante e on e.id=ei.estudiante_id
inner join estudiante_inscripcion_especial eie on ei.id = eie.estudiante_inscripcion_id
LEFT JOIN lugar_tipo lt ON lt.id = jg.lugar_tipo_id_localidad
LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
LEFT JOIN lugar_tipo lt2 ON lt2.id = lt1.lugar_tipo_id
LEFT JOIN lugar_tipo lt3 ON lt3.id = lt2.lugar_tipo_id
LEFT JOIN lugar_tipo lt4 ON lt4.id = lt3.lugar_tipo_id
WHERE ".$where." AND rc.gestion = 2024 AND c.gestion_tipo_id = 2024  AND c.nivel_tipo_id<>405 AND
rc.institucioneducativa_tipo_id = 4 AND inst.estadoinstitucion_tipo_id = 10
and ei.estadomatricula_tipo_id not in (6,10)
 and t.id in (22,41,19,46,39,44,61,62,63,28,38,50,51,52,53,54,55,56,57,58,59,33,31,47,48)
and m.id<3
and ei.fecha_inscripcion between '2024-08-01' and '2024-12-30' 
and ei.estadomatricula_tipo_id<>78
and (select max (nc.nota_tipo_id) from estudiante_nota_cualitativa nc where nc.estudiante_inscripcion_id=ei.id) is null
     group by        lt4.lugar,  inst.id, inst.institucioneducativa,a.area_especial
ORDER BY
lt4.lugar, total desc
");
$query22->execute();
$consol_programas2 = $query22->fetchAll();

        $gestiones = $em->getRepository('SieAppWebBundle:GestionTipo')->findBy(array(), array('id' => 'DESC'));    
        $gestionesArray = array();
        
        foreach ($gestiones as $value) {
            if ($value->getId() >= $mingestion) {
                $gestionesArray[$value->getId()] = $value->getGestion();
            }
        }
        
        return $this->render($this->session->get('pathSystem') . ':Institucioneducativa:index_gestion_lista.html.twig', array(
            'consol' => $consol,
            'consol_servicios' => $consol_servicios,
            'consol_programas' => $consol_programas,
            'consol_servicios2' => $consol_servicios2,
            'consol_programas2' => $consol_programas2,
            'gestiones' => $gestionesArray,
            'gestionactual' => $gestionactual,
            'title' => $title,
            'label' => $label,
            'label_distrito' => $label_distrito,
            'ues' => '',
            'instipoid' => $instipoid
        ));
    }


    public function registroConsolAction(Request $request, $gestionid){       
        $gestionactual = $gestionid;
        $roluser = $this->session->get('roluser');
        $roluserlugarid = $this->session->get('roluserlugarid');
        $bundle = $this->session->get('pathSystem');

        switch ($bundle) {
            case 'SieRegularBundle':
            case 'SieHerramientaBundle':
                $instipoid = 1;
                $title = 'Reporte de consolidación de operativos por gestión y Unidad Educativa - '.$gestionid;
                $label = 'Cantidad de Unidades Educativas que reportaron información';
                $label_distrito = 'Cantidad de Unidades Educativas que reportaron información en el distrito';
                break;

            case 'SieEspecialBundle':
                $instipoid = 4;
                $title = 'Reporte de cierre de operativos por gestión y Centro de Educación Especial - '.$gestionid;
                $label = 'Cantidad de Centros que reportaron matrícula';
                $label_distrito = 'Cantidad de Centros que reportaron matrícula en el distrito';
                break;
            
            default:
                $instipoid = 1;
                $title = 'Reporte de consolidación de operativos por gestión y Unidad Educativa - '.$gestionid;
                $label = 'Cantidad de Unidades Educativas que reportaron información';
                $label_distrito = 'Cantidad de Unidades Educativas que reportaron información en el distrito';
                break;
        }
        
        $consol = $this->get('sie_app_web.funciones')->reporteConsol($gestionid, $roluser, $roluserlugarid, $instipoid);

        switch ($roluser) {
            case 8:
                $ues = $this->get('sie_app_web.funciones')->estadisticaConsolNal($gestionid, $instipoid);
                break;

            case 7:
                $ues = $this->get('sie_app_web.funciones')->estadisticaConsolDptal($gestionid, $roluserlugarid, $instipoid);
                break;

            case 10:
                $ues = $this->get('sie_app_web.funciones')->estadisticaConsolDtal($gestionid, $roluserlugarid, $instipoid);
                break;

            default:
                $ues = null;
                break;
        }

        return $this->render('SieEspecialBundle:Institucioneducativa:consol_especial.html.twig', array(
            'consol' => $consol,
            'ues' => $ues,
            'title' => $title,
            'label' => $label,
            'label_distrito' => $label_distrito,
            'gestionactual' => $gestionactual
        ));
    }

    public function observacionesMaestrosAction(Request $request) {

        //dump( $request->getSession()); die;

        $institucion = $request->getSession()->get('ie_id');        
        $gestion = $request->getSession()->get('idGestion'); // sale 2014 ???
        

        $em = $this->getDoctrine()->getManager();      
        //$query = $em->getConnection()->prepare("select * FROM sp_crea_curso_oferta('$gestionTipo', '$institucioneducativa', '$turnoTipo', '$nivelTipo', '$gradoTipo','$paraleloTipo') ");
        //$query = $em->getConnection()->prepare("select * from public.sp_alerta_materia_sin_maestro('2022', '80730274')");
        $query = $em->getConnection()->prepare("select * from public.sp_alerta_materia_sin_maestro('2022', '$institucion')");
        
        $query->execute();
        $valor= $query->fetchAll();       

        $response = new JsonResponse();
        //return $response->setData(array('data'=>$valor,'existe'=> 0));
        return $response->setData(array('data'=>$valor,'existe'=> sizeof($valor)));
  
    }

    public function getInconsistenciasAction(Request $request) {

         //dump( $request->get('ueid')); die;
         $ueid = $request->get('ueid');
         $institucion = $request->getSession()->get('ie_id');
         $gestion = $request->getSession()->get('idGestion'); // sale 2014 ???
         
 
         $em = $this->getDoctrine()->getManager();      
         //$query = $em->getConnection()->prepare("select * FROM sp_crea_curso_oferta('$gestionTipo', '$institucioneducativa', '$turnoTipo', '$nivelTipo', '$gradoTipo','$paraleloTipo') ");
         //$query = $em->getConnection()->prepare("select * from public.sp_alerta_materia_sin_maestro('2022', '80730274')");
         $query = $em->getConnection()->prepare("select * from sp_validacion_regular_inscripcion_ig_web('2024','$ueid','0');");
         $query->execute();
         $valor= $query->fetchAll();
        // dump($valor); die;
         
         $response = new JsonResponse();
         return $response->setData(array('data'=>$valor,'mensaje'=>''));

    }
  

}
