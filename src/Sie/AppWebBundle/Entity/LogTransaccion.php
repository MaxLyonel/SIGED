<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LogTransaccion
 */
class LogTransaccion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $key;

    /**
     * @var string
     */
    private $tabla;

    /**
     * @var \DateTime
     */
    private $fecha;

    /**
     * @var string
     */
    private $tipoTransaccion;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var string
     */
    private $valorNuevo;

    /**
     * @var string
     */
    private $valorAnt;

    /**
     * @var string
     */
    private $sistema;

    /**
     * @var string
     */
    private $archivo;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;


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
     * Set key
     *
     * @param integer $key
     * @return LogTransaccion
     */
    public function setKey($key)
    {
        $this->key = $key;
    
        return $this;
    }

    /**
     * Get key
     *
     * @return integer 
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set tabla
     *
     * @param string $tabla
     * @return LogTransaccion
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
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return LogTransaccion
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
     * Set tipoTransaccion
     *
     * @param string $tipoTransaccion
     * @return LogTransaccion
     */
    public function setTipoTransaccion($tipoTransaccion)
    {
        $this->tipoTransaccion = $tipoTransaccion;
    
        return $this;
    }

    /**
     * Get tipoTransaccion
     *
     * @return string 
     */
    public function getTipoTransaccion()
    {
        return $this->tipoTransaccion;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return LogTransaccion
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
     * Set observacion
     *
     * @param string $observacion
     * @return LogTransaccion
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
     * Set valorNuevo
     *
     * @param string $valorNuevo
     * @return LogTransaccion
     */
    public function setValorNuevo($valorNuevo)
    {
        $this->valorNuevo = $valorNuevo;
    
        return $this;
    }

    /**
     * Get valorNuevo
     *
     * @return string 
     */
    public function getValorNuevo()
    {
        return $this->valorNuevo;
    }

    /**
     * Set valorAnt
     *
     * @param string $valorAnt
     * @return LogTransaccion
     */
    public function setValorAnt($valorAnt)
    {
        $this->valorAnt = $valorAnt;
    
        return $this;
    }

    /**
     * Get valorAnt
     *
     * @return string 
     */
    public function getValorAnt()
    {
        return $this->valorAnt;
    }

    /**
     * Set sistema
     *
     * @param string $sistema
     * @return LogTransaccion
     */
    public function setSistema($sistema)
    {
        $this->sistema = $sistema;
    
        return $this;
    }

    /**
     * Get sistema
     *
     * @return string 
     */
    public function getSistema()
    {
        return $this->sistema;
    }

    /**
     * Set archivo
     *
     * @param string $archivo
     * @return LogTransaccion
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
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return LogTransaccion
     */
    public function setUsuario(\Sie\AppWebBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
}
