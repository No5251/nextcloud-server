<?php declare(strict_types=1);

namespace OC\Metadata;

use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\Events\Node\NodeCreatedEvent;
use OCP\Files\Events\Node\NodeDeletedEvent;
use OCP\Files\Events\Node\NodeWrittenEvent;
use OCP\Files\File;
use OCP\Files\Node;

class FileEventListener implements IEventListener {
	private IMetadataManager $manager;

	private function shouldExtractMetadata(Node $node): bool {
		if ($node->getMimetype() === 'httpd/unix-directory') {
			return false;
		}
		$path = $node->getPath();

		// TODO make this more dynamic, we have the same issue in other places
		return !str_starts_with($path, 'appdata_') && !str_starts_with($path, 'files_versions/') && !str_starts_with($path, 'files_trashbin/');
	}

	public function __construct(IMetadataManager $manager) {
		$this->manager = $manager;
	}

	public function handle(Event $event): void {
		if ($event instanceof NodeDeletedEvent) {
			$node = $event->getNode();
			if ($this->shouldExtractMetadata($node)) {
				/** @var File $node */
				$this->manager->clearMetadata($event->getNode()->getId());
			}
		}

		if ($event instanceof NodeCreatedEvent || $event instanceof NodeWrittenEvent) {
			$node = $event->getNode();
			if ($this->shouldExtractMetadata($node)) {
				/** @var File $node */
				$this->manager->generateMetadata($event->getNode());
			}
		}
	}
}
