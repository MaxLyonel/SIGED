<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionHumnisticoTecnico
 */
class EstudianteInscripcionHumnisticoTecnico {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $especialidadTipo;

    public function __toString() {
        return $this->especialidadTipo;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set especialidadTipo
     *
     * @param integer $especialidadTipo
     * @return EstudianteInscripcionHumnisticoTecnico
     */
    public function setEspecialidadTipo($especialidadTipo) {
        $this->especialidadTipo = $especialidadTipo;

        return $this;
    }

    /**
     * Get especialidadTipo
     *
     * @return integer
     */
    public function getEspecialidadTipo() {
        return $this->especialidadTipo;
    }

    /**
     * @var integer
     */
    private $horas;


    /**
     * Set horas
     *
     * @param integer $horas
     * @return EstudianteInscripcionHumnisticoTecnico
     */
    public function setHoras($horas)
    {
        $this->horas = $horas;

        return $this;
    }

    /**
     * Get horas
     *
     * @return integer
     */
    public function getHoras()
    {
        return $this->horas;
    }
    /**
     * @var integer
     */
    private $institucioneducativaHumanisticoId;

    /**
     * @var \Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo
     */
    private $especialidadTecnicoHumanisticoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;


    /**
     * Set institucioneducativaHumanisticoId
     *
     * @param integer $institucioneducativaHumanisticoId
     * @return EstudianteInscripcionHumnisticoTecnico
     */
    public function setInstitucioneducativaHumanisticoId($institucioneducativaHumanisticoId)
    {
        $this->institucioneducativaHumanisticoId = $institucioneducativaHumanisticoId;

        return $this;
    }

    /**
     * Get institucioneducativaHumanisticoId
     *
     * @return integer
     */
    public function getInstitucioneducativaHumanisticoId()
    {
        return $this->institucioneducativaHumanisticoId;
    }

    /**
     * Set especialidadTecnicoHumanisticoTipo
     *
     * @param \Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo $especialidadTecnicoHumanisticoTipo
     * @return EstudianteInscripcionHumnisticoTecnico
     */
    public function setEspecialidadTecnicoHumanisticoTipo(\Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo $especialidadTecnicoHumanisticoTipo = null)
    {
        $this->especialidadTecnicoHumanisticoTipo = $especialidadTecnicoHumanisticoTipo;

        return $this;
    }

    /**
     * Get especialidadTecnicoHumanisticoTipo
     *
     * @return \Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo
     */
    public function getEspecialidadTecnicoHumanisticoTipo()
    {
        return $this->especialidadTecnicoHumanisticoTipo;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return EstudianteInscripcionHumnisticoTecnico
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
    /**
     * @var boolean
     */
    private $esvalido;


    /**
     * Set esvalido
     *
     * @param boolean $esvalido
     * @return EstudianteInscripcionHumnisticoTecnico
     */
    public function setEsvalido($esvalido)
    {
        $this->esvalido = $esvalido;
    
        return $this;
    }

    /**
     * Get esvalido
     *
     * @return boolean 
     */
    public function getEsvalido()
    {
        return $this->esvalido;
    }
}
