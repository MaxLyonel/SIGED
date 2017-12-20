<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecRegimenEstudioTipo
 */
class TtecRegimenEstudioTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $regimenEstudio;

    /**
     * @var string
     */
    private $descripcion;


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
     * Set regimenEstudio
     *
     * @param string $regimenEstudio
     * @return TtecRegimenEstudioTipo
     */
    public function setRegimenEstudio($regimenEstudio)
    {
        $this->regimenEstudio = $regimenEstudio;
    
        return $this;
    }

    /**
     * Get regimenEstudio
     *
     * @return string 
     */
    public function getRegimenEstudio()
    {
        return $this->regimenEstudio;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return TtecRegimenEstudioTipo
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }
}
