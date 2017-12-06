<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;

class ListCloseRudeController extends Controller
{
  public $session;
   /**
    * [__construct description]
    */
    public function __construct(){
      $this->session = new Session();
    }
    /**
     * [indexAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function indexAction(Request $request){

      //get the users distrito
      $codDistrito=$this->getUserDistrito($this->session->get('personaId'));
      //get the UEs rude option close
      $arrUeCloseRude = $this->getUeCloseRude($codDistrito);
      // dump($arrUeCloseRude);die;
        return $this->render('SieRegularBundle:ListCloseRude:index.html.twig', array(
                'sw' => $codDistrito,
                 'arrUeCloseRude' => $arrUeCloseRude,
                 'codDistrito' => base64_encode($codDistrito)
            ));
    }
    /**
     * [enableAction description]
     * @return [type] [description]
     */
    public function enableAction(Request $request){
      // if everything is good do the next
      try {
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        // get the valuse send
        $form = $request->get('form');
        // check if the UE close the rude task
        $objinstitucioneducativaOperativoLogExist = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findOneBy(array(
          'institucioneducativa' => $form['sie'],
          'gestionTipoId'  => $form['gestion'],
          'institucioneducativaOperativoLogTipo' => $form['operativotipo']
        ));
        $this->addFlash(
          'removeit',
          'Unidad Educativa '.$form['sie'].' habilitada para el registro'
        );
        //remove the data
        $em->remove($objinstitucioneducativaOperativoLogExist);
        // $em->flush();
        // $em->getConnection()->commit();
        $response = $this->forward('SieRegularBundle:ListCloseRude:index');
        return $response;
      } catch (Exception $e) {
        // if there is an error got it
        $em->getConnection()->rollback();
        echo "some error on SIGED ".$e->getMessage();
        $this->addFlash(
          'noremoveit',
          'Unidad Educativa '.$form['sie'].' no habilitada  para el registro'
        );
      }

    }
    /**
     * [getUserDistrito description]
     * @param  [type] $personaId [description]
     * @return [type]            [description]
     */
    private function getUserDistrito($personaId){
      $em = $this->getDoctrine()->getManager();
      $query = $em->getConnection()->prepare(
        "
              select f.id,g.codigo
              from usuario d
              left join usuario_rol e on e.usuario_id = d.id
              left join rol_tipo f on e.rol_tipo_id = f.id
              inner join lugar_tipo g on e.lugar_tipo_id = g.id
              inner join lugar_nivel_tipo h on f.lugar_nivel_tipo_id = h.id
              where
              d.persona_id = ".$personaId." and
              e.esactivo is true and
              f.id not in ('9','2')
        "
      );
      $query->execute();
      $arrRolesDistritoUser = $query->fetchAll();
      $swOption = true;
      reset($arrRolesDistritoUser);
      while($swOption && $val = current($arrRolesDistritoUser)){
        if($val['id']==10){
          $swOption = false;
          $codUserDistrito = $val['codigo'];
        }
        next($arrRolesDistritoUser);
      }
      if($swOption)
       return false;
      else
       return $codUserDistrito;
    }
    /**
     * [getUeCloseRude description]
     * @param  [type] $codDistrito [description]
     * @return [type]              [description]
     */
    private function getUeCloseRude($codDistrito){
      $em = $this->getDoctrine()->getManager();
      $query = $em->getConnection()->prepare("
                                  SELECT DISTINCT
                                  lt4.codigo AS codigo_departamento,
                                  lt4.lugar AS departamento,
                                  dist.id AS codigo_distrito,
                                  dist.distrito AS distrito,
                                  inst.id AS codigo_sie,
                                  inst.institucioneducativa AS centro_educativo,
                                  lt.id
                                  FROM
                                  institucioneducativa AS inst
                                  INNER JOIN jurisdiccion_geografica AS jurg ON inst.le_juridicciongeografica_id = jurg.id
                                  INNER JOIN distrito_tipo AS dist ON dist.id = jurg.distrito_tipo_id
                                  LEFT JOIN lugar_tipo AS lt ON lt.id = jurg.lugar_tipo_id_localidad
                                  LEFT JOIN lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
                                  LEFT JOIN lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
                                  LEFT JOIN lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
                                  LEFT JOIN lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
                                  WHERE
                                  inst.id IN
                                  (
                                  SELECT DISTINCT institucioneducativa_id
                                  from institucioneducativa_operativo_log
                                  where institucioneducativa_operativo_log_tipo_id = 4
                                  and gestion_tipo_id = 2017
                                  )
                                  and dist.id = ".$codDistrito."
                                  ORDER BY
                                  codigo_departamento,
                                  codigo_distrito,
                                  codigo_sie
      ");
      $query->execute();
      $objUeCloseRude = $query->fetchAll();
      return $objUeCloseRude;
    }

}
