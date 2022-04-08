<?php

namespace OC\Metadata;

use OCP\Capabilities\IPublicCapability;

class Capabilities implements IPublicCapability {
	private IMetadataManager $manager;

	public function __construct(IMetadataManager $manager) {
		$this->manager = $manager;
	}

	public function getCapabilities() {
		return ['metadataAvailable' => $this->manager->getCapabilities()];
	}
}
