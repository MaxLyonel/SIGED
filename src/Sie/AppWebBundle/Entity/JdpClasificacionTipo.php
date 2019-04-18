<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpClasificacionTipo
 */
class JdpClasificacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $clasificacion;

    /**
     * @var string
     */
    private $obs;


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
     * Set clasificacion
     *
     * @param string $clasificacion
     * @return JdpClasificacionTipo
     */
    public function setClasificacion($clasificacion)
    {
        $this->clasificacion = $clasificacion;
    
        return $this;
    }

    /**
     * Get clasificacion
     *
     * @return string 
     */
    public function getClasificacion()
    {
        return $this->clasificacion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return JdpClasificacionTipo
     */
    public function setObs($obs)
    {
        $this->obs = $obs;
    
        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs()
    {
        return $this->obs;
    }
}
