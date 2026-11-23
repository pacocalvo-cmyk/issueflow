<?php

declare(strict_types=1);

namespace App\Form\Model;

use App\Entity\Category;
use Symfony\Component\Validator\Constraints as Assert;

final class CategoryData
{
    #[Assert\NotBlank(message: 'El nombre de la categoría es obligatorio', normalizer: 'trim')]
    #[Assert\Length(max: 120, maxMessage: 'El nombre no puede superar los 120 caracteres', normalizer: 'trim')]
    public string $name = '';

    public static function fromCategory(Category $category): self
    {
        $data = new self();
        $data->name = $category->getName();

        return $data;
    }
}
