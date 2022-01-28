<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeEducacionDiversa
 */
class RudeEducacionDiversa
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
     * @var \Sie\AppWebBundle\Entity\EducacionDiversaTipo
     */
    private $educacionDiversaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\RecintoPenitenciarioTipo
     */
    private $recintoPenitenciarioTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnidadMilitarTipo
     */
    private $unidadMilitarTipo;


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
     * @return RudeEducacionDiversa
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
     * @return RudeEducacionDiversa
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
     * @return RudeEducacionDiversa
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
     * Set educacionDiversaTipo
     *
     * @param \Sie\AppWebBundle\Entity\EducacionDiversaTipo $educacionDiversaTipo
     * @return RudeEducacionDiversa
     */
    public function setEducacionDiversaTipo(\Sie\AppWebBundle\Entity\EducacionDiversaTipo $educacionDiversaTipo = null)
    {
        $this->educacionDiversaTipo = $educacionDiversaTipo;
    
        return $this;
    }

    /**
     * Get educacionDiversaTipo
     *
     * @return \Sie\AppWebBundle\Entity\EducacionDiversaTipo 
     */
    public function getEducacionDiversaTipo()
    {
        return $this->educacionDiversaTipo;
    }

    /**
     * Set recintoPenitenciarioTipo
     *
     * @param \Sie\AppWebBundle\Entity\RecintoPenitenciarioTipo $recintoPenitenciarioTipo
     * @return RudeEducacionDiversa
     */
    public function setRecintoPenitenciarioTipo(\Sie\AppWebBundle\Entity\RecintoPenitenciarioTipo $recintoPenitenciarioTipo = null)
    {
        $this->recintoPenitenciarioTipo = $recintoPenitenciarioTipo;
    
        return $this;
    }

    /**
     * Get recintoPenitenciarioTipo
     *
     * @return \Sie\AppWebBundle\Entity\RecintoPenitenciarioTipo 
     */
    public function getRecintoPenitenciarioTipo()
    {
        return $this->recintoPenitenciarioTipo;
    }

    /**
     * Set unidadMilitarTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnidadMilitarTipo $unidadMilitarTipo
     * @return RudeEducacionDiversa
     */
    public function setUnidadMilitarTipo(\Sie\AppWebBundle\Entity\UnidadMilitarTipo $unidadMilitarTipo = null)
    {
        $this->unidadMilitarTipo = $unidadMilitarTipo;
    
        return $this;
    }

    /**
     * Get unidadMilitarTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnidadMilitarTipo 
     */
    public function getUnidadMilitarTipo()
    {
        return $this->unidadMilitarTipo;
    }
}
