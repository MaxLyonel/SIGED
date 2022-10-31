<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertificadoRueInstitucioneducativa
 *
 * @ORM\Table(name="certificado_rue_institucioneducativa", indexes={@ORM\Index(name="IDX_44A47F58C877D7CB", columns={"certificado_rue_id"}), @ORM\Index(name="IDX_44A47F583AB163FE", columns={"institucioneducativa_id"}), @ORM\Index(name="IDX_44A47F58CC1457D1", columns={"le_juridicciongeografica_id"})})
 * @ORM\Entity
 */
class CertificadoRueInstitucioneducativa
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certificado_rue_institucioneducativa_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="nro_certificado", type="integer", nullable=true)
     */
    private $nroCertificado;

    /**
     * @var string
     *
     * @ORM\Column(name="institucioneducativades", type="string", length=255, nullable=true)
     */
    private $institucioneducativades;

    /**
     * @var string
     *
     * @ORM\Column(name="departamento", type="string", length=255, nullable=true)
     */
    private $departamento;

    /**
     * @var string
     *
     * @ORM\Column(name="provincia", type="string", length=255, nullable=true)
     */
    private $provincia;

    /**
     * @var string
     *
     * @ORM\Column(name="seccion", type="string", length=255, nullable=true)
     */
    private $seccion;

    /**
     * @var string
     *
     * @ORM\Column(name="canton", type="string", length=255, nullable=true)
     */
    private $canton;

    /**
     * @var string
     *
     * @ORM\Column(name="localidad", type="string", length=255, nullable=true)
     */
    private $localidad;

    /**
     * @var string
     *
     * @ORM\Column(name="area", type="string", length=1, nullable=true)
     */
    private $area;

    /**
     * @var string
     *
     * @ORM\Column(name="dependencia", type="string", length=255, nullable=true)
     */
    private $dependencia;

    /**
     * @var string
     *
     * @ORM\Column(name="distrito", type="string", length=255, nullable=true)
     */
    private $distrito;

    /**
     * @var string
     *
     * @ORM\Column(name="institucioneducativatipo", type="string", length=255, nullable=true)
     */
    private $institucioneducativatipo;

    /**
     * @var string
     *
     * @ORM\Column(name="niveles", type="string", length=255, nullable=true)
     */
    private $niveles;

    /**
     * @var string
     *
     * @ORM\Column(name="areas", type="string", length=255, nullable=true)
     */
    private $areas;

    /**
     * @var string
     *
     * @ORM\Column(name="operacion_rue", type="string", length=255, nullable=true)
     */
    private $operacionRue;

    /**
     * @var string
     *
     * @ORM\Column(name="nro_resolucion", type="string", length=255, nullable=true)
     */
    private $nroResolucion;

    /**
     * @var \CertificadoRue
     *
     * @ORM\ManyToOne(targetEntity="CertificadoRue")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="certificado_rue_id", referencedColumnName="id")
     * })
     */
    private $certificadoRue;

    /**
     * @var \Institucioneducativa
     *
     * @ORM\ManyToOne(targetEntity="Institucioneducativa")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="institucioneducativa_id", referencedColumnName="id")
     * })
     */
    private $institucioneducativa;

    /**
     * @var \JurisdiccionGeografica
     *
     * @ORM\ManyToOne(targetEntity="JurisdiccionGeografica")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="le_juridicciongeografica_id", referencedColumnName="id")
     * })
     */
    private $leJuridicciongeografica;



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
     * Set nroCertificado
     *
     * @param integer $nroCertificado
     * @return CertificadoRueInstitucioneducativa
     */
    public function setNroCertificado($nroCertificado)
    {
        $this->nroCertificado = $nroCertificado;
    
        return $this;
    }

    /**
     * Get nroCertificado
     *
     * @return integer 
     */
    public function getNroCertificado()
    {
        return $this->nroCertificado;
    }

    /**
     * Set institucioneducativades
     *
     * @param string $institucioneducativades
     * @return CertificadoRueInstitucioneducativa
     */
    public function setInstitucioneducativades($institucioneducativades)
    {
        $this->institucioneducativades = $institucioneducativades;
    
        return $this;
    }

    /**
     * Get institucioneducativades
     *
     * @return string 
     */
    public function getInstitucioneducativades()
    {
        return $this->institucioneducativades;
    }

    /**
     * Set departamento
     *
     * @param string $departamento
     * @return CertificadoRueInstitucioneducativa
     */
    public function setDepartamento($departamento)
    {
        $this->departamento = $departamento;
    
        return $this;
    }

    /**
     * Get departamento
     *
     * @return string 
     */
    public function getDepartamento()
    {
        return $this->departamento;
    }

    /**
     * Set provincia
     *
     * @param string $provincia
     * @return CertificadoRueInstitucioneducativa
     */
    public function setProvincia($provincia)
    {
        $this->provincia = $provincia;
    
        return $this;
    }

    /**
     * Get provincia
     *
     * @return string 
     */
    public function getProvincia()
    {
        return $this->provincia;
    }

    /**
     * Set seccion
     *
     * @param string $seccion
     * @return CertificadoRueInstitucioneducativa
     */
    public function setSeccion($seccion)
    {
        $this->seccion = $seccion;
    
        return $this;
    }

    /**
     * Get seccion
     *
     * @return string 
     */
    public function getSeccion()
    {
        return $this->seccion;
    }

    /**
     * Set canton
     *
     * @param string $canton
     * @return CertificadoRueInstitucioneducativa
     */
    public function setCanton($canton)
    {
        $this->canton = $canton;
    
        return $this;
    }

    /**
     * Get canton
     *
     * @return string 
     */
    public function getCanton()
    {
        return $this->canton;
    }

    /**
     * Set localidad
     *
     * @param string $localidad
     * @return CertificadoRueInstitucioneducativa
     */
    public function setLocalidad($localidad)
    {
        $this->localidad = $localidad;
    
        return $this;
    }

    /**
     * Get localidad
     *
     * @return string 
     */
    public function getLocalidad()
    {
        return $this->localidad;
    }

    /**
     * Set area
     *
     * @param string $area
     * @return CertificadoRueInstitucioneducativa
     */
    public function setArea($area)
    {
        $this->area = $area;
    
        return $this;
    }

    /**
     * Get area
     *
     * @return string 
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set dependencia
     *
     * @param string $dependencia
     * @return CertificadoRueInstitucioneducativa
     */
    public function setDependencia($dependencia)
    {
        $this->dependencia = $dependencia;
    
        return $this;
    }

    /**
     * Get dependencia
     *
     * @return string 
     */
    public function getDependencia()
    {
        return $this->dependencia;
    }

    /**
     * Set distrito
     *
     * @param string $distrito
     * @return CertificadoRueInstitucioneducativa
     */
    public function setDistrito($distrito)
    {
        $this->distrito = $distrito;
    
        return $this;
    }

    /**
     * Get distrito
     *
     * @return string 
     */
    public function getDistrito()
    {
        return $this->distrito;
    }

    /**
     * Set institucioneducativatipo
     *
     * @param string $institucioneducativatipo
     * @return CertificadoRueInstitucioneducativa
     */
    public function setInstitucioneducativatipo($institucioneducativatipo)
    {
        $this->institucioneducativatipo = $institucioneducativatipo;
    
        return $this;
    }

    /**
     * Get institucioneducativatipo
     *
     * @return string 
     */
    public function getInstitucioneducativatipo()
    {
        return $this->institucioneducativatipo;
    }

    /**
     * Set niveles
     *
     * @param string $niveles
     * @return CertificadoRueInstitucioneducativa
     */
    public function setNiveles($niveles)
    {
        $this->niveles = $niveles;
    
        return $this;
    }

    /**
     * Get niveles
     *
     * @return string 
     */
    public function getNiveles()
    {
        return $this->niveles;
    }

    /**
     * Set areas
     *
     * @param string $areas
     * @return CertificadoRueInstitucioneducativa
     */
    public function setAreas($areas)
    {
        $this->areas = $areas;
    
        return $this;
    }

    /**
     * Get areas
     *
     * @return string 
     */
    public function getAreas()
    {
        return $this->areas;
    }

    /**
     * Set operacionRue
     *
     * @param string $operacionRue
     * @return CertificadoRueInstitucioneducativa
     */
    public function setOperacionRue($operacionRue)
    {
        $this->operacionRue = $operacionRue;
    
        return $this;
    }

    /**
     * Get operacionRue
     *
     * @return string 
     */
    public function getOperacionRue()
    {
        return $this->operacionRue;
    }

    /**
     * Set nroResolucion
     *
     * @param string $nroResolucion
     * @return CertificadoRueInstitucioneducativa
     */
    public function setNroResolucion($nroResolucion)
    {
        $this->nroResolucion = $nroResolucion;
    
        return $this;
    }

    /**
     * Get nroResolucion
     *
     * @return string 
     */
    public function getNroResolucion()
    {
        return $this->nroResolucion;
    }

    /**
     * Set certificadoRue
     *
     * @param \Sie\AppWebBundle\Entity\CertificadoRue $certificadoRue
     * @return CertificadoRueInstitucioneducativa
     */
    public function setCertificadoRue(\Sie\AppWebBundle\Entity\CertificadoRue $certificadoRue = null)
    {
        $this->certificadoRue = $certificadoRue;
    
        return $this;
    }

    /**
     * Get certificadoRue
     *
     * @return \Sie\AppWebBundle\Entity\CertificadoRue 
     */
    public function getCertificadoRue()
    {
        return $this->certificadoRue;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return CertificadoRueInstitucioneducativa
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
     * Set leJuridicciongeografica
     *
     * @param \Sie\AppWebBundle\Entity\JurisdiccionGeografica $leJuridicciongeografica
     * @return CertificadoRueInstitucioneducativa
     */
    public function setLeJuridicciongeografica(\Sie\AppWebBundle\Entity\JurisdiccionGeografica $leJuridicciongeografica = null)
    {
        $this->leJuridicciongeografica = $leJuridicciongeografica;
    
        return $this;
    }

    /**
     * Get leJuridicciongeografica
     *
     * @return \Sie\AppWebBundle\Entity\JurisdiccionGeografica 
     */
    public function getLeJuridicciongeografica()
    {
        return $this->leJuridicciongeografica;
    }
    /**
     * @var string
     */
    private $orgcurricular;


    /**
     * Set orgcurricular
     *
     * @param string $orgcurricular
     * @return CertificadoRueInstitucioneducativa
     */
    public function setOrgcurricular($orgcurricular)
    {
        $this->orgcurricular = $orgcurricular;
    
        return $this;
    }

    /**
     * Get orgcurricular
     *
     * @return string 
     */
    public function getOrgcurricular()
    {
        return $this->orgcurricular;
    }
}
