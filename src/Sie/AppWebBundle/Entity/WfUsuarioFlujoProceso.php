<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WfUsuarioFlujoProceso
 */
class WfUsuarioFlujoProceso
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var integer
     */
    private $lugarTipoId;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

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
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return WfUsuarioFlujoProceso
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
     * Set lugarTipoId
     *
     * @param integer $lugarTipoId
     * @return WfUsuarioFlujoProceso
     */
    public function setLugarTipoId($lugarTipoId)
    {
        $this->lugarTipoId = $lugarTipoId;
    
        return $this;
    }

    /**
     * Get lugarTipoId
     *
     * @return integer 
     */
    public function getLugarTipoId()
    {
        return $this->lugarTipoId;
    }

    /**
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return WfUsuarioFlujoProceso
     */
    public function setUsuario(\Sie\AppWebBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set flujoProceso
     *
     * @param \Sie\AppWebBundle\Entity\FlujoProceso $flujoProceso
     * @return WfUsuarioFlujoProceso
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
