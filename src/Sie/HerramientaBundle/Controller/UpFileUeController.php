<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\UploadFileControl;

class UpFileUeController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * cargamos actin index para la subida de archivos
     *
     * @todo: creamos el index de subida de archivo
     *
     * @return objeto form para la subida de archivo
     */
    public function indexAction(Request $request) {
        //$sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $aAccess = array(5, 2, 9);
        if (in_array($this->session->get('roluser'), $aAccess)) {
            $institutionData = $this->intitucioneducativaData($this->session->get('personaId'), $this->session->get('currentyear'));

            $institutionData1 = $this->getDataUe($this->session->get('ie_id'));

            //verificar si es IE
            if ($institutionData1) {
                $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($institutionData1[0]['id']);

                //$objUeUploadFiles = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getFilesUploadByDistrito($objUe[0]['distritoTipoId']);
                $objUeUploadFiles = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getFilesUploadByUnidadEducativa($objUe[0]['ueId']);

                return $this->render('SieHerramientaBundle:UpFile:index.html.twig', array(
                            'institutionData' => $institutionData1,
                            'form' => $this->formUp($institutionData1[0]['id'])->createView(),
                            'sie' => $institutionData1[0]['id'],
                            'ueuploadfiles' => $objUeUploadFiles
                ));
            } else {
                return $this->render('SieHerramientaBundle:UpFile:find.html.twig');
            }
        } else {
            return $this->render('SieHerramientaBundle:UpFile:find.html.twig');
        }
    }

    private function getDataUe($id) {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $entity->createQueryBuilder('i')
                ->select('i.id', 'i.institucioneducativa', 'dt.dependencia', 'ct.convenio', 'it.descripcion')
                //->select('mi.id as maInsId', 'i.id as instId, i.institucioneducativa', 'p.id as personId, p.paterno, p.materno, p.nombre,p.carnet')
                ->leftJoin('SieAppWebBundle:DependenciaTipo', 'dt', 'WITH', 'i.dependenciaTipo=dt.id')
                ->leftJoin('SieAppWebBundle:ConvenioTipo', 'ct', 'WITH', 'i.convenioTipo=ct.id')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo=it.id')
                ->where('i.id = :id')
                ->setParameter('id', $id)
                ->getQuery();
        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    /**
     * creamos el formulario de subida de archivo
     *
     * @todo: formulario creado con el parametro cod. sie
     *
     * @return retorna objecto formulario
     */
    private function formUp($ieId) {

        $user = new Usuario();
        $formup = $this->createFormBuilder($user)
                //->setAction($this->generateUrl('consolidation_sie_upfile_web'))
                ->add('siefile', 'file', array('mapped' => false, 'data_class' => null))
                ->add('ieId', 'hidden', array('mapped' => false, 'data' => $ieId))
                ->add('up', 'submit', array('label' => 'Continuar'))
                ->getForm();
        return $formup;
    }

    public function findAction(Request $request) {

        if ($request->isMethod('POST')) {

            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');
            //look for UE
            $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['codigoSie']);

            if (!$objUe) {
                $message = "Unidad Eductiva no existe";
                $this->addFlash('warningcons', $message);
                return $this->redirectToRoute('sie_herramienta_upfile_index');
            }

            // $objUeUploadFiles = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getFilesUploadByDistrito($objUe[0]['distritoTipoId']);
            $objUeUploadFiles = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getFilesUploadByUnidadEducativa($objUe[0]['ueId']);
            return $this->render('SieHerramientaBundle:UpFile:index.html.twig', array(
                        'institutionData' => array(),
                        'instEdu' => $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['codigoSie']),
                        'form' => $this->formUp($form['codigoSie'])->createView(),
                        'sie' => $form['codigoSie'],
                        'ueuploadfiles' => $objUeUploadFiles
            ));
        }
    }

    /**
     * get the data about the intitucion educativa
     *
     * @todo: obtener informacion del usuario e inst. educativa con los parametros $id, $y de entrada
     *
     * @return: retorna array con datos de usuario e inst. educativa.
     */
    private function intitucioneducativaData($id, $y) {
//echo $id . ' ' . $y;
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $entity->createQueryBuilder('mi')
                ->select('mi.id as maInsId', 'i.id as instId, i.institucioneducativa', 'p.id as personId, p.paterno, p.materno, p.nombre,p.carnet')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'mi.institucioneducativa=i.id')
                ->leftJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona=p.id')
                ->where('mi.persona = :id')
                ->andWhere('mi.gestionTipo = :cgestion')
                ->setParameter('id', $id)
                ->setParameter('cgestion', $y)
                ->getQuery();
        try {
            return $query->getArrayResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    private function removeFiles2($dir, $unzipFile, $newFile) {
        system('rm -fr ' . $dir . $unzipFile . '.emp');
        system('rm -fr ' . $dir . $unzipFile);
        system('rm -fr ' . $dir . 'e' . $unzipFile);
        system('rm -fr ' . $dir . 'e' . $unzipFile . '.txt');
        system('rm -fr ' . $dir . 'e' . $newFile . '_2015_MG');
        return 'krloss';
    }

    /**
     * Procesa el archivo subido en el servidor de DB
     *
     * @todo: parametro de entrada archivo de subida en el request
     *
     * @return: resultado procesado y/o satisfactoriamente
     *
     * */
//get the file to work
    public function upfilewebAction(Request $request) {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $form = $request->get('form');

        $id = $request->get('sie');
        //look for tuicion info
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion(:user_id::INT, :sie::INT, :roluser::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $id);
        $query->bindValue(':roluser', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();
        //validate the tuición function
//        if (!$aTuicion[0]['get_ue_tuicion']) {
//            $message = "Usuario no tiene tuición sobre la Unidad  Educativa";
//            $this->addFlash('warningcons', $message);
//            return $this->redirectToRoute('sie_herramienta_upfile_index');
//        }
//
        //new new by krlos
        if ($request->getMethod() == 'POST') {

            $connection = ssh2_connect('172.20.0.103', 22);
            ssh2_auth_password($connection, 'root', 'ASDFqwe12.103');
            $sftp = ssh2_sftp($connection);
            try {
                //echo "<pre>";
                //get the form file
                $oFile = $request->files->get('siefile');

                //get the name of file upload
                $originalName = $oFile->getClientOriginalName();
                //get the extension
                $aName = explode('.', $originalName);
                $fileType = $aName[sizeof($aName) - 1];

                //validate the allows extensions
                $aValidTypes = array('emp');
                if (!in_array(strtolower($fileType), $aValidTypes)) {
                    $session->getFlashBag()->add('warningcons', 'El archivo que intenta subir no tiene la extensión correcta.');
                    return $this->redirect($this->generateUrl('sie_herramienta_upfile_index'));
                }

                //validate the files weight
                if (!( 10001 < $oFile->getSize()) && !($oFile->getSize() < 2000000000)) {
                    $session->getFlashBag()->add('warningcons', 'El archivo que intenta subir no tiene el tamaño correcto.');
                    return $this->redirect($this->generateUrl('sie_herramienta_upfile_index'));
                }

                ////////////////////////////////////////////////////////////////////new new by krlos
                // //make the temp dir name
                $dirtmp = $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $aName[0];
                if (!file_exists($dirtmp)) {
                    mkdir($dirtmp, 0777);
                }

                //move the file emp to the directory temp
                $file = $oFile->move($dirtmp, $originalName);

                //unzip the file emp
                if (!$result = system('unzip -P  3I35I3Client ' . $dirtmp . '/' . $originalName . ' -d ' . $dirtmp . '/')) {
                    $session->getFlashBag()->add('warningcons', 'El archivo ' . $originalName . ' tiene problemas para descomprimirse.');
                    system('rm -fr ' . $dirtmp);
                    return $this->redirect($this->generateUrl('sie_herramienta_upfile_index'));
                }

                //get the content of extract file
                $aDataFileUnzip = explode('/', $result);

                $aFileName = explode('_', $originalName);
                $nameFileUnZip = str_replace(' ', '\ ', $aDataFileUnzip[sizeof($aDataFileUnzip) - 1]);
                //convert the file to base64
                system('base64 -d -i ' . $dirtmp . '/' . $nameFileUnZip . ' >  ' . $dirtmp . '/' . $nameFileUnZip . '.txt',$output);
                //convert file to UTF8
                system('iconv -f UTF-8 -t UTF-8 ' . $dirtmp . '/' . $nameFileUnZip . '.txt >> ' . $dirtmp . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 2]);

                //get the content of file
                $fileInfoContent = file($dirtmp . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 2]);

                //validate the full descompresition with the SieTypeFile from begin to the end
                if ((strcmp(preg_replace('/\s+/', '', $fileInfoContent[0]), preg_replace('/\s+/', '', $fileInfoContent[sizeof($fileInfoContent) - 1]))) !== 0) {
                    $session->getFlashBag()->add('warningcons', 'El archivo ' . $originalName . ' tiene fallas en el contenido.');
                    system('rm -fr ' . $dirtmp);
                    return $this->redirect($this->generateUrl('sie_herramienta_upfile_index'));
                }

                //dump($fileInfoContent);
                $aDataExtractFileUE = explode('|', $fileInfoContent[3]);
                $aFileInfoSie = explode('|', $fileInfoContent[1]);

                //validation up old file ... only by tec distrito, depto
                $aAccess = array(5, 2, 9);
                if ((strcmp(preg_replace('/\s+/', '', $aFileInfoSie[1]), preg_replace('/\s+/', '', '2016'))) == 0 && in_array($this->session->get('roluser'), $aAccess)) {
                  $session->getFlashBag()->add('warningcons', 'El archivo ' . $aDataExtractFileUE[1] . ' debe ser subido por su Tecnico Distrito/Departamento');
                  system('rm -fr ' . $dirtmp);
                  return $this->redirect($this->generateUrl('sie_herramienta_upfile_index'));
                }

                //validate the correct sie send with the correct version in 2016
                if ((strcmp(preg_replace('/\s+/', '', $aFileInfoSie[1]), preg_replace('/\s+/', '', '2017'))) == 0) {
                    if (
                            (strcmp(preg_replace('/\s+/', '', $aFileInfoSie[10]), preg_replace('/\s+/', '', '1.2.5'))) !== 0
                            and ( strcmp(preg_replace('/\s+/', '', $aFileInfoSie[12]), preg_replace('/\s+/', '', 'SIGED4'))) !== 0
                    ) {
                        $session->getFlashBag()->add('warningcons', 'El archivo ' . $aDataExtractFileUE[1] . ' presenta versión incorrecta para ser cargado.');
                        system('rm -fr ' . $dirtmp);
                        return $this->redirect($this->generateUrl('sie_herramienta_upfile_index'));
                    }
                }
                //die;
                //validate the correct sie send with the content of file
                if ((strcmp(preg_replace('/\s+/', '', $aDataExtractFileUE[1]), preg_replace('/\s+/', '', $id))) !== 0) {
                    $session->getFlashBag()->add('warningcons', 'El archivo ' . $aDataExtractFileUE[1] . ' que intenta subir no corresponde al seleccionado ' . $id);
                    system('rm -fr ' . $dirtmp);
                    return $this->redirect($this->generateUrl('sie_herramienta_upfile_index'));
                }

                //look for row on the db with the head data
                $objUploadFile = $em->getRepository('SieAppWebBundle:UploadFileControl')->findOneBy(array(
                    'codUe' => $aDataExtractFileUE[1],
                    'operativo' => $aDataExtractFileUE[5],
                    'gestion' => $aDataExtractFileUE[4]
                ));
                //validate if the file was uploaded
                if ($objUploadFile) {
                    if ($objUploadFile->getEstadoFile()) {
                        system('rm -fr ' . $dirtmp);
                        $session->getFlashBag()->add('warningcons', 'El archivo que intenta subir ya fue cargado al repositorio.');
                        return $this->redirect($this->generateUrl('sie_herramienta_upfile_index'));
                    } else {

                        $objUploadFile->setEstadoFile(1);
                        $objUploadFile->setDateUpload(new \DateTime('now'));
                        $objUploadFile->setRemoteAddr($_SERVER['REMOTE_ADDR']);
                        $objUploadFile->setUserAgent($_SERVER['HTTP_USER_AGENT']);
                        $em->persist($objUploadFile);
                        $em->flush();
                    }
                }

                /**
                 * create the local directory
                 */
                //get the main dir to create the direcotries
                $mainDir = $this->get('kernel')->getRootDir() . '/../web/empfiles/';
                //create the year directory with org curricular info
                $yearDir = $mainDir . $aDataExtractFileUE[4];

                if (!file_exists($yearDir)) {
                    mkdir($yearDir, 0777);
                }
                //create the distrito directory
                $distritoDir = $yearDir . '/' . $aDataExtractFileUE[38];

                if (!file_exists($distritoDir)) {
                    mkdir($distritoDir, 0777);
                }
                //create the cod UE directory
                $unidadEducativaDir = $distritoDir . '/' . $aDataExtractFileUE[1];

                if (!file_exists($unidadEducativaDir)) {
                    mkdir($unidadEducativaDir, 0777);
                }
                //create the sucursal directory
                $periodoDir = $unidadEducativaDir . '/' . $aDataExtractFileUE[5];

                if (!file_exists($periodoDir)) {
                    mkdir($periodoDir, 0777);
                }

                /**
                 * create the db directory
                 */
                //create the year directory on db server
                $pathGestion = "/consolidacion_online/" . $aDataExtractFileUE[4];
                if (!($remoteStream = is_dir("ssh2.sftp://$sftp" . $pathGestion))) {
                    ssh2_sftp_mkdir($sftp, $pathGestion);
                }

                //create the distrito directory on db server
                $pathDistrito = $pathGestion . '/' . $aDataExtractFileUE[38];
                if (!($remoteStream = is_dir("ssh2.sftp://$sftp" . $pathDistrito))) {
                    ssh2_sftp_mkdir($sftp, $pathDistrito);
                }

                //create the unidad educativa directory on db server
                $pathUnidadEducativa = $pathDistrito . '/' . $aDataExtractFileUE[1];
                if (!($remoteStream = is_dir("ssh2.sftp://$sftp" . $pathUnidadEducativa))) {
                    ssh2_sftp_mkdir($sftp, $pathUnidadEducativa);
                }

                //create the sucursal directory on db server
                $pathPeriodo = $pathUnidadEducativa . '/' . $aDataExtractFileUE[5];
                if (!($remoteStream = is_dir("ssh2.sftp://$sftp" . $pathPeriodo))) {
                    ssh2_sftp_mkdir($sftp, $pathPeriodo);
                }

                //move the file on the new local path
                rename($dirtmp . '/' . $originalName, $periodoDir . '/' . $originalName);
                rename($dirtmp . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 2], $periodoDir . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 2]);
                //send the file to the db server
                if (!ssh2_scp_send($connection, $periodoDir . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 2], $pathPeriodo . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 2], 0644)) {
                    $session->getFlashBag()->add('warningcons', "Failed to copy file [" . $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $unzipFile . "] to [" . '/consolidacion_online/' . $unzipFile . "].");
                    return $this->redirect($this->generateUrl('sie_herramienta_upfile_index'));
                }

                // todo the save record on the db if not exists
                if (!$objUploadFile) {
                    $objUploadFileNew = new UploadFileControl();
                    $objUploadFileNew->setCodUe($aDataExtractFileUE[1]);
                    $objUploadFileNew->setBimestre($aDataExtractFileUE[5] - 1);
                    $objUploadFileNew->setOperativo($aDataExtractFileUE[5]);
                    $objUploadFileNew->setVersion($aFileInfoSie[10]);
                    $objUploadFileNew->setEstadoFile(1);
                    $objUploadFileNew->setGestion($aDataExtractFileUE[4]);
                    $objUploadFileNew->setDistrito($aDataExtractFileUE[38]);
                    $objUploadFileNew->setPath($pathPeriodo . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 2]);
                    $objUploadFileNew->setDateUpload(new \DateTime('now'));
                    $objUploadFileNew->setRemoteAddr($_SERVER['REMOTE_ADDR']);
                    $objUploadFileNew->setUserAgent($_SERVER['HTTP_USER_AGENT']);
                    $em->persist($objUploadFileNew);
                    $em->flush();
                    //need remove the temporaly files
                }
                $em->getConnection()->commit();
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
                system('rm -fr ' . $dirtmp);
                echo 'Excepción capturada: ', $ex->getMessage(), "\n";
            }
            //everything good so need to remove the tmp file
            system('rm -fr ' . $dirtmp);
            $session->getFlashBag()->add('successcons', 'Archivo cargado correctamente.');
            return $this->redirect($this->generateUrl('sie_herramienta_upfile_index'));
        } else {
            $session->getFlashBag()->add('warningcons', 'Datos enviados incorrectamente.');
            return $this->redirect($this->generateUrl('sie_herramienta_upfile_index'));
        }

    }

    private function removeFiles($dir, $unzipFile) {
        // system('rm -fr ' . $dir . $unzipFile . '.emp');
        system('rm -fr ' . $dir . $unzipFile);
        system('rm -fr ' . $dir . 'e' . $unzipFile);
        system('rm -fr ' . $dir . 'e' . $unzipFile . '.txt');
        return 'krloss';
    }

    private function removeFilesAll($dir, $unzipFile) {
        system('rm -fr ' . $dir . $unzipFile . '.emp');
        system('rm -fr ' . $dir . $unzipFile);
        system('rm -fr ' . $dir . 'e' . $unzipFile);
        system('rm -fr ' . $dir . 'e' . $unzipFile . '.txt');
        return 'krloss';
    }

    private function removeFilesExist($dir, $unzipFile) {
        system('rm -fr ' . $dir . $unzipFile . '.emp');
        //system('rm -fr ' . $dir . $unzipFile);
        //   system('rm -fr ' . $dir . 'e' . $unzipFile);
        //   system('rm -fr ' . $dir . 'e' . $unzipFile . '.txt');
        return 'krloss';
    }

    private function verifyToFile($dir, $name) {
        $em = $this->getDoctrine()->getManager();
        $result = system('unzip -P  3I35I3Client ' . $dir . $name . ' -d' . $dir);
        $aFile = explode('/', $result);
        $infoName = explode('_', $name);
        $infoUpFileName = explode('_', $aFile[9]);
        $fileUploadName = substr($infoUpFileName[0], 1);
        $unzipFile = substr($name, 0, strlen($name) - 4);
        system('base64 -di ' . $dir . $aFile[9] . ' >>  ' . $dir . 'e' . $unzipFile . '.txt');
        system('iconv -f ISO-8859-15 -t UTF-8 ' . $dir . 'e' . $unzipFile . '.txt >> ' . $dir . $unzipFile);
        $fileInfo = file($dir . $unzipFile);
        $arrayFile = explode('|', $fileInfo[1]);
        die('krlos');
//check the name into the file content
        if (strcmp($infoName[0], $arrayFile[2]) != 0) {
            $this->removeFiles($dir, $unzipFile);
            return "0"; //not match the name
        }
        $bim = $arrayFile[6] - 1;
        $entity = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
            'gestion' => $arrayFile[1],
            'unidadEducativa' => $arrayFile[2],
            'bim' . $bim => 1
        ));
//check it the file was consolided
        if ($entity) {
            $this->removeFiles($dir, $unzipFile);
            return "1"; //the file was consolided
        }
        echo "<br>" . $unzipFile . "<br>";

        system('rm -fr ' . $dir . $unzipFile);
        system('rm -fr ' . $dir . 'e' . $unzipFile);
        system('rm -fr ' . $dir . 'e' . $unzipFile . '.txt');

        return $arrayFile;
    }

    /**
     * Retorana la imagen de loading minetras se procesa la informacion
     *
     * @todo: parametro de entrada array request
     *
     * @return: retorna el gif de loading
     */
    public function greetingAction(Request $request) {
        $request = $this->get('request');
//echo $request;
        $name = $request->request->get('formName');

        $img = "<img class='irc_mut' width='' height='' style='margin-top: 0px;
' src='/images/processcing.gif') }}'>";
        $return = array("responseCode" => 200, "formulario" => $img);
        $return = json_encode($return); //jscon encode the array
        return new Response($return, 200, array('Content-Type' => 'application/json')); //make sure it has the correct content type
    }

}
