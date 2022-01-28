<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpModalidadPrueba
 */
class JdpModalidadPrueba
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpPruebaTipo
     */
    private $pruebaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpModalidadTipo
     */
    private $modalidadTipo;


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
     * Set pruebaTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpPruebaTipo $pruebaTipo
     * @return JdpModalidadPrueba
     */
    public function setPruebaTipo(\Sie\AppWebBundle\Entity\JdpPruebaTipo $pruebaTipo = null)
    {
        $this->pruebaTipo = $pruebaTipo;
    
        return $this;
    }

    /**
     * Get pruebaTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpPruebaTipo 
     */
    public function getPruebaTipo()
    {
        return $this->pruebaTipo;
    }

    /**
     * Set modalidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpModalidadTipo $modalidadTipo
     * @return JdpModalidadPrueba
     */
    public function setModalidadTipo(\Sie\AppWebBundle\Entity\JdpModalidadTipo $modalidadTipo = null)
    {
        $this->modalidadTipo = $modalidadTipo;
    
        return $this;
    }

    /**
     * Get modalidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpModalidadTipo 
     */
    public function getModalidadTipo()
    {
        return $this->modalidadTipo;
    }
}
