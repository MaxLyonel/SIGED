<?php

namespace Sie\AppWebBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\LogTransaccion;
use JMS\Serializer\SerializerBuilder;

class Login {

	protected $em;
	protected $router;
    protected $session;

	public function __construct(EntityManager $entityManager, Router $router) {
		$this->em = $entityManager;
		$this->router = $router;
        $this->session = new Session();
	}

	/*
     * verificamos si tiene roles activos
     */
	public function verificarRolesActivos($id, $key) {                
        $gestion = '2018';
        $semestre = '3';

        $db = $this->em->getConnection();
        //******************
        //****COMPRUEBA SI EL DIRECTOR TIENE VIGENTES EN EL PERIODO SOLICITADO ALTERNATIVA    
        //*****************
        $query = "
                select a.persona_id, b.id, b.institucioneducativa, c.orgcurricula
                from maestro_inscripcion a
                inner join institucioneducativa b on a.institucioneducativa_id = b.id
                inner join institucioneducativa_sucursal d on d.institucioneducativa_id = b.id
                inner join orgcurricular_tipo c on b.orgcurricular_tipo_id = c.id
                where
                a.persona_id = ".$id." and
                c.id = 2 and
                d.gestion_tipo_id = ".$gestion." and
                a.gestion_tipo_id = ".$gestion." and
                d.periodo_tipo_id = ".$semestre." and
                (a.cargo_tipo_id = 1 or a.cargo_tipo_id = 12 or a.cargo_tipo_id = 0 ) and
                a.es_vigente_administrativo is true and
                b.institucioneducativa_tipo_id = 2
                ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();                
        
        //*****EN CASO DE QUE EXISTA VIGENTES EN LA GESTION
        if (count($po) > 0){ 
            $gestionAlt = $gestion;
            $periodoAlt = $semestre;        
        //*****EN CASO DE QUE NO EXISTA VIGENTES EN LA GESTION ENVIA LA BUSQUEDA UN PERIODO ATRAS PARA ALTERNATIVA
        }else{
            if ($semestre == '2'){
                $gestionAlt = strval( intval($gestion)-1);
                $periodoAlt = '3';
            }
            if ($semestre == '3'){
                $gestionAlt = $gestion;
                $periodoAlt = '2';
            }
        }

        //******************
        //****COMPRUEBA SI EL DIRECTOR TIENE VIGENTES EN EL PERIODO SOLICITADO REGULAR    
        //*****************
        $query = "
                select a.persona_id, b.id, b.institucioneducativa, c.orgcurricula
                from maestro_inscripcion a
                inner join institucioneducativa b on a.institucioneducativa_id = b.id            
                inner join orgcurricular_tipo c on b.orgcurricular_tipo_id = c.id
                where
                a.persona_id = ".$id." and
                c.id = 1 and            
                a.gestion_tipo_id = ".$gestion." and            
                (a.cargo_tipo_id = 1 or a.cargo_tipo_id = 12 or a.cargo_tipo_id = 0 ) and
                a.es_vigente_administrativo is true
            ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();                

        //*****EN CASO DE QUE EXISTA VIGENTES EN LA GESTION
        if (count($po) > 0){ 
            $gestionReg = $gestion;                
         //*****EN CASO DE QUE NO EXISTA VIGENTES EN LA GESTION ENVIA LA BUSQUEDA UN PERIODO ATRAS PARA ALTERNATIVA
        }else{                
            $gestionReg = strval( intval($gestion)-1);
        }


        //******************
        //****COMPRUEBA SI EL DIRECTOR TIENE VIGENTES EN EL PERIODO SOLICITADO ESPECIAL    
        //*****************
        $query = "
            select a.persona_id, b.id, b.institucioneducativa, c.orgcurricula
            from maestro_inscripcion a
            inner join institucioneducativa b on a.institucioneducativa_id = b.id            
            inner join orgcurricular_tipo c on b.orgcurricular_tipo_id = c.id
            where
            a.persona_id = ".$id." and
            c.id = 2 and            
            a.gestion_tipo_id = ".$gestion." and            
            (a.cargo_tipo_id = 1 or a.cargo_tipo_id = 12 or a.cargo_tipo_id = 0 ) and
            a.es_vigente_administrativo is true and
            b.institucioneducativa_tipo_id = 4
        ";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();                

        //*****EN CASO DE QUE EXISTA VIGENTES EN LA GESTION
        if (count($po) > 0){ 
            $gestionEsp = $gestion;                
        //*****EN CASO DE QUE NO EXISTA VIGENTES EN LA GESTION ENVIA LA BUSQUEDA UN PERIODO ATRAS PARA ALTERNATIVA
        }else{                
            $gestionEsp = strval( intval($gestion)-1);
        }
                
        $query =    "select * from (
                            --OTROS ROLES                  
                            select '1' as peso, (cast(e.id as varchar)||cast(g.id as varchar)||cast('-' as varchar)) as key, f.id as id, f.rol, h.id as idNivelLugar, h.nivel, g.id as rollugarid, g.lugar, d.persona_id as idPersona, '-' as sie, '-' as institucioneducativa, cast('-' as varchar) as idIETipo, '-' as orgcurricula
                            from usuario d
                            left join usuario_rol e on e.usuario_id = d.id
                            left join rol_tipo f on e.rol_tipo_id = f.id
                            inner join lugar_tipo g on e.lugar_tipo_id = g.id
                            inner join lugar_nivel_tipo h on f.lugar_nivel_tipo_id = h.id                  
                            where 
                            d.persona_id = '".$id."' and            
                            e.esactivo is true and
                            f.id not in ('9','2')

                            UNION--DIRECTOR REGULAR
                            select '2' as peso, (cast(e.id as varchar)||cast(g.id as varchar)||cast(b.id as varchar)) as key, f.id as id, f.rol, h.id as idNivelLugar, h.nivel, g.id as rollugarid, g.lugar, a.persona_id as idPersona, cast(b.id as varchar) as sie, b.institucioneducativa, cast(b.institucioneducativa_tipo_id as varchar) as idIETipo, c.orgcurricula
                            from maestro_inscripcion a
                            inner join institucioneducativa b on a.institucioneducativa_id = b.id
                            inner join orgcurricular_tipo c on b.orgcurricular_tipo_id = c.id
                            inner join usuario d on d.persona_id = a.persona_id
                            left join usuario_rol e on e.usuario_id = d.id
                            left join rol_tipo f on e.rol_tipo_id = f.id
                            inner join lugar_tipo g on e.lugar_tipo_id = g.id
                            inner join lugar_nivel_tipo h on f.lugar_nivel_tipo_id = h.id                  
                            where 
                            a.persona_id = '".$id."' and
                            c.id = 1 and
                            a.gestion_tipo_id = '".$gestionReg."' and
                            (a.cargo_tipo_id = 1 or a.cargo_tipo_id = 12 ) and
                            a.es_vigente_administrativo is true and
                            b.institucioneducativa_tipo_id = 1 and                 
                            f.id = 9 and
                            e.esactivo is true

                            UNION--DOCENTE REGULAR
                            select '3' as peso, (cast(e.id as varchar)||cast(g.id as varchar)||cast(b.id as varchar)) as key, f.id as id, f.rol, h.id as idNivelLugar, h.nivel, g.id as rollugarid, g.lugar, a.persona_id as idPersona, cast(b.id as varchar) as sie, b.institucioneducativa, cast(b.institucioneducativa_tipo_id as varchar) as idIETipo, c.orgcurricula
                            from maestro_inscripcion a
                            inner join institucioneducativa b on a.institucioneducativa_id = b.id
                            inner join orgcurricular_tipo c on b.orgcurricular_tipo_id = c.id
                            inner join usuario d on d.persona_id = a.persona_id
                            left join usuario_rol e on e.usuario_id = d.id
                            left join rol_tipo f on e.rol_tipo_id = f.id
                            inner join lugar_tipo g on e.lugar_tipo_id = g.id
                            inner join lugar_nivel_tipo h on f.lugar_nivel_tipo_id = h.id                  
                            where 
                            a.persona_id = '".$id."' and
                            c.id = 1 and
                            a.gestion_tipo_id = '".$gestion."' and
                            (a.cargo_tipo_id = 0) and
                            a.es_vigente_administrativo is true and
                            b.institucioneducativa_tipo_id = 1 and
                            f.id = 2 and
                            e.esactivo is true
                            
                            UNION--DIRECTOR ALTERNATIVA
                            select '4' as peso, (cast(e.id as varchar)||cast(g.id as varchar)||cast(b.id as varchar)) as key, f.id as id, f.rol, h.id as idNivelLugar, h.nivel, g.id as rollugarid, g.lugar, a.persona_id as idPersona, cast(b.id as varchar) as sie, b.institucioneducativa, cast(b.institucioneducativa_tipo_id as varchar) as idIETipo, c.orgcurricula
                            from maestro_inscripcion a
                            inner join institucioneducativa b on a.institucioneducativa_id = b.id
                            inner join institucioneducativa_sucursal dd on dd.institucioneducativa_id = b.id
                            inner join orgcurricular_tipo c on b.orgcurricular_tipo_id = c.id
                            inner join usuario d on d.persona_id = a.persona_id
                            left join usuario_rol e on e.usuario_id = d.id
                            left join rol_tipo f on e.rol_tipo_id = f.id
                            inner join lugar_tipo g on e.lugar_tipo_id = g.id
                            inner join lugar_nivel_tipo h on f.lugar_nivel_tipo_id = h.id                  
                            where 
                            a.persona_id = '".$id."' and
                            c.id = 2 and
                            dd.gestion_tipo_id = '".$gestionAlt."' and
                            a.gestion_tipo_id = '".$gestionAlt."' and
                            dd.periodo_tipo_id = '".$periodoAlt."' and
                            (a.cargo_tipo_id = 1 or a.cargo_tipo_id = 12) and
                            a.es_vigente_administrativo is true and
                            b.institucioneducativa_tipo_id = 2 and
                            f.id = 9 and
                            e.esactivo is true

                            UNION--DOCENTE ALTERNATIVA
                            select '5' as peso, (cast(e.id as varchar)||cast(g.id as varchar)||cast(b.id as varchar)) as key, f.id as id, f.rol, h.id as idNivelLugar, h.nivel, g.id as rollugarid, g.lugar, a.persona_id as idPersona, cast(b.id as varchar) as sie, b.institucioneducativa, cast(b.institucioneducativa_tipo_id as varchar) as idIETipo, c.orgcurricula
                            from maestro_inscripcion a
                            inner join institucioneducativa b on a.institucioneducativa_id = b.id
                            inner join institucioneducativa_sucursal dd on dd.institucioneducativa_id = b.id
                            inner join orgcurricular_tipo c on b.orgcurricular_tipo_id = c.id
                            inner join usuario d on d.persona_id = a.persona_id
                            left join usuario_rol e on e.usuario_id = d.id
                            left join rol_tipo f on e.rol_tipo_id = f.id
                            inner join lugar_tipo g on e.lugar_tipo_id = g.id
                            inner join lugar_nivel_tipo h on f.lugar_nivel_tipo_id = h.id                  
                            where 
                            a.persona_id = '".$id."' and
                            c.id = 2 and
                            dd.gestion_tipo_id = '".$gestionAlt."' and
                            a.gestion_tipo_id = '".$gestionAlt."' and
                            dd.periodo_tipo_id = '".$periodoAlt."' and
                            ( a.cargo_tipo_id = 0 ) and
                            a.es_vigente_administrativo is true and
                            b.institucioneducativa_tipo_id = 2 and
                            f.id = 2 and
                            e.esactivo is true

                            UNION--DIRECTOR ESPECIAL
                            select '6' as peso, (cast(e.id as varchar)||cast(g.id as varchar)||cast(b.id as varchar)) as key, f.id as id, f.rol, h.id as idNivelLugar, h.nivel, g.id as rollugarid, g.lugar, a.persona_id as idPersona, cast(b.id as varchar) as sie, b.institucioneducativa, cast(b.institucioneducativa_tipo_id as varchar) as idIETipo, 'Especial' as orgcurricula
                            from maestro_inscripcion a
                            inner join institucioneducativa b on a.institucioneducativa_id = b.id
                            inner join orgcurricular_tipo c on b.orgcurricular_tipo_id = c.id
                            inner join usuario d on d.persona_id = a.persona_id
                            left join usuario_rol e on e.usuario_id = d.id
                            left join rol_tipo f on e.rol_tipo_id = f.id
                            inner join lugar_tipo g on e.lugar_tipo_id = g.id
                            inner join lugar_nivel_tipo h on f.lugar_nivel_tipo_id = h.id                  
                            where 
                            a.persona_id = '".$id."' and
                            c.id = 2 and
                            a.gestion_tipo_id = '".$gestionEsp."' and
                            (a.cargo_tipo_id = 1 or a.cargo_tipo_id = 12 ) and
                            a.es_vigente_administrativo is true and                  
                            f.id = 9 and
                            b.institucioneducativa_tipo_id = 4 and
                            e.esactivo is true

                            UNION--DOCENTE ESPECIAL
                            select '7' as peso, (cast(e.id as varchar)||cast(g.id as varchar)||cast(b.id as varchar)) as key, f.id as id, f.rol, h.id as idNivelLugar, h.nivel, g.id as rollugarid, g.lugar, a.persona_id as idPersona, cast(b.id as varchar) as sie, b.institucioneducativa, cast(b.institucioneducativa_tipo_id as varchar) as idIETipo, 'Especial' as orgcurricula
                            from maestro_inscripcion a
                            inner join institucioneducativa b on a.institucioneducativa_id = b.id
                            inner join orgcurricular_tipo c on b.orgcurricular_tipo_id = c.id
                            inner join usuario d on d.persona_id = a.persona_id
                            left join usuario_rol e on e.usuario_id = d.id
                            left join rol_tipo f on e.rol_tipo_id = f.id
                            inner join lugar_tipo g on e.lugar_tipo_id = g.id
                            inner join lugar_nivel_tipo h on f.lugar_nivel_tipo_id = h.id                  
                            where 
                            a.persona_id = '".$id."' and
                            c.id = 2 and
                            a.gestion_tipo_id = '".$gestion."' and
                            (a.cargo_tipo_id = 0) and
                            a.es_vigente_administrativo is true and    
                            f.id = 2 and
                            b.institucioneducativa_tipo_id = 4 and
                            e.esactivo is true

                            UNION--DOCENTE TECNICA
                            select '8' as peso, (cast(a.id as varchar)||cast(b.id as varchar)||cast(a.id as varchar)) as key,                            
                            f.id as id, f.rol, h.id as idNivelLugar, h.nivel, g.id as rollugarid, g.lugar, a.persona_id as idPersona, cast(b.id as varchar) as sie,
                             b.institucioneducativa, cast(b.institucioneducativa_tipo_id as varchar) as idIETipo, 'Técnica Tecnologica' as orgcurricula
                            from ttec_docente_persona a
                                inner join institucioneducativa b on a.institucioneducativa_id = b.id
                                inner join orgcurricular_tipo y on b.orgcurricular_tipo_id = y.id
                            
                                    inner join usuario d on d.persona_id = a.persona_id
                                                        left join usuario_rol e on e.usuario_id = d.id
                                                        left join rol_tipo f on e.rol_tipo_id = f.id
                                                        inner join lugar_tipo g on e.lugar_tipo_id = g.id
                                                        inner join lugar_nivel_tipo h on f.lugar_nivel_tipo_id = h.id  
                            
                            where 
                            a.persona_id in(".$id.") and
                            y.id = 3 and
                            a.gestion_tipo_id = '".$gestion."' and
                            f.id = 2 and
                            b.institucioneducativa_tipo_id in (7,8,9) and
                            e.esactivo is true and
                            a.es_vigente is true

                            UNION--DIRECTOR PERMANENTE
                            select '2' as peso, (cast(e.id as varchar)||cast(g.id as varchar)||cast(b.id as varchar)) as key, f.id as id, f.rol, h.id as idNivelLugar, h.nivel, g.id as rollugarid, g.lugar, a.persona_id as idPersona, cast(b.id as varchar) as sie, b.institucioneducativa, cast(b.institucioneducativa_tipo_id as varchar) as idIETipo, c.orgcurricula
                            from maestro_inscripcion a
                            inner join institucioneducativa b on a.institucioneducativa_id = b.id
                            inner join orgcurricular_tipo c on b.orgcurricular_tipo_id = c.id
                            inner join usuario d on d.persona_id = a.persona_id
                            left join usuario_rol e on e.usuario_id = d.id
                            left join rol_tipo f on e.rol_tipo_id = f.id
                            inner join lugar_tipo g on e.lugar_tipo_id = g.id
                            inner join lugar_nivel_tipo h on f.lugar_nivel_tipo_id = h.id                  
                            where 
                            a.persona_id = '".$id."' and
                            c.id = 2 and
                            a.gestion_tipo_id = '".$gestion."' and
                            (a.cargo_tipo_id = 1 or a.cargo_tipo_id = 12 ) and
                            a.es_vigente_administrativo is true and
                            b.institucioneducativa_tipo_id = 5 and                 
                            f.id = 9 and
                            e.esactivo is true
                ) z";
        if ($key == '-1') {
            $query = $query."
                    order by peso, idnivellugar, idIETipo
                ";
        } else{
            $query = $query."
                    where key = '".$key."'
                    order by peso, idnivellugar, idIETipo
                ";
        }

        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();               
        return $po;
	}
}