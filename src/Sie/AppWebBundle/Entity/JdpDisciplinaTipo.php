<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpDisciplinaTipo
 */
class JdpDisciplinaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $disciplina;

    /**
     * @var boolean
     */
    private $estado;

    /**
     * @var string
     */
    private $cantidad;

    /**
     * @var integer
     */
    private $cantidadMaestro;

    /**
     * @var integer
     */
    private $cantidadPadre;

    /**
     * @var \Sie\AppWebBundle\Entity\NivelTipo
     */
    private $nivelTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpDisciplinaParticipacionTipo
     */
    private $disciplinaParticipacionTipo;


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
     * Set disciplina
     *
     * @param string $disciplina
     * @return JdpDisciplinaTipo
     */
    public function setDisciplina($disciplina)
    {
        $this->disciplina = $disciplina;
    
        return $this;
    }

    /**
     * Get disciplina
     *
     * @return string 
     */
    public function getDisciplina()
    {
        return $this->disciplina;
    }

    /**
     * Set estado
     *
     * @param boolean $estado
     * @return JdpDisciplinaTipo
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return boolean 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set cantidad
     *
     * @param string $cantidad
     * @return JdpDisciplinaTipo
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;
    
        return $this;
    }

    /**
     * Get cantidad
     *
     * @return string 
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Set cantidadMaestro
     *
     * @param integer $cantidadMaestro
     * @return JdpDisciplinaTipo
     */
    public function setCantidadMaestro($cantidadMaestro)
    {
        $this->cantidadMaestro = $cantidadMaestro;
    
        return $this;
    }

    /**
     * Get cantidadMaestro
     *
     * @return integer 
     */
    public function getCantidadMaestro()
    {
        return $this->cantidadMaestro;
    }

    /**
     * Set cantidadPadre
     *
     * @param integer $cantidadPadre
     * @return JdpDisciplinaTipo
     */
    public function setCantidadPadre($cantidadPadre)
    {
        $this->cantidadPadre = $cantidadPadre;
    
        return $this;
    }

    /**
     * Get cantidadPadre
     *
     * @return integer 
     */
    public function getCantidadPadre()
    {
        return $this->cantidadPadre;
    }

    /**
     * Set nivelTipo
     *
     * @param \Sie\AppWebBundle\Entity\NivelTipo $nivelTipo
     * @return JdpDisciplinaTipo
     */
    public function setNivelTipo(\Sie\AppWebBundle\Entity\NivelTipo $nivelTipo = null)
    {
        $this->nivelTipo = $nivelTipo;
    
        return $this;
    }

    /**
     * Get nivelTipo
     *
     * @return \Sie\AppWebBundle\Entity\NivelTipo 
     */
    public function getNivelTipo()
    {
        return $this->nivelTipo;
    }

    /**
     * Set disciplinaParticipacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpDisciplinaParticipacionTipo $disciplinaParticipacionTipo
     * @return JdpDisciplinaTipo
     */
    public function setDisciplinaParticipacionTipo(\Sie\AppWebBundle\Entity\JdpDisciplinaParticipacionTipo $disciplinaParticipacionTipo = null)
    {
        $this->disciplinaParticipacionTipo = $disciplinaParticipacionTipo;
    
        return $this;
    }

    /**
     * Get disciplinaParticipacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpDisciplinaParticipacionTipo 
     */
    public function getDisciplinaParticipacionTipo()
    {
        return $this->disciplinaParticipacionTipo;
    }
}
