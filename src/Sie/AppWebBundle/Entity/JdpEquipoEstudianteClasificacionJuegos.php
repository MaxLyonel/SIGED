<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpEquipoEstudianteClasificacionJuegos
 */
class JdpEquipoEstudianteClasificacionJuegos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $posicion;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var integer
     */
    private $usuarioId;

    /**
     * @var float
     */
    private $distancia;

    /**
     * @var float
     */
    private $puntaje;

    /**
     * @var string
     */
    private $marca;

    /**
     * @var boolean
     */
    private $impreso;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpEquipoEstudianteInscripcionJuegos
     */
    private $equipoEstudianteInscripcionJuegos;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpFaseTipo
     */
    private $faseTipo;


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
     * Set posicion
     *
     * @param integer $posicion
     * @return JdpEquipoEstudianteClasificacionJuegos
     */
    public function setPosicion($posicion)
    {
        $this->posicion = $posicion;
    
        return $this;
    }

    /**
     * Get posicion
     *
     * @return integer 
     */
    public function getPosicion()
    {
        return $this->posicion;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return JdpEquipoEstudianteClasificacionJuegos
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
     * @return JdpEquipoEstudianteClasificacionJuegos
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
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return JdpEquipoEstudianteClasificacionJuegos
     */
    public function setUsuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    
        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return integer 
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
    }

    /**
     * Set distancia
     *
     * @param float $distancia
     * @return JdpEquipoEstudianteClasificacionJuegos
     */
    public function setDistancia($distancia)
    {
        $this->distancia = $distancia;
    
        return $this;
    }

    /**
     * Get distancia
     *
     * @return float 
     */
    public function getDistancia()
    {
        return $this->distancia;
    }

    /**
     * Set puntaje
     *
     * @param float $puntaje
     * @return JdpEquipoEstudianteClasificacionJuegos
     */
    public function setPuntaje($puntaje)
    {
        $this->puntaje = $puntaje;
    
        return $this;
    }

    /**
     * Get puntaje
     *
     * @return float 
     */
    public function getPuntaje()
    {
        return $this->puntaje;
    }

    /**
     * Set marca
     *
     * @param string $marca
     * @return JdpEquipoEstudianteClasificacionJuegos
     */
    public function setMarca($marca)
    {
        $this->marca = $marca;
    
        return $this;
    }

    /**
     * Get marca
     *
     * @return string 
     */
    public function getMarca()
    {
        return $this->marca;
    }

    /**
     * Set impreso
     *
     * @param boolean $impreso
     * @return JdpEquipoEstudianteClasificacionJuegos
     */
    public function setImpreso($impreso)
    {
        $this->impreso = $impreso;
    
        return $this;
    }

    /**
     * Get impreso
     *
     * @return boolean 
     */
    public function getImpreso()
    {
        return $this->impreso;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return JdpEquipoEstudianteClasificacionJuegos
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }

    /**
     * Set equipoEstudianteInscripcionJuegos
     *
     * @param \Sie\AppWebBundle\Entity\JdpEquipoEstudianteInscripcionJuegos $equipoEstudianteInscripcionJuegos
     * @return JdpEquipoEstudianteClasificacionJuegos
     */
    public function setEquipoEstudianteInscripcionJuegos(\Sie\AppWebBundle\Entity\JdpEquipoEstudianteInscripcionJuegos $equipoEstudianteInscripcionJuegos = null)
    {
        $this->equipoEstudianteInscripcionJuegos = $equipoEstudianteInscripcionJuegos;
    
        return $this;
    }

    /**
     * Get equipoEstudianteInscripcionJuegos
     *
     * @return \Sie\AppWebBundle\Entity\JdpEquipoEstudianteInscripcionJuegos 
     */
    public function getEquipoEstudianteInscripcionJuegos()
    {
        return $this->equipoEstudianteInscripcionJuegos;
    }

    /**
     * Set faseTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpFaseTipo $faseTipo
     * @return JdpEquipoEstudianteClasificacionJuegos
     */
    public function setFaseTipo(\Sie\AppWebBundle\Entity\JdpFaseTipo $faseTipo = null)
    {
        $this->faseTipo = $faseTipo;
    
        return $this;
    }

    /**
     * Get faseTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpFaseTipo 
     */
    public function getFaseTipo()
    {
        return $this->faseTipo;
    }
}
