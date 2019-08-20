<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\CdlClubLectura;
use Sie\AppWebBundle\Entity\CdlEventos;
use Doctrine\ORM\EntityRepository;

class CdlSeguimientoController extends Controller
{
    public function indexAction(Request $request)
    { 
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $lista = array();
        $lista[0]['nombre'] = "Clubs de Lectura por Unidad Educativa";
        $lista[0]['ruta'] = "cdl_seguimiento_porsie";
        $lista[1]['nombre'] = "Unidades Educativas de su Jurisdicción que cuentan con Clubs de Lectura.";
        $lista[1]['ruta'] = "cdl_seguimiento_porjurisdiccion";
        return $this->render('SieHerramientaBundle:CdlSeguimiento:index.html.twig', array('lista'=>$lista,
            ));
    }
    
    public function cdlPorSieAction(Request $request)
    { 
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
                    ->add('codsie','text',array('label'=>'SIE:', 'required'=>true, 'attr'=>array('maxlength' => '8','class'=>'form-control validar')))
                    ->add('gestion','entity',array('label'=>'Gestión:','required'=>true,'class'=>'SieAppWebBundle:GestionTipo','query_builder'=>function(EntityRepository $g){
                        return $g->createQueryBuilder('g')->where('g.id>=2019')->orderBy('g.id','DESC');},'property'=>'gestion','empty_value' => false,'attr'=>array('class'=>'form-control')))
                    ->add('buscar', 'button', array('label'=> 'Buscar', 'attr'=>array('class'=>'form-control btn btn-primary','onclick'=>'buscarCdl()')))
                    ->getForm();
        
        return $this->render('SieHerramientaBundle:CdlSeguimiento:cdlPorSie.html.twig', array('form'=>$form->createView(),
            ));
    }

    public function cdlPorSieListaAction(Request $request)
    { 
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $rol = $this->session->get('roluser');
        $form = $request->get('form');
        //dump($form);die;
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $id_usuario);
        $query->bindValue(':sie', $form['codsie']);
        $query->bindValue(':rolId', $rol);
        $query->execute();
        $aTuicion = $query->fetchAll();
        if ($aTuicion[0]['get_ue_tuicion']) {
            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['codsie']);
            $query = $em->getConnection()->prepare("SELECT a.id as cdl_club_lectura_id,a.nombre_club,COUNT(DISTINCT b.id) as nro_eventos,COUNT(DISTINCT c.id) as nro_integrantes
                                                    FROM cdl_club_lectura a
                                                    JOIN institucioneducativa_sucursal ies ON a.institucioneducativasucursal_id=ies.id
                                                    LEFT JOIN cdl_eventos b on a.id=b.cdl_club_lectura_id
                                                    LEFT JOIN cdl_integrantes c ON a.id=c.cdl_club_lectura_id
                                                    WHERE ies.institucioneducativa_id=". $form['codsie'] ." and gestion_tipo_id=". $form['gestion'] ."
                                                    GROUP BY a.id,a.nombre_club");
            $query->execute();
            $data = $query->fetchAll();
            $msg="ok";
        }else{
            $data = array();
            $msg="No tiene tuición sobre la unidad educativa";
        }        
        return $this->render('SieHerramientaBundle:CdlSeguimiento:cdlPorSieLista.html.twig', array('data'=>$data,'msg'=>$msg,'institucioneducativa'=>$institucioneducativa
                    ));
    }

    
    public function cdlPorJurisdiccionAction(Request $request)
    { 
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $roluserlugarid = $this->session->get('roluserlugarid');
        $gestion = $this->session->get('currentyear');

        
        if ($request->isMethod('POST')) {
            $codigo = $request->get('codigo');
			$rol = $request->get('rol');
        } else {
            $rol = $this->session->get('roluser');
            if($rol == 9){
                $codigo = $this->session->get('ie_id');
            }else{
                
                $codigo = $em->getRepository('SieAppWebBundle:LugarTipo')->find($roluserlugarid)->getCodigo();
                //dump($codigo);die;
            }
		}

        $em = $this->getDoctrine()->getManager();
        
        if($rol == 8 or $rol == 20){
            $query = $em->getConnection()->prepare("SELECT 'NACIONAL' AS jurisdiccion,'Departamento' as nombreArea, lt1.codigo as codigo, lt1.lugar  as nombre,COUNT(DISTINCT ie.id) as cantidad, 7 as rolUsuario, (SELECT COUNT(*) as total
            FROM institucioneducativa ie
            JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
            WHERE ie.institucioneducativa_tipo_id=1 and estadoinstitucion_tipo_id=10 and institucioneducativa_acreditacion_tipo_id=1)
            FROM cdl_club_lectura a
            JOIN institucioneducativa_sucursal ies on a.institucioneducativasucursal_id=ies.id AND ies.gestion_tipo_id=". $gestion ."
            JOIN institucioneducativa ie on ie.id=ies.institucioneducativa_id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            JOIN lugar_tipo lt on lt.id=jg.lugar_tipo_id_distrito
            JOIN lugar_tipo lt1 on lt1.id=lt.lugar_tipo_id
            GROUP BY lt1.id,lt1.codigo,lt1.lugar");
        }elseif($rol == 7){
            $query = $em->getConnection()->prepare("SELECT lt1.lugar as jurisdiccion,'Distrito Educativo' as nombreArea, lt.codigo as codigo, lt.lugar  as nombre,COUNT(DISTINCT ie.id) as cantidad, 10 as rolUsuario,(SELECT COUNT(*) as total
            FROM institucioneducativa ie
            JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            JOIN lugar_tipo lt on lt.id=jg.lugar_tipo_id_distrito
            JOIN lugar_tipo lt1 on lt1.id=lt.lugar_tipo_id
            WHERE ie.institucioneducativa_tipo_id=1 and estadoinstitucion_tipo_id=10 and institucioneducativa_acreditacion_tipo_id=1 and lt1.codigo='". $codigo ."')
            FROM cdl_club_lectura a
            JOIN institucioneducativa_sucursal ies on a.institucioneducativasucursal_id=ies.id AND ies.gestion_tipo_id=". $gestion ."
            JOIN institucioneducativa ie on ie.id=ies.institucioneducativa_id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            JOIN lugar_tipo lt on lt.id=jg.lugar_tipo_id_distrito
            JOIN lugar_tipo lt1 on lt1.id=lt.lugar_tipo_id
            WHERE lt1.codigo::integer=". $codigo ."
            GROUP BY lt.id,lt.codigo,lt.lugar,lt1.lugar");
        }elseif($rol == 10){
            $query = $em->getConnection()->prepare("SELECT lt.lugar as jurisdiccion,'Unidad Educativa' as nombreArea, ie.id as codigo, ie.id || '-'||ie.institucioneducativa  as nombre,COUNT(DISTINCT ie.id) as cantidad, 9 as rolUsuario,(SELECT COUNT(*) as total
            FROM institucioneducativa ie
            JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            JOIN lugar_tipo lt on lt.id=jg.lugar_tipo_id_distrito
            WHERE ie.institucioneducativa_tipo_id=1 and estadoinstitucion_tipo_id=10 and institucioneducativa_acreditacion_tipo_id=1 and lt.codigo='". $codigo ."')
            FROM cdl_club_lectura a
            JOIN institucioneducativa_sucursal ies on a.institucioneducativasucursal_id=ies.id AND ies.gestion_tipo_id=". $gestion ."
            JOIN institucioneducativa ie on ie.id=ies.institucioneducativa_id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            JOIN lugar_tipo lt on lt.id=jg.lugar_tipo_id_distrito
            JOIN lugar_tipo lt1 on lt1.id=lt.lugar_tipo_id
            WHERE lt.codigo::integer=". $codigo ."
            GROUP BY ie.id,ie.institucioneducativa,lt.lugar");

        }elseif($rol == 9){
            $query = $em->getConnection()->prepare("SELECT ie.id || '-'||ie.institucioneducativa as jurisdiccion,'Club de Lectura' as nombreArea, a.id as codigo, a.nombre_club  as nombre,1 as cantidad,0 as rolUsuario, 1 as total
                                                    FROM cdl_club_lectura a
                                                    JOIN institucioneducativa_sucursal ies on a.institucioneducativasucursal_id=ies.id AND ies.gestion_tipo_id=". $gestion ."
                                                    JOIN institucioneducativa ie on ie.id=ies.institucioneducativa_id
                                                    JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
                                                    JOIN lugar_tipo lt on lt.id=jg.lugar_tipo_id_distrito
                                                    JOIN lugar_tipo lt1 on lt1.id=lt.lugar_tipo_id
                                                    WHERE ie.id=". $codigo);


        }
        
        $query->execute();
        $data = $query->fetchAll();
        //dump($data);die;
        
        return $this->render('SieHerramientaBundle:CdlSeguimiento:cdlPorJuridiccion.html.twig', array('data'=>$data,
            ));
    }
}
