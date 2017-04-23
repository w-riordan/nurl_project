<?php
/**
 * Created by PhpStorm.
 * User: wayne
 * Date: 14/04/17
 * Time: 16:36
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request){
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();
        $banned = null;
        if (isset($lastUsername)){
            $user = $this->getDoctrine()->getManager()->getRepository("AppBundle:User")->findOneByUsername($lastUsername);
            $userban = $this->getDoctrine()->getManager()->getRepository("AppBundle:UserBan")->findOneByUser($user);
           if (isset($userban)) {
                $this->addFlash('notify',"Your account was frozen on ". $userban->getTimestamp()->format('d-m-Y')
                . " for the following reason :" . $userban->getReason() . "!");
           }
        }
        $templateName = 'security/login';
        $argsArray = [
            'last_username' => $lastUsername,
            'error'
            => $error,
        ];
        return $this->render($templateName . '.html.twig', $argsArray);
    }

}