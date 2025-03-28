<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * EstudianteInscripcionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EstudianteInscripcionRepository extends EntityRepository {

    public function getNotasStudent($idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie) {


        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
                ->select('ast.id, ast.asignatura, IDENTITY(en.notaTipo) as notaTipo, en.notaCuantitativa, en.notaCualitativa, nt.notaTipo as notaTipoLiteral')
                ->from('SieAppWebBundle:EstudianteInscripcion', 'ei')
                ->leftJoin('SieAppWebBundle:EstudianteAsignatura', 'ea', 'WITH', 'ei.id = ea.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco', 'WITH', 'ea.institucioneducativaCursoOferta = ieco.id')
                ->leftJoin('SieAppWebBundle:AsignaturaTipo', 'ast', 'WITH', 'ieco.asignaturaTipo = ast.id')
                ->leftJoin('SieAppWebBundle:EstudianteNota', 'en', 'WITH', 'ea.id = en.estudianteAsignatura')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ei.estudiante = e.id')
                ->leftJoin('SieAppWebBundle:NotaTipo', 'nt', 'WITH', 'en.notaTipo = nt.id')
                ->where('e.id = :id')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andwhere('iec.turnoTipo = :turno')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.institucioneducativa = :sie')
                ->setParameter('id', $idstudent)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('turno', $turno)
                ->setParameter('gestion', $gestion)
                ->setParameter('sie', $sie)
                ->orderBy('ast.id, en.notaTipo')


        ;
        return $qb->getQuery()->getResult();
    }

    public function getNotasStudentNew($inscripcionid, $idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie) {


        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
                ->select('ast.id, ast.asignatura, IDENTITY(en.notaTipo) as notaTipo, en.notaCuantitativa, en.notaCualitativa, nt.notaTipo as notaTipoLiteral')
                ->from('SieAppWebBundle:EstudianteInscripcion', 'ei')
                ->leftJoin('SieAppWebBundle:EstudianteAsignatura', 'ea', 'WITH', 'ei.id = ea.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco', 'WITH', 'ea.institucioneducativaCursoOferta = ieco.id')
                ->leftJoin('SieAppWebBundle:AsignaturaTipo', 'ast', 'WITH', 'ieco.asignaturaTipo = ast.id')
                ->leftJoin('SieAppWebBundle:EstudianteNota', 'en', 'WITH', 'ea.id = en.estudianteAsignatura')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ei.estudiante = e.id')
                ->leftJoin('SieAppWebBundle:NotaTipo', 'nt', 'WITH', 'en.notaTipo = nt.id')
                ->where('e.id = :id')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andwhere('iec.turnoTipo = :turno')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.institucioneducativa = :sie')
                ->andwhere('ei.id = :inscripcionid')
                ->setParameter('id', $idstudent)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('turno', $turno)
                ->setParameter('gestion', $gestion)
                ->setParameter('sie', $sie)
                ->setParameter('inscripcionid', $inscripcionid)
                ->orderBy('ast.id, en.notaTipo')


        ;
        return $qb->getQuery()->getResult();
    }

    public function getNotasStudentCualitativa($idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie) {


        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
                ->select('IDENTITY(enc.notaTipo) as notaTipo,enc.notaCualitativa, nt.notaTipo')
                ->from('SieAppWebBundle:EstudianteInscripcion', 'ei')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftJoin('SieAppWebBundle:EstudianteNotaCualitativa', 'enc', 'WITH', 'ei.id = enc.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ei.estudiante = e.id')
                ->leftJoin('SieAppWebBundle:NotaTipo', 'nt', 'WITH', 'enc.notaTipo = nt.id')
                ->where('e.id = :id')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andwhere('iec.turnoTipo = :turno')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.institucioneducativa = :sie')
                ->setParameter('id', $idstudent)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('turno', $turno)
                ->setParameter('gestion', $gestion)
                ->setParameter('sie', $sie)
                ->orderBy('enc.notaTipo')


        ;
        return $qb->getQuery()->getResult();
    }

    public function getNotasStudentCualitativaNew($inscripcionid, $idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie) {


        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
                ->select('IDENTITY(enc.notaTipo) as notaTipo,enc.notaCualitativa, nt.notaTipo')
                ->from('SieAppWebBundle:EstudianteInscripcion', 'ei')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftJoin('SieAppWebBundle:EstudianteNotaCualitativa', 'enc', 'WITH', 'ei.id = enc.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ei.estudiante = e.id')
                ->leftJoin('SieAppWebBundle:NotaTipo', 'nt', 'WITH', 'enc.notaTipo = nt.id')
                ->where('e.id = :id')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andwhere('iec.turnoTipo = :turno')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.institucioneducativa = :sie')
                ->andwhere('ei.id = :inscripcionid')
                ->setParameter('id', $idstudent)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('turno', $turno)
                ->setParameter('gestion', $gestion)
                ->setParameter('sie', $sie)
                ->setParameter('inscripcionid', $inscripcionid)
                ->orderBy('enc.notaTipo')


        ;
        return $qb->getQuery()->getResult();
    }

    public function getNotasStudentCualitativaIni($idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie) {


        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
                ->select('IDENTITY(enc.notaTipo) as notaTipo,enc.notaCualitativa')
                ->from('SieAppWebBundle:EstudianteInscripcion', 'ei')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftJoin('SieAppWebBundle:EstudianteNotaCualitativa', 'enc', 'WITH', 'ei.id = enc.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ei.estudiante = e.id')
                ->where('e.id = :id')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andwhere('iec.turnoTipo = :turno')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.institucioneducativa = :sie')
                ->andwhere('enc.notaTipo = :notaTipo')
                ->setParameter('id', $idstudent)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('turno', $turno)
                ->setParameter('gestion', $gestion)
                ->setParameter('sie', $sie)
                ->setParameter('notaTipo', '18')
                ->orderBy('enc.notaTipo')


        ;
        return $qb->getQuery()->getResult();
    }

    public function getNotasStudentCualitativaIniNew($inscripcionid, $idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie) {


        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
                ->select('IDENTITY(enc.notaTipo) as notaTipo,enc.notaCualitativa')
                ->from('SieAppWebBundle:EstudianteInscripcion', 'ei')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftJoin('SieAppWebBundle:EstudianteNotaCualitativa', 'enc', 'WITH', 'ei.id = enc.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ei.estudiante = e.id')
                ->where('e.id = :id')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andwhere('iec.turnoTipo = :turno')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.institucioneducativa = :sie')
                ->andwhere('enc.notaTipo = :notaTipo')
                ->andwhere('ei.id = :inscripcionid')
                ->setParameter('id', $idstudent)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('turno', $turno)
                ->setParameter('gestion', $gestion)
                ->setParameter('sie', $sie)
                ->setParameter('notaTipo', '18')
                ->setParameter('inscripcionid', $inscripcionid)
                ->orderBy('enc.notaTipo')


        ;
        return $qb->getQuery()->getResult();
    }
    /*
      select DISTINCT(ast.id),  ast.asignatura
      from estudiante_inscripcion ei
      left join institucioneducativa_curso iec on (ei.institucioneducativa_curso_id = iec.id)
      left join estudiante_asignatura ea on ( ei.id =ea.estudiante_inscripcion_id)
      left join asignatura_tipo ast on (ea.asignatura_tipo_id =  ast.id)
      where iec.id =133406 and ea.gestion_tipo_id=2015
      order by  ast.id
     */

    /**
     * get areas' course
     * @param type $id
     * @param type $gestion
     * @return type
     * array with the areas
     */
    public function getAsignaturasPerCourse($id, $gestion) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
                ->select('ast.id, ast.asignatura,')
                ->from('SieAppWebBundle:EstudianteInscripcion', 'ei')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftJoin('SieAppWebBundle:EstudianteAsignatura', 'ea', 'WITH', 'ei.id = ea.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:AsignaturaTipo', 'ast', 'WITH', 'ea.asignaturaTipo = ast.id')
                ->where('iec.id = :id')
                ->andwhere('ea.gestionTipo = :gestion')
                ->setParameter('id', $id)
                ->setParameter('gestion', $gestion)
                ->distinct()
                ->orderBy('ast.id')
        ;
        return $qb->getQuery()->getResult();
    }

    /**
     * get the student's inscriptions
     * @param type $rude
     * array with the areas
     */
    public function getInscriptionAlternativaStudent($id) {

      $qb = $this->getEntityManager()->createQueryBuilder();
      $qb
              ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId','IDENTITY(iec.cicloTipo) as cicloId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa, (ei.id) as insId, e.id idStudent')
              ->from('SieAppWebBundle:Estudiante', 'e')
              ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
              ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
              ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
              ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
              ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
              ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
              ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
              ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
              ->where('e.id = :id')
              ->setParameter('id', $id)
              ->orderBy('iec.gestionTipo', 'DESC')
      ;
      return $qb->getQuery()->getResult();
    }
    /**
     * get the student's inscriptions
     * @param type $rude
     * array with the areas
     */
    public function getInscriptionEspecialStudent($id) {

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId','IDENTITY(iec.cicloTipo) as cicloId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa, (ei.id) as insId, e.id idStudent','m.modalidad','pt.id as programaId', 'pt.programa','st.id as servicioId', 'st.servicio','at.id as areaId', 'at.areaEspecial')
                ->from('SieAppWebBundle:Estudiante', 'e')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCursoEspecial', 'iece', 'WITH', 'iece.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftjoin('SieAppWebBundle:EspecialModalidadTipo', 'm', 'WITH', 'iece.especialModalidadTipo = m.id')
                ->leftjoin('SieAppWebBundle:EspecialProgramaTipo', 'pt', 'WITH', 'iece.especialProgramaTipo = pt.id')
                ->leftjoin('SieAppWebBundle:EspecialServicioTipo', 'st', 'WITH', 'iece.especialServicioTipo = st.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->leftJoin('SieAppWebBundle:EspecialAreaTipo', 'at', 'WITH', 'iece.especialAreaTipo = at.id')
                ->where('e.id = :id')
                ->setParameter('id', $id)
                ->orderBy('iec.gestionTipo', 'DESC')
        ;
        return $qb->getQuery()->getResult();
      }

    /**
     * get the student's inscriptions in this year
     * @param type $rude
     * array with the areas
     */
    public function getInscriptionStudentByYear($id, $year, $iecId) {
      $qb = $this->getEntityManager()->createQueryBuilder();
      $qb
              ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId','IDENTITY(iec.cicloTipo) as cicloId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa, (ei.id) as insId, e.id idStudent')
              ->from('SieAppWebBundle:Estudiante', 'e')
              ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
              ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
              ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
              ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
              ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
              ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
              ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
              ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
              ->where('e.id = :id')
              ->andWhere('iec.gestionTipo = :year')
              ->andWhere('iec.id = :iecId')
              ->setParameter('id', $id)
              ->setParameter('year', $year)
              ->setParameter('iecId', $iecId)
              ->orderBy('iec.gestionTipo', 'DESC')
      ;
      return $qb->getQuery()->getResult();
    }


}
