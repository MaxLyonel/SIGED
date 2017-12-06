<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PnpReconocimientoSaberes
 */
class PnpReconocimientoSaberes
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $curso;

    /**
     * @var boolean
     */
    private $homologado;

    /**
     * @var \DateTime
     */
    private $fechaCreacion;

    /**
     * @var \DateTime
     */
    private $fechaHomologacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioHomologado;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\Estudiante
     */
    private $estudiante;


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
     * Set curso
     *
     * @param integer $curso
     * @return PnpReconocimientoSaberes
     */
    public function setCurso($curso)
    {
        $this->curso = $curso;
    
        return $this;
    }

    /**
     * Get curso
     *
     * @return integer 
     */
    public function getCurso()
    {
        return $this->curso;
    }

    /**
     * Set homologado
     *
     * @param boolean $homologado
     * @return PnpReconocimientoSaberes
     */
    public function setHomologado($homologado)
    {
        $this->homologado = $homologado;
    
        return $this;
    }

    /**
     * Get homologado
     *
     * @return boolean 
     */
    public function getHomologado()
    {
        return $this->homologado;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     * @return PnpReconocimientoSaberes
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;
    
        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime 
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * Set fechaHomologacion
     *
     * @param \DateTime $fechaHomologacion
     * @return PnpReconocimientoSaberes
     */
    public function setFechaHomologacion($fechaHomologacion)
    {
        $this->fechaHomologacion = $fechaHomologacion;
    
        return $this;
    }

    /**
     * Get fechaHomologacion
     *
     * @return \DateTime 
     */
    public function getFechaHomologacion()
    {
        return $this->fechaHomologacion;
    }

    /**
     * Set usuarioHomologado
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioHomologado
     * @return PnpReconocimientoSaberes
     */
    public function setUsuarioHomologado(\Sie\AppWebBundle\Entity\Usuario $usuarioHomologado = null)
    {
        $this->usuarioHomologado = $usuarioHomologado;
    
        return $this;
    }

    /**
     * Get usuarioHomologado
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioHomologado()
    {
        return $this->usuarioHomologado;
    }

    /**
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return PnpReconocimientoSaberes
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return PnpReconocimientoSaberes
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
     * Set estudiante
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $estudiante
     * @return PnpReconocimientoSaberes
     */
    public function setEstudiante(\Sie\AppWebBundle\Entity\Estudiante $estudiante = null)
    {
        $this->estudiante = $estudiante;
    
        return $this;
    }

    /**
     * Get estudiante
     *
     * @return \Sie\AppWebBundle\Entity\Estudiante 
     */
    public function getEstudiante()
    {
        return $this->estudiante;
    }
}
