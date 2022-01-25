<?php

namespace Sie\InfraBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

class SheetFourController extends Controller
{
  public $session;

    public function __construct(){
      $this->session = new Session();
    }
    public function indexAction(Request $request){

        // dump($request);die;

        return $this->render('SieInfraBundle:SheetFour:index.html.twig', array(
                // ...
            ));
        }

    public function accessAction()
    {
        return $this->render('SieInfraBundle:SheetFour:access.html.twig', array(
                // ...
            ));    }

    public function saveAction(Request $request){
        dump($request);die;
    }

}
