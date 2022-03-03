<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteHistorialModificacion; 
use Sie\AppWebBundle\Entity\EstudianteInscripcion; 
use Sie\AppWebBundle\Entity\Estudiante; 
use Sie\AppWebBundle\Entity\EstudianteDocumento; 
use Symfony\Component\Validator\Constraints\DateTime;
use Sie\AppWebBundle\Entity\UnificacionRude;
use Sie\AppWebBundle\Entity\EstudianteBack;
use Sie\AppWebBundle\Entity\ApoderadoInscripcion;

 /**
     * Modulo para la Unificación de Homónimos
    */
class HomonimoController extends Controller{

 
    

}
