<?php

namespace App\Service;

use App\Form\Model\ArticleFormModel;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ArticlesProvider
{
    const BASE_URL = 'https://127.0.0.1:8000';
    
    private $client;

    private $security;

    public function __construct(HttpClientInterface $client, Security $security)
    {
        $this->client = $client;
        $this->security = $security;
    }

    public function fetchArticlesList(int $page = 1, ?int $categoryId = null)
    {
        $url = self::BASE_URL . '/api/articles?page=' . $page;
        
        if($categoryId) {
            $url .= '&category=' . $categoryId;
        }

        return $this->getData($url);
    }

    public function fetchUsersList(int $page = 1, ?int $categoryId = null)
    {
        $url = self::BASE_URL . '/api/users';
        
        return $this->getData($url);
    }

    public function fetchCategoryList()
    {
        $url = self::BASE_URL . '/api/categories';
        
        return $this->getData($url);
    }

    public function fetchArticle(int $id)
    {
        $url = self::BASE_URL . '/api/articles/' . $id;
        
        return $this->getData($url);
    }

    public function postArticle(ArticleFormModel $formModel)
    {
        $url = self::BASE_URL . '/api/articles';
        
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
        $url = self::BASE_URL . '/api/likes';
        
        $data = array(
            'author' => '/api/users/' . $userId,
            'article' => '/api/articles/' . $articleId,
        );
        
        return $this->sendData($url, $data);
    }

    public function deleteLike(int $likeId)
    {
        $url = self::BASE_URL . '/api/likes/' . $likeId;
        
        return $this->deleteItem($url);
    }

    public function getToken(string $email, string $password)
    {
        $response = $this->client->request(
            'POST',
            self::BASE_URL . '/authentication_token',
            [
                'json' => array('email' => $email, 'password' => $password),
            ]
        );

        if($response->getStatusCode() == 200) {
            $content = $response->toArray();
            
            return $content['token'];
        }
        
        return null;
    }

    public function getUserByEmail(string $email)
    {
        $url = self::BASE_URL . '/api/users';
        
        if($email) {
            $url .= '?email=' . $email;
        }

        return $this->getData($url);
    }
    
    private function getData(string $url)
    {
        $response = $this->client->request(
            'GET',
            $url,
            [
                'auth_bearer' => $this->security->getUser()->getToken(),
            ]
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
                'auth_bearer' => $this->security->getUser()->getToken(),
            ]
        );

        return ($response->getStatusCode() > 299) ? false : true;
    }

    private function deleteItem(string $url)
    {
        $response = $this->client->request(
            'DELETE',
            $url,
            [
                'auth_bearer' => $this->security->getUser()->getToken(),
            ]
        );

        return ($response->getStatusCode() > 299) ? false : true;
    }
}
