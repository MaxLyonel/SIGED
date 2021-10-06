<?php

namespace Sie\AppWebBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\LogTransaccion;
use JMS\Serializer\SerializerBuilder;
use Sie\AppWebBundle\Entity\InstitucioneducativaControlOperativoMenus;
//use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;


class OperativoUtils
{
	protected $em;
	protected $router;
	protected $session;

	public function __construct(EntityManager $entityManager, Router $router)
	{
		$this->em = $entityManager;
		$this->router = $router;
		$this->session = new Session();
	}

	public function verificarEstadoOperativo($request_sie,$request_gestion,$resquest_estado)
	{
		$estadoOperativo=false;
		$institucioneducativa = $this->em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request_sie);
		$operativo = null;
		if($institucioneducativa)
		{
			$tmpId=$institucioneducativa->getInstitucioneducativaTipo()->getId();
			$iePerCod=filter_var($this->session->get('ie_per_cod'),FILTER_SANITIZE_NUMBER_INT);
			$iePerCod=is_numeric($iePerCod)?$iePerCod:-1;

			$repository = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus');
			$query = $repository->createQueryBuilder('me')
								->where('me.institucioneducativa = :institucioneducativa')
								->andWhere('me.gestionTipoId = :gestion')
								->andWhere('me.estadoMenu = :estado')
								->setParameter('institucioneducativa', $institucioneducativa->getId())
								->setParameter('gestion', $request_gestion)
								->setParameter('estado', $resquest_estado)
								//->setParameter('periodoTipoId', $iePerCod )
								->getQuery();
			$operativo = $query->getOneOrNullResult();
			if($operativo)
			{
				if($operativo->getEstadoMenu()==$resquest_estado)
				{
					$estadoOperativo=true;
				}
				else
				{
					$estadoOperativo=false;
				}
			}
		}
		return $estadoOperativo;
	}

	public function cerrarOperativo($request_sie,$request_gestion,$request_estado)
	{
		$data=null;
		$status= 404;
		$msj='Ocurrio un error, por favor vuelva a intentarlo.';

		if($request_sie >0 && $request_gestion >0 && $request_estado>0)
		{
			$institucioneducativa = $this->em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request_sie);
			if($institucioneducativa)
			{
				$tmpId=$institucioneducativa->getInstitucioneducativaTipo()->getId();
				$iePerCod=filter_var($this->session->get('ie_per_cod'),FILTER_SANITIZE_NUMBER_INT);
				$iePerCod=is_numeric($iePerCod)?$iePerCod:-1;
				$repository = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus');
				$query = $repository->createQueryBuilder('me')
									->where('me.institucioneducativa = :institucioneducativa')
									->andWhere('me.gestionTipoId = :gestion')
									->andWhere('me.estadoMenu = :estado')
									->andWhere('me.periodoTipoId = :periodoTipoId')
									->setParameter('institucioneducativa', $institucioneducativa->getId() )
									->setParameter('gestion', $request_gestion )
									->setParameter('estado', $request_estado )
									->setParameter('periodoTipoId', $iePerCod )
									->getQuery();
				$operativo = $query->getOneOrNullResult();

				if($operativo==null)//no existe
				{
					$operativo = new InstitucioneducativaControlOperativoMenus();
					$operativo->setGestionTipoId($request_gestion);
					$operativo->setEstadoMenu($request_estado);
					$operativo->setFecharegistro(new \DateTime('now'));
					$operativo->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->findOneById(0));
					$operativo->setInstitucioneducativa($institucioneducativa);
					$operativo->setPeriodoTipoId($iePerCod);
					$this->em->persist($operativo);
					$this->em->flush();

					$data='ok';
					$status= 200;
					$msj='El operativo se cerró correctamente.';
				}
				else//si existe
				{
					$operativo->setEstadoMenu($request_estado);
					$operativo->setFecharegistro(new \DateTime('now'));
					$this->em->persist($operativo);
					$this->em->flush();

					$data='ok';
					$status= 200;
					$msj='El operativo ya esta cerrado.';
				}
			}
			else
			{
				$data=null;
				$status= 404;
				$msj='La institucion educativa no existe.';
			}
		}
		else
		{
			$data=null;
			$status= 404;
			$msj='Ocurrio un error, por favor vuelva a intentarlo.';
		}
		return array($data,$status,$msj);
	}

	public function abrirOperativo($request_id,$request_estado)
	{
		$db = $this->em->getConnection();
		$data=null;
		$status= 404;
		$msj='Ocurrio un error, por favor vuelva a intentarlo.';
		if($request_id >0)
		{
			$query ="delete from institucioneducativa_control_operativo_menus where id=? and estado_menu = ?";
			$stmt = $db->prepare($query);
			$params = array($request_id,$request_estado);
			$stmt->execute($params);
			$tmp=$stmt->fetchAll();
			$borrado = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus')->findOneBy(array('id' => $request_id));
			if($borrado==null)
			{
				$data='ok';
				$status= 200;
				$msj='El operativo fue abierto.';
			}
			else
			{
				$data=null;
				$status= 404;
				$msj='Ocurrio un error el operativo no pudo ser abierto, por favor vuelva a intentarlo.';
			}
		}
		else
		{
			$data=null;
			$status= 404;
			$msj='Ocurrio un error, por favor vuelva a intentarlo.';
		}
		return array($data,$status,$msj);
	}

	public function getUnidadesEducativas($departamento,$distrito,$gestion,$estado,$tipoUENopermitidos=array(-1))
	{
		$ue=array();
		$db = $this->em->getConnection();
		//$tipoUE=$this->session->get('sistemaid');
		$sysName = $this->session->get('sysname');
		$ie_tipo=$this->getTipoUE($sysName);

		$query ="
		SELECT a.*,b.id, b.gestion_tipo_id, b.estado_menu, to_char(b.fecharegistro,'dd/mm/YYYY') as  fecharegistro
		from(
				select distinct e.codigo as cod_dis,e.lugar as des_dis,c.id as institucioneducativa_id_target,c.institucioneducativa,(select dependencia from dependencia_tipo where id=c.dependencia_tipo_id ) as dependencia
				from (institucioneducativa c 
				inner join jurisdiccion_geografica d on c.le_juridicciongeografica_id=d.id
				inner join lugar_tipo e on e.lugar_nivel_id=7 and d.lugar_tipo_id_distrito=e.id)
				inner join institucioneducativa_sucursal f  on f.institucioneducativa_id = c.id
				where c.estadoinstitucion_tipo_id=10 
				and c.institucioneducativa_tipo_id = ?
				and f.gestion_tipo_id =?
				and c.dependencia_tipo_id not in (?)
				--and c.institucioneducativa_acreditacion_tipo_id=1 
				--and orgcurricular_tipo_id=1 
			) a
		left JOIN (select * from institucioneducativa_control_operativo_menus WHERE estado_menu=? and gestion_tipo_id =?) b on a.institucioneducativa_id_target = b.institucioneducativa_id
		where 
		cod_dis = ?
		and substring(cod_dis,1,1) = ?
		ORDER BY  dependencia asc,institucioneducativa asc
		";

		$stmt = $db->prepare($query);
		$params = array($ie_tipo,$gestion,implode(',',$tipoUENopermitidos),$estado,$gestion,$distrito,$departamento);
		$stmt->execute($params);
		$ue=$stmt->fetchAll();

		if(!$ue)
		{
			$ue=array();
		}
		return $ue;
	}

	public function getDepartamentoDistrito($numero)
	{
	  $departamento=-1;
	  $distrito=-1;
	  if($numero==0)
	  {
		$departamento=-1;
		$distrito=-1;
	  }
	  else
	  {
		if($numero>0 && $numero<=9)
		{
		  $departamento=$numero;
		  $distrito=-1;
		}
		else
		{
		  if($numero > 10 and strlen($numero)==4)
		  {
			$departamento=substr($numero,0,1);
			$distrito=$numero;
		  }
		  else
		  {
			$departamento=-1;
			$distrito=-1;
		  }
		}
	  }
	  return array($departamento,$distrito);
	}

	public function getDepartamentos()
	{
		$departamentos =  $this->em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();
		$departamentos_array = array();
		foreach ($departamentos as $value)
		{
			$departamentos_array[] = array('id'=>$value->getId(),'codigo'=>$value->getCodigo(),'depto'=>$value->getDepartamento());
		}
		return $departamentos_array;
	}

	public function getDistritos($departamento)
	{
		$distritos_array=array();
		$distritos = $this->em->getRepository('SieAppWebBundle:DistritoTipo')->findBy(array('departamentoTipo'=>$departamento));
		
		foreach ($distritos as $d)
		{
			$distritos_array[]=array('id' =>$d->getId(),'distrito'=>$d->getDistrito());
		}
		return $distritos_array;
	}

	public function getDatosUsuario($userId,$userRol)
	{
		$db = $this->em->getConnection();
		$userId=($userId)?$userId:-1;
		$userRol=($userRol)?$userRol:-1;
		$gestion = date('Y');
		$user=NULL;
		$query = '
		select *
		from (
		select b.rol_tipo_id,(select rol from rol_tipo where id=b.rol_tipo_id) as rol,a.persona_id,c.codigo as cod_dis,a.esactivo,a.id as user_id
		from usuario a 
		  inner join usuario_rol b on a.id=b.usuario_id 
			inner join lugar_tipo c on b.lugar_tipo_id=c.id
		where codigo not in (\'04\') and b.rol_tipo_id not in (2,3,9,29,26,21,14,39,6) and a.esactivo=\'t\'
		union all
		select f.rol_tipo_id,(select rol from rol_tipo where id=f.rol_tipo_id) as rol,a.persona_id,d.codigo as cod_dis,e.esactivo,e.id as user_id
		from maestro_inscripcion a
		  inner join institucioneducativa b on a.institucioneducativa_id=b.id
			inner join jurisdiccion_geografica c on b.le_juridicciongeografica_id=c.id
			  inner join lugar_tipo d on d.lugar_nivel_id=7 and c.lugar_tipo_id_distrito=d.id
				inner join usuario e on a.persona_id=e.persona_id
				  inner join usuario_rol f on e.id=f.usuario_id
		--where a.gestion_tipo_id='.$gestion.' and cargo_tipo_id in (1,12) and periodo_tipo_id=1 and f.rol_tipo_id=9 and e.esactivo=\'t\') a
		where a.gestion_tipo_id='.$gestion.' and cargo_tipo_id in (1,12) and e.esactivo=\'t\') a
		where user_id = ?
		and rol_tipo_id = ?
		ORDER BY cod_dis
		LIMIT 1
		';
		$stmt = $db->prepare($query);
		$params = array($userId,$userRol);
		$stmt->execute($params);
		$user=$stmt->fetch();
		return $user;
	}

	public function getTipoUE($sysName)
	{
        $tmpTipoUE = strtolower(filter_var($sysName , FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
        $tipoUE =-1;
        if(strpos($tmpTipoUE,'regular')>0)
        {
            $tipoUE=1;
        }
        elseif(strpos($tmpTipoUE,'alternativa')>0)
        {
            $tipoUE=2;
        }
        elseif(strpos($tmpTipoUE,'especial')>0)
        {
            $tipoUE=4;
        }
        else
        {
            $tipoUE=-1;
        }
        return $tipoUE;
	}

}
