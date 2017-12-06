<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GrupoSanguineo
 */
class GrupoSanguineo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $grupoSanguineo;


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
     * Set grupoSanguineo
     *
     * @param string $grupoSanguineo
     * @return GrupoSanguineo
     */
    public function setGrupoSanguineo($grupoSanguineo)
    {
        $this->grupoSanguineo = $grupoSanguineo;

        return $this;
    }

    /**
     * Get grupoSanguineo
     *
     * @return string 
     */
    public function getGrupoSanguineo()
    {
        return $this->grupoSanguineo;
    }
}
