<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoRegHablaNinezTipo
 */
class EstudianteInscripcionSocioeconomicoRegHablaNinezTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $hablaNiñez;

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
     * Set hablaNiñez
     *
     * @param string $hablaNiñez
     * @return EstudianteInscripcionSocioeconomicoRegHablaNinezTipo
     */
    public function setHablaNiñez($hablaNiñez)
    {
        $this->hablaNiñez = $hablaNiñez;
    
        return $this;
    }

    /**
     * Get hablaNiñez
     *
     * @return string 
     */
    public function getHablaNiñez()
    {
        return $this->hablaNiñez;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionSocioeconomicoRegHablaNinezTipo
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
