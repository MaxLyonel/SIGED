<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BioPreguntasTipo
 */
class BioPreguntasTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $preguntas;

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
     * Set preguntas
     *
     * @param string $preguntas
     * @return BioPreguntasTipo
     */
    public function setPreguntas($preguntas)
    {
        $this->preguntas = $preguntas;
    
        return $this;
    }

    /**
     * Get preguntas
     *
     * @return string 
     */
    public function getPreguntas()
    {
        return $this->preguntas;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return BioPreguntasTipo
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
