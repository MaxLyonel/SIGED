<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpEquipoEstudianteInscripcionJuegos
 */
class JdpEquipoEstudianteInscripcionJuegos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $equipoId;

    /**
     * @var string
     */
    private $equipoNombre;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpEstudianteInscripcionJuegos
     */
    private $estudianteInscripcionJuegos;


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
     * Set equipoId
     *
     * @param integer $equipoId
     * @return JdpEquipoEstudianteInscripcionJuegos
     */
    public function setEquipoId($equipoId)
    {
        $this->equipoId = $equipoId;
    
        return $this;
    }

    /**
     * Get equipoId
     *
     * @return integer 
     */
    public function getEquipoId()
    {
        return $this->equipoId;
    }

    /**
     * Set equipoNombre
     *
     * @param string $equipoNombre
     * @return JdpEquipoEstudianteInscripcionJuegos
     */
    public function setEquipoNombre($equipoNombre)
    {
        $this->equipoNombre = $equipoNombre;
    
        return $this;
    }

    /**
     * Get equipoNombre
     *
     * @return string 
     */
    public function getEquipoNombre()
    {
        return $this->equipoNombre;
    }

    /**
     * Set estudianteInscripcionJuegos
     *
     * @param \Sie\AppWebBundle\Entity\JdpEstudianteInscripcionJuegos $estudianteInscripcionJuegos
     * @return JdpEquipoEstudianteInscripcionJuegos
     */
    public function setEstudianteInscripcionJuegos(\Sie\AppWebBundle\Entity\JdpEstudianteInscripcionJuegos $estudianteInscripcionJuegos = null)
    {
        $this->estudianteInscripcionJuegos = $estudianteInscripcionJuegos;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionJuegos
     *
     * @return \Sie\AppWebBundle\Entity\JdpEstudianteInscripcionJuegos 
     */
    public function getEstudianteInscripcionJuegos()
    {
        return $this->estudianteInscripcionJuegos;
    }
}
