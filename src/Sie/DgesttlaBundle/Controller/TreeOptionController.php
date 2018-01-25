<?php

namespace Sie\DgesttlaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;


class TreeOptionController extends Controller{

    public $session;
    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();        
    }

    public function indexAction(){

        $form = array('sie'=>80730830, 'gestion'=>2017, 'tipoCarrera'=>10);

        $em = $this->getDoctrine()->getManager();
        //find the levels from UE
        //levels gestion -1
        //$objLevelsOld = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getNivelBySieAndGestion($form['sie'], $form['gestion']);
        $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(
            array('id' => $form['sie'],
        ));
        $exist = true;
       //check if the sie exists
        if ($objUeducativa) {

            //look for the carrera and materias
            $objAsignaturas = $this->get('dgfunctions')->getTreeAsignaturas($form);

// dump($objAsignaturas);die;
            foreach ($objAsignaturas as $uEducativa) {

                //get the literal data of unidad educativa
                // $sinfoUeducativa = serialize(array(
                //     'ueducativaInfo' => array('nivel' => $uEducativa['nivel'], 'grado' => $uEducativa['grado'], 'paralelo' => $uEducativa['paralelo'], 'turno' => $uEducativa['turno']),
                //     'ueducativaInfoId' => array('paraleloId' => $uEducativa['paraleloId'], 'turnoId' => $uEducativa['turnoId'], 'nivelId' => $uEducativa['nivelId'], 'gradoId' => $uEducativa['gradoId'], 'cicloId' => $uEducativa['cicloTipoId'], 'iecId' => $uEducativa['iecId']),
                //     'requestUser' => array('sie' => $form['sie'], 'gestion' => $form['gestion'])
                // ));

                //send the values to the next steps
                $aInfoUnidadEductiva[$uEducativa['periodo']][$uEducativa['asignatura']][$uEducativa['turno']][$uEducativa['paralelo']] = array('infoUe' => $uEducativa);
            }

        } else {
            $message = 'No existe información de la Unidad Educativa para la gestión seleccionada ó Código SIE no existe ';
            $this->addFlash('warninresult', $message);
            $exist = false;
        }

        // dump($aInfoUnidadEductiva);die;

        // check if the UE close the rude task
        // $objinstitucioneducativaOperativoLogExist = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findOneBy(array(
        //   'institucioneducativa' => $form['sie'],
        //   'gestionTipoId'  => $form['gestion'],
        //   'institucioneducativaOperativoLogTipo' => 4
        // ));
        // // create the var to show the message about close opertive
        // $this->session->set('closeRude',(!$objinstitucioneducativaOperativoLogExist)?false:true);

        $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);
        //$objInfoAutorizadaUe = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->getInfoAutorizadaUe($form['sie'], $form['gestion']);die('krlossdfdfdfs');
        $odataUedu = $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);

        return $this->render($this->session->get('pathSystem') . ':TreeOption:index.html.twig', array(
                    'aInfoUnidadEductiva' => $aInfoUnidadEductiva,
                    'sie' => $form['sie'],
                    'gestion' => $form['gestion'],
                    'objUe' => $objUeducativa,
                    'exist' => $exist,
                    'odataUedu' => $odataUedu
        ));

        // return $this->render('SieDgesttlaBundle:TreeOption:index.html.twig', array(
        //         // ...
        //     ));    
    
    }

    public function seeStudentsAction(){
        return $this->render('SieDgesttlaBundle:TreeOption:seeStudents.html.twig', array(
                // ...
            ));    
    }

}
