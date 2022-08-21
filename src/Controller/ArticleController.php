<?php

namespace App\Controller;

use App\Form\ArticleFormType;
use App\Service\ArticlesProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_USER")
 */
class ArticleController extends AbstractController
{
    const MAX_ITEM_PER_PAGE = 30;
    /**
     * @Route("/", name="app_articles")
     */
    public function articles(ArticlesProvider $articlesProvider, Request $request): Response
    {
        $selectCategory = $request->query->get('category') ?: null;
        $page = $request->query->get('page') ?: null;
        
        if(!$page) {
            $page = 1;
        }
        
        $rawArticles = $articlesProvider->fetchArticlesList($page, $selectCategory);
        $articles = $rawArticles['hydra:member'];

        $likes = [];
        $author = $articlesProvider->getUserByEmail($this->getUser()->getEmail());
        
        $authorId = $author['hydra:member'][0]['id'];
        foreach($articles as $article) {
            foreach($article['likes'] as $like) {
                if($like['author']['id'] == $authorId) {
                    $likes[] = $article['id'];
                }
            }
        }
        
        $totalItems = $rawArticles['hydra:totalItems'];
        $pages = 1;
        
        if($totalItems > count($articles) && count($articles) > 0) {
            $pages = ($totalItems % self::MAX_ITEM_PER_PAGE) ? intdiv($totalItems, self::MAX_ITEM_PER_PAGE) + 1 : intdiv($totalItems, self::MAX_ITEM_PER_PAGE);
        }
        
        $rawCategory = $articlesProvider->fetchCategoryList();
        $category = $rawCategory['hydra:member'];
         
        return $this->render('history.html.twig', [
            'articles' => $articles,
            'category' => $category,
            'selectCategory' => $selectCategory,
            'pages' => $pages,
            'page' => $page,
            'likes' => $likes,
        ]);
    }

    /**
     * @Route("/article/create", name="app_article_create")
     */
    public function createArticle(ArticlesProvider $articlesProvider, Request $request): Response
    {
        $form = $this->createForm(ArticleFormType::class);

        $form->handleRequest($request);

        $error = null;
        
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $author = $articlesProvider->getUserByEmail($this->getUser()->getEmail());
            $article->author = $author['hydra:member'][0]['id'];
            $result = $articlesProvider->postArticle($article);

            if($result) {
                
                return $this->redirectToRoute('app_articles');
            } else {
                $error = "Не удалось создать статью";
            }
        }

        return $this->render('create_article.html.twig', [
            'articleForm' => $form->createView(),
            'error' => $error,
        ]);
    }

    /**
     * @Route("/likes/{id}", name="app_likes")
     */
    public function likes(ArticlesProvider $articleProvider, int $id): Response
    {
        $article = $articleProvider->fetchArticle($id);
        
        return $this->render('likes.html.twig', [
            'article' => $article,
       ]);
    }
}
