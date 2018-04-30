<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimGrupoProyecto
 */
class OlimGrupoProyecto
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var boolean
     */
    private $esVigente;

    /**
     * @var string
     */
    private $documentoPdf1;

    /**
     * @var string
     */
    private $documentoPdf2;

    /**
     * @var string
     */
    private $documentoPdf3;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var integer
     */
    private $usuarioRegistroId;

    /**
     * @var integer
     */
    private $usuarioModificacionId;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimTutor
     */
    private $olimTutor;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimReglasOlimpiadasTipo
     */
    private $olimReglasOlimpiadasTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimPeriodoTipo
     */
    private $periodoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimCategoriaTipo
     */
    private $categoriaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimMateriaTipo
     */
    private $materiaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimGrupoProyectoTipo
     */
    private $olimGrupoProyectoTipo;

    public function __toString(){
        return $this->nombreGrupo;
    }
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
     * Set nombre
     *
     * @param string $nombre
     * @return OlimGrupoProyecto
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return OlimGrupoProyecto
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
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return OlimGrupoProyecto
     */
    public function setEsVigente($esVigente)
    {
        $this->esVigente = $esVigente;
    
        return $this;
    }

    /**
     * Get esVigente
     *
     * @return boolean 
     */
    public function getEsVigente()
    {
        return $this->esVigente;
    }

    /**
     * Set documentoPdf1
     *
     * @param string $documentoPdf1
     * @return OlimGrupoProyecto
     */
    public function setDocumentoPdf1($documentoPdf1)
    {
        $this->documentoPdf1 = $documentoPdf1;
    
        return $this;
    }

    /**
     * Get documentoPdf1
     *
     * @return string 
     */
    public function getDocumentoPdf1()
    {
        return $this->documentoPdf1;
    }

    /**
     * Set documentoPdf2
     *
     * @param string $documentoPdf2
     * @return OlimGrupoProyecto
     */
    public function setDocumentoPdf2($documentoPdf2)
    {
        $this->documentoPdf2 = $documentoPdf2;
    
        return $this;
    }

    /**
     * Get documentoPdf2
     *
     * @return string 
     */
    public function getDocumentoPdf2()
    {
        return $this->documentoPdf2;
    }

    /**
     * Set documentoPdf3
     *
     * @param string $documentoPdf3
     * @return OlimGrupoProyecto
     */
    public function setDocumentoPdf3($documentoPdf3)
    {
        $this->documentoPdf3 = $documentoPdf3;
    
        return $this;
    }

    /**
     * Get documentoPdf3
     *
     * @return string 
     */
    public function getDocumentoPdf3()
    {
        return $this->documentoPdf3;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return OlimGrupoProyecto
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
     * Set usuarioRegistroId
     *
     * @param integer $usuarioRegistroId
     * @return OlimGrupoProyecto
     */
    public function setUsuarioRegistroId($usuarioRegistroId)
    {
        $this->usuarioRegistroId = $usuarioRegistroId;
    
        return $this;
    }

    /**
     * Get usuarioRegistroId
     *
     * @return integer 
     */
    public function getUsuarioRegistroId()
    {
        return $this->usuarioRegistroId;
    }

    /**
     * Set usuarioModificacionId
     *
     * @param integer $usuarioModificacionId
     * @return OlimGrupoProyecto
     */
    public function setUsuarioModificacionId($usuarioModificacionId)
    {
        $this->usuarioModificacionId = $usuarioModificacionId;
    
        return $this;
    }

    /**
     * Get usuarioModificacionId
     *
     * @return integer 
     */
    public function getUsuarioModificacionId()
    {
        return $this->usuarioModificacionId;
    }

    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return OlimGrupoProyecto
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
     * Set olimTutor
     *
     * @param \Sie\AppWebBundle\Entity\OlimTutor $olimTutor
     * @return OlimGrupoProyecto
     */
    public function setOlimTutor(\Sie\AppWebBundle\Entity\OlimTutor $olimTutor = null)
    {
        $this->olimTutor = $olimTutor;
    
        return $this;
    }

    /**
     * Get olimTutor
     *
     * @return \Sie\AppWebBundle\Entity\OlimTutor 
     */
    public function getOlimTutor()
    {
        return $this->olimTutor;
    }

    /**
     * Set olimReglasOlimpiadasTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimReglasOlimpiadasTipo $olimReglasOlimpiadasTipo
     * @return OlimGrupoProyecto
     */
    public function setOlimReglasOlimpiadasTipo(\Sie\AppWebBundle\Entity\OlimReglasOlimpiadasTipo $olimReglasOlimpiadasTipo = null)
    {
        $this->olimReglasOlimpiadasTipo = $olimReglasOlimpiadasTipo;
    
        return $this;
    }

    /**
     * Get olimReglasOlimpiadasTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimReglasOlimpiadasTipo 
     */
    public function getOlimReglasOlimpiadasTipo()
    {
        return $this->olimReglasOlimpiadasTipo;
    }

    /**
     * Set periodoTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimPeriodoTipo $periodoTipo
     * @return OlimGrupoProyecto
     */
    public function setPeriodoTipo(\Sie\AppWebBundle\Entity\OlimPeriodoTipo $periodoTipo = null)
    {
        $this->periodoTipo = $periodoTipo;
    
        return $this;
    }

    /**
     * Get periodoTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimPeriodoTipo 
     */
    public function getPeriodoTipo()
    {
        return $this->periodoTipo;
    }

    /**
     * Set categoriaTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimCategoriaTipo $categoriaTipo
     * @return OlimGrupoProyecto
     */
    public function setCategoriaTipo(\Sie\AppWebBundle\Entity\OlimCategoriaTipo $categoriaTipo = null)
    {
        $this->categoriaTipo = $categoriaTipo;
    
        return $this;
    }

    /**
     * Get categoriaTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimCategoriaTipo 
     */
    public function getCategoriaTipo()
    {
        return $this->categoriaTipo;
    }

    /**
     * Set materiaTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimMateriaTipo $materiaTipo
     * @return OlimGrupoProyecto
     */
    public function setMateriaTipo(\Sie\AppWebBundle\Entity\OlimMateriaTipo $materiaTipo = null)
    {
        $this->materiaTipo = $materiaTipo;
    
        return $this;
    }

    /**
     * Get materiaTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimMateriaTipo 
     */
    public function getMateriaTipo()
    {
        return $this->materiaTipo;
    }

    /**
     * Set olimGrupoProyectoTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimGrupoProyectoTipo $olimGrupoProyectoTipo
     * @return OlimGrupoProyecto
     */
    public function setOlimGrupoProyectoTipo(\Sie\AppWebBundle\Entity\OlimGrupoProyectoTipo $olimGrupoProyectoTipo = null)
    {
        $this->olimGrupoProyectoTipo = $olimGrupoProyectoTipo;
    
        return $this;
    }

    /**
     * Get olimGrupoProyectoTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimGrupoProyectoTipo 
     */
    public function getOlimGrupoProyectoTipo()
    {
        return $this->olimGrupoProyectoTipo;
    }
    /**
     * @var string
     */
    private $nombreProyecto;


    /**
     * Set nombreProyecto
     *
     * @param string $nombreProyecto
     * @return OlimGrupoProyecto
     */
    public function setNombreProyecto($nombreProyecto)
    {
        $this->nombreProyecto = $nombreProyecto;
    
        return $this;
    }

    /**
     * Get nombreProyecto
     *
     * @return string 
     */
    public function getNombreProyecto()
    {
        return $this->nombreProyecto;
    }
}
