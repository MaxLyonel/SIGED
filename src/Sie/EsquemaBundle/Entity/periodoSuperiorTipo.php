<?php

namespace Sie\EsquemaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * periodoSuperiorTipo
 *
 * @ORM\Table(name="superior.periodo_superior_tipo")
 * @ORM\Entity
 */
class periodoSuperiorTipo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="superior.periodo_superior_tipo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="periodo_superior", type="string", length=50, nullable=false)
     */
    private $periodoSuperior;

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
     * Set periodoSuperior
     *
     * @param string $periodoSuperior
     * @return periodoSuperiorTipo
     */
    public function setPeriodoSuperior($periodoSuperior)
    {
        $this->periodoSuperior = $periodoSuperior;
    
        return $this;
    }

    /**
     * Get periodoSuperior
     *
     * @return string 
     */
    public function getPeriodoSuperior()
    {
        return $this->periodoSuperior;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return periodoSuperiorTipo
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
