<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\UserBan;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * User controller.
 *
 * @Route("user")
 */
class UserController extends Controller
{
    /**
     * Lists all user entities.
     *
     * @Route("/", name="user_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('AppBundle:User')->findAll();

        return $this->render('user/index.html.twig', array(
            'users' => $users,
        ));
    }

    /**
     * Creates a new user entity.
     *
     * @Route("/new", name="user_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {

        if($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            return $this->redirectToRoute('homepage');
        }
        $user = new User();
        $form = $this->createForm('AppBundle\Form\UserType', $user);
        $form->handleRequest($request);
        $user->setProfilepublic(true);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $userType = $em->getRepository("AppBundle:UserType")->findOneByType("Standard");
            $user->setJoindate(new \DateTime())->setFrozen(false)->setUsertype($userType);
            $plainPass = $user->getPassword();
            $pass =  $this->get('security.password_encoder')->encodePassword($user, $plainPass);
            $user->setPassword($pass);

            $profilepic = $user->getProfilepic();
            if (isset($profilepic)){
                $filename = md5(uniqid()).'.'.$profilepic->guessExtension();

                $profilepic->move($this->getParameter('profile_pics_dir'), $filename);
                $user->setProfilepic($filename);
            }


            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user_show', array('id' => $user->getId()));
        }

        return $this->render('user/new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a user entity.
     *
     * @Route("/{id}", name="user_show")
     * @Method("GET")
     */
    public function showAction(User $user)
    {
        $deleteForm = $this->createDeleteForm($user);

        return $this->render('user/show.html.twig', array(
            'user' => $user,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Changes the users profile to Public/Private
     * @param User $user
     *
     * @Route("/changePublic/{id}", name="user_change_public")
     */
    public function changePublicAction(User $user){
        $has_Permision=false;
        if($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            if($this->getUser() == $user || $this->get('security.authorization_checker')->isGranted('ROLE_MODERATOR')){
                $has_Permision = true;
            }
        }

        //Redirect user if not have permision
        if (!$has_Permision){
            $this->addFlash('notify',"You don't have the neccesary permisions to make changes to this account");
            return $this->redirectToRoute('homepage');
        }
        $em = $this->getDoctrine()->getManager();
        $public = $user->getProfilepublic();
        $user->setProfilepublic(!$public);
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('user_show', array('id'=>$user->getId()));
    }
    /**
     * Displays a form to edit an existing user entity.
     *
     * @Route("/{id}/edit/{type}", name="user_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, User $user, $type)
    {
        $has_Permision = false;
        //Check if user has permision to edit
        if($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            if($this->getUser() == $user || $this->get('security.authorization_checker')->isGranted('ROLE_MODERATOR')){
                $has_Permision = true;
            }
        }

        //Redirect user if not have permision
        if (!$has_Permision){
            $this->addFlash('notify',"You don't have the neccesary permisions to make changes to this account");
            return $this->redirectToRoute('homepage');
        }

        $editForm = $this->createFormBuilder()->getForm();
        $em = $this->getDoctrine()->getManager();
        //if editing password
        if ($type == 'password'){
            $editForm->add('password',PasswordType::class,array('label'=>'Current Password'))
                ->add('newpass1',PasswordType::class,array('label'=>'New Password'))
                ->add('newpass2',PasswordType::class,array('label'=>'Confirm New Password'));
            $editForm->handleRequest($request);

            if ($editForm->isSubmitted() && $editForm->isValid()) {
                $data = $editForm->getData();

                //Check password is valid
                if($this->get('security.password_encoder')->isPasswordValid($user, $data['password'])){
                    if($data['newpass1'] == $data['newpass2']){
                        $newpass = $this->get('security.password_encoder')->encodePassword($user, $data['newpass1']);
                        $user->setPassword($newpass);
                        $em->persist($user);
                        $em->flush();
                        $this->addFlash('notify',"Password changed succesfully");
                    }else{
                        $this->addFlash('notify', "Passwords do not match.");
                    }
                }else{
                    $this->addFlash('notify', "The password is incorrect.");
                }
            }
        }else if($type=='about'){
            $editForm->add('about',TextareaType::class, array('label' => 'About Me'));
            $editForm->handleRequest($request);

            if ($editForm->isSubmitted() && $editForm->isValid()) {
                $data = $editForm->getData();
                $user->setAbout($data['about']);
                $em->persist($user);
                $em->flush();
                $this->addFlash('notify',"Updated Profile.");
            }

        }else if($type=='pic'){
            $editForm->add('pic',FileType::class,array('label'=>'Profile Picture'));
            $editForm->handleRequest($request);

            if ($editForm->isSubmitted() && $editForm->isValid()) {
                $data = $editForm->getData();
                $profilepic = $data['pic'];

                $filename = md5(uniqid()).'.'.$profilepic->guessExtension();

                $profilepic->move($this->getParameter('profile_pics_dir'), $filename);
                $user->setProfilepic($filename);

                $em->persist($user);
                $em->flush();

                $this->addFlash('notify',"Updated Profile Picture.");
            }
        }
        else{
            $this->addFlash('notify','An Error occured while trying to make an edit.');
        }

        return $this->render('user/edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Freeze or unfreeze a user
     * @param User $user
     * @Route("freeze/{id}",name="user_freeze")
     * @Method({"GET","POST"})
     */
    public function freezeAction(Request $request,User $user){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_MOD')){
           $this->addFlash('notufy', "You don't have the required permissions to complete this action");
           return $this->redirectToRoute('homepage');
        }
        $em = $this->getDoctrine()->getManager();
        if ($user->getFrozen()){
            $this->addFlash('notify',"The account has been unfrozen.");
            $user->setFrozen(false);
            $ban = $em->getRepository("AppBundle:UserBan")->findOneByUser($user);
            $em->remove($ban);
        }else{
            $banForm = $this->createFormBuilder()->getForm();

            $banForm->add('reason',TextareaType::class,array('label' => 'Reason For Freeze','required'=>true));

            $banForm->handleRequest($request);
            if($banForm->isSubmitted() && $banForm->isValid()){
                $this->addFlash('notify',"The account has been frozen.");
                $user->setFrozen(true);
                $ban = new UserBan();
                $data=$banForm->getData();
                $ban->setUser($user)->setTimestamp(new \DateTime())->setReason($data['reason']);
                $em->persist($ban);
            }else{
                return $this->render('user/ban.html.twig', array(
                    'user' => $user,
                    'ban_form' => $banForm->createView(),
                ));
            }


        }

        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('user_show', array('id'=>$user->getId()));
    }

    /**
     * Deletes a user entity.
     *
     * @Route("/{id}", name="user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, User $user)
    {
        $session = new Session();
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $collections = $user->getCollections();
            $nurls = $user->getNurls();
            foreach ($collections as $collection){
                $em->remove($collection);
            }
            foreach ($nurls as $nurl){
                $em->remove($nurl);
            }
            $em->remove($user);
            $em->flush();
            $this->get('security.token_storage')->setToken(null);
            $session->invalidate();
        }

        $this->addFlash('notify',"The user account has been deleted");
        return $this->redirectToRoute('logout');
    }

    /**
     * Creates a form to delete a user entity.
     *
     * @param User $user The user entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
