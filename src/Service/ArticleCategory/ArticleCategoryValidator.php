<?php

declare(strict_types=1);

namespace App\Service\ArticleCategory;

use App\Entity\ArticleCategory;
use App\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleCategoryValidator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @throws ValidationException
     */
    public function validate(ArticleCategory $articleCategory): void
    {
        $violations = $this->validator->validate($articleCategory);

        if ($violations->count() > 0) {
            throw new ValidationException($violations->get(0)->getMessage());
        }
    }
}