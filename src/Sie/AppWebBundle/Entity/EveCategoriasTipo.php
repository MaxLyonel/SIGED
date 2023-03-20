<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EveCategoriasTipo
 */
class EveCategoriasTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nivel;

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
     * @var string
     */
    private $categoria;

    /**
     * @var \Sie\AppWebBundle\Entity\EveModalidadesTipo
     */
    private $eveModalidadesTipo;


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
     * Set nivel
     *
     * @param string $nivel
     * @return EveCategoriasTipo
     */
    public function setNivel($nivel)
    {
        $this->nivel = $nivel;
    
        return $this;
    }

    /**
     * Get nivel
     *
     * @return string 
     */
    public function getNivel()
    {
        return $this->nivel;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return EveCategoriasTipo
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
     * @return EveCategoriasTipo
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
     * @return EveCategoriasTipo
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
     * Set categoria
     *
     * @param string $categoria
     * @return EveCategoriasTipo
     */
    public function setCategoria($categoria)
    {
        $this->categoria = $categoria;
    
        return $this;
    }

    /**
     * Get categoria
     *
     * @return string 
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
     * Set eveModalidadesTipo
     *
     * @param \Sie\AppWebBundle\Entity\EveModalidadesTipo $eveModalidadesTipo
     * @return EveCategoriasTipo
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
