<?php

namespace App\Entity;

use App\Repository\LikeRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=LikeRepository::class)
 * @ORM\Table(name="`like`")
 * @ApiResource(
 *   normalizationContext={"groups" = {"readLike"}},
 *   denormalizationContext={"groups" = {"writeLike"}},
 *   itemOperations={"get", "delete"},
 *   collectionOperations={"get","post"}
 * )
 * @UniqueEntity(
 *     fields={"article", "author"},
 *     errorPath="author",
 *     message="No more one like"
 * )
 */
class Like
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"readArticle"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Article::class, inversedBy="likes")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readLike", "writeLike"})
     */
    private $article;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="likes")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readLike", "writeLike", "readArticle"})
     */
    private $author;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }
}
