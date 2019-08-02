<?php

namespace Sie\RegularBundle\Controller;

use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Controller\WfTramiteController;
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLogTipo;
use Sie\AppWebBundle\Entity\RehabilitacionBth;




/**
 * ChangeMatricula controller.
 *
 */
class ReactivarBTHController extends Controller {
    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
    }
    public function indexAction (Request $request) {

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $form = $this->createFormBuilder()
            ->add('nro', 'text', array('label' => 'Nro.', 'required' => true, 'attr' => array('class' => 'form-control')))
            ->add('buscar', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary','onclick'=>'buscarTramite()')))
            ->getForm();
        return $this->render('SieRegularBundle:ReactivarBTH:index.html.twig',array(
            'form' => $form->createView()
            )
        );
    }
    public function buscaTramiteBTHAction(Request $request){
        
        $id = $request->get('nro');
        $em = $this->getDoctrine()->getManager();
        $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
        if($ie){
            $idInstitucion = $ie->getId();
        }else{
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id'=>$id,'flujoTipo'=>6));
            if($tramite){
                $idInstitucion = $tramite->getInstitucioneducativa()->getId();
            }else{
                $idInstitucion = null;
            }
        }
        
        if ($idInstitucion){
            $tramites = $em->getRepository('SieAppWebBundle:Tramite')->createQueryBuilder('t')
                    ->select('SUM(CASE WHEN t.fechaFin IS NULL THEN 1 ELSE 0 END) AS pendientes, SUM(CASE WHEN t.fechaFin IS NOT NULL THEN 1 ELSE 0 END) AS concluidos,COUNT(t.institucioneducativa) AS cantidad')
                    ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = t.institucioneducativa')
                    ->where('t.institucioneducativa = :id')
                    ->andwhere('t.tramiteTipo IN (:tipo)')
                    ->andwhere('t.flujoTipo = 6')
                    ->groupBy('ie.id')
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('tipo', array(27,28,31))
                    ->getQuery()
                    ->getResult();
            //dump($tramites);die;
            if($tramites){
                if($tramites[0]['cantidad'] > 0){
                    if($tramites[0]['pendientes'] > 0){ //TIENE PENDIENTES
                        $response = new JsonResponse();    
                        if($tramites[0]['cantidad'] == 1){
                            $msg = 'La Unidad Educativa tiene un trámite BTH pendiente. Finalize su trámite para poder rehabilitar BTH';
                        }else{
                            $msg = 'La Unidad Educativa ya rehabilitó su trámite para BTH. Finalize su último trámite para poder rehabilitar nuevamente';
                        }
                        return $response->setData(array(
                            'msg' => $msg,
                        ));    
                    }else{ //NO TIENE TRAMITES PENDIENTES
                        //$tr = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('institucioneducativa'=>$id,'flujoTipo'=>6),array('id'=>'DESC'),1);
                        $tr = $em->getRepository('SieAppWebBundle:Tramite')->createQueryBuilder('t')
                            ->select('t.id,ie.id as codsie,ie.institucioneducativa,t.fechaRegistro,t.fechaFin,g.id as grado,tt.tramiteTipo,tt.id as tramiteTipoId,f.id as flujoTipoId')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = t.institucioneducativa')
                            ->innerJoin('SieAppWebBundle:TramiteTipo', 'tt', 'WITH', 'tt.id = t.tramiteTipo')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico', 'h', 'WITH', 'ie.id = h.institucioneducativaId')
                            ->innerJoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'g.id = h.gradoTipo')
                            ->innerJoin('SieAppWebBundle:FlujoTipo', 'f', 'WITH', 'f.id = t.flujoTipo')
                            ->where('t.institucioneducativa = :id')
                            ->andwhere('t.tramiteTipo IN (:tipo)')
                            ->andwhere('h.gestionTipoId = (select max(h1.gestionTipoId) from SieAppWebBundle:InstitucioneducativaHumanisticoTecnico h1 where h1.institucioneducativaId=' . $idInstitucion.')')
                            ->andwhere('t.flujoTipo = 6')
                            ->orderBy('t.id','DESC')
                            ->setParameter('id', $idInstitucion)
                            ->setParameter('tipo', array(27,28,31))
                            ->getQuery()
                            ->getResult();
                        //dump($tr);die;
                        if($tr[0]['tramiteTipoId'] == 31){
                            $response = new JsonResponse();    
                            $msg = 'La Unidad Educativa ya rehabilitó su trámite para BTH.';
                            return $response->setData(array(
                                'msg' => $msg,
                            ));    
                        }
                        $form = $this->createFormBuilder()
                            ->setAction($this->generateUrl('reactivarbth_buscar_nuevo_guardar'))
                            ->add('idtramite', 'hidden', array('data' => $tr[0]['id']))
                            ->add('codsie', 'hidden', array('data' => $tr[0]['codsie']))
                            ->add('obs', 'textarea', array('label' => 'Observación', 'required' => true, 'attr' => array('class' => 'form-control','style'=>'text-transform:uppercase;')))
                            ->add('adjunto', 'file', array('label' => 'Adjuntar respaldo (Máximo permitido 3M)', 'attr' => array('title'=>"Adjuntar Respaldo",'accept'=>"application/pdf,.doc,.docx")))
                            ->add('guardar', 'submit', array('label' => 'Reactivar Trámite', 'attr' => array('class' => 'btn btn-primary')))
                            ->getForm();

                        return $this->render('SieRegularBundle:ReactivarBTH:reactivarBthGuardar.html.twig',array(
                                'formDatos' => $form->createView(),
                                'tramite'=> $tr,
                        ));
            
                    }
                }else{
                    $response = new JsonResponse();    
                    return $response->setData(array(
                        'msg' => 'El Nro. de Trámite o Código SIE son incorrectos para la rehabilitacion.',
                    ));    
                }
            }else{
                $response = new JsonResponse();    
                return $response->setData(array(
                    'msg' => 'El Nro. de Trámite o Código SIE son incorrectos para la rehabilitacion.',
                ));    
            }
        }else{
            $response = new JsonResponse();    
            return $response->setData(array(
                'msg' => 'El Nro. de Trámite o Código SIE son incorrectos para la rehabilitacion.',
            ));    
        }
    }

    public function buscaTramiteBTHGuardarAction(Request $request){
        
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $form = $request->get('form');
        $documento = $request->files->get('form')['adjunto'];

        /*Validacion para que guarde el docuemnto*/
        if(!empty($documento)){
            $root_bth_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$form['codsie'];
            //$root_bth_path = 'uploads/archivos/flujos/'.$form['codsie'];
            //dump($root_bth_path);die;
            if (!file_exists($root_bth_path)) {
                mkdir($root_bth_path, 0777);
            }
            $destination_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$form['codsie'].'/bth/';
            //$destination_path = 'uploads/archivos/flujos/'.$form['codsie'].'/bth/';
            if (!file_exists($destination_path)) {
                mkdir($destination_path, 0777);
            }
            $imagen = date('YmdHis').'.'.$documento->getClientOriginalExtension();
            $documento->move($destination_path, $imagen);
        }else{
            $imagen='default-2x.pdf';
        }
        $em = $this->getDoctrine()->getManager();
        //dump($form,$imagen,$sesion->get('currentyear'));die;
        $ieht = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$form['codsie'],'gestionTipoId'=>$sesion->get('currentyear')));
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($form['idtramite']);
        //dump($form,$imagen,$ieht);die;
        $em->getConnection()->beginTransaction();
        try {
            /**
             * registro de reactivacion bth
            */
            $rehabilitacionBth = new RehabilitacionBth();    
            $rehabilitacionBth->setObs(mb_strtoupper($form['obs'],'UTF-8') );
            $rehabilitacionBth->setFechaInicio(new \DateTime(date('Y-m-d')));
            $rehabilitacionBth->setAdjunto($imagen);
            $rehabilitacionBth->setTramite($tramite);
            $rehabilitacionBth->setInstitucioneducativaHumanisticoTecnico($ieht);
            $rehabilitacionBth->setUsuarioRegistroId($id_usuario);
            $rehabilitacionBth->setInstitucioneducativaId($ieht->getInstitucioneducativaId());
            $em->persist($rehabilitacionBth);
            $em->flush();
            
            //Modificacion tabla institucioneducativa_humanistico_tecnico
            $ieht->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(0));
            $ieht->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find(5));
            $ieht->setFechaModificacion(new \DateTime(date('Y-m-d')));
            $em->flush();

            //Modificacion tipo de tramite a Regularizacion en tramite
            $tramite->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find(31));
            $em->flush();

            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('success', 'El tramite fué reactivado, la Unidad Educativa puede volver a iniciar un nuevo trámite como Registro Nuevo.');
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un error al reactivar el trámite, vuelva a intentar.');
        }
        
        return $this->redirectToRoute('reactivarbth_index');

    }
}
