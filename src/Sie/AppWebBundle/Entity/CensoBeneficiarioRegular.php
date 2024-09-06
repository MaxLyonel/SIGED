<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CensoBeneficiarioRegular
 */
class CensoBeneficiarioRegular
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $notaCuantitava;

    /**
     * @var integer
     */
    private $usuarioId;

    /**
     * @var \DateTime
     */
    private $fechaCreacion;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\CensoBeneficiario
     */
    private $censoBeneficiario;

    /**
     * @var \Sie\AppWebBundle\Entity\AsignaturaTipo
     */
    private $asignaturaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\NotaTipo
     */
    private $notaTipo;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set notaCuantitava
     *
     * @param integer $notaCuantitava
     * @return CensoBeneficiarioRegular
     */
    public function setNotaCuantitava($notaCuantitava)
    {
        $this->notaCuantitava = $notaCuantitava;
    
        return $this;
    }

    /**
     * Get notaCuantitava
     *
     * @return integer 
     */
    public function getNotaCuantitava()
    {
        return $this->notaCuantitava;
    }

    /**
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return CensoBeneficiarioRegular
     */
    public function setUsuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    
        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return integer 
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     * @return CensoBeneficiarioRegular
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;
    
        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime 
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return CensoBeneficiarioRegular
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime 
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set censoBeneficiario
     *
     * @param \Sie\AppWebBundle\Entity\CensoBeneficiario $censoBeneficiario
     * @return CensoBeneficiarioRegular
     */
    public function setCensoBeneficiario(\Sie\AppWebBundle\Entity\CensoBeneficiario $censoBeneficiario = null)
    {
        $this->censoBeneficiario = $censoBeneficiario;
    
        return $this;
    }

    /**
     * Get censoBeneficiario
     *
     * @return \Sie\AppWebBundle\Entity\CensoBeneficiario 
     */
    public function getCensoBeneficiario()
    {
        return $this->censoBeneficiario;
    }

    /**
     * Set asignaturaTipo
     *
     * @param \Sie\AppWebBundle\Entity\AsignaturaTipo $asignaturaTipo
     * @return CensoBeneficiarioRegular
     */
    public function setAsignaturaTipo(\Sie\AppWebBundle\Entity\AsignaturaTipo $asignaturaTipo = null)
    {
        $this->asignaturaTipo = $asignaturaTipo;
    
        return $this;
    }

    /**
     * Get asignaturaTipo
     *
     * @return \Sie\AppWebBundle\Entity\AsignaturaTipo 
     */
    public function getAsignaturaTipo()
    {
        return $this->asignaturaTipo;
    }

    /**
     * Set notaTipo
     *
     * @param \Sie\AppWebBundle\Entity\NotaTipo $notaTipo
     * @return CensoBeneficiarioRegular
     */
    public function setNotaTipo(\Sie\AppWebBundle\Entity\NotaTipo $notaTipo = null)
    {
        $this->notaTipo = $notaTipo;
    
        return $this;
    }

    /**
     * Get notaTipo
     *
     * @return \Sie\AppWebBundle\Entity\NotaTipo 
     */
    public function getNotaTipo()
    {
        return $this->notaTipo;
    }
}
