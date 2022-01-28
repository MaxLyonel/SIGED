<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PersonaDatos
 */
class PersonaDatos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $telefono;

    /**
     * @var string
     */
    private $correo;

    /**
     * @var string
     */
    private $direccion;

    /**
     * @var integer
     */
    private $gestionId;

    /**
     * @var string
     */
    private $oficialia;

    /**
     * @var string
     */
    private $libro;

    /**
     * @var string
     */
    private $partida;

    /**
     * @var string
     */
    private $folio;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;


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
     * Set telefono
     *
     * @param string $telefono
     * @return PersonaDatos
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Get telefono
     *
     * @return string 
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set correo
     *
     * @param string $correo
     * @return PersonaDatos
     */
    public function setCorreo($correo)
    {
        $this->correo = $correo;

        return $this;
    }

    /**
     * Get correo
     *
     * @return string 
     */
    public function getCorreo()
    {
        return $this->correo;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     * @return PersonaDatos
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * Get direccion
     *
     * @return string 
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set gestionId
     *
     * @param integer $gestionId
     * @return PersonaDatos
     */
    public function setGestionId($gestionId)
    {
        $this->gestionId = $gestionId;

        return $this;
    }

    /**
     * Get gestionId
     *
     * @return integer 
     */
    public function getGestionId()
    {
        return $this->gestionId;
    }

    /**
     * Set oficialia
     *
     * @param string $oficialia
     * @return PersonaDatos
     */
    public function setOficialia($oficialia)
    {
        $this->oficialia = $oficialia;

        return $this;
    }

    /**
     * Get oficialia
     *
     * @return string 
     */
    public function getOficialia()
    {
        return $this->oficialia;
    }

    /**
     * Set libro
     *
     * @param string $libro
     * @return PersonaDatos
     */
    public function setLibro($libro)
    {
        $this->libro = $libro;

        return $this;
    }

    /**
     * Get libro
     *
     * @return string 
     */
    public function getLibro()
    {
        return $this->libro;
    }

    /**
     * Set partida
     *
     * @param string $partida
     * @return PersonaDatos
     */
    public function setPartida($partida)
    {
        $this->partida = $partida;

        return $this;
    }

    /**
     * Get partida
     *
     * @return string 
     */
    public function getPartida()
    {
        return $this->partida;
    }

    /**
     * Set folio
     *
     * @param string $folio
     * @return PersonaDatos
     */
    public function setFolio($folio)
    {
        $this->folio = $folio;

        return $this;
    }

    /**
     * Get folio
     *
     * @return string 
     */
    public function getFolio()
    {
        return $this->folio;
    }

    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return PersonaDatos
     */
    public function setPersona(\Sie\AppWebBundle\Entity\Persona $persona = null)
    {
        $this->persona = $persona;

        return $this;
    }

    /**
     * Get persona
     *
     * @return \Sie\AppWebBundle\Entity\Persona 
     */
    public function getPersona()
    {
        return $this->persona;
    }
}
