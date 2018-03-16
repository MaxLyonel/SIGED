<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH3RiesgosDelitos;
use Sie\AppWebBundle\Entity\InfraestructuraH3DelitosEdificioDetalle;
use Sie\AppWebBundle\Form\InfraestructuraH3RiesgosDelitosType;
use Sie\AppWebBundle\Form\InfraestructuraH3DelitosEdificioDetalleType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * InfraestructuraH3RiesgosDelitos controller.
 *
 */
class InfraestructuraH3RiesgosDelitosController extends Controller
{
    public $session;

    public function __construct(){
        $this->session = new Session();
    }

    /**
     * Lists all InfraestructuraH3RiesgosDelitos entities.
     *
     */
    public function indexAction(Request $request)
    {
        $this->session->set('infjgid', 13395);

        if($this->session->get('infjgid') == null){
            return $this->redirectToRoute('logout');
        }

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH3RiesgosDelitos')->findOneBy(
            array(
                'infraestructuraJuridiccionGeografica'=>$this->session->get('infjgid')
            )
        );

        if(!is_object($entity)){
            $entity = new InfraestructuraH3RiesgosDelitos();
            $this->session->set('infh3id', 'new');
        }else{
            $this->session->set('infh3id', $entity->getId());
        }

        $form = $this->createForm(new InfraestructuraH3RiesgosDelitosType() ,$entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $req = $request->get('sie_appwebbundle_infraestructurah3riesgosdelitos');

            $entity->setInfraestructuraJuridiccionGeografica($em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($this->session->get('infjgid')));
            
            $em->persist($entity);
            $em->flush();

            /**
             * Registro de detalles de delitos
             */
            
            // Eliminamos los registros previos
            $delitosDetalle = $em->getRepository('SieAppWebBundle:InfraestructuraH3DelitosEdificioDetalle')->findBy(array(
                'infraestructuraH3RiesgosDelitos'=>$entity->getId()
            ));

            foreach ($delitosDetalle as $dd) {
                $em->remove($dd);
                $em->flush();
            }

            // Registro
            $id = $request->get('id');
            $gestion = $request->get('gestion');
            $cantidad = $request->get('cantidad');
            $horario = $request->get('horario');
            $ambiente = $request->get('ambiente');
            $mobiliario = $request->get('mobiliario');
            $equipamiento = $request->get('equipamiento');
            $acciones = $request->get('acciones');

            for ($i=0; $i < count($id); $i++) { 
                $detalle = new InfraestructuraH3DelitosEdificioDetalle();
                $detalle->setInfraestructuraH3RiesgosDelitos($entity);
                if($gestion[$i] != ''){ $detalle->setN22GestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion[$i]));}
                $detalle->setN22CantVeces($cantidad[$i]);
                if($horario[$i] != ''){ $detalle->setN22HorarioTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenHorarioTipo')->find($horario[$i]));}
                if($ambiente[$i] != ''){ $detalle->setN22AmbientesTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenAmbientesTipo')->find($ambiente[$i]));}
                if($mobiliario[$i] != ''){ $detalle->setN22MobiliarioTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMobiliarioTipo')->find($mobiliario[$i]));}
                if($equipamiento[$i] != ''){ $detalle->setN22EquipamientoTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEquipamientoTipo')->find($equipamiento[$i]));}
                $detalle->setN22ObsAcciones($acciones[$i]);

                $em->persist($detalle);
                $em->flush();
            }

            /**
             * End registro detalle
             */

            return $this->redirect($this->generateUrl('infraestructurah3riesgosdelitos'));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH3RiesgosDelitos:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
        
    }
    

    /**
     * Funcion para crear delitos
     */
    public function createDelitoAction(){
        $form = $this->createForm(new InfraestructuraH3DelitosEdificioDetalleType());

        return $this->render('SieAppWebBundle:InfraestructuraH3RiesgosDelitos:delitoNew.html.twig', array('form'=>$form->createView()));
    }

    public function getDelitosAction(){

        $data = array();

        if($this->session->get('infh3id') && $this->session->get('infh3id') != 'new'){
            $id = $this->session->get('infh3id');
            $em = $this->getDoctrine()->getManager();
            $detalles = $em->getRepository('SieAppWebBundle:InfraestructuraH3DelitosEdificioDetalle')->findBy(array(
                'infraestructuraH3RiesgosDelitos'=>$id
            ));

            foreach ($detalles as $d) {
                $data[] = array(
                    'gestion' => $d->getN22GestionTipo()->getGestion(),
                    'cantidad' => $d->getN22CantVeces(),
                    'horario' => ($d->getN22HorarioTipo())?$d->getN22HorarioTipo()->getId():'',
                    'horarioNombre' => ($d->getN22HorarioTipo())?$d->getN22HorarioTipo()->getDescripcion():'',
                    'ambiente' => ($d->getN22AmbientesTipo())?$d->getN22AmbientesTipo()->getId():'',
                    'ambienteNombre' => ($d->getN22AmbientesTipo())?$d->getN22AmbientesTipo()->getDescripcion():'',
                    'mobiliario' => ($d->getN22MobiliarioTipo())?$d->getN22MobiliarioTipo()->getId():'',
                    'mobiliarioNombre' => ($d->getN22MobiliarioTipo())?$d->getN22MobiliarioTipo()->getDescripcion():'',
                    'equipamiento' => ($d->getN22EquipamientoTipo())?$d->getN22EquipamientoTipo()->getId():'',
                    'equipamientoNombre' => ($d->getN22EquipamientoTipo())?$d->getN22EquipamientoTipo()->getDescripcion():'',
                    'acciones' => $d->getN22ObsAcciones()
                );
            }
        }

        return $this->get('funciones')->json($data);
    }
}
