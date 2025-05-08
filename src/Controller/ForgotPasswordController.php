<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotPasswordRequestFormType;
use App\Form\ResetPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\BrevoMailerService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function request(
        Request $request, 
        EntityManagerInterface $entityManager,
        BrevoMailerService $brevoMailerService
    ): Response {
        $form = $this->createForm(ForgotPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $email = $form->getData()['email'];
                $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if ($user) {
                    $otp = random_int(100000, 999999);

                    $brevoMailerService->sendOtpEmail($email, $otp);

                    $this->addFlash('success', 'Un email avec un code OTP a été envoyé.');
                    $request->getSession()->set('otp_code', $otp);
                    $request->getSession()->set('reset_email', $email);

                    return $this->redirectToRoute('app_verify_otp');
                } else {
                    $this->addFlash('error', 'Aucun utilisateur trouvé pour cet email.');
                }
            } else {
                $this->addFlash('error', 'Veuillez corriger les erreurs du formulaire.');
            }

            return $this->redirectToRoute('app_forgot_password');
        }

        return $this->render('forgot_password/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/verify-otp', name: 'app_verify_otp')]
    public function verifyOtp(Request $request, SessionInterface $session): Response
    {
        if ($request->isMethod('POST')) {
            $inputOtp = $request->request->get('otp');
            $sessionOtp = $session->get('otp_code');

            if ($inputOtp == $sessionOtp) {
                return $this->redirectToRoute('app_reset_password');
            } else {
                $this->addFlash('error', 'Le code OTP est incorrect.');
                return $this->redirectToRoute('app_verify_otp');
            }
        }

        return $this->render('forgot_password/verify_otp.html.twig');
    }

    #[Route('/reset-password', name: 'app_reset_password')]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $email = $session->get('reset_email');

        if (!$email) {
            return $this->redirectToRoute('app_forgot_password');
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->getData()['password'];
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setMotDePasse($hashedPassword);

            $entityManager->flush();

            $session->remove('reset_email');
            $session->remove('otp_code');

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('forgot_password/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
