<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * EstudianteInformacion controller.
 *
 */
class EstudianteInformacionController extends Controller {

    private $session;

    /**
     * Inicializa Constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Muestra el formulario de búsqueda.
     */
    public function indexAction() {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieAppWebBundle:EstudianteInformacion:index.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
                    'form2' => $this->createSearch2Form()->createView(),
        ));
    }

    /**
     * Crea un formulario para buscar al estudiante por el código RUDE
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm() {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('estudianteinformacion_result'))
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'RUDE', 'required' => true, 'invalid_message' => 'Campo Obligatorio', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9a-zA-Z\sñÑ]{14,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestión', 'choices' => array('2023' => '2023','2022' => '2022','2021' => '2021','2020' => '2020','2019' => '2019','2018' => '2018', '2017' => '2017', '2016' => '2016', '2015' => '2015', '2014' => '2014', '2013' => '2013'), 'attr' => array('class' => 'form-control')))
                ->add('buscar', 'submit', array('label' => 'Buscar estudiante'))
                ->getForm();
        return $form;
    }

    /**
     * Crea un formulario para buscar al estudiante por el código RUDE
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearch2Form() {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('estudianteinformacion_result2'))
                ->add('carnet', 'text', array('mapped' => false, 'label' => 'Carnet', 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{1,15}', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('paterno', 'text', array('mapped' => false, 'label' => 'Apellido Paterno', 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{1,30}', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('materno', 'text', array('mapped' => false, 'label' => 'Apellido Materno', 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{1,30}', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('nombre', 'text', array('mapped' => false, 'label' => 'Nombre(s)', 'required' => true, 'invalid_message' => 'Campo Obligatorio', 'attr' => array('class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{1,30}', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('buscar2', 'submit', array('label' => 'Buscar coincidencias'))
                ->getForm();
        return $form;
    }

    /**
     * Obtiene los registros del Estudiante para visualiarlos
     * @param Request $request
     */
    public function resultAction(Request $request) {

        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $form = $request->get('form');
        $rude = $form['codigoRude'];
        $gestion = $form['gestion'];

        //Información de la/el estudiante
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));

        //Verifica si el estudiente existe
        if ($student) {

            //Información de inscripción de la/el estudiante
            $repository = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');

            $query = $repository->createQueryBuilder('i')
                    ->select('i.id insId, nt.nivel nivel, gt.grado grado, pt.paralelo paralelo, tt.turno turno, ie.id ieducativaId')
                    ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'i.estudiante = e.id')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'ic', 'WITH', 'i.institucioneducativaCurso = ic.id')
                    ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ic.institucioneducativa = ie.id')
                    ->innerJoin('SieAppWebBundle:NivelTipo', 'nt', 'WITH', 'ic.nivelTipo = nt.id')
                    ->innerJoin('SieAppWebBundle:GradoTipo', 'gt', 'WITH', 'ic.gradoTipo = gt.id')
                    ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'ic.paraleloTipo = pt.id')
                    ->innerJoin('SieAppWebBundle:TurnoTipo', 'tt', 'WITH', 'ic.turnoTipo = tt.id')
                    ->andWhere('i.estudiante = :estudiante')
                    ->andWhere('ic.gestionTipo = :gestion')
                    ->andWhere('i.estadomatriculaTipo IN (:estados)')
                    ->setParameter('estudiante', $student)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('estados', array(4,5,11,28,55))
                    ->orderBy('i.id')
                    ->setMaxResults(1)
                    ->getQuery();

            $inscription = $query->getOneOrNullResult();
            
            //Verifica si el estudiante cuenta con inscripción en la UE y n la gestión ingresada en el formulario de búsqueda
            if ($inscription) {
                $idEstudiante = $student->getId();

/*                $repository = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion');

                $query = $repository->createQueryBuilder('ai')
                        ->select('p.id perId, ai.id aiId, aid.id aidId, p.carnet, p.paterno, p.materno, p.nombre, at.apoderado apoTipo, aid.empleo')
                        ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'ai.persona = p.id')
                        ->leftJoin('SieAppWebBundle:ApoderadoInscripcionDatos', 'aid', 'WITH', 'aid.apoderadoInscripcion = ai.id')
                        ->innerJoin('SieAppWebBundle:ApoderadoTipo', 'at', 'WITH', 'ai.apoderadoTipo = at.id')
                        ->where('ai.estudianteInscripcion = :inscripcion')
                        ->setParameter('inscripcion', $inscription['insId'])
                        ->getQuery();

                $apoderados = $query->getResult();*/

                $repository = $em->getRepository('SieAppWebBundle:Estudiante');

                $query = $repository->createQueryBuilder('e')
                        ->select('p.id perId, ai.id aiId, aid.id aidId, p.carnet, p.paterno, p.materno, p.nombre, at.apoderado apoTipo, aid.empleo')
                        ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                        ->innerJoin('SieAppWebBundle:ApoderadoInscripcion', 'ai', 'WITH', 'ei.id = ai.estudianteInscripcion')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'ic', 'WITH', 'ei.institucioneducativaCurso = ic.id')
                        ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'ai.persona = p.id')
                        ->innerJoin('SieAppWebBundle:ApoderadoInscripcionDatos', 'aid', 'WITH', 'ai.id = aid.apoderadoInscripcion')
                        ->innerJoin('SieAppWebBundle:ApoderadoTipo', 'at', 'WITH', 'ai.apoderadoTipo = at.id')
                        ->where('e.codigoRude = :rude')
                        ->andwhere('ic.gestionTipo = :gestion')
                        ->setParameter('rude', $rude)
                        ->setParameter('gestion', $gestion)
                        ->getQuery();

                $apoderados = $query->getResult();                

                //Información de la institución educativa
                $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

                $query = $repository->createQueryBuilder('i')
                        ->select('i.id ieducativaId, i.institucioneducativa ieducativa, d.id distritoId, d.distrito distrito, dp.id departamentoId, dp.departamento departamento, de.dependencia dependencia, jg.cordx cordx, jg.cordy cordy')
                        ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'jg', 'WITH', 'i.leJuridicciongeografica = jg.id')
                        ->innerJoin('SieAppWebBundle:DistritoTipo', 'd', 'WITH', 'jg.distritoTipo = d.id')
                        ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dp', 'WITH', 'd.departamentoTipo = dp.id')
                        ->innerJoin('SieAppWebBundle:DependenciaTipo', 'de', 'WITH', 'i.dependenciaTipo = de.id')
                        ->where('i.id = :ieducativa')
                        ->setParameter('ieducativa', $inscription['ieducativaId'])
                        ->getQuery();

                $institucion = $query->getOneOrNullResult();

                $socioeconomico = $em->getRepository('SieAppWebBundle:Rude')->findOneBy(array('estudianteInscripcion' => $inscription['insId']));

                return $this->render('SieAppWebBundle:EstudianteInformacion:result.html.twig', array(
                            'student' => $student,
                            'inscription' => $inscription,
                            'institucion' => $institucion,
                            'rude' => $rude,
                            'sie' => $inscription['ieducativaId'],
                            'gestion' => $gestion,
                            'apoderados' => $apoderados,
                            'socioeconomico' => $socioeconomico
                ));
            } else {
                $message = "Estudiante con RUDE " . $rude . " no cuenta con inscripción en la gestión " . $gestion;
                $this->addFlash('notiext', $message);
                return $this->redirectToRoute('estudianteinformacion');
            }
        } else {
            $message = "Estudiante con RUDE " . $rude . " no se encuentra registrado";
            $this->addFlash('notiext', $message);
            return $this->redirectToRoute('estudianteinformacion');
        }
    }

    public function result2Action(Request $request) {

        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $request->get('form');

        $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Estudiante');

        $query = $repository->createQueryBuilder('e')
                ->where('e.carnetIdentidad like :carnet')
                ->andWhere('e.paterno like :paterno')
                ->andWhere('upper(e.materno) like :materno')
                ->andWhere('upper(e.nombre) like :nombre')
                ->setParameter('carnet', '%' . mb_strtoupper($form['carnet'], 'utf8') . '%')
                ->setParameter('paterno', '%' . mb_strtoupper($form['paterno'], 'utf8') . '%')
                ->setParameter('materno', '%' . mb_strtoupper($form['materno'], 'utf8') . '%')
                ->setParameter('nombre', '%' . mb_strtoupper($form['nombre'], 'utf8') . '%')
                ->orderBy('e.paterno, e.materno, e.nombre', 'ASC')
                ->getQuery();
        $entities = $query->getResult();

        if (!$entities) {
            $message = "Estudiante no registrado";
            $this->addFlash('notiext', $message);
            return $this->redirectToRoute('estudianteinformacion');
        }

        $message = 'Se ha encontrado coincidencias con los criterios de búsqueda.';
        $this->addFlash('successstudent', $message);
        return $this->render('SieAppWebBundle:EstudianteInformacion:result2.html.twig', array(
                    'entities' => $entities,
        ));
    }

    public function historyAction(Request $request, $idStudent) {
        $em = $this->getDoctrine()->getManager();
        $dataInscriptionR = array();
        $dataInscriptionA = array();
        $dataInscriptionE = array();
        $dataInscriptionP = array();

        $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($idStudent);
        
        $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $student->getCodigoRude() . "') order by gestion_tipo_id_raep desc, estudiante_inscripcion_id_raep desc;");
        $query->execute();
        $dataInscription = $query->fetchAll();

        foreach ($dataInscription as $key => $inscription) {
            switch ($inscription['institucioneducativa_tipo_id_raep']) {
              case '1':
                $dataInscriptionR[$key] = $inscription;
                break;
              case '2':
                $dataInscriptionA[$key] = $inscription;
                break;
              case '4':
                $dataInscriptionE[$key] = $inscription;
                break;
              case '5':
                $dataInscriptionP[$key] = $inscription;
                break;
            }
        }

        return $this->render('SieAppWebBundle:EstudianteInformacion:resultHistory.html.twig', array(
            'datastudent' => $student,
            'dataInscriptionR' => $dataInscriptionR,
            'dataInscriptionA' => $dataInscriptionA,
            'dataInscriptionE' => $dataInscriptionE,
            'dataInscriptionP' => $dataInscriptionP,
            'visible' => false
        ));
    }
}
