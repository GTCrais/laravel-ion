<?php

/*
|
| This class is not a Singleton because
| it needs to check for auth user
| every time it's used
|
*/

namespace GTCrais\Ion\Services;

use GTCrais\Ion\Repositories\EntityRepository;
use GTCrais\Ion\Resources\Entity;
use GTCrais\Ion\Resources\EntityConfig;

class DataProvider
{
	protected $entityConfigProvider;
	protected $navigationProvider;
	protected $entityRepository;
	protected $requestHelper;
	protected $domainHelper;
	protected $user;

	public function __construct(
		EntityConfigProvider $entityConfigProvider,
		NavigationProvider $navigationProvider,
		EntityRepository $entityRepository,
		RequestHelper $requestHelper,
		DomainHelper $domainHelper
	) {
		$this->entityConfigProvider = $entityConfigProvider;
		$this->navigationProvider = $navigationProvider;
		$this->entityRepository = $entityRepository;
		$this->requestHelper = $requestHelper;
		$this->domainHelper = $domainHelper;
	}

	public function forUser($user)
	{
		$this->user = $user;

		return $this;
	}

	public function getConstData()
	{
		return [
			'ionWebPath' => $this->domainHelper->ionWebPath,
			'ionApiPath' => $this->domainHelper->ionApiPath
		];
	}

	public function getInitialData()
	{
		if (!$this->getUser()) {
			return [];
		}

		return [
			'user' => $this->getUser(),
			'navigation' => $this->navigationProvider->navigationForUser($this->getUser()),
			'entityConfigs' => $this->entityConfigProvider->entityConfigsForUser($this->getUser()),
			'defaultEntityUid' => config('ion.settings.defaultEntityUid'),
			'entityLocales' => config('ion.settings.entityLocales')
		];
	}

	public function getEntityData($entityUid)
	{
		if ($entityConfig = $this->entityConfigProvider->getEntityConfigOrNull($entityUid)) {
			$data = $this->entityRepository->getIndexData($entityConfig);

			if ($entityConfig->structure('type') == 'keyValue') {
				$data['entityObjectData'] = $this->getEntityObjectDataForKeyValueObjects($entityConfig, $data['objects']);
			}

			return $data;
		}

		return [];
	}

	protected function getEntityObjectDataForKeyValueObjects(EntityConfig $entityConfig, $objects)
	{
		$fieldData = [
			'defaultEditValues' => [],
			'selectValues' => []
		];

		foreach ($objects as $object) {
			if ($field = ($entityConfig->localizedFieldOrNull($object->key) ?? $entityConfig->fieldOrNull($object->key))) {

				$fieldData['defaultEditValues'] += $entityConfig->getEditValueForField($field, $object) ?? [];

				if ($field->type == 'select') {
					$fieldData['selectValues'][$field->property] = $entityConfig->getSelectListForField($field);
				}
			}
		}

		return $fieldData;
	}

	public function getEntityObjectData($entityUid, $entityId = null)
	{
		$entityConfig = $this->entityConfigProvider->getEntityConfig($entityUid);
		/** @var Entity $entity */
		$entity = $entityConfig->fqcn;
		$object =
			$entityId ?
			$entity::with($entity::editWith())
				->without($entity::editWithout())
				->findOrFail($entityId) :
			$entity::getNewEntity();

		$additionalData = $entityId ? $entity::getAdditionalEditData($object) : $entity::getAdditionalAddData();

		return
			compact('object') +
			$this->getFieldData($entityConfig, $object) +
			$additionalData;
	}

	public function getFieldData(EntityConfig $entityConfig, $object)
	{
		$fieldData = [
			'defaultEditValues' => [],
			'selectValues' => []
		];

		foreach ($entityConfig->fields->all as $field) {
			if ($object) {
				$fieldData['defaultEditValues'] += $entityConfig->getEditValueForField($field, $object) ?? [];
			}

			if (in_array($field->type, ['select', 'selectMany'])) {
				$fieldData['selectValues'][$field->property] = $entityConfig->getSelectListForField($field);
			}
		}

		return $fieldData;
	}

	protected function getUser()
	{
		if (!$this->user) {
			$this->user = auth()->user();
		}

		if (method_exists($this->user, 'loadRelated')) {
			$this->user->loadRelated();
		}

		return $this->user;
	}
}