<?php

namespace Lunches\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Lunches\Exception\RuntimeException;
use Lunches\Exception\ValidationException;

/**
 * Class SizeWeights.
 */
class SizeWeights extends ArrayCollection
{
    /**
     * @return array
     */
    public function toArray()
    {
        $sizes = $this->map(function (SizeWeight $sizeWeight) {
            return $sizeWeight->getSize();
        })->toArray();
        $weights = $this->map(function (SizeWeight $sizeWeight) {
            return $sizeWeight->getWeight();
        })->toArray();

        return array_combine($sizes, $weights);
    }

    /**
     * @param string $size
     * @return int
     * @throws RuntimeException
     * @throws ValidationException
     */
    public function getWeightFromSize($size)
    {
        if ($this->isEmpty()) {
            throw RuntimeException::requiredPropertyIsEmpty('SizeWeights');
        }
        foreach ($this->getValues() as $sizeWeight) {
            /** @var SizeWeight $sizeWeight */
            if ($sizeWeight->getSize() === $size) {
                return $sizeWeight->getWeight();
            }
        }

        throw ValidationException::invalidSize();
    }

}
