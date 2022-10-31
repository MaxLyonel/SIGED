<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CdlIntegrantes
 */
class CdlIntegrantes
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\CdlClubLectura
     */
    private $cdlClubLectura;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;


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
     * Set obs
     *
     * @param string $obs
     * @return CdlIntegrantes
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
     * Set cdlClubLectura
     *
     * @param \Sie\AppWebBundle\Entity\CdlClubLectura $cdlClubLectura
     * @return CdlIntegrantes
     */
    public function setCdlClubLectura(\Sie\AppWebBundle\Entity\CdlClubLectura $cdlClubLectura = null)
    {
        $this->cdlClubLectura = $cdlClubLectura;
    
        return $this;
    }

    /**
     * Get cdlClubLectura
     *
     * @return \Sie\AppWebBundle\Entity\CdlClubLectura 
     */
    public function getCdlClubLectura()
    {
        return $this->cdlClubLectura;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return CdlIntegrantes
     */
    public function setEstudianteInscripcion(\Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion = null)
    {
        $this->estudianteInscripcion = $estudianteInscripcion;
    
        return $this;
    }

    /**
     * Get estudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcion 
     */
    public function getEstudianteInscripcion()
    {
        return $this->estudianteInscripcion;
    }
}
