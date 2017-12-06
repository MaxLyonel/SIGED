<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
//use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Usuario
 */
class Usuario implements AdvancedUserInterface {
    /**
     * @var integer
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
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

    public function __toString() {
        return $this->username;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Usuario
     */
    public function setUsername($username) {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Usuario
     */
    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword() {
        return $this->password;
    }

    public function getRoles(){
        $em = $GLOBALS['kernel']->getContainer()->get('doctrine')->getManager();
        $idUsuario = $this->getId();
        //dump($idUsuario);die;
        $usuarioRol = $em->getRepository('SieAppWebBundle:Usuario')->getFindByUserPersolRol($idUsuario);
        //dump($usuarioRol);die();
        $arrayRoles = array();
        //dump($usuarioRol);die;
        // Sele asigna un rol por defecto a los usuarios logueados   6650860     
        $arrayRoles[] = 'ROLE_USER';
        
        foreach ($usuarioRol as $ur) {
            //dump($ur['rol']);die;
            $subsistemas = explode(',',$ur['subSistema']);
            //dump($subsistemas);die;
            foreach ($subsistemas as $sub) {
                $arrayRoles[] = 'ROLE_'.trim($ur['diminutivo']).'_'.trim($sub);                
            } 
        }
        /*// Sele asigna un rol por defecto a los usuarios logueados
        $arrayRoles[] = 'ROLE_USER';

        foreach ($usuarioRol as $ur) {
            switch ($ur->getRolTipo()->getId()) {
                case 3:
                    $arrayRoles[] = 'ROLE_APODERADO';
                    break;
                case 7:
                    $arrayRoles[] = 'ROLE_DEPARTAMENTAL';
                    break;
                case 8:
                    $arrayRoles[] = 'ROLE_NACIONAL';
                    break;
                case 10:
                    $arrayRoles[] = 'ROLE_DISTRITAL';
                    break;
            }
        }
        /*
        $arrayRoles[] = 'ROLE_USER';
        $resp = $GLOBALS['kernel']->getContainer()->get('login')->verificarRolesActivos($this->getPersona()->getId(),'-1');
        foreach ($resp as $key => $r) {
            switch ($r['id']) {
                case 3:
                    $arrayRoles[] = 'ROLE_APODERADO';
                    break;
                case 7:
                    $arrayRoles[] = 'ROLE_DEPARTAMENTAL';
                    break;
                case 8:
                    $arrayRoles[] = 'ROLE_NACIONAL';
                    break;
                case 10:
                    $arrayRoles[] = 'ROLE_DISTRITAL';
                    break;
            }
        }*/
        
        return $arrayRoles;
    }

    public function getSalt(){
        return false;
    }

    public function eraseCredentials(){
        return false;
    }

    public function equals(AdvancedUserInterface $user){
        return $this->getUsername() == $user->getUsername();
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return Usuario
     */
    public function setFechaRegistro($fechaRegistro) {
        $this->fechaRegistro = $fechaRegistro;

        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro() {
        return $this->fechaRegistro;
    }

    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return Usuario
     */
    public function setPersona(\Sie\AppWebBundle\Entity\Persona $persona = null) {
        $this->persona = $persona;

        return $this;
    }

    /**
     * Get persona
     *
     * @return \Sie\AppWebBundle\Entity\Persona 
     */
    public function getPersona() {
        return $this->persona;
    }


    /**
     * @var string
     */
    private $password2;


    /**
     * Set password2
     *
     * @param string $password2
     * @return Usuario
     */
    public function setPassword2($password2)
    {
        $this->password2 = $password2;
    
        return $this;
    }

    /**
     * Get password2
     *
     * @return string 
     */
    public function getPassword2()
    {
        return $this->password2;
    }
    /**
     * @var boolean
     */
    private $esactivo;


    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return Usuario
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

    public function isAccountNonExpired(){
        return true;
    }

    public function isAccountNonLocked(){
        // VERIFICAMOS SI EL USUARIO TIENE ROLES ACTIVOS
        $rolesActivos = $this->getRoles();
        if(count($rolesActivos)>=1){
            return true;
        }else{
            return false;
        }
    }

    public function isCredentialsNonExpired(){
        return true;
    }

    public function isEnabled(){
        return $this->esactivo;
    }


    /**
     * @var integer
     */
    private $estadopassword;


    /**
     * Set estadopassword
     *
     * @param integer $estadopassword
     * @return Usuario
     */
    public function setEstadopassword($estadopassword)
    {
        $this->estadopassword = $estadopassword;
    
        return $this;
    }

    /**
     * Get estadopassword
     *
     * @return integer 
     */
    public function getEstadopassword()
    {
        return $this->estadopassword;
    }
}
