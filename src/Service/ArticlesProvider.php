<?php

namespace App\Service;

use App\Form\Model\ArticleFormModel;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ArticlesProvider
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function fetchArticlesList(int $page = 1, ?int $categoryId = null)
    {
        $url = 'https://127.0.0.1:8000/api/articles?page=' . $page;
        
        if($categoryId) {
            $url .= '&category=' . $categoryId;
        }

        return $this->getData($url);
    }

    public function fetchUsersList(int $page = 1, ?int $categoryId = null)
    {
        $url = 'https://127.0.0.1:8000/api/users';
        
        return $this->getData($url);
    }

    public function fetchCategoryList()
    {
        $url = 'https://127.0.0.1:8000/api/categories';
        
        return $this->getData($url);
    }

    public function fetchArticle(int $id)
    {
        $url = 'https://127.0.0.1:8000/api/articles/' . $id;
        
        return $this->getData($url);
    }

    public function postArticle(ArticleFormModel $formModel)
    {
        $url = 'https://127.0.0.1:8000/api/articles';
        
        $data = array(
            'title' => $formModel->title,
            'publishedAt' => (new \DateTime())->format("Y-m-d H:i:s"),
            'author' => '/api/users/' . $formModel->author,
            'category' => '/api/categories/' . $formModel->category,
        );
        
        return $this->sendData($url, $data);
    }

    public function postLike(int $userId, int $articleId)
    {
        $url = 'https://127.0.0.1:8000/api/likes';
        
        $data = array(
            'author' => '/api/users/' . $userId,
            'article' => '/api/articles/' . $articleId,
        );
        
        return $this->sendData($url, $data);
    }

    public function deleteLike(int $likeId)
    {
        $url = 'https://127.0.0.1:8000/api/likes/' . $likeId;
        
        return $this->deleteItem($url);
    }
    
    private function getData(string $url)
    {
        $response = $this->client->request(
            'GET',
            $url
        );
        
        if($response->getStatusCode() > 299) {
            
            return null;
        }
        
        $content = $response->toArray();
       
        return $content;
    }

    private function sendData(string $url, array $data, string $method = 'POST')
    {
        $response = $this->client->request(
            $method,
            $url,
            [
                'json' => $data,
            ]
        );

        return ($response->getStatusCode() > 299) ? false : true;
    }

    private function deleteItem(string $url)
    {
        $response = $this->client->request(
            'DELETE',
            $url
        );

        return ($response->getStatusCode() > 299) ? false : true;
    }
}
