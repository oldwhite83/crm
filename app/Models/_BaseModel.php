<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class _BaseModel extends Model {
	protected $perPage = 50;

	public function getById($id, $columns = ['*']) {
		$one = $this->find($id, $columns);
        return empty($one) ? array():$one->toArray();
	}

	public function getLists(array $condition = [], array $columns = ['*'], array $orderBy = [], $limit = null) {
		return $this
			->parseCondition($condition)
			->parseOrderBy($orderBy)
			->limit($limit)
			->select($columns)
			->get()
			->toArray();
	}

	public function getCount(array $condition = []) {
		return $this
			->parseCondition($condition)
			->count();
	}

	public function getPaginate(array $condition = [], array $columns = ['*'], array $orderBy = [], $page = null, $pageName = 'page') {
		return $this
			->parseCondition($condition)
			->parseOrderBy($orderBy)
			->select($columns)
			->paginate($this->perPage, $columns, $pageName, $page);
	}

	public function updateById($id, array $data) {
		$one = $this->findOrFail($id);

		$update = $one->update($data);
		return $one->toArray();
	}

	public function updateByCondition(array $condition, array $data) {
		return $this
			->parseCondition($condition)
			->update($data);
	}

	public function scopeParseCondition($query, array $condition) {
		foreach ($condition as $column => $where) {
			$where = (array) $where;
			if (in_array($where[0], ['in', 'notIn', 'null', 'notNull', 'between', 'notBetween'])) {
				$method = Str::camel('where_' . $where[0]);
				array_shift($where);
                $where[0] = explode(",",$where[0]);
			} else {
				$method = 'where';
			}
			$whereArgs[0] = $column;
			$whereArgs = array_merge($whereArgs, (array) $where);
			call_user_func_array([$query, $method], $whereArgs);
			unset($whereArgs);
		}
		return $query;
	}

	public function scopeParseOrderBy($query, array $orderBy) {
		foreach ($orderBy as $order) {
			switch (count($order)) {
				case '1':
					$query->orderBy($order[0]);
					break;
				case '2':
					$query->orderBy($order[0], $order[1]);
					break;
			}
		}
		return $query;
	}
}
