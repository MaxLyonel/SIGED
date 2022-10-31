<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EstudentFind
 *
 * @author krlos
 */

namespace Sie\AppWebBundle\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Form\PersonaType;
use Sie\AppWebBundle\Entity\Estudiante;
use Symfony\Component\HttpFoundation\Session\Session;

class findStudentController extends Controller {


    //put your code here
    public function buildStudentForm() {
        return 'krlos';
        /* $estudiante = new Estudiante();
          $agestion = array('2015' => '2015', '2016' => '2016', '2017' => '2017');
          $form = $this->createFormBuilder($estudiante)
          ->setAction($this->generateUrl('usuario_main_find'))
          ->add('codigoRude', 'text', array('required' => true, 'invalid_message' => 'Campo 1 obligatorio'))
          ->add('gestion', 'choice', array("mapped" => false, 'choices' => $agestion, 'required' => true))
          ->add('buscar', 'submit', array('label' => 'Buscar'))
          ->getForm();
          return $form; */
    }

    /**
     * get the data of student- obtenemos datos personales del estudiante
     * parameters: codigo rude
     * @author krlos
     */
    public function getDataStudent($rude) {
        //die($rude);
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT get_estudiante_datos (:rude::VARCHAR)');
        $query->bindValue(':rude', $rude);
        $query->execute();
        return $query->fetchAll();
    }

}
