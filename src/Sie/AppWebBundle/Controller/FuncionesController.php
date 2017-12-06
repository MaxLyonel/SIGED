<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\UsuarioRol;


class FuncionesController extends Controller {
    public function ObtenerUnidadEducativaAction($idUsuario,$gestion){
        $em = $GLOBALS['kernel']->getContainer()->get('doctrine')->getManager();
        $usuarioRol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneByUsuario($idUsuario);
        if(!$usuarioRol){
            return 0;
        }
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneById($idUsuario);
        /**$maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('persona'=>$usuario->getPersona(),'gestionTipo'=>$gestion));
        if(!$maestroInscripcion){
            return 0;
        }*/
        return $usuario->getUsername();
    }
}