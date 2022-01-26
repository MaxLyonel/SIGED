<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoAltHabla
 */
class EstudianteInscripcionSocioeconomicoAltHabla
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
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltHablaTipo
     */
    private $estudianteInscripcionSocioeconomicoAltHablaTipo;


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
     * @return EstudianteInscripcionSocioeconomicoAltHabla
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
     * Set estudianteInscripcionSocioeconomicoAltHablaTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltHablaTipo $estudianteInscripcionSocioeconomicoAltHablaTipo
     * @return EstudianteInscripcionSocioeconomicoAltHabla
     */
    public function setEstudianteInscripcionSocioeconomicoAltHablaTipo(\Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltHablaTipo $estudianteInscripcionSocioeconomicoAltHablaTipo = null)
    {
        $this->estudianteInscripcionSocioeconomicoAltHablaTipo = $estudianteInscripcionSocioeconomicoAltHablaTipo;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionSocioeconomicoAltHablaTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltHablaTipo 
     */
    public function getEstudianteInscripcionSocioeconomicoAltHablaTipo()
    {
        return $this->estudianteInscripcionSocioeconomicoAltHablaTipo;
    }
}
