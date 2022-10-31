<?php

namespace Sie\InfraestructuraBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Sie\AppWebBundle\Entity\Usuario;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

class MapsController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {
        $codigoEdificio = 80730016;
        $em = $this->getDoctrine()->getManager();
        $jurisdiccion = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($codigoEdificio);
        $latitud = $jurisdiccion->getCordx();
        $longitud = $jurisdiccion->getCordy();

        $localidad = $em->getRepository('SieAppWebBundle:LugarTipo')->find($jurisdiccion->getLugarTipoIdLocalidad2012());
        $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->find($localidad->getLugarTipo());
        $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->find($comunidad->getLugarTipo());
        $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->find($municipio->getLugarTipo());
        $departamento = $em->getRepository('SieAppWebBundle:LugarTipo')->find($provincia->getLugarTipo());

        // Arrays
        $departamentos = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel'=>8));
        $depArray = array();
        foreach ($departamentos as $d) {
            $depArray[$d->getId()] = $d->getLugar();
        }
        $provincias = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$departamento->getId(),'lugarNivel'=>9));
        $provArray = array();
        foreach ($provincias as $p) {
            $provArray[$p->getId()] = $p->getLugar();
        }
        $municipios = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$provincia->getId(),'lugarNivel'=>10));
        $muniArray = array();
        foreach ($municipios as $m) {
            $muniArray[$m->getId()] = $m->getLugar();
        }
        $comunidades = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$municipio->getId(),'lugarNivel'=>11));
        $comuArray = array();
        foreach ($comunidades as $c) {
            $comuArray[$c->getId()] = $c->getLugar();
        }
        $localidades = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$comunidad->getId(),'lugarNivel'=>12));
        $localArray = array();
        foreach ($localidades as $l) {
            $localArray[$l->getId()] = $l->getLugar();
        }

        $form = $this->createFormBuilder()
                    ->add('codigoEdificio', 'hidden', array('data' => $codigoEdificio))
                    ->add('departamento', 'choice', array('label' => 'Departamento', 'disabled' => false, 'required' => true, 'choices' => $depArray,'data'=>$departamento->getId(),'attr' => array('class' => 'form-control jupper', 'onchange' => 'listarProvincias(this.value);')))
                    ->add('provincia', 'choice', array('label' => 'Provincia', 'disabled' => false, 'required' => true, 'choices' => $provArray, 'data'=>$provincia->getId(),'attr' => array('class' => 'form-control jupper', 'onchange' => 'listarMunicipios(this.value)')))
                    ->add('municipio', 'choice', array('label' => 'Municipio', 'disabled' => false, 'required' => true, 'choices' => $muniArray,'data'=>$municipio->getId(),'attr' => array('class' => 'form-control jupper', 'onchange' => 'listarComunidades(this.value)')))
                    ->add('comunidad', 'choice', array('label' => 'Cantón', 'disabled' => false, 'required' => true, 'choices' => $comuArray,'data'=>$comunidad->getId(), 'attr' => array('class' => 'form-control jupper', 'onchange' => 'listarLocalidades(this.value)')))
                    ->add('localidad', 'choice', array('label' => 'Localidad', 'disabled' => false, 'required' => true, 'choices' => $localArray,'data'=>$localidad->getId(),'attr' => array('class' => 'form-control jupper', 'onchange'=>'verificarLocalidad()')))
                    ->getForm();

        return $this->render('SieInfraestructuraBundle:Maps:index.html.twig', array(
            'latitud'=>$latitud,
            'longitud'=>$longitud,
            'codigoEdificio'=>$codigoEdificio,
            'form'=>$form->createView()
        ));
    }

}
