<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\EstudiantePersonaDiplomatico;
use Sie\AppWebBundle\Entity\Estudiante; 
use Sie\AppWebBundle\Entity\CtrEstudianteCenso; 
use Sie\AppWebBundle\Entity\Institucioneducativa; 
use Sie\AppWebBundle\Entity\EveEstudianteInscripcionEvento; 
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog; 
use Sie\AppWebBundle\Entity\CenEstudianteInscripcionCenso; 
use Sie\AppWebBundle\Entity\CensoBeneficiario; 
use Sie\AppWebBundle\Entity\CensoBeneficiarioRegular; 


class CensoBeneficioController extends Controller
{
    public $session;
    public $limitDay;
    public $arrUesCapinota;
    public function __construct() {
        $this->session = new Session();

        // $this->limitDay = '30-10-2023';

        // $this->arrUesCapinota = $this->setUEs();
    }       

    public function indexAction(Request $request){
        
        $ie_id=$this->session->get('ie_id');
        $em = $this->getDoctrine()->getManager();
        $id_usuario = $this->session->get('userId');
        // dump($id_usuario);die;
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }        
        
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ie_id);

        $gestion = $request->getSession()->get('currentyear');
        // $query="select 
        //         e.id e_id, e.codigo_rude, e.nombre, e.paterno, e.materno, e.carnet_identidad, e.complemento, e.fecha_nacimiento,
        //         ei.id ei_id, ei.estadomatricula_tipo_id, ic.institucioneducativa_id, ic.nivel_tipo_id, nt.nivel, ic.grado_tipo_id, gt.grado, ic.paralelo_tipo_id, pt.paralelo, ic.turno_tipo_id, tt.turno,
        //         cec.id cec_id, cec.ced_identidad, cec.nombres cnombres, cec.primer_ap cpaterno, cec.segundo_ap cmaterno, cec.departamento
        //         from estudiante_inscripcion ei 
        //         inner join estudiante e on ei.estudiante_id = e.id 
        //         inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id  = ic.id
        //         inner join ctr_estudiante_censo cec on cec.estudiante_inscripcion_id = ei.id
        //         inner join nivel_tipo nt on ic.nivel_tipo_id = nt.id
        //         inner join grado_tipo gt on ic.grado_tipo_id = gt.id
        //         inner join paralelo_tipo pt on ic.paralelo_tipo_id = pt.id
        //         inner join turno_tipo tt on ic.turno_tipo_id = tt.id
        //         where ic.nivel_tipo_id = 13
        //         and ic.gestion_tipo_id = 2024
        //         and ic.institucioneducativa_id = $ie_id
        //         order by ic.nivel_tipo_id, ic.grado_tipo_id desc, ic.paralelo_tipo_id, ic.turno_tipo_id, e.paterno, e.materno, e.nombre";
        $query = " select a.e_id, a.codigo_rude, a.nombre, a.paterno, a.materno, a.carnet_identidad, a.complemento, a.fecha_nacimiento,
                    a.ei_id, a.estadomatricula_tipo_id, a.institucioneducativa_id, a.nivel_tipo_id, a.nivel, a.grado_tipo_id, a.grado, a.paralelo_tipo_id, a.paralelo, a.turno_tipo_id, a.turno,
                    a.cec_id, a.estado
                    from (
                    select 
                    e.id e_id, e.codigo_rude, e.nombre, e.paterno, e.materno, e.carnet_identidad, e.complemento, e.fecha_nacimiento,
                    ei.id ei_id, ei.estadomatricula_tipo_id, ic.institucioneducativa_id, ic.nivel_tipo_id, nt.nivel, ic.grado_tipo_id, gt.grado, ic.paralelo_tipo_id, pt.paralelo, ic.turno_tipo_id, tt.turno,
                    cb.id cec_id, cast(1 as int) estado
                    from estudiante_inscripcion ei 
                    inner join estudiante e on ei.estudiante_id = e.id 
                    inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id  = ic.id
                    inner join censo_beneficiario cb on cb.estudiante_inscripcion_id = ei.id
                    inner join nivel_tipo nt on ic.nivel_tipo_id = nt.id
                    inner join grado_tipo gt on ic.grado_tipo_id = gt.id
                    inner join paralelo_tipo pt on ic.paralelo_tipo_id = pt.id
                    inner join turno_tipo tt on ic.turno_tipo_id = tt.id
                    where ic.nivel_tipo_id = 13
                    and ic.grado_tipo_id in (4,5,6)
                    and ei.estadomatricula_tipo_id = 4
                    and ic.gestion_tipo_id = 2024
                    and ic.institucioneducativa_id = $ie_id
                    union all 
                    select a.e_id, a.codigo_rude, a.nombre, a.paterno, a.materno, a.carnet_identidad, a.complemento, a.fecha_nacimiento,
                    a.ei_id, a.estadomatricula_tipo_id, a.institucioneducativa_id, a.nivel_tipo_id, a.nivel, a.grado_tipo_id, a.grado, a.paralelo_tipo_id, a.paralelo, a.turno_tipo_id, a.turno,
                    a.cec_id, cast(0 as int) estado
                    from (
                    select 
                    e.id e_id, e.codigo_rude, e.nombre, e.paterno, e.materno, e.carnet_identidad, e.complemento, e.fecha_nacimiento,
                    ei.id ei_id, ei.estadomatricula_tipo_id, ic.institucioneducativa_id, ic.nivel_tipo_id, nt.nivel, ic.grado_tipo_id, gt.grado, ic.paralelo_tipo_id, pt.paralelo, ic.turno_tipo_id, tt.turno,
                    cec.id cec_id, cb.id cb_id
                    from estudiante_inscripcion ei 
                    inner join estudiante e on ei.estudiante_id = e.id 
                    inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id  = ic.id
                    inner join ctr_estudiante_censo cec on cec.estudiante_inscripcion_id = ei.id
                    inner join nivel_tipo nt on ic.nivel_tipo_id = nt.id
                    inner join grado_tipo gt on ic.grado_tipo_id = gt.id
                    inner join paralelo_tipo pt on ic.paralelo_tipo_id = pt.id
                    inner join turno_tipo tt on ic.turno_tipo_id = tt.id
                    left join censo_beneficiario cb on ei.id = cb.estudiante_inscripcion_id 
                    where ic.nivel_tipo_id = 13
                    and ic.grado_tipo_id in (4,5,6)
                    and ei.estadomatricula_tipo_id = 4
                    and ic.gestion_tipo_id = 2024
                    and ic.institucioneducativa_id = $ie_id) a
                    where a.cb_id is null) a 
                    order by a.nivel_tipo_id, a.grado_tipo_id desc, a.paralelo_tipo_id, a.turno_tipo_id, a.paterno, a.materno, a.nombre ";

        $estudiantesCenso = $em->getConnection()->prepare($query);
        $estudiantesCenso->execute();
        $arrEstudiantesCenso = $estudiantesCenso->fetchAll();
        // dump($arrEstudiantesCenso);die;
        return $this->render('SieHerramientaBundle:CensoBeneficio:index.html.twig',array(
            'institucion' => $institucion,
            'estudiante' => $arrEstudiantesCenso
            // 'codsie'=>$sie,
            // 'disableElement'=>$disableElement
        )
        );        
    }

    public function formRegistroAction(Request $request, $id){
       
        $em = $this->getDoctrine()->getManager();
        $query="select nt.nivel, tt.turno, gt.grado, pt.paralelo,
                e.id e_id, e.codigo_rude, (e.nombre ||' '|| e.paterno || ' '|| e.materno) estudiante, 
                case when e.segip_id = 1 then e.carnet_identidad else '' end ci, 
                case when e.segip_id = 1 then e.complemento else '' end complemento,
                e.fecha_nacimiento, ei.id ei_id
                from estudiante_inscripcion ei 
                inner join estudiante e on ei.estudiante_id = e.id 
                inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id = ic.id
                inner join nivel_tipo nt on ic.nivel_tipo_id = nt.id
                inner join grado_tipo gt on ic.grado_tipo_id = gt.id
                inner join turno_tipo tt on ic.turno_tipo_id = tt.id
                inner join paralelo_tipo pt on ic.paralelo_tipo_id = pt.id
                where ei.id = $id ";
        $estudiante = $em->getConnection()->prepare($query);
        $estudiante->execute();
        $arrEstudiante = $estudiante->fetchAll();

        $query="select at2.id, at2.asignatura 
                from estudiante_inscripcion ei
                inner join estudiante_asignatura ea on ei.id = ea.estudiante_inscripcion_id 
                inner join institucioneducativa_curso_oferta ico on ea.institucioneducativa_curso_oferta_id = ico.id
                inner join asignatura_tipo at2 on at2.id = ico.asignatura_tipo_id 
                where ei.id = $id ";
        $areas = $em->getConnection()->prepare($query);
        $areas->execute();
        $arrAreas = $areas->fetchAll();
    
       
        return $this->render($this->session->get('pathSystem').':CensoBeneficio:registro_puntos.html.twig'
        ,array(
            'estudiante' => $arrEstudiante[0],
            'areas' => $arrAreas
            //'areasasig' => $asignaturamaestro
            )
        );
    }

    public function saveBeneficioAction(Request  $request){
        $response = new JsonResponse();
        try {
            $form = $request->request->all();
            $em = $this->getDoctrine()->getManager();
            $id_usuario = $this->session->get('userId');
            $inscripcion = $form['inscripcion_id'];
            $areas = $form['areas'];
            $areas = json_decode($form['areas'], true); // Decodificar el JSON a un array
            // dump(count($areas));
            // dump($areas);die;
            if (count($areas) == 0) {
                return $response->setData(['error' => 'No presenta áreas seleccionadas.']);
            }
            // Validar que 'areas' es un array
            if (!is_array($areas)) {
                return $response->setData(['error' => 'Formato de datos inválido.']);
            }

            $sumaPuntos = 0;
            $valid = true;
            foreach ($areas as $area) {
                $areaid = $area['areaid'];
                $puntosT2 = isset($area['puntosT2']) ? (int)$area['puntosT2'] : 0;
                $puntosT3 = isset($area['puntosT3']) ? (int)$area['puntosT3'] : 0;
                if (!is_numeric($areaid) || $areaid <= 0) {
                    $valid = false;
                    break;
                }
                $sumaPuntos += $puntosT2 + $puntosT3;
            }

            if (!$valid) {
                return $response->setData(['error' => 'Debe seleccionar las áreas.']);
            }

            if ($sumaPuntos < 0) {
                return $response->setData(['error' => 'La suma de puntos no puede ser menor a 0.']);
            }
            if ($sumaPuntos > 30) {
                return $response->setData(['error' => 'La suma de puntos no puede ser mayor a 30.']);
            }
            // return $response->setData(['suma' => $sumaPuntos]);
            $sie=$this->session->get('ie_id');
            $gestion=$this->session->get('currentyear');
            
            $query="select e.codigo_rude, ei.estudiante_id, ei.id ei_id, ic.institucioneducativa_id, ic.nivel_tipo_id, ic.grado_tipo_id, COALESCE(cec.id,0) cec_id
                    from estudiante_inscripcion ei 
                    inner join estudiante e on ei.estudiante_id = e.id
                    inner join institucioneducativa_curso ic on ic.id = ei.institucioneducativa_curso_id 
                    left join ctr_estudiante_censo cec on cec.estudiante_inscripcion_id = ei.id
                    where ei.id = $inscripcion
                    and ei.estadomatricula_tipo_id in (4)
                    and ic.nivel_tipo_id = 13 
                    and ic.grado_tipo_id in (4,5,6) ";
            $estreg = $em->getConnection()->prepare($query);
            $estreg->execute();
            $arrEstReg = $estreg->fetchAll();

            if (empty($arrEstReg)) {
                return $response->setData(['error' => 'El estudiante no es efectivo o tiene observaciones en el año de escolaridad para aplicar el beneficio.']);
            }
            $regestudiante = $em->getRepository('SieAppWebBundle:CensoBeneficiario')->findBy(['estudiante' => $arrEstReg[0]['estudiante_id']]);
                        
            if (count($regestudiante) > 0) {
                $ieBeneficioReg = $regestudiante[0]->getInstitucioneducativa()->getId(); 
                return $response->setData([
                    'error' => 'El estudiante ya cuenta con el Registro del Beneficio CPV en la UE o CEA: ' . $ieBeneficioReg
                ]);
            }

            // dump($arrEstReg[0]['estudiante_id']);
            // dump($regestudiante['institucioneducativa']);die;

            if (count($arrEstReg) == 0) {
                return $response->setData(['error' => 'El estudiante no es efectivo o tiene observaciones en el año de escolaridad para aplicar el beneficio.']);
            }

            $codigoRude = $arrEstReg[0]['codigo_rude'];
            $nArcFormReg = $this->guardarArch($sie, $codigoRude,$gestion,'CPV2024_FR', $_FILES['formulario_registro']);
            $nArcCertif = $this->guardarArch($sie, $codigoRude,$gestion,'CPV2024_CT', $_FILES['certificado_cpv']);
            $data = [
                'form_reg' => $nArcFormReg,
                'certificado_cpv' => $nArcCertif
            ];
            $jsonData = json_encode($data);

            $cenBeneficiario = new CensoBeneficiario();
            $cenBeneficiario->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($arrEstReg[0]['estudiante_id']));
            $cenBeneficiario->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrEstReg[0]['ei_id']));
            $cenBeneficiario->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($arrEstReg[0]['institucioneducativa_id']));
            $cenBeneficiario->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find($arrEstReg[0]['nivel_tipo_id']));
            $cenBeneficiario->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($arrEstReg[0]['grado_tipo_id']));
            $cenBeneficiario->setCensoTablaId($arrEstReg[0]['cec_id']);
            $cenBeneficiario->setArchivo($jsonData);
            $cenBeneficiario->setUsuarioId($id_usuario);
            $cenBeneficiario->setFechaRegistro(new \DateTime('now'));
            $em->persist($cenBeneficiario);
            $em->flush();
    

            foreach ($areas as $area) {
                foreach (['puntosT2' => 59, 'puntosT3' => 60] as $key => $notaTipoId) {
                    $censoBeneficiarioRegular = new CensoBeneficiarioRegular();
                    $censoBeneficiarioRegular->setCensoBeneficiario($cenBeneficiario);
                    $censoBeneficiarioRegular->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find((int)$area['areaid']));
                    $censoBeneficiarioRegular->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($notaTipoId));
                    $censoBeneficiarioRegular->setNotaCuantitava((int)$area[$key]);
                    $censoBeneficiarioRegular->setUsuarioId($id_usuario);
                    $censoBeneficiarioRegular->setFechaCreacion(new \DateTime('now'));
                    $em->persist($censoBeneficiarioRegular);
                }
            }
            
            $em->flush();

            return $response->setData(['success' => true]);

            // return $this->redirect($this->generateUrl('censobeneficio_index'));
            
            
          

        } catch (Exception $e) {
            
            $em->getConnection()->rollback();
            $response->setStatusCode(500);
            return $response;
        }
        
    }

    private function guardarArch($sie, $codigoRude, $gestion, $prefijo, $file)
    {
        if (isset($file)) {
            $type = $file['type'];
            $size = $file['size'];
            $tmp_name = $file['tmp_name'];
            $name = $file['name'];
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_name = $prefijo.date('YmdHis') . '.' . $extension;
    
            // GUARDAR EL ARCHIVO
            $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/censo/'.$gestion.'/'. $sie . '/' . $codigoRude;
            if (!file_exists($directorio)) {
                mkdir($directorio, 0777, true);
            }
    
            $archivador = $directorio . '/' . $new_name;
    
            if (!move_uploaded_file($tmp_name, $archivador)) {
                return null;
            }
    
            // CREAR DATOS DEL ARCHIVO
            $informe = array(
                'name' => $name,
                'type' => $type,
                'tmp_name' => 'nueva_ruta',
                'size' => $size,
                'new_name' => $new_name
            );
    
            return $new_name;
        } else {
            return null;
        }
    }
   
    public function findEstudianteAction(Request $request, $id){

    //     // get the vars send        
    //     // create db conexion
        $ie_id=$this->session->get('ie_id');
        $em = $this->getDoctrine()->getManager();
        $query = " select a.e_id, a.codigo_rude, a.nombre, a.paterno, a.materno, a.carnet_identidad, a.complemento, a.fecha_nacimiento,
                    a.ei_id, a.estadomatricula_tipo_id, a.institucioneducativa_id, a.nivel_tipo_id, a.nivel, a.grado_tipo_id, a.grado, a.paralelo_tipo_id, a.paralelo, a.turno_tipo_id, a.turno,
                    a.cec_id, a.estado
                    from (
                    select 
                    e.id e_id, e.codigo_rude, e.nombre, e.paterno, e.materno, e.carnet_identidad, e.complemento, e.fecha_nacimiento,
                    ei.id ei_id, ei.estadomatricula_tipo_id, ic.institucioneducativa_id, ic.nivel_tipo_id, nt.nivel, ic.grado_tipo_id, gt.grado, ic.paralelo_tipo_id, pt.paralelo, ic.turno_tipo_id, tt.turno,
                    cb.id cec_id, cast(1 as int) estado
                    from estudiante_inscripcion ei 
                    inner join estudiante e on ei.estudiante_id = e.id 
                    inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id  = ic.id
                    inner join censo_beneficiario cb on cb.estudiante_inscripcion_id = ei.id
                    inner join nivel_tipo nt on ic.nivel_tipo_id = nt.id
                    inner join grado_tipo gt on ic.grado_tipo_id = gt.id
                    inner join paralelo_tipo pt on ic.paralelo_tipo_id = pt.id
                    inner join turno_tipo tt on ic.turno_tipo_id = tt.id
                    where ic.nivel_tipo_id = 13
                    and ic.grado_tipo_id in (4,5,6)
                    and ei.estadomatricula_tipo_id = 4
                    and ic.gestion_tipo_id = 2024
                    and ic.institucioneducativa_id = $ie_id
                    union all 
                    select a.e_id, a.codigo_rude, a.nombre, a.paterno, a.materno, a.carnet_identidad, a.complemento, a.fecha_nacimiento,
                    a.ei_id, a.estadomatricula_tipo_id, a.institucioneducativa_id, a.nivel_tipo_id, a.nivel, a.grado_tipo_id, a.grado, a.paralelo_tipo_id, a.paralelo, a.turno_tipo_id, a.turno,
                    a.cec_id, cast(0 as int) estado
                    from (
                    select 
                    e.id e_id, e.codigo_rude, e.nombre, e.paterno, e.materno, e.carnet_identidad, e.complemento, e.fecha_nacimiento,
                    ei.id ei_id, ei.estadomatricula_tipo_id, ic.institucioneducativa_id, ic.nivel_tipo_id, nt.nivel, ic.grado_tipo_id, gt.grado, ic.paralelo_tipo_id, pt.paralelo, ic.turno_tipo_id, tt.turno,
                    cec.id cec_id, cb.id cb_id
                    from estudiante_inscripcion ei 
                    inner join estudiante e on ei.estudiante_id = e.id 
                    inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id  = ic.id
                    inner join ctr_estudiante_censo cec on cec.estudiante_inscripcion_id = ei.id
                    inner join nivel_tipo nt on ic.nivel_tipo_id = nt.id
                    inner join grado_tipo gt on ic.grado_tipo_id = gt.id
                    inner join paralelo_tipo pt on ic.paralelo_tipo_id = pt.id
                    inner join turno_tipo tt on ic.turno_tipo_id = tt.id
                    left join censo_beneficiario cb on ei.id = cb.estudiante_inscripcion_id 
                    where ic.nivel_tipo_id = 13
                    and ic.grado_tipo_id in (4,5,6)
                    and ei.estadomatricula_tipo_id = 4
                    and ic.gestion_tipo_id = 2024
                    and ic.institucioneducativa_id = $ie_id) a
                    where a.cb_id is null) a 
                    where a.codigo_rude = '$id'
                    order by a.nivel_tipo_id, a.grado_tipo_id desc, a.paralelo_tipo_id, a.turno_tipo_id, a.paterno, a.materno, a.nombre ";

        $estudiantesCenso = $em->getConnection()->prepare($query);
        $estudiantesCenso->execute();
        $arrEstudiantesCenso = $estudiantesCenso->fetchAll();

        if ( count($arrEstudiantesCenso) > 0){
            $response = new JsonResponse();
            $response->setData(['estudiantes' =>  $arrEstudiantesCenso]);
            return $response;
        }

        $query = "select 
                    e.id e_id, e.codigo_rude, e.nombre, e.paterno, e.materno, e.carnet_identidad, e.complemento, e.fecha_nacimiento,
                    ei.id ei_id, ei.estadomatricula_tipo_id, ic.institucioneducativa_id, ic.nivel_tipo_id, nt.nivel, ic.grado_tipo_id, gt.grado, ic.paralelo_tipo_id, pt.paralelo, ic.turno_tipo_id, tt.turno,
                    cast(0 as int) cec_id, cast(2 as int) estado
                    from estudiante_inscripcion ei 
                    inner join estudiante e on ei.estudiante_id = e.id 
                    inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id  = ic.id
                    inner join nivel_tipo nt on ic.nivel_tipo_id = nt.id
                    inner join grado_tipo gt on ic.grado_tipo_id = gt.id
                    inner join paralelo_tipo pt on ic.paralelo_tipo_id = pt.id
                    inner join turno_tipo tt on ic.turno_tipo_id = tt.id
                    where ic.nivel_tipo_id = 13
                    and ic.grado_tipo_id in (4,5,6)
                    and ei.estadomatricula_tipo_id = 4
                    and ic.gestion_tipo_id = 2024
                    and ic.institucioneducativa_id = $ie_id
                    and e.codigo_rude = '$id'";

        $estudiantesNewCenso = $em->getConnection()->prepare($query);
        $estudiantesNewCenso->execute();
        $arrEstudiantesNewCenso = $estudiantesNewCenso->fetchAll();

        if ( count($arrEstudiantesNewCenso) > 0){
            $response = new JsonResponse();
            $response->setData(['estudiantes' =>  $arrEstudiantesNewCenso]);
            return $response;
        } else {
            $response = new JsonResponse();
            $response->setData(['error' =>  'El estudiante no cumple con los requisitos']);
            return $response;
        }

        // return $this->render('SieHerramientaBundle:CensoBeneficio:index.html.twig',array(
        //     'institucion' => $institucion,
        //     'estudiante' => $arrEstudiantesCenso
        //     // 'codsie'=>$sie,
        //     // 'disableElement'=>$disableElement
        // )
        // );        

    //     $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
    //     $query->bindValue(':user_id', $this->session->get('userId'));
    //     $query->bindValue(':sie', $sie);
    //     $query->bindValue(':roluser', $this->session->get('roluser'));
    //     $query->execute();
    //     $aTuicion = $query->fetchAll();

    //     $existUE = 0;
    //     $arrModalidades = array();        
    //     // $arrLevel = array(
    //     //     array('id'=>12,'level'=>'Educación Primaria Comunitaria Vocacional'),
    //     //     array('id'=>13,'level'=>'Educación Secundaria Comunitaria Productiva'),
    //     // ); 

    //     $swcloseevent =  (is_object($this->checkOperativeChees($sie)))?1:0;   
    //     // start this secction validate the last day to report the inscription INFO
    //     $swcloseevent = ($swcloseevent)?1:$this->getLastDayRegistryOpeCheesEventStatus($this->limitDay);          
    //     // end this secction validate the last day to report the inscription INFO                     

    //     // start look for the level, grado, parallel & turno
    //     $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
    //     if($institucion){
        	
	//         $em = $this->getDoctrine()->getManager();
	//         //get the Niveles

	//         $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
	//         $query = $entity->createQueryBuilder('iec')
	//                 ->select('(iec.nivelTipo)')
	//                 ->where('iec.institucioneducativa = :sie')
	//                 ->andwhere('iec.gestionTipo = :gestion')
	//                 ->andwhere('iec.nivelTipo = :nivel')
	//                 ->setParameter('sie', $sie)
	//                 ->setParameter('gestion', $this->session->get('currentyear') )
	//                 // ->setParameter('gestion', 2020 )
	//                 ->setParameter('nivel', '13')
	//                 ->orderBy('iec.nivelTipo', 'ASC')
	//                 ->distinct()
	//                 ->getQuery();
	//         $aNiveles = $query->getResult();

    //         if(sizeof($aNiveles)>0){
	// 	        $arrniveles = array();
	// 	        foreach ($aNiveles as $nivel) {
	// 	            $arrniveles[] = array('id'=> $nivel[1], 'level'=>$em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel());
	// 	        }			
	// 	    }else{
    //             $response = new JsonResponse();
    //             $response->setStatusCode(404);
    //             return $response;
    //         }

	//     }        
    //     // end   look for the level, grado, parallel & turno


    //     // if ($aTuicion[0]['get_ue_tuicion'] == true){
    //     if (1){
    //         $objUE = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
            
    //         if(sizeof($objUE)>0){
    //             $existUE = 1;
    //             $objModalidades = $em->getRepository('SieAppWebBundle:EveModalidadesTipo')->findAll();
    //             if(sizeof($objModalidades) > 0 ){
    //                 foreach ($objModalidades as $value) {
    //                     $arrModalidades[]=array('id'=>$value->getId(), 'modalidad'=>$value->getDescripcion() );
    //                 }
    //             }
    //             $arrResponse = array(
    //                 'sie'                 => $objUE->getId(),
    //                 'institucioneducativa'=> $objUE->getInstitucioneducativa(),
    //                 'existUE'         => $existUE,                
    //                 'arrModalidades'  => $arrModalidades,                
    //                 'arrLevel'         => $arrniveles,                
    //                 'swcloseevent'         => $swcloseevent,
    //                 'urlreporte'=> ($swcloseevent)?$this->generateUrl('cheesevent_reportChessInscription', array('sie'=>$sie)):''
    //             );               
    //         }else{
    //             $arrResponse = array(
    //                 'sie'                 => '',
    //                 'institucioneducativa'=> 'No tiene tuición sobre la institución educatia o no existe codigo SIE ',
    //                 'existUE'             => $existUE,
    //                 'arrModalidades'      => $arrModalidades,
    //                 'arrLevel'            => $arrniveles,
    //                 'swcloseevent'            => $swcloseevent,
    //                 'urlreporte'=> ($swcloseevent)?$this->generateUrl('cheesevent_reportChessInscription', array('sie'=>$sie)):''
    //             );   
    //         }            
    //     }else{
    //             $arrResponse = array(
    //                 'sie'                 => '',
    //                 'institucioneducativa'=> 'N',
    //                 'existUE'         => $existUE,
    //                 'arrModalidades'         => $arrModalidades,
    //                 'arrLevel'         => $arrniveles,
    //                 'swcloseevent'         => $swcloseevent,
    //                 'urlreporte'=> ($swcloseevent)?$this->generateUrl('cheesevent_reportChessInscription', array('sie'=>$sie)):''
    //             ); 
    //     }
        
    //     $response = new JsonResponse();
    //     $response->setStatusCode(200);
    //     $response->setData($arrResponse);

    //     return $response;        
    }

    // /**
    //  * get grado
    //  * @param type $idnivel
    //  * @param type $sie
    //  * return list of grados
    //  */
    // public function getGradoAction(Request  $request) {
    	
    // 	// ini vars
    //     $em = $this->getDoctrine()->getManager();
    //     $response = new JsonResponse();
    //     // get the send values
    //     $sie = $request->get('sie');
    //     $idnivel = $request->get('levelId');
    //     $gestionselected = $this->session->get('currentyear') ;

	//     $agrados = array();

    //     if($idnivel>0){
	//         //get grado
	//         $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
	//         $query = $entity->createQueryBuilder('iec')
	//                 ->select('(iec.gradoTipo)')
	//                 //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
	//                 ->where('iec.institucioneducativa = :sie')
	//                 ->andWhere('iec.nivelTipo = :idnivel')
	//                 ->andwhere('iec.gestionTipo = :gestion')
	//                 ->andwhere('iec.gradoTipo IN (:agrados)')
	//                 ->setParameter('sie', $sie)
	//                 ->setParameter('idnivel', $idnivel)
	//                 ->setParameter('gestion', $gestionselected)
	//                 ->setParameter('agrados', array(3,4,5,6))
	//                 ->distinct()
	//                 ->orderBy('iec.gradoTipo', 'ASC')
	//                 ->getQuery();
	//         $aGrados = $query->getResult();

	//         $agrados = array();
	        
	//         foreach ($aGrados as $grado) {
	//         	$agrados[$grado[1]] = array('id'=>$grado[1], 'grado'=>$em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado() );
	//         }

	//     }

      
    //   $arrResponse = array(
    //   	'arrGrado' => $agrados,
    //   	'existUE' => 1,

    //   );
    //   $response->setStatusCode(200);
    //   $response->setData($arrResponse);

    //   return $response;
        
    // }

    // /**
    //  * get the paralelos
    //  * @param type $idnivel
    //  * @param type $sie
    //  * @return type
    //  */
    // public function getParallelAction(Request $request) {
    // 	// ini vars
    //     $em = $this->getDoctrine()->getManager();
    //     $response = new JsonResponse();
    //     // get the send values
    //     $sie = $request->get('sie');
    //     $nivel = $request->get('levelId');
    //     $grado = $request->get('gradoId');
    //     $gestion = $this->session->get('currentyear') ;
	// 	//get paralelo
    //     $aparalelos = array();
    //     $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
    //     $query = $entity->createQueryBuilder('iec')
    //             ->select('(iec.paraleloTipo)')
    //             //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
    //             ->where('iec.institucioneducativa = :sie')
    //             ->andWhere('iec.nivelTipo = :nivel')
    //             ->andwhere('iec.gradoTipo = :grado')
    //             ->andwhere('iec.gestionTipo = :gestion')
    //             ->setParameter('sie', $sie)
    //             ->setParameter('nivel', $nivel)
    //             ->setParameter('grado', $grado)
    //             ->setParameter('gestion', $gestion)
    //             ->distinct()
    //             ->orderBy('iec.paraleloTipo', 'ASC')
    //             ->getQuery();
    //     $aParalelos = $query->getResult();
    //     $aparalelos = array();
    //     foreach ($aParalelos as $paralelo) {
    //     	$aparalelos[$paralelo[1]] = array('id'=>$paralelo[1], 'paralelo'=>$em->getRepository('SieAppWebBundle:ParaleloTipo')->find($paralelo[1])->getParalelo() );    
    //     }

    //   $arrResponse = array(
    //   	'arrParalelo' => $aparalelos,
    //   	'existUE' => 1,
    //   );
    //   $response->setStatusCode(200);
    //   $response->setData($arrResponse);

    //   return $response;
    // }

    // /**
    //  * get the turnos
    //  * @param type $turno
    //  * @param type $sie
    //  * @param type $nivel
    //  * @param type $grado
    //  * @return type
    //  */
    // public function getTurnoAction(Request $request) {
    //     // ini vars
    //     $em = $this->getDoctrine()->getManager();
    //     $response = new JsonResponse();
    //     // get the send values
    //     $sie = $request->get('sie');
    //     $nivel = $request->get('levelId');
    //     $grado = $request->get('gradoId');
    //     $paralelo = $request->get('parallelId');
    //     $gestion = $this->session->get('currentyear') ;
	// 	//get turno
    //     $aturnos = array();

    //     $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
    //     $query = $entity->createQueryBuilder('iec')
    //             ->select('(iec.turnoTipo)')
    //             ->where('iec.institucioneducativa = :sie')
    //             ->andWhere('iec.nivelTipo = :nivel')
    //             ->andwhere('iec.gradoTipo = :grado')
    //             ->andwhere('iec.paraleloTipo = :paralelo')
    //             ->andWhere('iec.gestionTipo = :gestion')
    //             ->setParameter('sie', $sie)
    //             ->setParameter('nivel', $nivel)
    //             ->setParameter('grado', $grado)
    //             ->setParameter('paralelo', $paralelo)
    //             ->setParameter('gestion', $gestion)
    //             ->distinct()
    //             ->orderBy('iec.turnoTipo', 'ASC')
    //             ->getQuery();
    //     $aTurnos = $query->getResult();
    //     foreach ($aTurnos as $turno) {
    //         $aturnos[] =array('id'=>$turno[1], 'turno'=> $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno[1])->getTurno());
    //     }

    //   $arrResponse = array(
    //   	'arrTurno' => $aturnos,
    //   	'existUE' => 1,
    //   );
    //   $response->setStatusCode(200);
    //   $response->setData($arrResponse);

    //   return $response;
    // }


    // public function dataSelectedAction(Request $request){
        
    //     // get the vars send        
    //     $sie = $request->get('sie');
    //     $institucioneducativa = $request->get('institucioneducativa');
    //     $levelId = $request->get('levelId');
    //     $gradoId = $request->get('gradoId');
    //     $parallelId = $request->get('parallelId');
    //     $turnoId = $request->get('turnoId');
    //     $year = $this->session->get('currentyear');
        
    //     // create db conexion
    //     $em = $this->getDoctrine()->getManager();

    //     $levelLabel = $em->getRepository('SieAppWebBundle:NivelTipo')->find($levelId);
    //     $gradoLabel = $em->getRepository('SieAppWebBundle:GradoTipo')->find($gradoId);
    //     $parallelLabel = $em->getRepository('SieAppWebBundle:ParaleloTipo')->find($parallelId);
    //     $turnoLabel = $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turnoId);
        
    //     //start get the students
    //     $query="
    //             select e.carnet_identidad, e.complemento ,e.codigo_rude ,e.paterno ,e.materno ,e.nombre ,e.genero_tipo_id , ei.id as inscriptionId, gt.genero
    //             from estudiante e 
    //             inner join estudiante_inscripcion ei on (e.id = ei.estudiante_id)
    //             inner join institucioneducativa_curso ic on (ei.institucioneducativa_curso_id=ic.id)
    //             inner join genero_tipo gt on (e.genero_tipo_id=gt.id)
    //             where ic.nivel_tipo_id =$levelId and ic.grado_tipo_id =$gradoId and ic.paralelo_tipo_id = '".$parallelId."' and ic.turno_tipo_id = $turnoId and ic.institucioneducativa_id =$sie and ic.gestion_tipo_id =$year  and  ei.estadomatricula_tipo_id in (4,5,55)  
    //             order by e.paterno, e.materno, e.nombre
    //     ";


    //     $statement = $em->getConnection()->prepare($query);
    //     $statement->execute();
    //     $arrStudents = $statement->fetchAll();
        
    //     $arrStudentsList = array();

    //     foreach ($arrStudents as $value) {
    //                 $query=" select * from cen_estudiante_inscripcion_censo
    //                 where institucioneducativa_id =".$sie." and estudiante_inscripcion_id = ".$value['inscriptionid']."  ";

    //         $statement = $em->getConnection()->prepare($query);
    //         $statement->execute();
    //         $arrStudentsRegistered = $statement->fetchAll();
    //         if(sizeof($arrStudentsRegistered)>0){
    //             // dump($value);die;
    //             $value["estado"] = 0;
    //             $arrStudentsList[] = $value;
    //         }else{
    //             $value['estado'] = 1;
    //             $arrStudentsList[] = $value;
    //         }
    //     }
    //     //end   get the students
      
    //     $swcloseevent =  (is_object($this->checkOperativeChees($sie)))?1:0;            
    //     // start this secction validate the last day to report the inscription INFO
    //     $swcloseevent = ($swcloseevent)?1:$this->getLastDayRegistryOpeCheesEventStatus($this->limitDay);  
    //     // end this secction validate the last day to report the inscription INFO        
    //     // get students data
    //     $arrEveStudentsCenso = $this->getAllRegisteredInscription($sie);
        
    //     $arrResponse = array(
    //     	'levelLabel'=>$levelLabel->getNivel(),
    //     	'gradoLabel'=>$gradoLabel->getGrado(),
    //     	'parallelLabel'=>$parallelLabel->getParalelo(),
    //     	'turnoLabel'=>$turnoLabel->getTurno(),
    //     	'arrStudents' => $arrStudentsList,
    //         'arrEveStudentsCenso' => $arrEveStudentsCenso,
    //         'existSelectData' => 1,    
    //         'swcloseevent'    => $swcloseevent,   
    //         'existStudent'         => 1,    
    //     ); 

    //     $response = new JsonResponse();
    //     $response->setStatusCode(200);
    //     $response->setData($arrResponse);
    //     return $response;  
    // }

    // public function validateTutorAction(Request $request){
        
    //     $dataStudent = $request->get('dataStudent');
    //     $apoderadoInput = $request->get('apoderado');
    //     $sie = $request->get('sie');

    //     $em = $this->getDoctrine()->getManager();

    //     $year = $this->session->get('currentyear');
    //     $query="
    //             select p.*, ai.id as apoderadoInscripId
    //             from apoderado_inscripcion ai
    //             inner join persona p on (ai.persona_id = p.id)
    //             inner join estudiante_inscripcion ei on (ai.estudiante_inscripcion_id = ei.id)
    //             inner join estudiante e on (ei.estudiante_id = e.id)
    //             inner join institucioneducativa_curso iec on (ei.institucioneducativa_curso_id = iec.id)
    //             where e.codigo_rude =  '".$dataStudent['codigo_rude']."'  and iec.gestion_tipo_id=2023
    //     ";

    //     $statement = $em->getConnection()->prepare($query);
    //     $statement->execute();
    //     $arrDataStudentsFull = $statement->fetchAll();
    
    //     $apoderadoOutput = array();
    //     $swparent = 1;

    //     $apoderadoInput['fecNacimiento'] = str_replace('/', '-', $apoderadoInput['fecNacimiento']);
    //     $apoderadoInput = array_map("strtoupper", $apoderadoInput);
    //     $apoderadoInput['complemento'] = (isset($apoderadoInput['complemento'])) ? $apoderadoInput['complemento'] : '';

    //     if(sizeof($arrDataStudentsFull)>0){
    //         // foreach ($arrDataStudentsFull as $arrDataStudents) {
    //         while (($arrDataStudents = current($arrDataStudentsFull)) !== FALSE && $swparent) {                
    //             $apoderadoOutput = array(
    //                 "paterno" => trim($arrDataStudents['paterno']),
    //                 "materno" => trim($arrDataStudents['materno']),
    //                 "nombre" => trim($arrDataStudents['nombre']),
    //                 "carnet" => $arrDataStudents['carnet'],
    //                 "complemento" => $arrDataStudents['complemento'],
    //                 "fecNacimiento" =>date('d-m-Y',strtotime($arrDataStudents['fecha_nacimiento']) ),              
    //             );
                
    //             $apoderadoOutput = array_map("strtoupper", $apoderadoOutput);
    //             // $apoderadoInput = array_map("strtoupper", $apoderadoInput);
    //             // check if the values of parent/tutor are the same                
    //             if(($apoderadoInput == $apoderadoOutput)){
    //                 $apoderadoOutput['apoderadoinscripid'] = $arrDataStudents['apoderadoinscripid'];
    //                 $swparent = 0;
    //             }
    //                 //$apoderadoOutput['apoderadoinscripid'] = $arrDataStudents[0]['apoderadoinscripid'];
    //             next($arrDataStudentsFull);
    //         }

    //     }

    //     if($swparent){
    //           $data = array(
    //               'operativoTipo' => 12,
    //               'gestion' => $this->session->get('currentyear'),
    //               'id' => $sie,                  
    //               'consolidacionValor' => json_encode($apoderadoInput)
    //           );   
    //           $operativo = $this->get('funciones')->saveDataInstitucioneducativaOperativoLog($data);
    //     }

    //     $arrResponse = array(
    //         'apoderadoOutput' => $apoderadoOutput,
    //         'swparent' => !$swparent,
    //     ); 
        
    //     $response = new JsonResponse();
    //     $response->setStatusCode(200);
    //     $response->setData($arrResponse);
    //     return $response;

    // }

    // function strtoupper($ele){
    //     return strtoupper($ele);
    // }

    // public function saveStudentAction(Request  $request){

    //     try {
    //         //code...
    //         $dataInfoUE = json_decode($request->get('infoUE'),true);
            
    //         if(isset($_FILES['informe'])){
    //             $file = $_FILES['informe'];
    
    //             if ($file['name'] != "") {
    //                 $type = $file['type'];
    //                 $size = $file['size'];
    //                 $tmp_name = $file['tmp_name'];
    //                 $name = $file['name'];
    //                 $extension = explode('.', $name);
    //                 $extension = $extension[count($extension)-1];
    //                 $new_name = $dataInfoUE['dataStudent']['inscriptionid'].'-'.date('YmdHis').'.'.$extension;
    
    //                 // GUARDAMOS EL ARCHIVO
    //                 $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/opecenso/' . $dataInfoUE['sie'];
    //                 if (!file_exists($directorio)) {
    //                     mkdir($directorio, 0777, true);
    //                 }
    
    //                 $archivador = $directorio.'/'.$new_name;
    //                 //unlink($archivador);
    //                 if(!move_uploaded_file($tmp_name, $archivador)){
    //                     $response->setStatusCode(500);
    //                     return $response;
    //                 }
                    
    //                 // CREAMOS LOS DATOS DE LA IMAGEN
    //                 $informe = array(
    //                     'name' => $name,
    //                     'type' => $type,
    //                     'tmp_name' => 'nueva_ruta',
    //                     'size' => $size,
    //                     'new_name' => $new_name
    //                 );
    //             }else{
    //                 $informe = null;
    //             }
    //         }else{
    //             $informe = null;
    //         }
    
    //         $em = $this->getDoctrine()->getManager();

    //         $cenBeneficiario = new CenEstudianteInscripcionCenso();

    //         $cenBeneficiario->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($dataInfoUE['dataStudent']['inscriptionid']));
    //         $cenBeneficiario->setApoderadoInscripcion($em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($dataInfoUE['apoderadoinscripid']));
    //         $cenBeneficiario->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($dataInfoUE['sie']));

    //         $cenBeneficiario->setDocumentoPath($archivador);
    //         $cenBeneficiario->setEmail($dataInfoUE['email']);
    //         $cenBeneficiario->setCelnumero($dataInfoUE['celular']);
    //         $cenBeneficiario->setEsVigente(1);
    //         $cenBeneficiario->setFechaRegistro(new \DateTime('now'));
    //         $cenBeneficiario->setFechaModificacion(new \DateTime('now'));
    
    //         $em->persist($cenBeneficiario);
    //         $em->flush();
    
    //         $arrEveStudentsCenso = $this->getAllRegisteredInscription($dataInfoUE['sie']);

    //         $arrResponse = array(
                
    //             'arrEveStudentsCenso' => $arrEveStudentsCenso,
                
    //         ); 
    
    //         $response = new JsonResponse();
    //         $response->setStatusCode(200);
    //         $response->setData($arrResponse);
    //         return $response;
                            
    //     } catch (\Exception $e) {
    //         dump($e);die;
    //     }
        
    // }

    // public function checkEdadCensoAction( Request $request ){

    //     $em = $this->getDoctrine()->getManager();
    //     $db = $em->getConnection();

    //     $query = $db->prepare("select e.fecha_nacimiento from estudiante e where e.carnet_identidad='".$request->get('carnet')."' and e.codigo_rude='".$request->get('rude')."'");
    //     $query->execute();
    //     $resultStudent = $query->fetch();

    //     $queryGestion = $db->prepare("SELECT TO_CHAR(NOW()::date, 'yyyy-mm-dd') as currentyear");
    //     $queryGestion->execute();
    //     $resultDate = $queryGestion->fetch();

    //     $yearAdd = substr( $resultDate['currentyear'], 0,4 ) + 1;
    //     $dateAdd = $yearAdd.'-03-21';

    //     $queryAge = $db->prepare("select public.sp_obtener_edad(to_date('".$resultStudent['fecha_nacimiento']."','YYYY-MM-DD'),to_date('".$dateAdd."','YYYY-MM-DD')) as edad");
    //     $queryAge->execute();
    //     $resultAge = $queryAge->fetch();

    //     $response = new JsonResponse();
    //     $response->setStatusCode(200);

    //     $arrResponse = array(
    //         'statusCode' => 200
    //     );

    //     if( $resultAge['edad'] < 16 ){
            
    //         $arrResponse = array(
    //             'statusCode' => 401,
    //             'message' => "Este estudiante no cumple con 16 años al 21 de Marzo de ".$yearAdd
    //         );

    //     }
        
    //     $response->setData($arrResponse);
    //     return $response;

    // }

    // private function getLastDayRegistryOpeCheesEventStatus($limitDay){
    //     $today = date('d-m-Y');
    //     $swcloseevent =  (strtotime($today) >= strtotime($limitDay))?1:0;  
    //     return $swcloseevent;
    // }
    // private function checkOperativeChees($sie){
    //     // create db conexion
    //     $em=$this->getDoctrine()->getManager();

    //       $objDownloadFilenewOpe = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findOneBy(array(
    //         'institucioneducativa'=>$sie,
    //         'institucioneducativaOperativoLogTipo'=>12,
    //         'consolidacionValor'=>1,
    //         'gestionTipoId'=>$this->session->get('currentyear')
    //       ));

    //     return $objDownloadFilenewOpe;        
    // }   


    // private function getAllRegisteredInscription($sie){
    //     $em = $this->getDoctrine()->getManager();
    //     $query = "SELECT e.codigo_rude,e.paterno,e.materno,e.nombre,e.carnet_identidad,e.complemento,nt.nivel,gt.grado,pt.paralelo, eeie.id as eveinscriptionid 
    //             FROM cen_estudiante_inscripcion_censo eeie
    //             inner join estudiante_inscripcion ei on (eeie.estudiante_inscripcion_id = ei.id)
    //             inner join estudiante e on (ei.estudiante_id = e.id)
    //             inner join institucioneducativa_curso iec on (ei.institucioneducativa_curso_id = iec.id)
    //             inner join nivel_tipo nt on (iec.nivel_tipo_id=nt.id)
    //             inner join grado_tipo gt on (iec.grado_tipo_id=gt.id)
    //             inner join paralelo_tipo pt on (iec.paralelo_tipo_id=pt.id)
    //             where 
    //             iec.institucioneducativa_id = " . $sie ;

    //     $statement = $em->getConnection()->prepare($query);
    //     $statement->execute();
    //     $arrEveStudents = $statement->fetchAll();
    //     // dump($arrEveStudents);
    //     // die;
    //     return $arrEveStudents;        

    // }    


    // public function closeEventCheesAction(Request $request) {
    //     // DUMP($request);die;
    //     // get the send values
    //     $sie = $request->get('sie');        
    //     //conexion to DB
    //     $em = $this->getDoctrine()->getManager();
    //     // $em->getConnection()->beginTransaction();
    //     try {
    //       //save the log data
          
    //       // dump(is_object($objDownloadFilenewOpe));die;
    //       $objDownloadFilenewOpe = $this->checkOperativeChees($sie);
    //       if(!is_object($objDownloadFilenewOpe)){
    //         $objDownloadFilenewOpe = new InstitucioneducativaOperativoLog();
    //       }
          
    //       $objDownloadFilenewOpe->setInstitucioneducativaOperativoLogTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLogTipo')->find(12));
    //       $objDownloadFilenewOpe->setGestionTipoId($this->session->get('currentyear'));
    //       $objDownloadFilenewOpe->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
    //       $objDownloadFilenewOpe->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));
    //       $objDownloadFilenewOpe->setInstitucioneducativaSucursal(0);
    //       $objDownloadFilenewOpe->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(0));
    //       $objDownloadFilenewOpe->setDescripcion('CENSO INE');
    //       $objDownloadFilenewOpe->setEsexitoso('t');
    //       $objDownloadFilenewOpe->setEsonline('t');
    //       $objDownloadFilenewOpe->setConsolidacionValor(1);
    //       $objDownloadFilenewOpe->setUsuario($this->session->get('userId'));
    //       $objDownloadFilenewOpe->setFechaRegistro(new \DateTime('now'));
    //       $objDownloadFilenewOpe->setClienteDescripcion($_SERVER['HTTP_USER_AGENT']);
    //       $em->persist($objDownloadFilenewOpe);
    //       $em->flush();
    //        // $em->getConnection()->commit();

    //       $swcloseevent = 1;

    //     } catch (\Exception $ex) {
    //         $swcloseevent = 0;
    //       $em->getConnection()->rollback();
    //       echo 'Excepción capturada: ', $ex->getMessage(), "\n";
    //     }   

    //     $arrResponse = array(
    //         'sie'          => $sie,
    //         'swcloseevent' => $swcloseevent,
    //         'swcloseevent' => $swcloseevent,
    //         'urlreporte'=> $this->generateUrl('cheesevent_reportChessInscription', array('sie'=>$sie))
    //     ); 


    //     $response = new JsonResponse();
    //     $response->setStatusCode(200);
    //     $response->setData($arrResponse);
    //     return $response;                
    // }

    // public function removeInscriptionCensoAction(Request $request){

    //     // get the send values
    //     $sie = $request->get('sie');
    //     $institucioneducativa = $request->get('institucioneducativa');
    //     $remoinscriptionid = $request->get('remoinscriptionid');
    //     // $genderRequest = $request->get('genderRequest');
    //     $year = $this->session->get('currentyear');

    //     // create db conexion
    //     $em=$this->getDoctrine()->getManager();
        
    //     $existRemoveStudent=0;
    //     $objEveStudentInscription = $em->getRepository('SieAppWebBundle:CenEstudianteInscripcionCenso')->find($remoinscriptionid);
    //     // dump($remoinscriptionid);
    //     // dump($objEveStudentInscription);
    //     // die;
    //     if(is_object($objEveStudentInscription) ){
    //         $em->remove($objEveStudentInscription);
    //         // $em->persist($objEveStudentInscription);
    //         $em->flush();
    //         $existRemoveStudent=1;
    //     }
        
    //     $arrEveStudents = $this->getAllRegisteredInscription($sie);

    //     $arrResponse = array(
    //         'sie'            => $sie,
    //         'arrEveStudents' => $arrEveStudents,
    //         'existRemoveStudent'         => $existRemoveStudent,    ); 


    //     $response = new JsonResponse();
    //     $response->setStatusCode(200);
    //     $response->setData($arrResponse);
    //     return $response;  
    // }

    // public function setUEs(){
    //     return array(
    //                     80920043,
    //                     80920053,
    //                     80920026,
    //                     80920022,
    //                     80920052,
    //                     80920020,
    //                     80920051,
    //                     80920054,
    //                     60920002,
    //                     60920003,
    //                     80920001,
    //                     60920001,
    //                     80920005,
    //                     80920008,
    //                     80920009,
    //                     80920012,
    //                     80920011,
    //                     80920013,
    //                     80920014,
    //                     80920015,
    //                     80920016,
    //                     80920017,
    //                     80920006,
    //                     80920024,
    //                     80920049,
    //                     80920033,
    //                     80920036,
    //                     80920047,
    //                     80920055,
    //                     80920039,
    //                     80920023,
    //                     80920027,
    //                     80920028,
    //                     80920029,
    //                     80920030,
    //                     80920031,
    //                     80920032,
    //                     80920034,
    //                     80920035,
    //                     80920037,
    //                     80920041,
    //                     80920046,
    //                     80920004,
    //                     80920019,
    //                     80920010
    //     );
    // }
//////////////////////////////////////////////////////////////////////////////////////
/*





    public function getInfoEventAction(Request $request){
        // get the vars send        
        $modalidadId = $request->get('modalidadId');
        $levelId = $request->get('levelId');
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        // get fases data
        $query = "SELECT * FROM eve_fase_tipo where es_vigente = true and   eve_modalidades_tipo_id = " .$modalidadId  ;              
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrFases = $statement->fetchAll();
        // get categories data
        $query = "SELECT * FROM eve_categorias_tipo where eve_modalidades_tipo_id = " .$modalidadId ." and nivel_tipo_id =".$levelId ;              
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrCategories = $statement->fetchAll();        
        
        $arrResponse = array(
            'arrFases'         => $arrFases,
            'arrCategories'    => $arrCategories,
            'existUE'         => 1,
        ); 
                
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;  
    }



    public function getParalelosAction(Request  $request){
        // get the send values
        $sie = $request->get('sie');
        $institucioneducativa = $request->get('institucioneducativa');
        $modalidadId = $request->get('modalidadId');
        $faseId = $request->get('faseId');
        $categorieId = $request->get('categorieId');
        $levelId = $request->get('levelId');
        $gradeId = $request->get('gradeId');
        $genderRequest = $request->get('genderRequest');
        $year = $this->session->get('currentyear');

        // create db conexion
        $em=$this->getDoctrine()->getManager();
        $query="
            select ic.turno_tipo_id ,  ic.paralelo_tipo_id, pt.paralelo 
            from institucioneducativa_curso ic
            inner join paralelo_tipo pt on (ic.paralelo_tipo_id = pt.id)
            where ic.institucioneducativa_id = $sie and ic.nivel_tipo_id =$levelId and grado_tipo_id = $gradeId and gestion_tipo_id = $year 
            order by ic.paralelo_tipo_id 
        ";

        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrParallels = $statement->fetchAll();        

        // dump($arrParallels);die;

        $arrResponse = array(
            'sie'            => $sie,
            'modalidadId'    => $modalidadId,
            'faseId'         => $faseId,
            'categorieId'    => $categorieId,
            'arrParallels' => $arrParallels,
            'existParall'         => 1,    ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;          
    }

    public function showStudentsAction(Request $request){
        
        // get the send values
        $sie = $request->get('sie');
        $institucioneducativa = $request->get('institucioneducativa');
        $modalidadId = $request->get('modalidadId');
        $faseId = $request->get('faseId');
        $categorieId = $request->get('categorieId');
        $levelId = $request->get('levelId');
        $gradeId = $request->get('gradeId');
        $parallelId = $request->get('parallelId');
        $genderRequest = $request->get('genderRequest');
        $year = $this->session->get('currentyear');

        // create db conexion
        $em=$this->getDoctrine()->getManager();
        $query="
                select e.carnet_identidad, e.complemento ,e.codigo_rude ,e.paterno ,e.materno ,e.nombre ,e.genero_tipo_id , ei.id as inscriptionId, gt.genero
                from estudiante e 
                inner join estudiante_inscripcion ei on (e.id = ei.estudiante_id)
                inner join institucioneducativa_curso ic on (ei.institucioneducativa_curso_id=ic.id)
                inner join genero_tipo gt on (e.genero_tipo_id=gt.id)
                where ic.nivel_tipo_id =$levelId and ic.grado_tipo_id =$gradeId and ic.gestion_tipo_id =$year and e.genero_tipo_id = $genderRequest and ic.paralelo_tipo_id = '".$parallelId."' and ic.institucioneducativa_id =$sie and  ei.estadomatricula_tipo_id = 4
        ";


        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrStudents = $statement->fetchAll();
        $arrStudents2 = array();
        foreach ($arrStudents  as $value) {
            $query = "SELECT * 
                    FROM eve_estudiante_inscripcion_evento eeie
                    where 
                    eeie.eve_categorias_tipo_id = $categorieId and  
                    eeie.eve_fase_tipo_id = $faseId and  
                    eeie.eve_modalidades_tipo_id = $modalidadId and  
                    eeie.estudiante_inscripcion_id = " . $value['inscriptionid'] ;


                $statement = $em->getConnection()->prepare($query);
                $statement->execute();
                $arrRegStudent = $statement->fetchAll();  
                if(sizeof(($arrRegStudent))>0){
                }else{
                    $arrStudents2[]=$value;
                }
             }     

        $arrResponse = array(
            'sie'            => $sie,
            'modalidadId'    => $modalidadId,
            'faseId'         => $faseId,
            'categorieId'    => $categorieId,
            'arrStudents' => $arrStudents2,
            'existStudent'         => 1,    ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;          
    }

    public function doInscriptionAction(Request $request){
        // get the send values
        $sie = $request->get('sie');
        $institucioneducativa = $request->get('institucioneducativa');
        $modalidadId = $request->get('modalidadId');
        $faseId = $request->get('faseId');
        $categorieId = $request->get('categorieId');
        $levelId = $request->get('levelId');
        $gradeId = $request->get('gradeId');
        $parallelId = $request->get('parallelId');
        $inscriptionid = $request->get('inscriptionid');
        $genderRequest = $request->get('genderRequest');
        $year = $this->session->get('currentyear');

        // create db conexion
        $em=$this->getDoctrine()->getManager();
        
        $objEveStudentInscription = new EveEstudianteInscripcionEvento();
        $objEveStudentInscription->setFechaRegistro(new \DateTime('now'));
        $objEveStudentInscription->setEsVigente(1);
        $objEveStudentInscription->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($inscriptionid));
        $objEveStudentInscription->setEveCategoriasTipo($em->getRepository('SieAppWebBundle:EveCategoriasTipo')->find($categorieId));
        $objEveStudentInscription->setEveFaseTipo($em->getRepository('SieAppWebBundle:EveFaseTipo')->find($faseId));
        $objEveStudentInscription->setEveModalidadesTipo($em->getRepository('SieAppWebBundle:EveModalidadesTipo')->find($modalidadId));

        $em->persist($objEveStudentInscription);
        $em->flush();

        $arrEveStudents = $this->getAllRegisteredInscription( $categorieId,$faseId,$modalidadId,$sie);

        $arrResponse = array(
            'sie'            => $sie,
            'modalidadId'    => $modalidadId,
            'faseId'         => $faseId,
            'categorieId'    => $categorieId,
            'arrEveStudents' => $arrEveStudents,
            'existStudent'         => 1,    ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;  
    }






    public function reportChessInscriptionAction(Request $request, $sie){

        $response = new Response();
        $gestion = $this->session->get('currentyear');
        $codigoQR = 'EVEAJE'.$sie.'|'.$gestion;

        $data = $this->session->get('userId').'|'.$gestion.'|'.$sie;
        //$link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($codigoQR);
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'eventChees'.$sie.'_'.$this->session->get('currentyear'). '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') .'reg_lst_registro_ajedrez_v1_EEA_ue.rptdesign&institucioneducativa_id='.$sie.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }     

    public function findStudentAction(Request $request){
        
        // get the vars send        
        $sie = $request->get('sie');
        $institucioneducativa = $request->get('institucioneducativa');
        $modalidadId = $request->get('modalidadId');
        $faseId = $request->get('faseId');
        $categorieId = $request->get('categorieId');
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        
        
        // get students data
        $yearIns = $this->session->get('currentyear');
        $query = "
                select
                e.codigo_rude, e.carnet_identidad ,
                e.nombre, e.paterno, e.materno, ei.id as studentId, nt.nivel, gt.grado, pt.paralelo  
                from estudiante e 
                inner join estudiante_inscripcion ei on (e.id = ei.estudiante_id)
                inner join institucioneducativa_curso ic on (ei.institucioneducativa_curso_id=ic.id)
                inner join nivel_tipo nt on (ic.nivel_tipo_id=nt.id) 
                inner join grado_tipo gt on (ic.grado_tipo_id=gt.id)
                inner join paralelo_tipo pt on (ic.paralelo_tipo_id=pt.id)
                where ic.gestion_tipo_id = ".$yearIns." and ic.institucioneducativa_id = $sie and e.codigo_rude ='".$request->get('codigoRude')."' 
                " ;

        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrStudents = $statement->fetchAll();

        if(sizeof($arrStudents)>0 ){
            $existStudent=1;
            $arrStudents=$arrStudents[0];
        }else{
            $existStudent=0;
            $arrStudents=array();
        }

       

        $arrResponse = array(
            'sie'            => $sie,
            'modalidadId'    => $modalidadId,
            'faseId'         => $faseId,
            'categorieId'    => $categorieId,
            'arrStudents' => $arrStudents,
            'existStudent'         => 1,    ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;          
    }

*/

}
