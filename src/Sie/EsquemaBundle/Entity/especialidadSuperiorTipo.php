<?php

namespace Sie\EsquemaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * especialidadSuperiorTipo
 *
 * @ORM\Table(name="superior.especialidad_superior_tipo", indexes={@ORM\Index(name="IDX_16E39EFE7FDF747", columns={"facultad_area_tipo_id"})})
 * @ORM\Entity
 */
class especialidadSuperiorTipo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="superior.especialidad_superior_tipo_id_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="especialidad_especialidad", type="string", length=50, nullable=true)
     */
    private $especialidadEspecialidad;

    /**
     * @var string
     *
     * @ORM\Column(name="obs", type="string", length=255, nullable=true)
     */
    private $obs;

    /**
     * @var \facultadAreaTipo
     *
     * @ORM\ManyToOne(targetEntity="facultadAreaTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="facultad_area_tipo_id", referencedColumnName="id")
     * })
     */
    private $facultadAreaTipo;



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
     * @return especialidadSuperiorTipo
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
     * Set especialidadEspecialidad
     *
     * @param string $especialidadEspecialidad
     * @return especialidadSuperiorTipo
     */
    public function setEspecialidadEspecialidad($especialidadEspecialidad)
    {
        $this->especialidadEspecialidad = $especialidadEspecialidad;
    
        return $this;
    }

    /**
     * Get especialidadEspecialidad
     *
     * @return string 
     */
    public function getEspecialidadEspecialidad()
    {
        return $this->especialidadEspecialidad;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return especialidadSuperiorTipo
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
     * Set facultadAreaTipo
     *
     * @param \Sie\EsquemaBundle\Entity\facultadAreaTipo $facultadAreaTipo
     * @return especialidadSuperiorTipo
     */
    public function setFacultadAreaTipo(\Sie\EsquemaBundle\Entity\facultadAreaTipo $facultadAreaTipo = null)
    {
        $this->facultadAreaTipo = $facultadAreaTipo;
    
        return $this;
    }

    /**
     * Get facultadAreaTipo
     *
     * @return \Sie\EsquemaBundle\Entity\facultadAreaTipo 
     */
    public function getFacultadAreaTipo()
    {
        return $this->facultadAreaTipo;
    }
}
