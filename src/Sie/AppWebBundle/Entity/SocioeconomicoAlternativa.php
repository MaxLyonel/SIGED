<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SocioeconomicoAlternativa
 */
class SocioeconomicoAlternativa
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $atenmedicaTipoId;

    /**
     * @var integer
     */
    private $sangreTipoId;

    /**
     * @var integer
     */
    private $gestionId;

    /**
     * @var integer
     */
    private $dicapacidadId;

    /**
     * @var string
     */
    private $direccionZona;

    /**
     * @var string
     */
    private $direccionCalle;

    /**
     * @var string
     */
    private $direccionNro;

    /**
     * @var string
     */
    private $direccionTelefono;

    /**
     * @var string
     */
    private $direccionCelular;

    /**
     * @var integer
     */
    private $nroHijos;

    /**
     * @var integer
     */
    private $seguro;

    /**
     * @var string
     */
    private $empleo;

    /**
     * @var string
     */
    private $motivoInterrupcion;

    /**
     * @var integer
     */
    private $aniosInterrupcion;

    /**
     * @var integer
     */
    private $ultimoCurso;

    /**
     * @var integer
     */
    private $estadoCivilId;

    /**
     * @var integer
     */
    private $modalidadId;

    /**
     * @var integer
     */
    private $usuarioId;

    /**
     * @var \DateTime
     */
    private $fechaLastUpdate;

    /**
     * @var \Sie\AppWebBundle\Entity\EtniaTipo
     */
    private $etniaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $idiomaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $idiomaTipo2;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $idiomaTipo3;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $idiomaTipo4;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $idiomaTipo5;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $idiomaTipo6;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

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
     * Set atenmedicaTipoId
     *
     * @param integer $atenmedicaTipoId
     * @return SocioeconomicoAlternativa
     */
    public function setAtenmedicaTipoId($atenmedicaTipoId)
    {
        $this->atenmedicaTipoId = $atenmedicaTipoId;

        return $this;
    }

    /**
     * Get atenmedicaTipoId
     *
     * @return integer 
     */
    public function getAtenmedicaTipoId()
    {
        return $this->atenmedicaTipoId;
    }

    /**
     * Set sangreTipoId
     *
     * @param integer $sangreTipoId
     * @return SocioeconomicoAlternativa
     */
    public function setSangreTipoId($sangreTipoId)
    {
        $this->sangreTipoId = $sangreTipoId;

        return $this;
    }

    /**
     * Get sangreTipoId
     *
     * @return integer 
     */
    public function getSangreTipoId()
    {
        return $this->sangreTipoId;
    }

    /**
     * Set gestionId
     *
     * @param integer $gestionId
     * @return SocioeconomicoAlternativa
     */
    public function setGestionId($gestionId)
    {
        $this->gestionId = $gestionId;

        return $this;
    }

    /**
     * Get gestionId
     *
     * @return integer 
     */
    public function getGestionId()
    {
        return $this->gestionId;
    }

    /**
     * Set dicapacidadId
     *
     * @param integer $dicapacidadId
     * @return SocioeconomicoAlternativa
     */
    public function setDicapacidadId($dicapacidadId)
    {
        $this->dicapacidadId = $dicapacidadId;

        return $this;
    }

    /**
     * Get dicapacidadId
     *
     * @return integer 
     */
    public function getDicapacidadId()
    {
        return $this->dicapacidadId;
    }

    /**
     * Set direccionZona
     *
     * @param string $direccionZona
     * @return SocioeconomicoAlternativa
     */
    public function setDireccionZona($direccionZona)
    {
        $this->direccionZona = $direccionZona;

        return $this;
    }

    /**
     * Get direccionZona
     *
     * @return string 
     */
    public function getDireccionZona()
    {
        return $this->direccionZona;
    }

    /**
     * Set direccionCalle
     *
     * @param string $direccionCalle
     * @return SocioeconomicoAlternativa
     */
    public function setDireccionCalle($direccionCalle)
    {
        $this->direccionCalle = $direccionCalle;

        return $this;
    }

    /**
     * Get direccionCalle
     *
     * @return string 
     */
    public function getDireccionCalle()
    {
        return $this->direccionCalle;
    }

    /**
     * Set direccionNro
     *
     * @param string $direccionNro
     * @return SocioeconomicoAlternativa
     */
    public function setDireccionNro($direccionNro)
    {
        $this->direccionNro = $direccionNro;

        return $this;
    }

    /**
     * Get direccionNro
     *
     * @return string 
     */
    public function getDireccionNro()
    {
        return $this->direccionNro;
    }

    /**
     * Set direccionTelefono
     *
     * @param string $direccionTelefono
     * @return SocioeconomicoAlternativa
     */
    public function setDireccionTelefono($direccionTelefono)
    {
        $this->direccionTelefono = $direccionTelefono;

        return $this;
    }

    /**
     * Get direccionTelefono
     *
     * @return string 
     */
    public function getDireccionTelefono()
    {
        return $this->direccionTelefono;
    }

    /**
     * Set direccionCelular
     *
     * @param string $direccionCelular
     * @return SocioeconomicoAlternativa
     */
    public function setDireccionCelular($direccionCelular)
    {
        $this->direccionCelular = $direccionCelular;

        return $this;
    }

    /**
     * Get direccionCelular
     *
     * @return string 
     */
    public function getDireccionCelular()
    {
        return $this->direccionCelular;
    }

    /**
     * Set nroHijos
     *
     * @param integer $nroHijos
     * @return SocioeconomicoAlternativa
     */
    public function setNroHijos($nroHijos)
    {
        $this->nroHijos = $nroHijos;

        return $this;
    }

    /**
     * Get nroHijos
     *
     * @return integer 
     */
    public function getNroHijos()
    {
        return $this->nroHijos;
    }

    /**
     * Set seguro
     *
     * @param integer $seguro
     * @return SocioeconomicoAlternativa
     */
    public function setSeguro($seguro)
    {
        $this->seguro = $seguro;

        return $this;
    }

    /**
     * Get seguro
     *
     * @return integer 
     */
    public function getSeguro()
    {
        return $this->seguro;
    }

    /**
     * Set empleo
     *
     * @param string $empleo
     * @return SocioeconomicoAlternativa
     */
    public function setEmpleo($empleo)
    {
        $this->empleo = $empleo;

        return $this;
    }

    /**
     * Get empleo
     *
     * @return string 
     */
    public function getEmpleo()
    {
        return $this->empleo;
    }

    /**
     * Set motivoInterrupcion
     *
     * @param string $motivoInterrupcion
     * @return SocioeconomicoAlternativa
     */
    public function setMotivoInterrupcion($motivoInterrupcion)
    {
        $this->motivoInterrupcion = $motivoInterrupcion;

        return $this;
    }

    /**
     * Get motivoInterrupcion
     *
     * @return string 
     */
    public function getMotivoInterrupcion()
    {
        return $this->motivoInterrupcion;
    }

    /**
     * Set aniosInterrupcion
     *
     * @param integer $aniosInterrupcion
     * @return SocioeconomicoAlternativa
     */
    public function setAniosInterrupcion($aniosInterrupcion)
    {
        $this->aniosInterrupcion = $aniosInterrupcion;

        return $this;
    }

    /**
     * Get aniosInterrupcion
     *
     * @return integer 
     */
    public function getAniosInterrupcion()
    {
        return $this->aniosInterrupcion;
    }

    /**
     * Set ultimoCurso
     *
     * @param integer $ultimoCurso
     * @return SocioeconomicoAlternativa
     */
    public function setUltimoCurso($ultimoCurso)
    {
        $this->ultimoCurso = $ultimoCurso;

        return $this;
    }

    /**
     * Get ultimoCurso
     *
     * @return integer 
     */
    public function getUltimoCurso()
    {
        return $this->ultimoCurso;
    }

    /**
     * Set estadoCivilId
     *
     * @param integer $estadoCivilId
     * @return SocioeconomicoAlternativa
     */
    public function setEstadoCivilId($estadoCivilId)
    {
        $this->estadoCivilId = $estadoCivilId;

        return $this;
    }

    /**
     * Get estadoCivilId
     *
     * @return integer 
     */
    public function getEstadoCivilId()
    {
        return $this->estadoCivilId;
    }

    /**
     * Set modalidadId
     *
     * @param integer $modalidadId
     * @return SocioeconomicoAlternativa
     */
    public function setModalidadId($modalidadId)
    {
        $this->modalidadId = $modalidadId;

        return $this;
    }

    /**
     * Get modalidadId
     *
     * @return integer 
     */
    public function getModalidadId()
    {
        return $this->modalidadId;
    }

    /**
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return SocioeconomicoAlternativa
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
     * Set fechaLastUpdate
     *
     * @param \DateTime $fechaLastUpdate
     * @return SocioeconomicoAlternativa
     */
    public function setFechaLastUpdate($fechaLastUpdate)
    {
        $this->fechaLastUpdate = $fechaLastUpdate;

        return $this;
    }

    /**
     * Get fechaLastUpdate
     *
     * @return \DateTime 
     */
    public function getFechaLastUpdate()
    {
        return $this->fechaLastUpdate;
    }

    /**
     * Set etniaTipo
     *
     * @param \Sie\AppWebBundle\Entity\EtniaTipo $etniaTipo
     * @return SocioeconomicoAlternativa
     */
    public function setEtniaTipo(\Sie\AppWebBundle\Entity\EtniaTipo $etniaTipo = null)
    {
        $this->etniaTipo = $etniaTipo;

        return $this;
    }

    /**
     * Get etniaTipo
     *
     * @return \Sie\AppWebBundle\Entity\EtniaTipo 
     */
    public function getEtniaTipo()
    {
        return $this->etniaTipo;
    }

    /**
     * Set idiomaTipo
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idiomaTipo
     * @return SocioeconomicoAlternativa
     */
    public function setIdiomaTipo(\Sie\AppWebBundle\Entity\IdiomaTipo $idiomaTipo = null)
    {
        $this->idiomaTipo = $idiomaTipo;

        return $this;
    }

    /**
     * Get idiomaTipo
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdiomaTipo()
    {
        return $this->idiomaTipo;
    }

    /**
     * Set idiomaTipo2
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idiomaTipo2
     * @return SocioeconomicoAlternativa
     */
    public function setIdiomaTipo2(\Sie\AppWebBundle\Entity\IdiomaTipo $idiomaTipo2 = null)
    {
        $this->idiomaTipo2 = $idiomaTipo2;

        return $this;
    }

    /**
     * Get idiomaTipo2
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdiomaTipo2()
    {
        return $this->idiomaTipo2;
    }

    /**
     * Set idiomaTipo3
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idiomaTipo3
     * @return SocioeconomicoAlternativa
     */
    public function setIdiomaTipo3(\Sie\AppWebBundle\Entity\IdiomaTipo $idiomaTipo3 = null)
    {
        $this->idiomaTipo3 = $idiomaTipo3;

        return $this;
    }

    /**
     * Get idiomaTipo3
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdiomaTipo3()
    {
        return $this->idiomaTipo3;
    }

    /**
     * Set idiomaTipo4
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idiomaTipo4
     * @return SocioeconomicoAlternativa
     */
    public function setIdiomaTipo4(\Sie\AppWebBundle\Entity\IdiomaTipo $idiomaTipo4 = null)
    {
        $this->idiomaTipo4 = $idiomaTipo4;

        return $this;
    }

    /**
     * Get idiomaTipo4
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdiomaTipo4()
    {
        return $this->idiomaTipo4;
    }

    /**
     * Set idiomaTipo5
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idiomaTipo5
     * @return SocioeconomicoAlternativa
     */
    public function setIdiomaTipo5(\Sie\AppWebBundle\Entity\IdiomaTipo $idiomaTipo5 = null)
    {
        $this->idiomaTipo5 = $idiomaTipo5;

        return $this;
    }

    /**
     * Get idiomaTipo5
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdiomaTipo5()
    {
        return $this->idiomaTipo5;
    }

    /**
     * Set idiomaTipo6
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idiomaTipo6
     * @return SocioeconomicoAlternativa
     */
    public function setIdiomaTipo6(\Sie\AppWebBundle\Entity\IdiomaTipo $idiomaTipo6 = null)
    {
        $this->idiomaTipo6 = $idiomaTipo6;

        return $this;
    }

    /**
     * Get idiomaTipo6
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdiomaTipo6()
    {
        return $this->idiomaTipo6;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return SocioeconomicoAlternativa
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
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return SocioeconomicoAlternativa
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
