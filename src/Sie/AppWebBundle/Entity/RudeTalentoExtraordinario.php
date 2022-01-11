<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeTalentoExtraordinario
 */
class RudeTalentoExtraordinario
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $coeficienteintelectual;

    /**
     * @var string
     */
    private $promediocalificaciones;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Rude
     */
    private $rude;

    /**
     * @var \Sie\AppWebBundle\Entity\TalentoExtraordinarioTipo
     */
    private $talentoExtraordinarioTipo;


    /**
     * Set id
     *
     * @param integer $id
     * @return RudeTalentoExtraordinario
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
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
     * Set coeficienteintelectual
     *
     * @param string $coeficienteintelectual
     * @return RudeTalentoExtraordinario
     */
    public function setCoeficienteintelectual($coeficienteintelectual)
    {
        $this->coeficienteintelectual = $coeficienteintelectual;
    
        return $this;
    }

    /**
     * Get coeficienteintelectual
     *
     * @return string 
     */
    public function getCoeficienteintelectual()
    {
        return $this->coeficienteintelectual;
    }

    /**
     * Set promediocalificaciones
     *
     * @param string $promediocalificaciones
     * @return RudeTalentoExtraordinario
     */
    public function setPromediocalificaciones($promediocalificaciones)
    {
        $this->promediocalificaciones = $promediocalificaciones;
    
        return $this;
    }

    /**
     * Get promediocalificaciones
     *
     * @return string 
     */
    public function getPromediocalificaciones()
    {
        return $this->promediocalificaciones;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return RudeTalentoExtraordinario
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
     * @return RudeTalentoExtraordinario
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
     * Set rude
     *
     * @param \Sie\AppWebBundle\Entity\Rude $rude
     * @return RudeTalentoExtraordinario
     */
    public function setRude(\Sie\AppWebBundle\Entity\Rude $rude = null)
    {
        $this->rude = $rude;
    
        return $this;
    }

    /**
     * Get rude
     *
     * @return \Sie\AppWebBundle\Entity\Rude 
     */
    public function getRude()
    {
        return $this->rude;
    }

    /**
     * Set talentoExtraordinarioTipo
     *
     * @param \Sie\AppWebBundle\Entity\TalentoExtraordinarioTipo $talentoExtraordinarioTipo
     * @return RudeTalentoExtraordinario
     */
    public function setTalentoExtraordinarioTipo(\Sie\AppWebBundle\Entity\TalentoExtraordinarioTipo $talentoExtraordinarioTipo = null)
    {
        $this->talentoExtraordinarioTipo = $talentoExtraordinarioTipo;
    
        return $this;
    }

    /**
     * Get talentoExtraordinarioTipo
     *
     * @return \Sie\AppWebBundle\Entity\TalentoExtraordinarioTipo 
     */
    public function getTalentoExtraordinarioTipo()
    {
        return $this->talentoExtraordinarioTipo;
    }
}
