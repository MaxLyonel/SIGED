<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoRegInternetTipo
 */
class EstudianteInscripcionSocioeconomicoRegInternetTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $accesointernetTipo;

    /**
     * @var string
     */
    private $obs;


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
     * Set accesointernetTipo
     *
     * @param integer $accesointernetTipo
     * @return EstudianteInscripcionSocioeconomicoRegInternetTipo
     */
    public function setAccesointernetTipo($accesointernetTipo)
    {
        $this->accesointernetTipo = $accesointernetTipo;
    
        return $this;
    }

    /**
     * Get accesointernetTipo
     *
     * @return integer 
     */
    public function getAccesointernetTipo()
    {
        return $this->accesointernetTipo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionSocioeconomicoRegInternetTipo
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
}
