<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioRol
 */
class UsuarioRol
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var string
     */
    private $subSistema;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var \Sie\AppWebBundle\Entity\RolTipo
     */
    private $rolTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\LugarTipo
     */
    private $lugarTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\CircunscripcionTipo
     */
    private $circunscripcionTipo;


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
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return UsuarioRol
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
     * Set subSistema
     *
     * @param string $subSistema
     * @return UsuarioRol
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
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return UsuarioRol
     */
    public function setUsuario(\Sie\AppWebBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set rolTipo
     *
     * @param \Sie\AppWebBundle\Entity\RolTipo $rolTipo
     * @return UsuarioRol
     */
    public function setRolTipo(\Sie\AppWebBundle\Entity\RolTipo $rolTipo = null)
    {
        $this->rolTipo = $rolTipo;
    
        return $this;
    }

    /**
     * Get rolTipo
     *
     * @return \Sie\AppWebBundle\Entity\RolTipo 
     */
    public function getRolTipo()
    {
        return $this->rolTipo;
    }

    /**
     * Set lugarTipo
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $lugarTipo
     * @return UsuarioRol
     */
    public function setLugarTipo(\Sie\AppWebBundle\Entity\LugarTipo $lugarTipo = null)
    {
        $this->lugarTipo = $lugarTipo;
    
        return $this;
    }

    /**
     * Get lugarTipo
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getLugarTipo()
    {
        return $this->lugarTipo;
    }

    /**
     * Set circunscripcionTipo
     *
     * @param \Sie\AppWebBundle\Entity\CircunscripcionTipo $circunscripcionTipo
     * @return UsuarioRol
     */
    public function setCircunscripcionTipo(\Sie\AppWebBundle\Entity\CircunscripcionTipo $circunscripcionTipo = null)
    {
        $this->circunscripcionTipo = $circunscripcionTipo;
    
        return $this;
    }

    /**
     * Get circunscripcionTipo
     *
     * @return \Sie\AppWebBundle\Entity\CircunscripcionTipo 
     */
    public function getCircunscripcionTipo()
    {
        return $this->circunscripcionTipo;
    }
}
