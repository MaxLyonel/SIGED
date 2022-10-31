<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionCursoCorto
 */
class EstudianteInscripcionCursoCorto
{
    /**
     * @var integer
     */
    private $id;

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
     * @var integer
     */
    private $edad;

    /**
     * @var string
     */
    private $carnetIdentidad;

    /**
     * @var string
     */
    private $organizacionComunidad;

    /**
     * @var string
     */
    private $complemento;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaCursoCorto
     */
    private $institucioneducativaCursoCorto;

    /**
     * @var \Sie\AppWebBundle\Entity\GeneroTipo
     */
    private $generoTipo;


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
     * Set paterno
     *
     * @param string $paterno
     * @return EstudianteInscripcionCursoCorto
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
     * @return EstudianteInscripcionCursoCorto
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
     * @return EstudianteInscripcionCursoCorto
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
     * Set edad
     *
     * @param integer $edad
     * @return EstudianteInscripcionCursoCorto
     */
    public function setEdad($edad)
    {
        $this->edad = $edad;
    
        return $this;
    }

    /**
     * Get edad
     *
     * @return integer 
     */
    public function getEdad()
    {
        return $this->edad;
    }

    /**
     * Set carnetIdentidad
     *
     * @param string $carnetIdentidad
     * @return EstudianteInscripcionCursoCorto
     */
    public function setCarnetIdentidad($carnetIdentidad)
    {
        $this->carnetIdentidad = $carnetIdentidad;
    
        return $this;
    }

    /**
     * Get carnetIdentidad
     *
     * @return string 
     */
    public function getCarnetIdentidad()
    {
        return $this->carnetIdentidad;
    }

    /**
     * Set organizacionComunidad
     *
     * @param string $organizacionComunidad
     * @return EstudianteInscripcionCursoCorto
     */
    public function setOrganizacionComunidad($organizacionComunidad)
    {
        $this->organizacionComunidad = $organizacionComunidad;
    
        return $this;
    }

    /**
     * Get organizacionComunidad
     *
     * @return string 
     */
    public function getOrganizacionComunidad()
    {
        return $this->organizacionComunidad;
    }

    /**
     * Set complemento
     *
     * @param string $complemento
     * @return EstudianteInscripcionCursoCorto
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
     * Set institucioneducativaCursoCorto
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaCursoCorto $institucioneducativaCursoCorto
     * @return EstudianteInscripcionCursoCorto
     */
    public function setInstitucioneducativaCursoCorto(\Sie\AppWebBundle\Entity\InstitucioneducativaCursoCorto $institucioneducativaCursoCorto = null)
    {
        $this->institucioneducativaCursoCorto = $institucioneducativaCursoCorto;
    
        return $this;
    }

    /**
     * Get institucioneducativaCursoCorto
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaCursoCorto 
     */
    public function getInstitucioneducativaCursoCorto()
    {
        return $this->institucioneducativaCursoCorto;
    }

    /**
     * Set generoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GeneroTipo $generoTipo
     * @return EstudianteInscripcionCursoCorto
     */
    public function setGeneroTipo(\Sie\AppWebBundle\Entity\GeneroTipo $generoTipo = null)
    {
        $this->generoTipo = $generoTipo;
    
        return $this;
    }

    /**
     * Get generoTipo
     *
     * @return \Sie\AppWebBundle\Entity\GeneroTipo 
     */
    public function getGeneroTipo()
    {
        return $this->generoTipo;
    }
}
