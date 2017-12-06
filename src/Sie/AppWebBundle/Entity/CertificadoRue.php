<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertificadoRue
 *
 * @ORM\Table(name="certificado_rue", indexes={@ORM\Index(name="IDX_2683A37FDB38439E", columns={"usuario_id"})})
 * @ORM\Entity
 */
class CertificadoRue
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certificado_rue_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_corte", type="date", nullable=true)
     */
    private $fechaCorte = 'now()';

    /**
     * @var integer
     *
     * @ORM\Column(name="nro_certificado_inicio", type="integer", nullable=true)
     */
    private $nroCertificadoInicio;

    /**
     * @var string
     *
     * @ORM\Column(name="observacion", type="string", length=255, nullable=true)
     */
    private $observacion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_registro", type="date", nullable=true)
     */
    private $fechaRegistro = 'now()';

    /**
     * @var \Usuario
     *
     * @ORM\ManyToOne(targetEntity="Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     * })
     */
    private $usuario;

    /**
     * @ORM\OneToMany(targetEntity="CertificadoRueInstitucioneducativa", mappedBy="certificadoRue", cascade={"remove"})
     */
    protected $certificados;
    

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
     * Set fechaCorte
     *
     * @param \DateTime $fechaCorte
     * @return CertificadoRue
     */
    public function setFechaCorte($fechaCorte)
    {
        $this->fechaCorte = $fechaCorte;
    
        return $this;
    }

    /**
     * Get fechaCorte
     *
     * @return \DateTime 
     */
    public function getFechaCorte()
    {
        return $this->fechaCorte;
    }

    /**
     * Set nroCertificadoInicio
     *
     * @param integer $nroCertificadoInicio
     * @return CertificadoRue
     */
    public function setNroCertificadoInicio($nroCertificadoInicio)
    {
        $this->nroCertificadoInicio = $nroCertificadoInicio;
    
        return $this;
    }

    /**
     * Get nroCertificadoInicio
     *
     * @return integer 
     */
    public function getNroCertificadoInicio()
    {
        return $this->nroCertificadoInicio;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return CertificadoRue
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return CertificadoRue
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
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return CertificadoRue
     */
    public function setUsuario(\Sie\AppWebBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    public function getCertificados() {
    	return $this->certificados;
    }
    public function setCertificados($certificados) {
    	$this->certificados = $certificados;
    	return $this;
    }
    
}
