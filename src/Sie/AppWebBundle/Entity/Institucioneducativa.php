<?php

namespace Sie\AppWebBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Institucioneducativa
 */
class Institucioneducativa {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $institucioneducativa;

    /**
     * @var boolean
     */
    private $rueUe;

    /**
     * @var \DateTime
     */
    private $fechaResolucion;

    /**
     * @var string
     */
    private $nroResolucion;

    /**
     * @var string
     */
    private $obsRue;

    /**
     * @var string
     */
    private $desUeAntes;

    /**
     * @var \DateTime
     */
    private $fechaCreacion;

    /**
     * @var string
     */
    private $fechaCierre;

    /**
     * @var \Sie\AppWebBundle\Entity\ConvenioTipo
     */
    private $convenioTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\DependenciaTipo
     */
    private $dependenciaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstadoinstitucionTipo
     */
    private $estadoinstitucionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OrgcurricularTipo
     */
    private $orgcurricularTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaTipo
     */
    private $institucioneducativaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\JurisdiccionGeografica
     */
    private $leJuridicciongeografica;

    /**
     * @ORM\OneToMany(targetEntity="FormularioRue", mappedBy="institucioneducativa", cascade={"remove"})
     */
    protected $formularios;
    
    /**
     * @ORM\OneToMany(targetEntity="CertificadoRueInstitucioneducativa", mappedBy="institucioneducativa", cascade={"remove"})
     */
    protected $certificados;
    
    public function __construct()
    {
    	$this->formularios = new ArrayCollection();
    }
    
    public function __toString() {
        return $this->institucioneducativa;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }
    public function setId($id)
    {
    	$this->id = $id;
    }
    
    /**
     * Set institucioneducativa
     *
     * @param string $institucioneducativa
     * @return Institucioneducativa
     */
    public function setInstitucioneducativa($institucioneducativa) {
        $this->institucioneducativa = $institucioneducativa;

        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return string 
     */
    public function getInstitucioneducativa() {
        return $this->institucioneducativa;
    }

    /**
     * Set rueUe
     *
     * @param boolean $rueUe
     * @return Institucioneducativa
     */
    public function setRueUe($rueUe) {
        $this->rueUe = $rueUe;

        return $this;
    }

    /**
     * Get rueUe
     *
     * @return boolean 
     */
    public function getRueUe() {
        return $this->rueUe;
    }

    /**
     * Set fechaResolucion
     *
     * @param \DateTime $fechaResolucion
     * @return Institucioneducativa
     */
    public function setFechaResolucion($fechaResolucion) {
        $this->fechaResolucion = $fechaResolucion;

        return $this;
    }

    /**
     * Get fechaResolucion
     *
     * @return \DateTime 
     */
    public function getFechaResolucion() {
        return $this->fechaResolucion;
    }

    /**
     * Set nroResolucion
     *
     * @param string $nroResolucion
     * @return Institucioneducativa
     */
    public function setNroResolucion($nroResolucion) {
        $this->nroResolucion = $nroResolucion;

        return $this;
    }

    /**
     * Get nroResolucion
     *
     * @return string 
     */
    public function getNroResolucion() {
        return $this->nroResolucion;
    }

    /**
     * Set obsRue
     *
     * @param string $obsRue
     * @return Institucioneducativa
     */
    public function setObsRue($obsRue) {
        $this->obsRue = $obsRue;

        return $this;
    }

    /**
     * Get obsRue
     *
     * @return string 
     */
    public function getObsRue() {
        return $this->obsRue;
    }

    /**
     * Set desUeAntes
     *
     * @param string $desUeAntes
     * @return Institucioneducativa
     */
    public function setDesUeAntes($desUeAntes) {
        $this->desUeAntes = $desUeAntes;

        return $this;
    }

    /**
     * Get desUeAntes
     *
     * @return string 
     */
    public function getDesUeAntes() {
        return $this->desUeAntes;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     * @return Institucioneducativa
     */
    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime 
     */
    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    /**
     * Set fechaCierre
     *
     * @param string $fechaCierre
     * @return Institucioneducativa
     */
    public function setFechaCierre($fechaCierre) {
        $this->fechaCierre = $fechaCierre;

        return $this;
    }

    /**
     * Get fechaCierre
     *
     * @return string 
     */
    public function getFechaCierre() {
        return $this->fechaCierre;
    }

    /**
     * Set convenioTipo
     *
     * @param \Sie\AppWebBundle\Entity\ConvenioTipo $convenioTipo
     * @return Institucioneducativa
     */
    public function setConvenioTipo(\Sie\AppWebBundle\Entity\ConvenioTipo $convenioTipo = null) {
        $this->convenioTipo = $convenioTipo;

        return $this;
    }

    /**
     * Get convenioTipo
     *
     * @return \Sie\AppWebBundle\Entity\ConvenioTipo 
     */
    public function getConvenioTipo() {
        return $this->convenioTipo;
    }

    /**
     * Set dependenciaTipo
     *
     * @param \Sie\AppWebBundle\Entity\DependenciaTipo $dependenciaTipo
     * @return Institucioneducativa
     */
    public function setDependenciaTipo(\Sie\AppWebBundle\Entity\DependenciaTipo $dependenciaTipo = null) {
        $this->dependenciaTipo = $dependenciaTipo;

        return $this;
    }

    /**
     * Get dependenciaTipo
     *
     * @return \Sie\AppWebBundle\Entity\DependenciaTipo 
     */
    public function getDependenciaTipo() {
        return $this->dependenciaTipo;
    }

    /**
     * Set estadoinstitucionTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstadoinstitucionTipo $estadoinstitucionTipo
     * @return Institucioneducativa
     */
    public function setEstadoinstitucionTipo(\Sie\AppWebBundle\Entity\EstadoinstitucionTipo $estadoinstitucionTipo = null) {
        $this->estadoinstitucionTipo = $estadoinstitucionTipo;

        return $this;
    }

    /**
     * Get estadoinstitucionTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstadoinstitucionTipo 
     */
    public function getEstadoinstitucionTipo() {
        return $this->estadoinstitucionTipo;
    }

    /**
     * Set orgcurricularTipo
     *
     * @param \Sie\AppWebBundle\Entity\OrgcurricularTipo $orgcurricularTipo
     * @return Institucioneducativa
     */
    public function setOrgcurricularTipo(\Sie\AppWebBundle\Entity\OrgcurricularTipo $orgcurricularTipo = null) {
        $this->orgcurricularTipo = $orgcurricularTipo;

        return $this;
    }

    /**
     * Get orgcurricularTipo
     *
     * @return \Sie\AppWebBundle\Entity\OrgcurricularTipo 
     */
    public function getOrgcurricularTipo() {
        return $this->orgcurricularTipo;
    }

    /**
     * Set institucioneducativaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo
     * @return Institucioneducativa
     */
    public function setInstitucioneducativaTipo(\Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo = null) {
        $this->institucioneducativaTipo = $institucioneducativaTipo;

        return $this;
    }

    /**
     * Get institucioneducativaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaTipo 
     */
    public function getInstitucioneducativaTipo() {
        return $this->institucioneducativaTipo;
    }

    /**
     * Set leJuridicciongeografica
     *
     * @param \Sie\AppWebBundle\Entity\JurisdiccionGeografica $leJuridicciongeografica
     * @return Institucioneducativa
     */
    public function setLeJuridicciongeografica(\Sie\AppWebBundle\Entity\JurisdiccionGeografica $leJuridicciongeografica = null) {
        $this->leJuridicciongeografica = $leJuridicciongeografica;

        return $this;
    }

    /**
     * Get leJuridicciongeografica
     *
     * @return \Sie\AppWebBundle\Entity\JurisdiccionGeografica 
     */
    public function getLeJuridicciongeografica() {
        return $this->leJuridicciongeografica;
    }
	public function getFormularios() {
		return $this->formularios;
	}
	public function setFormularios($formularios) {
		$this->formularios = $formularios;
		return $this;
	}
	public function getCertificados() {
		return $this->certificados;
	}
	public function setCertificados($certificados) {
		$this->certificados = $certificados;
		return $this;
	}
	
	

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaAcreditacionTipo
     */
    private $institucioneducativaAcreditacionTipo;


    /**
     * Set institucioneducativaAcreditacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaAcreditacionTipo $institucioneducativaAcreditacionTipo
     * @return Institucioneducativa
     */
    public function setInstitucioneducativaAcreditacionTipo(\Sie\AppWebBundle\Entity\InstitucioneducativaAcreditacionTipo $institucioneducativaAcreditacionTipo = null)
    {
        $this->institucioneducativaAcreditacionTipo = $institucioneducativaAcreditacionTipo;
    
        return $this;
    }

    /**
     * Get institucioneducativaAcreditacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaAcreditacionTipo 
     */
    public function getInstitucioneducativaAcreditacionTipo()
    {
        return $this->institucioneducativaAcreditacionTipo;
    }
}
