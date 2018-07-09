<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimArchivoSubido
 */
class OlimArchivoSubido
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $pathArchivo;

    /**
     * @var string
     */
    private $nombreArchivo;

    /**
     * @var \DateTime
     */
    private $fechaSubida;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimEtapaTipo
     */
    private $olimEtapaTipo;


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
     * Set pathArchivo
     *
     * @param string $pathArchivo
     * @return OlimArchivoSubido
     */
    public function setPathArchivo($pathArchivo)
    {
        $this->pathArchivo = $pathArchivo;
    
        return $this;
    }

    /**
     * Get pathArchivo
     *
     * @return string 
     */
    public function getPathArchivo()
    {
        return $this->pathArchivo;
    }

    /**
     * Set nombreArchivo
     *
     * @param string $nombreArchivo
     * @return OlimArchivoSubido
     */
    public function setNombreArchivo($nombreArchivo)
    {
        $this->nombreArchivo = $nombreArchivo;
    
        return $this;
    }

    /**
     * Get nombreArchivo
     *
     * @return string 
     */
    public function getNombreArchivo()
    {
        return $this->nombreArchivo;
    }

    /**
     * Set fechaSubida
     *
     * @param \DateTime $fechaSubida
     * @return OlimArchivoSubido
     */
    public function setFechaSubida($fechaSubida)
    {
        $this->fechaSubida = $fechaSubida;
    
        return $this;
    }

    /**
     * Get fechaSubida
     *
     * @return \DateTime 
     */
    public function getFechaSubida()
    {
        return $this->fechaSubida;
    }

    /**
     * Set olimEtapaTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimEtapaTipo $olimEtapaTipo
     * @return OlimArchivoSubido
     */
    public function setOlimEtapaTipo(\Sie\AppWebBundle\Entity\OlimEtapaTipo $olimEtapaTipo = null)
    {
        $this->olimEtapaTipo = $olimEtapaTipo;
    
        return $this;
    }

    /**
     * Get olimEtapaTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimEtapaTipo 
     */
    public function getOlimEtapaTipo()
    {
        return $this->olimEtapaTipo;
    }
}
