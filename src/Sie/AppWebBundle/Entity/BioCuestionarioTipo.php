<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BioCuestionarioTipo
 */
class BioCuestionarioTipo
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
     * @var \Sie\AppWebBundle\Entity\BioPreguntasTipo
     */
    private $bioPreguntasTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\BioClasificadorPreguntaTipo
     */
    private $bioClasificadorPreguntaTipo;


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
     * @return BioCuestionarioTipo
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
     * Set bioPreguntasTipo
     *
     * @param \Sie\AppWebBundle\Entity\BioPreguntasTipo $bioPreguntasTipo
     * @return BioCuestionarioTipo
     */
    public function setBioPreguntasTipo(\Sie\AppWebBundle\Entity\BioPreguntasTipo $bioPreguntasTipo = null)
    {
        $this->bioPreguntasTipo = $bioPreguntasTipo;
    
        return $this;
    }

    /**
     * Get bioPreguntasTipo
     *
     * @return \Sie\AppWebBundle\Entity\BioPreguntasTipo 
     */
    public function getBioPreguntasTipo()
    {
        return $this->bioPreguntasTipo;
    }

    /**
     * Set bioClasificadorPreguntaTipo
     *
     * @param \Sie\AppWebBundle\Entity\BioClasificadorPreguntaTipo $bioClasificadorPreguntaTipo
     * @return BioCuestionarioTipo
     */
    public function setBioClasificadorPreguntaTipo(\Sie\AppWebBundle\Entity\BioClasificadorPreguntaTipo $bioClasificadorPreguntaTipo = null)
    {
        $this->bioClasificadorPreguntaTipo = $bioClasificadorPreguntaTipo;
    
        return $this;
    }

    /**
     * Get bioClasificadorPreguntaTipo
     *
     * @return \Sie\AppWebBundle\Entity\BioClasificadorPreguntaTipo 
     */
    public function getBioClasificadorPreguntaTipo()
    {
        return $this->bioClasificadorPreguntaTipo;
    }
}
