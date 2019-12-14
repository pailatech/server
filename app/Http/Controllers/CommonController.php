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
    private $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function index(string $entity)
    {
        return $this->commonService->get($this->getModel($entity));
    }

    private function getModel($entity)
    {
        $modelResolver = [
            'foods' => resolve(Food::class),
            'orders' => resolve(Order::class),
            'branches' => resolve(Branch::class),
            'collapsibles' => resolve(Collapsible::class),
        ];

        return $modelResolver[$entity];
    }

    public function store(string $entity, Request $request)
    {
        return $this->commonService->save($request->all(), $this->getModel($entity));
    }

    public function update(string $entity, int $entityId, Request $request)
    {
        return $this->commonService->save($request->all(), $this->getModel($entity), $entityId);
    }

    public function delete(string $entity, $entityId)
    {
        return $this->commonService->delete($this->getModel($entity), $entityId);
    }
}
