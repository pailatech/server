<?php

namespace App\Http\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

class CommonService
{
    // $model may be just class of the new record or it can also be the model from db.

    public function save(array $attributes, EloquentModel $model, int $modelId = null): EloquentModel
    {
        // a check is being done so as to check in the database only if necessary.
        // if an eloquent model from database is sent we would just perform fill and save
        // or if only id is sent we would simply find it from database and perform fill & save.
        $modelObject = $modelId ? ($model->exists ? $model : $this->find($modelId, $model)) : $model;

        $modelClassBaseName = class_basename($modelObject);

        throw_if(
            !$modelObject->fill($attributes)->save(),
            new \Exception("{$modelClassBaseName} could not be saved.")
        );

        return $modelObject->fresh();
    }

    public function find(int $modelId, EloquentModel $model, array $with = []): EloquentModel
    {
        $modelObject = $this->baseEloquentBuilder($model, ['id' => $modelId], $with)->limit(1)->first();
        $classBaseName = class_basename($model);
        throw_if(!$modelObject, new \Exception("$classBaseName not found."));

        return $modelObject;
    }

    public function update(int $modelId, Model $model, array $attributes)
    {
        return $this->baseEloquentBuilder($model, ['id' => $modelId])->update($attributes);
    }

    public function baseEloquentBuilder(
        EloquentModel $model,
        array $where = [],
        array $with = [],
        array $select = ['*'],
        array $orderBy = ['id', 'desc']
    ): EloquentBuilder {
        return $model->with($with)->select($select)->where($where)->orderBy(reset($orderBy), end($orderBy));
    }

    public function createMany(array $models, Model $model): int
    {
        $model->query()->insert($models);
    }

    public function dynamicRelation(int $modelId, Model $model, string $relation): ? object
    {
        return $this->find($modelId, $model, [$relation])->$relation;
    }

    public function get(
        EloquentModel $model,
        array $where = [],
        array $with = [],
        array $select = ['*'],
        array $orderBy = []
    ): Collection {
        if (count($orderBy)) {
            return $this->baseEloquentBuilder($model, $where, $with, $select, $orderBy)->get();
        }

        return $this->baseEloquentBuilder($model, $where, $with, $select)->get();
    }

    public function delete($entity, $entityId)
    {
        return $this->baseEloquentBuilder($entity, ['id' => $entityId])->delete();
    }

}
