<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PnpSerialRude
 */
class PnpSerialRude
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $seqrude;


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
     * Set seqrude
     *
     * @param integer $seqrude
     * @return PnpSerialRude
     */
    public function setSeqrude($seqrude)
    {
        $this->seqrude = $seqrude;
    
        return $this;
    }

    /**
     * Get seqrude
     *
     * @return integer 
     */
    public function getSeqrude()
    {
        return $this->seqrude;
    }
}
