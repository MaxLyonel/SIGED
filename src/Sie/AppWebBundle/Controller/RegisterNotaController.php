<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RegisterNotaController extends Controller {
    /*     * 
     *
     * function to serach the info about the notes 
     * 
     * */

    public function searchmaterianotaAction(Request $request) {

        return $this->render('SieAppWebBundle:Rude:lookforrude.html.twig', array('action' => 'find_nota_student', 'titleform' => 'Insertar Nota'));
    }

    public function findmaterianotaAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        $dependenciaTipos = $em->getRePository('SieAppWebBundle:DependenciaTipo')->findAll();
        //$aContent = array('columnA' => array(0 => 'ca1', 1 => 'ca2', 2 => 'ca3'), 'columnB' => array(0 => 'cb1', 1 => 'cb2', 2 => 'cb3'));
        return $this->render('SieAppWebBundle:Materia:materianota.html.twig', array('dependenciatipos' => $dependenciaTipos));
    }

}
