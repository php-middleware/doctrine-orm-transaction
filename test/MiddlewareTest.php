<?php

namespace PhpMiddlewareTest\DoctrineOrmTransaction;

use Doctrine\ORM\EntityManagerInterface;
use PhpMiddleware\DoctrineOrmTransaction\Middleware;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class MiddlewareTest extends PHPUnit_Framework_TestCase
{
    private $middleware;
    private $entityManager;
    private $request;
    private $response;

    protected function setUp()
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);

        $this->middleware = new Middleware($this->entityManager);
    }

    public function testCommitIfAllRight()
    {
        $this->entityManager->expects($this->at(0))->method('beginTransaction');
        $this->entityManager->expects($this->at(1))->method('flush');
        $this->entityManager->expects($this->at(2))->method('commit');

        $this->entityManager->expects($this->never())->method('rollback');

        $next = function ($request, $response) {
            return $response;
        };

        $result = $this->executeMiddleware($next);

        $this->assertSame($this->response, $result);
    }

    public function testRollbackIfFail()
    {
        $this->entityManager->expects($this->at(0))->method('beginTransaction');
        $this->entityManager->expects($this->at(1))->method('rollback');

        $this->entityManager->expects($this->never())->method('flush');
        $this->entityManager->expects($this->never())->method('commit');

        $next = function () {
            throw new RuntimeException();
        };

        $this->expectException(RuntimeException::class);

        $this->executeMiddleware($next);
    }

    private function executeMiddleware(callable $next)
    {
        return $this->middleware->__invoke($this->request, $this->response, $next);
    }
}
