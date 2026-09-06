<?php
namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostController extends AbstractController
{
    #[Route('/posts/{id}/edit')]
    #[IsGranted('POST_EDIT', subject: 'post')]
    public function edit(Post $post): Response
    {
        // if the voter denies access, Symfony already returned
        // a 403 page before running any of this code
    }
}
