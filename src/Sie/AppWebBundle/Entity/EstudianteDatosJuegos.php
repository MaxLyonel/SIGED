<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteDatosJuegos
 */
class EstudianteDatosJuegos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $foto;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionJuegos
     */
    private $estudianteInscripcionJuegosI;


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
     * Set foto
     *
     * @param string $foto
     * @return EstudianteDatosJuegos
     */
    public function setFoto($foto)
    {
        $this->foto = $foto;

        return $this;
    }

    /**
     * Get foto
     *
     * @return string 
     */
    public function getFoto()
    {
        return $this->foto;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteDatosJuegos
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
     * Set estudianteInscripcionJuegosI
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionJuegos $estudianteInscripcionJuegosI
     * @return EstudianteDatosJuegos
     */
    public function setEstudianteInscripcionJuegosI(\Sie\AppWebBundle\Entity\EstudianteInscripcionJuegos $estudianteInscripcionJuegosI = null)
    {
        $this->estudianteInscripcionJuegosI = $estudianteInscripcionJuegosI;

        return $this;
    }

    /**
     * Get estudianteInscripcionJuegosI
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionJuegos 
     */
    public function getEstudianteInscripcionJuegosI()
    {
        return $this->estudianteInscripcionJuegosI;
    }
}
