<?php
namespace CarloNicora\Minimalism\Services\Messaging\Tests\Abstracts;

use CarloNicora\Minimalism\Interfaces\LoggerInterface;
use CarloNicora\Minimalism\Services\MinimalismLogger;
use CarloNicora\Minimalism\Services\Path;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

abstract class AbstractUnitTest extends TestCase
{
    /**
     * @param $object
     * @param $parameterName
     * @return mixed
     */
    protected function getProperty($object, $parameterName): mixed
    {
        try {
            $property = (new ReflectionClass(get_class($object)))->getProperty($parameterName);
            $property->setAccessible(true);
            return $property->getValue($object);
        } catch (ReflectionException) {
            return null;
        }
    }

    /**
     * @param $object
     * @param $parameterName
     * @param $parameterValue
     */
    protected function setProperty($object, $parameterName, $parameterValue): void
    {
        try {
            $property = (new ReflectionClass(get_class($object)))->getProperty($parameterName);
            $property->setAccessible(true);
            $property->setValue($object, $parameterValue);
        } catch (ReflectionException) {
        }
    }

    /**
     * @return Path
     */
    protected function getPath(): Path
    {
        return new Path();
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        return new MinimalismLogger(
            $this->getPath()
        );
    }

    /**
     * @param string $className
     * @param array $excluded
     * @return array
     */
    protected function getAllMethodNamesExcept(string $className, array $excluded = []): array
    {
        return array_diff(
            get_class_methods($className),
            $excluded
        );
    }

    /**
     * @param MockObject $table
     * @return string
     */
    protected function getSql(
        MockObject $table,
    ): string
    {
        return $this->getProperty(
            $table,
            'sql'
        );
    }

    /**
     * @param MockObject $table
     * @return array
     */
    protected function getParameters(
        MockObject $table,
    ): array
    {
        return $this->getProperty(
            $table,
            'parameters'
        );
    }
}