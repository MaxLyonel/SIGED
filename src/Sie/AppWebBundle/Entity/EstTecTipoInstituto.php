<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstTecTipoInstituto
 */
class EstTecTipoInstituto
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $tipoInstituto;


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
     * Set tipoInstituto
     *
     * @param string $tipoInstituto
     * @return EstTecTipoInstituto
     */
    public function setTipoInstituto($tipoInstituto)
    {
        $this->tipoInstituto = $tipoInstituto;
    
        return $this;
    }

    /**
     * Get tipoInstituto
     *
     * @return string 
     */
    public function getTipoInstituto()
    {
        return $this->tipoInstituto;
    }
}
