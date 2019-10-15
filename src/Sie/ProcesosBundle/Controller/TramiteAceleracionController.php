<?php

namespace Sie\ProcesosBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

use Sie\AppWebBundle\Entity\FlujoProceso;
use Sie\AppWebBundle\Entity\FlujoTipo;
use Sie\AppWebBundle\Entity\RolTipo;
use Sie\AppWebBundle\Form\FlujoProcesoType;
use Sie\AppWebBundle\Entity\Tramite;
use Sie\AppWebBundle\Entity\TramiteDetalle;
use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;

/**
 * TramiteAceleracionController controller.
 *
 */
class TramiteAceleracionController extends Controller
{
    public $session;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
    }

    public function nuevoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $flujotipo_id = $request->get('id');
        $rol = $request->getSession()->get('roluser');
        if ($rol != 9 or $flujotipo_id==null) {
            return $this->redirect($this->generateUrl('wf_tramite_index'));
        }//dump($request->getSession()->get('pathSystem'));die;
        $institucioneducativa_id = $request->getSession()->get('ie_id');
        
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select td.tramite_detalle_id from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id where t.id=".$flujotipo_id);
        $query->execute();
        $td = $query->fetch();
        if (isset($td['tramite_detalle_id'])) {//verifica si el tramite fue devuelto
            $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($td['tramite_detalle_id']);
            if($tramiteDetalle->getTramiteEstado()->getId() == 4) {
                $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                    ->select('wfd')
                    ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                    ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
                    ->where('td.tramite='.$flujotipo_id)
                    ->andWhere('fp.orden in (1, 4)')
                    ->andWhere("wfd.esValido=true")
                    ->orderBy("td.flujoProceso")
                    ->getQuery()
                    ->getResult();//dump(json_decode($resultDatos[1]->getdatos())->observacion);die;
                $datos = json_decode($resultDatos[0]->getdatos());
                $observacion = json_decode($resultDatos[1]->getdatos())->observacion;
                $primer_tramite = empty($datos->primer_tramite)?'':$datos->primer_tramite;
                $estudiante_result = $em->getRepository('SieAppWebBundle:Estudiante')->find($datos->estudiante_id);
                $estudiante = array();
                $inscripcion = array();
                $talento = array();
                $grados = array();
                if (!empty($estudiante_result)) {
                    $estudiante_talento = $em->getRepository('SieAppWebBundle:EstudianteTalento')->findOneBy(array('estudiante' => $estudiante_result));
                    $einscripcion_result = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $estudiante_result, 'estadomatriculaTipo' => 4), array('id' => 'DESC'));
                    if (!empty($einscripcion_result)) {
                        $estudianteinscripcion_id = $einscripcion_result->getId();
                        $institucioneducativa = $einscripcion_result->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa();
                        $estudiante = array(
                            'id' => $estudiante_result->getId(),
                            'nombre' => $estudiante_result->getNombre(),
                            'paterno' => $estudiante_result->getPaterno(),
                            'materno' => $estudiante_result->getMaterno(),
                            'cedula' => $estudiante_result->getCarnetIdentidad(),
                            'complemento' => $estudiante_result->getComplemento(),
                            'fecha_nacimiento' => $estudiante_result->getFechaNacimiento()==null?array(date=>''):$estudiante_result->getFechaNacimiento()->format('d/m/Y'),
                            'estudiante_ins_id' => $estudianteinscripcion_id,
                            'estudiante_id' => $estudiante_result->getId()
                        );

                        $codigo_sie = $einscripcion_result->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
                        $nivel_id = $einscripcion_result->getInstitucioneducativaCurso()->getNivelTipo()->getId();
                        $grado_id = $einscripcion_result->getInstitucioneducativaCurso()->getGradoTipo()->getId();
                        $paralelo_id = $einscripcion_result->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
                        $turno_id = $einscripcion_result->getInstitucioneducativaCurso()->getTurnoTipo()->getId();
                        $inscripcion = array(
                            'codigo_sie'=>$codigo_sie, 'nombre_sie'=>$einscripcion_result->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa(),
                            'nivel_id'=>$nivel_id, 'nivel'=>$einscripcion_result->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(),
                            'grado_id'=>$grado_id, 'grado'=>$einscripcion_result->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(),
                            'paralelo_id'=>$paralelo_id, 'paralelo'=>$einscripcion_result->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(),
                            'turno_id'=>$turno_id, 'turno'=>$einscripcion_result->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno()
                        );
                        $talento = array(
                            'tipo_talento' => strtoupper($estudiante_talento->getTalentoTipo()),
                            'puede_acelerar' => $estudiante_talento->getAcelera()==true?'SI':'NO',
                            'centro_tramite' => $estudiante_talento->getInstitucioneducativa()->getInstitucioneducativa(),
                            'informe' => $estudiante_talento->getInforme()
                        );
                        $cantidad = 1;
                        for ($i = $grado_id; $i <= 6; $i++) {
                            $grados[] = array(
                                'cantidad' => $cantidad,
                                'nivel' => $nivel_id,
                                'id' => $i,
                                'grado' => $this->getGrados($nivel_id, $i)
                            );
                            if ($i == 6 and $nivel_id == 12) {
                                $nivel_id = 13;
                                $i = 0;
                            }
                            $cantidad++;
                        }
                    }
                }
                return $this->render('SieProcesosBundle:TramiteAceleracion:devolucion.html.twig', array('institucioneducativa_id'=>$institucioneducativa_id, 'tramite_id'=>$flujotipo_id, 'flujotipo_id'=>$datos->flujotipo_id, 'observacion'=>$observacion, 'primer_tramite'=>$primer_tramite, 'estudiante'=>$estudiante, 'inscripcion'=>$inscripcion, 'talento'=>$talento, 'grados'=>$grados));
            }
        } else {
            return $this->render('SieProcesosBundle:TramiteAceleracion:nuevo.html.twig', array('institucioneducativa_id' => $institucioneducativa_id, 'flujotipo_id' => $flujotipo_id));
        }
    }

    public function buscaEstudianteAction(Request $request) {
        $msg = "existe";
        $rude = trim($request->get('rude'));
        $flujotipo_id = trim($request->get('flujotipo_id'));
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $estudiante_result = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
        $estudiante = array();
        $inscripcion = array();
        $talento = array();
        $grados = array();
        $materiasnotas = array();
        if (!empty($estudiante_result)){
            $estudiante_talento = $em->getRepository('SieAppWebBundle:EstudianteTalento')->findOneBy(array('estudiante' => $estudiante_result));
            if(empty($estudiante_talento)){
                return $response->setData(array('msg' => 'notalento'));
            }
            $einscripcion_result = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $estudiante_result, 'estadomatriculaTipo' => 4), array('id' => 'DESC'));//Evaluar 'estadomatriculaTipo' => 4
            if (!empty($einscripcion_result)) {
                // Verifica si la Unidad Educativa es regular
                if ($einscripcion_result->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativaTipo()->getId() != 1) {
                    return $response->setData(array('msg' => 'noregular'));
                }
                // Verifica si el Estudiante está inscrito en su Unidad Educativa
                if ($einscripcion_result->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId() != $request->getSession()->get('ie_id')) {
                    return $response->setData(array('msg' => 'noue'));
                }
                $estudianteinscripcion_id = $einscripcion_result->getId();
                $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                    ->select('wfd')
                    ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                    ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
                    ->where('fp.flujoTipo='.$flujotipo_id)
                    ->andWhere('fp.orden=1')
                    ->andWhere("wfd.esValido=true")
                    ->orderBy("td.flujoProceso")
                    ->getQuery()
                    ->getResult();
                $valida = 0;
                foreach ($resultDatos as $item) {
                    $datos = json_decode($item->getdatos());
                    if ($datos->estudiante_id == $estudiante_result->getId()) {
                        if (date('Y') == $item->getFechaRegistro()->format('Y')) {
                            $valida = 1;
                            break;
                        } elseif (date('Y') > $item->getFechaRegistro()->format('Y')) {
                            $valida = 2;
                            break;
                        }
                    }
                }
                if ($valida == 1) {
                    return $response->setData(array('msg' => $msg));
                } else {
                    $msg = "exito";
                }
                $estudiante = array(
                    'id' => $estudiante_result->getId(),
                    'nombre' => $estudiante_result->getNombre(),
                    'paterno' => $estudiante_result->getPaterno(),
                    'materno' => $estudiante_result->getMaterno(),
                    'cedula' => $estudiante_result->getCarnetIdentidad(),
                    'complemento' => $estudiante_result->getComplemento(),
                    'fecha_nacimiento' => $estudiante_result->getFechaNacimiento()==null?array(date=>''):$estudiante_result->getFechaNacimiento()->format('d/m/Y'),
                    'estudiante_ins_id' => $estudianteinscripcion_id,
                    'estudiante_id' => $estudiante_result->getId(),
                    'segundo' => $valida
                );

                $codigo_sie = $einscripcion_result->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
                $nivel_id = $einscripcion_result->getInstitucioneducativaCurso()->getNivelTipo()->getId();
                $grado_id = $einscripcion_result->getInstitucioneducativaCurso()->getGradoTipo()->getId();
                $paralelo_id = $einscripcion_result->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
                $turno_id = $einscripcion_result->getInstitucioneducativaCurso()->getTurnoTipo()->getId();
                $inscripcion = array(
                    'codigo_sie'=>$codigo_sie, 'nombre_sie'=>$einscripcion_result->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa(),
                    'nivel_id'=>$nivel_id, 'nivel'=>$einscripcion_result->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(),
                    'grado_id'=>$grado_id, 'grado'=>$einscripcion_result->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(),
                    'paralelo_id'=>$paralelo_id, 'paralelo'=>$einscripcion_result->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(),
                    'turno_id'=>$turno_id, 'turno'=>$einscripcion_result->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno()
                );
                $talento = array(
                    'tipo_talento' => strtoupper($estudiante_talento->getTalentoTipo()),
                    'puede_acelerar' => $estudiante_talento->getAcelera()==true?'SI':'NO',
                    'centro_tramite' => $estudiante_talento->getInstitucioneducativa()->getInstitucioneducativa(),
                    'informe' => $estudiante_talento->getInforme()
                );
                $cantidad = 1;
                for ($i = $grado_id; $i <= 6; $i++) {
                    $grados[] = array(
                        'cantidad' => $cantidad,
                        'nivel' => $nivel_id,
                        'id' => $i,
                        'grado' => $this->getGrados($nivel_id, $i)
                    );
                    if ($i == 6 and $nivel_id == 12) {
                        $nivel_id = 13;
                        $i = 0;
                    }
                    $cantidad++;
                }
            } else {
                $msg = 'noins';
            }
        } else {
            $msg = 'noest';
        }
        return $response->setData(array('msg' => $msg, 'estudiante' => $estudiante, 'inscripcion' => $inscripcion, 'grados' => $grados ,'talento' => $talento));
    }

    private function getGrados($nivel, $grado) {
        $leteral = '';
        switch ($grado) {
            case 1:
                $leteral = 'Primero de '.($nivel==12?'Primaria Comunitaria Vocacional':'Secundaria Comunitaria Productiva');
                break;
            case 2:
                $leteral = 'Segundo de '.($nivel==12?'Primaria Comunitaria Vocacional':'Secundaria Comunitaria Productiva');
                break;
            case 3:
                $leteral = 'Tercero de '.($nivel==12?'Primaria Comunitaria Vocacional':'Secundaria Comunitaria Productiva');
                break;
            case 4:
                $leteral = 'Cuarto de '.($nivel==12?'Primaria Comunitaria Vocacional':'Secundaria Comunitaria Productiva');
                break;
            case 5:
                $leteral = 'Quinto de '.($nivel==12?'Primaria Comunitaria Vocacional':'Secundaria Comunitaria Productiva');
                break;
            default:
                $leteral = 'Sexto de '.($nivel==12?'Primaria Comunitaria Vocacional':'Secundaria Comunitaria Productiva');
                break;
        }
        return $leteral;
    }

    /*
    public function getAsignaturasAction($sie, $nivel, $grado, $paralelo, $turno, $cantidad) {
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $arrayCursos = array();
        $arrayCursos[] = $this->getCursos($sie, $nivel, $grado, $paralelo, $turno);
        for ($i = 1; $i < $cantidad; $i++) {
            if ($nivel == 11) {
                if ($grado == 1) {
                    $grado++;
                } elseif ($grado == 2) {
                    $nivel = 12;
                    $grado = 1;
                }
            } elseif ($nivel == 12) {
                if (in_array($grado, [1, 2, 3, 4, 5])) {
                    $grado++;
                } elseif ($grado == 6) {
                    $nivel = 13;
                    $grado = 1;
                }
            } elseif ($nivel == 13) {
                if (in_array($grado, [1, 2, 3, 4, 5])) {
                    $grado++;
                } elseif ($grado == 6) {
                    $nivel = 13;
                    $grado = 1;
                }
            }
            //Llamar a la funcion cursos
            $arrayCursos[] = $this->getCursos($sie, $nivel, $grado, $paralelo, $turno);
        }
        try {
            $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
            $query = $entity->createQueryBuilder('iec')
                ->select('ast.id', 'ast.asignatura, ieco.id as iecoId')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco', 'WITH', 'iec.id=ieco.insitucioneducativaCurso')
                ->leftjoin('SieAppWebBundle:AsignaturaTipo', 'ast', 'WITH', 'ieco.asignaturaTipo=ast.id')
                ->leftjoin('SieAppWebBundle:AreaTipo', 'at', 'WITH', 'ast.areaTipo = at.id')
                ->where('iec.institucioneducativa= :sie')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andwhere('iec.turnoTipo = :turno')
                ->setParameter('sie', $sie)
                ->setParameter('gestion', $this->session->get('currentyear'))
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('turno', $turno)
                ->orderBy('at.id,ast.id')
                ->getQuery();
            return $response->setData(array('asignaturas' => $query->getResult(), 'cursos' => $arrayCursos));
        } catch (Exception $ex) {
            return $ex;
        }
    }
    */

    public function getcursos($sie, $nivel, $grado, $paralelo, $turno) {
        $em = $this->getDoctrine()->getManager();
        $paralelos = array();
        $queryParalelo = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->createQueryBuilder('iec')
            ->select('IDENTITY(iec.paraleloTipo) as paraleloTipo', 'pt.paralelo as paralelo')
            ->leftjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'iec.paraleloTipo = pt.id')
            ->where('iec.institucioneducativa = :id')
            ->andwhere('iec.nivelTipo = :nivel')
            ->andwhere('iec.gradoTipo = :grado')
            ->andwhere('iec.gestionTipo = :gestion')
            ->setParameter('id', $sie)
            ->setParameter('nivel', $nivel)
            ->setParameter('grado', $grado)
            ->setParameter('gestion', $this->session->get('currentyear'))
            ->distinct()
            ->orderBy('iec.paraleloTipo', 'ASC')
            ->getQuery();
        $turnos = array();
        $queryTurno = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->createQueryBuilder('iec')
            ->select('IDENTITY(iec.turnoTipo) as turnoTipo', 'pt.turno as turno')
            ->leftjoin('SieAppWebBundle:turnoTipo', 'pt', 'WITH', 'iec.turnoTipo = pt.id')
            ->where('iec.institucioneducativa = :id')
            ->andwhere('iec.nivelTipo = :nivel')
            ->andwhere('iec.gradoTipo = :grado')
            ->andwhere('iec.gestionTipo = :gestion')
            ->setParameter('id', $sie)
            ->setParameter('nivel', $nivel)
            ->setParameter('grado', $grado)
            ->setParameter('gestion', $this->session->get('currentyear'))
            ->distinct()
            ->orderBy('iec.turnoTipo', 'ASC')
            ->getQuery();
        $nivel_tipo = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel);
        $grado_tipo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado);
        $iecurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('institucioneducativa' => $sie, 'nivelTipo' => $nivel, 'gradoTipo' => $grado, 'gestionTipo' => $this->session->get('currentyear')));
        return array(
            'codigo_sie'=>$sie, 'nombre_sie'=>$iecurso->getInstitucioneducativa()->getInstitucioneducativa(),
            'nivel_id'=>$nivel, 'nivel'=>($nivel_tipo)?$nivel_tipo->getNivel():'',//$restudianteinst->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(),
            'grado_id'=>$grado, 'grado'=>($grado_tipo)?$grado_tipo->getGrado():'',//$restudianteinst->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(),
            // 'paralelo_id'=>$paralelo_id, 'paralelo'=>$restudianteinst->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(),
            'paralelos'=>$queryParalelo->getResult(),
            // 'turno_id'=>$turno_id, 'turno'=>$restudianteinst->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno(),
            'iec_id'=>($iecurso)?$iecurso->getId():'',
            'turnos'=>$queryTurno->getResult()
        );
    }

    public function guardaNuevoAction(Request $request) {
        $estado = 200;
        $tramite = 0;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        // $path = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
        $destination_path = 'uploads/archivos/flujos/tramite/aceleracion/';
        if(!file_exists($destination_path)) { 
            mkdir($destination_path, 0777, true);
        }
        $cant = count($request->files->get('documento'));
        $doc_sol = $doc_com = '';
        for($i = 0; $i < $cant; $i++) {
            if($i == 0) {
                $doc_sol = date('YmdHis').$i.'.'.$request->files->get('documento')[$i]->getClientOriginalExtension();
                $request->files->get('documento')[$i]->move($destination_path, $doc_sol);
            } else {
                $doc_com = date('YmdHis').$i.'.'.$request->files->get('documento')[$i]->getClientOriginalExtension();
                $request->files->get('documento')[$i]->move($destination_path, $doc_com);
            }
        }
        // $documentoSol = $request->files->get('solicitud_tutor');
        // if(!empty($documentoSol)) {
        //     $doc_sol = date('YmdHis').'1.'.$documentoSol->getClientOriginalExtension();
        //     $documentoSol->move($destination_path, $doc_sol);
        // }else{
        //     $doc_sol='default-2x.pdf';
        // }
        $datos = array();
        $datos['estudiante_id'] = $request->get('estudiante_id');
        $datos['estudiante_ins_id'] = $request->get('estudiante_ins_id');
        $datos['flujotipo_id'] = $request->get('flujotipo_id');
        $datos['institucioneducativa_id'] = $request->get('institucioneducativa_id');
        $datos['fecha_solicitud'] = date('d/m/Y');
        $datos['primer_tramite'] = $request->get('primer_tramite');
        $datos['grado_cantidad'] = $request->get('grado_cantidad');
        $datos['grado_acelerar'] = $request->get('grado_acelerar');
        $datos['grado_inscripcion'] = $request->get('grado_inscripcion');
        $datos['sie_destino'] = $request->get('sie_destino');
        $datos['procede_aceleracion'] = $request->get('procede_aceleracion');
        $datos['informe'] = $request->get('informe');        
        $datos['solicitud_tutor'] = $doc_sol;
        $datos['informe_comision'] = $doc_com;
        $datos['devolucion'] = $request->get('devolucion');
        $usuario_id = $request->getSession()->get('userId');
        $rol_id = $request->getSession()->get('roluser');
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $datos['flujotipo_id'], 'orden' => 1));
        $flujo_tipo = $flujoproceso->getFlujoTipo()->getId();
        $tarea_id = $flujoproceso->getId();
        $tabla = 'institucioneducativa';
        $institucioneducativa_id = $datos['institucioneducativa_id'];
        $tipotramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs' => 'AA'));
        if ($tipotramite == null) {
            $estado = 500;
            return $response->setData(array('estado' => $estado, 'msg' => 'Tipo de Trámite no habilitado.'));
        }
        $observaciones = $datos['devolucion']==0?'Inicio solicitud de aceleracion educativa':'Reinicio solicitud de aceleracion educativa';
        $tipotramite_id = $tipotramite->getId();
        $tramite_id = $request->get('tramite_id')==null?'':$request->get('tramite_id');
        $distrito_id = 0;
        $lugarlocalidad_id = 0;
        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->getSession()->get('ie_id'));//$institucioneducativa_id
        if ($ieducativa) {
            $distrito_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            $lugarlocalidad_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        }
        if ($datos['devolucion'] == 0) {
            $result = $this->get('wftramite')->guardarTramiteNuevo($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $tipotramite_id, $datos['procede_aceleracion'], $tramite_id, json_encode($datos), $lugarlocalidad_id, $distrito_id);
            if ($result) {
                $tramite_id = $result['idtramite'];
            }
        } else {
            $result = $this->get('wftramite')->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $datos['procede_aceleracion'], $tramite_id, json_encode($datos), $lugarlocalidad_id, $distrito_id);
        }
        if ($result['dato'] == true) {
            $msg = $result['msg'];
            $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $datos['flujotipo_id'], 'orden' => 2));
            $tarea_sig = $flujoproceso->getId();
            $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario_id, $tarea_sig, $tramite_id);
            if ($mensaje['dato'] == true) {
                $msg = $mensaje['msg'];
            } else {
                // eliminar guardarTramiteNuevo / guardarTramiteEnviado
                if ($datos['devolucion'] == 0) {
                    $result_el = $this->get('wftramite')->eliminarTramiteNuevo($tramite_id);
                } else {
                    $result_el = $this->get('wftramite')->eliminarTramiteEnviado($tramite_id, $usuario_id);
                }
            }
        } else {
            $estado = 500;
            $msg = $result['msg'];
        }
        return $response->setData(array('estado' => $estado, 'msg' => $msg, 'tramite' => $tramite_id));
    }

    public function supletorioAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tramite_id = $request->get('id');
        $notificar = 'NO';
        $rol = $request->getSession()->get('roluser');
        if ($rol != 9 or $tramite_id == null) {
            return $this->redirect($this->generateUrl('wf_tramite_index'));
        }
        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
            ->where('td.tramite='.$tramite_id)
            ->andWhere('fp.orden=1')
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getSingleResult();
        $datos = json_decode($resultDatos->getdatos());
        $restudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($datos->estudiante_id);

        $restudianteinst = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante'=>$restudiante), array('id'=>'DESC'));
        $codigo_sie = $restudianteinst->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $nivel_id = $restudianteinst->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $grado_id = $restudianteinst->getInstitucioneducativaCurso()->getGradoTipo()->getId();
        $paralelo_id = $restudianteinst->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
        $turno_id = $restudianteinst->getInstitucioneducativaCurso()->getTurnoTipo()->getId();

        $arrayActas = array();
        for ($i=1; $i < $datos->grado_cantidad; $i++) {
            $paralelos = array();
            $queryParalelo = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->createQueryBuilder('iec')
                ->select('IDENTITY(iec.paraleloTipo) as paraleloTipo', 'pt.paralelo as paralelo')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'iec.paraleloTipo = pt.id')
                ->where('iec.institucioneducativa = :id')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('id', $codigo_sie)
                ->setParameter('nivel', $nivel_id)
                ->setParameter('grado', $grado_id)
                ->setParameter('gestion', $this->session->get('currentyear'))
                ->distinct()
                ->orderBy('iec.paraleloTipo', 'ASC')
                ->getQuery();
            $turnos = array();
            $queryTurno = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->createQueryBuilder('iec')
                ->select('IDENTITY(iec.turnoTipo) as turnoTipo', 'pt.turno as turno')
                ->leftjoin('SieAppWebBundle:turnoTipo', 'pt', 'WITH', 'iec.turnoTipo = pt.id')
                ->where('iec.institucioneducativa = :id')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('id', $codigo_sie)
                ->setParameter('nivel', $nivel_id)
                ->setParameter('grado', $grado_id)
                ->setParameter('gestion', $this->session->get('currentyear'))
                ->distinct()
                ->orderBy('iec.turnoTipo', 'ASC')
                ->getQuery();
            if ($nivel_id == 13) {
                $codigo_sie = $datos->sie_destino;
            }
            $materiasnotas = $this->getAsignaturasPerStudent($codigo_sie, $nivel_id, $grado_id, $paralelo_id, $turno_id);
            if (count($materiasnotas) == 0) {
                $paralelo_id = 1; //Si no hay cursos en el paralelo por defecto, se indica el paralelo A
                $materiasnotas = $this->getAsignaturasPerStudent($codigo_sie, $nivel_id, $grado_id, $paralelo_id, $turno_id);
                if (count($materiasnotas) == 0) {
                    if ($turno_id == 1) { //Si no hay curso en el turno por defecto, se cambia el turno
                        $turno_id = 2;
                    } else {
                        $turno_id = 1;
                    }
                    $materiasnotas = $this->getAsignaturasPerStudent($codigo_sie, $nivel_id, $grado_id, $paralelo_id, $turno_id);
                }
            }
            $nivel_tipo = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel_id);
            $grado_tipo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado_id);
            $iecurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('institucioneducativa' => $codigo_sie, 'nivelTipo' => $nivel_id, 'gradoTipo' => $grado_id, 'paraleloTipo' => $paralelo_id, 'turnoTipo' => $turno_id, 'gestionTipo' => $this->session->get('currentyear')));
            $arrayActas[] = array(
                'curso' => array(
                    'codigo_sie'=>$codigo_sie, 'nombre_sie'=>$restudianteinst->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa(),
                    'nivel_id'=>$nivel_id, 'nivel'=>($nivel_tipo)?$nivel_tipo->getNivel():'',//$restudianteinst->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(),
                    'grado_id'=>$grado_id, 'grado'=>($grado_tipo)?$grado_tipo->getGrado():'',//$restudianteinst->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(),
                    'paralelo_id'=>$paralelo_id, 'paralelo'=>$restudianteinst->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(),
                    'paralelos'=>$queryParalelo->getResult(),
                    'turno_id'=>$turno_id, 'turno'=>$restudianteinst->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno(),
                    'turnos'=>$queryTurno->getResult(),
                    'iec_id'=>($iecurso)?$iecurso->getId():0),
                'materiasnotas' => $materiasnotas
            );
            if ($nivel_id == 11) {
                if ($grado_id == 1) {
                    $grado_id++;
                } elseif ($grado_id == 2) {
                    $nivel_id = 12;
                    $grado_id = 1;
                }
            } elseif ($nivel_id == 12) {
                if (in_array($grado_id, [1, 2, 3, 4, 5])) {
                    $grado_id++;
                } elseif ($grado_id == 6) {
                    $nivel_id = 13;
                    $grado_id = 1;
                }
            } elseif ($nivel_id == 13) {
                if (in_array($grado_id, [1, 2, 3, 4, 5])) {
                    $grado_id++;
                } elseif ($grado_id == 6) {
                    $nivel_id = 13;
                    $grado_id = 1;
                }
            }
        }
        
        $estudiante = $restudiante->getNombre().' '.$restudiante->getPaterno().' '.$restudiante->getMaterno();
        $rude = $restudiante->getCodigoRude();
        $informe = $datos->informe;
        if ($datos->procede_aceleracion == "SI") {
            return $this->render('SieProcesosBundle:TramiteAceleracion:supletorio.html.twig', array('tramite_id' => $tramite_id, 'institucioneducativa_id'=>$datos->institucioneducativa_id, 'rude' => $rude, 'estudiante' => $estudiante, 
            'procede_aceleracion' => $datos->procede_aceleracion, 'grado_cantidad' => $datos->grado_cantidad, 'grado_acelerar' => $datos->grado_acelerar, 'grado_inscripcion' => $datos->grado_inscripcion, 'informe' => $informe, 'solicitud_tutor' => $datos->solicitud_tutor, 'informe_comision' => $datos->informe_comision,
            'actas' => $arrayActas, 'notificar' => $notificar, 'devolucion' => $datos->devolucion));
        } else {
            return $this->render('SieProcesosBundle:TramiteAceleracion:observacion.html.twig', array('tramite_id' => $tramite_id, 'institucioneducativa_id'=>$datos->institucioneducativa_id, 'rude' => $rude, 'estudiante' => $estudiante,
            'procede_aceleracion' => $datos->procede_aceleracion, 'grado_cantidad' => $datos->grado_cantidad, 'grado_acelerar' => $datos->grado_acelerar, 'grado_inscripcion' => $datos->grado_inscripcion, 'informe' => $informe, 'solicitud_tutor' => $datos->solicitud_tutor, 'informe_comision' => $datos->informe_comision,
            'actas' => $arrayActas, 'notificar' => $notificar, 'devolucion' => $datos->devolucion));
        }
    }

    public function guardaSupleAction(Request $request) {
        $estado = 200;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        // $destination_path = 'uploads/archivos/flujos/tramite/aceleracion/';
        // $documentoActa = $request->files->get('acta_supletorio');
        // if(!empty($documentoActa)) {
        //     if(!file_exists($destination_path)) { 
        //         mkdir($destination_path, 0777, true);
        //     }
        //     $doc_acta = date('YmdHis').'.'.$documentoActa->getClientOriginalExtension();
        //     $documentoActa->move($destination_path, $doc_acta);
        // }else{
        //     $doc_acta = 'default-2x.pdf';
        // }
        $datos = array();
        $datos['tramite_id'] = $request->get('tramite_id');
        $datos['institucioneducativa_id'] = $request->get('institucioneducativa_id');
        $datos['curso_asignatura_notas'] = $request->get('curso_asignatura_notas');
        $datos['notificar'] = $request->get('notificar');
        $datos['devolucion'] = $request->get('devolucion');
        $datos['comision'] = $request->get('comision');
        $verificaTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
            ->where('td.tramite='.$datos['tramite_id'])
            ->andWhere('fp.orden = 2')
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getResult();
        if ($datos['devolucion']==0 and $verificaTramite) {
            $estado = 500;
            return $response->setData(array('estado' => $estado, 'msg' => 'El trámite ya fue enviado con anterioridad (revise en trámites enviados).'));
        }

        $usuario_id = $request->getSession()->get('userId');
        $rol_id = $request->getSession()->get('roluser');
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($datos['tramite_id']);

        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 2));
        $flujo_tipo = $flujoproceso->getFlujoTipo()->getId();
        $tarea_id = $flujoproceso->getId();
        $tabla = 'institucioneducativa';
        $institucioneducativa_id = $datos['institucioneducativa_id'];
        $tipotramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs' => 'AA'));
        if ($tipotramite == null) {
            $estado = 500;
            return $response->setData(array('estado' => $estado, 'msg' => 'Tipo de Trámite no habilitado.'));
        }
        $observaciones = 'Llenado del Acta Supletoria';
        $tipotramite_id = $tipotramite->getId();
        $evaluacion = '';
        $distrito_id = 0;
        $lugarlocalidad_id = 0;
        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucioneducativa_id);
        if ($ieducativa) {
            $distrito_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            $lugarlocalidad_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        }
        $result = $this->get('wftramite')->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);
        if ($result['dato'] == true) {
            $msg = $result['msg'];
        } else {
            $estado = 500;
            $msg = $result['msg'];
        }
        return $response->setData(array('estado' => $estado, 'msg' => $msg));
    }

    public function observacionAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tramite_id = $request->get('id');
        $rol = $request->getSession()->get('roluser');
        if ($rol == 9 or $tramite_id == null) {
            return $this->redirect($this->generateUrl('wf_tramite_index'));
        }
        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
            ->where('td.tramite='.$tramite_id)
            ->andWhere('fp.orden IN (1, 3)')
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getResult();
        $datos = json_decode($resultDatos[0]->getdatos());
        if (count($resultDatos) > 1){
            $datos2 = json_decode($resultDatos[1]->getdatos());
            $observacion = $datos2==null?'':json_decode($resultDatos[1]->getdatos())->observacion;
        } else {
            $observacion = '';
        }
        $restudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($datos->estudiante_id);
        $estudiante = $restudiante->getNombre().' '.$restudiante->getPaterno().' '.$restudiante->getMaterno();
        $rude = $restudiante->getCodigoRude();
        return $this->render('SieProcesosBundle:TramiteAceleracion:observacion.html.twig', array('tramite_id' => $tramite_id, 'institucioneducativa_id'=>$datos->institucioneducativa_id, 'rude' => $rude, 'estudiante' => $estudiante, 'procede_aceleracion' => $datos->procede_aceleracion, 'grado_cantidad' => $datos->grado_cantidad, 'grado_acelerar' => $datos->grado_acelerar, 'grado_inscripcion' => $datos->grado_inscripcion, 'informe' => $datos->informe, 'solicitud_tutor' => $datos->solicitud_tutor, 'informe_comision' => $datos->informe_comision, 'observacion' => $observacion));
    }

    public function guardaObsAction(Request $request) {
        $estado = 200;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();

        $datos = array();
        $datos['tramite_id'] = $request->get('tramite_id');
        $datos['institucioneducativa_id'] = $request->get('institucioneducativa_id');
        $datos['procede_obs'] = $request->get('procede_obs');
        $datos['observacion'] = $request->get('observacion');

        /* $verificaTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
            ->where('td.tramite='.$datos['tramite_id'])
            ->andWhere('fp.orden = 4')
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getResult();
        if ($verificaTramite) {
            $estado = 500;
            return $response->setData(array('estado' => $estado, 'msg' => 'La observación ya fue enviado con anterioridad (revise en trámites enviados).'));
        } */

        $usuario_id = $request->getSession()->get('userId');
        $rol_id = $request->getSession()->get('roluser');
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($datos['tramite_id']);

        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 4));
        $flujo_tipo = $flujoproceso->getFlujoTipo()->getId();
        $tarea_id = $flujoproceso->getId();
        $tabla = 'institucioneducativa';
        $institucioneducativa_id = $datos['institucioneducativa_id'];
        $tipotramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs' => 'AA'));
        if ($tipotramite == null) {
            $estado = 500;
            return $response->setData(array('estado' => $estado, 'msg' => 'Tipo de Trámite no habilitado.'));
        }
        $observaciones = 'Observación del trámite';
        $tipotramite_id = $tipotramite->getId();
        $evaluacion = $datos['procede_obs'];
        $tramite_id = $datos['tramite_id'];
        $distrito_id = 0;
        $lugarlocalidad_id = 0;
        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucioneducativa_id);
        if ($ieducativa) {
            $distrito_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            $lugarlocalidad_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        }
        // Evaluar devolucion, es a otra funcion
        $result = $this->get('wftramite')->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);
        if ($result['dato'] == true) {
            // if ($evaluacion == "NO") {
            //     $mensaje = $this->get('wftramite')->guardarTramiteRecibido($id_usuario, $tarea1, $tramite_id);
            // }
            $msg = $result['msg'];
        } else {
            $estado = 500;
            $msg = $result['msg'];
        }
        return $response->setData(array('estado' => $estado, 'msg' => $msg));
    }

    public function verificaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tramite_id = $request->get('id');
        $rol = $request->getSession()->get('roluser');
        if ($rol == 9 or $tramite_id==null) {
            return $this->redirect($this->generateUrl('wf_tramite_index'));
        }
        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->where('td.tramite='.$tramite_id)
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getResult();
        $datos1 = json_decode($resultDatos[0]->getdatos());
        $datos2 = json_decode($resultDatos[1]->getdatos());//dump($datos2->curso_asignatura_notas, count(json_decode($datos2->curso_asignatura_notas)));die;
        $restudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($datos1->estudiante_id);
        $estudiante = $restudiante->getNombre().' '.$restudiante->getPaterno().' '.$restudiante->getMaterno();
        $rude = $restudiante->getCodigoRude();

        $restudianteinst = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante'=>$restudiante), array('id'=>'DESC'));
        
        // Obtiene el ultimo código SIE
        $curso_asignatura = json_decode($datos2->curso_asignatura_notas);
        $posicion_sie = count($curso_asignatura);
        $codigo_sie = $curso_asignatura[$posicion_sie-1]->curso->sie;
        $institucion_e = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($codigo_sie);
        $nombre_ie = $institucion_e->getInstitucioneducativa();
        // $codigo_sie = $restudianteinst->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $nivel_id = $restudianteinst->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $grado_id = $restudianteinst->getInstitucioneducativaCurso()->getGradoTipo()->getId();
        $paralelo_id = $restudianteinst->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
        $turno_id = $restudianteinst->getInstitucioneducativaCurso()->getTurnoTipo()->getId();

        for ($i=1; $i < $datos1->grado_cantidad; $i++) {
            if ($nivel_id == 11) {
                if ($grado_id == 1) {
                    $grado_id++;
                } elseif ($grado_id == 2) {
                    $nivel_id = 12;
                    $grado_id = 1;
                }
            } elseif ($nivel_id == 12) {
                if (in_array($grado_id, [1, 2, 3, 4, 5])) {
                    $grado_id++;
                } elseif ($grado_id == 6) {
                    $nivel_id = 13;
                    $grado_id = 1;
                }
            } elseif ($nivel_id == 13) {
                if (in_array($grado_id, [1, 2, 3, 4, 5])) {
                    $grado_id++;
                } elseif ($grado_id == 6) {
                    $nivel_id = 13;
                    $grado_id = 1;
                }
            }
        }
        $queryParalelo = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->createQueryBuilder('iec')
            ->select('IDENTITY(iec.paraleloTipo) as paraleloTipo', 'pt.paralelo as paralelo')
            ->leftjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'iec.paraleloTipo = pt.id')
            ->where('iec.institucioneducativa = :id')
            ->andwhere('iec.nivelTipo = :nivel')
            ->andwhere('iec.gradoTipo = :grado')
            ->andwhere('iec.gestionTipo = :gestion')
            ->setParameter('id', $codigo_sie)
            ->setParameter('nivel', $nivel_id)
            ->setParameter('grado', $grado_id)
            ->setParameter('gestion', $this->session->get('currentyear'))
            ->distinct()
            ->orderBy('iec.paraleloTipo', 'ASC')
            ->getQuery();
        $queryTurno = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->createQueryBuilder('iec')
            ->select('IDENTITY(iec.turnoTipo) as turnoTipo', 'pt.turno as turno')
            ->leftjoin('SieAppWebBundle:turnoTipo', 'pt', 'WITH', 'iec.turnoTipo = pt.id')
            ->where('iec.institucioneducativa = :id')
            ->andwhere('iec.nivelTipo = :nivel')
            ->andwhere('iec.gradoTipo = :grado')
            ->andwhere('iec.gestionTipo = :gestion')
            ->setParameter('id', $codigo_sie)
            ->setParameter('nivel', $nivel_id)
            ->setParameter('grado', $grado_id)
            ->setParameter('gestion', $this->session->get('currentyear'))
            ->distinct()
            ->orderBy('iec.turnoTipo', 'ASC')
            ->getQuery();
        $nivel_tipo = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel_id);
        $grado_tipo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado_id);
        $iecurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('institucioneducativa' => $codigo_sie, 'nivelTipo' => $nivel_id, 'gradoTipo' => $grado_id, 'paraleloTipo' => $paralelo_id, 'turnoTipo' => $turno_id, 'gestionTipo' => $this->session->get('currentyear')));
        if (empty($iecurso)) {
            if ($paralelo_id > 1) {
                $paralelo_id = 1;
            }
            if ($turno_id > 1) {
                $turno_id = 1;
            }
            $iecurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('institucioneducativa' => $codigo_sie, 'nivelTipo' => $nivel_id, 'gradoTipo' => $grado_id, 'paraleloTipo' => $paralelo_id, 'turnoTipo' => $turno_id, 'gestionTipo' => $this->session->get('currentyear')));
        }
        $cursoActual = array(
            'codigo_sie' => $codigo_sie,
            'nombre_sie' => $nombre_ie,//$restudianteinst->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa(),
            'nivel_id' => $nivel_id,
            'nivel' => ($nivel_tipo)?$nivel_tipo->getNivel():'',
            'grado_id' => $grado_id,
            'grado' => ($grado_tipo)?$grado_tipo->getGrado():'',
            'paralelo_id' => $paralelo_id,
            'paralelos' => $queryParalelo->getResult(),
            'turno_id' => $turno_id,
            'turnos' => $queryTurno->getResult(),
            'iec_id' => ($iecurso)?$iecurso->getId():0
        );
        $arrayActas = array();
        foreach (json_decode($datos2->curso_asignatura_notas) as $key => $item) {
            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($item->curso->sie);
            $nivel = $em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($item->curso->nivel_id);
            $grado = $em->getRepository('SieAppWebBundle:GradoTipo')->findOneById($item->curso->grado_id);
            $paralelo = $em->getRepository('SieAppWebBundle:ParaleloTipo')->findOneById($item->curso->paralelo_id);
            $turno = $em->getRepository('SieAppWebBundle:TurnoTipo')->findOneById($item->curso->turno_id);
            $arrayActas[] = array(
                'curso' => array('sie'=>$item->curso->sie, 'nombre_sie'=>$institucioneducativa->getInstitucionEducativa(), 'iec_id'=>$item->curso->iec_id, 'nivel_id'=>$item->curso->nivel_id, 'nivel'=>$nivel->getNivel(), 'grado_id'=>$item->curso->grado_id, 'grado'=>$grado->getGrado(), 'paralelo_id'=>$item->curso->paralelo_id, 'paralelo'=>$paralelo->getParalelo(), 'turno_id'=>$item->curso->turno_id, 'turno'=>$turno->getTurno()),
                'asignatura_notas' => $item->asignatura_notas,
            );
        }

        return $this->render('SieProcesosBundle:TramiteAceleracion:verifica.html.twig', array(
            'tramite_id' => $tramite_id,
            'institucioneducativa_id'=>$datos1->institucioneducativa_id,
            'codigo_sie'=>$datos1->institucioneducativa_id,
            // 'codigo_sie'=>$restudianteinst->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId(),
            'rude' => $rude,
            'estudiante' => $estudiante,
            'procede_aceleracion' => $datos1->procede_aceleracion,
            'grado_cantidad' => $datos1->grado_cantidad,
            'grado_acelerar' => $datos1->grado_acelerar,
            'grado_inscripcion' => $datos1->grado_inscripcion,
            'informe' => $datos1->informe,
            'solicitud_tutor' => $datos1->solicitud_tutor,
            'informe_comision' => $datos1->informe_comision,
            'actas' => $arrayActas,
            'cursoactual' => $cursoActual
        ));
    }

    public function guardaVerificaAction(Request $request) {
        $estado = 200;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();

        $datos = array();
        $datos['tramite_id'] = $request->get('tramite_id');
        $datos['institucioneducativa_id'] = $request->get('institucioneducativa_id');
        $datos['tiene_obs'] = $request->get('tiene_obs');
        $datos['observacion'] = $request->get('observacion');
        if ($datos['tiene_obs'] == "NO") {
            $datos['cursoactual'] = $request->get('cursoactual');
        }
        /* $verificaTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
            ->where('td.tramite='.$datos['tramite_id'])
            ->andWhere('fp.orden in (3,4,5)')
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getResult();
        if ($verificaTramite) {
            $estado = 500;
            return $response->setData(array('estado' => $estado, 'msg' => 'El trámite ya fue enviado y/o concluido con anterioridad (revise en trámites enviados).'));
        } */
        $usuario_id = $request->getSession()->get('userId');
        $rol_id = $request->getSession()->get('roluser');
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($datos['tramite_id']);

        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 3));
        $flujo_tipo = $flujoproceso->getFlujoTipo()->getId();
        $tarea_id = $flujoproceso->getId();
        
        $tabla = 'institucioneducativa';
        $institucioneducativa_id = $datos['institucioneducativa_id'];
        $tipotramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs' => 'AA'));
        if ($tipotramite == null) {
            $estado = 500;
            return $response->setData(array('estado' => $estado, 'msg' => 'Tipo de Trámite no habilitado.'));
        }
        $observaciones = 'Datos verificados para aceleración educativa';
        $tipotramite_id = $tipotramite->getId();
        $evaluacion = $datos['tiene_obs'];
        $distrito_id = 0;
        $lugarlocalidad_id = 0;
        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucioneducativa_id);
        if ($ieducativa) {
            $distrito_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            $lugarlocalidad_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        }

        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->where('td.tramite='.$datos['tramite_id'])
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getResult();
        $datos1 = json_decode($resultDatos[0]->getdatos());
        $datos2 = json_decode($resultDatos[1]->getdatos());

        // dump($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);die;
        $result = $this->get('wftramite')->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);
        if ($result['dato'] == true) {
            if ($evaluacion == "NO") {
                $em->getConnection()->beginTransaction();
                try {
                    foreach (json_decode($datos2->curso_asignatura_notas) as $filac => $itemcurso) {
                        $nota_tipo = 5;
                        $estado_matricula = 58;
                        if ($filac == 0) {
                            // Iterrado de Asignaturas y Notas
                            foreach ($itemcurso->asignatura_notas as $asignaturanota) {
                                // Registra estudiante_asignatura en caso de no tener
                                $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array('estudianteInscripcion' =>$datos1->estudiante_ins_id, 'institucioneducativaCursoOferta' => $asignaturanota->ieco_id, 'gestionTipo' => $this->session->get('currentyear')));
                                if (empty($estudianteAsignatura)) {
                                    $estudianteAsignatura = new EstudianteAsignatura();
                                    $estudianteAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
                                    $estudianteAsignatura->setFechaRegistro(new \DateTime('now'));
                                    $estudianteAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($datos1->estudiante_ins_id));
                                    $estudianteAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($asignaturanota->ieco_id));
                                    $em->persist($estudianteAsignatura);
                                    $em->flush();
                                }
                                // Registra las notas con NotaTipo = 5 "Promedio Final"
                                $estudianteNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('notaTipo' => $nota_tipo, 'estudianteAsignatura' => $estudianteAsignatura));
                                if (empty($estudianteNota)) {
                                    $estudianteNota = new EstudianteNota();
                                    $estudianteNota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($nota_tipo));
                                    $estudianteNota->setEstudianteAsignatura($estudianteAsignatura);
                                    $estudianteNota->setNotaCuantitativa($asignaturanota->nota);
                                    $estudianteNota->setNotaCualitativa('');
                                    $estudianteNota->setRecomendacion('');
                                    $estudianteNota->setUsuarioId($this->session->get('userId'));
                                    $estudianteNota->setFechaRegistro(new \DateTime('now'));
                                    $estudianteNota->setFechaModificacion(new \DateTime('now'));
                                    $estudianteNota->setObs('');
                                    $em->persist($estudianteNota);
                                    $em->flush();
                                }
                            }
                            // Actualiza el estado de matricula "PROMOVIDO TALENTO EXTRAORDINARIO"
                            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($datos1->estudiante_ins_id);
                            $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($estado_matricula));
                            $em->flush();
                        } else {
                            // Registrar la inscripcion a la institucion educativa que corresponda por "INSCRITO TALENTO EXTRAORDINARIO"
                            $estudianteInscripcion = new EstudianteInscripcion();
                            $estudianteInscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($itemcurso->curso->sie));
                            $estudianteInscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
                            $estudianteInscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
                            $estudianteInscripcion->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($datos1->estudiante_id));
                            $estudianteInscripcion->setCodUeProcedenciaId($datos1->institucioneducativa_id);//$this->session->get('ie_id')
                            // $estudianteInscripcion->setObservacionId(1);
                            $estudianteInscripcion->setObservacion(1);
                            $estudianteInscripcion->setFechaInscripcion(new \DateTime(date('Y-m-d')));
                            $estudianteInscripcion->setFechaRegistro(new \DateTime(date('Y-m-d')));
                            $estudianteInscripcion->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($itemcurso->curso->iec_id));
                            $estudianteInscripcion->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(27));
                            $estudianteInscripcion->setOperativoId(1);
                            $em->persist($estudianteInscripcion);
                            $em->flush();
            
                            //Registra las asignaturas y notas
                            foreach ($itemcurso->asignatura_notas as $asignaturanota) {
                                // Registra estudiante_asignatura en caso de no tener de aceleración
                                $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array('estudianteInscripcion'=>$estudianteInscripcion, 'institucioneducativaCursoOferta'=>$asignaturanota->ieco_id, 'gestionTipo'=>$this->session->get('currentyear')));
                                if (empty($estudianteAsignatura)) {
                                    $estudianteAsignatura = new EstudianteAsignatura();
                                    $estudianteAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
                                    $estudianteAsignatura->setFechaRegistro(new \DateTime('now'));
                                    $estudianteAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($estudianteInscripcion));
                                    $estudianteAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($asignaturanota->ieco_id));
                                    $em->persist($estudianteAsignatura);
                                    $em->flush();
                                }
                                // Registra las notas con NotaTipo = 5 "Promedio Final" de aceleración
                                $estudianteNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('notaTipo'=>$nota_tipo, 'estudianteAsignatura'=>$estudianteAsignatura));
                                if (empty($estudianteNota)) {
                                    $estudianteNota = new EstudianteNota();
                                    $estudianteNota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($nota_tipo));
                                    $estudianteNota->setEstudianteAsignatura($estudianteAsignatura);
                                    $estudianteNota->setNotaCuantitativa($asignaturanota->nota);
                                    $estudianteNota->setNotaCualitativa('');
                                    $estudianteNota->setRecomendacion('');
                                    $estudianteNota->setUsuarioId($this->session->get('userId'));
                                    $estudianteNota->setFechaRegistro(new \DateTime('now'));
                                    $estudianteNota->setFechaModificacion(new \DateTime('now'));
                                    $estudianteNota->setObs('');
                                    $em->persist($estudianteNota);
                                    $em->flush();
                                }
                            }
                            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($estudianteInscripcion);
                            $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($estado_matricula));
                            $em->flush();
                        }
                    }

                    // Inscripcion en Nivel, Grado, Gestion Actual
                    $inscripcionActual = json_decode($datos['cursoactual']);
                    $estudianteInscripcion = new EstudianteInscripcion();
                    $estudianteInscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($inscripcionActual->sie));
                    $estudianteInscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
                    $estudianteInscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
                    $estudianteInscripcion->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($datos1->estudiante_id));
                    $estudianteInscripcion->setCodUeProcedenciaId($datos1->institucioneducativa_id);//$this->session->get('ie_id')
                    // $estudianteInscripcion->setObservacionId(1);
                    $estudianteInscripcion->setObservacion(1);
                    $estudianteInscripcion->setFechaInscripcion(new \DateTime(date('Y-m-d')));
                    $estudianteInscripcion->setFechaRegistro(new \DateTime(date('Y-m-d')));
                    $estudianteInscripcion->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcionActual->iec_id));
                    $estudianteInscripcion->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(27));
                    $estudianteInscripcion->setOperativoId(1);
                    $em->persist($estudianteInscripcion);
                    $em->flush();
                    $em->getConnection()->commit();
                } catch (\Throwable $th) {
                    $em->getConnection()->callback();
                    $resultD = $this->get('wftramite')->eliminarTramiteRecibido($result['tramite_id'], $usuario_id);
                }
                // Registrar los tramites de recepcion
                $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 5));
                $tarea_sig = $flujoproceso->getId();
                $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario_id, $tarea_sig, $datos['tramite_id']);
                if ($mensaje['dato'] == true) {
                    $msg = $mensaje['msg'];
                } else {
                    // Eliminar el tramite anterior si falla guardarTramiteEnviado
                    $resultE = $this->get('wftramite')->eliminarTramiteEnviado($datos['tramite_id'], $usuario_id);
                }
                
                // Preparado de datos de todas las tareas, para guardar
                $tareasDatos = array();
                foreach($resultDatos as $wfd) {
                    $tdatos = json_decode($wfd->getdatos(),true);
                    $tareasDatos[] = array('flujoProceso'=>$wfd->getTramiteDetalle()->getFlujoProceso()->getId(), 'datos'=>$tdatos);
                }
                // Se registra la última tarea.
                $resultf = $this->get('wftramite')->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_sig, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($tareasDatos), $lugarlocalidad_id, $distrito_id);
                if ($resultf['dato'] == true) {
                    $msg = $resultf['msg'];
                } else {
                    $resultR = $this->get('wftramite')->eliminarTramiteRecibido($datos['tramite_id']);
                    $resultE = $this->get('wftramite')->eliminarTramiteEnviado($datos['tramite_id'], $usuario_id);
                    $estado = 500;
                    $msg = $resultf['msg'];
                }
            } else {
                // Recibe el tramite para observacion
                $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 4));
                $tarea_sig = $flujoproceso->getId();
                $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario_id, $tarea_sig, $datos['tramite_id']);
                if ($mensaje['dato'] == true) {
                    $msg = $mensaje['msg'];
                    $observaciones = 'Observación del trámite';
                    $evaluacion = "SI";
                    $resultobs = $this->get('wftramite')->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_sig, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);
                    if ($resultobs['dato'] == true) {
                        $msg = $resultobs['msg'];
                    } else {
                        $resultR = $this->get('wftramite')->eliminarTramiteRecibido($datos['tramite_id']);
                        $estado = 500;
                        $msg = $resultobs['msg'];
                    }
                } else {
                    $resultE = $this->get('wftramite')->eliminarTramiteEnviado($datos['tramite_id'], $usuario_id);
                }
            }
            $msg = $result['msg'];
        } else {
            $estado = 500;
            $msg = $result['msg'];
        }
        return $response->setData(array('estado' => $estado, 'msg' => $msg));
    }

    public function buscaIeAction($id, $nivel, $grado) {
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion(:user_id::INT, :sie::INT, :roluser::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $id);
        $query->bindValue(':roluser', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();

        $nivel_id = 0;
        $nombre_nivel = '';
        $grado_id = 0;
        $nombre_grado = '';
        $paralelo = array();
        $turno = array();
        $iec_id = 0;
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
        if ($institucion) {
            if ($aTuicion[0]['get_ue_tuicion']) {
                $nombreIE = $institucion->getInstitucioneducativa();

                $queryParalelo = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->createQueryBuilder('iec')
                        ->select('IDENTITY(iec.paraleloTipo) as paraleloTipo', 'pt.paralelo as paralelo')
                        ->leftjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'iec.paraleloTipo = pt.id')
                        ->where('iec.institucioneducativa = :id')
                        ->andwhere('iec.nivelTipo = :nivel')
                        ->andwhere('iec.gradoTipo = :grado')
                        ->andwhere('iec.gestionTipo = :gestion')
                        ->setParameter('id', $id)
                        ->setParameter('nivel', $nivel)
                        ->setParameter('grado', $grado)
                        ->setParameter('gestion', $this->session->get('currentyear'))
                        ->distinct()
                        ->orderBy('iec.paraleloTipo', 'ASC')
                        ->getQuery();
                $resultQueryParalelo = $queryParalelo->getResult();
                foreach ($resultQueryParalelo as $info) {
                    $paralelo[$info['paraleloTipo']] = $info['paralelo'];
                }
                $queryTurno = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->createQueryBuilder('iec')
                        ->select('IDENTITY(iec.turnoTipo) as turnoTipo', 'pt.turno as turno')
                        ->leftjoin('SieAppWebBundle:turnoTipo', 'pt', 'WITH', 'iec.turnoTipo = pt.id')
                        ->where('iec.institucioneducativa = :id')
                        ->andwhere('iec.nivelTipo = :nivel')
                        ->andwhere('iec.gradoTipo = :grado')
                        ->andwhere('iec.gestionTipo = :gestion')
                        ->setParameter('id', $id)
                        ->setParameter('nivel', $nivel)
                        ->setParameter('grado', $grado)
                        ->setParameter('gestion', $this->session->get('currentyear'))
                        ->distinct()
                        ->orderBy('iec.turnoTipo', 'ASC')
                        ->getQuery();
                $resultQueryTurno = $queryTurno->getResult();
                foreach ($resultQueryTurno as $info) {
                    $turno[$info['turnoTipo']] = $info['turno'];
                }

                // $nivel_tipo = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel);
                // $grado_tipo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado);
                $iecurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('institucioneducativa' => $id, 'nivelTipo' => $nivel, 'gradoTipo' => $grado, 'gestionTipo' => $this->session->get('currentyear')));
                // $nivel_id = ($nivel_tipo)?$nivel_tipo->getId():'';
                // $nombre_nivel = ($nivel_tipo)?$nivel_tipo->getNivel():'';
                // $grado_id = ($grado_tipo)?$grado_tipo->getId():'';
                // $nombre_grado = ($grado_tipo)?$grado_tipo->getGrado():'';
                $iec_id = ($iecurso)?$iecurso->getId():'';
            } else {
                $nombreIE = '<span class="text-danger">No tiene Tuición sobre la Unidad Educativa<span/>';
            }
        } else {
            $nombreIE = '<span class="text-danger">No existe Unidad Educativa<span/>';
        }
        $response = new JsonResponse();
        return $response->setData(array('nombre'=>$nombreIE, 'paralelo'=>$paralelo, 'turno'=>$turno, 'iec_id'=>$iec_id));
    }

    public function verificaIeAction($sie) {
        $em = $this->getDoctrine()->getManager();
        $estado = 'existe';
        // $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        $institucion = $entity->createQueryBuilder('ie')
            ->select('count(ie.id) as existe')
            ->innerjoin('SieAppWebBundle:InstitucioneducativaNivelAutorizado', 'iena', 'WITH', 'ie.id=iena.institucioneducativa')
            ->where('ie.id= :sie')
            ->andwhere('iena.nivelTipo = :nivel_tipo_id')
            ->setParameter('sie', $sie)
            ->setParameter('nivel_tipo_id', 13)
            ->getQuery()
            ->getResult();
        if ($institucion[0]['existe'] == 0) {
            $estado = 'noexiste';
        }
        $response = new JsonResponse();
        return $response->setData(array('msg'=>$estado, 'aaa'=>$institucion));
    }

    public function buscaCursoAction($id, $nivel, $grado, $paralelo, $turno) {
        $em = $this->getDoctrine()->getManager();

        $nivel_id = 0;
        $nombre_nivel = '';
        $grado_id = 0;
        $nombre_grado = '';
        $paralelo_id = 0;
        $turno_id = 0;
        $nombre_turno = '';
        $materiasnotas = array();
        $iecurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('institucioneducativa' => $id, 'nivelTipo' => $nivel, 'gradoTipo' => $grado, 'paraleloTipo' => $paralelo, 'turnoTipo' => $turno, 'gestionTipo' => $this->session->get('currentyear')));
        if ($iecurso) {
            $iec_id = $iecurso->getId();
            // $nivel_id = $iecurso->getNivelTipo()->getId();
            // $nombre_nivel = $iecurso->getNivelTipo()->getNivel();
            // $grado_id = $iecurso->getGradoTipo()->getId();
            // $nombre_grado = $iecurso->getGradoTipo()->getGrado();
            $materiasnotas = $this->getAsignaturasPerStudent($id, $nivel, $grado, $paralelo, $turno);
        } else {
            $iec_id = 0;
        }
        $response = new JsonResponse();
        // return $response->setData(array('nombre'=>$nombreIE, 'nivel_id'=>$nivel_id, 'nivel'=>$nombre_nivel, 'grado_id'=>$grado_id, 'grado'=>$nombre_grado, 'paralelo'=>$paralelo, 'turno'=>$turno, 'iec_id'=>$iecurso->getId()));
        return $response->setData(array('iec_id'=>$iec_id, 'materiasnotas'=>$materiasnotas));
    }

      /**
     * get the asignaturas per student
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @param type $paralelo
     * @param type $turno
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getAsignaturasPerStudent($sie, $nivel, $grado, $paralelo, $turno) {
        $em = $this->getDoctrine()->getManager();
        try {
            $especialidades = ['1039'];//, '1038'
            $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
            $query = $entity->createQueryBuilder('iec')
                ->select('ast.id', 'ast.asignatura, ieco.id as iecoId')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco', 'WITH', 'iec.id=ieco.insitucioneducativaCurso')
                ->leftjoin('SieAppWebBundle:AsignaturaTipo', 'ast', 'WITH', 'ieco.asignaturaTipo=ast.id')
                ->leftjoin('SieAppWebBundle:AreaTipo', 'at', 'WITH', 'ast.areaTipo = at.id')
                ->where('iec.institucioneducativa= :sie')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andwhere('iec.turnoTipo = :turno')
                ->andwhere('ast.id not in (:especialidades)')
                ->setParameter('sie', $sie)
                ->setParameter('gestion', $this->session->get('currentyear'))
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('turno', $turno)
                ->setParameter('especialidades', $especialidades)
                ->orderBy('at.id,ast.id')
                ->getQuery();
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function rptSolicitudAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tramite_id = $request->get('idtramite');
        $tareadetalle_id = $request->get('id_td');

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($tramite_id);
        $institucioneducativa_id = $tramite->getInstitucioneducativa()->getId();
        $gestion_id = $tramite->getGestionId();

        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->where('td.id='.$tareadetalle_id)
            ->getQuery()
            ->getSingleResult();
        $estudiante_id = json_decode($resultDatos->getdatos())->estudiante_id;
        $filename = $institucioneducativa_id.'TA_Solicitud_'.$gestion_id. '_'.date('YmdHis').'.pdf';

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'aceleracion_educativa_solicitud_v1_amg.rptdesign&&institucioneducativa_id='.$institucioneducativa_id.'&&gestion_id='.$gestion_id.'&&estudiante_id='.$estudiante_id.'&&tramite_id='.$tramite_id.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function rptActaSupletorioAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tramite_id = $request->get('idtramite');
        $tareadetalle_id = $request->get('id_td');

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($tramite_id);
        $institucioneducativa_id = $tramite->getInstitucioneducativa()->getId();
        $gestion_id = $tramite->getGestionId();

        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
            ->where('td.tramite='.$tramite_id)
            ->andWhere('fp.orden=1')
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getSingleResult();
        $estudiante_id = json_decode($resultDatos->getdatos())->estudiante_id;
        $filename = $institucioneducativa_id.'TA_Supletorio_'.$gestion_id. '_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'aceleracion_educativa_supletorio_v1_amg.rptdesign&&institucioneducativa_id='.$institucioneducativa_id.'&&gestion_id='.$gestion_id.'&&estudiante_id='.$estudiante_id.'&&tramite_id='.$tramite_id.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function rptSupletorioAction(Request $request) {
        $pdf = $this->container->get("white_october.tcpdf")->create(
            'PORTRATE', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true
        );
        // $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetAuthor('Adal');
        $pdf->SetTitle('Acta Supletoria');
        $pdf->SetSubject('Report PDF');
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(true, -10);
        // $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 058', PDF_HEADER_STRING, array(10,10,0), array(255,255,255));
        $pdf->SetKeywords('TCPDF, PDF, ACTA SUPLETORIA');
        $pdf->setFontSubsetting(true);
        $pdf->SetMargins(10, 10, 10, true);
        $pdf->SetAutoPageBreak(true, 8);

        $em = $this->getDoctrine()->getManager();
        $tramite_id = $request->get('idtramite');//1670899;
        // $tareadetalle_id = $request->get('id_td');
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($tramite_id);
        $institucioneducativa_id = $tramite->getInstitucioneducativa()->getId();
        $gestion_id = $tramite->getGestionId();

        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
            ->where('td.tramite='.$tramite_id)
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getResult();
        $datos1 = json_decode($resultDatos[0]->getdatos());
        $datos2 = json_decode($resultDatos[1]->getdatos());

        $pdf->SetFont('helvetica', '', 9, '', true);
        $pdf->startPageGroup();
        $pdf->AddPage('P', array(215.9, 274.4));//'P', 'LETTER'
        /* $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $cabecera, $border = 0, $ln = 0, $fill = 0, $reseth = true,
            $align = '', $autopadding = true
        ); */
        //https://www.pngfind.com/pngs/m/387-3875743_formato-png-a-jpg-logotipos-png-transparent-png.png
        //{{absolute_url(asset(\'webEspecial/img/logo/html/logo-white.png\'))}}
        //<span style="font-size: 8px">(Aceleración Educativa)</span>
        //
        //dump(json_decode($datos2->curso_asignatura_notas));die;
        $inscripcion_actual_n = $inscripcion_actual_g = '';
        if ($datos2 and $datos2->curso_asignatura_notas) {
            $curso_actual = json_decode($datos2->curso_asignatura_notas)[0]->curso;
            $nivel_tipo = $em->getRepository('SieAppWebBundle:NivelTipo')->find($curso_actual->nivel_id);
            $grado_tipo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($curso_actual->grado_id);

            $inscripcion_actual_n = '<b>Nivel:</b> '.$nivel_tipo->getNivel();
            $inscripcion_actual_g = '<b>Grado:</b> '.$grado_tipo->getGrado();
        }

        $queryMaestroUE = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->createQueryBuilder('jg')
            ->select("lt4.lugar AS departamento, lt3.lugar AS provincia, lt2.lugar AS seccion, lt1.lugar AS canton, lt.lugar AS localidad,
                        dist.distrito, orgt.orgcurricula,
                        inst.id as sie, inst.institucioneducativa,
                        jg.direccion, jg.zona, CONCAT(prs.paterno, ' ', prs.materno, ' ', prs.nombre) AS maestro, prs.carnet, dep.sigla as expedido, prs.complemento")
            ->join('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.leJuridicciongeografica = jg.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
            ->innerJoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'mi.institucioneducativa = inst.id')
            ->innerJoin('SieAppWebBundle:Persona', 'prs', 'WITH', 'mi.persona = prs.id')
            ->join('SieAppWebBundle:DepartamentoTipo', 'dep', 'WITH', 'prs.expedido = dep.id')
            ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
            ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
            ->where('inst.id = :idInstitucion')
            ->andWhere('mi.gestionTipo = :gestion')
            ->andWhere('mi.cargoTipo = 1')
            ->setParameter('idInstitucion', $institucioneducativa_id)
            ->setParameter('gestion', $gestion_id)
            ->getQuery()
            ->getSingleResult();
        $queryEstudiante = $em->getRepository('SieAppWebBundle:Estudiante')->createQueryBuilder('est')
            ->select("est.codigoRude, est.carnetIdentidad, est.complemento, dep.sigla as expedido, CONCAT(est.paterno, ' ', est.materno, ' ', est.nombre) AS estudiante")
            ->join('SieAppWebBundle:DepartamentoTipo', 'dep', 'WITH', 'est.expedido = dep.id')
            ->where('est.id = :estudiante_id')
            ->setParameter('estudiante_id', $datos1->estudiante_id)
            ->getQuery()
            ->getSingleResult();
        // dump(json_decode($datos2->curso_asignatura_notas));die;

        $image_path = $this->getRequest()->getUriForPath('/images/escudo.jpg');
        $image_path = str_replace("/app_dev.php", "", $image_path);
        $cabecera = '<table border="0">';
        $cabecera .='<tr>';
            $cabecera .='<td width="15%" align="center" style="font-size: 6px"><img src="'.$image_path.'" width="60" height="47"><br><span>Estado Plurinacional de Bolivia</span><br><span>Ministerio de Educación</span></td>';
            $cabecera .='<td width="70%" align="center"><h2>ACTA SUPLETORIA DE PROMOCIÓN PARA<br>TALENTO EXTRAORDINARIO</h2></td>';
            $cabecera .='<td width="15%" align="right"><img src="http://172.20.0.114/index.php?data='.$queryEstudiante['codigoRude'].'|'.$queryEstudiante['carnetIdentidad'].'|'.'Aceleración_Educativa'.'|'.$tramite_id.'" width="66" height="66"></td>';
        $cabecera .='</tr>';
        $cabecera .='<tr>';
            $cabecera .='<td width="50%"><b>Fecha de Trámite: </b>'.$resultDatos[1]->getFechaRegistro()->format('d/m/Y').'</td>';
            $cabecera .='<td width="50%" align="right"><b>Nro. Trámite: </b>'.$tramite_id.'&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $cabecera .='</tr>';
        $cabecera .='</table>';
        $pdf->writeHTML($cabecera, true, false, true, false, '');

        $exist_primaria = $exist_secundaria = false;
        $cantidad_primaria = $cantidad_secundaria = 0;
        $grados_primaria = $grados_secundaria = '';
        $primaria = array(
            'nivel' => '',
            'asignatura' => array(),
            'nota1' => array('','','','','','','','',''),
            'nota2' => array('','','','','','','','',''),
            'nota3' => array('','','','','','','','',''),
            'nota4' => array('','','','','','','','',''),
            'nota5' => array('','','','','','','','',''),
            'nota6' => array('','','','','','','','','')
        );
        $secundaria = array(
            'nivel' => '',
            'asignatura_id' => array(),
            'asignatura' => array(),
            'nota1' => array('','','','','','','','','','','','','',''),
            'nota2' => array('','','','','','','','','','','','','',''),
            'nota3' => array('','','','','','','','','','','','','',''),
            'nota4' => array('','','','','','','','','','','','','',''),
            'nota5' => array('','','','','','','','','','','','','',''),
            'nota6' => array('','','','','','','','','','','','','','')
        );
        $posicion_asig = 0;
        foreach (json_decode($datos2->curso_asignatura_notas) as $indice => $item_nota) {//5 cursos
            $nivel_tipo = $em->getRepository('SieAppWebBundle:NivelTipo')->find($item_nota->curso->nivel_id);
            $grado_tipo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($item_nota->curso->grado_id);
            if ($item_nota->curso->nivel_id == 12) {
                $cantidad_primaria++;
                $exist_primaria = true;
                $primaria['nivel'] = strtoupper($nivel_tipo->getNivel());
                $grados_primaria.='<td align="center"><b>'.$grado_tipo->getGrado().'</b></td>';
                foreach ($item_nota->asignatura_notas as $key => $iteman) {
                    if ($indice == 0) {
                        $primaria['asignatura'][$key] = $iteman->asignatura;
                    }
                    switch ($item_nota->curso->grado_id) {
                        case '1':
                            $primaria['nota1'][$key] = $iteman->nota;
                            break;
                        case '2':
                            $primaria['nota2'][$key] = $iteman->nota;
                            break;
                        case '3':
                            $primaria['nota3'][$key] = $iteman->nota;
                            break;
                        case '4':
                            $primaria['nota4'][$key] = $iteman->nota;
                            break;
                        case '5':
                            $primaria['nota5'][$key] = $iteman->nota;
                            break;
                        case '6':
                            $primaria['nota6'][$key] = $iteman->nota;
                            break;
                        default:
                            # code...
                            break;
                    }
                }
            } else {
                $cantidad_secundaria++;
                $exist_secundaria = true;
                $secundaria['nivel'] = strtoupper($nivel_tipo->getNivel());
                $grados_secundaria.='<td align="center"><b>'.$grado_tipo->getGrado().'</b></td>';
                foreach ($item_nota->asignatura_notas as $key => $iteman) {//10 11 12 asignaturas
                    if (in_array($iteman->asignatura, $secundaria['asignatura'])) {
                        # code...
                        /* switch ($item_nota->curso->grado_id) {
                            case '1':
                                $secundaria['nota1'][$key] = $iteman->nota;
                                break;
                            case '2':
                                $secundaria['nota2'][$key] = $iteman->nota;
                                break;
                            case '3':
                                $secundaria['nota3'][$key] = $iteman->nota;
                                break;
                            case '4':
                                $secundaria['nota4'][$key] = $iteman->nota;
                                break;
                            case '5':
                                $secundaria['nota5'][$key] = $iteman->nota;
                                break;
                            case '6':
                                $secundaria['nota6'][$key] = $iteman->nota;
                                break;
                            default:
                                # code...
                                break;
                        } */
                    } else {
                        $secundaria['asignatura_id'][$posicion_asig] = $iteman->asignatura_id;
                        $secundaria['asignatura'][$posicion_asig] = $iteman->asignatura;
                        /* switch ($item_nota->curso->grado_id) {
                            case '1':
                                $secundaria['nota1'][$posicion_asig] = $iteman->nota;
                                break;
                            case '2':
                                $secundaria['nota2'][$posicion_asig] = $iteman->nota;
                                break;
                            case '3':
                                $secundaria['nota3'][$posicion_asig] = $iteman->nota;
                                break;
                            case '4':
                                $secundaria['nota4'][$posicion_asig] = $iteman->nota;
                                break;
                            case '5':
                                $secundaria['nota5'][$posicion_asig] = $iteman->nota;
                                break;
                            case '6':
                                $secundaria['nota6'][$posicion_asig] = $iteman->nota;
                                break;
                            default:
                                # code...
                                break;
                        } */
                        $posicion_asig++;
                    }
                }
                // llenado de notas
                foreach ($secundaria['asignatura_id'] as $pos => $itemid) {
                    foreach ($item_nota->asignatura_notas as $key => $iteman) {
                        if ($itemid == $iteman->asignatura_id) {
                            switch ($item_nota->curso->grado_id) {
                                case '1':
                                    $secundaria['nota1'][$pos] = $iteman->nota;
                                    break;
                                case '2':
                                    $secundaria['nota2'][$pos] = $iteman->nota;
                                    break;
                                case '3':
                                    $secundaria['nota3'][$pos] = $iteman->nota;
                                    break;
                                case '4':
                                    $secundaria['nota4'][$pos] = $iteman->nota;
                                    break;
                                case '5':
                                    $secundaria['nota5'][$pos] = $iteman->nota;
                                    break;
                                case '6':
                                    $secundaria['nota6'][$pos] = $iteman->nota;
                                    break;
                                default:
                                    # code...
                                    break;
                            }
                        }
                    }
                }
            }
        }
        $custom_fs = '8.5px';
        if ($exist_secundaria) {
            $custom_fs = '8.3px';
        }
        
        $datosTramite='<table border="0" cellpadding="1.5" style="font-size: '.$custom_fs.'">';
        // Datos del estudiante
        $datosTramite.='<tr style="background-color:#ddd;"><td colspan="4" height="14" style="line-height: 14px;"><b>1. Datos del Estudiante</b></td></tr>';
        $datosTramite.='<tr><td><b>Código RUDE:</b></td><td colspan="3">'.$queryEstudiante['codigoRude'].'</td></tr>';
        $datosTramite.='<tr><td><b>Nombre:</b></td><td colspan="3">'.$queryEstudiante['estudiante'].'</td></tr>';
        $datosTramite.='<tr><td><b>Cédula de Indentidad:</b></td><td>'.$queryEstudiante['carnetIdentidad'].' '.$queryEstudiante['expedido'].'</td><td><b>Complemento:</b></td><td>'.$queryEstudiante['complemento'].'</td></tr>';
        // Datos de la unidad educativa
        $datosTramite.='<tr style="background-color:#ddd;"><td colspan="4" height="14" style="line-height: 14px;"><b>2. Datos de la Unidad Educativa</b></td></tr>';
        $datosTramite.='<tr><td><b>Unidad Educativa:</b></td><td colspan="3">'.$queryMaestroUE['sie'].' - '.$queryMaestroUE['institucioneducativa'].'</td></tr>';
        // $datosTramite.='<tr></tr>';
        // Localtizacion
        $datosTramite.='<tr><td><b>Departamento:</b></td><td>'.$queryMaestroUE['departamento'].'</td><td><b>Distrito:</b></td><td>'.$queryMaestroUE['distrito'].'</td></tr>';
        $datosTramite.='<tr><td><b>Inscripción Actual:</b></td><td colspan="2">'.$inscripcion_actual_n.'</td><td>'.$inscripcion_actual_g.'</td></tr>';
        // $datosTramite.='<tr><td><b>Departamento:</b></td><td>'.$queryMaestroUE['departamento'].'</td><td><b>Localidad:</b></td><td>'.$queryMaestroUE['localidad'].'</td></tr>';
        // $datosTramite.='<tr><td><b>Cantón:</b></td><td>'.$queryMaestroUE['canton'].'</td><td><b>Zona:</b></td><td>'.$queryMaestroUE['zona'].'</td></tr>';
        // $datosTramite.='<tr><td><b>Distrito:</b></td><td>'.$queryMaestroUE['distrito'].'</td><td><b>Dirección:</b></td><td>'.$queryMaestroUE['direccion'].'</td></tr>';

        // Datos de talento extraordinario
        $datosTramite.='<tr style="background-color:#ddd;"><td colspan="4" height="14" style="line-height: 14px;"><b>3. Datos del Director(a)</b></td></tr>';
        $datosTramite.='<tr><td><b>Nombre:</b></td><td colspan="3">'.$queryMaestroUE['maestro'].'</td></tr>';
        $datosTramite.='<tr><td><b>Cédula de Indentidad:</b></td><td>'.$queryMaestroUE['carnet'].' '.$queryMaestroUE['expedido'].'</td><td><b>Complemento:</b></td><td>'.$queryMaestroUE['complemento'].'</td></tr>';
        $estudiante_talento = $em->getRepository('SieAppWebBundle:EstudianteTalento')->findOneBy(array('estudiante' => $datos1->estudiante_id));
        // Datos del director
        $datosTramite.='<tr style="background-color:#ddd;"><td colspan="4" height="14" style="line-height: 14px;"><b>4. Datos de Talento Extraordinario</b></td></tr>';
        $datosTramite.='<tr><td><b>Tipo de Talento Extraordinario:</b></td><td>'.strtoupper($estudiante_talento->getTalentoTipo()).'</td><td><b>Puede Acelerar:</b></td><td>'.($estudiante_talento->getAcelera()==true?'SI':'NO').'</td></tr>';
        $datosTramite.='<tr><td><b>Tramitó en Centro Educativo:</b></td><td colspan="3">'.$estudiante_talento->getInstitucioneducativa()->getId().' - '.$estudiante_talento->getInstitucioneducativa()->getInstitucioneducativa().'</td></tr>';
        $datosTramite.='</table><br><br>';
        $pdf->writeHTML($datosTramite, false, false, true, false, '');
        $array_ctp = json_decode($datos2->comision)!=null?json_decode($datos2->comision):array();
        $lista_comision = "";
        foreach ($array_ctp as $key => $value) {
            if ($key == 0){
                $lista_comision .= $value->nombre;
            } else {
                $lista_comision .= ", ".$value->nombre;
            }
        }
        $pdf->writeHTML('<span style="font-size: '.$custom_fs.'"><b>Comisión Técnica Pedagógica: </b>'.$lista_comision.'</span><br><br>', false, false, true, false, '');

        $pdf->writeHTML('<span style="font-size: 8px">RESULTADOS DE LA EVALUACIÓN DE LA COMISIÓN TÉCNICA PEDAGÓGICA:</span>', true, false, true, false, '');//$html, true, 0, true, true

        if ($exist_primaria) {
            $actaSupletorio='<table border="0.5" cellpadding="2" style="font-size: '.$custom_fs.'">';
            $actaSupletorio.='<tr><td width="100%" height="12" style="line-height: 12px;"><b>Nivel: </b>'.$primaria['nivel'].'</td></tr>';
            $actaSupletorio.='<tr style="background-color:#ddd;"><td width="55%" align="center" height="28" style="line-height: 28px;" rowspan="2"><b>Áreas curriculares</b></td><td width="45%" align="center" colspan="'.$cantidad_primaria.'"><b>Grado(s) a Promover</b></td></tr>';
            $actaSupletorio.='<tr style="background-color:#ddd;">'.$grados_primaria.'</tr>';
            foreach ($primaria['asignatura'] as $key => $iteman) {
                $actaSupletorio.='<tr>';
                $actaSupletorio.='<td>'.$iteman.'</td>';
                if($primaria['nota1'][$key]!='') {$actaSupletorio.='<td align="center">'.$primaria['nota1'][$key].'</td>';}
                if($primaria['nota2'][$key]!='') {$actaSupletorio.='<td align="center">'.$primaria['nota2'][$key].'</td>';}
                if($primaria['nota3'][$key]!='') {$actaSupletorio.='<td align="center">'.$primaria['nota3'][$key].'</td>';}
                if($primaria['nota4'][$key]!='') {$actaSupletorio.='<td align="center">'.$primaria['nota4'][$key].'</td>';}
                if($primaria['nota5'][$key]!='') {$actaSupletorio.='<td align="center">'.$primaria['nota5'][$key].'</td>';}
                if($primaria['nota6'][$key]!='') {$actaSupletorio.='<td align="center">'.$primaria['nota6'][$key].'</td>';}
                $actaSupletorio.='</tr>';
            }
            $actaSupletorio.='</table><br><br>';
            $pdf->writeHTML($actaSupletorio, false, false, true, false, '');
        }
        if ($exist_secundaria) {
            $actaSupletorio='<table border="0.5" cellpadding="2" style="font-size: '.$custom_fs.'">';
            $actaSupletorio.='<tr><td width="100%" height="12" style="line-height: 12px;"><b>Nivel: </b>'.$secundaria['nivel'].'</td></tr>';
            $actaSupletorio.='<tr style="background-color:#ddd;"><td width="55%" align="center" height="28" style="line-height: 28px;" rowspan="2"><b>Áreas curriculares</b></td><td width="45%" align="center" colspan="'.$cantidad_secundaria.'"><b>Grado(s) a Promover</b></td></tr>';
            $actaSupletorio.='<tr style="background-color:#ddd;">'.$grados_secundaria.'</tr>';
            foreach ($secundaria['asignatura'] as $key => $iteman) {
                $actaSupletorio.='<tr>';
                $actaSupletorio.='<td>'.$iteman.'</td>';
                $actaSupletorio.='<td align="center">'.$secundaria['nota1'][$key].'</td>';
                $actaSupletorio.='<td align="center">'.$secundaria['nota2'][$key].'</td>';
                $actaSupletorio.='<td align="center">'.$secundaria['nota3'][$key].'</td>';
                $actaSupletorio.='<td align="center">'.$secundaria['nota4'][$key].'</td>';
                $actaSupletorio.='<td align="center">'.$secundaria['nota5'][$key].'</td>';
                $actaSupletorio.='<td align="center">'.$secundaria['nota6'][$key].'</td>';

                /* if($secundaria['nota1'][$key]!='') {$actaSupletorio.='<td align="center">'.$secundaria['nota1'][$key].'</td>';}
                if($secundaria['nota2'][$key]!='') {$actaSupletorio.='<td align="center">'.$secundaria['nota2'][$key].'</td>';}
                if($secundaria['nota3'][$key]!='') {$actaSupletorio.='<td align="center">'.$secundaria['nota3'][$key].'</td>';}
                if($secundaria['nota4'][$key]!='') {$actaSupletorio.='<td align="center">'.$secundaria['nota4'][$key].'</td>';}
                if($secundaria['nota5'][$key]!='') {$actaSupletorio.='<td align="center">'.$secundaria['nota5'][$key].'</td>';}
                if($secundaria['nota6'][$key]!='') {$actaSupletorio.='<td align="center">'.$secundaria['nota6'][$key].'</td>';} */
                $actaSupletorio.='</tr>';
            }
            $actaSupletorio.='</table>';
            $pdf->writeHTML($actaSupletorio, true, false, true, false, '');
        } else {
            $pdf->SetAutoPageBreak(true, 10);
        }
        /* foreach (json_decode($datos2->curso_asignatura_notas) as $itemcan) {
            $nivel_tipo = $em->getRepository('SieAppWebBundle:NivelTipo')->find($itemcan->curso->nivel_id);
            $grado_tipo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($itemcan->curso->grado_id);
            
            $actaSupletorio='<table border="0.5" cellpadding="2" style="font-size: 8.5px">';
            $actaSupletorio.='<tr><td width="100%"><b>Nivel: </b>'.$nivel_tipo->getNivel().'</td></tr>';
            $actaSupletorio.='<tr style="background-color:#ddd;"><td width="55%" align="center" height="28" style="line-height: 28px;" rowspan="2"><b>Áreas curriculares</b></td><td width="45%" align="center" height="14" style="line-height: 14px;" colspan="6"><b>Valoración
                Cuantitativa</b></td></tr>';
            $actaSupletorio.='<tr style="background-color:#ddd;"><td align="center"><b>Primero</b></td><td align="center"><b>Segundo</b></td><td align="center"><b>Tercero</b></td><td align="center"><b>Cuarto</b></td><td align="center"><b>Quinto</b></td><td align="center"><b>Sexto</b></td></tr>';
            foreach ($itemcan->asignatura_notas as $key => $iteman) {
                $actaSupletorio.='<tr>';
                $actaSupletorio.='<td>'.$iteman->asignatura.'</td>';
                $actaSupletorio.='<td align="center">'.$nota_grado11[$key].'</td>';
                $actaSupletorio.='<td align="center">'.$nota_grado12[$key].'</td>';
                $actaSupletorio.='<td align="center">'.$nota_grado13[$key].'</td>';
                $actaSupletorio.='<td align="center">'.$nota_grado14[$key].'</td>';
                $actaSupletorio.='<td align="center">'.$nota_grado15[$key].'</td>';
                $actaSupletorio.='<td align="center">'.$nota_grado16[$key].'</td>';
                $actaSupletorio.='</tr>';
            }
            $actaSupletorio.='</table>';
            $pdf->writeHTML($actaSupletorio, true, false, true, false, '');
        } */
        $pdf->writeHTML('<span>Grado al que estará inscrito: &nbsp;<b>'.$datos1->grado_inscripcion.'.</b><br></span><br>', false, false, true, false, '');
        $pdf->writeHTML('<span>Para su consideración y fines consiguientes, firman los responsables del proceso de Aceleración Educativa.</span>', true, false, true, false, '');
        
        /* $firmas='<table cellpadding="0.5" style="font-size: 8px;">';
        $firmas.='<tr><td align="center">___________________________________<br/>Representante de la Comisión Técnica <br>Pedagógica de la Unidad Educativa</td><td align="center">_____________________________<br/>Directora(or) Unidad Educativa</td><td align="center">________________________________<br/>Directora(or) Distrital de Educación</td></tr>';
        $firmas.='<tr><td align="center">'.$array_ctp[0]->nombre.'</td><td align="center">'.$queryMaestroUE['maestro'].'</td><td>Nombre:</td></tr>';
        $firmas.='<tr><td align="center">Firma</td><td align="center">Sello y Firma</td><td>&nbsp;</td></tr>';
        $firmas.='<tr><td align="right" colspan="3"><br/><span style="font-size: 6px;"><br/>Fecha de Impresión: '.date('d/m/Y H:i:s').'</span></td></tr>';
        $firmas.='</table>';
        $pdf->writeHTML($firmas, true, false, true, false, ''); */
        $firmas='<table cellpadding="0.5" style="font-size: 8px;">';
        $firmas.='<tr><td align="center" width="36%"><br/><br/><br/><br/>___________________________________<br/>Representante de la Comisión Técnica <br>Pedagógica de la Unidad Educativa<br>'.$array_ctp[0]->nombre.'<br>Firma</td>
        <td align="center" width="36%"><br/><br/><br/><br/>_____________________________<br/>Directora(or) Unidad Educativa<br><br>'.$queryMaestroUE['maestro'].'<br>Sello y Firma</td>
        <td align="center" width="28%"><br/><br/><table border="1"><tr><td><br/><br/><br/><br/><br/><br/>VoBo<br/>Directora(or) Distrital de Educación</td></tr></table></td></tr>';
        $firmas.='<tr><td align="right" colspan="3"><br/><span style="font-size: 6px;"><br/>Fecha de Impresión: '.date('d/m/Y H:i:s').'</span></td></tr>';
        $firmas.='</table>';
        $pdf->writeHTML($firmas, true, false, true, false, '');
        //$lugar_fecha='<span style="font-size: 6px;"><br/>Fecha de Impresión: '.date('d/m/Y H:i:s').'</span>';
        //$pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $lugar_fecha, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = 'R', $autopadding = false);
        $pdf->Output($queryMaestroUE['sie']."Acta_Supletoria_".date('YmdHis').".pdf", 'I');
    }

    public function buscaCTPAction(Request $request) {
        $msg = "existe";
        $cedula = trim($request->get('cedula'));
        $flujotipo_id = trim($request->get('flujotipo_id'));
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $persona_result = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('carnet' => $cedula, 'segipId' => 1));
        $nombre = $cargo = '';
        if (!empty($persona_result)) {
            $cedula = $persona_result->getCarnet();
            $nombre = $persona_result->getPaterno().' '.$persona_result->getMaterno().' '.$persona_result->getNombre();
            $cargo = 'Maestro';
        } else {
            $msg = 'noest';
        }
        return $response->setData(array('msg' => $msg, 'cedula' => $cedula, 'nombre' => $nombre, 'cargo' => $cargo));
    }

    private function getMonth($mes) {
        $literal = '';
        switch ($mes) {
            case '01':
                $literal = 'enero';
                break;
            case '02':
                $literal = 'febrero';
                break;
            case '03':
                $literal = 'marzo';
                break;
            case '04':
                $literal = 'abril';
                break;
            case '05':
                $literal = 'mayo';
                break;
            case '06':
                $literal = 'junio';
                break;
            case '07':
                $literal = 'julio';
                break;
            case '08':
                $literal = 'agosto';
                break;
            case '09':
                $literal = 'septiembre';
                break;
            case '10':
                $literal = 'octubre';
                break;
            case '11':
                $literal = 'noviembre';
                break;
            default:
                $literal = 'diciembre';
                break;
        }
        return $literal;
    }
}