<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpEstudianteInscripcionJuegos
 */
class JdpEstudianteInscripcionJuegos
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
     * @var float
     */
    private $distancia;

    /**
     * @var string
     */
    private $obs;

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
     * @var \Sie\AppWebBundle\Entity\JdpPruebaTipo
     */
    private $pruebaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpFaseTipo
     */
    private $faseTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;


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
     * @return JdpEstudianteInscripcionJuegos
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
     * Set distancia
     *
     * @param float $distancia
     * @return JdpEstudianteInscripcionJuegos
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
     * Set obs
     *
     * @param string $obs
     * @return JdpEstudianteInscripcionJuegos
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return JdpEstudianteInscripcionJuegos
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
     * @return JdpEstudianteInscripcionJuegos
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
     * @return JdpEstudianteInscripcionJuegos
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
     * Set puntaje
     *
     * @param float $puntaje
     * @return JdpEstudianteInscripcionJuegos
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
     * @return JdpEstudianteInscripcionJuegos
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
     * @return JdpEstudianteInscripcionJuegos
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
     * @return JdpEstudianteInscripcionJuegos
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
     * Set pruebaTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpPruebaTipo $pruebaTipo
     * @return JdpEstudianteInscripcionJuegos
     */
    public function setPruebaTipo(\Sie\AppWebBundle\Entity\JdpPruebaTipo $pruebaTipo = null)
    {
        $this->pruebaTipo = $pruebaTipo;
    
        return $this;
    }

    /**
     * Get pruebaTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpPruebaTipo 
     */
    public function getPruebaTipo()
    {
        return $this->pruebaTipo;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return JdpEstudianteInscripcionJuegos
     */
    public function setEstudianteInscripcion(\Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion = null)
    {
        $this->estudianteInscripcion = $estudianteInscripcion;
    
        return $this;
    }

    /**
     * Get estudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcion 
     */
    public function getEstudianteInscripcion()
    {
        return $this->estudianteInscripcion;
    }

    /**
     * Set faseTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpFaseTipo $faseTipo
     * @return JdpEstudianteInscripcionJuegos
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

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return JdpEstudianteInscripcionJuegos
     */
    public function setGestionTipo(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipo = null)
    {
        $this->gestionTipo = $gestionTipo;
    
        return $this;
    }

    /**
     * Get gestionTipo
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipo()
    {
        return $this->gestionTipo;
    }
}
