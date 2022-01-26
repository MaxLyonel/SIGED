<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IdiomaorginarioTraduccion
 */
class IdiomaorginarioTraduccion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $titulocastellano;

    /**
     * @var string
     */
    private $titulooriginario;

    /**
     * @var integer
     */
    private $idiomaoriginarioTipoId;


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
     * Set titulocastellano
     *
     * @param string $titulocastellano
     * @return IdiomaorginarioTraduccion
     */
    public function setTitulocastellano($titulocastellano)
    {
        $this->titulocastellano = $titulocastellano;

        return $this;
    }

    /**
     * Get titulocastellano
     *
     * @return string 
     */
    public function getTitulocastellano()
    {
        return $this->titulocastellano;
    }

    /**
     * Set titulooriginario
     *
     * @param string $titulooriginario
     * @return IdiomaorginarioTraduccion
     */
    public function setTitulooriginario($titulooriginario)
    {
        $this->titulooriginario = $titulooriginario;

        return $this;
    }

    /**
     * Get titulooriginario
     *
     * @return string 
     */
    public function getTitulooriginario()
    {
        return $this->titulooriginario;
    }

    /**
     * Set idiomaoriginarioTipoId
     *
     * @param integer $idiomaoriginarioTipoId
     * @return IdiomaorginarioTraduccion
     */
    public function setIdiomaoriginarioTipoId($idiomaoriginarioTipoId)
    {
        $this->idiomaoriginarioTipoId = $idiomaoriginarioTipoId;

        return $this;
    }

    /**
     * Get idiomaoriginarioTipoId
     *
     * @return integer 
     */
    public function getIdiomaoriginarioTipoId()
    {
        return $this->idiomaoriginarioTipoId;
    }
}
