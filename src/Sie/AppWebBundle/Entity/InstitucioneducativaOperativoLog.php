<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaOperativoLog
 */
class InstitucioneducativaOperativoLog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var integer
     */
    private $institucioneducativaSucursal;

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
     * @var integer
     */
    private $consolidacionValor;

    /**
     * @var string
     */
    private $consultaSql;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var boolean
     */
    private $esexitoso;

    /**
     * @var boolean
     */
    private $esonline;

    /**
     * @var string
     */
    private $usuario;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var string
     */
    private $clienteDescripcion;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\NotaTipo
     */
    private $notaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\PeriodoTipo
     */
    private $periodoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLogTipo
     */
    private $institucioneducativaOperativoLogTipo;


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
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return InstitucioneducativaOperativoLog
     */
    public function setGestionTipoId($gestionTipoId)
    {
        $this->gestionTipoId = $gestionTipoId;
    
        return $this;
    }

    /**
     * Get gestionTipoId
     *
     * @return integer 
     */
    public function getGestionTipoId()
    {
        return $this->gestionTipoId;
    }

    /**
     * Set institucioneducativaSucursal
     *
     * @param integer $institucioneducativaSucursal
     * @return InstitucioneducativaOperativoLog
     */
    public function setInstitucioneducativaSucursal($institucioneducativaSucursal)
    {
        $this->institucioneducativaSucursal = $institucioneducativaSucursal;
    
        return $this;
    }

    /**
     * Get institucioneducativaSucursal
     *
     * @return integer 
     */
    public function getInstitucioneducativaSucursal()
    {
        return $this->institucioneducativaSucursal;
    }

    /**
     * Set tabla
     *
     * @param string $tabla
     * @return InstitucioneducativaOperativoLog
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
     * @return InstitucioneducativaOperativoLog
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
     * @return InstitucioneducativaOperativoLog
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
     * Set consolidacionValor
     *
     * @param integer $consolidacionValor
     * @return InstitucioneducativaOperativoLog
     */
    public function setConsolidacionValor($consolidacionValor)
    {
        $this->consolidacionValor = $consolidacionValor;
    
        return $this;
    }

    /**
     * Get consolidacionValor
     *
     * @return integer 
     */
    public function getConsolidacionValor()
    {
        return $this->consolidacionValor;
    }

    /**
     * Set consultaSql
     *
     * @param string $consultaSql
     * @return InstitucioneducativaOperativoLog
     */
    public function setConsultaSql($consultaSql)
    {
        $this->consultaSql = $consultaSql;
    
        return $this;
    }

    /**
     * Get consultaSql
     *
     * @return string 
     */
    public function getConsultaSql()
    {
        return $this->consultaSql;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return InstitucioneducativaOperativoLog
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set esexitoso
     *
     * @param boolean $esexitoso
     * @return InstitucioneducativaOperativoLog
     */
    public function setEsexitoso($esexitoso)
    {
        $this->esexitoso = $esexitoso;
    
        return $this;
    }

    /**
     * Get esexitoso
     *
     * @return boolean 
     */
    public function getEsexitoso()
    {
        return $this->esexitoso;
    }

    /**
     * Set esonline
     *
     * @param boolean $esonline
     * @return InstitucioneducativaOperativoLog
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
     * Set usuario
     *
     * @param string $usuario
     * @return InstitucioneducativaOperativoLog
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return InstitucioneducativaOperativoLog
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
     * Set clienteDescripcion
     *
     * @param string $clienteDescripcion
     * @return InstitucioneducativaOperativoLog
     */
    public function setClienteDescripcion($clienteDescripcion)
    {
        $this->clienteDescripcion = $clienteDescripcion;
    
        return $this;
    }

    /**
     * Get clienteDescripcion
     *
     * @return string 
     */
    public function getClienteDescripcion()
    {
        return $this->clienteDescripcion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InstitucioneducativaOperativoLog
     */
    public function setObs($obs)
    {
        $this->obs = $obs;
    
        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs()
    {
        return $this->obs;
    }

    /**
     * Set notaTipo
     *
     * @param \Sie\AppWebBundle\Entity\NotaTipo $notaTipo
     * @return InstitucioneducativaOperativoLog
     */
    public function setNotaTipo(\Sie\AppWebBundle\Entity\NotaTipo $notaTipo = null)
    {
        $this->notaTipo = $notaTipo;
    
        return $this;
    }

    /**
     * Get notaTipo
     *
     * @return \Sie\AppWebBundle\Entity\NotaTipo 
     */
    public function getNotaTipo()
    {
        return $this->notaTipo;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaOperativoLog
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
     * Set periodoTipo
     *
     * @param \Sie\AppWebBundle\Entity\PeriodoTipo $periodoTipo
     * @return InstitucioneducativaOperativoLog
     */
    public function setPeriodoTipo(\Sie\AppWebBundle\Entity\PeriodoTipo $periodoTipo = null)
    {
        $this->periodoTipo = $periodoTipo;
    
        return $this;
    }

    /**
     * Get periodoTipo
     *
     * @return \Sie\AppWebBundle\Entity\PeriodoTipo 
     */
    public function getPeriodoTipo()
    {
        return $this->periodoTipo;
    }

    /**
     * Set institucioneducativaOperativoLogTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLogTipo $institucioneducativaOperativoLogTipo
     * @return InstitucioneducativaOperativoLog
     */
    public function setInstitucioneducativaOperativoLogTipo(\Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLogTipo $institucioneducativaOperativoLogTipo = null)
    {
        $this->institucioneducativaOperativoLogTipo = $institucioneducativaOperativoLogTipo;
    
        return $this;
    }

    /**
     * Get institucioneducativaOperativoLogTipo
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLogTipo 
     */
    public function getInstitucioneducativaOperativoLogTipo()
    {
        return $this->institucioneducativaOperativoLogTipo;
    }
}
