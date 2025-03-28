<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpDelegadoInscripcionJuegos
 */
class JdpDelegadoInscripcionJuegos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpFaseTipo
     */
    private $faseTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpComisionTipo
     */
    private $comisionTipo;


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
     * Set obs
     *
     * @param string $obs
     * @return JdpDelegadoInscripcionJuegos
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
     * Set faseTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpFaseTipo $faseTipo
     * @return JdpDelegadoInscripcionJuegos
     */
    public function setFaseTipo(\Sie\AppWebBundle\Entity\JdpFaseTipo $faseTipo = null)
    {
        $this->faseTipo = $faseTipo;
    
        return $this;
    }

    /**
     * Get faseTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpFaseTipo 
     */
    public function getFaseTipo()
    {
        return $this->faseTipo;
    }

    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return JdpDelegadoInscripcionJuegos
     */
    public function setPersona(\Sie\AppWebBundle\Entity\Persona $persona = null)
    {
        $this->persona = $persona;
    
        return $this;
    }

    /**
     * Get persona
     *
     * @return \Sie\AppWebBundle\Entity\Persona 
     */
    public function getPersona()
    {
        return $this->persona;
    }

    /**
     * Set comisionTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpComisionTipo $comisionTipo
     * @return JdpDelegadoInscripcionJuegos
     */
    public function setComisionTipo(\Sie\AppWebBundle\Entity\JdpComisionTipo $comisionTipo = null)
    {
        $this->comisionTipo = $comisionTipo;
    
        return $this;
    }

    /**
     * Get comisionTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpComisionTipo 
     */
    public function getComisionTipo()
    {
        return $this->comisionTipo;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\LugarTipo
     */
    private $lugarTipo;


    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return JdpDelegadoInscripcionJuegos
     */
    public function setGestionTipo(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipo = null)
    {
        $this->gestionTipo = $gestionTipo;
    
        return $this;
    }

    /**
     * Get gestionTipo
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipo()
    {
        return $this->gestionTipo;
    }

    /**
     * Set lugarTipo
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $lugarTipo
     * @return JdpDelegadoInscripcionJuegos
     */
    public function setLugarTipo(\Sie\AppWebBundle\Entity\LugarTipo $lugarTipo = null)
    {
        $this->lugarTipo = $lugarTipo;
    
        return $this;
    }

    /**
     * Get lugarTipo
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getLugarTipo()
    {
        return $this->lugarTipo;
    }
    /**
     * @var string
     */
    private $rutaImagen;


    /**
     * Set rutaImagen
     *
     * @param string $rutaImagen
     * @return JdpDelegadoInscripcionJuegos
     */
    public function setRutaImagen($rutaImagen)
    {
        $this->rutaImagen = $rutaImagen;
    
        return $this;
    }

    /**
     * Get rutaImagen
     *
     * @return string 
     */
    public function getRutaImagen()
    {
        return $this->rutaImagen;
    }
}
