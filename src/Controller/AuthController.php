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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{

    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
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
     * @Route("/register", name="register")
     */
    public function index(Request $request, MailerInterface $mailer) : Response
    {
        $user = new User();

        $form = $this->createForm(RegisterFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            (new UserManager(
                $this->getDoctrine()->getManager(),
                $mailer,
                $this->passwordEncoder
                )
            )->register($user);
            return $this->redirectToRoute('app_login');
        }
        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("password/forgot", methods={"GET", "POST"}, name="forgot_password")
     */
    public function forgotPassword(Request $request, MailerInterface $mailer) : Response
    {
        $user = new User();
        $form = $this->createForm(ForgotPasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            (new UserManager(
                $this->getDoctrine()->getManager(),
                $mailer       
                )
            )->forgotPassword($user);
        }
        return $this->render('security/forgotPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("password/change", name="change_password")
     */
    public function changePassword(Request $request) : Response
    {
        $user = new User();
        $token = $request->query->get('token');
        $form = $this->createForm(ChangePasswordType::class, 
                $user,
                [
                    'action' => '/password/change?token='.$token,
                    'method' => 'POST'
                ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            (new UserManager(
                $this->getDoctrine()->getManager(),
                null,
                $this->passwordEncoder
            )
            )->changePassword($user, $token);
            return $this->redirectToRoute('/login');
        }
        return $this->render('security/changePassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="check_email")
     */
    public function validEmail(Request $request) : Response
    {
        $mn = new UserManager(
            $this->getDoctrine()->getManager(),
            null,
            null
        );
        $mn->validEmail($request->query->get('token'));

        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout() : Response
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
