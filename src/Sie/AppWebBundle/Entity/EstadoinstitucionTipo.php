<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstadoinstitucionTipo
 */
class EstadoinstitucionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $estadoinstitucion;

    /**
     * @var string
     */
    private $obsCerrada;


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
     * Set estadoinstitucion
     *
     * @param string $estadoinstitucion
     * @return EstadoinstitucionTipo
     */
    public function setEstadoinstitucion($estadoinstitucion)
    {
        $this->estadoinstitucion = $estadoinstitucion;

        return $this;
    }

    /**
     * Get estadoinstitucion
     *
     * @return string 
     */
    public function getEstadoinstitucion()
    {
        return $this->estadoinstitucion;
    }

    /**
     * Set obsCerrada
     *
     * @param string $obsCerrada
     * @return EstadoinstitucionTipo
     */
    public function setObsCerrada($obsCerrada)
    {
        $this->obsCerrada = $obsCerrada;

        return $this;
    }

    /**
     * Get obsCerrada
     *
     * @return string 
     */
    public function getObsCerrada()
    {
        return $this->obsCerrada;
    }
}
