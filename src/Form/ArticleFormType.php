<?php

namespace App\Form;

use App\Service\ArticlesProvider;
use App\Form\Model\ArticleFormModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ArticleFormType extends AbstractType
{
    private $articlesProvider;

    public function __construct(ArticlesProvider $articlesProvider)
    {
        $this->articlesProvider = $articlesProvider;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categoriesRaw = $this->articlesProvider->fetchCategoryList();
        $usersRaw = $this->articlesProvider->fetchUsersList();
        $categories = [];
        
        foreach($categoriesRaw['hydra:member'] as $item) {
            $categories[$item['title']] = $item['id'];
        }
        
        foreach($usersRaw['hydra:member'] as $item) {
            $users[$item['firstName']] = $item['id'];
        }
        
        $builder
            ->add('title', TextType::class, [
                'label' => 'Укажите название статьи',
                'required' => true,
            ])
            ->add('category', ChoiceType::class, [
                'choices' => $categories,
                'placeholder' => 'Выберите категорию',
                'required'   => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ArticleFormModel::class,
        ]);
    }
}
