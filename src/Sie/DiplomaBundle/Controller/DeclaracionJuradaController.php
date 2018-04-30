<?php

namespace Sie\DiplomaBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;

class DeclaracionJuradaController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * declaracion jurada Index
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':DeclaracionJurada:find.html.twig', array(
                    'form' => $this->craeteformsearchsie()->createView()
        ));
    }

    private function craeteformsearchsie() {
        //set new gestion to the select year
        $aGestion = array();
        $currentYear = date('Y');
        for ($i = 1; $i <= 8; $i++) {
            $aGestion[$currentYear] = $currentYear;
            $currentYear--;
        }
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('diploma_declaracion_jurada_find'))
                        ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('gestion', 'choice', array('label' => 'Gestión', 'choices' => $aGestion, 'attr' => array('class' => 'form-control')))
                        ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                        ->getForm();
    }

    /**
     * find the bachillers per sie
     * @param Request $request
     * @return type the list of bachilleres
     */
    public function findAction(Request $request) {

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');

            $institucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);
            if (!$institucionEducativa) {
                $this->session->getFlashBag()->add('noticeddjj', 'No existe Unidad Educativa');
                return $this->redirectToRoute('diploma_declaracion_jurada_index');
            }
//            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
//            $query->bindValue(':user_id', $this->session->get('userId'));
//            $query->bindValue(':sie', $institucionEducativa->getId());
//            $query->bindValue(':roluser', $this->session->get('roluser'));
//            $query->execute();
//            $aTuicion = $query->fetchAll();


//            if (!$aTuicion[0]['get_ue_tuicion']) {
//                $this->session->getFlashBag()->add('noticeddjj', 'No tiene tuición sobre la Unidad Educativa');
//                return $this->redirectToRoute('diploma_declaracion_jurada_index');
//            }

            $bachilleres = $this->getBachilleresPerSie($form['sie'], $form['gestion']);

            return $this->render($this->session->get('pathSystem') . ':DeclaracionJurada:bachilleres.html.twig', array(
                        'bachilleres' => $bachilleres,
                        'unidadEducativa' => $institucionEducativa,
                        'gestionSelected' => $form['gestion']
            ));
        }
    }

    /*
     * select *
      from institucioneducativa i
      left join estudiante_inscripcion ei on (i.id = ei.institucioneducativa_id)
      left join estudiante e on (ei.estudiante_id=e.id)
      where i.id = 80730200 and ei.nivel_tipo_id=13 and ei.grado_tipo_id=6 and ei.gestion_tipo_id=2015 */

    private function getBachilleresPerSie($sie, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        if ($gestion >= 2011){
            $query = $entity->createQueryBuilder('ie')
                    ->select('e.paterno', ' e.materno', 'e.nombre', 'e.codigoRude', 'e.fechaNacimiento', 'ie.id as insteduId', 'e.id as studentId, IDENTITY(iec.gestionTipo) as gestionTipo')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ie.id = iec.institucioneducativa')
                    ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                    ->leftjoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ei.estudiante=e.id')
                    ->where('ie.id = :sie')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('iec.nivelTipo = :nivel')
                    ->andwhere('iec.gradoTipo = :grado')
                    ->setParameter('sie', $sie)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('nivel', '13')
                    ->setParameter('grado', '6')
                    ->orderBy('e.paterno', 'ASC')
                    ->getQuery();
        } else {
            $query = $entity->createQueryBuilder('ie')
                    ->select('e.paterno', ' e.materno', 'e.nombre', 'e.codigoRude', 'e.fechaNacimiento', 'ie.id as insteduId', 'e.id as studentId, IDENTITY(iec.gestionTipo) as gestionTipo')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ie.id = iec.institucioneducativa')
                    ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                    ->leftjoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ei.estudiante=e.id')
                    ->where('ie.id = :sie')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('iec.nivelTipo = :nivel')
                    ->andwhere('iec.gradoTipo = :grado')
                    ->setParameter('sie', $sie)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('nivel', '3')
                    ->setParameter('grado', '4')
                    ->orderBy('e.paterno', 'ASC')
                    ->getQuery();
        }
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

}
