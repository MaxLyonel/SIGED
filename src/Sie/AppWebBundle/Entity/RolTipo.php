<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RolTipo
 */
class RolTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $rol;

    /**
     * @var integer
     */
    private $lugarNivelTipoId;

    /**
     * @var string
     */
    private $subSistema;

    /**
     * @var string
     */
    private $diminutivo;


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
     * Set rol
     *
     * @param string $rol
     * @return RolTipo
     */
    public function setRol($rol)
    {
        $this->rol = $rol;
    
        return $this;
    }

    /**
     * Get rol
     *
     * @return string 
     */
    public function getRol()
    {
        return $this->rol;
    }

    /**
     * Set lugarNivelTipoId
     *
     * @param integer $lugarNivelTipoId
     * @return RolTipo
     */
    public function setLugarNivelTipoId($lugarNivelTipoId)
    {
        $this->lugarNivelTipoId = $lugarNivelTipoId;
    
        return $this;
    }

    /**
     * Get lugarNivelTipoId
     *
     * @return integer 
     */
    public function getLugarNivelTipoId()
    {
        return $this->lugarNivelTipoId;
    }

    /**
     * Set subSistema
     *
     * @param string $subSistema
     * @return RolTipo
     */
    public function setSubSistema($subSistema)
    {
        $this->subSistema = $subSistema;
    
        return $this;
    }

    /**
     * Get subSistema
     *
     * @return string 
     */
    public function getSubSistema()
    {
        return $this->subSistema;
    }

    /**
     * Set diminutivo
     *
     * @param string $diminutivo
     * @return RolTipo
     */
    public function setDiminutivo($diminutivo)
    {
        $this->diminutivo = $diminutivo;
    
        return $this;
    }

    /**
     * Get diminutivo
     *
     * @return string 
     */
    public function getDiminutivo()
    {
        return $this->diminutivo;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\LugarNivelTipo
     */
    private $lugarNivelTipo;


    /**
     * Set lugarNivelTipo
     *
     * @param \Sie\AppWebBundle\Entity\LugarNivelTipo $lugarNivelTipo
     * @return RolTipo
     */
    public function setLugarNivelTipo(\Sie\AppWebBundle\Entity\LugarNivelTipo $lugarNivelTipo = null)
    {
        $this->lugarNivelTipo = $lugarNivelTipo;
    
        return $this;
    }

    /**
     * Get lugarNivelTipo
     *
     * @return \Sie\AppWebBundle\Entity\LugarNivelTipo 
     */
    public function getLugarNivelTipo()
    {
        return $this->lugarNivelTipo;
    }

    public function __toString() {
        return $this->rol;
    }
}
