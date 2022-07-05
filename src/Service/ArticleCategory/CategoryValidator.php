<?php

declare(strict_types=1);

namespace App\Service\ArticleCategory;

use App\Entity\Category;
use App\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryValidator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @throws ValidationException
     */
    public function validate(Category $articleCategory): void
    {
        $violations = $this->validator->validate($articleCategory);

        if ($violations->count() > 0) {
            throw new ValidationException($violations->get(0)->getMessage());
        }
    }
}