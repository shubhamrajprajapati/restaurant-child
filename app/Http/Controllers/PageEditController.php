<?php

namespace App\Http\Controllers;

use App\Services\PageEditService;

class PageEditController extends Controller
{
    protected $pageEditService;

    public function __construct(PageEditService $pageEditService)
    {
        $this->pageEditService = $pageEditService;
    }
}
