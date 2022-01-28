<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LogRegularizacionMaestro
 */
class LogRegularizacionMaestro
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fecha;

    /**
     * @var integer
     */
    private $institucioneducativaId;

    /**
     * @var integer
     */
    private $usuarioId;

    /**
     * @var string
     */
    private $browser;

    /**
     * @var boolean
     */
    private $esModificado;


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
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return LogRegularizacionMaestro
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set institucioneducativaId
     *
     * @param integer $institucioneducativaId
     * @return LogRegularizacionMaestro
     */
    public function setInstitucioneducativaId($institucioneducativaId)
    {
        $this->institucioneducativaId = $institucioneducativaId;
    
        return $this;
    }

    /**
     * Get institucioneducativaId
     *
     * @return integer 
     */
    public function getInstitucioneducativaId()
    {
        return $this->institucioneducativaId;
    }

    /**
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return LogRegularizacionMaestro
     */
    public function setUsuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    
        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return integer 
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
    }

    /**
     * Set browser
     *
     * @param string $browser
     * @return LogRegularizacionMaestro
     */
    public function setBrowser($browser)
    {
        $this->browser = $browser;
    
        return $this;
    }

    /**
     * Get browser
     *
     * @return string 
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * Set esModificado
     *
     * @param boolean $esModificado
     * @return LogRegularizacionMaestro
     */
    public function setEsModificado($esModificado)
    {
        $this->esModificado = $esModificado;
    
        return $this;
    }

    /**
     * Get esModificado
     *
     * @return boolean 
     */
    public function getEsModificado()
    {
        return $this->esModificado;
    }
}
