<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PreinsJustificativoTipo
 */
class PreinsJustificativoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $justificativo;


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
     * Set justificativo
     *
     * @param string $justificativo
     * @return PreinsJustificativoTipo
     */
    public function setJustificativo($justificativo)
    {
        $this->justificativo = $justificativo;
    
        return $this;
    }

    /**
     * Get justificativo
     *
     * @return string 
     */
    public function getJustificativo()
    {
        return $this->justificativo;
    }
}
