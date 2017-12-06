<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaOperativoLogTipo
 */
class InstitucioneducativaOperativoLogTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $operativoLog;


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
     * Set operativoLog
     *
     * @param string $operativoLog
     * @return InstitucioneducativaOperativoLogTipo
     */
    public function setOperativoLog($operativoLog)
    {
        $this->operativoLog = $operativoLog;
    
        return $this;
    }

    /**
     * Get operativoLog
     *
     * @return string 
     */
    public function getOperativoLog()
    {
        return $this->operativoLog;
    }
}
