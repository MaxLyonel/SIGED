<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BioCuestionarioBrigadaTipo
 */
class BioCuestionarioBrigadaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $bioClasificadorPreguntaBrig;

    /**
     * @var string
     */
    private $bioPreguntasBrigada;

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
     * Set bioClasificadorPreguntaBrig
     *
     * @param string $bioClasificadorPreguntaBrig
     * @return BioCuestionarioBrigadaTipo
     */
    public function setBioClasificadorPreguntaBrig($bioClasificadorPreguntaBrig)
    {
        $this->bioClasificadorPreguntaBrig = $bioClasificadorPreguntaBrig;
    
        return $this;
    }

    /**
     * Get bioClasificadorPreguntaBrig
     *
     * @return string 
     */
    public function getBioClasificadorPreguntaBrig()
    {
        return $this->bioClasificadorPreguntaBrig;
    }

    /**
     * Set bioPreguntasBrigada
     *
     * @param string $bioPreguntasBrigada
     * @return BioCuestionarioBrigadaTipo
     */
    public function setBioPreguntasBrigada($bioPreguntasBrigada)
    {
        $this->bioPreguntasBrigada = $bioPreguntasBrigada;
    
        return $this;
    }

    /**
     * Get bioPreguntasBrigada
     *
     * @return string 
     */
    public function getBioPreguntasBrigada()
    {
        return $this->bioPreguntasBrigada;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return BioCuestionarioBrigadaTipo
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
