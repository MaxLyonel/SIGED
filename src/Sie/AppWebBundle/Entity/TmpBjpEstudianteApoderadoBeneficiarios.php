<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TmpBjpEstudianteApoderadoBeneficiarios
 */
class TmpBjpEstudianteApoderadoBeneficiarios
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
    private $apoderadoTipoId;

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
     * @var integer
     */
    private $estadomatriculaTipoId;

    /**
     * @var integer
     */
    private $estadoId;

    /**
     * @var integer
     */
    private $generoTipoEstId;

    /**
     * @var integer
     */
    private $generoTipoTutId;

    /**
     * @var \DateTime
     */
    private $fechaNacimientoTut;

    /**
     * @var \DateTime
     */
    private $fechaCorte;


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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * Set apoderadoTipoId
     *
     * @param integer $apoderadoTipoId
     * @return TmpBjpEstudianteApoderadoBeneficiarios
     */
    public function setApoderadoTipoId($apoderadoTipoId)
    {
        $this->apoderadoTipoId = $apoderadoTipoId;
    
        return $this;
    }

    /**
     * Get apoderadoTipoId
     *
     * @return integer 
     */
    public function getApoderadoTipoId()
    {
        return $this->apoderadoTipoId;
    }

    /**
     * Set segipIdTut
     *
     * @param integer $segipIdTut
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * @return TmpBjpEstudianteApoderadoBeneficiarios
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
     * Set estadomatriculaTipoId
     *
     * @param integer $estadomatriculaTipoId
     * @return TmpBjpEstudianteApoderadoBeneficiarios
     */
    public function setEstadomatriculaTipoId($estadomatriculaTipoId)
    {
        $this->estadomatriculaTipoId = $estadomatriculaTipoId;
    
        return $this;
    }

    /**
     * Get estadomatriculaTipoId
     *
     * @return integer 
     */
    public function getEstadomatriculaTipoId()
    {
        return $this->estadomatriculaTipoId;
    }

    /**
     * Set estadoId
     *
     * @param integer $estadoId
     * @return TmpBjpEstudianteApoderadoBeneficiarios
     */
    public function setEstadoId($estadoId)
    {
        $this->estadoId = $estadoId;
    
        return $this;
    }

    /**
     * Get estadoId
     *
     * @return integer 
     */
    public function getEstadoId()
    {
        return $this->estadoId;
    }

    /**
     * Set generoTipoEstId
     *
     * @param integer $generoTipoEstId
     * @return TmpBjpEstudianteApoderadoBeneficiarios
     */
    public function setGeneroTipoEstId($generoTipoEstId)
    {
        $this->generoTipoEstId = $generoTipoEstId;
    
        return $this;
    }

    /**
     * Get generoTipoEstId
     *
     * @return integer 
     */
    public function getGeneroTipoEstId()
    {
        return $this->generoTipoEstId;
    }

    /**
     * Set generoTipoTutId
     *
     * @param integer $generoTipoTutId
     * @return TmpBjpEstudianteApoderadoBeneficiarios
     */
    public function setGeneroTipoTutId($generoTipoTutId)
    {
        $this->generoTipoTutId = $generoTipoTutId;
    
        return $this;
    }

    /**
     * Get generoTipoTutId
     *
     * @return integer 
     */
    public function getGeneroTipoTutId()
    {
        return $this->generoTipoTutId;
    }

    /**
     * Set fechaNacimientoTut
     *
     * @param \DateTime $fechaNacimientoTut
     * @return TmpBjpEstudianteApoderadoBeneficiarios
     */
    public function setFechaNacimientoTut($fechaNacimientoTut)
    {
        $this->fechaNacimientoTut = $fechaNacimientoTut;
    
        return $this;
    }

    /**
     * Get fechaNacimientoTut
     *
     * @return \DateTime 
     */
    public function getFechaNacimientoTut()
    {
        return $this->fechaNacimientoTut;
    }

    /**
     * Set fechaCorte
     *
     * @param \DateTime $fechaCorte
     * @return TmpBjpEstudianteApoderadoBeneficiarios
     */
    public function setFechaCorte($fechaCorte)
    {
        $this->fechaCorte = $fechaCorte;
    
        return $this;
    }

    /**
     * Get fechaCorte
     *
     * @return \DateTime 
     */
    public function getFechaCorte()
    {
        return $this->fechaCorte;
    }
}
