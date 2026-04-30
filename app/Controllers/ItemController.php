<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\Core\Authenticator;
use ECommerce\Core\Validator;
use ECommerce\App\Models\Item;
use ECommerce\App\Models\Category;
use ECommerce\App\Models\Comment;

/**
 * Item Controller
 * 
 * Handles item listing and creation
 */
class ItemController extends BaseController
{
    /**
     * Display item details
     */
    public function show($id)
    {
        $itemModel = new Item();
        $item = $itemModel->getFullDetails($id);

        if (!$item || $item['approve'] !== 1) {
            $this->abort(404, 'Item not found');
        }

        $commentModel = new Comment();
        $comments = $commentModel->getItemComments($id);
        $commentsCount = $commentModel->getItemCommentsCount($id);

        return $this->render('item.show', [
            'item' => $item,
            'comments' => $comments,
            'comments_count' => $commentsCount,
            'can_comment' => Authenticator::isLoggedIn(),
        ]);
    }

    /**
     * Display create item form
     */
    public function createForm()
    {
        if (!Authenticator::isLoggedIn()) {
            $this->flash('error', 'You must be logged in to post an item', 'error');
            $this->redirect('/login');
        }

        $categoryModel = new Category();
        $categories = $categoryModel->getRootCategories();

        return $this->render('item.create', [
            'categories' => $categories,
            'flash' => $this->getFlash(),
        ]);
    }

    /**
     * Handle item creation
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/items/create');
        }

        if (!Authenticator::isLoggedIn()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        // Validate input
        $validator = Validator::validate($this->post());
        
        $validator->required('name', 'Item name is required')
                  ->minLength('name', 5, 'Name must be at least 5 characters')
                  ->required('description', 'Description is required')
                  ->minLength('description', 10, 'Description must be at least 10 characters')
                  ->required('price', 'Price is required')
                  ->numeric('price', 'Price must be numeric')
                  ->required('category_id', 'Category is required');

        if ($validator->fails()) {
            $this->json(['errors' => $validator->errors()], 422);
        }

        // Insert item
        $itemModel = new Item();
        $itemId = $itemModel->insert([
            'name' => htmlspecialchars($this->post('name')),
            'description' => htmlspecialchars($this->post('description')),
            'price' => (float) $this->post('price'),
            'cat_id' => (int) $this->post('category_id'),
            'member_id' => Authenticator::userId(),
            'add_date' => date('Y-m-d H:i:s'),
            'approve' => 0,
            'tags' => htmlspecialchars($this->post('tags', '')),
            'country_made' => htmlspecialchars($this->post('country_made', '')),
        ]);

        if ($itemId) {
            $this->json(['success' => true, 'id' => $itemId]);
        }

        $this->json(['error' => 'Failed to create item'], 500);
    }

    /**
     * Display items by category
     */
    public function category($id)
    {
        $categoryModel = new Category();
        $category = $categoryModel->find($id);

        if (!$category) {
            $this->abort(404, 'Category not found');
        }

        $page = $this->getParam('page', 1);
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $itemModel = new Item();
        $items = $itemModel->getCategoryItems($id, $limit, $offset);
        $total = $itemModel->count(['cat_id' => $id, 'approve' => 1]);
        $totalPages = ceil($total / $limit);

        return $this->render('item.category', [
            'category' => $category,
            'items' => $items,
            'page' => $page,
            'total_pages' => $totalPages,
            'breadcrumb' => $categoryModel->getBreadcrumb($id),
        ]);
    }
}
