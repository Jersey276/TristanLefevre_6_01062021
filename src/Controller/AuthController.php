<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\ForgotPasswordType;
use App\Form\RegisterFormType;
use App\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Controller about all function/route about authentification system
 * @author Tristan
 * @version 1
 */
class AuthController extends AbstractController
{

    /**
     * @var UserManager $manager
     */
    private UserManager $manager;

    public function __construct(UserManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Login a user (prebuild function)
     * @param AuthenticationUtils $authenticationUtils authentification utility class
     * @return Response Render / Json response
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * Display registration template / register a user
     * @param Request $request request data
     * @return Response Render / Json response
     * @Route("/register", name="register")
     */
    public function register(Request $request) : Response
    {
        $user = new User();

        $form = $this->createForm(RegisterFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->register($user);
            return $this->redirectToRoute('app_login');
        }
        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Display forgot password template / ask to send mail for reset password
     * @param Request $request request data
     * @return Response Render / Json response
     * @Route("/forgot_password", methods={"GET", "POST"}, name="forgot_password")
     */
    public function forgotPassword(Request $request) : Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = ($form->getData())['email'];
            $this->manager->forgotPassword($email);
            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/forgotPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Display change password template / change password
     * @param Request $request request data
     * @param String $token token
     * @return Response Render / Json response
     * @Route("/reset_password/{token}", name="change_password")
     */
    public function changePassword(Request $request, string $token) : Response
    {
        $user = new User();
        $form = $this->createForm(
            ChangePasswordType::class,
            $user,
            [
                    'action' => '/reset_password/'.$token,
                    'method' => 'POST'
                ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->changePassword($user, $token);
            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/changePassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Display registration template / register a user
     * @param String $token token
     * @return Response Render / Json response
     * @Route("/verify_email/{token}", name="check_email")
     */
    public function validEmail(string $token) : Response
    {
        $this->manager->validEmail($token);

        return $this->redirectToRoute('app_login');
    }

    /**
     * Logout User (prebuild function)
     * @return Response Render / Json response
     * @Route("/logout", name="app_logout")
     */
    public function logout() : Response
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
