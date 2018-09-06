<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAlternativa;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltHabla;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltOcupacion;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltAcceso;
use Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltTransporte;
use Sie\AppWebBundle\Form\EstudianteInscripcionSocioeconomicoAlternativaType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\Estudiante;

/**
 * SocioeconomicoAlternativa controller.
 *
 */
class EstudianteInscripcionSocioeconomicoAlternativaController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');

        $aInfoUeducativa = unserialize($infoUe);
        $aInfoStudent = json_decode($infoStudent, TRUE);

        $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfoId']['iecId']);
        $ieducativaId = $iec->getInstitucioneducativa()->getId();
        $gestion = $iec->getGestionTipo()->getId();

        $idInscripcion = $aInfoStudent['eInsId'];
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

        $estudiante = array(
            'codigoRude' => $aInfoStudent['codigoRude'],
            'estudiante' => $aInfoStudent['nombre'] . ' ' . $aInfoStudent['paterno'] . ' ' . $aInfoStudent['materno'],
            'estadoMatricula' => $inscripcion->getEstadomatriculaTipo()->getEstadomatricula()
        );

        //Información de la institución educativa
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

        $query = $repository->createQueryBuilder('i')
            ->select('i.id ieducativaId, i.institucioneducativa ieducativa, d.id distritoId, d.distrito distrito, dp.id departamentoId, dp.departamento departamento, de.dependencia dependencia, jg.cordx cordx, jg.cordy cordy, st.id sucId')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'isuc', 'WITH', 'isuc.institucioneducativa = i.id')
            ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'jg', 'WITH', 'i.leJuridicciongeografica = jg.id')
            ->innerJoin('SieAppWebBundle:DistritoTipo', 'd', 'WITH', 'jg.distritoTipo = d.id')
            ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dp', 'WITH', 'd.departamentoTipo = dp.id')
            ->innerJoin('SieAppWebBundle:DependenciaTipo', 'de', 'WITH', 'i.dependenciaTipo = de.id')
            ->innerJoin('SieAppWebBundle:SucursalTipo', 'st', 'WITH', 'isuc.sucursalTipo = st.id')
            ->where('i.id = :ieducativa')
            ->andWhere('isuc.id = :sucursal')
            ->setParameter('ieducativa', $ieducativaId)
            ->setParameter('sucursal', $this->session->get('ie_suc_id'))
            ->getQuery();

        $institucion = $query->getOneOrNullResult();

        //Información de la/el estudiante
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $aInfoStudent['codigoRude']));

        // $unidadmil= $em->getRepository('SieAppWebBundle:UnidadMilitar')->findOneBy(array('unidadMilitar'=>$form['seccioniiUnidadMilitar']));

        //
        //Datos Socioeconómicos
        $socioeconomico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAlternativa')->findOneBy(array('estudianteInscripcion' => $aInfoStudent['eInsId']));

        if ($socioeconomico) {

            //return $this->redirect($this->generateUrl('herramienta_alter_cursos_index'));
            return $this->render('SieHerramientaAlternativaBundle:SocioeconomicoAlternativa:edit.html.twig', array(
                'socioeconomico' => $socioeconomico,
                'institucion' => $institucion,
                'estudiante' => $estudiante,
                'student' => $student,
                'form' => $this->editForm($idInscripcion, $gestion, $socioeconomico)->createView(),
            ));
        } else {

            return $this->render('SieHerramientaAlternativaBundle:SocioeconomicoAlternativa:new.html.twig', array(
                'socioeconomico' => $socioeconomico,
                'institucion' => $institucion,
                'estudiante' => $estudiante,
                'student' => $student,
                'form' => $this->newForm($idInscripcion, $gestion)->createView(),
            ));
        }


    }

    /*
     * formulario de editar NUEVO rudeal
     */

    private function newForm($idInscripcion, $gestion) {
        $em = $this->getDoctrine()->getManager();

        $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $idInscripcion));
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' => $estudianteInscripcion->getEstudiante()));

        $paisNac = $estudiante->getPaisTipo();
        $dptoNac = $estudiante->getLugarNacTipo();
        $provNac = $estudiante->getLugarProvNacTipo();
        $localidadNac = $estudiante->getLocalidadNac();

        $pertUM="0";
        $nombreA="NINGUNO";
        $pertED="0";
        $pertP="0";
        $nombreP="NINGUNO";
        $cea="1";
        $umt="0";
        $pt="0";



        if($paisNac->getId() == 0 or $paisNac->getId() == null){
            $paisNac =  $em->getRepository('SieAppWebBundle:PaisTipo')->findOneBy(array('id' => 1));
        }


        //Lugar de Nacimiento
        $query = $em->createQuery(
            'SELECT p
                FROM SieAppWebBundle:PaisTipo p
                ORDER BY p.id');
        $paisNacE = $query->getResult();


        $paisNacArray = array();
        foreach ($paisNacE as $value) {
            $paisNacArray[$value->getId()] = $value->getPais();
        }

        $query = $em->createQuery(
            'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
            ->setParameter('nivel', 1)
            ->setParameter('lt1', $paisNac);
        $dptoNacE = $query->getResult();

        $dptoNacArray = array();
        foreach ($dptoNacE as $value) {
            $dptoNacArray[$value->getId()] = $value->getLugar();
        }

        $query = $em->createQuery(
            'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
            ->setParameter('nivel', 2)
            ->setParameter('lt1', $dptoNac);
        $provNacE = $query->getResult();

        $provNacArray = array();
        foreach ($provNacE as $value) {
            $provNacArray[$value->getId()] = $value->getLugar();
        }


        $civil = $em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findAll();
        $estadoCivilArray = array();
        foreach($civil as $value){
            $estadoCivilArray[$value->getId()] = $value->getEstadoCivil();
        }

        $unidadMilitar =$em->getRepository('SieAppWebBundle:UnidadMilitarTipo')->findBy(array(),array('unidadMilitar' => 'ASC'));

        $unidadMilitarArray=array();
        foreach($unidadMilitar as $value){
            $unidadMilitarArray[$value->getId()] = $value->getUnidadMilitar();
        }


        $unidadMilitarTipo =$em->getRepository('SieAppWebBundle:FuerzaMilitarTipo')->findAll();
        $unidadMilitarTipoArray=array();
        foreach($unidadMilitarTipo as $value){
            $unidadMilitarTipoArray[$value->getId()] = $value->getFuerzaMilitar();
        }
        //dump($unidadMilitarTipoArray);die;


        $educacionDiversa =$em->getRepository('SieAppWebBundle:EducacionDiversaTipo')->findBy(array(),array('id' => 'ASC'));
        $educacionDiversaArray=array();
        $EDEstudianteArray=array();
        foreach($educacionDiversa as $value){

            $educacionDiversaArray[$value->getId()] = $value->getEducacionDiversa();
            //dump($value->getId());die;

            if( $value->getId()==0||$value->getId()==5)
            {

            }else            {
                $EDEstudianteArray[$value->getId()]= $value->getEducacionDiversa();
            }


        }

        /*
        $educacionDiversa =$em->getRepository('SieAppWebBundle:EducacionDiversa')->findBy(array(),array('id' => 'ASC'));
        $educacionDiversaArray=array();
        foreach($educacionDiversa as $value){
            $educacionDiversaArray[$value->getId()] = $value->getEducacionDiversa();
        }*/
      //  dump($educacionDiversaArray);die;

        $penal =$em->getRepository('SieAppWebBundle:RecintoPenitenciarioTipo')->findBy(array(),array('recintoPenitenciario' => 'ASC'));
        $penalArray=array();
        foreach($penal as $value){
            $penalArray[$value->getId()] = $value->getRecintoPenitenciario();
        }

        $penalTipo =$em->getRepository('SieAppWebBundle:LugarReclusionTipo')->findAll();
        $penalTipoArray=array();
        foreach($penalTipo as $value){
            $penalTipoArray[$value->getId()] = $value->getLugarReclusion();
        }

        $genero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
        $generoArray = array();
        foreach($genero as $value){
            $generoArray[$value->getId()] = $value->getGenero();
        }

        $prov = array();
        $muni = array();
        $cantn = array();
        $locald = array();




        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('socioeconomicoalt_create'))
            ->add('estudianteInscripcion', 'hidden', array('data' => $idInscripcion))
            ->add('gestionId', 'hidden', array('data' => $gestion))
            //Datos del estudiante
            ->add('seccioniiPais', 'choice', array('data' => $paisNac ? $paisNac->getId() : 0, 'label' => 'Pais', 'required' => true, 'choices' => $paisNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccioniiDepartamento', 'choice', array('data' => $dptoNac ? $dptoNac->getId() : 0, 'label' => 'Departamento', 'required' => false, 'choices' => $dptoNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarProvinciasNac(this.value - 1);')))
            ->add('seccioniiProvincia', 'choice', array('data' => $provNac ? $provNac->getId() : 0, 'label' => 'Provincia', 'required' => false, 'choices' => $provNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccioniiLocalidad', 'text', array('data' => $localidadNac,'required' => false, 'attr' => array('class' => 'form-control')))
            ->add('seccioniiOficialia', 'text', array('data' => $estudiante->getOficialia(), 'required' => false, 'attr' => array('class' => 'form-control')))
            ->add('seccioniiLibro', 'text', array('data' => $estudiante->getLibro(), 'required' => false, 'attr' => array('class' => 'form-control')))
            ->add('seccioniiPartida', 'text', array('data' => $estudiante->getPartida(), 'required' => false, 'attr' => array('class' => 'form-control')))
            ->add('seccioniiFolio', 'text', array('data' => $estudiante->getFolio(), 'required' => false, 'attr' => array('class' => 'form-control')))
            ->add('seccioniiCivil', 'choice', array('data' => $estudiante->getEstadoCivil() ? $estudiante->getEstadoCivil()->getId() : 0, 'required' => false, 'choices' => $estadoCivilArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccioniiGenero', 'choice', array('data' => $estudiante->getGeneroTipo() ? $estudiante->getGeneroTipo()->getId() : 0, 'required' => false, 'choices' => $generoArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
           // ->add('seccioniiUnidadMilitar', 'choice', array('required' => false, 'choices' => $unidadMilitarArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
         //   ->add('seccioniiUnidadMilitarTipo', 'choice', array('required' => false, 'choices' => $unidadMilitarTipoArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccioniiEducacionDiversaTipo', 'choice', array('data'=> $pertED,'required' => false, 'choices' => $EDEstudianteArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarmenu(this.value)')))
            //end
            ->add('pertUM', 'hidden', array('data'=>$pertUM ) )
            ->add('pertP', 'hidden', array('data'=>$pertP ) )
            ->add('nombreA', 'hidden', array('data'=>$nombreA ) )
            ->add('nombreP', 'hidden', array('data'=>$nombreP ) )
            ->add('cea', 'hidden', array('data'=>$cea ) )
            ->add('umt', 'hidden', array('data'=>$umt ) )
            ->add('pt', 'hidden', array('data'=>$pt ) )

            ->add('seccioniiHijos', 'text', array('data' => 0, 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{1,2}', 'maxlength' => '2')))
            ->add('seccioniiEsserviciomilitar', 'choice', array('required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
           ->add('seccioniiEsserviciomilitarCea', 'choice', array('required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(true => 'El CEA', false => 'El Cuartel')))
            ->add('departamento', 'entity', array('label' => 'Departamento', 'required' => true, 'class' => 'SieAppWebBundle:DepartamentoTipo', 'property' => 'departamento', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarProvincias(this.value);')))
            ->add('provincia', 'choice', array('label' => 'Provincia', 'required' => true, 'choices' => $prov, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarMunicipios(this.value)')))
            ->add('municipio', 'choice', array('label' => 'Municipio', 'required' => true, 'choices' => $muni, 'empty_value' => 'Seleccionar...','attr' => array('class' => 'form-control', 'onchange' => 'listarCantones(this.value)')))
            ->add('canton', 'choice', array('label' => 'Cantón', 'required' => true, 'choices' => $cantn, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarLocalidades(this.value)')))
            ->add('localidad', 'choice', array('label' => 'Localidad', 'required' => true, 'choices' => $locald, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccioniiiZona', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('seccioniiiAvenida', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('seccioniiiNumero', 'text', array('data' => 0, 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{0,5}', 'maxlength' => '5')))
            ->add('seccioniiiTelefonofijo', 'text', array('required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{0,7}', 'maxlength' => '7')))
            ->add('seccioniiiTelefonocelular', 'text', array('required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{0,8}', 'maxlength' => '8')))
            ->add('seccionivEscarnetDiscapacidad', 'choice', array('required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
            ->add('seccionivNumeroCarnetDiscapacidad', 'text', array('required' => false, 'attr' => array('class' => 'form-control', 'maxlength' => '15')))
            ->add('seccionivDiscapacitadTipo', 'entity', array('label' => false, 'required' => false, 'class' => 'SieAppWebBundle:DiscapacidadTipo', 'property' => 'origendiscapacidad', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccionivGradoTipo', 'entity', array('label' => false, 'required' => false, 'class' => 'SieAppWebBundle:GradoDiscapacidadTipo', 'property' => 'grado_discapacidad', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccionivCarnetIbc', 'choice', array('required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
            ->add('seccionivNumeroCarnetIbc', 'text', array('required' => false, 'attr' => array('class' => 'form-control', 'maxlength' => '15')))
            ->add('seccionivEscegueratotal', 'choice', array('required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'Baja Visión', true => 'Ceguera Total')))
            ->add('seccionvIdioma1', 'entity', array('required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo', 'property' => 'habla', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccionvIdioma2', 'entity', array('required' => false, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo', 'property' => 'habla', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccionvIdioma3', 'entity', array('required' => false, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo', 'property' => 'habla', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccionvEstudianteEsnacionoriginaria', 'choice', array('required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
            ->add('seccionvEstudianteNacionoriginariaTipo', 'entity', array('label' => false, 'required' => false, 'class' => 'SieAppWebBundle:NacionOriginariaTipo', 'property' => 'nacion_originaria', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccionvEstudianteEsocupacion', 'choice', array('required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
            ->add('seccionvTrabaja', 'entity', array('multiple' => true, 'required' => false, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltOcupacionTipo', 'property' => 'ocupacion', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control js-example-basic-multiple', 'style'=>'width: 100%')))
            ->add('seccionvOtroTrabajo', 'text', array('required' => false, 'attr' => array('class' => 'form-control', 'maxlength' => '50')))
            ->add('seccionvEstudianteEsseguroSalud', 'choice', array('required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
            ->add('seccionvEstudianteSeguroSaludDonde', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('seccionvEstudianteGrupoSanguineoTipo', 'entity', array('label' => false, 'required' => false, 'class' => 'SieAppWebBundle:SangreTipo', 'property' => 'grupo_sanguineo', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccionvAcceso', 'entity', array('multiple' => true, 'label' => false, 'required' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltAccesoTipo', 'property' => 'acceso', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control js-example-basic-multiple', 'style'=>'width: 100%')))
            ->add('seccionvTransporte', 'entity', array('multiple' => true, 'label' => false, 'required' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltTransporteTipo', 'property' => 'transporte', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control js-example-basic-multiple', 'style'=>'width: 100%')))
            ->add('seccionvEstudianteDemoraLlegarCentroHoras', 'text', array('data' => 0, 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{1,2}', 'maxlength' => '2')))
            ->add('seccionvEstudianteDemoraLlegarCentroMinutos', 'text', array('data' => 0, 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{1,2}', 'maxlength' => '2')))
            ->add('seccionviModalidadEstudioTipo', 'entity', array('label' => false, 'required' => true, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltModalidadTipo', 'property' => 'modalidad', 'attr' => array('class' => 'form-control')))
            ->add('seccionviEstudiantePorqueInterrupcionservicios', 'textarea', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('lugar', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('fecha', 'date', array('format' => 'dd-MM-yyyy', 'data' => new \DateTime('now'), 'required' => false, 'attr' => array('class' => 'form-control')))
            ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        return $form;
    }

    /*
     * formulario de editar rudeal
     */

    private function editform($idInscripcion, $gestion, $socioeconomico) {
        
        $em = $this->getDoctrine()->getManager();

        $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $idInscripcion));
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' => $estudianteInscripcion->getEstudiante()));

        $paisNac = $estudiante->getPaisTipo();
        $dptoNac = $estudiante->getLugarNacTipo();
        $provNac = $estudiante->getLugarProvNacTipo();
        $localidadNac = $estudiante->getLocalidadNac();

        if($paisNac->getId() == 0 or $paisNac->getId() == null){
            $paisNac =  $em->getRepository('SieAppWebBundle:PaisTipo')->findOneBy(array('id' => 1));
        }

        $lt5_id = $socioeconomico->getSeccioniiiLocalidadTipo()->getLugarTipo();
        $lt4_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt5_id)->getLugarTipo();
        $lt3_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt4_id)->getLugarTipo();
        $lt2_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt3_id)->getLugarTipo();
        $lt1_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt2_id)->getLugarTipo();

        $l_id = $socioeconomico->getSeccioniiiLocalidadTipo()->getId();
        $c_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt5_id)->getId();
        $m_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt4_id)->getId();
        $p_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt3_id)->getId();
        $d_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt2_id)->getId();

        //Pertenece a una unidadMilitar devuelve el id
        if((  $unidad = $socioeconomico->getSeccioniiUnidadMilitarTipo())!= null)
        {
            $pertUM = $socioeconomico->getSeccioniiUnidadMilitarTipo()->getId();

            $em = $this->getDoctrine()->getManager();
            $unidadMilitarA =$em->getRepository('SieAppWebBundle:UnidadMilitarTipo')->findOneBy(array('id'=>$pertUM));
            $unidadMilitarTipo =$unidadMilitarA ->getFuerzaMilitarTipo();
            $nombreA=$unidadMilitarTipo->getFuerzaMilitar();
            $umt=$unidadMilitarTipo->getId();
        }

        else
        {
            $pertUM="0";
            $nombreA="NINGUNO";
            $umt="0";
        }


       // dump($unidadMilitarTipo);die;
        //Pertenece a educaciondiversa devuelve el id
        if((  $educadiv = $socioeconomico->getSeccioniiEducacionDiversaTipo())!= null)
        {
            $pertED = $socioeconomico->getSeccioniiEducacionDiversaTipo()->getId();
        }
        else
        {
            $pertED="0";
        }

        //Pertenece a un penal devuelve el id
        if((  $penal = $socioeconomico->getSeccioniiRecintoPenitenciarioTipo())!= null)
        {
            $pertP = $socioeconomico->getSeccioniiRecintoPenitenciarioTipo()->getId();

            $em = $this->getDoctrine()->getManager();
            $penalA =$em->getRepository('SieAppWebBundle:RecintoPenitenciarioTipo')->findOneBy(array('id'=>$pertP));
            $penalTipo =$penalA ->getLugarReclusionTipo();
            $nombreP=$penalTipo->getLugarReclusion();
            $pt=$penalTipo->getId();

        }
        else
        {
            $pertP="0";
            $nombreP="NINGUNO";
            $pt="0";
        }
        //dump($nombreP);die;


        if($cea = $socioeconomico->getSeccioniiEsserviciomilitarCea()== true)
        {
            $cea="1";
        }else
            {
                $cea="0";
            }

        //$cea = $socioeconomico->getSeccioniiEsserviciomilitarCea();
      //  dump($cea);die;

        //obetener datos especificos para tipificar al estudiante segun educacion diversa
        $educacionDiversa =$em->getRepository('SieAppWebBundle:EducacionDiversaTipo')->findBy(array(),array('id' => 'ASC'));
        $educacionDiversaArray=array();
        $EDEstudianteArray=array();
        foreach($educacionDiversa as $value){

            $educacionDiversaArray[$value->getId()] = $value->getEducacionDiversa();
            //dump($value->getId());die;

            if( $value->getId()==0||$value->getId()==5)
            {

            }else            {
                $EDEstudianteArray[$value->getId()]= $value->getEducacionDiversa();
            }


        }



       // dump($educacionDiversaArray);die;

        //Pertenece a una educacion diversa devuelve el id
       // $pertED = $socioeconomico-> getSeccioniiEducacionDiversa()->getId();

        $query = $em->createQuery(
            'SELECT p
                FROM SieAppWebBundle:PaisTipo p
                ORDER BY p.id');
        $paisNacE = $query->getResult();

        $paisNacArray = array();
        foreach ($paisNacE as $value) {
            $paisNacArray[$value->getId()] = $value->getPais();
        }

        $dpto = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();
        $dptoArray = array();
        foreach($dpto as $value){
            $dptoArray[$value->getId()] = $value->getDepartamento();
        }

        $civil = $em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findAll();
        $estadoCivilArray = array();
        foreach($civil as $value){
            $estadoCivilArray[$value->getId()] = $value->getEstadoCivil();
        }




        //listar el tipo de educacion
        /*
        $educacionDiversa =$em->getRepository('SieAppWebBundle:EducacionDiversa')->findBy(array(),array('id' => 'ASC'));

        $educacionDiversaArray=array();
        foreach($educacionDiversa as $value){
            $educacionDiversaArray[$value->getId()] = $value->getEducacionDiversa();
        }*/
//findBy(array(),array('name' => 'ASC'));
        $unidadMilitar =$em->getRepository('SieAppWebBundle:UnidadMilitarTipo')->findBy(array(),array('unidadMilitar' => 'ASC'));
        $unidadMilitarArray=array();
        foreach($unidadMilitar as $value){
            $unidadMilitarArray[$value->getId()] = $value->getUnidadMilitar();
        }



        $unidadMilitarTipo =$em->getRepository('SieAppWebBundle:FuerzaMilitarTipo')->findAll();
        $unidadMilitarTipoArray=array();
        foreach($unidadMilitarTipo as $value){
            $unidadMilitarTipoArray[$value->getId()] = $value->getFuerzaMilitar();
        }


        $penal =$em->getRepository('SieAppWebBundle:RecintoPenitenciarioTipo')->findBy(array(),array('recintoPenitenciario' => 'ASC'));
        $penalArray=array();
        foreach($penal as $value){
            $penalArray[$value->getId()] = $value->getRecintoPenitenciario();
        }


        $penalTipo =$em->getRepository('SieAppWebBundle:LugarReclusionTipo')->findAll();
        $penalTipoArray=array();
        foreach($penalTipo as $value){
            $penalTipoArray[$value->getId()] = $value->getLugarReclusion();
        }


        $genero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
        $generoArray = array();
        foreach($genero as $value){
            $generoArray[$value->getId()] = $value->getGenero();
        }


        $query = $em->createQuery(
            'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
            ->setParameter('nivel', 2)
            ->setParameter('lt1', $d_id);
        $prov = $query->getResult();

        $provArray = array();
        foreach ($prov as $value) {
            $provArray[$value->getId()] = $value->getLugar();
        }

        //Lugar de Nacimiento
        $query = $em->createQuery(
            'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
            ->setParameter('nivel', 1)
            ->setParameter('lt1', $paisNac);
        $dptoNacE = $query->getResult();

        $dptoNacArray = array();
        foreach ($dptoNacE as $value) {
            $dptoNacArray[$value->getId()] = $value->getLugar();
        }

        $query = $em->createQuery(
            'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
            ->setParameter('nivel', 2)
            ->setParameter('lt1', $dptoNac);
        $provNacE = $query->getResult();

        $provNacArray = array();
        foreach ($provNacE as $value) {
            $provNacArray[$value->getId()] = $value->getLugar();
        }
        //dump($provNacArray);die;

        $query = $em->createQuery(
            'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
            ->setParameter('nivel', 3)
            ->setParameter('lt1', $p_id);
        $muni = $query->getResult();

        $muniArray = array();
        foreach ($muni as $value) {
            $muniArray[$value->getId()] = $value->getLugar();
        }

        $query = $em->createQuery(
            'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
            ->setParameter('nivel', 4)
            ->setParameter('lt1', $m_id);
        $cantn = $query->getResult();

        $cantnArray = array();
        foreach ($cantn as $value) {
            $cantnArray[$value->getId()] = $value->getLugar();
        }

        $query = $em->createQuery(
            'SELECT lt
                FROM SieAppWebBundle:LugarTipo lt
                WHERE lt.lugarNivel = :nivel
                AND lt.lugarTipo = :lt1
                ORDER BY lt.id')
            ->setParameter('nivel', 5)
            ->setParameter('lt1', $c_id);
        $locald = $query->getResult();

        $localdArray = array();
        foreach ($locald as $value) {
            $localdArray[$value->getId()] = $value->getLugar();
        }





        /*idiomas*/
        $idiomas = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHabla')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $socioeconomico));

        $idiomasArray = array();
        $cont = 1;
        foreach ($idiomas as $value) {
            $idioma_aux = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo')->findOneById($value->getEstudianteInscripcionSocioeconomicoAltHablaTipo()->getId());
            $idiomasArray[$cont] = $idioma_aux->getId();
            $cont++;
        }

        switch(count($idiomasArray)){
            case 1: $idioma1 = $idiomasArray[1];
                $idioma2 = 0;
                $idioma3 = 0;
                break;
            case 2: $idioma1 = $idiomasArray[1];
                $idioma2 = $idiomasArray[2];
                $idioma3 = 0;
                break;
            case 3: $idioma1 = $idiomasArray[1];
                $idioma2 = $idiomasArray[2];
                $idioma3 = $idiomasArray[3];
                break;
        }

        $ocupacion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltOcupacion')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $socioeconomico));

        $ocupacionArray = array();
        foreach ($ocupacion as $value) {
            $ocupacion_aux = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltOcupacionTipo')->findOneById($value->getEstudianteInscripcionSocioeconomicoAltOcupacionTipo()->getId());
            $ocupacionArray[$ocupacion_aux->getId()] = $ocupacion_aux->getId();
        }

        $query = $em->createQuery(
            'SELECT o
                FROM SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltOcupacionTipo o
                WHERE o.id in (:ocu)')
            ->setParameter('ocu', $ocupacionArray);
        $ocupacionArray = $query->getResult();

        $acceso = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltAcceso')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $socioeconomico));

        $accesoArray = array();
        foreach ($acceso as $value) {
            $acceso_aux = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltAccesoTipo')->findOneById($value->getEstudianteInscripcionSocioeconomicoAltAccesoTipo()->getId());
            $accesoArray[$acceso_aux->getId()] = $acceso_aux->getId();
        }


        $query = $em->createQuery(
            'SELECT a
                FROM SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltAccesoTipo a
                WHERE a.id in (:acc)')
            ->setParameter('acc', $accesoArray);
        $accesoArray = $query->getResult();

        $transporte = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltTransporte')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $socioeconomico));

        $transporteArray = array();
        foreach ($transporte as $value) {
            $transporte_aux = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltTransporteTipo')->findOneById($value->getEstudianteInscripcionSocioeconomicoAltTransporteTipo()->getId());
            $transporteArray[$transporte_aux->getId()] = $transporte_aux->getId();
        }

        $query = $em->createQuery(
            'SELECT t
                FROM SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltTransporteTipo t
                WHERE t.id in (:tra)')
            ->setParameter('tra', $transporteArray);
        $transporteArray = $query->getResult();

        $modalidad = 1;
        if($socioeconomico->getSeccionviModalidadTipo() != null)
        {
            $modalidad = $socioeconomico->getSeccionviModalidadTipo()->getId();
        }

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('socioeconomicoalt_update'))
            ->add('estudianteInscripcion', 'hidden', array('data' => $idInscripcion))
            ->add('gestionId', 'hidden', array('data' => $gestion))
            ->add('socioeconomico', 'hidden', array('data' => $socioeconomico->getId()))
            //Datos del estudiante
            ->add('seccioniiPais', 'choice', array('data' => $paisNac ? $paisNac->getId() : 0, 'label' => 'Pais', 'required' => true, 'choices' => $paisNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccioniiDepartamento', 'choice', array('data' => $dptoNac ? $dptoNac->getId() : 0, 'label' => 'Departamento', 'required' => false, 'choices' => $dptoNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarProvinciasNac(this.value - 1);')))
            ->add('seccioniiProvincia', 'choice', array('data' => $provNac ? $provNac->getId() : 0, 'label' => 'Provincia', 'required' => false, 'choices' => $provNacArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccioniiLocalidad', 'text', array('data' => $localidadNac,'required' => false, 'attr' => array('class' => 'form-control')))
            ->add('seccioniiOficialia', 'text', array('data' => $estudiante->getOficialia(), 'required' => false, 'attr' => array('class' => 'form-control')))
            ->add('seccioniiLibro', 'text', array('data' => $estudiante->getLibro(), 'required' => false, 'attr' => array('class' => 'form-control')))
            ->add('seccioniiPartida', 'text', array('data' => $estudiante->getPartida(), 'required' => false, 'attr' => array('class' => 'form-control')))
            ->add('seccioniiFolio', 'text', array('data' => $estudiante->getFolio(), 'required' => false, 'attr' => array('class' => 'form-control')))
            ->add('seccioniiCivil', 'choice', array('data' => $estudiante->getEstadoCivil() ? $estudiante->getEstadoCivil()->getId() : 0, 'required' => false, 'choices' => $estadoCivilArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccioniiGenero', 'choice', array('data' => $estudiante->getGeneroTipo() ? $estudiante->getGeneroTipo()->getId() : 0, 'required' => false, 'choices' => $generoArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccioniiUnidadMilitarTipo', 'choice', array('data'=> $pertUM , 'required' => false, 'choices' => $unidadMilitarArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccioniiFuerzaMilitarTipo', 'text', array('data' => $nombreA , 'required' => false, 'attr' => array('class' => 'form-control','disabled' => true)))
            ->add('seccioniiEducacionDiversaTipo', 'choice', array('data'=> $pertED, 'required' => false, 'choices' => $EDEstudianteArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarmenu(this.value)')))
            ->add('pertUM', 'hidden', array('data'=>$pertUM ) )
            ->add('pertP', 'hidden', array('data'=>$pertP ) )
            ->add('nombreA', 'hidden', array('data'=>$nombreA ) )
            ->add('nombreP', 'hidden', array('data'=>$nombreP ) )
            ->add('cea', 'hidden', array('data'=>$cea ) )
            ->add('umt', 'hidden', array('data'=>$umt ) )
            ->add('pt', 'hidden', array('data'=>$pt ) )
            ->add('seccioniiRecintoPenitenciarioTipo', 'choice', array('data'=> $pertP , 'required' => false, 'choices' => $penalArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccioniiLugarReclusionTipo', 'text', array('data' => $nombreP , 'required' => false, 'attr' => array('class' => 'form-control','disabled' => true)))
            //end
            ->add('seccioniiHijos', 'text', array('data' => $socioeconomico->getSeccioniiHijos(), 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{1,2}', 'maxlength' => '2')))
            ->add('seccioniiEsserviciomilitar', 'choice', array('data' => $socioeconomico->getSeccioniiEsserviciomilitar(),'required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
            ->add('seccioniiEsserviciomilitarCea', 'choice', array('data' => $socioeconomico->getSeccioniiEsserviciomilitarCea(),'required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(true => 'El CEA', false => 'El Cuartel')))
            ->add('departamento', 'choice', array('data' => $d_id - 1, 'label' => 'Departamento', 'required' => true, 'choices' => $dptoArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarProvincias(this.value);')))
            ->add('provincia', 'choice', array('data' => $p_id, 'label' => 'Provincia', 'required' => true, 'choices' => $provArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarMunicipios(this.value)')))
            ->add('municipio', 'choice', array('data' => $m_id, 'label' => 'Municipio', 'required' => true, 'choices' => $muniArray, 'empty_value' => 'Seleccionar...','attr' => array('class' => 'form-control', 'onchange' => 'listarCantones(this.value)')))
            ->add('canton', 'choice', array('data' => $c_id, 'label' => 'Cantón', 'required' => true, 'choices' => $cantnArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'listarLocalidades(this.value)')))
            ->add('localidad', 'choice', array('data' => $l_id, 'label' => 'Localidad', 'required' => true, 'choices' => $localdArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccioniiiZona', 'text', array('data' => $socioeconomico->getSeccioniiiZona(), 'required' => true, 'attr' => array('class' => 'form-control')))
            ->add('seccioniiiAvenida', 'text', array('data' => $socioeconomico->getSeccioniiiAvenida(),'required' => true, 'attr' => array('class' => 'form-control')))
            ->add('seccioniiiNumero', 'text', array('data' => $socioeconomico->getSeccioniiiNumero(), 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{0,5}', 'maxlength' => '5')))
            ->add('seccioniiiTelefonofijo', 'text', array('data' => $socioeconomico->getSeccioniiiTelefonofijo(), 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{0,7}', 'maxlength' => '7')))
            ->add('seccioniiiTelefonocelular', 'text', array('data' => $socioeconomico->getSeccioniiiTelefonocelular(), 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{0,8}', 'maxlength' => '8')))
            ->add('seccionivEscarnetDiscapacidad', 'choice', array('data' => $socioeconomico->getSeccionivEscarnetDiscapacidad(), 'required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
            ->add('seccionivNumeroCarnetDiscapacidad', 'text', array('data' => $socioeconomico->getSeccionivNumeroCarnetDiscapacidad(), 'required' => false, 'attr' => array('class' => 'form-control', 'maxlength' => '15')))
            ->add('seccionivDiscapacitadTipo', 'entity', array('data' => $em->getReference('SieAppWebBundle:DiscapacidadTipo', $socioeconomico->getSeccionivDiscapacitadTipo()->getId()), 'label' => false, 'required' => false, 'class' => 'SieAppWebBundle:DiscapacidadTipo', 'property' => 'origendiscapacidad', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccionivGradoTipo', 'entity', array('data' => $em->getReference('SieAppWebBundle:GradoDiscapacidadTipo', $socioeconomico->getSeccionivGradoTipo()->getId()), 'label' => false, 'required' => false, 'class' => 'SieAppWebBundle:GradoDiscapacidadTipo', 'property' => 'grado_discapacidad', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccionivCarnetIbc', 'choice', array('data' => $socioeconomico->getSeccionivCarnetIbc(), 'required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
            ->add('seccionivNumeroCarnetIbc', 'text', array('data' => $socioeconomico->getSeccionivNumeroCarnetIbc(), 'required' => false, 'attr' => array('class' => 'form-control', 'maxlength' => '15')))
            ->add('seccionivEscegueratotal', 'choice', array('data' => $socioeconomico->getSeccionivEscegueratotal(), 'required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'Baja Visión', true => 'Ceguera Total')))
            ->add('seccionvIdioma1', 'entity', array('data' => $em->getReference('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo', $idioma1), 'required' => true, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo', 'property' => 'habla', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccionvIdioma2', 'entity', array('data' => $em->getReference('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo', $idioma2), 'required' => false, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo', 'property' => 'habla', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccionvIdioma3', 'entity', array('data' => $em->getReference('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo', $idioma3), 'required' => false, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo', 'property' => 'habla', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccionvEstudianteEsnacionoriginaria', 'choice', array('data' => $socioeconomico->getSeccionvEstudianteEsnacionoriginaria(), 'required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
            ->add('seccionvEstudianteNacionoriginariaTipo', 'entity', array('data' => $em->getReference('SieAppWebBundle:NacionOriginariaTipo', $socioeconomico->getSeccionvEstudianteNacionoriginariaTipo()->getId()), 'label' => false, 'required' => false, 'class' => 'SieAppWebBundle:NacionOriginariaTipo', 'property' => 'nacion_originaria', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccionvEstudianteEsocupacion', 'choice', array('data' => $socioeconomico->getSeccionvEstudianteEsocupacion(), 'required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
            ->add('seccionvTrabaja', 'entity', array('data' => $ocupacionArray, 'multiple' => true, 'required' => false, 'label' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltOcupacionTipo', 'property' => 'ocupacion', 'attr' => array('class' => 'form-control js-example-basic-multiple', 'style'=>'width: 100%')))
            ->add('seccionvOtroTrabajo', 'text', array('data' => $socioeconomico->getSeccionvOtroTrabajo(), 'required' => false, 'attr' => array('class' => 'form-control', 'maxlength' => '50')))
            ->add('seccionvEstudianteEsseguroSalud', 'choice', array('data' => $socioeconomico->getSeccionvEstudianteEsseguroSalud(), 'required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(false => 'NO', true => 'SI')))
            ->add('seccionvEstudianteSeguroSaludDonde', 'text', array('data' => $socioeconomico->getSeccionvEstudianteSeguroSaludDonde(), 'required' => false, 'attr' => array('class' => 'form-control')))
            ->add('seccionvEstudianteGrupoSanguineoTipo', 'entity', array('data' => $em->getReference('SieAppWebBundle:SangreTipo', $socioeconomico->getSeccionvEstudianteGrupoSanguineoTipo()->getId()), 'label' => false, 'required' => false, 'class' => 'SieAppWebBundle:SangreTipo', 'property' => 'grupo_sanguineo', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('seccionvAcceso', 'entity', array('data' => $accesoArray, 'multiple' => true, 'label' => false, 'required' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltAccesoTipo', 'property' => 'acceso', 'attr' => array('class' => 'form-control js-example-basic-multiple', 'style'=>'width: 100%')))
            ->add('seccionvTransporte', 'entity', array('data' => $transporteArray, 'multiple' => true, 'label' => false, 'required' => false, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltTransporteTipo', 'property' => 'transporte', 'attr' => array('class' => 'form-control js-example-basic-multiple', 'style'=>'width: 100%')))//js-example-basic-multiple
            ->add('seccionvEstudianteDemoraLlegarCentroHoras', 'text', array('data' => $socioeconomico->getSeccionvEstudianteDemoraLlegarCentroHoras(), 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{1,2}', 'maxlength' => '2')))
            ->add('seccionvEstudianteDemoraLlegarCentroMinutos', 'text', array('data' => $socioeconomico->getSeccionvEstudianteDemoraLlegarCentroMinutos(), 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{1,2}', 'maxlength' => '2')))
            ->add('seccionviModalidadEstudioTipo', 'entity', array('data' => $em->getReference('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltModalidadTipo', $modalidad), 'label' => false, 'required' => true, 'class' => 'SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltModalidadTipo', 'property' => 'modalidad', 'attr' => array('class' => 'form-control')))
            ->add('seccionviEstudiantePorqueInterrupcionservicios', 'textarea', array('data' => $socioeconomico->getSeccionviEstudiantePorqueInterrupcionservicios(), 'required' => false, 'attr' => array('class' => 'form-control')))
            ->add('lugar', 'text', array('data' => $socioeconomico->getLugar(), 'required' => true, 'attr' => array('class' => 'form-control')))
            ->add('fecha', 'date', array('format' => 'dd-MM-yyyy', 'data' => new \DateTime($socioeconomico->getFecha()->format('d-m-Y')), 'required' => false, 'attr' => array('class' => 'form-control')))

            ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'disbled' => true)))
            ->getForm();
         //
           // dump($umt);die;
       // dump($pertED);die;
        return $form;
    }

    /**
     * Creates a new SocioeconomicoAlternativa entity.
     *
     */
    public function createAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();





        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        try {
            $form = $request->get('form');
            $edid=$form['seccioniiEducacionDiversaTipo'];
            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $form['estudianteInscripcion']));
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' => $estudianteInscripcion->getEstudiante()));

            $estudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->findOneBy(array('id' => $form['seccioniiPais'])));
            $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['seccioniiDepartamento'] ? $form['seccioniiDepartamento'] : null)));
            $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['seccioniiProvincia'] ? $form['seccioniiProvincia'] : null)));
            $estudiante->setLocalidadNac($form['seccioniiLocalidad']);
            $estudiante->setOficialia($form['seccioniiOficialia']);
            $estudiante->setLibro($form['seccioniiLibro']);
            $estudiante->setPartida($form['seccioniiPartida']);
            $estudiante->setFolio($form['seccioniiFolio']);
            $estudiante->setEstadoCivil($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneBy(array('id' => $form['seccioniiCivil'])));
            $estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneBy(array('id' => $form['seccioniiGenero'])));
            $em->persist($estudiante);
            $em->flush();

            //dump($form['fecha']);die;
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_socioeconomico_alternativa');")->execute();

            $socioinscripcion = new EstudianteInscripcionSocioeconomicoAlternativa();
            $socioinscripcion->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($form['estudianteInscripcion']));
            $socioinscripcion->setSeccioniiHijos($form['seccioniiHijos'] ? $form['seccioniiHijos'] : 0);
         //   $socioinscripcion->setSeccioniiEsserviciomilitar($form['seccioniiEsserviciomilitar'] ? $form['seccioniiEsserviciomilitar'] : 0);
           // $socioinscripcion->setSeccioniiEsserviciomilitarCea($form['seccioniiEsserviciomilitarCea'] ? $form['seccioniiEsserviciomilitarCea'] : 0);
            $socioinscripcion->setSeccioniiiLocalidadTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['localidad']));
            $socioinscripcion->setSeccioniiiZona($form['seccioniiiZona'] ? mb_strtoupper($form['seccioniiiZona'], 'utf-8') : '');
            $socioinscripcion->setSeccioniiiAvenida($form['seccioniiiAvenida'] ? mb_strtoupper($form['seccioniiiAvenida'], 'utf-8') : '');
            $socioinscripcion->setSeccioniiiNumero($form['seccioniiiNumero'] ? $form['seccioniiiNumero'] : 0);
            $socioinscripcion->setSeccioniiiTelefonofijo($form['seccioniiiTelefonofijo'] ? $form['seccioniiiTelefonofijo'] : 0);
            $socioinscripcion->setSeccioniiiTelefonocelular($form['seccioniiiTelefonocelular'] ? $form['seccioniiiTelefonocelular'] : 0);
            $socioinscripcion->setSeccionivEscarnetDiscapacidad($form['seccionivEscarnetDiscapacidad'] ? $form['seccionivEscarnetDiscapacidad'] : 0);
            $socioinscripcion->setSeccionivNumeroCarnetDiscapacidad($form['seccionivNumeroCarnetDiscapacidad'] ? $form['seccionivNumeroCarnetDiscapacidad'] : 0);
            $socioinscripcion->setSeccionivDiscapacitadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->findOneById($form['seccionivDiscapacitadTipo'] ? $form['seccionivDiscapacitadTipo'] : 0));
            $socioinscripcion->setSeccionivGradoTipo($em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->findOneById($form['seccionivGradoTipo'] ? $form['seccionivGradoTipo'] : 0));
            $socioinscripcion->setSeccionivCarnetIbc($form['seccionivCarnetIbc'] ? $form['seccionivCarnetIbc'] : 0);
            $socioinscripcion->setSeccionivNumeroCarnetibc($form['seccionivNumeroCarnetIbc'] ? $form['seccionivNumeroCarnetIbc'] : 0);
            $socioinscripcion->setSeccionivEscegueratotal($form['seccionivEscegueratotal'] ? $form['seccionivEscegueratotal'] : 0);
            $socioinscripcion->setSeccionvEstudianteEsnacionoriginaria($form['seccionvEstudianteEsnacionoriginaria'] ? $form['seccionvEstudianteEsnacionoriginaria'] : 0);
            $socioinscripcion->setSeccionvEstudianteNacionoriginariaTipo($em->getRepository('SieAppWebBundle:NacionOriginariaTipo')->findOneById($form['seccionvEstudianteNacionoriginariaTipo'] ? $form['seccionvEstudianteNacionoriginariaTipo'] : 0));
            $socioinscripcion->setSeccionvEstudianteEsocupacion($form['seccionvEstudianteEsocupacion'] ? $form['seccionvEstudianteEsocupacion'] : 0);
            $socioinscripcion->setSeccionvEstudianteEsseguroSalud($form['seccionvEstudianteEsseguroSalud'] ? $form['seccionvEstudianteEsseguroSalud'] : 0);
            $socioinscripcion->setSeccionvEstudianteSeguroSaludDonde($form['seccionvEstudianteSeguroSaludDonde'] ? mb_strtoupper($form['seccionvEstudianteSeguroSaludDonde'], 'utf-8') : '');
            $socioinscripcion->setSeccionvEstudianteGrupoSanguineoTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById($form['seccionvEstudianteGrupoSanguineoTipo'] ? $form['seccionvEstudianteGrupoSanguineoTipo'] : 0));
            $socioinscripcion->setSeccionvEstudianteDemoraLlegarCentroHoras($form['seccionvEstudianteDemoraLlegarCentroHoras'] ? $form['seccionvEstudianteDemoraLlegarCentroHoras'] : 0);
            $socioinscripcion->setSeccionvEstudianteDemoraLlegarCentroMinutos($form['seccionvEstudianteDemoraLlegarCentroMinutos'] ? $form['seccionvEstudianteDemoraLlegarCentroMinutos'] : 0);
            $socioinscripcion->setSeccionviModalidadTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltModalidadTipo')->findOneById($form['seccionviModalidadEstudioTipo'] ? $form['seccionviModalidadEstudioTipo'] : 1));
            $socioinscripcion->setSeccionviEstudiantePorqueInterrupcionservicios($form['seccionviEstudiantePorqueInterrupcionservicios'] ? mb_strtoupper($form['seccionviEstudiantePorqueInterrupcionservicios'], 'utf-8') : '');
            //$socioinscripcion->setSeccioniiUnidadMilitar($em->getRepository('SieAppWebBundle:UnidadMilitar')->findOneById($form['seccioniiUnidadMilitar']));
            $socioinscripcion->setSeccioniiEducacionDiversaTipo($em->getRepository('SieAppWebBundle:EducacionDiversaTipo')->findOneById($form['seccioniiEducacionDiversaTipo']));
          //  $socioinscripcion->setSeccioniiPenal($em->getRepository('SieAppWebBundle:Penal')->findOneById($form['seccioniiPenal']));
            $socioinscripcion->setLugar($form['lugar'] ? mb_strtoupper($form['lugar'], 'utf-8') : '');
            $socioinscripcion->setSeccionvOtroTrabajo($form['seccionvOtroTrabajo'] ? mb_strtoupper($form['seccionvOtroTrabajo'], 'utf-8') : '');
            $socioinscripcion->setFecha(new \DateTime($form['fecha']['year'].'-'.$form['fecha']['month'].'-'.$form['fecha']['day']));
            $socioinscripcion->setFechaRegistro(new \DateTime('now'));
            $socioinscripcion->setFechaModificacion(new \DateTime('now'));
            //
            if($edid==2)
            {
                $socioinscripcion->setSeccioniiEsserviciomilitar(true);
                $socioinscripcion->setSeccioniiEsserviciomilitarCea($form['seccioniiEsserviciomilitarCea'] ? $form['seccioniiEsserviciomilitarCea'] : 0);
                $socioinscripcion->setSeccioniiUnidadMilitarTipo($em->getRepository('SieAppWebBundle:UnidadMilitarTipo')->find($form['seccioniiUnidadMilitarTipo']));
                $socioinscripcion->setSeccioniiRecintoPenitenciarioTipo($em->getRepository('SieAppWebBundle:RecintoPenitenciarioTipo')->find(0));
                //   dump($socioinscripcion);die;

            }elseif ($edid==3)
            {
                $socioinscripcion->setSeccioniiEsserviciomilitarCea(false);
                $socioinscripcion->setSeccioniiEsserviciomilitar(false);
                $socioinscripcion->setSeccioniiRecintoPenitenciarioTipo($em->getRepository('SieAppWebBundle:RecintoPenitenciarioTipo')->find($form['seccioniiRecintoPenitenciarioTipo']));
                $socioinscripcion->setSeccioniiUnidadMilitarTipo($em->getRepository('SieAppWebBundle:UnidadMilitarTipo')->find(0));

            }elseif($edid==1 || $edid==4 )
            {
                $socioinscripcion->setSeccioniiEsserviciomilitarCea(false);
                $socioinscripcion->setSeccioniiEsserviciomilitar(false);
                $socioinscripcion->setSeccioniiRecintoPenitenciarioTipo($em->getRepository('SieAppWebBundle:RecintoPenitenciarioTipo')->find(0));
                $socioinscripcion->setSeccioniiUnidadMilitarTipo($em->getRepository('SieAppWebBundle:UnidadMilitarTipo')->find(0));
            }else{

            }


            $em->persist($socioinscripcion);
            $em->flush();

            /*Registro de idiomas*/
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_socioeconomico_alt_habla');")->execute();
            $socioinscripcionhabla = new EstudianteInscripcionSocioeconomicoAltHabla();
            if($form['seccionvIdioma1']){
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoAlternativa($socioinscripcion);
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoAltHablaTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo')->findOneById($form['seccionvIdioma1'] ? $form['seccionvIdioma1'] : 0));
                $em->persist($socioinscripcionhabla);
                $em->flush();
            }

            $socioinscripcionhabla = new EstudianteInscripcionSocioeconomicoAltHabla();
            if($form['seccionvIdioma2']){
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoAlternativa($socioinscripcion);
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoAltHablaTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo')->findOneById($form['seccionvIdioma2'] ? $form['seccionvIdioma2'] : 0));
                $em->persist($socioinscripcionhabla);
                $em->flush();
            }

            $socioinscripcionhabla = new EstudianteInscripcionSocioeconomicoAltHabla();
            if($form['seccionvIdioma3']){
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoAlternativa($socioinscripcion);
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoAltHablaTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo')->findOneById($form['seccionvIdioma3'] ? $form['seccionvIdioma3'] : 0));
                $em->persist($socioinscripcionhabla);
                $em->flush();
            }

            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_socioeconomico_alt_ocupacion');")->execute();
            if ($form['seccionvEstudianteEsocupacion'] == '1'){
                if($form['seccionvTrabaja']){
                    $seccionvTrabaja = $form['seccionvTrabaja'];
                    foreach ($seccionvTrabaja as $value) {
                        $socioinscripcionocupacion = new EstudianteInscripcionSocioeconomicoAltOcupacion();
                        $socioinscripcionocupacion->setEstudianteInscripcionSocioeconomicoAlternativa($socioinscripcion);
                        $socioinscripcionocupacion->setEstudianteInscripcionSocioeconomicoAltOcupacionTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltOcupacionTipo')->find($value));
                        $em->persist($socioinscripcionocupacion);
                        $em->flush();
                    }
                }
            } else {
                $socioinscripcionocupacion = new EstudianteInscripcionSocioeconomicoAltOcupacion();
                $socioinscripcionocupacion->setEstudianteInscripcionSocioeconomicoAlternativa($socioinscripcion);
                $socioinscripcionocupacion->setEstudianteInscripcionSocioeconomicoAltOcupacionTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltOcupacionTipo')->find('0'));
                $em->persist($socioinscripcionocupacion);
                $em->flush();
            }
            /*Registro de Acceso*/
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_socioeconomico_alt_acceso');")->execute();
            if(isset($form['seccionvAcceso'])){
                if($form['seccionvAcceso']){
                    $seccionvAcceso = $form['seccionvAcceso'];

                    foreach ($seccionvAcceso as $value) {
                        $socioinscripcionacceso = new EstudianteInscripcionSocioeconomicoAltAcceso();
                        $socioinscripcionacceso->setEstudianteInscripcionSocioeconomicoAlternativa($socioinscripcion);
                        $socioinscripcionacceso->setEstudianteInscripcionSocioeconomicoAltAccesoTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltAccesoTipo')->find($value));
                        $em->persist($socioinscripcionacceso);
                        $em->flush();
                    }
                }
            }

            /*Registro de Transporte*/
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_socioeconomico_alt_transporte');")->execute();
            if(isset($form['seccionvTransporte'])){
                $seccionvTransporte= $form['seccionvTransporte'];

                foreach ($seccionvTransporte as $value) {
                    $socioinscripciontransporte = new EstudianteInscripcionSocioeconomicoAltTransporte();
                    $socioinscripciontransporte->setEstudianteInscripcionSocioeconomicoAlternativa($socioinscripcion);
                    $socioinscripciontransporte->setEstudianteInscripcionSocioeconomicoAltTransporteTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltTransporteTipo')->find($value));
                    $em->persist($socioinscripciontransporte);
                    $em->flush();
                }
            }

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente.');
            return $this->redirect($this->generateUrl('herramienta_alter_cursos_index'));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron registrados.');
            return $this->redirect($this->generateUrl('herramienta_alter_cursos_index'));
        }


    }

    /**
     * Edits an existing SocioeconomicoAlternativa entity.
     *
     */
    public function updateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        try {
            $form = $request->get('form');
            //$formB = $request->get('formlista');
            //dump($formB);die;
            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $form['estudianteInscripcion']));
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' => $estudianteInscripcion->getEstudiante()));

            $estudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->findOneBy(array('id' => $form['seccioniiPais'])));
            $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['seccioniiDepartamento'] ? $form['seccioniiDepartamento'] : null)));
            $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id' => $form['seccioniiProvincia'] ? $form['seccioniiProvincia'] : null)));
            $estudiante->setLocalidadNac($form['seccioniiLocalidad']);
            $estudiante->setOficialia($form['seccioniiOficialia']);
            $estudiante->setLibro($form['seccioniiLibro']);
            $estudiante->setPartida($form['seccioniiPartida']);
            $estudiante->setFolio($form['seccioniiFolio']);
            $estudiante->setEstadoCivil($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneBy(array('id' => $form['seccioniiCivil'])));
            $estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneBy(array('id' => $form['seccioniiGenero'])));
            $em->persist($estudiante);
            $em->flush();

            $socioId = $form['socioeconomico'];
            $edid=$form['seccioniiEducacionDiversaTipo'];
            //dump($edid);die;
            $socioinscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAlternativa')->findOneById($socioId);
           //COndicional para el guardado



            if($edid==2)
            {
                $socioinscripcion->setSeccioniiEsserviciomilitar(true);
                $socioinscripcion->setSeccioniiEsserviciomilitarCea($form['seccioniiEsserviciomilitarCea'] ? $form['seccioniiEsserviciomilitarCea'] : 0);
                $socioinscripcion->setSeccioniiUnidadMilitarTipo($em->getRepository('SieAppWebBundle:UnidadMilitarTipo')->find($form['seccioniiUnidadMilitarTipo']));
                $socioinscripcion->setSeccioniiRecintoPenitenciarioTipo($em->getRepository('SieAppWebBundle:RecintoPenitenciarioTipo')->find(0));
                $socioinscripcion->setSeccioniiEducacionDiversaTipo($em->getRepository('SieAppWebBundle:EducacionDiversaTipo')->findOneById($form['seccioniiEducacionDiversaTipo']));

                //   dump($socioinscripcion);die;

            }elseif ($edid==3)
            {
                $socioinscripcion->setSeccioniiEsserviciomilitarCea(true);
                $socioinscripcion->setSeccioniiEsserviciomilitar(false);
                $socioinscripcion->setSeccioniiRecintoPenitenciarioTipo($em->getRepository('SieAppWebBundle:RecintoPenitenciarioTipo')->find($form['seccioniiRecintoPenitenciarioTipo']));
                $socioinscripcion->setSeccioniiUnidadMilitarTipo($em->getRepository('SieAppWebBundle:UnidadMilitarTipo')->find(0));
                $socioinscripcion->setSeccioniiEducacionDiversaTipo($em->getRepository('SieAppWebBundle:EducacionDiversaTipo')->findOneById($form['seccioniiEducacionDiversaTipo']));

            }elseif($edid==1 || $edid==4 )
            {
                $socioinscripcion->setSeccioniiEsserviciomilitarCea(true);
                $socioinscripcion->setSeccioniiEsserviciomilitar(false);
                $socioinscripcion->setSeccioniiRecintoPenitenciarioTipo($em->getRepository('SieAppWebBundle:RecintoPenitenciarioTipo')->find(0));
                $socioinscripcion->setSeccioniiEducacionDiversaTipo($em->getRepository('SieAppWebBundle:EducacionDiversaTipo')->findOneById($form['seccioniiEducacionDiversaTipo']));
                $socioinscripcion->setSeccioniiUnidadMilitarTipo($em->getRepository('SieAppWebBundle:UnidadMilitarTipo')->find(0));
            }else{

            }

            $socioinscripcion->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($form['estudianteInscripcion']));
            $socioinscripcion->setSeccioniiHijos($form['seccioniiHijos'] ? $form['seccioniiHijos'] : 0);



            $socioinscripcion->setSeccioniiiLocalidadTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['localidad']));
            $socioinscripcion->setSeccioniiiZona($form['seccioniiiZona'] ? mb_strtoupper($form['seccioniiiZona'], 'utf-8') : '');
            $socioinscripcion->setSeccioniiiAvenida($form['seccioniiiAvenida'] ? mb_strtoupper($form['seccioniiiAvenida'], 'utf-8') : '');
            $socioinscripcion->setSeccioniiiNumero($form['seccioniiiNumero'] ? $form['seccioniiiNumero'] : 0);
            $socioinscripcion->setSeccioniiiTelefonofijo($form['seccioniiiTelefonofijo'] ? $form['seccioniiiTelefonofijo'] : 0);
            $socioinscripcion->setSeccioniiiTelefonocelular($form['seccioniiiTelefonocelular'] ? $form['seccioniiiTelefonocelular'] : 0);
            $socioinscripcion->setSeccionivEscarnetDiscapacidad($form['seccionivEscarnetDiscapacidad'] ? $form['seccionivEscarnetDiscapacidad'] : 0);
            $socioinscripcion->setSeccionivNumeroCarnetDiscapacidad($form['seccionivNumeroCarnetDiscapacidad'] ? $form['seccionivNumeroCarnetDiscapacidad'] : 0);
            $socioinscripcion->setSeccionivDiscapacitadTipo($em->getRepository('SieAppWebBundle:DiscapacidadTipo')->findOneById($form['seccionivDiscapacitadTipo'] ? $form['seccionivDiscapacitadTipo'] : 0));
            $socioinscripcion->setSeccionivGradoTipo($em->getRepository('SieAppWebBundle:GradoDiscapacidadTipo')->findOneById($form['seccionivGradoTipo'] ? $form['seccionivGradoTipo'] : 0));
            $socioinscripcion->setSeccionivCarnetIbc($form['seccionivCarnetIbc'] ? $form['seccionivCarnetIbc'] : 0);
            $socioinscripcion->setSeccionivNumeroCarnetibc($form['seccionivNumeroCarnetIbc'] ? $form['seccionivNumeroCarnetIbc'] : 0);
            $socioinscripcion->setSeccionivEscegueratotal($form['seccionivEscegueratotal'] ? $form['seccionivEscegueratotal'] : 0);
            $socioinscripcion->setSeccionvEstudianteEsnacionoriginaria($form['seccionvEstudianteEsnacionoriginaria'] ? $form['seccionvEstudianteEsnacionoriginaria'] : 0);
            $socioinscripcion->setSeccionvEstudianteNacionoriginariaTipo($em->getRepository('SieAppWebBundle:NacionOriginariaTipo')->findOneById($form['seccionvEstudianteNacionoriginariaTipo'] ? $form['seccionvEstudianteNacionoriginariaTipo'] : 0));
            $socioinscripcion->setSeccionvEstudianteEsocupacion($form['seccionvEstudianteEsocupacion'] ? $form['seccionvEstudianteEsocupacion'] : 0);
            $socioinscripcion->setSeccionvEstudianteEsseguroSalud($form['seccionvEstudianteEsseguroSalud'] ? $form['seccionvEstudianteEsseguroSalud'] : 0);
            $socioinscripcion->setSeccionvEstudianteSeguroSaludDonde($form['seccionvEstudianteSeguroSaludDonde'] ? mb_strtoupper($form['seccionvEstudianteSeguroSaludDonde'], 'utf-8') : '');
            $socioinscripcion->setSeccionvEstudianteGrupoSanguineoTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById($form['seccionvEstudianteGrupoSanguineoTipo'] ? $form['seccionvEstudianteGrupoSanguineoTipo'] : 0));
            $socioinscripcion->setSeccionvEstudianteDemoraLlegarCentroHoras($form['seccionvEstudianteDemoraLlegarCentroHoras'] ? $form['seccionvEstudianteDemoraLlegarCentroHoras'] : 0);
            $socioinscripcion->setSeccionvEstudianteDemoraLlegarCentroMinutos($form['seccionvEstudianteDemoraLlegarCentroMinutos'] ? $form['seccionvEstudianteDemoraLlegarCentroMinutos'] : 0);
            $socioinscripcion->setSeccionviModalidadTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltModalidadTipo')->findOneById($form['seccionviModalidadEstudioTipo'] ? $form['seccionviModalidadEstudioTipo'] : 1));
            $socioinscripcion->setSeccionviEstudiantePorqueInterrupcionservicios($form['seccionviEstudiantePorqueInterrupcionservicios'] ? mb_strtoupper($form['seccionviEstudiantePorqueInterrupcionservicios'], 'utf-8') : '');
            $socioinscripcion->setLugar($form['lugar'] ? mb_strtoupper($form['lugar'], 'utf-8') : '');
            $socioinscripcion->setSeccionvOtroTrabajo($form['seccionvOtroTrabajo'] ? mb_strtoupper($form['seccionvOtroTrabajo'], 'utf-8') : '');
            $socioinscripcion->setFecha(new \DateTime($form['fecha']['year'].'-'.$form['fecha']['month'].'-'.$form['fecha']['day']));
            $socioinscripcion->setFechaRegistro(new \DateTime('now'));
            $socioinscripcion->setFechaModificacion(new \DateTime('now'));
            $em->persist($socioinscripcion);
            $em->flush();

            /*eliminar idiomas*/
            $idiomas = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHabla')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $socioinscripcion));

            foreach ($idiomas as $value) {
                $em->remove($value);
            }
            $em->flush();

            /*Registro de idiomas*/
            $socioinscripcionhabla = new EstudianteInscripcionSocioeconomicoAltHabla();
            if($form['seccionvIdioma1']){
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoAlternativa($socioinscripcion);
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoAltHablaTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo')->findOneById($form['seccionvIdioma1'] ? $form['seccionvIdioma1'] : 0));
                $em->persist($socioinscripcionhabla);
                $em->flush();
            }

            $socioinscripcionhabla = new EstudianteInscripcionSocioeconomicoAltHabla();
            if($form['seccionvIdioma2']){
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoAlternativa($socioinscripcion);
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoAltHablaTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo')->findOneById($form['seccionvIdioma2'] ? $form['seccionvIdioma2'] : 0));
                $em->persist($socioinscripcionhabla);
                $em->flush();
            }

            $socioinscripcionhabla = new EstudianteInscripcionSocioeconomicoAltHabla();
            if($form['seccionvIdioma3']){
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoAlternativa($socioinscripcion);
                $socioinscripcionhabla->setEstudianteInscripcionSocioeconomicoAltHablaTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHablaTipo')->findOneById($form['seccionvIdioma3'] ? $form['seccionvIdioma3'] : 0));
                $em->persist($socioinscripcionhabla);
                $em->flush();
            }

            /*eliminar ocupacion*/
            $ocupacion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltOcupacion')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $socioinscripcion));

            foreach ($ocupacion as $value) {
                $em->remove($value);
            }
            $em->flush();

            /*Registro de Ocupación*/
            if ($form['seccionvEstudianteEsocupacion'] == '1'){
                if($form['seccionvTrabaja']){
                    $seccionvTrabaja = $form['seccionvTrabaja'];
                    foreach ($seccionvTrabaja as $value) {
                        $socioinscripcionocupacion = new EstudianteInscripcionSocioeconomicoAltOcupacion();
                        $socioinscripcionocupacion->setEstudianteInscripcionSocioeconomicoAlternativa($socioinscripcion);
                        $socioinscripcionocupacion->setEstudianteInscripcionSocioeconomicoAltOcupacionTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltOcupacionTipo')->find($value));
                        $em->persist($socioinscripcionocupacion);
                        $em->flush();
                    }
                }
            } else {
                $socioinscripcionocupacion = new EstudianteInscripcionSocioeconomicoAltOcupacion();
                $socioinscripcionocupacion->setEstudianteInscripcionSocioeconomicoAlternativa($socioinscripcion);
                $socioinscripcionocupacion->setEstudianteInscripcionSocioeconomicoAltOcupacionTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltOcupacionTipo')->find('0'));
                $em->persist($socioinscripcionocupacion);
                $em->flush();
            }

            /*eliminar acceso*/
            $acceso = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltAcceso')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $socioinscripcion));

            foreach ($acceso as $value) {
                $em->remove($value);
            }
            $em->flush();
            if(isset($form['seccionvAcceso'])){
                if($form['seccionvAcceso']){
                    $seccionvAcceso = $form['seccionvAcceso'];

                    foreach ($seccionvAcceso as $value) {
                        $socioinscripcionacceso = new EstudianteInscripcionSocioeconomicoAltAcceso();
                        $socioinscripcionacceso->setEstudianteInscripcionSocioeconomicoAlternativa($socioinscripcion);
                        $socioinscripcionacceso->setEstudianteInscripcionSocioeconomicoAltAccesoTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltAccesoTipo')->find($value));
                        $em->persist($socioinscripcionacceso);
                        $em->flush();
                    }
                }
            }


            /*eliminar transporte*/
            $transporte = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltTransporte')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $socioinscripcion));

            foreach ($transporte as $value) {
                $em->remove($value);
            }
            $em->flush();

            /*Registro de Transporte*/
            if(isset($form['seccionvTransporte'])){
                $seccionvTransporte= $form['seccionvTransporte'];

                foreach ($seccionvTransporte as $value) {
                    $socioinscripciontransporte = new EstudianteInscripcionSocioeconomicoAltTransporte();
                    $socioinscripciontransporte->setEstudianteInscripcionSocioeconomicoAlternativa($socioinscripcion);
                    $socioinscripciontransporte->setEstudianteInscripcionSocioeconomicoAltTransporteTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltTransporteTipo')->find($value));
                    $em->persist($socioinscripciontransporte);
                    $em->flush();
                }
            }

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron actualizados correctamente.');
            return $this->redirect($this->generateUrl('herramienta_alter_cursos_index'));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Los datos no fueron actualizados.');
            return $this->redirect($this->generateUrl('herramienta_alter_cursos_index'));
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarprovinciasAction($dpto) {
        try {
            $em = $this->getDoctrine()->getManager();


            $query = $em->createQuery(
                'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                ->setParameter('nivel', 2)
                ->setParameter('lt1', $dpto + 1);
            $provincias = $query->getResult();

            $provinciasArray = array();
            foreach ($provincias as $c) {
                $provinciasArray[$c->getId()] = $c->getLugar();
            }

            $response = new JsonResponse();
            return $response->setData(array('listaprovincias' => $provinciasArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarmunicipiosAction($prov) {
        try {
            $em = $this->getDoctrine()->getManager();


            $query = $em->createQuery(
                'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                ->setParameter('nivel', 3)
                ->setParameter('lt1', $prov);
            $municipios = $query->getResult();

            $municipiosArray = array();
            foreach ($municipios as $c) {
                $municipiosArray[$c->getId()] = $c->getLugar();
            }

            $response = new JsonResponse();
            return $response->setData(array('listamunicipios' => $municipiosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarcantonesAction($muni) {
        try {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQuery(
                'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                ->setParameter('nivel', 4)
                ->setParameter('lt1', $muni);
            $cantones = $query->getResult();

            $cantonesArray = array();
            foreach ($cantones as $c) {
                $cantonesArray[$c->getId()] = $c->getLugar();
            }

            $response = new JsonResponse();
            return $response->setData(array('listacantones' => $cantonesArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */
    public function listarlocalidadesAction($cantn) {
        try {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQuery(
                'SELECT lt
                    FROM SieAppWebBundle:LugarTipo lt
                    WHERE lt.lugarNivel = :nivel
                    AND lt.lugarTipo = :lt1
                    ORDER BY lt.id')
                ->setParameter('nivel', 5)
                ->setParameter('lt1', $cantn);
            $localidades = $query->getResult();

            $localidadesArray = array();
            foreach ($localidades as $c) {
                $localidadesArray[$c->getId()] = $c->getLugar();
            }

            $response = new JsonResponse();
            return $response->setData(array('listalocalidades' => $localidadesArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }



    public function listarmenuAction($idM, $pertUM, $pertP,$cea,$umt,$pt) {
        try {
            $idMArray = array ('id'=>$idM);
            $em = $this->getDoctrine()->getManager();
            $unidadMilitar =$em->getRepository('SieAppWebBundle:UnidadMilitarTipo')->findBy(array(),array('unidadMilitar' => 'ASC'));
            $unidadMilitarArray=array();
            foreach($unidadMilitar as $value){
                $unidadMilitarArray[$value->getId()] = $value->getUnidadMilitar();
            }

            $penal =$em->getRepository('SieAppWebBundle:RecintoPenitenciarioTipo')->findBy(array(),array('recintoPenitenciario' => 'ASC'));
            $penalArray=array();
            foreach($penal as $value){
                $penalArray[$value->getId()] = $value->getRecintoPenitenciario();
            }

            $em = $this->getDoctrine()->getManager();

            $penalTipo =$em->getRepository('SieAppWebBundle:LugarReclusionTipo')->findAll();
            $penalTipoArray=array();
            foreach($penalTipo as $value){

                $penalTipoArray[$value->getId()] = $value->getLugarReclusion();
                if( $value->getId()==0)
                {

                }else            {
                    $listaPenalArray[$value->getId()]= $value->getLugarReclusion();
                }

            }
            $unidadMilitarTipo =$em->getRepository('SieAppWebBundle:FuerzaMilitarTipo')->findAll();
            $unidadMilitarTipoArray=array();
            foreach($unidadMilitarTipo as $value){
                $unidadMilitarTipoArray[$value->getId()] = $value->getFuerzaMilitar();
                if( $value->getId()==0)
                {

                }else            {
                    $listaUMArray[$value->getId()]= $value->getFuerzaMilitar();
                }
            }
            $form = $this->createFormBuilder()

                ->setAction($this->generateUrl('socioeconomicoalt_update'))
               ->add('seccioniiUnidadMilitarTipo', 'choice', array( 'data'=> $pertUM ,'required' => false, 'choices' => $unidadMilitarArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
               ->add('seccioniiRecintoPenitenciarioTipo', 'choice', array('data'=> $pertP , 'required' => false, 'choices' => $penalArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
               ->add('seccioniiFuerzaMilitarTipo', 'choice', array( 'data'=> $umt ,'required' => false, 'choices' => $listaUMArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'tipomili(this.value)')))
                ->add('seccioniiLugarReclusionTipo', 'choice', array('data'=> $pt , 'required' => false, 'choices' => $listaPenalArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'tipopenal(this.value)')))
                ->add('seccioniiEsserviciomilitarCea', 'choice', array('data' => $cea,'required' => false, 'label' => false, 'empty_value' => false, 'choices' => array(true => 'El CEA', false => 'El Cuartel')))
                ->getForm();

         //   dump($penalArray);die;

             return $this->render('SieHerramientaAlternativaBundle:SocioeconomicoAlternativa:listarmenu.html.twig', array(
                   // 'datakrlos' => $krlosArray,
                    'dataidM' => $idMArray,
                    'formlista' => $form->createView(),
                   // 'form' => $form,

              ));

        } catch (Exception $ex) {

        }
}



    public function tipomiliAction($idUM,$pertUM)
    {
        try {
            //dump($idUM);die;
            $idUMArray = array ('id'=>$idUM);
            $em = $this->getDoctrine()->getManager();
            $unidadMilitar =$em->getRepository('SieAppWebBundle:UnidadMilitarTipo')->findBy(array('fuerzaMilitarTipo'=>$idUM),array('unidadMilitar' => 'ASC'));
            $unidadMilitarArray=array();
            foreach($unidadMilitar as $value){
                $unidadMilitarArray[$value->getId()] = $value->getUnidadMilitar();
            }

            $response = new JsonResponse();
            return $response->setData(array('listarunidadmilitar' => $unidadMilitarArray));
            //return $this->json(array('listarunidadmilitar' => $unidadMilitarArray));
/*
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('socioeconomicoalt_update'))
                ->add('seccioniiUnidadMilitar', 'choice', array( 'data'=> $pertUM ,'required' => false, 'choices' => $unidadMilitarArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                //->add('seccioniiUnidadMilitarTipo', 'text', array( 'data'=> $nombreA ,'required' => false, 'attr' => array('class' => 'form-control','disabled' => true)))
                ->getForm();
            return $this->render('SieHerramientaAlternativaBundle:SocioeconomicoAlternativa:tipomili.html.twig', array(
                'dataidUM' => $idUMArray,
                'formlista' => $form->createView(),
            ));
*/



        } catch (Exception $ex) {

        }
    }



    public function tipopenalAction($idP,$pertP)
    {
        try {

            $idPArray = array ('id'=>$idP);
            $em = $this->getDoctrine()->getManager();

           $penal =$em->getRepository('SieAppWebBundle:RecintoPenitenciarioTipo')->findBy(array('lugarReclusionTipo'=>$idP),array('recintoPenitenciario' => 'ASC'));

            $penalArray=array();
            foreach($penal as $value){
                $penalArray[$value->getId()] = $value->getRecintoPenitenciario();
            }

            $response = new JsonResponse();
            return $response->setData(array('listarpenal' => $penalArray));

        } catch (Exception $ex) {

        }
    }






}
