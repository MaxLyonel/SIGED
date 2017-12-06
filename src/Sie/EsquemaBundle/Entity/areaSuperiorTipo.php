<?php

namespace Sie\EsquemaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * areaSuperiorTipo
 *
 * @ORM\Table(name="superior.area_superior_tipo")
 * @ORM\Entity
 */
class areaSuperiorTipo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="superior.area_superior_tipo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="area_superior", type="string", length=100, nullable=false)
     */
    private $areaSuperior;

    /**
     * @var string
     *
     * @ORM\Column(name="obs", type="string", length=255, nullable=true)
     */
    private $obs;



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
     * Set areaSuperior
     *
     * @param string $areaSuperior
     * @return areaSuperiorTipo
     */
    public function setAreaSuperior($areaSuperior)
    {
        $this->areaSuperior = $areaSuperior;
    
        return $this;
    }

    /**
     * Get areaSuperior
     *
     * @return string 
     */
    public function getAreaSuperior()
    {
        return $this->areaSuperior;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return areaSuperiorTipo
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
}
