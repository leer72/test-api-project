<?php

namespace App\Controller;

use App\Service\ArticlesProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ArticleLikeController extends AbstractController
{
    /**
     * @Route("/articles/{id}/like/{type<like|dislike>}", methods={"POST"}, name="app_article_like")
     */
    public function like(ArticlesProvider $articlesProvider, int $id, string $type)
    {
        $article = $articlesProvider->fetchArticle($id);

        $likeExist = false;
        $likeId = 0;
        $likeOffset = 0;

        if(count($article['likes'])) {
            foreach($article['likes'] as $item) {
                if($item['author']['id'] == $this->getUser()->getId()) {
                    $likeId = $item['id'];
                    $likeExist = true;
                    break;
                }
            }
        }
        
        if($likeExist) {
            if($articlesProvider->deleteLike($likeId)) {
                $likeOffset = -1;
            }
        } else {
            if($articlesProvider->postLike($this->getUser()->getId(), $id)) {
                $likeOffset = 1;
            }
        }

        return $this->json(['likes' => count($article['likes']) + $likeOffset]);
    }
}
