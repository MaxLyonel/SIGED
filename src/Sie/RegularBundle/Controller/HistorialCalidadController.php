<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteInscripcionExtranjero;

/**
 * EstudianteInscripcion controller.
 *
 */
class HistorialCalidadController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request) {
        try {
            $form = $request->get('form');
            $id = $form['idDetalle'];
            $llave = $form['llave'];
            $gestion = $form['gestion'];

            $em = $this->getDoctrine()->getManager();

            $validacionProceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($id);

            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$llave));

            if ($validacionProceso->getEsActivo() == true) {
                // VERIFICAMOS SI SE REALIZO LA CORRECCION Y MOSTRAMOS EL MENSAJE
                return $this->render('SieRegularBundle:HistorialCalidad:index.html.twig', array(
                    'solucionado'=>true,
                    'estudiante'=>$estudiante
                ));
            }


            $historial = $em->createQueryBuilder()
                            ->select('ei.id, ie.id as sie, ie.institucioneducativa, gt.gestion, nt.nivel, grt.grado, pt.paralelo, tt.turno, emt.estadomatricula, emit.id as estadomatriculaInicioId, emit.estadomatricula as estadomatriculaInicio')
                            ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                            ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
                            ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                            ->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
                            ->innerJoin('SieAppWebBundle:GradoTipo','grt','with','iec.gradoTipo = grt.id')
                            ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','with','iec.paraleloTipo = pt.id')
                            ->innerJoin('SieAppWebBundle:TurnoTipo','tt','with','iec.turnoTipo = tt.id')
                            ->innerJoin('SieAppWebBundle:EstadomatriculaTipo','emt','with','ei.estadomatriculaTipo = emt.id')
                            ->innerJoin('SieAppWebBundle:EstadomatriculaTipo','emit','with','ei.estadomatriculaInicioTipo = emit.id')
                            ->where('e.codigoRude = :codigoRude')
                            ->setParameter('codigoRude', $llave)
                            ->orderBy('ei.id','desc')
                            ->getQuery()
                            ->getResult();

            $paises = $em->createQueryBuilder()
                        ->select('pt')
                        ->from('SieAppWebBundle:PaisTipo','pt')
                        ->where('pt.id != 0')
                        ->orderBy('pt.id','asc')
                        ->getQuery()
                        ->getResult();

            return $this->render('SieRegularBundle:HistorialCalidad:index.html.twig', array(
                'solucionado'=>false,
                'estudiante'=>$estudiante,
                'idDetalle'=>$id,
                'historial'=>$historial,
                'gestion'=>$gestion,
                'paises'=>$paises
            ));

        } catch (Exception $e) {
            
        }
    }

    public function saveAction(Request $request){
        try {

            $em = $this->getDoctrine()->getManager();
            $idDetalle = $request->get('idDetalle');
            $idInscripcion = $request->get('idInscripcion');
            $unidadProcedencia = $request->get('unidadProcedencia');
            $grado = $request->get('grado');
            $pais = $request->get('pais');
            $informe = $request->get('informe');

            $validacionProceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($idDetalle);
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$validacionProceso->getLlave()));

            if ($validacionProceso->getEsActivo() == false) {

                $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

                if(isset($_FILES['informe'])){
                    $file = $_FILES['informe'];

                    $type = $file['type'];
                    $size = $file['size'];
                    $tmp_name = $file['tmp_name'];
                    $name = $file['name'];
                    $extension = explode('.', $name);
                    $extension = $extension[count($extension)-1];
                    $new_name = $idInscripcion.'_'.$gestion.'.'.$extension;

                    // GUARDAMOS EL ARCHIVO
                    $directorio = $this->get('kernel')->getRootDir() . '/../web/empfiles/insExtranjeros/';
                    if (!file_exists($directorio)) {
                        mkdir($directorio, 0777, true);
                    }

                    $archivador = $directorio.'/'.$new_name;
                    //unlink($archivador);
                    if(!move_uploaded_file($tmp_name, $archivador)){
                        $response->setStatusCode(500);
                        return $response;
                    }

                    $ruta = $archivador;
                }else{
                    $ruta = null;
                }

                // REGISTRAMOS LOS DATOS DEL ESTUDIANTE EXTRANJERO
                $estudianteExtranjero = new EstudianteInscripcionExtranjero();
                $estudianteExtranjero->setInstitucioneducativaOrigen($unidadProcedencia);
                $estudianteExtranjero->setCursoVencido($grado);
                $estudianteExtranjero->setRutaImagen($ruta);
                $estudianteExtranjero->setEstudianteInscripcion($inscripcion);
                $estudianteExtranjero->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($pais));
                $em->persist($estudianteExtranjero);
                $em->flush();

                // ACTUALIZAMOS EL ESTADO DE MATRICULA DE INICIO COMO EXTRANJERO
                $inscripcion->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(19));
                $em->flush();

                // ACTUALIZAMOS LA TABLA VALIDACION PROCESO CON TODAS LAS OBSERVACIONES
                // DE TIPO 16 SIN HISTORIAL DEL ESTUDIANTE PARA CORREGIR TODOS LAS
                // INCONSISTENCIAS
                
                $codigoRude = $validacionProceso->getLlave();
                $gestion = $validacionProceso->getGestionTipo()->getId();

                $similares = $em->createQueryBuilder()
                                ->select('vp')
                                ->from('SieAppWebBundle:ValidacionProceso','vp')
                                ->where('vp.llave = :rude')
                                ->andWhere('vp.validacionReglaTipo = 16')
                                ->andWhere('vp.gestionTipo = :gestion')
                                ->setParameter('rude', $codigoRude)
                                ->setParameter('gestion', $gestion)
                                ->getQuery()
                                ->getResult();

                foreach ($similares as $s) {
                    $s->setEsActivo(true);
                    $em->flush();
                }
            }

            return $this->render('SieRegularBundle:HistorialCalidad:index.html.twig', array(
                'solucionado'=>true,
                'estudiante'=>$estudiante
            ));

        } catch (Exception $e) {
            
        }
    }
}
