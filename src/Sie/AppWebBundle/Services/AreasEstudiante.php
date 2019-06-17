<?php

namespace Sie\AppWebBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\LogTransaccion;
use JMS\Serializer\SerializerBuilder;
use Sie\AppWebBundle\Services\Funciones;
use Sie\AppWebBundle\Services\Notas;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;

class AreasEstudiante {

	protected $em;
	protected $router;
    protected $session;
    protected $funciones;
    protected $notas;

	public function __construct(EntityManager $entityManager, Router $router) {
		$this->em = $entityManager;
		$this->router = $router;
        $this->session = new Session();
	}

    public function setServices(Funciones $funciones,Notas $notas){
        $this->funciones = $funciones;
        $this->notas = $notas;
    }

    public function areasEstudiante($idInscripcion){
        $inscripcion = $this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $areasCurso = $this->em->createQueryBuilder()
                        ->select('at.id as idAsignatura, at.asignatura, ieco.id as idCursoOferta')
                        ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','with','ieco.insitucioneducativaCurso = iec.id')
                        ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','with','ieco.asignaturaTipo = at.id')
                        ->where('ei.id = :idInscripcion')
                        ->setParameter('idInscripcion', $idInscripcion)
                        ->orderBy('at.id','ASC')
                        ->getQuery()
                        ->getResult();

        $areasCursoId = [];
        foreach ($areasCurso as $ac) {
            $areasCursoId[] = $ac['idAsignatura'];
        }

        $areasEstudiante = $this->em->createQueryBuilder()
                        ->select('at.id as idAsignatura, at.asignatura, ieco.id as idCursoOferta, ea.id as idEstudianteAsignatura')
                        ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                        ->innerJoin('SieAppWebBundle:EstudianteAsignatura','ea','with','ea.estudianteInscripcion = ei.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','with','ea.institucioneducativaCursoOferta = ieco.id')
                        ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','with','ieco.asignaturaTipo = at.id')
                        ->where('ei.id = :idInscripcion')
                        ->setParameter('idInscripcion', $idInscripcion)
                        ->orderBy('at.id','ASC')
                        ->getQuery()
                        ->getResult();

        $areasEstudianteId = [0];
        foreach ($areasEstudiante as $ae) {
            $areasEstudianteId[] = $ae['idAsignatura'];
        }

        $areasFaltantes = $this->em->createQueryBuilder()
                        ->select('at.id as idAsignatura, at.asignatura, ieco.id as idCursoOferta')
                        ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco','with','ieco.insitucioneducativaCurso = iec.id')
                        ->innerJoin('SieAppWebBundle:AsignaturaTipo','at','with','ieco.asignaturaTipo = at.id')
                        ->where('ei.id = :idInscripcion')
                        ->andWhere('at.id NOT IN (:areasEst)')
                        ->setParameter('idInscripcion', $idInscripcion)
                        ->setParameter('areasEst', $areasEstudianteId)
                        ->orderBy('at.id','ASC')
                        ->getQuery()
                        ->getResult();

        $areasFaltantesId = [];
        foreach ($areasFaltantes as $af) {
            $areasFaltantesId[] = $af['idAsignatura'];
        }

        $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
        $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();

        $operativo = $this->funciones->obtenerOperativo($sie,$gestion) - 1;

        return array(
            'areasCursoId' => $areasCursoId,
            'areasCurso' => $areasCurso,
            'areasEstudianteId' => $areasEstudianteId,
            'areasEstudiante' => $areasEstudiante,
            'areasFaltantesId' => $areasFaltantesId,
            'areasFaltantes' => $areasFaltantes,
            'operativo' => $operativo,
            'sie'=>$sie,
            'gestion'=>$gestion,
            'nivel'=>$nivel,
            'grado'=>$grado
        );


    }

    public function delete($idEstudianteAsignatura){
        try {

            $eliminar = $this->em->createQueryBuilder()
                            ->delete('')
                            ->from('SieAppWebBundle:EstudianteAsignatura','ea')
                            ->where('ea.id = :idEstudianteAsignatura')
                            ->setParameter('idEstudianteAsignatura', $idEstudianteAsignatura)
                            ->getQuery()
                            ->getResult();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function nuevo($idCursoOferta, $idInscripcion, $gestion){
        try {
            $this->em->getConnection()->beginTransaction();
            // SI NO TIENE EL AREA, ENTONCES LO AGREGAMOS
            $newEstudianteAsignatura = new EstudianteAsignatura();
            $newEstudianteAsignatura->setGestionTipo($this->em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
            $newEstudianteAsignatura->setEstudianteInscripcion($this->em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
            $newEstudianteAsignatura->setInstitucioneducativaCursoOferta($this->em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta));
            $newEstudianteAsignatura->setFechaRegistro(new \DateTime('now'));
            $this->em->persist($newEstudianteAsignatura);
            $this->em->flush();

            $this->em->getConnection()->commit();

            return $newEstudianteAsignatura;
        } catch (Exception $e) {
            return null;
        }
        
    }

}