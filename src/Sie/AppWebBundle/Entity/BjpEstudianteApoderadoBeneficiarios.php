<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BjpEstudianteApoderadoBeneficiarios
 */
class BjpEstudianteApoderadoBeneficiarios
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $institucioneducativaId;

    /**
     * @var integer
     */
    private $nivelTipoId;

    /**
     * @var integer
     */
    private $gradoTipoId;

    /**
     * @var string
     */
    private $paraleloTipoId;

    /**
     * @var integer
     */
    private $turnoTipoId;

    /**
     * @var integer
     */
    private $estudianteInscripcionId;

    /**
     * @var integer
     */
    private $estudianteId;

    /**
     * @var string
     */
    private $codigoRude;

    /**
     * @var string
     */
    private $carnetEst;

    /**
     * @var string
     */
    private $complementoEst;

    /**
     * @var string
     */
    private $paternoEst;

    /**
     * @var string
     */
    private $maternoEst;

    /**
     * @var string
     */
    private $nombreEst;

    /**
     * @var \DateTime
     */
    private $fechaNacimientoEst;

    /**
     * @var integer
     */
    private $personaId;

    /**
     * @var string
     */
    private $carnetTut;

    /**
     * @var string
     */
    private $complementoTut;

    /**
     * @var string
     */
    private $paternoTut;

    /**
     * @var string
     */
    private $maternoTut;

    /**
     * @var string
     */
    private $nombreTut;

    /**
     * @var integer
     */
    private $segipIdTut;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaActualizacion;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var \Sie\AppWebBundle\Entity\ApoderadoTipo
     */
    private $apoderadoTipo;


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
     * Set institucioneducativaId
     *
     * @param integer $institucioneducativaId
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setInstitucioneducativaId($institucioneducativaId)
    {
        $this->institucioneducativaId = $institucioneducativaId;
    
        return $this;
    }

    /**
     * Get institucioneducativaId
     *
     * @return integer 
     */
    public function getInstitucioneducativaId()
    {
        return $this->institucioneducativaId;
    }

    /**
     * Set nivelTipoId
     *
     * @param integer $nivelTipoId
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setNivelTipoId($nivelTipoId)
    {
        $this->nivelTipoId = $nivelTipoId;
    
        return $this;
    }

    /**
     * Get nivelTipoId
     *
     * @return integer 
     */
    public function getNivelTipoId()
    {
        return $this->nivelTipoId;
    }

    /**
     * Set gradoTipoId
     *
     * @param integer $gradoTipoId
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setGradoTipoId($gradoTipoId)
    {
        $this->gradoTipoId = $gradoTipoId;
    
        return $this;
    }

    /**
     * Get gradoTipoId
     *
     * @return integer 
     */
    public function getGradoTipoId()
    {
        return $this->gradoTipoId;
    }

    /**
     * Set paraleloTipoId
     *
     * @param string $paraleloTipoId
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setParaleloTipoId($paraleloTipoId)
    {
        $this->paraleloTipoId = $paraleloTipoId;
    
        return $this;
    }

    /**
     * Get paraleloTipoId
     *
     * @return string 
     */
    public function getParaleloTipoId()
    {
        return $this->paraleloTipoId;
    }

    /**
     * Set turnoTipoId
     *
     * @param integer $turnoTipoId
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setTurnoTipoId($turnoTipoId)
    {
        $this->turnoTipoId = $turnoTipoId;
    
        return $this;
    }

    /**
     * Get turnoTipoId
     *
     * @return integer 
     */
    public function getTurnoTipoId()
    {
        return $this->turnoTipoId;
    }

    /**
     * Set estudianteInscripcionId
     *
     * @param integer $estudianteInscripcionId
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setEstudianteInscripcionId($estudianteInscripcionId)
    {
        $this->estudianteInscripcionId = $estudianteInscripcionId;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionId
     *
     * @return integer 
     */
    public function getEstudianteInscripcionId()
    {
        return $this->estudianteInscripcionId;
    }

    /**
     * Set estudianteId
     *
     * @param integer $estudianteId
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setEstudianteId($estudianteId)
    {
        $this->estudianteId = $estudianteId;
    
        return $this;
    }

    /**
     * Get estudianteId
     *
     * @return integer 
     */
    public function getEstudianteId()
    {
        return $this->estudianteId;
    }

    /**
     * Set codigoRude
     *
     * @param string $codigoRude
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setCodigoRude($codigoRude)
    {
        $this->codigoRude = $codigoRude;
    
        return $this;
    }

    /**
     * Get codigoRude
     *
     * @return string 
     */
    public function getCodigoRude()
    {
        return $this->codigoRude;
    }

    /**
     * Set carnetEst
     *
     * @param string $carnetEst
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setCarnetEst($carnetEst)
    {
        $this->carnetEst = $carnetEst;
    
        return $this;
    }

    /**
     * Get carnetEst
     *
     * @return string 
     */
    public function getCarnetEst()
    {
        return $this->carnetEst;
    }

    /**
     * Set complementoEst
     *
     * @param string $complementoEst
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setComplementoEst($complementoEst)
    {
        $this->complementoEst = $complementoEst;
    
        return $this;
    }

    /**
     * Get complementoEst
     *
     * @return string 
     */
    public function getComplementoEst()
    {
        return $this->complementoEst;
    }

    /**
     * Set paternoEst
     *
     * @param string $paternoEst
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setPaternoEst($paternoEst)
    {
        $this->paternoEst = $paternoEst;
    
        return $this;
    }

    /**
     * Get paternoEst
     *
     * @return string 
     */
    public function getPaternoEst()
    {
        return $this->paternoEst;
    }

    /**
     * Set maternoEst
     *
     * @param string $maternoEst
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setMaternoEst($maternoEst)
    {
        $this->maternoEst = $maternoEst;
    
        return $this;
    }

    /**
     * Get maternoEst
     *
     * @return string 
     */
    public function getMaternoEst()
    {
        return $this->maternoEst;
    }

    /**
     * Set nombreEst
     *
     * @param string $nombreEst
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setNombreEst($nombreEst)
    {
        $this->nombreEst = $nombreEst;
    
        return $this;
    }

    /**
     * Get nombreEst
     *
     * @return string 
     */
    public function getNombreEst()
    {
        return $this->nombreEst;
    }

    /**
     * Set fechaNacimientoEst
     *
     * @param \DateTime $fechaNacimientoEst
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setFechaNacimientoEst($fechaNacimientoEst)
    {
        $this->fechaNacimientoEst = $fechaNacimientoEst;
    
        return $this;
    }

    /**
     * Get fechaNacimientoEst
     *
     * @return \DateTime 
     */
    public function getFechaNacimientoEst()
    {
        return $this->fechaNacimientoEst;
    }

    /**
     * Set personaId
     *
     * @param integer $personaId
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setPersonaId($personaId)
    {
        $this->personaId = $personaId;
    
        return $this;
    }

    /**
     * Get personaId
     *
     * @return integer 
     */
    public function getPersonaId()
    {
        return $this->personaId;
    }

    /**
     * Set carnetTut
     *
     * @param string $carnetTut
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setCarnetTut($carnetTut)
    {
        $this->carnetTut = $carnetTut;
    
        return $this;
    }

    /**
     * Get carnetTut
     *
     * @return string 
     */
    public function getCarnetTut()
    {
        return $this->carnetTut;
    }

    /**
     * Set complementoTut
     *
     * @param string $complementoTut
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setComplementoTut($complementoTut)
    {
        $this->complementoTut = $complementoTut;
    
        return $this;
    }

    /**
     * Get complementoTut
     *
     * @return string 
     */
    public function getComplementoTut()
    {
        return $this->complementoTut;
    }

    /**
     * Set paternoTut
     *
     * @param string $paternoTut
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setPaternoTut($paternoTut)
    {
        $this->paternoTut = $paternoTut;
    
        return $this;
    }

    /**
     * Get paternoTut
     *
     * @return string 
     */
    public function getPaternoTut()
    {
        return $this->paternoTut;
    }

    /**
     * Set maternoTut
     *
     * @param string $maternoTut
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setMaternoTut($maternoTut)
    {
        $this->maternoTut = $maternoTut;
    
        return $this;
    }

    /**
     * Get maternoTut
     *
     * @return string 
     */
    public function getMaternoTut()
    {
        return $this->maternoTut;
    }

    /**
     * Set nombreTut
     *
     * @param string $nombreTut
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setNombreTut($nombreTut)
    {
        $this->nombreTut = $nombreTut;
    
        return $this;
    }

    /**
     * Get nombreTut
     *
     * @return string 
     */
    public function getNombreTut()
    {
        return $this->nombreTut;
    }

    /**
     * Set segipIdTut
     *
     * @param integer $segipIdTut
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setSegipIdTut($segipIdTut)
    {
        $this->segipIdTut = $segipIdTut;
    
        return $this;
    }

    /**
     * Get segipIdTut
     *
     * @return integer 
     */
    public function getSegipIdTut()
    {
        return $this->segipIdTut;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return BjpEstudianteApoderadoBeneficiarios
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
     * Set fechaActualizacion
     *
     * @param \DateTime $fechaActualizacion
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setFechaActualizacion($fechaActualizacion)
    {
        $this->fechaActualizacion = $fechaActualizacion;
    
        return $this;
    }

    /**
     * Get fechaActualizacion
     *
     * @return \DateTime 
     */
    public function getFechaActualizacion()
    {
        return $this->fechaActualizacion;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set apoderadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\ApoderadoTipo $apoderadoTipo
     * @return BjpEstudianteApoderadoBeneficiarios
     */
    public function setApoderadoTipo(\Sie\AppWebBundle\Entity\ApoderadoTipo $apoderadoTipo = null)
    {
        $this->apoderadoTipo = $apoderadoTipo;
    
        return $this;
    }

    /**
     * Get apoderadoTipo
     *
     * @return \Sie\AppWebBundle\Entity\ApoderadoTipo 
     */
    public function getApoderadoTipo()
    {
        return $this->apoderadoTipo;
    }
}
