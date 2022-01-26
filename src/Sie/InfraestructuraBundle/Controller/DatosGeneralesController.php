<?php

namespace Sie\InfraestructuraBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InfraestructuraH1Datosgenerales;
use Sie\AppWebBundle\Entity\InfraestructuraH1Institucioneseducativa;

class DatosGeneralesController extends Controller
{
	public $session;
    public $infJurGeoId;
    public $jurGeoId = null;
	public function __construct() {
        $this->session = new Session();
        $this->infJurGeoId = $this->session->get('infJurGeoId');

    }

    public function indexAction()
    {
        $infJurGeoId = $this->session->get('infJurGeoId');
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $infH1Dat = $em->getRepository('SieAppWebBundle:InfraestructuraH1Datosgenerales')->findOneBy(array('infraestructuraJuridiccionGeografica'=>$infJurGeoId));
            if(!$infH1Dat){
                $infH1Dat = new InfraestructuraH1Datosgenerales();
                $id = 'new';
            }else{
                $id = $infH1Dat->getId();
            }

            $form = $this->createDatosForm($infH1Dat,$id);

            return $this->render('SieInfraestructuraBundle:DatosGenerales:index.html.twig',array('form'=>$form->createView()));

        } catch (Exception $e) {

        }
    }

    public function createDatosForm($entity, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $infJurGeo = $em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($this->infJurGeoId);
        $jurGeo = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($infJurGeo->getJuridiccionGeografica()->getId());

        $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');
        $ubicacion = $repository->createQueryBuilder('jg')
                ->select('lt4.codigo AS codigo_departamento,
                        lt4.lugar AS departamento,
                        lt3.codigo AS codigo_provincia,
                        lt3.lugar AS provincia,
                        lt2.codigo AS codigo_seccion,
                        lt2.lugar AS seccion,
                        lt1.codigo AS codigo_canton,
                        lt1.lugar AS canton,
                        lt.codigo AS codigo_localidad,
                        lt.lugar AS localidad,
                        dist.id AS codigo_distrito,
                        dist.distrito,
                        jg.id AS codigo_le,
                        lt.area2001')
                ->join('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
                ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
                ->where('jg.id = :idJurGeo')
                ->setParameter('idJurGeo', $jurGeo->getId())
                ->getQuery()
                ->getResult();

        if(!$ubicacion){
            $ubicacion = null;
        }

        $this->jurGeoId = $ubicacion[0]['codigo_le'];
    	
        $form = $this->createFormBuilder($entity)
            ->setAction($this->generateUrl('infra_datosGenerales_create_update'))
            ->add('id','hidden',array('data'=>$id,'mapped'=>false))
            /*
             * 1. Datos de ubicacion
             */
            ->add('n12codigoEdificio','text',array('label'=>'','mapped'=>false,'data'=>$ubicacion[0]['codigo_le'],'attr'=> array('class'=>'form-control','disabled'=>true)))
            ->add('n13codDepartamento','text',array('label'=>'','mapped'=>false,'data'=>$ubicacion[0]['codigo_departamento'],'attr'=> array('class'=>'form-control','disabled'=>true)))
            ->add('n13departamento','text',array('label'=>'','mapped'=>false,'data'=>$ubicacion[0]['departamento'],'attr'=> array('class'=>'form-control','disabled'=>true)))
            ->add('n14codProvincia','text',array('label'=>'','mapped'=>false,'data'=>$ubicacion[0]['codigo_provincia'],'attr'=> array('class'=>'form-control','disabled'=>true)))
            ->add('n14provincia','text',array('label'=>'','mapped'=>false,'data'=>$ubicacion[0]['provincia'],'attr'=> array('class'=>'form-control','disabled'=>true)))
            ->add('n15codMunicipal','text',array('label'=>'','mapped'=>false,'data'=>$ubicacion[0]['codigo_seccion'],'attr'=> array('class'=>'form-control','disabled'=>true)))
            ->add('n15municipal','text',array('label'=>'','mapped'=>false,'data'=>$ubicacion[0]['seccion'],'attr'=> array('class'=>'form-control','disabled'=>true)))
            ->add('n16codCanton','text',array('label'=>'','mapped'=>false,'data'=>$ubicacion[0]['codigo_canton'],'attr'=> array('class'=>'form-control','disabled'=>true)))
            ->add('n16canton','text',array('label'=>'','mapped'=>false,'data'=>$ubicacion[0]['canton'],'attr'=> array('class'=>'form-control','disabled'=>true)))
            ->add('n17codCuidad','text',array('label'=>'','mapped'=>false,'data'=>$ubicacion[0]['codigo_localidad'],'attr'=> array('class'=>'form-control','disabled'=>true)))
            ->add('n17cuidad','text',array('label'=>'','mapped'=>false,'data'=>$ubicacion[0]['localidad'],'attr'=> array('class'=>'form-control','disabled'=>true)))
            ->add('n18distrito','text',array('label'=>'','mapped'=>false,'data'=>$ubicacion[0]['distrito'],'attr'=> array('class'=>'form-control','disabled'=>true)))
            ->add('n19areaGeografica','text',array('label'=>'','mapped'=>false,'data'=>$ubicacion[0]['area2001'],'attr'=> array('class'=>'form-control','disabled'=>true)))

            ->add('n110Zonabarrio','text',array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>100,'autocomplete'=>'off','title'=>'1.10 Zona/Barrio')))
            ->add('n111Macrodistrito','text',array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>100,'autocomplete'=>'off','title'=>'1.11 Macrodistrito municipal')))
            ->add('n112Distritomun','text',array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>100,'autocomplete'=>'off','title'=>'1.12 Distrito municipal')))

            /*
             * 2. Direccion espedifica del edificio
             */
            ->add('n21Calleavenida','text',array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>100,'title'=>'2.1 Calle o Avenida y Número')))
            ->add('n22Descripcionacceso','textarea',array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>170,'title'=>'2.2 Descripción del acceso a partir de un lugar fácilmente reconocible en la zona')))

            /*
             * 3. Vías de acceso
             */
            ->add('n31Tramotroncal','text',array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>100,'title'=>'3.1 Tramo red troncal')))
            ->add('n32Tramocomplementaria','text',array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>100,'title'=>'3.2 Tramo red complementaria - Red departamental')))
            ->add('n33Tramovecinal','text',array('label'=>'','attr'=> array('class'=>'form-control jupper','maxlength'=>100,'title'=>'3.3 Tramo red vecinal')))

            ->add('n34TerrestreDias','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>3,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Terrestrehrs','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>3,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Terrestremin','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>3,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Terrestrecosto','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>5,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Terrestredescripcion','text',array('label'=>'','required'=>false,'attr'=> array('class'=>'form-control jupper','maxlength'=>170)))

            ->add('n34Fluvialdias','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>3,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Fluvialhrs','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>3,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Fluvialmin','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>3,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Fluvialcosto','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>5,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Fluvialdescripcion','text',array('label'=>'','required'=>false,'attr'=> array('class'=>'form-control jupper','maxlength'=>170)))

            ->add('n34Aereodias','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>3,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Aereohrs','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>3,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Aereomin','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>3,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Aereocosto','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>5,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Aereodescripcion','text',array('label'=>'','required'=>false,'attr'=> array('class'=>'form-control jupper','maxlength'=>170)))

            ->add('n34Combinaciondias','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>3,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Combinacionhrs','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>3,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Combinacionmin','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>3,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Combinacioncosto','text',array('label'=>'','data'=>0,'attr'=> array('class'=>'form-control','style'=>'width: 50px','maxlength'=>5,'title'=>'3.4 Tipo de acceso desde la Dirección Distrital')))
            ->add('n34Combinaciondescripcion','text',array('label'=>'','required'=>false,'attr'=> array('class'=>'form-control jupper','maxlength'=>170)))

            /*
             * 4 Unidades educativas que operan en este edificio educativo
             */
            
            /*
             * 5. Otros servicios
             */
            
            ->add('n5Obs','textarea',array('label'=>'Observaciones','required'=>false,'attr'=>array('class'=>'form-control jupper','maxlength'=>200,'rows'=>5)))

            ->add('guardar','submit',array('label'=>'Guardar Cambios','attr'=>array('class'=>'btn btn-success-alt')))

            ->getForm();

        return $form;
    }

    public function createUpdateAction(Request $request){
        try{
            $infJurGeoId = $this->session->get('infJurGeoId');
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $formulario = $request->get('form');
            //dump($request);die;
            
            if($formulario['id'] == 'new'){ 
                $entity = new InfraestructuraH1Datosgenerales();
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h1_datosgenerales');")->execute();
            }else{
                $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH1Datosgenerales')->find($formulario['id']);
            }

            $form = $this->createDatosForm($entity,$formulario['id']);
            $form->handleRequest($request);

            if ($form->isValid()) {

                if($formulario['id'] == 'new'){
                    $entity->setInfraestructuraJuridiccionGeografica($em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($infJurGeoId));
                    $entity->setFecharegistro(new \DateTime('now'));
                }
                $em->persist($entity);
                $em->flush();

                // REgistro de unidades asociadas

                $uesasociadas = $em->getRepository('SieAppWebBundle:InfraestructuraH1Institucioneseducativa')->findBy(array('infraestructuraH1Datosgenerales' => $entity->getId()));
                if ($uesasociadas) {
                    foreach ($uesasociadas as $u) {
                        $em->remove($em->getRepository('SieAppWebBundle:InfraestructuraH1Institucioneseducativa')->find($u->getId()));
                        $em->flush();
                    }
                }


                $sie = $request->get('sie');
                $institucion = $request->get('institucion');
                $personaId= $request->get('personaId');
                $persona = $request->get('persona');
                $telefono = $request->get('telefono');
                $tenenciaTipo = $request->get('tenenciaTipo');
                $orgCurricularTipo = $request->get('orgCurricularTipo');
                $obs = $request->get('obs');

                /*dump($sie);
                dump($institucion);
                dump($personaId);
                dump($persona);
                dump($telefono);
                dump($tenenciaTipo);
                dump($orgCurricularTipo);
                //die;*/

                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h1_institucioneseducativa');")->execute();
                for ($i=0; $i < count($sie); $i++) { 
                    $newServSan  = new InfraestructuraH1Institucioneseducativa();
                    $newServSan->setInfraestructuraH1Datosgenerales($entity);
                    $newServSan->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie[$i]));
                    $newServSan->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($personaId[$i]));
                    $newServSan->setTelefonoJe($telefono[$i]);
                    $newServSan->setTenenciaTipo($em->getRepository('SieAppWebBundle:InfraestructuraH1TenenciaTipo')->find($tenenciaTipo[$i]));
                    $newServSan->setOrgcurricularTipo($em->getRepository('SieAppWebBundle:OrgcurricularTipo')->find($orgCurricularTipo[$i]));
                    $newServSan->setObs(mb_strtoupper($obs[$i],'utf-8'));
                    $em->persist($newServSan);
                    $em->flush();
                }
                $em->getConnection()->commit();
                return $this->redirectToRoute('infra_info_acceder');
            }
            $em->getConnection()->commit();
            return $this->render('SieInfraestructuraBundle:DatosGenerales:index.html.twig',array('form'=>$form->createView()));

        }catch(Exception $e){
            $em->getConnection()->rollback();
        }
    }

    public function uesasociadasAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $id = $request->get('id');

            $uesasociadas = array();

            // Segun el RUE
            $infrJurGeo = $em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($this->infJurGeoId);
            $uesSegunRUE = $em->getRepository('SieAppWebBundle:Institucioneducativa')
                              ->createQueryBuilder('ie')
                              ->select('ie')
                              ->where('ie.leJuridicciongeografica = :jurGeoId')
                              ->andWhere('ie.estadoinstitucionTipo IN (:estado)')
                              ->setParameter('jurGeoId',$infrJurGeo->getJuridiccionGeografica()->getId())
                              ->setParameter('estado',array(10,20,30,40))
                              ->orderBy('ie.id','ASC')
                              ->getQuery()
                              ->getResult();

            if($uesSegunRUE){
                foreach ($uesSegunRUE as $ueRUE) {
                    // Obtenemos el director
                    $directorDatos = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('institucioneducativa'=>$ueRUE->getId(),'gestionTipo'=>$infrJurGeo->getGestionTipo()->getId(),'rolTipo'=>9));
                    if($directorDatos){
                        $director = $directorDatos->getPersona()->getNombre().' '.$directorDatos->getPersona()->getPaterno().' '.$directorDatos->getPersona()->getMaterno();
                    }else{
                        $director = '';
                    }

                    if($id != 'new'){
                        $unidad = $em->getRepository('SieAppWebBundle:InfraestructuraH1Institucioneseducativa')->findOneBy(array('infraestructuraH1Datosgenerales'=>$id, 'institucioneducativa'=>$ueRUE->getId()));
                        if($unidad){
                            $uesasociadas[] = array(
                                'sie'=>$unidad->getInstitucioneducativa()->getId(),
                                'institucion'=>$unidad->getInstitucioneducativa()->getId().' - '.$unidad->getInstitucioneducativa()->getInstitucioneducativa(),
                                'personaId'=>$unidad->getPersona()->getId(),
                                'personaCarnet'=>$unidad->getPersona()->getCarnet(),
                                'persona'=>$unidad->getPersona()->getNombre().' '.$unidad->getPersona()->getPaterno().' '.$unidad->getPersona()->getMaterno(),
                                'telefono'=>$unidad->getTelefonoJe(),
                                'tenencia'=>$unidad->getTenenciaTipo()->getId(),
                                'orgCurricular'=>$unidad->getOrgcurricularTipo()->getId(),
                                'orgCurricularNombre'=>$unidad->getOrgcurricularTipo()->getOrgcurricula(),
                                'obs'=>$unidad->getObs(),
                                'director'=>$director
                            );
                        }else{
                            $uesasociadas[] = array(
                                'sie'=>$ueRUE->getId(),
                                'institucion'=>$ueRUE->getId().' - '.$ueRUE->getInstitucioneducativa(),
                                'personaId'=>'',
                                'personaCarnet'=>'',
                                'persona'=>'',
                                'telefono'=>'',
                                'tenencia'=>'',
                                'orgCurricular'=>$ueRUE->getOrgcurricularTipo()->getId(),
                                'orgCurricularNombre'=>$ueRUE->getOrgcurricularTipo()->getOrgcurricula(),
                                'obs'=>'',
                                'director'=>'');

                        }
                    }else{
                        $uesasociadas[] = array(
                                'sie'=>$ueRUE->getId(),
                                'institucion'=>$ueRUE->getId().' - '.$ueRUE->getInstitucioneducativa(),
                                'personaId'=>'',
                                'persona'=>'',
                                'persona'=>'',
                                'telefono'=>'',
                                'tenencia'=>'',
                                'orgCurricular'=>$ueRUE->getOrgcurricularTipo()->getId(),
                                'orgCurricularNombre'=>$ueRUE->getOrgcurricularTipo()->getOrgcurricula(),
                                'obs'=>'',
                                'director'=>'');
                    }
                }                
            }else{
                $uesasociadas[] = null;
            }
            /*
            //dump($uesasociadas);die;
            if($id != 'new'){
                $unidadesAsociadas = $em->getRepository('SieAppWebBundle:InfraestructuraH1Institucioneseducativa')->findBy(array('infraestructuraH1Datosgenerales'=>$id));
                foreach ($unidadesAsociadas as $uea) {
                    $uesasociadas[] = array(
                        'sie'=>$uea->getInstitucioneducativa()->getId(),
                        'institucion'=>$uea->getInstitucioneducativa()->getId().' - '.$uea->getInstitucioneducativa()->getInstitucioneducativa(),
                        'personaId'=>$uea->getPersona()->getId(),
                        'persona'=>$uea->getPersona()->getPaterno().' '.$uea->getPersona()->getMaterno().' '.$uea->getPersona()->getNombre(),
                        'telefono'=>$uea->getTelefonoJe(),
                        'tenencia'=>$uea->getTenenciaTipo()->getId(),
                        'orgCurricular'=>$uea->getOrgcurricularTipo()->getId(),
                        'obs'=>$uea->getObs()
                    );
                }
            }else{
                $uesasociadas = null;
            }*/

            $tenencia = $em->getRepository('SieAppWebBundle:InfraestructuraH1TenenciaTipo')->findAll();
            $tenenciaTipo = array();
            foreach ($tenencia as $t) {
                $tenenciaTipo[$t->getId()] = $t->getTenenciaTipo();
            }

            $orgCurricular = $em->getRepository('SieAppWebBundle:OrgcurricularTipo')->findBy(array('id'=>array(1,2,3)));
            $orgCurricularTipo = array();
            foreach ($orgCurricular as $eg) {
                $orgCurricularTipo[$eg->getId()] = $eg->getOrgcurricula();
            }

            $response = new JsonResponse();
            $em->getConnection()->commit();
            return $response->setData(array(
                'uesasociadas' => $uesasociadas,
                'tenenciaTipo'=>$tenenciaTipo,
                'orgCurricularTipo'=>$orgCurricularTipo
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    public function seleccionarUEAction(Request $request){
        $sie = $request->get('sie');
        $em = $this->getDoctrine()->getManager();
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
        if($institucioneducativa){
            $sie = $institucioneducativa->getId();
            $institucion = $institucioneducativa->getInstitucionEducativa();
            $existe = 1;
        }else{
            $sie = '';
            $institucion ='';
            $existe = 0;
        }

        return new JsonResponse(array(
            'sie'=>$sie,
            'institucion'=>$institucion,
            'existe'=>$existe
        ));
    }

    public function seleccionarPersonaAction(Request $request){
        $carnet = $request->get('carnet');
        $em = $this->getDoctrine()->getManager();
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('carnet'=>$carnet));
        if($persona){
            $id = $persona->getId();
            $carnet = $persona->getCarnet();
            $nombre = $persona->getPaterno().' '.$persona->getMaterno().' '.$persona->getNombre();
            $existe = 1;
        }else{
            $id = '';
            $carnet = '';
            $nombre = '';
            $existe = 0;
        }

        return new JsonResponse(array(
            'id'=>$id,
            'carnet'=>$carnet,
            'nombre'=>$nombre,
            'existe'=>$existe
        ));
    } 
}
