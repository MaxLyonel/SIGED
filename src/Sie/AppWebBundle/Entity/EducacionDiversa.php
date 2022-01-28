<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EducacionDiversa
 */
class EducacionDiversa
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
     * @return EducacionDiversa
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
