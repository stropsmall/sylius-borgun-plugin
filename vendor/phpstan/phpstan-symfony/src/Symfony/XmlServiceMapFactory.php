<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use function simplexml_load_file;
use function sprintf;
use function strpos;
use function substr;

final class XmlServiceMapFactory implements ServiceMapFactory
{

	/** @var string */
	private $containerXml;

	public function __construct(string $containerXml)
	{
		$this->containerXml = $containerXml;
	}

	public function create(): ServiceMap
	{
		$xml = @simplexml_load_file($this->containerXml);
		if ($xml === false) {
			throw new XmlContainerNotExistsException(sprintf('Container %s not exists', $this->containerXml));
		}

		/** @var \PHPStan\Symfony\Service[] $services */
		$services = [];
		/** @var \PHPStan\Symfony\Service[] $aliases */
		$aliases = [];
		foreach ($xml->services->service as $def) {
			/** @var \SimpleXMLElement $attrs */
			$attrs = $def->attributes();
			if (!isset($attrs->id)) {
				continue;
			}

			$service = new Service(
				strpos((string) $attrs->id, '.') === 0 ? substr((string) $attrs->id, 1) : (string) $attrs->id,
				isset($attrs->class) ? (string) $attrs->class : null,
				!isset($attrs->public) || (string) $attrs->public !== 'false',
				isset($attrs->synthetic) && (string) $attrs->synthetic === 'true',
				isset($attrs->alias) ? (string) $attrs->alias : null
			);

			if ($service->getAlias() !== null) {
				$aliases[] = $service;
			} else {
				$services[$service->getId()] = $service;
			}
		}
		foreach ($aliases as $service) {
			$alias = $service->getAlias();
			if ($alias !== null && !isset($services[$alias])) {
				continue;
			}
			$id = $service->getId();
			$services[$id] = new Service(
				$id,
				$services[$alias]->getClass(),
				$service->isPublic(),
				$service->isSynthetic(),
				$alias
			);
		}

		return new ServiceMap($services);
	}

}
