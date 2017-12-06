<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AtencionmedicaTipo
 */
class AtencionmedicaTipo
{
    /**
     * @var integer
     */
    private $idAtencionmedica;

    /**
     * @var string
     */
    private $descAtencionmedica;


    /**
     * Get idAtencionmedica
     *
     * @return integer 
     */
    public function getIdAtencionmedica()
    {
        return $this->idAtencionmedica;
    }

    /**
     * Set descAtencionmedica
     *
     * @param string $descAtencionmedica
     * @return AtencionmedicaTipo
     */
    public function setDescAtencionmedica($descAtencionmedica)
    {
        $this->descAtencionmedica = $descAtencionmedica;

        return $this;
    }

    /**
     * Get descAtencionmedica
     *
     * @return string 
     */
    public function getDescAtencionmedica()
    {
        return $this->descAtencionmedica;
    }
}
