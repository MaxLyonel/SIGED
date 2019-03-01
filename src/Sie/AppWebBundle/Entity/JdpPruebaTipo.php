<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpPruebaTipo
 */
class JdpPruebaTipo
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
     * @var string
     */
    private $clasificadorPuntuacion;

    /**
     * @var string
     */
    private $clasificadorTipo;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var \Sie\AppWebBundle\Entity\DisciplinaTipo
     */
    private $disciplinaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GeneroTipo
     */
    private $generoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpPruebaParticipacionTipo
     */
    private $pruebaParticipacionTipo;


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
     * @return JdpPruebaTipo
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
     * Set clasificadorPuntuacion
     *
     * @param string $clasificadorPuntuacion
     * @return JdpPruebaTipo
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
     * @return JdpPruebaTipo
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
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return JdpPruebaTipo
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

    /**
     * Set disciplinaTipo
     *
     * @param \Sie\AppWebBundle\Entity\DisciplinaTipo $disciplinaTipo
     * @return JdpPruebaTipo
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
     * @return JdpPruebaTipo
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
     * Set pruebaParticipacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpPruebaParticipacionTipo $pruebaParticipacionTipo
     * @return JdpPruebaTipo
     */
    public function setPruebaParticipacionTipo(\Sie\AppWebBundle\Entity\JdpPruebaParticipacionTipo $pruebaParticipacionTipo = null)
    {
        $this->pruebaParticipacionTipo = $pruebaParticipacionTipo;
    
        return $this;
    }

    /**
     * Get pruebaParticipacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpPruebaParticipacionTipo 
     */
    public function getPruebaParticipacionTipo()
    {
        return $this->pruebaParticipacionTipo;
    }
}
