<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Collection controller.
 *
 * @Route("collection")
 */
class CollectionController extends Controller
{
    /**
     * Lists all collection entities.
     *
     * @Route("/", name="collection_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $collections = $em->getRepository('AppBundle:Collection')->findAll();

        return $this->render('collection/index.html.twig', array(
            'collections' => $collections,
        ));
    }

    /**
     * Creates a new collection entity.
     *
     * @Route("/new", name="collection_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('notify',"Only registered users can create collections");
            return $this->redirectToRoute('homepage');
        }
        $collection = new Collection();
        $collection->setOwner($this->getUser());
        $form = $this->createForm('AppBundle\Form\CollectionType', $collection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($collection);
            $em->flush();

            return $this->redirectToRoute('collection_show', array('id' => $collection->getId()));
        }

        return $this->render('collection/new.html.twig', array(
            'collection' => $collection,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a collection entity.
     *
     * @Route("/{id}", name="collection_show")
     * @Method("GET")
     */
    public function showAction(Collection $collection)
    {
        $deleteForm = $this->createDeleteForm($collection);

        return $this->render('collection/show.html.twig', array(
            'collection' => $collection,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing collection entity.
     *
     * @Route("/{id}/edit", name="collection_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Collection $collection)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('notify',"You don't have the required permisions.");
            return $this->redirectToRoute('homepage');
        }
        if (!($this->getUser() == $collection->getOwner()) &&
            !($this->get('security.authorization_checker')->isGranted('ROLE_MOD'))){
            $this->addFlash('notify',"You don't have the required permisions.");
            return $this->redirectToRoute('homepage');
        }
        $deleteForm = $this->createDeleteForm($collection);
        $editForm = $this->createForm('AppBundle\Form\CollectionType', $collection);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('collection_show', array('id' => $collection->getId()));
        }

        return $this->render('collection/edit.html.twig', array(
            'collection' => $collection,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a collection entity.
     *
     * @Route("/{id}", name="collection_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Collection $collection)
    {
        $form = $this->createDeleteForm($collection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
                foreach ($collection->getNurls() as $nurl) {
                    $em->remove($nurl);
                }
            $em->remove($collection);
            $em->flush();
            $this->addFlash('notify', 'The collection has been deleted');
        }

        return $this->redirectToRoute('user_show', array('id'=>$collection->getOwner()->getId()));
    }

    /**
     * Creates a form to delete a collection entity.
     *
     * @param Collection $collection The collection entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Collection $collection)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('collection_delete', array('id' => $collection->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
