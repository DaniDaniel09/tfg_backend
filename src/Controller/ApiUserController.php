<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/usuario', name: 'api_user_')]
class ApiUserController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(UserRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $repo->findAll();
        $data = array_map(function (User $usuario) {
            return [
                'id' => $usuario->getId(),
                'email' => $usuario->getEmail(),
                'roles' => $usuario->getRoles()
            ];
        }, $users);

        return $this->json(['users' => $data]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(User $usuario, EntityManagerInterface $em): JsonResponse
    {
        $userLogged = $this->getUser();

        if (!$userLogged instanceof User) {
            return $this->json(['error' => 'No autenticado'], 401);
        }

        // Solo puede eliminarse a sí mismo, o un admin a cualquier otro
        if ($userLogged->getId() !== $usuario->getId() && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Acceso denegado'], 403);
        }

        $em->remove($usuario);
        $em->flush();

        return $this->json(['message' => 'Usuario eliminado']);
    }

    #[Route('/{id}/password', name: 'change_password', methods: ['PUT'])]
    public function changePassword(
        int $id,
        Request $request,
        UserRepository $repo,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $userLogged = $this->getUser();

        if (!$userLogged instanceof User) {
            return $this->json(['error' => 'No autenticado'], 401);
        }

        $targetUser = $repo->find($id);
        if (!$targetUser) {
            return $this->json(['error' => 'Usuario no encontrado'], 404);
        }

        // Solo puede cambiar su propia contraseña, o un admin
        if ($userLogged->getId() !== $targetUser->getId() && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Acceso denegado'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['password']) || strlen($data['password']) < 6) {
            return $this->json(['error' => 'Contraseña inválida (mínimo 6 caracteres)'], 400);
        }

        $hashedPassword = $hasher->hashPassword($targetUser, $data['password']);
        $targetUser->setPassword($hashedPassword);
        $em->flush();

        return $this->json(['message' => 'Contraseña actualizada correctamente']);
    }
}
