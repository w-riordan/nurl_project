<?php

namespace AppBundle\Controller;

use AppBundle\Entity\PendingNurl;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Pendingnurl controller.
 *
 * @Route("pendingnurl")
 */
class PendingNurlController extends Controller
{
    /**
     * Lists all pendingNurl entities.
     *
     * @Route("/", name="pendingnurl_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_MOD')) {
            $this->addFlash('notify',"You don't have the required permisions.");
            return $this->redirectToRoute('homepage');
        }
        $em = $this->getDoctrine()->getManager();

        $pendingNurls = $em->getRepository('AppBundle:PendingNurl')->findByAccepted(null);

        return $this->render('pendingnurl/index.html.twig', array(
            'pendingNurls' => $pendingNurls,
        ));
    }

    /**
     * Creates a new pendingNurl entity.
     *
     * @Route("/new", name="pendingnurl_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $pendingNurl = new Pendingnurl();
        $form = $this->createForm('AppBundle\Form\PendingNurlType', $pendingNurl);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($pendingNurl);
            $em->flush();

            return $this->redirectToRoute('pendingnurl_show', array('id' => $pendingNurl->getId()));
        }

        return $this->render('pendingnurl/new.html.twig', array(
            'pendingNurl' => $pendingNurl,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a pendingNurl entity.
     *
     * @Route("/{id}", name="pendingnurl_show")
     * @Method("GET")
     */
    public function showAction(PendingNurl $pendingNurl)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_MOD')) {
            $this->addFlash('notify',"You don't have the required permisions.");
            return $this->redirectToRoute('homepage');
        }
        $deleteForm = $this->createDeleteForm($pendingNurl);

        return $this->render('pendingnurl/show.html.twig', array(
            'pendingNurl' => $pendingNurl,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Accept the pending nurl
     * @param PendingNurl $pend_nurl
     * @Route("/accept/{id}", name="pending_accept")
     */
    public function acceptAction(PendingNurl $pend_nurl){
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_MOD')) {
            $this->addFlash('notify',"You don't have the required permisions.");
            return $this->redirectToRoute('homepage');
        }

        $pend_nurl->setAccepted(true);
        $pend_nurl->setTimestamp(new \DateTime());
        $nurl=$pend_nurl->getNurl();
        $nurl->setFrozen(false);

        $em = $this->getDoctrine()->getManager();
        $em->persist($pend_nurl);
        $em->persist($nurl);
        $em->flush();

        $this->addFlash('notify',"The nurl has been accepted");
        return $this->redirectToRoute('nurl_show',array('id'=>$nurl->getId()));
    }

    /**
     * Reject the pending nurl
     * @param PendingNurl $pend_nurl
     * @Route("/reject/{id}", name="pending_reject")
     */
    public function rejectAction(PendingNurl $pend_nurl){
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_MOD')) {
            $this->addFlash('notify',"You don't have the required permisions.");
            return $this->redirectToRoute('homepage');
        }

        $pend_nurl->setAccepted(false);
        $pend_nurl->setTimestamp(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($pend_nurl);
        $em->flush();

        $this->addFlash('notify',"The nurl has been rejected");
        return $this->redirectToRoute('pendingnurl_index');
    }

    /**
     * Displays a form to edit an existing pendingNurl entity.
     *
     * @Route("/{id}/edit", name="pendingnurl_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, PendingNurl $pendingNurl)
    {
        $deleteForm = $this->createDeleteForm($pendingNurl);
        $editForm = $this->createForm('AppBundle\Form\PendingNurlType', $pendingNurl);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('pendingnurl_edit', array('id' => $pendingNurl->getId()));
        }

        return $this->render('pendingnurl/edit.html.twig', array(
            'pendingNurl' => $pendingNurl,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a pendingNurl entity.
     *
     * @Route("/{id}", name="pendingnurl_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, PendingNurl $pendingNurl)
    {
        $form = $this->createDeleteForm($pendingNurl);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($pendingNurl);
            $em->flush();
        }

        return $this->redirectToRoute('pendingnurl_index');
    }

    /**
     * Creates a form to delete a pendingNurl entity.
     *
     * @param PendingNurl $pendingNurl The pendingNurl entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(PendingNurl $pendingNurl)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('pendingnurl_delete', array('id' => $pendingNurl->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
