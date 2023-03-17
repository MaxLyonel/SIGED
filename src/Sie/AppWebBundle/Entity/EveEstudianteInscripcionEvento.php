<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EveEstudianteInscripcionEvento
 */
class EveEstudianteInscripcionEvento
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var boolean
     */
    private $esVigente;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\EveCategoriasTipo
     */
    private $eveCategoriasTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EveFaseTipo
     */
    private $eveFaseTipo;


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
     * Set descripcion
     *
     * @param string $descripcion
     * @return EveEstudianteInscripcionEvento
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return EveEstudianteInscripcionEvento
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EveEstudianteInscripcionEvento
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
     * @return EveEstudianteInscripcionEvento
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
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return EveEstudianteInscripcionEvento
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
     * Set eveCategoriasTipo
     *
     * @param \Sie\AppWebBundle\Entity\EveCategoriasTipo $eveCategoriasTipo
     * @return EveEstudianteInscripcionEvento
     */
    public function setEveCategoriasTipo(\Sie\AppWebBundle\Entity\EveCategoriasTipo $eveCategoriasTipo = null)
    {
        $this->eveCategoriasTipo = $eveCategoriasTipo;
    
        return $this;
    }

    /**
     * Get eveCategoriasTipo
     *
     * @return \Sie\AppWebBundle\Entity\EveCategoriasTipo 
     */
    public function getEveCategoriasTipo()
    {
        return $this->eveCategoriasTipo;
    }

    /**
     * Set eveFaseTipo
     *
     * @param \Sie\AppWebBundle\Entity\EveFaseTipo $eveFaseTipo
     * @return EveEstudianteInscripcionEvento
     */
    public function setEveFaseTipo(\Sie\AppWebBundle\Entity\EveFaseTipo $eveFaseTipo = null)
    {
        $this->eveFaseTipo = $eveFaseTipo;
    
        return $this;
    }

    /**
     * Get eveFaseTipo
     *
     * @return \Sie\AppWebBundle\Entity\EveFaseTipo 
     */
    public function getEveFaseTipo()
    {
        return $this->eveFaseTipo;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\EveModalidadesTipo
     */
    private $eveModalidadesTipo;


    /**
     * Set eveModalidadesTipo
     *
     * @param \Sie\AppWebBundle\Entity\EveModalidadesTipo $eveModalidadesTipo
     * @return EveEstudianteInscripcionEvento
     */
    public function setEveModalidadesTipo(\Sie\AppWebBundle\Entity\EveModalidadesTipo $eveModalidadesTipo = null)
    {
        $this->eveModalidadesTipo = $eveModalidadesTipo;
    
        return $this;
    }

    /**
     * Get eveModalidadesTipo
     *
     * @return \Sie\AppWebBundle\Entity\EveModalidadesTipo 
     */
    public function getEveModalidadesTipo()
    {
        return $this->eveModalidadesTipo;
    }
}
