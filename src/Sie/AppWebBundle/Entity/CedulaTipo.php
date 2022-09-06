<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CedulaTipo
 */
class CedulaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $cedulaTipo;


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
     * Set cedulaTipo
     *
     * @param string $cedulaTipo
     * @return CedulaTipo
     */
    public function setCedulaTipo($cedulaTipo)
    {
        $this->cedulaTipo = $cedulaTipo;
    
        return $this;
    }

    /**
     * Get cedulaTipo
     *
     * @return string 
     */
    public function getCedulaTipo()
    {
        return $this->cedulaTipo;
    }
}
