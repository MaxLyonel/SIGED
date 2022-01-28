<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UploadFileControl
 */
class UploadFileControl
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
     * @var string
     */
    private $version;

    /**
     * @var boolean
     */
    private $estadoFile;

    /**
     * @var string
     */
    private $gestion;

    /**
     * @var string
     */
    private $distrito;

    /**
     * @var string
     */
    private $path;

    /**
     * @var \DateTime
     */
    private $dateUpload;

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
     * @return UploadFileControl
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
     * @return UploadFileControl
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
     * @return UploadFileControl
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
     * Set version
     *
     * @param string $version
     * @return UploadFileControl
     */
    public function setVersion($version)
    {
        $this->version = $version;
    
        return $this;
    }

    /**
     * Get version
     *
     * @return string 
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set estadoFile
     *
     * @param boolean $estadoFile
     * @return UploadFileControl
     */
    public function setEstadoFile($estadoFile)
    {
        $this->estadoFile = $estadoFile;
    
        return $this;
    }

    /**
     * Get estadoFile
     *
     * @return boolean 
     */
    public function getEstadoFile()
    {
        return $this->estadoFile;
    }

    /**
     * Set gestion
     *
     * @param string $gestion
     * @return UploadFileControl
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
     * Set distrito
     *
     * @param string $distrito
     * @return UploadFileControl
     */
    public function setDistrito($distrito)
    {
        $this->distrito = $distrito;
    
        return $this;
    }

    /**
     * Get distrito
     *
     * @return string 
     */
    public function getDistrito()
    {
        return $this->distrito;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return UploadFileControl
     */
    public function setPath($path)
    {
        $this->path = $path;
    
        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set dateUpload
     *
     * @param \DateTime $dateUpload
     * @return UploadFileControl
     */
    public function setDateUpload($dateUpload)
    {
        $this->dateUpload = $dateUpload;
    
        return $this;
    }

    /**
     * Get dateUpload
     *
     * @return \DateTime 
     */
    public function getDateUpload()
    {
        return $this->dateUpload;
    }

    /**
     * Set remoteAddr
     *
     * @param string $remoteAddr
     * @return UploadFileControl
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
     * @return UploadFileControl
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
