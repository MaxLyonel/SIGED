<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ModalidadAtencionTipo
 */
class ModalidadAtencionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $modalidadAtencion;

    /**
     * @var string
     */
    private $obs;


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
     * Set modalidadAtencion
     *
     * @param string $modalidadAtencion
     * @return ModalidadAtencionTipo
     */
    public function setModalidadAtencion($modalidadAtencion)
    {
        $this->modalidadAtencion = $modalidadAtencion;
    
        return $this;
    }

    /**
     * Get modalidadAtencion
     *
     * @return string 
     */
    public function getModalidadAtencion()
    {
        return $this->modalidadAtencion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return ModalidadAtencionTipo
     */
    public function setObs($obs)
    {
        $this->obs = $obs;
    
        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs()
    {
        return $this->obs;
    }
}
