<?php

namespace Sie\HerramientaBundle\Controller;

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
        return $this->render('SieHerramientaBundle:ReactivarBTH:index.html.twig');
    }
    public function buscaTramiteBTHAction(Request $request){
        
        $nroTramite = $request->get('nroTramite');
        dump($nroTramite);die;
        return $nroTramite;
    }

    
}
