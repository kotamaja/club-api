<?php

namespace App\Core\Exception;

use App\Core\Enum\Feature;
use RuntimeException;

final class FeatureNotAvailableException extends RuntimeException
{
    public function __construct(private readonly Feature $feature)
    {
        parent::__construct(sprintf('Feature "%s" is not available for this organization.', $feature->value));
    }

    public function getFeature(): Feature
    {
        return $this->feature;
    }
}
