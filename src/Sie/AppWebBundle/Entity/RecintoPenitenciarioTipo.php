<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RecintoPenitenciarioTipo
 */
class RecintoPenitenciarioTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $recintoPenitenciario;

    /**
     * @var \Sie\AppWebBundle\Entity\LugarReclusionTipo
     */
    private $lugarReclusionTipo;


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
     * Set recintoPenitenciario
     *
     * @param string $recintoPenitenciario
     * @return RecintoPenitenciarioTipo
     */
    public function setRecintoPenitenciario($recintoPenitenciario)
    {
        $this->recintoPenitenciario = $recintoPenitenciario;
    
        return $this;
    }

    /**
     * Get recintoPenitenciario
     *
     * @return string 
     */
    public function getRecintoPenitenciario()
    {
        return $this->recintoPenitenciario;
    }

    /**
     * Set lugarReclusionTipo
     *
     * @param \Sie\AppWebBundle\Entity\LugarReclusionTipo $lugarReclusionTipo
     * @return RecintoPenitenciarioTipo
     */
    public function setLugarReclusionTipo(\Sie\AppWebBundle\Entity\LugarReclusionTipo $lugarReclusionTipo = null)
    {
        $this->lugarReclusionTipo = $lugarReclusionTipo;
    
        return $this;
    }

    /**
     * Get lugarReclusionTipo
     *
     * @return \Sie\AppWebBundle\Entity\LugarReclusionTipo 
     */
    public function getLugarReclusionTipo()
    {
        return $this->lugarReclusionTipo;
    }
}
