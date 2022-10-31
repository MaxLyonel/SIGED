<?php

namespace Sie\AppWebBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * EspecialProgramaTipo
 *
 * @ORM\Table(name="especial_programa_tipo")
 * @ORM\Entity
 */
class EspecialProgramaTipo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="especial_programa_tipo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="programa", type="string", length=70, nullable=false)
     */
    private $programa;

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
     * Set programa
     *
     * @param string $programa
     * @return EspecialProgramaTipo
     */
    public function setPrograma($programa)
    {
        $this->programa = $programa;
    
        return $this;
    }

    /**
     * Get programa
     *
     * @return string 
     */
    public function getPrograma()
    {
        return $this->programa;
    }

    /**
     * Set areaEspecialTipoId
     *
     * @param integer $areaEspecialTipoId
     * @return EspecialProgramaTipo
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
