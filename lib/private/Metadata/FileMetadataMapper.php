<?php declare(strict_types=1);

namespace OC\Metadata;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class FileMetadataMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'file_metadata', FileMetadata::class);
	}

	/**
	 * @return FileMetadata[]
	 * @throws Exception
	 */
	public function findForFile(int $fileId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($fileId, IQueryBuilder::PARAM_INT)));

		return $this->findEntities($qb);
	}

	/**
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 * @throws Exception
	 */
	public function findForGroupForFile(int $fileId, string $groupName): FileMetadata {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($fileId, IQueryBuilder::PARAM_INT_ARRAY)))
			->andWhere($qb->expr()->eq('group_name', $qb->createNamedParameter($groupName, IQueryBuilder::PARAM_STR)));

		return $this->findEntity($qb);
	}

	/**
	 * @return array<int, FileMetadata>
	 * @throws Exception
	 */
	public function findForGroupForFiles(array $fileIds, string $groupName): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->in('id', $qb->createNamedParameter($fileIds,IQueryBuilder::PARAM_INT_ARRAY)))
			->andWhere($qb->expr()->eq('group_name', $qb->createNamedParameter($groupName, IQueryBuilder::PARAM_STR)));

		/** @var FileMetadata[] $rawEntities */
		$rawEntities = $this->findEntities($qb);
		$metadata = [];
		foreach ($rawEntities as $entity) {
			$metadata[$entity->getId()] = $entity;
		}
		foreach ($fileIds as $id) {
			if (isset($metadata[$id])) {
				continue;
			}
			$empty = new FileMetadata();
			$empty->setMetadata([]);
			$empty->setGroupName($groupName);
			$empty->setId($id);
			$metadata[$id] = $empty;
		}
		return $metadata;
	}

	public function clear(int $fileId): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($fileId, IQueryBuilder::PARAM_INT)));

		$qb->executeStatement();
	}
}
