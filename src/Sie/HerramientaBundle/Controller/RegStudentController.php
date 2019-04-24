<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

use Sie\AppWebBundle\Entity\CdlIntegrantes;


class RegStudentController extends Controller
{
    public function indexAction(Request $request)
    {
        $idClubLectura = 1;//$request->get('id');
        $sie = 40730067;//$request->get('sie');
        $gestion = 2019;//$request->get('gestion');

        return $this->render('SieHerramientaBundle:RegStudent:index.html.twig', array(
                'integrantes'=>$this->integrantes($idClubLectura),
                'sie'=>$sie,
                'gestion'=>$gestion,
                'cdl'=>$idClubLectura
        ));    
    }

    public function integrantes($idClubLectura){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:CdlClubLectura');
        $integrantes = $entity->createQueryBuilder('ccl')
                        ->select('ci.id, e.codigoRude, e.paterno, e.materno, e.nombre, ccl.id as cdl')
                        ->innerJoin('SieAppWebBundle:CdlIntegrantes','ci','with','ci.cdlClubLectura = ccl.id')
                        ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','ci.estudianteInscripcion = ei.id')
                        ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                        ->where('ccl.id = :idClub')
                        ->setParameter('idClub', $idClubLectura)
                        ->getQuery()
                        ->getResult();

        return $integrantes;
    }

    public function findAction(Request $request)
    {

        // dump($request);die;
        $tipo = $request->get('tipo');

        $cdl = $request->get('cdl');

        $sie = $request->get('sie');
        $gestion = $request->get('gestion');

        $em = $this->getDoctrine()->getManager();
        $estudiantes = null;
        if ($tipo == 'rude') {
            $rude = $request->get('rude');
            $estudiantes = $em->createQueryBuilder()
                            ->select('ei.id, e.codigoRude, e.nombre, e.paterno, e.materno')
                            ->from('SieAppWebBundle:Estudiante','e')
                            ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','ei.estudiante = e.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
                            ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                            ->where('e.codigoRude = :rude')
                            ->andWhere('ie.id = :sie')
                            ->andWhere('gt.id = :gestion')
                            ->setParameter('rude', $rude)
                            ->setParameter('sie', $sie)
                            ->setParameter('gestion', $gestion)
                            ->getQuery()
                            ->getResult();
        }else{
            $nombre = $request->get('nombre');
            $paterno = $request->get('paterno');
            $materno = $request->get('materno');
            $estudiantes = $em->createQueryBuilder()
                            ->select('ei.id, e.codigoRude, e.nombre, e.paterno, e.materno')
                            ->from('SieAppWebBundle:Estudiante','e')
                            ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','ei.estudiante = e.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
                            ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                            ->where('e.nombre = :nombre')
                            ->andWhere('e.paterno = :paterno')
                            ->andWhere('e.materno = :materno')
                            ->andWhere('ie.id = :sie')
                            ->andWhere('gt.id = :gestion')
                            ->setParameter('nombre', mb_strtoupper($nombre, 'utf-8'))
                            ->setParameter('paterno', mb_strtoupper($paterno, 'utf-8'))
                            ->setParameter('materno', mb_strtoupper($materno, 'utf-8'))
                            ->setParameter('sie', $sie)
                            ->setParameter('gestion', $gestion)
                            ->getQuery()
                            ->getResult();
        }
        
        return $this->render('SieHerramientaBundle:RegStudent:find.html.twig', array(
            'estudiantes'=>$estudiantes,
            'cdl'=> $cdl
        ));    
    }

    public function registerAction(Request $request)
    {
        $cdl = $request->get('cdl');
        $idInscripcion = $request->get('idInscripcion');

        $em = $this->getDoctrine()->getManager();

        // validamos que no este registrado
        $integranteInscrito = $em->getRepository('SieAppWebBundle:CdlIntegrantes')->findOneBy(array(
            'cdlClubLectura'=>$cdl,
            'estudianteInscripcion'=>$idInscripcion
        ));

        if(!$integranteInscrito){
            $cdlIntegrantes = new CdlIntegrantes();
            $cdlIntegrantes->setCdlClubLectura($em->getRepository('SieAppWebBundle:CdlClubLectura')->find($cdl));
            $cdlIntegrantes->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
            $cdlIntegrantes->setObs('');
            $em->persist($cdlIntegrantes);
            $em->flush();
        }else{
            
        }

        return $this->render('SieHerramientaBundle:RegStudent:list.html.twig', array(
            'integrantes'=>$this->integrantes($cdl)
        ));    
    }

    public function deleteAction(Request $request)
    {
        $cdl = $request->get('cdl');
        $id = $request->get('id');

        $em = $this->getDoctrine()->getManager();

        $cdlIntegrantes = $em->getRepository('SieAppWebBundle:CdlIntegrantes')->find($id);
        $em->remove($cdlIntegrantes);
        $em->flush();

        return $this->render('SieHerramientaBundle:RegStudent:list.html.twig', array(
            'integrantes'=>$this->integrantes($cdl)
        ));    
    }

}
