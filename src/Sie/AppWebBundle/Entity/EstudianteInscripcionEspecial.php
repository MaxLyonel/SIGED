<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionEspecial
 *
 * @ORM\Table(name="estudiante_inscripcion_especial", indexes={@ORM\Index(name="IDX_826A3188AED57829", columns={"institucioneducativa_curso_especial_id"}), @ORM\Index(name="IDX_826A3188FA6D71F9", columns={"grado_discapacidad_tipo_id"}), @ORM\Index(name="IDX_826A3188D2EA1892", columns={"especial_area_tipo_id"}), @ORM\Index(name="IDX_826A3188C4F4AB1B", columns={"socioeconomico_especial_id"}), @ORM\Index(name="IDX_826A3188A1104027", columns={"estudiante_inscripcion_id"})})
 * @ORM\Entity
 */
class EstudianteInscripcionEspecial
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="estudiante_inscripcion_especial_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="informe_psicopedagogico_talento", type="string", length=255, nullable=true)
     */
    private $informePsicopedagogicoTalento;

    /**
     * @var string
     *
     * @ORM\Column(name="evaluacion_pedagogica", type="string", length=255, nullable=true)
     */
    private $evaluacionPedagogica;

    /**
     * @var string
     *
     * @ORM\Column(name="evaluacion_unidad_educativa", type="string", length=255, nullable=true)
     */
    private $evaluacionUnidadEducativa;

    /**
     * @var string
     *
     * @ORM\Column(name="evaluacion_escolaridad", type="string", length=255, nullable=true)
     */
    private $evaluacionEscolaridad;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo_pedagogico", type="string", length=255, nullable=true)
     */
    private $codigoPedagogico;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo_psicopedagogico", type="string", length=255, nullable=true)
     */
    private $codigoPsicopedagogico;

    /**
     * @var string
     *
     * @ORM\Column(name="modalidad_atencion", type="string", nullable=true)
     */
    private $modalidadAtencion;

    /**
     * @var string
     *
     * @ORM\Column(name="dificultad_aprendizaje", type="string", nullable=true)
     */
    private $dificultadAprendizaje;

    /**
     * @var string
     *
     * @ORM\Column(name="deteccion_talento", type="string", nullable=true)
     */
    private $deteccionTalento;

    /**
     * @var string
     *
     * @ORM\Column(name="grado_talento", type="string", nullable=true)
     */
    private $gradoTalento;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo_talento", type="string", nullable=true)
     */
    private $tipoTalento;

    /**
     * @var string
     *
     * @ORM\Column(name="discapacidad", type="string", nullable=true)
     */
    private $discapacidad;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo_discapacidad", type="string", nullable=true)
     */
    private $tipoDiscapacidad;

    /**
     * @var \InstitucioneducativaCursoEspecial
     *
     * @ORM\ManyToOne(targetEntity="InstitucioneducativaCursoEspecial")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="institucioneducativa_curso_especial_id", referencedColumnName="id")
     * })
     */
    private $institucioneducativaCursoEspecial;

    /**
     * @var \GradoDiscapacidadTipo
     *
     * @ORM\ManyToOne(targetEntity="GradoDiscapacidadTipo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grado_discapacidad_tipo_id", referencedColumnName="id")
     * })
     */
    private $gradoDiscapacidadTipo;

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
     * @var \SocioeconomicoEspecial
     *
     * @ORM\ManyToOne(targetEntity="SocioeconomicoEspecial")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="socioeconomico_especial_id", referencedColumnName="id")
     * })
     */
    private $socioeconomicoEspecial;

    /**
     * @var \EstudianteInscripcion
     *
     * @ORM\ManyToOne(targetEntity="EstudianteInscripcion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="estudiante_inscripcion_id", referencedColumnName="id")
     * })
     */
    private $estudianteInscripcion;



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
     * Set informePsicopedagogicoTalento
     *
     * @param string $informePsicopedagogicoTalento
     * @return EstudianteInscripcionEspecial
     */
    public function setInformePsicopedagogicoTalento($informePsicopedagogicoTalento)
    {
        $this->informePsicopedagogicoTalento = $informePsicopedagogicoTalento;
    
        return $this;
    }

    /**
     * Get informePsicopedagogicoTalento
     *
     * @return string 
     */
    public function getInformePsicopedagogicoTalento()
    {
        return $this->informePsicopedagogicoTalento;
    }

    /**
     * Set evaluacionPedagogica
     *
     * @param string $evaluacionPedagogica
     * @return EstudianteInscripcionEspecial
     */
    public function setEvaluacionPedagogica($evaluacionPedagogica)
    {
        $this->evaluacionPedagogica = $evaluacionPedagogica;
    
        return $this;
    }

    /**
     * Get evaluacionPedagogica
     *
     * @return string 
     */
    public function getEvaluacionPedagogica()
    {
        return $this->evaluacionPedagogica;
    }

    /**
     * Set evaluacionUnidadEducativa
     *
     * @param string $evaluacionUnidadEducativa
     * @return EstudianteInscripcionEspecial
     */
    public function setEvaluacionUnidadEducativa($evaluacionUnidadEducativa)
    {
        $this->evaluacionUnidadEducativa = $evaluacionUnidadEducativa;
    
        return $this;
    }

    /**
     * Get evaluacionUnidadEducativa
     *
     * @return string 
     */
    public function getEvaluacionUnidadEducativa()
    {
        return $this->evaluacionUnidadEducativa;
    }

    /**
     * Set evaluacionEscolaridad
     *
     * @param string $evaluacionEscolaridad
     * @return EstudianteInscripcionEspecial
     */
    public function setEvaluacionEscolaridad($evaluacionEscolaridad)
    {
        $this->evaluacionEscolaridad = $evaluacionEscolaridad;
    
        return $this;
    }

    /**
     * Get evaluacionEscolaridad
     *
     * @return string 
     */
    public function getEvaluacionEscolaridad()
    {
        return $this->evaluacionEscolaridad;
    }

    /**
     * Set codigoPedagogico
     *
     * @param string $codigoPedagogico
     * @return EstudianteInscripcionEspecial
     */
    public function setCodigoPedagogico($codigoPedagogico)
    {
        $this->codigoPedagogico = $codigoPedagogico;
    
        return $this;
    }

    /**
     * Get codigoPedagogico
     *
     * @return string 
     */
    public function getCodigoPedagogico()
    {
        return $this->codigoPedagogico;
    }

    /**
     * Set codigoPsicopedagogico
     *
     * @param string $codigoPsicopedagogico
     * @return EstudianteInscripcionEspecial
     */
    public function setCodigoPsicopedagogico($codigoPsicopedagogico)
    {
        $this->codigoPsicopedagogico = $codigoPsicopedagogico;
    
        return $this;
    }

    /**
     * Get codigoPsicopedagogico
     *
     * @return string 
     */
    public function getCodigoPsicopedagogico()
    {
        return $this->codigoPsicopedagogico;
    }

    /**
     * Set modalidadAtencion
     *
     * @param string $modalidadAtencion
     * @return EstudianteInscripcionEspecial
     */
    public function setModalidadAtencion($modalidadAtencion)
    {
        $this->modalidadAtencion = $modalidadAtencion;
    
        return $this;
    }

    /**
     * Get modalidadAtencion
     *
     * @return string 
     */
    public function getModalidadAtencion()
    {
        return $this->modalidadAtencion;
    }

    /**
     * Set dificultadAprendizaje
     *
     * @param string $dificultadAprendizaje
     * @return EstudianteInscripcionEspecial
     */
    public function setDificultadAprendizaje($dificultadAprendizaje)
    {
        $this->dificultadAprendizaje = $dificultadAprendizaje;
    
        return $this;
    }

    /**
     * Get dificultadAprendizaje
     *
     * @return string 
     */
    public function getDificultadAprendizaje()
    {
        return $this->dificultadAprendizaje;
    }

    /**
     * Set deteccionTalento
     *
     * @param string $deteccionTalento
     * @return EstudianteInscripcionEspecial
     */
    public function setDeteccionTalento($deteccionTalento)
    {
        $this->deteccionTalento = $deteccionTalento;
    
        return $this;
    }

    /**
     * Get deteccionTalento
     *
     * @return string 
     */
    public function getDeteccionTalento()
    {
        return $this->deteccionTalento;
    }

    /**
     * Set gradoTalento
     *
     * @param string $gradoTalento
     * @return EstudianteInscripcionEspecial
     */
    public function setGradoTalento($gradoTalento)
    {
        $this->gradoTalento = $gradoTalento;
    
        return $this;
    }

    /**
     * Get gradoTalento
     *
     * @return string 
     */
    public function getGradoTalento()
    {
        return $this->gradoTalento;
    }

    /**
     * Set tipoTalento
     *
     * @param string $tipoTalento
     * @return EstudianteInscripcionEspecial
     */
    public function setTipoTalento($tipoTalento)
    {
        $this->tipoTalento = $tipoTalento;
    
        return $this;
    }

    /**
     * Get tipoTalento
     *
     * @return string 
     */
    public function getTipoTalento()
    {
        return $this->tipoTalento;
    }

    /**
     * Set discapacidad
     *
     * @param string $discapacidad
     * @return EstudianteInscripcionEspecial
     */
    public function setDiscapacidad($discapacidad)
    {
        $this->discapacidad = $discapacidad;
    
        return $this;
    }

    /**
     * Get discapacidad
     *
     * @return string 
     */
    public function getDiscapacidad()
    {
        return $this->discapacidad;
    }

    /**
     * Set tipoDiscapacidad
     *
     * @param string $tipoDiscapacidad
     * @return EstudianteInscripcionEspecial
     */
    public function setTipoDiscapacidad($tipoDiscapacidad)
    {
        $this->tipoDiscapacidad = $tipoDiscapacidad;
    
        return $this;
    }

    /**
     * Get tipoDiscapacidad
     *
     * @return string 
     */
    public function getTipoDiscapacidad()
    {
        return $this->tipoDiscapacidad;
    }

    /**
     * Set institucioneducativaCursoEspecial
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaCursoEspecial $institucioneducativaCursoEspecial
     * @return EstudianteInscripcionEspecial
     */
    public function setInstitucioneducativaCursoEspecial(\Sie\AppWebBundle\Entity\InstitucioneducativaCursoEspecial $institucioneducativaCursoEspecial = null)
    {
        $this->institucioneducativaCursoEspecial = $institucioneducativaCursoEspecial;
    
        return $this;
    }

    /**
     * Get institucioneducativaCursoEspecial
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaCursoEspecial 
     */
    public function getInstitucioneducativaCursoEspecial()
    {
        return $this->institucioneducativaCursoEspecial;
    }

    /**
     * Set gradoDiscapacidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\GradoDiscapacidadTipo $gradoDiscapacidadTipo
     * @return EstudianteInscripcionEspecial
     */
    public function setGradoDiscapacidadTipo(\Sie\AppWebBundle\Entity\GradoDiscapacidadTipo $gradoDiscapacidadTipo = null)
    {
        $this->gradoDiscapacidadTipo = $gradoDiscapacidadTipo;
    
        return $this;
    }

    /**
     * Get gradoDiscapacidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\GradoDiscapacidadTipo 
     */
    public function getGradoDiscapacidadTipo()
    {
        return $this->gradoDiscapacidadTipo;
    }

    /**
     * Set especialAreaTipo
     *
     * @param \Sie\AppWebBundle\Entity\EspecialAreaTipo $especialAreaTipo
     * @return EstudianteInscripcionEspecial
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
     * Set socioeconomicoEspecial
     *
     * @param \Sie\AppWebBundle\Entity\SocioeconomicoEspecial $socioeconomicoEspecial
     * @return EstudianteInscripcionEspecial
     */
    public function setSocioeconomicoEspecial(\Sie\AppWebBundle\Entity\SocioeconomicoEspecial $socioeconomicoEspecial = null)
    {
        $this->socioeconomicoEspecial = $socioeconomicoEspecial;
    
        return $this;
    }

    /**
     * Get socioeconomicoEspecial
     *
     * @return \Sie\AppWebBundle\Entity\SocioeconomicoEspecial 
     */
    public function getSocioeconomicoEspecial()
    {
        return $this->socioeconomicoEspecial;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return EstudianteInscripcionEspecial
     */
    public function setEstudianteInscripcion(\Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion = null)
    {
        $this->estudianteInscripcion = $estudianteInscripcion;
    
        return $this;
    }

    /**
     * Get estudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcion 
     */
    public function getEstudianteInscripcion()
    {
        return $this->estudianteInscripcion;
    }
}
