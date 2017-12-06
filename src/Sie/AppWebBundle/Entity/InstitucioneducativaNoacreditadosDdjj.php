<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaNoacreditadosDdjj
 */
class InstitucioneducativaNoacreditadosDdjj
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var \DateTime
     */
    private $fecha;

    /**
     * @var boolean
     */
    private $esabierta;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;


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
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return InstitucioneducativaNoacreditadosDdjj
     */
    public function setGestionTipoId($gestionTipoId)
    {
        $this->gestionTipoId = $gestionTipoId;
    
        return $this;
    }

    /**
     * Get gestionTipoId
     *
     * @return integer 
     */
    public function getGestionTipoId()
    {
        return $this->gestionTipoId;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return InstitucioneducativaNoacreditadosDdjj
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
     * Set esabierta
     *
     * @param boolean $esabierta
     * @return InstitucioneducativaNoacreditadosDdjj
     */
    public function setEsabierta($esabierta)
    {
        $this->esabierta = $esabierta;
    
        return $this;
    }

    /**
     * Get esabierta
     *
     * @return boolean 
     */
    public function getEsabierta()
    {
        return $this->esabierta;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaNoacreditadosDdjj
     */
    public function setInstitucioneducativa(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa = null)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }
}
