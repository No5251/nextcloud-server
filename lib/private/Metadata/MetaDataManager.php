<?php

namespace OC\Metadata;

use OCP\Files\File;
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
	}

	/**
	 * @param class-string<IMetadataProvider> $className
	 */
	public function registerProvider(string $className):void {
		if (in_array($className, $this->providerClasses)) {
			return;
		}

		if (call_user_func([$className, 'isAvailable'])) {
			$this->providers[call_user_func([$className, 'supportedMimetypes'])]
				= new $className($this->config, $this->logger);
		}
	}

	public function generateMetadata(File $file, array $existingMetadataGroups = []): void {
		$mimeType = $file->getMimeType();
		foreach ($this->providers as $supportedMimetype => $provider) {
			if (preg_match($supportedMimetype, $mimeType)) {
				if (count(array_intersect($existingMetadataGroups, $provider::groupsProvided())) > 0) {
					$metaDataGroup = $provider->execute($file);
					foreach ($metaDataGroup as $group => $metadata) {
						$fileMetadata = new FileMetadata();
						$fileMetadata->setFileId($file->getId());
						$fileMetadata->setGroupName($group);
						$fileMetadata->setMetadata();
					}
				}
			}
		}
	}
}
