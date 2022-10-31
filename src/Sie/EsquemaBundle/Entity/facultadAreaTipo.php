<?php

namespace Sie\EsquemaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * facultadAreaTipo
 *
 * @ORM\Table(name="superior.facultad_area_tipo", indexes={@ORM\Index(name="fki_institucioneducativa_tipo", columns={"institucioneducativa_tipo_id"})})
 * @ORM\Entity
 */
class facultadAreaTipo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="superior.facultad_area_tipo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="codigo", type="integer", nullable=true)
     */
    private $codigo;

    /**
     * @var string
     *
     * @ORM\Column(name="facultad_area", type="string", length=50, nullable=true)
     */
    private $facultadArea;

    /**
     * @var string
     *
     * @ORM\Column(name="obs", type="string", length=255, nullable=true)
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaTipo
     *
     * @ORM\ManyToOne(targetEntity="\Sie\AppWebBundle\Entity\InstitucioneducativaTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="institucioneducativa_tipo_id", referencedColumnName="id")
     * })
     */
    private $institucioneducativaTipo;



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
     * Set codigo
     *
     * @param integer $codigo
     * @return facultadAreaTipo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return integer 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set facultadArea
     *
     * @param string $facultadArea
     * @return facultadAreaTipo
     */
    public function setFacultadArea($facultadArea)
    {
        $this->facultadArea = $facultadArea;
    
        return $this;
    }

    /**
     * Get facultadArea
     *
     * @return string 
     */
    public function getFacultadArea()
    {
        return $this->facultadArea;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return facultadAreaTipo
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
     * Set institucioneducativaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo
     * @return facultadAreaTipo
     */
    public function setInstitucioneducativaTipo(\Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo = null)
    {
        $this->institucioneducativaTipo = $institucioneducativaTipo;
    
        return $this;
    }

    /**
     * Get institucioneducativaTipo
     *
     * @return \Sie\EsquemaBundle\Entity\InstitucioneducativaTipo 
     */
    public function getInstitucioneducativaTipo()
    {
        return $this->institucioneducativaTipo;
    }
}
