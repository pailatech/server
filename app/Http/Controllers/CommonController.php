<?php

namespace App\Http\Controllers;


use App\Branch;
use App\Collapsible;
use App\Food;
use App\Http\Services\CommonService;
use App\Order;
use Illuminate\Http\Request;

class CommonController
{
    public function index(string $entity, CommonService $commonService)
    {
        return $commonService->get($this->getModel($entity));
    }

    private function getModel($entity)
    {
        $modelResolver = [
            'foods' => resolve(Food::class),
            'branches' => resolve(Branch::class),
            'orders' => resolve(Order::class),
            'collapsibles' => resolve(Collapsible::class),
        ];

        return $modelResolver[$entity];
    }

    public function store(string $entity, Request $request, CommonService $commonService)
    {
        return $commonService->save($request->except('db_id'), $this->getModel($entity));
    }
}
