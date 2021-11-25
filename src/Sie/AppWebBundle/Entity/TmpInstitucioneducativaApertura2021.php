<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TmpInstitucioneducativaApertura2021
 */
class TmpInstitucioneducativaApertura2021
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $institucioneducativaId;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;


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
     * Set institucioneducativaId
     *
     * @param integer $institucioneducativaId
     * @return TmpInstitucioneducativaApertura2021
     */
    public function setInstitucioneducativaId($institucioneducativaId)
    {
        $this->institucioneducativaId = $institucioneducativaId;
    
        return $this;
    }

    /**
     * Get institucioneducativaId
     *
     * @return integer 
     */
    public function getInstitucioneducativaId()
    {
        return $this->institucioneducativaId;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TmpInstitucioneducativaApertura2021
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }
}
