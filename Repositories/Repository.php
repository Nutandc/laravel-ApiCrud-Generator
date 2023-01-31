<?php

namespace App\Repositories;


use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class Repository implements RepositoryInterface
{
    protected Model $model;

    public function __call($method, $arguments)
    {
        return $this->model->{$method}(...$arguments);
    }

    public function getAll()
    {
        return $this->model->filterPaginate();
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function update($id, array $attributes)
    {
        $record = $this->getById($id);
        $record->update($attributes);
        return $record;
    }

    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function delete($id)
    {
        return $this->getById($id)->delete();
    }

    public function getNext($id)
    {
        return $this->model->where('id', '>', $id)
            ->orderBy('id', 'ASC')->first();
    }

    public function getPrevious($id)
    {
        return $this->model->where('id', '<', $id)
            ->orderBy('id', 'DESC')->first();
    }

    public function getModel()
    {
        return $this->model;
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function paginate(int $page)
    {
        return $this->model
            ->orderBy('created_at', 'DESC')
            ->paginate($page);
    }

}
