<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Carreraespecialidad
 */
class Carreraespecialidad
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $especialidad;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\CarreraTipo
     */
    private $carreraTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\PermanenteprogramaTipo
     */
    private $permanenteprogramaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\AcreditacionnivelTipo
     */
    private $acreditacionnivelTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ModalidadTipo
     */
    private $modalidadTipo;


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
     * Set especialidad
     *
     * @param string $especialidad
     * @return Carreraespecialidad
     */
    public function setEspecialidad($especialidad)
    {
        $this->especialidad = $especialidad;
    
        return $this;
    }

    /**
     * Get especialidad
     *
     * @return string 
     */
    public function getEspecialidad()
    {
        return $this->especialidad;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return Carreraespecialidad
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
     * Set obs
     *
     * @param string $obs
     * @return Carreraespecialidad
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
     * Set carreraTipo
     *
     * @param \Sie\AppWebBundle\Entity\CarreraTipo $carreraTipo
     * @return Carreraespecialidad
     */
    public function setCarreraTipo(\Sie\AppWebBundle\Entity\CarreraTipo $carreraTipo = null)
    {
        $this->carreraTipo = $carreraTipo;
    
        return $this;
    }

    /**
     * Get carreraTipo
     *
     * @return \Sie\AppWebBundle\Entity\CarreraTipo 
     */
    public function getCarreraTipo()
    {
        return $this->carreraTipo;
    }

    /**
     * Set permanenteprogramaTipo
     *
     * @param \Sie\AppWebBundle\Entity\PermanenteprogramaTipo $permanenteprogramaTipo
     * @return Carreraespecialidad
     */
    public function setPermanenteprogramaTipo(\Sie\AppWebBundle\Entity\PermanenteprogramaTipo $permanenteprogramaTipo = null)
    {
        $this->permanenteprogramaTipo = $permanenteprogramaTipo;
    
        return $this;
    }

    /**
     * Get permanenteprogramaTipo
     *
     * @return \Sie\AppWebBundle\Entity\PermanenteprogramaTipo 
     */
    public function getPermanenteprogramaTipo()
    {
        return $this->permanenteprogramaTipo;
    }

    /**
     * Set acreditacionnivelTipo
     *
     * @param \Sie\AppWebBundle\Entity\AcreditacionnivelTipo $acreditacionnivelTipo
     * @return Carreraespecialidad
     */
    public function setAcreditacionnivelTipo(\Sie\AppWebBundle\Entity\AcreditacionnivelTipo $acreditacionnivelTipo = null)
    {
        $this->acreditacionnivelTipo = $acreditacionnivelTipo;
    
        return $this;
    }

    /**
     * Get acreditacionnivelTipo
     *
     * @return \Sie\AppWebBundle\Entity\AcreditacionnivelTipo 
     */
    public function getAcreditacionnivelTipo()
    {
        return $this->acreditacionnivelTipo;
    }

    /**
     * Set modalidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\ModalidadTipo $modalidadTipo
     * @return Carreraespecialidad
     */
    public function setModalidadTipo(\Sie\AppWebBundle\Entity\ModalidadTipo $modalidadTipo = null)
    {
        $this->modalidadTipo = $modalidadTipo;
    
        return $this;
    }

    /**
     * Get modalidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\ModalidadTipo 
     */
    public function getModalidadTipo()
    {
        return $this->modalidadTipo;
    }
}
