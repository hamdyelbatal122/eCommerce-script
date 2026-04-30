<?php

namespace ECommerce\App\Models;

use ECommerce\Core\BaseModel;
use ECommerce\Core\Database;

/**
 * Category Model
 * 
 * Represents product categories
 */
class Category extends BaseModel
{
    protected $table = 'categories';
    protected $primaryKey = 'id';

    const VISIBILITY_ALL = 1;
    const VISIBILITY_HIDDEN = 0;

    /**
     * Get all visible categories
     * 
     * @return array
     */
    public function getVisible()
    {
        return $this->where('visibility', self::VISIBILITY_ALL);
    }

    /**
     * Get root categories (parent = 0)
     * 
     * @return array
     */
    public function getRootCategories()
    {
        return $this->where('parent', 0);
    }

    /**
     * Get subcategories
     * 
     * @param int $parentId
     * @return array
     */
    public function getSubcategories($parentId)
    {
        return $this->where('parent', $parentId);
    }

    /**
     * Get category with subcategories
     * 
     * @param int $categoryId
     * @return array|null
     */
    public function getWithSubcategories($categoryId)
    {
        $category = $this->find($categoryId);
        
        if ($category) {
            $category['subcategories'] = $this->getSubcategories($categoryId);
        }

        return $category;
    }

    /**
     * Get categories as tree
     * 
     * @param int $parentId
     * @return array
     */
    public function getCategoriesTree($parentId = 0)
    {
        $db = Database::getInstance();
        $query = "SELECT * FROM {$this->table} WHERE parent = ? ORDER BY ordering ASC";
        
        $categories = $db->query($query, [$parentId]);

        foreach ($categories as &$category) {
            $category['children'] = $this->getCategoriesTree($category['id']);
        }

        return $categories;
    }

    /**
     * Get category with items count
     * 
     * @return array
     */
    public function getCategoriesWithCounts()
    {
        $db = Database::getInstance();
        $query = "SELECT c.*, COUNT(i.item_id) as items_count
                  FROM {$this->table} c
                  LEFT JOIN items i ON c.id = i.cat_id AND i.approve = 1
                  GROUP BY c.id
                  ORDER BY c.ordering ASC";
        
        return $db->query($query);
    }

    /**
     * Get parent category
     * 
     * @param int $categoryId
     * @return array|null
     */
    public function getParent($categoryId)
    {
        $category = $this->find($categoryId);
        
        if ($category && $category['parent'] > 0) {
            return $this->find($category['parent']);
        }

        return null;
    }

    /**
     * Get breadcrumb path
     * 
     * @param int $categoryId
     * @return array
     */
    public function getBreadcrumb($categoryId)
    {
        $breadcrumb = [];
        $currentId = $categoryId;

        while ($currentId > 0) {
            $category = $this->find($currentId);
            if (!$category) break;

            array_unshift($breadcrumb, $category);
            $currentId = $category['parent'];
        }

        return $breadcrumb;
    }

    /**
     * Check if category allows comments
     * 
     * @param int $categoryId
     * @return bool
     */
    public function allowsComments($categoryId)
    {
        $category = $this->find($categoryId);
        return $category && $category['allow_comment'] == 1;
    }

    /**
     * Check if category allows ads
     * 
     * @param int $categoryId
     * @return bool
     */
    public function allowsAds($categoryId)
    {
        $category = $this->find($categoryId);
        return $category && $category['allow_ads'] == 1;
    }
}
