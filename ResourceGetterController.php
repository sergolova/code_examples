<?php

// Returning a resource by its type and ID in the database.

namespace App\Controller;

use App\Entity\Users;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResourceGetterController extends AbstractController
{

    #[Route('/resource/{type}/{id}', name: 'app_resource_getter')]
    public function index(string $type, string $id): Response
    {
        $id = DIRECTORY_SEPARATOR . str_replace('-', '.', $id);
        $fileName =
          match (strtolower($type)) {
              'avatar' => $this->getParameter(
                  'user_avatars_path'
                ) . $id,
              'file' => $this->getParameter(
                  'user_files_path'
                ) . $id,
              default => $this->getParameter('user_not_found')
          };

        $response = new Response();

        if (file_exists($fileName)) {
            $imageData = file_get_contents($fileName);
            $response->setContent($imageData);
            $response->headers->set('Content-Type', 'image/jpeg');                                                    
            $response->headers->set(
              'Content-Disposition',
              'inline; filename="image.jpg"'
            );
        }

        return $response;
    }

}