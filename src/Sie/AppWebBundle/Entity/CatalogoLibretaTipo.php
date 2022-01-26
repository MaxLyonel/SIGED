<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CatalogoLibretaTipo
 */
class CatalogoLibretaTipo
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
     * @var integer
     */
    private $nivelTipoId;

    /**
     * @var integer
     */
    private $gradoTipoId;

    /**
     * @var string
     */
    private $notaAbrev;

    /**
     * @var integer
     */
    private $notaTipoId;

    /**
     * @var string
     */
    private $notaTipo;

    /**
     * @var integer
     */
    private $orden;

    /**
     * @var string
     */
    private $notaBoletin;


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
     * @return CatalogoLibretaTipo
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
     * Set nivelTipoId
     *
     * @param integer $nivelTipoId
     * @return CatalogoLibretaTipo
     */
    public function setNivelTipoId($nivelTipoId)
    {
        $this->nivelTipoId = $nivelTipoId;
    
        return $this;
    }

    /**
     * Get nivelTipoId
     *
     * @return integer 
     */
    public function getNivelTipoId()
    {
        return $this->nivelTipoId;
    }

    /**
     * Set gradoTipoId
     *
     * @param integer $gradoTipoId
     * @return CatalogoLibretaTipo
     */
    public function setGradoTipoId($gradoTipoId)
    {
        $this->gradoTipoId = $gradoTipoId;
    
        return $this;
    }

    /**
     * Get gradoTipoId
     *
     * @return integer 
     */
    public function getGradoTipoId()
    {
        return $this->gradoTipoId;
    }

    /**
     * Set notaAbrev
     *
     * @param string $notaAbrev
     * @return CatalogoLibretaTipo
     */
    public function setNotaAbrev($notaAbrev)
    {
        $this->notaAbrev = $notaAbrev;
    
        return $this;
    }

    /**
     * Get notaAbrev
     *
     * @return string 
     */
    public function getNotaAbrev()
    {
        return $this->notaAbrev;
    }

    /**
     * Set notaTipoId
     *
     * @param integer $notaTipoId
     * @return CatalogoLibretaTipo
     */
    public function setNotaTipoId($notaTipoId)
    {
        $this->notaTipoId = $notaTipoId;
    
        return $this;
    }

    /**
     * Get notaTipoId
     *
     * @return integer 
     */
    public function getNotaTipoId()
    {
        return $this->notaTipoId;
    }

    /**
     * Set notaTipo
     *
     * @param string $notaTipo
     * @return CatalogoLibretaTipo
     */
    public function setNotaTipo($notaTipo)
    {
        $this->notaTipo = $notaTipo;
    
        return $this;
    }

    /**
     * Get notaTipo
     *
     * @return string 
     */
    public function getNotaTipo()
    {
        return $this->notaTipo;
    }

    /**
     * Set orden
     *
     * @param integer $orden
     * @return CatalogoLibretaTipo
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;
    
        return $this;
    }

    /**
     * Get orden
     *
     * @return integer 
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * Set notaBoletin
     *
     * @param string $notaBoletin
     * @return CatalogoLibretaTipo
     */
    public function setNotaBoletin($notaBoletin)
    {
        $this->notaBoletin = $notaBoletin;
    
        return $this;
    }

    /**
     * Get notaBoletin
     *
     * @return string 
     */
    public function getNotaBoletin()
    {
        return $this->notaBoletin;
    }
}
