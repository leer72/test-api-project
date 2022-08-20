<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ArticleFixtures extends BaseFixtures implements DependentFixtureInterface
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Article::class, 60, function (Article $article) {
            $category = $this->getRandomReference(Category::class);
            $user = $this->getRandomReference(User::class);
            
            $article
                ->setTitle($this->faker->text())
                ->setAuthor($user)
                ->setCategory($category)
                ->setPublishedAt(new \DateTime())
            ;
        });

       $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            CategoryFixtures::class,
        ];
    }
}
