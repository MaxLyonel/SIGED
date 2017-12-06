<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RegistroConsolidacion
 */
class RegistroConsolidacion
{
    /**
     * @var integer
     */
    private $tipo;

    /**
     * @var integer
     */
    private $gestion;

    /**
     * @var integer
     */
    private $unidadEducativa;

    /**
     * @var integer
     */
    private $periodoId;

    /**
     * @var string
     */
    private $tabla;

    /**
     * @var string
     */
    private $identificador;

    /**
     * @var string
     */
    private $codigo;

    /**
     * @var string
     */
    private $descripcionError;

    /**
     * @var \DateTime
     */
    private $fecha;

    /**
     * @var string
     */
    private $usuario;

    /**
     * @var string
     */
    private $consulta;

    /**
     * @var integer
     */
    private $bim1;

    /**
     * @var integer
     */
    private $bim2;

    /**
     * @var integer
     */
    private $bim3;

    /**
     * @var integer
     */
    private $bim4;

    /**
     * @var integer
     */
    private $subCea;

    /**
     * @var integer
     */
    private $ban;

    /**
     * @var boolean
     */
    private $esonline;

    /**
     * @var integer
     */
    private $institucioneducativaTipoId;


    /**
     * Set tipo
     *
     * @param integer $tipo
     * @return RegistroConsolidacion
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    
        return $this;
    }

    /**
     * Get tipo
     *
     * @return integer 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set gestion
     *
     * @param integer $gestion
     * @return RegistroConsolidacion
     */
    public function setGestion($gestion)
    {
        $this->gestion = $gestion;
    
        return $this;
    }

    /**
     * Get gestion
     *
     * @return integer 
     */
    public function getGestion()
    {
        return $this->gestion;
    }

    /**
     * Set unidadEducativa
     *
     * @param integer $unidadEducativa
     * @return RegistroConsolidacion
     */
    public function setUnidadEducativa($unidadEducativa)
    {
        $this->unidadEducativa = $unidadEducativa;
    
        return $this;
    }

    /**
     * Get unidadEducativa
     *
     * @return integer 
     */
    public function getUnidadEducativa()
    {
        return $this->unidadEducativa;
    }

    /**
     * Set periodoId
     *
     * @param integer $periodoId
     * @return RegistroConsolidacion
     */
    public function setPeriodoId($periodoId)
    {
        $this->periodoId = $periodoId;
    
        return $this;
    }

    /**
     * Get periodoId
     *
     * @return integer 
     */
    public function getPeriodoId()
    {
        return $this->periodoId;
    }

    /**
     * Set tabla
     *
     * @param string $tabla
     * @return RegistroConsolidacion
     */
    public function setTabla($tabla)
    {
        $this->tabla = $tabla;
    
        return $this;
    }

    /**
     * Get tabla
     *
     * @return string 
     */
    public function getTabla()
    {
        return $this->tabla;
    }

    /**
     * Set identificador
     *
     * @param string $identificador
     * @return RegistroConsolidacion
     */
    public function setIdentificador($identificador)
    {
        $this->identificador = $identificador;
    
        return $this;
    }

    /**
     * Get identificador
     *
     * @return string 
     */
    public function getIdentificador()
    {
        return $this->identificador;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     * @return RegistroConsolidacion
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return string 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set descripcionError
     *
     * @param string $descripcionError
     * @return RegistroConsolidacion
     */
    public function setDescripcionError($descripcionError)
    {
        $this->descripcionError = $descripcionError;
    
        return $this;
    }

    /**
     * Get descripcionError
     *
     * @return string 
     */
    public function getDescripcionError()
    {
        return $this->descripcionError;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return RegistroConsolidacion
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set usuario
     *
     * @param string $usuario
     * @return RegistroConsolidacion
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return string 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set consulta
     *
     * @param string $consulta
     * @return RegistroConsolidacion
     */
    public function setConsulta($consulta)
    {
        $this->consulta = $consulta;
    
        return $this;
    }

    /**
     * Get consulta
     *
     * @return string 
     */
    public function getConsulta()
    {
        return $this->consulta;
    }

    /**
     * Set bim1
     *
     * @param integer $bim1
     * @return RegistroConsolidacion
     */
    public function setBim1($bim1)
    {
        $this->bim1 = $bim1;
    
        return $this;
    }

    /**
     * Get bim1
     *
     * @return integer 
     */
    public function getBim1()
    {
        return $this->bim1;
    }

    /**
     * Set bim2
     *
     * @param integer $bim2
     * @return RegistroConsolidacion
     */
    public function setBim2($bim2)
    {
        $this->bim2 = $bim2;
    
        return $this;
    }

    /**
     * Get bim2
     *
     * @return integer 
     */
    public function getBim2()
    {
        return $this->bim2;
    }

    /**
     * Set bim3
     *
     * @param integer $bim3
     * @return RegistroConsolidacion
     */
    public function setBim3($bim3)
    {
        $this->bim3 = $bim3;
    
        return $this;
    }

    /**
     * Get bim3
     *
     * @return integer 
     */
    public function getBim3()
    {
        return $this->bim3;
    }

    /**
     * Set bim4
     *
     * @param integer $bim4
     * @return RegistroConsolidacion
     */
    public function setBim4($bim4)
    {
        $this->bim4 = $bim4;
    
        return $this;
    }

    /**
     * Get bim4
     *
     * @return integer 
     */
    public function getBim4()
    {
        return $this->bim4;
    }

    /**
     * Set subCea
     *
     * @param integer $subCea
     * @return RegistroConsolidacion
     */
    public function setSubCea($subCea)
    {
        $this->subCea = $subCea;
    
        return $this;
    }

    /**
     * Get subCea
     *
     * @return integer 
     */
    public function getSubCea()
    {
        return $this->subCea;
    }

    /**
     * Set ban
     *
     * @param integer $ban
     * @return RegistroConsolidacion
     */
    public function setBan($ban)
    {
        $this->ban = $ban;
    
        return $this;
    }

    /**
     * Get ban
     *
     * @return integer 
     */
    public function getBan()
    {
        return $this->ban;
    }

    /**
     * Set esonline
     *
     * @param boolean $esonline
     * @return RegistroConsolidacion
     */
    public function setEsonline($esonline)
    {
        $this->esonline = $esonline;
    
        return $this;
    }

    /**
     * Get esonline
     *
     * @return boolean 
     */
    public function getEsonline()
    {
        return $this->esonline;
    }

    /**
     * Set institucioneducativaTipoId
     *
     * @param integer $institucioneducativaTipoId
     * @return RegistroConsolidacion
     */
    public function setInstitucioneducativaTipoId($institucioneducativaTipoId)
    {
        $this->institucioneducativaTipoId = $institucioneducativaTipoId;
    
        return $this;
    }

    /**
     * Get institucioneducativaTipoId
     *
     * @return integer 
     */
    public function getInstitucioneducativaTipoId()
    {
        return $this->institucioneducativaTipoId;
    }
}
