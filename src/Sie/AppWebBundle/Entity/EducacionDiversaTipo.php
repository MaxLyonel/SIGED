<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EducacionDiversaTipo
 */
class EducacionDiversaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $educacionDiversa;


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
     * Set educacionDiversa
     *
     * @param string $educacionDiversa
     * @return EducacionDiversaTipo
     */
    public function setEducacionDiversa($educacionDiversa)
    {
        $this->educacionDiversa = $educacionDiversa;
    
        return $this;
    }

    /**
     * Get educacionDiversa
     *
     * @return string 
     */
    public function getEducacionDiversa()
    {
        return $this->educacionDiversa;
    }
}
