<?php

namespace Sie\AppWebBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaCursoEspecial
 *
 * @ORM\Table(name="institucioneducativa_curso_especial", indexes={@ORM\Index(name="IDX_1568B052D2EA1892", columns={"especial_area_tipo_id"}), @ORM\Index(name="IDX_1568B05294D2466B", columns={"especial_tecnica_especialidad_tipo_id"}), @ORM\Index(name="IDX_1568B0529E4EDBFE", columns={"institucioneducativa_curso_id"}), @ORM\Index(name="IDX_1568B052C1831C46", columns={"especial_nivel_tecnico_tipo_id"}), @ORM\Index(name="IDX_1568B0521ED4B6D5", columns={"especial_programa_tipo_id"}), @ORM\Index(name="IDX_1568B052B4692AC1", columns={"especial_servicio_tipo_id"})})
 * @ORM\Entity
 */
class InstitucioneducativaCursoEspecial
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="institucioneducativa_curso_especial_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \EspecialAreaTipo
     *
     * @ORM\ManyToOne(targetEntity="EspecialAreaTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="especial_area_tipo_id", referencedColumnName="id")
     * })
     */
    private $especialAreaTipo;

    /**
     * @var \EspecialTecnicaEspecialidadTipo
     *
     * @ORM\ManyToOne(targetEntity="EspecialTecnicaEspecialidadTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="especial_tecnica_especialidad_tipo_id", referencedColumnName="id")
     * })
     */
    private $especialTecnicaEspecialidadTipo;

    /**
     * @var \InstitucioneducativaCurso
     *
     * @ORM\ManyToOne(targetEntity="InstitucioneducativaCurso")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="institucioneducativa_curso_id", referencedColumnName="id")
     * })
     */
    private $institucioneducativaCurso;

    /**
     * @var \EspecialNivelTecnicoTipo
     *
     * @ORM\ManyToOne(targetEntity="EspecialNivelTecnicoTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="especial_nivel_tecnico_tipo_id", referencedColumnName="id")
     * })
     */
    private $especialNivelTecnicoTipo;

    /**
     * @var \EspecialProgramaTipo
     *
     * @ORM\ManyToOne(targetEntity="EspecialProgramaTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="especial_programa_tipo_id", referencedColumnName="id")
     * })
     */
    private $especialProgramaTipo;

    /**
     * @var \EspecialServicioTipo
     *
     * @ORM\ManyToOne(targetEntity="EspecialServicioTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="especial_servicio_tipo_id", referencedColumnName="id")
     * })
     */
    private $especialServicioTipo;



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
     * Set especialAreaTipo
     *
     * @param \Sie\AppWebBundle\Entity\EspecialAreaTipo $especialAreaTipo
     * @return InstitucioneducativaCursoEspecial
     */
    public function setEspecialAreaTipo(\Sie\AppWebBundle\Entity\EspecialAreaTipo $especialAreaTipo = null)
    {
        $this->especialAreaTipo = $especialAreaTipo;
    
        return $this;
    }

    /**
     * Get especialAreaTipo
     *
     * @return \Sie\AppWebBundle\Entity\EspecialAreaTipo 
     */
    public function getEspecialAreaTipo()
    {
        return $this->especialAreaTipo;
    }

    /**
     * Set especialTecnicaEspecialidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\EspecialTecnicaEspecialidadTipo $especialTecnicaEspecialidadTipo
     * @return InstitucioneducativaCursoEspecial
     */
    public function setEspecialTecnicaEspecialidadTipo(\Sie\AppWebBundle\Entity\EspecialTecnicaEspecialidadTipo $especialTecnicaEspecialidadTipo = null)
    {
        $this->especialTecnicaEspecialidadTipo = $especialTecnicaEspecialidadTipo;
    
        return $this;
    }

    /**
     * Get especialTecnicaEspecialidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\EspecialTecnicaEspecialidadTipo 
     */
    public function getEspecialTecnicaEspecialidadTipo()
    {
        return $this->especialTecnicaEspecialidadTipo;
    }

    /**
     * Set institucioneducativaCurso
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaCurso $institucioneducativaCurso
     * @return InstitucioneducativaCursoEspecial
     */
    public function setInstitucioneducativaCurso(\Sie\AppWebBundle\Entity\InstitucioneducativaCurso $institucioneducativaCurso = null)
    {
        $this->institucioneducativaCurso = $institucioneducativaCurso;
    
        return $this;
    }

    /**
     * Get institucioneducativaCurso
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaCurso 
     */
    public function getInstitucioneducativaCurso()
    {
        return $this->institucioneducativaCurso;
    }

    /**
     * Set especialNivelTecnicoTipo
     *
     * @param \Sie\AppWebBundle\Entity\EspecialNivelTecnicoTipo $especialNivelTecnicoTipo
     * @return InstitucioneducativaCursoEspecial
     */
    public function setEspecialNivelTecnicoTipo(\Sie\AppWebBundle\Entity\EspecialNivelTecnicoTipo $especialNivelTecnicoTipo = null)
    {
        $this->especialNivelTecnicoTipo = $especialNivelTecnicoTipo;
    
        return $this;
    }

    /**
     * Get especialNivelTecnicoTipo
     *
     * @return \Sie\AppWebBundle\Entity\EspecialNivelTecnicoTipo 
     */
    public function getEspecialNivelTecnicoTipo()
    {
        return $this->especialNivelTecnicoTipo;
    }

    /**
     * Set especialProgramaTipo
     *
     * @param \Sie\AppWebBundle\Entity\EspecialProgramaTipo $especialProgramaTipo
     * @return InstitucioneducativaCursoEspecial
     */
    public function setEspecialProgramaTipo(\Sie\AppWebBundle\Entity\EspecialProgramaTipo $especialProgramaTipo = null)
    {
        $this->especialProgramaTipo = $especialProgramaTipo;
    
        return $this;
    }

    /**
     * Get especialProgramaTipo
     *
     * @return \Sie\AppWebBundle\Entity\EspecialProgramaTipo 
     */
    public function getEspecialProgramaTipo()
    {
        return $this->especialProgramaTipo;
    }

    /**
     * Set especialServicioTipo
     *
     * @param \Sie\AppWebBundle\Entity\EspecialServicioTipo $especialServicioTipo
     * @return InstitucioneducativaCursoEspecial
     */
    public function setEspecialServicioTipo(\Sie\AppWebBundle\Entity\EspecialServicioTipo $especialServicioTipo = null)
    {
        $this->especialServicioTipo = $especialServicioTipo;
    
        return $this;
    }

    /**
     * Get especialServicioTipo
     *
     * @return \Sie\AppWebBundle\Entity\EspecialServicioTipo 
     */
    public function getEspecialServicioTipo()
    {
        return $this->especialServicioTipo;
    }
}
