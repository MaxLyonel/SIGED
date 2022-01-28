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
use Sie\AppWebBundle\Entity\UploadFileControl;

class UpFileFtpController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * cargamos actin index para la subida de archivos
     * @todo: creamos el index de subida de archivo 
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
        return $this->render('SieRegularBundle:UpFileFtp:index.html.twig', array(
                    'files' => scandir($this->get('kernel')->getRootDir() . '/../web/ftpempfiles/', 1)
        ));
    }

    public function seefileAction(request $request) {
        return $this->render('SieRegularBundle:UpFileFtp:empfilesperdepto1.html.twig', array(
                    'filesemp' => scandir($this->get('kernel')->getRootDir() . '/../web/ftpempfiles/' . $request->get('dep'), 1),
                    'depto' => $request->get('dep')
        ));
    }

    public function upfileAction(request $request) {
        ///dump($request);
        $file = $request->get('file');
        //get the name of file
        $originalName = $file;

        $aFile = explode('.', $file);
        $aMoreFile = explode('_', $aFile[0]);
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //get the extension file
        //$extFile = $aFile[sizeof($aFile) - 1];
        $connection = ssh2_connect('172.20.0.103', 22);
        ssh2_auth_password($connection, 'root', 'ASDFqwe12.103');
        $sftp = ssh2_sftp($connection);
        try {
            //get the name of file upload
            $originalName = $file;
            //get the extension
            $aName = explode('.', $originalName);
            $fileType = $aName[sizeof($aName) - 1];

            //validate the allows extensions
            $aValidTypes = array('emp');
            if (!in_array(strtolower($fileType), $aValidTypes)) {
                $message = "El archivo " . $originalName . " que intenta subir no tiene la extensión correcta";
                $this->addFlash('wrongftpupload', $message);
                return $this->render('SieRegularBundle:UpFileFtp:empfilesperdepto1.html.twig', array(
                            'filesemp' => scandir($this->get('kernel')->getRootDir() . '/../web/ftpempfiles/' . $request->get('depto'), 1),
                            'depto' => $request->get('depto')
                ));
            }
            ////////////////////////////////////////////////////////////////////new new by krlos
            $uploadFilePath = $dirtmp = $this->get('kernel')->getRootDir() . '/../web/ftpempfiles/' . $request->get('depto');
            // //make the temp dir name
            $dirtmp = $this->get('kernel')->getRootDir() . '/../web/ftpempfiles/' . $request->get('depto') . '/' . $aName[0];

            if (!file_exists($dirtmp)) {
                mkdir($dirtmp, 0777);
            }
            //move the file emp to the directory temp
            //$file = $oFile->move($dirtmp, $originalName);
            rename($uploadFilePath . '/' . $originalName, $dirtmp . '/' . $originalName);

            //unzip the file emp
            if (!$result = exec('unzip -P  3I35I3Client ' . $dirtmp . '/' . $originalName . ' -d ' . $dirtmp . '/')) {
                system('rm -fr ' . $dirtmp);
                $message = "El archivo " . $originalName . " tiene problemas en descomprimires";
                $this->addFlash('wrongftpupload', $message);
                return $this->render('SieRegularBundle:UpFileFtp:empfilesperdepto1.html.twig', array(
                            'filesemp' => scandir($this->get('kernel')->getRootDir() . '/../web/ftpempfiles/' . $request->get('depto'), 1),
                            'depto' => $request->get('depto')
                ));
            }

            //get the content of extract file
            $aDataFileUnzip = explode('/', $result);
            $aFileName = explode('_', $originalName);
            $nameFileUnZip = $aDataFileUnzip[sizeof($aDataFileUnzip) - 1];
            //convert the file to base64
            system('base64 -di ' . $dirtmp . '/' . $nameFileUnZip . ' >>  ' . $dirtmp . '/' . $nameFileUnZip . '.txt');
            //convert file to UTF8
            system('iconv -f UTF-8 -t UTF-8 ' . $dirtmp . '/' . $nameFileUnZip . '.txt >> ' . $dirtmp . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 2]);

            //get the content of file
            $fileInfoContent = file($dirtmp . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 2]);

            //validate the full descompresition with the SieTypeFile from begin to the end
            if ((strcmp(preg_replace('/\s+/', '', $fileInfoContent[0]), preg_replace('/\s+/', '', $fileInfoContent[sizeof($fileInfoContent) - 1]))) !== 0) {
                system('rm -fr ' . $dirtmp);
                $message = 'El archivo ' . $originalName . ' tiene fallas en el contenido';
                $this->addFlash('wrongftpupload', $message);
                return $this->render('SieRegularBundle:UpFileFtp:empfilesperdepto1.html.twig', array(
                            'filesemp' => scandir($this->get('kernel')->getRootDir() . '/../web/ftpempfiles/' . $request->get('depto'), 1),
                            'depto' => $request->get('depto')
                ));
            }
            $aDataExtractFileUE = explode('|', $fileInfoContent[3]);
            $aFileInfoSie = explode('|', $fileInfoContent[1]);

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
                    $message = 'El archivo ' . $originalName . ' que intenten subir ya fue cargado al repositorio';
                    $this->addFlash('wrongftpupload', $message);
                    return $this->render('SieRegularBundle:UpFileFtp:empfilesperdepto1.html.twig', array(
                                'filesemp' => scandir($this->get('kernel')->getRootDir() . '/../web/ftpempfiles/' . $request->get('depto'), 1),
                                'depto' => $request->get('depto')
                    ));
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
                //$session->getFlashBag()->add('warningcons', "Failed to copy file [" . $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $unzipFile . "] to [" . '/consolidacion_online/' . $unzipFile . "].");
                //return $this->redirect($this->generateUrl('consolidation_sie_web'));
                system('rm -fr ' . $dirtmp);
                $message = "Failed to copy file [" . $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $unzipFile . "] to [" . '/consolidacion_online/' . $unzipFile . "].";
                $this->addFlash('wrongftpupload', $message);
                return $this->render('SieRegularBundle:UpFileFtp:empfilesperdepto1.html.twig', array(
                            'filesemp' => scandir($this->get('kernel')->getRootDir() . '/../web/ftpempfiles/' . $request->get('depto'), 1),
                            'depto' => $request->get('depto')
                ));
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
            $message = "Archivo " . $originalName . " subido correctamente";
            $this->addFlash('gooduploadftp', $message);
            //remove the file uploaded
            system('rm -fr ' . $dirtmp);
            return $this->render('SieRegularBundle:UpFileFtp:empfilesperdepto1.html.twig', array(
                        'filesemp' => scandir($this->get('kernel')->getRootDir() . '/../web/ftpempfiles/' . $request->get('depto'), 1),
                        'depto' => $request->get('depto')
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            system('rm -fr ' . $dirtmp);
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

//    public function upfileAllAction(request $request) {
//
//
//        $em = $this->getDoctrine()->getManager();
//        $empFiles = scandir($this->get('kernel')->getRootDir() . '/../web/ftpempfiles/' . $request->get('dep'), 1);
//
//        //print_r($empFiles);
//        reset($empFiles);
//        while ($val = current($empFiles)) {
//            //get the name of file
//            $originalName = $val;
//            $aFile = explode('.', $val);
//            $aMoreFile = explode('_', $aFile[0]);
//            //get the extension file
//            $extFile = $aFile[sizeof($aFile) - 1];
//            //check if the extesion is emp
//            if ($extFile == 'emp') {
//                $connection = ssh2_connect('172.20.0.103', 22);
//                ssh2_auth_password($connection, 'root', 'ASDFqwe12.103');
//                $sftp = ssh2_sftp($connection);
//                //echo $theEmpFile;
//                //create directories on app server
//                $dirgestion = $this->get('kernel')->getRootDir() . '/../web/empfiles/' . '2015';
//                if (!file_exists($dirgestion)) {
//                    mkdir($dirgestion, 0777);
//                }
//                //get the distrito
//                $objUnidEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($aMoreFile[0]);
//                $dirdistrito = $dirgestion . '/' . $objUnidEducativa[0]['distritoTipoId'];
//                if (!file_exists($dirdistrito)) {
//                    mkdir($dirdistrito, 0777);
//                }
//                //create the sie directory
//                $dirsie = $dirdistrito . '/' . $aMoreFile[0];
//                if (!file_exists($dirsie)) {
//                    mkdir($dirsie, 0777);
//                }
//                $dir = $dirsie . '/';
//                rename($this->get('kernel')->getRootDir() . '/../web/ftpempfiles/' . $request->get('dep') . '/' . $originalName, $dir . '' . $originalName);
//                //$file = $oFile->move($dir, $originalName);
//                //echo 'unzip -P  3I35I3Client ' . $dir . $originalName . ' -d ' . $dir;
//                //unzip the file
//                if ($result = system('unzip -P  3I35I3Client ' . $dir . $originalName . ' -d ' . $dir)) {
//
//                    $aDataFileUnzip = explode('/', $result);
//                    $aFileName = explode('_', $originalName);
//                    echo "<pre>";
//                    print_r($aDataFileUnzip);
//                    print_r($aFileName);
//                    echo "</pre>";
//
//                    $unzipFile = substr($originalName, 0, strlen($originalName) - 4);
//
//                    system('base64 -di ' . $dir . $aDataFileUnzip[12] . ' >>  ' . $dir . 'e' . $unzipFile . '.txt');
//                    system('iconv -f ISO-8859-15 -t UTF-8 ' . $dir . 'e' . $unzipFile . '.txt >> ' . $dir . $unzipFile);
//
//                    //get the head of file
//                    $fileInfo = file($dir . $unzipFile);
//                    $aFileInfoSie = explode('|', $fileInfo[1]);
//                    print_r($aFileInfoSie);
//
//                    //$sieLogged = $request->get('sie');
////                    echo $sieLogged . "krlosssssssssssss" . $unzipFile . "<br>";
////                    echo ('base64 -di ' . $dir . $aDataFileUnzip[12] . ' >>  ' . $dir . 'e' . $unzipFile . '.txt');
////                    echo "<br>";
////                    echo ('base64 -di ' . $dir . $aDataFileUnzip[12] . ' >>  ' . $dir . 'e' . $unzipFile . '.txt');
////                    die;
////                    die;
//                    //$this->removeFilesAll($dirsie . '/', $unzipFile);
//                    //$session->getFlashBag()->add('warningcons', 'El archivo seleccionado ' . $sieLogged . ' No es el mismo que intenta subir ' . $aFileInfoSie['2']);
//                    //return $this->redirect($this->generateUrl('consolidation_sie_web'));
//                    echo "krlosssss";
//                    print_r($aFileInfoSie);
//                    //validate: if the file has emp and SIGED paramters
//                    //look for row on the db with the head data
//                    $objUploadFile = $em->getRepository('SieAppWebBundle:UploadFileControl')->findOneBy(array(
//                        'codUe' => $aFileInfoSie[2],
//                        'operativo' => $aFileInfoSie[6],
//                        'gestion' => $aFileInfoSie[1]//,
//                            //'estadoFile' => 1
//                    ));
//                    $dirope = $dirsie . '/' . $aFileInfoSie[6];
//                    if (!file_exists($dirope)) {
//                        mkdir($dirope, 0777);
//                    }
//                    //create the directory on db server
//                    //$pathope = $pathsie . '/' . $aFileInfoSie[6];
////                        if (!($remoteStream = is_dir("ssh2.sftp://$sftp" . $pathope))) {
////                            ssh2_sftp_mkdir($sftp, $pathope);
////                        }
//                    //check if the file was upload
//                    if ($objUploadFile) {
//                        if ($objUploadFile->getEstadoFile()) {
//                            $this->removeFilesAll($dirsie . '/', $unzipFile);
//                            //$session->getFlashBag()->add('warningcons', 'El archivo que intenta subir ya fue cargado al repositorio');
//                            //return $this->redirect($this->generateUrl('consolidation_sie_web'));
//                        } else {
//                            $this->removeFilesExist($dirope . '/', $unzipFile);
//                            $objUploadFile->setEstadoFile(1);
//                            $objUploadFile->setDateUpload(new \DateTime('now'));
//                            $objUploadFileNew->setRemoteAddr($_SERVER['REMOTE_ADDR']);
//                            $objUploadFileNew->setUserAgent($_SERVER['HTTP_USER_AGENT']);
//                            $em->persist($objUploadFile);
//                            $em->flush();
//                        }
//                        //need remove the temporaly files
//                    } else {
//                        $objUploadFileNew = new UploadFileControl();
//                        $objUploadFileNew->setCodUe($aFileInfoSie[2]);
//                        $objUploadFileNew->setBimestre($aFileInfoSie[6] - 1);
//                        $objUploadFileNew->setOperativo($aFileInfoSie[6]);
//                        $objUploadFileNew->setVersion($aFileInfoSie[10]);
//                        $objUploadFileNew->setEstadoFile(1);
//                        $objUploadFileNew->setGestion($aFileInfoSie[1]);
//                        $objUploadFileNew->setDistrito($objUnidEducativa[0]['distritoTipoId']);
//                        //$objUploadFileNew->setPath($pathope . '/' . $unzipFile);
//                        $objUploadFileNew->setDateUpload(new \DateTime('now'));
//                        $objUploadFileNew->setRemoteAddr($_SERVER['REMOTE_ADDR']);
//                        $objUploadFileNew->setUserAgent($_SERVER['HTTP_USER_AGENT']);
//                        $em->persist($objUploadFileNew);
//                        $em->flush();
//                    }
//                    //$em->getConnection()->commit();
//
//
//
//                    echo "<br>" . $unzipFile . "<br>";
//                    rename($dirsie . '/' . $originalName, $dirope . '/' . $originalName);
//                    rename($dirsie . '/' . $unzipFile, $dirope . '/' . $unzipFile);
//                    //send the file to the remote server
//                    /* if (!ssh2_scp_send($connection, $dirope . '/' . $unzipFile, $pathope . '/' . $unzipFile, 0644)) {
//                      } */
//                    //$file = $oFile->move($dirope, $originalName);
//                    var_dump($objUploadFile);
//                    $this->removeFiles($dirsie . '/', $unzipFile);
//                }
//            }
//            echo "<hr>";
//            next($empFiles);
//        }
//        echo "</pre>";
//
//        die;
//    }

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
//    public function upfile1Action(Request $request) {
//
//        $session = new Session();
//        $em = $this->getDoctrine()->getManager();
//        $em->getConnection()->beginTransaction();
//        $form = $request->get('form');
//
//        $id = $request->get('sie');
//        //look for tuicion info
//        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion(:user_id::INT, :sie::INT, :roluser::INT)');
//        $query->bindValue(':user_id', $this->session->get('userId'));
//        $query->bindValue(':sie', $id);
//        $query->bindValue(':roluser', $this->session->get('roluser'));
//        $query->execute();
//        $aTuicion = $query->fetchAll();
//        //validate the tuición function 
////        if (!$aTuicion[0]['get_ue_tuicion']) {
////            $message = "Usuario no tiene tuición sobre la Unidad  Educativa";
////            $this->addFlash('warningcons', $message);
////            return $this->redirectToRoute('consolidation_sie_web');
////        }
////
//        //new new by krlos
//        if ($request->getMethod() == 'POST') {
//            $connection = ssh2_connect('172.20.0.103', 22);
//            ssh2_auth_password($connection, 'root', 'ASDFqwe12.103');
//            $sftp = ssh2_sftp($connection);
//            try {
//                echo "<pre>";
//                //get the form file
//                $oFile = $request->files->get('siefile');
//
//                //get the name of file upload
//                $originalName = $oFile->getClientOriginalName();
//                //get the extension
//                $aName = explode('.', $originalName);
//                $fileType = $aName[sizeof($aName) - 1];
//
//                //validate the allows extensions
//                $aValidTypes = array('emp');
//                if (!in_array(strtolower($fileType), $aValidTypes)) {
//                    $session->getFlashBag()->add('warningcons', 'El archivo que intenta subir No tiene la extension correcta');
//                    return $this->redirect($this->generateUrl('consolidation_sie_web'));
//                }
//
//                //validate the files weight
//                if (!( 10001 < $oFile->getSize()) && !($oFile->getSize() < 2000000000)) {
//                    $session->getFlashBag()->add('warningcons', 'El archivo que intenta no tiene el tamaño correcto');
//                    return $this->redirect($this->generateUrl('consolidation_sie_web'));
//                }
//                /////////
//                //create directories on app server
//                $dirgestion = $this->get('kernel')->getRootDir() . '/../web/empfiles/' . '2015';
//                if (!file_exists($dirgestion)) {
//                    mkdir($dirgestion, 0777);
//                }
//                //create the directory on db server
//                $pathgestion = "/consolidacion_online/" . '2015';
//                if (!($remoteStream = is_dir("ssh2.sftp://$sftp" . $pathgestion))) {
//                    ssh2_sftp_mkdir($sftp, $pathgestion);
//                }
//                //get the distrito
//                $objUnidEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($id);
//                $dirdistrito = $dirgestion . '/' . $objUnidEducativa[0]['distritoTipoId'];
//                if (!file_exists($dirdistrito)) {
//                    mkdir($dirdistrito, 0777);
//                }
//                //create the directory on db server
//                $pathdistrito = $pathgestion . '/' . $objUnidEducativa[0]['distritoTipoId'];
//                if (!($remoteStream = is_dir("ssh2.sftp://$sftp" . $pathdistrito))) {
//                    ssh2_sftp_mkdir($sftp, $pathdistrito);
//                }
//
//                //create the sie directory
//                $dirsie = $dirdistrito . '/' . $id;
//                if (!file_exists($dirsie)) {
//                    mkdir($dirsie, 0777);
//                }
//                //create the directory on db server
//                $pathsie = $pathdistrito . '/' . $id;
//                if (!($remoteStream = is_dir("ssh2.sftp://$sftp" . $pathsie))) {
//                    ssh2_sftp_mkdir($sftp, $pathsie);
//                }
//
//                ///////////
//                //move the file to the app server
//                //$dir = $this->get('kernel')->getRootDir() . '/../web/empfiles/';
//                $dir = $dirsie . '/';
//                $file = $oFile->move($dir, $originalName);
//                //echo 'unzip -P  3I35I3Client ' . $dir . $originalName . ' -d ' . $dir;
//                //unzip the file
//                if (!$result = system('unzip -P  3I35I3Client ' . $dir . $originalName . ' -d ' . $dir)) {
//                    $session->getFlashBag()->add('warningcons', 'El archivo ' . $originalName . ' tiene problemas para descomprimirse');
//                    return $this->redirect($this->generateUrl('consolidation_sie_web'));
//                }
//
//                //get the name file upload and unzipfile
//                $aDataFileUnzip = explode('/', $result);
//                $aFileName = explode('_', $originalName);
//
//                echo "<pre>";
//                print_r($aDataFileUnzip);
//                print_r($aFileName);
//                echo "</pre>";
////            die;
//                $unzipFile = substr($originalName, 0, strlen($originalName) - 4);
//                system('base64 -di ' . $dir . $aDataFileUnzip[13] . ' >>  ' . $dir . 'e' . $unzipFile . '.txt');
//                system('iconv -f ISO-8859-15 -t UTF-8 ' . $dir . 'e' . $unzipFile . '.txt >> ' . $dir . $unzipFile);
//                //get the head of file
//                $fileInfo = file($dir . $unzipFile);
//                $aFileInfoSie = explode('|', $fileInfo[1]);
//                print_r($aFileInfoSie);
//                //die;
//                $sieLogged = $request->get('sie');
//                if ($sieLogged != $aFileInfoSie['2']) {
//                    $this->removeFilesAll($dirsie . '/', $unzipFile);
//                    $session->getFlashBag()->add('warningcons', 'El archivo seleccionado ' . $sieLogged . ' No es el mismo que intenta subir ' . $aFileInfoSie['2']);
//                    return $this->redirect($this->generateUrl('consolidation_sie_web'));
//                }
//
//                echo "krlosssss";
//                print_r($aFileInfoSie);
//                //validate: if the file has emp and SIGED paramters
//                $aValidFile = array('emp', 'siged');
//                if (!in_array(strtolower($aFileInfoSie[7]), $aValidFile)) {
//                    $this->removeFilesAll($dirsie . '/', $unzipFile);
//                    $session->getFlashBag()->add('warningcons', 'El archivo que intenta subir los parametros requeridos -(' . $aFileInfoSie[7] . ' ) ');
//                    return $this->redirect($this->generateUrl('consolidation_sie_web'));
//                }
//                if (!in_array(strtolower($aFileInfoSie[12]), $aValidFile)) {
//                    $this->removeFilesAll($dirsie . '/', $unzipFile);
//                    $session->getFlashBag()->add('warningcons', 'El archivo que intenta subir los parametros requeridos -(' . $aFileInfoSie[12] . ' ) ');
//                    return $this->redirect($this->generateUrl('consolidation_sie_web'));
//                }
//                //look for row on the db with the head data
//                $objUploadFile = $em->getRepository('SieAppWebBundle:UploadFileControl')->findOneBy(array(
//                    'codUe' => $aFileInfoSie[2],
//                    'operativo' => $aFileInfoSie[6],
//                    'gestion' => $aFileInfoSie[1]//,
//                        //'estadoFile' => 1
//                ));
//                $dirope = $dirsie . '/' . $aFileInfoSie[6];
//                if (!file_exists($dirope)) {
//                    mkdir($dirope, 0777);
//                }
//                //create the directory on db server
//                $pathope = $pathsie . '/' . $aFileInfoSie[6];
//                if (!($remoteStream = is_dir("ssh2.sftp://$sftp" . $pathope))) {
//                    ssh2_sftp_mkdir($sftp, $pathope);
//                }
//
//                //check if the file was upload
//                if ($objUploadFile) {
//                    if ($objUploadFile->getEstadoFile()) {
//                        $this->removeFilesAll($dirsie . '/', $unzipFile);
//                        $session->getFlashBag()->add('warningcons', 'El archivo que intenta subir ya fue cargado al repositorio');
//                        return $this->redirect($this->generateUrl('consolidation_sie_web'));
//                    } else {
//                        $this->removeFilesExist($dirope . '/', $unzipFile);
//                        $objUploadFile->setEstadoFile(1);
//                        $objUploadFile->setDateUpload(new \DateTime('now'));
//                        $objUploadFileNew->setRemoteAddr($_SERVER['REMOTE_ADDR']);
//                        $objUploadFileNew->setUserAgent($_SERVER['HTTP_USER_AGENT']);
//                        $em->persist($objUploadFile);
//                        $em->flush();
//                    }
//                    //need remove the temporaly files
//                } else {
//                    $objUploadFileNew = new UploadFileControl();
//                    $objUploadFileNew->setCodUe($aFileInfoSie[2]);
//                    $objUploadFileNew->setBimestre($aFileInfoSie[6] - 1);
//                    $objUploadFileNew->setOperativo($aFileInfoSie[6]);
//                    $objUploadFileNew->setVersion($aFileInfoSie[10]);
//                    $objUploadFileNew->setEstadoFile(1);
//                    $objUploadFileNew->setGestion($aFileInfoSie[1]);
//                    $objUploadFileNew->setDistrito($objUnidEducativa[0]['distritoTipoId']);
//                    $objUploadFileNew->setPath($pathope . '/' . $unzipFile);
//                    $objUploadFileNew->setDateUpload(new \DateTime('now'));
//                    $objUploadFileNew->setRemoteAddr($_SERVER['REMOTE_ADDR']);
//                    $objUploadFileNew->setUserAgent($_SERVER['HTTP_USER_AGENT']);
//                    $em->persist($objUploadFileNew);
//                    $em->flush();
//                }
//                $em->getConnection()->commit();
//
//
//
//                echo "<br>" . $unzipFile . "<br>";
//                rename($dirsie . '/' . $originalName, $dirope . '/' . $originalName);
//                rename($dirsie . '/' . $unzipFile, $dirope . '/' . $unzipFile);
//                //send the file to the remote server
//                if (!ssh2_scp_send($connection, $dirope . '/' . $unzipFile, $pathope . '/' . $unzipFile, 0644)) {
//                    $session->getFlashBag()->add('warningcons', "Failed to copy file [" . $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $unzipFile . "] to [" . '/consolidacion_online/' . $unzipFile . "].");
//                    return $this->redirect($this->generateUrl('consolidation_sie_web'));
//                }
//                //$file = $oFile->move($dirope, $originalName);
//                var_dump($objUploadFile);
//                $this->removeFiles($dirsie . '/', $unzipFile);
//
//                $session->getFlashBag()->add('successcons', 'Archivo cargado correctamente');
//                return $this->redirect($this->generateUrl('consolidation_sie_web'));
//            } catch (Exception $ex) {
//                $em->getConnection()->rollback();
//                $this->removeFilesAll($dirsie . '/', $unzipFile);
//                echo 'Excepción capturada: ', $ex->getMessage(), "\n";
//            }
//            //connect to the remote server throght SSH
//            //remove the temporaly files
//            print_r($request->get('sie'));
//            die;
//            echo "</pre>";
//        } else {
//            $session->getFlashBag()->add('warningcons', 'Datos enviados incorrectamente');
//            return $this->redirect($this->generateUrl('consolidation_sie_web'));
//        }
//    }

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
