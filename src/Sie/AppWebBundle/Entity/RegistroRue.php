<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RegistroRue
 *
 * @ORM\Table(name="registro_rue", indexes={@ORM\Index(name="IDX_9D275E3B18BB59DA", columns={"formulario_rue_id"})})
 * @ORM\Entity
 */
class RegistroRue
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="registro_rue_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="operacion_rue", type="string", nullable=true)
     */
    private $operacionRue;

    /**
     * @var string
     *
     * @ORM\Column(name="campo", type="string", length=255, nullable=true)
     */
    private $campo;

    /**
     * @var string
     *
     * @ORM\Column(name="valor_nuevo", type="string", length=255, nullable=true)
     */
    private $valorNuevo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_registro", type="date", nullable=true)
     */
    private $fechaRegistro = 'now()';

    /**
     * @var string
     *
     * @ORM\Column(name="valor_anterior", type="string", length=255, nullable=true)
     */
    private $valorAnterior;

    /**
     * @var string
     *
     * @ORM\Column(name="tabla", type="string", length=255, nullable=true)
     */
    private $tabla;

    /**
     * @var \FormularioRue
     *
     * @ORM\ManyToOne(targetEntity="FormularioRue")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="formulario_rue_id", referencedColumnName="id")
     * })
     */
    private $formularioRue;



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
     * @return RegistroRue
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
     * Set campo
     *
     * @param string $campo
     * @return RegistroRue
     */
    public function setCampo($campo)
    {
        $this->campo = $campo;
    
        return $this;
    }

    /**
     * Get campo
     *
     * @return string 
     */
    public function getCampo()
    {
        return $this->campo;
    }

    /**
     * Set valorNuevo
     *
     * @param string $valorNuevo
     * @return RegistroRue
     */
    public function setValorNuevo($valorNuevo)
    {
        $this->valorNuevo = $valorNuevo;
    
        return $this;
    }

    /**
     * Get valorNuevo
     *
     * @return string 
     */
    public function getValorNuevo()
    {
        return $this->valorNuevo;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return RegistroRue
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
     * Set valorAnterior
     *
     * @param string $valorAnterior
     * @return RegistroRue
     */
    public function setValorAnterior($valorAnterior)
    {
        $this->valorAnterior = $valorAnterior;
    
        return $this;
    }

    /**
     * Get valorAnterior
     *
     * @return string 
     */
    public function getValorAnterior()
    {
        return $this->valorAnterior;
    }

    /**
     * Set tabla
     *
     * @param string $tabla
     * @return RegistroRue
     */
    public function setTabla($tabla)
    {
        $this->tabla = $tabla;
    
        return $this;
    }

    /**
     * Get tabla
     *
     * @return string 
     */
    public function getTabla()
    {
        return $this->tabla;
    }

    /**
     * Set formularioRue
     *
     * @param \Sie\AppWebBundle\Entity\FormularioRue $formularioRue
     * @return RegistroRue
     */
    public function setFormularioRue(\Sie\AppWebBundle\Entity\FormularioRue $formularioRue = null)
    {
        $this->formularioRue = $formularioRue;
    
        return $this;
    }

    /**
     * Get formularioRue
     *
     * @return \Sie\AppWebBundle\Entity\FormularioRue 
     */
    public function getFormularioRue()
    {
        return $this->formularioRue;
    }
}
