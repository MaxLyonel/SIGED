<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteDiplomatico
 */
class EstudianteDiplomatico
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nroDocumento;

    /**
     * @var string
     */
    private $embajada;

    /**
     * @var string
     */
    private $pasaporte;

    /**
     * @var string
     */
    private $cargo;

    /**
     * @var \DateTime
     */
    private $vigencia;

    /**
     * @var string
     */
    private $categoriaDocumento;

    /**
     * @var string
     */
    private $documentoPath;

    /**
     * @var integer
     */
    private $createdUserId;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var integer
     */
    private $updateUserId;

    /**
     * @var \DateTime
     */
    private $updateAt;

    /**
     * @var \Sie\AppWebBundle\Entity\PaisTipo
     */
    private $paisTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Estudiante
     */
    private $estudiante;


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
     * Set nroDocumento
     *
     * @param string $nroDocumento
     * @return EstudianteDiplomatico
     */
    public function setNroDocumento($nroDocumento)
    {
        $this->nroDocumento = $nroDocumento;
    
        return $this;
    }

    /**
     * Get nroDocumento
     *
     * @return string 
     */
    public function getNroDocumento()
    {
        return $this->nroDocumento;
    }

    /**
     * Set embajada
     *
     * @param string $embajada
     * @return EstudianteDiplomatico
     */
    public function setEmbajada($embajada)
    {
        $this->embajada = $embajada;
    
        return $this;
    }

    /**
     * Get embajada
     *
     * @return string 
     */
    public function getEmbajada()
    {
        return $this->embajada;
    }

    /**
     * Set pasaporte
     *
     * @param string $pasaporte
     * @return EstudianteDiplomatico
     */
    public function setPasaporte($pasaporte)
    {
        $this->pasaporte = $pasaporte;
    
        return $this;
    }

    /**
     * Get pasaporte
     *
     * @return string 
     */
    public function getPasaporte()
    {
        return $this->pasaporte;
    }

    /**
     * Set cargo
     *
     * @param string $cargo
     * @return EstudianteDiplomatico
     */
    public function setCargo($cargo)
    {
        $this->cargo = $cargo;
    
        return $this;
    }

    /**
     * Get cargo
     *
     * @return string 
     */
    public function getCargo()
    {
        return $this->cargo;
    }

    /**
     * Set vigencia
     *
     * @param \DateTime $vigencia
     * @return EstudianteDiplomatico
     */
    public function setVigencia($vigencia)
    {
        $this->vigencia = $vigencia;
    
        return $this;
    }

    /**
     * Get vigencia
     *
     * @return \DateTime 
     */
    public function getVigencia()
    {
        return $this->vigencia;
    }

    /**
     * Set categoriaDocumento
     *
     * @param string $categoriaDocumento
     * @return EstudianteDiplomatico
     */
    public function setCategoriaDocumento($categoriaDocumento)
    {
        $this->categoriaDocumento = $categoriaDocumento;
    
        return $this;
    }

    /**
     * Get categoriaDocumento
     *
     * @return string 
     */
    public function getCategoriaDocumento()
    {
        return $this->categoriaDocumento;
    }

    /**
     * Set documentoPath
     *
     * @param string $documentoPath
     * @return EstudianteDiplomatico
     */
    public function setDocumentoPath($documentoPath)
    {
        $this->documentoPath = $documentoPath;
    
        return $this;
    }
    

    /**
     * Get documentoPath
     *
     * @return string 
     */
    public function getDocumentoPath()
    {
        return $this->documentoPath;
    }

    /**
     * Set createdUserId
     *
     * @param integer $createdUserId
     * @return EstudianteDiplomatico
     */
    public function setCreatedUserId($createdUserId)
    {
        $this->createdUserId = $createdUserId;
    
        return $this;
    }

    /**
     * Get createdUserId
     *
     * @return integer 
     */
    public function getCreatedUserId()
    {
        return $this->createdUserId;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return EstudianteDiplomatico
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updateUserId
     *
     * @param integer $updateUserId
     * @return EstudianteDiplomatico
     */
    public function setUpdateUserId($updateUserId)
    {
        $this->updateUserId = $updateUserId;
    
        return $this;
    }

    /**
     * Get updateUserId
     *
     * @return integer 
     */
    public function getUpdateUserId()
    {
        return $this->updateUserId;
    }

    /**
     * Set updateAt
     *
     * @param \DateTime $updateAt
     * @return EstudianteDiplomatico
     */
    public function setUpdateAt($updateAt)
    {
        $this->updateAt = $updateAt;
    
        return $this;
    }

    /**
     * Get updateAt
     *
     * @return \DateTime 
     */
    public function getUpdateAt()
    {
        return $this->updateAt;
    }

    /**
     * Set paisTipo
     *
     * @param \Sie\AppWebBundle\Entity\PaisTipo $paisTipo
     * @return EstudianteDiplomatico
     */
    public function setPaisTipo(\Sie\AppWebBundle\Entity\PaisTipo $paisTipo = null)
    {
        $this->paisTipo = $paisTipo;
    
        return $this;
    }

    /**
     * Get paisTipo
     *
     * @return \Sie\AppWebBundle\Entity\PaisTipo 
     */
    public function getPaisTipo()
    {
        return $this->paisTipo;
    }

    /**
     * Set estudiante
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $estudiante
     * @return EstudianteDiplomatico
     */
    public function setEstudiante(\Sie\AppWebBundle\Entity\Estudiante $estudiante = null)
    {
        $this->estudiante = $estudiante;
    
        return $this;
    }

    /**
     * Get estudiante
     *
     * @return \Sie\AppWebBundle\Entity\Estudiante 
     */
    public function getEstudiante()
    {
        return $this->estudiante;
    }
}
