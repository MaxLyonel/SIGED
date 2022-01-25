<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteDestacado
 */
class EstudianteDestacado
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $estudianteInscripcionId;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var string
     */
    private $ipOrigen;

    /**
     * @var boolean
     */
    private $impreso;

    /**
     * @var integer
     */
    private $generoTipoId;

    /**
     * @var \DateTime
     */
    private $fechaEnvio;

    /**
     * @var string
     */
    private $lote;

    /**
     * @var string
     */
    private $promedioFinal;

    /**
     * @var integer
     */
    private $institucioneducativaId;

    /**
     * @var integer
     */
    private $estudianteId;

    /**
     * @var boolean
     */
    private $esoficial;


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
     * Set estudianteInscripcionId
     *
     * @param integer $estudianteInscripcionId
     * @return EstudianteDestacado
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
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return EstudianteDestacado
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstudianteDestacado
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
     * Set ipOrigen
     *
     * @param string $ipOrigen
     * @return EstudianteDestacado
     */
    public function setIpOrigen($ipOrigen)
    {
        $this->ipOrigen = $ipOrigen;
    
        return $this;
    }

    /**
     * Get ipOrigen
     *
     * @return string 
     */
    public function getIpOrigen()
    {
        return $this->ipOrigen;
    }

    /**
     * Set impreso
     *
     * @param boolean $impreso
     * @return EstudianteDestacado
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
     * Set generoTipoId
     *
     * @param integer $generoTipoId
     * @return EstudianteDestacado
     */
    public function setGeneroTipoId($generoTipoId)
    {
        $this->generoTipoId = $generoTipoId;
    
        return $this;
    }

    /**
     * Get generoTipoId
     *
     * @return integer 
     */
    public function getGeneroTipoId()
    {
        return $this->generoTipoId;
    }

    /**
     * Set fechaEnvio
     *
     * @param \DateTime $fechaEnvio
     * @return EstudianteDestacado
     */
    public function setFechaEnvio($fechaEnvio)
    {
        $this->fechaEnvio = $fechaEnvio;
    
        return $this;
    }

    /**
     * Get fechaEnvio
     *
     * @return \DateTime 
     */
    public function getFechaEnvio()
    {
        return $this->fechaEnvio;
    }

    /**
     * Set lote
     *
     * @param string $lote
     * @return EstudianteDestacado
     */
    public function setLote($lote)
    {
        $this->lote = $lote;
    
        return $this;
    }

    /**
     * Get lote
     *
     * @return string 
     */
    public function getLote()
    {
        return $this->lote;
    }

    /**
     * Set promedioFinal
     *
     * @param string $promedioFinal
     * @return EstudianteDestacado
     */
    public function setPromedioFinal($promedioFinal)
    {
        $this->promedioFinal = $promedioFinal;
    
        return $this;
    }

    /**
     * Get promedioFinal
     *
     * @return string 
     */
    public function getPromedioFinal()
    {
        return $this->promedioFinal;
    }

    /**
     * Set institucioneducativaId
     *
     * @param integer $institucioneducativaId
     * @return EstudianteDestacado
     */
    public function setInstitucioneducativaId($institucioneducativaId)
    {
        $this->institucioneducativaId = $institucioneducativaId;
    
        return $this;
    }

    /**
     * Get institucioneducativaId
     *
     * @return integer 
     */
    public function getInstitucioneducativaId()
    {
        return $this->institucioneducativaId;
    }

    /**
     * Set estudianteId
     *
     * @param integer $estudianteId
     * @return EstudianteDestacado
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
     * Set esoficial
     *
     * @param boolean $esoficial
     * @return EstudianteDestacado
     */
    public function setEsoficial($esoficial)
    {
        $this->esoficial = $esoficial;
    
        return $this;
    }

    /**
     * Get esoficial
     *
     * @return boolean 
     */
    public function getEsoficial()
    {
        return $this->esoficial;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\GeneroTipo
     */
    private $generoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;


    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return EstudianteDestacado
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
     * Set generoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GeneroTipo $generoTipo
     * @return EstudianteDestacado
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

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return EstudianteDestacado
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
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return EstudianteDestacado
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
    /**
     * @var \Sie\AppWebBundle\Entity\Estudiante
     */
    private $estudiante;


    /**
     * Set estudiante
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $estudiante
     * @return EstudianteDestacado
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
    /**
     * @var string
     */
    private $nombre;

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
    private $carnetIdentidad;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var string
     */
    private $codigoRude;

    /**
     * @var integer
     */
    private $orgcurricularTipoId;

    /**
     * @var string
     */
    private $promedioSem1;

    /**
     * @var string
     */
    private $promedioSem2;


    /**
     * Set nombre
     *
     * @param string $nombre
     * @return EstudianteDestacado
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
     * Set paterno
     *
     * @param string $paterno
     * @return EstudianteDestacado
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
     * @return EstudianteDestacado
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
     * Set carnetIdentidad
     *
     * @param string $carnetIdentidad
     * @return EstudianteDestacado
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
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return EstudianteDestacado
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
     * Set codigoRude
     *
     * @param string $codigoRude
     * @return EstudianteDestacado
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
     * Set orgcurricularTipoId
     *
     * @param integer $orgcurricularTipoId
     * @return EstudianteDestacado
     */
    public function setOrgcurricularTipoId($orgcurricularTipoId)
    {
        $this->orgcurricularTipoId = $orgcurricularTipoId;
    
        return $this;
    }

    /**
     * Get orgcurricularTipoId
     *
     * @return integer 
     */
    public function getOrgcurricularTipoId()
    {
        return $this->orgcurricularTipoId;
    }

    /**
     * Set promedioSem1
     *
     * @param string $promedioSem1
     * @return EstudianteDestacado
     */
    public function setPromedioSem1($promedioSem1)
    {
        $this->promedioSem1 = $promedioSem1;
    
        return $this;
    }

    /**
     * Get promedioSem1
     *
     * @return string 
     */
    public function getPromedioSem1()
    {
        return $this->promedioSem1;
    }

    /**
     * Set promedioSem2
     *
     * @param string $promedioSem2
     * @return EstudianteDestacado
     */
    public function setPromedioSem2($promedioSem2)
    {
        $this->promedioSem2 = $promedioSem2;
    
        return $this;
    }

    /**
     * Get promedioSem2
     *
     * @return string 
     */
    public function getPromedioSem2()
    {
        return $this->promedioSem2;
    }
    /**
     * @var \DateTime
     */
    private $fechaNacimiento;

    /**
     * @var string
     */
    private $complemento;


    /**
     * Set fechaNacimiento
     *
     * @param \DateTime $fechaNacimiento
     * @return EstudianteDestacado
     */
    public function setFechaNacimiento($fechaNacimiento)
    {
        $this->fechaNacimiento = $fechaNacimiento;
    
        return $this;
    }

    /**
     * Get fechaNacimiento
     *
     * @return \DateTime 
     */
    public function getFechaNacimiento()
    {
        return $this->fechaNacimiento;
    }

    /**
     * Set complemento
     *
     * @param string $complemento
     * @return EstudianteDestacado
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
}
