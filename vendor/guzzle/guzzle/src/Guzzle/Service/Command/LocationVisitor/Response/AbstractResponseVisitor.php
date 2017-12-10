<?php

namespace Guzzle\Service\Command\LocationVisitor\Response;

use Guzzle\Http\Message\Response;
use Guzzle\Service\Command\CommandInterface;
use Guzzle\Service\Description\Parameter;

/**
 * {@inheritdoc}
 * @codeCoverageIgnore
 */
abstract class AbstractResponseVisitor implements ResponseVisitorInterface {
	public function after(CommandInterface $command) {}
	public function before(CommandInterface $command, array &$result) {}
	public function visit(
		CommandInterface $command,
		Response $response,
		Parameter $param,
		&$value,
		$context = null
	) {}
}
