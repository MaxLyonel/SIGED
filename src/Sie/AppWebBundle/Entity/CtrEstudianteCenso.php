<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CtrEstudianteCenso
 */
class CtrEstudianteCenso
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nro;

    /**
     * @var string
     */
    private $idMinedu;

    /**
     * @var string
     */
    private $cedIdentidad;

    /**
     * @var string
     */
    private $nombres;

    /**
     * @var string
     */
    private $primerAp;

    /**
     * @var string
     */
    private $segundoAp;

    /**
     * @var string
     */
    private $categoria;

    /**
     * @var string
     */
    private $subCategoria;

    /**
     * @var string
     */
    private $detalle;

    /**
     * @var string
     */
    private $departamento;

    /**
     * @var string
     */
    private $codigoCertificado;

    /**
     * @var string
     */
    private $codigoRude;

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
     * @var integer
     */
    private $estudianteInscripcionId;

    /**
     * @var integer
     */
    private $estadomatriculaTipoId;

    /**
     * @var \DateTime
     */
    private $fechaProcesamiento;

    /**
     * @var integer
     */
    private $estudianteId;

    /**
     * @var string
     */
    private $observacion;


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
     * Set nro
     *
     * @param string $nro
     * @return CtrEstudianteCenso
     */
    public function setNro($nro)
    {
        $this->nro = $nro;
    
        return $this;
    }

    /**
     * Get nro
     *
     * @return string 
     */
    public function getNro()
    {
        return $this->nro;
    }

    /**
     * Set idMinedu
     *
     * @param string $idMinedu
     * @return CtrEstudianteCenso
     */
    public function setIdMinedu($idMinedu)
    {
        $this->idMinedu = $idMinedu;
    
        return $this;
    }

    /**
     * Get idMinedu
     *
     * @return string 
     */
    public function getIdMinedu()
    {
        return $this->idMinedu;
    }

    /**
     * Set cedIdentidad
     *
     * @param string $cedIdentidad
     * @return CtrEstudianteCenso
     */
    public function setCedIdentidad($cedIdentidad)
    {
        $this->cedIdentidad = $cedIdentidad;
    
        return $this;
    }

    /**
     * Get cedIdentidad
     *
     * @return string 
     */
    public function getCedIdentidad()
    {
        return $this->cedIdentidad;
    }

    /**
     * Set nombres
     *
     * @param string $nombres
     * @return CtrEstudianteCenso
     */
    public function setNombres($nombres)
    {
        $this->nombres = $nombres;
    
        return $this;
    }

    /**
     * Get nombres
     *
     * @return string 
     */
    public function getNombres()
    {
        return $this->nombres;
    }

    /**
     * Set primerAp
     *
     * @param string $primerAp
     * @return CtrEstudianteCenso
     */
    public function setPrimerAp($primerAp)
    {
        $this->primerAp = $primerAp;
    
        return $this;
    }

    /**
     * Get primerAp
     *
     * @return string 
     */
    public function getPrimerAp()
    {
        return $this->primerAp;
    }

    /**
     * Set segundoAp
     *
     * @param string $segundoAp
     * @return CtrEstudianteCenso
     */
    public function setSegundoAp($segundoAp)
    {
        $this->segundoAp = $segundoAp;
    
        return $this;
    }

    /**
     * Get segundoAp
     *
     * @return string 
     */
    public function getSegundoAp()
    {
        return $this->segundoAp;
    }

    /**
     * Set categoria
     *
     * @param string $categoria
     * @return CtrEstudianteCenso
     */
    public function setCategoria($categoria)
    {
        $this->categoria = $categoria;
    
        return $this;
    }

    /**
     * Get categoria
     *
     * @return string 
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
     * Set subCategoria
     *
     * @param string $subCategoria
     * @return CtrEstudianteCenso
     */
    public function setSubCategoria($subCategoria)
    {
        $this->subCategoria = $subCategoria;
    
        return $this;
    }

    /**
     * Get subCategoria
     *
     * @return string 
     */
    public function getSubCategoria()
    {
        return $this->subCategoria;
    }

    /**
     * Set detalle
     *
     * @param string $detalle
     * @return CtrEstudianteCenso
     */
    public function setDetalle($detalle)
    {
        $this->detalle = $detalle;
    
        return $this;
    }

    /**
     * Get detalle
     *
     * @return string 
     */
    public function getDetalle()
    {
        return $this->detalle;
    }

    /**
     * Set departamento
     *
     * @param string $departamento
     * @return CtrEstudianteCenso
     */
    public function setDepartamento($departamento)
    {
        $this->departamento = $departamento;
    
        return $this;
    }

    /**
     * Get departamento
     *
     * @return string 
     */
    public function getDepartamento()
    {
        return $this->departamento;
    }

    /**
     * Set codigoCertificado
     *
     * @param string $codigoCertificado
     * @return CtrEstudianteCenso
     */
    public function setCodigoCertificado($codigoCertificado)
    {
        $this->codigoCertificado = $codigoCertificado;
    
        return $this;
    }

    /**
     * Get codigoCertificado
     *
     * @return string 
     */
    public function getCodigoCertificado()
    {
        return $this->codigoCertificado;
    }

    /**
     * Set codigoRude
     *
     * @param string $codigoRude
     * @return CtrEstudianteCenso
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
     * Set paternoEst
     *
     * @param string $paternoEst
     * @return CtrEstudianteCenso
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
     * @return CtrEstudianteCenso
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
     * @return CtrEstudianteCenso
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
     * Set estudianteInscripcionId
     *
     * @param integer $estudianteInscripcionId
     * @return CtrEstudianteCenso
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
     * Set estadomatriculaTipoId
     *
     * @param integer $estadomatriculaTipoId
     * @return CtrEstudianteCenso
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
     * Set fechaProcesamiento
     *
     * @param \DateTime $fechaProcesamiento
     * @return CtrEstudianteCenso
     */
    public function setFechaProcesamiento($fechaProcesamiento)
    {
        $this->fechaProcesamiento = $fechaProcesamiento;
    
        return $this;
    }

    /**
     * Get fechaProcesamiento
     *
     * @return \DateTime 
     */
    public function getFechaProcesamiento()
    {
        return $this->fechaProcesamiento;
    }

    /**
     * Set estudianteId
     *
     * @param integer $estudianteId
     * @return CtrEstudianteCenso
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
     * Set observacion
     *
     * @param string $observacion
     * @return CtrEstudianteCenso
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
}
