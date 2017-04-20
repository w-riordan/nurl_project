<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ProposedTags;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Proposedtag controller.
 *
 * @Route("proposedtags")
 */
class ProposedTagsController extends Controller
{
    /**
     * Lists all proposedTag entities.
     *
     * @Route("/", name="proposedtags_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $proposedTags = $em->getRepository('AppBundle:ProposedTags')->findAll();

        return $this->render('proposedtags/index.html.twig', array(
            'proposedTags' => $proposedTags,
        ));
    }

    /**
     * Increases the rating on a proposed Tag
     * @Route("/vote_up/{id}", name="vote_up")
     */
    public function voteUpAction(ProposedTags $tag){
        $rating = $tag->getRating();
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            $tag->setRating($rating+5);
        }else{
            $tag->setRating($rating+1);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($tag);
        $em->flush();

        return $this->redirectToRoute('proposedtags_show',array('id' => $tag->getId()));
    }

    /**
     * Increases the rating on a proposed Tag
     * @Route("/vote_down/{id}",name="vote_down")
     */
    public function voteDownAction(ProposedTags $tag){
        $rating = $tag->getRating();
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            $tag->setRating($rating-5);
        }else{
            $tag->setRating($rating-1);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($tag);
        $em->flush();

        return $this->redirectToRoute('proposedtags_show',array('id' => $tag->getId()));
    }

    /**
     * Creates a new proposedTag entity.
     *
     * @Route("/new", name="proposedtags_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $proposedTag = new Proposedtag();
        $form = $this->createForm('AppBundle\Form\ProposedTagsType', $proposedTag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($proposedTag);
            $em->flush();

            return $this->redirectToRoute('proposedtags_show', array('id' => $proposedTag->getId()));
        }

        return $this->render('proposedtags/new.html.twig', array(
            'proposedTag' => $proposedTag,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a proposedTag entity.
     *
     * @Route("/{id}", name="proposedtags_show")
     * @Method("GET")
     */
    public function showAction(ProposedTags $proposedTag)
    {
        $deleteForm = $this->createDeleteForm($proposedTag);

        return $this->render('proposedtags/show.html.twig', array(
            'proposedTag' => $proposedTag,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing proposedTag entity.
     *
     * @Route("/{id}/edit", name="proposedtags_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ProposedTags $proposedTag)
    {
        $deleteForm = $this->createDeleteForm($proposedTag);
        $editForm = $this->createForm('AppBundle\Form\ProposedTagsType', $proposedTag);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('proposedtags_edit', array('id' => $proposedTag->getId()));
        }

        return $this->render('proposedtags/edit.html.twig', array(
            'proposedTag' => $proposedTag,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a proposedTag entity.
     *
     * @Route("/{id}", name="proposedtags_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ProposedTags $proposedTag)
    {
        $form = $this->createDeleteForm($proposedTag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($proposedTag);
            $em->flush();
        }

        return $this->redirectToRoute('proposedtags_index');
    }

    /**
     * Creates a form to delete a proposedTag entity.
     *
     * @param ProposedTags $proposedTag The proposedTag entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ProposedTags $proposedTag)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('proposedtags_delete', array('id' => $proposedTag->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
