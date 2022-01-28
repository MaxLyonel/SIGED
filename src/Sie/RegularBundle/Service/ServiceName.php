<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ServiceName
 *
 * @author krlos
 */

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\PaisTipo;
use Sie\AppWebBundle\Form\EstudianteType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Symfony\Component\HttpFoundation\JsonResponse;

class ServiceName extends Controller {

    //put your code here
    protected $entityManager;

    //construct method
    public function __construct($entityManager) {
        $this->entityManager = $entityManager;
    }

    //get the student inscription
    public function findInscription($id) {
        $em = $this->getDoctrine()->getManager();

        $objStudent = $em->getDoctrine('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $id));
        return $objStudent->getPaterno();
    }

}
