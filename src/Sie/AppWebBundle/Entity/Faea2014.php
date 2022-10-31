<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Faea2014
 */
class Faea2014
{
    /**
     * @var string
     */
    private $codUe;

    /**
     * @var string
     */
    private $dpto;

    /**
     * @var string
     */
    private $distrito;

    /**
     * @var string
     */
    private $descUe;

    /**
     * @var string
     */
    private $matricula;

    /**
     * @var string
     */
    private $categoria;

    /**
     * @var string
     */
    private $monto;


    /**
     * Get codUe
     *
     * @return string 
     */
    public function getCodUe()
    {
        return $this->codUe;
    }

    /**
     * Set dpto
     *
     * @param string $dpto
     * @return Faea2014
     */
    public function setDpto($dpto)
    {
        $this->dpto = $dpto;
    
        return $this;
    }

    /**
     * Get dpto
     *
     * @return string 
     */
    public function getDpto()
    {
        return $this->dpto;
    }

    /**
     * Set distrito
     *
     * @param string $distrito
     * @return Faea2014
     */
    public function setDistrito($distrito)
    {
        $this->distrito = $distrito;
    
        return $this;
    }

    /**
     * Get distrito
     *
     * @return string 
     */
    public function getDistrito()
    {
        return $this->distrito;
    }

    /**
     * Set descUe
     *
     * @param string $descUe
     * @return Faea2014
     */
    public function setDescUe($descUe)
    {
        $this->descUe = $descUe;
    
        return $this;
    }

    /**
     * Get descUe
     *
     * @return string 
     */
    public function getDescUe()
    {
        return $this->descUe;
    }

    /**
     * Set matricula
     *
     * @param string $matricula
     * @return Faea2014
     */
    public function setMatricula($matricula)
    {
        $this->matricula = $matricula;
    
        return $this;
    }

    /**
     * Get matricula
     *
     * @return string 
     */
    public function getMatricula()
    {
        return $this->matricula;
    }

    /**
     * Set categoria
     *
     * @param string $categoria
     * @return Faea2014
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
     * Set monto
     *
     * @param string $monto
     * @return Faea2014
     */
    public function setMonto($monto)
    {
        $this->monto = $monto;
    
        return $this;
    }

    /**
     * Get monto
     *
     * @return string 
     */
    public function getMonto()
    {
        return $this->monto;
    }
}
