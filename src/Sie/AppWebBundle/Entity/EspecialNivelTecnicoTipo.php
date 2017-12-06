<?php

namespace Sie\AppWebBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * EspecialNivelTecnicoTipo
 *
 * @ORM\Table(name="especial_nivel_tecnico_tipo")
 * @ORM\Entity
 */
class EspecialNivelTecnicoTipo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="especial_nivel_tecnico_tipo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nivel_tecnico", type="string", length=45, nullable=true)
     */
    private $nivelTecnico;



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
     * Set nivelTecnico
     *
     * @param string $nivelTecnico
     * @return EspecialNivelTecnicoTipo
     */
    public function setNivelTecnico($nivelTecnico)
    {
        $this->nivelTecnico = $nivelTecnico;
    
        return $this;
    }

    /**
     * Get nivelTecnico
     *
     * @return string 
     */
    public function getNivelTecnico()
    {
        return $this->nivelTecnico;
    }
}
