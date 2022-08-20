<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Like;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class LikeFixtures extends BaseFixtures implements DependentFixtureInterface
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Like::class, 90, function (Like $like) {
            $article = $this->getRandomReference(Article::class);
            $user = $this->getRandomReference(User::class);
            
            $like
                ->setAuthor($user)
                ->setArticle($article)
            ;
        });

       $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            ArticleFixtures::class,
        ];
    }
}
