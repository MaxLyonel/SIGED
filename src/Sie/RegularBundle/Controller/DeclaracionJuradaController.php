<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;

use Sie\TramitesBundle\Controller\DefaultController as defaultTramiteController;

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
        for ($i = 1; $i <= 9; $i++) {
            $aGestion[$currentYear] = $currentYear;
            $currentYear--;
        }
        $em = $this->getDoctrine()->getManager();
        $gestionEntity = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $currentYear));
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('declaracion_jurada_find'))
                        ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        //->add('gestion', 'choice', array('label' => 'Gestión', 'choices' => $aGestion, 'attr' => array('class' => 'form-control')))
                        ->add('gestion', 'entity', array('data' => $gestionEntity, 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('gt')
                                        ->where('gt.id > 2008')
                                        ->orderBy('gt.id', 'DESC');
                            },
                        ))
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
                return $this->redirectToRoute('declaracion_jurada_index');
            }
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $institucionEducativa->getId());
            if($this->session->get('sysname') == 'DIPLOMAS' or $this->session->get('sysname') == 'TRAMITES'){
                $defaultTramiteController = new defaultTramiteController();
                $defaultTramiteController->setContainer($this->container);
                $rolOpcionTramite = 0;
                $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($this->session->get('userId'),16);
                if($esValidoUsuarioRol and $rolOpcionTramite == 0){
                    $rolOpcionTramite = 16;
                }
                $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($this->session->get('userId'),15);
                if($esValidoUsuarioRol and $rolOpcionTramite == 0){
                    $rolOpcionTramite = 15;
                }
                $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($this->session->get('userId'),14);
                if($esValidoUsuarioRol and $rolOpcionTramite == 0){
                    $rolOpcionTramite = 14;
                }
                $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($this->session->get('userId'),13);
                if($esValidoUsuarioRol and $rolOpcionTramite == 0){
                    $rolOpcionTramite = 13;
                }
                $query->bindValue(':roluser', $rolOpcionTramite);
            } else {                
                $query->bindValue(':roluser', $this->session->get('roluser'));
            }
            $query->execute();
            $aTuicion = $query->fetchAll();


            if (!$aTuicion[0]['get_ue_tuicion']) {
                if($this->session->get('sysname') == 'DIPLOMAS' or $this->session->get('sysname') == 'TRAMITES'){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No tiene tuición sobre la Unidad Educativa'));
                } else {                
                    $this->session->getFlashBag()->add('noticeddjj', 'No tiene tuición sobre la Unidad Educativa');
                }
                return $this->redirectToRoute('declaracion_jurada_index');
            }

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
        /*$query = $entity->createQueryBuilder('ie')
                ->select('e.paterno', ' e.materno', 'e.nombre', 'e.codigoRude', 'e.fechaNacimiento', 'ie.id as insteduId', 'e.id as studentId, IDENTITY(iec.gestionTipo) as gestionTipo')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ie.id = iec.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->leftjoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ei.estudiante=e.id')
                ->where('ie.id = :sie')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.nivelTipo IN (:nivel)')
                ->andwhere('iec.gradoTipo IN (:grado)')
                ->setParameter('sie', $sie)
                ->setParameter('gestion', $gestion)
                ->setParameter('nivel', array(13, 3, 15))
                ->setParameter('grado', array(6, 4,3))
                ->Distinct()
                ->orderBy('e.paterno', 'ASC')
                ->getQuery();*/

                $query  = $em->getConnection()->prepare("
                        select e.codigo_rude, e.carnet_identidad,e.complemento, e.pasaporte, e.paterno, e.materno, e.nombre, e.fecha_nacimiento,
                        e.genero_tipo_id, ptp.pais,ltd.lugar as departamento,ltp.lugar as provincia, e.localidad_nac , g.genero, ei.estadomatricula_tipo_id,
                        i.institucioneducativa, i.id as insteduid,iec.gestion_tipo_id, iec.nivel_tipo_id as nivel_id, iec.grado_tipo_id, sat.id as acre, iec.gestion_tipo_id
                        from estudiante e
                        left join pais_tipo ptp on (e.pais_tipo_id = ptp.id)
                        left join lugar_tipo ltpa on (e.lugar_nac_tipo_id= ltpa.id)
                        left join lugar_tipo ltp on (e.lugar_prov_nac_tipo_id = ltp.id)
                        left join lugar_tipo ltd on (ltd.id = ltp.lugar_tipo_id)
                        left join estudiante_inscripcion ei on (e.id = ei.estudiante_id)
                        left join genero_tipo g on (e.genero_tipo_id = g.id)
                        left join institucioneducativa_curso iec on (ei.institucioneducativa_curso_id = iec.id)
                        left join superior_institucioneducativa_periodo as siep on siep.id = iec.superior_institucioneducativa_periodo_id
                        left join superior_institucioneducativa_acreditacion as siea on siea.id = siep.superior_institucioneducativa_acreditacion_id
                        left join superior_acreditacion_especialidad as sae on sae.id = siea.acreditacion_especialidad_id
                        left join superior_acreditacion_tipo as sat on sat.id = sae.superior_acreditacion_tipo_id
                        left join institucioneducativa i on (iec.institucioneducativa_id = i.id)
                        where iec.gestion_tipo_id = ".$gestion." and i.id = ".$sie."
                        and 
                        case when iec.gestion_tipo_id >=2011 then (iec.nivel_tipo_id=13 and iec.grado_tipo_id=6) or (iec.nivel_tipo_id=15 and sat.codigo = 3 /*sat.id in (6,52)*/)
                        when iec.gestion_tipo_id <= 2010 then (iec.nivel_tipo_id=3 and iec.grado_tipo_id=4) or (iec.nivel_tipo_id=15 and sat.codigo = 3 /*iec.grado_tipo_id=2*/) 
                        else ((iec.nivel_tipo_id=13 and iec.grado_tipo_id=6) or (iec.nivel_tipo_id=15 and sat.codigo = 3)) end
                        order by  e.paterno, e.materno
                      ");

                      $query->execute();
                      $objEntidad = $query->fetchAll();


        try {
          //dump($query->getSQL());die;
            return $objEntidad;//$query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

}
