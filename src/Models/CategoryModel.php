<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\BeanHelper;
use RedBeanPHP\R;

class CategoryModel
{
    public function findAll(): array
    {
        return BeanHelper::castBeanArray(R::findAll('categories', 'ORDER BY name ASC'));
    }

    public function getAll(): array
    {
        return $this->findAll();
    }

    /**
     * Paginated variant of getAll for the admin /categories index.
     * Same ORDER BY name as findAll() with LIMIT ? OFFSET ? appended.
     */
    public function getAllPaginated(int $limit, int $offset): array
    {
        return BeanHelper::castBeanArray(
            R::findAll('categories', 'ORDER BY name ASC LIMIT ? OFFSET ?', [$limit, $offset])
        );
    }

    /** Row-count of every category — drives the admin /categories paginator. */
    public function countAll(): int
    {
        return (int) R::count('categories');
    }

    public function load(int $id): mixed
    {
        return BeanHelper::castBeanProperties(R::load('categories', $id));
    }

    public function getById(int $id): mixed
    {
        $bean = R::load('categories', $id);
        return BeanHelper::isValidBean($bean) ? BeanHelper::castBeanProperties($bean) : null;
    }

    public function create(string $name): void
    {
        $bean = R::dispense('categories');
        $bean->name = $name;
        R::store($bean);
    }

    public function save(mixed $bean): void
    {
        R::store($bean);
    }

    public function delete(mixed $bean): void
    {
        R::trash($bean);
    }
}
