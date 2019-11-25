<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MaestroCuentabancaria
 */
class MaestroCuentabancaria
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $personaId;

    /**
     * @var integer
     */
    private $maestroInscripcionId;

    /**
     * @var integer
     */
    private $institucioneducativaId;

    /**
     * @var integer
     */
    private $cargoTipoId;

    /**
     * @var integer
     */
    private $entidadfinancieraTipoId;

    /**
     * @var string
     */
    private $cuentabancaria;

    /**
     * @var string
     */
    private $carnet;

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
    private $complemento;

    /**
     * @var boolean
     */
    private $esactivo;

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
     * Set personaId
     *
     * @param integer $personaId
     * @return MaestroCuentabancaria
     */
    public function setPersonaId($personaId)
    {
        $this->personaId = $personaId;
    
        return $this;
    }

    /**
     * Get personaId
     *
     * @return integer 
     */
    public function getPersonaId()
    {
        return $this->personaId;
    }

    /**
     * Set maestroInscripcionId
     *
     * @param integer $maestroInscripcionId
     * @return MaestroCuentabancaria
     */
    public function setMaestroInscripcionId($maestroInscripcionId)
    {
        $this->maestroInscripcionId = $maestroInscripcionId;
    
        return $this;
    }

    /**
     * Get maestroInscripcionId
     *
     * @return integer 
     */
    public function getMaestroInscripcionId()
    {
        return $this->maestroInscripcionId;
    }

    /**
     * Set institucioneducativaId
     *
     * @param integer $institucioneducativaId
     * @return MaestroCuentabancaria
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
     * Set cargoTipoId
     *
     * @param integer $cargoTipoId
     * @return MaestroCuentabancaria
     */
    public function setCargoTipoId($cargoTipoId)
    {
        $this->cargoTipoId = $cargoTipoId;
    
        return $this;
    }

    /**
     * Get cargoTipoId
     *
     * @return integer 
     */
    public function getCargoTipoId()
    {
        return $this->cargoTipoId;
    }

    /**
     * Set entidadfinancieraTipoId
     *
     * @param integer $entidadfinancieraTipoId
     * @return MaestroCuentabancaria
     */
    public function setEntidadfinancieraTipoId($entidadfinancieraTipoId)
    {
        $this->entidadfinancieraTipoId = $entidadfinancieraTipoId;
    
        return $this;
    }

    /**
     * Get entidadfinancieraTipoId
     *
     * @return integer 
     */
    public function getEntidadfinancieraTipoId()
    {
        return $this->entidadfinancieraTipoId;
    }

    /**
     * Set cuentabancaria
     *
     * @param string $cuentabancaria
     * @return MaestroCuentabancaria
     */
    public function setCuentabancaria($cuentabancaria)
    {
        $this->cuentabancaria = $cuentabancaria;
    
        return $this;
    }

    /**
     * Get cuentabancaria
     *
     * @return string 
     */
    public function getCuentabancaria()
    {
        return $this->cuentabancaria;
    }

    /**
     * Set carnet
     *
     * @param string $carnet
     * @return MaestroCuentabancaria
     */
    public function setCarnet($carnet)
    {
        $this->carnet = $carnet;
    
        return $this;
    }

    /**
     * Get carnet
     *
     * @return string 
     */
    public function getCarnet()
    {
        return $this->carnet;
    }

    /**
     * Set paterno
     *
     * @param string $paterno
     * @return MaestroCuentabancaria
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
     * @return MaestroCuentabancaria
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
     * @return MaestroCuentabancaria
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
     * Set complemento
     *
     * @param string $complemento
     * @return MaestroCuentabancaria
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
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return MaestroCuentabancaria
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
     * Set esoficial
     *
     * @param boolean $esoficial
     * @return MaestroCuentabancaria
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
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\CargoTipo
     */
    private $cargoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EntidadfinancieraTipo
     */
    private $entidadfinancieraTipo;


    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return MaestroCuentabancaria
     */
    public function setPersona(\Sie\AppWebBundle\Entity\Persona $persona = null)
    {
        $this->persona = $persona;
    
        return $this;
    }

    /**
     * Get persona
     *
     * @return \Sie\AppWebBundle\Entity\Persona 
     */
    public function getPersona()
    {
        return $this->persona;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return MaestroCuentabancaria
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
     * Set cargoTipo
     *
     * @param \Sie\AppWebBundle\Entity\CargoTipo $cargoTipo
     * @return MaestroCuentabancaria
     */
    public function setCargoTipo(\Sie\AppWebBundle\Entity\CargoTipo $cargoTipo = null)
    {
        $this->cargoTipo = $cargoTipo;
    
        return $this;
    }

    /**
     * Get cargoTipo
     *
     * @return \Sie\AppWebBundle\Entity\CargoTipo 
     */
    public function getCargoTipo()
    {
        return $this->cargoTipo;
    }

    /**
     * Set entidadfinancieraTipo
     *
     * @param \Sie\AppWebBundle\Entity\EntidadfinancieraTipo $entidadfinancieraTipo
     * @return MaestroCuentabancaria
     */
    public function setEntidadfinancieraTipo(\Sie\AppWebBundle\Entity\EntidadfinancieraTipo $entidadfinancieraTipo = null)
    {
        $this->entidadfinancieraTipo = $entidadfinancieraTipo;
    
        return $this;
    }

    /**
     * Get entidadfinancieraTipo
     *
     * @return \Sie\AppWebBundle\Entity\EntidadfinancieraTipo 
     */
    public function getEntidadfinancieraTipo()
    {
        return $this->entidadfinancieraTipo;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\MaestroInscripcion
     */
    private $maestroInscripcion;


    /**
     * Set maestroInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\MaestroInscripcion $maestroInscripcion
     * @return MaestroCuentabancaria
     */
    public function setMaestroInscripcion(\Sie\AppWebBundle\Entity\MaestroInscripcion $maestroInscripcion = null)
    {
        $this->maestroInscripcion = $maestroInscripcion;
    
        return $this;
    }

    /**
     * Get maestroInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\MaestroInscripcion 
     */
    public function getMaestroInscripcion()
    {
        return $this->maestroInscripcion;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;


    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return MaestroCuentabancaria
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
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;


    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return MaestroCuentabancaria
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
     * @return MaestroCuentabancaria
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
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var \DateTime
     */
    private $fechaNacimiento;

    /**
     * @var string
     */
    private $apellidoEsposo;


    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return MaestroCuentabancaria
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
     * Set fechaNacimiento
     *
     * @param \DateTime $fechaNacimiento
     * @return MaestroCuentabancaria
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
     * Set apellidoEsposo
     *
     * @param string $apellidoEsposo
     * @return MaestroCuentabancaria
     */
    public function setApellidoEsposo($apellidoEsposo)
    {
        $this->apellidoEsposo = $apellidoEsposo;
    
        return $this;
    }

    /**
     * Get apellidoEsposo
     *
     * @return string 
     */
    public function getApellidoEsposo()
    {
        return $this->apellidoEsposo;
    }
}
