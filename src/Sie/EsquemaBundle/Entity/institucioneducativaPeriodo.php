<?php

namespace Sie\EsquemaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * institucioneducativaPeriodo
 *
 * @ORM\Table(name="superior.institucioneducativa_periodo", indexes={@ORM\Index(name="IDX_B70233A55753617B", columns={"periodo_superior_tipo_id"}), @ORM\Index(name="IDX_B70233A521C626E3", columns={"institucioneducativa_acreditacion_id"})})
 * @ORM\Entity
 */
class institucioneducativaPeriodo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="superior.institucioneducativa_periodo_id_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="horas_periodo", type="integer", nullable=true)
     */
    private $horasPeriodo;

    /**
     * @var \periodoSuperiorTipo
     *
     * @ORM\ManyToOne(targetEntity="periodoSuperiorTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="periodo_superior_tipo_id", referencedColumnName="id")
     * })
     */
    private $periodoSuperiorTipo;

    /**
     * @var \institucioneducativaAcreditacion
     *
     * @ORM\ManyToOne(targetEntity="institucioneducativaAcreditacion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="institucioneducativa_acreditacion_id", referencedColumnName="id")
     * })
     */
    private $institucioneducativaAcreditacion;



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
     * @return institucioneducativaPeriodo
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
     * Set horasPeriodo
     *
     * @param integer $horasPeriodo
     * @return institucioneducativaPeriodo
     */
    public function setHorasPeriodo($horasPeriodo)
    {
        $this->horasPeriodo = $horasPeriodo;
    
        return $this;
    }

    /**
     * Get horasPeriodo
     *
     * @return integer 
     */
    public function getHorasPeriodo()
    {
        return $this->horasPeriodo;
    }

    /**
     * Set periodoSuperiorTipo
     *
     * @param \Sie\EsquemaBundle\Entity\periodoSuperiorTipo $periodoSuperiorTipo
     * @return institucioneducativaPeriodo
     */
    public function setPeriodoSuperiorTipo(\Sie\EsquemaBundle\Entity\periodoSuperiorTipo $periodoSuperiorTipo = null)
    {
        $this->periodoSuperiorTipo = $periodoSuperiorTipo;
    
        return $this;
    }

    /**
     * Get periodoSuperiorTipo
     *
     * @return \Sie\EsquemaBundle\Entity\periodoSuperiorTipo 
     */
    public function getPeriodoSuperiorTipo()
    {
        return $this->periodoSuperiorTipo;
    }

    /**
     * Set institucioneducativaAcreditacion
     *
     * @param \Sie\EsquemaBundle\Entity\institucioneducativaAcreditacion $institucioneducativaAcreditacion
     * @return institucioneducativaPeriodo
     */
    public function setInstitucioneducativaAcreditacion(\Sie\EsquemaBundle\Entity\institucioneducativaAcreditacion $institucioneducativaAcreditacion = null)
    {
        $this->institucioneducativaAcreditacion = $institucioneducativaAcreditacion;
    
        return $this;
    }

    /**
     * Get institucioneducativaAcreditacion
     *
     * @return \Sie\EsquemaBundle\Entity\institucioneducativaAcreditacion 
     */
    public function getInstitucioneducativaAcreditacion()
    {
        return $this->institucioneducativaAcreditacion;
    }
}
