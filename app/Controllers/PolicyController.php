<?php

namespace ECommerce\App\Controllers;

use ECommerce\Core\BaseController;

class PolicyController extends BaseController
{
    public function about()
    {
        return $this->render('pages.about');
    }

    public function privacy()
    {
        return $this->render('pages.privacy');
    }

    public function terms()
    {
        return $this->render('pages.terms');
    }
}
