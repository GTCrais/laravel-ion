<?php

namespace GTCrais\Ion\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use GTCrais\Ion\Resources\EntityConfig;
use GTCrais\Ion\Resources\Entity;

class EntityRepository
{
	protected $request;
	protected $page;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	// todo: move to DataProvider
	public function getIndexData(EntityConfig $entityConfig)
	{
		/** @var Entity $entity */
		$entity = $entityConfig->fqcn;
		$indexData = [
			'addActionAllowed' => $entity::addActionAllowed()
		];

		if ($entityConfig->structure('type') == 'keyValue') {
			return $indexData + $this->getKeyValueEntityIndexData($entityConfig);
		} else if ($entityConfig->structure('type') == 'tree') {
			return $indexData + $this->getTreeEntityIndexData($entityConfig);
		} else {
			return $indexData + $this->getDefaultEntityIndexData($entityConfig);
		}
	}

	// todo: move to DataProvider
	protected function getKeyValueEntityIndexData(EntityConfig $entityConfig)
	{
		/** @var Entity $className */
		$className = $entityConfig->fqcn;

		return [
			'objects' => $className::all(),
			'additionalData' => $this->getAdditionalData($entityConfig)
		];
	}

	// todo: move to DataProvider
	protected function getTreeEntityIndexData(EntityConfig $entityConfig)
	{
		/** @var Entity $className */
		$className = $entityConfig->fqcn;

		return [
			'objects' =>
				$className::indexQuery()
					->with($className::indexWith())
					->without($className::indexWithout())
					->whereNull($entityConfig->structure('parentProperty'))
					->orderBy($entityConfig->structure('position.property'))
					->get(),
			'additionalData' => $this->getAdditionalData($entityConfig)
		];
	}

	// todo: move to DataProvider
	protected function getDefaultEntityIndexData(EntityConfig $entityConfig)
	{
		$indexData = [];

		if ($entityConfig->type == 'model') {
			$indexData['paginator'] = $this->getPaginator($entityConfig);
		}

		$indexData['additionalData'] = $this->getAdditionalData($entityConfig);

		return $indexData;
	}

	protected function getPaginator(EntityConfig $entityConfig)
	{
		/** @var Entity $className */
		$className = $entityConfig->fqcn;

		$query = $className::indexQuery()
			->with($className::indexWith())
			->without($className::indexWithout());
		$query = $this->addQuickSearchParameters($entityConfig, $query);

		$countQuery = clone $query;
		$count = $countQuery->count();

		$objects = $query->orderBy($entityConfig->structure('orderBy'), $entityConfig->structure('direction'))
			->limit($entityConfig->structure('perPage'))
			->offset($this->offset($entityConfig->structure('perPage')))
			->get();

		return new LengthAwarePaginator($objects->toArray(), $count, $entityConfig->structure('perPage'), $this->page(), [
			'path' => $this->request->url()
		]);
	}

	// todo: move to DataProvider
	protected function getAdditionalData(EntityConfig $entityConfig)
	{
		/** @var Entity $fqcn */
		$fqcn = $entityConfig->fqcn;

		return $fqcn::getAdditionalIndexData();
	}

	protected function addQuickSearchParameters(EntityConfig $entityConfig, Builder $query)
	{
		if ($term = trim($this->request->input('term'))) {
			$query->where(function(Builder $query2) use ($entityConfig, $term) {
				foreach ($entityConfig->fields->quickSearch as $field) {

					$table = $entityConfig->getSearchTableForField($field);
					$searchProperties = $entityConfig->getQuickSearchPropertiesForField($field);

					foreach ($searchProperties as $searchProperty) {
						$query2->orWhere(
							$table . '.' . $searchProperty,
							'LIKE',
							'%' . $term . '%'
						);
					}
				}

				// todo: Do we need this? Can't we just add this through 'indexQuery' scope?
				/** @var Entity $fqcn */
				$fqcn = $entityConfig->fqcn;
				$fqcn::addEntityQuickSearchParameters($query2, $term);
			});
		}

		return $query;
	}

	protected function page()
	{
		if (!is_null($this->page)) {
			return $this->page;
		}

		$this->page = filter_var($this->request->get('page'), FILTER_VALIDATE_INT) ?: 1;

		return $this->page;
	}

	protected function offset($perPage)
	{
		return ($this->page() - 1) * $perPage;
	}
}