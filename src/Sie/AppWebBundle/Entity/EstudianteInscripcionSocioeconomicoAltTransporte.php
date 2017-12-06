<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoAltTransporte
 */
class EstudianteInscripcionSocioeconomicoAltTransporte
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
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltTransporteTipo
     */
    private $estudianteInscripcionSocioeconomicoAltTransporteTipo;


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
     * @return EstudianteInscripcionSocioeconomicoAltTransporte
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
     * Set estudianteInscripcionSocioeconomicoAltTransporteTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltTransporteTipo $estudianteInscripcionSocioeconomicoAltTransporteTipo
     * @return EstudianteInscripcionSocioeconomicoAltTransporte
     */
    public function setEstudianteInscripcionSocioeconomicoAltTransporteTipo(\Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltTransporteTipo $estudianteInscripcionSocioeconomicoAltTransporteTipo = null)
    {
        $this->estudianteInscripcionSocioeconomicoAltTransporteTipo = $estudianteInscripcionSocioeconomicoAltTransporteTipo;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionSocioeconomicoAltTransporteTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoAltTransporteTipo 
     */
    public function getEstudianteInscripcionSocioeconomicoAltTransporteTipo()
    {
        return $this->estudianteInscripcionSocioeconomicoAltTransporteTipo;
    }
}
