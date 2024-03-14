<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeExtranjero
 */
class RudeExtranjero
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $ciExtranjero;

    /**
     * @var boolean
     */
    private $ciDiplomatico;

    /**
     * @var boolean
     */
    private $cnExtranjero;

    /**
     * @var boolean
     */
    private $dni;

    /**
     * @var boolean
     */
    private $pasaporte;

    /**
     * @var boolean
     */
    private $declaracion;

    /**
     * @var string
     */
    private $codigoDocumento;

    /**
     * @var string
     */
    private $archivo;

    /**
     * @var string
     */
    private $centroAcogida;

    /**
     * @var string
     */
    private $tutorExtInstitucion;

    /**
     * @var \Sie\AppWebBundle\Entity\Rude
     */
    private $rude;


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
     * Set ciExtranjero
     *
     * @param boolean $ciExtranjero
     * @return RudeExtranjero
     */
    public function setCiExtranjero($ciExtranjero)
    {
        $this->ciExtranjero = $ciExtranjero;
    
        return $this;
    }

    /**
     * Get ciExtranjero
     *
     * @return boolean 
     */
    public function getCiExtranjero()
    {
        return $this->ciExtranjero;
    }

    /**
     * Set ciDiplomatico
     *
     * @param boolean $ciDiplomatico
     * @return RudeExtranjero
     */
    public function setCiDiplomatico($ciDiplomatico)
    {
        $this->ciDiplomatico = $ciDiplomatico;
    
        return $this;
    }

    /**
     * Get ciDiplomatico
     *
     * @return boolean 
     */
    public function getCiDiplomatico()
    {
        return $this->ciDiplomatico;
    }

    /**
     * Set cnExtranjero
     *
     * @param boolean $cnExtranjero
     * @return RudeExtranjero
     */
    public function setCnExtranjero($cnExtranjero)
    {
        $this->cnExtranjero = $cnExtranjero;
    
        return $this;
    }

    /**
     * Get cnExtranjero
     *
     * @return boolean 
     */
    public function getCnExtranjero()
    {
        return $this->cnExtranjero;
    }

    /**
     * Set dni
     *
     * @param boolean $dni
     * @return RudeExtranjero
     */
    public function setDni($dni)
    {
        $this->dni = $dni;
    
        return $this;
    }

    /**
     * Get dni
     *
     * @return boolean 
     */
    public function getDni()
    {
        return $this->dni;
    }

    /**
     * Set pasaporte
     *
     * @param boolean $pasaporte
     * @return RudeExtranjero
     */
    public function setPasaporte($pasaporte)
    {
        $this->pasaporte = $pasaporte;
    
        return $this;
    }

    /**
     * Get pasaporte
     *
     * @return boolean 
     */
    public function getPasaporte()
    {
        return $this->pasaporte;
    }

    /**
     * Set declaracion
     *
     * @param boolean $declaracion
     * @return RudeExtranjero
     */
    public function setDeclaracion($declaracion)
    {
        $this->declaracion = $declaracion;
    
        return $this;
    }

    /**
     * Get declaracion
     *
     * @return boolean 
     */
    public function getDeclaracion()
    {
        return $this->declaracion;
    }

    /**
     * Set codigoDocumento
     *
     * @param string $codigoDocumento
     * @return RudeExtranjero
     */
    public function setCodigoDocumento($codigoDocumento)
    {
        $this->codigoDocumento = $codigoDocumento;
    
        return $this;
    }

    /**
     * Get codigoDocumento
     *
     * @return string 
     */
    public function getCodigoDocumento()
    {
        return $this->codigoDocumento;
    }

    /**
     * Set archivo
     *
     * @param string $archivo
     * @return RudeExtranjero
     */
    public function setArchivo($archivo)
    {
        $this->archivo = $archivo;
    
        return $this;
    }

    /**
     * Get archivo
     *
     * @return string 
     */
    public function getArchivo()
    {
        return $this->archivo;
    }

    /**
     * Set centroAcogida
     *
     * @param string $centroAcogida
     * @return RudeExtranjero
     */
    public function setCentroAcogida($centroAcogida)
    {
        $this->centroAcogida = $centroAcogida;
    
        return $this;
    }

    /**
     * Get centroAcogida
     *
     * @return string 
     */
    public function getCentroAcogida()
    {
        return $this->centroAcogida;
    }

    /**
     * Set tutorExtInstitucion
     *
     * @param string $tutorExtInstitucion
     * @return RudeExtranjero
     */
    public function setTutorExtInstitucion($tutorExtInstitucion)
    {
        $this->tutorExtInstitucion = $tutorExtInstitucion;
    
        return $this;
    }

    /**
     * Get tutorExtInstitucion
     *
     * @return string 
     */
    public function getTutorExtInstitucion()
    {
        return $this->tutorExtInstitucion;
    }

    /**
     * Set rude
     *
     * @param \Sie\AppWebBundle\Entity\Rude $rude
     * @return RudeExtranjero
     */
    public function setRude(\Sie\AppWebBundle\Entity\Rude $rude = null)
    {
        $this->rude = $rude;
    
        return $this;
    }

    /**
     * Get rude
     *
     * @return \Sie\AppWebBundle\Entity\Rude 
     */
    public function getRude()
    {
        return $this->rude;
    }
}
