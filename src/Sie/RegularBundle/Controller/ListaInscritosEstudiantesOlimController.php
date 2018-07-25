<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\Estudiante;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListaInscritosEstudiantesOlimController extends Controller{
	public function __construct() {
      $this->session = new Session();
  }

    public function indexAction(){

    $em = $this->getDoctrine()->getManager();
      //check if the user is logged
      $id_usuario = $this->session->get('userId');
      if (!isset($id_usuario)) {
          return $this->redirect($this->generateUrl('login'));
      }
      $coddis = '';
       if($this->session->get('roluser') == 10){
            //call find sie template
            $modeview = 0;
            $query = $em->getConnection()->prepare('select * from get_usuario_lugar(:userid)');
            $query->bindValue(':userid', $this->session->get('userId'));
            $query->execute();
            $dataDistrito = $query->fetchAll();
            // dump($dataDistrito[0]['get_usuario_lugar']);die;
            $arrDataDistrito = explode('|', $dataDistrito[0]['get_usuario_lugar']);
            // dump($arrDataDistrito);die;
            $coddis = $arrDataDistrito[0];
        }else{
            $modeview = 1;
        }
        
        return $this->render('SieRegularBundle:ListaInscritosEstudiantesOlim:index.html.twig', array(
                'form' => $this->createSearchForm($coddis)->createView(),
                'modeview' => $modeview
            ));    
    }

    /**
     * Creates a form to search the users of student selected
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm($coddis) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('listainscritosestudiantesolim_lookdistritoinfo'))
                ->add('codigoDistrito', 'text', array('label' => 'Código Distrito', 'invalid_message' => 'campo obligatorio', 'data'=> $coddis, 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 8, 'required' => true, 'class' => 'form-control')))
                ->add('gestion', 'hidden', array('label' => 'Gestion', 'invalid_message' => 'campo obligatorio','data'=> $this->session->get('currentyear'), 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 4, 'required' => true, 'class' => 'form-control')))
                // ->add('buscar', 'button', array('label' => 'Generar', 'attr'=> array('onclick'=>'lookdistrito()')))
                ->add('buscar', 'submit', array('label' => 'Generar', 'attr'=> array('onclick'=>'')))
                ->getForm();
        return $form;
    }

    public function lookdistritoinfoAction(Request $request){

    	//get the send values
    	$form = $request->get('form');
      $codigoDistrito = $form['codigoDistrito'];

            $conn = $this->get('database_connection');
            $response = new StreamedResponse(function() use($conn, $form) {
            $handle = fopen('php://output', 'w+');
            // Add the header
            fputcsv($handle, ['INSTITUCIONEDUCATIVA_ID', 'INSTITUCIONEDUCATIVA', 'PATERNO','MATERNO','NOMBRE','CEDULA','COMPLEMENTO','DISCAPACIDAD','MATERIA/ÁREA','CATEGORIA','GRADO_TIPO_ID','NIVEL_ABR','NOMBRE_PROYECTO'],';');

        
            // Query data from database
              $results = $conn->query( "select ie.id as institucioneducativa_id, ie.institucioneducativa
              -- , oei.id olimestudianteid, oei.telefono_estudiante, oei.correo_estudiante, e.codigo_rude
              -- , cast(e.carnet_identidad as varchar)||(case when e.complemento is null then '' when e.complemento = '' then '' else '-'|| e.complemento end) as carnet_identidad
              , e.paterno, e.materno, e.nombre, e.carnet_identidad as cedula, e.complemento, upper(odt.discapacidad) as discapacidad
              , upper(omt.materia) as materia, upper(coalesce(orot.categoria,'')) as categoria
              -- , p.carnet carnet1, p.paterno paterno1, p.materno materno1, p.nombre nombre1, 'Grado regular' obs
              , iec.grado_tipo_id, case iec.nivel_tipo_id when 12 then 'PRI.' when 13 then 'SEC.' else '' end as nivel_abr, ogp.nombre as nombre_proyecto
              -- , case e.genero_tipo_id when 1 then 'M' when 2 then 'F' else '' end as genero
              -- , nt.nivel
              from olim_estudiante_inscripcion oei
              inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
              inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id
              inner join olim_materia_tipo as omt on omt.id = oei.materia_tipo_id
              inner join olim_reglas_olimpiadas_tipo as orot on orot.id = oei.olim_reglas_olimpiadas_tipo_id
              inner join estudiante e on e.id = ei.estudiante_id
              inner join olim_discapacidad_tipo odt on odt.id = oei.discapacidad_tipo_id
              inner join olim_tutor ot on ot.id = oei.olim_tutor_id
              inner join persona p on p.id = ot.persona_id
              inner join nivel_tipo as nt on nt.id = iec.nivel_tipo_id
              inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
              inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
              -- left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
              -- left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
              -- left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
              -- left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
              -- left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
              left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
              left join olim_inscripcion_grupo_proyecto as pigp on pigp.olim_estudiante_inscripcion_id = oei.id
              left join olim_grupo_proyecto as ogp on ogp.id = pigp.olim_grupo_proyecto_id
              left join olim_estudiante_inscripcion_curso_superior as oeics on oeics.olim_estudiante_inscripcion_id = oei.id
              where lt5.codigo = '".$form['codigoDistrito']."' and iec.gestion_tipo_id = ".$form['gestion'].":: double precision and oeics.id is null
              union all           
              select ie.id as institucioneducativa_id, ie.institucioneducativa
              -- , oei.id olimestudianteid, oei.telefono_estudiante, oei.correo_estudiante, e.codigo_rude
              , e.paterno, e.materno, e.nombre, e.carnet_identidad as cedula, e.complemento, upper(odt.discapacidad) as discapacidad
              -- , cast(e.carnet_identidad as varchar)||(case when e.complemento is null then '' when e.complemento = '' then '' else '-'|| e.complemento end) as carnet_identidad    
              , upper(omt.materia) as materia, upper(coalesce(orot.categoria,'')) as categoria          
              -- , p.carnet carnet1, p.paterno paterno1, p.materno materno1, p.nombre nombre1, 'Grado superior' obs
              , oeis.grado_tipo_id, case oeis.nivel_tipo_id when 12 then 'PRI.' when 13 then 'SEC.' else '' end as nivel_abr, ogp.nombre as nombre_proyecto
              -- , case e.genero_tipo_id when 1 then 'M' when 2 then 'F' else '' end as genero
              -- , nt.nivel            
              from olim_estudiante_inscripcion oei
              inner join estudiante_inscripcion ei on ei.id = oei.estudiante_inscripcion_id
              inner join olim_estudiante_inscripcion_curso_superior oeis on oei.id = oeis.olim_estudiante_inscripcion_id
              inner join institucioneducativa_curso iec on iec.id = ei.institucioneducativa_curso_id  
              inner join olim_materia_tipo as omt on omt.id = oei.materia_tipo_id   
              inner join olim_reglas_olimpiadas_tipo as orot on orot.id = oei.olim_reglas_olimpiadas_tipo_id    
              inner join estudiante e on e.id = ei.estudiante_id
              inner join olim_discapacidad_tipo odt on odt.id = oei.discapacidad_tipo_id
              inner join olim_tutor ot on ot.id = oei.olim_tutor_id
              inner join persona p on p.id = ot.persona_id
              inner join nivel_tipo as nt on nt.id = oeis.nivel_tipo_id
              inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
              inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
              -- left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
              -- left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
              -- left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
              -- left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
              -- left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
              left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
              left join olim_inscripcion_grupo_proyecto as pigp on pigp.olim_estudiante_inscripcion_id = oei.id
              left join olim_grupo_proyecto as ogp on ogp.id = pigp.olim_grupo_proyecto_id
              where lt5.codigo = '".$form['codigoDistrito']."' and iec.gestion_tipo_id = ".$form['gestion'].":: double precision
              order by institucioneducativa_id, institucioneducativa, materia, categoria, nivel_abr, nombre_proyecto, grado_tipo_id, paterno, materno, nombre");
            // $results = $conn->query("select id, etapa from olim_etapa_tipo");
            while($row = $results->fetch()) {
                fputcsv($handle, array($row['institucioneducativa_id'], $row['institucioneducativa'], $row['paterno'],$row['materno'],$row['nombre'],$row['cedula'],$row['complemento'],$row['discapacidad'],$row['materia'],$row['categoria'],$row['grado_tipo_id'],$row['nivel_abr'],$row['nombre_proyecto']), ';');
            }
            fclose($handle);

        });
        
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$form['codigoDistrito'].'_'.$form['gestion'].'_'. date("d-m H:i:s").'.csv');
        return $response;

/*    	 return $this->render('SieRegularBundle:ListaInscritosEstudiantesOlim:lookdistrito.html.twig', array(
               'data' => $form,
            ));    */
    }

}
