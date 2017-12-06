<?php

namespace Sie\EsquemaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * turnoSuperiorTipo
 *
 * @ORM\Table(name="superior.turno_superior_tipo")
 * @ORM\Entity
 */
class turnoSuperiorTipo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="superior.turno_superior_tipo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="turno_superior", type="string", length=120, nullable=true)
     */
    private $turnoSuperior;

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
     * Set turnoSuperior
     *
     * @param string $turnoSuperior
     * @return turnoSuperiorTipo
     */
    public function setTurnoSuperior($turnoSuperior)
    {
        $this->turnoSuperior = $turnoSuperior;
    
        return $this;
    }

    /**
     * Get turnoSuperior
     *
     * @return string 
     */
    public function getTurnoSuperior()
    {
        return $this->turnoSuperior;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return turnoSuperiorTipo
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
