<?php

namespace Sie\EsquemaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * moduloPeriodo
 *
 * @ORM\Table(name="superior.modulo_periodo", indexes={@ORM\Index(name="IDX_89A972429772DAEB", columns={"modulo_tipo_id"}), @ORM\Index(name="IDX_89A972425C545951", columns={"institucioneducativa_periodo_id"})})
 * @ORM\Entity
 */
class moduloPeriodo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="superior.modulo_periodo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="obs", type="string", length=255, nullable=true)
     */
    private $obs;

    /**
     * @var integer
     *
     * @ORM\Column(name="horas_modulo", type="integer", nullable=false)
     */
    private $horasModulo;

    /**
     * @var \moduloTipo
     *
     * @ORM\ManyToOne(targetEntity="moduloTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modulo_tipo_id", referencedColumnName="id")
     * })
     */
    private $moduloTipo;

    /**
     * @var \institucioneducativaPeriodo
     *
     * @ORM\ManyToOne(targetEntity="institucioneducativaPeriodo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="institucioneducativa_periodo_id", referencedColumnName="id")
     * })
     */
    private $institucioneducativaPeriodo;



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
     * Set obs
     *
     * @param string $obs
     * @return moduloPeriodo
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

    /**
     * Set horasModulo
     *
     * @param integer $horasModulo
     * @return moduloPeriodo
     */
    public function setHorasModulo($horasModulo)
    {
        $this->horasModulo = $horasModulo;
    
        return $this;
    }

    /**
     * Get horasModulo
     *
     * @return integer 
     */
    public function getHorasModulo()
    {
        return $this->horasModulo;
    }

    /**
     * Set moduloTipo
     *
     * @param \Sie\EsquemaBundle\Entity\moduloTipo $moduloTipo
     * @return moduloPeriodo
     */
    public function setModuloTipo(\Sie\EsquemaBundle\Entity\moduloTipo $moduloTipo = null)
    {
        $this->moduloTipo = $moduloTipo;
    
        return $this;
    }

    /**
     * Get moduloTipo
     *
     * @return \Sie\EsquemaBundle\Entity\moduloTipo 
     */
    public function getModuloTipo()
    {
        return $this->moduloTipo;
    }

    /**
     * Set institucioneducativaPeriodo
     *
     * @param \Sie\EsquemaBundle\Entity\institucioneducativaPeriodo $institucioneducativaPeriodo
     * @return moduloPeriodo
     */
    public function setInstitucioneducativaPeriodo(\Sie\EsquemaBundle\Entity\institucioneducativaPeriodo $institucioneducativaPeriodo = null)
    {
        $this->institucioneducativaPeriodo = $institucioneducativaPeriodo;
    
        return $this;
    }

    /**
     * Get institucioneducativaPeriodo
     *
     * @return \Sie\EsquemaBundle\Entity\institucioneducativaPeriodo 
     */
    public function getInstitucioneducativaPeriodo()
    {
        return $this->institucioneducativaPeriodo;
    }
}
