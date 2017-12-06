<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteEspecialIndirecta
 */
class EstudianteEspecialIndirecta {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $institucioneducativaId;

    /**
     * @var integer
     */
    private $institucioneducativaTipoId;

    /**
     * @var boolean
     */
    private $disIntelectualGenerlal;

    /**
     * @var boolean
     */
    private $disIntelectualDown;

    /**
     * @var boolean
     */
    private $disIntelectualAutismo;

    /**
     * @var boolean
     */
    private $disVisualTotal;

    /**
     * @var boolean
     */
    private $disVisualBaja;

    /**
     * @var boolean
     */
    private $disAuditiva;

    /**
     * @var boolean
     */
    private $disFisicomotora;

    /**
     * @var boolean
     */
    private $disMultiple;

    /**
     * @var boolean
     */
    private $disOtros;

    /**
     * @var boolean
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Estudiante
     */
    private $estudiante;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set institucioneducativaId
     *
     * @param integer $institucioneducativaId
     * @return EstudianteEspecialIndirecta
     */
    public function setInstitucioneducativaId($institucioneducativaId) {
        $this->institucioneducativaId = $institucioneducativaId;

        return $this;
    }

    /**
     * Get institucioneducativaId
     *
     * @return integer 
     */
    public function getInstitucioneducativaId() {
        return $this->institucioneducativaId;
    }

    /**
     * Set institucioneducativaTipoId
     *
     * @param integer $institucioneducativaTipoId
     * @return EstudianteEspecialIndirecta
     */
    public function setInstitucioneducativaTipoId($institucioneducativaTipoId) {
        $this->institucioneducativaTipoId = $institucioneducativaTipoId;

        return $this;
    }

    /**
     * Get institucioneducativaTipoId
     *
     * @return integer 
     */
    public function getInstitucioneducativaTipoId() {
        return $this->institucioneducativaTipoId;
    }

    /**
     * Set disIntelectualGenerlal
     *
     * @param boolean $disIntelectualGenerlal
     * @return EstudianteEspecialIndirecta
     */
    public function setDisIntelectualGenerlal($disIntelectualGenerlal) {
        $this->disIntelectualGenerlal = $disIntelectualGenerlal;

        return $this;
    }

    /**
     * Get disIntelectualGenerlal
     *
     * @return boolean 
     */
    public function getDisIntelectualGenerlal() {
        return $this->disIntelectualGenerlal;
    }

    /**
     * Set disIntelectualDown
     *
     * @param boolean $disIntelectualDown
     * @return EstudianteEspecialIndirecta
     */
    public function setDisIntelectualDown($disIntelectualDown) {
        $this->disIntelectualDown = $disIntelectualDown;

        return $this;
    }

    /**
     * Get disIntelectualDown
     *
     * @return boolean 
     */
    public function getDisIntelectualDown() {
        return $this->disIntelectualDown;
    }

    /**
     * Set disIntelectualAutismo
     *
     * @param boolean $disIntelectualAutismo
     * @return EstudianteEspecialIndirecta
     */
    public function setDisIntelectualAutismo($disIntelectualAutismo) {
        $this->disIntelectualAutismo = $disIntelectualAutismo;

        return $this;
    }

    /**
     * Get disIntelectualAutismo
     *
     * @return boolean 
     */
    public function getDisIntelectualAutismo() {
        return $this->disIntelectualAutismo;
    }

    /**
     * Set disVisualTotal
     *
     * @param boolean $disVisualTotal
     * @return EstudianteEspecialIndirecta
     */
    public function setDisVisualTotal($disVisualTotal) {
        $this->disVisualTotal = $disVisualTotal;

        return $this;
    }

    /**
     * Get disVisualTotal
     *
     * @return boolean 
     */
    public function getDisVisualTotal() {
        return $this->disVisualTotal;
    }

    /**
     * Set disVisualBaja
     *
     * @param boolean $disVisualBaja
     * @return EstudianteEspecialIndirecta
     */
    public function setDisVisualBaja($disVisualBaja) {
        $this->disVisualBaja = $disVisualBaja;

        return $this;
    }

    /**
     * Get disVisualBaja
     *
     * @return boolean 
     */
    public function getDisVisualBaja() {
        return $this->disVisualBaja;
    }

    /**
     * Set disAuditiva
     *
     * @param boolean $disAuditiva
     * @return EstudianteEspecialIndirecta
     */
    public function setDisAuditiva($disAuditiva) {
        $this->disAuditiva = $disAuditiva;

        return $this;
    }

    /**
     * Get disAuditiva
     *
     * @return boolean 
     */
    public function getDisAuditiva() {
        return $this->disAuditiva;
    }

    /**
     * Set disFisicomotora
     *
     * @param boolean $disFisicomotora
     * @return EstudianteEspecialIndirecta
     */
    public function setDisFisicomotora($disFisicomotora) {
        $this->disFisicomotora = $disFisicomotora;

        return $this;
    }

    /**
     * Get disFisicomotora
     *
     * @return boolean 
     */
    public function getDisFisicomotora() {
        return $this->disFisicomotora;
    }

    /**
     * Set disMultiple
     *
     * @param boolean $disMultiple
     * @return EstudianteEspecialIndirecta
     */
    public function setDisMultiple($disMultiple) {
        $this->disMultiple = $disMultiple;

        return $this;
    }

    /**
     * Get disMultiple
     *
     * @return boolean 
     */
    public function getDisMultiple() {
        return $this->disMultiple;
    }

    /**
     * Set disOtros
     *
     * @param boolean $disOtros
     * @return EstudianteEspecialIndirecta
     */
    public function setDisOtros($disOtros) {
        $this->disOtros = $disOtros;

        return $this;
    }

    /**
     * Get disOtros
     *
     * @return boolean 
     */
    public function getDisOtros() {
        return $this->disOtros;
    }

    /**
     * Set obs
     *
     * @param boolean $obs
     * @return EstudianteEspecialIndirecta
     */
    public function setObs($obs) {
        $this->obs = $obs;

        return $this;
    }

    /**
     * Get obs
     *
     * @return boolean 
     */
    public function getObs() {
        return $this->obs;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return EstudianteEspecialIndirecta
     */
    public function setGestionTipo(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipo = null) {
        $this->gestionTipo = $gestionTipo;

        return $this;
    }

    /**
     * Get gestionTipo
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipo() {
        return $this->gestionTipo;
    }

    /**
     * Set estudiante
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $estudiante
     * @return EstudianteEspecialIndirecta
     */
    public function setEstudiante(\Sie\AppWebBundle\Entity\Estudiante $estudiante = null) {
        $this->estudiante = $estudiante;

        return $this;
    }

    /**
     * Get estudiante
     *
     * @return \Sie\AppWebBundle\Entity\Estudiante 
     */
    public function getEstudiante() {
        return $this->estudiante;
    }

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var integer
     */
    private $usuarioId;

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstudianteEspecialIndirecta
     */
    public function setFechaRegistro($fechaRegistro) {
        $this->fechaRegistro = $fechaRegistro;

        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro() {
        return $this->fechaRegistro;
    }

    /**
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return EstudianteEspecialIndirecta
     */
    public function setUsuarioId($usuarioId) {
        $this->usuarioId = $usuarioId;

        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return integer 
     */
    public function getUsuarioId() {
        return $this->usuarioId;
    }

}
