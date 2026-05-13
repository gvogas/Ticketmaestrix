<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\CategoryModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class CategoryController
{
    public function __construct(
        private Environment $twig,
        private CategoryModel $categoryModel,
        private string $basePath,
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $html = $this->twig->render('category/index.html.twig', [
            'base_path'  => $this->basePath,
            'categories' => $this->categoryModel->getAll(),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function create(Request $request, Response $response): Response
    {
        $html = $this->twig->render('category/create.html.twig', [
            'base_path' => $this->basePath,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function store(Request $request, Response $response): Response
    {

        $data = (array) ($request->getParsedBody() ?? []);

        $errors = [];
        if (empty($data['name'])) $errors['name'] = ['Name is required.'];

        if ($errors) {
            $html = $this->twig->render('category/create.html.twig', [
                'base_path' => $this->basePath,
                'errors'    => $errors,
                'input'     => $data,
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(422);
        }

        $this->categoryModel->create((string) ($data['name'] ?? ''));

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.category_created'];
        return $response->withHeader('Location', $this->basePath . '/categories')->withStatus(302);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $category = $this->categoryModel->getById((int) $args['id']);

        if (!$category) {
            return $response->withHeader('Location', $this->basePath . '/categories')->withStatus(302);
        }

        $html = $this->twig->render('category/edit.html.twig', [
            'base_path' => $this->basePath,
            'category'  => $category,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function update(Request $request, Response $response, array $args): Response
    {

        $id   = (int) $args['id'];
        $data = (array) ($request->getParsedBody() ?? []);

        $errors = [];
        if (empty($data['name'])) $errors['name'] = ['Name is required.'];

        if ($errors) {
            $category = $this->categoryModel->getById($id);
            $html     = $this->twig->render('category/edit.html.twig', [
                'base_path' => $this->basePath,
                'category'  => $category,
                'errors'    => $errors,
                'input'     => $data,
            ]);
            $response->getBody()->write($html);
            return $response->withStatus(422);
        }

        $category = $this->categoryModel->load($id);

        if ($category->id) {
            $category->name = (string) ($data['name'] ?? $category->name);
            $this->categoryModel->save($category);
        }

        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.category_updated'];
        return $response->withHeader('Location', $this->basePath . '/categories')->withStatus(302);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $category = $this->categoryModel->load((int) $args['id']);
        if ($category->id) {
            $this->categoryModel->delete($category);
        }
        $_SESSION['flash'] = ['type' => 'success', 'key' => 'flash.category_deleted'];
        return $response->withHeader('Location', $this->basePath . '/categories')->withStatus(302);
    }

    public function viewDetails(Request $request, Response $response, array $args): Response
    {
        $category = $this->categoryModel->getById((int) $args['id']);

        if (!$category) {
            return $response->withHeader('Location', $this->basePath . '/categories')->withStatus(302);
        }

        $html = $this->twig->render('category/category_detail.html.twig', [
            'base_path' => $this->basePath,
            'category'  => $category,
        ]);
        $response->getBody()->write($html);
        return $response;
    }


}
