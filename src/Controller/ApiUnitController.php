<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use App\Entity\Unit;

#[Route('/api/unit')]
class ApiUnitController extends AbstractController
{
    #[Rest\Get(path:'/', name:'unit_api_list')]
    public function unitApiList(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $conn   = $entityManager->getConnection();
        $sql    = 'SELECT id FROM unit WHERE 1=1';
        $params = [];

        // Filtrar por nombre
        if ($name = $request->query->get('name')) {
            $sql .= ' AND ( JSON_CONTAINS(name, :jsonName) = 1
                         OR CAST(name AS CHAR) COLLATE utf8_general_ci LIKE :likeName )';
            $params['jsonName'] = json_encode($name);
            $params['likeName'] = '%'.$name.'%';
        }

        // Filtrar por color
        if ($color = $request->query->get('color')) {
            $allowed = ['red','blue','yellow','purple','green'];
            $color   = strtolower($color);
            if (!in_array($color, $allowed, true)) {
                return new JsonResponse(['ok'=>false,'error'=>"Color inválido: debe ser uno de ".implode(', ',$allowed)], 400);
            }
            $sql .= ' AND JSON_CONTAINS(colors, JSON_QUOTE(:col))';
            $params['col'] = $color.'.webp';
        }

        // Filtrar por rareza
        if ($rarity = $request->query->get('rarity')) {
            $allowed = ['hero','extreme','sparking','ultra'];
            $rarity  = strtolower($rarity);
            if (!in_array($rarity, $allowed, true)) {
                return new JsonResponse(['ok'=>false,'error'=>"Rareza inválida: debe ser uno de ".implode(', ',$allowed)], 400);
            }
            $sql .= ' AND rarity = :rarity';
            $params['rarity'] = $rarity;
        }

        // Filtrar booleanos
        foreach (['is_legends_limited','is_zenkai','is_fusing','is_tag','is_transforming'] as $field) {
            if (null !== ($val = $request->query->get($field))) {
                $bool = filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if (is_null($bool)) {
                    return new JsonResponse(['ok'=>false,'error'=>"Valor booleano inválido para {$field}"], 400);
                }
                $sql .= " AND {$field} = :{$field}";
                $params[$field] = $bool ? 1 : 0;
            }
        }

        $stmt = $conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $rows = $stmt->executeQuery()->fetchAllAssociative();

        if (empty($rows)) {
            return new JsonResponse(['ok'=>true,'units'=>[]], 200);
        }

        $ids       = array_column($rows, 'id');
        $units     = $entityManager->getRepository(Unit::class)->findBy(['id' => $ids]);
        $unitsList = [];
        foreach ($units as $unit) {
            $unitsList[] = $unit->toArray();
        }

        return new JsonResponse(['ok'=>true,'units'=>$unitsList], 200);
    }

    #[Rest\Get(path:'/{id}', name:'single_unit_api')]
    public function index(EntityManagerInterface $entityManager, $id = ''): JsonResponse
    {
        $unit = $entityManager->getRepository(Unit::class)->find($id);
        if ($unit) {
            return new JsonResponse(['ok'=>true,'unit'=>$unit->toArray()], 200);
        }
        return new JsonResponse(['ok'=>false,'error'=>'Unit not found'], 404);
    }

    #[Rest\Post(path:'/', name:'unit_api_new_unit')]
    public function newUnit(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        try {
            $content  = $request->getContent();
            $unitData = json_decode($content, true);

            if (isset($unitData['images']) && isset($unitData['image_base64']) && isset($unitData['rarity'])) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/units/' . $unitData['rarity'];
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0777, true);
                }

                foreach ($unitData['images'] as $i => $filename) {
                    $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $unitData['image_base64'][$i] ?? '');
                    file_put_contents($uploadsDir . '/' . $filename, base64_decode($base64));
                }
            }

            if (isset($unitData['name']) && !is_array($unitData['name'])) {
                $unitData['name'] = [$unitData['name']];
                $content = json_encode($unitData);
            }

            $unit = new Unit();
            $unit->fromJson($content);
            $entityManager->persist($unit);
            $entityManager->flush();

            return new JsonResponse(['ok'=>true,'unit'=>'Unit inserted'], 201);
        } catch (\Throwable $e) {
            return new JsonResponse(['ok'=>false,'error'=>'Error inserting unit: '.$e->getMessage()], 400);
        }
    }

    #[Rest\Put(path:'/{id}', name:'unit_api_update_unit')]
    public function editUnit(EntityManagerInterface $entityManager, Request $request, $id = ''): JsonResponse
    {
        try {
            $content  = $request->getContent();
            $unitData = json_decode($content, true);

            $unit = $entityManager->getRepository(Unit::class)->find($id);
            if (!$unit) {
                return new JsonResponse(['ok'=>false,'error'=>'Unit not found'], 404);
            }

            if (isset($unitData['name']) && !is_array($unitData['name'])) {
                $unitData['name'] = [$unitData['name']];
                $content = json_encode($unitData);
            }

            // (Aquí sigue el código de manejo de imágenes y colores en base64)

            $unit->fromJson($content);
            $entityManager->flush();

            return new JsonResponse(['ok'=>true,'unit'=>'Unit updated'], 200);
        } catch (\Throwable $e) {
            return new JsonResponse(['ok'=>false,'error'=>'Error updating unit: '.$e->getMessage()], 400);
        }
    }

    #[Rest\Delete(path:'/{id}', name:'unit_api_delete_unit')]
    public function deleteUnit(EntityManagerInterface $entityManager, $id = ''): JsonResponse
    {
        try {
            $unit = $entityManager->getRepository(Unit::class)->find($id);
            if (!$unit) {
                return new JsonResponse(['ok'=>false,'error'=>'Unit not found'], 404);
            }

            $rarity = $unit->getRarity();
            foreach ($unit->getImages() as $img) {
                $this->removeImageFile($img, $rarity);
            }
            foreach ($unit->getColors() as $col) {
                $this->removeImageFile($col, $rarity);
            }

            $entityManager->remove($unit);
            $entityManager->flush();

            return new JsonResponse(['ok'=>true,'unit'=>'Unit deleted'], 200);
        } catch (\Throwable $e) {
            return new JsonResponse(['ok'=>false,'error'=>'Error deleting unit: '.$e->getMessage()], 400);
        }
    }

    private function removeImageFile(string $imageName, string $rarity): bool
    {
        $path = $this->getParameter('kernel.project_dir').'/public/uploads/units/'.$rarity.'/'.$imageName;
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }
}
