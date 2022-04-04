<?php

declare(strict_types=1);

namespace OC\Core\Migrations;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version240000Date20220404230027 extends SimpleMigrationStep {
	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('file_metadata')) {
			$table = $schema->createTable('file_metadata');
			$table->addColumn('file_id', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('group_name', 'string', [
				'notnull' => true,
				'length' => 50,
			]);
			$table->addColumn('data', 'string', [
				'notnull' => true,
				'length' => 2000,
			]);
			$table->setPrimaryKey(['file_id', 'group_name'], 'file_metadata_idx');
		}
		return $schema;
	}
}
