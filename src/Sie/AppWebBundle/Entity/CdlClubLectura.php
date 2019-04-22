<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CdlClubLectura
 */
class CdlClubLectura
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombreClub;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\MaestroInscripcion
     */
    private $maestroinscripcion;


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
     * Set nombreClub
     *
     * @param string $nombreClub
     * @return CdlClubLectura
     */
    public function setNombreClub($nombreClub)
    {
        $this->nombreClub = $nombreClub;
    
        return $this;
    }

    /**
     * Get nombreClub
     *
     * @return string 
     */
    public function getNombreClub()
    {
        return $this->nombreClub;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return CdlClubLectura
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
     * Set maestroinscripcion
     *
     * @param \Sie\AppWebBundle\Entity\MaestroInscripcion $maestroinscripcion
     * @return CdlClubLectura
     */
    public function setMaestroinscripcion(\Sie\AppWebBundle\Entity\MaestroInscripcion $maestroinscripcion = null)
    {
        $this->maestroinscripcion = $maestroinscripcion;
    
        return $this;
    }

    /**
     * Get maestroinscripcion
     *
     * @return \Sie\AppWebBundle\Entity\MaestroInscripcion 
     */
    public function getMaestroinscripcion()
    {
        return $this->maestroinscripcion;
    }
}
