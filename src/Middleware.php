<?php

namespace PhpMiddleware\DoctrineOrmTransaction;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class Middleware
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $this->entityManager->beginTransaction();

        try {
            $result = $next($request, $response);
        } catch (Exception $exception) {
            $this->entityManager->rollback();

            throw $exception;
        } catch (Throwable $exception) {
            $this->entityManager->rollback();

            throw $exception;
        }

        $this->entityManager->flush();
        $this->entityManager->commit();

        return $result;
    }
}
