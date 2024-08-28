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
                    // $einscripcion_result = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $estudiante_result, 'estadomatriculaTipo' => 4), array('id' => 'DESC'));
                    $einscripcion_result = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->createQueryBuilder('eins')
                        ->select('eins')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'with', 'eins.institucioneducativaCurso = iec.id')
                        ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'with', 'iec.institucioneducativa = ie.id')
                        ->where('eins.estudiante='.$estudiante_result->getId())
                        ->andWhere('eins.estadomatriculaTipo in (4,5)')
                        ->andWhere('ie.institucioneducativaTipo=1')
                        ->orderBy("eins.id", "DESC")
                        ->getQuery()
                        ->getResult();
                    if (count($einscripcion_result)>0) {
                        $estudianteinscripcion_id = $einscripcion_result[0]->getId();
                        $institucioneducativa = $einscripcion_result[0]->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa();
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

                        $codigo_sie = $einscripcion_result[0]->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
                        $nivel_id = $einscripcion_result[0]->getInstitucioneducativaCurso()->getNivelTipo()->getId();
                        $grado_id = $einscripcion_result[0]->getInstitucioneducativaCurso()->getGradoTipo()->getId();
                        $paralelo_id = $einscripcion_result[0]->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
                        $turno_id = $einscripcion_result[0]->getInstitucioneducativaCurso()->getTurnoTipo()->getId();
                        $inscripcion = array(
                            'codigo_sie'=>$codigo_sie, 'nombre_sie'=>$einscripcion_result[0]->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa(),
                            'nivel_id'=>$nivel_id, 'nivel'=>$einscripcion_result[0]->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(),
                            'grado_id'=>$grado_id, 'grado'=>$einscripcion_result[0]->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(),
                            'paralelo_id'=>$paralelo_id, 'paralelo'=>$einscripcion_result[0]->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(),
                            'turno_id'=>$turno_id, 'turno'=>$einscripcion_result[0]->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno()
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
            //ajuste para q recupere ultimo registro en caso de varios tramites de talento
            $estudiante_talento = $em->getRepository('SieAppWebBundle:EstudianteTalento')->findOneBy(['estudiante' => $estudiante_result], ['id' => 'DESC']);
            if(empty($estudiante_talento)){
                return $response->setData(array('msg' => 'notalento'));
            }
            // $einscripcion_result = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $estudiante_result, 'estadomatriculaTipo' => 4), array('id' => 'DESC'));//Evaluar 'estadomatriculaTipo' => 4
            $einscripcion_result = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->createQueryBuilder('eins')
                ->select('eins')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'with', 'eins.institucioneducativaCurso = iec.id')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'with', 'iec.institucioneducativa = ie.id')
                ->where('eins.estudiante='.$estudiante_result->getId())
                ->andWhere('eins.estadomatriculaTipo in (4,5)')
                ->andWhere('ie.institucioneducativaTipo=1')
                ->orderBy("eins.id", "DESC")
                ->getQuery()
                ->getResult();
            if (count($einscripcion_result)>0) {
                // Verifica si la Unidad Educativa es regular
                if ($einscripcion_result[0]->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativaTipo()->getId() != 1) {
                    return $response->setData(array('msg' => 'noregular'));
                }
                // Verifica si el Estudiante está inscrito en su Unidad Educativa
                if ($einscripcion_result[0]->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId() != $request->getSession()->get('ie_id')) {
                    return $response->setData(array('msg' => 'noue'));
                }
                $estudianteinscripcion_id = $einscripcion_result[0]->getId();
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

                $codigo_sie = $einscripcion_result[0]->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
                $nivel_id = $einscripcion_result[0]->getInstitucioneducativaCurso()->getNivelTipo()->getId();
                $grado_id = $einscripcion_result[0]->getInstitucioneducativaCurso()->getGradoTipo()->getId();
                $paralelo_id = $einscripcion_result[0]->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
                $turno_id = $einscripcion_result[0]->getInstitucioneducativaCurso()->getTurnoTipo()->getId();
                $inscripcion = array(
                    'codigo_sie'=>$codigo_sie, 'nombre_sie'=>$einscripcion_result[0]->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa(),
                    'nivel_id'=>$nivel_id, 'nivel'=>$einscripcion_result[0]->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(),
                    'grado_id'=>$grado_id, 'grado'=>$einscripcion_result[0]->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(),
                    'paralelo_id'=>$paralelo_id, 'paralelo'=>$einscripcion_result[0]->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(),
                    'turno_id'=>$turno_id, 'turno'=>$einscripcion_result[0]->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno()
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
        $gestion = $request->getSession()->get('currentyear');
        $operativo = $this->get('funciones')->obtenerOperativo($institucioneducativa_id, $gestion);
        if ($operativo == 4) {
            $estado = 500;
            return $response->setData(array('estado' => $estado, 'msg' => 'La Unidad Educativa no puede iniciar el trámite debido a que ya cuenta con información consolidada.'));
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

        // $restudianteinst = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante'=>$restudiante), array('id'=>'DESC'));
        $restudianteinst = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->createQueryBuilder('eins')
            ->select('eins')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'with', 'eins.institucioneducativaCurso = iec.id')
            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'with', 'iec.institucioneducativa = ie.id')
            ->where('eins.estudiante='.$restudiante->getId())
            ->andWhere('eins.estadomatriculaTipo in (4,5)')
            ->andWhere('ie.institucioneducativaTipo=1')
            ->orderBy("eins.id", "DESC")
            ->getQuery()
            ->getResult();

        $codigo_sie = $restudianteinst[0]->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $nivel_id = $restudianteinst[0]->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $grado_id = $restudianteinst[0]->getInstitucioneducativaCurso()->getGradoTipo()->getId();
        $paralelo_id = $restudianteinst[0]->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
        $turno_id = $restudianteinst[0]->getInstitucioneducativaCurso()->getTurnoTipo()->getId();

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
                    'codigo_sie'=>$codigo_sie, 'nombre_sie'=>$restudianteinst[0]->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa(),
                    'nivel_id'=>$nivel_id, 'nivel'=>($nivel_tipo)?$nivel_tipo->getNivel():'',//$restudianteinst[0]->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(),
                    'grado_id'=>$grado_id, 'grado'=>($grado_tipo)?$grado_tipo->getGrado():'',//$restudianteinst[0]->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(),
                    'paralelo_id'=>$paralelo_id, 'paralelo'=>$restudianteinst[0]->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(),
                    'paralelos'=>$queryParalelo->getResult(),
                    'turno_id'=>$turno_id, 'turno'=>$restudianteinst[0]->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno(),
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
        $observaciones = 'Llenado del Acta Supletorio';
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
        $datos2 = json_decode($resultDatos[1]->getdatos());
        $restudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($datos1->estudiante_id);
        $estudiante = $restudiante->getNombre().' '.$restudiante->getPaterno().' '.$restudiante->getMaterno();
        $rude = $restudiante->getCodigoRude();
        //added
        $arrFechasolicitud = explode("/", $datos1->fecha_solicitud);

        // $restudianteinst = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante'=>$restudiante), array('id'=>'DESC'));
        $restudianteinst = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->createQueryBuilder('eins')
            ->select('eins')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'with', 'eins.institucioneducativaCurso = iec.id')
            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'with', 'iec.institucioneducativa = ie.id')
            ->where('eins.estudiante='.$restudiante->getId())
            ->andWhere('eins.estadomatriculaTipo in (4,5)')
            ->andWhere('ie.institucioneducativaTipo=1')
            ->orderBy("eins.id", "DESC")
            ->getQuery()
            ->getResult();
        
        // Obtiene el ultimo código SIE
        $curso_asignatura = json_decode($datos2->curso_asignatura_notas);
        $posicion_sie = count($curso_asignatura);
        $codigo_sie = $curso_asignatura[$posicion_sie-1]->curso->sie;
        $institucion_e = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($codigo_sie);
        $nombre_ie = $institucion_e->getInstitucioneducativa();
        // $codigo_sie = $restudianteinst[0]->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $nivel_id = $restudianteinst[0]->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $grado_id = $restudianteinst[0]->getInstitucioneducativaCurso()->getGradoTipo()->getId();
        $paralelo_id = $restudianteinst[0]->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
        $turno_id = $restudianteinst[0]->getInstitucioneducativaCurso()->getTurnoTipo()->getId();

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
            ->setParameter('gestion', $arrFechasolicitud[2])
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
            ->setParameter('gestion', $arrFechasolicitud[2])
            ->distinct()
            ->orderBy('iec.turnoTipo', 'ASC')
            ->getQuery();
        $nivel_tipo = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel_id);
        $grado_tipo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado_id);
        $iecurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('institucioneducativa' => $codigo_sie, 'nivelTipo' => $nivel_id, 'gradoTipo' => $grado_id, 'gestionTipo' => $arrFechasolicitud[2]));
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
            'nombre_sie' => $nombre_ie,//$restudianteinst[0]->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa(),
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
            // 'codigo_sie'=>$restudianteinst[0]->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId(),
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
        $gestion = $request->getSession()->get('currentyear');

        $datos = array();
        $datos['tramite_id'] = $request->get('tramite_id');
        $datos['institucioneducativa_id'] = $request->get('institucioneducativa_id');
        $datos['tiene_obs'] = $request->get('tiene_obs');
        $datos['observacion'] = $request->get('observacion');
        if ($datos['tiene_obs'] == "NO") {
            $datos['cursoactual'] = $request->get('cursoactual');
            $curso_inscripcion = json_decode($request->get('cursoactual'));
            $iec = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneById($curso_inscripcion->iec_id);
            $gestion = $iec->getGestionTipo()->getId();
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
        $operativo = $this->get('funciones')->obtenerOperativo($institucioneducativa_id, $gestion);
        if ($operativo == 4) {
            $estado = 500;
            return $response->setData(array('estado' => $estado, 'msg' => 'El trámite no podrá finalizar, debido a que la Unidad Educativa ya cuenta con la información consolidada.'));
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
                        //$nota_tipo = 5; BIMESTRAL
                        $nota_tipo = 9; //TRIMESTRAL (PROMEDIO ANUAL)
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
        $pdf->SetTitle('Acta Supletorio');
        $pdf->SetSubject('Report PDF');
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(true, -10);
        // $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 058', PDF_HEADER_STRING, array(10,10,0), array(255,255,255));
        $pdf->SetKeywords('TCPDF, PDF, ACTA SUPLETORIO');
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
        $image_path = base64_decode('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');
        $pdf->Image('@'.$image_path, 9, 9, 30, 24, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
        /* $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $cabecera, $border = 0, $ln = 0, $fill = 0, $reseth = true,
            $align = '', $autopadding = true
        ); */
        //https://www.pngfind.com/pngs/m/387-3875743_formato-png-a-jpg-logotipos-png-transparent-png.png
        //{{absolute_url(asset(\'webEspecial/img/logo/html/logo-white.png\'))}}
        //<span style="font-size: 8px">(Aceleración Educativa)</span>
        //
        $image_path = $this->getRequest()->getUriForPath('/images/escudo.jpg');
        $image_path = str_replace("/app_dev.php", "", $image_path);
        $cabecera = '<table border="0">';
        $cabecera .='<tr>';
            $cabecera .='<td width="15%" align="center" style="font-size: 6px"><img src="'.$image_path.'" width="64" height="47"><br><span>Estado Plurinacional de Bolivia</span><br><span>Ministerio de Educación</span></td>';
            $cabecera .='<td width="70%" align="center"><h2>ACTA SUPLETORIA DE PROMOCIÓN PARA<br>TALENTO EXTRAORDINARIO</h2></td>';
            $cabecera .='<td width="15%" align="right"><img src="http://172.20.0.114/index.php?data='.$tramite_id.'" width="66" height="66"></td>';
        $cabecera .='</tr>';
        $cabecera .='<tr>';
            $cabecera .='<td width="50%"><b>Fecha de Trámite: </b>'.$resultDatos[1]->getFechaRegistro()->format('d/m/Y').'</td>';
            $cabecera .='<td width="50%" align="right"><b>Nro. Trámite: </b>'.$tramite_id.'&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $cabecera .='</tr>';
        $cabecera .='</table>';
        $pdf->writeHTML($cabecera, true, false, true, false, '');

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
            ->andWhere('mi.cargoTipo in (:cargos)')
            ->andWhere('mi.esVigenteAdministrativo = :estado')
            ->setParameter('idInstitucion', $institucioneducativa_id)
            ->setParameter('gestion', $gestion_id)
            ->setParameter('cargos', array(1,12))
            ->setParameter('estado', 't')
            ->orderBy("mi.cargoTipo")
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
        
        // $image_path = $this->getRequest()->getUriForPath('/images/escudo.jpg');
        // $image_path = str_replace("/app_dev.php", "", $image_path);
        // <img src="'.$image_path.'" width="60" height="47"><br><span>Estado Plurinacional de Bolivia</span><br><span>Ministerio de Educación</span>
        $cabecera = '<table border="0">';
        $cabecera .='<tr>';
            $cabecera .='<td width="15%" align="center" style="font-size: 6px"></td>';
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
        $cursoAasignaturaNotas = json_decode($datos2->curso_asignatura_notas);
        $cantidadCursos = count($cursoAasignaturaNotas);
        $secundaria1 = $secundaria2 = $secundaria3 = $secundaria4 = $secundaria5 = 0;
        foreach ($cursoAasignaturaNotas as $indice => $item_nota) {//5 cursos
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
                if($cantidadCursos == 1) {
                    foreach ($item_nota->asignatura_notas as $key => $iteman) {
                        $secundaria['asignatura'][$key] = $iteman->asignatura;
                        switch ($item_nota->curso->grado_id) {
                            case '1':
                                $secundaria['nota1'][$key] = ($iteman->nota == "" ? '-' : $iteman->nota);
                                break;
                            case '2':
                                $secundaria['nota2'][$key] = ($iteman->nota == "" ? '-' : $iteman->nota);
                                break;
                            case '3':
                                $secundaria['nota3'][$key] = ($iteman->nota == "" ? '-' : $iteman->nota);
                                break;
                            case '4':
                                $secundaria['nota4'][$key] = ($iteman->nota == "" ? '-' : $iteman->nota);
                                break;
                            case '5':
                                $secundaria['nota5'][$key] = ($iteman->nota == "" ? '-' : $iteman->nota);
                                break;
                            case '6':
                                $secundaria['nota6'][$key] = ($iteman->nota == "" ? '-' : $iteman->nota);
                                break;
                            default:
                                # code...
                                break;
                        }
                    }
                } else {
                    foreach ($item_nota->asignatura_notas as $key => $iteman) {//10 11 12 asignaturas
                        if (in_array($iteman->asignatura, $secundaria['asignatura'])) {
                        } else {
                            $secundaria['asignatura_id'][$posicion_asig] = $iteman->asignatura_id;
                            $secundaria['asignatura'][$posicion_asig] = $iteman->asignatura;
                            $posicion_asig++;
                        }
                    }
                    // llenado de notas
                    
                    foreach ($secundaria['asignatura_id'] as $pos => $itemid) {
                        foreach ($item_nota->asignatura_notas as $key => $iteman) {
                            if ($itemid == $iteman->asignatura_id) {
                                switch ($item_nota->curso->grado_id) {
                                    case '1':
                                        $secundaria['nota1'][$pos] = ($iteman->nota == "" ? '-' : $iteman->nota);
                                        $secundaria1 = 1;
                                        break;
                                    case '2':
                                        $secundaria['nota2'][$pos] = ($iteman->nota == "" ? '-' : $iteman->nota);
                                        $secundaria2 = 1;
                                        break;
                                    case '3':
                                        $secundaria['nota3'][$pos] = ($iteman->nota == "" ? '-' : $iteman->nota);
                                        $secundaria3 = 1;
                                        break;
                                    case '4':
                                        $secundaria['nota4'][$pos] = ($iteman->nota == "" ? '-' : $iteman->nota);
                                        $secundaria4 = 1;
                                        break;
                                    case '5':
                                        $secundaria['nota5'][$pos] = ($iteman->nota == "" ? '-' : $iteman->nota);
                                        $secundaria5 = 1;
                                        break;
                                    case '6':
                                        $secundaria['nota6'][$pos] = ($iteman->nota == "" ? '-' : $iteman->nota);
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
        }
        
        $custom_fs = '8.5px';
        if ($exist_secundaria) {
            $custom_fs = '8px';
        }
        
        $datosTramite='<table border="0" cellpadding="1.5" style="font-size: '.$custom_fs.'">';
        // Datos del estudiante
        $datosTramite.='<tr style="background-color:#ddd;"><td colspan="4" height="14" style="line-height: 14px;"><b>1. Datos del Estudiante</b></td></tr>';
        $datosTramite.='<tr><td><b>Código RUDE:</b></td><td colspan="3">'.$queryEstudiante['codigoRude'].'</td></tr>';
        $datosTramite.='<tr><td><b>Nombre:</b></td><td colspan="3">'.$queryEstudiante['estudiante'].'</td></tr>';
        $datosTramite.='<tr><td><b>Cédula de Identidad:</b></td><td>'.$queryEstudiante['carnetIdentidad'].' '.$queryEstudiante['expedido'].'</td><td><b>Complemento:</b></td><td>'.$queryEstudiante['complemento'].'</td></tr>';
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
        $datosTramite.='<tr><td><b>Cédula de Identidad:</b></td><td>'.$queryMaestroUE['carnet'].' '.$queryMaestroUE['expedido'].'</td><td><b>Complemento:</b></td><td>'.$queryMaestroUE['complemento'].'</td></tr>';
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

                if(($cantidadCursos == 1) or ($cantidadCursos == 3 and $secundaria3==1 and $secundaria4==1 and $secundaria5==1) or ($cantidadCursos == 2 and $secundaria3==1 and $secundaria4==1) or ($cantidadCursos == 2 and $secundaria4==1 and $secundaria5==1)) {
                    if($secundaria['nota1'][$key]!='') {$actaSupletorio.='<td align="center">'.$secundaria['nota1'][$key].'</td>';}
                    if($secundaria['nota2'][$key]!='') {$actaSupletorio.='<td align="center">'.$secundaria['nota2'][$key].'</td>';}
                    if($secundaria['nota3'][$key]!='') {$actaSupletorio.='<td align="center">'.$secundaria['nota3'][$key].'</td>';}
                    if($secundaria['nota4'][$key]!='') {$actaSupletorio.='<td align="center">'.$secundaria['nota4'][$key].'</td>';}
                    if($secundaria['nota5'][$key]!='') {$actaSupletorio.='<td align="center">'.$secundaria['nota5'][$key].'</td>';}
                    if($secundaria['nota6'][$key]!='') {$actaSupletorio.='<td align="center">'.$secundaria['nota6'][$key].'</td>';}
                } else if($secundaria1==0 and $secundaria2==1 and ($secundaria3==1 or $secundaria4==1 or $secundaria5==1)) {
                    if($secundaria['nota1'][$key]!='') {
                        $actaSupletorio.='<td align="center">'.$secundaria['nota1'][$key].'</td>';
                    }
                    $actaSupletorio.='<td align="center">'.$secundaria['nota2'][$key].'</td>';
                    $actaSupletorio.='<td align="center">'.$secundaria['nota3'][$key].'</td>';
                    $actaSupletorio.='<td align="center">'.$secundaria['nota4'][$key].'</td>';
                    $actaSupletorio.='<td align="center">'.$secundaria['nota5'][$key].'</td>';
                    $actaSupletorio.='<td align="center">'.$secundaria['nota6'][$key].'</td>';
                } else {
                    $actaSupletorio.='<td align="center">'.$secundaria['nota1'][$key].'</td>';
                    $actaSupletorio.='<td align="center">'.$secundaria['nota2'][$key].'</td>';
                    $actaSupletorio.='<td align="center">'.$secundaria['nota3'][$key].'</td>';
                    $actaSupletorio.='<td align="center">'.$secundaria['nota4'][$key].'</td>';
                    $actaSupletorio.='<td align="center">'.$secundaria['nota5'][$key].'</td>';
                    $actaSupletorio.='<td align="center">'.$secundaria['nota6'][$key].'</td>';
                }
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
        // $firmas.='<tr><td align="right" colspan="3"><br/><span style="font-size: 6px;"><br/>Fecha de Impresión: '.date('d/m/Y H:i:s').'</span></td></tr>';
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