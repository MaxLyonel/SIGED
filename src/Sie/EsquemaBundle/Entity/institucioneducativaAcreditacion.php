<?php

namespace Sie\EsquemaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * institucioneducativaAcreditacion
 *
 * @ORM\Table(name="superior.institucioneducativa_acreditacion", indexes={@ORM\Index(name="IDX_7A4386274246CCFB", columns={"turno_superior_tipo_id"}), @ORM\Index(name="IDX_7A4386277A322B24", columns={"institucioneducativa_sucursal_id"}), @ORM\Index(name="IDX_7A438627179FF95", columns={"gestion_tipo_id"}), @ORM\Index(name="IDX_7A43862721B796C6", columns={"acreditacion_especialidad_id"}), @ORM\Index(name="IDX_7A4386273AB163FE", columns={"institucioneducativa_id"})})
 * @ORM\Entity
 */
class institucioneducativaAcreditacion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="superior.institucioneducativa_acreditacion_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="obs", type="string", length=255, nullable=true)
     */
    private $obs;

    /**
     * @var integer
     *
     * @ORM\Column(name="pensum_numero", type="integer", nullable=true)
     */
    private $pensumNumero;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_inicio", type="date", nullable=true)
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_fin", type="date", nullable=true)
     */
    private $fechaFin;

    /**
     * @var boolean
     *
     * @ORM\Column(name="esactivo", type="boolean", nullable=true)
     */
    private $esactivo;

    /**
     * @var integer
     *
     * @ORM\Column(name="horas_especialidad", type="integer", nullable=true)
     */
    private $horasEspecialidad;

    /**
     * @var \turnoSuperiorTipo
     *
     * @ORM\ManyToOne(targetEntity="turnoSuperiorTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="turno_superior_tipo_id", referencedColumnName="id")
     * })
     */
    private $turnoSuperiorTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal
     *
     * @ORM\ManyToOne(targetEntity="\Sie\AppWebBundle\Entity\InstitucioneducativaSucursal")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="institucioneducativa_sucursal_id", referencedColumnName="id")
     * })
     */
    private $institucioneducativaSucursal;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     *
     * @ORM\ManyToOne(targetEntity="\Sie\AppWebBundle\Entity\GestionTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="gestion_tipo_id", referencedColumnName="id")
     * })
     */
    private $gestionTipo;

    /**
     * @var \acreditacionEspecialidad
     *
     * @ORM\ManyToOne(targetEntity="acreditacionEspecialidad")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="acreditacion_especialidad_id", referencedColumnName="id")
     * })
     */
    private $acreditacionEspecialidad;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     *
     * @ORM\ManyToOne(targetEntity="\Sie\AppWebBundle\Entity\Institucioneducativa")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="institucioneducativa_id", referencedColumnName="id")
     * })
     */
    private $institucioneducativa;



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
     * Set obs
     *
     * @param string $obs
     * @return institucioneducativaAcreditacion
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
     * Set pensumNumero
     *
     * @param integer $pensumNumero
     * @return institucioneducativaAcreditacion
     */
    public function setPensumNumero($pensumNumero)
    {
        $this->pensumNumero = $pensumNumero;
    
        return $this;
    }

    /**
     * Get pensumNumero
     *
     * @return integer 
     */
    public function getPensumNumero()
    {
        return $this->pensumNumero;
    }

    /**
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return institucioneducativaAcreditacion
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
    
        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime 
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaFin
     *
     * @param \DateTime $fechaFin
     * @return institucioneducativaAcreditacion
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;
    
        return $this;
    }

    /**
     * Get fechaFin
     *
     * @return \DateTime 
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return institucioneducativaAcreditacion
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
     * Set horasEspecialidad
     *
     * @param integer $horasEspecialidad
     * @return institucioneducativaAcreditacion
     */
    public function setHorasEspecialidad($horasEspecialidad)
    {
        $this->horasEspecialidad = $horasEspecialidad;
    
        return $this;
    }

    /**
     * Get horasEspecialidad
     *
     * @return integer 
     */
    public function getHorasEspecialidad()
    {
        return $this->horasEspecialidad;
    }

    /**
     * Set turnoSuperiorTipo
     *
     * @param \Sie\EsquemaBundle\Entity\turnoSuperiorTipo $turnoSuperiorTipo
     * @return institucioneducativaAcreditacion
     */
    public function setTurnoSuperiorTipo(\Sie\EsquemaBundle\Entity\turnoSuperiorTipo $turnoSuperiorTipo = null)
    {
        $this->turnoSuperiorTipo = $turnoSuperiorTipo;
    
        return $this;
    }

    /**
     * Get turnoSuperiorTipo
     *
     * @return \Sie\EsquemaBundle\Entity\turnoSuperiorTipo 
     */
    public function getTurnoSuperiorTipo()
    {
        return $this->turnoSuperiorTipo;
    }

    /**
     * Set institucioneducativaSucursal
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal $institucioneducativaSucursal
     * @return institucioneducativaAcreditacion
     */
    public function setInstitucioneducativaSucursal(\Sie\AppWebBundle\Entity\InstitucioneducativaSucursal $institucioneducativaSucursal = null)
    {
        $this->institucioneducativaSucursal = $institucioneducativaSucursal;
    
        return $this;
    }

    /**
     * Get institucioneducativaSucursal
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal 
     */
    public function getInstitucioneducativaSucursal()
    {
        return $this->institucioneducativaSucursal;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return institucioneducativaAcreditacion
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
     * Set acreditacionEspecialidad
     *
     * @param \Sie\EsquemaBundle\Entity\acreditacionEspecialidad $acreditacionEspecialidad
     * @return institucioneducativaAcreditacion
     */
    public function setAcreditacionEspecialidad(\Sie\EsquemaBundle\Entity\acreditacionEspecialidad $acreditacionEspecialidad = null)
    {
        $this->acreditacionEspecialidad = $acreditacionEspecialidad;
    
        return $this;
    }

    /**
     * Get acreditacionEspecialidad
     *
     * @return \Sie\EsquemaBundle\Entity\acreditacionEspecialidad 
     */
    public function getAcreditacionEspecialidad()
    {
        return $this->acreditacionEspecialidad;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return institucioneducativaAcreditacion
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
}
