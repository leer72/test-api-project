<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends BaseFixtures
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Category::class, 15, function (Category $category) use ($manager) {
            $category
                ->setTitle($this->faker->word())
            ;
        });

        $manager->flush();
    }
}
