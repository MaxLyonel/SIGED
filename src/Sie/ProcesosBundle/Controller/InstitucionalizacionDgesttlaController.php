<?php

namespace Sie\ProcesosBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\InstitucionalizacionDgesttla;

class InstitucionalizacionDgesttlaController extends Controller
{
    public $session;
    public $iddep;
    public $idprov;
    public $idmun;
    public $idcan;
    public $idloc;
    public $iddis;
    public $tramiteTipoArray;
    public $nivelArray;
    
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->fechaActual = new \DateTime('now');
        $this->fechaCorte = new \DateTime('2021-01-09');
    }

    /***
     * Formulario de registro
     */
    public function indexAction(Request $request) {
        $form = $this->createRegistroNuevoForm();
        
        if($this->fechaActual > $this->fechaCorte) {
            $data = array(
                'form' => $form->createView(),
                'fin_inscripcion' => true
            );
        } else {
            $data = array(
                'form' => $form->createView(),
                'fin_inscripcion' => false
            );
        }

        return $this->render('SieProcesosBundle:InstitucionalizacionDgesttla:index.html.twig', $data);
    }

    public function createRegistroNuevoForm()
    {
        $form = $this->createFormBuilder();
        
        $form=$form
            ->setAction($this->generateUrl('institucionalizacion_new'))
            ->add('carnet','text',array('label'=>'Carnet de Identidad:','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '15', 'placeholder' => 'INGRESAR EL NRO DE C.I.')))
            ->add('complemento','text',array('label'=>'Complemento:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '2', 'placeholder' => '(OPCIONAL)')))
            ->add('paterno','text',array('label'=>'Apellido Paterno:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '100', 'placeholder' => 'INGRESAR EL APELLIDO PATERNO')))
            ->add('materno','text',array('label'=>'Apellido Materno:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '100', 'placeholder' => 'INGRESAR EL APELLIDO MATERNO')))
            ->add('nombre','text',array('label'=>'Nombre(s):','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '180', 'placeholder' => 'INGRESAR EL/LOS NOMBRE/S')))
            ->add('apellidoEsposo','text',array('label'=>'Apellido de casada:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '100', 'placeholder' => '(OPCIONAL)')))
            ->add('nacionalidad','text',array('label'=>'Nacionalidad:','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '100', 'placeholder' => 'INGRESAR LA NACIONALIDAD')))
            ->add('genero', 'choice', array('label' => 'Género:', 'required' => true, 'choices' => [1 => 'Masculino', 2 => 'Femenino', 3 => 'Otro']))
            ->add('fechaNacimiento', 'date', array('label' => 'Fecha de nacimiento:', 'widget' => 'single_text','format' => 'dd-mm-yyyy', 'required' => true, 'attr' => array('class' => 'form-control')))
            ->add('direccion', 'text', array('label' => 'Dirección actual:','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '200', 'placeholder' => 'INGRESAR LA DIRECCIÓN ACTUAL')))
            ->add('telefono', 'text', array('label' => 'Telefono fijo:','required'=>false,'attr' => array('class' => 'form-control', 'maxlength' => '10', 'placeholder' => 'INGRESAR EL TELÉFONO FIJO')))
            ->add('celular', 'text', array('label' => 'Telefono celular:','required'=>true,'attr' => array('class' => 'form-control', 'maxlength' => '10', 'placeholder' => 'INGRESAR EL TELÉFONO CELULAR')))
            ->add('correoElectronico', 'text', array('label' => 'Correo Electrónico:','required'=>true,'attr' => array('class' => 'form-control', 'maxlength' => '100', 'placeholder' => 'INGRESAR EL CORREO ELECTRÓNICO')))
            ->add('licenciatura', 'text', array('label' => 'Licenciatura en:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '200', 'placeholder' => 'INGRESAR SU LICENCIATURA (SI CORRESPONDE)')))
            ->add('tecnicoSuperior', 'text', array('label' => 'Técnico Superior en:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '200', 'placeholder' => 'INGRESAR SU TÉCNICO SUPERIOR (SI CORRESPONDE)')))
            ->add('diplomado', 'text', array('label' => 'Diplomado en:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '200', 'placeholder' => 'INGRESAR SU DIPLOMADO (SI CORRESPONDE)')))
            ->add('especialidad', 'text', array('label' => 'Especialidad en:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '200', 'placeholder' => 'INGRESAR SU ESPECIALIDAD (SI CORRESPONDE)')))
            ->add('maestria', 'text', array('label' => 'Maestría en:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '200', 'placeholder' => 'INGRESAR SU MAESTRÍA (SI CORRESPONDE)')))
            ->add('doctorado', 'text', array('label' => 'Doctorado en:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '200', 'placeholder' => 'INGRESAR SU DOCTORADO (SI CORRESPONDE)')))
            ->add('departamento', 'entity', array(
                'class' => 'Sie\AppWebBundle\Entity\InstitucionalizacionDepartamento',
                'mapped' => false,
                'required' => true,
                'property' => 'departamento',
                'label' => 'Departamento:',
                'empty_value' => 'Seleccionar...'
            ))
            ->add('instituto', 'choice', array('label' => 'Instituto', 'required' => true, 'choices' => [0 => 'Seleccionar...']))
            ->add('cargo', 'choice', array('label' => 'Cargo', 'required' => true, 'choices' => [0 => 'Seleccionar...']))
            ->add('nroDeposito','text',array('label'=>'Número de depósito bancario:','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '100', 'placeholder' => 'INGRESAR EL NÚMERO DE DEPÓSITO BANCARIO')))
            ->add('guardar','submit',array('label'=>'Enviar formulario'))
            ->getForm();

        return $form;
    }


    public function registroNuevoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $carnet = $form['carnet'];
        $complemento = $form['complemento'];
        $paterno = $form['paterno'];
        $materno = $form['materno'];
        $nombre = $form['nombre'];
        $apellidoEsposo = $form['apellidoEsposo'];
        $genero = $form['genero'];
        $fechaNacimiento = $form['fechaNacimiento'];
        $nacionalidad = $form['nacionalidad'];
        $direccion = $form['direccion'];
        $telefono = $form['telefono'] ? intval($form['telefono']) : 0;
        $celular = $form['celular'];
        $correoElectronico = $form['correoElectronico'];
        $licenciatura = $form['licenciatura'];
        $tecnicoSuperior = $form['tecnicoSuperior'];
        $especialidad = $form['especialidad'];
        $diplomado = $form['diplomado'];
        $maestria = $form['maestria'];
        $doctorado = $form['doctorado'];
        $departamento = $form['departamento'];
        $instituto = $form['instituto'];
        $cargo = $form['cargo'];
        $nroDeposito = $form['nroDeposito'];

        $persona = array(
            'complemento'=>$complemento,
            'primer_apellido'=>$paterno,
            'segundo_apellido'=>$materno,
            'nombre'=>$nombre,
            'fecha_nacimiento'=>$fechaNacimiento
        );

        $registro = $em->getRepository('SieAppWebBundle:InstitucionalizacionDgesttla')->findBy(array('carnet' => $carnet, 'complemento' => $complemento, 'esOficial' => true));
        
        if(count($registro)>=2) {
            $this->get('session')->getFlashBag()->add('error', 'Se encontraron 2 registros previos para la persona. No se realizó el registro.');
            return $this->redirectToRoute('institucionalizacion_index');
        } else {
            $resultado = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($carnet, $persona, 'prod', 'academico');
        
            if($resultado) {
                $em->getConnection()->beginTransaction();
                try{
                    $institucionalizacion = new InstitucionalizacionDgesttla();
                    $institucionalizacion->setFechaRegistro(new \DateTime('now'));
                    $institucionalizacion->setCarnet(mb_strtoupper($carnet, 'UTF-8'));
                    $institucionalizacion->setComplemento(mb_strtoupper($complemento, 'UTF-8'));
                    $institucionalizacion->setPaterno(mb_strtoupper($paterno, 'UTF-8'));
                    $institucionalizacion->setMaterno(mb_strtoupper($materno, 'UTF-8'));
                    $institucionalizacion->setNombre(mb_strtoupper($nombre, 'UTF-8'));
                    $institucionalizacion->setApellidoEsposo(mb_strtoupper($apellidoEsposo, 'UTF-8'));
                    $institucionalizacion->setGenero($genero);
                    $institucionalizacion->setFechaNacimiento(new \DateTime($fechaNacimiento));
                    $institucionalizacion->setNacionalidad(mb_strtoupper($nacionalidad, 'UTF-8'));
                    $institucionalizacion->setDireccion(mb_strtoupper($direccion, 'UTF-8'));
                    $institucionalizacion->setTelefono($telefono);
                    $institucionalizacion->setCelular($celular);
                    $institucionalizacion->setCorreoElectronico($correoElectronico);
                    $institucionalizacion->setLicenciatura(mb_strtoupper($licenciatura, 'UTF-8'));
                    $institucionalizacion->setTecnicoSuperior(mb_strtoupper($tecnicoSuperior, 'UTF-8'));
                    $institucionalizacion->setEspecialidad(mb_strtoupper($especialidad, 'UTF-8'));
                    $institucionalizacion->setDiplomado(mb_strtoupper($diplomado, 'UTF-8'));
                    $institucionalizacion->setMaestria(mb_strtoupper($maestria, 'UTF-8'));
                    $institucionalizacion->setDoctorado(mb_strtoupper($doctorado, 'UTF-8'));
                    $institucionalizacion->setDepartamento($departamento);
                    $institucionalizacion->setInstituto($instituto);
                    $institucionalizacion->setCargo($cargo);
                    $institucionalizacion->setNroDeposito(mb_strtoupper($nroDeposito, 'UTF-8'));
                    $institucionalizacion->setEsOficial(true);
                    
                    $em->persist($institucionalizacion);
                    $em->flush();
                    $em->getConnection()->commit();   

                    $this->get('session')->getFlashBag()->add('success', 'Los datos fueron registrados correctamente.');
                    return $this->redirectToRoute('institucionalizacion_index');
                    
                }catch (\Exception $ex) {
                    $em->getConnection()->rollback();
                    $this->get('session')->getFlashBag()->add('error', 'No se realizó el registro.'.$ex);
                    return $this->redirectToRoute('institucionalizacion_index');
                }
            } else {
                $this->get('session')->getFlashBag()->add('noSegip', 'Los datos no fueron validados con SEGIP. No se realizó el registro.');
                return $this->redirectToRoute('institucionalizacion_index');
            }
        }
    }

    public function institutosAction($idDepartamento){
        $em = $this->getDoctrine()->getManager();

        $departamento = $em->getRepository('SieAppWebBundle:InstitucionalizacionDepartamento')->findOneById($idDepartamento);
        $institutos = $em->getRepository('SieAppWebBundle:InstitucionalizacionInstituto')->findBy(array('departamento' => $departamento));

        $institutosArray = array();
        foreach($institutos as $itt) {
            $institutosArray[$itt->getId()] = $itt->getInstituto();
        }

        $response = new JsonResponse();
        return $response->setData(array('institutos' => $institutosArray));
    }

    public function cargosAction($idInstituto){
        $em = $this->getDoctrine()->getManager();

        $instituto = $em->getRepository('SieAppWebBundle:InstitucionalizacionInstituto')->findOneById($idInstituto);

        $entity = $em->getRepository('SieAppWebBundle:InstitucionalizacionInstitutoCargo');
        $query = $entity->createQueryBuilder('a')
            ->select('b')
            ->innerjoin('SieAppWebBundle:InstitucionalizacionCargo', 'b', 'WITH', 'a.cargo=b.id')
            ->where('a.instituto = :instituto')
            ->setParameter('instituto', $instituto)
            ->getQuery();

        $cargos = $query->getResult();

        $cargosArray = array();
        foreach($cargos as $cargo) {
            $cargosArray[$cargo->getId()] = $cargo->getCargo();
        }

        $response = new JsonResponse();
        return $response->setData(array('cargos' => $cargosArray));
    }

    public function indexPrintAction(Request $request) {
        $form = $this->createBuscarForm();
        $data = array(
            'form' => $form->createView(),
        );

        return $this->render('SieProcesosBundle:InstitucionalizacionDgesttla:index_print.html.twig', $data);
    }

    public function printInscAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $formulario = $request->get("form");
        
        $registro = $em->getRepository('SieAppWebBundle:InstitucionalizacionDgesttla')->findOneBy(array('carnet' => $formulario['carnet'], 'departamento' => $formulario['departamento'], 'instituto' => $formulario['instituto'], 'cargo' => $formulario['cargo']));
        
        if($registro) {
            $arch = 'DGESTTLA_INSTITUCIONALIZACION_' . $registro->getCarnet() . '_' . date('YmdHis') . '.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dgesttla_insc_Institucionalizacion_v2_afv.rptdesign&__format=pdf&&id='.$registro->getId().'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } else {
            $this->get('session')->getFlashBag()->add('error', 'No se encontró el registro. No es posible generar el formulario de inscripción.');
            return $this->redirectToRoute('institucionalizacion_index_print');
        }
    }

    public function createBuscarForm()
    {
        $form = $this->createFormBuilder();
        
        $form=$form
            ->setAction($this->generateUrl('institucionalizacion_print_insc'))
            ->add('carnet','text',array('label'=>'Carnet de Identidad:','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '15', 'placeholder' => 'INGRESAR EL NRO DE C.I.')))
            ->add('complemento','text',array('label'=>'Complemento:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase', 'maxlength' => '2', 'placeholder' => '(OPCIONAL)')))
            ->add('departamento', 'entity', array(
                'class' => 'Sie\AppWebBundle\Entity\InstitucionalizacionDepartamento',
                'mapped' => false,
                'required' => true,
                'property' => 'departamento',
                'label' => 'Departamento:',
                'empty_value' => 'Seleccionar...'
            ))
            ->add('instituto', 'choice', array('label' => 'Instituto', 'required' => true, 'choices' => [0 => 'Seleccionar...']))
            ->add('cargo', 'choice', array('label' => 'Cargo', 'required' => true, 'choices' => [0 => 'Seleccionar...']))            
            ->add('buscar','submit',array('label'=>'Generar'))
            ->getForm();

        return $form;
    }
}