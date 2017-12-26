<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecPensum
 */
class TtecPensum
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $pensum;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var string
     */
    private $resolucionAdministrativa;

    /**
     * @var string
     */
    private $nroResolucion;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var boolean
     */
    private $esVigente;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo
     */
    private $ttecDenominacionTituloProfesionalTipo;


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
     * Set pensum
     *
     * @param string $pensum
     * @return TtecPensum
     */
    public function setPensum($pensum)
    {
        $this->pensum = $pensum;
    
        return $this;
    }

    /**
     * Get pensum
     *
     * @return string 
     */
    public function getPensum()
    {
        return $this->pensum;
    }

    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return TtecPensum
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
     * Set resolucionAdministrativa
     *
     * @param string $resolucionAdministrativa
     * @return TtecPensum
     */
    public function setResolucionAdministrativa($resolucionAdministrativa)
    {
        $this->resolucionAdministrativa = $resolucionAdministrativa;
    
        return $this;
    }

    /**
     * Get resolucionAdministrativa
     *
     * @return string 
     */
    public function getResolucionAdministrativa()
    {
        return $this->resolucionAdministrativa;
    }

    /**
     * Set nroResolucion
     *
     * @param string $nroResolucion
     * @return TtecPensum
     */
    public function setNroResolucion($nroResolucion)
    {
        $this->nroResolucion = $nroResolucion;
    
        return $this;
    }

    /**
     * Get nroResolucion
     *
     * @return string 
     */
    public function getNroResolucion()
    {
        return $this->nroResolucion;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecPensum
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
     * @return TtecPensum
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
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return TtecPensum
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
     * Set ttecDenominacionTituloProfesionalTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo $ttecDenominacionTituloProfesionalTipo
     * @return TtecPensum
     */
    public function setTtecDenominacionTituloProfesionalTipo(\Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo $ttecDenominacionTituloProfesionalTipo = null)
    {
        $this->ttecDenominacionTituloProfesionalTipo = $ttecDenominacionTituloProfesionalTipo;
    
        return $this;
    }

    /**
     * Get ttecDenominacionTituloProfesionalTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo 
     */
    public function getTtecDenominacionTituloProfesionalTipo()
    {
        return $this->ttecDenominacionTituloProfesionalTipo;
    }
}
