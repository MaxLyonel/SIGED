<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AltModuloemergente
 */
class AltModuloemergente
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $moduloEmergente;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta
     */
    private $institucioneducativaCursoOferta;


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
     * Set moduloEmergente
     *
     * @param string $moduloEmergente
     * @return AltModuloemergente
     */
    public function setModuloEmergente($moduloEmergente)
    {
        $this->moduloEmergente = $moduloEmergente;
    
        return $this;
    }

    /**
     * Get moduloEmergente
     *
     * @return string 
     */
    public function getModuloEmergente()
    {
        return $this->moduloEmergente;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return AltModuloemergente
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return AltModuloemergente
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
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return AltModuloemergente
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
     * Set institucioneducativaCursoOferta
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta $institucioneducativaCursoOferta
     * @return AltModuloemergente
     */
    public function setInstitucioneducativaCursoOferta(\Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta $institucioneducativaCursoOferta = null)
    {
        $this->institucioneducativaCursoOferta = $institucioneducativaCursoOferta;
    
        return $this;
    }

    /**
     * Get institucioneducativaCursoOferta
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta 
     */
    public function getInstitucioneducativaCursoOferta()
    {
        return $this->institucioneducativaCursoOferta;
    }
}
