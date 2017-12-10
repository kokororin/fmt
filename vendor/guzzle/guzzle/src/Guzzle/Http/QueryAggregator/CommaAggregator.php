<?php

namespace Guzzle\Http\QueryAggregator;

use Guzzle\Http\QueryString;

/**
 * Aggregates nested query string variables using commas
 */
class CommaAggregator implements QueryAggregatorInterface {
	public function aggregate($key, $value, QueryString $query) {
		if ($query->isUrlEncoding()) {
			return [$query->encodeValue($key) => implode(',', array_map([$query, 'encodeValue'], $value))];
		} else {
			return [$key => implode(',', $value)];
		}
	}
}
