<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SuperiorAcreditacionTipo
 */
class SuperiorAcreditacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $codigo;

    /**
     * @var string
     */
    private $acreditacion;

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
     * Set codigo
     *
     * @param integer $codigo
     * @return SuperiorAcreditacionTipo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return integer 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set acreditacion
     *
     * @param string $acreditacion
     * @return SuperiorAcreditacionTipo
     */
    public function setAcreditacion($acreditacion)
    {
        $this->acreditacion = $acreditacion;
    
        return $this;
    }

    /**
     * Get acreditacion
     *
     * @return string 
     */
    public function getAcreditacion()
    {
        return $this->acreditacion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return SuperiorAcreditacionTipo
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
