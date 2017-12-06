<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoAltAcceso
 */
class EstudianteInscripcionSocioeconomicoAltAcceso
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
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltAccesoTipo
     */
    private $estudianteInscripcionSocioeconomicoAltAccesoTipo;


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
     * @return EstudianteInscripcionSocioeconomicoAltAcceso
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
     * Set estudianteInscripcionSocioeconomicoAltAccesoTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltAccesoTipo $estudianteInscripcionSocioeconomicoAltAccesoTipo
     * @return EstudianteInscripcionSocioeconomicoAltAcceso
     */
    public function setEstudianteInscripcionSocioeconomicoAltAccesoTipo(\Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltAccesoTipo $estudianteInscripcionSocioeconomicoAltAccesoTipo = null)
    {
        $this->estudianteInscripcionSocioeconomicoAltAccesoTipo = $estudianteInscripcionSocioeconomicoAltAccesoTipo;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionSocioeconomicoAltAccesoTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltAccesoTipo 
     */
    public function getEstudianteInscripcionSocioeconomicoAltAccesoTipo()
    {
        return $this->estudianteInscripcionSocioeconomicoAltAccesoTipo;
    }
}
