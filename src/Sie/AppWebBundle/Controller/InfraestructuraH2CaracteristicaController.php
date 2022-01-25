<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH2Caracteristica;
use Sie\AppWebBundle\Form\InfraestructuraH2CaracteristicaType;
use Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificados;
use Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificadosPisos;
use Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificacionSenalesTipo;
use Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificacionRampasTipo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Form\InfraestructuraH2CaracteristicaEdificadosPisosType;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * InfraestructuraH2Caracteristica controller.
 *
 */
class InfraestructuraH2CaracteristicaController extends Controller
{
    public $session;

    public function __construct(){
        $this->session = new Session();
    }

    /**
     * Lists all InfraestructuraH2Caracteristica entities.
     *
     */
    public function indexAction(Request $request)
    {

        $this->session->set('infjgid', 13395);

        if($this->session->get('infjgid') == null){
            return $this->redirectToRoute('logout');
        }

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH2Caracteristica')->findOneBy(
            array(
                'infraestructuraJuridiccionGeografica'=>$this->session->get('infjgid')
            )
        );

        if(!is_object($entity)){
            $entity = new InfraestructuraH2Caracteristica();
            $this->session->set('infh2id', 'new');

        }else{
            $this->session->set('infh2id', $entity->getId());
        }

        $form = $this->createForm(new InfraestructuraH2CaracteristicaType(), $entity);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->getDoctrine()->getManager()->flush();

            $em = $this->getDoctrine()->getManager();
            /*
            // Eliminar tipos de señales
            $senalesAnteriores = $em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificacionSenalesTipo')->findBy(array('infraestructuraH2Caracteristica'=>$infraestructuraH2Caracteristica->getId()));

            foreach ($senalesAnteriores as $sa) {
                $em->remove($sa);
                $em->flush();
            }

            // Registro de tipos de señales
            $senales = $request->get('sie_appwebbundle_infraestructurah2caracteristica')['senalesTipo'];

            for ($i=0; $i < count($senales); $i++) { 
                //$em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h2_caracteristica_edificacion_senales_tipo');")->execute();
                $senalesTipo = new InfraestructuraH2CaracteristicaEdificacionSenalesTipo();
                $senalesTipo->setInfraestructuraH2Caracteristica($infraestructuraH2Caracteristica);
                $senalesTipo->setN213TipoSenales($em->getRepository('SieAppWebBundle:InfraestructuraH2SenalesTipo')->find($senales[$i]));
                /*if($senales[$i] == 7){
                    $senalesTipo->setN213TipoSenalesIdioma1($em->getRepository('SieAppWebBundle:InfraestructuraH2SenalesIdiomaTipo')->find(1));
                }
                $em->persist($senalesTipo);
                $em->flush();
            }*/
            /*
            // Eliminar tipos de rampas
            $rampasAnteriores = $em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificacionRampasTipo')->findBy(array('infraestructuraH2Caracteristica'=>$infraestructuraH2Caracteristica->getId()));

            foreach ($rampasAnteriores as $ra) {
                $em->remove($ra);
                $em->flush();
            }

            // Registro de tipos de rampas
            $rampas = $request->get('sie_appwebbundle_infraestructurah2caracteristica')['rampasTipo'];

            for ($i=0; $i < count($rampas); $i++) { 
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h2_caracteristica_edificacion_rampas_tipo');")->execute();
                $rampasTipo = new InfraestructuraH2CaracteristicaEdificacionRampasTipo();
                $rampasTipo->setInfraestructuraH2Caracteristica($infraestructuraH2Caracteristica);
                $rampasTipo->setN213TipoSenales($em->getRepository('SieAppWebBundle:InfraestructuraH2SenalesTipo')->find($senales[$i]));
                /*if($senales[$i] == 7){
                    $rampasTipo->setN213TipoSenalesIdioma1($em->getRepository('SieAppWebBundle:InfraestructuraH2SenalesIdiomaTipo')->find(1));
                }
                $em->persist($rampasTipo);
                $em->flush();
            }*/

            $infrah2id = $entity->getId(); //$this->session->get('infh2id');

            // Eliminacion de pisos y bloques
            $bloques = $em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificados')->findBy(array('infraestructuraH2Caracteristica'=>$infrah2id));
            if(count($bloques)>0){
                foreach ($bloques as $b) {
                    $pisos = $em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificadosPisos')->findBy(array('infraestructuraH2CaracteristicaEdificados'=>$b->getId()));
                    if(count($pisos)>0){
                        foreach ($pisos as $p) {
                            $em->remove($p);
                            $em->flush();
                        }

                    }
                    $em->remove($b);
                    $em->flush();
                }
            }

            // Registro de bloques
            $bloqueId = $request->get('bloqueId');
            $bloqueNro = $request->get('bloqueNro');
            $bloqueAscensor = $request->get('bloqueAscensor');
            $bloqueFotografia = $request->get('bloqueFotografia');

            for ($i=0; $i < count($bloqueId); $i++) {
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h2_caracteristica_edificados');")->execute();
                $bloque = new InfraestructuraH2CaracteristicaEdificados();
                $bloque->setInfraestructuraH2Caracteristica($em->getRepository('SieAppWebBundle:InfraestructuraH2Caracteristica')->find($infrah2id));
                $bloque->setN31NombreBloque($bloqueNro[$i]);
                $bloque->setN32SiTieneAscensores($bloqueAscensor[$i]);
                $em->persist($bloque);
                $em->flush();
            }

            // Registro de pisos
            $pisoId = $request->get('pisoId');
            $pisoBloque = $request->get('pisoBloque');
            $pisoNroPiso = $request->get('pisoNroPiso');
            $pisoArea = $request->get('pisoArea');
            $pisoNroPedagogicos = $request->get('pisoNroPedagogicos');
            $pisoNroNoPedagogicos = $request->get('pisoNroNoPedagogicos');
            $pisoNroBanios = $request->get('pisoNroBanios');
            $pisoTotal = $request->get('pisoTotal');
            $pisoCielo = $request->get('pisoCielo');
            $pisoCieloCaracteristica = $request->get('pisoCieloCaracteristica');
            $pisoPuerta = $request->get('pisoPuerta');
            $pisoPuertaSeguro = $request->get('pisoPuertaSeguro');
            $pisoPuertaAbre = $request->get('pisoPuertaAbre');
            $pisoVentana = $request->get('pisoVentana');
            $pisoVentanaCaracteristica = $request->get('pisoVentanaCaracteristica');
            $pisoTecho = $request->get('pisoTecho');
            $pisoMuro = $request->get('pisoMuro');
            $pisoMuroMaterial = $request->get('pisoMuroMaterial');
            $pisoMuroCaracteristica = $request->get('pisoMuroCaracteristica');
            $pisoRevestimiento = $request->get('pisoRevestimiento');
            $pisoRevestimientoMaterial = $request->get('pisoRevestimientoMaterial');
            $pisoRevestimientoCaracteristica = $request->get('pisoRevestimientoCaracteristica');
            $pisoPiso = $request->get('pisoPiso');
            $pisoPisoMaterial = $request->get('pisoPisoMaterial');
            $pisoPisoCaracteristica = $request->get('pisoPisoCaracteristica');
            $pisoGradas = $request->get('pisoGradas');
            $pisoGradasCuentanPasamano = $request->get('pisoGradasCuentanPasamano');
            $pisoGradasCuentanGuiaPrevencion = $request->get('pisoGradasCuentanGuiaPrevencion');
            $pisoRampas = $request->get('pisoRampas');
            $pisoRampasCuentan = $request->get('pisoRampasCuentan');
            $pisoSenales = $request->get('pisoSenales');
            $pisoSenalesTipoOrientadoras = $request->get('pisoSenalesTipoOrientadoras');
            $pisoSenalesTipoAudibles = $request->get('pisoSenalesTipoAudibles');
            $pisoSenalesTipoVisuales = $request->get('pisoSenalesTipoVisuales');
            $pisoSenalesTipoTactiles = $request->get('pisoSenalesTipoTactiles');
            $pisoSenalesTipoDirecciones = $request->get('pisoSenalesTipoDirecciones');
            $pisoSenalesTipoUbicacion = $request->get('pisoSenalesTipoUbicacion');
            $pisoSenalesTipoInformativas = $request->get('pisoSenalesTipoInformativas');
            $pisoSenalesIdioma1 = $request->get('pisoSenalesIdioma1');
            $pisoSenalesIdioma2 = $request->get('pisoSenalesIdioma2');

            for ($i=0; $i < count($pisoId) ; $i++) { 
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h2_caracteristica_edificados_pisos');")->execute();
                $piso = new InfraestructuraH2CaracteristicaEdificadosPisos();

                $bloque = $em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificados')->findOneBy(array(
                    'n31NombreBloque'=>$pisoBloque[$i],
                    'infraestructuraH2Caracteristica'=>$infrah2id
                ));
                $piso->setInfraestructuraH2CaracteristicaEdificados($bloque);

                $piso->setN11NroPisoTipo($em->getRepository('SieAppWebBundle:InfraestructuraH2PisoNroPisoTipo')->find($pisoNroPiso[$i]));
                $piso->setN12AreaM2((int) $pisoArea[$i]);
                $piso->setN13NroAmbPedagogicos((int) $pisoNroPedagogicos[$i]);
                $piso->setN14NroAmbNoPedagogicos((int) $pisoNroNoPedagogicos[$i]);
                $piso->setN15TotalBanios((int) $pisoNroBanios[$i]);
                $piso->setN16TotalAmbientes((int) $pisoTotal[$i]);
                $piso->setN21SiCieloFalso((int) $pisoCielo[$i]);
                if($pisoCieloCaracteristica[$i] != ""){
                    $piso->setN211CaracteristicasTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($pisoCieloCaracteristica[$i]));
                }
                $piso->setN22SiPuertas($pisoPuerta[$i]);
                if($pisoPuertaSeguro[$i] != ""){
                    $piso->setN221SeguroTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasSeguroTipo')->find($pisoPuertaSeguro[$i]));
                }
                if($pisoPuertaAbre[$i] != ""){
                    $piso->setN222AbreTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasAbreTipo')->find($pisoPuertaAbre[$i]));
                }
                $piso->setN23SiVentanas($pisoVentana[$i]);
                if($pisoVentanaCaracteristica[$i] != ""){
                    $piso->setN231VidriosTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenVentanasCaracTipo')->find($pisoVentanaCaracteristica[$i]));
                }
                $piso->setN24SiTecho($pisoTecho[$i]);
                $piso->setN25SiMuros($pisoMuro[$i]);
                if($pisoMuroMaterial[$i] != ""){
                    $piso->setN251MurosMaterialTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosMaterialTipo')->find($pisoMuroMaterial[$i]));
                }
                if($pisoMuroCaracteristica[$i] != ""){
                    $piso->setN252MurosCaracteristicasTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosCaracTipo')->find($pisoMuroCaracteristica[$i]));
                }
                $piso->setN26SiRevestimiento($pisoRevestimiento[$i]);
                if($pisoRevestimientoMaterial[$i] != ""){
                    $piso->setN261RevestMaterialTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenRevestimientoMaterialTipo')->find($pisoRevestimientoMaterial[$i]));
                }
                if($pisoRevestimientoCaracteristica[$i] != ""){
                    $piso->setN262RevestCaracteristicasTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($pisoRevestimientoCaracteristica[$i]));
                }
                $piso->setN27SiPiso($pisoPiso[$i]);
                if($pisoPisoMaterial[$i] != ""){
                    $piso->setN271PisoMaterialTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPisosMaterialTipo')->find($pisoPisoMaterial[$i]));
                }
                if($pisoPisoCaracteristica[$i] != ""){
                    $piso->setN272PisoCaracteristicasTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($pisoPisoCaracteristica[$i]));
                }
                $piso->setN31SiGradas($pisoGradas[$i]);
                $piso->setN33SiRampas($pisoRampas[$i]);
                $piso->setN35SiSenaletica($pisoSenales[$i]);


                $em->persist($piso);
                $em->flush();

            }

            
        }

        return $this->render('SieAppWebBundle:InfraestructuraH2Caracteristica:new.html.twig', array(
            'form'=>$form->createView()
        ));

    }
    

    /**
     * Funciones ajax para bloques
     */
    
    public function getBloqueAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');

        // Editar
        $bloqueEdificado = $em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificados')->find($id);

        return $this->get('funciones')->json($bloqueEdificado);

    }

    

    public function getBloquesAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $id = $this->session->get('infh2id');

        // Editar
        $bloquesEdificados = $em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificados')->findBy(
            array('infraestructuraH2Caracteristica' => $id),
            array('n31NombreBloque'=>'ASC')
        );

        $bloqueIds = null;

        if($bloquesEdificados){
            $bloques = array();
            foreach ($bloquesEdificados as $be) {
                $bloques[] = array(
                    'id'=>$be->getId(),
                    'n31NombreBloque'=>$be->getN31NombreBloque(),
                    'n32SiTieneAscensores'=>($be->getN32SiTieneAscensores())?$be->getN32SiTieneAscensores():'',
                    'n33AdjuntarFotoBloque'=>($be->getN33AdjuntarFotoBloque())?$be->getN33AdjuntarFotoBloque():''
                );

                $bloqueIds[] = $be->getId();
            }

            $bloquesEdificados = $bloques;
            
        }else{
            $bloquesEdificados = 1;
        }

        // Pisos
        $pisos = array();
        if($bloqueIds != null){
            $pisos = $em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificadosPisos')->findBy(array(
                'infraestructuraH2CaracteristicaEdificados'=>$bloqueIds
            ));
        }

        $data = array(
            'items'=>$bloquesEdificados,
            'pisos'=>$pisos
        );

        return $this->get('funciones')->json($data);

    }

    public function createBloqueAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $nro = $request->get('nro');
        $ascensor = $request->get('ascensor');

        if($id == 'new'){
            /**
             * Nuevo registro de bloque
             */
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h2_caracteristica_edificados');")->execute();

            $infrah2caracteristica = $em->getRepository('SieAppWebBundle:InfraestructuraH2Caracteristica')->find($this->session->get('infh2id'));

            $bloque = new InfraestructuraH2CaracteristicaEdificados();
            $bloque->setInfraestructuraH2Caracteristica($infrah2caracteristica);
        }else{
            /**
             * Editar regsitro de bloque
             */
            $bloque = $em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificados')->find($id);
        }
        $bloque->setN31NombreBloque($nro);
        $bloque->setN32SiTieneAscensores($ascensor);
        $em->persist($bloque);
        $em->flush();

        return $this->get('funciones')->json($bloque);
    }

    public function updateBloqueAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $nro = $request->get('nro');
        $ascensor = $request->get('ascensor');
        /**
         * Editar regsitro de bloque
         */
        $bloque = $em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificados')->find($id);
        $bloque->setN31NombreBloque($nro);
        $bloque->setN32SiTieneAscensores($ascensor);
        $em->persist($bloque);
        $em->flush();

        return $this->get('funciones')->json($bloque);
    }

    public function deleteBloqueAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        /**
         * Editar regsitro de bloque
         */
        $bloque = $em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificados')->find($id);
        $em->remove($bloque);
        $em->flush();

        return $this->get('funciones')->json($bloque);
    }

    /**
     * Funciones PISOS
     */
    public function getPisosAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');

        $pisos = $em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificadosPisos')->findBy(array(
            'infraestructuraH2CaracteristicaEdificados'=>$id
        ));

        return $this->get('funciones')->json($pisos);
    }




    public function createPisoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        
        
        //$infrah2edificados = $em->getRepository('SieAppWebBundle:InfraestructuraH2CaracteristicaEdificados')->find($id);
        
        $pisoForm = $this->createForm(
            new InfraestructuraH2CaracteristicaEdificadosPisosType(), 
            new InfraestructuraH2CaracteristicaEdificadosPisos()
        );

        /*$pisoForm->handleRequest($request);

        if($pisoForm->isSubmitted()){
            //guardar
            dump($pisoForm);die;
        }*/



        return $this->render('SieAppWebBundle:InfraestructuraH2Caracteristica:pisoNew.html.twig', array(
            'form' => $pisoForm->createView(),
            //'infrah2edificados'=>$infrah2edificados
        ));
    }
}
