<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BfObservacionValidacion
 */
class BfObservacionValidacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $codigoRude;

    /**
     * @var integer
     */
    private $estudianteInscripcionId;

    /**
     * @var integer
     */
    private $bfObservacionTipoId;

    /**
     * @var boolean
     */
    private $esValidado;

    /**
     * @var string
     */
    private $documento;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var string
     */
    private $justificacion;

    /**
     * @var string
     */
    private $idDepartamento;

    /**
     * @var string
     */
    private $descDepartamento;

    /**
     * @var string
     */
    private $codDistrito;

    /**
     * @var string
     */
    private $distrito;

    /**
     * @var integer
     */
    private $codUeId;

    /**
     * @var string
     */
    private $descUe;


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
     * Set codigoRude
     *
     * @param string $codigoRude
     * @return BfObservacionValidacion
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
     * Set estudianteInscripcionId
     *
     * @param integer $estudianteInscripcionId
     * @return BfObservacionValidacion
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
     * Set bfObservacionTipoId
     *
     * @param integer $bfObservacionTipoId
     * @return BfObservacionValidacion
     */
    public function setBfObservacionTipoId($bfObservacionTipoId)
    {
        $this->bfObservacionTipoId = $bfObservacionTipoId;
    
        return $this;
    }

    /**
     * Get bfObservacionTipoId
     *
     * @return integer 
     */
    public function getBfObservacionTipoId()
    {
        return $this->bfObservacionTipoId;
    }

    /**
     * Set esValidado
     *
     * @param boolean $esValidado
     * @return BfObservacionValidacion
     */
    public function setEsValidado($esValidado)
    {
        $this->esValidado = $esValidado;
    
        return $this;
    }

    /**
     * Get esValidado
     *
     * @return boolean 
     */
    public function getEsValidado()
    {
        return $this->esValidado;
    }

    /**
     * Set documento
     *
     * @param string $documento
     * @return BfObservacionValidacion
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return BfObservacionValidacion
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
     * @return BfObservacionValidacion
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
     * Set justificacion
     *
     * @param string $justificacion
     * @return BfObservacionValidacion
     */
    public function setJustificacion($justificacion)
    {
        $this->justificacion = $justificacion;
    
        return $this;
    }

    /**
     * Get justificacion
     *
     * @return string 
     */
    public function getJustificacion()
    {
        return $this->justificacion;
    }

    /**
     * Set idDepartamento
     *
     * @param string $idDepartamento
     * @return BfObservacionValidacion
     */
    public function setIdDepartamento($idDepartamento)
    {
        $this->idDepartamento = $idDepartamento;
    
        return $this;
    }

    /**
     * Get idDepartamento
     *
     * @return string 
     */
    public function getIdDepartamento()
    {
        return $this->idDepartamento;
    }

    /**
     * Set descDepartamento
     *
     * @param string $descDepartamento
     * @return BfObservacionValidacion
     */
    public function setDescDepartamento($descDepartamento)
    {
        $this->descDepartamento = $descDepartamento;
    
        return $this;
    }

    /**
     * Get descDepartamento
     *
     * @return string 
     */
    public function getDescDepartamento()
    {
        return $this->descDepartamento;
    }

    /**
     * Set codDistrito
     *
     * @param string $codDistrito
     * @return BfObservacionValidacion
     */
    public function setCodDistrito($codDistrito)
    {
        $this->codDistrito = $codDistrito;
    
        return $this;
    }

    /**
     * Get codDistrito
     *
     * @return string 
     */
    public function getCodDistrito()
    {
        return $this->codDistrito;
    }

    /**
     * Set distrito
     *
     * @param string $distrito
     * @return BfObservacionValidacion
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
     * Set codUeId
     *
     * @param integer $codUeId
     * @return BfObservacionValidacion
     */
    public function setCodUeId($codUeId)
    {
        $this->codUeId = $codUeId;
    
        return $this;
    }

    /**
     * Get codUeId
     *
     * @return integer 
     */
    public function getCodUeId()
    {
        return $this->codUeId;
    }

    /**
     * Set descUe
     *
     * @param string $descUe
     * @return BfObservacionValidacion
     */
    public function setDescUe($descUe)
    {
        $this->descUe = $descUe;
    
        return $this;
    }

    /**
     * Get descUe
     *
     * @return string 
     */
    public function getDescUe()
    {
        return $this->descUe;
    }
}
