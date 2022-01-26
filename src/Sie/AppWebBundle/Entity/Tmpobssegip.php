<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tmpobssegip
 */
class Tmpobssegip
{
    /**
     * @var integer
     */
    private $idest;

    /**
     * @var string
     */
    private $complementovisible;

    /**
     * @var string
     */
    private $numerodocumento;

    /**
     * @var string
     */
    private $complemento;

    /**
     * @var string
     */
    private $nombres;

    /**
     * @var string
     */
    private $primerapellido;

    /**
     * @var string
     */
    private $segundoapellido;

    /**
     * @var string
     */
    private $apellidoesposo;

    /**
     * @var string
     */
    private $fechanacimiento;


    /**
     * Get idest
     *
     * @return integer 
     */
    public function getIdest()
    {
        return $this->idest;
    }

    /**
     * Set complementovisible
     *
     * @param string $complementovisible
     * @return Tmpobssegip
     */
    public function setComplementovisible($complementovisible)
    {
        $this->complementovisible = $complementovisible;
    
        return $this;
    }

    /**
     * Get complementovisible
     *
     * @return string 
     */
    public function getComplementovisible()
    {
        return $this->complementovisible;
    }

    /**
     * Set numerodocumento
     *
     * @param string $numerodocumento
     * @return Tmpobssegip
     */
    public function setNumerodocumento($numerodocumento)
    {
        $this->numerodocumento = $numerodocumento;
    
        return $this;
    }

    /**
     * Get numerodocumento
     *
     * @return string 
     */
    public function getNumerodocumento()
    {
        return $this->numerodocumento;
    }

    /**
     * Set complemento
     *
     * @param string $complemento
     * @return Tmpobssegip
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
     * Set nombres
     *
     * @param string $nombres
     * @return Tmpobssegip
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
     * Set primerapellido
     *
     * @param string $primerapellido
     * @return Tmpobssegip
     */
    public function setPrimerapellido($primerapellido)
    {
        $this->primerapellido = $primerapellido;
    
        return $this;
    }

    /**
     * Get primerapellido
     *
     * @return string 
     */
    public function getPrimerapellido()
    {
        return $this->primerapellido;
    }

    /**
     * Set segundoapellido
     *
     * @param string $segundoapellido
     * @return Tmpobssegip
     */
    public function setSegundoapellido($segundoapellido)
    {
        $this->segundoapellido = $segundoapellido;
    
        return $this;
    }

    /**
     * Get segundoapellido
     *
     * @return string 
     */
    public function getSegundoapellido()
    {
        return $this->segundoapellido;
    }

    /**
     * Set apellidoesposo
     *
     * @param string $apellidoesposo
     * @return Tmpobssegip
     */
    public function setApellidoesposo($apellidoesposo)
    {
        $this->apellidoesposo = $apellidoesposo;
    
        return $this;
    }

    /**
     * Get apellidoesposo
     *
     * @return string 
     */
    public function getApellidoesposo()
    {
        return $this->apellidoesposo;
    }

    /**
     * Set fechanacimiento
     *
     * @param string $fechanacimiento
     * @return Tmpobssegip
     */
    public function setFechanacimiento($fechanacimiento)
    {
        $this->fechanacimiento = $fechanacimiento;
    
        return $this;
    }

    /**
     * Get fechanacimiento
     *
     * @return string 
     */
    public function getFechanacimiento()
    {
        return $this->fechanacimiento;
    }
    /**
     * @var string
     */
    private $esvalido;

    /**
     * @var string
     */
    private $mensaje;

    /**
     * @var string
     */
    private $tipomensaje;

    /**
     * @var string
     */
    private $codigorespuesta;

    /**
     * @var string
     */
    private $codigounico;

    /**
     * @var string
     */
    private $descripcionrespuesta;


    /**
     * Set esvalido
     *
     * @param string $esvalido
     * @return Tmpobssegip
     */
    public function setEsvalido($esvalido)
    {
        $this->esvalido = $esvalido;
    
        return $this;
    }

    /**
     * Get esvalido
     *
     * @return string 
     */
    public function getEsvalido()
    {
        return $this->esvalido;
    }

    /**
     * Set mensaje
     *
     * @param string $mensaje
     * @return Tmpobssegip
     */
    public function setMensaje($mensaje)
    {
        $this->mensaje = $mensaje;
    
        return $this;
    }

    /**
     * Get mensaje
     *
     * @return string 
     */
    public function getMensaje()
    {
        return $this->mensaje;
    }

    /**
     * Set tipomensaje
     *
     * @param string $tipomensaje
     * @return Tmpobssegip
     */
    public function setTipomensaje($tipomensaje)
    {
        $this->tipomensaje = $tipomensaje;
    
        return $this;
    }

    /**
     * Get tipomensaje
     *
     * @return string 
     */
    public function getTipomensaje()
    {
        return $this->tipomensaje;
    }

    /**
     * Set codigorespuesta
     *
     * @param string $codigorespuesta
     * @return Tmpobssegip
     */
    public function setCodigorespuesta($codigorespuesta)
    {
        $this->codigorespuesta = $codigorespuesta;
    
        return $this;
    }

    /**
     * Get codigorespuesta
     *
     * @return string 
     */
    public function getCodigorespuesta()
    {
        return $this->codigorespuesta;
    }

    /**
     * Set codigounico
     *
     * @param string $codigounico
     * @return Tmpobssegip
     */
    public function setCodigounico($codigounico)
    {
        $this->codigounico = $codigounico;
    
        return $this;
    }

    /**
     * Get codigounico
     *
     * @return string 
     */
    public function getCodigounico()
    {
        return $this->codigounico;
    }

    /**
     * Set descripcionrespuesta
     *
     * @param string $descripcionrespuesta
     * @return Tmpobssegip
     */
    public function setDescripcionrespuesta($descripcionrespuesta)
    {
        $this->descripcionrespuesta = $descripcionrespuesta;
    
        return $this;
    }

    /**
     * Get descripcionrespuesta
     *
     * @return string 
     */
    public function getDescripcionrespuesta()
    {
        return $this->descripcionrespuesta;
    }
}
