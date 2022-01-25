<?php

namespace Sie\AppWebBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * EspecialServicioTipo
 *
 * @ORM\Table(name="especial_servicio_tipo")
 * @ORM\Entity
 */
class EspecialServicioTipo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="especial_servicio_tipo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="servicio", type="string", length=70, nullable=false)
     */
    private $servicio;

    /**
     * @var integer
     *
     * @ORM\Column(name="area_especial_tipo_id", type="smallint", nullable=true)
     */
    private $areaEspecialTipoId;



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
     * Set servicio
     *
     * @param string $servicio
     * @return EspecialServicioTipo
     */
    public function setServicio($servicio)
    {
        $this->servicio = $servicio;
    
        return $this;
    }

    /**
     * Get servicio
     *
     * @return string 
     */
    public function getServicio()
    {
        return $this->servicio;
    }

    /**
     * Set areaEspecialTipoId
     *
     * @param integer $areaEspecialTipoId
     * @return EspecialServicioTipo
     */
    public function setAreaEspecialTipoId($areaEspecialTipoId)
    {
        $this->areaEspecialTipoId = $areaEspecialTipoId;
    
        return $this;
    }

    /**
     * Get areaEspecialTipoId
     *
     * @return integer 
     */
    public function getAreaEspecialTipoId()
    {
        return $this->areaEspecialTipoId;
    }
}
