<?php

namespace OC\Metadata;

use OC\Metadata\Provider\ExifProvider;
use OCP\Files\File;
use OCP\Files\Storage\IStorage;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

class MetaDataManager implements IMetadataManager {
	/** @var array<string, IMetadataProvider> */
	private array $providers;
	private array $providerClasses;
	private FileMetadataMapper $fileMetadataMapper;
	private IConfig $config;
	private LoggerInterface $logger;

	public function __construct(
		FileMetadataMapper $fileMetadataMapper,
		IConfig $config,
		LoggerInterface $logger
	) {
		$this->providers = [];
		$this->providerClasses = [];
		$this->fileMetadataMapper = $fileMetadataMapper;
		$this->config = $config;
		$this->logger = $logger;

		// TODO move to another place, where?
		$this->registerProvider(ExifProvider::class);
	}

	/**
	 * @param class-string<IMetadataProvider> $className
	 */
	public function registerProvider(string $className):void {
		if (in_array($className, $this->providerClasses)) {
			return;
		}

		if (call_user_func([$className, 'isAvailable'])) {
			$this->providers[call_user_func([$className, 'getMimetypesSupported'])]
				= new $className($this->config, $this->logger);
		}
	}

	public function generateMetadata(File $file, bool $checkExisting = false): void {
		$existingMetadataGroups = [];

		if ($checkExisting) {
			$existingMetadata = $this->fileMetadataMapper->findForFile($file->getId());
			foreach ($existingMetadata as $metadata) {
				$existingMetadataGroups[] = $metadata->getGroupName();
			}
		}

		foreach ($this->providers as $supportedMimetype => $provider) {
			if (preg_match($supportedMimetype, $file->getMimeType())) {
				if (count(array_diff($provider::groupsProvided(), $existingMetadataGroups)) > 0) {
					$metaDataGroup = $provider->execute($file);
					foreach ($metaDataGroup as $group => $metadata) {
						$this->fileMetadataMapper->insertOrUpdate($metadata);
					}
				}
			}
		}
	}

	public function clearMetadata(int $fileId): void {
		$this->fileMetadataMapper->clear($fileId);
	}

	public function fetchMetadataFor(string $group, array $fileIds): array {
		return $this->fileMetadataMapper->findForGroupForFiles($fileIds, $group);
	}
}
