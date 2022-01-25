<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ControlArchivosBajada
 */
class ControlArchivosBajada
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $codUe;

    /**
     * @var integer
     */
    private $bimestre;

    /**
     * @var integer
     */
    private $operativo;

    /**
     * @var boolean
     */
    private $estadoDescarga;

    /**
     * @var string
     */
    private $gestion;

    /**
     * @var \DateTime
     */
    private $dateDownload;

    /**
     * @var string
     */
    private $remoteAddr;

    /**
     * @var string
     */
    private $userAgent;


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
     * Set codUe
     *
     * @param integer $codUe
     * @return ControlArchivosBajada
     */
    public function setCodUe($codUe)
    {
        $this->codUe = $codUe;
    
        return $this;
    }

    /**
     * Get codUe
     *
     * @return integer 
     */
    public function getCodUe()
    {
        return $this->codUe;
    }

    /**
     * Set bimestre
     *
     * @param integer $bimestre
     * @return ControlArchivosBajada
     */
    public function setBimestre($bimestre)
    {
        $this->bimestre = $bimestre;
    
        return $this;
    }

    /**
     * Get bimestre
     *
     * @return integer 
     */
    public function getBimestre()
    {
        return $this->bimestre;
    }

    /**
     * Set operativo
     *
     * @param integer $operativo
     * @return ControlArchivosBajada
     */
    public function setOperativo($operativo)
    {
        $this->operativo = $operativo;
    
        return $this;
    }

    /**
     * Get operativo
     *
     * @return integer 
     */
    public function getOperativo()
    {
        return $this->operativo;
    }

    /**
     * Set estadoDescarga
     *
     * @param boolean $estadoDescarga
     * @return ControlArchivosBajada
     */
    public function setEstadoDescarga($estadoDescarga)
    {
        $this->estadoDescarga = $estadoDescarga;
    
        return $this;
    }

    /**
     * Get estadoDescarga
     *
     * @return boolean 
     */
    public function getEstadoDescarga()
    {
        return $this->estadoDescarga;
    }

    /**
     * Set gestion
     *
     * @param string $gestion
     * @return ControlArchivosBajada
     */
    public function setGestion($gestion)
    {
        $this->gestion = $gestion;
    
        return $this;
    }

    /**
     * Get gestion
     *
     * @return string 
     */
    public function getGestion()
    {
        return $this->gestion;
    }

    /**
     * Set dateDownload
     *
     * @param \DateTime $dateDownload
     * @return ControlArchivosBajada
     */
    public function setDateDownload($dateDownload)
    {
        $this->dateDownload = $dateDownload;
    
        return $this;
    }

    /**
     * Get dateDownload
     *
     * @return \DateTime 
     */
    public function getDateDownload()
    {
        return $this->dateDownload;
    }

    /**
     * Set remoteAddr
     *
     * @param string $remoteAddr
     * @return ControlArchivosBajada
     */
    public function setRemoteAddr($remoteAddr)
    {
        $this->remoteAddr = $remoteAddr;
    
        return $this;
    }

    /**
     * Get remoteAddr
     *
     * @return string 
     */
    public function getRemoteAddr()
    {
        return $this->remoteAddr;
    }

    /**
     * Set userAgent
     *
     * @param string $userAgent
     * @return ControlArchivosBajada
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    
        return $this;
    }

    /**
     * Get userAgent
     *
     * @return string 
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }
}
