<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteDiscapacidadCertificado
 */
class EstudianteDiscapacidadCertificado
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $departamentoRegistro;

    /**
     * @var string
     */
    private $cedulaIdentidad;

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
    private $fechaNacimiento;

    /**
     * @var string
     */
    private $sexo;

    /**
     * @var string
     */
    private $celular;

    /**
     * @var string
     */
    private $direccion;

    /**
     * @var string
     */
    private $certificados;

    /**
     * @var integer
     */
    private $esValidado;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Estudiante
     */
    private $estudiante;


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
     * Set departamentoRegistro
     *
     * @param string $departamentoRegistro
     * @return EstudianteDiscapacidadCertificado
     */
    public function setDepartamentoRegistro($departamentoRegistro)
    {
        $this->departamentoRegistro = $departamentoRegistro;
    
        return $this;
    }

    /**
     * Get departamentoRegistro
     *
     * @return string 
     */
    public function getDepartamentoRegistro()
    {
        return $this->departamentoRegistro;
    }

    /**
     * Set cedulaIdentidad
     *
     * @param string $cedulaIdentidad
     * @return EstudianteDiscapacidadCertificado
     */
    public function setCedulaIdentidad($cedulaIdentidad)
    {
        $this->cedulaIdentidad = $cedulaIdentidad;
    
        return $this;
    }

    /**
     * Get cedulaIdentidad
     *
     * @return string 
     */
    public function getCedulaIdentidad()
    {
        return $this->cedulaIdentidad;
    }

    /**
     * Set paterno
     *
     * @param string $paterno
     * @return EstudianteDiscapacidadCertificado
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
     * @return EstudianteDiscapacidadCertificado
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
     * @return EstudianteDiscapacidadCertificado
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
     * Set fechaNacimiento
     *
     * @param string $fechaNacimiento
     * @return EstudianteDiscapacidadCertificado
     */
    public function setFechaNacimiento($fechaNacimiento)
    {
        $this->fechaNacimiento = $fechaNacimiento;
    
        return $this;
    }

    /**
     * Get fechaNacimiento
     *
     * @return string 
     */
    public function getFechaNacimiento()
    {
        return $this->fechaNacimiento;
    }

    /**
     * Set sexo
     *
     * @param string $sexo
     * @return EstudianteDiscapacidadCertificado
     */
    public function setSexo($sexo)
    {
        $this->sexo = $sexo;
    
        return $this;
    }

    /**
     * Get sexo
     *
     * @return string 
     */
    public function getSexo()
    {
        return $this->sexo;
    }

    /**
     * Set celular
     *
     * @param string $celular
     * @return EstudianteDiscapacidadCertificado
     */
    public function setCelular($celular)
    {
        $this->celular = $celular;
    
        return $this;
    }

    /**
     * Get celular
     *
     * @return string 
     */
    public function getCelular()
    {
        return $this->celular;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     * @return EstudianteDiscapacidadCertificado
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    
        return $this;
    }

    /**
     * Get direccion
     *
     * @return string 
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set certificados
     *
     * @param string $certificados
     * @return EstudianteDiscapacidadCertificado
     */
    public function setCertificados($certificados)
    {
        $this->certificados = $certificados;
    
        return $this;
    }

    /**
     * Get certificados
     *
     * @return string 
     */
    public function getCertificados()
    {
        return $this->certificados;
    }

    /**
     * Set esValidado
     *
     * @param integer $esValidado
     * @return EstudianteDiscapacidadCertificado
     */
    public function setEsValidado($esValidado)
    {
        $this->esValidado = $esValidado;
    
        return $this;
    }

    /**
     * Get esValidado
     *
     * @return integer 
     */
    public function getEsValidado()
    {
        return $this->esValidado;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstudianteDiscapacidadCertificado
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
     * @return EstudianteDiscapacidadCertificado
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
     * Set estudiante
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $estudiante
     * @return EstudianteDiscapacidadCertificado
     */
    public function setEstudiante(\Sie\AppWebBundle\Entity\Estudiante $estudiante = null)
    {
        $this->estudiante = $estudiante;
    
        return $this;
    }

    /**
     * Get estudiante
     *
     * @return \Sie\AppWebBundle\Entity\Estudiante 
     */
    public function getEstudiante()
    {
        return $this->estudiante;
    }
}
