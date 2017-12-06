<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaEspecialidadTecnicoHumanistico
 */
class InstitucioneducativaEspecialidadTecnicoHumanistico
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
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo
     */
    private $especialidadTecnicoHumanisticoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return InstitucioneducativaEspecialidadTecnicoHumanistico
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
     * Set obs
     *
     * @param string $obs
     * @return InstitucioneducativaEspecialidadTecnicoHumanistico
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
     * Set especialidadTecnicoHumanisticoTipo
     *
     * @param \Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo $especialidadTecnicoHumanisticoTipo
     * @return InstitucioneducativaEspecialidadTecnicoHumanistico
     */
    public function setEspecialidadTecnicoHumanisticoTipo(\Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo $especialidadTecnicoHumanisticoTipo = null)
    {
        $this->especialidadTecnicoHumanisticoTipo = $especialidadTecnicoHumanisticoTipo;
    
        return $this;
    }

    /**
     * Get especialidadTecnicoHumanisticoTipo
     *
     * @return \Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo 
     */
    public function getEspecialidadTecnicoHumanisticoTipo()
    {
        return $this->especialidadTecnicoHumanisticoTipo;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return InstitucioneducativaEspecialidadTecnicoHumanistico
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaEspecialidadTecnicoHumanistico
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
