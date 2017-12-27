<?php
namespace Sie\RieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Form\InstitucioneducativaType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\CertificadoRue;
use Sie\AppWebBundle\Entity\CertificadoRueInstitucioneducativa;

/**
 * Institucioneducativa controller.
 *
 */
class CertificadoInstitutoController extends Controller {

    /**
     * Listado de certificados
     *
     */
    public function indexAction(Request $request) {
    	$sesion = $request->getSession();
    	$id_usuario = $sesion->get('userId');
    	if (!isset($id_usuario)){
    		return $this->redirect($this->generateUrl('login'));
    	}
    	 
    	try{
    		$em = $this->getDoctrine()->getManager();
    		$this->session = new Session();
    
    		$query = $em->createQuery('SELECT fm
										 FROM SieAppWebBundle:CertificadoRue fm
									    WHERE fm.observacion LIKE :obs
									 ORDER BY fm.fechaCorte DESC')
									 ->setParameter('obs', 'RIE%');
    		$entities = $query->getResult();

    		return $this->render('SieRieBundle:CertificadoInstituto:index.html.twig', array('entities' => $entities));
    	
    	} catch (Exception $ex){
    
    	}
    }
    
    /*
     * Mostrando formulario de adición para certificación
     */    
    public function newAction(Request $request) {

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

    	$form = $this->createFormBuilder()
		->setAction($this->generateUrl('certificado_rie_create'))
		->add('userId', 'hidden', array('data' => $id_usuario))
    	->add('fechaCorte', 'text', array('label' => 'Fecha de corte', 'required' => true, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'Seleccione una fecha...')))
    	->add('nroCertificadoInicio', 'text', array('label' => 'Nro. certificado inicio', 'required' => true, 'attr' => array('class' => 'form-control jnumbers', 'autocomplete' => 'off','maxlength'=>'15')))
		->add('observacion', 'text', array('label' => 'Observaciones','required' => true,'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
    	->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
    	return $this->render('SieRieBundle:CertificadoInstituto:new.html.twig', array('form' => $form->getForm()->createView()));
	}
	
    /*
     * Guardando datos del nuevo formulario 
     */       
    public function createAction(Request $request) {
    	try {

    		$em = $this->getDoctrine()->getManager();
    		$form = $request->get('form');
			$em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_nivel_autorizado');")->execute();
    		$entity = new CertificadoRue();
    		$entity->setFechaCorte(new \DateTime($form['fechaCorte']));
    		$entity->setFechaRegistro(new \DateTime('now'));
    		$entity->setObservacion('RIE: '.$form['observacion']);
			$entity->setNroCertificadoInicio($form['nroCertificadoInicio']);
			$entity->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->findOneById($form['userId']));
    		$em->persist($entity);
    		$em->flush();
    		
    		return $this->redirect($this->generateUrl('certificado_rie'));
    	} catch (Exception $ex) {
     		$this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar los datos de certificacion');
     		return $this->redirect($this->generateUrl('certificado_rie_new'));
    	}
    }

    /*
     * Editando los datos de certificación
     */ 
    public function editAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:CertificadoRue')->findOneById($request->get('idCertificado'));
        	if (!$entity){
        		$idCertificado = $request->getSession()->get('idCertificado');
        		$entity = $em->getRepository('SieAppWebBundle:CertificadoRue')->findOneById($idCertificado);
        	}
        	else {
        		$request->getSession()->set('idCertificado', $entity->getId());
        	}

        	if (!$entity){
        		throw $this->createNotFoundException('No se puede encontrar proceso de certificados.');
        	}
        	 
        	$form = $this->createFormBuilder()
        	->setAction($this->generateUrl('certificado_rie_update'))
        	->add('idCertificado', 'hidden', array('data' => $entity->getId()))
        	->add('fechaCorte', 'text', array('label' => 'Fecha de corte','data'=>$entity->getFechaCorte()->format('d-m-Y'), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        	->add('nroCertificadoInicio', 'text', array('label' => 'Nro. certificado inicio', 'data' => $entity->getNroCertificadoInicio(), 'disabled' => true, 'attr' => array('class' => 'form-control jnumbers')))
        	->add('observacion', 'text', array('label' => 'Observaciones', 'data' => $entity->getObservacion(),'disabled' => true,'attr' => array('class' => 'form-control')))
			->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
        	return $this->render('SieRieBundle:CertificadoInstituto:edit.html.twig', array('entity' => $entity,'form' => $form->getForm()->createView()));
    }


    public function updateAction(Request $request) {
    	$this->session = new Session();
    	$form = $request->get('form');
    	$em = $this->getDoctrine()->getManager();
    	$entity = $em->getRepository('SieAppWebBundle:CertificadoRue')->findOneById($form['idCertificado']);
    	$nro = $entity->getNroCertificadoInicio(); 
    	foreach ($entity->getCertificados() as $certificado) {
    		$certificado->setNroCertificado($nro);
    		$em->persist($certificado);
    		$nro = $nro + 1;
    	}
    	$em->flush();
    	$this->get('session')->getFlashBag()->add('mensaje', 'Los datos se guardaron correctamente');
    	return $this->redirect($this->generateUrl('certificado_rie_edit'));
    }
    

    public function deleteAction(Request $request) {
    	try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $certificado = $em->getRepository('SieAppWebBundle:CertificadoRueInstitucioneducativa')->find($request->get('idCertificadoRue'));
            $em->remove($certificado);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('deleteOk', 'Se elimino correctamente');
            return $this->redirect($this->generateUrl('certificado_rue_edit'));
        }catch(Exception $ex){
        	$em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('deleteError', $ex->getMessage());
            return $this->redirect($this->generateUrl('certificado_rue_edit'));
        }
    }

    public function certificadolistaAction(Request $request){
        $listar = $this->obtieneListaCertificados($request->get('idCertificado'));
		/*		
		echo '<pre>';
			print_r($listar);
		echo '</pre>';
		*/
		//$custom_layout = array(111, 200);
		$custom_layout = array(150, 200);
		$pdf = $this->container->get("white_october.tcpdf")->create('PORTRAIT', PDF_UNIT, $custom_layout, true,'UTF-8', false);
		$pdf->setAuthor('Ministerio de Educación');
		$pdf->SetTitle('Certificado');
		$pdf->SetSubject('');
		$pdf->SetPrintHeader(false);
		$pdf->SetFooterMargin(0);
		$pdf->SetFont('helvetica','B',12);
		$resolution= array(218, 280);
		$pdf->AddPage('L', $resolution);

		$gestion = date('Y');
		$mes = strtoupper($this->nombremes(date('m')));
		$dia = date('d');
		$fecha = 'Impreso: '.$dia.' de '.$mes.' de '.$gestion;        

		foreach($listar as $lista){
			// QR
			$style = array(
				'border' => 0,
				'fgcolor' => array(0,0,0),
				'bgcolor' => false, //array(255,255,255)
			);
			// write RAW 2D Barcode
			// QRCODE,L : QR-CODE Low error correction
			$pdf->write2DBarcode($lista['rie'], 'QRCODE,L', 235, 20, 30, 30, $style, 'N');

			$pdf->Ln(25);
			if(strlen($lista['denominacion']) > 40){
				$pdf->SetFont('helvetica','B', 18);
			}else{
				$pdf->SetFont('helvetica','B', 24);
			}			
			$pdf->Cell(0, 0, $lista['denominacion'], 0, 0, 'C', 0, '', 0);
			$pdf->Ln(15);
			$pdf->SetFont('helvetica','B', 12);
			$pdf->Cell(0, 0, $lista['sede'], 0, 0, 'C', 0, '', 0);

			$pdf->Ln(15);
			$pdf->Cell(60, 0, '', 0, 0, '', 0, '', 0);
			$pdf->Cell(50, 0, $lista['resolucion'], 0, 0, '', 0, '', 0);
			$pdf->Cell(60, 0, $lista['fecharesolucion'], 0, 0, 'C', 0, '', 0);
			$pdf->Cell(80, 0, $lista['vigencia'], 0, 0, 'C', 0, '', 0);

			$pdf->Ln(12);
			$pdf->Cell(60, 0, '', 0, 0, '', 0, '', 0);
			$pdf->Cell(80, 0, $lista['caracterjuridico'], 0, 0, '', 0, '', 0);
			$pdf->Cell(100, 0, $lista['departamento'], 0, 0, '', 0, '', 0);

			$pdf->Ln(12);
			$pdf->Cell(60, 0, '', 0, 0, '', 0, '', 0);
			$pdf->Cell(180, 0, $lista['direccion'], 0, 0, 'L', 0, '', 0);
			$pdf->Ln(8);
			$pdf->Cell(60, 0, '', 0, 0, '', 0, '', 0);
			$pdf->Cell(180, 0, $lista['zona'], 0, 0, 'L', 0, '', 0);

			$pdf->Ln(12);
			$pdf->Cell(60, 0, '', 0, 0, '', 0, '', 0);
			$pdf->Cell(180, 0, $lista['niveles'], 0, 0, '', 0, '', 0);
			
			$pdf->Ln(32);
			$pdf->SetFont('helvetica','I', 9);
			$pdf->Cell(200, 0, '', 0, 0, '', 0, '', 0);
			$pdf->Cell(20, 0, 'Código RIE: ', 0, 0, '', 0, '', 0);
			$pdf->SetFont('helvetica','B', 9);
			$pdf->Cell(30, 0, $lista['rie'], 0, 0, '', 0, '', 0);

			$pdf->Ln(5);
			$pdf->SetFont('helvetica','I', 9);
			$pdf->Cell(200, 0, '', 0, 0, '', 0, '', 0);
			$pdf->Cell(20, 0, 'Código LE: ', 0, 0, '', 0, '', 0);
			$pdf->SetFont('helvetica','B', 9);
			$pdf->Cell(30, 0, $lista['le'], 0, 0, '', 0, '', 0);

			$pdf->Ln(8);
			$pdf->SetFont('helvetica','', 7);
			$pdf->Cell(200, 0, '', 0, 0, '', 0, '', 0);
			$pdf->Cell(50, 0, $fecha, 0, 0, '', 0, '', 0);

			$pdf->AddPage();
			$ultimaPagina = $pdf->getPage();
		}

		$pdf->deletePage($ultimaPagina);
		$pdf->Output('certificados-'.date('d-m-Y H:i').'.pdf', 'D');
	}

    /***
     * Obtiene lista de certificados 
     */
    function obtieneListaCertificados($idCertificados){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT cei
                                     FROM SieAppWebBundle:CertificadoRueInstitucioneducativa cei
                                     JOIN cei.certificadoRue ce 
                                    WHERE ce.id = :idCe')
                        ->setParameter('idCe', $idCertificados);        
        $certificados = $query->getResult();      
        $lista = array();
        $i = 0;
        foreach($certificados as $certificado){
            $dato = explode("|", $certificado->getInstitucioneducativades());
            $resolucion = explode("|", $certificado->getNroResolucion());
            $departamento = explode("-", $certificado->getDepartamento());
            $lista[$i]['rie'] = $certificado->getInstitucioneducativa()->getId();
            $lista[$i]['le'] = $certificado->getLeJuridicciongeografica()->getId();
            $lista[$i]['denominacion'] = $dato[0];
            $lista[$i]['sede'] = strtoupper($dato[1]);
            $lista[$i]['resolucion'] = $resolucion[0];
            $lista[$i]['fecharesolucion'] = $resolucion[1];
            $lista[$i]['vigencia'] = $resolucion[2];
            $lista[$i]['caracterjuridico'] = strtoupper($certificado->getDependencia());
            $lista[$i]['departamento'] = strtoupper($departamento[1]);
			$lista[$i]['direccion'] = $dato[4];
			$lista[$i]['zona'] = $dato[3];
            $lista[$i]['niveles'] = $certificado->getNiveles();
            $i++;
        }
        return $lista;
    }    

    /*** 
     * Funcion que genera el nombre de mes en literal
     */  
    function nombremes($mes){
        setlocale(LC_TIME, 'spanish');  
        $nombre=strftime("%B",mktime(0, 0, 0, $mes, 1, 2000)); 
        return $nombre;
    }


}
