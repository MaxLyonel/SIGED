<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NivelTipo
 */
class NivelTipo {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nivel;

    /**
     * @var boolean
     */
    private $vigente;

    /**
     * @var \Sie\AppWebBundle\Entity\OrgcurricularTipo
     */
    private $codOrgCurr;

    public function __toString() {
        return $this->nivel;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set nivel
     *
     * @param string $nivel
     * @return NivelTipo
     */
    public function setNivel($nivel) {
        $this->nivel = $nivel;

        return $this;
    }

    /**
     * Get nivel
     *
     * @return string 
     */
    public function getNivel() {
        return $this->nivel;
    }

    /**
     * Set vigente
     *
     * @param boolean $vigente
     * @return NivelTipo
     */
    public function setVigente($vigente) {
        $this->vigente = $vigente;

        return $this;
    }

    /**
     * Get vigente
     *
     * @return boolean 
     */
    public function getVigente() {
        return $this->vigente;
    }

    /**
     * Set codOrgCurr
     *
     * @param \Sie\AppWebBundle\Entity\OrgcurricularTipo $codOrgCurr
     * @return NivelTipo
     */
    public function setCodOrgCurr(\Sie\AppWebBundle\Entity\OrgcurricularTipo $codOrgCurr = null) {
        $this->codOrgCurr = $codOrgCurr;

        return $this;
    }

    /**
     * Get codOrgCurr
     *
     * @return \Sie\AppWebBundle\Entity\OrgcurricularTipo 
     */
    public function getCodOrgCurr() {
        return $this->codOrgCurr;
    }

}
