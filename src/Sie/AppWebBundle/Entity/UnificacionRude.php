<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnificacionRude
 */
class UnificacionRude
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $rudecorr;

    /**
     * @var string
     */
    private $rudeinco;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\EstadomatriculaTipo
     */
    private $estadomatriculaTipoInco;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $sieinco;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $siecorr;

    /**
     * @var \Sie\AppWebBundle\Entity\EstadomatriculaTipo
     */
    private $estadomatriculaTipoCorr;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipoInco;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipoCorr;


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
     * Set rudecorr
     *
     * @param string $rudecorr
     * @return UnificacionRude
     */
    public function setRudecorr($rudecorr)
    {
        $this->rudecorr = $rudecorr;
    
        return $this;
    }

    /**
     * Get rudecorr
     *
     * @return string 
     */
    public function getRudecorr()
    {
        return $this->rudecorr;
    }

    /**
     * Set rudeinco
     *
     * @param string $rudeinco
     * @return UnificacionRude
     */
    public function setRudeinco($rudeinco)
    {
        $this->rudeinco = $rudeinco;
    
        return $this;
    }

    /**
     * Get rudeinco
     *
     * @return string 
     */
    public function getRudeinco()
    {
        return $this->rudeinco;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return UnificacionRude
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set estadomatriculaTipoInco
     *
     * @param \Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaTipoInco
     * @return UnificacionRude
     */
    public function setEstadomatriculaTipoInco(\Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaTipoInco = null)
    {
        $this->estadomatriculaTipoInco = $estadomatriculaTipoInco;
    
        return $this;
    }

    /**
     * Get estadomatriculaTipoInco
     *
     * @return \Sie\AppWebBundle\Entity\EstadomatriculaTipo 
     */
    public function getEstadomatriculaTipoInco()
    {
        return $this->estadomatriculaTipoInco;
    }

    /**
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return UnificacionRude
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
     * Set sieinco
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $sieinco
     * @return UnificacionRude
     */
    public function setSieinco(\Sie\AppWebBundle\Entity\Institucioneducativa $sieinco = null)
    {
        $this->sieinco = $sieinco;
    
        return $this;
    }

    /**
     * Get sieinco
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getSieinco()
    {
        return $this->sieinco;
    }

    /**
     * Set siecorr
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $siecorr
     * @return UnificacionRude
     */
    public function setSiecorr(\Sie\AppWebBundle\Entity\Institucioneducativa $siecorr = null)
    {
        $this->siecorr = $siecorr;
    
        return $this;
    }

    /**
     * Get siecorr
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getSiecorr()
    {
        return $this->siecorr;
    }

    /**
     * Set estadomatriculaTipoCorr
     *
     * @param \Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaTipoCorr
     * @return UnificacionRude
     */
    public function setEstadomatriculaTipoCorr(\Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaTipoCorr = null)
    {
        $this->estadomatriculaTipoCorr = $estadomatriculaTipoCorr;
    
        return $this;
    }

    /**
     * Get estadomatriculaTipoCorr
     *
     * @return \Sie\AppWebBundle\Entity\EstadomatriculaTipo 
     */
    public function getEstadomatriculaTipoCorr()
    {
        return $this->estadomatriculaTipoCorr;
    }

    /**
     * Set gestionTipoInco
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipoInco
     * @return UnificacionRude
     */
    public function setGestionTipoInco(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipoInco = null)
    {
        $this->gestionTipoInco = $gestionTipoInco;
    
        return $this;
    }

    /**
     * Get gestionTipoInco
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipoInco()
    {
        return $this->gestionTipoInco;
    }

    /**
     * Set gestionTipoCorr
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipoCorr
     * @return UnificacionRude
     */
    public function setGestionTipoCorr(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipoCorr = null)
    {
        $this->gestionTipoCorr = $gestionTipoCorr;
    
        return $this;
    }

    /**
     * Get gestionTipoCorr
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipoCorr()
    {
        return $this->gestionTipoCorr;
    }
}
