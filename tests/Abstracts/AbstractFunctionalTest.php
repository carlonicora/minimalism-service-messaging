<?php
namespace CarloNicora\Minimalism\Services\Messaging\Tests\Abstracts;

use CarloNicora\Minimalism\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\ServiceInterface;
use CarloNicora\Minimalism\Minimalism;
use CarloNicora\Minimalism\Services\Encrypter\Encrypter;
use Exception;
use PHPUnit\Framework\TestCase;

abstract class AbstractFunctionalTest extends TestCase
{
    /**
     * @return array
     */
    protected function getEnv(): array
    {
        $fileName = __DIR__ . '/../../.env';

        return file($fileName);
    }

    /**
     * @param string $parameterName
     * @return string|null
     */
    protected function getEnvParameter(string $parameterName): ?string
    {
        foreach ($this->getEnv() ?? [] as $line) {
            if ($line[0] !== '#' && str_contains($line, $parameterName)){
                return substr($line, strlen($parameterName) + 1, -1);
            }
        }
        return null;
    }

    /**
     * @param string $serviceName
     * @return ServiceInterface
     * @throws Exception
     */
    protected function getService(
        string $serviceName
    ): ServiceInterface
    {
        return (new Minimalism())->getService(serviceName: $serviceName, requiresBaseService: false);
    }

    /**
     * @return EncrypterInterface
     */
    public function getEncrypter(): EncrypterInterface
    {
        $encrypterKey = $this->getEnvParameter('MINIMALISM_SERVICE_ENCRYPTER_KEY');
        return new Encrypter($encrypterKey);
    }

}