<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BfEstudiantesValidados
 */
class BfEstudiantesValidados
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $codigoRude;

    /**
     * @var integer
     */
    private $estudianteInscripcionIdant;

    /**
     * @var integer
     */
    private $estudianteInscripcionId;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var integer
     */
    private $gestionTipoId;


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
     * Set codigoRude
     *
     * @param string $codigoRude
     * @return BfEstudiantesValidados
     */
    public function setCodigoRude($codigoRude)
    {
        $this->codigoRude = $codigoRude;
    
        return $this;
    }

    /**
     * Get codigoRude
     *
     * @return string 
     */
    public function getCodigoRude()
    {
        return $this->codigoRude;
    }

    /**
     * Set estudianteInscripcionIdant
     *
     * @param integer $estudianteInscripcionIdant
     * @return BfEstudiantesValidados
     */
    public function setEstudianteInscripcionIdant($estudianteInscripcionIdant)
    {
        $this->estudianteInscripcionIdant = $estudianteInscripcionIdant;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionIdant
     *
     * @return integer 
     */
    public function getEstudianteInscripcionIdant()
    {
        return $this->estudianteInscripcionIdant;
    }

    /**
     * Set estudianteInscripcionId
     *
     * @param integer $estudianteInscripcionId
     * @return BfEstudiantesValidados
     */
    public function setEstudianteInscripcionId($estudianteInscripcionId)
    {
        $this->estudianteInscripcionId = $estudianteInscripcionId;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionId
     *
     * @return integer 
     */
    public function getEstudianteInscripcionId()
    {
        return $this->estudianteInscripcionId;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return BfEstudiantesValidados
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return BfEstudiantesValidados
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
}
