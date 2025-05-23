<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($hasher->hashPassword($user, $data['password']));
        $user->setRoles(['ROLE_USER']);
        $em->persist($user);
        $em->flush();
        $token = $jwtManager->create($user);

        return new JsonResponse(['message'=>'OK','token'=>$token,'id'=>$user->getId(),'email'=>$user->getEmail(),'roles'=>$user->getRoles()], 201);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): void
    {
        // Este método NUNCA se ejecuta:
        // el listener json_login de tu firewall
        // intercepta la llamada y devuelve el JWT.
        throw new \LogicException('Este endpoint es manejado por el firewall de JSON-login.');
    }

    #[Route('/api/user/profile', name: 'user_profile', methods: ['GET'])]
    public function userProfile(): JsonResponse
    {
        return new JsonResponse(['message' => 'Área de usuario']);
    }

    #[Route('/api/admin/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    public function adminDashboard(): JsonResponse
    {
        return new JsonResponse(['message' => 'Área de administrador']);
    }
}
