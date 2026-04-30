<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;
use ECommerce\App\Models\Item;
use ECommerce\App\Models\Category;
use ECommerce\Core\Database;
use ECommerce\App\Services\FileCacheService;

class SearchController extends BaseController
{
    /**
     * Advanced search
     */
    public function index()
    {
        $query = trim((string) ($this->getParam('q', '')));
        $category = (int) $this->getParam('category_id', 0);
        $minPrice = (float) $this->getParam('min_price', 0);
        $maxPrice = (float) $this->getParam('max_price', 0);
        $sortBy = (string) $this->getParam('sort', 'newest');
        $page = max(1, (int) $this->getParam('page', 1));
        $perPage = 12;
        $filters = [
            'q' => $query,
            'category_id' => $category,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'sort' => $sortBy,
        ];

        $cache = new FileCacheService();
        $cacheKey = 'plp.' . md5(json_encode([$filters, $page, $perPage]));
        $result = $cache->remember($cacheKey, 90, function () use ($filters, $page, $perPage) {
            return (new Item())->advancedList($filters, $page, $perPage);
        });

        $categories = (new Category())->getRootCategories();
        return $this->render('home.search', [
            'items' => $result['items'],
            'total' => $result['total'],
            'query' => $query,
            'category_id' => $category,
            'categories' => $categories,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'sort' => $sortBy,
            'page' => $result['page'],
            'per_page' => $result['per_page'],
            'total_pages' => $result['total_pages'],
        ]);
    }

    /**
     * Autocomplete search
     */
    public function autocomplete()
    {
        $query = trim((string) $this->getParam('q', ''));

        if (strlen($query) < 2) {
            return $this->json(['suggestions' => []]);
        }

        $items = Database::getInstance()->query(
            'SELECT item_id, name, price FROM items WHERE approve = 1 AND name LIKE ? ORDER BY add_date DESC LIMIT 10',
            ['%' . $query . '%']
        );

        $suggestions = array_map(fn($item) => [
            'id' => $item['item_id'],
            'name' => $item['name'],
            'price' => $item['price']
        ], $items);

        return $this->json(['suggestions' => $suggestions]);
    }

    /**
     * Filter suggestions
     */
    public function filters()
    {
        $categories = (new Category())->getRootCategories();
        $priceRange = Database::getInstance()->queryOne(
            "SELECT MIN(CAST(price AS DECIMAL(10,2))) as min_price, MAX(CAST(price AS DECIMAL(10,2))) as max_price FROM items WHERE approve = 1"
        );

        return $this->json([
            'categories' => $categories,
            'price_range' => $priceRange
        ]);
    }

    /**
     * Popular tags/keywords
     */
    public function trending()
    {
        $trending = Database::getInstance()->query(
            "SELECT * FROM items WHERE approve = 1 ORDER BY rating DESC, add_date DESC LIMIT 10"
        );

        return $this->json(['trending' => $trending]);
    }
}
