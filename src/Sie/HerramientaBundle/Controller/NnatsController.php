<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sie\AppWebBundle\Entity\Tramite;
use Sie\AppWebBundle\Entity\TramiteDetalle;
use Sie\AppWebBundle\Entity\Documento;
use Sie\AppWebBundle\Entity\RegistroConsolidacion;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class NnatsController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

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
        return $this->render('SieHerramientaBundle:Nnats:index.html.twig', array(
            'form'=>$this->formFindDepDis()->createView()
        ));
    }
    private function formFindDepDis(){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select id, departamento FROM departamento_tipo ORDER BY 1");
        $query->execute();
        $departamento = $query->fetchAll(); //dump($departamento);die;
        $deptArray = array();
        $deptArray[-1] = 'TODOS';
        for ($i = 1; $i < count($departamento); $i++) {
            $deptArray[$departamento[$i]['id']] = $departamento[$i]['departamento'];
        }
        return $this->createFormBuilder()
            //->setAction($this->generateUrl('ReportesNnastlist'))
            ->setMethod('POST')
            ->add('departamento', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $deptArray, 'attr' => array('class' => 'form-control input-lg','onchange'=>'cargarDitrito()')))
            ->add('distrito', 'choice', array('required' => true,'label' => 'Distrito', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control input-lg','onchange'=>'listarNnats()' )))
            ->getForm()
            ;
    }
    public function busquedadistritosAction(Request $request){
        $id_departamento = $request->get('id_departamento');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT dist.id,dist.distrito FROM departamento_tipo  dept INNER JOIN distrito_tipo dist ON dept.id = dist.departamento_tipo_id
                                                WHERE   dept.id = $id_departamento ORDER BY dist.distrito " );
        $query->execute();
        $distrito = $query->fetchAll();
        $distritotArray = array();
        $distritotArray[-1] = 'TODOS';
        for ($i = 0; $i < count($distrito); $i++) {
            $distritotArray[$distrito[$i]['id']] = $distrito[$i]['distrito'];
        }
        $response = new JsonResponse();
        return $response->setData(array('distrito' => $distritotArray));
    }
    public function listarNnatsAction(Request $request )
    {
        $id_departamento = (int)$request->get('id_departamento');
        $id_distrito = (int)$request->get('id_distrito');
        $em = $this->getDoctrine()->getManager();
        if($id_departamento == -1 and $id_distrito == 0 ){
            $query = $em->getConnection()->prepare("select b.cod_dep, b.des_dep, b.cod_dis, b.des_dis, a.id cod_ue_id, a.institucioneducativa desc_ue, c.estudiante
                                                from institucioneducativa a 
                                                inner join 
                                                (select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona
                                                from jurisdiccion_geografica a inner join 
                                                (select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001
                                                from (select id,codigo as cod_dep,lugar_tipo_id,lugar
                                                from lugar_tipo
                                                where lugar_nivel_id=1) a inner join (
                                                select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo
                                                where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id inner join (
                                                select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo
                                                where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id inner join (
                                                select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo
                                                where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id inner join (
                                                select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo
                                                where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id) b on a.lugar_tipo_id_localidad=b.id
                                                inner join 
                                                (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo
                                                where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id) b on a.le_juridicciongeografica_id = b.cod_le
                                                inner join (
                                                select sie, \"count\"(*) estudiante
                                                from estudiante_trabajo_remuneracion
                                                where gestion = 2018
                                                group by sie) c on a.id =  c.sie::integer                                                
                                                ");
            $query->execute();
            $data = $query->fetchAll();
        }elseif  ($id_distrito == -1){
            $query = $em->getConnection()->prepare("select b.cod_dep, b.des_dep, b.cod_dis, b.des_dis, a.id cod_ue_id, a.institucioneducativa desc_ue, c.estudiante
                                                from institucioneducativa a 
                                                inner join 
                                                (select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona
                                                from jurisdiccion_geografica a inner join 
                                                (select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001
                                                from (select id,codigo as cod_dep,lugar_tipo_id,lugar
                                                from lugar_tipo
                                                where lugar_nivel_id=1) a inner join (
                                                select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo
                                                where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id inner join (
                                                select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo
                                                where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id inner join (
                                                select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo
                                                where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id inner join (
                                                select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo
                                                where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id) b on a.lugar_tipo_id_localidad=b.id
                                                inner join 
                                                (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo
                                                where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id) b on a.le_juridicciongeografica_id = b.cod_le
                                                inner join (
                                                select sie, \"count\"(*) estudiante
                                                from estudiante_trabajo_remuneracion
                                                where gestion = 2018
                                                group by sie) c on a.id =  c.sie::integer                                                
                                                where b.cod_dep = '".$id_departamento."' ");
            $query->execute();
            $data = $query->fetchAll(); //dump($data);die;
        }
        else{
            $query = $em->getConnection()->prepare("select b.cod_dep, b.des_dep, b.cod_dis, b.des_dis, a.id cod_ue_id, a.institucioneducativa desc_ue, c.estudiante
                                                from institucioneducativa a 
                                                inner join 
                                                (select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona
                                                from jurisdiccion_geografica a inner join 
                                                (select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001
                                                from (select id,codigo as cod_dep,lugar_tipo_id,lugar
                                                from lugar_tipo
                                                where lugar_nivel_id=1) a inner join (
                                                select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo
                                                where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id inner join (
                                                select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo
                                                where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id inner join (
                                                select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo
                                                where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id inner join (
                                                select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo
                                                where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id) b on a.lugar_tipo_id_localidad=b.id
                                                inner join 
                                                (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo
                                                where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id) b on a.le_juridicciongeografica_id = b.cod_le
                                                inner join (
                                                select sie, \"count\"(*) estudiante
                                                from estudiante_trabajo_remuneracion
                                                where gestion = 2018
                                                group by sie) c on a.id =  c.sie::integer                                                
                                                where b.cod_dep = '".$id_departamento."' and b.cod_dis = '".$id_distrito."'  ");
            $query->execute();
            $data = $query->fetchAll();
        }
        return $this->render('SieHerramientaBundle:Nnats:listaNnats.html.twig',array('data'=>$data));
    }

    /**
     * Reportes Generales - Completo
     */
    public function ReporteNnatsCompletaNacionalPrintPdfAction(Request $request){
        $roluser = $request->get('roluser');
       // $gestion = $request->get('gestion');
        $gestion =  2018; // solo para el operativo del 2018
        $departamento_distrito = $request->get('departamento_distrito');
        $em = $this->getDoctrine()->getManager();
        $lugarTipo = $em->getRepository('SieAppWebBundle:LugarTipo')->find($departamento_distrito);
        $idDepartamento = $lugarTipo->getCodigo();

        $arch = 'NNATS'.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        if($roluser == 8 or $roluser == 20 ) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_nacional_nnats_v1_ma.rptdesign&__format=pdf&gestion='.$gestion));
        }

        if($roluser == 7  ) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_departamental_nnats_v1_ma.rptdesign&__format=pdf&gestion='.$gestion.'&depto='.$idDepartamento));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    public function ReporteNnatsCompletaNacionalPrintXlsAction(Request $request){
        $roluser = $request->get('roluser');
        $gestion = $request->get('gestion');
        $gestion =  2018; // solo para el operativo del 2018
        $departamento_distrito = $request->get('departamento_distrito');
        $em = $this->getDoctrine()->getManager();
        $lugarTipo = $em->getRepository('SieAppWebBundle:LugarTipo')->find($departamento_distrito);
        $idDepartamento = $lugarTipo->getCodigo();

        $arch =  'NNATS'.'_'.date('YmdHis').'.xlsx';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        if($roluser == 8 or $roluser == 20 ) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_nacional_nnats_v1_ma.rptdesign&__format=xlsx&gestion='.$gestion));
        }

        if($roluser == 7  ) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_departamental_nnats_v1_ma.rptdesign&__format=xlsx&gestion='.$gestion.'&depto='.$idDepartamento));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    //Especifico
    public function ReporteNnatsEspecificoNacionalPrintPdfAction(Request $request){
        $roluser = $request->get('roluser');
        // $gestion = $request->get('gestion');
        $gestion =  2018; // solo para el operativo del 2018
        $departamento_distrito = $request->get('departamento_distrito');
        $em = $this->getDoctrine()->getManager();
        $lugarTipo = $em->getRepository('SieAppWebBundle:LugarTipo')->find($departamento_distrito);
        $idDepartamento = $lugarTipo->getCodigo();

        $arch = 'NNATS'.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        if($roluser == 8 or $roluser == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_nacional_nnats_v2_ma.rptdesign&__format=pdf&gestion='.$gestion));
        }

        if($roluser == 7  ) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_departamental_nnats_v2_ma.rptdesign&__format=pdf&gestion='.$gestion.'&depto='.$idDepartamento));
        }
        if($roluser == 10  ) // Tecnico Distrital
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_distrital_nnats_v1_ma.rptdesign&__format=pdf&gestion='.$gestion.'&distrito='.$idDepartamento));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    public function ReporteNnatsEspecificoNacionalPrintXlsAction(Request $request){
        $roluser = $request->get('roluser');
        $gestion = $request->get('gestion');
        $gestion =  2018; // solo para el operativo del 2018
        $departamento_distrito = $request->get('departamento_distrito');
        $em = $this->getDoctrine()->getManager();
        $lugarTipo = $em->getRepository('SieAppWebBundle:LugarTipo')->find($departamento_distrito);
        $idDepartamento = $lugarTipo->getCodigo();
        $arch =  'NNATS'.'_'.date('YmdHis').'.xlsx';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        if($roluser == 8 or $roluser == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_nacional_nnats_v1_ma.rptdesign&__format=xlsx&gestion='.$gestion));
        }

        if($roluser == 7  ) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_departamental_nnats_v2_ma.rptdesign&__format=xlsx&gestion='.$gestion.'&depto='.$idDepartamento));
        }
        if($roluser == 10  ) // Tecnico Distrital
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_distrital_nnats_v1_ma.rptdesign&__format=xlsx&gestion='.$gestion.'&distrito='.$idDepartamento));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
}
