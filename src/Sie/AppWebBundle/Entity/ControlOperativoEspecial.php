<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ControlOperativoEspecial
 *
 * @ORM\Table(name="control_operativo_especial")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ControlOperativoEspecialRepository")

 */
class ControlOperativoEspecial
{
    /**
     * @var integer
     */
    private $id;
    
    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var integer
     */
    private $especialAreaTipoId;

    /**
     * @var integer
     */
    private $nivelTipoId;

    /**
     * @var integer
     */
    private $especialProgramaTipoId;

    /**
     * @var integer
     */
    private $especialServicioTipoId;

    /**
     * @var integer
     */
    private $especialEspecialidadTecnicaId;

    /**
     * @var integer
     */
    private $periodoTipoId;

    /**
     * @var integer
     */
    private $estadoMatriculaId;

     /**
     * @var boolean
     */
    private $estadoInscripcion;

    /**
     * @var string
     */
    private $tipoEvaluacion;

    /**
     * @var integer
     */
    private $notaTipoId;

    /**
     * @var boolean
     */
    private $estadoLlenadoRude;

    /**
     * @var boolean
     */
    private $estadoLlenadoNotas;

    /**
     * @var boolean
     */
    private $estadoLibreta;

     /**
     * @var string
     */
    private $operativo;

    /**
     * @var boolean
     */
    private $estadoOperativo;

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
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return Consolidacion
     */
    public function setGestionTipoId($gestionTipoId)
    {
        $this->gestionTipoId = $gestionTipoId;
    
        return $this;
    }

    /**
     * Get gestionTipoId
     *
     * @return integer 
     */
    public function getGestionTipoId()
    {
        return $this->gestionTipoId;
    }

    /**
     * Set especialAreaTipoId
     *
     * @param integer $especialAreaTipoId
     * @return Consolidacion
     */
    public function setEspecialAreaTipoId($especialAreaTipoId)
    {
        $this->especialAreaTipoId = $especialAreaTipoId;
    
        return $this;
    }

    /**
     * Get especialAreaTipoId
     *
     * @return integer 
     */
    public function getEspecialAreaTipoId()
    {
        return $this->especialAreaTipoId;
    }

    /**
     * Set gestionTipoId
     *
     * @param integer $nivelTipoId
     * @return Consolidacion
     */
    public function setNivelTipoId($nivelTipoId)
    {
        $this->nivelTipoId = $nivelTipoId;
    
        return $this;
    }

    /**
     * Get nivelTipoId
     *
     * @return integer 
     */
    public function getNivelTipoId()
    {
        return $this->nivelTipoId;
    }

     /**
     * Set especialProgramaTipoId
     *
     * @param integer $especialProgramaTipoId
     * @return Consolidacion
     */
    public function setEspecialProgramaTipoId($especialProgramaTipoId)
    {
        $this->especialProgramaTipoId = $especialProgramaTipoId;
    
        return $this;
    }

    /**
     * Get especialProgramaTipoId
     *
     * @return integer 
     */
    public function getEspecialProgramaTipoId()
    {
        return $this->especialProgramaTipoId;
    }

      /**
     * Set especialServicioTipoId
     *
     * @param integer $especialServicioTipoId
     * @return Consolidacion
     */
    public function setEspecialServicioTipoId($especialServicioTipoId)
    {
        $this->especialServicioTipoId = $especialServicioTipoId;
    
        return $this;
    }

    /**
     *ServicioecialServicioTipoId
     *
     * @return integer 
     */
    public function getEspecialServicioTipoId()
    {
        return $this->especialServicioTipoId;
    }

     /**
     * Set especialEspecialidadTecnicaId
     *
     * @param integer $especialEspecialidadTecnicaId
     * @return Consolidacion
     */
    public function setEspecialEspecialidadTecnicaId($especialEspecialidadTecnicaId)
    {
        $this->especialEspecialidadTecnicaId = $especialEspecialidadTecnicaId;
    
        return $this;
    }

    /**
     *especialEspecialidadTecnicaId
     *
     * @return integer 
     */
    public function getEspecialEspecialidadTecnicaId()
    {
        return $this->especialEspecialidadTecnicaId;
    }

     /**
     * Set periodoTipoId
     *
     * @param integer $periodoTipoId
     * @return Consolidacion
     */
    public function setPeriodoTipoId($periodoTipoId)
    {
        $this->periodoTipoId = $periodoTipoId;
    
        return $this;
    }

    /**
     * Get periodoTipoId
     *
     * @return integer 
     */
    public function getPeriodoTipoId()
    {
        return $this->periodoTipoId;
    }

     /**
     * Set estadoMatriculaId
     *
     * @param integer $estadoMatriculaId
     * @return Consolidacion
     */
    public function setEstadoMatriculaId($estadoMatriculaId)
    {
        $this->estadoMatriculaId = $estadoMatriculaId;
    
        return $this;
    }

    /**
     * Get periodoTipoId
     *
     * @return integer 
     */
    public function getEstadoMatriculaId()
    {
        return $this->estadoMatriculaId;
    }
     /**
     * Set estadoInscripcion
     *
     * @param boolean $estadoInscripcion
     * @return Consolidacion
     */
    public function setEstadoInscripcion($estadoInscripcion)
    {
        $this->estadoInscripcion = $estadoInscripcion;
    
        return $this;
    }

    /**
     * Get estadoInscripcion
     *
     * @return boolean 
     */
    public function getEstadoInscripcion()
    {
        return $this->estadoInscripcion;
    }

    /**
     * Set>tipoEvaluacion
     *
     * @param string >tipoEvaluacion
     * @return Consolidacion
     */
    public function setTipoEvaluacion($tipoEvaluacion)
    {
        $this->tipoEvaluacion = $tipoEvaluacion;
    
        return $this;
    }

    /**
     * Get>tipoEvaluacion
     *
     * @return string 
     */
    public function getTipoEvaluacion()
    {
        return $this->tipoEvaluacion;
    }

    


     /**
     * Set notaTipoId
     *
     * @param integer $notaTipoId
     * @return Consolidacion
     */
    public function setNotaTipoId($notaTipoId)
    {
        $this->notaTipoId = $notaTipoId;
    
        return $this;
    }

    /**
     * Get periodoTipoId
     *
     * @return integer 
     */
    public function getNotaTipoId()
    {
        return $this->notaTipoId;
    }

   

    /**
     * Get estadoLlenadoNotas
     *
     * @return boolean 
     */
    public function getEstadoLlenadoNotas()
    {
        return $this->estadoLlenadoNotas;
    }

     /**
     * Set estadoLlenadoNotas
     *
     * @param boolean $estadoLlenadoNotas
     * @return Consolidacion
     */
    public function setEstadoLlenadoNotas($estadoLlenadoNotas)
    {
        $this->estadoLlenadoNotas = $estadoLlenadoNotas;
    
        return $this;
    }

     /**
     * Get estadoLlenadoRude
     *
     * @return boolean 
     */
    public function getEstadoLlenadoRude()
    {
        return $this->estadoLlenadoRude;
    }

     /**
     * Set estadoLlenadoRude
     *
     * @param boolean $estadoLlenadoRude
     * @return Consolidacion
     */
    public function setEstadoLlenadoRude($estadoLlenadoRude)
    {
        $this->estadoLlenadoRude = $estadoLlenadoRude;
    
        return $this;
    }

      /**
     * Set estadoLibreta
     *
     * @param boolean $estadoLibreta
     * @return Consolidacion
     */
    public function setEstadoLibreta($estadoLibreta)
    {
        $this->estadoLibreta = $estadoLibreta;
    
        return $this;
    }
    /**
     * Get estadoLibreta
     *
     * @return boolean 
     */
    public function getEstadoLibreta()
    {
        return $this->estadoLibreta;
    }

    
     /*operativo
     * Set operativo
     *
     * @param string $operativo
     * @return Consolidacion
     */
    public function setOperativo($obs)
    {
        $this->operativo = $operativo;
    
        return $this;
    }

    /**
     * Get operativo
     *
     * @return string 
     */
    public function getOperativo()
    {
        return $this->operativo;
    }


     /**
     * Set estadoOperativo
     *
     * @param boolean $estadoOperativo
     * @return Consolidacion
     */
    public function setEstadoOperativo($estadoOperativo)
    {
        $this->estadoOperativo = $estadoOperativo;
    
        return $this;
    }

    /**
     * Get estadoOperativo
     *
     * @return boolean 
     */
    public function getEstadoOperativo()
    {
        return $this->estadoOperativo;
    }
}
