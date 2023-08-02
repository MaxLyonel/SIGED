<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SolucionComparacionPlanillaTipo
 */
class SolucionComparacionPlanillaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $solucionTipo;


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
     * Set solucionTipo
     *
     * @param string $solucionTipo
     * @return SolucionComparacionPlanillaTipo
     */
    public function setSolucionTipo($solucionTipo)
    {
        $this->solucionTipo = $solucionTipo;
    
        return $this;
    }

    /**
     * Get solucionTipo
     *
     * @return string 
     */
    public function getSolucionTipo()
    {
        return $this->solucionTipo;
    }
}
