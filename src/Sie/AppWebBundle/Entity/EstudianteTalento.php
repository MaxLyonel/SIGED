<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteTalento
 */
class EstudianteTalento
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $acelera;

    /**
     * @var string
     */
    private $talentoTipo;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioRegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\Estudiante
     */
    private $estudiante;


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
     * Set acelera
     *
     * @param boolean $acelera
     * @return EstudianteTalento
     */
    public function setAcelera($acelera)
    {
        $this->acelera = $acelera;
    
        return $this;
    }

    /**
     * Get acelera
     *
     * @return boolean 
     */
    public function getAcelera()
    {
        return $this->acelera;
    }

    /**
     * Set talentoTipo
     *
     * @param string $talentoTipo
     * @return EstudianteTalento
     */
    public function setTalentoTipo($talentoTipo)
    {
        $this->talentoTipo = $talentoTipo;
    
        return $this;
    }

    /**
     * Get talentoTipo
     *
     * @return string 
     */
    public function getTalentoTipo()
    {
        return $this->talentoTipo;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstudianteTalento
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return EstudianteTalento
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime 
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set usuarioRegistro
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioRegistro
     * @return EstudianteTalento
     */
    public function setUsuarioRegistro(\Sie\AppWebBundle\Entity\Usuario $usuarioRegistro = null)
    {
        $this->usuarioRegistro = $usuarioRegistro;
    
        return $this;
    }

    /**
     * Get usuarioRegistro
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioRegistro()
    {
        return $this->usuarioRegistro;
    }

    /**
     * Set usuarioModificacion
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioModificacion
     * @return EstudianteTalento
     */
    public function setUsuarioModificacion(\Sie\AppWebBundle\Entity\Usuario $usuarioModificacion = null)
    {
        $this->usuarioModificacion = $usuarioModificacion;
    
        return $this;
    }

    /**
     * Get usuarioModificacion
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioModificacion()
    {
        return $this->usuarioModificacion;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return EstudianteTalento
     */
    public function setInstitucioneducativa(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa = null)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }

    /**
     * Set estudiante
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $estudiante
     * @return EstudianteTalento
     */
    public function setEstudiante(\Sie\AppWebBundle\Entity\Estudiante $estudiante = null)
    {
        $this->estudiante = $estudiante;
    
        return $this;
    }

    /**
     * Get estudiante
     *
     * @return \Sie\AppWebBundle\Entity\Estudiante 
     */
    public function getEstudiante()
    {
        return $this->estudiante;
    }
    /**
     * @var string
     */
    private $informe;


    /**
     * Set informe
     *
     * @param string $informe
     * @return EstudianteTalento
     */
    public function setInforme($informe)
    {
        $this->informe = $informe;
    
        return $this;
    }

    /**
     * Get informe
     *
     * @return string 
     */
    public function getInforme()
    {
        return $this->informe;
    }
}
