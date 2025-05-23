<?php

namespace App\Controller;

use App\Entity\ZPower;
use App\Repository\UserRepository;
use App\Repository\ZPowerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/zpower', name: 'api_zpower_')]
final class ApiZPowerController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(ZPowerRepository $repo): JsonResponse
    {
        $zpowers = $repo->findAll();
        $data = array_map(fn(ZPower $z) => $z->toArray(), $zpowers);

        return $this->json($data);
    }

    #[Route('/{usuarioId}', name: 'get_by_user', methods: ['GET'])]
    public function getByUser(int $usuarioId, ZPowerRepository $zRepo): JsonResponse
    {
        $zpower = $zRepo->findOneBy(['usuario' => $usuarioId]);

        if (!$zpower) {
            return $this->json(['error' => 'ZPower not found for this user'], 404);
        }

        return $this->json($zpower->toArray());
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['usuario_id'])) {
            return $this->json(['error' => 'usuario_id is required'], 400);
        }

        $user = $userRepo->find($data['usuario_id']);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $zpower = new ZPower();
        $zpower->fromJson($data);
        $zpower->setUsuario($user);

        $em->persist($zpower);
        $em->flush();

        return $this->json($zpower->toArray(), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        ZPowerRepository $repo,
        UserRepository $userRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $zpower = $repo->find($id);
        if (!$zpower) {
            return $this->json(['error' => 'ZPower not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $zpower->fromJson($data);

        if (isset($data['usuario_id'])) {
            $user = $userRepo->find($data['usuario_id']);
            if (!$user) {
                return $this->json(['error' => 'User not found'], 404);
            }
            $zpower->setUsuario($user);
        }

        $em->flush();

        return $this->json($zpower->toArray());
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, ZPowerRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $zpower = $repo->find($id);
        if (!$zpower) {
            return $this->json(['error' => 'ZPower not found'], 404);
        }

        $em->remove($zpower);
        $em->flush();

        return $this->json(null, 204);
    }
}
