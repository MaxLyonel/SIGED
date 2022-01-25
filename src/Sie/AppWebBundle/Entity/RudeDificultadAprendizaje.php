<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeDificultadAprendizaje
 */
class RudeDificultadAprendizaje
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Rude
     */
    private $rude;

    /**
     * @var \Sie\AppWebBundle\Entity\DificultadAprendizajeTipo
     */
    private $dificultadAprendizajeTipo;


    /**
     * Set id
     *
     * @param integer $id
     * @return RudeDificultadAprendizaje
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
    }

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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return RudeDificultadAprendizaje
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
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return RudeDificultadAprendizaje
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime 
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set rude
     *
     * @param \Sie\AppWebBundle\Entity\Rude $rude
     * @return RudeDificultadAprendizaje
     */
    public function setRude(\Sie\AppWebBundle\Entity\Rude $rude = null)
    {
        $this->rude = $rude;
    
        return $this;
    }

    /**
     * Get rude
     *
     * @return \Sie\AppWebBundle\Entity\Rude 
     */
    public function getRude()
    {
        return $this->rude;
    }

    /**
     * Set dificultadAprendizajeTipo
     *
     * @param \Sie\AppWebBundle\Entity\DificultadAprendizajeTipo $dificultadAprendizajeTipo
     * @return RudeDificultadAprendizaje
     */
    public function setDificultadAprendizajeTipo(\Sie\AppWebBundle\Entity\DificultadAprendizajeTipo $dificultadAprendizajeTipo = null)
    {
        $this->dificultadAprendizajeTipo = $dificultadAprendizajeTipo;
    
        return $this;
    }

    /**
     * Get dificultadAprendizajeTipo
     *
     * @return \Sie\AppWebBundle\Entity\DificultadAprendizajeTipo 
     */
    public function getDificultadAprendizajeTipo()
    {
        return $this->dificultadAprendizajeTipo;
    }
}
