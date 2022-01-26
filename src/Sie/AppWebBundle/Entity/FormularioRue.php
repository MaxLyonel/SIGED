<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FormularioRue
 *
 * @ORM\Table(name="formulario_rue", indexes={@ORM\Index(name="IDX_8D852E8C3AB163FE", columns={"institucioneducativa_id"})})
 * @ORM\Entity
 */
class FormularioRue
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="formulario_rue_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="operacion_rue", type="string", nullable=true)
     */
    private $operacionRue;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_registro", type="date", nullable=true)
     */
    private $fechaRegistro = 'now()';

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
     * @ORM\OneToMany(targetEntity="RegistroRue", mappedBy="registroRue", cascade={"remove"})
     */
    protected $registros;
    

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
     * Set operacionRue
     *
     * @param string $operacionRue
     * @return FormularioRue
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return FormularioRue
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return FormularioRue
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
	public function getRegistros() {
		return $this->registros;
	}
	public function setRegistros($registros) {
		$this->registros = $registros;
		return $this;
	}
	
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->registros = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add registros
     *
     * @param \Sie\AppWebBundle\Entity\RegistroRue $registros
     * @return FormularioRue
     */
    public function addRegistro(\Sie\AppWebBundle\Entity\RegistroRue $registros)
    {
        $this->registros[] = $registros;
    
        return $this;
    }

    /**
     * Remove registros
     *
     * @param \Sie\AppWebBundle\Entity\RegistroRue $registros
     */
    public function removeRegistro(\Sie\AppWebBundle\Entity\RegistroRue $registros)
    {
        $this->registros->removeElement($registros);
    }
}
