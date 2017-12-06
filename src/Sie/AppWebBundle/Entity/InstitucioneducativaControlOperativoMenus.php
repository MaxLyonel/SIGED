<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaControlOperativoMenus
 */
class InstitucioneducativaControlOperativoMenus
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
    private $estadoMenu;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\NotaTipo
     */
    private $notaTipo;

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
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return InstitucioneducativaControlOperativoMenus
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
     * @return InstitucioneducativaControlOperativoMenus
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
     * @return InstitucioneducativaControlOperativoMenus
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
     * Set notaTipo
     *
     * @param \Sie\AppWebBundle\Entity\NotaTipo $notaTipo
     * @return InstitucioneducativaControlOperativoMenus
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

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaControlOperativoMenus
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
