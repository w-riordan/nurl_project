<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Nurl;
use AppBundle\Entity\ReportedNurl;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Reportednurl controller.
 *
 * @Route("reportednurl")
 */
class ReportedNurlController extends Controller
{
    /**
     * Lists all reportedNurl entities.
     *
     * @Route("/", name="reportednurl_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $reportedNurls = $em->getRepository('AppBundle:ReportedNurl')->findByAccepted(null);

        return $this->render('reportednurl/index.html.twig', array(
            'reportedNurls' => $reportedNurls,
        ));
    }

    /**
     * Creates a new reportedNurl entity.
     *
     * @Route("/report/{id}", name="reportednurl_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, Nurl $nurl)
    {
        $reportedNurl = new Reportednurl();
        $reportedNurl->setNurl($nurl);
        $form = $this->createForm('AppBundle\Form\ReportedNurlType', $reportedNurl);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $reportedNurl->setTimestamp(new \DateTime());
            $nurl = $reportedNurl->getNurl();
            $nurl->setFrozen(true);
            $em->persist($reportedNurl);
            $em->persist($nurl);
            $em->flush();
            $this->addFlash("notify","The following nurl was succefully reported : ".$nurl->getTitle());
            return $this->redirectToRoute('homepage');
        }

        return $this->render('reportednurl/new.html.twig', array(
            'reportedNurl' => $reportedNurl,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a reportedNurl entity.
     *
     * @Route("/{id}", name="reportednurl_show")
     * @Method("GET")
     */
    public function showAction(ReportedNurl $reportedNurl)
    {
        $deleteForm = $this->createDeleteForm($reportedNurl);

        return $this->render('reportednurl/show.html.twig', array(
            'reportedNurl' => $reportedNurl,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Accept the report
     * @param ReportedNurl $report_nurl
     * @Route("/accept/{id}", name="report_accept")
     */
    public function acceptAction(ReportedNurl $report_nurl){
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_MOD')) {
            $this->addFlash('notify',"You don't have the required permisions.");
            return $this->redirectToRoute('homepage');
        }

        $report_nurl->setAccepted(true);

        $em = $this->getDoctrine()->getManager();
        $em->persist($report_nurl);
        $em->flush();

        $this->addFlash('notify',"The report has been accepted");
        return $this->redirectToRoute('reportednurl_index');
    }

    /**
     * Reject the report
     * @param ReportedNurl $report_nurl
     * @Route("/reject/{id}", name="report_reject")
     */
    public function rejectAction(ReportedNurl $report_nurl){
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_MOD')) {
            $this->addFlash('notify',"You don't have the required permisions.");
            return $this->redirectToRoute('homepage');
        }

        $report_nurl->setAccepted(false);
        $nurl = $report_nurl->getNurl();
        $nurl->setFrozen(false);

        $em = $this->getDoctrine()->getManager();
        $em->persist($report_nurl);
        $em->persist($nurl);
        $em->flush();

        $this->addFlash('notify',"The nurl has been restored");
        return $this->redirectToRoute('nurl_show',array('id'=>$nurl->getId()));
    }

    /**
     * Displays a form to edit an existing reportedNurl entity.
     *
     * @Route("/{id}/edit", name="reportednurl_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ReportedNurl $reportedNurl)
    {
        $deleteForm = $this->createDeleteForm($reportedNurl);
        $editForm = $this->createForm('AppBundle\Form\ReportedNurlType', $reportedNurl);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('reportednurl_edit', array('id' => $reportedNurl->getId()));
        }

        return $this->render('reportednurl/edit.html.twig', array(
            'reportedNurl' => $reportedNurl,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a reportedNurl entity.
     *
     * @Route("/{id}", name="reportednurl_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ReportedNurl $reportedNurl)
    {
        $form = $this->createDeleteForm($reportedNurl);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($reportedNurl);
            $em->flush();
        }

        return $this->redirectToRoute('reportednurl_index');
    }

    /**
     * Creates a form to delete a reportedNurl entity.
     *
     * @param ReportedNurl $reportedNurl The reportedNurl entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ReportedNurl $reportedNurl)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('reportednurl_delete', array('id' => $reportedNurl->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
