<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ReportedNurl;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

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

        $reportedNurls = $em->getRepository('AppBundle:ReportedNurl')->findAll();

        return $this->render('reportednurl/index.html.twig', array(
            'reportedNurls' => $reportedNurls,
        ));
    }

    /**
     * Creates a new reportedNurl entity.
     *
     * @Route("/new", name="reportednurl_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $reportedNurl = new Reportednurl();
        $form = $this->createForm('AppBundle\Form\ReportedNurlType', $reportedNurl);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($reportedNurl);
            $em->flush();

            return $this->redirectToRoute('reportednurl_show', array('id' => $reportedNurl->getId()));
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
