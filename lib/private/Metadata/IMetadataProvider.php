<?php

namespace OC\Metadata;

use OCP\Files\File;
use OCP\Files\Storage\IStorage;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

/**
 * Interface for the metadata providers. If you want an application to provide
 * some metadata, you can use this to store them.
 */
interface IMetadataProvider {

	public function __construct(IConfig $config, LoggerInterface $logger);

	/**
	 * The list of groups that this metadata provider is able to provide. When
	 * an application request metadata about a files, the server will look into
	 * each provider to determine which one can provide the metadata and with
	 * which priority (the highest priority win).
	 *
	 * @return array<string, int> A map from the group name to the priority of
	 *         the group.
	 */
	static public function groupsProvided(): array;

	/**
	 * Check if the metadata provider is available. A metadata provider might be
	 * unavailable due to a php extension not being installed.
	 */
	static public function isAvailable(): bool;

	/**
	 * Get the mimetypes supported as a regex.
	 */
	static public function getMimetypesSupported(): string;

	/**
	 * Execute the extraction on the specified file. The metadata should be
	 * grouped by metadata
	 *
	 * Each group should be json serializable and the string representation
	 * shouldn't be longer than 4000 characters.
	 *
	 * @param File $file The file to extract the metadata from
	 * @param array<string, MetadataGroup> An array containing all the metadata fetched.
	 */
	public function execute(File $file): array;
}
