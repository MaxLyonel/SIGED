<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoAltOcupacion
 */
class EstudianteInscripcionSocioeconomicoAltOcupacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAlternativa
     */
    private $estudianteInscripcionSocioeconomicoAlternativa;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltOcupacionTipo
     */
    private $estudianteInscripcionSocioeconomicoAltOcupacionTipo;


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
     * Set estudianteInscripcionSocioeconomicoAlternativa
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAlternativa $estudianteInscripcionSocioeconomicoAlternativa
     * @return EstudianteInscripcionSocioeconomicoAltOcupacion
     */
    public function setEstudianteInscripcionSocioeconomicoAlternativa(\Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAlternativa $estudianteInscripcionSocioeconomicoAlternativa = null)
    {
        $this->estudianteInscripcionSocioeconomicoAlternativa = $estudianteInscripcionSocioeconomicoAlternativa;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionSocioeconomicoAlternativa
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAlternativa 
     */
    public function getEstudianteInscripcionSocioeconomicoAlternativa()
    {
        return $this->estudianteInscripcionSocioeconomicoAlternativa;
    }

    /**
     * Set estudianteInscripcionSocioeconomicoAltOcupacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltOcupacionTipo $estudianteInscripcionSocioeconomicoAltOcupacionTipo
     * @return EstudianteInscripcionSocioeconomicoAltOcupacion
     */
    public function setEstudianteInscripcionSocioeconomicoAltOcupacionTipo(\Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltOcupacionTipo $estudianteInscripcionSocioeconomicoAltOcupacionTipo = null)
    {
        $this->estudianteInscripcionSocioeconomicoAltOcupacionTipo = $estudianteInscripcionSocioeconomicoAltOcupacionTipo;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionSocioeconomicoAltOcupacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltOcupacionTipo 
     */
    public function getEstudianteInscripcionSocioeconomicoAltOcupacionTipo()
    {
        return $this->estudianteInscripcionSocioeconomicoAltOcupacionTipo;
    }
}
