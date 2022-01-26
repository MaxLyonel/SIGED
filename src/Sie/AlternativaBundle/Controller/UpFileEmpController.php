<?php

namespace Sie\AlternativaBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\UploadFileControl;

class UpFileEmpController extends Controller {

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
        return $this->render($this->session->get('pathSystem') . ':UpFileEmp:index.html.twig', array(
                    'form' => $this->formindex()->createView()
        ));
    }

    private function formindex() {
        $aGestion = array();
        $currentYear = date('Y');
        for ($i = 1; $i <= 4; $i++) {
            $aGestion[$currentYear] = $currentYear;
            $currentYear--;
        }
        return $this->createFormBuilder()
                        ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('gestion', 'choice', array('label' => 'Gestión', 'choices' => $aGestion, 'attr' => array('class' => 'form-control')))
                        ->add('search', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue', 'onclick' => 'formUpFileEmp()')))
                        ->getForm();
    }

    public function upfileFormAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        //get the values to sende
        $form = $request->get('form');
        //get the ue data
        $institutionData = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);
//        print_r($institutionData);
//        die;
        $exist = true;
        //if not exist we change the exist var
        if (!$institutionData || $institutionData[0]['orgcurricularTipo'] != 2) {
            $exist = false;
            $this->session->getFlashBag()->add('noue', 'Unidad educativa no existe');
        }
        //get the upload files per UE
        $objUeUploadFiles = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getFilesUploadByUe($form['sie'], $form['gestion']);
        return $this->render($this->session->get('pathSystem') . ':UpFileEmp:upformemp.html.twig', array(
                    'institutionData' => $institutionData,
                    'dataSend' => $form,
                    'objUeUploadFiles' => $objUeUploadFiles,
                    'exist' => $exist
        ));
    }

    public function upfileEmpAction(Request $request) {
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();


        $id = $request->get('sie');
        $gestion = $request->get('gestion');
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
//            return $this->redirectToRoute('consolidation_sie_web');
//        }
//
        //new new by krlos
        if ($request->getMethod() == 'POST') {
            $connection = ssh2_connect('172.20.0.103', 22);
            ssh2_auth_password($connection, 'root', 'ASDFqwe12.103');
            $sftp = ssh2_sftp($connection);
            try {

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
                    $session->getFlashBag()->add('warningcons', 'El archivo que intenta subir No tiene la extension correcta');
                    return $this->redirect($this->generateUrl('alternativa_up_file_emp_index'));
                }

                //validate the files weight
                if (!( 10001 < $oFile->getSize()) && !($oFile->getSize() < 2000000000)) {
                    $session->getFlashBag()->add('warningcons', 'El archivo que intenta no tiene el tamaño correcto');
                    return $this->redirect($this->generateUrl('alternativa_up_file_emp_index'));
                }

                //make the temp dir name
                $dirtmp = $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $aName[0];
                if (!file_exists($dirtmp)) {
                    mkdir($dirtmp, 0777);
                }

                //move the file emp to the directory temp
                $file = $oFile->move($dirtmp, $originalName);

                //unzip the file emp
                if (!$result = system('unzip -P  3I35I3Client ' . $dirtmp . '/' . $originalName . ' -d ' . $dirtmp . '/')) {
                    $session->getFlashBag()->add('warningcons', 'El archivo ' . $originalName . ' tiene problemas para descomprimirse');
                    system('rm -fr ' . $dirtmp);
                    return $this->redirect($this->generateUrl('alternativa_up_file_emp_index'));
                }

                //get the content of extract file
                $aDataFileUnzip = explode('/', $result);
                $aFileName = explode('_', $originalName);
                $nameFileUnZip = $aDataFileUnzip[sizeof($aDataFileUnzip) - 1];
                //convert the file to base64
                system('base64 -di ' . $dirtmp . '/' . $nameFileUnZip . ' >>  ' . $dirtmp . '/' . $nameFileUnZip . '.txt');
                //convert file to UTF8
                system('iconv -f ISO-8859-15 -t UTF-8 ' . $dirtmp . '/' . $nameFileUnZip . '.txt >> ' . $dirtmp . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 2]);
                //get the content of file
                $fileInfoContent = file($dirtmp . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 2]);


                //validate the full descompresition with the SieTypeFile from begin to the end
                if ((strcmp(preg_replace('/\s+/', '', $fileInfoContent[0]), preg_replace('/\s+/', '', $fileInfoContent[sizeof($fileInfoContent) - 1]))) !== 0) {
                    $session->getFlashBag()->add('warningcons', 'El archivo ' . $originalName . ' tiene fallas en el contenido');
                    system('rm -fr ' . $dirtmp);
                    return $this->redirect($this->generateUrl('alternativa_up_file_emp_index'));
                }
                $aDataExtractFileUE = explode('|', $fileInfoContent[3]);
                //validate the correct sie send with the content of file
                if ((strcmp(preg_replace('/\s+/', '', $aDataExtractFileUE[1]), preg_replace('/\s+/', '', $id))) !== 0) {
                    $session->getFlashBag()->add('warningcons', 'El archivo ' . $aDataExtractFileUE[1] . 'que intenta subir no corresponde al seleccionado ' . $id);
                    system('rm -fr ' . $dirtmp);
                    return $this->redirect($this->generateUrl('alternativa_up_file_emp_index'));
                }

                //look for row on the db with the head data
                $objUploadFile = $em->getRepository('SieAppWebBundle:UploadFileControl')->findOneBy(array(
                    'codUe' => $aDataExtractFileUE[1],
                    'operativo' => $aDataExtractFileUE[6],
                    'gestion' => $aDataExtractFileUE[4]
                ));
                //validate if the file was uploaded
                if ($objUploadFile) {
                    if ($objUploadFile->getEstadoFile()) {
                        system('rm -fr ' . $dirtmp);
                        $session->getFlashBag()->add('warningcons', 'El archivo que intenta subir ya fue cargado al repositorio');
                        return $this->redirect($this->generateUrl('alternativa_up_file_emp_index'));
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
                $yearDir = $mainDir . $aDataExtractFileUE[4] . '_' . $aDataExtractFileUE[22];

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
                $sucursalDir = $unidadEducativaDir . '/' . $aDataExtractFileUE[3];

                if (!file_exists($sucursalDir)) {
                    mkdir($sucursalDir, 0777);
                }
                //create the periodo directory
                $periodoDir = $sucursalDir . '/' . $aDataExtractFileUE[6];

                if (!file_exists($periodoDir)) {
                    mkdir($periodoDir, 0777);
                }
                /**
                 * create the db directory
                 */
                //create the year directory on db server
                $pathGestion = "/consolidacion_online/" . $aDataExtractFileUE[4] . '_' . $aDataExtractFileUE[22];
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
                $pathSucursal = $pathUnidadEducativa . '/' . $aDataExtractFileUE[3];
                if (!($remoteStream = is_dir("ssh2.sftp://$sftp" . $pathSucursal))) {
                    ssh2_sftp_mkdir($sftp, $pathSucursal);
                }

                //create the periodo directory on db server
                $pathPeriodo = $pathSucursal . '/' . $aDataExtractFileUE[6];
                if (!($remoteStream = is_dir("ssh2.sftp://$sftp" . $pathPeriodo))) {
                    ssh2_sftp_mkdir($sftp, $pathPeriodo);
                }



                //move the file on the new local path
                rename($dirtmp . '/' . $originalName, $periodoDir . '/' . $originalName);
                rename($dirtmp . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 2], $periodoDir . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 2]);
                //send the file to the db server
                if (!ssh2_scp_send($connection, $periodoDir . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 2], $pathPeriodo . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 2], 0644)) {
                    $session->getFlashBag()->add('warningcons', "Failed to copy file [" . $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $unzipFile . "] to [" . '/consolidacion_online/' . $unzipFile . "].");
                    return $this->redirect($this->generateUrl('alternativa_up_file_emp_index'));
                }

                // todo the save record on the db if not exists
                if (!$objUploadFile) {
                    $objUploadFileNew = new UploadFileControl();
                    $objUploadFileNew->setCodUe($aDataExtractFileUE[1]);
                    $objUploadFileNew->setBimestre($aDataExtractFileUE[6] - 1);
                    $objUploadFileNew->setOperativo($aDataExtractFileUE[6]);
                    $objUploadFileNew->setVersion('siged');
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
            $session->getFlashBag()->add('successcons', 'Archivo cargado correctamente');
            return $this->redirect($this->generateUrl('alternativa_up_file_emp_index'));
        } else {
            $session->getFlashBag()->add('warningcons', 'Datos enviados incorrectamente');
            return $this->redirect($this->generateUrl('alternativa_up_file_emp_index'));
        }
    }

}
