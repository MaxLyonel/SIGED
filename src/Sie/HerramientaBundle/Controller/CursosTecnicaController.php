<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\acreditacionEspecialidad;
use Doctrine\ORM\EntityRepository;
use Sie\EsquemaBundle\Entity\InstitucioneducativaCurso;

/**
 * Malla Curricular controller.
 *
 */
class CursosTecnicaController extends Controller {

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
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request) {
// Verificacmos si existe la session de usuario
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
//dump($form);
//get the send values
        $ieducativa = $form['sie'];
        $gestion = $form['gestion'];
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ieducativa);
        return $this->render($this->session->get('pathSystem') . ':CursoTecnica:index.html.twig', array(
                    'gestion' => $gestion,
                    'institucion' => $institucion,
                    'form' => $this->createCursoForm(serialize($form))->createView()
        ));
    }

    private function createCursoForm($data) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_cursosTecnica_crear'))
                ->add('datasend', 'hidden', array('data' => $data))
                ->add('crearcurso', 'submit', array('label' => 'Crear Curso', 'attr' => array('class' => 'btn btn-sm', 'data-toggle' => 'tooltip', 'data-title' => "Crear Curso")))
                ->getForm()
        ;
        return $form;
    }

    public function crearAction(Request $request) {
//get the send values
        $form = $request->get('form');

        $arrDataInst = unserialize($form['datasend']);
//get the db conexion
        $em = $this->getDoctrine()->getManager();
//get the send values
        $ieducativa = $arrDataInst['sie'];
        $gestion = $arrDataInst['gestion'];

        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ieducativa);
        $arrTurno = $this->getTurnoBySieAndGestion($arrDataInst);

        return $this->render($this->session->get('pathSystem') . ':CursoTecnica:crear.html.twig', array(
                    'gestion' => $gestion,
                    'institucion' => $institucion,
                    'form' => $this->crearForm($form['datasend'], $arrTurno)->createView()
        ));
        die;
    }

    private function crearForm($data, $arraTurno) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_cursosTecnica_savecurso'))
                ->add('turno', 'choice', array('label' => 'Turno', 'choices' => $arraTurno, 'attr' => array('class' => 'form-control')))
                ->add('especialidad', 'choice', array('label' => 'Especialidad', 'attr' => array('class' => 'form-control')))
                ->add('periodo', 'choice', array('label' => 'Periodo', 'attr' => array('class' => 'form-control')))
                ->add('paralelo', 'choice', array('label' => 'Paralelo', 'attr' => array('class' => 'form-control')))
                ->add('datasend', 'text', array('data' => ($data)))
                ->add('next', 'submit', array('label' => 'Crear Curso', 'attr' => array('class' => 'btn btn-success')))
                ->getForm()
        ;
        return $form;
    }

    /**
     * get turno by sie and gestion
     * @param type $data
     * @return array turno
     */
    private function getTurnoBySieAndGestion($data) {

        $em = $this->getDoctrine()->getManager();
        $queryTurno = $em->getRepository('SieEsquemaBundle:institucioneducativaAcreditacion');
        $query = $queryTurno->createQueryBuilder('iea')
                ->select(' tst.id, tst.turnoSuperior')
                ->leftjoin('SieEsquemaBundle:turnoSuperiorTipo', 'tst', 'WITH', 'iea.turnoSuperiorTipo = tst.id')
                ->where('iea.institucioneducativa = :sie')
                ->andwhere('iea.gestionTipo = :gestion')
                ->setParameter('sie', $data['sie'])
                ->setParameter('gestion', $data['gestion'])
                ->distinct()
                ->orderBy('tst.id', 'ASC')
                ->getQuery();
        $objTurno = $query->getResult();
        $arrTurno = array();
        foreach ($objTurno as $key => $turno) {
            $arrTurno [$turno['id']] = $turno['turnoSuperior'];
        }

        return $arrTurno;
    }

    /**
     * get specialidades by sie, gestion and turno
     * @param Request $request
     * @return type array specialidades
     */
    public function getSpecialidadAction(Request $request) {
        $data = $request->get('form');
        $arrInfoUe = unserialize($data['datasend']);

        $em = $this->getDoctrine()->getManager();
        $querySpecialidad = $em->getRepository('SieEsquemaBundle:institucioneducativaAcreditacion');
        $query = $querySpecialidad->createQueryBuilder('iea')
                ->select(' est.id, est.especialidadEspecialidad ')
                ->leftjoin('SieEsquemaBundle:turnoSuperiorTipo', 'tst', 'WITH', 'iea.turnoSuperiorTipo = tst.id')
                ->leftjoin('SieEsquemaBundle:acreditacionEspecialidad', 'ae', 'WITH', 'iea.acreditacionEspecialidad = ae.id')
                ->leftjoin('SieEsquemaBundle:especialidadSuperiorTipo', 'est', 'WITH', 'ae.especialidadSuperiorTipo=est.id')
                ->where('iea.institucioneducativa = :sie')
                ->andwhere('iea.gestionTipo = :gestion')
                ->andWhere('iea.turnoSuperiorTipo = :turno')
                ->setParameter('sie', $arrInfoUe['sie'])
                ->setParameter('gestion', $arrInfoUe['gestion'])
                ->setParameter('turno', $data['turno'])
                ->distinct()
                ->orderBy('est.id', 'ASC')
                ->getQuery();
        $objSpecialidad = $query->getResult();
        dump($objSpecialidad);
        $arrSpecialidad = array();
        foreach ($objSpecialidad as $key => $special) {
            $arrSpecialidad [$special['id']] = $special['especialidadEspecialidad'];
        }
        $response = new JsonResponse();
        return $response->setData(array('especialidad' => $arrSpecialidad));

        return $arrSpecialidad;
    }

    /**
     * get periodo
     * @param Request $request
     * @return array periodo
     */
    public function getPeriodoAction(Request $request) {
        $data = $request->get('form');
        $arrInfoUe = unserialize($data['datasend']);

        $em = $this->getDoctrine()->getManager();
        $queryPeriodo = $em->getRepository('SieEsquemaBundle:institucioneducativaAcreditacion');
        $query = $queryPeriodo->createQueryBuilder('iea')
                ->select(' pst.id, pst.periodoSuperior ')
                ->leftjoin('SieEsquemaBundle:institucioneducativaPeriodo', 'iep', 'WITH', 'iea.id = iep.institucioneducativaAcreditacion')
                ->leftjoin('SieEsquemaBundle:periodoSuperiorTipo', 'pst', 'WITH', 'iep.periodoSuperiorTipo=pst.id')
                ->leftjoin('SieEsquemaBundle:turnoSuperiorTipo', 'tst', 'WITH', 'iea.turnoSuperiorTipo = tst.id')
                ->leftjoin('SieEsquemaBundle:acreditacionEspecialidad', 'ae', 'WITH', 'iea.acreditacionEspecialidad = ae.id')
                ->leftjoin('SieEsquemaBundle:especialidadSuperiorTipo', 'est', 'WITH', 'ae.especialidadSuperiorTipo=est.id')
                ->where('iea.institucioneducativa = :sie')
                ->andwhere('iea.gestionTipo = :gestion')
                ->andWhere('iea.turnoSuperiorTipo = :turno')
                ->andwhere('est.id = :especial')
                ->setParameter('sie', $arrInfoUe['sie'])
                ->setParameter('gestion', $arrInfoUe['gestion'])
                ->setParameter('turno', $data['turno'])
                ->setParameter('especial', $data['especialidad'])
                ->distinct()
                ->orderBy('pst.id', 'ASC')
                ->getQuery();
        $objPeriodo = $query->getResult();

        $arrPeriodo = array();
        foreach ($objPeriodo as $key => $periodo) {
            $arrPeriodo [$periodo['id']] = $periodo['periodoSuperior'];
        }
        $objParalelo = $em->getRepository('SieAppWebBundle:ParaleloTipo')->findAll();
        $arrParalelo = array();
        foreach ($objParalelo as $key => $paralelo) {
            $arrParalelo [$paralelo->getId()] = $paralelo->getParalelo();
        }
        $response = new JsonResponse();
        return $response->setData(array('periodo' => $arrPeriodo, 'paralelo' => $arrParalelo));

        return $arrSpecialidad;
    }

    public function saveCursoAction(Request $request) {
        dump($request->get('form'));

        //firtst get the institucionEducativa_id
        $data = $request->get('form');
        $arrInfoUe = unserialize($data['datasend']);

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $queryInstEduPeriodo = $em->getRepository('SieEsquemaBundle:institucioneducativaAcreditacion');
            $query = $queryInstEduPeriodo->createQueryBuilder('iea')
                    ->select(' pst.id, pst.periodoSuperior, iep.id as inseduId ')
                    ->leftjoin('SieEsquemaBundle:institucioneducativaPeriodo', 'iep', 'WITH', 'iea.id = iep.institucioneducativaAcreditacion')
                    ->leftjoin('SieEsquemaBundle:periodoSuperiorTipo', 'pst', 'WITH', 'iep.periodoSuperiorTipo=pst.id')
                    ->leftjoin('SieEsquemaBundle:turnoSuperiorTipo', 'tst', 'WITH', 'iea.turnoSuperiorTipo = tst.id')
                    ->leftjoin('SieEsquemaBundle:acreditacionEspecialidad', 'ae', 'WITH', 'iea.acreditacionEspecialidad = ae.id')
                    ->leftjoin('SieEsquemaBundle:especialidadSuperiorTipo', 'est', 'WITH', 'ae.especialidadSuperiorTipo=est.id')
                    ->where('iea.institucioneducativa = :sie')
                    ->andwhere('iea.gestionTipo = :gestion')
                    ->andWhere('iea.turnoSuperiorTipo = :turno')
                    ->andwhere('est.id = :especial')
                    ->andwhere('pst.id = :periodo')
                    ->setParameter('sie', $arrInfoUe['sie'])
                    ->setParameter('gestion', $arrInfoUe['gestion'])
                    ->setParameter('turno', $data['turno'])
                    ->setParameter('especial', $data['especialidad'])
                    ->setParameter('periodo', $data['periodo'])
                    ->distinct()
                    ->orderBy('pst.id', 'ASC')
                    ->getQuery();
            $objInstEduPeriodo = $query->getResult();
            dump($objInstEduPeriodo[0]['inseduId']);
//            die;
            //second save the data in institucioneducativaa_curso
            $objInstEduCurso = new InstitucioneducativaCurso();
            $objInstEduCurso->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(0));
            $objInstEduCurso->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find(0));
            $objGrado = ($data > 4 ? $em->getRepository('SieAppWebBundle:GradoTipo')->find(6) : $em->getRepository('SieAppWebBundle:GradoTipo')->find(5));
            $objInstEduCurso->setGradoTipo($objGrado);
            $objInstEduCurso->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find($data['paralelo']));
            $objInstEduCurso->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($arrInfoUe['gestion']));
            $objInstEduCurso->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find(13));
            $objInstEduCurso->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find(0));
            $objInstEduCurso->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find($data['turno']));
            $objInstEduCurso->setSuperiorInstitucioneducativaPeriodo($em->getRepository('SieEsquemaBundle:institucioneducativaAcreditacion')->find($objInstEduPeriodo[0]['inseduId']));
            $em->persist($objInstEduCurso);
            $em->flush();



            //get the id's in modulo_periodo
            //
        //save the id's in institcioneducativa_curso_oferta
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }




        die;
    }

/////////////////////////////////////////*///////////////////////////////////////////////////////////////////////////////////////////this is
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////the 
/////////////////////////////////////////*///////////////////////////////////////////////////////////////////////////////////////////end
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
