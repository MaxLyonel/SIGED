<?php

namespace Sie\EsquemaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * acreditacionEspecialidad
 *
 * @ORM\Table(name="superior.acreditacion_especialidad", indexes={@ORM\Index(name="IDX_1FE32C554754E7F5", columns={"acreditacion_tipo_id"}), @ORM\Index(name="IDX_1FE32C556E9CC31E", columns={"especialidad_superior_tipo_id"})})
 * @ORM\Entity
 */
class acreditacionEspecialidad
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="superior.acreditacion_especialidad_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="obs", type="string", length=255, nullable=true)
     */
    private $obs;

    /**
     * @var \acreditacionTipo
     *
     * @ORM\ManyToOne(targetEntity="acreditacionTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="acreditacion_tipo_id", referencedColumnName="id")
     * })
     */
    private $acreditacionTipo;

    /**
     * @var \especialidadSuperiorTipo
     *
     * @ORM\ManyToOne(targetEntity="especialidadSuperiorTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="especialidad_superior_tipo_id", referencedColumnName="id")
     * })
     */
    private $especialidadSuperiorTipo;



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
     * @return acreditacionEspecialidad
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
     * Set acreditacionTipo
     *
     * @param \Sie\EsquemaBundle\Entity\acreditacionTipo $acreditacionTipo
     * @return acreditacionEspecialidad
     */
    public function setAcreditacionTipo(\Sie\EsquemaBundle\Entity\acreditacionTipo $acreditacionTipo = null)
    {
        $this->acreditacionTipo = $acreditacionTipo;
    
        return $this;
    }

    /**
     * Get acreditacionTipo
     *
     * @return \Sie\EsquemaBundle\Entity\acreditacionTipo 
     */
    public function getAcreditacionTipo()
    {
        return $this->acreditacionTipo;
    }

    /**
     * Set especialidadSuperiorTipo
     *
     * @param \Sie\EsquemaBundle\Entity\especialidadSuperiorTipo $especialidadSuperiorTipo
     * @return acreditacionEspecialidad
     */
    public function setEspecialidadSuperiorTipo(\Sie\EsquemaBundle\Entity\especialidadSuperiorTipo $especialidadSuperiorTipo = null)
    {
        $this->especialidadSuperiorTipo = $especialidadSuperiorTipo;
    
        return $this;
    }

    /**
     * Get especialidadSuperiorTipo
     *
     * @return \Sie\EsquemaBundle\Entity\especialidadSuperiorTipo 
     */
    public function getEspecialidadSuperiorTipo()
    {
        return $this->especialidadSuperiorTipo;
    }
}
