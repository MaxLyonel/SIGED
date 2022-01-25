<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Controller\FuncionesController;

/**
 * MaestroGestion controller.
 *
 */
class InstitucionEducativaGestionController extends Controller {
    public $session;
    /*
     * Lista de maestros en la unidad educativa
     */
    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request,$op) {
        // generar los titulos para los diferentes sistemas
        $this->session = new Session();
        $tipoSistema = $request->getSession()->get('sysname');
        switch($tipoSistema){
            case 'REGULAR': $this->session->set('tituloTipo', 'Unidad Educativa');break;
            case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Centro de Educación');break;
            case 'PERMANENTE': $this->session->set('tituloTipo', 'Centro');break;
            default: $this->session->set('tituloTipo', 'Maestro(s)');break;
        }
        
        ////////////////////////////////////////////////////
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
            $form = $request->get('form');
            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
            if (!$institucioneducativa) {
                $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                return $this->render('SieAppWebBundle:InstitucionEducativaGestion:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
            /*
            * verificamos si tiene tuicion
            */
            
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $form['institucioneducativa']);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();
            if ($aTuicion[0]['get_ue_tuicion']){
                $institucion = $form['institucioneducativa'];
                $gestion = $form['gestion'];
            }else{
                $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
                return $this->render('SieAppWebBundle:InstitucionEducativaGestion:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            } 
            
        } else {
            $nivelUsuario = $request->getSession()->get('roluser');
            if ($nivelUsuario != 9 && $nivelUsuario != 5) { // si no es institucion educativa 5 o administrativp 9
                // formulario de busqueda de institucion educativa
                // mejorarrrrrrrrrrr
                $sesinst = $request->getSession()->get('idInstitucion');
                if ($sesinst) {
                    $institucion = $sesinst;
                    $gestion = $request->getSession()->get('idGestion');
                    if($op == 'search'){
                       return $this->render('SieAppWebBundle:InstitucionEducativaGestion:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView())); 
                    }
                } else {
                    
                    return $this->render('SieAppWebBundle:InstitucionEducativaGestion:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    
                }
            } else { // si es institucion educativa
                $sesinst = $request->getSession()->get('idInstitucion');
                if ($sesinst) {
                    $institucion = $sesinst;
                    $gestion = $request->getSession()->get('idGestion');
                }else{
                    $funcion = new FuncionesController();
                    $institucion = $funcion->ObtenerUnidadEducativaAction($request->getSession()->get('userId'),$request->getSession()->get('currentyear')); //5484231);
                    $gestion = $request->getSession()->get('currentyear');
                }    
            }
        }

        // creamos variables de sesion de la institucion educativa y gestion
        $request->getSession()->set('idInstitucion', $institucion);
        $request->getSession()->set('idGestion', $gestion);

        // Vista para la unidad educativa
        $query = $em->createQuery(
                    'SELECT ies, st FROM SieAppWebBundle:InstitucioneducativaSucursal ies
                    JOIN ies.sucursalTipo st
                    WHERE ies.institucioneducativa = :idInstitucion
                    AND ies.gestionTipo = :gestion')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion',$gestion);
        $sucursales = $query->getResult();

        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        //return $this->render('SieAppWebBundle:MaestroGestion:index.html.twig', array('maestro' => $maestro, 'institucion' => $institucion, 'gestion' => $gestion));
    
        /*$paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $maestro, $this->get('request')->query->get('page', 1), 10
        );
        */
        return $this->render('SieAppWebBundle:InstitucionEducativaGestion:index.html.twig', array(
                    'sucursales' => $sucursales, 'institucion' => $institucion, 'gestion' => $gestion
        ));
    }

    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual,$gestionactual-1 => $gestionactual-1);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioneducativagestion'))
                ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on','maxlength'=>8,'autocomplete'=>'off')))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    public function newAction(Request $request) {
        
    }

    private function newForm($idInstitucion, $gestion) {
       
    }

    public function createAction(Request $request) {
       
    }


    /*
     * Modificar el registro del maestro
     */

    public function editAction(Request $request) {
        try{
            $em = $this->getDoctrine()->getManager();
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idInstitucion'));
            $sucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($request->get('idSucursal'));
            return $this->render('SieAppWebBundle:InstitucionEducativaGestion:edit.html.twig', array('form'=>$this->editForm($institucion,$sucursal)->createView()));
        } catch (Exception $ex) {
            
        }
    }

    private function editForm($institucion, $sucursal) {
        $em = $this->getDoctrine()->getManager();
        $jurisdiccion = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($institucion->getLeJuridicciongeografica());
        $localidad = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($jurisdiccion->getLugarTipoIdLocalidad());
        $canton = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($localidad->getLugarTipo());
        $seccionMunicipal = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($canton->getLugarTipo());
        $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($seccionMunicipal->getLugarTipo());
        $departamento = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($provincia->getLugarTipo());
        
        $distrito = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($jurisdiccion->getLugarTipoIdDistrito());
        
        switch($this->session->get('sysname')){
            case 'PERMANENTE': $display = 'none'; 
                $query = $em->createQuery(
                    'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                    WHERE ct.institucioneducativaTipo IN (:id)
                    ORDER BY ct.id')
                ->setParameter('id', array(4,0));
                break;
            default: $display = 'block';
                $query = $em->createQuery(
                    'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                    WHERE ct.institucioneducativaTipo IN (:id)')
                ->setParameter('id', array(1,2,3,4));
                break;
        }
        $cargos = $query->getResult();
        $cargosArray = array();
        foreach($cargos as $c){
            $cargosArray[$c->getId()] = $c->getCargo();
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioneducativagestion_update'))
                ->add('idInstitucion', 'hidden', array('data' => $institucion->getId()))
                ->add('idSucursal', 'hidden', array('data' => $sucursal->getId()))
                ->add('codigo', 'text', array('label' => 'Código', 'required' => true, 'data' => $institucion->getId(), 'disabled' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control','pattern' => '[0-9]{5,10}')))
                ->add('nombre', 'text', array('label' => 'Nombre', 'required' => true, 'data' => $institucion->getInstitucioneducativa(),'disabled' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'pattern' => '[0-9]{1,10}')))
                ->add('esabierta', 'checkbox', array('label' => '¿Esta abierta?', 'required' => false,'attr' => array('class' => 'form-control','checked' => $sucursal->getEsabierta())))
                ->add('departamento','text',array('label'=>'Departamento','data'=>mb_strtoupper($departamento->getCodigo().' - '.$departamento->getLugar()),'disabled'=>true,'attr'=>array('class'=>'form-control')))
                ->add('provincia','text',array('label'=>'Provincia','data'=>$provincia->getCodigo().' - '.$provincia->getLugar(),'disabled'=>true,'attr'=>array('class'=>'form-control')))
                ->add('municipio','text',array('label'=>'Sección Municipal','data'=>$seccionMunicipal->getCodigo().' - '.$seccionMunicipal->getLugar(),'disabled'=>true,'attr'=>array('class'=>'form-control')))
                ->add('canton','text',array('label'=>'Cantón','data'=>$canton->getCodigo().' - '.$canton->getLugar(),'disabled'=>true,'attr'=>array('class'=>'form-control')))
                ->add('localidad','text',array('label'=>'Ciudad o Localidad','data'=>$localidad->getCodigo().' - '.$localidad->getLugar(),'disabled'=>true,'attr'=>array('class'=>'form-control')))
                ->add('zona','text',array('label'=>'Zona','data'=>$sucursal->getZona(),'disabled'=>true,'attr'=>array('class'=>'form-control')))
                ->add('codigoEdificio','text',array('label'=>'Código de edificio escolar','data'=>$jurisdiccion->getId(),'disabled'=>true,'attr'=>array('class'=>'form-control')))
                ->add('direccion','text',array('label'=>'Dirección','data'=>$sucursal->getDireccion(),'disabled'=>true,'attr'=>array('class'=>'form-control')))
                ->add('area','text',array('label'=>'Area','data'=>$localidad->getArea2001(),'disabled'=>true,'attr'=>array('class'=>'form-control')))
                ->add('telefono','text',array('label'=>'Teléfono','data'=>$sucursal->getTelefono1(),'disabled'=>false,'attr'=>array('class'=>'form-control jnumbers','pattern'=>'[0-9]{7,8}','autocomplete'=>'off','maxlength'=>'8')))
                ->add('fax','text',array('label'=>'Fax','required'=>false,'data'=>$sucursal->getFax(),'disabled'=>false,'attr'=>array('class'=>'form-control jnumbers','autocomplete'=>'off','maxlength'=>'10')))
                ->add('correo','text',array('label'=>'Correo electrónico','data'=>$sucursal->getEmail(),'disabled'=>false,'attr'=>array('class'=>'form-control jemail','autocomplete'=>'off')))
                ->add('casillaPostal','text',array('label'=>'Casilla postal','required'=>false,'data'=>$sucursal->getCasilla(),'disabled'=>false,'attr'=>array('class'=>'form-control jnumbers','maxlength'=>'8','autocomplete'=>'off')))
                ->add('telefonoRef','text',array('label'=>'Teléfono','data'=>$sucursal->getTelefono2(),'required'=>true,'disabled'=>false,'attr'=>array('class'=>'form-control jnumbers','pattern' => '[0-9]{1,8}','autocomplete'=>'off','maxlength'=>'8')))
                ->add('pertenece','choice',array('label'=>'Pertenece a (cargo o relación)','choices'=>$cargosArray,'data'=>$sucursal->getReferenciaTelefono2(),'empty_value'=>'Seleccionar...','required'=>true,'disabled'=>false,'attr'=>array('class'=>'form-control')))
                ->add('distrito','text',array('label'=>'Distrito','data'=>$distrito->getCodigo().' - '.$distrito->getLugar(),'disabled'=>true,'attr'=>array('class'=>'form-control')))
                ->add('dependencia','text',array('label'=>'Descripción','data'=>$institucion->getDependenciaTipo()->getDependencia(),'disabled'=>true,'attr'=>array('class'=>'form-control')))
                ->add('turno', 'entity', array('class' => 'SieAppWebBundle:TurnoTipo', 'data' =>$em->getReference('SieAppWebBundle:TurnoTipo',($sucursal->getTurnoTipo() != null)?$sucursal->getTurnoTipo()->getId():0), 'label' => 'Turno(s)', 'required' => true, 'attr' => array('class' => 'form-control')))
                
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $form;
    }

    /*
     * Modificacion de datos actualizacion
     */

    public function updateAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');
            $sucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($form['idSucursal']);
            $sucursal->setTelefono1($form['telefono']);
            $sucursal->setFax($form['fax']);
            $sucursal->setEmail($form['correo']);
            $sucursal->setCasilla($form['casillaPostal']);
            $sucursal->setTelefono2($form['telefonoRef']);
            $sucursal->setReferenciaTelefono2($form['pertenece']);
            $sucursal->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneById($form['turno']));
            $sucursal->setEsabierta((isset($form['esabierta'])?1:0));
            
            $em->persist($sucursal);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('updateOk', 'Datos modificados correctamente');
            return $this->redirect($this->generateUrl('institucioneducativagestion'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la modificacion de datos');
            return $this->redirect($this->generateUrl('institucioneducativagestion'));
        }
    }

    /*
     * Eliminacion de maestro
     */

    public function deleteAction(Request $request) {
        
    }
}
