<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeVive
 */
class RudeVive
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $rudeId;

    /**
     * @var integer
     */
    private $viveCon;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var string
     */
    private $viveOtro;


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
     * Set rudeId
     *
     * @param integer $rudeId
     * @return RudeVive
     */
    public function setRudeId($rudeId)
    {
        $this->rudeId = $rudeId;
    
        return $this;
    }

    /**
     * Get rudeId
     *
     * @return integer 
     */
    public function getRudeId()
    {
        return $this->rudeId;
    }

    /**
     * Set viveCon
     *
     * @param integer $viveCon
     * @return RudeVive
     */
    public function setViveCon($viveCon)
    {
        $this->viveCon = $viveCon;
    
        return $this;
    }

    /**
     * Get viveCon
     *
     * @return integer 
     */
    public function getViveCon()
    {
        return $this->viveCon;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return RudeVive
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return RudeVive
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
     * Set viveOtro
     *
     * @param string $viveOtro
     * @return RudeVive
     */
    public function setViveOtro($viveOtro)
    {
        $this->viveOtro = $viveOtro;
    
        return $this;
    }

    /**
     * Get viveOtro
     *
     * @return string 
     */
    public function getViveOtro()
    {
        return $this->viveOtro;
    }
}
