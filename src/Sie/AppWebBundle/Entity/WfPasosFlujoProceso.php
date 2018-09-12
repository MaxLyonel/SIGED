<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WfPasosFlujoProceso
 */
class WfPasosFlujoProceso
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var integer
     */
    private $posicion;

    /**
     * @var \Sie\AppWebBundle\Entity\FlujoProceso
     */
    private $flujoProceso;

    /**
     * @var \Sie\AppWebBundle\Entity\WfPasosTipo
     */
    private $wfPasosTipo;


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
     * Set nombre
     *
     * @param string $nombre
     * @return WfPasosFlujoProceso
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set posicion
     *
     * @param integer $posicion
     * @return WfPasosFlujoProceso
     */
    public function setPosicion($posicion)
    {
        $this->posicion = $posicion;
    
        return $this;
    }

    /**
     * Get posicion
     *
     * @return integer 
     */
    public function getPosicion()
    {
        return $this->posicion;
    }

    /**
     * Set flujoProceso
     *
     * @param \Sie\AppWebBundle\Entity\FlujoProceso $flujoProceso
     * @return WfPasosFlujoProceso
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

    /**
     * Set wfPasosTipo
     *
     * @param \Sie\AppWebBundle\Entity\WfPasosTipo $wfPasosTipo
     * @return WfPasosFlujoProceso
     */
    public function setWfPasosTipo(\Sie\AppWebBundle\Entity\WfPasosTipo $wfPasosTipo = null)
    {
        $this->wfPasosTipo = $wfPasosTipo;
    
        return $this;
    }

    /**
     * Get wfPasosTipo
     *
     * @return \Sie\AppWebBundle\Entity\WfPasosTipo 
     */
    public function getWfPasosTipo()
    {
        return $this->wfPasosTipo;
    }
}
