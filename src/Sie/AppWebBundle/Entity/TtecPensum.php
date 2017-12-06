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
    private $resolucionAdministriva;

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
     * Set resolucionAdministriva
     *
     * @param string $resolucionAdministriva
     * @return TtecPensum
     */
    public function setResolucionAdministriva($resolucionAdministriva)
    {
        $this->resolucionAdministriva = $resolucionAdministriva;
    
        return $this;
    }

    /**
     * Get resolucionAdministriva
     *
     * @return string 
     */
    public function getResolucionAdministriva()
    {
        return $this->resolucionAdministriva;
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
}
