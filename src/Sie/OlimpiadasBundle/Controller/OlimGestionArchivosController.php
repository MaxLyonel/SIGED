<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * OlimArchivo controller.
 *
 */
class OlimGestionArchivosController extends Controller {

	public function indexAction(Request $request){

		// $archivos = null;
		// $path = $this->get('kernel')->getRootDir() . '/../web/uploads/olimpiadas/documentos/';

		$id = $request->get('idMateria');
		if (!$id) {
			$id = null;
		}

		$em = $this->getDoctrine()->getManager();

		$materia = $em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($id);
		if($materia){
			$materia = $materia->getMateria();
		}else{
			$materia = '';
		}

		$gestion = $request->get('gestion');
		if (!$gestion) {
			$gestion = 1990;
		}


		$grupos = $em->createQueryBuilder()
					->select('ie.id as sie, ie.institucioneducativa, ogp.nombre as grupo, ogp.nombreProyecto, ogp.documentoPdf1, p.paterno, p.materno, p.nombre, ot.telefono1, ot.telefono2, ot.correoElectronico, count(oigp.id) as inscritos, orot.id as olimRegla, ogp.fechaRegistro')
					->from('SieAppWebBundle:OlimGrupoProyecto','ogp')
					->innerJoin('SieAppWebBundle:OlimMateriaTipo','omt','with','ogp.materiaTipo = omt.id')
					->innerJoin('SieAppWebBundle:OlimTutor','ot','with','ogp.olimTutor = ot.id')
					->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','ot.institucioneducativa = ie.id')
					->innerJoin('SieAppWebBundle:Persona','p','with','ot.persona = p.id')
					->innerJoin('SieAppWebBundle:OlimReglasOlimpiadasTipo','orot','with','ogp.olimReglasOlimpiadasTipo = orot.id')
					->leftJoin('SieAppWebBundle:OlimInscripcionGrupoProyecto','oigp','with','oigp.olimGrupoProyecto = ogp.id')
					->where('ogp.gestionTipoId = :gestion')
					->andWhere('omt.id = :id')
					->setParameter('id', $id)
					->setParameter('gestion', $gestion)
					->groupBy('ie.id, ie.institucioneducativa, ogp.nombre, ogp.nombreProyecto, ogp.documentoPdf1, p.paterno, p.materno, p.nombre, ot.telefono1, ot.telefono2, ot.correoElectronico, orot.id, ogp.fechaRegistro')
					->orderBy('ogp.fechaRegistro','ASC')
					->getQuery()
					->getResult();
		// 427

		$array = array();
		foreach ($grupos as $g) {
			$olimNivelGrado = $em->createQueryBuilder()
							->select('orongt')
							->from('SieAppWebBundle:OlimReglasOlimpiadasNivelGradoTipo','orongt')
							->where('orongt.olimReglasOlimpiadasTipo = :idGrupo')
							->setParameter('idGrupo', $g['olimRegla'])
							->getQuery()
							->getResult();

			$array[] = array(
				'grupo'=> $g,
				'niveles'=>$olimNivelGrado
			);
		}
		
		return $this->render('SieOlimpiadasBundle:OlimGestionArchivos:index.html.twig', array(
			'grupos'=>$array,
			'materia'=>$materia,
			'gestion'=>$gestion
		));
	}

}