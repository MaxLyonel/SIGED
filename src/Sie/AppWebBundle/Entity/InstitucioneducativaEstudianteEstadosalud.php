<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaEstudianteEstadosalud
 */
class InstitucioneducativaEstudianteEstadosalud
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $enfermo2020;

    /**
     * @var integer
     */
    private $enfermo2021;

    /**
     * @var integer
     */
    private $fallecido2020;

    /**
     * @var integer
     */
    private $fallecido2021;

    /**
     * @var integer
     */
    private $sinSintomas2020;

    /**
     * @var integer
     */
    private $sinSintomas2021;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;


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
     * Set enfermo2020
     *
     * @param integer $enfermo2020
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setEnfermo2020($enfermo2020)
    {
        $this->enfermo2020 = $enfermo2020;
    
        return $this;
    }

    /**
     * Get enfermo2020
     *
     * @return integer 
     */
    public function getEnfermo2020()
    {
        return $this->enfermo2020;
    }

    /**
     * Set enfermo2021
     *
     * @param integer $enfermo2021
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setEnfermo2021($enfermo2021)
    {
        $this->enfermo2021 = $enfermo2021;
    
        return $this;
    }

    /**
     * Get enfermo2021
     *
     * @return integer 
     */
    public function getEnfermo2021()
    {
        return $this->enfermo2021;
    }

    /**
     * Set fallecido2020
     *
     * @param integer $fallecido2020
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setFallecido2020($fallecido2020)
    {
        $this->fallecido2020 = $fallecido2020;
    
        return $this;
    }

    /**
     * Get fallecido2020
     *
     * @return integer 
     */
    public function getFallecido2020()
    {
        return $this->fallecido2020;
    }

    /**
     * Set fallecido2021
     *
     * @param integer $fallecido2021
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setFallecido2021($fallecido2021)
    {
        $this->fallecido2021 = $fallecido2021;
    
        return $this;
    }

    /**
     * Get fallecido2021
     *
     * @return integer 
     */
    public function getFallecido2021()
    {
        return $this->fallecido2021;
    }

    /**
     * Set sinSintomas2020
     *
     * @param integer $sinSintomas2020
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setSinSintomas2020($sinSintomas2020)
    {
        $this->sinSintomas2020 = $sinSintomas2020;
    
        return $this;
    }

    /**
     * Get sinSintomas2020
     *
     * @return integer 
     */
    public function getSinSintomas2020()
    {
        return $this->sinSintomas2020;
    }

    /**
     * Set sinSintomas2021
     *
     * @param integer $sinSintomas2021
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setSinSintomas2021($sinSintomas2021)
    {
        $this->sinSintomas2021 = $sinSintomas2021;
    
        return $this;
    }

    /**
     * Get sinSintomas2021
     *
     * @return integer 
     */
    public function getSinSintomas2021()
    {
        return $this->sinSintomas2021;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return InstitucioneducativaEstudianteEstadosalud
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaEstudianteEstadosalud
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

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return InstitucioneducativaEstudianteEstadosalud
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
     * @var integer
     */
    private $enfermoF2020;

    /**
     * @var integer
     */
    private $enfermoF2021;

    /**
     * @var integer
     */
    private $fallecidoF2020;

    /**
     * @var integer
     */
    private $fallecidoF2021;

    /**
     * @var integer
     */
    private $sinSintomasF2020;

    /**
     * @var integer
     */
    private $sinSintomasF2021;

    /**
     * @var integer
     */
    private $enfermoM2020;

    /**
     * @var integer
     */
    private $enfermoM2021;

    /**
     * @var integer
     */
    private $fallecidoM2020;

    /**
     * @var integer
     */
    private $fallecidoM2021;

    /**
     * @var integer
     */
    private $sinSintomasM2020;

    /**
     * @var integer
     */
    private $sinSintomasM2021;


    /**
     * Set enfermoF2020
     *
     * @param integer $enfermoF2020
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setEnfermoF2020($enfermoF2020)
    {
        $this->enfermoF2020 = $enfermoF2020;
    
        return $this;
    }

    /**
     * Get enfermoF2020
     *
     * @return integer 
     */
    public function getEnfermoF2020()
    {
        return $this->enfermoF2020;
    }

    /**
     * Set enfermoF2021
     *
     * @param integer $enfermoF2021
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setEnfermoF2021($enfermoF2021)
    {
        $this->enfermoF2021 = $enfermoF2021;
    
        return $this;
    }

    /**
     * Get enfermoF2021
     *
     * @return integer 
     */
    public function getEnfermoF2021()
    {
        return $this->enfermoF2021;
    }

    /**
     * Set fallecidoF2020
     *
     * @param integer $fallecidoF2020
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setFallecidoF2020($fallecidoF2020)
    {
        $this->fallecidoF2020 = $fallecidoF2020;
    
        return $this;
    }

    /**
     * Get fallecidoF2020
     *
     * @return integer 
     */
    public function getFallecidoF2020()
    {
        return $this->fallecidoF2020;
    }

    /**
     * Set fallecidoF2021
     *
     * @param integer $fallecidoF2021
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setFallecidoF2021($fallecidoF2021)
    {
        $this->fallecidoF2021 = $fallecidoF2021;
    
        return $this;
    }

    /**
     * Get fallecidoF2021
     *
     * @return integer 
     */
    public function getFallecidoF2021()
    {
        return $this->fallecidoF2021;
    }

    /**
     * Set sinSintomasF2020
     *
     * @param integer $sinSintomasF2020
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setSinSintomasF2020($sinSintomasF2020)
    {
        $this->sinSintomasF2020 = $sinSintomasF2020;
    
        return $this;
    }

    /**
     * Get sinSintomasF2020
     *
     * @return integer 
     */
    public function getSinSintomasF2020()
    {
        return $this->sinSintomasF2020;
    }

    /**
     * Set sinSintomasF2021
     *
     * @param integer $sinSintomasF2021
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setSinSintomasF2021($sinSintomasF2021)
    {
        $this->sinSintomasF2021 = $sinSintomasF2021;
    
        return $this;
    }

    /**
     * Get sinSintomasF2021
     *
     * @return integer 
     */
    public function getSinSintomasF2021()
    {
        return $this->sinSintomasF2021;
    }

    /**
     * Set enfermoM2020
     *
     * @param integer $enfermoM2020
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setEnfermoM2020($enfermoM2020)
    {
        $this->enfermoM2020 = $enfermoM2020;
    
        return $this;
    }

    /**
     * Get enfermoM2020
     *
     * @return integer 
     */
    public function getEnfermoM2020()
    {
        return $this->enfermoM2020;
    }

    /**
     * Set enfermoM2021
     *
     * @param integer $enfermoM2021
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setEnfermoM2021($enfermoM2021)
    {
        $this->enfermoM2021 = $enfermoM2021;
    
        return $this;
    }

    /**
     * Get enfermoM2021
     *
     * @return integer 
     */
    public function getEnfermoM2021()
    {
        return $this->enfermoM2021;
    }

    /**
     * Set fallecidoM2020
     *
     * @param integer $fallecidoM2020
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setFallecidoM2020($fallecidoM2020)
    {
        $this->fallecidoM2020 = $fallecidoM2020;
    
        return $this;
    }

    /**
     * Get fallecidoM2020
     *
     * @return integer 
     */
    public function getFallecidoM2020()
    {
        return $this->fallecidoM2020;
    }

    /**
     * Set fallecidoM2021
     *
     * @param integer $fallecidoM2021
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setFallecidoM2021($fallecidoM2021)
    {
        $this->fallecidoM2021 = $fallecidoM2021;
    
        return $this;
    }

    /**
     * Get fallecidoM2021
     *
     * @return integer 
     */
    public function getFallecidoM2021()
    {
        return $this->fallecidoM2021;
    }

    /**
     * Set sinSintomasM2020
     *
     * @param integer $sinSintomasM2020
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setSinSintomasM2020($sinSintomasM2020)
    {
        $this->sinSintomasM2020 = $sinSintomasM2020;
    
        return $this;
    }

    /**
     * Get sinSintomasM2020
     *
     * @return integer 
     */
    public function getSinSintomasM2020()
    {
        return $this->sinSintomasM2020;
    }

    /**
     * Set sinSintomasM2021
     *
     * @param integer $sinSintomasM2021
     * @return InstitucioneducativaEstudianteEstadosalud
     */
    public function setSinSintomasM2021($sinSintomasM2021)
    {
        $this->sinSintomasM2021 = $sinSintomasM2021;
    
        return $this;
    }

    /**
     * Get sinSintomasM2021
     *
     * @return integer 
     */
    public function getSinSintomasM2021()
    {
        return $this->sinSintomasM2021;
    }
}
