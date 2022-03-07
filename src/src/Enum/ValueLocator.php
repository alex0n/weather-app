<?php
declare(strict_types=1);

namespace App\Enum;

use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use function array_map;
use function is_array;
use function array_unique;
use function array_filter;

final class ValueLocator
{
    /**
     * @throws ReflectionException
     */
    public function locate(string $target): array
    {
        $reflectionClass = new ReflectionClass($target);

        $publicReflectionConstants = $this->getPublicReflectionConstants(
            ...$reflectionClass->getReflectionConstants()
        );

        return $this->getConstantValues(...$publicReflectionConstants);
    }

    private function getPublicReflectionConstants(ReflectionClassConstant ...$reflectionClassConstants): array
    {
        return array_filter($reflectionClassConstants, static fn (ReflectionClassConstant $constant) => $constant->isPublic());
    }

    private function getConstantValues(ReflectionClassConstant ...$reflectionClassConstants): array
    {
        $constantValues = array_map(static function (ReflectionClassConstant $constant) {
            $value = $constant->getValue();

            if (is_array($value) || $value === null) {
                return null;
            }

            return $value;
        }, $reflectionClassConstants);

        return array_unique(array_filter($constantValues));
    }
}
