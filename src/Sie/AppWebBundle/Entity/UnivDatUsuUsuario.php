<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivDatUsuUsuario
 */
class UnivDatUsuUsuario
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var string
     */
    private $paterno;

    /**
     * @var string
     */
    private $materno;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $celular;

    /**
     * @var string
     */
    private $telefono;

    /**
     * @var string
     */
    private $estado;

    /**
     * @var string
     */
    private $rolId;

    /**
     * @var string
     */
    private $fCreacion;

    /**
     * @var string
     */
    private $fModificacion;

    /**
     * @var string
     */
    private $revisor;

    /**
     * @var string
     */
    private $foto;


    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return UnivDatUsuUsuario
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return UnivDatUsuUsuario
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return UnivDatUsuUsuario
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set paterno
     *
     * @param string $paterno
     * @return UnivDatUsuUsuario
     */
    public function setPaterno($paterno)
    {
        $this->paterno = $paterno;
    
        return $this;
    }

    /**
     * Get paterno
     *
     * @return string 
     */
    public function getPaterno()
    {
        return $this->paterno;
    }

    /**
     * Set materno
     *
     * @param string $materno
     * @return UnivDatUsuUsuario
     */
    public function setMaterno($materno)
    {
        $this->materno = $materno;
    
        return $this;
    }

    /**
     * Get materno
     *
     * @return string 
     */
    public function getMaterno()
    {
        return $this->materno;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return UnivDatUsuUsuario
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set celular
     *
     * @param string $celular
     * @return UnivDatUsuUsuario
     */
    public function setCelular($celular)
    {
        $this->celular = $celular;
    
        return $this;
    }

    /**
     * Get celular
     *
     * @return string 
     */
    public function getCelular()
    {
        return $this->celular;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     * @return UnivDatUsuUsuario
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
     * Set estado
     *
     * @param string $estado
     * @return UnivDatUsuUsuario
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return string 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set rolId
     *
     * @param string $rolId
     * @return UnivDatUsuUsuario
     */
    public function setRolId($rolId)
    {
        $this->rolId = $rolId;
    
        return $this;
    }

    /**
     * Get rolId
     *
     * @return string 
     */
    public function getRolId()
    {
        return $this->rolId;
    }

    /**
     * Set fCreacion
     *
     * @param string $fCreacion
     * @return UnivDatUsuUsuario
     */
    public function setFCreacion($fCreacion)
    {
        $this->fCreacion = $fCreacion;
    
        return $this;
    }

    /**
     * Get fCreacion
     *
     * @return string 
     */
    public function getFCreacion()
    {
        return $this->fCreacion;
    }

    /**
     * Set fModificacion
     *
     * @param string $fModificacion
     * @return UnivDatUsuUsuario
     */
    public function setFModificacion($fModificacion)
    {
        $this->fModificacion = $fModificacion;
    
        return $this;
    }

    /**
     * Get fModificacion
     *
     * @return string 
     */
    public function getFModificacion()
    {
        return $this->fModificacion;
    }

    /**
     * Set revisor
     *
     * @param string $revisor
     * @return UnivDatUsuUsuario
     */
    public function setRevisor($revisor)
    {
        $this->revisor = $revisor;
    
        return $this;
    }

    /**
     * Get revisor
     *
     * @return string 
     */
    public function getRevisor()
    {
        return $this->revisor;
    }

    /**
     * Set foto
     *
     * @param string $foto
     * @return UnivDatUsuUsuario
     */
    public function setFoto($foto)
    {
        $this->foto = $foto;
    
        return $this;
    }

    /**
     * Get foto
     *
     * @return string 
     */
    public function getFoto()
    {
        return $this->foto;
    }
}
