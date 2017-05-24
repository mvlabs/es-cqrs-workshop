<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Action;

use Fig\Http\Message\StatusCodeInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

final class CreatePizzeria implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        return (new Response())->withStatus(StatusCodeInterface::STATUS_ACCEPTED);
    }
}
