<?php

namespace OC\Metadata\Provider;

use OC\Metadata\IMetadataProvider;
use OC\Metadata\MetadataGroup;
use OCP\Files\File;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

class ExifProvider implements IMetadataProvider {
	public function __construct(IConfig $config, LoggerInterface $logger) {
	}

	static public function groupsProvided(): array {
		return ['size', 'gps'];
	}

	static public function isAvailable(): bool {
		return extension_loaded('exif');
	}

	private function getGps(array $exifCoordinate, string $hemi): float {
		$degrees = count($exifCoordinate) > 0 ? $this->gps2Num($exifCoordinate[0]) : 0;
		$minutes = count($exifCoordinate) > 1 ? $this->gps2Num($exifCoordinate[1]) : 0;
		$seconds = count($exifCoordinate) > 2 ? $this->gps2Num($exifCoordinate[2]) : 0;

		$flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

		return $flip * ($degrees + $minutes / 60 + $seconds / 3600);

	}

	private function gps2Num(string $coordinatePart): float {
		$parts = explode('/', $coordinatePart);

		if (count($parts) <= 0) {
			return 0;
		}

		if (count($parts) === 1) {
			return $parts[0];
		}

		return floatval($parts[0]) / floatval($parts[1]);
	}


	public function execute(File $file): array {
		$fileDescriptor = $file->fopen('rb');
		$data = exif_read_data($fileDescriptor, 'ANY_TAG', true);
		if (!$data) {
			return [
				'size' => new MetadataGroup('size', []),
				'gps' => new MetadataGroup('gps', []),
			];
		}

		if (in_array('COMPUTED', $data)
			&& in_array('Width', $data['COMPUTED'])
			&& in_array('Height', $data['COMPUTED'])
		) {
			$size = new MetadataGroup('size', [
				'width' => $data['COMPUTED']['Width'],
				'height' => $data['COMPUTED']['Height'],
			]);
		} else {
			$size = new MetadataGroup('size', []);
		}

		if (in_array('GPS', $data)) {
			$gps = new MetadataGroup('gps', [
				'longitude' => $this->getGps($data['GPS']['GPSLongitude'],
					$data['GPS']['GPSLongitudeRef']),
				'latitude' => $this->getGps($data['GPS']['GPSLatitude'],
					$data['GPS']['GPSLatitudeRef'])
			]);
		} else {
			$gps = new MetadataGroup('gps', []);
		}

		return [
			'size' => $size,
			'gps' => $gps,
		];
	}

	static public function getMimetypesSupported(): string {
		return '/image\/.*/';
	}
}
