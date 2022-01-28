<?php

namespace Sie\EsquemaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * acreditacionTipo
 *
 * @ORM\Table(name="superior.acreditacion_tipo")
 * @ORM\Entity
 */
class acreditacionTipo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="superior.acreditacion_tipo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="codigo", type="integer", nullable=true)
     */
    private $codigo;

    /**
     * @var string
     *
     * @ORM\Column(name="acreditacion", type="string", length=50, nullable=true)
     */
    private $acreditacion;

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
     * Set codigo
     *
     * @param integer $codigo
     * @return acreditacionTipo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return integer 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set acreditacion
     *
     * @param string $acreditacion
     * @return acreditacionTipo
     */
    public function setAcreditacion($acreditacion)
    {
        $this->acreditacion = $acreditacion;
    
        return $this;
    }

    /**
     * Get acreditacion
     *
     * @return string 
     */
    public function getAcreditacion()
    {
        return $this->acreditacion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return acreditacionTipo
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
