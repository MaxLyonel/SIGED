<?php

namespace Sie\UsuariosBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

use Sie\UsuariosBundle\Form\RolNewType;

use Sie\AppWebBundle\Entity\RolRolesAsignacion;

class RolAdminController extends Controller
{
    private $session;
    //EJEMPLO DE CI DUPLICADO 34523
    //EJEMPLO DE CI SIN DUPLICADOS 356597
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    public function roladminAction()
    {
        $em = $this->getDoctrine()->getManager();

        $roles = $em->getRepository('SieAppWebBundle:RolRolesAsignacion')->findall();

        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();            
        $query = "  select a.*, b.rol as rola, c.rol as rolb
                     from rol_roles_asignacion a  
                        inner join rol_tipo b on a.rol_id = b.id
                        inner join rol_tipo c on a.roles = c.id

                ";
        $stmt = $db->prepare($query);        
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        //dump($po);die;
        /*if (!$po) {

        }*/   
        //dump($roles); die;
        return $this->render('SieUsuariosBundle:RolAdmin:index.html.twig', array(
                'roles'   => $po,                
            ));
    }    
    
    public function rolnuevoAction()
    {
        $form = $this->createForm(new RolNewType(), null, array('action' => $this->generateUrl('sie_usuarios_rol_insert'), 'method' => 'POST',));

        return $this->render('SieUsuariosBundle:RolAdmin:RolForm.html.twig', array(            
            'form'   => $form->createView(),
        ));
    }
    
    public function rolinsertAction(Request $request){
        $form = $this->createForm(new RolNewType());        
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager(); 
            $em->getConnection()->beginTransaction();
            $data = $form->getData();
            try {
                //print_r($data);
                //die;
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('rol_roles_asignacion');");
                $query->execute();
                $rolasignacion = new RolRolesAsignacion();                
                $rolasignacion->setRol($this->getDoctrine()->getRepository('SieAppWebBundle:RolTipo')->find($data['rolTipoadmin']));
                $rolasignacion->setRoles($this->getDoctrine()->getRepository('SieAppWebBundle:RolTipo')->find($data['rolTipoasign']));
                $em->persist($rolasignacion);
                $em->flush();
                
                $em->getConnection()->commit();                
                
                $this->session->getFlashBag()->add('success', 'Proceso realizado exitosamente.');
                return $this->redirect($this->generateUrl('sie_usuarios_rol_admin'));
                } 
            catch (Exception $ex) {
                $em->getConnection()->rollback();
                
                $this->session->getFlashBag()->add('error', 'Proceso detenido. Se ha detectado inconsistencia de datos.');
                return $this->redirect($this->generateUrl('sie_usuarios_rol_admin'));
                }
        }
    }
    
    public function roldeleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $entity = $em->getRepository('SieAppWebBundle:RolRolesAsignacion')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Rol Asignacion entity.');
            }

            $em->remove($entity);
            $em->flush();
                
            $em->getConnection()->commit();                
                
            $this->session->getFlashBag()->add('success', 'Proceso realizado exitosamente.');
            return $this->redirect($this->generateUrl('sie_usuarios_rol_admin'));
            } 
        catch (Exception $ex) {
            $em->getConnection()->rollback();
                
            $this->session->getFlashBag()->add('error', 'Proceso detenido. Se ha detectado inconsistencia de datos.');
            return $this->redirect($this->generateUrl('sie_usuarios_rol_admin'));
            }
    }

    public function rolchangeinhotAction(){
        $rolselected = $this->get('login')->verificarRolesActivos($this->session->get('personaId'),'-1');

        return $this->render('SieAppWebBundle:Login:rolesunidades.html.twig', 
        array(
            'user' => $this->session->get('userName'),
            'carnet' => $this->session->get('userName') ,
            'roles' => $rolselected, 
            'persona' => $this->session->get('name').' '.$this->session->get('lastname')  
        ));
    }

}
