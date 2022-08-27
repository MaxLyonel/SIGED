<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstTecSede
 */
class EstTecSede
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $sede;

    /**
     * @var string
     */
    private $resolucionSuprema;

    /**
     * @var string
     */
    private $resolucionMinisterial;

    /**
     * @var string
     */
    private $naturalezaJuridica;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecSedeTipo
     */
    private $estTecSedeTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecNaturalezajuridicaTipo
     */
    private $estTecNaturalezajuridicaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecInstituto
     */
    private $estTecInstituto;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecJurisdiccionGeografica
     */
    private $estTecJuridicciongeografica;


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
     * Set sede
     *
     * @param string $sede
     * @return EstTecSede
     */
    public function setSede($sede)
    {
        $this->sede = $sede;
    
        return $this;
    }

    /**
     * Get sede
     *
     * @return string 
     */
    public function getSede()
    {
        return $this->sede;
    }

    /**
     * Set resolucionSuprema
     *
     * @param string $resolucionSuprema
     * @return EstTecSede
     */
    public function setResolucionSuprema($resolucionSuprema)
    {
        $this->resolucionSuprema = $resolucionSuprema;
    
        return $this;
    }

    /**
     * Get resolucionSuprema
     *
     * @return string 
     */
    public function getResolucionSuprema()
    {
        return $this->resolucionSuprema;
    }

    /**
     * Set resolucionMinisterial
     *
     * @param string $resolucionMinisterial
     * @return EstTecSede
     */
    public function setResolucionMinisterial($resolucionMinisterial)
    {
        $this->resolucionMinisterial = $resolucionMinisterial;
    
        return $this;
    }

    /**
     * Get resolucionMinisterial
     *
     * @return string 
     */
    public function getResolucionMinisterial()
    {
        return $this->resolucionMinisterial;
    }

    /**
     * Set naturalezaJuridica
     *
     * @param string $naturalezaJuridica
     * @return EstTecSede
     */
    public function setNaturalezaJuridica($naturalezaJuridica)
    {
        $this->naturalezaJuridica = $naturalezaJuridica;
    
        return $this;
    }

    /**
     * Get naturalezaJuridica
     *
     * @return string 
     */
    public function getNaturalezaJuridica()
    {
        return $this->naturalezaJuridica;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstTecSede
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
     * @return EstTecSede
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
     * Set estTecSedeTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstTecSedeTipo $estTecSedeTipo
     * @return EstTecSede
     */
    public function setEstTecSedeTipo(\Sie\AppWebBundle\Entity\EstTecSedeTipo $estTecSedeTipo = null)
    {
        $this->estTecSedeTipo = $estTecSedeTipo;
    
        return $this;
    }

    /**
     * Get estTecSedeTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstTecSedeTipo 
     */
    public function getEstTecSedeTipo()
    {
        return $this->estTecSedeTipo;
    }

    /**
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return EstTecSede
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
     * Set estTecNaturalezajuridicaTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstTecNaturalezajuridicaTipo $estTecNaturalezajuridicaTipo
     * @return EstTecSede
     */
    public function setEstTecNaturalezajuridicaTipo(\Sie\AppWebBundle\Entity\EstTecNaturalezajuridicaTipo $estTecNaturalezajuridicaTipo = null)
    {
        $this->estTecNaturalezajuridicaTipo = $estTecNaturalezajuridicaTipo;
    
        return $this;
    }

    /**
     * Get estTecNaturalezajuridicaTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstTecNaturalezajuridicaTipo 
     */
    public function getEstTecNaturalezajuridicaTipo()
    {
        return $this->estTecNaturalezajuridicaTipo;
    }

    /**
     * Set estTecInstituto
     *
     * @param \Sie\AppWebBundle\Entity\EstTecInstituto $estTecInstituto
     * @return EstTecSede
     */
    public function setEstTecInstituto(\Sie\AppWebBundle\Entity\EstTecInstituto $estTecInstituto = null)
    {
        $this->estTecInstituto = $estTecInstituto;
    
        return $this;
    }

    /**
     * Get estTecInstituto
     *
     * @return \Sie\AppWebBundle\Entity\EstTecInstituto 
     */
    public function getEstTecInstituto()
    {
        return $this->estTecInstituto;
    }

    /**
     * Set estTecJuridicciongeografica
     *
     * @param \Sie\AppWebBundle\Entity\EstTecJurisdiccionGeografica $estTecJuridicciongeografica
     * @return EstTecSede
     */
    public function setEstTecJuridicciongeografica(\Sie\AppWebBundle\Entity\EstTecJurisdiccionGeografica $estTecJuridicciongeografica = null)
    {
        $this->estTecJuridicciongeografica = $estTecJuridicciongeografica;
    
        return $this;
    }

    /**
     * Get estTecJuridicciongeografica
     *
     * @return \Sie\AppWebBundle\Entity\EstTecJurisdiccionGeografica 
     */
    public function getEstTecJuridicciongeografica()
    {
        return $this->estTecJuridicciongeografica;
    }
}
