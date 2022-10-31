<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ModalidadEstudioTipo
 */
class ModalidadEstudioTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $modalidadEstudio;

    /**
     * @var boolean
     */
    private $esVigente;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;


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
     * Set modalidadEstudio
     *
     * @param string $modalidadEstudio
     * @return ModalidadEstudioTipo
     */
    public function setModalidadEstudio($modalidadEstudio)
    {
        $this->modalidadEstudio = $modalidadEstudio;
    
        return $this;
    }

    /**
     * Get modalidadEstudio
     *
     * @return string 
     */
    public function getModalidadEstudio()
    {
        return $this->modalidadEstudio;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return ModalidadEstudioTipo
     */
    public function setEsVigente($esVigente)
    {
        $this->esVigente = $esVigente;
    
        return $this;
    }

    /**
     * Get esVigente
     *
     * @return boolean 
     */
    public function getEsVigente()
    {
        return $this->esVigente;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return ModalidadEstudioTipo
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
     * @return ModalidadEstudioTipo
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
}
