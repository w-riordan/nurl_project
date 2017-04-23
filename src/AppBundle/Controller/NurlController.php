<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Nurl;
use AppBundle\Entity\PendingNurl;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Nurl controller.
 *
 * @Route("nurl")
 */
class NurlController extends Controller
{
    /**
     * Lists all nurl entities.
     *
     * @Route("/", name="nurl_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $nurls = $em->getRepository('AppBundle:Nurl')->findBy(array('frozen'=>false,'public'=>true),array('id'=>'desc'));

        return $this->render('nurl/index.html.twig', array(
            'nurls' => $nurls,
        ));
    }

    /**
     * Creates a new nurl entity.
     *
     * @Route("/search", name="nurl_search")
     * @Method({"GET", "POST"})
     */
    public function searchAction(Request $request)
    {
        $submitted = false;
        $form = $this->createFormBuilder()->getForm();
        $form->add('search_words',TextType::class,array(
            'label'=>'search',
            'required'=>false
        ))->add('tags',EntityType::class,array(
            'class'=>'AppBundle:Tag',
            'label'=>'Tag',
            'multiple'=> true,
        ))->add('submit', SubmitType::class,array(
            'label'=>'Search'
        ));
        $nurls=null;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $submitted=true;
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery(
                'SELECT n FROM AppBundle:Nurl n WHERE n.tag  in(:tags)  ORDER BY n.id DESC'
            )->setParameter('tags', $data['tags']);//->setParameter('search', $data['search_words']);
            if ($data['search_words'] != ""){
                $query = $em->createQuery(
                    'SELECT n FROM AppBundle:Nurl n WHERE n.tag  in(:tags) AND (n.title LIKE :search OR n.notes LIKE :search)  ORDER BY n.id DESC'
                )->setParameter('tags', $data['tags'])->setParameter('search', '%'.$data['search_words'].'%');
            }
            $nurls = $query->getResult();
        }
    //
        return $this->render('nurl/search.html.twig', array(
            'nurls' => $nurls,
            'search_form' => $form->createView(),
            'submitted' => $submitted
        ));
    }


    /**
     * Creates a new nurl entity.
     *
     * @Route("/new", name="nurl_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $nurl = new Nurl();
        $form = $this->createForm('AppBundle\Form\NurlType', $nurl,array('entity_manager'=>$this->get('doctrine.orm.entity_manager'), 'user' => $this->getUser()));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            //Validate Url
            $url=$nurl->getURL();
            if (strpos($url,'http://')!=0 && strpos($url,'https://')!=0){
                $nurl->setURL('http://' . $url);
            }

            if($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
                $nurl->setFrozen(false)->setAuthor($this->getUser());

                $em->persist($nurl);
                $em->flush();

                return $this->redirectToRoute('nurl_show', array('id' => $nurl->getId()));
            }else{
                $anonUser = $em->getRepository("AppBundle:User")->findOneByUsername("Anonymous");
                $nurl->setPublic(true)->setFrozen(true)->setAuthor($anonUser);
                $pendingNurl = new PendingNurl();
                $pendingNurl->setNurl($nurl)->setAccepted(null)->setReason(null)->setTimestamp(null);

                $em->persist($nurl);
                $em->persist($pendingNurl);
                $em->flush();

                return $this->redirectToRoute("homepage");
            }


        }

        return $this->render('nurl/new.html.twig', array(
            'nurl' => $nurl,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a nurl entity.
     *
     * @Route("/{id}", name="nurl_show")
     * @Method("GET")
     */
    public function showAction(Nurl $nurl)
    {
        $deleteForm = $this->createDeleteForm($nurl);

        return $this->render('nurl/show.html.twig', array(
            'nurl' => $nurl,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing nurl entity.
     *
     * @Route("/{id}/edit", name="nurl_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Nurl $nurl)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('notify',"You don't have the required permisions.");
            return $this->redirectToRoute('homepage');
        }
        if (!($this->getUser() == $nurl->getAuthor()) &&
            !($this->get('security.authorization_checker')->isGranted('ROLE_MOD'))) {
            $this->addFlash('notify',"You don't have the required permisions.");
            return $this->redirectToRoute('homepage');
        }

        $deleteForm = $this->createDeleteForm($nurl);
        $editForm = $this->createForm('AppBundle\Form\NurlType', $nurl,array(
            'entity_manager'=> $this->get('doctrine.orm.entity_manager'),
            'user'=> $this->getUser(),
            'show_public'=> false
        ));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            //Validate Url
            $url=$nurl->getURL();
            if (strpos($url,'http://')!=0 && strpos($url,'https://')!=0){
                $nurl->setURL('http://' . $url);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($nurl);
            $em->flush();
            $this->addFlash('notify',"Updated Succesfully");
            return $this->redirectToRoute('nurl_show', array('id' => $nurl->getId()));
        }

        return $this->render('nurl/edit.html.twig', array(
            'nurl' => $nurl,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Makes the nurl public
     * @param Nurl $nurl
     * @Route("/makepublic/{id}", name="nurl_public")
     */
    public function makePublicAction(Nurl $nurl){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('notify',"You don't have the required permisions.");
            return $this->redirectToRoute('homepage');
        }
        if (!($this->getUser() == $nurl->getAuthor()) &&
            !($this->get('security.authorization_checker')->isGranted('ROLE_MOD'))) {
            $this->addFlash('notify',"You don't have the required permisions.");
            return $this->redirectToRoute('homepage');
        }
        $em = $this->getDoctrine()->getManager();
        $nurl->setPublic(true);
        $em->persist($nurl);
        $em->flush();
        return $this->redirectToRoute('nurl_show', array('id'=>$nurl->getId()));

    }

    /**
     * Deletes a nurl entity.
     *
     * @Route("/{id}", name="nurl_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Nurl $nurl)
    {
        $form = $this->createDeleteForm($nurl);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($nurl);
            $em->flush();
            $this->addFlash('notify',"The nurl has been deleted");
        }

        return $this->redirectToRoute('nurl_index');
    }

    /**
     * Creates a form to delete a nurl entity.
     *
     * @param Nurl $nurl The nurl entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Nurl $nurl)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('nurl_delete', array('id' => $nurl->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
