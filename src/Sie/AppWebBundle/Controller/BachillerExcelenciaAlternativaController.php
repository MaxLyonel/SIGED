<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Form\SelectIeType;
use Sie\AppWebBundle\Form\EstudianteDestacadoAlternativaType;
use Sie\AppWebBundle\Form\MaestroCuentabancariaType;
use Sie\AppWebBundle\Entity\EstudianteDestacado;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\MaestroCuentabancaria;
use Sie\AppWebBundle\Entity\EntidadfinancieraTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;
use Sie\AppWebBundle\Entity\NotaTipo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;

/**
 * Bachiller de Excelencia Controller.
 *
 */
class BachillerExcelenciaAlternativaController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
        $this->fechaActual = new \DateTime('now');
        $this->fechaCorte = new \DateTime('2024-11-30');
        $this->gestionOperativo = $this->session->get('currentyear');
    }

    /*
     * Muestra el formulario de búsqueda de Institución Educativa
     */

    public function indexAction() {
        //return $this->redirect($this->generateUrl('login'));
       
        $id_usuario = $this->session->get('userId');
        $ie_id = $this->session->get('ie_id');
        $em = $this->getDoctrine()->getManager();

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        /*if($this->fechaActual > $this->fechaCorte) {
            return $this->redirect($this->generateUrl('principal_web'));
        }*/

        $form = $this->createSearchIeForm();

        return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:index.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /*
     * Formulario de búsqueda de Institucion Educativa
     */

    private function createSearchIeForm() {
        $form = $this->createForm(new SelectIeType(), null, array(
            'action' => $this->generateUrl('bach_exc_alt_ie_search'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Consolidar IBD'));

        return $form;
    }


    /*
     * Muestra el formulario de búsqueda de Institución Educativa
     */

    public function indexDirAction() {
        //return $this->redirect($this->generateUrl('login'));
        
        $id_usuario = $this->session->get('userId');
        $ie_id = $this->session->get('ie_id');
        $em = $this->getDoctrine()->getManager();

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if($this->fechaActual > $this->fechaCorte) {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $form = $this->createSearchIeDirForm();

        return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:index_dir.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /*
     * Formulario de búsqueda de Institucion Educativa
     */

    private function createSearchIeDirForm() {
        $form = $this->createForm(new SelectIeType(), null, array(
            'action' => $this->generateUrl('bach_exc_alt_ie_dir_search'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Buscar'));

        return $form;
    }

    public function resultSearchIeDirAction(Request $request) {
        //verificacion registro alternativa
        
        $em = $this->getDoctrine()->getManager();
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if($this->fechaActual > $this->fechaCorte) {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $form = $this->createSearchIeDirForm();
        $form->handleRequest($request);

        if ($form->isValid()) {

            $formulario = $form->getData();

            /*
            * verificamos si tiene tuicion
            */
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $formulario['institucioneducativa']);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            if ($aTuicion[0]['get_ue_tuicion']) {
                $institucion = $formulario['institucioneducativa'];
                $gestion = $formulario['gestion'];
            } else {
                $this->get('session')->getFlashBag()->add('searchIe', 'No tiene tuición sobre la Institución Educativa');
            return $this->redirect($this->generateUrl('bach_exc_alt_dir'));
            }
            /* Verificar si la UE ya ha registrado al director */
            $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');

            $query = $repository->createQueryBuilder('m')
                    ->where('m.institucioneducativa = :institucion')
                    ->andWhere('m.gestionTipo = :gestion')
                    ->setParameter('institucion', $formulario['institucioneducativa'])
                    ->setParameter('gestion', $formulario['gestion'])
                    ->getQuery();

            $registrado = $query->getResult();

            /*if(count($registrado) > 1) {
                return $this->redirect($this->generateUrl('principal_web'));
            }*/

            /*
            * Verificar si la UE cuenta con aprendizajes especializados
            */

            $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

            $query = $repository->createQueryBuilder('i')
                    ->select('count(i.id)')
                    ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion', 'sia', 'WITH', 'sia.institucioneducativa = i.id')
                    ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad', 'sae', 'WITH', 'sia.acreditacionEspecialidad = sae.id')
                    ->innerJoin('SieAppWebBundle:SuperiorAcreditacionTipo', 'sat', 'WITH', 'sae.superiorAcreditacionTipo = sat.id')
                    ->where('i.id = :institucion')
                    ->andWhere('sat.id = :codigo')
                    ->andWhere('sia.gestionTipo = :gestion')
                    ->setParameter('institucion', $formulario['institucioneducativa'])
                    ->setParameter('codigo', 52)
                    ->setParameter('gestion', $formulario['gestion'])
                    ->getQuery();

            $quinto = $query->getResult();
            // dump($query);die;
            if ($quinto[0][1] == 0) {
                $this->get('session')->getFlashBag()->add('searchIe', 'La Institución Educativa ' . $formulario['institucioneducativa'] . ' no cuenta con aprendizajes especializados.');
                return $this->redirect($this->generateUrl('bach_exc_alt_dir'));
            }

            /* Verificar si la UE ya ha registrado al director e impreso la ddjj */
            $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');

            $query = $repository->createQueryBuilder('m')
                    ->select('count(m.id)')
                    ->where('m.institucioneducativa = :institucion')
                    ->andWhere('m.gestionTipo = :gestion')
                    //->andWhere('m.esactivo = :esactivo')
                    ->andWhere('m.esoficial = :esoficial')
                    ->setParameter('institucion', $formulario['institucioneducativa'])
                    ->setParameter('gestion', $formulario['gestion'])
                    //->setParameter('esactivo', 't')
                    ->setParameter('esoficial', 't')
                    ->getQuery();

            $impreso = $query->getResult();

            if ($impreso[0][1] > 0) {

                $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');

                $query = $repository->createQueryBuilder('m')
                        ->select('m.carnet, m.complemento, m.paterno, m.materno, m.nombre, ef.entidadfinanciera, m.cuentabancaria, m.fechaNacimiento, m.apellidoEsposo')
                        ->innerJoin('SieAppWebBundle:EntidadfinancieraTipo', 'ef', 'WITH', 'ef.id = m.entidadfinancieraTipo')
                        ->where('m.institucioneducativa = :institucion')
                        ->andWhere('m.gestionTipo = :gestion')
                        //->andWhere('m.esactivo = :esactivo')
                        ->andWhere('m.esoficial = :esoficial')
                        ->setParameter('institucion', $formulario['institucioneducativa'])
                        ->setParameter('gestion', $formulario['gestion'])
                        //->setParameter('esactivo', 't')
                        ->setParameter('esoficial', 't')
                        ->getQuery();

                $director = $query->getSingleResult();

                return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:resultDirector.html.twig', array(
                            'datadirector' => $director,
                ));
            }

            /*
             * Información de la Institución Educativa y Datos de la Directora o el Director
             */
            $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

            $query = $repository->createQueryBuilder('i')
                    ->select('i.id instId, i.institucioneducativa, ct.cargo, p.id dirId, p.carnet, p.paterno, p.materno, p.nombre, p.complemento, p.fechaNacimiento, mi.id dirinsId')
                    ->innerJoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'mi.institucioneducativa = i.id')
                    ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
                    ->innerJoin('SieAppWebBundle:CargoTipo', 'ct', 'WITH', 'mi.cargoTipo = ct.id')
                    ->where('i.id = :institucion')
                    ->andWhere('ct.id in (:esdirector1,:esdirector2)')
                    ->andWhere('mi.gestionTipo in (:gestion)')
                    ->setParameter('institucion', $formulario['institucioneducativa'])
                    ->setParameter('esdirector1', 1)
                    ->setParameter('esdirector2', 12)
                    ->setParameter('gestion', $formulario['gestion'])
                    //->setParameter('gestion1', 2015)
                    //->setParameter('gestion2', 2014)
                    //->setParameter('gestion3', 2013)
                    ->orderBy('mi.gestionTipo', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery();

            $entity = $query->getOneOrNullResult();

            if (!$entity) {
                $this->get('session')->getFlashBag()->add('searchIe', 'El código ingresado no es válido o la Institución Educativa no existe o aún no se consolidó la información.');
                return $this->redirect($this->generateUrl('bach_exc_alt_dir'));
            }

            $form_dir = $this->createMaestroCuentabancariaForm();

            return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:resultSearchIeDir.html.twig', array(
                        'entity' => $entity,
                        'form_dir' => $form_dir->createView(),
            ));
        }
    }

    /*
     * Formulario Maestro Cuenta Bancaria para registro cuenta bancaria director/a UE
     */

    private function createMaestroCuentabancariaForm() {
        $form = $this->createForm(new MaestroCuentabancariaType(), null, array(
            //'action' => $this->generateUrl('bach_exc_alt_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar Cambios'));

        return $form;
    }

    /**
     * Registro del Bachiller de Excelencia.
     *
     */
    public function dirCtaCreateAction(Request $request) {

      
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if($this->fechaActual > $this->fechaCorte) {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $response = new JsonResponse();
        try {
            $form_aux = $request->get('sie_appwebbundle_maestrocuentabancaria');
            //dump($form_aux); die;
            $entidadfinancieraTipoId = $form_aux['entidadfinancieraTipo'];
            $cuentabancaria = $form_aux['cuentabancaria'];
            $carnet = $form_aux['carnet'];
            $complemento = $form_aux['complemento'];
            $paterno = $form_aux['paterno'];
            $materno = $form_aux['materno'];
            $nombre = $form_aux['nombre'];

            $personaId = $form_aux['persona'];
            $maestroinscripcionId = $form_aux['maestroInscripcion'];
            $institucioneducativaId = $form_aux['institucioneducativa'];
            $cargoTipoId = $form_aux['cargoTipo'];
            $gestion = $this->gestionOperativo;

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            /* Verificar si la UE ya ha registrado al director */
            $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');

            $query = $repository->createQueryBuilder('m')
                    ->select('count(m.id)')
                    ->where('m.institucioneducativa = :institucion')
                    ->andWhere('m.gestionTipo = :gestion')
                    //->andWhere('m.esactivo = :esactivo')
                    ->andWhere('m.esoficial = :esoficial')
                    ->setParameter('institucion', $institucioneducativaId)
                    ->setParameter('gestion', 2024)
                    //->setParameter('esactivo', 't')
                    ->setParameter('esoficial', 't')
                    ->getQuery();

            $registro = $query->getResult();

            if ($registro[0][1] > 0) {
                return $response->setData(array('mensaje' => 'Ya existe un registro previo, por tanto no puede realizar más cambios.'));
            }

            $persona = $em->getRepository('SieAppWebBundle:Persona')->find($personaId);
            $inscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($maestroinscripcionId);
            $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucioneducativaId);
            $cargo = $em->getRepository('SieAppWebBundle:CargoTipo')->find($cargoTipoId);           
            $entidadfinanciera = $em->getRepository('SieAppWebBundle:EntidadfinancieraTipo')->find($entidadfinancieraTipoId);
            $gestion = $em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion);

            $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');

            $query = $repository->createQueryBuilder('m')
                    ->where('m.institucioneducativa = :institucion')
                    ->andWhere('m.gestionTipo = :gestion')
                    ->andWhere('m.esoficial = :esoficial')
                    ->setParameter('institucion', $ieducativa)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('esoficial', 't')
                    ->getQuery();

            $directores = $query->getResult();

          
            foreach ($directores as $value) {
                $value->setEsoficial('f');
                $em->persist($value);
                $em->flush();
            }

            $maestrocuenta = new MaestroCuentabancaria();
            $maestrocuenta->setPersona($persona);
            $maestrocuenta->setMaestroInscripcion($inscripcion);
            $maestrocuenta->setInstitucioneducativa($ieducativa);
            $maestrocuenta->setCargoTipo($cargo);
            $maestrocuenta->setEntidadfinancieraTipo($entidadfinanciera);
            $maestrocuenta->setCuentabancaria($cuentabancaria);
            $maestrocuenta->setCarnet(mb_strtoupper($carnet, 'UTF-8'));
            $maestrocuenta->setPaterno(mb_strtoupper($paterno, 'UTF-8'));
            $maestrocuenta->setMaterno(mb_strtoupper($materno, 'UTF-8'));
            $maestrocuenta->setNombre(mb_strtoupper($nombre, 'UTF-8'));
            $maestrocuenta->setComplemento(mb_strtoupper($complemento, 'UTF-8'));
            $maestrocuenta->setEsoficial('t');
            $maestrocuenta->setGestionTipo($gestion);
            $maestrocuenta->setFechaRegistro(new \DateTime('now'));
            $em->persist($maestrocuenta);
            $em->flush();
            $em->getConnection()->commit();

           

            return $response->setData(array('mensaje' => 'Se ha actualizado correctamente la información para: ' . $maestrocuenta->getNombre() . ' ' . $maestrocuenta->getPaterno() . ' ' . $maestrocuenta->getMaterno() . ', con C.I.: ' . $maestrocuenta->getCarnet() . ' y Complemento: ' . $maestrocuenta->getComplemento()));
        } catch (Exception $ex) {
          
            $em->getConnection()->rollback();
            //return $response->setData(array('mensaje' => 'Error en el registro.'));
            return $response->setData(array('mensaje' => $ex));
        }

        return $response->setData(array('mensaje' => 'Error en el registro.'));
    }

    /*
     * Muestra el formulario de búsqueda de Institución Educativa
     */

    public function indexRstAction() {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        /*if($this->fechaActual > $this->fechaCorte) {
            return $this->redirect($this->generateUrl('principal_web'));
        }*/

        $form = $this->createSearchIeRstForm();

        return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:index_rst.html.twig', array(
                    'form' => $form->createView(),
        ));
    }


    private function createSearchIeRstForm() {
        $form = $this->createForm(new SelectIeType(), null, array(
            'action' => $this->generateUrl('bach_exc_alt_ie_rst_search'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Buscar'));

        return $form;
    }

    /*
     * Muestra el resultado de la búsqueda de Institución Educativa
     */

    public function resultSearchIeAction(Request $request) {
        // dump('ok');die;
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection(); 

        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        /*if($this->fechaActual > $this->fechaCorte) {
            return $this->redirect($this->generateUrl('principal_web'));
        }*/
       

        $form = $this->createSearchIeForm();
        $form->handleRequest($request);

        if ($form->isValid()) {

            $formulario = $form->getData();

            /*
            * verificamos si tiene tuicion
            */
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $formulario['institucioneducativa']);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            if ($aTuicion[0]['get_ue_tuicion']) {
                $institucion = $formulario['institucioneducativa'];
                $gestion = $formulario['gestion'];
            } else {
                $this->get('session')->getFlashBag()->add('searchIe', 'No tiene tuición sobre la Institución Educativa');
            return $this->redirect($this->generateUrl('bach_exc_alt'));
            }
            // $gestion = 2022;
            $bachilleres=$this->listaregistoest($institucion, $gestion);
            if (count($bachilleres) > 0){ 
                return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:resultBachilleres.html.twig', array(
                    'bachilleres' => $bachilleres,
                ));
            } 
            /*
            * Registra acceso para declaración jurada
            */
            $em->getConnection()->beginTransaction();
            $institucioneducativaOperativoLog = new InstitucioneducativaOperativoLog();
            $institucioneducativaOperativoLog->setInstitucioneducativaOperativoLogTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLogTipo')->find(14));
            $institucioneducativaOperativoLog->setGestionTipoId($gestion);
            $institucioneducativaOperativoLog->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(3));
            $institucioneducativaOperativoLog->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucion));
            $institucioneducativaOperativoLog->setInstitucioneducativaSucursal(0);
            $institucioneducativaOperativoLog->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(54));
            $institucioneducativaOperativoLog->setDescripcion('IBD Preliminar');
            $institucioneducativaOperativoLog->setEsexitoso('t');
            $institucioneducativaOperativoLog->setEsonline('t');
            $institucioneducativaOperativoLog->setUsuario($this->session->get('userId'));
            $institucioneducativaOperativoLog->setFechaRegistro(new \DateTime('now'));
            $institucioneducativaOperativoLog->setClienteDescripcion($_SERVER['HTTP_USER_AGENT']);
            $em->persist($institucioneducativaOperativoLog);
            $em->flush();
            $em->getConnection()->commit();


            /*
            * Verificar si la UE cuenta con aprendizajes especializados
            */
            $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

            $query = $repository->createQueryBuilder('i')
                    ->select('count(i.id)')
                    ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion', 'sia', 'WITH', 'sia.institucioneducativa = i.id')
                    ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad', 'sae', 'WITH', 'sia.acreditacionEspecialidad = sae.id')
                    ->innerJoin('SieAppWebBundle:SuperiorAcreditacionTipo', 'sat', 'WITH', 'sae.superiorAcreditacionTipo = sat.id')
                    ->where('i.id = :institucion')
                    ->andWhere('sat.id = :codigo')
                    ->andWhere('sia.gestionTipo = :gestion')
                    ->setParameter('institucion', $institucion )
                    ->setParameter('codigo', 52)
                    ->setParameter('gestion',$gestion)
                    ->getQuery();

            $quinto = $query->getResult();

            if ($quinto[0][1] == 0) {
                $this->get('session')->getFlashBag()->add('searchIe', 'La Institución Educativa ' . $formulario['institucioneducativa'] . ' no cuenta con aprendizajes especializados.');
                return $this->redirect($this->generateUrl('bach_exc_alt'));
            }

            $closeopequinto = $this->get('funciones')->verificarSextoSecundariaCerrado($institucion,$gestion);
            // dump($closeopequinto);die;
            if ($closeopequinto==false){
                $this->get('session')->getFlashBag()->add('searchIe', 'La Institución Educativa ' . $formulario['institucioneducativa'] . ' no cerro el operativo de aprendizajes especializados.');
                return $this->redirect($this->generateUrl('bach_exc_alt'));
            }
            /* Verificar si la UE ya ha registrado al bachiller destacado */
            // $repository = $em->getRepository('SieAppWebBundle:EstudianteDestacado');

            // $query = $repository->createQueryBuilder('e')
            //         ->select('count(e.id)')
            //         ->where('e.institucioneducativa = :institucion')
            //         ->andWhere('e.gestionTipo = :gestion')
            //         ->andWhere('e.esoficial = :esoficial')
            //         ->setParameter('institucion', $institucion)
            //         ->setParameter('gestion', $gestion)
            //         ->setParameter('esoficial', 't')
            //         ->getQuery();

            // $registro = $query->getResult();
            // if ($registro[0][1] > 1) {
            //     $repository = $em->getRepository('SieAppWebBundle:EstudianteDestacado');

            //     $query = $repository->createQueryBuilder('ed')
            //             ->select('e.codigoRude, e.carnetIdentidad, e.paterno, e.materno, e.nombre, g.genero, ed.promedioFinal, ed.promedioSem1, ed.promedioSem2')
            //             ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ed.estudiante = e.id')
            //             ->innerJoin('SieAppWebBundle:GeneroTipo', 'g', 'WITH', 'e.generoTipo = g.id')
            //             ->where('ed.institucioneducativa = :institucion')
            //             ->andWhere('ed.gestionTipo = :gestion')
            //             ->andWhere('ed.esoficial = :esoficial')
            //             ->setParameter('institucion', $institucion)
            //             ->setParameter('gestion', $gestion)
            //             ->setParameter('esoficial', 't')
            //             ->getQuery();

            //     $bachilleres = $query->getResult();
            //     return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:resultBachilleres.html.twig', array(
            //                 'bachilleres' => $bachilleres,
            //     ));
            // }

            /*
             * Lista de estudiantes de aprendizajes especializados
             */

            $query = $em->getConnection()->prepare("SELECT * from sp_genera_alternativa_bachiller_destacado_vista('".$gestion."', '".$institucion."')");
            $query->execute();
            $estudiantes = $query->fetchAll();
            // dump($estudiantes);die;
            $est_oficial = [];
            $est_duplicadosM = [];
            $est_duplicadosF = [];

            foreach ($estudiantes as $estudiante) {
                if ($estudiante['ban_manual'] == 1 || $estudiante['ban_manual'] == 3) {
                    $est_oficial[] = $estudiante;
                    // $est_duplicadosF[] = $estudiante;
                } elseif ($estudiante['ban_manual'] == 2){
                    $est_duplicadosM[] = $estudiante;
                } elseif ($estudiante['ban_manual'] == 4) {
                    $est_duplicadosF[] = $estudiante;
                }
            }

            return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:resultSearchIe.html.twig', array(
                'estoficial' => $est_oficial,
                'estduplicadoM' => $est_duplicadosM,
                'estduplicadoF' => $est_duplicadosF,
            ));
        }
    }



    public function resultSearchIeRstAction(Request $request) {
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        /*if($this->fechaActual > $this->fechaCorte) {
            return $this->redirect($this->generateUrl('principal_web'));
        }*/

        $form = $this->createSearchIeRstForm();
        $form->handleRequest($request);

        if ($form->isValid()) {

            $formulario = $form->getData();

            $em = $this->getDoctrine()->getManager();

            /*
            * Verificar si la UE cuenta con aprendizajes especializados
            */

            $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

            $query = $repository->createQueryBuilder('i')
                    ->select('count(i.id)')
                    ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion', 'sia', 'WITH', 'sia.institucioneducativa = i.id')
                    ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad', 'sae', 'WITH', 'sia.acreditacionEspecialidad = sae.id')
                    ->innerJoin('SieAppWebBundle:SuperiorAcreditacionTipo', 'sat', 'WITH', 'sae.superiorAcreditacionTipo = sat.id')
                    ->where('i.id = :institucion')
                    ->andWhere('sat.codigo = :codigo')
                    ->andWhere('sia.gestionTipo = :gestion')
                    ->setParameter('institucion', $formulario['institucioneducativa'])
                    ->setParameter('codigo', 3)
                    ->setParameter('gestion', $formulario['gestion'])
                    ->getQuery();

            $quinto = $query->getResult();

            if ($quinto[0][1] == 0) {
                $this->get('session')->getFlashBag()->add('searchIe', 'La Institución Educativa ' . $formulario['institucioneducativa'] . ' no cuenta con aprendizajes especializados.');
                return $this->redirect($this->generateUrl('bach_exc_alt_rst'));
            }

            $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');

            $query = $repository->createQueryBuilder('m')
                    ->select('m.id, m.carnet, m.complemento, m.paterno, m.materno, m.nombre, ef.entidadfinanciera, m.cuentabancaria, m.esoficial estado, m.fechaNacimiento, m.apellidoEsposo')
                    ->innerJoin('SieAppWebBundle:EntidadfinancieraTipo', 'ef', 'WITH', 'ef.id = m.entidadfinancieraTipo')
                    ->where('m.institucioneducativa = :institucion')
                    ->andWhere('m.gestionTipo = :gestion')
                    ->andWhere('m.esoficial = :esoficial')
                    ->setParameter('institucion', $formulario['institucioneducativa'])
                    ->setParameter('gestion', $this->gestionOperativo)
                    ->setParameter('esoficial', 't')
                    ->getQuery();

            $director = $query->getOneOrNullResult();
            
            $repository = $em->getRepository('SieAppWebBundle:EstudianteDestacado');

            $query = $repository->createQueryBuilder('ed')
                    ->select('ed.codigoRude, ed.carnetIdentidad, ed.paterno, ed.materno, ed.nombre, g.genero, ed.promedioFinal, ed.id edId, g.id gen')
                    ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ed.estudiante = e.id')
                    ->innerJoin('SieAppWebBundle:GeneroTipo', 'g', 'WITH', 'e.generoTipo = g.id')
                    ->where('ed.institucioneducativa = :institucion')
                    ->andWhere('ed.gestionTipo = :gestion')
                    ->andWhere('ed.esoficial = :esoficial')
                    ->setParameter('institucion', $formulario['institucioneducativa'])
                    ->setParameter('gestion', $formulario['gestion'])
                    ->setParameter('esoficial', 't')
                    ->getQuery();

            $bachilleres = $query->getResult();
            
            return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:resultRst.html.twig', array(
                        'datadirector' => $director,
                        'ieducativa' => $formulario['institucioneducativa'],
                        'bachilleres' => $bachilleres
            ));
        }
    }

    public function dirCtaRstAction($ie) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if($this->fechaActual > $this->fechaCorte) {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');

        $query = $repository->createQueryBuilder('m')
                ->where('m.institucioneducativa = :institucion')
                ->setParameter('institucion', $ie)
                ->getQuery();

        $directores = $query->getResult();

        foreach ($directores as $value) {
            $value->setEsactivo('f');
            $value->setEsoficial('f');
            $em->persist($value);
            $em->flush();
        }

        $this->get('session')->getFlashBag()->add('searchIe', 'Los datos han sido restablecidos correctamente. (Directora/Director)');
        return $this->redirect($this->generateUrl('bach_exc_alt_rst'));
    }

    public function beRstAction($ie, $ed, $g) {

        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        /*if($this->fechaActual > $this->fechaCorte) {
            return $this->redirect($this->generateUrl('principal_web'));
        }*/

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:EstudianteDestacado');

        $query = $repository->createQueryBuilder('ed')
                ->where('ed.institucioneducativa = :institucion')
                ->andWhere('ed.id = :destacado')
                ->andWhere('ed.generoTipo = :genero')
                ->setParameter('institucion', $ie)
                ->setParameter('destacado', $ed)
                ->setParameter('genero', $g)
                ->getQuery();

        $bachiller = $query->getOneOrNullResult();

        $inscripcion = $bachiller->getEstudianteInscripcion()->getId();
        $matricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4);
        $beinscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($inscripcion);
        $beinscripcion->setEstadomatriculaTipo($matricula);
        $em->persist($beinscripcion);
        $em->flush();

        $bachiller->setImpreso('f');
        $bachiller->setEsoficial('f');
        $em->persist($bachiller);
        $em->flush();

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:EstudianteDestacado');

        $query = $repository->createQueryBuilder('ed')
                ->where('ed.institucioneducativa = :institucion')
                ->andWhere('ed.generoTipo = :genero')
                ->setParameter('institucion', $ie)
                ->setParameter('genero', $g)
                ->getQuery();

        $bachilleres = $query->getResult();

        foreach ($bachilleres as $value) {
            $value->setImpreso('f');
            $value->setEsoficial('f');
            $em->persist($value);
            $em->flush();
        }

        $this->get('session')->getFlashBag()->add('searchIe', 'Los datos han sido restablecidos correctamente. (Bachiller de Excelencia)');
        return $this->redirect($this->generateUrl('bach_exc_alt_rst'));
    }

    /*
     * Formulario de Estudiante Destacado para Registro Bachiller de Excelencia
     */

    private function createEstudianteDestacadoForm() {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $this->createForm(new EstudianteDestacadoAlternativaType(), null, array(
            //'action' => $this->generateUrl('bach_exc_alt_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }

    public function infoStudentAction(Request $request, $estId, $estinsId, $instId, $genId) {
        $em = $this->getDoctrine()->getManager();

        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($estId);

        $form_ed = $this->createEstudianteDestacadoForm();

        return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:infoStudent.html.twig', array(
                    'datastudent' => $estudiante,
                    'form_ed' => $form_ed->createView(),
                    'estId' => $estId,
                    'estinsId' => $estinsId,
                    'instId' => $instId,
                    'genId' => $genId
        ));
    }

    /**
     * Registro del Bachiller de Excelencia.
     *
     */
    public function xbeCreateAction(Request $request) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->redirect($this->generateUrl('principal_web'));

        /*if($this->fechaActual > $this->fechaCorte) {
            return $this->redirect($this->generateUrl('principal_web'));
        }*/

        $response = new JsonResponse();
        try {
            $form_aux = $request->get('sie_appwebbundle_estudiantedestacado');
            $promedioSem1 = $form_aux['promedioSem1'];
            $promedioSem2 = $form_aux['promedioSem2'];
            $promedioFinal = $form_aux['promedioFinal'];
            $estudianteInscripcionId = $form_aux['estudianteInscripcion'];
            $generoTipoId = $form_aux['generoTipo'];
            $institucioneducativaId = $form_aux['institucioneducativa'];
            $estudianteId = $form_aux['estudiante'];

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            /* Verificar si la UE ya ha registrado al bachiller destacado para un determinado género */
            $repository = $em->getRepository('SieAppWebBundle:EstudianteDestacado');

            $query = $repository->createQueryBuilder('e')
                    ->select('count(e.id)')
                    ->where('e.institucioneducativa = :institucion')
                    ->andWhere('e.gestionTipo = :gestion')
                    ->andWhere('e.generoTipo = :genero')
                    ->andWhere('e.esoficial = :esoficial')
                    ->setParameter('institucion', $institucioneducativaId)
                    ->setParameter('gestion', $this->gestionOperativo)
                    ->setParameter('genero', $generoTipoId)
                    ->setParameter('esoficial', 't')
                    ->getQuery();

            $registro = $query->getResult();

            if ($registro[0][1] > 0) {
                return $response->setData(array('mensaje' => 'Ya existe un registro previo para este género, por tanto no puede realizar más cambios.'));
            }

            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($estudianteId);
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($estudianteInscripcionId);
            $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucioneducativaId);
            $genero = $em->getRepository('SieAppWebBundle:GeneroTipo')->find($generoTipoId);
            $gestion = $em->getRepository('SieAppWebBundle:GestionTipo')->find($this->gestionOperativo);

            $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(55));

            $estudiantedestacado = new EstudianteDestacado();
            $estudiantedestacado->setFechaRegistro(new \DateTime('now'));
            $estudiantedestacado->setEstudiante($estudiante);
            $estudiantedestacado->setFechaNacimiento($estudiante->getFechaNacimiento());
            $estudiantedestacado->setComplemento($estudiante->getComplemento());
            $estudiantedestacado->setEstudianteId($estudianteId);
            $estudiantedestacado->setEstudianteInscripcion($inscripcion);
            $estudiantedestacado->setGeneroTipo($genero);
            $estudiantedestacado->setGestionTipo($gestion);
            $estudiantedestacado->setInstitucioneducativa($ieducativa);
            $estudiantedestacado->setPromedioSem1($promedioSem1);
            $estudiantedestacado->setPromedioSem2($promedioSem2);
            $estudiantedestacado->setPromedioFinal($promedioFinal);
            $estudiantedestacado->setIpOrigen($_SERVER['HTTP_USER_AGENT']. ' - '. $_SERVER['REMOTE_ADDR']);
            $estudiantedestacado->setEsoficial('t');
            $estudiantedestacado->setNombre($estudiante->getNombre());
            $estudiantedestacado->setPaterno($estudiante->getPaterno());
            $estudiantedestacado->setMaterno($estudiante->getMaterno());
            $estudiantedestacado->setCarnetIdentidad($estudiante->getCarnetIdentidad());
            $estudiantedestacado->setCodigoRude($estudiante->getCodigoRude());
            $estudiantedestacado->setOrgCurricularTipoId(2);

            $em->persist($estudiantedestacado);
            $em->persist($inscripcion);
            $em->flush();
            $em->getConnection()->commit();

            return $response->setData(array('mensaje' => 'Se ha registrado satisfactoriamente a ' . $estudiante->getMaterno() . ' ' . $estudiante->getPaterno() . ' ' . $estudiante->getMaterno() . ' con un Promedio Final de ' . $promedioFinal, 'genero' => $generoTipoId));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $response->setData(array('mensaje' => 'Error en el registro.'));
        }

        return $response->setData(array('mensaje' => 'Error en el registro.'));
    }



    public function declaracionJuradaAction(Request $request) {
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $seleccion_f = $request->request->get('seleccionf');
        $seleccion_m = $request->request->get('seleccionm');
        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('currentyear');
        // $gestion = 2022;
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');
        $em = $this->getDoctrine()->getManager();

        /*
        * Verificar si la UE cuenta con aprendizajes especializados
        */
        
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

        $query = $repository->createQueryBuilder('i')
                ->select('count(i.id)')
                ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion', 'sia', 'WITH', 'sia.institucioneducativa = i.id')
                ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad', 'sae', 'WITH', 'sia.acreditacionEspecialidad = sae.id')
                ->innerJoin('SieAppWebBundle:SuperiorAcreditacionTipo', 'sat', 'WITH', 'sae.superiorAcreditacionTipo = sat.id')
                ->where('i.id = :institucion')
                ->andWhere('sat.id = :codigo')
                ->andWhere('sia.gestionTipo = :gestion')
                ->setParameter('institucion', $institucion)
                ->setParameter('codigo', 52)
                ->setParameter('gestion', $this->gestionOperativo)
                ->getQuery();

        $quinto = $query->getResult();
        
        $msj = '';
        $status = 200;
        if (count($quinto) == 0) {
            $msj = 'La Institución Educativa ' . $institucion . ' no cuenta con aprendizajes especializados.';
            $status = 400;
            return  new JsonResponse(array(
                'msj' => $msj,
                'status' => $status,
            ));
        }

        $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');
        
        $query = $repository->createQueryBuilder('m')
                ->select('m.carnet, m.complemento, m.paterno, m.materno, m.nombre, ef.entidadfinanciera, m.cuentabancaria, m.fechaNacimiento, m.apellidoEsposo')
                ->innerJoin('SieAppWebBundle:EntidadfinancieraTipo', 'ef', 'WITH', 'ef.id = m.entidadfinancieraTipo')
                ->where('m.institucioneducativa = :institucion')
                ->andWhere('m.esoficial = :esoficial')
                ->andWhere('m.gestionTipo = :gestion')
                ->setParameter('institucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('esoficial', 't')
                ->getQuery();

        $director = $query->getOneOrNullResult();
        if (count($director)==0) {
            $msj = 'La Institución Educativa ' . $institucion . ' no cuenta con declaración Juradad de cuenta Director';
            $status = 400;
            return  new JsonResponse(array(
                'msj' => $msj,
                'status' => $status,
            ));
        }

        $query = $em->getConnection()->prepare("SELECT * from sp_genera_alternativa_bachiller_destacado_vista('".$gestion."', '".$institucion."')");
        $query->execute();
        $estudiantes = $query->fetchAll();
        // dump($estudiantes);die;
        $est_oficialM = [];
        $est_oficialF = [];

        foreach ($estudiantes as $estudiante) {
            if ($estudiante['ban_manual'] == 1) {
                $est_oficialM[] = $estudiante;
            } elseif ($estudiante['ban_manual'] == 3) {
                $est_oficialF[] = $estudiante;
            }
        }
        if (count($est_oficialM) == 0 and $seleccion_m != null){
            foreach ($estudiantes as $estudiante) {
                if ($estudiante['ban_manual'] == 2 and $estudiante['estudiante_inscripcion_id']== $seleccion_m) {
                    $est_oficialM[] = $estudiante;
                } 
            }
        }

        if (count($est_oficialF) == 0 and $seleccion_f != null){
            foreach ($estudiantes as $estudiante) {
                if ($estudiante['ban_manual'] == 4 and $estudiante['estudiante_inscripcion_id']== $seleccion_f) {
                    $est_oficialF[] = $estudiante;
                } 
            }
        }

        $bachilleres=$this->listaregistoest($institucion, $gestion);
        if (count($est_oficialF) > 0 and count($bachilleres)== 0){
            $query = $em->getConnection()->prepare("SELECT * from sp_genera_alternativa_bachiller_destacado_guarda('".$est_oficialF[0]['estudiante_inscripcion_id']."','".$est_oficialF[0]['nota_cuant_prom']."', '".$id_usuario."')");
            $query->execute();
        }
        if (count($est_oficialM) > 0 and count($bachilleres)== 0){
            $query = $em->getConnection()->prepare("SELECT * from sp_genera_alternativa_bachiller_destacado_guarda('".$est_oficialM[0]['estudiante_inscripcion_id']."','".$est_oficialM[0]['nota_cuant_prom']."', '".$id_usuario."')");
            $query->execute();
        }
        $ruta ='';
        if (count($bachilleres) == 0){ 
            $ruta = 'resultDDJJest.html.twig';
            return new JsonResponse([
                'status' => 200, // or any other appropriate success code
                'view' => $this->renderView('SieAppWebBundle:BachillerExcelenciaAlternativa:'.$ruta, [
                    'datadirector' => $director,
                    'bachilleres' => $bachilleres,
                ]),
            ]);
        } else {
            $ruta = 'resultDDJJ.html.twig';
            return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:'.$ruta, array(
                    'datadirector' => $director,
                    'bachilleres' => $bachilleres
            ));
        } 

    }

    public function listaregistoest($institucion, $gestion){
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('SieAppWebBundle:EstudianteDestacado');

        $query = $repository->createQueryBuilder('ed')
                ->select('ed.codigoRude, ed.carnetIdentidad, ed.paterno, ed.materno, ed.nombre, g.genero, ed.promedioFinal, ed.promedioSem1, ed.promedioSem2')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ed.estudiante = e.id')
                ->innerJoin('SieAppWebBundle:GeneroTipo', 'g', 'WITH', 'e.generoTipo = g.id')
                ->where('ed.institucioneducativa = :institucion')
                ->andWhere('ed.gestionTipo = :gestion')
                ->andWhere('ed.esoficial = :esoficial')
                ->setParameter('institucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('esoficial', 't')
                ->getQuery();

        $bachilleres = $query->getResult();
        return $bachilleres;
    }

    public function impresionDDJJAction() {
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        $em = $this->getDoctrine()->getManager();

        $bachilleres = $em->getRepository('SieAppWebBundle:EstudianteDestacado')->findByInstitucioneducativa($institucion);

        foreach ($bachilleres as $estudiante) {
            $estudiante->setImpreso('t');
            $em->persist($estudiante);
        }

        $em->flush();

        $gestion_reporte = $this->gestionOperativo;
        // $gestion_reporte = 2022;
        // dump($gestion_reporte);die;

        $arch = 'DECLARACION_JURADA_BACHILLER_' . $institucion . '_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_EstudianteExcelencia_unidadeducativa_alternativa_v3_igg.rptdesign&__format=pdf&&codue=' . $institucion . '&gestion='.$gestion_reporte.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function impresionHistorialEstAction($gen, Request $request){
        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('currentyear');
        // $gestion = 2022;
        $gen = strtoupper($gen);
        if ($gen !== "FEMENINO" && $gen !== "MASCULINO") {
           return $this->redirect($this->generateUrl('bach_exc_alt'));
        } 
        $arch = 'IBD_HISTORIAL_ESTUDIANTES_' . $gen . '_'. $institucion  . '_' . date('Ymd') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        if($gen=='FEMENINO'){
            $archivo = 'reg_dj_CalculoAutomaticoEstudianteExcelencia_unidadeducativa_alter_fem_v3_ejea.rptdesign';
        } elseif ($gen=='MASCULINO'){
            $archivo = 'reg_dj_CalculoAutomaticoEstudianteExcelencia_unidadeducativa_alter_mas_v3_ejea.rptdesign';
        }
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') .$archivo.'&__format=pdf&&codue=' . $institucion . '&gestion='.$gestion.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function declaracionJuradaDirAction(Request $request) {
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        /*
         * Verificar si la UE cuenta con aprendizajes especializados
         */

        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

        $query = $repository->createQueryBuilder('i')
                ->select('count(i.id)')
                ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion', 'sia', 'WITH', 'sia.institucioneducativa = i.id')
                ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad', 'sae', 'WITH', 'sia.acreditacionEspecialidad = sae.id')
                ->innerJoin('SieAppWebBundle:SuperiorAcreditacionTipo', 'sat', 'WITH', 'sae.superiorAcreditacionTipo = sat.id')
                ->where('i.id = :institucion')
                ->andWhere('sat.codigo = :codigo')
                ->andWhere('sia.gestionTipo = :gestion')
                ->setParameter('institucion', $institucion)
                ->setParameter('codigo', 3)
                ->setParameter('gestion', $this->gestionOperativo)
                ->getQuery();

        $quinto = $query->getResult();

        if ($quinto[0][1] == 0) {
            $this->get('session')->getFlashBag()->add('searchIe', 'La Institución Educativa ' . $formulario['institucioneducativa'] . ' no cuenta con aprendizajes especializados.');
            return $this->redirect($this->generateUrl('bach_exc_alt_dir'));
        }

        $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');

        $query = $repository->createQueryBuilder('m')
                ->select('m.carnet, m.complemento, m.paterno, m.materno, m.nombre, ef.entidadfinanciera, m.cuentabancaria, m.fechaNacimiento, m.apellidoEsposo')
                ->innerJoin('SieAppWebBundle:EntidadfinancieraTipo', 'ef', 'WITH', 'ef.id = m.entidadfinancieraTipo')
                ->where('m.institucioneducativa = :institucion')
                ->andWhere('m.esoficial = :esoficial')
                ->andWhere('m.gestionTipo = :gestion')
                ->setParameter('institucion', $institucion)
                ->setParameter('esoficial', 't')
                ->setParameter('gestion', $this->gestionOperativo)
                ->getQuery();

        $director = $query->getOneOrNullResult();

        return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:resultDDJJDir.html.twig', array(
                    'datadirector' => $director,
        ));
    }

    public function impresionDDJJDirAction() {
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');

        $query = $repository->createQueryBuilder('m')
                ->where('m.institucioneducativa = :institucion')
                ->andWhere('m.gestionTipo = :gestion')
                ->setParameter('institucion', $institucion)
                ->setParameter('gestion', $this->gestionOperativo)
                ->getQuery();

        $directores = $query->getResult();

        foreach ($directores as $value) {
            $value->setEsactivo('t');
            $em->persist($value);
            $em->flush();
        }

        $gestion_reporte = $this->gestionOperativo;

        $arch = 'DECLARACION_JURADA_DIRECTOR_' . $institucion . '_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_DirectorEstudianteExcelencia_unidadeducativa_regular_v2_afv.rptdesign&__format=pdf&&codue=' . $institucion . '&gestion='.$gestion_reporte.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function indexReporteDistritoAction() {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $this->createSearchDistritoForm();

        return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:index_reporte_dist.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    private function createSearchDistritoForm() {
        $form = $this->createForm(new SelectIeType(), null, array(
            'action' => $this->generateUrl('bach_exc_alt_rep_dist_search'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Reporte por distrito'));

        return $form;
    }

    public function resultSearchDistRepAction(Request $request) {
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $form = $this->createSearchDistritoForm();
        $form->handleRequest($request);
        $gestion = $this->gestionOperativo;

        if ($form->isValid()) {

            $formulario = $form->getData();

            $arch = 'DECLARACION_JURADA_DISTRITO_' . $formulario['institucioneducativa'] . '_' . date('YmdHis') . '.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_EstudianteExcelencia_DIstritoRegular_v2_afv_hcq.rptdesign&distrito=' . $formulario['institucioneducativa'] . '&gestion='.$gestion.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        }
    }

    public function validarEstudianteIbdSegipAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        
        $form = $request->get('form');

        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($form['idEstudiante']);

        $datos = array(
            'complemento'=>$estudiante->getComplemento(),
            'primer_apellido'=>$estudiante->getPaterno(),
            'segundo_apellido'=>$estudiante->getMaterno(),
            'nombre'=>$estudiante->getNombre(),
            'fecha_nacimiento'=>$estudiante->getFechaNacimiento()->format('d-m-Y')
        );
        
        if($estudiante){
            if($estudiante->getCarnetIdentidad()){
                $resultadoEstudiante = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($estudiante->getCarnetIdentidad(),$datos,'prod','academico');

                if($resultadoEstudiante){
                    $mensaje = "Se realizó el proceso satisfactoriamente. Los datos de la/el estudiante:".$estudiante->getCodigoRude().", se validaron correctamente con SEGIP.";
                    $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneBy(array('llave' => $estudiante->getCodigoRude(), 'validacionReglaTipo' => 37));
                    $estudiante->setSegipId(1);
                    $em->persist($estudiante);
                    $em->flush();
                    if($vproceso) {
                        $this->ratificar($vproceso);
                    }
                    $this->addFlash('successSegip', $mensaje);
                } else {
                    $mensaje = "No se realizó la validación con SEGIP. Verifique la información de la/el estudiante.";
                    $this->addFlash('warningSegip', $mensaje);
                }                
            } else {
                $mensaje = "No se realizó la validación con SEGIP. Actualice el C.I. de la/el estudiante.";
                $this->addFlash('warningSegip', $mensaje);
            }
        } else {
            $mensaje = "No se realizó la validación con SEGIP. No existe información de la/el estudiante con el código RUDE proporcionado.";
            $this->addFlash('warningSegip', $mensaje);
        }

        if($form['subsistema'] == 2) {
            return $this->redirect($this->generateUrl('bach_exc_alt'));
        } else {
            return $this->redirect($this->generateUrl('bach_exc'));
        }
    }

    private function ratificar($vproceso){
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $arrayRegistro = null;

        try {
            // Antes
            $arrayRegistro['id'] = $vproceso->getId();
            $arrayRegistro['fecha_proceso'] = $vproceso->getFechaProceso();
            $arrayRegistro['validacion_regla_tipo_id'] = $vproceso->getValidacionReglaTipo()->getId();
            $arrayRegistro['llave'] = $vproceso->getLlave();
            $arrayRegistro['gestion_tipo_id'] = $vproceso->getGestionTipoId();
            $arrayRegistro['periodo_tipo_id'] = $vproceso->getPeriodoTipoId();
            $arrayRegistro['es_activo'] = $vproceso->getEsActivo();
            $arrayRegistro['obs'] = $vproceso->getObs();
            $arrayRegistro['institucioneducativa_id'] = $vproceso->getInstitucioneducativaId();
            $arrayRegistro['lugar_tipo_id_distrito'] = $vproceso->getLugarTipoIdDistrito();
            $arrayRegistro['solucion_tipo_id'] = $vproceso->getSolucionTipoId();
            $arrayRegistro['omitido'] = $vproceso->getOmitido();

            $antes = json_encode($arrayRegistro);

            // despues
            $arrayRegistro = null;

            $vproceso->setEsActivo(true);
            $em->persist($vproceso);
            $em->flush();
            // $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);

            $arrayRegistro['id'] = $vproceso->getId();
            $arrayRegistro['fecha_proceso'] = $vproceso->getFechaProceso();
            $arrayRegistro['validacion_regla_tipo_id'] = $vproceso->getValidacionReglaTipo()->getId();
            $arrayRegistro['llave'] = $vproceso->getLlave();
            $arrayRegistro['gestion_tipo_id'] = $vproceso->getGestionTipoId();
            $arrayRegistro['periodo_tipo_id'] = $vproceso->getPeriodoTipoId();
            $arrayRegistro['es_activo'] = $vproceso->getEsActivo();
            $arrayRegistro['obs'] = $vproceso->getObs();
            $arrayRegistro['institucioneducativa_id'] = $vproceso->getInstitucioneducativaId();
            $arrayRegistro['lugar_tipo_id_distrito'] = $vproceso->getLugarTipoIdDistrito();
            $arrayRegistro['solucion_tipo_id'] = $vproceso->getSolucionTipoId();
            $arrayRegistro['omitido'] = $vproceso->getOmitido();

            $despues = json_encode($arrayRegistro);

            // registro del log
            $resp = $defaultController->setLogTransaccion(
                $vproceso->getId(),
                'validacion_proceso',
                'U',
                json_encode(array('browser' => $_SERVER['HTTP_USER_AGENT'],'ip'=>$_SERVER['REMOTE_ADDR'])),
                $this->session->get('userId'),
                '',
                $despues,
                $antes,
                'SIGED',
                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );

            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();           
        }
    }

}
