<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Action;

use Fig\Http\Message\StatusCodeInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Prooph\ServiceBus\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

final class CompleteOrder implements MiddlewareInterface
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $this->commandBus->dispatch(\MVLabs\EsCqrsWorkshop\Domain\Command\CompleteOrder::fromCustomerNamePizzeriaPizzaTasteandTimestamp(
            urldecode($request->getParsedBody()['customer']),
            $request->getParsedBody()['pizzeria'],
            urldecode($request->getParsedBody()['pizza']),
            (int) $request->getParsedBody()['at']
        ));

        return (new Response())->withStatus(StatusCodeInterface::STATUS_ACCEPTED);
    }
}
