<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeCatalogo
 */
class RudeCatalogo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombreTabla;

    /**
     * @var string
     */
    private $campoTabla;

    /**
     * @var integer
     */
    private $llaveTabla;

    /**
     * @var boolean
     */
    private $esVigente;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\ClaseTablaTipo
     */
    private $claseTablaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaTipo
     */
    private $institucioneducativaTipo;

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
     * Set nombreTabla
     *
     * @param string $nombreTabla
     * @return RudeCatalogo
     */
    public function setNombreTabla($nombreTabla)
    {
        $this->nombreTabla = $nombreTabla;
    
        return $this;
    }

    /**
     * Get nombreTabla
     *
     * @return string 
     */
    public function getNombreTabla()
    {
        return $this->nombreTabla;
    }

    /**
     * Set campoTabla
     *
     * @param string $campoTabla
     * @return RudeCatalogo
     */
    public function setCampoTabla($campoTabla)
    {
        $this->campoTabla = $campoTabla;
    
        return $this;
    }

    /**
     * Get campoTabla
     *
     * @return string 
     */
    public function getCampoTabla()
    {
        return $this->campoTabla;
    }

    /**
     * Set llaveTabla
     *
     * @param integer $llaveTabla
     * @return RudeCatalogo
     */
    public function setLlaveTabla($llaveTabla)
    {
        $this->llaveTabla = $llaveTabla;
    
        return $this;
    }

    /**
     * Get llaveTabla
     *
     * @return integer 
     */
    public function getLlaveTabla()
    {
        return $this->llaveTabla;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return RudeCatalogo
     */
    public function setEsVigente($esVigente)
    {
        $this->esVigente = $esVigente;
    
        return $this;
    }

    /**
     * Get esVigente
     *
     * @return boolean 
     */
    public function getEsVigente()
    {
        return $this->esVigente;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return RudeCatalogo
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
     * @return RudeCatalogo
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
     * Set claseTablaTipo
     *
     * @param \Sie\AppWebBundle\Entity\ClaseTablaTipo $claseTablaTipo
     * @return RudeCatalogo
     */
    public function setClaseTablaTipo(\Sie\AppWebBundle\Entity\ClaseTablaTipo $claseTablaTipo = null)
    {
        $this->claseTablaTipo = $claseTablaTipo;
    
        return $this;
    }

    /**
     * Get claseTablaTipo
     *
     * @return \Sie\AppWebBundle\Entity\ClaseTablaTipo 
     */
    public function getClaseTablaTipo()
    {
        return $this->claseTablaTipo;
    }

    /**
     * Set institucioneducativaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo
     * @return RudeCatalogo
     */
    public function setInstitucioneducativaTipo(\Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo = null)
    {
        $this->institucioneducativaTipo = $institucioneducativaTipo;
    
        return $this;
    }

    /**
     * Get institucioneducativaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaTipo 
     */
    public function getInstitucioneducativaTipo()
    {
        return $this->institucioneducativaTipo;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return RudeCatalogo
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
}
