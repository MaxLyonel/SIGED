<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PermanentePoblacionTipo
 */
class PermanentePoblacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $poblacion;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\PermanenteOrganizacionTipo
     */
    private $organizacionTipo;


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
     * Set poblacion
     *
     * @param string $poblacion
     * @return PermanentePoblacionTipo
     */
    public function setPoblacion($poblacion)
    {
        $this->poblacion = $poblacion;
    
        return $this;
    }

    /**
     * Get poblacion
     *
     * @return string 
     */
    public function getPoblacion()
    {
        return $this->poblacion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return PermanentePoblacionTipo
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

    /**
     * Set organizacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\PermanenteOrganizacionTipo $organizacionTipo
     * @return PermanentePoblacionTipo
     */
    public function setOrganizacionTipo(\Sie\AppWebBundle\Entity\PermanenteOrganizacionTipo $organizacionTipo = null)
    {
        $this->organizacionTipo = $organizacionTipo;
    
        return $this;
    }

    /**
     * Get organizacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\PermanenteOrganizacionTipo 
     */
    public function getOrganizacionTipo()
    {
        return $this->organizacionTipo;
    }
}
