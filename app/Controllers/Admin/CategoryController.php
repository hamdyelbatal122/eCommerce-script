<?php

namespace ECommerce\App\Controllers\Admin;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Validator;
use ECommerce\App\Models\Category;

/**
 * Admin Category Controller
 */
class CategoryController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
    }

    private function requireAdmin()
    {
        if (!Authenticator::isAdmin()) {
            $this->abort(403, 'Access Denied');
        }
    }

    /**
     * List categories
     */
    public function index()
    {
        $categoryModel = new Category();
        $categories = $categoryModel->getCategoriesTree();

        return $this->render('admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Create category form
     */
    public function createForm()
    {
        $categoryModel = new Category();
        $categories = $categoryModel->all();

        return $this->render('admin.categories.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store category
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories/create');
        }

        $data = $this->post();
        $validator = Validator::validate($data);

        $validator->required('name', 'Category name is required');

        if ($validator->fails()) {
            $this->json(['errors' => $validator->errors()], 422);
        }

        $categoryModel = new Category();
        $categoryId = $categoryModel->insert([
            'name' => htmlspecialchars($data['name']),
            'description' => htmlspecialchars($data['description'] ?? ''),
            'parent' => (int) ($data['parent'] ?? 0),
            'visibility' => isset($data['visibility']) ? 1 : 0,
            'allow_comment' => isset($data['allow_comment']) ? 1 : 0,
            'allow_ads' => isset($data['allow_ads']) ? 1 : 0,
        ]);

        if ($categoryId) {
            $this->json(['success' => true, 'id' => $categoryId]);
        }

        $this->json(['error' => 'Failed to create category'], 500);
    }

    /**
     * Edit category
     */
    public function edit($id)
    {
        $categoryModel = new Category();
        $category = $categoryModel->find($id);

        if (!$category) {
            $this->abort(404, 'Category not found');
        }

        $categories = $categoryModel->all();

        return $this->render('admin.categories.edit', [
            'category' => $category,
            'categories' => $categories,
        ]);
    }

    /**
     * Update category
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/admin/categories/{$id}/edit");
        }

        $categoryModel = new Category();
        $category = $categoryModel->find($id);

        if (!$category) {
            $this->json(['error' => 'Category not found'], 404);
        }

        $data = $this->post();
        $validator = Validator::validate($data);

        $validator->required('name', 'Category name is required');

        if ($validator->fails()) {
            $this->json(['errors' => $validator->errors()], 422);
        }

        $updateData = [
            'name' => htmlspecialchars($data['name']),
            'description' => htmlspecialchars($data['description'] ?? ''),
            'parent' => (int) ($data['parent'] ?? 0),
            'visibility' => isset($data['visibility']) ? 1 : 0,
            'allow_comment' => isset($data['allow_comment']) ? 1 : 0,
            'allow_ads' => isset($data['allow_ads']) ? 1 : 0,
        ];

        if ($categoryModel->update($id, $updateData)) {
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to update category'], 500);
    }

    /**
     * Delete category
     */
    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid method'], 405);
        }

        $categoryModel = new Category();
        $category = $categoryModel->find($id);

        if (!$category) {
            $this->json(['error' => 'Category not found'], 404);
        }

        if ($categoryModel->delete($id)) {
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to delete category'], 500);
    }
}
