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

class ConsolidationController extends Controller {

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
        $id_usuario = $this->session->get('userId');
//        echo $this->session->get('roluser');
//        die;
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $aAccess = array(5, 2, 9);
        if (in_array($this->session->get('roluser'), $aAccess)) {
            $institutionData = $this->intitucioneducativaData($this->session->get('personaId'), $this->session->get('currentyear'));
            $institutionData1 = $this->getDataUe($this->session->get('userName'));
            //verificar si es IE
            if ($institutionData1) {
                return $this->render('SieRegularBundle:Consolidation:index.html.twig', array(
                            'institutionData' => $institutionData1,
                            'form' => $this->formUp($institutionData[0]['instId'])->createView()
                ));
            } else {
                return $this->render('SieRegularBundle:Consolidation:find.html.twig');
            }
        } else {
            return $this->render('SieRegularBundle:Consolidation:find.html.twig');
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
                ->setAction($this->generateUrl('consolidation_upfile_web'))
                ->add('upfile', 'file', array('mapped' => false, 'data_class' => null))
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
            $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['codigoSie']);

            if (!$objUe) {
                $message = "Unidad Eductiva no existe";
                $this->addFlash('warningcons', $message);
                return $this->redirectToRoute('consolidation_web');
            }

            return $this->render('SieRegularBundle:Consolidation:index.html.twig', array(
                        'institutionData' => array(),
                        'instEdu' => $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['codigoSie']),
                        'form' => $this->formUp($form['codigoSie'])->createView()
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

    private function removeFiles($dir, $unzipFile) {
        system('rm -fr ' . $dir . $unzipFile . '.emp');
        system('rm -fr ' . $dir . $unzipFile);
        system('rm -fr ' . $dir . 'e' . $unzipFile);
        system('rm -fr ' . $dir . 'e' . $unzipFile . '.txt');
        return 'krloss';
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
    public function upfileAction(Request $request) {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $id = $form['ieId'];
        //look for tuicion info
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion(:user_id::INT, :sie::INT, :roluser::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $id);
        $query->bindValue(':roluser', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();
        //validate the tuición function 

        if (!$aTuicion[0]['get_ue_tuicion']) {
            $message = "Usuario no tiene tuición sobre la Unidad  Educativa";
            $this->addFlash('warningcons', $message);
            return $this->redirectToRoute('consolidation_web');
        }

        //after to continue we need to validate the file
        foreach ($request->files as $uploadedFile) {
            $nameFile = ($uploadedFile['upfile']->getClientOriginalName());
            //$extention =  $uploadedFile['upfile']->getClientOriginalExtension();
        }


        //validation the extention
        if (pathinfo($nameFile, PATHINFO_EXTENSION) != 'emp') {
            $session->getFlashBag()->add('warningcons', 'El archivo que intenta subir no es el correcto...');
            return $this->redirect($this->generateUrl('consolidation_web'));
        }
        //validate the name and year
        $aFile = explode('_', $nameFile);
        //echo $request->get('form')['ieId'] . '==' . $aFile[0], '&&' . $session->get('currentyear') . '==' . $aFile[1] . "<br><br><br><br>";
        if (!($request->get('form')['ieId'] == $aFile[0] && $session->get('currentyear') == $aFile[1])) {
            $session->getFlashBag()->add('warningcons', 'El archivo no pertenece a la Institución Educativa o la gestión no corresponde');
            return $this->redirect($this->generateUrl('consolidation_web'));
        }


        $dir = $this->get('kernel')->getRootDir() . '/../web/empfiles/';

//get the name and move the file to the app server
        foreach ($request->files as $uploadedFile) {
            $name = $uploadedFile['upfile']->getClientOriginalName();
            $file = $uploadedFile['upfile']->move($dir, $name);
        }

//before to upload the file we check it if is the correct

        $dir = $this->get('kernel')->getRootDir() . '/../web/empfiles/';
        if (!$result = system('unzip -P  3I35I3Client ' . $dir . $name . ' -d' . $dir)) {
            $session->getFlashBag()->add('warningcons', 'El archivo ' . $name . ' tiene problemas para descomprimirse');
            return $this->redirect($this->generateUrl('consolidation_web'));
        }

        $aFile = explode('/', $result);
        $infoName = explode('_', $name);

        $infoUpFileName = explode('_', $aFile[9]);
        $fileUploadName = substr($infoUpFileName[0], 1);

        $unzipFile = substr($name, 0, strlen($name) - 4);

        system('base64 -di ' . $dir . $aFile[9] . ' >>  ' . $dir . 'e' . $unzipFile . '.txt');
        system('iconv -f ISO-8859-15 -t UTF-8 ' . $dir . 'e' . $unzipFile . '.txt >> ' . $dir . $unzipFile);
        $fileInfo = file($dir . $unzipFile);
//        print_r($fileInfo);
//        die;
        if (!isset($fileInfo[1])) {
            $session->getFlashBag()->add('warningcons', 'El archivo subido ' . $name . ' esta corrupto o no le corresponde el contenido ');
            return $this->redirect($this->generateUrl('consolidation_web'));
        }
        $arrayFile = explode('|', $fileInfo[1]);

//this file $unzipFile is going to up to the DB server
//echo "<br>" . $unzipFile . "<br>";
        //check the name into the file content

        if (strcmp($infoName[0], $arrayFile[2]) != 0) {
            $this->removeFiles2($dir, $unzipFile, $arrayFile[2]);
//$session->getFlashBag()->add(" el archivo subido no corresponde a la Unidad Educativa");
            $session->getFlashBag()->add('warningcons', 'El archivo subido ' . $infoName[0] . ' tiene el contenido de ' . $arrayFile[2] . ', por favor revise');
            return $this->redirect($this->generateUrl('consolidation_web'));
//return "0"; //not match the name 
        }
//        echo "<pre>";
//        print_r($infoName);
//        print_r($arrayFile);
//        echo "</pre>";
//        die('krlos');
        $bim = $arrayFile[4]; //$arrayFile[6] - 1;
        $entity = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
            'gestion' => $arrayFile[1],
            'unidadEducativa' => $arrayFile[2],
            'bim' . $bim => 1
        ));
//check it the file was consolided
        if ($entity) {
            $this->removeFiles($dir, $unzipFile);
            $session->getFlashBag()->add('warningcons', 'El archivo ' . $infoName[0] . ' ya fue consolidado');
            return $this->redirect($this->generateUrl('consolidation_web'));
//return "1"; //the file was consolided
        }
//connect to the DB server throws SSH
        $connection = ssh2_connect('172.20.0.103', 22);
        ssh2_auth_password($connection, 'root', 'ASDFqwe12.103');
        $sftp = ssh2_sftp($connection);
//send the file to the DB server
        if (!ssh2_scp_send($connection, $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $unzipFile, '/consolidacion_online/' . $unzipFile, 0644)) {
            $session->getFlashBag()->add('warningcons', "Failed to copy file [" . $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $unzipFile . "] to [" . '/consolidacion_online/' . $unzipFile . "].");
            return $this->redirect($this->generateUrl('consolidation_web'));
        }

        ssh2_exec($connection, 'psql -U postgres -h 172.20.0.103 sie_produccion');
        ssh2_exec($connection, "select * from sp_consol_menuprincipal('/consolidacion_online/" . $unzipFile . "');");
        $query = $em->getConnection()->prepare("select * from sp_consol_menuprincipal('/consolidacion_online/" . $unzipFile . "');");
        $query->execute();

        unlink($this->get('kernel')->getRootDir() . '/../web/empfiles/' . $nameFile);
        //get the name of file fisicaly
        $ainstEducativa = explode('_', $unzipFile);
        $entity = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('gestion' => $session->get('currentyear'), 'unidadEducativa' => $ainstEducativa[0]));

        ssh2_sftp_unlink($sftp, '/consolidacion_online/' . $unzipFile);
        $this->removeFiles($dir, $unzipFile);
//verficacmos si la consolidacion tiene problemas y mostramos los mismos
        ssh2_exec($connection, 'exit');
        if ($entity) {
//good on consolidacion
            $session->getFlashBag()->add('successdata', 'El archivo ha sido Consolidado correctamente...');
            return $this->redirectToRoute('consolidation_web');
        } else {
//error to consolidator
            $session->getFlashBag()->add('successdata', 'Se presento problemas durante la consolidacion...');
            return $this->redirectToRoute('consolidation_web');
        }
        die('krlos');


//        //$isCorrectFile = $this->verifyToFile($dir, $name);
//        //var_dump($isCorrectFile);
//        //$unzip_result = shell_exec('unzip -P 3I35I3Client ' . $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $nameFile . ' -d ' . $this->get('kernel')->getRootDir() . '/../web/empfiles/');
//        $dencryptFile = substr($nameFile, 0, strlen($nameFile) - 4);
//
//        //send the file to the DB server
//        $connection = ssh2_connect('172.20.0.103', 22);
//        ssh2_auth_password($connection, 'root', 'ASDFqwe12.103');
//        //$sftp = ssh2_sftp($connection);
////        echo 'unzip -P 3I35I3Client ' . '/consolidacion_online/' . $nameFile . ' -d ' . '/consolidacion_online/';
////        die;
//        if (!ssh2_scp_send($connection, $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $nameFile, '/consolidacion_online/' . $nameFile, 0644)) {
//            $session->getFlashBag()->add("Failed to copy file [" . $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $nameFile . "] to [" . '/consolidacion_online/' . $nameFile . "].");
//            return $this->redirect($this->generateUrl('consolidation_web'));
//        }
//        if (!($stream = ssh2_exec($connection, 'unzip -P 3I35I3Client ' . '/consolidacion_online/' . $nameFile . ' -d ' . '/consolidacion_online/ > /consolidacion_online/a' . $dencryptFile))) {
//            echo "fail: unable to execute command\n";
//            return $this->redirect($this->generateUrl('consolidation_web'));
//        }
////echo $dencryptFile . "<br>";
////get the name of unzip file    
////        $h = ssh2_exec($connection, "head -n 2 /consolidacion_online/a" . $dencryptFile);
////        $out = ssh2_fetch_stream($h, SSH2_STREAM_STDIO);
//////        if (ob_get_level() == 0)
//////            ob_start();
////        while (!feof($out)) {
////            $extractFileName = fgets($out);
//////            ob_flush();
//////            flush();
//////            sleep(0);
////        }
////        fclose($out);
//////        ob_end_flush();
////        $aextractFileName = explode('/', $extractFileName);
////        $intonameFile = substr($aextractFileName[2], 1);
////
////
////        if (strcmp($dencryptFile, $intonameFile) > 0) {
////            //ssh2_sftp_unlink($sftp, '/consolidacion_online/' . $nameFile);
////            //ssh2_sftp_unlink($sftp, '/consolidacion_online/a' . $dencryptFile);
////            //ssh2_sftp_unlink($sftp, '/consolidacion_online/e' . $intonameFile);
////            //ssh2_exec($connection, 'rm -fr /consolidacion_online/' . $nameFile);
////            $session->getFlashBag()->add('warningcons', 'El archivo ' . $dencryptFile . ' corresponde a: ' . $intonameFile . '. Archivo no Consolidado ');
////            return $this->redirect($this->generateUrl('consolidation_web'));
////        }
//
//
//        ssh2_exec($connection, 'base64 -di /consolidacion_online/e' . $dencryptFile . ' >> /consolidacion_online/' . $dencryptFile . '.txt');
//        ssh2_exec($connection, 'iconv -f ISO-8859-15 -t UTF-8 /consolidacion_online/' . $dencryptFile . '.txt >> /consolidacion_online/' . $dencryptFile . 'C.txt');
//
//        //get the name of file fisicaly
//        $ainstEducativa = explode('_', $dencryptFile);
//
//        //get the information file 
//        $h = ssh2_exec($connection, "head -n 2 /consolidacion_online/" . $dencryptFile . "C.txt");
//        $out = ssh2_fetch_stream($h, SSH2_STREAM_STDIO);
//        if (ob_get_level() == 0)
//            ob_start();
//        while (!feof($out)) {
//            $informationFile = fgets($out);
//            ob_flush();
//            flush();
//            sleep(0);
//        }
//        fclose($out);
//        ob_end_flush();
//
//        //echo $informationFile . '<br>' . $nameFile;
//        $ainfoFile = explode('|', $informationFile);
////        print_r($ainfoFile);
////        die;
//        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
//        $query->bindValue(':user_id', $this->session->get('userId'));
//        $query->bindValue(':sie', $ainfoFile[2]);
//        $query->bindValue(':roluser', $this->session->get('roluser'));
//        $query->execute();
//        $aTuicion = $query->fetchAll();
//
//        if (!$aTuicion[0]['get_ue_tuicion']) {
//            //ssh2_exec($connection, 'mv /consolidacion_online/' . $dencryptFile . 'C.txt ../');
//            //ssh2_exec($connection, 'rm -fr /consolidacion_online/' . $nameFile);
//            $session->getFlashBag()->add('warningcons', 'El usuario no tiene tuición sobre la Unidad educativa : ' . $ainfoFile[2] . '-' . $ainfoFile[3]);
//            return $this->redirectToRoute('consolidation_web');
//        }
//
//        //validation the name file with the content
//        if ($ainfoFile[2] != $ainstEducativa[0]) {
//            $session->getFlashBag()->add('warningcons', 'El contendio para el archivo ' . $nameFile . ' No correspnde. Los datos son: ' . $ainfoFile[2] . '-' . $ainfoFile[3]);
//            return $this->redirectToRoute('consolidation_web');
//        }
//        $bim = ($ainfoFile[6] > 1) ? $ainfoFile[6] - 1 : 2;
//
//        $conditionArray = array(
//            'gestion' => $ainfoFile[1],
//            'unidadEducativa' => $ainfoFile[2],
//            'bim' . $bim => 1
//        );
//        $dbentity = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy($conditionArray);
//
//        if (!$dbentity) {
//            $session->getFlashBag()->add('warningcons', 'El' . $nameFile . ' con datos: ' . $ainfoFile[2] . '-' . $ainfoFile[3] . ' ya fue consolidado');
//            return $this->redirectToRoute('consolidation_web');
//        }
//
//        //////////////////
//        ssh2_exec($connection, 'psql -U postgres -h 172.20.0.103 sie_produccion');
//        ssh2_exec($connection, "select * from sp_consol_menuprincipal('/consolidacion_online/" . $dencryptFile . "C.txt');");
//        $query = $em->getConnection()->prepare("select * from sp_consol_menuprincipal('/consolidacion_online/" . $dencryptFile . "C.txt');");
//        $query->execute();
//
//        unlink($this->get('kernel')->getRootDir() . '/../web/empfiles/' . $nameFile);
//
//        $entity = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('gestion' => $session->get('currentyear'), 'unidadEducativa' => $ainstEducativa[0]));
//
//        //verficacmos si la consolidacion tiene problemas y mostramos los mismos
//        if ($entity) {
//            //good on consolidacion
//            $session->getFlashBag()->add('successdata', 'Archivo Subido Correctamente...');
//            return $this->redirectToRoute('consolidation_web');
//        } else {
//            //error to consolidator
//            $session->getFlashBag()->add('successdata', 'Se presento problemas durante la consolidacion...');
//            return $this->redirectToRoute('consolidation_web');
//        }
//
//        ssh2_exec($connection, 'exit');
//        unset($connection);
//        die;
//
//        $session->getFlashBag()->add('successdata', 'Archivo Subido Correctamente...');
//        return $this->redirect($this->generateUrl('consolidation_web'));
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
