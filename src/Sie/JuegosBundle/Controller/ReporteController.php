<?php

namespace Sie\JuegosBundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sie\AppWebBundle\Entity\EstudianteInscripcionJuegos;

class ReporteController extends Controller {

    public $session;
    public $idInstitucion;
    private $nivelId;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        //$this->aCursos = $this->fillCursos();
    }

    public function indexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');  
        $id_usuario = $this->session->get('userName'); 


        return $this->render($this->session->get('pathSystem') . ':Default:index.html.twig', array(
                    'infoEntidad' => $objEntidad[0],
                    'infoDisciplinas' => $entityDisciplinas,
        ));       
    }

    public function fasePreviaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');  
        $id_usuario = $this->session->get('userName'); 

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }    

        $objEntidad = $this->registroPorFase(1);
        $objEntidadGenero = $this->registroPorGeneroFase(1);

        return $this->render($this->session->get('pathSystem') . ':Default:index.html.twig', array(
                    'infoEntidad' => array('id'=>$objEntidad[0]['id'],'nombre'=>$objEntidad[0]['nombre']),
                    'infoDisciplinas' => $objEntidad,
                    'infoGeneros' => $objEntidadGenero,
        ));
    
    }

    public function fasePrimeraAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');  
        $id_usuario = $this->session->get('userName'); 

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }    

        $objEntidad = $this->registroPorFase(2);
        $objEntidadGenero = $this->registroPorGeneroFase(2);

        return $this->render($this->session->get('pathSystem') . ':Default:index.html.twig', array(
                    'infoEntidad' => array('id'=>$objEntidad[0]['id'],'nombre'=>$objEntidad[0]['nombre']),
                    'infoDisciplinas' => $objEntidad,
                    'infoGeneros' => $objEntidadGenero,
        ));     
    }

    public function faseSegundaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');  
        $id_usuario = $this->session->get('userName'); 

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }   

        $objEntidad = $this->registroPorFase(3);
        $objEntidadGenero = $this->registroPorGeneroFase(3);

        return $this->render($this->session->get('pathSystem') . ':Default:index.html.twig', array(
                    'infoEntidad' => array('id'=>$objEntidad[0]['id'],'nombre'=>$objEntidad[0]['nombre']),
                    'infoDisciplinas' => $objEntidad,
                    'infoGeneros' => $objEntidadGenero,
        ));      
    } 

    public function faseTerceraAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');  
        $id_usuario = $this->session->get('userName'); 

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }         

        $objEntidad = $this->registroPorFase(4);
        $objEntidadGenero = $this->registroPorGeneroFase(4);

        return $this->render($this->session->get('pathSystem') . ':Default:index.html.twig', array(
                    'infoEntidad' => array('id'=>$objEntidad[0]['id'],'nombre'=>$objEntidad[0]['nombre']),
                    'infoDisciplinas' => $objEntidad,
                    'infoGeneros' => $objEntidadGenero,
        ));
    }    

    public function registroPorFase($fase) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $em = $this->getDoctrine()->getManager();
        $id_usuario = $this->session->get('userId');
        $aRoles = $this->session->get('roluser');

        if($aRoles[0]['id'] == 9) // Director
        {
            $ue = $this->session->get('userName');
            $queryEntidad = $em->getConnection()->prepare("
                    select id, institucioneducativa as nombre from institucioneducativa where id = ".$ue."
                ");
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll();

            $query = $em->getConnection()->prepare("
                    select v.id, v.nombre, nt.id as nivel_id, nt.nivel, dt.id as disciplina_id, dt.disciplina, coalesce(v.cantidad,0) as cantidad from (
                    select ie.id, ie.institucioneducativa as nombre, pt.disciplina_tipo_id, count(eij.id) as cantidad
                    from estudiante_inscripcion_juegos as eij
                    inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                    inner join (select * from institucioneducativa_curso where gestion_tipo_id = ".$gestionActual.") as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                    inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id 
                    where ie.id = ".$objEntidad[0]['id']." and eij.gestion_tipo_id = ".$gestionActual." and eij.fase_tipo_id = ".$fase."
                    group by ie.id, ie.institucioneducativa, pt.disciplina_tipo_id
                    ) as v 
                    right join disciplina_tipo as dt on dt.id = v.disciplina_tipo_id
                    left join nivel_tipo as nt on nt.id = dt.nivel_tipo_id
                    order by nt.id, dt.disciplina
                ");
        }

        if($aRoles[0]['id'] == 10 or $aRoles[0]['id'] == 11) // Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.id, lt.lugar as nombre from usuario as u 
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$id_usuario." and rol_tipo_id = ".$aRoles[0]['id']."
                ");
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll();
            $query = $em->getConnection()->prepare("
                    select v.id, v.nombre, nt.id as nivel_id, nt.nivel, dt.id as disciplina_id, dt.disciplina, coalesce(v.cantidad,0) as cantidad from (
                    select lt.id, lt.lugar as nombre, pt.disciplina_tipo_id, count(eij.id) as cantidad
                    from estudiante_inscripcion_juegos as eij
                    inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                    inner join (select * from institucioneducativa_curso where gestion_tipo_id = ".$gestionActual.") as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
                    inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id 
                    where lt.id = ".$objEntidad[0]['id']." and eij.gestion_tipo_id = ".$gestionActual." and eij.fase_tipo_id = ".$fase."
                    group by lt.id, lt.lugar, pt.disciplina_tipo_id
                    ) as v 
                    right join disciplina_tipo as dt on dt.id = v.disciplina_tipo_id
                    left join nivel_tipo as nt on nt.id = dt.nivel_tipo_id
                    order by nt.id, dt.disciplina
                ");
        }

        if($aRoles[0]['id'] == 6) // Circunscripcion
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select ur.circunscripcion_tipo_id as id, 'Circunscripción '||cast(ur.circunscripcion_tipo_id as varchar) as nombre from usuario as u 
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    where u.id = ".$id_usuario." and rol_tipo_id = ".$aRoles[0]['id']."
                ");
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll();
            $query = $em->getConnection()->prepare("
                    select v.id, 'Circunscripción '||cast(v.id as varchar) as nombre, nt.id as nivel_id, nt.nivel, dt.id as disciplina_id, dt.disciplina, coalesce(v.cantidad,0) as cantidad from (
                    select jg.circunscripcion_tipo_id as id, pt.disciplina_tipo_id, count(eij.id) as cantidad
                    from estudiante_inscripcion_juegos as eij
                    inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                    inner join (select * from institucioneducativa_curso where gestion_tipo_id = ".$gestionActual.") as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id 
                    where jg.circunscripcion_tipo_id = ".$objEntidad[0]['id']." and eij.gestion_tipo_id = ".$gestionActual." and eij.fase_tipo_id = ".$fase."
                    group by jg.circunscripcion_tipo_id, pt.disciplina_tipo_id
                    ) as v 
                    right join disciplina_tipo as dt on dt.id = v.disciplina_tipo_id
                    left join nivel_tipo as nt on nt.id = dt.nivel_tipo_id
                    order by nt.id, dt.disciplina
                ");
        }

        if($aRoles[0]['id'] == 7) // Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.codigo as id, lt.lugar as nombre from usuario as u 
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$id_usuario." and rol_tipo_id = ".$aRoles[0]['id']."
                ");
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll();
            $query = $em->getConnection()->prepare("
                    select v.id, v.nombre, nt.id as nivel_id, nt.nivel, dt.id as disciplina_id, dt.disciplina, coalesce(v.cantidad,0) as cantidad from (
                    select lt5.codigo as id, lt5.lugar as nombre, pt.disciplina_tipo_id, count(eij.id) as cantidad
                    from estudiante_inscripcion_juegos as eij
                    inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                    inner join (select * from institucioneducativa_curso where gestion_tipo_id = ".$gestionActual.") as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join lugar_tipo as lt1 on lt1.id = jg.lugar_tipo_id_localidad
                    inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                    inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                    inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                    inner join lugar_tipo as lt5 on lt5.id = lt4.lugar_tipo_id
                    inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id 
                    where lt5.codigo = '".$objEntidad[0]['id']."' and eij.gestion_tipo_id = ".$gestionActual." and eij.fase_tipo_id = ".$fase."
                    group by lt5.codigo, lt5.lugar, pt.disciplina_tipo_id
                    ) as v 
                    right join disciplina_tipo as dt on dt.id = v.disciplina_tipo_id
                    left join nivel_tipo as nt on nt.id = dt.nivel_tipo_id
                    order by nt.id, dt.disciplina
                ");
        }

        if($aRoles[0]['id'] == 8 or $aRoles[0]['id'] == 20) // Nacional
        {           
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.codigo as id, lt.lugar as nombre from usuario as u 
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$id_usuario." and rol_tipo_id = ".$aRoles[0]['id']."
                ");
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll();
            $query = $em->getConnection()->prepare("
                    select v.id, v.nombre, nt.id as nivel_id, nt.nivel, dt.id as disciplina_id, dt.disciplina, coalesce(v.cantidad,0) as cantidad from (
                    select 0 as id, 'Bolivia' as nombre, pt.disciplina_tipo_id, count(eij.id) as cantidad
                    from estudiante_inscripcion_juegos as eij
                    inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                    inner join (select * from institucioneducativa_curso where gestion_tipo_id = ".$gestionActual.") as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join lugar_tipo as lt1 on lt1.id = jg.lugar_tipo_id_localidad
                    inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                    inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                    inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                    inner join lugar_tipo as lt5 on lt5.id = lt4.lugar_tipo_id
                    inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id 
                    where eij.gestion_tipo_id = ".$gestionActual." and eij.fase_tipo_id = ".$fase."
                    group by pt.disciplina_tipo_id
                    ) as v 
                    right join disciplina_tipo as dt on dt.id = v.disciplina_tipo_id
                    left join nivel_tipo as nt on nt.id = dt.nivel_tipo_id
                    order by nt.id, dt.disciplina
                ");
        }        

        $query->execute();
        $entityDisciplinas = $query->fetchAll(); 
        return $entityDisciplinas;
    }    

    public function registroPorGeneroFase($fase) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $em = $this->getDoctrine()->getManager();
        $id_usuario = $this->session->get('userId');
        $aRoles = $this->session->get('roluser');

        if($aRoles[0]['id'] == 9) // Director
        {
            $ue = $this->session->get('userName');
            $queryEntidad = $em->getConnection()->prepare("
                    select id, institucioneducativa as nombre from institucioneducativa where id = ".$ue."
                ");
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll();

            $query = $em->getConnection()->prepare("
                    select v.id, v.nombre, nt.id as nivel_id, nt.nivel, gt.id as genero_id, gt.genero, coalesce(v.cantidad,0) as cantidad from (
                    select ie.id, ie.institucioneducativa as nombre, dt.nivel_tipo_id as nivel_id, pt.genero_tipo_id, count(eij.id) as cantidad
                    from estudiante_inscripcion_juegos as eij
                    inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                    inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                    inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
                    inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                    where ie.id = ".$objEntidad[0]['id']." and eij.gestion_tipo_id = ".$gestionActual." and eij.fase_tipo_id = ".$fase."
                    group by ie.id, ie.institucioneducativa, dt.nivel_tipo_id, pt.genero_tipo_id
                    ) as v 
                    right join (select * from genero_tipo where id not in (3)) as gt on gt.id = v.genero_tipo_id
                    left join nivel_tipo as nt on nt.id = v.nivel_id
                    order by nt.id, gt.genero
                ");
        }

        if($aRoles[0]['id'] == 10 or $aRoles[0]['id'] == 11) // Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.id, lt.lugar as nombre from usuario as u 
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$id_usuario." and rol_tipo_id = ".$aRoles[0]['id']."
                ");
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll();
            $query = $em->getConnection()->prepare("
                    select v.id, v.nombre, nt.id as nivel_id, nt.nivel, gt.id as genero_id, gt.genero, coalesce(v.cantidad,0) as cantidad from (
                    select lt.id, lt.lugar as nombre, dt.nivel_tipo_id as nivel_id, pt.genero_tipo_id, count(eij.id) as cantidad
                    from estudiante_inscripcion_juegos as eij
                    inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                    inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
                    inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id 
                    inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                    where lt.id = ".$objEntidad[0]['id']." and eij.gestion_tipo_id = ".$gestionActual." and eij.fase_tipo_id = ".$fase."
                    group by lt.id, lt.lugar, dt.nivel_tipo_id, pt.genero_tipo_id
                    ) as v 
                    right join (select * from genero_tipo where id not in (3)) as gt on gt.id = v.genero_tipo_id
                    left join nivel_tipo as nt on nt.id = v.nivel_id
                    order by nt.id, gt.genero
                ");
        }

        if($aRoles[0]['id'] == 6) // Circunscripcion
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select ur.circunscripcion_tipo_id as id, 'Circunscripción '||cast(ur.circunscripcion_tipo_id as varchar) as nombre from usuario as u 
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    where u.id = ".$id_usuario." and rol_tipo_id = ".$aRoles[0]['id']."
                ");
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll();

            $query = $em->getConnection()->prepare("
                    select v.id, 'Circunscripción '||cast(v.id as varchar) as nombre, nt.id as nivel_id, nt.nivel, gt.id as genero_id, gt.genero, coalesce(v.cantidad,0) as cantidad from (
                    select jg.circunscripcion_tipo_id as id, dt.nivel_tipo_id as nivel_id, pt.genero_tipo_id, count(eij.id) as cantidad
                    from estudiante_inscripcion_juegos as eij
                    inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                    inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id 
                    inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                    where jg.circunscripcion_tipo_id = ".$objEntidad[0]['id']." and eij.gestion_tipo_id = ".$gestionActual." and eij.fase_tipo_id = ".$fase."
                    group by jg.circunscripcion_tipo_id, dt.nivel_tipo_id, pt.genero_tipo_id
                    ) as v 
                    right join (select * from genero_tipo where id not in (3)) as gt on gt.id = v.genero_tipo_id
                    left join nivel_tipo as nt on nt.id = v.nivel_id
                    order by nt.id, gt.genero
                ");
        }

        if($aRoles[0]['id'] == 7) // Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.codigo as id, lt.lugar as nombre from usuario as u 
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$id_usuario." and rol_tipo_id = ".$aRoles[0]['id']."
                ");
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll();
            $query = $em->getConnection()->prepare("                    
                    select v.id, v.nombre, nt.id as nivel_id, nt.nivel, gt.id as genero_id, gt.genero, coalesce(v.cantidad,0) as cantidad from (
                    select lt5.codigo as id, lt5.lugar as nombre, dt.nivel_tipo_id as nivel_id, pt.genero_tipo_id, count(eij.id) as cantidad
                    from estudiante_inscripcion_juegos as eij
                    inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                    inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join lugar_tipo as lt1 on lt1.id = jg.lugar_tipo_id_localidad
                    inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                    inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                    inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                    inner join lugar_tipo as lt5 on lt5.id = lt4.lugar_tipo_id
                    inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id 
                    inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                    where lt5.codigo = '".$objEntidad[0]['id']."' and eij.gestion_tipo_id = ".$gestionActual." and eij.fase_tipo_id = ".$fase."
                    group by lt5.codigo, lt5.lugar, dt.nivel_tipo_id, pt.genero_tipo_id
                    ) as v 
                    right join (select * from genero_tipo where id not in (3)) as gt on gt.id = v.genero_tipo_id
                    left join nivel_tipo as nt on nt.id = v.nivel_id
                    order by nt.id, gt.genero
                ");
        }

        if($aRoles[0]['id'] == 8 or $aRoles[0]['id'] == 20) // Nacional
        {           
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.codigo as id, lt.lugar as nombre from usuario as u 
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$id_usuario." and rol_tipo_id = ".$aRoles[0]['id']."
                ");
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll();
            $query = $em->getConnection()->prepare("                    
                    select v.id, v.nombre, nt.id as nivel_id, nt.nivel, gt.id as genero_id, gt.genero, coalesce(v.cantidad,0) as cantidad from (
                    select 0 as id, 'Bolivia' as nombre, dt.nivel_tipo_id as nivel_id, pt.genero_tipo_id, count(eij.id) as cantidad
                    from estudiante_inscripcion_juegos as eij
                    inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                    inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                    inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join lugar_tipo as lt1 on lt1.id = jg.lugar_tipo_id_localidad
                    inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                    inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                    inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                    inner join lugar_tipo as lt5 on lt5.id = lt4.lugar_tipo_id
                    inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id 
                    inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                    where eij.gestion_tipo_id = ".$gestionActual." and eij.fase_tipo_id = ".$fase."
                    group by dt.nivel_tipo_id, pt.genero_tipo_id
                    ) as v 
                    right join (select * from genero_tipo where id not in (3)) as gt on gt.id = v.genero_tipo_id
                    left join nivel_tipo as nt on nt.id = v.nivel_id
                    order by nt.id, gt.genero
                ");
        }        

        $query->execute();
        $entityDisciplinas = $query->fetchAll(); 
        return $entityDisciplinas;
    }  
}