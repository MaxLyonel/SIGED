<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Form\SelectIeType;
use Sie\AppWebBundle\Form\EstudianteDestacadoType;
use Sie\AppWebBundle\Form\MaestroCuentabancariaType;
use Sie\AppWebBundle\Entity\EstudianteDestacado;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\MaestroCuentabancaria;
use Sie\AppWebBundle\Entity\EntidadfinancieraTipo;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Bachiller de Excelencia Controller.
 *
 */
class BachillerExcelenciaController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
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

        $fechaActual = new \DateTime('now');
        $fechaCorte = new \DateTime('2018-12-11');

        if($this->session->get('userId') != 13856176) {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $form = $this->createSearchIeForm();

        return $this->render('SieAppWebBundle:BachillerExcelencia:index.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /*
     * Formulario de búsqueda de Institucion Educativa
     */

    private function createSearchIeForm() {
        $form = $this->createForm(new SelectIeType(), null, array(
            'action' => $this->generateUrl('bach_exc_ie_search'),
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

        $fechaActual = new \DateTime('now');
        $fechaCorte = new \DateTime('2018-12-11');

        if($this->session->get('userId') != 13856176) {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $form = $this->createSearchIeDirForm();

        return $this->render('SieAppWebBundle:BachillerExcelencia:index_dir.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /*
     * Formulario de búsqueda de Institucion Educativa
     */

    private function createSearchIeDirForm() {
        $form = $this->createForm(new SelectIeType(), null, array(
            'action' => $this->generateUrl('bach_exc_ie_dir_search'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Buscar'));

        return $form;
    }

    public function resultSearchIeDirAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $fechaActual = new \DateTime('now');
        $fechaCorte = new \DateTime('2018-12-11');

        if($this->session->get('userId') != 13856176) {
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
            return $this->redirect($this->generateUrl('bach_exc_dir'));
            }

            /*
             * Verificar si la UE cuenta con sexto de secundaria
             * SELECT
              "public".institucioneducativa."id",
              "public".institucioneducativa_curso.nivel_tipo_id,
              "public".institucioneducativa_curso.grado_tipo_id,
              "public".institucioneducativa_curso.paralelo_tipo_id
              FROM
              "public".institucioneducativa
              INNER JOIN "public".institucioneducativa_curso ON "public".institucioneducativa_curso.institucioneducativa_id = "public".institucioneducativa."id"
              WHERE
              "public".institucioneducativa."id" = 80730808 and "public".institucioneducativa_curso.nivel_tipo_id = 13 and "public".institucioneducativa_curso.gestion_tipo_id = 2018 and "public".institucioneducativa_curso.grado_tipo_id = 6
             */

            $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

            $query = $repository->createQueryBuilder('i')
                    ->select('count(i.id)')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'ic', 'WITH', 'ic.institucioneducativa = i.id')
                    ->where('i.id = :institucion')
                    ->andWhere('ic.nivelTipo = :nivel')
                    ->andWhere('ic.gradoTipo = :grado')
                    ->andWhere('ic.gestionTipo = :gestion')
                    ->setParameter('institucion', $formulario['institucioneducativa'])
                    ->setParameter('nivel', 13)
                    ->setParameter('grado', 6)
                    ->setParameter('gestion', $formulario['gestion'])
                    ->getQuery();

            $sexto = $query->getResult();

            if ($sexto[0][1] == 0) {
                $this->get('session')->getFlashBag()->add('searchIe', 'La Institución Educativa ' . $formulario['institucioneducativa'] . ' no cuenta con Sexto de Secundaria.');
                return $this->redirect($this->generateUrl('bach_exc_dir'));
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
                        ->select('m.carnet, m.complemento, m.paterno, m.materno, m.nombre, ef.entidadfinanciera, m.cuentabancaria')
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

                return $this->render('SieAppWebBundle:BachillerExcelencia:resultDirector.html.twig', array(
                            'datadirector' => $director,
                ));
            }

            /*
             * Información de la Institución Educativa y Datos de la Directora o el Director
             */
            $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

            $query = $repository->createQueryBuilder('i')
                    ->select('i.id instId, i.institucioneducativa, ct.cargo, p.id dirId, p.carnet, p.paterno, p.materno, p.nombre, p.complemento, mi.id dirinsId')
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
                    //->setParameter('gestion1', 2014)
                    //->setParameter('gestion2', 2013)
                    ->orderBy('mi.gestionTipo', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery();

            $entity = $query->getOneOrNullResult();

            if (!$entity) {
                $this->get('session')->getFlashBag()->add('searchIe', 'El código ingresado no es válido o la Institución Educativa no existe o aún no se consolidó la información.');
                return $this->redirect($this->generateUrl('bach_exc_dir'));
            }

            $form_dir = $this->createMaestroCuentabancariaForm();

            return $this->render('SieAppWebBundle:BachillerExcelencia:resultSearchIeDir.html.twig', array(
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
            //'action' => $this->generateUrl('bach_exc_create'),
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

        $fechaActual = new \DateTime('now');
        $fechaCorte = new \DateTime('2018-12-11');

        if($this->session->get('userId') != 13856176) {
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
            $gestion = 2018;

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

        $fechaActual = new \DateTime('now');
        $fechaCorte = new \DateTime('2018-12-11');

        if($this->session->get('userId') != 13856176) {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $form = $this->createSearchIeRstForm();

        return $this->render('SieAppWebBundle:BachillerExcelencia:index_rst.html.twig', array(
                    'form' => $form->createView(),
        ));
    }


    private function createSearchIeRstForm() {
        $form = $this->createForm(new SelectIeType(), null, array(
            'action' => $this->generateUrl('bach_exc_ie_rst_search'),
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
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $fechaActual = new \DateTime('now');
        $fechaCorte = new \DateTime('2018-12-11');

        if($this->session->get('userId') != 13856176) {
            return $this->redirect($this->generateUrl('principal_web'));
        }

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
            return $this->redirect($this->generateUrl('bach_exc'));
            }

            /*
             * Verificar si la UE cuenta con sexto de secundaria
             * SELECT
              "public".institucioneducativa."id",
              "public".institucioneducativa_curso.nivel_tipo_id,
              "public".institucioneducativa_curso.grado_tipo_id,
              "public".institucioneducativa_curso.paralelo_tipo_id
              FROM
              "public".institucioneducativa
              INNER JOIN "public".institucioneducativa_curso ON "public".institucioneducativa_curso.institucioneducativa_id = "public".institucioneducativa."id"
              WHERE
              "public".institucioneducativa."id" = 80730808 and "public".institucioneducativa_curso.nivel_tipo_id = 13 and "public".institucioneducativa_curso.gestion_tipo_id = 2018 and "public".institucioneducativa_curso.grado_tipo_id = 6
             */

            $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

            $query = $repository->createQueryBuilder('i')
                    ->select('count(i.id)')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'ic', 'WITH', 'ic.institucioneducativa = i.id')
                    ->where('i.id = :institucion')
                    ->andWhere('ic.nivelTipo = :nivel')
                    ->andWhere('ic.gradoTipo = :grado')
                    ->andWhere('ic.gestionTipo = :gestion')
                    ->setParameter('institucion', $formulario['institucioneducativa'])
                    ->setParameter('nivel', 13)
                    ->setParameter('grado', 6)
                    ->setParameter('gestion', $formulario['gestion'])
                    ->getQuery();

            $sexto = $query->getResult();

            if ($sexto[0][1] == 0) {
                $this->get('session')->getFlashBag()->add('searchIe', 'La Institución Educativa ' . $formulario['institucioneducativa'] . ' no cuenta con Sexto de Secundaria');
                return $this->redirect($this->generateUrl('bach_exc'));
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
                        ->select('e.codigoRude, e.carnetIdentidad, e.paterno, e.materno, e.nombre, g.genero, ed.promedioFinal')
                        ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ed.estudianteId = e.id')
                        ->innerJoin('SieAppWebBundle:GeneroTipo', 'g', 'WITH', 'e.generoTipo = g.id')
                        ->where('ed.institucioneducativa = :institucion')
                        ->andWhere('ed.gestionTipo = :gestion')
                        ->andWhere('ed.esoficial = :esoficial')
                        ->setParameter('institucion', $formulario['institucioneducativa'])
                        ->setParameter('gestion', $formulario['gestion'])
                        ->setParameter('esoficial', 't')
                        ->getQuery();

                $bachilleres = $query->getResult();

                return $this->render('SieAppWebBundle:BachillerExcelencia:resultBachilleres.html.twig', array(
                            'bachilleres' => $bachilleres,
                ));
            }

            /*
             * Lista de estudiantes de 6to de secundaria
             */
            $repository = $em->getRepository('SieAppWebBundle:Estudiante');

            $query = $repository->createQueryBuilder('e')
                    ->select('e.id estId, e.codigoRude, e.carnetIdentidad, e.paterno, e.materno, e.nombre, ei.id estinsId, i.id instId, gn.id genId, gn.genero, pt.paralelo, nt.nivel, ct.ciclo, gt.grado')
                    ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.estudiante = e.id')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'ic', 'WITH', 'ei.institucioneducativaCurso = ic.id')
                    ->innerJoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'ic.institucioneducativa = i.id')
                    ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'ic.paraleloTipo = pt.id')
                    ->innerJoin('SieAppWebBundle:NivelTipo', 'nt', 'WITH', 'ic.nivelTipo = nt.id')
                    ->innerJoin('SieAppWebBundle:CicloTipo', 'ct', 'WITH', 'ic.cicloTipo = ct.id')
                    ->innerJoin('SieAppWebBundle:GradoTipo', 'gt', 'WITH', 'ic.gradoTipo = gt.id')
                    ->innerJoin('SieAppWebBundle:GeneroTipo', 'gn', 'WITH', 'e.generoTipo = gn.id')
                    ->where('i.id = :institucion')
                    ->andWhere('ic.gestionTipo = :gestion')
                    ->andWhere('ic.nivelTipo = :nivel')
                    ->andWhere('ic.gradoTipo = :grado')
                    ->andWhere('ic.cicloTipo = :ciclo')
                    ->andWhere('gn.id = :genero')
                    ->andWhere('ei.estadomatriculaTipo in (:matricula)')
                    ->setParameter('institucion', $formulario['institucioneducativa'])
                    ->setParameter('gestion', $formulario['gestion'])
                    ->setParameter('nivel', 13)
                    ->setParameter('grado', 6)
                    ->setParameter('ciclo', 3)
                    ->setParameter('genero', 2)
                    ->setParameter('matricula', array(4,5,55))
                    ->addOrderBy('e.paterno, e.materno, e.nombre', 'asc')
                    ->getQuery();

            $estudiantesF = $query->getResult();

            $query = $repository->createQueryBuilder('e')
                    ->select('e.id estId, e.codigoRude, e.carnetIdentidad, e.paterno, e.materno, e.nombre, ei.id estinsId, i.id instId, gn.id genId, gn.genero, pt.paralelo, nt.nivel, ct.ciclo, gt.grado')
                    ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.estudiante = e.id')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'ic', 'WITH', 'ei.institucioneducativaCurso = ic.id')
                    ->innerJoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'ic.institucioneducativa = i.id')
                    ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'ic.paraleloTipo = pt.id')
                    ->innerJoin('SieAppWebBundle:NivelTipo', 'nt', 'WITH', 'ic.nivelTipo = nt.id')
                    ->innerJoin('SieAppWebBundle:CicloTipo', 'ct', 'WITH', 'ic.cicloTipo = ct.id')
                    ->innerJoin('SieAppWebBundle:GradoTipo', 'gt', 'WITH', 'ic.gradoTipo = gt.id')
                    ->innerJoin('SieAppWebBundle:GeneroTipo', 'gn', 'WITH', 'e.generoTipo = gn.id')
                    ->where('i.id = :institucion')
                    ->andWhere('ic.gestionTipo = :gestion')
                    ->andWhere('ic.nivelTipo = :nivel')
                    ->andWhere('ic.gradoTipo = :grado')
                    ->andWhere('ic.cicloTipo = :ciclo')
                    ->andWhere('gn.id = :genero')
                    ->andWhere('ei.estadomatriculaTipo in (:matricula)')
                    ->setParameter('institucion', $formulario['institucioneducativa'])
                    ->setParameter('gestion', $formulario['gestion'])
                    ->setParameter('nivel', 13)
                    ->setParameter('grado', 6)
                    ->setParameter('ciclo', 3)
                    ->setParameter('genero', 1)
                    ->setParameter('matricula', array(4,5,55))
                    ->addOrderBy('e.paterno, e.materno, e.nombre', 'asc')
                    ->getQuery();

            $estudiantesM = $query->getResult();

            return $this->render('SieAppWebBundle:BachillerExcelencia:resultSearchIe.html.twig', array(
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

        $fechaActual = new \DateTime('now');
        $fechaCorte = new \DateTime('2018-12-11');

        if($this->session->get('userId') != 13856176) {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $form = $this->createSearchIeRstForm();
        $form->handleRequest($request);

        if ($form->isValid()) {

            $formulario = $form->getData();

            $em = $this->getDoctrine()->getManager();

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
            return $this->redirect($this->generateUrl('bach_exc_rst'));
            }

            $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');

            $query = $repository->createQueryBuilder('m')
                    ->select('m.id, m.carnet, m.complemento, m.paterno, m.materno, m.nombre, ef.entidadfinanciera, m.cuentabancaria, m.esoficial estado')
                    ->innerJoin('SieAppWebBundle:EntidadfinancieraTipo', 'ef', 'WITH', 'ef.id = m.entidadfinancieraTipo')
                    ->where('m.institucioneducativa = :institucion')
                    ->andWhere('m.gestionTipo = :gestion')
                    ->andWhere('m.esoficial = :esoficial')
                    ->setParameter('institucion', $formulario['institucioneducativa'])
                    ->setParameter('gestion', $formulario['gestion'])
                    ->setParameter('esoficial', 't')
                    ->getQuery();

            $director = $query->getOneOrNullResult();

            $repository = $em->getRepository('SieAppWebBundle:EstudianteDestacado');

            $query = $repository->createQueryBuilder('ed')
                    ->select('ed.codigoRude, ed.carnetIdentidad, ed.paterno, ed.materno, ed.nombre, g.genero, ed.promedioFinal, ed.id edId, g.id gen')
                    ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ed.estudianteId = e.id')
                    ->innerJoin('SieAppWebBundle:GeneroTipo', 'g', 'WITH', 'e.generoTipo = g.id')
                    ->where('ed.institucioneducativa = :institucion')
                    ->andWhere('ed.gestionTipo = :gestion')
                    ->andWhere('ed.esoficial = :esoficial')
                    ->setParameter('institucion', $formulario['institucioneducativa'])
                    ->setParameter('gestion', $formulario['gestion'])
                    ->setParameter('esoficial', 't')
                    ->getQuery();

            $bachilleres = $query->getResult();

            return $this->render('SieAppWebBundle:BachillerExcelencia:resultRst.html.twig', array(
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

        $fechaActual = new \DateTime('now');
        $fechaCorte = new \DateTime('2018-12-11');

        if($this->session->get('userId') != 13856176) {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');

        $query = $repository->createQueryBuilder('m')
                ->where('m.institucioneducativa = :institucion')
                ->andWhere('m.gestionTipo = :gestion')
                ->setParameter('institucion', $ie)
                ->setParameter('gestion', 2018)
                ->getQuery();

        $directores = $query->getResult();

        foreach ($directores as $value) {
            $value->setEsactivo('f');
            $value->setEsoficial('f');
            $em->persist($value);
            $em->flush();
        }

        $this->get('session')->getFlashBag()->add('searchIe', 'Los datos han sido restablecidos correctamente. (Directora/Director)');
        return $this->redirect($this->generateUrl('bach_exc_rst'));
    }

    public function beRstAction($ie, $ed, $g) {

        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $fechaActual = new \DateTime('now');
        $fechaCorte = new \DateTime('2018-12-11');

        if($this->session->get('userId') != 13856176) {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:EstudianteDestacado');

        $query = $repository->createQueryBuilder('ed')
                ->where('ed.institucioneducativa = :institucion')
                ->andWhere('ed.gestionTipo = :gestion')
                ->andWhere('ed.id = :destacado')
                ->andWhere('ed.generoTipo = :genero')
                ->setParameter('institucion', $ie)
                ->setParameter('gestion', 2018)
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
                ->andWhere('ed.gestionTipo = :gestion')
                ->andWhere('ed.generoTipo = :genero')
                ->setParameter('institucion', $ie)
                ->setParameter('gestion', 2018)
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
        return $this->redirect($this->generateUrl('bach_exc_rst'));
    }

    /*
     * Formulario de Estudiante Destacado para Registro Bachiller de Excelencia
     */

    private function createEstudianteDestacadoForm() {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $fechaActual = new \DateTime('now');
        $fechaCorte = new \DateTime('2018-12-11');

        if($this->session->get('userId') != 13856176) {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $form = $this->createForm(new EstudianteDestacadoType(), null, array(
            //'action' => $this->generateUrl('bach_exc_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }

    public function infoStudentAction(Request $request, $estId, $estinsId, $instId, $genId) {
        $em = $this->getDoctrine()->getManager();

        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($estId);

        $form_ed = $this->createEstudianteDestacadoForm();

        return $this->render('SieAppWebBundle:BachillerExcelencia:infoStudent.html.twig', array(
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
    public function beCreateAction(Request $request) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $fechaActual = new \DateTime('now');
        $fechaCorte = new \DateTime('2018-12-11');

        if($this->session->get('userId') != 13856176) {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $response = new JsonResponse();
        try {
            $form_aux = $request->get('sie_appwebbundle_estudiantedestacado');
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
                    ->setParameter('gestion', 2018)
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
            $gestion = $em->getRepository('SieAppWebBundle:GestionTipo')->find(2018);

            $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(55));

            $estudiantedestacado = new EstudianteDestacado();
            $estudiantedestacado->setFechaRegistro(new \DateTime('now'));
            $estudiantedestacado->setEstudiante($estudiante);
            $estudiantedestacado->setEstudianteId($estudianteId);
            $estudiantedestacado->setEstudianteInscripcion($inscripcion);
            $estudiantedestacado->setGeneroTipo($genero);
            $estudiantedestacado->setGestionTipo($gestion);
            $estudiantedestacado->setInstitucioneducativa($ieducativa);
            $estudiantedestacado->setPromedioFinal($promedioFinal);
            $estudiantedestacado->setIpOrigen($_SERVER['HTTP_USER_AGENT']. ' - '. $_SERVER['REMOTE_ADDR']);
            $estudiantedestacado->setEsoficial('t');
            $estudiantedestacado->setNombre($estudiante->getNombre());
            $estudiantedestacado->setPaterno($estudiante->getPaterno());
            $estudiantedestacado->setMaterno($estudiante->getMaterno());
            $estudiantedestacado->setCarnetIdentidad($estudiante->getCarnetIdentidad());
            $estudiantedestacado->setCodigoRude($estudiante->getCodigoRude());
            $estudiantedestacado->setOrgCurricularTipoId(1);

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

        $arrSieInfo = array('id'=>$this->session->get('ie_id'), 'datainfo'=>$this->session->get('ie_nombre'));

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

        $query = $repository->createQueryBuilder('i')
                ->select('count(i.id)')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'ic', 'WITH', 'ic.institucioneducativa = i.id')
                ->where('i.id = :institucion')
                ->andWhere('ic.nivelTipo = :nivel')
                ->andWhere('ic.gradoTipo = :grado')
                ->andWhere('ic.gestionTipo = :gestion')
                ->setParameter('institucion', $arrSieInfo['id'])
                ->setParameter('nivel', 13)
                ->setParameter('grado', 6)
                ->setParameter('gestion', 2018)
                ->getQuery();

        $sexto = $query->getResult();

        if ($sexto[0][1] == 0) {
            $this->get('session')->getFlashBag()->add('searchIe', 'La Institución Educativa ' . $arrSieInfo['id'] . ' no cuenta con Sexto de Secundaria.');
            return $this->redirect($this->generateUrl('bach_exc'));
        }

        $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');

        $query = $repository->createQueryBuilder('m')
                ->select('m.carnet, m.complemento, m.paterno, m.materno, m.nombre, ef.entidadfinanciera, m.cuentabancaria')
                ->innerJoin('SieAppWebBundle:EntidadfinancieraTipo', 'ef', 'WITH', 'ef.id = m.entidadfinancieraTipo')
                ->where('m.institucioneducativa = :institucion')
                ->andWhere('m.gestionTipo = :gestion')
                ->andWhere('m.esoficial = :esoficial')
                ->setParameter('institucion', $arrSieInfo['id'])
                ->setParameter('gestion', 2018)
                ->setParameter('esoficial', 't')
                ->getQuery();

        $director = $query->getOneOrNullResult();

        $repository = $em->getRepository('SieAppWebBundle:EstudianteDestacado');

        $query = $repository->createQueryBuilder('ed')
                ->select('e.codigoRude, e.carnetIdentidad, e.paterno, e.materno, e.nombre, g.genero, ed.promedioFinal')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ed.estudianteId = e.id')
                ->innerJoin('SieAppWebBundle:GeneroTipo', 'g', 'WITH', 'e.generoTipo = g.id')
                ->where('ed.institucioneducativa = :institucion')
                ->andWhere('ed.gestionTipo = :gestion')
                ->andWhere('ed.esoficial = :esoficial')
                ->setParameter('institucion', $arrSieInfo['id'])
                ->setParameter('gestion', 2018)
                ->setParameter('esoficial', 't')
                ->getQuery();

        $bachilleres = $query->getResult();

        return $this->render('SieAppWebBundle:BachillerExcelencia:resultDDJJ.html.twig', array(
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

        $arrSieInfo = array('id'=>$this->session->get('ie_id'), 'datainfo'=>$this->session->get('ie_nombre'));

        $em = $this->getDoctrine()->getManager();

        $bachilleres = $em->getRepository('SieAppWebBundle:EstudianteDestacado')->findByInstitucioneducativa($arrSieInfo['id']);

        foreach ($bachilleres as $estudiante) {
            $estudiante->setImpreso('t');
            $em->persist($estudiante);
        }

        $em->flush();

        $arch = 'DECLARACION_JURADA_BACHILLER_' . $arrSieInfo['id'] . '_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_EstudianteExcelencia_unidadeducativa_regular_v1_afv.rptdesign&__format=pdf&&codue=' . $arrSieInfo['id'] . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function declaracionJuradaDirAction(Request $request) {
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        $arrSieInfo = array('id'=>$this->session->get('ie_id'), 'datainfo'=>$this->session->get('ie_nombre'));

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

        $query = $repository->createQueryBuilder('i')
                ->select('count(i.id)')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'ic', 'WITH', 'ic.institucioneducativa = i.id')
                ->where('i.id = :institucion')
                ->andWhere('ic.nivelTipo = :nivel')
                ->andWhere('ic.gradoTipo = :grado')
                ->andWhere('ic.gestionTipo = :gestion')
                ->setParameter('institucion', $arrSieInfo['id'])
                ->setParameter('nivel', 13)
                ->setParameter('grado', 6)
                ->setParameter('gestion', 2018)
                ->getQuery();

        $sexto = $query->getResult();

        if ($sexto[0][1] == 0) {
            $this->get('session')->getFlashBag()->add('searchIe', 'La Institución Educativa ' . $username . ' no cuenta con Sexto de Secundaria.');
            return $this->redirect($this->generateUrl('bach_exc'));
        }

        $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');

        $query = $repository->createQueryBuilder('m')
                ->select('m.carnet, m.complemento, m.paterno, m.materno, m.nombre, ef.entidadfinanciera, m.cuentabancaria')
                ->innerJoin('SieAppWebBundle:EntidadfinancieraTipo', 'ef', 'WITH', 'ef.id = m.entidadfinancieraTipo')
                ->where('m.institucioneducativa = :institucion')
                ->andWhere('m.esoficial = :esoficial')
                ->andWhere('m.gestionTipo = :gestion')
                ->setParameter('institucion', $arrSieInfo['id'])
                ->setParameter('esoficial', 't')
                ->setParameter('gestion', 2018)
                ->getQuery();

        $director = $query->getOneOrNullResult();

        return $this->render('SieAppWebBundle:BachillerExcelencia:resultDDJJDir.html.twig', array(
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

        $arrSieInfo = array('id'=>$this->session->get('ie_id'), 'datainfo'=>$this->session->get('ie_nombre'));

        $repository = $em->getRepository('SieAppWebBundle:MaestroCuentabancaria');

        $query = $repository->createQueryBuilder('m')
                ->where('m.institucioneducativa = :institucion')
                ->andWhere('m.gestionTipo = :gestion')
                ->setParameter('institucion', $arrSieInfo['id'])
                ->setParameter('gestion', 2018)
                ->getQuery();

        $directores = $query->getResult();

        foreach ($directores as $value) {
            $value->setEsactivo('t');
            $em->persist($value);
            $em->flush();
        }

        $arch = 'DECLARACION_JURADA_DIRECTOR_' . $arrSieInfo['id'] . '_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_DirectorEstudianteExcelencia_unidadeducativa_regular_v1_afv.rptdesign&__format=pdf&&codue=' . $arrSieInfo['id'] . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /*
     * Muestra el formulario de búsqueda de Institución Educativa
     */

    public function indexReporteDistritoAction() {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $this->createSearchDistritoForm();

        return $this->render('SieAppWebBundle:BachillerExcelencia:index_reporte_dist.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    private function createSearchDistritoForm() {
        $form = $this->createForm(new SelectIeType(), null, array(
            'action' => $this->generateUrl('bach_exc_rep_dist_search'),
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

        if ($form->isValid()) {

            $formulario = $form->getData();

            $arch = 'DECLARACION_JURADA_DISTRITO_' . $formulario['institucioneducativa'] . '_' . date('YmdHis') . '.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_EstudianteExcelencia_DIstritoRegular_v2_afv_hcq.rptdesign&distrito=' . $formulario['institucioneducativa'] . '&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        }
    }

}
