<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\JdpEstudianteDatopersonal;
use Sie\AppWebBundle\Form\EstudianteType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Form\EstudianteJuegosDatosClasificacionType;

/**
 * Estudiante controller.
 *
 */
class EstudianteDatopersonalController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Lists all Estudiante entities.
     *
     */
    public function indexAction(Request $request) {
        // data es un array con claves 'name', 'email', y 'message'
        return $this->render('SieAppWebBundle:Estudiante:searchstudent.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
        ));
    }

    /**
     * Lists all Estudiante Juegos Fase 3 entities.
     *
     */
    public function juegosDeportistasFase3Action(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                select lt.codigo as id, lt.lugar as nombre from usuario as u
                inner join usuario_rol as ur on ur.usuario_id = u.id
                inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                where u.id = ".$id_usuario." and (rol_tipo_id = 7 or rol_tipo_id = 28)
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();

        if (count($objEntidad)<1){
            $msg = "Su usuario no tienen asignado un departamento";
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));
            return $this->redirectToRoute('sie_juegos_inscripcion_index');
        }

        $codigoEntidad = $objEntidad[0]['id'];   

        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $queryEntidadDeportistas = $em->getConnection()->prepare("
                select e.id as estudiante_id, lt5.lugar as departamento, cpt.id as circunscripcion, lt.lugar as distrito, cast(ie.id as varchar) || ' - ' || ie.institucioneducativa as institucioneducativa
                , dt.disciplina, pt.prueba, gt.genero, e.codigo_rude, e.carnet_identidad, e.complemento, e.paterno, e.materno, e.nombre, edp.estatura,  edp.peso,  edp.talla,  edp.foto
                from estudiante_inscripcion_juegos as eij
                inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                inner join estudiante as e on e.id = ei.estudiante_id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join lugar_tipo as lt1 on lt1.id = jg.lugar_tipo_id_localidad
                inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                inner join lugar_tipo as lt5 on lt5.id = lt4.lugar_tipo_id
                inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
                inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id 
                inner join genero_tipo as gt on gt.id = e.genero_tipo_id
                inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                inner join circunscripcion_tipo as cpt on cpt.id = jg.circunscripcion_tipo_id
                left join (select * from jdp_estudiante_datopersonal where id in (select max(id) from jdp_estudiante_datopersonal where gestion_tipo_id = ".$gestionActual." group by estudiante_id)) as edp on edp.estudiante_id = e.id and edp.gestion_tipo_id = ".$gestionActual."
                where eij.gestion_tipo_id = ".$gestionActual." and eij.fase_tipo_id = 4 and lt5.codigo = '".$codigoEntidad."' --and dt.nivel_tipo_id = 13
                order by lt5.lugar, cpt.id, lt.lugar, ie.id, dt.disciplina, pt.prueba, gt.genero, e.nombre, e.paterno, e.materno
            ");
        $queryEntidadDeportistas->execute();
        $objEntidadDeportistas = $queryEntidadDeportistas->fetchAll();

        return $this->render('SieAppWebBundle:EstudianteDatopersonal:index.html.twig', array(
                    'objEntidadDeportistas' => $objEntidadDeportistas,
                    'infoEntidad' => $objEntidad, 
                    'fase' => 3, 
        ));
    }

    /**
     * Lists all Estudiante Juegos Fase 2 entities.
     *
     */
    public function juegosDeportistasFase2Action(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                    select ct.id, ct.circunscripcion as nombre from usuario as u
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join circunscripcion_tipo as ct on ct.id = ur.circunscripcion_tipo_id
                    where u.id = ".$id_usuario." and rol_tipo_id = 6
                ");

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();

        if (count($objEntidad)<1){
            $msg = "Su usuario no tienen asignado un departamento";
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));
            return $this->redirectToRoute('sie_juegos_inscripcion_index');
        }

        $codigoEntidad = $objEntidad[0]['id'];   

        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $queryEntidadDeportistas = $em->getConnection()->prepare("
                select e.id as estudiante_id, lt5.lugar as departamento, cpt.id as circunscripcion, lt.lugar as distrito, cast(ie.id as varchar) || ' - ' || ie.institucioneducativa as institucioneducativa
                , dt.disciplina, pt.prueba, gt.genero, e.codigo_rude, e.carnet_identidad, e.complemento, e.paterno, e.materno, e.nombre, edp.estatura,  edp.peso,  edp.talla,  edp.foto
                from jdp_estudiante_inscripcion_juegos as eij
                inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                inner join estudiante as e on e.id = ei.estudiante_id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join lugar_tipo as lt1 on lt1.id = jg.lugar_tipo_id_localidad
                inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                inner join lugar_tipo as lt5 on lt5.id = lt4.lugar_tipo_id
                inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
                inner join jdp_prueba_tipo as pt on pt.id = eij.prueba_tipo_id 
                inner join genero_tipo as gt on gt.id = e.genero_tipo_id
                inner join jdp_disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                inner join jdp_circunscripcion_tipo as cpt on cpt.id = jg.circunscripcion_tipo_id
                left join (select * from jdp_estudiante_datopersonal where id in (select max(id) from jdp_estudiante_datopersonal where gestion_tipo_id = ".$gestionActual." group by estudiante_id)) as edp on edp.estudiante_id = e.id and edp.gestion_tipo_id = ".$gestionActual."
                where eij.gestion_tipo_id = ".$gestionActual." and eij.fase_tipo_id = 3 and jg.circunscripcion_tipo_id = ".$codigoEntidad." --and dt.nivel_tipo_id = 13
                order by lt5.lugar, cpt.id, lt.lugar, ie.id, dt.disciplina, pt.prueba, gt.genero, e.nombre, e.paterno, e.materno
            ");
        $queryEntidadDeportistas->execute();
        $objEntidadDeportistas = $queryEntidadDeportistas->fetchAll();

        return $this->render('SieAppWebBundle:EstudianteDatopersonal:index.html.twig', array(
                    'objEntidadDeportistas' => $objEntidadDeportistas,
                    'infoEntidad' => $objEntidad, 
                    'fase' => 2, 
        ));
    }


    /**
     * Lists all Estudiante entities.
     *
     */
    public function juegosDeportistasFase3FormAction(Request $request) {        
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 
        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();

            /*
             * Recupera datos del formulario
             */
            $estudianteId = $request->get('id');
            $fase = $request->get('fase');
            //get db connexion
            $em = $this->getDoctrine()->getManager();
            $queryEntidad = $em->getConnection()->prepare("
                    select e.id as estudiante_id, gt.genero, e.codigo_rude, e.carnet_identidad, e.complemento, e.paterno, e.materno, e.nombre, e.nombre, e.nombre || ' ' || e.materno || ' ' || e.paterno as estudiante, edp.estatura,  edp.peso,  edp.talla,  edp.foto
                    from estudiante as e
                    inner join genero_tipo as gt on gt.id = e.genero_tipo_id
                    left join jdp_estudiante_datopersonal as edp on edp.estudiante_id = e.id and edp.gestion_tipo_id = ".$gestionActual."
                    where e.id = ".$estudianteId."
                ");
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll();

            if (count($objEntidad)<1){
                $msg = "Es estudiante no cuenta con un registro";
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));
                return $this->redirectToRoute('sie_juegos_inscripcion_index');
            }

            $entityDatos = new JdpEstudianteDatopersonal();
            $form = $this->createCreateDatosForm($entityDatos);

            // data es un array con claves 'name', 'email', y 'message'
            return $this->render('SieAppWebBundle:EstudianteDatopersonal:edit.html.twig', array(
                    'form' => $form->createView(),
                    'estudiante' => $objEntidad[0],
                    'fase' => $fase,
            ));            
        } else {            
            $msg = "Envío de datos de forma incorrecta, intente nuevamente";
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));
            return $this->redirectToRoute('sie_juegos_inscripcion_index');
        }
    }

    /**
     * Lists all Estudiante entities.
     *
     */
    public function juegosDeportistasFase3FormSaveAction(Request $request) {        
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y'); 
        //if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            /*
             * Recupera datos del formulario
             */
            $estudianteId = $request->get('id');
            $fase = $request->get('fase');

            

            try {
                $entityEstudianteDatoPersonal = $em->getRepository('SieAppWebBundle:JdpEstudianteDatopersonal')->findOneBy(array('estudiante' => $estudianteId));
                if(count($entityEstudianteDatoPersonal)<=0){
                   $entityEstudianteDatopersonal = new JdpEstudianteDatopersonal();
                }                      
                $form = $this->createCreateDatosForm($entityEstudianteDatopersonal);
                $form->handleRequest($request);

                if ($form->isValid()) {
                    if (null != $form->get('foto')->getData()) {
                        $file = $entityEstudianteDatopersonal->getFoto();

                        $filename = md5(uniqid()) . '.' . $file->guessExtension();
                        
                        $filesize = $file->getClientSize();
                        if ($filesize/1024 < 501) {
                            $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/fotos_juegos';
                            $file->move($adjuntoDir, $filename);

                            $entityEstudianteDatopersonal->setFoto($filename);
                            
                        } else {
                            $this->get('session')->getFlashBag()->set(
                                'danger',
                                array(
                                    'title' => 'Alerta!',
                                    'message' => 'Fotografia muy grande, favor ingresar una fotografía que no exceda los 500KiB.'
                                )
                            );
                            return $this->redirect($this->generateUrl('estudiantedatopersonal_juegos_deportistas_fase'.$fase));  
                        } 

                        
                    }
                }            
                
                $em = $this->getDoctrine()->getManager();
                $entityEstudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' => $estudianteId));


                $entityEstudianteDatopersonal->setEstudiante($entityEstudiante);
                //$entityEstudianteDatopersonal->setEstatura($estatura);
                //$entityEstudianteDatopersonal->setTalla($talla);
                //$entityEstudianteDatopersonal->setPeso($peso);                                                                
                $em = $this->getDoctrine()->getManager(); 
                $em->persist($entityEstudianteDatopersonal);
                $em->flush(); 
                $em->getConnection()->commit();
                $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Registro guardado de forma correcta'));  
                return $this->redirectToRoute('estudiantedatopersonal_juegos_deportistas_fase'.$fase);
            } catch (Exception $e) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Problemas al guardar el registro, intente nuevamente'));  
                return $this->redirectToRoute('estudiantedatopersonal_juegos_deportistas_fase'.$fase);
            } 

            //get db connexion
            $em = $this->getDoctrine()->getManager();
            $queryEntidad = $em->getConnection()->prepare("
                    select e.id as estudiante_id, gt.genero, e.codigo_rude, e.carnet_identidad, e.complemento, e.paterno, e.materno, e.nombre, e.nombre, e.nombre || ' ' || e.materno || ' ' || e.paterno as estudiante, edp.estatura,  edp.peso,  edp.talla,  edp.foto
                    from estudiante as e
                    inner join genero_tipo as gt on gt.id = e.genero_tipo_id
                    left join jdp_estudiante_datopersonal as edp on edp.estudiante_id = e.id and edp.gestion_tipo_id = ".$gestionActual."
                    where e.id = ".$estudianteId."
                ");
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll();

            if (count($objEntidad)<1){
                $msg = "Es estudiante no cuenta con un registro";
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));
                return $this->redirectToRoute('sie_juegos_inscripcion_index');
            }
            // data es un array con claves 'name', 'email', y 'message'
            return $this->render('SieAppWebBundle:EstudianteDatopersonal:edit.html.twig', array(
                    'form' => $this->createDatosPersonalesForm($objEntidad[0])->createView(),
                    'estudiante' => $objEntidad[0],
            ));            
        //} else {            
        //   $msg = "Envío de datos de forma incorrecta, intente nuevamente";
        //    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));
        //    return $this->redirectToRoute('sie_juegos_inscripcion_index');
        //}
    }

    private function createCreateDatosForm(JdpEstudianteDatopersonal $entity)
    { 
        //$entity->setPruebaTipo();
        $form = $this->createForm(new EstudianteJuegosDatosClasificacionType(), $entity, array(
            'action' => $this->generateUrl('estudiantedatopersonal_juegos_deportistas_fase3_form_save'),
            'method' => 'POST', 
        ));
        //->add('submit', 'submit',array('label' => 'Guardar'));    
        return $form;
        
    }

    /**
     * Creates a form to search the users of student selected
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDatosPersonalesForm($entityEstudiante) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('estudiantedatopersonal_juegos_deportistas_fase3_form_save'))
                ->add('id', 'hidden', array('label' => 'Peso (Ej.: 50,50)','required' => true, 'attr' => array('value' => $entityEstudiante['estudiante_id'])))
                ->add('estatura','number', array('label' => 'Estatura (Ej.: 1,55)','required' => true, 'attr' => array('value' => $entityEstudiante['estatura'])))
                ->add('peso','number', array('label' => 'Peso (60,10)','required' => true, 'attr' => array('value' => $entityEstudiante['peso'])))
                ->add('talla',
                          'choice',  
                          array('label' => 'Talla (Selecciona)',
                                'choices' => array( '8' => 'XXXS - 8'
                                                    ,'10' => 'XXS - 10'
                                                    ,'12' => 'XS - 12'
                                                    ,'14' => 'S - 14'
                                                    ,'16' => 'M - 16'
                                                    ,'18' => 'L - 18'
                                                    ,'20' => 'XL - 20'
                                                    ,'22' => 'XXL - 22'
                                                    ,'24' => 'XXXL - 24'
                                                    ),
                                'data' => $entityEstudiante['talla']))
                ->add('foto', 'file', array('label' => 'Fotografía (.bmp)', 'required' => true))
                ->add('submit', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-default')))
                ->getForm();
        return $form;
    }

    

    private function createNewForm() {
        $estudiante = new Estudiante();
        $em = $this->getDoctrine()->getManager();
        $lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneByLugarNivel(1);
        $form = $this->createFormBuilder($estudiante)
                ->setAction($this->generateUrl('estudiante_main_create'))
                ->add('carnetIdentidad', 'text', array('required' => true, 'pattern' => '[0-9]{4,10}'))
                ->add('paterno', 'text', array('required' => true, 'pattern' => '[a-zA-Z\s]{2,20}'))
                ->add('materno', 'text', array('required' => true, 'pattern' => '[a-zA-Z\s]{2,20}'))
                ->add('nombre', 'text', array('required' => true, 'pattern' => '[a-zA-Z\s]{2,50}'))
                ->add('oficialia', 'text', array('required' => false))
                ->add('libro', 'text', array('required' => false))
                ->add('partida', 'text', array('required' => false))
                ->add('folio', 'text', array('required' => false))
                ->add('sangreTipoId', 'entity', array('class' => 'SieAppWebBundle:SangreTipo', 'property' => 'grupoSanguineo'))
                ->add('idiomaMaternoId', 'entity', array('class' => 'SieAppWebBundle:IdiomaMaterno', 'property' => 'idiomaMaterno'))
                ->add('complemento', 'text', array('required' => false, 'pattern' => '[0-9]{1}[A-Z]{1}', 'max_length' => '2'))
                ->add('fechaNacimiento', 'text', array('required' => true, 'attr' => array('placeholder' => 'dd-mm-YYYY')))
                ->add('correo', 'text', array('required' => false))
                ->add('localidadNac', 'text', array('required' => false))
                ->add('celular', 'text', array('required' => false, 'pattern' => '[0-9]{6,10}'))
                ->add('carnetCodepedis', 'text', array('required' => false))
                ->add('carnetIbc', 'text', array('required' => false))
                ->add('generoTipo', 'entity', array('class' => 'SieAppWebBundle:GeneroTipo', 'property' => 'genero'))
                ->add('lugarNacTipo', 'entity', array('class' => 'SieAppWebBundle:LugarTipo',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('lt')
                                ->where('lt.lugarNivel = :id')
                                ->setParameter('id', '1')
                                ->orderBy('lt.id', 'ASC')
                        ;
                    }, 'property' => 'lugar'))
                ->add('estadoCivil', 'entity', array('class' => 'SieAppWebBundle:EstadoCivilTipo', 'property' => 'estadoCivil'))
                ->add('guardar', 'submit', array('label' => 'Guardar y continuar'))
                ->getForm();
        return $form;
    }
}
