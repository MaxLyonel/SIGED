<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\Usuario;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Controller\ReporteController as ReporteEstaditicoRegular;
use Sie\AppWebBundle\Entity\LogTransaccion;
use Sie\AppWebBundle\Entity\UsuarioSession;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Cookie;


class DefaultController extends Controller {

    public $session;
    private $myarrayRoles;

    /**
     * construct function
     */
    public function __construct() {
        //load the session component
        $this->session = new Session();
        //$this->session->set('currentyear', '2015');
    }

    /**
     * build the login interface
     * @param Request $request
     * @return object form login
     */
    public function indexAction(Request $request) {

        $this->session = $request->getSession();

        //$this->session->clear();
        $this->session->set('currentyear', date("Y"));

        //settear variables a usar antes de la interfaz login
        switch ($request->server->get('HTTP_HOST')) {
            case '172.20.196.13:8011':
            case 'siged.sie.gob.bo':
                $sysname = 'Sistema Siged';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutRegular.html.twig';
                $this->session->set('pathSystem', "SieRegularBundle");
                $this->session->set('sistemaid', 1);
                $this->session->set('color', 'blue');
                break;
            case '172.20.196.13:8015':
                $sysname = 'ALTERNATIVA';
                $sysporlet = 'green';
                $sysbutton = false;
                $layout = 'layoutAlternativa.html.twig';
                $this->session->set('pathSystem', "SieAlternativaBundle");
                break;
            case '172.20.196.191:8025':
            case 'sieitt.sie.gob.bo':
                $sysname = 'DGESTTLA ESTADISTICO';
                $sysporlet = 'blue';
                $sysbutton = false;
                $layout = 'layoutTecnicaEst.html.twig';
                $this->session->set('pathSystem', "SieTecnicaEstBundle");
                break;
            case '172.20.196.191:8022':
            case 'sieuni.sie.gob.bo':
                $sysname = 'UNIVERSIDADES';
                $sysporlet = 'blue';
                $sysbutton = false;
                $layout = 'layoutUniversity.html.twig';
                $this->session->set('pathSystem', "SieUniversityBundle");
                break;
            case '172.20.16.239a':
            // case '172.20.196.7':
                $sysname = 'Herramienta';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutHerramienta.html.twig';
                $this->session->set('pathSystem', "SieHerramientaBundle");
                $this->session->set('color', 'primary');
                break;
            case 'academico.sie.gob.bo':
            case 'academico.local':
            case '172.20.196.5:8013':
            case '172.20.0.53:8013':
                $sysname = 'Sistema Académico Educación Regular';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutHerramienta.html.twig';
                $this->session->set('pathSystem', "SieHerramientaBundle");
                $this->session->set('color', 'primary');
                $this->session->set('sistemaid', 6);
                break;
            case '172.20.0.53:8013':
                $sysname = 'Herramienta';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutHerramienta.html.twig';
                $this->session->set('pathSystem', "SieHerramientaBundle");
                break;
            case '172.20.196.5:8016':
            case '172.20.196.191:8016':
            case 'www.herramientaalternativa.local':
            case 'alternativa.sie.gob.bo':
            case 'alternativa.local':
                $sysname = 'Sistema Académico Educación Alternativa';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutHerramientaAlternativa.html.twig';
                $this->session->set('pathSystem', "SieHerramientaAlternativaBundle");
                $this->session->set('color', 'success');
                $this->session->set('sistemaid', 2);
                break;
            case 'eduper.sie.gob.bo':
            case 'permanente.local':
            case '172.20.196.7':
                $sysname = 'Sistema Académico Educación Permanente';
                $sysporlet = 'green';
                $sysbutton = false;
                $layout = 'layoutPermanente.html.twig';
                $this->session->set('pathSystem', "SiePermanenteBundle");
                break;
            case 'www.herramienta.local':
                $sysname = 'Sistema Académico Educación Regular';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutHerramienta.html.twig';
                $this->session->set('pathSystem', "SieHerramientaBundle");
                $this->session->set('color', 'primary');
                break;
            case '172.20.196.13:8014':
            case 'rue.sie.gob.bo':
            case '172.20.16.224':
                $sysname = 'RuePublico';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutRuePublico.html.twig';
                $this->session->set('pathSystem', "SieRueBundle");
                break;
            case 'rue.interno.sie.gob.bo':
                $sysname = 'Rue';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutRue.html.twig';
                $this->session->set('pathSystem', "SieRueBundle");
                break;
            case 'www.siged.locals':
                $sysname = 'PERMANENTE';
                $sysporlet = 'green';
                $sysbutton = false;
                $layout = 'layoutPermanente.html.twig';
                $this->session->set('pathSystem', "SiePermanenteBundle");
                break;
            // case 'diplomas2015.sie.gob.bo':
            //     $sysname = 'DIPLOMAS';
            //     $sysporlet = 'dpl';
            //     $sysbutton = false;
            //     $layout = 'layoutDiplomas.html.twig';
            //     $this->session->set('pathSystem', "SieDiplomaBundle");
            //     break;
            case '172.20.16.239a':
            case 'diplomas2015.local':
                $sysname = 'DIPLOMAS';
                $sysporlet = 'dpl';
                $sysbutton = false;
                $layout = 'layoutDiplomas.html.twig';
                $this->session->set('pathSystem', "SieDiplomaBundle");
                break;
            // case 'diplomas.sie.gob.bo':
            //     $sysname = 'DIPLOMAS';
            //     $sysporlet = 'dpl';
            //     $sysbutton = false;
            //     $layout = 'layoutDiplomas.html.twig';
            //     $this->session->set('pathSystem', "SieDiplomaBundle");
            //     break;
            case 'reportes.sie.gob.bo':
            case 'reportes':
                $sysname = 'REPORTES';
                $sysporlet = 'rep';
                $sysbutton = false;
                $layout = 'layoutReportes.html.twig';
                $this->session->set('pathSystem', "SieAppWebBundle");
                break;
            case '172.20.196.17a':
                $sysname = 'REPORTES';
                $sysporlet = 'rep';
                $sysbutton = false;
                $layout = 'layoutReportes.html.twig';
                $this->session->set('pathSystem', "SieAppWebBundle");
                break;
            case 'certificacionalt.sie.gob.bo':
            case '172.20.17.249':
            case '172.20.196.13:8020':
            case '172.20.0.53:8020':
                $sysname = 'TRAMITES';
                $sysporlet = 'green';
                $sysbutton = false;
                $layout = 'layoutCertification.html.twig';
                $this->session->set('pathSystem', "SieTramitesBundle");
                break;
            case '172.20.196.17a':
                $sysname = 'JUEGOS';
                $sysporlet = 'jdp';
                $sysbutton = false;
                $layout = 'layoutJuegos.html.twig';
                $this->session->set('pathSystem', "SieJuegosBundle");
                break;
            case '172.20.196.18':                
                // $sysname = 'TRAMITE';
                // $sysporlet = 'green';
                // $sysbutton = false;
                // $layout = 'layoutTramites.html.twig';
                // $this->session->set('pathSystem', "SieTramitesBundle");
                // break;
                // $sysname = 'Sistema Siged';
                // $sysporlet = 'blue';
                // $sysbutton = true;
                // $layout = 'layoutRegular.html.twig';
                // $this->session->set('pathSystem', "SieRegularBundle");
                // $this->session->set('sistemaid', 1);
                // $this->session->set('color', 'blue');
                // break;
                // $sysname = 'UNIVERSIDADES';
                // $sysporlet = 'blue';
                // $sysbutton = false;
                // $layout = 'layoutUniversity.html.twig';
                // $this->session->set('pathSystem', "SieUniversityBundle");
                // break;
                // $sysname = 'Sistema Académico Educación Regular';
                // $sysporlet = 'blue';
                // $sysbutton = true;
                // $layout = 'layoutHerramienta.html.twig';
                // $this->session->set('pathSystem', "SieHerramientaBundle");
                // $this->session->set('color', 'primary');
                // $this->session->set('sistemaid', 6);
                // break;
                $sysname = 'DGESTTLA ESTADISTICO';
                $sysporlet = 'blue';
                $sysbutton = false;
                $layout = 'layoutTecnicaEst.html.twig';
                $this->session->set('pathSystem', "SieTecnicaEstBundle");
                break;
                // $sysname = 'Sistema Académico Educación Alternativa';
                // $sysporlet = 'blue';
                // $sysbutton = true;
                // $layout = 'layoutHerramientaAlternativa.html.twig';
                // $this->session->set('pathSystem', "SieHerramientaAlternativaBundle");
                // $this->session->set('color', 'success');
                // $this->session->set('sistemaid', 2);
                // break;
                // $sysname = 'Sistema Académico Educación Permanente';
                // $sysporlet = 'green';
                // $sysbutton = false;
                // $layout = 'layoutPermanente.html.twig';
                // $this->session->set('pathSystem', "SiePermanenteBundle");
                // break;
            case 'tramite.sie.gob.bo':
            case 'diplomas.sie.gob.bo':
            case 'diplomas2015.sie.gob.bo':           
                    $sysname = 'TRAMITE';
                    $sysporlet = 'green';
                    $sysbutton = false;
                    $layout = 'layoutTramites.html.twig';
                    $this->session->set('pathSystem', "SieTramitesBundle");
                    break;
            case 'juegos.minedu.gob.bo':
            case '172.20.196.13:8018':
                $sysname = 'JUEGOS';
                $sysporlet = 'jdp';
                $sysbutton = false;
                $layout = 'layoutJuegos.html.twig';
                $this->session->set('pathSystem', "SieJuegosBundle");
                break;
            case 'especial.sie.gob.bo':
            case 'especial.local':
            case '172.20.196.13:8010':
            case '172.20.0.53:8010':
                $sysname = 'Sistema Académico Educación Especial';
                $sysporlet = 'red';
                $sysbutton = true;
                $layout = 'layoutEspecialSie.html.twig';
                $this->session->set('pathSystem', "SieEspecialBundle");
                $this->session->set('color', 'danger');
                break;
            case 'lc.herramientaregular.ch':
                $sysname = 'Herramienta';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutHerramienta.html.twig';
                $this->session->set('pathSystem', "SieHerramientaBundle");
                break;
            case 'lc.herramientaalternativa.ch':
                $sysname = 'Herramienta';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutHerramientaAlternativa.html.twig';
                $this->session->set('pathSystem', "SieHerramientaAlternativaBundle");
                break;
            case 'lc.superior.ch'://80730830
                $sysname = 'Herramienta';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutDgesttla.html.twig';
                $this->session->set('pathSystem', "SieDgesttlaBundle");
                break;
            case '172.20.196.13:8012':
                $sysname = 'PNP';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutPnp.html.twig';
                $this->session->set('pathSystem', "SiePnpBundle");
                break;
            case 'ritt.local':
            case 'ritt.sie.gob.bo':
                $sysname = 'RITT';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutRie.html.twig';
                $this->session->set('pathSystem', "SieRieBundle");
                break;
            case '172.20.196.13:8024':
            case 'www.dgesttla.local':
            case 'dgesttla.sie.gob.bo':
                $sysname = 'DGESTTLA';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutDgesttla.html.twig';
                $this->session->set('pathSystem', "SieDgesttlaBundle");
                break;
            case '172.20.196.13:8030':
            // case '172.20.196.7':
            case 'inscripcionocepb.minedu.gob.bo':
            case 'olimpiada.sie.gob.bo':
                $sysname = 'olimpiadas';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutOlimpiadas.html.twig';
                $this->session->set('pathSystem', "SieOlimpiadasBundle");
                break;
            case '172.20.196.13':
            case 'procesos.interno.sie.gob.bo':
                $sysname = 'PROCESOS';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutProcesos.html.twig';
                $this->session->set('pathSystem', "SieProcesosBundle");
                break;
            default :
                // $sysname = 'ESPECIAL';
                // $sysporlet = '#F44336';//red
                // $sysbutton = true;
                // $layout = 'layoutEspecialSie.html.twig';
                // $this->session->set('pathSystem', "SieEspecialBundle");

                // $sysname = 'Sistema Académico Educación Alternativa';
                // $sysporlet = 'blue';
                // $sysbutton = true;
                // $layout = 'layoutHerramientaAlternativa.html.twig';
                // $this->session->set('pathSystem', "SieHerramientaAlternativaBundle");
                // $this->session->set('color', 'success');
                // $this->session->set('sistemaid', 2);


                // $sysname = 'Sistema Siged';
                // $sysporlet = 'blue';
                // $sysbutton = true;
                // $layout = 'layoutRegular.html.twig';
                // $this->session->set('pathSystem', "SieRegularBundle");
                // $this->session->set('sistemaid', 1);
                // $this->session->set('color', 'blue');
                
                
                $sysname = 'Sistema Académico Educación Regular';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutHerramienta.html.twig';
                $this->session->set('pathSystem', "SieHerramientaBundle");
                $this->session->set('color', 'primary');
                $this->session->set('sistemaid', 6);
                
            break;            
            
                // $sysname = 'REGULAR';
                // $sysporlet = 'blue';
                // $sysbutton = true;
                // $layout = 'layoutRegular.html.twig';
                // $this->session->set('pathSystem', "SieRegularBundle");
                //$sysname = 'Herramienta Alternativa';
                //$sysporlet = 'blue';
                //$sysbutton = true;
                //$layout = 'layoutHerramientaAlternativa.html.twig';
                //$this->session->set('pathSystem', "SieHerramientaAlternativaBundle");

                // $sysname = 'PROCESOS';
                // $sysporlet = 'blue';
                // $sysbutton = true;
                // $layout = 'layoutProcesos.html.twig';
                // $this->session->set('pathSystem', "SieProcesosBundle");
                // break;

                $sysname = 'Sistema Siged';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutRegular.html.twig';
                $this->session->set('pathSystem', "SieRegularBundle");
                $this->session->set('sistemaid', 1);
                $this->session->set('color', 'blue');
                break;   
            case '172.20.196.5:9008':
                $sysname = 'Sistema Académico Educación Regular';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutHerramienta.html.twig';
                $this->session->set('pathSystem', "SieHerramientaBundle");
                $this->session->set('color', 'primary');
                $this->session->set('sistemaid', 6);
                break;
            case '172.20.196.191:9009':
                $sysname = 'Sistema Siged';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutRegular.html.twig';
                $this->session->set('pathSystem', "SieRegularBundle");
                $this->session->set('sistemaid', 1);
                $this->session->set('color', 'blue');
                break;

            case 'pnp.sie.gob.bo':
                $sysname = 'PNP';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutPnp.html.twig';
                $this->session->set('pathSystem', "SiePnpBundle");
                break;
            case 'herramienta.local':
                $sysname = 'Herramienta';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutHerramienta.html.twig';
                $this->session->set('pathSystem', "SieHerramientaBundle");
                break;
            case 'siged.local':
                $sysname = 'Sistema Siged';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutRegular.html.twig';
                $this->session->set('pathSystem', "SieRegularBundle");
                $this->session->set('sistemaid', 1);
                $this->session->set('color', 'blue');
                break;
            case 'herramientalt.local':
                $sysname = 'Herramienta Alternativa';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutHerramientaAlternativa.html.twig';
                $this->session->set('pathSystem', "SieHerramientaAlternativaBundle");
                break;
//            default :
//                $sysname = 'PNP';
//                $sysporlet = 'black';
//                $sysbutton = true;
//                $this->session->set('pathSystem', "SiePnpBundle");
//                break;
//            default :
//                $sysname = 'USUARIOS';
//                $sysporlet = 'white';
//                $sysbutton = true;
//                $this->session->set('pathSystem', "SieUsuariosBundle");
//                break;
            case '172.20.196.13:8021':
            case '172.20.0.53:8021':
            case 'infraestructura.local':
                $sysname = 'INFRAESTRUCTURA';
                $sysporlet = 'black';
                $sysbutton = false;
                $layout = 'layoutInfraestructura.html.twig';
                $this->session->set('pathSystem', "SieInfraestructuraBundle");
                break;
            case '172.20.196.13:8023':
            case '172.20.0.53:8021':
            // case '172.20.196.7':
            case 'infraestructura.local':
                $sysname = 'INFRAESTRUCTURA';
                $sysporlet = 'black';
                $sysbutton = false;
                $layout = 'layoutInfra.html.twig';
                $this->session->set('pathSystem', "SieInfraBundle");
                break;

            case '172.20.16.221':
                $sysname = 'BJP';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutBjp.html.twig';
                $this->session->set('pathSystem', "SieBjpBundle");
                break;
            case 'lc.bono.ch':
                $sysname = 'Bono';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutBjp.html.twig';
                $this->session->set('pathSystem', "SieBjpBundle");
                break;

            case 'lc.diplomas.ch':
                $sysname = 'DIPLOMAS';
                $sysporlet = 'dpl';
                $sysbutton = false;
                $layout = 'layoutDiplomas.html.twig';
                $this->session->set('pathSystem', "SieDiplomaBundle");
                break;
            case 'www.gis.local':
            // case '172.20.196.7':
            case 'sigee.sie.gob.bo':
                $this->session->set('pathSystem', "SieGisBundle");
                return $this->render('SieGisBundle:Default:index.html.twig');
                break;
            case 'sigeeeee.sie.gob.bo':
                $sysname = 'Herramienta';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutHerramienta.html.twig';
                $this->session->set('pathSystem', "SieHerramientaBundle");
                break;
            case 'alternativaaa.sie.gob.bo':
                $sysname = 'Herramienta Alternativa';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutHerramientaAlternativa.html.twig';
                $this->session->set('pathSystem', "SieHerramientaAlternativaBundle");
                break;
            case 'ue-alta-demanda.minedu.gob.bo':
                $this->session->set('pathSystem', "AppWebBundle");
                return $this->render('SieAppWebBundle:PreInscription:index.html.twig');
                break;  
            case '172.20.196.5:9006': /// control de datos cel students
                $this->session->set('pathSystem', "AppWebBundle");
                return $this->render('SieAppWebBundle:ControlDatosCelular:index.html.twig');
                break;    
            case '172.20.196.5:9007': /// login plataforma
	    case 'aula.minedu.gob.bo':
                $this->session->set('pathSystem', "AppWebBundle");
                return $this->render('SieAppWebBundle:ControlDatosPlataforma:index.html.twig');
                break;   
            case '172.20.196.5:9008':
                $sysname = 'Sistema Académico Educación Regular';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutHerramienta.html.twig';
                $this->session->set('pathSystem', "SieHerramientaBundle");
                $this->session->set('color', 'primary');
                $this->session->set('sistemaid', 6);
                break;
            case '172.20.196.5:9009':
                $sysname = 'Sistema Siged';
                $sysporlet = 'blue';
                $sysbutton = true;
                $layout = 'layoutRegular.html.twig';
                $this->session->set('pathSystem', "SieRegularBundle");
                $this->session->set('sistemaid', 1);
                $this->session->set('color', 'blue');
                break;       
            case '172.20.196.5:8010':
                $sysname = 'Sistema Académico Educación Especial';
                $sysporlet = 'red';
                $sysbutton = true;
                $layout = 'layoutEspecialSie.html.twig';
                $this->session->set('pathSystem', "SieEspecialBundle");
                $this->session->set('color', 'danger');
                break;  

        }


        $this->session->set('sysname', $sysname);
        $this->session->set('sysporlet', $sysporlet);
        $this->session->set('sysbutton', $sysbutton);
        $this->session->set('layout', $layout);

        $em = $this->getDoctrine()->getManager();
        // dump($sysname);die;
        //***********************
        //*****CONFIGURACIONES EXTAS PARA SISTEMAS DE REPORTES
        //review if the system is Reportes
        if ($sysname === 'REPORTES') {

            return $this->redirectToRoute('reporte_regular_index');
            // $reporteController = new ReporteEstaditicoRegular();
            // $reporteController->setContainer($this->container);
            // $em = $this->getDoctrine()->getManager();
            // /*
            //  * Define la zona horaria y halla la fecha actual
            //  */
            // date_default_timezone_set('America/La_Paz');
            // $fechaActual = new \DateTime(date('Y-m-d'));
            // $gestionActual = date_format($fechaActual,'Y');
            // $entidad = $reporteController->buscaEntidadRol(0,0);
            // $subEntidades = $reporteController->buscaSubEntidadRol(0,0);
            // $entityEstadistica = $reporteController->buscaEstadisticaAreaRol(0,0);
            // //$entityEstadisticaUE = $reporteController->buscaEstadisticaUERol(0,0);
            // //$entityEstadisticaEE = $reporteController->buscaEstadisticaEERol(0,0);
            // $fechaEstadisticaRegular = $reporteController->buscaFechaVistaMaterializadaRegular($gestionActual);
            // $chartMatricula = $reporteController->chartColumnInformacionGeneral($entityEstadistica,"Matrícula",$gestionActual,1,"chartContainerMatricula");
            // $chartNivel = $reporteController->chartDonut3dInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Nivel de Estudio",$gestionActual,2,"chartContainerEfectivoNivel");
            // $chartNivelGrado = $reporteController->chartDonutInformacionGeneralNivelGrado($entityEstadistica,"Estudiantes Matriculados según Nivel de Estudio y Año de Escolaridad ",$gestionActual,6,"chartContainerEfectivoNivelGrado");
            // $chartGenero = $reporteController->chartPieInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Sexo",$gestionActual,3,"chartContainerEfectivoGenero");
            // $chartArea = $reporteController->chartPyramidInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Área Geográfica",$gestionActual,4,"chartContainerEfectivoArea");
            // $chartDependencia = $reporteController->chartColumnInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Dependencia",$gestionActual,5,"chartContainerEfectivoDependencia");
            // return $this->render($this->session->get('pathSystem') . ':Reporte:matriculaEducativaRegular.html.twig', array(
            //     'infoEntidad'=>$entidad,
            //     'infoSubEntidad'=>$subEntidades,
            //     'infoEstadistica'=>$entityEstadistica,
            //     //'infoEstadisticaUE'=>$entityEstadisticaUE,
            //     //'infoEstadisticaEE'=>$entityEstadisticaEE,
            //     'rol'=>0,
            //     'datoGraficoMatricula'=>$chartMatricula,
            //     'datoGraficoNivel'=>$chartNivel,
            //     'datoGraficoNivelGrado'=>$chartNivelGrado,
            //     'datoGraficoGenero'=>$chartGenero,
            //     'datoGraficoArea'=>$chartArea,
            //     'datoGraficoDependencia'=>$chartDependencia,
            //     'mensaje'=>'$("#modal-bootstrap-tour").modal("show");',
            //     'gestion'=>$gestionActual,
            //     'fechaEstadisticaRegular'=>$fechaEstadisticaRegular,
            //     'form' => $this->createLoginForm()->createView()
            // ));
        }
        //*****FIN DE CONFIGURACIONES PARA OTROS SUBSISTEMAS
        //***********************

        //***********************
        //*****VUELVE A GENERAR LA PANTALLA DE LOGEO
        $arrDataLogin = array('form' => $this->createLoginForm()->createView());
        if ($sysname == 'RuePublico') {
            return $this->redirect($this->generateURL('consulta_rue'));
            /*$arrDataLogin['formInstitucioneducativa'] = $this->createSearchFormInstitucioneducativa()->createView();
            $arrDataLogin['formInstitucioneducativaId'] = $this->createSearchFormInstitucioneducativaId()->createView();
            $arrDataLogin['formInstitucioneducativaTipo'] = $this->createSearchFormInstitucioneducativaTipo()->createView();*/
        }

        /*
        *  ////// REDIRECCIONAMOS AL FORMULARIO DE LOGIN SEGUN EL SUBSISTEMA
        */
        return $this->render('SieAppWebBundle:Login:login4.html.twig',array(
            'last_username'=>$this->get('request')->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'=>array('message'=>'¡Ocurrió un error interno!')//'error' => $this->get('request')->getSession()->get(SecurityContext::AUTHENTICATION_ERROR) 
        ));
    }

    /**
    * FUNCION PARA VALIDAR LOS ROLES Y OTRAS VALIDACIONES
    */
    public function create_sessions_loginAction(Request $request){
        $sesion = $request->getSession();
        try {
            $em = $this->getDoctrine()->getManager();
            $this->session = $request->getSession();
            //***************************
            //******REALIZA LA BUSQUEDA DE USUARIO Y CONTRASEÑA
            //***************************
            //$user = $em->getRepository('SieAppWebBundle:Usuario')->findBy(array('username' => $form['_username'], 'password' => md5($form['_password']), 'esactivo' => 'true'));

            $user = $this->container->get('security.context')->getToken()->getUser();
            //dump($user);die();
            if ( $user and is_object($user) ) {//USUARIO Y CONTRASEÑA CORRECTAS

                // VERIFICAMOS SI EL USUARIO ES DE ALTERNATIVA Y ES UN CENTRO
                /*$arrauuseralt = array(4747180,466334,3063920);
                if($request->server->get('HTTP_HOST') == 'alternativa.sie.gob.bo' and !(in_array($user->getUsername(),$arrauuseralt) )){
                    $this->session->getFlashBag()->add('errorusuario', 'El sistema esta temporalmente fuera de servicio, por mantenimiento. Disculpe las molestias.');
                    return $this->redirectToRoute('login');
                }*/

                //*******SE VERIFICA SI SE TRATA DE RESETEO DE CONTRASEÑA
                $this->session->set('userId', $user->getId());
                
                if (md5($user->getUsername()) == $user->getPassword()) {
                    return $this->redirect($this->generateUrl('sie_usuarios_reset_login', array('usuarioid' => $user->getId())));
                }

                //*******************
                //BUSCANDO ROLES ACTIVOS Y UNIDADES O CENTROS DONDE ESTES COMO VIGENTES
                //dump($this->get('login'));die;
                $rolselected = $this->get('login')->verificarRolesActivos($user->getPersona()->getId(),'-1');

                $aPersona = $em->getRepository('SieAppWebBundle:Persona')->find($user->getPersona()->getId());
                $carnet = $aPersona->getCarnet().$aPersona->getComplemento();
                $this->session->set('lastname', $aPersona->getPaterno());
                $this->session->set('lastname2', $aPersona->getMaterno());
                $this->session->set('name', $aPersona->getNombre());
                $this->session->set('userName', $user->getUsername());
                $this->session->set('personaId', $aPersona->getId());
                $this->session->set('userfoto', $aPersona->getFoto());
                $this->session->set('userId2', $user->getId());
                $this->session->set('currentyear', date('Y'));
                
                if($this->session->get('pathSystem')=='SieUniversityBundle'){
                    $useruni_id = $this->session->get('userId');
                    $useruni_name = $this->session->get('userName');
                    
                    $estadoUsuarioRol = $this->getAccessUsuarioRol(array("usuarioId"=>$this->session->get('userId'),"rolId"=>20));                    
                    if ($estadoUsuarioRol){
                        $this->session->set('roluser', 20); // 20 es de consultas
                        return $this->redirect($this->generateUrl('sie_university_dashboard'));
                    } else {
                        return $this->redirect($this->generateUrl('sie_university_homepage'));
                    }
                    
                }
               
                if($this->session->get('pathSystem')=='SieTecnicaEstBundle'){
                    $estadoUsuarioRol = $this->getAccessUsuarioRol(array("usuarioId"=>$this->session->get('userId'),"rolId"=>20));
                    if ($estadoUsuarioRol){
                        return $this->redirect($this->generateUrl('sie_tecnicaest_dashboard'));
                    } else {
                        return $this->redirect($this->generateUrl('sie_tecnicaest_homepage'));
                    }
                    
                }
                //*************************
                //*************************
                //*****CONFIGURACIONES PARA OTROS SUBSISTEMAS
                //*****CONFIGURACIONES PARA OTROS SUBSISTEMAS
                if  ( ($this->session->get('pathSystem') === 'SieDiplomaBundle')
                   || ($this->session->get('pathSystem') === 'SieTramitesBundle')
                   || ($this->session->get('sysname') === 'REPORTES') ){
                        //review if the system is Diplomas
                        $aRoles = $this->getUserRoles($user->getId());
                        $this->myarrayRoles = $aRoles;

                        //**************
                        //**** SE REGISTRA LA SESSION DEL USUARIO
                        $sesionUsuario = new UsuarioSession();
                        $sesionUsuario->setUsuarioId($sesion->get('userId'));
                        $nombreUsuario = $sesion->get('name') . " " . $sesion->get('lastname') . " " . $sesion->get('lastname2');
                        $sesionUsuario->setNombre($nombreUsuario);
                        $sesionUsuario->setFecharegistro((new \DateTime('now'))->format('Y-m-d H:i:s'));
                        //dump((new \DateTime('now'))->format('Y-m-d H:i:s')); die;
                        $sesionUsuario->setUserName($sesion->get('userName'));
                        $sesionUsuario->setIp($_SERVER['REMOTE_ADDR']);
                        $sesionUsuario->setLogeoEstado('1');
                        $sesionUsuario->setObservaciones($_SERVER['HTTP_USER_AGENT']);
                        $sesionUsuario->setRolTipoId($aRoles[count($aRoles) -1]['id']);
                        $em->persist($sesionUsuario);
                        $em->flush();
                        //**** FIN DE SE REGISTRA LA SESSION DEL USUARIO
                        //************

                        if ($this->session->get('pathSystem') === 'SieDiplomaBundle') {
                            return $this->redirect($this->generateUrl('sie_diploma_homepage', array('name' => 'asd')));
                        }

                        if ($this->session->get('pathSystem') === 'SieTramitesBundle') {
                            return $this->redirect($this->generateUrl('tramite_homepage'));
                        }
                        //review if the system is Reportes
                        if ($this->session->get('sysname') === 'REPORTES') {
                            $this->session->set('roluser', $aRoles);

                            return $this->redirectToRoute('reporte_regular_index');

                            // $reporteController = new ReporteEstaditicoRegular();
                            // $reporteController->setContainer($this->container);
                            // $em = $this->getDoctrine()->getManager();
                            // /*
                            // * Define la zona horaria y halla la fecha actual
                            // */
                            // date_default_timezone_set('America/La_Paz');
                            // $fechaActual = new \DateTime(date('Y-m-d'));
                            // $gestionActual = date_format($fechaActual,'Y');
                            // $entidad = $reporteController->buscaEntidadRol(0,0);
                            // $subEntidades = $reporteController->buscaSubEntidadRol(0,0);
                            // $entityEstadistica = $reporteController->buscaEstadisticaAreaRol(0,0);
                            // //$entityEstadisticaUE = $reporteController->buscaEstadisticaUERol(0,0);
                            // //$entityEstadisticaEE = $reporteController->buscaEstadisticaEERol(0,0);
                            // $fechaEstadisticaRegular = $reporteController->buscaFechaVistaMaterializadaRegular($gestionActual);
                            // $chartMatricula = $reporteController->chartColumnInformacionGeneral($entityEstadistica,"Matrícula",$gestionActual,1,"chartContainerMatricula");
                            // $chartNivel = $reporteController->chartDonut3dInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Nivel de Estudio",$gestionActual,2,"chartContainerEfectivoNivel");
                            // $chartNivelGrado = $reporteController->chartDonutInformacionGeneralNivelGrado($entityEstadistica,"Estudiantes Matriculados según Nivel de Estudio y Año de Escolaridad ",$gestionActual,6,"chartContainerEfectivoNivelGrado");
                            // $chartGenero = $reporteController->chartPieInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Sexo",$gestionActual,3,"chartContainerEfectivoGenero");
                            // $chartArea = $reporteController->chartPyramidInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Área Geográfica",$gestionActual,4,"chartContainerEfectivoArea");
                            // $chartDependencia = $reporteController->chartColumnInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Dependencia",$gestionActual,5,"chartContainerEfectivoDependencia");
                            // return $this->render($this->session->get('pathSystem') . ':Reporte:matriculaEducativaRegular.html.twig', array(
                            //     'infoEntidad'=>$entidad,
                            //     'infoSubEntidad'=>$subEntidades,
                            //     'infoEstadistica'=>$entityEstadistica,
                            //     //'infoEstadisticaUE'=>$entityEstadisticaUE,
                            //     //'infoEstadisticaEE'=>$entityEstadisticaEE,
                            //     'rol'=>0,
                            //     'datoGraficoMatricula'=>$chartMatricula,
                            //     'datoGraficoNivel'=>$chartNivel,
                            //     'datoGraficoNivelGrado'=>$chartNivelGrado,
                            //     'datoGraficoGenero'=>$chartGenero,
                            //     'datoGraficoArea'=>$chartArea,
                            //     'datoGraficoDependencia'=>$chartDependencia,
                            //     'mensaje'=>'$("#modal-bootstrap-tour").modal("show");',
                            //     'gestion'=>$gestionActual,
                            //     'fechaEstadisticaRegular'=>$fechaEstadisticaRegular,
                            //     'form' => $this->createLoginForm()->createView()
                            // ));
                        }
                        //review if the system is Diplomas
                        if ($this->session->get('pathSystem') === 'SieTramitesBundle') {
                            if (sizeof($aRoles) == 1) {
                                //$this->session->getFlashBag()->add('info', 'Datos Correctos...');
                                $this->session->set('roluser', $aRoles[0]['id']);
                                $this->session->set('roluserlugarid', $aRoles[0]['rollugarid']);
                                return $this->redirect($this->generateUrl('tramite_homepage'));
                            }
                            $this->myarrayRoles = $aRoles;
                            if (sizeof($aRoles) > 1) {
                                $formUserRol = $this->createFormBuilder($user)
                                        ->setAction($this->generateUrl('tramite_homepage'))
                                        ->add('username', 'text', array('mapped' => true, 'disabled' => true, 'data' => $this->session->get('userName')))
                                        ->add('name', 'text', array('mapped' => false, 'disabled' => true, 'data' => $aPersona->getNombre()))
                                        ->add('lastName', 'text', array('mapped' => false, 'disabled' => true, 'data' => $aPersona->getPaterno()))
                                        ->add('id', 'hidden', array('mapped' => true))
                                        ->add('roluser', 'entity', array('empty_value' => 'Seleccione Rol con el que desea entrar', 'mapped' => false,  'class' => 'SieAppWebBundle:RolTipo',
                                            'query_builder' => function(EntityRepository $e) {
                                                return $e->createQueryBuilder('rt')
                                                        ->where('rt.id IN (:myarray)')
                                                        ->setParameter('myarray', $this->myarrayRoles)
                                                        ->orderBy('rt.rol', 'ASC');
                                            }, 'property' => 'rol'))
                                        ->add('goin', 'submit', array('label' => 'Continuar'))
                                        ->getForm();
                                return $this->render($this->session->get('pathSystem') . 'SieAppWebBundle:Default:roles.html.twig', array('user' => $user, 'roles' => $aRoles, 'persona' => $aPersona, 'form' => $formUserRol->createView()));
                            }
                        }
                        //*****FIN DE CONFIGURACIONES PARA OTROS SUBSISTEMAS
                        //*****FIN DE CONFIGURACIONES PARA OTROS SUBSISTEMAS
                        //***********************
                        //***********************
                }else{
                    //*******************
                    //MENSAJE DE QUE CORRECCION EN CASO DE QUE USUARIO NO COINCIDA CON CARNET
                    $carnetban = 'null';
                    $personausuarios = $em->getRepository('SieAppWebBundle:Usuario')->findByUsername($carnet);
                    if ($this->session->get('userName') <> $carnet) {
                        if (sizeof($personausuarios) == 0) {
                            $this->session->getFlashBag()->add('errorusername', 'La omisión reiterada a esta observación derivara en la ');
                        }
                        if (sizeof($personausuarios) > 0) {
                            $this->session->getFlashBag()->add('errorusernameexistente', 'Y se le comunica que ya existe el usuario :'.$carnet.' asignada a otra persona. Debe corregir esta observación con urgencia con su técnico SIE para precautelar su responsabilidad en la información asignada a su persona.');
                        }
                        $carnetban = 'true';
                    }
                    //dump($carnet);dump($this->session->get('userName'));die;

                    //*******************
                    //MENSAJE DE RESETEO DE CONTRASEÑA CANTIDAD DE DIAS DESDE EL ULTIMO CAMBIO DE CONTRASEÑA
                    $exp = 'null';
                    $dateObject = $user->getFechaRegistro();
                    $date = $dateObject->format('Y-m-d');
                    $datetime1 = new \DateTime($date);
                    $datetime2 = new \DateTime("today");
                    $interval = $datetime1->diff($datetime2);
                    $dias = $interval->format('%a');
                    if (intval($dias) > 90) {
                        $this->session->getFlashBag()->add('errorcontraexp', 'La omisión reiterada a esta observación derivara en la ');
                        $exp = 'true';
                    }
                    //dump($exp);die;

                    //*******************
                    //MENSAJE DIRIGIDO AL USUARIO
                    $mendir = 'null';
                    /*$mensajedirecto = $em->getRepository('SieAppWebBundle:NotificacionUsuario')->findOneBy(array('usuario' => $this->session->get('userId'), 'notif' => '5713980' ) );

                    if (sizeof($mensajedirecto) > 0) {
                        //CORRECCCION DE MENSAJE
                        $mensaje = $em->getRepository('SieAppWebBundle:Notificacion')->find($mensajedirecto->getNotif() );
                        $this->session->getFlashBag()->add('mensajedircod4', $mensaje->getMensaje());
                        $mendir = 'true';
                    }*/
                    //dump($mensaje->getMensaje());die;

                    $sistema = $this->session->get('pathSystem');

                    switch ($sistema) {
                        case 'SieRegularBundle':
                            $this->session->set('sysname', 'SISTEMA SIGED');
                            $this->session->set('sysporlet', '#0101DF');                            
                            break;
                        case 'SieHerramientaBundle':
                            $this->session->set('sysname', 'SISTEMA ACADÉMICO EDUCACIÓN REGULAR');
                            $this->session->set('sysporlet', '#0101DF');                            
                            break;
                        case 'SieHerramientaAlternativaBundle':
                            $this->session->set('sysname', 'SISTEMA ACADÉMICO EDUCACIÓN ALTERNATIVA');
                            $this->session->set('sysporlet', '#0101DF');                            
                            break;
                        case 'SieRueBundle':
                            $this->session->set('sysname', 'RUE');
                            $this->session->set('sysporlet', '#0101DF');                            
                            break;
                        case 'SieDiplomaBundle':
                            $this->session->set('sysname', 'DIPLOMAS');
                            $this->session->set('sysporlet', '#0101DF');                            
                            break;
                        case 'SieTramitesBundle':
                            $this->session->set('sysname', 'TRAMITES');
                            $this->session->set('sysporlet', '#0101DF');                            
                            break;
                        case 'SieJuegosBundle':
                            $this->session->set('sysname', 'JUEGOS');
                            $this->session->set('sysporlet', '#0101DF');                            
                            break;
                        case 'SieEspecialBundle':
                            $this->session->set('sysname', 'SISTEMA ACADÉMICO EDUCACIÓN ESPECIAL');
                            $this->session->set('sysporlet', '#0101DF');                            
                            break;
                        case 'SieDgesttlaBundle':
                            $this->session->set('sysname', 'FORMACIÓN TECNICA Y TECNOLOGICA');
                            $this->session->set('sysporlet', '#0101DF');                            
                            break;
                        case 'SieInfraestructuraBundle':
                            $this->session->set('sysname', 'INFRAESTRUCTURA');
                            $this->session->set('sysporlet', '#0101DF');                            
                            break;
                        case 'SieBjpBundle':
                            $this->session->set('sysname', 'BONO JUANCITO PINTO');
                            $this->session->set('sysporlet', '#0101DF');                            
                            break;
                        case 'SieGisBundle':
                            $this->session->set('sysname', 'GIS');
                            $this->session->set('sysporlet', '#0101DF');                            
                            break;
                        case 'SieOlimpiadasBundle':
                            $this->session->set('sysname', 'SISTEMA OLIMPIADAS CIENTÍFICAS');
                            $this->session->set('sysporlet', '#0101DF');                            
                            break;
                        case 'SiePermanenteBundle':
                            $this->session->set('sysname', 'SISTEMA ACADÉMICO EDUCACIÓN PERMANENTE');
                            $this->session->set('sysporlet', '#0101DF');                            
                            break;    
                        default:
                            $this->session->set('sysname', 'SISTEMA SIGED');
                            $this->session->set('sysporlet', '#0101DF');                            
                            break;
                    }

                    if (($exp == 'true') || ($carnetban == 'true') || ($mendir == 'true')){
                        //dump($rolselected);die;
                        return $this->render('SieAppWebBundle:Login:rolesunidades.html.twig',
                        array(
                            'titulosubsistema' => $this->session->get('sysname'),
                            'color' => $this->session->get('sysporlet'),
                            'user' => $this->session->get('userName'),
                            'carnet' => $carnet,
                            'roles' => $rolselected,
                            'persona' => $this->session->get('name').' '.$this->session->get('lastname')
                        ));
                    }
                    //dump($rolselected);die;
                    //*******************
                    //CUANDO EL USUARIO SOLO TIENE UN ROL ACTIVO
                    //SE ENVIA AL CONTROLADOR LOGIN PARA ULTIMAS VERIFICACIONES
                    $sesion->set('directorAlternativa', false);
                    //dump($rolselected);die;
                    if (count($rolselected) == 1) {
                        if ( ($rolselected[0]['id'] == 2) || ($rolselected[0]['id'] == 9) ){
                            $this->session->set('roluser', $rolselected[0]['id']);
                            $this->session->set('roluserlugarid', $rolselected[0]['rollugarid']);
                            $this->session->set('ie_id', $rolselected[0]['sie']);
                            $this->session->set('ie_per_estado', '-1');
                            $this->session->set('ie_nombre', $rolselected[0]['institucioneducativa']);
                            $this->session->set('cuentauser', $rolselected[0]['rol']);
                            $this->session->set('tiposubsistema', $rolselected[0]['idietipo']);
                             //to show the option create rude on alternativa by krlos
                                if($rolselected[0]['id'] == 9){
                                    $objInstitucioneducativaAlt = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array(
                                    'id'=> $rolselected[0]['sie'],
                                    'institucioneducativaTipo'=>2
                                        ));
                                    if($objInstitucioneducativaAlt && $rolselected[0]['sie']!='80730796'){

                                        $sesion->set('directorAlternativa', true);
                                    }

                                }
                        }else{
                            $this->session->set('roluser', $rolselected[0]['id']);
                            $this->session->set('roluserlugarid', $rolselected[0]['rollugarid']);
                            $this->session->set('ie_id', '-1');
                            $this->session->set('ie_per_estado', '-1');
                            $this->session->set('ie_nombre', 'Seleccionar CEA');
                            $this->session->set('cuentauser', $rolselected[0]['rol']);
                            $this->session->set('tiposubsistema', $rolselected[0]['idietipo']);
                        }
                        
                        return $this->redirect($this->generateUrl('sie_login_homepage'));
                    }
                    //FIN DE CUANDO EL USUARIO SOLO TIENE UN ROL ACTIVO
                    //*******************

                    //*************************
                    //CUANDO EL USUARIO TIENEN VARIOS ROLES ACTIVOS
                    if (count($rolselected) > 1) {
                        return $this->render('SieAppWebBundle:Login:rolesunidades.html.twig',
                        array(
                            'titulosubsistema' => $this->session->get('sysname'),
                            'color' => $this->session->get('sysporlet'),
                            'user' => $this->session->get('userName'),
                            'carnet' => $carnet,
                            'roles' => $rolselected,
                            'persona' => $this->session->get('name').' '.$this->session->get('lastname')
                        ));
                    }
                    //FIN DE CUANDO EL USUARIO TIENEN VARIOS ROLES ACTIVOS
                    //*************************

                    //*************************
                    //CUANDO EL USUARIO NO TIENEN ROLES ACTIVOS
                    //dump(count($rolselected));die();
                    if (count($rolselected) === 0) {
                        $this->session->getFlashBag()->add('error', '¡Usted no cuenta registro vigente en la presente gestión! Consulte con su técnico SIE en el módulo Gestión Administrativos.');

                        return $this->render('SieAppWebBundle:Login:rolesunidades.html.twig',
                        array(
                            'titulosubsistema' => $this->session->get('sysname'),
                            'color' => $this->session->get('sysporlet'),
                            'user' => $this->session->get('userName'),
                            'carnet' => $carnet,
                            'roles' => $rolselected,
                            'persona' => $this->session->get('name').' '.$this->session->get('lastname')
                        ));
                    }
                    //FIN DE CUANDO EL USUARIO NO TIENEN ROLES ACTIVOS
                    //*************************
                }
            } else {///****** EN CASO DE QUE NO EXISTA EL USUARIO
                return $this->redirect($this->generateURL('logout'));
            }
        } catch (Exception $e) {

        }
    }

    public function sigedrolselectAction(Request $request, $key) {
        $em =  $this->getDoctrine()->getManager();
        $sesion = $request->getSession();
        //$id_usuario = $this->sesion->get('userId');
        //dump($sesion);die;
        if (!$sesion->get('personaId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        $rolselected = $this->get('login')->verificarRolesActivos($sesion->get('personaId'),$key);
        //dump($rolselected);
        //die;
        //to show the option create rude on alternativa
        $sesion->set('directorAlternativa', false);
        if (sizeof($rolselected) == 1) {
            if ( ($rolselected[0]['id'] == 2) || ($rolselected[0]['id'] == 9) ){
                $sesion->set('roluser', $rolselected[0]['id']);
                $sesion->set('roluserlugarid', $rolselected[0]['rollugarid']);
                $sesion->set('ie_id', $rolselected[0]['sie']);
                $sesion->set('ie_per_estado', '-1');
                $sesion->set('ie_nombre', $rolselected[0]['institucioneducativa']);
                $sesion->set('cuentauser', $rolselected[0]['rol']);
                $sesion->set('tiposubsistema', $rolselected[0]['idietipo']);
                // dump($rolselected);die;
                //to show the option create rude on alternativa by krlos
                if($rolselected[0]['id'] == 9){
                    $objInstitucioneducativaAlt = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array(
                    'id'=> $rolselected[0]['sie'],
                    'institucioneducativaTipo'=>2
                        ));
                    if($objInstitucioneducativaAlt && $rolselected[0]['sie']!='80730796'){
                        $sesion->set('directorAlternativa', true);
                    }

                }

            }else{
                $sesion->set('roluser', $rolselected[0]['id']);
                $sesion->set('roluserlugarid', $rolselected[0]['rollugarid']);
                $sesion->set('ie_id', '-1');
                $sesion->set('ie_per_estado', '-1');
                $sesion->set('ie_nombre', 'Seleccionar CEA');
                $sesion->set('cuentauser', $rolselected[0]['rol']);
                $sesion->set('tiposubsistema', $rolselected[0]['idietipo']);
            }
            return $this->redirect($this->generateUrl('sie_login_homepage'));
        } else{
            $sesion->getFlashBag()->add('info', '¡Se ha detectado error codigo 002! comuniquese con el Siged - Nacional.');
            return $this->redirect($this->generateURL('logout'));
        }
    }

    /**
     * create the login form to SIGED system
     * @return object form
     */
    public function createLoginForm() {
        $usuario = new Usuario();
        return $this->createFormBuilder($usuario)
                        ->setAction($this->generateUrl('login'))
                        ->add('_username', 'text', array('mapped' => false, 'required' => true, 'invalid_message' => 'Campo 1 obligatorio'))
                        ->add('_password', 'password', array('mapped' => false, 'required' => true, 'invalid_message' => 'Campo 2 obligatorio'))
                        ->add('check', 'checkbox', array('mapped' => false, 'label' => 'Omitir esta validación', 'required' => false))
                        ->add('captcha', 'text', array('mapped' => false, 'required' => true, 'invalid_message' => 'Campor 2 obligatorio'))
                        ->add('save', 'submit', array('label' => 'Aceptar'))
                        ->getForm();
    }

    /**
     * Creates a form to search the users of student selected
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchFormInstitucioneducativa() {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('consulta_rue_result'))
                ->add('tipo_search', 'hidden', array('data' => 'institucioneducativa'))
                ->add('institucioneducativa', 'text', array('required' => true, 'invalid_message' => 'Campo obligatorio'))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    private function createSearchFormInstitucioneducativaId() {

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('consulta_rue_result'))
                ->add('tipo_search', 'hidden', array('data' => 'institucioneducativaId'))
                ->add('institucioneducativaId', 'text', array('required' => true, 'invalid_message' => 'Campo obligatorio', 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
                ->add('buscarId', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    private function createSearchFormInstitucioneducativaTipo() {
        $em = $this->getDoctrine()->getManager();

        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' => 1));
        //      dump($dep);die;
        $depArray = array();
        foreach ($dep as $de) {
            $depArray[$de->getId()] = $de->getLugar();
        }
        $depArray[1] = 'Todos';
        ksort($depArray);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('consulta_rue_result'))
                ->add('tipo_search', 'hidden', array('data' => 'institucioneducativaTipo'))
                ->add('departamento', 'choice', array('label' => 'Departamento', 'required' => true, 'choices' => $depArray, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                ->add('institucioneducativaTipo', 'entity', array('label' => 'Tipo', 'required' => true, 'class' => 'SieAppWebBundle:InstitucioneducativaTipo',
                    'query_builder' => function(EntityRepository $e) {
                        return $e->createQueryBuilder('iet')
                                ->where('iet.id in (:ids)')
                                ->setParameter('ids', array(1, 2, 4, 5))
                        ;
                    },
                            'property' => 'descripcion', 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control')))
                        ->add('buscarTipo', 'submit', array('label' => 'Buscar'))
                        ->getForm();
                return $form;
            }

            /**
             * get the roles of user- obtenemos datos de roles del user
             * parameters: codigo user
             * @author krlos
             */
            function getUserRoles($id) {
                $em = $this->getDoctrine()->getManager();
                $entity = $em->getRepository('SieAppWebBundle:Usuario');
                $query = $entity->createQueryBuilder('u')
                        ->select('rt.id, rt.rol, lt.id as rollugarid')
                        ->leftJoin('SieAppWebBundle:UsuarioRol', 'ur', 'WITH', 'u.id=ur.usuario')
                        ->leftJoin('SieAppWebBundle:RolTipo', 'rt', 'WITH', 'ur.rolTipo=rt.id')
                        ->innerJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'ur.lugarTipo=lt.id')
                        ->where('u.id = :id')
                        ->andwhere('ur.esactivo = true')
                        ->setParameter('id', $id)
                        ->getQuery();
                try {
                    return $query->getResult();
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    return array();
                }
            }

            function getPersonaVarioUsuarios($username) {
                $em = $this->getDoctrine()->getManager();
                $db = $em->getConnection();
                //*************
                //BUSCA EL ID PERSONA ANTIGUO
                $query = "
                       select persona_id
                            from  __temporal.usuario1172017
                            where username = '".$username."'";
                $stmt = $db->prepare($query);
                $params = array();
                $stmt->execute($params);
                $poaux = $stmt->fetchAll();
                if (count($poaux) > 0){
                    //****COMPRUEBA SI LA PERSONA TIENE VIGENTES COMO DIRECTOR O DOCENTE Y MAS DE UN USUARIOS
                    $query = "
                            select zzz.persona_id from (
                            select * from (
                            select persona_id, count(username) as count
                            from  __temporal.usuario1172017
                            where persona_id = '".$poaux['0']['persona_id']."'
                            group by persona_id
                            order by count) abc
                            where abc.count > 1 ) xxx
                            inner join public.maestro_inscripcion zzz on xxx.persona_id = zzz.persona_id
                            where zzz.cargo_tipo_id in ('0','1','12')
                            and zzz.gestion_tipo_id = 2017
                            and zzz.es_vigente_administrativo is true
                            group by zzz.persona_id
                            order by zzz.persona_id
                            ";
                    $stmt = $db->prepare($query);
                    $params = array();
                    $stmt->execute($params);
                    $po = $stmt->fetchAll();
                    //dump($po); die;
                    if (count($po) > 0){
                        return $poaux['0']['persona_id'];
                    }else{
                        return '0';
                    }
                }else{
                    return '0';
                }
            }


    public function setLogTransaccion($key,$tabla,$tipoTransaccion,$ip,$usuarioId,$observacion,$valorNuevo,$valorAnt,$sistema,$archivo){
        //try {
            $em = $this->getDoctrine()->getManager();
            $newLogTransaccion = new LogTransaccion();
            $newLogTransaccion->setKey($key);
            $newLogTransaccion->setTabla($tabla);
            $newLogTransaccion->setFecha(new \DateTime('now'));
            $newLogTransaccion->setTipoTransaccion($tipoTransaccion);
            $newLogTransaccion->setIp($ip);
            $newLogTransaccion->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($usuarioId));
            $newLogTransaccion->setObservacion($observacion);
            $newLogTransaccion->setValorNuevo($valorNuevo);
            $newLogTransaccion->setValorAnt($valorAnt);
            $newLogTransaccion->setSistema($sistema);
            $newLogTransaccion->setArchivo($archivo);
            $em->persist($newLogTransaccion);
            $em->flush();

            return $newLogTransaccion;

       /* } catch (Exception $e) {

        }*/
    }

    public function getAccessMenuCtrl($data){

      //ini DB conexxion
      $em = $this->getDoctrine()->getManager();

      $operativo = $this->get('funciones')->obtenerOperativo($data['sie'],$data['gestion']);
// dump($operativo);die;
      //set the flag to do the inscription
      $swDoInscription = false;
      $objCtrlOpeMenu = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus')->findOneBy(array(
        'institucioneducativa' => $data['sie'],
        'gestionTipoId' => $data['gestion'],
        'notaTipo' => $operativo+1,
      ));

      if($objCtrlOpeMenu && $objCtrlOpeMenu->getEstadoMenu()){
        $swDoInscription = true;
      }
      //return the correct value before the validation
      return $swDoInscription;

    }

    public function getAccessUsuarioRol($data){

        //ini DB conexxion
        $em = $this->getDoctrine()->getManager();
  
        $operativo = $this->get('funciones')->obtenerOperativo($data['usuarioId'],$data['rolId']);
  // dump($operativo);die;
        //set the flag to do the inscription
        $estado = false;
        $objUsuarioRol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array(
          'usuario' => $data['usuarioId'],
          'rolTipo' => $data['rolId'],
          'esactivo' => true,
        ));
  
        if($objUsuarioRol && $objUsuarioRol->getId()){
          $estado = true;
        }
        //return the correct value before the validation
        return $estado;
  
      }
}
