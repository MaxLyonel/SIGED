<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GradoDiscapacidadTipo
 *
 * @ORM\Table(name="grado_discapacidad_tipo")
 * @ORM\Entity
 */
class GradoDiscapacidadTipo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="grado_discapacidad_tipo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="grado_discapacidad", type="string", length=70, nullable=false)
     */
    private $gradoDiscapacidad;

    public function __toString(){
        return $this->gradoDiscapacidad;
    }

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
     * Set gradoDiscapacidad
     *
     * @param string $gradoDiscapacidad
     * @return GradoDiscapacidadTipo
     */
    public function setGradoDiscapacidad($gradoDiscapacidad)
    {
        $this->gradoDiscapacidad = $gradoDiscapacidad;
    
        return $this;
    }

    /**
     * Get gradoDiscapacidad
     *
     * @return string 
     */
    public function getGradoDiscapacidad()
    {
        return $this->gradoDiscapacidad;
    }
}
