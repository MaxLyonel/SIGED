<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PruebaTipo
 */
class PruebaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $prueba;

    /**
     * @var \Sie\AppWebBundle\Entity\DisciplinaTipo
     */
    private $disciplinaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GeneroTipo
     */
    private $generoTipo;


    public function __toString() {
        return $this->getDisciplinaTipo()->getDisciplina().' - '.$this->prueba.' - '.$this->getGeneroTipo()->getGenero();
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
     * Set prueba
     *
     * @param string $prueba
     * @return PruebaTipo
     */
    public function setPrueba($prueba)
    {
        $this->prueba = $prueba;

        return $this;
    }

    /**
     * Get prueba
     *
     * @return string 
     */
    public function getPrueba()
    {
        return $this->prueba;
    }

    /**
     * Set disciplinaTipo
     *
     * @param \Sie\AppWebBundle\Entity\DisciplinaTipo $disciplinaTipo
     * @return PruebaTipo
     */
    public function setDisciplinaTipo(\Sie\AppWebBundle\Entity\DisciplinaTipo $disciplinaTipo = null)
    {
        $this->disciplinaTipo = $disciplinaTipo;

        return $this;
    }

    /**
     * Get disciplinaTipo
     *
     * @return \Sie\AppWebBundle\Entity\DisciplinaTipo 
     */
    public function getDisciplinaTipo()
    {
        return $this->disciplinaTipo;
    }

    /**
     * Set generoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GeneroTipo $generoTipo
     * @return PruebaTipo
     */
    public function setGeneroTipo(\Sie\AppWebBundle\Entity\GeneroTipo $generoTipo = null)
    {
        $this->generoTipo = $generoTipo;

        return $this;
    }

    /**
     * Get generoTipo
     *
     * @return \Sie\AppWebBundle\Entity\GeneroTipo 
     */
    public function getGeneroTipo()
    {
        return $this->generoTipo;
    }
    /**
     * @var string
     */
    private $clasificadorPuntuacion;

    /**
     * @var string
     */
    private $clasificadorTipo;


    /**
     * Set clasificadorPuntuacion
     *
     * @param string $clasificadorPuntuacion
     * @return PruebaTipo
     */
    public function setClasificadorPuntuacion($clasificadorPuntuacion)
    {
        $this->clasificadorPuntuacion = $clasificadorPuntuacion;
    
        return $this;
    }

    /**
     * Get clasificadorPuntuacion
     *
     * @return string 
     */
    public function getClasificadorPuntuacion()
    {
        return $this->clasificadorPuntuacion;
    }

    /**
     * Set clasificadorTipo
     *
     * @param string $clasificadorTipo
     * @return PruebaTipo
     */
    public function setClasificadorTipo($clasificadorTipo)
    {
        $this->clasificadorTipo = $clasificadorTipo;
    
        return $this;
    }

    /**
     * Get clasificadorTipo
     *
     * @return string 
     */
    public function getClasificadorTipo()
    {
        return $this->clasificadorTipo;
    }
    /**
     * @var boolean
     */
    private $esactivo;


    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return PruebaTipo
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }
}
