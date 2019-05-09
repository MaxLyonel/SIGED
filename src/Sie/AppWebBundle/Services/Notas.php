<?php

namespace Sie\AppWebBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Services\Funciones;
use Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Symfony\Component\HttpFoundation\JsonResponse;

class Notas{
	protected $em;
	protected $router;
    protected $session;
    protected $funciones;
    protected $estadosPermitidos = array();
    protected $estadosActualizables = array();

	public function __construct(EntityManager $entityManager) {
		$this->em = $entityManager;
        $this->session = new Session();
        $this->estadosPermitidos = array(4,5,11,26,28,37,55,56,57,58);
        // Estados que podran actualizar su estado
        $this->estadosActualizables = array(4,5,11,28);
	}

    public function setFunciones(Funciones $funciones)
    {
        $this->funciones = $funciones;
    }
    /*
     * __________________________________ REGULAR __________________________________________
     * /////////////////////////////////////////////////////////////////////////////////////
     */

    public function estadosPermitidos(){
        return $this->estadosPermitidos;
    }
    public function estadosActualizables(){
        return $this->estadosActualizables;
    }

    public function getTipoNota($sie,$gestion,$nivel,$grado,$discapacidad){
        /*if(($gestion <= 2012) or ($gestion == 2013 and $grado > 1 and !in_array($nivel, array(1,11,403))) or ($gestion == 2013 and $grado == (1 or 2 ) and in_array($nivel, array(1,11,403))) or (in_array($nivel, array(401,402)) and $gestion <= 2013 and $grado > 1)){
            $tipoNota = 'Trimestre';
        }else{
            $tipoNota = 'Bimestre';
        }*/
        if($gestion>2018 and $discapacidad == 2){
            $tipoNota = 'Etapa';
        }else{
            if($gestion <= 2012){
                $tipoNota = 'Trimestre';
            }else{
                if($gestion >= 2014){
                    $tipoNota = 'Bimestre';
                }else{
                    if($grado == 1 and !in_array($nivel, array(1,11,403))){
                        $tipoNota = 'Bimestre';
                    }else{
                        $tipoNota = 'Trimestre';
                    }
                }
            }
        }

        return $tipoNota;
    }
    /*
     * Variable opcion define si se trabajara sobre el operativo actual o regularizacion, R = regularizacion , A = actual,
     */
	public function regular($idInscripcion,$operativo){
        try {
            $tipoSubsistema = $this->session->get('tiposubsistema');

            $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

            $cursoEspecial = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array('institucioneducativaCurso'=>$inscripcion->getInstitucioneducativaCurso()->getId()));
            if($cursoEspecial){
                $discapacidad = $cursoEspecial->getEspecialAreaTipo()->getId();
                $tipoNota = $this->getTipoNota($sie,$gestion,$nivel,$grado,$discapacidad);
            }else{
                $tipoNota = $this->getTipoNota($sie,$gestion,$nivel,$grado,'');    
            }
            
            // Cantidad de notas faltantes
            $cantidadFaltantes = 0;

            //$tipoNota = $this->getTipoNota($sie,$gestion,$nivel,$grado);

            if($tipoNota == 'Trimestre'){

                $tn = array(30,27,6,31,28,7,32,29,8,9,10,11);
                //$tn = array(6,7,8,9,10,11);
                // TRIMESTRALES
                vuelve1:

                $asignaturas = $this->em->createQueryBuilder()
                            ->select('asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                            ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                            ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ea.estudianteInscripcion = ei.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                            ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                            ->groupBy('asit.id, asit.asignatura, ea.id')
                            ->orderBy('asit.id','ASC')
                            ->where('ei.id = :idInscripcion')
                            ->setParameter('idInscripcion',$idInscripcion)
                            ->getQuery()
                            ->getResult();

                $cursoOferta = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$inscripcion->getInstitucioneducativaCurso()->getId()));

                $arrayAsignaturasEstudiante = array();
                foreach ($asignaturas as $a) {
                    $arrayAsignaturasEstudiante[] = $a['asignaturaId'];
                }

                
                $nuevaArea = false;
                foreach ($cursoOferta as $co) {
                    if(!in_array($co->getAsignaturaTipo()->getId(), $arrayAsignaturasEstudiante)){

                        $query = $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');")->execute();
                        // Si no existe la asignatura, registramos la asignatura para el maestro
                        $newEstAsig = new EstudianteAsignatura();
                        $newEstAsig->setGestionTipo($this->em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                        $newEstAsig->setFechaRegistro(new \DateTime('now'));
                        $newEstAsig->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                        $newEstAsig->setInstitucioneducativaCursoOferta($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId()));
                        $this->em->persist($newEstAsig);
                        $this->em->flush();
                        $nuevaArea = true;
                    }
                }

                // Volvemos atras si se adiciono alguna nueva materia o asignatura
                if($nuevaArea == true){
                    goto vuelve1;
                }

                $notasArray = array();
                $cont = 0;
                foreach ($asignaturas as $a) {
                    $notasArray[$cont] = array('idAsignatura'=>$a['asignaturaId'],'asignatura'=>$a['asignatura']);
                    $asignaturasNotas = $this->em->createQueryBuilder()
                                        ->select('en.id as idNota, nt.id as idNotaTipo, nt.notaTipo, ea.id as idEstudianteAsignatura, en.notaCuantitativa, en.notaCualitativa, at.id')
                                        ->from('SieAppWebBundle:EstudianteNota','en')
                                        ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','en.estudianteAsignatura = ea.id')
                                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                                        ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','WITH','ieco.asignaturaTipo = at.id')
                                        ->innerJoin('SieAppWebBundle:NotaTipo','nt','with','en.notaTipo = nt.id')
                                        ->orderBy('nt.id','ASC')
                                        ->where('ea.id = :estAsigId')
                                        ->setParameter('estAsigId',$a['estAsigId'])
                                        ->getQuery()
                                        ->getResult();

                    if($nivel != 11 and $nivel != 1 and $nivel != 403){
                        for($i=0;$i<count($tn);$i++){
                            $existe = 'no';
                            foreach ($asignaturasNotas as $an) {
                                if($tn[$i] == $an['idNotaTipo']){
                                    $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$tn[$i],
                                                            'idEstudianteNota'=>$an['idNota'],
                                                            'nota'=>$an['notaCuantitativa'],
                                                            'notaCualitativa'=>$an['notaCualitativa'],
                                                            'idNotaTipo'=>$an['idNotaTipo'],
                                                            'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                                        );
                                    $existe = 'si';
                                    break;
                                }

                            }
                            if($existe == 'no'){
                                $cantidadFaltantes++;
                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$tn[$i],
                                                            'idEstudianteNota'=>'nuevo',
                                                            'nota'=>0,
                                                            'notaCualitativa'=>'',
                                                            'idNotaTipo'=>$tn[$i],
                                                            'idEstudianteAsignatura'=>$a['estAsigId']
                                                        );
                            }
                        }
                    }else{
                        for($i=6;$i<=8;$i++){
                            $existe = 'no';
                            foreach ($asignaturasNotas as $an) {
                                if($tn[$i] == $an['idNotaTipo']){
                                    $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$i,
                                                            'idEstudianteNota'=>$an['idNota'],
                                                            'nota'=>$an['notaCuantitativa'],
                                                            'notaCualitativa'=>$an['notaCualitativa'],
                                                            'idNotaTipo'=>$an['idNotaTipo'],
                                                            'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                                        );
                                    $existe = 'si';
                                    break;
                                }

                            }
                            if($existe == 'no'){
                                $cantidadFaltantes++;
                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$i,
                                                            'idEstudianteNota'=>'nuevo',
                                                            'nota'=>0,
                                                            'notaCualitativa'=>'',
                                                            'idNotaTipo'=>$i,
                                                            'idEstudianteAsignatura'=>$a['estAsigId']
                                                        );
                            }
                        }
                    }

                    $cont++;
                }

                $areas = array();
                $areas = $notasArray;
                //dump($areas);die;
                $tipo = 'Trimestre';
                //dump($areas);die;

            }else{
                // BIMESTRALES
                if($gestion == 2013 and $nivel != 11 and $grado == 1){
                    $conArea = true;
                }else{
                    $conArea = true;
                }
                vuelve:
                if($conArea == true){
                    // Obtenemos las areas o campos del estudiante
                    $asignaturas = $this->em->createQueryBuilder()
                                ->select('at.id, at.area, asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                                ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ea.estudianteInscripcion = ei.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                                ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                                ->innerJoin('SieAppWebBundle:AreaTipo','at','WITH','asit.areaTipo = at.id')
                                ->groupBy('at.id, at.area, asit.id, asit.asignatura, ea.id')
                                ->orderBy('at.id','ASC')
                                ->addOrderBy('asit.id','ASC')
                                ->where('ei.id = :idInscripcion')
                                ->setParameter('idInscripcion',$idInscripcion)
                                ->getQuery()
                                ->getResult();
                }else{
                    // Obtenemos las asigganturas sin areas o campos del estudiante
                    $asignaturas = $this->em->createQueryBuilder()
                                ->select('asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                                ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ea.estudianteInscripcion = ei.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                                ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                                ->groupBy('asit.id, asit.asignatura, ea.id')
                                ->orderBy('asit.id','ASC')
                                ->where('ei.id = :idInscripcion')
                                ->setParameter('idInscripcion',$idInscripcion)
                                ->getQuery()
                                ->getResult();
                }

                //dump($asignaturas);die;

                $cursoOferta = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$inscripcion->getInstitucioneducativaCurso()->getId()));

                $arrayAsignaturasEstudiante = array();
                foreach ($asignaturas as $a) {
                    $arrayAsignaturasEstudiante[] = $a['asignaturaId'];
                }

                
                $nuevaArea = false;
                foreach ($cursoOferta as $co) {
                    if(!in_array($co->getAsignaturaTipo()->getId(), $arrayAsignaturasEstudiante)){

                        $query = $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');")->execute();
                        // Si no existe la asignatura, registramos la asignatura para el maestro
                        $newEstAsig = new EstudianteAsignatura();
                        $newEstAsig->setGestionTipo($this->em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                        $newEstAsig->setFechaRegistro(new \DateTime('now'));
                        $newEstAsig->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                        $newEstAsig->setInstitucioneducativaCursoOferta($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId()));
                        $this->em->persist($newEstAsig);
                        $this->em->flush();
                        $nuevaArea = true;
                    }
                }

                // Volvemos atras si se adiciono alguna nueva materia o asignatura
                if($nuevaArea == true){
                    goto vuelve;
                }

                //dump($asignaturas);die;
                $notasArray = array();
                $cont = 0;

                switch ($operativo) {
                    case 0:
                        $inicio = 1;
                        $fin = 0;
                        break;
                    case 1:
                        $inicio = 1;
                        $fin = 1;
                        break;
                    case 5:
                        $inicio = 1;
                        $fin = 4;
                        break;
                    default:
                        $inicio = 1;
                        $fin = $operativo;
                        break;
                }


                if($this->session->get('ue_modular') == true and $nivel == 13){
                    $operativo = 4;
                    $fin = 4;
                }

                foreach ($asignaturas as $a) {
                    // Concatenamos la especialidad si se tiene registrado
                    $nombreAsignatura = $a['asignatura'];
                    if($a['asignaturaId'] == 1039){
                        $especialidad = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion'=>$idInscripcion));
                        if($especialidad){
                            $nombreAsignatura = $a['asignatura'].' '.$especialidad->getEspecialidadTecnicoHumanisticoTipo()->getEspecialidad();
                        }
                    }

                    if($conArea == true){
                        $notasArray[$cont] = array('areaId'=>$a['id'],'area'=>$a['area'],'idAsignatura'=>$a['asignaturaId'],'asignatura'=>$nombreAsignatura);
                    }else{
                        $notasArray[$cont] = array('idAsignatura'=>$a['asignaturaId'],'asignatura'=>$a['asignatura']);
                    }
                    $asignaturasNotas = $this->em->createQueryBuilder()
                                        ->select('en.id as idNota, nt.id as idNotaTipo, nt.notaTipo, ea.id as idEstudianteAsignatura, en.notaCuantitativa, en.notaCualitativa, at.id')
                                        ->from('SieAppWebBundle:EstudianteNota','en')
                                        ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','en.estudianteAsignatura = ea.id')
                                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                                        ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','WITH','ieco.asignaturaTipo = at.id')
                                        ->innerJoin('SieAppWebBundle:NotaTipo','nt','with','en.notaTipo = nt.id')
                                        ->orderBy('nt.id','ASC')
                                        ->where('ea.id = :estAsigId')
                                        ->setParameter('estAsigId',$a['estAsigId'])
                                        ->getQuery()
                                        ->getResult();

                    // EN LA GESTION 2019 INICIAL NO SE REGISTRARAN LAS NOTAS POR MATERIA
                    if (($gestion < 2019) or ($gestion >= 2019 and $nivel != 11) ) {
                        for($i=$inicio;$i<=$fin;$i++){
                            $existe = 'no';
                            foreach ($asignaturasNotas as $an) {
                                if($nivel != 11 and $nivel != 1 and $nivel != 403){
                                    $valorNota = $an['notaCuantitativa'];
                                    if($valorNota == 0){
                                        $cantidadFaltantes++;
                                    }
                                }else{
                                    $valorNota = $an['notaCualitativa'];
                                    if($valorNota == ""){
                                        $cantidadFaltantes++;
                                    }
                                }
                                if($i == $an['idNotaTipo']){
                                    $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$i,
                                                            'idEstudianteNota'=>$an['idNota'],
                                                            'nota'=>$valorNota,
                                                            'idNotaTipo'=>$an['idNotaTipo'],
                                                            'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                                        );
                                    $existe = 'si';
                                    break;
                                }

                            }
                            if($existe == 'no'){
                                $cantidadFaltantes++;
                                if($nivel != 11 and $nivel != 1 and $nivel != 403){
                                    $valorNota = '';
                                }else{
                                    $valorNota = '';
                                }
                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$i,
                                                            'idEstudianteNota'=>'nuevo',
                                                            'nota'=>$valorNota,
                                                            'idNotaTipo'=>$i,
                                                            'idEstudianteAsignatura'=>$a['estAsigId']
                                                        );
                            }
                        }

                        /**
                         * PROMEDIOS
                         */

                        if($nivel != 11 and $nivel != 1 and $nivel != 403 and $operativo >= 4){
                            // Para el promedio
                            foreach ($asignaturasNotas as $an) {
                                $existe = 'no';
                                if($an['idNotaTipo'] == 5){
                                    $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-5",
                                                                'idEstudianteNota'=>$an['idNota'],
                                                                'nota'=>$an['notaCuantitativa'],
                                                                'idNotaTipo'=>$an['idNotaTipo'],
                                                                'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                                            );
                                    $existe = 'si';
                                    break;
                                }
                            }
                            if($existe == 'no'){

                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-5",
                                                            'idEstudianteNota'=>'nuevo',
                                                            'nota'=>'',
                                                            'idNotaTipo'=>5,
                                                            'idEstudianteAsignatura'=>$a['estAsigId']
                                                        );
                            }
                        }
                    }
                    
                    $cont++;
                }
                $areas = array();
                /*if($conArea == true){
                    foreach ($notasArray as $n) {
                        $areas[$n['area']][] = $n;
                    }
                }else{*/
                    $areas = $notasArray;
                //}
                //dump($areas);die;
                $tipo = 'Bimestre';
            }

            //notas cualitativas
            $arrayCualitativas = array();

            $cualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion'=>$idInscripcion),array('notaTipo'=>'ASC'));

            if($nivel == 11 or $nivel == 1 or $nivel == 403){

                if ($gestion >= 2019) {
                    for ($i=$inicio; $i <=$fin; $i++) { 
                        $existe = false;
                        foreach ($cualitativas as $c) {
                            if($c->getNotaTipo()->getId() == $i){
                                $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                             'idEstudianteNotaCualitativa'=>$c->getId(),
                                                             'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                             'notaCualitativa'=>$c->getNotaCualitativa(),
                                                             'notaCuantitativa'=>0,
                                                             'notaTipo'=>$c->getNotaTipo()->getNotaTipo()
                                                            );
                                $existe = true;
                                if($c->getNotaCualitativa() == ""){
                                    $cantidadFaltantes++;
                                }
                            }
                        }
                        if($existe == false){
                            $cantidadFaltantes++;
                            $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                         'idEstudianteNotaCualitativa'=>'nuevo',
                                                         'idNotaTipo'=>$i,
                                                         'notaCualitativa'=>'',
                                                         'notaCuantitativa'=>'',
                                                         'notaTipo'=>$this->literal($i).' '.$tipoNota
                                                        );
                            $existe = true;
                        }
                    }
                }

                // VERIFICAMOS SI EL OPERATIVO ES MAYOR O IGUAL A 4 PARA CARGAR LA NOTA CUALITATIVA ANUAL
                if ($operativo >= 4) {
                    // Para inicial
                    $existe = false;
                    foreach ($cualitativas as $c) {
                        if($c->getNotaTipo()->getId() == 18){
                            $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                         'idEstudianteNotaCualitativa'=>$c->getId(),
                                                         'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                         'notaCualitativa'=>$c->getNotaCualitativa(),
                                                         'notaCuantitativa'=>$c->getNotaCuantitativa(),
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo()
                                                        );
                            $existe = true;
                            if($c->getNotaCualitativa() == ""){
                                $cantidadFaltantes++;
                            }
                        }
                    }
                    if($existe == false and $operativo >= 4){
                        $cantidadFaltantes++;
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>'nuevo',
                                                     'idNotaTipo'=>18,
                                                     'notaCualitativa'=>'',
                                                     'notaCuantitativa'=>'',
                                                     'notaTipo'=>$this->literal(18).' '.$tipoNota
                                                    );
                        $existe = true;
                    }
                }

            }else{
                // Para primaria y secundaria
                if($tipo == 'Bimestre'){
                  //$inicio = 1;
                  //$fin = 4;
                  $tipoNot = 'Bimestre';
                }else{
                  $inicio = 6;
                  $fin = 8;
                  $tipoNot = 'Trimestre';
                }

                // PARA LOS NIVELES DE PRIMARIA Y SECUNDARIA NO HAY NOTAS CUALITATIVAS
                // PARA ESTO PONEMOS EL BIMESTRE EN 0 Y NO ENTRE AL CICLO FOR
                if ($gestion >= 2019 and $nivel != 11) {
                    $fin = 0;
                }

                for($i=$inicio;$i<=$fin;$i++){
                    $existe = false;
                    foreach ($cualitativas as $c) {
                        if($c->getNotaTipo()->getId() == $i){
                            $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                         'idEstudianteNotaCualitativa'=>$c->getId(),
                                                         'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                         'notaCualitativa'=>$c->getNotaCualitativa(),
                                                         'notaCualitativa'=>$c->getNotaCuantitativa(),
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo()
                                                        );
                            $existe = true;
                            if($c->getNotaCualitativa() == ""){
                                $cantidadFaltantes++;
                            }
                        }
                    }
                    if($existe == false){
                        $cantidadFaltantes++;
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>'nuevo',
                                                     'idNotaTipo'=>$i,
                                                     'notaCualitativa'=>'',
                                                     'notaCuantitativa'=>'',
                                                     'notaTipo'=>$this->literal($i).' '.$tipoNot
                                                    );
                        $existe = true;
                    }
                }

                // VERIFICAMOS SI LA GESTION ES MAYOR O IGUAL A 2019
                // Y NIVEL PRIMARIA Y SECUNDARIA PARA AGREGARLE EL PROMEDIO ANUAL
                // if ($gestion >= 2019 and ($nivel == 13 or $nivel == 12) and $operativo >= 4) {     PROMEDIO ANUAL TAMBIEN PARA SECUNDARIA
                if ($gestion >= 2019 and $nivel == 12 and $operativo >= 4) {
                    $existe = false;
                    foreach ($cualitativas as $c) {
                        if($c->getNotaTipo()->getId() == 5){
                            $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                         'idEstudianteNotaCualitativa'=>$c->getId(),
                                                         'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                         'notaCualitativa'=>$c->getNotaCualitativa(),
                                                         'notaCuantitativa'=>$c->getNotaCuantitativa(),
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo()
                                                        );
                            $existe = true;
                            if($c->getNotaCualitativa() == ""){
                                $cantidadFaltantes++;
                            }
                        }
                    }
                    if($existe == false){
                        $cantidadFaltantes++;
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>'nuevo',
                                                     'idNotaTipo'=>5,
                                                     'notaCualitativa'=>'',
                                                     'notaCuantitativa'=>'',
                                                     'notaTipo'=>'Promedio anual'
                                                    );
                        $existe = true;
                    }
                }
            }

            $estadosPermitidos = $this->estadosPermitidos;

            return array(
                'cuantitativas'=>$areas,
                'cualitativas'=>$arrayCualitativas,
                'operativo'=>$operativo,
                'nivel'=>$nivel,
                'estadoMatricula'=>$inscripcion->getEstadomatriculaTipo()->getId(),
                'gestionActual'=>$this->session->get('currentyear'),
                'idInscripcion'=>$idInscripcion,
                'gestion'=>$gestion,
                'tipoNota'=>$tipo,
                'estadosPermitidos'=>$estadosPermitidos,
                'cantidadFaltantes'=>$cantidadFaltantes,
                'tipoSubsistema'=>$tipoSubsistema
            );
        } catch (Exception $e) {
            return null;
        }

	}

    public function postBachillerato($idInscripcion){

        $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

        $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
        $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

        $asignaturas = $this->em->createQueryBuilder()
                                ->select('at.id, at.area, asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                                ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ea.estudianteInscripcion = ei.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                                ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                                ->innerJoin('SieAppWebBundle:AreaTipo','at','WITH','asit.areaTipo = at.id')
                                ->groupBy('at.id, at.area, asit.id, asit.asignatura, ea.id')
                                ->orderBy('at.id','ASC')
                                ->addOrderBy('asit.id','ASC')
                                ->where('ei.id = :idInscripcion')
                                ->setParameter('idInscripcion',$idInscripcion)
                                ->getQuery()
                                ->getResult();

        $notasArray = array();

        $cont = 0;
        $cantidadFaltantes = 0;
        foreach ($asignaturas as $a) {
            // Concatenamos la especialidad si se tiene registrado
            $nombreAsignatura = $a['asignatura'];
            if($a['asignaturaId'] == 1039){
                $especialidad = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion'=>$idInscripcion));
                if($especialidad){
                    $nombreAsignatura = $a['asignatura'].' '.$especialidad->getEspecialidadTecnicoHumanisticoTipo()->getEspecialidad();
                }
            }

            
            $notasArray[$cont] = array('idAsignatura'=>$a['asignaturaId'],'asignatura'=>$a['asignatura']);
            
            $asignaturasNotas = $this->em->createQueryBuilder()
                                ->select('en.id as idNota, nt.id as idNotaTipo, nt.notaTipo, ea.id as idEstudianteAsignatura, en.notaCuantitativa, en.notaCualitativa, at.id')
                                ->from('SieAppWebBundle:EstudianteNota','en')
                                ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','en.estudianteAsignatura = ea.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                                ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','WITH','ieco.asignaturaTipo = at.id')
                                ->innerJoin('SieAppWebBundle:NotaTipo','nt','with','en.notaTipo = nt.id')
                                ->orderBy('nt.id','ASC')
                                ->where('ea.id = :estAsigId')
                                ->andWhere('nt.id = 5')
                                ->setParameter('estAsigId',$a['estAsigId'])
                                ->getQuery()
                                ->getResult();

            $existe = 'no';
            foreach ($asignaturasNotas as $an) {
                
                $valorNota = $an['notaCuantitativa'];
                if($valorNota == 0){
                    $cantidadFaltantes++;
                }
                
                    $notasArray[$cont]['notas'][] =   array(
                                            'id'=>$cont."-5",
                                            'idEstudianteNota'=>$an['idNota'],
                                            'nota'=>$valorNota,
                                            'idNotaTipo'=>$an['idNotaTipo'],
                                            'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                        );
                    $existe = 'si';
                    break;

            }
            if($existe == 'no'){
                $cantidadFaltantes++;
                
                $valorNota = '';
                
                $notasArray[$cont]['notas'][] =   array(
                                            'id'=>$cont."-5",
                                            'idEstudianteNota'=>'nuevo',
                                            'nota'=>$valorNota,
                                            'idNotaTipo'=>5,
                                            'idEstudianteAsignatura'=>$a['estAsigId']
                                        );
            }

            $cont++;
        }

        return array(
            'idInscripcion'=>$idInscripcion,
            'cuantitativas'=>$notasArray,
            'cantidadFaltantes'=>$cantidadFaltantes,
            'estadoMatricula'=>29 // ESTADO DE MATRICULA INSCRIPCION ES DIFERENTE AL ESTADO MATRICULA
        );
    }

    public function literal($num){
        switch ($num) {
            case '1': $lit = 'Primer'; break;
            case '2': $lit = 'Segundo'; break;
            case '3': $lit = 'Tercer'; break;
            case '4': $lit = 'Cuarto'; break;
            case '6': $lit = 'Primer'; break;
            case '7': $lit = 'Segundo'; break;
            case '8': $lit = 'Tercer'; break;
            case '18': $lit = 'Informe Final Inicial'; break;
            default; $lit = 0;break;
        }
        return $lit;
    }

    public function regularRegistro(Request $request){
        try {
            $this->em->getConnection()->beginTransaction();
            // DAtos de las notas cuantitativas
            $idEstudianteNota = $request->get('idEstudianteNota');
            $idNotaTipo = $request->get('idNotaTipo');
            $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
            $notas = $request->get('nota');

            // Datos de las notas cualitativas
            $idEstudianteNotaCualitativa = $request->get('idEstudianteNotaCualitativa');
            $idNotaTipoCualitativa = $request->get('idNotaTipoCualitativa');
            $notaCualitativa = $request->get('notaCualitativa');

            /* Datos de las notas cualitativas de primaria gestion 2013 */
            $idEstudianteNotaC = $request->get('idEstudianteNotaC');
            $idNotaTipoC = $request->get('idNotaTipoC');
            $idEstudianteAsignaturaC = $request->get('idEstudianteAsignaturaC');
            $notasC = $request->get('notasC');
            /*
            dump($idEstudianteNota);
            dump($idNotaTipo);
            dump($idEstudianteAsignatura);
            dump($notas);

            dump($idEstudianteNotaCualitativa);
            dump($idNotaTipoCualitativa);
            dump($notaCualitativa);
            /*
            dump($idEstudianteNotaC);
            dump($idNotaTipoC);
            dump($idEstudianteAsignaturaC);
            dump($notasC);
            die;*/

            $tipo = $request->get('tipoNota');
            $nivel = $request->get('nivel');
            $gestion = $request->get('gestion');
            $idInscripcion = $request->get('idInscripcion');
            /**
             * Para las notas BIMESTRALES
             */
            if($tipo == 'Bimestre'){
                // Registro y/o modificacion de notas
                for($i=0;$i<count($idEstudianteNota);$i++) {
                    if($idEstudianteNota[$i] == 'nuevo'){
                        if(($nivel != 11 and $notas[$i] != 0) or ($nivel == 11 and $notas[$i] != "")){
                            $query = $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();
                            $newNota = new EstudianteNota();
                            $newNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$i]));
                            $newNota->setEstudianteAsignatura($this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                            if($nivel == 11){
                                $newNota->setNotaCuantitativa(0);
                                $newNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                            }else{
                                $newNota->setNotaCuantitativa($notas[$i]);
                                $newNota->setNotaCualitativa('');
                            }
                            $newNota->setRecomendacion('');
                            $newNota->setUsuarioId($this->session->get('userId'));
                            $newNota->setFechaRegistro(new \DateTime('now'));
                            $newNota->setFechaModificacion(new \DateTime('now'));
                            $newNota->setObs('');
                            $this->em->persist($newNota);
                            $this->em->flush();

                            // Registro log
                            /*
                            $this->funciones->setLogTransaccion(
                                $newNota->getId(),
                                'estudiante_nota',
                                'C',
                                '',
                                $newNota,
                                '',
                                'SERVICIO',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );*/
                        }
                    }else{
                        $updateNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
                        // $regAnterior = clone $updateNota;
                        if($updateNota){
                            if($nivel == 11){
                                // Verificamos si la nota fue modificada o no para guardar los datos del usuario
                                if($updateNota->getNotaCualitativa() != mb_strtoupper($notas[$i],'utf-8') and $notas[$i] != ""){
                                    //$updateNota->setUsuarioId($this->session->get('userId'));
                                    $updateNota->setFechaModificacion(new \DateTime('now'));
                                    $updateNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                                }
                            }else{
                                // Verificamos si la nota fue modificada o no para guardar los datos del usuario
                                if($updateNota->getNotaCuantitativa() != $notas[$i] and $notas[$i] != 0 and $notas[$i] > 0 and $notas[$i] <= 100 ){
                                    //$updateNota->setUsuarioId($this->session->get('userId'));
                                    $updateNota->setFechaModificacion(new \DateTime('now'));
                                    $updateNota->setNotaCuantitativa($notas[$i]);
                                }
                            }
                            $this->em->flush();

                            // Registro log
                            /*
                            $this->funciones->setLogTransaccion(
                                $newNota->getId(),
                                'estudiante_nota',
                                'C',
                                '',
                                $newNota,
                                '',
                                'SERVICIO',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );*/
                        }
                    }
                }

                // Registro de notas cualitativas de incial primaria yo secundaria
                for($j=0;$j<count($idEstudianteNotaCualitativa);$j++){
                    if($idEstudianteNotaCualitativa[$j] == 'nuevo'){
                        if($notaCualitativa[$j] != ""){
                            $query = $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota_cualitativa');")->execute();
                            $newCualitativa = new EstudianteNotaCualitativa();
                            $newCualitativa->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipoCualitativa[$j]));
                            $newCualitativa->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));

                            if ($gestion >= 2019 and ($nivel == 12 or $nivel == 13)) {
                                $newCualitativa->setNotaCuantitativa($notaCualitativa[$j]);
                                $newCualitativa->setNotaCualitativa('');
                            } else {
                                $newCualitativa->setNotaCuantitativa(0);
                                $newCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                            }
                            
                            $newCualitativa->setRecomendacion('');
                            $newCualitativa->setUsuarioId($this->session->get('userId'));
                            $newCualitativa->setFechaRegistro(new \DateTime('now'));
                            $newCualitativa->setFechaModificacion(new \DateTime('now'));
                            $newCualitativa->setObs('');
                            $this->em->persist($newCualitativa);
                            $this->em->flush();
                        }
                    }else{
                        $updateCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaCualitativa[$j]);
                        if($updateCualitativa){
                            // Verificamos si la nota fue modificada para guardar los datos del usuario que lo modifico
                            if($updateCualitativa->getNotaCualitativa() != mb_strtoupper($notaCualitativa[$j],'utf-8')){
                                $updateCualitativa->setUsuarioId($this->session->get('userId'));
                                $updateCualitativa->setFechaModificacion(new \DateTime('now'));
                            }

                            if ($gestion >= 2019 and ($nivel == 12 or $nivel == 13)) {
                                $updateCualitativa->setNotaCuantitativa($notaCualitativa[$j]);
                                $updateCualitativa->setNotaCualitativa('');
                            } else {
                                $updateCualitativa->setNotaCuantitativa(0);
                                $updateCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                            }

                            $this->em->persist($updateCualitativa);
                            $this->em->flush();
                        }
                    }
                }
            }
            /**
             * PAra las notas TRIMESTRALES
             */
            if($tipo == 'Trimestre'){
                $gestion = $request->get('gestion');
                for($i=0;$i<count($idEstudianteNota);$i++) {
                    if($idEstudianteNota[$i] == 'nuevo'){
                        if($notas[$i]!=0 ){
                            $newNota = new EstudianteNota();
                            $newNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$i]));
                            $newNota->setEstudianteAsignatura($this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                            if($nivel == 11 or $nivel == 1 or $nivel == 403){
                                $newNota->setNotaCuantitativa(0);
                                $newNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                            }else{
                                $newNota->setNotaCuantitativa($notas[$i]);
                                $newNota->setNotaCualitativa('');
                            }
                            $newNota->setRecomendacion('');
                            $newNota->setUsuarioId($this->session->get('userId'));
                            $newNota->setFechaRegistro(new \DateTime('now'));
                            $newNota->setFechaModificacion(new \DateTime('now'));
                            $newNota->setObs('');
                            $this->em->persist($newNota);
                            $this->em->flush();
                        }
                    }else{
                        $updateNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
                        if($updateNota){
                            if($notas[$i]!=0){
                                if($nivel == 11 or $nivel == 1 or $nivel == 403){
                                    $updateNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                                }else{
                                    $updateNota->setNotaCuantitativa($notas[$i]);
                                }
                                $updateNota->setUsuarioId($this->session->get('userId'));
                                $updateNota->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($updateNota);
                                $this->em->flush();
                            }else{
                                // Eliminar la nota de reforzamiento o promedio final si es cero
                                $this->em->remove($updateNota);
                                $this->em->flush();
                            }
                        }
                    }
                }
                if($nivel == 12 or $nivel == 2 or $nivel == 3 or ($nivel == 13 and $gestion <= 2013)){
                    // Registro de notas cualitativas de primaria
                    for($j=0;$j<count($idEstudianteNotaC);$j++){
                        if($idEstudianteNotaC[$j] == 'nuevo'){
                            // Verificamos si la nota cuantitativa existe para hacer el update
                            $existeNotaCuantitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('notaTipo'=>$idNotaTipoC[$j],'estudianteAsignatura'=>$idEstudianteAsignaturaC[$j]));
                            if($existeNotaCuantitativa){
                                $existeNotaCuantitativa->setNotaCualitativa(mb_strtoupper($notasC[$j],'utf-8'));
                                $existeNotaCuantitativa->setUsuarioId($this->session->get('userId'));
                                $existeNotaCuantitativa->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($existeNotaCuantitativa);
                                $this->em->flush();
                            }

                        }else{
                            $updateNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNotaC[$j]);
                            if($updateNota){
                                $updateNota->setNotaCualitativa(mb_strtoupper($notasC[$j],'utf-8'));
                                $updateNota->setUsuarioId($this->session->get('userId'));
                                $updateNota->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($updateNota);
                                $this->em->flush();
                            }
                        }
                    }
                }else{
                    // Registro de notas cualitativas de incial y secundaria
                    for($j=0;$j<count($idEstudianteNotaCualitativa);$j++){
                        if($idEstudianteNotaCualitativa[$j] == 'nuevo'){
                            $newCualitativa = new EstudianteNotaCualitativa();
                            $newCualitativa->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipoCualitativa[$j]));
                            $newCualitativa->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                            $newCualitativa->setNotaCuantitativa(0);
                            $newCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                            $newCualitativa->setRecomendacion('');
                            $newCualitativa->setUsuarioId($this->session->get('userId'));
                            $newCualitativa->setFechaRegistro(new \DateTime('now'));
                            $newCualitativa->setFechaModificacion(new \DateTime('now'));
                            $newCualitativa->setObs('');
                            $this->em->persist($newCualitativa);
                            $this->em->flush();
                        }else{
                            $updateCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaCualitativa[$j]);
                            if($updateCualitativa){
                                $updateCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                                $updateCualitativa->setUsuarioId($this->session->get('userId'));
                                $updateCualitativa->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($updateCualitativa);
                                $this->em->flush();
                            }
                        }
                    }
                }
            }

            $this->em->getConnection()->commit();
            return new JsonResponse(array('msg'=>'ok'));
        } catch (Exception $e) {
            $this->em->getConnection()->rollback();
            return new JsonResponse(array('msg'=>'error'));
        }
    }

	public function actualizarEstadoMatricula($idInscripcion){
        try {
            $this->em->getConnection()->beginTransaction();
            $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();

            $gestionActual = $this->session->get('currentyear');

            $operativo = $this->funciones->obtenerOperativo($sie,$gestion);
            /*
            if($gestion <= 2012 or ($gestion == 2013 and $grado > 1 and $nivel != 11) or ($gestion == 2013 and $grado == (1 or 2 ) and $nivel == 11) ){
                $tipo = 't';
            }else{
                $tipo = 'b';
            }*/

            $tipo = $this->getTipoNota($sie,$gestion,$nivel,$grado,'');

            if($nivel == 11 or $nivel == 1 or $nivel == 403){

                if($operativo >= 4){
                    $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                    $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(5));
                    $this->em->persist($inscripcion);
                    $this->em->flush();
                }
            }else{
                $asignaturas = $this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findby(array('estudianteInscripcion'=>$idInscripcion));
                $arrayPromedios = array();
                foreach ($asignaturas as $a) {
                    // Notas Bimestrales
                    if($tipo == 'Bimestre'){
                        $notaPromedio = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>5));
                        if($notaPromedio){
                            /**
                             * GESTION 2018 NO SE CONSIDERAN LAS MATERIAS DE
                             * 1052 - CIENCIAS NATURALES: FISICA
                             * 1053 - CIENCIAS NATURALES: QUIMICA
                             * PARA LA PROMOCION DEL ESTUDIANTE
                             */
                            $codigoAsignatura = $a->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId();
                            if($gestion >= 2018){
                                if($codigoAsignatura != 1052 and $codigoAsignatura != 1053){
                                    $arrayPromedios[] = $notaPromedio->getNotaCuantitativa();    
                                }
                            }else{
                                $arrayPromedios[] = $notaPromedio->getNotaCuantitativa();
                            }
                        }
                    }
                    // Notas Trimestrales
                    if($tipo == 'Trimestre'){
                        $notaPromedioFinal = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>11));
                        if($notaPromedioFinal){
                            $arrayPromedios[] = $notaPromedioFinal->getNotaCuantitativa();
                        }else{
                            $notaPromedioAnual = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>9));
                            if($notaPromedioAnual){
                                $arrayPromedios[] = $notaPromedioAnual->getNotaCuantitativa();
                            }
                        }
                    }
                }

                if((sizeof($asignaturas) > 0 && count($asignaturas) == count($arrayPromedios)) or ($gestion < $gestionActual && sizeof($arrayPromedios) > 0 ) or ($gestion == 2018 && count($arrayPromedios) == (count($asignaturas) - 2)) ){
                    $estadoAnterior = $inscripcion->getEstadomatriculaTipo()->getId();
                    $nuevoEstado = 5; // Aprobado
                    if($tipo == 'Bimestre'){
                        foreach ($arrayPromedios as $ap) {
                            if($ap < 51){
                                $nuevoEstado = 11;
                                break;
                            }
                        }
                    }
                    if($tipo == 'Trimestre'){
                        foreach ($arrayPromedios as $ap) {
                            if($ap < 36){
                                $nuevoEstado = 11;
                                break;
                            }
                        }
                    }

                    if(count($asignaturas) != count($arrayPromedios)){
                        if($gestion >= 2018){

                        }else{
                            $nuevoEstado = 11;
                        }
                    }

                    $tipoUE = $this->funciones->getTipoUE($sie,$gestion);

                    /**
                     * TipoUE = 3  ues modulares
                     */
                    if($tipoUE['id'] != 3 or ($tipoUE['id'] == 3 and $nivel<13) or ($tipoUE['id'] == 3 and $nivel == 13 and $operativo >= 4 )){

                        if(in_array($estadoAnterior,$this->estadosActualizables)){

                            // PARA PRIMARIA A PARTIR DE LA GESTION 2019 PARA LA PROMOCION SE EVALUARA EL PROMEDIO ANUAL GENERAL
                            if ($gestion >= 2019 and $nivel == 12) {
                                $promedioGeneral = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array(
                                    'estudianteInscripcion'=>$inscripcion->getId(),
                                    'notaTipo'=>5
                                ));
                                if($promedioGeneral){
                                    if ($promedioGeneral->getNotaCuantitativa() < 51) {
                                        $nuevoEstado = 28; // ESTADO RETENIDO 28 - REEMPLAZA ESTADO REPROBADO 11
                                    } else {
                                        $nuevoEstado = 5;
                                    }

                                    $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($nuevoEstado));
                                    $this->em->persist($inscripcion);
                                    $this->em->flush();
                                    
                                }
                            }else{
                                $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($nuevoEstado));
                                $this->em->persist($inscripcion);
                                $this->em->flush();
                            }
                        }
                    }
                }
            }
            $this->em->getConnection()->commit();
            return new JsonResponse(array('msg'=>'ok'));
        } catch (Exception $e) {
            $this->em->getConnection()->rollback();
            return new JsonResponse(array('msg'=>'error'));
        }
	}


    /*
     * ______________________ CALCULO DE PROMEDIOS TRIMESTRALES ____________________________
     * /////////////////////////////////////////////////////////////////////////////////////
     */

    public function calcularPromedioBimestral($idEstudianteAsignatura){
        try {
            $notas = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura'=>$idEstudianteAsignatura));
            $PB = null;
            $SB = null;
            $TB = null;
            $CB = null;
            $P = null;

            foreach ($notas as $n) {
                if($n->getNotaTipo()->getId() == 1){ $PB = $n; }
                if($n->getNotaTipo()->getId() == 2){ $SB = $n; }
                if($n->getNotaTipo()->getId() == 3){ $TB = $n; }
                if($n->getNotaTipo()->getId() == 4){ $CB = $n; }
                if($n->getNotaTipo()->getId() == 5){ $P = $n; }
            }

            if($PB != null and $SB != null and $TB != null and $CB != null){
                $promedio = round(($PB->getNotaCuantitativa() + $SB->getNotaCuantitativa() + $TB->getNotaCuantitativa() + $CB->getNotaCuantitativa())/4);
                if($P){
                    $P->setNotaCuantitativa($promedio);
                    $this->em->persist($P);
                    $this->em->flush();
                }else{
                    $P = $this->registrarNota(5,$idEstudianteAsignatura,$promedio,'');
                }
            }

            return true;

        } catch (Exception $e) {
            
        }
    }

    public function calcularPromediosTrimestrales($idEstudianteAsignatura){
        try {
            $notas = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura'=>$idEstudianteAsignatura));
            $suma1 = 0;
            $suma2 = 0;
            $suma3 = 0;

            $PTP = null;
            $STP = null;
            $TTP = null;
            $promedioAnual = null;
            $reforzamiento = null;
            $promedioFinal = null;

            foreach ($notas as $n) {
                if($n->getNotaTipo()->getId() == 30 or $n->getNotaTipo()->getId() == 27){
                    $suma1 = $suma1 + $n->getNotaCuantitativa();
                }
                if($n->getNotaTipo()->getId() == 31 or $n->getNotaTipo()->getId() == 28){
                    $suma2 = $suma2 + $n->getNotaCuantitativa();
                }
                if($n->getNotaTipo()->getId() == 32 or $n->getNotaTipo()->getId() == 29){
                    $suma3 = $suma3 + $n->getNotaCuantitativa();
                }
                if($n->getNotaTipo()->getId() == 6){ $PTP = $n; }
                if($n->getNotaTipo()->getId() == 7){ $STP = $n; }
                if($n->getNotaTipo()->getId() == 8){ $TTP = $n; }
                if($n->getNotaTipo()->getId() == 9){ $promedioAnual = $n; }
                if($n->getNotaTipo()->getId() == 10){ $reforzamiento = $n; }
                if($n->getNotaTipo()->getId() == 11){ $promedioFinal = $n; }
            }

            // Verificamos si existe el promedio de primer trimestre, sino lo registramos
            if($PTP){
                if($suma1 > 0){$PTP->setNotaCuantitativa($suma1);}
            }else{
                $PTP = $this->registrarNota(6,$idEstudianteAsignatura,$suma1,'');
            }
            // Verificamos si existe el promedio de segundo trimestre, sino lo registramos
            if($STP){
                if($suma2 > 0){$STP->setNotaCuantitativa($suma2);}
            }else{
                $STP = $this->registrarNota(7,$idEstudianteAsignatura,$suma2,'');
            }
            // Verificamos si existe el promedio de tercer tremestre, sino lo registramos
            if($TTP){
                if($suma3 > 0){$TTP->setNotaCuantitativa($suma3);}
            }else{
                $TTP = $this->registrarNota(8,$idEstudianteAsignatura,$suma3,'');
            }

            // Verificamos si existe la nota de promedio anual
            $notaPromedioAnual = round(($PTP->getNotaCuantitativa() + $STP->getNotaCuantitativa() + $TTP->getNotaCuantitativa())/3);
            if($promedioAnual){
                $promedioAnual->setNotaCuantitativa($notaPromedioAnual);
            }else{
                $promedioAnual = $this->registrarNota(9,$idEstudianteAsignatura,$notaPromedioAnual,'');
            }

            // Verificamos si existe la nota de reforzamiento
            if($reforzamiento){
                $notaPromedioFinal = round(($promedioAnual->getNotaCuantitativa() + $reforzamiento->getNotaCuantitativa())/2);
                // Verificamos si existe la nota de promedio final
                if($promedioFinal){
                    $promedioFinal->setNotaCuantitativa($notaPromedioFinal);
                }else{
                    $promedioFinal = $this->registrarNota(11,$idEstudianteAsignatura,$notaPromedioFinal,'');
                }
            }

            // Verificamos si el promedio anual es mayor o igual a 36
            // Si es nota de aprobacion se eliminan el reforzamiento y el promedio final
            //dump($reforzamiento);die;
            if($promedioAnual->getNotaCuantitativa() >= 36){
                if($reforzamiento){ $this->em->remove($reforzamiento); }
                if($promedioFinal){ $this->em->remove($promedioFinal); }
            }

            $this->em->flush();

            return true;
        } catch (Exception $e) {
            
        }
    }
    /**
    * REGISTRO Y MODIFICACION DE NOTAS CUANTITATIVAS E INICIAL(estudiante_nota) 
    */
    public function registrarNota($idNotaTipo, $idEstudianteAsignatura,$notaCuantitativa, $notaCualitativa){
        // Reiniciamos la secuencia de la tabla notas
        $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();        
        $newNota = new EstudianteNota();
        $newNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo));
        $newNota->setEstudianteAsignatura($this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura));
        $newNota->setNotaCuantitativa($notaCuantitativa);
        $newNota->setNotaCualitativa(mb_strtoupper($notaCualitativa, 'utf-8'));
        $newNota->setUsuarioId($this->session->get('userId'));
        $newNota->setFechaRegistro(new \DateTime('now'));
        $newNota->setFechaModificacion(new \DateTime('now'));
        $this->em->persist($newNota);
        $this->em->flush();

        return $newNota;
    }

    public function modificarNota($idEstudianteNota, $notaCuantitativa, $notaCualitativa){
        $datosNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota);
        if($datosNota){
            $datosNota->setNotaCuantitativa($notaCuantitativa);
            $datosNota->setNotaCualitativa(mb_strtoupper($notaCualitativa, 'utf-8'));
            $datosNota->setUsuarioId($this->session->get('userId'));
            $datosNota->setFechaModificacion(new \DateTime('now'));
            $this->em->persist($datosNota);
            $this->em->flush();
        }

        return $datosNota;
    }

    /**
    * REGISTRO Y MODIFICACION DE NOTAS CUALITATIVAS (estudiante_nota_cualitativa) 
    */
    public function registrarNotaCualitativa($idNotaTipo, $idEstudianteInscripcion, $notaCualitativa){
        // Reiniciamos la secuencia de la tabla notas
        $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota_cualitativa');")->execute();        
        $newNotaCualitativa = new EstudianteNotaCualitativa();
        $newNotaCualitativa->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo));
        $newNotaCualitativa->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idEstudianteInscripcion));
        $newNotaCualitativa->setNotaCuantitativa(0);
        $newNotaCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa, 'utf-8'));
        $newNotaCualitativa->setUsuarioId($this->session->get('userId'));
        $newNotaCualitativa->setFechaRegistro(new \DateTime('now'));
        $newNotaCualitativa->setFechaModificacion(new \DateTime('now'));
        $newNotaCualitativa->setObs('');
        $this->em->persist($newNotaCualitativa);
        $this->em->flush();

        return $newNotaCualitativa;
    }

    public function modificarNotaCualitativa($idEstudianteNotaCualitativa, $notaCualitativa){
        $datosNotaCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaCualitativa);
        if($datosNotaCualitativa){
            $datosNotaCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa, 'utf-8'));
            $datosNotaCualitativa->setUsuarioId($this->session->get('userId'));
            $datosNotaCualitativa->setFechaModificacion(new \DateTime('now'));
            $this->em->persist($datosNotaCualitativa);
            $this->em->flush();
        }

        return $datosNotaCualitativa;
    }



    /*
     * __________________________________ ESPECIAL __________________________________________
     * /////////////////////////////////////////////////////////////////////////////////////
     */

    public function especial_cualitativo($idInscripcion,$operativo){
        try {
            //dump($idInscripcion);die;
            $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
            $discapacidad = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBY(array('institucioneducativaCurso'=>$inscripcion->getInstitucioneducativaCurso()->getId()))->getEspecialAreaTipo()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            $gestionActual = $this->session->get('currentyear');

            vuelve:

            $asignaturas = $this->em->createQueryBuilder()
                                ->select('at.id, at.area, asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                                ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ea.estudianteInscripcion = ei.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                                ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                                ->innerJoin('SieAppWebBundle:AreaTipo','at','WITH','asit.areaTipo = at.id')
                                ->groupBy('at.id, at.area, asit.id, asit.asignatura, ea.id')
                                ->orderBy('at.id','ASC')
                                ->addOrderBy('asit.id','ASC')
                                ->where('ei.id = :idInscripcion')
                                ->setParameter('idInscripcion',$idInscripcion)
                                ->getQuery()
                                ->getResult();

            $cursoOferta = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$inscripcion->getInstitucioneducativaCurso()->getId()));

            $arrayAsignaturasEstudiante = array();
            foreach ($asignaturas as $a) {
                $arrayAsignaturasEstudiante[] = $a['asignaturaId'];
            }

            $query = $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');")->execute();
            $nuevaArea = false;
            foreach ($cursoOferta as $co) {
                if(!in_array($co->getAsignaturaTipo()->getId(), $arrayAsignaturasEstudiante)){
                    // Si no existe la asignatura, registramos la asignatura para el maestro
                    $newEstAsig = new EstudianteAsignatura();
                    $newEstAsig->setGestionTipo($this->em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                    $newEstAsig->setFechaRegistro(new \DateTime('now'));
                    $newEstAsig->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                    $newEstAsig->setInstitucioneducativaCursoOferta($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId()));
              
                  
                    $this->em->persist($newEstAsig);
                    $this->em->flush();
                    $nuevaArea = true;
                }
            }

            // Volvemos atras si se adiciono alguna nueva materia o asignatura
            if($nuevaArea == true){
                goto vuelve;
            }

            //dump($asignaturas);die;
            $notasArray = array();
            $cont = 0;

            $tipoNota = $this->getTipoNota($sie,$gestion,$nivel,$grado,$discapacidad);

            // if($tipoNota == 'Bimestre'){
            if($tipoNota == 'Bimestre' or $tipoNota == 'Etapa' ){
                switch ($operativo) {
                    case 0:
                        $inicio = 1;
                        $fin = 0;
                        break;
                    case 1:
                        $inicio = 1;
                        $fin = 1;
                        break;
                    case 5:
                        $inicio = 1;
                        $fin = 4;
                        break;
                    default:
                        $inicio = 1;
                        $fin = $operativo;
                        break;
                }
            }else{
                $inicio = 6;
                $fin = 8;
            }
            $fechaEtapasArray = array();

            foreach ($asignaturas as $a) {
                $notasArray[$cont] = array('areaId'=>$a['id'],'area'=>$a['area'],'idAsignatura'=>$a['asignaturaId'],'asignatura'=>$a['asignatura']);

                $asignaturasNotas = $this->em->createQueryBuilder()
                                    ->select('en.id as idNota, nt.id as idNotaTipo, nt.notaTipo, ea.id as idEstudianteAsignatura, en.notaCuantitativa, en.notaCualitativa, at.id')
                                    ->from('SieAppWebBundle:EstudianteNota','en')
                                    ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','en.estudianteAsignatura = ea.id')
                                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                                    ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','WITH','ieco.asignaturaTipo = at.id')
                                    ->innerJoin('SieAppWebBundle:NotaTipo','nt','with','en.notaTipo = nt.id')
                                    ->orderBy('nt.id','ASC')
                                    ->where('ea.id = :estAsigId')
                                    ->setParameter('estAsigId',$a['estAsigId'])
                                    ->getQuery()
                                    ->getResult();
                //dump($asignaturasNotas);die;
                for($i=$inicio;$i<=$fin;$i++){
                    $existe = 'no';
                    foreach ($asignaturasNotas as $an) {
						/*
						* 401 Independencia personal
						* 402 Independencia solcial
						* 403 Educacion inicial
						* 404 Educacion primaria
						* 405 Formacion tecnica
						* 406 Area de discapacidad auditiva y visual
						* 407 Area de discapacidad intelectual y multiple
						* 410 Servicios
						* 411 Programas
						*/
                        if($nivel != 401 and $nivel != 402 and $nivel != 403 and $nivel != 411){
                            $valorNota = $an['notaCuantitativa'];
                        }else{
                            $valorNota = $an['notaCualitativa'];
                        }
                        if($i == $an['idNotaTipo']){
                            if($gestion > 2018 and ($discapacidad == 2 or $discapacidad == 3 or $discapacidad == 5)){
                                $notasArray[$cont]['notas'][] =   array(
                                    'id'=>$cont."-".$i,
                                    'idEstudianteNota'=>$an['idNota'],
                                    'nota'=>json_decode($valorNota,true),
                                    'idNotaTipo'=>$an['idNotaTipo'],
                                    'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                );
                                //dump(json_decode($valorNota,true)['fecha_fin']);die;
                                if($discapacidad == 2){
                                    $fechaEtapasArray[$i] = array('fechaEtapa'=>json_decode($valorNota,true)['fechaEtapa']);
                                }
                                /* $fechaEtapasArray[$i] = array('fechainicio'=>json_decode($valorNota,true)['fechainicio'],
                                                                'fechafin'=>json_decode($valorNota,true)['fechafin']); */
                            }else{
                                $notasArray[$cont]['notas'][] =   array(
                                    'id'=>$cont."-".$i,
                                    'idEstudianteNota'=>$an['idNota'],
                                    'nota'=>$valorNota,
                                    'idNotaTipo'=>$an['idNotaTipo'],
                                    'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                );
                            }
                            
                            $existe = 'si';
                            break;
                        }

                    }
                    if($existe == 'no'){

                        $valorNota = '';

                        $notasArray[$cont]['notas'][] =   array(
                                                    'id'=>$cont."-".$i,
                                                    'idEstudianteNota'=>'nuevo',
                                                    'nota'=>$valorNota,
                                                    'idNotaTipo'=>$i,
                                                    'idEstudianteAsignatura'=>$a['estAsigId']
                                                );
                    }
                }
                if($nivel != 401 and $nivel != 402 and $nivel != 403 and $nivel != 411 and $operativo >= 4){
                    // Para el promedio
                    foreach ($asignaturasNotas as $an) {
                        $existe = 'no';
                        if($an['idNotaTipo'] == 5){
                            $notasArray[$cont]['notas'][] =   array(
                                                        'id'=>$cont."-5",
                                                        'idEstudianteNota'=>$an['idNota'],
                                                        'nota'=>$an['notaCuantitativa'],
                                                        'idNotaTipo'=>$an['idNotaTipo'],
                                                        'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                                    );
                            $existe = 'si';
                            break;
                        }
                    }
                    if($existe == 'no'){

                        $notasArray[$cont]['notas'][] =   array(
                                                    'id'=>$cont."-5",
                                                    'idEstudianteNota'=>'nuevo',
                                                    'nota'=>'',
                                                    'idNotaTipo'=>5,
                                                    'idEstudianteAsignatura'=>$a['estAsigId']
                                                );
                    }
                }
                $cont++;
            }
            $areas = array();
            $areas = $notasArray;
            //dump($areas);die;

            //notas cualitativas
            $arrayCualitativas = array();

            $cualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion'=>$idInscripcion),array('notaTipo'=>'ASC'));

            if($nivel == 401 or $nivel == 402 or $nivel == 403 or $nivel != 411){
                // Para inicial
                $existe = false;
                foreach ($cualitativas as $c) {
                    if($c->getNotaTipo()->getId() == 18){
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>$c->getId(),
                                                     'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                     'notaCualitativa'=>$c->getNotaCualitativa(),
                                                     'notaTipo'=>$c->getNotaTipo()->getNotaTipo()
                                                    );
                        $existe = true;
                    }
                }
                if($existe == false and $operativo >= 4){
                    $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                 'idEstudianteNotaCualitativa'=>'nuevo',
                                                 'idNotaTipo'=>18,
                                                 'notaCualitativa'=>'',
                                                 'notaTipo'=>$this->literal(18).' '.$tipoNota
                                                );
                    $existe = true;
                }


            }else{
                // Para primaria y secundaria

                for($i=$inicio;$i<=$fin;$i++){
                    $existe = false;
                    foreach ($cualitativas as $c) {
                        if($c->getNotaTipo()->getId() == $i){
                            $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                         'idEstudianteNotaCualitativa'=>$c->getId(),
                                                         'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                         'notaCualitativa'=>$c->getNotaCualitativa(),
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo()
                                                        );
                            $existe = true;
                        }
                    }
                    if($existe == false){
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>'nuevo',
                                                     'idNotaTipo'=>$i,
                                                     'notaCualitativa'=>'',
                                                     'notaTipo'=>$this->literal($i).' '.$tipoNota
                                                    );
                        $existe = true;
                    }
                }
            }

            $estadosPermitidos = array(0,4,5,70,71,72,73,47);
			// Tipos de notas
            if (($discapacidad == 2 or $discapacidad == 3 or $discapacidad == 5 or $discapacidad == 7) and $gestion > 2018){
                $tiposNotas = $this->em->getRepository('SieAppWebBundle:NotaEspecialTipo')->findById(array(1,2,3,5,7));
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(78,79,80));
            }else{
                $tiposNotas = $this->em->getRepository('SieAppWebBundle:NotaEspecialTipo')->findById(array(1,2,3,4));
                $estadosFinales = "";
            }
            
			$tiposNotasArray = array();
			foreach ($tiposNotas as $tn) {
				$tiposNotasArray[] = array('id'=>$tn->getId(),'nota'=>$tn->getNota(),'descripcion'=>$tn->getDescripcion());
            }
            
            //dump($areas);die;

            return array(
                'cuantitativas'     =>$areas,
                'cualitativas'      =>$arrayCualitativas,
                'operativo'         =>$operativo,
                'nivel'             =>$nivel,
                'estadoMatricula'   =>$inscripcion->getEstadomatriculaTipo()->getId(),
                'gestionActual'     =>$this->session->get('currentyear'),
                'idInscripcion'     =>$idInscripcion,
                'gestion'           =>$gestion,
                'tipoNota'          =>$tipoNota,
                'estadosPermitidos' =>$estadosPermitidos,
                'tiposNotas'        =>$tiposNotasArray,
                'estadosFinales'    =>$estadosFinales,
                'fechaEtapas'       =>$fechaEtapasArray
            );

        } catch (Exception $e) {
            return null;
        }
    }

    public function especialRegistro(Request $request, $discapacidad){
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        // Validar si existe la session del usuario
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        try {
            // dump($request, $discapacidad);die;
            $this->em->getConnection()->beginTransaction();
            // Datos de las notas cuantitativas
            $idEstudianteNota = $request->get('idEstudianteNota');
            $idNotaTipo = $request->get('idNotaTipo');
            $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
            $gestion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($request->get('idInscripcion'))->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            if($gestion > 2018 and ($discapacidad == 2 or $discapacidad == 3 or $discapacidad == 5)){
                /* $fechaInicio = $request->get('fechaInicio');
                $fechaFin = $request->get('fechaFin'); */
                if($discapacidad == 2 ){
                    $fechaEtapas = $request->get('fechaEtapas');
                }
                $contenidos = $request->get('contenidos');
                $resultados = $request->get('resultados');
                $indicador = $request->get('indicador');
                $estado = $request->get('estado');
                foreach ($contenidos as $i => $c){
                    //$datosNotas[] = array('contenidos'=>mb_strtoupper($c,'utf-8'),'resultados'=>mb_strtoupper($resultados[$i],'utf-8'),'idIndicador'=>$indicador[$i],'idEstado'=>$estado[$i],'fechainicio'=>$fechaInicio[$idNotaTipo[$i]],'fechafin'=>$fechaFin[$idNotaTipo[$i]]);
                    if($discapacidad == 2 ){
                        $datosNotas[] = array('contenidos'=>mb_strtoupper($c,'utf-8'),'resultados'=>mb_strtoupper($resultados[$i],'utf-8'),'idIndicador'=>$indicador[$i],'idEstado'=>$estado[$i],'fechaEtapa'=>$fechaEtapas[$idNotaTipo[$i]]);
                    }else{
                        $datosNotas[] = array('contenidos'=>mb_strtoupper($c,'utf-8'),'resultados'=>mb_strtoupper($resultados[$i],'utf-8'),'idIndicador'=>$indicador[$i],'idEstado'=>$estado[$i]);
                    }
                }
                //dump($datosNotas);die;
                $notas = $datosNotas;
            }else{
                $notas = $request->get('nota');
            }
            

            // Datos de las notas cualitativas
            $idEstudianteNotaCualitativa = $request->get('idEstudianteNotaCualitativa');
            $idNotaTipoCualitativa = $request->get('idNotaTipoCualitativa');
            $notaCualitativa = $request->get('notaCualitativa');

            /* Datos de las notas cualitativas de primaria gestion 2013 */
            $idEstudianteNotaC = $request->get('idEstudianteNotaC');
            $idNotaTipoC = $request->get('idNotaTipoC');
            $idEstudianteAsignaturaC = $request->get('idEstudianteAsignaturaC');
            $notasC = $request->get('notasC');

            $tipo = $request->get('tipoNota');
            $nivel = $request->get('nivel');
            $idInscripcion = $request->get('idInscripcion');
            $nivelesCualitativos = array(1,11,401,402,403,411);

            if($tipo == 'Bimestre' or $tipo == 'Etapa'){
                // Registro y/o modificacion de notas
                for($i=0;$i<count($idEstudianteNota);$i++) {
                    if($idEstudianteNota[$i] == 'nuevo'){
                        //if((!in_array($nivel, $nivelesCualitativos) and $notas[$i] != 0 ) or (in_array($nivel, $nivelesCualitativos) and $notas[$i] != "")){
                            $query = $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();
                            $newNota = new EstudianteNota();
                            $newNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$i]));
                            $newNota->setEstudianteAsignatura($this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                            if($nivel == 401 or $nivel == 402 or $nivel == 403 or $nivel == 411){
                                $newNota->setNotaCuantitativa(0);
                                if($gestion > 2018 and ($discapacidad == 2 or $discapacidad == 3 or $discapacidad == 5)){
                                    $newNota->setNotaCualitativa(json_encode($notas[$i]));
                                }else{
                                    $newNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                                }
                            }else{
                                $newNota->setNotaCuantitativa($notas[$i]);
                                $newNota->setNotaCualitativa('');
                            }
                            $newNota->setRecomendacion('');
                            $newNota->setUsuarioId($this->session->get('userId'));
                            $newNota->setFechaRegistro(new \DateTime('now'));
                            $newNota->setFechaModificacion(new \DateTime('now'));
                            $newNota->setObs('');
                            $this->em->persist($newNota);
                            $this->em->flush();

                            // Registro log
                            /*
                            $this->funciones->setLogTransaccion(
                                $newNota->getId(),
                                'estudiante_nota',
                                'C',
                                '',
                                $newNota,
                                '',
                                'SERVICIO',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );*/
                        //}
                    }else{
                        $updateNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
                        $regAnterior = clone $updateNota;
                        if($updateNota){
                            if($nivel == 401 or $nivel == 402 or $nivel == 403 or $nivel == 411){
                                if($gestion > 2018 and ($discapacidad == 2 or $discapacidad == 3 or $discapacidad == 5)){
                                    $updateNota->setNotaCualitativa(json_encode($notas[$i]));
                                }else{
                                    $updateNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                                }
                            }else{
                                $updateNota->setNotaCuantitativa($notas[$i]);
                            }
                            $updateNota->setUsuarioId($this->session->get('userId'));
                            $updateNota->setFechaModificacion(new \DateTime('now'));
                            $this->em->persist($updateNota);
                            $this->em->flush();

                            // Registro log
                            /*
                            $this->funciones->setLogTransaccion(
                                $newNota->getId(),
                                'estudiante_nota',
                                'C',
                                '',
                                $newNota,
                                '',
                                'SERVICIO',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );*/
                        }
                    }
                }

                // Registro de notas cualitativas de incial primaria y/o secundaria
                for($j=0;$j<count($idEstudianteNotaCualitativa);$j++){
                    if($idEstudianteNotaCualitativa[$j] == 'nuevo'){
                        if($notaCualitativa[$j] != ""){
                            $query = $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota_cualitativa');")->execute();
                            $newCualitativa = new EstudianteNotaCualitativa();
                            $newCualitativa->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipoCualitativa[$j]));
                            $newCualitativa->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                            $newCualitativa->setNotaCuantitativa(0);
                            $newCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                            $newCualitativa->setRecomendacion('');
                            $newCualitativa->setUsuarioId($this->session->get('userId'));
                            $newCualitativa->setFechaRegistro(new \DateTime('now'));
                            $newCualitativa->setFechaModificacion(new \DateTime('now'));
                            $newCualitativa->setObs('');
                            $this->em->persist($newCualitativa);
                            $this->em->flush();
                        }
                    }else{
                        $updateCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaCualitativa[$j]);
                        if($updateCualitativa){
                            $updateCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                            $updateCualitativa->setUsuarioId($this->session->get('userId'));
                            $updateCualitativa->setFechaModificacion(new \DateTime('now'));
                            $this->em->persist($updateCualitativa);
                            $this->em->flush();
                        }
                    }
                }
            }
            if($tipo == 'Trimestre'){
                for($i=0;$i<count($idEstudianteNota);$i++) {
                    if($idEstudianteNota[$i] == 'nuevo'){
                        if(($idNotaTipo[$i] <=9) or (($idNotaTipo[$i]==10 or $idNotaTipo[$i]==11) and $notas[$i]!=0 )){
                            $newNota = new EstudianteNota();
                            $newNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$i]));
                            $newNota->setEstudianteAsignatura($this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                            if($nivel == 11 or $nivel == 1 or $nivel == 401 or $nivel == 402 or $nivel == 403 or $nivel == 411){
                                $newNota->setNotaCuantitativa(0);
                                $newNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                            }else{
                                $newNota->setNotaCuantitativa($notas[$i]);
                                $newNota->setNotaCualitativa('');
                            }
                            $newNota->setRecomendacion('');
                            $newNota->setUsuarioId($this->session->get('userId'));
                            $newNota->setFechaRegistro(new \DateTime('now'));
                            $newNota->setFechaModificacion(new \DateTime('now'));
                            $newNota->setObs('');
                            $this->em->persist($newNota);
                            $this->em->flush();
                        }
                    }else{
                        $updateNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
                        if($updateNota){
                            if(($idNotaTipo[$i] <=9) or (($idNotaTipo[$i]==10 or $idNotaTipo[$i]==11) and $notas[$i]!=0)){
                                if($nivel == 11 or $nivel == 1 or $nivel == 401 or $nivel == 402 or $nivel == 403 or $nivel == 411){
                                    $updateNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                                }else{
                                    $updateNota->setNotaCuantitativa($notas[$i]);
                                }
                                $updateNota->setUsuarioId($this->session->get('userId'));
                                $updateNota->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($updateNota);
                                $this->em->flush();
                            }else{
                                // Eliminar la nota de reforzamiento o promedio final si es cero
                                $this->em->remove($updateNota);
                                $this->em->flush();
                            }
                        }
                    }
                }
                if($nivel == 12 or $nivel == 2 or $nivel == 404 or $nivel == 3 or ($nivel == 13 and $gestion <= 2013)){
                    // Registro de notas cualitativas de primaria
                    for($j=0;$j<count($idEstudianteNotaC);$j++){
                        if($idEstudianteNotaC[$j] == 'nuevo'){
                            // Verificamos si la nota cuantitativa existe para hacer el update
                            $existeNotaCuantitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('notaTipo'=>$idNotaTipoC[$j],'estudianteAsignatura'=>$idEstudianteAsignaturaC[$j]));
                            if($existeNotaCuantitativa){
                                $existeNotaCuantitativa->setNotaCualitativa(mb_strtoupper($notasC[$j],'utf-8'));
                                $existeNotaCuantitativa->setUsuarioId($this->session->get('userId'));
                                $existeNotaCuantitativa->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($existeNotaCuantitativa);
                                $this->em->flush();
                            }

                        }else{
                            $updateNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNotaC[$j]);
                            if($updateNota){
                                $updateNota->setNotaCualitativa(mb_strtoupper($notasC[$j],'utf-8'));
                                $updateNota->setUsuarioId($this->session->get('userId'));
                                $updateNota->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($updateNota);
                                $this->em->flush();
                            }
                        }
                    }
                }else{
                    // Registro de notas cualitativas de incial y secundaria
                    for($j=0;$j<count($idEstudianteNotaCualitativa);$j++){
                        if($idEstudianteNotaCualitativa[$j] == 'nuevo'){
                            $newCualitativa = new EstudianteNotaCualitativa();
                            $newCualitativa->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipoCualitativa[$j]));
                            $newCualitativa->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                            $newCualitativa->setNotaCuantitativa(0);
                            $newCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                            $newCualitativa->setRecomendacion('');
                            $newCualitativa->setUsuarioId($this->session->get('userId'));
                            $newCualitativa->setFechaRegistro(new \DateTime('now'));
                            $newCualitativa->setFechaModificacion(new \DateTime('now'));
                            $newCualitativa->setObs('');
                            $this->em->persist($newCualitativa);
                            $this->em->flush();
                        }else{
                            $updateCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaCualitativa[$j]);
                            if($updateCualitativa){
                                $updateCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                                $updateCualitativa->setUsuarioId($this->session->get('userId'));
                                $updateCualitativa->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($updateCualitativa);
                                $this->em->flush();
                            }
                        }
                    }
                }
            }
            // Datos del siguimiento
            if($gestion > 2018 and ($discapacidad == 7)){
                $seguimientoNota = new EstudianteNotaCualitativa();
                $seguimientoNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($request->get('tipoNota')));
                $seguimientoNota->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($request->get('idInscripcion')));
                $seguimientoNota->setNotaCuantitativa(0);
                $seguimientoNota->setNotaCualitativa($request->get('seguimiento'));
                $seguimientoNota->setRecomendacion('');
                $seguimientoNota->setUsuarioId($this->session->get('userId'));
                $seguimientoNota->setFechaRegistro(new \DateTime('now'));
                $seguimientoNota->setFechaModificacion(new \DateTime('now'));
                $seguimientoNota->setObs('');
                $this->em->persist($seguimientoNota);
                $this->em->flush();
            }
            $this->em->getConnection()->commit();
            return new JsonResponse(array('msg'=>'ok'));
        } catch (Exception $e) {
            $this->em->getConnection()->rollback();
            return new JsonResponse(array('msg'=>'error'));
        }
    }

    public function especial_seguimiento($idInscripcion,$operativo){
        try {
            $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
            $subarea = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBY(array('institucioneducativaCurso'=>$inscripcion->getInstitucioneducativaCurso()->getId()))->getEspecialAreaTipo()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            $gestionActual = $this->session->get('currentyear');

            $idNotaTipo = 0;
            $arrayCualitativas = array();
            // $tipoNota = $this->getTipoNota($sie,$gestion,$nivel,$grado,$subarea);
            $tipoNota = $this->em->getRepository('SieAppWebBundle:NotaTipo')->findOneBy(array('obs'=>'SEG'));
            if($tipoNota) {
                $idNotaTipo = $tipoNota->getId();
                $notaCualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$idNotaTipo));
                if($notaCualitativas) {
                    $arrayCualitativas = json_decode($notaCualitativas->getNotaCualitativa(), true);
                    $enota = 1;
                } else {
                    $enota = 0;
                }
            }
            
            if (($subarea == 7) and $gestion > 2018){
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(77,78));
            }else{
                $estadosFinales = array();
            }
            $estadosPermitidos = array(0,4,5,70,71,72,73,47);
            return array(
                'cuantitativas'     =>array(),
                'cualitativas'      =>$arrayCualitativas,
                'operativo'         =>$operativo,
                'nivel'             =>$nivel,
                'estadoMatricula'   =>$inscripcion->getEstadomatriculaTipo()->getId(),
                'gestionActual'     =>$this->session->get('currentyear'),
                'idInscripcion'     =>$idInscripcion,
                'gestion'           =>$gestion,
                'tipoNota'          =>$idNotaTipo,
                'estadosPermitidos' =>$estadosPermitidos,
                'estadosFinales'    =>$estadosFinales,
                'enota'             =>$enota
            );

        } catch (Exception $e) {
            return null;
        }
    }
}
