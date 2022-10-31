<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Vista controller.
 *
 */
class EstadisticaController extends Controller {
    private $route_anterior;
    private $var_anterior;
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    public function defaultAction() {
        $bimestre = 1;
        $entity = $this->consolidacionNivelNacional();
        $datos = $this->chartBarConsolidacion($entity,$bimestre);
        
        return $this->render('SieEspecialBundle:Estadistica:consolidacion.html.twig', array('periodo'=>$bimestre,'dato' => $datos, 'entity' => $entity, 'nivel' => 'departamentos', 'nivelnext' => 'distrital'));
    } 
    
    /**
     * Pagina Inicial - InformaciÃ³n General - Nacional - Educacion Especial
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {
    
    	/*
    	 * Define la zona horaria y halla la fecha actual
    	 */
    	date_default_timezone_set('America/La_Paz');
    	$fechaActual = new \DateTime(date('Y-m-d'));
    	//$gestionActual = date_format($fechaActual,'Y');
    	$gestionActual = $this->buscaGestionVistaMaterializadaEspecial();
//     	dump($gestionActual);die;
    	 
    	$defaultController = new DefaultCont();
    
    	$defaultController->setContainer($this->container);
    	$lugarNivel = 0;
    	$lugar=0;
    	 
    
    	$entidad = $this->buscaEntidadRol(0,0);
//     	$subEntidades = $this->buscaSubEntidadRol(0,0);
    	$subEntidades = $this->buscaSubEntidad(0,0,$gestionActual);

    	 
    	$entityEstadisticaEspecial = $this->estadisticaLugar($lugarNivel,$lugar,$gestionActual);
    	$entityEstadisticaEspecialIE = $this->estadisticaIELugar($lugarNivel,$lugar,$gestionActual);
    	$entityEstadisticaEspecialEE = $this->estadisticaEELugar($lugarNivel,$lugar,$gestionActual);
    	$chartAreaEspecial = $this->chartDonutInformacionGeneral($entityEstadisticaEspecial,"Estudiantes Matriculados por Area de Especial",$gestionActual,1,"chartContainerMatriculaAreaEspecial");    	 
    	$chartGeneroEspecial = $this->chartPieInformacionGeneral($entityEstadisticaEspecial,"Estudiantes Matriculados por Género",$gestionActual,2,"chartContainerMatriculaGeneroEspecial");
    	$chartDependenciaEspecial = $this->chartColumnInformacionGeneral($entityEstadisticaEspecial,"Estudiantes Matriculados por Dependencia",$gestionActual,3,"chartContainerMatriculaDependenciaEspecial");
    
    	$fechaEstadisticaEspecial = $this->buscaFechaVistaMaterializadaEspecial($gestionActual);
    	return $this->render('SieEspecialBundle:Estadistica:informacionGeneralEspecial.html.twig', array(
    			'infoEntidad'=>$entidad,
    			'infoSubEntidad'=>$subEntidades,
    			'infoEstadisticaEspecial'=>$entityEstadisticaEspecial,
    			'infoEstadisticaEspecialIE'=>$entityEstadisticaEspecialIE,
    			'infoEstadisticaEspecialEE'=>$entityEstadisticaEspecialEE,
    			'datoGraficoAreaEspecial'=>$chartAreaEspecial,
    			'datoGraficoGeneroEspecial'=>$chartGeneroEspecial,
    			'datoGraficoDependenciaEspecial'=>$chartDependenciaEspecial,
    			'infoLugarNivel'=>$lugarNivel,
    			'infoLugar'=>$lugar,
    			
    			'rol'=>0,
    			'mensaje'=>'$("#modal-bootstrap-tour").modal("show");',
    			'gestion'=>$gestionActual,
    			'fechaEstadisticaEspecial'=>$fechaEstadisticaEspecial,
    			'form' => $defaultController->createLoginForm()->createView()
    	));
    
    }
    
    public function estadisticaLugar($lugarNivel,$lugar,$gestion) {
    	/*
    	 * Define la zona horaria y halla la fecha actual
    	 */
    	date_default_timezone_set('America/La_Paz');
    	$fechaActual = new \DateTime(date('Y-m-d'));
    	//$gestionActual = date_format($fechaActual,'Y');
    	         $gestionActual = $this->buscaGestionVistaMaterializadaEspecial();
    
    	$em = $this->getDoctrine()->getManager();
    
    	$queryEntidad = $em->getConnection()->prepare("select * from get_estudiantes_estadistica_especial(".$lugarNivel.",".$lugar.",".$gestionActual.")");
    
    	$queryEntidad->execute();
    	$objEntidad = $queryEntidad->fetchAll();
    
    	if (count($objEntidad)>0){
    		return $objEntidad[0];
    	} else {
    		return '';
    	}
    
    }
    
    public function estadisticaIELugar($lugarNivel,$lugar,$gestion) {
    	/*
    	 * Define la zona horaria y halla la fecha actual
    	 */
    	date_default_timezone_set('America/La_Paz');
    	$fechaActual = new \DateTime(date('Y-m-d'));
    	//$gestionActual = date_format($fechaActual,'Y');
    	$gestionActual = $this->buscaGestionVistaMaterializadaEspecial();
    
    	$em = $this->getDoctrine()->getManager();
    
    	$queryEntidad = $em->getConnection()->prepare("
		SELECT
		 MAX(CASE WHEN ".$lugarNivel." = 0 THEN 'BOLIVIA'
		      WHEN ".$lugarNivel." = 1 THEN departamento
		      WHEN ".$lugarNivel." = 7 THEN distrito
		      WHEN ".$lugarNivel." = 8 THEN institucioneducativa
    			ELSE '' END) as nombre_entidad,
		 COUNT(*) as ie_total,
 		 SUM(CASE WHEN ie.dependencia = 'Fiscal o Estatal' THEN 1 ELSE 0 END) as ie_fiscal,
 		 SUM(CASE WHEN ie.dependencia = 'Convenio' THEN 1 ELSE 0 END) as ie_convenio,
 		 SUM(CASE WHEN ie.dependencia = 'Privada' THEN 1 ELSE 0 END) as ie_privada
 		 FROM (select distinct gestion_tipo_id,departamento,distrito,institucioneducativa,departamento_id,distrito_id,institucion_educativa_id,dependencia from vm_estudiantes_estadistica_especial ) ie
 		 WHERE CASE WHEN ".$lugarNivel." = 0 THEN true
 		            WHEN ".$lugarNivel." = 1 THEN ie.departamento_id = ".$lugar." 
 		            WHEN ".$lugarNivel." = 7 THEN ie.distrito_id = ".$lugar." 
    			    WHEN ".$lugarNivel." = 8 THEN ie.institucion_educativa_id = ".$lugar." ELSE false END 
 		            AND ie.gestion_tipo_id = ".$gestionActual." 
    			
    			");
    
    	$queryEntidad->execute();
    	$objEntidad = $queryEntidad->fetchAll();
    
    	if (count($objEntidad)>0){
    		return $objEntidad[0];
    	} else {
    		return '';
    	}
    
    }
    
    public function estadisticaEELugar($lugarNivel,$lugar,$gestion) {
    	/*
    	 * Define la zona horaria y halla la fecha actual
    	 */
    	date_default_timezone_set('America/La_Paz');
    	$fechaActual = new \DateTime(date('Y-m-d'));
    	//$gestionActual = date_format($fechaActual,'Y');
    	$gestionActual = $this->buscaGestionVistaMaterializadaEspecial();
    
    	$em = $this->getDoctrine()->getManager();
    
    	$queryEntidad = $em->getConnection()->prepare("
		SELECT
		 MAX(CASE WHEN ".$lugarNivel." = 0 THEN 'BOLIVIA'
		      WHEN ".$lugarNivel." = 1 THEN departamento
		      WHEN ".$lugarNivel." = 7 THEN distrito
		      ELSE '' END) as nombre_entidad,
		 COUNT(*) as ee_total
 		 FROM (select distinct gestion_tipo_id,departamento,distrito,departamento_id,distrito_id,le_juridicciongeografica_id from vm_estudiantes_estadistica_especial ) ie
 		 WHERE CASE WHEN ".$lugarNivel." = 0 THEN true
 		            WHEN ".$lugarNivel." = 1 THEN ie.departamento_id = ".$lugar."
	    			WHEN ".$lugarNivel." = 7 THEN ie.distrito_id = ".$lugar." ELSE false END
 		            AND ie.gestion_tipo_id = ".$gestionActual."
    
    			");
    
    	$queryEntidad->execute();
    	$objEntidad = $queryEntidad->fetchAll();
    
    	if (count($objEntidad)>0){
    		return $objEntidad[0];
    	} else {
    		return '';
    	}
    
    }
    
    
    
    public function estadisticaGeneralEspecialAction(Request $request) {
    	/*
    	 * Define la zona horaria y halla la fecha actual
    	 */
    	date_default_timezone_set('America/La_Paz');
    	$fechaActual = new \DateTime(date('Y-m-d'));
    	//$gestionActual = date_format($fechaActual,'Y');
    	$gestionActual = $this->buscaGestionVistaMaterializadaEspecial();
    	$idUsuario = $this->session->get('userId');
    
    	if ($request->isMethod('POST')) {
    		/*
    		 * Recupera datos del formulario
    		 */
    		$lugarNivel = $request->get('lugarNivel');
    		$lugar = $request->get('lugar'); 
    		$gestion = $request->get('gestion');
    		
    	} else {
    		$lugarNivel = 0;
    		$lugar = 0;
    		$gestion = $request->get('gestion');
    		
    	}
    
    	$defaultController = new DefaultCont();
    	$defaultController->setContainer($this->container);
		if ($lugarNivel != 8) {
			$entidad = $this->buscaEntidadRol(0,0);
				
		} else {
			$entidad = $this->buscaEntidadRol($lugar,0);
// 			    	dump($entidad);die;				
		}

    	 
    	$subEntidades = $this->buscaSubEntidad($lugarNivel,$lugar,$gestion);
// 		dump($lugarNivel);dump($lugar);dump($gestion);die;
    	$entityEstadisticaEspecial = $this->estadisticaLugar($lugarNivel,$lugar,$gestion);
    	$entityEstadisticaEspecialIE = $this->estadisticaIELugar($lugarNivel,$lugar,$gestion);
    	$entityEstadisticaEspecialEE = $this->estadisticaEELugar($lugarNivel,$lugar,$gestion);
    	$chartAreaEspecial = $this->chartDonutInformacionGeneral($entityEstadisticaEspecial,"Estudiantes Matriculados por Area de Especial",$gestion,1,"chartContainerMatriculaAreaEspecial");
    	$chartGeneroEspecial = $this->chartPieInformacionGeneral($entityEstadisticaEspecial,"Estudiantes Matriculados por Género",$gestion,2,"chartContainerMatriculaGeneroEspecial");
    	$chartDependenciaEspecial = $this->chartColumnInformacionGeneral($entityEstadisticaEspecial,"Estudiantes Matriculados por Dependencia",$gestion,3,"chartContainerMatriculaDependenciaEspecial");
    	 
    	$fechaEstadisticaEspecial = $this->buscaFechaVistaMaterializadaEspecial($gestionActual);
    	if ($entidad != '' and $subEntidades != ''){
    		return $this->render('SieEspecialBundle:Estadistica:informacionGeneralEspecial.html.twig', array(
    				'infoEntidad'=>$entidad,
    				'infoSubEntidad'=>$subEntidades,
    				'infoEstadisticaEspecial'=>$entityEstadisticaEspecial,
    				'infoEstadisticaEspecialIE'=>$entityEstadisticaEspecialIE,
    				'infoEstadisticaEspecialEE'=>$entityEstadisticaEspecialEE,
    				'datoGraficoAreaEspecial'=>$chartAreaEspecial,
    				'datoGraficoGeneroEspecial'=>$chartGeneroEspecial,
    				'datoGraficoDependenciaEspecial'=>$chartDependenciaEspecial,
    				'infoLugarNivel'=>$lugarNivel,
    				'infoLugar'=>$lugar,
    				
    				'gestion'=>$gestionActual,
    				'fechaEstadisticaEspecial'=>$fechaEstadisticaEspecial,
    				'form' => $defaultController->createLoginForm()->createView()
    		));
    		
    	}
    }
    

    
    public function buscaSubEntidad($lugarNivel,$lugar,$gestion) {
    	$em = $this->getDoctrine()->getManager();
    	


		if ($lugarNivel == 0) {
			$queryEntidad = $em->getConnection()->prepare("
                select 'Departamentos' as nombre_nivel,id  ,lugar as nombre,lugar_nivel_id from lugar_tipo where lugar_nivel_id = 1 and lugar<>'NINGUNO' order by codigo
            ");
		} elseif ($lugarNivel == 1) {
			$queryEntidad = $em->getConnection()->prepare("
                select distinct 'Distritos' as nombre_nivel,distrito_id as id  ,distrito as nombre,7 as lugar_nivel_id from vm_estudiantes_estadistica_especial where  departamento_id = ".$lugar."  order by distrito_id
            ");
				
		} elseif ($lugarNivel == 7) {
			$queryEntidad = $em->getConnection()->prepare("
			SELECT distinct  'Centros' as nombre_nivel ,institucion_educativa_id  as id,cast(institucion_educativa_id as varchar(8)) || '-' || institucioneducativa as nombre,8 as lugar_nivel_id
			from vm_estudiantes_estadistica_especial where gestion_tipo_id = ".$gestion." and distrito_id = ".$lugar." order by institucion_educativa_id  
            ");
		} else {
			$queryEntidad = $em->getConnection()->prepare("
                select 'Departamentos' as nombre_nivel, id ,lugar as nombre,lugar_nivel_id from lugar_tipo where lugar_nivel_id = 1 and lugar<>'NINGUNO' order by codigo
            ");
				
		}
		
		$queryEntidad->execute();
		$objEntidad = $queryEntidad->fetchAll();
		if (count($objEntidad)>0 ){
			return $objEntidad;
		} else {
			return '';
		}
		
    }
    
    

    
    /**
     * Funcion que retorna el Reporte Grafico Donut Chart - Higcharts
     * @param Request $entity
     * @return chart
     */
    public function chartDonutInformacionGeneral($entity,$titulo,$subTitulo,$tipoReporte,$contenedor) {
    	switch ($tipoReporte) {
    		case 1:
    			$datosTemp = "{name: 'Auditiva', y: ".round(((100*$entity['matricula_auditiva'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_auditiva']."}, {name: 'Visual', y: ".round(((100*$entity['matricula_visual'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_visual']."}, {name: 'Intelectual', y: ".round(((100*$entity['matricula_intelectual'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_intelectual']."}, {name: 'Fisico-Motora', y: ".round(((100*$entity['matricula_fisico_motora'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_fisico_motora']."}, {name: 'Multiple', y: ".round(((100*$entity['matricula_multiple'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_multiple']."}, {name: 'Dificultad en el aprendizaje', y: ".round(((100*$entity['matricula_dificultad'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_dificultad']."}, {name: 'Talento extraordinario', y: ".round(((100*$entity['matricula_talento'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_talento']."}, {name: 'Sordoceguera', y: ".round(((100*$entity['matricula_sordo_ceguera'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_sordo_ceguera']."}, {name: 'Problemas emocionales', y: ".round(((100*$entity['matricula_problemas_emocionales'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_problemas_emocionales']."}, {name: 'Modalidad indirecta', y: ".round(((100*$entity['matricula_indirecta'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_indirecta']."} ,";
    			break;
    		case 2:
    			$datosTemp = "{name: 'Masculino', y: ".round(((100*$entity['matricula_masculino'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_masculino']."}, {name: 'Femenino', y: ".round(((100*$entity['matricula_femenino'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_femenino']."},";
    			break;
    		case 3:
    			$datosTemp = "{name: 'PÃºblica', y: ".round(((100*$entity['matricula_fiscal'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_fiscal']."}, {name: 'Convenio', y: ".round(((100*$entity['matricula_convenio'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_convenio']."}, {name: 'Privada', y: ".round(((100*$entity['matricula_privada'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_privada']."} ,";
    			break;
    		default:
    			$datosTemp = "";
    			break;
    	}
    
    	$datos = "
            function ".$contenedor."Load() {
                 $('#chartContainer').highcharts({
                    chart: {
                        type: 'pie',
                        options3d: {
                            enabled: true,
                            alpha: 45
                        }
                    },
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    plotOptions: {
                        pie: {
                            innerSize: 100,
                            depth: 45,
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.percentage:.1f}% - {point.label}',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            }
                        }
                    },
                    series: [{
                        name: 'Estudiantes',
                        colorByPoint: true,
                        data: [".$datosTemp."]
                    }]
    
                });
            }
        ";
    	return $datos;
    }
    
    /**
     * Funcion que retorna el Reporte Grafico Pie Chart - Higcharts
     * @param Request $entity
     * @return chart
     */
    public function chartPieInformacionGeneral($entity,$titulo,$subTitulo,$tipoReporte,$contenedor) {
    	switch ($tipoReporte) {
    		case 1:
    			$datosTemp = "{name: 'Auditiva', y: ".round(((100*$entity['matricula_auditiva'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_auditiva']."}, {name: 'Visual', y: ".round(((100*$entity['matricula_visual'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_visual']."}, {name: 'Intelectual', y: ".round(((100*$entity['matricula_intelectual'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_intelectual']."}, {name: 'Fisico-Motora', y: ".round(((100*$entity['matricula_fisico_motora'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_fisico_motora']."}, {name: 'Multiple', y: ".round(((100*$entity['matricula_multiple'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_multiple']."}, {name: 'Dificultad en el aprendizaje', y: ".round(((100*$entity['matricula_dificultad'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_dificultad']."}, {name: 'Talento extraordinario', y: ".round(((100*$entity['matricula_talento'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_talento']."}, {name: 'Sordoceguera', y: ".round(((100*$entity['matricula_sordo_ceguera'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_sordo_ceguera']."}, {name: 'Problemas emocionales', y: ".round(((100*$entity['matricula_problemas_emocionales'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_problemas_emocionales']."}, {name: 'Modalidad indirecta', y: ".round(((100*$entity['matricula_indirecta'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_indirecta']."} ,";
    			break;
    		case 2:
    			$datosTemp = "{name: 'Masculino', y: ".round(((100*$entity['matricula_masculino'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_masculino']."}, {name: 'Femenino', y: ".round(((100*$entity['matricula_femenino'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_femenino']."},";
    			break;
    		case 3:
    			$datosTemp = "{name: 'PÃºblica', y: ".round(((100*$entity['matricula_fiscal'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_fiscal']."}, {name: 'Convenio', y: ".round(((100*$entity['matricula_convenio'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_convenio']."}, {name: 'Privada', y: ".round(((100*$entity['matricula_privada'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_privada']."} ,";
    			break;
    		default:
    			$datosTemp = "";
    			break;
    	}
    
    	$datos = "
            function ".$contenedor."Load() {
                 $('#chartContainer').highcharts({
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.percentage:.1f}% - {point.label}',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label} Estudiantes</b> del total<br/>'
                    },
                    series: [{
                        name: 'Estudiantes',
                        colorByPoint: true,
                        data: [".$datosTemp."]
                    }]
                });
            }
        ";
    	return $datos;
    }
    
    /**
     * Funcion que retorna el Reporte Grafico Bar Jquery de tipo Map
     * @param Request $entity
     * @return chart
     */
    public function chartColumnInformacionGeneral($entity,$titulo,$subTitulo,$tipoReporte,$contenedor) {
    	switch ($tipoReporte) {
    		case 1:
    			$datosTemp = "{name: 'Auditiva', y: ".round(((100*$entity['matricula_auditiva'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_auditiva']."}, {name: 'Visual', y: ".round(((100*$entity['matricula_visual'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_visual']."}, {name: 'Intelectual', y: ".round(((100*$entity['matricula_intelectual'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_intelectual']."}, {name: 'Fisico-Motora', y: ".round(((100*$entity['matricula_fisico_motora'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_fisico_motora']."}, {name: 'Multiple', y: ".round(((100*$entity['matricula_multiple'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_multiple']."}, {name: 'Dificultad en el aprendizaje', y: ".round(((100*$entity['matricula_dificultad'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_dificultad']."}, {name: 'Talento extraordinario', y: ".round(((100*$entity['matricula_talento'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_talento']."}, {name: 'Sordoceguera', y: ".round(((100*$entity['matricula_sordo_ceguera'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_sordo_ceguera']."}, {name: 'Problemas emocionales', y: ".round(((100*$entity['matricula_problemas_emocionales'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_problemas_emocionales']."}, {name: 'Modalidad indirecta', y: ".round(((100*$entity['matricula_indirecta'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_indirecta']."} ,";
    			break;
    		case 2:
    			$datosTemp = "{name: 'Masculino', y: ".round(((100*$entity['matricula_masculino'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_masculino']."}, {name: 'Femenino', y: ".round(((100*$entity['matricula_femenino'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_femenino']."},";
    			break;
    		case 3:
    			$datosTemp = "{name: 'PÃºblica', y: ".round(((100*$entity['matricula_fiscal'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_fiscal']."}, {name: 'Convenio', y: ".round(((100*$entity['matricula_convenio'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_convenio']."}, {name: 'Privada', y: ".round(((100*$entity['matricula_privada'])/(($entity['matricula_total']==0) ? 1:$entity['matricula_total'])),1).", label: ".$entity['matricula_privada']."} ,";
    			break;
    		default:
    			$datosTemp = "";
    			break;
    	}
    
    	$datos = "
            function ".$contenedor."Load() {
                 $('#chartContainer').highcharts({
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        title: {
                            text: 'Total porcentaje por matricula de estudiantes'
                        }
    
                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                format: '{point.y:.1f}%'
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label} Estudiantes</b> del total<br/>'
                    },
    
                    series: [{
                        name: 'Estudiantes',
                        colorByPoint: true,
                        data: [".$datosTemp."]
                    }]
                });
            }
        ";
    	return $datos;
    }
    
    /**
     * Busca el nombre de la entidad en funcion al tipo de rol
     * @param Request $request
     * @return type
     */
    public function buscaEntidadRol($codigo,$rol) {
    	/*
    	 * Define la zona horaria y halla la fecha actual
    	 */
    	date_default_timezone_set('America/La_Paz');
    	$fechaActual = new \DateTime(date('Y-m-d'));
    	//$gestionActual = date_format($fechaActual,'Y');
    	$gestionActual = $this->buscaGestionVistaMaterializadaEspecial();
    
    	$em = $this->getDoctrine()->getManager();

    	
    	   
    	
    	$queryEntidad = $em->getConnection()->prepare("
                select lt.codigo as id, lt.lugar as nombre, 0 as rolActual, -16.2256989 as cordx, -68.0441409 as cordy from lugar_tipo as lt
                where cast(lt.codigo as integer) = ".$codigo." and lugar_nivel_id = 0 and lugar_tipo_id = 0
            ");
    	
    	if ($codigo != 0) {
    		$queryEntidad = $em->getConnection()->prepare("
    	     select ie.id as id, 'U.E.: '|| cast(ie.id as varchar) ||' - '|| ie.institucioneducativa as nombre, ".$rol." as rolActual, coalesce(jg.cordx,-16.2256989) as cordx, coalesce(jg.cordy,-68.0441409) as cordy from institucioneducativa as ie
    	     left join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
    	     where ie.id = ".$codigo."
    	");
    		
    	}
    
    
    	if($rol == 9 or $rol == 5) // Director o Administrativo
    	{
    		$queryEntidad = $em->getConnection()->prepare("
                select ie.id as id, 'U.E.: '|| cast(ie.id as varchar) ||' - '|| ie.institucioneducativa as nombre, ".$rol." as rolActual, coalesce(jg.cordx,-16.2256989) as cordx, coalesce(jg.cordy,-68.0441409) as cordy from institucioneducativa as ie
                left join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                where ie.id = ".$codigo."
            ");
    		/*
    		 $queryEntidad = $em->getConnection()->prepare("
    		 select ie.id, ie.id||' - '||ie.institucioneducativa as nombre from usuario_rol as ur
    		 inner join usuario as u on u.id = ur.usuario_id
    		 inner join persona as p on p.id = u.persona_id
    		 inner join maestro_inscripcion as mi on mi.persona_id = p.id
    		 inner join institucioneducativa as ie on ie.id = mi.institucioneducativa_id
    		 where ur.usuario_id = ".$usuario." and ur.rol_tipo_id = ".$rol ." and mi.gestion_tipo_id = ".$gestionActual." and mi.es_vigente_administrativo = 1
    		 ");
    		 */
    	}
    
    	if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
    	{
    		$queryEntidad = $em->getConnection()->prepare("
                select lt.codigo as id, 'DISTRITO: '||lt.lugar as nombre, ".$rol." as rolActual, coalesce(jg.cordx,-16.2256989) as cordx, coalesce(jg.cordy,-68.0441409) as cordy from lugar_tipo as lt
                left join jurisdiccion_geografica as jg on jg.lugar_tipo_id_distrito = lt.id
                where cast(lt.codigo as integer) = ".$codigo." and lugar_nivel_id = 7 and lt.codigo not in ('1000','2000','3000','4000','5000','6000','7000','8000','9000')
            ");
    		/*
    		 $queryEntidad = $em->getConnection()->prepare("
    		 select lt.id, lt.lugar as nombre from usuario as u
    		 inner join usuario_rol as ur on ur.usuario_id = u.id
    		 inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
    		 where u.id = ".$usuario." and rol_tipo_id = ".$rol."
    		 ");
    		 */
    	}
    
    	if($rol == 7) // Tecnico Departamental
    	{
    		$queryEntidad = $em->getConnection()->prepare("
                select lt.codigo as id, 'Departamento: '||lt.lugar as nombre, ".$rol." as rolActual, -16.2256989 as cordx, -68.0441409 as cordy from lugar_tipo as lt
                where cast(lt.codigo as integer) = ".$codigo." and lugar_nivel_id = 1
            ");
    		/*
    		 $queryEntidad = $em->getConnection()->prepare("
    		 select lt.codigo as id, lt.lugar as nombre from usuario as u
    		 inner join usuario_rol as ur on ur.usuario_id = u.id
    		 inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
    		 where u.id = ".$usuario." and rol_tipo_id = ".$rol."
    		 ");
    		 */
    	}
    
    	if($rol == 8 or $rol == 20) // Tecnico Nacional
    	{
    		$queryEntidad = $em->getConnection()->prepare("
                select lt.codigo as id, lt.lugar as nombre, ".$rol." as rolActual, coalesce(jg.cordx,-16.2256989) as cordx, coalesce(jg.cordy,-68.0441409) as cordy from lugar_tipo as lt
                left join jurisdiccion_geografica as jg on jg.lugar_tipo_id_localidad = lt.id
                where cast(lt.codigo as integer) = ".$codigo." and lugar_nivel_id = 0 and lugar_tipo_id = 0
            ");
    		/*
    		 $queryEntidad = $em->getConnection()->prepare("
    		 select lt.codigo as id, lt.lugar as nombre from usuario as u
    		 inner join usuario_rol as ur on ur.usuario_id = u.id
    		 inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
    		 where u.id = ".$usuario." and rol_tipo_id = ".$rol ."
    		 ");
    		 */
    	}
    
    	$queryEntidad->execute();
    	$objEntidad = $queryEntidad->fetchAll();
    	if (count($objEntidad)>0){
    		return $objEntidad[0];
    	} else {
    		return '';
    	}
    }
    

    public function buscaFechaVistaMaterializadaEspecial() {
    
    	$em = $this->getDoctrine()->getManager();
    
    	$queryEntidad = $em->getConnection()->prepare("
            select  upper(departamento) as lugar_nombre, cast(date_part('day',fecha_vista) as integer) as dia, cast(date_part('month',fecha_vista) as integer) as mes
            , case date_part('dow',fecha_vista) when 1 then 'lunes' when 2 then 'martes' when 3 then 'miercoles' when 4 then 'jueves' when 5 then 'viernes' when 6 then 'sabado' when 7 then 'domingo' else '' end as dia_literal
            , case date_part('month',fecha_vista) when 1 then 'enero' when 2 then 'febrero' when 3 then 'marzo' when 4 then 'abril' when 5 then 'mayo' when 6 then 'junio' when 7 then 'julio' when 8 then 'agosto' when 9 then 'septiembre' when 10 then 'octubre' when 11 then 'noviembre' when 12 then 'diciembre' else '' end as mes_literal
            , cast(date_part('year',fecha_vista) as integer) as gestion
            from vm_estudiantes_estadistica_especial limit 1
        ");
    
    	$queryEntidad->execute();
    	$objEntidad = $queryEntidad->fetchAll();
    
    	if (count($objEntidad)>0){
    		return $objEntidad[0]['dia'].' de '.$objEntidad[0]['mes_literal'].' de '.$objEntidad[0]['gestion'];
    	} else {
    		return '';
    	}
    }
    
    
    public function buscaGestionVistaMaterializadaEspecial() {
    
    	$em = $this->getDoctrine()->getManager();
    
    	$queryEntidad = $em->getConnection()->prepare("
            select gestion_tipo_id
            from vm_estudiantes_estadistica_especial limit 1
        ");
    
    	$queryEntidad->execute();
    	$objEntidad = $queryEntidad->fetchAll();
    
    	if (count($objEntidad)>0){
    		return $objEntidad[0]['gestion_tipo_id'];
    	} else {
    		return '';
    	}
    }
    
    
    
    
    
    
    
    
    
    
    ////////////////////////////
    
    public function directorioInstitucionEducativaEspecialAction(Request $request) {
    	/*
    	 * Define la zona horaria y halla la fecha actual
    	 */
    	date_default_timezone_set('America/La_Paz');
    	$fechaActual = new \DateTime(date('Y-m-d'));
    	$gestionActual = date_format($fechaActual,'Y');
    
    	$defaultController = new DefaultCont();
    	$defaultController->setContainer($this->container);
    
    	$em = $this->getDoctrine()->getManager();
    	$entityDepartamento = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => 0));
    	$arrayDependencia = [1,2,5,3];
    
    	//dump($this->creaFormularioBusquedaUnidadEducativa('reporte_regular_directorio_institucioneducativa_busqueda',80551245,$entityDepartamento,array('1'=>false,'2'=>false,'5'=>false,'3'=>false))->createView());
//     	die();
    	return $this->render('SieEspecialBundle:Estadistica:directorioInstitucionEducativaEspecial.html.twig', array(
    			'infoEntidad'=>'',
    			'form' => $defaultController->createLoginForm()->createView(),
    			'formBusqueda' => $this->creaFormularioBusquedaUnidadEducativa('reporte_especial_directorio_institucioneducativa_busqueda','',$entityDepartamento, $arrayDependencia)->createView()
    	));
    }
    
    private function creaFormularioBusquedaUnidadEducativa($routing, $ue, $entityDepartamento, $arrayDependencia) {
    	$entityDependencia = ['1'=>'Fiscal o Estatal', '2'=>'Convenio', '5'=>'Comunitaria', '3'=>'Privada'];
    
    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl($routing))
    	->add('ue', 'text', array('label' => 'CÃ³digo SIE o Nombre de U.E.', 'required' => false, 'attr' => array('value' => $ue, 'class' => 'form-control no-border-left', 'placeholder' => 'CÃ³digo SIE o Nombre de Unidad Educativa', 'style' => 'text-transform:uppercase')))
    	->add('departamento', 'entity', array('label' => 'Departamento.', 'empty_value' => 'Todos', 'data' => $entityDepartamento, 'required' => false, 'attr' => array('class' => 'form-control mb-20'), 'class' => 'Sie\AppWebBundle\Entity\DepartamentoTipo',
    			'query_builder' => function(EntityRepository $er) {
    			return $er->createQueryBuilder('dt')
    			->orderBy('dt.id', 'ASC')
    			->where('dt.id != :codDepartamento')
    			->setParameter('codDepartamento', 0);
    			},
    			))
    	->add('dependencia', 'choice', ['choices' => $entityDependencia, 'data' => $arrayDependencia, 'multiple' => true, 'expanded' => true])
    	->add('submit', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-success')))
    	->getForm();
    	return $form;
    }
    
    
    public function directorioInstitucionEducativaBusquedaEspecialAction(Request $request) {
    	/*
    	 * Define la zona horaria y halla la fecha actual
    	 */
    	date_default_timezone_set('America/La_Paz');
    	$fechaActual = new \DateTime(date('Y-m-d'));
    	$gestionActual = date_format($fechaActual,'Y');
    
    	$defaultController = new DefaultCont();
    	$defaultController->setContainer($this->container);
    
    
    	$request = $this->getRequest();
    	$this->route_anterior = $request->get('_route');
    	$this->var_anterior = $request->query->all();
    
    
    	if ($request->isMethod('POST')) {
    		/*
    		 * Recupera datos del formulario
    		 */
    		$form = $request->get('form');
    
    		if ($form) {
    			$codigoDepartamento = $form['departamento'];
    			if ($codigoDepartamento == "") {
    				$codigoDepartamento = 0;
    			}
    			$textUnidadEducativa = $form['ue'];
    			$dependencia = isset($form['dependencia']) ? $form['dependencia'] : array();
    			$dependenciaList = "";
    			for($i = 0; $i < count($dependencia); $i++) {
    				if($dependenciaList==""){
    					$dependenciaList = $dependencia[$i];
    				} else {
    					$dependenciaList = $dependenciaList.",".$dependencia[$i];
    				}
    			}
    			if($dependenciaList==""){
    				$dependenciaList = "1,2,5,3";
    			}
    
    			$em = $this->getDoctrine()->getManager();
    			$query = $em->getConnection()->prepare("
                    select ie.id as codigo, ie.institucioneducativa as institucioneducativa, det.dependencia, eit.id as estadoinstitucion_id, eit.estadoinstitucion, dep.lugar as departamento, dis.lugar as distrito, jg.direccion as direccion
                    , (case oct.id when 2 then (case iena.nivel_tipo_id when 6 then 'Especial' else oct.orgcurricula end) else oct.orgcurricula end) as orgcurricular, loc.lugar as localidad, can.lugar as canton, sec.lugar as seccion
                    , pro.lugar as provincia, jg.zona
                    from institucioneducativa as ie
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id,area2001 AS area FROM lugar_tipo WHERE lugar_nivel_id = 5) loc ON loc.id = jg.lugar_tipo_id_localidad
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 4) can ON can.id = loc.lugar_tipo_id
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 3) sec ON sec.id = can.lugar_tipo_id
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 2) pro ON pro.id = sec.lugar_tipo_id
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 1) dep ON dep.id = pro.lugar_tipo_id
                    inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito
                    inner join dependencia_tipo as det on det.id = ie.dependencia_tipo_id
                    inner join estadoinstitucion_tipo as eit on eit.id = ie.estadoinstitucion_tipo_id
                    inner join orgcurricular_tipo as oct ON oct.id = ie.orgcurricular_tipo_id
                    left join (select distinct institucioneducativa_id, nivel_tipo_id from institucioneducativa_nivel_autorizado where nivel_tipo_id = 6) as iena on iena.institucioneducativa_id = ie.id
                    where ie.institucioneducativa_acreditacion_tipo_id = 1
                    and (cast(ie.id as varchar) = replace('".$textUnidadEducativa."',' ','%') or ie.institucioneducativa like '%'||replace(UPPER('".$textUnidadEducativa."'),' ','%')||'%')
                    and (case ".$codigoDepartamento." when 0 then dep.codigo != '0' else dep.codigo = '".$codigoDepartamento."' end)
                    and det.id in (".$dependenciaList.")
                ");
    			$query->execute();
    			$entityInstitucionEducativa = $query->fetchAll();
    
    			$infoBusqueda = serialize(array(
    					'unidadeducativa' => $textUnidadEducativa,
    					'departamento' => $codigoDepartamento,
    					'dependencia' => $dependencia
    			));
    		}  else {
    			$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Formulario enviado de forma incorrecta, intente nuevamente'));
    			return $this->redirectToRoute('reporte_especial_directorio_institucioneducativa');
    		}
    	} else {
    		$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al enviar informaciÃ³n, intente nuevamente'));
    		return $this->redirectToRoute('reporte_especial_directorio_institucioneducativa');
    	}
    
    	$em = $this->getDoctrine()->getManager();
    	$entityDepartamento = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => $codigoDepartamento ));
    
    
    	return $this->render('SieEspecialBundle:Estadistica:directorioInstitucionEducativaEspecial.html.twig', array(
    			'infoEntidad' => '',
    			'infoBusqueda' => $infoBusqueda,
    			'entityBusqueda'=> $entityInstitucionEducativa,
    			'form' => $defaultController->createLoginForm()->createView(),
    			'dependencia' => $dependencia,
    			'formBusqueda' => $this->creaFormularioBusquedaUnidadEducativa('reporte_especial_directorio_institucioneducativa_busqueda',$textUnidadEducativa, $entityDepartamento, $dependencia)->createView()
    	));
    }

    
    public function directorioInstitucionEducativaDetalleEspecialAction(Request $request) {
    	/*
    	 * Define la zona horaria y halla la fecha actual
    	 */
    
    	date_default_timezone_set('America/La_Paz');
    	$fechaActual = new \DateTime(date('Y-m-d'));
    	$gestionActual = date_format($fechaActual,'Y');
    
    	$defaultController = new DefaultCont();
    	$defaultController->setContainer($this->container);
    
    	$em = $this->getDoctrine()->getManager();
    	 
    	//return $this->redirect($this->generateUrl($this->route_anterior,$this->var_anterior));
    
    	if ($request->isMethod('POST')) {
    		/*
    		 * Recupera datos del formulario
    		 */
    		$infoBusqueda = $request->get('infoBusqueda');
    		$sie = $request->get('sie');
    		$ainfoBusqueda = unserialize($infoBusqueda);
    		//get the values throght the infoUe
    		$unidadEducativaText = $ainfoBusqueda['unidadeducativa'];
    		$departamentoId = $ainfoBusqueda['departamento'];
    		$dependenciaArrayId = $ainfoBusqueda['dependencia'];
    		$dependenciaList = "";
    		for($i = 0; $i < count($dependenciaArrayId); $i++) {
    			if($dependenciaList==""){
    				$dependenciaList = $dependenciaArrayId[$i];
    			} else {
    				$dependenciaList = $dependenciaList.",".$dependenciaArrayId[$i];
    			}
    		}
    
    		$entityDepartamento = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => $departamentoId ));
    
    		$query = $em->getConnection()->prepare("
                select ie.id as codigo, ie.institucioneducativa as institucioneducativa, det.dependencia, eit.id as estadoinstitucion_id, eit.estadoinstitucion, dep.lugar as departamento, dis.lugar as distrito, jg.direccion as direccion
                , 'EducaciÃ³n '||(case oct.id when 2 then (case iena.nivel_tipo_id when 6 then 'Especial' else oct.orgcurricula end) else oct.orgcurricula end) as orgcurricular, loc.lugar as localidad, can.lugar as canton, sec.lugar as seccion
                , pro.lugar as provincia, jg.zona, c.director, jg.cordx, jg.cordy, loc.area
                from institucioneducativa as ie
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id,area2001 AS area FROM lugar_tipo WHERE lugar_nivel_id = 5) loc ON loc.id = jg.lugar_tipo_id_localidad
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 4) can ON can.id = loc.lugar_tipo_id
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 3) sec ON sec.id = can.lugar_tipo_id
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 2) pro ON pro.id = sec.lugar_tipo_id
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 1) dep ON dep.id = pro.lugar_tipo_id
                inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito
                inner join dependencia_tipo as det on det.id = ie.dependencia_tipo_id
                inner join estadoinstitucion_tipo as eit on eit.id = ie.estadoinstitucion_tipo_id
                inner join orgcurricular_tipo as oct ON oct.id = ie.orgcurricular_tipo_id
                left join (select distinct institucioneducativa_id, nivel_tipo_id from institucioneducativa_nivel_autorizado where nivel_tipo_id = 6) as iena on iena.institucioneducativa_id = ie.id
                left join (select institucioneducativa_id, carnet as ci_director,paterno||' '||materno||' '||nombre as director,cargo_tipo_id as item_director from maestro_inscripcion a
                    inner join persona b on a.persona_id=b.id
                    where a.gestion_tipo_id in (select max(gestion_tipo_id) as gestion_tipo_id from maestro_inscripcion where institucioneducativa_id= ".$sie." and cargo_tipo_id in (1,12) and es_vigente_administrativo = 'true') and a.institucioneducativa_id= ".$sie." and cargo_tipo_id in (1,12) and a.es_vigente_administrativo = 'true'
                ) c on ie.id = c.institucioneducativa_id
                where ie.institucioneducativa_acreditacion_tipo_id = 1 and ie.id = ".$sie."
                and (case ".$departamentoId." when 0 then dep.codigo != '0' else dep.codigo = '".$departamentoId."' end)
                and det.id in (".$dependenciaList.")
            ");
    
    		$query->execute();
    		$entityInstitucionEducativa = $query->fetchAll();
    	} else {
    		$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al enviar informaciÃ³n, intente nuevamente'));
    	}
    
    	return $this->render('SieEspecialBundle:Estadistica:directorioInstitucionEducativaDetalleEspecial.html.twig', array(
    			'infoEntidad'=>'',
    			'entityUnidadEducativa'=> $entityInstitucionEducativa,
    			'form' => $defaultController->createLoginForm()->createView(),
    			'formBusqueda' => $this->creaFormularioBusquedaUnidadEducativa('reporte_especial_directorio_institucioneducativa_busqueda',$unidadEducativaText,$entityDepartamento, $dependenciaArrayId)->createView()
    	));
    }
    
    
    
    
    
}

