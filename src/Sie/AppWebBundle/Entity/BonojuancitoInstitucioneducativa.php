<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonojuancitoInstitucioneducativa
 */
class BonojuancitoInstitucioneducativa
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
    private $cantidadEstudiantes;

    /**
     * @var integer
     */
    private $institucioneducativaIdNueva;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var string
     */
    private $tipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\BonojuancitoInstitucioneducativaTipo
     */
    private $bonojuancitoInstitucioneducativaTipo;


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
     * @return BonojuancitoInstitucioneducativa
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
     * Set cantidadEstudiantes
     *
     * @param integer $cantidadEstudiantes
     * @return BonojuancitoInstitucioneducativa
     */
    public function setCantidadEstudiantes($cantidadEstudiantes)
    {
        $this->cantidadEstudiantes = $cantidadEstudiantes;
    
        return $this;
    }

    /**
     * Get cantidadEstudiantes
     *
     * @return integer 
     */
    public function getCantidadEstudiantes()
    {
        return $this->cantidadEstudiantes;
    }

    /**
     * Set institucioneducativaIdNueva
     *
     * @param integer $institucioneducativaIdNueva
     * @return BonojuancitoInstitucioneducativa
     */
    public function setInstitucioneducativaIdNueva($institucioneducativaIdNueva)
    {
        $this->institucioneducativaIdNueva = $institucioneducativaIdNueva;
    
        return $this;
    }

    /**
     * Get institucioneducativaIdNueva
     *
     * @return integer 
     */
    public function getInstitucioneducativaIdNueva()
    {
        return $this->institucioneducativaIdNueva;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return BonojuancitoInstitucioneducativa
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
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return BonojuancitoInstitucioneducativa
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
     * Set tipo
     *
     * @param string $tipo
     * @return BonojuancitoInstitucioneducativa
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    
        return $this;
    }

    /**
     * Get tipo
     *
     * @return string 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return BonojuancitoInstitucioneducativa
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
     * Set bonojuancitoInstitucioneducativaTipo
     *
     * @param \Sie\AppWebBundle\Entity\BonojuancitoInstitucioneducativaTipo $bonojuancitoInstitucioneducativaTipo
     * @return BonojuancitoInstitucioneducativa
     */
    public function setBonojuancitoInstitucioneducativaTipo(\Sie\AppWebBundle\Entity\BonojuancitoInstitucioneducativaTipo $bonojuancitoInstitucioneducativaTipo = null)
    {
        $this->bonojuancitoInstitucioneducativaTipo = $bonojuancitoInstitucioneducativaTipo;
    
        return $this;
    }

    /**
     * Get bonojuancitoInstitucioneducativaTipo
     *
     * @return \Sie\AppWebBundle\Entity\BonojuancitoInstitucioneducativaTipo 
     */
    public function getBonojuancitoInstitucioneducativaTipo()
    {
        return $this->bonojuancitoInstitucioneducativaTipo;
    }
}
