<?php

namespace OC\Metadata;

use OCP\Files\File;

interface IMetadataManager {
	/**
	 * @param class-string<IMetadataProvider> $className
	 */
	public function registerProvider(string $className): void;

	public function generateMetadata(File $node, array $existingMetadataGroups = []): void;

	public function clearMetadata(int $fileId): void;
}
