<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuObjeto
 */
class MenuObjeto
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fcreacion;

    /**
     * @var \DateTime
     */
    private $fupdate;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var \Sie\AppWebBundle\Entity\MenuTipo
     */
    private $menuTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ObjetoTipo
     */
    private $objetoTipo;


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
     * Set fcreacion
     *
     * @param \DateTime $fcreacion
     * @return MenuObjeto
     */
    public function setFcreacion($fcreacion)
    {
        $this->fcreacion = $fcreacion;

        return $this;
    }

    /**
     * Get fcreacion
     *
     * @return \DateTime 
     */
    public function getFcreacion()
    {
        return $this->fcreacion;
    }

    /**
     * Set fupdate
     *
     * @param \DateTime $fupdate
     * @return MenuObjeto
     */
    public function setFupdate($fupdate)
    {
        $this->fupdate = $fupdate;

        return $this;
    }

    /**
     * Get fupdate
     *
     * @return \DateTime 
     */
    public function getFupdate()
    {
        return $this->fupdate;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return MenuObjeto
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
     * Set menuTipo
     *
     * @param \Sie\AppWebBundle\Entity\MenuTipo $menuTipo
     * @return MenuObjeto
     */
    public function setMenuTipo(\Sie\AppWebBundle\Entity\MenuTipo $menuTipo = null)
    {
        $this->menuTipo = $menuTipo;

        return $this;
    }

    /**
     * Get menuTipo
     *
     * @return \Sie\AppWebBundle\Entity\MenuTipo 
     */
    public function getMenuTipo()
    {
        return $this->menuTipo;
    }

    /**
     * Set objetoTipo
     *
     * @param \Sie\AppWebBundle\Entity\ObjetoTipo $objetoTipo
     * @return MenuObjeto
     */
    public function setObjetoTipo(\Sie\AppWebBundle\Entity\ObjetoTipo $objetoTipo = null)
    {
        $this->objetoTipo = $objetoTipo;

        return $this;
    }

    /**
     * Get objetoTipo
     *
     * @return \Sie\AppWebBundle\Entity\ObjetoTipo 
     */
    public function getObjetoTipo()
    {
        return $this->objetoTipo;
    }
    /**
     * @var integer
     */
    private $orden;

    /**
     * @var \Sie\AppWebBundle\Entity\SistemaTipo
     */
    private $sistemaTipo;


    /**
     * Set orden
     *
     * @param integer $orden
     * @return MenuObjeto
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;
    
        return $this;
    }

    /**
     * Get orden
     *
     * @return integer 
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * Set sistemaTipo
     *
     * @param \Sie\AppWebBundle\Entity\SistemaTipo $sistemaTipo
     * @return MenuObjeto
     */
    public function setSistemaTipo(\Sie\AppWebBundle\Entity\SistemaTipo $sistemaTipo = null)
    {
        $this->sistemaTipo = $sistemaTipo;
    
        return $this;
    }

    /**
     * Get sistemaTipo
     *
     * @return \Sie\AppWebBundle\Entity\SistemaTipo 
     */
    public function getSistemaTipo()
    {
        return $this->sistemaTipo;
    }
}
