<?php
namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    #[Route('/articles')]
    public function list(ArticleRepository $articles): Response
    {
        $latest = $articles->findBy([], ['title' => 'ASC'], limit: 10);
        return $this->render('article/list.html.twig', [
            'articles' => $latest,
        ]);
    }
}
