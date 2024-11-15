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
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

class StudentScoreController extends Controller{

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
        $aInfoUe = unserialize($infoUe);

        $infoStudent = $request->get('infoStudent');
        $aInfoStudent = json_decode($infoStudent,true);

        $idInscripcion = $aInfoStudent['eInsId'];
        //dump($aInfoUe['requestUser']['gestion']);die;
        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
        $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();

        $dataCurso = array(
        	'gestion'=>$gestion,
        	'nivel'=>$nivel,
        	'grado'=>$grado,
        );

        
        /*
        $tipoNota = $this->get('notas')->getTipoNota($sie,$gestion,$nivel,$grado);
        */
        $tipoNota = $this->get('notas')->getTipoNota($sie,$gestion,$nivel,$grado,'');
        $objDatalibreta = $this->getInfoLibreta($dataCurso);

        $tipoUE = $this->get('funciones')->getTipoUE($aInfoUe['requestUser']['sie'],$aInfoUe['requestUser']['gestion']);
        $operativo = $this->get('funciones')->obtenerOperativoTrimestre2020($sie,$gestion);
        
        if ($nivel==13 and $grado==6 and $operativo==3){
            $operativosexto = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findBy(array(
                'institucioneducativa' => $sie,
                'gestionTipoId'  => $gestion,
                'institucioneducativaOperativoLogTipo' => 10
            ));
            if (count($operativosexto)>0){
                return $this->redirect($this->generateUrl('principal_web'));
            }
            
        }

        $notas = $this->get('notas')->regularDB($idInscripcion,$operativo);

        if($tipoUE){
            // PAra ues modulares secundaria
            if($tipoUE['id'] == 3 and $notas['nivel'] == 13){
                $notas = $this->get('notas')->regularDB($idInscripcion,4);
                $plantilla = 'newmodular';
                $vista = 1;
            }else{
                // Verificamos si el tipo es 1:plena, 2:tecnica tecnologica, 3:modular, 5: humanistica 7:transformacion (las que hayan hecho una solicitud pàra trabajar gestion actual)
                if($tipoUE['id'] == 1 or $tipoUE['id'] == 2 or $tipoUE['id'] == 3 or $tipoUE['id'] == 5 or $tipoUE['id'] == 7 or $tipoUE['id'] == 8 or $tipoUE['id'] == 11){
                    $plantilla = 'regularDB';
                    $vista = 1;
                }else{
                    // Regularizacion de gestiones pasadas 
                    if($notas['gestion'] < $notas['gestionActual']){
                        $plantilla = 'regularDB';
                        $vista = 1;
                    }else{
                        $plantilla = 'regularDB';
                        $vista = 0;
                    }
                }
            }
        }else{
            $plantilla = 'regularDB';
            $vista = 0;
        }
        $swspeciality  = false;
        $objLevelModular    = false;
        foreach ($notas['cuantitativas'] as $key => $value) {
          if($value['idAsignatura']==1039){
            $swspeciality    = true;
            $objLevelModular = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array(
              'institucioneducativaId'=>$sie,
              'gestionTipoId'=>$gestion
            ));
          }

        }
        if (!($nivel==13 and $grado==6 and $operativo==3)){$vista = 0;} //habilitar solo cuando sea operativo 6to secundaria
        
        return $this->render('SieHerramientaBundle:StudentScore:newtrimestre.html.twig',array(
            'notas'=>$notas,
            'swspeciality'=>$swspeciality,
            'grado'=>$grado,
            'objLevelModular'=>$objLevelModular,
            'inscripcion'=>$inscripcion,
            'vista'=>$vista,
            'plantilla'=>$plantilla,
            'infoUe'=>$infoUe,
            'infoStudent'=>$infoStudent
        ));   


    /*
    $data = $this->getNotas($infoUe,$infoStudent);
    return $this->render('SieHerramientaBundle:InfoEstudianteNotas:notasTrimestre.html.twig',$data);*/
         	


    }

    public function updateStatusAction(Request $request){
    	//dump($request);die;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $idInscripcion = $request->get('idInscripcion');

        $currentinscription = $em->createQueryBuilder()
                            ->select('estins')
                            ->from('SieAppWebBundle:EstudianteInscripcion','estins')
                            ->where('estins.id = :idInscripcion')
                            ->setParameter('idInscripcion', $idInscripcion)
                            ->getQuery()
                            ->getResult();
        $statusStudent = '';
        if(sizeof($currentinscription)>0){
            $statusStudent = $currentinscription[0]->getEstadomatriculaTipo()->getEstadomatricula();       
        }

        $response->setStatusCode(200);
        $response->setData(array(
            'idInscripcion'=> $idInscripcion,
            'statusStudent'=> $statusStudent,
        ));

        return $response;        
    }   

    public function createUpdateAction(Request $request){
        //dump($request);die;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $this->get('notas')->regularRegistroDB($request);
        $idInscripcion = $request->get('idInscripcion');

        //$this->get('notas')->actualizarEstadoMatriculaDB($idInscripcion);
        // $this->get('notas')->actualizarEstadoMatriculaIGP($idInscripcion);
        $this->get('notas')->updateStatusStudent($idInscripcion);

        $currentinscription = $em->createQueryBuilder()
                            ->select('estins')
                            ->from('SieAppWebBundle:EstudianteInscripcion','estins')
                            ->where('estins.id = :idInscripcion')
                            ->setParameter('idInscripcion', $idInscripcion)
                            ->getQuery()
                            ->getResult();
        $statusStudent = '';
        if(sizeof($currentinscription)>0){
            $statusStudent = $currentinscription[0]->getEstadomatriculaTipo()->getEstadomatricula();       
        }

        //die;
        // return 1;
        $response->setStatusCode(200);
        $response->setData(array(
            'idInscripcion'=> $idInscripcion,
            'statusStudent'=> $statusStudent,
        ));

        return $response;        
    }    

    public function getInfoLibreta($dataCurso){
        
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        //get the vars
        $gestion = $dataCurso['gestion'];
        $nivelId = $dataCurso['nivel'];
        $gradoId = $dataCurso['grado'];

        // get the asignaturas
        $qAsignaturas = $em->createQueryBuilder()
                            ->select('asig')
                            ->from('SieAppWebBundle:TmpAsignaturaHistorico','asig')
                            ->where('asig.gestionTipoId = :gestion')
                            ->andWhere('asig.nivelTipoId = :nivelId')
                            ->andWhere('asig.gradoTipoId = :gradoId')
                            ->setParameter('gestion', $gestion)
                            ->setParameter('nivelId', $nivelId)
                            ->setParameter('gradoId', $gradoId)
                            // ->setParameter('sie', $sie)
                            ->addOrderBy('asig.asignaturaTipo','ASC')
                            ->getQuery()
                            ->getResult();
        // create the DB array like container
        $DBAsignatura = array();
        foreach ($qAsignaturas as $value) {
            $DBAsignatura[] = array(
                                'asignatura' => $value->getAsignatura(),
                                'asignaturaTipoId' => $value->getAsignaturaTipo()->getId()
            );
        }
        
        // get the CatalogoLibreta 
        $qCatalogoLibreta = $em->createQueryBuilder()
                            ->select('cata')
                            ->from('SieAppWebBundle:CatalogoLibretaTipo','cata')
                            ->where('cata.gestionTipoId = :gestion')
                            ->andWhere('cata.nivelTipoId = :nivelId')
                            ->andWhere('cata.gradoTipoId = :gradoId')
                            ->setParameter('gestion', $gestion)
                            ->setParameter('nivelId', $nivelId)
                            ->setParameter('gradoId', $gradoId)
                            // ->setParameter('sie', $sie)
                            ->addOrderBy('cata.orden','ASC')
                            ->getQuery()
                            ->getResult();
        // create the DB array like container
        $DBCatalogLibreta = array();
        

        foreach ($qCatalogoLibreta as $value) {
            $DBCatalogLibreta[] = array(
                                'notaTipo' => $value->getNotaTipo(),
                                'notaTipoId' => $value->getNotaTipoId()
            );
        }

        /*if(!$inscripciones){
            $response->setStatusCode(202);
            $response->setData('El estudiante no tiene inscripciones registradas en esta Unidad Educativa');
            return $response;
        } */       
return array(
            'DBCatalogLibreta'=>$DBCatalogLibreta,
            'DBAsignatura'=> $DBAsignatura,
        );
        /*$response->setStatusCode(200);
        $response->setData(array(
            'DBCatalogLibreta'=>$DBCatalogLibreta,
            'DBAsignatura'=> $DBAsignatura,
        ));

        return $response;*/

    }    

}
