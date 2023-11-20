<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SuperiorModuloTipo
 */
class SuperiorModuloTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $modulo;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var integer
     */
    private $codigo;

    /**
     * @var string
     */
    private $sigla;

    /**
     * @var integer
     */
    private $oficial;

    /**
     * @var string
     */
    private $contenido;

    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorAreaSaberesTipo
     */
    private $superiorAreaSaberesTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorEspecialidadTipo
     */
    private $superiorEspecialidadTipo;


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
     * Set modulo
     *
     * @param string $modulo
     * @return SuperiorModuloTipo
     */
    public function setModulo($modulo)
    {
        $this->modulo = $modulo;
    
        return $this;
    }

    /**
     * Get modulo
     *
     * @return string 
     */
    public function getModulo()
    {
        return $this->modulo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return SuperiorModuloTipo
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
     * Set codigo
     *
     * @param integer $codigo
     * @return SuperiorModuloTipo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return integer 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set sigla
     *
     * @param string $sigla
     * @return SuperiorModuloTipo
     */
    public function setSigla($sigla)
    {
        $this->sigla = $sigla;
    
        return $this;
    }

    /**
     * Get sigla
     *
     * @return string 
     */
    public function getSigla()
    {
        return $this->sigla;
    }

    /**
     * Set oficial
     *
     * @param integer $oficial
     * @return SuperiorModuloTipo
     */
    public function setOficial($oficial)
    {
        $this->oficial = $oficial;
    
        return $this;
    }

    /**
     * Get oficial
     *
     * @return integer 
     */
    public function getOficial()
    {
        return $this->oficial;
    }

    /**
     * Set contenido
     *
     * @param string $contenido
     * @return SuperiorModuloTipo
     */
    public function setContenido($contenido)
    {
        $this->contenido = $contenido;
    
        return $this;
    }

    /**
     * Get contenido
     *
     * @return string 
     */
    public function getContenido()
    {
        return $this->contenido;
    }

    /**
     * Set superiorAreaSaberesTipo
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorAreaSaberesTipo $superiorAreaSaberesTipo
     * @return SuperiorModuloTipo
     */
    public function setSuperiorAreaSaberesTipo(\Sie\AppWebBundle\Entity\SuperiorAreaSaberesTipo $superiorAreaSaberesTipo = null)
    {
        $this->superiorAreaSaberesTipo = $superiorAreaSaberesTipo;
    
        return $this;
    }

    /**
     * Get superiorAreaSaberesTipo
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorAreaSaberesTipo 
     */
    public function getSuperiorAreaSaberesTipo()
    {
        return $this->superiorAreaSaberesTipo;
    }

    /**
     * Set superiorEspecialidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorEspecialidadTipo $superiorEspecialidadTipo
     * @return SuperiorEspecialidadTipo
     */
    public function setSuperiorEspecialidadTipo(\Sie\AppWebBundle\Entity\SuperiorEspecialidadTipo $superiorEspecialidadTipo = null)
    {
        $this->superiorEspecialidadTipo = $superiorEspecialidadTipo;
    
        return $this;
    }

    /**
     * Get superiorEspecialidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorEspecialidadTipo 
     */
    public function getSuperiorEspecialidadTipo()
    {
        return $this->superiorEspecialidadTipo;
    }


    /**
     * @var boolean
     */
    private $esvigente;

    /**
     * Set esvigente
     *
     * @param boolean $esvigente
     * @return SuperiorModuloTipo
     */
    public function setEsvigente($esvigente)
    {
        $this->esvigente = $esvigente;
    
        return $this;
    }

    /**
     * Get esvigente
     *
     * @return boolean 
     */
    public function getEsvigente()
    {
        return $this->esvigente;
    }
}
