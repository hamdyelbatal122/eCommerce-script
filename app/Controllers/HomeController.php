<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\App\Models\Item;
use ECommerce\App\Models\Category;
use ECommerce\App\Services\FileCacheService;

/**
 * Home Controller
 * 
 * Handles homepage and main navigation
 */
class HomeController extends BaseController
{
    /**
     * Display homepage
     */
    public function index()
    {
        $itemModel = new Item();
        $categoryModel = new Category();
        $cache = new FileCacheService();

        $items = $cache->remember('home.latest_items', 120, function () use ($itemModel) {
            return $itemModel->getLatest(12);
        });
        $categories = $cache->remember('home.categories_with_counts', 300, function () use ($categoryModel) {
            return $categoryModel->getCategoriesWithCounts();
        });
        $stats = $cache->remember('home.stats', 180, function () use ($itemModel) {
            return [
                'total_items' => $itemModel->getTotalCount(),
                'total_users' => (new \ECommerce\App\Models\User())->getTotalCount(),
            ];
        });

        return $this->render('home.index', [
            'items' => $items,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }

    /**
     * Search items
     */
    public function search()
    {
        $query = $this->getParam('q', '');
        
        if (empty($query)) {
            $this->redirect('/');
        }

        $itemModel = new Item();
        $items = $itemModel->search($query);

        return $this->render('home.search', [
            'query' => $query,
            'items' => $items,
        ]);
    }

    /**
     * Display about page
     */
    public function about()
    {
        return $this->render('home.about');
    }

    /**
     * Display contact page
     */
    public function contact()
    {
        return $this->render('home.contact');
    }
}
