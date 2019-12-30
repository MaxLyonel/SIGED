<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OperativoGestionEspecialidadTipo
 */
class OperativoGestionEspecialidadTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $operativo;


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
     * Set operativo
     *
     * @param string $operativo
     * @return OperativoGestionEspecialidadTipo
     */
    public function setOperativo($operativo)
    {
        $this->operativo = $operativo;
    
        return $this;
    }

    /**
     * Get operativo
     *
     * @return string 
     */
    public function getOperativo()
    {
        return $this->operativo;
    }
}
