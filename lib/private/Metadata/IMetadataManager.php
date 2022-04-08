<?php declare(strict_types=1);

namespace OC\Metadata;

use OCP\Files\File;

interface IMetadataManager {
	/**
	 * @param class-string<IMetadataProvider> $className
	 */
	public function registerProvider(string $className): void;

	public function generateMetadata(File $node, bool $checkExisting): void;

	public function clearMetadata(int $fileId): void;

	/** @return array<int, FileMetadata> */
	public function fetchMetadataFor(string $group, array $fileIds): array;

	public function getCapabilities(): array;
}
