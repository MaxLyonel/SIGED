<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CenEstudianteInscripcionCenso
 */
class CenEstudianteInscripcionCenso
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $documentoPath;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $celnumero;

    /**
     * @var boolean
     */
    private $esVigente;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\ApoderadoInscripcion
     */
    private $apoderadoInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;


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
     * Set documentoPath
     *
     * @param string $documentoPath
     * @return CenEstudianteInscripcionCenso
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
     * Set email
     *
     * @param string $email
     * @return CenEstudianteInscripcionCenso
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set celnumero
     *
     * @param string $celnumero
     * @return CenEstudianteInscripcionCenso
     */
    public function setCelnumero($celnumero)
    {
        $this->celnumero = $celnumero;
    
        return $this;
    }

    /**
     * Get celnumero
     *
     * @return string 
     */
    public function getCelnumero()
    {
        return $this->celnumero;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return CenEstudianteInscripcionCenso
     */
    public function setEsVigente($esVigente)
    {
        $this->esVigente = $esVigente;
    
        return $this;
    }

    /**
     * Get esVigente
     *
     * @return boolean 
     */
    public function getEsVigente()
    {
        return $this->esVigente;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return CenEstudianteInscripcionCenso
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
     * @return CenEstudianteInscripcionCenso
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
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return CenEstudianteInscripcionCenso
     */
    public function setEstudianteInscripcion(\Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion = null)
    {
        $this->estudianteInscripcion = $estudianteInscripcion;
    
        return $this;
    }

    /**
     * Get estudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcion 
     */
    public function getEstudianteInscripcion()
    {
        return $this->estudianteInscripcion;
    }

    /**
     * Set apoderadoInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\ApoderadoInscripcion $apoderadoInscripcion
     * @return CenEstudianteInscripcionCenso
     */
    public function setApoderadoInscripcion(\Sie\AppWebBundle\Entity\ApoderadoInscripcion $apoderadoInscripcion = null)
    {
        $this->apoderadoInscripcion = $apoderadoInscripcion;
    
        return $this;
    }

    /**
     * Get apoderadoInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\ApoderadoInscripcion 
     */
    public function getApoderadoInscripcion()
    {
        return $this->apoderadoInscripcion;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return CenEstudianteInscripcionCenso
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
}
