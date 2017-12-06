<?php


namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EspecialAreaTipo
 *
 * @ORM\Table(name="especial_area_tipo")
 * @ORM\Entity
 */
class EspecialAreaTipo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="especial_area_tipo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="area_especial", type="string", length=70, nullable=false)
     */
    private $areaEspecial;



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
     * Set areaEspecial
     *
     * @param string $areaEspecial
     * @return EspecialAreaTipo
     */
    public function setAreaEspecial($areaEspecial)
    {
        $this->areaEspecial = $areaEspecial;
    
        return $this;
    }

    /**
     * Get areaEspecial
     *
     * @return string 
     */
    public function getAreaEspecial()
    {
        return $this->areaEspecial;
    }
}
