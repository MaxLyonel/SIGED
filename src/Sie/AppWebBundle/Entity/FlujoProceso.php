<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FlujoProceso
 */
class FlujoProceso
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\ProcesoTipo
     */
    private $proceso;

    /**
     * @var \Sie\AppWebBundle\Entity\FlujoTipo
     */
    private $flujoTipo;


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
     * @return FlujoProceso
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
     * Set proceso
     *
     * @param \Sie\AppWebBundle\Entity\ProcesoTipo $proceso
     * @return FlujoProceso
     */
    public function setProceso(\Sie\AppWebBundle\Entity\ProcesoTipo $proceso = null)
    {
        $this->proceso = $proceso;
    
        return $this;
    }

    /**
     * Get proceso
     *
     * @return \Sie\AppWebBundle\Entity\ProcesoTipo 
     */
    public function getProceso()
    {
        return $this->proceso;
    }

    /**
     * Set flujoTipo
     *
     * @param \Sie\AppWebBundle\Entity\FlujoTipo $flujoTipo
     * @return FlujoProceso
     */
    public function setFlujoTipo(\Sie\AppWebBundle\Entity\FlujoTipo $flujoTipo = null)
    {
        $this->flujoTipo = $flujoTipo;
    
        return $this;
    }

    /**
     * Get flujoTipo
     *
     * @return \Sie\AppWebBundle\Entity\FlujoTipo 
     */
    public function getFlujoTipo()
    {
        return $this->flujoTipo;
    }
    /**
     * @var integer
     */
    private $orden;

    /**
     * @var \Sie\AppWebBundle\Entity\RolTipo
     */
    private $rolTipo;


    /**
     * Set orden
     *
     * @param integer $orden
     * @return FlujoProceso
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;
    
        return $this;
    }

    /**
     * Get orden
     *
     * @return integer 
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * Set rolTipo
     *
     * @param \Sie\AppWebBundle\Entity\RolTipo $rolTipo
     * @return FlujoProceso
     */
    public function setRolTipo(\Sie\AppWebBundle\Entity\RolTipo $rolTipo = null)
    {
        $this->rolTipo = $rolTipo;
    
        return $this;
    }

    /**
     * Get rolTipo
     *
     * @return \Sie\AppWebBundle\Entity\RolTipo 
     */
    public function getRolTipo()
    {
        return $this->rolTipo;
    }
    /**
     * @var integer
     */
    private $procesoId;

    /**
     * @var boolean
     */
    private $esEvaluacion;

    /**
     * @var integer
     */
    private $plazo;

    /**
     * @var integer
     */
    private $tareaAntId;

    /**
     * @var integer
     */
    private $tareaSigId;

    /**
     * @var string
     */
    private $variableEvaluacion;

    /**
     * @var \Sie\AppWebBundle\Entity\WfAsignacionTareaTipo
     */
    private $wfAsignacionTareaTipo;


    /**
     * Set procesoId
     *
     * @param integer $procesoId
     * @return FlujoProceso
     */
    public function setProcesoId($procesoId)
    {
        $this->procesoId = $procesoId;
    
        return $this;
    }

    /**
     * Get procesoId
     *
     * @return integer 
     */
    public function getProcesoId()
    {
        return $this->procesoId;
    }

    /**
     * Set esEvaluacion
     *
     * @param boolean $esEvaluacion
     * @return FlujoProceso
     */
    public function setEsEvaluacion($esEvaluacion)
    {
        $this->esEvaluacion = $esEvaluacion;
    
        return $this;
    }

    /**
     * Get esEvaluacion
     *
     * @return boolean 
     */
    public function getEsEvaluacion()
    {
        return $this->esEvaluacion;
    }

    /**
     * Set plazo
     *
     * @param integer $plazo
     * @return FlujoProceso
     */
    public function setPlazo($plazo)
    {
        $this->plazo = $plazo;
    
        return $this;
    }

    /**
     * Get plazo
     *
     * @return integer 
     */
    public function getPlazo()
    {
        return $this->plazo;
    }

    /**
     * Set tareaAntId
     *
     * @param integer $tareaAntId
     * @return FlujoProceso
     */
    public function setTareaAntId($tareaAntId)
    {
        $this->tareaAntId = $tareaAntId;
    
        return $this;
    }

    /**
     * Get tareaAntId
     *
     * @return integer 
     */
    public function getTareaAntId()
    {
        return $this->tareaAntId;
    }

    /**
     * Set tareaSigId
     *
     * @param integer $tareaSigId
     * @return FlujoProceso
     */
    public function setTareaSigId($tareaSigId)
    {
        $this->tareaSigId = $tareaSigId;
    
        return $this;
    }

    /**
     * Get tareaSigId
     *
     * @return integer 
     */
    public function getTareaSigId()
    {
        return $this->tareaSigId;
    }

    /**
     * Set variableEvaluacion
     *
     * @param string $variableEvaluacion
     * @return FlujoProceso
     */
    public function setVariableEvaluacion($variableEvaluacion)
    {
        $this->variableEvaluacion = $variableEvaluacion;
    
        return $this;
    }

    /**
     * Get variableEvaluacion
     *
     * @return string 
     */
    public function getVariableEvaluacion()
    {
        return $this->variableEvaluacion;
    }

    /**
     * Set wfAsignacionTareaTipo
     *
     * @param \Sie\AppWebBundle\Entity\WfAsignacionTareaTipo $wfAsignacionTareaTipo
     * @return FlujoProceso
     */
    public function setWfAsignacionTareaTipo(\Sie\AppWebBundle\Entity\WfAsignacionTareaTipo $wfAsignacionTareaTipo = null)
    {
        $this->wfAsignacionTareaTipo = $wfAsignacionTareaTipo;
    
        return $this;
    }

    /**
     * Get wfAsignacionTareaTipo
     *
     * @return \Sie\AppWebBundle\Entity\WfAsignacionTareaTipo 
     */
    public function getWfAsignacionTareaTipo()
    {
        return $this->wfAsignacionTareaTipo;
    }
}
