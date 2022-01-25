<?php

namespace Sie\AppWebBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * SocioeconomicoEspecial
 *
 * @ORM\Table(name="socioeconomico_especial", indexes={@ORM\Index(name="IDX_6B37765977F9D570", columns={"domicilio_departamento_id"}), @ORM\Index(name="IDX_6B377659C1D971CB", columns={"domicilio_provincia_id"}), @ORM\Index(name="IDX_6B377659E7E1C25F", columns={"etnia_tipo_id"}), @ORM\Index(name="IDX_6B377659179FF95", columns={"gestion_tipo_id"}), @ORM\Index(name="IDX_6B377659812C420E", columns={"idioma1_tipo_id"}), @ORM\Index(name="IDX_6B377659B8A17ECB", columns={"idioma2_tipo_id"}), @ORM\Index(name="IDX_6B377659AFDA6A88", columns={"idioma3_tipo_id"}), @ORM\Index(name="IDX_6B37765970591119", columns={"sangre_tipo_id"})})
 * @ORM\Entity
 */
class SocioeconomicoEspecial
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="socioeconomico_especial_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="domicilio_municipio", type="string", length=255, nullable=true)
     */
    private $domicilioMunicipio;

    /**
     * @var string
     *
     * @ORM\Column(name="domicilio_localidad", type="string", length=255, nullable=true)
     */
    private $domicilioLocalidad;

    /**
     * @var string
     *
     * @ORM\Column(name="direccion_zona", type="string", length=45, nullable=true)
     */
    private $direccionZona;

    /**
     * @var string
     *
     * @ORM\Column(name="direccion_calle", type="string", length=45, nullable=true)
     */
    private $direccionCalle;

    /**
     * @var string
     *
     * @ORM\Column(name="direccion_nro", type="string", length=45, nullable=true)
     */
    private $direccionNro;

    /**
     * @var string
     *
     * @ORM\Column(name="direccion_telefono", type="string", length=45, nullable=true)
     */
    private $direccionTelefono;

    /**
     * @var string
     *
     * @ORM\Column(name="direccion_celular", type="string", length=45, nullable=true)
     */
    private $direccionCelular;

    /**
     * @var string
     *
     * @ORM\Column(name="vive_con", type="string", nullable=true)
     */
    private $viveCon;

    /**
     * @var string
     *
     * @ORM\Column(name="pariente_discapacidad", type="string", length=255, nullable=true)
     */
    private $parienteDiscapacidad;

    /**
     * @var string
     *
     * @ORM\Column(name="grado_parentesco", type="string", nullable=true)
     */
    private $gradoParentesco;

    /**
     * @var string
     *
     * @ORM\Column(name="seguro", type="string", length=255, nullable=true)
     */
    private $seguro;

    /**
     * @var string
     *
     * @ORM\Column(name="medicacion", type="string", length=255, nullable=true)
     */
    private $medicacion;

    /**
     * @var integer
     *
     * @ORM\Column(name="usuario_id", type="integer", nullable=true)
     */
    private $usuarioId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_last_update", type="date", nullable=true)
     */
    private $fechaLastUpdate;

    /**
     * @var \LugarTipo
     *
     * @ORM\ManyToOne(targetEntity="LugarTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="domicilio_departamento_id", referencedColumnName="id")
     * })
     */
    private $domicilioDepartamento;

    /**
     * @var \LugarTipo
     *
     * @ORM\ManyToOne(targetEntity="LugarTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="domicilio_provincia_id", referencedColumnName="id")
     * })
     */
    private $domicilioProvincia;

    /**
     * @var \EtniaTipo
     *
     * @ORM\ManyToOne(targetEntity="EtniaTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="etnia_tipo_id", referencedColumnName="id")
     * })
     */
    private $etniaTipo;

    /**
     * @var \GestionTipo
     *
     * @ORM\ManyToOne(targetEntity="GestionTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="gestion_tipo_id", referencedColumnName="id")
     * })
     */
    private $gestionTipo;

    /**
     * @var \IdiomaTipo
     *
     * @ORM\ManyToOne(targetEntity="IdiomaTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idioma1_tipo_id", referencedColumnName="id")
     * })
     */
    private $idioma1Tipo;

    /**
     * @var \IdiomaTipo
     *
     * @ORM\ManyToOne(targetEntity="IdiomaTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idioma2_tipo_id", referencedColumnName="id")
     * })
     */
    private $idioma2Tipo;

    /**
     * @var \IdiomaTipo
     *
     * @ORM\ManyToOne(targetEntity="IdiomaTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idioma3_tipo_id", referencedColumnName="id")
     * })
     */
    private $idioma3Tipo;

    /**
     * @var \SangreTipo
     *
     * @ORM\ManyToOne(targetEntity="SangreTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sangre_tipo_id", referencedColumnName="id")
     * })
     */
    private $sangreTipo;



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
     * Set domicilioMunicipio
     *
     * @param string $domicilioMunicipio
     * @return SocioeconomicoEspecial
     */
    public function setDomicilioMunicipio($domicilioMunicipio)
    {
        $this->domicilioMunicipio = $domicilioMunicipio;
    
        return $this;
    }

    /**
     * Get domicilioMunicipio
     *
     * @return string 
     */
    public function getDomicilioMunicipio()
    {
        return $this->domicilioMunicipio;
    }

    /**
     * Set domicilioLocalidad
     *
     * @param string $domicilioLocalidad
     * @return SocioeconomicoEspecial
     */
    public function setDomicilioLocalidad($domicilioLocalidad)
    {
        $this->domicilioLocalidad = $domicilioLocalidad;
    
        return $this;
    }

    /**
     * Get domicilioLocalidad
     *
     * @return string 
     */
    public function getDomicilioLocalidad()
    {
        return $this->domicilioLocalidad;
    }

    /**
     * Set direccionZona
     *
     * @param string $direccionZona
     * @return SocioeconomicoEspecial
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
     * @return SocioeconomicoEspecial
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
     * @return SocioeconomicoEspecial
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
     * @return SocioeconomicoEspecial
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
     * @return SocioeconomicoEspecial
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
     * Set viveCon
     *
     * @param string $viveCon
     * @return SocioeconomicoEspecial
     */
    public function setViveCon($viveCon)
    {
        $this->viveCon = $viveCon;
    
        return $this;
    }

    /**
     * Get viveCon
     *
     * @return string 
     */
    public function getViveCon()
    {
        return $this->viveCon;
    }

    /**
     * Set parienteDiscapacidad
     *
     * @param string $parienteDiscapacidad
     * @return SocioeconomicoEspecial
     */
    public function setParienteDiscapacidad($parienteDiscapacidad)
    {
        $this->parienteDiscapacidad = $parienteDiscapacidad;
    
        return $this;
    }

    /**
     * Get parienteDiscapacidad
     *
     * @return string 
     */
    public function getParienteDiscapacidad()
    {
        return $this->parienteDiscapacidad;
    }

    /**
     * Set gradoParentesco
     *
     * @param string $gradoParentesco
     * @return SocioeconomicoEspecial
     */
    public function setGradoParentesco($gradoParentesco)
    {
        $this->gradoParentesco = $gradoParentesco;
    
        return $this;
    }

    /**
     * Get gradoParentesco
     *
     * @return string 
     */
    public function getGradoParentesco()
    {
        return $this->gradoParentesco;
    }

    /**
     * Set seguro
     *
     * @param string $seguro
     * @return SocioeconomicoEspecial
     */
    public function setSeguro($seguro)
    {
        $this->seguro = $seguro;
    
        return $this;
    }

    /**
     * Get seguro
     *
     * @return string 
     */
    public function getSeguro()
    {
        return $this->seguro;
    }

    /**
     * Set medicacion
     *
     * @param string $medicacion
     * @return SocioeconomicoEspecial
     */
    public function setMedicacion($medicacion)
    {
        $this->medicacion = $medicacion;
    
        return $this;
    }

    /**
     * Get medicacion
     *
     * @return string 
     */
    public function getMedicacion()
    {
        return $this->medicacion;
    }

    /**
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return SocioeconomicoEspecial
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
     * @return SocioeconomicoEspecial
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
     * Set domicilioDepartamento
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $domicilioDepartamento
     * @return SocioeconomicoEspecial
     */
    public function setDomicilioDepartamento(\Sie\AppWebBundle\Entity\LugarTipo $domicilioDepartamento = null)
    {
        $this->domicilioDepartamento = $domicilioDepartamento;
    
        return $this;
    }

    /**
     * Get domicilioDepartamento
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getDomicilioDepartamento()
    {
        return $this->domicilioDepartamento;
    }

    /**
     * Set domicilioProvincia
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $domicilioProvincia
     * @return SocioeconomicoEspecial
     */
    public function setDomicilioProvincia(\Sie\AppWebBundle\Entity\LugarTipo $domicilioProvincia = null)
    {
        $this->domicilioProvincia = $domicilioProvincia;
    
        return $this;
    }

    /**
     * Get domicilioProvincia
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getDomicilioProvincia()
    {
        return $this->domicilioProvincia;
    }

    /**
     * Set etniaTipo
     *
     * @param \Sie\AppWebBundle\Entity\EtniaTipo $etniaTipo
     * @return SocioeconomicoEspecial
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
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return SocioeconomicoEspecial
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
     * Set idioma1Tipo
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idioma1Tipo
     * @return SocioeconomicoEspecial
     */
    public function setIdioma1Tipo(\Sie\AppWebBundle\Entity\IdiomaTipo $idioma1Tipo = null)
    {
        $this->idioma1Tipo = $idioma1Tipo;
    
        return $this;
    }

    /**
     * Get idioma1Tipo
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdioma1Tipo()
    {
        return $this->idioma1Tipo;
    }

    /**
     * Set idioma2Tipo
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idioma2Tipo
     * @return SocioeconomicoEspecial
     */
    public function setIdioma2Tipo(\Sie\AppWebBundle\Entity\IdiomaTipo $idioma2Tipo = null)
    {
        $this->idioma2Tipo = $idioma2Tipo;
    
        return $this;
    }

    /**
     * Get idioma2Tipo
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdioma2Tipo()
    {
        return $this->idioma2Tipo;
    }

    /**
     * Set idioma3Tipo
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idioma3Tipo
     * @return SocioeconomicoEspecial
     */
    public function setIdioma3Tipo(\Sie\AppWebBundle\Entity\IdiomaTipo $idioma3Tipo = null)
    {
        $this->idioma3Tipo = $idioma3Tipo;
    
        return $this;
    }

    /**
     * Get idioma3Tipo
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdioma3Tipo()
    {
        return $this->idioma3Tipo;
    }

    /**
     * Set sangreTipo
     *
     * @param \Sie\AppWebBundle\Entity\SangreTipo $sangreTipo
     * @return SocioeconomicoEspecial
     */
    public function setSangreTipo(\Sie\AppWebBundle\Entity\SangreTipo $sangreTipo = null)
    {
        $this->sangreTipo = $sangreTipo;
    
        return $this;
    }

    /**
     * Get sangreTipo
     *
     * @return \Sie\AppWebBundle\Entity\SangreTipo 
     */
    public function getSangreTipo()
    {
        return $this->sangreTipo;
    }
}
