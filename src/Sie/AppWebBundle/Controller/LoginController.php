<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Sie\AppWebBundle\Entity\Usuario;
use Sie\AppWebBundle\Entity\UsuarioSession;

class LoginController extends Controller {
    
    /**
     * create the view interface to login with some rol
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {        
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();      
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        //******************        
        /*if ($request->get('form')) {
            $form = $request->get('form');
            $rolUserId = $form['roluser'];
            $rolentity = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario' => $sesion->get('userId'),'rolTipo' => $rolUserId));
            $roluserlugarid = $rolentity[0]->getLugarTipo()->getId();
            $sesion->set('roluser', $rolUserId);
            $sesion->set('roluserlugarid', $roluserlugarid);
        } else {
            $rolUserId = $sesion->get('roluser');
        }*/
        
        //****SE OBTIENE EL ID DEL ROL DEL USUARIO 
        $rolUserId = $sesion->get('roluser');
        //****FIN DE SE OBTIENE EL ID DEL ROL DEL USUARIO 
        //******************
               
        //****************
        //****SE GENERAN LOS MENUS PARA EL SIGEN EN BASE AL ID DEL ROL DEL USUARIO
        /*$query = $em->getConnection()->prepare('SELECT get_objeto_menu (:rol_id::INT)');
        $query->bindValue(':rol_id', $rolUserId);
        $query->execute();
        $aMenuUser = $query->fetchAll(); 
        if (sizeof($aMenuUser) > 0) {            
                foreach ($aMenuUser as $m) {
                    $menu = $m['get_objeto_menu'];
                    $menu = str_replace(array('(', ')', '"'), '', $menu);
                    $element = explode(',', $menu);

                    $aBuildMenu[] = array(
                        'sistema_tipo_id'=>$element[0],
                        'sistema'=>$element[1],
                        'objeto_tipo_id' => $element[2],
                        'objeto_tipo_icono'=>$element[3],
                        'icono' => $element[4],
                        'menu_tipo_id' => $element[5],
                        'nombre' => $element[6],
                        'menu_tipo_icono'=>$element[7],
                        'ruta' => $element[8],
                        'obs' => $element[9],
                        'menu_objeto_id' => $element[10],
                        'menu_objeto_esactivo'=>$element[11],
                        'permiso_id' => $element[12],
                        'permiso' => $element[13],
                        '_create' => $element[14],
                        '_read' => $element[15],
                        '_delete' => $element[16],
                        '_update' => $element[17],
                        'rol_permiso_id' => $element[18],
                        'rol_tipo_id' => $element[19],
                        'rol' => $element[20],
                        'objeto_tipo_activo' => $element[21],
                    );
                }
            $i = 0;
            $limit = count($aBuildMenu);
            $optionMenu = array();
            while ($i < $limit) {
                $optionMenu[$aBuildMenu[$i]['objeto_tipo_icono']][] = array('label' => $aBuildMenu[$i]['nombre'], 'status' => $aBuildMenu[$i]['menu_objeto_esactivo'], 'ruta' => $aBuildMenu[$i]['ruta'],'icono'=>$aBuildMenu[$i]['menu_tipo_icono']);
                $i++;
            }
            //set some values fot the view template
            //$sesion->set('aMenuOption', $aBuildMenu);
            $sesion->set('aMenu', $optionMenu);
        }*/
        //****FIN SE GENERAN LOS MENUS PARA EL SIGED EN BASE AL ID DEL ROL DEL USUARIO
        //****************
        
        //****************
        //****REDIRECCIONA SEGUN EL TIPO DE SUBSISTEMA 
        /*switch ($sesion->get('tiposubsistema')) {
            case 1:
                $sesion->set('pathSystem', "SieHerramientaBundle");                
                break;
            case 2:
                $sesion->set('pathSystem', "SieHerramientaAlternativaBundle");                
                break;
            case 4:
                $sesion->set('pathSystem', "SieEspecialBundle");                
                break;
        }*/
        //****REDIRECCIONA SEGUN EL TIPO DE SUBSISTEMA 
        //****************

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
        $sesionUsuario->setObservaciones($sesion->get('pathSystem').'-/-'.$_SERVER['HTTP_USER_AGENT']);        
        $sesionUsuario->setRolTipoId($rolUserId);        
        $em->persist($sesionUsuario);
        $em->flush();
        //**** FIN DE SE REGISTRA LA SESSION DEL USUARIO
        //************ 

        return $this->redirect($this->generateUrl('principal_web'));

        //**************************        
        /*if ( ($rolUserId == 2) || ($rolUserId == 9) ){
            $objUeinfo = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->getUserSieDirGest($sesion->get('personaId'), '2017','2');
            if (count($objUeinfo) == 1){
                $sesion->set('ie_per_estado', '-1');
                $sesion->set('ie_id', $objUeinfo[0]['id']);
                $sesion->set('ie_nombre',$em->getRepository('SieAppWebBundle:Institucioneducativa')->find($objUeinfo[0]['id'])->getInstitucioneducativa());                
                $cuenta = $em->getRepository('SieAppWebBundle:RolTipo')->find($rolUserId);
                $sesion->set('cuentauser', $cuenta->getRol());
                return $this->redirect($this->generateUrl('principal_web'));
            }            
            if (count($objUeinfo) > 1){
                return $this->render('SieAppWebBundle:Login:unidades.html.twig', array('objUeinfo' => $objUeinfo));                    
            }
            if (count($objUeinfo) == 0){
                $sesion->getFlashBag()->add('info', 'Aún no se ha establecido cargo como director para la presente gestión, comuníquese con su Téc. Distrital para que a través del módulo "Gestión Administrativos" se actualice su vigencia.');
                return $this->redirect($this->generateURL('login'));
            }        
        }else{
            $sesion->set('ie_id', '-1');
            $sesion->set('ie_per_estado', '-1');
            $sesion->set('ie_nombre', 'Seleccionar CEA');
            $cuenta = $em->getRepository('SieAppWebBundle:RolTipo')->find($rolUserId);
            $sesion->set('cuentauser', $cuenta->getRol());
            return $this->redirect($this->generateUrl('principal_web'));
        }*/
    }
    
    /*public function dirselunieduAction(Request $request, $ue) {
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $sesion->set('ie_id', $ue);
        $sesion->set('ie_per_estado', '-1');
        $sesion->set('ie_nombre', $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ue)->getInstitucioneducativa());
        $cuenta = $em->getRepository('SieAppWebBundle:RolTipo')->find($sesion->get('roluser'));
        $sesion->set('cuentauser', $cuenta->getRol());
        return $this->redirect($this->generateUrl('principal_web'));
    }*/

    public function sieAction() {
        return $this->redirectToRoute('login');
    }

    public function userlogedAction(Request $request, $persona, $aOptionUser) {
        return $this->render('SieAppWebBundle:WelcomeSiged:index.html.twig', array('persona' => $persona, 'aOptionUser' => $aOptionUser));
    }

    /**
     * build the login interface
     * @param Request $request
     * @return type
     */
    public function logoutAction(Request $request) {
        $sesion = $request->getSession();
        $sesion->clear();
        $usuario = new Usuario();
        $form = $this->createFormBuilder($usuario)
                ->setAction($this->generateUrl('sie_login_homepage'))
                ->add('username', 'text', array('required' => true, 'invalid_message' => 'Campor 1 obligatorio'))
                ->add('password', 'password', array('required' => true, 'invalid_message' => 'Campor 2 obligatorio'))
                ->add('check', 'checkbox', array('mapped' => false, 'label' => 'Omitir esta validacion xd', 'required' => false))
                ->add('captcha', 'captcha', array('label' => 'Registre la imagen',
                    'width' => 250,
                    'height' => 70,
                    'length' => 6,
                    'required' => false,
                    'invalid_message' => 'The captcha code is invalid.'
                ))
                ->add('save', 'submit', array('label' => 'Aceptar'))
                ->getForm();
        return $this->render('SieAppWebBundle:Login:login.html.twig', array("form" => $form->createView()));
    }
}
