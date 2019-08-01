<?php

namespace Sie\HerramientaBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use Sie\ProcesosBundle\Controller\TramiteRueController;
use Sie\AppWebBundle\Controller\WfTramiteController;
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnicoTipo;
use Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo;
use Sie\AppWebBundle\Entity\GestionTipo;
use Sie\AppWebBundle\Entity\TramiteDetalle;
use Sie\AppWebBundle\Entity\Tramite;
use Sie\AppWebBundle\Entity\TramiteTipo;


class NewRequestBthController extends Controller{
	
    protected $session;

	public function __construct() {		
        $this->session = new Session();
	}

    public function indexAction(Request $request){
        // get the send data
    	$id_Institucion =  $request->getSession()->get('ie_id');
        $gestion =  $request->getSession()->get('currentyear');
        $flujotipo = 6;//$request->get('id');
        // validate if the ue has tramite
        $verificarinicioTramite = $this->verificatramite($id_Institucion,$gestion,$flujotipo);
        

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select td.*
                                            from tramite t
                                            join tramite_detalle td on cast(t.tramite as int)=td.id where t.id=".$flujotipo);
        $query->execute();
        $td = $query->fetchAll();

            if (isset($td[0]['tramite_detalle_id'])){
                $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($td[0]['tramite_detalle_id']);
                if($tramiteDetalle->getTramiteEstado()->getId()== 4){
                    $iUE = $tramiteDetalle->getTramite()->getInstitucioneducativa()->getId();
                    return $this->redirectToRoute('solicitud_bth_formulario',array('iUE'=>$iUE,'id_tramite'=>$flujotipo));
                }
            }else{

               

                //Informacion de la U.E. y del director
                $institucion = $request->getSession()->get('ie_id');
                $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
                $query = $repository->createQueryBuilder('inss')
                    ->select('max(inss.gestionTipo)')
                    ->where('inss.institucioneducativa = :idInstitucion')
                    ->setParameter('idInstitucion', $institucion)
                    ->getQuery();
                $inss = $query->getResult();
                $gestion = $inss[0][1];
                //$gestion = 2018;
                $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
                $query = $repository->createQueryBuilder('ie')
                    ->select('ie, ies')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
                    ->where('ie.id = :idInstitucion')
                    ->andWhere('ies.gestionTipo in (:gestion)')
                    ->setParameter('idInstitucion', $institucion)
                    ->setParameter('gestion', $inss)
                    ->getQuery();
                $infoUe = $query->getResult();
               
                $ubicacionUe = $this->getJurisdiccionGeograficaUE($inss,$institucion);
                /*
                 * obtenemos datos de la unidad educativa
                 */
                $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
                $institucionid = $request->getSession()->get('ie_id');
                // Lista de cursos institucioneducativa
                $query = $em->getConnection()->prepare("SELECT  iue.grado_tipo_id,gt.grado  
                                                FROM institucioneducativa_curso iue 
		                                        INNER JOIN grado_tipo gt ON iue.grado_tipo_id=gt.id  
                                                WHERE iue.institucioneducativa_id=$institucionid
                                                AND iue.gestion_tipo_id=$gestion
                                                AND iue.nivel_tipo_id = 3");
                $query->execute();
                $cursos = $query->fetchAll();
                /*
                 * Listamos los maestros inscritos en la unidad educativa
                 */
                $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));
                $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
                $query = $repository->createQueryBuilder('mins')
                    ->select('per.carnet, per.paterno, per.materno, per.nombre')
                    ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
                    ->where('mins.institucioneducativa = :idInstitucion')
                    ->andWhere('mins.gestionTipo = :gestion')
                    ->andWhere('mins.cargoTipo IN (:cargo)')
                    ->andWhere('mins.esVigenteAdministrativo = :esvigente')
                    ->setParameter('idInstitucion', $institucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('cargo', array(1,12))
                    ->setParameter('esvigente', true)
                    ->setMaxResults(1)
                    ->getQuery();
                $director = $query->getOneOrNullResult();
                /*Grado para el cual se aplica la ratificacion BTH*/
                /**
                 * Se aplica a las gestion  2018 mientras pase las inscripciones $gestion = 2018
                 */
                //$gestion = 2018;
                /*$query = $em->getConnection()->prepare("SELECT ieht.institucioneducativa_id,ieht.grado_tipo_id FROM institucioneducativa_humanistico_tecnico ieht
                                                    WHERE ieht.institucioneducativa_id = $institucionid AND ieht.gestion_tipo_id = $gestion");
                $query->execute();*/
                // $query = $em->getConnection()->prepare("SELECT ieht.institucioneducativa_id,ieht.grado_tipo_id FROM institucioneducativa_humanistico_tecnico ieht 
                // WHERE ieht.institucioneducativa_id = $institucionid and gestion_tipo_id = 2018 and institucioneducativa_humanistico_tecnico_tipo_id in(7,1)
                // ORDER BY gestion_tipo_id DESC limit 1");
                // $query->execute();
                // $grado = $query->fetch();

                // if($grado){
                //      if ((int)$grado['grado_tipo_id'] < 6){                  
                //     $grado = (int)$grado['grado_tipo_id']+1;    
                //     }else{
                //     $grado = (int)$grado['grado_tipo_id'];
                //     }
                // } else{
                //     $grado = 3;
                // } 

               
                /** Adecuacion a las equivalencias de especialidades */
                $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad
                                                FROM especialidad_tecnico_humanistico_tipo eth
                                                WHERE eth.es_vigente is TRUE
                                                ORDER BY 2");
                $query->execute();
                $especialidad = $query->fetchAll();
                
                //get all course of the UE 
                $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
                $query = $entity->createQueryBuilder('iec')
                        ->select('(iec.gradoTipo)')
                        ->where('iec.institucioneducativa = :sie')
                        ->andWhere('iec.nivelTipo = :idnivel')
                        ->andwhere('iec.gestionTipo = :gestion')
                        ->andWhere('iec.gradoTipo in (:arrgrados)')
                        ->setParameter('sie', $institucion->getId())
                        ->setParameter('idnivel', 13)
                        ->setParameter('gestion', $gestion)
                        ->setParameter('arrgrados', array(3,4,5,6))
                        ->distinct()
                        ->orderBy('iec.gradoTipo', 'ASC')
                        ->getQuery();
                        // dump($query->getSQL());die;
                $objGrados = $query->getResult();
                $arrGrados = array();
                foreach ($objGrados as $data) {
                    $arrGrados[$data[1]] = $em->getRepository('SieAppWebBundle:GradoTipo')->find($data[1])->getGrado();
                }
                
                 return $this->render('SieHerramientaBundle:NewRequestBth:index.html.twig', array(
                 							'ieducativa'	=> $infoUe,
							                'institucion'   => $institucion,
							                'gestion'       => $gestion,
							                'ubicacion'     => $ubicacionUe,
							                'director'      => $director,
							                'cursos'        => $cursos,
							                'maestros'      => $maestros,
							                'especialidad'  => $especialidad,
							                'grados'        => $arrGrados,
							                // 'grado'         => $grado,
							                'idflujo'=>$request->get('id'),
							                'estado'=>$verificarinicioTramite
							         ));   
            }


    }


    public function verificatramite($id_Institucion,$gestion,$flujotipo){
        /**
         * Verificicacion de que la UE inicio un tramite
         */
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT COUNT(tr.id)AS  cantidad_tramite_bth FROM tramite tr  
                                                    WHERE tr.flujo_tipo_id = $flujotipo AND tr.institucioneducativa_id = $id_Institucion
                                                    AND tr.tramite_tipo <> 31
                                                    AND tr.gestion_id = $gestion");
        $query->execute();
        $tramite_ue = $query->fetchAll(); //dump($tramite_ue);die;
        $tramite_iniciado=$tramite_ue[0]['cantidad_tramite_bth'];
        if($tramite_iniciado==0){
            /**
             * Verifica si la unidad educativa que inicio el tramite tenga estudiantes para el nivel SECUNDARIA
             */
            $query = $em->getConnection()->prepare("SELECT count(ei.id) AS est
                                                FROM estudiante_inscripcion ei
                                                INNER JOIN institucioneducativa_curso iec ON ei.institucioneducativa_curso_id = iec.id
                                                WHERE
                                                iec.nivel_tipo_id = 13 AND
                                                ei.estadomatricula_tipo_id = 4 AND
                                                iec.gestion_tipo_id = $gestion AND
                                                iec.institucioneducativa_id = $id_Institucion
                                                group by
                                                iec.institucioneducativa_id");
            $query->execute();
            $cant_alumnos = $query->fetchAll(); //dump($cant_alumnos);die;
            $cantidad_est=count($cant_alumnos)>0? $cant_alumnos[0]['est']:0;
            $verificarue= $this->validaUE($id_Institucion,$gestion); //dump($verificarue);die;
            if ($cantidad_est==0 or $verificarue == 0){
                $tramite_iniciado=1;
            }else{
                $tramite_iniciado=0;
            }
            //dump($tramite_iniciado);die;
            return $tramite_iniciado;
        }else{
            return $tramite_iniciado;
        }
    }

    private function getJurisdiccionGeograficaUE($inss, $institucion){
        $em = $this->getDoctrine()->getManager();
        
        $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');
          $query = $repository->createQueryBuilder('jg')
                    ->select('lt4.codigo AS codigo_departamento,
                        lt4.lugar AS departamento,
                        lt3.codigo AS codigo_provincia,
                        lt3.lugar AS provincia,
                        lt2.codigo AS codigo_seccion,
                        lt2.lugar AS seccion,
                        lt1.codigo AS codigo_canton,
                        lt1.lugar AS canton,
                        lt.codigo AS codigo_localidad,
                        lt.lugar AS localidad,
                        dist.id AS codigo_distrito,
                        dist.distrito,
                        orgt.orgcurricula,
                        dept.dependencia,
                        jg.id AS codigo_le,
                        inst.id,
                        inst.institucioneducativa,
                        lt.area2001,
                        estt.estadoinstitucion,
                        inss.direccion,
                        jg.direccion,
                        jg.zona,
                        jg.lugarTipoIdDistrito')
                    ->join('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.leJuridicciongeografica = jg.id')
                    ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
                    ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
                    ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
                    ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
                    ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'inss', 'WITH', 'inss.institucioneducativa = inst.id')
                    ->innerJoin('SieAppWebBundle:EstadoinstitucionTipo', 'estt', 'WITH', 'inst.estadoinstitucionTipo = estt.id')
                    ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
                    ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
                    ->join('SieAppWebBundle:DependenciaTipo', 'dept', 'WITH', 'inst.dependenciaTipo = dept.id')
                    ->where('inst.id = :idInstitucion')
                    ->andWhere('inss.gestionTipo in (:gestion)')
                    ->setParameter('idInstitucion', $institucion)
                    ->setParameter('gestion', $inss)
                    ->getQuery();
        return $query->getSingleResult();
    }

}
