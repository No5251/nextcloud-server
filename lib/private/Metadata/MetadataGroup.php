<?php

namespace OC\Metadata;

final class MetadataGroup extends \JsonSerializable {
	private string $name;
	/** @var array<string, mixed> */
	private array $metadata;

	public function __construct(string $name, array $metadata) {
		$this->metadata = $metadata;
		$this->name = $name;
	}

	public function getName(): string {
		return $this->name;
	}

	/** @return array<string, mixed> */
	public function getMetadata(): array {
		return $this->metadata;
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->metadata;
	}
}
