<?php

namespace ECommerce\App\Controllers\Admin;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Validator;
use ECommerce\App\Models\Item;
use ECommerce\App\Models\Category;

/**
 * Admin Item Controller
 */
class ItemController extends BaseController
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
     * List items
     */
    public function index()
    {
        $itemModel = new Item();
        $status = $this->getParam('status', 'all');

        if ($status === 'pending') {
            $items = $itemModel->getPending();
        } else {
            $items = $itemModel->all();
        }

        return $this->render('admin.items.index', [
            'items' => $items,
            'status' => $status,
        ]);
    }

    /**
     * Edit item
     */
    public function edit($id)
    {
        $itemModel = new Item();
        $item = $itemModel->find($id);

        if (!$item) {
            $this->abort(404, 'Item not found');
        }

        $categoryModel = new Category();
        $categories = $categoryModel->all();

        return $this->render('admin.items.edit', [
            'item' => $item,
            'categories' => $categories,
        ]);
    }

    /**
     * Update item
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/admin/items/{$id}/edit");
        }

        $itemModel = new Item();
        $item = $itemModel->find($id);

        if (!$item) {
            $this->json(['error' => 'Item not found'], 404);
        }

        $data = $this->post();
        $validator = Validator::validate($data);

        $validator->required('name', 'Name is required')
                  ->required('description', 'Description is required')
                  ->required('price', 'Price is required')
                  ->numeric('price', 'Price must be numeric');

        if ($validator->fails()) {
            $this->json(['errors' => $validator->errors()], 422);
        }

        $updateData = [
            'name' => htmlspecialchars($data['name']),
            'description' => htmlspecialchars($data['description']),
            'price' => (float) $data['price'],
            'cat_id' => (int) $data['cat_id'],
            'tags' => htmlspecialchars($data['tags'] ?? ''),
            'country_made' => htmlspecialchars($data['country_made'] ?? ''),
        ];

        if ($itemModel->update($id, $updateData)) {
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to update item'], 500);
    }

    /**
     * Approve item
     */
    public function approve($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid method'], 405);
        }

        $itemModel = new Item();
        $item = $itemModel->find($id);

        if (!$item) {
            $this->json(['error' => 'Item not found'], 404);
        }

        if ($itemModel->approve($id)) {
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to approve item'], 500);
    }

    /**
     * Delete item
     */
    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid method'], 405);
        }

        $itemModel = new Item();
        $item = $itemModel->find($id);

        if (!$item) {
            $this->json(['error' => 'Item not found'], 404);
        }

        if ($itemModel->delete($id)) {
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to delete item'], 500);
    }
}
