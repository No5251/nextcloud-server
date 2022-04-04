<?php

namespace OC\Metadata;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @psalm-type FileId = int
 */

class FileMetadataMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'file_metadata', FileMetadata::class);
	}

	/**
	 * @return MetadataGroup[]
	 * @throws Exception
	 */
	public function findForFile(int $fileId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('file_id', $qb->createNamedParameter($fileId, IQueryBuilder::PARAM_INT)));

		return $this->findEntities($qb);
	}

	/**
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 * @throws Exception
	 */
	public function findForGroupForFile(int $fileId, string $groupName): MetadataGroup {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('file_id', $qb->createNamedParameter($fileId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('group_name', $qb->createNamedParameter($groupName, IQueryBuilder::PARAM_STR)));

		return $this->findEntity($qb);
	}

	/**
	 * @return array<FileId, MetadataGroup>
	 * @throws Exception
	 */
	public function findForGroupForFiles(array $fileIds, string $groupName): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->in('file_id', $qb->createNamedParameter($fileIds, )));

		return $this->findEntities($qb);
	}
}
