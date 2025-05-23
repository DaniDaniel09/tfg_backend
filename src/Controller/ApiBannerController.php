<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Banner;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/banner')]
final class ApiBannerController extends AbstractController
{
    #[Rest\Get(path:'/', name:'banner_api_list')]
    public function bannerApiList(EntityManagerInterface $entityManager): JsonResponse
    {
        $banners = $entityManager->getRepository(Banner::class)->findAll();
        $bannersList = [];

        if (count($banners) > 0) {
            foreach ($banners as $banner) {
                $bannersList[] = $banner->toArray();
            }
            $response = [
                'ok' => true,
                'banners' => $bannersList,
            ];
            return new JsonResponse($response, 200);
        }else{
            $response = [
                'ok' => false,
                'error' => 'No banners found',
            ];
            return new JsonResponse($response, 404);
        }
    }

    #[Rest\Get(path:'/{id}', name:'single_banner_api')]
    public function index(EntityManagerInterface $entityManager,$id=''): JsonResponse
    {
        $banner = $entityManager->getRepository(Banner::class)->find($id);
        if ($banner){
            $bannerArray = $banner->toArray();
            $response = [
                'ok' => true,
                'banner' => $bannerArray,
            ];
            return new JsonResponse($response, 200);
        }else{
            $response = [
                'ok' => false,
                'error' => 'Banner not found',
            ];
            return new JsonResponse($response, 404);
        }
    }

    #[Rest\Post(path: '/', name: 'banner_api_create')]
    public function createBanner(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['banner_name'], $data['banner_type'])) {
            return new JsonResponse(['ok' => false, 'error' => 'Missing banner_name or banner_type'], 400);
        }

        $banner = new Banner();
        $banner->setBannerName($data['banner_name']);
        $banner->setBannerType($data['banner_type']);

        // ⚠️ NO asignamos unidades en este punto
        $entityManager->persist($banner);
        $entityManager->flush();

        return new JsonResponse(['ok' => true, 'banner' => $banner->toArray()], 201);
    }

    #[Rest\Put(path: '/{id}', name: 'banner_api_update')]
    public function updateBanner(Request $request, EntityManagerInterface $entityManager, $id): JsonResponse
    {
        $banner = $entityManager->getRepository(Banner::class)->find($id);
        if (!$banner) {
            return new JsonResponse(['ok' => false, 'error' => 'Banner not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['banner_name'], $data['banner_type'])) {
            return new JsonResponse(['ok' => false, 'error' => 'Invalid data'], 400);
        }

        $banner->setBannerName($data['banner_name']);
        $banner->setBannerType($data['banner_type']);
        $banner->setUnits($data['units'] ?? []);

        $entityManager->persist($banner);
        $entityManager->flush();

        return new JsonResponse(['ok' => true, 'banner' => $banner->toArray()], 200);
    }

    #[Rest\Delete(path: '/{id}', name: 'banner_api_delete')]
    public function deleteBanner(EntityManagerInterface $entityManager, $id): JsonResponse
    {
        $banner = $entityManager->getRepository(Banner::class)->find($id);
        if (!$banner) {
            return new JsonResponse(['ok' => false, 'error' => 'Banner not found'], 404);
        }

        $entityManager->remove($banner);
        $entityManager->flush();

        return new JsonResponse(['ok' => true, 'message' => 'Banner deleted'], 200);
    }

}
