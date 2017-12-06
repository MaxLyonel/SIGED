<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\acreditacionEspecialidad;
use Sie\EsquemaBundle\Entity\institucioneducativaAcreditacion;
use Sie\EsquemaBundle\Entity\institucioneducativaPeriodo;
use Sie\EsquemaBundle\Entity\moduloTipo;
use Sie\EsquemaBundle\Entity\moduloPeriodo;
use Doctrine\ORM\EntityRepository;

/**
 * Malla Curricular controller.
 *
 */
class MallaCurricularController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request) {
        // Verificacmos si existe la session de usuario
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $ieducativa = $form['sie'];
        $gestion = $form['gestion'];
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ieducativa);
        //get the factulta area tipo 
        //$objFacultadAreaTipo = $this->getFacultades(16);

        return $this->render($this->session->get('pathSystem') . ':MallaCurricular:index.html.twig', array(
                    //'areas' => $areas,
                    'form' => $this->createMallaForm($form)->createView(),
                    'gestion' => $gestion,
                    'institucion' => $institucion,
        ));
    }

    private function createMallaForm($dataSend) {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_mallacurricular_create'))
                /* ->add('facultadArea', 'entity', array('label' => 'Area Tipo', 'attr' => array('class' => 'form-control'),
                  'mapped' => false, 'class' => 'SieEsquemaBundle:facultadAreaTipo',
                  )) */
                ->add('facultadArea', 'entity', array('label' => 'Área Técnica Productiva', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieEsquemaBundle:facultadAreaTipo',
                    'query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('fat')
                        ->where('fat.id IN (:keyarea)')
                        ->setParameter('keyarea', array(16, 17, 18, 19, 20, 21, 22, 23))
                        ->orderBy('fat.id', 'ASC')
                ;
            }, 'property' => 'facultadArea',
                        //'data' => $em->getReference("SieAppWebBundle:LugarTipo", '90')
                ))
                ->add('especialidad', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('acreditation', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('turno', 'entity', array('label' => 'Turno', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieEsquemaBundle:turnoSuperiorTipo'
                ))
                //->add('descmodulo', 'text', array('label' => 'Desc. Modulo', 'attr' => array('class' => 'form-control')))
                //->add('areasaberconocimiento', 'choice', array('label' => 'Área Saber y Conocimiento', 'attr' => array('class' => 'form-control')))
                //->add('cargahoraria', 'choice', array('label' => 'Carga Horaria', 'attr' => array('class' => 'form-control')))
                ->add('dataSend', 'text', array('data' => serialize($dataSend)))
                ->add('next', 'submit', array('label' => 'Continuar', 'attr' => array('class' => 'btn btn-success'/* , 'onclick' => 'buildMalla();' */)))
                ->getForm()

        ;
        return $form;
    }

    /**
     * create the malla trowght bimestre, turno, duracion and materia
     * @param Request $request
     * @return form with data to craete malla 
     */
    public function buildMallaAction(Request $request) {

        //create the connexion to the DB
        $em = $this->getDoctrine()->getManager();
        //get the data send
        $form = $request->get('form');

        $dataForm = unserialize($form['dataSend']);
        $adataSelected = array(
            'area' => $em->getRepository('SieEsquemaBundle:facultadAreaTipo')->find($form['facultadArea']),
            'especialidad' => $em->getRepository('SieEsquemaBundle:especialidadSuperiorTipo')->find($form['especialidad'])
        );

        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($dataForm['sie']);
        return $this->render($this->session->get('pathSystem') . ':MallaCurricular:build.html.twig', array(
                    'form' => $this->createBuildMallaForm(serialize($form))->createView(),
                    'gestion' => $dataForm['gestion'],
                    'institucion' => $institucion,
                    'dataSelected' => $adataSelected
        ));
        die;
    }

    /**
     * build the malla
     * @param type $data create form to buil the malla
     */
    private function createBuildMallaForm($data) {

        $form = $this->createFormBuilder()
                //->setAction($this->generateUrl('krlos'))
                ->add('bimestre', 'entity', array('label' => 'Bimestre', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieEsquemaBundle:periodoSuperiorTipo',
                    'query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('periodo')
                        ->where('periodo.id IN (:keyperiodo)')
                        ->setParameter('keyperiodo', array(1, 2, 3, 4, 5, 6, 7, 8))
                        ->orderBy('periodo.id', 'ASC')
                ;
            }, 'property' => 'periodoSuperior',
                        )
                )
                /* ->add('turno', 'entity', array('label' => 'Turno', 'attr' => array('class' => 'form-control'),
                  'mapped' => false, 'class' => 'SieEsquemaBundle:turnoSuperiorTipo'
                  )) */
                ->add('materia', 'text', array('label' => 'Materia (*)', 'attr' => array('class' => 'form-control')))
                //->add('duracion', 'text', array('label' => 'Duración', 'attr' => array('class' => 'form-control')))
                ->add('datasend', 'text', array('data' => $data))
                ->add('save', 'button', array('label' => 'Crear', 'attr' => array('class' => 'btn btn-success', 'onclick' => 'setMateria();')))
                ->getForm();
        return $form;
    }

    public function setMateriaAction(Request $request) {
        //todo the db conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //get the send values 
        $form = $request->get('form');
        //dump($form);
        $arrAreaInfo = unserialize($form['datasend']);
        //dump($arrAreaInfo);
        $arrSieInfo = unserialize($arrAreaInfo['dataSend']);
        //dump($arrSieInfo);
        //dump($objInstEducativaAcreditation);
        try {
            //find speciality 
            $objAcreditationEspecialidad = $em->getRepository('SieEsquemaBundle:acreditacionEspecialidad')->findOneBy(array(
                'acreditacionTipo' => $arrAreaInfo['acreditation'],
                'especialidadSuperiorTipo' => $arrAreaInfo['especialidad']
            ));
            $objInstEducativaAcreditation = $em->getRepository('SieEsquemaBundle:institucioneducativaAcreditacion')->findOneBy(array(
                'institucioneducativa' => $arrSieInfo['sie'],
                'acreditacionEspecialidad' => $objAcreditationEspecialidad->getId(),
                'gestionTipo' => $arrSieInfo['gestion'],
                'turnoSuperiorTipo' => $arrAreaInfo['turno']
            ));
            //if doesnot exist -> save it
            if (!$objInstEducativaAcreditation) {
                //set the correct ID 
                //$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_acreditacion');");
                //$query->execute();
                //set the institucioneducativa_id, acreditacion_especialidad_id on institucioneducativa_acreditation
                $objInstitutioneducativaAcreditation = new institucioneducativaAcreditacion();
                $objInstitutioneducativaAcreditation->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($arrSieInfo['sie']));
                $objInstitutioneducativaAcreditation->setAcreditacionEspecialidad($em->getRepository('SieEsquemaBundle:acreditacionEspecialidad')->findOneBy(array(
                            'acreditacionTipo' => $arrAreaInfo['acreditation'],
                            'especialidadSuperiorTipo' => $arrAreaInfo['especialidad']
                )));
                $objInstitutioneducativaAcreditation->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($arrSieInfo['gestion']));
                $objInstitutioneducativaAcreditation->setTurnoSuperiorTipo($em->getRepository('SieEsquemaBundle:turnoSuperiorTipo')->find($arrAreaInfo['turno']));
                $em->persist($objInstitutioneducativaAcreditation);
                $em->flush();
                dump('krlos');
            }
            //save speciality on insttitucioneducativa_acreditation
            $objInstEducativaPeriodo = new institucioneducativaPeriodo();
            $objInstEducativaPeriodo->setInstitucioneducativaAcreditacion($em->getRepository('SieEsquemaBundle:institucioneducativaAcreditacion')->find(($objInstEducativaAcreditation) ? $objInstEducativaAcreditation->getId() : $objInstitutioneducativaAcreditation->getId()));
            $objInstEducativaPeriodo->setPeriodoSuperiorTipo($em->getRepository('SieEsquemaBundle:periodoSuperiorTipo')->find($form['bimestre']));
            $em->persist($objInstEducativaPeriodo);
            $em->flush();
            //save the materia on modulo_tipo table
            $objModuloTipo = new moduloTipo();
            $objModuloTipo->setModulo($form['materia']);
            $objModuloTipo->setAreaSuperiorTipo($em->getRepository('SieEsquemaBundle:areaSuperiorTipo')->find(1));
            $em->persist($objModuloTipo);
            $em->flush();
            // save the materia and periodo on modulo_periodo
            $objModuloPeriodo = new moduloPeriodo();
            $objModuloPeriodo->setModuloTipo($em->getRepository('SieEsquemaBundle:moduloTipo')->find($objModuloTipo->getId()));
            $objModuloPeriodo->setInstitucioneducativaPeriodo($em->getRepository('SieEsquemaBundle:institucioneducativaPeriodo')->find($objInstEducativaPeriodo->getId()));
            $objModuloPeriodo->setObs('krloss');
            $objModuloPeriodo->setHorasModulo('40');
            $em->persist($objModuloPeriodo);
            $em->flush();
            //get the malla
            $objMalla = $this->getMallaPerUeAndGestion($form, $objAcreditationEspecialidad->getId());
            //build the malla to send the value
            $arrMallaPeriodo = array();
            foreach ($objMalla as $key => $mallaData) {
                $arrMallaPeriodo[$mallaData['periodoSuperior']][] = $mallaData['modulo'];
            }
            dump($arrMallaPeriodo);
            //do the commit in DB
            $em->getConnection()->commit();

            return $this->render($this->session->get('pathSystem') . ':MallaCurricular:setmateria.html.twig', array(
                        'arrMallaPeriodo' => $arrMallaPeriodo,
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
        die;
    }

    private function getMallaPerUeAndGestion($data, $acreditation) {
        $arrAreaInfo = unserialize($data['datasend']);
        $arrSieInfo = unserialize($arrAreaInfo['dataSend']);
        $em = $this->getDoctrine()->getManager();
        $objMalla = $em->getRepository('SieEsquemaBundle:institucioneducativaAcreditacion');
        $query = $objMalla->createQueryBuilder('iea')
                ->select('IDENTITY(iea.institucioneducativa) as sie, IDENTITY(iea.gestionTipo) as gestion,  (est.id) as espId, est.especialidadEspecialidad, (mt.id) as moduloId, mt.modulo, pst.id as periodoId , pst.periodoSuperior ')
                ->leftjoin('SieEsquemaBundle:acreditacionEspecialidad', 'ae', 'WITH', 'iea.acreditacionEspecialidad = ae.id')
                ->leftjoin('SieEsquemaBundle:especialidadSuperiorTipo', 'est', 'WITH', 'ae.especialidadSuperiorTipo= est.id')
                ->leftjoin('SieEsquemaBundle:institucioneducativaPeriodo', 'iep', 'WITH', 'iea.id = iep.institucioneducativaAcreditacion')
                ->leftjoin('SieEsquemaBundle:periodoSuperiorTipo', 'pst', 'WITH', 'iep.periodoSuperiorTipo = pst.id')
                ->leftjoin('SieEsquemaBundle:moduloPeriodo', 'mp', 'WITH', 'iep.id = mp.institucioneducativaPeriodo')
                ->leftjoin('SieEsquemaBundle:moduloTipo', 'mt', 'WITH', 'mp.moduloTipo=mt.id')
                ->where('iea.institucioneducativa = :sie')
                ->andwhere(' iea.acreditacionEspecialidad = :acreditation')
                ->andwhere('iea.gestionTipo = :gestion')
                ->andwhere('iea.turnoSuperiorTipo = :turno')
                ->setParameter('sie', $arrSieInfo['sie'])
                ->setParameter('acreditation', $acreditation)
                ->setParameter('gestion', $arrSieInfo['gestion'])
                ->setParameter('turno', $arrAreaInfo['turno'])
                //->distinct()
                ->orderBy('pst.id ', 'ASC')
                ->getQuery();
        $objMallaComplete = $query->getResult();

        return $objMallaComplete;
    }

    /**
     * get grado
     * @param type $idnivel
     * @param type $sie
     * return list of grados
     */
    public function getSpecialidadAction(Request $request, $idSpecialidad) {
        $em = $this->getDoctrine()->getManager();
        //get grado
        $aSpecilidads = array();
        $entity = $em->getRepository('SieEsquemaBundle:especialidadSuperiorTipo');
        $query = $entity->createQueryBuilder('esp')
                ->select('esp.id, esp.especialidadEspecialidad')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('esp.facultadAreaTipo = :area')
                ->setParameter('area', $idSpecialidad)
                ->orderBy('esp.especialidadEspecialidad', 'ASC')
                ->getQuery();
        $objSpecialidad = $query->getResult();
        foreach ($objSpecialidad as $specialidad) {
            $aSpecilidads[$specialidad['id']] = $specialidad['especialidadEspecialidad'];
        }
//        
        $response = new JsonResponse();
        return $response->setData(array('specialidades' => $aSpecilidads));
    }

    /**
     * get acreditation
     * @param type $idnivel
     * @param type $sie
     * return list of acreditation
     */
    public function getAcreditationAction(Request $request, $idAcreditation) {

        $em = $this->getDoctrine()->getManager();
        //get grado
        $aAcreditation = array();
        $entity = $em->getRepository('SieEsquemaBundle:acreditacionEspecialidad');
        $query = $entity->createQueryBuilder('acred')
                ->select('acredT.id, acredT.acreditacion')
                ->leftjoin('SieEsquemaBundle:acreditacionTipo', 'acredT', 'WITH', 'acred.acreditacionTipo = acredT.id')
                ->where('acred.especialidadSuperiorTipo = :specialId')
                ->andWhere('acredT.id in (:ids)')
                ->setParameter('specialId', $idAcreditation)
                ->setParameter('ids', array(12, 16, 32))
                ->orderBy('acredT.acreditacion', 'ASC')
                ->getQuery();
        $objAcreditation = $query->getResult();

        foreach ($objAcreditation as $acreditation) {
            $aAcreditation[$acreditation['id']] = $acreditation['acreditacion'];
        }

        $response = new JsonResponse();
        return $response->setData(array('acreditation' => $aAcreditation));
    }

    /**
     * get the facultades
     * @param type $id
     * @return \Sie\HerramientaBundle\Controller\Exception
     */
    private function getFacultades($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieEsquemaBundle:facultadAreaTipo');
        $query = $entity->createQueryBuilder('fat')
                ->select('fat.id, fat.facultadArea')
                ->where('fat.id >= :id')
                ->setParameter('id', $id)
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * Formulario de busqueda de institucion educativa
     * @param type $gestionactual
     * @return type
     */
    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('adicioneliminacionareas'))
                ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'off', 'maxlength' => 8)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    public function createMallaAction(Request $request) {
        //get the send data
        $formSend = $request->get('form');
        //create the new view to create malla
        return $this->render($this->session->get('pathSystem') . ':MallaCurricular:createMalla.html.twig', array(
                    'form' => $this->mallaForm($formSend)->createView(),
        ));
        // echo 'krlos krlos';
        //die;
    }

    /**
     * create the malla form
     * @return type
     */
    private function mallaForm($data) {
        $form = $this->createFormBuilder()
                ->add('turno', 'text')
                ->add('duracion', 'text')
                ->add('areasaberconocimiento', 'entity', array('label' => 'Área Saber Conocimiento', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieEsquemaBundle:areaSuperiorTipo',
                    'query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('areasup')
                        ->where('areasup.id = :id')
                        ->setParameter('id', 1)
                        ->orderBy('areasup.id', 'ASC')
                ;
            }, 'property' => 'areaSuperior',
                ))
                ->add('modulo', 'entity', array('label' => 'Modulo', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieEsquemaBundle:moduloTipo',
                    'query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('mod')
                        ->where('mod.areaSuperiorTipo = :id')
                        ->setParameter('id', 1)
                        ->orderBy('mod.id', 'ASC')
                ;
            }, 'property' => 'modulo',
                ))
                ->add('horas', 'text')
                ->add('data', 'text', array('data' => serialize($data)))
                ->getForm()
        ;
        return $form;
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////die

    public function buildAction(Request $request) {

        $form['sie'] = $request->get('sie');
        $form['gestion'] = $request->get('gestion');
        $form['bimestre'] = $request->get('bimestre');

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            //get the content of directory 
            $aDirectoryContent = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';

            //generate to file with thwe sql process
            $operativo = $form['bimestre'] + 1;
            $query = $em->getConnection()->prepare("select * from sp_genera_arch_regular_txt('" . $form['sie'] . "','" . $form['gestion'] . "','" . $operativo . "','" . $form['bimestre'] . "');");
            $query->execute();
            $em->getConnection()->commit();
            $em->flush();
            $em->clear();

            //todo the connexion to the server
            $connection = ssh2_connect('172.20.0.103', 22);
            ssh2_auth_password($connection, 'root', 'ASDFqwe12.103');
            $sftp = ssh2_sftp($connection);
            //get the path server
            $path = '../bajada_local/';
            //ssh2_exec($connection, 'iconv -f UTF-8  -t ISO-8859-1 ' . $path . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie  >> ' . $path . 'ee' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie');
            ssh2_exec($connection, 'base64  ' . $path . '' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie  >> ' . $path . 'e' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie');
            //ssh2_exec($connection, 'cp ' . $path . '' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie   ' . $path . 'e' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie');
            /////////////////////////////////
            $server = "172.20.0.103"; //address of ftp server (leave out ftp://)
            $ftp_user_name = "regulardb"; // Username
            $ftp_user_pass = "regular2015v4azx-"; // Password

            $mode = "FTP_BINARY";
            $conn = ftp_connect($server, 21);
            $login = ftp_login($conn, $ftp_user_name, $ftp_user_pass);

            if (!$conn || !$login) {
                die("Connection attempt failed!");
            }
            // try to download $server_file and save to $local_file
            $newGenerateFile = $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B';
            $local_file = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/' . 'e' . $newGenerateFile . '.sie';
            $server_file = 'e' . $newGenerateFile . '.sie';

            if (ftp_get($conn, $local_file, $server_file, FTP_BINARY)) {
                //echo "generado correctamente to $local_file\n";
            } else {
                echo "There was a problem in conexion server\n :(";
            }
            $dir = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';

            exec('zip -P 3I35I3Client ' . $dir . $newGenerateFile . '.zip ' . $dir . 'e' . $newGenerateFile . '.sie');
            exec('mv ' . $dir . $newGenerateFile . '.zip ' . $dir . $newGenerateFile . '.igm ');
            ssh2_sftp_unlink($sftp, '/bajada_local/' . $server_file);
            //system('rm -fr ' . $dir . $newGenerateFile . '.igm ');
            system('rm -fr ' . $dir . $server_file);
            ftp_close($conn);

            //echo "done";
            $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);

            return $this->render('SieHerramientaBundle:AdicionEliminacionAreas:fileDownload.html.twig', array(
                        'uEducativa' => $objUe[0],
                        'file' => $newGenerateFile . '.igm',
                            //'form' => $this->createFormToBuild($form['sie'], $form['gestion'], $form['bimestre'])->createView()
            ));
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            $em->getConnection()->rollback();
            $em->close();
            throw $e;
        }
    }

}
