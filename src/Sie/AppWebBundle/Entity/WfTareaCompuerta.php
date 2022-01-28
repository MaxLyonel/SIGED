<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WfTareaCompuerta
 */
class WfTareaCompuerta
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $condicion;

    /**
     * @var integer
     */
    private $condicionTareaSiguiente;

    /**
     * @var \Sie\AppWebBundle\Entity\WfCompuerta
     */
    private $wfCompuerta;

    /**
     * @var \Sie\AppWebBundle\Entity\FlujoProceso
     */
    private $flujoProceso;


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
     * Set condicion
     *
     * @param string $condicion
     * @return WfTareaCompuerta
     */
    public function setCondicion($condicion)
    {
        $this->condicion = $condicion;
    
        return $this;
    }

    /**
     * Get condicion
     *
     * @return string 
     */
    public function getCondicion()
    {
        return $this->condicion;
    }

    /**
     * Set condicionTareaSiguiente
     *
     * @param integer $condicionTareaSiguiente
     * @return WfTareaCompuerta
     */
    public function setCondicionTareaSiguiente($condicionTareaSiguiente)
    {
        $this->condicionTareaSiguiente = $condicionTareaSiguiente;
    
        return $this;
    }

    /**
     * Get condicionTareaSiguiente
     *
     * @return integer 
     */
    public function getCondicionTareaSiguiente()
    {
        return $this->condicionTareaSiguiente;
    }

    /**
     * Set wfCompuerta
     *
     * @param \Sie\AppWebBundle\Entity\WfCompuerta $wfCompuerta
     * @return WfTareaCompuerta
     */
    public function setWfCompuerta(\Sie\AppWebBundle\Entity\WfCompuerta $wfCompuerta = null)
    {
        $this->wfCompuerta = $wfCompuerta;
    
        return $this;
    }

    /**
     * Get wfCompuerta
     *
     * @return \Sie\AppWebBundle\Entity\WfCompuerta 
     */
    public function getWfCompuerta()
    {
        return $this->wfCompuerta;
    }

    /**
     * Set flujoProceso
     *
     * @param \Sie\AppWebBundle\Entity\FlujoProceso $flujoProceso
     * @return WfTareaCompuerta
     */
    public function setFlujoProceso(\Sie\AppWebBundle\Entity\FlujoProceso $flujoProceso = null)
    {
        $this->flujoProceso = $flujoProceso;
    
        return $this;
    }

    /**
     * Get flujoProceso
     *
     * @return \Sie\AppWebBundle\Entity\FlujoProceso 
     */
    public function getFlujoProceso()
    {
        return $this->flujoProceso;
    }
}
