<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ObjetoTipo
 */
class ObjetoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $objeto;

    /**
     * @var boolean
     */
    private $esactivo;


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
     * Set objeto
     *
     * @param string $objeto
     * @return ObjetoTipo
     */
    public function setObjeto($objeto)
    {
        $this->objeto = $objeto;

        return $this;
    }

    /**
     * Get objeto
     *
     * @return string 
     */
    public function getObjeto()
    {
        return $this->objeto;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return ObjetoTipo
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;

        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }
    /**
     * @var string
     */
    private $icono;

    /**
     * @var integer
     */
    private $orden;


    /**
     * Set icono
     *
     * @param string $icono
     * @return ObjetoTipo
     */
    public function setIcono($icono)
    {
        $this->icono = $icono;
    
        return $this;
    }

    /**
     * Get icono
     *
     * @return string 
     */
    public function getIcono()
    {
        return $this->icono;
    }

    /**
     * Set orden
     *
     * @param integer $orden
     * @return ObjetoTipo
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;
    
        return $this;
    }

    /**
     * Get orden
     *
     * @return integer 
     */
    public function getOrden()
    {
        return $this->orden;
    }
    
    public function __toString() {
        return $this->objeto;
    }
}
