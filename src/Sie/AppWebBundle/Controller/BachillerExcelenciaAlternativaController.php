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
        $this->fechaCorte = new \DateTime('2023-11-30');
        $this->gestionOperativo = $this->session->get('currentyear');
    }

    /*
     * Muestra el formulario de búsqueda de Institución Educativa
     */

    public function indexAction() {

       
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

        $form->add('submit', 'submit', array('label' => 'Buscar'));

        return $form;
    }


    /*
     * Muestra el formulario de búsqueda de Institución Educativa
     */

    public function indexDirAction() {
       
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
                    ->setParameter('gestion', $gestion)
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
            return $response->setData(array('mensaje' => 'Error en el registro.'));
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
            dcastillo: si la UE esta en la tabla ues_sin_reporte2022, se retorna a la vista principal
            */
            /*$sql= "
            SELECT COUNT
                ( * ) as existe
            FROM
                ues_sin_reporte2022                
            WHERE ues_sin_reporte2022.id =  " .$formulario['institucioneducativa'];
            
            $stmt = $db->prepare($sql);
            $params = array();
            $stmt->execute($params);
            $po = $stmt->fetchAll();
            $ue_no_reporta = $po[0]['existe'];
            if($ue_no_reporta > 0) {
                return $this->redirect($this->generateUrl('principal_web'));
            }*/

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
                return $this->redirect($this->generateUrl('bach_exc_alt_dir'));
            }

            /* Verificar si la UE ya ha registrado al bachiller destacado */
            $repository = $em->getRepository('SieAppWebBundle:EstudianteDestacado');

            $query = $repository->createQueryBuilder('e')
                    ->select('count(e.id)')
                    ->where('e.institucioneducativa = :institucion')
                    ->andWhere('e.gestionTipo = :gestion')
                    ->andWhere('e.esoficial = :esoficial')
                    ->setParameter('institucion', $formulario['institucioneducativa'])
                    ->setParameter('gestion', $formulario['gestion'])
                    ->setParameter('esoficial', 't')
                    ->getQuery();

            $registro = $query->getResult();

            if ($registro[0][1] > 1) {
                $repository = $em->getRepository('SieAppWebBundle:EstudianteDestacado');

                $query = $repository->createQueryBuilder('ed')
                        ->select('e.codigoRude, e.carnetIdentidad, e.paterno, e.materno, e.nombre, g.genero, ed.promedioFinal, ed.promedioSem1, ed.promedioSem2')
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

                return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:resultBachilleres.html.twig', array(
                            'bachilleres' => $bachilleres,
                ));
            }

            /*
             * Lista de estudiantes de aprendizajes especializados
             */
            // $repository = $em->getRepository('SieAppWebBundle:SuperiorFacultadAreaTipo');

            // $query = $repository->createQueryBuilder('a')
            //         ->select('j.id estId, j.codigoRude, j.carnetIdentidad, j.paterno, j.materno, j.nombre, i.id estinsId, ie.id instId, gn.id genId, gn.genero, pt.paralelo, nt.nivel, ct.ciclo, gt.grado')
            //         //->select('emt.estadomatricula, emt.id as estadomatriculaId, j.id, j.carnetIdentidad, j.codigoRude, j.paterno, j.materno, j.nombre, j.fechaNacimiento, i.id as eInsId, a.codigo as nivelId, b.codigo as cicloId, d.codigo as gradoId')
            //         ->innerJoin('SieAppWebBundle:SuperiorEspecialidadTipo', 'b', 'WITH', 'a.id = b.superiorFacultadAreaTipo')
            //         ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad', 'c', 'WITH', 'b.id = c.superiorEspecialidadTipo')
            //         ->innerJoin('SieAppWebBundle:SuperiorAcreditacionTipo', 'd', 'WITH', 'c.superiorAcreditacionTipo=d.id')
            //         ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion', 'e', 'WITH', 'e.acreditacionEspecialidad=c.id')
            //         ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'f', 'WITH', 'e.institucioneducativaSucursal = f.id')
            //         ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g', 'WITH', 'g.superiorInstitucioneducativaAcreditacion=e.id')
            //         ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo=g.id')
            //         ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'i', 'WITH', 'h.id=i.institucioneducativaCurso')
            //         ->innerJoin('SieAppWebBundle:Estudiante', 'j', 'WITH', 'i.estudiante=j.id')
            //         ->innerJoin('SieAppWebBundle:EstadomatriculaTipo', 'emt', 'WITH', 'i.estadomatriculaTipo = emt.id')
            //         ->innerJoin('SieAppWebBundle:GeneroTipo', 'gn', 'WITH', 'j.generoTipo = gn.id')
            //         ->innerJoin('SieAppWebBundle:NivelTipo', 'nt', 'WITH', 'h.nivelTipo = nt.id')
            //         ->innerJoin('SieAppWebBundle:CicloTipo', 'ct', 'WITH', 'h.cicloTipo = ct.id')
            //         ->innerJoin('SieAppWebBundle:GradoTipo', 'gt', 'WITH', 'h.gradoTipo = gt.id')
            //         ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'h.paraleloTipo = pt.id')
            //         ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'h.institucioneducativa = ie.id')

            //         ->where('h.institucioneducativa = :sie')
            //         ->andwhere('h.gestionTipo = :gestion')
            //         //->andwhere('f.periodoTipoId = :periodo')
            //         ->andwhere('d.codigo = :satCodigo')
            //         ->andWhere('j.generoTipo = :genero')
            //         ->andWhere('emt.id = :matricula')


            //         ->setParameter('sie', $formulario['institucioneducativa'])
            //         ->setParameter('gestion', $formulario['gestion'])
            //         //->setParameter('periodo', 3)
            //         ->setParameter('satCodigo', 3)
            //         ->setParameter('genero', 2)
            //         ->setParameter('matricula', 4)


            //         ->orderBy('j.paterno, j.materno, j.nombre')
            //         ->getQuery();

            $estudiantesF = $this->get('sie_app_web.funciones')->getEstudianteBachillerHumanisticoAlternativa($formulario['institucioneducativa'], $formulario['gestion'], 2);

            // $query = $repository->createQueryBuilder('a')
            //         ->select('j.id estId, j.codigoRude, j.carnetIdentidad, j.paterno, j.materno, j.nombre, i.id estinsId, ie.id instId, gn.id genId, gn.genero, pt.paralelo, nt.nivel, ct.ciclo, gt.grado')
            //         //->select('emt.estadomatricula, emt.id as estadomatriculaId, j.id, j.carnetIdentidad, j.codigoRude, j.paterno, j.materno, j.nombre, j.fechaNacimiento, i.id as eInsId, a.codigo as nivelId, b.codigo as cicloId, d.codigo as gradoId')
            //         ->innerJoin('SieAppWebBundle:SuperiorEspecialidadTipo', 'b', 'WITH', 'a.id = b.superiorFacultadAreaTipo')
            //         ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad', 'c', 'WITH', 'b.id = c.superiorEspecialidadTipo')
            //         ->innerJoin('SieAppWebBundle:SuperiorAcreditacionTipo', 'd', 'WITH', 'c.superiorAcreditacionTipo=d.id')
            //         ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion', 'e', 'WITH', 'e.acreditacionEspecialidad=c.id')
            //         ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'f', 'WITH', 'e.institucioneducativaSucursal = f.id')
            //         ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo', 'g', 'WITH', 'g.superiorInstitucioneducativaAcreditacion=e.id')
            //         ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'h', 'WITH', 'h.superiorInstitucioneducativaPeriodo=g.id')
            //         ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'i', 'WITH', 'h.id=i.institucioneducativaCurso')
            //         ->innerJoin('SieAppWebBundle:Estudiante', 'j', 'WITH', 'i.estudiante=j.id')
            //         ->innerJoin('SieAppWebBundle:EstadomatriculaTipo', 'emt', 'WITH', 'i.estadomatriculaTipo = emt.id')
            //         ->innerJoin('SieAppWebBundle:GeneroTipo', 'gn', 'WITH', 'j.generoTipo = gn.id')
            //         ->innerJoin('SieAppWebBundle:NivelTipo', 'nt', 'WITH', 'h.nivelTipo = nt.id')
            //         ->innerJoin('SieAppWebBundle:CicloTipo', 'ct', 'WITH', 'h.cicloTipo = ct.id')
            //         ->innerJoin('SieAppWebBundle:GradoTipo', 'gt', 'WITH', 'h.gradoTipo = gt.id')
            //         ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'h.paraleloTipo = pt.id')
            //         ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'h.institucioneducativa = ie.id')

            //         ->where('h.institucioneducativa = :sie')
            //         ->andwhere('h.gestionTipo = :gestion')
            //         //->andwhere('g.superiorPeriodoTipo = :periodo')
            //         ->andwhere('d.codigo = :satCodigo')
            //         ->andWhere('j.generoTipo = :genero')
            //         ->andWhere('emt.id = :matricula')


            //         ->setParameter('sie', $formulario['institucioneducativa'])
            //         ->setParameter('gestion', $formulario['gestion'])
            //         //->setParameter('periodo', 3)
            //         ->setParameter('satCodigo', 3)
            //         ->setParameter('genero', 1)
            //         ->setParameter('matricula', 4)


            //         ->orderBy('j.paterno, j.materno, j.nombre')
            //         ->getQuery();

            $estudiantesM = $this->get('sie_app_web.funciones')->getEstudianteBachillerHumanisticoAlternativa($formulario['institucioneducativa'], $formulario['gestion'], 1);

            return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:resultSearchIe.html.twig', array(
                        'estudiantesF' => $estudiantesF,
                        'estudiantesM' => $estudiantesM,
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

        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
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
                ->andWhere('sat.codigo = :codigo')
                ->andWhere('sia.gestionTipo = :gestion')
                ->setParameter('institucion', $institucion)
                ->setParameter('codigo', 3)
                ->setParameter('gestion', $this->gestionOperativo)
                ->getQuery();

        $quinto = $query->getResult();

        if ($quinto[0][1] == 0) {
            $this->get('session')->getFlashBag()->add('searchIe', 'La Institución Educativa ' . $institucion . ' no cuenta con aprendizajes especializados.');
            return $this->redirect($this->generateUrl('bach_exc_alt'));
        }

        $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');

        $query = $repository->createQueryBuilder('m')
                ->select('m.carnet, m.complemento, m.paterno, m.materno, m.nombre, ef.entidadfinanciera, m.cuentabancaria, m.fechaNacimiento, m.apellidoEsposo')
                ->innerJoin('SieAppWebBundle:EntidadfinancieraTipo', 'ef', 'WITH', 'ef.id = m.entidadfinancieraTipo')
                ->where('m.institucioneducativa = :institucion')
                ->andWhere('m.esoficial = :esoficial')
                ->andWhere('m.gestionTipo = :gestion')
                ->setParameter('institucion', $institucion)
                ->setParameter('gestion', $this->gestionOperativo)
                ->setParameter('esoficial', 't')
                ->getQuery();

        $director = $query->getOneOrNullResult();

        $repository = $em->getRepository('SieAppWebBundle:EstudianteDestacado');

        $query = $repository->createQueryBuilder('ed')
                ->select('ed.codigoRude, ed.carnetIdentidad, ed.paterno, ed.materno, ed.nombre, g.genero, ed.promedioFinal, ed.promedioSem1, ed.promedioSem2')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ed.estudiante = e.id')
                ->innerJoin('SieAppWebBundle:GeneroTipo', 'g', 'WITH', 'e.generoTipo = g.id')
                ->where('ed.institucioneducativa = :institucion')
                ->andWhere('ed.gestionTipo = :gestion')
                ->andWhere('ed.esoficial = :esoficial')
                ->setParameter('institucion', $institucion)
                ->setParameter('gestion', $this->gestionOperativo)
                ->setParameter('esoficial', 't')
                ->getQuery();

        $bachilleres = $query->getResult();

        return $this->render('SieAppWebBundle:BachillerExcelenciaAlternativa:resultDDJJ.html.twig', array(
                    'datadirector' => $director,
                    'bachilleres' => $bachilleres
        ));
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

        $arch = 'DECLARACION_JURADA_BACHILLER_' . $institucion . '_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_EstudianteExcelencia_unidadeducativa_regular_v2_afv.rptdesign&__format=pdf&&codue=' . $institucion . '&gestion='.$gestion_reporte.'&&__format=pdf&'));
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
