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
                $arrNotaTipo = array();
                // validation type of request
                if($estudiante['bth']=='true'){
                    $opcion = 2;
                }   
                $arrStudent = array(
                    'nombre'=>'',
                    'paterno'=>'',
                    'materno'=>'',
                    'estId'=>'',
                    'estInsId'=>'',
                    'institucioneducativaId'=>'',
                    'codigoRude' => '',
                    'carnet' => '',
                    //'idObsBono' => sizeof($objObservationsStudent)>0?$objObservationsStudent[0]->getId():''
                );                                  
                // validation on SIE info
                $codigoSie = mb_strtoupper($estudiante['codigoSie']);
                $objInstitucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id'=>$codigoSie, 'institucioneducativaTipo'=>1));
                // check if the UE exist
                if(!$objInstitucioneducativa){                    
                    return $response->setData([
                        'status'=>'error',
                        'msg'=>'Los datos introducidos no son válidos, codigo SIE invalido'
                    ]);
                }else{
                    // validation to the UE plena
                    if($opcion == 2){
                        $objUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$codigoSie, 'gestionTipoId'=>$this->session->get('currentyear'),'institucioneducativaHumanisticoTecnicoTipo'=>1));
                        
                        if(sizeof($objUePlena)>0){
                           
                        }else{
                            return $response->setData([
                                'status'=>'error',
                                'msg'=>'Los datos del introducidos no son válidos, codigo SIE no pertenece a una Unidad Educativa Plena'
                            ]);
                        }                    
                    }
                    
                    $arrUnidadEducativa = array(
                        'nombre'=>$objInstitucioneducativa->getInstitucioneducativa(),
                        'codigoSie' => $objInstitucioneducativa->getId(),
                        
                    );                     
                }

                // validation on codigoRude
                if($estudiante['codigoRude']){

                    $codigoRude = mb_strtoupper($estudiante['codigoRude']);
                    $objestudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$estudiante['codigoRude']));
                    
                    if (($estudiante)) {
                        //$codigoRude = $objestudiante->getCodigoRude();
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
                                            ->setParameter('idEstudiante', $objestudiante->getId())
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
                        $idStudent = $objestudiante->getId(); 
                        $arrStudent = array(
                            'nombre'=>$objestudiante->getNombre(),
                            'paterno'=>$objestudiante->getPaterno(),
                            'materno'=>$objestudiante->getMaterno(),
                            'estId'=>$objestudiante->getId(),
                            'estInsId'=>$inscripcionesEfectivas[0]['id'],
                            'institucioneducativaId'=>$inscripcionesEfectivas[0]['institucioneducativaId'],
                            'codigoRude' => $objestudiante->getCodigoRude(),
                            'carnet' => $objestudiante->getCarnetIdentidad(),
                            //'idObsBono' => sizeof($objObservationsStudent)>0?$objObservationsStudent[0]->getId():''
                        );                                                     

                    }else{
                        return $response->setData([
                            'status'=>'error',
                            'msg'=>'Los datos del introducidos no son válidos, codigo Rude invalido'
                        ]);
                    }                        
                
                }else{
                    $idStudent = null;
                }
                // get the nota tipo info to do execute the query
                if($estudiante['tri1']=='true'){
                    array_push($arrNotaTipo,6);
                }
                if($estudiante['tri2']=='true'){
                    array_push($arrNotaTipo,7);
                }
                if($estudiante['tri3']=='true'){
                    array_push($arrNotaTipo,8);
                }                   
                array_push($arrNotaTipo,9);
                /*if($estudiante['prom']=='true'){
                
                }*/                             
                $notaTipoId = implode(',', $arrNotaTipo);


                                      

        
                switch ($opcion) {
                    case 1:
                        //get notas from 2019 to 2020
                        $query = $em->getConnection()->prepare('SELECT * from sp_genera_elimina_notas_2020(:ibandera ::VARCHAR,:icodue ::VARCHAR,:inivel_tipo_id ::VARCHAR,:igrado_tipo_id ::VARCHAR,:iasignatura_tipo_id ::VARCHAR,:inota_tipo_id::VARCHAR,:iestudiante_id::VARCHAR )');
                        $query->bindValue(':ibandera', 1);
                        $query->bindValue(':icodue', $objInstitucioneducativa->getId());
                        $query->bindValue(':inivel_tipo_id', null);
                        $query->bindValue(':igrado_tipo_id', null);
                        $query->bindValue(':iasignatura_tipo_id', null);
                        $query->bindValue(':inota_tipo_id', $notaTipoId);
                        $query->bindValue(':iestudiante_id', $idStudent);
                        $query->execute();
                       
                        break;
                    case 2:
                        //get notas from 2019 to 2020
                        $query = $em->getConnection()->prepare('SELECT * from sp_genera_elimina_notas_2020(:ibandera ::VARCHAR,:icodue ::VARCHAR,:inivel_tipo_id ::VARCHAR,:igrado_tipo_id ::VARCHAR,:iasignatura_tipo_id ::VARCHAR,:inota_tipo_id::VARCHAR,:iestudiante_id::VARCHAR )');
                        $query->bindValue(':ibandera', 2);
                        $query->bindValue(':icodue', $objInstitucioneducativa->getId());
                        $query->bindValue(':inivel_tipo_id', null);
                        $query->bindValue(':igrado_tipo_id', null);
                        $query->bindValue(':iasignatura_tipo_id', null);
                        $query->bindValue(':inota_tipo_id', null);
                        $query->bindValue(':iestudiante_id', $idStudent);
                        $query->execute();     
                        break;
                }
             
                return $response->setData([
                    'status'=>'success',
                    'datos'=>array(
           
                        'arrUnidadEducativa'=>$arrUnidadEducativa,
                        'arrStudent'=>$arrStudent,
                        'opcion' => 2
                        
                    )
                ]);
            }                

}
