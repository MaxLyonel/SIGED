<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

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

/**
 * EstudianteInscripcion controller.
 *
 */
class EstudianteNotasController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction(Request $request){
       
        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        //si cerro ooprativo
        // se devuelve

        /*dump($infoUe);
        dump($infoStudent);   
        dump(json_decode($infoStudent));    die; */
                   
        
        $estudianteInscripcionS2aux = json_decode($infoStudent) ;
        $estudianteInscripcionS2 = $estudianteInscripcionS2aux->eInsId;
        //dump($estudianteInscripcionS2);
        //496266104

        $guardanotas = true;
        
        //si es especializados preguntamos
        if(($estudianteInscripcionS2aux->nivelId == 15 and $estudianteInscripcionS2aux->gradoId == 3))
        {
            /*$especializadoscierre = $this->get('funciones')->verificarApEspecializadosCerrado($this->session->get('ie_id'),$this->session->get('ie_gestion'),$this->session->get('ie_per_cod'));
            if($especializadoscierre == true){
                $guardanotas = false;
            }*/
            
            //por cierre de todo
            $guardanotas = false;

        }else{

            // solo tecnico medio
            if( ($estudianteInscripcionS2aux->nivelId == 22 or $estudianteInscripcionS2aux->nivelId == 19 or $estudianteInscripcionS2aux->nivelId == 18 ) and $estudianteInscripcionS2aux->gradoId == 3){
                $guardanotas = true;
            }else{
                
                //todos los demas no hbailitados
                $guardanotas = false;
            }
            

        }
        
        //ultimos casos alternativa
        //if ($this->session->get('ie_id') == 50950042  or $this->session->get('ie_id') == 61470045 or $this->session->get('ie_id') == 80630044 or $this->session->get('ie_id') == 81230269 ){
        if ($this->session->get('ie_id') == 50950042  or $this->session->get('ie_id') == 61470045 or $this->session->get('ie_id') == 80630044 or $this->session->get('ie_id') == 81230269 ){
            $guardanotas = true;
            //para todos
        }


        //dump($guardanotas); die; 

        

        $data = $this->getNotas($infoUe, $infoStudent);
        //dump($data); die;
               
        //obtenemos el dato del censo
        /*$query = $em->getConnection()->prepare("
            SELECT
                censo_alternativa_beneficiarios.id, 
                censo_alternativa_beneficiarios.cea, 
                censo_alternativa_beneficiarios.rudeal, 
                censo_alternativa_beneficiarios.total_asignado_2s, 
                censo_alternativa_beneficiarios.estudiante_inscripcion_s2, 
                censo_alternativa_beneficiarios.institucioneducativa_curso_id_s2, 
                censo_alternativa_beneficiarios.estudiante_id, 
                censo_alternativa_beneficiarios_detalle.periodo_id, 
                censo_alternativa_beneficiarios_detalle.beneficio, 
                censo_alternativa_beneficiarios_detalle.modulo_tipo_id
            FROM
                censo_alternativa_beneficiarios
                INNER JOIN
                censo_alternativa_beneficiarios_detalle
                ON 
                    censo_alternativa_beneficiarios.id = censo_alternativa_beneficiarios_detalle.beneficiario_id
                    where 
                    censo_alternativa_beneficiarios.estudiante_inscripcion_s2 = :estudiante_inscripcion
        ");  */              

        $query = $em->getConnection()->prepare("
            SELECT  distinct
                censo_alternativa_beneficiarios.id, 
                censo_alternativa_beneficiarios.cea, 
                censo_alternativa_beneficiarios.rudeal, 
                censo_alternativa_beneficiarios.total_asignado_2s, 
                censo_alternativa_beneficiarios.estudiante_inscripcion_s2, 
                censo_alternativa_beneficiarios.institucioneducativa_curso_id_s2, 
                censo_alternativa_beneficiarios.estudiante_id, 
                censo_alternativa_beneficiarios_detalle.periodo_id, 
                censo_alternativa_beneficiarios_detalle.beneficio, 
                censo_alternativa_beneficiarios_detalle.modulo_tipo_id
            FROM
                censo_alternativa_beneficiarios
                INNER JOIN
                censo_alternativa_beneficiarios_detalle
                ON 
                    censo_alternativa_beneficiarios.id = censo_alternativa_beneficiarios_detalle.beneficiario_id
                    where 
                    censo_alternativa_beneficiarios.estudiante_inscripcion_s2 = :estudiante_inscripcion and periodo_id = 3 
        ");                


        $query->bindValue(':estudiante_inscripcion', $estudianteInscripcionS2);
        $query->execute();
        $moduloscenso = $query->fetchAll(); 
        //dump($moduloscenso);


        // una copia de las areas para poner el beneficio
        //dump($data['areas']);
        $notascenso = $data['areas'];
        $notasfinales = $data['areas'];
        
        //a todos adicionamos los campos en cero
        for ($i=0; $i < count($data['areas']) ; $i++) {                 
            
                $data['areas'][$i]['notas'][0]['notacenso'] = 0;
                $data['areas'][$i]['notas'][0]['notafinal'] = 0;
            
        }          

        //comparamos con el censo
      
        for ($i=0; $i < count($moduloscenso) ; $i++) { 

          

            $modulo_tipo_id = $moduloscenso[$i]['modulo_tipo_id'];
            $beneficiocenso = $moduloscenso[$i]['beneficio'];

            //con esto buscamos en el array notascenso
            
            for ($j=0; $j < count($data['areas']) ; $j++) { 
                
                if( $data['areas'][$j]['idAsignatura'] == $modulo_tipo_id ){

                    if($data['areas'][$j]['notas'][0]['nota'] == 0){
                        $data['areas'][$j]['notas'][0]['notacenso'] = $beneficiocenso;
                        $data['areas'][$j]['notas'][0]['notafinal'] = $beneficiocenso;
                    }else{
                        $data['areas'][$j]['notas'][0]['nota'] = $data['areas'][$j]['notas'][0]['nota'] - $beneficiocenso; //correjido para visualizacion
                        $data['areas'][$j]['notas'][0]['notacenso'] = $beneficiocenso;

                        if(($data['areas'][$j]['notas'][0]['nota'] + $beneficiocenso) <= 100 )
                        {
                            $data['areas'][$j]['notas'][0]['notafinal'] = $data['areas'][$j]['notas'][0]['nota'] + $beneficiocenso;                       
                        }else{
                            $data['areas'][$j]['notas'][0]['notafinal'] = 100;                       
                        }
                        
                    }
                }
            }          
            
        }
      

        if (count($moduloscenso) == 0){
            // no tiene censo
            for ($i=0; $i < count($data['areas']) ; $i++) { 
                $data['areas'][$i]['notas'][0]['notacenso'] = 0;
                $data['areas'][$i]['notas'][0]['notafinal'] = $data['areas'][$i]['notas'][0]['nota'];

            }

        }


        /*for ($i=0; $i < count($moduloscenso) ; $i++) { 

            $modulo_tipo_id = $moduloscenso[$i]['modulo_tipo_id'];
            $beneficiocenso = $moduloscenso[$i]['beneficio'];

            //con esto buscamos en el array notascenso
            
            for ($j=0; $j < count($notascenso) ; $j++) { 
                
                if( $notascenso[$i]['idAsignatura'] == $modulo_tipo_id ){
                    $notascenso[$i]['notas'][0]['nota'] = $beneficiocenso;
                }
            }          
            
        }*/

        //$data['notascenso'] = $notascenso;
        //$data['notasfinales'] = $notasfinales;
        
        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $periodo = $this->session->get('ie_per_cod');
        //dump($data);die;
        $closeopequinto = $this->get('funciones')->verificarApEspecializadosCerrado($institucion,$gestion,$periodo);
        //dump($closeopequinto); die;
        $aInfoUeducativa = unserialize($infoUe);

        /*********CUANDO CIERRA OPERATIVO 5TO AÑO - IBD****/
        // if ($closeopequinto and $aInfoUeducativa['ueducativaInfo']['superiorAcreditacionTipoId'] == 52){
        // if ($aInfoUeducativa['ueducativaInfo']['superiorAcreditacionTipoId'] == 52 && $institucion != 80730796 && $this->session->get('userId')!=94161725){
        /*if ($aInfoUeducativa['ueducativaInfo']['superiorAcreditacionTipoId'] == 52 && $periodo == 3 && $this->session->get('userId')!=94161725){
            return $this->redirect($this->generateUrl('principal_web'));
        }*/

        //no es especializados
       //dump($aInfoUeducativa['ueducativaInfo']['superiorAcreditacionTipoId']); die;
       //52 especializados, 32 tecnico medio

       $habilita = false;
        if ($aInfoUeducativa['ueducativaInfo']['superiorAcreditacionTipoId'] == 52 ){
            $habilita = true;           
        }

        /*if ($aInfoUeducativa['ueducativaInfo']['superiorAcreditacionTipoId'] == 32 ){
            $habilita = true;           
        }*/

        /*if ($aInfoUeducativa['ueducativaInfo']['superiorAcreditacionTipoId'] == 52 ){
            return $this->redirect($this->generateUrl('principal_web'));           
        }*/

        /*if ($habilita == false ){
            return $this->redirect($this->generateUrl('principal_web'));           
        }*/

         /********* temporal notas****/
        // if ($gestion == 2024 && $this->session->get('userId')!=94161725){
        //     return $this->redirect($this->generateUrl('principal_web'));
        //  }
        /**************************************************/

        $data['guardanotas'] = $guardanotas;
        
        /*dump($data);
        die;*/

        
        if($data['gestion'] >= 2016){
           
            return $this->render('SieHerramientaAlternativaBundle:EstudianteNotas:notasSemestreActual.html.twig',$data);
        }else{
           
            return $this->render('SieHerramientaAlternativaBundle:EstudianteNotas:notasSemestre.html.twig',$data);
        }
        
    }
    

    public function newAction(Request $request){

    }

    /**
     * [deleteAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function deleteAction(Request $request){

    }

    /**
     * get Areas Curso
     * @param  Request $request [description]
     * @return View table areas
     */
    public function getNotas($infoUe, $infoStudent){
        /*********TEMPORAL IBD****/
        // $arrayue = array(81981321);
        // $institucion = $this->session->get('ie_id');
        // if (!(in_array($institucion, $arrayue))){
            //return $this->redirect($this->generateUrl('principal_web'));
        // }
        /*********TEMPORAL IBD****/
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        // datos ue
        $aInfoUeducativa = unserialize($infoUe);

        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $periodo = $this->session->get('ie_per_cod');
        // $closeopequinto = $this->get('funciones')->verificarApEspecializadosCerrado($institucion,$gestion,$periodo);

        /*********CUANDO CIERRA OPERATIVO 5TO AÑO - IBD****/
        // if ($closeopequinto and $aInfoUeducativa['ueducativaInfo']['superiorAcreditacionTipoId'] == 52){
        // if ($aInfoUeducativa['ueducativaInfo']['superiorAcreditacionTipoId'] == 52 && $institucion != 80730796 && $this->session->get('userId')!=94161725){
        /*if ($aInfoUeducativa['ueducativaInfo']['superiorAcreditacionTipoId'] == 52 && $periodo == 3 && $this->session->get('userId')!=94161725){
            return $this->redirect($this->generateUrl('principal_web'));
        }*/
        /**************************************************//*********CUANDO CIERRA OPERATIVO 5TO AÑO - IBD****/
        // dump($aInfoUeducativa);die;
        //$sie = $aInfoUeducativa['requestUser']['sie'];
        //$gestion = $aInfoUeducativa['requestUser']['gestion'];
        //$nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        //$grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        //$curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecId);

        $curso = $em->createQueryBuilder()
                    ->select('sfat.codigo as nivel, ie.id as sie, gt.id as gestion')
                    ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
                    ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
                    ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                    ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo','siep','with','iec.superiorInstitucioneducativaPeriodo = siep.id')
                    ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion','siea','with','siep.superiorInstitucioneducativaAcreditacion = siea.id')
                    ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad','sae','with','siea.acreditacionEspecialidad = sae.id')
                    ->innerJoin('SieAppWebBundle:SuperiorEspecialidadTipo','supet','with','sae.superiorEspecialidadTipo = supet')
                    ->innerJoin('SieAppWebBundle:SuperiorFacultadAreaTipo','sfat','with','supet.superiorFacultadAreaTipo = sfat.id')
                    ->where('iec.id = :idCurso')
                    ->setParameter('idCurso',$iecId)
                    ->getQuery()
                    ->getResult();

        $sie = $curso[0]['sie'];
        $gestion = $curso[0]['gestion'];
        $nivel = $curso[0]['nivel'];

        // datos estudiante
        $aInfoStudent = json_decode($infoStudent,true);
        $idInscripcion = $aInfoStudent['eInsId'];//162015409;//143116257;//
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        //dump($idInscripcion);die;
        //dump($this->session->get('personaId'));die;
        $estudianteDatos = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$aInfoStudent['codigoRude']));
        $estudiante = array(
                            'codigoRude'=>$aInfoStudent['codigoRude'],
                            'estudiante'=>$estudianteDatos->getPaterno().' '.$estudianteDatos->getMaterno().' '.$estudianteDatos->getNombre(),
                            'estadoMatricula'=>$inscripcion->getEstadomatriculaTipo()->getEstadomatricula());
        /*$especialidad = $em->createQueryBuilder()
                          ->select('set.especialidad')
                          ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
                          ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo','siep','with','iec.superiorInstitucioneducativaPeriodo = siep.id')
                          ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion','siea','with','siep.superiorInstitucioneducativaAcreditacion = siea.id')
                          ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad','sae','with','siea.acreditacionEspecialidad = sae.id')
                          ->innerJoin('SieAppWebBundle:SuperiorEspecialidadTipo','seti','with','sae.superiorEspecialidadTipo = seti.id')
                          ->where('iec.id = :idCurso')
                          ->setParameter('idCurso',$iecId)
                          ->getQuery()
                          ->getResult();

        dump($especialidad);die;*/

        $operativo = 1;
        // Obtenemos las asignaturas de humanistica (15) tecnica (18 a 25)
        $asignaturas = $em->createQueryBuilder()
                    ->select('smt.id as asignaturaId, smt.modulo as asignatura, ea.id as estAsigId, eae.id as idEstadoAsignatura, ieco.id as idCursoOferta, sast.id as idAreaSaberes, sast.areaSuperior')
                    ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                    ->innerJoin('SieAppWebBundle:EstudianteasignaturaEstado','eae','with','ea.estudianteasignaturaEstado = eae.id')
                    ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ea.estudianteInscripcion = ei.id')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                    ->innerJoin('SieAppWebBundle:SuperiorModuloPeriodo','smp','WITH','ieco.superiorModuloPeriodo = smp.id')
                    ->innerJoin('SieAppWebBundle:SuperiorModuloTipo','smt','WITH','smp.superiorModuloTipo = smt.id')
                    ->innerJoin('SieAppWebBundle:SuperiorAreaSaberesTipo','sast','with','smt.superiorAreaSaberesTipo = sast.id')
                    //->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro','iecom','with','iecom.institucioneducativaCursoOferta = ieco.id')
                    //->innerJoin('SieAppWebBundle:MaestroInscripcion','mi','with','iecom.maestroInscripcion = mi.id')
                    //->innerJoin('SieAppWebBundle:Persona','p','with','mi.persona = p.id')
                   // ->innerJoin('SieAppWebBundle:NotaTipo','nt','with','iecom.notaTipo = nt.id')
                    ->groupBy('smt.id, smt.modulo, ea.id, eae.id, ieco.id, sast.id')
                    ->orderBy('sast.id','ASC')
                    ->addOrderBy('smt.modulo','ASC')
                    ->where('ei.id = :idInscripcion')
                    //->andWhere('nt.id IN (:idsNotas)')
                    ->setParameter('idInscripcion',$idInscripcion)
                    //->setParameter('idsNotas',array(0,1,2,3,4))
                    ->getQuery()
                    ->getResult();

                    //quitar persona maestro para evitar duplicados

        //dump($asignaturas);die;

        // Obtenemos los estados de de las asignaturas
        $estadosAsignatura = $em->createQueryBuilder()
                              ->select('eae')
                              ->from('SieAppWebBundle:EstudianteasignaturaEstado','eae')
                              //->where('eae.id IN (:ids)')
                              //->setParameter('ids',array(1,2,3,4))
                              ->getQuery()
                              ->getResult();
        //dump($estadosAsignatura);die;
        // Nivel tecnico y humanistico
        if($nivel == 15){
            $inicio = 23;
            $fin = 26;
        }else{
            $inicio = 19;
            $fin = 22;
        }

        $notasArray = array();
        $cont = 0;
        foreach ($asignaturas as $a) {
            $notasArray[$cont] = array('area'=>$a['areaSuperior'],'idAsignatura'=>$a['asignaturaId'],'asignatura'=>$a['asignatura'],'idEstadoAsignatura'=>$a['idEstadoAsignatura']);

            $asignaturasNotas = $em->createQueryBuilder()
                                ->select('en.id as idNota, nt.id as idNotaTipo, nt.notaTipo, ea.id as idEstudianteAsignatura, en.notaCuantitativa, en.notaCualitativa')
                                ->from('SieAppWebBundle:EstudianteNota','en')
                                ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','en.estudianteAsignatura = ea.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                                ->innerJoin('SieAppWebBundle:NotaTipo','nt','with','en.notaTipo = nt.id')
                                ->orderBy('nt.id','ASC')
                                ->where('ea.id = :estAsigId')
                                ->setParameter('estAsigId',$a['estAsigId'])
                                ->getQuery()
                                ->getResult();
            //dump($asignaturasNotas);die;
            if($gestion < 2016){
              for($i=$inicio;$i<=$fin;$i++){
                  $existe = 'no';
                  foreach ($asignaturasNotas as $an) {
                      $valorNota = $an['notaCuantitativa'];
                      if($i == $an['idNotaTipo']){
                          $notasArray[$cont]['notas'][] =   array(
                                                  'id'=>$cont."-".$i,
                                                  'idEstudianteNota'=>$an['idNota'],
                                                  'nota'=>$valorNota,
                                                  'idNotaTipo'=>$an['idNotaTipo'],
                                                  'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                              );
                          $existe = 'si';
                          break;
                      }

                  }
                  if($existe == 'no'){
                      $valorNota = 0;
                      $notasArray[$cont]['notas'][] =   array(
                                                  'id'=>$cont."-".$i,
                                                  'idEstudianteNota'=>'nuevo',
                                                  'nota'=>$valorNota,
                                                  'idNotaTipo'=>$i,
                                                  'idEstudianteAsignatura'=>$a['estAsigId']
                                              );
                  }
              }
            }else{
              // Para gestion 2016 en adelante solo se registrara una nota
              //dump($fin);die;
              for($i=$fin;$i<=$fin;$i++){
                  $existe = 'no';
                  foreach ($asignaturasNotas as $an) {
                      $valorNota = $an['notaCuantitativa'];
                      //dump($an['idNotaTipo']);die;
                      if($i == $an['idNotaTipo']){
                          $notasArray[$cont]['notas'][] =   array(
                                                  'id'=>$cont."-".$i,
                                                  'idEstudianteNota'=>$an['idNota'],
                                                  'nota'=>$valorNota,
                                                  'idNotaTipo'=>$an['idNotaTipo'],
                                                  'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                              );
                          $existe = 'si';
                          break;
                      }

                  }
                  if($existe == 'no'){
                      $valorNota = 0;
                      $notasArray[$cont]['notas'][] =   array(
                                                  'id'=>$cont."-".$i,
                                                  'idEstudianteNota'=>'nuevo',
                                                  'nota'=>$valorNota,
                                                  'idNotaTipo'=>$i,
                                                  'idEstudianteAsignatura'=>$a['estAsigId']
                                              );
                  }
              }
            }
            $cont++;
        }
        $areas = array();
        $areas = $notasArray;
        //dump($areas);die;
        $tipo = 'semestre';

        //notas cualitativas
        $arrayCualitativas = array();

        $cualitativas = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion'=>$idInscripcion),array('notaTipo'=>'ASC'));
        // Para primaria y secundaria
        for($i=$inicio;$i<=$fin;$i++){
            $existe = false;
            foreach ($cualitativas as $c) {
                if($c->getNotaTipo()->getId() == $i){
                    $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                 'idEstudianteNotaCualitativa'=>$c->getId(),
                                                 'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                 'notaCualitativa'=>$c->getNotaCualitativa(),
                                                 'notaTipo'=>$c->getNotaTipo()->getNotaTipo()
                                                );
                    $existe = true;
                }
            }
            if($existe == false){
                $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                             'idEstudianteNotaCualitativa'=>'nuevo',
                                             'idNotaTipo'=>$i,
                                             'notaCualitativa'=>'',
                                             'notaTipo'=>$this->literal($i).' Semestre'
                                            );
                $existe = true;
            }
        }

        /**
         * [$tieneEstadoGeneral = determina si se calculara el estado general]
         */
        $estadosGenerales = null;
        if($gestion == 2019){
            $estadosGenerales = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(4,5,22,3,6)));
        }

        $em->getConnection()->commit();
        return array(
                    'tipo'=>$tipo,
                    'areas'=>$areas,
                    'cualitativas'=>$arrayCualitativas,
                    'nivel'=>$nivel,
                    'estudiante'=>$estudiante,
                    //'grado'=>$grado,
                    'gestion'=>$gestion,
                    'infoStudent'=>$infoStudent,
                    'infoUe'=>$infoUe,
                    'operativo'=>$operativo,
                    'estadosAsignatura'=>$estadosAsignatura,
                    'especialidad'=>$aInfoUeducativa['ueducativaInfo']['ciclo'],
                    'area'=>$aInfoUeducativa['ueducativaInfo']['nivel'],
                    'acreditacion'=>$aInfoUeducativa['ueducativaInfo']['grado'],
                    'paralelo'=>$aInfoUeducativa['ueducativaInfo']['paralelo'],
                    'turno'=>$aInfoUeducativa['ueducativaInfo']['turno'],
                    'estadosGenerales'=>$estadosGenerales,
                    'inscripcion'=>$inscripcion
                );
    }

    public function literal($num){
        switch ($num) {
            case '1': $lit = 'Primer'; break;
            case '2': $lit = 'Segundo'; break;
            case '3': $lit = 'Tercer'; break;
            case '4': $lit = 'Cuarto'; break;
            case '6': $lit = 'Primer'; break;
            case '7': $lit = 'Segundo'; break;
            case '8': $lit = 'Tercer'; break;
            case '18': $lit = 'Informe Final Inicial'; break;
            default: $lit = 'Participacion';break;
        }
        return $lit;
    }

    public function operativo($sie,$gestion){
        $em = $this->getDoctrine()->getManager();
        // Obtenemos el operativo para bloquear los controles
        $registroOperativo = $em->createQueryBuilder()
                        ->select('rc.bim1,rc.bim2,rc.bim3,rc.bim4')
                        ->from('SieAppWebBundle:RegistroConsolidacion','rc')
                        ->where('rc.unidadEducativa = :ue')
                        ->andWhere('rc.gestion = :gestion')
                        ->setParameter('ue',$sie)
                        ->setParameter('gestion',$gestion)
                        ->getQuery()
                        ->getResult();
        $operativo = 5;
        if(!$registroOperativo){
            // Si no existe es operativo inicio de gestion
            $operativo = 0;
        }else{
            //dump($registroOperativo);die;
            if($registroOperativo[0]['bim1'] == 0 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 1; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 2; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 3; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 4; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] >= 1){
                $operativo = 5; // Fin de gestion o cerrado
            }
        }
        return $operativo;
    }

    public function createUpdateAction(Request $request){

        $infoStudent = $request->get('infoStudent');
        
        // verificar
        // no debe devolver null
        /*dump($request);
        dump($infoStudent);
        die;*/

        // datos ue
        $infoUe= $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        //dump($aInfoUeducativa);die;
        //$sie = $aInfoUeducativa['requestUser']['sie'];
        //$gestion = $aInfoUeducativa['requestUser']['gestion'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $idCurso = $aInfoUeducativa['ueducativaInfoId']['iecId'];

        $em = $this->getDoctrine()->getManager();
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
        $gestion = $curso->getGestionTipo()->getId();
        // datos estudiante
        $aInfoStudent = json_decode($infoStudent,true);
        $idInscripcion = $aInfoStudent['eInsId'];
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

        // DAtos de las notas cuantitativas
        $idEstudianteNota = $request->get('idEstudianteNota');
        $idNotaTipo = $request->get('idNotaTipo');
        $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
        
        $notas = $request->get('notas');
        $notascenso = $request->get('notasCenso');
        $notasfinales = $request->get('notasFinales');

        /*dump($notas);
        dump($notascenso);
        dump($notasfinales);
        die;*/

        $idEstados = $request->get('idEstados');

        $estadoGeneral = $request->get('estadoGeneral');
        
        /*dump($idEstudianteNota);
        dump($idNotaTipo);
        dump($idEstudianteAsignatura);
        dump($notas);
        dump($idEstados);
        die;*/

        //1: revisar si las sumas estan correctas
        for ($i=0; $i < count($notas) ; $i++) { 
            if ( $notasfinales[$i] <> $notas[$i] + $notascenso[$i] ){
                die;
                return 0;
            }
        }

        //2: si alguna suma pasa de 100 se queda con 100
        for ($i=0; $i < count($notas) ; $i++) { 
            if ( ($notas[$i] + $notascenso[$i]) > 100 ){
                $notasfinales[$i] = 100;
            }
        }

        //3: reemplazamos la nota final en el array notas y todo queda como debe ser
        $notasAux = $notas;
        for ($i=0; $i < count($notas) ; $i++) { 
            $notas[$i] = $notasfinales[$i];
        }

        //4: de aqui en adelante deberia ser todo como esta y se supone que funciona


        if(count($notas)>0){
            // Validamos que las notas sean numeros y esten entre 0 y 100
            if($gestion >= 2016){
                foreach ($notas as $n) {
                   if(!is_numeric($n) or ($n < 0 or $n > 100 )){
                      die;
                      return 0;
                   }
                }
            }else{
                $cont = 0;
                foreach ($notas as $n) {
                   if(!is_numeric($n) or
                      ($idNotaTipo[$cont] == 19 and ($n < 0 or $n > 20) ) or
                      ($idNotaTipo[$cont] == 20 and ($n < 0 or $n > 20) ) or
                      ($idNotaTipo[$cont] == 21 and ($n < 0 or $n > 30) ) or
                      ($idNotaTipo[$cont] == 22 and ($n < 0 or $n > 70) ) or
                      ($idNotaTipo[$cont] == 23 and ($n < 0 or $n > 20) ) or
                      ($idNotaTipo[$cont] == 24 and ($n < 0 or $n > 20) ) or
                      ($idNotaTipo[$cont] == 25 and ($n < 0 or $n > 30) ) or
                      ($idNotaTipo[$cont] == 26 and ($n < 0 or $n > 70) )
                      ){
                      echo "Error al registrar las notas";die;
                      return 0;
                   }
                   $cont++;
                }
            }

            // Reiniciamos el id seq de la tabla estudainte nota
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();

            // Registro y/o modificacion de notas
            for($i=0;$i<count($idEstudianteNota);$i++) {

                // Actualizamos el estado de la asignatura 2016 en adelante
                if($gestion >= 2016){
                    $estAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]);
                    if($notas[$i]==0){ $nuevoEstado = 3; } // RETIRADO
                    if($notas[$i]>=1 and $notas[$i]<=50){ $nuevoEstado = 25; } // PORTERGADO
                    if($notas[$i]>=51 and $notas[$i]<=100){ $nuevoEstado = 5; } // APROBADO
                    $estAsignatura->setEstudianteasignaturaEstado($em->getRepository('SieAppWebBundle:EstudianteasignaturaEstado')->find($nuevoEstado));
                    $em->flush($estAsignatura);
                }else{
                    if($idNotaTipo[$i] == 22 or $idNotaTipo[$i] == 26){
                        $estAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]);
                        if($notas[$i]==0){ $nuevoEstado = 3; } // RETIRADO
                        if($notas[$i]>=1 and $notas[$i]<=35){ $nuevoEstado = 25; } // POSTERGADO
                        if($notas[$i]>=36 and $notas[$i]<=70){ $nuevoEstado = 5; } // APROBADO
                        $estAsignatura->setEstudianteasignaturaEstado($em->getRepository('SieAppWebBundle:EstudianteasignaturaEstado')->find($nuevoEstado));
                        $em->flush($estAsignatura);
                    }
                }

                //dump($idEstudianteNota[$i]); 

                if($idEstudianteNota[$i] == 'nuevo'){

                    
                 
                    $newNota = new EstudianteNota();
                    $newNota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$i]));
                    $newNota->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                    $newNota->setNotaCuantitativa($notas[$i]);
                    $newNota->setNotaCualitativa('');
                    $newNota->setRecomendacion('');
                    $newNota->setUsuarioId($this->session->get('userId'));
                    $newNota->setFechaRegistro(new \DateTime('now'));
                    $newNota->setFechaModificacion(new \DateTime('now'));
                    $newNota->setObs('');
                    $em->persist($newNota);
                    $em->flush();



                }else{
                   
                    $updateNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
                    if($updateNota){
                        $updateNota->setNotaCuantitativa($notas[$i]);
                        $updateNota->setUsuarioId($this->session->get('userId'));
                        $updateNota->setFechaModificacion(new \DateTime('now'));
                        $em->flush();
                    }
                }
            }

            // REGISTRO DEL ESTADO GENERAL SI CORRESPONDE
            // ESTADOS:
            // 5 = PROMOVIDO
            // 22 = POSTERGADO
            // 3 = RETIRADO
            // 6 = NO INCORPORADO

            if ($estadoGeneral != "") {
                $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($estadoGeneral));
                $em->flush();
            }
        }

        // ACTUALIZAR ESTADO DE MATRICULA
        $materias = $em->createQueryBuilder('')
                    ->select('count(ea)')
                    ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                    ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','with','ea.estudianteInscripcion = ei.id')
                    ->where('ei.id = :idInscripcion')
                    ->setParameter('idInscripcion', $idInscripcion)
                    ->getQuery()
                    ->getSingleResult();

        $notas = $em->createQueryBuilder('')
                    ->select('en')
                    ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                    ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','with','ea.estudianteInscripcion = ei.id')
                    ->innerJoin('SieAppWebBundle:EstudianteNota','en','with','en.estudianteAsignatura = ea.id')
                    ->where('ei.id = :idInscripcion')
                    ->setParameter('idInscripcion', $idInscripcion)
                    ->getQuery()
                    ->getResult();

        if($materias[1] == count($notas)){
          $nuevoEstado = $inscripcion->getEstadomatriculaTipo()->getId();
          $contadorCeros = 0;
          $contadorReprobados = 0;
          $contadorAprobados = 0;
          foreach ($notas as $n) {
            if($n->getNotaCuantitativa() == 0){ $contadorCeros+=1; } // PORTERGADO
            if($n->getNotaCuantitativa()>=1 and $n->getNotaCuantitativa()<=50){ $contadorReprobados+=1; } // PORTERGADO
            if($n->getNotaCuantitativa()>=51 and $n->getNotaCuantitativa()<=100){  $contadorAprobados+=1; } // APROBADO
          }  

          if($contadorCeros == count($notas)){
            $nuevoEstado = 6; //NO INCORPORADO
          }else{
            if($contadorAprobados == count($notas)){
              $nuevoEstado = 5; // PROMOVIDO
            }else{
              if ($contadorCeros > 0) {
                $nuevoEstado = 3; // RETIRADO
              }else{
                $nuevoEstado = 22; // POSTERGADO
              }
            }
          }

          $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($nuevoEstado));
          // $em->persist($inscripcion);
          $em->flush();
        }
        die;
        return 1;
    }
}
