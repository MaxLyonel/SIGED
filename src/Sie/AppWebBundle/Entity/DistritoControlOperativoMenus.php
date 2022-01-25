<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DistritoControlOperativoMenus
 */
class DistritoControlOperativoMenus
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $distrito;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var integer
     */
    private $estadoMenu;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var integer
     */
    private $departamentoTipoId;

    /**
     * @var \Sie\AppWebBundle\Entity\DistritoTipo
     */
    private $distritoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\NotaTipo
     */
    private $notaTipo;


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
     * Set distrito
     *
     * @param string $distrito
     * @return DistritoControlOperativoMenus
     */
    public function setDistrito($distrito)
    {
        $this->distrito = $distrito;
    
        return $this;
    }

    /**
     * Get distrito
     *
     * @return string 
     */
    public function getDistrito()
    {
        return $this->distrito;
    }

    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return DistritoControlOperativoMenus
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
     * Set estadoMenu
     *
     * @param integer $estadoMenu
     * @return DistritoControlOperativoMenus
     */
    public function setEstadoMenu($estadoMenu)
    {
        $this->estadoMenu = $estadoMenu;
    
        return $this;
    }

    /**
     * Get estadoMenu
     *
     * @return integer 
     */
    public function getEstadoMenu()
    {
        return $this->estadoMenu;
    }

    /**
     * Set fecharegistro
     *
     * @param \DateTime $fecharegistro
     * @return DistritoControlOperativoMenus
     */
    public function setFecharegistro($fecharegistro)
    {
        $this->fecharegistro = $fecharegistro;
    
        return $this;
    }

    /**
     * Get fecharegistro
     *
     * @return \DateTime 
     */
    public function getFecharegistro()
    {
        return $this->fecharegistro;
    }

    /**
     * Set departamentoTipoId
     *
     * @param integer $departamentoTipoId
     * @return DistritoControlOperativoMenus
     */
    public function setDepartamentoTipoId($departamentoTipoId)
    {
        $this->departamentoTipoId = $departamentoTipoId;
    
        return $this;
    }

    /**
     * Get departamentoTipoId
     *
     * @return integer 
     */
    public function getDepartamentoTipoId()
    {
        return $this->departamentoTipoId;
    }

    /**
     * Set distritoTipo
     *
     * @param \Sie\AppWebBundle\Entity\DistritoTipo $distritoTipo
     * @return DistritoControlOperativoMenus
     */
    public function setDistritoTipo(\Sie\AppWebBundle\Entity\DistritoTipo $distritoTipo = null)
    {
        $this->distritoTipo = $distritoTipo;
    
        return $this;
    }

    /**
     * Get distritoTipo
     *
     * @return \Sie\AppWebBundle\Entity\DistritoTipo 
     */
    public function getDistritoTipo()
    {
        return $this->distritoTipo;
    }

    /**
     * Set notaTipo
     *
     * @param \Sie\AppWebBundle\Entity\NotaTipo $notaTipo
     * @return DistritoControlOperativoMenus
     */
    public function setNotaTipo(\Sie\AppWebBundle\Entity\NotaTipo $notaTipo = null)
    {
        $this->notaTipo = $notaTipo;
    
        return $this;
    }

    /**
     * Get notaTipo
     *
     * @return \Sie\AppWebBundle\Entity\NotaTipo 
     */
    public function getNotaTipo()
    {
        return $this->notaTipo;
    }
}
