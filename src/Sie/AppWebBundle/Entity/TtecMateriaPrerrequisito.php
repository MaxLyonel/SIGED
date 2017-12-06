<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecMateriaPrerrequisito
 */
class TtecMateriaPrerrequisito
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
     * @var \Sie\AppWebBundle\Entity\TtecMateriaTipo
     */
    private $ttecMateriaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecMateriaTipo
     */
    private $ttecMateriaTipoPre;


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
     * @return TtecMateriaPrerrequisito
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
     * Set ttecMateriaTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecMateriaTipo $ttecMateriaTipo
     * @return TtecMateriaPrerrequisito
     */
    public function setTtecMateriaTipo(\Sie\AppWebBundle\Entity\TtecMateriaTipo $ttecMateriaTipo = null)
    {
        $this->ttecMateriaTipo = $ttecMateriaTipo;
    
        return $this;
    }

    /**
     * Get ttecMateriaTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecMateriaTipo 
     */
    public function getTtecMateriaTipo()
    {
        return $this->ttecMateriaTipo;
    }

    /**
     * Set ttecMateriaTipoPre
     *
     * @param \Sie\AppWebBundle\Entity\TtecMateriaTipo $ttecMateriaTipoPre
     * @return TtecMateriaPrerrequisito
     */
    public function setTtecMateriaTipoPre(\Sie\AppWebBundle\Entity\TtecMateriaTipo $ttecMateriaTipoPre = null)
    {
        $this->ttecMateriaTipoPre = $ttecMateriaTipoPre;
    
        return $this;
    }

    /**
     * Get ttecMateriaTipoPre
     *
     * @return \Sie\AppWebBundle\Entity\TtecMateriaTipo 
     */
    public function getTtecMateriaTipoPre()
    {
        return $this->ttecMateriaTipoPre;
    }
}
