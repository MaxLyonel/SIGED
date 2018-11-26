<?php

namespace Sie\HerramientaBundle\Controller;

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
use Sie\ProcesosBundle\Controller\WfTramiteController;
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnicoTipo;
use Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo;
use Sie\AppWebBundle\Entity\GestionTipo;




/**
 * ChangeMatricula controller.
 *
 */
class ConsultasController extends Controller {
    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction (Request $request) {


        return $this->render($this->session->get('pathSystem') . ':Consultas:index.html.twig');

    }

    public function buscarInstitucioneducativaAction(Request $request){

        $Idinstitucion=$request->get('inst_edu');
        $institucionId = ($request->get('inst_edu')) ? $Idinstitucion :"";
        $gestion = ($request->get('gestion')) ? $request->get('gestion') :"";
        $ci =  ($request->get('ci')) ? $request->get('ci') :"";
        $paterno = ($request->get('paterno')) ? $request->get('paterno') :"";
        $materno = ($request->get('materno')) ? $request->get('materno') :"";
        $nombre = ($request->get('nombre')) ? $request->get('nombre') :   "";
        $data = $this->busquedas($institucionId,$ci,$gestion,$paterno,$materno,$nombre);

        //identificar al tipo de educacion al cual pertenece la unidad.
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT ie.id ,ie.institucioneducativa_tipo_id, ie.orgcurricular_tipo_id,ie.institucioneducativa 
                                                FROM institucioneducativa ie WHERE ie.orgcurricular_tipo_id in (1,2)
                                                and ie.id = $Idinstitucion
                                                ORDER BY 1");
        $query->execute();
        $tipoInstitucion = $query->fetch();

        $query = $em->getConnection()->prepare("SELECT ps.nombre || ' ' || ps.paterno ||' '|| ps.materno As nombre_director,ct.cargo
                                                FROM maestro_inscripcion mins 
                                                INNER JOIN institucioneducativa_sucursal ies ON ies.id = mins.institucioneducativa_sucursal_id
                                                INNER JOIN persona ps ON ps.id = mins.persona_id							 -- MAESTROINSCRIPCION-PERSONA
                                                INNER JOIN cargo_tipo ct ON ct.id = mins.cargo_tipo_id				 -- MAESTROINSCRIPCION-CARGO
                                                INNER JOIN formacion_tipo ft ON ft.id = mins.formacion_tipo_id -- MAESTROINSCRIPCION-FORMACION MAESTRO
                                                WHERE mins.institucioneducativa_id =$institucionId--40730448--80730460  --81410151 -- id_centro			
                                                AND mins.cargo_tipo_id in(SELECT ct.id from cargo_tipo ct  WHERE ct.rol_tipo_id in (2,9) AND ct.esdirector = TRUE)	
                                                 AND mins.es_vigente_administrativo = TRUE 		
                                                 AND mins.gestion_tipo_id = $gestion				
                                                ORDER BY 1");
        $query->execute();
        $infoDirector= $query->fetch();


        switch ($tipoInstitucion['institucioneducativa_tipo_id']) {

            case 1: // Educación Regular
                return $this->render('SieHerramientaBundle:Consultas:infoUE.html.twig',array('data'=>$data['maestros'],
                    'infoInstitucion'=>$tipoInstitucion,
                    'infoDirector'=>$infoDirector
                ));
                break;
            case 2://Educación Alternativa
                $query = $em->getConnection()->prepare("SELECT ies.institucioneducativa_id, ies.gestion_tipo_id, ies.nombre_subcea, ies.periodo_tipo_id, pt.periodo from institucioneducativa_sucursal ies, periodo_tipo pt where ies.periodo_tipo_id = pt.id and ies.institucioneducativa_id = $Idinstitucion and ies.gestion_tipo_id = $gestion");
                $query->execute();
                $subcentro = $query->fetchAll();
                return $this->render('SieHerramientaBundle:Consultas:infoUEAlternativa.html.twig',array('data'=>$subcentro
                ));
                break;
            case 4://Educación Especial
                return $this->render('SieHerramientaBundle:Consultas:infoUE.html.twig',array('data'=>$data['maestros'],
                    'infoInstitucion'=>$tipoInstitucion,
                    'infoDirector'=>$infoDirector
                ));
                break;
            case 5://Educacion Permanente
                $query = $em->getConnection()->prepare("SELECT ies.institucioneducativa_id, ies.gestion_tipo_id, ies.nombre_subcea, ies.periodo_tipo_id, pt.periodo from institucioneducativa_sucursal ies, periodo_tipo pt where ies.periodo_tipo_id = pt.id and ies.institucioneducativa_id = $Idinstitucion and ies.gestion_tipo_id = $gestion");
                $query->execute();
                $subcentro = $query->fetchAll();
                return $this->render('SieHerramientaBundle:Consultas:infoUEAlternativa.html.twig',array('data'=>$subcentro
                ));
                break;
            case 6://EJA Primaria
                break;
            case 10://PNP
                break;
            default: //Educación Regular
                return $this->render('SieHerramientaBundle:Consultas:infoUE.html.twig',array('data'=>$data['maestros'],
                    'infoInstitucion'=>$tipoInstitucion,
                    'infoDirector'=>$infoDirector
                    //'data'=>$infomaestro
                ));
                break;
        }

        //Obtenemos la informacion del director de la UE o CEA
        //infoDirector



        /*if($institucionId!=''){
        $query = $em->getConnection()->prepare("SELECT ie.id, ie.institucioneducativa  from  institucioneducativa ie WHERE ie.id = $institucionId");
        $query->execute();
        $infoInstitucion= $query->fetch();
        } else {
           $infoInstitucion=array();
        }*/


        //para Busqueda por Ci

       /* $query = $em->getConnection()->prepare("SELECT  mins.institucioneducativa_id,mins.gestion_tipo_id,ps.carnet,ps.complemento,ps.nombre,ps.paterno,ps.materno,
                                                                ft.formacion,ps.genero_tipo_id,ps.rda,ps.fecha_nacimiento,ps.correo,ps.celular,ps.direccion,
                                                                ct.cargo
                                                FROM maestro_inscripcion mins 
                                                        INNER JOIN formacion_tipo ft ON ft.id = mins.formacion_tipo_id -- MAESTROINSCRIPCION-FORMACION MAESTRO
                                                        INNER JOIN persona ps ON ps.id = mins.persona_id							 -- MAESTROINSCRIPCION-PERSONA
                                                        INNER JOIN cargo_tipo ct ON ct.id = mins.cargo_tipo_id				 -- MAESTROINSCRIPCION-CARGO
                                                        WHERE 
                                                                    mins.cargo_tipo_id in(SELECT ct.id from cargo_tipo ct  WHERE ct.rol_tipo_id=2)	
                                                                    AND mins.gestion_tipo_id=$gestion
                                                                    AND ps.carnet = '$ci'
                                                ORDER BY 5 ");
        $query->execute();
        $infomaestro= $query->fetchAll();*/

    }

    public function listaMaestrosAlterAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $gestion = $request->get('gestion_tipo_id');
        $idinstitucion = $request->get('institucioneducativa_id');
        $idperiodo = $request->get('periodo_tipo_id');
        $query = $em->getConnection()->prepare("SELECT ps.carnet,  mins.institucioneducativa_id,mins.gestion_tipo_id,ps.carnet,ps.complemento,ps.nombre,ps.paterno,ps.materno,
                                                        ft.formacion,ps.genero_tipo_id,ps.rda,ps.fecha_nacimiento,ps.correo,ps.celular,ps.direccion,
                                                        ct.cargo,ie.orgcurricular_tipo_id,ies.periodo_tipo_id,pt.periodo,mins.es_vigente_administrativo
                                                FROM maestro_inscripcion mins 
                                                INNER JOIN persona ps ON ps.id = mins.persona_id							 -- MAESTROINSCRIPCION-PERSONA		
                                                INNER JOIN institucioneducativa_sucursal ies ON ies.id = mins.institucioneducativa_sucursal_id
                                                INNER JOIN institucioneducativa ie ON ie.id = mins.institucioneducativa_id
                                                INNER JOIN formacion_tipo ft ON ft.id = mins.formacion_tipo_id -- MAESTROINSCRIPCION-FORMACION MAESTRO
                                                INNER JOIN cargo_tipo ct ON ct.id = mins.cargo_tipo_id				 -- MAESTROINSCRIPCION-CARGO
                                                LEFT JOIN periodo_tipo pt ON pt.id = ies.periodo_tipo_id
                                                WHERE  mins.institucioneducativa_id = $idinstitucion --80730660 --82220199 --80730660 -- 40730256--80730815  --40730424  --80730660 --40730424 --40730424 -- id_centro
                                                     AND mins.cargo_tipo_id in(0,1)	
                                                     AND mins.gestion_tipo_id=$gestion
                                                     AND ies.periodo_tipo_id=$idperiodo
                                                                    --AND mins.es_vigente_administrativo = TRUE 
                                                                    --AND ies.institucioneducativa_id = 82220199
                                                ORDER BY 4,5,6,pt.periodo");
        $query->execute();
        $infomaestro = $query->fetchAll();

        $query = $em->getConnection()->prepare("SELECT ie.id ,ie.institucioneducativa_tipo_id, ie.orgcurricular_tipo_id,ie.institucioneducativa 
                                                FROM institucioneducativa ie WHERE ie.orgcurricular_tipo_id in (1,2)
                                                and ie.id = $idinstitucion
                                                ORDER BY 1");
        $query->execute();
        $tipoInstitucion = $query->fetch();

        $query = $em->getConnection()->prepare("SELECT ps.nombre || ' ' || ps.paterno ||' '|| ps.materno As nombre_director,ct.cargo
                                                FROM maestro_inscripcion mins 
                                                INNER JOIN institucioneducativa_sucursal ies ON ies.id = mins.institucioneducativa_sucursal_id
                                                INNER JOIN persona ps ON ps.id = mins.persona_id							 -- MAESTROINSCRIPCION-PERSONA
                                                INNER JOIN cargo_tipo ct ON ct.id = mins.cargo_tipo_id				 -- MAESTROINSCRIPCION-CARGO
                                                INNER JOIN formacion_tipo ft ON ft.id = mins.formacion_tipo_id -- MAESTROINSCRIPCION-FORMACION MAESTRO
                                                WHERE mins.institucioneducativa_id =$idinstitucion--40730448--80730460  --81410151 -- id_centro			
                                                AND mins.cargo_tipo_id in(SELECT ct.id from cargo_tipo ct  WHERE ct.rol_tipo_id in (2,9) AND ct.esdirector = TRUE)	
                                                 AND mins.es_vigente_administrativo = TRUE 		
                                                 AND mins.gestion_tipo_id = $gestion				
                                                ORDER BY 1");
        $query->execute();
        $infoDirector= $query->fetch();
        //dump($infomaestro);die;
        return $this->render('SieHerramientaBundle:Consultas:infoUE.html.twig',array('data'=>$infomaestro,
            'infoInstitucion'=>$tipoInstitucion,
            'infoDirector'=>$infoDirector
        ));
    }

    public function buscarCiAction(Request $request){

        $Idinstitucion=$request->get('inst_edu');
        $institucionId = ($request->get('inst_edu')) ? $Idinstitucion :"";
        $gestion = ($request->get('gestion')) ? $request->get('gestion') :"";
        $ci =  ($request->get('ci')) ? $request->get('ci') :"";
        $paterno = ($request->get('paterno')) ? $request->get('paterno') :"";
        $materno = ($request->get('materno')) ? $request->get('materno') :"";
        $nombre = ($request->get('nombre')) ? $request->get('nombre') :   "";
        $data = $this->busquedas($institucionId,$ci,$gestion,$paterno,$materno,$nombre);
        //dump($data);die;

        $em = $this->getDoctrine()->getManager();

        //para Busqueda por Ci

        $query = $em->getConnection()->prepare("SELECT  mins.institucioneducativa_id,mins.gestion_tipo_id,ps.carnet,ps.complemento,ps.nombre,ps.paterno,ps.materno,
                                                ft.formacion,ps.genero_tipo_id,ps.rda,ps.fecha_nacimiento,ps.correo,ps.celular,ps.direccion,
                                                ct.cargo
                                                FROM maestro_inscripcion mins 
                                                INNER JOIN persona ps ON ps.id = mins.persona_id							 -- MAESTROINSCRIPCION-PERSONA
                                                INNER JOIN institucioneducativa_sucursal ies ON ies.id = mins.institucioneducativa_sucursal_id
                                                INNER JOIN formacion_tipo ft ON ft.id = mins.formacion_tipo_id -- MAESTROINSCRIPCION-FORMACION MAESTRO
                                                INNER JOIN cargo_tipo ct ON ct.id = mins.cargo_tipo_id				 -- MAESTROINSCRIPCION-CARGO
                                                WHERE mins.cargo_tipo_id in(SELECT ct.id from cargo_tipo ct  WHERE ct.rol_tipo_id=2)	
                                                                                                                    AND mins.gestion_tipo_id=$gestion
                                                                                                                    AND ps.carnet LIKE'$ci%'
                                                                                                                    AND mins.es_vigente_administrativo = TRUE
                                                ORDER BY 5");
        $query->execute();
        $infomaestro= $query->fetchAll();

            //dump($infomaestro);die;

        return $this->render('SieHerramientaBundle:Consultas:infoMaestro.html.twig',array(
            'data'=>$infomaestro
        ));

    }



    public function busquedas ($institucionId,$ci,$gestion,$paterno,$materno,$nombre){
        //dump($institucionId);die;

        if($institucionId!='')
        {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT  mins.institucioneducativa_id,mins.gestion_tipo_id,ps.carnet,ps.complemento,ps.nombre,ps.paterno,ps.materno,
                                                        ft.formacion,ps.genero_tipo_id,ps.rda,ps.fecha_nacimiento,ps.correo,ps.celular,ps.direccion,
                                                        ct.cargo
                                                FROM maestro_inscripcion mins 		
                                                INNER JOIN persona ps ON ps.id = mins.persona_id							 -- MAESTROINSCRIPCION-PERSONA		
                                                INNER JOIN institucioneducativa_sucursal ies ON ies.id = mins.institucioneducativa_sucursal_id
                                                INNER JOIN institucioneducativa ie ON ie.id = mins.institucioneducativa_id
                                                INNER JOIN formacion_tipo ft ON ft.id = mins.formacion_tipo_id -- MAESTROINSCRIPCION-FORMACION MAESTRO
                                                INNER JOIN cargo_tipo ct ON ct.id = mins.cargo_tipo_id				 -- MAESTROINSCRIPCION-CARGO
                                                WHERE mins.institucioneducativa_id = $institucionId -- id_centro
                                                            AND mins.cargo_tipo_id in(0,1)	
                                                            AND mins.gestion_tipo_id=$gestion
                                                            AND mins.es_vigente_administrativo = TRUE 
                                                  ORDER BY 4,5,6");
        $query->execute();
        $maestros = $query->fetchAll();
        $data['maestros'] = $maestros;
        return $data;
        }elseif ($ci!=''){
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare("SELECT ps.id,ps.carnet,ps.complemento,ps.paterno,ps.materno,ps.nombre,ct.cargo,ps.genero_tipo_id,ps.rda,ps.fecha_nacimiento,ps.correo,ps.celular,ps.direccion,
                                                                ie.id as id_institucioneducativa,ie.institucioneducativa,mins.gestion_tipo_id,mins.periodo_tipo_id,
                                                                ic.id as ic_id ,ieco.id as ieco_id, iecom.nota_tipo_id ,iecom.id as iecom_id, ic.grado_tipo_id,ic.paralelo_tipo_id,ic.nivel_tipo_id,(case  WHEN ic.turno_tipo_id = 1 THEN 'mañana' WHEN ic.turno_tipo_id = 2  THEN 'tarde'  WHEN ic.turno_tipo_id =3  THEN 'NOche'  else 'Otro' END) as turno,
                                                                ieco.asignatura_tipo_id,asg.asignatura,ie.institucioneducativa_tipo_id,iet.descripcion
                                                                ,piecc.cursocorto_tipo_id,pcct.cursocorto
                                                    FROM       institucioneducativa_curso ic
                                                    INNER JOIN institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id = ic.id --INSTITUCIONEDUCATIVACURSO-INSEDUCURSOOFERTA
                                                    INNER JOIN institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id --INSEDUCUROFER-INSEDUCUROFERMAESTR
                                                    INNER JOIN maestro_inscripcion mins on mins.id = iecom.maestro_inscripcion_id --INSEDUCUROFERMAESTR-MAESTRINSCRIP
                                                    INNER JOIN formacion_tipo ft on ft.id =mins.formacion_tipo_id ---MAESTROINSCR-FORMACION
                                                    INNER JOIN persona ps on ps.id = mins.persona_id ---MAESTROINSCR-PERSONA
                                                    INNER JOIN institucioneducativa ie on ie.id = ic.institucioneducativa_id ----INSTITUCIONEDUCATIVACURSO-INSTITUCIONEDUCATIVA
                                                    INNER JOIN cargo_tipo ct ON ct.id = mins.cargo_tipo_id				--MAESTROiNSCRIPCION-CARGO
                                                    INNER JOIN asignatura_tipo asg ON asg.id = ieco.asignatura_tipo_id ----INSTITUCIONEDUCATIVACURSOOFERTA-ASIGNATURA
                                                    INNER JOIN institucioneducativa_tipo iet ON iet.id = ie.institucioneducativa_tipo_id 
                                                    LEFT JOIN permanente_institucioneducativa_cursocorto piecc ON piecc.institucioneducativa_curso_id = ic.id
                                                    LEFT JOIN permanente_cursocorto_tipo pcct ON pcct.id = piecc.cursocorto_tipo_id
                                                    WHERE		ps.carnet = '$ci'    AND mins.gestion_tipo_id = $gestion  AND mins.es_vigente_administrativo =  TRUE
                                                    ORDER BY ic.id");
            $query->execute();
            $maestro_x = $query->fetchAll();
            $data['maestros_x'] = $maestro_x;
            return $data;
        }



    }

    public function fichapersonalAction(Request $request){
        //dump($request);die;

        $Idinstitucion=$request->get('inst_edu');
        $gestion = ($request->get('gestion')) ? $request->get('gestion') :"";
        $ci =  ($request->get('ci')) ? $request->get('ci') :"";
        $complemento =  ($request->get('complemento')) ? $request->get('complemento') :"";

        //informacion del maesttro
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT * from persona ps WHERE ps.carnet='$ci' and ps.complemento='$complemento' ");
        $query->execute();
        $infomaestro= $query->fetch();
        //Cantidad de UE
        $query = $em->getConnection()->prepare("SELECT  distinct ic.institucioneducativa_id
                                                FROM       institucioneducativa_curso ic
                                                INNER JOIN institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id = ic.id --INSTITUCIONEDUCATIVACURSO-INSEDUCURSOOFERTA
                                                INNER JOIN institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id --INSEDUCUROFER-INSEDUCUROFERMAESTR
                                                INNER JOIN maestro_inscripcion mins on mins.id = iecom.maestro_inscripcion_id --INSEDUCUROFERMAESTR-MAESTRINSCRIP
                                                INNER JOIN formacion_tipo ft on ft.id =mins.formacion_tipo_id ---MAESTROINSCR-FORMACION
                                                INNER JOIN persona ps on ps.id = mins.persona_id ---MAESTROINSCR-PERSONA
                                                INNER JOIN institucioneducativa ie on ie.id = ic.institucioneducativa_id ----INSTITUCIONEDUCATIVACURSO-INSTITUCIONEDUCATIVA
                                                INNER JOIN cargo_tipo ct ON ct.id = mins.cargo_tipo_id				--MAESTROiNSCRIPCION-CARGO
                                                INNER JOIN asignatura_tipo asg ON asg.id = ieco.asignatura_tipo_id ----INSTITUCIONEDUCATIVACURSOOFERTA-ASIGNATURA
                                                INNER JOIN institucioneducativa_tipo iet ON iet.id = ie.institucioneducativa_tipo_id 
                                                LEFT JOIN permanente_institucioneducativa_cursocorto piecc ON piecc.institucioneducativa_curso_id = ic.id
                                                LEFT JOIN permanente_cursocorto_tipo pcct ON pcct.id = piecc.cursocorto_tipo_id
                                                WHERE		ps.carnet = '$ci' AND ps.complemento = '$complemento' AND mins.gestion_tipo_id =$gestion AND mins.es_vigente_administrativo =  TRUE
                                                ORDER BY ic.institucioneducativa_id");
        $query->execute();
        $idInstitucionEducativas= $query->fetchAll();


        return $this->render('SieHerramientaBundle:Consultas:fichapersonal.html.twig',array('id_institucioneducactivas'=>$idInstitucionEducativas
        ,'maestro'=>$infomaestro,'gestion'=>$gestion
       ));
    }
     public function fichapersonal_materiasAction(Request $request){ //Recibir el parametro tipo de institucion_tipo_id
      // dump($request);die;
         //case y evalual // tipo  y mandar a diferentes tablas
        $ci=$request->get('ci');
        $complemento=$request->get('complemento');
        $gestion = $request->get('gestion');
        $inst_edu = $request->get('inst_edu');
         $institucioneducativa_id = $request->get('institucioneducativa_id');
         //infomaterias
          $em = $this->getDoctrine()->getManager();
         $query = $em->getConnection()->prepare("SELECT ps.id,ps.carnet,ps.complemento,ps.paterno,ps.materno,ps.nombre,ct.cargo,ps.genero_tipo_id,ps.rda,ps.fecha_nacimiento,ps.correo,ps.celular,ps.direccion,
                                                            ie.id as id_institucioneducativa,ie.institucioneducativa,mins.gestion_tipo_id,mins.periodo_tipo_id,
                                                            ic.id as ic_id ,ieco.id as ieco_id, iecom.nota_tipo_id ,iecom.id as iecom_id, ic.grado_tipo_id,ic.paralelo_tipo_id,ic.nivel_tipo_id,trt.turno,
                                                            ieco.asignatura_tipo_id,asg.asignatura,ie.institucioneducativa_tipo_id,iet.descripcion,ie.institucioneducativa_tipo_id,nt.nota_tipo
                                                            ,pt.paralelo,gt.grado,nvt.nivel,prt.periodo
                                                            --,piecc.cursocorto_tipo_id,pcct.cursocorto
                                                FROM       institucioneducativa_curso ic
                                                INNER JOIN institucioneducativa_curso_oferta ieco on ieco.insitucioneducativa_curso_id = ic.id --INSTITUCIONEDUCATIVACURSO-INSEDUCURSOOFERTA
                                                INNER JOIN institucioneducativa_curso_oferta_maestro iecom on iecom.institucioneducativa_curso_oferta_id = ieco.id --INSEDUCUROFER-INSEDUCUROFERMAESTR
                                                INNER JOIN maestro_inscripcion mins on mins.id = iecom.maestro_inscripcion_id --INSEDUCUROFERMAESTR-MAESTRINSCRIP
                                                INNER JOIN formacion_tipo ft on ft.id =mins.formacion_tipo_id ---MAESTROINSCR-FORMACION
                                                INNER JOIN persona ps on ps.id = mins.persona_id ---MAESTROINSCR-PERSONA
                                                INNER JOIN institucioneducativa ie on ie.id = ic.institucioneducativa_id ----INSTITUCIONEDUCATIVACURSO-INSTITUCIONEDUCATIVA
                                                INNER JOIN cargo_tipo ct ON ct.id = mins.cargo_tipo_id				--MAESTROiNSCRIPCION-CARGO
                                                INNER JOIN asignatura_tipo asg ON asg.id = ieco.asignatura_tipo_id ----INSTITUCIONEDUCATIVACURSOOFERTA-ASIGNATURA
                                                INNER JOIN institucioneducativa_tipo iet ON iet.id = ie.institucioneducativa_tipo_id and iet.orgcurricular_tipo_id in(1,2)
                                                LEFT JOIN nota_tipo nt ON nt.id = iecom.nota_tipo_id  
                                                INNER JOIN paralelo_tipo pt ON ic.paralelo_tipo_id = pt.id
                                                INNER JOIN grado_tipo gt ON gt.id=ic.grado_tipo_id
                                                INNER JOIN nivel_tipo nvt ON nvt.id = ic.nivel_tipo_id 
                                                INNER JOIN periodo_tipo prt ON ic.periodo_tipo_id = prt.id 
                                                INNER JOIN turno_tipo trt on trt.id = ic.turno_tipo_id
                                                LEFT JOIN permanente_institucioneducativa_cursocorto piecc ON piecc.institucioneducativa_curso_id = ic.id
                                                LEFT JOIN permanente_cursocorto_tipo pcct ON pcct.id = piecc.cursocorto_tipo_id
                                                WHERE		ps.carnet = '$ci' AND mins.gestion_tipo_id =$gestion AND mins.es_vigente_administrativo =  TRUE and ic.institucioneducativa_id= $inst_edu --81410151 --80730660
                                                ORDER BY ic.id");
         $query->execute();
         $infomaterias= $query->fetchAll();
         //dump($infomaterias);die;
        return $this->render('SieHerramientaBundle:Consultas:infoMaterias.html.twig',array('infomaterias'=>$infomaterias, 'institucioneducativa_id'=>$institucioneducativa_id));
     }

     function BuscardpAction(Request $request){
         //ump($request);die;
         $Idinstitucion=$request->get('inst_edu');
         $institucionId = ($request->get('inst_edu')) ? $Idinstitucion :"";
         $gestion = ($request->get('gestion')) ? $request->get('gestion') :"";
         $ci =  ($request->get('ci')) ? $request->get('ci') :"";
         $paterno = ($request->get('paterno')) ? $request->get('paterno') :"";
         $materno = ($request->get('materno')) ? $request->get('materno') :"";
         $nombre = ($request->get('nombre')) ? $request->get('nombre') :   "";
         $data = $this->busquedas($institucionId,$ci,$gestion,$paterno,$materno,$nombre);
         //dump($data);die;
         if($institucionId!=''){
             $em = $this->getDoctrine()->getManager();
             $query = $em->getConnection()->prepare("SELECT ie.id, ie.institucioneducativa  from  institucioneducativa ie WHERE ie.id = $institucionId");
             $query->execute();
             $infoInstitucion= $query->fetch();
         } else {
             $infoInstitucion=array();
         }
         $em = $this->getDoctrine()->getManager();
         $query = $em->getConnection()->prepare("SELECT    ps.nombre || ' ' || ps.paterno ||' '|| ps.materno As nombre_director,	
                                                              ct.cargo
                                                FROM maestro_inscripcion mins 
                                                        INNER JOIN formacion_tipo ft ON ft.id = mins.formacion_tipo_id -- MAESTROINSCRIPCION-FORMACION MAESTRO
                                                        INNER JOIN persona ps ON ps.id = mins.persona_id							 -- MAESTROINSCRIPCION-PERSONA
                                                        INNER JOIN cargo_tipo ct ON ct.id = mins.cargo_tipo_id				 -- MAESTROINSCRIPCION-CARGO
                                                        WHERE mins.institucioneducativa_id = $institucionId--40730448--80730460  --81410151 -- id_centro			
                                                                    AND mins.cargo_tipo_id in(SELECT ct.id from cargo_tipo ct  WHERE ct.rol_tipo_id in (2,9) AND ct.esdirector = TRUE)	
                                                                    AND mins.gestion_tipo_id=$gestion				
                                                ORDER BY 1 ");
         $query->execute();
         $infoDirector= $query->fetch();
         //para Busqueda por dp

         $query = $em->getConnection()->prepare("SELECT  mins.institucioneducativa_id,mins.gestion_tipo_id,ps.carnet,ps.complemento,ps.nombre,ps.paterno,ps.materno,
                                                                ft.formacion,ps.genero_tipo_id,ps.rda,ps.fecha_nacimiento,ps.correo,ps.celular,ps.direccion,
                                                                ct.cargo
                                                FROM maestro_inscripcion mins 
                                                        INNER JOIN formacion_tipo ft ON ft.id = mins.formacion_tipo_id -- MAESTROINSCRIPCION-FORMACION MAESTRO
                                                        INNER JOIN persona ps ON ps.id = mins.persona_id							 -- MAESTROINSCRIPCION-PERSONA
                                                        INNER JOIN cargo_tipo ct ON ct.id = mins.cargo_tipo_id				 -- MAESTROINSCRIPCION-CARGO
                                                        WHERE 
                                                                    mins.cargo_tipo_id in(SELECT ct.id from cargo_tipo ct  WHERE ct.rol_tipo_id=2)	
                                                                    --AND mins.gestion_tipo_id = 2018
                                                                    AND ps.nombre LIKE '$nombre%' AND ps.paterno LIKE '$paterno%' AND ps.materno LIKE '$materno%'
                                                ORDER BY 5 ");
         $query->execute();
         $infomaestro= $query->fetchAll();
        // dump($infomaestro);die;

         return $this->render('SieHerramientaBundle:Consultas:infoMaestro.html.twig',array(
             'data'=>$infomaestro
         ));

     }


}
