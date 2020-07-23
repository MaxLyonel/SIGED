<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Form\EstudianteType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * ControlOperativoMenuController.
 *
 */
class ControlOperativoMenuController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction() {
        return $this->render('SieRegularBundle:ControlOperativoMenu:index.html.twig');
    }

    public function searchAction(Request $request){
        try {
            $sie = $request->get('sie');
            $em = $this->getDoctrine()->getManager();
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
            $gestion = $this->session->get('currentyear');
            $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);
            if(in_array($this->session->get('roluser'), array(7,8,10))){
                $operativo++;
            }
            $tipoUE = $this->get('funciones')->getTipoUE($sie,$gestion);

            $mensaje = "";

            // Verificamos la tuicion
            $tuicion = $this->get('funciones')->verificaTuicion($sie,$this->session->get('userId'),$this->session->get('roluser'));

            if ($tuicion) {
                // Verificamos si es ue humanistica, nocturan o en transformacion, y GAM
                if(in_array($tipoUE['id'], array(5,6,7,11))){
                    $registro = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus')->findOneBy(array('institucioneducativa'=>$sie,'gestionTipoId'=>$gestion,'notaTipo'=>$operativo));
                    if(!$registro){
                        $registro = null;
                        $mensaje = "La unidad educativa aún no realizó la descarga de su archivo";
                    }
                }else{
                    $registro = null;
                    $mensaje = 'La unidad educativa es '.$tipoUE['tipo'].' por este motivo no descarga su archivo. Debe reportar su información a travéz del Sistema Académico';    
                }

            } else {
                $registro = null;
                $mensaje = 'No tiene tuición sobre la unidad educativa';
            }
            return $this->render('SieRegularBundle:ControlOperativoMenu:response.html.twig',array(
                'registro'=>$registro, 
                'mensaje'=>$mensaje, 
                'tipoUE'=>$tipoUE,
                'institucion'=>$institucion,
                'gestion'=>$gestion,
                'operativo'=>$operativo,
                'nombreOperativo'=>$this->nombreOperativo($operativo),
                'tuicion'=>$tuicion
            ));

        } catch (Exception $e) {
            
        }
    }

    public function nombreOperativo($operativo){
        switch ($operativo) {
            case 0: $nombreOperativo = 'Inicio de gestiòn';break;
            case 1: $nombreOperativo = '1er Bimestre';break;
            case 2: $nombreOperativo = '2do Bimestre';break;
            case 3: $nombreOperativo = '3er Bimestre';break;
            case 4: $nombreOperativo = '4to Bimestre';break;
            case 5: $nombreOperativo = 'Gestión cerrada';break;
            default:
                $nombreOperativo = 'No trabajo inicio de gestión';
                break;
        }
        return $nombreOperativo;
    }

    public function changeAction(Request $request){
        try {
            $idControlOperativo = $request->get('id');
            $em = $this->getDoctrine()->getManager();
            $registro = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus')->find($idControlOperativo);

            // Verificamos si ya subio su archivo al repositorio
            $upload = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findOneBy(array('institucioneducativa'=>$registro->getInstitucioneducativa()->getId(),'gestionTipoId'=>$registro->getGestionTipoId(),'notaTipo'=>$registro->getNotaTipo()->getId(),'institucioneducativaOperativoLogTipo'=>2));

            if(!$upload){
                $nuevoEstado = 0;
                $registro->setEstadoMenu($nuevoEstado);
                $em->flush();

                $nuevoEstadoText = 'No descargado';
                $msg = 'Estado actualizado, la Unidad Educativa ya puede descargar su archivo.';
            }else{
                $nuevoEstado = $registro->getEstadoMenu();
                if($nuevoEstado == 0){
                    $nuevoEstadoText = 'No Descargado';
                    $msg = '';
                }else{
                    $nuevoEstadoText = 'Descargado';
                    $msg = '';
                }

                if($upload){
                    $msg = 'No se puede actualizar el estado porque la Unidad Educativa ya reporto(subio) su archivo.';
                }
            }
            return new JsonResponse(array('nuevoEstado'=>$nuevoEstado,'nuevoEstadoText'=>$nuevoEstadoText,'msg'=>$msg));
        } catch (Exception $e) {
            return new JsonResponse(array('nuevoEstado'=>'error'));
        }

    }
}
