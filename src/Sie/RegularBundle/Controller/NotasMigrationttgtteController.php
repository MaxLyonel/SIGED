<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

class NotasMigrationttgtteController extends Controller
{
    /*public function indexAction()
    {
        return $this->render('SieRegularBundle:NotasMigrationttgtte:index.html.twig', array(
                // ...
            ));    }*/
            public $session;

            public function __construct() {
                $this->session = new Session();
            }
            
            public function indexAction() {
         
                return $this->render('SieRegularBundle:NotasMigrationttgtte:index.html.twig', array(
                    // ...
                )); 
            }
        
            public function buscarAction(Request $request){
                $response = new JsonResponse();
                $estudiante = $request->get('estudiante', null);
                $opcion = $request->get('opcion', null);
                $gestionSelected = $this->session->get('currentyear');
                $em = $this->getDoctrine()->getManager();
                
                $objUe = true;

                // VALIDAMOS DATOS DEL ESTUDIANTE
                $arrStudent = array();
                switch ($opcion) {
                    case 1:
                        $codigoRude = mb_strtoupper($estudiante['codigoRude']);
                        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$codigoRude));
                        $codigoRude = $estudiante->getCodigoRude();
                        // BUSCAMOS UNA INSCRIPCION CON ESTADO EFECTIVO EN LA GESTION 2020
                        $inscripcionesEfectivas = $em->createQueryBuilder()
                                            ->select('ei.id, nt.id as idNivel, grat.id as idGrado, IDENTITY(iec.institucioneducativa) as institucioneducativaId, (iec.id) as iecId, e.id as estId')
                                            ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                                            ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                                            ->innerJoin('SieAppWebBundle:Institucioneducativa','inst','with','iec.institucioneducativa = inst.id')
                                            ->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
                                            ->innerJoin('SieAppWebBundle:GradoTipo','grat','with','iec.gradoTipo = grat.id')
                                            ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                                            ->where('e.id = :idEstudiante')
                                            //->andWhere('ei.estadomatriculaTipo = 4')
                                            ->andWhere('gt.id = :year')
                                            ->andWhere('inst.institucioneducativaTipo IN (:tipeInst)')
                                            ->setParameter('idEstudiante', $estudiante->getId())
                                            ->setParameter('tipeInst', array(1))
                                            ->setParameter('year', $gestionSelected)
                                            ->getQuery()
                                            ->getResult();
                
                        
                        if (count($inscripcionesEfectivas)==0) {
                            return $response->setData([
                                'status'=>'error',
                                'msg'=>'El estudiante no cuenta con una inscripción efectiva en la presente gestión'
                            ]);
                        }
                        
                        break;
                    case 2:
                        $codigoSie = mb_strtoupper($estudiante['codigoSie']);
                        $estudiante = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id'=>$codigoSie, 'institucioneducativaTipo'=>1));
                        //$codigoRude = $estudiante->getCodigoRude();
                        break;
                }
                
                if (!is_object($estudiante)) {
                    return $response->setData([
                        'status'=>'error',
                        'msg'=>'Los datos del introducidos no son válidos'
                    ]);
                }
        
        

        
                switch ($opcion) {
                    case 1:
                        //get notas from 2019 to 2020
                        /*$query = $em->getConnection()->prepare('SELECT * from sp_genera_migracion_notas_ttg_tte_2019_2020(:iestudiante_id ::VARCHAR,:iinstitucioneducativa_id::VARCHAR, )');
                        $query->bindValue(':iestudiante_id', $inscripcionesEfectivas[0]['estId']);
                        $query->bindValue(':iinstitucioneducativa_id', NULL);               
                        $query->execute();*/

                        $arrStudent = array(
                            'nombre'=>$estudiante->getNombre(),
                            'paterno'=>$estudiante->getPaterno(),
                            'materno'=>$estudiante->getMaterno(),
                            'estId'=>$estudiante->getId(),
                            'estInsId'=>$inscripcionesEfectivas[0]['id'],
                            'institucioneducativaId'=>$inscripcionesEfectivas[0]['institucioneducativaId'],
                            'codigoRude' => $estudiante->getCodigoRude(),
                            'carnet' => $estudiante->getCarnetIdentidad(),
                            //'idObsBono' => sizeof($objObservationsStudent)>0?$objObservationsStudent[0]->getId():''
                        ); 
                       
                        break;
                    case 2:
                        //get notas from history to 2020 notas
                        /*$query = $em->getConnection()->prepare('SELECT * from sp_genera_migracion_notas_ttg_tte_2019_2020(:iestudiante_id ::VARCHAR,:iinstitucioneducativa_id::VARCHAR, )');
                        $query->bindValue(':iestudiante_id', $inscripcionesEfectivas[0]['estId']);
                        $query->bindValue(':iinstitucioneducativa_id', $inscripcionesEfectivas[0]['institucioneducativaId']);               
                        $query->execute();*/
                       
                        $arrStudent = array(
                            'nombre'=>$estudiante->getInstitucioneducativa(),
                            'codigoSie' => $estudiante->getId(),
                            //'idObsBono' => sizeof($objObservationsStudent)>0?$objObservationsStudent[0]->getId():''
                        ); 
                        
                        break;
                }
             
               
         
             
                return $response->setData([
                    'status'=>'success',
                    'datos'=>array(
           
                        'arrStudent'=>$arrStudent,
                        'opcion' => $opcion
                        //'arrToPrint'=>$arrToPrint,
                      
        
                       
                    )
                ]);
            }                

}
