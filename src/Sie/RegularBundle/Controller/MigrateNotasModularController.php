<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

class MigrateNotasModularController extends Controller
{
    /*public function indexAction()
    {
        return $this->render('SieRegularBundle:MigrateNotasModular:index.html.twig', array(
                // ...
            ));    }
            */
            public $session;

            public function __construct() {
                $this->session = new Session();
            }
            
            public function indexAction() {
         
                return $this->render('SieRegularBundle:MigrateNotasModular:index.html.twig', array(
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

                // validation on SIE info
                $codigoSie = mb_strtoupper($estudiante['codigoSie']);
                $objInstitucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id'=>$codigoSie, 'institucioneducativaTipo'=>1));
                // check if the UE exist
                if(!$objInstitucioneducativa){                    
                    return $response->setData([
                        'status'=>'error',
                        'msg'=>'Los datos del introducidos no son válidos, codigo SIE invalido'
                    ]);
                }else{
                    // validation to the UE modular

						$objUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$codigoSie, 'gestionTipoId'=>$this->session->get('currentyear'),'institucioneducativaHumanisticoTecnicoTipo'=>3));
						if(sizeof($objUePlena)>0){
						   
						}else{
						    return $response->setData([
						        'status'=>'error',
						        'msg'=>'Los datos introducidos no son válidos, codigo SIE no pertenece a una Unidad Educativa Modular'
						    ]);
						}                    
                    
                    
                    $arrUnidadEducativa = array(
                        'nombre'=>$objInstitucioneducativa->getInstitucioneducativa(),
                        'codigoSie' => $objInstitucioneducativa->getId(),
                        
                    );                     
                }

                //execute function to migrate modular notas
                $query = $em->getConnection()->prepare('SELECT * from sp_genera_migracion_notas_modulares_estudiante_2019_2020(:icodue ::VARCHAR )');
                $query->bindValue(':icodue', $objInstitucioneducativa->getId());
                $query->execute();

                return $response->setData([
                    'status'=>'success',
                    'datos'=>array(           
                        'arrUnidadEducativa'=>$arrUnidadEducativa,
                        'opcion' => 2
                        
                    )
                ]);
            }            


}
