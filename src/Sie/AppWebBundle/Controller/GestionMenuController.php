<?php

namespace Sie\AppWebBundle\Controller;

use Sie\AppWebBundle\Entity\MenuNivelTipo;
use Sie\AppWebBundle\Entity\SistemaTipo;
use Sie\AppWebBundle\Entity\MenuSistemaRol;
use Sie\AppWebBundle\Entity\MenuTipo;
use Sie\AppWebBundle\Entity\MenuSistema;
use Sie\AppWebBundle\Entity\SistemaRol;
use Sie\AppWebBundle\Entity\Permiso;
use Sie\AppWebBundle\Entity\RolPermiso;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\PropertyInfo\Tests\Fixtures\Dummy;

//use Symfony\Component\Form\Extension\Core\Type\CheckboxType;




/**
 * Gestión de Menú Controller.
 */
class GestionMenuController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        //$this->session->set('sistemaid', 1);
    }

    /**
     * Muestra el listado de Menús
     */
    public function indexAction() {
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT mt.id, mt.detalle_menu,mt.ruta,mt.icono, mt.menu_nivel_tipo_id  
          FROM menu_tipo mt 
          WHERE mt.menu_nivel_tipo_id = 1
          ORDER BY 1');
        $query->execute();
        $menu= $query->fetchAll();
        return $this->render('SieAppWebBundle:GestionMenu:index.html.twig', array('menu' => $menu));
    }
    public function inicioAction() {
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieAppWebBundle:GestionMenu:inicio.html.twig');
    }

    /**
     * Administracíon de Meús Princpales.
     */
    public function  administraSistemaAction(){
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT * FROM sistema_tipo ORDER BY 2 ASC");
        $query->execute();
        $sistema = $query->fetchAll();
        return $this->render('SieAppWebBundle:GestionMenu:sistema.html.twig', array('sistema'=>$sistema));
    }
    public function administraSistemanuevoAction(){

        $form = $this->createFormBuilder()
            //->setAction($this->generateUrl('gestionmenu_administra_sistema_create'))
            ->add('sistema', 'text', array( 'attr' => array('class' => 'form-control','enabled' => true)))
            ->add('abreviatura', 'text', array( 'attr' => array('class' => 'form-control','enabled' => true,'max_length' => 5)))
            ->add('observaciones', 'textarea', array( 'required' => false,'attr' => array('class' => 'form-control','enabled' => true)))
            ->add('bundle', 'text', array('attr' => array('class' => 'form-control','enabled' => true)))
            ->add('url', 'text', array('attr' => array('class' => 'form-control','enabled' => true)))
            ->add('guardar', 'button', array('label'=> 'Guardar', 'attr'=>array('class'=>'btn btn-primary ','onclick'=>'guardarSistemanuevo()')))
            ->getForm();
        return $this->render('SieAppWebBundle:GestionMenu:nuevoSistema.html.twig',array(
            'form'=>$form->createView()
        ));
    }

    public function administraSistemacreateAction(Request $request){
        $form = $request->get('form');
        $sistemanom               = $form['sistema'];
        $observaciones           = $form['observaciones'];
        $abre                    = $form['abreviatura'];
        $abreviatura             =strtoupper($abre);
        $sistemanombre           =strtoupper($sistemanom);
        $bundle                  = $form['bundle'];
        $url                     = $form['url'];
        $em = $this->getDoctrine()->getManager();
        $sistema=new SistemaTipo();
        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('sistema_tipo');")->execute();
        $tamaño=strlen($abreviatura);
        if($tamaño > 5){
            $mensaje = 'La Abreviatura: ' . $sistema->getAbreviatura() . 'solo debe contener como máximo 5 letras';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
        }
        else{
            $sistema->setSistema($sistemanombre);
            $sistema->setAbreviatura($abreviatura);
            $sistema->setObs($observaciones);
            $sistema->setBundle($bundle);
            $sistema->setUrl($url);
            $em->persist($sistema);
            $em->flush();
            $mensaje = 'El Sistema ' . $sistema->getSistema() . ' Fue Registrado con éxito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        }
        $query = $em->getConnection()->prepare('SELECT sit.id,sistema,abreviatura,bundle,url,obs
                                                FROM sistema_tipo sit 
                                                ORDER BY 2');
        $query->execute();
        $listasistema = $query->fetchAll();
        return $this->render('SieAppWebBundle:GestionMenu:listaSistemas.html.twig',array('listasistema'=>$listasistema));
    }

    public function administraSistemaeditAction(Request $request){
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $idsistema      = $request->get('idsistema');
        $sistema        = $em->getRepository('SieAppWebBundle:SistemaTipo')->find($idsistema);
        $nomsis         =$sistema->getSistema();
        $abreviatura    =$sistema->getAbreviatura();
        $obs            =$sistema->getObs();
        $bundle         =$sistema->getBundle();
        $url            =$sistema->getUrl();
        $abr            =strtoupper($abreviatura);
        $nomsistema     =strtoupper($nomsis);
        $form = $this->createFormBuilder()
            ->add('sistema', 'text', array( 'data' =>$nomsistema , 'attr' => array('class' => 'form-control','enabled' => true)))
            ->add('bundle', 'text', array( 'data' =>$bundle , 'attr' => array('class' => 'form-control','enabled' => true)))
            ->add('url', 'text', array( 'data' =>$url , 'attr' => array('class' => 'form-control','enabled' => true)))
            ->add('abreviatura', 'text', array( 'data' =>$abr ,'attr' => array('class' => 'form-control','enabled' => true)))
            ->add('observaciones', 'text', array(  'data' =>$obs , 'attr' => array('class' => 'form-control','enabled' => true)))
            ->add('idsistema', 'hidden', array(  'data' =>$idsistema , 'attr' => array('class' => 'form-control','enabled' => true)))
            ->add('guardar', 'button', array('label'=> 'Guardar Cambios', 'attr'=>array('class'=>'btn btn-primary ','onclick'=>'guardarSistema()')))
            ->getForm();
        return $this->render('SieAppWebBundle:GestionMenu:editarSistema.html.twig',array(
            'form'=>$form->createView(),'sistema'=>$sistema
        ));
    }

    public function  administraSistemaupdateAction(Request $request){
        $form= $request->get('form');
        $idsistema              = $form['idsistema'];
        $sistemanom             = $form['sistema'];
        $observaciones          = $form['observaciones'];
        $abre                   = $form['abreviatura'];
        $bundle                 = $form['bundle'];
        $url                    = $form['url'];
        $em = $this->getDoctrine()->getManager();
        $sistematipo  = $em->getRepository('SieAppWebBundle:SistemaTipo')->find($idsistema);
        $tamaño=strlen($abre);
        if($tamaño > 5){
            $mensaje = 'La Abreviatura: ' . $abre . 'solo debe contener como máximo 5 letras';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
        }
        else{
            $abre                     =strtoupper($abre);
            $sistemanombre           =strtoupper($sistemanom);
            $sistematipo->setSistema($sistemanombre);
            $sistematipo->setAbreviatura($abre);
            $sistematipo->setObs($observaciones);
            $sistematipo->setBundle($bundle);
            $sistematipo->setUrl($url);
            $em->persist($sistematipo);
            $em->flush();
            $mensaje = 'El Sistema ' . $sistematipo->getSistema() . ' Fue Actualizada con éxito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        }
        $query = $em->getConnection()->prepare('SELECT sit.id,sistema,abreviatura,bundle,url,obs
                                                FROM sistema_tipo sit 
                                                ORDER BY 2
                                               ');
        $query->execute();
        $listasistema = $query->fetchAll();
        return $this->render('SieAppWebBundle:GestionMenu:listaSistemas.html.twig',array('listasistema'=>$listasistema));
    }

    public function administraSistemadeleteAction(Request $request){
        $idsistema= $request->get('idSistema');
        $em=$this->getDoctrine()->getManager();
        $sistema=$em->getRepository('SieAppWebBundle:SistemaTipo')->find($idsistema);
        $query = $em->getConnection()->prepare("SELECT COUNT(*) FROM sistema_rol WHERE sistema_rol.sistema_tipo_id = $idsistema");
        $query->execute();
        $sistemaasignadorol = $query->fetch();
        $query = $em->getConnection()->prepare("SELECT COUNT(*) FROM menu_sistema WHERE menu_sistema.sistema_tipo_id=$idsistema");
        $query->execute();
        $sistemaasignadomenutipo = $query->fetch();
        if($sistemaasignadorol['count'] > 0|| $sistemaasignadomenutipo['count']>0){
            $mensaje = 'El Sistema ' . $sistema->getSistema() . '  NO se puede eliminar por que se encuentra asignado a un Menú ';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
        }
        else{
            $em->remove($sistema);
            $em->flush();
            $mensaje = 'El Sistema ' . $sistema->getSistema() . ' Fue Eliminado con Éxito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        }
        $query = $em->getConnection()->prepare('SELECT sit.id,sistema,abreviatura,bundle,url,obs
                                                FROM sistema_tipo sit 
                                                ORDER BY 2
                                               ');
        $query->execute();
        $listasistema = $query->fetchAll();
        return $this->render('SieAppWebBundle:GestionMenu:listaSistemas.html.twig',array('listasistema'=>$listasistema));
    }

    // Administracion de Menús
    public function nuevomenuAction(){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT menu_nivel_tipo FROM menu_nivel_tipo 
                                                WHERE menu_nivel_tipo.id>=100
                                                ORDER BY 1");
        $query->execute();
        $listaicono= $query->fetchAll();
        $icono = array();
        for ($i = 0; $i < count($listaicono); $i++) {
            $icono[$listaicono[$i]['menu_nivel_tipo']] = $listaicono[$i]['menu_nivel_tipo'];
        }
        $form = $this->createFormBuilder()
            ->add('nombre', 'text', array( 'attr' => array('class' => 'form-control')))
            ->add('icono', 'text', array('attr' => array('class' => 'form-horizontal icp icp-auto','autocomplete'=>'off')))
            ->add('guardar', 'button', array('label'=> 'Guardar Menú', 'attr'=>array('class'=>'btn btn-primary','onclick'=>'guardarMenu()')))
            ->getForm();
        return $this->render('SieAppWebBundle:GestionMenu:nuevoMenu.html.twig',array('form'=>$form->createView(),'listaicono'=>$listaicono));
    }
    public function createMenuAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $nombre=$form['nombre'];
        $detallemenu=trim($nombre);
        $query = $em->getConnection()->prepare("SELECT count(*) 
                                                FROM menu_tipo 
                                                WHERE  menu_tipo.ruta='#' 
                                                AND  LOWER(translate(menu_tipo.detalle_menu, 'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ', 'aeiouAEIOUaeiouAEIOU'))=lower(translate('".$detallemenu."','áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ', 'aeiouAEIOUaeiouAEIOU'))");
        $query->execute();
        $cantidad= $query->fetch();
        if($cantidad['count'] == 0){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('menu_tipo');")->execute();
            $menutipo = new MenuTipo();
            $niveltipo = $em->getRepository('SieAppWebBundle:MenuNivelTipo')->find(1);
            $menutipo ->setDetalleMenu($form['nombre']);
            $menutipo ->setRuta("#");
            $menutipo ->setIcono($form['icono']);
            $menutipo ->setOrden(1);
            $menutipo ->setObs("-");
            $menutipo ->setMenuNivelTipo($niveltipo);
            $em->persist($menutipo);
            $em->flush();
            $mensaje = 'El Menú Principal Fue Registrado Correctamente';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        }
        else{
            $mensaje = 'El Nombre de : '.$detallemenu.' ya se Encuentra Registrado. Elija otro nombre para el Menú Principal';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
        }
        $query = $em->getConnection()->prepare('SELECT mt.id, mt.detalle_menu,mt.ruta,mt.icono, mt.menu_nivel_tipo_id  
                                              FROM menu_tipo mt 
                                              WHERE mt.menu_nivel_tipo_id = 1
                                              ORDER BY 1');
        $query->execute();
        $menu= $query->fetchAll();
        return $this->render('SieAppWebBundle:GestionMenu:listaMenusprincipales.html.twig', array('menu' => $menu));
    }
    public function updateMenuAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id_menu= $request->get('id_menu');
        $menutipo   = $em->getRepository('SieAppWebBundle:MenuTipo')->find($id_menu);
        $detalle_menu   =$menutipo->getDetalleMenu();
        $query = $em->getConnection()->prepare("SELECT menu_nivel_tipo FROM menu_nivel_tipo 
                                                WHERE menu_nivel_tipo.id >= 100
                                                ORDER BY 1");
        $query->execute();
        $listaicono= $query->fetchAll();
        $iconoArray = array();
        for ($i = 0; $i < count($listaicono); $i++) {
            $iconoArray[$listaicono[$i]['menu_nivel_tipo']] = $listaicono[$i]['menu_nivel_tipo'];
        }
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('gestionmenu_editmenu'))
            ->add('detalle_menu', 'text', array( 'required' => true, 'data' =>$detalle_menu , 'attr' => array('class' => 'form-control','enabled' => true)))
            ->add('icono', 'text', array('attr' => array('class' => 'form-horizontal icp icp-auto','autocomplete'=>'off')))
            ->add('id_menu', 'hidden', array( 'data'=>$id_menu))
            ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'disbled' => true)))
            ->getForm();
        return $this->render('SieAppWebBundle:GestionMenu:editarMenu.html.twig',array('form'=>$form->createView(),'menu'=>$menutipo ));
    }
    public function editmenuAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $id_menu        =   $form['id_menu'];
        $detalle_menu   =   $form['detalle_menu'];
        $icono          =   $form['icono'];
        $menutipo   = $em->getRepository('SieAppWebBundle:MenuTipo')->find($id_menu);
        $menutipo->setDetalleMenu($detalle_menu);
        $menutipo->setIcono($icono);
        $em->persist($menutipo);
        $em->flush();
        $mensaje = 'Se realizaron los cambios correctamente';
        $request->getSession()
            ->getFlashBag()
            ->add('exito', $mensaje);
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT mt.id, mt.detalle_menu,mt.ruta,mt.icono, mt.menu_nivel_tipo_id  
                                                  FROM menu_tipo mt 
                                                  WHERE mt.menu_nivel_tipo_id = 1
                                                  ORDER BY 1');
        $query->execute();
        $menu= $query->fetchAll();
        return $this->redirectToRoute('gestionmenu', array('menu'=>$menu));
    }
    public function eliminamenuAction(Request $request){
        $idmenuprincipal =$request->get('idmenu');
        $em=$this->getDoctrine()->getManager();
        $menuprincipalencontrado   = $em->getRepository('SieAppWebBundle:MenuTipo')->find($idmenuprincipal);
        $query = $em->getConnection()->prepare("SELECT COUNT(*) FROM menu_tipo 
                                WHERE menu_tipo.menu_tipo_id = $idmenuprincipal ");
        $query->execute();
        $cantidadsub = $query->fetch();
        if($cantidadsub['count']>0){
            $mensaje = 'El Menú principal: ' . $menuprincipalencontrado->getDetalleMenu() . ' Tiene sub menús. No se puede realizar la eliminación';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
        }
        else{
            $em->remove($menuprincipalencontrado);
            $em->flush();
            $mensaje = 'El Menú principal: ' . $menuprincipalencontrado->getDetalleMenu() . ' Fue eliminado con éxito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        }
        $query = $em->getConnection()->prepare('SELECT mt.id, mt.detalle_menu,mt.ruta,mt.icono, mt.menu_nivel_tipo_id  
                                              from menu_tipo mt 
                                              where mt.menu_nivel_tipo_id = 1
                                              ORDER BY 1');
        $query->execute();
        $menu= $query->fetchAll();
        return $this->render('SieAppWebBundle:GestionMenu:listaMenusprincipales.html.twig', array('menu' => $menu,));
    }
    //Administracion de Sub Menus

    public function submenusAction(Request $request){
        $idmenu = $request->get('id');
        $idnivel = $request->get('nivel');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT mt.id,mt.detalle_menu,mt.icono,mt.ruta,mt.orden,mt.obs,mt.menu_nivel_tipo_id 
                        FROM menu_tipo mt  WHERE mt.menu_tipo_id = ".$idmenu." 
					    ORDER BY mt.orden ASC");
        $query->execute();
        $menu = $query->fetchAll();
        $query = $em->getConnection()->prepare("SELECT mt.id,mt.detalle_menu,mt.icono,mt.ruta,mt.orden,mt.obs,mt.menu_nivel_tipo_id 
                        FROM menu_tipo mt  WHERE mt.id = ".$idmenu." 
					    ORDER BY mt.orden ASC");
        $query->execute();
        $menuanterior = $query->fetch();
        return $this->render('SieAppWebBundle:GestionMenu:subMenus.html.twig', array(
            'menu' => $menu,'idmenu'=>$idmenu,'idnivel'=>$idnivel,'menuanterior'=>$menuanterior
        ));
    }
    public function nuevosubmenuAction(Request $request){
        $idmenu= $request->get('idmenu');
        $idnivel= $request->get('idnivel');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT max(menu_tipo.orden) from menu_tipo WHERE menu_tipo.menu_tipo_id =$idmenu ");
        $query->execute();
        $orden_maximo = $query->fetch();
        $orden=($orden_maximo['max']+1);
        $query = $em->getConnection()->prepare("SELECT detalle_menu,ruta from menu_tipo WHERE menu_tipo.id =$idmenu ");
        $query->execute();
        $menuanterior = $query->fetch();
        $nombremenu=$menuanterior['detalle_menu'];
        $rutamenuanterior=$menuanterior['ruta'];
        $query = $em->getConnection()->prepare("SELECT menu_nivel_tipo FROM menu_nivel_tipo 
                                                WHERE menu_nivel_tipo.id >= 100
                                                ORDER BY 1");
        $query->execute();
        $listaicono= $query->fetchAll();
        $icono = array();
        for ($i = 0; $i < count($listaicono); $i++) {
            $icono[$listaicono[$i]['menu_nivel_tipo']] = $listaicono[$i]['menu_nivel_tipo'];
        }
        $form = $this->createFormBuilder()
            ->add('nombre', 'text', array( 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
            ->add('ruta', 'text', array( 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true)))
            ->add('icono', 'text', array('attr' => array('class' => 'form-horizontal icp icp-auto','autocomplete'=>'off')))
            ->add('orden', 'hidden', array( 'required' => true, 'data'=>$orden, 'attr' => array('class' => 'form-control','disabled' => false)))
            ->add('observaciones', 'textarea', array( 'required' => false, 'attr' => array('class' => 'form-control','enabled' => true,'rows'=>'3','cols'=>'40')))
            ->add('idmenu', 'hidden', array( 'data'=>$idmenu))
            ->add('idnivel', 'hidden', array( 'data'=>$idnivel))
            ->add('guardar', 'button', array('label'=> 'Guardar Sub Menú', 'attr'=>array('class'=>'btn btn-primary','ata-placement'=>'top','onclick'=>'guardarsubMenu()')))
            ->getForm();
        return $this->render('SieAppWebBundle:GestionMenu:nuevoSubmenu.html.twig',array(
            'form'=>$form->createView(),'nombremenu'=>$nombremenu
        ));
    }
    public function createSubMenuAction(Request $request){
        //dump($request);die;
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $nombresubmenu = TRIM($form['nombre']);
        $idmenu=(int)$form['idmenu'];
        $nivel=$form['idnivel'];
        $query = $em->getConnection()->prepare("SELECT count(*) 
                                                FROM menu_tipo 
                                                WHERE  menu_tipo.ruta!='#' 
                                                AND  LOWER(translate(menu_tipo.detalle_menu, 'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ', 'aeiouAEIOUaeiouAEIOU'))=lower(translate('".$nombresubmenu."','áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ', 'aeiouAEIOUaeiouAEIOU'))");
        $query->execute();
        $cantidad= $query->fetch();
        $cant=(int)$cantidad['count'];
        if($cant==0){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('menu_tipo');")->execute();
            $menutipo = new MenuTipo();
            $menutipoid = $em->getRepository('SieAppWebBundle:MenuTipo')->find($form['idmenu']);
            $niveltipo = $em->getRepository('SieAppWebBundle:MenuNivelTipo')->find($form['idnivel']+1);
            $menutipo ->setDetalleMenu($form['nombre']);
            $menutipo ->setRuta($form['ruta']);
            $menutipo ->setIcono($form['icono']);
            $menutipo ->setOrden($form['orden']);
            $menutipo ->setObs($form['observaciones']);
            $menutipo ->setMenuNivelTipo($niveltipo);
            $menutipo ->setMenuTipo($menutipoid);
            $em->persist($menutipo);
            $em->flush();
        }else{
            $mensaje = 'El Nombre del sub Menú : '.$nombresubmenu.' ya se Encuentra Registrado. Elija otro Nombre para el Sub Menú';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
        }
        $query = $em->getConnection()->prepare("SELECT mt.id,mt.detalle_menu,mt.icono,mt.ruta,mt.orden,mt.obs,mt.menu_nivel_tipo_id
              FROM menu_tipo mt  
              WHERE   mt.menu_tipo_id = $idmenu " );
        $query->execute();
        $menu= $query->fetchAll();
        $query = $em->getConnection()->prepare("SELECT mt.id,mt.detalle_menu,mt.icono,mt.ruta,mt.orden,mt.obs,mt.menu_nivel_tipo_id 
                        FROM menu_tipo mt  WHERE mt.id = ".$idmenu." 
					    ");
        $query->execute();
        $menuanterior = $query->fetch();
        return $this->render('SieAppWebBundle:GestionMenu:listaSubMenusBase.html.twig', array(
            'menu' => $menu,'idmenu'=>$idmenu,'idnivel'=>$nivel,'menuanterior'=>$menuanterior
        ));
    }

    public function updatesubMenuAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id_menuprincipal       = $request->get('id_menuprincipal');
        $idmenu_sub             = $request->get('idmenu_sub');
        $idnivel                = $request->get('idnivel');

        $menutipo   = $em->getRepository('SieAppWebBundle:MenuTipo')->find($idmenu_sub);
        $detalle_menu   =$menutipo->getDetalleMenu();
        $icono          =$menutipo->getIcono();
        $ruta           =$menutipo->getRuta();
        $ordenant       =$menutipo->getOrden();
        $obs            =$menutipo->getObs();
        $query = $em->getConnection()->prepare("SELECT menu_tipo.id,menu_tipo.orden from menu_tipo WHERE menu_tipo.menu_tipo_id =$id_menuprincipal ORDER BY 1");
        $query->execute();
        $orden= $query->fetchAll();
        $ordenArray = array();
        for ($i = 0; $i < count($orden); $i++) {
            $ordenArray[$orden[$i]['orden']] = $orden[$i]['orden'];
        }

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('gestionmenu_editsubmenu'))
            ->add('detalle_menu', 'text', array( 'required'=>true, 'data' =>$detalle_menu , 'attr' => array('class' => 'form-control')))
            ->add('icono', 'text', array('attr' => array('class' => 'form-horizontal icp icp-auto','autocomplete'=>'off')))
            ->add('ruta', 'text', array('required'=>true, 'data' =>$ruta , 'attr' => array('class' => 'form-control')))
            //->add('orden', 'choice', array( 'choices' => $ordenArray, 'attr' => array('class' => 'form-control')))
            ->add('obs', 'textarea', array( 'required'=>false, 'data' =>$obs , 'attr' => array('class' => 'form-control','enabled' => true,'rows'=>'3','cols'=>'40')))
            ->add('id_menu', 'hidden', array( 'data'=>$idmenu_sub))
            ->add('id_menup', 'hidden', array( 'data'=>$id_menuprincipal))
            ->add('idnivel', 'hidden', array( 'data'=>$idnivel))
            ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();
        return $this->render('SieAppWebBundle:GestionMenu:editarSubmenu.html.twig',array('form'=>$form->createView(),'menutipo'=>$menutipo ));
    }
    public function editsubmenuAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $id_menu        =   $form['id_menu'];
        $id_menup       =   $form['id_menup'];
        $idnivel        =   $form['idnivel'];
        $detalle_menu   =   $form['detalle_menu'];
        $icono          =   $form['icono'];
        $ruta           =   $form['ruta'];
        $obs            =   $form['obs'];
        $menutipo   = $em->getRepository('SieAppWebBundle:MenuTipo')->find($id_menu);
        $menutipo->setDetalleMenu($detalle_menu);
        $menutipo->setIcono($icono);
        $menutipo->setRuta($ruta);
        //$menutipo->setOrden($orden);
        $menutipo->setObs($obs);
        $em->persist($menutipo);
        $em->flush();
        $mensaje = 'Sub Menú modificado correctamente';
        $request->getSession()
            ->getFlashBag()
            ->add('exito', $mensaje);
        return $this->redirectToRoute('gestionmenu_submenus',array('id'=>$id_menup,'nivel'=>$idnivel));
    }
    public function eliminasubmenuAction(Request $request){
        //dump($request);die;
        $id=$request->get('id'); //menude la tabla
        $idnivel=$request->get('nivel');//nivel de la tabla
        $idmenuprincipal=$request->get('idmenu');//padre
        $em = $this->getDoctrine()->getManager();
        $submenu=$em->getRepository('SieAppWebBundle:MenuTipo')->find($id);
        $nom = $submenu->getDetalleMenu();
        //dump($nom); die;
        $query = $em->getConnection()->prepare("SELECT  COUNT (*) from menu_tipo where menu_tipo.menu_tipo_id= $id");
        $query->execute();
        $submenuhijos = $query->fetch();
        $query = $em->getConnection()->prepare("SELECT COUNT (*) from menu_sistema where menu_sistema.menu_tipo_id =  $id");
        $query->execute();
        $submenuasigando = $query->fetch();
        if ($submenuhijos['count']>0||$submenuasigando['count']>0){
            $mensaje = 'El Sub Menú: ' . $nom  . ' Tiene sub menús o ya se encuentra asignado a un sistema';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
        }
        else{
            $em->remove($submenu);
            $em->flush();
            $mensaje = 'El Sub Menú: ' . $nom  . ' Fue eliminado con éxito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        }
        $query = $em->getConnection()->prepare("SELECT mt.id,mt.detalle_menu,mt.icono,mt.ruta,mt.orden,mt.obs,mt.menu_nivel_tipo_id 
                        from menu_tipo mt  where mt.id = ".$idmenuprincipal." 
					    ORDER BY mt.orden ASC");
        $query->execute();
        $menuanterior = $query->fetch();
        $query = $em->getConnection()->prepare("SELECT mt.id,mt.detalle_menu,mt.icono,mt.ruta,mt.orden,mt.obs,mt.menu_nivel_tipo_id 
                        from menu_tipo mt  where mt.menu_tipo_id = ".$idmenuprincipal." 
					    ORDER BY mt.orden ASC");
        $query->execute();
        $menu = $query->fetchAll();
        return $this->render('SieAppWebBundle:GestionMenu:listaSubMenusBase.html.twig', array(
            'menu' => $menu,'idmenu'=>$idmenuprincipal,'idnivel'=>$idnivel,'menuanterior'=>$menuanterior
        ));
    }

    //MODULO ASIGNACION ROL-SISTEMA

    public function asignarrolsistemaAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT stipo.id,stipo.sistema from sistema_tipo stipo
                                                ORDER BY 1');
        $query->execute();
        $listasistematipo = $query->fetchAll();
        $sistArray = array();
        for ($i = 0; $i < count($listasistematipo); $i++) {
            $sistArray[$listasistematipo[$i]['id']] = $listasistematipo[$i]['sistema'];
        }
        $form= $this->createFormBuilder()
            ->add('sistema', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $sistArray, 'attr' => array('class' => 'chosen-select','onchange' => 'cargarRolesAsignados()')))
            ->getForm();
        return $this->render('SieAppWebBundle:GestionMenu:asignacionRolSistema.html.twig',array( 'form' => $form->createView()));
    }

    public function rolesasignadosAction(Request $request){

        $idsistema= $request->get('id_sistema');

        $em = $this->getDoctrine()->getManager();
        //Consulta que muestra todos los roles asigandos al sistema seleccionado
        $query = $em->getConnection()->prepare("SELECT sr.id, stipo.sistema,rt.rol 
                FROM sistema_rol sr 
              INNER JOIN sistema_tipo stipo ON sr.sistema_tipo_id =stipo.id
              INNER JOIN rol_tipo rt ON sr.rol_tipo_id =rt.id   
              WHERE sr.sistema_tipo_id=$idsistema ORDER BY 3 ");
        $query->execute();
        $rolesasigandos = $query->fetchAll();
        // dump($rolesasigandos);die;

        return $this->render('SieAppWebBundle:GestionMenu:listaRolesAsignados.html.twig',array('rolesasigandos'=>$rolesasigandos,'id_sistema'=>$idsistema));

    }

    public function rolesnoasigandosAction(Request $request){

        $idsistema= $request->get('id_sistema');

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT rol_tipo.id,rol_tipo.rol from rol_tipo 
                                                WHERE rol_tipo.id NOT IN (
												SELECT sr.rol_tipo_id 
												FROM sistema_rol sr 
														INNER JOIN sistema_tipo stipo ON sr.sistema_tipo_id =stipo.id
														INNER JOIN rol_tipo rt ON sr.rol_tipo_id =rt.id   
												WHERE sr.sistema_tipo_id=$idsistema 
                                                ) ORDER BY 2 ");
        $query->execute();
        $rolesnoasigandos = $query->fetchAll();

        return $this->render('SieAppWebBundle:GestionMenu:listaRolesNoAsignados.html.twig',array('rolesasigandos'=>$rolesnoasigandos,'id_sistema'=>$idsistema));

    }

    public function nuevoRolSistemaAction(Request $request){

        $idsistema= $request->get('id_sistema');
        $idrol= $request->get('idrol');

        $em = $this->getDoctrine()->getManager();
        $sistemaRol= new  SistemaRol();

        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('sistema_rol');")->execute();

        $sistema = $em->getRepository('SieAppWebBundle:SistemaTipo')->find($idsistema);
        $idrol = $em->getRepository('SieAppWebBundle:RolTipo')->find($idrol);
        $sistemaRol->setSistemaTipo($sistema);
        $sistemaRol->setRolTipo($idrol);
        $em->persist($sistemaRol);
        $em->flush();

        return $this->redirectToRoute('gestionmenu_cargar_roles_asignados', array('id_sistema'=>$idsistema));

    }

    public function eliminaRolSistemaAction(Request $request){

        $idrol =$request->get('idrol');
        $idsistema =$request->get('id_sistema');
        $em = $this->getDoctrine()->getManager();
        $rolSistema   = $em->getRepository('SieAppWebBundle:SistemaRol')->find($idrol);
        //dump($rolSistema->getId());die;
        $rolSistemaId=$rolSistema->getId();

        // $totalCursoOferta = count($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$entity->getId())));

        $query = $em->getConnection()->prepare("SELECT COUNT(*)
                                                from menu_sistema_rol --ORDER BY 3
                                                WHERE menu_sistema_rol.sistema_rol_id=$rolSistemaId");
        $query->execute();
        $rolesasignados = $query->fetch();


        if($rolesasignados['count'] > 0){

            $mensaje = 'El Rol ' . $rolSistema->getRolTipo() . 'Tiene Menús asignados No se puede eliminar';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);

            return $this->redirectToRoute('gestionmenu_cargar_roles_asignados', array('id_sistema'=>$idsistema));
        }
        else{
            $em->remove($rolSistema);
            $em->flush();
            $mensaje = 'El Rol ' . $rolSistema->getRolTipo() . ' fue eliminado con exito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
            return $this->redirectToRoute('gestionmenu_cargar_roles_asignados', array('id_sistema'=>$idsistema));
            // dump("El rol seleccionado se puede eliminar");die;
        }


    }


//MODULO ASIGNACION MENU - SISTEMA
    public function asignamenusistemaAction(Request $request){
        //dump($request);die;.$menu_id.
        //$menu_id = $request->get('menu_id');
        $id_sistema = $request->get('id_sistema');
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare("SELECT st.id,st.sistema FROM sistema_tipo st ORDER BY 1");
        $query->execute();
        $sistema = $query->fetchAll();

        $sistArray = array();
        for ($i = 0; $i < count($sistema); $i++) {
            $sistArray[$sistema[$i]['id']] = $sistema[$i]['sistema'];
        }
        $form= $this->createFormBuilder()
            //'onchange' => 'cargarRoles()' 'onchange' => 'cargarGrados()'
            ->add('sistema', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $sistArray, 'attr' => array('class' => 'form-control chosen-select', 'onchange' => 'cargarMenusAsignados()')))
            ->getForm();

        return $this->render('SieAppWebBundle:GestionMenu:asignarMenuSistema.html.twig',array( 'form' => $form->createView()));
    }

    public function menusasignadosAction(Request $request){

        //dump($request);die;
        $id_sistema =$request->get('id_sistema');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
        SELECT menu_sistema.id, menu_sistema.detalle_menu,menu_tipo.icono,menu_sistema.fecha_inicio,menu_sistema.fecha_fin 
        from menu_sistema  INNER JOIN menu_tipo on menu_sistema.menu_tipo_id = menu_tipo.\"id\"   
        WHERE sistema_tipo_id=$id_sistema ORDER BY 1");
        $query->execute();
        $menusasignados = $query->fetchAll();
        //dump($menusasignados);die;

        return $this->render('SieAppWebBundle:GestionMenu:listaMenusAsignados.html.twig',array( 'menusasignados' => $menusasignados,'id_sistema'=>$id_sistema));

    }
    public function menusnoasignadossistemaAction(Request $request){
        //dump($request);die;
        $id_sistema =$request->get('id_sistema');
        // dump($id_sistema);die;

        $em = $this->getDoctrine()->getManager();
        /*Nota.- se lista todos lo menus disponibles para asignar y ademas que sean todos sub menus*/
        /* $query = $em->getConnection()->prepare("SELECT mt.id,mt.detalle_menu,mt.icono,mt.ruta from menu_tipo mt
                                                 where  mt.\"id\" NOT IN (SELECT ms.menu_tipo_id
                                                 from menu_sistema ms INNER JOIN menu_tipo mt ON ms.sistema_tipo_id=mt.\"id\"
                                                 WHERE ms.sistema_tipo_id=$id_sistema) AND mt.ruta not like '#%'");
         $query->execute();*/
        $query = $em->getConnection()->prepare("SELECT mt.id,mt.detalle_menu,mt.icono,mt.ruta from menu_tipo mt 
                                                where  mt.id NOT IN (SELECT ms.menu_tipo_id 
                                                from menu_sistema ms INNER JOIN menu_tipo mt ON ms.sistema_tipo_id=mt.id
                                                WHERE ms.sistema_tipo_id=$id_sistema)
                                                ORDER BY 1 ");
        $query->execute();
        $menusNosasignados = $query->fetchAll();

        return $this->render('SieAppWebBundle:GestionMenu:listaMenusNoAsignados.html.twig',array( 'menusNosasignados' => $menusNosasignados,'id_sistema'=>$id_sistema));
    }

    public function nuevoMenuSistemaAction(Request $request){
        //dump($request);die;
        $idmenu =$request->get('idmenu');
        $idsistema =$request->get('idsistema');

        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare("SELECT menu_tipo_id from menu_tipo WHERE id=$idmenu LIMIT 1");
        $query->execute();
        $menupadre = $query->fetch();
        if (!empty($menupadre) && $menupadre['menu_tipo_id']==null) {
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('menu_sistema');")->execute();
            $menusistema = new MenuSistema();

            $menutipo   = $em->getRepository('SieAppWebBundle:MenuTipo')->find($idmenu);
            $sistema = $em->getRepository('SieAppWebBundle:SistemaTipo')->find($idsistema);

            $menusistema->setMenuTipo($menutipo);
            $menusistema->setSistemaTipo($sistema);
            $menusistema->setDetalleMenu($menutipo->getDetalleMenu());
            $menusistema->setIcono($menutipo->getIcono());

            $em->persist($menusistema);
            $em->flush();

            $query = $em->getConnection()->prepare("SELECT menu_sistema.id, menu_tipo.detalle_menu,menu_tipo.icono,menu_sistema.fecha_inicio,menu_sistema.fecha_fin 
          from menu_sistema  INNER JOIN menu_tipo on menu_sistema.menu_tipo_id = menu_tipo.\"id\"   
         WHERE sistema_tipo_id=$idsistema ORDER BY 1");
            $query->execute();
            $menusasignados = $query->fetchAll();

            return $this->render('SieAppWebBundle:GestionMenu:listaMenusAsignados.html.twig',array( 'menusasignados' => $menusasignados,'id_sistema'=>$idsistema));
        }elseif (!empty($menupadre) && $menupadre['menu_tipo_id']!=null) {
            $menupadreid=$menupadre['menu_tipo_id'];
            $query = $em->getConnection()->prepare("SELECT count(id) as canti from menu_sistema WHERE sistema_tipo_id=$idsistema and menu_tipo_id=$menupadreid");
            $query->execute();
            $existepadre = $query->fetch();
            if (!empty($existepadre) && $existepadre['canti']>0) {
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('menu_sistema');")->execute();
                $menusistema = new MenuSistema();

                $menutipo   = $em->getRepository('SieAppWebBundle:MenuTipo')->find($idmenu);
                $sistema = $em->getRepository('SieAppWebBundle:SistemaTipo')->find($idsistema);

                $menusistema->setMenuTipo($menutipo);
                $menusistema->setSistemaTipo($sistema);
                $menusistema->setDetalleMenu($menutipo->getDetalleMenu());
                $menusistema->setIcono($menutipo->getIcono());

                $em->persist($menusistema);
                $em->flush();

                $query = $em->getConnection()->prepare("SELECT menu_sistema.id, menu_tipo.detalle_menu,menu_tipo.icono,menu_sistema.fecha_inicio,menu_sistema.fecha_fin 
          from menu_sistema  INNER JOIN menu_tipo on menu_sistema.menu_tipo_id = menu_tipo.\"id\"   
         WHERE sistema_tipo_id=$idsistema ORDER BY 1");
                $query->execute();
                $menusasignados = $query->fetchAll();

                return $this->render('SieAppWebBundle:GestionMenu:listaMenusAsignados.html.twig',array( 'menusasignados' => $menusasignados,'id_sistema'=>$idsistema));
            }else{
                return new Response('no');
            }
        }
    }

    public  function editaMenuSistemaAction(Request $request){
        $id_sistema =$request->get('id_sistema');
        $idmenu =$request->get('idmenu');
        $em = $this->getDoctrine()->getManager();
        $menusistema   = $em->getRepository('SieAppWebBundle:MenuSistema')->find($idmenu);

        $query = $em->getConnection()->prepare("SELECT \"id\",detalle_menu  from menu_sistema where menu_sistema.id=$idmenu");
        $query->execute();
        $menuseleccionado = $query->fetch();

        $detalle_menu   =$menusistema->getDetalleMenu();
        $icono          =$menusistema->getIcono();
        $fechaInicio    =$menusistema->getFechaInicio();
        $fechaFin       =$menusistema->getFechaFin();
        if( is_null($fechaInicio)|| is_null($fechaFin)){
            $form = $this->createFormBuilder()

                ->add('detalle_menu', 'text', array( 'data' =>$detalle_menu , 'attr' => array('class' => 'form-control','enabled' => true)))
                //->add('icono', 'choice', array( 'data' =>$icono , 'empty_value' => 'Seleccionar...', 'choices' => $iconos, 'attr' => array('class' => 'form-control')))
                ->add('icono', 'text', array('data' =>$icono,'attr' => array('class' => 'form-horizontal icp icp-auto','autocomplete'=>'off')))
                ->add('fechaInicio', 'text', array(  'data' =>$fechaInicio , 'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('fechaFin', 'text', array(  'data' =>$fechaFin , 'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('idmenu', 'hidden', array(  'data' =>$idmenu , 'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('id_sistema', 'hidden', array(  'data' =>$id_sistema , 'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('guardar', 'button', array('label'=> 'Guardar Cambios', 'attr'=>array('class'=>'btn btn-primary ','onclick'=>'guardarmenuasignado()')))
                ->getForm();

            return $this->render('SieAppWebBundle:GestionMenu:editarMenuSistema.html.twig',array(
                'form'=>$form->createView(),'menuseleccionado'=>$menuseleccionado
            ));
        }else{
            $form = $this->createFormBuilder()

                ->add('detalle_menu', 'text', array( 'data' =>$detalle_menu , 'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('icono', 'text', array('data'=>$icono,'attr' => array('class' => 'form-horizontal icp icp-auto','autocomplete'=>'off')))
                ->add('fechaFin', 'date', array('widget' => 'single_text','format' => 'dd-MM-yyyy','data' =>new \DateTime($menusistema->getFechaFin()->format('d-m-Y')),'required' => false, 'attr' => array('class' => 'form-control calendario')))
                ->add('fechaInicio', 'date', array('widget' => 'single_text','format' => 'dd-MM-yyyy','data' =>new \DateTime($menusistema->getFechaInicio()->format('d-m-Y')),'required' => false, 'attr' => array('class' => 'form-control calendario')))
                ->add('idmenu', 'hidden', array(  'data' =>$idmenu , 'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('id_sistema', 'hidden', array(  'data' =>$id_sistema , 'attr' => array('class' => 'form-control','enabled' => true)))
                ->add('guardar', 'button', array('label'=> 'Guardar Cambios', 'attr'=>array('class'=>'btn btn-primary ','onclick'=>'guardarmenuasignado()')))
                ->getForm();
            return $this->render('SieAppWebBundle:GestionMenu:editarMenuSistema.html.twig',array(
                'form'=>$form->createView(),'menuseleccionado'=>$menuseleccionado
            ));
        }
    }

    public function updatemenusistemaAction(Request $request){
        //dump($request);die;
        $form =$request->get('form');

        $idmenu         =$form['idmenu'];
        $id_sistema     =$form['id_sistema'];
        $detalle_menu   = $form['detalle_menu'];
        $icono          =$form['icono'];
        $fechaInicio    = $form['fechaInicio'];
        $fechaFinal     = $form['fechaFin'];

        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('menu_sistema');")->execute();
        $menusistema = $em->getRepository('SieAppWebBundle:MenuSistema')->find($idmenu);
        $menusistema->setDetalleMenu($detalle_menu);
        $menusistema->setIcono($icono);
        $menusistema->setFechaInicio(new \DateTime($fechaInicio));
        $menusistema->setFechaFin(new \DateTime($fechaFinal));
        $em->persist($menusistema);
        $em->flush();

        $query = $em->getConnection()->prepare("SELECT menu_sistema.id, menu_sistema.detalle_menu,menu_tipo.icono,menu_sistema.fecha_inicio,menu_sistema.fecha_fin 
        FROM menu_sistema  INNER JOIN menu_tipo on menu_sistema.menu_tipo_id = menu_tipo.id   
        WHERE sistema_tipo_id=$id_sistema ORDER BY 1");
        $query->execute();
        $menusasignados = $query->fetchAll();

        return $this->render('SieAppWebBundle:GestionMenu:listaMenusAsignados.html.twig',array( 'menusasignados' => $menusasignados,'id_sistema'=>$id_sistema));
    }

    public function eliminaMenuSistemaAction(Request $request){
        /***
         * revisar funcionamiento
         */
        $em = $this->getDoctrine()->getManager();
        $idmenu = $request->get('idmenu');
        $id_sistema = $request->get('id_sistema');
        $menutipo = $em->getRepository('SieAppWebBundle:MenuSistema')->find($idmenu);

        $query = $em->getConnection()->prepare("SELECT COUNT(*) from menu_sistema_rol WHERE menu_sistema_rol.menu_sistema_id= $idmenu ");
        $query->execute();
        $cantidadmenuroles = $query->fetch();

        //dump($cantidadmenuroles['count']);die;

        if($cantidadmenuroles['count'] > 0){
            $mensaje = 'El Menú ' . $menutipo->getDetalleMenu() . 'Se encuentra con Roles Asignados No se puede eliminar';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);

            $query = $em->getConnection()->prepare("SELECT menu_sistema.id, menu_tipo.detalle_menu,menu_tipo.icono,menu_sistema.fecha_creacion,menu_sistema.fecha_fin 
                                                from menu_sistema  INNER JOIN menu_tipo on menu_sistema.menu_tipo_id = menu_tipo.\"id\"   
                                                WHERE sistema_tipo_id=$id_sistema ORDER BY 1");
            $query->execute();
            $menusasignados = $query->fetchAll();
            return new Response('');
//            return $this->render('SieAppWebBundle:GestionMenu:listaMenusAsignados.html.twig',array( 'menusasignados' => $menusasignados,'id_sistema'=>$id_sistema));
        } else {
            $em->remove($menutipo);
            $em->flush();
            $mensaje = 'El Menú ' . $menutipo->getDetalleMenu() . ' fue eliminado con exito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
            $query = $em->getConnection()->prepare("SELECT menu_sistema.id, menu_tipo.detalle_menu,menu_tipo.icono,menu_sistema.fecha_creacion,menu_sistema.fecha_fin 
                                                from menu_sistema  INNER JOIN menu_tipo on menu_sistema.menu_tipo_id = menu_tipo.\"id\"   
                                                WHERE sistema_tipo_id=$id_sistema ORDER BY 1");
            $query->execute();
            $menusasignados = $query->fetchAll();
            return new Response('');
//            return $this->render('SieAppWebBundle:GestionMenu:listaMenusAsignados.html.twig',array( 'menusasignados' => $menusasignados,'id_sistema'=>$id_sistema));

        }
    }




    //MODULO ASIGNACION MENU - SISTEMA -ROL

    public function asignamenusistemarolAction(Request $request){

        $id_usuario = $this->session->get('userId');


        $em = $this->getDoctrine()->getManager();
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $query = $em->getConnection()->prepare("SELECT st.\"id\",st.sistema FROM sistema_tipo st ORDER BY 1 ");
        $query->execute();
        $sistema = $query->fetchAll();
        $sistArray = array();
        for ($i = 0; $i < count($sistema); $i++) {
            $sistArray[$sistema[$i]['id']] = $sistema[$i]['sistema'];
        }

        $form= $this->createFormBuilder()
            ->add('sistema', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $sistArray, 'attr' => array('class' => 'form-control input-lg','onchange'=>'cargarMenuSistema()')))
            ->add('menu', 'choice',     array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control input-lg','onchange'=>'cargarMenuSistemaRol()')))
            ->add('rol', 'choice',      array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control input-lg' )))
            ->add('create', 'checkbox', array('label' => 'Create'))
            ->add('read', 'checkbox',   array('label' => 'Read'))
            ->add('delete', 'checkbox', array('label' => 'Delete'))
            ->add('update', 'checkbox', array('label' => 'Update'))
            ->add('observaciones', 'textarea', array( 'required' => true, 'attr' => array('class' => 'form-control','enabled' => true,'rows'=>'3','cols'=>'40')))
            //->add('observaciones', 'text',  array( 'required' => true, 'attr' => array('class' => 'form-control','placeholder' => 'Observaciones','enabled' => true,'required' => true)))
            ->add('fechaInicio','text',     array('attr' => array('class' => 'form-control input-lg','placeholder' => 'Fecha de Inicio','enabled' => true)) )
            ->add('fechaFinal','text',      array('attr' => array('class' => 'form-control input-lg','placeholder' => 'Fecha Final','enabled' => true)) )
            ->add('esactivo', 'checkbox',   array('label'     => 'Activar'))
            ->add('guardar', 'button',      array('label'=> 'Guardar', 'attr'=>array('class'=>'btn btn-primary btn-sm btn-push','ata-placement'=>'top', 'onclick'=>'guardarMenuSisRol()')))
            ->getForm();

        return $this->render('SieAppWebBundle:GestionMenu:asignaMenuSistemaRol.html.twig', array('sistema'=>$sistema,'form' => $form->createView()));
    }
    public  function cargasistemamenuAction(Request $request ){
        $id_sistema = $request->get('id_sistema');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT ms.id, mt.detalle_menu FROM menu_sistema ms 
                                    INNER JOIN menu_tipo mt on ms.menu_tipo_id=mt.id 
                                    WHERE ms.sistema_tipo_id=$id_sistema and mt.menu_tipo_id is NOT NULL");
        $query->execute();
        $sistemamenu = $query->fetchAll();


        $menusArray = array();
        for ($i = 0; $i < count($sistemamenu); $i++) {
            $menusArray[$sistemamenu[$i]['id']] = $sistemamenu[$i]['detalle_menu'];
        }

        //dump($id_sistema);die;
        $query = $em->getConnection()->prepare("SELECT msr.id,st.sistema,mt.detalle_menu,rtp.rol,per.\"_create\",per.\"_delete\",per.\"_read\",per.\"_update\",msr.esactivo 
                                                 from menu_sistema_rol msr 
												INNER JOIN menu_sistema ms ON msr.menu_sistema_id = ms.id 
												INNER JOIN sistema_tipo st ON ms.sistema_tipo_id = st.\"id\"
												INNER JOIN sistema_rol sr ON msr.sistema_rol_id = sr.\"id\"
												INNER JOIN rol_tipo rtp ON sr.rol_tipo_id=rtp.\"id\"
												INNER JOIN menu_tipo mt ON ms.menu_tipo_id= mt.\"id\" 
												INNER JOIN permiso per 	ON per.menu_sistema_rol_id=msr.\"id\"
WHERE ms.sistema_tipo_id = $id_sistema
ORDER BY 2,3,4");
        $query->execute();

        $sistemamenuroltabla = $query->fetchAll();

        //  dump($sistemamenuroltabla);die;

        $response = new JsonResponse();
        return $response->setData(array('sistemamenu' => $menusArray,'sistemamenuroltabla'=>$sistemamenuroltabla));
        //dump($sistemamenu);die;
    }
    public function cargarRolesAction(Request $request){

        $idsistema =$request->get('sistema');
        //dump($idsistema);die;
        $em = $this->getDoctrine()->getManager();
        /*Obtiene la abreviatura del sistema seleccionado*/
        $query = $em->getConnection()->prepare("SELECT sistema_tipo.abreviatura FROM sistema_tipo WHERE sistema_tipo.\"id\" = $idsistema");
        $query->execute();
        $sist = $query->fetch();
        $siste=trim($sist['abreviatura']);
        //dump($siste);die;
        /*De a cuerdo al sistema seleccionado muestra sus roles:*/
        $query = $em->getConnection()->prepare("select a.id,a.rol,a.lugar_nivel_tipo_id,a.sub_sistema,a.diminutivo from rol_tipo a
                                                where a.sub_sistema like '%".$siste."%' or a.sub_sistema like '%*%'");
        $query->execute();
        $roles = $query->fetchAll();
        return $this->render('SieAppWebBundle:GestionMenu:menuRoles.html.twig',array( 'roles' => $roles));
        //dump($roles);die;

    }

    public function cargarMenuAction(Request $request){

        $id_menu =$request->get('id_menu');
        $id_sistema =$request->get('id_sistema');

        //dump($id_menu);die;
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare("WITH RECURSIVE EmpCTE (id, detalle_menu, ruta,menu_nivel_tipo_id, icono, menu_tipo_id,fecha_inicio,fecha_fin )
                AS (
	            SELECT DISTINCT a.id, a.detalle_menu,a.ruta,a.menu_nivel_tipo_id,a.icono,a.menu_tipo_id, b.fecha_inicio,b.fecha_fin
		        FROM menu_tipo a
		        LEFT JOIN menu_sistema b ON b.menu_tipo_id=a.id
		        WHERE a.id = $id_menu
	            UNION ALL
                --RECURSIVIDAD
	            SELECT e.id, e.detalle_menu,e.ruta,e.menu_nivel_tipo_id,e.icono,e.menu_tipo_id,date('now'),date('now')
		        FROM menu_tipo  AS e INNER JOIN EmpCTE AS m ON e.menu_tipo_id = m.id
                    )
                 -- Desplegar ResultSet
                SELECT * FROM EmpCTE");
        $query->execute();
        $arbolmenu = $query->fetchAll();
        //dump($arbolmenu);die;

        return $this->render('SieAppWebBundle:GestionMenu:listaSubMenus.html.twig',array('arbolmenu'=>$arbolmenu,'id_sistema'=>$id_sistema));
        //dump($arbolmenu);die;
    }

    public  function cargaSistemamenuRolesAction(Request $request){
        $id_sistema = $request->get('id_sistema');
        $id_menu = $request->get('id_menu');

        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare("SELECT sr.id as idsisrol , rt.id as idrol , rt.rol 
                                                                      FROM sistema_rol sr
			                                                          inner join rol_tipo rt ON sr.rol_tipo_id = rt.id 

                                                                      WHERE sr.sistema_tipo_id =$id_sistema  and sr.ID not in(SELECT menu_sistema_rol.sistema_rol_id --menu_sistema_rol.sistema_rol_id 
                                                                      from menu_sistema_rol 
                                                    WHERE menu_sistema_rol.menu_sistema_id =$id_menu)");
        $query->execute();
        $roldisponibles = $query->fetchAll();

        $rolesArray = array();
        for ($i = 0; $i < count($roldisponibles); $i++) {
            $rolesArray[$roldisponibles[$i]['idsisrol']] = $roldisponibles[$i]['rol'];
        }

        $response = new JsonResponse();
        return $response->setData(array('roles'=>$rolesArray));
    }

    public function cargalistasistemamenurolesAction(Request $request){
        $id_sistema = $request->get('id_sistema');
        $id_menu = $request->get('id_menu');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT msr.id,st.sistema,mt.detalle_menu,rt.rol,per._create,per._delete,per._read,per._update,msr.esactivo
                                                FROM menu_sistema_rol msr
                                                INNER JOIN menu_sistema ms ON msr.menu_sistema_id=ms.\"id\"
                                                INNER JOIN menu_tipo mt ON ms.menu_tipo_id=mt.\"id\"
                                                INNER JOIN sistema_tipo st ON st.\"id\"=ms.sistema_tipo_id
                                                INNER JOIN sistema_rol sr ON msr.sistema_rol_id = sr.\"id\"
                                                LEFT JOIN permiso per ON msr.\"id\" = per.menu_sistema_rol_id
                                                INNER JOIN rol_tipo rt ON sr.rol_tipo_id=rt.\"id\"
                                                WHERE msr.menu_sistema_id = $id_menu
                                                ORDER BY 4");
        $query->execute();
        $sistemamenupermiso = $query->fetchAll();

        return $this->render('SieAppWebBundle:GestionMenu:listaAsignaMenuSistemaRol.html.twig', array('sistemamenupermiso'=>$sistemamenupermiso,'id_sistema'=>$id_sistema));
    }

    public function createMenuSistemaRolAction(Request $request){
        $form = $request->get('form');
        $obs            = $form['observaciones'];

        if (isset($form['create'])){
            $crear = 1;
        }else{
            $crear = 0;
        }
        if (isset($form['read'])){
            $leer = 1;
        }else{
            $leer = 0;
        }
        if (isset($form['update'])){
            $actualizar = 1;
        }else{
            $actualizar = 0;
        }
        if (isset($form['delete'])){
            $eliminar = 1;
        }else{
            $eliminar = 0;
        }
        if (isset($form['esactivo'])){
            $esactivo = 1;
        }else{
            $esactivo = 0;
        }

        $id_sistema     = $form['sistema'];
        $id_menu        = $form['menu'];
        $id_rol         = $form['rol'];

        $fechaInicio    = $form['fechaInicio'];
        $fechaFinal     = $form['fechaFinal'];
        $obs            = $form['observaciones'];

        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare("SELECT menu_tipo_id from menu_sistema WHERE id=$id_menu AND sistema_tipo_id=$id_sistema");
        $query->execute();
        $menusistema = $query->fetch();
        $menutipoid = $menusistema['menu_tipo_id'];

        $query = $em->getConnection()->prepare("SELECT menu_tipo_id from menu_tipo WHERE id=$menutipoid");
        $query->execute();
        $menupadre = $query->fetch();
        $menupadreid = $menupadre['menu_tipo_id'];

        $query = $em->getConnection()->prepare("SELECT id from menu_sistema WHERE menu_tipo_id=$menupadreid AND sistema_tipo_id=$id_sistema");
        $query->execute();
        $menusistema = $query->fetch();
        $menusistemaid = $menusistema['id'];

        $query = $em->getConnection()->prepare("SELECT count(id) as pcant from menu_sistema_rol WHERE menu_sistema_id=$menusistemaid AND sistema_rol_id=$id_rol");
        $query->execute();
        $existepadrerol = $query->fetch();
        if ($existepadrerol['pcant']==0) {
            //Menu padre
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('menu_sistema_rol');")->execute();
            $menutipoid = $em->getRepository('SieAppWebBundle:MenuSistema')->find($menusistemaid);
            $rol        =   $em->getRepository('SieAppWebBundle:SistemaRol')->find($id_rol);

            $menusistemarol =new MenuSistemaRol();
            $menusistemarol->setMenuSistema($menutipoid);
            $menusistemarol->setSistemaRol($rol);
            $menusistemarol->setFechaInicio(new \DateTime($fechaInicio));
            $menusistemarol->setFechaFin(new \DateTime($fechaFinal));
            $menusistemarol->setEsactivo($esactivo);

            $em->persist($menusistemarol);
            $em->flush();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('permiso');")->execute();
            $permiso = new Permiso();
            $permiso->setMenuSistemaRolId($menusistemarol->getId());
            $permiso->setCreate($crear);
            $permiso->setRead($leer);
            $permiso->setUpdate($actualizar);
            $permiso->setDelete($eliminar);
            $permiso->setObs($obs);
            $em->persist($permiso);
            $em->flush();

            //Menu hijo
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('menu_sistema_rol');")->execute();
            $menutipoid = $em->getRepository('SieAppWebBundle:MenuSistema')->find($id_menu);
            $rol        =   $em->getRepository('SieAppWebBundle:SistemaRol')->find($id_rol);

            $menusistemarol =new MenuSistemaRol();
            $menusistemarol->setMenuSistema($menutipoid);
            $menusistemarol->setSistemaRol($rol);
            $menusistemarol->setFechaInicio(new \DateTime($fechaInicio));
            $menusistemarol->setFechaFin(new \DateTime($fechaFinal));
            $menusistemarol->setEsactivo($esactivo);

            $em->persist($menusistemarol);
            $em->flush();

            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('permiso');")->execute();
            $permiso = new Permiso();
            $permiso->setMenuSistemaRolId($menusistemarol->getId());
            $permiso->setCreate($crear);
            $permiso->setRead($leer);
            $permiso->setUpdate($actualizar);
            $permiso->setDelete($eliminar);
            $permiso->setObs($obs);
            $em->persist($permiso);
            $em->flush();
        } else {
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('menu_sistema_rol');")->execute();
            $menutipoid = $em->getRepository('SieAppWebBundle:MenuSistema')->find($id_menu);
            $rol        =   $em->getRepository('SieAppWebBundle:SistemaRol')->find($id_rol);

            $menusistemarol =new MenuSistemaRol();
            $menusistemarol->setMenuSistema($menutipoid);
            $menusistemarol->setSistemaRol($rol);
            $menusistemarol->setFechaInicio(new \DateTime($fechaInicio));
            $menusistemarol->setFechaFin(new \DateTime($fechaFinal));
            $menusistemarol->setEsactivo($esactivo);
            $em->persist($menusistemarol);
            $em->flush();

            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('permiso');")->execute();
            $permiso = new Permiso();
            $permiso->setMenuSistemaRolId($menusistemarol->getId());
            $permiso->setCreate($crear);
            $permiso->setRead($leer);
            $permiso->setUpdate($actualizar);
            $permiso->setDelete($eliminar);
            $permiso->setObs($obs);
            $em->persist($permiso);
            $em->flush();
        }
        $query = $em->getConnection()->prepare("SELECT msr.id,st.sistema,mt.detalle_menu,rt.rol,per._create,per._delete,per._read,per._update,msr.esactivo
                                                FROM menu_sistema_rol msr
                                                INNER JOIN menu_sistema ms ON msr.menu_sistema_id=ms.id
                                                INNER JOIN menu_tipo mt ON ms.menu_tipo_id=mt.id
                                                INNER JOIN sistema_tipo st ON st.id=ms.sistema_tipo_id
                                                INNER JOIN sistema_rol sr ON msr.sistema_rol_id = sr.id
                                                LEFT JOIN permiso per ON msr.id = per.menu_sistema_rol_id
                                                INNER JOIN rol_tipo rt ON sr.rol_tipo_id=rt.id
                                                WHERE msr.menu_sistema_id = $id_menu
                                                ORDER BY 4");
        $query->execute();
        $sistemamenupermiso = $query->fetchAll();

        return $this->render('SieAppWebBundle:GestionMenu:listaAsignaMenuSistemaRol.html.twig', array('sistemamenupermiso'=>$sistemamenupermiso,'id_sistema'=>$id_sistema));
    }

    public function cambiaestadoAction(Request $request){
        $idmsr = $request->get('idmsr');
        $id_sistema = $request->get('idsis');

        $em = $this->getDoctrine()->getManager();
        $menusistemarol = $em->getRepository('SieAppWebBundle:MenuSistemaRol')->find($idmsr);

        if ($menusistemarol->getEsactivo()==1){
            $menusistemarol->setEsactivo(0);
            $em->persist($menusistemarol);
            $em->flush();
        }
        else{
            $menusistemarol->setEsactivo(1);
            $em->persist($menusistemarol);
            $em->flush();
        }

        $id_msr=$menusistemarol->getId();
        $query = $em->getConnection()->prepare("SELECT ms.id
                                                FROM menu_sistema_rol msr 
                                                INNER JOIN menu_sistema ms ON msr.menu_sistema_id=ms.\"id\"
                                                WHERE msr.id = $id_msr");
        $query->execute();
        $menu = $query->fetch();
        $id_menu=$menu['id'];
        $query = $em->getConnection()->prepare("SELECT msr.id,st.sistema,mt.detalle_menu,rt.rol,per._create,per._delete,per._read,per._update,msr.esactivo
                                                FROM menu_sistema_rol msr
                                                INNER JOIN menu_sistema ms ON msr.menu_sistema_id=ms.\"id\"
                                                INNER JOIN menu_tipo mt ON ms.menu_tipo_id=mt.\"id\"
                                                INNER JOIN sistema_tipo st ON st.\"id\"=ms.sistema_tipo_id
                                                INNER JOIN sistema_rol sr ON msr.sistema_rol_id = sr.\"id\"
                                                INNER JOIN permiso per ON msr.\"id\" = per.menu_sistema_rol_id
                                                INNER JOIN rol_tipo rt ON sr.rol_tipo_id=rt.\"id\"
                                                WHERE msr.menu_sistema_id = $id_menu
                                                ORDER BY 4");
        $query->execute();
        $sistemamenupermiso = $query->fetchAll();

        return $this->render('SieAppWebBundle:GestionMenu:listaAsignaMenuSistemaRol.html.twig', array('sistemamenupermiso'=>$sistemamenupermiso,'id_sistema'=>$id_sistema));
    }

    public function cambiaestadocreateAction(Request $request){

        $idmsr = $request->get('idmsr');
        $id_sistema = $request->get('idsis');
        $em = $this->getDoctrine()->getManager();
        $menusistemarol = $em->getRepository('SieAppWebBundle:MenuSistemaRol')->find($idmsr);
        $id_menusistemarol=$menusistemarol->getId();
        $permiso = $em->getRepository('SieAppWebBundle:Permiso')->findOneBy(array('menuSistemaRol'=> $id_menusistemarol));

        if ($permiso->getCreate()==1){
            $permiso->setCreate(0);
            $em->persist($permiso);
            $em->flush();
        }
        else{
            $permiso->setCreate(1);
            $em->persist($permiso);
            $em->flush();
        }

        $query = $em->getConnection()->prepare("SELECT msr.id,st.sistema,mt.detalle_menu,rtp.rol,per.\"_create\",per.\"_delete\",per.\"_read\",per.\"_update\",msr.esactivo 
                                                from menu_sistema_rol msr 
												INNER JOIN menu_sistema ms ON msr.menu_sistema_id = ms.id 
												INNER JOIN sistema_tipo st ON ms.sistema_tipo_id = st.\"id\"
												INNER JOIN sistema_rol sr ON msr.sistema_rol_id = sr.\"id\"
												INNER JOIN rol_tipo rtp ON sr.rol_tipo_id=rtp.\"id\"
												INNER JOIN menu_tipo mt ON ms.menu_tipo_id= mt.\"id\" 
												INNER JOIN permiso per 	ON per.menu_sistema_rol_id=msr.\"id\"
                                                WHERE ms.sistema_tipo_id = $id_sistema
                                                ORDER BY 2,3,4 ");
        $query->execute();
        $sistemamenupermiso = $query->fetchAll();

        return $this->render('SieAppWebBundle:GestionMenu:listaAsignaMenuSistemaRol.html.twig', array('sistemamenupermiso'=>$sistemamenupermiso,'id_sistema'=>$id_sistema));

    }

    public function cambiaestadoreadAction(Request $request){
        $idmsr = $request->get('idmsr');
        $id_sistema = $request->get('idsis');
        $em = $this->getDoctrine()->getManager();
        $menusistemarol = $em->getRepository('SieAppWebBundle:MenuSistemaRol')->find($idmsr);
        $id_menusistemarol=$menusistemarol->getId();
        $permiso = $em->getRepository('SieAppWebBundle:Permiso')->findOneBy(array('menuSistemaRol'=> $id_menusistemarol));

        if ($permiso->getRead()==1){
            $permiso->setRead(0);
            $em->persist($permiso);
            $em->flush();
        }
        else{
            $permiso->setRead(1);
            $em->persist($permiso);
            $em->flush();
        }

        $query = $em->getConnection()->prepare("SELECT msr.id,st.sistema,mt.detalle_menu,rtp.rol,per._create,per._delete,per._read,per._update,msr.esactivo 
                                                from menu_sistema_rol msr 
												INNER JOIN menu_sistema ms ON msr.menu_sistema_id = ms.id 
												INNER JOIN sistema_tipo st ON ms.sistema_tipo_id = st.id
												INNER JOIN sistema_rol sr ON msr.sistema_rol_id = sr.id
												INNER JOIN rol_tipo rtp ON sr.rol_tipo_id=rtp.id
												INNER JOIN menu_tipo mt ON ms.menu_tipo_id= mt.id 
												INNER JOIN permiso per 	ON per.menu_sistema_rol_id=msr.id
                                                WHERE ms.sistema_tipo_id = $id_sistema
                                                ORDER BY 2,3,4 ");
        $query->execute();
        $sistemamenupermiso = $query->fetchAll();

        return $this->render('SieAppWebBundle:GestionMenu:listaAsignaMenuSistemaRol.html.twig', array('sistemamenupermiso'=>$sistemamenupermiso,'id_sistema'=>$id_sistema));


    }
    public function cambiaestadoupdateAction(Request $request){
        $idmsr = $request->get('idmsr');
        $id_sistema = $request->get('idsis');
        $em = $this->getDoctrine()->getManager();
        $menusistemarol = $em->getRepository('SieAppWebBundle:MenuSistemaRol')->find($idmsr);
        $id_menusistemarol=$menusistemarol->getId();
        $permiso = $em->getRepository('SieAppWebBundle:Permiso')->findOneBy(array('menuSistemaRol'=> $id_menusistemarol));

        if ($permiso->getUpdate()==1){
            $permiso->setUpdate(0);
            $em->persist($permiso);
            $em->flush();
        }
        else{
            $permiso->setUpdate(1);
            $em->persist($permiso);
            $em->flush();
        }

        $query = $em->getConnection()->prepare("SELECT msr.id,st.sistema,mt.detalle_menu,rtp.rol,per._create,per._delete,per._read,per._update,msr.esactivo 
                                                from menu_sistema_rol msr 
												INNER JOIN menu_sistema ms ON msr.menu_sistema_id = ms.id 
												INNER JOIN sistema_tipo st ON ms.sistema_tipo_id = st.id
												INNER JOIN sistema_rol sr ON msr.sistema_rol_id = sr.id
												INNER JOIN rol_tipo rtp ON sr.rol_tipo_id=rtp.id
												INNER JOIN menu_tipo mt ON ms.menu_tipo_id= mt.id 
												INNER JOIN permiso per 	ON per.menu_sistema_rol_id=msr.id
                                                WHERE ms.sistema_tipo_id = $id_sistema
                                                ORDER BY 2,3,4");
        $query->execute();
        $sistemamenupermiso = $query->fetchAll();

        return $this->render('SieAppWebBundle:GestionMenu:listaAsignaMenuSistemaRol.html.twig', array('sistemamenupermiso'=>$sistemamenupermiso,'id_sistema'=>$id_sistema));


    }
    public function cambiaestadodeleteAction(Request $request){
        $idmsr = $request->get('idmsr');
        $id_sistema = $request->get('idsis');
        $em = $this->getDoctrine()->getManager();
        $menusistemarol = $em->getRepository('SieAppWebBundle:MenuSistemaRol')->find($idmsr);
        $id_menusistemarol=$menusistemarol->getId();
        $permiso = $em->getRepository('SieAppWebBundle:Permiso')->findOneBy(array('menuSistemaRol'=> $id_menusistemarol));

        if ($permiso->getDelete()==1){
            $permiso->setDelete(0);
            $em->persist($permiso);
            $em->flush();
        }
        else{
            $permiso->setDelete(1);
            $em->persist($permiso);
            $em->flush();
        }

        $query = $em->getConnection()->prepare("SELECT msr.id,st.sistema,mt.detalle_menu,rtp.rol,per._create,per._delete,per._read,per._update,msr.esactivo 
                                                from menu_sistema_rol msr 
												INNER JOIN menu_sistema ms ON msr.menu_sistema_id = ms.id 
												INNER JOIN sistema_tipo st ON ms.sistema_tipo_id = st.id
												INNER JOIN sistema_rol sr ON msr.sistema_rol_id = sr.id
												INNER JOIN rol_tipo rtp ON sr.rol_tipo_id=rtp.id
												INNER JOIN menu_tipo mt ON ms.menu_tipo_id= mt.id 
												INNER JOIN permiso per 	ON per.menu_sistema_rol_id=msr.id
                                                WHERE ms.sistema_tipo_id = $id_sistema
                                                ORDER BY 2,3,4 ");
        $query->execute();
        $sistemamenupermiso = $query->fetchAll();

        return $this->render('SieAppWebBundle:GestionMenu:listaAsignaMenuSistemaRol.html.twig', array('sistemamenupermiso'=>$sistemamenupermiso,'id_sistema'=>$id_sistema));


    }

    public function eliminamenusistemarolAction(Request $request){
        $idmsr = $request->get('idmsr');
        $id_sistema = $request->get('id_sistema');
        $id_rol = $request->get('id_rol');
        $tipo_menu = $request->get('tipo_menu');
        $em = $this->getDoctrine()->getManager();

       /* $query = $em->getConnection()->prepare("SELECT ms.id
                                                FROM menu_sistema_rol msr 
                                                INNER JOIN menu_sistema ms ON msr.menu_sistema_id=ms.id
                                                WHERE msr.id = $idmsr");
        $query->execute();
        $menu = $query->fetch();
        $menusistemaid=$menu['id'];*/
        //Se sabe que es padre, entonces buscamos los submenus
        if ($tipo_menu==0) {
            $query = $em->getConnection()->prepare("SELECT menu_sistema.menu_tipo_id
            FROM menu_sistema WHERE menu_sistema.id IN (SELECT menu_sistema_rol.menu_sistema_id 
            FROM menu_sistema_rol WHERE menu_sistema_rol.id=$idmsr) AND menu_sistema.sistema_tipo_id=$id_sistema");
            $query->execute();
            $menutipo = $query->fetch();
            $menutipoid = $menutipo['menu_tipo_id'];

            $query = $em->getConnection()->prepare("SELECT COUNT (msr.id) AS hcant FROM menu_sistema_rol  msr 
                                                INNER JOIN sistema_rol sr ON msr.sistema_rol_id = sr.id
                                                INNER JOIN menu_sistema ms ON msr.menu_sistema_id=ms.id
                                                INNER JOIN menu_tipo mt ON ms.menu_tipo_id=mt.id
                                                INNER JOIN sistema_tipo sti ON sti.id = ms.sistema_tipo_id
                                                INNER JOIN rol_tipo rtip ON rtip.id =sr.rol_tipo_id   
                                                WHERE sti.id = $id_sistema AND mt.menu_tipo_id = $menutipoid AND rtip.id = $id_rol");
            $query->execute();
            $resultado = $query->fetch();
            if ($resultado['hcant']>0) {
                return new Response('no');
            } else {
                $permiso = $em->getRepository('SieAppWebBundle:Permiso')->findOneBy(array('menuSistemaRolId'=> $idmsr));
                $menusistemarol = $em->getRepository('SieAppWebBundle:MenuSistemaRol')->find($idmsr);
                $em->remove($permiso);
                $em->flush();
                $em->remove($menusistemarol);
                $em->flush();
                $mensaje = 'La asignación fue eliminada con éxito';
            }
        } else {
            $permiso = $em->getRepository('SieAppWebBundle:Permiso')->findOneBy(array('menuSistemaRolId'=> $idmsr));
            $menusistemarol = $em->getRepository('SieAppWebBundle:MenuSistemaRol')->find($idmsr);
            $em->remove($permiso);
            $em->flush();
            $em->remove($menusistemarol);
            $em->flush();
            $mensaje = 'La asignación fue eliminada con éxito';
        }

        $request->getSession()
            ->getFlashBag()
            ->add('exito', $mensaje);

        $query = $em->getConnection()->prepare("SELECT msr.id,st.sistema,mt.detalle_menu,mt.icono,mt.ruta,mt.menu_tipo_id,rt.rol,rt.id as rol_id,msr.esactivo
                                                FROM menu_sistema_rol msr
                                                INNER JOIN menu_sistema ms ON msr.menu_sistema_id=ms.id
                                                INNER JOIN menu_tipo mt ON ms.menu_tipo_id=mt.id
                                                INNER JOIN sistema_tipo st ON st.id=ms.sistema_tipo_id
                                                INNER JOIN sistema_rol sr ON msr.sistema_rol_id = sr.id
                                                INNER JOIN rol_tipo rt ON sr.rol_tipo_id=rt.id
                                                WHERE ms.sistema_tipo_id = $id_sistema
                                                ORDER BY 4");
        $query->execute();
        $sistemamenupermiso = $query->fetchAll();
        return $this->render('SieAppWebBundle:GestionMenu:generaListaSistemamenuRol.html.twig', array('listaSistemamenurol'=>$sistemamenupermiso,'id_sistema'=>$id_sistema));
        /*return $this->render('SieAppWebBundle:GestionMenu:listaAsignaMenuSistemaRol.html.twig', array('sistemamenupermiso'=>$sistemamenupermiso,'id_sistema'=>$id_sistema));*/
    }
    public function verificasubmenusAction(Request $request){
        $em=$this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT count(*) as cantidad
                                                from menu_tipo mt --ORDER BY 1
                                                WHERE mt.menu_tipo_id = 276 ");
        $query->execute();
        $cantidadsub = $query->fetch();
        return($cantidadsub);
    }

//MODULO GENERACION DE MENUS
    /**
     *Funcion para generar los menús en el sistema de tramites
     */
    public function generamenuAction($rol_tipo_id, $idsistema, $userId){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select * from get_objeto_menu_usuario($userId::INT,'$idsistema'::VARCHAR,$rol_tipo_id::INT) as v");
        $query->execute();
        $menu_arboles = $query->fetchAll();
        /**
         * Adecuacion para dibijar los menús en diferentes plantillas
         */
        if ($idsistema!=1 ){
            return $this->render(
                'SieAppWebBundle:GestionMenu:list_menu.html.twig', array('menu_arboles' => $menu_arboles, 'rol_tipo_id' => $rol_tipo_id, 'sistema' => $idsistema)
            );
        }else{
            return $this->render(
                'SieAppWebBundle:GestionMenu:list_menu_siged.html.twig', array('menu_arboles' => $menu_arboles, 'rol_tipo_id' => $rol_tipo_id, 'sistema' => $idsistema)
            );
        }

    }
    /**
     *Funcion para generar los menús en los demás sistemas
     */

    public function generamenuSistemaAction($rol_tipo_id, $idsistema, $userId){//dump($rol_tipo_id, $idsistema, $userId);die;
        /*Generacion de menús para los demás sistemas*/
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT mt.id,sti.sistema, rtip.rol, ms.detalle_menu,mt.ruta,mt.icono,mt.id,mt.menu_tipo_id,msr.esactivo,mt.menu_nivel_tipo_id
                                                FROM menu_sistema_rol  msr 
                                                INNER JOIN sistema_rol sr ON msr.sistema_rol_id = sr.id
                                                INNER JOIN menu_sistema ms ON msr.menu_sistema_id=ms.id
                                                INNER JOIN menu_tipo mt ON ms.menu_tipo_id=mt.id
                                                INNER JOIN sistema_tipo sti ON sti.id = ms.sistema_tipo_id
                                                INNER JOIN rol_tipo rtip ON rtip.id =sr.rol_tipo_id   
                                                WHERE sti.id = $idsistema  and rtip.id  = $rol_tipo_id AND msr.esactivo = TRUE
                                                ORDER BY 7,8");
        $query->execute();
        $menu_arboles = $query->fetchAll();//dump($menu_arboles);die;
        $query = $em->getConnection()->prepare("SELECT TRIM(usuario_rol.sub_sistema)as sub_sistema
                                                FROM usuario_rol
                                                WHERE  usuario_rol.rol_tipo_id=$rol_tipo_id
                                                AND usuario_rol.usuario_id=$userId");
        $query->execute();
        $subsistemas = $query->fetch();
        $cadena=$subsistemas['sub_sistema'];
        $sistemas = explode(",", $cadena);
        $query = $em->getConnection()->prepare("SELECT TRIM(sistema_tipo.abreviatura) AS abreviatura from sistema_tipo WHERE sistema_tipo.id=$idsistema");
        $query->execute();
        $abreviatura = $query->fetch();
        for ($i = 0; $i < count($sistemas); $i++) {
            if($sistemas[$i] == $abreviatura['abreviatura'] OR  $sistemas[$i]=='*' OR $rol_tipo_id==8)
            { $sw=1; break; }
            else{$sw=0;
            }
        }
        if($sw==1  )
        {
            /*
             * Adecuación de menus Para diferentes Plantillas*/
            if ($idsistema==1){
                return $this->render(
                    'SieAppWebBundle:GestionMenu:list_menu_siged.html.twig', array('menu_arboles' => $menu_arboles, 'rol_tipo_id' => $rol_tipo_id, 'sistema' => $idsistema)
                );
            }else{
                // dump($menu_arboles);die;
                return $this->render(
                    'SieAppWebBundle:GestionMenu:list_menu.html.twig', array('menu_arboles' => $menu_arboles, 'rol_tipo_id' => $rol_tipo_id, 'sistema' => $idsistema)
                );
            }
        }
        else{
            return new Response('');
        }
    }
//LISTA DE MENUS X SISTEMA
    public function listasistemamenurolAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT stipo.id,stipo.sistema from sistema_tipo stipo
                                                    ORDER BY 1');
        $query->execute();
        $listasistematipo = $query->fetchAll();

        $sistArray = array();
        for ($i = 0; $i < count($listasistematipo); $i++) {
            $sistArray[$listasistematipo[$i]['id']] = $listasistematipo[$i]['sistema'];
        }

        $form= $this->createFormBuilder()
            ->add('sistema', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $sistArray, 'attr' => array('class' => 'chosen-select','onchange' => 'cargarListaSistemaMenuRol()')))
            ->getForm();
        //return $this->render('SieAppWebBundle:GestionMenu:asignacionRolSistema.html.twig',array( 'form' => $form->createView()));


        return $this->render(
            'SieAppWebBundle:GestionMenu:listaSistemasMenusRoles.html.twig',array( 'form' => $form->createView()));
    }

    public function generalistasistemamenurolAction(Request $request){
        $idsistema = $request->get('id_sistema');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT msr.id,sti.sistema,ms.detalle_menu,mt.menu_tipo_id,mt.icono,mt.ruta,rtip.id as rol_id,rtip.rol,mt.menu_nivel_tipo_id,msr.esactivo
                                                FROM menu_sistema_rol  msr 
                                                INNER JOIN sistema_rol sr ON msr.sistema_rol_id = sr.id
                                                INNER JOIN menu_sistema ms ON msr.menu_sistema_id=ms.id
                                                INNER JOIN menu_tipo mt ON ms.menu_tipo_id=mt.id
                                                INNER JOIN sistema_tipo sti ON sti.id = ms.sistema_tipo_id
                                                INNER JOIN rol_tipo rtip ON rtip.id =sr.rol_tipo_id   
                                                WHERE sti.id = $idsistema  
                                                ORDER BY 3");
        $query->execute();
        $listaSistemamenurol = $query->fetchAll();
        return $this->render(
            'SieAppWebBundle:GestionMenu:generaListaSistemamenuRol.html.twig',array( 'listaSistemamenurol' => $listaSistemamenurol,'id_sistema'=>$idsistema));
    }


    public function generareportemenuAction(Request $request)
    {
        /*$idsistema = $request->get('id_sistema');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT msr.id,sti.sistema, ms.detalle_menu,mt.icono,mt.ruta,rtip.rol,mt.menu_nivel_tipo_id,msr.esactivo
                                                FROM menu_sistema_rol  msr
                                                INNER JOIN sistema_rol sr ON msr.sistema_rol_id = sr.id
                                                INNER JOIN menu_sistema ms ON msr.menu_sistema_id=ms.id
                                                INNER JOIN menu_tipo mt ON ms.menu_tipo_id=mt.id
                                                INNER JOIN sistema_tipo sti ON sti.id = ms.sistema_tipo_id
                                                INNER JOIN rol_tipo rtip ON rtip.id =sr.rol_tipo_id
                                                WHERE sti.id = $idsistema
                                                ORDER BY 3");
        $query->execute();
        $listaSistemamenurol = $query->fetchAll();
        //dump($listaSistemamenurol);die;
        //return new Response("eeee");*/
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT stipo.id,stipo.sistema from sistema_tipo stipo
                                                    ORDER BY 1');
        $query->execute();
        $listasistematipo = $query->fetchAll();

        $sistArray = array();
        for ($i = 0; $i < count($listasistematipo); $i++) {
            $sistArray[$listasistematipo[$i]['id']] = $listasistematipo[$i]['sistema'];
        }

        $form= $this->createFormBuilder()
            ->add('sistema', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $sistArray, 'attr' => array('class' => 'chosen-select','onchange' => 'cargarListaSistemaMenuRol()')))
            ->getForm();
        //return $this->render('SieAppWebBundle:GestionMenu:asignacionRolSistema.html.twig',array( 'form' => $form->createView()));


        return $this->render(
            'SieAppWebBundle:GestionMenu:listaSistemasMenusRoles.html.twig',array( 'form' => $form->createView()));
        return $this->render(
            'SieAppWebBundle:GestionMenu:reportesMenus.html.twig');
    }

        public function cambiaestadolistasistemamenurolAction(Request $request){
        // dump($request);die;
        $idmsr = $request->get('idmsr');
        $id_sistema = $request->get('idsis');

        $em = $this->getDoctrine()->getManager();
        $menusistemarol = $em->getRepository('SieAppWebBundle:MenuSistemaRol')->find($idmsr);

        if ($menusistemarol->getEsactivo()==1){
            $menusistemarol->setEsactivo(0);
            $em->persist($menusistemarol);
            $em->flush();
        }
        else{
            $menusistemarol->setEsactivo(1);
            $em->persist($menusistemarol);
            $em->flush();
        }
        return $this->redirectToRoute('gestionmenu_lista_sistema_menu_rol', array('id_sistema'=>$id_sistema));
        // return $this->render('SieAppWebBundle:GestionMenu:generaListaSistemamenuRol.html.twig',array( 'listaSistemamenurol' => $listaSistemamenurol,'id_sistema'=>$id_sistema));

    }

    public function iconosAction(){
        return $this->render(
            'SieAppWebBundle:GestionMenu:_gestion.html.twig');
    }
    public function validarolesasigandosAction(Request $request){
        $idrol = $request->get('idrol');
        $id_sistema = $request->get('id_sistema');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT count(*) 
                                                FROM sistema_rol
                                                WHERE sistema_rol.sistema_tipo_id=$id_sistema AND sistema_rol.rol_tipo_id=$idrol");
        $query->execute();
        $lista = $query->fetch();
        $cant = $lista['count'];
        return new Response($cant);
    }

    public function validamenusasigandosAction(Request $request){
        //dump($request);die;
        $idmenu = $request->get('idmenu');
        $idsistema = $request->get('id_sistema');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT count(*) AS cantidad 
                                                FROM menu_sistema
                                                WHERE menu_sistema.sistema_tipo_id=$idsistema AND menu_sistema.menu_tipo_id=$idmenu");
        $query->execute();
        $lista = $query->fetch();
        $cant = $lista['cantidad'];
        return new Response($cant);
    }


}
