<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivDatHabDatoPersonal
 */
class UnivDatHabDatoPersonal
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $ci;

    /**
     * @var string
     */
    private $complemento;

    /**
     * @var string
     */
    private $expedidoId;

    /**
     * @var string
     */
    private $paterno;

    /**
     * @var string
     */
    private $materno;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var string
     */
    private $generoId;

    /**
     * @var string
     */
    private $nCertificadoNacimiento;

    /**
     * @var string
     */
    private $dipInstitucion;

    /**
     * @var string
     */
    private $dipFechaEmision;

    /**
     * @var string
     */
    private $dipNumero;

    /**
     * @var string
     */
    private $complementario;

    /**
     * @var string
     */
    private $resolucionDptal;

    /**
     * @var string
     */
    private $nPartida;

    /**
     * @var string
     */
    private $pasaporte;

    /**
     * @var string
     */
    private $paisId;

    /**
     * @var string
     */
    private $visa;

    /**
     * @var string
     */
    private $habilitacionId;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var string
     */
    private $resolucion;

    /**
     * @var string
     */
    private $updatedAt;


    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ci
     *
     * @param string $ci
     * @return UnivDatHabDatoPersonal
     */
    public function setCi($ci)
    {
        $this->ci = $ci;
    
        return $this;
    }

    /**
     * Get ci
     *
     * @return string 
     */
    public function getCi()
    {
        return $this->ci;
    }

    /**
     * Set complemento
     *
     * @param string $complemento
     * @return UnivDatHabDatoPersonal
     */
    public function setComplemento($complemento)
    {
        $this->complemento = $complemento;
    
        return $this;
    }

    /**
     * Get complemento
     *
     * @return string 
     */
    public function getComplemento()
    {
        return $this->complemento;
    }

    /**
     * Set expedidoId
     *
     * @param string $expedidoId
     * @return UnivDatHabDatoPersonal
     */
    public function setExpedidoId($expedidoId)
    {
        $this->expedidoId = $expedidoId;
    
        return $this;
    }

    /**
     * Get expedidoId
     *
     * @return string 
     */
    public function getExpedidoId()
    {
        return $this->expedidoId;
    }

    /**
     * Set paterno
     *
     * @param string $paterno
     * @return UnivDatHabDatoPersonal
     */
    public function setPaterno($paterno)
    {
        $this->paterno = $paterno;
    
        return $this;
    }

    /**
     * Get paterno
     *
     * @return string 
     */
    public function getPaterno()
    {
        return $this->paterno;
    }

    /**
     * Set materno
     *
     * @param string $materno
     * @return UnivDatHabDatoPersonal
     */
    public function setMaterno($materno)
    {
        $this->materno = $materno;
    
        return $this;
    }

    /**
     * Get materno
     *
     * @return string 
     */
    public function getMaterno()
    {
        return $this->materno;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return UnivDatHabDatoPersonal
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set generoId
     *
     * @param string $generoId
     * @return UnivDatHabDatoPersonal
     */
    public function setGeneroId($generoId)
    {
        $this->generoId = $generoId;
    
        return $this;
    }

    /**
     * Get generoId
     *
     * @return string 
     */
    public function getGeneroId()
    {
        return $this->generoId;
    }

    /**
     * Set nCertificadoNacimiento
     *
     * @param string $nCertificadoNacimiento
     * @return UnivDatHabDatoPersonal
     */
    public function setNCertificadoNacimiento($nCertificadoNacimiento)
    {
        $this->nCertificadoNacimiento = $nCertificadoNacimiento;
    
        return $this;
    }

    /**
     * Get nCertificadoNacimiento
     *
     * @return string 
     */
    public function getNCertificadoNacimiento()
    {
        return $this->nCertificadoNacimiento;
    }

    /**
     * Set dipInstitucion
     *
     * @param string $dipInstitucion
     * @return UnivDatHabDatoPersonal
     */
    public function setDipInstitucion($dipInstitucion)
    {
        $this->dipInstitucion = $dipInstitucion;
    
        return $this;
    }

    /**
     * Get dipInstitucion
     *
     * @return string 
     */
    public function getDipInstitucion()
    {
        return $this->dipInstitucion;
    }

    /**
     * Set dipFechaEmision
     *
     * @param string $dipFechaEmision
     * @return UnivDatHabDatoPersonal
     */
    public function setDipFechaEmision($dipFechaEmision)
    {
        $this->dipFechaEmision = $dipFechaEmision;
    
        return $this;
    }

    /**
     * Get dipFechaEmision
     *
     * @return string 
     */
    public function getDipFechaEmision()
    {
        return $this->dipFechaEmision;
    }

    /**
     * Set dipNumero
     *
     * @param string $dipNumero
     * @return UnivDatHabDatoPersonal
     */
    public function setDipNumero($dipNumero)
    {
        $this->dipNumero = $dipNumero;
    
        return $this;
    }

    /**
     * Get dipNumero
     *
     * @return string 
     */
    public function getDipNumero()
    {
        return $this->dipNumero;
    }

    /**
     * Set complementario
     *
     * @param string $complementario
     * @return UnivDatHabDatoPersonal
     */
    public function setComplementario($complementario)
    {
        $this->complementario = $complementario;
    
        return $this;
    }

    /**
     * Get complementario
     *
     * @return string 
     */
    public function getComplementario()
    {
        return $this->complementario;
    }

    /**
     * Set resolucionDptal
     *
     * @param string $resolucionDptal
     * @return UnivDatHabDatoPersonal
     */
    public function setResolucionDptal($resolucionDptal)
    {
        $this->resolucionDptal = $resolucionDptal;
    
        return $this;
    }

    /**
     * Get resolucionDptal
     *
     * @return string 
     */
    public function getResolucionDptal()
    {
        return $this->resolucionDptal;
    }

    /**
     * Set nPartida
     *
     * @param string $nPartida
     * @return UnivDatHabDatoPersonal
     */
    public function setNPartida($nPartida)
    {
        $this->nPartida = $nPartida;
    
        return $this;
    }

    /**
     * Get nPartida
     *
     * @return string 
     */
    public function getNPartida()
    {
        return $this->nPartida;
    }

    /**
     * Set pasaporte
     *
     * @param string $pasaporte
     * @return UnivDatHabDatoPersonal
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
     * Set paisId
     *
     * @param string $paisId
     * @return UnivDatHabDatoPersonal
     */
    public function setPaisId($paisId)
    {
        $this->paisId = $paisId;
    
        return $this;
    }

    /**
     * Get paisId
     *
     * @return string 
     */
    public function getPaisId()
    {
        return $this->paisId;
    }

    /**
     * Set visa
     *
     * @param string $visa
     * @return UnivDatHabDatoPersonal
     */
    public function setVisa($visa)
    {
        $this->visa = $visa;
    
        return $this;
    }

    /**
     * Get visa
     *
     * @return string 
     */
    public function getVisa()
    {
        return $this->visa;
    }

    /**
     * Set habilitacionId
     *
     * @param string $habilitacionId
     * @return UnivDatHabDatoPersonal
     */
    public function setHabilitacionId($habilitacionId)
    {
        $this->habilitacionId = $habilitacionId;
    
        return $this;
    }

    /**
     * Get habilitacionId
     *
     * @return string 
     */
    public function getHabilitacionId()
    {
        return $this->habilitacionId;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return UnivDatHabDatoPersonal
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
     * Set resolucion
     *
     * @param string $resolucion
     * @return UnivDatHabDatoPersonal
     */
    public function setResolucion($resolucion)
    {
        $this->resolucion = $resolucion;
    
        return $this;
    }

    /**
     * Get resolucion
     *
     * @return string 
     */
    public function getResolucion()
    {
        return $this->resolucion;
    }

    /**
     * Set updatedAt
     *
     * @param string $updatedAt
     * @return UnivDatHabDatoPersonal
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return string 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
