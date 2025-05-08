<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $user = $this->getUser();

            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                return $this->redirectToRoute('dashadmin');
            } elseif (in_array('ROLE_CLIENT', $user->getRoles(), true)) {
                return $this->redirectToRoute('app_participant');
            }
        }

        if ($request->isMethod('POST')) {
            $captchaResponse = $request->request->get('h-captcha-response');

            if (!$this->verifyHCaptcha($captchaResponse)) {
                return $this->render('security/login.html.twig', [
                    'last_username' => $request->request->get('email', ''),
                    'error' => new AuthenticationException('Veuillez valider le hCaptcha.'),
                ]);
            }
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Intercepté automatiquement par Symfony.');
    }

    private function verifyHCaptcha(?string $captchaResponse): bool
    {
        if (!$captchaResponse) return false;

        $verifyUrl = 'https://hcaptcha.com/siteverify';
        $secret = 'ES_0534e12180164bb8822b63c75ec2f39a';

        $data = http_build_query([
            'secret' => $secret,
            'response' => $captchaResponse
        ]);

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => $data,
            ],
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($verifyUrl, false, $context);
        $json = json_decode($result, true);

        return $json['success'] ?? false;
    }
}
