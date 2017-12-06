<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaTipo
 */
class InstitucioneducativaTipo {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var string
     */
    private $obs;

    public function __toString() {
        return $this->descripcion;
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
     * Set descripcion
     *
     * @param string $descripcion
     * @return InstitucioneducativaTipo
     */
    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion() {
        return $this->descripcion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InstitucioneducativaTipo
     */
    public function setObs($obs) {
        $this->obs = $obs;

        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs() {
        return $this->obs;
    }

    /**
     * @var \Sie\AppWebBundle\Entity\OrgcurricularTipo
     */
    private $orgcurricularTipo;


    /**
     * Set orgcurricularTipo
     *
     * @param \Sie\AppWebBundle\Entity\OrgcurricularTipo $orgcurricularTipo
     * @return InstitucioneducativaTipo
     */
    public function setOrgcurricularTipo(\Sie\AppWebBundle\Entity\OrgcurricularTipo $orgcurricularTipo = null)
    {
        $this->orgcurricularTipo = $orgcurricularTipo;
    
        return $this;
    }

    /**
     * Get orgcurricularTipo
     *
     * @return \Sie\AppWebBundle\Entity\OrgcurricularTipo 
     */
    public function getOrgcurricularTipo()
    {
        return $this->orgcurricularTipo;
    }

    public function getAbreviacionTipo() {
        $dato = explode(' ',$this->getDescripcion());
        return $dato[2];
    }
}
