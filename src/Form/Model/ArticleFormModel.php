<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ArticleFormModel
{
    /**
     * @Assert\NotBlank()
     */
    public $title;
    
    /**
     * @Assert\NotBlank()
     */
    public $category;

    public $author;
}
