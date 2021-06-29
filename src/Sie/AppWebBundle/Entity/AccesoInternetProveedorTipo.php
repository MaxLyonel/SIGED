<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccesoInternetProveedorTipo
 */
class AccesoInternetProveedorTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $proveedor;

    /**
     * @var boolean
     */
    private $esactivo;


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
     * Set proveedor
     *
     * @param string $proveedor
     * @return AccesoInternetProveedorTipo
     */
    public function setProveedor($proveedor)
    {
        $this->proveedor = $proveedor;
    
        return $this;
    }

    /**
     * Get proveedor
     *
     * @return string 
     */
    public function getProveedor()
    {
        return $this->proveedor;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return AccesoInternetProveedorTipo
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
