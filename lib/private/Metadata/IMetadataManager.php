<?php

namespace OC\Metadata;

use OCP\Files\File;

interface IMetadataManager {
	/**
	 * @param class-string<IMetadataProvider> $className
	 */
	public function registerProvider(string $className): void;

	public function generateMetadata(File $file, array $existingMetadataGroups = []): void;
}
