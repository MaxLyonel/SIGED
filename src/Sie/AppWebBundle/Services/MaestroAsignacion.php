<?php

namespace Sie\AppWebBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Services\Funciones;

class MaestroAsignacion{
	protected $em;
	protected $router;
    protected $session;
    protected $funciones;

	public function __construct(EntityManager $entityManager) {
		$this->em = $entityManager;
        $this->session = new Session();
	}

    public function setFunciones(Funciones $funciones)
    {
        $this->funciones = $funciones;
    }

	public function listar($idCursoOferta){

        $cursoOferta = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta);
        $curso = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($cursoOferta->getInsitucioneducativaCurso()->getId());

        $sie = $curso->getInstitucioneducativa()->getId();
        $gestion = $curso->getGestionTipo()->getId();
        $grado = $curso->getGradoTipo()->getId();

        $idAsignatura = $cursoOferta->getAsignaturatipo()->getId();

        $operativo = $this->funciones->obtenerOperativo($sie,$gestion);
        
		$arrayMaestros = array();
        if($gestion <= 2012 or ($gestion == 2013 and $grado > 1 and $nivel != 11) or ($gestion == 2013 and $grado == (1 or 2 ) and $nivel == 11) ){
            // trimestrales
            $inicio = 6;
            $fin = 8;
        }else{
            // Bimestrales
            $inicio = 0;

            if($operativo == 5){
                $fin = 4; //4;
            }else{
                $fin = $operativo;
            }
            if($gestion < $this->session->get('currentyear')){
                $fin = 4;
            }

        }
        $maestrosMateria = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('institucioneducativaCursoOferta'=>$idCursoOferta),array('id'=>'ASC'));
        for($i=$inicio;$i<=$fin;$i++){
            $existe = false;
            foreach ($maestrosMateria as $mm) {
                if($mm->getNotaTipo()->getId() == $i){
                    $arrayMaestros[] = array(
                                            'id'=>$mm->getId(),
                                            'idmi'=>$mm->getMaestroInscripcion()->getId(),
                                            'horas'=>$mm->getHorasMes(),
                                            'idNotaTipo'=>$mm->getNotaTipo()->getId(),
                                            'periodo'=>$this->literal($i),
                                            'idco'=>$idCursoOferta,
                                            'idAsignatura'=>$mm->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId());
                    $existe = true;
                    //break;
                }
            }
            if($existe == false){
                $arrayMaestros[] = array(
                                            'id'=>'nuevo',
                                            'idmi'=>'',
                                            'horas'=>'',
                                            'idNotaTipo'=>$i,
                                            'periodo'=>$this->literal($i),
                                            'idco'=>$idCursoOferta,
                                            'idAsignatura'=>$cursoOferta->getAsignaturaTipo()->getId());
            }
        }

        $maestros = $this->em->createQueryBuilder()
                        ->select('mi')
                        ->from('SieAppWebBundle:MaestroInscripcion','mi')
                        ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','WITH','mi.institucioneducativa = ie.id')
                        ->innerJoin('SieAppWebBundle:GestionTipo','gt','WITH','mi.gestionTipo = gt.id')
                        ->innerJoin('SieAppWebBundle:Persona','p','WITH','mi.persona = p.id')
                        ->innerJoin('SieAppWebBundle:CargoTipo','ct','with','mi.cargoTipo = ct.id')
                        ->innerJoin('SieAppWebBundle:RolTipo','rt','with','ct.rolTipo = rt.id')
                        ->where('ie.id = :sie')
                        ->andWhere('gt.id = :gestion')
                        ->andWhere('rt.id = 2')
                        ->andWhere('mi.esVigenteAdministrativo = :estado')
                        ->orderBy('p.paterno','ASC')
                        ->addOrderBy('p.materno','ASC')
                        ->addOrderBy('p.nombre','ASC')
                        ->setParameter('sie',$sie)
                        ->setParameter('gestion',$gestion)
                        ->setParameter('estado',true)
                        ->getQuery()
                        ->getResult();

        $maestrosJson = array();
        foreach ($maestros as $m) {
            $maestrosJson[] = array('id'=>$m->getId(),'persona'=>$m->getPersona()->getPaterno().' '.$m->getPersona()->getMaterno().' '.$m->getPersona()->getNombre());
        }

        // MAterias que tendran mas de un maestro
        $materiasMasMaestros = null;//array(1040,1045,1031,1036,1043,1044,1011,1015,1019);
        $vista = ($this->session->get('roluser') == 8)?2:1;
        
        return array(
            'maestrosCursoOferta'=>$arrayMaestros, 
            'maestros'=>$maestros,
            'ieco'=>$idCursoOferta,
            'operativo'=>$operativo,
            'gestion'=>$gestion,
            'gestionActual'=>$this->session->get('currentyear'),
            'maestrosJson'=>$maestrosJson,
            'fin'=>$fin,
            'idAsignatura'=>$idAsignatura,
            'materiasMasMaestros'=>$materiasMasMaestros,
            'vista'=>$vista // 0 : todo bloqueado, 1 : Completar faltantes , 2 : Todo abierto
        );
	}

    private function literal($num){
        switch ($num) {
            case '0': $lit = 'Inicio de gestión'; break;
            case '1': $lit = '1er Bimestre'; break;
            case '2': $lit = '2do Bimestre'; break;
            case '3': $lit = '3er Bimestre'; break;
            case '4': $lit = '4to Bimestre'; break;
            case '6': $lit = '1er Trimestre'; break;
            case '7': $lit = '2do Trimestre'; break;
            case '8': $lit = '3er Trimestre'; break;
            case '18': $lit = 'Informe Final Inicial'; break;
        }
        return $lit;
    }

    public function asignar(Request $request){
        try {
            //dump($request);die;
            $iecom = $request->get('iecom');
            $ieco = $request->get('ieco');
            $idmi = $request->get('idmi');
            $idnt = $request->get('idnt');
            $horas = $request->get('horas');

            $cursoOfertaId = $request->get('cursoOfertaId');
            //dump($iecom);
            //dump($ieco);
            //dump($idmi);
            //dump($idnt);
            //dump($horas);
            //die;
            $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta_maestro');")->execute();
            for($i=0;$i<count($iecom);$i++){
                $regAnt = null;
                if($horas[$i] == ''){
                    $horasNum = 0;
                }else{
                    $horasNum = $horas[$i];
                }
                if($iecom[$i] == 'nuevo' and $idmi[$i] != ''){
                    $newCOM = new InstitucioneducativaCursoOfertaMaestro();
                    $newCOM->setInstitucioneducativaCursoOferta($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($ieco[$i]));
                    $newCOM->setMaestroInscripcion($this->em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idmi[$i]));
                    $newCOM->setHorasMes($horasNum);
                    $newCOM->setFechaRegistro(new \DateTime('now'));
                    $newCOM->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idnt[$i]));
                    $newCOM->setEsVigenteMaestro('t');
                    $this->em->persist($newCOM);
                    $this->em->flush();

                    // Registro del log
                    /*
                    $this->funciones->setLogTransaccion(
                        $newCOM->getId(),
                        'institucioneducativa_curso_oferta_maestro',
                        'C',
                        '',
                        $newCOM,
                        '',
                        'SIGED',
                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                    );*/
                }else{
                    if($idmi[$i] != ''){
                        $updateCOM = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->find($iecom[$i]);
                        $preview = clone $updateCOM;
                        
                        $updateCOM->setMaestroInscripcion($this->em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idmi[$i]));
                        $updateCOM->setHorasMes($horasNum);
                        $updateCOM->setFechaModificacion(new \DateTime('now'));
                        $updateCOM->setEsVigenteMaestro('t');

                        //dump($preview);
                        //dump($updateCOM);die;
                        /*
                        $this->funciones->setLogTransaccion(
                            $updateCOM->getId(),
                            'institucioneducativa_curso_oferta_maestro',
                            'C',
                            '',
                            $updateCOM,
                            $preview,
                            'SIGED',
                            json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                        );*/

                        $this->em->flush();
                    }
                }
            }
            $response = new JsonResponse();

            return $response->setData(array('ieco'=>$cursoOfertaId));
        } catch (Exception $e) {
            $response = new JsonResponse();
            return $response->setData(array('ieco'=>0));
        }

        
    }
}