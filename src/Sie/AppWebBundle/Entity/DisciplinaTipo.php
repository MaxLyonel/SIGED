<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DisciplinaTipo
 */
class DisciplinaTipo
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
     * @var \Sie\AppWebBundle\Entity\NivelTipo
     */
    private $nivelTipo;
    
    
    public function __toString() {
        return $this->disciplina;
    }


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
     * @return DisciplinaTipo
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
     * @return DisciplinaTipo
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
     * @return DisciplinaTipo
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
     * Set nivelTipo
     *
     * @param \Sie\AppWebBundle\Entity\NivelTipo $nivelTipo
     * @return DisciplinaTipo
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
}
