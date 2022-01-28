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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;

class MassiveTransferController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * declaracion jurada Index 
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':MassiveTransfer:index.html.twig', array(
                    'form' => $this->craeteformsearchsie()->createView()
        ));
    }

    private function craeteformsearchsie() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('download_file_sie_build'))
                        ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        //->add('gestion', 'choice', array('label' => 'Gestión', 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        //->add('bimestre', 'choice', array('label' => 'Bimestre', 'attr' => array('class' => 'form-control', 'empty_data' => 'Seleccionar...')))
                        ->add('nivel', 'choice', array('label' => 'Nivel', 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('grado', 'choice', array('label' => 'Grado', 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('paralelo', 'choice', array('label' => 'Paralelo', 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('turno', 'choice', array('label' => 'Turno', 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('search', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'lookforstudents()')))
                        ->getForm();
    }

    private function createFormToBuild($sie, $gestion, $bim) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('download_file_sie_build'))
                        ->add('sie', 'hidden', array('data' => $sie))
                        ->add('gestion', 'hidden', array('data' => $gestion))
                        ->add('bimestre', 'hidden', array('data' => $bim))
                        ->add('generar', 'button', array('label' => 'Generar Archivo', 'attr' => array('class' => 'btn btn-link', 'onclick' => 'buildAgain()')))
                        ->getForm()
        ;
    }

    public function downloadAction(Request $request, $file) {
        //get path of the file
        $dir = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';
        //remove space on the post values
        $file = preg_replace('/\s+/', '', $file);
        $file = str_replace('%20', '', $file);
        //create response to donwload the file
        $response = new Response();
        //then send the headers to foce download the zip file
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $file));
        $response->setContent(file_get_contents($dir) . $file);
        $response->headers->set('Pragma', "no-cache");
        $response->headers->set('Expires', "0");
        $response->headers->set('Content-Transfer-Encoding', "binary");
        $response->sendHeaders();
        $response->setContent(readfile($dir . $file));
        return $response;
    }

    public function getnivelAction($sie) {
        $em = $this->getDoctrine()->getManager();
        $objNivel = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getNivelBySieAndGestion($sie, $this->session->get('currentyear'));
        $aNivel = array();
        foreach ($objNivel as $onivel) {
            $aNivel[$onivel['nivelTipo']] = $onivel['nivel'];
        }
        $response = new JsonResponse();
        return $response->setData(array('nivel' => $aNivel));
    }

    public function getgradoAction($sie, $nivel) {
        $em = $this->getDoctrine()->getManager();
        $objGrado = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getGradolBySieAndGestion($sie, $nivel, $this->session->get('currentyear'));
        $aGrado = array();
        foreach ($objGrado as $ogrado) {
            $aNivel[$ogrado['gradoTipo']] = $ogrado['grado'];
        }
        $response = new JsonResponse();
        return $response->setData(array('grado' => $aNivel));
    }

    public function getparaleloAction($sie, $nivel, $grado) {
        $em = $this->getDoctrine()->getManager();
        $objParalelo = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getParaleloBySieAndGestion($sie, $nivel, $grado, $this->session->get('currentyear'));
        $aParalelo = array();
        foreach ($objParalelo as $oparalelo) {
            $aParalelo[$oparalelo['paraleloTipo']] = $oparalelo['paralelo'];
        }
        $response = new JsonResponse();
        return $response->setData(array('paralelo' => $aParalelo));
    }

    public function getturnoAction($sie, $nivel, $grado, $paralelo) {

        $em = $this->getDoctrine()->getManager();
        $objTurno = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getTurnoBySieAndGestion($sie, $nivel, $grado, $paralelo, $this->session->get('currentyear'));
        $aTurno = array();
        foreach ($objTurno as $oturno) {
            $aTurno[$oturno['turnoTipo']] = $oturno['turno'];
        }
        $response = new JsonResponse();
        return $response->setData(array('turno' => $aTurno));
    }

    public function studentsAction(request $request) {
        $em = $this->getDoctrine()->getManager();

        $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getStudentsBySieAndGestion($request->get('sie'), $request->get('nivel'), $request->get('grado'), $request->get('paralelo'), $request->get('turno'), $this->session->get('currentyear'));
        $dataSelected = serialize(array('sie' => $request->get('sie'), 'nivel' => $request->get('nivel'), 'grado' => $request->get('grado'), 'paralelo' => $request->get('paralelo'), 'turno' => $request->get('turno'), 'year' => $this->session->get('currentyear')));
        return $this->render($this->session->get('pathSystem') . ':MassiveTransfer:fileDownload.html.twig', array(
                    'students' => $objStudents,
                    'form' => $this->createFormTransferMassive($dataSelected)->createView()
        ));
    }

    private function createFormTransferMassive($data) {
        return $this->createFormBuilder()
                        ->add('sienew', 'text', array('label' => 'SIE TRANSFERNECIA', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('massivetransfer', 'button', array('label' => 'Aceptar', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'desunificarrude()')))
                        ->add('dataselected', 'text', array('data' => $data))
                        //->add('datasend', 'text')
                        ->getForm();
    }

    /**
     * to do the desunification of rudes
     * @param Request $request
     * @return \Sie\RegularBundle\Controller\Exception
     */
    public function exectAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $aDataSend = $request->get('form');
            $aInscriptions = $request->get('forme');
            echo "<pre>";
            print_r($aInscriptions);
            print_r($aDataSend);

            $aInfoUe = unserialize($aDataSend['dataselected']);
            print_r($aInfoUe);
            //look if UE exists
            $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'paraleloTipo' => $aInfoUe['paralelo'],
                'turnoTipo' => $aInfoUe['turno'],
                'nivelTipo' => $aInfoUe['nivel'],
                'gradoTipo' => $aInfoUe['grado'],
                'institucioneducativa' => $aDataSend['sienew'],
                'gestionTipo' => $aInfoUe['year']
            ));

            //check if ue has the curso
            if (!$objCurso) {
                //return to the index page
            }
            $newCursoId = $objCurso->getId();
            echo "<hr>";
            var_dump($objCurso->getId());
            //echo $objCurso->getId();

            echo "</pre>";
            die;


//            $aStudent = $request->get('forme');
//            $aInscription = $request->get('form');
//            //get the data student through id studen
//            $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($aStudent['idStudent']);
//            //data UE
//            $sSie = substr($objStudent->getCodigoRude(), '0', '8');
//            //create a new studen with the same data and new rude
//            $digits = 4;
//            $mat = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);
//            $rude = $sSie . $this->session->get('currentyear') . $mat . $this->generarRude($sSie . $this->session->get('currentyear') . $mat);
//
//            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante');");
//            $query->execute();
//
//            $student = new Estudiante();
//            $student->setPaterno(strtoupper($objStudent->getPaterno()));
//            $student->setMaterno(strtoupper($objStudent->getMaterno()));
//            $student->setNombre(strtoupper($objStudent->getNombre()));
//            $student->setCarnetIdentidad($objStudent->getCarnetIdentidad());
//            $student->setComplemento($objStudent->getComplemento());
//            $student->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($objStudent->getGeneroTipo()));
//            $student->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($objStudent->getPaisTipo()));
//            $student->setFechaNacimiento(($objStudent->getFechaNacimiento()));
//            $student->setCodigoRude($rude);
//
//            $em->persist($student);
//            $em->flush();
//            $studentnewId = $student->getId();
//            //read the data inscription to explode
//            reset($aInscription);
//            while ($val = current($aInscription)) {
//                //change the inscription to the new student
//                if (key($aInscription) != '_token') {
//                    $objInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($val);
//                    $objInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($studentnewId));
//                    $em->persist($objInscription);
//                    $em->flush();
//                }
//                next($aInscription);
//            }
//            $em->getConnection()->commit();
//            //get the inscription history to the old studen
//            $dataOldInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getHistoryInscription($aStudent['idStudent']);
//            //get the inscription history to the new student
//            $dataNewInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getHistoryInscription($studentnewId);
//            $objNewStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($studentnewId);
//            return $this->render($this->session->get('pathSystem') . ':DesunificationRude:result.html.twig', array(
//                        'dataOldInscription' => $dataOldInscription,
//                        'dataNewInscription' => $dataNewInscription,
//                        'oldStudent' => $objStudent,
//                        'newStudent' => $objNewStudent
//            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $ex;
        }
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function buildAgainAction(Request $request) {


        $form['sie'] = $request->get('sie');
        $form['gestion'] = $request->get('gestion');
        $form['bimestre'] = $request->get('bimestre');
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
//        echo "<pre>";
//        print_r($form);
//        echo "</pre>";
//        die;
        try {
            //get the content of directory 
            $aDirectoryContent = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';
//            var_dump(!(in_array($form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.igm', scandir($aDirectoryContent, 1))));
//            echo "<br>";
//            var_dump($request->get('genera'));
//            die;
            //generate to file with thwe sql process
            $operativo = $form['bimestre'] + 1;
            $query = $em->getConnection()->prepare("select * from sp_genera_arch_regular_txt('" . $form['sie'] . "','" . $form ['gestion'] . "','" . $operativo . "','" . $form['bimestre'] . "');");
            $query->execute();
            $em->getConnection()->commit();
            $em->flush();
            $em->clear();
            //die('krlos');
            //todo the connexion to the server
            $connection = ssh2_connect('172.20.0.103', 22);
            ssh2_auth_password($connection, 'root', 'ASDFqwe12.103');
            $sftp = ssh2_sftp($connection);
            //get the path server
            $path = '../bajada_local/';
            //ssh2_exec($connection, 'iconv -f UTF-8  -t ISO-8859-1 ' . $path . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie  >> ' . $path . 'ee' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie');
            ssh2_exec($connection, 'base64  ' . $path . '' . $form ['sie'] . '-' . $form ['gestion'] . '-' . date('m-d') . '_' . $form ['bimestre'] . 'B.sie  >> ' . $path . 'e' . $form ['sie'] . '-' . $form ['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie');
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
            $newGenerateFile = $form ['sie'] . '-' . $form ['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B';
            $local_file = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/' . 'e' . $newGenerateFile . '.sie';
            $server_file = 'e' . $newGenerateFile . '.sie';

            if (ftp_get($conn, $local_file, $server_file, FTP_BINARY)) {
                //echo "generado correctamente to $local_file\n";
            } else {
                echo "There was a problem\n :(";
            }


            $dir = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';
            /*
              //GET THE FILE
              $file = $dir . 'e' . $newGenerateFile . '.sie';
              $fch = fopen($file, "a+");
              $fileContent = $file = file_get_contents($file, true);
              //$texto = utf8_decode($fileContent);
              //$Result = base64_encode($fileContent);
              fwrite($fch, $fileContent); // Grabas
              fclose($fch);
             */
            exec('zip -P 3I35I3Client ' . $dir . $newGenerateFile . '.zip ' . $dir . 'e' . $newGenerateFile . '.sie');
            exec('mv ' . $dir . $newGenerateFile . '.zip ' . $dir . $newGenerateFile . '.igm ');
            ssh2_sftp_unlink($sftp, '/bajada_local/' . $server_file);
//system('rm -fr ' . $dir . $newGenerateFile . '.igm ');
            system('rm -fr ' . $dir . $server_file);
            ftp_close($conn);


//echo "done";
            $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);

            return $this->render($this->session->get('pathSystem') . ':MassiveTransfer:fileDownload.html.twig', array(
                        'uEducativa' => $objUe [0],
                        'file' => $newGenerateFile . '.igm', 'form' => $this->createFormToBuild($form['sie'], $form['gestion'], $form['bimestre'])->createView()
            ));
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            $em->getConnection()->rollback();
            $em->close();
            throw $e;
        }
    }

}
