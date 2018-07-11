<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimModalidadPruebaTipo
 */
class OlimModalidadPruebaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $prueba;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimRegistroOlimpiada
     */
    private $olimRegistroOlimpiada;


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
     * Set prueba
     *
     * @param string $prueba
     * @return OlimModalidadPruebaTipo
     */
    public function setPrueba($prueba)
    {
        $this->prueba = $prueba;
    
        return $this;
    }

    /**
     * Get prueba
     *
     * @return string 
     */
    public function getPrueba()
    {
        return $this->prueba;
    }

    /**
     * Set olimRegistroOlimpiada
     *
     * @param \Sie\AppWebBundle\Entity\OlimRegistroOlimpiada $olimRegistroOlimpiada
     * @return OlimModalidadPruebaTipo
     */
    public function setOlimRegistroOlimpiada(\Sie\AppWebBundle\Entity\OlimRegistroOlimpiada $olimRegistroOlimpiada = null)
    {
        $this->olimRegistroOlimpiada = $olimRegistroOlimpiada;
    
        return $this;
    }

    /**
     * Get olimRegistroOlimpiada
     *
     * @return \Sie\AppWebBundle\Entity\OlimRegistroOlimpiada 
     */
    public function getOlimRegistroOlimpiada()
    {
        return $this->olimRegistroOlimpiada;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\OlimModalidadTipo
     */
    private $olimModalidadTipo;


    /**
     * Set olimModalidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimModalidadTipo $olimModalidadTipo
     * @return OlimModalidadPruebaTipo
     */
    public function setOlimModalidadTipo(\Sie\AppWebBundle\Entity\OlimModalidadTipo $olimModalidadTipo = null)
    {
        $this->olimModalidadTipo = $olimModalidadTipo;
    
        return $this;
    }

    /**
     * Get olimModalidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimModalidadTipo 
     */
    public function getOlimModalidadTipo()
    {
        return $this->olimModalidadTipo;
    }
}
