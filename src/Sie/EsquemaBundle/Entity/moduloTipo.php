<?php

namespace Sie\EsquemaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * moduloTipo
 *
 * @ORM\Table(name="superior.modulo_tipo", indexes={@ORM\Index(name="IDX_FABF7AF8F62B4F27", columns={"area_superior_tipo_id"})})
 * @ORM\Entity
 */
class moduloTipo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="superior.modulo_tipo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="modulo", type="string", length=100, nullable=false)
     */
    private $modulo;

    /**
     * @var string
     *
     * @ORM\Column(name="obs", type="string", length=255, nullable=true)
     */
    private $obs;

    /**
     * @var integer
     *
     * @ORM\Column(name="codigo", type="integer", nullable=true)
     */
    private $codigo;

    /**
     * @var string
     *
     * @ORM\Column(name="sigla", type="string", length=7, nullable=true)
     */
    private $sigla;

    /**
     * @var integer
     *
     * @ORM\Column(name="oficial", type="smallint", nullable=true)
     */
    private $oficial;

    /**
     * @var string
     *
     * @ORM\Column(name="contenido", type="string", length=250, nullable=true)
     */
    private $contenido;

    /**
     * @var \areaSuperiorTipo
     *
     * @ORM\ManyToOne(targetEntity="areaSuperiorTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="area_superior_tipo_id", referencedColumnName="id")
     * })
     */
    private $areaSuperiorTipo;



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
     * Set modulo
     *
     * @param string $modulo
     * @return moduloTipo
     */
    public function setModulo($modulo)
    {
        $this->modulo = $modulo;
    
        return $this;
    }

    /**
     * Get modulo
     *
     * @return string 
     */
    public function getModulo()
    {
        return $this->modulo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return moduloTipo
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
     * Set codigo
     *
     * @param integer $codigo
     * @return moduloTipo
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
     * Set sigla
     *
     * @param string $sigla
     * @return moduloTipo
     */
    public function setSigla($sigla)
    {
        $this->sigla = $sigla;
    
        return $this;
    }

    /**
     * Get sigla
     *
     * @return string 
     */
    public function getSigla()
    {
        return $this->sigla;
    }

    /**
     * Set oficial
     *
     * @param integer $oficial
     * @return moduloTipo
     */
    public function setOficial($oficial)
    {
        $this->oficial = $oficial;
    
        return $this;
    }

    /**
     * Get oficial
     *
     * @return integer 
     */
    public function getOficial()
    {
        return $this->oficial;
    }

    /**
     * Set contenido
     *
     * @param string $contenido
     * @return moduloTipo
     */
    public function setContenido($contenido)
    {
        $this->contenido = $contenido;
    
        return $this;
    }

    /**
     * Get contenido
     *
     * @return string 
     */
    public function getContenido()
    {
        return $this->contenido;
    }

    /**
     * Set areaSuperiorTipo
     *
     * @param \Sie\EsquemaBundle\Entity\areaSuperiorTipo $areaSuperiorTipo
     * @return moduloTipo
     */
    public function setAreaSuperiorTipo(\Sie\EsquemaBundle\Entity\areaSuperiorTipo $areaSuperiorTipo = null)
    {
        $this->areaSuperiorTipo = $areaSuperiorTipo;
    
        return $this;
    }

    /**
     * Get areaSuperiorTipo
     *
     * @return \Sie\EsquemaBundle\Entity\areaSuperiorTipo 
     */
    public function getAreaSuperiorTipo()
    {
        return $this->areaSuperiorTipo;
    }
}
