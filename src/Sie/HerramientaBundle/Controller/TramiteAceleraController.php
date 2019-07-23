<?php

namespace Sie\HerramientaBundle\Controller;

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
 * TramiteAceleraController controller.
 *
 */
class TramiteAceleraController extends Controller
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
                            'estudiante_id' => $estudiante_result->getId(),
                            // 'institucion_educativa' => $institucioneducativa,
                            // 'tipo_talento' => $estudiante_talento->getTalentoTipo(),
                            // 'puede_acelerar' => $estudiante_talento->getAcelera()==true?'SI':'NO',
                            // 'informe' => $estudiante_talento->getInforme()
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
                return $this->render('SieHerramientaBundle:TramiteAcelera:devolucion.html.twig', array('institucioneducativa_id'=>$institucioneducativa_id, 'tramite_id'=>$flujotipo_id, 'flujotipo_id'=>$datos->flujotipo_id, 'observacion'=>$observacion, 'primer_tramite'=>$primer_tramite, 'estudiante'=>$estudiante, 'inscripcion'=>$inscripcion, 'talento'=>$talento, 'grados'=>$grados));
            }
        } else {
            return $this->render('SieHerramientaBundle:TramiteAcelera:nuevo.html.twig', array('institucioneducativa_id' => $institucioneducativa_id, 'flujotipo_id' => $flujotipo_id));
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
            // dump($estudiante_talento);die;
            if(empty($estudiante_talento)){
                return $response->setData(array('msg' => 'notalento'));
            }
            $einscripcion_result = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $estudiante_result, 'estadomatriculaTipo' => 4), array('id' => 'DESC'));//Evaluar 'estadomatriculaTipo' => 4
            if (!empty($einscripcion_result)){
                if ($einscripcion_result->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativaTipo()->getId() != 1) {
                    return $response->setData(array('msg' => 'noregular'));
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
                // dump($resultDatos);die;
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
                $leteral = 'Primero de '.($nivel==12?'primaria':'secundaria');
                break;
            case 2:
                $leteral = 'Segundo de '.($nivel==12?'primaria':'secundaria');
                break;
            case 3:
                $leteral = 'Tercero de '.($nivel==12?'primaria':'secundaria');
                break;
            case 4:
                $leteral = 'Cuarto de '.($nivel==12?'primaria':'secundaria');
                break;
            case 5:
                $leteral = 'Quinto de '.($nivel==12?'primaria':'secundaria');
                break;
            default:
                $leteral = 'Sexto de '.($nivel==12?'primaria':'secundaria');
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
        $observaciones = 'Inicio solicitud de aceleracion educativa';
        $tipotramite_id = $tipotramite->getId();
        $tramite_id = $request->get('tramite_id')==null?'':$request->get('tramite_id');
        $distrito_id = 0;
        $lugarlocalidad_id = 0;
        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->getSession()->get('ie_id'));//$institucioneducativa_id
        if ($ieducativa) {
            $distrito_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            $lugarlocalidad_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        }
        if ($request->get('devolucion') == 0) {
            $result = $this->get('wftramite')->guardarTramiteNuevo($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $tipotramite_id, $datos['procede_aceleracion'], $tramite_id, json_encode($datos), $lugarlocalidad_id, $distrito_id);
        } else {
            $result = $this->get('wftramite')->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $tipotramite_id, $datos['procede_aceleracion'], $tramite_id, json_encode($datos), $lugarlocalidad_id, $distrito_id);
        }
        if ($result['dato'] == true) {
            $msg = $result['msg'];
            $tramite_id = $result['idtramite'];
            $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $datos['flujotipo_id'], 'orden' => 2));
            $tarea_sig = $flujoproceso->getId();
            $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario_id, $tarea_sig, $tramite_id);
            if ($mensaje['dato'] == true) {
                $msg = $mensaje['msg'];
            } else {
                // eliminar guardarTramiteNuevo / guardarTramiteEnviado
                if ($request->get('devolucion') == 0) {
                    $result_el = $this->get('wftramite')->eliminarTramiteNuevo($tramite_id);
                } else {
                    $result_el = $this->get('wftramite')->eliminarTramiteEnviado($tramite_id, $usuario_id);
                }
            }
        } else {
            $estado = 500;
            $tramite_id = '';
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
            return $this->render('SieHerramientaBundle:TramiteAcelera:supletorio.html.twig', array('tramite_id' => $tramite_id, 'institucioneducativa_id'=>$datos->institucioneducativa_id, 'rude' => $rude, 'estudiante' => $estudiante, 
            'procede_aceleracion' => $datos->procede_aceleracion, 'grado_cantidad' => $datos->grado_cantidad, 'grado_acelerar' => $datos->grado_acelerar, 'grado_inscripcion' => $datos->grado_inscripcion, 'informe' => $informe, 'solicitud_tutor' => $datos->solicitud_tutor, 'informe_comision' => $datos->informe_comision,
            'actas' => $arrayActas, 'notificar' => $notificar));
        } else {
            return $this->render('SieHerramientaBundle:TramiteAcelera:observacion.html.twig', array('tramite_id' => $tramite_id, 'institucioneducativa_id'=>$datos->institucioneducativa_id, 'rude' => $rude, 'estudiante' => $estudiante,
            'procede_aceleracion' => $datos->procede_aceleracion, 'grado_cantidad' => $datos->grado_cantidad, 'grado_acelerar' => $datos->grado_acelerar, 'grado_inscripcion' => $datos->grado_inscripcion, 'informe' => $informe, 'solicitud_tutor' => $datos->solicitud_tutor, 'informe_comision' => $datos->informe_comision,
            'actas' => $arrayActas, 'notificar' => $notificar));
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
        // $datos['acta_supletorio'] = $doc_acta;

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
        $observaciones = 'Agrega acta supletorio';
        $tipotramite_id = $tipotramite->getId();
        $evaluacion = '';
        $distrito_id = 0;
        $lugarlocalidad_id = 0;
        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucioneducativa_id);
        if ($ieducativa) {
            $distrito_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            $lugarlocalidad_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        }
        // dump($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);die;
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
        return $this->render('SieHerramientaBundle:TramiteAcelera:observacion.html.twig', array('tramite_id' => $tramite_id, 'institucioneducativa_id'=>$datos->institucioneducativa_id, 'rude' => $rude, 'estudiante' => $estudiante, 'procede_aceleracion' => $datos->procede_aceleracion, 'grado_cantidad' => $datos->grado_cantidad, 'grado_acelerar' => $datos->grado_acelerar, 'grado_inscripcion' => $datos->grado_inscripcion, 'informe' => $datos->informe, 'solicitud_tutor' => $datos->solicitud_tutor, 'informe_comision' => $datos->informe_comision, 'observacion' => $observacion));
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
        // dump($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);die;
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
        $datos2 = json_decode($resultDatos[1]->getdatos());//dump(json_decode($datos2->curso_asignatura_notas));die;
        $restudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($datos1->estudiante_id);
        $estudiante = $restudiante->getNombre().' '.$restudiante->getPaterno().' '.$restudiante->getMaterno();
        $rude = $restudiante->getCodigoRude();

        $restudianteinst = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante'=>$restudiante), array('id'=>'DESC'));
        
        $codigo_sie = $restudianteinst->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $nivel_id = $restudianteinst->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $grado_id = $restudianteinst->getInstitucioneducativaCurso()->getGradoTipo()->getId();

        $paralelo_id = $restudianteinst->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
        $turno_id = $restudianteinst->getInstitucioneducativaCurso()->getTurnoTipo()->getId();

        /* $curso = array(
            'sie'=>$codigo_sie,
            'nombre'=>$restudianteinst->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa(),
            'nivel_id'=>$nivel_id,
            'nivel'=>$restudianteinst->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(),
            'grado_id'=>$grado_id,
            'grado'=>$restudianteinst->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(),
            'paralelo_id'=>$paralelo_id,
            'paralelo'=>$restudianteinst->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(),
            'turno_id'=>$turno_id,
            'turno'=>$restudianteinst->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno(),
            'iec_id'=>$restudianteinst->getInstitucioneducativaCurso()->getId(),
        ); */
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
        // Modificar el curso de inscripcion
        $cursoActual = array(
            'codigo_sie' => $codigo_sie,
            'nombre_sie' => $restudianteinst->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa(),
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

        return $this->render('SieHerramientaBundle:TramiteAcelera:verifica.html.twig', array(
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
        $em->getConnection()->beginTransaction();

        $datos = array();
        $datos['tramite_id'] = $request->get('tramite_id');
        $datos['institucioneducativa_id'] = $request->get('institucioneducativa_id');
        $datos['tiene_obs'] = $request->get('tiene_obs');
        $datos['observacion'] = $request->get('observacion');
        $datos['cursoactual'] = $request->get('cursoactual');

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
            /* $nivel_id = $iecurso->getNivelTipo()->getId();
            $nombre_nivel = $iecurso->getNivelTipo()->getNivel();
            $grado_id = $iecurso->getGradoTipo()->getId();
            $nombre_grado = $iecurso->getGradoTipo()->getGrado(); */
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

    public function rptSupletorioAction(Request $request) {
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
}