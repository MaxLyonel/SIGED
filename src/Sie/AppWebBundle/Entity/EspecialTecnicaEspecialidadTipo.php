<?php

namespace Sie\AppWebBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * EspecialTecnicaEspecialidadTipo
 *
 * @ORM\Table(name="especial_tecnica_especialidad_tipo")
 * @ORM\Entity
 */
class EspecialTecnicaEspecialidadTipo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="especial_tecnica_especialidad_tipo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="especialidad", type="string", length=75, nullable=false)
     */
    private $especialidad;

    /**
     * @var integer
     *
     * @ORM\Column(name="area_tecnica_tipo_id", type="smallint", nullable=true)
     */
    private $areaTecnicaTipoId;



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
     * Set especialidad
     *
     * @param string $especialidad
     * @return EspecialTecnicaEspecialidadTipo
     */
    public function setEspecialidad($especialidad)
    {
        $this->especialidad = $especialidad;
    
        return $this;
    }

    /**
     * Get especialidad
     *
     * @return string 
     */
    public function getEspecialidad()
    {
        return $this->especialidad;
    }

    /**
     * Set areaTecnicaTipoId
     *
     * @param integer $areaTecnicaTipoId
     * @return EspecialTecnicaEspecialidadTipo
     */
    public function setAreaTecnicaTipoId($areaTecnicaTipoId)
    {
        $this->areaTecnicaTipoId = $areaTecnicaTipoId;
    
        return $this;
    }

    /**
     * Get areaTecnicaTipoId
     *
     * @return integer 
     */
    public function getAreaTecnicaTipoId()
    {
        return $this->areaTecnicaTipoId;
    }
}
