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

class UpFileFtpZeroController extends Controller {

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
        return $this->render('SieRegularBundle:UpFileFtpZero:index.html.twig', array(
                    'form' => $this->lookforform()->createView()
        ));
    }

    private function lookforform() {
        return $this->createFormBuilder()
                        ->add('directroy', 'text', array('label' => 'Directorio', 'attr' => array('class' => 'form-control')))
                        ->add('find', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-default', 'onclick' => 'findFiles(this)')))
                        ->getForm();
    }

    public function seefilesAction(request $request) {

        $connection = ssh2_connect('172.20.0.103', 22);
        ssh2_auth_password($connection, 'root', 'ASDFqwe12.103');
        $sftp = ssh2_sftp($connection);

        //get the directory name
        $directory = '/' . $request->get('directory');
        if (($remoteStream = is_dir("ssh2.sftp://$sftp" . $directory))) {
            echo 'existe direcotry<br>';

            $stream = ssh2_exec($connection, 'find ' . $directory . ' size 0k');

            stream_set_blocking($stream, true);
            $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);

            $resultFiles = stream_get_contents($stream_out);
            echo '<pre>';
            print_r($resultFiles);
            echo '</pre>';
        }

        //print_r($directory);

        die;

        return $this->render('SieRegularBundle:UpFileFtpZero:empfilesperdepto1.html.twig', array(
                    'filesemp' => scandir($this->get('kernel')->getRootDir() . '/../web/ftpempfiles/' . $request->get('dep'), 1),
                    'depto' => $request->get('dep')
        ));
    }

}
