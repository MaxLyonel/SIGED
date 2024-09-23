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
        
        
        $em = $this->getDoctrine()->getManager();
        $id_usuario = $this->session->get('userId');
        // dump($id_usuario);die;
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $roluser = $this->session->get('roluser');

        // Si el rol es 9, toma el 'ie_id' de la sesión
        if ($roluser == 9) {
            $ie_id = $this->session->get('ie_id');
        } elseif (in_array($roluser, [8, 7, 10])) {
            $sie = $request->get('sie');
            // verificamos si tiene tuicion
            if (!empty($sie)) {
                $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
                $query->bindValue(':user_id', $this->session->get('userId'));
                $query->bindValue(':sie',  $sie );
                $query->bindValue(':rolId', $this->session->get('roluser'));
                $query->execute();
                $aTuicion = $query->fetchAll();

                if ($aTuicion[0]['get_ue_tuicion']) {
                    $ie_id = $sie;
                } else {
                    $this->get('session')->getFlashBag()->add('searchIe', 'No tiene tuición sobre la Institución Educativa');
                    return $this->render('SieHerramientaBundle:CensoBeneficio:findUe.html.twig');
                }
            }
        } else {
            return $this->redirect($this->generateUrl('login'));
        }

        // Si el 'ie_id' es null o -1, muestra un formulario para seleccionar la unidad educativa
        if (empty($ie_id) || $ie_id == -1) {
            return $this->render('SieHerramientaBundle:CensoBeneficio:findUe.html.twig');
        }
        
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($ie_id);

        $gestion = $request->getSession()->get('currentyear');
      
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

        $operativo = $this->get('funciones')->obtenerOperativo($ie_id,2024);
        // dump($operativo);die;
        return $this->render('SieHerramientaBundle:CensoBeneficio:index.html.twig',array(
            'institucion' => $institucion,
            'estudiante' => $arrEstudiantesCenso,
            'operativo'=>$operativo
            // 'disableElement'=>$disableElement
        )
        );        
    }

    public function formRegistroAction(Request $request, $id){
       
        $roluser = $this->session->get('roluser');

        // Si el rol es 9, toma el 'ie_id' de la sesión
        if (!in_array($roluser, [7])) {
            return $this->redirect($this->generateUrl('login'));
        }
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
            
            $operativo = $this->get('funciones')->obtenerOperativo($arrEstReg[0]['institucioneducativa_id'],2024);
            if ($operativo >= 2) {
                return $response->setData(['error' => 'La Unidad Educativa finalizo el operativo de calificaciones.']);
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
   
    public function findEstudianteAction(Request $request, $id, $sie){

        $id_usuario = $this->session->get('userId');
        // dump($id_usuario);die;
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $roluser = $this->session->get('roluser');
      
        if ($roluser == 9) {
            $ie_id = $sie;
        } elseif (in_array($roluser, [7])) {
            if (!empty($sie)) {
                $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
                $query->bindValue(':user_id', $this->session->get('userId'));
                $query->bindValue(':sie',  $sie );
                $query->bindValue(':rolId', $this->session->get('roluser'));
                $query->execute();
                $aTuicion = $query->fetchAll();

                if ($aTuicion[0]['get_ue_tuicion']) {
                    $ie_id = $sie;
                } else {
                    $this->get('session')->getFlashBag()->add('searchIe', 'No tiene tuición sobre la Institución Educativa');
                    return $this->render('SieHerramientaBundle:CensoBeneficio:findUe.html.twig');
                }
            }
        } else {
            return $this->redirect($this->generateUrl('login'));
        }

        // Si el 'ie_id' es null o -1, muestra un formulario para seleccionar la unidad educativa
        if (empty($ie_id) || $ie_id == -1) {
            return $this->render('SieHerramientaBundle:CensoBeneficio:findUe.html.twig');
        }
        
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
       
    }
    
    public function reporteBeneficioAction(Request  $request) {
    	

        // "roluser" => 7
        // "roluserlugarid" => 31359
        $em = $this->getDoctrine()->getManager();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }        
        $roluserlugarid = $this->session->get('roluserlugarid');

        // $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrEstReg[0]['ei_id']))
        // dump($roluserlugarid);die;
        $lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($roluserlugarid);
// dump($lugar);die;
        if ($this->session->get('roluser') == 10) {
            $wherenw = " where dt.id = '".$lugar->getCodigo()."' ";
        }     
        if ($this->session->get('roluser') == 7) {
            $wherenw = " where lt4.codigo = '".$lugar->getCodigo()."' ";
        }      
        // dump($wherenw);die;
              
        $query = " select lt4.codigo as codigo_departamento,
                                lt4.lugar as departamento,
                                dt.id as codigo_distrito,
                                dt.distrito as distrito,
                                a.institucioneducativa_id,
                                inst.institucioneducativa,
                                a.pen, 
                                a.reg,
                                a.nuevo,
                                (a.pen+a.reg+a.nuevo) total
                    from (
                    SELECT institucioneducativa_id, 
                        SUM(pendiente) AS pen, 
                        SUM(registrados) AS reg, 
                        SUM(nuevo) AS nuevo 
                    FROM (
                        SELECT ic.institucioneducativa_id, 
                            SUM(CASE WHEN cb.id IS NULL THEN 1 ELSE 0 END) AS pendiente, 
                            SUM(CASE WHEN cb.id IS NOT NULL THEN 1 ELSE 0 END) AS registrados, 
                            0 AS nuevo
                        FROM ctr_estudiante_censo cec
                        INNER JOIN estudiante_inscripcion ei ON cec.estudiante_inscripcion_id = ei.id
                        INNER JOIN institucioneducativa_curso ic ON ei.institucioneducativa_curso_id = ic.id
                        LEFT JOIN censo_beneficiario cb ON ei.id = cb.estudiante_inscripcion_id
                        WHERE ic.nivel_tipo_id = 13
                        AND ic.grado_tipo_id IN (4, 5, 6)
                        GROUP BY ic.institucioneducativa_id
                        UNION ALL
                        SELECT cb.institucioneducativa_id, 
                            0 AS pendiente, 
                            0 AS registrados, 
                            COUNT(0) AS nuevo
                        FROM censo_beneficiario cb
                        WHERE cb.nivel_tipo_id = 13
                        AND cb.censo_tabla_id = 0
                        GROUP BY cb.institucioneducativa_id
                    ) AS c
                    GROUP BY institucioneducativa_id) a
                    INNER JOIN institucioneducativa inst ON a.institucioneducativa_id = inst.id
                                INNER JOIN jurisdiccion_geografica jg on jg.id = inst.le_juridicciongeografica_id
                                LEFT JOIN lugar_tipo lt ON lt.id = jg.lugar_tipo_id_localidad
                                LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
                                LEFT JOIN lugar_tipo lt2 ON lt2.id = lt1.lugar_tipo_id
                                LEFT JOIN lugar_tipo lt3 ON lt3.id = lt2.lugar_tipo_id
                                LEFT JOIN lugar_tipo lt4 ON lt4.id = lt3.lugar_tipo_id
                                INNER JOIN distrito_tipo dt ON jg.distrito_tipo_id = dt.id ".$wherenw." order by 1,3, 5";

        $ueregCenso = $em->getConnection()->prepare($query);
        $ueregCenso->execute();
        $arrueregCenso = $ueregCenso->fetchAll();
        // dump($arrueregCenso);die;
        return $this->render('SieHerramientaBundle:CensoBeneficio:reporteIndex.html.twig',array(
            'ue' => $arrueregCenso
            // 'codsie'=>$sie,
            // 'disableElement'=>$disableElement
        )
        );  
     }

    public function verRegistroAction(Request $request, $id){

        $em = $this->getDoctrine()->getManager();
        $query="select nt.nivel, tt.turno, gt.grado, pt.paralelo,
                e.id e_id, e.codigo_rude, (e.nombre ||' '|| e.paterno || ' '|| e.materno) estudiante, 
                case when e.segip_id = 1 then e.carnet_identidad else '' end ci, 
                case when e.segip_id = 1 then e.complemento else '' end complemento,
                e.fecha_nacimiento, cb.estudiante_inscripcion_id ei_id, cb.archivo, cb.institucioneducativa_id sie
                from censo_beneficiario cb 
                inner join estudiante_inscripcion ei on ei.id = cb.estudiante_inscripcion_id
                inner join estudiante e on cb.estudiante_id = e.id 
                inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id = ic.id
                inner join nivel_tipo nt on ic.nivel_tipo_id = nt.id
                inner join grado_tipo gt on ic.grado_tipo_id = gt.id
                inner join turno_tipo tt on ic.turno_tipo_id = tt.id
                inner join paralelo_tipo pt on ic.paralelo_tipo_id = pt.id
                where cb.estudiante_inscripcion_id = $id ";
        $estudiante = $em->getConnection()->prepare($query);
        $estudiante->execute();
        $arrEstudiante = $estudiante->fetchAll();

        // Procesar los archivos almacenados en JSON
        foreach ($arrEstudiante as &$fila) {
            if (!empty($fila['archivo'])) {
                // Decodificar el JSON almacenado en 'archivo'
                $archivoData = json_decode($fila['archivo'], true);
                
                // Obtener los archivos individuales
                $formReg = isset($archivoData['form_reg']) ? $archivoData['form_reg'] : null;
                $certificadoCpv = isset($archivoData['certificado_cpv']) ? $archivoData['certificado_cpv'] : null;
                
            }
        }

        $query="select at2.asignatura, 
                sum(case when cbr.nota_tipo_id = 59 then cbr.nota_cuantitava else 0 end) t2,
                sum(case when cbr.nota_tipo_id = 60 then cbr.nota_cuantitava else 0 end) t3
                from censo_beneficiario cb 
                inner join censo_beneficiario_regular cbr on cb.id = cbr.censo_beneficiario_id 
                inner join asignatura_tipo at2 on cbr.asignatura_tipo_id = at2.id
                where cb.estudiante_inscripcion_id = $id
                group by at2.id 
                order by at2.id; ";
        $areasnotas = $em->getConnection()->prepare($query);
        $areasnotas->execute();
        $arrAreasNotas = $areasnotas->fetchAll();

        $sumatotal = 0;

        foreach ($arrAreasNotas as $nota) {
            // Sumar los valores de t2 y t3
            $sumatotal += $nota['t2'] + $nota['t3'];
        }
    
       
        return $this->render($this->session->get('pathSystem').':CensoBeneficio:ver_registro_puntos.html.twig'
        ,array(
            'estudiante' => $arrEstudiante[0],
            'areasNotas' => $arrAreasNotas,
            'sumaTotal' => $sumatotal,
            'formulario' => $formReg,
            'certificado' => $certificadoCpv
            )
        );
    }

    public function editRegistroAction(Request $request, $id){
        return $this->redirect($this->generateUrl('login'));
        $em = $this->getDoctrine()->getManager();
        $query="select cb.id cb_id, nt.nivel, tt.turno, gt.grado, pt.paralelo,
                e.id e_id, e.codigo_rude, (e.nombre ||' '|| e.paterno || ' '|| e.materno) estudiante, 
                case when e.segip_id = 1 then e.carnet_identidad else '' end ci, 
                case when e.segip_id = 1 then e.complemento else '' end complemento,
                e.fecha_nacimiento, cb.estudiante_inscripcion_id ei_id, cb.archivo, cb.institucioneducativa_id sie
                from censo_beneficiario cb 
                inner join estudiante_inscripcion ei on ei.id = cb.estudiante_inscripcion_id
                inner join estudiante e on cb.estudiante_id = e.id 
                inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id = ic.id
                inner join nivel_tipo nt on ic.nivel_tipo_id = nt.id
                inner join grado_tipo gt on ic.grado_tipo_id = gt.id
                inner join turno_tipo tt on ic.turno_tipo_id = tt.id
                inner join paralelo_tipo pt on ic.paralelo_tipo_id = pt.id
                where cb.estudiante_inscripcion_id = $id ";
        $estudiante = $em->getConnection()->prepare($query);
        $estudiante->execute();
        $arrEstudiante = $estudiante->fetchAll();

        // Procesar los archivos almacenados en JSON
        foreach ($arrEstudiante as &$fila) {
            if (!empty($fila['archivo'])) {
                // Decodificar el JSON almacenado en 'archivo'
                $archivoData = json_decode($fila['archivo'], true);
                
                // Obtener los archivos individuales
                $formReg = isset($archivoData['form_reg']) ? $archivoData['form_reg'] : null;
                $certificadoCpv = isset($archivoData['certificado_cpv']) ? $archivoData['certificado_cpv'] : null;
                
            }
        }

        $query="select at2.id, at2.asignatura, 
                sum(case when cbr.nota_tipo_id = 59 then cbr.nota_cuantitava else 0 end) t2,
                sum(case when cbr.nota_tipo_id = 60 then cbr.nota_cuantitava else 0 end) t3
                from censo_beneficiario cb 
                inner join censo_beneficiario_regular cbr on cb.id = cbr.censo_beneficiario_id 
                inner join asignatura_tipo at2 on cbr.asignatura_tipo_id = at2.id
                where cb.estudiante_inscripcion_id = $id
                group by at2.id, at2.asignatura 
                order by at2.id; ";
        $areasnotas = $em->getConnection()->prepare($query);
        $areasnotas->execute();
        $arrAreasNotas = $areasnotas->fetchAll();

        $sumatotal = 0;

        foreach ($arrAreasNotas as $nota) {
            // Sumar los valores de t2 y t3
            $sumatotal += $nota['t2'] + $nota['t3'];
        }
    
        $query="select at2.id, at2.asignatura 
                from estudiante_inscripcion ei
                inner join estudiante_asignatura ea on ei.id = ea.estudiante_inscripcion_id 
                inner join institucioneducativa_curso_oferta ico on ea.institucioneducativa_curso_oferta_id = ico.id
                inner join asignatura_tipo at2 on at2.id = ico.asignatura_tipo_id 
                where ei.id = $id ";
        $areas = $em->getConnection()->prepare($query);
        $areas->execute();
        $arrAreasEst = $areas->fetchAll();
       
        return $this->render($this->session->get('pathSystem').':CensoBeneficio:edit_registro_puntos.html.twig'
        ,array(
            'estudiante' => $arrEstudiante[0],
            'areasNotas' => $arrAreasNotas,
            'areas' => $arrAreasEst,
            'sumaTotal' => $sumatotal,
            'formulario' => $formReg,
            'certificado' => $certificadoCpv
            )
        );
    }

}
