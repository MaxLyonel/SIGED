<?php

namespace Sie\PermanenteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Controller\FuncionesController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoCorto;
use Sie\AppWebBundle\Entity\EstudianteInscripcionCursoCorto;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoCortoOferta;
use Symfony\Component\HttpFoundation\Response;

/**
 * MaestroGestion controller.
 *
 */
class InstitucionCursoCortoController extends Controller {
    public $session;
    public $idCarrera;
    public $idDepartamento;
    /*
     * Lista de maestros en la unidad educativa
     */
    public function indexAction(Request $request,$op) {
        // generar los titulos para los diferentes sistemas
        $this->session = new Session();
        $tipoSistema = $request->getSession()->get('sysname');
        
        
        ////////////////////////////////////////////////////
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
            $form = $request->get('form');
            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
            if (!$institucioneducativa) {
                $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                return $this->render('SiePermanenteBundle:InstitucionCursoCorto:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
            /*
            * verificamos si tiene tuicion
            */
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $form['institucioneducativa']);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            if ($aTuicion[0]['get_ue_tuicion']){
                $institucion = $form['institucioneducativa'];
                $gestion = $form['gestion'];
            }else{
                $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
                return $this->render('SiePermanenteBundle:InstitucionCursoCorto:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
        } else {
            $nivelUsuario = $request->getSession()->get('roluser');
            if ($nivelUsuario != 9 && $nivelUsuario != 5) { // si no es institucion educativa 5 o administrativp 9
                // formulario de busqueda de institucion educativa
                $sesinst = $request->getSession()->get('idInstitucion');
                if ($sesinst) {
                    $institucion = $sesinst;
                    $gestion = $request->getSession()->get('idGestion');
                    if($op == 'search'){
                       return $this->render('SiePermanenteBundle:InstitucionCursoCorto:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView())); 
                    }
                } else {
                    return $this->render('SiePermanenteBundle:InstitucionCursoCorto:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
            } else { // si es institucion educativa
                $sesinst = $request->getSession()->get('idInstitucion');
                if ($sesinst) {
                    $institucion = $sesinst;
                    $gestion = $request->getSession()->get('idGestion');
                }else{
                    $funcion = new FuncionesController();
                    $institucion = $funcion->ObtenerUnidadEducativaAction($request->getSession()->get('userId'),$request->getSession()->get('currentyear')); //5484231);
                    $gestion = $request->getSession()->get('currentyear');
                }    
            }
        }



        // creamos variables de sesion de la institucion educativa y gestion
        $request->getSession()->set('idInstitucion', $institucion);
        $request->getSession()->set('idGestion', $gestion);

        /*
         * lista de maestros registrados en la unidad educativa
         */
        $query = $em->createQuery(
                    'SELECT iecc FROM SieAppWebBundle:InstitucioneducativaCursoCorto iecc                    
                    WHERE iecc.institucioneducativa = :idInstitucion
                    AND iecc.gestionTipo = :gestion')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion);
        $cursosCortos = $query->getResult();
        
        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        
        return $this->render('SiePermanenteBundle:InstitucionCursoCorto:index.html.twig', array(
                    'cursosCortos' => $cursosCortos, 'institucion' => $institucion, 'gestion' => $gestion
        ));
    }
    
    /*
     * formularios de busqueda de institucion educativa
     */
    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioncursocorto'))
                ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'off','maxlength'=>8,'class'=>'form-control jnumbers','pattern'=>'[0-9]{8}')))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }
    
    /*
     * Llamamos al formulario para asignacion de nueva carrera
     * a la institucion
     */
    public function newAction(Request $request) {
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('idInstitucion'));
        
        // MSestrs o facilitadores
        $query = $em->createQuery(
                        'SELECT mi, per, ft FROM SieAppWebBundle:MaestroInscripcion mi
                    JOIN mi.persona per
                    JOIN mi.formacionTipo ft
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    AND mi.rolTipo = :rol
                    ORDER BY per.paterno, per.materno, per.nombre')
                ->setParameter('idInstitucion', $this->session->get('idInstitucion'))
                ->setParameter('gestion', $this->session->get('idGestion'))
                ->setParameter('rol',2);
        $maestro = $query->getResult();
        $maestroArray = array();
        foreach ($maestro as $m) {
            $maestroArray[] = array('id'=>$m->getId(),'nombre'=>$m->getPersona()->getPaterno()." ".$m->getPersona()->getMaterno()." ".$m->getPersona()->getNombre());
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioncursocorto_create'))
                ->add('idInstitucion', 'hidden', array('data' => $this->session->get('idInstitucion')))
                ->add('gestion', 'hidden', array('data' => $this->session->get('idGestion')))
                
                // Curso
                ->add('numero','text',array('label'=>'Número','required'=>true,'attr'=>array('class'=>'form-control jnumbers','maxlength'=>'4','autocomplete'=>'off')))
                ->add('curso','text',array('label'=>'Curso','attr'=>array('class'=>'form-control jsimbolosnumbersletters jupper','autocomplete'=>'off')))
                ->add('duracion','text',array('label'=>'Duración en horas','attr'=>array('class'=>'form-control jnumbers','pattern'=>'[0-9]{1,5}','autocomplete'=>'off','maxlength'=>'3')))
                ->add('fechaInicio','text',array('label'=>'Fecha de Inicio','attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                ->add('fechaFin','text',array('label'=>'Fecha de conclusión','attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                //->add('fechaRegistro','text',array('label'=>'Fecha de Registro','attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                ->add('departamento', 'entity', array('class' => 'SieAppWebBundle:LugarTipo',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('lt')
                                ->where('lt.lugarNivel = :id')
                                ->setParameter('id', '1')
                                ->orderBy('lt.id', 'ASC')
                        ;
                    }, 'property' => 'lugar','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('distrito', 'choice', array('label' => 'Provincia', 'required' =>true,'empty_value'=>'Seleccionar...','attr' => array('class' => 'form-control')))
                ->add('municipio','choice',array('label'=>'Seccion/Municipio','empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('lugar','text',array('label'=>'Lugar','attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                ->add('poblacion','entity',array('label'=>'Poblacion','class'=>'SieAppWebBundle:PoblacionTipo','attr'=>array('class'=>'form-control')))
                ->add('poblacionDetalle','text',array('label'=>'Poblacion Descripcion','required'=>true,'attr'=>array('class'=>'form-control jnumbersletters','autocomplete'=>'off')))
                ->add('areatematica','entity',array('label'=>'Área Temática','class'=>'SieAppWebBundle:AreatematicaTipo','attr'=>array('class'=>'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $this->render('SiePermanenteBundle:InstitucionCursoCorto:new.html.twig', array('form' => $form->createView(),'institucion'=>$institucion,'maestros'=>$maestroArray));
    }
    
    /*
     * registramos el nuevo maestro
     */
    public function createAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        try{
            $em->getConnection()->beginTransaction();
            $form = $request->get('form');
            $this->session = new Session();        
            $nuevoCursoCorto = new InstitucioneducativaCursoCorto();
            $nuevoCursoCorto->setCurso($form['curso']);
            $nuevoCursoCorto->setDuracionhoras($form['duracion']);
            //$nuevoCursoCorto->setFacilitador($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($form['facilitador']));
            $nuevoCursoCorto->setFechaInicio(new \DateTime($form['fechaInicio']));
            $nuevoCursoCorto->setFechaConclusion(new \DateTime($form['fechaFin']));
            $nuevoCursoCorto->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('idInstitucion')));
            $nuevoCursoCorto->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('idGestion')));
            $nuevoCursoCorto->setPoblacionTipo($em->getRepository('SieAppWebBundle:PoblacionTipo')->find($form['poblacion']));
            $nuevoCursoCorto->setPoblacionDetalle($form['poblacionDetalle']);
            $nuevoCursoCorto->setAreatematicaTipo($em->getRepository('SieAppWebBundle:AreatematicaTipo')->find($form['areatematica']));
            $nuevoCursoCorto->setLugarTipoMunicipio($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio']));
            $nuevoCursoCorto->setLugar($form['lugar']);
            $nuevoCursoCorto->setNumero($form['numero']);
            $nuevoCursoCorto->setEsabierto(true);
            $em->persist($nuevoCursoCorto);
            $em->flush();

            // Registramos los facilitadores
            $facilitadores = $request->get('facilitador');
            $horasFacilitadores = $request->get('horasFacilitador');
            for($i=0;$i<count($facilitadores);$i++){
                $newIECCO = new InstitucioneducativaCursoCortoOferta();
                $newIECCO->setInstitucioneducativaCursoCorto($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoCorto')->find($nuevoCursoCorto->getId()));
                $newIECCO->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($facilitadores[$i]));
                $newIECCO->setHoras($horasFacilitadores[$i]);
                $newIECCO->setFechaRegistro(new \DateTime('now'));
                $em->persist($newIECCO);
                $em->flush();
            }

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente');
            return $this->redirect($this->generateUrl('institucioncursocorto'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
        
    }
    
    public function editAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $inscursocorto = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoCorto')->find($request->get('idCursoCorto'));
        $this->session = new Session();
        
        // MSestrs o facilitadores
        $query = $em->createQuery(
                        'SELECT mi, per, ft FROM SieAppWebBundle:MaestroInscripcion mi
                    JOIN mi.persona per
                    JOIN mi.formacionTipo ft
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    AND mi.rolTipo = :rol
                    ORDER BY per.paterno, per.materno, per.nombre')
                ->setParameter('idInstitucion', $this->session->get('idInstitucion'))
                ->setParameter('gestion', $this->session->get('idGestion'))
                ->setParameter('rol',2);
        $maestro = $query->getResult();
        $maestroArray = array();
        foreach ($maestro as $m) {
            $maestroArray[] = array('id'=>$m->getId(),'nombre'=>$m->getPersona()->getPaterno()." ".$m->getPersona()->getMaterno()." ".$m->getPersona()->getNombre());
        }

        $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->find($inscursocorto->getLugarTipoMunicipio());
        $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->find($municipio->getLugarTipo()->getId());
        $departamento = $em->getRepository('SieAppWebBundle:LugarTipo')->find($provincia->getLugarTipo()->getId());

        $provincias = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$departamento->getId()));
        $provinciasArray = array();
        foreach ($provincias as $p) {
            $provinciasArray[$p->getId()] = $p->getLugar();
        }

        $municipios = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarTipo'=>$provincia->getId()));
        $municipiosArray = array();
        foreach ($municipios as $p) {
            $municipiosArray[$p->getId()] = $p->getLugar();
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioncursocorto_update'))
                ->add('idInstitucion', 'hidden', array('data' => $this->session->get('idInstitucion')))
                ->add('gestion', 'hidden', array('data' => $this->session->get('idGestion')))
                ->add('idCursoCorto', 'hidden', array('data' => $request->get('idCursoCorto')))
                // Curso
                ->add('numero','text',array('label'=>'Número','data'=>$inscursocorto->getNumero(),'required'=>true,'attr'=>array('class'=>'form-control jnumbers','maxlength'=>'4','autocomplete'=>'off')))
                ->add('curso','text',array('label'=>'Curso','data'=>$inscursocorto->getCurso(),'attr'=>array('class'=>'form-control jsimbolosnumbersletters','autocomplete'=>'off')))
                ->add('duracion','text',array('label'=>'Duración en horas','data'=>$inscursocorto->getDuracionhoras(),'attr'=>array('class'=>'form-control jnumbers','pattern'=>'[0-9]{1,5}','autocomplete'=>'off','maxlength'=>'3')))
                //->add('facilitador','choice',array('label'=>'Facilitador','choices'=>$maestroArray,'data'=>$inscursocorto->getFacilitador()->getId(),'attr'=>array('class'=>'form-control')))
                ->add('fechaInicio','text',array('label'=>'Fecha de Inicio','data'=>$inscursocorto->getFechaInicio()->format('d-m-Y'),'attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                ->add('fechaFin','text',array('label'=>'Fecha de conclusión','data'=>$inscursocorto->getFechaConclusion()->format('d-m-Y'),'attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                //->add('fechaRegistro','text',array('label'=>'Fecha de Registro','attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                ->add('departamento', 'entity', array('class' => 'SieAppWebBundle:LugarTipo',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('lt')
                                ->where('lt.lugarNivel = :id')
                                ->setParameter('id', '1')
                                ->orderBy('lt.id', 'ASC')
                        ;
                    }, 'property' => 'lugar','data'=>$em->getReference('SieAppWebBundle:LugarTipo',$departamento->getId()),'attr'=>array('class'=>'form-control')))
                ->add('distrito', 'choice', array('label' => 'Provincia','choices'=>$provinciasArray,'data'=>$provincia->getId(),'required' =>true,'empty_value'=>'Seleccionar...','attr' => array('class' => 'form-control')))
                ->add('municipio','choice',array('label'=>'Seccion/Municipio','choices'=>$municipiosArray,'data'=>$inscursocorto->getLugarTipoMunicipio()->getId(),'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('lugar','text',array('label'=>'Lugar','data'=>$inscursocorto->getLugar(),'attr'=>array('class'=>'form-control','autocomplete'=>'off')))
                ->add('poblacion','entity',array('label'=>'Poblacion','class'=>'SieAppWebBundle:PoblacionTipo','data'=>$em->getReference('SieAppWebBundle:PoblacionTipo',$inscursocorto->getPoblacionTipo()->getId()),'attr'=>array('class'=>'form-control')))
                ->add('poblacionDetalle','text',array('label'=>'Poblacion Descripcion','data'=>$inscursocorto->getPoblacionDetalle(),'required'=>true,'attr'=>array('class'=>'form-control jnumbersletters','autocomplete'=>'off')))
                ->add('areatematica','entity',array('label'=>'Área Temática','class'=>'SieAppWebBundle:AreatematicaTipo','data'=>$em->getReference('SieAppWebBundle:AreatematicaTipo',$inscursocorto->getAreatematicaTipo()->getId()),'attr'=>array('class'=>'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $this->render('SiePermanenteBundle:InstitucionCursoCorto:edit.html.twig', array('form' => $form->createView(),'maestros'=>$maestroArray));
    }
    
    public function updateAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        try{
            $em->getConnection()->beginTransaction();
            $form = $request->get('form');
            $this->session = new Session();        
            $updateCursoCorto = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoCorto')->find($form['idCursoCorto']);
            $updateCursoCorto->setCurso($form['curso']);
            $updateCursoCorto->setDuracionhoras($form['duracion']);
            //$updateCursoCorto->setFacilitador($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($form['facilitador']));
            $updateCursoCorto->setFechaInicio(new \DateTime($form['fechaInicio']));
            $updateCursoCorto->setFechaConclusion(new \DateTime($form['fechaFin']));
            $updateCursoCorto->setPoblacionTipo($em->getRepository('SieAppWebBundle:PoblacionTipo')->find($form['poblacion']));
            $updateCursoCorto->setPoblacionDetalle($form['poblacionDetalle']);
            $updateCursoCorto->setAreatematicaTipo($em->getRepository('SieAppWebBundle:AreatematicaTipo')->find($form['areatematica']));
            $updateCursoCorto->setLugarTipoMunicipio($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio']));
            $updateCursoCorto->setLugar($form['lugar']);
            $updateCursoCorto->setNumero($form['numero']);
            $em->flush();


            // Eliminamos previamente los facilitadores
            $q = $em->createQuery(
                'DELETE FROM SieAppWebBundle:InstitucioneducativaCursoCortoOferta iecco
                WHERE iecco.institucioneducativaCursoCorto = :idCursoCorto')
                ->setParameter('idCursoCorto',$form['idCursoCorto']);
            $numDelete = $q->execute();
            // Registramos los facilitadores
            $facilitadores = $request->get('facilitador');
            $horasFacilitadores = $request->get('horasFacilitador');
            
            for($i=0;$i<count($facilitadores);$i++){
                $newIECCO = new InstitucioneducativaCursoCortoOferta();
                $newIECCO->setInstitucioneducativaCursoCorto($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoCorto')->find($updateCursoCorto->getId()));
                $newIECCO->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($facilitadores[$i]));
                $newIECCO->setHoras($horasFacilitadores[$i]);
                $newIECCO->setFechaRegistro(new \DateTime('now'));
                $em->persist($newIECCO);
                $em->flush();
            }


            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('updateOk', 'Los datos fueron actualizados correctamente');
            return $this->redirect($this->generateUrl('institucioncursocorto'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }
    
    public function deleteAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $institucionCursoCorto = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoCorto')->find($request->get('idCursoCorto'));
            
            // Verificacmo si el curso corto no tiene estudiantes inscritos
            $inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcionCursoCorto')->findBy(array('institucioneducativaCursoCorto'=>$request->get('idCursoCorto')));
            if(count($inscritos)>0){

                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('deleteError', 'No se puede eliminar el curso, tiene participantes inscritos');
                return $this->redirect($this->generateUrl('institucioncursocorto'));
            }
            // Eliminnamos los facilitadores registrados
            $q = $em->createQuery(
                'DELETE FROM SieAppWebBundle:InstitucioneducativaCursoCortoOferta iecco
                WHERE iecco.institucioneducativaCursoCorto = :idCursoCorto')
                ->setParameter('idCursoCorto',$institucionCursoCorto->getId());
            $numDelete = $q->execute();

            $em->remove($institucionCursoCorto);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('deleteOk', 'El registro fue eliminado correctamente');
            return $this->redirect($this->generateUrl('institucioncursocorto'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('deleteError', 'No se pudo eliminar el registro');
            return $this->redirect($this->generateUrl('institucioncursocorto'));
        }
    }

    public function closeAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            // Verificamos si el curso tiene al menos un estudiante oparticipante inscrito
            $inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcionCursoCorto')->findBy(array('institucioneducativaCursoCorto'=>$request->get('idCursoCorto')));
            if(count($inscritos)<=0){
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('closeError', 'No se puede cerrar un curso si no tiene participantes inscritos');
                return $this->redirect($this->generateUrl('institucioncursocorto'));
            }

            $cursoCorto = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoCorto')->find($request->get('idCursoCorto'));
            $cursoCorto->setEsabierto(false);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('closeOk', 'Se cerro el curso, ahora puede imprimir los certificados');
            return $this->redirect($this->generateUrl('institucioncursocorto'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }
    /**
     * Participantes
     */
    public function participante_indexAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $this->session = new Session();
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->get('idInstitucion'));
            $cursoCorto = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoCorto')->find($request->get('idCursoCorto'));
            $participantes = $em->createQuery(
                'SELECT eicc FROM SieAppWebBundle:EstudianteInscripcionCursoCorto eicc
                WHERE eicc.institucioneducativaCursoCorto = :idCursoCorto
                ORDER BY eicc.paterno,eicc.materno,eicc.nombre')
                ->setParameter('idCursoCorto',$cursoCorto->getId())
                ->getResult();
                $this->session->set('idCursoCorto',$cursoCorto->getId());
            $em->getConnection()->commit();
            return $this->render('SiePermanenteBundle:InstitucionCursoCorto:participante_index.html.twig',array('institucion'=>$institucion,'cursoCorto'=>$cursoCorto,'gestion'=>$request->get('gestion'),'participantes'=>$participantes));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    public function participante_sessionAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $this->session = new Session();
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('idInstitucion'));
            $cursoCorto = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoCorto')->find($this->session->get('idCursoCorto'));
            $participantes = $em->createQuery(
                'SELECT eicc FROM SieAppWebBundle:EstudianteInscripcionCursoCorto eicc
                WHERE eicc.institucioneducativaCursoCorto = :idCursoCorto
                ORDER BY eicc.paterno,eicc.materno,eicc.nombre')
                ->setParameter('idCursoCorto',$cursoCorto->getId())
                ->getResult();
            $em->getConnection()->commit();
            return $this->render('SiePermanenteBundle:InstitucionCursoCorto:participante_index.html.twig',array('institucion'=>$institucion,'cursoCorto'=>$cursoCorto,'gestion'=>$this->session->get('idGestion'),'participantes'=>$participantes));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    public function participante_newAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $this->session = new Session();

            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->get('idInstitucion'));
            $cursoCorto = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoCorto')->find($request->get('idCursoCorto'));
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioncursocorto_participante_create'))
                ->add('idInstitucion', 'hidden', array('data' => $this->session->get('idInstitucion')))
                ->add('gestion', 'hidden', array('data' => $this->session->get('idGestion')))
                ->add('idCursoCorto', 'hidden', array('data' => $request->get('idCursoCorto')))
                // Curso
                ->add('carnet','text',array('label'=>'Carnet','required'=>false,'attr'=>array('class'=>'form-control jsimbolosnumbersletters','maxlength'=>'11','autocomplete'=>'off')))
                ->add('paterno','text',array('label'=>'Paterno','required'=>false,'attr'=>array('class'=>'form-control jname','maxlength'=>'45','autocomplete'=>'off','pattern'=>"[a-zA-Z\sñÑáéíóúÁÉÍÚÓ']{2,45}")))
                ->add('materno','text',array('label'=>'Materno','required'=>false,'attr'=>array('class'=>'form-control jname','maxlength'=>'45','autocomplete'=>'off','pattern'=>"[a-zA-Z\sñÑáéíóúÁÉÍÚÓ']{2,45}")))
                ->add('nombre','text',array('label'=>'Nombre','required'=>true,'attr'=>array('class'=>'form-control jname','maxlength'=>'45','autocomplete'=>'off','pattern'=>"[a-zA-Z\sñÑáéíóúÁÉÍÚÓ']{2,45}")))
                ->add('edad','text',array('label'=>'Edad','required'=>true,'attr'=>array('class'=>'form-control jnumbers','maxlength'=>'2','autocomplete'=>'off','pattern'=>'[0-9]{1,2}','onkeyup'=>'validarEdad(this.value)')))
                ->add('genero', 'entity', array('class' => 'SieAppWebBundle:GeneroTipo',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('gt')
                                ->where('gt.id IN (:ids)')
                                ->setParameter('ids', array(1,2))
                                ->orderBy('gt.id', 'ASC')
                        ;
                    }, 'property' => 'genero','label'=>'Género','empty_value'=>'Seleccionar...','required'=>true,'attr'=>array('class'=>'form-control')))
                ->add('organizacion','text',array('label'=>'Organización/Comunidad','required'=>true,'attr'=>array('class'=>'form-control jnumbersletters','maxlength'=>'140','autocomplete'=>'off','pattern'=>"[a-zA-ZñÑáéíóúÁÉÍÚÓ'\s]{2,140}")))
                
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

            $em->getConnection()->commit();
            return $this->render('SiePermanenteBundle:InstitucionCursoCorto:participante_new.html.twig',array('form'=>$form->createView(),'institucion'=>$institucion,'cursoCorto'=>$cursoCorto,'gestion'=>$request->get('gestion')));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    public function participante_createAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $this->session = new Session();
            $form = $request->get('form');
            $newEstudiante = new EstudianteInscripcionCursoCorto();
            $newEstudiante->setPaterno($form['paterno']);
            $newEstudiante->setMaterno($form['materno']);
            $newEstudiante->setNombre($form['nombre']);
            $newEstudiante->setEdad($form['edad']);
            $newEstudiante->setCarnetIdentidad($form['carnet']);
            $newEstudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['genero']));
            $newEstudiante->setOrganizacionComunidad($form['organizacion']);
            $newEstudiante->setInstitucioneducativaCursoCorto($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoCorto')->find($form['idCursoCorto']));
            $em->persist($newEstudiante);
            $em->flush();
            
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Registro correcto');
            return $this->redirect($this->generateUrl('institucioncursocorto_participante_session'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }         
    }

    public function participante_editAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $this->session = new Session();
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->get('idInstitucion'));
            $cursoCorto = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoCorto')->find($request->get('idCursoCorto'));
            $participante = $em->getRepository('SieAppWebBundle:EstudianteInscripcionCursoCorto')->find($request->get('idParticipante'));
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('institucioncursocorto_participante_update'))
                ->add('idParticipante', 'hidden', array('data' => $participante->getId()))
                ->add('carnet','text',array('label'=>'Carnet','data'=>$participante->getCarnetIdentidad(),'required'=>false,'attr'=>array('class'=>'form-control jsimbolosnumbersletters','maxlength'=>'11','autocomplete'=>'off')))
                ->add('paterno','text',array('label'=>'Paterno','data'=>$participante->getPaterno(),'required'=>false,'attr'=>array('class'=>'form-control jname','maxlength'=>'45','autocomplete'=>'off','pattern'=>"[a-zA-Z\sñÑáéíóúÁÉÍÚÓ']{2,45}")))
                ->add('materno','text',array('label'=>'Materno','data'=>$participante->getMaterno(),'required'=>false,'attr'=>array('class'=>'form-control jname','maxlength'=>'45','autocomplete'=>'off','pattern'=>"[a-zA-Z\sñÑáéíóúÁÉÍÚÓ']{2,45}")))
                ->add('nombre','text',array('label'=>'Nombre','data'=>$participante->getNombre(),'required'=>true,'attr'=>array('class'=>'form-control jname','maxlength'=>'45','autocomplete'=>'off','pattern'=>"[a-zA-Z\sñÑáéíóúÁÉÍÚÓ']{2,45}")))
                ->add('edad','text',array('label'=>'Edad','data'=>$participante->getEdad(),'required'=>true,'attr'=>array('class'=>'form-control jnumbers','maxlength'=>'2','autocomplete'=>'off','pattern'=>'[0-9]{1,2}')))
                ->add('genero', 'entity', array('class' => 'SieAppWebBundle:GeneroTipo',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('gt')
                                ->where('gt.id IN (:ids)')
                                ->setParameter('ids', array(1,2))
                                ->orderBy('gt.id', 'ASC')
                        ;
                    }, 'property' => 'genero','label'=>'Género','empty_value'=>'Seleccionar...','data'=>$em->getReference('SieAppWebBundle:GeneroTipo',$participante->getGeneroTipo()->getId()),'required'=>true,'attr'=>array('class'=>'form-control')))
                ->add('organizacion','text',array('label'=>'Organización/Comunidad','data'=>$participante->getOrganizacionComunidad(),'required'=>true,'attr'=>array('class'=>'form-control jnumbersletters','maxlength'=>'140','autocomplete'=>'off','pattern'=>"[a-zA-ZñÑáéíóúÁÉÍÚÓ'\s]{2,140}")))
                
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

            $em->getConnection()->commit();
            return $this->render('SiePermanenteBundle:InstitucionCursoCorto:participante_edit.html.twig',array('form'=>$form->createView(),'institucion'=>$institucion,'cursoCorto'=>$cursoCorto,'gestion'=>$request->get('gestion')));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }
    }

    public function participante_updateAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $this->session = new Session();
            $form = $request->get('form');
            $newEstudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcionCursoCorto')->find($form['idParticipante']);
            $newEstudiante->setPaterno($form['paterno']);
            $newEstudiante->setMaterno($form['materno']);
            $newEstudiante->setNombre($form['nombre']);
            $newEstudiante->setEdad($form['edad']);
            $newEstudiante->setCarnetIdentidad($form['carnet']);
            $newEstudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['genero']));
            $newEstudiante->setOrganizacionComunidad($form['organizacion']);
            $em->flush();
            
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('updateOk', 'El registro fue modificado correctamente');
            return $this->redirect($this->generateUrl('institucioncursocorto_participante_session'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }         
    }

    public function participante_deleteAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $this->session = new Session();
            $deleteEstudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcionCursoCorto')->find($request->get('idParticipante'));
            $em->remove($deleteEstudiante);
            $em->flush();
            
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('deleteOk', 'El registro fue eliminado correctamente');
            return $this->redirect($this->generateUrl('institucioncursocorto_participante_session'));
        }catch(Exception $ex){
            $em->getConnection()->rollback();
        }       
    }

    public function facilitadoresAction(){
        $em = $this->getDoctrine()->getManager();
        $this->session = new Session();
        $query = $em->createQuery(
                        'SELECT mi, per, ft FROM SieAppWebBundle:MaestroInscripcion mi
                    JOIN mi.persona per
                    JOIN mi.formacionTipo ft
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    AND mi.rolTipo = :rol
                    ORDER BY per.paterno, per.materno, per.nombre')
                ->setParameter('idInstitucion', $this->session->get('idInstitucion'))
                ->setParameter('gestion', $this->session->get('idGestion'))
                ->setParameter('rol',2);
        $maestro = $query->getResult();
        $maestroArray = array();
        foreach ($maestro as $m) {
            $maestroArray[$m->getId()] = $m->getPersona()->getPaterno()." ".$m->getPersona()->getMaterno()." ".$m->getPersona()->getNombre();
        }
        
        $response = new JsonResponse();
        return $response->setData(array('facilitadores'=>$maestroArray));
    }

    public function facilitadores_editAction($idCursoCorto){
        $em = $this->getDoctrine()->getManager();
        $this->session = new Session();
        $query = $em->createQuery(
                        'SELECT mi, per, ft FROM SieAppWebBundle:MaestroInscripcion mi
                    JOIN mi.persona per
                    JOIN mi.formacionTipo ft
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    AND mi.rolTipo = :rol
                    ORDER BY per.paterno, per.materno, per.nombre')
                ->setParameter('idInstitucion', $this->session->get('idInstitucion'))
                ->setParameter('gestion', $this->session->get('idGestion'))
                ->setParameter('rol',2);
        $maestro = $query->getResult();
        $maestroArray = array();
        foreach ($maestro as $m) {
            $maestroArray[$m->getId()] = $m->getPersona()->getPaterno()." ".$m->getPersona()->getMaterno()." ".$m->getPersona()->getNombre();
        }

        $facilitadores = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoCortoOferta')->findBy(array('institucioneducativaCursoCorto'=>$idCursoCorto));
        $facArray = array();
        foreach ($facilitadores as $f) {
            $facArray[] = array('maestroInscripcionId'=>$f->getMaestroInscripcion()->getId(),'horas'=>$f->getHoras());
        }
        
        $response = new JsonResponse();
        return $response->setData(array('facCurso'=>$facArray, 'facilitadores'=>$maestroArray));
    }

    public function downloadCertificadoAction($centro, $estudiante, $curso, $duracion){

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'certificado.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_dj_certificado_curso_corto_gral_v1.rptdesign&ue=' . $centro . '&student=' . $estudiante.'&curso=' . $curso . '&duracion=' . $duracion . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    
    }
}
