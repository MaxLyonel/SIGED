<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaPlataforma
 */
class InstitucioneducativaPlataforma
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $plataforma;

    /**
     * @var string
     */
    private $dominio;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string
     */
    private $celDirector;

    /**
     * @var string
     */
    private $celResponsable;

    /**
     * @var integer
     */
    private $estado;

    /**
     * @var string
     */
    private $documento;

    /**
     * @var string
     */
    private $json;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $directorPersona;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $responsablePersona;


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
     * Set plataforma
     *
     * @param integer $plataforma
     * @return InstitucioneducativaPlataforma
     */
    public function setPlataforma($plataforma)
    {
        $this->plataforma = $plataforma;
    
        return $this;
    }

    /**
     * Get plataforma
     *
     * @return integer 
     */
    public function getPlataforma()
    {
        return $this->plataforma;
    }

    /**
     * Set dominio
     *
     * @param string $dominio
     * @return InstitucioneducativaPlataforma
     */
    public function setDominio($dominio)
    {
        $this->dominio = $dominio;
    
        return $this;
    }

    /**
     * Get dominio
     *
     * @return string 
     */
    public function getDominio()
    {
        return $this->dominio;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return InstitucioneducativaPlataforma
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    
        return $this;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set celDirector
     *
     * @param string $celDirector
     * @return InstitucioneducativaPlataforma
     */
    public function setCelDirector($celDirector)
    {
        $this->celDirector = $celDirector;
    
        return $this;
    }

    /**
     * Get celDirector
     *
     * @return string 
     */
    public function getCelDirector()
    {
        return $this->celDirector;
    }

    /**
     * Set celResponsable
     *
     * @param string $celResponsable
     * @return InstitucioneducativaPlataforma
     */
    public function setCelResponsable($celResponsable)
    {
        $this->celResponsable = $celResponsable;
    
        return $this;
    }

    /**
     * Get celResponsable
     *
     * @return string 
     */
    public function getCelResponsable()
    {
        return $this->celResponsable;
    }

    /**
     * Set estado
     *
     * @param integer $estado
     * @return InstitucioneducativaPlataforma
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return integer 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set documento
     *
     * @param string $documento
     * @return InstitucioneducativaPlataforma
     */
    public function setDocumento($documento)
    {
        $this->documento = $documento;
    
        return $this;
    }

    /**
     * Get documento
     *
     * @return string 
     */
    public function getDocumento()
    {
        return $this->documento;
    }

    /**
     * Set json
     *
     * @param string $json
     * @return InstitucioneducativaPlataforma
     */
    public function setJson($json)
    {
        $this->json = $json;
    
        return $this;
    }

    /**
     * Get json
     *
     * @return string 
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return InstitucioneducativaPlataforma
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

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return InstitucioneducativaPlataforma
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime 
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaPlataforma
     */
    public function setInstitucioneducativa(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa = null)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }

    /**
     * Set directorPersona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $directorPersona
     * @return InstitucioneducativaPlataforma
     */
    public function setDirectorPersona(\Sie\AppWebBundle\Entity\Persona $directorPersona = null)
    {
        $this->directorPersona = $directorPersona;
    
        return $this;
    }

    /**
     * Get directorPersona
     *
     * @return \Sie\AppWebBundle\Entity\Persona 
     */
    public function getDirectorPersona()
    {
        return $this->directorPersona;
    }

    /**
     * Set responsablePersona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $responsablePersona
     * @return InstitucioneducativaPlataforma
     */
    public function setResponsablePersona(\Sie\AppWebBundle\Entity\Persona $responsablePersona = null)
    {
        $this->responsablePersona = $responsablePersona;
    
        return $this;
    }

    /**
     * Get responsablePersona
     *
     * @return \Sie\AppWebBundle\Entity\Persona 
     */
    public function getResponsablePersona()
    {
        return $this->responsablePersona;
    }
}
