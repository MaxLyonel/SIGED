<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Form\BuscarPersonaSegipType;
use Sie\AppWebBundle\Form\VerificarPersonaSegipType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;
use Sie\AppWebBundle\Entity\Persona;

/**
 * SegipController.
 *
 */
class SegipController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $roluser = $this->session->get('roluser');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $formBuscarPersona = $this->createForm(new BuscarPersonaSegipType(), 
            null, 
            array(
                'action' => $this->generateUrl('segip_search'), 
                'method' => 'POST'
            )
        );

        if($roluser != 8) {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $formVerificarPersona = $this->createForm(new VerificarPersonaSegipType(), 
            null, 
            array(
                'action' => $this->generateUrl('segip_verify'), 
                'method' => 'POST'
            )
        );

        return $this->render('SieAppWebBundle:Segip:index.html.twig', array(
            'form1' => $formBuscarPersona->createView(),
            'form2' => $formVerificarPersona->createView(),
        ));
    }

    public function searchAction(Request $request){        
        $em = $this->getDoctrine()->getManager();
        
        $form = $request->get('sie_buscar_persona_segip');
        $resultado = $this->get('sie_app_web.segip')->buscarPersonaPorCarnet($form['carnet'], $form, $form['entorno'], 'academico');
        $tmpData =  array();
        if( $resultado )
        {
            $tmpData = $resultado['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson'];
            $tmpDataDecode = json_decode($tmpData);
            
            $complementoVisible = $tmpDataDecode->ComplementoVisible;
            $complemento = $tmpDataDecode->Complemento;
            $fechaNacimiento = $tmpDataDecode->FechaNacimiento;
            $fechaNacimiento = \DateTime::createFromFormat('m/d/Y', $fechaNacimiento)->format('Y-d-m');
            $carnet = $tmpDataDecode->NumeroDocumento;

            $personasTmp = $em->getRepository('SieAppWebBundle:Persona')->findBy(array(
                'carnet' => $carnet,
                'fechaNacimiento' => new \DateTime($fechaNacimiento),
                'complemento' => $complemento
            ));

            $persona = array();
            foreach ($personasTmp as $p)
            {
                $persona[] = (array(
                    'carnet' => $p->getCarnet(),
                    'paterno' => $p->getPaterno(),
                    'materno' => $p->getMaterno(),
                    'nombre' =>  $p->getNombre(),
                    'fechaNacimiento' => $p->getFechaNacimiento()->format('d-m-Y'),
                    'extranjero' => $p->getEsExtranjero())
                );
            }
            $resultado['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson'] = json_encode($persona);
        }
        else
        {
            $resultado['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson'] = json_encode(array());
        }
        return $this->render('SieAppWebBundle:Segip:resultado1.html.twig',array(
            'resultado'=>$resultado
        ));
    }

    public function verifyAction(Request $request){
        
        $form = $request->get('sie_verificar_persona_segip');

        $resultado = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($form['carnet'], $form, $form['entorno'], 'academico');

        return $this->render('SieAppWebBundle:Segip:resultado2.html.twig',array(
            'resultado'=>$resultado
        ));
    }
}
