<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaAreaEspecialAutorizado
 *
 * @ORM\Table(name="institucioneducativa_area_especial_autorizado", indexes={@ORM\Index(name="IDX_A666012BD2EA1892", columns={"especial_area_tipo_id"}), @ORM\Index(name="IDX_A666012B3AB163FE", columns={"institucioneducativa_id"})})
 * @ORM\Entity
 */
class InstitucioneducativaAreaEspecialAutorizado
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="institucioneducativa_area_especial_autorizado_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_registro", type="date", nullable=true)
     */
    private $fechaRegistro = 'now()';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_modificacion", type="date", nullable=true)
     */
    private $fechaModificacion;

    /**
     * @var \EspecialAreaTipo
     *
     * @ORM\ManyToOne(targetEntity="EspecialAreaTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="especial_area_tipo_id", referencedColumnName="id")
     * })
     */
    private $especialAreaTipo;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return InstitucioneducativaAreaEspecialAutorizado
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
     * @return InstitucioneducativaAreaEspecialAutorizado
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
     * Set especialAreaTipo
     *
     * @param \Sie\AppWebBundle\Entity\EspecialAreaTipo $especialAreaTipo
     * @return InstitucioneducativaAreaEspecialAutorizado
     */
    public function setEspecialAreaTipo(\Sie\AppWebBundle\Entity\EspecialAreaTipo $especialAreaTipo = null)
    {
        $this->especialAreaTipo = $especialAreaTipo;
    
        return $this;
    }

    /**
     * Get especialAreaTipo
     *
     * @return \Sie\AppWebBundle\Entity\EspecialAreaTipo 
     */
    public function getEspecialAreaTipo()
    {
        return $this->especialAreaTipo;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaAreaEspecialAutorizado
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
