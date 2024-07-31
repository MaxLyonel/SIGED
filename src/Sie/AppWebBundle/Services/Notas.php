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
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativaDetalle;
use Sie\AppWebBundle\Entity\EspecialNivelTalento;
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
        $this->estadosPermitidos = array(4,5,7,11,26,28,37,55,56,57,58,68);
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

    public function getTipoNotaXX($sie,$gestion,$nivel,$grado){
        /*if(($gestion <= 2012) or ($gestion == 2013 and $grado > 1 and !in_array($nivel, array(1,11,403))) or ($gestion == 2013 and $grado == (1 or 2 ) and in_array($nivel, array(1,11,403))) or (in_array($nivel, array(401,402)) and $gestion <= 2013 and $grado > 1)){
            $tipoNota = 'Trimestre';
        }else{
            $tipoNota = 'Bimestre';
        }*/

        if($gestion <= 2012){
            $tipoNota = 'Trimestre';
        }else{
            if($gestion >= 2014){
                if($gestion <= 2019)
                    $tipoNota = 'Bimestre';
                else
                    $tipoNota = 'Trimestre';
            }else{
                if($grado == 1 and !in_array($nivel, array(1,11,403))){
                    $tipoNota = 'Bimestre';
                }else{
                    $tipoNota = 'Trimestre';
                }
            }
        }

        return $tipoNota;
    }

    public function getTipoNotaEsp($sie,$gestion,$nivel,$grado){

        if($gestion <= 2012 || $gestion > 2020){
            if($gestion >= 2021){
                $tipoNota = 'newTemplateDB';
            }else{
                $tipoNota = 'Trimestre';
            }
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

        return $tipoNota;
    }

    public function getTipoNota($sie,$gestion,$nivel,$grado,$discapacidad){
        /*if(($gestion <= 2012) or ($gestion == 2013 and $grado > 1 and !in_array($nivel, array(1,11,403))) or ($gestion == 2013 and $grado == (1 or 2 ) and in_array($nivel, array(1,11,403))) or (in_array($nivel, array(401,402)) and $gestion <= 2013 and $grado > 1)){
            $tipoNota = 'Trimestre';
        }else{
            $tipoNota = 'Bimestre';
        }*/
        if($gestion>2019 and $discapacidad == 2){
            $tipoNota = 'Etapa';
        }else{
            if($gestion <= 2012 or $gestion >= 2020 ){
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
        //if($gestion>2019 and $discapacidad == 1){
         //   $tipoNota = 'Trimestre';
       //  }
        return $tipoNota;
    }

    /*
     * Variable opcion define si se trabajara sobre el operativo actual o regularizacion, R = regularizacion , A = actual,
     */
    public function regularRegularize($arrDataInfro){
        try {

            
            $operativoTrue = $arrDataInfro['operativo'];
            $operativo = $arrDataInfro['operativo'];
            //dump($operativo);die;
            $tipoSubsistema = $this->session->get('tiposubsistema');

            $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrDataInfro['idInscripcion']);

            $nivel = $arrDataInfro['nivelId'];
            $grado = $arrDataInfro['gradoId'];
            $sie = $arrDataInfro['sie'];
            $gestion = $arrDataInfro['gestion'];

            // Cantidad de notas faltantes
            $cantidadFaltantes = 0;
            $cantidadRegistrados = 0;

            $tipoNota = $this->getTipoNota($sie,$gestion,$nivel,$grado,'');

            if($tipoNota == 'Trimestre'){
                
                if($gestion == 2020){
                    
                    $operativo = ($nivel==11 or ($nivel ==12 && $grado == 1) or ($nivel ==403 && $grado == 1) or ($nivel ==404 && $grado == 1))?1:3;

                    //the new ini
                    $asignaturas = $this->em->createQueryBuilder()
                                ->select('at.id, at.area, asit.id as asignaturaId, asit.asignatura')
                                ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ieco.insitucioneducativaCurso = iec.id')                                    
                                ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                                ->innerJoin('SieAppWebBundle:AreaTipo','at','WITH','asit.areaTipo = at.id')
                                ->groupBy('at.id, at.area, asit.id, asit.asignatura')
                                ->orderBy('at.id','ASC')
                                ->addOrderBy('asit.id','ASC')
                                ->where('iec.id = :cursoId')
                                ->setParameter('cursoId',$arrDataInfro['idCourse'])
                                ->getQuery()
                                ->getResult();  

                    //dump($asignaturas);die;
                    $notasArray = array();
                    $cont = 0;


                    $inicio = 6;
                    $fin = 8;
              


                    if($this->session->get('ue_modular') == true and $nivel == 13){
                        $operativo = 3;
                        $fin = 3;
                        if($gestion == 2020){
                            $inicio = 6;
                            $fin = 8;
                        }                        
                    }

                    foreach ($asignaturas as $a) {
                     //dump($a);
                        // Concatenamos la especialidad si se tiene registrado
                        $nombreAsignatura = $a['asignatura'];


                        if(false){
                            $notasArray[$cont] = array('areaId'=>$a['id'],'area'=>$a['area'],'idAsignatura'=>$a['asignaturaId'],'asignatura'=>$nombreAsignatura);
                        }else{
                            $notasArray[$cont] = array('idAsignatura'=>$a['asignaturaId'],'asignatura'=>$a['asignatura']);
                        }

                        // EN LA GESTION 2019 INICIAL NO SE REGISTRARAN LAS NOTAS POR MATERIA
                        //if (($gestion < 2019) or ($gestion >= 2019 and $nivel != 11) ) {
                        if (($gestion < 2019) or ($gestion >= 2019 ) ) {
                            for($i=$inicio;$i<=$fin;$i++){
                                $existe = 'no';
                                if($existe == 'no'){
                                    $cantidadFaltantes++;
                                    if($nivel != 11 and $nivel != 1 and $nivel != 403){
                                        $valorNota = '';
                                    }else{
                                        $valorNota = '';
                                    }
                                    $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-".$i,
                                                                'asignaturaId'=>$a['asignaturaId'],
                                                                'idEstudianteNota'=>'nuevo',
                                                                'nota'=>$valorNota,
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>$i,
                                                                'idEstudianteAsignatura'=>'nuevo',
                                                                'bimestre'=>$this->literal($i)['titulo'],
                                                                'idFila'=>$a['asignaturaId'].''.$i
                                                            );
                                }
                            }

                            /**
                             * PROMEDIOS
                             */

                            if($nivel != 11 and $nivel != 1 and $nivel != 403 and $nivel != 404 and $operativo >= 3){
                                //new by krlos
                                 $idavg = ($gestion <= 2019)?"-5":"-9";
                                 $existe = 'no';
                             
                                if($existe == 'no'){

                                    $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont.$idavg ,
                                                                'asignaturaId'=>$a['asignaturaId'],
                                                                'idEstudianteNota'=>'nuevo',
                                                                'nota'=>'',
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>9,
                                                                'idEstudianteAsignatura'=>'nuevo',
                                                                'bimestre'=>'Promedio',
                                                                'idFila'=>$a['asignaturaId'].$idavg 
                                                            );
                                }
                                
                            }
                        }                                                                    

                                               
                        $cont++;
                    }
                    //die;
                    //die;
                    $areas = array();
                    /*if($conArea == true){
                        foreach ($notasArray as $n) {
                            $areas[$n['area']][] = $n;
                        }
                    }else{*/
                        $areas = $notasArray;
                    //}
                    //dump($areas);die;
                    $tipo = 'trimestre2020';

                    //the new end

                }else{

                    $tn = array(30,27,6,31,28,7,32,29,8,9,10,11);
                    // TRIMESTRALES
                    $asignaturas = $this->em->createQueryBuilder()
                            ->select('at.id, at.area, asit.id as asignaturaId, asit.asignatura')
                            ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ieco.insitucioneducativaCurso = iec.id')                                    
                            ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                            ->innerJoin('SieAppWebBundle:AreaTipo','at','WITH','asit.areaTipo = at.id')
                            ->groupBy('at.id, at.area, asit.id, asit.asignatura')
                            ->orderBy('at.id','ASC')
                            ->addOrderBy('asit.id','ASC')
                            ->where('iec.id = :cursoId')
                            ->setParameter('cursoId',$arrDataInfro['idCourse'])
                            ->getQuery()
                            ->getResult();   

                    $notasArray = array();
                    $cont = 0;
                    foreach ($asignaturas as $a) {
                        $notasArray[$cont] = array('idAsignatura'=>$a['asignaturaId'],'asignatura'=>$a['asignatura']);
            
                        if($nivel != 11 and $nivel != 1 and $nivel != 403){
                            for($i=0;$i<count($tn);$i++){
                                $existe = 'no';

                                if($existe == 'no'){
                                    $cantidadFaltantes++;
                                    $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-".$tn[$i],
                                                                'asignaturaId'=>$a['asignaturaId'],
                                                                'idEstudianteNota'=>'nuevo',
                                                                'nota'=>0,
                                                                'notaCualitativa'=>'',
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>$tn[$i],
                                                                'idEstudianteAsignatura'=>'nuevo',
                                                                'bimestre'=> $this->literal($tn[$i])['abrev'],
                                                                'idFila'=>$a['asignaturaId'].'-'.$tn[$i]
                                                            );
                                }
                            }
                        }else{
                            for($i=6;$i<=8;$i++){
                                $existe = 'no';

                                if($existe == 'no'){
                                    $cantidadFaltantes++;
                                    $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-".$i,
                                                                'asignaturaId'=>$a['asignaturaId'],
                                                                'idEstudianteNota'=>'nuevo',
                                                                'nota'=>0,
                                                                'notaCualitativa'=>'',
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>$i,
                                                                'idEstudianteAsignatura'=>'nuevo',
                                                                'bimestre'=>$this->literal($tn[$i]['abrev']),
                                                                'idFila'=>$a['asignaturaId'].'-'.$tn[$i]
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
                }

            }else{
                // BIMESTRALES
                if($gestion == 2013 and $nivel != 11 and $grado == 1){
                    $conArea = true;
                }else{
                    $conArea = true;
                }
             

                 //dump($asignaturas);die;

                $cursoOferta = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$arrDataInfro['idCourse']));

                $asignaturas = $this->em->createQueryBuilder()
                            ->select('at.id, at.area, asit.id as asignaturaId, asit.asignatura')
                            ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ieco.insitucioneducativaCurso = iec.id')                                    
                            ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                            ->innerJoin('SieAppWebBundle:AreaTipo','at','WITH','asit.areaTipo = at.id')
                            ->groupBy('at.id, at.area, asit.id, asit.asignatura')
                            ->orderBy('at.id','ASC')
                            ->addOrderBy('asit.id','ASC')
                            ->where('iec.id = :cursoId')
                            ->setParameter('cursoId',$arrDataInfro['idCourse'])
                            ->getQuery()
                            ->getResult();                  

                //dump($cursoOferta);die;
                $arrayAsignaturasEstudiante = array();


                //dump($asignaturas);die;
                $notasArray = array();
                $cont = 0;

                        $inicio = 1;
                        $fin = $operativo;
                        
                


                if($this->session->get('ue_modular') == true and $nivel == 13){
                    $operativo = 4;
                    $fin = 4;
                    if($gestion == 2020){
                        $inicio = 6;
                        $fin = 9;
                    }
                }

                foreach ($asignaturas as $a) {
                    // Concatenamos la especialidad si se tiene registrado
                    $nombreAsignatura = $a['asignatura'];

                    if($conArea == true){
                        $notasArray[$cont] = array('areaId'=>$a['id'],'area'=>$a['area'],'idAsignatura'=>$a['asignaturaId'],'asignatura'=>$nombreAsignatura);
                    }else{
                        $notasArray[$cont] = array('idAsignatura'=>$a['asignaturaId'],'asignatura'=>$a['asignatura']);
                    }


                    // EN LA GESTION 2019 INICIAL NO SE REGISTRARAN LAS NOTAS POR MATERIA
                    if (($gestion < 2019) or ($gestion >= 2019 and $nivel != 11) ) {
                        for($i=$inicio;$i<=$fin;$i++){
                            $existe = 'no';
                            if($existe == 'no'){
                                $cantidadFaltantes++;
                                if($nivel != 11 and $nivel != 1 and $nivel != 403){
                                    $valorNota = '';
                                }else{
                                    $valorNota = '';
                                }
                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$i,
                                                            'asignaturaId'=>$a['asignaturaId'],
                                                            'idEstudianteNota'=>'nuevo',
                                                            'nota'=>$valorNota,
                                                            'notaNueva'=>'',
                                                            'notaCualitativaNueva'=>'',
                                                            'idNotaTipo'=>$i,
                                                            'idEstudianteAsignatura'=>'nuevo',
                                                            'bimestre'=>$this->literal($i)['titulo'],
                                                            'idFila'=>$a['asignaturaId'].'-'.$i
                                                        );
                            }
                        }

                        /**
                         * PROMEDIOS
                         */

                        if($nivel != 11 and $nivel != 1 and $nivel != 403 and $operativo >= 4){
                            // Para el promedio
                            if($existe == 'no'){

                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-5",
                                                            'asignaturaId'=>$a['asignaturaId'],
                                                            'idEstudianteNota'=>'nuevo',
                                                            'nota'=>'',
                                                            'notaNueva'=>'',
                                                            'notaCualitativaNueva'=>'',
                                                            'idNotaTipo'=>5,
                                                            'idEstudianteAsignatura'=>'nuevo',
                                                            'bimestre'=>'Promedio',
                                                            'idFila'=>$a['asignaturaId'].'-5'
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

            /*
            * Recorremos el array generado de las notas y recuperamos los titulos de las notas, para la tabla
            */
            $titulos_notas = array();
            if(count($notasArray)>0 and isset($notasArray[0]['notas'])){
                $titulos = $notasArray[0]['notas'];
                for($i=0;$i<count($titulos);$i++){
                    $titulos_notas[] = array('titulo'=>$titulos[$i]['bimestre']);
                }
            }


            //notas cualitativas
            $arrayCualitativas = array();

            $cualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion'=>$arrDataInfro['idInscripcion']),array('notaTipo'=>'ASC'));

            //if($nivel == 11 or $nivel == 1 or $nivel == 403){
            if(($nivel == 11 or $nivel == 1 or $nivel == 403) or ($nivel ==12 and $grado == 1)){

                if ($gestion == 2019) {
                    for ($i=$inicio; $i <=$fin; $i++) { 
                        $existe = false;
                        foreach ($cualitativas as $c) {
                            if($c->getNotaTipo()->getId() == $i){
                                $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                             'idEstudianteNotaCualitativa'=>$c->getId(),
                                                             'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                             'notaCualitativa'=>$c->getNotaCualitativa(),
                                                             'notaCuantitativa'=>0,
                                                             'notaCuantitativaNueva'=>'',
                                                             'notaCualitativaNueva'=>'',
                                                             'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                             'idFila'=>$idInscripcion.'-'.$i
                                                            );
                                $existe = true;
                                if($c->getNotaCualitativa() == ""){
                                    $cantidadFaltantes++;
                                }else{
                                    $cantidadRegistrados++;
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
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$this->literal($i)['titulo'],
                                                         'idFila'=>$idInscripcion.'-'.$i
                                                        );
                            $existe = true;
                        }
                    }
                }

                // VERIFICAMOS SI EL OPERATIVO ES MAYOR O IGUAL A 4 PARA CARGAR LA NOTA CUALITATIVA ANUAL
                if (($operativo >= 4 and $gestion<2020 ) or ($gestion == 2020)) {
                    // Para inicial
                    $existe = false;
                    //dump($cualitativas);die;
                    foreach ($cualitativas as $c) {
                        if($c->getNotaTipo()->getId() == 18){
                            $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                         'idEstudianteNotaCualitativa'=>$c->getId(),
                                                         'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                         'notaCualitativa'=>$c->getNotaCualitativa(),
                                                         'notaCuantitativa'=>$c->getNotaCuantitativa(),
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                         'idFila'=>$idInscripcion.'-18'
                                                        );
                            $existe = true;
                            if($c->getNotaCualitativa() == ""){
                                // $cantidadFaltantes++;
                            }
                        }
                    }
                    $conditionAvg = ($nivel == 11 or ($nivel ==12 && $grado == 1) && $gestion == 2020)?$operativo >= 1:$operativo >= 3;
                    if($existe == false and $conditionAvg){
                        // $cantidadFaltantes++;
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>'nuevo',
                                                     'idNotaTipo'=>18,
                                                     'notaCualitativa'=>'',
                                                     'notaCuantitativa'=>'',
                                                     'notaCuantitativaNueva'=>'',
                                                     'notaCualitativaNueva'=>'',
                                                     'notaTipo'=>$this->literal(18)['titulo'],
                                                     'idFila'=>$idInscripcion.'-18'
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
                                                         'notaCuantitativa'=>$c->getNotaCuantitativa(),
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                         'idFila'=>$arrDataInfro['idInscripcion'].'-'.$i
                                                        );
                            $existe = true;
                            if($c->getNotaCualitativa() == ""){
                                $cantidadFaltantes++;
                            }
                        }
                    }
                    if($existe == false){
                        $cantidadFaltantes++;
                        $arrayCualitativas[] = array('idInscripcion'=>$arrDataInfro['idInscripcion'],
                                                     'idEstudianteNotaCualitativa'=>'nuevo',
                                                     'idNotaTipo'=>$i,
                                                     'notaCualitativa'=>'',
                                                     'notaCuantitativa'=>'',
                                                     'notaCuantitativaNueva'=>'',
                                                     'notaCualitativaNueva'=>'',
                                                     'notaTipo'=>$this->literal($i)['titulo'],
                                                     'idFila'=>$arrDataInfro['idInscripcion'].'-'.$i
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
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                         'idFila'=>$idInscripcion.'-5'
                                                        );
                            $existe = true;
                            if($c->getNotaCualitativa() == ""){
                                // $cantidadFaltantes++;
                            }
                        }
                    }
                    if($existe == false){
                        // $cantidadFaltantes++;
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>'nuevo',
                                                     'idNotaTipo'=>5,
                                                     'notaCualitativa'=>'',
                                                     'notaCuantitativa'=>'',
                                                     'notaCuantitativaNueva'=>'',
                                                     'notaCualitativaNueva'=>'',
                                                     'notaTipo'=>'Promedio anual',
                                                     'idFila'=>$idInscripcion.'-5'
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
                'operativoTrue'=>$operativoTrue,
                'nivel'=>$nivel,
                'estadoMatricula'=>'newsofar',
                'gestionActual'=>$this->session->get('currentyear'),
                'idInscripcion'=>$arrDataInfro['idInscripcion'],
                'gestion'=>$gestion,
                'grado'=>$grado,
                'tipoNota'=>$tipo,
                'estadosPermitidos'=>$estadosPermitidos,
                'cantidadRegistrados'=>$cantidadRegistrados,
                'cantidadFaltantes'=>$cantidadFaltantes,
                'tipoSubsistema'=>$tipoSubsistema,
                'titulosNotas'=>$titulos_notas
            );
        } catch (Exception $e) {
            return null;
        }

    }


    /*
     * Variable opcion define si se trabajara sobre el operativo actual o regularizacion, R = regularizacion , A = actual,
     */
    public function regular($idInscripcion,$operativo){
        try {
            $operativoTrue = $operativo;
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
            $cantidadRegistrados = 0;

            $tipoNota = $this->getTipoNota($sie,$gestion,$nivel,$grado,'');
            
            if($tipoNota == 'Trimestre'){
                
                if($gestion >= 2020){
                    
                    $operativo = ($nivel==11 or ($nivel ==12 && $grado == 1) or ($nivel ==403 && $grado == 1) or ($nivel ==404 && $grado == 1))?1:3;

                    //the new ini

                    vuelveX:
              
                        // REALIZAMOS LA VUELTA COMPLETA PARA OBTENER LAS MATERIAS CORRECTAS
                        $asignaturas = $this->em->createQueryBuilder()
                                    ->select('at.id, at.area, asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                                    ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
                                    ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.institucioneducativaCurso = iec.id')
                                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ieco.insitucioneducativaCurso = iec.id')
                                    ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','ea.estudianteInscripcion = ei.id and ea.institucioneducativaCursoOferta = ieco.id')
                                    ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                                    ->innerJoin('SieAppWebBundle:AreaTipo','at','WITH','asit.areaTipo = at.id')
                                    ->groupBy('at.id, at.area, asit.id, asit.asignatura, ea.id')
                                    ->orderBy('at.id','ASC')
                                    ->addOrderBy('asit.id','ASC')
                                    ->where('ei.id = :idInscripcion')
                                    ->setParameter('idInscripcion',$idInscripcion)
                                    ->getQuery()
                                    ->getResult();
                

                    // dump($asignaturas);die;

                    $cursoOferta = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$inscripcion->getInstitucioneducativaCurso()->getId()));

                    $arrayAsignaturasEstudiante = array();
                    foreach ($asignaturas as $a) {
                        $arrayAsignaturasEstudiante[] = $a['asignaturaId'];
                    }

                    
                    $nuevaArea = false;
                    foreach ($cursoOferta as $co) {
                        // LA MATERIA TECNICA GENERAL Y ESPECILIZADA NO SE AGREGAN AUTOMATICAMENTE A TODOS LOS ESTUDIANTES A PARTIR DE LA GESTION 2019
                        if(!in_array($co->getAsignaturaTipo()->getId(), $arrayAsignaturasEstudiante) and ($gestion < 2019 or ($gestion >= 2019 and $nivel == 13 and $grado >= 3 and $co->getAsignaturaTipo()->getId() != 1038 and $co->getAsignaturaTipo()->getId() != 1039) or ($gestion >= 2019 and $nivel != 13) or ($gestion >= 2019 and $nivel == 13 and $grado<3))) {

                            // Si no existe la asignatura, registramos la asignatura para el maestro
                            $newEstAsig = new EstudianteAsignatura();
                            $newEstAsig->setGestionTipo($this->em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                            $newEstAsig->setFechaRegistro(new \DateTime('now'));
                            $newEstAsig->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                            $newEstAsig->setInstitucioneducativaCursoOferta($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId()));
                            $this->em->persist($newEstAsig);
                            $this->em->flush();
                            $nuevaArea = true;

                            // Registro de materia para estudiantes estudiante_asignatura en el log
                            $arrayEstAsig = [];
                            $arrayEstAsig['id'] = $newEstAsig->getId();
                            $arrayEstAsig['gestionTipo'] = $newEstAsig->getGestionTipo()->getId();
                            $arrayEstAsig['fechaRegistro'] = $newEstAsig->getFechaRegistro()->format('d-m-Y');
                            $arrayEstAsig['estudianteInscripcion'] = $newEstAsig->getEstudianteInscripcion()->getId();
                            $arrayEstAsig['institucioneducativaCursoOferta'] = $newEstAsig->getInstitucioneducativaCursoOferta()->getId();
                            
                            $this->funciones->setLogTransaccion(
                                $newEstAsig->getId(),
                                'estudiante_asignatura',
                                'C',
                                '',
                                $arrayEstAsig,
                                '',
                                'ACADEMICO',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );
                        }
                    }

                    // Volvemos atras si se adiciono alguna nueva materia o asignatura
                    if($nuevaArea == true){
                        goto vuelveX;
                    }

                    //dump($asignaturas);die;
                    $notasArray = array();
                    $cont = 0;

                    switch ($operativo) {
                        case 0:
                            $inicio = 6;
                            $fin = 0;
                            break;
                        case 1:
                            $inicio = 9;
                            $fin = 9;
                            break;
                        case 3:
                            $inicio = 6;
                            $fin = 8;
                            break;                             
                        case 4:
                            $inicio = 6;
                            $fin = 3;
                            break;
                        default:
                            $inicio = 6;
                            $fin = 9;
                            break;
                    }


                    if($this->session->get('ue_modular') == true and $nivel == 13){
                        $operativo = 3;
                        $fin = 3;
                        if($gestion == 2020){
                            $inicio = 6;
                            $fin = 8;
                        }                        
                    }

                    foreach ($asignaturas as $a) {
                     //dump($a);
                        // Concatenamos la especialidad si se tiene registrado
                        $nombreAsignatura = $a['asignatura'];
                        if($a['asignaturaId'] == 1039){
                            $especialidad = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion'=>$idInscripcion));
                            if($especialidad){
                                $nombreAsignatura = $a['asignatura'].':'.$especialidad->getEspecialidadTecnicoHumanisticoTipo()->getEspecialidad();
                            }
                        }

                        if(false){
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

                                             //dump($asignaturasNotas);
                                             //dump($inicio);
                                             //dump($fin);
                                            // die;

                        // EN LA GESTION 2019 INICIAL NO SE REGISTRARAN LAS NOTAS POR MATERIA
                        //if (($gestion < 2019) or ($gestion >= 2019 and $nivel != 11) ) {
                        if (($gestion < 2019) or ($gestion >= 2019 ) ) {
                            for($i=$inicio;$i<=$fin;$i++){
                                $existe = 'no';
                                foreach ($asignaturasNotas as $an) {
                                    if($i == $an['idNotaTipo']){
                                        // dump($nivel);
                                        // dump($grado);
                                        if($nivel != 11 and $nivel != 1 and $nivel != 403 and $nivel != 11 and ($nivel.$grado !=121) and ($nivel.$grado !=4041)){
                                            $valorNota = $an['notaCuantitativa'];
                                            if($valorNota == 0 or $valorNota == "0"){
                                                $cantidadFaltantes++;
                                            }else{
                                                $cantidadRegistrados++;
                                            }
                                        }else{
                                            $valorNota = $an['notaCualitativa'];
                                            if($valorNota == ""){
                                                $cantidadFaltantes++;
                                            }else{
                                                $cantidadRegistrados++;
                                            }
                                        }
                                        $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-".$i,
                                                                'idEstudianteNota'=>$an['idNota'],
                                                                'nota'=>$valorNota,
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>$an['idNotaTipo'],
                                                                'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                                'bimestre'=>$an['notaTipo'],
                                                                'idFila'=>$a['asignaturaId'].''.$i
                                                            );
                                        $existe = 'si';
                                        break;
                                    }

                                } //dump($valorNota);
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
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>$i,
                                                                'idEstudianteAsignatura'=>$a['estAsigId'],
                                                                'bimestre'=>$this->literal($i)['titulo'],
                                                                'idFila'=>$a['asignaturaId'].''.$i
                                                            );
                                }
                            }

                            /**
                             * PROMEDIOS
                             */

                            if($nivel != 11 and $nivel != 1 and $nivel != 403 and $nivel != 404 and $operativo >= 3){
                                //new by krlos
                                 $idavg = ($gestion <= 2019)?"-5":"-9";
                                 
                                // Para el promedio
                                foreach ($asignaturasNotas as $an) {
                                    $existe = 'no';
                                    if(in_array($an['idNotaTipo'], array(9))){
                                        $notasArray[$cont]['notas'][] =   array(
                                                                    'id'=>$cont.$idavg,
                                                                    'idEstudianteNota'=>$an['idNota'],
                                                                    'nota'=>$an['notaCuantitativa'],
                                                                    'notaNueva'=>'',
                                                                    'notaCualitativaNueva'=>'',
                                                                    'idNotaTipo'=>$an['idNotaTipo'],
                                                                    'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                                    'bimestre'=>$an['notaTipo'],
                                                                    'idFila'=>$a['asignaturaId'].$idavg 
                                                                );
                                        $existe = 'si';
                                        break;
                                    }
                                }
                                if($existe == 'no'){

                                    $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont.$idavg ,
                                                                'idEstudianteNota'=>'nuevo',
                                                                'nota'=>'',
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>9,
                                                                'idEstudianteAsignatura'=>$a['estAsigId'],
                                                                'bimestre'=>'Promedio',
                                                                'idFila'=>$a['asignaturaId'].$idavg 
                                                            );
                                }
                                
                            }
                        }                                                                    

                                               
                        $cont++;
                    }
                    //die;
                    //die;
                    $areas = array();
                    /*if($conArea == true){
                        foreach ($notasArray as $n) {
                            $areas[$n['area']][] = $n;
                        }
                    }else{*/
                        $areas = $notasArray;
                    //}
                    //dump($areas);die;
                    $tipo = 'trimestre2020';

                    //the new end

                }else{

                    $tn = array(30,27,6,31,28,7,32,29,8,9,10,11);
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

                            // Si no existe la asignatura, registramos la asignatura para el maestro
                            $newEstAsig = new EstudianteAsignatura();
                            $newEstAsig->setGestionTipo($this->em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                            $newEstAsig->setFechaRegistro(new \DateTime('now'));
                            $newEstAsig->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                            $newEstAsig->setInstitucioneducativaCursoOferta($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId()));
                            $this->em->persist($newEstAsig);
                            $this->em->flush();
                            $nuevaArea = true;

                            // Registro de materia para estudiantes estudiante_asignatura en el log
                            $arrayEstAsig = [];
                            $arrayEstAsig['id'] = $newEstAsig->getId();
                            $arrayEstAsig['gestionTipo'] = $newEstAsig->getGestionTipo()->getId();
                            $arrayEstAsig['fechaRegistro'] = $newEstAsig->getFechaRegistro()->format('d-m-Y');
                            $arrayEstAsig['estudianteInscripcion'] = $newEstAsig->getEstudianteInscripcion()->getId();
                            $arrayEstAsig['institucioneducativaCursoOferta'] = $newEstAsig->getInstitucioneducativaCursoOferta()->getId();
                            
                            $this->funciones->setLogTransaccion(
                                $newEstAsig->getId(),
                                'estudiante_asignatura',
                                'C',
                                '',
                                $arrayEstAsig,
                                '',
                                'ACADEMICO',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );
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
                                        $cantidadRegistrados++;
                                        $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-".$tn[$i],
                                                                'idEstudianteNota'=>$an['idNota'],
                                                                'nota'=>$an['notaCuantitativa'],
                                                                'notaCualitativa'=>$an['notaCualitativa'],
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>$an['idNotaTipo'],
                                                                'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                                'bimestre'=> $this->literal($tn[$i])['abrev'],
                                                                'idFila'=>$an['id'].''.$tn[$i]
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
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>$tn[$i],
                                                                'idEstudianteAsignatura'=>$a['estAsigId'],
                                                                'bimestre'=> $this->literal($tn[$i])['abrev'],
                                                                'idFila'=>$a['asignaturaId'].''.$tn[$i]
                                                            );
                                }
                            }
                        }else{
                            for($i=6;$i<=8;$i++){
                                $existe = 'no';
                                foreach ($asignaturasNotas as $an) {
                                    if($tn[$i] == $an['idNotaTipo']){
                                        $cantidadRegistrados++;
                                        $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-".$i,
                                                                'idEstudianteNota'=>$an['idNota'],
                                                                'nota'=>$an['notaCuantitativa'],
                                                                'notaCualitativa'=>$an['notaCualitativa'],
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>$an['idNotaTipo'],
                                                                'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                                'bimestre'=>$this->literal($tn[$i]['abrev']),
                                                                'idFila'=>$an['id'].''.$tn[$i]
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
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>$i,
                                                                'idEstudianteAsignatura'=>$a['estAsigId'],
                                                                'bimestre'=>$this->literal($tn[$i]['abrev']),
                                                                'idFila'=>$a['asignaturaId'].''.$tn[$i]
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
                }

            } else {

                // BIMESTRALES
                if($gestion == 2013 and $nivel != 11 and $grado == 1){
                    $conArea = true;
                }else{
                    $conArea = true;
                }
                vuelve:
                if($conArea == true){
                    // Obtenemos las areas o campos del estudiante
                    // $asignaturas = $this->em->createQueryBuilder()
                    //             ->select('at.id, at.area, asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                    //             ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                    //             ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ea.estudianteInscripcion = ei.id')
                    //             ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                    //             ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                    //             ->innerJoin('SieAppWebBundle:AreaTipo','at','WITH','asit.areaTipo = at.id')
                    //             ->groupBy('at.id, at.area, asit.id, asit.asignatura, ea.id')
                    //             ->orderBy('at.id','ASC')
                    //             ->addOrderBy('asit.id','ASC')
                    //             ->where('ei.id = :idInscripcion')
                    //             ->setParameter('idInscripcion',$idInscripcion)
                    //             ->getQuery()
                    //             ->getResult();

                    // REALIZAMOS LA VUELTA COMPLETA PARA OBTENER LAS MATERIAS CORRECTAS
                    $asignaturas = $this->em->createQueryBuilder()
                                ->select('at.id, at.area, asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                                ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
                                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.institucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ieco.insitucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','ea.estudianteInscripcion = ei.id and ea.institucioneducativaCursoOferta = ieco.id')
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
                    // $asignaturas = $this->em->createQueryBuilder()
                    //             ->select('asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                    //             ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                    //             ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ea.estudianteInscripcion = ei.id')
                    //             ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ea.institucioneducativaCursoOferta = ieco.id')
                    //             ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                    //             ->groupBy('asit.id, asit.asignatura, ea.id')
                    //             ->orderBy('asit.id','ASC')
                    //             ->where('ei.id = :idInscripcion')
                    //             ->setParameter('idInscripcion',$idInscripcion)
                    //             ->getQuery()
                    //             ->getResult();
                }

                // dump($asignaturas);die;

                $cursoOferta = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$inscripcion->getInstitucioneducativaCurso()->getId()));

                $arrayAsignaturasEstudiante = array();
                foreach ($asignaturas as $a) {
                    $arrayAsignaturasEstudiante[] = $a['asignaturaId'];
                }

                
                $nuevaArea = false;
                foreach ($cursoOferta as $co) {
                    // LA MATERIA TECNICA GENERAL Y ESPECILIZADA NO SE AGREGAN AUTOMATICAMENTE A TODOS LOS ESTUDIANTES A PARTIR DE LA GESTION 2019
                    if(!in_array($co->getAsignaturaTipo()->getId(), $arrayAsignaturasEstudiante) and ($gestion < 2019 or ($gestion >= 2019 and $nivel == 13 and $grado >= 3 and $co->getAsignaturaTipo()->getId() != 1038 and $co->getAsignaturaTipo()->getId() != 1039) or ($gestion >= 2019 and $nivel != 13) or ($gestion >= 2019 and $nivel == 13 and $grado<3))) {

                        // Si no existe la asignatura, registramos la asignatura para el maestro
                        $newEstAsig = new EstudianteAsignatura();
                        $newEstAsig->setGestionTipo($this->em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                        $newEstAsig->setFechaRegistro(new \DateTime('now'));
                        $newEstAsig->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                        $newEstAsig->setInstitucioneducativaCursoOferta($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId()));
                        $this->em->persist($newEstAsig);
                        $this->em->flush();
                        $nuevaArea = true;

                        // Registro de materia para estudiantes estudiante_asignatura en el log
                        $arrayEstAsig = [];
                        $arrayEstAsig['id'] = $newEstAsig->getId();
                        $arrayEstAsig['gestionTipo'] = $newEstAsig->getGestionTipo()->getId();
                        $arrayEstAsig['fechaRegistro'] = $newEstAsig->getFechaRegistro()->format('d-m-Y');
                        $arrayEstAsig['estudianteInscripcion'] = $newEstAsig->getEstudianteInscripcion()->getId();
                        $arrayEstAsig['institucioneducativaCursoOferta'] = $newEstAsig->getInstitucioneducativaCursoOferta()->getId();
                        
                        $this->funciones->setLogTransaccion(
                            $newEstAsig->getId(),
                            'estudiante_asignatura',
                            'C',
                            '',
                            $arrayEstAsig,
                            '',
                            'ACADEMICO',
                            json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                        );
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
                    if($gestion == 2020){
                        $inicio = 6;
                        $fin = 9;
                    }
                }

                foreach ($asignaturas as $a) {
                    // Concatenamos la especialidad si se tiene registrado
                    $nombreAsignatura = $a['asignatura'];
                    if($a['asignaturaId'] == 1039){
                        $especialidad = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion'=>$idInscripcion));
                        if($especialidad){
                            $nombreAsignatura = $a['asignatura'].':'.$especialidad->getEspecialidadTecnicoHumanisticoTipo()->getEspecialidad();
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

                                        // dump($asignaturasNotas);
                                        // dump($inicio);
                                        // dump($fin);
                                        // die;

                    // EN LA GESTION 2019 INICIAL NO SE REGISTRARAN LAS NOTAS POR MATERIA
                    if (($gestion < 2019) or ($gestion >= 2019 and $nivel != 11) ) {
                        for($i=$inicio;$i<=$fin;$i++){
                            $existe = 'no';
                            foreach ($asignaturasNotas as $an) {
                                if($i == $an['idNotaTipo']){
                                    if($nivel != 11 and $nivel != 1 and $nivel != 403){
                                        $valorNota = $an['notaCuantitativa'];
                                        if($valorNota == 0 or $valorNota == "0"){
                                            $cantidadFaltantes++;
                                        }else{
                                            $cantidadRegistrados++;
                                        }
                                    }else{
                                        $valorNota = $an['notaCualitativa'];
                                        if($valorNota == ""){
                                            $cantidadFaltantes++;
                                        }else{
                                            $cantidadRegistrados++;
                                        }
                                    }
                                    $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$i,
                                                            'idEstudianteNota'=>$an['idNota'],
                                                            'nota'=>$valorNota,
                                                            'notaNueva'=>'',
                                                            'notaCualitativaNueva'=>'',
                                                            'idNotaTipo'=>$an['idNotaTipo'],
                                                            'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                            'bimestre'=>$an['notaTipo'],
                                                            'idFila'=>$a['asignaturaId'].''.$i
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
                                                            'notaNueva'=>'',
                                                            'notaCualitativaNueva'=>'',
                                                            'idNotaTipo'=>$i,
                                                            'idEstudianteAsignatura'=>$a['estAsigId'],
                                                            'bimestre'=>$this->literal($i)['titulo'],
                                                            'idFila'=>$a['asignaturaId'].''.$i
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
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>$an['idNotaTipo'],
                                                                'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                                'bimestre'=>$an['notaTipo'],
                                                                'idFila'=>$a['asignaturaId'].'5'
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
                                                            'notaNueva'=>'',
                                                            'notaCualitativaNueva'=>'',
                                                            'idNotaTipo'=>5,
                                                            'idEstudianteAsignatura'=>$a['estAsigId'],
                                                            'bimestre'=>'Promedio',
                                                            'idFila'=>$a['asignaturaId'].'5'
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

            /*
            * Recorremos el array generado de las notas y recuperamos los titulos de las notas, para la tabla
            */
            $titulos_notas = array();
            if(count($notasArray)>0 and isset($notasArray[0]['notas'])){
                $titulos = $notasArray[0]['notas'];
                for($i=0;$i<count($titulos);$i++){
                    $titulos_notas[] = array('titulo'=>$titulos[$i]['bimestre']);
                }
            }


            //notas cualitativas
            $arrayCualitativas = array();

            $cualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion'=>$idInscripcion),array('notaTipo'=>'ASC'));

            //if($nivel == 11 or $nivel == 1 or $nivel == 403){
            if(($nivel == 11 or $nivel == 1 or $nivel == 403) or ($nivel ==12 and $grado == 1)){

                if ($gestion == 2019) {
                    for ($i=$inicio; $i <=$fin; $i++) { 
                        $existe = false;
                        foreach ($cualitativas as $c) {
                            if($c->getNotaTipo()->getId() == $i){
                                $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                             'idEstudianteNotaCualitativa'=>$c->getId(),
                                                             'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                             'notaCualitativa'=>$c->getNotaCualitativa(),
                                                             'notaCuantitativa'=>0,
                                                             'notaCuantitativaNueva'=>'',
                                                             'notaCualitativaNueva'=>'',
                                                             'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                             'idFila'=>$idInscripcion.''.$i
                                                            );
                                $existe = true;
                                if($c->getNotaCualitativa() == ""){
                                    $cantidadFaltantes++;
                                }else{
                                    $cantidadRegistrados++;
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
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$this->literal($i)['titulo'],
                                                         'idFila'=>$idInscripcion.''.$i
                                                        );
                            $existe = true;
                        }
                    }
                }

                // VERIFICAMOS SI EL OPERATIVO ES MAYOR O IGUAL A 4 PARA CARGAR LA NOTA CUALITATIVA ANUAL
                if (($operativo >= 4 and $gestion<2020 ) or ($gestion == 2020)) {
                    // Para inicial
                    $existe = false;
                    //dump($cualitativas);die;
                    foreach ($cualitativas as $c) {
                        if($c->getNotaTipo()->getId() == 18){
                            $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                         'idEstudianteNotaCualitativa'=>$c->getId(),
                                                         'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                         'notaCualitativa'=>$c->getNotaCualitativa(),
                                                         'notaCuantitativa'=>$c->getNotaCuantitativa(),
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                         'idFila'=>$idInscripcion.'18'
                                                        );
                            $existe = true;
                            if($c->getNotaCualitativa() == ""){
                                // $cantidadFaltantes++;
                            }
                        }
                    }
                    $conditionAvg = ($nivel == 11 or ($nivel ==12 && $grado == 1) && $gestion == 2020)?$operativo >= 1:$operativo >= 3;
                    if($existe == false and $conditionAvg){
                        // $cantidadFaltantes++;
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>'nuevo',
                                                     'idNotaTipo'=>18,
                                                     'notaCualitativa'=>'',
                                                     'notaCuantitativa'=>'',
                                                     'notaCuantitativaNueva'=>'',
                                                     'notaCualitativaNueva'=>'',
                                                     'notaTipo'=>$this->literal(18)['titulo'],
                                                     'idFila'=>$idInscripcion.'18'
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
                                                         'notaCuantitativa'=>$c->getNotaCuantitativa(),
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                         'idFila'=>$idInscripcion.''.$i
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
                                                     'notaCuantitativaNueva'=>'',
                                                     'notaCualitativaNueva'=>'',
                                                     'notaTipo'=>$this->literal($i)['titulo'],
                                                     'idFila'=>$idInscripcion.''.$i
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
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                         'idFila'=>$idInscripcion.'5'
                                                        );
                            $existe = true;
                            if($c->getNotaCualitativa() == ""){
                                // $cantidadFaltantes++;
                            }
                        }
                    }
                    if($existe == false){
                        // $cantidadFaltantes++;
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>'nuevo',
                                                     'idNotaTipo'=>5,
                                                     'notaCualitativa'=>'',
                                                     'notaCuantitativa'=>'',
                                                     'notaCuantitativaNueva'=>'',
                                                     'notaCualitativaNueva'=>'',
                                                     'notaTipo'=>'Promedio anual',
                                                     'idFila'=>$idInscripcion.'5'
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
                'operativoTrue'=>$operativoTrue,
                'nivel'=>$nivel,
                'estadoMatricula'=>$inscripcion->getEstadomatriculaTipo()->getId(),
                'gestionActual'=>$this->session->get('currentyear'),
                'idInscripcion'=>$idInscripcion,
                'gestion'=>$gestion,
                'grado'=>$grado,
                'tipoNota'=>$tipo,
                'estadosPermitidos'=>$estadosPermitidos,
                'cantidadRegistrados'=>$cantidadRegistrados,
                'cantidadFaltantes'=>$cantidadFaltantes,
                'tipoSubsistema'=>$tipoSubsistema,
                'titulosNotas'=>$titulos_notas
            );
        } catch (Exception $e) {
            return null;
        }

    }

    public function regularDB($idInscripcion,$operativo){

            $operativoTrue = $operativo;
            $tipoSubsistema = $this->session->get('tiposubsistema');

            $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

            // Cantidad de notas faltantes
            $cantidadFaltantes = 0;
            $cantidadRegistrados = 0;

            $tipoNota = $this->getTipoNota($sie,$gestion,$nivel,$grado,'');        

                // BIMESTRALES
                if($gestion == 2013 and $nivel != 11 and $grado == 1){
                    $conArea = true;
                }else{
                    $conArea = true;
                }
                vuelve:
                if($conArea == true){
                   

                    // REALIZAMOS LA VUELTA COMPLETA PARA OBTENER LAS MATERIAS CORRECTAS
                    $asignaturas = $this->em->createQueryBuilder()
                                ->select('at.id, at.area, asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                                ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
                                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.institucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ieco.insitucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','ea.estudianteInscripcion = ei.id and ea.institucioneducativaCursoOferta = ieco.id')
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
                    
                }

                // dump($asignaturas);die;

                $cursoOferta = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$inscripcion->getInstitucioneducativaCurso()->getId()));

                $arrayAsignaturasEstudiante = array();
                foreach ($asignaturas as $a) {
                    $arrayAsignaturasEstudiante[] = $a['asignaturaId'];
                }

                
                $nuevaArea = false;
                foreach ($cursoOferta as $co) {
                    // LA MATERIA TECNICA GENERAL Y ESPECILIZADA NO SE AGREGAN AUTOMATICAMENTE A TODOS LOS ESTUDIANTES A PARTIR DE LA GESTION 2019
                    if(!in_array($co->getAsignaturaTipo()->getId(), $arrayAsignaturasEstudiante) and ($gestion < 2019 or ($gestion >= 2019 and $nivel == 13 and $grado >= 3 and $co->getAsignaturaTipo()->getId() != 1038 and $co->getAsignaturaTipo()->getId() != 1039) or ($gestion >= 2019 and $nivel != 13) or ($gestion >= 2019 and $nivel == 13 and $grado<3))) {

                        // Si no existe la asignatura, registramos la asignatura para el maestro
                        $newEstAsig = new EstudianteAsignatura();
                        $newEstAsig->setGestionTipo($this->em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                        $newEstAsig->setFechaRegistro(new \DateTime('now'));
                        $newEstAsig->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                        $newEstAsig->setInstitucioneducativaCursoOferta($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId()));
                        $this->em->persist($newEstAsig);
                        $this->em->flush();
                        $nuevaArea = true;

                        // Registro de materia para estudiantes estudiante_asignatura en el log
                        $arrayEstAsig = [];
                        $arrayEstAsig['id'] = $newEstAsig->getId();
                        $arrayEstAsig['gestionTipo'] = $newEstAsig->getGestionTipo()->getId();
                        $arrayEstAsig['fechaRegistro'] = $newEstAsig->getFechaRegistro()->format('d-m-Y');
                        $arrayEstAsig['estudianteInscripcion'] = $newEstAsig->getEstudianteInscripcion()->getId();
                        $arrayEstAsig['institucioneducativaCursoOferta'] = $newEstAsig->getInstitucioneducativaCursoOferta()->getId();
                        
                        $this->funciones->setLogTransaccion(
                            $newEstAsig->getId(),
                            'estudiante_asignatura',
                            'C',
                            '',
                            $arrayEstAsig,
                            '',
                            'ACADEMICO',
                            json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                        );
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
                            $inicio = 6;
                            $fin = 0;
                            break;
                        case 1:
                            $inicio = 6;
                            $fin = $inicio;
                            break;
                        case 4:
                            $inicio = 6;
                            $fin = 8;
                            break;
                        default:
                            $inicio = 6;
                            $fin = $inicio+($operativo-1);
                            break;
                    }


                    if($this->session->get('ue_modular') == true and $nivel == 13){
                        $operativo = 3;
                        $fin = 3;
                        if( in_array($gestion, array(2020,2021,2022,2023,2024)) ){
                            $inicio = 6;
                            $fin = 8;
                        }                        
                    }
                    //to validate info consolidation
                $arrConsolidation = array('6'=>'bim1','7'=>'bim2','8'=>'bim3','9'=>'bim4');                    
                foreach ($asignaturas as $a) {
                    // Concatenamos la especialidad si se tiene registrado
                    $nombreAsignatura = $a['asignatura'];
                    if($a['asignaturaId'] == 1039){
                        $especialidad = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion'=>$idInscripcion));
                        if($especialidad){
                            $nombreAsignatura = $a['asignatura'].':'.$especialidad->getEspecialidadTecnicoHumanisticoTipo()->getEspecialidad();
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

                                        // dump($asignaturasNotas);
                                        // dump($inicio);
                                        // dump($fin);
                                        // die;

                    // EN LA GESTION 2019 INICIAL NO SE REGISTRARAN LAS NOTAS POR MATERIA
                    if (($gestion < 2019) or ($gestion >= 2019 and $nivel != 11) ) {

                        for($i=$inicio;$i<=$fin;$i++){
                            $swCloseOperative = false;
                            if($this->em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa'=>$sie, 'gestion'=>$gestion, "$arrConsolidation[$i]"=> 2))){
                                $swCloseOperative = true;
                            }                            
                            $existe = 'no';
                            foreach ($asignaturasNotas as $an) {
                                if($i == $an['idNotaTipo']){
                                    if($nivel != 11 and $nivel != 1 and $nivel != 403){
                                        $valorNota = $an['notaCuantitativa'];
                                        if($valorNota == 0 or $valorNota == "0"){
                                            $cantidadFaltantes++;
                                        }else{
                                            $cantidadRegistrados++;
                                        }
                                    }else{
                                        $valorNota = $an['notaCualitativa'];
                                        if($valorNota == ""){
                                            $cantidadFaltantes++;
                                        }else{
                                            $cantidadRegistrados++;
                                        }
                                    }
                                    $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$i,
                                                            'idEstudianteNota'=>$an['idNota'],
                                                            'nota'=>$valorNota,
                                                            'notaNueva'=>'',
                                                            'notaCualitativaNueva'=>'',
                                                            'idNotaTipo'=>$an['idNotaTipo'],
                                                            'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                            'bimestre'=>$an['notaTipo'],
                                                            'idFila'=>$a['asignaturaId'].''.$i,
                                                            'swCloseOperative'=>$swCloseOperative
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
                                                            'notaNueva'=>'',
                                                            'notaCualitativaNueva'=>'',
                                                            'idNotaTipo'=>$i,
                                                            'idEstudianteAsignatura'=>$a['estAsigId'],
                                                            'bimestre'=>$this->literal($i)['titulo'],
                                                            'idFila'=>$a['asignaturaId'].''.$i,
                                                            'swCloseOperative'=>$swCloseOperative
                                                        );
                            }
                        }

                        /**
                         * PROMEDIOS
                         */

                        if($nivel != 11 and $nivel != 1 and $nivel != 403 and $operativo >= 3){
                            $idavg = "-9";
                            // Para el promedio
                            foreach ($asignaturasNotas as $an) {
                                $existe = 'no';
                                if($an['idNotaTipo'] == 9){
                                    $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-9",
                                                                'idEstudianteNota'=>$an['idNota'],
                                                                'nota'=>$an['notaCuantitativa'],
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>$an['idNotaTipo'],
                                                                'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                                'bimestre'=>$an['notaTipo'],
                                                                'idFila'=>$a['asignaturaId'].$idavg,
                                                                'swCloseOperative'=>true 

                                                            );
                                    $existe = 'si';
                                    break;
                                }
                            }
                            if($existe == 'no'){

                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-9",
                                                            'idEstudianteNota'=>'nuevo',
                                                            'nota'=>'',
                                                            'notaNueva'=>'',
                                                            'notaCualitativaNueva'=>'',
                                                            'idNotaTipo'=>9,
                                                            'idEstudianteAsignatura'=>$a['estAsigId'],
                                                            'bimestre'=>'Promedio',
                                                            'idFila'=>$a['asignaturaId'].$idavg,
                                                            'swCloseOperative'=>true 
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
                $tipo = 'newTemplateDB';
                


            /*
            * Recorremos el array generado de las notas y recuperamos los titulos de las notas, para la tabla
            */
            $titulos_notas = array();
            if(count($notasArray)>0 and isset($notasArray[0]['notas'])){
                $titulos = $notasArray[0]['notas'];
                for($i=0;$i<count($titulos);$i++){
                    $titulos_notas[] = array('titulo'=>$titulos[$i]['bimestre']);
                }
            }


            //notas cualitativas
            $arrayCualitativas = array();

            $cualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion'=>$idInscripcion),array('notaTipo'=>'ASC'));

            //if($nivel == 11 or $nivel == 1 or $nivel == 403){
            if(($nivel == 11 or $nivel == 1 or $nivel == 403) ){
                
                if ($gestion >= 2022) {
                    for ($i=$inicio; $i <=$fin; $i++) { 
                        $swCloseOperative = false;
                        if($this->em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa'=>$sie, 'gestion'=>$gestion, "$arrConsolidation[$i]"=> 2))){
                            $swCloseOperative = true;
                        }
                        $existe = false;
                        foreach ($cualitativas as $c) {
                            if($c->getNotaTipo()->getId() == $i){
                                $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                             'idEstudianteNotaCualitativa'=>$c->getId(),
                                                             'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                             'notaCualitativa'=>$c->getNotaCualitativa(),
                                                             'notaCuantitativa'=>0,
                                                             'notaCuantitativaNueva'=>'',
                                                             'notaCualitativaNueva'=>'',
                                                             'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                             'idFila'=>$idInscripcion.''.$i,
                                                             'swCloseOperative'=>$swCloseOperative
                                                            );
                                $existe = true;
                                if($c->getNotaCualitativa() == ""){
                                    $cantidadFaltantes++;
                                }else{
                                    $cantidadRegistrados++;
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
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$this->literal($i)['titulo'],
                                                         'idFila'=>$idInscripcion.''.$i,
                                                         'swCloseOperative'=>$swCloseOperative
                                                        );
                            $existe = true;
                        }
                    }
                }

                // VERIFICAMOS SI EL OPERATIVO ES MAYOR O IGUAL A 4 PARA CARGAR LA NOTA CUALITATIVA ANUAL
                if (($operativo >= 3 and $gestion<2020 ) or ($gestion >= 2022)) {
                    // Para inicial
                    $existe = false;
                    //dump($cualitativas);die;
                    foreach ($cualitativas as $c) {
                        if($c->getNotaTipo()->getId() == 18){
                            $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                         'idEstudianteNotaCualitativa'=>$c->getId(),
                                                         'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                         'notaCualitativa'=>$c->getNotaCualitativa(),
                                                         'notaCuantitativa'=>$c->getNotaCuantitativa(),
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                         'idFila'=>$idInscripcion.'18',
                                                         'swCloseOperative'=>true

                                                        );
                            $existe = true;
                            if($c->getNotaCualitativa() == ""){
                                // $cantidadFaltantes++;
                            }
                        }
                    }
                    $conditionAvg = ($nivel == 11 && $gestion == 2021)?$operativo >= 1:$operativo >= 3;
                    if($existe == false and $conditionAvg){
                        // $cantidadFaltantes++;
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>'nuevo',
                                                     'idNotaTipo'=>18,
                                                     'notaCualitativa'=>'',
                                                     'notaCuantitativa'=>'',
                                                     'notaCuantitativaNueva'=>'',
                                                     'notaCualitativaNueva'=>'',
                                                     'notaTipo'=>$this->literal(18)['titulo'],
                                                     'idFila'=>$idInscripcion.'18',
                                                     'swCloseOperative'=>true
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
                  $inicio = 9;
                  $fin = 9;
                  $tipoNot = 'Trimestre';
                }

                // PARA LOS NIVELES DE PRIMARIA Y SECUNDARIA NO HAY NOTAS CUALITATIVAS
                // PARA ESTO PONEMOS EL BIMESTRE EN 0 Y NO ENTRE AL CICLO FOR
                if ($gestion >= 2019 and $nivel != 11) {
                    $fin = 9;
                }


                for($i=$inicio;$i<=$fin;$i++){
                    $existe = false;

                    foreach ($cualitativas as $c) {
                    
                        if($c->getNotaTipo()->getId() == $i){
                            $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                         'idEstudianteNotaCualitativa'=>$c->getId(),
                                                         'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                         'notaCualitativa'=>$c->getNotaCualitativa(),
                                                         'notaCuantitativa'=>$c->getNotaCuantitativa(),
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                         'idFila'=>$idInscripcion.''.$i
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
                                                     'notaCuantitativaNueva'=>'',
                                                     'notaCualitativaNueva'=>'',
                                                     'notaTipo'=>$this->literal($i)['titulo'],
                                                     'idFila'=>$idInscripcion.''.$i
                                                    );
                        $existe = true;
                    }
                }

                // VERIFICAMOS SI LA GESTION ES MAYOR O IGUAL A 2019
                // Y NIVEL PRIMARIA Y SECUNDARIA PARA AGREGARLE EL PROMEDIO ANUAL
                // if ($gestion >= 2019 and ($nivel == 13 or $nivel == 12) and $operativo >= 4) {     PROMEDIO ANUAL TAMBIEN PARA SECUNDARIA
                /*if ($gestion >= 2019 and $nivel == 12 and $operativo >= 3) {
                    $existe = false;
                    foreach ($cualitativas as $c) {
                        if($c->getNotaTipo()->getId() == 5){
                            $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                         'idEstudianteNotaCualitativa'=>$c->getId(),
                                                         'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                         'notaCualitativa'=>$c->getNotaCualitativa(),
                                                         'notaCuantitativa'=>$c->getNotaCuantitativa(),
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                         'idFila'=>$idInscripcion.'9'
                                                        );
                            $existe = true;
                            if($c->getNotaCualitativa() == ""){
                                // $cantidadFaltantes++;
                            }
                        }
                    }
                    if($existe == false){
                        // $cantidadFaltantes++;
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>'nuevo',
                                                     'idNotaTipo'=>9,
                                                     'notaCualitativa'=>'',
                                                     'notaCuantitativa'=>'',
                                                     'notaCuantitativaNueva'=>'',
                                                     'notaCualitativaNueva'=>'',
                                                     'notaTipo'=>'Promedio anual',
                                                     'idFila'=>$idInscripcion.'9'
                                                    );
                        $existe = true;
                    }
                }*/
            }

            $estadosPermitidos = $this->estadosPermitidos;

            return array(
                'cuantitativas'=>$areas,
                'cualitativas'=>$arrayCualitativas,
                'operativo'=>$operativo,
                'operativoTrue'=>$operativoTrue,
                'nivel'=>$nivel,
                'estadoMatricula'=>$inscripcion->getEstadomatriculaTipo()->getId(),
                'gestionActual'=>$this->session->get('currentyear'),
                'idInscripcion'=>$idInscripcion,
                'gestion'=>$gestion,
                'grado'=>$grado,
                'tipoNota'=>$tipo,
                'estadosPermitidos'=>$estadosPermitidos,
                'cantidadRegistrados'=>$cantidadRegistrados,
                'cantidadFaltantes'=>$cantidadFaltantes,
                'tipoSubsistema'=>$tipoSubsistema,
                'titulosNotas'=>$titulos_notas
            );

    }

    
   public function especialAsignaturaCualitativo($idInscripcion,$operativo, $idNota){

    $operativoTrue = $operativo;
    $tipoSubsistema = $this->session->get('tiposubsistema');

    $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
    $discapacidad = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBY(array('institucioneducativaCurso'=>$inscripcion->getInstitucioneducativaCurso()->getId()));

    $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
    $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
    $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
    $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
    $programa =  $discapacidad->getEspecialProgramaTipo()->getId();
    $servicio =  $discapacidad->getEspecialServicioTipo()->getId();
    // Cantidad de notas faltantes
    $cantidadFaltantes = 0;
    $cantidadRegistrados = 0;

    $tipoNota = $this->getTipoNotaEsp($sie,$gestion,$nivel,$grado);        
    $conArea = true;
        if($conArea == true){
            // REALIZAMOS LA VUELTA COMPLETA PARA OBTENER LAS MATERIAS CORRECTAS
            $asignaturas = $this->em->createQueryBuilder()
                        ->select('at.id, at.area, asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                        ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
                        ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.institucioneducativaCurso = iec.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ieco.insitucioneducativaCurso = iec.id')
                        ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','ea.estudianteInscripcion = ei.id and ea.institucioneducativaCursoOferta = ieco.id')
                        ->innerJoin('SieAppWebBundle:AsignaturaTipo','asit','WITH','ieco.asignaturaTipo = asit.id')
                        ->innerJoin('SieAppWebBundle:AreaTipo','at','WITH','asit.areaTipo = at.id')
                        ->groupBy('at.id, at.area, asit.id, asit.asignatura, ea.id')
                        ->orderBy('at.id','ASC')
                        ->addOrderBy('asit.id','ASC')
                        ->where('ei.id = :idInscripcion')
                        ->andWhere('asit.id <> :serv')
                        ->setParameter('idInscripcion',$idInscripcion)
                        ->setParameter('serv','4') 
                        ->getQuery()
                        ->getResult();
        }else{
            
        }
        ////// dump($asignaturas);die;
        $cursoOferta = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$inscripcion->getInstitucioneducativaCurso()->getId()));

        $arrConsolidation = array('6'=>'bim1','7'=>'bim2','8'=>'bim3','9'=>'bim4','53'=>'bim1','54'=>'bim2','55'=>'bim1','56'=>'bim2','57'=>'bim3','58'=>'bim4');   
        $notasArray = array();
        $cont = 0;

          switch ($operativo) {
                case 0:
                case 1:
                    $inicio = 6;
                    $fin = 6;
                    break;
                case 2:
                    $inicio = 6;
                    $fin = 7;
                    break;
                case 3:
                    $inicio = 6;
                    $fin = 8;
                    break;
                default:
                    $inicio = 6;
                    $fin = 8;
                    break;
            }

        if($nivel == 409){ //Atención Temprana
            $inicio = 53;
            $fin = 53;
        }
        if(in_array($servicio, array(40)) or in_array($programa, array(22))){ //LSB
            $inicio = 55;
            $fin = 55;
        }
      
        if(in_array($programa, array(19,41,39,28,50,51,52,53,54,55,56,57,58,59))){
            $inicio = 53;
            $fin = 53;
        }
        
        foreach ($asignaturas as $a) {
            // Concatenamos la especialidad si se tiene registrado
            $nombreAsignatura = $a['asignatura'];
            
            if($conArea == true){
                $notasArray[$cont] = array('areaId'=>$a['id'],'area'=>$a['area'],'idAsignatura'=>$a['asignaturaId'],'asignatura'=>$nombreAsignatura);
            }else{
                $notasArray[$cont] = array('idAsignatura'=>$a['asignaturaId'],'asignatura'=>$a['asignatura']);
            }
            $asignaturasNotas = $this->em->createQueryBuilder()
                                ->select('en.id as idNota, nt.id as idNotaTipo, nt.notaTipo, ea.id as idEstudianteAsignatura, en.notaCuantitativa, en.notaCualitativa, en.recomendacion, at.id')
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
                //dump($inicio); dump($fin);die;
                for($i=$inicio;$i<=$fin;$i++){ 
                    $swCloseOperative = false;
                    if($this->em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa'=>$sie, 'gestion'=>$gestion, "$arrConsolidation[$i]"=> 2))){
                        $swCloseOperative = true;
                    }                            
                    $existe = 'no'; 
                    foreach ($asignaturasNotas as $an) {
                        if($i == $an['idNotaTipo']){ 
                            if($nivel != 11 and $nivel != 1 and $nivel != 403){
                                $valorNota = $an['notaCuantitativa'];
                                if($valorNota == 0 or $valorNota == "0"){
                                    $cantidadFaltantes++;
                                }else{
                                    $cantidadRegistrados++;
                                }
                            }else{
                                $valorNota = $an['notaCualitativa'];
                                if($valorNota == ""){
                                    $cantidadFaltantes++;
                                }else{
                                    $cantidadRegistrados++;
                                }
                            }
                            $notasArray[$cont]['notas'][] =   array(
                                                    'id'=>$cont."-".$i,
                                                    'idEstudianteNota'=>$an['idNota'],
                                                    'nota'=>$valorNota,
                                                    'notaNueva'=>'',
                                                    'notaCualitativaNueva'=>$an['notaCualitativa'],
                                                    'recomendacionNueva'=>$an['recomendacion'],
                                                    'idNotaTipo'=>$an['idNotaTipo'],
                                                    'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                    'bimestre'=>$an['notaTipo'],
                                                    'idFila'=>$a['asignaturaId'].''.$i,
                                                    'swCloseOperative'=>$swCloseOperative
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
                                                    'notaNueva'=>'',
                                                    'notaCualitativaNueva'=>'',
                                                    'recomendacionNueva'=>'',
                                                    'idNotaTipo'=>$i,
                                                    'idEstudianteAsignatura'=>$a['estAsigId'],
                                                    'bimestre'=>$this->literal($i)['titulo'],
                                                    'idFila'=>$a['asignaturaId'].''.$i,
                                                    'swCloseOperative'=>$swCloseOperative
                                                );
                    }
                }
               // dump($notasArray);die;
            }
            
            $cont++;
        }
        $areas = array();
        $areas = $notasArray;
        $tipo = 'TrimestralPrograma';

    /*
    * Recorremos el array generado de las notas y recuperamos los titulos de las notas, para la tabla
    */
    $arrayCualitativas = array();

    $estadosPermitidos = array(0);

    $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(5,28));
    if($nivel==409 or in_array($programa, array(41,43,19,28,39,50,51,52,53,54,55,56,57,58,59)))
        $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(47,78));

    $notaCualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion,'notaTipo'=>$inicio));
    if($notaCualitativas){ 
        $obsResult = json_decode($notaCualitativas->getObs(), true);
        $arrayCualitativas = array('idEstado'=>$obsResult["id"],'notaCualitativa'=>$notaCualitativas->getNotaCualitativa());
    }
    return array(
        'cuantitativas'=>$areas,
        'cualitativas'=>$arrayCualitativas,
        'operativo'=>$operativo,
        'operativoTrue'=>$operativoTrue,
        'nivel'=>$nivel,
        'estadoMatricula'=>$inscripcion->getEstadomatriculaTipo()->getId(),
        'gestionActual'=>$this->session->get('currentyear'),
        'idInscripcion'=>$idInscripcion,
        'gestion'=>$gestion,
        'grado'=>$grado,
        'tipoNota'=>$tipo,
        'estadosPermitidos'=>$estadosPermitidos,
        'cantidadRegistrados'=>$cantidadRegistrados,
        'cantidadFaltantes'=>$cantidadFaltantes,
        'tipoSubsistema'=>$tipoSubsistema,
        'titulosNotas'=>'',
        'estadosFinales'=>$estadosFinales
    );

}  

   public function regularEspecial($idInscripcion,$operativo){

            $operativoTrue = $operativo;
            $tipoSubsistema = $this->session->get('tiposubsistema');

            $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

            // Cantidad de notas faltantes
            $cantidadFaltantes = 0;
            $cantidadRegistrados = 0;

            $tipoNota = $this->getTipoNotaEsp($sie,$gestion,$nivel,$grado);        

                // BIMESTRALES
                if($gestion == 2013 and $nivel != 11 and $grado == 1){
                    $conArea = true;
                }else{
                    $conArea = true;
                }
                vuelve:
                if($conArea == true){
                   

                    // REALIZAMOS LA VUELTA COMPLETA PARA OBTENER LAS MATERIAS CORRECTAS
                    $asignaturas = $this->em->createQueryBuilder()
                                ->select('at.id, at.area, asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                                ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
                                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.institucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ieco.insitucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','ea.estudianteInscripcion = ei.id and ea.institucioneducativaCursoOferta = ieco.id')
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
                    
                }

                // dump($asignaturas);die;

                $cursoOferta = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$inscripcion->getInstitucioneducativaCurso()->getId()));

                $arrayAsignaturasEstudiante = array();
                foreach ($asignaturas as $a) {
                    $arrayAsignaturasEstudiante[] = $a['asignaturaId'];
                }

                
                $nuevaArea = false;
                foreach ($cursoOferta as $co) {
                    // LA MATERIA TECNICA GENERAL Y ESPECILIZADA NO SE AGREGAN AUTOMATICAMENTE A TODOS LOS ESTUDIANTES A PARTIR DE LA GESTION 2019
                    if(!in_array($co->getAsignaturaTipo()->getId(), $arrayAsignaturasEstudiante) and ($gestion < 2019 or ($gestion >= 2019 and $nivel == 13 and $grado >= 3 and $co->getAsignaturaTipo()->getId() != 1038 and $co->getAsignaturaTipo()->getId() != 1039) or ($gestion >= 2019 and $nivel != 13) or ($gestion >= 2019 and $nivel == 13 and $grado<3))) {

                        // Si no existe la asignatura, registramos la asignatura para el maestro
                        $newEstAsig = new EstudianteAsignatura();
                        $newEstAsig->setGestionTipo($this->em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                        $newEstAsig->setFechaRegistro(new \DateTime('now'));
                        $newEstAsig->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                        $newEstAsig->setInstitucioneducativaCursoOferta($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId()));
                        $this->em->persist($newEstAsig);
                        $this->em->flush();
                        $nuevaArea = true;

                        // Registro de materia para estudiantes estudiante_asignatura en el log
                        $arrayEstAsig = [];
                        $arrayEstAsig['id'] = $newEstAsig->getId();
                        $arrayEstAsig['gestionTipo'] = $newEstAsig->getGestionTipo()->getId();
                        $arrayEstAsig['fechaRegistro'] = $newEstAsig->getFechaRegistro()->format('d-m-Y');
                        $arrayEstAsig['estudianteInscripcion'] = $newEstAsig->getEstudianteInscripcion()->getId();
                        $arrayEstAsig['institucioneducativaCursoOferta'] = $newEstAsig->getInstitucioneducativaCursoOferta()->getId();
                        
                        $this->funciones->setLogTransaccion(
                            $newEstAsig->getId(),
                            'estudiante_asignatura',
                            'C',
                            '',
                            $arrayEstAsig,
                            '',
                            'ACADEMICO',
                            json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                        );
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
                            $inicio = 6;
                            $fin = 0;
                            break;
                        case 1:
                            $inicio = 6;
                            $fin = $inicio;
                            break;
                        case 4:
                            $inicio = 6;
                            $fin = 8;
                            break;
                        default:
                            $inicio = 6;
                            $fin = $inicio+($operativo-1);
                            break;
                    }


                    if($this->session->get('ue_modular') == true and $nivel == 13){
                        $operativo = 3;
                        $fin = 3;
                        if( in_array($gestion, array(2020,2021)) ){
                            $inicio = 6;
                            $fin = 8;
                        }                        
                    }
                    //to validate info consolidation
                $arrConsolidation = array('6'=>'bim1','7'=>'bim2','8'=>'bim3','9'=>'bim4');                    
                foreach ($asignaturas as $a) {
                    // Concatenamos la especialidad si se tiene registrado
                    $nombreAsignatura = $a['asignatura'];
                    if($a['asignaturaId'] == 1039){
                        $especialidad = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion'=>$idInscripcion));
                        if($especialidad){
                            $nombreAsignatura = $a['asignatura'].':'.$especialidad->getEspecialidadTecnicoHumanisticoTipo()->getEspecialidad();
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

                                        // dump($asignaturasNotas);
                                        // dump($inicio);
                                        // dump($fin);
                                        // die;
                   // dump($nivel);die;
                    // EN LA GESTION 2019 INICIAL NO SE REGISTRARAN LAS NOTAS POR MATERIA
                    if (($gestion < 2019) or ($gestion >= 2019 and $nivel != 11) ) {

                        for($i=$inicio;$i<=$fin;$i++){
                            $swCloseOperative = false;
                            if($this->em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa'=>$sie, 'gestion'=>$gestion, "$arrConsolidation[$i]"=> 2))){
                                $swCloseOperative = true;
                            }                            
                            $existe = 'no';
                            foreach ($asignaturasNotas as $an) {
                                if($i == $an['idNotaTipo']){ 
                                    if($nivel != 11 and $nivel != 1 and $nivel != 403){
                                        $valorNota = $an['notaCuantitativa'];
                                        if($valorNota == 0 or $valorNota == "0"){
                                            $cantidadFaltantes++;
                                        }else{
                                            $cantidadRegistrados++;
                                        }
                                    }else{
                                        $valorNota = $an['notaCualitativa'];
                                        if($valorNota == ""){
                                            $cantidadFaltantes++;
                                        }else{
                                            $cantidadRegistrados++;
                                        }
                                    }
                                    $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-".$i,
                                                            'idEstudianteNota'=>$an['idNota'],
                                                            'nota'=>$valorNota,
                                                            'notaNueva'=>'',
                                                            'notaCualitativaNueva'=>'',
                                                            'idNotaTipo'=>$an['idNotaTipo'],
                                                            'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                            'bimestre'=>$an['notaTipo'],
                                                            'idFila'=>$a['asignaturaId'].''.$i,
                                                            'swCloseOperative'=>$swCloseOperative
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
                                                            'notaNueva'=>'',
                                                            'notaCualitativaNueva'=>'',
                                                            'idNotaTipo'=>$i,
                                                            'idEstudianteAsignatura'=>$a['estAsigId'],
                                                            'bimestre'=>$this->literal($i)['titulo'],
                                                            'idFila'=>$a['asignaturaId'].''.$i,
                                                            'swCloseOperative'=>$swCloseOperative
                                                        );
                            }
                        }

                        /**
                         * PROMEDIOS
                         */

                        if($nivel != 11 and $nivel != 1 and $nivel != 403 and $operativo >= 3){
                            $idavg = "-9";
                            // Para el promedio
                            foreach ($asignaturasNotas as $an) {
                                $existe = 'no';
                                if($an['idNotaTipo'] == 9){
                                    $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-9",
                                                                'idEstudianteNota'=>$an['idNota'],
                                                                'nota'=>$an['notaCuantitativa'],
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>$an['idNotaTipo'],
                                                                'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                                'bimestre'=>$an['notaTipo'],
                                                                'idFila'=>$a['asignaturaId'].$idavg,
                                                                'swCloseOperative'=>true 

                                                            );
                                    $existe = 'si';
                                    break;
                                }
                            }
                            if($existe == 'no'){

                                $notasArray[$cont]['notas'][] =   array(
                                                            'id'=>$cont."-9",
                                                            'idEstudianteNota'=>'nuevo',
                                                            'nota'=>'',
                                                            'notaNueva'=>'',
                                                            'notaCualitativaNueva'=>'',
                                                            'idNotaTipo'=>9,
                                                            'idEstudianteAsignatura'=>$a['estAsigId'],
                                                            'bimestre'=>'Promedio',
                                                            'idFila'=>$a['asignaturaId'].$idavg,
                                                            'swCloseOperative'=>true 
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
                $tipo = 'newTemplateDB';
                


            /*
            * Recorremos el array generado de las notas y recuperamos los titulos de las notas, para la tabla
            */
            $titulos_notas = array();
            if(count($notasArray)>0 and isset($notasArray[0]['notas'])){
                $titulos = $notasArray[0]['notas'];
                for($i=0;$i<count($titulos);$i++){
                    $titulos_notas[] = array('titulo'=>$titulos[$i]['bimestre']);
                }
            }


            //notas cualitativas
            $arrayCualitativas = array();

            $cualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion'=>$idInscripcion),array('notaTipo'=>'ASC'));

            //if($nivel == 11 or $nivel == 1 or $nivel == 403){
            if(($nivel == 11 or $nivel == 1 or $nivel == 403) ){
                
                if ($gestion == 2021) {
                    for ($i=$inicio; $i <=$fin; $i++) { 
                        $swCloseOperative = false;
                        if($this->em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa'=>$sie, 'gestion'=>$gestion, "$arrConsolidation[$i]"=> 1))){
                            $swCloseOperative = true;
                        }                          
                        $existe = false;
                        foreach ($cualitativas as $c) {
                            if($c->getNotaTipo()->getId() == $i){
                                $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                             'idEstudianteNotaCualitativa'=>$c->getId(),
                                                             'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                             'notaCualitativa'=>$c->getNotaCualitativa(),
                                                             'notaCuantitativa'=>0,
                                                             'notaCuantitativaNueva'=>'',
                                                             'notaCualitativaNueva'=>'',
                                                             'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                             'idFila'=>$idInscripcion.''.$i,
                                                             'swCloseOperative'=>$swCloseOperative
                                                            );
                                $existe = true;
                                if($c->getNotaCualitativa() == ""){
                                    $cantidadFaltantes++;
                                }else{
                                    $cantidadRegistrados++;
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
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$this->literal($i)['titulo'],
                                                         'idFila'=>$idInscripcion.''.$i,
                                                         'swCloseOperative'=>$swCloseOperative
                                                        );
                            $existe = true;
                        }
                    }
                }

                // VERIFICAMOS SI EL OPERATIVO ES MAYOR O IGUAL A 4 PARA CARGAR LA NOTA CUALITATIVA ANUAL
                if (($operativo >= 3 and $gestion<2020 ) or ($gestion == 2021)) {
                    // Para inicial
                    $existe = false;
                    //dump($cualitativas);die;
                    foreach ($cualitativas as $c) {
                        if($c->getNotaTipo()->getId() == 18){
                            $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                         'idEstudianteNotaCualitativa'=>$c->getId(),
                                                         'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                         'notaCualitativa'=>$c->getNotaCualitativa(),
                                                         'notaCuantitativa'=>$c->getNotaCuantitativa(),
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                         'idFila'=>$idInscripcion.'18',
                                                         'swCloseOperative'=>true

                                                        );
                            $existe = true;
                            if($c->getNotaCualitativa() == ""){
                                // $cantidadFaltantes++;
                            }
                        }
                    }
                    $conditionAvg = ($nivel == 11 && $gestion == 2021)?$operativo >= 1:$operativo >= 3;
                    if($existe == false and $conditionAvg){
                        // $cantidadFaltantes++;
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>'nuevo',
                                                     'idNotaTipo'=>18,
                                                     'notaCualitativa'=>'',
                                                     'notaCuantitativa'=>'',
                                                     'notaCuantitativaNueva'=>'',
                                                     'notaCualitativaNueva'=>'',
                                                     'notaTipo'=>$this->literal(18)['titulo'],
                                                     'idFila'=>$idInscripcion.'18',
                                                     'swCloseOperative'=>true
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
                  $inicio = 9;
                  $fin = 9;
                  $tipoNot = 'Trimestre';
                }

                // PARA LOS NIVELES DE PRIMARIA Y SECUNDARIA NO HAY NOTAS CUALITATIVAS
                // PARA ESTO PONEMOS EL BIMESTRE EN 0 Y NO ENTRE AL CICLO FOR
                if ($gestion >= 2019 and $nivel != 11) {
                    $fin = 9;
                }


                for($i=$inicio;$i<=$fin;$i++){
                    $existe = false;

                    foreach ($cualitativas as $c) {
                    
                        if($c->getNotaTipo()->getId() == $i){
                            $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                         'idEstudianteNotaCualitativa'=>$c->getId(),
                                                         'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                         'notaCualitativa'=>$c->getNotaCualitativa(),
                                                         'notaCuantitativa'=>$c->getNotaCuantitativa(),
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                         'idFila'=>$idInscripcion.''.$i
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
                                                     'notaCuantitativaNueva'=>'',
                                                     'notaCualitativaNueva'=>'',
                                                     'notaTipo'=>$this->literal($i)['titulo'],
                                                     'idFila'=>$idInscripcion.''.$i
                                                    );
                        $existe = true;
                    }
                }

                // VERIFICAMOS SI LA GESTION ES MAYOR O IGUAL A 2019
                // Y NIVEL PRIMARIA Y SECUNDARIA PARA AGREGARLE EL PROMEDIO ANUAL
                // if ($gestion >= 2019 and ($nivel == 13 or $nivel == 12) and $operativo >= 4) {     PROMEDIO ANUAL TAMBIEN PARA SECUNDARIA
                /*if ($gestion >= 2019 and $nivel == 12 and $operativo >= 3) {
                    $existe = false;
                    foreach ($cualitativas as $c) {
                        if($c->getNotaTipo()->getId() == 5){
                            $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                         'idEstudianteNotaCualitativa'=>$c->getId(),
                                                         'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                         'notaCualitativa'=>$c->getNotaCualitativa(),
                                                         'notaCuantitativa'=>$c->getNotaCuantitativa(),
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                         'idFila'=>$idInscripcion.'9'
                                                        );
                            $existe = true;
                            if($c->getNotaCualitativa() == ""){
                                // $cantidadFaltantes++;
                            }
                        }
                    }
                    if($existe == false){
                        // $cantidadFaltantes++;
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>'nuevo',
                                                     'idNotaTipo'=>9,
                                                     'notaCualitativa'=>'',
                                                     'notaCuantitativa'=>'',
                                                     'notaCuantitativaNueva'=>'',
                                                     'notaCualitativaNueva'=>'',
                                                     'notaTipo'=>'Promedio anual',
                                                     'idFila'=>$idInscripcion.'9'
                                                    );
                        $existe = true;
                    }
                }*/
            }

            $estadosPermitidos = $this->estadosPermitidos;

            return array(
                'cuantitativas'=>$areas,
                'cualitativas'=>$arrayCualitativas,
                'operativo'=>$operativo,
                'operativoTrue'=>$operativoTrue,
                'nivel'=>$nivel,
                'estadoMatricula'=>$inscripcion->getEstadomatriculaTipo()->getId(),
                'gestionActual'=>$this->session->get('currentyear'),
                'idInscripcion'=>$idInscripcion,
                'gestion'=>$gestion,
                'grado'=>$grado,
                'tipoNota'=>$tipo,
                'estadosPermitidos'=>$estadosPermitidos,
                'cantidadRegistrados'=>$cantidadRegistrados,
                'cantidadFaltantes'=>$cantidadFaltantes,
                'tipoSubsistema'=>$tipoSubsistema,
                'titulosNotas'=>$titulos_notas
            );

    }  

    public function regularDB11($idInscripcion,$operativo){
        try {
            $operativoTrue = $operativo;
            $tipoSubsistema = $this->session->get('tiposubsistema');

            $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

            // Cantidad de notas faltantes
            $cantidadFaltantes = 0;
            $cantidadRegistrados = 0;

            $tipoNota = $this->getTipoNota($sie,$gestion,$nivel,$grado,'');
            
            if($tipoNota == 'Trimestre'){
                
                if(in_array($gestion, array(2021))){
                    
                    //$operativo = ($nivel==11 or ($nivel ==12 && $grado == 1) or ($nivel ==403 && $grado == 1) or ($nivel ==404 && $grado == 1))?1:3;
                    //$operativo = 1;
                    //the new ini
                    vuelveX:
              
                        // REALIZAMOS LA VUELTA COMPLETA PARA OBTENER LAS MATERIAS CORRECTAS
                        $asignaturas = $this->em->createQueryBuilder()
                                    ->select('at.id, at.area, asit.id as asignaturaId, asit.asignatura, ea.id as estAsigId')
                                    ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
                                    ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.institucioneducativaCurso = iec.id')
                                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','WITH','ieco.insitucioneducativaCurso = iec.id')
                                    ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','WITH','ea.estudianteInscripcion = ei.id and ea.institucioneducativaCursoOferta = ieco.id')
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

                    
                    $nuevaArea = false;
                    foreach ($cursoOferta as $co) {
                        // LA MATERIA TECNICA GENERAL Y ESPECILIZADA NO SE AGREGAN AUTOMATICAMENTE A TODOS LOS ESTUDIANTES A PARTIR DE LA GESTION 2019
                        if(!in_array($co->getAsignaturaTipo()->getId(), $arrayAsignaturasEstudiante) and ($gestion < 2019 or ($gestion >= 2019 and $nivel == 13 and $grado >= 3 and $co->getAsignaturaTipo()->getId() != 1038 and $co->getAsignaturaTipo()->getId() != 1039) or ($gestion >= 2019 and $nivel != 13) or ($gestion >= 2019 and $nivel == 13 and $grado<3))) {

                            // Si no existe la asignatura, registramos la asignatura para el maestro
                            $newEstAsig = new EstudianteAsignatura();
                            $newEstAsig->setGestionTipo($this->em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                            $newEstAsig->setFechaRegistro(new \DateTime('now'));
                            $newEstAsig->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                            $newEstAsig->setInstitucioneducativaCursoOferta($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId()));
                            $this->em->persist($newEstAsig);
                            $this->em->flush();
                            $nuevaArea = true;

                            // Registro de materia para estudiantes estudiante_asignatura en el log
                            $arrayEstAsig = [];
                            $arrayEstAsig['id'] = $newEstAsig->getId();
                            $arrayEstAsig['gestionTipo'] = $newEstAsig->getGestionTipo()->getId();
                            $arrayEstAsig['fechaRegistro'] = $newEstAsig->getFechaRegistro()->format('d-m-Y');
                            $arrayEstAsig['estudianteInscripcion'] = $newEstAsig->getEstudianteInscripcion()->getId();
                            $arrayEstAsig['institucioneducativaCursoOferta'] = $newEstAsig->getInstitucioneducativaCursoOferta()->getId();
                            
                            $this->funciones->setLogTransaccion(
                                $newEstAsig->getId(),
                                'estudiante_asignatura',
                                'C',
                                '',
                                $arrayEstAsig,
                                '',
                                'ACADEMICO',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );
                        }
                    }

                    // Volvemos atras si se adiciono alguna nueva materia o asignatura
                    if($nuevaArea == true){
                        goto vuelveX;
                    }

                    //dump($asignaturas);die;
                    $notasArray = array();
                    $cont = 0;

                  switch ($operativo) {
                        case 0:
                            $inicio = 6;
                            $fin = 0;
                            break;
                        case 1:
                            $inicio = 6;
                            $fin = $inicio;
                            break;
                        case 4:
                            $inicio = 6;
                            $fin = 8;
                            break;
                        default:
                            $inicio = 6;
                            $fin = $inicio+($operativo-1);
                            break;
                    }


                    if($this->session->get('ue_modular') == true and $nivel == 13){
                        $operativo = 3;
                        $fin = 3;
                        if( in_array($gestion, array(2020,2021)) ){
                            $inicio = 6;
                            $fin = 8;
                        }                        
                    }
                    //to validate info consolidation
                    $arrConsolidation = array('6'=>'bim1','7'=>'bim2','8'=>'bim3','9'=>'bim4');
                    foreach ($asignaturas as $a) {
                     //dump($a);
                        // Concatenamos la especialidad si se tiene registrado
                        $nombreAsignatura = $a['asignatura'];
                        if($a['asignaturaId'] == 1039){
                            $especialidad = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion'=>$idInscripcion));
                            if($especialidad){
                                $nombreAsignatura = $a['asignatura'].':'.$especialidad->getEspecialidadTecnicoHumanisticoTipo()->getEspecialidad();
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
                                            ->setParameter('estAsigId',$a['estAsigId'])
                                            ->getQuery()
                                            ->getResult();

                                             //dump($asignaturasNotas);
                                             //dump($inicio);
                                             //dump($fin);
                                            // die;

                        // EN LA GESTION 2019 INICIAL NO SE REGISTRARAN LAS NOTAS POR MATERIA
                        //if (($gestion < 2019) or ($gestion >= 2019 and $nivel != 11) ) {
                        if (($gestion < 2019) or ($gestion >= 2019 ) ) {
                            for($i=$inicio;$i<=$fin;$i++){
                                // check if the operatvie was consolidate
                                $swCloseOperative = false;
                                if($this->em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa'=>$sie, 'gestion'=>$gestion, "$arrConsolidation[$i]"=> 1))){
                                    $swCloseOperative = true;
                                }
                                $existe = 'no';
                                foreach ($asignaturasNotas as $an) {
                                    if($i == $an['idNotaTipo']){
                                        // dump($nivel);
                                        // dump($grado);
                                        if($nivel != 11 and $nivel != 1 and $nivel != 403 and $nivel != 11 and ($nivel.$grado !=4041)){
                                            $valorNota = $an['notaCuantitativa'];
                                            if($valorNota == 0 or $valorNota == "0"){
                                                $cantidadFaltantes++;
                                            }else{
                                                $cantidadRegistrados++;
                                            }
                                        }else{
                                            $valorNota = $an['notaCualitativa'];
                                            if($valorNota == ""){
                                                $cantidadFaltantes++;
                                            }else{
                                                $cantidadRegistrados++;
                                            }
                                        }
                                        $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont."-".$i,
                                                                'idEstudianteNota'=>$an['idNota'],
                                                                'nota'=>$valorNota,
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>$an['idNotaTipo'],
                                                                'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                                'bimestre'=>$an['notaTipo'],
                                                                'idFila'=>$a['asignaturaId'].''.$i,
                                                                'swCloseOperative'=>$swCloseOperative
                                                            );
                                        $existe = 'si';
                                        break;
                                    }

                                } //dump($valorNota);
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
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>$i,
                                                                'idEstudianteAsignatura'=>$a['estAsigId'],
                                                                'bimestre'=>$this->literal($i)['titulo'],
                                                                'idFila'=>$a['asignaturaId'].''.$i,
                                                                'swCloseOperative'=>$swCloseOperative
                                                            );
                                }
                            }
                            /**
                             * PROMEDIOS
                             */

                            if($nivel != 11 and $nivel != 1 and $nivel != 403 and $nivel != 404 and $operativo >= 3){
                                //new by krlos
                                 $idavg = ($gestion <= 2019)?"-5":"-9";
                                 
                                // Para el promedio
                                foreach ($asignaturasNotas as $an) {
                                    $existe = 'no';
                                    if(in_array($an['idNotaTipo'], array(9))){
                                        $notasArray[$cont]['notas'][] =   array(
                                                                    'id'=>$cont.$idavg,
                                                                    'idEstudianteNota'=>$an['idNota'],
                                                                    'nota'=>$an['notaCuantitativa'],
                                                                    'notaNueva'=>'',
                                                                    'notaCualitativaNueva'=>'',
                                                                    'idNotaTipo'=>$an['idNotaTipo'],
                                                                    'idEstudianteAsignatura'=>$an['idEstudianteAsignatura'],
                                                                    'bimestre'=>$an['notaTipo'],
                                                                    'idFila'=>$a['asignaturaId'].$idavg,
                                                                    'swCloseOperative'=>true 
                                                                );
                                        $existe = 'si';
                                        break;
                                    }
                                }
                                if($existe == 'no'){

                                    $notasArray[$cont]['notas'][] =   array(
                                                                'id'=>$cont.$idavg ,
                                                                'idEstudianteNota'=>'nuevo',
                                                                'nota'=>'',
                                                                'notaNueva'=>'',
                                                                'notaCualitativaNueva'=>'',
                                                                'idNotaTipo'=>9,
                                                                'idEstudianteAsignatura'=>$a['estAsigId'],
                                                                'bimestre'=>'Promedio',
                                                                'idFila'=>$a['asignaturaId'].$idavg,
                                                                'swCloseOperative'=>true
                                                            );
                                }
                                
                            }
                        }                                                                    

                                               
                        $cont++;
                    }
                    //die;
                    //die;
                    $areas = array();
                    /*if($conArea == true){
                        foreach ($notasArray as $n) {
                            $areas[$n['area']][] = $n;
                        }
                    }else{*/
                        $areas = $notasArray;
                    //}
                    //dump($areas);die;
                    $tipo = 'newTemplateDB';

                    //the new end

                }

            }

            /*
            * Recorremos el array generado de las notas y recuperamos los titulos de las notas, para la tabla
            */
            $titulos_notas = array();
            if(count($notasArray)>0 and isset($notasArray[0]['notas'])){
                $titulos = $notasArray[0]['notas'];
                for($i=0;$i<count($titulos);$i++){
                    $titulos_notas[] = array('titulo'=>$titulos[$i]['bimestre']);
                }
            }

            //////////////////////
            //NOTAS CUALITATIVAS//
            //////////////////////
            $arrayCualitativas = array();
            $cualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion'=>$idInscripcion),array('notaTipo'=>'ASC'));

            //if($nivel == 11 or $nivel == 1 or $nivel == 403){
            if(($nivel == 11 or $nivel == 1 or $nivel == 403) ){
                // VERIFICAMOS SI EL OPERATIVO ES MAYOR O IGUAL A 4 PARA CARGAR LA NOTA CUALITATIVA ANUAL
                if (($operativo >= 4 and $gestion<2020 ) or (in_array($gestion, array(2021)) )) {
                    // Para inicial
                    $existe = false;
                    //dump($cualitativas);die;
                    foreach ($cualitativas as $c) {
                        if($c->getNotaTipo()->getId() == 18){
                            $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                         'idEstudianteNotaCualitativa'=>$c->getId(),
                                                         'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                         'notaCualitativa'=>$c->getNotaCualitativa(),
                                                         'notaCuantitativa'=>$c->getNotaCuantitativa(),
                                                         'notaCuantitativaNueva'=>'',
                                                         'notaCualitativaNueva'=>'',
                                                         'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                         'idFila'=>$idInscripcion.'18'
                                                        );
                            $existe = true;
                            if($c->getNotaCualitativa() == ""){
                                // $cantidadFaltantes++;
                            }
                        }
                    }
                    $conditionAvg = ($nivel == 11 && $gestion == 2021)?$operativo >= 1:$operativo >= 3;
                    if($existe == false and $conditionAvg){
                        // $cantidadFaltantes++;
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>'nuevo',
                                                     'idNotaTipo'=>18,
                                                     'notaCualitativa'=>'',
                                                     'notaCuantitativa'=>'',
                                                     'notaCuantitativaNueva'=>'',
                                                     'notaCualitativaNueva'=>'',
                                                     'notaTipo'=>$this->literal(18)['titulo'],
                                                     'idFila'=>$idInscripcion.'18'
                                                    );
                        $existe = true;
                    }
                }

            }

            $estadosPermitidos = $this->estadosPermitidos;

            $arrResponse = array(
                'cuantitativas'=>$areas,
                'cualitativas'=>$arrayCualitativas,
                'operativo'=>$operativo,
                'operativoTrue'=>$operativoTrue,
                'nivel'=>$nivel,
                'estadoMatricula'=>$inscripcion->getEstadomatriculaTipo()->getId(),
                'gestionActual'=>$this->session->get('currentyear'),
                'idInscripcion'=>$idInscripcion,
                'gestion'=>$gestion,
                'grado'=>$grado,
                'tipoNota'=>$tipo,
                'estadosPermitidos'=>$estadosPermitidos,
                'cantidadRegistrados'=>$cantidadRegistrados,
                'cantidadFaltantes'=>$cantidadFaltantes,
                'tipoSubsistema'=>$tipoSubsistema,
                'titulosNotas'=>$titulos_notas
            );
            
            return $arrResponse;
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

    public function literal($idNota)
    {
        $bim = -1;
        if($idNota!=null)
        {
            $tipoNota = $this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNota);
            $bim = array('titulo'=>$tipoNota->getNotaTipo(),'abrev'=>$tipoNota->getAbrev());
        }
        return $bim;
    }

    public function literalNum($num){
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
die;/*
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
            $arrinfoUe = unserialize($request->get('infoUe')); 
            
            /**
             * Para las notas BIMESTRALES
             */
            if($tipo == 'Bimestre' or $tipo == 'trimestre2020'){
                // Registro y/o modificacion de notas
                for($i=0;$i<count($idEstudianteNota);$i++) {
                    if($idEstudianteNota[$i] == 'nuevo'){
                        if(($nivel != 11 and $notas[$i] != 0) or ($nivel == 11 and $notas[$i] != "") or ($nivel.$arrinfoUe['ueducativaInfoId']['gradoId'] == '121')){
                            $query = $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();
                            $newNota = new EstudianteNota();
                            if($idNotaTipo[$i]==5){
                                $newNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find(9));
                            }else{
                                $newNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$i]));    
                            }
                            
                            $newNota->setEstudianteAsignatura($this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                            if($nivel == 11 or ($nivel.$arrinfoUe['ueducativaInfoId']['gradoId'] == '121')){
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

                            // Registro de notas estudiante en el log
                            $arrayNota = [];
                            $arrayNota['id'] = $newNota->getId();
                            $arrayNota['notaTipo'] = $newNota->getNotaTipo()->getId();
                            $arrayNota['estudianteAsignatura'] = $newNota->getEstudianteAsignatura()->getId();
                            $arrayNota['notaCuantitativa'] = $newNota->getNotaCuantitativa();
                            $arrayNota['notaCualitativa'] = $newNota->getNotaCualitativa();
                            $arrayNota['fechaRegistro'] = $newNota->getFechaRegistro()->format('d-m-Y');
                            $arrayNota['fechaModificacion'] = $newNota->getFechaModificacion()->format('d-m-Y');
                            
                            $this->funciones->setLogTransaccion(
                                $newNota->getId(),
                                'estudiante_nota',
                                'C',
                                '',
                                $arrayNota,
                                '',
                                'SERVICIO NOTAS',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );
                        }
                    }else{
                        $updateNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
                        // $regAnterior = clone $updateNota;
                        if($updateNota){
                            if($nivel == 11 or ($nivel.$arrinfoUe['ueducativaInfoId']['gradoId'] == '121')){
                                // Verificamos si la nota fue modificada o no para guardar los datos del usuario
                                if($updateNota->getNotaCualitativa() != mb_strtoupper($notas[$i],'utf-8') and $notas[$i] != ""){
                                    
                                    // Registro de notas estudiante en el log
                                    $anterior = [];
                                    $anterior['id'] = $updateNota->getId();
                                    $anterior['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                    $anterior['notaCualitativa'] = $updateNota->getNotaCualitativa();
                                    $anterior['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';


                                    $updateNota->setFechaModificacion(new \DateTime('now'));
                                    $updateNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                                    $this->em->flush();

                                    // Registro de notas estudiante en el log
                                    $nuevo = [];
                                    $nuevo['id'] = $updateNota->getId();
                                    $nuevo['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                    $nuevo['notaCualitativa'] = $updateNota->getNotaCualitativa();
                                    $nuevo['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';
                                    
                                    $this->funciones->setLogTransaccion(
                                        $updateNota->getId(),
                                        'estudiante_nota',
                                        'U',
                                        '',
                                        $nuevo,
                                        $anterior,
                                        'SERVICIO NOTAS',
                                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                    );
                                }
                            }else{
                                // Verificamos si la nota fue modificada o no para guardar los datos del usuario
                                if($updateNota->getNotaCuantitativa() != $notas[$i] and $notas[$i] != 0 and $notas[$i] > 0 and $notas[$i] <= 100 ){
                                    
                                    $anterior = [];
                                    $anterior['id'] = $updateNota->getId();
                                    $anterior['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                    $anterior['notaCuantitativa'] = $updateNota->getNotaCuantitativa();
                                    $anterior['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';

                                    $updateNota->setFechaModificacion(new \DateTime('now'));
                                    $updateNota->setNotaCuantitativa($notas[$i]);
                                    $this->em->flush();

                                    $nuevo = [];
                                    $nuevo['id'] = $updateNota->getId();
                                    $nuevo['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                    $nuevo['notaCuantitativa'] = $updateNota->getNotaCuantitativa();
                                    $nuevo['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';

                                    $this->funciones->setLogTransaccion(
                                        $updateNota->getId(),
                                        'estudiante_nota',
                                        'U',
                                        '',
                                        $nuevo,
                                        $anterior,
                                        'SERVICIO NOTAS',
                                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                    );
                                }
                            }
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

                            if ($gestion >= 2019 and ( ($nivel == 12 and $arrinfoUe['ueducativaInfoId']['gradoId']>1 )or $nivel == 13)) {
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

                            /// Registro de notas estudiante en el log
                            $arrayNotaCualitativa = [];
                            $arrayNotaCualitativa['id'] = $newCualitativa->getId();
                            $arrayNotaCualitativa['notaTipo'] = $newCualitativa->getNotaTipo()->getId();
                            $arrayNotaCualitativa['estudianteInscripcion'] = $newCualitativa->getEstudianteInscripcion()->getId();
                            $arrayNotaCualitativa['notaCuantitativa'] = $newCualitativa->getNotaCuantitativa();
                            $arrayNotaCualitativa['notaCualitativa'] = $newCualitativa->getNotaCualitativa();
                            $arrayNotaCualitativa['fechaRegistro'] = $newCualitativa->getFechaRegistro()->format('d-m-Y');
                            $arrayNotaCualitativa['fechaModificacion'] = $newCualitativa->getFechaModificacion()->format('d-m-Y');
                            
                            $this->funciones->setLogTransaccion(
                                $newCualitativa->getId(),
                                'estudiante_nota_cualitativa',
                                'C',
                                '',
                                $arrayNotaCualitativa,
                                '',
                                'SERVICIO NOTAS',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );
                        }
                    }else{
                        $updateCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaCualitativa[$j]);
                        if($updateCualitativa){
                            // Verificamos si la nota fue modificada para guardar los datos del usuario que lo modifico
                            if($updateCualitativa->getNotaCualitativa() != mb_strtoupper($notaCualitativa[$j],'utf-8')){

                                /// Registro de notas estudiante en el log
                                $anterior = [];
                                $anterior['id'] = $updateCualitativa->getId();
                                $anterior['notaTipo'] = $updateCualitativa->getNotaTipo()->getId();
                                $anterior['notaCuantitativa'] = $updateCualitativa->getNotaCuantitativa();
                                $anterior['notaCualitativa'] = $updateCualitativa->getNotaCualitativa();
                                $anterior['fechaModificacion'] = ($updateCualitativa->getFechaModificacion())?$updateCualitativa->getFechaModificacion()->format('d-m-Y'):'';


                                $updateCualitativa->setUsuarioId($this->session->get('userId'));
                                $updateCualitativa->setFechaModificacion(new \DateTime('now'));

                                if ($gestion >= 2019 and ($nivel == 12 or $nivel == 13)) {
                                    $updateCualitativa->setNotaCuantitativa($notaCualitativa[$j]);
                                    $updateCualitativa->setNotaCualitativa('');
                                } else {
                                    $updateCualitativa->setNotaCuantitativa(0);
                                    $updateCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                                }

                                $this->em->persist($updateCualitativa);
                                $this->em->flush();

                                $nuevo = [];
                                $nuevo['id'] = $updateCualitativa->getId();
                                $nuevo['notaTipo'] = $updateCualitativa->getNotaTipo()->getId();
                                $nuevo['notaCuantitativa'] = $updateCualitativa->getNotaCuantitativa();
                                $nuevo['notaCualitativa'] = $updateCualitativa->getNotaCualitativa();
                                $nuevo['fechaModificacion'] = ($updateCualitativa->getFechaModificacion())?$updateCualitativa->getFechaModificacion()->format('d-m-Y'):'';
                                
                                $this->funciones->setLogTransaccion(
                                    $updateCualitativa->getId(),
                                    'estudiante_nota_cualitativa',
                                    'U',
                                    '',
                                    $nuevo,
                                    $anterior,
                                    'SERVICIO NOTAS',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                );
                            }

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

                            // Registro de notas estudiante en el log
                            $arrayNota = [];
                            $arrayNota['id'] = $newNota->getId();
                            $arrayNota['notaTipo'] = $newNota->getNotaTipo()->getId();
                            $arrayNota['estudianteAsignatura'] = $newNota->getEstudianteAsignatura()->getId();
                            $arrayNota['notaCuantitativa'] = $newNota->getNotaCuantitativa();
                            $arrayNota['notaCualitativa'] = $newNota->getNotaCualitativa();
                            $arrayNota['fechaRegistro'] = $newNota->getFechaRegistro()->format('d-m-Y');
                            $arrayNota['fechaModificacion'] = $newNota->getFechaModificacion()->format('d-m-Y');
                            
                            $this->funciones->setLogTransaccion(
                                $newNota->getId(),
                                'estudiante_nota',
                                'C',
                                '',
                                $arrayNota,
                                '',
                                'SERVICIO NOTAS',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );
                        }
                    }else{
                        $updateNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
                        if($updateNota){
                            if($notas[$i] != 0){

                                if($nivel == 11 or $nivel == 1 or $nivel == 403){
                                    // Registro de notas estudiante en el log
                                    $anterior = [];
                                    $anterior['id'] = $updateNota->getId();
                                    $anterior['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                    $anterior['notaCualitativa'] = $updateNota->getNotaCualitativa();
                                    $anterior['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';

                                    $updateNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                                    $updateNota->setUsuarioId($this->session->get('userId'));
                                    $updateNota->setFechaModificacion(new \DateTime('now'));
                                    $this->em->persist($updateNota);
                                    $this->em->flush();

                                    // Registro de notas estudiante en el log
                                    $nuevo = [];
                                    $nuevo['id'] = $updateNota->getId();
                                    $nuevo['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                    $nuevo['notaCualitativa'] = $updateNota->getNotaCualitativa();
                                    $nuevo['fechaRegistro'] = $updateNota->getFechaRegistro()->format('d-m-Y');
                                    $nuevo['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';

                                }else{
                                    // Registro de notas estudiante en el log
                                    $anterior = [];
                                    $anterior['id'] = $updateNota->getId();
                                    $anterior['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                    $anterior['notaCuantitativa'] = $updateNota->getNotaCuantitativa();
                                    $anterior['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';

                                    $updateNota->setNotaCuantitativa($notas[$i]);
                                    $updateNota->setUsuarioId($this->session->get('userId'));
                                    $updateNota->setFechaModificacion(new \DateTime('now'));
                                    $this->em->persist($updateNota);
                                    $this->em->flush();

                                    // Registro de notas estudiante en el log
                                    $nuevo = [];
                                    $nuevo['id'] = $updateNota->getId();
                                    $nuevo['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                    $nuevo['notaCuantitativa'] = $updateNota->getNotaCuantitativa();
                                    $nuevo['fechaRegistro'] = $updateNota->getFechaRegistro()->format('d-m-Y');
                                    $nuevo['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';
                                }

                                $this->funciones->setLogTransaccion(
                                    $updateNota->getId(),
                                    'estudiante_nota',
                                    'U',
                                    '',
                                    $nuevo,
                                    $anterior,
                                    'SERVICIO NOTAS',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                );

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

                                $anterior = [];
                                $anterior['id'] = $existeNotaCuantitativa->getId();
                                $anterior['notaTipo'] = $existeNotaCuantitativa->getNotaTipo()->getId();
                                $anterior['notaCuantitativa'] = $existeNotaCuantitativa->getNotaCuantitativa();
                                $anterior['fechaModificacion'] = ($existeNotaCuantitativa->getFechaModificacion())?$existeNotaCuantitativa->getFechaModificacion()->format('d-m-Y'):'';

                                $existeNotaCuantitativa->setNotaCualitativa(mb_strtoupper($notasC[$j],'utf-8'));
                                $existeNotaCuantitativa->setUsuarioId($this->session->get('userId'));
                                $existeNotaCuantitativa->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($existeNotaCuantitativa);
                                $this->em->flush();

                                // Registro de notas estudiante en el log
                                $nuevo = [];
                                $nuevo['id'] = $existeNotaCuantitativa->getId();
                                $nuevo['notaTipo'] = $existeNotaCuantitativa->getNotaTipo()->getId();
                                $nuevo['notaCuantitativa'] = $existeNotaCuantitativa->getNotaCuantitativa();
                                $nuevo['fechaModificacion'] = ($existeNotaCuantitativa->getFechaModificacion())?$existeNotaCuantitativa->getFechaModificacion()->format('d-m-Y'):'';
                                
                                $this->funciones->setLogTransaccion(
                                    $existeNotaCuantitativa->getId(),
                                    'estudiante_nota',
                                    'U',
                                    '',
                                    $nuevo,
                                    $anterior,
                                    'SERVICIO NOTAS',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                );
                            }

                        }else{
                            $updateNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNotaC[$j]);
                            if($updateNota){

                                $anterior = [];
                                $anterior['id'] = $updateNota->getId();
                                $anterior['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                $anterior['notaCuantitativa'] = $updateNota->getNotaCuantitativa();
                                $anterior['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';

                                $updateNota->setNotaCualitativa(mb_strtoupper($notasC[$j],'utf-8'));
                                $updateNota->setUsuarioId($this->session->get('userId'));
                                $updateNota->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($updateNota);
                                $this->em->flush();

                                // Registro de notas estudiante en el log
                                $nuevo = [];
                                $nuevo['id'] = $updateNota->getId();
                                $nuevo['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                $nuevo['notaCuantitativa'] = $updateNota->getNotaCuantitativa();
                                $nuevo['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';
                                
                                $this->funciones->setLogTransaccion(
                                    $updateNota->getId(),
                                    'estudiante_nota',
                                    'U',
                                    '',
                                    $nuevo,
                                    $anterior,
                                    'SERVICIO NOTAS',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                );
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

                            /// Registro de notas estudiante en el log
                            $arrayNotaCualitativa = [];
                            $arrayNotaCualitativa['id'] = $newCualitativa->getId();
                            $arrayNotaCualitativa['notaTipo'] = $newCualitativa->getNotaTipo()->getId();
                            $arrayNotaCualitativa['estudianteInscripcion'] = $newCualitativa->getEstudianteInscripcion()->getId();
                            $arrayNotaCualitativa['notaCuantitativa'] = $newCualitativa->getNotaCuantitativa();
                            $arrayNotaCualitativa['notaCualitativa'] = $newCualitativa->getNotaCualitativa();
                            $arrayNotaCualitativa['fechaRegistro'] = $newCualitativa->getFechaRegistro()->format('d-m-Y');
                            $arrayNotaCualitativa['fechaModificacion'] = $newCualitativa->getFechaModificacion()->format('d-m-Y');
                            
                            $this->funciones->setLogTransaccion(
                                $newCualitativa->getId(),
                                'estudiante_nota_cualitativa',
                                'C',
                                '',
                                $arrayNotaCualitativa,
                                '',
                                'SERVICIO NOTAS',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );
                        }else{
                            $updateCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaCualitativa[$j]);
                            if($updateCualitativa){

                                /// Registro de notas estudiante en el log
                                $anterior = [];
                                $anterior['id'] = $updateCualitativa->getId();
                                $anterior['notaTipo'] = $updateCualitativa->getNotaTipo()->getId();
                                $anterior['notaCualitativa'] = $updateCualitativa->getNotaCualitativa();
                                $anterior['fechaModificacion'] = ($updateCualitativa->getFechaModificacion())?$updateCualitativa->getFechaModificacion()->format('d-m-Y'):'';

                                $updateCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                                $updateCualitativa->setUsuarioId($this->session->get('userId'));
                                $updateCualitativa->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($updateCualitativa);
                                $this->em->flush();

                                /// Registro de notas estudiante en el log
                                $nuevo = [];
                                $nuevo['id'] = $updateCualitativa->getId();
                                $nuevo['notaTipo'] = $updateCualitativa->getNotaTipo()->getId();
                                $nuevo['notaCualitativa'] = $updateCualitativa->getNotaCualitativa();
                                $nuevo['fechaModificacion'] = ($updateCualitativa->getFechaModificacion())?$updateCualitativa->getFechaModificacion()->format('d-m-Y'):'';
                                
                                $this->funciones->setLogTransaccion(
                                    $updateCualitativa->getId(),
                                    'estudiante_nota_cualitativa',
                                    'U',
                                    '',
                                    $nuevo,
                                    $anterior,
                                    'SERVICIO NOTAS',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                );
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

    public function regularRegistroDB(Request $request){
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
die;/*
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
            $arrinfoUe = unserialize($request->get('infoUe')); 
            
            /**
             * Para las notas BIMESTRALES
             */
            if($tipo == 'newTemplateDB'){
                // Registro y/o modificacion de notas
                for($i=0;$i<count($idEstudianteNota);$i++) {
                    if($idEstudianteNota[$i] == 'nuevo'){
                        if(($nivel != 11 and $notas[$i] != 0) or ($nivel == 11 and $notas[$i] != "") ){
                            $query = $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();
                            $newNota = new EstudianteNota();
                            if($idNotaTipo[$i]==9){
                                $newNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find(9));
                            }else{
                                $newNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$i]));    
                            }
                            
                            $newNota->setEstudianteAsignatura($this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                            if($nivel == 11 ){
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

                            // Registro de notas estudiante en el log
                            $arrayNota = [];
                            $arrayNota['id'] = $newNota->getId();
                            $arrayNota['notaTipo'] = $newNota->getNotaTipo()->getId();
                            $arrayNota['estudianteAsignatura'] = $newNota->getEstudianteAsignatura()->getId();
                            $arrayNota['notaCuantitativa'] = $newNota->getNotaCuantitativa();
                            $arrayNota['notaCualitativa'] = $newNota->getNotaCualitativa();
                            $arrayNota['fechaRegistro'] = $newNota->getFechaRegistro()->format('d-m-Y');
                            $arrayNota['fechaModificacion'] = $newNota->getFechaModificacion()->format('d-m-Y');
                            
                            $this->funciones->setLogTransaccion(
                                $newNota->getId(),
                                'estudiante_nota',
                                'C',
                                '',
                                $arrayNota,
                                '',
                                'SERVICIO NOTAS',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );
                        }
                    }else{
                        $updateNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
                        // $regAnterior = clone $updateNota;
                        if($updateNota){
                            if($nivel == 11 ){
                                // Verificamos si la nota fue modificada o no para guardar los datos del usuario
                                if($updateNota->getNotaCualitativa() != mb_strtoupper($notas[$i],'utf-8') and $notas[$i] != ""){
                                    
                                    // Registro de notas estudiante en el log
                                    $anterior = [];
                                    $anterior['id'] = $updateNota->getId();
                                    $anterior['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                    $anterior['notaCualitativa'] = $updateNota->getNotaCualitativa();
                                    $anterior['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';


                                    $updateNota->setFechaModificacion(new \DateTime('now'));
                                    $updateNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                                    $this->em->flush();

                                    // Registro de notas estudiante en el log
                                    $nuevo = [];
                                    $nuevo['id'] = $updateNota->getId();
                                    $nuevo['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                    $nuevo['notaCualitativa'] = $updateNota->getNotaCualitativa();
                                    $nuevo['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';
                                    
                                    $this->funciones->setLogTransaccion(
                                        $updateNota->getId(),
                                        'estudiante_nota',
                                        'U',
                                        '',
                                        $nuevo,
                                        $anterior,
                                        'SERVICIO NOTAS',
                                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                    );
                                }
                            }else{
                                // Verificamos si la nota fue modificada o no para guardar los datos del usuario
                                if($updateNota->getNotaCuantitativa() != $notas[$i] and $notas[$i] != 0 and $notas[$i] > 0 and $notas[$i] <= 100 ){
                                    
                                    $anterior = [];
                                    $anterior['id'] = $updateNota->getId();
                                    $anterior['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                    $anterior['notaCuantitativa'] = $updateNota->getNotaCuantitativa();
                                    $anterior['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';

                                    $updateNota->setFechaModificacion(new \DateTime('now'));
                                    $updateNota->setNotaCuantitativa($notas[$i]);
                                    $this->em->flush();

                                    $nuevo = [];
                                    $nuevo['id'] = $updateNota->getId();
                                    $nuevo['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                    $nuevo['notaCuantitativa'] = $updateNota->getNotaCuantitativa();
                                    $nuevo['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';

                                    $this->funciones->setLogTransaccion(
                                        $updateNota->getId(),
                                        'estudiante_nota',
                                        'U',
                                        '',
                                        $nuevo,
                                        $anterior,
                                        'SERVICIO NOTAS',
                                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                    );
                                }
                            }
                        }
                    }
                }

                // Registro de notas cualitativas de incial primaria yo secundaria                
                for($j=0;$j<count($idEstudianteNotaCualitativa);$j++){
                    
                    if($idEstudianteNotaCualitativa[$j] == 'nuevo'){
                        if($notaCualitativa[$j] != ""){
                            $query = $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota_cualitativa');")->execute();

                            $newCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion));

                            // if(sizeof($newCualitativa)>0){
                            // }else{
                                $newCualitativa = new EstudianteNotaCualitativa();
                            // }

                            $newCualitativa->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipoCualitativa[$j]));
                            $newCualitativa->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));

                            if ($gestion >= 2019 and ( ($nivel == 12 and $arrinfoUe['ueducativaInfoId']['gradoId']>=1 )/*or $nivel == 13*/)) {
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

                            /// Registro de notas estudiante en el log
                            $arrayNotaCualitativa = [];
                            $arrayNotaCualitativa['id'] = $newCualitativa->getId();
                            $arrayNotaCualitativa['notaTipo'] = $newCualitativa->getNotaTipo()->getId();
                            $arrayNotaCualitativa['estudianteInscripcion'] = $newCualitativa->getEstudianteInscripcion()->getId();
                            $arrayNotaCualitativa['notaCuantitativa'] = $newCualitativa->getNotaCuantitativa();
                            $arrayNotaCualitativa['notaCualitativa'] = $newCualitativa->getNotaCualitativa();
                            $arrayNotaCualitativa['fechaRegistro'] = $newCualitativa->getFechaRegistro()->format('d-m-Y');
                            $arrayNotaCualitativa['fechaModificacion'] = $newCualitativa->getFechaModificacion()->format('d-m-Y');
                            
                            $this->funciones->setLogTransaccion(
                                $newCualitativa->getId(),
                                'estudiante_nota_cualitativa',
                                'C',
                                '',
                                $arrayNotaCualitativa,
                                '',
                                'SERVICIO NOTAS',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );
                        }
                    }else{
                        $updateCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaCualitativa[$j]);
                        if($updateCualitativa){
                            // Verificamos si la nota fue modificada para guardar los datos del usuario que lo modifico
                            if($updateCualitativa->getNotaCualitativa() != mb_strtoupper($notaCualitativa[$j],'utf-8')){

                                /// Registro de notas estudiante en el log
                                $anterior = [];
                                $anterior['id'] = $updateCualitativa->getId();
                                $anterior['notaTipo'] = $updateCualitativa->getNotaTipo()->getId();
                                $anterior['notaCuantitativa'] = $updateCualitativa->getNotaCuantitativa();
                                $anterior['notaCualitativa'] = $updateCualitativa->getNotaCualitativa();
                                $anterior['fechaModificacion'] = ($updateCualitativa->getFechaModificacion())?$updateCualitativa->getFechaModificacion()->format('d-m-Y'):'';


                                $updateCualitativa->setUsuarioId($this->session->get('userId'));
                                $updateCualitativa->setFechaModificacion(new \DateTime('now'));


                                if ($gestion >= 2019 and ($nivel == 12 /*or $nivel == 13*/)) {
                                    $updateCualitativa->setNotaCuantitativa($notaCualitativa[$j]);
                                    $updateCualitativa->setNotaCualitativa('');
                                } else {
                                    $updateCualitativa->setNotaCuantitativa(0);
                                    $updateCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                                }

                                $this->em->persist($updateCualitativa);
                                $this->em->flush();

                                $nuevo = [];
                                $nuevo['id'] = $updateCualitativa->getId();
                                $nuevo['notaTipo'] = $updateCualitativa->getNotaTipo()->getId();
                                $nuevo['notaCuantitativa'] = $updateCualitativa->getNotaCuantitativa();
                                $nuevo['notaCualitativa'] = $updateCualitativa->getNotaCualitativa();
                                $nuevo['fechaModificacion'] = ($updateCualitativa->getFechaModificacion())?$updateCualitativa->getFechaModificacion()->format('d-m-Y'):'';
                                
                                $this->funciones->setLogTransaccion(
                                    $updateCualitativa->getId(),
                                    'estudiante_nota_cualitativa',
                                    'U',
                                    '',
                                    $nuevo,
                                    $anterior,
                                    'SERVICIO NOTAS',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                );
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

            // ARRAY PARA EL LOG
            $anterior = [];
            $anterior['id'] = $inscripcion->getId();
            $anterior['estadoMatricula'] = $inscripcion->getEstadomatriculaTipo()->getId();
            ////////////////////

            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();

            $gestionActual = $this->session->get('currentyear');

            //$operativo = $this->funciones->obtenerOperativo($sie,$gestion);
            $operativo = $this->funciones->obtenerOperativoTrimestre2020($sie,$gestion);
            
            /*
            if($gestion <= 2012 or ($gestion == 2013 and $grado > 1 and $nivel != 11) or ($gestion == 2013 and $grado == (1 or 2 ) and $nivel == 11) ){
                $tipo = 't';
            }else{
                $tipo = 'b';
            }*/

            $tipo = $this->getTipoNota($sie,$gestion,$nivel,$grado,'');

            // ACTUALIZAMOS EL ESTADO DE MATRICULA DE EDUCACION INICIAL A PROMOVIDO
            if($nivel == 11 or $nivel == 1 or $nivel == 403 or ($nivel.$grado == 121)){
                // SE ACTUALIZA EL ESTADO DE MATRICULA SI EL OPERATIVO ACTUAL ES MAYOR A 4TO BIMESTRE
                //if($operativo >= 4){
                if($operativo >= 1){
                    $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

                    $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(5));
                    $this->em->persist($inscripcion);
                    $this->em->flush();

                    $nuevo = [];
                    $nuevo['id'] = $inscripcion->getId();
                    $nuevo['estadoMatricula'] = $inscripcion->getEstadomatriculaTipo()->getId();       

                    if ($anterior['estadoMatricula'] != $nuevo['estadoMatricula']) {
                        $this->funciones->setLogTransaccion(
                            $inscripcion->getId(),
                            'estudiante_inscripcion',
                            'U',
                            '',
                            $nuevo,
                            $anterior,
                            'SERVICIO NOTAS - ESTADO MATRICULA',
                            json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                        );
                    }

                }
            }else{
                // ACTUALIZAMOS EL ESTADO DE MATRICULA DE PRIMARIA Y SECUNDARIA
                $asignaturas = $this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findby(array('estudianteInscripcion'=>$idInscripcion));
                
                $arrayPromedios = array();
                foreach ($asignaturas as $a) {
                    // Notas Bimestrales
                    if($tipo == 'Bimestre' or ($tipo == 'Trimestre' and $gestion == 2020)){
                        //$notaPromedio = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>5));
                        $notaPromedio = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>9));
                        //$notaPromedio = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId()));
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
                        } else {
                            $arrayPromedios[] = 0; 
                        }
                    }
                    // Notas Trimestrales
                    if($tipo == 'Trimestre' and $gestion < 2020){
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
                    
                    if($inscripcion->getEstadomatriculaInicioTipo() != null and $inscripcion->getEstadomatriculaInicioTipo()->getId() == 29){
                        $nuevoEstado = 26; // promovido por postbachillerato
                    }else{
                        // VERIFICAMOS SI EL ESTADO DE MATRICULA ACTUAL ES
                        // 26 PROMOVIDO POST-BACHILLERATO
                        // 55 PROMOVIDO BACHILLER DE EXCELENCIA
                        // 57 PROMOVIDO POR REZAGO ESCOLAR
                        // 58 PROMOVIDO TALENTO EXTRAORDINARIO
                        // PARA NO MODIFICAR EL ESTADO DE MATRICULA ORIGINAL SI EL NUEVO ESTADO ES PROMOVIDO
                        if (in_array($inscripcion->getEstadomatriculaTipo()->getId(), [26,55,57,58])) {
                            $nuevoEstado = $inscripcion->getEstadomatriculaTipo()->getId();
                        }else{
                            $nuevoEstado = 5; // PROMOVIDO
                        }
                    }

                    if($tipo == 'Bimestre' or ($tipo == 'Trimestre' and $gestion == 2020) ){
                        $registroConsolidacion = $this->em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('gestion'=>2020,'unidadEducativa'=>$sie));
                        $cierreGestion2020 = false;
                        if(count($registroConsolidacion)>0){
                            if($registroConsolidacion->getBim1() != 0 and $registroConsolidacion->getBim2() != 0 ){
                                $cierreGestion2020 = true;
                            }
                        }
                        
                        // NO REALIZAMOS LA VERIFICACION DE LOS PROMEDIOS PARA PRIMARIA 
                        // A PARTIR DE LA GESTION 2019 DEBIDO A QUE LA PROMOCION SE
                        // DETERMINA CON EL PROMEDIO ANUAL
                        if($gestion == 2020 and $cierreGestion2020 == false){
                            if ($gestion < 2019 or ($gestion >= 2019 and $nivel != 12)) {
                                foreach ($arrayPromedios as $ap) {
                                    if($ap < 51){
                                        $nuevoEstado = 4;
                                        break;
                                    }
                                }   
                            }
                        } else {
                            if ($gestion < 2019 or ($gestion >= 2019 and $nivel != 12)) {
                                foreach ($arrayPromedios as $ap) {
                                    if($ap < 51){
                                        $nuevoEstado = 11;
                                        break;
                                    }
                                }   
                            }
                        }

                        
                    }
                    if($tipo == 'Trimestre'  and $gestion < 2020){
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
                                // CALCULAMOS EL PROMEDIO GENERAL PRIMARIA
                                $sumaPrimaria = 0;
                                foreach ($arrayPromedios as $ap) {
                                    $sumaPrimaria = $sumaPrimaria + $ap;
                                }
                                $promedioPrimaria = round($sumaPrimaria/count($arrayPromedios));

                                // OBTENEMOS EL REGISTRO DE LA NOTA PROMEDIO
                                $promedioGeneral = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array(
                                    'estudianteInscripcion'=>$inscripcion->getId(),
                                    'notaTipo'=>5
                                ));
                                
                                if($promedioGeneral){
                                    // SI EXISTE EL PROMEDIO GENERAL LO ACTUALIZAMOS
                                    $promedioGeneral = $this->modificarNotaCualitativa($promedioGeneral->getId(), '', $promedioPrimaria);
                                }else{
                                    // SI NO EXISTE LO REGISTRAMOS
                                    $promedioGeneral = $this->registrarNotaCualitativa(5, $idInscripcion, '', $promedioPrimaria);
                                }

                                if ($promedioGeneral->getNotaCuantitativa() < 51) {
                                    $nuevoEstado = 28; // ESTADO RETENIDO 28 - REEMPLAZA ESTADO REPROBADO 11
                                }

                                $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($nuevoEstado));
                                $this->em->persist($inscripcion);
                                $this->em->flush();
                                
                                /// Registro den log de estado de matricula
                                $nuevo = [];
                                $nuevo['id'] = $inscripcion->getId();
                                $nuevo['estadoMatricula'] = $inscripcion->getEstadomatriculaTipo()->getId();

                                if ($anterior['estadoMatricula'] != $nuevo['estadoMatricula']) {
                                    $this->funciones->setLogTransaccion(
                                        $inscripcion->getId(),
                                        'estudiante_inscripcion',
                                        'U',
                                        '',
                                        $nuevo,
                                        $anterior,
                                        'SERVICIO NOTAS - ESTADO MATRICULA',
                                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                    );
                                }

                            }else{
                                $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($nuevoEstado));
                                $this->em->persist($inscripcion);
                                $this->em->flush();

                                /// Registro den log de estado de matricula
                                $nuevo = [];
                                $nuevo['id'] = $inscripcion->getId();
                                $nuevo['estadoMatricula'] = $inscripcion->getEstadomatriculaTipo()->getId();  
                                if ($anterior['estadoMatricula'] != $nuevo['estadoMatricula']) {
                                    $this->funciones->setLogTransaccion(
                                        $inscripcion->getId(),
                                        'estudiante_inscripcion',
                                        'U',
                                        '',
                                        $nuevo,
                                        $anterior,
                                        'SERVICIO NOTAS - ESTADO MATRICULA',
                                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                    );
                                }
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


    
    public function actualizarEstadoMatriculaEspecial($idInscripcion){
        
        try {
            $this->em->getConnection()->beginTransaction();
            $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

            // ARRAY PARA EL LOG
            $anterior = [];
            $anterior['id'] = $inscripcion->getId();
            $anterior['estadoMatricula'] = $inscripcion->getEstadomatriculaTipo()->getId();
            ////////////////////

            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();

            $gestionActual = $this->session->get('currentyear');

            //$operativo = $this->funciones->obtenerOperativo($sie,$gestion);
            $operativo = $this->funciones->obtenerOperativoTrimestre2020($sie,$gestion);
            
            /*
            if($gestion <= 2012 or ($gestion == 2013 and $grado > 1 and $nivel != 11) or ($gestion == 2013 and $grado == (1 or 2 ) and $nivel == 11) ){
                $tipo = 't';
            }else{
                $tipo = 'b';
            }*/

            $tipo = $this->getTipoNota($sie,$gestion,$nivel,$grado,'');

            // ACTUALIZAMOS EL ESTADO DE MATRICULA DE EDUCACION INICIAL A PROMOVIDO
            if($nivel == 11 or $nivel == 1 or $nivel == 403 or ($nivel.$grado == 121)){ 
                // SE ACTUALIZA EL ESTADO DE MATRICULA SI EL OPERATIVO ACTUAL ES MAYOR A 4TO BIMESTRE
                //if($operativo >= 4){
                if($operativo >= 1){
                    $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

                    $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(5));
                    $this->em->persist($inscripcion);
                    $this->em->flush();

                    $nuevo = [];
                    $nuevo['id'] = $inscripcion->getId();
                    $nuevo['estadoMatricula'] = $inscripcion->getEstadomatriculaTipo()->getId();       

                    if ($anterior['estadoMatricula'] != $nuevo['estadoMatricula']) {
                        $this->funciones->setLogTransaccion(
                            $inscripcion->getId(),
                            'estudiante_inscripcion',
                            'U',
                            '',
                            $nuevo,
                            $anterior,
                            'SERVICIO NOTAS - ESTADO MATRICULA',
                            json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                        );
                    }

                }
            }else{
                // ACTUALIZAMOS EL ESTADO DE MATRICULA DE PRIMARIA Y SECUNDARIA
                $asignaturas = $this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findby(array('estudianteInscripcion'=>$idInscripcion));
                
                $arrayPromedios = array();
                
                foreach ($asignaturas as $a) {
                    // Notas Bimestrales
                    if($tipo == 'Bimestre' or ($tipo == 'Trimestre' and $gestion >= 2020)){ 
                        
                        //$notaPromedio = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>5));
                        $notaPromedio = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>9));
                        //$notaPromedio = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId()));
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
                        } else {
                            $arrayPromedios[] = 0; 
                        }
                    }
                    // Notas Trimestrales
                    if($tipo == 'Trimestre' and $gestion < 2020){
                        
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
                    
                    if($inscripcion->getEstadomatriculaInicioTipo() != null and $inscripcion->getEstadomatriculaInicioTipo()->getId() == 29){
                        $nuevoEstado = 26; // promovido por postbachillerato
                    }else{
                        // VERIFICAMOS SI EL ESTADO DE MATRICULA ACTUAL ES
                        // 26 PROMOVIDO POST-BACHILLERATO
                        // 55 PROMOVIDO BACHILLER DE EXCELENCIA
                        // 57 PROMOVIDO POR REZAGO ESCOLAR
                        // 58 PROMOVIDO TALENTO EXTRAORDINARIO
                        // PARA NO MODIFICAR EL ESTADO DE MATRICULA ORIGINAL SI EL NUEVO ESTADO ES PROMOVIDO
                        if (in_array($inscripcion->getEstadomatriculaTipo()->getId(), [26,55,57,58])) {
                            $nuevoEstado = $inscripcion->getEstadomatriculaTipo()->getId();
                        }else{
                            $nuevoEstado = 5; // PROMOVIDO
                        }
                    }
                    
                    if($tipo == 'Bimestre' or ($tipo == 'Trimestre' and $gestion >= 2020) ){
                        $registroConsolidacion = $this->em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('gestion'=>$gestion,'unidadEducativa'=>$sie));
                        $cierreGestion2020 = false;
                        if(count($registroConsolidacion)>0){
                            if($registroConsolidacion->getBim1() != 0 and $registroConsolidacion->getBim2() != 0 ){
                                $cierreGestion2020 = true;
                            }
                        }
                        
                        // NO REALIZAMOS LA VERIFICACION DE LOS PROMEDIOS PARA PRIMARIA 
                        // A PARTIR DE LA GESTION 2019 DEBIDO A QUE LA PROMOCION SE
                        // DETERMINA CON EL PROMEDIO ANUAL
                        if($gestion == 2020 and $cierreGestion2020 == false){
                            if ($gestion < 2019 or ($gestion >= 2019 and $nivel != 12)) {
                                foreach ($arrayPromedios as $ap) {
                                    if($ap < 51){
                                        $nuevoEstado = 4;
                                        break;
                                    }
                                }   
                            }
                        } else {


                            if ($gestion < 2019 or ($gestion >= 2019 and $nivel != 12)) {
                                foreach ($arrayPromedios as $ap) {
                                    if($ap < 51){
                                        $nuevoEstado = 11;
                                        break;
                                    }
                                }   
                            }
                        }
                        if($gestion > 2020 and $cierreGestion2020 == false){
                                foreach ($arrayPromedios as $ap) {
                                    if($ap < 51){
                                        $nuevoEstado = 4;
                                        break;
                                    }
                                }   
                        }
                        if(($gestion ==2020 or $gestion ==2021) and $cierreGestion2020 == true){
                            $nuevoEstado = 5;
                            $total = 0;
                            $cant = 0;
                            foreach ($arrayPromedios as $ap) {
                                    $total = $total + $ap;
                                    $cant++;
                            }   
                            $promedio = $total/$cant;
                                if(round($promedio)<51) {
                                    $nuevoEstado = 11;
                                }
                            
                        }
                        if($gestion == 2022 and $cierreGestion2020 == true){ 
                            $nuevoEstado = 5;
                            foreach ($arrayPromedios as $ap) {
                                    if($ap<51)
                                        $nuevoEstado = 11;
                            }   
                        }
                    } 
                    if($tipo == 'Trimestre'  and $gestion < 2020){
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
                                // CALCULAMOS EL PROMEDIO GENERAL PRIMARIA
                                $sumaPrimaria = 0;
                                foreach ($arrayPromedios as $ap) {
                                    $sumaPrimaria = $sumaPrimaria + $ap;
                                }
                                $promedioPrimaria = round($sumaPrimaria/count($arrayPromedios));

                                // OBTENEMOS EL REGISTRO DE LA NOTA PROMEDIO
                                $promedioGeneral = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array(
                                    'estudianteInscripcion'=>$inscripcion->getId(),
                                    'notaTipo'=>5
                                ));
                                
                                if($promedioGeneral){
                                    // SI EXISTE EL PROMEDIO GENERAL LO ACTUALIZAMOS
                                    $promedioGeneral = $this->modificarNotaCualitativa($promedioGeneral->getId(), '', $promedioPrimaria);
                                }else{
                                    // SI NO EXISTE LO REGISTRAMOS
                                    $promedioGeneral = $this->registrarNotaCualitativa(5, $idInscripcion, '', $promedioPrimaria);
                                }

                                if ($promedioGeneral->getNotaCuantitativa() < 51) {
                                    $nuevoEstado = 28; // ESTADO RETENIDO 28 - REEMPLAZA ESTADO REPROBADO 11
                                }

                                $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($nuevoEstado));
                                $this->em->persist($inscripcion);
                                $this->em->flush();
                                
                                /// Registro den log de estado de matricula
                                $nuevo = [];
                                $nuevo['id'] = $inscripcion->getId();
                                $nuevo['estadoMatricula'] = $inscripcion->getEstadomatriculaTipo()->getId();

                                if ($anterior['estadoMatricula'] != $nuevo['estadoMatricula']) {
                                    $this->funciones->setLogTransaccion(
                                        $inscripcion->getId(),
                                        'estudiante_inscripcion',
                                        'U',
                                        '',
                                        $nuevo,
                                        $anterior,
                                        'SERVICIO NOTAS - ESTADO MATRICULA',
                                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                    );
                                }

                            }else{
                                $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($nuevoEstado));
                                $this->em->persist($inscripcion);
                                $this->em->flush();

                                /// Registro den log de estado de matricula
                                $nuevo = [];
                                $nuevo['id'] = $inscripcion->getId();
                                $nuevo['estadoMatricula'] = $inscripcion->getEstadomatriculaTipo()->getId();  
                                if ($anterior['estadoMatricula'] != $nuevo['estadoMatricula']) {
                                    $this->funciones->setLogTransaccion(
                                        $inscripcion->getId(),
                                        'estudiante_inscripcion',
                                        'U',
                                        '',
                                        $nuevo,
                                        $anterior,
                                        'SERVICIO NOTAS - ESTADO MATRICULA',
                                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                    );
                                }
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


    public function actualizarEstadoMatriculaDB($idInscripcion){
        
        try {
            $this->em->getConnection()->beginTransaction();
            $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

            // ARRAY PARA EL LOG
            $anterior = [];
            $anterior['id'] = $inscripcion->getId();
            $anterior['estadoMatricula'] = $inscripcion->getEstadomatriculaTipo()->getId();
            ////////////////////

            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();

            $gestionActual = $this->session->get('currentyear');

            //$operativo = $this->funciones->obtenerOperativo($sie,$gestion);
            $operativo = $this->funciones->obtenerOperativoTrimestre2020($sie,$gestion);
            
            /*
            if($gestion <= 2012 or ($gestion == 2013 and $grado > 1 and $nivel != 11) or ($gestion == 2013 and $grado == (1 or 2 ) and $nivel == 11) ){
                $tipo = 't';
            }else{
                $tipo = 'b';
            }*/

            $tipo = $this->getTipoNota($sie,$gestion,$nivel,$grado,'');

            // ACTUALIZAMOS EL ESTADO DE MATRICULA DE EDUCACION INICIAL A PROMOVIDO
            if($nivel == 11 or $nivel == 1 or $nivel == 403 ){
                // SE ACTUALIZA EL ESTADO DE MATRICULA SI EL OPERATIVO ACTUAL ES MAYOR A 4TO BIMESTRE
                if($operativo >= 4){
                //if($operativo >= 1){
                    $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

                    $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(5));
                    $this->em->persist($inscripcion);
                    $this->em->flush();

                    $nuevo = [];
                    $nuevo['id'] = $inscripcion->getId();
                    $nuevo['estadoMatricula'] = $inscripcion->getEstadomatriculaTipo()->getId();       

                    if ($anterior['estadoMatricula'] != $nuevo['estadoMatricula']) {
                        $this->funciones->setLogTransaccion(
                            $inscripcion->getId(),
                            'estudiante_inscripcion',
                            'U',
                            '',
                            $nuevo,
                            $anterior,
                            'SERVICIO NOTAS - ESTADO MATRICULA',
                            json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                        );
                    }

                }
            }else{
                // ACTUALIZAMOS EL ESTADO DE MATRICULA DE PRIMARIA Y SECUNDARIA
                $asignaturas = $this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findby(array('estudianteInscripcion'=>$idInscripcion));
                
                $arrayPromedios = array();
                foreach ($asignaturas as $a) {
                    // Notas Bimestrales
                    if($tipo == 'Bimestre' or ($tipo == 'Trimestre' and $gestion == 2020)){
                        //$notaPromedio = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>5));
                        $notaPromedio = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>9));
                        //$notaPromedio = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId()));
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
                        } else {
                            $arrayPromedios[] = 0; 
                        }
                    }
                    // Notas Trimestrales
                    if($tipo == 'Trimestre' and $gestion < 2020){
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
                    
                    if($inscripcion->getEstadomatriculaInicioTipo() != null and $inscripcion->getEstadomatriculaInicioTipo()->getId() == 29){
                        $nuevoEstado = 26; // promovido por postbachillerato
                    }else{
                        // VERIFICAMOS SI EL ESTADO DE MATRICULA ACTUAL ES
                        // 26 PROMOVIDO POST-BACHILLERATO
                        // 55 PROMOVIDO BACHILLER DE EXCELENCIA
                        // 57 PROMOVIDO POR REZAGO ESCOLAR
                        // 58 PROMOVIDO TALENTO EXTRAORDINARIO
                        // PARA NO MODIFICAR EL ESTADO DE MATRICULA ORIGINAL SI EL NUEVO ESTADO ES PROMOVIDO
                        if (in_array($inscripcion->getEstadomatriculaTipo()->getId(), [26,55,57,58])) {
                            $nuevoEstado = $inscripcion->getEstadomatriculaTipo()->getId();
                        }else{
                            $nuevoEstado = 5; // PROMOVIDO
                        }
                    }

                    if($tipo == 'Bimestre' or ($tipo == 'Trimestre' and $gestion == 2020) ){
                        $registroConsolidacion = $this->em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('gestion'=>2020,'unidadEducativa'=>$sie));
                        $cierreGestion2020 = false;
                        if(count($registroConsolidacion)>0){
                            if($registroConsolidacion->getBim1() != 0 and $registroConsolidacion->getBim2() != 0 and $registroConsolidacion->getBim3() != 0){
                                $cierreGestion2020 = true;
                            }
                        }
                        
                        // NO REALIZAMOS LA VERIFICACION DE LOS PROMEDIOS PARA PRIMARIA 
                        // A PARTIR DE LA GESTION 2019 DEBIDO A QUE LA PROMOCION SE
                        // DETERMINA CON EL PROMEDIO ANUAL
                        if($gestion == 2020 and $cierreGestion2020 == false){
                            if ($gestion < 2019 or ($gestion >= 2019 and $nivel != 12)) {
                                foreach ($arrayPromedios as $ap) {
                                    if($ap < 51){
                                        $nuevoEstado = 4;
                                        break;
                                    }
                                }   
                            }
                        } else {
                            if ($gestion < 2019 or ($gestion >= 2019 and $nivel != 12)) {
                                foreach ($arrayPromedios as $ap) {
                                    if($ap < 51){
                                        $nuevoEstado = 11;
                                        break;
                                    }
                                }   
                            }
                        }
                    }
                    if($tipo == 'Trimestre'  and $gestion < 2020){
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
                                // CALCULAMOS EL PROMEDIO GENERAL PRIMARIA
                                $sumaPrimaria = 0;
                                foreach ($arrayPromedios as $ap) {
                                    $sumaPrimaria = $sumaPrimaria + $ap;
                                }
                                $promedioPrimaria = round($sumaPrimaria/count($arrayPromedios));

                                // OBTENEMOS EL REGISTRO DE LA NOTA PROMEDIO
                                $promedioGeneral = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array(
                                    'estudianteInscripcion'=>$inscripcion->getId(),
                                    'notaTipo'=>5
                                ));
                                
                                if($promedioGeneral){
                                    // SI EXISTE EL PROMEDIO GENERAL LO ACTUALIZAMOS
                                    $promedioGeneral = $this->modificarNotaCualitativa($promedioGeneral->getId(), '', $promedioPrimaria);
                                }else{
                                    // SI NO EXISTE LO REGISTRAMOS
                                    $promedioGeneral = $this->registrarNotaCualitativa(5, $idInscripcion, '', $promedioPrimaria);
                                }

                                if ($promedioGeneral->getNotaCuantitativa() < 51) {
                                    $nuevoEstado = 28; // ESTADO RETENIDO 28 - REEMPLAZA ESTADO REPROBADO 11
                                }

                                $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($nuevoEstado));
                                $this->em->persist($inscripcion);
                                $this->em->flush();
                                
                                /// Registro den log de estado de matricula
                                $nuevo = [];
                                $nuevo['id'] = $inscripcion->getId();
                                $nuevo['estadoMatricula'] = $inscripcion->getEstadomatriculaTipo()->getId();

                                if ($anterior['estadoMatricula'] != $nuevo['estadoMatricula']) {
                                    $this->funciones->setLogTransaccion(
                                        $inscripcion->getId(),
                                        'estudiante_inscripcion',
                                        'U',
                                        '',
                                        $nuevo,
                                        $anterior,
                                        'SERVICIO NOTAS - ESTADO MATRICULA',
                                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                    );
                                }

                            }else{
                                $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($nuevoEstado));
                                $this->em->persist($inscripcion);
                                $this->em->flush();

                                /// Registro den log de estado de matricula
                                $nuevo = [];
                                $nuevo['id'] = $inscripcion->getId();
                                $nuevo['estadoMatricula'] = $inscripcion->getEstadomatriculaTipo()->getId();  
                                if ($anterior['estadoMatricula'] != $nuevo['estadoMatricula']) {
                                    $this->funciones->setLogTransaccion(
                                        $inscripcion->getId(),
                                        'estudiante_inscripcion',
                                        'U',
                                        '',
                                        $nuevo,
                                        $anterior,
                                        'SERVICIO NOTAS - ESTADO MATRICULA',
                                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                    );
                                }
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

    public function updateAveragePrim($idInscripcion){

        $asignaturasNotas = $this->em->createQueryBuilder()
                            ->select('nt')
                            ->from('SieAppWebBundle:EstudianteAsignatura','eas')
                            ->innerJoin('SieAppWebBundle:EstudianteNota','nt','with','eas.id = nt.estudianteAsignatura')
                            ->orderBy('nt.id','ASC')
                            ->where('eas.estudianteInscripcion = :idInscripcion')
                            ->andWhere('nt.notaTipo = :averagePrima')
                            ->setParameter('idInscripcion',$idInscripcion)
                            ->setParameter('averagePrima',9)
                            ->getQuery()
                            ->getResult();  
        // dump(sizeof($asignaturasNotas));
        $averaTotal = 0;
        if(sizeof($asignaturasNotas)>0){
            $averaSum = 0;
            foreach ($asignaturasNotas as $value) {
                $averaSum += $value->getNotaCuantitativa();
                // dump($value->getNotaCuantitativa());
            }
            $averaTotal = round($averaSum/sizeof($asignaturasNotas));
            // dump($averaTotal);
            $averagePrim=$this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion));
            if (count($averagePrim) > 0) {
                $averagePrim->setNotaCuantitativa($averaTotal);
                $this->em->persist($averagePrim);
                $this->em->flush(); 
            }
        }
        return $averaTotal;

    }    

    public function actualizarEstadoMatriculaIGP($inscripcionId){

        $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcionId);
        
        $igestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
        $chooseyear = $igestion;
        $iinstitucioneducativa_id = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $inivel_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $igrado_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
        $iturno_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getTurnoTipo()->getId();
        $iparalelo_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
        $icodigo_rude = $inscripcion->getEstudiante()->getCodigoRude();
        $complementario = "";
        $estado_inicial = $inscripcion->getEstadomatriculaTipo()->getEstadomatricula();

        if($igestion == 2013) {
            if($inivel_tipo_id == 12) {
                if($igrado_tipo_id == 1) {
                    $complementario = "'(1,2,3)','(1,2,3,4,5)','(5)','51'";
                } else {
                    $complementario = "'(6,7)','(6,7,8)','(9,11)','36'";
                }
            } else if($inivel_tipo_id == 13) {
                if($igrado_tipo_id == 1) {
                    $complementario = "'(1,2,3)','(1,2,3,4,5)','(5)','51'";
                } else {
                    $complementario = "'(6,7)','(6,7,8)','(9,11)','36'";
                }
            }
        } else if($igestion < 2013) {
            $complementario = "'(6,7)','(6,7,8)','(9,11)','36'";
        } else if($igestion > 2013 && $igestion < 2020) {
            $complementario = "'(1,2,3)','(1,2,3,4,5)','(5)','51'";
        } else if($igestion == 2020) {
            if($inivel_tipo_id == 12) {
                if($igrado_tipo_id > 1) {
                    $complementario = "'(6,7)','(6,7,8)','(9)','51'";
                }
            } else if($inivel_tipo_id == 13) {
                if($igrado_tipo_id >= 1) {
                    $complementario = "'(6,7)','(6,7,8)','(9)','51'";
                }
            }
        }else if($igestion == 2021 || $igestion == 2022 || $igestion == 2023) {
            if($inivel_tipo_id == 11) {
                $complementario = "";
            }else if($inivel_tipo_id == 12) {
                if($igrado_tipo_id >= 1) {
                    $complementario = "'(6,7)','(6,7,8)','(9)','51'";
                }
            } else if($inivel_tipo_id == 13) {
                if($igrado_tipo_id >= 1) {
                    $complementario = "'(6,7)','(6,7,8)','(9)','51'";
                }
            }
        }
        $operativo = $this->funciones->obtenerOperativo($iinstitucioneducativa_id, $igestion);
        
        if($operativo==3 && ($igestion == 2021 || $igestion == 2022 || $igestion == 2023 || $igestion == 2024)){
            switch ($inivel_tipo_id) {
                case '13':
                    $query = $this->em->getConnection()->prepare("select * from sp_genera_evaluacion_estado_estudiante_regular('".$igestion."','".$iinstitucioneducativa_id."','".$inivel_tipo_id."','".$igrado_tipo_id."','".$iturno_tipo_id."','".$iparalelo_tipo_id."','".$icodigo_rude."',".$complementario.")");
                    $query->execute();        
                    $resultado = $query->fetchAll();        
                    break;
                case '12':
                    $averagePrim=$this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$inscripcionId));
                    if($averagePrim){
                        if($averagePrim->getNotaCuantitativa()>50){
                            $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(5));
                        }else{
                            $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(11));
                        }
                        $this->em->persist($inscripcion);
                        $this->em->flush();
                    }
                    break;
                case '11':
                    
                        $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(5));
                        $this->em->persist($inscripcion);
                        $this->em->flush();                    
            

                    break;                
                default:
                    # code...
                    break;
            }
        }else{
            if( $igestion != 2021 and $igestion != 2022 and $igestion != 2023 and $igestion != 2024){
                $query = $this->em->getConnection()->prepare("select * from sp_genera_evaluacion_estado_estudiante_regular('".$igestion."','".$iinstitucioneducativa_id."','".$inivel_tipo_id."','".$igrado_tipo_id."','".$iturno_tipo_id."','".$iparalelo_tipo_id."','".$icodigo_rude."',".$complementario.")");
                $query->execute();        
                $resultado = $query->fetchAll();              
            }
        }
        
      
       
    }
    public function updateStatusStudent($inscripcionId){

        $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcionId);
        
        $igestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
        $chooseyear = $igestion;
        $iinstitucioneducativa_id = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $inivel_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $igrado_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
        $iturno_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getTurnoTipo()->getId();
        $iparalelo_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
        $icodigo_rude = $inscripcion->getEstudiante()->getCodigoRude();
        $complementario = "";
        $estado_inicial = $inscripcion->getEstadomatriculaTipo()->getEstadomatricula();

        if($igestion == 2021 || $igestion == 2022 || $igestion == 2023) {
            if($inivel_tipo_id == 11) {
                $complementario = "";
            }else if($inivel_tipo_id == 12) {
                if($igrado_tipo_id >= 1) {
                    $complementario = "'(6,7)','(6,7,8)','(9)','51'";
                }
            } else if($inivel_tipo_id == 13) {
                if($igrado_tipo_id >= 1) {
                    $complementario = "'(6,7)','(6,7,8)','(9)','51'";
                }
            }
        }
        $operativo = $this->funciones->obtenerOperativo($iinstitucioneducativa_id, $igestion);
        
        if($operativo==3 && (in_array($igestion, array(2022,2023,2024) ) ) ){
            switch ($inivel_tipo_id) {
                case '12':
                case '13':
                    $query = $this->em->getConnection()->prepare("select * from sp_genera_evaluacion_estado_estudiante_regular('".$igestion."','".$iinstitucioneducativa_id."','".$inivel_tipo_id."','".$igrado_tipo_id."','".$iturno_tipo_id."','".$iparalelo_tipo_id."','".$icodigo_rude."',".$complementario.")");
                    $query->execute();        
                    $resultado = $query->fetchAll();        
                    
                    break;
                case '11':
                    
                        $inscripcion->setEstadomatriculaTipo($this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(5));
                        $this->em->persist($inscripcion);
                        $this->em->flush();                    
            

                    break;                
                default:
                    # code...
                    break;
            }
        }else{
            if( $igestion != 2021 and $igestion != 2022 and $igestion != 2023 and $igestion != 2024){
                $query = $this->em->getConnection()->prepare("select * from sp_genera_evaluacion_estado_estudiante_regular('".$igestion."','".$iinstitucioneducativa_id."','".$inivel_tipo_id."','".$igrado_tipo_id."','".$iturno_tipo_id."','".$iparalelo_tipo_id."','".$icodigo_rude."',".$complementario.")");
                $query->execute();        
                $resultado = $query->fetchAll();              
            }
        }
        
      
       
    }

    /*
     * ______________________ CALCULO DE PROMEDIOS TRIMESTRALES 2020____________________________
     * /////////////////////////////////////////////////////////////////////////////////////
     */

    public function calcularPromedioTrim2020($idEstudianteAsignatura){
        
        try {
            $notas = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura'=>$idEstudianteAsignatura));
            
            //return($notas);            
            $PT = null;
            $ST = null;
            $TT = null;
            
            $P = null;

            foreach ($notas as $n) {
                if($n->getNotaTipo()->getId() == 6){ $PT = $n; }
                if($n->getNotaTipo()->getId() == 7){ $ST = $n; }
                if($n->getNotaTipo()->getId() == 8){ $TT = $n; }
                if($n->getNotaTipo()->getId() == 9){ $P = $n; }
            }

            if($PT != null and $ST != null and $TT != null ){
                $promedio = round(($PT->getNotaCuantitativa() + $ST->getNotaCuantitativa() + $TT->getNotaCuantitativa()  )/3);
                if($P){
                    $P->setNotaCuantitativa($promedio);
                    $this->em->persist($P);
                    $this->em->flush();
                }else{
                    $P = $this->registrarNota(9,$idEstudianteAsignatura,$promedio,'');
                }
            }

            return true;

        } catch (Exception $e) {
            
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
   
    /*====================================================================
    =            REGISTRO Y MODIFICACION DE CALIFICACIONES            =
    ====================================================================*/

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

        // Registro de notas estudiante en el log
        $arrayNota = [];
        $arrayNota['id'] = $newNota->getId();
        $arrayNota['notaTipo'] = $newNota->getNotaTipo()->getId();
        $arrayNota['estudianteAsignatura'] = $newNota->getEstudianteAsignatura()->getId();
        $arrayNota['notaCuantitativa'] = $newNota->getNotaCuantitativa();
        $arrayNota['notaCualitativa'] = $newNota->getNotaCualitativa();
        $arrayNota['fechaRegistro'] = $newNota->getFechaRegistro()->format('d-m-Y');
        $arrayNota['fechaModificacion'] = $newNota->getFechaModificacion()->format('d-m-Y');
        
        $this->funciones->setLogTransaccion(
            $newNota->getId(),
            'estudiante_asignatura',
            'C',
            '',
            $arrayNota,
            '',
            'SERVICIO NOTAS',
            json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
        );

        return $newNota;
    }

    public function modificarNota($idEstudianteNota, $notaCuantitativa, $notaCualitativa){
        $datosNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota);
        if($datosNota){

            // Registro de notas estudiante en el log
            $anterior = [];
            $anterior['id'] = $datosNota->getId();
            $anterior['notaTipo'] = $datosNota->getNotaTipo()->getId();
            $anterior['notaCuantitativa'] = $datosNota->getNotaCuantitativa();
            $anterior['notaCualitativa'] = $datosNota->getNotaCualitativa();
            $anterior['fechaModificacion'] = ($datosNota->getFechaModificacion())?$datosNota->getFechaModificacion()->format('d-m-Y'):'';

            $datosNota->setNotaCuantitativa($notaCuantitativa);
            $datosNota->setNotaCualitativa(mb_strtoupper($notaCualitativa, 'utf-8'));
            $datosNota->setUsuarioId($this->session->get('userId'));
            $datosNota->setFechaModificacion(new \DateTime('now'));
            $this->em->persist($datosNota);
            $this->em->flush();

            // Registro de notas estudiante en el log
            $nuevo = [];
            $nuevo['id'] = $datosNota->getId();
            $nuevo['notaTipo'] = $datosNota->getNotaTipo()->getId();
            $nuevo['notaCuantitativa'] = $datosNota->getNotaCuantitativa();
            $nuevo['notaCualitativa'] = $datosNota->getNotaCualitativa();
            $nuevo['fechaModificacion'] = ($datosNota->getFechaModificacion())?$datosNota->getFechaModificacion()->format('d-m-Y'):'';
            
            $this->funciones->setLogTransaccion(
                $datosNota->getId(),
                'estudiante_asignatura',
                'U',
                '',
                $nuevo,
                $anterior,
                'SERVICIO NOTAS',
                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );
        }

        return $datosNota;
    }

    /*=====  End of REGISTRO Y MODIFICACION DE CALIFICACACION  ======*/


    /**
    * REGISTRO Y MODIFICACION DE NOTAS CUALITATIVAS (estudiante_nota_cualitativa) 
    */
    public function registrarNotaCualitativa($idNotaTipo, $idEstudianteInscripcion, $notaCualitativa, $notaCuantitativa){
        // Reiniciamos la secuencia de la tabla notas
        $newNotaCualitativa = new EstudianteNotaCualitativa();
        $newNotaCualitativa->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo));
        $newNotaCualitativa->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idEstudianteInscripcion));
        $newNotaCualitativa->setNotaCuantitativa($notaCuantitativa);
        $newNotaCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa, 'utf-8'));
        $newNotaCualitativa->setUsuarioId($this->session->get('userId'));
        $newNotaCualitativa->setFechaRegistro(new \DateTime('now'));
        $newNotaCualitativa->setFechaModificacion(new \DateTime('now'));
        $newNotaCualitativa->setObs('');
        $this->em->persist($newNotaCualitativa);
        $this->em->flush();

        /// Registro de notas estudiante en el log
        $arrayNotaCualitativa = [];
        $arrayNotaCualitativa['id'] = $newNotaCualitativa->getId();
        $arrayNotaCualitativa['notaTipo'] = $newNotaCualitativa->getNotaTipo()->getId();
        $arrayNotaCualitativa['estudianteInscripcion'] = $newNotaCualitativa->getEstudianteInscripcion()->getId();
        $arrayNotaCualitativa['notaCuantitativa'] = $newNotaCualitativa->getNotaCuantitativa();
        $arrayNotaCualitativa['notaCualitativa'] = $newNotaCualitativa->getNotaCualitativa();
        $arrayNotaCualitativa['fechaRegistro'] = $newNotaCualitativa->getFechaRegistro()->format('d-m-Y');
        $arrayNotaCualitativa['fechaModificacion'] = $newNotaCualitativa->getFechaModificacion()->format('d-m-Y');
        
        $this->funciones->setLogTransaccion(
            $newNotaCualitativa->getId(),
            'estudiante_nota_cualitativa',
            'C',
            '',
            $arrayNotaCualitativa,
            '',
            'SERVICIO NOTAS',
            json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
        );

        return $newNotaCualitativa;
    }

    public function modificarNotaCualitativa($idEstudianteNotaCualitativa, $notaCualitativa, $notaCuantitativa){
        $datosNotaCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaCualitativa);
        if($datosNotaCualitativa){

            /// Registro de notas estudiante en el log
            $anterior = [];
            $anterior['id'] = $datosNotaCualitativa->getId();
            $anterior['notaTipo'] = $datosNotaCualitativa->getNotaTipo()->getId();
            $anterior['notaCualitativa'] = $datosNotaCualitativa->getNotaCualitativa();
            $anterior['fechaModificacion'] = ($datosNotaCualitativa->getFechaModificacion())?$datosNotaCualitativa->getFechaModificacion()->format('d-m-Y'):'';

            $datosNotaCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa, 'utf-8'));
            $datosNotaCualitativa->setNotaCuantitativa(mb_strtoupper($notaCuantitativa, 'utf-8'));
            $datosNotaCualitativa->setUsuarioId($this->session->get('userId'));
            $datosNotaCualitativa->setFechaModificacion(new \DateTime('now'));
            $this->em->persist($datosNotaCualitativa);
            $this->em->flush();

            /// Registro de notas estudiante en el log
            $nuevo = [];
            $nuevo['id'] = $datosNotaCualitativa->getId();
            $nuevo['notaTipo'] = $datosNotaCualitativa->getNotaTipo()->getId();
            $nuevo['notaCualitativa'] = $datosNotaCualitativa->getNotaCualitativa();
            $nuevo['fechaModificacion'] = ($datosNotaCualitativa->getFechaModificacion())?$datosNotaCualitativa->getFechaModificacion()->format('d-m-Y'):'';
            
            $this->funciones->setLogTransaccion(
                $datosNotaCualitativa->getId(),
                'estudiante_nota_cualitativa',
                'U',
                '',
                $nuevo,
                $anterior,
                'SERVICIO NOTAS',
                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );
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

                    // Registro de materia para estudiantes estudiante_asignatura en el log
                    $arrayEstAsig = [];
                    $arrayEstAsig['id'] = $newEstAsig->getId();
                    $arrayEstAsig['gestionTipo'] = $newEstAsig->getGestionTipo()->getId();
                    $arrayEstAsig['fechaRegistro'] = $newEstAsig->getFechaRegistro()->format('d-m-Y');
                    $arrayEstAsig['estudianteInscripcion'] = $newEstAsig->getEstudianteInscripcion()->getId();
                    $arrayEstAsig['institucioneducativaCursoOferta'] = $newEstAsig->getInstitucioneducativaCursoOferta()->getId();
                    
                    $this->funciones->setLogTransaccion(
                        $newEstAsig->getId(),
                        'estudiante_asignatura',
                        'C',
                        '',
                        $arrayEstAsig,
                        '',
                        'ACADEMICO',
                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                    );
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
                        if($nivel != 400 and $nivel != 401 and $nivel != 408 and $nivel != 402 and $nivel != 403 and $nivel != 411){
                            $valorNota = $an['notaCuantitativa'];
                        }else{
                            $valorNota = $an['notaCualitativa'];
                        }
                        if($i == $an['idNotaTipo']){
                            if($gestion > 2019 and ($discapacidad == 3 or $discapacidad == 5)){
                                $notasArray[$cont]['notas'][] =   array(
                                    'id'=>$cont."-".$i,
                                    'idEstudianteNota'=>$an['idNota'],
                                    'nota'=>json_decode($valorNota,true),
                                    'idNotaTipo'=>$an['idNotaTipo'],
                                    'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                );
                                //dump(json_decode($valorNota,true)['fecha_fin']);die;
                                
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
                if($nivel != 400 and $nivel != 401 and $nivel != 408 and $nivel != 402 and $nivel != 403 and $nivel != 411 and $operativo >= 4){
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

            if($nivel == 400 or $nivel == 401 or $nivel == 408 or $nivel == 402 or $nivel == 403 or $nivel != 411){
                // Para inicial
                $existe = false;
                foreach ($cualitativas as $c) {
                    if($c->getNotaTipo()->getId() == 18){
                        
                        if (($nivel == 400 or $nivel == 401 or $nivel == 408 or $nivel == 402) and $gestion > 2019){
                            $nota['notaCualitativa'] = json_decode($c->getNotaCualitativa(),true)['notaCualitativa'];
                            $nota['promovido'] = json_decode($c->getNotaCualitativa(),true)['promovido'];
                        }else{
                            $nota['notaCualitativa'] = $c->getNotaCualitativa();
                            $nota['promovido'] = '';
                            
                        }
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>$c->getId(),
                                                     'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                     'notaCualitativa'=>$nota['notaCualitativa'],
                                                     'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                     'promovido'=>$nota['promovido']
                                                    );
                        $existe = true;
                    }
                }
                if($existe == false and $operativo >= 4){
                    $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                 'idEstudianteNotaCualitativa'=>'nuevo',
                                                 'idNotaTipo'=>18,  
                                                 'notaCualitativa'=>'',
                                                 'notaTipo'=>$this->literal(18)
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
                                                     'notaTipo'=>$this->literal($i)
                                                    );
                        $existe = true;
                    }
                }
            }

            if($gestion < 2020){
                $estadosPermitidos = array(0,4,5,7,70,71,72,73,47,68);    
            }else{
                $estadosPermitidos = array(0,4,5,7,78,79,28,68);
                //$estadosPermitidos = array(0);
            }
            
            // Tipos de notas
            if (($discapacidad == 3 or $discapacidad == 5 or $discapacidad == 7) and $gestion > 2019){
                $tiposNotas = $this->em->getRepository('SieAppWebBundle:NotaEspecialTipo')->findById(array(1,2,4));
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(5,28));
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
            );

        } catch (Exception $e) {
            return null;
        }
    }

    public function especial_cualitativoEsp($idInscripcion,$operativo){
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

                    // Registro de materia para estudiantes estudiante_asignatura en el log
                    $arrayEstAsig = [];
                    $arrayEstAsig['id'] = $newEstAsig->getId();
                    $arrayEstAsig['gestionTipo'] = $newEstAsig->getGestionTipo()->getId();
                    $arrayEstAsig['fechaRegistro'] = $newEstAsig->getFechaRegistro()->format('d-m-Y');
                    $arrayEstAsig['estudianteInscripcion'] = $newEstAsig->getEstudianteInscripcion()->getId();
                    $arrayEstAsig['institucioneducativaCursoOferta'] = $newEstAsig->getInstitucioneducativaCursoOferta()->getId();
                    
                    $this->funciones->setLogTransaccion(
                        $newEstAsig->getId(),
                        'estudiante_asignatura',
                        'C',
                        '',
                        $arrayEstAsig,
                        '',
                        'ACADEMICO',
                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                    );
                }
            }

            // Volvemos atras si se adiciono alguna nueva materia o asignatura
            if($nuevaArea == true){
                goto vuelve;
            }

            //dump($asignaturas);die;
            $notasArray = array();
            $cont = 0;

            $tipoNota = $this->getTipoNotaEsp($sie,$gestion,$nivel,$grado,$discapacidad);

            // if($tipoNota == 'Bimestre'){
            if( in_array($tipoNota,array('newTemplateDB','Bimestre', 'Etapa') )  ){
                //dump($operativo);
                $inicio = 6;
                $fin = $inicio + ($operativo-1);
                // switch ($operativo) {
                //     case 0:
                //         $inicio = 1;
                //         $fin = 0;
                //         break;
                //     case 1:
                //         $inicio = 1;
                //         $fin = 1;
                //         break;
                //     case 5:
                //         $inicio = 1;
                //         $fin = 4;
                //         break;
                //     default:
                //         $inicio = 1;
                //         $fin = $operativo;
                //         break;
                // }
            }else{
                $inicio = 6;
                $fin = 8;
            }

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
               // dump($inicio); dump($fin);die;
                for($i=$inicio;$i<=$fin;$i++){
                    $existe = 'no';
                    foreach ($asignaturasNotas as $an) {
                        /*
                        * 401 Independencia personal
                        * 408 Independencia personal
                        * 402 Independencia solcial
                        * 412 Independencia solcial
                        * 403 Educacion inicial
                        * 404 Educacion primaria
                        * 405 Formacion tecnica
                        * 406 Area de discapacidad auditiva y visual
                        * 407 Area de discapacidad intelectual y multiple
                        * 410 Servicios
                        * 411 Programas
                        */
                        if($nivel != 400 and $nivel != 401 and $nivel != 408 and $nivel != 402 and $nivel != 403 and $nivel != 411 and $nivel != 412){
                            $valorNota = $an['notaCuantitativa'];
                        }else{
                            $valorNota = $an['notaCualitativa'];
                        }
                        
                        if($i == $an['idNotaTipo']){
                            if($gestion > 2019 and ($discapacidad == 3 or $discapacidad == 5 or $discapacidad == 12)){
                                $notasArray[$cont]['notas'][] =   array(
                                    'id'=>$cont."-".$i,
                                    'idEstudianteNota'=>$an['idNota'],
                                    'nota'=>json_decode($valorNota,true),
                                    'idNotaTipo'=>$an['idNotaTipo'],
                                    'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                );
                                //dump(json_decode($valorNota,true)['fecha_fin']);die;
                                
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
                if($nivel != 400  and $nivel != 401 and $nivel != 408 and $nivel != 402 and $nivel != 403 and $nivel != 411 and $nivel != 412 and $operativo >= 3){
                    
                    // Para el promedio
                    foreach ($asignaturasNotas as $an) {
                        $existe = 'no';
                        if($an['idNotaTipo'] == 5){
                            $notasArray[$cont]['notas'][] =   array(
                                                        'id'=>$cont."-9",
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
                                                    'id'=>$cont."-9",
                                                    'idEstudianteNota'=>'nuevo',
                                                    'nota'=>'',
                                                    'idNotaTipo'=>9,
                                                    'idEstudianteAsignatura'=>$a['estAsigId']
                                                );
                    }
                }
                $cont++;
            }
            $areas = array();
            $areas = $notasArray;

            //notas cualitativas
            $arrayCualitativas = array();

            $cualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion'=>$idInscripcion),array('notaTipo'=>'ASC'));
            //  dump($cualitativas);die;
            if($nivel == 400 or $nivel == 401 or $nivel == 408 or $nivel == 402 or $nivel == 403 or $nivel == 412 or $nivel != 411){
                // Para inicial
                $existe = false;
                foreach ($cualitativas as $c) {
                    if($c->getNotaTipo()->getId() == 18){
                       
                        if (($nivel == 400 or $nivel == 401 or $nivel == 408 or $nivel == 402 or $nivel == 403 or $nivel == 412) and $gestion > 2019){
                       //json validar
                            $nota['notaCualitativa'] = json_decode($c->getNotaCualitativa(),true)['notaCualitativa'];
                            $nota['promovido'] = json_decode($c->getNotaCualitativa(),true)['promovido'];
                            if(!json_decode($c->getNotaCualitativa(),true)['notaCualitativa'] && $nota['notaCualitativa']==''){
                                $nota['notaCualitativa'] = $c->getNotaCualitativa();
                            }
                        }else{
                            $nota['notaCualitativa'] = $c->getNotaCualitativa();
                            $nota['promovido'] = '';
                            
                        }
                        $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                     'idEstudianteNotaCualitativa'=>$c->getId(),
                                                     'idNotaTipo'=>$c->getNotaTipo()->getId(),
                                                     'notaCualitativa'=>$nota['notaCualitativa'],
                                                     'notaTipo'=>$c->getNotaTipo()->getNotaTipo(),
                                                     'promovido'=>$nota['promovido']
                                                    );
                        $existe = true;
                    }
                }
                if($existe == false and $operativo >= 3){
                    $arrayCualitativas[] = array('idInscripcion'=>$idInscripcion,
                                                 'idEstudianteNotaCualitativa'=>'nuevo',
                                                 'idNotaTipo'=>18,  
                                                 'notaCualitativa'=>'',
                                                 'notaTipo'=>$this->literal(18)
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
                                                     'notaTipo'=>$this->literal($i)
                                                    );
                        $existe = true;
                    }
                }
            }

            if($gestion < 2020){
                $estadosPermitidos = array(0,4,5,7,70,71,72,73,47);    
            }else{
                $estadosPermitidos = array(0,4,7,5,78,79,28,68);
                //$estadosPermitidos = array(0);
            }
            
            // Tipos de notas
            if (($discapacidad == 3 or $discapacidad == 5 or $discapacidad == 12 or $discapacidad == 7) and $gestion > 2019){
                $tiposNotas = $this->em->getRepository('SieAppWebBundle:NotaEspecialTipo')->findById(array(1,2,4));
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(5,28));
            }else{
                $tiposNotas = $this->em->getRepository('SieAppWebBundle:NotaEspecialTipo')->findById(array(1,2,3,4));
                $estadosFinales = "";
            }
            
            $tiposNotasArray = array();
            foreach ($tiposNotas as $tn) {
                $tiposNotasArray[] = array('id'=>$tn->getId(),'nota'=>$tn->getNota(),'descripcion'=>$tn->getDescripcion());
            }
            
            //dump($areas);die;
//2222222222222
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
            );

        } catch (Exception $e) {
            return null;
        }
    }
/**TODO
 * 
 */
    public function especial_cualitativo_visual($idInscripcion,$operativo){
        try {
            //dump($idInscripcion);die;
            $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $cursoEspecial = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array('institucioneducativaCurso'=>$inscripcion->getInstitucioneducativaCurso()->getId()));
            $programa = $cursoEspecial->getEspecialProgramaTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
            $discapacidad = $cursoEspecial->getEspecialAreaTipo()->getId();
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
            //dump($cursoOferta);die;
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
            /**
             * Notas cualitativas
             */
            //dump($programa);die;
            $cualitativas = $this->em->createQueryBuilder()
                                    ->select('ei.id as idEstudianteInscripcion,enc.id as idEstudianteCualitativo, nt.id as idNotaTipo,enc.notaCualitativa,nt.notaTipo,gt.id as gestion')
                                    ->from('SieAppWebBundle:EstudianteNotaCualitativa','enc')
                                    ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','enc.estudianteInscripcion = ei.id')
                                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','iec.id = ei.institucioneducativaCurso')
                                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoEspecial','iece','with','iece.institucioneducativaCurso = iec.id')
                                    ->innerJoin('SieAppWebBundle:NotaTipo','nt','with','enc.notaTipo = nt.id')
                                    ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                                    ->where('ei.estudiante = '. $inscripcion->getEstudiante()->getId())
                                    ->andWhere('gt.id > 2020')
                                    ->andWhere('iece.especialProgramaTipo = '.$programa)
                                    ->orderBy('gt.id','DESC')
                                    ->addOrderBy('nt.id','DESC')
                                    ->setMaxResults(1)
                                    ->getQuery()
                                    ->getResult();          
                                    
           //dump($cualitativas);die;
            if($cualitativas){
                if(json_decode($cualitativas[0]['notaCualitativa'],true)['estadoEtapa'] == 78){ //78=CONCLUIDO
                   
                    $inicio = 0;
                    $fin = 0;
                    $etapasArray[0] = array('idNotaTipo'=>$cualitativas[0]['idNotaTipo'],
                                            'etapa'=>json_decode($cualitativas[0]['notaCualitativa'],true)['etapa'],
                                            'fechaEtapa'=>json_decode($cualitativas[0]['notaCualitativa'],true)['fechaEtapa']
                                    );
                }else{ 
                   
                    $inicio = 0;
                    $fin = 1;
                    if(in_array(json_decode($cualitativas[0]['notaCualitativa'],true)['estadoEtapa'] , array(79,80))){ //79=PROSIGUE, 80=EXTENDIDO
                        $etapa = json_decode($cualitativas[0]['notaCualitativa'],true)['etapa']+1; 
                       
                    }else{
                       
                        $etapa = json_decode($cualitativas[0]['notaCualitativa'],true)['etapa']; 
                    }
                    //dump($etapa);
                    if($cualitativas[0]['gestion'] == $gestion){
                        $idNotaTipo = $cualitativas[0]['idNotaTipo']+1;
                    }else{ 
                        $idNotaTipo = 42;
                        $idNotaTipo = $cualitativas[0]['idNotaTipo']+1;
                    }
                   
                    $etapasArray[0] = array('idNotaTipo'=>$cualitativas[0]['idNotaTipo'],
                                                'etapa'=>json_decode($cualitativas[0]['notaCualitativa'],true)['etapa'],
                                                'fechaEtapa'=>json_decode($cualitativas[0]['notaCualitativa'],true)['fechaEtapa']
                                        );
                    $etapasArray[1] = array('idNotaTipo'=>$idNotaTipo,
                                            'etapa'=>$etapa,
                                            'fechaEtapa'=>""
                                        );
                }
                $idEstudianteInscripcion = $cualitativas[0]['idEstudianteInscripcion'];
                //die;
            }else{
                $inicio = 0;
                $fin = 0;
                $etapasArray[0] = array('idNotaTipo'=>42,
                                        'etapa'=>1,
                                        'fechaEtapa'=>""
                                );
                $idEstudianteInscripcion = $idInscripcion;
            }
            //dump($etapasArray);//die;
            //dump($cualitativas, $idInscripcion,$gestion,$etapasArray);die;
            $asignaturasC = $this->em->createQueryBuilder()
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
                                ->setParameter('idInscripcion',$idEstudianteInscripcion)
                                ->getQuery()
                                ->getResult();

            
          // dump($asignaturasC,$asignaturas);die;
            if($asignaturas){ 
                foreach ($asignaturasC as $key=>$a) { 
                    //dump($asignaturas,$a,$key);die;
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
                                        ->andWhere('en.notaTipo = :nt')
                                        ->setParameter('estAsigId',$a['estAsigId'])
                                        ->setParameter('nt',$etapasArray[0]['idNotaTipo'])
                                        ->getQuery()
                                        ->getResult();
                                    //    dump($asignaturasNotas);
                   // dump($inicio);dump($fin); die;
                    //dump($asignaturasC,$asignaturasNotas);die;
                    
                    for($i=$inicio;$i<=$fin;$i++){
                        $existe = 'no';
                        foreach ($asignaturasNotas as $an) {
                            $valorNota = $an['notaCualitativa'];
                            //dump($etapasArray[$i]['idNotaTipo']);
                            //dump($an['idNotaTipo']);
                            if($etapasArray[$i]['idNotaTipo'] == $an['idNotaTipo']){
                                $notasArray[$cont]['notas'][] =   array(
                                        'id'=>$cont."-".$etapasArray[$i]['idNotaTipo'],
                                        'idEstudianteNota'=>$an['idNota'],
                                        'nota'=>json_decode($valorNota,true),
                                        'idNotaTipo'=>$an['idNotaTipo'],
                                        'idEstudianteAsignatura'=>$an['idEstudianteAsignatura']
                                    );
                                $existe = 'si';
                                break;
                            }
    
                        }
                        //dump($notasArray);die;
                        if($existe == 'no'){
                            $valorNota = '';
                            $notasArray[$cont]['notas'][] =   array(
                                                        'id'=>$cont."-".$etapasArray[$i]['idNotaTipo'],
                                                        'idEstudianteNota'=>'nuevo',
                                                        'nota'=>$valorNota,
                                                        'idNotaTipo'=>$etapasArray[$i]['idNotaTipo'],
                                                        'idEstudianteAsignatura'=>$asignaturas[$key]['estAsigId']
                                                    );
                        }
                    }
                   
                    $cont++;
                }    
                // dump($notasArray);
            }
            //die;
            //dump($notasArray);die;
            $areas = array();
            $areas = $notasArray;
           //dump($areas);die;

            //notas cualitativas
            $arrayCualitativas = array();
           // dump($inicio);
           // dump($fin); //die;
            ///$cualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion'=>$idInscripcion),array('notaTipo'=>'ASC'));
            //dump($cualitativas[0]);die;
            $existe = false; //dump($inicio); dump($fin);
            for($i=$inicio;$i<=$fin;$i++){ //<=
                if($cualitativas and $existe == false and $cualitativas[$i]['gestion']<=$gestion){ //dump("si");die;
                    $arrayCualitativas[] = array(
                                                'idInscripcion'=>$cualitativas[0]['idEstudianteInscripcion'],
                                                'idEstudianteNotaCualitativa'=>$cualitativas[0]['idEstudianteCualitativo'],
                                                'idNotaTipo'=>$cualitativas[0]['idNotaTipo'],
                                                'notaCualitativa'=>json_decode($cualitativas[0]['notaCualitativa'],true),
                                                'notaTipo'=>$cualitativas[0]['notaTipo']
                                            );
                    $existe = true;
                }else{ 
                    $existe = false;
                }
                if($existe == false){ //dump($i);die;
                    $arrayCualitativas[] = array(
                                            'idInscripcion'=>$idInscripcion,
                                            'idEstudianteNotaCualitativa'=>'nuevo',
                                            'idNotaTipo'=>$etapasArray[$i]['idNotaTipo'],
                                            'notaCualitativa'=>'',
                                            'notaTipo'=>$this->literal($etapasArray[$i]['idNotaTipo'])['titulo'].' '.$tipoNota
                                        );
                                         
                }
               
            }
          //  dump($arrayCualitativas);//die;
            $estadosPermitidos = array(0,4,7,78,79,68 );
            //$estadosPermitidos = array(0);
            //dump($areas);die;

            // Tipos de notas
            $tiposNotas = $this->em->getRepository('SieAppWebBundle:NotaEspecialTipo')->findById(array(1,2,3,5));    
            $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(78,79,80));  
            
            $tiposNotasArray = array();
            foreach ($tiposNotas as $tn) {
                $tiposNotasArray[] = array('id'=>$tn->getId(),'nota'=>$tn->getNota(),'descripcion'=>$tn->getDescripcion());
            }
            //dump($arrayCualitativas);die;
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
                'etapas'            =>$etapasArray,
            );

        } catch (Exception $e) {
            return null;
        }
    }
    //nuevo regidtro especial
    public function especialRegistro(Request $request, $discapacidad){
        
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //dump($request); die;
        // Validar si existe la session del usuario
        //dump($request); die;
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
            
        try {
                $this->em->getConnection()->beginTransaction();
            // Datos de las notas cuantitativas
            $idEstudianteNota = $request->get('idEstudianteNota');
            $idNotaTipo = $request->get('idNotaTipo');
            $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
            $gestion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($request->get('idInscripcion'))->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            
            if($gestion > 2019 and ($discapacidad == 3 or $discapacidad == 5  or $discapacidad == 12)){ //intelectual y multiple
                
                //$estadoPromovido = $request->get('contenidos');
                $promovido = $request->get('promovido');
                $contenidos = $request->get('contenidos');
                $resultados = $request->get('resultados');
                $indicador = $request->get('indicador');
                $estado = $request->get('estado');
                $datosNotas = array();
                
                if($contenidos){
                    foreach ($contenidos as $i => $c){
                        $datosNotas[] = array('contenidos'=>mb_strtoupper($c,'utf-8'),'resultados'=>mb_strtoupper($resultados[$i],'utf-8'),'idIndicador'=>$indicador[$i],'idEstado'=>$estado[$i]);
                    }
                }
                $notas = $datosNotas;
              
            }else{
                $notas = $request->get('nota');
            }
            // dump($notas);die;

            // Datos de las notas cualitativas
            $idEstudianteNotaCualitativa = $request->get('idEstudianteNotaCualitativa');
            $idNotaTipoCualitativa = $request->get('idNotaTipoCualitativa');
            $notaCualitativa = $request->get('notaCualitativa');
            
            if($request->get('nuevoEstadomatricula') == 5 and $gestion > 2019 and ($discapacidad == 3 or $discapacidad == 5 or $discapacidad == 12)){
                $notaCualitativa[0] = array('notaCualitativa'=>mb_strtoupper($notaCualitativa[0],'utf-8'),'promovido'=>mb_strtoupper($promovido,'utf-8'));
                
            }
            // dump($notaCualitativa);die;

            /* Datos de las notas cualitativas de primaria gestion 2013 */
            $idEstudianteNotaC = $request->get('idEstudianteNotaC');
            $idNotaTipoC = $request->get('idNotaTipoC');
            $idEstudianteAsignaturaC = $request->get('idEstudianteAsignaturaC');
            $notasC = $request->get('notasC');

            $tipo = $request->get('tipoNota');
            $nivel = $request->get('nivel');
            $idInscripcion = $request->get('idInscripcion');
            $nivelesCualitativos = array(1,11,400,401,408,402,403,411, 412);
            //dump($tipo);die;
            if( in_array($tipo, array('newTemplateDB','Bimestre' )) ){
               
                // Registro y/o modificacion de notas
                $total = 0; $cantidad=0;
                for($i=0;$i<count($idEstudianteNota);$i++) {
                    if($idEstudianteNota[$i] == 'nuevo'){
                        
                        //if((!in_array($nivel, $nivelesCualitativos) and $notas[$i] != 0 ) or (in_array($nivel, $nivelesCualitativos) and $notas[$i] != "")){

                            $newNota = new EstudianteNota();
                            $newNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$i]));
                            $newNota->setEstudianteAsignatura($this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                            
                            if($nivel == 400 or $nivel == 401 or $nivel == 402 or $nivel == 408 or $nivel == 403 or $nivel == 411 or $nivel == 412){
                                
                                $newNota->setNotaCuantitativa(0);
                                if($gestion > 2019 and ($discapacidad == 2 or $discapacidad == 3 or $discapacidad == 5 or $discapacidad == 12)){
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
                            
                            //dump($newNota);die;
                            // Registro de notas estudiante en el log
                            $arrayNota = [];
                            $arrayNota['id'] = $newNota->getId();
                            $arrayNota['notaTipo'] = $newNota->getNotaTipo()->getId();
                            $arrayNota['estudianteAsignatura'] = $newNota->getEstudianteAsignatura()->getId();
                            $arrayNota['notaCuantitativa'] = $newNota->getNotaCuantitativa();
                            $arrayNota['notaCualitativa'] = $newNota->getNotaCualitativa();
                            $arrayNota['fechaRegistro'] = $newNota->getFechaRegistro()->format('d-m-Y');
                            $arrayNota['fechaModificacion'] = $newNota->getFechaModificacion()->format('d-m-Y');
                            
                            $this->funciones->setLogTransaccion(
                                $newNota->getId(),
                                'estudiante_nota',
                                'C',
                                '',
                                $arrayNota,
                                '',
                                'SERVICIO NOTAS',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );
                        //}
                    }else{
                        $updateNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
                        // $regAnterior = clone $updateNota;
                        if($updateNota){
                            /// Registro de notas estudiante en el log
                            $anterior = [];
                            $anterior['id'] = $updateNota->getId();
                            $anterior['notaTipo'] = $updateNota->getNotaTipo()->getId();
                            $anterior['notaCuantitativa'] = $updateNota->getNotaCuantitativa();
                            $anterior['notaCualitativa'] = $updateNota->getNotaCualitativa();
                            $anterior['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';

                            if($nivel == 400 or $nivel == 401 or $nivel == 402 or $nivel == 408  or $nivel == 403 or $nivel == 411 or $nivel == 412){
                                if($gestion > 2019 and ($discapacidad == 2 or $discapacidad == 3 or $discapacidad == 5 or $discapacidad == 12)){
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

                            /// Registro de notas estudiante en el log
                            $nuevo = [];
                            $nuevo['id'] = $updateNota->getId();
                            $nuevo['notaTipo'] = $updateNota->getNotaTipo()->getId();
                            $nuevo['notaCuantitativa'] = $updateNota->getNotaCuantitativa();
                            $nuevo['notaCualitativa'] = $updateNota->getNotaCualitativa();
                            $nuevo['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';
                            
                            $this->funciones->setLogTransaccion(
                                $updateNota->getId(),
                                'estudiante_nota',
                                'U',
                                '',
                                $nuevo,
                                $anterior,
                                'SERVICIO NOTAS',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );
                        }
                    }
                    if ($discapacidad == 1 and (($i+1)%5 == 0) and $request->get('operativo') == 3 and $nivel == 404 and $gestion<2021) {
                        $total += $notas[$i];
                        $cantidad += 1;
                    }

                    if ($discapacidad == 1 and (($i+1)%4 == 0) and $request->get('operativo') == 3 and $nivel == 404 and $gestion>2020) {
                        $total += $notas[$i];
                        $cantidad += 1;
                    }
                    
                }
                $newNotaTipo = 5;
                if($gestion >= 2021){
                    $newNotaTipo = 9;
                }
                $nota_cualitativa_auditiva = false;
                if($discapacidad == 1 and $request->get('operativo') == 3 and $nivel == 404) {
                   
                    $promedio = $cantidad>0?round($total/$cantidad):$total;//round(($total/$cantidad), 3)
                    
                    $notaCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion' => $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion), 'notaTipo' => $this->em->getRepository('SieAppWebBundle:NotaTipo')->find($newNotaTipo)));
                    
                    if ($notaCualitativa) {

                        $notaCualitativa->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($newNotaTipo));
                        $notaCualitativa->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                        $notaCualitativa->setNotaCuantitativa($promedio);
                        $this->em->persist($notaCualitativa);
                        $this->em->flush();
                    } else {
                        $query = $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota_cualitativa');")->execute();
                        $notaCualitativa = new EstudianteNotaCualitativa();
                        $notaCualitativa->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($newNotaTipo));
                        $notaCualitativa->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                        $notaCualitativa->setNotaCuantitativa($promedio);
                        $notaCualitativa->setNotaCualitativa('');
                        $notaCualitativa->setRecomendacion('');
                        $notaCualitativa->setUsuarioId($this->session->get('userId'));
                        $notaCualitativa->setFechaRegistro(new \DateTime('now'));
                        $notaCualitativa->setFechaModificacion(new \DateTime('now'));
                        $notaCualitativa->setObs('Promedio');
                        $this->em->persist($notaCualitativa);
                        $this->em->flush();
                    }
                    $nota_cualitativa_auditiva = true;
                }

                // Registro de notas cualitativas de incial primaria y/o secundaria
               
              // dump($idEstudianteNotaCualitativa);
              //  dump($notaCualitativa);die;
                for($j=0;$j<count($idEstudianteNotaCualitativa);$j++){
                    if($nota_cualitativa_auditiva == false){
                         if($idEstudianteNotaCualitativa[$j] == 'nuevo' ){
                        
                            if($notaCualitativa[$j] != ""){
                                $query = $this->em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota_cualitativa');")->execute();
                                $newCualitativa = new EstudianteNotaCualitativa();
                                $newCualitativa->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipoCualitativa[$j]));
                                $newCualitativa->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                                $newCualitativa->setNotaCuantitativa(0);
                                if($gestion > 2019 and ($discapacidad == 3 or $discapacidad == 5) and $request->get('nuevoEstadomatricula') == 5){
                                    $newCualitativa->setNotaCualitativa(json_encode($notaCualitativa[$j]));
                                }else{
                                    $newCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                                }
                                $newCualitativa->setRecomendacion('');
                                $newCualitativa->setUsuarioId($this->session->get('userId'));
                                $newCualitativa->setFechaRegistro(new \DateTime('now'));
                                $newCualitativa->setFechaModificacion(new \DateTime('now'));
                                $newCualitativa->setObs('');
                                $this->em->persist($newCualitativa);
                                $this->em->flush();

                                // Registro de notas estudiante en el log
                                $arrayNotaCualitativa = [];
                                $arrayNotaCualitativa['id'] = $newCualitativa->getId();
                                $arrayNotaCualitativa['notaTipo'] = $newCualitativa->getNotaTipo()->getId();
                                $arrayNotaCualitativa['estudianteInscripcion'] = $newCualitativa->getEstudianteInscripcion()->getId();
                                $arrayNotaCualitativa['notaCuantitativa'] = $newCualitativa->getNotaCuantitativa();
                                $arrayNotaCualitativa['notaCualitativa'] = $newCualitativa->getNotaCualitativa();
                                $arrayNotaCualitativa['fechaRegistro'] = $newCualitativa->getFechaRegistro()->format('d-m-Y');
                                $arrayNotaCualitativa['fechaModificacion'] =  ($newCualitativa->getFechaModificacion())? $newCualitativa->getFechaModificacion()->format('d-m-Y'):'';
                                
                                $this->funciones->setLogTransaccion(
                                    $newCualitativa->getId(),
                                    'estudiante_nota_cualitativa',
                                    'C',
                                    '',
                                    $arrayNotaCualitativa,
                                    '',
                                    'SERVICIO NOTAS',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                );

                            }
                        }else{
                            $updateCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaCualitativa[$j]);
                            //dump($updateCualitativa);die;
                            if($updateCualitativa){

                                // Registro de notas estudiante en el log
                                $anterior = [];
                                $anterior['id'] = $updateCualitativa->getId();
                                $anterior['notaTipo'] = $updateCualitativa->getNotaTipo()->getId();
                                $anterior['notaCualitativa'] = $updateCualitativa->getNotaCualitativa();
                                $anterior['fechaModificacion'] = ($updateCualitativa->getFechaModificacion())?$updateCualitativa->getFechaModificacion()->format('d-m-Y'):'';

                                $updateCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                                $updateCualitativa->setUsuarioId($this->session->get('userId'));
                                $updateCualitativa->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($updateCualitativa);
                                $this->em->flush();

                                // Registro de notas estudiante en el log
                                $nuevo = [];
                                $nuevo['id'] = $updateCualitativa->getId();
                                $nuevo['notaTipo'] = $updateCualitativa->getNotaTipo()->getId();
                                $nuevo['notaCualitativa'] = $updateCualitativa->getNotaCualitativa();
                                $nuevo['fechaModificacion'] = $updateCualitativa->getFechaModificacion()->format('d-m-Y');
                                $nuevo['fechaModificacion'] = ($updateCualitativa->getFechaModificacion())?$updateCualitativa->getFechaModificacion()->format('d-m-Y'):'';
                                
                                $this->funciones->setLogTransaccion(
                                    $updateCualitativa->getId(),
                                    'estudiante_nota_cualitativa',
                                    'U',
                                    '',
                                    $nuevo,
                                    $anterior,
                                    'SERVICIO NOTAS',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                );
                            }
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
                            if($nivel == 11 or $nivel == 1 or $nivel == 400 or $nivel == 401 or $nivel == 402 or $nivel == 408 or $nivel == 403 or $nivel == 411){
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

                            // Registro de notas estudiante en el log
                            $arrayNota = [];
                            $arrayNota['id'] = $newNota->getId();
                            $arrayNota['notaTipo'] = $newNota->getNotaTipo()->getId();
                            $arrayNota['estudianteAsignatura'] = $newNota->getEstudianteAsignatura()->getId();
                            $arrayNota['notaCuantitativa'] = $newNota->getNotaCuantitativa();
                            $arrayNota['notaCualitativa'] = $newNota->getNotaCualitativa();
                            $arrayNota['fechaRegistro'] = $newNota->getFechaRegistro()->format('d-m-Y');
                            $arrayNota['fechaModificacion'] = $newNota->getFechaModificacion()->format('d-m-Y');
                            
                            $this->funciones->setLogTransaccion(
                                $newNota->getId(),
                                'estudiante_nota',
                                'C',
                                '',
                                $arrayNota,
                                '',
                                'SERVICIO NOTAS',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );
                        }
                    }else{
                        $updateNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
                        if($updateNota){
                            if(($idNotaTipo[$i] <=9) or (($idNotaTipo[$i]==10 or $idNotaTipo[$i]==11) and $notas[$i]!=0)){

                                /// Registro de notas estudiante en el log
                                $anterior = [];
                                $anterior['id'] = $updateNota->getId();
                                $anterior['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                $anterior['notaCuantitativa'] = $updateNota->getNotaCuantitativa();
                                $anterior['notaCualitativa'] = $updateNota->getNotaCualitativa();
                                $anterior['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';

                                if($nivel == 11 or $nivel == 1 or $nivel == 400 or $nivel == 401 or $nivel == 402 or $nivel == 408 or $nivel == 403 or $nivel == 411 or $nivel == 412){
                                    $updateNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                                }else{
                                    $updateNota->setNotaCuantitativa($notas[$i]);
                                }
                                $updateNota->setUsuarioId($this->session->get('userId'));
                                $updateNota->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($updateNota);
                                $this->em->flush();

                                /// Registro de notas estudiante en el log
                                $nuevo = [];
                                $nuevo['id'] = $updateNota->getId();
                                $nuevo['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                $nuevo['notaCuantitativa'] = $updateNota->getNotaCuantitativa();
                                $nuevo['notaCualitativa'] = $updateNota->getNotaCualitativa();
                                $nuevo['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';
                                
                                $this->funciones->setLogTransaccion(
                                    $updateNota->getId(),
                                    'estudiante_nota',
                                    'U',
                                    '',
                                    $nuevo,
                                    $anterior,
                                    'SERVICIO NOTAS',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                );

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

                                $arrayNota = [];
                                $arrayNota['id'] = $existeNotaCuantitativa->getId();
                                $arrayNota['notaTipo'] = $existeNotaCuantitativa->getNotaTipo()->getId();
                                $arrayNota['estudianteAsignatura'] = $existeNotaCuantitativa->getEstudianteAsignatura()->getId();
                                $arrayNota['notaCuantitativa'] = $existeNotaCuantitativa->getNotaCuantitativa();
                                $arrayNota['notaCualitativa'] = $existeNotaCuantitativa->getNotaCualitativa();
                                $arrayNota['fechaRegistro'] = $existeNotaCuantitativa->getFechaRegistro()->format('d-m-Y');
                                $arrayNota['fechaModificacion'] = ($existeNotaCuantitativa->getFechaModificacion())?$existeNotaCuantitativa->getFechaModificacion()->format('d-m-Y'):'';
                                
                                $this->funciones->setLogTransaccion(
                                    $existeNotaCuantitativa->getId(),
                                    'estudiante_nota',
                                    'U',
                                    '',
                                    $arrayNota,
                                    '',
                                    'SERVICIO NOTAS',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                );
                            }

                        }else{
                            $updateNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNotaC[$j]);
                            if($updateNota){

                                $anterior = [];
                                $anterior['id'] = $updateNota->getId();
                                $anterior['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                $anterior['notaCualitativa'] = $updateNota->getNotaCualitativa();
                                $anterior['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';

                                $updateNota->setNotaCualitativa(mb_strtoupper($notasC[$j],'utf-8'));
                                $updateNota->setUsuarioId($this->session->get('userId'));
                                $updateNota->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($updateNota);
                                $this->em->flush();

                                $nuevo = [];
                                $nuevo['id'] = $updateNota->getId();
                                $nuevo['notaTipo'] = $updateNota->getNotaTipo()->getId();
                                $nuevo['notaCualitativa'] = $updateNota->getNotaCualitativa();
                                $nuevo['fechaModificacion'] = ($updateNota->getFechaModificacion())?$updateNota->getFechaModificacion()->format('d-m-Y'):'';
                                
                                $this->funciones->setLogTransaccion(
                                    $updateNota->getId(),
                                    'estudiante_nota',
                                    'U',
                                    '',
                                    $nuevo,
                                    $anterior,
                                    'SERVICIO NOTAS',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                );
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

                            // Registro de notas estudiante en el log
                            $arrayNotaCualitativa = [];
                            $arrayNotaCualitativa['id'] = $newCualitativa->getId();
                            $arrayNotaCualitativa['notaTipo'] = $newCualitativa->getNotaTipo()->getId();
                            $arrayNotaCualitativa['estudianteInscripcion'] = $newCualitativa->getEstudianteInscripcion()->getId();
                            $arrayNotaCualitativa['notaCuantitativa'] = $newCualitativa->getNotaCuantitativa();
                            $arrayNotaCualitativa['notaCualitativa'] = $newCualitativa->getNotaCualitativa();
                            $arrayNotaCualitativa['fechaRegistro'] = $newCualitativa->getFechaRegistro()->format('d-m-Y');
                            $arrayNotaCualitativa['fechaModificacion'] = $newCualitativa->getFechaModificacion()->format('d-m-Y');
                            
                            $this->funciones->setLogTransaccion(
                                $newCualitativa->getId(),
                                'estudiante_nota_cualitativa',
                                'C',
                                '',
                                $arrayNotaCualitativa,
                                '',
                                'SERVICIO NOTAS',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );
                        }else{
                            $updateCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaCualitativa[$j]);
                            if($updateCualitativa){

                                // Registro de notas estudiante en el log
                                $anterior = [];
                                $anterior['id'] = $updateCualitativa->getId();
                                $anterior['notaTipo'] = $updateCualitativa->getNotaTipo()->getId();
                                $anterior['notaCualitativa'] = $updateCualitativa->getNotaCualitativa();
                                $anterior['fechaModificacion'] = ($updateCualitativa->getFechaModificacion())?$updateCualitativa->getFechaModificacion()->format('d-m-Y'):'';

                                $updateCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                                $updateCualitativa->setUsuarioId($this->session->get('userId'));
                                $updateCualitativa->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($updateCualitativa);
                                $this->em->flush();

                                // Registro de notas estudiante en el log
                                $nuevo = [];
                                $nuevo['id'] = $updateCualitativa->getId();
                                $nuevo['notaTipo'] = $updateCualitativa->getNotaTipo()->getId();
                                $nuevo['notaCualitativa'] = $updateCualitativa->getNotaCualitativa();
                                $nuevo['fechaModificacion'] = ($updateCualitativa->getFechaModificacion())?$updateCualitativa->getFechaModificacion()->format('d-m-Y'):'';
                                
                                $this->funciones->setLogTransaccion(
                                    $updateCualitativa->getId(),
                                    'estudiante_nota_cualitativa',
                                    'U',
                                    '',
                                    $nuevo,
                                    $anterior,
                                    'SERVICIO NOTAS',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                );
                            }
                        }
                    }
                }
            }
            
            if($tipo == 'Semestral'){ 
              
                $tipos_notas_array = $request->get('id_nota');
                
                for($x=0; $x<count($tipos_notas_array); $x++){
                    $actividad = $request->get('actividad'.$tipos_notas_array[$x]);
                    $valoracion = $request->get('valoracion'.$tipos_notas_array[$x]);
                    $sw_registro = false;

                    if($actividad && $valoracion){
                        $detalle = array('valoracion'=>$valoracion,'actividad'=>$actividad);
                        $sw_registro = true;
                    }
                    $contenido = $request->get('contenido'.$tipos_notas_array[$x]);
                    $resultado = $request->get('resultado'.$tipos_notas_array[$x]);
                    $recomendacion = $request->get('recomendacion'.$tipos_notas_array[$x]);
                    $estado = $request->get('estado'.$tipos_notas_array[$x]);
                    if($contenido && $resultado){
                        $detalle = array('contenido'=>$contenido,'resultado'=>$resultado, 'recomendacion'=>$recomendacion,'estado'=>$estado);
                        $sw_registro = true;
                    }

                    $estimulacion = $request->get('estimulacion'.$tipos_notas_array[$x]);
                    $orientacion = $request->get('orientacion'.$tipos_notas_array[$x]);
                    $deteccion = $request->get('deteccion'.$tipos_notas_array[$x]);
                    if($estimulacion && $orientacion){
                        $detalle = array('estimulacion'=>$estimulacion,'orientacion'=>$orientacion, 'deteccion'=>$deteccion,'estado'=>$estado);
                        $sw_registro = true;
                    }
                    $programa = $request->get('programa'.$tipos_notas_array[$x]);
                    if($programa){
                        $detalle = array('programa'=>$programa, 'estado'=> $request->get('nuevoEstadoMatricula'));
                        $sw_registro = true;
                    }
                    if($sw_registro){                     
                       // dump($detalle);die;
                        //$detalle = array('valoracion'=>$valoracion,'actividad'=>$actividad);
                        $id_tipo_nota = $tipos_notas_array[$x];
                        $newCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$id_tipo_nota));
                        $nuevosw = true;
                        if(!$newCualitativa){ 
                            $newCualitativa = new EstudianteNotaCualitativa();
                            $newCualitativa->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($id_tipo_nota));
                            $newCualitativa->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                            $newCualitativa->setNotaCuantitativa(0);
                            $newCualitativa->setRecomendacion('');
                            $newCualitativa->setUsuarioId($this->session->get('userId'));
                            $newCualitativa->setFechaRegistro(new \DateTime('now'));
                        }else{
                            $nuevosw = false;
                            $anterior = [];
                            $anterior['id'] = $newCualitativa->getId();
                            $anterior['notaTipo'] = $newCualitativa->getNotaTipo()->getId();
                            $anterior['notaCualitativa'] = $newCualitativa->getNotaCualitativa();
                            $anterior['fechaModificacion'] = ($newCualitativa->getFechaModificacion())?$newCualitativa->getFechaModificacion()->format('d-m-Y'):'';
                        }
                            $newCualitativa->setFechaModificacion(new \DateTime('now'));
                            $newCualitativa->setNotaCualitativa(json_encode($detalle));
                            $this->em->persist($newCualitativa);
                            $this->em->flush();
                        
                        //logs
                        if($nuevosw){
                            $arrayNotaCualitativa = [];
                            $arrayNotaCualitativa['id'] = $newCualitativa->getId();
                            $arrayNotaCualitativa['notaTipo'] = $newCualitativa->getNotaTipo()->getId();
                            $arrayNotaCualitativa['estudianteInscripcion'] = $newCualitativa->getEstudianteInscripcion()->getId();
                            $arrayNotaCualitativa['notaCuantitativa'] = $newCualitativa->getNotaCuantitativa();
                            $arrayNotaCualitativa['notaCualitativa'] = $newCualitativa->getNotaCualitativa();
                            $arrayNotaCualitativa['fechaRegistro'] = $newCualitativa->getFechaRegistro()->format('d-m-Y');
                            $arrayNotaCualitativa['fechaModificacion'] = $newCualitativa->getFechaModificacion()->format('d-m-Y');
                            
                            $this->funciones->setLogTransaccion(
                                $newCualitativa->getId(),
                                'estudiante_nota_cualitativa',
                                'C',
                                '',
                                $arrayNotaCualitativa,
                                '',
                                'SERVICIO NOTAS',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );

                         }else{
                                
                                $nuevo = [];
                                $nuevo['id'] = $newCualitativa->getId();
                                $nuevo['notaTipo'] = $newCualitativa->getNotaTipo()->getId();
                                $nuevo['notaCualitativa'] = $newCualitativa->getNotaCualitativa();
                                $nuevo['fechaModificacion'] = ($newCualitativa->getFechaModificacion())?$newCualitativa->getFechaModificacion()->format('d-m-Y'):'';
                                
                                $this->funciones->setLogTransaccion(
                                    $newCualitativa->getId(),
                                    'estudiante_nota_cualitativa',
                                    'U',
                                    '',
                                    $nuevo,
                                    $anterior,
                                    'SERVICIO NOTAS',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                );
                        }

                        //dump("se guardo");die;
                    }
                }
            }

            if($tipo == 'SemestralPrograma'){
                $programa = $request->get('progserv');
                $tipos_notas_array = $request->get('id_nota');
                
                 //las asignaturas son:
                 if($programa == 41)
                 $lista = array(437,438);
                 elseif($programa == 43)
                    $lista = array(439,440);
                 elseif($programa == 44)
                   $lista = array(442,443);
                elseif($programa == 19)
                   $lista = array(401,404,32836,32837,32838);
                 else
                    $lista = array(444,443);

                 $asignaturas =  $this->em->createQuery(
                            'SELECT at
                            FROM SieAppWebBundle:AsignaturaTipo at
                            WHERE at.id IN (:ids)
                            ORDER BY at.id ASC'
                            )->setParameter('ids',$lista)
                            ->getResult();

               
                for($x=0; $x<count($tipos_notas_array); $x++){
                    
                    $detalle = [];
                        foreach ($asignaturas as $as) {
                            $dato = $request->get($as->getContenido().$tipos_notas_array[$x]);
                            if ($dato!=''){
                             $detalle[$as->getContenido()] = $dato;
                            }
                        }     
                      //  dump(json_encode($detalle)); die;
                     if(count($detalle)>0){                     
                        //dump($detalle);die;
                        //$detalle = array('valoracion'=>$valoracion,'actividad'=>$actividad);
                        $id_tipo_nota = $tipos_notas_array[$x];
                        $newCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$id_tipo_nota));
                        $nuevosw = true;
                        if(!$newCualitativa){ 
                            $newCualitativa = new EstudianteNotaCualitativa();
                            $newCualitativa->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($id_tipo_nota));
                            $newCualitativa->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                            $newCualitativa->setNotaCuantitativa(0);
                            $newCualitativa->setRecomendacion('');
                            $newCualitativa->setUsuarioId($this->session->get('userId'));
                            $newCualitativa->setFechaRegistro(new \DateTime('now'));
                        }else{
                            $nuevosw = false;
                            $anterior = [];
                            $anterior['id'] = $newCualitativa->getId();
                            $anterior['notaTipo'] = $newCualitativa->getNotaTipo()->getId();
                            $anterior['notaCualitativa'] = $newCualitativa->getNotaCualitativa();
                            $anterior['fechaModificacion'] = ($newCualitativa->getFechaModificacion())?$newCualitativa->getFechaModificacion()->format('d-m-Y'):'';
                        }
                            $newCualitativa->setFechaModificacion(new \DateTime('now'));
                            $newCualitativa->setNotaCualitativa(json_encode($detalle));
                            $this->em->persist($newCualitativa);
                            $this->em->flush();
                        
                        //logs
                        if($nuevosw){
                            $arrayNotaCualitativa = [];
                            $arrayNotaCualitativa['id'] = $newCualitativa->getId();
                            $arrayNotaCualitativa['notaTipo'] = $newCualitativa->getNotaTipo()->getId();
                            $arrayNotaCualitativa['estudianteInscripcion'] = $newCualitativa->getEstudianteInscripcion()->getId();
                            $arrayNotaCualitativa['notaCuantitativa'] = $newCualitativa->getNotaCuantitativa();
                            $arrayNotaCualitativa['notaCualitativa'] = $newCualitativa->getNotaCualitativa();
                            $arrayNotaCualitativa['fechaRegistro'] = $newCualitativa->getFechaRegistro()->format('d-m-Y');
                            $arrayNotaCualitativa['fechaModificacion'] = $newCualitativa->getFechaModificacion()->format('d-m-Y');
                            
                            $this->funciones->setLogTransaccion(
                                $newCualitativa->getId(),
                                'estudiante_nota_cualitativa',
                                'C',
                                '',
                                $arrayNotaCualitativa,
                                '',
                                'SERVICIO NOTAS',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );

                         }else{
                                
                                $nuevo = [];
                                $nuevo['id'] = $newCualitativa->getId();
                                $nuevo['notaTipo'] = $newCualitativa->getNotaTipo()->getId();
                                $nuevo['notaCualitativa'] = $newCualitativa->getNotaCualitativa();
                                $nuevo['fechaModificacion'] = ($newCualitativa->getFechaModificacion())?$newCualitativa->getFechaModificacion()->format('d-m-Y'):'';
                                
                                $this->funciones->setLogTransaccion(
                                    $newCualitativa->getId(),
                                    'estudiante_nota_cualitativa',
                                    'U',
                                    '',
                                    $nuevo,
                                    $anterior,
                                    'SERVICIO NOTAS',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                );
                        }
                    }
                }
                
            }

            if($tipo == 'SemestralAsignatura'){ //dump("registro de notas"); dump($request);
                $programa = $request->get('progserv');
                $tipos_notas_array = $request->get('id_nota');
                
                $asignaturas =  $this->em->createQuery(
                    'SELECT at.id as asignatura_id, at.asignatura,eat.id
                    FROM SieAppWebBundle:AsignaturaTipo at,
                    SieAppWebBundle:EstudianteAsignatura eat,
                    SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                    WHERE 
                     at.id = ieco.asignaturaTipo and
                     eat.institucioneducativaCursoOferta = ieco.id and
                     eat.estudianteInscripcion IN (:ids)
                     ORDER BY at.id ASC')
                    ->setParameter('ids',$idInscripcion)
                    ->getResult();
               //dump($asignaturas);die;
                for($x=0; $x<count($tipos_notas_array); $x++){
                    $estado = $request->get('estado'.$tipos_notas_array[$x]);
                    $estado_dato = array('estado'=>$estado);
                    $detalle = [];
                        foreach ($asignaturas as $as) {
                            $contenido = $request->get('contenido'.$as["id"].$tipos_notas_array[$x]);
                            $resultado = $request->get('resultado'.$as["id"].$tipos_notas_array[$x]);
                            $recomendacion = $request->get('recomendacion'.$as["id"].$tipos_notas_array[$x]);
                       
                            if($contenido && $resultado){
                               // $detalle = array('contenido'=>$contenido,'resultado'=>$resultado, 'recomendacion'=>$recomendacion,'estado'=>$estado);
                                $detalle = array('contenido'=>$contenido,'resultado'=>$resultado);
                               
                            }
                            if(count($detalle)>0){    
                                $newNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$as["id"], 'notaTipo'=>$tipos_notas_array[$x]));
                                if(!$newNota){ 
                                    $newNota = new EstudianteNota();
                                    $newNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($tipos_notas_array[$x]));
                                    $newNota->setEstudianteAsignatura($this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($as["id"]));
                                    $newNota->setNotaCuantitativa(0);
                                    $newNota->setUsuarioId($this->session->get('userId'));
                                    $newNota->setFechaRegistro(new \DateTime('now'));
                                }
                                $newNota->setNotaCualitativa(json_encode($detalle));
                                $newNota->setRecomendacion($recomendacion);
                                $newNota->setObs(json_encode($estado_dato));
                                $newNota->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($newNota);
                                $this->em->flush();
                            }
                        }     
                        if(count($asignaturas)==0){
                            $newCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$tipos_notas_array[$x]));
                        
                            if(!$newCualitativa){ 
                                $newCualitativa = new EstudianteNotaCualitativa();
                                $newCualitativa->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($tipos_notas_array[$x]));
                                $newCualitativa->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                                $newCualitativa->setNotaCuantitativa(0);
                                $newCualitativa->setRecomendacion('');
                                $newCualitativa->setUsuarioId($this->session->get('userId'));
                                $newCualitativa->setFechaRegistro(new \DateTime('now'));
                            }
                                $newCualitativa->setFechaModificacion(new \DateTime('now'));
                                $newCualitativa->setNotaCualitativa(json_encode($estado_dato));
                                $this->em->persist($newCualitativa);
                                $this->em->flush();
                        }
                }
            }

            if($tipo == 'TrimestralPrograma'){ //dump("registro de notas semestral o trimestral cuakquiera"); dump($request);
                $programa = $request->get('progserv');
                $tipos_notas_array = $request->get('idNotaTipo');
               //dump($tipos_notas_array);die;
                $asignaturas =  $this->em->createQuery(
                    'SELECT at.id as asignatura_id, at.asignatura,eat.id
                    FROM SieAppWebBundle:AsignaturaTipo at,
                    SieAppWebBundle:EstudianteAsignatura eat,
                    SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                    WHERE 
                     at.id = ieco.asignaturaTipo and
                     eat.institucioneducativaCursoOferta = ieco.id and
                     eat.estudianteInscripcion IN (:ids)
                     ORDER BY at.id ASC')
                    ->setParameter('ids',$idInscripcion)
                    ->getResult();
                    //$estado = $request->get('estado');
                    //dump($request->get('nuevoEstadomatricula'.$tipos_notas_array[0]));die;
                for($x=0; $x<count($tipos_notas_array); $x++){
                    //$detalle = [];
                        foreach ($asignaturas as $as) {
                            $nota = $request->get('nota'.$as["id"].$tipos_notas_array[$x]);
                            $recomendacion = $request->get('recomendacion'.$as["id"].$tipos_notas_array[$x]);
                
                            if($nota && $recomendacion){    
                                $newNota = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$as["id"], 'notaTipo'=>$tipos_notas_array[$x]));
                                if(!$newNota){ 
                                    $newNota = new EstudianteNota();
                                    $newNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($tipos_notas_array[$x]));
                                    $newNota->setEstudianteAsignatura($this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($as["id"]));
                                    $newNota->setNotaCuantitativa(0);
                                    $newNota->setUsuarioId($this->session->get('userId'));
                                    $newNota->setFechaRegistro(new \DateTime('now'));
                                }
                                $newNota->setNotaCualitativa($nota);
                                $newNota->setRecomendacion($recomendacion);
                                $newNota->setFechaModificacion(new \DateTime('now'));
                                $this->em->persist($newNota);
                                $this->em->flush();
                            }
                        }     
                       
                }
               
                if($request->get('informeSemestral'.$tipos_notas_array[0])!='' or $request->get('nuevoEstadomatricula'.$tipos_notas_array[0])!=''){
                    $detalleEstado ='';
                    if($request->get('nuevoEstadomatricula'.$tipos_notas_array[0])!=''){
                        $estadoFinalNota = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($request->get('nuevoEstadomatricula'.$tipos_notas_array[0]));
                        $detalleEstado = array('id'=>$request->get('nuevoEstadomatricula'.$tipos_notas_array[0]), 'estado'=>$estadoFinalNota->getEstadomatricula());
                    }
                    
                    $cualitativoNota  = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion' => $request->get('idInscripcion'), 'notaTipo' => $tipos_notas_array[0]));
                    if(!$cualitativoNota){
                        $cualitativoNota = new EstudianteNotaCualitativa();
                        $cualitativoNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($tipos_notas_array[0]));
                        $cualitativoNota->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($request->get('idInscripcion')));
                        $cualitativoNota->setNotaCuantitativa(0);
                        $cualitativoNota->setRecomendacion('');
                        $cualitativoNota->setUsuarioId($this->session->get('userId'));
                        $cualitativoNota->setFechaRegistro(new \DateTime('now'));
                    }
                    $cualitativoNota->setNotaCualitativa($request->get('informeSemestral'.$tipos_notas_array[0]));                               
                    $cualitativoNota->setFechaModificacion(new \DateTime('now'));
                    $cualitativoNota->setObs(json_encode($detalleEstado));
                    $this->em->persist($cualitativoNota);
                    $this->em->flush();
                }
            }

            if($tipo == 'Modular'){   // dump("modular");
                $tipos_notas_array = $request->get('id_nota');
               
                for($x=0; $x<count($tipos_notas_array); $x++){
                    $cultura = $request->get('cultura'.$tipos_notas_array[$x]);
                    $identidad = $request->get('identidad'.$tipos_notas_array[$x])?$request->get('identidad'.$tipos_notas_array[$x]):'';
                    $lengua = $request->get('lengua'.$tipos_notas_array[$x]);
                    $dactilologia = $request->get('dactilologia'.$tipos_notas_array[$x])?$request->get('dactilologia'.$tipos_notas_array[$x]):'';
                    $dialogo = $request->get('dialogo'.$tipos_notas_array[$x])?$request->get('dialogo'.$tipos_notas_array[$x]):'';
                    $produccion = $request->get('produccion'.$tipos_notas_array[$x]);
                    $estado = $request->get('nuevoEstado'.$tipos_notas_array[$x]);
                    
                    if($cultura && $lengua && $produccion && $estado){
                        $estado_modulo = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find( $estado);

                        $id_tipo_nota = $tipos_notas_array[$x];
                        $detalle = array('cultura'=>$cultura,'identidad'=>$identidad, 'lengua'=>$lengua, 'dactilologia'=>$dactilologia ,'dialogo'=>$dialogo, 'produccion'=>$produccion);
                       // dump($detalle);die;
                        $newCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$id_tipo_nota));
                        $nuevosw = true;
                        if(!$newCualitativa){ 
                            $newCualitativa = new EstudianteNotaCualitativa();
                            $newCualitativa->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($id_tipo_nota));
                            $newCualitativa->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                            $newCualitativa->setNotaCuantitativa(0);
                            $newCualitativa->setUsuarioId($this->session->get('userId'));
                            $newCualitativa->setFechaRegistro(new \DateTime('now'));
                        }else{
                            $nuevosw = false;
                            $anterior = [];
                            $anterior['id'] = $newCualitativa->getId();
                            $anterior['notaTipo'] = $newCualitativa->getNotaTipo()->getId();
                            $anterior['notaCualitativa'] = $newCualitativa->getNotaCualitativa();
                            $anterior['fechaModificacion'] = ($newCualitativa->getFechaModificacion())?$newCualitativa->getFechaModificacion()->format('d-m-Y'):'';
                        }
                            $newCualitativa->setFechaModificacion(new \DateTime('now'));
                            $newCualitativa->setNotaCualitativa(json_encode($detalle));
                            $newCualitativa->setRecomendacion($estado_modulo->getEstadomatricula());
                            $this->em->persist($newCualitativa);
                            $this->em->flush();
                            
                        //logs
                        if($nuevosw){ 
                            $arrayNotaCualitativa = [];
                            $arrayNotaCualitativa['id'] = $newCualitativa->getId();
                            $arrayNotaCualitativa['notaTipo'] = $newCualitativa->getNotaTipo()->getId();
                            $arrayNotaCualitativa['estudianteInscripcion'] = $newCualitativa->getEstudianteInscripcion()->getId();
                            $arrayNotaCualitativa['notaCuantitativa'] = $newCualitativa->getNotaCuantitativa();
                            $arrayNotaCualitativa['notaCualitativa'] = $newCualitativa->getNotaCualitativa();
                            $arrayNotaCualitativa['fechaRegistro'] = $newCualitativa->getFechaRegistro()->format('d-m-Y');
                            $arrayNotaCualitativa['fechaModificacion'] = $newCualitativa->getFechaModificacion()->format('d-m-Y');
                            
                            $this->funciones->setLogTransaccion(
                                $newCualitativa->getId(),
                                'estudiante_nota_cualitativa',
                                'C',
                                '',
                                $arrayNotaCualitativa,
                                '',
                                'SERVICIO NOTAS',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                            );

                         }else{ 
                                
                                $nuevo = [];
                                $nuevo['id'] = $newCualitativa->getId();
                                $nuevo['notaTipo'] = $newCualitativa->getNotaTipo()->getId();
                                $nuevo['notaCualitativa'] = $newCualitativa->getNotaCualitativa();
                                $nuevo['fechaModificacion'] = ($newCualitativa->getFechaModificacion())?$newCualitativa->getFechaModificacion()->format('d-m-Y'):'';
                                
                                $this->funciones->setLogTransaccion(
                                    $newCualitativa->getId(),
                                    'estudiante_nota_cualitativa',
                                    'U',
                                    '',
                                    $nuevo,
                                    $anterior,
                                    'SERVICIO NOTAS',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                                );
                        }
                    }
                   
                }
                
            }

            if($tipo == 'semestralTrimestralSeguimiento'){  //TODO se mezclo con el nuevo seguimiento, corregir
                
                //$estadoFinalNota = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($request->get('nuevoEstadomatricula'));
                $estadoFinalNota = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($request->get('nuevoEstadomatricula'));
                $detalleEstado = array('id'=>$estadoFinalNota->getId(), 'estado'=>$estadoFinalNota->getEstadomatricula());

                $cualitativoNota  = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion' => $request->get('idInscripcion'), 'notaTipo' => $request->get('idNota')));
                if(!$cualitativoNota){
                    $cualitativoNota = new EstudianteNotaCualitativa();
                    $cualitativoNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($request->get('idNota')));
                    $cualitativoNota->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($request->get('idInscripcion')));
                    $cualitativoNota->setNotaCuantitativa(0);
                    $cualitativoNota->setRecomendacion('');
                    $cualitativoNota->setUsuarioId($this->session->get('userId'));
                    $cualitativoNota->setFechaRegistro(new \DateTime('now'));
                }
                $cualitativoNota->setNotaCualitativa('');                               
                $cualitativoNota->setFechaModificacion(new \DateTime('now'));
                $cualitativoNota->setObs(json_encode($detalleEstado));
                $this->em->persist($cualitativoNota);
                $this->em->flush();

                $cualitativoDetalleNota  = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativaDetalle')->findOneBy(array('estudianteNotaCualitativa' => $cualitativoNota->getId()));
                if(!$cualitativoDetalleNota){
                    $cualitativoDetalleNota = new EstudianteNotaCualitativaDetalle();
                    $cualitativoDetalleNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($request->get('idNota')));
                    $cualitativoDetalleNota->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($request->get('idInscripcion')));
                    $cualitativoDetalleNota->setEstudianteNotaCualitativa($this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($cualitativoNota->getId()));
                    $cualitativoDetalleNota->setUsuarioId($this->session->get('userId'));
                    $cualitativoDetalleNota->setFechaRegistro(new \DateTime('now'));
                }
                $cualitativoDetalleNota->setContenido($request->get('seguimiento'));                
                $cualitativoDetalleNota->setFechaModificacion(new \DateTime('now'));
                $this->em->persist($cualitativoDetalleNota);
                $this->em->flush();
               
            }
            // Datos del siguimiento
            if($gestion > 20233333 and $discapacidad == 2 and in_array($request->get('progserv') , array('47','48' )) ){
                dump("seguimiento");
                dump($request);
                die;
                $seguimientoNota  = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion' => $request->get('idInscripcion'), 'notaTipo' => $request->get('tipoNota')));
                if(!$seguimientoNota){
                    $seguimientoNota = new EstudianteNotaCualitativa();
                    $seguimientoNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($request->get('tipoNota')));
                    $seguimientoNota->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($request->get('idInscripcion')));
                    $seguimientoNota->setNotaCuantitativa(0);
                    $seguimientoNota->setRecomendacion('');
                    $seguimientoNota->setUsuarioId($this->session->get('userId'));
                    $seguimientoNota->setFechaRegistro(new \DateTime('now'));
                }
                $seguimientoNota->setNotaCualitativa($request->get('seguimiento'));                
                $seguimientoNota->setFechaModificacion(new \DateTime('now'));
                $seguimientoNota->setObs('');
                $this->em->persist($seguimientoNota);
                $this->em->flush();
            }
            if($gestion > 2019999 and $discapacidad != 7 and $discapacidad != 6 and ($discapacidad == 4 or ($discapacidad != 5 and ($nivel == 411 or $nivel==410)) or ($discapacidad == 1 and ($request->get('progserv') == 200 or $request->get('progserv') == 21 )))){
               
                dump("seguimiento2");
                dump($request);
                die;
               
                $seguimientoNota  = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion' => $request->get('idInscripcion'), 'notaTipo' => $request->get('tipoNota')));
                if(!$seguimientoNota){
                    $seguimientoNota = new EstudianteNotaCualitativa();
                    $seguimientoNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($request->get('tipoNota')));
                    $seguimientoNota->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($request->get('idInscripcion')));
                    $seguimientoNota->setNotaCuantitativa(0);
                    $seguimientoNota->setRecomendacion('');
                    $seguimientoNota->setUsuarioId($this->session->get('userId'));
                    $seguimientoNota->setFechaRegistro(new \DateTime('now'));
                }
                $seguimientoNota->setNotaCualitativa($request->get('seguimiento'));                
                $seguimientoNota->setFechaModificacion(new \DateTime('now'));
                $seguimientoNota->setObs('');
                $this->em->persist($seguimientoNota);
                $this->em->flush();
            }
            if(!empty($request->get('progserv')) and $gestion > 2018 and $gestion < 2023 and $discapacidad == 1 and $request->get('progserv') == 19){
                dump("seguimiento3");
                dump($request);
                die;
                $seguimiento = array();
                $seguimiento['anho'] = $request->get('anho');
                $seguimiento['resumen'] = mb_strtoupper($request->get('resumen'), 'utf-8');
                $seguimiento['promanual'] = $request->get('promanual');
                $seguimientoNota = new EstudianteNotaCualitativa();
                $seguimientoNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($request->get('tipoNota')));
                $seguimientoNota->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($request->get('idInscripcion')));
                $seguimientoNota->setNotaCuantitativa(0);
                $seguimientoNota->setNotaCualitativa(json_encode($seguimiento));
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

    public function especialVisualRegistro(Request $request, $discapacidad){
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        // Validar si existe la session del usuario
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        try {
            //dump($request);die;
            $this->em->getConnection()->beginTransaction();
            // Datos de las notas cuantitativas
            $idEstudianteNota = $request->get('idEstudianteNota');
            $idNotaTipo = $request->get('idNotaTipo');
            $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
            $gestion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($request->get('idInscripcion'))->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            $fechaEtapa = $request->get('fechaEtapa');
            $contenidos = $request->get('contenidos');
            $resultados = $request->get('resultados');
            $indicador = $request->get('indicador');
            $estado = $request->get('estado');
            $datosNotas = array();
            if($contenidos){
                foreach ($contenidos as $i => $c){
                    $datosNotas[] = array('contenidos'=>mb_strtoupper($c,'utf-8'),'resultados'=>mb_strtoupper($resultados[$i],'utf-8'),'idIndicador'=>$indicador[$i],'idEstado'=>$estado[$i]);
                }
            }
            $notas = $datosNotas;

            // Datos de las notas cualitativas
            $idEstudianteNotaCualitativa = $request->get('idEstudianteNotaCualitativa');
            $idNotaTipoCualitativa = $request->get('idNotaTipoCualitativa');
            $idInscripcion = $request->get('idInscripcion');
            $notaCualitativa =  array();
            if($fechaEtapa){
                $notaCualitativa = array('etapa'=>key($fechaEtapa),'estadoEtapa'=>$request->get('notaCualitativa'),'fechaEtapa'=>current($fechaEtapa));
            }
            //dump($notaCualitativa);die;
            // Registro de notas visual
            for($i=0;$i<count($idEstudianteNota);$i++) {
                
                $newNota = new EstudianteNota();
                $newNota->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$i]));
                $newNota->setEstudianteAsignatura($this->em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                $newNota->setNotaCuantitativa(0);
                $newNota->setNotaCualitativa(json_encode($notas[$i]));
                $newNota->setRecomendacion('');
                $newNota->setUsuarioId($this->session->get('userId'));
                $newNota->setFechaRegistro(new \DateTime('now'));
                $newNota->setFechaModificacion(new \DateTime('now'));
                $newNota->setObs('');
                $this->em->persist($newNota);
                $this->em->flush();
            }

            // Registro de notas cualitativas visual
            if($notaCualitativa){ 
                $notaCualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$idNotaTipoCualitativa));
                if(!$notaCualitativas){
                    $newCualitativas = new EstudianteNotaCualitativa();
                    $newCualitativas->setNotaTipo($this->em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipoCualitativa));
                    $newCualitativas->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                    $newCualitativas->setNotaCuantitativa(0);
                }
                $newCualitativas->setNotaCualitativa(json_encode($notaCualitativa));
                $newCualitativas->setRecomendacion('');
                $newCualitativas->setUsuarioId($this->session->get('userId'));
                $newCualitativas->setFechaRegistro(new \DateTime('now'));
                $newCualitativas->setFechaModificacion(new \DateTime('now'));
                $newCualitativas->setObs('');
                $this->em->persist($newCualitativas);
                $this->em->flush();
            }
            
            
            $this->em->getConnection()->commit();
            return new JsonResponse(array('msg'=>'ok'));
        } catch (Exception $e) {
            $this->em->getConnection()->rollback();
            return new JsonResponse(array('msg'=>'error'));
        }
    }

    public function especial_seguimiento($idInscripcion,$operativo,$idNota){
        try {
            $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
            $observacion = $inscripcion->getInstitucioneducativaCurso()->getLugar();
            $cursoEspecial = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array('institucioneducativaCurso'=>$inscripcion->getInstitucioneducativaCurso()->getId()));
            $subarea = $cursoEspecial->getEspecialAreaTipo()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            $gestionActual = $this->session->get('currentyear');
            $programa = $cursoEspecial->getEspecialProgramaTipo()->getId();
            $servicio = $cursoEspecial->getEspecialServicioTipo()->getId();
            $momento = $cursoEspecial->getEspecialMomentoTipo()->getId();

            $idNotaTipo = 0;
            $arrayCualitativas = array();
            // $tipoNota = $this->getTipoNota($sie,$gestion,$nivel,$grado,$subarea);
            $tipoNota = $this->em->getRepository('SieAppWebBundle:NotaTipo')->findOneBy(array('obs'=>'SEG'));
            if($tipoNota) {
                $idNotaTipo = $tipoNota->getId();
                $notaCualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$idNotaTipo));
                if($notaCualitativas) {
                    $arrayCualitativas = json_decode($notaCualitativas->getNotaCualitativa(), true);
                    $enota = 0; // 1
                } else {
                    $enota = 0;
                }

            }
            $valoraciones = '';
            if (($subarea == 4 or $subarea == 7 or $subarea == 2 or $subarea == 3 or $subarea == 5 or $subarea == 1 ) and $gestion > 2019){
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(10,78,79));
            }elseif ($subarea == 6 and $gestion > 2019){
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(10,78,79));
               
            }else{
                $estadosFinales = array();
            }

            $estadosPermitidos = array(4,7,68);
            $tiposNotasArray = array();
            
            //Nueva evaluacion de talento y dificultades en el aprendizaje
            if ( ($subarea == 6 or $subarea == 7)  and $gestion > 2021){ 
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(10,78,79));
                $valoraciones = $this->em->getRepository('SieAppWebBundle:EspecialNivelTalento')->findAll();
                $idNotaTipo = 'Semestral';
                $tiposNotas = $this->em->getRepository('SieAppWebBundle:NotaTipo')->findBy(array('obs'=>'Semestral'));
                foreach ($tiposNotas as $tn) {
                    $notaCualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$tn->getId()));
                    $valoracion = '';
                    $actividad = '';
                    if($notaCualitativas){
                        $arrayCualitativas = json_decode($notaCualitativas->getNotaCualitativa(), true);
                        $valoracion = $arrayCualitativas["valoracion"];
                        $actividad = $arrayCualitativas["actividad"];
                    }
                    $tiposNotasArray[] = array('id'=>$tn->getId(),'nota'=>$tn->getNotaTipo(),'actividad'=> $actividad, 'valoracion'=> $valoracion);
                }
            }

            
            if ( $subarea == 1   and $gestion > 2022 and ($programa==22 or $servicio==40)){ //Nueva evaluacion de programa modular
                $cualitativas = $this->em->createQueryBuilder()
                ->select('ei.id as idEstudianteInscripcion,enc.id as idEstudianteCualitativo, nt.id as idNotaTipo,enc.notaCualitativa,nt.notaTipo,gt.id as gestion')
                ->from('SieAppWebBundle:EstudianteNotaCualitativa','enc')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','enc.estudianteInscripcion = ei.id')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','iec.id = ei.institucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoEspecial','iece','with','iece.institucioneducativaCurso = iec.id')
                ->innerJoin('SieAppWebBundle:NotaTipo','nt','with','enc.notaTipo = nt.id')
                ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                ->where('ei.estudiante = '. $inscripcion->getEstudiante()->getId())
                ->andWhere('gt.id > 2022')
                ->andWhere('iece.especialProgramaTipo = '.$programa)
                ->orderBy('gt.id','DESC')
                ->addOrderBy('nt.id','DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getResult(); 
               
               // dump($cualitativas);die;
                $ultimo = 55;
                if($cualitativas)
                    $ultimo =  $cualitativas[0]["idNotaTipo"];

                
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(28,37));
                if($ultimo == 58)
                    $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(28,37,106));
                //$estadosPermitidos = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(28,37));
                $idNotaTipo = 'Modular';
                $tiposNotas = $this->em->getRepository('SieAppWebBundle:NotaTipo')->findBy(array('obs'=>'Modular'));
                //         dump($tiposNotas);die;
                foreach ($tiposNotas as $tn) {
                    $notaCualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$tn->getId()));
                    $cultura = '';
                    if($tn->getAbrev()=='M1'){
                        $identidad = '';
                        $lengua = '';
                        $dactilologia = '';
                        $dialogo = '';
                       }
                    elseif($tn->getAbrev()=='M3'){                       
                        $identidad = 'noaplica';
                        $lengua = '';
                        $dactilologia = 'noaplica';
                        $dialogo = '';
                        
                    }else{
                        $identidad = 'noaplica';
                        $lengua = '';
                        $dactilologia = 'noaplica';
                        $dialogo = 'noaplica';
                    }
                    $produccion = '';
                    $recomendacion = '';
                   // $estado = '';

                    if($notaCualitativas){
                        $arrayCualitativas = json_decode($notaCualitativas->getNotaCualitativa(), true);       
                       // dump($arrayCualitativas->getRecomendacion());die;
                        $cultura = $arrayCualitativas["cultura"];
                        if($tn->getAbrev()=='M1'){
                            $identidad = $arrayCualitativas["identidad"];
                            $lengua = $arrayCualitativas["lengua"];
                            $dactilologia = $arrayCualitativas["dactilologia"];
                            $dialogo = $arrayCualitativas["dialogo"];
                            
                        }elseif($tn->getAbrev()=='M3'){
                            $cultura = $arrayCualitativas["cultura"];
                            $lengua =  $arrayCualitativas["lengua"];
                            $dialogo = $arrayCualitativas["dialogo"];
                        
                        }else{
                            $cultura = $arrayCualitativas["cultura"];
                            $lengua =  $arrayCualitativas["lengua"];
                        }
                    
                        $produccion = $arrayCualitativas["produccion"];
                        $recomendacion = $notaCualitativas->getRecomendacion();
                        
                    }

                    if($tn->getNotaTipo())
                    $tiposNotasArray[] = array('id'=>$tn->getId(),'nota'=>$tn->getNotaTipo(),'cultura'=> $cultura,'identidad'=> $identidad,'lengua'=> $lengua,'dactilologia'=> $dactilologia,'dialogo'=> $dialogo,'produccion'=> $produccion, 'recomendacion'=> $recomendacion, 'ultimo'=> $ultimo);
                }
                
            }

            if ( $subarea == 1   and $gestion > 2022222 and in_array($programa,array(19,41,43,44,46)) ){ 
                //Nueva evaluacion de programa de auditiva con diferentes asignaturas
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(47,78));
                if(in_array($programa, array(44,46,19)))
                    $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(28,37,106));
                //las asignaturas son:
                if($programa == 41)
                    $lista = array(437,438);
                elseif($programa == 43)
                    $lista = array(439,440);
                elseif($programa == 44)
                    $lista = array(442,443);
                elseif($programa == 19)
                    $lista = array(401,404,32836,32837,32838);
                else
                    $lista = array(444,443);

                $idNotaTipo = 'SemestralPrograma';
                $tiposNotas = $this->em->getRepository('SieAppWebBundle:NotaTipo')->findBy(array('obs'=>'Semestral'));
                $asignaturas =  $this->em->createQuery(
                    'SELECT at
                    FROM SieAppWebBundle:AsignaturaTipo at
                    WHERE at.id IN (:ids)
                    ORDER BY at.id ASC'
                    )->setParameter('ids',$lista)
                    ->getResult();
                //dump($asignaturas);die;
                foreach ($asignaturas as $as) {
                    $titulo = $as->getAsignatura();
                    $subNotasArray = array();
                    foreach ($tiposNotas as $tn) {

                        $notaCualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$tn->getId()));
                        $contenido = '';
                        if($notaCualitativas){
                            $arrayCualitativas = json_decode($notaCualitativas->getNotaCualitativa(), true);       
                            $contenido = $arrayCualitativas[$as->getContenido()];
                        }

                        if($tn->getNotaTipo())
                         $subNotasArray[] = array('id'=>$tn->getId(),'nota'=>$tn->getNotaTipo(),"nombre" =>$as->getContenido().''.$tn->getId(), "contenido"=>$contenido );
                    }
                    
                     $tiposNotasArray[] = array('ida'=>$as->getId(),'titulo'=>$as->getAsignatura(),"subnotas" =>$subNotasArray );
                }
                
            }

            if ($subarea == 2 and $gestion > 20222222 and $programa == 26 and $momento<3){ 
                //Nueva evaluacion de programa de visual con diferentes asignaturas
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(78,79,80));
                $idNotaTipo = 'SemestralAsignatura';
                $tiposNotas = $this->em->getRepository('SieAppWebBundle:NotaTipo')->findBy(array('obs'=>'Semestral'));
                $asignaturas =  $this->em->createQuery(
                        'SELECT at.id as asignatura_id, at.asignatura,eat.id
                        FROM SieAppWebBundle:AsignaturaTipo at,
                        SieAppWebBundle:EstudianteAsignatura eat,
                        SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                        WHERE 
                         at.id = ieco.asignaturaTipo and
                         eat.institucioneducativaCursoOferta = ieco.id and
                         eat.estudianteInscripcion IN (:ids)
                        ORDER BY at.id ASC'
                        )->setParameter('ids',$idInscripcion)
                        ->getResult();
                    // dump($idInscripcion);die;
                    foreach ($tiposNotas as $tn) {
                        $subNotasArray = array();
                        $estado = '';
                        foreach ($asignaturas as $as) {
                            $notaCualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$as["id"], 'notaTipo'=>$tn->getId()));
                            $contenido = '';
                            $resultado = '';
                            $recomendacion = '';
                          
                            if($notaCualitativas){
                                $arrayCualitativas = json_decode($notaCualitativas->getNotaCualitativa(), true); 
                                $contenido = $arrayCualitativas["contenido"];
                                $resultado = $arrayCualitativas["resultado"];
                                $recomendacion = $arrayCualitativas["recomendacion"];   
                             //   $estado = $arrayCualitativas["estado"];   
                            }
                            if($tn->getNotaTipo())
                                $subNotasArray[] = array('idea'=>$as["id"],'asignatura'=>$as["asignatura"],'contenido'=> $contenido,'resultado'=> $resultado, 'recomendacion'=> $recomendacion );
                        }
                        $cualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$tn->getId()));
                        if($cualitativa){
                            $arrayCualitativa = json_decode($cualitativa->getNotaCualitativa(), true); 
                            $estado = $arrayCualitativas["estado"];
                        }
                        $tiposNotasArray[] = array('id'=>$tn->getId(),'semestre'=>$tn->getNotaTipo(), 'estado'=> $estado, "subnotas" =>$subNotasArray );
                }  
                //dump($tiposNotasArray);die;
            } 
         

            if ( $subarea == 222222 and $gestion > 2022 and ($nivel==411 or $nivel==410) and $momento>2 ){ 
                //Visual - Programa Semestral Contenido-resultado y recomendacion
                // dump("visual..........OK");
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(78,79,80));
                $idNotaTipo = 'Semestral';
                $tiposNotas = $this->em->getRepository('SieAppWebBundle:NotaTipo')->findBy(array('obs'=>'Semestral'));
                $subNotasArray = array();
               
                foreach ($tiposNotas as $tn) {
                    $notaCualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$tn->getId()));
                    $contenido = '';
                    $resultado = '';
                    $recomendacion = '';
                    $estado = '';
                    if($notaCualitativas){
                        $arrayCualitativas = json_decode($notaCualitativas->getNotaCualitativa(), true);
                        $contenido = $arrayCualitativas["contenido"];
                        $resultado = $arrayCualitativas["resultado"];
                        $recomendacion = $arrayCualitativas["recomendacion"];   
                        $estado = $arrayCualitativas["estado"];   
                    }
                    $tiposNotasArray[] = array('id'=>$tn->getId(),'nota'=>$tn->getNotaTipo(),'titulo'=>$tn->getNotaTipo(),'contenido'=> $contenido, 'resultado'=> $resultado, 'recomendacion'=> $recomendacion, 'estado'=> $estado);
                }

               // dump($tiposNotasArray);die;
            }
           
             //cualitativo sin areas CONTENIDOS(ACTIVIDADES)-RESULTADOS-RECOMENDACIONES SEMESTRAL
             //if (($subarea == 3 or $subarea == 2) and $gestion > 2023 and ($nivel==411 or $nivel==410) and ($momento>2 or $servicio==20)){  
              if ($gestion > 2023 and (($subarea == 3 or $subarea == 2) and ($nivel==411 or $nivel==410)) or ($nivel==410 and $servicio==20)  ){  
                
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(78,47)); //PROSIGUE-CONCLUIDO-CONTINUA
                if(in_array($programa, array(47,48)) or in_array($servicio, array(35,36,37,38)))
                    $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(78,79,80)); //PROSIGUE-CONCLUIDO-EXTENDIDO
                if($servicio == 20)
                    $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(78,79,10,6)); //PROSIGUE-CONCLUIDO-retiro.abandono, no incorporado
                $idNotaTipo = 'semestralTrimestralSeguimiento';
                $notaCualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$idNota));
                if($notaCualitativa){
                    $detalle = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativaDetalle')->findOneBy(array('estudianteNotaCualitativa'=>$notaCualitativa->getId()));
                    $lista = json_decode($detalle->getContenido(), true);
                    foreach ($lista as $tn) {
                        $contenido = $tn["con"];
                        $resultado = $tn["res"];
                        $recomendacion = $tn["rec"];
                        $arrayCualitativas[] = array('con'=> $contenido, 'res'=> $resultado, 'rec'=> $recomendacion);
                    }
                }
             }
             if ($subarea == 3 and $gestion > 2023 and $nivel==409 ){  
                //Nueva evaluacion de programa con  asignaturas
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(47,78));
                $idNotaTipo = 'SemestralAsignatura';
                $tiposNotas = $this->em->getRepository('SieAppWebBundle:NotaTipo')->findBy(array('obs'=>'Semestral'));
                $asignaturas =  $this->em->createQuery(
                        'SELECT at.id as asignatura_id, at.asignatura,eat.id
                        FROM SieAppWebBundle:AsignaturaTipo at,
                        SieAppWebBundle:EstudianteAsignatura eat,
                        SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                        WHERE 
                         at.id = ieco.asignaturaTipo and
                         eat.institucioneducativaCursoOferta = ieco.id and
                         eat.estudianteInscripcion IN (:ids)
                        ORDER BY at.id ASC'
                        )->setParameter('ids',$idInscripcion)
                        ->getResult();
                    // dump($idInscripcion);die;
                    foreach ($tiposNotas as $tn) {
                        $subNotasArray = array();
                        $estado = '';
                        foreach ($asignaturas as $as) {
                            $notaCualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$as["id"], 'notaTipo'=>$tn->getId()));
                            $contenido = '';
                            $resultado = '';
                            $recomendacion = '';
                          
                            if($notaCualitativas){
                                $arrayCualitativas = json_decode($notaCualitativas->getNotaCualitativa(), true); 
                                $contenido = $arrayCualitativas["contenido"];
                                $resultado = $arrayCualitativas["resultado"];
                                $recomendacion = $arrayCualitativas["recomendacion"];   
                             //   $estado = $arrayCualitativas["estado"];   
                            }
                            if($tn->getNotaTipo())
                                $subNotasArray[] = array('idea'=>$as["id"],'asignatura'=>$as["asignatura"],'contenido'=> $contenido,'resultado'=> $resultado, 'recomendacion'=> $recomendacion );
                        }
                        $cualitativa = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$tn->getId()));
                        if($cualitativa){
                            $arrayCualitativa = json_decode($cualitativa->getNotaCualitativa(), true); 
                            $estado = $arrayCualitativas["estado"];
                        }
                        $tiposNotasArray[] = array('id'=>$tn->getId(),'semestre'=>$tn->getNotaTipo(), 'estado'=> $estado, "subnotas" =>$subNotasArray );
                    }  
                //dump($tiposNotasArray);die;
            } 
         

            if ( $subarea == 3 and $gestion > 2022 and $nivel==409){ //Intelectual - Atencion temprana
                //dump($subarea);die;
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(47,78));
                $idNotaTipo = 'Semestral';
                $tiposNotas = $this->em->getRepository('SieAppWebBundle:NotaTipo')->findBy(array('obs'=>'Semestral'));
                foreach ($tiposNotas as $tn) {
                    $notaCualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$tn->getId()));
                    $estimulacion = '';
                    $orientacion = '';
                    $deteccion = '';
                    if($notaCualitativas){
                        $arrayCualitativas = json_decode($notaCualitativas->getNotaCualitativa(), true);
                        $estimulacion = $arrayCualitativas["estimulacion"];
                        $orientacion = $arrayCualitativas["orientacion"];
                        $deteccion = $arrayCualitativas["deteccion"];   
                    }
                    $tiposNotasArray[] = array('id'=>$tn->getId(),'nota'=>$tn->getNotaTipo(),'estimulacion'=> $estimulacion, 'orientacion'=> $orientacion, 'deteccion'=> $deteccion);
                }
            }
            if ( $subarea == 3 and $gestion == 2023 and $nivel==411 and $programa==37){ //Intelectual - 2023 itinerario personalizado - semestral
               // dump($subarea);die;
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(47,5));
                $idNotaTipo = 'Semestral';
                $tiposNotas = $this->em->getRepository('SieAppWebBundle:NotaTipo')->findBy(array('obs'=>'SEG'));
                foreach ($tiposNotas as $tn) {
                    $notaCualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$tn->getId()));
                   
                    $contenido = '';
                    $resultado = '';
                    $recomendacion = '';
                    $estado = '';
                    if($notaCualitativas){
                        $arrayCualitativas = json_decode($notaCualitativas->getNotaCualitativa(), true);
                        $contenido = $arrayCualitativas["contenido"];
                        $resultado = $arrayCualitativas["resultado"];
                        $recomendacion = $arrayCualitativas["recomendacion"];   
                        $estado = $arrayCualitativas["estado"];   
                    }
                    $tiposNotasArray[] = array('id'=>$tn->getId(),'nota'=>$tn->getNotaTipo(),'contenido'=> $contenido, 'resultado'=> $resultado, 'recomendacion'=> $recomendacion, 'estado'=> $estado);
                }
                //dump( $tiposNotasArray);die;
            }
            if ( $subarea == 10 and $gestion > 2023 and $nivel==411){ //Mental Psiquica
                
                $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(47, 5));
                $idNotaTipo = 'Semestral';
                $tiposNotas = $this->em->getRepository('SieAppWebBundle:NotaTipo')->findBy(array('obs'=>'Semestral'));
                foreach ($tiposNotas as $tn) {
                    $notaCualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$tn->getId()));
                    $programa = '';
                    $estado = '';
                    if($notaCualitativas){
                        $arrayCualitativas = json_decode($notaCualitativas->getNotaCualitativa(), true);
                        $programa = $arrayCualitativas["programa"];
                        $estado = $arrayCualitativas["estado"]; 
                    }
                    $tiposNotasArray[] = array('id'=>$tn->getId(),'nota'=>$tn->getNotaTipo(),'programa'=> $programa,'estado'=> $estado);
                }
            }
           // dump($nivel);
            //cualitativo sin areas ACCIONES-RESULTADOS-RECOMENDACIONES sea TRIMESTRAL/SEMESTRAL - ATENCION TEMPRANA
            if ($gestion > 20233333 and ($nivel==411 or $nivel==410) and $servicio==20){ 
                //General - Programa Semestral Contenido-resultado y recomendacion
                $estadosMatriculas = array(78,79,80);
                $notasTipos = array(53); //1er-2do semestre
               if($servicio==20){ //solo para servicios apoyo tecnico educativo vemoes si es trimestral o semestral
                    $esRegular = $this->busquedaInscripcionRegular($idInscripcion); // si es regular notas trimestrales
                    if($esRegular)
                        $notasTipos = array(6,7,8); //1er-2do-3er trimestre
                    $estadosMatriculas = array(78,79);
                }
                 $estadosFinales = $this->em->createQuery(
                    'SELECT at 
                    FROM SieAppWebBundle:EstadomatriculaTipo at
                    WHERE at.id IN (:ids)
                    ORDER BY at.id ASC'
                    )->setParameter('ids',$estadosMatriculas)
                    ->getResult();
                 
                    $tiposNotas = $this->em->createQuery(
                        'SELECT at 
                        FROM SieAppWebBundle:NotaTipo at
                        WHERE at.id IN (:ids)
                        ORDER BY at.id ASC'
                        )->setParameter('ids',$notasTipos)
                        ->getResult();

                 $idNotaTipo = 'semestralTrimestralSeguimiento';
                 $subNotasArray = array();
                
                 foreach ($tiposNotas as $tn) {
                     $notaCualitativas = $this->em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findOneBy(array('estudianteInscripcion'=>$idInscripcion, 'notaTipo'=>$tn->getId()));
                     $contenido = '';
                     $resultado = '';
                     $recomendacion = '';
                     $estado = '';
                     if($notaCualitativas){
                         $arrayCualitativas = json_decode($notaCualitativas->getNotaCualitativa(), true);
                         $arrayEstados = json_decode($notaCualitativas->getObs(), true);
                         $contenido = $arrayCualitativas["contenido"];
                         $resultado = $arrayCualitativas["resultado"];
                         $recomendacion = $notaCualitativas->getRecomendacion();   
                         $estado =$arrayEstados["estado"];   
                     }
                     $tiposNotasArray[] = array('id'=>$tn->getId(),'nota'=>$tn->getNotaTipo(),'titulo'=>$tn->getNotaTipo(),'contenido'=> $contenido, 'resultado'=> $resultado, 'recomendacion'=> $recomendacion, 'estado'=> $estado);
                 }
 
             }
            // dump($idNotaTipo);
            // dump($arrayCualitativas);die;
            return array(
                'cuantitativas'     =>array(),
                'cualitativas'      =>$arrayCualitativas,
                'operativo'         =>$operativo,
                'nivel'             =>$nivel,
                'observacion'       =>$observacion,
                'estadoMatricula'   =>$inscripcion->getEstadomatriculaTipo()->getId(),
                'gestionActual'     =>$this->session->get('currentyear'),
                'idInscripcion'     =>$idInscripcion,
                'gestion'           =>$gestion,
                'tipoNota'          =>$idNotaTipo,
                'tiposNotasArray'   =>$tiposNotasArray,
                'estadosPermitidos' =>$estadosPermitidos,
                'estadosFinales'    =>$estadosFinales,
                'valoraciones'      =>$valoraciones,
                'enota'             =>$enota
            );

        } catch (Exception $e) {
            return null;
        }
    }

    public function especial_auditiva($idInscripcion,$operativo){
        
        try {
            $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

            vuelve:
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

                    // Si no existe la asignatura, registramos la asignatura para el maestro
                    $newEstAsig = new EstudianteAsignatura();
                    $newEstAsig->setGestionTipo($this->em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                    $newEstAsig->setFechaRegistro(new \DateTime('now'));
                    $newEstAsig->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                    $newEstAsig->setInstitucioneducativaCursoOferta($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId()));
                    $this->em->persist($newEstAsig);
                    $this->em->flush();
                    $nuevaArea = true;

                    // Registro de materia para estudiantes estudiante_asignatura en el log
                    $arrayEstAsig = [];
                    $arrayEstAsig['id'] = $newEstAsig->getId();
                    $arrayEstAsig['gestionTipo'] = $newEstAsig->getGestionTipo()->getId();
                    $arrayEstAsig['fechaRegistro'] = $newEstAsig->getFechaRegistro()->format('d-m-Y');
                    $arrayEstAsig['estudianteInscripcion'] = $newEstAsig->getEstudianteInscripcion()->getId();
                    $arrayEstAsig['institucioneducativaCursoOferta'] = $newEstAsig->getInstitucioneducativaCursoOferta()->getId();
                    
                    $this->funciones->setLogTransaccion(
                        $newEstAsig->getId(),
                        'estudiante_asignatura',
                        'C',
                        '',
                        $arrayEstAsig,
                        '',
                        'ESPECIAL',
                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                    );
                }
            }

            // Volvemos atras si se adiciono alguna nueva materia o asignatura
            if($nuevaArea == true){
                goto vuelve;
            }

            $idNotaTipo = 0;
            $arrayCualitativas = array();
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

            $estadosFinales = $this->em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findById(array(10,78));
            $estadosPermitidos = array(4);
            return array(
                'cuantitativas'=>$asignaturas,
                'cualitativas'=>$arrayCualitativas,
                'operativo'=>$operativo,
                'nivel'=>$nivel,
                'estadoMatricula'=>$inscripcion->getEstadomatriculaTipo()->getId(),
                'gestionActual'=>$this->session->get('currentyear'),
                'idInscripcion'=>$idInscripcion,
                'gestion'=>$gestion,
                'tipoNota'=>$idNotaTipo,
                'estadosPermitidos'=>$estadosPermitidos,
                'estadosFinales'=>$estadosFinales,
                'enota'=>$enota
            );
        } catch (Exception $e) {
            return null;
        }

    }

    /*=====  End of REGISTRO DE CALIFICACIONES EDUCACION ESPECIAL  ======*/


    /*==========================================================================
    =             REGISTRO DE NOTAS ANTES DE REGISTRAR LAS MATERIAS            =
    ==========================================================================*/
    
    /**
     * [registroNotasParaMateria description]
     * @param  integer $idco [id curso oferta]
     * @return array       [datos de las notas cualitativas y cuantitativas que se deben llenar]
     */
    public function llenarNotasMateriaAntes($idInscripcion, $idsco){
        $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $curso = $inscripcion->getInstitucioneducativaCurso();
        $sie = $curso->getInstitucioneducativa()->getId();
        $gestion = $curso->getGestionTipo()->getId();
        $nivel = $curso->getNivelTipo()->getId();
        $grado = $curso->getGradoTipo()->getId();

        $operativo = $this->funciones->obtenerOperativo($sie, $gestion);

        // VERIFICAMOS SI SE CERRO EL OPERATIVO DE SEXTO DE SECUNDARIA
        // PARA PEDIR LAS CALIFICACIONES HASTA CUARTO BIMESTRE
        if($gestion >= 2018 and $operativo == 4 and $nivel == 13 and $grado == 6){
            $validacionSexto = $this->funciones->verificarGradoCerrado($sie, $gestion);
            if($validacionSexto){
                $operativo++;
            }
        }

        $cursosOferta = $this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('id'=>$idsco));
        $cont = 0;
        $cuantitativas = [];
        
        if($operativo > 1){
            if ($gestion < 2019 or ($gestion >= 2019 and $nivel != 11)) {
                foreach ($cursosOferta as $key => $co) {

                    $cuantitativas[$cont] = array(
                        'idco'=>$co->getId(),
                        'idAsignatura'=>$co->getAsignaturaTipo()->getId(),
                        'asignatura'=>$co->getAsignaturaTipo()->getAsignatura(),
                    );

                    for ($i=1; $i < $operativo; $i++) { 
                        $cuantitativas[$cont]['notas'][] = array(
                            'bimestre'=>$i.' Bim',
                            'idNotaTipo'=>$i,
                            'nota'=>'',
                            'notaCualitativa'=>''
                        );
                    }
                    $cont++;
                }   
            }
        }

        $cualitativas = [];
        // if($gestion < 2019 or ($gestion >= 2019 and $nivel == 11)){
        //     for ($i=1; $i < $operativo; $i++) {
        //         $cualitativas[] = array(
        //             'bimestre'=>$this->literal($i).' Bimestre',
        //             'idNotaTipo'=>$i,
        //             'nota'=>'',
        //             'notaCualitativa'=>''
        //         );
        //     }
        // }

        // if($nivel == 11 and $operativo >= 4){
        //     $cualitativas[] = array(
        //         'bimestre'=>'Valoración anual',
        //         'idNotaTipo'=>18,
        //         'nota'=>'',
        //         'notaCualitativa'=>''
        //     );
        // }


        $data = [
            'cuantitativas'=>$cuantitativas,
            'cualitativas'=>$cualitativas,
            'idInscripcion'=>$idInscripcion,
            'sie'=>$sie,
            'gestion'=>$gestion,
            'nivel'=>$nivel,
            'operativo'=>$operativo,
        ];

        return $data;
    }
        /**
     * @return array       [datos de estudaintes inscritos en regular]
     */
    public function busquedaInscripcionRegular($idInscripcion){
        $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $inscRegular = $this->em->createQueryBuilder()
        ->select('ei.id')
        ->from('SieAppWebBundle:EstudianteInscripcion','ei')
        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','WITH','ei.institucioneducativaCurso = iec.id')
        ->where('ei.estudiante = :idEstudiante')
        ->andWhere('iec.gestionTipo = :gestion')
        ->andWhere('iec.nivelTipo in (:niveles)')
        ->setParameter('idEstudiante',$inscripcion->getEstudiante()->getId())
        ->setParameter('gestion',$inscripcion->getInstitucioneducativaCurso()->getGestionTipo())
       ->setParameter('niveles',array(11,12,13))
        ->getQuery()
        ->getResult();
        if(count($inscRegular)>0)
            return true;        
        else
            return false;        
    }
    /*=====  End of  REGISTRO DE NOTAS ANTES DE REGISTRAR LAS MATERIAS  ======*/
    
}
